<?php

namespace Linguise\Script\Core;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class Url
{

    public static function translateUrl($redirect_url)
    {
        $request = Request::getInstance();

        $base_url = $request->getProtocol() . '://' . $request->getHostname() . $request->getBaseDir();

        if (strpos($redirect_url, $base_url) !== 0) {
            // Redirection url not based on the same website
            return $redirect_url;
        }

        $url_parsed = parse_url($redirect_url);
        $path = substr($url_parsed['path'], strlen($request->getBaseDir()));

        $query = !empty($url_parsed['query'])===true?'?'.$url_parsed['query']:'';

        preg_match('/.*?(\/*)$/', $url_parsed['path'], $matches);
        $trailing_slashes =  $matches[1];

        if (!rtrim($path, '/')) {
            return $request->getProtocol() . '://' . $request->getHostname() . $request->getBaseDir() . '/' . $request->getLanguage() . $path . $query;
        }

        $translated_url = Database::getInstance()->getTranslatedUrl(rtrim($path, '/'));

        if (!$translated_url) {
            return $request->getProtocol() . '://' . $request->getHostname() . $request->getBaseDir() . '/' . $request->getLanguage() . $path . $query;
        }

        return $request->getProtocol() . '://' . $request->getHostname() . $request->getBaseDir() . $translated_url . $trailing_slashes . $query;
    }
}