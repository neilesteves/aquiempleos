<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class App_Controller_Action_Helper_Solr extends Zend_Controller_Action_Helper_Abstract
{

     private $_postulante;
    public function __construct()
    {
        $this->_config = Zend_Registry::get('config');
        $this->_postulante= new Application_Model_Postulante();
    }
    
    
    public function valServidor(){
     
        try {
        $client = new \Solarium\Client($this->_config->solr);
        $ping = $client->createPing();
        $test ='';
        $result = $client->ping($ping); 
        return true;
        } catch (Solarium\Exception\HttpException $exc) {
        return false;
        }

        
    }
    
    public function valServidorSorlAviso(){
        $config = Zend_Registry::get('config');
        try {
            
        $client = new \Solarium\Client($config->solrAviso);
        $ping = $client->createPing();
        $test ='';
        $result = $client->ping($ping); 
        return true;
        } catch (Solarium\Exception\HttpException $exc) {
        return false;
        }

        
    }
    
    public function valServidorSor($config)
    {
        try {            
            $client = new \Solarium\Client($config);
            $ping = $client->createPing();
            $client->ping($ping);             
            return true;
        } catch (Solarium\Exception\HttpException $exc) {
            return false;
        }
    }
    
    
    public function addSolr($idPostulante){
         $where='id='.$idPostulante;
        
        if($this->valServidor()==true){
        $sc = new Solarium\Client($this->_config->solr);
        $moPostulante = new Solr_SolrAbstract($sc,'postulante');    
        $solradd=1;

//        $ususarioEmp = new Application_Model_Usuario();
//        $cuentaConfirmada = $ususarioEmp->hasConfirmedIdPost($idPostulante);
//        if(!$cuentaConfirmada){
//             
//            return true;
//        }

        try {
              $solradd =  $moPostulante->addPostulante($idPostulante);
        } catch (Exception $exc) {
             $this->_postulante->update(array('solr'=>'0'),$where);
        }

        if($solradd==0){
            $this->_postulante->update(array('solr'=>'1'),$where);
        }else{
            $this->_postulante->update(array('solr'=>'0'),$where);
        }
        
        }else{
         $this->_postulante->update(array('solr'=>'0'),$where);
        }
    }
    
    
    public function deleteSolar ($idPostulante){
       if($this->valServidor()==true){
       $where='id='.$idPostulante;
        $sc = new Solarium\Client($this->_config->solr);
        $moPostulante = new Solr_SolrAbstract($sc,'postulante');    
        $solradd=1;
        try {
              $solradd =  $moPostulante->deletePostulante($idPostulante);
        } catch (Exception $exc) {
             $this->_postulante->update(array('solr'=>'0'),$where);
        }

    //var_dump($solradd);exit;
        if($solradd==0){
            $this->_postulante->update(array('solr'=>'1'),$where);
        }else{
            $this->_postulante->update(array('solr'=>'0'),$where);
        }
     // var_dump($solradd);exit;
        
        }else{
         $this->_postulante->update(array('solr'=>'0'),$where);
        } 
    }
}

