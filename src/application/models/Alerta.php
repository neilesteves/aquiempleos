<?php

class Application_Model_Alerta extends App_Db_Table_Abstract {

    protected $_name = "alerta";    
     const LIMITES_DE_ALERTAS = 20;
    public function getAlertas($id_propietario = 0,$tipo = null) {
        $sql = $this->_db->select()
                ->from(array('a' => $this->_name), array('a.id', 'a.nombre', 'a.url'))
                ->where('a.id_propietario = ?', $id_propietario)
                ->where('a.tipo = ?', $tipo)
                ->order('a.nombre');
        $result = $this->_db->fetchAll($sql);
        return $result;
    }
    
}