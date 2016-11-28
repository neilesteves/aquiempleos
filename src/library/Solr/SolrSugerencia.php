<?php

class Solr_SolrSugerencia extends Solr_SolrClient
{

    protected $core = 'avisos';
    private $view = null;
    private $viewE = null;
    private $_log;

    public function __construct()
    {
        $this->valServer = false;
        $this->helperSever = new App_Controller_Action_Helper_Solr();
        $mConfig = Zend_Registry::get('config')->solrAviso;
        $this->_log = Zend_Registry::get('log');
        if(!$this->helperSever->valServidorSor($mConfig)) {
            $this->valServer = true;
        }
        $this->view = new App_View_Helper_S();
        $this->viewE = new App_View_Helper_E();
        $this->solrCli = new Solarium\Client($mConfig);
        $this->solrCli->getPlugin('postbigrequest');
        $this->select = $this->solrCli->createSelect();
    }

    public function getListadoAvisosSugeridos( $params = array() )
    {

        // fix para traer solo una cantidad especifica de elementos
        if(isset($params['numItems'])) {
            $rows = (int) $params['numItems'];
        } else {
            $rows = Zend_Registry::get('config')->paginadorSugerencias->numItems;
        }

        $start = !isset($params['page']) ? 0 : ($params['page'] - 1) * $rows;
        $idPostulante = $params['id_postulante'];

        $anunciosELiminados = array();
        $AnunciosFavoritos = array();

        try {
            //$dynParamSugeridos = new Amazon_Dynamo_ParamSugeridosPostulante();
            $dynParamSugeridos = new Mongo_ParamSugeridosPostulante();
            $datosListado = $dynParamSugeridos->getDatos($idPostulante);

            $anunciosELiminados = $this->getAnunciosEliminados($idPostulante);
            $AnunciosFavoritos = $this->getAnunciosfavoritos($idPostulante);

            if(count($datosListado) == 0) {
                $result['ntotal'] = 0;
                $result['data'] = array();
                $result['error'] = true;
                $result['count'] = 0;
                $result['filter']['area'] = array();
                $result['params'] = 0;
                return $result;
            }
            $anunciosELiminados = !empty($anunciosELiminados) ? $anunciosELiminados : array();
            $AnunciosFavoritos = !empty($AnunciosFavoritos) ? $AnunciosFavoritos : array();
        } catch(Exception $ex) {
            //   $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
            $anunciosELiminados = array();
            $AnunciosFavoritos = array();
            $datosListado = array(
                'area_nivel' => array(
                    'S' => ''
                ),
                'txtUbicacion' => array(
                    'S' => ''
                )
            );
        }

        // * Areas Y Puesto
        //var_dump($datosListado);exit;
        if(!empty($datosListado['area_nivel'])) {
            // if ( !empty($datosListado['area_nivel']) ) {
            $areasPuestos = $datosListado['area_nivel'];
            //$areasPuestos = $datosListado['area_nivel'];
            // var_dump($areasPuestos);exit;
            $areasPuestos = $areasPuestos;

            if(!($areasPuestos)) {
                $result['ntotal'] = 0;
                $result['data'] = array();
                $result['error'] = true;
                $result['count'] = 0;
                $result['filter']['area'] = array();
                $result['params'] = 0;
                return $result;
            }
         
            foreach ($areasPuestos as $item) {
                if(count($item) >= 1) {
                    if(!isset($item['area']) && !isset($item['nivel'])) {
                        foreach ($item as $obj) {
                            $params['areas'][] = $obj['area']['name'];
                            $params['nivel'][] = $obj['nivel']['name'];
                        }
                    } else {
                        $params['areas'][] = $item['area']['name'];
                        $params['nivel'][] = $item['nivel']['name'];
                    }
                } else {
                    if(isset($item['area']) && isset($item['nivel'])) {
                        $params['areas'][] = $item['area']['name'];
                        $params['nivel'][] = $item['nivel']['name'];
                    }
                }
            }
        }

        $anunciosPostulado = array();
        $postulacion = new Application_Model_Postulacion();
        $res = $postulacion->getIdAvisosPostulaciones($params['id_postulante']);



        foreach ($res as $item) {
            $anunciosPostulado[] = $item['id_anuncio_web'];
        }
        $avisosExcluidos = array_unique(array_merge($anunciosPostulado, $anunciosELiminados, $AnunciosFavoritos));

        try {

            $select = $this->select;
            $select->setStart($start)->setRows($rows);
            $select->setQueryDefaultOperator('OR');
            $edismax = $select->getEDisMax();
            $edismax->setQueryFields('destacado^100.0');

            foreach ($avisosExcluidos as $id) {
                $select->createFilterQuery('excluir' . $id)->setQuery("id_anuncio_web:(*:* NOT $id)");
            }

            if(isset($params['excluir_empresa'])) {
                $select->createFilterQuery('excluir_empresa_' . $params['excluir_empresa'])->setQuery("id_empresa:(*:* NOT {$params['excluir_empresa']})");
            }

            if(isset($params['areas'])) {
                $areas = implode(' OR ', $params['areas']);
                $select->createFilterQuery('areas')->setQuery("areaslug:($areas)");
            }
            if(isset($params['nivel'])) {
                $puestos = implode(' OR ', array_unique($params['nivel']));
                $select->createFilterQuery('nivel')->setQuery("nivelslug:($puestos)");
            }

            $select->addSort('destacado', 'desc');
            $select->addSort('fecha_publicacion', 'desc');
            $resultset = $this->solrCli->select($select);
            $result['ntotal'] = (int) $resultset->getNumFound();
            foreach ($resultset as $doc) {
                $result['data'][] = $this->formatoItemSolr($doc);
            }
        } catch(Solarium\Exception\HttpException $exc) {
            $this->_log->log($exc->getMessage() . '. ' . $exc->getTraceAsString(), Zend_Log::CRIT);

            $result['ntotal'] = 0;
            $result['data'] = array();
            $result['error'] = true;
        } catch(Exception $ex) {
            // $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
            $result['ntotal'] = 0;
            $result['data'] = array();
            $result['error'] = true;
        }

        $result['count'] = $rows;
        return $result;
    }

