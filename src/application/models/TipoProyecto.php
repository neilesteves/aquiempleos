<?php

class Application_Model_TipoProyecto extends App_Db_Table_Abstract {

    protected $_name = "tipo_proyecto";
    protected $_empresaId = 1;

    public function __construct($config = array()) {
        $this->_empresaId = Application_Model_Usuario::getEmpresaId();
        parent::__construct($config);
    }

    /**
     * Retorna la lista de tipos de carreras disponibles.
     * 
     * @return array
     */
    public function getTipoProyectos() {
        $sql = $this->_db->select()
                ->from(array('tp' => 'tipo_proyecto'), array('tp.id', 'tp.nombre'))
                ->joinInner(array('etp' => 'empresa_tipo_proyecto'), 'tp.id = etp.id_tipo_proyecto', array())
                ->group('tp.id')
                ->order('tp.nombre');
        if ($this->_empresaId === TRUE) {
            $result = $this->_db->fetchPairs($sql);
            return $result;
        }
        $sql->where('id_empresa = ?', $this->_empresaId);
        $result = $this->_db->fetchPairs($sql);

        if (count($result) <= 0) {
            $sql->orWhere('id_empresa = 1');
            $result = $this->_db->fetchPairs($sql);
        }
        return $result;
    }

}
