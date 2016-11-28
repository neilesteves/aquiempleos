<?php

class Application_Model_AdecsysContingenciaAviso extends App_Db_Table_Abstract
{
    protected $_name = "adecsys_contingencia_aviso";    
    
    public function registrar($anuncioId)
    {
        $fecha  = new Zend_Date;
        $data   = array();
        $data['id_anuncio']     = $anuncioId;
        $data['fecha_registro'] = $fecha->get('YYYY-MM-dd HH:mm:ss');
        $this->insert($data);
    }
    
    public function quitar($id)
    {
        $where = $this->getAdapter()->quoteInto('id =?', (int)$id);        
        return $this->delete($where);
    }
    
    public function quitarPorAviso($anuncioId)
    {
        $where = $this->getAdapter()->quoteInto('id_anuncio =?', 
                (int)$anuncioId);        
        return $this->delete($where);
    }
    
    public function obtenerPorAviso($anuncioId)
    {
        return $this->fetchRow($this->select()
            ->where('id_anuncio =?', $anuncioId));
    }
}