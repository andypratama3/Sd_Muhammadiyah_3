<?php

namespace Linguise\Script\Core;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class CurlRequest
{
    /**
     * Reads JSON input from the request body, and seeks back to the start after reading.
     *
     * Include fixes for https://github.com/php/php-src/issues/9441
     *
     * @return array|null|false Decoded JSON, null if no JSON, false if fail to read
     */
    private function readJSONInput()
    {
        if (isset($_SERVER['HTTP_CONTENT_TYPE']) && strpos($_SERVER['HTTP_CONTENT_TYPE'], 'application/json') === 0) {
            $stream = fopen('php://input', 'r');

            // Check if it's seeked already.
            $pos = ftell($stream);
            if ($pos === 0) {
                // read to the end
                $memory = stream_get_contents($stream);
                if ($memory === false) {
                    // fail to read
                    fclose($stream);
                    return false;
                }

                // Seek back to the start for further processing by other scripts
                fseek($stream, 0);
            } else {
                // Seeked already, move back to start
                $success = fseek($stream, 0);
                if ($success !== 0) {
                    // fail to seek
                    return false;
                }

                $memory = stream_get_contents($stream);
                if ($memory === false) {
                    // fail to read, seek back to original $pos
                    fseek($stream, $pos);
                    fclose($stream);
                    return false;
                }

                // Seek back to the original position
                fseek($stream, $pos);
            }

            fclose($stream);
            $decoded_json = json_decode($memory, true);
            if ($decoded_json !== null) {
                return $decoded_json;
            }

            return null;
        }
    }

    public function makeRequest()
    {
        session_write_close(); // Make sure to close session that could prevent curl to timeout
        $ch = curl_init();
        $input_headers = array();

        $skip_content_type = false;
        if (in_array($_SERVER['REQUEST_METHOD'], array('POST', 'PUT'))) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
            } else {
                curl_setopt($ch, CURLOPT_PUT, true);
            }

            $post_fields = array();

            if (!empty($_POST)) {
                foreach ($_POST as $post_name => $post_value) {
                    $post_fields[$post_name] = $post_value;
                }
            }

            $content_type = isset($_SERVER['HTTP_CONTENT_TYPE']) ? $_SERVER['HTTP_CONTENT_TYPE'] : (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '');
            if (isset($_SERVER['CONTENT_TYPE']) && !isset($_SERVER['HTTP_CONTENT_TYPE']) && !empty($content_type)) {
                // Set HTTP_CONTENT_TYPE
                $_SERVER['HTTP_CONTENT_TYPE'] = $content_type;
            }