    public function listItems( $params )
    {
        $respuesta = array();
        switch($params['tipo']) {
            case 'sugerencia':
                $Sugerencias = $this->getListadoAvisosSugeridos($params);
                foreach ($Sugerencias['data'] as $key => $value) {
                    $respuesta[$key]['logoanuncio'] = trim($value['logoanuncio']);
                    $respuesta[$key]['image'] = $value['logoanuncio'];
                    $respuesta[$key]['company'] = $value['empresa_rs'];
                    $respuesta[$key]['title'] = $value['puesto'];
                    $respuesta[$key]['content'] = $value['description'];
                    $respuesta[$key]['ubigeo'] = $value['ubicacion'];
                    $respuesta[$key]['id'] = $value['id'];
                    $respuesta[$key]['url'] = $value['url'];
                    $respuesta[$key]['ishighlight'] = $value['destacado'];
                    $respuesta[$key]['date'] = $value['dias_fp'];
                }
                break;
            case 'eliminados':
                $Sugerencias = $this->getListadoAvisosEliminados($params);
                foreach ($Sugerencias['data'] as $key => $value) {
                    $respuesta[$key]['image'] = trim($value['logoanuncio']);
                    $respuesta[$key]['company'] = $value['empresa_rs'];
                    $respuesta[$key]['title'] = $value['puesto'];
                    $respuesta[$key]['content'] = $value['description'];
                    $respuesta[$key]['ubigeo'] = $value['ubicacion'];
                    $respuesta[$key]['id'] = $value['id'];
                    $respuesta[$key]['url'] = $value['url'];
                    $respuesta[$key]['ishighlight'] = $value['destacado'];
                    $respuesta[$key]['date'] = $value['dias_fp'];
                }
                break;
            case 'favoritos':
                $Sugerencias = $this->getListadoAvisosFavoritos($params);
                foreach ($Sugerencias['data'] as $key => $value) {
                    $respuesta[$key]['image'] = trim($value['logoanuncio']);
                    $respuesta[$key]['company'] = $value['empresa_rs'];
                    $respuesta[$key]['title'] = $value['puesto'];
                    $respuesta[$key]['content'] = $value['description'];
                    $respuesta[$key]['ubigeo'] = $value['ubicacion'];
                    $respuesta[$key]['id'] = $value['id'];
                    $respuesta[$key]['url'] = $value['url'];
                    $respuesta[$key]['ishighlight'] = $value['destacado'];
                    $respuesta[$key]['date'] = $value['dias_fp'];
                }
            default:
                break;
        }
        return $respuesta;
    }

