<?php

class Application_Model_Descargas extends App_Db_Table_Abstract
{
    protected $_name = "descargas";
    private $_model = null;

    public function __construct()
    {
        parent::__construct();
        $cparts = explode('_', __CLASS__);
        $this->_model = $cparts[2];
    }

    public function contadores() {
        $sql = $this->_db->select()
                ->from(array('d' => $this->_name), array('d.id', 'd.tipo', 'd.num'));               
        $result = $this->_db->fetchAll($sql);
        return $result;
    }
    
    public function contadorTipo($tipo) {
        $sql = $this->_db->select()
                ->from(array('d' => $this->_name), array('d.num'))    ;   
        $sql->where("d.tipo = ? ","$tipo");
        $result = $this->_db->fetchOne($sql);
        return $result;
    }
}