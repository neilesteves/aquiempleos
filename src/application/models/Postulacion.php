<?php


class Application_Model_Postulacion extends App_Db_Table_Abstract
{

    protected $_name = "postulacion";
    private $_model = null;
    
    CONST POSTULACION_INACTIVA          = 0;
    CONST POSTULACION_ACTIVA            = 1;    
    CONST POSTULACION_BLOQUEADA         = 2;
    
    CONST POSTULACION_DESCARTADA        = 1;    
    CONST POSTULACION_NO_DESCARTADA     = 0;    
    
    CONST POSTULACION_APTITUS   ="aptitus";
    CONST POSTULACION_API       ="api";
    CONST POSTULACION_REFERIDO  ="referido";
    
    CONST ES_NUEVO          = 1;
    
    CONST ES_REFERENCIADO   = 1;
    CONST NO_ES_REFENCIADO  = 0;
    
    CONST NO_INVITADO       = 0;
    CONST INVITADO          = 1;
            

    public function init()
    {
        parent::init();
    }

    public function __construct()
    {
        parent::__construct();
        $cparts = explode('_', __CLASS__);
        $this->_model = $cparts[2];
    }

    /**
     * Retorna la lista de areas disponibles en un puesto.
     * 
     * @return array
     * 
     */
    private function _setCols($getCols)
    {
        $cols = $this->_getCols();
        if (count($getCols) > 0) {
            $cols = array_intersect($this->_getCols(), $getCols);
        }
        return $cols;
    }

    public function getOrigenPostulacion($paginator)
    {
        $valorOrigenPostulacion = array();

        foreach ($paginator as $item):
            if ((int) $item['referenciado'] == 1) {
                $valorOrigenPostulacion[] = 'referido';
            } else {
                $valorOrigenPostulacion[] = $item["origen_postulacion"];
            }
        endforeach;

        $finalArrayOrigen = array_values(array_unique($valorOrigenPostulacion));
        $countPag = count($finalArrayOrigen);
        $newPag = array();

        for ($i = 0; $i < (int) $countPag; $i++) {
            $newPag[] = array(
                "id" => $finalArrayOrigen[$i],
                "nombre" => ucwords($finalArrayOrigen[$i])
            );
        }

        return $newPag;
    }

    public function getPostulaciones($idPostulante, $limit = null, $col = '',
        $ord = '')
    {
        $col = $col == '' ? 'p.fh' : $col;
        $ord = $ord == '' ? 'DESC' : $ord;
        $anioIni=  date('Y');
        $aniofin=  date('Y') - 2;
        $sql = $this->_db->select()
            ->from(
                array('p' => $this->_name),
                array('fecha' => 'p.fh',
                'idpostulacion' => 'p.id',
                'puesto' => 'aw.puesto',
                'empresa' => 'aw.empresa_rs',
                'id_empresa' => 'aw.id_empresa',
                'logoanuncio' => 'aw.logo',
                'leidos' => 'p.msg_leidos',
                'noleidos' => 'p.msg_no_leidos',
                'norespondidos' => 'p.msg_por_responder',
                'p.id_anuncio_web' => 'aw.id',
                'slugaviso' => 'aw.slug',
                 'mensaje' => 'p.activo',
                'es_nuevo' => 'p.es_nuevo',
                'urlaviso' => 'aw.url_id',
                'total_postulantes' => 'aw.ntotal',
                'fecha_creacion' => 'aw.fh_creacion',
                'online' => 'aw.online',
                'mostrar_empresa' => 'aw.mostrar_empresa'
                )
            )
            ->joinInner(
                array('aw' => 'anuncio_web'), 'p.id_anuncio_web = aw.id and p.id_postulante = '.$idPostulante,null
            )
            ->joinInner(
                array('u' => 'ubigeo'), 'u.id = aw.id_ubigeo',
                array('ubicacionslug' => new Zend_Db_Expr("REPLACE(LOWER(u.nombre),' ','-')"))
            )
            ->joinLeft(
                    array('e' => 'empresa'), 'aw.id_empresa = e.id', array('empresaslug'=>'slug_empresa')
            )
            ->where('p.id_postulante = ?', $idPostulante)
            ->where("YEAR(p.fh) <= ?",$anioIni)
            ->where("YEAR(p.fh) > ?", $aniofin)
           // ->where("aw.fh_vencimiento_proceso > CURDATE()")
            ->where('p.activo = 1') //add v2.0
            ->order(sprintf('%s %s', $col, $ord));
        if (!is_null($limit)) {
            $sql = $sql->limit($limit);
        }
        return $sql;
    }

    public function getPostulacionesByEmpresaYPostulante($idPostulante,
        $idEmpresa, $col = '', $ord = '')
    {
        $col = $col == '' ? 'p.fh' : $col;
        $ord = $ord == '' ? 'DESC' : $ord;
        $sql = $this->_db->select()
            ->from(
                array('p' => $this->_name),
                array('fecha' => 'p.fh',
                'idpostulacion' => 'p.id',
                'puesto' => 'aw.puesto',
                'empresa' => 'aw.empresa_rs',
                'leidos' => 'p.msg_leidos',
                'noleidos' => 'p.msg_no_leidos',
                'norespondidos' => 'p.msg_por_responder',
                'p.id_anuncio_web' => 'aw.id',
                'slugaviso' => 'aw.slug',
                'urlaviso' => 'aw.url_id'
                )
            )
            ->joinInner(
                array('aw' => 'anuncio_web'), 'p.id_anuncio_web = aw.id'
            )
            ->where('p.id_postulante = ?', $idPostulante)
            ->where('aw.id_empresa = ?', $idEmpresa)
            ->where("aw.fh_vencimiento_proceso > CURDATE()")
            ->order(sprintf('%s %s', $col, $ord));
        return $this->_db->fetchAll($sql);
    }

    public function UpdateMsjsLeidos($idUsuario, $idPostulacion)
    {
        //Obtener los leidos y no leidos de una postulación
        $sql = $this->_db->select()
            ->from(
                array('m' => 'mensaje'),
                array('msg_leidos' => 'sum(leido)',
                'msg_no_leidos' => 'sum(if(leido = 0 , 1,0))',
                'msg_por_responder' => "sum(if(respondido = 0 and tipo_mensaje='pregunta', 1,0))"
                )
            )
            ->where('id_postulacion = ?', $idPostulacion)
            ->where('para = ?', $idUsuario)
            ->where('notificacion = ?', 0);
        $row = $this->_db->fetchRow($sql);
        //echo $sql->assemble($sql);         exit;
        //Actualizar en la tabla postulación
        $msgleidos = $row["msg_leidos"];
        $msgnoleidos = $row["msg_no_leidos"];
        $msgporresponder = $row["msg_por_responder"];
        /* $zl = new ZendLucene();
          $zl->updateIndexPostulaciones($idPostulacion, "msgnoleidos", $msgnoleidos);
          $zl->updateIndexPostulaciones($idPostulacion, "msgporresponder", $msgporresponder);
         */
        $where = $this->_db->quoteInto('id = ?', $idPostulacion);
        return $this->update($row, $where);
    }

