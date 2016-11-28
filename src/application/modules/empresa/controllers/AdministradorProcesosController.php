<?php

class Empresa_AdministradorProcesosController extends App_Controller_Action_Empresa
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
    private $empresa;

    private $_usuarioEmpresa;    
    
    const MSJ_ASIGNACION_CORRECTA   = 'Se asignó el proceso con éxito';
    const MSJ_ELIMINAR_CORRECTA     = 'Se quito la asignación con éxito';

    public function init()
    {
        parent::init();
        $this->_usuario = new Application_Model_Usuario();
        $this->empresa = new Application_Model_Empresa();
        if (isset($this->auth['usuario']->id) && $this->_usuario->hasvailBlokeo($this->auth['usuario']->id) ) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::forgetMe();
            $this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
            $this->_redirect('/empresa');
        }
        $beneficios = $this->auth['empresa']['membresia_info']['beneficios'];
        $codigo     = Application_Model_Beneficio::CODE_REASIGNAR_PROCESOS;

        if (!isset($beneficios->$codigo)) {
            $this->_redirect('empresa/mi-cuenta');
        }
        
        $this->_anuncioUsuarioEmpresaModelo = new 
                Application_Model_AnuncioUsuarioEmpresa;
        
        $this->_anuncioModelo           = new Application_Model_AnuncioWeb;
        $this->_usuarioEmpresaModelo    = new Application_Model_UsuarioEmpresa;
        $administrador_Id               = $this->_getParam('administrador_id', null);
        $this->_empresaId               = 
                $this->auth['usuario-empresa']['id_empresa'];
                
        $usuarioLogeado = new App_Service_Validate_UserCompany;
        if (!$usuarioLogeado->isCreator($this->auth['usuario-empresa'])) {
            $this->_redirect('empresa/mi-cuenta');
        }
        
        $administradorSecundario = 
                new App_Service_Validate_UserCompany($administrador_Id);
        
        if ($administradorSecundario->isCreator()) {
            $this->_redirect('empresa/mi-cuenta');
        }
        $this->_tieneBolsaCVs=0;
        if(isset($this->auth['empresa']['membresia_info']['membresia']['id_membresia'])){
          $this->_tieneBolsaCVs = !empty($this->auth['empresa']['membresia_info']['beneficios']->bolsacv)?1:0;
        }       
        $this->view->tieneBolsaCVs = $this->_tieneBolsaCVs;
        $this->view->tieneBuscador = $this->_anuncioweb->beneficioBuscadorAptitus($this->auth);
        $this->_usuarioEmpresa = $administradorSecundario->getData();
        $this->view->Look_Feel= $this->empresa->LooFeelActivo($this->auth['empresa']['id'])    ;

        
    }

    public function asignadosAction()
    {
        $this->_helper->layout->disableLayout();
        
        $areaModelo         = new Application_Model_Area;
        
        $usuarioEmpresaId   = $this->_getParam('administrador_id', null);
        $pagina             = $this->_getParam('pagina', 1);                
        $procesosAsignados  = $this->_paginado($usuarioEmpresaId, $pagina);
        $areas              = $areaModelo->getAreasAviso();                
        
        $this->view->procesosAsignados      = $procesosAsignados;
        $this->view->areas                  = $areas;
        $this->view->usuarioEmpresa         = $this->_usuarioEmpresa;        
    }
    
    public function listarAction()
    {
        $this->_helper->layout->disableLayout();
        $usuarioEmpresaId   = $this->_getParam('administrador_id', null);
        $pagina             = $this->_getParam('pagina', 1);        
        $procesosAsignados  = $this->_paginado($usuarioEmpresaId, $pagina);
        
        $this->view->procesosAsignados      = $procesosAsignados;
    }
    
    private function _paginado($usuarioEmpresaId, $pagina = 1)
    {
        $numeroItems = $this->config->paginado->numeroItems;
        $selectAsignados = 
                $this->_anuncioUsuarioEmpresaModelo->obtenerAnunciosPorUsuario(
                        $usuarioEmpresaId);
        
        $procesosAsignados = Zend_Paginator::factory($selectAsignados);        
        $procesosAsignados->setItemCountPerPage($numeroItems);
        $procesosAsignados->setCurrentPageNumber($pagina);
        
        return $procesosAsignados;
    }
    
    public function procesosNoAsignadosAction()
    {
        $areaId         = $this->_getParam('area_id', null);
        $usuarioEmpresa = $this->_usuarioEmpresa;
        $procesos       = $this->_anuncioModelo->obtenerNoAsginadosUsuario(
                $usuarioEmpresa['id'], $usuarioEmpresa['id_empresa'], $areaId);
        $this->_helper->json->sendJson($procesos);
    }
    
    
    public function asignarAction()
    {
        $anuncioId          = $this->_getParam('anuncio_id', null);
        $administradorId    = $this->_getParam('administrador_id', null);
        
        $servicio = new App_Service_ReassignProcesses;
        
        $respuesta['estado']    = self::PROCESO_EXITOSO;
        $respuesta['mensaje']   = self::MSJ_ASIGNACION_CORRECTA;
        
        if (!$servicio->assignAd(
                $administradorId, $anuncioId, $this->_empresaId)) {
            $respuesta['estado']  = self::PROCESO_INCOMPLETO;
            $respuesta['mensaje'] = $servicio->getMessage();
        }
        
        $this->_helper->json->sendJson($respuesta);
    }
    
    public function quitarAction()
    {
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