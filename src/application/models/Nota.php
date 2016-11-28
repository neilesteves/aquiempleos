<?php

class Application_Model_Nota extends App_Db_Table_Abstract
{
    protected $_name = "nota";
    
    public function getNotasPostulaciones($idPostulacion)
    {
        $sql = $this->_db->select()
                ->from(
                    $this->_name,
                    array(
                        'id_nota'=>'id',
                        'fecha'=>'fh',
                        'text',
                        'path_original'
                    )
                )
                ->where('id_postulacion = ?', $idPostulacion)
                ->order('fecha desc');
        $rs = $this->getAdapter()->fetchAll($sql);
        return $rs;
    }
    
    public function getNotaPostulacion($idNota)
    {
        $sql = $this->_db->select()
                ->from(
                    $this->_name, 
                    array('id_nota'=>'id', 'fecha'=>'fh',
                    'text', 'path', 'path_original')
                )
                ->where('id= ?', $idNota);
        $rs = $this->getAdapter()->fetchRow($sql);
        return $rs;
    }
}
