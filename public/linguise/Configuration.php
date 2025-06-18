<?php
namespace Linguise\Script;

use Linguise\Script\Core\Boundary;
use Linguise\Script\Core\Helper;
use Linguise\Script\Core\Request;
use Linguise\Script\Core\Response;

use Linguise\Script\Core\Platforms\OpenCart;
use Linguise\Script\Core\Platforms\PrestaShop;
use Linguise\Script\Core\Platforms\Zencart;

if (!defined('LINGUISE_SCRIPT_TRANSLATION')) die();

class Configuration {
    /** Mandatory configuration **/
    public static $token = 'KSybDCQmzltRl8c2epqgW9huzv1CZptu'; // Replace the token by the one found in your Linguise dashboard

    /** Basic configuration **/
    /*
     * Update the CMS value according to your CMS
     * Available CMS are: laravel, prestashop, magento
     */
    public static $cms = 'laravel';

    public $cache_enabled = false;
    public $cache_max_size = 200; // In megabyte
    /**
     * Search translation, enable so that Linguise can automatically translate search queries
     * Note: This option will increase your quota usages by a lot
     */
    public $search_translations = false;

    /** Advanced configuration **/
    public static $server_ip = null;
    public static $server_port = 443;
    public static $debug = false;
    public static $data_dir = null;
    public static $base_dir = null;
    public static $dl_certificates = false;

    /** Advanced database configuration **/
    /*
     *  In case you don't want to use Sqlite, you can use MySQL
     *  To do so, you need to fill the following variables
     *  Linguise will create the tables for you
     */
    public static $db_host = '';
    public static $db_user = '';
    public static $db_password = '';
    public static $db_name = '';
    public static $db_prefix = '';
    // If your database use SSL connection, set this into MYSQLI_CLIENT_SSL
    // https://www.php.net/manual/en/mysqli.constants.php
    public static $db_flags = 0;

    /** Development configuration */
    public static $port = 443;
    public static $host = 'translate.linguise.com';
    public static $update_url = 'https://www.linguise.com/files/php-script-update.json';

    public static function onBeforeMakeRequest()
    {
        $configuration = \Linguise\Script\Core\Configuration::getInstance();
        if ($configuration->get('cms') === 'opencart' && $configuration->get('search_translations')) {
            $request = Request::getInstance();
            $queries = $request->getQuery();

            $query_param = Helper::queryStringToArray($queries);

            if (!empty($query_param['route']) && $query_param['route'] === 'product/search' && !empty($query_param['search'])) {
                $translated_search = \Linguise\Script\Core\Translation::getInstance()->translateJson(['search' => $query_param['search']], $request->getBaseUrl(), $request->getLanguage());

                if (empty($translated_search->search)) {
                    return;
                }

                $query_param['search'] = $translated_search->search;

                $request->setQuery(Helper::arrayToQueryString($query_param));
            }
        }
    }

    public static function onAfterMakeRequest()
    {
        $configuration = \Linguise\Script\Core\Configuration::getInstance();
        if ($configuration->get('cms') === 'prestashop') {
            if (!empty($_POST['action']) && !empty($_POST['id_product'])) {
                // This is a cart request, let's just return the response
                Response::getInstance()->end();
                return;
            }

            $content = Response::getInstance()->getContent();
            $content = PrestaShop::skipTranslateTemplate($content);
            $content = PrestaShop::replaceControllerUrl($content);
            Response::getInstance()->setContent($content);
            return;
        }

        if ($configuration->get('cms') === 'opencart' && $configuration->get('search_translations')) {
            $request = Request::getInstance();
            $response = Response::getInstance();

            $content = $response->getContent();

            [$base_dir, $language_dir] = OpenCart::getOpenCartReplacement();

            $prefixed_data = [
                'data-oc-load',
                'formaction',
            ];

            foreach ($prefixed_data as $prefix) {
                $matcher = '/' . $prefix . '="' . $base_dir . '/i';
                $replacer = $prefix . '="' . $language_dir;
                $content = preg_replace($matcher, $replacer, $content);
            }

            $queries = $request->getQuery();
            $request_query = !empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : $queries;

            $query_param = Helper::queryStringToArray($request_query);
            if (!empty($query_param['route']) && $query_param['route'] === 'product/search' && !empty($query_param['search'])) {
                $content = OpenCart::openCartReplaceHTMLSearchParams(
                    '/<option value="(.+?)"( selected)?>/i',
                    function ($new_url, $matches) {
                        return '<option value="' . htmlspecialchars($new_url, ENT_QUOTES, 'UTF-8', false) . '"' . $matches[2] . '>';
                    },
                    $query_param,
                    $content,
                );
            }

            $response->setContent($content);
        }
    }

    public static function onBeforeTranslation()
    {
        $configuration = \Linguise\Script\Core\Configuration::getInstance();
        if ($configuration->get('cms') === 'prestashop') {
            $response = Response::getInstance();

            $content = $response->getContent();
            $content = PrestaShop::preprocessPrestashopVariables($content);

            $response->setContent($content);
        }
    }

