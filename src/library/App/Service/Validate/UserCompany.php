<?php

/**
 * Validaciones sobre un usuario empresa
 *
 * @author Carlos Muñoz Ramirez, <camura8503@gmail.com>
 */

class App_Service_Validate_UserCompany extends App_Service_Validate
{
    const ASSIGN     = 'assign';
    const CREATOR    = 'creator';
    const BELONGS    = 'belongs';
    
    protected $_messageTemplates = array(
        self::ASSIGN    => "No esta assignado",
        self::CREATOR   => "Mo es el usuario principal",
        self::IS_NULL   => "No se encontro el anuncio",
        self::BELONGS   => "No pertenece con a esta empresa"
    );
    
    protected $_modelName = 'Application_Model_UsuarioEmpresa';
    
    /*
     * Application_Model_AnuncioUsuarioEmoresa
     */
    private $_adUserCompanyModel = null;
    
    public function __construct($id = null)
    {
        parent::__construct($id);
        
        $this->_adUserCompanyModel = new 
                Application_Model_AnuncioUsuarioEmpresa;
    }
    
    public function isAssign($adId, $data = array())
    {
        $userCompany = $this->getData($data);
        
        $assign = $this->_adUserCompanyModel->obtenerPorAnuncioYUsuario(
                $adId, $userCompany['id'], array('id'));
                        
        if (!isset($assign)) {
            $this->_error(self::ASSIGN);
            return false;
        }
        
        return true;
    }
    
    public function isCreator($data = array())
    {
        $userCompany = $this->getData($data);
        
        if ($userCompany['creador'] != 
                Application_Model_UsuarioEmpresa::PRINCIPAL) {
            $this->_error(self::CREATOR);
            return false;
        }
        
        return true;
    }
    
    public function belongsTo($companyId, $data = array())
    {
        $userCompany = $this->getData($data);
                        
        if ($userCompany['id_empresa'] != $companyId) {
            $this->_error(self::BELONGS);
            return false;
        }
        
        return true;
    }
}