<?php

class Application_Model_NivelPuesto extends App_Db_Table_Abstract
{

    protected $_name = "nivel_puesto";
    protected $_empresaId = 2;
    protected $_otrosId = 10;

    const GERENCIA = 11;

    public function __construct( $config = array() )
    {
        $this->_empresaId = Application_Model_Usuario::getEmpresaId();
        parent::__construct($config);
    }

    public function getNivel( $id )
    {
        $sql = $this->select()
                ->from(array('np' => 'nivel_puesto'), array('np.id', 'np.nombre'))
                ->group('np.id')
                ->order('np.nombre')
                ->where('np.id=?', $id);

        return $this->getAdapter()->fetchRow($sql);
    }

    public function getNiveles()
    {
        $sql = $this->_db->select()
                ->from(array('np' => 'nivel_puesto'), array('np.id', 'np.nombre'))
                ->joinInner(array('enp' => 'empresa_nivel_puesto'), 'np.id = enp.id_nivel_puesto', array())
                ->group('np.id')
                ->order('np.nombre');
        if($this->_empresaId === TRUE) {
            $sql->where('np.activo = 1');
            $result = $this->_db->fetchPairs($sql);
            return $result;
        }
        $sql->where('np.activo = 1 AND enp.id_empresa = ?', $this->_empresaId);

        $result = $this->_db->fetchPairs($sql);
        if(count($result) <= 0) {
            $sql->orWhere('np.activo = 1 AND enp.id_empresa = 2');
            $result = $this->_db->fetchPairs($sql);
        }
        if(isset($result[$this->_otrosId])) {
            $rx = $result[$this->_otrosId];
            unset($result[$this->_otrosId]);
            $result[$this->_otrosId] = $rx;
        }




        return $result;
    }

    public function getNivelesByArea( $id_area )
    {
        $sql = $this->_db->select()
                ->from(array('np' => 'nivel_puesto'), array('np.id', 'np.nombre'))
                ->joinInner(array('enp' => 'empresa_nivel_puesto'), 'np.id = enp.id_nivel_puesto', array())
                ->group('np.id')
                ->order('np.nombre');
        if($this->_empresaId === TRUE) {
            $sql->where('np.activo = 1');
            $result = $this->_db->fetchAll($sql);
            return $result;
        }
        //$sql->where('np.activo = 1 AND enp.id_empresa = ?', $this->_empresaId);
        $sql->where('np.activo = 1 AND enp.id_empresa = 2 AND np.id_area = ?', $id_area);

        $result = $this->_db->fetchAll($sql);

        return $result;
    }
    public function getNivelesByAreaParis( $id_area )
    {
        $sql = $this->_db->select()
                ->from(array('np' => 'nivel_puesto'), array('np.id', 'np.nombre'))
                ->joinInner(array('enp' => 'empresa_nivel_puesto'), 'np.id = enp.id_nivel_puesto', array())
                ->group('np.id')
                ->order('np.nombre');
        if($this->_empresaId === TRUE) {
            $sql->where('np.activo = 1');
            $result = $this->_db->fetchAll($sql);
            return $result;
        }
        //$sql->where('np.activo = 1 AND enp.id_empresa = ?', $this->_empresaId);
        $sql->where('np.activo = 1 AND enp.id_empresa = 2 AND np.id_area = ?', $id_area);
      //  echo $sql;exit;
        $result = $this->_db->fetchPairs($sql);

        return $result;
    }
    public function getNivelesByAreaSelect( $id_area )
    {
        $sql = $this->_db->select()
                ->from(array('np' => 'nivel_puesto'), array('np.id', 'np.nombre'))
                ->joinInner(array('enp' => 'empresa_nivel_puesto'), 'np.id = enp.id_nivel_puesto', array())
                ->group('np.id')
                ->order('np.nombre');
        if($this->_empresaId === TRUE) {
            $sql->where('np.activo = 1');
            $result = $this->_db->fetchPairs($sql);
            return $result;
        }
        //$sql->where('np.activo = 1 AND enp.id_empresa = ?', $this->_empresaId);
        $sql->where('np.activo = 1 AND enp.id_empresa = 2 AND np.id_area = ?', $id_area);

        $result = $this->_db->fetchPairs($sql);

        return $result;
    }

    public static function getNivelesPuestosIds()
    {
        $obj = new Application_Model_NivelPuesto();
        return $obj->getNiveles();
    }

    /**
     * Lista todos los niveles de puesto disonible en la base de datos.
     *
     * @return array
     */
    public function getNiveles_old()
    {
        $cacheId = $this->_prefix . __FUNCTION__;
        if($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()
                ->from($this->_name, array('id', 'nombre'))
                ->order('nombre')
                ->where('activo = 1');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
                $rs, $cacheId, array(), $this->_config->cache->NivelPuesto->getNiveles
        );
        return $rs;
    }

    public function getNivelesToRegistro()
    {
        $cacheId = $this->_prefix . __FUNCTION__;
        if($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()
                ->from($this->_name, array('id', 'nombre', 'slug', 'id_area'))
                ->order('nombre')
                ->where('activo = ?', 1);
        $rs = $this->getAdapter()->fetchAll($sql);
        $this->_cache->save(
                $rs, $cacheId, array(), $this->_config->cache->NivelPuesto->getNiveles
        );
        return $rs;
    }

    public function listadoNivelesBuscadorBuscaMas( $nivelJSON )
    {

        $arrayNivel1 = array();
        $arrayNivel2 = array();
        $dataNivel1 = $nivelJSON;
        $dataNivel2 = $nivelJSON;

        arsort($dataNivel1);
        ksort($dataNivel2);

        $contador = 0;
        foreach ($dataNivel1 as $key => $value) {
            $dataNivel = $this->fetchRow('slug = "' . $key . '"');
            if($dataNivel != null) {
                $arrayNivel1[$contador]['ind'] = $dataNivel['id'];
                $arrayNivel1[$contador]['cant'] = $value;
                $arrayNivel1[$contador]['slug'] = $key;
                $arrayNivel1[$contador]['msg'] = $dataNivel['nombre'];
                $contador ++;
            }
        }

        $contador = 0;
        foreach ($dataNivel2 as $key => $value) {
            $dataNivel = $this->fetchRow('slug = "' . $key . '"');
            if($dataNivel != null) {

                $arrayNivel2[$contador]['ind'] = $dataNivel['id'];
                $arrayNivel2[$contador]['cant'] = $value;
                $arrayNivel2[$contador]['slug'] = $key;
                $arrayNivel2[$contador]['msg'] = $dataNivel['nombre'];
                $contador ++;
            }
        }

        $data[0] = $arrayNivel1;
        $data[1] = $arrayNivel2;

        return $data;
    }

}
