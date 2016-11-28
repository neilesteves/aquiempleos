<?php

class Empresa_PostulantesBloqueadosController extends App_Controller_Action_Empresa {
    
    CONST ITEMS_PER_PAGE     = 10;
    
    CONST MSJ_DESBLOQUEO_EXITOSO       = 'Postulante desbloqueado';
    CONST MSJ_DESBLOQUEO_REDUNDANTE    = 'El postulante ya se encuentra desbloqueado';
    CONST MSJ_DESBLOQUEO_INCOMPLETO    = 'Ocurrio un error';
    
    CONST ORDEN_ASCENDENTE  = 'asc';
    CONST ORDEN_DESCENDENTE = 'desc';
    
    /**
    * @var Application_Model_EmpresaPostulanteBloqueado
    */    
    private $_empresaPostulanteBloqueadoModelo  = null;
    
    /**
    * @var Application_Model_Postulacion
    */
    private $_postulacionModelo                 = null;
    
    /**
    * @var Application_Model_HistoricoPs
    */
    private $_historicoPsModelo                 = null;
    
    /**
    * @var Application_Model_AnuncioWeb
    */
    private $_anuncioWebModelo                  = null;           
    
    public function init() 
    {
        parent::init();
        $this->_usuario = new Application_Model_Usuario();
        if (isset($this->auth['usuario']->id) && $this->_usuario->hasvailBlokeo($this->auth['usuario']->id) ) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::forgetMe();
            $this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
            $this->_redirect('/empresa');
        }
        $this->_empresaPostulanteBloqueadoModelo = 
                new Application_Model_EmpresaPostulanteBloqueado;
        $this->_postulacionModelo   = new Application_Model_Postulacion;
        $this->_historicoPsModelo   = new Application_Model_HistoricoPs;
        $this->_anuncioWebModelo    = new Application_Model_AnuncioWeb;
    }
    
    public function listarAction()
    {                     
        $empresaId      = $this->auth['empresa']['id'];
        $paginaActual   = $this->_getParam('pagina', 1);
        $orden          = $this->_getParam('orden', null);
        $columna        = $this->_getParam('columna', null);
                
        $postulantesBloqueados = 
            $this->_empresaPostulanteBloqueadoModelo->obtenerPostulantesPorEmpresa(
                    $empresaId);               
        
        $ordenamiento  = $this->_agregarOrdenamiento($columna, $orden);
        
        if (!is_null($columna)) {
            $postulantesBloqueados = 
                $this->_empresaPostulanteBloqueadoModelo->ordenarBloqueados(
                        $columna, $orden, $postulantesBloqueados);           
        }       

        $postulantesBloqueados = Zend_Paginator::factory($postulantesBloqueados);
        $postulantesBloqueados->setItemCountPerPage(self::ITEMS_PER_PAGE);
        $postulantesBloqueados->setCurrentPageNumber($paginaActual);
                                
        $totalBloqueados         = $postulantesBloqueados->getTotalItemCount();        
        $totalBloqueadosEnRango  = 0;        
        
        if ($totalBloqueados > 0) {
            $paginas = $postulantesBloqueados->getPagesInRange(1, $paginaActual);
            
            foreach ($paginas as $pagina) {                
                $numeroDeItems = $postulantesBloqueados
                        ->setCurrentPageNumber($pagina)->getCurrentItemCount();
                
                $totalBloqueadosEnRango += $numeroDeItems;
            }
        }
        
        $codeBuscador = Application_Model_Beneficio::CODE_BUSCADOR;
        
        $beneficios = $this->_anuncioWebModelo->tieneBeneficio(
            $this->auth['empresa']['id'], $codeBuscador, true
        );
        
        $buscadorAptitus = FALSE;
        if ($beneficios > 0)
            $buscadorAptitus = true;
        
        if (isset($this->auth['empresa']['membresia_info']['beneficios'])) {
            $buscadorAptitus = true;
        }
                
        $this->view->menu_sel               = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel          = self::MENU_POST_POSTULANTES_BLOQUEADOS;
        $this->view->buscadorAptitus        = $buscadorAptitus;        
        $this->view->pagina                 = $paginaActual;
        $this->view->orden                  = $ordenamiento;
        $this->view->postulantesBloqueados  = $postulantesBloqueados;
        $this->view->totalBloqueados        = $totalBloqueados;
        $this->view->totalBloqueadosPagina  = $totalBloqueadosEnRango;
        
    }
    
    public function buscarAction()
    {               
        $empresaId      = $this->auth['empresa']['id'];
        $paginaActual   = $this->_getParam('pagina', 1);
        $orden          = $this->_getParam('orden', null);
        $columna        = $this->_getParam('columna', null);
        $criterio       = $this->_getParam('criterio');
        
        $postulantesBloqueados = 
                $this->_empresaPostulanteBloqueadoModelo->buscar(
                        $empresaId, $criterio);
        
        $ordenamiento  = $this->_agregarOrdenamiento($columna, $orden);

        if (!is_null($columna)) {
            $postulantesBloqueados = 
                $this->_empresaPostulanteBloqueadoModelo->ordenarBloqueados(
                        $columna, $orden, $postulantesBloqueados);           
        }
        
        $postulantesBloqueados = Zend_Paginator::factory($postulantesBloqueados);
        $postulantesBloqueados->setItemCountPerPage(self::ITEMS_PER_PAGE);
        $postulantesBloqueados->setCurrentPageNumber($paginaActual);
        
        $totalBloqueados         = $postulantesBloqueados->getTotalItemCount();        
        $totalBloqueadosEnRango  = 0;        
        
        if ($totalBloqueados > 0) {
            $paginas = $postulantesBloqueados->getPagesInRange(1, $paginaActual);
            
            foreach ($paginas as $pagina) {                
                $numeroDeItems = $postulantesBloqueados
                        ->setCurrentPageNumber($pagina)->getCurrentItemCount();
                
                $totalBloqueadosEnRango += $numeroDeItems;
            }
        }
        
        $codeBuscador = Application_Model_Beneficio::CODE_BUSCADOR;
        
        $beneficios = $this->_anuncioWebModelo->tieneBeneficio(
            $this->auth['empresa']['id'], $codeBuscador, true
        );
        
        $buscadorAptitus = FALSE;
        if ($beneficios > 0)
            $buscadorAptitus = true;
        
        if (isset($this->auth['empresa']['membresia_info']['beneficios'])) {
            $buscadorAptitus = true;
        }
        
        $this->view->menu_sel               = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel          = self::MENU_POST_POSTULANTES_BLOQUEADOS;
        $this->view->buscadorAptitus        = $buscadorAptitus;
        $this->view->pagina                 = $paginaActual;
        $this->view->criterio               = $criterio;
        $this->view->orden                  = $ordenamiento;
        $this->view->postulantesBloqueados  = $postulantesBloqueados;
        $this->view->totalBloqueados        = $totalBloqueados;
        $this->view->totalBloqueadosPagina  = $totalBloqueadosEnRango;
    }
    
    public function desbloquearAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
       
        $postulanteId = $this->_getParam('postulante-id');
        $empresaId = $this->auth['empresa']['id'];
        
        $respuesta = $this->_desbloquear($postulanteId, $empresaId);
        
        $this->_response->appendBody(Zend_Json::encode($respuesta));       
    }
    
    public function desbloquearVariosAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $postulantesId = $this->_getParam('id');
              
        $empresaId = $this->auth['empresa']['id'];      
        
        $respuesta = array();
        
        foreach ($postulantesId as $postulanteId) {
            $respuesta['postulantes'][$postulanteId] = $this->_desbloquear(
                    $postulanteId, $empresaId);
        }              
        
        $this->_response->appendBody(Zend_Json::encode($respuesta));
    }
    
    private function _desbloquear($postulanteId, $empresaId)
    {
        $bloqueado = 
                $this->_empresaPostulanteBloqueadoModelo->obtenerPorEmpresaYPostulante(
                        $empresaId, $postulanteId, array('id'));

        $respuesta = Array();

        if (empty($bloqueado)) {
            $respuesta['estado']    = self::PROCESO_REDUNDANTE;
            $respuesta['mensaje']   = self::MSJ_DESBLOQUEO_REDUNDANTE;
            return $respuesta;
        }
        
        $postulaciones = $this->_postulacionModelo->obtenerPorEmpresaYPostulante(
                $empresaId, 
                $postulanteId, array('id', 'id_anuncio_web'));                

        $db = $this->getAdapter();
        
        try {
            $this->_empresaPostulanteBloqueadoModelo->desbloquear(
                    $empresaId, $postulanteId);
            
            foreach ($postulaciones as $postulacion) {
                $this->_postulacionModelo->desbloquear($postulacion['id']);
                $this->_historicoPsModelo->registarDesbloqueo($postulacion['id']); 
                
                $numeroDePostulantes = 
                        $this->_postulacionModelo->getPostulantesByAviso(
                                $postulacion['id_anuncio_web']);                
                $this->_anuncioWebModelo->actualizarPostulantes(
                        $postulacion['id_anuncio_web'], $numeroDePostulantes);                               
            }
 
        } catch (Zend_Db_Exception $e) {
            $respuesta['estado']    =   self::PROCESO_INCOMPLETO;
            $respuesta['mensaje']    =   self::MSJ_DESBLOQUEO_INCOMPLETO;            
            return $respuesta;
        }

        $respuesta['estado']    =   self::PROCESO_EXITOSO;
        $respuesta['mensaje']    =   self::MSJ_DESBLOQUEO_EXITOSO;
        return $respuesta;
    }
    
    private function _agregarOrdenamiento($columna = null, $orden = null)
    {
        $ordenamiento = array();
        $ordenamiento['path_foto']          = self::ORDEN_ASCENDENTE;
        $ordenamiento['nombres']            = self::ORDEN_ASCENDENTE;
        $ordenamiento['sexo']               = self::ORDEN_ASCENDENTE;
        $ordenamiento['edad']               = self::ORDEN_ASCENDENTE;
        $ordenamiento['nivel_nombre']       = self::ORDEN_ASCENDENTE;
        $ordenamiento['otro_carrera']       = self::ORDEN_ASCENDENTE;
        $ordenamiento['fecha_bloqueo']      = self::ORDEN_ASCENDENTE;

        if (!is_null($columna)) {                        
            if ($orden == self::ORDEN_DESCENDENTE) {
                $ordenamiento[$columna] = self::ORDEN_ASCENDENTE;
            } else {
                $ordenamiento[$columna] = self::ORDEN_DESCENDENTE;
            }
            
        }

        return $ordenamiento;
    }
}