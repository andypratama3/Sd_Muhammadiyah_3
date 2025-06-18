<?php

namespace Linguise\Script\Core\Databases;

use Linguise\Script\Core\Configuration;
use Linguise\Script\Core\Request;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class Sqlite
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
            self::$_instance = new Sqlite();
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
    public function connect()
    {
        $database_exists = true;
        $database_path = Configuration::getInstance()->get('data_dir') . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'linguise.sqlite';
        if (!file_exists($database_path)) {
            $database_exists = false;
        }
        $this->_database = new \SQLite3($database_path);
        $this->_database_table_urls = 'urls';

        //$this->_database->set_charset("utf8");

        //$existing_tables = array();

        if (!$database_exists) {
            $this->_database->exec('
                    CREATE TABLE '. $this->_database_table_urls.' (
                      `id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                      `language` STRING NOT NULL,
                      `source` BINARY NOT NULL,
                      `translation` BINARY NOT NULL,
                      `hash_source` STRING NOT NULL,
                      `hash_translation` STRING NOT NULL
                    );
                ');
            $this->_database->exec('CREATE UNIQUE INDEX source ON '. $this->_database_table_urls.' (hash_source, language)');
            $this->_database->exec('CREATE UNIQUE INDEX translation ON '. $this->_database_table_urls.' (hash_translation, language)');
            // todo: store creation date
            // todo: number of url usage
        }

        $this->_database->busyTimeout(3000);
        $this->_database->exec('PRAGMA journal_mode = wal;');

        return true;
    }

    public function getSourceUrl($url) {
        $smt = $this->_database->prepare('SELECT * from ' . $this->_database_table_urls . ' WHERE hash_translation=:hash_translation AND language=:language LIMIT 0, 1');
        if ($smt === false) {
            return false;
        }
        $smt->bindValue(':hash_translation', md5($url), SQLITE3_TEXT);
        $smt->bindValue(':language', Request::getInstance()->getLanguage(), SQLITE3_TEXT);

        $result = $smt->execute()->fetchArray();
        
        // Ensure that $result is of array type
        if (empty($result) || !is_array($result)) {
            return false;
        }

        return $result['source'];
    }

    public function getTranslatedUrl($url) {
        $smt = $this->_database->prepare('SELECT * from ' . $this->_database_table_urls . ' WHERE hash_source=:hash_source AND language=:language LIMIT 0, 1');
        $smt->bindValue(':hash_source', md5($url), SQLITE3_TEXT);
        $smt->bindValue(':language', Request::getInstance()->getLanguage(), SQLITE3_TEXT);

        try {
            $result = $smt->execute()->fetchArray();
        } catch (\Exception $e) {
            return false;
        }

        // Ensure that $result is of array type
        if (empty($result) || !is_array($result)) {
            return false;
        }

        return $result[0]['translation'];
    }

    public function saveUrls($urls)
    {
        $query = 'INSERT OR REPLACE INTO ' . $this->_database_table_urls . ' (language, source, translation, hash_source, hash_translation) VALUES ';

        foreach ($urls as $translation => $source) {
            $smt = $this->_database->prepare($query . '(:language, :source, :translation, :hash_source, :hash_translation)');
            $smt->bindValue(':language', Request::getInstance()->getLanguage(), SQLITE3_TEXT);
            $smt->bindValue(':source', $source, SQLITE3_BLOB);
            $smt->bindValue(':translation', $translation, SQLITE3_BLOB);
            $smt->bindValue(':hash_source', md5($source), SQLITE3_TEXT);
            $smt->bindValue(':hash_translation', md5($translation), SQLITE3_TEXT);

            try {
                $smt->execute();
            } catch (\Exception $e) {}
        }
    }


    public function removeUrls($urls)
    {
        $query = 'DELETE FROM ' . $this->_database_table_urls . ' WHERE (hash_source) IN ';

        $elements = array();
        foreach ($urls as $source) {
            $elements[] = '"'.md5($source).'"';
        }
        $query .= '(' . implode(',', $elements) . ') ';

        $smt = $this->_database->prepare($query . ' AND language=:language');
        $smt->bindValue(':language', Request::getInstance()->getLanguage(), SQLITE3_TEXT);
        try {
            $smt->execute();
        } catch (\Exception $e) {}
    }

    public function close() {
        $this->_database->close();
    }
}
