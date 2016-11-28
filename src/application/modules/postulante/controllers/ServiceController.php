<?php

class Postulante_ServiceController extends Zend_Rest_Controller {


    public function init() {
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
    }

    public function indexAction() {
        
        $config = Zend_Registry::get('config');
        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;

        $nid = $this->_getParam('nid');
        $api_key = $this->_getParam('api_key');

        if ($buscamasConsumerKey != $api_key) {
            
            header ("Content-Type:text/xml");  
            echo '<?xml version="1.0" encoding="utf-8"?><xml><item>Api key not valid</item></xml>';
            
        } else {
            
            $appBuscaMas = new App_Buscamas;
            
            //Actualizar aviso en APiTUS. buscamas = 1
            $anuncio = new Application_Model_AnuncioWeb;
            $idAviso = $nid;

            $data = array('buscamas' => 1);
            $where = array('id = ?' => $idAviso);
            $anuncio->update($data, $where);
            
            $datos = $appBuscaMas->enviar($nid);
            header('Content-Type: application/json');
            echo Zend_Json::encode($datos);
            
        }
        
        

    }

    public function deleteAction() {
        
    }

    public function getAction() {
        
    }

    public function postAction() {
        
    }

    public function putAction() {
        
    }

}