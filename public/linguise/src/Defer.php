<?php

namespace Linguise\Script\Core;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class Defer
{
    /**
     * @var null|Defer
     */
    private static $_instance = null;

    private $_actions = array();

    /**
     * Retrieve singleton instance
     *
     * @return Defer|null
     */
    public static function getInstance() {

        if(is_null(self::$_instance)) {
            self::$_instance = new Defer();
        }

        return self::$_instance;
    }

    private function __construct()
    {
    }

    public function defer($function, $args = array())
    {
        $this->_actions[] = array('function' => $function, 'args' => $args);
    }

    public function finalize()
    {
        foreach ($this->_actions as $action) {
            call_user_func($action['function'], $action['args']);
        }
    }
}