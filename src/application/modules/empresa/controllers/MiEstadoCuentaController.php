<?php

class Empresa_MiEstadoCuentaController extends App_Controller_Action_Empresa
{
    protected $_tieneBuscador;
    protected $empresa;

    
    public function init()
    {
        parent::init();
        $this->_compra = new Application_Model_Compra();
        $this->empresa = new Application_Model_Empresa();
        $this->_anuncioweb = new Application_Model_AnuncioWeb();
        if ( Zend_Auth::getInstance()->hasIdentity()!= true ) {
            $this->_redirect('/empresa');
        }
        
        $this->_usuario = new Application_Model_Usuario();
        if (isset($this->auth['usuario']->id) && $this->_usuario->hasvailBlokeo($this->auth['usuario']->id) ) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::forgetMe();
            $this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
            $this->_redirect('/empresa');
        }
        $usuarioEmpresaDatos = $this->auth['usuario-empresa'];
        $usuarioEmpresa = new App_Service_Validate_UserCompany;
        
        if (!$usuarioEmpresa->isCreator($usuarioEmpresaDatos)) {
            $this->_redirect('/empresa');
        }
        
     
        $this->_tieneBolsaCVs=0;
        if(isset($this->auth['empresa']['membresia_info']['membresia']['id_membresia'])){
          $this->_tieneBolsaCVs = !empty($this->auth['empresa']['membresia_info']['beneficios']->bolsacv)?1:0;
        }
        $this->view->tieneBuscador = $this->_anuncioweb->beneficioBuscadorAptitus($this->auth);
        $this->view->tieneBolsaCVs =  $this->_tieneBolsaCVs  ;
        $this->view->auth           = $this->auth;
        $this->view->Look_Feel=$this->empresa->LooFeelActivo($this->auth['empresa']['id'])    ;
    }
    
    public function preDispatch()
    {
        parent::preDispatch();
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/empresa/estado.cuenta.js')
        );
    }

    public function indexAction()
    {
        $config = Zend_Registry::get('config');
        /*$this->view->headScript()
            ->appendFile($this->mediaUrl.'/js/empresa/empresa.aviso.paso4.js');*/
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/empresa/empresa.pestana.js')
        );
        $this->view->menu_sel = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MI_ESTADO_CUENTA;
        $this->view->menu_sidebar_sel = self::MENU_SIDEBAR_MI_ESTADO_CUENTA_AVISOS_PAGADOS;
        $this->view->col = $col = $this->_getParam('col');
        $this->view->ord = $ord = $this->_getParam('ord', '');
        $paginator = $this->_compra->getPaginatorPagados($this->auth['empresa']['id'], $col, $ord);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $this->view->estadoCuentaPagados = $paginator;
        $this->view->moneda = $config->app->moneda;
    }
    
    public function enProcesoAction()
    {
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/empresa/empresa.pestana.js')
        );
        $this->view->menu_sel = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MI_ESTADO_CUENTA;
        $this->view->menu_sidebar_sel = self::MENU_SIDEBAR_MI_ESTADO_CUENTA_AVISOS_EN_PROCESO;
        $this->view->col = $col = $this->_getParam('col', '');
        $this->view->ord = $ord = $this->_getParam('ord', '');
        $paginator = $this->_compra->getPaginatorEnProceso($this->auth['empresa']['id'], $col, $ord);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
        $this->view->estadoCuentaEnProceso = $paginator;
    }
    
    public function detalleCompraAction()
    {
        $config = Zend_Registry::get('config');
        $compraId = $this->_getParam('compra');
        if (
            !$this->_helper->Aviso->perteneceCompraAEmpresa(
                $compraId, $this->auth['empresa']['id']
            )
        ) {
            throw new App_Exception_Permisos();
        }
        $this->view->menu_sel = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MI_ESTADO_CUENTA;
        $this->view->detalleCompra = $this->_compra->getDetalleCompraAnuncio($compraId);
        $this->view->moneda = $config->app->moneda;
    }
    
    public function anularPagoEfectivoAction()
    {
        $this->_helper->layout->disableLayout();
        
        $compraId = $this->_getParam('compraId');
        if (
            !$this->_helper->Aviso->perteneceCompraAEmpresa(
                $compraId, $this->auth['empresa']['id']
            )
        ) {
            throw new App_Exception_Permisos();
        }
        if ($this->_hash->isValid($this->_getParam('csrfhash'))) {
            $this->view->headScript()->appendFile(
                    $this->view->S(
                    '/js/empresa/empresa.aviso.paso4.js')
            );
            $this->view->menu_sel = self::MENU_MI_CUENTA;
            $this->view->menu_post_sel = self::MENU_POST_MI_ESTADO_CUENTA;
            $this->view->menu_sidebar_sel = self::MENU_SIDEBAR_MI_ESTADO_CUENTA_AVISOS_PAGADOS;
            $cip = $this->_getParam('cip');
            $helper = $this->_helper->getHelper('WebServiceCip');
            $eliminarCip = $helper->eliminarCip($cip);
            // @codingStandardsIgnoreStart
            $eliminarCip->Estado;
            // @codingStandardsIgnoreEnd
            $where = $this->_compra->getAdapter()->quoteInto('id = ?', $compraId);
            $okUpdateP = $this->_compra->update(
                array(
                'estado' => 'anulado',
                ), $where
            );
        }
        $this->_redirect('/empresa/mi-estado-cuenta/en-proceso');
    }
    
    public function pagarOkAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $compraId = $this->_getParam('compra');
        if (!$this->_compra->verificarPagado($compraId)) {
            try{
                $this->_helper->aviso->confirmarCompraAviso($compraId);
            }catch(Exception $e){
                $flashMessenger = $this->_helper->getHelper('FlashMessenger');
                $flashMessenger->addMessage('Ocurrio un error al momento de registrar el aviso.');
            }
        }
        $this->_redirect('/empresa/comprar-aviso/pago-satisfactorio/compra/'.$compraId);

    }
    
    public function membresiasAction()
    {
        $config = Zend_Registry::get('config');
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/empresa/empresa.pestana.js')
        );
        $this->view->menu_sel = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MI_ESTADO_CUENTA;
        $this->view->menu_sidebar_sel = self::MENU_SIDEBAR_MI_ESTADO_CUENTA_MEMBRESIAS;
//        $this->view->headScript()->appendFile($this->mediaUrl . '/js/empresa/empresa.miestado.mismembresias.js');
        $membresias = Application_Model_Membresia
            ::getMembresiasAndConsumoTotalByEmpresaId($this->auth['empresa']['id']);
        $this->view->membresias = $membresias;
        $this->view->moneda = $config->app->moneda;
        
     
    }
    
    public function listaMembresiasAction()
    {
        $this->_helper->layout->disableLayout();
    }
    
    public function avisosMembresiaAction()
    {
        $config = Zend_Registry::get('config');
//        $this->_helper->layout->disableLayout();
        $idEmpresaMembresia = $this->_getParam('idEmpMem', 1);
        $avisos = Application_Model_Membresia::getAvisosConsumidosByMembresiaId($idEmpresaMembresia);
        $membresia = $membresias = Application_Model_Membresia::getInfoMembresiaPorEmpresaById($idEmpresaMembresia);
        $this->view->membresia = $membresia;
        $this->view->avisos = $avisos;
        $this->view->moneda = $config->app->moneda;
    }
}

