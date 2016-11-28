<?php

class Solr_SolrPuesto extends Solr_SolrClient
{

    private $coreName = 'puesto';
    protected $core = 'puesto';
    private $_config;
    private $_cache;
    private $select = null;

    public function __construct()
    {
        parent::__construct();
        $this->select = $this->solrCli->createSelect();
    }

    /*
     * Listado de Instituciones buscados por nombre
     * 
     * @param 
     * @return 
     */

    public function getPuestosByName( $nombre, $cantidad = 5 )
    {
        $nombre = str_replace(
                array("\\", "¨", "º", "-", "~",
            "#", "@", "|", "!", "\"",
            "·", "$", "%", "&", "/",
            "(", ")", "?", "'", "¡",
            "¿", "[", "^", "`", "]",
            "+", "}", "{", "¨", "´",
            ">", "< ", ";", ",", ":", '"', "'",
            "."), '', $nombre
        );
        $client = $this->solrCli;
        $query = $this->select;
        $query->setStart(0)->setRows($cantidad);
        $query->setStart(0)->setRows(3);
        $nombre = mb_convert_case($nombre, MB_CASE_TITLE, 'UTF-8');
        $query->setQuery('nombre:"' . $nombre . '*"');
        $response = $client->suggester($query);
        $results = array();
        foreach ($response as $term => $termResult) {
            foreach ($termResult as $k => $v) {
                $results[$term]['id'] = $termResult['id'];
                $results[$term]['mostrar'] = $termResult['nombre'];
            }
        }
        return $results;
    }

    public function getPuestos()
    {
        $fs = new App_Filter_Slug();
        $client = $this->solrCli;
        $query = $this->select;
        $query->setRows(100000);
        $response = $client->select($query);
        $results = array();

        foreach ($response as $termResult) {
            $nombre = $termResult['nombre'];
            $slug = $fs->filter1($nombre);
            $results[$nombre[0]][$slug] = $nombre;
        }
        return $results;
    }

}
