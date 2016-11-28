<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ValidateRUC
 *
 * @author eanaya
 */
class App_Validate_Ruc extends Zend_Validate_Abstract
{
    
    private $_firstTwoDigitsAllowed = array(10, 20, 17, 15);
    private $_factor = "5432765432";
    
    const NOT_ELEVEN_DIGITS = 'not11digits';
    const NOT_DIGITS = 'notdigits';
    const INVALID_RUC = 'invalidruc';
    
    protected $_messageTemplates = array(
        self::NOT_ELEVEN_DIGITS => 'No parece que hayan 11 dígitos',
        self::NOT_DIGITS => '"%value%" no parece que fuera solo dígitos',
        self::INVALID_RUC => 'No parece un RUC válido',
    );
    
    public function isValid($value)
    {
        
        $value = (string) $value;
        $this->_setValue($value);
        
        if (!is_numeric($value)) { 
            $this->_error(self::NOT_DIGITS); 
            return false;
        }
            
        if (strlen($value) !== 11) {  
            $this->_error(self::NOT_ELEVEN_DIGITS);
            return false;   
        }
        
        $firstTwoDigits = (int) substr($this->_value, 0, 2);
        
        $exists = false;
        foreach ($this->_firstTwoDigitsAllowed as $val) {
            if ($val == $firstTwoDigits) {
                $exists = true;
            }
        }
        
        if (!$exists) {
            $this->_error(self::INVALID_RUC);
            return false;
        }
        
        $suma = 0;
        for ($i = 0; $i < strlen($this->_factor); $i++) {
            $suma = $suma + ((int) substr($this->_value, $i, 1)) *
                    ((int) substr($this->_factor, $i, 1));
        }
        
        $digitVal = 11 - ($suma % 11);
        if ($digitVal == 10) {
            $digitVal = 0;
        }
        if ($digitVal == 11) {
            $digitVal = 1;
        }
        
        if ($digitVal != ((int) substr($this->_value, 10, 1))) {
            $this->_error(self::INVALID_RUC);
            return false;
        }
        
        return true;
    }
}