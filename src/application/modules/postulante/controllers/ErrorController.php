<?php

class Postulante_ErrorController extends Zend_Controller_Action
{

    public function init()
    {
        parent::init();

        $module     = $this->_getParam('module');
        $controller = $this->_getParam('controller');
        $action     = $this->_getParam('action');
        defined('MODULE') || define('MODULE', $module);
        defined('CONTROLLER') || define('CONTROLLER', $controller);
        defined('ACTION') || define('ACTION', $action);


        $headLinkContainer = $this->view->headLink()->getContainer();
        if (isset($headLinkContainer->{1}) && isset($headLinkContainer->{2})) {
            unset($headLinkContainer->{1});
            unset($headLinkContainer->{2});
        }



        $config = Zend_Registry::get('config');
        $view   = $this->view;
        $view->headMeta()->appendHttpEquiv('Content-Type',
            'text/html; charset=utf-8');
        $view->headTitle($config->app->title)->setSeparator(' | ');
        $view->headLink()->appendStylesheet($view->S('/css/layout.css'), 'all');
        $view->headLink()->appendStylesheet($view->S('/css/portada.css'), 'all');
        $view->headLink()->appendStylesheet($view->S('/css/icons.css'), 'all');
        $view->headLink()->appendStylesheet($view->S('/css/plugins.css'), 'all');
        $view->headLink()->appendStylesheet($view->S('/css/layout2.css'), 'all');
        $view->headLink()->appendStylesheet($view->S('/css/class.css'), 'all');
        $view->headLink()->appendStylesheet($view->S('/css/plugins/jquery.fancybox.css'),
            'all');
        $view->headLink()->appendStylesheet($view->S('/css/printCip.css'),
            'print');
        $view->headLink(array('rel' => 'shortcut icon', 'href' => $view->S('/images/favicon.ico')));

        $view->headScript()->appendFile($view->S('/js/jquery.js'));
        $view->headScript()->appendFile($view->S('/js/src/libs/jquery/jqParsley.js'));
        $view->headScript()->appendFile($view->S('/js/src/libs/jquery/jqParsley_es.js'));

        //$view->headScript()->appendFile($view->S('/js/main.js'));

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
                googleApi : '%s',
                rangoFecha : '%s',
                cantdescExp : '%s',
                maxSizeFile : '%s',
                staticCache : '%s',
                pprf : '%s',
                elementsUrlCvs : '%s'
            }", $config->app->mediaUrl, $config->app->elementsUrl,
            $config->app->siteUrl, $config->app->adminUrl, date('j'), date('n'),
            date('Y'), '1910', $config->apis->google->appid,
            $config->app->rangoFecha, $config->app->cantdescExp,
            $config->app->maxSizeFile, $config->confpaginas->staticCache,
            $config->app->publicar->preferenciales, $config->app->elementsUrlCvs
        );
        $view->headScript()->appendScript($js);
    }

    public function page404Action()
    {
        Zend_Layout::getMvcInstance()->assign('bodyAttr',
            array('id' => 'pagErrorApt'));
        Zend_Layout::getMvcInstance()->setLayout('simple');
    }

    public function warningProfilePrivateAction()
    {
        Zend_Layout::getMvcInstance()->assign('bodyAttr',
            array('id' => 'pagErrorApt'));
        Zend_Layout::getMvcInstance()->setLayout('simple');
    }

    public function errorAction()
    {
        $logError = true;
        // Auth Storage
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $authStorage = Zend_Auth::getInstance()->getStorage()->read();
            $isAuth      = true;
        } else {
            $authStorage = null;
            $isAuth      = false;
        }

        $this->view->viewLinkCuenta = $isAuth;
        if ($isAuth) {
            switch ($authStorage["usuario"]->rol) {
                case App_Controller_Action::USUARIO_POSTULANTE:
                    $this->view->miCuentaUrl    = "mi-cuenta/";
                    break;
                case App_Controller_Action::USUARIO_EMPRESA:
                case App_Controller_Action::ADMIN_EMPRESA:
                    $this->view->miCuentaUrl    = "empresa/mi-cuenta/";
                    break;
                case App_Controller_Action::USUARIO_ADMIN_CALLCENTER:
                case App_Controller_Action::USUARIO_ADMIN_DIGITADOR:
                case App_Controller_Action::USUARIO_ADMIN_MASTER:
                case App_Controller_Action::USUARIO_ADMIN_MODERADOR:
                case App_Controller_Action::USUARIO_ADMIN_SOPORTE:
                case App_Controller_Action::USUARIO_ADMIN:
                    $this->view->viewLinkCuenta = false;
                    break;
            }
        }
        $config                  = Zend_Registry::get("config");
        $error                   = $config->confpaginas->errorActivo;
        $this->view->ActivaError = $error;

        if ($error == 1) {

            if ($module == 'postulante') {
                $form                       = new Application_Form_RegistroRapidoPostulante();
                $idusuario                  = isset($this->auth['usuario']->id) ? $this->auth['usuario']->id
                        : null;
                $this->view->authEmpresa    = isset($this->auth['empresa']) ? true
                        : false;
                $this->view->authPostulante = isset($this->auth['postulante']) ? true
                        : false;
                $form->validadorEmail($idusuario, 'postulante');
                Zend_Layout::getMvcInstance()->assign(
                    'formRegistroRapido', $form
                );
            }
            Zend_Layout::getMvcInstance()->assign('loginFormNew',
                Application_Form_LoginNew::factory($module));
            Zend_Layout::getMvcInstance()->assign(
                'bodyAttr', array('id' => 'pagErrorApt')
            );
            Zend_Layout::getMvcInstance()->setLayout('error');
        } else {
            $this->_helper->layout->disableLayout();
        }

        $errors = $this->_getParam('error_handler');

        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }


        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority            = Zend_Log::NOTICE;
                $this->view->message = 'Page not found';
                $logError            = FALSE;
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority            = Zend_Log::CRIT;
                $this->view->message = 'Application error';
                break;
        }

        // Log exception, if logger available
        if ($logError && $log = $this->getLog()) {
            $log->log($this->view->message, $priority, $errors->exception);
            $log->crit($this->view->message, $errors->exception);
            $log->log('Request Parameters', $priority,
                $errors->request->getParams());
        }

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        $this->view->request = $errors->request;
    }

    /**
     * @return Zend_Log_Writer_Stream
     *
     */
    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }
}