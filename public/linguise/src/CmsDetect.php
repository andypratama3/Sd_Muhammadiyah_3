<?php

namespace Linguise\Script\Core;

class CmsDetect {
    /**
     * Check for the current CMS and return it
     *
     * @return string
     */
    public static function detect() {
        $current_cms = Configuration::getInstance()->get('cms');
        if (empty($current_cms)) {
            $current_cms = 'auto';
        }

        $current_cms = strtolower($current_cms);

        if ($current_cms !== 'auto') {
            return $current_cms;
        }

        $base_dir = Configuration::getInstance()->get('base_dir');
        if (file_exists($base_dir . 'wp-config.php')) {
            return 'wordpress';
        }

        if (file_exists($base_dir . 'configuration.php')) {
            $config_content = file_get_contents($base_dir . 'configuration.php');
            if ($config_content && strpos($config_content, 'JConfig') !== false) {
                return 'joomla';
            }
        }

        if (file_exists($base_dir . 'config.php')) {
            include_once $base_dir . 'config.php';
            if (defined('DIR_OPENCART')) {
                return 'opencart';
            }
        }

        if (file_exists(realpath($base_dir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'defines.inc.php'))) {
            include_once realpath($base_dir . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'defines.inc.php');
            if (defined('_PS_ROOT_DIR_')) {
                return 'prestashop';
            }
        }

        if (file_exists($base_dir . 'mage')) {
            // Magento 1.x
            $php_file = file_get_contents($base_dir . 'mage');
            if ($php_file && strpos($php_file, 'Magento') !== false) {
                return 'magento';
            }
        }

        if (file_exists($base_dir . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'magento')) {
            // Magento 2.x
            return 'magento';
        }

        if (file_exists($base_dir . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'version.php')) {
            // Read file
            $php_file = file_get_contents($base_dir . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'version.php');
            if ($php_file && strpos($php_file, 'Zen Cart') !== false) {
                return 'zencart';
            }
            if ($php_file && strpos($php_file, 'zen-cart.com') !== false) {
                return 'zencart';
            }
        }

        // TODO: Find a reliable way to detect Laravel installation setup

        return $current_cms;
    }
}