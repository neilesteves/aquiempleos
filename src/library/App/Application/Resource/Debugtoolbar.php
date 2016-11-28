<?php

class App_Application_Resource_DebugToolbar extends Zend_Application_Resource_ResourceAbstract {

    public function init() {
        $bootstrap = $this->getBootstrap();

        $options = $this->getOptions();

        // if plugins options is empty then set default
        if (!(isset($options['plugins']) && is_array($options['plugins']))) {
            $options['plugins'] = array(
                'Variables',
                'File' => array('base_path' => realpath(APPLICATION_PATH . '/../')),
                'Html',
                'Memory',
                'Time',
                'Cache' => array('backend' => Zend_Registry::get("cache")->getBackend()),
                'Exception'
            );
        }

        $debug = new App_Controller_Plugin_Debug($options);

        // Instantiate the database adapter and setup the plugin.
        // Alternatively just add the plugin like above and rely on the autodiscovery feature.
        if ($bootstrap->hasPluginResource('db')) {
            $bootstrap->bootstrapDb();
            $db = $bootstrap->getPluginResource('db')->getDbAdapter();
            $options['plugins']['Database']['adapter'] = $db;
        }

        // Setup the cache plugin
        if ($bootstrap->hasPluginResource('cache')) {
            $bootstrap->bootstrapCache();
            $cache = $bootstrap - getPluginResource('cache')->getDbAdapter();
            $options['plugins']['Cache']['backend'] = $cache->getBackend();
        }

        $bootstrap->bootstrapFrontController();
        $frontController = $bootstrap->getResource('frontController');
        $frontController->registerPlugin($debug);
    }

}