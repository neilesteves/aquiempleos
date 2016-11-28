<?php

class Application_Model_TipoVia extends App_Db_Table_Abstract {

    protected $_name = "tipo_via";

    const OTROS_TIPO_CARRERA = 16;
    const ACTIVO = 1;
    const INACTIVO = 0;

    public function __construct($config = array()) {
        parent::__construct($config);
    }

    public function lista() {

        $sql = $this->getAdapter()->select()->from($this->_name);
        
        return $this->getAdapter()->fetchAll($sql);
        
    }
     public function listaTipoVia() {

        $sql = $this->getAdapter()->select()->from(array('tv'=>$this->_name), array('id'=>'tv.codigo','descripcion'=>'tv.descripcion'));
        
        return $this->getAdapter()->fetchPairs($sql);
        
    }

}
