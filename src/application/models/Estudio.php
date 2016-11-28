<?php

class Application_Model_Estudio extends App_Db_Table_Abstract
{

    protected $_name = "estudio";

    CONST ESTUDIO_REGULAR = 0;
    CONST ESTUDIO_PRINCIPAL = 1;
    CONST SIN_ESTUDIOS = 1;
    CONST OTROS_ESTUDIOS = 9;

    public function delete($where)
    {
        $storage = Zend_Auth::getInstance()->getStorage()->read();
        if (isset($data['id_postulante'])) {
            $idpostulantetosestudios = 'Postulante_getTodosOtrosEstudios_' . $data['id_postulante'];
            if ($this->_cache->test($idpostulantetosestudios)) {
                $this->_cache->remove('Postulante_getTodosOtrosEstudios_' . $data['id_postulante']);
            }
            $idgetestudios = 'Postulante_getEstudios_' . $data['id_postulante'];
            if ($this->_cache->test($idgetestudios)) {
                $this->_cache->remove($idgetestudios);
            }
        }
        if (!isset($data['nombre'])) {
            $data['nombre'] = '';
        }
        if (!isset($data['inicio_mes'])) {
            $data['inicio_mes'] = 1;
        }
        if (!isset($data['inicio_ano'])) {
            $data['inicio_ano'] = 1;
        }
        if (!isset($data['pais_estudio'])) {
            $data['pais_estudio'] = 1;
        }
        if (!isset($data['interrumpidos'])) {
            $data['interrumpidos'] = 0;
        }
        if (!isset($data['en_curso'])) {
            $data['en_curso'] = 0;
        }
        return parent::delete($where);
    }

    public function insert(array $data)
    {
        if (isset($data['id_postulante'])) {
            $idpostulantetosestudios = 'Postulante_getTodosOtrosEstudios_' . $data['id_postulante'];
            if ($this->_cache->test($idpostulantetosestudios)) {
                $this->_cache->remove('Postulante_getTodosOtrosEstudios_' . $data['id_postulante']);
            }
            $idgetestudios = 'Postulante_getEstudios_' . $data['id_postulante'];
            if ($this->_cache->test($idgetestudios)) {
                $this->_cache->remove($idgetestudios);
            }
        }
        if (!isset($data['nombre'])) {
            $data['nombre'] = '';
        }
        if (!isset($data['inicio_mes'])) {
            $data['inicio_mes'] = 1;
        }
        if (!isset($data['inicio_ano'])) {
            $data['inicio_ano'] = 1;
        }
        if (!isset($data['pais_estudio'])) {
            $data['pais_estudio'] = 1;
        }
        if (!isset($data['interrumpidos'])) {
            $data['interrumpidos'] = 0;
        }
        if (!isset($data['en_curso'])) {
            $data['en_curso'] = 0;
        }

        return parent::insert($data);
    }

    public function update(array $data, $where)
    {
        if (isset($data['id_postulante'])) {
            $idpostulantetosestudios = 'Postulante_getTodosOtrosEstudios_' . $data['id_postulante'];
            if ($this->_cache->test($idpostulantetosestudios)) {
                $this->_cache->remove('Postulante_getTodosOtrosEstudios_' . $data['id_postulante']);
            }
            $idgetestudios = 'Postulante_getEstudios_' . $data['id_postulante'];
            if ($this->_cache->test($idgetestudios)) {
                $this->_cache->remove($idgetestudios);
            }
        }

        if (!isset($data['nombre'])) {
            $data['nombre'] = '';
        }
        if (!isset($data['inicio_mes'])) {
            $data['inicio_mes'] = 1;
        }
        if (!isset($data['inicio_ano'])) {
            $data['inicio_ano'] = 1;
        }
        if (!isset($data['pais_estudio'])) {
            $data['pais_estudio'] = 1;
        }
        if (!isset($data['interrumpidos'])) {
            $data['interrumpidos'] = 0;
        }
        if (!isset($data['en_curso'])) {
            $data['en_curso'] = 0;
        }
        return parent::update($data, $where);
    }

