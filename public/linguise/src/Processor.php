<?php
namespace Linguise\Script\Core;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class Processor {

    public static $version = LINGUISE_SCRIPT_TRANSLATION_VERSION;

    public function __construct($version = null)
    {
        if ($version !== 'null') {
            Processor::$version = $version;
        }

        if (!empty(Configuration::getInstance()->get('debug')) && Configuration::getInstance()->get('debug')) {
            if (is_int(Configuration::getInstance()->get('debug'))) {
                $verbosity = Configuration::getInstance()->get('debug');
            } else {
                $verbosity = 0;
            }
            Debug::enable($verbosity, Configuration::getInstance()->get('debug_ip'));
        }

        // Generate data folder name and create it if it doesn't exit
        Helper::prepareDataDir();

        // Finalize defer actions on shutdown
        register_shutdown_function(function() {
            Defer::getInstance()->finalize();
            Database::getInstance()->close();
        });
    }

    /**
     * Load the page and translate it
     */
    public function run()
    {
        if (!isset($_GET['linguise_language'])) {
            die();
        }

        Debug::log('$_SERVER: ' . print_r(array_merge($_SERVER, ['PHP_AUTH_PW' => '', 'HTTP_AUTHORIZATION' =>  '', 'HTTP_COOKIE' => '']), true), 4);

        Hook::trigger('onBeforeMakeRequest');

        ob_start();
        $request = new CurlRequest();
        $request->makeRequest();

        Hook::trigger('onAfterMakeRequest');

        if (Response::getInstance()->getResponseCode() === 304) {
            Debug::log('304 Not modified');
            Response::getInstance()->end();
        }

        // We want to translate the page
        $editor_enabled = !empty($_COOKIE['linguiseEditorToken']) && !empty($_COOKIE['linguiseEditorStatus']);
        $cache_enabled = Configuration::getInstance()->get('cache_enabled');

        if ($editor_enabled) {
            Translation::getInstance()->enableEditor($_COOKIE['linguiseEditorToken']);
            Response::getInstance()->addHeader('Cache-Control', 'no-store');
        } else if ($cache_enabled) {
            // Serve cache if it exists
            Cache::getInstance()->serve();
        }

        Translation::getInstance()->translate();

        if (!$editor_enabled && $cache_enabled) {
            Defer::getInstance()->defer(function () {
                Cache::getInstance()->save();
            });
        }

        Response::getInstance()->end();
    }

    public function update()
    {
        Updater::getInstance()->update();
    }

    public function editor()
    {
        if (!empty($_POST['token']) && !empty($_POST['expires']) && !empty($_POST['timestamp']) && !empty($_POST['signature'])) {
            // Validate the signature from Linguise
            if ($_POST['timestamp'] < time()-120) {
                // Make sure the timestamp is not more than a few minutes old (120 seconds)
                echo '<p>It seems you were trying the Live Editor with the wrong domain configuration. </p> 
                      <p>Please double-check on your configuration in your Linguise dashboard or reach out to our support team</p>';
                die();
            }

            $POST = $_POST;
            ksort($POST);

            $params = [];
            foreach($POST as $key => $value) {
                if ($key === 'signature') {
                    continue;
                }

                $params[] = $key . '=' . $value;
            }

            $signature = hash_hmac('sha256', implode('', $params), Configuration::getInstance()->get('token'));

            if ($signature !== $_POST['signature']) {
                echo '<p>It seems you were trying the Live Editor with the wrong domain configuration. </p> 
                      <p>Please double-check on your configuration in your Linguise dashboard or reach out to our support team</p>';
                die();
            }

            Response::getInstance()->addCookie('linguiseEditorToken', $_POST['token'], strtotime($_POST['expires']));
            Response::getInstance()->addCookie('linguiseEditorStatus', 1);
            // Set wordpress_logged_in_ cookie, editor not showing if wordpress hosted in cloudways
            Response::getInstance()->addCookie('wordpress_logged_in_'. md5('linguise'), 'linguise');
        }

        $content = file_get_contents(__DIR__ .  DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'editor.html');

        $options = '';
        foreach (json_decode($_POST['languages']) as $language) {
            $options .= '<option value="'.htmlspecialchars($language->code).'">'.htmlspecialchars($language->name).'</option>';
        }

        $content = str_replace('{{options}}', $options, $content);

        Response::getInstance()->setContent($content);
        Response::getInstance()->setResponseCode(200, false);
        Response::getInstance()->end();
    }

    public function clearCache()
    {
        Cache::getInstance()->clear();
    }

    public function updateCertificates()
    {
        Certificates::getInstance()->downloadCertificates();
    }

}