<?php

class App_Controller_Action extends Zend_Controller_Action
{
    /**
     *
     * @var Zend_Form_Element_Hash
     */
    protected $_hash           = null;
    protected $_flashMessenger = null;
    protected $_pass           = false;
    public $sessionAdmin;

    const ADMIN_EMPRESA            = "empresa-admin";
    const USUARIO_EMPRESA          = "empresa-usuario";
    const USUARIO_POSTULANTE       = "postulante";
    const USUARIO_ADMIN            = "admin";
    const USUARIO_ADMIN_MASTER     = 'admin-master'; //prueba
    const USUARIO_ADMIN_CALLCENTER = 'admin-callcenter';
    const USUARIO_ADMIN_SOPORTE    = 'admin-soporte';
    const USUARIO_ADMIN_DIGITADOR  = 'admin-digitador';
    const USUARIO_ADMIN_MODERADOR  = 'admin-moderador';

    public function init()
    {
        // Auth Storage
        $module     = $this->_getParam('module');
        $controller = $this->_getParam('controller');
        $action     = $this->_getParam('action');
        if ($module == 'admin') {
            $this->sessionAdmin = (!isset($this->sessionAdmin)) ? new Zend_Session_Namespace('admin')
                    : null;
            //$this->sessionAdmin = new Zend_Session_Namespace('admin');
            if (!$this->sessionAdmin->auth) {
                $authStorage = null;
                $isAuth      = false;
            } else {
                $authStorage = $this->sessionAdmin->auth;
                $isAuth      = true;
            }
        } else {
            if (Zend_Auth::getInstance()->hasIdentity()) {
                $authStorage = Zend_Auth::getInstance()->getStorage()->read();
                $isAuth      = true;
            } else {
                $authStorage = null;
                $isAuth      = false;
            }
        }

        defined('MODULE') || define('MODULE', $module);
        defined('CONTROLLER') || define('CONTROLLER', $controller);
        defined('ACTION') || define('ACTION', $action);
        $config = $this->getConfig();
        $view   = $this->view;
        if ($this->view->Util()->getPostulanteValid()) {
            $view->headTitle($config->app->title)->setSeparator(' | ');
            $view->headLink()->appendStylesheet($view->S('/eb/css/empleo.busco.css'),
                'all');
            $view->headLink()->appendStylesheet($view->S('/eb/css/bootstrap-theme.min.css'),
                'all');
            $view->headLink()->appendStylesheet($view->S('/eb/font-awesome/css/font-awesome.min.css'),
                'all');
            $view->headLink()->appendStylesheet($view->S('/eb/css/utilities.css'),
                'all');
            $view->headLink()->appendStylesheet($view->S('/eb/css/header.css'),
                'all');
            $view->headLink()->appendStylesheet($view->S('/eb/css/footer.css'),
                'all');

            $view->headLink()->appendStylesheet($view->S('/main/css/layout/fonts.css'),
                'all');
            $view->headLink()->appendStylesheet($view->S('/main/css/layout/layout-old.css'),
                'all');
            $view->headLink()->appendStylesheet($view->S('/main/css/modules/'.MODULE.'/all.css'),
                'all');
            $view->headLink()->appendStylesheet($view->S('/main/css/modules/'.MODULE.'/'.CONTROLLER.'.css'),
                'all');


            $view->headLink(array(
                'rel' => 'icon',
                'href' => $view->S('/img/favicon.ico'),
                'type' => 'image/vnd.microsoft.icon'));
            $view->headLink(array(
                'rel' => 'shortcut icon',
                'href' => $view->S('/img/favicon.ico'),
                'type' => 'image/vnd.microsoft.icon'));
            $view->headLink(array(
                'rel' => 'shortcut icon',
                'href' => $view->S('/img/favicon.ico'),
                'type' => 'image/x-icon'));
            $view->headLink(array(
                'rel' => 'image_src',
                'href' => $view->S('/img/favicon.ico'),
                'type' => 'image/jpeg'));

            $view->headScript()->appendFile($view->S('/main/js/libs/jquery/dist/jquery.min.js'));
            $view->headScript()->appendFile($view->S('/main/js/libs/jquery.mobile.js'));
            $view->headScript()->appendFile($view->S('/main/js/libs/yoson_files/yosonjs-utils.js'));
            $view->headScript()->appendFile($view->S('/main/js/libs/yosonjs/build/yoson-min.js'));
            $view->headScript()->appendFile($view->S('/main/js/modules/all/all.js'));

            if (file_exists($config->urls->app->mediaRoot.'/main/js/modules/'.MODULE.'/'.CONTROLLER.'/'.CONTROLLER.'.js')) {
                $view->headScript()->appendFile($view->S('/main/js/modules/'.MODULE.'/'.CONTROLLER.'/'.CONTROLLER.'.js'));
            }
            if (file_exists($config->urls->app->mediaRoot.'/main/js/modules/'.MODULE.'/'.CONTROLLER.'/'.ACTION.'/'.ACTION.'.js')) {
                $view->headScript()->appendFile($view->S('/main/js/modules/'.MODULE.'/'.CONTROLLER.'/'.ACTION.'/'.ACTION.'.js'));
            }
            $view->headScript()->appendFile($view->S('/main/js/libs/yoson_files/modules.js'));
            $view->headScript()->appendFile($view->S('/main/js/libs/yoson_files/appLoad.js'));
            //  $view->headScript()->appendFile($view->S('/eb/js/jquery.js'));
            // $view->headScript()->appendFile($view->S('/eb/js/bootstrap.min.js'));
            Zend_Layout::getMvcInstance()->assign('loginFormNew',
                Application_Form_LoginNew::factory(MODULE));
            if (!$isAuth) {
                Zend_Layout::getMvcInstance()->assign(
                    'newrecuperarClaveForm',
                    Application_Form_NewRecuperarClave::factory(
                        Application_Form_Login::ROL_POSTULANTE
                    )
                );
            }
            $this->_helper->layout->setLayout('main_portal');
        } elseif ($this->view->Util()->getEmpresaValid()) {
            Zend_Layout::getMvcInstance()->assign('loginFormNew',
                Application_Form_LoginNew::factory(
                    Application_Form_LoginNew::ROL_EMPRESA));
            Zend_Layout::getMvcInstance()->assign('headMeta',
                $config
                ->headMeta->app->toArray());
            Zend_Layout::getMvcInstance()->assign('regEmp', 'registro-empresa');
            if (!$isAuth) {
                Zend_Layout::getMvcInstance()->assign(
                    'newrecuperarClaveForm',
                    Application_Form_NewRecuperarClave::factory(
                        Application_Form_Login::ROL_EMPRESA
                    )
                );
            }

            $view->headScript()->appendFile($view->S('/eb/js/productos.js'));

            $this->_helper->layout->setLayout('main_portal_empresa');
        } elseif ($this->view->Util()->getPostulanteMainValid()) {
            Zend_Layout::getMvcInstance()->assign('loginFormNew',
                Application_Form_LoginNew::factory(
                    Application_Form_LoginNew::ROL_POSTULANTE));
            Zend_Layout::getMvcInstance()->assign('headMeta',
                $config
                ->headMeta->app->toArray());
            Zend_Layout::getMvcInstance()->assign('regEmp', 'registro-empresa');
            Zend_Layout::getMvcInstance()->assign(
                'newrecuperarClaveForm',
                Application_Form_NewRecuperarClave::factory(
                    Application_Form_Login::ROL_POSTULANTE
                )
            );
            Zend_Layout::getMvcInstance()->assign('facebook',
                $this->_helper->AuthFacebook->Ulrlogin());
            $this->_helper->AuthFacebook->setUrlReturn('/mi-cuenta/');

            $view->headScript()->appendFile($view->S('/eb/js/jquery.js'));
            $view->headScript()->appendFile($view->S('/eb/js/bootstrap.min.js'));
            $view->headScript()->appendFile($view->S('/eb/slick/slick.js'));
            $view->headScript()->appendFile($view->S('/eb/js/main.js'));
            $view->headScript()->appendFile($view->S('/eb/js/empleo-busco.js'));
            $view->headScript()->appendFile($view->S('/eb/js/src/lib/jquery.easy-autocomplete.min.js'));
            $view->headScript()->appendFile($view->S('/eb/js/'.MODULE.'/'.CONTROLLER.'/'.ACTION.'.js'));
            $this->_helper->layout->setLayout('main_portal_empleo_busco');
        } else {
            $view->headMeta()->appendHttpEquiv('Content-Type',
                'text/html; charset=utf-8');
            $view->headTitle($config->app->title)->setSeparator(' | ');
            $view->headLink()->appendStylesheet($view->S('/css/layout.css'),
                'all');
            $view->headLink()->appendStylesheet($view->S('/css/portada.css'),
                'all');
            $view->headLink()->appendStylesheet($view->S('/css/icons.css'),
                'all');
            $view->headLink()->appendStylesheet($view->S('/css/plugins.css'),
                'all');
            $view->headLink()->appendStylesheet($view->S('/css/layout2.css'),
                'all');
            $view->headLink()->appendStylesheet($view->S('/css/class.css'),
                'all');
            $view->headLink()->appendStylesheet($view->S('/css/plugins/jquery.fancybox.css'),
                'all');
            $view->headLink()->appendStylesheet($view->S('/css/printCip.css'),
                'print');
            $view->headLink(array(
                'rel' => 'shortcut icon',
                'href' => $view->S('/img/favicon.ico')));
            $view->headScript()->appendFile($view->S('/js/jquery.js'));
            $view->headScript()->appendFile($view->S('/js/src/libs/jquery/jqParsley.js'));
            $view->headScript()->appendFile($view->S('/js/src/libs/jquery/jqParsley_es.js'));

            $js = sprintf(
                "var urls = {
                    mediaUrl : '%s',
                    elementsUrl : '%s',
                    siteUrl : '%s',
                    adminUrl : '%s',
                    fDayCurrent : %s,
                    fMonthCurrent : %s,
                    fYearCurrent : %s,
                    fMinDate : %s,

                    rangoFecha : '%s',
                    cantdescExp : '%s',
                    maxSizeFile : '%s',
                    staticCache : '%s',
                    pprf : '%s',
                    elementsUrlCvs : '%s'
                }", $config->app->mediaUrl, $config->app->elementsUrl,
                $config->app->siteUrl, $config->app->adminUrl, date('j'),
                date('n'), date('Y'),
                '1910',
                //$config->apis->google->appid,
                $config->app->rangoFecha, $config->app->cantdescExp,
                $config->app->maxSizeFile, $config->confpaginas->staticCache,
                '',
                $config->app->elementsUrlCvs
            );


            $view->headScript()->appendScript($js);
        }
        $this->auth = $authStorage;
        $this->view->assign('auth', $authStorage);
        Zend_Layout::getMvcInstance()->assign('auth', $authStorage);
        Zend_Layout::getMvcInstance()->assign('modulo',
            $this->getRequest()->getModuleName());
        Zend_Layout::getMvcInstance()->assign('title', $config->app->title);
        Zend_Layout::getMvcInstance()->assign('staticCache',
            $config->confpaginas->staticCache);
        Zend_Layout::getMvcInstance()->assign('loginForm',
            Application_Form_Login::factory(Application_Form_Login::ROL_POSTULANTE));
        Zend_Layout::getMvcInstance()->assign('recuperarClaveForm',
            Application_Form_RecuperarClave::factory(Application_Form_Login::ROL_POSTULANTE));

