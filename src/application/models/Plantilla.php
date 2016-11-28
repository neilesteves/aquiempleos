<?php

class Application_Model_Plantilla extends App_Db_Table_Abstract
{
    protected $_name = "plantilla";
    
    public function getPlantillas()
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()->from($this->_name);
        $rs = $this->getAdapter()->fetchAll($sql);
        $this->_cache->save(
            $rs, 
            $cacheId, 
            array(), 
            $this->_config->cache->AnuncioWebPreferencial->plantilla
        );
        return $rs;
    }
    
    public function requiereAdjuntoByIdPlantilla($idPlantilla)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name, 'contiene_logo')
            ->where('id = ?', $idPlantilla);
            
        return $this->getAdapter()->fetchRow($sql);
    }
}