<?php
namespace Linguise\Script\Core;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class Response {

    /**
     * @var null|Response
     */
    private static $_instance = null;

    /**
     * @var null|string Content to translate
     */
    protected $content = null;

    /**
     * @var null|string Url to redirect the user to
     */
    protected $redirect = null;

    /**
     * @var int Response code received
     */
    protected $response_code = null;

    /**
     * @var null|int Content type
     */
    protected $content_type = null;

    /**
     * @var array Headers
     */
    protected $headers = [];


    /**
     * @var array Cookies
     */
    protected $cookies = [];

    /**
     * Retrieve singleton instance
     *
     * @return Response|null
     */
    public static function getInstance() {

        if(is_null(self::$_instance)) {
            self::$_instance = new Response();
        }

        return self::$_instance;
    }

    /**
     * Set html content to be translated
     *
     * @param $content string Html content to translate
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Return current response content
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Clear the content
     *
     * @return void
     */
    public function clearContent()
    {
        $this->content = null;
    }

    public function setResponseCode($response_code, $overwrite = true)
    {
        if ($this->response_code === null || $overwrite === true) {
            $this->response_code = $response_code;
        }
    }

    public function getResponseCode()
    {
        return $this->response_code;
    }

    public function setContentType($content_type)
    {
        $this->content_type = $content_type;
    }

    /**
     * Get current redirect url
     * 
     * @return string|null
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * Set redirection
     *
     * @param $url string Url to redirect the user to
     * @param int $response_code int Response code to set
     */
    public function setRedirect($url, $response_code = 303)
    {
        $this->redirect = $url;
        $this->setResponseCode($response_code, true);
    }

    /**
     * Add header
     *
     * @param $name
     * @param $value
     */
    public function addHeader($name, $value)
    {
        $name = ucwords($name, '-');
        if ($name === 'Set-Cookie') {
            $cookie_parser = new SetCookie;
            $this->cookies[] = $cookie_parser->fromString($value);
        } else {
            $this->headers[$name] = $value;
        }
    }

    /**
     * Check if a header is set
     *
     * @param $name
     * @return bool
     */
    public function hasHeader($name) {
        if (!empty($this->headers[$name])) {
            return true;
        }
        return false;
    }

    /**
     * Retrieve a header already set
     *
     * @param $name
     * @return null|String
     */
    public function getHeader($name) {
        if (!empty($this->headers[$name])) {
            return $this->headers[$name];
        }
        return null;
    }

    public function addCookie($name, $value, $expires = 0, $path = "", $domain = "", $secure = false, $httpOnly = false) {
        $cookie_parser = new SetCookie([
            'Name'     => $name,
            'Value'    => $value,
            'Domain'   => $domain,
            'Path'     => $path,
            'Expires'  => $expires,
            'Secure'   => $secure,
            'HttpOnly' => $httpOnly
        ]);
        $this->cookies[] = $cookie_parser;
    }

    /**
     * Get Cookies
     * 
     * @return array Cookies
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Actually redirect
     */
    protected function redirect()
    {
        if ($this->redirect === null) {
            // Nothing to do
            return;
        }

        // Make sure nothing we don't want is echoed
        //ob_end_clean();

        header('Location: '.$this->redirect, true, 301);
    }

    public function end()
    {
        ignore_user_abort(true);

        // Remove current request headers
        header_remove();

        // Remove all content that could have been sent to buffer
        ob_end_clean();

        // Turn on output buffering
        ob_start();

        if ($this->content) {
            //fixme: handle gzip
            echo $this->content;
        }

        // Set redirection if any
        $this->redirect();

        foreach ($this->headers as $header_name => $header_value) {
            if (in_array(strtolower($header_name), array('transfer-encoding', 'location', 'content-encoding'))) continue;
            header($header_name.': '.$header_value);
        }

        foreach ($this->cookies as $cookie) {
            setrawcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpires(), $cookie->getPath(), $cookie->getDomain(), $cookie->getSecure(), $cookie->getHttpOnly());
        }

        header('Connection: close');
        header_remove('Content-Length');

        http_response_code($this->response_code);

        ob_end_flush();

        exit(0);
    }
}