    public function getListadoAvisosFavoritos( $params )
    {
        // fix para traer solo una cantidad especifica de elementos
        if(isset($params['numItems'])) {
            $rows = (int) $params['numItems'];
        } else {
            $rows = Zend_Registry::get('config')->paginadorSugerencias->numItems;
        }

        $start = !isset($params['page']) ? 0 : ($params['page'] - 1) * $rows;

        $idPostulante = $params['id_postulante'];
        $anunciosELiminados = array();

        //$anunciosELiminados =  $this->getAnunciosEliminados( $idPostulante );
        $anunciosFavoritos = $this->getAnunciosfavoritos($idPostulante);
//        $anunciosPostulados
        $select = $this->select;
        $select->setStart($start)->setRows($rows);

        $result = array();
        $result['data'] = array();
        $result['ntotal'] = 0;
        $result['count'] = $rows;
//        var_dump($anunciosFavoritos,$anunciosELiminados);exit;
        if(count($anunciosFavoritos) > 0) {
            $anunciosFavoritos = array_diff($anunciosFavoritos, array(NULL));
            $anunciosFavoritos = implode(' OR ', $anunciosFavoritos);
            $anunciosELiminados = array_unique($anunciosELiminados);
            $anunciosELiminados = array_diff($anunciosELiminados, array(NULL));
            foreach ($anunciosELiminados as $id) {
                $select->createFilterQuery('excluir' . $id)->setQuery("id_anuncio_web:(*:* NOT $id)");
            }

//            foreach ($anunciosELiminados as $id)  {
//                $select->createFilterQuery('excluir'.$id)->setQuery("id_anuncio_web:(*:* NOT $id)");
//            }
            $select->createFilterQuery('favoritos')->setQuery("id_anuncio_web:($anunciosFavoritos)");


            $select->addSort('destacado', 'desc');
            $select->addSort('fecha_publicacion', 'desc');
            $resultset = $this->solrCli->select($select);
            $result['ntotal'] = (int) $resultset->getNumFound();


            foreach ($resultset as $doc) {
                $result['data'][] = $this->formatoItemSolr($doc);
            }
        }

        return $result;
    }

    public function getListadoAvisosEliminados( $params )
    {
        // fix para traer solo una cantidad especifica de elementos
        if(isset($params['numItems'])) {
            $rows = (int) $params['numItems'];
        } else {
            $rows = Zend_Registry::get('config')->paginadorSugerencias->numItems;
        }

        $start = !isset($params['page']) ? 0 : ($params['page'] - 1) * $rows;

        $idPostulante = $params['id_postulante'];
        $anunciosELiminados = $this->getAnunciosEliminados($idPostulante);
        $AnunciosFavoritos = $this->getAnunciosfavoritos($idPostulante);
        $select = $this->select;
        $select->setStart($start)->setRows($rows);

        $result = array();
        $result['data'] = array();
        $result['ntotal'] = 0;
        $result['count'] = $rows;

        if(count($anunciosELiminados) > 0) {
            $anunciosELiminados = implode(' OR ', $anunciosELiminados);
            $AnunciosFavoritos = array_unique($AnunciosFavoritos);
            foreach ($AnunciosFavoritos as $id) {
                $select->createFilterQuery('excluir' . $id)->setQuery("id_anuncio_web:(*:* NOT $id)");
            }
            $select->createFilterQuery('eliminados')->setQuery("id_anuncio_web:($anunciosELiminados)");

            $select->addSort('destacado', 'desc');
            $select->addSort('fecha_publicacion', 'desc');

            $resultset = $this->solrCli->select($select);
            $result['ntotal'] = (int) $resultset->getNumFound();


            foreach ($resultset as $doc) {
                $result['data'][] = $this->formatoItemSolr($doc);
            }
        }

        return $result;
    }

