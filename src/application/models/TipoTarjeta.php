<?php

class Application_Model_TipoTarjeta extends App_Db_Table_Abstract
{
    protected $_name = "tipo_tarjeta";
    
    public function getTarjetasFormSelect()
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from($this->_name, array('id','descripcion'))
            //->where("estado = 'activo'")
            ->order('descripcion ASC');
        $rs = $this->getAdapter()->fetchPairs($sql);
        return $rs;
    }

}