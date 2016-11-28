<?php
/**
 * @author Carlos Muñoz <camura8503@gmail.com>
 */

class App_Service_RegisterReferrals
{
    private $_postulants        = array();
    
    private $_adId              = null;
    
    private $_directoryOrigin   = null;
    
    private $_directoryDestiny  = null;
    
    private $_errors            = array();
    
    /**
     * @var Application_Model_Referenciado
     */
    private $_referredModel     = null;
    
    /**
     * @var Application_Model_Postulacion
     */
    private $_postulationModel  = null;
    
    /**
     * @var App_Validate_PostulantReferred
     */
    private $_validatorReferred = null;
    
    /**
     * @var App_Validate_PostulantReference
     */
    private $_validatorReference = null;   
    
    const ACTION_REFERENCE      = 1;
    
    const ACTION_REGISTER       = 2;   
            
    public function __construct($companyId, $adId, $postulants = array())
    {
        $this->_referredModel       = new Application_Model_Referenciado;
        $this->_postulationModel    = new Application_Model_Postulacion;
        $this->_validatorReference  = new 
                App_Validate_PostulantReference($companyId, $adId);
        
        $this->_validatorReferred   = new 
                App_Validate_PostulantReferred($adId);                
        
        $config = Zend_Registry::get('config');

        $this->_postulants       = $postulants;
        $this->_adId             = $adId;
        $this->_directoryOrigin  = $config->urls->app->elementsCvRootTmp;
        $this->_directoryDestiny = $config->urls->app->elementsCvRoot;               
    }
        
    public function setPostulants($postulants)
    {
        $this->_postulants = $postulants;
    }
    
    public function setAdId($adId)
    {
        $this->_adId = $adId;
    }
    
    private function _addError($email, $error)
    {
        $this->_errors[$email] = $error;
    }    
    
    public function getErrors()
    {
        return $this->_errors;
    }
    
    public function registerAll()
    {
        $postulants = $this->_postulants;
        foreach ($postulants as $postulant) {
            $email = $postulant['email'];
            
            if ($postulant['action'] == self::ACTION_REGISTER) {
                if ($this->registerReferred($postulant))
                    unset($postulants[$email]);
            }
            
            if ($postulant['action'] == self::ACTION_REFERENCE) {
                if ($this->reference($postulant))
                   unset($postulants[$email]);    
            }
        }
        
        return $postulants;
    }
    
    public function registerReferred($postulant)
    {
        $email = $postulant['email'];
        
        if (!$this->_validatorReferred->isValid($email)) {
            $message = $this->_validatorReferred->getMessage();
            $this->_addError($email, $message);
            return false;
        }

        try {
            $now = new Zend_Date;
            
            $postulant['id_anuncio_web']    = $this->_adId;
            $postulant['nombre']            = $postulant['nombres'];
            $postulant['apellidos']            = $postulant['apellidos'];
            $postulant['fecha_creacion']    = $now->get('YYYY-MM-dd HH:mm:ss');
            $postulant['tipo']              = 
                    Application_Model_Referenciado::TIPO_REFERIDO;
            
            $this->_referredModel->registrar($postulant);                                
           
            $destino    = $this->_directoryDestiny;
            $origen     = $this->_directoryOrigin;
            
            $origen     = $origen . $postulant['curriculo'];

            $destino    = $destino . $postulant['curriculo'];

            copy($origen, $destino);
            
            unlink($origen);            
            
        } catch (Zend_Exception $e) {
            $error = $e->getMessage();
            $this->_addError($email, $error);
            return false;
        }
        
        return true; 
    }
    
    public function reference($postulant)
    {
        $email = $postulant['email'];
        
        if (!$this->_validatorReference->isValid($email)) {
            $message = $this->_validatorReference->getMessage();
            $this->_addError($email, $message);
            return false;
        }
                       
        try {
            $adId   = $this->_adId;
            $now    = new Zend_Date; 
            
            $data = array();
            $data['referenciado'] = 
                    Application_Model_Postulacion::ES_REFERENCIADO;                        
                                           
            $adModel = new Application_Model_AnuncioWeb();
            $match = $adModel->porcentajeCoincidencia($adId, $postulant['id']);
            
            $postulant['match'] = 0;
            if (is_null($match[0]["aptitus_match"])) {
                $postulant['match'] = $match[0]["aptitus_match"];
            }
            
            $postulation = new App_Service_Validate_Postulation_Postulant(
                    $adId, $postulant['id']);
            
            $postulant['id_anuncio_web']    = $adId;
            $postulant['fecha_creacion']    = $now->get('YYYY-MM-dd HH:mm:ss');
            $postulant['tipo']              = 
                    Application_Model_Referenciado::TIPO_REFERENCIADO;
            
            if (!$postulation->isNull()) {
                $postulant['notificado'] = 
                        Application_Model_Referenciado::NOTIFICADO;
            }
                                                
            $this->_postulationModel->actualizarPorPostulanteYAnuncio(
                $postulant['id'], $adId, $data);
            
            unset($postulant['id']);                        
            
            $this->_referredModel->registrar($postulant);                                    

        } catch (Zend_Exception $e) {
            $error = $e->getMessage();
            $this->_addError($email, $error);
            return false;
        }
        
        return true;
    }     
}