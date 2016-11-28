<?php

class Solr_SolrPostulante extends Solr_SolrClient
{
    // Nombre del core de solr
    protected $core   = 'postulante';
    // String generado por query builder
    private $bqString = '';
    protected $area   = '';

    public function __construct()
    {
        parent::__construct();
        $this->_filter = Zend_Registry::get('config')->filter->toArray();
        $this->select  = $this->solrCli->createSelect();
    }

    public function delete($id)
    {
        $update = $this->solrCli->createUpdate();
        $update->addDeleteQuery("idpostulante:$id");
        $update->addCommit();
        $result = $this->solrCli->update($update);
        return $result->getStatus();
    }

    public function add($id)
    {
        $moPostulante                              = new Application_Model_Postulante();
        $params                                    = $moPostulante->solr($id);
        if (empty($params)) return 0;
        $params['det_aptitudes']                   = $moPostulante->getSolrPostulanteAptitudes($params['idpostulante']);
        $update                                    = $this->solrCli->createUpdate();
        $doc1                                      = $update->createDocument();
        $doc1->area_cargo_interes                  = '';
        $doc1->salario_interes                     = '';
        $doc1->id_ubigeo_interes                   = '';
        $doc1->area_cargo_interes                  = '';
        $doc1->disponibilidad_provincia_extranjero = '';
        $doc1->prefs_confidencialidad              = '';
        $doc1->website                             = '';
        $doc1->telefono                            = '';
        $doc1->celular                             = '';
        $doc1->path_cv                             = '';
        $doc1->idiomas                             = '';
        $doc1->programas_claves                    = '';
        $doc1->otros_estudios                      = '';
        $doc1->destacado                           = '';
        $doc1->conadis_code                        = '';
        $doc1->det_aptitudes                       = array();
        foreach ($params as $k => $v) {
            if (!empty($v)) {
                $doc1->$k = $v;
            }
        }
        $update->addDocument($doc1);
        $update->addCommit();
        $result = $this->solrCli->update($update);
        return $result;
    }

    private function Setfacet($facetSet)
    {
        foreach ($this->_filter['postulante'] as $key => $value) {
            $facetSet->createFacetField("$value")->setField("$value");
        }
    }

    private function getFacet($resultset)
    {
        $data = array();
        //var_dump($this->_filter);exit;
        foreach ($this->_filter['postulante'] as $key => $value) {
       
            //  $data[$value] =
            $data[$value] = $resultset->getFacetSet()->getFacet($value);
        }

        return $data;
    }

    public function search($params = array())
    {
        $Util = new App_Util_Filter();

        $select   = $this->solrCli->createSelect();
        //  $select = $this->select;
        $helper   = $select->getHelper();
        $facetSet = $select->getFacetSet();
        $rows     = Zend_Registry::get('config')->buscadoravisos->buscador->paginadoavisos;
        if (!isset($params['page']) || !is_numeric($params['page']) || $params['page']
            < 2) $start    = 0;
        else $start    = ((int) $params['page'] - 1) * $rows;

        $select->setStart($start)->setRows($rows);
        $this->Setfacet($facetSet);
//        if(isset($params['query']) && !empty($params['query'])) {
//            $q = $Util->clearParamsSolr($params['query']);
//            $de = self::$conectores;
//            $q = str_replace($de, " ", ' ' . trim($q) . ' ');
//            if($q != " ") {
//                $select->setQuery('nomape:\"' . trim($q) . '\"');
//            }
//        }

        $select->addSort('destacado', 'asc');
        $select->addSort('fecha_cv_update', 'desc');
        $resultset            = $this->solrCli->select($select);
        $datRes               = array();
        $dataResult['ntotal'] = $resultset->getNumFound();
        $dataResult['start']  = $start;
        $dataResult['data']   = array();
        foreach ($resultset as $key => $rs) {
            $dataResult['rows'][] = $rs;
        }
        $dataResult['count']  = $rows;
        $dataResult['filter'] = $this->getFacet($resultset);

        // var_dump($dataResult);Exit;
        return $dataResult;
    }

    public function getPostulantes()
    {
        $client            = $this->solrCli;
        $query             = $this->select;
        $response          = $client->select($query);
        $results           = array();
        $results['ntotal'] = $response->getNumFound();

        return $results['ntotal'];
    }

    public function getPostulantesMensuales($fecha)
    {
        $client = $this->solrCli;
        $query  = $this->select;
        $helper = $query->getHelper();

        if (isset($fecha)) {
            $rango = '-1MONTH';
            $query->createFilterQuery('fh_creacion')->setQuery($helper->rangeQuery('fh_creacion',
                    "NOW$rango/DAY", 'NOW'));
        }
        $response          = $client->select($query);
        $results['ntotal'] = $response->getNumFound();
        return $results['ntotal'];
    }
}