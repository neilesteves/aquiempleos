<?php

class Application_Model_NivelEstudio extends App_Db_Table_Abstract {

    protected $_name = "nivel_estudio";
    protected $_empresaId = 1;
    
    CONST OTRO_ESTUDIO = 9;
    CONST SIN_ESTUDIOS = 1;
    
    public static $_nivelColegiado = array(18);

    public function __construct($config = array()) {
        $this->_empresaId = Application_Model_Usuario::getEmpresaId();
        parent::__construct($config);
    }

    public function getNiveles() {
        $sql = $this->_db->select()
                ->from(array('ne' => 'nivel_estudio'), array('id', 'nombre'))
                ->joinInner(array('ene' => 'empresa_nivel_estudio'), 'ne.id = ene.id_nivel_estudio', array())
                ->where('ne.id <> ?', self::SIN_ESTUDIOS)
                ->order('peso DESC');
        
        $result = $this->_db->fetchPairs($sql);
        
        /*if ($this->_empresaId === TRUE) {
            return $result;
        }*/
        $sql->where('ene.id_empresa = ?', $this->_empresaId);
        $result = $this->_db->fetchPairs($sql);
        if (count($result) <= 0) {
            $sql->orWhere('ene.id_empresa = 1');
            $result = $this->_db->fetchPairs($sql);
        }
        return $result;
    }
    public function getNivelesApitus() {
        
        $cacheId = $this->_prefix . __FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        } 
        $sql = $this->_db->select()
                ->from(array('ne' => 'nivel_estudio'), array('id', 'nombre'))
                ->joinInner(array('ene' => 'empresa_nivel_estudio'), 'ne.id = ene.id_nivel_estudio', array())
                ->where('ne.id <> ?', self::SIN_ESTUDIOS)
                ->order('peso DESC');
         $sql->where('ene.id_empresa = ?', $this->_empresaId);
        $result = $this->_db->fetchPairs($sql);
         $this->_cache->save(
                $result, $cacheId, array(), $this->_config->cache->NivelEstudio->getNivelesApitus
        );
        /*if ($this->_empresaId === TRUE) {
            return $result;
        }*/
//        $sql->where('ene.id_empresa = ?', $this->_empresaId);
//        $result = $this->_db->fetchPairs($sql);
//        if (count($result) <= 0) {
//            $sql->orWhere('ene.id_empresa = 1');
//            $result = $this->_db->fetchPairs($sql);
//        }
        return $result;
    }
      public function getNivelesAptitus() {
        $sql = $this->_db->select()
                ->from(array('ne' => 'nivel_estudio'), array('id', 'nombre'))
                ->joinInner(array('ene' => 'empresa_nivel_estudio'), 'ne.id = ene.id_nivel_estudio', array())
                ->order('peso DESC');
       $sql->where('ene.id_empresa = ?', 1);
        $result = $this->_db->fetchPairs($sql);       
        return $result;
    }
        public function getNivelesAviso() {
        $sql = $this->_db->select()
                ->from(array('ne' => 'nivel_estudio'), array('id', 'nombre'))
                ->joinInner(array('ene' => 'empresa_nivel_estudio'), 'ne.id = ene.id_nivel_estudio', array())
                ->where('ene.id_empresa = 1')->order('peso DESC');
        $result = $this->_db->fetchPairs($sql);
        /*if ($this->_empresaId === TRUE) {
            return $result;
        }
        $sql->where('ene.id_empresa = ?', $this->_empresaId);
        $result = $this->_db->fetchPairs($sql);
        if (count($result) <= 0) {
            $sql->orWhere('ene.id_empresa = 1');
            $result = $this->_db->fetchPairs($sql);
        }*/
        return $result;
    }

    /**
     * Lista todos los niveles de estudio disponibles en la base de datos
     * 
     * @return array
     */
    public function getNiveles_old() {
        $cacheId = $this->_prefix . __FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()
                ->from($this->_name, array('id', 'nombre'))
                ->order('nombre');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
                $rs, $cacheId, array(), $this->_config->cache->NivelEstudio->getNiveles
        );
        return $rs;
    }

    /**
     * Lista todos los niveles de estudio con detalle disponibles en la base de datos
     * 
     * @return array
     */
    public function getNivelesSinDetalle() {
        $cacheId = $this->_prefix . __FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()->from($this->_name, array('id', 'nombre'))
                ->where('detalle = ?', 0);
        $rs = $this->getAdapter()->fetchAll($sql);
//        Zend_Debug::dump($sql->__toString());
        $this->_cache->save(
                $rs, $cacheId, array(), $this->_config->cache->NivelEstudio->getNiveles
        );
        return $rs;
    }

    /**
     * Compara un nombre del nivel de estudio, con los valores que se tienen en la base 
     * de datos, devolviendo el mas cercano
     * @param string $nombreCarrera
     * @return array
     */
    public function compararNivelEstudio($nivelEstudio) {
        $sql = "SELECT `id`, `nombre`, LEVENSHTEIN(`nombre`, ?) 
            AS comparacion FROM `nivel_estudio` 
            ORDER BY comparacion ASC LIMIT 1";
        return $this->getAdapter()->fetchRow($sql, $nivelEstudio);
    }

    /**
     * Retorna el detalle de un nivel de estudio de terminado, si es nulo 
     * devuelve un array con todos los datos
     * @param int $idNivelEstudio
     */
    public function getDetalleNivelEstudio($idNivelEstudio = null) {

        $sql = $this->select()->from($this->_name, array('id', 'detalle'));
        if (isset($idNivelEstudio)) {
            $sql->where('id = ?', $idNivelEstudio);
        }
        return $this->getAdapter()->fetchPairs($sql);
    }
    /**
     * Lista todos los subniveles de estudio 
     * 
     * @return array
     */
    public function getSubNiveles($padre = 0) {
        if(in_array($padre,array(1,2,3)))
            return array();        
        $sql = $this->_db->select()
                ->from(array('ne' => 'nivel_estudio'), array('id', 'nombre'))
                ->where("ne.padre != ''")
                ->order('peso DESC');
        if(!empty($padre))
            $sql->where("ne.padre LIKE ?", "%$padre%");
        $result = $this->_db->fetchPairs($sql);
        return $result;
    }

    /**
     * Lista todos los subniveles de estudio 
     * 
     * @return array
     */
    /*public function getSubNiveles($padre = 0) {
        $sql = $this->_db->select()
                ->from(array('ne' => 'nivel_estudio'), array('id', 'nombre'))
                ->where("ne.padre != ''")
                ->order('nombre');
        if(!empty($padre))
            $sql->where("ne.padre LIKE ?", "%$padre%");
        $result = $this->_db->fetchPairs($sql);
        return $result;
    }*/
    
    public function getSubNivelesPadre($padre = 0) 
    {
        if (in_array($padre,array(1,2,3))) {
            return array();        
        }
        
        $sql = $this->_db->select()
                ->from(array('ne' => 'nivel_estudio'), array('id', 'nombre','padre'))
                ->where("ne.padre != ''")
                ->where("ne.padre <> ?", 9)
                ->order('peso DESC');
        if (!empty($padre)) {
            $sql->where("ne.padre LIKE ?", "%$padre%");            
        }
        return $this->getAdapter()->fetchAll($sql);
    }
    
    
    
    public function getSubNivelesPadreSelect($padre) {
        if(in_array($padre,array(1,2,3)))
            return array();        
        $sql = $this->getAdapter()->select()
                ->from(array('ne' => 'nivel_estudio'), array('peso','id', 'nombre'))
                ->where("ne.padre != ''")
                ->where("ne.padre <> ?", 9)
                ->order(array('ne.peso desc','ne.nombre asc'));
        if(!empty($padre))
            $sql->where("ne.padre LIKE ?", "%$padre%");
        
        return $this->getAdapter()->fetchAll($sql);
    }
    
    /**
     * Retorna los tipos de otros estudios
     * @param int $idPadre Id del padre de los niveles de estudio
     * @return array
     */
    public function getTipoOtroEstudio($idPadre)
    {
        $cacheId = $this->_prefix . __FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()
                    ->from($this->_name, array('id', 'nombre'))
                    ->where('padre = ?', $idPadre);
         $rs= $this->getAdapter()->fetchPairs($sql);
         $this->_cache->save(
                $rs, $cacheId, array(), $this->_config->cache->NivelEstudio->getTipoOtroEstudio
        );
        return $rs;
    }
    
    
    public function getNombreNivelById($idNivel)
    {
        if ($idNivel) {
            $sql = $this->select()->from($this->_name, array(
                'nombre'
            ));
            $sql->where('id = ?', $idNivel);
            $res =  $this->getAdapter()->fetchOne($sql);            
            return $res;
        }
        return null;
        
        
    }
    
}
