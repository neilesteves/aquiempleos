<?php

class Application_Model_Postulante extends App_Db_Table_Abstract
{

    protected $_name = "postulante";
    public static $estadoCivil = array(
        'soltero' => 'Soltero(a)',
        'casado' => 'Casado(a)',
        'divorciado' => 'Divorciado(a)',
        'union_libre' => 'Unión Libre',
        'separado' => 'Separado(a)',
        'viudo' => 'Viudo(a)',        
    );
    public static $tipoDiscapacidad = array(
        '1' => 'Física',
        '2' => 'Sensorial',
        '3' => 'Mental',
        '4' => 'Intelectual'
    );

    const DESTACADO = 1;
    const NO_DESTACADO = 0;
    const CONFIDENCIALIDAD = 0;

    private $_model;

    public function __construct()
    {
        parent::__construct();
        $cparts = explode('_', __CLASS__);
        $this->_model = $cparts[2];
    }

  

    public function getPostulanteByUsarioId($id_usuario)
    {
        $sql = $this->_db->select()
                ->from(array(
                    'p' => $this->_name), array(
                    'p.*',
                    'idempresa' => 'ep.id_empresa'))
                ->joinLeft(array(
                    'ep' => 'empresa_postulante'), 'p.id = ep.id_postulante', array())
                ->where('p.id_usuario = ?', $id_usuario);
        return $this->_db->fetchRow($sql);
    }

