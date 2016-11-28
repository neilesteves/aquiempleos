<?php

class App_Controller_Action_Front extends Zend_Controller_Action
{

    /**
     *
     * @var Zend_Form_Element_Hash
     */
    protected $_hash = null;

    protected $_flashMessenger = null;
    protected $_pass = false;

    const ADMIN_EMPRESA = "empresa-admin";
    const USUARIO_EMPRESA = "empresa-usuario";
    const USUARIO_POSTULANTE = "postulante";
    const USUARIO_ADMIN = "admin";
    const USUARIO_ADMIN_MASTER = 'admin-master'; //prueba
    const USUARIO_ADMIN_CALLCENTER = 'admin-callcenter';
    const USUARIO_ADMIN_SOPORTE = 'admin-soporte';
    const USUARIO_ADMIN_DIGITADOR = 'admin-digitador';
    const USUARIO_ADMIN_MODERADOR = 'admin-moderador';


    public function init()
    {
        // Auth Storage
       $module = $this->_getParam('module');
        $controller = $this->_getParam('controller');
        $action = $this->_getParam('action');

        if($module == 'admin') {
            $this->sessionAdmin = (!isset($this->sessionAdmin)) ? new Zend_Session_Namespace('admin') : null;
            //$this->sessionAdmin = new Zend_Session_Namespace('admin');
            if(!$this->sessionAdmin->auth) {
                $authStorage = null;
                $isAuth = false;
            } else {
                $authStorage = $this->sessionAdmin->auth;
                $isAuth = true;
            }
        } else {
            if(Zend_Auth::getInstance()->hasIdentity()) {
                $authStorage = Zend_Auth::getInstance()->getStorage()->read();
                $isAuth = true;
            } else {
                $authStorage = null;
                $isAuth = false;
            }
        }

        defined('MODULE') || define('MODULE', $module);
        defined('CONTROLLER') || define('CONTROLLER', $controller);
        defined('ACTION') || define('ACTION', $action);
        $config = $this->getConfig();

        //$this->_helper->layout->setLayout('main_front');
        parent::init();
    }

    public function redirectHome($rol)
    {
        switch($rol) {
            case App_Controller_Action::USUARIO_POSTULANTE:
                $this->_redirect("");
                break;
            case App_Controller_Action::USUARIO_EMPRESA:
            case App_Controller_Action::ADMIN_EMPRESA:
                $this->_redirect("empresa");
                break;
            case App_Controller_Action::USUARIO_ADMIN_CALLCENTER:
            case App_Controller_Action::USUARIO_ADMIN_MODERADOR:
            case App_Controller_Action::USUARIO_ADMIN_DIGITADOR:
            case App_Controller_Action::USUARIO_ADMIN_SOPORTE:
            case App_Controller_Action::USUARIO_ADMIN_MASTER:
            case App_Controller_Action::USUARIO_ADMIN:
                $this->_redirect("admin");
                break;
        }
    }

