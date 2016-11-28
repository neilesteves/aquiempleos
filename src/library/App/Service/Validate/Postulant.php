<?php

/**
 * Validaciones sobre un postulante
 *
 * @author Carlos Muñoz Ramirez, <camura8503@gmail.com>
 */

class App_Service_Validate_Postulant extends App_Service_Validate
{
    const BLOCKED       = 'blocked';
    const IS_NULL       = 'isNull';
    
    const IS_REFERRED       = 'isReferred';
    const IS_REFERRED_MSJ   = 'Este email ya existe como referido';

    protected $_messageTemplates = array(
        self::BLOCKED       => "El postulante esta bloqueado",
        self::IS_NULL       => "No se encontro el postulante",
        self::IS_REFERRED   => "Ya se encuentra referido",
    );
                
    /**
     * @var Application_Model_EmpresaPostulanteBloqueado
     */
    private $_companyPostulantBlockedModel  = null;
    
    protected $_modelName = 'Application_Model_Postulante';

    public function __construct($id = null)
    {
        parent::__construct($id);
        
        $this->_companyPostulantBlockedModel = 
                new Application_Model_EmpresaPostulanteBloqueado;
    }
    
    public function isBlocked($companyId, $data = array())
    {
        $postulant = $this->getData($data);

        $blocked = $this->_companyPostulantBlockedModel->
                obtenerPorEmpresaYPostulante(
                        $companyId, $postulant['id'], array('id'));
        
        if (isset($blocked)) {
            $this->_error(self::BLOCKED);
            return true;
        }
        return false;
    }
    
    static public function hasReferred($email, $adId)
    {
        $referralModel   = new Application_Model_Referenciado;
        $referral = $referralModel->obtenerPorEmailYAnuncio(
                $email, $adId, array('id'));
        
        if ($referral) {
            self::$staticMessageError = self::IS_REFERRED_MSJ;
            self::$staticTypeError    = self::IS_REFERRED;
            return true;
        }
        
        return false;
    }
}