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
class ZendLucene_WebService
{
    /**
     * @var ZendLucene
     */
    protected $_zl;
    
    /**
     *
     * @var Zend_Log
     */
    protected $_wslog;
    
    public function  __construct()
    {
        $this->_wslog = new Zend_Log(
            new Zend_Log_Writer_Stream(APPLICATION_PATH.'/../logs/wslucene.log')
        );
        //$this->_wslog->log('cont', Zend_Log::INFO);
        $this->_zl = new ZendLucene();
    }
    
    public function agregarDocumentoAviso($urlSite, $awid, $urlId)
    {
        //$this->_wslog->log('pre', Zend_Log::INFO);
        $result = false;
        try {
            $msg = "(".$awid.":".$urlId.")";
            $this->_wslog->log(__FUNCTION__.$msg, Zend_Log::INFO);
            $result = $this->_zl->agregarDocumentoAviso($urlSite, $awid, $urlId);
            $this->_wslog->log("r: ".$result, Zend_Log::INFO);
        } catch (Exception $exc) {
            $this->_wslog->log($exc->getTraceAsString(), Zend_Log::ERR);
            $this->_wslog->log($exc->getMessage(), Zend_Log::ERR);
        }
        
        return $result;
    }
    
    public function agregarDocumentoPostulante($objPostulante)
    {
        $result = false;
        try {
            $msg = "(ID Postulante: ".$objPostulante["idpostulante"].")";
            $this->_wslog->log(__FUNCTION__.$msg, Zend_Log::INFO);
            $result = $this->_zl->insertarIndexPostulante($objPostulante, false);
            $this->_wslog->log("r: ".$result, Zend_Log::INFO);
        } catch (Exception $exc) {
            $this->_wslog->log($exc->getTraceAsString(), Zend_Log::ERR);
            $this->_wslog->log($exc->getMessage(), Zend_Log::ERR);
        }
        
        return $result;
    }
    
    public function agregarDocumentoPostulacion($objPostulacion)
    {
        $result = false;
        try {
            $msg = "(ID Postulacion: ".$objPostulacion["idpostulacion"].")";
            $this->_wslog->log(__FUNCTION__.$msg, Zend_Log::INFO);
            $result = $this->_zl->insertarIndexPostulaciones($objPostulacion, false);
            $this->_wslog->log("r: ".$result, Zend_Log::INFO);
        } catch (Exception $exc) {
            $this->_wslog->log($exc->getTraceAsString(), Zend_Log::ERR);
            $this->_wslog->log($exc->getMessage(), Zend_Log::ERR);
        }
        
        return $result;
    }
    
    public function duplicarDocumentoPostulacion($idPostulacion, $valor)
    {
        $result = false;
        try {
            $msg = "(ID Postulacion: ".$idPostulacion.")";
            $this->_wslog->log(__FUNCTION__.$msg, Zend_Log::INFO);
            $result = $this->_zl->duplicarIndexPostulaciones($idPostulacion, $valor, false);
            $this->_wslog->log("r: ".$result, Zend_Log::INFO);
        } catch (Exception $exc) {
            $this->_wslog->log($exc->getTraceAsString(), Zend_Log::ERR);
            $this->_wslog->log($exc->getMessage(), Zend_Log::ERR);
        }
        
        return $result;
    }
    
    public function actualizarDocumentoPostulante($idPostulante, $valores)
    {
        $result = false;
        try {
            $msg = "(ID Postulante: ".$idPostulante.")";
            $this->_wslog->log(__FUNCTION__.$msg, Zend_Log::INFO);
            $result = $this->_zl->updateIndexPostulante($idPostulante, $valores, false);
            $this->_wslog->log("r: ".$result, Zend_Log::INFO);
        } catch (Exception $exc) {
            $this->_wslog->log($exc->getTraceAsString(), Zend_Log::ERR);
            $this->_wslog->log($exc->getMessage(), Zend_Log::ERR);
        }
        
        return $result;
    }
    
    function actualizarDocumentosPostulaciones($idPostulacion, $campo, $valor)
    {
        $result = false;
        try {
            $msg = "(ID Postulacion: ".$idPostulacion.")";
            $this->_wslog->log(__FUNCTION__.$msg, Zend_Log::INFO);
            $result = $this->_zl->updateIndexPostulaciones($idPostulacion, $campo, $valor, false);
            $this->_wslog->log("r: ".$result, Zend_Log::INFO);
        } catch (Exception $exc) {
            $this->_wslog->log($exc->getTraceAsString(), Zend_Log::ERR);
            $this->_wslog->log($exc->getMessage(), Zend_Log::ERR);
        }
        
        return $result;
    }
    
    function actualizarDocumentosPostulacionesxidAnuncioWeb($idAnuncioWeb, $campo, $valor)
    {
        $result = false;
        try {
            $msg = "(ID AnuncioWeb: ".$idAnuncioWeb.")";
            $this->_wslog->log(__FUNCTION__.$msg, Zend_Log::INFO);
            $result = $this->_zl->updateIndexPostulacionesxidAnuncioWeb(
                $idAnuncioWeb, $campo, $valor, false
            );
            $this->_wslog->log("r: ".$result, Zend_Log::INFO);
        } catch (Exception $exc) {
            $this->_wslog->log($exc->getTraceAsString(), Zend_Log::ERR);
            $this->_wslog->log($exc->getMessage(), Zend_Log::ERR);
        }
        
        return $result;
    }
    
    function actualizarDocumentosPostulacionesxidPostulante($idPostulante, $campo, $valor)
    {
        $result = false;
        try {
            $msg = "(ID Postulante: ".$idPostulante.")";
            $this->_wslog->log(__FUNCTION__.$msg, Zend_Log::INFO);
            $result = $this->_zl->updateIndexPostulacionesxidPostulante(
                $idPostulante, $campo, $valor, false
            );
            $this->_wslog->log("r: ".$result, Zend_Log::INFO);
        } catch (Exception $exc) {
            $this->_wslog->log($exc->getTraceAsString(), Zend_Log::ERR);
            $this->_wslog->log($exc->getMessage(), Zend_Log::ERR);
        }
        
        return $result;
    }
    
    public function eliminarDocumentoAviso($idAviso)
    {
        // To be implemented
    }
    
    public function agregarDocumentosAvisos($dataAnunios)
    {
        //$this->_wslog->log('pre', Zend_Log::INFO);
        $result = false;
        try {
            $this->_wslog->log(__FUNCTION__, Zend_Log::INFO);
            foreach ($dataAnunios as $dataAnuncio) {
                $msg = "(".$dataAnuncio["awid"].":".$dataAnuncio["urlId"].")";
                
                $result = $this->_zl->agregarDocumentoAviso(
                    $dataAnuncio["urlSite"], $dataAnuncio["awid"], $dataAnuncio["urlId"]
                );
                $this->_wslog->log("Data: ".$msg."  r: ".$result, Zend_Log::INFO);
            }
        } catch (Exception $exc) {
            $this->_wslog->log($exc->getTraceAsString(), Zend_Log::ERR);
            $this->_wslog->log($exc->getMessage(), Zend_Log::ERR);
        }
        
        return $result;
    }
}
