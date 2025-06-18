<?php

namespace Linguise\Script\Core;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

/**
 * Check if the array is an actual object or not.
 *
 * @param array|object $arr_or_object The array or object to be checked
 *
 * @return boolean - True if it's an actual object, false if it's an array
 */
function is_actual_object($arr_or_object): bool
{
    if (is_object($arr_or_object)) {
        return true;
    }

    if (!is_array($arr_or_object)) {
        // preliminary check
        return false;
    }

    // https://stackoverflow.com/a/72949244 (PHP 7 compatible)
    if (function_exists('array_is_list')) {
        return array_is_list($arr_or_object) === false; // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.array_is_listFound
    }

    $keys = array_keys($arr_or_object);
    return implode('', $keys) !== implode(range(0, count($keys) - 1));
}

/**
 * A simplified port of FragmentHandler
 */
class JsonWalker
{
    /**
     * Regex/matcher for our custom HTML fragment
     *
     * @var string
     */
    protected static $frag_html_match = '/<(div|a|linguise-main) class="linguise-fragment" data-fragment-key="([^"]*)" data-fragment-format="(link|html|html-main|text)"(?: href="([^"]*)")?>(.*?)<\/\1>/si';

    /**
     * Check with the Configuration for the allow list and deny list.
     *
     * @param string $key           The key to be checked
     * @param string $full_key      The full JSON path key to be checked
     * @param array  $fragment_list The allow/deny list
     *
     * @return boolean|null - True if it's allowed, false if it's not
     */
    public static function isKeyAllowed($key, $full_key, $fragment_list)
    {
        // the allow/deny list are formatted array like:
        // [
        //    [
        //        'key' => 'woocommerce',
        //        'mode' => 'regex' | 'exact | 'path' | 'wildcard',
        //        'kind' => 'allow' | 'deny',
        //    ]
        // ]

        foreach ($fragment_list as $frag_item) {
            $allow = $frag_item['kind'] === 'allow';
            $cast_data = isset($frag_item['cast']) ? $frag_item['cast'] : null;
            if ($frag_item['mode'] === 'path') {
                // check if full key is the same
                if ($frag_item['key'] === $full_key) {
                    // Return cast or bool
                    return $cast_data ? $cast_data : $allow;
                }
            } elseif ($frag_item['mode'] === 'exact') {
                // check if key is the same
                if ($frag_item['key'] === $key) {
                    return $cast_data ? $cast_data : $allow;
                }
            } elseif ($frag_item['mode'] === 'regex' || $frag_item['mode'] === 'regex_full') {
                // check if regex matches
                $key_match = $frag_item['mode'] === 'regex_full' ? $full_key : $key;
                $match_re = '/' . $frag_item['key'] . '/';
                if (preg_match($match_re, $key_match)) {
                    return $cast_data ? $cast_data : $allow;
                }
            } elseif ($frag_item['mode'] === 'wildcard') {
                // check if wildcard matches
                $match_re = '/^.*?' . $frag_item['key'] . '.*?$/';
                if (preg_match($match_re, $key)) {
                    return $cast_data ? $cast_data : $allow;
                }
            }
        }

        return null;
    }

