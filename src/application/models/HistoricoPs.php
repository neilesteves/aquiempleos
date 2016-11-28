<?php

class Application_Model_HistoricoPs extends App_Db_Table_Abstract
{
    protected $_name = "historico_ps";

    const ESTADO_POSTULACION    = 'Postulación';
    const ESTADO_PRESELECCION   = 'Pre-Seleccionados';
    const ESTADO_SELECCIONADO   = 'Seleccionados';
    const ESTADO_FINALISTA      = 'Finalistas';
    const ESTADO_DESCARTAR      = 'Descartar';
    const ESTADO_RESTITUIR      = 'Restituir';
    const ESTADO_BLOQUEO        = 'Bloqueado';
    const ESTADO_DESBLOQUEO     = 'Desbloqueado';
    const ESTADO_SINSELECCIONAR = 'Sin Seleccionar';
    
    const EVENTO_BLOQUEO        =   'bloqueo';
    const EVENTO_DESBLOQUEO     =   'desbloqueo';
    const EVENTO_POSTULACION    =   'postulacion'; 
    const EVENTO_RESTITUIR      =   'restituir';
    
    public function getHistoricoPostulacion($idPostulacion) 
    {
        
        $sql = $this->getAdapter()->select()
            ->from($this->_name, array('descripcion','fecha_hora'))
            ->where('id_postulacion = ?', $idPostulacion);
        $r = $this->getAdapter()->fetchAll($sql);
        return $r;
    }
    
    public function registrar($postulacionId, $tipo, $descripcion)
    {
        $date = new Zend_Date; 
        
        $registro = array();
        $registro["id_postulacion"] = $postulacionId;
        $registro["evento"]         = $tipo;
        $registro["fecha_hora"]     = $date->get('YYYY-MM-dd HH:mm:ss');
        $registro["descripcion"]    = $descripcion;
        
        $this->insert($registro);
    }
    
    public function registarBloqueo($postulacion_id)
    {
        $date = new Zend_Date; 
        
        $registro = array();
        $registro["id_postulacion"] = $postulacion_id;
        $registro["evento"] = self::EVENTO_BLOQUEO;
        $registro["fecha_hora"] = $date->get('YYYY-MM-dd HH:mm:ss');
        $registro["descripcion"] = self::ESTADO_BLOQUEO;
        
        $this->insert($registro);
    }
    
    public function registarDesbloqueo($postulacionId)
    {
        $date = new Zend_Date; 
        
        $registro = array();
        $registro["id_postulacion"] = $postulacionId;
        $registro["evento"] = self::EVENTO_DESBLOQUEO;
        $registro["fecha_hora"] = $date->get('YYYY-MM-dd HH:mm:ss');
        $registro["descripcion"] = self::ESTADO_DESBLOQUEO;
        
        $this->insert($registro);
    }
}