<?php

class Solr_SolrAviso extends Solr_SolrClient
{

    private $url = null;
    // Nombre del core de solr
    protected $core = 'aviso';

    const TIPOMONEDA = 'C$';

    protected $_config;
    private $_model;
    protected $_cache;
    private $valServer;
    private $validador;

    public function __construct()
    {
        parent::__construct();
        $this->_config = Zend_Registry::get('config');
        $this->_cache = Zend_Registry::get('cache');
        $this->select = $this->solrCli->createSelect();
    }

    public function obtenerResultadoBuscaMas1( $params )
    {
        $moneda = $this->_config->app->moneda;
        $fs = new App_Filter_Slug();
        $rows = Zend_Registry::get('config')->buscadoravisos->buscador->paginadoavisos;
        if(!isset($params['page']))
            $start = 0;
        else
            $start = ($params['page'] - 1) * $rows;
        $arrFecha = array(
            "fecha|hoy" => array("clave" => "-5HOURS", "valor" => "Hoy"),
            "fecha|hace-2-dias" => array("clave" => "-2DAYS", "valor" => "Hace 2 días"),
            "fecha|ultima-semana" => array("clave" => "-7DAYS", "valor" => "Última semana"),
            "fecha|ultima-quincena" => array("clave" => "-15DAYS", "valor" => "Últimos 15 días"),
            "fecha|ultimo-mes" => array("clave" => "-1MONTH", "valor" => "Último mes")
        );
        $arrSalario = array(
            "salario|0-750" => array("min" => "1", "max" => "750", "valor" => "De {$moneda}0 a {$moneda}750"),
            "salario|751-1500" => array("min" => "751", "max" => "1500", "valor" => "De {$moneda}751 a {$moneda}1500"),
            "salario|1501-3000" => array("min" => "1501", "max" => "3000", "valor" => "De {$moneda}1501 a {$moneda}3000"),
            "salario|3001-6000" => array("min" => "3001", "max" => "6000", "valor" => "De {$moneda}3001 a {$moneda}6000"),
            "salario|6001-10000" => array("min" => "6001", "max" => "10000", "valor" => "De {$moneda}6001 a {$moneda}10000"),
            "salario|mas-10000" => array("min" => "10001", "max" => "*", "valor" => "Más de {$moneda}10000")
        );
        $extraFecha = "";
        foreach ($arrFecha as $k => $v)
            $extraFecha.="&facet.query={!key=$k}fecha_publicacion:[NOW{$v['clave']}/DAY+TO+NOW]";
        $extraSalario = "";
        foreach ($arrSalario as $k => $v)
            $extraSalario.="&facet.query={!key=$k}price:{$v['min']}+AND+price2:{$v['max']}";
        $extra = "$extraFecha$extraSalario&facet.field=area&facet.field=nivel&facet.field=ubicacion&facet.field=dataempresa&start=$start&rows=$rows";
        if(isset($params['q'])) {
            $q = $params['q'];
            /* $arrQ = explode(' ', $q);
              array_walk($arrQ, function(&$value, $key) { $value .= '~'; });
              $rq = implode('OR', $arrQ); */
            $q = str_replace(' ', '+', $q);
            $de = array("DE", "de", "De", "dE");
            $q = str_replace($de, "", $q);

            $extra.="&q=$q~0.8&qf=adecsys_code+url_id+puesto^100.0+description+empresa_rs+nivel_busqueda+carrera_busqueda&defType=edismax&_val_=\"score,prioridad+asc,fecha_publicacion+desc\"";
            //$extra.="&q=*:*&fq=adecsys_code:$q+OR+url_id:$q+OR+empresa_rs:($rq)";
        } else {
            $extra.="&q=*:*&sort=prioridad+asc,fecha_publicacion+desc";
        }
        if(isset($params['areas'])) {
            $areas = str_replace('--', '+OR+', $params['areas']);
            $extra.="&fq=areaslug:($areas)";
        }
        if(isset($params['nivel'])) {
            $nivel = str_replace('--', '+OR+', $params['nivel']);
            $extra.="&fq=nivelslug:($nivel)";
        }
        if(isset($params['ubicacion'])) {
            $ubicacion = str_replace('--', '+OR+', $params['ubicacion']);
            $extra.="&fq=ubicacionslug:($ubicacion)";
        }
        if(isset($params['remuneracion'])) {
            $salario_min = $arrSalario['salario|' . $params['remuneracion']]['min'];
            $salario_max = $arrSalario['salario|' . $params['remuneracion']]['max'];
            $extra.="&fq=price:$salario_min&fq=price2:$salario_max";
        }
        if(isset($params['fecha-publicacion'])) {
            $rango = $arrFecha['fecha|' . $params['fecha-publicacion']]['clave'];
            $extra.="&fq=fecha_publicacion:[NOW$rango/DAY+TO+NOW]";
        }
        if(isset($params['carrera'])) {
            $carrera = str_replace('--', '+OR+', $params['carrera']);
            $extra.="&fq=carreraslug:($carrera)";
        }
        if(isset($params['empresa'])) {
            $empresa = str_replace('--', '+OR+', $params['empresa']);
            $extra.="&fq=empresaslug:($empresa)";
        }
        $url = $this->url . $extra;
        //echo $url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $resultado = curl_exec($ch);
        curl_close($ch);
        $resultado = Zend_Json::decode($resultado);
        $resultado['ntotal'] = $resultado['response']['numFound'];
        $resultado['start'] = $start;
        $resultado['count'] = $rows;
        $docs = $resultado['response']['docs'];
        $data = array();
        foreach ($docs as $doc) {
            $datetime1 = new DateTime('now');
            $datetime2 = new DateTime($doc['fecha_publicacion']);
            $interval = $datetime1->diff($datetime2);
            $dias = $interval->format('%a');
            $doc["dias_fp"] = $dias;
            $data[] = $doc;
        }
        $resultado['data'] = $data;
        $area = $resultado['facet_counts']['facet_fields']['area'];
        $level = $resultado['facet_counts']['facet_fields']['nivel'];
        $location = $resultado['facet_counts']['facet_fields']['ubicacion'];
        $fecha = $resultado['facet_counts']['facet_queries'];
        $empresa = $resultado['facet_counts']['facet_fields']['dataempresa'];
        $areaF = array();
        foreach ($area as $k => $v) {
            if($v > 0) {
                $s = explode('|', $k);
                $areaF[] = array('label' => $s[0], 'slug' => $s[1], 'count' => $v);
            }
        }
        $resultado['filter']['area'] = $areaF;
        $levelF = array();
        foreach ($level as $k => $v) {
            if($v > 0) {
                $s = explode('|', $k);
                $levelF[] = array('label' => $s[0], 'slug' => $s[1], 'count' => $v);
            }
        }
        $resultado['filter']['level'] = $levelF;
        $locationF = array();
        foreach ($location as $k => $v) {
            if($v > 0) {
                $s = $fs->filter($k);
                $locationF[] = array('label' => $k, 'slug' => $s, 'count' => $v);
            }
        }
        $resultado['filter']['location'] = $locationF;
        $fechaF = array();
        $salarioF = array();
        foreach ($fecha as $k => $v) {
            if($v > 0) {
                $e = explode('|', $k);
                if($e[0] == 'fecha') {
                    $s = $arrFecha[$k]['valor'];
                    $fechaF[] = array('label' => $s, 'slug' => $e[1], 'count' => $v);
                }
                if($e[0] == 'salario') {
                    $s = $arrSalario[$k]['valor'];
                    $salarioF[] = array('label' => $s, 'slug' => $e[1], 'count' => $v);
                }
            }
        }
        $empresaF = array();
        foreach ($empresa as $k => $v) {
            if($v > 0) {
                $s = explode('|', $k);
                $empresaF[] = array('label' => $s[2], 'slug' => $s[0], 'count' => $v);
            }
        }
        $resultado['filter']['level'] = $levelF;
        $resultado['filter']['fecha'] = $fechaF;
        $resultado['filter']['salario'] = $salarioF;
        $resultado['filter']['company_slug'] = $empresaF;
        $resultado = Zend_Json::encode($resultado);
        return $resultado;
    }