    public static function onAfterTranslation()
    {
        $configuration = \Linguise\Script\Core\Configuration::getInstance();
        if ($configuration->get('cms') === 'opencart' && $configuration->get('search_translations')) {
            $request = Request::getInstance();
            $queries = $request->getQuery();
            $request_query = !empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : $queries;

            $query_param = Helper::queryStringToArray($request_query);

            // replace value for search, works for default theme
            if (!empty($query_param['route']) && $query_param['route'] === 'product/search' && !empty($query_param['search'])) {
                $response = Response::getInstance();
                $matcher = '/<input type="text" name="search" value="(.+?)"/s';
                $replacer = '<input type="text" name="search" value="' . htmlspecialchars($query_param['search'], ENT_QUOTES, 'UTF-8', false) . '"';

                $content = $response->getContent();
                $new_content = preg_replace($matcher, $replacer, $content);
                if (!empty($new_content)) {
                    $content = $new_content;
                }

                $content = OpenCart::openCartReplaceHTMLSearchParams(
                    '/<input type="hidden" name="redirect" value="(.+?)">/i',
                    function ($new_url) {
                        return '<input type="hidden" name="redirect" value="' . htmlspecialchars($new_url, ENT_QUOTES, 'UTF-8', false) . '">';
                    },
                    $query_param,
                    $content,
                );

                $base_url = preg_quote($request->getBaseUrl(), '/');

                $content = OpenCart::openCartReplaceHTMLSearchParams(
                    '/<a href="(' . $base_url . '\/[^"]+)"([^>]*)>(.*?)<\/a>/i',
                    function ($new_url, $matches) {
                        return '<a href="' . htmlspecialchars($new_url, ENT_QUOTES, 'UTF-8', false) . '"' . $matches[2] . '>' . $matches[3] . '</a>';
                    },
                    $query_param,
                    $content,
                    true,
                );

                $response->setContent($content);
            }

            return;
        }

        if ($configuration->get('cms') === 'prestashop') {
            $response = Response::getInstance();
            $content = $response->getContent();
            $content = PrestaShop::translatePrestashopVariables($content);
            $response->setContent($content);
            return;
        }

        if ($configuration->get('cms') === 'zencart') {
            $response = Response::getInstance();
            $content = $response->getContent();
            $content = Zencart::patchInnerJavascriptLink($content);
            $response->setContent($content);
            return;
        }
    }

    protected static function replaceRedirectUrls($json_data, $language, $matcher, $replacement)
    {
        if (is_array($json_data) || is_object($json_data)) {
            foreach ($json_data as $key => $value) {
                if ($key === 'redirect' && is_string($value)) {
                    $result = preg_replace($matcher, $replacement, $value, 1);
                    if (!empty($result)) {
                        $json_data[$key] = $result;
                    }
                } else {
                    $json_data[$key] = self::replaceRedirectUrls($value, $language, $matcher, $replacement);
                }
            }
        }

        return $json_data;
    }

    public static function onBeforeRedirect()
    {
        $configuration = \Linguise\Script\Core\Configuration::getInstance();
        $query_param = Helper::queryStringToArray(Request::getInstance()->getQuery());
        if (
            $configuration->get('cms') === 'opencart' &&
            $configuration->get('search_translations') &&
            !empty($query_param['route']) &&
            $query_param['route'] === 'common/search.redirect'
        ) {
            // Ensure we have the the language code for opencart
            $request = Request::getInstance();
            $response = Response::getInstance();

            $language = $request->getLanguage();
            if (empty($language)) {
                return;
            }

            $redirect_url = $response->getRedirect();
            if (empty($redirect_url)) {
                return;
            }

            [$base_dir, $language_dir] = OpenCart::getOpenCartReplacement();

            // make a regex matcher from $base_dir, escape it
            $matcher = '/^' . $base_dir . '/i';

            $redirect_new = preg_replace($matcher, $language_dir, $redirect_url, 1);
            // Force replacement for broken redirects!!!!!
            if (!empty($redirect_new)) {
                // Redirect properly
                $response->setRedirect($redirect_new, 302);
            }

            return;
        }

        $configuration = \Linguise\Script\Core\Configuration::getInstance();
        $query_param = !empty($_SERVER['QUERY_STRING']) ? Helper::queryStringToArray($_SERVER['QUERY_STRING']) : array();

        if (
            $configuration->get('cms') === 'opencart' &&
            $configuration->get('search_translations') &&
            !empty($query_param['route']) &&
            !empty($query_param['search']) &&
            $query_param['route'] === 'product/search'
        ) {
            $response = Response::getInstance();
            $redirect_url = $response->getRedirect();

            if (empty($redirect_url)) {
                return;
            }

            // replace search query param
            $parsed_url = parse_url($redirect_url);
            if (empty($parsed_url)) {
                return;
            }

            $this_param = !empty($parsed_url['query']) ? Helper::queryStringToArray($parsed_url['query']) : array();
            if (!empty($this_param['search'])) {
                // Replace back the search
                $this_param['search'] = $query_param['search'];
                $parsed_url['query'] = Helper::arrayToQueryString($this_param);
            }

            $redirect_new = Helper::buildUrl($parsed_url);

            if (!empty($redirect_new)) {
                // Redirect properly
                $response->setRedirect($redirect_new, 302);
            }
        }
    }

