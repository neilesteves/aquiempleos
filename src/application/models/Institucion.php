<?php

class Application_Model_Institucion extends App_Db_Table_Abstract
{
    protected $_name = "institucion";

    /**
     * Lista de instituciones en la base de datos
     * 
     * @return array
     */
    public function getInstituciones()
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()
                ->from($this->_name, array('id', 'nombre'))
                ->order('nombre ASC');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
            $rs, 
            $cacheId, 
            array(), 
            $this->_config->cache->Institucion->getInstituciones
        );
        return $rs;
    }

    /**
     * Compara un nombre de carrera, con los valores que se tienen en la base 
     * de datos, devolviendo el mas cercano
     * 
     * @param string $nombreInstitucion
     */
    public function compararInstitucion($nombreInstitucion)
    {
        $sql = "SELECT `id`, `nombre`, 
            LEVENSHTEIN(UPPER( TRIM(`nombre`)), UPPER(TRIM(?))) 
            AS comparacion FROM `institucion` 
            ORDER BY comparacion ASC LIMIT 1";
        return $this->getAdapter()->fetchRow($sql, $nombreInstitucion);
    }
    
    public function autocomplete($q, $subset, $nivel = null)
    {

        if (isset($nivel)) {
            $nivelEstudio = new Application_Model_NivelEstudio();
            $nivelLista = $nivelEstudio->getDetalleNivelEstudio($nivel);
                
            if ($nivelLista[$nivel] == '1') {
                $nivel = 'instituto';
            } elseif ($nivelLista[$nivel] == '2') {
                $nivel = 'universidad';
            }
        }
        $subsets = array(
            'prueba' => '1=1',
            'test2' => 'id < 10',
        );
      
        $sql = $this->getAdapter()->select()
            ->from($this->_name, array('id', 'nombre'))
            ->where(' UPPER(nombre) like ?', '%'.strtoupper($q).'%')
            ->where('tipo = ?', $nivel)
            ->limit($this->_config->app->limitSuggest);
    if (!is_null($subset)) {
        if (!array_key_exists($subset, $subsets)) {
            throw new Zend_Exception('SUBSET inválido');
        }
        $sql = $sql->where($subsets[$subset]);
    }
            
        return $this->getAdapter()->fetchPairs($sql);
    }
    
    public function getInstitucionesName($nombre, $limit = null)
    {
        $sql = $this->_db->select()
                ->from('institucion', array('id','mostrar'=> 'nombre'))
                ->where('nombre like (?)','%'.$nombre.'%')
                ->order('nombre');
        if ($limit) {
            $sql->limit($limit);
        }
        
        $rs = $this->getAdapter()->fetchAll($sql);       
        return $rs;
    }
}