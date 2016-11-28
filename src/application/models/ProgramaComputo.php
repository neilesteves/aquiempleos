<?php

class Application_Model_ProgramaComputo extends App_Db_Table_Abstract {

    protected $_name = "programa_computo";
    protected $_empresaId = 1;

    public function __construct($config = array()) {
        $this->_empresaId = Application_Model_Usuario::getEmpresaId();
        parent::__construct($config);
    }

    public function getProgramasComputo() {
        $sql = $this->_db->select()
                ->from(array('pc' => 'programa_computo'), array('pc.id', 'pc.nombre'))
                ->joinInner(array('epc' => 'empresa_programa_computo'), 'pc.id = epc.id_programa_computo', array())
                ->group('pc.id')
                ->order('pc.nombre');
        if ($this->_empresaId === TRUE) {
            $result = $this->_db->fetchPairs($sql);
            return $result;
        }
        $sql->where('epc.id_empresa = ?', $this->_empresaId);
        $result = $this->_db->fetchPairs($sql);
        if (count($result) <= 0) {
            $sql->orWhere('epc.id_empresa = 1');
            $result = $this->_db->fetchPairs($sql);
        }
        return $result;
    }
    
    /**
     * Lista todos los programas de computo disponibles en la base de datos
     * 
     * @return array
     */
    public function getProgramas() {
        $cacheId = $this->_prefix . __FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $rs = $this->_config->enumeraciones->programas_computo->toArray();
        $this->_cache->save(
                $rs, $cacheId, array(), $this->_config->cache->ProgramaComputo->getProgramas
        );
        return $rs;
    }

    /**
     * Retorna en nombre del programa a partir del id.
     * 
     * @param int $idPrograma
     * @return string
     */
    public function findPrograma($idPrograma) {
        $lista = $this->_config->enumeraciones->programas_computo->toArray();
        foreach ($lista as $key => $value) {
            if ($key == $idPrograma) {
                return $value;
            }
        }
    }

    public function get() {
        return $this->getProgramas();
    }

    public static function getProgramasComputoIds() {
        $obj = new Application_Model_ProgramaComputo();
        return $obj->getProgramasComputo();
    }
}