    /**
     * Retorna el nombre y el slug de un postulante de acuerdo al ID de usuario
     * 
     * @param int $usuarioId
     */
    public function getSlugByUsuarioId($usuarioId)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, array(
                    'nombres',
                    'slug'))
                ->where('id_usuario = ?', $usuarioId);
        return $db->fetchRow($sql);
    }

    public function getPerfil($id)
    {
//        $cacheId = 'perfil_postulante_' . str_replace('-', '_', $id);
//        if ($this->_cache->test($cacheId)) {
//            return $this->_cache->load($cacheId);
//        } 
        $datospersonales = $this->getPostulantePerfil($id);
        if ($datospersonales == null) {
            return false;
        }
        $modelAreaInteres = new Application_Model_AreaInteres();

        $perfil = array(
            'postulante' => $datospersonales,
            'estudios' => $this->getEstudios($datospersonales['idpostulante']),
            'otrosEstudios' => $this->getOtrosEstudios($datospersonales['idpostulante']),
            'mejor_nivel_puesto' => $this->getMejorNivelEstudio(
                    $datospersonales['idpostulante']
            ),
            'experiencias' => $this->getExperiencias($datospersonales['idpostulante']),
            'idiomas' => $this->getIdiomas($datospersonales['idpostulante']),
            'programas' => $this->getprogramas($datospersonales['idpostulante']),
            'logros' => $this->getLogros($datospersonales['idpostulante']),
            'aptitudes' => $modelAreaInteres->obtenerAptitudesPostulante($datospersonales['idpostulante']),
        );
//            $this->_cache->save(
//                    $perfil, $cacheId, array(), $this->_config->cache->Postulante->getPerfil
//            );
        return $perfil;
//    }
    }

    public function getPerfilPostulante($id)
    {
//        $cacheId = 'get_perfil_postulante_' . str_replace('-', '_', $id);
//        if ($this->_cache->test($cacheId)) {
//            return $this->_cache->load($cacheId);
//        }
        $datospersonales = $this->getPostulantePerfil($id);
        $modelAreaInteres = new Application_Model_AreaInteres();
        if ($datospersonales == null) {
            return false;
        }
        $perfil = array(
            'postulante' => $datospersonales,
            'estudios' => $this->getEstudios($datospersonales['idpostulante']),
            'otrosEstudios' => $this->getOtrosEstudios($datospersonales['idpostulante']),
            'mejor_nivel_puesto' => $this->getMejorNivelEstudio(
                    $datospersonales['idpostulante']
            ),
            'experiencias' => $this->getExperiencias($datospersonales['idpostulante']),
            'idiomas' => $this->getIdiomasPostulante($datospersonales['idpostulante']),
            'programas' => $this->getprogramas($datospersonales['idpostulante']),
            'referencias' => $this->getReferenciasPostulante($datospersonales['idpostulante']),
            'hobbies' => array(),
            'logros' => $this->getLogros($datospersonales['idpostulante']),
            'aptitudes' => $modelAreaInteres->obtenerAptitudesPostulante($datospersonales['idpostulante']),
        );
//        $this->_cache->save(
//                    $perfil, $cacheId, array(), $this->_config->cache->Postulante->getItems
//        );
        return $perfil;
//    }
    }

    public function getPostulantePerfil($id)
    {
        $db = $this->getAdapter();

        $whereField = is_numeric($id) ? 'id' : 'slug';

        $sql = $db->select()
                ->from(array(
                    'p' => $this->_name), array(
                    'idpostulante' => 'p.id',
                    'ubicacion' => 'ubi.display_name',
                    'idpaisnac' => 'paisnac.id',
                    'paisnac' => 'paisnac.nombre',
                    'idpaisres' => 'paisres.id',
                    'paisres' => 'paisres.nombre',
                    'nombres' => 'p.nombres',
                    'apellidos' => new Zend_Db_Expr("TRIM(CONCAT(p.apellido_paterno,' ',p.apellido_materno))"),
                    'tipo_doc' => 'p.tipo_doc',
                    'num_doc' => 'p.num_doc',
                    'fecha_nac' => 'p.fecha_nac',
                    'estado_civil' => 'p.estado_civil',
                    'sexoMF' => 'p.sexo',
                    'telefono' => new Zend_Db_Expr("TRIM(CONCAT(' C. ',p.celular,'  T. ',p.telefono))"),
                    'path_foto' => 'p.path_foto',
                    'p.celular',
                    'fijo' => 'p.telefono',
                    'path_foto_uno' => 'p.path_foto1',
                    'path_foto_dos' => 'p.path_foto2',
                    'slug' => 'p.slug',
                    'website' => 'p.website',
                    'presentacion' => 'p.presentacion',
                    'path_cv' => 'p.path_cv',
                    'notif_leidas' => 'p.notif_leidas',
                    'notif_no_leidas' => 'p.notif_no_leidas',
                    'idusuario' => 'p.id_usuario',
                    'esConfidencial' => 'p.prefs_confidencialidad',
                    'ultima_actualizacion' => 'p.ultima_actualizacion',
                    'id_ubigeo' => 'p.id_ubigeo',
                    'mejor_nivel_estudio' => 'p.mejor_nivel_estudio',
                    'extranjero' => 'p.disponibilidad_provincia_extranjero',
                    'mejor_carrera' => 'p.mejor_carrera',
                    'facebook' => 'p.facebook',
                    'twitter' => 'p.twitter',
                    'destacado' => 'p.destacado',
                    'discapacidad' => 'p.discapacidad',
                    'conadis' => 'p.conadis_code'
                        )
                )
                ->joinLeft(array(
                    'ubi' => 'ubigeo'), 'ubi.id=p.id_ubigeo')
                ->joinLeft(array(
                    'paisnac' => 'ubigeo'), 'p.pais_nacionalidad = paisnac.id')
                ->joinLeft(array(
                    'paisres' => 'ubigeo'), 'p.pais_residencia = paisres.id')
                ->join(
                        array(
                    'u' => 'usuario'), 'u.id=p.id_usuario ', array(
                    'id_usuario' => 'u.id',
                    'email' => 'u.email')
                )
                ->where("p.$whereField = ?", $id);
        $rs = $db->fetchRow($sql);
        return $rs;
    }

    public function getPostulanteUsuario($idusuario)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(
                        array(
                    'p' => $this->_name), array(
                    'idpostulante' => 'p.id',
                    'ubicacion' => 'ubi.display_name',
                    'idpaisnac' => 'paisnac.id',
                    'paisnac' => 'paisnac.nombre',
                    'idpaisres' => 'paisres.id',
                    'paisres' => 'paisres.nombre',
                    'nombres' => 'p.nombres',
                    'apellidos' => 'p.apellidos',
                    'tipo_doc' => 'p.tipo_doc',
                    'num_doc' => 'p.num_doc',
                    'fecha_nac' => 'p.fecha_nac',
                    'estado_civil' => 'p.estado_civil',
                    'sexoMF' => 'p.sexo',
                    'telefono' => 'p.telefono',
                    'path_foto' => 'p.path_foto',
                    'path_foto_uno' => 'p.path_foto1',
                    'path_foto_dos' => 'p.path_foto2',
                    'slug' => 'p.slug',
                    'website' => 'p.website',
                    'presentacion' => 'p.presentacion',
                    'path_cv' => 'p.path_cv',
                    'notif_leidas' => 'p.notif_leidas',
                    'notif_no_leidas' => 'p.notif_no_leidas',
                    'idusuario' => 'p.id_usuario',
                    'ultima_actualizacion' => 'p.ultima_actualizacion',
                    'id_ubigeo' => 'p.id_ubigeo',
                    'mejor_nivel_estudio' => 'p.mejor_nivel_estudio',
                    'mejor_carrera' => 'p.mejor_carrera')
                )
                ->join(array(
                    'ubi' => 'ubigeo'), 'ubi.id=p.id_ubigeo')
                ->join(array(
                    'paisnac' => 'ubigeo'), 'p.pais_nacionalidad = paisnac.id')
                ->join(array(
                    'paisres' => 'ubigeo'), 'p.pais_residencia = paisres.id')
                ->join(
                        array(
                    'u' => 'usuario'), 'u.id=p.id_usuario ', array(
                    'id_usuario' => 'u.id',
                    'email' => 'u.email')
                )
                ->where("u.id = ?", $idusuario);
        $rs = $db->fetchRow($sql);
        return $rs;
    }

    public function getPostulante($id)
    {
        $db = $this->getAdapter();
        $whereField = is_numeric($id) ? 'id' : 'slug';
        $sql = $db->select()
                ->from(
                        array(
                    'p' => $this->_name), array(
                    'level' => 'u.level')
                )
                ->join(array(
                    'u' => 'ubigeo'), 'p.id_ubigeo = u.id')
                ->where("p.$whereField = ?", $id);
        $level = $db->fetchOne($sql);
        $empty = new Zend_Db_Expr("''");
        switch ($level) {
            case Application_Model_Ubigeo::NIVEL_PAIS:
                $extraFields = array(
                    'iddistrito' => $empty,
                    'distrito' => $empty,
                    'idprov' => $empty,
                    'provincia' => $empty,
                    'iddpto' => $empty,
                    'dpto' => $empty,
                    'idpaisres' => 'paisres.id',
                    'paisres' => 'paisres.nombre',
                );
                break;
            case Application_Model_Ubigeo::NIVEL_DEPARTAMENTO:
                $extraFields = array(
                    'iddistrito' => $empty,
                    'distrito' => $empty,
                    'idprov' => $empty,
                    'provincia' => $empty,
                    'iddpto' => 'dpto.id',
                    'dpto' => 'dpto.nombre',
                    'idpaisres' => 'paisres.id',
                    'paisres' => 'paisres.nombre',
                );
                break;
            case Application_Model_Ubigeo::NIVEL_PROVINCIA:
                $extraFields = array(
                    'iddistrito' => $empty,
                    'distrito' => $empty,
                    'idprov' => 'prov.id',
                    'provincia' => 'prov.nombre',
                    'iddpto' => 'dpto.id',
                    'dpto' => 'dpto.nombre',
                    'idpaisres' => 'paisres.id',
                    'paisres' => 'paisres.nombre',
                );
                break;
            case Application_Model_Ubigeo::NIVEL_DISTRITO:
                $extraFields = array(
                    'iddistrito' => 'dist.id',
                    'distrito' => 'dist.nombre',
                    'idprov' => 'prov.id',
                    'provincia' => 'prov.nombre',
                    'iddpto' => 'dpto.id',
                    'dpto' => 'dpto.nombre',
                    'idpaisres' => 'paisres.id',
                    'paisres' => 'paisres.nombre',
                );
                break;
        }

        $baseFields = array(
            'idpaisnac' => 'paisnac.id',
            'paisnac' => 'paisnac.nombre',
            'nombres' => 'p.nombres',
//            'apellidos' => 'p.apellidos',
            'apellido_paterno' => 'p.apellido_paterno',
            'apellido_materno' => 'p.apellido_materno',
            'tipo_doc' => 'p.tipo_doc',
            'num_doc' => 'p.num_doc',
            'fecha_nac' => 'p.fecha_nac',
            'estado_civil' => 'p.estado_civil',
            'sexoMF' => 'p.sexo',
            'telefono' => 'p.telefono',
            'celular' => 'p.celular',
            'path_foto' => 'p.path_foto',
            'path_foto_uno' => 'p.path_foto1',
            'path_foto_dos' => 'p.path_foto2',
            'slug' => 'p.slug',
            'website' => 'p.website',
            'presentacion' => 'p.presentacion',
            'path_cv' => 'p.path_cv',
            'idpostulante' => 'p.id',
            'notif_leidas' => 'p.notif_leidas',
            'notif_no_leidas' => 'p.notif_no_leidas',
            'idusuario' => 'p.id_usuario',
            'disponibilidad_provincia_extranjero',
            'facebook' => 'p.facebook',
            'twitter' => 'p.twitter',
            'pais_residencia' => 'p.pais_residencia',
            'id_ubigeo' => 'p.id_ubigeo',
            'destacado' => 'p.destacado',
            'conadis_code' => 'p.conadis_code',
            'discapacidad' => 'p.discapacidad'
        );

        $fields = array_merge($extraFields, $baseFields);

        $sql = $db->select()->from(array(
            'p' => $this->_name), $fields);

        if ($level == Application_Model_Ubigeo::NIVEL_DISTRITO) {
            $sql = $sql->join(array(
                'dist' => 'ubigeo'), 'dist.id=p.id_ubigeo');
            $sql = $sql->join(array(
                'prov' => 'ubigeo'), 'dist.padre = prov.id');
            $sql = $sql->join(array(
                'dpto' => 'ubigeo'), 'prov.padre = dpto.id');
            $sql = $sql->joinLeft(array(
                'paisres' => 'ubigeo'), 'dpto.padre = paisres.id');
        }
        if ($level == Application_Model_Ubigeo::NIVEL_PROVINCIA) {
            $sql = $sql->join(array(
                'prov' => 'ubigeo'), 'prov.id = p.id_ubigeo');
            $sql = $sql->join(array(
                'dpto' => 'ubigeo'), 'prov.padre = dpto.id');
            $sql = $sql->joinLeft(array(
                'paisres' => 'ubigeo'), 'dpto.padre = paisres.id');
        }
        if ($level == Application_Model_Ubigeo::NIVEL_DEPARTAMENTO) {
            $sql = $sql->join(array(
                'dpto' => 'ubigeo'), 'dpto.id = p.id_ubigeo');
            $sql = $sql->joinLeft(array(
                'paisres' => 'ubigeo'), 'dpto.padre = paisres.id');
        }
        if ($level == Application_Model_Ubigeo::NIVEL_PAIS) {
            $sql = $sql->joinLeft(array(
                'paisres' => 'ubigeo'), 'paisres.id = p.id_ubigeo');
        }
        $sql = $sql->joinLeft(array(
                    'paisnac' => 'ubigeo'), 'p.pais_nacionalidad = paisnac.id')
                ->join(
                        array(
                    'u' => 'usuario'), 'u.id=p.id_usuario ', array(
                    'id_usuario' => 'u.id',
                    'email' => 'u.email',
                    'activo' => 'u.activo')
                )
                ->where("p.$whereField = ?", $id);

        $rs = $db->fetchRow($sql);




        return $rs;
    }

    public function getPostulanteForPorcentaje($id)
    {
        $db = $this->getAdapter();
        $fields = array(
            'nombres' => 'p.nombres',
            'apellido_paterno' => 'p.apellido_paterno',
            'apellido_materno' => 'p.apellido_materno',
            'num_doc' => 'p.num_doc',
            'fecha_nac' => 'p.fecha_nac',
            'sexoMF' => 'p.sexo',
            'telefono' => 'p.telefono',
            'path_foto' => 'p.path_foto',
            'idpostulante' => 'p.id',
            'facebook' => 'p.facebook',
            'twitter' => 'p.twitter',
            'id_ubigeo' => 'p.id_ubigeo',
            'estado_civil' => 'p.estado_civil',
            'id_usuario' => 'p.id_usuario',
            'id' => 'p.id'
        );

        $sql = $db->select()
                ->from(array(
                    'p' => $this->_name
                        ), $fields)
                ->where('id = ? ', $id);
        $rs = $db->fetchRow($sql);
        return $rs;
    }

    public static function validacionDocumento($value)
    {
        $options = func_get_args();


        $tipoDocumento = substr($options[2], 0, 3);
        $id = $options[3];
        $o = new Application_Model_Postulante();
        $sql = $o->select()
                ->from('postulante', 'id')
                ->where('tipo_doc = ?', $tipoDocumento)
                ->where('num_doc = ?', $value)
                ->limit('1');
        if ($id) {
            $sql = $sql->where('id != ?', $id);
        }
        $sql = $sql->limit('1');

        $r = $o->getAdapter()->fetchOne($sql);
        return !(bool) $r;
    }

    public function getEstudios($id, $getCached = false)
    {
        if ($getCached) {
            $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
            $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $id;
            if ($this->_cache->test($cacheId)) {
                return $this->_cache->load($cacheId);
            }
        }


        $sql = $this->getAdapter()->select()
                ->from(array(
                    'e' => 'estudio'), array(
                    'institucion' => 'e.otro_institucion',
                    'actualmente' => 'e.en_curso',
                    'id_nivel_estudio',
                    'inicio_mes',
                    'inicio_ano',
                    'fin_mes',
                    'fin_ano',
                    'otro_estudio',
                    'otro_carrera',
                    'colegiatura_numero'
                ))
                ->joinleft(array(
                    'c' => 'carrera'), 'e.id_carrera = c.id', array(
                    'titulo' => 'c.nombre'))
                ->joinleft(array(
                    'tc' => 'tipo_carrera'), 'c.id_tipo_carrera = tc.id', array(
                    'tipo_carrera' => 'tc.nombre'))
                ->joinleft(array(
                    'ne' => 'nivel_estudio'), 'e.id_nivel_estudio = ne.id', array(
                    'nivel_nombre' => 'ne.nombre'
                ))
                ->joinleft(array(
                    'net' => 'nivel_estudio'), 'e.id_nivel_estudio_tipo = net.id', array(
                    'nivel_tipo_nombre' => 'net.nombre'
                ))
                ->where('id_postulante = ?', $id)
                //->where('en_curso = ?', 1)
                ->where('id_nivel_estudio != ?', 9)
                ->order('ne.peso DESC')
                ->order('net.peso DESC');
        $res = $this->getAdapter()->fetchAll($sql);
        foreach ($res as &$r) {
            if ($r['id_nivel_estudio'] == 9) {
                $r['titulo'] = $r['otro_estudio'];
            }
            if ($r['otro_carrera']) {
                $r['titulo'] = $r['otro_carrera'];
            }
        }

        if ($getCached) {
            $this->_cache->save($res, $cacheId, array(), $cacheEt);
        }


        return $res;
    }

    public function getExperiencias($id)
    {
        $sql = $this->_db->select()
                ->from(array(
                    'e' => 'experiencia'), array(
                    'experienciaId' => 'id',
                    'empresa' => 'e.otra_empresa',
                    'rubro' => new Zend_Db_Expr("COALESCE(e.otro_rubro,r.nombre)"),
                    'puesto' => 'e.otro_puesto',
                    'id_nivel_puesto' => 'e.id_nivel_puesto',
                    'id_area' => 'e.id_area',
                    'id_puesto' => 'e.id_puesto',
                    'lugar' => 'e.lugar',
                    'id_tipo_proyecto' => 'e.id_tipo_proyecto',
                    'tipo_proyecto' => 'tp.nombre',
                    'nombre_proyecto' => 'e.nombre_proyecto',
                    'costo_proyecto' => 'e.costo_proyecto',
                    'inicio_mes' => 'e.inicio_mes',
                    'inicio_ano' => 'e.inicio_ano',
                    'fin_mes' => 'e.fin_mes',
                    'fin_ano' => 'e.fin_ano',
                    'actualmente' => 'e.en_curso',
                    'comentarios' => 'e.comentarios',
                    'nombre_puesto' => 'p.nombre'
                ))
                ->joinInner(array(
                    'p' => 'puesto'), 'p.id = e.id_puesto', null)
                ->joinLeft(array(
                    'r' => 'rubro'), 'e.id_rubro = r.id')

//                ->joinInner(array('p'=>'puesto'),'e.id_puesto = p.id',array())
                ->joinInner(array(
                    'ep' => 'empresa_puesto'), 'e.id_puesto = ep.id_puesto', array())

//                ->joinInner(array('np'=>'nivel_puesto'),'e.id_nivel_puesto = np.id',array())
                ->joinInner(array(
                    'enp' => 'empresa_nivel_puesto'), 'e.id_nivel_puesto = enp.id_nivel_puesto', array())

//                ->joinInner(array('a'=>'area'),'e.id_area = a.id',array())
                ->joinInner(array(
                    'ea' => 'empresa_area'), 'e.id_area = ea.id_area', array())
                ->joinLeft(array(
                    'tp' => 'tipo_proyecto'), 'e.id_tipo_proyecto = tp.id', array())
                ->joinLeft(array(
                    'etp' => 'empresa_tipo_proyecto'), 'e.id_tipo_proyecto = etp.id_tipo_proyecto', array())

//                ->where('np.activo = 1')
                ->where('e.id_postulante = ?', $id)
//                ->where('ep.id_empresa = ?', $idEmpresa)
//                ->where('enp.id_empresa = ?', $idEmpresa)
//                ->where('ea.id_empresa = ?', $idEmpresa)
                ->group('e.id')
                ->order('inicio_ano DESC')
                ->order('inicio_mes DESC');
        $rs = $this->getAdapter()->fetchAll($sql);
        $refs = $this->getReferencias($id);
        $res = array();
        foreach ($rs as $key => $row) {
            $res[$key] = $row;
            if (isset($refs[$row['experienciaId']])) {
                $res[$key]['referencias'] = $refs[$row['experienciaId']];
            }
        }
        return $res;
    }

    public function getUltimaExperiencias($id)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $id;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->_db->select()
                ->from(array(
                    'e' => 'experiencia'), array(
                    'puesto' => 'e.otro_puesto'
                ))
                ->joinInner(array(
                    'p' => 'puesto'), 'p.id = e.id_puesto', array())
                ->where('e.id_postulante = ?', $id)
                ->group('e.id')
                ->order('inicio_ano DESC')
                ->order('inicio_mes DESC');
        $rs = $this->getAdapter()->fetchOne($sql);
        $this->_cache->save($rs, $cacheId, array(), $cacheEt);
        return $rs;
    }

    public function getReferencias($id)
    {
        $sql = $this->getAdapter()->select()
                ->from(
                        array(
                    'r' => 'referencia'), array(
                    'rid' => 'r.id',
                    'eid' => 'e.id',
                    'nombre',
                    'cargo',
                    'telefono',
                    'telefono2',
                    'email'
                        )
                )
                ->join(
                        array(
                    'e' => 'experiencia'), 'e.id = r.id_experiencia', array()
                )
                ->where('e.id_postulante = ?', $id);
        $rs = $this->getAdapter()->fetchAll($sql);
        $res = array();
        foreach ($rs as $row) {
            $res[$row['eid']][] = $row;
        }
        return $res;
    }

    public function getReferenciasPostulante($id)
    {
        $sql = $this->getAdapter()->select()
                ->from(
                        array(
                    'r' => 'referencia'), array(
                    'rid' => 'r.id',
                    'eid' => 'e.id',
                    'nombre',
                    'cargo',
                    'telefono',
                    'telefono2',
                    'email',
                    'empresa' => 'e.otra_empresa',
                    'puesto' => new Zend_Db_Expr("CASE p.nombre
                        WHEN concat('OTROS' ) THEN e.otro_puesto 
                        WHEN p.nombre THEN p.nombre END")
                        )
                )
                ->joinInner(
                        array(
                    'e' => 'experiencia'), 'e.id = r.id_experiencia', array()
                )->joinInner(
                        array(
                    'p' => 'puesto'), 'e.id_puesto = p.id', array()
                )
                ->where('e.id_postulante = ?', $id);
        $rs = $this->getAdapter()->fetchAll($sql);
        return $rs;
    }

    public function getIdiomas($id)
    {
        $sql = $this->getAdapter()->select()
                ->from(
                        array(
                    'di' => 'dominio_idioma'), array(
                    'idioma' => 'di.id_idioma',
                    'nivel' => 'nivel_hablar',
                    'di.id'
                        )
                )
                ->where('id_postulante = ?', $id);
        //echo $sql->assemble();exit;
        $rs = $this->getAdapter()->fetchAll($sql);
        return $rs;
    }

    public function getIdiomasPostulante($id)
    {
        $sql = $this->getAdapter()->select()
                ->from(
                        array(
                    'didm' => 'dominio_idioma'), array(
                    'id_dominioIdioma' => 'didm.id',
                    'id_idioma' => 'didm.id_idioma',
                    'nombreIdioma' => 'idm.nombre',
                    'selLevelWritten' => 'didm.nivel_lee',
                    'selLevelOral' => 'didm.nivel_lee'
                        )
                )->joinInner(array(
                    'idm' => 'idioma'), 'didm.id_idioma = idm.id_slug', array())
                ->group('didm.id_idioma')
                ->where('id_postulante = ? ', $id);

        return ($this->getAdapter()->fetchAll($sql));
    }

    /**
     * 
     * @param type $id
     * @return type 
     */
    public function getMejorNivelEstudio($id)
    {
        $db = $this->getAdapter();
        $sqlSub = $db->select()
                ->from(
                        array(
                    'e' => 'estudio'), array(
                    'e.id_nivel_estudio',
                    'e.id_carrera',
                    'e.otro_carrera',
                    'e.colegiatura_numero',
                    'e.otro_institucion',
                    'e.id_nivel_estudio_tipo')
                )
                ->where('e.id_postulante = ?', $id)
                ->where('e.id_nivel_estudio != ?', 9)
                ->joinLeft(array(
                    'n' => 'nivel_estudio'), 'n.id=e.id_nivel_estudio', array())
                ->joinLeft(array(
                    'nt' => 'nivel_estudio'), 'nt.id=e.id_nivel_estudio_tipo', array())
                ->order('n.peso DESC')
                ->order('nt.peso DESC')
                ->order('e.id DESC')
                ->limit(1);
        $datos = $this->getAdapter()->fetchRow($sqlSub);
        $rs = null;
        if ($datos != false && $datos['id_carrera'] != null) {
            $sql = $db->select()
                    ->from(
                            array(
                        'ne' => 'nivel_estudio'), array(
                        'nivel_estudio' => 'ne.nombre',
                        'nivel_estudio_tipo' => 'net.nombre',
                        'carrera' => 'c.nombre')
                    )
                    ->where('ne.id = ' . $datos['id_nivel_estudio'])
                    ->joinLeft(array(
                        'net' => 'nivel_estudio'), 'net.id=' . (int) $datos['id_nivel_estudio_tipo'], array())
                    ->joinLeft(array(
                'c' => 'carrera'), 'c.id=' . $datos['id_carrera'], array());
            $rs = $this->getAdapter()->fetchRow($sql);
            $rs['colegiatura_numero'] = $datos['colegiatura_numero'];
            $rs['otro_institucion'] = $datos['otro_institucion'];
        } elseif ($datos != false && $datos['otro_carrera'] != "") {
            $sql = $db->select()
                    ->from(
                            array(
                        'ne' => 'nivel_estudio'), array(
                        'nivel_estudio' => 'ne.nombre',
                        'nivel_estudio_tipo' => 'net.nombre')
                    )
                    ->where('ne.id = ' . $datos['id_nivel_estudio'])
                    ->joinLeft(array(
                'net' => 'nivel_estudio'), 'net.id=' . (int) $datos['id_nivel_estudio_tipo'], array());
            $rs = $this->getAdapter()->fetchRow($sql);
            $rs['carrera'] = $datos['otro_carrera'];
            $rs['colegiatura_numero'] = $datos['colegiatura_numero'];
            $rs['otro_institucion'] = $datos['otro_institucion'];
        }
        return $rs;
    }

    public function getProgramas($id)
    {
        $sql = $this->getAdapter()->select()
                ->from(array(
                    'pc' => 'programa_computo'), array(
                    'id_programa' => 'dpc.id_programa_computo',
                    'programa' => 'pc.nombre',
                    'dpc.nivel'))
                ->joinInner(array(
                    'dpc' => 'dominio_programa_computo'), 'pc.id = dpc.id_programa_computo', array())
                ->where('id_postulante = ?', $id);
        $rs = $this->getAdapter()->fetchAll($sql);
        return $rs;
    }

    public function getPostulantePrivacidad($idPostulante)
    {
        $sql = $this->getAdapter()->select()
                ->from($this->_name, 'prefs_confidencialidad')
                ->where('id = ?', $idPostulante);

        $rs = $this->getAdapter()->fetchOne($sql);
        return $rs;
    }

    public function UpdateMsjsLeidos($idUsuario)
    {
        //Obtener los leidos y no leidos de una postulación
        $sql = $this->_db->select()
                ->from(
                        array(
                    'm' => 'mensaje'), array(
                    'notif_leidas' => 'sum(leido)',
                    'notif_no_leidas' => new Zend_Db_Expr('sum(if(leido = 0, 1, 0))')
                        )
                )
                ->where('notificacion = ?', 1)
                ->where('para = ?', $idUsuario);
        $row = $this->_db->fetchRow($sql);

        //Actualizar en la tabla postulante
        $where = $this->_db->quoteInto('id_usuario = ?', $idUsuario);
        return $this->update($row, $where);
    }

    public function eliminarCv($idPostulante)
    {
        $sql = $this->select()
                ->from($this->_name, 'path_cv')
                ->where('id = ?', $idPostulante);
        $cvPath = $this->getAdapter()->fetchOne($sql);

        if (unlink($this->_config->urls->app->elementsCvRoot . $cvPath)) {
            $this->update(
                    array(
                'path_cv' => '',
                'ultima_actualizacion' => date('Y-m-d H:i:s'),
                'last_update_ludata' => date('Y-m-d H:i:s')
                    ), $this->getAdapter()->quoteInto('id = ?', $idPostulante)
            );
        }
    }

    public function getAlertaPostulante($idPostulante)
    {
        $sql = $this->getAdapter()
                ->select()
                ->from(
                        $this->_name, array(
                    'prefs_emailing_avisos' => 'prefs_emailing_avisos',
                    'prefs_emailing_info' => 'prefs_emailing_info',
                    'prefs_emailing_mercado' => 'prefs_emailing_mercado'
                        )
                )
                ->where('id = ?', $idPostulante);
        $rs = $this->getAdapter()->fetchRow($sql);
        return $rs;
    }

    public function getCaracteristicasZendLucene($idPostulante)
    {
        $adapter = $this->getAdapter();
        $sql = "(SELECT GROUP_CONCAT(tc.nombre SEPARATOR '-') AS descripcion FROM estudio e
                   INNER JOIN carrera c ON c.id = e.id_carrera
                   INNER JOIN tipo_carrera tc ON tc.id=c.id_tipo_carrera
                   WHERE e.id_postulante=$idPostulante)
                   UNION ALL
                 (SELECT GROUP_CONCAT(ne.nombre SEPARATOR '-') AS descripcion FROM estudio e
                  INNER JOIN  nivel_estudio ne ON ne.id=e.id_nivel_estudio
                  WHERE e.id_postulante=$idPostulante)
                  UNION ALL
                  (SELECT GROUP_CONCAT(ne.id SEPARATOR '-') AS descripcion FROM estudio e
                   INNER JOIN  nivel_estudio ne ON ne.id=e.id_nivel_estudio
                   WHERE e.id_postulante=$idPostulante)
                   UNION ALL
                  (SELECT GROUP_CONCAT(tc.id SEPARATOR '-') AS descripcion FROM estudio e
                   INNER JOIN carrera c ON c.id = e.id_carrera
                   INNER JOIN tipo_carrera tc ON tc.id=c.id_tipo_carrera
                   WHERE e.id_postulante=$idPostulante)
                   UNION ALL
                   (SELECT GROUP_CONCAT(
                    IF(e.fin_ano,e.fin_ano*12+e.fin_mes,YEAR(CURDATE())*12+MONTH(CURDATE()))-
                       (e.inicio_ano*12+e.inicio_mes)
                        SEPARATOR '-') AS descripcion
                     FROM experiencia e
                     WHERE e.id_postulante=$idPostulante)
                 UNION ALL
                 (SELECT GROUP_CONCAT(di.id_idioma SEPARATOR '-')  AS descripcion
                  FROM dominio_idioma di
                  WHERE di.id_postulante=$idPostulante)
                  UNION ALL
                 (SELECT GROUP_CONCAT(dpc.id_programa_computo SEPARATOR '-') AS descripcion
                  FROM dominio_programa_computo dpc
                  WHERE dpc.id_postulante=$idPostulante)";
        $stm = $adapter->query($sql);
        return $stm->fetchAll();
    }

    public function getCaracteristicasPostulanteZendLucene($idPostulante)
    {
        $adapter = $this->getAdapter();
        $sql = "SELECT
                IF ((SELECT COUNT(1) AS descripcion
                    FROM (SELECT @r:=otra_empresa AS descripcion
                         FROM experiencia e WHERE e.id_postulante=$idPostulante
                         ORDER BY e.fin_ano DESC,e.fin_mes DESC,e.id DESC LIMIT 1 ) AS tabla
                    )=0,'Ninguno', @r) AS descripcion
                 UNION ALL
                SELECT
                IF ((SELECT COUNT(1) AS descripcion
                    FROM (SELECT @r2:=otro_puesto AS descripcion
                         FROM experiencia e WHERE e.id_postulante=$idPostulante
                         ORDER BY e.fin_ano DESC,e.fin_mes DESC,e.id DESC LIMIT 1 ) AS tabla
                    )=0,'Ninguno', @r2) AS descripcion
                  UNION ALL
		(SELECT GROUP_CONCAT(tc.nombre SEPARATOR '-') AS descripcion FROM estudio e
                   INNER JOIN carrera c ON c.id = e.id_carrera
                   INNER JOIN tipo_carrera tc ON tc.id=c.id_tipo_carrera
                   WHERE e.id_postulante=$idPostulante)
                   UNION ALL
                 (SELECT GROUP_CONCAT(ne.nombre SEPARATOR '-') AS descripcion FROM estudio e
                  INNER JOIN  nivel_estudio ne ON ne.id=e.id_nivel_estudio
                  WHERE e.id_postulante=$idPostulante)
                  UNION ALL
                  (SELECT GROUP_CONCAT(ne.id SEPARATOR '-') AS descripcion FROM estudio e
                   INNER JOIN  nivel_estudio ne ON ne.id=e.id_nivel_estudio
                   WHERE e.id_postulante=$idPostulante)
                   UNION ALL
                  (SELECT GROUP_CONCAT(tc.id SEPARATOR '-') AS descripcion FROM estudio e
                   INNER JOIN carrera c ON c.id = e.id_carrera
                   INNER JOIN tipo_carrera tc ON tc.id=c.id_tipo_carrera
                   WHERE e.id_postulante=$idPostulante)
                   UNION ALL
                   (SELECT GROUP_CONCAT(
                    IF(e.fin_ano,e.fin_ano*12+e.fin_mes,YEAR(CURDATE())*12+MONTH(CURDATE()))-
                       (e.inicio_ano*12+e.inicio_mes)
                        SEPARATOR '-') AS descripcion
                     FROM experiencia e
                     WHERE e.id_postulante=$idPostulante)
                 UNION ALL
                 (SELECT GROUP_CONCAT(di.id_idioma SEPARATOR '-')  AS descripcion
                  FROM dominio_idioma di
                  WHERE di.id_postulante=$idPostulante)
                  UNION ALL
                 (SELECT GROUP_CONCAT(dpc.id_programa_computo SEPARATOR '-') AS descripcion
                  FROM dominio_programa_computo dpc
                  WHERE dpc.id_postulante=$idPostulante)
                 UNION ALL
                   (SELECT GROUP_CONCAT(e.id_nivel_puesto SEPARATOR '-') as descripcion
                   FROM experiencia e WHERE e.id_postulante=$idPostulante )
                 UNION ALL
                   (SELECT GROUP_CONCAT(e.id_area SEPARATOR '-') as descripcion FROM experiencia e
                   WHERE e.id_postulante=$idPostulante )";
        $stm = $adapter->query($sql);
        return $stm->fetchAll();
    }

    public function getBusquedaPersonalizada($nombre, $apellido, $numDoc, $email, $col = '', $ord = ''
    )
    {
        $col = $col == '' ? 'p.nombres' : $col;
        $ord = $ord == '' ? 'DESC' : $ord;

        $sql = $this->getAdapter()->select()
                ->from(
                        array(
                    'p' => $this->_name), array(
                    'p.id',
                    'p.id_usuario',
                    'p.nombres',
                    'p.apellidos',
                    'p.num_doc')
                )
                ->joinInner(
                array(
            'u' => 'usuario'), 'u.id = p.id_usuario', array(
            'u.email',
            'u.activo',
            'u.fh_registro',
            'token' => 'MD5(RAND())')
        );
        if ($nombre != null) {
            $sql = $sql->where('p.nombres like ?', '%' . $nombre . '%');
        }
        if ($apellido != null) {
            $sql = $sql->where('p.apellidos like ?', '%' . $apellido . '%');
        }
        if ($numDoc != null) {
            $sql = $sql->where('p.num_doc = ?', $numDoc);
        }
        if ($email != null) {
            $sql = $sql->where('u.email = ?', $email);
        }
        $sql = $sql->order(sprintf('%s %s', $col, $ord));
        //return $this->getAdapter()->fetchAll($sql);
        return $sql;
    }

    public function getPaginadorBusquedaPersonalizada($nombre, $apellido, $tipoDoc, $email, $col, $ord
    )
    {
        $paginadoBusqueda = $this->_config->administrador->gestion->paginadoBusqueda;
        $p = Zend_Paginator::factory(
                        $this->getBusquedaPersonalizada(
                                $nombre, $apellido, $tipoDoc, $email, $col, $ord
                        )
        );
        return $p->setItemCountPerPage($paginadoBusqueda);
    }

    public function buscarPostulantexEmail($email)
    {
        $sql = $this->getAdapter()->select()
                ->from(
                        array(
                    "p" => $this->_name), array(
                    "id" => "p.id",
                    "nombres" => "p.nombres",
                    "apellidos" => "p.apellidos",
                    "apellido_paterno" => "p.apellido_paterno",
                    "apellido_materno" => "p.apellido_materno",
                    "sexo" => "p.sexo",
                    "telefono" => "p.telefono",
                    "celular" => "p.celular",
                    "path_cv" => "p.path_cv",
                    "path_foto" => "p.path_foto",
                    "slug" => "p.slug",
                    "presentacion" => "p.presentacion"
                        )
                )
                ->join(
                        array(
                    "u" => "usuario"), "u.id = p.id_usuario", array(
                    'email')
                )
                ->where('u.email = ?', $email)
                ->where('u.rol="postulante"')
                ->where('u.activo=1');

        $result = $this->getAdapter()->fetchRow($sql);
        return $result;
    }

    public function getDataPostulantes_($ids, $col = null, $ord = null)
    {
        $db = $this->getAdapter();
        $campoOrdenar = null;
        if ($col == "nivel_estudio" || $col == "carrera") {
            $campoOrdenar = $col;
            $col = null;
        }
        if ($col == "id") {
            $col = "idpostulante";
        }

        $sql = $db->select()
                ->from(array(
                    'p' => $this->_name), array(
                    'idpostulante' => 'p.id',
                    'nombres' => 'CONCAT(p.nombres, " ", p.apellido_paterno, " ", p.apellido_materno)',
                    'nombre' => 'p.nombres',
//                    'apellidos' => 'p.apellidos',
                    'apellido_paterno' => 'p.apellido_paterno',
                    'apellido_materno' => 'p.apellido_materno',
                    'tipo_doc' => 'p.tipo_doc',
                    'num_doc' => 'p.num_doc',
                    'fecha_nac' => 'p.fecha_nac',
                    'estado_civil' => 'p.estado_civil',
                    'sexoMF' => 'p.sexo',
                    'edad' => 'FLOOR(DATEDIFF(CURDATE(),p.fecha_nac)/365)',
                    'telefono' => new Zend_Db_Expr("TRIM(p.telefono)"),
                    'celular' => new Zend_Db_Expr("TRIM(p.celular)"),
                    'path_foto' => 'p.path_foto',
                    'path_foto_uno' => 'p.path_foto1',
                    'path_foto_dos' => 'p.path_foto2',
                    'slug' => 'p.slug',
                    'website' => 'p.website',
                    'presentacion' => 'p.presentacion',
                    'path_cv' => 'p.path_cv',
                    'notif_leidas' => 'p.notif_leidas',
                    'notif_no_leidas' => 'p.notif_no_leidas',
                    'idusuario' => 'p.id_usuario',
                    'esConfidencial' => 'p.prefs_confidencialidad',
                    'ultima_actualizacion' => 'p.ultima_actualizacion',
                    'id_ubigeo' => 'p.id_ubigeo',
                    'mejor_nivel_estudio' => 'p.mejor_nivel_estudio',
                    "destacado" => "p.destacado",
                    'mejor_carrera' => 'p.mejor_carrera')
                )
                ->join(array(
                    'u' => 'usuario'), 'u.id=p.id_usuario ', array(
                    'id_usuario' => 'u.id',
                    'email' => 'u.email')
                )
                ->where("p.id IN (?)", $ids);
        if ($col != null && $ord != null) {
            $sql->order($col . " " . $ord);
        }
        $sql->order("p.destacado DESC");
        $postulantes = $db->fetchAll($sql);
        $dataPostulantes = array();
        for ($i = 0; $i < count($postulantes); $i++) {
            $mejorEstudio = $this->getMejorNivelEstudio($postulantes[$i]["idpostulante"]);

            $postulantes[$i]["nivel_estudio"] = $mejorEstudio["nivel_estudio"] . '/' . $mejorEstudio["nivel_estudio_tipo"];
            $postulantes[$i]["carrera"] = $mejorEstudio["carrera"];
            if ($mejorEstudio == null) {
                $postulantes[$i]["nivel_estudio"] = "Ninguno";
                $postulantes[$i]["carrera"] = "Ninguno";
            }
            $dataPostulantes[] = $postulantes[$i];
        }

        if ($campoOrdenar != null) {

            $position = array();
            $newRow = array();
            foreach ($dataPostulantes as $key => $row) {
                $position[$key] = $row[$campoOrdenar];
                $newRow[$key] = $row;
            }
            if ($ord == "DESC") {
                arsort($position);
            } else {
                asort($position);
            }
            $returnArray = array();
            foreach ($position as $key => $pos) {
                $returnArray[] = $newRow[$key];
            }
        } else {
            $returnArray = $dataPostulantes;
        }

        return $returnArray;
    }

    public function getDataPostulantes($idGrupo, $col = null, $ord = null, $idpostulante = array())
    {

        $db = $this->getAdapter();
        $campoOrdenar = null;
        if ($col == "nivel_estudio" || $col == "carrera") {
            $campoOrdenar = $col;
            $col = null;
        }
        if ($col == "id") {
            $col = "idpostulante";
        }

        $sql = $db->select()->distinct()
                ->from(array(
                    'p' => $this->_name), array(
                    'idpostulante' => 'p.id',
                    'nombres' => 'CONCAT(p.nombres, " ", p.apellido_paterno, " ", p.apellido_materno)',
                    'nombre' => 'p.nombres',
                    'apellido_paterno' => 'p.apellido_paterno',
                    'apellido_materno' => 'p.apellido_materno',
                    'tipo_doc' => 'p.tipo_doc',
                    'num_doc' => 'p.num_doc',
                    'fecha_nac' => 'p.fecha_nac',
                    'estado_civil' => 'p.estado_civil',
                    'sexoMF' => 'p.sexo',
                    'edad' => new Zend_Db_Expr('FLOOR(DATEDIFF(CURDATE(),p.fecha_nac)/365)'),
                    'telefono' => new Zend_Db_Expr("TRIM(p.telefono)"),
                    'celular' => new Zend_Db_Expr("TRIM(p.celular)"),
                    'path_foto' => 'p.path_foto',
                    'path_foto_uno' => 'p.path_foto1',
                    'path_foto_dos' => 'p.path_foto2',
                    'slug' => 'p.slug',
                    'website' => 'p.website',
                    'presentacion' => 'p.presentacion',
                    'path_cv' => 'p.path_cv',
                    'notif_leidas' => 'p.notif_leidas',
                    'notif_no_leidas' => 'p.notif_no_leidas',
                    'idusuario' => 'p.id_usuario',
                    'esConfidencial' => 'p.prefs_confidencialidad',
                    'ultima_actualizacion' => 'p.ultima_actualizacion',
                    'id_ubigeo' => 'p.id_ubigeo',
                    'nivel_estudio' => 'p.mejor_nivel_estudio',
                    "destacado" => "p.destacado",
                    'carrera' => 'p.mejor_carrera')
                )
                ->joinInner(array(
                    'u' => 'usuario'), 'u.id=p.id_usuario ', array(
                    'id_usuario' => 'u.id',
                    'email' => 'u.email')
                )->joinInner(array(
                    'bcp' => 'bolsa_cv_postulante'), 'bcp.id_postulante = p.id', null)
                //->joinLeft(array('ubi' => 'ubigeo'), 'ubi.id = p.id_ubigeo', null)
                //->joinLeft(array('di' => 'dominio_idioma'), 'di.id_postulante = p.id', null)
                ->where('bcp.id_bolsa_cv = ?', $idGrupo);
        if (count($idpostulante) > 0) {
            $sql->where('p.id IN (?)', $idpostulante);
        }
        if ($col != null && $ord != null) {
            $sql->order($col . " " . $ord);
        }
        $sql->order("p.destacado DESC");
        // echo $sql;exit;
        $postulantes = $db->fetchAll($sql);
        $dataPostulantes = $postulantes;
        /* $dataPostulantes = array();
          for ($i = 0; $i < count($postulantes); $i++) {
          $mejorEstudio = $this->getMejorNivelEstudio($postulantes[$i]["idpostulante"]);

          $postulantes[$i]["nivel_estudio"] = $mejorEstudio["nivel_estudio"].'/'.$mejorEstudio["nivel_estudio_tipo"];
          $postulantes[$i]["carrera"] = $mejorEstudio["carrera"];
          if ($mejorEstudio == null) {
          $postulantes[$i]["nivel_estudio"] = "Ninguno";
          $postulantes[$i]["carrera"] = "Ninguno";
          }
          $dataPostulantes[] = $postulantes[$i];
          } */

        if ($campoOrdenar != null) {

            $position = array();
            $newRow = array();
            foreach ($dataPostulantes as $key => $row) {
                $position[$key] = $row[$campoOrdenar];
                $newRow[$key] = $row;
            }
            if ($ord == "DESC") {
                arsort($position);
            } else {
                asort($position);
            }
            $returnArray = array();
            foreach ($position as $key => $pos) {
                $returnArray[] = $newRow[$key];
            }
        } else {
            $returnArray = $dataPostulantes;
        }

        return $returnArray;
    }

    public function getPaginatorBolsaCVs($pagData)
    {
        $paginado = $this->_config->empresa->bolsacvs->paginadopostulantes;
        $p = Zend_Paginator::factory($pagData);
        return $p->setItemCountPerPage($paginado);
    }

    public function getPostulantesDesincronizados($limit)
    {
        $adapter = $this->getAdapter();
        $sql = "SELECT ps.id AS idpostulante,
                        ps.path_foto AS foto,
                        ps.nombres,
                        ps.apellido_paterno,
                        ps.apellido_materno,
                        ps.telefono,
                        ps.celular,
                        ps.slug,
                        ps.sexo,
                        FLOOR(DATEDIFF(CURDATE(),ps.fecha_nac)/365) AS edad,
                        ps.path_cv,
                        ps.fecha_nac,
                        (SELECT GROUP_CONCAT(ne.nombre SEPARATOR '-') FROM estudio e
                         INNER JOIN  nivel_estudio ne ON ne.id=e.id_nivel_estudio
                         WHERE e.id_postulante=ps.id) AS estudios,
                        (SELECT GROUP_CONCAT(ne.id SEPARATOR '-') FROM estudio e
                         INNER JOIN  nivel_estudio ne ON ne.id=e.id_nivel_estudio
                         WHERE e.id_postulante=ps.id) AS estudios_claves,
                        (SELECT GROUP_CONCAT(tc.nombre SEPARATOR '-') FROM estudio e
                         INNER JOIN carrera c ON c.id = e.id_carrera
                         INNER JOIN tipo_carrera tc ON tc.id=c.id_tipo_carrera
                         WHERE e.id_postulante=ps.id) AS carrera,
                         (SELECT GROUP_CONCAT(tc.id SEPARATOR '-') FROM estudio e
                         INNER JOIN carrera c ON c.id = e.id_carrera
                         INNER JOIN tipo_carrera tc ON tc.id=c.id_tipo_carrera
                         WHERE e.id_postulante=ps.id) AS carrera_claves,
                         (SELECT GROUP_CONCAT(
                             IF(e.fin_ano,e.fin_ano*12+e.fin_mes,
                             YEAR(CURDATE())*12+MONTH(CURDATE()))-
                             (e.inicio_ano*12+e.inicio_mes)
                             SEPARATOR '-')
                         FROM experiencia e
                         WHERE e.id_postulante=ps.id) AS experiencia,
                         (SELECT GROUP_CONCAT(di.id_idioma SEPARATOR '-')
                         FROM dominio_idioma di
                         WHERE di.id_postulante=ps.id) AS idiomas,
                         (SELECT GROUP_CONCAT(dpc.id_programa_computo SEPARATOR '-')
                          FROM dominio_programa_computo dpc
                          WHERE dpc.id_postulante=ps.id) AS programas_claves,
                          ps.sexo AS sexo_claves,
                          IF(ps.sexo='M','Masculino','Femenino') AS sexo,
                          ps.id_ubigeo AS ubigeo_claves,
                          (SELECT u.nombre FROM ubigeo u
                           WHERE u.id=ps.id_ubigeo) AS ubigeo,
                          (SELECT otra_empresa FROM experiencia e
                           WHERE e.id_postulante=ps.id
                           ORDER BY e.fin_ano DESC,e.fin_mes DESC
                           LIMIT 1) AS empresa,
			  (SELECT otro_puesto FROM experiencia e
                           WHERE e.id_postulante=ps.id
                           ORDER BY e.fin_ano DESC,e.fin_mes DESC
                           LIMIT 1) AS puesto,
                           (SELECT GROUP_CONCAT(e.id_nivel_puesto SEPARATOR '-') FROM experiencia e
                           WHERE e.id_postulante=ps.id ) nivel_puesto,
	                   (SELECT GROUP_CONCAT(e.id_area SEPARATOR '-') FROM experiencia e
                           WHERE e.id_postulante=ps.id ) 'area',
                        IF(ps.last_update_luindex IS NULL, 'insert', 'update') AS accion
               FROM postulante AS ps
               INNER JOIN usuario AS u ON ps.`id_usuario`=u.id
               WHERE u.`activo`=1 
                     AND (last_update_ludata > last_update_luindex OR last_update_luindex IS NULL)
               LIMIT " . $limit;
        $stm = $adapter->query($sql);
        return $stm->fetchAll();
    }

    public function sincronizaPostulante($idPostulante)
    {
        if (is_array($idPostulante)) {
            $where = $this->getAdapter()->quoteInto('id IN (?)', $idPostulante);
        } else {
            $where = $this->getAdapter()->quoteInto('id = ?', $idPostulante);
        }
        return $this->update(array(
                    'last_update_luindex' => date('Y-m-d H:i:s')), $where);
    }

    public function sincronizaPostulantes($inicio, $n)
    {
        return $this->getAdapter()->query(
                        "UPDATE `postulante` SET last_update_luindex = '" . date('Y-m-d H:i:s') . "'
             WHERE `id` IN ( 
                SELECT pos2.idpos FROM (SELECT ps.id AS idpos FROM `postulante` AS ps
                INNER JOIN `usuario` AS u ON ps.`id_usuario`=u.`id`
                WHERE u.`activo`=1 LIMIT " . $inicio . "," . $n . ") AS pos2)"
        );
    }

    public static function getPostulantesMatchingAviso(
    $idAviso, $porcentajeMatch = 60, $diasUltimoLogin = null, $minimoDatosProfesionales = 0, $limit = 1000, $offset = 0
    )
    {
        $obj = new Application_Model_Postulante();
        $db = $obj->getAdapter();

        $sql = $db->select()
                ->from(
                        array(
                    'p' => $obj->_name), array(
                    'apmatch' => new Zend_Db_Expr('APMATCH(' . $idAviso . ', p.id)'),
                    'p.id',
                    'p.nombres',
//                    'p.apellidos',
                    'p.apellido_paterno',
                    'p.apellido_materno',
                    'p.sexo',
                    'p.telefono',
                    'p.celular',
                    'postulante_slug' => 'p.slug',
                    'tDI' => new Zend_Db_Expr(
                            '(SELECT 1 FROM dominio_idioma AS di WHERE 
                        di.id_postulante = p.id GROUP BY di.id_postulante)'
                    ),
                    'tDPC' => new Zend_Db_Expr(
                            '(SELECT 1 FROM dominio_programa_computo AS dpc WHERE 
                        dpc.id_postulante = p.id GROUP BY dpc.id_postulante)'
                    ),
                    'tEx' => new Zend_Db_Expr(
                            '(SELECT 1 FROM experiencia AS ex WHERE 
                        ex.id_postulante = p.id GROUP BY ex.id_postulante)'
                    ),
                    'tEs' => new Zend_Db_Expr(
                            '(SELECT 1 FROM estudio AS e WHERE 
                        e.id_postulante = p.id GROUP BY e.id_postulante)'
                    )
                        )
                )->join(array(
                    'u' => 'usuario'), 'u.id = p.id_usuario', array(
                    'u.email'))
                ->having(
                        '(IFNULL(tDI, 0) + IFNULL(tDPC, 0) + IFNULL(tEx, 0) + IFNULL(tEs, 0)) > ?', $minimoDatosProfesionales
                )
                ->having('apmatch >= ?', $porcentajeMatch)
                ->limit($limit, $offset);

        if (!empty($diasUltimoLogin)) {
            $sql = $sql->where('u.ultimo_login > SUBDATE(NOW(), INTERVAL ? DAY)', $diasUltimoLogin);
        }