        $this->isAuth = $isAuth;
        $this->view->assign('isAuth', $isAuth);
        Zend_Layout::getMvcInstance()->assign('isAuth', $isAuth);
        if (isset($this->auth) && !isset($this->auth['usuario'])) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::forgetMe();
        }
        Zend_Layout::getMvcInstance()->assign('hashForm',
            new Application_Form_HashForm());


        $params                               = $this->_getAllParams();
        $params['REQUEST_URI']                = $this->getRequest()->getServer('REQUEST_URI');
        Zend_Layout::getMvcInstance()->params = $params;

        $publicControllersAndActions = $this->getConfig()->publicAccess->toArray();
        $pass                        = false;
        $helper                      = $this->_helper->getHelper('FlashMessengerCustom');
        $this->_flashMessenger       = $helper;

        if (!$this->checkAccessSec($module, $controller, $action,
                $publicControllersAndActions)) {
            if ($module == "empresa") {
                if (!$isAuth) {
                    $this->_redirect(
                        "empresa/#loginP-".
                        base64_encode($params['REQUEST_URI'])
                    );
                } else if ($this->auth["usuario"]->rol != App_Controller_Action::USUARIO_EMPRESA
                    &&
                    $this->auth["usuario"]->rol != App_Controller_Action::ADMIN_EMPRESA
                ) {
                    $this->getMessenger()->error(
                        "No tiene permisos de acceso para esta solicitud."
                    );
                    $this->redirectHome($this->auth["usuario"]->rol);
                } else {
                    $pass = true;
                }
            } else if ($module == "postulante") {
                if (!$isAuth) {
                    $this->_redirect(
                        "#loginP-".base64_encode("/".$module."/".$controller."/".$action)
                    );
                } else if (
                    $this->auth["usuario"]->rol != App_Controller_Action::USUARIO_POSTULANTE
                ) {
                    $this->getMessenger()->error(
                        "No tiene permisos de acceso para esta solicitud."
                    );
                    $this->redirectHome($this->auth["usuario"]->rol);
                } else {
                    $pass = true;
                }
            } else if ($module == "admin") {
                if (!$isAuth) {
                    $this->_redirect("");
                } else if ($this->auth["usuario"]->rol != App_Controller_Action::USUARIO_ADMIN_CALLCENTER
                    &&
                    $this->auth["usuario"]->rol != App_Controller_Action::USUARIO_ADMIN_DIGITADOR
                    &&
                    $this->auth["usuario"]->rol != App_Controller_Action::USUARIO_ADMIN_MASTER
                    &&
                    $this->auth["usuario"]->rol != App_Controller_Action::USUARIO_ADMIN_MODERADOR
                    &&
                    $this->auth["usuario"]->rol != App_Controller_Action::USUARIO_ADMIN_SOPORTE
                ) {
                    $this->getMessenger()->error(
                        "No tiene permisos de acceso para esta solicitud."
                    );
                    $this->redirectHome($this->auth["usuario"]->rol);
                } else {
                    $pass = true;
                }
            } else {
                $this->_redirect("");
            }
        }

        $this->_pass = $pass;


        //if ($this->_hash == null) {
        $this->_hash = new Zend_Form_Element_Hash('csrf_hash',
            array(
            'salt' => 'exitsalt'));
        //}

        $this->_hash->setTimeout(3600);
        $this->_hash->initCsrfToken();
        $csrfhash             = $this->_hash->getValue();
        $this->view->csrfhash = $csrfhash;
        defined('CSRF_HASH') || define('CSRF_HASH', $csrfhash);
        // $this->_helper->layout->setLayout('main_empresa');
        $urlaquiempleos =  $this->getConfig()->app->siteUrlAquiempleos;
        Zend_Layout::getMvcInstance()->assign('aquiempleos', $urlaquiempleos);
        parent::init();
    }

    public function redirectHome($rol)
    {
        switch ($rol) {
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

    public function checkAccessSec($module, $controller, $action,
                                   $controllersAndActions)
    {
        if (array_key_exists($module, $controllersAndActions) && $controllersAndActions[$module]
            != null && is_array($controllersAndActions[$module])
        ) {
            if (array_key_exists($controller, $controllersAndActions[$module]) && $controllersAndActions[$module][$controller]
                != null && is_array($controllersAndActions[$module][$controller])
            ) {
                if ($controllersAndActions[$module][$controller][0] == "ALL") {
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

    public function Empresa_Ne($param)
    {

    }

    public function checkAccess($module, $controller, $action,
                                $controllersAndActions)
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
            $isAuth      = true;
        } else {
            $authStorage = null;
            $isAuth      = false;
        }

        $config = $this->getConfig();

        $this->config   = $this->getConfig();
        $this->log      = $this->getLog();
        $this->mediaUrl = $this->config->app->mediaUrl;
        $this->siteUrl  = $this->config->app->siteUrl;

        $this->view->assign('mediaUrl', $config->app->mediaUrl);
        $this->view->assign('elementsUrl', $config->app->elementsUrl);
        $this->view->assign('siteUrl', $config->app->siteUrl);

        $helper                = $this->_helper->getHelper('FlashMessengerCustom');
        $this->_flashMessenger = $helper;
        if ($this->_request->getModuleName() == "empresa") {
            $this->view->headLink()->appendStylesheet(
                Zend_Layout::getMvcInstance()->getView()->S('/css/empresa/empresa.layout.css'),
                'all'
            );

            $this->view->headLink()->appendStylesheet(
                Zend_Layout::getMvcInstance()->getView()->S('/css/empresa/empresa.class.css'),
                'all'
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
        $messages = $this->_flashMessenger->getMessages();
        if ($this->_flashMessenger->hasCurrentMessages()) {
            $messages = $this->_flashMessenger->getCurrentMessages();
            $this->_flashMessenger->clearCurrentMessages();
        }
        $this->view->assign('flashMessages', $messages);
        Zend_Layout::getMvcInstance()->assign('flashMessages', $messages);
    }
    protected $_loginRequiredFor = array();
    protected $_loginUrl         = '/auth/login';
    protected $_authCheckEnabled = true;

    public function checkAuth()
    {
        $action = $this->getRequest()->getActionName();
        if ($this->_authCheckEnabled == true && false == Zend_Auth::getInstance()->hasIdentity()
            && in_array($action, $this->_loginRequiredFor)
        ) {
            $url = $this->getRequest()->getRequestUri();
            $this->_redirect($this->_loginUrl.'?next='.$url);
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
        $session = new Zend_Session_Namespace('aptitus');
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
