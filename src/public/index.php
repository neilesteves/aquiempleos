<?php

//error_reporting(E_DEPRECATED & ~E_ALL & ~E_NOTICE & ~E_WARNING);



// Define path to application directory
defined('APPLICATION_PATH') || define(
                'APPLICATION_PATH', realpath(__DIR__ . '/../application')
);

// Ensure library/ is on include_path
$paths = array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/../vendor'),
    realpath(APPLICATION_PATH . '/models'),
    realpath(APPLICATION_PATH . '/forms'),
    // '/usr/share/php/libzend-framework-php',
    get_include_path()
);


set_include_path(implode(PATH_SEPARATOR, $paths));
require_once realpath(APPLICATION_PATH . '/../vendor/autoload.php');
require 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
require_once 'App/Application.php';
$config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/private.ini');

// Define application environment
defined('APPLICATION_ENV') || define(
                'APPLICATION_ENV', (
                getenv('APPLICATION_ENV') ?
                        getenv('APPLICATION_ENV') : $config->env
                )
);
$applicationini = 'application.ini';
$lc_file = APPLICATION_PATH . '/configs/local.ini' ;
if (is_readable($lc_file)) {
    $applicationini = 'local.ini';
}
$inis = array(
    $applicationini,
    'aquiempleos.ini',
    'routes.ini',
    'cache.ini',
    'cachepage.php',
    'adecsys.ini',
    'security.ini',
    'scot.ini',
    'frontend.ini',
    'private.ini',
        // 'docker.php'
);
/** Zend_Application */
// Create application, bootstrap, and run
$application = new App_Application(
        APPLICATION_ENV, $inis
);

$application->bootstrap()->run();
