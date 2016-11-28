<?php

class Application_Model_AdecsysContingenciaPerfil extends App_Db_Table_Abstract
{
    protected $_name = "adecsys_contingencia_perfil";    
    
    public function registrar($perfilId)
    {
        $fecha  = new Zend_Date;
        $data   = array();
        $data['id_perfil']     = $perfilId;
        $data['fecha_registro'] = $fecha->get('YYYY-MM-dd HH:mm:ss');
        $this->insert($data);
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
    
    public function obtenerPorPerfil($perfilId)
    {
        return $this->fetchRow($this->select()
            ->where('id_perfil =?', $perfilId));
    }
}