    public function getPaginator($idPostulante, $col, $ord)
    {
        $limit = $this->_config->postulacionesPostulante->paginador->items;
        $ord = ($ord == 'ASC' || $ord == 'DESC') ? $ord : 'DESC';
        $colMap = array(
            'aviso' => 'aw.puesto',
            'empresa' => 'aw.empresa_rs',
            'fecha' => 'p.fh',
            'mensajes' => 'p.msg_por_responder'
        );
        $column = array_key_exists($col, $colMap) ? $colMap[$col] : 'p.fh';
        $p = Zend_Paginator::factory($this->getPostulaciones($idPostulante,
                    null, $column, $ord));
        return $p->setItemCountPerPage($limit);
    }

    public function hasPostulado($idAviso, $idPostulante)
    {
        if ($idPostulante == null) {
            return false;
        }

        $sql = $this->_db->select()
            ->from(
                array('p' => $this->_name), array('fh' => 'fh')
            )
            ->where('p.id_postulante = ?', $idPostulante)
            ->where('p.id_anuncio_web = ?', $idAviso)
            ->where('p.activo <>?', self::POSTULACION_INACTIVA);

        $rs = $this->_db->fetchOne($sql);
      
        
        if ($rs) {
            return $rs;
        } else {
            return false;
        }
    }

    public function getPostulacionByIdAvisoandEmail($idAviso, $email)
    {
        if ($email == null) {
            return false;
        }

        $sql = $this->getAdapter()->select()
            ->from(
                array('ps' => $this->_name), array('fh' => 'fh')
            )
            ->joinInner(array('p' => 'postulante'), 'ps.id_postulante = p.id',
                array())
            ->joinInner(array('u' => 'usuario'), 'u.id = p.id_usuario', array())
            ->where('u.email = ?', $email)
            ->where('ps.id_anuncio_web = ?', $idAviso)
            ->where('u.rol = ?', Application_Model_Usuario::ROL_POSTULANTE)
            ->where('ps.activo <>?', self::POSTULACION_INACTIVA);
        return $this->getAdapter()->fetchRow($sql, array(), Zend_Db::FETCH_OBJ);
    }

    public function hasPostuladoByUrlId($urlId, $idPostulante)
    {
        if ($idPostulante == null) {
            return false;
        }
        $sql = $this->_db->select()
            ->from(
                array('p' => $this->_name), array('fh' => 'fh')
            )
            ->joinInner(
                array('aw' => 'anuncio_web'), 'p.id_anuncio_web = aw.id'
            )
            ->where('p.id_postulante = ?', $idPostulante)
            ->where('aw.url_id = ?', $urlId);
        $rs = $this->_db->fetchOne($sql);
        if ($rs) {
            return $rs;
        } else {
            return false;
        }
    }

