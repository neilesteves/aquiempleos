<?php

class AuthController extends App_Controller_Action
{

    protected $_loginRequiredFor = array('logout');
    
    
    public function loginAction()
    {
        $next = $this->getRequest()->getParam('next', $this->view->baseUrl('/'));
        
        if ($this->getRequest()->isPost()) {
            $adapter = new App_Auth_Adapter_AptitusDbTable(
                $this->getAdapter()
            );

            $adapter->setIdentity($this->getRequest()->getPost('userEmail', ''));
            $adapter->setCredential($this->getRequest()->getPost('userPass', ''));
            
            $auth = Zend_Auth::getInstance();
            $result = $auth->authenticate($adapter);
            
            if ($result->isValid()) {
                # TODO : Cambiar dirección de inicio
                
            } else {
                $this->getMessenger()->error('Error al iniciar sesión');
            }
        }
        
        $this->_redirect('/');
    }
    
    public function logoutAction()
    {
        
    }
    
}