    public function getAnunciosfavoritos( $idpostulante )
    {
        //$dyn = new Amazon_Dynamo_Favoritos();
        $dyn = new Mongo_Favoritos();

        $res = $dyn->getDatos($idpostulante);
        if(empty($res)) {
            return array();
        }
        $arr = array();
        foreach ($res as $doc) {
            //if(isset($doc['id_aviso']['S'])){
            if(isset($doc['id_aviso'])) {
                //$arr[] = $doc['id_aviso']['S'];
                $arr[] = $doc['id_aviso'];
            }
            /* if(isset($doc['id_aviso'])){                
              $arr[] = $doc['id_aviso'];
              } */
        }

        return $arr;
    }

    public function getAnunciosEliminados( $idPostulante )
    {
        //$dyn = new Amazon_Dynamo_AvisosSugeridosEliminados();
        $dyn = new Mongo_AvisosSugeridosEliminados();
        $res = $dyn->getDatos($idPostulante);

        $arr = array();

        foreach ($res as $doc) {
            //if(isset($doc['idAnuncioWeb']['S'])){
            if(isset($doc['idAnuncioWeb'])) {
                //$arr[] = $doc['idAnuncioWeb']['S'];
                $arr[] = $doc['idAnuncioWeb'];
            }
            /* if(isset($doc['idAnuncioWeb']['N'])){
              $arr[] = $doc['idAnuncioWeb']['N'];
              } */
        }

        return $arr;
    }

    public function getAnuncioSolr( $idAnuncio )
    {
        $select = $this->select;
        $select->setQuery('*');
        $edismax = $select->getEDisMax();
        $edismax->setQueryFields('destacado^100.0');
        $idanuncio = $idAnuncio; //$param['id_anuncio'];
        $select->createFilterQuery('id')->setQuery("id_anuncio_web:($idanuncio)");

        $item = null;
        $resultset = $this->solrCli->select($select);
        foreach ($resultset as $doc) {
            $item = $this->formatoItemSolr($doc);
        }

        return $item;
    }

    public function nextAvisoSugerido( $param )
    {
        unset($param['id_anuncio']);
        //  $param['numItems'] = 1; // fix para evitar traer muchos items
        $data = $this->getListadoAvisosSugeridos($param);
        if(!isset($data["data"])) {
            return false;
        }

        if(count($data["data"]) == 0) {
            return false;
        }
        $data["data"][0]['logoanuncio'] = trim($data["data"][0]['logoanuncio']);
        if(isset($data["data"][0]['logoanuncio']) && !empty($data["data"][0]['logoanuncio'])) {
            $logo = $this->viewE->E()->getlastcommitElementLogos($data["data"][0]['logoanuncio']);
        } else {
            $logo = $this->view->S('/images/icon-empresa-blank.png');
        }
        $return = array(
            'image' => $logo,
            'company' => $data["data"][0]['empresa_rs'],
            'puesto' => $data["data"][0]['puesto'],
            'title' => $data["data"][0]['title'],
            'content' => mb_substr($data["data"][0]['description'], 0, 50, 'utf-8') . " ...",
            'ubigeo' => $data["data"][0]['ubicacion'],
            'id' => $data["data"][0]['id'],
            'url' => $data["data"][0]['url'],
            'urlAviso' => $data["data"][0]['idAviso'],
            'ishighlight' => $data["data"][0]['destacado'],
//            'urlHighlight'=>'/avisos-sugeridos/agregar-favoritos-ajax',
//            'urlDelete'=>'/avisos-sugeridos/eliminar-anuncio-sugerido-ajax',
            'date' => $data["data"][0]['dias_fp'],
        );

        return $return;
    }

