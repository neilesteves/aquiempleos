<?php

/*
 * Configura el entorno para la ejecución de tareas programadas
 */

// Define path to application directory
defined('APPLICATION_PATH')
    || define(
        'APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application')
    );
// Ensure library/ is on include_path
$paths = array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(APPLICATION_PATH . '/../vendor'),
    realpath(APPLICATION_PATH . '/models'),
    realpath(APPLICATION_PATH . '/forms'),
    get_include_path()
);

set_include_path(implode(PATH_SEPARATOR, $paths));

require 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
$config = new Zend_Config_Ini(APPLICATION_PATH.'/configs/private.ini');
require_once 'App/Application.php';

// Define application environment
defined('APPLICATION_ENV')
    || define(
        'APPLICATION_ENV', (
            getenv('APPLICATION_ENV') ?
            getenv('APPLICATION_ENV') : $config->env
        )
    );



/** Zend_Application */

$inis = array(
        'application.ini',
        'aquiempleos.ini', 
        'routes.ini',
        'cache.ini',
        'adecsys.ini',
        'security.ini',
        'scot.ini',
        'private.ini'
        );

/** Zend_Application */
// Create application, bootstrap, and run
$application = new App_Application(
        APPLICATION_ENV, $inis
);

$application->bootstrap();

define('JOBS_PATH', realpath(dirname(__FILE__)));
$config = Zend_Registry::get('config');
