<?php

namespace Linguise\Script\Core\Platforms;

use Linguise\Script\Core\Helper;
use Linguise\Script\Core\Request;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class OpenCart
{
    /**
     * Get the replacement regex for the OpenCart platform URL
     */
    public static function getOpenCartReplacement()
    {
        $request = Request::getInstance();

        $base_dir = $request->getBaseUrl();
        $language_dir = $request->getBaseUrl();
        // Endswith /
        if (substr($base_dir, -1) === '/') {
            // Trim the last /
            $base_dir = substr($base_dir, 0, -1);
        }
        if (substr($language_dir, -1) === '/') {
            // Trim the last /
            $language_dir = substr($language_dir, 0, -1);
        }

        $base_dir = preg_quote($base_dir, '/');
        $base_dir .= '\/?([\w\.\-]+\/?|\?.*)(.*)';
        $language = $request->getLanguage();
        $language_dir = $language_dir . '/' . $language . '/$1$2';

        return [$base_dir, $language_dir];
    }

    /**
     * Replace the search params in the HTML content based on the given $matcher and $replacer_callback
     *
     * @param string $matcher The regex pattern to match for the search param in the HTML content
     * @param callable $replacer_callback The callback to replace the search param with the translated value
     * @param array $query_param The query param array
     * @param string $content The HTML content to replace
     * @param bool $skip_language Skip the language replacement
     *
     * @return string The replaced HTML content
     */
    public static function openCartReplaceHTMLSearchParams($matcher, $replacer_callback, $query_param, $content, $skip_language = false)
    {
        [$base_dir, $language_dir] = self::getOpenCartReplacement();
        $replaced_content = preg_replace_callback($matcher, function ($matches) use ($base_dir, $language_dir, $query_param, $skip_language, $replacer_callback) {
            $url = $matches[1];
            $parsed_url = parse_url(html_entity_decode($url));
            if (empty($parsed_url)) {
                return $matches[0];
            }
            if (empty($parsed_url['host'])) {
                // Don't continue
                return $matches[0];
            }

            $this_param = !empty($parsed_url['query']) ? Helper::queryStringToArray($parsed_url['query']) : array();
            if (!empty($this_param['search'])) {
                // Replace back the search
                $this_param['search'] = $query_param['search'];
                $parsed_url['query'] = Helper::arrayToQueryString($this_param);
            }

            // Make the new URL
            $new_url = Helper::buildUrl($parsed_url);
            // Replace the base dir with the proper one
            if (!$skip_language) {
                $new_url = preg_replace('/' . $base_dir . '/i', $language_dir, $new_url);                
            }

            return $replacer_callback($new_url, $matches);
        }, $content);

        if (!empty($replaced_content)) {
            return $replaced_content;
        }

        return $content;
    }
}