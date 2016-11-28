<?php

class Api_Action extends Zend_Controller_Action
{

    public function init()
    {
        // Auth Storage
        if(Zend_Auth::getInstance()->hasIdentity()) {
            $authStorage = Zend_Auth::getInstance()->getStorage()->read();
            $isAuth = true;
        } else {
            $authStorage = null;
            $isAuth = false;
        }

        $this->auth = $authStorage;
        $this->view->assign('auth', $authStorage);
        Zend_Layout::getMvcInstance()->assign('auth', $authStorage);
        Zend_Layout::getMvcInstance()->assign(
                'modulo', $this->getRequest()->getModuleName()
        );
        $config = $this->getConfig();
        Zend_Layout::getMvcInstance()->assign(
                'title', $config->app->title
        );
        Zend_Layout::getMvcInstance()->assign(
                'staticCache', $config->confpaginas->staticCache
        );




        Zend_Layout::getMvcInstance()->assign(
                'loginForm', Application_Form_Login::factory(
                        Application_Form_Login::ROL_POSTULANTE
                )
        );
        Zend_Layout::getMvcInstance()->assign(
                'recuperarClaveForm', Application_Form_RecuperarClave::factory(
                        Application_Form_Login::ROL_POSTULANTE
                )
                //new Application_Form_RecuperarClave()
        );
        $this->isAuth = $isAuth;
        $this->view->assign('isAuth', $isAuth);
        Zend_Layout::getMvcInstance()->assign('isAuth', $isAuth);
        Zend_Layout::getMvcInstance()->assign(
                'hashForm', new Application_Form_HashForm()
        );
        $js = "var modulo_actual='" . $this->getRequest()->getModuleName() . "';";
        $this->view->headScript()->appendScript($js);
        $module = $this->_getParam('module');
        $controller = $this->_getParam('controller');
        $action = $this->_getParam('action');
        $params = $this->_getAllParams();
        $params['REQUEST_URI'] = $this->getRequest()->getServer('REQUEST_URI');

        defined('MODULE') || define('MODULE', $module);
        defined('CONTROLLER') || define('CONTROLLER', $controller);
        defined('ACTION') || define('ACTION', $action);
        Zend_Layout::getMvcInstance()->params = $params;

        $publicControllersAndActions = $this->getConfig()->publicAccess->toArray();

        $pass = false;

        $helper = $this->_helper->getHelper('FlashMessengerCustom');
        $this->_flashMessenger = $helper;
        $contol = $config->views->toArray();
        if(isset($contol[MODULE][CONTROLLER][ACTION])) {
            $this->_helper->layout->setLayout('main_portada');
        }
        if(!$this->checkAccessSec($module, $controller, $action, $publicControllersAndActions)) {
            if($module == "empresa") {
                if(!$isAuth) {
                    $this->_redirect(
                            "empresa/#loginP-" .
                            base64_encode("/" . $module . "/" . $controller . "/" . $action)
                    );
                } else if($this->auth["usuario"]->rol != App_Controller_Action::USUARIO_EMPRESA &&
                        $this->auth["usuario"]->rol != App_Controller_Action::ADMIN_EMPRESA
                ) {
                    $this->getMessenger()->error(
                            "No tiene permisos de acceso para esta solicitud."
                    );
                    $this->redirectHome($this->auth["usuario"]->rol);
                } else {
                    $pass = true;
                }
            } else if($module == "postulante") {
                if(!$isAuth) {


                    $this->_redirect(
                            "#loginP-" . base64_encode("/" . $module . "/" . $controller . "/" . $action)
                    );
                } else if(
                        $this->auth["usuario"]->rol != App_Controller_Action::USUARIO_POSTULANTE
                ) {
                    $this->getMessenger()->error(
                            "No tiene permisos de acceso para esta solicitud."
                    );
                    $this->redirectHome($this->auth["usuario"]->rol);
                } else {
                    $pass = true;
                }
            } else if($module == "admin") {
                if(!$isAuth) {
                    $this->_redirect("");
                } else if($this->auth["usuario"]->rol != App_Controller_Action::USUARIO_ADMIN_CALLCENTER &&
                        $this->auth["usuario"]->rol != App_Controller_Action::USUARIO_ADMIN_DIGITADOR &&
                        $this->auth["usuario"]->rol != App_Controller_Action::USUARIO_ADMIN_MASTER &&
                        $this->auth["usuario"]->rol != App_Controller_Action::USUARIO_ADMIN_MODERADOR &&
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
        $this->_hash = new Zend_Form_Element_Hash('csrf_hash', array('salt' => 'exitsalt'));
        //}

        $this->_hash->setTimeout(3600);
        $this->_hash->initCsrfToken();
        $csrfhash = $this->_hash->getValue();
        $this->view->csrfhash = $csrfhash;
        defined('CSRF_HASH') || define('CSRF_HASH', $csrfhash);



        parent::init();
    }

}
