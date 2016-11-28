<?php

class Application_Model_AnuncioWeb extends App_Db_Table_Abstract
{
    protected $_name   = "anuncio_web";
    protected $_intervalosRemuneraciones;
    protected $_diasPublicacion;
    public $usarLucene = false;

    const ESTADO_REGISTRADO     = 'registrado';
    const MEDIO_PAGO_BONIFICADO = 'destacado_impreso';
    const ESTADO_PENDIENTE_PAGO = 'pendiente_pago';
    const ESTADO_EXTORNADO      = 'extornado';
    const ESTADO_PAGADO         = 'pagado';
    const ESTADO_PUBLICADO      = 'publicado';
    const ESTADO_DADO_BAJA      = 'dado_baja';
    const ESTADO_VENCIDO        = 'vencido';
    const ESTADO_EXTENDIDO      = 'extendido';
    const ESTADO_BANEADO        = 'baneado';
    const TIPO_SOLOWEB          = 'soloweb';
    const TIPO_WEB              = 'web';
    const TIPO_CLASIFICADO      = 'clasificado';
    const TIPO_PREFERENCIAL     = 'preferencial';
    const TIPO_DESTACADO        = 'destacado';
    const ONLINE                = 1;
    const CERRADO               = 1;
    const PROCESO_ACTIVO        = 1;
    const NO_CERRADO            = 0;
    const NO_ELIMINADO          = 0;
    const NO_BORRADOR           = 0;
    const JJC_ID                = 19044;
    const DESTACADO             = 1;
    const NO_DESTACADO          = 0;

    protected $_ordenTipoAviso = array("12", "11", "10", "9", "8", "7", "6", "5",
        "4", "3", "2",
        "1", null
    );
    private $_model            = null;

    public function __construct()
    {
        parent::__construct();
        $cparts       = explode('_', __CLASS__);
        $this->_model = $cparts[2];
    }

    public function insert(array $data)
    {
        if (!isset($data['buscamas'])) {
            $data['buscamas'] = 0;
        } else {
            $data['buscamas'] = (int) $data['buscamas'];
        }
        return parent::insert($data);
    }

    public function getUltimosAvisos()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $limit  = $this->_config->portadaPostulante->ultimosAvisos->limite;
        $sql    = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('url_aviso' => 'aw.url_id',
                'id_anuncio_web' => 'aw.id',
                'ubicacion' => 'u.display_name',
                'puesto' => 'aw.puesto',
                'slugaviso' => 'aw.slug',
//                    'empresa_rs' => 'aw.empresa_rs',
                'empresa_rs' => new Zend_Db_Expr(
                    "CASE e.es_persona_natural
                            WHEN 1 THEN 'Importante Empresa'
                            WHEN 0 THEN aw.empresa_rs END"
                ),
                'mostrar_empresa' => 'aw.mostrar_empresa',
                'urlaviso' => 'aw.url_id',
                'fh_pub' => 'aw.fh_pub')
            )
            ->join(
                array('e' => 'empresa'), 'aw.id_empresa = e.id',
                array(
                'logo' => new Zend_Db_Expr(
                    "CASE e.es_persona_natural
                        WHEN 1 THEN NULL
                        WHEN 0 THEN e.logo END"
                ),
                'razon_social' => 'aw.empresa_rs',
                )
            )
            ->joinleft(
                array('u' => 'ubigeo'), 'aw.id_ubigeo = u.id'
            )
            ->where('aw.online = ?', 1)
            ->where('aw.tipo <> ?', 'soloweb')
            ->where('DATE_SUB(CURDATE(),INTERVAL 5 DAY) <= aw.fh_pub')
            ->order('fh_pub DESC')
            ->limit($limit);
        $result = $this->getAdapter()->fetchAll($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getUltimosAvisosMembresias()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }

        $limit  = 10;
        $sql    = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('url_aviso' => 'aw.url_id',
                'id_anuncio_web' => 'aw.id',
                'ubicacion' => 'u.display_name',
                'puesto' => 'upper(aw.puesto)',
                'slugaviso' => 'aw.slug',
                'destacado' => 'aw.destacado',
//                    'empresa_rs' => 'aw.empresa_rs',
                'empresa_rs' => new Zend_Db_Expr(
                    "CASE aw.mostrar_empresa
                      WHEN 0 THEN aw.empresa_rs
                      WHEN 1 THEN e.nombre_comercial
                    END"
                ),
                'areas' => new Zend_Db_Expr(
                    "(SELECT ar.nombre FROM area ar WHERE ar.id=aw.id_area)"
                ),
                'slug_area' => new Zend_Db_Expr(
                    "(SELECT ar.slug FROM area ar WHERE ar.id=aw.id_area)"
                ),
                'slug_nivel' => new Zend_Db_Expr(
                    "(SELECT ar.slug FROM nivel_puesto ar WHERE ar.id=aw.id_nivel_puesto)"
                ),
                'mostrar_empresa' => 'aw.mostrar_empresa',
                'urlaviso' => 'aw.url_id',
                'fh_pub' => 'aw.fh_pub')
            )
            ->join(
                array('e' => 'empresa'), 'aw.id_empresa = e.id',
                array(
                'logo' => new Zend_Db_Expr(
                    "CASE aw.mostrar_empresa
                            WHEN 0 THEN NULL
                            WHEN 1 THEN aw.logo
                        END"
                ),
                'razon_social' => 'aw.empresa_rs',
                'empresaslug' => 'e.slug_empresa',
                )
            )
            ->join(
                array('em' => 'empresa_membresia'), 'em.id_empresa = e.id',
                array()
            )
            ->join(
                array('m' => 'membresia'), 'em.id_membresia = m.id',
                array('monto' => 'm.monto')
            )
            ->joinleft(
                array('u' => 'ubigeo'), 'aw.id_ubigeo = u.id',
                array(
                'ubicacionslug' => new Zend_Db_Expr(
                    "REPLACE(LOWER(u.nombre),' ','-')"
                )
                )
            )
            ->where('aw.online = ?', 1)
            ->where('aw.borrador = ?', 0)
            ->where('aw.cerrado = ?', 0)
            ->where('aw.eliminado = ?', 0)
            ->where('aw.chequeado = ?', 1)
            ->where('aw.estado = ?', 'pagado')
            ->where('em.estado = ?', 'vigente')
