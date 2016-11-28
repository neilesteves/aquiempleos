<?php

require_once 'Zend/Validate/Abstract.php';
 
class App_Validate_Uri extends Zend_Validate_Abstract
{
    const INVALID = 'URIInvalid';
 
    protected $_messageTemplates = array(
        self::INVALID => "'%value%' no es una url válida"
    );
 
    public function isValid($value)
    {
        if (empty($value)) return true;
 
        
        $valueUrl = substr($value, 0,7);
        if ($valueUrl != "http://")
            $value = "http://".$value;
        
        if (!Zend_Uri::check($value)) {
            $this->_error(self::INVALID);
            return false;
        }
 
        return true;
    }
 
}