<?php

class Solr_SolrCarrera extends Solr_SolrClient
{

    private $url = null;
    // Nombre del core de solr
    protected $core = 'carrera';

    const TIPOMONEDA = '$';

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

    /*
     * Listado de Carreras buscados por nombre
     * 
     * @param 
     * @return 
     */

    public function getCarreraByName($queryString, $cantidad)
    {
        $queryString = str_replace(
                array(
            "\\",
            "¨",
            "º",
            "-",
            "~",
            "#",
            "@",
            "|",
            "!",
            "\"",
            "·",
            "$",
            "%",
            "&",
            "/",
            "(",
            ")",
            "?",
            "'",
            "¡",
            "¿",
            "[",
            "^",
            "`",
            "]",
            "+",
            "}",
            "{",
            "¨",
            "´",
            ">",
            "< ",
            ";",
            ",",
            ":",
            '"',
            "'",
            "."), '', $queryString
        );
        $client = $this->solrCli;
        $query = $this->select;
        $query->setStart(0)->setRows($cantidad);
        //var_dump($queryString);exit;
        $queryString = mb_convert_case($queryString, MB_CASE_TITLE, 'UTF-8');
        $queryString = str_replace(' ', '\ ', $queryString);
        $query->setQuery('nombre:"' . $queryString . '*"');

        $response = $client->suggester($query);
        $results = array();

        foreach ($response as $term => $termResult) {
            // foreach($termResult as $k => $v){ 
            //var_dump($term,$termResult);
            $results[$term]['id'] = $termResult['id_carrera'];
            $results[$term]['mostrar'] = $termResult['nombre'];
            //}
        }
        //exit;
        //var_dump($results);exit;
        return $results;
    }

    public function getCarrera()
    {
        $client = $this->solrCli;
        $query = $this->select;
        $query->setRows(100000)->addSort('slug', 'asc');
        $response = $client->select($query);
        $results = array();
        foreach ($response as $termResult) {
            $results[$termResult['slug']] = $termResult['nombre'];
        }
        return $results;
    }

}
