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

// special import for the module class as it doesn't appear to namespace structure
require_once __DIR__ . '/../../Module.php';

use EMRCore\Config\Application as ApplicationConfig;
use Zend\Config\Config as ZendConfig;
use Zend\Config\Processor\Token;

// Core global config.
$moduleConfig = new ZendConfig(include __DIR__ . '/../../vendor/WebPT/EMRCore/src/EMRCore/Config/config/global.php', true);

// Module config.
$module = new DeskModule\Module;
$moduleConfig->merge(new ZendConfig($module->getConfig()));

// Integration global config.
$moduleConfig->merge(new ZendConfig(include __DIR__ . '/config/global.php'));

// Integration local config.
if (file_exists(__DIR__ . '/config/local.php') == true) {
    $localTestConfig = new ZendConfig(include __DIR__ . '/config/local.php', true);
    $moduleConfig->merge($localTestConfig);
}

$processor = new Token($moduleConfig->get('tokens'));
$processor->process($moduleConfig);

ApplicationConfig::getInstance()->setConfiguration($moduleConfig->toArray(), false);
