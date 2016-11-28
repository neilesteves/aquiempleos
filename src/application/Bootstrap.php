<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    public function _initConfig()
    {
        $config = new Zend_Config($this->getOptions(), true);
        Zend_Registry::set('config', $config);
    }

    public function _initViewHelpers()
    {
        $config = Zend_Registry::get('config');
        $doctypeHelper = new Zend_View_Helper_Doctype();
        $doctypeHelper->doctype(Zend_View_Helper_Doctype::XHTML1_TRANSITIONAL);
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
        $view = $layout->getView();
        $view->addHelperPath('App/View/Helper', 'App_View_Helper');

        //Definiendo Constante para Partials
        defined('DESCARGA') || define('DESCARGA', $config->app->mediaUrl . '/landing/media/');
        defined('MEDIALANDING') || define('MEDIALANDING', $config->app->mediaUrl . '/landing/');

        defined('MEDIA_URL') || define('MEDIA_URL', $config->app->mediaUrl);
        defined('ELEMENTS_URL_IMG') || define('ELEMENTS_URL_IMG', $config->app->elementsUrlImg);
        defined('ELEMENTS_URL_CVS') || define('ELEMENTS_URL_CVS', $config->app->elementsUrlCvs);
        defined('ELEMENTS_URL_LOGOS') || define('ELEMENTS_URL_LOGOS', $config->app->elementsUrlLogos);
        defined('ELEMENTS_URL_NOTAS') || define('ELEMENTS_URL_NOTAS', $config->app->elementsUrlNotas);
        defined('ELEMENTS_ROOT_IMG') || define('ELEMENTS_ROOT_IMG', $config->urls->app->elementsImgRoot);
        defined('ELEMENTS_ROOT_CVS') || define('ELEMENTS_ROOT_CVS', $config->urls->app->elementsCvRoot);
        defined('ELEMENTS_ROOT_LOGOS') || define('ELEMENTS_ROOT_LOGOS', $config->urls->app->elementsLogosRoot);
        defined('APTITUS_BUSQUEDA_DESTACADOS') || define('APTITUS_BUSQUEDA_DESTACADOS', $config->destacados->prioridad->hasta);
        defined('SITE_URL') || define('SITE_URL', $config->app->siteUrl);
        defined('AMBIENTE') || define('AMBIENTE', $config->ambiente->entorno);
        defined('CORREO_NOTIFICACIONES') || define('CORREO_NOTIFICACIONES', $config->resources->mail->defaultFrom->envio);
        defined('CORREO_INFO') || define('CORREO_INFO', $config->resources->mail->contactanos->empresa);
        //resources->mail->defaultFrom->envio change por resources->mail->defaultFrom->envio
        //------------------------------------------
        //------------------------------------------
    }

    public function _initActionHelpers()
    {
        Zend_Controller_Action_HelperBroker::addHelper(new App_Controller_Action_Helper_Ajax());
    }

    public function _initLibrerias()
    {
        define("DOMPDF_ENABLE_REMOTE", true);
        //define("DOMPDF_ENABLE_PHP", true);
        require_once( APPLICATION_PATH . "/../library/Dompdf/dompdf_config.inc.php");
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->pushAutoloader("DOMPDF_autoload");
        require_once( APPLICATION_PATH . "/../library/ZendImage/zendimage.php");
        require_once( APPLICATION_PATH . "/../library/ZendLucene/zendlucene.php");
        require_once( APPLICATION_PATH . "/../library/NuSoap/nusoap.php");
    }

    public function _initRegistries()
    {
        $this->_executeResource('cachemanager');
        $cacheManager = $this->getResource('cachemanager');
        Zend_Registry::set('cache', $cacheManager->getCache('appdata'));
        Zend_Registry::set('CachePage', $cacheManager->getCache('page'));
        Zend_Registry::set('outputCache', $cacheManager->getCache('Memcached'));

        $this->_executeResource('db');
        $adapter = $this->getResource('db');
        Zend_Registry::set('db', $adapter);

        $this->_executeResource('log');
        $log = $this->getResource('log');
        Zend_Registry::set('log', $log);

        //Creacion de un Log para BD
        $columnMapping = array(
            'idusuario' => 'idusuario',
            'email' => 'email',
            'rol' => 'rol',
            'message' => 'message',
            'timestamp' => 'timestamp',
            'userIp' => 'userip',
            'userHost' => 'userhost'
        );
        $logger = new Zend_Log(new Zend_Log_Writer_Db($adapter, "log", $columnMapping));
        Zend_Registry::set('logDb', $logger);
    }

    public function _initTranslate()
    {
        $translator = new Zend_Translate(
                Zend_Translate::AN_ARRAY, APPLICATION_PATH . '/configs/locale/', 'es', array('scan' => Zend_Translate::LOCALE_DIRECTORY)
        );

        Zend_Validate_Abstract::setDefaultTranslator($translator);
    }

    public function _initCachePage()
    {
        if(isset($_SERVER['SERVER_NAME']) && isset($_SERVER['QUERY_STRING'])) {
            $id = $_SERVER['SERVER_NAME'] . $_SERVER['QUERY_STRING'];
            $id .= isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : NULL;

            $nameCache = md5($id);
            $config = $this->getOptions();
            if(!isset($config['app']['page']['cache'])) {
                Zend_Registry::get('CachePage')->start($nameCache);
            }
        }
    }

    public function _initPlugins()
    {
        Zend_Controller_Action_HelperBroker::addHelper(
                new App_Controller_Action_Helper_AdminVhost()
        );

        Zend_Controller_Action_HelperBroker::addHelper(
                new App_Controller_Action_Helper_LimitarOpcionesEmpresa()
        );
    }

    protected function _initLog()
    {

        $options = $this->getOption('resources');
        $logOptions = $options['log'];
        $partitionConfig = $this->getOption('log');

        if(isset($partitionConfig['partitionFrequency'])) {
            $baseFilenameApplication = $logOptions['stream']['writerParams']['stream'];
            $baseFilenameInfo = $logOptions['info']['writerParams']['stream'];
            $logFilenameApp = '';
            $logFilenameInfo = '';
            switch(strtolower($partitionConfig['partitionFrequency'])) {
                case 'daily':
                    $logFilenameApp = $baseFilenameApplication . '_' . date('Y_m_d');
                    $logFilenameInfo = $baseFilenameInfo . '_' . date('Y_m_d');
                    break;
                case 'weekly':
                    $logFilenameApp = $baseFilenameApplication . '_' . date('Y_W');
                    $logFilenameInfo = $baseFilenameInfo . '_' . date('Y_W');
                    break;
                case 'monthly':
                    $logFilenameApp = $baseFilenameApplication . '_' . date('Y_m');
                    $logFilenameInfo = $baseFilenameInfo . '_' . date('Y_m');
                    break;
                default:
                    $logFilenameApp = $baseFilenameApplication;
                    $logFilenameInfo = $baseFilenameInfo;
            }

            $logOptions['stream']['writerParams']['stream'] = $logFilenameApp;
            $logOptions['info']['writerParams']['stream'] = $logFilenameInfo;
        }

        try {

            $logger = Zend_Log::factory($logOptions);
            Zend_Registry::set('logger', $logger);
            return $logger;
        } catch(Exception $ex) {
            /**
             * @todo
             */
            $logger = Zend_Log::factory($options['log']);
            Zend_Registry::set('logger', $logger);
            return $logger;
        }
    }

    /**
     * Initialize the Session Id
     * This code initializes the session and then
     * will ensure that we force them into an id to
     * prevent session fixation / hijacking.
     *
     * @return void
     */
//    protected function _initSessionId()
//    {
//        $this->bootstrap('session');
//        $opts = $this->getOptions();
//        if('Zend_Session_SaveHandler_Cache' == $opts['resources']['session']['saveHandler']['class']) {
//            $cache = $this->bootstrap('cachemanager')
//                    ->getResource('cachemanager')
//                    ->getCache('memcached');
//            Zend_Session::getSaveHandler()->setCache($cache);
//        }
//        $defaultNamespace = new Zend_Session_Namespace();
//        if(!isset($defaultNamespace->initialized)) {
//            Zend_Session::regenerateId();
//            $defaultNamespace->initialized = true;
//        }
//    }

}