    /**
     * Check if the string is a translatable string or not.
     *
     * @param string $value The string to be checked
     *
     * @return boolean - True if it's a translatable string, false if it's not
     */
    private static function isTranslatableString($value)
    {
        $value = trim($value);

        if (empty($value) || !is_string($value)) {
            return false;
        }

        // Check if it's a JSON, if yes, do not translate
        $json_parse = json_decode($value);
        if (!is_null($json_parse)) {
            return false;
        }

        // Has space? Most likely a translateable string
        if (preg_match('/\s/', $value) > 0) {
            return true;
        }

        // Check if first word is lowercase (bad idea?)
        // Or, check if has a number/symbols
        if (ctype_lower($value[0]) || preg_match('/[0-9\W]/', $value)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the string is a link or not.
     *
     * @param string $value The string to be checked
     *
     * @return boolean - True if it's a link, false if it's not
     */
    private static function isStringLink($value)
    {
        // Has http:// or https://
        // Has %%endpoint%%
        // Starts with / and has no space
        if (preg_match('/https?:\/\//', $value) || preg_match('/%%.*%%/', $value) || (substr($value, 0, 1) === '/' && !preg_match('/\s/', $value) > 0)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the string is a HTML element or not.
     *
     * @param string $value The string to be checked
     *
     * @return boolean - True if it's a HTML element, false if it's not
     */
    private static function isHTMLElement($value)
    {
        if (empty($value)) {
            return false;
        }

        // use simplexml, suppress the warning
        $doc = @simplexml_load_string($value); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
        if ($doc !== false) {
            return 'html';
        }

        // Use strip_tags method
        if (strip_tags($value) !== $value) {
            return 'html-main';
        }

        return false;
    }

    /**
     * Check and wrap key if has dot
     *
     * @param string $key The key to be checked
     *
     * @return string
     */
    private static function wrapKey($key)
    {
        // if include '.'
        if (preg_match('/\./', $key)) {
            // replace . with $$
            $key = str_replace('.', '$$', $key);
        }

        return $key;
    }

    /**
     * Collect the fragment from the JSON data.
     *
     * @param string  $key                 The key of the fragment
     * @param mixed   $value               The value of the fragment
     * @param array   $collected_fragments The array of collected fragments
     * @param string  $current_key         The current key of the fragment
     * @param boolean $fragment_list       The list of allowed JSON key
     * @param integer $array_index         The index of the array, if it's an array
     *
     * @return array - The array of collected fragments
     */
    private static function collectFragment($key, $value, $collected_fragments, $current_key, $fragment_list, $array_index = null)
    {
        $use_key = self::wrapKey($key);
        if (!empty($current_key)) {
            $use_key = '.' . $use_key;

            $use_key = $current_key . $use_key;
        }
        if ($array_index !== null) {
            $use_key = $use_key . '.' . $array_index;
        }

        if (is_actual_object($value)) {
            $collected_fragments = self::collectFragmentFromJson($value, $fragment_list, $collected_fragments, $use_key);
        } elseif (is_array($value)) {
            for ($i = 0; $i < count($value); $i++) { // phpcs:ignore Generic.CodeAnalysis.ForLoopWithTestFunctionCall.NotAllowed
                $inner_value = $value[$i];
                $collected_fragments = self::collectFragment($key, $inner_value, $collected_fragments, $current_key, $fragment_list, $i);
            }
        } elseif (is_string($value)) {
            // By default, we assume "text" for now.
            $allowed_filters = self::isKeyAllowed($key, $use_key, $fragment_list);
            if (is_null($allowed_filters) || $allowed_filters === false) {
                // If it's not null and it's false, then we don't need to check further
                return $collected_fragments;
            }
            $tl_string = self::isTranslatableString($value);
            $tl_link = self::isStringLink($value);
            $tl_dom = self::isHTMLElement($value);

            $format = 'text';
            if ($tl_link) {
                $format = 'link';
            } elseif (is_string($tl_dom)) {
                $format = $tl_dom;
            }
            if (is_string($allowed_filters)) {
                $format = $allowed_filters;
            }
            if ($tl_string || $tl_link || is_string($tl_dom) || $allowed_filters) {
                $collected_fragments[] = [
                    'key' => $use_key,
                    'value' => $value,
                    'format' => $format,
                ];
            }
        }

        return $collected_fragments;
    }

    /**
     * A recursive function that iterates through the json data and collects the fragments
     *
     * @param array   $json_data           The JSON data to be iterated
     * @param boolean $fragment_list       The list of allowed JSON key
     * @param array   $collected_fragments Default to empty array, when it's being called inside the function it will be appended for the parent key
     * @param string  $current_key         Default to empty string, when it's being called inside the function it will be appended for the parent key
     *
     * @return array
     */
    public static function collectFragmentFromJson($json_data, $fragment_list, $collected_fragments = null, $current_key = null)
    {
        // set default if null
        if ($collected_fragments === null) {
            $collected_fragments = [];
        }
        if ($current_key === null) {
            $current_key = '';
        }
        foreach ($json_data as $key => $value) {
            $collected_fragments = self::collectFragment($key, $value, $collected_fragments, $current_key, $fragment_list);
        }
        return $collected_fragments;
    }

    /**
     * Convert the array fragments created previously into a HTML
     * data that will be injected into the page.
     *
     * @param array  $json_fragments The array of fragments, generated with collectFragmentFromJson
     *
     * @return string - The HTML data that will be injected into the page
     */
    public static function intoHTMLFragments($json_fragments)
    {
        $html = '';
        foreach ($json_fragments as $fragment) {
            $tag = 'div';
            if ($fragment['format'] === 'link') {
                $tag = 'a';
            }
            $frag_value = $fragment['value'];
            if ($fragment['format'] === 'html') {
                // check if html has div, if yes change it to divlinguise
                $frag_value = preg_replace('/<div(.*?)>(.*?)<\/div>$/si', '<linguise-div$1>$2</linguise-div>', $frag_value, 1);
            }
            if ($fragment['format'] === 'html-main') {
                $tag = 'linguise-main';
            }
            $html .= '<' . $tag . ' class="linguise-fragment" data-fragment-key="' . htmlspecialchars($fragment['key'], ENT_QUOTES, 'UTF-8', false) . '" data-fragment-format="' . $fragment['format'] . '"';
            if ($fragment['format'] === 'link') {
                $html .= ' href="' . $fragment['value'] . '">';
            } else {
                $html .= '>' . $frag_value;
            }
            $html .= '</' . $tag . '>' . "\n";
        }

        return trim($html);
    }

    /**
     * Convert back the translated fragments into the original JSON data
     *
     * @param string $html_fragments The HTML fragments that was injected into the page
     *
     * @return array - The array of fragments
     */
    public static function intoJSONFragments($html_fragments)
    {
        $fragments = [];
        // Let's just use regex to get anything with "linguise-fragment" class
        preg_match_all(self::$frag_html_match, $html_fragments, $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $match) {
            $fragment_key = htmlspecialchars_decode($match[2], ENT_QUOTES);
            $fragment_format = $match[3];

            $fragment_value = $match[5];

            if ($fragment_format === 'link') {
                $fragment_value = $match[4];
            }

            if ($fragment_format === 'html') {
                // parse back the linguise-dev
                $fragment_value = preg_replace('/<linguise-div(.*?)>(.*?)<\/linguise-div>$/si', '<div$1>$2</div>', $fragment_value, 1);
            } else if ($fragment_format === 'html-main') {
                // parse back the linguise-main
                $fragment_value = preg_replace('/<linguise-main>(.*?)<\/linguise-main>$/si', '$1', $fragment_value, 1);
            }

            // The returned data is encoded HTML entities for non-ASCII characters
            // Decode it back to UTF-8 for link and text, for HTML since it would be rendered
            // the browser will decode it back to UTF-8 automatically
            if ($fragment_format !== 'html' && $fragment_format !== 'html-main') {
                $fragment_value = html_entity_decode($fragment_value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }

            // make it into list for each fragment name
            $fragments[] = [
                'key' => $fragment_key,
                'value' => $fragment_value,
                'format' => $fragment_format,
                'match' => $match[0],
            ];
        }

        return $fragments;
    }

    /**
     * Replace the fragments into the original JSON data in-place
     * 
     * @param array $original  The original JSON data
     * @param array $fragments The array of fragments, generated with collectFragmentFromJson or from intoJSONFragments
     */
    public static function replaceFragments(&$original, $fragments)
    {
        foreach ($fragments as $fragment) {
            // key is dot separated like "key1.key2.key3"
            // which means the data is in key1 -> key2 -> key3
            $keys = explode('.', $fragment['key']);
            $current = &$original;
            $stop = false;
            foreach ($keys as $key) {
                // unwrap key
                $key = str_replace('$$', '.', $key);
                if (isset($current[$key])) {
                    $current = &$current[$key];
                } else {
                    $stop = true;
                }
            }

            if ($stop) {
                continue;
            }

            // replace the value
            $current = $fragment['value'];
        }
    }
}
