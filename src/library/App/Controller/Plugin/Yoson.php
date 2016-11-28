<?php

class App_Controller_Plugin_Yoson extends Zend_Controller_Plugin_Abstract
{

    protected $config;
    protected $lastCommit;
    protected $view;

    public function __construct()
    {
        $this->config = Zend_Registry::get('config');
        $this->lastCommit = new App_View_Helper_S();
        $this->view = new App_View_Helper_Util();
    }

    public function postDispatch( Zend_Controller_Request_Abstract $request )
    {
        $yOSON = array();
        $view = Zend_Layout::getMvcInstance()->getView();
        $helper = new App_Controller_Action_Helper_Util();
        if(!$this->view->Util()->getPostulanteMainValid()) {
            $statHost = $this->config->app->mediaUrl . '/';
            $modulo = 'modulo';
            if(defined('MODULE') && defined('CONTROLLER') && defined('ACTION')) {
                if($this->view->getPostulanteValid()) {
                    $statHost = $this->config->app->mediaUrl . '/main/';
                    $modulo = 'module';
                }
                if($this->view->getEmpresaValid()) {
                    $statHost = $this->config->app->mediaUrl . '/main/';
                    $modulo = 'module';
                }
            }


            $yOSON[$modulo] = $request->getModuleName();
            $yOSON['controller'] = $request->getControllerName();
            $yOSON['action'] = $request->getActionName();
            $yOSON['baseHost'] = $this->config->app->siteUrl;
            $yOSON['statHost'] = $statHost;
            $yOSON['eHost'] = $this->config->app->elementsUrl;
            $yOSON['statVers'] = $this->lastCommit->getLastCommit();
            $yOSON['token'] = defined('CSRF_HASH') ? CSRF_HASH : '';
            $yOSON['AppCore'] = array();
            $yOSON['AppSandbox'] = array();
            $yOSON['AppSchema'] = array('module' => array(), 'requires' => array());
            $yOSON['Eplanning'] = array(); 
            $yOSON['tmp'] = array();
            $yOSON['tmp']['appIdFacebook'] = (isset($this->apis->facebook->appid) ? $this->apis->facebook->appid : '');
//        defined('MODULE');
//        defined('CONTROLLER');
//        if( MODULE == 'empresa' &&  CONTROLLER == 'look-and-feel') {
//          $yOSON['tmp']['profileEmpresa'] = $this->config->s3->app->profileEmpresa->toArray() ;
//        }
            if(defined('MODULE') && defined('CONTROLLER') && defined('ACTION')) {
                if(MODULE == 'postulante' && CONTROLLER == 'registro' && ACTION == 'paso3') {
                    $yOSON['salaryRange'] = $helper->salarios();
                    $yOSON['maxInterval'] = $this->config->salarios->maxInterval;
                    $yOSON['minInterval'] = $this->config->salarios->minInterval;
                    $yOSON['salaryTooHigh'] = $this->config->salarios->salaryTooHigh;
                    $yOSON['maxItems']['maxTags'] = $this->config->salarios->maxTags;
                    $yOSON['maxItems']['maxLocationsItems'] = isset($this->config->registropaso3->maxlocations) ? $this->config->registropaso3->maxlocations : 8;
                    $yOSON['maxLocationsItems'] = isset($this->config->registropaso3->maxlocations) ? $this->config->registropaso3->maxlocations : 8;
                }
                if(MODULE == 'postulante' && CONTROLLER == 'home' && ACTION == 'index') {
                    $yOSON['isAutocompleteActive'] = isset($this->config->home->autocomplete) ? $this->config->home->autocomplete : 0;
                }
                if(MODULE == 'postulante' && CONTROLLER == 'buscar' && ACTION == 'busqueda-avanzada') {
                    $yOSON['maxItems']['maxCompanyTags'] = isset($this->config->autocomplete->busquedaAvanzada) ? $this->config->autocomplete->busquedaAvanzada : 5;
                    $yOSON['maxItems']['maxUbigeoItems'] = isset($this->config->autocomplete->busquedaAvanzada) ? $this->config->autocomplete->busquedaAvanzada : 5;
                }
            }
            $view->HeadScript()->prependScript('var yOSON= ' . json_encode($yOSON));
        } else {
            $statHost = $this->config->app->mediaUrl . '/';
            $yOSON[MODULE] = $request->getModuleName();
            $yOSON['controller'] = $request->getControllerName();
            $yOSON['action'] = $request->getActionName();
            $yOSON['baseHost'] = $this->config->app->siteUrl;
            $yOSON['statHost'] = $statHost;
            $yOSON['mediaUrl'] = $statHost . '/eb/';
            $yOSON['areasHome'] = $statHost . 'eb/svg/icons-areas/';
            $yOSON['eHost'] = $this->config->app->elementsUrl;
            $yOSON['statVers'] = $this->lastCommit->getLastCommit();
            $yOSON['token'] = defined('CSRF_HASH') ? CSRF_HASH : '';
            $view->HeadScript()->prependScript('var APP= ' . json_encode($yOSON));
        }


        parent::postDispatch($request);
    }

