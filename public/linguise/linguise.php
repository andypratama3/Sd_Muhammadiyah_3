<?php

use Linguise\Script\Core\CmsDetect;
use Linguise\Script\Core\Configuration;
use Linguise\Script\Core\Hook;

define('LINGUISE_SCRIPT_TRANSLATION', true);
define('LINGUISE_SCRIPT_TRANSLATION_VERSION', '1.3.15');

ini_set('display_errors', false);

// require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
// require_once('./vendor/autoload.php');

if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'Configuration.php')) {
    require(__DIR__ . DIRECTORY_SEPARATOR . 'Configuration.php');
    foreach (get_class_vars(\Linguise\Script\Configuration::class) as $attribute_name => $attribute_value) {
        Configuration::getInstance()->set($attribute_name, $attribute_value);
    }
    foreach (get_class_methods(\Linguise\Script\Configuration::class) as $hook) {
        if (strpos($hook, 'on') !== 0) {
            continue;
        }
        Hook::add($hook, \Linguise\Script\Configuration::class);
    }
}

Configuration::getInstance()->set('base_dir', realpath(__DIR__ . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR);
$detected_cms = CmsDetect::detect();
Configuration::getInstance()->set('cms', $detected_cms);

$processor = new \Linguise\Script\Core\Processor();
if (isset($_GET['linguise_language']) && $_GET['linguise_language'] === 'zz-zz' &&  isset($_GET['linguise_action']) && $_GET['linguise_action'] === 'update') {
    $processor->update();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['linguise_language']) && $_GET['linguise_language'] === 'zz-zz') {
    $processor->editor();
} elseif (in_array($_SERVER['REQUEST_METHOD'], array('POST', 'HEAD')) && isset($_GET['linguise_language']) && $_GET['linguise_language'] === 'zz-zz' &&  isset($_GET['linguise_action'])) {
    switch ($_GET['linguise_action']) {
        case  'clear-cache':
            $processor->clearCache();
            break;
        case 'update-certificates':
            $processor->updateCertificates();
            break;
    }
} else {
    $processor->run();
}
