<?php

date_default_timezone_set('UTC');

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(__DIR__ . '/../zf/application'));

// Define path to library directory
defined('LIBRARY_PATH') || define('LIBRARY_PATH', realpath(__DIR__ . '/../zf/library'));

// Define path to vendor directory
defined('VENDOR_PATH') || define('VENDOR_PATH', realpath(__DIR__ . '/../zf/vendor'));

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
  LIBRARY_PATH,
  get_include_path(),
)));

// Autoloader
$composerAutoloader = require_once VENDOR_PATH . '/autoload.php';
$composerAutoloader->addClassMap(require_once APPLICATION_PATH . '/autoload_classmap.php');
$composerAutoloader->setUseIncludePath(true);

// Shutdown function
register_shutdown_function('session_write_close');

// Zend_Application
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application (
  APPLICATION_ENV,
  APPLICATION_PATH . '/configs/application.ini'
);
spl_autoload_unregister(array('Zend_Loader_Autoloader', 'autoload'));
$application->bootstrap()->run();