    public function getEstudios($idPostulante)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'e' => $this->_name), array(
                    'id_estudio' => 'id',
                    'id_nivel_estudio' => 'id_nivel_estudio',
                    'id_nivel_estudio_tipo' => 'id_nivel_estudio_tipo',
                    'id_institucion' => 'id_institucion',
                    'nombre' => 'otro_institucion',
                    'otro_estudio' => 'otro_estudio',
                    'pais_estudio' => 'pais_estudio',
                    'id_carrera' => 'id_carrera',
                    'id_tipo_carrera' => 'id_tipo_carrera',
                    'otro_carrera' => 'otro_carrera',
                    'colegiatura' => 'e.colegiatura',
                    'colegiatura_numero' => 'e.colegiatura_numero',
                    'inicio_mes' => 'inicio_mes',
                    'inicio_ano' => 'inicio_ano',
                    'fin_mes' => 'fin_mes',
                    'fin_ano' => 'fin_ano',
                    'en_curso' => 'en_curso'
                ))
                ->joinInner(array(
                    'ne' => 'nivel_estudio'), 'ne.id = e.id_nivel_estudio', array())
                ->where('id_postulante = ?', $idPostulante)
                ->where('id_nivel_estudio != ?', self::OTROS_ESTUDIOS)
                ->where('id_nivel_estudio != ?', self::SIN_ESTUDIOS)
                ->order('inicio_ano DESC')
                ->order('inicio_mes DESC');
        return $db->fetchAll($sql);
    }

    public function getNivelCarrera($idNivelEstudio, $idpostulante)
    {
        $adapter = $this->getAdapter();
        $sql = $adapter->select()->from(
                        array(
                    "e" => $this->_name), array(
                    "descripcion" => "c.nombre",
                    "otracarrera" => "e.otro_carrera"
                        )
                )
                ->joinLeft(
                        array(
                    "c" => "carrera"
                        ), "e.id_carrera = c.id", array()
                )
                ->where("e.id_nivel_estudio = ?", $idNivelEstudio)
                ->where("e.id_postulante = ?", $idpostulante);

        $result = $adapter->fetchRow($sql);
        return $result;
    }

    public function getLogPostulanteEstudioTotal($idPostulante)
    {
        if (is_numeric($idPostulante)) {
            $sql = $this->getAdapter()->select()
                    ->from($this->_name, array(
                        'id'
                    ))
                    ->where('id_postulante = ?', (int) $idPostulante)
                    ->where('id_nivel_estudio != 9')
                    ->limit(1);
            $res = $this->getAdapter()->fetchAll($sql);
            return (count($res) > 0 ? true : false);
        }
        return false;
    }

    public function obtenerEstudiosMayorPesoPorPostulante($postulanteId)
    {
        return $this->getAdapter()->fetchRow($this->getAdapter()->select()
                                ->from(array(
                                    'e' => 'estudio'), array(
                                    'e.id'))
                                ->joinInner(array(
                                    'ne' => 'nivel_estudio'), 'e.id_nivel_estudio = ne.id', array(
                                    'ne.peso'))
                                ->where('e.id_postulante =?', (int) $postulanteId)
                                ->order(array(
                                    'ne.peso DESC',
                                    'e.id DESC')));
    }

    public function actualizarEstudioPrincipal($postulanteId, $estudioId)
    {
        $data = array();
        $data['principal'] = self::ESTUDIO_PRINCIPAL;

        $this->actualizarEstudiosARegularPorPostulante($postulanteId);

        $this->update($data, 'id_postulante = ' . (int) $postulanteId . ' and id = ' . (int) $estudioId);
    }

    public function actualizarEstudiosARegularPorPostulante($postulanteId)
    {
        $data = array();
        $data['principal'] = self::ESTUDIO_REGULAR;

        $this->update($data, 'id_postulante = ' . (int) $postulanteId);
    }

    public function obtenerEstudioPrincipalPorPostulante(
    $postulanteId, $columnas = array())
    {
        $columnas = $this->setCols($columnas);

        return $this->fetchRow($this->select()
                                ->from($this->_name, $columnas)
                                ->where('id_postulante =?', (int) $postulanteId)
                                ->where('principal =?', self::ESTUDIO_PRINCIPAL));
    }

    public function getOtrosEstudios($idPostulante)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'e' => $this->_name), array(
                    'id_estudio' => 'id',
                    'id_nivel_estudio' => 'id_nivel_estudio',
                    'id_nivel_estudio_tipo' => 'id_nivel_estudio_tipo',
                    'id_institucion' => 'id_institucion',
                    'nombre' => 'otro_institucion',
                    'otro_estudio' => 'otro_estudio',
                    'pais_estudio' => 'pais_estudio',
                    'id_carrera' => 'id_carrera',
                    'id_tipo_carrera' => 'id_tipo_carrera',
                    'otro_carrera' => 'otro_carrera',
                    'colegiatura' => 'e.colegiatura',
                    'colegiatura_numero' => 'e.colegiatura_numero',
                    'inicio_mes' => 'inicio_mes',
                    'inicio_ano' => 'inicio_ano',
                    'fin_mes' => 'fin_mes',
                    'fin_ano' => 'fin_ano',
                    'en_curso' => 'en_curso'
                ))
                ->joinInner(array(
                    'ne' => 'nivel_estudio'), 'ne.id = e.id_nivel_estudio', array())
                ->where('id_postulante = ?', $idPostulante)
                ->where('id_nivel_estudio = ?', 9)
                ->order('inicio_ano DESC')
                ->order('inicio_mes DESC');
        return $db->fetchAll($sql);
    }

    public function obtenerMejorEstudio($postulanteId)
    {
        $adapter = $this->getAdapter();
        $postulanteId = $adapter->quote($postulanteId);
        $sqlE = "SELECT 
        IFNULL(CONCAT_WS('/',ne.nombre,IF(ne.id<4,NULL,net.nombre)),'') AS mejor_nivel_estudio,
        IFNULL(IF(ne.id<4,NULL,IF(car.nombre = 'Otros',es.otro_carrera,car.nombre)),'') AS mejor_carrera,
        IFNULL(IF(es.nombre!='',es.nombre,es.otro_institucion),'') AS institucion
        FROM  estudio es 
        INNER JOIN nivel_estudio ne ON ne.id = es.id_nivel_estudio
        LEFT JOIN nivel_estudio net ON net.id = es.id_nivel_estudio_tipo
        LEFT JOIN carrera car ON car.id = es.id_carrera
        WHERE es.id_postulante = $postulanteId
        ORDER BY ne.peso DESC,net.peso DESC
        LIMIT 1";
        $stmp = $adapter->query($sqlE);
        $stmp->execute();
        return $stmp->fetch(Zend_Db::FETCH_ASSOC);
    }

    public function getEstudiosNuevo($idPostulante)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'e' => $this->_name), array(
                    'id_estudio' => 'id',
                    'nombre' => 'otro_institucion',
                    'otro_carrera' => 'otro_carrera',
                    'inicio_mes' => 'inicio_mes',
                    'inicio_ano' => 'inicio_ano',
                    'fin_mes' => 'fin_mes',
                    'fin_ano' => 'fin_ano',
                    'en_curso' => 'en_curso',
                    'id_tipo_carrera' => 'id_tipo_carrera',
                    'id_carrera' => 'id_carrera',
                    'id_nivel_estudio' => 'id_nivel_estudio',
                    'id_nivel_estudio_tipo' => 'id_nivel_estudio_tipo'
                ))
                ->joinInner(array(
                    'ne' => 'nivel_estudio'), 'ne.id = e.id_nivel_estudio', array(
                    'grado' => 'ne.nombre'))
                ->joinLeft(array(
                    'net' => 'nivel_estudio'), 'net.id = e.id_nivel_estudio_tipo', array(
                    'estado' => 'net.nombre'))
                ->joinLeft(array(
                    'c' => 'carrera'), 'c.id = e.id_carrera', array(
                    'carrera' => 'c.nombre'))
                ->where('id_postulante = ?', $idPostulante)
                ->where('id_nivel_estudio != ?', Application_Model_Estudio::OTROS_ESTUDIOS)
                ->where('id_nivel_estudio != ?', Application_Model_Estudio::SIN_ESTUDIOS)
                ->order('inicio_ano DESC')
                ->order('inicio_mes DESC');
        return $db->fetchAll($sql);
    }

    /**
     * Retorna los datos de un estudio
     * @param int $id Id del estudio
     * @return array
     */
    public function getEstudioXId($id)
    {
        $sql = $this->getAdapter()->select()
                ->from(array(
                    'e' => $this->_name), array(
                    '*'))
                ->where('e.id = ? ', $id);
        return ($this->getAdapter()->fetchAll($sql));
    }

    public function getLogPostulanteOtroEstudioTotal($idPostulante)
    {
        $sql = $this->getAdapter()->select()
                ->from($this->_name, array(
                    'num' => 'id',
                ))
                ->where('id_postulante = ?', (int) $idPostulante)
                ->where('id_nivel_estudio = 9')
                ->limit(1);
        $res = $this->getAdapter()->fetchAll($sql);
        return (count($res) > 0) ? true : false;
    }

}
