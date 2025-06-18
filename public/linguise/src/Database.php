<?php

namespace Linguise\Script\Core;

use Linguise\Script\Core\Databases\Mysql;
use Linguise\Script\Core\Databases\Sqlite;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class Database
{
    /**
     * @var null|Database
     */
    private static $_instance = null;

    /**
     * @var null|Mysql
     */
    protected $_database;

    private $_configuration = null;

    private function __construct()
    {
        $cms = CmsDetect::detect();

        if ($cms === 'joomla') {
            $this->_configuration = $this->retrieveJoomlaConfiguration();
            $this->_database = Mysql::getInstance();
            $connection_result = $this->_database->connect($this->_configuration);
        } elseif ($cms === 'wordpress') {
            $this->_configuration = $this->retrieveWordPressConfiguration();
            $this->_database = Mysql::getInstance();
            $connection_result = $this->_database->connect($this->_configuration);
        } elseif (Configuration::getInstance()->get('db_host')) {
            $this->_database = Mysql::getInstance();
            $this->_configuration = $this->retrieveMysqlConfiguration();
            $connection_result = $this->_database->connect($this->_configuration);
        } else {
            $this->_database = Sqlite::getInstance();
            $connection_result = $this->_database->connect();
        }

        if (!$connection_result) {
            //fixme: redirect to non translated page
        }
    }

    /**
     * Retrieve singleton instance
     *
     * @return Database|null
     */
    public static function getInstance() {

        if(is_null(self::$_instance)) {
            self::$_instance = new Database();
        }

        return self::$_instance;
    }

    /**
     * Retrieve Joomla database credentials and tries to connect
     *
     * @return bool
     */
    protected function retrieveJoomlaConfiguration()
    {
        $configuration_file = Configuration::getInstance()->get('base_dir') . DIRECTORY_SEPARATOR . 'configuration.php';
        if (!file_exists($configuration_file)) {
            return false;
        }

        include_once($configuration_file);
        if (!class_exists('JConfig')) {
            return false;
        }

        $jconf = new \JConfig();

        $config = new \stdClass();
        $config->db = $jconf->db;
        $config->user = $jconf->user;
        $config->password = $jconf->password;
        // Host include port
        $config->host = $jconf->host;
        $config->dbprefix = $jconf->dbprefix;
        // Make flags depending on ssl config
        $config->flags = 0;

        if (isset($jconf->dbencryption) && !empty($jconf->dbencryption)) {
            $config->flags = MYSQLI_CLIENT_SSL;
            if (isset($jconf->dbsslverifyservercert) && !empty($jconf->dbsslverifyservercert)) {
                // Don't verify server certificate
                $config->flags |= MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
            }
        }

        return $config;
    }

    /**
     * Retrieve Wordpress database credentials and tries to connect
     *
     * @return bool|\stdClass
     */
    protected function retrieveWordPressConfiguration()
    {
        $config = new \stdClass();

        // Wordpress use MYSQL_CLIENT_FLAGS
        $db_flags = defined('MYSQL_CLIENT_FLAGS') ? MYSQL_CLIENT_FLAGS : 0;
        $config->flags = $db_flags;

        global $wpdb;
        if (!empty($wpdb) && !empty($wpdb->db_version())) {
            // We have already mysql connected

            $config->db = $wpdb->__get('dbname');
            $config->user = $wpdb->__get('dbuser');
            $config->password = $wpdb->__get('dbpassword');
            $config->host = $wpdb->__get('dbhost');
            $config->dbprefix = $wpdb->base_prefix;
            $config->multisite = is_multisite();
            if (defined('DOMAIN_CURRENT_SITE')) {
                $config->domain_current_site = DOMAIN_CURRENT_SITE;
            }

        } else {
            // Fallback to loading configuration file
            $configuration_file = Configuration::getInstance()->get('base_dir') . DIRECTORY_SEPARATOR . 'wp-config.php';
            if (!file_exists($configuration_file)) {
                return false;
            }

            $config_content = file_get_contents($configuration_file);

            preg_match_all('/define\( *[\'"](.*.)[\'"] *, *(?:[\'"](.*?)[\'"]|([0-9]+)|(true)|(TRUE)) *\)/m', $config_content, $matches, PREG_SET_ORDER, 0);

            foreach ($matches as $config_line) {
                switch ($config_line[1]) {
                    case 'DB_NAME':
                        $config->db = $config_line[2];
                        break;
                    case 'DB_USER':
                        $config->user = $config_line[2];
                        break;
                    case 'DB_PASSWORD':
                        $config->password = $config_line[2];
                        break;
                    case 'DB_HOST':
                        $config->host = $config_line[2];
                        break;
                    case 'MULTISITE':
                        if ((!empty($config_line[3]) && (int)$config_line[3] > 0) || empty($config_line[4]) || empty($config_line[5])) {
                            $config->multisite = true;
                        } else {
                            $config->multisite = false;
                        }
                        break;
                    case 'DOMAIN_CURRENT_SITE':
                        $config->domain_current_site = $config_line[2];
                        break;
                }
            }

            preg_match('/\$table_prefix *= *[\'"](.*?)[\'"]/', $config_content, $matches);
            $config->dbprefix = $matches[1];
        }

        return $config;
    }

    /**
     * Retrieve credentials from Configuration.php
     *
     * @return bool|\stdClass
     */
    protected function retrieveMysqlConfiguration()
    {
        $config = new \stdClass();
        $config->db = Configuration::getInstance()->get('db_name');
        $config->user = Configuration::getInstance()->get('db_user');
        $config->password = Configuration::getInstance()->get('db_password');
        $config->host = Configuration::getInstance()->get('db_host');
        $config->dbprefix = Configuration::getInstance()->get('db_prefix');
        $config->dbport = Configuration::getInstance()->get('db_port');
        $config->flags = Configuration::getInstance()->get('db_flags');

        return $config;
    }

    public function getSourceUrl($url) {
        return $this->_database->getSourceUrl($url);
    }

    public function getTranslatedUrl($url) {
        return $this->_database->getTranslatedUrl($url);
    }

    public function saveUrls($urls) {
        if (empty($urls)) {
            return;
        }
        return $this->_database->saveUrls($urls);
    }

    public function removeUrls($urls) {
        if (empty($urls)) {
            return;
        }
        return $this->_database->removeUrls($urls);
    }

    public function retrieveWordpressOption($option_name, $host = null) {
        if (function_exists('get_option')) {
            $options = get_option('linguise_options');

            if (empty($options[$option_name])) {
                return false;
            }

            return $options[$option_name];
        }

        if (!empty($this->_configuration->multisite) && $host !== $this->_configuration->domain_current_site) {
            return $this->_database->retrieveWordpressMultisiteOption($option_name, $host);
        } else {
            return $this->_database->retrieveWordpressOption($option_name);
        }
    }

    public function retrieveJoomlaParam($option_name) {
        return $this->_database->retrieveJoomlaParam($option_name);
    }

    public function close() {
        $this->_database->close();
    }
}
