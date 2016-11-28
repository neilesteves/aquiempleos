<?php

class Application_Model_Idioma extends App_Model_Enum
{
    protected $_name = "idioma";
    /** 
     * Lista de idiomas
     * 
     * @return array
     */
    public function getIdiomas()
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $rs = $this->_config->enumeraciones->lenguajes->toArray();
        $this->_cache->save(
            $rs, 
            $cacheId, 
            array(), 
            $this->_config->cache->Idioma->getIdiomas
        );
        return $rs;
    }
    
    public function get()
    {
        return $this->getIdiomas();
    }
    

    
}