<?php

/**
 * Description of EmpresaProgramaComputo
 *
 * @author josue
 */
class Application_Model_EmpresaProgramaComputo extends App_Db_Table_Abstract {

    protected $_name = "empresa_programa_computo";
    
    public function getEmpresaProgramasComputo($empresaId = 1) {
//        $cacheId = $this->_prefix . __FUNCTION__;
//        if ($this->_cache->test($cacheId)) {
//            return $this->_cache->load($cacheId);
//        }
        $sql = $this->_db->select()
                ->from(array('pc' => 'programa_computo'), array('pc.id', 'pc.nombre'))
                ->joinInner(array('epc' => $this->_name), 'pc.id = epc.id_programa_computo', array())
                ->where('id_empresa = ?', $empresaId)
                ->order('pc.nombre');
        $result = $this->_db->fetchPairs($sql);

        if (count($result) <= 0) {
            $sql->orWhere('id_empresa = 1');
            $result = $this->_db->fetchPairs($sql);
        }
//        $this->_cache->save(
//                $tipos, $cacheId, array(), $this->_config->cache->NivelEstudio->getNiveles
//        );
        return $result;
    }

    public static function getEmpresaProgramasComputoIds() {
        $empresaId = Application_Model_Usuario::getEmpresaId();
        $obj = new Application_Model_EmpresaProgramaComputo();
        return $obj->getEmpresaProgramasComputo($empresaId);
    }

}

?>
