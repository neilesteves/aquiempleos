<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Description of DetalleMembresiaEmpresa
 *
 * @author Paul Taboada	
 */
class Application_Model_MembresiaDetalle extends App_Db_Table_Abstract
{
    protected $_name = 'membresia_detalle';
    
    const ACTIVO   = 1;
    const INACTIVO = 0;
    
    public function __construct()
    {
        parent::__construct();
        $cparts = explode('_', __CLASS__);
        $this->_model = $cparts[2];
    }
    
    public function getDetalleByMembresia($id,$benficoweb=false)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        
        $cacheId = $this->_model.'_'.__FUNCTION__.$id;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('mdet' => $this->_name),
                array('id_beneficio', 'id_membresia', 'valor')
            )->where('mdet.id_membresia = ?', $id)
             ->where('estado =?', self::ACTIVO);
        $sql->join(array('benef'=>'beneficio'), 
                'benef.id = mdet.id_beneficio', array('*'));
        if($benficoweb){
           $sql->where("benef.codigo IN ('memprem-web','memprem-imp','memprem-adic','memsele-web','memsele-imp','memsele-adic','memesen-web','memesen-imp','memesen-adic','memdigi')");
        }
        $result = $db->fetchAll($sql);
         
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        
        return $result; 
    }
    
        public function getDetalleByMembresiaPago($id)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        
       $cacheId = $this->_model.'_'.__FUNCTION__.$id;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('mdet' => $this->_name),
                array('id_beneficio', 'id_membresia', 'valor')
            )->where('mdet.id_membresia = ?', $id)
             ->where('estado =?', self::ACTIVO);
        $sql->join(array('benef'=>'beneficio'), 
                'benef.id = mdet.id_beneficio', array('*'));
       
        $result = $db->fetchAll($sql);
         
       $this->_cache->save($result, $cacheId, array(), $cacheEt);
        
        return $result; 
    }

    public function getAllDetallesMembresia($benficoweb=false)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        
        $cacheId = $this->_model.'_'.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(
                    array('mdet' => $this->_name),
                    array('id_beneficio', 'id_membresia', 'valor'))
                ->where('estado =?', self::ACTIVO)
                ->join(array('benef'=>'beneficio'), 'benef.id = mdet.id_beneficio', array('*'))
                ->order('id_membresia')
                ->order('benef.id ASC'); 
        if($benficoweb){
           $sql->where("benef.codigo IN ('memprem-web','memprem-imp','memprem-adic','memsele-web','memsele-imp','memsele-adic','memesen-web','memesen-imp','memesen-adic','memdigi')");
        }
        $result = $db->fetchAll($sql);
      
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
     

        return $result; 
    }
    
}
