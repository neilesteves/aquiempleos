<?php

class Application_Model_Area extends App_Db_Table_Abstract {

    protected $_name = "area";
    protected $_empresaId = 1;
    protected $_otrosId = 26;

    public function __construct($config = array()) {
        $this->_empresaId = Application_Model_Usuario::getEmpresaId();
        parent::__construct($config);
    }

    public function getAreas() {
        $sql = $this->_db->select()
                ->from(array('a' => 'area'), array('a.id', 'a.nombre'))
                ->joinInner(array('ea' => 'empresa_area'), 'a.id = ea.id_area', array())
                ->group('a.id')
                ->order('a.nombre');
        if ($this->_empresaId === TRUE) {
            $result = $this->_db->fetchPairs($sql);
            return $result;
        }
        //$sql->where('ea.id_empresa = ?', $this->_empresaId);
        $sql->where('ea.id_empresa = ?', 2);
        $result = $this->_db->fetchPairs($sql);
        if (count($result) <= 0) {
            $sql2 = $this->_db->select()
                ->from(array('a' => 'area'), array('a.id', 'a.nombre'))
                ->joinInner(array('ea' => 'empresa_area'), 'a.id = ea.id_area', array())
                ->where('a.estado=?',0)
                ->group('a.id')
                ->order('a.nombre');            
            $sql2->where('ea.id_empresa = ?', 2);
            $result = $this->_db->fetchPairs($sql2);
        }
        /*$rx = $result[$this->_otrosId];
        unset($result[$this->_otrosId]);
        $result[$this->_otrosId] = $rx;*/

        return $result;
    }
    
    public function getAreasJJc($id) {
        $sql = $this->_db->select()
                ->from(array('a' => 'area'), array('a.id', 'a.nombre'))
                ->joinInner(array('ea' => 'empresa_area'), 'a.id = ea.id_area', array())
                ->group('a.id')
                ->order('a.nombre');
        if ($id === TRUE) {
            $result = $this->_db->fetchPairs($sql);
            return $result;
        }
        $sql->where('ea.id_empresa = ?', $id);
        $result = $this->_db->fetchPairs($sql);
        return $result;
    }
    
    public function getAreasEmpJJc($id) {
        $sql = $this->_db->select()
                ->from(array('a' => 'area'), array('a.id', 'a.nombre'))
                ->joinInner(array('ea' => 'empresa_area'), 'a.id = ea.id_area', array())
                ->group('a.id')
                ->order('a.nombre');    
        $sql->where('ea.id_empresa = ?', $id);
        $result = $this->_db->fetchPairs($sql);
        return $result;
    }

    public function getAreasAviso() {
        $sql = $this->_db->select()
                ->from(array('a' => 'area'), array('a.id', 'a.nombre'))
                ->joinInner(array('ea' => 'empresa_area'), 'a.id = ea.id_area', array())
                ->where('a.estado=?',1)
                ->group('a.id')
                ->order('a.nombre');
        if ($this->_empresaId === TRUE) {
            $result = $this->_db->fetchPairs($sql);
            return $result;
        }
        
        $sql->where('ea.id_empresa = ?', $this->_empresaId);
       
        $result = $this->_db->fetchPairs($sql);
        if (count($result) <= 0) {
            $sql2 = $this->_db->select()
                ->from(array('a' => 'area'), array('a.id', 'a.nombre'))
                ->where('a.estado=?',1)
                ->group('a.id')
                ->order('a.nombre');            
          
            $result = $this->_db->fetchPairs($sql2);
        }

        return $result;
    }
    
    public static function getAreasNew($idArea){
        $dataAreajjc=  $this->getAreasJJc($idArea);
        if($dataAreajjc){
            return $idArea;
        }
        $sql2 = $this->_db->select()
            ->from(array('a' => 'area'), array('a.id', 'a.nombre'))
            ->where('a.id=?',$id)
            ->group('a.id')
            ->order('a.nombre');
        
        $result = $this->_db->fetchPairs($sql2);
    }

    public function getAreasAvisoAdmin(){
        $sql2 = $this->_db->select()
                ->from(array('a' => 'area'), array('a.id', 'a.nombre'))
                ->where('a.estado=?',1)
                ->group('a.id')
                ->order('a.nombre');        
        $result = $this->_db->fetchPairs($sql2);
        return $result;
    }

    public static function getAreasIds() {
        $obj = new Application_Model_Area();
        return $obj->getAreas();
    }

    /**
     * Retorna la lista de areas disponibles en un puesto.
     * 
     * @return array
     */
    public function getAreas_old() {
        $cacheId = $this->_prefix . __FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()
                ->from($this->_name, array('id', 'nombre'))
                ->order('nombre');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
                $rs, $cacheId, array(), $this->_config->cache->Area->getAreas
        );
        return $rs;
    }
    
    
    /**
     * Retorna la lista de areas disponibles en un puesto.
     * 
     * @return array
     */
    public function getAreasToRegistro() 
    {
        $cacheId = $this->_prefix . __FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()
                ->from($this->_name, array('id', 'nombre','slug'))
                ->where('estado = ?', 1)
                ->order('nombre');
        $rs = $this->getAdapter()->fetchAll($sql);
        $this->_cache->save(
                $rs, $cacheId, 
                array(), $this->_config->cache->Area->getAreas
        );
        
        return $rs;
    }

    /**
     * Retorna la lista de areas disponibles en un puesto.
     * 
     * @return array
     */
    public function getAreasFeed() {
        $cacheId = $this->_prefix . __FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()
                ->from($this->_name, array('id', 'nombre', 'slug'))
                ->where('contador_anuncios>0')
                ->order('nombre');
        $rs = $this->getAdapter()->fetchAll($sql);
        $this->_cache->save(
                $rs, $cacheId, array(), $this->_config->cache->Area->getAreas
        );
        return $rs;
    }

    public function listadoAreaBuscadorBuscaMas($areasJSON) {
        $arrayAreas1 = array();
        $arrayAreas2 = array();
        $dataArea1 = $areasJSON;
        $dataArea2 = $areasJSON;

        arsort($dataArea1);
        ksort($dataArea2);

        $contador = 0;
        foreach ($dataArea1 as $key => $value) {
            $dataArea = $this->fetchRow('slug = "' . $key . '"');
            if ($dataArea != null) {
                $arrayAreas1[$contador]['ind'] = $dataArea['id'];
                $arrayAreas1[$contador]['cant'] = $value;
                $arrayAreas1[$contador]['slug'] = $key;
                $arrayAreas1[$contador]['msg'] = $dataArea['nombre'];
                $contador ++;
            }
        }

        $contador = 0;
        foreach ($dataArea2 as $key => $value) {
            $dataArea = $this->fetchRow('slug = "' . $key . '"');
            if ($dataArea != null) {

                $arrayAreas2[$contador]['ind'] = $dataArea['id'];
                $arrayAreas2[$contador]['cant'] = $value;
                $arrayAreas2[$contador]['slug'] = $key;
                $arrayAreas2[$contador]['msg'] = $dataArea['nombre'];
                $contador ++;
            }
        }

        $data[0] = $arrayAreas1;
        $data[1] = $arrayAreas2;

        return $data;
    }
    
    public function getAreasSlug() {
        $sql2 = $this->_db->select()
            ->from(array('a' => 'area'), array('a.slug', 'a.nombre'))
            ->where('a.estado=?',1)
            ->order('a.nombre');
        $result = $this->_db->fetchPairs($sql2);
        return $result;
    }

}
