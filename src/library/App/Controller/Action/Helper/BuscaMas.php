<?php

class App_Controller_Action_Helper_BuscaMas extends Zend_Controller_Action_Helper_Abstract {
    
    protected $_config;
    protected $_model;
    protected $_cache;

    public function __construct() {
        $this->_config = Zend_Registry::get('config');
        $cparts = explode('_', __CLASS__);
        $this->_model = ucfirst(strtolower($cparts[4]));       
        $this->_cache = Zend_Registry::get('cache');
    }
    
    public function obtenerResultadoBuscaMas($url) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        //Solo en local
        if  (APPLICATION_ENV == 'development') {
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
            curl_setopt($ch, CURLOPT_PROXY, '172.21.0.83:3128');
        }

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $resultado = curl_exec($ch);
        curl_close($ch);

        return $resultado;
    }

    public function obtenerResultadoBuscaMasCache($url) {
        $cacheEt = $this->_model.'_'.__FUNCTION__;
        //  var_dump($cacheEt,$url);
        $token=explode('_', $url);
        $key=explode('/', $token[1]);        
        $cacheId = $this->_model . '_' . __FUNCTION__.$key[1] ;        
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        //Solo en local
        if  (APPLICATION_ENV == 'development') {
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
            curl_setopt($ch, CURLOPT_PROXY, '172.21.0.83:3128');
        }

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $resultado = curl_exec($ch);
        curl_close($ch);
      
        $this->_cache->save($resultado, $cacheId, array(), $cacheEt);
        
        return $resultado;
    }
    
    public function ordenarArray ($toOrderArray, $field, $inverse = false) {
        $position = array();
        $newRow = array();
        $otros = array();
        if (count($toOrderArray) >0) {
            foreach ($toOrderArray as $key => $row) {
                //Niveles que no deben mostrar por JJC
                    if ($row['slug'] != 'senior' && $row['slug'] != 'alta-gerencia' &&
                            $row['slug'] != 'gerencia-de-obra') {
                        $position[$key]  = $row[$field];
                        $newRow[$key] = $row;
                    }
            }
        }
        
        if ($inverse) {
            arsort($position);
        }
        else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {
            if ($newRow[$key]['slug'] == 'otros') {
                $otros = $newRow[$key];
                unset($newRow[$key]);
                continue;
            }
            if ($newRow[$key]['slug'] != '')
            $returnArray[] = $newRow[$key];
        }
        
        if (!empty($otros))
        array_push($returnArray, $otros);

        return $returnArray;
    }
    
    public function ordenarArrayUbicacion ($toOrderArray, $field, $inverse = false) {
        $position = array();
        $newRow = array();
        
        if (count($toOrderArray) >0) {
            //No tomar en cuenta dis callao y Perú
            foreach ($toOrderArray as $key => $row) {

    //                if ($row['slug'] != 'peru' && $row['slug'] != 'carmen-de-la-legua-reynoso'
    //                        && $row['slug'] != 'la-perla' && $row['slug'] != 'la-punta' && $row['slug'] != 'bellavista'
    //                        && $row['slug'] != 'ventanilla') {
                     if ($row['slug'] != 'peru') {
                        $position[$key]  = $row[$field];
                        $newRow[$key] = $row;
                    }

            }
        }
        
        if ($inverse) {
            arsort($position);
        }
        else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {     
            $returnArray[] = $newRow[$key];
        }
        return $returnArray;
    }
}