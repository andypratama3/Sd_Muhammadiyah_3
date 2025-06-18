<?php

namespace Linguise\Script\Core;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class Cache
{
    /**
     * @var null|Request
     */
    private static $_instance = null;

    /**
     * Hash of the original content retrieved from untranslated page
     *
     * @var null|string
     */
    private $_hash = null;

    /**
     * @var null|string
     */
    private $_content = null;

    /**
     * @var null|string
     */
    private $_language = null;

    /**
     * @var string The action parameter to use in request
     */
    public $_action = 'clear-cache';

    /**
     * Retrieve singleton instance
     *
     * @return Request|null
     */
    public static function getInstance() {

        if(is_null(self::$_instance)) {
            self::$_instance = new Cache();
        }

        return self::$_instance;
    }

    public function getPath() {
        Helper::prepareDataDir();
        return Configuration::getInstance()->get('data_dir') . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
    }

    public function serve() {
        $response = Response::getInstance();

        $content = $response->getContent();

        if (!$content) {
            return false;
        }

        $this->_hash = md5(json_encode(array(
            'content' => extension_loaded('mbstring') ? mb_convert_encoding($content, 'UTF-8', 'UTF-8') : $content,
            'url' => Request::getInstance()->getRequestedUrl()
        )));

        // In case we failed to json_encode (non utf8 chars and no mbstring extension)
        if (!$this->_hash) {
            return false;
        }

        $language = Request::getInstance()->getLanguage();

        if (!preg_match('/^[a-z]{2,3}(?:-[a-z]{2})?$/', $language)) {
            // Some characters are not allowed skip
            return false;
        }

        $this->_language = $language;

        if (!$this->load()) {
            return false;
        }

        $response->setContent($this->_content);

        $response->end();
    }

    protected function load() {
        $cache_file = Configuration::getInstance()->get('data_dir') . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $this->_language . '_' . $this->_hash . '.php';

        if (!file_exists($cache_file)) {
            return false;
        }

        $content = file_get_contents($cache_file);

        // Update cache file modified time
        touch($cache_file);

        // Remove php head
        $this->_content = substr($content, 15);

        return true;
    }

    public function save() {
        if (!$this->_hash || !$this->_language) {
            return false;
        }

        $response = Response::getInstance();

        $content = $response->getContent();

        if (!$content) {
            return false;
        }

        $cache_path = $this->getPath();
        $cache_file = $cache_path . $this->_language . '_' . $this->_hash . '.php';

        if (!file_exists($cache_path)) {
            mkdir($cache_path);
        }

        file_put_contents($cache_file, '<?php die(); ?>' . $content);

        return true;
    }

    /**
     * Check if the request to launch this task should be executed or not
     *
     * @return bool
     */
    public function shouldBeExecuted() {
        if (!Configuration::getInstance()->get('cache_enabled')) {
            return false;
        }
        $cache_info_file = $this->getPath() . 'clear.txt';
        if (file_exists($cache_info_file) && (int)file_get_contents($cache_info_file) + Configuration::getInstance()->get('cache_time_check') > time()) {
            return false;
        }
        return true;
    }

    public function clear() {
        if (!$this->shouldBeExecuted()) {
            return;
        }

        $cache_path = $this->getPath();

        $files = glob($cache_path . '*.php');

        usort($files, function($x, $y) {
            return (filemtime($x) < filemtime($y)) ? -1 : 1;
        });

        $max_size = Configuration::getInstance()->get('cache_max_size') * 1024 * 1024;
        $total_size = 0;
        $total_cleared = 0;
        foreach($files as $file) {
            if ($total_size > $max_size) {
                unlink($file);
                continue;
            }
            $size = filesize($file);
            $total_cleared += $size;
            $total_size += $size;
        }

        file_put_contents($cache_path . 'clear.txt', time());

        $response = Response::getInstance();
        $response->setResponseCode(200, false);
        $response->setContent('Cleared cache: ' . (int)($total_cleared/1000) . 'kb');
        $response->end();
    }

    public function clearAll()
    {
        $total_cleared = 0;
        $cache_path = $this->getPath();
        $files = glob($cache_path . '*.php');

        foreach($files as $file) {
            if (!in_array($file, ['.', '..'])) {
                $total_cleared += filesize($file);
                unlink($file);
            }
        }

        $response = Response::getInstance();
        if ($total_cleared > 0) {
            $response->setContent('Cleared cache: ' . (int)($total_cleared/1000) . 'kb');
        } else {
            $response->setContent('Cache Empty!');
        }
        $response->setResponseCode(200, false);
        $response->end();
    }
}
