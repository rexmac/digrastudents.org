<?php

date_default_timezone_set('UTC');

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
#chdir(dirname(__DIR__));

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(__DIR__ . '/../application'));

// Define path to library directory
defined('LIBRARY_PATH') || define('LIBRARY_PATH', realpath(__DIR__ . '/../library'));

// Define path to vendor directory
defined('VENDOR_PATH') || define('VENDOR_PATH', realpath(__DIR__ . '/../vendor'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(LIBRARY_PATH, get_include_path())));

// Autoloader
$composerAutoloader = require_once VENDOR_PATH . '/autoload.php';
$composerAutoloader->addClassMap(require_once APPLICATION_PATH . '/autoload_classmap.php');
$composerAutoloader->setUseIncludePath(true);

// Setup autoloading
// require 'init_autoloader.php';
//
// // Run the application!
// Zend\Mvc\Application::init(require 'config/application.config.php')->run()
