<?php

namespace Linguise\Script\Core;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class Debug
{
    protected static $enabled = false;

    protected static $verbosity = 0;

    protected static $ip = '';

    protected static $error_file;

    public static function enable($verbosity = 0, $ip = '')
    {
        self::$error_file = dirname(__FILE__) . '/../debug.php';

        if (!file_exists(self::$error_file)) {
            file_put_contents(self::$error_file, '<?php die(); ?>' . PHP_EOL);
        }

        self::$verbosity = (int)$verbosity;
        self::$ip = $ip;

        ini_set('error_log', self::$error_file);
        self::$enabled = true;
    }

    public static function disable()
    {
        self::$enabled = false;
    }

    public static function log($message, $verbosity = 0)
    {
        if (self::$enabled === false || $verbosity > self::$verbosity) {
            return;
        }

        if (!is_writable(self::$error_file)) {
            return;
        }

        if (self::$ip && self::$ip !== ($_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'])) {
            return;
        }

        error_log($message . PHP_EOL, 3, self::$error_file);
    }

    public static function timing($message, $start, $end = null, $verbosity = 0)
    {
        if (self::$enabled === false) {
            return;
        }

        if ($end === null) {
            $end = microtime(true);
        }

        $total = $end - $start;

        if ($total < 0.0001) {
            $total = number_format($total * 1000000, 3) . 'μs';
        } elseif ($total < 1) {
            $total = number_format($total * 1000, 3) . 'ms';
        } else {
            $total = number_format($total, 3) . 's';
        }

        $message = str_replace('%s', $total, $message);

        self::log($message, $verbosity);

    }

    /**
     * Save the latest 20 error in a file
     * This file can be use to display the latest errors to end user
     *
     * @return void
     */
    public static function saveError($error)
    {
        $file = dirname(__FILE__) . '/../errors.php';

        // Retrieve content if it exists
        if (file_exists($file)) {
            $content = file_get_contents($file);
        } else {
            $content = "<?php die(); ?>";
        }

        // Format the error with date
        $error = '[' . date('Y-m-d H:i:s') . '] ' . $error;

        // Get the existing errors in an array
        $contentArray = explode("\n", $content);

        // Add the latest error on top
        array_splice( $contentArray, 1, 0, $error);

        // Keep only the latest 20 errors
        $contentArray = array_slice($contentArray, 0, 21);

        // Save the errors back
        file_put_contents($file, implode("\n", $contentArray));
    }
}