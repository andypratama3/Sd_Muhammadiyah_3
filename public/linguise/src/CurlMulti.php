<?php

namespace Linguise\Script\Core;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class CurlMulti
{
    /**
     * @var null|Request
     */
    private static $_instance = null;

    /**
     * @var array Array of all requests class instances to run in multi
     */
    protected $_request_instances = [];

    /**
     * @var resource
     */
    protected $_curl_multi;

    /**
     * Retrieve singleton instance
     *
     * @return Request|null
     */
    public static function getInstance() {

        if(is_null(self::$_instance)) {
            self::$_instance = new CurlMulti();
        }

        return self::$_instance;
    }

    /**
     * Add a request to execute to the list
     *
     * @param $instance
     * @return void
     */
    public function addRequest($instance)
    {
        $this->_request_instances[] = $instance;
    }

    /**
     * Execute a curl multi request
     *
     * @return void
     */
    public function executeRequests()
    {
        if (!count($this->_request_instances)) {
            return;
        }

        $at_least_one_request = false;
        $this->_curl_multi = curl_multi_init();
        foreach ($this->_request_instances as $instance) {
            $ch = $this->prepareRequest($instance);
            if (!$ch) {
                continue;
            }
            $at_least_one_request = true;
            curl_multi_add_handle($this->_curl_multi, $ch);
        }

        if (!$at_least_one_request) {
            return;
        }

        curl_multi_exec($this->_curl_multi, $still_running);
    }

    /**
     * Wait for the request to finish
     * @return void
     */
    public function waitRequests()
    {
        if (!$this->_curl_multi) {
            return;
        }

        do {
            $status = curl_multi_exec($this->_curl_multi, $active);
            if ($active) {
                // Wait a short time for more activity
                curl_multi_select($this->_curl_multi);
            }
        } while ($active && $status === CURLM_OK);
    }

    /**
     * Prepare the request and return the curl resource
     *
     * @param $instance
     * @return false|resource
     */
    public function prepareRequest($instance) {
        if (!$instance->shouldBeExecuted()) {
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_NOBODY, true); // SET HEAD type
        curl_setopt($ch, CURLOPT_URL, Request::getInstance()->getBaseUrl() . '/zz-zz/?linguise_action=' . $instance->_action);
        if (Configuration::getInstance()->get('server_ip') !== null) {
            curl_setopt($ch, CURLOPT_CONNECT_TO, [Request::getInstance()->getHostname() . ':' . Configuration::getInstance()->get('server_port') . ':' . Configuration::getInstance()->get('server_ip') . ':' . Configuration::getInstance()->get('server_port')]); // fixme: only available from php 7.0
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        if (Configuration::getInstance()->get('dl_certificates') === true) {
            curl_setopt($ch, CURLOPT_CAINFO, Certificates::getInstance()->getPath());
        }

        return $ch;
    }

}