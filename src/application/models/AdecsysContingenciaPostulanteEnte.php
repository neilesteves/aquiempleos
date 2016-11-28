<?php

class Application_Model_AdecsysContingenciaPostulanteEnte extends App_Db_Table_Abstract
{
    protected $_name = "adecsys_contingencia_postulante_ente";    
    
    public function registrar($idPostulante)
    {
        $fecha  = new Zend_Date;
        $data   = array();
        $data['id_postulante'] = $idPostulante;
        $data['fecha_registro'] = $fecha->get('YYYY-MM-dd HH:mm:ss');
        $this->insert($data);
    }
    
    public function quitar($id)
    {
        $where = $this->getAdapter()->quoteInto('id =?', (int)$id);        
        return $this->delete($where);
    }
    
    public function quitarPorPostulante($postulanteId)
    {
        $where = $this->getAdapter()->quoteInto('id_postulante =?', 
                (int)$postulanteId);        
        return $this->delete($where);
    }
    
    public function obtenerPorPostulante($postulanteId)
    {
        return $this->fetchRow($this->select()
            ->where('id_postulante =?', $postulanteId));
    }
}