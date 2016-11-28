<?php

class Application_Model_Partials {
    
    public $_information=0;

    public static function getAvisosSugeridos() {
        $auth = Zend_Auth::getInstance()->getStorage()->read();
        $idPostulante = $auth['postulante']['id'];
        
        $solr = new Solr_SolrSugerencia();
        $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
        $view->verLogoDefecto='icon-empresa-blank.png';
        $view->avisosSugerencias = $solr->getListadoAvisosSugeridos(
                array('id_postulante'=>$idPostulante));
        $view->paginaActual=1;
        echo $view->render('_partials/_avisos_sugeridos.phtml');
    }

    
    public static function getPerfilPorcentaje() 
    {
        $auth = Zend_Auth::getInstance()->getStorage()->read();        
        $idPostulante = $auth['postulante']['id'];
        $porcCV = new App_Controller_Action_Helper_PorcentajeCV();
        $postulante = new Application_Model_Postulante();
        //$arrayPostulante = $postulante->getPostulante($idPostulante);
        $arrayPostulante = $postulante->getPostulanteForPorcentaje($idPostulante);
        $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
        $view->dataperfil = $porcCV->getPorcentajes($arrayPostulante,true); 
        $layout=  Zend_Layout::getMvcInstance();
        Zend_Layout::getMvcInstance()->assign(
                        'information',$view->dataperfil['sugerencias']['total']
        );
        $view->modalInfoUpdatePerfil= $layout->modalInfoUpdatePerfil;
       
        $view->destacado = isset($auth['postulante']['destacado']) ? $auth['postulante']['destacado'] : 0;
        
        $view->destacado = isset($auth['postulante']['destacado']) ? $auth['postulante']['destacado'] : 0;
        
        if (MODULE == 'postulante' && CONTROLLER == 'home' && ACTION == 'index') {
            echo $view->render('mi-cuenta/_carousel-perfil-home.phtml');
        } else {
            echo $view->render('mi-cuenta/_carousel-perfil.phtml');
        }
    }
    
    public static function getMenuPostulante() 
    {
        $auth = Zend_Auth::getInstance()->getStorage()->read();
        $arrayPostulante = array();
        $updateCV=0;
        $information = null;
        
        if (isset($auth['postulante'])) {
            $idPostulante = $auth['postulante']['id'];
            //$porcCV = new App_Controller_Action_Helper_PorcentajeCV();
              $postulante = new Application_Model_Postulante();
            /* 
             * Solo se usa el idPostulante no es necesario traer muchos datos
             * Ademas de que el ID es unico y no cambia, por el momento usamos 
             * el ID:
             */
//            $postulante = new Application_Model_Postulante();
//            $arrayPostulante = $postulante->getPostulante($idPostulante);
            
            $arrayPostulante = array(
                'idpostulante' => $idPostulante
            );   
            $updateCV = $postulante->hasDataForApplyJobSession($auth['postulante']);
            $information = !($updateCV)?true:false;
            
            if(CONTROLLER !='aviso' && ACTION != 'ver'){
                   
              Zend_Layout::getMvcInstance()->assign(
                    'redirect', SITE_URL.'/perfil-destacado'

                ); 
                 Zend_Layout::getMvcInstance()->assign(
                    'modalInfoUpdatePerfil', $information
                );
                Zend_Layout::getMvcInstance()->assign(
                 'newCompleteRecord', new Application_Form_RegistroComplePostulante(isset($idPostulante)?$idPostulante:null,$auth['postulante'])

                );
            }                           
            $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
            $view->modalInfoUpdatePerfil= $information;
            $view->dataperfil = $arrayPostulante; 
            $view->auht = isset($auth)?$auth:0; 

            $view->menu_post_sel=CONTROLLER.'/'.ACTION;
            echo    $view->render('mi-cuenta/_new_sec-menu.phtml');
        }              
       
    }
    
     public static function getMessagesAyuda() 
    {
        if ( (CONTROLLER == "mi-cuenta" && MODULE == "postulante") ){         
        $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');   
        echo    $view->render('_partials/_menssage_help.phtml');
        }
    }
    
    
    
    public static function getIncompleteDashboard() 
    {
        $auth = Zend_Auth::getInstance()->getStorage()->read();        
        $idPostulante = $auth['postulante']['id'];
        $porcCV = new App_Controller_Action_Helper_PorcentajeCV();
        $postulante = new Application_Model_Postulante();
        $arrayPostulante = $postulante->getPostulante($idPostulante);
        $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
        $view->dataperfil = $porcCV->getPorcentajes($arrayPostulante,true);  
//        var_dump($view->dataperfil["total_incompleto"]);exit;
        
        
        echo    $view->render('_partials/_incomplete_dashboard.phtml');
    }
}

