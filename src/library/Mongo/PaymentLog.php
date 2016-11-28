<?php
class Mongo_PaymentLog extends Mongo_Collection
{
    
    protected $_collection = 'paymentLog';
    
    public function __construct()
    {
        parent::__construct();
        $this->setUpCollection($this->_collection);
    }
    
    public function save($datos)
    {
        $datos['fecha_hora'] = date('Y-m-d H:i:s');
        $datos['ip'] = $_SERVER['REMOTE_ADDR'];
        $id = $this->guardar($datos);
        return $id;
    }
    
}