//                ->where('m.tipo = ?', 'membresia')
            ->where('aw.destacado = ?', 0)
            ->order('monto DESC')
            ->order('fh_pub DESC')
            ->group('aw.id_empresa')
            ->limit($limit);
        $result = $this->getAdapter()->fetchAll($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getUltimosAvisosDestacados()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }

        $limit  = 6;
        $sql    = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array(
                'url_aviso' => 'aw.url_id',
                'id_anuncio_web' => 'aw.id',
                'display_name' => 'u.display_name',
                'puesto' => 'upper(aw.puesto)',
                'slugaviso' => 'aw.slug',
                'destacado' => 'aw.destacado',
                'funciones' => 'aw.funciones',
                'prioridad' => 'aw.prioridad',
                'responsabilidades' => 'aw.responsabilidades',
                'empresa_rs' => new Zend_Db_Expr(
                    "CASE aw.mostrar_empresa
                      WHEN 0 THEN aw.empresa_rs
                      WHEN 1 THEN e.nombre_comercial
                    END"
                ),
                'areas' => new Zend_Db_Expr(
                    "(SELECT ar.nombre FROM area ar WHERE ar.id=aw.id_area)"
                ),
                'slug_area' => new Zend_Db_Expr(
                    "(SELECT ar.slug FROM area ar WHERE ar.id=aw.id_area)"
                ),
                'slug_nivel' => new Zend_Db_Expr(
                    "(SELECT ar.slug FROM nivel_puesto ar WHERE ar.id=aw.id_nivel_puesto)"
                ),
                'mostrar_empresa' => 'aw.mostrar_empresa',
                'urlaviso' => 'aw.url_id',
                'fh_pub' => 'aw.fh_pub')
            )
            ->join(
                array('e' => 'empresa'), 'aw.id_empresa = e.id',
                array(
                'logo' => new Zend_Db_Expr(
                    "CASE aw.mostrar_empresa
                            WHEN 0 THEN NULL
                            WHEN 1 THEN aw.logo
                        END"
                ),
                'razon_social' => 'aw.empresa_rs',
                'empresaslug' => 'e.slug_empresa',
                )
            )
            ->joinleft(
                array('u' => 'ubigeo'), 'aw.id_ubigeo = u.id',
                array(
                'ubicacionslug' => new Zend_Db_Expr(
                    "REPLACE(LOWER(u.nombre),' ','-')"
                ),
                'ubicacion' => 'u.nombre'
                )
            )
            ->where('aw.online = ?', 1)
            ->where('aw.estado = ?', 'pagado')
            ->where('aw.destacado = ?', 1)
            ->where('aw.logo != ?', '')
            ->where('aw.logo IS NOT NULL')
            //  ->group('aw.id_empresa')
            ->order('fh_pub DESC')
            ->limit($limit);
        $result = $this->getAdapter()->fetchAll($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getAvisosFeed()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $limit  = $this->_config->portadaPostulante->ultimosAvisosFeed->limite;
        $db     = $this->getAdapter();
        $sql    = $db->select()
            ->from(
                'anuncio_web',
                array(
                'logo',
                'url_id',
                'slug',
                'puesto',
                'funciones',
                'responsabilidades',
                'fh_pub'
                )
            )
            ->where('online = 1')
            ->where('DATE_SUB(CURDATE(),INTERVAL 5 DAY) <= fh_pub')
            ->order('fh_pub DESC')
            ->limit($limit);
        $result = $db->fetchAll($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getUltimosAvisosDest()
    {
        $limit = $this->_config->portadaPostulante->avisosDestacados->limite;
        $sql   = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('ubicacion' => 'u.display_name',
                'puesto' => 'aw.puesto',
                'slug' => 'aw.slug',
                'fh_pub' => 'aw.fh_pub',)
            )
            ->join(
                array('e' => 'empresa'), 'aw.id_empresa = e.id'
            )
            ->join(
                array('u' => 'ubigeo'), 'aw.id_ubigeo = u.id'
            )
            ->where('aw.online = 1')
            ->where('aw.destacado = ?', '1')
            ->order('fh_pub DESC')
            ->limit($limit);
        $rs    = $this->getAdapter()->fetchAll($sql);
        //$cache->save($rs, $cacheId, array(), 3600);

        return $rs;
    }

    public function getGroupArea($tipoOrden = 1)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        /* if ($this->_cache->test($cacheId)) {
          return $this->_cache->load($cacheId);
          } */
        $sql     = $this->getAdapter()->select()
            ->from(
                array('area' => 'area'),
                array(
                'ind' => 'id',
                'cant' => 'contador_anuncios',
                'slug' => 'slug',
                'msg' => 'nombre',
                )
            )
            ->where('contador_anuncios > 0');

        switch ($tipoOrden) {
            case 1:
                $sql = $sql->order('nombre ASC');
                break;
            case 2:
                $sql = $sql->order('contador_anuncios DESC');
                break;
        }

        $result = $this->getAdapter()->fetchAssoc($sql);
        //$this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getGroupEmpresa()
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('ind' => 'id_empresa', 'cant' => 'count(aw.id)')
            )
            ->join(
                array('e' => 'empresa'), 'aw.id_empresa = e.id',
                array('razon_social as msg')
            )
            ->where('aw.online = 1')
            ->group('aw.id_empresa')
            ->order('cant DESC');
        $rs  = $this->getAdapter()->fetchAssoc($sql);
        //$cache->save($rs, $cacheId, array(), 3600);
        return $rs;
    }

    public function getGroupUbicacionOrdenAlf($padre)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        $cache   = $this->_cache;
        if ($cache->test($cacheId)) {
            return $cache->load($cacheId);
        }
        $data = $this->getGroupUbicacion($padre);
        $cache->save($data, $cacheId, array(), $cacheEt);
        return $data;
    }

    public function getGroupUbicacionOrdenNum($padre)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        $cache   = $this->_cache;
        if ($cache->test($cacheId)) {
            return $cache->load($cacheId);
        }
        $data = $this->getGroupUbicacion($padre);
        foreach ($data as $key => $row) {
            $cant[$key] = $row['cant'];
        }
        if (isset($cant)) {
            array_multisort($cant, SORT_DESC, $data);
        }
        $cache->save($data, $cacheId, array(), $cacheEt);
        return $data;
    }

    public function getGroupNivelPuesto($tipoOrden = 1)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        /* if ($this->_cache->test($cacheId)) {
          return $this->_cache->load($cacheId);
          }
         * */

        $sql = $this->getAdapter()->select()
            ->from(
                array('np' => 'nivel_puesto'),
                array('ind' => 'id',
                'cant' => 'contador_anuncios',
                'msg' => 'nombre',
                'slug' => 'slug')
            )
            ->where('contador_anuncios > 0');

        switch ($tipoOrden) {
            case 1:
                $sql = $sql->order('nombre ASC');
                break;
            case 2:
                $sql = $sql->order('contador_anuncios DESC');
                break;
        }

        $result = $this->getAdapter()->fetchAssoc($sql);
        //$this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getGroupAreaOrdenAlf()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        /* $cache = $this->_cache;
          if ($cache->test($cacheId)) {
          return $cache->load($cacheId);
          }
         *
         */
        $data    = $this->getGroupArea(1);
        //$cache->save($data, $cacheId, array(), $cacheEt);
        return $data;
    }

    public function getGroupAreaOrdenNum()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        /* $cache = $this->_cache;
          if ($cache->test($cacheId)) {
          return $cache->load($cacheId);
          }
         */
        $data    = $this->getGroupArea(2);
        /*
          foreach ($data as $key => $row) {
          $cant[$key]  = $row['cant'];
          }
          if (isset($cant)) {
          array_multisort($cant, SORT_DESC, $data);
          }
          $cache->save($data, $cacheId, array(), $cacheEt);
         */
        return $data;
    }

    public function getGroupNivelPuestoOrdenAlf()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        /* $cache = $this->_cache;
          if ($cache->test($cacheId)) {
          return $cache->load($cacheId);
          } */
        $data    = $this->getGroupNivelPuesto(1);
        //$cache->save($data, $cacheId, array(), $cacheEt);
        return $data;
    }

    public function getGroupNivelPuestoOrdenNum()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        /* $cache = $this->_cache;
          if ($cache->test($cacheId)) {
          return $cache->load($cacheId);
          } */
        $data    = $this->getGroupNivelPuesto(2);
        /*
          foreach ($data as $key => $row) {
          $cant[$key]  = $row['cant'];
          }

          if (isset($cant)) {
          array_multisort($cant, SORT_DESC, $data);
          }
         */
        //$cache->save($data, $cacheId, array(), $cacheEt);
        return $data;
    }

    public function getGroupUbicacion($padre, $tipoOrden = 1)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__.implode('_', $padre);
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->getAdapter()->select()
            ->from(
                array('u' => 'ubigeo'),
                array('padre' => 'padre',
                'cant' => 'contador_anuncios',
                'ind' => 'id',
                'slug' => "REPLACE(search_name,' ','-')",
                'msg' => 'nombre')
            )
            ->where('padre IN (?)', array($padre))
            ->where('contador_anuncios > 0');
        switch ($tipoOrden) {
            case 1:
                $sql = $sql->order('nombre ASC');
                break;
            case 2:
                $sql = $sql->order('contador_anuncios DESC');
                break;
        }

        $result = $this->getAdapter()->fetchAll($sql);
        //$this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    /**
     *
     * @param type $padre
     * @return type
     */
    public function getGroupUbicacionCombo($padre)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__.'_'.$padre;
        /* if ($this->_cache->test($cacheId)) {
          return $this->_cache->load($cacheId);
          } */
        $sql     = $this->getAdapter()->select()
            ->from(
                array('u' => 'ubigeo'),
                array('slug' => "REPLACE(search_name,' ','-')",
                'msg' => "concat(nombre,' (',contador_anuncios,')' )",
                'ind' => 'id')
            )
            ->where('padre IN (?)', array($padre))
            ->where('contador_anuncios > 0')
            ->order('nombre ASC');
        $result  = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getGroupUbicacionComboPaises()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql    = $this->getAdapter()->select()
            ->from(
                array('u' => 'ubigeo'),
                array('slug' => "REPLACE(search_name,' ','-')",
                'msg' => "concat(nombre,'(',contador_anuncios,')' )",
                'ind' => 'id')
            )
            ->where('padre IS NULL')
            ->where('id !=?', Application_Model_Ubigeo::PERU_UBIGEO_ID)
            ->where('contador_anuncios > 0')
            ->order('nombre ASC');
        $result = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getGroupDistritos()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql    = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('ind' => 'id_ubigeo',
                'cant' => 'count(aw.id)',
                'msg' => 'dist.nombre')
            )
            ->join(
                array('dist' => 'ubigeo'),
                'aw.id_ubigeo = dist.id AND dist.level = 3',
                array('slug' => "REPLACE(dist.search_name,' ','-')")
            )
            ->join(array('prov' => 'ubigeo'), 'dist.padre = prov.id', array())
            ->where('dist.padre = 3927')
            ->where('aw.online = 1')
            ->group('aw.id_ubigeo')
            ->order('cant DESC');
        $result = $this->getAdapter()->fetchAssoc($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getGroupDistritosLima()
    {
        /*        $cache = Zend_Registry::get('cache');
          $cacheId = 'group_distrito_list';
          if ($cache->test($cacheId)) {
          return $cache->load($cacheId);
          } */

        $sql = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('slug' => 'REPLACE(dist.search_name," ","-")',
                'nombre' => "concat(dist.nombre,' (',count(aw.id),')')",
                'ind' => 'id_ubigeo')
            )
            ->join(
                array('dist' => 'ubigeo'),
                'aw.id_ubigeo = dist.id AND dist.level = 3', array()
            )
            ->join(array('prov' => 'ubigeo'), 'dist.padre = prov.id', array())
            ->where('prov.padre = 3926')
            ->where('aw.online = 1')
            ->group('aw.id_ubigeo');
        $rs  = $this->getAdapter()->fetchPairs($sql);
        //$cache->save($rs, $cacheId, array(), 3600);

        return $rs;
    }

    public function getGroupDepartamentos()
    {
        /*        $cache = Zend_Registry::get('cache');
          $cacheId = 'group_distrito_list';
          if ($cache->test($cacheId)) {
          return $cache->load($cacheId);
          } */

        $sql = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('slug' => 'REPLACE(dpto.search_name," ","-")',
                'msg' => "concat(dpto.nombre, ' (', count(aw.id), ')' )",
                'ind' => 'dpto.id')
            )
            ->join(
                array('dpto' => 'ubigeo'),
                'aw.id_ubigeo = dpto.id AND dpto.level = 1', array()
            )
            ->group('aw.id_ubigeo');
        //echo $sql->assemble($sql);
        $rs  = $this->getAdapter()->fetchPairs($sql);
        //$cache->save($rs, $cacheId, array(), 3600);

        return $rs;
    }

    public function getGroupPaises()
    {
        /*        $cache = Zend_Registry::get('cache');
          $cacheId = 'group_distrito_list';
          if ($cache->test($cacheId)) {
          return $cache->load($cacheId);
          } */

        $sql = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('slug' => 'REPLACE(pais.search_name," ","-")',
                'msg' => "concat(pais.nombre, ' (', count(aw.id), ')' )",
                'ind' => 'pais.id')
            )
            ->join(
                array('pais' => 'ubigeo'),
                'aw.id_ubigeo = pais.id AND pais.level = 0', array()
            )
            ->group('aw.id_ubigeo');
        $rs  = $this->getAdapter()->fetchPairs($sql);
        //$cache->save($rs, $cacheId, array(), 3600);

        return $rs;
    }

    public function getRangoRemuneracionesBusqueda($tipoOrden = 1)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        /* if ($this->_cache->test($cacheId)) {
          return $this->_cache->load($cacheId);
          }
         */
        $sql     = $this->getAdapter()->select()
            ->from(
            array('crr' => 'contador_rango_remuneracion'),
            array(
            'msg' => 'nombre',
            'slug' => 'slug',
            'cant' => 'contador_anuncios'
            )
        );

        switch ($tipoOrden) {
            case 1:
                $sql = $sql->order('id ASC');
                break;
            case 2:
                $sql = $sql->order('contador_anuncios DESC');
                break;
        }
        $result = $this->getAdapter()->fetchAssoc($sql);
        //echo $sql->assemble();
        //$this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getFechasPublicacionBusquedas()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql    = $this->getAdapter()->select()
            ->from(
                array('cfp' => 'contador_fecha_publicacion'),
                array(
                'msg' => 'nombre',
                'slug' => 'slug',
                'cant' => 'contador_anuncios'
                )
            )
            ->order('id ASC');
        $result = $this->getAdapter()->fetchAssoc($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    /**
     * Retorna un array de estudios de un anuncio web.
     *
     * @param int $anuncioId
     * @return Array
     */
    public function getEstudiosByAnuncio($anuncioId)
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('ae' => 'anuncio_estudio'),
                array(
                'ae.id',
                'nivel_estudio' => 'ne.nombre',
                'nivel_estudio_tipo' => 'net.nombre',
                'carrera' => 'c.nombre',
                'otra_carrera' => 'ae.otra_carrera'
                )
            )
            ->joinLeft(
                array('c' => 'carrera'), 'ae.id_carrera = c.id', array()
            )
            ->joinLeft(
                array('ne' => 'nivel_estudio'), 'ae.id_nivel_estudio = ne.id',
                array()
            )
            ->joinLeft(
                array('net' => 'nivel_estudio'),
                'ae.id_nivel_estudio_tipo = net.id', array()
            )
            ->where('ae.id_anuncio_web = ?', $anuncioId);
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * Retorna un array de experiencias de un anuncio web.
     *
     * @param int $anuncioId
     * @return Array
     */
    public function getExperienciasByAnuncio($anuncioId)
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('aex' => 'anuncio_experiencia'),
                array(
                'aex.id',
                'experiencia' => 'aex.experiencia',
                'nombre_puesto' => 'p.nombre',
                'nivel_puesto' => 'np.nombre',
                'nombre_area' => 'a.nombre',
                'rubro' => 'r.nombre'
                )
            )
            ->joinLeft(
                array('p' => 'puesto'), 'aex.id_puesto = p.id', array()
            )
            ->joinLeft(
                array('np' => 'nivel_puesto'), 'aex.id_nivel_puesto = np.id',
                array()
            )
            ->joinLeft(
                array('a' => 'area'), 'aex.id_area = a.id', array()
            )
            ->joinLeft(
                array('r' => 'rubro'), 'aex.id_rubro = r.id', array()
            )
            ->where('aex.id_anuncio_web = ?', $anuncioId);
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * Retorna un array de idiomas de un anuncio web.
     *
     * @param int $anuncioId
     * @return Array
     */
    public function getIdiomasByAnuncio($anuncioId)
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('ai' => 'anuncio_idioma'),
                array(
                'ai.id',
                'nombre' => 'i.nombre',
                'idioma' => 'ai.id_idioma',
                'nivel_idioma' => 'ai.nivel'
                )
            )
            ->join(array('i' => 'idioma'), ' ai.id_idioma = i.id_slug', array())
            ->where('ai.id_anuncio_web = ?', $anuncioId);
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * Retorna un array de programas de un anuncio web.
     *
     * @param int $anuncioId
     * @return Array
     */
    public function getProgramasByAnuncio($anuncioId)
    {
        $programaComputo = new Application_Model_ProgramaComputo();
        $db              = $this->getAdapter();
        $sql             = $db->select()
            ->from(
                array('apc' => 'anuncio_programa_computo'),
                array(
                'apc.id',
                'id_programa_computo' => 'apc.id_programa_computo',
                'nivel_programa' => 'apc.nivel',
                'nombre_programa' => 'pc.nombre'
                )
            )
            ->joinLeft(
                array('pc' => 'programa_computo'),
                'apc.id_programa_computo = pc.id', array()
            )
            ->where('apc.id_anuncio_web = ?', $anuncioId);

        $rs = $this->getAdapter()->fetchAll($sql);
        // for ($i = 0; $i < count($rs); $i++) {
        //     $rs[$i]['nombre_programa'] = $rs[''];// $programaComputo->findPrograma($rs[$i]['id_programa_computo']);
        // }
        return $rs;
    }

    /**
     * Retorna el aviso deacuerdo a la url id
     *
     * @param string $urlId
     * @return array
     */
    public function getAvisoBySlug($urlId, $considera = 0)
    {
        /* $cacheId = $this->_prefix . $urlId;
          if ($this->_cache->test($cacheId) && $considera == 0) {
          return $this->_cache->load($cacheId);
          } */

        $db = $this->getAdapter();

        $whereEstado = $db->quoteInto('aw.online =? OR ', self::ONLINE);

        $whereEstado .= $db->quoteInto('aw.cerrado =?', self::CERRADO);

        $sql = $db->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('aw.id', 'aw.puesto', 'aw.funciones', 'aw.responsabilidades',
                'aw.slug', 'aw.id_empresa', 'aw.salario_min', 'aw.salario_max',
                'aw.url_id', 'aw.mostrar_salario',
                'aw.online', 'aw.borrador', 'aw.cerrado',
                'aw.mostrar_empresa', 'aw.fh_vencimiento',
                'aw.fh_vencimiento_proceso',
                'nombre_empresa' => 'e.razon_social', 'aw.estado',
                //'nombre_empresa' => 'aw.empresa_rs',
                'nombre_comercial' => new Zend_Db_Expr(
                    "CASE aw.mostrar_empresa
                        WHEN 0 THEN aw.empresa_rs
                        WHEN 1 THEN e.nombre_comercial END"
                ),
                'ciudad' => 'u.display_name',
                'area_puesto' => 'a.nombre',
                'area_puesto_slug' => 'a.slug',
                'nivel_puesto_slug' => 'np.slug',
                'redireccion' => 'aw.redireccion'
                //,'nombre_comercial' => 'e.nombre_comercial'
                )
            )
            ->joinLeft(
                array('e' => 'empresa'), 'aw.id_empresa = e.id',
                array('logo_empresa' => 'e.logo',
                'logo_facebook' => 'e.logo3')
            )
            ->joinLeft(
                array('u' => 'ubigeo'), 'aw.id_ubigeo = u.id',
                array('ubigeo_nombre' => 'u.nombre')
            )
            ->joinLeft(
                array('a' => 'area'), 'aw.id_area = a.id', array()
            )
            ->joinLeft(
                array('np' => 'nivel_puesto'), 'aw.id_nivel_puesto = np.id',
                array('nivel_puesto_nombre' => 'np.nombre')
            )
            ->where('aw.url_id = ?', $urlId)
            ->where($whereEstado)
            ->order('aw.id DESC');

        $anuncio = $this->getAdapter()->fetchRow($sql);

        if ($anuncio === false || $anuncio == null) {
            return null;
        }
        if ($anuncio['mostrar_empresa'] != 1) {
            $anuncio['logo_empresa'] = '';
        }
        $anuncio['estudios']     = $this->getEstudiosByAnuncio($anuncio['id']);
        $anuncio['experiencias'] = $this->getExperienciasByAnuncio($anuncio['id']);
        $anuncio['idiomas']      = $this->getIdiomasByAnuncio($anuncio['id']);
        $anuncio['programas']    = $this->getProgramasByAnuncio($anuncio['id']);

        /* $this->_cache->save(
          $anuncio, $cacheId, array(),
          $this->_config->cache->AnuncioWeb->getAvisoBySlug
          ); */

        return $anuncio;
    }

    public function getAvisoBySlugByIdAw($urlId, $idAw = null, $considera = 0)
    {
        $cacheId = $this->_prefix.$urlId.'_'.$idAw;

        //var_dump($cacheId,$this->_config->cache->AnuncioWeb->getAvisoBySlug);exit;
        if ($this->_cache->test($cacheId) && $considera == 0) {
            return $this->_cache->load($cacheId);
        }
        $db      = $this->getAdapter();
        $sql     = $db->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('aw.id', 'puesto' => new Zend_Db_Expr('UPPER(aw.puesto)'),
                'tituloaviso' => 'aw.puesto', 'aw.funciones', 'aw.responsabilidades',
                'aw.slug', 'aw.id_empresa', 'aw.salario_min', 'aw.salario_max',
                'aw.url_id', 'aw.mostrar_salario',
                'aw.online', 'aw.borrador', 'aw.cerrado', 'aw.eliminado',
                'aw.mostrar_empresa',
                'nombre_empresa' => 'e.nombre_comercial',
                //'nombre_empresa' => 'aw.empresa_rs',
                'nombre_comercial' => new Zend_Db_Expr(
                    "CASE aw.mostrar_empresa
                        WHEN 0 THEN aw.empresa_rs
                        WHEN 1 THEN e.nombre_comercial END"
                ),
                'mostrar_empresa' => 'aw.mostrar_empresa',
                'logo_empresa' => 'aw.logo',
                'ciudad' => 'u.display_name',
                'area_puesto' => 'a.nombre',
                'area_puesto_slug' => 'a.slug',
                'nivel_puesto_slug' => 'np.slug',
                'redireccion' => 'aw.redireccion'
                //,'nombre_comercial' => 'e.nombre_comercial'
                )
            )
            ->joinLeft(
                array('e' => 'empresa'), 'aw.id_empresa = e.id', array()
            )
            ->joinLeft(
                array('u' => 'ubigeo'), 'aw.id_ubigeo = u.id', array()
            )
            ->joinLeft(
                array('a' => 'area'), 'aw.id_area = a.id', array()
            )
            ->joinLeft(
                array('np' => 'nivel_puesto'), 'aw.id_nivel_puesto = np.id',
                array()
            )
            ->where('aw.url_id = ?', $urlId)
            ->where('aw.id = ?', $idAw);
        $anuncio = $this->getAdapter()->fetchRow($sql);

        if ($anuncio === false || $anuncio == null) {
            return null;
        }
        if ($anuncio['mostrar_empresa'] != 1) {
            $anuncio['logo_empresa'] = '';
        }
        $anuncio['estudios']     = $this->getEstudiosByAnuncio($anuncio['id']);
        $anuncio['experiencias'] = $this->getExperienciasByAnuncio($anuncio['id']);
        $anuncio['idiomas']      = $this->getIdiomasByAnuncio($anuncio['id']);
        $anuncio['programas']    = $this->getProgramasByAnuncio($anuncio['id']);
        $this->_cache->save(
            $anuncio, $cacheId, array(),
            $this->_config->cache->AnuncioWeb->getAvisoBySlug
        );

        return $anuncio;
    }

    public function getAvisoById($anuncioId, $cache = TRUE)
    {

        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__.'_'.$anuncioId;
        if ($this->_cache->test($cacheId) && $cache) {
            return $this->_cache->load($cacheId);
        }


        $db  = $this->getAdapter();
        $sql = $db->select()->from(
                array('aw' => 'anuncio_web'),
                array(
                'aw.id',
                'aw.id_producto',
                'aw.id_puesto',
                'puesto' => new Zend_Db_Expr('UPPER(aw.puesto)'),
                'aw.funciones', 'aw.responsabilidades', 'aw.slug', 'aw.url_id',
                'aw.salario_min', 'aw.id_area', 'aw.mostrar_salario', 'aw.fh_pub',
                'aw.salario_min', 'aw.salario_max', 'aw.online', 'aw.borrador',
                'aw.id_nivel_puesto', 'nombre_empresa' => 'e.razon_social',
                //'nombre_comercial' => 'e.nombre_comercial',
                'nombre_comercial' => new Zend_Db_Expr(
                    "CASE aw.mostrar_empresa
                        WHEN 0 THEN aw.empresa_rs
                        WHEN 1 THEN e.nombre_comercial END"
                ),
                'aw.id_tarifa', 'aw.tipo', 'aw.medio_publicacion',
                'aw.id_anuncio_impreso',
                'logo_empresa' => 'aw.logo',
                'aw.mostrar_empresa',
                'ciudad' => 'u.display_name',
                'id_producto' => 'aw.id_producto',
                'fcreacion' => new Zend_Db_Expr('DATE_FORMAT(aw.fh_pub,"%d/%m/%Y")'),
                'fpublicacion' => new Zend_Db_Expr('DATE_FORMAT(aw.fh_vencimiento,"%d/%m/%Y")'),
                'fvencimiento' => new Zend_Db_Expr('DATE_FORMAT(aw.fh_vencimiento_proceso,"%d/%m/%Y")'),
                'slug' => 'aw.slug',
                'url_id' => 'aw.url_id',
                'tipo_puesto' => 'p.nombre',
                'aw.id_empresa',
                'aw.estado',
                'aw.estado_anterior',
                'aw.cerrado',
                'aw.eliminado',
                'aw.proceso_activo',
                'aw.adecsys_code',
                'aw.creado_por',
                'aw.correo'
            ))
            ->joinLeft(
                array('e' => 'empresa'), 'aw.id_empresa = e.id', array()
            )
            ->joinLeft(
                array('p' => 'puesto'), 'aw.id_puesto = p.id', array()
            )
            ->joinLeft(
                array('u' => 'ubigeo'), 'aw.id_ubigeo = u.id', array()
            )
            ->where('aw.id = ?', $anuncioId);

        $anuncio = $this->getAdapter()->fetchRow($sql);

        if ($anuncio == null) {
            return null;
        }
        if ($anuncio['mostrar_empresa'] != 1) {
            $anuncio['logo_empresa'] = '';
        }
        $anuncio['estudios']     = $this->getEstudiosByAnuncio($anuncio['id']);
        $anuncio['experiencias'] = $this->getExperienciasByAnuncio($anuncio['id']);
        $anuncio['idiomas']      = $this->getIdiomasByAnuncio($anuncio['id']);
        $anuncio['programas']    = $this->getProgramasByAnuncio($anuncio['id']);
        $result                  = $anuncio;
        $this->_cache->save($result, $cacheId, array(), $cacheEt);

        return $anuncio;
    }

    public function getFullAvisoById($anuncioId)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__.'_'.$anuncioId;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $db      = $this->getAdapter();
        $sql     = $db->select()
            ->from(
                array('aw' => 'anuncio_web'), array('*')
            )
            ->joinLeft(
                array('e' => 'empresa'), 'aw.id_empresa = e.id', array()
            )
            ->joinLeft(
                array('u' => 'ubigeo'), 'e.id_ubigeo = u.id', array()
            )
            ->where('aw.id = ?', $anuncioId);
        $anuncio = $this->getAdapter()->fetchRow($sql);
        if ($anuncio == null) {
            return null;
        }
        $anuncio['estudios']     = $this->getEstudiosByAnuncio($anuncio['id']);
        $anuncio['experiencias'] = $this->getExperienciasByAnuncio($anuncio['id']);
        $anuncio['idiomas']      = $this->getIdiomasByAnuncio($anuncio['id']);
        $anuncio['programas']    = $this->getProgramasByAnuncio($anuncio['id']);

        $result = $anuncio;
        $this->_cache->save($result, $cacheId, array(), $cacheEt);

        return $anuncio;
    }

    public function getEstudioInfoByAnuncio($anuncioId)
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('ae' => 'anuncio_estudio'),
                array('ae.id', 'ae.id_nivel_estudio', 'ae.id_carrera', 'otra_carrera' => 'ae.otra_carrera',
                'ae.id_nivel_estudio', 'ae.id_nivel_estudio_tipo')
            )
            ->where('ae.id_nivel_estudio != 9')
            ->where('ae.id_anuncio_web = ?', $anuncioId);
        //echo $sql->assemble();
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getExperienciaInfoByAnuncio($anuncioId)
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('aex' => 'anuncio_experiencia'),
                array(
                'aex.id', 'aex.id_nivel_puesto', 'aex.id_area', 'aex.experiencia'
                )
            )
            ->where('aex.id_anuncio_web = ?', $anuncioId);
        //echo $sql->assemble();
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getIdiomaInfoByAnuncio($anuncioId)
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('ai' => 'anuncio_idioma'),
                array('ai.id', 'ai.id_idioma', 'nivel_idioma' => 'ai.nivel')
            )
            ->where('ai.id_anuncio_web = ?', $anuncioId);
        //echo $sql->assemble();
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getProgramaInfoByAnuncio($anuncioId)
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('apc' => 'anuncio_programa_computo'),
                array('apc.id', 'apc.id_programa_computo', 'apc.nivel')
            )
            ->where('apc.id_anuncio_web = ?', $anuncioId);
        //echo $sql->assemble();
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getPreguntaInfoByAnuncio($anuncioId)
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('p' => 'pregunta'),
                array(
                'p.id', 'p.pregunta'
                )
            )
            ->joinLeft(
                array('c' => 'cuestionario'), 'p.id_cuestionario = c.id',
                array()
            )
            ->where('p.estado = ?', 1)
            ->where('c.id_anuncio_web = ?', $anuncioId);
        //echo $sql->assemble();
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getAvisoInfoById($avisoId)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__.'_'.$avisoId;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $db      = $this->getAdapter();
        $sql     = $db->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('aw.id', 'aw.id_area', 'aw.id_nivel_puesto', 'aw.empresa_rs',
                'id_puesto', 'aw.id_empresa', 'nombre_puesto' => 'aw.puesto',
                'aw.funciones', 'aw.responsabilidades', 'aw.slug', 'aw.url_id',
                'salario_min' => 'aw.salario_min',
                'empresa_nombre' => 'e.nombre_comercial',
                'mostrar_empresa' => 'aw.mostrar_empresa',
                'salario' => 'CONCAT(aw.salario_min,"-",aw.salario_max)', 'aw.online',
                'aw.estado', 'aw.id_ubigeo',
                'aw.chequeado', 'aw.creado_por',
                'aw.mostrar_salario',
                'aw.id_tarifa', 'aw.discapacidad',
                'aw.correo',
                'aw.adecsys_code')
            )
            ->joinLeft(
                array('e' => 'empresa'), 'aw.id_empresa = e.id', array()
            )
            ->where('aw.id = ?', $avisoId);
        $result  = $anuncio = $this->getAdapter()->fetchRow($sql);
        if ($anuncio == null) {
            return null;
        }
        $this->_cache->save($result, $cacheId, array(), $cacheEt);

        return $anuncio;
    }

    public function getAvisoIdByUrl($urlId)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__.'_'.$urlId;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $db     = $this->getAdapter();
        $sql    = $db->select()
                ->from(array('aw' => 'anuncio_web'),
                    array('aw.id', "aw.creado_por"))
                ->where('aw.url_id = ?', $urlId)
                ->where('aw.online = ?', 1)->order('aw.id');
        $result = $this->getAdapter()->fetchAll($sql);
        $rs     = $result[0];
        if (count($result) == 0) {
            $sql    = $db->select()
                ->from(array('aw' => 'anuncio_web'),
                    array('aw.id', "aw.creado_por"))
                ->where('aw.url_id = ?', $urlId)
                ->where('aw.online = ?', 0);
            $result = $this->getAdapter()->fetchAll($sql);
            $rs     = $result[0];
        }
        $this->_cache->save($rs, $cacheId, array(), $cacheEt);


        return $result[0];
    }

    /**
     * Retorna los valores minimos del anuncio web
     *
     * @param int $urlId
     */
    public function getLiteAvisoByUrl($urlId)
    {
        $db = $this->getAdapter();

        $whereUrlId = $db->quoteInto('aw.url_id =?', $urlId);

        $sql = $db->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('aw.id', 'aw.url_id', 'aw.slug')
            )
            ->where($whereUrlId)
            ->where('aw.chequeado = 1')
            ->order('aw.id desc');
        return $this->getAdapter()->fetchRow($sql);
    }

    public function getLiteAvisoByEmpresa($Id)
    {
        $db = $this->getAdapter();

        $whereUrlId = $db->quoteInto('aw.id_empresa =?', $Id);

        $sql = $db->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('aw.id', 'aw.url_id', 'aw.estado')
            )
            ->where($whereUrlId)
            ->where('aw.online = 1')
            ->order('aw.id desc');
        return $this->getAdapter()->fetchRow($sql);
    }

    /**
     * Retorna los datos necesarios para armar la url del aviso a partir
     * del ID del anuncio web
     *
     * @param int $avisoId
     */
    public function getUrlInfoById($avisoId)
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('aw' => 'anuncio_web'), array('aw.slug', 'aw.url_id')
            )
            ->where('aw.id = ?', $avisoId);
        return $this->getAdapter()->fetchRow($sql);
    }

    public function getFichaInfoById($avisoId)
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('aw' => 'anuncio_web'), array('aw.slug', 'aw.url_id')
            )
            ->where('aw.id = ?', $avisoId);
        return $this->getAdapter()->fetchRow($sql);
    }

    public function getAvisosRelacionadosPasoTres($idPostulante)
    {
        $adapter = $this->getAdapter();

        $sql  = 'SELECT DISTINCT aw.*
                FROM anuncio_web aw
                LEFT JOIN anuncio_estudio ae ON ae.id_anuncio_web = aw.id
                LEFT JOIN estudio e ON e.id_carrera = ae.id_carrera AND ae.id_nivel_estudio = e.id_nivel_estudio
                LEFT JOIN experiencia ex ON ex.id_nivel_puesto = aw.id_nivel_puesto AND ex.id_area = aw.id_area
                WHERE aw.online=1 AND
                ex.id_postulante ='.$idPostulante.' limit 5';
        //echo $sql; exit;
        $stmp = $adapter->query($sql);
        return $stmp->fetchAll();
    }

    public function getAvisoRelacionados($idAnuncioWeb, $limit = 4)
    {
        $db = $this->getAdapter();

        $sql   = "SELECT aw.id,aw.empresa_rs, aw.puesto, u.display_name, aw.url_id, aw.slug,
            aw.logo, aw.mostrar_empresa
            FROM anuncio_web aw
            INNER JOIN ubigeo u ON u.id = aw.id_ubigeo
            WHERE id_area = (SELECT id_area FROM anuncio_web aw1 WHERE ".
            $db->quoteInto('aw1.id = ?', $idAnuncioWeb).")
            AND id_nivel_puesto = (SELECT id_nivel_puesto FROM anuncio_web aw2 WHERE ".
            $db->quoteInto('aw2.id = ?', $idAnuncioWeb)." )
            AND ".$db->quoteInto('aw.id != ?', $idAnuncioWeb)."
            AND aw.online = 1 LIMIT ".
            $db->quoteInto('?', $limit);
        $stmp  = $db->query($sql);
        $valor = $stmp->fetchAll();
        return $valor;
    }

    public function getAvisoRelacionadosnew($idAnuncioWeb, $limit = 4)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__.'_'.$idAnuncioWeb;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }

        $db    = $this->getAdapter();
        $sql   = "SELECT aw.id,aw.empresa_rs, aw.puesto, u.display_name, aw.url_id, aw.slug,
            (aw.funciones) AS description,aw.fh_pub AS fh_pub,
            aw.logo, aw.mostrar_empresa ,
            (u.display_name  ) AS ubicacion
            FROM anuncio_web aw
            INNER JOIN ubigeo u ON u.id = aw.id_ubigeo
            WHERE id_area = (SELECT id_area FROM anuncio_web aw1 WHERE ".
            $db->quoteInto('aw1.id = ?', $idAnuncioWeb).")
            AND id_nivel_puesto = (SELECT id_nivel_puesto FROM anuncio_web aw2 WHERE ".
            $db->quoteInto('aw2.id = ?', $idAnuncioWeb)." )
            AND ".$db->quoteInto('aw.id != ?', $idAnuncioWeb)."
            AND aw.online = 1 LIMIT ".
            $db->quoteInto('?', $limit);
        $stmp  = $db->query($sql);
        $valor = $stmp->fetchAll();
        if ($valor === false) {
            return null;
        }
        $this->_cache->save($valor, $cacheId, array(), $cacheEt);

        return $valor;
    }

    public function getAvisosRelacionadosAuxiliar($idAnuncioWeb, $limit)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__.'_'.$idAnuncioWeb;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }

        $prueWhere = $this->getAdapter()
            ->select()
            ->from($this->_name,
                array('id', 'id_area', 'id_nivel_puesto', 'puesto'))
            ->where('id = ? ', $idAnuncioWeb);

        $db     = $this->getAdapter();
        $sql    = $db->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('aw.empresa_rs', 'aw.puesto', 'u.display_name', 'url_id', 'slug',
                'aw.id')
            )
            ->join(array('u' => 'ubigeo'), 'u.id = aw.id_ubigeo')
            ->join(
                array('aw2' => $prueWhere),
                'aw2.id_area=aw.id_area AND aw2.id_nivel_puesto = aw.id_nivel_puesto AND aw2.id !='.
                $idAnuncioWeb
            )
            ->where('aw.online = 1')
            ->where('aw.id != ?', $idAnuncioWeb)
            ->limit($limit);
        $result = $this->getAdapter()->fetchAll($sql);

        if ($result === false) {
            return null;
        }
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getAvisosPortada($idpostulante, $areas, $nivel,
                                     $remuneracion, $fechapub, $empresa,
                                     $ubicacion, $urlid, $query, $awIds)
    {
        $db            = $this->getAdapter();
        if ($idpostulante == null) $idpostulante  = 0;
        $sql           = $db->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('display_name' => 'u.display_name',
                'awid' => 'aw.id',
                'puesto' => 'aw.puesto',
                'prioridad' => 'aw.prioridad',
                'slug_aviso' => 'aw.slug',
                'fecha_publicacion' => 'aw.fh_pub',
                'tipo_aviso' => 'aw.tipo',
                'dias_fp' => 'DATEDIFF(CURDATE(),aw.fh_pub)',
                'funciones' => 'aw.funciones',
                'responsabilidades' => 'aw.responsabilidades',
                'aw.mostrar_empresa',
                'logoanuncio' => new Zend_Db_Expr(
                    "CASE e.es_persona_natural
                            WHEN 1 THEN NULL
                            WHEN 0 THEN aw.logo END"
                ),
                'empresa_rs' => new Zend_Db_Expr(
                    "CASE e.es_persona_natural
                            WHEN 1 THEN 'Importante Empresa'
                            WHEN 0 THEN aw.empresa_rs END"
                ),
                'id_anuncio_web' => 'aw.url_id',
                'id_producto' => 'aw.id_producto')
            )
            ->join(
                array('e' => 'empresa'), 'aw.id_empresa = e.id'
            )
            ->joinleft(
                array('np' => 'nivel_puesto'), 'aw.id_nivel_puesto = np.id'
            )
            ->joinleft(
                array('a' => 'area'), 'aw.id_area = a.id'
            )
            ->joinleft(array('u' => 'ubigeo'), 'u.id= aw.id_ubigeo')
            ->joinleft(
                array('p' => 'postulacion'),
                'p.id_anuncio_web = aw.id and
                 p.id_postulante = '.$idpostulante,
                array('idpostulante' => 'p.id_postulante')
            )
            ->where('aw.online = 1 ')
            ->order('fh_pub DESC')
            ->order('prioridad ASC');
        //->limit('20');
        $aAreas        = explode("--", $areas);
        $aNivel        = explode("--", $nivel);
        $aRemuneracion = explode("--", $remuneracion);
        $aFechapub     = explode("--", $fechapub);
        $aEmpresa      = explode("--", $empresa);
        $aUbicacion    = explode("--", $ubicacion);
        foreach ($aUbicacion as $au) {
            if ($au == 'callao-callao-lima') {
                $ubigeo          = new Application_Model_Ubigeo();
                $distritosCallao = $ubigeo->getDistritosCallaoByBusqueda();
                $i               = 0;
                foreach ($distritosCallao as $dc) {
                    $dc['search_name'] = str_replace(' ', '-',
                        $dc['search_name']);
                    $aUbicacion[]      = $dc['search_name'];
                    //$aUbicacion[$i] = $dc['search_name'];
                    $i++;
                }
            }
        }

        //FILTRO PARA EL QUERY
        if ($this->usarLucene) { // usando Lucene
            if ($awIds !== "") {
                if (is_array($awIds) && count($awIds)) {
                    $sql = $sql->where(
                        $this->getAdapter()->quoteInto("aw.id IN (?)", $awIds)
                    );
                } else {
                    $sql = $sql->where("aw.id IN (0)");
                }
            }
        } else { // sin usar Lucene
            if ($query != "") {
                $queryLow = strtolower($query);
                $sql      = $sql->where(
                    $this->getAdapter()->quoteInto(
                        "LOWER(CONCAT(aw.puesto,' ',aw.funciones)) like ?",
                        "%".$queryLow."%"
                    )
                );
            }
        }
        //FILTRO DE AREAS
        if ($aAreas[0] != "") {
            $whereAreas = "";
            foreach ($aAreas as $key => $i) {
                $whereAreas .= $db->quoteInto('a.slug = ?', $i);
                if (count($aAreas) > 1 && $key < count($aAreas) - 1) {
                    $whereAreas.=" OR ";
                }
            }
            $sql = $sql->where($whereAreas);
        }
        //FILTRO DE NIVEL
        if ($aNivel[0] != "") {
            $whereNivel = "";
            foreach ($aNivel as $key => $i) {
                $whereNivel .= $db->quoteInto('np.slug = ?', $i);
                if (count($aNivel) > 1 && $key < count($aNivel) - 1) {
                    $whereNivel.=" OR ";
                }
            }
            $sql = $sql->where($whereNivel);
        }
        //FILTRO DE REMUNERACIONES
        if ($aRemuneracion[0] != "") {
            $whereRemuneracion = "";
            foreach ($aRemuneracion as $key => $i) {
                $x = explode("-", $i);
                if (count($x) == 1 && $x[0] == "0") {
                    $whereRemuneracion .="(".$db->quoteInto(
                            'aw.salario_min IS NULL', "a"
                        )." AND ";
                    $whereRemuneracion .=$db->quoteInto(
                            'aw.salario_max IS NULL', "a"
                        ).")";
                } else if ($x[1] == 'más') {
                    $whereRemuneracion .="(".$db->quoteInto(
                            'aw.salario_min >= ?', $x[0]
                        ).")";
                } else {
                    $whereRemuneracion .="(".$db->quoteInto(
                            'aw.salario_min >= ?', $x[0]
                    );
                    $whereRemuneracion .="  AND ".$db->quoteInto(
                            'aw.salario_min < ?', $x[1]
                        ).")";
                    $whereRemuneracion.=" OR ";
                    $whereRemuneracion .="(".$db->quoteInto(
                            'aw.salario_max > ?', $x[0]
                    );
                    $whereRemuneracion .="  AND ".$db->quoteInto(
                            'aw.salario_max <= ?', $x[1]
                        ).")";
                }
                if (count($aRemuneracion) > 1 && $key < count($aRemuneracion) - 1) {
                    $whereRemuneracion.=" OR ";
                }
            }
            $sql = $sql->where($whereRemuneracion);
        }
        //FILTRO DE FECHA PUBLICACION
        if ($aFechapub[0] != "") {
            $whereFechapublicacion = "";
            foreach ($aFechapub as $key => $i) {
                $i    = str_replace("-", "_", $i);
                $i    = $slug = str_replace('í', 'i', str_replace('Ú', 'u', $i));
                //echo $i;
                $dias = $this->_config->busqueda->filtros->diasPublicacionDias->$i;

                $whereFechapublicacion .= $db->quoteInto(
                    'fh_pub BETWEEN DATE_SUB(FROM_UNIXTIME(UNIX_TIMESTAMP()),
                     INTERVAL ? DAY) AND FROM_UNIXTIME(UNIX_TIMESTAMP())',
                    $dias
                );
                if (count($aFechapub) > 1 && $key < count($aFechapub) - 1) {
                    $whereFechapublicacion.=" OR ";
                }
            }
            $sql = $sql->where($whereFechapublicacion);
        }
        //FILTRO EMPRESA
        if ($aEmpresa[0] != "") {
            $whereEmpresa = "";
            foreach ($aEmpresa as $key => $i) {
                $whereEmpresa .= $this->getAdapter()->quoteInto('e.slug = ?', $i);
                if (count($aEmpresa) > 1 && $key < count($aEmpresa) - 1) {
                    $whereEmpresa.=" OR ";
                }
            }
            $sql = $sql->where($whereEmpresa);
        }

        //FILTRO UBICACION
        if ($aUbicacion[0] != "") {
            $whereUbicacion = "";
            foreach ($aUbicacion as $key => $i) {
                $whereUbicacion .= $this->getAdapter()->quoteInto(
                    'REPLACE(u.search_name," ","-") = ?', $i
                );
                if (count($aUbicacion) > 1 && $key < count($aUbicacion) - 1) {
                    $whereUbicacion.=" OR ";
                }
            }
            $sql = $sql->where($whereUbicacion);
        }

        //FILTRO DE URL_ID (CODIGO DE ADECSYS)
        if ($urlid !== "") {
            $sql = $sql->joinleft(
                    array('cac' => 'compra_adecsys_codigo'),
                    'aw.id_compra = cac.id_compra', array('adecsys_code')
                )
                ->where('cac.adecsys_code = ?', $urlid);
        }

        //echo "<!-- sql busqueda: ".$sql->assemble()."-->";
        //echo $sql->assemble();
        //exit;

        $rs = $this->getAdapter()->fetchAll($sql);
        foreach ($rs as $key => $value) {
            if ($rs[$key]['mostrar_empresa'] != 1) {
                $rs[$key]['logoanuncio'] = '';
            }
        }
        return $rs;
    }

    public function getRellenarIndexPostulaciones($inicio, $n)
    {
        $adapter = $this->getAdapter();
        $sql     = "SELECT  aw.id idanuncioweb,
                    ps.id AS idpostulante,
                    p.id AS idpostulacion,
                    ps.path_foto AS foto,
                    ps.nombres,
                    ps.apellidos,
                    ps.telefono,
                    ps.celular,
                    ps.slug,
                    p.msg_por_responder,
                    ps.sexo,
                    FLOOR(DATEDIFF(CURDATE(),ps.fecha_nac)/365) AS edad,
                    ROUND(p.match,0) AS 'match',
                    p.nivel_estudio,
                    p.carrera as carrerap,
                    ps.path_cv,
                    p.msg_no_leidos,
                    p.msg_respondido,
                    p.es_nuevo,
                    p.invitacion,
                    p.referenciado,
                    p.id_categoria_postulacion,
                    p.descartado,
                    p.es_nuevo,
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
                         IF(e.fin_ano,e.fin_ano*12+e.fin_mes,YEAR(CURDATE())*12+MONTH(CURDATE()))-
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
                      FLOOR(DATEDIFF(CURDATE(),ps.fecha_nac)/365) AS edad,
                      ps.sexo AS sexo_claves,
                      IF(ps.sexo='M','Masculino','Femenino') AS sexo,
                      ps.id_ubigeo AS ubigeo_claves,
                      (SELECT u.nombre FROM ubigeo u
                       WHERE u.id=ps.id_ubigeo) AS ubigeo,
                       aw.online as online

             FROM anuncio_web AS aw
             INNER JOIN postulacion AS p ON aw.id = p.id_anuncio_web
             INNER JOIN empresa AS e ON aw.id_empresa = e.id
             INNER JOIN postulante AS ps ON p.id_postulante = ps.id
             INNER JOIN usuario AS u ON ps.id_usuario = u.id
             WHERE (aw.eliminado = 0)
             LIMIT ".$inicio.",".$n;

        $stm = $adapter->query($sql);
        return $stm->fetchAll();
    }

    public function existePostulacion($idPostulante, $idAnuncio)
    {
        $adapter = $this->getAdapter();
        $sql     = $adapter->select()->from('postulacion', 'id_anuncio_web')
            ->where('id_postulante = ?', $idPostulante)
            ->where('id_anuncio_web = ?', $idAnuncio)
            ->where('activo = ?',
            Application_Model_Postulacion::POSTULACION_ACTIVA);

        $stm  = $adapter->query($sql);
        $data = $stm->fetchColumn();
        if ($data != null && count($data) >= 1) {
            return true;
        }
        return false;
    }

    public function getRellenarIndexPostulacionesxAnuncio($inicio, $n,
                                                          $idAnuncio)
    {
        $adapter = $this->getAdapter();
        $sql     = "SELECT  aw.id idanuncioweb,
                    ps.id AS idpostulante,
                    p.id AS idpostulacion,
                    ps.path_foto AS foto,
                    ps.nombres,
                    ps.apellidos,
                    ps.telefono,
                    ps.celular,
                    ps.slug,
                    p.msg_por_responder,
                    ps.sexo,
                    FLOOR(DATEDIFF(CURDATE(),ps.fecha_nac)/365) AS edad,
                    ROUND(p.match,0) AS 'match',
                    p.nivel_estudio,
                    p.carrera as carrerap,
                    ps.path_cv,
                    p.msg_no_leidos,
                    p.msg_respondido,
                    p.es_nuevo,
                    p.invitacion,
                    p.referenciado,
                    p.id_categoria_postulacion,
                    p.descartado,
                    p.es_nuevo,
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
                         IF(e.fin_ano,e.fin_ano*12+e.fin_mes,YEAR(CURDATE())*12+MONTH(CURDATE()))-
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
                      FLOOR(DATEDIFF(CURDATE(),ps.fecha_nac)/365) AS edad,
                      ps.sexo AS sexo_claves,
                      IF(ps.sexo='M','Masculino','Femenino') AS sexo,
                      ps.id_ubigeo AS ubigeo_claves,
                      (SELECT u.nombre FROM ubigeo u
                       WHERE u.id=ps.id_ubigeo) AS ubigeo,
                       aw.online as online
             FROM anuncio_web AS aw
             INNER JOIN postulacion AS p ON aw.id = p.id_anuncio_web
             INNER JOIN empresa AS e ON aw.id_empresa = e.id
             INNER JOIN postulante AS ps ON p.id_postulante = ps.id
             INNER JOIN usuario AS u ON ps.id_usuario = u.id
             WHERE (aw.eliminado = 0) and aw.id = ".$idAnuncio;
        $stm     = $adapter->query($sql);
        return $stm->fetchAll();
    }

    public function getRellenarIndexUsuarios($inicio, $n)
    {
        $adapter = $this->getAdapter();
        $sql     = "SELECT ps.id AS idpostulante,
                        ps.path_foto AS foto,
                        ps.nombres,
                        ps.apellidos,
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
                          FLOOR(DATEDIFF(CURDATE(),ps.fecha_nac)/365) AS edad,
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
                           WHERE e.id_postulante=ps.id ) 'area'
               FROM postulante AS ps
               INNER JOIN usuario AS u ON ps.`id_usuario`=u.id
               WHERE u.`activo`=1
               LIMIT ".$inicio.",".$n;

        $stm = $adapter->query($sql);
        return $stm->fetchAll();
    }

    public function getCountPostulaciones()
    {
        $adapter = $this->getAdapter();
        $sql     = "SELECT COUNT(id) AS n FROM postulacion";
        $stm     = $adapter->query($sql);
        return $stm->fetchAll();
    }

    public function getCountPostulantes()
    {
        $adapter = $this->getAdapter();
        $sql     = "SELECT COUNT(id) AS n FROM postulante";
        $stm     = $adapter->query($sql);
        return $stm->fetchAll();
    }

    public function getCountAnuncioWeb()
    {
        $adapter = $this->getAdapter();
        $sql     = "SELECT COUNT(id) AS n FROM anuncio_web";
        $stm     = $adapter->query($sql);
        return $stm->fetchAll();
    }

    public function getGroupNivelPuestoAnuncio()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql    = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('slug' => 'np.slug', 'cant' => 'count(aw.id)')
            )
            ->join(
                array('np' => 'nivel_puesto'), 'aw.id_nivel_puesto = np.id',
                array('nombre as msg')
            )
            ->where('aw.online = 1')
            ->group('aw.id_nivel_puesto')
            ->order('cant DESC');
        $result = $this->getAdapter()->fetchAssoc($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getGroupEmpresaAnuncio()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql    = $this->getAdapter()->select()
            ->from(
                array('empresa' => 'empresa'),
                array(
                'ind' => 'id',
                'cant' => 'contador_anuncios',
                'slug' => 'slug',
                'msg' => 'razon_social',
                )
            )
            ->where('contador_anuncios > 0')
            ->where("slug != ''")
            ->order('msg ASC');
        $result = $this->getAdapter()->fetchAssoc($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
//        echo $sql->assemble($sql);         exit;
        return $result;
    }

    public function getGroupDistritosAnuncios()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql    = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('slug' => 'REPLACE(dist.search_name," ","-")',
                'cant' => 'count(aw.id)',
                'msg' => 'dist.nombre')
            )
            ->join(
                array('dist' => 'ubigeo'),
                'aw.id_ubigeo = dist.id AND dist.level = 3', array()
            )
            ->join(array('prov' => 'ubigeo'), 'dist.padre = prov.id', array())
            ->where('prov.padre = 3926')
            ->where('aw.online = 1')
            ->group('aw.id_ubigeo')
            ->order('cant DESC');
        $result = $this->getAdapter()->fetchAssoc($sql);
        //echo $sql->assemble($sql);         exit;
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function queryFilters($areas, $nivel, $fechapub, $remuneracion,
                                 $ubicacion, $urlid)
    {
        $aAreas        = explode("--", $areas);
        $aNivel        = explode("--", $nivel);
        $aRemuneracion = explode("--", $remuneracion);
        $aFechapub     = explode("--", $fechapub);
        $aUbicacion    = explode("--", $ubicacion);

        $filters = "";

        if ($urlid != "")
                $filters = $filters."(urlid:".$urlid." OR codigoadecsys:".$urlid.") AND ";
        if ($aAreas[0] != "")
                $filters = $filters.$this->getQueryFilter("areaslug", $aAreas)." AND ";
        if ($aNivel[0] != "")
                $filters = $filters.$this->getQueryFilter("nivelslug", $aNivel)." AND ";
        if ($aUbicacion[0] != "")
                $filters = $filters.$this->getQueryFilter("ubicacionsearch",
                    $aUbicacion)." AND ";

        if ($aRemuneracion[0] != "")
                $filters = $filters.$this->getQueryFilterMaxMin("sueldomax",
                    "sueldomin", $aRemuneracion)." AND ";

        if ($aFechapub[0] != "")
                $filters = $filters.$this->getQueryFilterFecPub("valorfecha",
                    $aFechapub)." AND ";

        if ($filters != "")
                $filters = substr($filters, 0, strlen($filters) - 5);

        return $filters;
    }

    public function getQueryFilterFecPub($filter, $aData)
    {
        $qFilter = "";
        if ($aData[0] != "") {
            $diasMay = -1;
            foreach ($aData as $key => $i) {
                $i = str_replace("-", "_", $i);
                $i = str_replace('í', 'i', str_replace('Ú', 'u', $i));

                $dias = $this->_config->busqueda->filtros->diasPublicacionDias->$i;

                if ($dias > $diasMay) $diasMay = $dias;
            }
            $fecNow  = new Zend_Date();
            $fecLast = clone $fecNow;
            $fecLast->addDay(-1 * $diasMay);

            $qFilter = $qFilter.$filter.':['.$fecLast->toString('YYYYMMdd').' TO '.$fecNow->toString('YYYYMMdd').']';
        }

        return $qFilter;
    }

    public function getQueryFilterMaxMin($maxField, $minField, $aData)
    {
        $qFilterMax = "";
        $qFilterMin = "";
        $qFilter    = "";
        if ($aData[0] != "") {
            $qFilterMax = "(";
            $qFilterMin = "(";
            foreach ($aData as $dataFilter) {
                $range = array();
                if (trim($dataFilter) == "0") {
                    $range[0] = "0";
                    $range[1] = "0";
                } else {
                    $range = explode("-", $dataFilter);
                }

                $qFilterMin = $qFilterMin.$minField.':'.$range[0].' OR ';
                $qFilterMax = $qFilterMax.$maxField.':'.$range[1].' OR ';
            }
            $qFilterMin = substr($qFilterMin, 0, strlen($qFilterMin) - 4).")";
            $qFilterMax = substr($qFilterMax, 0, strlen($qFilterMax) - 4).")";

            $qFilter = $qFilterMin." AND ".$qFilterMax;
        }

        return $qFilter;
    }

    public function getQueryFilterRange($field, $aData)
    {
        $zl      = new ZendLucene();
        $qFilter = "";
        if ($aData[0] != "") {
            $qFilter = "(";
            foreach ($aData as $dataFilter) {
                $arr  = explode("-", $dataFilter);
                $nUno = $zl->fillZeroField($arr[0]);
                $nDos = $zl->fillZeroField($arr[1]);
                $qFilter.=$field.":[".$nUno." TO ".$nDos."] OR ";
            }
            $qFilter = substr($qFilter, 0, strlen($qFilter) - 4).")";
        }

        return $qFilter;
    }

    public function getQueryFilter($filter, $aData, $condicion = "OR",
                                   $modoZero = false, $valueToZero = "1")
    {
        $qFilter           = "";
        $agregarFiltroZero = false;
        if ($aData[0] != "") {
            $qFilter = "(";
            foreach ($aData as $dataFilter) {

                $words   = explode("-", $dataFilter);
                $qFilter = $qFilter."(";
                foreach ($words as $word) {
                    if ($modoZero && trim($word) == $valueToZero)
                            $agregarFiltroZero = true;
                    $qFilter           = $qFilter.$filter.':"'.$word.'" AND ';
                }
                $qFilter = substr($qFilter, 0, strlen($qFilter) - 5).") $condicion ";
            }
            if ($agregarFiltroZero)
                    $qFilter = $qFilter."(".$filter.':"0") '.$condicion.' ';
            $qFilter = substr($qFilter, 0,
                    strlen($qFilter) - (strlen($condicion) + 2)).")";
        }
        return $qFilter;
    }

    public function getPaginator(
    $id, $areas, $nivel, $remuneracion, $fechapub, $empresa, $ubicacion, $urlid,
    $query, $awIds, $luQuery = ""
    )
    {
        $paginadoavisos = $this->_config->avisosportada->ultimosavisos->paginadoavisos;

        if ($this->_config->confpaginas->javalucene == 1) {
            $adapter = new App_Paginator_Adapter_LuceneAvisos(
                $id, $luQuery, $query, // todo query
                $this->queryFilters($areas, $nivel, $fechapub, $remuneracion,
                    $ubicacion, $urlid)
            );
            $p       = new Zend_Paginator($adapter);
            return $p->setItemCountPerPage($paginadoavisos);
        } else {
            $p = Zend_Paginator::factory(
                    $this->ordenarAvisosBusqueda(
                        $this->getAvisosPortada(
                            $id, $areas, $nivel, $remuneracion, $fechapub,
                            $empresa, $ubicacion, $urlid, $query, $awIds
                        ), $query
                    )
            );
        }
        return $p->setItemCountPerPage($paginadoavisos);
    }

    public function ordenarAvisosBusqueda($avisos, $query)
    {
        $luceneCast = new App_View_Helper_LuceneCast();
        //$luceneCast->LuceneCast($aviso['awid']);

        $avisosConScore = array();
        $palabras       = explode(" ", $query);

        foreach ($avisos as $aviso) {
            $puesto                = $luceneCast->LuceneCast($aviso['puesto']);
            $empresa               = $luceneCast->LuceneCast($aviso['empresa_rs']);
            $aviso["scorePuesto"]  = 0;
            $aviso["scoreEmpresa"] = 0;
            $aviso["igualPuesto"]  = false;
            $aviso["igualEmpresa"] = false;
            //$aviso['empresa_rs'] = $aviso["fecha_publicacion"]." | ".$aviso["prioridad"]." //".$aviso['empresa_rs'];

            if (mb_strrpos($puesto, $query) !== false) {
                $aviso["igualPuesto"] = true;
            }
            if (mb_strrpos($empresa, $query) !== false) {
                $aviso["igualEmpresa"] = true;
            }

            foreach ($palabras as $palabra) {
                if (mb_strrpos($puesto, $palabra) !== false) {
                    $aviso["scorePuesto"]+=1;
                }
                if (mb_strrpos($empresa, $palabra) !== false) {
                    $aviso["scoreEmpresa"]+=1;
                }
            }

            $avisosConScore[] = $aviso;
        }

        $avisosOrdenados = $this->ordernarAvisosPorTipoSegunDia($avisosConScore);

        $avisosPuesto  = array();
        $avisosEmpresa = array();
        $avisos        = array();

        foreach ($avisosOrdenados as $aviso) {
            if ($aviso["igualPuesto"]) {
                $avisosPuesto[] = $aviso;
            } else if ($aviso["igualEmpresa"]) {
                $avisosEmpresa[] = $aviso;
            } else {
                $avisos[] = $aviso;
            }
        }

        $avisosReturn = array_merge($avisosPuesto, $avisosEmpresa, $avisos);
        //var_dump($avisosReturn);

        return $avisosReturn;
    }

    public function ordernarAvisosPorTipoSegunDia($avisosOrdenadosPorDia)
    {
        $returnArray  = array();
        $fecha        = "";
        $arrayOrdenar = array();

        for ($i = 0; $i < count($avisosOrdenadosPorDia); $i++) {
            if ($fecha != substr($avisosOrdenadosPorDia[$i]["fecha_publicacion"],
                    0, 10)) {
                foreach ($this->ordernarAvisosPorPrioridad($arrayOrdenar) as $agregar) {
                    $returnArray[] = $agregar;
                }

                $fecha        = substr($avisosOrdenadosPorDia[$i]["fecha_publicacion"],
                    0, 10);
                $arrayOrdenar = array();
            }
            $arrayOrdenar[] = $avisosOrdenadosPorDia[$i];
        }

        foreach ($this->ordernarAvisosPorPrioridad($arrayOrdenar) as $agregar) {
            $returnArray[] = $agregar;
        }

        //var_dump($returnArray);
        return $returnArray;
    }

    public function ordernarAvisosPorPrioridad($avisos)
    {
        $returnArray     = array();
        $prioridad       = "";
        $arrayOrdenar    = array();
        $gruposPrioridad = array();

        for ($i = 0; $i < count($avisos); $i++) {
            $key = !empty($avisos[$i]["prioridad"]) ? $avisos[$i]["prioridad"] : '0';
            if (!isset($gruposPrioridad[$key])) {
                //echo $avisos[$i]["prioridad"]." / ";
                $gruposPrioridad[$key] = array();
            }
            $gruposPrioridad[$key][] = $avisos[$i];
        }

        $key   = 0;
        $count = 0;

        while ($count < count($gruposPrioridad)) {
            if (isset($gruposPrioridad[$key])) {
                //echo $count." - ".$key." / ";

                foreach ($this->ordenarAvisosPorTipo($gruposPrioridad[$key]) as $agregar) {
                    $returnArray[] = $agregar;
                }
                $count++;
            }
            $key++;
        }

        //echo "<br>";
        //var_dump($returnArray);
        return $returnArray;
    }

    public function ordenarAvisosPorTipo($avisos)
    {
        $returnArray = array();
        $fecha       = "";
        for ($i = 0; $i < count($this->_ordenTipoAviso); $i++) {
            for ($j = 0; $j < count($avisos); $j++) {
                $idProd = !empty($avisos[$j]["id_producto"]) ? $avisos[$j]["id_producto"]
                        : '';
                if ($idProd == $this->_ordenTipoAviso[$i]) {
                    $returnArray[] = $avisos[$j];
                }
            }
        }
        return $this->ordenarPorRelevancia($returnArray);
    }
    /*
      public function ordenarPorRelevancia($avisos)
      {
      $sessionlucene = new Zend_Session_Namespace('lucene');
      if (is_null($sessionlucene->scores)) {
      return $avisos;
      }
      $scores = $sessionlucene->scores;

      $avisosPorId = array();
      foreach ($avisos as $aviso) {
      $avisosPorId[$aviso['awid']] = $aviso;
      }
      $temprelev = array();
      foreach ($avisos as $aviso) {
      if (!array_key_exists($aviso['awid'], $scores)) {
      return $avisos;
      }
      $temprelev[$aviso['awid']] = $scores[$aviso['awid']];
      }
      arsort($temprelev);
      $avisosResult = array();
      foreach ($temprelev as $awid => $relev) {
      $avisosPorId[$awid]['luc_rel'] = $relev;
      $avisosResult[] = $avisosPorId[$awid];
      }
      return $avisosResult;
      }
     */

    public function ordenarPorRelevancia($avisosScore)
    {
        $luceneCast = new App_View_Helper_LuceneCast();
        //$luceneCast->LuceneCast($aviso['awid']);
        //var_dump($avisosScore);

        $avisosReturn = array();
        $maxEstado    = 2;
        $estado       = 0;
        $cantMax      = count($avisosScore);
        while ($avisosScore != null && count($avisosScore) > 0) {
            $pMay   = 0;
            $valMay = -1;

            switch ($estado) {
                case 0:
                    for ($i = 0; $i < $cantMax; $i++) {
                        if (isset($avisosScore[$i]) && $valMay < $avisosScore[$i]["scorePuesto"]) {
                            $pMay   = $i;
                            $valMay = $avisosScore[$pMay]["scorePuesto"];
                        }
                    }
                    break;
                case 1:
                    for ($i = 0; $i < $cantMax; $i++) {
                        if (isset($avisosScore[$i]) && $valMay < $avisosScore[$i]["scoreEmpresa"]) {
                            $pMay   = $i;
                            $valMay = $avisosScore[$pMay]["scoreEmpresa"];
                        }
                    }
                    break;
            }



            if ($valMay == 0) {
                $estado++;
                if ($estado >= $maxEstado) {
                    //$avisosReturn = array_merge($avisosReturn, $avisosScore);
                    for ($i = 0; $i < $cantMax; $i++) {
                        if (isset($avisosScore[$i])) {
                            $avisosReturn[] = $avisosScore[$i];
                        }
                    }
                    $avisosScore = null;
                }
            } else if ($valMay == -1) {
                $avisosScore = null;
            } else {
                $avisosReturn[] = $avisosScore[$pMay];
                unset($avisosScore[$pMay]);
            }
            /*
              echo "/----------------------------------/";
              var_dump($avisosScore);
              var_dump($avisosReturn);
              echo "/----------------------------------/";
             */
        }

        //var_dump($avisosReturn);


        return $avisosReturn;
    }

    public function getAvisosActivos($idEmpresa, $online, $estado, $eliminado,
                                     $col, $ord)
    {
        $col = $col == '' ? 'aw.fh_pub' : $col;
        $ord = $ord == '' ? 'DESC' : $ord;

        $sql = $this->getAdapter()->select()
            ->from(
                array('aw' => $this->_name),
                array('aviso' => 'aw.puesto', 'npostulante' => 'aw.nnuevos',
                'aw.fh_pub', 'fecha_fin' => 'aw.fh_vencimiento',
                'fecha_proceso' => 'aw.fh_vencimiento_proceso',
                'aw.id',
                'fecha_baja' => 'aw.fh_aviso_baja',
                'fecha_baja' => new Zend_Db_Expr("COALESCE(aw.fh_aviso_baja,aw.fh_vencimiento)"),
                'slug' => 'aw.slug',
                'id_anuncio_web' => 'aw.url_id',
                'aw.estado'
                )
            )
            ->joinLeft(
                array('c' => 'compra'), 'aw.id_compra = c.id',
                array('fecha_compra' => 'aw.fh_pub', 'precio' => 'c.precio_total')
            )
            ->joinInner(
                array('p' => 'producto'), 'aw.id_producto = p.id',
                array('desc_tipo' => 'p.nombre')
            )
            ->joinLeft(
                array('ai' => 'anuncio_impreso'),
                'aw.id_anuncio_impreso = ai.id',
                array('fecha_impreso' => 'ai.fh_pub_confirmada')
            )
            ->where('aw.online = ?', $online)
            ->where('aw.borrador = 0')
            ->where('aw.estado = ?', $estado)
            ->where('aw.id_empresa = ? ', $idEmpresa)
            ->where('aw.eliminado = ?', $eliminado)
            ->order(sprintf('%s %s', $col, $ord));

        //return $this->getAdapter()->fetchAll($sql);
        return $sql;
    }

    public function getAvisosActivosPorAdministrador($select, $usuarioEmpresaId)
    {
        $select->joinInner(array('aue' => 'anuncio_usuario_empresa'),
                'aue.id_anuncio = aw.id', array())
            ->where('aue.id_usuario_empresa =?', $usuarioEmpresaId);

        return $select;
    }

    public function getPaginatorAvisosActivos($idEmpresa, $online, $estado,
                                              $eliminado, $col, $ord
    )
    {

        $paginado = $this->_config->empresa->misavisos->paginadoavisos;
        $p        = Zend_Paginator::factory(
                $this->getAvisosActivos($idEmpresa, $online, $estado,
                    $eliminado, $col, $ord)
        );
        return $p->setItemCountPerPage($paginado);
    }

    public function obtenerMisProcesosCerrados($empresa_id, $col = '', $ord = '')
    {
        $col = $col == '' ? 'aw.fh_pub' : $col;
        $ord = $ord == '' ? 'DESC' : $ord;

        $db = $this->getAdapter();

        $totalReferidos = $db->select()
            ->from(array('referenciado'),
                array('total_referidos' => 'COUNT(id)',
                'id_anuncio_web'))
            ->group('id_anuncio_web');

        $subselect = $db->select()
            ->from(array('aww' => 'anuncio_web'), array('aww.extiende_a'))
            ->where('aww.id <> aww.extiende_a')
            ->where('aww.id_empresa =?', $empresa_id);

        $dataSubSelect = $this->getAdapter()->fetchAll($subselect);

        $select = $db->select()
            ->from(array('aw' => 'anuncio_web'),
                array(
                new Zend_Db_Expr("DATE_FORMAT(aw.fh_pub,'%d/%m/%Y') AS fcreacion"),
                new Zend_Db_Expr("DATE_FORMAT(aw.fh_vencimiento,'%d/%m/%Y') AS f_publicacion"),
                'aw.id',
                'f_vencimiento' => 'aw.fh_vencimiento_proceso',
                new Zend_Db_Expr(' DATE(aw.fh_aviso_baja) AS f_baja'),
                new Zend_Db_Expr("DATE_FORMAT(aw.fh_pub,'%d/%m/%Y') AS fpublicacion"),
                new Zend_Db_Expr('DATE_FORMAT(aw.fh_vencimiento_proceso,"%d/%m/%Y") AS fvencimiento'),
                'aw.puesto', 'aw.fh_pub', 'aw.fh_vencimiento',
                'aw.fh_vencimiento_proceso', 'nnoleidos' => 'aw.nnuevos',
                'aw.ninvitaciones', 'aw.nmsjrespondidos', 'aw.slug',
                'aw.url_id', 'aw.extiende_a', 'aw.fh_pub',
                'aw.fh_vencimiento', 'aw.fh_vencimiento_proceso',
                'npostulantes' => 'aw.ntotal', 'aw.nnuevos',
                'aw.tipo', 'aw.id_producto'))
            ->joinLeft(array('r' => $totalReferidos),
                'aw.id = r.id_anuncio_web', array('r.total_referidos'))
            ->joinLeft(array('u' => 'usuario'), 'u.id = aw.creado_por',
                array('u.email'))
            ->where('aw.id_empresa =?', $empresa_id)
            ->where('aw.cerrado=?', self::CERRADO)
            ->order("$col $ord");

        if (!empty($dataSubSelect)) {
            $select->where('aw.id NOT IN (?)', $dataSubSelect);
        }

        return $select;
    }

    public function getMisProcesosActivos($empresa_id, $col = '', $ord = '')
    {
        $col = $col == '' ? 'aw.fh_pub' : $col;
        $ord = $ord == '' ? 'DESC' : $ord;


        /*
          $sql = "SELECT
          IF(aw.extiende_a = aw.id, 'original', 'extend') 'tipo_ex',
          COALESCE((SELECT distinct(1)
          FROM anuncio_web AS aww
          WHERE aw.id = aww.extiende_a
          AND aw.id != aww.id
          AND aww.id_empresa = " . $id . "), 0) 'extendido',
          aw.id AS `id`, `aw`.`puesto`,
          DATE_FORMAT(aw.fh_pub,'%d/%m/%Y') AS `fcreacion`,
          aw.fh_vencimiento AS `f_publicacion`, aw.fh_vencimiento_proceso AS `f_vencimiento`,
          DATE(aw.fh_aviso_baja) AS `f_baja`,
          DATE_FORMAT(aw.fh_vencimiento,'%d/%m/%Y') AS `fpublicacion`,
          DATE_FORMAT(aw.fh_vencimiento_proceso,'%d/%m/%Y') AS `fvencimiento`,
          (select count(*) as ntotal from postulacion as p where p.id_anuncio_web=aw.id and p.descartado = 0 and p.activo = 1) AS `npostulantes`,
          `aw`.`nnuevos` AS `nnoleidos`, `aw`.`ninvitaciones`,
          `aw`.`nmsjrespondidos`, `aw`.`slug`, `aw`.`url_id`, `aw`.`extiende_a` , `aw`.`fh_pub`,
          `aw`.`fh_vencimiento`, `aw`.`fh_vencimiento_proceso`,
          `aw`.`ntotal`,
          `aw`.`nnuevos`, `aw`.`tipo`, `aw`.`id_producto`
          FROM anuncio_web aw
          WHERE aw.id_empresa = " . $id . " AND aw.fh_vencimiento_proceso > CURDATE()
          ORDER BY " . sprintf('%s %s',
          $col, $ord); */

        $db = $this->getAdapter();

        $totalReferidos = $db->select()
            ->from(array('referenciado'),
                array('total_referidos' => 'COUNT(id)',
                'id_anuncio_web'))
            ->group('id_anuncio_web');

        $select = $db->select()
            ->from(array('aw' => 'anuncio_web'),
                array(
                new Zend_Db_Expr("DATE_FORMAT(aw.fh_pub,'%d/%m/%Y') AS fcreacion"),
                'f_publicacion' => 'aw.fh_vencimiento', 'aw.id',
                'f_vencimiento' => 'aw.fh_vencimiento_proceso',
                new Zend_Db_Expr(' DATE(aw.fh_aviso_baja) AS f_baja'),
                new Zend_Db_Expr("DATE_FORMAT(aw.fh_pub,'%d/%m/%Y') AS fpublicacion"),
                new Zend_Db_Expr('DATE_FORMAT(aw.fh_vencimiento_proceso,"%d/%m/%Y") AS fvencimiento'),
                'aw.puesto', 'aw.fh_pub', 'aw.fh_vencimiento',
                'aw.fh_vencimiento_proceso', 'nnoleidos' => 'aw.nnuevos',
                'aw.ninvitaciones', 'aw.nmsjrespondidos', 'aw.slug',
                'aw.url_id', 'aw.extiende_a', 'aw.fh_pub',
                'aw.fh_vencimiento', 'aw.fh_vencimiento_proceso',
                'npostulantes' => 'aw.ntotal', 'aw.nnuevos', 'aw.tipo',
                'aw.id_producto', 'aw.estado'))
            ->joinLeft(array('r' => $totalReferidos),
                'aw.id = r.id_anuncio_web', array('r.total_referidos'))
            ->joinLeft(array('u' => 'usuario'), 'u.id = aw.creado_por',
                array('u.email'))
            ->where('aw.id_empresa =?', $empresa_id)
            ->where('aw.fh_vencimiento_proceso >= CURDATE()')
            ->where('aw.cerrado =?', self::NO_CERRADO)
            ->where('aw.eliminado =?', self::NO_ELIMINADO)
            ->where('aw.borrador =?', self::NO_BORRADOR)
            ->group('aw.id')
            ->order("$col $ord");

        return $select;

        /* $sql = $this->getAdapter()->select()
          ->from(
          array('aw' => 'anuncio_web'),
          array('id' => 'aw.id',
          'puesto' => 'aw.puesto',
          'fcreacion'=>'DATE_FORMAT(aw.fh_pub,"%d/%m/%Y")' ,
          'fpublicacion'=>'DATE_FORMAT(aw.fh_vencimiento,"%d/%m/%Y")',
          'fvencimiento'=>'DATE_FORMAT(aw.fh_vencimiento_proceso,"%d/%m/%Y")',
          'f_publicacion'=>'aw.fh_vencimiento',
          'f_vencimiento'=>'aw.fh_vencimiento_proceso',
          'f_baja'=>'date(aw.fh_aviso_baja)',
          'npostulantes'=>'aw.ntotal',
          'nnoleidos'=>'aw.nnuevos',
          'ninvitaciones'=>'aw.ninvitaciones',
          'nmsjrespondidos'=>'aw.nmsjrespondidos',
          'slug' => 'aw.slug',
          'url_id' => 'aw.url_id',
          'extiende_a'=>'aw.extiende_a',
          'aw.tipo',
          'aw.id_producto')
          )
          ->where("aw.id_empresa = ?", $id)
          ->where("aw.fh_vencimiento_proceso>CURDATE()")
          ->where("aw.eliminado = 0")
          ->where("aw.borrador = 0")
          ->where("aw.cerrado = 0")
          ->order(sprintf('%s %s', $col, $ord));


          $adapter = $this->getAdapter();
          $stm = $adapter->query($sql);
          return $stm->fetchAll(); */
    }

    public function getPaginatorProcesosActivos($id, $activo, $col = '',
                                                $ord = '')
    {
        $paginado = $this->_config->empresa->misprocesos->paginadoactivos;
        $p        = Zend_Paginator::factory(
                $this->getMisProcesosActivos($id, $activo, $col, $ord)
        );
        return $p->setItemCountPerPage($paginado);
    }

    public function obtenerAvisosPorAdministradorSecundario(
    $empresa_id, $usuario_empresa_id, $col = '', $ord = '')
    {
        $select = $this->getMisProcesosActivos($empresa_id, $col, $ord);

        $select->joinInner(array('aue' => 'anuncio_usuario_empresa'),
                'aue.id_anuncio = aw.id', array())
            ->where('aue.id_usuario_empresa =?', $usuario_empresa_id);

        return $select;
    }

    public function obtenerAvisosCerradosPorAdministradorSecundario(
    $empresa_id, $usuario_empresa_id, $col = '', $ord = '')
    {
        $select = $this->obtenerMisProcesosCerrados(
            $empresa_id, $col, $ord);

        $select->joinInner(array('aue' => 'anuncio_usuario_empresa'),
                'aue.id_anuncio = aw.id', array())
            ->where('aue.id_usuario_empresa =?', $usuario_empresa_id);

        return $select;
    }

    public function cerrarProcesoActivo($id)
    {
        $log    = Zend_Registry::get('log');
        $config = Zend_Registry::get('config');

//        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
//        $buscamasUrl = $config->apis->buscamas->url;
//        $buscamasPublishUrl = $config->apis->buscamas->publishUrl;

        $db = $this->getAdapter();

        try {

            $db->beginTransaction();
            $where                             = $this->getAdapter()->quoteInto('id = ?',
                $id);
            $arreglo["online"]                 = 0;
            $arreglo["cerrado"]                = 1;
            $arreglo["fh_vencimiento_proceso"] = date("Y-m-d");
            $arreglo["fh_vencimiento"]         = date("Y-m-d");
            //$arreglo['estado']= Application_Model_AnuncioWeb::ESTADO_DADO_BAJA;
            $this->update(
                $arreglo, $where
            );
            $updata                            = true;
            try {
                $modsolr = new Solr_SolrAviso();
                $modsolr->addAvisoSolr($id);
            } catch (Solarium\Exception\HttpException $exc) {
                $log->log($exc->getMessage().'. '.$exc->getTraceAsString(),
                    Zend_Log::ERR);
                $updata = false;
            }
            if ($updata) {
                $db->commit();
            } else {
                $db->rollBack();
            }
        } catch (Zend_Db_Exception $e) {
            $db->rollBack();
            $log->log($e->getMessage().'. '.$e->getTraceAsString(),
                Zend_Log::ERR);
            echo 'Vuelva a intentarlo';
        } catch (Zend_Exception $e) {
            $log->log($x->getMessage().'. '.$x->getTraceAsString(),
                Zend_Log::ERR);
            echo 'Vuelva a intentarlo';
        }
    }

    public function eliminarProcesoActivo($id)
    {

        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
        $buscamasUrl         = $config->apis->buscamas->url;
        $buscamasPublishUrl  = $config->apis->buscamas->publishUrl;

        $where                = $this->getAdapter()->quoteInto('id = ?', $id);
        $arreglo["online"]    = "0";
        $arreglo["borrador"]  = "0";
        $arreglo["eliminado"] = "1";

        $rs = $this->update(
            $arreglo, $where
        );

        $resultado = exec("curl -X POST -d 'api_key=".$buscamasConsumerKey."&nid=".$id."&site=".$buscamasUrl."' ".$buscamasPublishUrl);
    }

    public function getMisProcesosBorradores($id, $col = '', $ord = '')
    {
        $col                  = $col == '' ? 'aw.fh_creacion' : $col;
        $ord                  = $ord == '' ? 'DESC' : $ord;
        $sql                  = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('id' => 'aw.id',
                'fcreacion' => 'DATE_FORMAT(aw.fh_creacion,"%d/%m/%Y")',
                'puesto' => new Zend_Db_Expr('(aw.puesto)'),
                'tipo' => 'aw.tipo',
                'estado' => 'aw.estado',
                'codigo' => 'aw.codigo_pago',
                'slug' => 'aw.slug',
                'url_id' => 'aw.url_id',
                'anuncio_impreso' => 'id_anuncio_impreso')
            )
            ->joinLeft(
                array('c' => 'compra'), 'c.id = aw.id_compra',
                array('codigoCip' => 'c.cip')
            )
            ->join(
                array('p' => 'producto'), 'p.id = aw.id_producto',
                array('nombreProducto' => 'p.nombre')
            )
            ->joinLeft(
                array('ai' => 'anuncio_impreso'),
                'ai.id = aw.id_anuncio_impreso',
                array('titulo', 'fh_creacion', 'estado_impreso' => 'estado', 'tipoAnuncio' => 'ai.tipo')
            )
            ->joinLeft(
                array('t' => 'tarifa'), 'aw.id_tarifa = t.id',
                array('medioPublicacion' => 't.medio_pub',
                'tamanoId' => 'id_tamano')
            )
            ->joinLeft(
                array('ti' => 'tamano_impreso'), 't.id_tamano = ti.id',
                array('medida' => 'ti.medida_tarifa',
                'tamanio' => 'ti.descripcion',
                'tamanioCentimetros' => 'ti.tamano_centimetro')
            )
            ->joinLeft(array('u' => 'usuario'), 'u.id = aw.creado_por',
                array('u.email'))
            ->where("aw.id_empresa = ?", $id)
            ->where("aw.borrador = 1")
            ->where("aw.origen != 'adecsys'")
            ->where("aw.eliminado = 0")
            //->where("aw.id_anuncio_impreso is not null")
            ->order(sprintf('%s %s', $col, $ord));
        $rs                   = $this->getAdapter()->fetchAll($sql); //echo $sql;
        $result               = array();
        $arrayPreferencial    = array();
        $anuncioImpresoModelo = new Application_Model_AnuncioImpreso();
        //var_dump($rs); exit;
        foreach ($rs as $r) {
            if ($r['tipoAnuncio'] != 'preferencial') {
                $result[] = $r;
            } else {
                if (!in_array($r['anuncio_impreso'], $arrayPreferencial)) {
                    $r['puesto']         = $r['titulo'];
                    $r['estado']         = $r['estado_impreso'];
                    $arrayPreferencial[] = $r['anuncio_impreso'];
                    $result[]            = $r;
                }
            }
        }
        //var_dump($result);
        return $result;
    }

    public function getPaginatorProcesosBorradores($id, $col = '', $ord = '')
    {
        $paginado = $this->_config->empresa->misprocesos->paginadoborradores;
        $p        = Zend_Paginator::factory(
                $this->getMisProcesosBorradores($id, $col, $ord)
        );
        return $p->setItemCountPerPage($paginado);
    }

    public function getHistorial($id)
    {
        $adapter = $this->getAdapter();
        $sql     = '(
                SELECT aw.id,
                        DATE_FORMAT(aw.fh_pub,"%d/%m/%Y") AS fcreacion,
                        DATE_FORMAT(aw.fh_vencimiento,"%d/%m/%Y") AS ffin,
                        UPPER(aw.puesto) AS puesto,
                        aw.tipo,
                        aw.estado AS estado,
                        aw.codigo_pago AS codigo,
                        aw.slug,
                        aw.url_id
                FROM anuncio_web AS aw
                WHERE aw.id='.$id.'
                )
                UNION ALL
                (
                SELECT aw2.id,
                        DATE_FORMAT(aw2.fh_pub,"%d/%m/%Y") AS fcreacion,
                        DATE_FORMAT(aw2.fh_vencimiento,"%d/%m/%Y") AS ffin,
                        aw2.puesto,
                        aw2.tipo,
                        aw2.estado AS estado,
                        aw2.codigo_pago AS codigo,
                        aw2.slug,
                        aw2.url_id
                FROM anuncio_web AS aw
                INNER JOIN anuncio_web AS aw2 ON aw.id = aw2.extiende_a
                WHERE (aw2.extiende_a = '.$id.') AND (aw2.eliminado = 0)
                    and aw2.id!=aw2.extiende_a
                ORDER BY aw2.fh_pub DESC
                )';
        $stm     = $adapter->query($sql);

        return $stm->fetchAll();
    }

    public static function accesoAnuncio($empresaAnuncioId, $auth = null)
    {
        if ($auth == null) {
            return false;
        }
        $usuario  = $auth['usuario'];
        $usuAdmin = substr($usuario->rol, 0, 5);

        if ($usuAdmin == Application_Form_Login::ROL_ADMIN) {
            return true;
        }

        if ($usuario->rol == Application_Form_Login::ROL_EMPRESA_ADMIN ||
            $usuario->rol == Application_Form_Login::ROL_EMPRESA_USUARIO) {
            $empresa = $auth['empresa'];
            if ($empresa['id'] == $empresaAnuncioId) {
                return true;
            }
        }
        return false;
    }
    /* consulta para ver a todos los postulantes en ver-proceso */

    /**
     *
     * @param type $id
     * @param type $col
     * @param type $ord
     * @param type $categoria
     * @param type $opcion
     * @param type $nivelestudio
     * @param type $tipocarrera
     * @param type $experiencia
     * @param type $idiomas
     * @param type $programas
     * @param type $edad
     * @param type $sexo
     * @param type $ubicacion
     * @param type $q
     * @return type
     */
    public function listarProcesos_(
    $id, $col = '', $ord = '', $categoria = '', $opcion = '',
    $nivelestudio = '', $niveldeOtrosestudios = '', $tipocarrera = '',
    $experiencia = '', $idiomas = '', $programas = '', $edad = '', $sexo = '',
    $ubicacion = '', $q = '', $postulacion = ''
    )
    {
        $buscador = "mysql";
        //------------------------------
        if ($buscador != "zend") {
            $this->getAdapter()->query("SET SESSION group_concat_max_len = 100000;");
            $col = $col == '' ? ' p.es_nuevo DESC' : $col." ".$ord;
            $sql = $this->getAdapter()->select()
                ->from(
                    array('aw' => 'anuncio_web'),
                    array('id' => 'aw.id',
                    'idpostulante' => 'ps.id',
                    'idpostulacion' => 'p.id',
                    'foto' => 'ps.path_foto',
                    'nombres' => 'ps.nombres',
                    'apellidos' => 'CONCAT(ps.apellido_paterno," ",ps.apellido_materno)',
                    'apellido_paterno' => 'ps.apellido_paterno',
                    'apellido_materno' => 'ps.apellido_materno',
                    'telefono' => 'ps.celular',
                    'slug' => 'ps.slug',
                    'msg_por_responder' => 'p.msg_por_responder',
                    'sexo' => 'ps.sexo',
                    'edad' => 'FLOOR(DATEDIFF(CURDATE(),ps.fecha_nac)/365)',
                    'match' => 'ROUND(p.match,0)',
                    'nivel_estudio' => 'ps.mejor_nivel_estudio',
                    /*   new Zend_Db_Expr("(
                      SELECT
                      (SELECT
                      GROUP_CONCAT(IF(ISNULL(`niv`.`nombre`),'Sin estudios',`niv`.`nombre`) SEPARATOR '/')
                      FROM `nivel_estudio` `niv`
                      WHERE ((`niv`.`id` IN(`es`.`id_nivel_estudio`,`es`.`id_nivel_estudio_tipo`))
                      AND (`es`.`id_nivel_estudio` <> 9))) AS `niveles`
                      FROM (`estudio` `es`
                      JOIN `nivel_estudio` `ne`
                      ON ((`ne`.`id` = `es`.`id_nivel_estudio`)))
                      WHERE (`es`.`id_postulante` = ps.id)
                      ORDER BY (((SELECT
                      `nivel_estudio`.`peso`
                      FROM `nivel_estudio`
                      WHERE (`nivel_estudio`.`id` = `ne`.`id`)) * 100) +
                      (SELECT `nivel_estudio`.`peso`  FROM `nivel_estudio`
                      WHERE (`nivel_estudio`.`id` = IF( `es`.`id_nivel_estudio_tipo`=0,1,`es`.`id_nivel_estudio_tipo`))))
                      DESC
                      LIMIT 1)"), */
                    // 'p.nivel_estudio',
                    'carrera' => 'ps.mejor_carrera',
                    /* new Zend_Db_Expr("
                      (SELECT
                      (SELECT
                      IF((`carre`.`nombre` = 'Otros'),`est`.`otro_carrera`,`carre`.`nombre`)
                      FROM `carrera` carre
                      WHERE (`carre`.`id` = `est`.`id_carrera`)) AS `car`
                      FROM (`estudio` `est`
                      JOIN `nivel_estudio` `ne`
                      ON ((`ne`.`id` = `est`.`id_nivel_estudio`)))
                      WHERE (`est`.`id_postulante` = `ps`.`id`)
                      ORDER BY (((SELECT
                      `nivel_estudio`.`peso`
                      FROM `nivel_estudio`
                      WHERE (`nivel_estudio`.`id` = `est`.`id_nivel_estudio`)) * 100) + (SELECT
                      `nivel_estudio`.`peso`
                      FROM `nivel_estudio`
                      WHERE (`nivel_estudio`.`id` = IF( `est`.`id_nivel_estudio_tipo`=0,1,`est`.`id_nivel_estudio_tipo`))))DESC
                      LIMIT 1)
                      "), */
                    //'p.carrera',
                    'telefono' => 'ps.celular',
                    'slug' => 'ps.slug',
                    'path_cv' => 'ps.path_cv',
                    'msg_no_leidos' => "p.msg_no_leidos",
                    'msg_respondido' => "p.msg_respondido",
                    'es_nuevo' => "p.es_nuevo",
                    'invitacion' => 'p.invitacion',
                    'referenciado' => 'p.referenciado',
                    'origen_postulacion' => new Zend_Db_Expr(
                        "if( p.referenciado =1,
                            'referido',
                            p.origen_postulacion )"
                    ),
                    'online' => 'aw.online',
                    'nexp' => new Zend_Db_Expr(
                        "
                                 IFNULL((SELECT SUM(
                                      (IFNULL(ex.fin_ano,YEAR(CURRENT_DATE()))
                                      *12+IFNULL(ex.fin_mes,MONTH(CURRENT_DATE())))-
                                      (ex.inicio_ano*12+ex.inicio_mes) )
                                  FROM experiencia ex
                                  WHERE ex.id_postulante=ps.id), 0)
                               "
                    ),
                    'ubigeo' => 'ub.nombre',
                    'id_ubigeo' => 'ub.id',
                    /* 'puesto' => new Zend_Db_Expr(
                      "
                      (SELECT GROUP_CONCAT(e.otro_puesto SEPARATOR ' ') FROM experiencia e
                      WHERE e.id_postulante=ps.id)
                      "
                      ),
                      'tareas' => new Zend_Db_Expr(
                      "
                      (SELECT GROUP_CONCAT(e.comentarios SEPARATOR ' ') FROM experiencia e
                      WHERE e.id_postulante=ps.id)
                      "
                      ),
                      'empresa' => new Zend_Db_Expr(
                      "
                      (SELECT GROUP_CONCAT(e.otra_empresa SEPARATOR ' ') FROM experiencia e
                      WHERE e.id_postulante=ps.id)
                      "
                      ),
                      'area' => new Zend_Db_Expr(
                      "
                      (SELECT GROUP_CONCAT(a.nombre SEPARATOR '-') FROM experiencia e
                      INNER JOIN `area` a ON e.id_area=a.id
                      WHERE e.id_postulante=ps.id)
                      "
                      ),
                      "presentacion" => "ps.presentacion", */
                    "destacado" => "ps.destacado"
                    )
                )
                ->join(
                    array('p' => 'postulacion'),
                    'aw.id = p.id_anuncio_web and p.activo = 1', array()
                )
                ->join(
                    array('e' => 'empresa'), 'aw.id_empresa = e.id', array()
                )
                ->join(
                    array('ps' => 'postulante'), 'p.id_postulante = ps.id',
                    array()
                )
                ->join(
                    array('ub' => 'ubigeo'), 'ps.id_ubigeo = ub.id', array()
                )
                ->join(
                    array('u' => 'usuario'), 'ps.id_usuario = u.id', array()
                )
                ->where("aw.id = ?", $id)
                ->where("aw.eliminado = 0")
                ->order($col)
                ->order("ps.destacado DESC", "p.match DESC");
            if ($opcion > -2) {
                if ($categoria != "")
                        $sql = $sql->where("p.id_categoria_postulacion=?",
                        $categoria);
                else $sql = $sql->where("p.id_categoria_postulacion IS NULL");

                if ($opcion != "" && $opcion > -1)
                        $sql = $sql->where("p.es_nuevo=?", $opcion);

                $sql = $sql->where("p.descartado=0");
                //   echo $sql;
            } else {
                if ($opcion == -3) {
                    $sql = $this->getAdapter()->select()
                        ->from(
                            array("r" => "referenciado"),
                            array("id" => "r.id",
                            "email" => "r.email",
                            "sexo" => "r.sexo",
                            "nombres" => "r.nombre",
                            "apellidos" => "r.apellidos",
                            "telefono" => "r.telefono",
                            "path_cv" => "r.curriculo",
                            "estado" => "r.estado",
                            "fecha_creacion" => "r.fecha_creacion",
                            "id_anuncio_web" => "r.id_anuncio_web",
                            "es_nuevo" => new Zend_Db_Expr("0"),
                            "idpostulacion" => new Zend_Db_Expr("0"),
                            "match" => new Zend_Db_Expr("100"),
                            "invitacion" => new Zend_Db_Expr("0"),
                            "referenciado" => new Zend_Db_Expr("1"),
                            "foto" => new Zend_Db_Expr("NULL"),
                            "slug" => new Zend_Db_Expr("NULL"),
                            "edad" => new Zend_Db_Expr("0"),
                            "nivel_estudio" => new Zend_Db_Expr("'Ninguno'"),
                            "carrera" => new Zend_Db_Expr("'Ninguno'"),
                            "msg_respondido" => new Zend_Db_Expr("NULL"),
                            "ubigeo" => new Zend_Db_Expr("NULL")
                            )
                        )
                        ->where("id_anuncio_web=?", $id)
                        ->where("tipo =?",
                        Application_Model_Referenciado::TIPO_REFERIDO);
                } else {
                    if ($opcion == -2) {
                        $sql = $sql->where("p.descartado=1");
                    }
                }
            }

            //FILTROS PARA EL QUERY
            //Busqueda por Nivel de Estudio
            if ($opcion != -3) {
                //NIVEL ESTUDIO -----------------------------------------
                if ($nivelestudio != "") {
                    $ne     = "";
                    $query1 = '';
                    for ($i = 0; $i < count($nivelestudio); $i++) {

                        // $ne = $ne . " ne.id =" . $nivelestudio[$i] . " OR ";
                        $nivel1          = strstr($nivelestudio[$i], ',', true);
                        ;
                        $SubnivelEstudio = trim(strstr($nivelestudio[$i], ','),
                            ',');
                        if ($nivel1 != '9') {
                            if ($SubnivelEstudio != '0') {
                                $nivel2 = "AND (es.id_nivel_estudio_tipo= ".$SubnivelEstudio.") OR ";
                            }
                            if ($SubnivelEstudio == '0') {
                                if ($nivel1 == 1 || $nivel1 == 2 || $nivel1 == 3) {
                                    $nivel2 = "OR ";
                                    $nivel1 = $nivel1;
                                } else {
                                    $nivel2 = "OR ";
                                    $nivel1 = 0;
                                }
                            }
                            $ne = $ne."(es.id_nivel_estudio =".$nivel1.") ".$nivel2;
                        } else {
                            $nivel2 = "AND (es.id_nivel_estudio_tipo= ".$SubnivelEstudio.") OR ";
                            $ne     = $ne."(es.id_nivel_estudio =".$nivel1.") ".$nivel2;
                        }
                    }
                    $ne = substr($ne, 0, strlen($ne) - 3);

                    /* */ $sql = $sql->join(
                            array("es" => "estudio"),
                            "es.id_postulante = ps.id", array()
                        )
                        ->join(
                            array("ne" => "nivel_estudio"),
                            "ne.id = es.id_nivel_estudio", array()
                        )
                        ->where($ne)
                    ;
                }

                if ($niveldeOtrosestudios != '') {
                    $nes = "";

                    for ($i = 0; $i < count($niveldeOtrosestudios); $i++) {

                        // $ne = $ne . " ne.id =" . $nivelestudio[$i] . " OR ";
                        $nivelotros1 = strstr($niveldeOtrosestudios[$i], ',',
                            true);
                        ;
                        $nivelotros2 = trim(strstr($niveldeOtrosestudios[$i],
                                ','), ',');
                        $nivel2      = "AND (est.id_nivel_estudio_tipo= ".$nivelotros2.") OR ";
                        $nes         = $nes."(est.id_nivel_estudio =".$nivelotros1.") ".$nivel2;
                    }
                    $nes = substr($nes, 0, strlen($nes) - 3);
                    $sql = $sql->join(
                            array("est" => "estudio"),
                            "est.id_postulante = ps.id", array()
                        )
                        ->where($nes)
                    ;
                }

                //TIPO CATEGORIA ---------------------------------------
                if ($tipocarrera != "") {
                    $ne = "";
                    for ($i = 0; $i < count($tipocarrera); $i++) {
                        $ne = $ne." tc.id=".$tipocarrera[$i]." OR ";
                    }
                    $ne = substr($ne, 0, strlen($ne) - 3);
                    if ($nivelestudio == "") {
                        $sql = $sql->join(
                            array("es" => "estudio"),
                            "es.id_postulante = ps.id", array()
                        );
                    }
                    $sql = $sql->join(
                            array("ca" => "carrera"), "ca.id = es.id_carrera",
                            array()
                        )
                        ->join(
                            array("tc" => "tipo_carrera"),
                            "tc.id = ca.id_tipo_carrera", array()
                        )
                        ->where($ne);
                }
                //IDIOMAS --------------------------------------------
                if ($idiomas != "") {
                    $ne = "";
                    for ($i = 0; $i < count($idiomas); $i++) {
                        $ne = $ne." di.id_idioma='".$idiomas[$i]."' OR ";
                    }
                    $ne  = substr($ne, 0, strlen($ne) - 3);
                    $sql = $sql->join(
                            array("di" => "dominio_idioma"),
                            "di.id_postulante = ps.id", array()
                        )
                        ->where($ne);
                }
                //PROGRAMAS ----------------------------------------------
                if ($programas != "") {
                    $ne = "";
                    for ($i = 0; $i < count($programas); $i++) {
                        $ne = $ne." pc.id_programa_computo=".$programas[$i]." OR ";
                    }
                    $ne  = substr($ne, 0, strlen($ne) - 3);
                    $sql = $sql->join(
                            array("pc" => "dominio_programa_computo"),
                            "pc.id_postulante = ps.id", array()
                        )
                        ->where($ne);
                }
                //EDAD ------------------------------------------------------
                if ($edad != "") {
                    $ne = "";
                    for ($i = 0; $i < count($edad); $i++) {
                        $arr = explode("-", $edad[$i]);
                        $a   = $arr[0];
                        $b   = $arr[1];
                        if ($b == "mas") $b   = 200;
                        $ne.="( FLOOR(DATEDIFF(CURDATE(),ps.fecha_nac)/365)>=$a
                                AND FLOOR(DATEDIFF(CURDATE(),ps.fecha_nac)/365)<=$b) OR ";
                    }
                    $ne  = substr($ne, 0, strlen($ne) - 4);
                    $sql = $sql->where($ne);
                }

                //SEXO -----------------------------------------------------------
                if ($sexo != "") {
                    $s = "";
                    for ($i = 0; $i < count($sexo); $i++) {
                        $s = $s."ps.sexo='".$sexo[$i]."' OR ";
                    }
                    $s   = substr($s, 0, strlen($s) - 4);
                    $sql = $sql->where($s);
                }
                //UBIGEO ---------------------------------------------------------
                if ($ubicacion != "") {
                    $s = "";
                    for ($i = 0; $i < count($ubicacion); $i++) {
                        $s = $s."ps.id_ubigeo=".$ubicacion[$i]." OR ";
                    }
                    $s   = substr($s, 0, strlen($s) - 4);
                    $sql = $sql->where($s);
                }
                //POSTULACION ---------------------------------------------------------
                if ($postulacion != "") {
                    $s = "";
                    for ($i = 0; $i < count($postulacion); $i++) {
                        if ((string) $postulacion[$i] == 'referido') {
                            $s = $s." p.referenciado = 1 OR ";
                        } else {
                            $s = $s." p.origen_postulacion = '".$postulacion[$i]."' and  referenciado=0 OR ";
                        }
                    }
                    $s   = substr($s, 0, strlen($s) - 4);
                    $sql = $sql->where($s);
                }
                //QUERY ---------------------------------------------------
                if ($q != "") {
                    $sql = $sql->having(
                        "LOWER(ps.nombres) like LOWER('%$q%') COLLATE utf8_bin
                        OR LOWER(ps.apellido_paterno) like LOWER('%$q%') COLLATE utf8_bin
                        OR LOWER(ps.apellido_materno) like LOWER('%$q%') COLLATE utf8_bin
                        OR LOWER(puesto) like LOWER('%$q%') COLLATE utf8_bin
                        OR LOWER(empresa) like LOWER('%$q%') COLLATE utf8_bin
                        OR LOWER(area) like LOWER('%$q%') COLLATE utf8_bin
                        OR LOWER(ps.presentacion) like LOWER('%$q%') COLLATE utf8_bin
                        OR LOWER(tareas) like LOWER('%$q%') COLLATE utf8_bin"
                    );
                }

                $sql = $sql->group("p.id");
                //EXPERIENCIA -----------------------------------------------------
                if ($experiencia != "") {
                    $s = "";
                    for ($i = 0; $i < count($experiencia); $i++) {
                        $arr = explode("-", $experiencia[$i]);
                        $a   = $arr[0];
                        $b   = $arr[1];
                        $s   = $s."(nexp>=".$a." AND nexp<=".$b.") OR ";
                    }
                    $s   = substr($s, 0, strlen($s) - 4);
                    $sql = $sql->having($s);
                }
            }
            //  echo $sql;
            //$rs = $this->getAdapter()->fetchAll($sql);
            return $sql;
        }
    }

    public function listarProcesos(
    $id, $col = '', $ord = '', $categoria = '', $opcion = '',
    $nivelestudio = '', $niveldeOtrosestudios = '', $tipocarrera = '',
    $experiencia = '', $idiomas = '', $programas = '', $edad = '', $sexo = '',
    $ubicacion = '', $q = '', $postulacion = '', $conadis = ''
    )
    {

        $this->getAdapter()->query("SET SESSION group_concat_max_len = 100000;");
        $col = $col == '' ? ' p.es_nuevo DESC' : $col." ".$ord;
        $sql = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('id' => 'aw.id',
                'idpostulante' => 'ps.id',
                'idpostulacion' => 'p.id',
                'foto' => 'ps.path_foto',
                'nombres' => 'ps.nombres',
                'apellidos' => new Zend_Db_Expr('CONCAT(ps.apellido_paterno," ",ps.apellido_materno)'),
                'apellido_paterno' => 'ps.apellido_paterno',
                'apellido_materno' => 'ps.apellido_materno',
                'telefono' => 'ps.celular',
                'slug' => 'ps.slug',
                'msg_por_responder' => 'p.msg_por_responder',
                'sexo' => 'ps.sexo',
                'edad' => new Zend_Db_Expr('FLOOR(DATEDIFF(CURDATE(),ps.fecha_nac)/365)'),
                'match' => new Zend_Db_Expr('ROUND(p.match,0)'),
                'nivel_estudio' => //'ps.mejor_nivel_estudio',
                new Zend_Db_Expr("(SELECT
                        (SELECT GROUP_CONCAT(IF(ISNULL(`niv`.`nombre`),'Sin estudios',`niv`.`nombre`) SEPARATOR '/')
                        FROM `nivel_estudio` `niv`
                        WHERE ((`niv`.`id` IN(`es`.`id_nivel_estudio`,`es`.`id_nivel_estudio_tipo`))
                        AND (`es`.`id_nivel_estudio` <> 9))) AS `niveles`
                        FROM `estudio` `es`
                        JOIN `nivel_estudio` `ne` ON `ne`.`id` = `es`.`id_nivel_estudio`
                        INNER JOIN nivel_estudio nte ON nte.id = IF( `es`.`id_nivel_estudio_tipo`=0,1,`es`.`id_nivel_estudio_tipo`)
                        WHERE `es`.`id_postulante` = ps.id
                        ORDER BY (ne.peso * 100 + nte.peso)
                        DESC LIMIT 1)"),
                'carrera' => //'ps.mejor_carrera',
                new Zend_Db_Expr("(SELECT IFNULL( c.nombre , nte.nombre + '*')
                        FROM carrera c INNER JOIN estudio e ON e.id_carrera = c.id
                        INNER JOIN nivel_estudio ne ON ne.id = e.id_nivel_estudio
                        INNER JOIN nivel_estudio nte ON nte.id = e.id_nivel_estudio_tipo
                        WHERE e.id_postulante = ps.id
                        ORDER BY (ne.peso * 100 + nte.peso) DESC , IF((`c`.`nombre` = 'Otros'),`e`.`otro_carrera`,`c`.`nombre`) ASC
                        LIMIT 1)"),
                'path_cv' => 'ps.path_cv',
                'msg_no_leidos' => "p.msg_no_leidos",
                'msg_respondido' => "p.msg_respondido",
                'es_nuevo' => "p.es_nuevo",
                'invitacion' => 'p.invitacion',
                'referenciado' => 'p.referenciado',
                'origen_postulacion' => new Zend_Db_Expr(
                    "if( p.referenciado = 1,
                            'referido',
                            p.origen_postulacion )"
                ),
                'online' => 'aw.online',
                'nexp' => 'ps.nexp',
                /* new Zend_Db_Expr(
                  "IFNULL(SUM( IF( IFNULL(ex.fin_ano,'') ='', YEAR(CURRENT_DATE()), ex.fin_ano) *
                  12 +  IF( IFNULL(ex.fin_mes,'') ='', MONTH(CURRENT_DATE()), ex.fin_mes)
                  - (IF(ex.inicio_ano='',YEAR(CURRENT_DATE()),ex.inicio_ano)*12+ex.inicio_mes)),0)"
                  ), */
                #'ubigeo' => 'ub.nombre',
                #'id_ubigeo' => 'ub.id',
                /* 'puesto' => new Zend_Db_Expr(
                  "GROUP_CONCAT( DISTINCT ex.otro_puesto SEPARATOR ' ')"
                  ),
                  'tareas' => new Zend_Db_Expr(
                  "GROUP_CONCAT( DISTINCT ex.comentarios SEPARATOR ' ')"
                  ),
                  'empresa' => new Zend_Db_Expr(
                  "GROUP_CONCAT(DISTINCT ex.otra_empresa SEPARATOR ' ')"
                  ),
                  'area' => new Zend_Db_Expr(
                  "GROUP_CONCAT( DISTINCT a.nombre SEPARATOR '-')"
                  ),
                  "presentacion" => "ps.presentacion", */
                "destacado" => "ps.destacado",
                "discapacidad" => "ps.discapacidad"
                )
            )
            ->join(
                array('p' => 'postulacion'),
                'aw.id = p.id_anuncio_web and p.activo = 1', array()
            )
            /* ->join(
              array('e' => 'empresa'), 'aw.id_empresa = e.id', array()
              ) */
            ->join(
                array('ps' => 'postulante'), 'p.id_postulante = ps.id', array()
            )
            #->join(
            #        array('ub' => 'ubigeo'), 'ps.id_ubigeo = ub.id', array()
            #)
            /* ->join(
              array('u' => 'usuario'), 'ps.id_usuario = u.id', array()
              )
              ->joinLeft(
              array('ex' => 'experiencia'), 'ex.id_postulante = ps.id', null
              )
              ->joinLeft(
              array('a' => 'area'), 'ex.id_area = a.id', null
              ) */
            ->where("aw.id = ?", $id)
            ->where("aw.eliminado = 0")
            ->order($col)
            ->order("ps.destacado DESC", "p.match DESC");
        if (!empty($conadis)) {
            $sql->where("ps.discapacidad > 0");
        }

        if ($opcion > -2) {
            if ($categoria != "")
                    $sql = $sql->where("p.id_categoria_postulacion=?",
                    $categoria);
            else $sql = $sql->where("p.id_categoria_postulacion IS NULL");

            if ($opcion != "" && $opcion > -1)
                    $sql = $sql->where("p.es_nuevo=?", $opcion);

            $sql = $sql->where("p.descartado=0");
        } else {

            if ($opcion == -3) {
                $sql = $this->getAdapter()->select()
                    ->from(
                        array("r" => "referenciado"),
                        array("id" => "r.id",
                        "email" => "r.email",
                        "sexo" => "r.sexo",
                        "nombres" => "r.nombre",
                        "apellidos" => "r.apellidos",
                        "telefono" => "r.telefono",
                        "path_cv" => "r.curriculo",
                        "estado" => "r.estado",
                        "fecha_creacion" => "r.fecha_creacion",
                        "id_anuncio_web" => "r.id_anuncio_web",
                        "es_nuevo" => new Zend_Db_Expr("0"),
                        "idpostulacion" => new Zend_Db_Expr("0"),
                        "match" => new Zend_Db_Expr("100"),
                        "invitacion" => new Zend_Db_Expr("0"),
                        "referenciado" => new Zend_Db_Expr("1"),
                        "foto" => new Zend_Db_Expr("NULL"),
                        "slug" => new Zend_Db_Expr("NULL"),
                        "edad" => new Zend_Db_Expr("0"),
                        "nivel_estudio" => new Zend_Db_Expr("'Ninguno'"),
                        "carrera" => new Zend_Db_Expr("'Ninguno'"),
                        "msg_respondido" => new Zend_Db_Expr("NULL"),
                        "ubigeo" => new Zend_Db_Expr("NULL")
                        )
                    )
                    ->where("id_anuncio_web=?", $id)
                    ->where("tipo =?",
                    Application_Model_Referenciado::TIPO_REFERIDO);
            } else {

                if ($opcion == -2) {
                    $sql = $sql->where("p.descartado=1");
                }
                if (empty($opcion)) {
                    $sql = $sql->where("p.descartado=0");
                }
            }
        }

        //FILTROS PARA EL QUERY
        //Busqueda por Nivel de Estudio
        if ($opcion != -3) {
            //NIVEL ESTUDIO -----------------------------------------
            if ($nivelestudio != "") {
                $ne     = "";
                $query1 = '';
                for ($i = 0; $i < count($nivelestudio); $i++) {
                    $nivel1          = strstr($nivelestudio[$i], ',', true);
                    ;
                    $SubnivelEstudio = trim(strstr($nivelestudio[$i], ','), ',');
                    if ($nivel1 != '9') {
                        if ($SubnivelEstudio != '0') {
                            $nivel2 = "AND (es.id_nivel_estudio_tipo= ".$SubnivelEstudio.") OR ";
                        }
                        if ($SubnivelEstudio == '0') {
                            if ($nivel1 == 1 || $nivel1 == 2 || $nivel1 == 3) {
                                $nivel2 = "OR ";
                                $nivel1 = $nivel1;
                            } else {
                                $nivel2 = "OR ";
                                $nivel1 = 0;
                            }
                        }
                        $ne = $ne."(es.id_nivel_estudio =".$nivel1.") ".$nivel2;
                    } else {
                        $nivel2 = "AND (es.id_nivel_estudio_tipo= ".$SubnivelEstudio.") OR ";
                        $ne     = $ne."(es.id_nivel_estudio =".$nivel1.") ".$nivel2;
                    }
                }
                $ne = substr($ne, 0, strlen($ne) - 3);

                $sql = $sql->join(
                        array("es" => "estudio"), "es.id_postulante = ps.id",
                        array()
                    )
                    ->join(
                        array("ne" => "nivel_estudio"),
                        "ne.id = es.id_nivel_estudio", array()
                    )
                    ->where($ne)
                ;
            }

            if ($niveldeOtrosestudios != '') {
                $nes = "";
                for ($i = 0; $i < count($niveldeOtrosestudios); $i++) {
                    $nivelotros1 = 9;
                    $nivelotros2 = $niveldeOtrosestudios[$i];
                    $nivel2      = "AND (est.id_nivel_estudio_tipo= ".$nivelotros2.") OR ";
                    $nes         = $nes."(est.id_nivel_estudio =".$nivelotros1.") ".$nivel2;
                }
                $nes = substr($nes, 0, strlen($nes) - 3);
                $sql = $sql->join(
                        array("est" => "estudio"), "est.id_postulante = ps.id",
                        array()
                    )
                    ->where($nes)
                ;
            }

            //TIPO CATEGORIA ---------------------------------------
            if ($tipocarrera != "") {
                $ne = "";
                for ($i = 0; $i < count($tipocarrera); $i++) {
                    $ne = $ne." tc.id=".$tipocarrera[$i]." OR ";
                }
                $ne = substr($ne, 0, strlen($ne) - 3);
                if ($nivelestudio == "") {
                    $sql = $sql->join(
                        array("es" => "estudio"), "es.id_postulante = ps.id",
                        array()
                    );
                }
                $sql = $sql->join(
                        array("ca" => "carrera"), "ca.id = es.id_carrera",
                        array()
                    )
                    ->join(
                        array("tc" => "tipo_carrera"),
                        "tc.id = ca.id_tipo_carrera", array()
                    )
                    ->where($ne);
            }
            //IDIOMAS --------------------------------------------
            if ($idiomas != "") {
                $ne = "";
                for ($i = 0; $i < count($idiomas); $i++) {
                    $ne = $ne." di.id_idioma='".$idiomas[$i]."' OR ";
                }
                $ne  = substr($ne, 0, strlen($ne) - 3);
                $sql = $sql->join(
                        array("di" => "dominio_idioma"),
                        "di.id_postulante = ps.id", array()
                    )
                    ->where($ne);
            }
            //PROGRAMAS ----------------------------------------------
            if ($programas != "") {
                $ne = "";
                for ($i = 0; $i < count($programas); $i++) {
                    $ne = $ne." pc.id_programa_computo=".$programas[$i]." OR ";
                }
                $ne  = substr($ne, 0, strlen($ne) - 3);
                $sql = $sql->join(
                        array("pc" => "dominio_programa_computo"),
                        "pc.id_postulante = ps.id", array()
                    )
                    ->where($ne);
            }
            //EDAD ------------------------------------------------------
            if ($edad != "") {
                $ne = "";
                for ($i = 0; $i < count($edad); $i++) {
                    $arr = explode("-", $edad[$i]);
                    $a   = $arr[0];
                    $b   = $arr[1];
                    if ($b == "mas") $b   = 200;
                    $ne.="( FLOOR(DATEDIFF(CURDATE(),ps.fecha_nac)/365)>=$a
                                AND FLOOR(DATEDIFF(CURDATE(),ps.fecha_nac)/365)<=$b) OR ";
                }
                $ne  = substr($ne, 0, strlen($ne) - 4);
                $sql = $sql->where($ne);
            }

            //SEXO -----------------------------------------------------------
            if ($sexo != "") {
                $s = "";
                for ($i = 0; $i < count($sexo); $i++) {
                    $s = $s."ps.sexo='".$sexo[$i]."' OR ";
                }
                $s   = substr($s, 0, strlen($s) - 4);
                $sql = $sql->where($s);
            }
            //UBIGEO ---------------------------------------------------------
            if ($ubicacion != "") {
                $s = "";
                for ($i = 0; $i < count($ubicacion); $i++) {
                    $s = $s."ps.id_ubigeo=".$ubicacion[$i]." OR ";
                }
                $s   = substr($s, 0, strlen($s) - 4);
                $sql = $sql->where($s);
            }
            //POSTULACION ---------------------------------------------------------
            if ($postulacion != "") {
                $s = "";
                for ($i = 0; $i < count($postulacion); $i++) {
                    if ((string) $postulacion[$i] == 'referido') {
                        $s = $s." p.referenciado = 1 OR ";
                    } else {
                        $s = $s." p.origen_postulacion = '".$postulacion[$i]."' and  referenciado=0 OR ";
                    }
                }
                $s   = substr($s, 0, strlen($s) - 4);
                $sql = $sql->where($s);
            }
            //QUERY ---------------------------------------------------
            if ($q != "") {
                $sql = $sql->joinLeft(
                        array('ex' => 'experiencia'),
                        'ex.id_postulante = ps.id',
                        array(
                        'puesto' => new Zend_Db_Expr(
                            "GROUP_CONCAT( DISTINCT ex.otro_puesto SEPARATOR ' ')"
                        ),
                        'tareas' => new Zend_Db_Expr(
                            "GROUP_CONCAT( DISTINCT ex.comentarios SEPARATOR ' ')"
                        ),
                        'empresa' => new Zend_Db_Expr(
                            "GROUP_CONCAT(DISTINCT ex.otra_empresa SEPARATOR ' ')"
                        )
                        )
                    )
                    ->joinLeft(
                    array('a' => 'area'), 'ex.id_area = a.id',
                    array(
                    'area' => new Zend_Db_Expr(
                        "GROUP_CONCAT( DISTINCT a.nombre SEPARATOR '-')"
                    )
                    )
                );
                $sql = $sql->having(
                    "LOWER(ps.nombres) like LOWER('%$q%') COLLATE utf8_bin
                        OR LOWER(ps.apellido_paterno) like LOWER('%$q%') COLLATE utf8_bin
                        OR LOWER(ps.apellido_materno) like LOWER('%$q%') COLLATE utf8_bin
                        OR LOWER(puesto) like LOWER('%$q%') COLLATE utf8_bin
                        OR LOWER(empresa) like LOWER('%$q%') COLLATE utf8_bin
                        OR LOWER(area) like LOWER('%$q%') COLLATE utf8_bin
                        OR LOWER(CONCAT(ps.nombres,' ',ps.apellido_paterno,' ',ps.apellido_materno)) like LOWER('%$q%') COLLATE utf8_bin
                        OR LOWER(tareas) like LOWER('%$q%') COLLATE utf8_bin"
                );
            }

            $sql = $sql->group("p.id");
            //EXPERIENCIA -----------------------------------------------------
            if ($experiencia != "") {
                $s = "";
                for ($i = 0; $i < count($experiencia); $i++) {
                    $arr = explode("-", $experiencia[$i]);
                    $a   = $arr[0];
                    $b   = $arr[1];
                    $s   = $s."(nexp>=".$a." AND nexp<=".$b.") OR ";
                }
                $s   = substr($s, 0, strlen($s) - 4);
                $sql = $sql->where($s);
            }
        }

        //$rs = $this->getAdapter()->fetchAll($sql);
        // die($sql);
        return $sql;
    }

    public function getPaginatorListarProceso(
    $id, $col = '', $ord = '', $categoria = '', $opcion = '',
    $nivelestudio = '', $niveldeOtrosestudios = '', $tipocarrera = '',
    $experiencia = '', $idiomas = '', $programas = '', $edad = '', $sexo = '',
    $ubicacion = '', $query = '', $postulacion = '', $conadis = ''
    )
    {
        $paginado = $this->_config->empresa->misprocesos->paginadoverproceso;
        $p        = Zend_Paginator::factory(
                $this->listarProcesos(
                    $id, $col, $ord, $categoria, $opcion, $nivelestudio,
                    $niveldeOtrosestudios, $tipocarrera, $experiencia, $idiomas,
                    $programas, $edad, $sexo, $ubicacion, $query, $postulacion,
                    $conadis
                )
        );
        return $p->setItemCountPerPage($paginado);
    }

    public function listarPostulantes(
    $col = '', $ord = '', $opcion = '', $nivelestudio = '', $tipocarrera = '',
    $experiencia = '', $idiomas = '', $programas = '', $edad = '', $sexo = '',
    $ubicacion = '', $q = '', $idanuncioweb = '', $idnivelpuesto = '',
    $idarea = ''
    )
    {

        if ($q != "") {
            $q = str_replace("%", "", $q);
            $q = str_replace("\\", "", $q);
            $q = str_replace("*", "", $q);
            //$q = strtolower($q);
        }


        $col = $col == '' ? 'nodefinido string ASC' : $col." string ".$ord;
        $zl  = new ZendLucene();

        $order = $col;
        $query = "";

        if ($idnivelpuesto != "") {
            $query = " AND (idnivelpuesto=".$idnivelpuesto." OR
                      idarea=".$idarea.")";
        }

        //Busqueda por Nivel de Estudio
        if ($nivelestudio != "") {
            $ne = "";
            for ($i = 0; $i < count($nivelestudio); $i++) {
                $ne.=" estudiosclaves:".$nivelestudio[$i]." AND";
            }
            $ne = substr($ne, 0, strlen($ne) - 3);
            $query.=" AND ($ne)";
        }
        //Busqueda por Tipo Carrera
        if ($tipocarrera != "") {
            $tc = "";
            for ($i = 0; $i < count($tipocarrera); $i++) {
                $tc.=" carreraclaves:".$tipocarrera[$i]." AND";
            }
            $tc = substr($tc, 0, strlen($tc) - 3);
            $query.=" AND ($tc)";
        }

        //Busqueda por Experiencia
        if ($experiencia != "") {
            $e = "";
            for ($i = 0; $i < count($experiencia); $i++) {
                $arr  = explode("-", $experiencia[$i]);
                $nUno = $zl->fillZeroField($arr[0]);
                $nDos = $zl->fillZeroField($arr[1]);
                $e.=" experiencia:[".$nUno." TO ".$nDos."] OR";
            }
            $e = substr($e, 0, strlen($e) - 2);
            $query.=" AND ($e)";
        }
        //Busqueda por Idioma
        if ($idiomas != "") {
            $idio = "";
            for ($i = 0; $i < count($idiomas); $i++) {
                $idio.=" idiomas:".$idiomas[$i]." AND";
            }
            $idio = substr($idio, 0, strlen($idio) - 3);
            $query.=" AND ($idio)";
        }

        //Busqueda por Programas
        if ($programas != "") {
            $pro = "";
            for ($i = 0; $i < count($programas); $i++) {
                $pro.=" programasclaves:".$programas[$i]." AND";
            }
            $pro = substr($pro, 0, strlen($pro) - 3);
            $query.=" AND ($pro)";
        }

        //Busqueda por Edad
        if ($edad != "") {
            $e = "";
            for ($i = 0; $i < count($edad); $i++) {
                $arr  = explode("-", $edad[$i]);
                $nUno = $zl->fillZeroField($arr[0]);
                $nDos = $zl->fillZeroField($arr[1]);
                $e.=" edad:[".$nUno." TO ".$nDos."] OR";
            }
            $e = substr($e, 0, strlen($e) - 2);
            $query.=" AND ($e)";
        }

        //Busqueda por sexo
        if ($sexo != "") {
            $s = "";
            for ($i = 0; $i < count($sexo); $i++) {
                $s.=" sexoclaves:".$sexo[$i]." OR";
            }
            $s = substr($s, 0, strlen($s) - 2);
            $query.=" AND ($s)";
        }

        //Busqueda por Ubigeo
        if ($ubicacion != "") {
            $s = "";
            for ($i = 0; $i < count($ubicacion); $i++) {
                $s.=" ubigeoclaves:".$ubicacion[$i]." OR";
            }
            $s = substr($s, 0, strlen($s) - 2);
            $query.=" AND ($s)";
        }

        //Busqueda por FILTRO DE TEXTO
        if ($q != "") {
            $query.=" AND (nombres:$q OR apellidos:$q OR empresa:$q OR cargo:$q ".
                "OR ubigeo:$q OR puesto:$q OR estudios:$q OR sexo:$q)";
        }
        $query = substr($query, 4, strlen($query));

        if ($this->_config->confpaginas->javalucene == 1) {
            return $query;
        }

        //echo $query; exit;
        $resultado = $zl->queryUsuarios(
            $query, array($order, "idpostulante int DESC")
        );
        return ($resultado == "") ? array() : $resultado;
    }

    public function queryFiltersPostulantes($nivelestudio = '',
                                            $tipocarrera = '',
                                            $experiencia = '', $idiomas = '',
                                            $programas = '', $edad = '',
                                            $sexo = '', $ubicacion = '',
                                            $idnivelpuesto = '', $idarea = '')
    {
        if ($nivelestudio == "") $nivelestudio = explode("--", $nivelestudio);
        if ($tipocarrera == "") $tipocarrera  = explode("--", $tipocarrera);
        if ($experiencia == "") $experiencia  = explode("--", $experiencia);
        if ($idiomas == "") $idiomas      = explode("--", $idiomas);
        if ($programas == "") $programas    = explode("--", $programas);
        if ($edad == "") $edad         = explode("--", $edad);
        if ($sexo == "") $sexo         = explode("--", $sexo);
        if ($ubicacion == "") $ubicacion    = explode("--", $ubicacion);
        /*
          $tipocarrera = explode("--", $tipocarrera);
          $experiencia = explode("--", $experiencia);
          $idiomas = explode("--", $idiomas);
          $programas = explode("--", $programas);
          $edad = explode("--", $edad);
          $sexo = explode("--", $sexo);
          $ubicacion = explode("--", $ubicacion);
         */

        $filters = "";

        if ($idnivelpuesto != "")
                $filters = $filters."(nivelpuesto:".$idnivelpuesto." OR area:".$idarea.") AND ";
        if ($edad[0] != "")
                $filters = $filters.$this->getQueryFilterRange("edad", $edad)." AND ";
        if ($programas[0] != "")
                $filters = $filters.$this->getQueryFilter("programasclaves",
                    $programas)." AND ";
        if ($idiomas[0] != "")
                $filters = $filters.$this->getQueryFilter("idiomas", $idiomas)." AND ";
        if ($tipocarrera[0] != "")
                $filters = $filters.$this->getQueryFilter("tipocarreraclaves",
                    $tipocarrera)." AND ";
        if ($nivelestudio[0] != "")
                $filters = $filters.$this->getQueryFilter("estudiosclaves",
                    $nivelestudio, "OR", true, "1")." AND ";
        if ($sexo[0] != "")
                $filters = $filters.$this->getQueryFilter("sexoclaves", $sexo,
                    "OR")." AND ";
        if ($ubicacion[0] != "")
                $filters = $filters.$this->getQueryFilter("ubigeoclaves",
                    $ubicacion)." AND ";
        if ($experiencia[0] != "")
                $filters = $filters.$this->getQueryFilterRange("experiencia",
                    $experiencia)." AND ";

        if ($filters != "")
                $filters = substr($filters, 0, strlen($filters) - 5);

        return $filters;
    }

    public function getPaginatorListarPostulantes(
    $col = '', $ord = '', $opcion = '', $nivelestudio = '', $tipocarrera = '',
    $experiencia = '', $idiomas = '', $programas = '', $edad = '', $sexo = '',
    $ubicacion = '', $query = '', $pQuery = '', $idanuncioweb = '',
    $idnivelpuesto = '', $idarea = ''
    )
    {
        if ($this->_config->confpaginas->javalucene == 1) {
            $paginado = $this->_config->empresa->misprocesos->paginadobuscadorpostulantes;
            // @codingStandardsIgnoreStart
            $adapter  = new App_Paginator_Adapter_LucenePostulantes(
                $query, $pQuery,
                $this->queryFiltersPostulantes(
                    $nivelestudio, $tipocarrera, $experiencia, $idiomas,
                    $programas, $edad, $sexo, $ubicacion, $idnivelpuesto,
                    $idarea
                ), $col, $ord
            );
            // @codingStandardsIgnoreEnd
            $p        = new Zend_Paginator($adapter);
            return $p->setItemCountPerPage($paginado);
        } else {
            $paginado = $this->_config->empresa->misprocesos->paginadobuscadorpostulantes;

            $p = Zend_Paginator::factory(
                    $this->listarPostulantes(
                        $col, $ord, $opcion, $nivelestudio, $tipocarrera,
                        $experiencia, $idiomas, $programas, $edad, $sexo,
                        $ubicacion, $query, $idanuncioweb, $idnivelpuesto,
                        $idarea
                    )
            );
            return $p->setItemCountPerPage($paginado);
        }
    }

    public function getDatosPagarAnuncio($id)
    {
        $sql       = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('nombreEmpresa' => 'e.razon_social',
                'anuncioId' => 'aw.id',
                'url' => 'url_id',
                'slug' => 'slug',
                'empresaId' => 'aw.id_empresa',
                'slug_anuncio' => 'aw.slug',
                'productoId' => 'aw.id_producto',
                'anuncioImpresoId' => 'aw.id_anuncio_impreso',
                'tarifaId' => 'aw.id_tarifa',
                'fechaCreacion' => 'aw.fh_creacion',
                'tipo' => 'aw.tipo')
            )
            ->joinLeft(
                array('c' => 'compra'), 'aw.id_compra = c.id',
                array('estadoCompra' => 'c.estado',
                'medioPago' => 'c.medio_pago')
            )
            ->join(
                array('t' => 'tarifa'), 'aw.id_tarifa = t.id',
                array('tarifaPrecio' => 't.precio',
                'medioPublicacion' => 't.medio_pub')
            )
            ->join(
                array('p' => 'producto'), 't.id_producto = p.id',
                array('nombreProducto' => 'p.nombre')
            )
            ->join(
                array('e' => 'empresa'), 'aw.id_empresa = e.id',
                array('empresaRuc' => 'e.ruc',
                'empresaRazonSocial' => 'e.razon_social',
                'empresaRazonComercial' => 'e.nombre_comercial')
            )
            ->join(
                array('u' => 'usuario'), 'e.id_usuario = u.id',
                array('empresaMail' => 'u.email',
                'usuarioId' => 'u.id')
            )
            ->where('aw.id = ?', $id);
        $rsAnuncio = $this->getAdapter()->fetchRow($sql);

        $sql                     = $this->getAdapter()->select()
            ->from(
                array('pd' => 'producto_detalle'),
                array(
                'codigo' => 'b.codigo',
                'nombreBeneficio' => 'b.nombre',
                'descbeneficio' => 'b.desc',
                'valor' => 'pd.valor',
                'idbeneficio' => 'b.id',
                'adecsyscode' => 'b.adecsys_code')
            )
            ->join(
                array('b' => 'beneficio'), 'pd.id_beneficio = b.id', array()
            )
            ->join(
                array('p' => 'producto'), 'pd.id_producto = p.id', array()
            )
            ->where('pd.id_producto = ?', $rsAnuncio['productoId']);
        $rs                      = $this->getAdapter()->fetchAssoc($sql);
        $rsAnuncio['beneficios'] = $rs;

        $sql                      = $this->getAdapter()->select()
            ->from(
                array('e' => 'extracargos'),
                array('codigoBeneficio' => 'b.codigo',
                'extracargoId' => 'e.id',
                'nombreBeneficio' => 'b.nombre',
                'precioExtracargo' => 'e.precio',
                'adecsysCod' => 'e.adecsys_cod',
                'adecsysCodEnvioDos' => 'e.adecsys_cod_envio_dos',
                'imagen' => 'e.imagen',
                'valorExtracargo' => 'e.valor')
            )
            ->join(
                array('b' => 'beneficio'), 'e.id_beneficio = b.id', array()
            )
            ->where('e.id_tarifa = ?', $rsAnuncio['tarifaId']);
        $rs                       = $this->getAdapter()->fetchAssoc($sql);
        $rsAnuncio['extracargos'] = $rs;

        $sql                 = $this->getAdapter()->select()
            ->from(
                array('ee' => 'empresa_ente'), array('enteId' => 'ee.ente_id')
            )
            ->where('ee.empresa_id = ?', $rsAnuncio['empresaId'])
            ->where('ee.esta_activo = 1');
        $enteId              = $this->getAdapter()->fetchOne($sql);
        $rsAnuncio['enteId'] = $enteId;
        return $rsAnuncio;
    }

    public function getDatosGenerarCompra($idAnuncio)
    {
        $sql       = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array(
                'AnuncioPuesto'=>'aw.puesto',
                    'AnuncioLogo'=>'aw.logo',
                'nombreEmpresa' => 'e.razon_social',
                'anuncioId' => 'aw.id',
                'id' => 'aw.id',
                'url' => 'url_id',
                'slug' => 'slug',
                'empresaId' => 'aw.id_empresa',
                'slug_anuncio' => 'aw.slug',
                'productoId' => 'aw.id_producto',
                'anuncioImpresoId' => 'aw.id_anuncio_impreso',
                'tarifaId' => 'aw.id_tarifa',
                'tipo' => 'aw.tipo')
            )
            ->joinLeft(
                array('c' => 'compra'), 'aw.id_compra = c.id',
                array('estadoCompra' => 'c.estado',
                'idCompra' => 'c.id', 'medioPago' => 'c.medio_pago')
            )
            ->join(
                array('t' => 'tarifa'), 'aw.id_tarifa = t.id',
                array('tarifaPrecio' => 't.precio',
                'medioPublicacion' => 't.medio_pub')
            )
            ->join(
                array('p' => 'producto'), 't.id_producto = p.id',
                array('nombreProducto' => 'p.nombre')
            )
            ->join(
                array('e' => 'empresa'), 'aw.id_empresa = e.id',
                array('empresaRuc' => 'e.ruc',
                'empresaRazonSocial' => 'e.razon_social',
                'empresaRazonComercial' => 'e.nombre_comercial')
            )
            ->join(
                array('u' => 'usuario'), 'e.id_usuario = u.id',
                array('empresaMail' => 'u.email',
                'usuarioId' => 'u.id')
            )
            ->where('aw.id = ?', $idAnuncio);
        $rsAnuncio = $this->getAdapter()->fetchRow($sql);
        //Bloque de numero de anuncios
        $rsIds     = array();
        if (!empty($rsAnuncio['anuncioImpresoId'])) {
            $sql   = $this->getAdapter()->select()
                ->from(
                    array('aw' => 'anuncio_web'), array('id')
                )
                ->where('id_anuncio_impreso = ?', $rsAnuncio['anuncioImpresoId']);
            $rsIds = $this->getAdapter()->fetchAll($sql);
        }
        if (count($rsIds) > 0) {
            $rsAnuncio['anunciosWeb'] = $rsIds;
        } else {
            $rsAnuncio['anunciosWeb'][0] = array('id' => $rsAnuncio['anuncioId']);
        }

        //Bloque de beneficios
        $sql                     = $this->getAdapter()->select()
            ->from(
                array('pd' => 'producto_detalle'),
                array(
                'codigo' => 'b.codigo',
                'nombreBeneficio' => 'b.nombre',
                'descbeneficio' => 'b.desc',
                'valor' => 'pd.valor',
                'idbeneficio' => 'b.id',
                'adecsyscode' => 'b.adecsys_code')
            )
            ->join(
                array('b' => 'beneficio'), 'pd.id_beneficio = b.id', array()
            )
            ->join(
                array('p' => 'producto'), 'pd.id_producto = p.id', array()
            )
            ->where('pd.id_producto = ?', $rsAnuncio['productoId']);
        $rs                      = $this->getAdapter()->fetchAssoc($sql);
        $rsAnuncio['beneficios'] = $rs;

        //Bloque de extracargos
        $sql                      = $this->getAdapter()->select()
            ->from(
                array('e' => 'extracargos'),
                array('codigoBeneficio' => 'b.codigo',
                'extracargoId' => 'e.id',
                'nombreBeneficio' => 'b.nombre',
                'precioExtracargo' => 'e.precio',
                'adecsysCod' => 'e.adecsys_cod',
                'adecsysCodEnvioDos' => 'e.adecsys_cod_envio_dos',
                'imagen' => 'e.imagen',
                'valorExtracargo' => 'e.valor')
            )
            ->join(
                array('b' => 'beneficio'), 'e.id_beneficio = b.id', array()
            )
            ->where('e.id_tarifa = ?', $rsAnuncio['tarifaId']);
        $rs                       = $this->getAdapter()->fetchAssoc($sql);
        $rsAnuncio['extracargos'] = $rs;

        //Bloque del Ente
        $sql                 = $this->getAdapter()->select()
            ->from(
                array('ee' => 'empresa_ente'), array('enteId' => 'ee.ente_id')
            )
            ->where('ee.empresa_id = ?', $rsAnuncio['empresaId'])
            ->where('ee.esta_activo = 1');
        $enteId              = $this->getAdapter()->fetchOne($sql);
        $rsAnuncio['enteId'] = $enteId;
        return $rsAnuncio;
    }

    public function getDatosWebGenerarCompra($idAnuncio)
    {
        $sql       = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array(
                'anuncioId' => 'aw.id',
                'id' => 'aw.id',
                'url' => 'aw.url_id',
                'slug' => 'aw.slug',
                'empresaId' => 'aw.id_empresa',
                'slug_anuncio' => 'aw.slug',
                'medio_pago_web' => 'aw.medio_pago',
                'productoId' => 'aw.id_producto',
                'anuncioImpresoId' => 'aw.id_anuncio_impreso',
                'tarifaId' => 'aw.id_tarifa',
                'tipo' => 'aw.tipo')
            )
            ->join(
                array('t' => 'tarifa'), 'aw.id_tarifa = t.id',
                array(
                'tarifaPrecio' => 't.precio',
                'medioPublicacion' => 't.medio_pub'
                )
            )
            ->join(
                array('p' => 'producto'), 't.id_producto = p.id',
                array(
                'nombreProducto' => 'p.nombre',
                'tipo_medio_publicacion' => 'p.tipo'
                )
            )
            ->where('aw.id = ?', $idAnuncio);
        $rsAnuncio = $this->getAdapter()->fetchRow($sql);

        //Bloque de numero de anuncios
        $rsIds = array();
        if (!empty($rsAnuncio['anuncioImpresoId'])) {
            $sql   = $this->getAdapter()->select()
                ->from(
                    array('aw' => 'anuncio_web'), array('id')
                )
                ->where('id_anuncio_impreso = ?', $rsAnuncio['anuncioImpresoId']);
            $rsIds = $this->getAdapter()->fetchAll($sql);
        }
        if (count($rsIds) > 0) {
            $rsAnuncio['anunciosWeb'] = $rsIds;
        } else {
            $rsAnuncio['anunciosWeb'][0] = array('id' => $rsAnuncio['anuncioId']);
        }

        //Bloque de beneficios
        $sql                     = $this->getAdapter()->select()
            ->from(
                array('pd' => 'producto_detalle'),
                array(
                'codigo' => 'b.codigo',
                'nombreBeneficio' => 'b.nombre',
                'descbeneficio' => 'b.desc',
                'valor' => 'pd.valor',
                'idbeneficio' => 'b.id',
                'adecsyscode' => 'b.adecsys_code')
            )
            ->join(
                array('b' => 'beneficio'), 'pd.id_beneficio = b.id', array()
            )
            ->join(
                array('p' => 'producto'), 'pd.id_producto = p.id', array()
            )
            ->where('pd.id_producto = ?', $rsAnuncio['productoId']);
        $rs                      = $this->getAdapter()->fetchAssoc($sql);
        $rsAnuncio['beneficios'] = $rs;

        //Bloque de extracargos
        $sql                      = $this->getAdapter()->select()
            ->from(
                array('e' => 'extracargos'),
                array('codigoBeneficio' => 'b.codigo',
                'extracargoId' => 'e.id',
                'nombreBeneficio' => 'b.nombre',
                'precioExtracargo' => 'e.precio',
                'adecsysCod' => 'e.adecsys_cod',
                'adecsysCodEnvioDos' => 'e.adecsys_cod_envio_dos',
                'imagen' => 'e.imagen',
                'valorExtracargo' => 'e.valor')
            )
            ->join(
                array('b' => 'beneficio'), 'e.id_beneficio = b.id', array()
            )
            ->where('e.id_tarifa = ?', $rsAnuncio['tarifaId']);
        $rs                       = $this->getAdapter()->fetchAssoc($sql);
        $rsAnuncio['extracargos'] = $rs;

        //Bloque del Ente
        $sql                 = $this->getAdapter()->select()
            ->from(
                array('ee' => 'empresa_ente'), array('enteId' => 'ee.ente_id')
            )
            ->where('ee.empresa_id = ?', $rsAnuncio['empresaId'])
            ->where('ee.esta_activo = 1');
        $enteId              = $this->getAdapter()->fetchOne($sql);
        $rsAnuncio['enteId'] = $enteId;
        return $rsAnuncio;
    }

    public function updateLogoAnuncio($empresaId, $logo, $cache = null)
    {
        $where = $this->getAdapter()->quoteInto("id_empresa = ?", $empresaId);
        $this->update(array("logo" => $logo), $where);

        $anuncios = $this->obtenerPorEmpresa($empresaId, array('url_id'));

//        $cache = new App_Service_Cache;
//        foreach ($anuncios as $anuncio) {
//            $cache->clear(App_Service_Cache::AD_PREFIX_SHEET, $anuncio['url_id']);
//        }
    }

    public function obtenerPorEmpresa($empresaId, $columnas = array())
    {
        $columnas = $this->setCols($columnas);

        return $this->fetchAll($this->select()
                    ->from($this->_name, $columnas)
                    ->where('id_empresa =?', $empresaId));
    }

    public function getAvisoExtendido($avisoId)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'), array('aw.extiende_a', 'aw.tipo')
            )
            ->where('aw.id = ?', $avisoId);
        return $this->getAdapter()->fetchRow($sql);
    }

    public function getAvisosExtendidosXImpreso($impresoId)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('aw.extiende_a', 'aw.tipo', 'aw.id')
            )
            ->joinInner(
                array('aw2' => 'anuncio_web'),
                'aw.id != aw2.`extiende_a`AND aw.id = aw2.id', array()
            )
            ->joinInner(
                array('ai' => 'anuncio_impreso'),
                'ai.id = aw.id_anuncio_impreso', array()
            )
            ->where('ai.id = ?', $impresoId);
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * Da de baja un anuncio web
     *
     * @param int $avisoId
     * @param int $usuarioId
     */
    public function bajaAnuncioWeb($avisoExtId, $avisoId, $usuarioId)
    {

        $config = Zend_Registry::get('config');

        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
        $buscamasUrl         = $config->apis->buscamas->url;
        $buscamasPublishUrl  = $config->apis->buscamas->publishUrl;

        $fecha      = date('Y-m-d H:i:s');
        $avisoExtId = (int) $avisoExtId;
        $avisoId    = (int) $avisoId;
        /* $where = $this->getAdapter()->quoteInto(
          "extiende_a = $avisoExtId AND id != $avisoId", null
          ); */

        $where = $this->getAdapter()->quoteInto("id = ?", $avisoExtId);

        $this->update(
            array(
            'online' => '0',
            'borrador' => '0',
            'cerrado' => '1',
            'proceso_activo' => '0',
            'fh_vencimiento_proceso' => date('Y-m-d'),
            'modificado_por' => $usuarioId,
            'fh_edicion' => $fecha,
            'fh_aviso_baja' => $fecha,
            'fh_aviso_eliminado' => $fecha
            ), $where
        );

        $resultado = exec("curl -X POST -d 'api_key=".$buscamasConsumerKey."&nid=".$avisoExtId."&site=".$buscamasUrl."' ".$buscamasPublishUrl);
    }

    /**
     * Publicar anuncio web
     *
     * @param int $avisoId
     * @param int $usuarioId
     */
    public function publicarAnuncioWeb($avisoId, $usuarioId)
    {

        $config = Zend_Registry::get('config');

        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
        $buscamasUrl         = $config->apis->buscamas->url;
        $buscamasPublishUrl  = $config->apis->buscamas->publishUrl;

        $where = $this->getAdapter()->quoteInto("id = ?", $avisoId);
        $this->update(
            array(
            'online' => 1,
            'borrador' => 0,
            'cerrado' => 0,
            'modificado_por' => $usuarioId
            ), $where
        );

        $resultado = exec("curl -X POST -d 'api_key=".$buscamasConsumerKey."&nid=".$avisoId."&site=".$buscamasUrl."' ".$buscamasPublishUrl);
    }
    /*
     * Super funcion para detectar porcentaje de coincidencia
     * $aId = ID DEL AVISO
     * $pId = ID POSTULANTE
     * Author: Solman Vaisman Gonzalez
     */

    public function porcentajeCoincidenciaNuevo($aId, $pId)
    {
        $config          = Zend_Registry::get("config");
        $pesoEstudios    = $config->empresa->coincidencia->estudios->peso;
        $pesoExperiencia = $config->empresa->coincidencia->experiencia->peso;
        $pesoIdiomas     = $config->empresa->coincidencia->idiomas->peso;
        $pesoProgramas   = $config->empresa->coincidencia->programas->peso;

        $adapter = $this->getAdapter();
        $aId     = $adapter->quote($aId);
        $pId     = $adapter->quote($pId);
        //@codingStandardsIgnoreStart

        $sql = "SELECT IFNULL(SUM(s.puntaje*s.peso)*100/SUM(s.total*s.peso), 0) AS aptitus_match
                FROM (  /*Estudios*/
                        SELECT  SUM(IF(NOT ep.id_postulante IS NULL, 1,0)) AS puntaje,
                            COUNT(*) AS total,
                            $pesoEstudios AS peso
                        FROM anuncio_estudio ae
                        LEFT JOIN estudio ep ON ep.id_nivel_estudio = ae.id_nivel_estudio
                            AND IFNULL(ep.id_carrera,0) = IFNULL(ae.id_carrera,0)
                            AND ep.id_postulante = $pId
                        WHERE ae.id_anuncio_web = $aId
                        UNION ALL
                        /*Experiencia*/
                        SELECT SUM(ex.puntaje) AS puntaje,ex.total,ex.peso
                        FROM (
                            SELECT SUM(q1.puntaje*q1.total) puntaje,
                                   SUM(q1.total) total, $pesoExperiencia AS peso
                            FROM (
                            SELECT IFNULL( IF((@vsum := SUM(IF( ae.experiencia <
                                       (@vif := IF(ep.fin_ano, ep.fin_ano*12+ep.fin_mes,
                                        YEAR(CURDATE())*12+MONTH(CURDATE())) -
                                    (ep.inicio_ano*12+ep.inicio_mes)), 1, @vif / ae.experiencia)
                                 )) > 1, 1, SUM(IF(NOT ep.id_postulante IS NULL,
                IF( ae.experiencia <
                    (@vif := (IF(ep.fin_ano,
                     ep.fin_ano*12+ep.fin_mes,
                     YEAR(CURDATE())*12+MONTH(CURDATE())) - (ep.inicio_ano*12+ep.inicio_mes)))
          , 1, @vif / ae.experiencia), 0)))*0.4, 0) AS puntaje,
                                ae.experiencia AS total
                            FROM anuncio_experiencia ae
                            LEFT JOIN experiencia ep ON ep.id_nivel_puesto = ae.id_nivel_puesto
                                AND ep.id_area = ae.id_area AND ep.id_postulante = $pId
                            WHERE ae.id_anuncio_web=$aId
                            GROUP BY ae.id) AS q1
                            UNION ALL
                            SELECT SUM(q2.puntaje*q2.total) puntaje,
                                   SUM(q2.total) total, $pesoExperiencia AS peso
                            FROM (
                            SELECT IFNULL( IF((@vsum := SUM(IF( ae.experiencia <
                                       (@vif := IF(ep.fin_ano, ep.fin_ano*12+ep.fin_mes,
                                        YEAR(CURDATE())*12+MONTH(CURDATE())) -
                                    (ep.inicio_ano*12+ep.inicio_mes)), 1, @vif / ae.experiencia)
                                 )) > 1, 1, SUM(IF(NOT ep.id_postulante IS NULL,
                IF( ae.experiencia <
                    (@vif := (IF(ep.fin_ano,
                     ep.fin_ano*12+ep.fin_mes,
                     YEAR(CURDATE())*12+MONTH(CURDATE())) - (ep.inicio_ano*12+ep.inicio_mes)))
          , 1, @vif / ae.experiencia), 0)))*0.35, 0) AS puntaje,
                                ae.experiencia AS total
                            FROM anuncio_experiencia ae
                            LEFT JOIN experiencia ep ON ep.id_nivel_puesto = ae.id_nivel_puesto
                                AND ep.id_postulante = $pId
                            WHERE ae.id_anuncio_web=$aId
                            GROUP BY ae.id) AS q2
                            UNION ALL
                            SELECT SUM(q3.puntaje*q3.total) puntaje,
                                   SUM(q3.total) total, $pesoExperiencia AS peso
                            FROM (
                            SELECT IFNULL( IF((@vsum := SUM(IF( ae.experiencia <
                                       (@vif := IF(ep.fin_ano, ep.fin_ano*12+ep.fin_mes,
                                        YEAR(CURDATE())*12+MONTH(CURDATE())) -
                                    (ep.inicio_ano*12+ep.inicio_mes)), 1, @vif / ae.experiencia)
                                 )) > 1, 1, SUM(IF(NOT ep.id_postulante IS NULL,
                IF( ae.experiencia <
                    (@vif := (IF(ep.fin_ano,
                     ep.fin_ano*12+ep.fin_mes,
                     YEAR(CURDATE())*12+MONTH(CURDATE())) - (ep.inicio_ano*12+ep.inicio_mes)))
          , 1, @vif / ae.experiencia), 0)))*0.25, 0) AS puntaje,
                                ae.experiencia AS total
                            FROM anuncio_experiencia ae
                            LEFT JOIN experiencia ep ON ep.id_area = ae.id_area
                                AND ep.id_postulante = $pId
                            WHERE ae.id_anuncio_web=$aId
                            GROUP BY ae.id) AS q3
                        ) AS ex
                        UNION ALL
                        /*Dominio Idioma*/
                        SELECT SUM( IF(NOT di.id_idioma IS NULL,
                                IF((@nilpos:=(CASE di.nivel_lee
                                            WHEN 'basico' THEN 1
                                            WHEN 'intermedio' THEN 2
                                            WHEN 'avanzado' THEN 3 END))
                                < (@nianu:=(CASE ai.nivel
                                            WHEN 'basico' THEN 1
                                            WHEN 'intermedio' THEN 2
                                            WHEN 'avanzado' THEN 3 END))
                                 , @nilpos, @nianu) +
                                IF((@niepos:=(CASE di.nivel_escribe
                                            WHEN 'basico' THEN 1
                                            WHEN 'intermedio' THEN 2
                                            WHEN 'avanzado' THEN 3 END))
                                < @nianu, @niepos, @nianu) +
                                IF((@nihpos:=(CASE di.nivel_hablar
                                            WHEN 'basico' THEN 1
                                            WHEN 'intermedio' THEN 2
                                            WHEN 'avanzado' THEN 3 END))
                                < @nianu, @nihpos, @nianu)
                                , 0)) AS puntaje,
                                   SUM((CASE ai.nivel
                                        WHEN 'basico' THEN 1
                                        WHEN 'intermedio' THEN 2
                                        WHEN 'avanzado' THEN 3 END)*3) AS total,
                          $pesoIdiomas AS peso
                        FROM anuncio_idioma ai
                        LEFT JOIN dominio_idioma di ON di.id_idioma = ai.id_idioma
                            AND di.id_postulante = $pId
                        WHERE ai.id_anuncio_web = $aId
                        UNION ALL
                        /*Programa Computo*/
                       SELECT SUM(IF(NOT dpc.id_programa_computo IS NULL,
                            IF((@npos:= (CASE dpc.nivel
                                        WHEN 'basico' THEN 1
                                        WHEN 'intermedio' THEN 2
                                        WHEN 'avanzado' THEN 3 END)) <
                                (@nanu:= (CASE apc.nivel
                                        WHEN 'basico' THEN 1
                                        WHEN 'intermedio' THEN 2
                                        WHEN 'avanzado' THEN 3 END)),
                            @npos, @nanu),
                            0)) AS puntaje,
                            SUM((CASE apc.nivel
                                WHEN 'basico' THEN 1
                                WHEN 'intermedio' THEN 2
                                WHEN 'avanzado' THEN 3 END)) AS total,
                            $pesoProgramas AS peso
                        FROM anuncio_programa_computo apc
                        LEFT JOIN dominio_programa_computo dpc
                            ON dpc.id_programa_computo = apc.id_programa_computo
                            AND dpc.id_postulante = $pId
                        WHERE apc.id_anuncio_web = $aId
                             ) AS s ";

        //@codingStandardsIgnoreEnd
        //echo $sql;  exit;
        $stmp = $adapter->query($sql);
        $stmp->execute();
        return $stmp->fetchAll();
    }
    /*
     * Extrae el mejor nivel de estudios y la carrera de la tabla estudio para ello
     * requerimos de el idPostulante
     */

    public function extraerMejorNivelEstudiosYCarrera($pId)
    {
        $adapter = $this->getAdapter();
        $pId     = $adapter->quote($pId);
        $sql     = "SELECT
                ne.nombre as nivelestudios,
                if(id_carrera IS NULL,e.otro_carrera,c.nombre)  as carrera
                FROM estudio e
                INNER JOIN nivel_estudio ne ON ne.id = e.id_nivel_estudio
                LEFT JOIN carrera c ON c.id = e.id_carrera
                WHERE id_postulante = {$pId}
                ORDER BY ne.peso DESC, e.id DESC
                limit 1;";
        $stmp    = $adapter->query($sql);
        $stmp->execute();
        return $stmp->fetchAll();
    }
    /*
     * Funcion que actualiza todos los registros en los campos MATCH, NIVELESTUDIO Y CARRERA de
     * la tabla "postulacion" con solo pasarle los parametros:
     *
     */

    public function actualizarPostulacion($idPostulacion, $match, $nivel = "",
                                          $carrera = "")
    {
        $adapter = $this->getAdapter();
        $where   = $this->getAdapter()->quoteInto('id = ?', $idPostulacion);
        $model   = new Application_Model_Postulacion();
        $model->update(
            array(
            "match" => $match,
            "nivel_estudio" => $nivel,
            "carrera" => $carrera
            ), $where);

//        $sql = "UPDATE postulacion AS p
//                    SET
//                      p.match = $match,
//                      p.nivel_estudio = '$nivel',
//                      p.carrera = '$carrera'
//                    WHERE p.id = $idPostulacion";

        if ($nivel == "" && $carrera == "") {
            $model->update(array("match" => $match), $where);
//            $sql = "UPDATE postulacion AS p
//                    SET
//                      p.match = $match
//                    WHERE p.id = $idPostulacion ";
        }
//        $stmp = $adapter->query($sql);
//        $stmp->execute();
//
    }

    /**
     * Retorna los datosd el anuncio impreso de un anuncio web
     *
     * @param int $anuncioId
     */
    public function getAnuncioImpreso($anuncioId)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'), array('texto' => 'ai.texto')
            )
            ->join(
                array('ai' => 'anuncio_impreso'),
                'aw.id_anuncio_impreso = ai.id'
            )
            ->where('aw.id = ?', $anuncioId);
        $rs  = $this->getAdapter()->fetchRow($sql);
        return $rs;
    }

    /**
     * Función para saber si una empresa tiene un beneficio dado entre sus anuncios activos
     * @todo Mejorar performance, poniendo un array de beneficios activos en $auth
     * @todo Buscar solo en avisos activos
     * @todo Mover este método a una clase más relevante (Empresa o Beneficio)
     */
    public function tieneBeneficio($empresaId, $beneficios, $tipo = null)
    {


        $sql = $this->getAdapter()->select()->distinct()
            ->from(array('aw' => $this->_name), array('codigo' => 'awd.codigo'))
            ->joinInner(array('awd' => 'anuncio_web_detalle'),
                'awd.id_anuncio_web = aw.id', array())
            ->where('aw.id_empresa = ?', $empresaId)
            ->where('awd.codigo= ?', "$beneficios")
            ->where('aw.online = ?', 1)
            ->where('aw.fh_vencimiento_proceso > ?', date('Y-m-d H:i:s'));


        if ($tipo != null) {
            $sql->where('aw.tipo = ?',
                Application_Model_AnuncioWeb::TIPO_PREFERENCIAL);
        }
        $rs = $this->getAdapter()->fetchAll($sql);
        return count($rs);
    }

    public function beneficioBuscadorAptitus($auth)
    {
//      $codeBuscador = Application_Model_Beneficio::CODE_BUSCADOR;
//      $buscardor=$auth['empresa']['membresia_info']['beneficios']->buscador;
//      $idMem=$auth['empresa']['membresia_info']['membresia']['id_membresia'];
//      $buscadorAviso=  $this->tieneBeneficio($auth['empresa']['id'], 'buscador',Application_Model_AnuncioWeb::TIPO_PREFERENCIAL);
//      $tieneBuscadorAptitus=false;
//      if(($buscardor || $idMem=='11') || $buscadorAviso){
//            $tieneBuscadorAptitus = true;
//      } 
        return true;
    }

    public function tieneBeneficioAvisoByFecha($empresaId, $beneficios,
                                               $tipo = null)
    {
        $adapter = $this->getAdapter();
        $sql     = $this->getAdapter()->select()->distinct()
            ->from(array('aw' => $this->_name), array('fh_pub' => 'aw.fh_pub'))
            ->joinInner(array('awd' => 'anuncio_web_detalle'),
                'awd.id_anuncio_web = aw.id', array())
            ->where('aw.id_empresa = ?', $empresaId)
            ->where('awd.codigo= ?', "$beneficios")
            ->where('aw.online = ?', 1)
            ->where('aw.fh_vencimiento_proceso > ?', date('Y-m-d H:i:s'));
        if ($tipo != null) {
            $sql->where('aw.tipo = ?',
                Application_Model_AnuncioWeb::TIPO_PREFERENCIAL);
        }
        $sql->order("awd.id_anuncio_web DESC")->limit(1);
        $rs = $this->getAdapter()->fetchAll($sql);
        return $rs;
    }

    public function getAvisosNoPostulados($idPostulante, $idEmpresa)
    {

        $idAnuncio = $this->getAdapter()->select()
            ->from('postulacion', 'id_anuncio_web')
            ->where('id_postulante = ?', $idPostulante);

        $sql = $this->getAdapter()->select()
            ->from($this->_name, array('id', 'puesto'))
            ->where('id NOT IN (?)', $idAnuncio)
            ->where('id_empresa = ?', $idEmpresa)
            ->where('borrador = ?', 0)
            ->where('eliminado = ?', 0)
            ->where("fh_vencimiento_proceso > CURDATE()")
            //->where('tipo = "preferencial"')
            ->where('online = ?', 1);
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * Retorna lista de procesos activos de una empresa para invitar a postulantes
     * 
     * @param int $idEmpresa Id de la empresa
     * @return array Lista de procesos activos
     */
    public function getAvisosInvitar($idEmpresa)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                $this->_name,
                array(
                'id',
                'puesto' => new Zend_Db_Expr('CONCAT(DATE_FORMAT(fh_pub,"%d/%m/%Y")," - ",puesto)')
                )
            )
            ->where('id_empresa ='.$idEmpresa)
            ->where('borrador = ?', 0)
            ->where('eliminado = ?', 0)
            ->where('fh_vencimiento_proceso > CURDATE()')
            ->where('online = ?', 1);

        return $this->getAdapter()->fetchAll($sql);
    }

    public function getAvisosInvitarPreferencial($idEmpresa)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                $this->_name,
                array(
                'id',
                'puesto' => new Zend_Db_Expr('CONCAT(DATE_FORMAT(fh_pub,"%d/%m/%Y")," - ",puesto)'))
            )
            ->where('id_empresa ='.$idEmpresa)
            //          AND (aw.extiende_a IS NOT NULL)
            ->where('borrador = ?', 0)
            ->where('eliminado = ?', 0)
            ->where('tipo = "preferencial"')
            ->where('fh_vencimiento_proceso > CURDATE()')
            ->where('online = ?', 1);
        //echo $sql->assemble();
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getInvitacionesPostulante($idPostulante, $idAnuncioW)
    {
        /* $adapter = $this->getAdapter();
          $sql = "SELECT CONCAT(pos.`nombres`,' ',pos.`apellidos`) AS nombres FROM postulacion p
          INNER JOIN anuncio_web aw ON aw.`id`=p.`id_anuncio_web`
          INNER JOIN postulante pos ON p.`id_postulante`=pos.`id`
          WHERE p.`id_postulante`=$idPostulante AND aw.`id`=$idAnuncioW";
          //AND aw.`tipo`='preferencial' ";
          $stmp = $adapter->query($sql);
          $stmp->execute();
          //echo $sql;
          return $stmp->fetchAll(); */
        $sql = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('nombres' => "CONCAT(pos.`nombres`,' ',pos.`apellidos`)")
            )
            ->join(
                array('p' => 'postulacion'), 'p.id_anuncio_web = aw.id', array()
            )
            ->join(
                array('pos' => 'postulante'), 'p.id_postulante = pos.id',
                array()
            )
            ->where('p.`id_postulante` = ?', $idPostulante)
            ->where('aw.`id` = ?', $idAnuncioW);
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getPaginadorBusquedaPersonalizada($urlid, $razonsocial,
                                                      $ruc, $codAdecsys,
                                                      $tipBus, $fhPub, $col,
                                                      $ord
    )
    {
        $paginadoBusqueda = $this->_config->administrador->gestion->paginadoBusqueda;
        $p                = Zend_Paginator::factory(
                $this->getBusquedaPersonalizada(
                    $urlid, $razonsocial, $ruc, $codAdecsys, $tipBus, $fhPub,
                    $col, $ord
                )
        );
        return $p->setItemCountPerPage($paginadoBusqueda);
    }

    public function getPaginadorBusquedaPersonalizadaAviso($fhPub, $fhPubFin,
                                                           $tipoDestaque,
                                                           $tipoImpreso,
                                                           $estadoweb, $col,
                                                           $ord
    )
    {
        $paginadoBusqueda = $this->_config->administrador->gestion->paginadoBusqueda;
        $p                = Zend_Paginator::factory(
                $this->getFilterAviso(
                    $fhPub, $fhPubFin, $tipoDestaque, $tipoImpreso, $estadoweb,
                    $col, $ord
                )
        );
        return $p->setItemCountPerPage($paginadoBusqueda);
    }

    public function getFilterAviso($fhPub, $fhPubFin, $tipoDestaque,
                                   $tipoImpreso, $estadoweb, $col, $ord)
    {
        $col       = $col == '' ? 'a.fh_creacion' : $col;
        $ord       = $ord == '' ? 'DESC' : $ord;
        $subselect = $this->getAdapter()->select()
            ->from(array('a' => 'anuncio_web'),
                array(
                'url_id' => 'a.url_id', 'a.estado_publicacion', 'a.cerrado',
                'estado' => 'a.estado',
                'ubicacionslug' => 'a.slug_pais',
                'empresaslug' => 'a.empresa_rs',
                'slug' => 'a.slug',
                'estado' => new Zend_Db_Expr(
                    "CASE a.estado
                        WHEN 'registrado' THEN 'Registrado'
                        WHEN 'pendiente_pago' THEN 'Pendiente de Pago'
                        WHEN 'extornado' THEN 'Extornado'
                        WHEN 'pagado' THEN 'Pagado'
                        WHEN 'publicado' THEN 'Publicado'
                        WHEN 'dado_baja' THEN 'Dado de Baja'
                        WHEN 'vencido' THEN 'Vencido'
                        WHEN 'extendido' THEN 'Extendido'
                        WHEN 'baneado' THEN 'Baneado'
                        END"
                ),
                'medio_pago_web' => 'a.medio_pago',
                'Portal' => new Zend_Db_Expr("CONCAT('Empleo busco')"),
                'Fecha_de_Publicacion' => new Zend_Db_Expr("DATE_FORMAT(a.fh_pub, '%d/%m/%Y')"),
                'Fecha_de_cierre' => new Zend_Db_Expr("DATE_FORMAT(a.fh_vencimiento, '%d/%m/%Y')"),
                'Tipo_Destaque' => new Zend_Db_Expr(
                    "CASE a.prioridad
                        WHEN 1 THEN 'Oro'
                        WHEN 2 THEN 'Plata'
                        WHEN 6 THEN 'Sin Destaque'
                        WHEN 0 THEN 'Sin Destaque'
                        END"
                ),
                'Medio_de_Pago_Web' => new Zend_Db_Expr(
                    "CASE a.medio_pago
                        WHEN 'pf' THEN 'Punto Facil'
                        WHEN 'pv' THEN 'Pago en Ventanilla'
                        WHEN 'credomatic' THEN 'Credomatic'
                        WHEN 'destacado_impreso' THEN 'Bonificado'
                        WHEN 'gratuito'   THEN 'Gratuito'
                        WHEN 'destaque' THEN 'Destaque Manual'
                        END"
                ),
                'Monto_Web' => 'c.precio_total',
                'Tipo_Aviso_Impreso' => new Zend_Db_Expr(
                    "CASE c.tipo_anuncio
                        WHEN 'clasificado' THEN 'Lineales'
                        END"
                ),
                'Medio_de_Pago_Impreso' => new Zend_Db_Expr(
                    "CASE c.medio_pago
                        WHEN 'pf' THEN 'Punto Facil'
                        WHEN 'pv' THEN 'Pago en Ventanilla'
                        WHEN 'credomatic' THEN 'Credomatic'
                        WHEN 'bonificado' THEN 'Bonificado'
                        WHEN 'gratuito'   THEN 'Gratuito'
                        WHEN 'destaque' THEN 'Destaque Manual'
                        END"
                ),
                'Monto_Impreso' => 'c.precio_total_impreso',
                'Correo' => 'u.email',
                'Teléfono' => new Zend_Db_Expr("CONCAT(ue.telefono,'-' ,ue.telefono2)"),
                'Titulo_del_Aviso' => new Zend_Db_Expr('UPPER(a.puesto)'),
                'Estado' => 'c.estado',
                'online' => 'a.online',
                'id' => 'a.id',
                )
            )
            ->joinInner(array('c' => 'compra'), 'a.id_compra=c.id', array())
            ->join(array('u' => 'usuario'), 'u.id=c.creado_por', array())
            ->join(array('ue' => 'usuario_empresa'),
                'ue.id_usuario=c.creado_por', array())
            ->order(sprintf('%s %s', $col, $ord));
        //  ->order('a.id DESC');
        if (!empty($fhPub) && !empty($fhPubFin)) {
            $subselect->where("date(a.fh_pub) BETWEEN  '$fhPub'  AND '$fhPubFin'");
        }
        if (!empty($tipoDestaque)) {
            switch ($tipoDestaque) {
                case 'ALL':
                    $subselect->Where('a.prioridad IN  (?)', array(1, 2, 6, 0));
                    break;
                default:
                    $subselect->Where('a.prioridad=?', $tipoDestaque);
                    break;
            }
        }
        if (!empty($tipoImpreso)) {
            switch ($tipoImpreso) {
                case 'ALL':
                    $subselect->Where('c.tipo_anuncio IN (?)',
                        array(
                        'clasificado', 'inserto', 'desplegados'
                    ));
                    break;
                default:
                    $subselect->Where('c.tipo_anuncio=?', $tipoImpreso);
                    break;
            }
        }
        if (!empty($estadoweb)) {
            switch ($estadoweb) {
                case 'ALL':
                    $subselect->Where("(a.estado= 'registrado'"
                        . " OR a.estado= 'pendiente_pago' "
                        . "OR a.estado= 'extornado' "
                        . "OR a.estado= 'pagado' "
                        . "OR a.estado= 'dado_baja' "
                        . "OR a.estado= 'vencido' "
                        . "OR a.estado= 'extendido' OR a.estado= 'baneado')");
                     break;
                default:
                    $subselect->Where('a.estado=?', $estadoweb);
                    break;
            }
        }

        $subselect->order('a.id DESC');
        $dataSubSelect = $this->getAdapter()->fetchAll($subselect);
        return $dataSubSelect;
    }

    public function getFilterAvisoExport($fhPub, $fhPubFin, $tipoDestaque,
                                         $tipoImpreso, $estadoweb, $col, $ord)
    {
        $col       = $col == '' ? 'a.fh_creacion' : $col;
        $ord       = $ord == '' ? 'DESC' : $ord;
        $subselect = $this->getAdapter()->select()
            ->from(array('a' => 'anuncio_web'),
                array(
                'Portal' => new Zend_Db_Expr("CONCAT('Empleo busco')"),
                'Fecha de Publicación' => new Zend_Db_Expr("DATE_FORMAT(a.fh_pub, '%d/%m/%Y')"),
                'Fecha de cierre/fin' => new Zend_Db_Expr("DATE_FORMAT(a.fh_vencimiento, '%d/%m/%Y')"),
                'Tipo de Destaque Web' => new Zend_Db_Expr(
                    "CASE a.prioridad
                        WHEN 1 THEN 'Oro'
                        WHEN 2 THEN 'Plata'
                        WHEN 6 THEN 'Sin Destaque'
                        WHEN 0 THEN 'Sin Destaque'
                        END"
                ),
                'Medio de Pago Web' => new Zend_Db_Expr(
                    "CASE c.medio_pago
                        WHEN 'pf' THEN 'Punto Facil'
                        WHEN 'pv' THEN 'Pago en Ventanilla'
                        WHEN 'credomatic' THEN 'Credomatic'
                        WHEN 'destacado_impreso' THEN 'Bonificado'
                        WHEN 'gratuito'   THEN 'Gratuito'
                        WHEN 'destaque' THEN 'Destaque Manual'
                        END"
                ),
                'Monto Web' => 'c.precio_total',
                'Tipo de Aviso Impreso' => new Zend_Db_Expr(
                    "CASE c.tipo_anuncio
                        WHEN 'clasificado' THEN 'Lineales'
                        END"
                ),
                'Medio de Pago Impreso' => new Zend_Db_Expr(
                    "CASE c.medio_pago
                        WHEN 'pf' THEN 'Punto Facil'
                        WHEN 'pv' THEN 'Pago en Ventanilla'
                        WHEN 'credomatic' THEN 'Credomatic'
                        WHEN 'bonificado' THEN 'Bonificado'
                        WHEN 'gratuito'   THEN 'Gratuito'
                        WHEN 'destaque' THEN 'Destaque Manual'
                        END"
                ),
                'Monto Impreso' => 'c.precio_total_impreso',
                'Correo' => 'u.email',
                //   'Teléfono' =>new Zend_Db_Expr("CONCAT(ue.telefono,'-' ,ue.telefono2)"),
                'Titulo del Aviso' => new Zend_Db_Expr('UPPER(a.puesto)'),
                'Estado' => 'c.estado',
                )
            )
            ->joinInner(array('c' => 'compra'), 'a.id_compra=c.id', array())
            ->join(array('u' => 'usuario'), 'u.id=c.creado_por', array())
            ->join(array('ue' => 'usuario_empresa'),
                'ue.id_usuario=c.creado_por', array())
            ->order(sprintf('%s %s', $col, $ord));
        //  ->order('a.id DESC');
        if (!empty($fhPub) && !empty($fhPubFin)) {
            $subselect->where("date(a.fh_pub) BETWEEN  '$fhPub'  AND '$fhPubFin'");
        }
        if (!empty($tipoDestaque)) {
            switch ($tipoDestaque) {
                case 'ALL':
                    $subselect->Where('a.prioridad IN  (?)', array(1, 2, 6, 0));
                    break;
                default:
                    $subselect->Where('a.prioridad=?', $tipoDestaque);
                    break;
            }
        }
        if (!empty($tipoImpreso)) {
            switch ($tipoImpreso) {
                case 'ALL':
                    $subselect->Where('c.tipo_anuncio IN (?)',
                        array(
                        'clasificado', 'inserto', 'desplegados'
                    ));
                    break;
                default:
                    $subselect->Where('c.tipo_anuncio=?', $tipoImpreso);
                    break;
            }
        }
        if (!empty($estadoweb)) {
            switch ($estadoweb) {
                case 'ALL':
                    $subselect->Where("(a.estado= 'registrado'"
                        . " OR a.estado= 'pendiente_pago' "
                        . "OR a.estado= 'extornado' "
                        . "OR a.estado= 'pagado' "
                        . "OR a.estado= 'dado_baja' "
                        . "OR a.estado= 'vencido' "
                        . "OR a.estado= 'extendido' OR a.estado= 'baneado')");
                     break;
                default:
                    $subselect->Where('a.estado=?', $estadoweb);
                    break;
            }
        }


        $dataSubSelect = $this->getAdapter()->fetchAll($subselect);
        return $dataSubSelect;
    }

    public function getBusquedaPersonalizada($urlid, $razonsocial, $ruc,
                                             $codAdecsys, $tipBus, $fhPub,
                                             $col = '', $ord = ''
    )
    {

        $col = $col == '' ? 'aw.fh_creacion' : $col;
        $ord = $ord == '' ? 'DESC' : $ord;

        $sqlComplement  = '';
        $sqlAuxBusqueda = '';

        if ($tipBus == 0) {
            if ($urlid != null) {
                $sqlComplement = $sqlComplement.' AND aw.url_id = "'.$urlid.'"';
            }
            $razonInt = (int) $razonsocial;
            if ($razonsocial != null && $razonInt != 0) {
                $sqlComplement = $sqlComplement.' AND aw.id_empresa = '.$razonInt;

                $subselect = $this->getAdapter()->select()
                    ->from(array('a' => 'anuncio_web'),
                        array('extiende_a' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT extiende_a)')))
                    ->where('id <> extiende_a')
                    ->where('id_empresa =?', $razonInt)
                    ->order('id DESC');

                $dataSubSelect = $this->getAdapter()->fetchOne($subselect);
                $query         = '';
                if (!empty($dataSubSelect)) {
                    $query = ' AND a.id NOT IN ('.$dataSubSelect.')';
                }

                $sqlAuxBusqueda = ' AND a.id_empresa = '.$razonInt.$query;
            }
            if ($ruc != null) {
                $sqlComplement = $sqlComplement.' AND e.ruc  = "'.$ruc.'"';
                $modelEmp      = new Application_Model_Empresa();
                $arrayEmp      = $modelEmp->getEmpresaByEmail('', $ruc);
                if ($arrayEmp != false) {

                    $subselect     = $this->getAdapter()->select()
                        ->from(array('a' => 'anuncio_web'),
                            array('extiende_a' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT extiende_a)')))
                        ->where('id <> extiende_a')
                        ->where('id_empresa =?', $arrayEmp['idempresa'])
                        ->order('id DESC');
                    $dataSubSelect = $this->getAdapter()->fetchOne($subselect);
                    $query         = '';
                    if (!empty($dataSubSelect)) {
                        $query = ' AND a.id NOT IN ('.$dataSubSelect.')';
                    }

                    $sqlAuxBusqueda = ' AND a.id_empresa = '.$arrayEmp['idempresa'].$query;
                }
            }
            if ($fhPub != null) {
                $sqlComplement = $sqlComplement.' AND date(aw.fh_pub) = "'.$fhPub.'"';
            }
        } else {
            if ($codAdecsys != null) {
                $sqlComplement = $sqlComplement.
                    " AND ( cac.adecsys_code = '$codAdecsys' OR aw.adecsys_code = '$codAdecsys')";
            }
        }

        if ($sqlComplement != '') {
            $sql     = "SELECT aw.*
                    FROM (
                        SELECT
                        DISTINCT(`aw`.`id`), `aw`.`extiende_a`, `aw`.`id_empresa`, `aw`.`url_id`,
                        `aw`.`puesto`, `aw`.`tipo`, `aw`.`fh_pub`, `aw`.`fh_creacion`,
                        `aw`.`online`, `aw`.`estado`, `u`.`activo`, `e`.`ruc`, `e`.`razon_social` ,
                        aw.destacado
                        FROM anuncio_web aw
                        INNER JOIN `empresa` AS `e` ON e.id = aw.id_empresa
                        INNER JOIN `usuario` AS `u` ON u.id = e.id_usuario
                        LEFT JOIN `compra_adecsys_codigo` AS `cac` ON cac.id_compra = aw.id_compra
                        WHERE
                        aw.fh_vencimiento_proceso > CURDATE()
                        ".$sqlComplement."
                        UNION
                        SELECT a.*
                        FROM (
                            SELECT
                            DISTINCT(`aw`.`id`), `aw`.`extiende_a`, `aw`.`id_empresa`, `aw`.`url_id`,
                            `aw`.`puesto`, `aw`.`tipo`, `aw`.`fh_pub`, `aw`.`fh_creacion`,
                            `aw`.`online`, `aw`.`estado`, `u`.`activo`, `e`.`ruc`, `e`.`razon_social` ,
                            aw.destacado
                            FROM anuncio_web aw
                            INNER JOIN `empresa` AS `e` ON e.id = aw.id_empresa
                            INNER JOIN `usuario` AS `u` ON u.id = e.id_usuario
                            LEFT JOIN `compra_adecsys_codigo` AS `cac` ON cac.id_compra = aw.id_compra
                            WHERE
                            aw.fh_vencimiento_proceso <= CURDATE()
                            ".$sqlComplement."
                            ORDER BY aw.id DESC
                        ) AS a
                        WHERE a.activo IN (0,1)
                            ".$sqlAuxBusqueda."
                         GROUP BY a.extiende_a
                    ) aw ORDER BY ".sprintf('%s %s', $col, $ord);
            //echo $sql;//exit;
            $adapter = $this->getAdapter();
            $stm     = $adapter->query($sql);

            return $stm->fetchAll();
        } else {
            return array();
        }
    }

    public function getPaginadorBusquedaPersonalizadaPreferencial($nomPuesto,
                                                                  $ruc, $fhPub,
                                                                  $origen,
                                                                  $codAdecsys,
                                                                  $tipBus, $col,
                                                                  $ord
    )
    {
        $paginadoBusqueda = $this->_config->administrador->gestion->paginadoBusqueda;
        $p                = Zend_Paginator::factory(
                $this->getBusquedaPersonalizadaPreferencial(
                    $nomPuesto, $ruc, $fhPub, $origen, $codAdecsys, $tipBus,
                    $col, $ord
                )
        );
        return $p->setItemCountPerPage($paginadoBusqueda);
    }

    public function getBusquedaPersonalizadaPreferencial($nomPuesto, $ruc,
                                                         $fhPub, $origen,
                                                         $codAdecsys, $tipBus,
                                                         $col = '', $ord = ''
    )
    {
        $col = $col == '' ? 'aw.fh_creacion' : $col;
        $ord = $ord == '' ? 'DESC' : $ord;

        $sql = $this->getAdapter()->select()
                ->from(
                    array('aw' => $this->_name),
                    array('aw.id', 'aw.id_empresa', 'aw.puesto', 'aw.medio_publicacion',
                    'aw.fh_pub', 'aw.chequeado')
                )
                ->joinInner(
                    array('e' => 'empresa'), 'e.id = aw.id_empresa',
                    array('e.ruc', 'e.razon_social')
                )->where('aw.tipo = "preferencial" and aw.origen = "adecsys"');

        if ($tipBus == 0) {
            if ($nomPuesto != null) {
                $sql = $sql->where('aw.puesto like (?)', '%'.$nomPuesto.'%');
            }
            if ($ruc != null) {
                $sql = $sql->where('e.ruc = ?', $ruc);
            }
            if ($fhPub != null) {
                $sql = $sql->where('date(aw.fh_pub) = ?', $fhPub);
            }
            if ($origen != null) {
                $sql = $sql->where(' medio_publicacion = ?', $origen);
            }
        } else {
            if ($codAdecsys != null) {
                $sql = $sql->where('adecsys_code = ?', $codAdecsys);
            }
        }
        $sql = $sql->order(sprintf('%s %s', $col, $ord))->order('aw.id')->limit(50); //echo $sql;exit;
        return $sql;
    }

    public function getAllAvisosProcesoActivo()
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('aw' => $this->_name), array('aw.id')
            )
            ->where('fh_vencimiento_proceso > CURDATE()');
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getCantAvisosPorFechaPublicacion()
    {
        $sqls    = array();
        $sql     = $this->select();
        $nombres = $this->_config->busqueda->filtros->diasPublicacionNombre->toArray();
        $dias    = $this->_config->busqueda->filtros->diasPublicacionDias->toArray();
        foreach ($nombres as $slug => $nombre) {
            $ndias      = $dias[$slug];
            $sqlPartial = $this->select();
            $slug       = str_replace('í', 'i',
                str_replace('Ú', 'u', str_replace('_', '-', $slug)));
            $sqlPartial = $sqlPartial->from(
                    $this->_name,
                    array(
                    'msg' => new Zend_Db_Expr("'$nombre'"),
                    'slug' => new Zend_Db_Expr("'$slug'"),
                    'dias' => new Zend_Db_Expr("'$ndias'"),
                    'cant' => 'count(id)'
                    )
                )->where("online=1");
            $sqlPartial = $sqlPartial->where(
                'fh_pub BETWEEN DATE_SUB(FROM_UNIXTIME(UNIX_TIMESTAMP()),
                INTERVAL ? DAY) AND FROM_UNIXTIME(UNIX_TIMESTAMP())', $ndias
            );
            $sqls[]     = $sqlPartial;
        }
        $sql->union($sqls);
        //echo $sql->assemble().PHP_EOL;
        $result = $this->getAdapter()->fetchAssoc($sql);
        return $result;
    }

    public function getCantAvisosPorRangoRemuneracion()
    {
        $valores                         = $this->_config->busqueda->filtros->rangoRemuneracion
            ->toArray();
        $moneda                          = $this->config->app->moneda;
        $this->_intervalosRemuneraciones = $valores;
        $sqls                            = array();
        $db                              = $this->getAdapter();
        $sql                             = $this->select();

        $sqlPartial = $this->select();
        $sqlPartial = $sqlPartial
            ->from(
                array('aw' => 'anuncio_web'),
                array(
                'msg' => new Zend_Db_Expr(
                    sprintf(
                        "'Sin especificar'"
                    )
                ),
                'slug' => new Zend_Db_Expr("'0'"),
                'minimo' => new Zend_Db_Expr("''"),
                'maximo' => new Zend_Db_Expr("''"),
                'cant' => 'count(id)'
                )
            )
            ->where('salario_min IS NULL')
            ->where('salario_max IS NULL')
            ->where('aw.online = 1');
        $sqls[]     = $sqlPartial;
        $intervalos = $this->_intervalosRemuneraciones;
        array_push($intervalos, null);
        foreach ($intervalos as $i => $intervalo) {
            if ($i == 0) {
                $interMin = 0;
            } else {
                $interMin = $intervalos[$i - 1] + 1;
            }
            $interMax   = $intervalo;
            $sqlPartial = $this->select();
            $sqlPartial = $sqlPartial->from(
                $this->_name,
                array(
                'msg' => new Zend_Db_Expr(
                    sprintf(
                        "'de %s a %s '$moneda", $interMin,
                        is_null($interMax) ? 'más' : $interMax
                    )
                ),
                'slug' => new Zend_Db_Expr(
                    sprintf(
                        "'%s-%s'", $interMin,
                        is_null($interMax) ? 'más' : $interMax
                    )
                ),
                'minimo' => new Zend_Db_Expr("'$interMin'"),
                'maximo' => new Zend_Db_Expr("'$interMax'"),
                // 'ind'=> new Zend_Db_Expr("'$i'"),
                'cant' => 'count(id)'
                )
            );
            if (is_null($interMax)) {
                $sqlPartial = $sqlPartial->where('salario_min >= ?', $interMin)
                    ->where('anuncio_web.online = 1');
            } else {
                $sqlPartial = $sqlPartial
                    ->where(
                        '((salario_min >= '.$interMin.' AND salario_min < '.$interMax.')'.
                        ' OR (salario_max > '.$interMin.' AND salario_max <= '.$interMax.'))'
                    )
                    //->where('salario_min > ?', $interMin)
                    //->where('salario_max = ?', $interMax)
                    ->where('anuncio_web.online = 1');
//                $sqlPartial = $sqlPartial
//                    ->orWhere('salario_max >= ?', $interMin)
//                    ->where('salario_max <= ?', $interMax)
//                    ->where('anuncio_web.online = 1');
            }
            $sqls[] = $sqlPartial;
        }
        $sql->union($sqls);
        $result = $this->getAdapter()->fetchAssoc($sql);
        return $result;
    }

    public function perteneceAvisoEmpresa($idAviso, $idEmpresa)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'), array('id' => 'id')
            )
            ->where('id = ?', $idAviso)
            ->where('id_empresa = ?', $idEmpresa)
            ->limit(1);
        return $this->getAdapter()->fetchOne($sql);
    }

    public function getAnunciosPorCompra($idCompra)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('anuncioId' => 'id',
                'anuncioUrl' => 'url_id')
            )
            ->where('id_compra = ?', $idCompra)
            ->order("aw.id DESC");
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * Retorna en un array los anuncios cuyos slug no tenga concordancia con
     * el nombre del puesto.
     *
     * @return ArrayObject
     */
    public function slugsIncoherentes()
    {
        $return     = array();
        $sql        = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('puesto', 'id', 'slug', 'url_id')
            )
            ->where('origen = ?', 'adecsys');
        $data       = $this->getAdapter()->fetchAll($sql);
        $slugFilter = new App_Filter_Slug();
        foreach ($data as $d) {
            if ($slugFilter->filter($d['puesto']) != $d['slug']) {
                $return[] = $d;
            }
        }
        return $return;
    }

    /**
     * Genera un nuevo slug para el aviso, cambiando el campo redireccion para
     * el SEO.
     *
     * @param int $id
     * @param string $nombrePuesto
     * @param string $urlId
     */
    public function generarSlug($id, $nombrePuesto, $urlId)
    {
        $cache                  = Zend_Registry::get('cache');
        $slugFilter             = new App_Filter_Slug();
        $where                  = $this->getAdapter()->quoteInto('id = ?', $id);
        $arreglo["redireccion"] = "1";
        $arreglo["slug"]        = $slugFilter->filter($nombrePuesto);
        $rs                     = $this->update(
            $arreglo, $where
        );
        $this->_cache->remove('AnuncioWeb_getAvisoInfoById_'.$id);
        $this->_cache->remove('AnuncioWeb_getAvisoById_'.$id);
        $this->_cache->remove('anuncio_web_'.$urlId);
    }

    public function getAvisoBaneadoXEmpresa($idEmpresa, $estado)
    {
        $sql = $this->getAdapter()
            ->select()
            ->from(
                $this->_name,
                array(
                'id', 'estado_anterior',
                'fpublicacion' => 'DATE_FORMAT(fh_vencimiento,"%d/%m/%Y")',
                'url_id'
                )
            )
            ->where('id_empresa = ?', $idEmpresa)
            ->where('estado = ?', $estado)
            ->where('online = 0');

        return $this->getAdapter()->fetchAll($sql);
    }

    public function buscarAvisoByCodigoAdecsys($codigo)
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('aw.id', 'aw.url_id', 'aw.slug')
            )
            ->joinleft(
                array('cac' => 'compra_adecsys_codigo'),
                'aw.id_compra = cac.id_compra', array('adecsys_code')
            )
            ->where('cac.adecsys_code = ?', $codigo)
            ->where('aw.chequeado = 1')
            ->where('aw.cerrado = 0')
            ->where('aw.online = 1'); //echo $sql;exit;

        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * Retorna un array con informacion importante para el paginado de
     * anuncios preferenciales, en un formato de (toda la informacion, total
     * de anuncios llenados, posicion actual de acuerdo al ID, ID del anuncio
     * impreso al que pertenece el aviso)
     *
     * @param int $idAviso
     * @return array('data', 'totalReady', 'current', 'anuncioImpreso')
     */
    public function getPosicionByAviso($idAviso = null)
    {
        $result = array();
        if ($idAviso == null) {
            $result['data']           = null;
            $result['totalReady']     = null;
            $result['current']        = 1;
            $result['anuncioImpreso'] = null;
            return $result;
        }
        $sql            = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'), array('id_anuncio_impreso')
            )
            ->where('id = ?', $idAviso);
        $anuncioImpreso = $this->getAdapter()->fetchOne($sql);
        $sql            = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'), array('id', 'puesto')
            )
            ->where('id_anuncio_impreso = ?', $anuncioImpreso)
            ->order('fh_creacion ASC');

        $anuncios             = $this->getAdapter()->fetchAll($sql);
        $result['data']       = $anuncios;
        $result['totalReady'] = count($anuncios);
        foreach ($anuncios as $key => $value) {
            if ($value['id'] == $idAviso) {
                $result['current'] = $key + 1;
            }
        }
        $result['anuncioImpreso'] = $anuncioImpreso;

        return $result;
    }

    /**
     * Retorna un array con informacion importante para el paginado de
     * anuncios preferenciales, en un formato de (toda la informacion, total
     * de anuncios llenados, posicion actual de acuerdo al ID, ID del anuncio
     * impreso al que pertenece el aviso)
     *
     * @param int $idAvisoPreferencial
     * @return array('data', 'totalReady', 'current', 'anuncioImpreso')
     */
    public function getPosicionByAvisoPreferencial($idAvisoPreferencial = null)
    {
        $result = array();
        if ($idAvisoPreferencial == null) {
            $result['data']           = null;
            $result['totalReady']     = null;
            $result['current']        = 1;
            $result['anuncioImpreso'] = null;
            return $result;
        }
        $sql      = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'), array('id', 'puesto')
            )
            ->where('id_anuncio_impreso = ?', $idAvisoPreferencial)
            ->order('fh_creacion ASC');
        $anuncios = $this->getAdapter()->fetchAll($sql);

        $result['data']           = $anuncios;
        $result['totalReady']     = count($anuncios);
        $result['current']        = count($anuncios) + 1;
        $result['anuncioImpreso'] = $idAvisoPreferencial;
        return $result;
    }

    /**
     *
     * @param int $idAvisoPreferencial
     * @return array('data', 'totalReady', 'current', 'anuncioImpreso')
     */
    public function getPosicionByAvisosClonados($array, $idAviso)
    {
        $result  = array();
        $arrayId = array();
        $ordenAW = array();

        foreach ($array as $val) {
            $arrayId[] = $val['id'];
        }

        $sql      = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'), array('id', 'puesto')
            )
            ->where('id in (?)', $arrayId)
            ->order('aw.fh_creacion ASC');
        $anuncios = $this->getAdapter()->fetchAll($sql);

        foreach ($arrayId as $id) {
            foreach ($anuncios as $value) {
                if ($id == $value['id']) {
                    $ordenAW[] = $value;
                }
            }
        }

        $result['data']       = $ordenAW;
        $result['totalReady'] = count($anuncios);
        foreach ($ordenAW as $key => $value) {

            if ($value['id'] == $idAviso) {
                $result['current'] = $key + 1;
            }
        }
        return $result;
    }

    public function getNombreAreaByUrlId($urlId = '')
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from(array('aw' => $this->_name), array())
            ->where('url_id = ?', $urlId)
            ->where('online = 1')
            ->join(array('a' => 'area'), 'aw.id_area = a.id', array('slug'));
        $rs  = $db->fetchRow($sql);
        return !empty($rs['slug']) ? $rs['slug'] : '';
    }

    public function getIdTarifaByIdAnuncio($idAnuncio = '')
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from($this->_name, array('id_tarifa'))
            ->where('id = ?', $idAnuncio);
        $rs  = $db->fetchRow($sql);
        return !empty($rs['id_tarifa']) ? $rs['id_tarifa'] : '';
    }

    public function getTipoAnuncioById($id)
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from($this->_name, array('tipo'))
            ->where('id = ?', $id);
        return $db->fetchOne($sql);
    }

    public static function getAnunciosForMatch($minimo = 0, $tipoIn = null,
                                               $diasPorVencer = null,
                                               $limit = 1000)
    {
        $obj = new Application_Model_AnuncioWeb();
        $db  = $obj->getAdapter();
        $sql = $db->select()
            ->from(
                array('aw' => $obj->_name),
                array(
                'aw.id',
                'aw.puesto',
                'anuncio_web_slug' => 'aw.slug',
                'aw.url_id',
                'aw.fh_pub',
                'aw.fh_vencimiento',
                'aw.empresa_rs',
                'aw.mostrar_empresa',
                'aw.tipo',
                'tC' => new Zend_Db_Expr(
                    '(SELECT 1 FROM anuncio_estudio AS ae WHERE '.
                    'ae.id_anuncio_web = aw.id GROUP BY ae.id_anuncio_web)'
                ),
                'tI' => new Zend_Db_Expr(
                    '(SELECT 1 FROM anuncio_idioma AS ai WHERE '.
                    'ai.id_anuncio_web = aw.id GROUP BY ai.id_anuncio_web)'
                ),
                'tPc' => new Zend_Db_Expr(
                    '(SELECT 1 FROM anuncio_programa_computo AS apc WHERE '.
                    'apc.id_anuncio_web = aw.id GROUP BY apc.id_anuncio_web)'
                ),
                'tEx' => new Zend_Db_Expr(
                    '(SELECT 1 FROM anuncio_experiencia AS aex WHERE '.
                    'aex.id_anuncio_web = aw.id GROUP BY aex.id_anuncio_web)'
                )
                )
            )->join(
                array('p' => 'puesto'), 'p.id = aw.id_puesto',
                array(
                'puesto_nombre' => 'p.nombre'
                )
            )->join(
                array('e' => 'empresa'), 'e.id = aw.id_empresa',
                array('e.razon_social', 'empresa_slug' => 'e.slug')
            )->where('aw.online = 1')
            ->having('(IFNULL(tC,0) + IFNULL(tI,0) + IFNULL(tPC,0) + IFNULL(tEx,0)) >= ?',
                $minimo)
            ->limit($limit)
            ->group('aw.id');
        if (!empty($tipoIn)) {
            $sql = $sql->where('aw.tipo IN (?)', $tipoIn);
        }
        if (!empty($diasPorVencer)) {
            $sql = $sql->where('aw.fh_vencimiento > SUBDATE(NOW(), INTERVAL ? DAY)',
                $diasPorVencer);
        }
//        echo $sql->assemble();exit;
        return $db->fetchAll($sql);
    }

    public static function getTotalAnunciosForMatch($minimo = 0, $tipoIn = null,
                                                    $diasPorVencer = null)
    {
        $obj = new Application_Model_AnuncioWeb();
        $db  = $obj->getAdapter();
        $sql = $db->select()->from(array('aw' => $obj->_name),
                array('count(*) as amount'))
            ->where('aw.online = 1')
            ->having(
                '(IFNULL((SELECT 1 FROM anuncio_estudio AS ae WHERE
                    ae.id_anuncio_web = aw.id GROUP BY ae.id_anuncio_web), 0)
                    + IFNULL((SELECT 1 FROM anuncio_idioma AS ai WHERE
                    ai.id_anuncio_web = aw.id GROUP BY ai.id_anuncio_web), 0)
                    + IFNULL((SELECT 1 FROM anuncio_programa_computo AS apc WHERE
                    apc.id_anuncio_web = aw.id GROUP BY apc.id_anuncio_web), 0)
                    + IFNULL((SELECT 1 FROM anuncio_experiencia AS aex WHERE
                    aex.id_anuncio_web = aw.id GROUP BY aex.id_anuncio_web), 0)) >= ?',
                $minimo
            )
            ->group('aw.id');
        if (!empty($tipoIn)) {
            $sql = $sql->where('aw.tipo IN (?)', $tipoIn);
        }
        if (!empty($diasPorVencer)) {
            $sql = $sql->where('aw.fh_vencimiento > SUBDATE(NOW(), INTERVAL ? DAY)',
                $diasPorVencer);
        }

        $sqlDos = $db->select()->from(array('records' => $sql),
            array('total' => 'COUNT(*)'));
