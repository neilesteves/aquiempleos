<?php

class Application_Model_AdecsysContingenciaEnte extends App_Db_Table_Abstract
{
    protected $_name = "adecsys_contingencia_ente";    
    
    public function registrar($idEmpresa)
    {
        $fecha  = new Zend_Date;
        $data   = array();
        $data['id_empresa']     = $idEmpresa;
        $data['fecha_registro'] = $fecha->get('YYYY-MM-dd HH:mm:ss');
        $this->insert($data);
    }
    
    public function quitar($id)
    {
        $where = $this->getAdapter()->quoteInto('id =?', (int)$id);        
        return $this->delete($where);
    }
    
    public function quitarPorEmpresa($empresaId)
    {
        $where = $this->getAdapter()->quoteInto('id_empresa =?', 
                (int)$empresaId);        
        return $this->delete($where);
    }
    
    public function obtenerPorEmpresa($empresaId)
    {
        return $this->fetchRow($this->select()
            ->where('id_empresa =?', $empresaId));
    }
}