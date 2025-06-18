<?php
namespace Linguise\Script\Core;

use ReflectionProperty;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class Configuration {
    /**
     * @var null|Configuration
     */
    private static $_instance = null;

    /** Mandatory configuration **/
    /** The token used to authenticate with Linguise */
    private $token = '';

    /** Basic configuration **/
    /** Enable or disable the cache */
    private $cache_enabled = true;
    /** Maximum size of the cache (in megabyte) */
    private $cache_max_size = 200; // In megabyte
    /** TTL of the cache (in seconds) */
    private $cache_time_check = 600; // In seconds
    /** Enable search translations */
    private $search_translations = true;

    /** Advanced configuration **/
    /** The CMS used by the website */
    private $cms = '';
    /** The server IP */
    private $server_ip = null;
    /** The server port */
    private $server_port = null;
    /** Enable debug */
    private $debug = false;
    /** Debug IP */
    private $debug_ip = '';
    /** The data directory of the website */
    private $data_dir = null;
    /** The base directory of the website */
    private $base_dir = null;
    /** Download certificates */
    private $dl_certificates = false;

    /** Advanced database configuration **/
    /** The database host */
    private $db_host = '';
    /** The database user */
    private $db_user = '';
    /** The database password */
    private $db_password = '';
    /** The database name */
    private $db_name = '';
    /** The database prefix */
    private $db_prefix = '';
    /** The database port */
    private $db_port = '3306';
    /** The database flags */
    private $db_flags = 0;

    /** Development configuration */
    /** The translation server port */
    private $port = 443;
    /** The translation server host */
    private $host = 'translate.linguise.com';
    /** The PHP script update URL */
    private $update_url = 'https://www.linguise.com/php_script_update.json';

    private function __construct()
    {
    }

    /**
     * Retrieve singleton instance
     *
     * @return Configuration|null
     */
    public static function getInstance() {

        if(is_null(self::$_instance)) {
            self::$_instance = new Configuration();
        }

        return self::$_instance;
    }

    public function loadFile($configurationFile, $localConfiguration = false) {
        if (!file_exists($configurationFile)) {
            return false;
        }

        require_once($configurationFile);
        if ($localConfiguration) {
            // We override the WordPress Configuration with a local configuration
            $class = \Linguise\Script\ConfigurationLocal::class;
        } else {
            // We use the default configuration
            $class = \Linguise\Script\Configuration::class;
        }

        foreach (get_class_vars($class) as $attribute_name => $attribute_value) {
            Configuration::getInstance()->set($attribute_name, $attribute_value);
        }
        foreach (get_class_methods($class) as $hook) {
            if (strpos($hook, 'on') !== 0) {
                continue;
            }
            Hook::add($hook, $class);
        }

        return true;
    }

    public function load($basePath) {
        $configurationLocalLoaded = $this->loadFile($basePath . DIRECTORY_SEPARATOR . 'ConfigurationLocal.php', true);
        if (!$configurationLocalLoaded) {
            $this->load($basePath . DIRECTORY_SEPARATOR . 'Configuration.php');
        }
    }

    public function get($property) {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return null;
    }

    public function set($property, $value) {
        if ($property === '_instance') {
            return $this;
        }
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }

        return $this;
    }

    /**
     * Convert the object to an array which contains
     * all the properties of the object
     * 
     * Each array will have the following key value pair:
     * - key: the name of the property
     * - value: the value of the property
     * - doc: the doc of the property
     * @return array
     */
    public function toArray() {
        $config = get_object_vars($this);
        $config_with_docs = [];
        foreach ($config as $key => $value) {
            $prop = new ReflectionProperty($this, $key);

            $doc_data = null;
            $temp_doc = $prop->getDocComment();
            if ($temp_doc) {
                // clean doc
                $doc_data = substr($temp_doc, 3, -2);
                $doc_data = trim($doc_data);
            }

            $config_with_docs[$key] = [
                'key' => $key,
                'value' => $value,
                'doc' => $doc_data,
            ];
        }

        return $config_with_docs;
    }
}