            if (count($post_fields) && !empty($content_type)) {
                if (strpos($content_type, 'application/json') === 0) {
                    Hook::trigger('onBeforePostFields', $post_fields);

                    $post_fields = json_encode($post_fields);
                } elseif (strpos($content_type, 'application/x-www-form-urlencoded') === 0) {
                    Hook::trigger('onBeforePostFields', $post_fields);

                    $post_fields = http_build_query($post_fields);
                } elseif (strpos($content_type, 'multipart/form-data') === 0) {
                    $boundary = new Boundary();
                    foreach ($post_fields as $post_field_name => $post_field_value) {
                        $boundary->addPostFields($post_field_name, $post_field_value);
                    }

                    // Add file here
                    if (!empty($_FILES)) {
                        foreach ($_FILES as $file_name => $file_value) {
                            if (is_array($file_value['name'])) {
                                foreach ($file_value['name'] as $index => $file_name_value) {
                                    if (!$file_value['tmp_name'][$index]) {
                                        continue;
                                    }
        
                                    $boundary->addPostFile(
                                        $file_name . '[' . $index . ']',
                                        $file_value['tmp_name'][$index],
                                        $file_name_value,
                                        $file_value['type'][$index]
                                    );
                                }
                            } else {
                                if (!$file_value['tmp_name']) {
                                    continue;
                                }

                                $boundary->addPostFile($file_name, $file_value['tmp_name'], $file_name, $file_value['type']);
                            }
                        }
                    }

                    Hook::trigger('onBeforePostFields', $boundary);

                    $post_fields = $boundary->getContent();
                    $input_headers[] = 'Content-Type: multipart/form-data; boundary=' . $boundary->getBoundary();
                    $skip_content_type = true;
                }
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            } else if (count($post_fields)) {
                // fallback for empty HTTP_CONTENT_TYPE variable
                $post_fields = http_build_query($post_fields);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            } else if (!count($post_fields) && strpos($content_type, 'application/json') === 0) {
                // handling for JSON content type, check if the $post_fields is empty and if the content type is application/json(; charset=UTF-8)
                $json_data = $this->readJSONInput();
                if (!empty($json_data)) {
                    // Valid JSON
                    $post_fields = json_encode($json_data);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
                }
            }
            //fixme: handle x-www-form-urlencoded  form-data and raw https://www.w3.org/TR/html401/interact/forms.html#h-17.13.4.1
        }

        $url = Request::getInstance()->getNonTranslatedUrl();

        Debug::log('Requesting website url ' . $url);

        foreach ($_SERVER as $header_name => $header_value) {
            if (substr($header_name, 0, 5) !== 'HTTP_') {
                continue;
            }

            if ($skip_content_type && $header_name === 'HTTP_CONTENT_TYPE') {
                continue;
            }

            if (in_array($header_name, array('HTTP_HOST', 'HTTP_ACCEPT_ENCODING', 'HTTP_CONTENT_LENGTH'))) {
                continue;
            }

            $input_headers[] = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($header_name, 5))))) . ': ' . $header_value;
        }

        $input_headers[] = 'Linguise-Original-Language: ' . preg_replace('[^a-zA-Z-]', '', Request::getInstance()->getLanguage());

        // Add real user IP
        $input_headers[] = 'X-Forwarded-For: ' . Helper::getIpAddress();

        curl_setopt($ch, CURLOPT_URL, $url);
        if (Configuration::getInstance()->get('server_ip') !== null) {
            curl_setopt($ch, CURLOPT_CONNECT_TO, [Request::getInstance()->getHostname() . ':' . Configuration::getInstance()->get('server_port') . ':' . Configuration::getInstance()->get('server_ip') . ':' . Configuration::getInstance()->get('server_port')]); // fixme: only available from php 7.0
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $input_headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        if (Configuration::getInstance()->get('dl_certificates') === true) {
            curl_setopt($ch, CURLOPT_CAINFO, Certificates::getInstance()->getPath());
        }

        $curl_multi = CurlMulti::getInstance();
        $curl_multi->addRequest(Cache::getInstance());
        $curl_multi->addRequest(Certificates::getInstance());
        $curl_multi->executeRequests();

        $time_start = microtime(true);
        $curl_response = curl_exec($ch);
        Debug::timing('Curl website request took %s', $time_start);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($curl_response, 0, $header_size);
        $body = substr($curl_response, $header_size);
        $redirected_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $response = Response::getInstance();

        if ($response_code === 0) {
            $response->setRedirect($url);
            Debug::log('Failed to retrieve website data, redirect to ' . $url . '. Error was :' . curl_error($ch));
            Debug::saveError('Failed to retrieve website data, redirect to ' . $url . '. Error was :' . curl_error($ch));
            $response->end();
        }

        // Add actual request headers
        foreach (explode("\r\n", $headers) as $index => $header) {
            if ($index === 0) {
                continue;
            }
            if ($header === '') {
                continue;
            }

            $header_parts = explode(':', $header, 2);

            if (count($header_parts) !== 2) {
                continue;
            }

            $response->addHeader($header_parts[0], ltrim($header_parts[1]));
        }

        $content_type = $response->getHeader('Content-Type');
        $content_type = explode(';', $content_type)[0];

        // Check if JSON response (include utf-8 charset)
        if ($response_code === 200 && strpos($content_type, 'application/json') === 0) {
            $response->setResponseCode($response_code);
            $response->setContent($body);
            // So in Configuration[Local] we can modify JSON response if we want.
            Hook::trigger('onJsonResponse');
            $response->end();
        }

        if ($response_code !== 304 && !in_array($content_type, ['text/html', 'application/xhtml+xml', 'application/xml', 'text/xml', 'application/rss+xml'])) {
            $response->setRedirect($url);
            Debug::log('Content type not translatable ' . $content_type);
            Debug::saveError('Content type not translatable ' . $content_type);
            $response->end();
        }

        $response->setResponseCode($response_code);
        $response->setContent($body);
        Debug::log('Response code: ' . $response_code);
        Debug::log('Original content retrieved: ' . PHP_EOL . '######################' . PHP_EOL . $body . PHP_EOL . '######################', 5);

        $curl_multi->waitRequests();

        if ($redirected_url) {
            Debug::log('Website redirect to ' . $redirected_url);

            if (!$response->hasHeader('Linguise-Translated-Redirect')) {
                // The url given is not a multilingual url, we need to translate it first
                $redirected_url = Url::translateUrl($redirected_url);
            }

            $response->setRedirect($redirected_url);
            Hook::trigger('onBeforeRedirect');
            $response->end();
        }

    }
}
