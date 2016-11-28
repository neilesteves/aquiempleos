<?php

class Postulante_ApiScotController extends App_Controller_Action_Postulante
{

    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        parent::init();
    }

    public function indexAction()
    {

        $classService = "App_Scot_WebService";

        if ('GET' == $_SERVER['REQUEST_METHOD']) {
            $autodiscover = new Zend_Soap_AutoDiscover();
            $autodiscover->setClass($classService);
            $autodiscover->handle();
        } else {
            $soap = new Zend_Soap_Server();
            $soap->setClass($classService);
            
            $config = Zend_Registry::get("config");
            // @codingStandardsIgnoreStart
            $setUrl = $config->SCOT->aptitus->finalizaOT;
            // @codingStandardsIgnoreEnd
            $soap->setUri($setUrl);
            $soap->handle();
        }
    }
    
}