    public function getEplanningUser()
    {
        $vars = array();
        $params = false;
        $sec = false;

        $vars['iIF'] = 1;
        $vars['sV'] = '://ads.us.e-planning.net/';
        $vars['vV'] = 4;
        $vars['sI'] = '8af1';
        $vars['custom'] = false;

        if(Zend_Layout::getMvcInstance()->paramsUser) {
            $params = Zend_Layout::getMvcInstance()->paramsUser;
            if(isset($params['seccion'])) {
                $sec = $params['seccion'];
                unset($params['seccion']);
            }
        }

        // Postulante
        if(MODULE == 'postulante') {
            switch(CONTROLLER) {
                case 'home' :
                    switch(ACTION) {
                        case 'index' :
                            $vars['sec'] = 'Portada';
                            $vars['eIs'] = array("Right1", "Middle", "Top", "Expandible", "Right2", "MegaBanner", "Bottom");
                            $vars['kVs'] = $this->_def($params, "");
                            break;

                        default:
                            $vars['sec'] = $this->_def($sec, "DetalleAviso");
                            $vars['eIs'] = array("Top", "Right1", "Bottom");
                            $vars['kVs'] = $this->_def($params, "");
                            break;
                    }
                    break;

                case 'aviso' :
                    $vars['sec'] = 'DetalleAviso';
                    $vars['eIs'] = array("Top", "Right1", "Right2");
                    $vars['kVs'] = $this->_def($params, "");
                    break;

                case 'buscar' :
                    $vars['sec'] = 'ResultadoBusqueda';
                    $vars['eIs'] = array("Top", "Right1", "Middle", "Bottom");
                    $vars['kVs'] = $this->_def($params, "");
                    break;

                default :
                    $vars['sec'] = $this->_def($sec, "DetalleAviso");
                    $vars['eIs'] = array("Top", "Right1", "Bottom");
                    $vars['kVs'] = $this->_def($params, "");
                    break;
            }
        }
        // Empresa
        else if(MODULE == 'empresa') {

            switch(CONTROLLER) {

                case 'home' :
                    switch(ACTION) {
                        case 'index' :
                        case 'seleccion' :
                            $vars['sec'] = 'Portada';
                            $vars['eIs'] = array("Right1", "Middle", "Top", "Expandible", "Right2", "MegaBanner", "Bottom");
                            $vars['kVs'] = $this->_def($params, "");
                            break;

                        default:
                            $vars['sec'] = $this->_def($sec, "DetalleAviso");
                            $vars['eIs'] = array("Top", "Right1", "Bottom");
                            $vars['kVs'] = $this->_def($params, "");
                            break;
                    }
                    break;

                default :
                    $vars['sec'] = $this->_def($sec, "DetalleAviso");
                    $vars['eIs'] = array("Top", "Right1", "Bottom");
                    $vars['kVs'] = $this->_def($params, "");
                    break;
            }
        }
        return $vars;
    }

    private function _def( $var, $var2 )
    {
        return isset($var) && $var ? $var : $var2;
    }

}
