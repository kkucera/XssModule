<?php
/**
 * Test Bootstrapper
 *
 * @copyright Copyright (c) 2012 WebPT, INC
 */
date_default_timezone_set('UTC');

error_reporting(E_ALL | E_STRICT);

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(__DIR__ . '/../..'));

chdir(APPLICATION_PATH);

require_once __DIR__ . "/../../vendor/autoload.php";
