<?php

namespace Linguise\Script\Core\Platforms;

use Linguise\Script\Core\Request;
use Linguise\Script\Core\Response;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class Zencart
{
    /**
     * Patch the inner javascript link
     * 
     * @param string $content
     * @return string
     */
    public static function patchInnerJavascriptLink($content)
    {
        $request = Request::getInstance();
        $language = $request->getLanguage();

        if (empty($language)) {
            return $content;
        }

        $response = Response::getInstance();

        // Replace popup window
        $matcher = '/javascript:popupWindow\((\'|"|&apos;|&quot;)([^\'"]+)(\'|"|&apos;|&quot;)\)/i';
        $replaced_content = preg_replace_callback($matcher, function ($matches) use ($language) {
            $url = $matches[2];
            $parsed_url = parse_url(html_entity_decode($url));
            if (empty($parsed_url)) {
                return $matches[0];
            }
            if (empty($parsed_url['host'])) {
                // Don't continue
                return $matches[0];
            }
            // Check if the URL is already in the correct format
            if (strpos($url, '/' . $language . '/') !== false) {
                return $matches[0];
            }
            // Replace the URL with the new one
            $new_url = str_replace('/index.php', '/' . $language . '/index.php', $url);
            return 'javascript:popupWindow(\'' . $new_url . '\')';
        }, $response->getContent());

        if (!empty($replaced_content)) {
            return $replaced_content;
        }

        return $content;
    }
}