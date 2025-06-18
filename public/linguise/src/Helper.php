<?php
namespace Linguise\Script\Core;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class Helper {

    public static function getIpAddress()
    {
        if (isset($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = (string)trim(current(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])));
            if ($ip) {
                return $ip;
            }
        }

        if (isset( $_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        return '';
    }

    public static function getClassStaticVars($classname)
    {
        $class = new \ReflectionClass($classname);
        return $class->getStaticProperties();
    }

    public static function prepareDataDir()
    {
        if (Configuration::getInstance()->get('data_dir') === null) {
            $data_folder = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . md5('data' . Configuration::getInstance()->get('token'));
            if (!file_exists($data_folder)) {
                mkdir($data_folder);
                mkdir($data_folder . DIRECTORY_SEPARATOR . 'database');
                mkdir($data_folder . DIRECTORY_SEPARATOR . 'cache');
                mkdir($data_folder . DIRECTORY_SEPARATOR . 'tmp');
                file_put_contents($data_folder . DIRECTORY_SEPARATOR . '.htaccess', 'deny from all');
            }
            Configuration::getInstance()->set('data_dir', $data_folder);
        }
    }

    /**
     * Convert a query string to an array
     * 
     * @param string $query
     * @return array
     */
    public static function queryStringToArray($query)
    {
        if (empty($query)) {
            return [];
        }
        $name_values = explode('&', $query);
        $query_params = [];
        foreach ($name_values as $name_value) {
            $values = explode('=', $name_value);
            if (empty($values[1])) {
                $query_params[$values[0]] = '';
            } else {
                $query_params[$values[0]] = rawurldecode($values[1]);
            }
        }
        return $query_params;
    }

    /**
     * Convert an array to a query string
     * 
     * @param array $query
     * @return string
     */
    public static function arrayToQueryString($query)
    {
        if (empty($query)) {
            return '';
        }

        $new_queries = [];
        foreach ($query as $name => $value) {
            $new_queries[] = $name . '=' . rawurlencode($value);
        }

        return implode('&', $new_queries);
    }

    /**
     * Create a new URL based on the parsed_url output
     * @param array $parsed_url
     * @return string
     */
    public static function buildUrl($parsed_url)
    {

        $final_url = '';
        if (empty($parsed_url['scheme'])) {
            $final_url .= '//';
        } else {
            $final_url .= $parsed_url['scheme'] . '://';
        }

        if (!empty($parsed_url['user'])) {
            $final_url .= $parsed_url['user'];
            if (!empty($parsed_url['pass'])) {
                $final_url .= ':' . $parsed_url['pass'];
            }
            $final_url .= '@';
        }

        $final_url .= empty($parsed_url['host']) ? '' : $parsed_url['host'];

        if (!empty($parsed_url['port'])) {
            $final_url .= ':' . $parsed_url['port'];
        }

        if (!empty($parsed_url['path'])) {
            $final_url .= $parsed_url['path'];
        }

        if (!empty($parsed_url['query'])) {
            $final_url .= '?' . $parsed_url['query'];
        }

        if (!empty($parsed_url['fragment'])) {
            $final_url .= '#' . $parsed_url['fragment'];
        }

        return $final_url;
    }
}