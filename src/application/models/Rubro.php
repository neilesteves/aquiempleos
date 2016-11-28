<?php

class Application_Model_Rubro extends App_Db_Table_Abstract
{
    protected $_name = "rubro";

    public function getRubros()
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()
                ->from($this->_name, array('id', 'nombre'))
                ->order('nombre ASC');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
            $rs, 
            $cacheId, 
            array(), 
            $this->_config->cache->Rubro->getRubros
        );
        return $rs;
    }
    
    public function getRubrosLanding()
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()
                ->from($this->_name, array('id','nombre'))
                ->order('nombre ASC');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
            $rs, 
            $cacheId, 
            array(), 
            $this->_config->cache->Rubro->getRubros
        );
        return $rs;
    }
}