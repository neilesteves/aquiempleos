<?php

/**
 *
 * @author Andy Ecca
 */
class Postulante_AvisosSugeridosController extends App_Controller_Action_Postulante
{

    protected $_notificaciones;

    public function  preDispatch()
    {
        parent::preDispatch();
        $url = $this->_getParam('url');
        if ($url!='' && !isset($this->auth['postulante'])) {
            $this->_redirect('#loginP-'.$url);
        } else {
            if ($url!='') {
                $this->_redirect(base64_decode($url));
            }
        }
    }

    public function init()
    {
        parent::init();
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id'=>'myAccount')
        );
        $this->_postulante = new Application_Model_Postulante();
        if ($this->isAuth &&
            $this->auth['usuario']->rol == Application_Form_Login::ROL_POSTULANTE) {
            $this->idPostulante = $this->auth['postulante']['id'];
        }
        $this->usuario = (isset($this->auth['usuario']))?$this->auth['usuario']:'';
    }

    public function indexAction()
    {

        $page = $this->_getParam('page', 1);

        $params['id_postulante'] = $this->idPostulante;
        $params['page'] = $page;

        $solr   = new Solr_SolrSugerencia();

        $avisos = $solr->getListadoAvisosSugeridos( $params );
        if ($avisos && $avisos['ntotal'] > 0) {

            $totalAvisos = $avisos['ntotal'];
            $totalPaginas = ceil($avisos['ntotal'] / $avisos['count']);

            if (empty($totalAvisos)) {
               Zend_Layout::getMvcInstance()->assign('robots', 'noindex');
            }

            $ntotalAS = $avisos['ntotal'];
            $ncountAS = count($avisos['data']);
            $nsufixAS = $ntotalAS != 1 ? "s" : "";


            if( isset($avisos['params']) && $avisos['params'] == 0) {
               $this->view->tieneAreas = false;
            }else{
               $this->view->tieneAreas = true;
            }

            $this->view->avisos = $avisos['data'];
            $this->view->totalPaginas = $totalPaginas > 1251 ? 1251 : $totalPaginas;

            $this->view->totalAvisosSugeridos = $ntotalAS;
            $this->view->mostrandoAvisos = sprintf("Mostrando %d de %d resultado%s" , $ncountAS , $ntotalAS, $nsufixAS);

        }
        $this->view->paginaActual = $page;

        $this->view->tab = Postulante_AvisosSugeridosController::sugerencias;
        $this->setDataLayoutAvisos();

    }

    public function favoritosAction()
    {
        $page = $this->_getParam('page', 1);

        $params['id_postulante'] = $this->idPostulante;
        $params['page'] = $page;

        $solr   = new Solr_SolrSugerencia();
        $avisos = $solr->getListadoAvisosFavoritos( $params );
        if( $avisos && $avisos['ntotal'] > 0 )
        {
            $totalAvisos = $avisos['ntotal'];
            $totalPaginas = ceil($avisos['ntotal'] / $avisos['count']);

            if( empty($totalAvisos) ) {
                Zend_Layout::getMvcInstance()->assign('robots', 'noindex');
            }

            $ntotalAS = $avisos['ntotal'];
            $ncountAS = count($avisos['data']);
            $nsufixAS = $ntotalAS != 1 ? "s" : "";

            $this->view->avisos = $avisos['data'];
            $this->view->totalPaginas = $totalPaginas > 1251 ? 1251 : $totalPaginas;
            $this->view->paginaActual = $page;
            $this->view->totalAvisosFavorito = $ntotalAS;

            $this->view->mostrandoAvisos = sprintf("Mostrando %d de %d resultado%s" , $ncountAS , $ntotalAS, $nsufixAS);

        }
        $this->view->tab = Postulante_AvisosSugeridosController::favoritos;
        $this->setDataLayoutAvisos();
    }

    public function eliminadosAction()
    {

        $page = $this->_getParam('page', 1);

        $params['id_postulante'] = $this->idPostulante;
        $params['page'] = $page;

        $solr   = new Solr_SolrSugerencia();
        $avisos = $solr->getListadoAvisosEliminados( $params );

        if( $avisos && $avisos['ntotal'] > 0 )
        {

            $totalAvisos = $avisos['ntotal'];
            $totalPaginas = ceil($avisos['ntotal'] / $avisos['count']);

            if( empty($totalAvisos) ) {
                Zend_Layout::getMvcInstance()->assign('robots', 'noindex');
            }

            $ntotalAS = $avisos['ntotal'];
            $ncountAS = count($avisos['data']);
            $nsufixAS = $ntotalAS != 1 ? "s" : "";

            $this->view->avisos = $avisos['data'];
            $this->view->totalPaginas = $totalPaginas > 1251 ? 1251 : $totalPaginas;
            $this->view->paginaActual = $page;
            $this->view->totalAvisosEliminado= $ntotalAS;


            $this->view->mostrandoAvisos = sprintf("Mostrando %d de %d resultado%s" , $ncountAS , $ntotalAS, $nsufixAS);

        }
        $this->view->tab = Postulante_AvisosSugeridosController::eliminados;
        $this->setDataLayoutAvisos();
    }

    public function eliminarAnuncioSugeridoAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $requestValid = $this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest();


        $solr = new Solr_SolrSugerencia();
        $response = array();
        try {
            if( $requestValid && is_numeric( $this->_getParam('id')) )
            {

                $idAnuncio = $this->_getParam('id');

                $data['id_anuncio']=$idAnuncio;
                $data['page']= $this->_getParam('page')+1;
                $data['id_postulante']=$this->idPostulante;

                $nextAviso = $solr->nextAvisoSugerido( $data );
                $anuncio['_id'] = $this->idPostulante.''.$idAnuncio;
                $anuncio['idAnuncioWeb'] = "$idAnuncio";
                $anuncio['idPostulante'] = "$this->idPostulante";
                if($nextAviso) {
                    $response['job'] =$nextAviso;
                    $response['job']['page'] =(count($nextAviso)>0)?$data['page']-1:$data['page']-1;
                     $response['job']['action'] ='sugerencia';
                } else {
                    $response['job']=array();
                }
                // insertar a dynamo
                //$dyn = new Amazon_Dynamo_AvisosSugeridosEliminados();
                $dyn = new Mongo_AvisosSugeridosEliminados();

                $ok = $dyn->guardarDatos($anuncio);
                if ($ok === true) {
                    $response['status']=1;
                    $response['menssage']='El aviso fue eliminado existosamente';
                    $response['token_ajax'] = CSRF_HASH;
                    $response['urlHighlight'] ='/avisos-sugeridos/agregar-favoritos-ajax';
                    $response['urlDelete'] ='/avisos-sugeridos/eliminar-anuncio-sugerido-ajax';
                } else {
                    $response['status'] = 0;
                    $response['menssage']='Por favor, intentelo nuevamente';
                    $response['token_ajax'] = CSRF_HASH;
                }
            } else {
                $response['status'] = 0;
                $response['menssage']='Por favor, intentelo nuevamente';
                $response['token_ajax'] = CSRF_HASH;
            }
        } catch (Exception $exc) {
             $response['menssage']='Por favor vuelva a intentarlo';
             $response['token_ajax'] = CSRF_HASH;
        }
        $this->_response->appendBody(Zend_Json::encode($response));

    }

    public function agregarFavoritosAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idAnuncio = (int)$this->getRequest()->getParam('id');
        $urlaviso =       $this->getRequest()->getParam('urlaviso');

        if ( !$idAnuncio < 0 ){
            exit('not access allowed');
        }
        try {

            //$dynfavorito = new Amazon_Dynamo_Favoritos();
            $dynfavorito = new Mongo_Favoritos();

            $data['id_anuncio']=$idAnuncio;
            $data['page']= $this->_getParam('page')+1;
            $page=$this->_getParam('page');
            $data['id_postulante']=$this->idPostulante;

            $favorito = array();
            $favorito['id_aviso'] = "$idAnuncio";
            $favorito['idAviso'] = "$urlaviso";
            $favorito['idPostulante'] = "$this->idPostulante";

            $solr = new Solr_SolrSugerencia();
            $favs = $solr->getAnunciosfavoritos( $this->idPostulante );
            $esFav = array_search( $idAnuncio, $favs) !== FALSE;

            if($esFav) {
                $anuncio['_id'] = $this->idPostulante.''.$idAnuncio;
                $anuncio['idAnuncioWeb'] = "$idAnuncio";
                $anuncio['idPostulante'] = "$this->idPostulante";
                $anuncio['id_aviso'] = "$idAnuncio";
                $el = $dynfavorito->borrarDatosAviso($anuncio);
                if( $el ) {

                    $respuesta['status']=1;
                    $respuesta['menssage']='El aviso se elimino de los favoritos exitósamente';
                    $respuesta['urlHighlight'] ='/avisos-sugeridos/agregar-favoritos-ajax';
                    $respuesta['urlDelete'] ='/avisos-sugeridos/eliminar-anuncio-sugerido-ajax';

                }else {
                    $respuesta['menssage']='Por favor vuelva a intentarlo';
                    $respuesta['status']=0;
                }

            }else {

                $solr = new Solr_SolrSugerencia();
                $DataAnuncioNext = $solr->nextAvisoSugerido($data);
                if( !isset($idAnuncio) ) {
                    exit('no existe el anuncio solicitado');
                }
                if($DataAnuncioNext) {
                    $respuesta['job'] =$DataAnuncioNext;
                    $respuesta['job']['page'] =$data['page']-1;
                    $respuesta['job']['action'] ='sugerencia';
                } else {
                    $respuesta['job']=array();
                }

                $ok = $dynfavorito->guardarDatos($favorito);
                $respuesta['token_ajax'] = CSRF_HASH;
                if ($ok === false) {
                    $respuesta['menssage']='Por favor vuelva a intentarlo';
                    $respuesta['status']=0;

                } else {
                    $respuesta['status']=1;
                    $respuesta['menssage']='El aviso se guardo a favoritos exitósamente';
                    $respuesta['urlHighlight'] ='/avisos-sugeridos/agregar-favoritos-ajax';
                    $respuesta['urlDelete'] ='/avisos-sugeridos/eliminar-anuncio-sugerido-ajax';
                }
            }


        } catch (Exception $exc) {
             $respuesta['menssage']='Por favor vuelva a intentarlo';
             $respuesta['status']=0;
             $respuesta['token_ajax'] = CSRF_HASH;
        }

        $respuesta['token_ajax'] = CSRF_HASH;

        $this->_response->appendBody(Zend_Json::encode($respuesta));


    }

    public function agregarFavoritosEliminadoAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $requestValid = $this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest();
        //$eliminados = new Amazon_Dynamo_AvisosSugeridosEliminados();
        $eliminados = new Mongo_AvisosSugeridosEliminados();
        $urlaviso = $this->getRequest()->getParam('urlaviso');
        $solr = new Solr_SolrSugerencia();
        $response = array();

        try {
            if( $requestValid && is_numeric( $this->_getParam('id')) )
            {

                $idAnuncio = (int)$this->getRequest()->getParam('id');
                $data['id_anuncio']=$idAnuncio;
                $data['page']= $this->_getParam('page')+1;
                $page=$this->_getParam('page');
                $data['id_postulante']=$this->idPostulante;
                $favorito['id_aviso'] = "$idAnuncio";
                $favorito['idAviso'] = "$urlaviso";
                $favorito['idPostulante'] = "$this->idPostulante";
                $nextAvisoEliminado = $solr->nextAvisoEliminado( $data );
                if($nextAvisoEliminado) {
                    $response['job'] =$nextAvisoEliminado;
                    $response['job']['page'] =$data['page']-1;
                    $response['job']['action'] ='eliminados';
                } else {
                    $response['job']=array();
                }
                $response['status']=1;
                $response['menssage']='El aviso fue eliminado exitósamente';
                $response['token_ajax'] =CSRF_HASH;
                $response['urlHighlight']='/avisos-sugeridos/agregar-favoritos-eliminado-ajax';
                // insertar a dynamo
                //$dynfavorito = new Amazon_Dynamo_Favoritos();
                $dynfavorito = new Mongo_Favoritos();
                $eliminados->borrarDatosAviso($favorito);
                $dynfavorito->guardarDatos($favorito);

            }else{
                $respuesta['status'] = 0;
                $respuesta['menssage']='Porfavor, intentelo nuevamente';
                $respuesta['token_ajax'] = CSRF_HASH;
            }
        } catch (Exception $exc) {
             $respuesta['menssage']='Por favor vuelva a intentarlo';
             $respuesta['status']=0;
        }
        $this->_response->appendBody(Zend_Json::encode($response));

    }
    public function agregarEliminadoFavoritosAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $requestValid = $this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest();
        //$dynfavorito = new Amazon_Dynamo_Favoritos();
        $dynfavorito = new Mongo_Favoritos();

        $solr = new Solr_SolrSugerencia();
        $response = array();
        try {
            if( $requestValid && is_numeric( $this->_getParam('id')) )
            {

                $idAnuncio = $this->_getParam('id');

                $data['id_anuncio']=$idAnuncio;
                $data['page']= $this->_getParam('page')+1;
                $page=$this->_getParam('page');
                $data['idPostulante']=$this->idPostulante;
                $data['id_postulante']=$this->idPostulante;


                $anuncio['_id'] = $this->idPostulante.''.$idAnuncio;
                $anuncio['idAnuncioWeb'] = "$idAnuncio";
                $anuncio['idPostulante'] = "$this->idPostulante";

                $favorito['_id'] = $this->idPostulante.''.$idAnuncio;
                $favorito['idAnuncioWeb'] = "$idAnuncio";
                $favorito['idPostulante'] = "$this->idPostulante";
                $anuncio['id_aviso'] = "$idAnuncio";

                $nextEliminadoFavorito = $solr->nextFavoritosEliminado( $data );
                if($nextEliminadoFavorito) {
                    $response['job'] =$nextEliminadoFavorito;
                    $response['job']['page'] =$data['page']-1;
                    $response['job']['action'] = 'favoritos';
                } else {
                    $response['job']=array();
                }
                $response['status']=1;
                $response['menssage']='El aviso se guardo a favoritos exitósamente';
                $response['token_ajax'] = CSRF_HASH;
                $response['urlDelete'] = '/avisos-sugeridos/agregar-eliminado-favoritos-ajax';
                // insertar a dynamo
                //$eliminados = new Amazon_Dynamo_AvisosSugeridosEliminados();
                $eliminados = new Mongo_AvisosSugeridosEliminados();
                $dynfavorito->borrarDatosAviso( $anuncio);
                $eliminados->guardarDatos($anuncio);


            }else{
                $response['status'] = 0;
                $response['menssage']='Por favor, intentelo nuevamente';
                $response['token_ajax'] = CSRF_HASH;
                 $response['action']='eliminados';
            }
        } catch (Exception $exc) {
             $response['menssage']='Por favor vuelva a intentarlo';
             $response['status']=0;
             $response['token_ajax'] = CSRF_HASH;
        }
        $this->_response->appendBody(Zend_Json::encode($response));

    }

    private function setDataLayoutAvisos()
    {
        $arrayPostulante = $this->_postulante->getPostulante($this->idPostulante);

        // Porcentaje
        $porcCV = new App_Controller_Action_Helper_PorcentajeCV();
        $porcentajes = $porcCV->getPorcentajes($arrayPostulante);
        $this->view->porcentaje = $porcentajes['total_completado'];
        $this->view->incompletos = $porcentajes['total_incompleto'];

        // preparar datos
        $this->view->imgPhoto = $arrayPostulante['path_foto'];
        $this->view->nombres = $arrayPostulante['nombres'];
        $this->view->apellidos = $arrayPostulante['apellido_paterno'] . ' ' . $arrayPostulante['apellido_materno'];
        $this->view->var = $this->config->dashboard;
        $this->view->slug = $arrayPostulante['slug'];

        // imagen defecto
        $rutaLogoDefecto = $this->config->defaultLogoEmpresa->fileName;
        $verLogoDefecto = (bool) $this->config->defaultLogoEmpresa->enabled;

        $this->view->logoDefecto = $rutaLogoDefecto;
        $this->view->verLogoDefecto = $verLogoDefecto;
        $this->view->recortaraviso = $this->config->busqueda->recortaraviso;
    }

    public function testAction(){

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $favorito['id_aviso'] = "1851874";
        $favorito['idPostulante'] = 592680;
        $dynfavorito = new Amazon_Dynamo_AvisosSugeridosEliminados();

// var_dump($favorito);exit;
        $rs = $dynfavorito->borrarDatosAviso($favorito);

        var_dump($rs);exit;
        exit;
    }


    public function listOfCurrentlyAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $Tipo = $this->getRequest()->getParam('type');
        $params = array(
            'id_postulante' => $this->idPostulante,
            'page' => 1,
            'numItems' => Zend_Registry::get('config')
                ->paginadorSugerencias
                ->numItems,
            'tipo' => $Tipo,
        );

        $respuesta = array();
        $requestValid = ($this->getRequest()->isXmlHttpRequest()
                && $this->getRequest()->isPost());

        if(!$requestValid) {
            exit;
        }

        $tok = $this->_getParam('token_ajax');
        try {
            if ($this->_hash->isValid($tok)) {
                $solr   = new Solr_SolrSugerencia();
                $respuesta['items_list'] = array();
                $respuesta['total_avisos'] = 0;
                $Sugerencias = array(
                    'ntotal' => 0
                );

                switch ($Tipo) {
                    case 'sugerencia':
                        $Sugerencias = $solr->getListadoAvisosSugeridos( $params );
                        $Sugerencias['data']=isset($Sugerencias['data'])?$Sugerencias['data']:array();
                        $respuesta['items_list'] =  $this->dataItems($Sugerencias['data']);
                        $vista = '/index';
                        $urleliminar = "/avisos-sugeridos/eliminar-anuncio-sugerido-ajax";
                        $urlfavoritos ="/avisos-sugeridos/agregar-favoritos-ajax";
                        break;
                    case 'eliminados':
                        $Sugerencias = $solr->getListadoAvisosEliminados( $params );
                        $Sugerencias['data']=isset($Sugerencias['data'])?$Sugerencias['data']:array();
                        $respuesta['items_list'] =  $this->dataItems($Sugerencias['data']);
                        $vista = '/eliminados';
                        $urleliminar = "/avisos-sugeridos/eliminar-anuncio-sugerido-ajax";
                        $urlfavoritos = "/avisos-sugeridos/agregar-favoritos-eliminado-ajax";
                        break;
                    case 'favoritos':
                        $Sugerencias = $solr->getListadoAvisosFavoritos( $params );
                        $Sugerencias['data']=isset($Sugerencias['data'])?$Sugerencias['data']:array();
                        $respuesta['items_list'] =  $this->dataItems($Sugerencias['data']);
                        $vista = '/favoritos';
                        $urleliminar = "/avisos-sugeridos/agregar-eliminado-favoritos-ajax";
                        $urlfavoritos = '';
                    default:
                         break;
                }

                if ($Sugerencias['ntotal'] > 0 ) {
                    $respuesta['total_avisos'] = $Sugerencias['ntotal'];
                    $messages='lista de avisos de '.$Tipo;
                } else {
                    $messages='No hay avisos para mostrar.';
                }

                $respuesta['urlHighlight'] = $urlfavoritos;
                $respuesta['urlDelete'] = $urleliminar;
                $respuesta['vertodos'] = SITE_URL.'/avisos-sugeridos'.$vista;
                $respuesta['status'] = 1;
                $respuesta['token_ajax'] = CSRF_HASH;
                $respuesta['action'] = $Tipo;
                $respuesta['messages'] = $messages;
            } else {
                $respuesta['status'] = 0;
                $respuesta['token_ajax'] = CSRF_HASH;
                $respuesta['messages'] = 'Por favor vuelva a intentarlo.';
            }

        } catch (Exception $exc) {
            $respuesta['status'] = 0;
            $respuesta['token_ajax'] = CSRF_HASH;
            $respuesta['messages'] = 'Por favor vuelva a intentarlo.';
        }
        $this->_response->appendBody(Zend_Json::encode($respuesta));
    }


    private function dataItems($Sugerencias) {
        $data=array();
         foreach ($Sugerencias as $key => $value ) {
             $value['logoanuncio']=trim($value['logoanuncio']);
            if(isset($value['logoanuncio']) && !empty($value['logoanuncio'])){
              $logo=$this->view->E()->getlastcommitElementLogos($value['logoanuncio']);
            } else {
              $logo=$this->view->S('/images/icon-empresa-blank.png');
            }

            $data[$key]['image'] = $logo;
            $data[$key]['company'] =$value['empresa_rs'];
            $data[$key]['puesto'] =(strlen ($value['puesto'])>=40)?mb_substr($value['puesto'], 0,40, 'utf-8') . " ...":$value['puesto'];
            $data[$key]['title'] =$value['puesto'];
            $data[$key]['content'] =mb_substr($value['description'], 0,80, 'utf-8') . " ...";
            $data[$key]['ubigeo'] =$value['ubicacion'];
            $data[$key]['id'] =$value['id'];
            $data[$key]['url'] =$value['url'];
            $data[$key]['ishighlight'] =$value['destacado'];
            $data[$key]['date'] =$value['dias_fp'];
            $data[$key]['urlAviso'] =str_replace( '/ofertas-de-trabajo/', '',$value['url']);
            $data[$key]['page']=1;
         }

         return $data;
    }


}