    public static function onJsonResponse()
    {
        $configuration = \Linguise\Script\Core\Configuration::getInstance();
        $request = Request::getInstance();

        if ($configuration->get('cms') === 'opencart') {
            $response = Response::getInstance();

            $content = $response->getContent();
            $language = $request->getLanguage();

            $json_content = json_decode($content, true);
            if ($json_content !== null) {
                [$base_dir, $language_dir] = OpenCart::getOpenCartReplacement();
                $repl_json = self::replaceRedirectUrls($json_content, $language, '/' . $base_dir . '/i', $language_dir);

                $response->setContent(json_encode($repl_json));
            }
        }

        if ($configuration->get('cms') === 'prestashop') {
            $response = Response::getInstance();
            $content = $response->getContent();
            $json_content = json_decode($content, true);
            $is_post = strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
            $queries = $request->getQuery();
            $request_query = !empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : $queries;
            $query_param = Helper::queryStringToArray($request_query);

            if ($json_content !== null) {
                if ($configuration->get('search_translations') && !empty($request->getMetadata('original_search'))) {
                    $translated_json = PrestaShop::translateSearchAutocomplete($json_content, $request->getLanguage());
                    $response->setContent(json_encode($translated_json));
                    return;
                }

                if ($is_post && strpos($request->getPathname(), 'ps_shoppingcart/ajax') !== false) {
                    $translated_json = PrestaShop::translateOrderAjax($json_content, $request->getLanguage(), PrestaShop::$shopping_cart_matchers);
                    $response->setContent(json_encode($translated_json));

                    return;
                }

                if ($is_post && strpos($request->getPathname(), 'order') !== false && !empty($query_param['ajax']) && !empty($query_param['action'])) {
                    $extra_matchers = [
                        'addressForm' => PrestaShop::$address_checkout_matchers,
                    ];

                    $extra_matcher = $extra_matchers[$query_param['action']] ?? [];
                    $translated_json = PrestaShop::translateOrderAjax($json_content, $request->getLanguage(), $extra_matcher);

                    $response->setContent(json_encode($translated_json));
                    return;
                }

                $has_id_product = !empty($_POST['id_product']) || !empty($query_param['id_product']);

                if ($is_post && !empty($query_param['controller']) && $query_param['controller'] === 'product' && $has_id_product) {
                    $translated_json = PrestaShop::translateProductAjax($json_content, $request->getLanguage());
                    $response->setContent(json_encode($translated_json));
                    return;
                }
            }
        }
    }

    /**
     * Called before the post fields are sent
     * @param Boundary|array $boundaryOrFields
     */
    public static function onBeforePostFields(&$boundaryOrFields)
    {
        // Check if boundary is an instance of Boundary
        $configuration = \Linguise\Script\Core\Configuration::getInstance();
        if ($configuration->get('cms') === 'prestashop' && $configuration->get('search_translations')) {
            if ($boundaryOrFields instanceof Boundary) {
                $request = Request::getInstance();
                // Check if we have s field in boundary
                $s_search = $boundaryOrFields->getPostField('s');
                $result = $boundaryOrFields->getPostField('resultsPerPage');
                if (!empty($s_search) && !empty($result)) {
                    // Assume we have a search request, translate
                    $translated = \Linguise\Script\Core\Translation::getInstance()->translateJson(['s' => $s_search], $request->getBaseUrl(), $request->getLanguage());

                    if (!empty($translated->s)) {
                        // This will replace the s field
                        $boundaryOrFields->addPostFields('s', $translated->s);
                        $request->setMetadata('original_search', $s_search);
                        $request->setMetadata('translated_search', $translated->s);
                    }
                }
            } else if (is_array($boundaryOrFields)) {
                $request = Request::getInstance();
                if (!empty($boundaryOrFields['s']) && !empty($boundaryOrFields['resultsPerPage'])) {
                    $translated = \Linguise\Script\Core\Translation::getInstance()->translateJson(['s' => $boundaryOrFields['s']], $request->getBaseUrl(), $request->getLanguage());

                    if (!empty($translated->s)) {
                        $boundaryOrFields['s'] = $translated->s;
                        $request->setMetadata('original_search', $boundaryOrFields['s']);
                        $request->setMetadata('translated_search', $translated->s);
                    }
                }
            }
        }
    }
}
