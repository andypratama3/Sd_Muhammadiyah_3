<?php

namespace Linguise\Script\Core\Platforms;

use Linguise\Script\Core\Boundary;
use Linguise\Script\Core\Configuration;
use Linguise\Script\Core\Database;
use Linguise\Script\Core\Helper;
use Linguise\Script\Core\JsonWalker;
use Linguise\Script\Core\Processor;
use Linguise\Script\Core\Request;
use Linguise\Script\Core\Translation;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class PrestaShop
{
    /**
     * List of autocomplete matchers for JSONWalker
     * 
     * @var array
     */
    protected static $autocomplete_matchers = [
        [
            'key' => 'rendered_products_top',
            'mode' => 'exact',
            'kind' => 'allow',
            'cast' => 'html-main',
        ],
        [
            'key' => 'rendered_products',
            'mode' => 'exact',
            'kind' => 'allow',
            'cast' => 'html-main',
        ],
        [
            'key' => 'rendered_products_bottom',
            'mode' => 'exact',
            'kind' => 'allow',
            'cast' => 'html-main',
        ],
        [
            'key' => 'rendered_facets',
            'mode' => 'exact',
            'kind' => 'allow',
            'cast' => 'html-main',
        ],
        [
            'key' => 'rendered_active_filters',
            'mode' => 'exact',
            'kind' => 'allow',
            'cast' => 'html-main',
        ],
        [
            'key' => 'label',
            'mode' => 'exact',
            'kind' => 'allow',
        ],
        [
            'key' => 'current_url',
            'mode' => 'exact',
            'kind' => 'allow',
            'cast' => 'link',
        ],
        [
            'key' => '^pagination\.pages\.(\d+)\.url',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'link',
        ],
        [
            'key' => '^sort_orders\.(\d+)\.(label|url)',
            'mode' => 'regex_full',
            'kind' => 'allow',
        ],
        [
            'key' => '^products\.(\d+)\.(url|[\w_]+_url|link)',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'link',
        ],
        [
            'key' => '^products\.(\d+)\.(?:manufacturer_|category_|tax_)?name',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
        [
            'key' => '^products\.(\d+)\.labels\.tax_(?:short|long)',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
        [
            'key' => '^products\.(\d+)\.cover\.legend',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
        [
            'key' => '^products\.(\d+)\.(?:description|description_short|attributes_small|legend)',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'html-main',
        ],
        [
            'key' => '^products\.(\d+)\.attributes\.(?:[\w]+)$',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
    ];

    /**
     * List of product matchers for JSONWalker
     * 
     * @var array
     */
    protected static $product_ajax_matchers = [
        [
            'key' => 'quickview_html',
            'mode' => 'exact',
            'kind' => 'allow',
            'cast' => 'html-main',
        ],
        [
            'key' => '^product_(?:prices|cover_thumbnails|customization|details|variants|add_to_cart|discounts|additional_info|images_modal|flags)$',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'html-main',
        ],
        [
            'key' => 'product_url',
            'mode' => 'exact',
            'kind' => 'allow',
            'cast' => 'link',
        ],
        [
            'key' => 'product_title',
            'mode' => 'exact',
            'kind' => 'allow',
            'cast' => 'text',
        ],
        [
            'key' => '^products\.(?:manufacturer_|category_|tax_)?name',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
        [
            'key' => '^products\.labels\.tax_(?:short|long)',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
        [
            'key' => '^products\.cover\.legend',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
        [
            'key' => '^products\.(?:description|description_short|attributes_small|legend)',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'html-main',
        ],
        [
            'key' => '^products\.attributes\.(?:[\w]+)$',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
    ];


    /**
     * List of common order matchers for JSONWalker
     * 
     * @var array
     */
    protected static $common_order_matchers = [
        [
            'key' => 'preview',
            'mode' => 'exact',
            'kind' => 'allow',
            'cast' => 'html-main',
        ],
    ];

    /**
     * List of shopping cart matchers for JSONWalker
     * 
     * @var array
     */
    public static $shopping_cart_matchers = [
        [
            'key' => 'modal',
            'mode' => 'exact',
            'kind' => 'allow',
            'cast' => 'html-main',
        ],
    ];

    /**
     * List of address matchers for JSONWalker
     * 
     * @var array
     */
    public static $address_checkout_matchers = [
        [
            'key' => 'address_form',
            'mode' => 'exact',
            'kind' => 'allow',
            'cast' => 'html-main',
        ],
    ];

    /**
     * List of matchers for prestashop variables in HTML
     * 
     * @var array
     */
    protected static $prestashop_variables_matchers = [
        [
            'key' => '^(cart|configuration)\.(totals|subtotals|quantity_discount)\.(?:[\w]+)\.(label|value)',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
        [
            'key' => '^cart\.(?:summary_string|minimalPurchaseRequired)$',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
        [
            'key' => '^page\.(?:title|meta\.(?:title|description))',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
        [
            'key' => 'page.canonical',
            'mode' => 'path',
            'kind' => 'allow',
            'cast' => 'link',
        ],
        [
            'key' => '^page\.password\-policy\.feedbacks\.(?:.+?)$',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
        [
            'key' => '^shop\..*',
            'mode' => 'regex_full',
            'kind' => 'allow',
        ],
        [
            'key' => '^urls\.(?:pages|actions)\.(?:.+?)$',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'link',
        ],
        [
            'key' => '^urls\.(?:base|current|shop_domain)_url$',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'link',
        ],
        [
            'key' => '^breadcumb\.links\.\d+\.(title|url)',
            'mode' => 'regex_full',
            'kind' => 'allow',
        ],
        [
            'key' => '^cart\.products\.\d+\.(url|[\w_]+_url|link)',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'link',
        ],
        [
            'key' => '^cart\.products\.\d+\.(?:embedded_attributes\.)?(?:manufacturer_|category_|tax_)?name',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
        [
            'key' => '^cart\.products\.\d+\.labels\.tax_(?:short|long)',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
        [
            'key' => '^cart\.products\.\d+\.cover\.legend',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
        [
            'key' => '^cart\.products\.\d+\.images\.\d+\.legend',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
        [
            'key' => '^cart\.products\.\d+\.(?:embedded_attributes\.)?(?:description|description_short|attributes_small|legend)',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'html-main',
        ],
        [
            'key' => '^cart\.products\.(\d+)\.(?:embedded_attributes\.)?attributes\.(?:[\w]+)$',
            'mode' => 'regex_full',
            'kind' => 'allow',
            'cast' => 'text',
        ],
    ];

    protected static function translateJson($json_content, $language, $matchers)
    {
        $fragments = JsonWalker::collectFragmentFromJson($json_content, $matchers);
        $html_fragments = JsonWalker::intoHTMLFragments($fragments);

        $content = '<html><head></head><body>';
        $content .= $html_fragments;
        $content .= '</body></html>';

        $content = self::skipTranslateTemplate($content);
        $content = self::replaceControllerUrl($content);

        $boundary = new Boundary();
        $request = Request::getInstance();

        $boundary->addPostFields('version', Processor::$version);
        $boundary->addPostFields('url', $request->getBaseUrl());
        $boundary->addPostFields('language', $language);
        $boundary->addPostFields('requested_path', $request->getNonTranslatedUrl());
        $boundary->addPostFields('content', $content);
        $boundary->addPostFields('token', Configuration::getInstance()->get('token'));
        $boundary->addPostFields('ip', Helper::getIpAddress());
        $boundary->addPostFields('response_code', 200);
        $boundary->addPostFields('user_agent', !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');

        $ch = curl_init();
        list($translated_content, $response_code) = Translation::getInstance()->_translate($ch, $boundary);
        curl_close($ch);

        if (!$translated_content || $response_code !== 200) {
            // We failed to translate
            return $json_content;
        }

        $result = json_decode($translated_content);

        if (isset($result->url_translations)) {
            $new_urls = get_object_vars($result->url_translations);
            Database::getInstance()->saveUrls((array)$new_urls);
        }
    
        if (isset($result->urls_untranslated)) {
            Database::getInstance()->removeUrls((array)$result->urls_untranslated);
        }

        if (isset($result->redirect)) {
            // Somehow we got this...?
            return $json_content;
        }

        $translated_fragments = JsonWalker::intoJSONFragments($result->content);
        if (empty($translated_fragments)) {
            return $json_content;
        }

        // Clone $json_content
        $translated_json = json_decode(json_encode($json_content), true);

        JsonWalker::replaceFragments($translated_json, $translated_fragments);

        return $translated_json;
    }

    public static function translateSearchAutocomplete($json_content, $language)
    {
        return self::translateJson($json_content, $language, self::$autocomplete_matchers);
    }

    public static function translateProductAjax($json_content, $language)
    {
        return self::translateJson($json_content, $language, self::$product_ajax_matchers);
    }

    public static function translateOrderAjax($json_content, $language, $additional_matchers = [])
    {
        return self::translateJson($json_content, $language, array_merge(self::$common_order_matchers, $additional_matchers));
    }

    public static function preprocessPrestashopVariables($content)
    {
        preg_match('/var prestashop = {(.+?)};/', $content, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches)) {
            return $content;
        }

        $json_content = json_decode('{' . $matches[1][0] . '}', true);
        if (empty($json_content)) {
            return $content;
        }

        $collected = JsonWalker::collectFragmentFromJson($json_content, self::$prestashop_variables_matchers);
        $html_data = JsonWalker::intoHTMLFragments($collected);

        // replace </body> with {$html_data}</body>
        $content = str_replace('</body>', $html_data . '</body>', $content);

        return $content;
    }

    public static function translatePrestashopVariables($content)
    {
        preg_match('/var prestashop = {(.+?)};/', $content, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches)) {
            return $content;
        }

        $json_content = json_decode('{' . $matches[1][0] . '}', true);
        if (empty($json_content)) {
            return $content;
        }

        $collected = JsonWalker::intoJSONFragments($content);
        JsonWalker::replaceFragments($json_content, $collected);

        // Remove all html tags
        foreach ($collected as $fragment) {
            // Remove the match
            $content = str_replace($fragment['match'], '', $content);
        }

        // Replace the var prestashop
        $replaced = preg_replace_callback('/var prestashop = {(.+?)};/', function () use ($json_content) {
            return 'var prestashop = ' . json_encode($json_content) . ';';
        }, $content);

        if (!empty($replaced)) {
            $content = $replaced;
        }

        return $content;
    }

    public static function skipTranslateTemplate($content)
    {
        // Mark some templating elements as not translatable
        $new_content = preg_replace('/<(button|a)([^>]*)>[\s]*\(\((\w+)\)\)[\s]*<\/\1>/', '<$1$2 translate="no">(($3))</$1>', $content);
        if (!empty($new_content)) {
            $content = $new_content;
        }

        // Other way around
        $new_content = preg_replace('/<(\w+) (\w+="[^"]+".*?)>\(\((\w+)\)\)<\/\1>/', '<$1 $2 translate="no">(($3))</$1>', $content);
        if (!empty($new_content)) {
            $content = $new_content;
        }

        // Skip material-icons
        $new_content = preg_replace('/(<i\b[^>]*\bclass\s*=\s*"(?:[^"]*\s)?material-icons(?:\s[^"]*)?")([^>]*)>([\w_\-]+?)<\/i>/', '$1$2 translate="no">$3</i>', $content);
        if (!empty($new_content)) {
            $content = $new_content;
        }

        return $content;
    }

    public static function replaceControllerUrl($content)
    {
        // Try to replace something like:
        $allowed_attributes = [
            'data-search-controller-url',
            'data-refresh-url',
            'data-url',
            'data-delete-list-url',
            'data-delete-product-url',
            'add-url',
        ];
        $attrs_regex = implode('|', $allowed_attributes);

        $repl_content = preg_replace_callback('/(' . $attrs_regex . ')="(.+?)"/i', function ($matches) {
            $url = parse_url(html_entity_decode($matches[2]));
        
            if (empty($url)) {
                return $matches[0];
            }

            if (empty($url['scheme'])) {
                $url['scheme'] = Request::getInstance()->getProtocol();
            }
        
            $parsed_current = parse_url(Request::getInstance()->getBaseUrl());
            if (empty($parsed_current)) {
                return $matches[0];
            }

            $base_path = empty($parsed_current['path']) ? '' : rtrim($parsed_current['path'], '/');
            $base_path .= '/' . Request::getInstance()->getLanguage();

            $url['path'] = empty($url['path']) ? $base_path : $base_path . $url['path'];
        
            $new_url = Helper::buildUrl($url);

            return $matches[1] . '="' . htmlspecialchars($new_url, ENT_QUOTES, 'UTF-8', false) . '"';
        }, $content);

        if (!empty($repl_content)) {
            $content = $repl_content;
        }

        return $content;
    }
}