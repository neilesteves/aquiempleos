<?php

class Application_Model_AnuncioPostulanteMatch extends App_Db_Table_Abstract
{
    protected $_name = 'anuncio_postulante_match';
    
    const ESTADO_POSTULA = 2; //Candidatos que se les ha enviado la inv o ya han postulado
    const ESTADO_LISTAR = 1; //Mostrar en lista los candidatos sugeridos
    const ESTADO_ELIMINADO = 0; //Quitados de la lista de cand. sugeridos

    public function getPostulantesSugeridos($empresaId)
    {

        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('apm' => $this->_name), array(
                'id_anuncio_web' => new Zend_Db_Expr('DISTINCT(apm.id_anuncio_web)'),
                'aw.puesto',
                'postulantes' => new Zend_Db_Expr('COUNT(1)')
                )
            )->join(array('aw' => 'anuncio_web'), 'aw.id = apm.id_anuncio_web')
            ->where("apm.id_empresa = ?", $empresaId)
            // DESCOMENTAR ESTO CUANDO ESTE OPERATIVO
            ->where("aw.online = ?", 1)
            ->where("apm.estado = ?", 1)
            ->group("apm.id_anuncio_web")
            ->order('aw.fh_pub desc');
        return $db->fetchAll($sql);
    }

    public function getPostulantesAnuncio($anuncioId)
    {
        $db = $this->getAdapter();

        $sql = $db->select()
            ->from(array('po' => 'postulante'))
            ->joinLeft(array('es' => 'estudio'), ' es.id_postulante = po.id ')
            ->joinLeft(array('ne' => 'nivel_estudio'), ' ne.id = es.id_nivel_estudio ')
            ->joinLeft(array('car' => 'carrera'), ' car.id = es.id_carrera ', array('nombreCarrera' => 'nombre'))
            ->join(array('apm' => 'anuncio_postulante_match'), ' apm.id_postulante = po.id ')
            ->where(" apm.id_anuncio_web = ?", $anuncioId)
            ->group('po.id');

        $result = $db->fetchAll($sql);

        return $result;
    }

    public function getPaginadorBusquedaPersonalizada($empresaId, $col, $ord)
    {
        $paginadoBusqueda = $this->_config->administrador->gestion->paginadoBusqueda;
        $p = Zend_Paginator::factory($this->getBusquedaPersonalizada($empresaId, $col, $ord));
        return $p->setItemCountPerPage($paginadoBusqueda);
    }

    public function getBusquedaPersonalizada($empresaId, $col, $ord)
    {

        $col = $col == '' ? 'fh_pub' : $col;
        $ord = $ord == '' ? 'desc' : $ord;

        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('apm' => $this->_name), array(
                'id_anuncio_web' => new Zend_Db_Expr('DISTINCT(apm.id_anuncio_web)'),
                'postulantes' => new Zend_Db_Expr('COUNT(*)')
                )
            )
            ->join(
                array('aw' => 'anuncio_web'), 'aw.id = apm.id_anuncio_web', array(
                'tipo_Aviso' => 'aw.tipo', 'id_anuncio_impreso_Aviso' => 'aw.id_anuncio_impreso',
                'slug_Aviso' => 'aw.slug', 'url_id_Aviso' => 'aw.url_id', 'fh_creacion' => 'aw.fh_creacion',
                'fh_vencimiento' => 'aw.fh_vencimiento', 'fh_vencimiento_proceso' => 'aw.fh_vencimiento_proceso', 
                'puesto_Aviso' => 'aw.puesto'
                )
            )
            ->join(array('ub' => 'ubigeo'), 'aw.id_ubigeo = ub.id', array('nombreLocacion' => 'ub.nombre'))
            ->where("apm.id_empresa = ?", $empresaId)
            ->where("aw.online = ?", 1)
            ->where("apm.estado = ?", 1)
            ->where("apm.match >= ?", $this->_config->profileMatch->match->porcentajeMinimo)
            ->group("apm.id_anuncio_web");

        $sql = $sql->order(sprintf('%s %s', $col, $ord));
        //echo $sql;exit;
        return $sql;
    }

    public function getPaginadorBusquedaAnuncioPostulantes($anuncioId, $col, $ord, $tipoLeido = null)
    {
        $paginadoBusqueda = $this->_config->administrador->gestion->paginadoBusqueda;
        $p = Zend_Paginator::factory($this->getBusquedaAnuncioPostulantesSugeridos($anuncioId, $col, $ord, $tipoLeido));
        return $p->setItemCountPerPage($paginadoBusqueda);
    }

    public function getBusquedaAnuncioPostulantesSugeridos($anuncioId, $col, $ord, $tipoLeido)
    {
        $col = $col == '' ? 'apm.match' : $col;
        $ord = $ord == '' ? 'ASC' : $ord;
     
        $db = $this->getAdapter();
        
        $sql = $db->select()
            ->from(array('po' => 'postulante'),
                   array('po.path_foto', 'po.nombres', 'po.apellidos',
                         'po.celular', 'po.id', 'po.notif_no_leidas',
                         'po.sexo', 'po.fecha_nac'))
            ->joinLeft(array('es' => 'estudio'), ' es.id_postulante = po.id ')
            ->joinLeft(array('ne' => 'nivel_estudio'), ' ne.id = es.id_nivel_estudio ',
                       array('ne.nombre'))
            ->joinLeft(array('car' => 'carrera'), ' car.id = es.id_carrera ',
                       array('nombreCarrera' => 'nombre'))
            ->joinLeft(array('ubi' => 'ubigeo'),
                       'ubi.id = po.id_ubigeo', array('ubi.display_name'))
            ->join(array('apm' => 'anuncio_postulante_match'),
                   'apm.id_postulante = po.id',
                   array('apm.id_postulante', 'apm.match', 'apm.leido',
                         'apm.id_anuncio_web'))
            ->where("apm.id_anuncio_web = ?", $anuncioId)
            ->where("estado = ?", self::ESTADO_LISTAR)
            ->group('po.id');
        
        if (($ord == '') && ($col == '')) {
            $sql = $sql->order(' apm.match ASC ');
        }

        if ($tipoLeido != null && $tipoLeido != '-1') {
            $sql = $sql->where('apm.leido = ?', $tipoLeido);
        }
        
        $sql = $sql->order(sprintf('%s %s', $col, $ord));
        return $sql;
    }

    public function quitarPostulantesSugeridos($idAw, $idPos)
    {
        $result = false;
        $where = $this->getAdapter()->quoteInto("id_anuncio_web = $idAw and id_postulante = $idPos", null);
        $sql = $this->update(
            array('estado' => self::ESTADO_ELIMINADO, 'fh_eliminacion' => date('Y-m-d H:i:s')), $where
        );
        if ($sql) {
            $result = true;
        }
        return $result;
    }

    public function getAnunciosSugeridos($postulanteId, $nroAvisos = null)
    {
        $result = array();
        if ($postulanteId) {
            $db = $this->getAdapter();
            $sql = $db->select()
                ->from(
                    array('apm' => $this->_name), array(
                    'match' => 'apm.match'
                    )
                )->join(
                    array('aw' => 'anuncio_web'), 'aw.id = apm.id_anuncio_web', array(
                    'id_anuncio_web' => 'aw.id',
                    'aw.empresa_rs',
                    'aw.puesto',
                    'aw.slug',
                    'aw.url_id',
                    'aw.tipo',
                    'logoanuncio' => 'aw.logo',
                    'aw.funciones',
                    'aw.responsabilidades',
                    new Zend_Db_Expr('DATEDIFF(CURDATE(),aw.fh_pub) AS dias_fp')                
                    )
                )->join(
                    array('u' => 'ubigeo'), 'u.id = aw.id_ubigeo', array('u.display_name')
                )
                ->where("apm.id_postulante = ?", $postulanteId)
                ->where("aw.online = ?", 1)
                ->where("apm.estado = ?", 1);

            $sql->order(array('apm.match desc','aw.fh_pub desc'));

            if ($nroAvisos != null) {
                $sql = $sql->limit($nroAvisos);
            } else {
                $sql = $sql->limit($this->_config->profileMatch->postulante->nroanunciosmax);
            }

            $result = $db->fetchAll($sql);
        }
        
        return $result;
        
    }

    public function getAnuncioPosByIdPosIdAviso($postulanteId, $avisoId)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name, 'id')
            ->where('id_postulante = ?', $postulanteId)
            ->where('id_anuncio_web = ?', $avisoId);

        return $this->getAdapter()->fetchRow($sql);
    }
    
    public static function getQueryAnunciosForMatch($tipoIn = array('preferencial', 'clasificado', 'soloweb'),
                                                    $diasPorVencer = 2, $prefix = 'aw')
    {
        $obj = new App_Db_Table_Abstract();
        $db = $obj->getAdapter();


        $where = $db->quoteInto("online = ?", 1);
        $tipoWhere = "";
        $diasWhere = $db->quoteInto("$prefix.fh_vencimiento > DATE_ADD(NOW(), INTERVAL ? DAY)", $diasPorVencer);

        $where = $where . " AND " . $diasWhere;

        if (is_array($tipoIn) || is_string($tipoIn)) {
            $tipoWhere = $db->quoteInto("tipo IN (?)", $tipoIn);
        }

        if (!empty($tipoWhere)) {
            $where = $where . " AND " . $tipoWhere;
        }
        
        $db->query(
            "UPDATE anuncio_web as aw ".
            "SET aw.match_calculado = 1, aw.fh_proceso_match = NOW() ".
            "WHERE ".$where
        );
        
        return $where;
    }

    public static function getAnunciosPrint($where)
    {
        $obj = new Application_Model_AnuncioWeb();
        $db = $obj->getAdapter();
        $sql = $db->select()->union(
            array(
                //INFORMACION DE IDIOMA
                    $db->select()
                    ->from(array('aw' => 'anuncio_web'), array())
                    ->join(
                        array('ai' => 'anuncio_idioma'), 'aw.id = ai.id_anuncio_web', array(
                        'ai.id_anuncio_web',
                        'item' => new Zend_Db_Expr(1),
                        'print' => new Zend_Db_Expr(
                            "(CASE ai.id_idioma " .
                            "WHEN 'de' THEN 10000 " .
                            "WHEN 'zh' THEN 20000 " .
                            "WHEN 'es' THEN 30000 " .
                            "WHEN 'fr' THEN 40000 " .
                            "WHEN 'en' THEN 50000 " .
                            "WHEN 'it' THEN 60000 " .
                            "WHEN 'jp' THEN 70000 " .
                            "WHEN 'pt' THEN 80000 " .
                            "WHEN 'qu' THEN 90000 " .
                            "END)" .
                            "+ " .
                            "(CASE ai.nivel " .
                            "WHEN 'basico' THEN 1000 " .
                            "WHEN 'intermedio' THEN 2000 " .
                            "WHEN 'avanzado' THEN 3000 " .
                            "END)"
                        ),
                        'bottom' => new Zend_Db_Expr(
                            "(CASE ai.id_idioma " .
                            "WHEN 'de' THEN 10000 " .
                            "WHEN 'zh' THEN 20000 " .
                            "WHEN 'es' THEN 30000 " .
                            "WHEN 'fr' THEN 40000 " .
                            "WHEN 'en' THEN 50000 " .
                            "WHEN 'it' THEN 60000 " .
                            "WHEN 'jp' THEN 70000 " .
                            "WHEN 'pt' THEN 80000 " .
                            "WHEN 'qu' THEN 90000 " .
                            "END)"
                        ),
                        'top' => new Zend_Db_Expr(
                            "(CASE ai.id_idioma " .
                            "WHEN 'de' THEN 10000 " .
                            "WHEN 'zh' THEN 20000 " .
                            "WHEN 'es' THEN 30000 " .
                            "WHEN 'fr' THEN 40000 " .
                            "WHEN 'en' THEN 50000 " .
                            "WHEN 'it' THEN 60000 " .
                            "WHEN 'jp' THEN 70000 " .
                            "WHEN 'pt' THEN 80000 " .
                            "WHEN 'qu' THEN 90000 " .
                            "END)" .
                            "+ " .
                            "(CASE ai.nivel " .
                            "WHEN 'basico' THEN 1000 " .
                            "WHEN 'intermedio' THEN 2000 " .
                            "WHEN 'avanzado' THEN 3000 " .
                            "END)"
                        )
                        )
                    )
                    ->where('ai.id_idioma != ?', -1)
                    ->where($where)
                    ->group(new Zend_Db_Expr("CONCAT(ai.id_anuncio_web, '-', ai.id_idioma)")),
                //INFORMACION DE PROGRAMAS DE COMPUTO
                    $db->select()
                    ->from(array('aw' => 'anuncio_web'), array())
                    ->join(
                        array('apc' => 'anuncio_programa_computo'), 'aw.id = apc.id_anuncio_web', array(
                        'apc.id_anuncio_web',
                        'item' => new Zend_Db_Expr(2),
                        'print' => new Zend_Db_Expr(
                            "(apc.id_programa_computo * 10000) " .
                            "+ " .
                            "(CASE apc.nivel " .
                            "WHEN 'basico' THEN 1000 " .
                            "WHEN 'intermedio' THEN 2000 " .
                            "WHEN 'avanzado' THEN 3000 " .
                            "END)"
                        ),
                        'bottom' => new Zend_Db_Expr("(apc.id_programa_computo * 10000)"),
                        'top' => new Zend_Db_Expr(
                            "(apc.id_programa_computo * 10000) " .
                            "+ " .
                            "(CASE apc.nivel " .
                            "WHEN 'basico' THEN 1000 " .
                            "WHEN 'intermedio' THEN 2000 " .
                            "WHEN 'avanzado' THEN 3000 " .
                            "END)"
                        )
                        )
                    )
                    ->where('apc.id_programa_computo > 0')
                    ->where($where)
                    ->group(new Zend_Db_Expr("CONCAT(apc.id_anuncio_web, '-', apc.id_programa_computo)")),
                //INFORMACION DE ESTUDIOS
                    $db->select()
                    ->from(array('aw' => 'anuncio_web'), array())
                    ->join(array('ae' => 'anuncio_estudio'), 'ae.id_anuncio_web = aw.id', array())
                    ->join(
                        array('ne' => 'nivel_estudio'), 'ae.id_nivel_estudio = ne.id', array(
                        'ae.id_anuncio_web',
                        'item' => new Zend_Db_Expr(3),
                        'print' => new Zend_Db_Expr("((IFNULL(ae.`id_carrera`,0) * 10000) + (ne.peso * 1000))"),
                        'bottom' => new Zend_Db_Expr("(IFNULL(ae.`id_carrera`,0) * 10000)"),
                        'top' => new Zend_Db_Expr("(IFNULL(ae.`id_carrera`,0) * 10000  + 10000)")
                        )
                    )
                    ->where($where)
                    ->group(
                        new Zend_Db_Expr("CONCAT(ae.id_anuncio_web, '-', ae.id_nivel_estudio, '-', ae.id_carrera)")
                    ),
                //INFORMACION EXPERIENCIA
                    $db->select()
                    ->from(array('aw' => 'anuncio_web'), array())
                    ->join(array('ae' => 'anuncio_experiencia'), 'aw.id = ae.id_anuncio_web', array())
                    ->join(array('a' => 'area'), 'a.id = ae.id_area', array())
                    ->join(
                        array('np' => 'nivel_puesto'), 'ae.id_nivel_puesto = np.id', array(
                        'ae.id_anuncio_web',
                        'item' => new Zend_Db_Expr(4),
                        'print' => new Zend_Db_Expr(
                            "((IFNULL(ae.`id_area`,0) * 10000) + 
                            ((IFNULL(ae.`id_nivel_puesto`,0) * 1000)) + SUM(ae.experiencia))"
                        ),
                        'bottom' => new Zend_Db_Expr(
                            "(IFNULL(ae.`id_area`,0) * 10000) + 
                            ((IFNULL(ae.`id_nivel_puesto`,0) * 1000))"
                        ),
                        'top' => new Zend_Db_Expr(
                            "(IFNULL(ae.`id_area`,0) * 10000) + 
                            ((IFNULL(ae.`id_nivel_puesto`,0) * 1000)) + 1000"
                        )
                        )
                    )
                    ->where($where)
                    ->group(new Zend_Db_Expr("CONCAT(ae.id_anuncio_web, '-', ae.id_area, '-', ae.id_nivel_puesto)"))
            )
        );
        //echo $sql->assemble();
        return $sql;
    }
    
    public static function getAnuncioNewTable($where, $pEst = 5, $pEx = 7, $pId = 3, $pCo = 2)
    {
        $obj = new Application_Model_AnuncioWeb();
        $db = $obj->getAdapter();
        
        $sqlInicial = $db->select()
            ->from(
                array('aw' => 'anuncio_web'), 
                array(
                    'id_empresa', 
                    'id_anuncio_web' => 'id',
                    'total_estudio' => new Zend_Db_Expr(
                        '(SELECT COUNT(*) FROM anuncio_estudio AS ae WHERE ae.id_anuncio_web = aw.id)'
                    ),
                    'total_experiencia' => new Zend_Db_Expr(
                        '(SELECT COUNT(*) FROM anuncio_experiencia AS aex WHERE aex.id_anuncio_web = aw.id)'
                    ),
                    'total_idioma' => new Zend_Db_Expr(
                        '(SELECT COUNT(*) FROM anuncio_idioma AS ai WHERE ai.id_anuncio_web = aw.id)'
                    ),
                    'total_computo' => new Zend_Db_Expr(
                        '(SELECT COUNT(*) FROM anuncio_programa_computo AS apc WHERE apc.id_anuncio_web = aw.id)'
                    )
                )
            )
            ->where($where);
        
        $sql = $db->select()
            ->from(
                array('a' => new Zend_Db_Expr("($sqlInicial)")),
                array (
                    "id_empresa", 
                    "id_anuncio_web",
                    "total_estudio",
                    "total_experiencia",
                    "total_idioma",
                    "total_computo",
                    "peso_estudio" => new Zend_Db_Expr("IF(total_estudio = 0,0,{$pEst}/total_estudio)"),
                    "peso_experiencia" => new Zend_Db_Expr("IF(total_experiencia = 0,0,{$pEx}/total_experiencia)"),
                    "peso_idioma" => new Zend_Db_Expr("IF(total_idioma = 0,0,{$pId}/total_idioma)"),
                    "peso_computo" => new Zend_Db_Expr("IF(total_computo = 0,0,{$pCo}/total_computo)"),
                    "total_peso" => new Zend_Db_Expr(
                        "IF(total_estudio = 0,0,{$pEst}) + " .
                        "IF(total_experiencia = 0,0,{$pEx}) + " .
                        "IF(total_idioma = 0,0,{$pId}) + " .
                        "IF(total_computo = 0,0,{$pCo}) "
                    )
                )
            );
        
        return $sql;
    }

    public static function getPostulantesPrint($where)
    {
        $obj = new Application_Model_Postulante();
        $db = $obj->getAdapter();

        $sql = $db->select()->union(
            array(
                //INFORMACION DE IDIOMA
                    $db->select()
                    ->from(array('p' => 'postulante'), array())
//                ->join(array('u' => 'usuario'), 'p.id_usuario = u.id', array())
                    ->join(
                        array('di' => 'dominio_idioma'), 'di.id_postulante = p.id', array(
                        'di.id_postulante',
                        'item' => new Zend_Db_Expr(1),
                        'print' => new Zend_Db_Expr(
                            "(CASE di.id_idioma " .
                            "WHEN 'de' THEN 10000 " .
                            "WHEN 'zh' THEN 20000 " .
                            "WHEN 'es' THEN 30000 " .
                            "WHEN 'fr' THEN 40000 " .
                            "WHEN 'en' THEN 50000 " .
                            "WHEN 'it' THEN 60000 " .
                            "WHEN 'jp' THEN 70000 " .
                            "WHEN 'pt' THEN 80000 " .
                            "WHEN 'qu' THEN 90000 " .
                            "END)" .
                            "+ " .
                            "(CASE di.nivel_hablar " .
                            "WHEN 'basico' THEN 1000 " .
                            "WHEN 'intermedio' THEN 2000 " .
                            "WHEN 'avanzado' THEN 3000 " .
                            "END)"
                        )
                        )
                    )
                    ->where('di.id_idioma != ?', -1)
                    ->where($where)
                    ->group(new Zend_Db_Expr("CONCAT(di.id_postulante, '-', di.id_idioma)")),
                //INFORMACION DE PROGRAMAS DE COMPUTO
                    $db->select()
                    ->from(array('p' => 'postulante'), array())
//                ->join(array('u' => 'usuario'), 'p.id_usuario = u.id', array())
                    ->join(
                        array('dpc' => 'dominio_programa_computo'), 'dpc.id_postulante = p.id', array(
                        'dpc.id_postulante',
                        'item' => new Zend_Db_Expr(2),
                        'print' => new Zend_Db_Expr(
                            "(dpc.id_programa_computo * 10000) " .
                            "+ " .
                            "(CASE dpc.nivel " .
                            "WHEN 'basico' THEN 1000 " .
                            "WHEN 'intermedio' THEN 2000 " .
                            "WHEN 'avanzado' THEN 3000 " .
                            "END)"
                        )
                        )
                    )
                    ->where('dpc.id_programa_computo > ?', 0)
                    ->where($where)
                    ->group(new Zend_Db_Expr("CONCAT(dpc.id_postulante, '-', dpc.id_programa_computo)")),
                //INFORMACION DE ESTUDIOS
                    $db->select()
                    ->from(array('p' => 'postulante'), array())
//                ->join(array('u' => 'usuario'), 'p.id_usuario = u.id', array())
                    ->join(array('e' => 'estudio'), 'e.id_postulante = p.id', array())
                    ->join(
                        array('ne' => 'nivel_estudio'), 'e.id_nivel_estudio = ne.id', array(
                        'e.id_postulante',
                        'item' => new Zend_Db_Expr(3),
                        'print' => new Zend_Db_Expr("((COALESCE(e.`id_carrera`,0) * 10000) + (ne.peso * 1000))")
                        )
                    )
                    ->where($where)
                    ->group(
                        new Zend_Db_Expr(
                            "CONCAT(e.id_postulante, '-', e.id_nivel_estudio, '-'
                            , COALESCE(e.`id_carrera`,0))"
                        )
                    ),
                //INFORMACION DE EXPERIENCIA
                    $db->select()
                    ->from(array('p' => 'postulante'), array())
//                ->join(array('u' => 'usuario'), 'p.id_usuario = u.id', array())
                    ->join(array('e' => 'experiencia'), 'e.id_postulante = p.id', array())
                    ->join(array('a' => 'area'), 'e.id_area = a.id', array())
                    ->join(
                        array('np' => 'nivel_puesto'), 'e.id_nivel_puesto = np.id', array(
                        'e.id_postulante',
                        'item' => new Zend_Db_Expr(4),
                        'print' => new Zend_Db_Expr(
                            "((COALESCE(e.`id_area`,0) * 10000) + " .
                            "((COALESCE(e.`id_nivel_puesto`,0) * 1000)) + " .
                            "SUM(COALESCE( ((e.fin_ano * 12) - (11-e.fin_mes)) - " .
                            "((e.inicio_ano * 12) - (12-e.inicio_mes)),0)))"
                        )
                        )
                    )
                    ->where($where)
                    ->group(
                        new Zend_Db_Expr(
                            "CONCAT(e.id_postulante, '-', (COALESCE(e.`id_area`,0)), '-'
                            , (COALESCE(e.`id_nivel_puesto`,0)))"
                        )
                    )
            )
        );
        //echo $sql->assemble();
        return $sql;
    }
    
    public static function getPostulanteNewTable($where)
    {
        $obj = new Application_Model_Postulante();
        $db = $obj->getAdapter();
        $sql = $db->select()
            ->from(array('p' => 'postulante'), array('id_postulante' => 'id'))
            ->where($where);
        return $sql;
    }

    public static function markPostulantesForMatch(
        $tipoIn = array('preferencial', 'clasificado', 'soloweb'), $diasUltimoLogin = 180
    )
    {
        $obj = new Application_Model_Postulante();
        $db = $obj->getAdapter();
        $where = $db->quoteInto('for_match = ?', 1);
        $obj->update(array('for_match' => 0), $where);

        $db->query(
            "UPDATE postulante AS p INNER JOIN usuario AS u " .
            "ON u.id = p.`id_usuario`SET p.for_match = 1 " .
            "WHERE u.`ultimo_login` > DATE_SUB(NOW(), INTERVAL $diasUltimoLogin DAY) " .
            "AND prefs_confidencialidad = 0"
        );
        return true;
    }

    public static function getQueryPostulantesForMatch($prefix = 'p')
    {
        $obj = new App_Db_Table_Abstract();
        $db = $obj->getAdapter();

        $where = $db->quoteInto("$prefix.for_match = ?", 1);
        return $where;
    }
    
    //@codingStandardsIgnoreStart
    public function matchAnuncios(
        $idEmpresa, $idAnuncio, $pIdioma, $pComputo, $pEstudio, $pExperiencia, $tPeso,$pMinimo, $offset
    )
    {
        $config = Zend_Registry::get('config');
        $obj = new Application_Model_DominioIdioma();

        $sql = "
        INSERT INTO anuncio_postulante_match (id_postulante, id_anuncio_web, id_empresa, `match`, fh_creacion)
            SELECT b.*,NOW() FROM (
                SELECT a.id_postulante, $idAnuncio as id_anuncio_web, $idEmpresa as id_empresa, 
                ROUND(((SUM(a.idioma) + SUM(a.computo) + SUM(a.estudio) + SUM(a.experiencia))/$tPeso)*100,'2') as `match`
                FROM (
                    SELECT id_postulante, ap.item, SUM(IF(pp.`print`>=ap.`print`,1,((ap.top - (ap.print-pp.`print`))-ap.bottom)/(ap.top-ap.bottom)) )* $pIdioma AS 'idioma', 
                    0 'computo' , 0 'estudio', 0 'experiencia'
                    FROM postulante_profile AS pp
                    INNER JOIN anuncio_profile AS ap ON ap.`item` = pp.`item`
                    WHERE pp.`item` = 1 AND id_anuncio_web = $idAnuncio
                    AND pp.`print` <= ap.`top` AND pp.`print` > ap.`bottom`
                    GROUP BY id_postulante
                    UNION 
                    SELECT id_postulante, ap.item, 0 'idioma', 
                    SUM(IF(pp.`print`>=ap.`print`,1,((ap.top - (ap.print-pp.`print`))-ap.bottom)/(ap.top -ap.bottom)) )* $pComputo AS 'computo', 0 'estudio', 0 'experiencia'
                    FROM postulante_profile AS pp
                    INNER JOIN anuncio_profile AS ap ON ap.`item` = pp.`item`
                    WHERE pp.`item` = 2 AND id_anuncio_web = $idAnuncio
                    AND pp.`print` <= ap.`top` AND pp.`print` > ap.`bottom`
                    GROUP BY id_postulante
                    UNION
                    SELECT id_postulante, ap.item, 0 'idioma', 0 'computo', SUM(1 * $pEstudio) AS 'estudio' , 0 'experiencia'
                    FROM postulante_profile AS pp 
                    INNER JOIN anuncio_profile AS ap ON ap.`print` = pp.`print`
                    WHERE pp.`item` = 3 AND id_anuncio_web = $idAnuncio
                    GROUP BY id_postulante
                    UNION
                    SELECT id_postulante, ap.item, 0 'idioma', 0 'computo', 0 'estudio', 
                    SUM(IF(pp.`print`>=ap.`print`,1,(pp.print-ap.bottom)/(ap.print-ap.bottom)))* $pExperiencia AS 'experiencia'
                    FROM postulante_profile AS pp
                    INNER JOIN anuncio_profile AS ap ON ap.`item` = pp.`item`
                    WHERE pp.`item` = 4 AND id_anuncio_web = $idAnuncio
                    AND pp.`print` < ap.`top` AND pp.`print` > ap.`bottom`
                    GROUP BY id_postulante) AS a
                GROUP BY a.id_postulante) AS b
            WHERE b.`match`>= $pMinimo
        ON DUPLICATE KEY UPDATE
        `match`= VALUES(`match`), `fh_actualizacion` = NOW()";
        
        /*Funciona
         * $sql = "SELECT a.id_postulante, SUM(a.idioma) as idioma, SUM(a.computo) as computo, SUM(a.estudio) as estudio, SUM(a.experiencia) as experiencia 
                FROM (
                    SELECT id_postulante, ap.item, SUM(IF(pp.`print`>=ap.`print`,1,((ap.top - (ap.print-pp.`print`))-ap.bottom)/(ap.top-ap.bottom)) )* $pIdioma AS 'idioma', 
                    0 'computo' , 0 'estudio', 0 'experiencia'
                    FROM postulante_profile AS pp
                    INNER JOIN anuncio_profile AS ap ON ap.`item` = pp.`item`
                    WHERE pp.`item` = 1 AND id_anuncio_web = $idAnuncio
                    AND pp.`print` <= ap.`top` AND pp.`print` > ap.`bottom`
                    GROUP BY id_postulante
                    UNION 
                    SELECT id_postulante, ap.item, 0 'idioma', 
                    SUM(IF(pp.`print`>=ap.`print`,1,((ap.top - (ap.print-pp.`print`))-ap.bottom)/(ap.top -ap.bottom)) )* $pComputo AS 'computo', 0 'estudio', 0 'experiencia'
                    FROM postulante_profile AS pp
                    INNER JOIN anuncio_profile AS ap ON ap.`item` = pp.`item`
                    WHERE pp.`item` = 2 AND id_anuncio_web = $idAnuncio
                    AND pp.`print` <= ap.`top` AND pp.`print` > ap.`bottom`
                    GROUP BY id_postulante
                    UNION
                    SELECT id_postulante, ap.item, 0 'idioma', 0 'computo', SUM(1 * $pEstudio) AS 'estudio' , 0 'experiencia'
                    FROM postulante_profile AS pp 
                    INNER JOIN anuncio_profile AS ap ON ap.`print` = pp.`print`
                    WHERE pp.`item` = 3 AND id_anuncio_web = $idAnuncio
                    GROUP BY id_postulante
                    UNION
                    SELECT id_postulante, ap.item, 0 'idioma', 0 'computo', 0 'estudio', 
                    SUM(IF(pp.`print`>=ap.`print`,1,(pp.print-ap.bottom)/(ap.print-ap.bottom)))* $pExperiencia AS 'experiencia'
                    FROM postulante_profile AS pp
                    INNER JOIN anuncio_profile AS ap ON ap.`item` = pp.`item`
                    WHERE pp.`item` = 4 AND id_anuncio_web = $idAnuncio
                    AND pp.`print` < ap.`top` AND pp.`print` > ap.`bottom`
                    GROUP BY id_postulante) AS a
                GROUP BY id_postulante;";
        */
        
         /*
         $sql = "SELECT 
                        a.id_postulante,
                        (SUM(a.idioma) * 1) as idioma,
                        (SUM(a.computo) * 1) as computo,
                        (SUM(a.estudio) * 1) as estudio,
                       (SUM(a.experiencia) * 1) as experiencia
                FROM (
                SELECT pp.id_postulante,     
                       CASE WHEN pp. item = 1  THEN SUM(IF(pp. print >= ap. print,
                                      1,
                                      (ap.top - (ap.print - pp. print)) / ap.top)) * $pIdioma
                            ELSE 0 END AS 'idioma', 
                       CASE WHEN pp. item = 2 THEN SUM(IF(pp. print >= ap. print,
                                      1,
                                      (ap.top - (ap.print - pp. print)) / ap.top)) * $pComputo
                            ELSE 0 END AS 'computo',
                       CASE WHEN (pp. item = 3 AND ap.`print` = pp.`print`)  THEN SUM(1 * $pEstudio)
                            ELSE 0 END AS 'estudio',      
                       CASE WHEN pp. item = 4 THEN SUM(IF(pp. print >= ap. print,
                                      1,
                                      ((pp. print * 100) / ap. print) / 100)) * $pExperiencia
                            ELSE 0 END  AS 'experiencia'
                       FROM postulante_profile AS pp
                       INNER JOIN anuncio_profile AS ap ON ap. item = pp. item
                       WHERE pp. item IN (1,2,3,4)
                         AND id_anuncio_web = $idAnuncio
                         AND pp. print < ap. top
                         AND pp. print >= ap. bottom
                       GROUP BY id_postulante, pp.item) a
                GROUP BY a.id_postulante;";*/
            $adapter = $obj->getAdapter();
            
            $stm = $adapter->query($sql);
    }
    //@codingStandardsIgnoreEnd
    
    public function calculateMatch(
        $dataAnuncio, $offset, $pesoEstudio = 5, $pesoExperiencia = 7, $pesoIdioma = 3, $pesoComputo = 2
    )
    {
        
        $config = Zend_Registry::get('config');
        $obj = new Application_Model_AnuncioWeb();
        $sql = $obj->getAdapter()->select()
            ->from(
                array('pp' => 'postulante_profile'),
                array(
                    "pp.item" , "pp.id_postulante",
                    'idioma' => new Zend_Db_Expr(
                        "COALESCE((SELECT ROUND(MAX(IF(pp.`print`>=ap.`print`,1,
                        (ap.top - (ap.print-pp.`print`))/ap.top)),4) 
                        FROM anuncio_profile AS ap 
                        WHERE ap.id_anuncio_web = {$dataAnuncio['id_anuncio_web']} 
                        AND pp.`item` = 1 AND pp.`print` < ap.`top` 
                        AND pp.`print` > ap.`bottom`), 0) * {$pesoIdioma}"
                    ),
                    'computo' => new Zend_Db_Expr(
                        " COALESCE((SELECT ROUND(MAX(IF(pp.`print`>=ap.`print`,1,
                        (ap.top - (ap.print-pp.`print`))/ap.top)),4) 
                        FROM anuncio_profile AS ap 
                        WHERE ap.id_anuncio_web = {$dataAnuncio['id_anuncio_web']} 
                        AND pp.`item` = 2 AND pp.`print` < ap.`top` 
                        AND pp.`print` > ap.`bottom`), 0) * {$pesoComputo}"
                    ),
                    'estudio' => new Zend_Db_Expr(
                        "COALESCE((SELECT ROUND(MAX(1.0000),4) 
                        FROM anuncio_profile AS ap 
                        WHERE ap.id_anuncio_web = {$dataAnuncio['id_anuncio_web']} 
                        AND pp.`print` = ap.`print` 
                        AND pp.`item` = 3),0) * {$pesoEstudio}"
                    ),
                    'experiencia' => new Zend_Db_Expr(
                        "COALESCE((SELECT ROUND(MAX(IF(pp.`print`>=ap.`print`,1,((pp.`print`*100)/ap.`print`)/100)),4) 
                        FROM anuncio_profile AS ap 
                        WHERE ap.id_anuncio_web = {$dataAnuncio['id_anuncio_web']} 
                        AND pp.`print` < ap.`top` AND pp.`print` > ap.`bottom` 
                        AND pp.`item` = ap.`item` AND ap.`item` = 4), 0) * {$pesoExperiencia}"
                    ),
                )
            )
            ->order("pp.id_postulante ASC")
            ->limit($config->profileMatch->match->row, $offset);
            //echo $sql->assemble();
        return $obj->getAdapter()->fetchAll($sql);
    }
    
}