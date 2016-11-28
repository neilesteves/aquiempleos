<?php
class Mongo_Login extends Mongo_Collection
{
    /**
     * @var MongoCollection
     */
    protected $_collection = 'login';
    protected $_timeout = 5000;
    
    public function __construct()
    {
        parent::__construct($this->_timeout);
        $this->setUpCollection($this->_collection);
    }
    
    public function save($datos)
    {
        $datos['fecha_hora'] = date('Y-m-d H:i:s');
        $id = $this->guardar($datos);
        return $id;
    }
    
}
