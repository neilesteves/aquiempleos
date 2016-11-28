<?php

class Empresa_ProcesoAdministradorController extends App_Controller_Action_Empresa
{
    /**
     * @var Application_Model_AnuncioUsuarioEmpresa
     */
    private $_anuncioUsuarioEmpresaModelo;
    
    /**
     * @var Application_Model_AnuncioWeb
     */
    private $_anuncioModelo;
    
    /**
     * @var Application_Model_UsuarioEmpresa
     */
    private $_usuarioEmpresaModelo;
    
    private $_empresaId;
    
    const MSJ_ASIGNACION_CORRECTA   = 'Se asignó el proceso con éxito';
    const MSJ_ELIMINAR_CORRECTA     = 'Se quito la asignación con éxito';

    public function init()
    {
        parent::init();
        $beneficios = $this->auth['empresa']['membresia_info']['beneficios'];
        $codigo     = Application_Model_Beneficio::CODE_REASIGNAR_PROCESOS;

        $this->_usuario = new Application_Model_Usuario();
        if (isset($this->auth['usuario']->id) && $this->_usuario->hasvailBlokeo($this->auth['usuario']->id) ) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::forgetMe();
            $this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
            $this->_redirect('/empresa');
        }
        if (!isset($beneficios->$codigo))
            $this->_redirect('empresa/mi-cuenta');        
        
        $this->_anuncioUsuarioEmpresaModelo = new 
                Application_Model_AnuncioUsuarioEmpresa;
        
        $this->_anuncioModelo           = new Application_Model_AnuncioWeb;
        $this->_usuarioEmpresaModelo    = new Application_Model_UsuarioEmpresa;
        $this->_empresaId               = 
                $this->auth['usuario-empresa']['id_empresa'];

        $servicioValidador = new App_Service_Validate_UserCompany;
        if (!$servicioValidador->isCreator($this->auth['usuario-empresa']))
            $this->_redirect('empresa/mi-cuenta');        
    }

    public function asignadoAction()
    {
        $this->_helper->layout->disableLayout();

        $usuarioEmpresaModelo = new Application_Model_UsuarioEmpresa;
        $anuncioModelo        = new Application_Model_AnuncioWeb;
        $anuncioId            = $this->_getParam('anuncio_id', null);
        $administradores = 
                $usuarioEmpresaModelo->obtenerSecundariosNoAsignados(
                        $this->_empresaId, $anuncioId);
        
        $administradorAsignado = 
            $this->_anuncioUsuarioEmpresaModelo->obtenerAdministrador(
                    $anuncioId);
        if (empty($administradorAsignado))
            $administradorAsignado = null;
        
        $anuncio = $anuncioModelo->obtenerPorId(
                $anuncioId, array('id', 'puesto'));
        
        $this->view->administradores        = $administradores;
        $this->view->administradorAsignado  = $administradorAsignado;
        $this->view->anuncioId              = $anuncioId;
        $this->view->anuncio                = $anuncio;
    }
    
    public function asignarAction()
    {
        $this->_helper->layout->disableLayout();
        
        $administradorId = $this->_getParam('administrador_id', null);
        $anuncioId       = $this->_getParam('anuncio_id', null);

        $servicio = new App_Service_ReassignProcesses;        
        
        $respuesta['estado']    = self::PROCESO_EXITOSO;
        $respuesta['mensaje']   = self::MSJ_ASIGNACION_CORRECTA;
        
        if (!$servicio->assignAd(
                $administradorId, $anuncioId, $this->_empresaId)) {
            $respuesta['estado']  = self::PROCESO_INCOMPLETO;
            $respuesta['mensaje'] = $servicio->getMessage();
        }
        
        $administrador = $this->_usuarioEmpresaModelo->obtenerPorId(
                $administradorId, array('id', 'nombres', 'apellidos'));
        
        $respuesta['administrador'] = $administrador;
        
        $this->_helper->json->sendJson($respuesta);              
    }
    
    public function quitarAction()
    {
        $this->_helper->layout->disableLayout();
        
        $anuncioId          = $this->_getParam('anuncio_id', null);
        $administradorId    = $this->_getParam('administrador_id', null);
        
        $servicio = new App_Service_ReassignProcesses;
        
        $respuesta['estado']    = self::PROCESO_EXITOSO;
        $respuesta['mensaje']   = self::MSJ_ELIMINAR_CORRECTA;
        
        if (!$servicio->remove($administradorId, $anuncioId)) {
            $respuesta['estado']    = self::PROCESO_INCOMPLETO;
            $respuesta['mensaje']   = $servicio->getMessage();
        }
        
        $this->_helper->json->sendJson($respuesta);
    }
}