<?php

namespace Linguise\Script\Core;

defined('LINGUISE_SCRIPT_TRANSLATION') or die();

class AfterUpdate
{
    static function afterUpdateRun($base_folder)
    {
        Debug::log('After update done');
    }
}