    public function nextAvisoEliminado( $param )
    {
        unset($param['id_anuncio']);
        //  $param['numItems'] = 1; // fix para evitar traer muchos items
        $data = $this->getListadoAvisosEliminados($param);
        if(!isset($data["data"])) {
            return false;
        }
        if(count($data["data"]) == 0) {
            return false;
        }
        $data["data"][0]['logoanuncio'] = trim($data["data"][0]['logoanuncio']);
        if(isset($data["data"][0]['logoanuncio']) && !empty($data["data"][0]['logoanuncio'])) {
            $logo = $this->viewE->E()->getlastcommitElementLogos($data["data"][0]['logoanuncio']);
        } else {
            $logo = $this->view->S('/images/icon-empresa-blank.png');
        }
        $return = array(
            'image' => $logo,
            'company' => $data["data"][0]['empresa_rs'],
            'puesto' => $data["data"][0]['puesto'],
            'title' => $data["data"][0]['title'],
            'content' => mb_substr($data["data"][0]['description'], 0, 50, 'utf-8') . " ...",
            'ubigeo' => $data["data"][0]['ubicacion'],
            'id' => $data["data"][0]['id'],
            'url' => $data["data"][0]['url'],
            'urlAviso' => $data["data"][0]['idAviso'],
            'ishighlight' => $data["data"][0]['destacado'],
            'date' => $data["data"][0]['dias_fp'],
        );

        return $return;
    }

    public function nextFavoritos( $param )
    {
        unset($param['id_anuncio']);
        //    $param['numItems'] = 1; // fix para evitar traer muchos items
        $data = $this->getListadoAvisosFavoritos($param);
        if(!isset($data["data"]) && count($data["data"]) == 0) {
            return false;
        }
        $data["data"][0]['logoanuncio'] = trim($data["data"][0]['logoanuncio']);
        if(isset($data["data"][0]['logoanuncio']) && !empty($data["data"][0]['logoanuncio'])) {
            $logo = $this->viewE->E()->getlastcommitElementLogos($data["data"][0]['logoanuncio']);
        } else {
            $logo = $this->view->S('/images/icon-empresa-blank.png');
        }
        $return = array(
            'image' => $logo,
            'company' => $data["data"][0]['empresa_rs'],
            'puesto' => $data["data"][0]['puesto'],
            'title' => $data["data"][0]['title'],
            'content' => mb_substr($data["data"][0]['description'], 0, 50, 'utf-8') . " ...",
            'ubigeo' => $data["data"][0]['ubicacion'],
            'id' => $data["data"][0]['id'],
            'url' => $data["data"][0]['url'],
            'urlAviso' => $data["data"][0]['idAviso'],
            'ishighlight' => $data["data"][0]['destacado'],
            'date' => $data["data"][0]['dias_fp'],
        );

        return $return;
    }

    public function nextFavoritosEliminado( $param )
    {
//        unset($param['id_anuncio']);
        //  $param['numItems'] = 1; // fix para evitar traer muchos items


        $data = $this->getListadoAvisosFavoritos($param);
        if(!isset($data["data"])) {
            return false;
        }
        if(count($data["data"]) == 0) {
            return false;
        }
        $data["data"][0]['logoanuncio'] = trim($data["data"][0]['logoanuncio']);
        if(isset($data["data"][0]['logoanuncio']) && !empty($data["data"][0]['logoanuncio'])) {
            $logo = $this->viewE->E()->getlastcommitElementLogos($data["data"][0]['logoanuncio']);
        } else {
            $logo = $this->view->S('/images/icon-empresa-blank.png');
        }
        $return = array(
            'image' => $logo,
            'company' => $data["data"][0]['empresa_rs'],
            'puesto' => $data["data"][0]['puesto'],
            'title' => $data["data"][0]['title'],
            'content' => mb_substr($data["data"][0]['description'], 0, 50, 'utf-8') . " ...",
            'ubigeo' => $data["data"][0]['ubicacion'],
            'id' => $data["data"][0]['id'],
            'url' => $data["data"][0]['url'],
            'urlAviso' => $data["data"][0]['idAviso'],
            'ishighlight' => $data["data"][0]['destacado'],
//             'urlHighlight'=>'',
//            'urlDelete'=>'/avisos-sugeridos/agregar-eliminado-favoritos-ajax',
            'date' => $data["data"][0]['dias_fp'],
        );

        return $return;
    }

