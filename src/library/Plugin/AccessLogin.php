<?php

class Plugin_AccessLogin extends Zend_Controller_Plugin_Abstract
{
   
    
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {

        if ($this->getRequest()->isXmlHttpRequest()) {
            
            $config = Zend_Registry::get('config');
            $publicControllersAndActions = $config->publicAccess->toArray();
        
            $module = $this->getRequest()->getParam('module');
            $controller = $this->getRequest()->getParam('controller');
            $action = $this->getRequest()->getParam('action');

        if ($module == "admin") { 
            $this->sessionAdmin = (!isset($this->sessionAdmin))?new Zend_Session_Namespace('admin'):null;
            //$this->sessionAdmin = new Zend_Session_Namespace('admin');
            
            if(!$this->sessionAdmin->auth) {
                $authStorage = null;
                $isAuth = false;                
            } else {
                $authStorage = $this->sessionAdmin->auth;
                $isAuth = true;                
            }           
        } else {
            if (Zend_Auth::getInstance()->hasIdentity()) {
                $authStorage = Zend_Auth::getInstance()->getStorage()->read();
                $isAuth = true;
            } else {
                $authStorage = null;
                $isAuth = false;
            }
        }
            
            if (!$this->checkAccessSec($module, $controller, $action, $publicControllersAndActions)) {
                if ($module == "empresa") {
                    if (!$isAuth) {
                        $this->devolver500();
                    } else if ($authStorage["usuario"]->rol != App_Controller_Action::USUARIO_EMPRESA &&
                        $authStorage["usuario"]->rol != App_Controller_Action::ADMIN_EMPRESA
                    ) {
                        $this->devolver500();
                    } 
                } else if ($module == "postulante") {
                    if (!$isAuth) {
                        $this->devolver500();
                    } else if (
                        $authStorage["usuario"]->rol != App_Controller_Action::USUARIO_POSTULANTE
                    ) {
                        $this->devolver500();
                    }
                } else if ($module == "admin") {
                    if (!$isAuth) {
                        $this->devolver500();
                    } else if ($authStorage["usuario"]->rol != App_Controller_Action::USUARIO_ADMIN_CALLCENTER &&
                        $authStorage["usuario"]->rol != App_Controller_Action::USUARIO_ADMIN_DIGITADOR &&
                        $authStorage["usuario"]->rol != App_Controller_Action::USUARIO_ADMIN_MASTER &&
                        $authStorage["usuario"]->rol != App_Controller_Action::USUARIO_ADMIN_MODERADOR &&
                        $authStorage["usuario"]->rol != App_Controller_Action::USUARIO_ADMIN_SOPORTE 
                    ) {
                        $this->devolver500();
                    } 
                } else {
                    $this->devolver500();
                }
            }
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
    
    public function devolver500()
    {
        Zend_Auth::getInstance()->clearIdentity();
        echo '<script languaje="Javascript">location.reload(true);</script>';
        exit;
      
    }
    
}