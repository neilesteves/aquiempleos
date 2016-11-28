<?php
class Mongo_BusquedaPostulante extends Mongo_Collection
{
    /**
     * @var MongoCollection
     */
    protected $_collection = 'busqueda_postulante';
    
    public function __construct()
    {
        parent::__construct();
        $this->setUpCollection($this->_collection);
    }
    
    public function save($datos)
    {
        $datos['fecha_hora'] = date('Y-m-d H:i:s');
        $datos['url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $datos['ip'] = $_SERVER['REMOTE_ADDR'];
        $datos['url_origen'] = $_SERVER['HTTP_REFERER'];
        $datos['agente'] = $_SERVER['HTTP_USER_AGENT'];
        $id = $this->guardar($datos);
        return $id;
    }
    
}
