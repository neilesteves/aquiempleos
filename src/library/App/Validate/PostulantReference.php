<?php

/**
 * @author Carlos Muñoz <camura8503@gmail.com>
 */

class App_Validate_PostulantReference extends Zend_Validate_Abstract
{        
    protected $_postulantModel  = null;    
    protected $_companyId       = null;
    protected $_adId            = null;
    protected $_postulant       = null;
    
    protected $_message         = '';
            
    function __construct($companyId, $adId) 
    {                          
        $this->_postulantModel      = new Application_Model_Postulante;
        
        $this->setCompanyId($companyId);
        $this->setAdId($adId);                       
    }

    public function getAdId()
    {
        return $this->_adId;
    }
    
    public function getCompanyId()
    {
        return $this->_companyId;
    }
    
    public function getPostulant()
    {
        return $this->_postulant;
    }
    
    public function setAdId($adId)
    {
        $this->_adId = $adId;
    }
    
    public function setCompanyId($companyId)
    {
        $this->_companyId = $companyId;
    }
    
    public function getMessage()
    {
        return $this->_message;
    }

    public function isValid($postulantEmail)
    {
        $adId       = $this->getAdId();
        $companyId  = $this->getCompanyId();
        
        if (App_Service_Validate_Postulant::hasReferred(
                $postulantEmail, $adId)) {
            $this->_message = App_Service_Validate_Postulant::getStaticMessage();
            return false;
        }
            
        
        if (!App_Service_Validate_User::isRegister($postulantEmail)) {
            $this->_message = App_Service_Validate_User::getStaticMessage();
            return false;
        }

        $postulantData = $this->_postulantModel->buscarPostulantexEmail(
            $postulantEmail);
        
        $this->_postulant = $postulantData;
            
        $postulant = new App_Service_Validate_Postulant;
        
        $postulant->setData($postulantData);        
        
        if ($postulant->isBlocked($companyId)) {
            $this->_message = $postulant->getMessage();
            return false;
        }
                    
        $postulation = new App_Service_Validate_Postulation_Postulant(
                $adId, $postulantData['id']);
        
        if (!$postulation->isNull() && $postulation->isReferred()) {
            $this->_message = $postulation->getMessage();
            return false;
        }
        
        return true;
    }
}