//        echo $sql->assemble();exit;
        return $db->fetchOne($sqlDos);
    }

    public function getIdImpresoByIdAviso($idAw)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name, 'id_anuncio_impreso')
            ->where('id = ?', $idAw)
            ->limit('1');
        return $this->getAdapter()->fetchOne($sql);
    }

    public function getAnuncioAmpliado($idAw, $idEmpresa)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name)
            ->where('id != ?', $idAw)
            ->where('extiende_a = ?', $idAw)
            ->where('estado = "'.self::ESTADO_PENDIENTE_PAGO.'" OR estado = "'.self::ESTADO_REGISTRADO.'"')
            ->where('id_empresa = ?', $idEmpresa)
            ->where('borrador = 1')
            ->limit('1');
        return $this->getAdapter()->fetchRow($sql);
    }

    public function confirmaAvisoActivo($idAw)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name)
            ->where('id = ?', $idAw)
            ->where('online = 1')
            ->limit('1');
        return $this->getAdapter()->fetchOne($sql);
    }

    public function actualizarPostulantes($anuncio_web_id, $numeroDePostulantes)
    {
        $data           = array();
        $data['ntotal'] = $numeroDePostulantes;
        $this->update($data, 'id = '.$anuncio_web_id);
    }

    public function obtenerPorId($id, $getCols = array())
    {
        $columnas = $this->setCols($getCols);
        return $this->fetchRow($this->select()
                    ->from($this->_name, $columnas)
                    ->where('id =?', $id));
    }

    public function obtenerPorIdYEmpresa($id, $empresaId, $columnas = array())
    {
        $columnas = $this->setCols($columnas);

        return $this->fetchRow($this->select()
                    ->from($this->_name, $columnas)
                    ->where('id =?', (int) $id)
                    ->where('id_empresa =?', (int) $empresaId));
    }

    public function obtenerConEmpresaPorId($id)
    {
        return $this->fetchRow($this->select()
                    ->from(array('aw' => $this->_name),
                        array('aw.puesto',
                        'empresa_rs', 'url_id', 'slug'))
                    ->where('aw.id =?', (int) $id));
    }

    public function obtenerVarios($ini, $fin, $columnas = array())
    {
        $columnas = $this->setCols($columnas);

        return $this->fetchAll($this->select()
                    ->from($this->_name, $columnas)
                    //->where('id >= ?', $ini)
                    //->where('id <= ?', $fin)
                    ->where('fh_pub != ?', '')
                    ->where('estado in (?)',
                        array('pagado', 'publicado',
                        'dado_baja', 'vencido', 'extendido'))
                    ->order('id desc')
                    ->limit($fin, $ini));
    }

    public function obtenerNoAsginadosUsuario(
    $usuarioEmpresaId, $empresaId, $areaId = null)
    {
        $where = $this->getAdapter()->quoteInto(
            'aue.id_usuario_empresa <>? or id_usuario_empresa IS NULL',
            (int) $usuarioEmpresaId);

        $select = $this->getAdapter()->select()
            ->from(array('a' => $this->_name), array('a.id', 'a.puesto'))
            ->joinLeft(array('aue' => 'anuncio_usuario_empresa'),
                'aue.id_anuncio = a.id', array())
            ->where($where)
            ->where('a.id_empresa =?', (int) $empresaId)
            ->where('a.online =?', self::ONLINE)
            ->where('a.fh_vencimiento >= CURDATE()');

        if (!is_null($areaId)) {
            $select->where('a.id_area =?', (int) $areaId);
        }

        return $this->getAdapter()->fetchAll($select);
    }

    public function obtenerProcesosParaAsignacion($empresaId)
    {
        return $this->getAdapter()->fetchAll($this->getAdapter()->select()
                    ->from(array('a' => $this->_name),
                        array('a.id', 'a.creado_por'))
                    ->joinInner(array('u' => 'usuario'), 'u.id = a.creado_por',
                        array())
                    ->joinInner(array('ue' => 'usuario_empresa'),
                        'ue.id_usuario = u.id',
                        array('usuario_empresa_id' => 'ue.id'))
                    ->joinLeft(array('aue' => 'anuncio_usuario_empresa'),
                        'aue.id_anuncio = a.id', array())
                    ->where('a.id_empresa =?', $empresaId)
                    ->where('a.eliminado =?', self::NO_ELIMINADO)
                    ->where('a.borrador =?', self::NO_BORRADOR)
                    ->where('aue.id_usuario_empresa IS NULL')
                    ->where('ue.creador =?',
                        Application_Model_UsuarioEmpresa::SECUNDARIO));
    }

    public function obtenerParaAdecsys($id)
    {

        return $this->getAdapter()->fetchRow($this->getAdapter()->select()
                    ->from(array('a' => $this->_name),
                        array('a.id_empresa', 'a.tipo',
                        'a.id_compra', 'a.id'))
                    ->joinInner(array('c' => 'compra'), 'a.id_compra = c.id',
                        array('c.tipo_anuncio'))
                    ->joinLeft(array('t' => 'tarifa'), 't.id = c.id_tarifa',
                        array('t.medio_pub'))
                    ->joinLeft(array('p' => 'producto'), 'p.id = t.id_producto',
                        array('producto_nombre' => 'p.desc'))
                    ->joinLeft(array('ai' => 'anuncio_impreso'),
                        'ai.id_compra = c.id',
                        array('id_impreso' => 'ai.id', 'ai.fh_pub_confirmada', 'ai.texto'))
                    ->joinLeft(array('ad' => 'anuncio_web_detalle'),
                        'ad.id_anuncio_web = a.id',
                        array(
                        'numero_palabras' => 'ad.valor'))
                    ->joinInner(array('e' => 'empresa'), 'e.id = a.id_empresa',
                        array('e.ruc', 'e.razon_social'))
                    ->joinInner(array('u' => 'usuario'), 'c.creado_por = u.id',
                        array('u.email'))
                    ->joinInner(array('ue' => 'usuario_empresa'),
                        'e.id = ue.id_empresa',
                        array('ue.nombres', 'ue.apellidos', 'ue.telefono',
                        'ue.telefono2'))
                    ->joinLeft(array('ee' => 'empresa_ente'),
                        'a.id_empresa = ee.empresa_id', array())
                    ->joinLeft(array('ae' => 'adecsys_ente'),
                        'ae.id = ee.ente_id',
                        array('id_ente' => 'ae.id', 'ente_cod'))
                    ->joinLeft(array('pto' => 'puesto'), 'a.id_puesto = pto.id',
                        array('puesto_nombre' => 'pto.nombre',
                        'puesto_tipo' => 'pto.tipo',
                        'puesto_adecsys_code' => 'adecsys_code',
                        'id_especialidad'))
                    ->where('a.id =?', $id)
                    ->where('ad.codigo =?',
                        Application_Model_AnuncioWebDetalle::CODIGO_NUMERO_PALABRAS)
        );
    }

    public function obtenerNoRegistrados()
    {
        return $this->getAdapter()->fetchAll($this->getAdapter()->select()
                    ->from(array('aw' => $this->_name),
                        array('aw.id', 'aw.id_compra'))
                    ->joinInner(array('c' => 'compra'), 'aw.id_compra = c.id',
                        array())
                    ->joinInner(array('ai' => 'anuncio_impreso'),
                        'ai.id_compra = c.id', array('ai.fh_pub_confirmada'))
                    ->joinLeft(array('aca' => 'adecsys_contingencia_aviso'),
                        'aca.id_anuncio = aw.id', array())
                    ->joinLeft(array('cac' => 'compra_adecsys_codigo'),
                        'cac.id_compra = c.id', array())
                    ->where('c.estado =?',
                        Application_Model_Compra::ESTADO_PAGADO)
                    ->where('ai.fh_pub_confirmada >= CURDATE()')
                    ->where('aca.id IS NULL')
                    ->where('cac.id IS NULL')
                    ->group('aw.id'));
    }

    public function avisoTieneCorreoOp($id)
    {
        return $this->getAdapter()->select()->from(
                $this->_name, 'correo')->where('id = ?', $id)->query()->fetchColumn();
    }

    public function AvisosActivos($limit)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('aw' => $this->_name),
                array('aviso' => 'aw.puesto', 'npostulante' => 'aw.nnuevos',
                'aw.puesto', 'aw.funciones',
                'aw.fh_pub', 'fecha_fin' => 'aw.fh_vencimiento',
                'fecha_proceso' => 'aw.fh_vencimiento_proceso',
                'aw.id',
                'fecha_baja' => 'aw.fh_aviso_baja',
                'fecha_baja' => new Zend_Db_Expr("COALESCE(aw.fh_aviso_baja,aw.fh_vencimiento)"),
                'slug' => 'aw.slug',
                'id_anuncio_web' => 'aw.url_id',
                'aw.estado',
                'aw.correo',
                'aw.creado_por'
                )
            )
            ->where('aw.online = ?', 1)
            ->where('aw.borrador = ?', 0)
            ->where('aw.estado = ?', 'pagado')
            ->where('aw.eliminado = ?', 0)
            ->order(sprintf('%s %s', 'aw.fh_pub', 'desc'));

        if ($limit != 'TODOS') $sql->limit($limit);

        return $sql->query()->fetchAll();
    }

    public function getAvisoIdByCreado($urlId)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__.'_'.$urlId;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $db     = $this->getAdapter();
        $sql    = $db->select()
            ->from(array('aw' => 'anuncio_web'), array('aw.creado_por'))
            ->where('aw.url_id = ?', $urlId)
            ->order('aw.id desc');
        $result = $this->getAdapter()->fetchOne($sql);
        if ($result == null) {
            return null;
        }
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    //Dado de baja mostrar cintillo
    public function getAvisoInfo($urlId)
    {

        $db = $this->getAdapter();

        //$whereEstado = $db->quoteInto('aw.online =? OR ', self::ONLINE);
        //$whereEstado .= $db->quoteInto('aw.cerrado =?', self::CERRADO);

        $cacheAI = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__.'_'.$urlId;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $db->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('aw.id', 'puesto' => new Zend_Db_Expr('UPPER(aw.puesto)'),
                'aw.funciones', 'aw.responsabilidades',
                'aw.slug', 'aw.id_empresa', 'aw.salario_min', 'aw.salario_max',
                'aw.url_id', 'aw.mostrar_salario', 'aw.proceso_activo',
                'aw.online', 'aw.borrador', 'aw.cerrado',
                'aw.mostrar_empresa', 'aw.fh_vencimiento',
                'aw.fh_vencimiento_proceso', 'aw.republicado', 'fh_publicacion' => new Zend_Db_Expr("DATE_FORMAT(aw.fh_pub, '%d/%m/%Y')"),
                'nombre_empresa' => 'e.nombre_comercial', 'aw.estado',
                'nombre_comercial' => new Zend_Db_Expr(
                    "CASE aw.mostrar_empresa
                        WHEN 0 THEN aw.empresa_rs
                        WHEN 1 THEN e.nombre_comercial END"
                ),
                'ciudad' => 'u.display_name',
                'e.slug_empresa',
                'area_puesto' => 'a.nombre',
                'area_puesto_slug' => 'a.slug',
                'nivel_puesto_slug' => 'np.slug',
                'redireccion' => 'aw.redireccion'
                )
            )
            ->joinLeft(
                array('e' => 'empresa'), 'aw.id_empresa = e.id',
                array('logo_empresa' => 'e.logo',
                'logo_facebook' => 'e.logo3')
            )
            ->joinLeft(
                array('u' => 'ubigeo'), 'aw.id_ubigeo = u.id',
                array('ubigeo_nombre' => 'u.nombre')
            )
            ->joinLeft(
                array('a' => 'area'), 'aw.id_area = a.id', array()
            )
            ->joinLeft(
                array('np' => 'nivel_puesto'), 'aw.id_nivel_puesto = np.id',
                array('nivel_puesto_nombre' => 'np.nombre')
            )
            ->where('aw.url_id = ?', $urlId)
            ->where('aw.eliminado = ?', self::NO_ELIMINADO)
            //->where($whereEstado)
            ->order('aw.id DESC');

        $anuncio = $this->getAdapter()->fetchRow($sql);

        if ($anuncio === false || $anuncio == null) {
            return null;
        }
        if ($anuncio['mostrar_empresa'] != 1) {
            $anuncio['logo_empresa'] = '';
        }
        $anuncio['estudios']     = $this->getEstudiosByAnuncio($anuncio['id']);
        $anuncio['experiencias'] = $this->getExperienciasByAnuncio($anuncio['id']);
        $anuncio['idiomas']      = $this->getIdiomasByAnuncio($anuncio['id']);
        $anuncio['programas']    = $this->getProgramasByAnuncio($anuncio['id']);
        $this->_cache->save($anuncio, $cacheId, array(), $cacheAI);

        return $anuncio;
    }

    //Dado de baja mostrar cintillo
    public function getAvisoInfoficha($id)
    {
        //$whereEstado = $db->quoteInto('aw.online =? OR ', self::ONLINE);
        //$whereEstado .= $db->quoteInto('aw.cerrado =?', self::CERRADO);
        $id      = (int) $id;
        $cacheAI = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__.'_'.$id;
        if ($this->_cache->test($cacheId)) {
            //return $this->_cache->load($cacheId);
        }
        /* $sql = $db->select()
          ->from(
          array('aw' => 'anuncio_web'), array('aw.id','puesto'=>new Zend_Db_Expr('UPPER(aw.puesto)'), 'aw.funciones', 'aw.responsabilidades',
          'aw.slug', 'aw.id_empresa', 'aw.salario_min', 'aw.salario_max',
          'aw.url_id', 'aw.mostrar_salario', 'aw.proceso_activo',
          'aw.online', 'aw.borrador', 'aw.cerrado',
          'aw.mostrar_empresa', 'aw.fh_vencimiento',
          'aw.fh_vencimiento_proceso', 'aw.republicado', 'fh_publicacion' => new Zend_Db_Expr("DATE_FORMAT(aw.fh_pub, '%d/%m/%Y')"),
          'nombre_empresa' => 'e.nombre_comercial', 'aw.estado',
          'nombre_comercial' => new Zend_Db_Expr(
          "CASE aw.mostrar_empresa
          WHEN 0 THEN aw.empresa_rs
          WHEN 1 THEN e.nombre_comercial END"
          ),
          'ciudad' => 'u.display_name',
          'e.slug_empresa',
          'e.slug',
          'area_puesto' => 'a.nombre',
          'area_puesto_slug' => 'a.slug',
          'nivel_puesto_slug' => 'np.slug',
          'redireccion' => 'aw.redireccion'
          )
          )
          ->joinLeft(
          array('e' => 'empresa'), 'aw.id_empresa = e.id', array('logo_empresa' => 'e.logo',
          'logo_facebook' => 'e.logo3')
          )
          ->joinLeft(
          array('u' => 'ubigeo'), 'aw.id_ubigeo = u.id', array('ubigeo_nombre' => 'u.nombre')
          )
          ->joinLeft(
          array('a' => 'area'), 'aw.id_area = a.id', array()
          )
          ->joinLeft(
          array('np' => 'nivel_puesto'), 'aw.id_nivel_puesto = np.id', array('nivel_puesto_nombre' => 'np.nombre')
          )
          ->where('aw.url_id = ?', $urlId)
          ->order('aw.online desc');

          $anuncio = $this->getAdapter()->fetchRow($sql); */
        $anuncio = $this->getAvisoInfofichaAnuncio($id);
        if ($anuncio === false || $anuncio == null) {
            return null;
        }
        if ($anuncio['mostrar_empresa'] != 1) {
            $anuncio['logo_empresa'] = '';
        }
        $anuncio['estudios']     = $this->getEstudiosByAnuncio($anuncio['id']);
        $anuncio['experiencias'] = $this->getExperienciasByAnuncio($anuncio['id']);
        $anuncio['idiomas']      = $this->getIdiomasByAnuncio($anuncio['id']);
        $anuncio['programas']    = $this->getProgramasByAnuncio($anuncio['id']);
        $this->_cache->save($anuncio, $cacheId, array(), $cacheAI);
        return $anuncio;
    }

    public function numAvisos()
    {

        return $this->getAdapter()->select()
                ->from($this->_name, 'count(1)')
                ->where('fh_pub != ?', '')
                ->where('estado in (?)',
                    array('pagado', 'publicado',
                    'dado_baja', 'vencido', 'extendido'))
                ->query()->fetchColumn();
    }

    //Servicio que provee APTiTUS a BuscaMas para enviar la información del aviso
    public function servicioRestBuscaMas($nid)
    {

        $sql = $this->getAdapter()->select()->from(array("a" => $this->_name)
                    ,
                    array('source_id' => 'a.id',
                    'ad_url' => new Zend_Db_Expr("CONCAT('".SITE_URL."/ofertas-de-trabajo/',a.slug,'-',a.url_id)"),
                    'title' => 'a.puesto', 'business_name' => new Zend_Db_Expr(
                        "CASE a.mostrar_empresa
                        WHEN 0 THEN TRIM(REPLACE(REPLACE(a.empresa_rs ,'.',''),'  ',''))
                        WHEN 1 THEN TRIM(REPLACE(REPLACE(e.nombre_comercial,'  ',' ') ,'.',''))  END"
                    ),
                    'description' => new Zend_Db_Expr("IF(CONCAT(a.funciones,a.responsabilidades) = '',' ',CONCAT(a.funciones,' ',a.responsabilidades)) "),
                    'body' => new Zend_Db_Expr("IF(CONCAT(a.funciones,a.responsabilidades) = '',' ',CONCAT(a.funciones,' ',a.responsabilidades,' ',IFNULL(GROUP_CONCAT(DISTINCT ae.otra_carrera),''),"
                        ."' ',IFNULL(GROUP_CONCAT(DISTINCT cac.adecsys_code),''))) "),
                    'publication_date' => new Zend_Db_Expr("IFNULL(DATE_FORMAT(a.fh_pub, '%Y%m%dT%H%i%s'),' ')"),
                    'a.url_id',
                    'logo' => new Zend_Db_Expr("(CASE a.mostrar_empresa WHEN 0 THEN ' ' WHEN 1 THEN IF(a.logo IS NULL OR a.logo = '',' ',a.logo) END)"),
                    'area' => new Zend_Db_Expr("ifnull(ar.nombre,' ')"), 'area_slug' => new Zend_Db_Expr("ifnull(ar.slug,' ')"),
                    'level' => 'np.nombre', 'level_slug' => 'np.slug', 'location' => 'u.nombre',
                    'location_slug' => new Zend_Db_Expr("REPLACE(u.search_name,' ','-')"),
                    'featured' => 'a.prioridad',
                    'price' => new Zend_Db_Expr("if(a.salario_min is null,'0',
                    IF(a.mostrar_salario = 0,0,CAST(IF(a.salario_max = 750 or a.salario_max = 600,1,a.salario_min) AS DECIMAL(10,2))))"),
                    'price2' => new Zend_Db_Expr("if(a.salario_max is null or a.salario_max = '',
                                IF ((a.salario_min = 10001 OR a.salario_min = 9001) AND a.mostrar_salario = 1,15000.00,0),
                    IF(a.mostrar_salario = 0,0,CAST(a.salario_max AS DECIMAL(10,2))))"),
                    'code_adecsys' => new Zend_Db_Expr("IF(a.adecsys_code IS NULL OR a.adecsys_code = '',IF(cac.adecsys_code IS NULL OR cac.adecsys_code = '',' ',cac.`adecsys_code`),a.adecsys_code)"),
                    'destacado' => new Zend_Db_Expr("(
                                CASE WHEN (a.tipo = 'soloweb') THEN 1
                                WHEN (a.tipo = 'clasificado') THEN 2
                                WHEN (a.tipo = 'preferencial') THEN 3
                                WHEN (a.tipo = 'destacado') THEN 4
                                END)"),
                    'pub_days' => new Zend_Db_Expr('IFNULL(DATEDIFF(CURRENT_TIMESTAMP,a.fh_pub),0)'),
                    'active' => new Zend_Db_Expr("IF(a.estado = 'pagado' and a.online = 1 and a.cerrado = 0,1,0)"),
                    //Carrera nuevo filtro Búsqueda avanzada
                    'carrera' => new Zend_Db_Expr("GROUP_CONCAT(DISTINCT c.slug)")))
                ->joinInner(array('e' => 'empresa'), 'e.id = a.id_empresa', null)
                ->joinLeft(array('ar' => 'area'), 'ar.id = a.id_area', null)
                ->joinInner(array('np' => 'nivel_puesto'),
                    'np.id = a.id_nivel_puesto', null)
                ->joinInner(array('u' => 'ubigeo'), 'u.id = a.id_ubigeo', null)
                ->joinLeft(array('em' => 'empresa_membresia'),
                    'em.id_empresa = a.id_empresa', null)
                ->joinLeft(array('cac' => 'compra_adecsys_codigo'),
                    'cac.id_compra = a.id_compra', null)
                ->joinInner(array('ae' => 'anuncio_estudio'),
                    'ae.id_anuncio_web = a.id', null)
                ->joinLeft(array('c' => 'carrera'), 'c.id = ae.id_carrera', null)
                ->where('a.id = ?', $nid)->limit(1);

        //echo $sql;

        return $this->getAdapter()->fetchRow($sql);
    }

    //Cron que genera los avisos que se encuentran activos en APTiTUS e indexa
    // la información a BuscaMas
    public function servicioRestBuscaMasIndexar()
    {

        $sql = $this->getAdapter()->select()->distinct()->from(array("a" => $this->_name),
                array('source_id' => 'a.id')
            )
            ->joinInner(array('e' => 'empresa'), 'e.id = a.id_empresa', null)
            ->joinInner(array('ar' => 'area'), 'ar.id = a.id_area', null)
            ->joinInner(array('np' => 'nivel_puesto'),
                'np.id = a.id_nivel_puesto', null)
            ->joinInner(array('u' => 'ubigeo'), 'u.id = a.id_ubigeo', null)
            ->joinLeft(array('em' => 'empresa_membresia'),
                'em.id_empresa = a.id_empresa', null)
            ->joinLeft(array('cac' => 'compra_adecsys_codigo'),
                'cac.id_compra = a.id_compra', null)
            ->where('a.online = ?', 1)
            ->where('a.estado = ?', self::ESTADO_PAGADO)
            ->where('a.borrador = ?', 0)
            ->where('a.eliminado = ?', 0)
            ->where('a.cerrado = ?', 0)
            ->where('a.buscamas = ?', 1)
            ->order('a.id desc');

        $data = $this->getAdapter()->fetchAll($sql);

        return $data;
    }

    public function listaAreaPorNumAvisos($data)
    {

        $sql = $this->getAdapter()->select()
            ->from(
                array('area' => 'area'),
                array(
                'ind' => 'id',
                'cant' => 'contador_anuncios',
                'slug' => 'slug',
                'msg' => 'nombre',
                )
            )
            ->where('contador_anuncios > 0');

        $sql = $sql->order('contador_anuncios DESC');

        $result = $this->getAdapter()->fetchAssoc($sql);

        return $result;
    }

    public function avisosActualizarPrioridadBuscamMas()
    {

        $sql = $this->getAdapter()->select()->from($this->_name, 'id')
                ->where('online = ?', self::ONLINE)
                ->where('fh_vencimiento_prioridad < NOW()')
                ->where('prioridad_ndias_busqueda > ?', 0)
                ->where('prioridad < ?', 6)->query()->fetchAll();

        return $sql;

        //return $this->fetchAll($sql);
    }

    //Contador de Ubicacion Portada
    public function getGroupUbiPortadaDistrito($padre, $ubigeo)
    {
        //Generar array con distritos
        $arrayUbigeo = array();

        foreach ($ubigeo as $item) {

            $slug = str_replace('-', '', $item['slug']);

            $sql = $this->getAdapter()->select()
                ->from('ubigeo', 'nombre')
                ->where('index_name like ?', '%'.$slug.'%')
                ->where('padre = ?', $padre)
                ->where('level = ?', 3)
                ->where('contador_anuncios > ?', 0);

            $dataUbigeo = $this->getAdapter()->fetchRow($sql);

            if ($dataUbigeo['nombre'] != '') {
                $arrayUbigeo[$slug]   = $dataUbigeo['nombre']." (".$item['count'].")";
                $dataUbigeo['nombre'] = '';
            }
        }

        return $arrayUbigeo;
    }

    //Contador de Ubicacion Portada
    public function getGroupUbiPortadaDepartamento($padre, $ubigeo)
    {
        //Generar array con departamentos
        $arrayUbigeo = array();

        foreach ($ubigeo as $item) {

            $slug = str_replace('-', '', $item['slug']);

            $sql = $this->getAdapter()->select()
                ->from('ubigeo', 'nombre')
                ->where('index_name like ?', '%'.$slug.'%')
                ->where('padre = ?', $padre)
                ->where('level = ?', 1)
                ->where('contador_anuncios > ?', 0);

            if ($slug == 'ica') $sql->where('id = ?', 3606);

            $dataUbigeo = $this->getAdapter()->fetchRow($sql);

            if ($dataUbigeo['nombre'] != '') {
                $arrayUbigeo[$slug]   = $dataUbigeo['nombre']." (".$item['count'].")";
                $dataUbigeo['nombre'] = '';
            }
        }

        return $arrayUbigeo;
    }

    /**
     * Retorna una lista los departamentos del Perú en dos campos id y nombre
     * @return array fetchPairs 
     */
    public function getDepartamentos()
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->getAdapter()->select()
            ->from(
                array('u' => 'ubigeo'), array('slug_ubigeo', 'nombre')
            )
            ->where('u.padre = ? ', Application_Model_Ubigeo::PERU_UBIGEO_ID)
            ->order('nombre');
        $rs  = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
            $rs, $cacheId, array(),
            $this->_config->cache->Ubigeo->getDepartamentos
        );

        return $rs;
    }

    public function getGroupUbiPortadaPais($ubigeo)
    {
        $arrayPais = array();

        foreach ($ubigeo as $item) {

            $slug = str_replace('-', '', $item['slug']);

            $sql = $this->getAdapter()->select()
                ->from('ubigeo', 'nombre')
                ->where('index_name like ?', '%'.$slug.'%')
                ->where('padre is null')
                ->where('contador_anuncios > ?', 0)
                ->where('id <> ?', Application_Model_Ubigeo::PERU_UBIGEO_ID)
                ->where('level = ?', 0);

            $dataUbigeo = $this->getAdapter()->fetchRow($sql);

            if ($dataUbigeo['nombre'] != '' && $slug != 'ica') {
                $arrayPais[$slug]     = $dataUbigeo['nombre']." (".$item['count'].")";
                $dataUbigeo['nombre'] = '';
            }
        }

        return $arrayPais;
    }

    public function getAvisosJobs()
    {
//        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
//        $cacheId = $this->_model . '_' . __FUNCTION__;
//        if ($this->_cache->test($cacheId)) {
//            return $this->_cache->load($cacheId);
//        }
        $sql    = $this->getAdapter()->select()
            ->from(array('aw' => 'anuncio_web'),
                array(
                'url_aviso' => 'aw.url_id',
                'id_anuncio_web' => 'aw.id',
                'ubicacion' => 'u.display_name',
                'puesto' => 'aw.puesto',
                'slugaviso' => 'aw.slug',
                'description' => new Zend_Db_Expr("CONCAT(CONCAT(aw.funciones, ' '), CONCAT('', aw.responsabilidades))"),
                'urlaviso' => 'aw.url_id',
                'fh_pub' => 'aw.fh_pub',
                'fh_ven' => 'aw.fh_vencimiento',
                'area' => 'a.nombre'
            ))
            ->joinleft(
                array('u' => 'ubigeo'), 'aw.id_ubigeo = u.id'
            )
            ->joinleft(
                array('a' => 'area'), 'aw.id_area = a.id'
            )
            ->where('aw.online = ?', 1)
            ->where('aw.borrador = ?', 0)
            ->where('aw.estado = ?', 'pagado')
            ->where('aw.eliminado = ?', 0)
            ->order('aw.id DESC')
            ->limit(200);
        $result = $this->getAdapter()->fetchAll($sql);
//        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function obtenerLogo($idAviso)
    {
        $adapter = $this->getAdapter();
        $sql     = $adapter->select()->from(array('e' => 'empresa'), 'logo2')
            ->joinInner(array('a' => 'anuncio_web'), 'a.id_empresa = e.id', null)
            ->where('a.id = ?', $idAviso)
            ->where('a.mostrar_empresa = ?', 1);
        $stm     = $adapter->query($sql);
        $data    = $stm->fetchColumn();


        if ($data != null && count($data) >= 1) {
            return $data;
        }
        return '';
    }

    /**
     * Obtiene los avisos activos que se han actualizado por BD y se
     * deben indexar la info a buscamas.
     * @return type Description
     */
    public function avisosActualizadoBDBuscamas()
    {

        $sql = $this->getAdapter()->select()->from($this->_name, 'id')
                ->where('buscamas = ?', 0)
                ->where('online = ?', 1)
                ->where('estado = ?', self::ESTADO_PAGADO)
                ->query()->fetchAll();

        return $sql;
    }

    public function avisosIndexarNew()
    {
        $sql = $this->getAdapter()->select()->from($this->_name, 'id')
            ->where('fh_pub >= ?', '2014-01-20')
            ->where('fh_pub <= ?', '2014-01-22')
            ->where('online = ?', 1)
            ->where('estado = ?', 'pagado')
            ->order('id desc');

        return $this->getAdapter()->fetchAll($sql);
    }

    public function avisosPorIdCompra($idCompra)
    {

        $sql = $this->getAdapter()->select()->from($this->_name, 'id')
                ->where('id_compra = ?', $idCompra)
                ->where('online = ?', 1)
                ->where('estado = ?', 'pagado')
                ->query()->fetchAll();

        return $sql;
    }

    public function obtenerAvisosActivosEmpresa($idEmpresa)
    {

        $sql = $this->getAdapter()->select()->from($this->_name,
                    array('id', 'url_id'))
                ->where('estado = ?', 'pagado')
                ->where('online = ?', 1)
                ->where('cerrado = ?', 0)
                ->where('id_empresa = ?', $idEmpresa)
                ->order('id desc')
                ->query()->fetchAll();

        return $sql;
    }

    public function prioridadEmpresaAvisoDestacado($idEmp)
    {

        $prioridad = 0;
        $dias      = 0;

        $db  = $this->getAdapter();
        $sql = $this->getAdapter()->select()->from(array('m' => 'membresia'),
                    array('prioridad' => new Zend_Db_Expr("(CASE m.nombre WHEN 'Esencial' THEN 3
	WHEN 'Selecto' THEN 2
        WHEN 'Digital' THEN 2
        WHEN 'Mensual' THEN 9
	WHEN 'Premium' THEN 1
		 END)"),
                    'dias' => new Zend_Db_Expr("(CASE m.nombre WHEN 'Esencial' THEN 4
	WHEN 'Selecto' THEN 7
        WHEN 'Digital' THEN 7
        WHEN 'Mensual' THEN 0
	WHEN 'Premium' THEN 14
		 END)"), null))
                ->joinInner(array('em' => 'empresa_membresia'),
                    'em.id_membresia = m.id', null)
                ->where('em.id_empresa = ?', $idEmp)
                ->where('em.estado = ?', 'vigente')->limit(1);


        $data = $db->fetchRow($sql);

        //Si la empresa no tiene membresia
        if (is_null($data) || empty($data)) {
            $data = array('prioridad' => 9, 'dias' => 0);
        }

        return $data;
    }

    //Obtiene la prioridad de un aviso destacado, preferencial y clasificados
    public function prioridadAviso($databeneficio)
    {
        if (isset($databeneficio['destaque'])) {
            return isset($databeneficio['destaque']['valor']) ? $databeneficio['destaque']['valor']
                    : 6;
        }
        //Retorna los días de prioridad y prioridad del aviso
        return 6;
    }

    //obtine la prioridad de un aviso destacado
    public function DiasprioridadAviso($databeneficio)
    {
        if (isset($databeneficio['ndias'])) {
            return isset($databeneficio['ndias']['valor']) ? $databeneficio['ndias']['valor']
                    : 0;
        }
        //Retorna los días de prioridad y prioridad del aviso
        return 0;
    }

    //Utilización de filtros de CVS para avisos web destacado
    public function webDestacadoBeneficioCVS($idEmp)
    {

        $sql = $this->getAdapter()->select()->from($this->_name)
            ->where('id_empresa = ?', $idEmp)
            ->where('fh_vencimiento_proceso > ?', date('Y-m-d'))
            ->where('proceso_activo = ?', self::PROCESO_ACTIVO);

        return $this->getAdapter()->fetchAll($sql);
    }

    public function avisosExtendidosDestacados($idAviso, $extendido)
    {

        $sql = $this->getAdapter()->select()->from($this->_name, 'id')
            ->where('estado = ?', 'pagado')
            ->where('online = ?', 1)
            ->where('id <> ?', $idAviso)
            ->where('extiende_a = ?', $extendido)
            ->order('id desc');
        return $this->getAdapter()->fetchAll($sql);
    }

    public function obtenerIdAviso($urlId)
    {

        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from('anuncio_web', array('id', 'url_id', 'slug'))
            ->where('url_id = ?', $urlId)
            ->where('online = ?', 1)
            ->where('estado = ?', self::ESTADO_PAGADO)
            ->order('id desc')
            ->limit(1);

        return $this->getAdapter()->fetchRow($sql);
    }

    public function getAvisoByUrl($urlId)
    {

        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from('anuncio_web')
            ->where('url_id = ?', $urlId)
            ->where('online = ?', 1)
            ->where('estado = ?', self::ESTADO_PAGADO)
            ->order('id desc')
            ->limit(1);

        return $this->getAdapter()->fetchRow($sql);
    }

    /**
     * Verifica si un aviso ya pasó los 5 días que tiene de búsqueda de postulantes en la BD
     *
     * Función retorna true si ya no pasó los 5 días de vigencia sino devuelve false
     * @param int $id
     * @return boolean
     */
    public function vigenciaAvisoPreferencial($idAviso)
    {

        $sql     = $this->getAdapter();
        $diasPub = $sql->select()->from($this->_name,
                    new Zend_Db_Expr('DATEDIFF(CURRENT_TIMESTAMP,fh_pub)'))
                ->where('id = ?', $idAviso)->query()->fetchColumn();

        $diasVigencia = $this->_config->busquedaAptitusPreferencial->vigencia->dias;

        if ($diasPub <= $diasVigencia) return true;
        else return false;
    }

    /**
     * Verifica el aviso preferencial con la fecha de publicación mayor
     *
     * Función retorna true si ya no pasó los 5 días de vigencia sino devuelve false
     * @param int $id
     * @return boolean
     */
    public function vigenciaAllAvisoPreferencial($idEmpresa)
    {

        $sql     = $this->getAdapter();
        $diasPub = $sql->select()->from($this->_name,
                    new Zend_Db_Expr('DATEDIFF(CURRENT_TIMESTAMP,max(fh_pub))'))
                ->where('estado = ?', self::ESTADO_PAGADO)
                ->where('online = ?', self::ONLINE)
                ->where('tipo = ?', self::TIPO_PREFERENCIAL)
                ->where('id_empresa = ?', $idEmpresa)->query()->fetchColumn();

        $diasVigencia = $this->_config->busquedaAptitusPreferencial->vigencia->dias;

        if (empty($diasPub)) return false;

        if ($diasPub <= $diasVigencia) return true;
        else return false;
    }

    public function obtenerParaAdecsysReproceso($id)
    {
        $sql = $this->getAdapter()->select()
                ->from(array('a' => $this->_name),
                    array('a.id_empresa', 'a.tipo',
                    'a.id_compra', 'a.id'))
                ->joinInner(array('c' => 'compra'), 'a.id_compra = c.id',
                    array('c.tipo_anuncio'))
                ->joinLeft(array('t' => 'tarifa'), 't.id = c.id_tarifa',
                    array('t.medio_pub'))
                ->joinLeft(array('p' => 'producto'), 'p.id = t.id_producto',
                    array('producto_nombre' => 'p.desc'))
                ->joinLeft(array('ai' => 'anuncio_impreso'),
                    'ai.id_compra = c.id',
                    array('id_impreso' => 'ai.id', 'ai.fh_pub_confirmada', 'ai.texto'))
                ->joinLeft(array('ad' => 'anuncio_web_detalle'),
                    'ad.id_anuncio_web = a.id',
                    array(
                    'numero_palabras' => 'ad.valor'))
                ->joinInner(array('e' => 'empresa'), 'e.id = a.id_empresa',
                    array('e.ruc', 'e.razon_social'))
                ->joinInner(array('u' => 'usuario'), 'c.creado_por = u.id',
                    array('u.email'))
                ->joinInner(array('ue' => 'usuario_empresa'),
                    'e.id = ue.id_empresa',
                    array('ue.nombres', 'ue.apellidos', 'ue.telefono',
                    'ue.telefono2'))
                ->joinLeft(array('ee' => 'empresa_ente'),
                    'a.id_empresa = ee.empresa_id', array())
                ->joinLeft(array('ae' => 'adecsys_ente'),
                    'ae.doc_numero = e.ruc',
                    array('id_ente' => 'ae.id', 'ente_cod'))
                ->joinLeft(array('pto' => 'puesto'), 'a.id_puesto = pto.id',
                    array('puesto_nombre' => 'pto.nombre',
                    'puesto_tipo' => 'pto.tipo',
                    'puesto_adecsys_code' => 'adecsys_code',
                    'id_especialidad'))
                ->where('a.id =?', $id)
                ->where('ad.codigo =?',
                    Application_Model_AnuncioWebDetalle::CODIGO_NUMERO_PALABRAS)->limit(1);

        //echo $sql;exit;
        return $this->getAdapter()->fetchRow($this->getAdapter()->select()
                    ->from(array('a' => $this->_name),
                        array('a.id_empresa', 'a.tipo',
                        'a.id_compra', 'a.id'))
                    ->joinInner(array('c' => 'compra'), 'a.id_compra = c.id',
                        array('c.tipo_anuncio'))
                    ->joinLeft(array('t' => 'tarifa'), 't.id = c.id_tarifa',
                        array('t.medio_pub'))
                    ->joinLeft(array('p' => 'producto'), 'p.id = t.id_producto',
                        array('producto_nombre' => 'p.desc'))
                    ->joinLeft(array('ai' => 'anuncio_impreso'),
                        'ai.id_compra = c.id',
                        array('id_impreso' => 'ai.id', 'ai.fh_pub_confirmada', 'ai.texto'))
                    ->joinLeft(array('ad' => 'anuncio_web_detalle'),
                        'ad.id_anuncio_web = a.id',
                        array(
                        'numero_palabras' => 'ad.valor'))
                    ->joinInner(array('e' => 'empresa'), 'e.id = a.id_empresa',
                        array('e.ruc', 'e.razon_social'))
                    ->joinInner(array('u' => 'usuario'), 'c.creado_por = u.id',
                        array('u.email'))
                    ->joinInner(array('ue' => 'usuario_empresa'),
                        'e.id = ue.id_empresa',
                        array('ue.nombres', 'ue.apellidos', 'ue.telefono',
                        'ue.telefono2'))
                    ->joinLeft(array('ee' => 'empresa_ente'),
                        'a.id_empresa = ee.empresa_id', array())
                    ->joinLeft(array('ae' => 'adecsys_ente'),
                        'ae.doc_numero = e.ruc',
                        array('id_ente' => 'ae.id', 'ente_cod'))
                    ->joinLeft(array('pto' => 'puesto'), 'a.id_puesto = pto.id',
                        array('puesto_nombre' => 'pto.nombre',
                        'puesto_tipo' => 'pto.tipo',
                        'puesto_adecsys_code' => 'adecsys_code',
                        'id_especialidad'))
                    ->where('a.id =?', $id)
                    ->where('ad.codigo =?',
                        Application_Model_AnuncioWebDetalle::CODIGO_NUMERO_PALABRAS)->limit(1)
        );
    }

    public function getOtroEstudioInfoByAnuncio($anuncioId)
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('ae' => 'anuncio_estudio'),
                array('ae.id', 'ae.id_nivel_estudio', 'ae.id_carrera', 'otra_carrera' => 'ae.otra_carrera',
                'ae.id_nivel_estudio', 'ae.id_nivel_estudio_tipo')
            )
            ->where('ae.id_nivel_estudio = 9')
            ->where('ae.id_anuncio_web = ?', $anuncioId);
        //echo $sql->assemble();
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * Revisar si el aviso web tiene estudios registrados.
     *
     * @param $anuncioId    ID del Anuncio Web
     * @return boolean
     *
     */
    public function tieneAnuncioEstudios($anuncioId)
    {

        $mAnuncioEstudio = new Application_Model_AnuncioEstudio();
        $existe          = $mAnuncioEstudio->getAnuncioEstudioByIdAnuncioWeb($anuncioId);
        return ($existe ? true : false);
    }

    /**
     * Revisar si el aviso web tiene experiencia registrados.
     *
     * @param $anuncioId    ID del Anuncio Web
     * @return boolean
     *
     */
    public function tieneAnuncioExperiencia($anuncioId)
    {
        $mAnuncioExperiencia = new Application_Model_AnuncioExperiencia();
        $existe              = $mAnuncioExperiencia->getAnuncioExperienciaByIdAnuncioWeb($anuncioId);
        return ($existe ? true : false);
    }

    public function obtenerNombreAviso($urlId)
    {

        $sql = $this->getAdapter()->select()->from($this->_name)
            ->where('online = ?', self::ONLINE)
            ->where('estado = ?', self::ESTADO_PAGADO)
            ->where('url_id = ?', $urlId);

        return $this->getAdapter()->fetchRow($sql);
    }
    /*
     * Super funcion para detectar porcentaje de coincidencia
     * $aId = ID DEL AVISO
     * $pId = ID POSTULANTE
     * Author: Antonio
     */

    public function porcentajeCoincidencia($aId, $pId)
    {
        $config          = Zend_Registry::get("config");
        $pesoEstudios    = $config->empresa->coincidencia->estudios->peso;
        $pesoExperiencia = $config->empresa->coincidencia->experiencia->peso;
        $pesoIdiomas     = $config->empresa->coincidencia->idiomas->peso;
        $pesoProgramas   = $config->empresa->coincidencia->programas->peso;

        $adapter = $this->getAdapter();
        $aId     = $adapter->quote($aId);
        $pId     = $adapter->quote($pId);
        //@codingStandardsIgnoreStart

        /* Estudios */
        $sqlE = "SELECT * FROM estudio WHERE id_nivel_estudio <> 9 AND id_postulante = $pId";
        $stmp = $adapter->query($sqlE);
        $stmp->execute();
        $resE = $stmp->fetchAll();
        if (empty($resE)) {
            $pjeEstudio = 0;
        } else {
            $sqlAE      = "SELECT * FROM anuncio_estudio WHERE id_nivel_estudio <> 9 AND id_anuncio_web = $aId";
            $stmp       = $adapter->query($sqlAE);
            $stmp->execute();
            $resAE      = $stmp->fetchAll();
            $pjeEstudio = $this->compararEstudio($resE, $resAE);
        }
        /* Experiencia */
        $sqlEx = "SELECT * FROM experiencia WHERE id_postulante = $pId";
        $stmp  = $adapter->query($sqlEx);
        $stmp->execute();
        $resEx = $stmp->fetchAll();
        if (empty($resEx)) {
            $pjeExperiencia = 0;
        } else {
            $sqlAEx         = "SELECT * FROM anuncio_experiencia WHERE id_anuncio_web = $aId";
            $stmp           = $adapter->query($sqlAEx);
            $stmp->execute();
            $resAEx         = $stmp->fetchAll();
            if (!empty($resAEx))
                    $pjeExperiencia = $this->compararExperienciaEval($resEx,
                        $resAEx, 'Exp') / count($resAEx);
            else $pjeExperiencia = 0;
        }


        /* Idioma */
        $sqlAI = "SELECT * FROM anuncio_idioma WHERE id_anuncio_web = $aId";
        $stmp  = $adapter->query($sqlAI);
        $stmp->execute();
        $resAI = $stmp->fetchAll();
        if (!empty($resAI)) {
            $sqlI      = "SELECT * FROM dominio_idioma WHERE id_postulante = $pId";
            $stmp      = $adapter->query($sqlI);
            $stmp->execute();
            $resI      = $stmp->fetchAll();
            $pjeIdioma = $this->compararExperienciaEval($resI, $resAI, 'Idi') / count($resAI);
        } else {
            $pjeIdioma   = 0;
            $pesoIdiomas = 0;
        }
        /* Programa */
        $sqlAP = "SELECT * FROM anuncio_programa_computo WHERE id_anuncio_web = $aId";
        $stmp  = $adapter->query($sqlAP);
        $stmp->execute();
        $resAP = $stmp->fetchAll();
        if (!empty($resAP)) {
            $sqlP        = "SELECT * FROM dominio_programa_computo WHERE id_postulante = $pId";
            $stmp        = $adapter->query($sqlP);
            $stmp->execute();
            $resP        = $stmp->fetchAll();
            $pjePrograma = $this->compararExperienciaEval($resP, $resAP, 'Pro') / count($resAP);
        } else {
            $pjePrograma   = 0;
            $pesoProgramas = 0;
        }


        $match = round(($pesoEstudios * $pjeEstudio + $pesoExperiencia * $pjeExperiencia
            + $pesoIdiomas * $pjeIdioma + $pesoProgramas * $pjePrograma) / ($pesoEstudios
            + $pesoExperiencia + $pesoIdiomas + $pesoProgramas));
        return array(0 => array('aptitus_match' => $match));
    }

    public function compararEstudio($resE, $resAE)
    {
        if (empty($resAE) || empty($resE)) return 0;
        $pjeEstudio = 0;
        foreach ($resE as $re) {
            foreach ($resAE as $rae) {
                $pjeEst     = $this->compararEst($re, $rae);
                if ($pjeEst > $pjeEstudio) $pjeEstudio = $pjeEst;
                if ($pjeEst == 100) break;
            }
        }
        return $pjeEstudio;
    }

    public function compararEst($re, $rae)
    {
        $grupos = array(
            14 => 3, 15 => 2, 16 => 1, 17 => 1, 18 => 3,
            19 => 2, 20 => 1, 21 => 1, 22 => 1
        );
        if (in_array($rae['id_nivel_estudio'], array(1, 2, 3))) {
            if ($re['id_nivel_estudio'] == $rae['id_nivel_estudio']) $pje = 100;
            else $pje = 0;
        }
        else {
            if ($re['id_carrera'] == $rae['id_carrera']) {
                if ($re['id_nivel_estudio'] == $rae['id_nivel_estudio'])
                        $pje  = 60;
                else $pje  = 10;
                $gre  = $grupos[$re['id_nivel_estudio_tipo']];
                $grae = $grupos[$rae['id_nivel_estudio_tipo']];
                if ($gre >= $grae) $pje  = $pje + 40;
                else $pje  = $pje + round($gre * 40 / $grae);
            }
            else {
                $pje = 0;
            }
        }
        return $pje;
    }

    public function compararExperiencia($resEx, $resAEx, $tip)
    {
        if (empty($resAEx) || empty($resEx)) return 0;
        $pjeExperiencia = 0;
        $a              = 0;
        $b              = 0;
        foreach ($resAEx as $i => $raex) {
            foreach ($resEx as $j => $rex) {
                $fun    = "comparar$tip";
                $pjeExp = $this->$fun($rex, $raex);
                if ($pjeExp > $pjeExperiencia) {
                    $pjeExperiencia = $pjeExp;
                    $a              = $i;
                    $b              = $j;
                }
            }
        }
        unset($resAEx[$a]);
        unset($resEx[$b]);
        return $pjeExperiencia + $this->compararExperiencia($resEx, $resAEx,
                $tip);
    }

    public function compararExperienciaEval($resEx, $resAEx, $tip)
    {
        if (empty($resAEx) || empty($resEx)) return 0;
        $pjeExperiencia = 0;
        $total          = 0;
        $totalpj        = 0;
        foreach ($resAEx as $i => $raex) {
            foreach ($resEx as $j => $rex) {
                $fun    = "comparar$tip";
                $pjeExp = $this->$fun($rex, $raex);

//              totalpj=0;

                if ($pjeExp >= $pjeExperiencia) {

                    $pjeExperiencia = $pjeExp;
                    $totalpj        = $pjeExp;
                }


                //echo $pjeExperiencia."<br>-----a";
            }
            $pjeExperiencia = 0;
            $total+= $totalpj;
        }

        return $total;
    }

    public function compararExp($rex, $raex)
    {
        $pje = 0;
        $div = 1;
        if ($rex['id_area'] == $raex['id_area']) {
            if ($rex['id_nivel_puesto'] == $raex['id_nivel_puesto'])
                    $pje = $pje + 20;
            else $div = 6;
            if ($rex['id_area'] == $raex['id_area']) $pje = $pje + 30;
            else $div = 6;


            if (empty($raex['experiencia'])) {
                $pje = $pje + 50 / $div;
            } else {
                $ia = $rex['inicio_ano'];
                $im = str_pad($rex['inicio_mes'], 2, '0', STR_PAD_LEFT);
                $d1 = new DateTime("$ia-$im-01");
                if (empty($rex['en_curso'])) {
                    $fa = $rex['fin_ano'];
                    $fm = str_pad($rex['fin_mes'], 2, '0', STR_PAD_LEFT);
//                    $mes = mktime( 0, 0, 0, $fm, 1, $fa );
//                    $dfin= date("t",$mes);
                    $d2 = new DateTime("$fa-$fm-01");
                } else {
                    $d2 = new DateTime("now");
                }


                $interval = $d2->diff($d1);
                $tiempo   = ($interval->m) + ($interval->y * 12 );


                if ($tiempo >= $raex['experiencia'])
                        $tiempo = $raex['experiencia'];
                $pje    = $pje + (50 * $tiempo) / ($div * $raex['experiencia']);
            }
        }


        return $pje;
    }

    public function compararIdi($rex, $raex)
    {
        $pje = 0;
        if ($rex['id_idioma'] == $raex['id_idioma']) {
            $nivel = array(
                'avanzado' => 3,
                'intermedio' => 2,
                'basico' => 1
            );
            $nre   = $nivel[$rex['nivel_lee']];
            $nrae  = $nivel[$raex['nivel']];
            if ($nre >= $nrae) $pje   = 100;
            else $pje   = 0;
        }
        return $pje;
    }

    public function compararPro($rex, $raex)
    {
        $pje = 0;
        if ($rex['id_programa_computo'] == $raex['id_programa_computo']) {
            $nivel = array(
                'avanzado' => 3,
                'intermedio' => 2,
                'basico' => 1
            );
            $nre   = $nivel[$rex['nivel']];
            $nrae  = $nivel[$raex['nivel']];
            if ($nre >= $nrae) $pje   = 100;
            else $pje   = 0;
        }
        return $pje;
    }

    public function getAvisoParaEstadisticas($idAviso)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name,
                array(
                'fh_pub',
                'fh_vencimiento', 'url_id'
            ))
            ->where('id = ?', $idAviso)
            ->group('id');
        return $this->getAdapter()->fetchRow($sql);
    }
    /*
     * Obtiene el número de avisos que se encuentran en el home
     * return Boolean
     * true si tiene menos de 3, y false si tiene 3 o más
     */

    public function obtieneNumAvisosHome()
    {
        $numDestacados = $this->_config->avisosportada->destacados;

        $sql = $this->getAdapter()->select()
            ->from($this->_name,
                array(
                'num' => new Zend_Db_Expr('count(1)'),
            ))
            ->where('destacado = ?', self::DESTACADO)
            ->where('estado = ?', self::ESTADO_PAGADO)
            ->where('online = ?', self::ONLINE);

        $num = $this->getAdapter()->fetchOne($sql);

        if ($numDestacados <= $num) {
            return false;
        } else {
            return true;
        }
    }

    public function getByIdCompra($id)
    {
        $result = $this->fetchAll($this->select()
                ->from($this->_name)
                ->where('id_compra =?', $id));
        if (!empty($result)) return $result->toArray();
        else return array();
    }
    /*
     * Obtiene los avisos gratuitos con membresia con el estado pendiente_pago
     * y que en compra estan con el estado de pagado.
     *
     * @access public
     * @return array o false
     */

    public function getAvisosGratuitosPendientePago()
    {
        $sql = $this->getAdapter()->select()
            ->from(array(
                'a' => $this->_name
            ))
            ->joinLeft(array('c' => 'compra'), 'c.id =  a.id_compra',
                array(
                'estado_compra' => 'estado',
                'tipoAnuncio' => 'tipo_anuncio',
            ))
            ->where('a.online = ?', 1)
            ->where('a.id_tarifa = ?', 1)
            ->where('a.estado = ?', 'pendiente_pago')
            ->where('a.id_empresa IN (?)',
                new Zend_Db_Expr("SELECT id_empresa FROM empresa_membresia WHERE estado = 'vigente'"))
            ->where('c.estado = ?', 'pagado')
        ;
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getCipByIdImpreso($idAnuncioWeb)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('a' => $this->_name), array('idImpreso' => 'a.id')
            )
            ->joinInner(
                array('c' => 'compra'), 'c.id = a.id_compra',
                array('idCompra' => 'c.id', 'c.cip')
            )
            ->where('a.id = ?', $idAnuncioWeb);
        return $this->getAdapter()->fetchRow($sql);
    }

    public function obtenerCodSubSeccion($idCompra)
    {

        $sql = $this->getAdapter()->select()
            ->from(
                array('aw' => $this->_name), array('np.cod_subseccion')
            )
            ->joinInner(
                array('np' => 'nivel_puesto'), 'np.id = aw.id_nivel_puesto',
                null
            )
            ->where('aw.id_compra = ?', $idCompra)
            ->order('np.peso asc')
            ->limit(1);

        return $this->getAdapter()->fetchOne($sql);
    }

    public function getSolrAviso($id)
    {
        $id      = (int) $id;
        $adapter = $this->getAdapter();
        $sql     = "SELECT DISTINCT
  a.id                     AS id_anuncio_web,
  a.creado_por             AS creado_por,
  a.fh_vencimiento_proceso AS fh_vencimiento_proceso,
  a.slug                   AS slugaviso,
  a.slug_pais              AS slugpais,
  (CASE a.mostrar_empresa WHEN 0 THEN ' ' WHEN 1 THEN IF((ISNULL(a.logo) OR (a.logo = '')),' ',a.logo) END) AS logoanuncio,
   (CASE a.mostrar_empresa WHEN 0 THEN TRIM(REPLACE(REPLACE(a.empresa_rs,'.',''),' ',' ')) WHEN 1 THEN TRIM(REPLACE(REPLACE(e.nombre_comercial,' ',' '),'.','')) END) AS empresa_rs,
   LOWER((CASE a.mostrar_empresa WHEN 0 THEN TRIM(REPLACE(REPLACE(a.empresa_rs,'.',''),' ',' ')) WHEN 1 THEN TRIM(REPLACE(REPLACE(e.nombre_comercial,' ',' '),'.','')) END)) AS empresa_rs_busqueda,
   a.mostrar_empresa        AS mostrar_empresa,
  a.mostrar_salario        AS mostrar_salario,
  LCASE(e.nombre_comercial) AS nombre_comercial,
  LCASE(e.razon_social)    AS razon_social,
  e.id                     AS id_empresa,
  
  REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LCASE(TRIM(REPLACE(e.nombre_comercial,'.',''))),' ','-'),'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'ñ','n'),'--',' '),' ','-') AS empresaslug,
  CONCAT(e.razon_social,'|',e.nombre_comercial,'|',REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LCASE(TRIM(REPLACE(e.nombre_comercial,'.',''))),' ','-'),'á','a'),'é','e'),'í','i'),'ó','o'),'ú','u'),'--',' '),' ','-')) AS dataempresa,
  (SELECT
     GROUP_CONCAT(ca.adecsys_code SEPARATOR ',')
   FROM compra_adecsys_codigo ca
   WHERE (ca.id_compra = a.id_compra)) AS adecsys_code,
  IF((CONCAT(a.funciones,a.responsabilidades) = ''),' ',CONCAT(a.funciones,' ',a.responsabilidades)) AS description,
  IF((CONCAT(a.funciones,a.responsabilidades) = ''),' ',LOWER(CONCAT(a.funciones,' ',a.responsabilidades))) AS description_busqueda,
  (SELECT
     CONCAT(u.nombre)
   FROM ubigeo u
   WHERE (u.id = a.id_ubigeo)) AS ubicacion,
  (SELECT
     u.slug_ubigeo
   FROM ubigeo u
   WHERE (u.id = a.id_ubigeo)) AS ubicacionslug,
  a.puesto                 AS puesto,
  LOWER(a.puesto)                 AS puesto_busqueda,
  (SELECT
     CONCAT(ar.nombre,'|',ar.slug)
   FROM area ar
   WHERE (ar.id = a.id_area)) AS area,
  (SELECT
     CONCAT(IFNULL(ar.slug,' '))
   FROM area ar
   WHERE (ar.id = a.id_area)) AS areaslug,
  (SELECT
     GROUP_CONCAT(DISTINCT c.nombre SEPARATOR ',')
   FROM ((anuncio_web aw
       JOIN anuncio_estudio ae
         ON ((aw.id = ae.id_anuncio_web)))
      JOIN carrera c
        ON ((c.id = ae.id_carrera)))
   WHERE ((c.id = ae.id_carrera)
          AND (aw.id = a.id))) AS carrera_busqueda,
  (SELECT
     GROUP_CONCAT(DISTINCT c.slug SEPARATOR ',')
   FROM ((anuncio_web aw
       JOIN anuncio_estudio ae
         ON ((aw.id = ae.id_anuncio_web)))
      JOIN carrera c
        ON ((c.id = ae.id_carrera)))
   WHERE ((c.id = ae.id_carrera)
          AND (aw.id = a.id))) AS carreraslug,
  (SELECT
     GROUP_CONCAT(DISTINCT tc.nombre SEPARATOR '#')
   FROM (((anuncio_web aw
        JOIN anuncio_estudio ae
          ON ((aw.id = ae.id_anuncio_web)))
       JOIN carrera c
         ON ((c.id = ae.id_carrera)))
      JOIN tipo_carrera tc
        ON ((tc.id = c.id_tipo_carrera)))
   WHERE ((c.id = ae.id_carrera)
          AND (aw.id = a.id))) AS tipo_carrera,
  (SELECT
     CONCAT(np.nombre,'|',np.slug)
   FROM nivel_puesto np
   WHERE (np.id = a.id_nivel_puesto)) AS nivel,
  (SELECT
     np.nombre
   FROM nivel_puesto np
   WHERE (np.id = a.id_nivel_puesto)) AS nivel_busqueda,
  (SELECT
     CONCAT(np.slug)
   FROM nivel_puesto np
   WHERE (np.id = a.id_nivel_puesto)) AS nivelslug,
  a.fh_pub                 AS fecha_publicacion,
  DATE(a.fh_pub)                 AS fecha_publi,
  IF(ISNULL(a.salario_min),'0',IF((a.mostrar_salario = 0),0,CAST(IF(((a.salario_max = 750) OR (a.salario_max = 600)),1,a.salario_min) AS DECIMAL(10,2)))) AS price,
  IF((ISNULL(a.salario_max) OR (a.salario_max = '')),IF((((a.salario_min = 10001) OR (a.salario_min = 9001)) AND (a.mostrar_salario = 1)),15000.00,0),IF((a.mostrar_salario = 0),0,CAST(a.salario_max AS DECIMAL(10,2)))) AS price2,
 
