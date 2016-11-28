<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WebService
 *
 * @author Yrving
 */
class App_Scot_WebService
{
    /**
     *
     * @var Zend_Log
     */
    protected $_wslog;
    
    public function  __construct()
    {
        $this->_wslog = new Zend_Log(
            new Zend_Log_Writer_Stream(APPLICATION_PATH.'/../logs/wsscot.log')
        );
        //$this->_wslog->log('cont', Zend_Log::INFO);
    }
    
    public function Finalizar_OT($nroSolicitud, $linkMaterial)
    {
        $result = array();
        
        
        try {
            $msg = "(".$nroSolicitud.": ".$linkMaterial.")";
            $this->_wslog->log(__FUNCTION__.$msg, Zend_Log::INFO);
            $aImp = new Application_Model_AnuncioImpreso();
            $state = $aImp->setURLSourceByCodScot($nroSolicitud, $linkMaterial);
            switch ($state) {
                case 1:
                    $result["result"] = true;
                    $result["message"] = "La URL se registró correctamente.";
                    break;
                case 2:
                    $result["result"] = false;
                    $result["message"] = "No se encontró una solicitud con el código enviado.";
                    break;
                case 3:
                    $result["result"] = true;
                    $result["message"] = "Ya existe la solicitud con el código enviado asociada con la URL enviada.";
                    break;
                case 0:
                    $result["result"] = false;
                    $result["message"] = "Ocurrió un Error de conexión a la BD.";
                    break;
                default: 
                    $result["result"] = false;
                    $result["message"] = "Ocurrió un Error.";
                    break;
            }
            $this->_wslog->log("r: ".$result, Zend_Log::INFO);
        } catch (Exception $exc) {
            $this->_wslog->log($exc->getTraceAsString(), Zend_Log::ERR);
            $this->_wslog->log($exc->getMessage(), Zend_Log::ERR);
            $result["result"] = false;
            $result["message"] = "Ocurrió un Error.";
        }
        
        return $result;
    }
}
