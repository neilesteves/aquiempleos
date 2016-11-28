<?php
/**
 * @author Carlos Muñoz <camura8503@gmail.com>
 */

class App_Service_ReassignProcesses
{
    /**
     * @var Application_Model_AnuncioUsuarioEmpresa
     */
    private $_adUserCompanyModel;
    
    /**
     * @var Application_Model_UsuarioEmpresa
     */
    private $_userCompanyModel;
    
    /**
     * @var Application_Model_AnuncioWeb
     */
    private $_adModel;
    
    /**
     * @var App_Controller_Action_Helper_Mail();
     */
    private $_mail;
    
    private $_message       = null;
    
    const MSJ_DELETE        = 'Se quito la asignación con éxito';
    const MSJ_SUCCESFUL     = 'Se asignó el proceso correctamente';    
    const MSJ_NOT_ASSIGNED  = 'No tiene un administrador asignado';

    public function __construct()
    {
        $this->_adUserCompanyModel  = new 
                Application_Model_AnuncioUsuarioEmpresa;
        $this->_userCompanyModel    = new Application_Model_UsuarioEmpresa;
        $this->_adModel             = new Application_Model_AnuncioWeb;
        
        $this->_mail = new App_Controller_Action_Helper_Mail();
    }
    
    public function assignAd(
            $userCompanyId, $adId, $companyId)
    {
        if (!$this->isValid($userCompanyId, $adId, $companyId))
            return false;                
        
        $newAdministrator = $this->_userCompanyModel->obtenerConUsuario(
                $userCompanyId);
                
        $previousAdministrator = 
                $this->_adUserCompanyModel->obtenerAdministrador(
                        $adId);
        
        $ad = $this->_adModel->obtenerPorId($adId, array('id', 'puesto'));
        
        if (!empty($previousAdministrator))
            $this->remove($previousAdministrator['usuario_empresa_id'], $adId);                
        
        $this->_adUserCompanyModel->asignar($userCompanyId, $adId);
        
        $this->_notify($newAdministrator, $previousAdministrator, $ad);
        
        $this->_message = self::MSJ_SUCCESFUL;
        return true;
    }
    
    public function remove($userCompanyId, $adId)
    {
        if ($this->_adUserCompanyModel->quitar($userCompanyId, $adId) > 0) {
            $this->_message = self::MSJ_DELETE;
            return true;
        }
        
        $this->_message = self::MSJ_NOT_ASSIGNED;
        return false;
    }
    
    public function isValid($userCompanyId, $adId, $companyId)
    {
        $anuncio        = new App_Service_Validate_Ad($adId);
        $userCompany    = new App_Service_Validate_UserCompany($userCompanyId);
        
        if ($anuncio->isNull()) {
            $this->_message = $anuncio->getMessage();
            return false;
        }
        
        if (!$anuncio->belongsTo($companyId) ||
                !$userCompany->belongsTo($companyId)) {
            $this->_message = $anuncio->getMessage();
            return false;
        }
        
        return true;
    }
    
    public function getMessage()
    {
        return $this->_message;
    }   
    
    private function _notify($newAdministrator, $previousAdministrator, $ad)
    {
        $data = array();
        $data['puesto'] = $ad['puesto'];
        $data['nombre'] = $newAdministrator['nombres'] . 
                ' ' . $newAdministrator['apellidos'];
        $data['to']     = $newAdministrator['email'];
        
        $this->_mail->notificarNuevoAdministradorProceso($data);
        
        if (!empty($previousAdministrator)) {
            $data['to']              = $previousAdministrator['email'];
            $data['nombre_anterior'] = $previousAdministrator['nombres'] . 
                    ' ' . $previousAdministrator['apellidos'];
            
            $this->_mail->notificarCambioAdministradorProceso($data);
        }
    }
}