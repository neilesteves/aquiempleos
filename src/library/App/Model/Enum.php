<?php 

class App_Model_Enum
{
    protected $_enums;
    protected $_config;
    protected $_cache;
    protected $_prefix;
    
    public function __construct()
    {
        $this->_prefix = isset($this->_name)?$this->_name.'_':null;
        $this->_config = Zend_Registry::get('config');
        $this->_cache = Zend_Registry::get('cache');
    }
}