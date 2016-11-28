<?php

/**
 * Validaciones sobre un aviso
 *
 * @author Carlos Muñoz Ramirez, <camura8503@gmail.com>
 */

class App_Service_Validate_Ad extends App_Service_Validate
{
    const MANAGED    = 'managed';
    const BELONGS    = 'belongs';
    
    /**
     * @var Application_Model_MembresiaEmpresaDetalle
     */
    private $_membershipCompanyDetail;

    protected $_messageTemplates = array(
        self::MANAGED   => "No puede administrar el aviso",
        self::BELONGS   => "No pertenece a esta empresa",
        self::IS_NULL   => "No se encontro el anuncio"
    );
    
    protected $_modelName = 'Application_Model_AnuncioWeb';    
    
    /*
     * App_Service_Validate_UserCompany
     */
    private $_userCompanyValidate   = null;
    
    public function __construct($id = null)
    {
        parent::__construct($id);        
        
        $this->_userCompanyValidate     = new App_Service_Validate_UserCompany;
        $this->_membershipCompanyDetail = 
                new Application_Model_MembresiaEmpresaDetalle;
    }
    
    public function isManaged($userCompany, $data = array())
    {
        $ad = $this->getData($data);
        
        if (!$this->belongsTo($userCompany['id_empresa']))
            return false;
        
        $this->_userCompanyValidate->setData($userCompany);
        
        $benefit = $this->_membershipCompanyDetail->obtenerBeneficioPorEmpresa(
            $userCompany['id_empresa'], 
            Application_Model_Beneficio::CODE_REASIGNAR_PROCESOS);
        
        /*if (!empty($benefit))
            return true;*/
        
        if ($this->_userCompanyValidate->isCreator())
            return true;        
        
        if (!$this->_userCompanyValidate->isAssign($ad['id']) && !empty($benefit)) {
            $this->_messageError = $this->_userCompanyValidate->getMessage();
            return false;
        }
                
        return true;
    }
    
    public function belongsTo($companyId, $data = array())
    {
        $ad = $this->getData($data);
                        
        if ($ad['id_empresa'] != $companyId) {
            $this->_error(self::BELONGS);
            return false;
        }
        
        return true;
    }
}