    public function getPostulacion($idpostulacion)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name)
            ->where('id = ?', $idpostulacion);
        return $this->getAdapter()->fetchRow($sql);
    }

    public function getPostulacionPostulante($idpostulacion)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array("pos" => $this->_name)
            )
            ->joinInner(
                array('p' => 'postulante'), "pos.id_postulante=p.id",
                array(
                "idusuario" => "p.id_usuario",
                "nombres" => "p.nombres",
                "apellidos" => "p.apellidos"
                )
            )
            ->where('pos.id = ?', $idpostulacion);
        return $this->getAdapter()->fetchRow($sql);
    }

    /**
     * Extiende las postulacionesd e un anuncio web a otro
     * 
     * @param int $idAnuncioOld
     * @param int $idAnuncioNew
     */
    public function extenderPostulaciones($idAnuncioOld, $idAnuncioNew)
    {
        $idanuncioAnterior = $idAnuncioOld;
        
        //Obtener postulantes par actualizar Match cuando se extiende el aviso
        $postulantes = $this->obtenerPostulantesxAviso($idAnuncioOld);
        
        $where = $this->getAdapter()->quoteInto("id_anuncio_web = ?",
            $idanuncioAnterior);
        $this->update(
            array('id_anuncio_web' => $idAnuncioNew), $where
        );
        
        if (!is_null($postulantes)) {
            $registroExtra = new App_Controller_Action_Helper_RegistrosExtra;
            foreach ($postulantes as $value) {//Cálcula match
                $registroExtra->ActualizarPostulacionAvisoAmpliado($value['id_postulante'],$idAnuncioNew);
            }
            
        }

    }

    /**
     * 
     * @param type $id
     * @return objects
     */
    public function listPostulantesByAviso($id)
    {
        $where = $this->getAdapter()->quoteInto('ps.id_anuncio_web = ?', $id);


        $whereReferenciado = $this->getAdapter()->quoteInto(
            'r.id_anuncio_web = ? 
            AND 
            r.estado = ' . Application_Model_Referenciado::ESTADO_NO_POSTULO . '
            AND 
            r.tipo = ' . Application_Model_Referenciado::TIPO_REFERENCIADO . '
            AND 
            u.rol = "' . Application_Model_Usuario::ROL_POSTULANTE . '"',
            $id);

        $whereReferido = $this->getAdapter()->quoteInto(
            'r.id_anuncio_web = ? 
            AND 
            r.estado = ' . Application_Model_Referenciado::ESTADO_NO_POSTULO . '
            AND 
            r.tipo = ' . Application_Model_Referenciado::TIPO_REFERIDO,
            $id);


//(e.principal > 0 OR e.principal IS NULL) AND (u.rol = 'postulante') AND (r.tipo = 2) AND (r.id_anuncio_web = '764258')
        $sql = "SELECT 
                        p.nombres,
                    CONCAT(p.apellido_paterno,' ',p.apellido_materno) AS `apellidos`,
                        u.email,
                        p.num_doc AS dni,
                        p.sexo,
                        p.celular as 'telefono celular',
                        p.telefono as 'telefono fijo',
                        cast((FLOOR(DATEDIFF(CURDATE(), p.fecha_nac) / 365))
                            AS CHAR (3)) as edad,
                        (select 
                                ne.nombre
                            from
                                estudio as e
                                    Inner Join
                                nivel_estudio as ne ON e.id_nivel_estudio = ne.id
                            where
                                e.id_postulante = p.id
                            ORDER BY ne.peso DESC, e.id DESC
                            limit 1) AS `nivel estudio`,
                        (SELECT IF(c.nombre='Otros' OR c.nombre IS NULL,IF(e.otro_carrera='' OR e.otro_carrera IS NULL,e.otro_estudio,e.otro_carrera),c.nombre ) 
                        FROM estudio AS e INNER JOIN nivel_estudio AS ne ON e.id_nivel_estudio = ne.id LEFT JOIN carrera c ON e.id_carrera = c.id
                        WHERE e.id_postulante = p.id ORDER BY ne.peso DESC, e.id DESC LIMIT 1
                        )  AS carrera,
                        (select 
                                e.otro_institucion
                            from
                                estudio as e
                                    Inner Join
                                nivel_estudio as ne ON e.id_nivel_estudio = ne.id
                            where
                                e.id_postulante = p.id
                            order by ne.peso DESC
                            limit 1) as 'nombre de la institución',
                        (max_idioma(p.id)) as 'idioma y nivel',
                        ub.display_name as 'lugar de residencia',
                        cp.nombre AS 'etapas del proceso',
                        (select 
                            cast(concat('|',
                                        dpc.id_programa_computo,
                                        '|',
                                        ' ',
                                        dpc.nivel)
                                as char) as 'programa y nivel'
                        from
                            dominio_programa_computo as dpc
                            WHERE
                                dpc.id_postulante = p.id
                            ORDER BY FIELD(dpc.nivel,
                                    'Basico',
                                    'Intermedio',
                                    'Avanzado') DESC
                            LIMIT 1) as 'programas y nivel',
                        DATE_FORMAT(ps.fh, '%d-%m-%Y')  as 'fecha de postulación',
                        (SELECT otra_empresa FROM experiencia WHERE id_postulante = p.id 
                        ORDER BY en_curso DESC,fin_ano DESC,fin_mes DESC  LIMIT 1) AS 'ultima empresa donde trabajó',
                        (SELECT np.nombre FROM experiencia e  INNER JOIN nivel_puesto np ON 
                         np.id = e.id_nivel_puesto WHERE e.id_postulante = p.id  
                         ORDER BY e.en_curso DESC,e.fin_ano DESC,e.fin_mes DESC LIMIT 1) AS 'nivel de puesto',
                        (SELECT IF(pu.nombre='OTROS' OR pu.nombre IS NULL,ex.otro_puesto,pu.nombre ) AS nombre_puesto FROM experiencia ex 
                        LEFT JOIN  puesto pu ON ex.id_puesto=pu.id WHERE ex.id_postulante = p.id 
                        ORDER BY ex.en_curso DESC, ex.fin_ano DESC,ex.fin_mes DESC LIMIT 1  
                        ) AS 'nombre de puesto'
                    FROM
                        postulacion AS ps
                            INNER JOIN
                        postulante AS p ON ps.id_postulante = p.id
                    INNER JOIN
                        ubigeo as ub ON p.id_ubigeo = ub.id 
                            INNER JOIN
                        usuario as u ON u.id = p.id_usuario
                            LEFT JOIN
                        categoria_postulacion AS cp ON ps.id_categoria_postulacion = cp.id
                    WHERE
                    ({$where}) AND ps.activo = " . self::POSTULACION_ACTIVA . " AND ps.descartado = " . self::POSTULACION_NO_DESCARTADA . "
                    UNION 
                    SELECT                        
                        r.nombre as 'nombres',
                        r.apellidos,
                        r.email as 'email',
                        '' AS dni,
                        r.sexo,
                        '' as 'telefono celular',
                        r.telefono as 'telefono fijo',
                        '' as 'edad',
                        '' as 'nivel de estudio',
                        '' as 'carrera',
                        '' as 'nombre de institucion',
                        '' as 'idioma y nivel',
                        '' as 'lugar de residencia',
                        'Referido' as 'etapa de proceso',
                        '' as 'programas y nivel',
                        '' as 'fecha de postulacion',
                        '' AS 'ultima empresa donde trabajó',
                        '' AS 'nivel de puesto',
                        '' AS 'nombre de puesto'
                    FROM
                        referenciado as r
                    WHERE
                        ({$whereReferido})
                    UNION 

                    SELECT 
                        r.nombre as 'nombres',
                        r.apellidos,
                        r.email AS 'email', 
                        p.num_doc AS dni, 
                        r.sexo,
                        p.celular as 'telefono celular',
                        p.telefono as 'telefono fijo',
                        FLOOR(DATEDIFF(CURDATE(), p.fecha_nac) / 365) AS edad,
                        ne.nombre AS 'nivel de estudio',
                        (SELECT 
                       IF(c.nombre='Otros' OR c.nombre IS NULL,IF(e.otro_carrera='' OR e.otro_carrera IS NULL,e.otro_estudio,e.otro_carrera),c.nombre ) 
                       FROM estudio AS e INNER JOIN nivel_estudio AS ne ON e.id_nivel_estudio = ne.id LEFT JOIN carrera c ON e.id_carrera = c.id
                        WHERE e.id_postulante = p.id ORDER BY ne.peso DESC, e.id DESC LIMIT 1
                        )  AS carrera,
                        e.otro_institucion as 'nombre de la institución',
                        (max_idioma(p.id)) as 'idioma y nivel',
                        ub.display_name as 'lugar de residencia',
                        'Referido' as 'etapa de proceso',
                        (select 
                            cast(concat('|',
                                        dpc.id_programa_computo,
                                        '|',
                                        ' ',
                                        dpc.nivel)
                                as char) as 'programa y nivel'
                        from
                            dominio_programa_computo as dpc
                            WHERE
                                dpc.id_postulante = p.id
                            ORDER BY FIELD(dpc.nivel,
                                    'Basico',
                                    'Intermedio',
                                    'Avanzado') DESC
                            LIMIT 1) as 'programas y nivel',
                        ''  as 'fecha de postulación',
                         (SELECT otra_empresa FROM experiencia WHERE id_postulante = p.id 
                        ORDER BY id DESC LIMIT 1) AS 'ultima empresa donde trabajó',
                        (SELECT np.nombre FROM experiencia e  INNER JOIN nivel_puesto np ON 
                         np.id = e.id_nivel_puesto WHERE e.id_postulante = p.id  
                         ORDER BY e.id DESC LIMIT 1) AS 'nivel de puesto',
                        (SELECT IF(pu.nombre='OTROS' OR pu.nombre IS NULL,ex.otro_puesto,pu.nombre ) AS nombre_puesto FROM experiencia ex 
                        LEFT JOIN  puesto pu ON ex.id_puesto=pu.id WHERE ex.id_postulante = p.id 
                        ORDER BY ex.en_curso DESC, ex.fin_ano DESC,ex.fin_mes DESC LIMIT 1
                         ) AS 'nombre de puesto'
                    FROM
                        referenciado AS r
                            LEFT JOIN
                        usuario AS u ON r.email = u.email
                            LEFT JOIN
                        postulante AS p ON p.id_usuario = u.id
                            LEFT JOIN
                        ubigeo AS ub ON p.id_ubigeo = ub.id
                            LEFT JOIN
                        estudio AS e ON e.id_postulante = p.id
                            LEFT JOIN
                        nivel_estudio AS ne ON e.id_nivel_estudio = ne.id
                    WHERE
                    ({$whereReferenciado})
                    AND (e.principal > 0 OR e.principal IS NULL)";
        return $this->getAdapter()->fetchAll($sql, Zend_Db::FETCH_OBJ);
    }

    /**
     * Retorna la cantidad de postulantes por anuncio web
     * 
     * @param int $id
     */
    public function getPostulantesByAviso($id, $opcion = "", $cache = false)
    {
        /* $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
          $cacheId = $this->_model.'_'.__FUNCTION__."_".$id;
          if ($this->_cache->test($cacheId) && $cache) {
          return $this->_cache->load($cacheId);
          } */
        $sql = $this->getAdapter()->select()
            ->from(
                array('p' => 'postulacion'), array('total' => 'count(id)')
            )
            ->where('p.id_anuncio_web
        = ?', $id)
            ->where('p.activo = ?', 1)
            ->where('p.descartado
        = ?', 0);
        if ($opcion != "")
                $sql = $sql->where("p.id_categoria_postulacion IS NULL");
     //   echo $sql;exit;
        $result = $this->getAdapter()->fetchOne($sql);
        //$this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    /**
     * Retorna la cantidad de invitaciones por anuncio web.
     * 
     * @param int $id
     */
    public function getInvitacionesByAviso($id)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('p' => 'postulacion'),
                array('total' => 'sum(p.invitacion)')
            )
            ->where('p.id_anuncio_web
        = ?', $id)
            ->where('p.invitacion = ?', 1);
        return $this->getAdapter()->fetchOne($sql);
    }

    /**
     * Retorna la cantidad de nuevas postulaciones por anuncio web
     * 
     * @param int $id
     */
    public function getNuevasPostulacionesByAviso($id)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('p' => 'postulacion'), array('total' => 'sum(p.es_nuevo)')
            )
            ->where('p.id_anuncio_web
        = ?', $id)
            ->where('p.activo = ?', 1)
            ->where('p.descartado
        = ?', 0);
        return $this->getAdapter()->fetchOne($sql);
    }

    /**
     * Retorna la cantidad de mensajes no leidos por anuncio web
     * 
     * @param int $id
     */
    public function getMsgRsptNoLeidosByAviso($id)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('m1' => 'mensaje'),
                array('msjrespnoleido' => 'count(m2.leido)')
            )
            ->joinInner(array('m2' => 'mensaje'), 'm2.padre
        = m1.id',
                array())
            ->joinInner(array('p' => 'postulacion'),
                'p.id
        = m1.id_postulacion', array())
            ->where('m2.leido
        = ?', 0)
            ->where('p.activo = ?', 1)
            ->where('p.id_anuncio_web
        = ?', $id);
         //   echo $sql;exit;
           // echo $sql;exit;
        return $this->getAdapter()->fetchOne($sql);
    }

    public function getMsgRsptNoLeidosXPostulacion($id)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('m1' => 'mensaje'),
                array('msjrespnoleido' => 'count(m2.leido)')
            )
            ->joinInner(array('m2' => 'mensaje'), 'm2.padre
        = m1.id',
                array())
            ->where('m2.leido = ?', 0)
            ->where('m1.id_postulacion
        = ?', $id);

        return $this->getAdapter()->fetchOne($sql);
    }

    /*
     * Funcion para descartar postulacion
     */

    public function descartarPostulacion($idPostulacion)
    {
        $where = $this->getAdapter()->quoteInto('id
        = ?', $idPostulacion);
        $arreglo["descartado"] = "1";
        $sql = $this->update(
            $arreglo, $where
        );

        $historico = new Application_Model_HistoricoPs();
        $data["id_postulacion"] = $idPostulacion;
        $data["evento"] = "descartar";
        $data["fecha_hora"] = date("Y-m-d H:i");
        $data["descripcion"] = "Descartar";
        $idHistorico = $historico->insert($data);
        return $idHistorico;
    }

    public function restituirPostulacion($idPostulacion)
    {
        $where = $this->getAdapter()->quoteInto('id
        = ?', $idPostulacion);
        $arreglo["descartado"] = "0";
        $sql = $this->update(
            $arreglo, $where
        );

        $historico = new Application_Model_HistoricoPs();
        $data["id_postulacion"] = $idPostulacion;
        $data["evento"] = "restituir";
        $data["fecha_hora"] = date("Y-m-d H:i");
        $data["descripcion"] = "Restituir";
        $historico->insert($data);
    }

    /*
     * Funcion para descartar postulacion
     */

    public function moveraetapaPostulacion($idPostulacion, $etapa, $descripcion)
    {
        $where = $this->getAdapter()->quoteInto('id
        = ?', $idPostulacion);
        $arreglo["id_categoria_postulacion"] = $etapa;
        $sql = $this->update(
            $arreglo, $where
        );

        $historico = new Application_Model_HistoricoPs();
        $data["id_postulacion"] = $idPostulacion;
        $data["evento"] = "cambiocategoria";
        $data["fecha_hora"] = date("Y-m-d H:i");
        $data["descripcion"] = $descripcion;
        $historico->insert($data);
    }

    /*
     * Funcion que te marca como Leido o No Leido ya sea el caso.
     */

    public function marcarPostulacion($idPostulacion, $marca)
    {
        $where = $this->getAdapter()->quoteInto('id
        = ?', $idPostulacion);
        $arreglo["es_nuevo"] = $marca;
        $sql = $this->update(
            $arreglo, $where
        );
    }

    public function getBuscarPostulacion($idPostulante, $avisoId)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name)
            ->where('id_postulante
        = ?', $idPostulante)
            ->where('invitacion = 1')
            ->where('id_anuncio_web
        = ?', $avisoId);

        return $this->getAdapter()->fetchRow($sql);
    }
      public function getNivelesOrtrosEstudiosBuscadorEmpresa($idPostulante,$padre)
    {   
            $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ ;
          if($idPostulante==true){
          $sql = $this->getAdapter()->select()
                ->from(array('ne' => 'nivel_estudio'), array('id', 'nombre'))
                ->where("ne.padre != ''")
                ->order('nombre');
        if(!empty($padre))
            $sql->where("ne.padre LIKE ?", "%$padre%");
        $result = $this->getAdapter()->fetchAll($sql);
        }
         $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }
    /* Querys para el Buscador de empresa */

    public function getNivelesEstudiosBuscadorEmpresa($idAnuncioWeb = "")
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . "_" . $idAnuncioWeb;
        //para el buscador
        if ($idAnuncioWeb == "") {
            $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__} . "2";
            $cacheId = $this->_model . '_' . __FUNCTION__ . "2_" . $idAnuncioWeb;
        }

        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        if($idAnuncioWeb==true){
            
            
                     $sql = $this->getAdapter()->select()
                ->from(array('ne' => 'nivel_estudio'), array('id', 'nombre'))
                ->joinInner(array('ene' => 'empresa_nivel_estudio'), 'ne.id = ene.id_nivel_estudio', array())
                ->order('id');
        $result =$this->getAdapter()->fetchAll($sql);
        /*if ($this->_empresaId === TRUE) {
            return $result;
        }*/
        $sql->where('ene.id_empresa = ?', 1);
       // echo $sql->assemble();exit;
        $result = $this->getAdapter()->fetchAll($sql);
 
        
        return $result;
        }
        
        if ($idAnuncioWeb == "") {
            /* $sql = $this->getAdapter()->select()
              ->from(
              array("p"=>"postulante"),
              array(
              "nombre"=>"ne.nombre",
              "id" => "ne.id"
              )
              )
              ->joinInner(
              array("e"=>"estudio"),
              "e.id_postulante=p.id",
              array()
              )
              ->joinInner(
              array("ne"=>"nivel_estudio"),
              "e.id_nivel_estudio=ne.id",
              array()
              );

              $sql=$sql->group("ne.id")
              ->order("ne.id"); */

            $sql = $this->getAdapter()->select()
                ->from(
                array("ne" => "nivel_estudio"),
                array(
                "nombre" => "ne.nombre",
                "id" => "ne.id"
                )
            )->where("ne.padre=''") ->where("ne.id !=?",13)->order('ne.peso DESC')                  ;
        } else {
            $sql = $this->getAdapter()->select()
                ->from(
                    array("p" => $this->_name),
                    array(
                    "nombre" => "ne.nombre",
                    "id" => "ne.id"
                    )
                )
                ->joinInner(
                    array("pos" => "postulante"), "pos.id = p.id_postulante",
                    array()
                )
                ->joinInner(
                    array("e" => "estudio"), "e.id_postulante=p.id_postulante",
                    array()
                )
                ->joinInner(
                array("ne" => "nivel_estudio"), "e.id_nivel_estudio=ne.id",
                array()
            );

            $sql = $sql->where('p.id_anuncio_web
        = ?', $idAnuncioWeb);

            $sql = $sql->group("ne.id")
                ->order("ne.id");
        }
        $result = $this->getAdapter()->fetchAll($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getTipoCarreraBuscadorEmpresa($idAnuncioWeb = "",$addZero = false)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . "_" . $idAnuncioWeb;

        //para el buscador
        if ($idAnuncioWeb == "") {
            $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__} . "2";
            $cacheId = $this->_model . '_' . __FUNCTION__ . "2_" . $idAnuncioWeb;
        }

        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        if ($idAnuncioWeb == "") {
            /* $sql = $this->getAdapter()->select()
              ->from(
              array(
              "p"=>"postulante"
              ),
              array(
              "nombre"=>"tc.nombre",
              "id" => "tc.id"
              )
              )
              ->joinInner(
              array("e"=>"estudio"),
              "e.id_postulante=p.id",
              array()
              )
              ->joinInner(
              array("c"=>"carrera"),
              "e.id_carrera = c.id",
              array()
              )
              ->joinInner(
              array("tc"=>"tipo_carrera"),
              "c.id_tipo_carrera = tc.id",
              array()
              );
              $sql=$sql->group("tc.id")
              ->order("tc.id"); */
            $sql = $this->getAdapter()->select()
                ->from(
                array("tc" => "tipo_carrera"),
                array(
                "nombre" => "tc.nombre",
                "id" => "tc.id"
                )
            );
        } else {
            $sql = $this->getAdapter()->select()
                ->from(
                    array(
                    "p" => $this->_name
                    ),
                    array(
                    "nombre" => "tc.nombre",
                    "id" => "tc.id"
                    )
                )
                ->joinInner(
                    array("pos" => "postulante"), "pos.id = p.id_postulante",
                    array()
                )
                ->joinInner(
                    array("e" => "estudio"), "e.id_postulante=p.id_postulante",
                    array()
                )
                ->joinInner(
                    array("c" => "carrera"), "e.id_carrera = c.id", array()
                )
                ->joinInner(
                array("tc" => "tipo_carrera"), "c.id_tipo_carrera = tc.id",
                array()
            );

            $sql = $sql->where('p.id_anuncio_web
        = ?', $idAnuncioWeb);

            $sql = $sql->group("tc.id")
                ->order("tc.id");
        }
        $result = $this->getAdapter()->fetchAll($sql);
        if($addZero)
        {
            $resu = array();
            foreach($result as $rs)
            {
                $resuid = str_pad($rs['id'], 2, "0", STR_PAD_LEFT);
                $resu[$resuid] = array('nombre' => $rs['nombre'],'id'=>$resuid);
            }
            $result = $resu;
        }
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getAnosExperienciasBuscadorEmpresa()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }

        $config = Zend_Registry::get('config');
        $rango = $config->experiencia->tiempo->rango->toArray();
        $keys = array_keys($rango);
        $arreglo = "";
        $i = 0;
        for ($i = 0; $i < count($keys)-1; $i++) {
            if ($i == 0) {
                $arreglo[$i . "-" . $keys[$i]]["nombre"] = $i . " - " . $rango[$keys[$i]];
                $arreglo[$i . "-" . $keys[$i]]["id"] = $i . "-" . $keys[$i];
            } else {
                $arreglo[$keys[$i - 1]+1 . "-" . $keys[$i]]["nombre"] = $rango[$keys[$i - 1]] . " - " . $rango[$keys[$i]];
                $arreglo[$keys[$i - 1]+1 . "-" . $keys[$i]]["id"] = $keys[$i - 1]+1 . "-" . $keys[$i];
            }
        }
        $arreglo[$keys[$i - 1]+1 . "-" . $keys[$i]]["nombre"] = $rango[$keys[$i - 1]] . " - 5 Años";
        $arreglo[$keys[$i - 1]+1 . "-" . $keys[$i]]["id"] = $keys[$i - 1]+1 . "-" . $keys[$i];
        $arreglo[$keys[$i]+1 . "-600"]["nombre"] = $rango[$keys[$i]];
        $arreglo[$keys[$i]+1 . "-600"]["id"] = $keys[$i]+1 . "-600";

        $this->_cache->save($arreglo, $cacheId, array(), $cacheEt);
        return $arreglo;
    }

    public function getIdiomasBuscadorEmpresa($idAnuncioWeb = "")
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . "_" . $idAnuncioWeb;
        //para el buscador
        if ($idAnuncioWeb == "") {
            $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__} . "2";
            $cacheId = $this->_model . '_' . __FUNCTION__ . "2_" . $idAnuncioWeb;
        }
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $config = Zend_Registry::get('config');
        if ($idAnuncioWeb == "") {
            /* $sql = $this->getAdapter()->select()
              ->from(
              array(
              "p"=>"postulante"
              ),
              array(
              "nombre"=>"di.id_idioma",
              "id" => "di.id_idioma"
              )
              )
              ->joinInner(
              array("e"=>"estudio"),
              "e.id_postulante=p.id",
              array()
              )
              ->joinInner(
              array("di"=>"dominio_idioma"),
              "di.id_postulante = p.id",
              array()
              );
              $sql=$sql->group("di.id_idioma")
              ->order("di.id_idioma");
             */

            /* $sql = $this->getAdapter()->select()
              ->from(
              array("di"=>"dominio_idioma"),
              array(
              "nombre"=>"di.id_idioma",
              "id" => "di.id_idioma"
              )
              ); */
            $idiomas = $config->enumeraciones->lenguajes->toArray();
            $i = array();
            $x = 0;
            foreach ($idiomas as $index => $item) {
                $i[$index]["nombre"] = $item;
                $i[$index]["id"] = $index;
                $x++;
            }
//            echo "ok";
//            print_r($i);exit;
            return $i;
        } else {
            $sql = $this->getAdapter()->select()
                ->from(
                    array(
                    "p" => $this->_name
                    ),
                    array(
                    "nombre" => "di.id_idioma",
                    "id" => "di.id_idioma"
                    )
                )
                ->joinInner(
                    array("pos" => "postulante"), "pos.id = p.id_postulante",
                    array()
                )
//                ->joinInner(
//                    array("e" => "estudio"), "e.id_postulante=p.id_postulante",
//                    array()
//                )
                ->joinInner(
                array("di" => "dominio_idioma"), "di.id_postulante = pos.id",
                array()
            );
            $sql = $sql->where('p.id_anuncio_web
        = ?', $idAnuncioWeb);

            $sql = $sql->group("di.id_idioma")
                ->order("di.id_idioma");
        }
        $result = $this->getAdapter()->fetchAll($sql);
        
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getProgramasBuscadorEmpresa($idAnuncioWeb = "",$addZero = false, $idEmpresa = false)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . "_" . $idAnuncioWeb;

        //para el buscador
        if ($idAnuncioWeb == "") {
            $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__} . "2";
            $cacheId = $this->_model . '_' . __FUNCTION__ . "2_" . $idAnuncioWeb;
        }
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $config = Zend_Registry::get('config');
        if ($idAnuncioWeb == "") {            
            $sql = $this->getAdapter()
                    ->select()
                    ->from(array('ep' => 'empresa_programa_computo'),'')
                    ->joinLeft(array('prog'=>'programa_computo'),
                            'prog.id = ep.id_programa_computo',
                            array('nombre','id'))
                    ->where('ep.id_empresa = ?',$idEmpresa)
                    ->order('prog.nombre')
                    ;
            
            $resProg = $this->getAdapter()->fetchAll($sql);
            $i = array();
            foreach ($resProg as $index => $item) {                
                if($addZero) {
                    $index = str_pad($item['id'], 2, '0', STR_PAD_LEFT);
                }
                
                $i[$index]['id'] = $index;
                $i[$index]['nombre'] = $item['nombre'];
                
            }           
            return $i;
        } else {
            $sql = $this->getAdapter()->select()
                ->from(
                    array(
                    "p" => $this->_name
                    ),
                    array(
                    "nombre" => "prog.nombre",
                    "id" => "dpc.id_programa_computo"
                    )
                )
                ->joinInner(
                    array("pos" => "postulante"), "p.id_postulante = pos.id",
                    array()
                )
                ->joinInner(
                array("dpc" => "dominio_programa_computo"),
                "dpc.id_postulante = pos.id", array()
                )
                ->joinInner(
                array("prog" => "programa_computo"),
                "prog.id = dpc.id_programa_computo", array()
                );
            $sql = $sql->where('p.id_anuncio_web
        = ?', $idAnuncioWeb);
            $sql = $sql->group("dpc.id_programa_computo")
                ->order("dpc.id_programa_computo");
        }

        $result = $this->getAdapter()->fetchAll($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);

        return $result;
    }

    public function getEdadesBuscadorEmpresa($idAnuncioWeb = "")
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . "_" . $idAnuncioWeb;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $config = Zend_Registry::get('config');
        $rango = $config->edades->rango->toArray();
        $keys = array_keys($rango);

        $arreglo = "";
        $i = 0;
        for ($i = 0; $i < count($keys) - 1; $i++) {
            if($i==0)
            {
                $arreglo[$keys[$i] . "-" . $keys[$i + 1]]["nombre"] = $rango[$keys[$i]] . " - " . $rango[$keys[$i + 1]];
                $arreglo[$keys[$i] . "-" . $keys[$i + 1]]["id"] = $keys[$i] . "-" . $keys[$i + 1];
            }
            else
            {
                $arreglo[$keys[$i]+1 . "-" . $keys[$i + 1]]["nombre"] = $rango[$keys[$i]] . " - " . $rango[$keys[$i + 1]];
                $arreglo[$keys[$i]+1 . "-" . $keys[$i + 1]]["id"] = $keys[$i]+1 . "-" . $keys[$i + 1];                
            }
        }
        $arreglo[$keys[$i]+1 . "-mas"]["nombre"] = $rango[$keys[$i]] . " - Más";
        $arreglo[$keys[$i]+1 . "-mas"]["id"] = $keys[$i]+1 . "-mas";
        $this->_cache->save($arreglo, $cacheId, array(), $cacheEt);
        return $arreglo;
    }

    public function getSexoBuscadorEmpresa()
    {
        $arreglo = array(
            "M" => array(
                "nombre" => "Masculino",
                "id" => "M"
            ),
            "F" => array(
                "nombre" => "Femenino",
                "id" => "F"
            )
        );
        return $arreglo;
    }

    public function getUbicacionBuscadorEmpresa($idAnuncioWeb = "")
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . "_" . $idAnuncioWeb;

        //para el buscador
        if ($idAnuncioWeb == "") {
            $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__} . "2";
            $cacheId = $this->_model . '_' . __FUNCTION__ . "2_" . $idAnuncioWeb;
        }

        /* if ($this->_cache->test($cacheId)) {
          return $this->_cache->load($cacheId);
          } */
        $config = Zend_Registry::get('config');
        if ($idAnuncioWeb == "") {
            /* $sql = $this->getAdapter()->select()
              ->from(
              array(
              "p"=>$this->_name
              ),
              array(
              "id"=>"u.id",
              "nombre"=>"u.nombre"
              )
              )
              ->joinInner(
              array("pos"=>"postulante"),
              "p.id_postulante = pos.id",
              array()
              )
              ->joinInner(
              array("u"=>"ubigeo"),
              "pos.id_ubigeo = u.id",
              array()
              ); */
            $sql = $this->getAdapter()->select()
                ->from(
                    array("u" => "ubigeo"),
                    array(
                    "id" => "u.id",
                    "nombre" => "u.nombre"
                    )
                )
                ->where("u.padre=3927 OR padre=3926 OR u.padre=2533")
                ->where("u.id<>3926")
                ->order("u.padre DESC");
        } else {
            $sql = $this->getAdapter()->select()
                ->from(
                    array(
                    "p" => $this->_name
                    ),
                    array(
                    "id" => "u.id",
                    "nombre" => "u.nombre"
                    )
                )
                ->joinInner(
                    array("pos" => "postulante"), "p.id_postulante = pos.id",
                    array()
                )
                ->joinInner(
                array("u" => "ubigeo"), "pos.id_ubigeo = u.id", array()
            );
            $sql = $sql->where('p.id_anuncio_web
        = ?', $idAnuncioWeb);

            $sql = $sql->group("u.id")->order("u.id");
        }

        $result = $this->getAdapter()->fetchAssoc($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function obtenerPorEmpresaYPostulante($empresa_id, $postulante_id,
        $columnas = array())
    {
        $columnas = $this->setCols($columnas);

        return $this->getAdapter()->fetchAll($this->getAdapter()->select()
                    ->from(array('p' => $this->_name), $columnas)
                    ->joinInner(array('aw' => 'anuncio_web'),
                        'aw.id = p.id_anuncio_web', array())
                    ->where('p.id_postulante=?', $postulante_id)
                    ->where('aw.id_empresa=?', $empresa_id));
    }

    public function bloquear($postulacion_id)
    {
        $this->update(array('activo' => self::POSTULACION_BLOQUEADA),
            'id = ' .
            $postulacion_id);
    }

    public function desbloquear($postulacion_id)
    {
        $this->update(array('activo' => self::POSTULACION_ACTIVA),
            'id = ' .
            $postulacion_id);
    }

    public function registrar($registro)
    {
        $this->insert($registro);
    }

    public function obtenerPorAnuncioYPostulante(
    $anuncioId, $postulanteId, $columnas = array())
    {
        $columnas = $this->setCols($columnas);

        return $this->fetchRow($this->select()
                    ->from($this->_name, $columnas)
                    ->where('id_postulante =?', $postulanteId)
                    ->where('id_anuncio_web =?', $anuncioId));
    }

    public function postulacionActual($idAviso, $idPostulante)
    {
        $columnas['id'] = 'id';
        $columnas['referenciado'] = 'referenciado';
        $columnas['invitacion'] = 'invitacion';
        $columnas['activo'] = 'activo';

        return $this->fetchRow($this->select()
                    ->from($this->_name, $columnas)
                    ->where('id_postulante =?', $idPostulante)
                    ->where('id_anuncio_web =?', $idAviso));
    }

    public function updatePostulacion($idPostulcion, $data)
    {
        //return $this->update($data, 'id = ' . (int)$idPostulcion);
        $where = $this->_db->quoteInto('id = ?', $idPostulcion);
        return $this->update($data, $where);
    }

    public function obtenerRefenreciada(
    $anuncioId, $postulanteId, $columnas = array())
    {
        $columnas = $this->setCols($columnas);

        return $this->fetchRow($this->select()
                    ->from($this->_name, $columnas)
                    ->where('id_postulante =?', $postulanteId)
                    ->where('id_anuncio_web =?', $anuncioId)
                    ->where('referenciado =?', self::ES_REFERENCIADO));
    }

    public function actualizarPorPostulanteYAnuncio(
    $postulanteId, $anuncioId, $datos)
    {
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('id_postulante = ?',
            $postulanteId);
        $where[] = $this->getAdapter()->quoteInto('id_anuncio_web = ?',
            $anuncioId);

        return $this->update($datos, $where);
    }

    public function obtenerInvitado(
    $anuncioId, $postulanteId, $columnas = array())
    {
        $columnas = $this->setCols($columnas);

        return $this->fetchRow($this->select()
                    ->from($this->_name, $columnas)
                    ->where('id_postulante =?', $postulanteId)
                    ->where('id_anuncio_web =?', $anuncioId)
                    ->where('invitacion =?', self::INVITADO));
    }

    public function activar($id)
    {
        $datos = array();
        $datos['activo'] = self::POSTULACION_ACTIVA;

        $where = $this->getAdapter()->quoteInto('id = ?', $id);
        $this->update($datos, $where);
    }
    
    public function obtenerDetalle($id, $empresaId = null)
    {
        $select = $this->getAdapter()->select()
            ->from(array('ps' => $this->_name), array('ps.id'))
            ->joinInner(
                    array('aw' => 'anuncio_web'), 'aw.id = ps.id_anuncio_web',
                    array('anuncio_id' => 'aw.id', 'aw.puesto',
                          'anuncio_cerrado' => 'aw.cerrado'))
            ->joinInner(array('p' =>'postulante'), 'ps.id_postulante = p.id',
                        array('nombres', 'apellidos', 'foto_postulante' => 'path_foto'))
            ->where('ps.id =?', (int)$id);
            
        if (!is_null($empresaId))
            $select->where('aw.id_empresa =?', (int)$empresaId);
           
        return $this->getAdapter()->fetchRow($select);
    }

    public function getOtrosEstudiosBuscadorEmpresa()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__} . '2';
        $cacheId = $this->_model . '_' . __FUNCTION__ . '2_';        
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->getAdapter()->select()
            ->from(
            array("ne" => "nivel_estudio"),
            array(
            "id" => "ne.id",
            "nombre" => "ne.nombre"
            )
        )->where("ne.padre LIKE ?", "%9%");
        $result = $this->getAdapter()->fetchAssoc($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }
    public function getNivelesEstudiosBuscadorEmpresaNuevo($addZero = false)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__} . '2';
        $cacheId = $this->_model . '_' . __FUNCTION__ . '2_';        
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $nivel = new Application_Model_NivelEstudio;
        $listaNiveles = $nivel->getNivelesAptitus();
        $result = array();
        foreach($listaNiveles as $k=>$v)
        {
            $ke = $k;
            $ze = '0';
            if($addZero)
            {
                $ke = str_pad($k, 2, "0", STR_PAD_LEFT);
                $ze = '00';
            }
            $result["$ke-$ze"] = array("nombre"=>"$v","id"=>"$ke-$ze");
            $listaSubNiveles = $nivel->getSubNiveles($k);
            foreach($listaSubNiveles as $a=>$b)
            {
                $ae = $a;
                if($addZero)
                    $ae = str_pad($a, 2, "0", STR_PAD_LEFT);
                $result["$ke-$ae"] = array("nombre"=>"$b","id"=>"$ke-$ae");
            }
        }
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }
    /**
     * Retorna la cantidad de postulantes por dia y por anuncio web
     * 
     * @param int $id
     */
    public function getPostulantesByDia($id)
    {
        $desde= date('Y-m-d', strtotime('-30 day')) ;
        $sql = $this->getAdapter()->select()
            ->from(
                array('p' => 'postulacion'), array('fecha'=> new Zend_Db_Expr('DATE(fh)'),'total' =>new Zend_Db_Expr( 'count(id)'))
            )
            ->where('p.id_anuncio_web = ?', $id)
            ->where('p.activo = ?', 1)
            ->where('p.invitacion = ?', 0)
            ->where('p.fh >= ?', $desde)
            ->group('DATE(p.fh)');
         $result = $this->getAdapter()->fetchAssoc($sql);
      
        return $result;
    }
    /**
     * Retorna los postulantes por anuncio web
     * 
     * @param int $id
     */
    public function getDataPostulantesByAviso($id)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('p' => 'postulacion'), 
                    array('p.id','po.sexo',
                        'edad'=>new Zend_Db_Expr('FLOOR(((TO_DAYS(CURDATE()) - TO_DAYS(po.fecha_nac)) / 365))'),
  'estudios'=>new Zend_Db_Expr("(SELECT
     (SELECT
        GROUP_CONCAT(IF(ISNULL(`niv`.`nombre`),'Sin estudios',`niv`.`nombre`) SEPARATOR '/')
      FROM `nivel_estudio` `niv`
      WHERE ((`niv`.`id` IN(`e`.`id_nivel_estudio`,`e`.`id_nivel_estudio_tipo`))
             AND (`e`.`id_nivel_estudio` <> 9))) AS `niveles`
   FROM (`estudio` `e`
      JOIN `nivel_estudio` `ne`
        ON ((`ne`.`id` = `e`.`id_nivel_estudio`)))
   WHERE (`e`.`id_postulante` = `po`.`id`)
   ORDER BY (((SELECT
                 `nivel_estudio`.`peso`
               FROM `nivel_estudio`
               WHERE (`nivel_estudio`.`id` = `ne`.`id`)) * 100) + (SELECT `nivel_estudio`.`peso`
                                                                                   FROM `nivel_estudio`
                                                                                   WHERE (`nivel_estudio`.`id` = IF((`e`.`id_nivel_estudio_tipo` = 0),1,`e`.`id_nivel_estudio_tipo`))))DESC
   LIMIT 1)"    ),
                'categoria'=>new Zend_Db_Expr("(SELECT IF(ISNULL(cp.nombre),'',CONCAT(cp.nombre,'|',cp.id)) FROM categoria_postulacion cp WHERE cp.id=p.id_categoria_postulacion)")
                        
                        ))
            ->joinInner(array('po'=>'postulante'),'po.id = p.id_postulante',array())
            ->joinLeft(array('e'=>'estudio'),'po.id = e.id_postulante',array())
            ->where('p.id_anuncio_web = ?', $id)
            ->where('p.activo = ?', 1)
            ->where('p.invitacion = ?', 0)
            ->group('p.id');
        $result = $this->getAdapter()->fetchAssoc($sql);
        //echo $sql;
        return $result;
    }
    
    //Obtiene todos los postulantes que han postulado al aviso
    public function obtenerPostulantesxAviso($idAviso) {
        
        $sql = $this->getAdapter()->select()->from($this->_name, array('id_postulante'))
                ->where('id_anuncio_web = ?', $idAviso);
        
        return $this->getAdapter()->fetchAll($sql);
        
    }
    
    //Obtiene todas las postulaciones por aviso
    public function getPostulacionesAviso($idPostulante, $idAviso)
    {
        
        $sql = $this->_db->select()
            ->from(
                array('p' => $this->_name),
                array('fecha' => 'p.fh',
                'idpostulacion' => 'p.id',
                'puesto' => 'aw.puesto',
                'empresa' => 'aw.empresa_rs',
                'leidos' => 'p.msg_leidos',
                'noleidos' => 'p.msg_no_leidos',
                'norespondidos' => 'p.msg_por_responder',
                'p.id_anuncio_web' => 'aw.id',
                'slugaviso' => 'aw.slug',
                'es_nuevo' => 'p.es_nuevo',
                'urlaviso' => 'aw.url_id'
                )
            )
            ->joinInner(
                array('aw' => 'anuncio_web'), 'p.id_anuncio_web = aw.id'
            )
            ->where('p.id_postulante = ?', $idPostulante)
            ->where('p.id_anuncio_web = ?', $idAviso);

        return $sql;
    }
    
    //Actualizar Match a postulantes de un aviso
    public function actualizarMatchPostulantes($idAnuncio)
    {
        //Obtener postulantes par actualizar Match cuando se extiende el aviso
        $postulantes = $this->obtenerPostulantesxAviso($idAnuncio);
        if (!is_null($postulantes)) {
            $registroExtra = new App_Controller_Action_Helper_RegistrosExtra;
            foreach ($postulantes as $value) {//Cálcula match
                $registroExtra->ActualizarPostulacionAvisoAmpliado($value['id_postulante'],$idAnuncio);
            }
        }
    }
    /**
     * 
     * @param type $IdPostulante
     * @return type
     */

    public function getIdAvisosPostulaciones( $IdPostulante )
    {
      $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
      $cacheId = $this->_model . '_' . __FUNCTION__ .'_'.$IdPostulante;  
      if ($this->_cache->test($cacheId)) {
         return $this->_cache->load($cacheId);
      }      
      $sql = $this->_db->select()
          ->from(
              array('p' => $this->_name),
              array('id_anuncio_web'=>'id')
          )
          ->joinInner(
              array('aw' => 'anuncio_web'), 'p.id_anuncio_web = aw.id',array()
          )
          ->where('p.id_postulante = ?', $IdPostulante);
      $result= $this->_db->fetchAll($sql); 
      $this->_cache->save($result, $cacheId, array(), $cacheEt);       
      return $result;      
    }
     public function getPostulacionDiscapacidad($paginator)
    {
      //  $valorOrigenPostulacion = array();
        $valorOrigenPostulacion[] =  
                  array(
                     'id'=>'conadis',
                    'nombre'=>'Con discapacidad / Conadis'
                    )  ;
        return $valorOrigenPostulacion;
    }
        
    /**
     * Todas las postulaciones de los avisos activos de la empresa se llevan al
     * nivel inicial.
     * 
     * @param type int Id de empresa
     */
    public function reiniciarNivelPostulacion($idEmpresa) {
        $aw = new Application_Model_AnuncioWeb();
        $anuncios = $aw->obtenerAvisosActivosEmpresaMembresia($idEmpresa);

        foreach ($anuncios as $item) {
            $where = $this->getAdapter()->quoteInto('id_anuncio_web = ?', $item['id']);
            $where .= " AND id_categoria_postulacion IS NOT NULL ";
            $this->update(array('id_categoria_postulacion' => NULL), $where);
        }
    }
}


