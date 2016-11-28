<?php

/**
 * Description of EmpresaPuesto
 *
 * @author josue
 */
class Application_Model_EmpresaPuesto extends App_Db_Table_Abstract {

    protected $_name = "empresa_puesto";

    const TIPO_PROFESIONAL = 'profesional';
    const TIPO_OFICIO = 'oficio';
    const OTROS_PUESTO_ID = '1292';
    const OTROS_PUESTO_NAME = 'Otros';

    public function getPuestos($empresaId = 1) {
        $sql = $this->_db->select()
//                ->from(array('p' => 'puesto'), array('p.id', 'LOWER(p.nombre)'))
                ->from(array('p' => 'puesto'), array('p.id', 'nombre' => 'CONCAT(UCASE(LEFT(p.nombre,1)),LCASE(SUBSTRING(p.nombre,2)))'))
                ->joinInner(array('ep' => $this->_name), 'p.id = ep.id_puesto', array())
                ->where('ep.id_empresa = ?', $empresaId)
                ->order('p.nombre');
        $result = $this->_db->fetchPairs($sql);
        if (count($result) <= 0) {
            $sql->orWhere('ep.id_empresa = 1');
            $result = $this->_db->fetchPairs($sql);
        }
        return $result;
    }

    public static function getPuestosIds(){
        $empresaId = Application_Model_Usuario::getEmpresaId();
        $obj = new Application_Model_EmpresaPuesto();
        return $obj->getPuestos($empresaId);
    }
}

?>
