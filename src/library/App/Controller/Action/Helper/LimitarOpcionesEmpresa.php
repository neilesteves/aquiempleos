<?php

class App_Controller_Action_Helper_LimitarOpcionesEmpresa extends Zend_Controller_Action_Helper_Abstract {

    protected $_module;
    protected $_empresa;
    private $_estadoMembresia;
    private $_restricciones;
    private $_flashMessenger;
    private $_tipoAviso;
    private $_action;
    private $_controller;
    protected $_config;
    protected $_model;
    protected $_cache;

    public function __construct() {
        $cparts = explode('_', __CLASS__);
        $this->_model = $cparts[2];
        $this->_config = Zend_Registry::get('config');
        $this->_cache = Zend_Registry::get('cache');
    }

    public function init() {
        parent::init();
        $this->_module = $this->getRequest()->getModuleName();
        $this->_flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessengerCustom');
        $this->_restricciones = $this->obtenerRestricciones();
    }

    private function obtenerRestricciones() {
        $cacheEt = $this->_config->cache->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }

        $result = require(APPLICATION_PATH . '/configs/restricciones.php');
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function preDispatch() {
        parent::preDispatch();

        if ($this->_module == 'empresa') {
            $this->_empresa = $this->_getEmpresa();
            if (!empty($this->_empresa)) {
                $this->_getEstadoMembresia();
                //           if ( $this->getRequest()->getActionName() == 'exportar-proceso') {
                ////            if ($this->getRequest()->getActionName() == 'ver-proceso' ||
                ////                   $this->getRequest()->getActionName() == 'exportar-proceso') {
                $anuncioWeb = new Application_Model_AnuncioWeb();
                $this->_tipoAviso = $anuncioWeb
                        ->getTipoAnuncioById($this->getRequest()->getParam('id', 0));

                $this->_controller = $this->getRequest()->getControllerName();
                $this->_action = $this->getRequest()->getActionName();
                if (!empty($this->_restricciones[$this->_controller . '.' . $this->_action . '.' . $this->_tipoAviso])) {
                    $this->_limitarOpciones();
                }
            }
        }
    }

    protected function _limitarOpciones() {
       /* if (!$this->_estadoMembresia) {
            if ($this->_restricciones[$this->_controller . '.' . $this->_action . '.' . $this->_tipoAviso]['activo'] === 0) {
                if ($this->getRequest()->isXmlHttpRequest()) {
                    //$this->getActionController()->view->assign('flashMessages', $this->_flashMessenger);
                    echo "No tiene permisos de acceso para esta solicitud.";

                    $this->getResponse()->sendResponse();
                    die();
                } else {
                    $this->getMessenger()->error(
                            "No tiene permisos de acceso para esta solicitud."
                    );
                    $this->getActionController()->view->assign('flashMessages', $this->_flashMessenger);
                    $this->getResponse()->setRedirect('/empresa/mi-cuenta');
                    $this->getResponse()->sendResponse();
                }
            }
        }*/
    }

    private function _getEmpresa() {
        $datos = Zend_Auth::getInstance()->getStorage()->read();
        return isset($datos['empresa']) ? $datos['empresa'] : '';
    }

    /**
     * Función para obtener el estado de una membresía de una empresa
     */
    private function _getEstadoMembresia() {
        if (isset($this->_empresa['membresia_info']['membresia']['estado'])) {
            $estado = isset($this->_empresa['membresia_info']['membresia']['estado']);
            if ($estado == Application_Model_Membresia::TIPO_ESTADO_VIGENTE)
                $this->_estadoMembresia = true;
            else {
                $this->_estadoMembresia = false;
            }
        } else {
            $this->_estadoMembresia = false;
        }
    }

    private function _getConfig() {
        return Zend_Registry::get('config');
    }

    /**
     * Retorna la instancia personalizada de FlashMessenger
     * Forma de uso:
     * $this->getMessenger()->info('Mensaje de información');
     * $this->getMessenger()->success('Mensaje de información');
     * $this->getMessenger()->error('Mensaje de información');
     *
     * @return App_Controller_Action_Helper_FlashMessengerCustom
     */
    public function getMessenger() {
        return $this->_flashMessenger;
    }

}
