<?php

/**
 * @author Carlos Muñoz <camura8503@gmail.com>
 */

class App_Validate_PostulantReferred extends Zend_Validate_Abstract
{        
    protected $_adId    = null;
    protected $_message = '';
            
    function __construct($adId) {        
        $this->_setAdId($adId);
    }
    
    private function _setAdId($adId)
    {
        $this->_adId = $adId;
    }
    
    public function getAdId()
    {
        return $this->_adId;
    }
    
    public function getMessage()
    {
        return $this->_message;
    }
    
    public function isValid($postulantEmail)
    {        
        if (App_Service_Validate_User::isRegister($postulantEmail)) {
            $this->_message = App_Service_Validate_User::getStaticMessage();
            return false;
        }
               
        if (App_Service_Validate_Postulant::hasReferred(
                $postulantEmail, $this->getAdId())) {
            $this->_message = App_Service_Validate_Postulant::getStaticMessage();
            return false;
        }
        
        return true;
    }
}