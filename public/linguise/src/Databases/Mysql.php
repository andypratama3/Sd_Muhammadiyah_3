<?php

namespace Linguise\Script\Core\Databases;

use Linguise\Script\Core\Request;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class Mysql
{
    /**
     * @var null|Mysql
     */
    private static $_instance = null;

    /**
     * @var string Urls table name
     */
    protected $_database_table_urls;

    /**
     * @var string Prefix for the tables
     */
    protected $_dbprefix;

    /**
     * @var null
     */
    protected $_database;

    /**
     * Retrieve singleton instance
     *
     * @return Mysql|null
     */
    public static function getInstance() {

        if(is_null(self::$_instance)) {
            self::$_instance = new Mysql();
        }

        return self::$_instance;
    }

    private function __construct()
    {
    }

    /**
     * Connect to the mysql database
     *
     * @param $config Array configuration
     *
     * @return bool
     */
    public function connect($config)
    {
        $port = !empty($config->db_port) ? $config->db_port : 3306;
        $host = $config->host;

        $parts = explode(':', $host, 2);

        if (count($parts) === 2) {
            $host = $parts[0];
            $port = $parts[1];
        }

        $database = mysqli_init();
        if (!$database) {
            return false;
        }

        mysqli_real_connect($database, $host, $config->user, $config->password, $config->db, $port, null, $config->flags);
        if ($database->connect_errno) {
            return false;
        }

        $this->_database = $database;
        $this->_dbprefix = $config->dbprefix;
        $this->_database_table_urls = $config->dbprefix.'linguise_urls';

        $this->_database->set_charset("utf8mb4");

        $existing_tables = array();
        $results = $database->query("SHOW TABLES LIKE '".mysqli_real_escape_string($database, $config->dbprefix.'linguise_%')."'");
        while($table = $results->fetch_array()) {
            $existing_tables[] = $table[0];
        }

        if (!in_array($this->_database_table_urls, $existing_tables)) {
            $install_query = $this->getInstallQuery(mysqli_real_escape_string($database, $this->_database_table_urls));
            $database->query($install_query);
        }

        return true;
    }

    public function getInstallQuery($table_name)
    {
        return 'CREATE TABLE IF NOT EXISTS '. $table_name .' (
                  `id` BIGINT NOT NULL AUTO_INCREMENT,
                  `language` varchar(5) NOT NULL,
                  `source` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                  `translation` varchar(2000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                  `hash_source` varchar(32) NOT NULL,
                  `hash_translation` varchar(32) NOT NULL,
                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (id),
                  UNIQUE INDEX (hash_source, language),
                  UNIQUE INDEX (hash_translation, language)
                );';
        // todo: number of url usage
    }

    public function getSourceUrl($url) {
        $result = $this->_database->query("SELECT * from ".mysqli_real_escape_string($this->_database, $this->_database_table_urls)." WHERE hash_translation='".md5($url)."' AND language='".mysqli_real_escape_string($this->_database, Request::getInstance()->getLanguage())."' LIMIT 0, 1");

        if (!$result->num_rows) {
            return false;
        }

        $url = $result->fetch_object();

        return $url->source;
    }

    public function getTranslatedUrl($url) {
        $result = $this->_database->query("SELECT * from ".mysqli_real_escape_string($this->_database, $this->_database_table_urls)." WHERE hash_source='".md5($url)."' AND language='".mysqli_real_escape_string($this->_database, Request::getInstance()->getLanguage())."' LIMIT 0, 1");
        if (!$result->num_rows) {
            return false;
        }

        $url = $result->fetch_object();

        return $url->translation;
    }

    public function saveUrls($urls)
    {
        $query = 'INSERT INTO '.mysqli_real_escape_string($this->_database, $this->_database_table_urls).' (language, source, translation, hash_source, hash_translation) VALUES ';
        $elements = array();
        foreach ($urls as $translation => $source) {
            $elements[] = '("'.mysqli_real_escape_string($this->_database, Request::getInstance()->getLanguage()).'", "'.mysqli_real_escape_string($this->_database, $source).'", "'.mysqli_real_escape_string($this->_database, $translation).'", "'.md5($source).'", "'.md5($translation).'")';
        }
        $query .= implode(',', $elements);
        $query .= ' ON DUPLICATE KEY UPDATE source=VALUES(source), translation=VALUES(translation), hash_source=VALUES(hash_source), hash_translation=VALUES(hash_translation)';
        $this->_database->query($query);
    }

    public function removeUrls($urls)
    {
        $query = 'DELETE FROM '.mysqli_real_escape_string($this->_database, $this->_database_table_urls).' WHERE (hash_source) IN ';
        $elements = array();
        foreach ($urls as $source) {
            $elements[] = '"'.mysqli_real_escape_string($this->_database, md5($source)).'"';
        }
        $query .= '(' . implode(',', $elements) . ') AND language="'.mysqli_real_escape_string($this->_database, Request::getInstance()->getLanguage()).'"';
        $this->_database->query($query);
    }

    public function retrieveWordpressOption($option_name) {
        $result = $this->_database->query('SELECT option_value from '.mysqli_real_escape_string($this->_database, $this->_dbprefix . 'options').' WHERE option_name="linguise_options" LIMIT 0, 1');

        if (!$result->num_rows) {
            return false;
        }

        $options = $result->fetch_object();
        $options = unserialize($options->option_value);

        if (empty($options[$option_name])) {
            return false;
        }

        return $options[$option_name];
    }

    public function retrieveWordpressMultisiteOption($option_name, $host) {
        $result = $this->_database->query('SELECT blog_id from '.mysqli_real_escape_string($this->_database, $this->_dbprefix . 'blogs').' WHERE domain="' . mysqli_real_escape_string($this->_database, $host) . '" LIMIT 0, 1');

        if (!$result->num_rows) {
            return false;
        }

        $site = $result->fetch_object();

        if (empty($site)) {
            return false;
        }

        $result = $this->_database->query('SELECT option_value from '.mysqli_real_escape_string($this->_database, $this->_dbprefix . (int)$site->blog_id . '_options').' WHERE option_name="linguise_options" LIMIT 0, 1');

        if (!$result->num_rows) {
            return false;
        }

        $options = $result->fetch_object();
        $options = unserialize($options->option_value);

        if (empty($options[$option_name])) {
            return false;
        }

        return $options[$option_name];
    }

    public function retrieveJoomlaParam($param_name) {
        $result = $this->_database->query('SELECT params from '.mysqli_real_escape_string($this->_database, $this->_dbprefix . 'extensions').' WHERE type="plugin" AND name="linguise" AND folder="system" LIMIT 0, 1');

        if (!$result->num_rows) {
            return false;
        }

        $params = $result->fetch_object();
        $params = json_decode($params->params);

        if (empty($params->$param_name)) {
            return false;
        }

        return $params->$param_name;
    }

    public function close() {
    }
}
