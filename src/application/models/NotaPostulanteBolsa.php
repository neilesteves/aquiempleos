<?php

class Application_Model_NotaPostulanteBolsa extends App_Db_Table_Abstract
{
    protected $_name = "nota_postulante_bolsa";

    public function crear($idEmpresaPostulante, $idUsuario, $nota)
    {
        $this->insert(
            array('id_empresa_postulante'=>$idEmpresaPostulante, 'id_usuario'=>$idUsuario, 
                'nota'=>$nota, 'fecha' => date("Y-m-d H:i:s"))
        );
    }
    
    public function editar($idNota, $nota)
    {
        return $this->update(
            array('nota'=>$nota),
            $this->getAdapter()->quoteInto('id = ?', $idNota) 
        );
    }
    
    public function eliminar($idNota)
    {
        return $this->delete($this->getAdapter()->quoteInto('id = ?', $idNota));
    }
    
    public function getNotasPostulanteEmpresa($idEmpresaPostulante) 
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array($this->_name),
                array('id' => 'id', 'idEmpresaPostulante' => "id_empresa_postulante", 
                      'nota' => "nota", 'fecha' => "fecha", 'idUsuario' => "id_usuario")
            )
            ->where('id_empresa_postulante = ?', $idEmpresaPostulante)
            ->order("id DESC");
        //echo $sql->assemble(); exit;
        return $this->getAdapter()->fetchAll($sql);
    }
    
    public function getUltimaNotaPostulanteEmpresa($idEmpresaPostulante) 
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array($this->_name),
                array('id' => 'id', 'idEmpresaPostulante' => "id_empresa_postulante", 
                      'nota' => "nota", 'fecha' => "fecha", 'idUsuario' => "id_usuario")
            )
            ->where('id_empresa_postulante = ?', $idEmpresaPostulante)
            ->order("id DESC")
            ->limit(1);
        //echo $sql->assemble(); exit;
        return $this->getAdapter()->fetchRow($sql);
    }
    
    public function getNotaBolsa($idNota) 
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array($this->_name),
                array('id' => 'id', 'idEmpresaPostulante' => "id_empresa_postulante", 
                      'nota' => "nota", 'fecha' => "fecha", 'idUsuario' => "id_usuario")
            )
            ->where('id = ?', $idNota);
            
        //echo $sql->assemble(); exit;
        return $this->getAdapter()->fetchRow($sql);
    }
}