//        echo $sql->assemble();exit;
        return $db->fetchAll($sql);
    }

    public function obtenerUsuario($id)
    {
        $select = $this->getAdapter()->select()
                ->from(array(
                    'p' => $this->_name), array(
                    'p.id'))
                ->joinInner(array(
                    'u' => 'usuario'), 'u.id = p.id_usuario', array(
                    'email'))
                ->where('p.id =?', $id);

        return $this->getAdapter()->fetchRow($select);
    }

    public function obtenerPorId($id, $columnas = array())
    {
        $columnas = $this->setCols($columnas);

        return $this->fetchRow($this->select()
                                ->from($this->_name, $columnas)
                                ->where('id =?', (int) $id));
    }

    public function nivelMaximoEstudio($id_usuario)
    {

        $sql = $this->getAdapter();
        $nivel = $sql->select()->from(array(
                            'p' => 'postulante'), null)
                        ->joinInner(array(
                            'e' => 'estudio'), 'e.id_postulante = p.id', null)
                        ->joinInner(array(
                            'ne' => 'nivel_estudio'), 'ne.id = e.id_nivel_estudio', 'nombre')
                        ->where('p.id_usuario = ?', $id_usuario)->query()->fetchColumn();

        if (empty($nivel))
            $nivel = 'Sin estudios';

        return $nivel;
    }

    /**
     * Retorna la lista de postulantes activos por aviso
     * 
     * @param int $idAviso
     */
    public function postulantesxAviso($idAviso)
    {

        $edad = new Zend_Db_Expr('CASE
            WHEN (MONTH(po.fecha_nac) < MONTH(CURRENT_DATE)) THEN YEAR(CURRENT_DATE) - YEAR(po.fecha_nac)
            WHEN (MONTH(po.fecha_nac) = MONTH(CURRENT_DATE)) AND (DAY(po.fecha_nac) <= DAY(CURRENT_DATE)) 
            THEN YEAR(CURRENT_DATE) - YEAR(po.fecha_nac)
            ELSE (YEAR(CURRENT_DATE) - YEAR(po.fecha_nac)) - 1 END');

        $upuesto = $this->getAdapter()->select()
                        ->from(array(
                            'ex' => 'experiencia'), 'ex.otro_puesto')
                        ->joinInner(array(
                            'np' => 'nivel_puesto'), 'np.id = ex.id_nivel_puesto', null)
                        ->where('ex.id_postulante = po.id')->order('np.peso desc')->limit(1);

        $uempresa = $this->getAdapter()->select()
                        ->from(array(
                            'ex' => 'experiencia'), 'ex.otra_empresa')
                        ->joinInner(array(
                            'np' => 'nivel_puesto'), 'np.id = ex.id_nivel_puesto', null)
                        ->where('ex.id_postulante = po.id')->order('np.peso desc')->limit(1);

        $sql = $this->getAdapter();
        $listaPostulantes = $sql->select()->from(array(
                            'p' => 'postulacion'), array(
                            'nivel_estudio',
                            'carrera'))
                        ->joinInner(array(
                            'po' => 'postulante'), 'po.id = p.id_postulante', array(
                            'po.apellidos',
                            'po.apellido_paterno',
                            'po.apellido_materno',
                            'po.nombres',
                            'po.num_doc',
                            'po.slug',
                            'edad' => $edad,
                            'path_foto',
                            'upuesto' => "(" . $upuesto . ")",
                            'uempresa' => "(" . $uempresa . ")"
                        ))
                        ->where('p.id_anuncio_web = ?', $idAviso)
                        ->where('p.activo = ?', 1)
                        ->where('p.descartado = ?', 0)->order('p.fh desc')
                        ->limit(6)->query()->fetchAll();

        return $listaPostulantes;
    }

    /**
     * Retorna el total de postulantes activos por aviso
     * 
     * @param int $idAviso
     */
    public function totalPostulantesxAviso($idAviso)
    {

        $edad = new Zend_Db_Expr('CASE
            WHEN (MONTH(po.fecha_nac) < MONTH(CURRENT_DATE)) THEN YEAR(CURRENT_DATE) - YEAR(po.fecha_nac)
            WHEN (MONTH(po.fecha_nac) = MONTH(CURRENT_DATE)) AND (DAY(po.fecha_nac) <= DAY(CURRENT_DATE)) 
            THEN YEAR(CURRENT_DATE) - YEAR(po.fecha_nac)
            ELSE (YEAR(CURRENT_DATE) - YEAR(po.fecha_nac)) - 1 END');

        $upuesto = $this->getAdapter()->select()
                        ->from(array(
                            'ex' => 'experiencia'), 'ex.otro_puesto')
                        ->joinInner(array(
                            'np' => 'nivel_puesto'), 'np.id = ex.id_nivel_puesto', null)
                        ->where('ex.id_postulante = po.id')->order('np.peso desc')->limit(1);

        $uempresa = $this->getAdapter()->select()
                        ->from(array(
                            'ex' => 'experiencia'), 'ex.otra_empresa')
                        ->joinInner(array(
                            'np' => 'nivel_puesto'), 'np.id = ex.id_nivel_puesto', null)
                        ->where('ex.id_postulante = po.id')->order('np.peso desc')->limit(1);

        $sql = $this->getAdapter();
        $listaPostulantes = $sql->select()->from(array(
                            'p' => 'postulacion'), array(
                            'nivel_estudio',
                            'carrera'))
                        ->joinInner(array(
                            'po' => 'postulante'), 'po.id = p.id_postulante', array(
                            'po.apellidos',
                            'po.apellido_paterno',
                            'po.apellido_materno',
                            'po.nombres',
                            'po.num_doc',
                            'po.slug',
                            'edad' => $edad,
                            'path_foto',
                            'upuesto' => "(" . $upuesto . ")",
                            'uempresa' => "(" . $uempresa . ")"
                        ))
                        ->where('p.id_anuncio_web = ?', $idAviso)
                        ->where('p.activo = ?', 1)
                        ->where('p.descartado = ?', 0)->order('p.fh desc')
                        ->query()->fetchAll();

        return $listaPostulantes;
    }

    //Si no ha actualizado hace 4 meses false  sino true
    public function verificarUpdateCV($idPostulante)
    {

        $sql = $this->getAdapter();
        $diaUpdateCV = $sql->select()->from($this->_name, new Zend_Db_Expr('DATEDIFF(CURRENT_TIMESTAMP,ultima_actualizacion)'))
                        ->where('id = ?', $idPostulante)->query()->fetchColumn();

        $diasLimite = $this->_config->updateCV->actualizacion->dias;

        if ($diaUpdateCV < $diasLimite)
            return true;
        else //Debe actualizar su info de postulante
            return false;
    }

    /*
     * Verifica si el postulante tiene los datos 
     * obligatorios faltantes despues del registro
     * 
     * @param int $idPostulante     ID del Postulante
     * @return bool                 true en caso de tener todo 
     *                              correcto y false en caso 
     *                              de tener campos faltantes
     */

    public function hasDataForApplyJob($idPostulante)
    {
        $sql = $this->getAdapter();
        $datos = $sql->select()
                ->from($this->_name, array(
                    'sexo',
                    'tipo_doc',
                    'num_doc',
                    'pais_residencia',
                    'id_ubigeo'))
                ->where('id = ?', $idPostulante)
                ->query()
                ->fetch();

        $validos = (
                !empty($datos['sexo']) & !empty($datos['tipo_doc']) & !empty($datos['num_doc']) & !empty($datos['pais_residencia']) & !empty($datos['id_ubigeo'])
                );

        return $validos;
    }

    public function hasDataForApplyJobSession($dataPostulante)
    {
        $validos = (
                !empty($dataPostulante['sexo']) & !empty($dataPostulante['tipo_doc']) & !empty($dataPostulante['num_doc']) & !empty($dataPostulante['pais_residencia']) & !empty($dataPostulante['id_ubigeo'])
                );

        return $validos;
    }

    public function getCv($idPostulante)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, array(
                    'path_cv'))
                ->where('id = ?', $idPostulante);
        return $db->fetchRow($sql);
    }

    public function solr($idPostulante)
    {
        $adapter = $this->getAdapter();
        $sql = "SELECT
  ps.id                   AS idpostulante,
    REPLACE(ps.ultima_actualizacion, ' ', 'T') AS fecha_cv_update,
  ps.fecha_nac,
  ps.path_foto            AS foto,
  ps.slug                 AS slug,
  u.email AS correo,
  u.id AS id_usuario,
  ps.area_cargo_interes,
  ps.salario_interes,
  ps.id_ubigeo_interes,
  ps.nombres AS nombres,
  ps.apellido_paterno  AS ap_paterno,
  ps.apellido_materno  AS ap_materno,
  CAST(ps.disponibilidad_provincia_extranjero AS CHAR(1)) AS disponibilidad_provincia_extranjero,
   CAST(ps.prefs_confidencialidad AS CHAR(1)) AS prefs_confidencialidad,
  ps.website,
  ps.id_ubigeo,
  ps.estado_civil,
 CONCAT(ps.nombres,' ',ps.apellido_paterno ,' ',ps.apellido_materno) AS nomape,
  UPPER(CONCAT(ps.nombres,' ',ps.apellido_paterno ,' ',ps.apellido_materno))  AS nomape_ord,
  ps.num_doc              AS numdoc,ps.tipo_doc              AS tipodoc,
  IF(ISNULL(ps.celular),ps.telefono,ps.celular) AS telefono,
                  (ps.celular) AS celular,
  (ps.telefono) AS telefono_fijo,
  FLOOR(((TO_DAYS(CURDATE()) - TO_DAYS(ps.fecha_nac)) / 365)) AS edad,
  ps.path_cv              AS path_cv,
  (SELECT
     GROUP_CONCAT(DISTINCT CONCAT(IF((LENGTH(ne.id) = 1),'0',''),ne.id,'-',IF((LENGTH(e.id_nivel_estudio_tipo) = 1),'0',''),e.id_nivel_estudio_tipo) SEPARATOR '#-#')
   FROM (estudio e
      JOIN nivel_estudio ne
        ON ((ne.id = e.id_nivel_estudio)))
   WHERE (e.id_postulante = ps.id)) AS estudios_claves,
  (
SELECT
   (((SELECT nivel_estudio.peso FROM nivel_estudio WHERE (nivel_estudio.id = ne.id)) * 100) + (SELECT nivel_estudio.peso FROM nivel_estudio WHERE (nivel_estudio.id = e.id_nivel_estudio_tipo))) AS maxnivel
   FROM (estudio e
      JOIN nivel_estudio ne
        ON ((ne.id = e.id_nivel_estudio)))
   WHERE (e.id_postulante = ps.id)
   ORDER BY (((SELECT
                 nivel_estudio.peso
               FROM nivel_estudio
               WHERE (nivel_estudio.id = ne.id)) * 100) + (SELECT
                                                                     nivel_estudio.peso
                                                                   FROM nivel_estudio
                                                                   WHERE (nivel_estudio.id = IF((e.id_nivel_estudio_tipo = 0),1,e.id_nivel_estudio_tipo))))DESC
   LIMIT 1
    ) AS mayor_nivel_estudio,
  (SELECT
     (SELECT
        GROUP_CONCAT(IF(ISNULL(niv.nombre),'Sin estudios',niv.nombre) SEPARATOR '/')
      FROM nivel_estudio niv
      WHERE ((niv.id IN(e.id_nivel_estudio,e.id_nivel_estudio_tipo))
             AND (e.id_nivel_estudio <> 9))) AS niveles
 FROM (estudio e
      JOIN nivel_estudio ne
        ON ((ne.id = e.id_nivel_estudio)))
   WHERE (e.id_postulante = ps.id)
   ORDER BY (((SELECT
                 nivel_estudio.peso
               FROM nivel_estudio
               WHERE (nivel_estudio.id = ne.id)) * 100) + (SELECT
                                                                     nivel_estudio.peso
                                                                   FROM nivel_estudio
                                                                   WHERE (nivel_estudio.id = IF((e.id_nivel_estudio_tipo = 0),1,e.id_nivel_estudio_tipo))))DESC
   LIMIT 1) AS estudios,
  (SELECT
     IF((e.id_carrera > 0),(SELECT carrera.nombre FROM carrera WHERE (carrera.id = e.id_carrera)),e.otro_carrera) AS car
   FROM (estudio e
      JOIN nivel_estudio ne
        ON ((ne.id = e.id_nivel_estudio)))
   WHERE (e.id_postulante = ps.id)
   ORDER BY ne.peso DESC
   LIMIT 1) AS carrera,

     (SELECT
     IF((e.id_carrera > 0),(SELECT  UPPER( carrera.nombre) FROM carrera WHERE (carrera.id = e.id_carrera)),UPPER(e.otro_carrera)) AS car
   FROM (estudio e
      JOIN nivel_estudio ne
        ON ((ne.id = e.id_nivel_estudio)))
   WHERE (e.id_postulante = ps.id)
   ORDER BY (((SELECT
                 nivel_estudio.peso
               FROM nivel_estudio
               WHERE (nivel_estudio.id = ne.id)) * 100) + (SELECT
                                                                     nivel_estudio.peso
                                                                   FROM nivel_estudio
                                                                   WHERE (nivel_estudio.id = IF((e.id_nivel_estudio_tipo = 0),1,e.id_nivel_estudio_tipo))))DESC
   LIMIT 1) AS carrera_ord,
  (SELECT
     GROUP_CONCAT(DISTINCT CONCAT(IF((LENGTH(tc.id) = 1),'0',''),tc.id) SEPARATOR '#-#')
   FROM ((estudio e
       JOIN carrera c
         ON ((c.id = e.id_carrera)))
      JOIN tipo_carrera tc
        ON ((tc.id = c.id_tipo_carrera)))
   WHERE (e.id_postulante = ps.id)) AS tipo_carrera_claves,
  (SELECT
     SUM((IF(e.fin_ano,((e.fin_ano * 12) + e.fin_mes),((YEAR(CURDATE()) * 12) + MONTH(CURDATE()))) - ((e.inicio_ano * 12) + e.inicio_mes)))
   FROM experiencia e
   WHERE (e.id_postulante = ps.id)) AS experiencia,
  (SELECT
     GROUP_CONCAT(DISTINCT di.id_idioma SEPARATOR '#-#')
   FROM dominio_idioma di
   WHERE (di.id_postulante = ps.id)) AS idiomas,
  (SELECT
     GROUP_CONCAT(DISTINCT CONCAT(IF((LENGTH(dpc.id_programa_computo) = 1),'0',''),dpc.id_programa_computo) SEPARATOR '#-#')
   FROM dominio_programa_computo dpc
   WHERE (dpc.id_postulante = ps.id)) AS programas_claves,
  ps.sexo                 AS sexo,
    (SELECT
     u.nombre
   FROM ubigeo u
   WHERE (u.id = ps.id_ubigeo)) AS ubigeo,

  (SELECT
     CONCAT(dist.id,'#-#',prov.id,'#-#',dpto.id) AS ubigeo
   FROM (((postulante post
        JOIN ubigeo dist
          ON ((dist.id = post.id_ubigeo)))
       JOIN ubigeo prov
         ON ((dist.padre = prov.id)))
      JOIN ubigeo dpto
        ON ((prov.padre = dpto.id)))
   WHERE (post.id = ps.id)) AS ubigeo_claves,
  (
SELECT
     SUBSTRING_INDEX(GROUP_CONCAT(DISTINCT IF(e.otro_puesto='' OR ISNULL (e.id_puesto) ,

  ( SELECT CONCAT( p.nombre) FROM puesto p WHERE p.id=e.id_puesto)   ,e.otro_puesto)
   ORDER BY
  IF(
  (e.fin_ano),

  (((e.fin_ano * 12) + e.fin_mes)  ) 	,
  ((YEAR(CURDATE()) * 12) + MONTH(CURDATE()))
  )  DESC

   SEPARATOR '#-#'),'#-#',2)
   FROM experiencia e
   WHERE (e.id_postulante =ps.id )) AS puesto,
  ps.presentacion         AS presentacion,
           (SELECT
                             GROUP_CONCAT(DISTINCT estu.id_nivel_estudio_tipo SEPARATOR '#-#')
                           FROM (estudio estu
                              JOIN nivel_estudio niest
                                ON ((estu.id_nivel_estudio = niest.id)))
                           WHERE ((estu.id_postulante = ps.id)
                                  AND (estu.id_nivel_estudio = 9))) AS otros_estudios,
  ps.destacado            AS destacado,
    IF(ps.discapacidad >0,0,1) AS conadis_code,
    REPLACE(u.fh_registro, ' ', 'T') as fh_creacion
FROM (postulante ps
   JOIN usuario u
     ON ((ps.id_usuario = u.id)))
WHERE ((u.activo = 1) AND ps.id = $idPostulante) ORDER BY idpostulante DESC";

        $stm = $adapter->query($sql);
        return $stm->fetch(Zend_Db::FETCH_ASSOC);
    }

    public function getMaximoNivelEstudio($id)
    {
        $sqlSub = $db->select()
                ->from(
                        array(
                    'e' => 'estudio'), array(
                    'e.id_nivel_estudio',
                    'e.id_carrera',
                    'e.otro_carrera',
                    'e.colegiatura_numero',
                    'e.otro_institucion',
                    'e.id_nivel_estudio_tipo')
                )
                ->where('e.id_postulante = ?', $id)
                ->where('e.id_nivel_estudio != ?', 9)
                ->joinLeft(array(
                    'n' => 'nivel_estudio'), 'n.id=e.id_nivel_estudio', array())
                ->joinLeft(array(
                    'nt' => 'nivel_estudio'), 'nt.id=e.id_nivel_estudio_tipo', array())
                ->order('n.peso DESC')
                ->order('nt.peso DESC')
                ->limit(1);
        $datos = $this->getAdapter()->fetchRow($sqlSub);
        return $datos;
    }

    public function getOtrosEstudios($id)
    {
        $sql = $this->getAdapter()->select()
                ->from(array(
                    'e' => 'estudio'), array(
                    'institucion' => 'e.otro_institucion',
                    'actualmente' => 'e.en_curso',
                    'id_nivel_estudio',
                    'inicio_mes',
                    'inicio_ano',
                    'fin_mes',
                    'fin_ano',
                    'otro_estudio',
                    'otro_carrera',
                    'colegiatura_numero'
                ))
                ->joinleft(array(
                    'c' => 'carrera'), 'e.id_carrera = c.id', array(
                    'titulo' => 'c.nombre'))
                ->joinleft(array(
                    'tc' => 'tipo_carrera'), 'c.id_tipo_carrera = tc.id', array(
                    'tipo_carrera' => 'tc.nombre'))
                ->joinleft(array(
                    'net' => 'nivel_estudio'), 'e.id_nivel_estudio_tipo = net.id', array(
                    'nivel_tipo_nombre' => 'net.nombre'
                ))
                ->where('id_postulante = ?', $id)
                ->where('en_curso = ?', 1)
                ->where('id_nivel_estudio = ?', 9)
                ->order('inicio_ano DESC')
                ->order('inicio_mes DESC');
        $rsEnCurso = $this->getAdapter()->fetchAll($sql);
        $sql = $this->getAdapter()->select()
                ->from(array(
                    'e' => 'estudio'), array(
                    'institucion' => 'e.otro_institucion',
                    'actualmente' => 'e.en_curso',
                    'id_nivel_estudio',
                    'id_carrera',
                    'inicio_mes',
                    'inicio_ano',
                    'fin_mes',
                    'fin_ano',
                    'otro_estudio',
                    'otro_carrera',
                    'colegiatura_numero'
                ))
                ->joinleft(array(
                    'c' => 'carrera'), 'e.id_carrera = c.id', array(
                    'titulo' => 'c.nombre'))
                ->joinleft(array(
                    'tc' => 'tipo_carrera'), 'c.id_tipo_carrera = tc.id', array(
                    'tipo_carrera' => 'tc.nombre'))
                ->joinleft(array(
                    'net' => 'nivel_estudio'), 'e.id_nivel_estudio_tipo = net.id', array(
                    'nivel_tipo_nombre' => 'net.nombre'
                ))
                ->where('id_postulante = ?', $id)
                ->where('en_curso = ?', 0)
                ->where('id_nivel_estudio = ?', 9)
                ->order('fin_ano DESC')
                ->order('fin_mes DESC')
                ->order('inicio_ano DESC')
                ->order('inicio_mes DESC');
        $rsRestante = $this->getAdapter()->fetchAll($sql);
        $res = array();
        foreach ($rsEnCurso as $rec) {
            $res[] = $rec;
        }
        foreach ($rsRestante as $rst) {
            $res[] = $rst;
        }

        foreach ($res as &$r) {
            if ($r['id_nivel_estudio'] == 9) {
                $r['titulo'] = $r['otro_estudio'];
            }
            /* if ($r['otro_carrera']) {
              $r['titulo'] = $r['otro_carrera'];
              } */
        }
        return $res;
    }

    public function getTodosOtrosEstudios($id)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $id;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->getAdapter()->select()
                ->from(array(
                    'e' => 'estudio'), array(
                    'institucion' => 'e.otro_institucion',
                    'id_nivel_estudio',
                    'otro_estudio',
                    'otro_carrera',
                ))
                ->joinleft(array(
                    'c' => 'carrera'), 'e.id_carrera = c.id', array(
                    'titulo' => 'c.nombre'))
                ->joinleft(array(
                    'tc' => 'tipo_carrera'), 'c.id_tipo_carrera = tc.id', array(
                    'tipo_carrera' => 'tc.nombre'))
                ->joinleft(array(
                    'net' => 'nivel_estudio'), 'e.id_nivel_estudio_tipo = net.id', array(
                    'nivel_tipo_nombre' => 'net.nombre'
                ))
                ->where('id_postulante = ?', $id)
                ->where('en_curso IN(?,?)', array(
                    0,
                    1))
                ->where('id_nivel_estudio = ?', 9)
                ->order('inicio_ano DESC')
                ->order('inicio_mes DESC');

        $res = $this->getAdapter()->fetchAll($sql);
        foreach ($res as &$r) {
            if ($r['id_nivel_estudio'] == 9) {
                $r['titulo'] = $r['otro_estudio'];
            }
            if ($r['otro_carrera']) {
                $r['titulo'] = $r['otro_carrera'];
            }
        }

        $this->_cache->save($res, $cacheId, array(), $cacheEt);

        return $res;
    }

    public function datosParaEnteAdecsysPostulante($postulanteId)
    {
        $sql = $this->getAdapter()->select()
                ->from(
                        array(
                    'p' => $this->_name), array(
                    'doc_numero' => 'p.num_doc',
                    'tipo_doc' => new Zend_Db_Expr("CASE p.tipo_doc WHEN 'ce' THEN 'CEX' ELSE upper(p.tipo_doc) END"),
                    'nombres',
                    'ape_pat' => 'upper(apellido_paterno)',
                    'ape_mat' => 'upper(apellido_paterno)',
                    'telefono',
                    'razon_social' => "UPPER(CONCAT(apellido_paterno,' ',apellido_materno,', ',nombres))",
                    'razonComercial' => "UPPER(CONCAT(apellido_paterno,' ',apellido_materno,', ',nombres))",
                    'ubigeoId' => 'ifnull(ub.id_adecsys,1)')
                )
                ->join(
                        array(
                    'u' => 'usuario'), 'p.id_usuario = u.id', array(
                    'email' => 'u.email')
                )
                ->joinLeft(array(
                    'ub' => 'ubigeo'), 'ub.id = p.id_ubigeo', null)
                ->where('u.activo = 1')
                ->where('p.id =?', $postulanteId);

        $rs = $this->getAdapter()->fetchRow($sql);
        return $rs;
    }

    public function getPostulanteNuevoSolar()
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                        ->from(array(
                            'p' => $this->_name), array(
                            'idPos' => 'p.id',
                            'publico' => 'p.prefs_confidencialidad'))
                        ->where("p.solr != ?", 1)->limit('100');
        return $this->getAdapter()->fetchAll($sql);
    }

    public function obtenerPorSlug($slug)
    {
        return $this->fetchRow($this->select()
                                ->from($this->_name)
                                ->where('slug =?', $slug));
    }

    public function valperfil($idPostulante)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, array(
                    'prefs_confidencialidad'))
                ->where('id = ?', $idPostulante);
        $post = $db->fetchRow($sql);
        return $post['prefs_confidencialidad'];
    }

    public function getPostulantesDestacadosConVisitas($ini, $fin)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'p' => 'perfil_destacado'), array(
                    'id' => 'p.id_postulante'))
                ->joinInner(array(
                    'v' => 'visitas'), 'p.id_postulante = v.id_postulante', array())
                ->where("p.activo = 1")
                ->where("p.estado = 'pagado'")
                ->where("v.tipo = 1")
                ->where("v.id_aviso <> 0")
                ->where("v.fecha_busqueda < '$fin'")
                ->where("v.fecha_busqueda >= '$ini'");
        $sql2 = $db->select()
                ->from(array(
                    'p' => 'perfil_destacado'), array(
                    'id' => 'p.id_postulante'))
                ->joinInner(array(
                    'pos' => 'postulacion'), 'pos.id_postulante = p.id_postulante', array())
                ->joinInner(array(
                    'm' => 'mensaje'), 'pos.id = m.id_postulacion', array())
                ->where("p.activo = 1")
                ->where("p.estado = 'pagado'")
                ->where("pos.activo = 1")
                ->where("m.tipo_mensaje IN ('pregunta')")
                ->where("m.fh < '$fin'")
                ->where("m.fh >= '$ini'");
        $select = $db->select()
                ->union(array(
            $sql,
            $sql2));
        return $this->getAdapter()->fetchCol($select);
    }

    public function getNotificacionSemanalPorIdPostulante($id, $ini, $fin)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'p' => $this->_name), array(
                    'nombres' => 'p.nombres',
                    'path_foto' => 'p.path_foto',
                    'website' => 'p.website',
                    'presentacion' => 'p.presentacion',
                    'path_cv' => 'p.path_cv',
                    'to' => 'u.email',
                        //'visitas' => 'GROUP_CONCAT(DISTINCT v.id)',
                        // 'busquedas' => 'GROUP_CONCAT(DISTINCT b.id)',
                        // 'postulaciones' => 'GROUP_CONCAT(DISTINCT pos.id)',
                        // 'invitaciones' => 'GROUP_CONCAT(DISTINCT inv.id_anuncio_web)',
                        //  'leidas' => 'GROUP_CONCAT(DISTINCT l.id_aviso)',
                        // 'mensajes' => 'GROUP_CONCAT(DISTINCT r.id)'
                ))
                ->joinInner(array(
                    'u' => 'usuario'), "p.id_usuario = u.id", array())
                // ->joinLeft(array('v' => 'visitas'), "v.id_postulante = p.id AND v.tipo = 1 AND v.id_aviso = 0 AND v.fecha_busqueda < '$fin' AND v.fecha_busqueda >= '$ini'", array())
                // ->joinLeft(array('b' => 'visitas'), "b.id_postulante = p.id AND b.tipo = 2 AND b.fecha_busqueda < '$fin' AND b.fecha_busqueda >= '$ini'", array())
                //  ->joinLeft(array('pos' => 'postulacion'), "pos.id_postulante = p.id AND pos.invitacion = 0 AND pos.fh < '$fin' AND pos.fh >= '$ini'", array())
                //  ->joinLeft(array('inv' => 'postulacion'), "inv.id_postulante = p.id AND inv.invitacion = 1 AND inv.fh_invitacion < '$fin' AND inv.fh_invitacion >= '$ini'", array())
                // ->joinLeft(array('l' => 'visitas'), "l.id_postulante = p.id AND l.tipo = 1 AND l.id_aviso <> 0 AND l.fecha_busqueda < '$fin' AND l.fecha_busqueda >= '$ini'", array())
                ->joinLeft(array(
                    'po' => 'postulacion'), "po.id_postulante = p.id", array())
                //    ->joinLeft(array('r' => 'mensaje'), "r.id_postulacion = po.id AND r.tipo_mensaje = 'pregunta' AND r.respondido = 0 AND r.fh < '$fin' AND r.fh >= '$ini'", array())
                ->where("p.id = ?", $id);
        return $this->getAdapter()->fetchRow($sql);
    }

    public function getNotificacionSemanalVisitasPorIdPostulante($row)
    {

        $id = $row['id_postulante'];
        $ini = $row['fh_inicio'];
        $fin = $row['fh_fin'];
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'v' => 'visitas'), array(
                    'visitas' => 'COUNT(DISTINCT v.id)',
                ))
                ->where("v.id_postulante = ?", $id)
                ->where("v.tipo = 1")
                ->where("v.fecha_busqueda < ?", $fin)
                ->where("v.fecha_busqueda >= ?", $ini);
        return $this->getAdapter()->fetchOne($sql);
    }

    public function getNotificacionSemanalBusquedasPorIdPostulante($row)
    {

        $id = $row['id_postulante'];
        $ini = $row['fh_inicio'];
        $fin = $row['fh_fin'];
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'b' => 'visitas'), array(
                    'busquedas' => 'COUNT(DISTINCT b.id)',
                ))
                ->where("b.id_postulante = ?", $id)
                ->where("b.tipo = 2")
                ->where("b.fecha_busqueda < ?", $fin)
                ->where("b.fecha_busqueda >= ?", $ini);
        return $this->getAdapter()->fetchOne($sql);
    }

    public function getNotificacionSemanalPostulacionesPorIdPostulante($row)
    {

        $id = $row['id_postulante'];
        $ini = $row['fh_inicio'];
        $fin = $row['fh_fin'];
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'pos' => 'postulacion'), array(
                    'postulaciones' => 'COUNT(DISTINCT pos.id)',
                ))
                ->where("pos.id_postulante = ?", $id)
                ->where("pos.invitacion = 0")
                ->where("pos.fh < ?", $fin)
                ->where("pos.fh >= ?", $ini);
        return $this->getAdapter()->fetchOne($sql);
    }

    public function getNotificacionSemanalInvitacionesPorIdPostulante($row)
    {

        $id = $row['id_postulante'];
        $ini = $row['fh_inicio'];
        $fin = $row['fh_fin'];
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'inv' => 'postulacion'), array(
                    'invitaciones' => 'COUNT(DISTINCT inv.id)',
                ))
                ->where("inv.id_postulante = ?", $id)
                ->where("inv.invitacion = 1")
                ->where("inv.fh_invitacion < ?", $fin)
                ->where("inv.fh_invitacion >= ?", $ini);
        return $this->getAdapter()->fetchOne($sql);
    }

    public function getNotificacionSemanalLeidasPorIdPostulante($row)
    {

        $id = $row['id_postulante'];
        $ini = $row['fh_inicio'];
        $fin = $row['fh_fin'];
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'l' => 'visitas'), array(
                    'leidas' => 'COUNT(DISTINCT l.id)',
                ))
                ->where("l.id_postulante = ?", $id)
                ->where("l.id_aviso <> 0")
                ->where("l.tipo = 1")
                ->where("l.fecha_busqueda < ?", $fin)
                ->where("l.fecha_busqueda >= ?", $ini);

        return $this->getAdapter()->fetchOne($sql);
    }

    public function getNotificacionSemanalMensajesPorIdPostulante($row)
    {

        $id = $row['id_postulante'];
        $ini = $row['fh_inicio'];
        $fin = $row['fh_fin'];
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'r' => 'mensaje'), array(
                    'mensajes' => 'count(DISTINCT r.id)'
                ))
                ->joinInner(array(
                    'po' => 'postulacion'), 'po.id = r.id_postulacion', null)
                ->where("po.id_postulante = ?", $id)
                ->where("r.respondido = 0")
                ->where("r.tipo_mensaje = 'pregunta'")
                ->where("r.fh < ?", $fin)
                ->where("r.fh >= ?", $ini);

        return $this->getAdapter()->fetchOne($sql);
    }

    public function getDetalleNotificacionSemanalPorIdPostulante($id, $ini, $fin)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'p' => 'postulacion'), array(
                    'fecha' => 'p.fh',
                    'puesto' => 'a.puesto',
                    'rs' => 'a.empresa_rs',
                    'visto' => 'GROUP_CONCAT(DISTINCT v.id)',
                    'mensajes' => 'GROUP_CONCAT(DISTINCT m.id)'
                ))
                ->joinInner(array(
                    'a' => 'anuncio_web'), "a.id=p.id_anuncio_web AND a.estado = 'publicado' AND a.online = 1", array())
                ->joinLeft(array(
                    'v' => 'visitas'), "v.id_aviso = a.id AND v.tipo = 1 AND v.fecha_busqueda < '$fin' AND v.fecha_busqueda >= '$ini'", array())
                ->joinLeft(array(
                    'm' => 'mensaje'), "m.id_postulacion = p.id AND m.fh < '$fin' AND m.fh >= '$ini'", array())
                ->where("p.id_postulante = ?", $id)
                ->group("a.id")
                ->order("fecha DESC");
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getPerfilDestacadoPorVencer()
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'p' => 'perfil_destacado'), array(
                    'id_postulante',
                    'fh_inicio',
                    'fh_fin'))
                ->where("p.activo = 1")
                ->where("p.estado = 'pagado'")
                ->where("DATE(p.fh_fin) < CURDATE() + INTERVAL 7 DAY");
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getNotificacionFinalPorIdPostulante($row)
    {
        $id = $row['id_postulante'];
        $ini = $row['fh_inicio'];
        $fin = $row['fh_fin'];
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'p' => $this->_name), array(
                    'nombres' => 'p.nombres',
                    'cuenta' => 'pr.nombre',
                    'to' => 'u.email',
                        /* 'visitas' => 'GROUP_CONCAT(DISTINCT v.id_empresa)',
                          'busquedas' => 'GROUP_CONCAT(DISTINCT b.id)',
                          'postulaciones' => 'GROUP_CONCAT(DISTINCT pos.id)',
                          'invitaciones' => 'GROUP_CONCAT(DISTINCT inv.id)',
                          'procesos' => 'GROUP_CONCAT(DISTINCT pro.id_aviso)',
                          'mensajes' => 'GROUP_CONCAT(DISTINCT m.id)',
                          'responder' => 'GROUP_CONCAT(DISTINCT r.id)' */
                ))
                ->joinInner(array(
                    'u' => 'usuario'), "p.id_usuario = u.id", array())
                ->joinInner(array(
                    'pd' => 'perfil_destacado'), "pd.id_postulante = p.id", array())
                ->joinInner(array(
                    'pr' => 'producto'), "pd.id_producto = pr.id", array())
                /* ->joinLeft(array('v' => 'visitas'), "v.id_postulante = p.id AND v.tipo = 1 AND v.fecha_busqueda < '$fin' AND v.fecha_busqueda >= '$ini'", array())
                  ->joinLeft(array('b' => 'visitas'), "b.id_postulante = p.id AND b.tipo = 2 AND b.fecha_busqueda < '$fin' AND b.fecha_busqueda >= '$ini'", array())
                  ->joinLeft(array('pos' => 'postulacion'), "pos.id_postulante = p.id AND pos.invitacion = 0 AND pos.fh < '$fin' AND pos.fh >= '$ini'", array())
                  ->joinLeft(array('inv' => 'postulacion'), "inv.id_postulante = p.id AND inv.invitacion = 1 AND inv.fh_invitacion < '$fin' AND inv.fh_invitacion >= '$ini'", array())
                  ->joinLeft(array('pro' => 'visitas'), "pro.id_postulante = p.id AND pro.tipo = 1 AND pro.fecha_busqueda < '$fin' AND pro.fecha_busqueda >= '$ini'", array())
                  ->joinLeft(array('po' => 'postulacion'), "po.id_postulante = p.id", array())
                  ->joinLeft(array('m' => 'mensaje'), "m.id_postulacion = po.id AND m.tipo_mensaje IN ('mensaje','pregunta') AND m.fh < '$fin' AND m.fh >= '$ini'", array())
                  ->joinLeft(array('r' => 'mensaje'), "r.id_postulacion = po.id AND r.tipo_mensaje = 'pregunta' AND r.respondido = 0 AND r.fh < '$fin' AND r.fh >= '$ini'", array()) */
                ->where("p.id = ?", $id);
        return $this->getAdapter()->fetchRow($sql);
    }

    public function getPerfilDestacadoVenceHoy()
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'p' => 'perfil_destacado'), array(
                    'id',
                    'id_postulante'))
                ->where("p.activo = 1")
                ->where("p.estado = 'pagado'")
                ->where("DATE(p.fh_fin) < CURDATE()");
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getProximoPerfilDestacado($idPostulante)
    {

        $sql = $this->getAdapter()->select()
                ->from('perfil_destacado', array(
                    'id'))
                ->where('id_postulante = ?', $idPostulante)
                ->where('activo = ?', Application_Model_PerfilDestacado::EN_ESPERA)
                ->where('estado = ?', Application_Model_PerfilDestacado::ESTADO_PAGADO)
                ->limit(1);

        return $this->getAdapter()->fetchOne($sql);
    }

    //Obtiene el campo destacado si tiene
    public function getPostulanteData($idPostulante)
    {

        $sql = $this->getAdapter()->select()
                ->from('postulante', array(
                    'destacado'))
                ->where('id = ?', $idPostulante);

        $data = $this->getAdapter()->fetchOne($sql);

        return $data;
    }

    public function getNotificacionFinalPorIdPostulanteVisitas($row)
    {
        $id = $row['id_postulante'];
        $ini = $row['fh_inicio'];
        $fin = $row['fh_fin'];
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'v' => 'visitas'), array(
                    'visitas' => 'COUNT(DISTINCT v.id_empresa)',
                ))
                ->where("v.id_postulante = ?", $id)
                ->where("v.tipo = 1")
                ->where("v.fecha_busqueda < ?", $fin)
                ->where("v.fecha_busqueda >= ?", $ini);
        return $this->getAdapter()->fetchOne($sql);
    }

    public function getNotificacionFinalPorIdPostulanteBusquedas($row)
    {
        $id = $row['id_postulante'];
        $ini = $row['fh_inicio'];
        $fin = $row['fh_fin'];
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'b' => 'visitas'), array(
                    'busquedas' => 'COUNT(DISTINCT b.id)',
                ))
                ->where("b.id_postulante = ?", $id)
                ->where("b.tipo = 2")
                ->where("b.fecha_busqueda < ?", $fin)
                ->where("b.fecha_busqueda >= ?", $ini);
        return $this->getAdapter()->fetchOne($sql);
    }

    public function getNotificacionFinalPorIdPostulantePostulaciones($row)
    {
        $id = $row['id_postulante'];
        $ini = $row['fh_inicio'];
        $fin = $row['fh_fin'];
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'pos' => 'postulacion'), array(
                    'postulaciones' => 'COUNT(DISTINCT pos.id)',
                ))
                ->where("pos.id_postulante = ?", $id)
                ->where("pos.invitacion = 0")
                ->where("pos.fh < ?", $fin)
                ->where("pos.fh >= ?", $ini);
        return $this->getAdapter()->fetchOne($sql);
    }

    public function getNotificacionFinalPorIdPostulanteInvitaciones($row)
    {
        $id = $row['id_postulante'];
        $ini = $row['fh_inicio'];
        $fin = $row['fh_fin'];
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'inv' => 'postulacion'), array(
                    'invitaciones' => 'COUNT(DISTINCT inv.id)',
                ))
                ->where("inv.id_postulante = ?", $id)
                ->where("inv.invitacion = 1")
                ->where("inv.fh_invitacion < ?", $fin)
                ->where("inv.fh_invitacion >= ?", $ini);
        return $this->getAdapter()->fetchOne($sql);
    }

    public function getNotificacionFinalPorIdPostulanteProcesos($row)
    {
        $id = $row['id_postulante'];
        $ini = $row['fh_inicio'];
        $fin = $row['fh_fin'];
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'pro' => 'visitas'), array(
                    'procesos' => 'COUNT(DISTINCT pro.id_aviso)',
                ))
                ->where("pro.id_postulante = ?", $id)
                ->where("pro.tipo = 1")
                ->where("pro.fecha_busqueda < ?", $fin)
                ->where("pro.fecha_busqueda >= ?", $ini);
        return $this->getAdapter()->fetchOne($sql);
    }

    public function getNotificacionFinalPorIdPostulanteMensajes($row)
    {
        $id = $row['id_postulante'];
        $ini = $row['fh_inicio'];
        $fin = $row['fh_fin'];
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'po' => 'postulacion'), array(
                    'mensajes' => 'COUNT(DISTINCT m.id)',
                ))
                ->joinInner(array(
                    'm' => 'mensaje'), "m.id_postulacion = po.id AND m.tipo_mensaje IN ('mensaje','pregunta') AND m.fh < '$fin' AND m.fh >= '$ini'", array())
                ->where("po.id_postulante = ?", $id);
        return $this->getAdapter()->fetchOne($sql);
    }

    public function getNotificacionFinalPorIdPostulanteResponder($row)
    {
        $id = $row['id_postulante'];
        $ini = $row['fh_inicio'];
        $fin = $row['fh_fin'];
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'po' => 'postulacion'), array(
                    'responder' => 'COUNT(DISTINCT r.id)',
                ))
                ->joinInner(array(
                    'r' => 'mensaje'), "r.id_postulacion = po.id AND r.tipo_mensaje = 'pregunta' AND r.respondido = 0 AND r.fh < '$fin' AND r.fh >= '$ini'", array())
                ->where("po.id_postulante = ?", $id);
        return $this->getAdapter()->fetchOne($sql);
    }

    public function getSolrPostulanteEstudios($id)
    {
        $id = (int) $id;
        $adapter = $this->getAdapter();
        $sql = "
                SELECT DISTINCT CONCAT_WS('|',IFNULL(`e`.`otro_institucion`,''), 
                IFNULL(CAST(`e`.`en_curso` AS CHAR),''), 
                IFNULL(CAST(`e`.`inicio_mes` AS CHAR),''), IFNULL(CAST(`e`.`inicio_ano` AS CHAR),''), 
                IFNULL(CAST(`e`.`fin_mes` AS CHAR),''), IFNULL(CAST(`e`.`fin_ano` AS CHAR),''), 
                IFNULL(IF(`c`.`id`='15',`e`.`otro_carrera`,`c`.`nombre`),''), IFNULL(`ne`.`nombre`,''), IFNULL(`net`.`nombre`,''),
                IFNULL(CAST(`e`.`id_nivel_estudio` AS CHAR),'0'),IFNULL(CAST(`e`.`id_carrera` AS CHAR),'0'),
                IFNULL(CAST(`e`.`id_nivel_estudio_tipo` AS CHAR),'0')
                ) AS det_estudios
                FROM `estudio` AS `e` 
                LEFT JOIN `carrera` AS `c` ON e.id_carrera = c.id 
                LEFT JOIN `nivel_estudio` AS `ne` ON e.id_nivel_estudio = ne.id 
                LEFT JOIN `nivel_estudio` AS `net` ON e.id_nivel_estudio_tipo = net.id 
                WHERE (id_postulante = '$id') AND (id_nivel_estudio != 9) 
                ORDER BY `ne`.`peso` DESC, `net`.`peso` DESC         
                    ";
        $stm = $adapter->query($sql);
        $stmt = $stm->fetchAll();
        $data = array();
        foreach ($stmt as $row) {
            $tmp = array_values(array_slice($row, 0, 1));
            $data[] = $tmp[0];
        }
        return $data;
    }

    public function getSolrPostulanteExperiencias($id)
    {
        $id = (int) $id;
        $adapter = $this->getAdapter();
        $sql = "
                SELECT DISTINCT CONCAT_WS('|',IFNULL(`e`.`otra_empresa`,''), 
                IFNULL(COALESCE(e.otro_rubro,r.nombre),''), IFNULL(CAST(`e`.`inicio_mes` AS CHAR),''), 
                IFNULL(CAST(`e`.`inicio_ano` AS CHAR),''), IFNULL(CAST(`e`.`fin_mes` AS CHAR),''), 
                IFNULL(CAST(`e`.`fin_ano` AS CHAR),''), IFNULL(CAST(`e`.`en_curso` AS CHAR),''), 
                IFNULL(`e`.`comentarios`,''), IFNULL(`p`.`nombre`,''),
                IFNULL(CAST(`e`.`id_area` AS CHAR),'0'),IFNULL(CAST(`e`.`id_nivel_puesto` AS CHAR),'0')
                ) AS det_experiencias
                FROM `experiencia` AS `e` 
                INNER JOIN `puesto` AS `p` ON p.id = e.id_puesto 
                LEFT JOIN `rubro` AS `r` ON e.id_rubro = r.id 
                WHERE (e.id_postulante = '$id') 
                ORDER BY `inicio_ano` DESC, `inicio_mes` DESC
                    ";
        $stm = $adapter->query($sql);
        $stmt = $stm->fetchAll();
        $data = array();
        foreach ($stmt as $row) {
            $tmp = array_values(array_slice($row, 0, 1));
            $data[] = $tmp[0];
        }
        return $data;
    }

    public function getSolrPostulanteOtrosEstudios($id)
    {
        $id = (int) $id;
        $adapter = $this->getAdapter();
        $sql = "
                SELECT DISTINCT CONCAT_WS('|',IFNULL(`e`.`otro_institucion`,''), 
                IFNULL(CAST(`e`.`en_curso` AS CHAR),''),
                IFNULL(CAST(`e`.`inicio_mes` AS CHAR),''), IFNULL(CAST(`e`.`inicio_ano` AS CHAR),''), 
                IFNULL(CAST(`e`.`fin_mes` AS CHAR),''), IFNULL(CAST(`e`.`fin_ano` AS CHAR),''), 
                IFNULL(`e`.`otro_estudio`,''), IFNULL(`net`.`nombre`,'')) AS det_otros_estudios
                FROM `estudio` AS `e` 
                LEFT JOIN `nivel_estudio` AS `net` ON e.id_nivel_estudio_tipo = net.id 
                WHERE (id_postulante = '$id') AND (id_nivel_estudio = 9) 
                ORDER BY `fin_ano` DESC, `fin_mes` DESC, `inicio_ano` DESC, `inicio_mes` DESC
                    ";
        $stm = $adapter->query($sql);
        $stmt = $stm->fetchAll();
        $data = array();
        foreach ($stmt as $row) {
            $tmp = array_values(array_slice($row, 0, 1));
            $data[] = $tmp[0];
        }
        return $data;
    }

    public function getSolrPostulanteIdiomas($id)
    {
        $id = (int) $id;
        $adapter = $this->getAdapter();
        $sql = "
                SELECT DISTINCT CONCAT_WS('|',IFNULL(`di`.`id_idioma`,''), 
                IFNULL(`di`.`nivel_lee`,'')) AS det_idiomas
                FROM `dominio_idioma` AS `di` 
                WHERE (id_postulante = '$id')
                    ";
        $stm = $adapter->query($sql);
        $stmt = $stm->fetchAll();
        $data = array();
        foreach ($stmt as $row) {
            $tmp = array_values(array_slice($row, 0, 1));
            $data[] = $tmp[0];
        }
        return $data;
    }

    public function getSolrPostulanteProgramas($id)
    {
        $id = (int) $id;
        $adapter = $this->getAdapter();
        $sql = "
                SELECT DISTINCT CONCAT_WS('|',IFNULL(`pc`.`nombre`,''), 
                IFNULL(`dpc`.`nivel`,''),
                IFNULL(CAST(`dpc`.`id_programa_computo` AS CHAR),'0')
                ) AS det_programas
                FROM `programa_computo` AS `pc` 
                INNER JOIN `dominio_programa_computo` AS `dpc` ON pc.id = dpc.id_programa_computo 
                WHERE (id_postulante = '$id')
                    ";
        $stm = $adapter->query($sql);
        $stmt = $stm->fetchAll();
        $data = array();
        foreach ($stmt as $row) {
            $tmp = array_values(array_slice($row, 0, 1));
            $data[] = $tmp[0];
        }
        return $data;
    }

    public static function verificarDocRepetido($numDoc, $tipoDoc, $idPostulante = null)
    {
        $post = new Application_Model_Postulante();
        $sql = $post->select()
                ->from('postulante', 'id')
                ->where('tipo_doc = ?', $tipoDoc)
                ->where('num_doc = ?', $numDoc)
                ->limit('1');
        if ($idPostulante) {
            $sql = $sql->where('id != ?', $idPostulante);
        }
        $sql = $sql->limit('1');

        $res = $post->getAdapter()->fetchAll($sql);

        return (count($res) > 0) ? 1 : 0;
    }

    public function getLogros($id)
    {
        $sql = $this->getAdapter()->select()
                ->from(array(
                    'lo' => 'logros'), array(
                    'id' => 'lo.id',
                    'logro' => 'lo.logro',
                    'institucion' => 'lo.institucion',
                    'ano' => 'lo.ano',
                    'mes' => 'lo.mes',
                    'descripcion' => 'lo.descripcion',
                ))
                ->where('id_postulante = ?', $id);
        $rs = $this->getAdapter()->fetchAll($sql);
        return $rs;
    }

    public function getSolrPostulanteAptitudes($id)
    {
        $id = (int) $id;
        $adapter = $this->getAdapter();
        $sql = "
            SELECT `ap`.`id_aptitud` AS det_aptitudes FROM aptitudes_postulante ap 
            WHERE ap.id_postulante='$id'
                ";
        $stm = $adapter->query($sql);
        $stmt = $stm->fetchAll();
        $data = array();
        foreach ($stmt as $row) {
            $tmp = array_values(array_slice($row, 0, 1));
            $data[] = $tmp[0];
        }
        return $data;
    }

    public function getLogrosPostulante($idpostulante)
    {
        $chooseMonth = " CASE l.mes
                WHEN '1' THEN 'Enero'
                WHEN '2' THEN 'Febrero'
                WHEN '3' THEN 'Marzo'
                WHEN '4' THEN 'Abril'
                WHEN '5' THEN 'Mayo'
                WHEN '6' THEN 'Junio'
                WHEN '7' THEN 'Julio'
                WHEN '8' THEN 'Agosto'
                WHEN '9' THEN 'Setiembre'
                WHEN '10' THEN 'Octubre'
                WHEN '11' THEN 'Noviembre'
                WHEN '12' THEN 'Diciembre'
                END ";

        $sql = $this->getAdapter()->select()
                ->from(array(
                    'l' => 'logros'), array(
                    'id_logros' => 'l.id',
                    'txtPrize' => 'l.logro',
                    'txtInstitution' => 'l.institucion',
                    'txtDateAchievement' => 'l.ano',
                    'selDate' => 'l.mes',
                    'txtDescription' => 'l.descripcion',
                    'txtMonth' => new Zend_Db_Expr($chooseMonth)
                ))
                ->where('id_postulante =?', $idpostulante);
        return $this->getAdapter()->fetchAll($sql);
    }

    public static function getAptitusdesIdPostulante($idPostulante)
    {
        $db = new App_Db_Table_Abstract();
        $sql = $db->getAdapter()->select()
                ->from(array(
                    'a' => 'aptitudes'), array(
                    'id' => 'a.id',
                    'mostrar' => 'a.nombre'))
                ->joinInner(array(
                    'ap' => 'aptitudes_postulante'), 'a.id=ap.id_aptitud', array())
                ->where('ap.id_postulante', $idPostulante);
        $rs = $db->getAdapter()->fetchAll($sql);


        return$rs;
    }

    public function getPostulantesSinSexo($limit)
    {
        $adapter = $this->getAdapter();
        $sql = "
            SELECT p.id,IF(LOCATE(' ',TRIM(nombres))>0, SUBSTR(TRIM(nombres),1,LOCATE(' ',TRIM(nombres)-1)), TRIM(nombres)) nombre
            FROM postulante p 
            JOIN usuario u ON p.id_usuario=u.id 
            WHERE p.sexo IS NULL 
            AND p.nombres NOT IN ('******', '-', '--', '---', '------', '.', '..', '.......', '0', 'OOO','1', 'A', 'a', 'aa', 'AA', 'aaa','fdjkjfe','jjjjjjjjjjjjjjjjjjjjj','p`´op','xxxxx','xxx','sdfffffffffffffffffffffffffff','nnn','{ñ{ñ','pppp','thjn','jjnkjnknkj','ñ^^**','XXXXXX','vvvvvv','ee','Atssss') 
            AND u.activo=1 
            GROUP BY p.id 
            HAVING TRIM(nombre)<>''
            LIMIT $limit
        ";
        $rs = $adapter->query($sql);
        return $rs;
    }

    public static function EmailPostulantedoc($numDocl)
    {
        $db = new App_Db_Table_Abstract();
        $sql = $db->getAdapter()->select()
                ->from(array(
                    'p' => 'postulante'), array(
                    'email' => 'u.email'))
                ->joinInner(array(
                    'u' => 'usuario'), "p.id_usuario=u.id", array())
                ->where('num_doc = ?', $numDocl)
                ->limit('1');
        $res = $db->getAdapter()->fetchOne($sql);
        return $res;
    }

    public function registerFb($facebookUser)
    {
        $valuesPostulante = array();
        $valuesPostulante['id_usuario'] = $facebookUser['idUser'];
        $valuesPostulante['nombres'] = (!empty($facebookUser['first_name'])) ? $facebookUser['first_name'] : '';
        $apellidos = $facebookUser['last_name'];
        $valuesPostulante['apellidos'] = $apellidos;
        $arrApellidos = explode(' ', $apellidos, 2);
        $valuesPostulante['apellido_paterno'] = (!empty($arrApellidos[0])) ? $arrApellidos[0] : '';
        $valuesPostulante['apellido_materno'] = (!empty($arrApellidos[1])) ? $arrApellidos[1] : '';
        $gender = array(
            'male' => 'M',
            'female' => 'F');
        $valuesPostulante['sexo'] = $gender[$facebookUser['gender']];
        $slug = $this->_crearSlug($valuesPostulante, $facebookUser['idUser']);
        $valuesPostulante['disponibilidad_mudarse'] = '0';
        $valuesPostulante['prefs_confidencialidad'] = '0';
        $valuesPostulante['prefs_emailing_avisos'] = '0';
        $valuesPostulante['prefs_emailing_info'] = '0';
        $valuesPostulante['ultima_actualizacion'] = date('Y-m-d H:i:s');
        $valuesPostulante['slug'] = $slug;
        $valuesPostulante['path_foto'] = '';
        $valuesPostulante['path_foto1'] = '';
        $valuesPostulante['path_foto2'] = '';
        $lastIdPostulante = $this->insert($valuesPostulante);
        return $lastIdPostulante;
    }

    private function _crearSlug($valuesPostulante, $lastId)
    {
        $slugFilter = new App_Filter_Slug(
                array(
            'field' => 'slug',
            'model' => new Application_Model_Postulante)
        );

        $slug = $slugFilter->filter(
                $valuesPostulante['nombres'] . ' ' .
                $valuesPostulante['apellidos'] . ' ' .
                substr(md5($lastId), 0, 8)
        );
        return $slug;
    }

    public function getCantPostulantes()
    {

        $sql = $this->_db->select()
                ->from($this->_name, array('total'=>'COUNT(id)')
                );
        $result = $this->_db->fetchOne($sql);
        return $result;
    }

}
