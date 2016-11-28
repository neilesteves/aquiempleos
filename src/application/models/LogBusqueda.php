<?php

class Application_Model_LogBusqueda extends App_Db_Table_Abstract
{
    protected $_name = "busqueda_log_actualizacion";
    
    const TIPO_BUSCADOR_APTITUS = 'busqueda general';
    const TIPO_BUSCADOR_PROCESO = 'busqueda proceso';
    
    public function getLogBusquedaXIdEmpresa($data)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name)
            ->where('id_empresa = ?', $data['id_empresa'])
            ->where('tipo_busqueda = ?', $data['tipo_busqueda'])
            ->where('tipo_filtro = ?', $data['tipo_filtro'])
            ->where('tipo_opcion_id = ?', $data['tipo_opcion_id'])
            ->order('id desc')
            ->limit('1');
            
        return $this->getAdapter()->fetchRow($sql);
    }
}
