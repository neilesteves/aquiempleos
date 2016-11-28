<?php

class Application_Model_BolsaCvPostulante extends App_Db_Table_Abstract {

    protected $_name = "bolsa_cv_postulante";
    protected $_model = "";

    public function __construct() {
        parent::__construct();
        $cparts = explode('_', __CLASS__);
        $this->_model = $cparts[2];
    }

    public function agregarPostulante($idPostulante, $idsBolsasAgregar) {
        foreach ($idsBolsasAgregar as $idBolsa) {
            if (!$this->existePostulanteEnGrupo($idBolsa, $idPostulante)) {
                $this->insert(
                        array('id_bolsa_cv' => $idBolsa, 'id_postulante' => $idPostulante)
                );
                
             
            }
        }
    }

    public function eliminar($idBolsaCvPostulante) {
        $this->delete($this->getAdapter()->quoteInto('id = ?', $idBolsaCvPostulante));
    }

    public function eliminarPostulanteDeGrupo($idPostulante, $idGrupo) {
        $sql = $this->getAdapter()->select()
                ->from(
                        array($this->_name), array('id' => 'id')
                )
                ->where('id_postulante = ?', $idPostulante)
                ->where('id_bolsa_cv = ?', $idGrupo);

        $idBolsaCvPostulante = $this->getAdapter()->fetchOne($sql);

        if ($idBolsaCvPostulante != null && $idBolsaCvPostulante != "") {
            $this->delete($this->getAdapter()->quoteInto('id = ?', $idBolsaCvPostulante));
        }
    }

    public function cambiarBolsaCV($idBolsaOrigen, $idsBolsaDestino) {
        $postulantesOrigen = $this->postulantesPorGrupo($idBolsaOrigen);
        $postulantesDestino = $this->postulantesPorGrupo($idsBolsaDestino);

        foreach ($postulantesOrigen as $pOrigen) {
            $esta = false;
            foreach ($postulantesDestino as $pDestino) {
                if ($pOrigen["idPostulante"] == $pDestino["idPostulante"]) {
                    $esta = true;
                }
            }

            if ($esta) {
                $this->eliminar($pOrigen["id"]);
            }
        }

        $this->update(
                array('id_bolsa_cv' => $idsBolsaDestino), $this->getAdapter()->quoteInto('id_bolsa_cv = ?', $idBolsaOrigen)
        );
    }

    public function postulantesPorGrupo($idGrupo, $cache = TRUE) {
//        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
//        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $idGrupo;
//        if ($this->_cache->test($cacheId) && $cache) {
//            return $this->_cache->load($cacheId);
//        }
        $sql = $this->getAdapter()->select()
                ->from(
                        array($this->_name), array('id' => 'id',
                    'idPostulante' => 'id_postulante')
                )
                ->where('id_bolsa_cv = ?', $idGrupo);
        $result = $this->getAdapter()->fetchAll($sql);
//        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function existePostulanteEnGrupo($idGrupo, $idPostulante) {
        $sql = $this->getAdapter()->select()
                ->from(
                        array($this->_name), array('cant' => 'count(id)')
                )
                ->where('id_bolsa_cv = ?', $idGrupo)
                ->where('id_postulante = ?', $idPostulante);

        $cant = $this->getAdapter()->fetchOne($sql);

        if ($cant > 0) {
            return true;
        }

        return false;
    }

    public function listarDataPostulantes($postulantes = array()) {
        if (empty($postulantes)) {
            return array();
        }
        $m_postulante = new Application_Model_Postulante();
        $string_id = $string_in = '';
        foreach ($postulantes as $pos) {
            $string_in .= ',' . $pos['idPostulante'];
            $string_id .= $pos['idPostulante'];
        }
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . md5($string_id);  
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->_db->select()
                ->from(array('p' => 'postulante'), array(
                    //'id' => 'p.id',
                    'DNI' => 'p.num_doc',
                    'Nombres' => 'p.nombres',
                    'Apellidos' => 'CONCAT(p.apellido_paterno," ",p.apellido_materno)',
                    'Sexo' => 'sexo',
                    'Telefono celular' => 'p.celular',
                    'Telefono fijo' => 'p.telefono',
                    'Lugar de Residencia' => 'ubi.display_name',
                    'Edad' => new Zend_Db_Expr('CAST((FLOOR(DATEDIFF(CURDATE(), p.fecha_nac) / 365)) AS CHAR (3))'),
                    'Nivel estudio' => 'p.mejor_nivel_estudio'/*new Zend_Db_Expr("'Ninguno'")*/,
                    'Carrera' => 'p.mejor_carrera'/*new Zend_Db_Expr("'Ninguno'")*/,
                    'Nombre de la Institución' => 'p.institucion'/*new Zend_Db_Expr("'Ninguno'")*/,
                    'Nivel de Ingles' => /*new Zend_Db_Expr('(SELECT di.`nivel_hablar`
                        FROM dominio_idioma AS di
                        WHERE di.`id_idioma` = "en" AND di.id_postulante = p.id 
                        ORDER BY di.`id` DESC
                        LIMIT 1)')*/ 'di.nivel_hablar'
                ))
                //->joinInner(array('u' => 'usuario'), 'u.id = p.id_usuario', array())
                ->joinLeft(array('ubi' => 'ubigeo'), 'ubi.id = p.id_ubigeo', array())
                ->joinLeft(array('di' => 'dominio_idioma'), 'di.id_idioma = "en" AND di.id_postulante = p.id', array())
                ->where('p.id IN(' . substr($string_in, 1) . ')');
        $result = $this->_db->fetchAll($sql);

        /*foreach ($result as $ind => $post) {
            $mejorEstudio = $m_postulante->getMejorNivelEstudio($post['id']);
            if ($mejorEstudio != null) {
                $result[$ind]["Nivel estudio"] = $mejorEstudio["nivel_estudio"];
                $result[$ind]["Carrera"] = $mejorEstudio["carrera"];
                $result[$ind]["Nombre de la Institución"] = $mejorEstudio["otro_institucion"];
            }
            unset($result[$ind]['id']);
        }*/
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

}
