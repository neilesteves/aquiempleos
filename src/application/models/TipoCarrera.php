<?php

class Application_Model_TipoCarrera extends App_Db_Table_Abstract {

    protected $_name = "tipo_carrera";
    protected $_empresaId = 1;
    protected $_otrosId = 16;
    
    const OTROS_TIPO_CARRERA = 16;
    const ACTIVO = 1;
    const INACTIVO = 0; 
    
    public function __construct($config = array()) {
        $this->_empresaId = Application_Model_Usuario::getEmpresaId();
        parent::__construct($config);
    }

    public function getTiposCarreras() {
        $sql = $this->_db->select()
                ->from(array('tc' => 'tipo_carrera'), array('id', 'nombre'))
                ->joinInner(array('etc' => 'empresa_tipo_carrera'), 'tc.id = etc.id_tipo_carrera', array())
                ->where('tc.active = ?', self::ACTIVO)
                ->order('nombre');
        $result = $this->_db->fetchPairs($sql);
//        echo $sql;exit;
//        echo $this->_empresaId;exit;
        if ($this->_empresaId === TRUE) {
            return $result;
        }
        $sql->where('etc.id_empresa = ?', $this->_empresaId);
        $result = $this->_db->fetchPairs($sql);
        if (count($result) <= 0) {
            $sql->orWhere('etc.id_empresa = 1 and tc.active = 1');
            $result = $this->_db->fetchPairs($sql);
        }
        $rx = $result[$this->_otrosId];
        unset($result[$this->_otrosId]);
        $result[$this->_otrosId] = $rx;
        
        return $result;
    }

    /**
     * Retorna la lista de tipos de carreras disponibles.
     * 
     * @return array
     */
    public function getTiposCarreras_old() {
        $cacheId = $this->_prefix . __FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()
                ->from($this->_name, array('id', 'nombre'))
                ->order('nombre');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
                $rs, $cacheId, array(), $this->_config->cache->TipoCarrera->getTiposCarreras
        );
        return $rs;
    }

    public static function getTiposCarrerasIds() {
        $obj = new Application_Model_TipoCarrera();
        return $obj->getTiposCarreras();
    }
    /**
     * Retorna el tipo de carrera por el slug de la carrera.
     * 
     * @return array
     */
    public function getTipoCarreraBySlug($slug) {
        $cacheId = $this->_prefix . __FUNCTION__ . '_' . str_replace('-', '_', $slug);
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->_db->select()
                ->from(array('t' => $this->_name))
                ->joinInner(array('c' => 'carrera'), 'c.id_tipo_carrera = t.id', array())
                ->where('c.slug  = ?', $slug);
        $rs = $this->getAdapter()->fetchRow($sql);
        $this->_cache->save(
                $rs, $cacheId, array(), $this->_config->cache->TipoCarrera->getTiposCarreras
        );
        return $rs;
    }

}
