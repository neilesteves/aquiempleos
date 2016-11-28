<?php

/**
 * Clase padre de los servicios de validacion
 *
 * @author Carlos Muñoz Ramirez, <camura8503@gmail.com>
 */

abstract class App_Service_Validate implements App_Service_Validate_Interface
{
    const IS_NULL   = 'null';
    
    protected $_messageTemplates    = array(self::IS_NULL => 'No result fount');
    
    protected $_messageError    = '';
    protected $_typeError       = '';
    protected $_data            = array();
    protected $_id              = null;
    protected $_modelName       = '';
    protected $_model           = null;
    
    static protected $staticMessageError  = '';
    static protected $staticTypeError     = '';
    static protected $staticData          = array();
    
    public function __construct($id = null)
    {
        if (!is_null($id) && !empty($this->_modelName)) {
            $this->_id      = $id;
            $this->_model   = new $this->_modelName;
            $this->_getData();
        }
    }
    
    public function getData($data = array())
    {
        if (!empty($data))
            $this->_data = $data;

        if (empty($this->_data)) {
            throw new Zend_Validate_Exception(
                    __CLASS__ . ': No result fount');
        }
                    
        return $this->_data;
    }
    
    public function setData($data)
    {
        $this->_data = $data;
    }
    
    public function getMessage()
    {
        return $this->_messageError;
    }
    
    public function getType()
    {
        return $this->_typeError;
    }
    
    public function isNull()
    {
        if (empty($this->_data)) {
            $this->_error(self::IS_NULL);
            return true;
        }
        
        return false;
    }
    
    protected function _error($type)
    {
        $this->_typeError       = $type;
        
        if (!isset($this->_messageTemplates[$type])) {
            $this->_messageError = null;
            return;
        }

        $this->_messageError    = $this->_messageTemplates[$type];
    }
    
    protected function _getData()
    {
        $data = $this->_model->find($this->_id)->current();
        
        if (isset($data)) {
            $this->_data = $data->toArray();
        }
    }
    
    public function reload() 
    {
        $this->_getData();
    }

    static public function getStaticMessage()
    {
        return self::$staticMessageError;
    }
    
    static public function getStaticType()
    {
        return self::$staticTypeError;
    }
    
    static public function getStaticData()
    {
        return self::$staticData;
    }
}