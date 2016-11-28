<?php

class Admin_TestimonioController extends App_Controller_Action_Admin
{

    protected $_url;
    protected $_cache = null;
    
    public function init()
    {
        parent::init();
        $this->_cache = Zend_Registry::get('cache');
        $this->_url = '/admin/gestion/testimonios';
        Zend_Layout::getMvcInstance()->assign('bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu'));
    }

    public function indexAction()
    {
       $this->_redirect('/admin/gestion/testimonios');
    }

    public function registrarTestimonioAction()
    {
        $config = Zend_Registry::get('config');
        
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/administrador/gestion.testimonio.js')
        );
        $objTestimonio = new Application_Model_Testimonio();
        $testimoniosActivos = $objTestimonio->getTestimoniosActivos();
        $this->view->testimoniosActivos = $testimoniosActivos;
        
        $formRegistroTestimonio = new Application_Form_RegistrarTestimonio();
        $usuarioId = $this->auth['usuario']->id;
        if ($testimoniosActivos == $this->config->testimonios->cantidad->testimonio) {
            $this->view->mensajeSuperado = 1;
        }
        $this->view->formRegistroTestimonio = $formRegistroTestimonio;

        $this->view->modulo = $this->getRequest()->getModuleName();
        $this->view->controlador = $this->getRequest()->getControllerName();

        
        $numtestimonio = $this->config->testimonios->cantidad->testimonio;
        $this->view->numtestimonio = $numtestimonio;
        $this->view->maxcaracteres = $this->config->testimonios->maxcaracteres->testimonio;        

        $pasoUpdate = 0;
        $pasoUpdate = $this->_getParam('rel');
        $this->_cache->remove('Testimonio_listarTestimonios');
        if ($pasoUpdate != 0) {
            $razonsocial = $testimonio = '';
            $resultList = $objTestimonio->searchTestimonio($pasoUpdate, $usuarioId);
            if ($resultList) {
                $razonsocial = $resultList['razonsocial'];

                $this->view->razonsocial = $razonsocial;
            }
        } else {
            if ($this->getRequest()->isPost()) {
                $data = $this->_getAllParams();
                $valid = $formRegistroTestimonio->isValid($data);
                if ($valid) {
                    $testimonio = $formRegistroTestimonio->getValues();                    
                    unset($testimonio['tok']);
                    
                    /*if($testimoniosActivos == $numtestimonio){
                        $testimonio['estado'] = 'inactivo';
                        $testimonio['orden'] = '0';
                    }*/
                    $testimonio['id_usuario'] = $usuarioId;
                    $testimonio['fecha_registro'] = date('Y-m-d');

                    $result = $objTestimonio->registrarTestimonio(
                        $testimonio, $this->config->testimonios->cantidad->testimonio
                    );
                   // var_dump($result); exit;
                    if ($result) {
                        $this->getMessenger()->success('Su registro se realizó de manera satisfactoria.');
                        $this->_cache->remove('Testimonio_listarTestimonios');
                        $this->_redirect('admin/gestion/testimonios');
                    } else {
                        $this->getMessenger()->error('Hubo un  error en el proceso de registro. Intente Nuevamente.');
                    }
                    /*try {
                        $db = $objTestimonio->getAdapter();
                        $db->beginTransaction();
                        $objTestimonio->insert($testimonio);
                        $db->commit();

                        $this->getMessenger()->success('Su registro se realizo de manera satisfactoria.');
                        $this->_redirect('admin/gestion/testimonios');
                    } catch (Exception $e) {
                        $db->rollBack();
                        $this->getMessenger()->error('Hubo un  error en el proceso de registro. Intente Nuevamente.');
                    }*/
                } else {
                    $formRegistroTestimonio->setDefaults($data);
                    $this->getMessenger()->error('Complete los datos correctamente.');
                }
            }
        }
    }

    public function editarTestimonioAction()
    {
        $this->view->headScript()->appendFile(
            '/js/administrador/gestion.testimonio.js'
        );
        $formRegistroTestimonio = new Application_Form_RegistrarTestimonio();        
        $this->view->formRegistroTestimonio = $formRegistroTestimonio;

        $this->view->modulo = $this->getRequest()->getModuleName();
        $this->view->controlador = $this->getRequest()->getControllerName();

        $config = Zend_Registry::get('config');
        $this->view->numtestimonio = $this->config->testimonios->cantidad->testimonio;
        $this->view->maxcaracteres = $this->config->testimonios->maxcaracteres->testimonio;
        $testimonioId=  $this->_getParam('id', null);
        $objTestimonio = new Application_Model_Testimonio();
        if ($testimonioId != null) {
            $testimonio = $objTestimonio->getTestimonioById($testimonioId);
            $formRegistroTestimonio->setDefaults($testimonio);
            $this->view->estado = $testimonio['estado'];
            $this->view->testimonioId = $testimonioId;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->_getAllParams();           
            $valid = $formRegistroTestimonio->isValid($data);            
            if ($valid) {
                $testimonio = $formRegistroTestimonio->getValues();
                if ($testimonio['estado'] == 'inactivo') {
                    $testimonio['orden'] = 0;
                }
                $testimonio['fecha_modificacion'] = date('Y-m-d');

                $result = $objTestimonio->updateTestimonio($testimonio, $data['testimonioId']);
                //var_dump($result);
                if ($result) {
                    $this->getMessenger()->success('La actualización se realizó de manera satisfactoria.');
                    $this->_cache->remove('Testimonio_listarTestimonios');
                    $this->_redirect('admin/gestion/testimonios');
                } else {
                    $this->getMessenger()->error('Hubo un  error en el proceso de actualización. Intente Nuevamente.');
                }                
            } else {
                $formRegistroTestimonio->setDefaults($data);
                $this->getMessenger()->error('Complete los datos correctamente.');
            }
        }
    }

    public function desactivarTestimonioAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
            
            $idTestimonio = $this->_getParam('idTestimonio');
            $token = $this->_getParam('tok');
            
            if (crypt($idTestimonio,$token) === $token) {
                $objTestimonio = new Application_Model_Testimonio();
                $objTestimonio->desactivarTestimonio($idTestimonio);
                $this->_cache->remove('Testimonio_listarTestimonios');
            }
                        
        }
        
        
    }
    public function activarTestimonioAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
            
            $idTestimonio = $this->_getParam('idTestimonio');
            $maxTestimonio = $this->_getParam('maxTestimonio');            
            $token = $this->_getParam('tok');
            
            if (crypt($idTestimonio,$token) === $token) {
                $formRegistroTestimonio = new Application_Form_OrdenTestimonio();
                echo $formRegistroTestimonio->getElement('orden')->renderViewHelper();
                $this->_cache->remove('Testimonio_listarTestimonios');
            }

            

        }

        
        
    }

    public function actualizarOrdenTestimonioAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $config = Zend_Registry::get('config');

        $testimonioId = $this->_getParam('idTestimonio');
        $orden = $this->_getParam('orden');
        $objTestimonio = new Application_Model_Testimonio();
        $result = $objTestimonio->actualizarOrdenTestimonio(
            $testimonioId, $orden, $this->config->testimonios->cantidad->testimonio
        );
        $this->_cache->remove('Testimonio_listarTestimonios');
        echo"Se reordenaron los testimonios de manera correcta.";
        
    }

    
    
}