    public function checkAccessSec($module, $controller, $action, $controllersAndActions)
    {
        if ( array_key_exists($module, $controllersAndActions)
             && $controllersAndActions[$module] != null
             && is_array($controllersAndActions[$module])
        ) {
            if ( array_key_exists($controller, $controllersAndActions[$module])
                 && $controllersAndActions[$module][$controller] != null
                 && is_array($controllersAndActions[$module][$controller])
            ) {
                if ($controllersAndActions[$module][$controller][0] == "ALL" ) {
                    return true;
                }

                foreach ($controllersAndActions[$module][$controller] as $publicAction) {
                    if ($action == $publicAction) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function checkAccess($module, $controller, $action, $controllersAndActions)
    {
        foreach ($controllersAndActions as $publicModule) {
            if ($module == $publicModule["module"]) {
                foreach ($publicModule["controllers"] as $publicController) {
                    if ($controller == $publicController["controller"]) {
                        if ($publicController["actions"] == "ALL") {
                            return true;
                        } else {
                            foreach ($publicController["actions"] as $publicAction) {
                                if ($action == $publicAction) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Pre-dispatch routines
     * Asignar variables de entorno
     *
     * @return void
     */
    public function preDispatch()
    {

        parent::preDispatch();
        //exit;
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $authStorage = Zend_Auth::getInstance()->getStorage()->read();
            $isAuth = true;
        } else {
            $authStorage = null;
            $isAuth = false;
        }

        $config = $this->getConfig();

        $this->config = $this->getConfig();
        $this->log = $this->getLog();
        $this->mediaUrl = $this->config->app->mediaUrl;
        $this->siteUrl = $this->config->app->siteUrl;

        $this->view->assign('mediaUrl', $config->app->mediaUrl);
        $this->view->assign('elementsUrl', $config->app->elementsUrl);
        $this->view->assign('siteUrl', $config->app->siteUrl);

        $helper = $this->_helper->getHelper('FlashMessengerCustom');
        $this->_flashMessenger = $helper;
        if ($this->_request->getModuleName()=="empresa") {
            $this->view->headLink()->appendStylesheet(
                $config->app->mediaUrl . '/css/main.empresa.css?v=14', 'all'
            );
        }
        
    }

    /**
     * Post-dispatch routines
     *
     * @return void
     */
    public function postDispatch()
    {
        $module = $this->_getParam('module');
        $controller = $this->_getParam('controller');
        $action = $this->_getParam('action');
        /*
        var_dump($module);
        var_dump($controller);
        var_dump($action);
        exit;
         *
         */
        $messages = $this->_flashMessenger->getMessages();
        if ($this->_flashMessenger->hasCurrentMessages()) {
            $messages = $this->_flashMessenger->getCurrentMessages();
            $this->_flashMessenger->clearCurrentMessages();
        }
        $this->view->assign('flashMessages', $messages);
        Zend_Layout::getMvcInstance()->assign('flashMessages', $messages);





    }

    protected $_loginRequiredFor = array();
    protected $_loginUrl = '/auth/login';
    protected $_authCheckEnabled = true;
    public function checkAuth()
    {
        $action = $this->getRequest()->getActionName();
        if ($this->_authCheckEnabled == true
            && false == Zend_Auth::getInstance()->hasIdentity()
            && in_array($action, $this->_loginRequiredFor)
        ) {
            $url = $this->getRequest()->getRequestUri();
            $this->_redirect($this->_loginUrl . '?next=' . $url);
        }
    }


    /**
     * Retorna la instancia personalizada de FlashMessenger
     * Forma de uso:
     * $this->getMessenger()->info('Mensaje de información');
     * $this->getMessenger()->success('Mensaje de información');
     * $this->getMessenger()->error('Mensaje de información');
     *
     * @return App_Controller_Action_Helper_FlashMessengerCustom
     */
    public function getMessenger()
    {
        return $this->_flashMessenger;
    }

    /**
     *
     * @see Zend/Controller/Zend_Controller_Action::getRequest()
     * @return Zend_Controller_Request_Http
     */
    public function getRequest()
    {
        return parent::getRequest();
    }

    /**
     * Retorna un objeto Zend_Config con los parámetros de la aplicación
     *
     * @return Zend_Config
     */
    public function getConfig()
    {
        return Zend_Registry::get('config');
    }

    /**
     * Retorna el objeto cache de la aplicación
     *
     * @return Zend_Cache_Core
     */
    public function getCache()
    {
        return Zend_Registry::get('cache');
    }

    /**
     * Retorna el adaptador
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        return Zend_Registry::get('db');
    }

    /**
     * Retorna el objeto Zend_Log de la aplicación
     *
     * @return Zend_Log
     */
    public function getLog()
    {
        return Zend_Registry::get('log');
    }

    public function getSession()
    {
        $session = new Zend_Session_Namespace('gallitotrabajo');
        return $session;
    }
    /*
     * retorna log para insertar en la BD
     */
    public function getLogger()
    {
        return $this->logger = Zend_Registry::get('logDb');
    }

    public function getPass()
    {
        return $this->_pass;
    }
}