    private function formatoItemSolr( $doc )
    {
        $date1 = time();
        $fechaPubli = (isset($doc['fecha_publi']) && count($doc['fecha_publi']) > 0) ? $doc['fecha_publi'] : $doc['fecha_publicacion'];
        $fechaPubli = str_replace('T', ' ', $fechaPubli);
        $fechaPubli = str_replace('Z', '', $fechaPubli);


        $date3 = strtotime($fechaPubli);
        $subTime1 = $date1 - $date3;
        $d = ($subTime1 / (60 * 60 * 24)) % 365;
        $dias = $d . 'd';
        if(empty($d)) {
            $fechaPublicacion = str_replace('T', ' ', $doc['fecha_publicacion']);
            $fechaPublicacion = str_replace('Z', '', $fechaPublicacion);
            $date2 = strtotime($fechaPublicacion);
            $subTime = $date1 - $date2;
            $h = ($subTime / (60 * 60)) % 24;
            if(!empty($h)) {
                $dias = $h . 'h';
            } else {
                $m = ($subTime / 60) % 60;
                if(!empty($m)) {
                    $dias = $m . 'm';
                } else {
                    $s = ($subTime) % 60;
                    $dias = $s . 's';
                }
            }
        }
        $item = array(
            "id" => $doc['id_anuncio_web'],
            "title" => $doc['puesto'],
            "puesto" => (strlen(trim($doc['puesto'])) >= 50) ? mb_substr(trim($doc['puesto']), 0, 50, 'utf-8') . " ..." : trim($doc['puesto']),
            "empresa_rs" => $doc['empresa_rs'],
            "logoanuncio" => trim($doc['logoanuncio']),
            "description" => mb_substr($doc["description"], 0, 80, 'utf-8') . " ...",
            "ubicacion" => $doc['ubicacion'],
            "url" => $doc['url'],
            "destacado" => $doc['destacado'],
            "prioridad" => $doc['prioridad'],
            "score" => $doc['score'],
            "slugaviso" => $doc['slugaviso'],
            "idAviso" => str_replace('/ofertas-de-trabajo/', '', $doc['url']),
            "dias_fp" => $dias
        );


        return $item;
    }

    /*
     * Avisos sugeridos para el postulante de acuerdo a los
     * parametros registrados en su registro (paso2 y paso3)
     *
     * @param int $idPostulante         ID del postulante
     *
     * jomaolva00249220
     */

    public function getTotalSugeridosAPostulante( $idPostulante = null )
    {
        $nroSugeridos = 0;

        if($idPostulante) {
            //$dynParamSugeridos = new Amazon_Dynamo_ParamSugeridosPostulante();
            $dynParamSugeridos = new Mongo_ParamSugeridosPostulante();
            $param = $dynParamSugeridos->getDatos($idPostulante);

            if(count($param) > 0) {

                $requestParams = array();

                /// Areas y Niveles del postulante:
                //$arNivelesAreas = unserialize($param['area_nivel']['S']);
                $arNivelesAreas = unserialize($param['area_nivel']);
                //$arNivelesAreas = unserialize($param['area_nivel']);

                foreach ($arNivelesAreas as $item) {
                    $requestParams['areas'][] = $item['area']['name'];
                    $requestParams['puestos'][] = $item['nivel']['name'];
                }


                /// Ubigeo:
                /// Rango de precios:
                /// Buscamos con los filtros en Solr:
                if(count($requestParams) > 0) {
//                    $requestParams['areas'] = implode('---',$requestParams['areas']);
//                    $requestParams['puestos'] = implode('---',$requestParams['puestos']);
                    // $solrAviso = new Solr_SolrAviso();
                    $resultado = $this->getListadoAvisosSugeridos($requestParams);

                    //$decodeBusqueda = Zend_Json::decode($resultado);
                    $nroSugeridos = $resultado['ntotal'];
                }
            }
        }

        return $nroSugeridos;
    }

}
