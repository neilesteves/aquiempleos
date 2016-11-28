<?php

class App_Controller_Action_Helper_WebServiceEncriptacion 
    extends Zend_Controller_Action_Helper_Abstract
{
    private $_clienteEnc;
    public function __construct()
    {
        $this->_config = Zend_Registry::get('config');
        $this->_clienteEnc = new Zend_Soap_Client(
            $this->_config->urlsComprarAviso->encriptacion
        );
    }
    
    public function encriptaCadena($cadena)
    {
        $parametrosEnc = array('Cad' => $cadena);
        $response = $this->_clienteEnc->BlackBox($parametrosEnc);
        // @codingStandardsIgnoreStart
        return $response->BlackBoxResult;
        // @codingStandardsIgnoreEnd
    }

    public function desencriptaCadena($cadena)
    {
        $parametrosEnc = array('Cad' => $cadena);
        $response = $this->_clienteEnc->BlackBoxDecrypta($parametrosEnc);
        // @codingStandardsIgnoreStart
        if(isset($response->BlackBoxDecryptaResult)){
            return $response->BlackBoxDecryptaResult;
        }
        return NULL;
        // @codingStandardsIgnoreEnd
    }
}
