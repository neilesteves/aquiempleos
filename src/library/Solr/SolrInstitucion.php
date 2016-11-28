<?php

class Solr_SolrInstitucion extends Solr_SolrClient
{

    protected $core = 'institucion';
    private $_config;
    private $_model;
    private $_cache;
    private $valServer;

    public function __construct()
    {
        parent::__construct();
        //$this->_filter = Zend_Registry::get('config')->filter->toArray();
        $this->select = $this->solrCli->createSelect();
    }

    /*
     * Listado de Instituciones buscados por nombre
     * 
     * @param 
     * @return 
     */

    public function getInstitucionByName( $nombre, $cantidad = 0 )
    {
//        if($this->valServer){
//             return 500;
//        }
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
        $nombre = mb_convert_case($nombre, MB_CASE_TITLE, 'UTF-8');
        $nombre = str_replace(' ', '\ ', $nombre);
        $query->setStart(0)->setRows(3);
        $query->setQuery('nombre:"' . $nombre . '*"');
        $query->addSorts(
                array(
                    'nombre' => 'asc'
                )
        );
        $response = $client->suggester($query);

        $results = array();

        foreach ($response as $term => $termResult) {
            $results[$term]['id'] = $termResult['id_institucion'];
            $results[$term]['mostrar'] = $termResult['nombre'];
        }


        return $results;
    }

}
