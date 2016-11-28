<?php

class Application_Model_Puesto extends App_Db_Table_Abstract {

    protected $_name = "puesto";
    protected $_empresaId = 1;

    const TIPO_PROFESIONAL = 'profesional';
    const TIPO_OFICIO = 'oficio';
    const OTROS_PUESTO_ID = '1292';
    const OTROS_NIVEL_PUESTO_ID = '10';
    const OTROS_PUESTO_NAME = 'OTROS';
    
    const APTITUS = 1;

    public function __construct($config = array()) {
        $this->_empresaId = Application_Model_Usuario::getEmpresaId();
        parent::__construct($config);
    }

    public function getPuestos() {
//        $lower = ($this->_empresaId == 10310) ? 'LOWER(p.nombre)' : 'p.nombre';
        $sql = $this->_db->select()
//                ->from(array('p' => 'puesto'), array('p.id', 'LOWER(p.nombre)'))
                ->from(array(
                    'p' => 'puesto'
                    ), array(
                    'p.id', 'nombre' => new Zend_Db_Expr('CONCAT(UCASE(LEFT(p.nombre,1)),LCASE(SUBSTRING(p.nombre,2)))')
                  ))
                ->joinInner(array('ep' => 'empresa_puesto'), 'p.id = ep.id_puesto', array())
                ->group('p.id')
                ->order('p.nombre');
        if ($this->_empresaId === TRUE) {
            $result = $this->_db->fetchPairs($sql);
            return $result;
        }
        $sql->where('ep.id_empresa = ?', $this->_empresaId);
        $result = $this->_db->fetchPairs($sql);
        if (count($result) <= 0) {
            $sql->orWhere('ep.id_empresa = 1');
           
        }
         $result = $this->_db->fetchPairs($sql);
        // echo $sql;
        return $result;
    }
    
        public function getPuestosjjc() {
//        $lower = ($this->_empresaId == 10310) ? 'LOWER(p.nombre)' : 'p.nombre';
        $sql = $this->_db->select()
//                ->from(array('p' => 'puesto'), array('p.id', 'LOWER(p.nombre)'))
                ->from(array('p' => 'puesto'), array('p.id', 'nombre' => 'CONCAT(UCASE(LEFT(p.nombre,1)),LCASE(SUBSTRING(p.nombre,2)))'))
                ->joinInner(array('ep' => 'empresa_puesto'), 'p.id = ep.id_puesto', array())
                ->group('p.id')
                ->order('p.nombre');
        if ($this->_empresaId === TRUE) {
            $result = $this->_db->fetchPairs($sql);
            return $result;
        }
        $sql->where('ep.id_empresa = ?', 19044);
        $result = $this->_db->fetchPairs($sql);
        if (count($result) <= 0) {
            $sql->orWhere('ep.id_empresa = 1');
           
        }
         $result = $this->_db->fetchPairs($sql);
        return $result;
    }

    public static function getPuestosIds() {
        $obj = new Application_Model_Puesto();
        return $obj->getPuestos();
    }
      public static function getPuestosIdsJjc() {
        $obj = new Application_Model_Puesto();
        return $obj->getPuestosjjc();
    }
    public function getPuestos_old() {
        $sql = $this->select()
                ->from($this->_name, array('id', 'CONCAT(UPPER(LEFT(nombre,1)), SUBSTR(nombre,2))'))
                ->order('nombre ASC');
        $sql->where("adecsys_code IS NOT NULL");
        return $this->getAdapter()->fetchPairs($sql);
    }
    
    public function getPuestosSlug() {

        $sql = $this->_db->select()->from(array('np' => 'nivel_puesto'),array('label' => 'nombre','slug'))
                ->joinInner(array('enp' => 'empresa_nivel_puesto'),'enp.id_nivel_puesto = np.id', null)
                ->where('enp.id_empresa = ?', self::APTITUS)
                ->where('np.activo = ?', 1)->order('np.nombre');
        //echo $sql;
        return $this->getAdapter()->fetchAll($sql);
    }

}
