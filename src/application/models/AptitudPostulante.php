<?php

class Application_Model_AptitudPostulante extends App_Db_Table_Abstract
{
    protected $_name = "aptitudes_postulante";    
    
    public function registrar($data)
    {
        
        $this->insert($data);
    }
    public function update($data)
    {
        
        $this->update($data);
    }
    public function guardarDataAptitud($data) {
        $dAptPost=$this->obteneraptitud($data['id_postulante'],$data['id']);
        
        if($dAptPost){
            $aptitud['id_postulante']=$data['id_postulante'];
            $aptitud['id_aptitud']=$data['id'];
            $aptitud['estado']='1';            
            $this->insert($data);
        }else{
            $where = $this->getAdapter()->quoteInto('id = ?',
            $dAptPost['id']);
            $aptitud['id_postulante']=$data['id_postulante'];
            $aptitud['id_aptitud']=$data['id'];
            $aptitud['estado']='1';
            $this->update($data,$where);
        }
        
    }
    public function quitar($id)
    {
        $where = $this->getAdapter()->quoteInto('id =?', (int)$id);        
        return $this->delete($where);
    }
    
    public function quitarPorPerfil($perfilId)
    {
        $where = $this->getAdapter()->quoteInto('id_perfil =?', 
                (int)$perfilId);        
        return $this->delete($where);
    }
    
    public function obteneraptitud($idpostulante,$idaptitud)
    {
        return $this->_db->select()->fetchRow($this->select()
            ->where('id_postulante =?', $idpostulante)->where('id_aptitud =?', $idaptitud));
    }
}