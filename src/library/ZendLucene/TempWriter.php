<?php
/**
 * Libreria para manejo de Zend Search Lucene
 * @author Solman Vaisman Gonzalez
 */
class ZendLucene_TempWriter
{
    //BUSCADOR PARA PROCESO SELECCION
    protected $_index;
    protected $_ruta;

    public function __construct()
    {
       
    }

    public function encolarElemento($tipo, $params, $namefunction)
    {
        $objTemp = new Application_Model_TempLucene();
        $data = array(
            "tipo" => $tipo,
            "params" => serialize($params),
            "namefunction" => $namefunction
        );
        $objTemp->insert($data);
    }
}