<?php

class Application_Model_AbusoCategoria extends App_Db_Table_Abstract
{
    protected $_name = "abuso_categoria";
    
    public function getCategoriasAviso()
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()->from($this->_name, array('id', 'descripcion'));
       
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
            $rs, 
            $cacheId, 
            array(), 
            $this->_config->cache->AbusoCategoria->getCategoriasAviso
        );
        return $rs;
    }
}