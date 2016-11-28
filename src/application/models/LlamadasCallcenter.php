<?php

class Application_Model_LlamadasCallcenter extends App_Db_Table_Abstract
{
    protected $_name = "llamadas_callcenter";

    public function getAllByEmpresa($idEmpresa)
    {
        $sql  =  $this->select()
                ->from(
                    array(
                        "llc"=>$this->_name
                    ),
                    array(
                        'id'=>'id',
                        'fecha_registro'=>'fecha_registro'
                    )
                )
                ->where("llc.id_empresa = ?", $idEmpresa)
                ->order("llc.fecha_registro desc")
                ->limit('5');
        $rs = $this->getAdapter()->fetchAll($sql);
        return $rs;
    }
}