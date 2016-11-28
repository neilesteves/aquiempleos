<?php

class App_Controller_Action_Helper_Ajax 
    extends Zend_Controller_Action_Helper_Abstract
{
    
    

    public function init()
    {
        parent::init();
        
        if ($this->getFrontController()->getRequest()->isXmlHttpRequest()) {
//            Zend_Controller_Action_HelperBroker::getExistingHelper('layout')->disableLayout();
//            Zend_Controller_Action_HelperBroker::getExistingHelper('viewRenderer')->setNoRender();
        }
        
        
    }
    
    public function preDispatch()
    {
        
        parent::preDispatch();
        
        if (!$this->getFrontController()->getRequest()->isPost()) {
            //exit;
        }
        
        
    }

}