    public function obtenerResultadoBuscaMasCache( $url = null )
    {
        $url = ($url ? $url : $this->url);
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . implode('_', $url);
        if($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        //Solo en local
        if(APPLICATION_ENV == 'development') {
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
            curl_setopt($ch, CURLOPT_PROXY, '172.21.0.83:3128');
        }

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $resultado = curl_exec($ch);
        curl_close($ch);

        $this->_cache->save($resultado, $cacheId, array(), $cacheEt);

        return $resultado;
    }

    public function ordenarArray( $toOrderArray, $field, $inverse = false )
    {
        $position = array();
        $newRow = array();
        $otros = array();

        foreach ($toOrderArray as $key => $row) {
            //Niveles que no deben mostrar por JJC
            if($row['slug'] != 'senior' && $row['slug'] != 'alta-gerencia' &&
                    $row['slug'] != 'gerencia-de-obra') {
                $position[$key] = $row[$field];
                $newRow[$key] = $row;
            }
        }
        if($inverse) {
            arsort($position);
        } else {
            asort($position);
        }
        $returnArray = array();
        foreach ($position as $key => $pos) {
            if($newRow[$key]['slug'] == 'otros') {
                $otros = $newRow[$key];
                unset($newRow[$key]);
                continue;
            }
            if($newRow[$key]['slug'] != '')
                $returnArray[] = $newRow[$key];
        }

        if(!empty($otros))
            array_push($returnArray, $otros);

        return $returnArray;
    }

    public function ordenarArrayUbicacion( $toOrderArray, $field, $inverse = false )
    {
        $position = array();
        $newRow = array();

        //No tomar en cuenta dis callao y Perú
        foreach ($toOrderArray as $key => $row) {

//                if ($row['slug'] != 'peru' && $row['slug'] != 'carmen-de-la-legua-reynoso'
//                        && $row['slug'] != 'la-perla' && $row['slug'] != 'la-punta' && $row['slug'] != 'bellavista'
//                        && $row['slug'] != 'ventanilla') {
            if($row['slug'] != 'peru') {
                $position[$key] = $row[$field];
                $newRow[$key] = $row;
            }
        }
//        if ($inverse) {
//            arsort($position);
//        }
//        else {
//            asort($position);
//        }
        $returnArray = array();
        foreach ($position as $key => $pos) {
            $returnArray[] = $newRow[$key];
        }
        return $returnArray;
    }

    //Lista de carrera pa la búsqueda avanzada
    public function obtenerCarreraSearchAdvanced1()
    {
        $fs = new App_Filter_Slug();
        $rows = Zend_Registry::get('config')->buscadoravisos->buscador->paginadoavisos;
        $start = 0;
        $moneda = self::TIPOMONEDA;
        $arrFecha = array(
            "fecha|hoy" => array("clave" => "", "valor" => "Hoy"),
            "fecha|hace-2-dias" => array("clave" => "-2DAYS", "valor" => "Hace 2 días"),
            "fecha|ultima-semana" => array("clave" => "-7DAYS", "valor" => "Última semana"),
            "fecha|ultima-quincena" => array("clave" => "-15DAYS", "valor" => "Últimos 15 días"),
            "fecha|ultimo-mes" => array("clave" => "-1MONTH", "valor" => "Último mes")
        );
        $arrSalario = array(
            "salario|0-750" => array("min" => "0", "max" => "750", "valor" => "De {$moneda}0 a {$moneda}750"),
            "salario|751-1500" => array("min" => "751", "max" => "1500", "valor" => "De {$moneda}751 a {$moneda}1500"),
            "salario|1501-3000" => array("min" => "1501", "max" => "3000", "valor" => "De {$moneda}1501 a {$moneda}3000"),
            "salario|3001-6000" => array("min" => "3001", "max" => "6000", "valor" => "De {$moneda}3001 a {$moneda}6000"),
            "salario|6001-10000" => array("min" => "6001", "max" => "10000", "valor" => "De {$moneda}6001 a {$moneda}10000"),
            "salario|mas-10000" => array("min" => "10001", "max" => "*", "valor" => "Más de {$moneda}10000")
        );
        $extraFecha = "";
        foreach ($arrFecha as $k => $v)
            $extraFecha.="&facet.query={!key=$k}fecha_publicacion:[NOW{$v['clave']}/DAY+TO+NOW]";
        $extraSalario = "";
        foreach ($arrSalario as $k => $v)
            $extraSalario.="&facet.query={!key=$k}price:{$v['min']}&price2:{$v['max']}";
        $extra = "&q=*:*$extraFecha$extraSalario&facet.field=area&facet.field=nivel&facet.field=ubicacion&facet.pivot=tipo_carrera,carrera&f.tipo_carrera.facet.sort=false&start=$start&rows=$rows";
        $url = $this->url . $extra;


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $resultado = curl_exec($ch);
        curl_close($ch);
        $resultado = Zend_Json::decode($resultado);
        $area = $resultado['facet_counts']['facet_fields']['area'];
        $level = $resultado['facet_counts']['facet_fields']['nivel'];
        $location = $resultado['facet_counts']['facet_fields']['ubicacion'];
        $areaF = array();
        foreach ($area as $k => $v) {
            if($v > 0) {
                $s = explode('|', $k);
                $areaF[] = array('label' => $s[0], 'slug' => $s[1], 'count' => $v);
            }
        }
        $resultado['filter']['area'] = $areaF;
        $levelF = array();
        foreach ($level as $k => $v) {
            if($v > 0) {
                $s = explode('|', $k);
                $levelF[] = array('label' => $s[0], 'slug' => $s[1], 'count' => $v);
            }
        }
        $resultado['filter']['level'] = $levelF;
        $locationF = array();
        foreach ($location as $k => $v) {
            if($v > 0) {
                $s = $fs->filter($k);
                $locationF[] = array('label' => $k, 'slug' => $s, 'count' => $v);
            }
        }
        $resultado['filter']['location'] = $locationF;
        $pivot = $resultado['facet_counts']['facet_pivot']['tipo_carrera,carrera'];
        $rs = array();
        foreach ($pivot as $pv) {
            $row = array();
            $row['nombre'] = $pv['value'] . '(' . $pv['count'] . ')';
            $dc = array();
            $sc = array();
            foreach ($pv['pivot'] as $spv) {
                $arrSPV = explode('|', $spv['value']);
                if($arrSPV[0] == $pv['value']) {
                    $dc[] = $arrSPV[1] . '(' . $spv['count'] . ')';
                    $sc[] = $arrSPV[2];
                }
            }
            $row['des_carrera'] = implode('.', $dc);
            $row['slug_carrera'] = implode(',', $sc);
            $rs[] = $row;
        }
        $resultado['filter']['carrera'] = $rs;
        $fecha = $resultado['facet_counts']['facet_queries'];
        $fechaF = array();
        $salarioF = array();
        foreach ($fecha as $k => $v) {
            if($v > 0) {
                $e = explode('|', $k);
                if($e[0] == 'fecha') {
                    $s = $arrFecha[$k]['valor'];
                    $fechaF[$e[1]] = "$s($v)";
                }
                if($e[0] == 'salario') {
                    $s = $arrSalario[$k]['valor'];
                    $salarioF[$e[1]] = "$s($v)";
                }
            }
        }
        $resultado['filter']['fecha'] = $fechaF;
        $resultado['filter']['salario'] = $salarioF;
        return $resultado;
    }

    /*
     * Adicionar el Aviso en el Solar
     *
     * @param Int $id   ID del Aviso.
     * @return Int      Retorna 0 en caso de exito, caso contrario un entero.
     *
     */

    public function addAvisoSolr( $id )
    {
        $log = Zend_Registry::get('log');
        try {
            $config = Zend_Registry::get('config');
            //$mConfig = Zend_Registry::get('config')->solrAviso;
            //$sc = new Solarium\Client($mConfig);
            $sc = $this->solrCli;
            $solradd = 1;
           // $moAviso = new Solr_SolrAbstract($sc, $this->coreName);
            $Aviso = new Application_Model_AnuncioWeb();
            $params = $Aviso->getSolrAviso($id);

            if($params) {
                $params["description"] = str_replace($config->avisopaso2->caractereiregulares->toArray(), ' ', $params["description"]);
                $params["description_busqueda"] = str_replace($config->avisopaso2->caractereiregulares->toArray(), ' ', $params["description_busqueda"]);
                $params['carrera'] = $Aviso->getSolrAvisoCarrera($params['id_anuncio_web']);
                $params['estudio'] = $Aviso->getSolrAvisoEstudio($params['id_anuncio_web']);
                $params['experiencia'] = $Aviso->getSolrAvisoExperiencia($params['id_anuncio_web']);
                $params['idioma'] = $Aviso->getSolrAvisoIdioma($params['id_anuncio_web']);
                $params['programa'] = $Aviso->getSolrAvisoPrograma($params['id_anuncio_web']);
                $params['pregunta'] = $Aviso->getSolrAvisoPregunta($params['id_anuncio_web']);
                $solradd = $this->add($params);
                if($solradd === 0) {
                    $where = $Aviso->getAdapter()->quoteInto('id = ?', (int) $id);
                    $Aviso->update(array('buscamas' => 1), $where);
                }
                return $solradd;
            } else {
                $solradd = $this->delete($id);
                return $solradd;
            }
            return 1;
        } catch(Solarium\Exception\HttpException $exc) {
            $log->log($exc->getMessage() . '. ' . $exc->getTraceAsString(), Zend_Log::ERR);
            $Aviso = new Application_Model_AnuncioWeb();
            $where = $Aviso->getAdapter()->quoteInto('id = ?', (int) $id);
            $Aviso->update(array('buscamas' => 0), $where);

            $mail = new App_Controller_Action_Helper_Mail();
            $config = Zend_Registry::get('config');
            $dataMail = array(
                'to' => $config->emailing->bccNotificacionSemanal,
                'razonSocial' => $exc->getMessage(),
                'medioPago' => $exc->getTraceAsString(),
                'tipoAnuncio' => ',no es un aviso es error solr',
                'usuario' => (int) $id,
                'anuncioId' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
                'compraId' => "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
            );
            $mail->adecsysAviso($dataMail);
            return 1;
        } catch(Exception $exc) {
            $log->log($exc->getMessage() . '. ' . $exc->getTraceAsString(), Zend_Log::ERR);
            $Aviso = new Application_Model_AnuncioWeb();
            $where = $Aviso->getAdapter()->quoteInto('id = ?', (int) $id);
            $Aviso->update(array('buscamas' => 0), $where);
            $mail = new App_Controller_Action_Helper_Mail();
            $config = Zend_Registry::get('config');
            echo $exc->getTraceAsString();exit;
            $dataMail = array(
                'to' => $config->emailing->bccNotificacionSemanal,
                'razonSocial' => $exc->getMessage(),
                'medioPago' => $exc->getTraceAsString(),
                'tipoAnuncio' => ',no es un aviso es error solr',
                'usuario' => (int) $id,
                'anuncioId' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
                'compraId' => "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"
            );
            $mail->adecsysAviso($dataMail);
            return 1;
        }
    }

    public function add( $params )
    {

        $update = $this->solrCli->createUpdate();
        $doc1 = $update->createDocument();
        foreach ($params as $k => $v)
            $doc1->$k = $v;
        $update->addDocument($doc1);
        $update->addCommit();
        $result = $this->solrCli->update($update);
        $select = $this->solrCli->createSelect();
        $select->setQuery("id_anuncio_web:{$params['id_anuncio_web']}");
        $resultset = $this->solrCli->select($select);
        $nro = $resultset->getNumFound();
        if(empty($nro))
            return 1;
        else
            return 0;
        //return $result->getStatus();
    }

    /*
     * Elimina el Aviso en el Solar
     *
     * @param Int $id   ID del Aviso.
     * @return Int      Retorna 0 en caso de exito, caso contrario un entero.
     *
     */

    public function DeleteAvisoSolr( $id )
    {

        //$mConfig = Zend_Registry::get('config')->solrAviso;
        //$sc = new Solarium\Client($mConfig);
        $sc = $this->solrCli;
        $moAviso = new Solr_SolrAbstract($sc, $this->coreName);
        $solradd = $moAviso->delete($id, "id_anuncio_web");

        return $solradd;
    }

    public function delete($id)
    {
        $update = $this->solrCli->createUpdate();
        $update->addDeleteQuery("id_anuncio_web:$id");
        $update->addCommit();
        $result = $this->solrCli->update($update);
        return $result->getStatus();
    }

    public function DeleteAvisoSolrCron( $id )
    {

        $mConfig = Zend_Registry::get('config')->solrAviso;
        $sc = new Solarium\Client($mConfig);

        //$sc = $this->solrCli;
        $moAviso = new Solr_SolrAbstract($sc, $this->coreName);
        $solradd = $moAviso->delete($id, "id_anuncio_web");

        return $solradd;
    }

    public function DeleteAvisoXEmpresaSolr( $id )
    {

        //$mConfig = Zend_Registry::get('config')->solrAviso;
        //$sc = new Solarium\Client($mConfig);
        $sc = $this->solrCli;
        $moAviso = new Solr_SolrAbstract($sc, $this->coreName);
        $solradd = $moAviso->delete($id, "id_empresa");

        return $solradd;
    }

    public function obtenerEmpresasBusquedaAvanzada1( $descripcion )
    {
        $fDescripcion = str_replace(' ', '\+', mb_strtolower($descripcion));
        $extra = "&q=*:*&fq=mostrar_empresa:1&fq=razon_social:$fDescripcion*+OR+nombre_comercial:$fDescripcion*&facet=true&facet.field=dataempresa&facet.sort=false";
        $url = $this->url . $extra;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $resultado = curl_exec($ch);
        curl_close($ch);
        $resultado = Zend_Json::decode($resultado);
        $dataempresa = $resultado['facet_counts']['facet_fields']['dataempresa'];
        $dataempresaF = array();

        foreach ($dataempresa as $k => $v) {
            if($v > 0) {
                $s = explode('|', $k);
                $dataempresaF[] = array('nombre' => trim($s[0]) . ' (' . trim($s[1]) . ')', 'val' => $s[2]);
            }
        }
        return $dataempresaF;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getInfoAvisos( $params = array() )
    {
        $fs = new App_Filter_Slug();

        $select = $this->select;
        $facetSet = $select->getFacetSet()->setLimit(100000);

        $facetSet->createFacetField('area')->setField('area');
        $facetSet->createFacetField('ubicacion')->setField('ubicacion');
        $facetSet->createFacetField('carreras')->setField('carrera');
        $facetSet->createFacetField('puestos')->setField('puesto_ft');
        $facetSet->createFacetField('empresas')->setField('nombre_comercial');

        $resultado = array();
        $resultset = $this->solrCli->select($select);
        $area = $resultset->getFacetSet()->getFacet('area');
        $ubicacion = $resultset->getFacetSet()->getFacet('ubicacion');
        $carreras = $resultset->getFacetSet()->getFacet('carreras');
        $puestos = $resultset->getFacetSet()->getFacet('puestos');
        $empresas = $resultset->getFacetSet()->getFacet('empresas');


        foreach ($area as $k => $v) {
            list($txt, $slug) = explode('|', $k);
            $resultado['areas'][$slug] = $txt;
        }

        foreach ($ubicacion as $k => $c) {
            $slug = $fs->filter1($k);
            $resultado['ubicacion'][$slug] = $k;
        }
        unset($resultado['ubicacion']['peru']);

        foreach ($carreras as $k => $c) {
            $carreraData = explode('|', $k);
            if(isset($carreraData[1])) {
                $slug = $fs->filter1($carreraData[1]);
                $resultado['carreras'][$slug] = $carreraData[1];
            }
        }

        $arrPuestos = array();
        $puestos = (array) $puestos;
        foreach ($puestos as $k => $c) {
            $arrPuestos = $c;
        }
        ksort($arrPuestos);
        foreach ($arrPuestos as $k => $c) {
            $slug = $fs->filter1($k);
            $k = ucwords(strtolower($k));
            $k = preg_replace('/^([^a-z]+)/i', '', $k);
            $resultado['puestos'][strtoupper($k[0])][$slug] = $k;
        }

        $arrEmpresas = array();
        $empresas = (array) $empresas;
        foreach ($empresas as $k => $c) {
            $arrEmpresas = $c;
        }
        ksort($arrEmpresas);
        foreach ($arrEmpresas as $k => $c) {
            $slug = $fs->filter1($k);
            $k = ucwords(strtolower($k));
            $k = preg_replace('/^([^a-z]+)/i', '', $k);
            $resultado['empresas'][strtoupper($k[0])][$slug] = $k;
        }

        asort($resultado['areas']);
        asort($resultado['ubicacion']);
        asort($resultado['carreras']);
        ksort($resultado['puestos']);
        ksort($resultado['empresas']);

        return $resultado;
    }

    public function obtenerResultadoBuscaMas( $params )
    {

        $util = new App_Util();
        $select = $this->select;
        $helper = $select->getHelper();
        $facetSet = $select->getFacetSet()->setLimit(100000);
        $fs = new App_Filter_Slug();
        $moneda = self::TIPOMONEDA;
        $rows = Zend_Registry::get('config')->buscadoravisos->buscador->paginadoavisos;
        if(isset($params['page']) && !$params['page']) {
            $params['page'] = '1';
        }

        if(isset($params['rows'])) {
            $rows = $params['rows'];
        }

        if(!isset($params['page']) || !is_numeric($params['page']) || $params['page'] < 2)
            $start = 0;
        else {
            $params['page'] = (int) $params['page'];
            $start = ($params['page'] - 1) * $rows;
        }


        if(!isset($params['q']))
            $params['q'] = '';
        $select->setStart($start)->setRows($rows);
        $arrFecha = array(
            "fecha|hoy" => array("clave" => "-5HOURS", "valor" => "Hoy"),
            "fecha|hace-2-dias" => array("clave" => "-2DAYS", "valor" => "Hace 2 días"),
            "fecha|ultima-semana" => array("clave" => "-7DAYS", "valor" => "Última semana"),
            "fecha|ultima-quincena" => array("clave" => "-15DAYS", "valor" => "Últimos 15 días"),
            "fecha|ultimo-mes" => array("clave" => "-1MONTH", "valor" => "Último mes")
        );
        $arrSalario = array(
            "salario|0-750" => array("min" => "1", "max" => "750", "valor" => "De {$moneda}0 a {$moneda}750"),
            "salario|751-1500" => array("min" => "751", "max" => "1500", "valor" => "De {$moneda}751 a {$moneda}1500"),
            "salario|1501-3000" => array("min" => "1501", "max" => "3000", "valor" => "De {$moneda}1501 a {$moneda}3000"),
            "salario|3001-6000" => array("min" => "3001", "max" => "6000", "valor" => "De {$moneda}3001 a {$moneda}6000"),
            "salario|6001-10000" => array("min" => "6001", "max" => "10000", "valor" => "De {$moneda}6001 a {$moneda}10000"),
            "salario|mas-10000" => array("min" => "10001", "max" => "*", "valor" => "Más de {$moneda}10000")
        );
        $facet = $facetSet->createFacetMultiQuery('fecha');
        foreach ($arrFecha as $k => $v)
            $facet->createQuery($k, $helper->rangeQuery('fecha_publicacion', "NOW{$v['clave']}/DAY", 'NOW'));
        $facet = $facetSet->createFacetMultiQuery('salario');
        foreach ($arrSalario as $k => $v)
            $facet->createQuery($k, "price:{$v['min']} AND price2:{$v['max']}");
        $facetSet->createFacetField('area')->setField('area');
        $facetSet->createFacetField('nivel')->setField('nivel');
        $facetSet->createFacetField('ubicacion')->setField('ubicacion')->setSort(false);
        $facetSet->createFacetField('discapacidad')->setField('discapacidad')->setSort(false);
        $facetSet->createFacetField('dataempresa')->setField('dataempresa');

        $params['q'] = str_replace(
                array("\\", "¨", "º", "-", "~",
            "#", "@", "|", "!", "\"",
            "·", "$", "%", "&", "/",
            "(", ")", "?", "'", "¡",
            "¿", "[", "^", "`", "]",
            "+", "}", "{", "¨", "´",
            ">", "< ", ";", ",", ":", '"', "'",
            "."), ' ', $params['q']
        );
        $filter = new Zend_Filter_Alnum(array('allowwhitespace' => true));


        $q = $filter->filter($params['q']);

        if(!empty($q)) {
            //$q = str_replace(' ','\ ',$q);
            //$q = mb_strtolower($q);
            $de = array(" DE ", " de ", " De ", " dE ");
            $q = str_replace($de, " ", $q);


            $select->setQuery("$q");
            //$select->setQuery("$q~0.8");
            $edismax = $select->getEDisMax();
            $edismax->setQueryFields('adecsys_code url_id puesto^100.0');
            //$edismax->setBoostQuery('destacado:1^20 puesto:"'.$q.'"^100');
//            if( preg_match('/\s/',$q) )
//                $boost = 20;
//            else
            $boost = 50;
            $edismax->setBoostQuery('prioridad:1^' . $boost . ' puesto:"' . $q . '"^25');
            if(preg_match('/\s/', $q)) {
                $select->addSorts(
                        array(
                            'score' => 'desc',
                            'prioridad' => 'asc',
                            'fecha_publicacion' => 'desc',
                        )
                );
            } else {
                $select->addSorts(
                        array(
                            'prioridad' => 'asc',
                            'fecha_publicacion' => 'desc',
                            'score' => 'desc',
                        )
                );
            }
        } else {
            $select->addSort('prioridad', 'asc');
            $select->addSort('fecha_publicacion', 'desc');
        }
        if(isset($params['areas'])) {
            $areas = $util->paramSolr($params['areas']);
            $select->createFilterQuery('areas')->setQuery("areaslug:($areas)");
        }
        if(isset($params['nivel'])) {
            $nivel = $util->paramSolr($params['nivel']);
            $select->createFilterQuery('nivel')->setQuery("nivelslug:($nivel)");
        }
        if(isset($params['ubicacion'])) {
            $ubicacion = ($util->paramSolrUbigeo($params['ubicacion']));
            $select->createFilterQuery('ubicacion')->setQuery("ubicacionslug:($ubicacion)");
        }
        if(isset($params['remuneracion'])) {
            $salario_min = $arrSalario['salario|' . $params['remuneracion']]['min'];
            $salario_max = $arrSalario['salario|' . $params['remuneracion']]['max'];
            $arrRemuneracion = explode('-', $params['remuneracion']);
            if(empty($salario_min))
                $salario_min = (int) $arrRemuneracion[0];
            if(empty($salario_max))
                $salario_max = (int) $arrRemuneracion[1];
            //$select->createFilterQuery('price')->setQuery("price:$salario_min");
            //$select->createFilterQuery('price2')->setQuery("price2:$salario_max");
            $select->createFilterQuery('price')->setQuery($helper->rangeQuery('price', 0, $salario_max));
            $select->createFilterQuery('price2')->setQuery($helper->rangeQuery('price2', $salario_min, 15000));
        }
        if(isset($params['fecha-publicacion'])) {
            $rango = $arrFecha['fecha|' . $params['fecha-publicacion']]['clave'];
            $select->createFilterQuery('fecha-publicacion')->setQuery($helper->rangeQuery('fecha_publicacion', "NOW$rango/DAY", 'NOW'));
        }
        if(isset($params['carrera'])) {
            $carrera = $util->paramSolr($params['carrera']);
            $select->createFilterQuery('carrera')->setQuery("carreraslug:($carrera)");
        }

        if(isset($params['empresa'])) {
            $empresa = str_replace('ñ', 'n', $params['empresa']);
            $empresa = str_replace('Ñ', 'N', $empresa);
            $empresa = $util->paramSolr($empresa);
            $select->createFilterQuery('mostrar')->setQuery("mostrar_empresa:1");
            $select->createFilterQuery('empresa')->setQuery("empresaslug:($empresa)");
        }
        if(isset($params['aviso']['id_empresa']) && is_int($params['aviso']['id_empresa'])) {
            $idempresa = $params['aviso']['id_empresa'];
            $select->createFilterQuery('mostrar')->setQuery("mostrar_empresa:1");
            $select->createFilterQuery('id_empresa')->setQuery("id_empresa:($idempresa)");
        }
        if(isset($params['ignore'])) {
            $id_aviso = $params['ignore'];
            $select->createFilterQuery('excluir' . $id_aviso)->setQuery("id_anuncio_web:(*:* NOT $id_aviso)");
        }
        if(isset($params['dataignore']) && is_array($params['dataignore'])) {
            foreach ($params['dataignore'] as $id) {
                if(!$id) {
                    $id = 1;
                }
                $select->createFilterQuery('excluir_data' . $id)->setQuery("id_anuncio_web:(*:* NOT $id)");
            }
        }
        if(isset($params['discapacidad'])) {
            $select->createFilterQuery('discapacidad')->setQuery("discapacidad:1");
        }
        if(isset($params['pais'])) {
            $slugpais = $params['pais'];
            $select->createFilterQuery('slugpais')->setQuery("slugpais:($slugpais)");
        }
        $resultado = array();
        $resultset = $this->solrCli->select($select);
        $resultado['ntotal'] = $resultset->getNumFound();
        $resultado['start'] = $start;
        $resultado['count'] = $rows;
        $docs = $resultset;
        $data = array();
        foreach ($docs as $doc) {

            $fecha = str_replace('T', ' ', $doc['fecha_publi']);
            $fecha = str_replace('Z', '', $fecha);
            $date1 = time();
            $date3 = strtotime($fecha);
            $subTime1 = $date1 - $date3;
            $d = ($subTime1 / (60 * 60 * 24)) % 365;
            if(!empty($d)) {
                $dias = $d . 'd';
            } else {
                $fecha2 = str_replace('T', ' ', $doc['fecha_publicacion']);
                $fecha2 = str_replace('Z', '', $fecha2);
                $date2 = strtotime($fecha2);

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
            $rep = array('T', 'Z');
            $fh_pub = str_replace($rep, " ", $doc['fecha_publi']);
            $logodefault = 'photoEmpDefault.png';
            $doc1 = array(
                "id" => $doc['id_anuncio_web'],
                "puesto" => $doc['puesto'],
                "empresa_rs" => $doc['empresa_rs'],
                "nombre_comercial" => $doc['nombre_comercial'],
                "empresaslug" => $doc['empresaslug'],
                "logoanuncio" => trim($doc['logoanuncio']),
                "description" => $doc['description'],
                "ubicacion" => $doc['ubicacion'],
                "ubicacionslug" => $doc['ubicacionslug'],
                "url" => $doc['url'],
                "url_id" => $doc['url_id'],
                "slug" => $doc['slugaviso'],
                'mostrar_empresa' => $doc['mostrar_empresa'],
                "destacado" => $doc['destacado'],
                "prioridad" => $doc['prioridad'],
                "score" => $doc['score'],
                "fh_pub" => trim($fh_pub),
                "dias_fp" => $dias
            );
            $data[] = $doc1;
        }
        $resultado['data'] = $data;
        $area = $resultset->getFacetSet()->getFacet('area');
        $level = $resultset->getFacetSet()->getFacet('nivel');
        $location = $resultset->getFacetSet()->getFacet('ubicacion');
        $empresa = $resultset->getFacetSet()->getFacet('dataempresa');
        $salario = $resultset->getFacetSet()->getFacet('salario');
        $fecha = $resultset->getFacetSet()->getFacet('fecha');
        $discapacidad = $resultset->getFacetSet()->getFacet('discapacidad');
        $areaF = array();

        $discapacidadAviso = array();
        foreach ($discapacidad as $k => $v) {

            if($v > 0) {
                $s = $k;
                $discapacidadAviso[] = array('label' => 'Apto para discapacitados', 'slug' => $s, 'count' => $v);
            }
        }
        $resultado['filter']['discapacidad'] = $discapacidadAviso;

        foreach ($area as $k => $v) {

            if($v > 0) {
                $s = explode('|', $k);
                $areaF[] = array('label' => $s[0], 'slug' => $s[1], 'count' => $v);
            }
        }


        $resultado['filter']['area'] = $areaF;
        $levelF = array();
        foreach ($level as $k => $v) {
            if($v > 0) {
                $s = explode('|', $k);
                $levelF[] = array('label' => $s[0], 'slug' => $s[1], 'count' => $v);
            }
        }

        $resultado['filter']['level'] = $levelF;
        $locationF = array();
        $no_permitidas = array("Á","É","Í","Ó","Ú","á","é","í","ó","ú");
        $permitidas = array("A","E","I","O","U","a","e","i","o","u");
        foreach ($location as $k => $v) {
            if($v > 0) {
                $s = $fs->filterUbicacion($k);
                $s = str_replace($no_permitidas, $permitidas ,$s);
                $locationF[] = array('label' => $k, 'slug' => mb_strtolower($s), 'count' => $v);
            }
        }
        $resultado['filter']['location'] = $locationF;
        $fechaF = array();
        $salarioF = array();
        foreach ($salario as $k => $v) {
            if($v > 0) {
                $e = explode('|', $k);
                $s = $arrSalario[$k]['valor'];
                $salarioF[] = array('label' => $s, 'slug' => $e[1], 'count' => $v);
            }
        }
        foreach ($fecha as $k => $v) {
            if($v > 0) {
                $e = explode('|', $k);
                $s = $arrFecha[$k]['valor'];
                $fechaF[] = array('label' => $s, 'slug' => $e[1], 'count' => $v);
            }
        }
        $empresaF = array();
        foreach ($empresa as $k => $v) {
            if($v > 0) {
                $s = explode('|', $k);
                $empresaF[] = array('label' => $s[2], 'slug' => $s[0], 'count' => $v);
            }
        }
        $resultado['filter']['fecha'] = $fechaF;
        $resultado['filter']['salario'] = $salarioF;
        $resultado['filter']['company_slug'] = $empresaF;
        $resultado = Zend_Json::encode($resultado);
//        if(CONTROLLER == 'home' && MODULE == 'postulante' && ACTION == 'index'){
//             $cache = Zend_Registry::get('cache');
//             $cacheId = 'solrAviso' . '' . 'homepostulante';
//             $cacheEt = $this->_config->cache->SolrAviso->HomePostulante;
//             $cache->save($resultado, $cacheId, array(), $cacheEt);
//        }
        return $resultado;
    }

    public function obtenerCarreraSearchAdvanced()
    {
        $select = $this->select;
        $helper = $select->getHelper();
        $facetSet = $select->getFacetSet()->setLimit(100000);
        $fs = new App_Filter_Slug();
        $rows = Zend_Registry::get('config')->buscadoravisos->buscador->paginadoavisos;
        $start = 0;
        $arrFecha = array(
            "fecha|hoy" => array("clave" => "", "valor" => "Hoy"),
            "fecha|hace-2-dias" => array("clave" => "-2DAYS", "valor" => "Hace 2 días"),
            "fecha|ultima-semana" => array("clave" => "-7DAYS", "valor" => "Última semana"),
            "fecha|ultima-quincena" => array("clave" => "-15DAYS", "valor" => "Últimos 15 días"),
            "fecha|ultimo-mes" => array("clave" => "-1MONTH", "valor" => "Último mes")
        );
        $arrSalario = array(
            "salario|0-750" => array("min" => "1", "max" => "750", "valor" => "De {$moneda}0 a {$moneda}750"),
            "salario|751-1500" => array("min" => "751", "max" => "1500", "valor" => "De {$moneda}751 a {$moneda}1500"),
            "salario|1501-3000" => array("min" => "1501", "max" => "3000", "valor" => "De {$moneda}1501 a {$moneda}3000"),
            "salario|3001-6000" => array("min" => "3001", "max" => "6000", "valor" => "De {$moneda}3001 a {$moneda}6000"),
            "salario|6001-10000" => array("min" => "6001", "max" => "10000", "valor" => "De {$moneda}6001 a {$moneda}10000"),
            "salario|mas-10000" => array("min" => "10001", "max" => "*", "valor" => "Más de {$moneda}10000")
        );
        $facet = $facetSet->createFacetMultiQuery('fecha');
        foreach ($arrFecha as $k => $v)
            $facet->createQuery($k, $helper->rangeQuery('fecha_publicacion', "NOW{$v['clave']}/DAY", 'NOW'));
        $facet = $facetSet->createFacetMultiQuery('salario');
        foreach ($arrSalario as $k => $v)
            $facet->createQuery($k, "price:{$v['min']} AND price2:{$v['max']}");
        $facetSet->createFacetField('area')->setField('area');
        $facetSet->createFacetField('nivel')->setField('nivel');
        $facetSet->createFacetField('ubicacion')->setField('ubicacion');
        $facet = $facetSet->createFacetPivot('tipo_carrera-carrera');
        $facet->addFields('tipo_carrera,carrera');
        $resultado = array();
        $resultset = $this->solrCli->select($select);
        $area = $resultset->getFacetSet()->getFacet('area');
        $level = $resultset->getFacetSet()->getFacet('nivel');
        $location = $resultset->getFacetSet()->getFacet('ubicacion');
        $pivot = $resultset->getFacetSet()->getFacet('tipo_carrera-carrera');
        $salario = $resultset->getFacetSet()->getFacet('salario');
        $fecha = $resultset->getFacetSet()->getFacet('fecha');
        $rs = array();
        foreach ($pivot as $pv) {
            $row = array();
            $row['nombre'] = $pv->getValue() . ' (' . $pv->getCount() . ')';
            $dc = array();
            $sc = array();
            foreach ($pv->getPivot() as $spv) {
                $arrSPV = explode('|', $spv->getValue());
                if($arrSPV[0] == $pv->getValue()) {
                    $dc[] = $arrSPV[1] . ' (' . $spv->getCount() . ')';
                    $sc[] = $arrSPV[2];
                }
            }
            $row['des_carrera'] = implode('.', $dc);
            $row['slug_carrera'] = implode(',', $sc);
            $rs[] = $row;
        }
        asort($rs);
        $resultado['filter']['carrera'] = $rs;
        $areaF = array();
        foreach ($area as $k => $v) {
            if($v > 0) {
                $s = explode('|', $k);
                $areaF[] = array('label' => $s[0], 'slug' => $s[1], 'count' => $v);
            }
        }
        $resultado['filter']['area'] = $areaF;
        $levelF = array();
        foreach ($level as $k => $v) {
            if($v > 0) {
                $s = explode('|', $k);
                $levelF[] = array('label' => $s[0], 'slug' => $s[1], 'count' => $v);
            }
        }
        $resultado['filter']['level'] = $levelF;
        $locationF = array();
        foreach ($location as $k => $v) {
            if($v > 0) {
                $s = $fs->filter($k);
                $locationF[] = array('label' => $k, 'slug' => $s, 'count' => $v);
            }
        }
        $resultado['filter']['location'] = $locationF;
        $fechaF = array();
        $salarioF = array();
        foreach ($salario as $k => $v) {
            if($v > 0) {
                $e = explode('|', $k);
                $s = $arrSalario[$k]['valor'];
                $salarioF[$e[1]] = "$s ($v)";
            }
        }
        foreach ($fecha as $k => $v) {
            if($v > 0) {
                $e = explode('|', $k);
                $s = $arrFecha[$k]['valor'];
                $fechaF[$e[1]] = "$s ($v)";
            }
        }
        $resultado['filter']['fecha'] = $fechaF;
        $resultado['filter']['salario'] = $salarioF;
        return $resultado;
    }

    public function obtenerEmpresasBusquedaAvanzada( $descripcion )
    {
        $select = $this->select;
        $descripcion = str_replace(
                array("\\", "¨", "º", "-", "~",
            "#", "@", "|", "!", "\"",
            "·", "$", "%", "&", "/",
            "(", ")", "?", "'", "¡",
            "¿", "[", "^", "`", "]",
            "+", "}", "{", "¨", "´",
            ">", "< ", ";", ",", ":", '"', "'",
            "."), '', $descripcion
        );
        $fDescripcion = mb_strtolower($descripcion);
        $fDescripcion = str_replace(' ', '\ ', $fDescripcion);
        $select->createFilterQuery('mostrar')->setQuery("mostrar_empresa:1");
        $select->createFilterQuery('empresa')->setQuery("razon_social:$fDescripcion* OR nombre_comercial:$fDescripcion*");
        $facetSet = $select->getFacetSet()->setLimit(100000);
        $facetSet->createFacetField('dataempresa')->setField('dataempresa')->setSort(false);
        $resultset = $this->solrCli->select($select);
        $dataempresa = $resultset->getFacetSet()->getFacet('dataempresa');
        $dataempresaF = array();

        foreach ($dataempresa as $k => $v) {
            if($v > 0) {
                $s = explode('|', $k);
                $dataempresaF[] = array('mostrar' => trim($s[0]) . ' (' . trim($s[1]) . ')', 'id' => $s[2]);
            }
        }
        return $dataempresaF;
    }

    public function obtenerEmpresasBusquedaAvanzadaold( $descripcion )
    {
        $select = $this->select;
        $fDescripcion = mb_strtolower($descripcion);
        $fDescripcion = str_replace(' ', '\ ', $fDescripcion);
        $select->createFilterQuery('mostrar')->setQuery("mostrar_empresa:1");
        $select->createFilterQuery('empresa')->setQuery("razon_social:$fDescripcion* OR nombre_comercial:$fDescripcion*");
        $facetSet = $select->getFacetSet()->setLimit(100000);
        $facetSet->createFacetField('dataempresa')->setField('dataempresa')->setSort(false);
        $resultset = $this->solrCli->select($select);
        $dataempresa = $resultset->getFacetSet()->getFacet('dataempresa');
        $dataempresaF = array();

        foreach ($dataempresa as $k => $v) {
            if($v > 0) {
                $s = explode('|', $k);
                $dataempresaF[] = array('nombre' => trim($s[0]) . ' (' . trim($s[1]) . ')', 'val' => $s[2]);
            }
        }
        return $dataempresaF;
    }

    public static function areanew( $area )
    {
        $itemArea = array(
            'almacen' => 'abastecimiento-y-logistica',
            'compras-logistica' => 'abastecimiento-y-logistica',
            'administracion-servicios-generales' => 'administracion-y-finanzas',
            'banca' => 'administracion-y-finanzas',
            //  'contabilidad'=>'administracion-y-finanzas',
            'finanzas' => 'administracion-y-finanzas',
            'comercial' => 'comercial-ventas',
            'ventas' => 'comercial-ventas',
            'call-center' => 'call-center-telemarketing',
            'comercio-exterior-aduanas' => 'comercio-exterior',
            'investigacion-y-desarrollo' => 'educacion-docencia-e-investigacion',
            'hoteleria-turismo-restaurantes' => 'hoteleria-turismo',
            //'auditoria'=>'auditoria',
            //'legal'=>'legal',
            // 'marketing-publicidad'=>'marketing-publicidad',
            'comunicaciones' => 'marketing-publicidad',
            //'operaciones'=>'operaciones',
            //'produccion'=>'produccion',
            'control-aseguramiento-calidad' => 'produccion',
            //'proyectos'=>'proyecto',
            //'recursos-humanos'=>'recursos-humanos',
            'seguridad-salud-ocupacional-medio-ambiente' => 'recursos-humanos',
            'medios-digitales-internet' => 'sistemas-ti',
            'sistemas' => 'sistemas-ti',
            // 'mantenimiento'=>'mantenimiento',
            'mantenimiento-equipos-maquinarias' => 'mantenimiento',
        );
        return isset($itemArea[$area]) ? $itemArea[$area] : false;
    }

    public function getAvisoByPuesto( $name, $movil = false )
    {
        $name = str_replace(
                array("\\", "¨", "º", "-", "~",
            "#", "@", "|", "!", "\"",
            "·", "$", "%", "&", "/",
            "(", ")", "?", "'", "¡",
            "¿", "[", "^", "`", "]",
            "+", "}", "{", "¨", "´",
            ">", "< ", ";", ",", ":", '"', "'",
            "."), ' ', $name
        );


        $page = 10;
        if(isset($this->_config->autocomplete->getAvisoByPuesto)) {
            $page = $this->_config->autocomplete->getAvisoByPuesto;
            if($movil == 'true') {
                $page = 5;
            }
        }
        $params = array(
            'q' => $name,
        );
        $resultado = $this->obtenerResultadoBuscaMas($params);
        $resultadoBusqueda = Zend_Json::decode($resultado);
        $rest = array();
        $data = array();
        if(isset($resultadoBusqueda['data'])) {
            foreach ($resultadoBusqueda['data'] as $key => $value) {
                $puesto = trim($value['puesto']);
                $data[] = ucwords(mb_strtolower($puesto));
            }
            $data = array_unique($data);
            $i = 0;
            foreach ($data as $k => $termResult) {
                $rest[$i]['id'] = SITE_URL . '/peru/buscar/q/' . $termResult;
                $rest[$i]['mostrar'] = ucwords(mb_strtolower($termResult));
                if($i + 1 > $page) {
                    unset($resultadoBusqueda['data']);
                    unset($rest[$i]);
                }
                $i++;
            }
        }

        return $rest;
    }

    public function getUbicacionByName( $name )
    {
        $name = str_replace(
                array("\\", "¨", "º", "~",
            "#", "@", "|", "!", "\"",
            "·", "$", "%", "&", "/",
            "(", ")", "?", "'", "¡",
            "¿", "[", "^", "`", "]",
            "+", "}", "{", "¨", "´",
            ">", "< ", ";", ",", ":", '"', "'",
            "."), '', $name
        );
        $client = $this->solrCli;
        $query = $this->select;
        $slugFilter = new App_Filter_Slug();
        $page = 10000;
        $name = $slugFilter->filter($name);
        $query->setQuery('ubicacionslug:' . $name . '*');
        $response = $client->suggester($query);
        $results = array();
        $rs = array();
        foreach ($response as $term => $termResult) {

            $rs[$termResult['ubicacionslug']] = ucwords(mb_strtolower($termResult['ubicacion']));
        }
        $i = 0;
        foreach ($rs as $key => $value) {
            $results[$i]['id'] = $key;
            $results[$i]['mostrar'] = $value;
            $i++;
        }
        return $results;
    }

    public function getSolr( $id )
    {
        $select = $this->solrCli->createSelect();
        $select->setQuery("id_anuncio_web:$id");
        $resultset = $this->solrCli->select($select);
        $docs = $resultset;
        $data = array();
        foreach ($docs as $key => $value) {
            foreach ($value as $k => $v) {
                $data[$k] = $v;
            }
        }
        return $data;
    }

    public function ActualizaFechaProcesoSolr( $id )
    {
        $sc = $this->solrCli;
        $moAviso = new Solr_SolrAbstract($sc, $this->coreName);
        $params = $this->getSolr((int) $id);
        if($params) {
            $params['fh_vencimiento_proceso'] = date('Y-m-d') . 'T00:00:00Z';
            unset($params['_version_']);
            unset($params['score']);
            $solradd = $moAviso->add($params);
        }
    }

    public function ActualizaPrioridad( $dataPrioridad )
    {
        $sc = $this->solrCli;
        $moAviso = new Solr_SolrAbstract($sc, $this->coreName);
        $params = $this->getSolr((int) $dataPrioridad['id']);
        if($params) {
            $params['prioridad'] = $dataPrioridad['prioridad'];
            $params['destacado'] = 0;
            unset($params['_version_']);
            unset($params['score']);
            $solradd = $moAviso->add($params);
        }
    }

    public function getAvisoRelacionadosnew( $param, $page )
    {
        $params['rows'] = $page;
        $params['ignore'] = $param['id'];
        $params['areas'] = $param['area_puesto_slug'];
        $params['nivel'] = $param['nivel_puesto_slug'];
        $dataAviso = Zend_Json::decode($this->obtenerResultadoBuscaMas($params));
        $dataAviso = isset($dataAviso["data"]) ? $dataAviso["data"] : array();
        $datarelacionado = array();
        foreach ($dataAviso as $key => $value) {
            $datarelacionado[] = array(
                'id' => $value['id'],
                'empresa_rs' => $value['empresa_rs'],
                "nombre_comercial" => $value['nombre_comercial'],
                "empresaslug" => $value['empresaslug'],
                'puesto' => $value['puesto'],
                'url_id' => $value['url_id'],
                'slug' => $value['slug'],
                'description' => $value['description'],
                'dias_fp' => $value['dias_fp'],
                'prioridad' => $value['prioridad'],
                'logo' => trim($value['logoanuncio']),
                'mostrar_empresa' => $value['mostrar_empresa'],
                'ubicacion' => $value['ubicacion'],
                "ubicacionslug" => $value['ubicacionslug'],
            );
        }
        return $datarelacionado;
    }

    public function getEmpresaAvisos( $dataAvisos = array(), $page )
    {
        $favorito = array();
        $datarelacionado = array();
        $auth = Zend_Auth::getInstance()->getStorage()->read();
        if(!empty($auth) && isset($auth['postulante']['id'])) {
            $solSugerencias = new Solr_SolrSugerencia();
            $idpostulante = $auth['postulante']['id'];
            $favorito = $solSugerencias->getAnunciosfavoritos($idpostulante);
            $params['dataignore'] = $favorito;
        }

        $params['rows'] = $page;
        $params['aviso']['id_empresa'] = (int) $dataAvisos['id_empresa'];
        $params['ignore'] = (int) $dataAvisos['id_aviso'];
        $dataAviso = Zend_Json::decode($this->obtenerResultadoBuscaMas($params));
        $dataAviso = isset($dataAviso["data"]) ? $dataAviso["data"] : array();




        foreach ($dataAviso as $key => $value) {
//              if () {
//
//              }
            $datarelacionado[] = array(
                'id' => $value['id'],
                'empresa_rs' => $value['empresa_rs'],
                "nombre_comercial" => $value['nombre_comercial'],
                "empresaslug" => $value['empresaslug'],
                'puesto' => $value['puesto'],
                'url_id' => $value['url_id'],
                'slug' => $value['slug'],
                'description' => $value['description'],
                'dias_fp' => $value['dias_fp'],
                'logo' => trim($value['logoanuncio']),
                'mostrar_empresa' => $value['mostrar_empresa'],
                'ubicacion' => $value['ubicacion'],
                "ubicacionslug" => $value['ubicacionslug'],
                'destacado' => (int) $value['destacado'],
                'url' => $value['url']
            );
        }

        return $datarelacionado;
    }

}
