<?php

class Application_Model_CategoriaPostulacion extends App_Db_Table_Abstract
{
    protected $_name  = "categoria_postulacion";
    protected $_model = "CategoriaPostulacion";

    public function getCategoriaPostulacion($idEmpresa, $cache = false)
    {
        /* */
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__."_".$idEmpresa;
        if ($this->_cache->test($cacheId) && $cache) {
            return $this->_cache->load($cacheId);
        }
        $sql    = $this->getAdapter()->select()
            ->from(
                array('cp' => $this->_name),
                array('nombre' => 'cp.nombre',
                'id' => 'cp.id')
            )
            ->where("cp.id_empresa=?", $idEmpresa)
            ->order("cp.orden ASC")
            ->group("cp.orden");
        $result = $this->getAdapter()->fetchAll($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getNombreCategoriaPost($idEmpresa, $idCategPostula)
    {
        $sql    = $this->getAdapter()->select()
                ->from(
                    array('cp' => $this->_name), array('nombre' => 'cp.nombre')
                )
                ->where("cp.id_empresa=?", $idEmpresa)
                ->where("cp.id = ?", $idCategPostula)->group('cp.nombre');
        $result = $this->getAdapter()->fetchRow($sql);
        return $result;
    }

    public function getNumeroCategoriasPostulaciones($idAnuncioWeb, $idEmpresa,
                                                     $cache = false)
    {
        //$cache=false;
        /* $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
          $cacheId = $this->_model.'_'.__FUNCTION__."_".$idAnuncioWeb."_".$idEmpresa;
          if ($this->_cache->test($cacheId) && $cache) {
          return $this->_cache->load($cacheId);
          } */
        $sql = $this->getAdapter()->select()
            ->from(
                array("p" => "postulacion"),
                array("id" => new Zend_Db_Expr("IFNULL(p.id_categoria_postulacion,-1)"),
                "n" => new Zend_Db_Expr("COUNT(p.id_postulante)")
                )
            )
            ->where("p.id_anuncio_web=".$idAnuncioWeb)
            ->where("p.descartado=0")
            ->where("p.activo =?",
                Application_Model_Postulacion::POSTULACION_ACTIVA)
            ->group("p.id_categoria_postulacion")
            ->order("p.id_categoria_postulacion ASC");

        $result    = $this->getAdapter()->fetchPairs($sql);
        $sqldos    = $this->getAdapter()->select()
            ->from(
                array("cp" => "categoria_postulacion"), array("id" => "cp.id")
            )
            ->where("cp.id_empresa=".$idEmpresa)
            ->order("cp.orden ASC");
        $resultdos = $this->getAdapter()->fetchAssoc($sqldos);

        $resultdos[-1] = array("id" => "-1");

        $r = $resultdos;
        foreach ($resultdos as $index => $item) {
            foreach ($result as $indexdos => $itemdos)
                if ($item["id"] == $indexdos)
                        $r[$index]["n"] = @$itemdos == null ? 0 : $itemdos;
        }
        $result = $r;

        //$this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function tieneCatPostulacion($id)
    {

        $sql = $this->select()->from($this->_name)
            ->where('id_empresa = ?', $id);

        return $this->fetchAll($sql);
    }
}