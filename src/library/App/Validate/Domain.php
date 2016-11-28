<?php

/**
 * Valida si el dominio existe, si tiene un IP asosciado valido.
 *
 * @author Jesus Fabian
 */

class App_Validate_Domain extends Zend_Validate_Abstract
{
    const INVALID_URL = 'invalidUrl';

    protected $_messageTemplates = array(
        self::INVALID_URL => "'%value%' no es una URL.",
    );

    public function isValid($value)
    {
        $valueString = (string) $value;
        $this->_setValue($valueString);
        
        if ($value == Application_Form_Paso1Postulante::$_defaultWebsite) {
            $this->_error(self::INVALID_URL);
            return false;
        }
        
        $a = strtolower(substr($value, 0, 3));
        
        if ($a == 'www') {
            $value = Application_Form_Paso1Postulante::$_defaultWebsite.$value;
        }
        if (!Zend_Uri::check($value)) {
            $this->_error(self::INVALID_URL);
            return false;
        }
        $domain = $value;
        $domain = str_replace("http://", "", $domain);
        $domain = str_replace("www.", "", $domain);
        if ($domain == "") {
            $this->_error(self::INVALID_URL);
            return false;
        }
        $actionHelper = new App_Controller_Action_Helper_Api();
        $dataIp = $actionHelper->getRealIp($domain);
        if (!isset($dataIp) || $dataIp === false || $dataIp == "") {
            $this->_error(self::INVALID_URL);
            return false;
        }
    return true;
    exit;
    }
}