IF(prioridad<6,1,0)  AS destacado,
a.destacado AS destacado_home,
   a.prioridad              AS prioridad,
  CONCAT('/ofertas-de-trabajo/',a.slug,'-',a.url_id) AS url,
  a.url_id                 AS url_id,  a.discapacidad

FROM (anuncio_web a
   JOIN empresa e
     ON ((e.id = a.id_empresa)))
WHERE ((a.online = 1)
       AND (a.estado = 'pagado')
       AND (a.borrador = 0)
       AND (a.eliminado = 0)
       AND (a.cerrado = 0))
       AND (a.id=$id)
GROUP BY a.id
       ";
        //die($sql); exit;
        //   echo $sql;exit;
        $stm     = $adapter->query($sql);
        return $stm->fetch(Zend_Db::FETCH_ASSOC);
    }

    public function getSolrAvisoCarrera($id)
    {
        $id      = (int) $id;
        $adapter = $this->getAdapter();
        $sql     = "SELECT
        DISTINCT CONCAT(`tc`.`nombre`,'|',`c`.`nombre`,'|',`c`.`slug`) AS carrera
        FROM `anuncio_estudio` `ae`
        INNER JOIN `carrera` `c` ON `c`.`id` = `ae`.`id_carrera`
        INNER JOIN `tipo_carrera` `tc` ON `tc`.`id` = `c`.`id_tipo_carrera`
        WHERE `ae`.`id_anuncio_web` = '$id'";
        //die($sql); exit;
        $stm     = $adapter->query($sql);
        $stmt    = $stm->fetchAll();
        $data    = array();
        foreach ($stmt as $row) {
            $tmp    = array_values(array_slice($row, 0, 1));
            $data[] = $tmp[0];
        }
        return $data;
    }

    public function getSolrAvisoEstudio($id)
    {
        $id      = (int) $id;
        $adapter = $this->getAdapter();
        $sql     = "SELECT
        CONCAT_WS('|',IFNULL(`ne`.`nombre`,''),IFNULL(`net`.`nombre`,''),IFNULL(`c`.`nombre`,''),IFNULL(`ae`.`otra_carrera`,''),
        IFNULL(CAST(`ae`.`id_nivel_estudio` AS CHAR),'0'),IFNULL(CAST(`ae`.`id_carrera` AS CHAR),'0'),
        IFNULL(CAST(`ae`.`id_nivel_estudio_tipo` AS CHAR),'0')
        ) AS estudio
        FROM `anuncio_estudio` AS `ae`
        LEFT JOIN `carrera` AS `c` ON ae.id_carrera = c.id
        LEFT JOIN `nivel_estudio` AS `ne` ON ae.id_nivel_estudio = ne.id
        LEFT JOIN `nivel_estudio` AS `net` ON ae.id_nivel_estudio_tipo = net.id
        WHERE ae.id_anuncio_web = '$id'";
        //die($sql); exit;
        $stm     = $adapter->query($sql);
        $stmt    = $stm->fetchAll();
        $data    = array();
        foreach ($stmt as $row) {
            $tmp    = array_values(array_slice($row, 0, 1));
            $data[] = $tmp[0];
        }
        return $data;
    }

    public function getSolrAvisoExperiencia($id)
    {
        $id      = (int) $id;
        $adapter = $this->getAdapter();
        $sql     = "SELECT
        CONCAT_WS('|',IFNULL(CAST(`aex`.`experiencia` AS CHAR),'0'),IFNULL(`a`.`nombre`,''),
        IFNULL(CAST(`aex`.`id_area` AS CHAR),'0'),IFNULL(CAST(`aex`.`id_nivel_puesto` AS CHAR),'0')
        ) AS experiencia
        FROM `anuncio_experiencia` AS `aex`
        LEFT JOIN `area` AS `a` ON aex.id_area = a.id
        WHERE aex.id_anuncio_web = '$id'";
        //die($sql); exit;
        $stm     = $adapter->query($sql);
        $stmt    = $stm->fetchAll();
        $data    = array();
        foreach ($stmt as $row) {
            $tmp    = array_values(array_slice($row, 0, 1));
            $data[] = $tmp[0];
        }
        return $data;
    }

    public function getSolrAvisoIdioma($id)
    {
        $id      = (int) $id;
        $adapter = $this->getAdapter();
        $sql     = "SELECT
        CONCAT(IFNULL(`ai`.`id_idioma`,''),'|',IFNULL(`ai`.`nivel`,'')) AS idioma
        FROM `anuncio_idioma` AS `ai`
        WHERE ai.id_anuncio_web = '$id'";
        //die($sql); exit;
        $stm     = $adapter->query($sql);
        $stmt    = $stm->fetchAll();
        $data    = array();
        foreach ($stmt as $row) {
            $tmp    = array_values(array_slice($row, 0, 1));
            $data[] = $tmp[0];
        }
        return $data;
    }

    public function getSolrAvisoPrograma($id)
    {
        $id      = (int) $id;
        $adapter = $this->getAdapter();
        $sql     = "SELECT
        CONCAT(IFNULL(`apc`.`id_programa_computo`,''),'|',IFNULL(`apc`.`nivel`,'')) AS programa
        FROM `anuncio_programa_computo` AS `apc`
        WHERE apc.id_anuncio_web = '$id'";
        //die($sql); exit;
        $stm     = $adapter->query($sql);
        $stmt    = $stm->fetchAll();
        $data    = array();
        foreach ($stmt as $row) {
            $tmp    = array_values(array_slice($row, 0, 1));
            $data[] = $tmp[0];
        }
        return $data;
    }

    public function getSolrAvisoPregunta($id)
    {
        $id      = (int) $id;
        $adapter = $this->getAdapter();
        $sql     = "SELECT
        CONCAT(`preg`.`id`,'|', `preg`.`pregunta`) AS pregunta from `pregunta` AS preg
        INNER JOIN `cuestionario` AS `cues` ON `preg`.`id_cuestionario` = `cues`.`id`
        WHERE `cues`.`id_anuncio_web` = '$id'";
        //die($sql); exit;
        $stm     = $adapter->query($sql);
        $stmt    = $stm->fetchAll();
        $data    = array();
        foreach ($stmt as $row) {
            $tmp    = array_values(array_slice($row, 0, 1));
            $data[] = $tmp[0];
        }
        return $data;
    }

    public function getIndexarSolr()
    {

        $sql  = $this->getAdapter()->select()->distinct()->from(array("a" => $this->_name),
                array(
                'source_id' => 'a.id',
                "eliminado" => 'a.eliminado',
                "cerrado" => 'a.cerrado',
                'borrador' => 'a.borrador'
            ))
            ->where('a.online = ?', 1)
            ->where('a.estado = ?', self::ESTADO_PAGADO)
            ->where('a.buscamas = ?', 0)
            ->order('a.id desc');
        $data = $this->getAdapter()->fetchAll($sql);

        return $data;
    }

    public function getAvisoInfofichaAnuncio($id)
    {

        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array(
                'aw.id',
                'puesto' => new Zend_Db_Expr('UPPER(aw.puesto)'),
                'aw.funciones', 'aw.responsabilidades',
                'aw.slug', 'aw.id_empresa', 'aw.salario_min', 'aw.salario_max',
                'aw.url_id', 'aw.mostrar_salario', 'aw.proceso_activo',
                'aw.online', 'aw.borrador', 'aw.cerrado',
                'aw.mostrar_empresa', 'aw.fh_vencimiento',
                'aw.fh_vencimiento_proceso',
                'aw.republicado',
                'fh_publicacion' => new Zend_Db_Expr("DATE_FORMAT(aw.fh_pub, '%d/%m/%Y')"),
                'aw.fh_pub',
                'aw.slug_pais',
                'aw.id_compra',
                'aw.estado',
                'aw.id_ubigeo',
                'aw.id_area',
                'aw.id_nivel_puesto',
                'aw.empresa_rs',
                'aw.prioridad',
                'redireccion' => 'aw.redireccion',
                'buscamas' => 'aw.buscamas'
                )
            )
            ->where('aw.id = ?', $id);
        //  ->order('aw.online desc');

        $anuncio = $this->getAdapter()->fetchRow($sql);

        if ($anuncio === false || $anuncio == null) {
            return null;
        }
        if (!empty($anuncio['id_empresa'])) {
            $sql1                        = $db->select()->from(
                    array('e' => 'empresa'),
                    array(
                    'nombre_empresa' => 'e.nombre_comercial', 'e.slug_empresa',
                    'slug_empresa' => 'e.slug_empresa',
                    'empresaslug' => 'e.slug_empresa',
                    'logo_empresa' => 'e.logo',
                    'logo_facebook' => 'e.logo3'
                    )
                )->joinLeft(array('ru' => 'rubro'), 'ru.id = e.id_rubro',
                    array('rubro_empresa' => 'ru.nombre'))
                ->where('e.id = ?', $anuncio['id_empresa']);
            $empresa                     = $this->getAdapter()->fetchRow($sql1);
            foreach ($empresa as $k => $v)
                $anuncio[$k]                 = $v;
            $anuncio['nombre_comercial'] = (empty($anuncio['mostrar_empresa'])) ? $anuncio['empresa_rs']
                    : $empresa['nombre_empresa'];
        } else {
            $anuncio['nombre_comercial'] = $anuncio['empresa_rs'];
        }
        if (empty($anuncio['id_ubigeo'])) $anuncio['id_ubigeo'] = 3928;
        $sql2                 = $db->select()
            ->from(
                array('u' => 'ubigeo'),
                array(
                'ciudad' => 'u.display_name',
                'ubigeo_nombre' => 'u.nombre',
                'ubicacionslug' => 'u.display_name'
                )
            )
            ->where('u.id = ?', $anuncio['id_ubigeo']);
        $ubigeo               = $this->getAdapter()->fetchRow($sql2);
        foreach ($ubigeo as $k => $v)
            $anuncio[$k]          = $v;
        if (!empty($anuncio['id_area'])) {
            $sql3        = $db->select()
                ->from(
                    array('a' => 'area'),
                    array('area_puesto' => 'a.nombre',
                    'area_puesto_slug' => 'a.slug')
                )
                ->where('a.id = ?', $anuncio['id_area']);
            $area        = $this->getAdapter()->fetchRow($sql3);
            foreach ($area as $k => $v)
                $anuncio[$k] = $v;
        }
        if (!empty($anuncio['id_nivel_puesto'])) {
            $sql4         = $db->select()
                ->from(
                    array('np' => 'nivel_puesto'),
                    array('nivel_puesto_slug' => 'np.slug',
                    'nivel_puesto_nombre' => 'np.nombre')
                )
                ->where('np.id = ?', $anuncio['id_nivel_puesto']);
            $nivel_puesto = $this->getAdapter()->fetchRow($sql4);
            foreach ($nivel_puesto as $k => $v)
                $anuncio[$k]  = $v;
        }
        return $anuncio;
    }

    public function getCompra($idAviso)
    {
        $db  = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('aw' => 'anuncio_web'), array('aw.id_compra')
            )
            ->where('aw.id = ?', $idAviso)
            ->order('aw.id DESC');
        return $this->getAdapter()->fetchOne($sql);
    }

    /**
     * Retorna los avisos activos de una empresa y pertenecientes a membresías
     *
     * @param type $idEmpresa Id de empresa
     * @return array
     */
    public function obtenerAvisosActivosEmpresaMembresia($idEmpresa)
    {
        $sql = $this->getAdapter()->select()->from($this->_name,
                array('id', 'url_id'))
            ->where('estado = ?', 'pagado')
            ->where('online = ?', 1)
            ->where('cerrado = ?', 0)
            ->where('id_empresa = ?', $idEmpresa)
            ->order('id desc');
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     *
     * @param int Id de aviso
     * @return string Estado online del aviso
     */
    public function estadoActivo($idAviso)
    {
        $sql = $this->getAdapter()->select()->from($this->_name, array('online'))
            ->where('id = ?', $idAviso);
        return $this->getAdapter()->fetchOne($sql);
    }
    /*
     * Funcion que retorna parametros para las notificaciones
     *
     */

    public function getDataAviso($id)
    {
        $sql = $this->getAdapter()->select()->from(array('aw' => $this->_name),
                array('logo' => 'aw.logo'))
            ->where('id = ?', $id);
        return $this->getAdapter()->fetchRow($sql);
    }

    /**
     * Retorna los datos del aviso que se usarán para enviar la invitación a un postulante
     * 
     * @param int $id Id del aviso
     * @return array Datos del aviso
     */
    public function getDataAvisoInvitacion($id)
    {
        $sql    = $this->getAdapter()->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('url_aviso' => 'aw.url_id',
                'id_anuncio_web' => 'aw.id',
                'ubicacion' => 'u.display_name',
                'puesto' => 'upper(aw.puesto)',
                'slugaviso' => 'aw.slug',
                'destacado' => 'aw.destacado',
                'creado_por' => 'aw.creado_por',
                'url_id' => 'aw.url_id',
                'empresa_rs' => new Zend_Db_Expr(
                    "CASE aw.mostrar_empresa
                      WHEN 0 THEN aw.empresa_rs
                      WHEN 1 THEN e.nombre_comercial
                    END"
                ),
                'areas' => new Zend_Db_Expr(
                    "(SELECT ar.nombre FROM area ar WHERE ar.id=aw.id_area)"
                ),
                'slug_area' => new Zend_Db_Expr(
                    "(SELECT ar.slug FROM area ar WHERE ar.id=aw.id_area)"
                ),
                'slug_nivel' => new Zend_Db_Expr(
                    "(SELECT ar.slug FROM nivel_puesto ar WHERE ar.id=aw.id_nivel_puesto)"
                ),
                'mostrar_empresa' => 'aw.mostrar_empresa',
                'urlaviso' => 'aw.url_id',
                'fh_pub' => 'aw.fh_pub')
            )
            ->join(
                array('e' => 'empresa'), 'aw.id_empresa = e.id',
                array(
                'razon_social' => 'aw.empresa_rs',
                'empresaslug' => 'e.slug_empresa',
                )
            )
            ->joinleft(
                array('u' => 'ubigeo'), 'aw.id_ubigeo = u.id',
                array(
                'ubicacionslug' => new Zend_Db_Expr(
                    "REPLACE(LOWER(u.nombre),' ','-')"
                )
                )
            )
            ->where('aw.id = ?', $id);
        $result = $this->getAdapter()->fetchAll($sql);
        return $result[0];
    }
}