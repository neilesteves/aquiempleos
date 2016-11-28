<?php

class Admin_ApiController extends App_Controller_Action_Admin
{
    protected $_messageSuccess = 'Actualización exitosa.';
    protected $_messageError = 'Error al momento de guardar.';
    protected $_cache = null;
    
    public function init()
    {
        parent::init();
        
        if ($this->_cache==null) {
            $this->_cache = Zend_Registry::get('cache');
        } 
        
        $this->view->rol = $this->auth['usuario']->rol;
    }

    public function indexAction()
    {
        
    }
    public function listarUsuariosAction()
    {
        
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/administrador/micuenta.admin.js')
        );
        
        $this->view->headLink()->appendStylesheet(
                $this->view->S(
                '/js/datepicker/themes/redmond/ui.all.css', 'all')
        );
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/datepicker/ui/ui.core.js')
        );
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/datepicker/ui/ui.datepicker.js')
        );
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/datepicker/ui/i18n/ui.datepicker-es.js')
        );        
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/administrador/admin.api.js')
        );
                
        $this->view->menu_sel_side = self::MENU_POST_SIDE_API_LISTARUSUARIO;
        $objApi = new Application_Model_Api();
        
        $this->view->idEmpresa = $idEmpresa = $this->_getParam('rel', null);
        $this->view->rol = $this->auth['usuario']->rol;
        $this->view->flag = 0;
        
        if (isset($idEmpresa)) {
            $sess = $this->getSession();
            $this->view->empresaAdminUrl = $this->view->url($sess->empresaAdminUrl, 'default', false);
            
            //$this->view->flag = 1;
            $modelEmpresa = new Application_Model_Empresa();
            $arrayEmpresa = $modelEmpresa->getEmpresa($idEmpresa);
            $arrayEM = $modelEmpresa->getEmpresaMembresia($idEmpresa);
            $this->view->activo = $arrayEmpresa['activo'];
            $this->view->razonsocial = $arrayEmpresa['razonsocial'];
            $this->view->membresiaTipo = $arrayEM['membresia_info']['membresia']['m_nombre'];
            $this->view->idTipoMembresia = $arrayEM['membresia_info']['membresia']['id_membresia'];
        }
        
        $this->view->col = $col = $this->_getParam('col', '');
        $this->view->ord = $ord = $this->_getParam('ord');
        $this->view->page = $page = $this->_getParam("page", 1);
        
        $paginator = $objApi->listarApi($col, $ord, $idEmpresa);
        
        $this->view->mostrando = "Mostrando "
                             .$paginator->getItemCount($paginator->getItemsByPage($page))." de "
                             .$paginator->getTotalItemCount();
        
        $this->view->paginador = $paginator;
        
        $paginator->setCurrentPageNumber($page);
        
        
    }
    public function agregarUsuarioAction()
    {
        $this->view->menu_sel_side = self::MENU_POST_SIDE_API_AGREGARUSUARIO;
        
        $this->view->headLink()->appendStylesheet(
                $this->view->S(
                '/js/datepicker/themes/redmond/ui.all.css', 'all')
        );
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/datepicker/ui/ui.core.js')
        );
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/datepicker/ui/ui.datepicker.js')
        );
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/datepicker/ui/i18n/ui.datepicker-es.js')
        );        
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/administrador/admin.api.js')
        );
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/administrador/admin.api.js')
        );        
        
        $formAgregarUsuario = new Application_Form_ApiAgregarUsuario();
        
        $apiHelper = $this->_helper->getHelper('Api');
        
        if ($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();
            
            $domainMod = strtolower(substr($postData['domain'], 0, 3));
            if ($domainMod != 'www' || $domainMod != Application_Form_Paso1Postulante::$_defaultWebsite) {
                $postData['domain'] = Application_Form_Paso1Postulante::$_defaultWebsite.$postData['domain'];
            }
            
            $postData['ip'] = $apiHelper->getRealIp($postData['domain']);
            if ($formAgregarUsuario->isValid($postData)) {
                $apiHelper->insertarUsuario($postData);
                $this->getMessenger()->success('Se agrego el usuario con éxito.');
                $this->_redirect('/admin/api/agregar-usuario/');
            }
        }
        $this->view->formAgregarUsuario = $formAgregarUsuario;
    }
    
    public function editarUsuarioAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $err = 0;
        
        $usuarioApi = $this->_getParam('idUsuApi');
        $apiModel = new Application_Model_Api();
        $dataApi = $apiModel->getDatosByApi($usuarioApi);
        $dataApi['usuario'] = $dataApi['email'];
        if ($dataApi['vigencia'] != 0) {
            $dataApi['fecha_ini'] = date('d/m/Y', strtotime($dataApi['fecha_ini']));
            $dataApi['fecha_fin'] = date('d/m/Y', strtotime($dataApi['fecha_fin']));
        }
        $dataApi['idUsuApi'] = $usuarioApi;        
        $formAgregarUsuario = new Application_Form_ApiAgregarUsuario(true);
        $formAgregarUsuario->isValid($dataApi);
        if ($formAgregarUsuario->domain->getValue() == "") {
            // @codingStandardsIgnoreStart
            $formAgregarUsuario->force_domain->setValue(true);
            // @codingStandardsIgnoreEnd
        }
        
        $apiHelper = $this->_helper->getHelper('Api');
        
        if ($this->_request->isPost()) {
            $postData = $this->_getAllParams();
            
            $domainMod = strtolower(substr($postData['domain'], 0, 3));
            $domainModDos = strtolower(substr($postData['domain'], 0, 7));
            
            if ($domainMod != 'www' && $domainModDos != Application_Form_Paso1Postulante::$_defaultWebsite) {
                $postData['domain'] = Application_Form_Paso1Postulante::$_defaultWebsite.$postData['domain'];
            }
                                    
            if ($formAgregarUsuario->isValid($postData) && $this->_hash->isValid($postData['tok'])) {
                unset($postData['tok']);
                $apiHelper->actualizarUsuario($postData);
                @$this->_cache->remove('Empresa_getEmpresaHome_');
                $err = 1;
            } else {
                $err = -1;
            }
            
        }
        
        $this->view->formAgregarUsuario = $formAgregarUsuario;
        $this->view->error = $err;
    }
    
    public function verDatosApiAction()
    {
        $this->_helper->layout->disableLayout();
        $usuarioApi = $this->_getParam('idUsuApi');
        $apiModel = new Application_Model_Api();
        $dataApi = $apiModel->getDatosByApi($usuarioApi);
        $this->view->dataApi = $dataApi;
    }
    
    public function desactivarUsuarioAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
            $idApi = $this->_getParam('idUsuApi');
            $token = $this->_getParam('tok');
            if ($idApi && $token) {

                if (crypt($idApi, $token) === $token ) {
                    $apiModel = new Application_Model_Api();
                    $apiModel->darDeBaja($idApi);
                    @$this->_cache->remove('Empresa_getEmpresaHome_');
                }

            }
        }
        exit;
        
    }
    
    public function activarUsuarioAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
            $idApi = $this->_getParam('idUsuApi');
            $token = $this->_getParam('tok');

            if ($idApi && $token) {

                if (crypt($idApi, $token) === $token ) {
                    $apiModel = new Application_Model_Api();
                    $apiModel->activar($idApi);
                }

            }
        }
        exit;
        
    }
    
    public function validarUrlAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $domain = $this->_getParam('domain');
        
        $validateDomain = new App_Validate_Domain();
        if ($validateDomain->isValid($domain) === true) {
            $response = array ('status' => 'Ok', 'msg' => 'Si existe el dominio');
        } else {
            $response = array ('status' => 'Error', 'msg' => 'No existe el dominio');
        }
        $this->_response->appendBody(Zend_Json::encode($response));
    }
    
    public function validarEmailAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $email = $this->_getParam('email');
        
        $empresaModel = new Application_Model_Empresa();
        $empresa = $empresaModel->getEmpresaByEmail($email, '');
        $empresaId = $empresa['idempresa'];
        if ($empresaId === null) {
            $result = false;
        } else {
            $result = false;
        }
        $adapter = new Application_Model_Api();
        $sql = $adapter->getAdapter()->select()
                ->from(
                    array('a' => 'api'),
                    array(
                        'id'  =>'a.id',
                    )
                )
                ->where($adapter->getAdapter()->quoteInto('a.usuario_id = ?', $empresaId));
                
        $result = $adapter->getAdapter()->fetchAll($sql);
        
        //var_dump($result);
        if (count($result) > 0) {
            $result = false;
        } else {
            $result = true;
        }
        if ($result === true) {
            $response = array('status' => 'Ok', 'msg' => 'El email es válido');
        } else {
            $response = array('status' => 'Error', 'msg' => 'El email no es válido');
        }
        $this->_response->appendBody(Zend_Json::encode($response));
    }
}

