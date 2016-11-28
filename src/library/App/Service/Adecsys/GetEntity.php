<?php
/**
 * @author Carlos Muñoz <camura8503@gmail.com>
 */

class App_Service_Adecsys_GetEntity
{
    /**
     * @var App_Service_RegisterTramas
     */
    private $_serviceTramas;
    
    private $_webService    = null;
    private $_config        = null; 
    
    private $_options       = array();
    private $_message       = null;
    private $_entity        = null;
    private $_nameTrama     = 'aptitus';
    
    const MSJ_ERROR                 = 'Ocurrio un error';  
    const MSJ_INVALID               = 'Datos invalidos';
    
    public function __construct($webService = null)
    {
        $this->_config        = Zend_Registry::get('config');        
        
        if ($this->_config->adecsys->proxy->enabled)
            $this->_options = $this->_config->adecsys->proxy->param->toArray();        

        $wsdl = $this->_config->adecsys->wsdl;

        $this->_webService = $webService;
        if (is_null($webService))
            $this->_webService = new Adecsys_Wrapper($wsdl, $this->_options);
        
        $this->_serviceTramas = new App_Service_Adecsys_RegisterTramas(
                $this->_webService);                       
    }
    
    public function get($documentType, $documentNumber)
    {
        $this->_entity  = null;
        $this->_message = null;
        
        if (!$this->_isValid($documentType, $documentNumber))
            return null;
        
        $typeTrama = App_Service_Adecsys_RegisterTramas::TYPE_CONSULTATION_ENTE;
        
        try {
            $response = $this->_webService->validarCliente(
                $documentType, $documentNumber);
            $this->_serviceTramas->register($this->_nameTrama, $typeTrama);
        } catch (Exception $e) {
            $this->_message = self::MSJ_ERROR;
            $this->_serviceTramas->register($this->_nameTrama, $typeTrama);
            return null;
        }
        
        $this->_entity = $response;
        
        return $response;
    }
    
    private function _isValid($documentType, $documentNumber)
    {
        if (is_null($documentType) || is_null($documentNumber)) {
            $this->_message = self::MSJ_INVALID;
            return false;
        }
        
        return true;
    }
    
    public function getMessage()
    {
        return $this->_message;
    }    
    
    public function getEntity()
    {
        return $this->_entity;
    }
    
    public function setNameTrama($name)
    {
        $this->_nameTrama = $name;
    }
}