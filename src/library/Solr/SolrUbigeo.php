<?php

class Solr_SolrUbigeo
{

    private $url = null;
    // Nombre del core de solr
    private $coreName = 'ubigeo';
    private $_config;
    private $_model;
    private $_cache;
    private $solrCli = null;
    private $select = null;
    private $valServer;
    private $validador;

    public function __construct()
    {
        $this->valServer = false;
        $this->helperSever = new App_Controller_Action_Helper_Solr();
        $mConfig = Zend_Registry::get('config')->solrUbigeo;
        if(!$this->helperSever->valServidorSor($mConfig)) {
            $this->valServer = true;
        }
        $this->_config = Zend_Registry::get('config');
        $this->_model = 'ubigeo';
        $this->_cache = Zend_Registry::get('cache');


        //var_dump($mConfig);exit;
        $this->solrCli = new Solarium\Client($mConfig);

        $this->select = $this->solrCli->createSelect();
    }

    /*
     * Adicionar el Aviso en el Solar
     *
     * @param Int $id   ID del Aviso.
     * @return Int      Retorna 0 en caso de exito, caso contrario un entero.
     *
     */


    /*
     * Elimina el Aviso en el Solar
     *
     * @param Int $id   ID del Aviso.
     * @return Int      Retorna 0 en caso de exito, caso contrario un entero.
     *
     */

    public function getUbicacionByName( $ubicacion, $cantidad )
    {
        $ubicacion = str_replace(
                array("\\", "¨", "º", "-", "~",
            "#", "@", "|", "!", "\"",
            "·", "$", "%", "&", "/",
            "(", ")", "?", "'", "¡",
            "¿", "[", "^", "`", "]",
            "+", "}", "{", "¨", "´",
            ">", "< ", ";", ",", ":", '"', "'",
            "."), '', $ubicacion
        );
        $client = $this->solrCli;
        $query = $this->select;
        $ubicacion = str_replace(' ', '\ ', $ubicacion);
//        var_dump($cantidad);exit;
        $query->setStart(0)->setRows($cantidad);
        $query->setQuery("*$ubicacion*");
        $query->createFilterQuery('pais')->setQuery("pais_id:2533");

        $edismax = $query->getEDisMax();
        $edismax->setQueryFields('ubicacion'); //->setQuery("pais_id:2533");
        //  $edismax->setBoostQuery('pais_id:2533');

       // $query->createFilterQuery('ubicacion_id')->setQuery("pais_id:2533");

        $response = $client->suggester($query);
        $results = array();
        foreach ($response as $term => $termResult) {
            foreach ($termResult as $k => $v) {
                $results[$term]['id'] = $termResult['dist_id'];
                $results[$term]['mostrar'] = $termResult['prov_nombre'] . ', ' . $termResult['dpto_nombre'];
            }
        }

        return $results;
    }

    public function obtenerEmpresasBusquedaAvanzada( $descripcion )
    {
        $select = $this->select;
        $fDescripcion = mb_strtolower($descripcion);
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

    public function addUbigeo( $params )
    {
        $update = $this->solrCli->createUpdate();
        $doc1 = $update->createDocument();
        foreach ($params as $k => $v) {
            $doc1->$k = $v;
        }
        $update->addDocument($doc1);
        $update->addCommit();
        $result = $this->solrCli->update($update);

        if(empty($result))
            return 1;
        else
            return 0;
        //return $result->getStatus();
    }

}
