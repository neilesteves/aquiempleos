<?php

class Application_Model_Carrera extends App_Db_Table_Abstract {

    protected $_name = "carrera";
    protected $_empresaId = 1;
    
    const OTRO_CARRERA = 15;
    const OTROS = 'Otros';
    
    const CARRERA_APTITUS = 1;

    public function __construct($config = array()) {
        $this->_empresaId = Application_Model_Usuario::getEmpresaId();
        parent::__construct($config);
    }

    public function getCarreras() {
        $sql = $this->_db->select()
                ->from(array('c' => 'carrera'), array('c.id', 'c.nombre'))
                ->joinInner(array('ec' => 'empresa_carrera'), 'c.id = ec.id_carrera', array())
                ->group('c.id')
                ->order('c.nombre');
//        echo $sql;exit;
        if ($this->_empresaId === TRUE) {
            $result = $this->_db->fetchPairs($sql);
            return $result;
        }
        $sql->where('ec.id_empresa = ?', $this->_empresaId);
        $result = $this->_db->fetchPairs($sql);
        if (count($result) <= 0) {
            $sql->orWhere('ec.id_empresa = 1');
            $result = $this->_db->fetchPairs($sql);
        }
//        Zend_Debug::dump($sql->__toString());
        return $result;
    }

    /**
     * Retorna la lista de carreras disponibles.
     * 
     * @return array
     */
    public function getCarreras_old() {
        $cacheId = $this->_prefix . __FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()
                ->from($this->_name, array('id', 'nombre'))
                ->order('nombre');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
                $rs, $cacheId, array(), $this->_config->cache->Carrera->getCarreras
        );
        return $rs;
    }
    
    /**
     * Retorna la lista de carreras disponibles.
     * 
     * @return array
     */
    public function getListaCarreras() {
        $cacheId = $this->_prefix . __FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()
                ->from($this->_name, array('id', 'nombre'))
                ->order('nombre');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
                $rs, $cacheId, array(), $this->_config->cache->Carrera->getCarreras
        );
        return $rs;
    }
    
    /**
     * Compara un nombre de carrera, con los valores que se tienen en la base 
     * de datos, devolviendo el mas cercano
     * @param string $nombreCarrera
     * @return array
     */
    public function compararCarrera($nombreCarrera) 
    {
        $sql = "SELECT `id`, `nombre`, LEVENSHTEIN(`nombre`, ?) 
            AS comparacion FROM `carrera` 
            ORDER BY comparacion ASC LIMIT 1";        
        return $this->getAdapter()->fetchRow($sql, $nombreCarrera);
    }

    /**
     * Retorna la lista de carreras de acuerdo a un tipo de carrera.
     * 
     * @param int $idTipoCarrera
     */
    public function filtrarCarrera($idTipoCarrera) {
        //$cacheId = $this->_prefix . __FUNCTION__ . '_' . $idTipoCarrera . '_'. $this->_empresaId;
//        if ($this->_cache->test($cacheId)) {
//            //return $this->_cache->load($cacheId);
//        }
        $sql = $this->_db->select()
                ->from(array('c' => $this->_name), array('id', 'nombre'))
                ->joinInner(array('ec' => 'empresa_carrera'), 'c.id = ec.id_carrera', array())
                //->where('ec.id_empresa = ?', self::CARRERA_APTITUS)
                ->group('c.nombre')
                ->order('c.nombre asc');
        
        if ($this->_empresaId === TRUE) {
            //$sql->where('c.id = 15 OR c.id_tipo_carrera = ' . $idTipoCarrera);
            $sql->where('(c.id = 15) OR ((c.id_tipo_carrera = ' . $idTipoCarrera . ') AND (ec.id_empresa = ' . $this->_empresaId . '))');
            $result = $this->_db->fetchPairs($sql);
            return $result;
        }
        //$sql->where('((c.id_tipo_carrera = ' . $idTipoCarrera . ') AND (ec.id_empresa = ' . $this->_empresaId . '))');
        $sql->where('(c.id = 15) OR ((c.id_tipo_carrera = ' . $idTipoCarrera . ' and c.nombre<>"Otros") AND (ec.id_empresa = ' . $this->_empresaId . '))');
        $result = $this->_db->fetchPairs($sql);
        if (count($result) <= 1) {
            $sql->orWhere('((c.id_tipo_carrera = ' . $idTipoCarrera . ') AND (ec.id_empresa = 1))');
            $result = $this->_db->fetchPairs($sql);
        }//echo $sql;
        //var_dump($result);
        foreach($result as $k=>$v)
        {
            if(trim($v)=='Otros')
            {
                $rx = $k;
                unset($result[$k]);            
                break;
            }
        }
        $result[$rx] = 'Otros';
        
        //$this->_cache->save(
        //        $result, $cacheId, array(), $this->_config->cache->Carrera->filtrarCarrera
        //);
        return $result;
    }

    public function autocomplete($q, $subset, $nivel = null) {
        $sql = $this->getAdapter()->select()
                ->from($this->_name, array('id', 'nombre'))
                ->where('id_tipo_carrera = ?', $nivel)
                ->where(' UPPER(nombre) like ?', '%' . strtoupper($q) . '%')
                ->limit($this->_config->app->limitSuggest);
        return $this->getAdapter()->fetchPairs($sql);
    }

    public function getTipoCarreras() {
        $cacheId = $this->_prefix . __FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()
                ->from($this->_name, array('id', 'id_tipo_carrera'));
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
                $rs, $cacheId, array(), $this->_config->cache->Carrera->getTipoCarreras
        );
        return $rs;
    }

    public function getTipoCarreraXCarrera($idCarrera) {
        $cacheId = $this->_prefix . __FUNCTION__ . '_' . $idCarrera;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()
                ->from($this->_name, array('id_tipo_carrera'))
                ->where('id = ?', $idCarrera);
        $rs = $this->getAdapter()->fetchOne($sql);
        $this->_cache->save(
                $rs, $cacheId, array(), $this->_config->cache->Carrera->getTipoCarreras
        );
        return $rs;
    }

    /**
     * Retorna el nombre de la carrera segun el id que tiene
     * 
     * @param id $idCarrera
     */
    public function getCarreraById($idCarrera) {
        $sql = $this->select()
                ->from($this->_name, array('nombre'))
                ->where('id  = ?', $idCarrera);
        $rs = $this->getAdapter()->fetchOne($sql);
        return $rs;
    }

    public static function getCarrerasIds() {
        $obj = new Application_Model_Carrera();
        return $obj->getCarreras();
    }
    
    //Lista de carrera pa la búsqueda avanzada
    public function obtenerCarreraSearchAdvanced() {
        
        $cacheId = $this->_prefix . __FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        
        $sql = $this->select()->setIntegrityCheck(false)
                ->from(array('t' => 'tipo_carrera'), array('id','nombre',
                    'des_carrera' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT c.nombre ORDER BY c.slug ASC SEPARATOR ".")'),
                    'slug_carrera' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT c.slug ORDER BY c.slug ASC)')))
                ->joinInner(array('c' => $this->_name), 'c.id_tipo_carrera = t.id', null)
                ->joinInner(array('ec' => "empresa_carrera"),"ec.id_carrera = c.id",null)
                ->where('ec.id_empresa = ?', self::CARRERA_APTITUS)
                ->group('t.id')
                ->order('t.nombre asc');
                
        
       $rs = $this->getAdapter()->fetchAll($sql);
        
       $this->_cache->save(
                $rs, $cacheId, array(), $this->_config->cache->Carrera->obtenerCarreraSearchAdvanced
        );
        return $rs;
        
    }
    
    public function getCarrerasPadre() {
        $sql = $this->_db->select()
                ->from(array('c' => 'carrera'), array('c.id', 'c.nombre','c.id_tipo_carrera'))
                ->joinInner(array('ec' => 'empresa_carrera'), 'c.id = ec.id_carrera', array())
                ->group('c.id')
                ->order('c.nombre');
        if ($this->_empresaId === TRUE) {
            $result = $this->getAdapter()->fetchAll($sql);
            return $result;
        }
        $sql->where('ec.id_empresa = ?', $this->_empresaId);
        $result = $this->getAdapter()->fetchAll($sql);
        if (count($result) <= 0) {
            $sql->orWhere('ec.id_empresa = 1');
            $result = $this->getAdapter()->fetchAll($sql);
        }
        
        return $result;
    }
    
    
    /**
     * Retorna la carrera por el slug.
     * 
     * @return array
     */
    public function getCarreraBySlug($slug) 
    {
        $slug = preg_replace("/[^-A-Za-z0-9?! ]/","",$slug);
        $slug = trim($slug);
        $cacheId = $this->_prefix . __FUNCTION__ . '_' . str_replace('-', '_', $slug);
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->_db->select()
                ->from(array('c' => 'carrera'),array('nombre_carrera' => 'nombre', 'slug_carrera' => 'slug'))                
                ->where('c.slug  = ?', $slug);
        
        $rs = $this->getAdapter()->fetchRow($sql);
        
        $this->_cache->save(
                $rs, $cacheId, array(), $this->_config->cache->TipoCarrera->getTiposCarreras
        );
        return $rs;
    }
    
    public function getCarrerasName($nombre)
    {
        $sql = $this->_db->select()
                ->from('carrera', array('id','mostrar'=> 'nombre'))
                ->where('nombre like (?)','%'.$nombre.'%')
                ->order('nombre');
        $rs = $this->getAdapter()->fetchAll($sql);       
        return $rs;
    }
}
