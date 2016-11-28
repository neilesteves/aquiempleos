<?php

class Admin_HomeController extends App_Controller_Action_Admin
{

    public function init()
    {
        parent::init();
        /* Initialize action controller here */
    }
    
    public function dbtestAction()
    {
//        $this->_helper->layout->disableLayout();
//        $this->_helper->viewRenderer->setNoRender();
//        $tc = new Application_Model_TipoCarrera();
//        var_dump($tc->fetchAll()->toArray());
    }

    public function indexAction()
    {
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'homeAdmin', 'class' => 'noMenu noMenuAdm')
        );
        $this->view->headLink()->appendStylesheet(
                $this->view->S(
                '/css/main.administrador.css')
        );
        if ( isset($this->auth['usuario']) && 
            ($this->auth['usuario']->rol==Application_Form_Login::ROL_ADMIN_MASTER ||
            $this->auth['usuario']->rol==Application_Form_Login::ROL_ADMIN_CALLCENTER)) {
            $this->_redirect('/admin/gestion');
        }
    }
    public function  limpiarSesionAction() 
    {
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $db = $this->getAdapter();
        $db->query('TRUNCATE TABLE zend_session');
        echo "< -- Session was cleanned succes -- >";

    }
    
}

