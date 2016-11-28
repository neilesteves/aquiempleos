<?php
/**
 * @author Carlos Muñoz <camura8503@gmail.com>
 */

class App_Service_Cache
{
    private $_prefixes = array();
    
    private $_cache = null;
    
    const AD_PREFIX_SHEET = 'anuncio_web_';
    
    const AD_PREFIX_SHEET_KEY = 'url_id';
    
    public function __construct()
    {
        $this->_cache = Zend_Registry::get('cache');
    }
    
    public function clearAll()
    {
        return false;
    }
    
    public function clear($prefix, $key)
    {            
        if (!is_null($this->_cache))
            $this->_cache->remove($prefix . $key);        
    }
}