<?php

class Application_Model_EmpresaNivelPuesto extends App_Db_Table_Abstract {

    protected $_name = "empresa_nivel_puesto";

    public function getNivelesPuestos($empresaId) {
        $sql = $this->_db->select()
                ->from(array('np' => 'nivel_puesto'), array('np.id', 'np.nombre'))
                ->joinInner(array('enp' => 'empresa_nivel_puesto'), 'np.id = enp.id_nivel_puesto', array())
                ->where('np.activo = 1 AND enp.id_empresa = ?', $empresaId)
                ->order('np.nombre');
        $niveles = $this->_db->fetchPairs($sql);
        if (count($niveles) <= 0) {
            $sql->orWhere('enp.id_empresa = 1 AND np.activo = 1');
            $niveles = $this->_db->fetchPairs($sql);
        }
        return $niveles;
    }

    public static function getNivelesPuestosIds(){
        $empresaId = Application_Model_Usuario::getEmpresaId();
        $obj = new Application_Model_EmpresaNivelPuesto();
        return $obj->getNivelesPuestos($empresaId);
    }

}