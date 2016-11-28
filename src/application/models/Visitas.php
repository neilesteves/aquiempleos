<?php

class Application_Model_Visitas extends App_Db_Table_Abstract 
{

    protected $_name = 'visitas';    
    
    public function getVisitas($id_postulante = 0,$tipo = 0) 
    {
        $sql = $this->_db->select()->from($this->_name, array(
            'total'=>'COUNT(id)'
        ));
        if (!empty($id_postulante)) {
            $sql->where('id_postulante = ?', $id_postulante);
        }
        
        if (!empty($tipo)) {
            $sql->where('tipo = ?', $tipo);        
        }
        
        $result = $this->_db->fetchOne($sql);
        return $result;
    }
    
    
    
    public function getPrimeraVisita(
            $id_postulante = null, 
            $id_empresa = null, 
            $tipo = 0, 
            $idAviso = null)
    {
        $sql = $this->_db->select()->from($this->_name);
        if ($id_postulante) {
            $sql->where('id_postulante = ?', $id_postulante);
        }
        
        if ($id_empresa) {
            $sql->where('id_empresa = ?', $id_empresa);
        }
        
        if ($tipo) {
            $sql->where('tipo = ?', $tipo);        
        }
        
        if ($idAviso) {
            $sql->where('id_aviso = ?', $idAviso);        
        }
        
        $sql->order('fecha_busqueda DESC')->limit(1);        
        
        $result = $this->_db->fetchAll($sql);
        return $result;
        
    }

    public function getVisitasUltimoMes() 
    {
        $fechaIni = date('Y-m-d H:i:s',strtotime('-1 month'));
        $sql = $this->_db->select()
                ->from($this->_name, array('id' => 'id', 'id_Postulante' => 'id_Postulante',
                    'id_Empresa' => 'id_Empresa', "tipo" => "tipo","id_aviso" => 'id_aviso',"fecha_busqueda" => 'fecha_busqueda')
                )
                ->where('fecha_busqueda >= ?', $fechaIni);
        
        $result = $this->_db->fetchAll($sql);
        return $result;
    }
    
}