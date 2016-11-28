<?php
/**
 * Created by PhpStorm.
 * User: usuario
 * Date: 20/03/15
 * Time: 12:32 PM
 */

class Solr_SolrExperiencia {

    private $solrCli = null;
    private $selct = null;

    function __construct()
    {
        $config = Zend_Registry::get('config')->solrPuestos;
        $this->solrCli = new Solarium\Client($config);
        $this->select = $this->solrCli->createSelect();
    }


    /*
     *  @params : array con los parametros de busqueda
     *    - q : query a buscar
     *    - orden : orden en que se devolveran los resultados
     *    - ( anything else ... )
     */
    function getBusquedaPuesto( $params ) {
       // var_dump($params);
    }


}