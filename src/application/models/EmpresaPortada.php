<?php

class Application_Model_EmpresaPortada extends App_Db_Table_Abstract
{
    protected $_name = "empresa";
    protected $_model = 'Empresa';

    public function quitarEmpresaPortada($empresaId)
    {
        $result = $this->update(
            array('portada' => '0'),
            $this->getAdapter()->quoteInto(' id = ?', $empresaId)
        );
        return $result;
    }

    public function agregarEmpresaPortada($empresaId)
    {
        $result = $this->update(
            array('portada' => '1'),
            $this->getAdapter()->quoteInto(' id = ?', $empresaId)
        );
        return $result;
    }

    public function getEmpresasPortadas($action =true)
    {
        $config = Zend_Registry::get('config');        
        $cacheEt = $config->cache->{$this->_model}->{__FUNCTION__};
       // var_dump($cacheEt);
        //$cacheEt = '10';
        $cacheId = $this->_model .'_'.__FUNCTION__;
      //  echo"<br>";
        //var_dump($cacheId);
//        if ($action) {
            if ($this->_cache->test($cacheId)) {
                return $this->_cache->load($cacheId);
            }

            $sql = $this->getAdapter()->select()
                ->from(
                    array('e' => 'empresa'),
                    array(
                        'id' => 'id',
                        'rs' => 'razon_social',
                        'logo' => 'logo'
                    )
                )
                ->where('portada = 1');
            //echo $sql;
            //echo $sql->assemble($sql);         exit;
            $result = $this->getAdapter()->fetchAll($sql);
            //var_dump($result);
            $cache = Zend_Registry::get('cache');
            $resultT = $cache->save($result, $cacheId, array(), $cacheEt);
          //  var_dump($resultT);
        //} else {

        //}
        return $result;
    }
}