<?php

class Application_Model_PeContingenciaCompra extends App_Db_Table_Abstract {

    protected $_name = "pe_contingencia_compra";
    
    const RUC = 'RUT';

    //Obtiene registro buscado por el id_compra
    public function getRegistroByCompra($idCompra) {

        $sql = $this->getAdapter()->select()->from($this->_name)
                ->where('id_compra = ?', $idCompra);

        $data = $this->getAdapter()->fetchRow($sql);

        if ($data) {
            return $data;
        }

        return null;
    }

    public function registrar($encripta) {
        $reg=array();
        $reg['encripta']=$encripta;
        $reg['fh_creacion']=date("Y-m-d H:i:s");
      return $this->insert($reg);
    }
    
    public function actualiza($reg){
        
        $id=$reg['id'];
        return $this->update(
            array('id_compra'=>$reg['id_compra'],
                'fh_edicion'=> date("Y-m-d H:i:s")),
            $this->getAdapter()->quoteInto('id = ?', $id) 
        );
    }


    
  

}
