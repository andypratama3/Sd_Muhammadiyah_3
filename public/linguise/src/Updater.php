<?php

namespace Linguise\Script\Core;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class Updater
{
    /**
     * @var null|Updater
     */
    private static $_instance = null;

    private function __construct()
    {

    }

    /**
     * Retrieve singleton instance
     *
     * @return Updater|null
     */
    public static function getInstance() {

        if(is_null(self::$_instance)) {
            self::$_instance = new Updater();
        }

        return self::$_instance;
    }

    public function update()
    {
        Debug::log('Start updating');

        if (!class_exists('\ZipArchive', false )) {
            Debug::log('Php zip extension missing');
            die('Update failed');
        }

        @set_time_limit(0);
        ignore_user_abort(true);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Configuration::getInstance()->get('update_url'));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (strpos('https', Configuration::getInstance()->get('update_url')) === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            if (Configuration::getInstance()->get('dl_certificates') === true) {
                curl_setopt($ch, CURLOPT_CAINFO, Certificates::getInstance()->getPath());
            }
        }
        $content = curl_exec($ch);
        curl_close($ch);

        if (!$content) {
            Debug::log('Failed load update information');
            die('Update failed');
        }

        $result = json_decode($content);
        if (!$result) {
            Debug::log('Failed decode update information');
            die('Update failed');
        }

        if (version_compare($result->version, LINGUISE_SCRIPT_TRANSLATION_VERSION, '<=')) {
            die('No update available');
        }

        $tmp_folder = Configuration::getInstance()->get('data_dir') . DIRECTORY_SEPARATOR . 'tmp';
        if (!file_exists($tmp_folder)) {
            mkdir($tmp_folder);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $result->location);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (strpos('https', $result->location) === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            if (Configuration::getInstance()->get('dl_certificates') === true) {
                curl_setopt($ch, CURLOPT_CAINFO, Certificates::getInstance()->getPath());
            }
        }
        $file_content = curl_exec($ch);
        curl_close($ch);

        if (!$file_content) {
            Debug::log('Failed load update information');
            die('Update failed');
        }

        $update_file = $tmp_folder . DIRECTORY_SEPARATOR . 'update.zip';
        file_put_contents($update_file, $file_content);
        $md5_sum = md5_file($update_file);

        if ($md5_sum !== $result->md5) {
            Debug::log('File verification failed');
            unlink($update_file);
            die('Update failed');
        }

        $zip = new \ZipArchive();
        if ($zip->open($update_file) !== true) {
            Debug::log('File extraction failed');
            unlink($update_file);
            die('Update failed');
        }

        $zip_folder = $tmp_folder . DIRECTORY_SEPARATOR . 'update';
        if (!$zip->extractTo($zip_folder)) {
            Debug::log('File extraction failed');
            unlink($update_file);
            die('Update failed');
        }
        $zip->close();

        unlink($update_file);

        $base_folder = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
        $backup_folder = $base_folder . uniqid('_update_');

        $data_dir_parts = explode(DIRECTORY_SEPARATOR, Configuration::getInstance()->get('data_dir'));
        $data_folder_name = $data_dir_parts[count($data_dir_parts) - 1];

        if (!rename($base_folder, $backup_folder)) {
            Debug::log('File extraction failed');
            $this->rmdir($zip_folder);
            die('Update failed');
        }

        if (!rename($backup_folder . DIRECTORY_SEPARATOR . $data_folder_name . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'update' . DIRECTORY_SEPARATOR . 'linguise' , $base_folder)) {
            Debug::log('Folder rename failed');
            $this->rmdir($zip_folder);
            die('Update failed');
        }

        if (!rename($backup_folder . DIRECTORY_SEPARATOR . 'Configuration.php', $base_folder . DIRECTORY_SEPARATOR . 'Configuration.php')) {
            Debug::log('Failed moving configuration file');
            $this->rmdir($base_folder);
            rename($backup_folder, $base_folder);
            die('Update failed');
        }

        if (!rename($backup_folder . DIRECTORY_SEPARATOR . $data_folder_name, $base_folder . DIRECTORY_SEPARATOR . $data_folder_name)) {
            Debug::log('Failed moving data directory');
            $this->rmdir($base_folder);
            rename($backup_folder, $base_folder);
            die('Update failed');
        }

        if (class_exists('Linguise\Core\AfterUpdate') && method_exists('Linguise\Core\AfterUpdate', 'afterUpdateRun')) {
            AfterUpdate::afterUpdateRun($base_folder);
        }

        $this->rmdir($backup_folder);

        Debug::log('Update done');
        die('Update succeed');
    }

    protected function rmdir($directory) {
        $files = array_diff(scandir($directory), array('.','..'));
        foreach ($files as $file) {
            (is_dir($directory . DIRECTORY_SEPARATOR . $file)) ? $this->rmdir($directory . DIRECTORY_SEPARATOR . $file) : unlink($directory . DIRECTORY_SEPARATOR . $file);
        }
        return rmdir($directory);
    }
}