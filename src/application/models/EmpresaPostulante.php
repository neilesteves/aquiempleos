<?php

class Application_Model_EmpresaPostulante extends App_Db_Table_Abstract
{
    protected $_name = "empresa_postulante";

    public function crear($idEmpresa, $idPostulante)
    {
        $this->insert(
            array('id_empresa'=>$idEmpresa, 'id_postulante'=>$idPostulante)
        );
    }
    
    public function getEmpresaPostulante($idEmpresa, $idPostulante)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array($this->_name),
                array('id' => 'id')
            )
            ->where('id_postulante = ?', $idPostulante)
            ->where('id_empresa = ?', $idEmpresa);
          
        $idEP = $this->getAdapter()->fetchOne($sql);
        
        if ($idEP == null || $idEP == "") {
            $this->crear($idEmpresa, $idPostulante);
            $idEP = $this->getAdapter()->fetchOne($sql);
        }
        
        return $idEP;
    }
}
