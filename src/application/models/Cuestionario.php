<?php

class Application_Model_Cuestionario extends App_Db_Table_Abstract
{
    protected $_name = 'cuestionario';
    private $_model;
    
    public function __construct() 
    {
        parent::__construct();
        $cparts = explode('_', __CLASS__);
        $this->_model = $cparts[2];
    }
    
    public function insert(array $data) 
    {
        if (isset($data['id_anuncio_web']) && !empty($data['id_anuncio_web'])) {
            $idCache = $this->_model . '_' . __FUNCTION__ . '_' . $data['id_anuncio_web'];
            @$this->_cache->remove($idCache);
        }
        return parent::insert($data);
    }
    
    public function delete($where) 
    {
        if (isset($where['id_anuncio_web']) && !empty($where['id_anuncio_web'])) {
            $idCache = $this->_model . '_' . __FUNCTION__ . '_' . $where['id_anuncio_web'];
            @$this->_cache->remove($idCache);
        }
        return parent::delete($where);
    }

    public function update(array $data, $where) {
        if (isset($data['id_anuncio_web']) && !empty($data['id_anuncio_web'])) {
            $idCache = $this->_model . '_' . __FUNCTION__ . '_' . $data['id_anuncio_web'];
            @$this->_cache->remove($idCache);
        }
        return parent::update($data, $where);
    }
    
    
        
    public function getPreguntasByAnuncioWeb($idAnuncioWeb)
    {        
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $idAnuncioWeb;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->getAdapter()->select()
            ->from(
                array('c' => 'cuestionario'),
                array(
                    'pregunta' => 'p.pregunta',
                    'id_pregunta' => 'p.id'
                )
            )
            ->join(
                array('p' => 'pregunta'), 
                'p.id_cuestionario= c.id'
            )
            ->where('p.estado = ?',1)
            ->where('c.id_anuncio_web = ?', $idAnuncioWeb);
        $valor = $this->getAdapter()->fetchAll($sql);
        if ($valor === false) {
            return null;
        }
        $this->_cache->save($valor, $cacheId, array(), $cacheEt);        
        return $valor;
    }
    
    /**
     * Retorna el ID del cuestionario de un determinado anuncio web
     * 
     * @param int $idAnuncioWeb
     */
    public function getCuestionarioByAnuncioWeb($idAnuncioWeb)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('c' => 'cuestionario'),
                array(
                    'cuestionario_id' => 'c.id'
                )
            )
            ->where('c.id_anuncio_web = ?', $idAnuncioWeb)
            ->order('c.id desc');
        return $this->getAdapter()->fetchOne($sql);
    }
}