<?php

namespace Linguise\Script\Core;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class Certificates
{
    /**
     * @var null|Request
     */
    private static $_instance = null;

    /**
     * @var string The action parameter to use in request
     */
    public $_action = 'update-certificates';

    /**
     * Retrieve singleton instance
     *
     * @return Request|null
     */
    public static function getInstance() {

        if(is_null(self::$_instance)) {
            self::$_instance = new Certificates();
        }

        return self::$_instance;
    }

    /**
     * Return the certificate file path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getCertificatesPath() . 'cacert.pem';
    }

    /**
     * Return the certificate folder path
     *
     * @return string
     */
    public function getCertificatesPath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'certificates' . DIRECTORY_SEPARATOR;
    }

    /**
     * Check if the request to launch this task should be executed or not
     * It checks if the certificate has been check more than a day ago
     *
     * @return bool
     */
    public function shouldBeExecuted() {
        $time_info_file = $this->getCertificatesPath() . 'time.txt';
        if (file_exists($time_info_file) && (int)file_get_contents($time_info_file) + (Configuration::getInstance()->get('dl_certificates') ? 86400 : 604800) > time()) {
            return false;
        }
        return true;
    }

    /**
     * Download latest bundle
     *
     * @return void
     */
    public function downloadCertificates()
    {
        if (!$this->shouldBeExecuted()) {
            return;
        }

        $etag_file = $this->getCertificatesPath() . 'etag.txt';
        $latest_etag = null;
        if (file_exists($etag_file)) {
            $latest_etag = trim(file_get_contents($etag_file));
        }

        $url = 'https://curl.se/ca/cacert.pem';

        $etag = null;
        if ($latest_etag) { // Only check for etag equality if we already have one
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_CAINFO, $this->getPath());
            $headers = curl_exec($ch);
            $info = curl_getinfo($ch);

            if ($info['http_code'] !== 200 || $info['content_type'] !== 'application/x-pem-file') {
                // Something went wrong
                return;
            }

            $etag = $this->getEtag($headers);

            curl_close($ch);
        }

        if ($etag && $latest_etag && $etag === $latest_etag) {
            // Same etag as last file we have downloaded, skip download
            $this->setLastCheckTime();
            return;
        }


        // File has changed, let's download it
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CAINFO, $this->getPath());

        $content = curl_exec($ch);
        $info = curl_getinfo($ch);

        if ($info['http_code'] !== 200 || $info['content_type'] !== 'application/x-pem-file') {
            // Something went wrong
            return;
        }

        // Download checksum
        curl_setopt($ch, CURLOPT_URL, 'https://curl.se/ca/cacert.pem.sha256');
        $sha256sum = curl_exec($ch);
        $info = curl_getinfo($ch);

        if ($info['http_code'] !== 200) {
            // Something went wrong
            return;
        }

        $sha256sum = explode(' ', $sha256sum);

        if (!empty($sha256sum[0]) &&hash('sha256', $content) !== $sha256sum[0]) {
            // Not the expected checksum
            return;
        }

        file_put_contents($this->getCertificatesPath() . 'cacert.pem', $content);
        file_put_contents($etag_file, $etag);

        $this->setLastCheckTime();
    }

    /**
     * Extract etag from headers
     *
     * @param $headers
     * @return string|null
     */
    protected function getEtag($headers)
    {
        $etag = null;
        foreach (explode("\r\n", $headers) as $index => $header) {
            if ($index === 0) continue;
            if ($header === 'etag') continue;

            $header_parts = explode(':', $header, 2);

            if ($header_parts[0] === 'etag') {
                $etag = trim(trim($header_parts[1]), '"');
                break;
            }
        }

        return $etag;
    }

    /**
     * Write to txt file the latest timestamp when we checked the certificates
     *
     * @return void
     */
    protected function setLastCheckTime()
    {
        $time_info_file = $this->getCertificatesPath() . 'time.txt';
        file_put_contents($time_info_file, time());
    }
}