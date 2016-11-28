<?php

class Empresa_ActivarMembresiaController extends App_Controller_Action_Postulante
{
    private $_compra;
    private $_perfil;
    private $_adecsysEnte;

    public function init() 
    {
        $this->_usuario = new Application_Model_Usuario();
        if (isset($this->auth['usuario']->id) && $this->_usuario->hasvailBlokeo($this->auth['usuario']->id) ) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::forgetMe();
            $this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
            $this->_redirect('/empresa');
        }
        $this->_compra = new Application_Model_Compra();
        $this->_perfil = new Application_Model_PerfilDestacado();
        $this->_adecsysEnte = new Application_Model_AdecsysEnte();
        $this->_config = Zend_Registry::get('config');
        parent::init();
    }

    public function indexAction()
    {        
        $token = $this->_getParam('token');
        if (!$token) {
            $this->_redirect('/');
        }
        
        $ipClient = new Zend_Session_Namespace('client');  
        if (!$ipClient->ip) {
            $ipClient->ip = $_SERVER['REMOTE_ADDR'];
        }            
               
        $token = base64_decode($token);        
        $modelCompra =  new Application_Model_Compra();        
        $compra = $modelCompra->getDetalleCompraMembresiaByToken($token);               
        if ($compra) {
            $this->view->compra = $compra;
            $this->view->moneda = $this->_config->app->moneda;
        } else {
            $this->_redirect('/');
        }            
        
    }
    
    public function registrarContratoAction ()
    {
        if ($this->getRequest()->isPost()) {
            $tokenPost = $this->getRequest()->getPost('tok', null);            
            $nunContrato = $this->getRequest()->getPost('num_contrato', null);
           
            if ($tokenPost && $nunContrato) {
                $nunContrato = substr($nunContrato, 0, 10);
                $modelCompra =  new Application_Model_Compra();
                $compra = $modelCompra->getDetalleCompraMembresiaByToken($tokenPost);                          
                if ($compra) {
             
                    $modelEmpresaMemb = new Application_Model_EmpresaMembresia();
                    
                    $tieneContratoPagado = (empty($compra['nroContratoMembresia']) 
                            && ($compra['estadoCompra'] == Application_Model_Compra::ESTADO_PAGADO));
                    
                    $yaExisteContrato = $modelEmpresaMemb->existeNroContrato($nunContrato);
                 
                    if ($yaExisteContrato) {
                        $this->getMessenger()->error('El número de contrato ya se encuentra registrado.');
                    } else {
                        if ($tieneContratoPagado) {
                            $ipClient = new Zend_Session_Namespace('client'); 
                            
                            $modelCompra->registraNroContratoMembresia($tokenPost, $nunContrato, $ipClient->ip,$compra['em_idempresa'],$compra['IdEmprMemb']);

                            $modelEmpresaMemb = new Application_Model_EmpresaMembresia();                          
                            $membActivas = $modelEmpresaMemb->getExistsActive($compra['em_idempresa']);

                            if (!$membActivas) {                            
                                $modelEmpresaMemb->activarMembresiaByToken(
                                    $tokenPost
                                );
                                $modelEmpresaMemb->notificarActivacionMembresia($compra['compraId']);
                            }                                                


                            $this->getMessenger()->success('El número de contrato se actualizó con éxito.');

                            unset($ipClient->ip);

                           
                            $modelEmpresaMemb->notificarFacturacionMembresia($compra['compraId']);


                        }
                    }
                                                                                                   
                    // Volver a la vista del formulario
                    $tokenPost = base64_encode($tokenPost);
                    $this->_redirect('/activar-membresia/'.$tokenPost);
                    
                }                    
            }
            
        } 
        
        $this->_redirect('/');
        
    }
    

}
