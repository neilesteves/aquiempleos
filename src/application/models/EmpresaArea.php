<?php

/**
 * Description of EmpresaArea
 *
 * @author josue
 */
class Application_Model_EmpresaArea extends App_Db_Table_Abstract {

    protected $_name = "empresa_area";

    public function getEmpresaAreas($empresaId) {
        $sql = $this->_db->select()
                ->from(array('a' => 'area'), array('a.id', 'a.nombre'))
                ->joinInner(array('ea' => $this->_name), 'a.id = ea.id_area', array())
                ->where('ea.id_empresa = ?', $empresaId)
                ->order('a.nombre');
        $puestos = $this->_db->fetchPairs($sql);
        if (count($puestos) <= 0) {
            $sql->orWhere('ea.id_empresa = 1');
            $puestos = $this->_db->fetchPairs($sql);
        }
        return$puestos;
    }

    public static function getAreasIds() {
        $empresaId = Application_Model_Usuario::getEmpresaId();
        $obj = new Application_Model_EmpresaArea();
        return $obj->getEmpresaAreas($empresaId);
    }

}