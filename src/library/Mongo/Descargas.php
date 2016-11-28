<?php
class Mongo_Descargas extends Mongo_Collection
{
    /**
     * @var MongoCollection
     */
    protected $_collection = 'descargas';
    
    public function __construct()
    {
        parent::__construct();
        $this->setUpCollection($this->_collection);
    }
    
    public function save($datos)
    {
      $dtmongo=  $this->contadorTipo($datos['tipo']);
      $datos['num']=$datos['num'];
      if(count($dtmongo)>0){
        $datos['_id']=$dtmongo['_id'];
        $datos['num']=(int)$dtmongo['num']+(int)$datos['num'];
      }
      $id = $this->guardar($datos);
      return $id;
    }
    
    public function contadorTipo($tipo)
    {
       $result=array();
      try {
        $collection = $this->getCollection();
        $res = $collection->find(array(
            'tipo' => $tipo
        ));
        $res->rewind();
        $datos = $res->current();    
        $result = $datos;
      } catch (Exception $ex) {
        $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
        $result=array();
      }
      return $result;
    }
    
}
