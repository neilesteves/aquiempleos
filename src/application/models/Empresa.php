<?php
/**
 * Description of Empresa
 *
 * @author Solman Vaisman
 */
class Application_Model_Empresa extends App_Db_Table_Abstract
{
    protected $_name = "empresa";
    
    /**
     * Retorna el nombre de la empresa con el id_usuario
     * 
     * @param int $usuarioId
     */
    
    const PORTADA = 1;
    
    public function __construct()
    {
        parent::__construct();
        $cparts = explode('_', __CLASS__);
        $this->_model = $cparts[2];
    }
    
    public function getLogosAleatorios($avisosPortada)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from($this->_name, 'id')
            ->where("logo != ''");
        
        $data = $db->fetchAll($sql);
        
        $idsSeleccionados = "";
        
        if ($data == null || count($data) == 0) {
            return array();
        }
        
        //$max = count($data);
        $max = $avisosPortada;
        //if ($max > 4) $max = 4;
        for ($i = 0; $i < $max; $i++) {
            $pos = mt_rand(0, count($data) - 1);
            
            $idsSeleccionados .= $data[$pos]['id'];
            array_splice($data, $pos, 1);
            
            if ($i < ($max - 1)) {
                $idsSeleccionados .= ", ";
            }
        }
        
        $sql = $db->select()
            ->from($this->_name, array("logo", "razon_comercial"))
            ->Where("id in (".$idsSeleccionados.")");
        
        
        $logosSeleccionados = $db->fetchAll($sql);
        return $logosSeleccionados;
    }
    
    public function getNombreByUsuarioId($usuarioId)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from($this->_name, array('razon_social','nombre_comercial'))
            ->where('id_usuario = ?', $usuarioId);
        return $db->fetchRow($sql);
    }
  public function getIdEmpresa($id=0)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from($this->_name, array('*'))
            ->where('id = ?', $id);
        return $db->fetchRow($sql);
    }
    public function getEmpresaByEmail($email, $ruc)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array("e" => $this->_name),
                array(
                    "nombres"   => "ue.nombres",
                    "apellidos" => "ue.apellidos",
                    "puesto"    => "ue.puesto",
                    "area"      => "ue.area",
                    "email"     => "u.email",
                    "telefono"  => "ue.telefono",
                    "telefono2" => "ue.telefono2",
                    "idempresa" => "e.id",
                    "idusuario" => "u.id",
                    "anexo"     =>  "ue.anexo",
                    "anexo2"    => "ue.anexo2",
                    "razonsocial" => "e.razon_social",
                    "nombre_comercial" => "e.nombre_comercial",
                    "id"               => "e.id",
                    "id_ubigeo"        => "e.id_ubigeo",
                    "logo"             => "e.logo"
                )
            )
            ->joinInner(
                array(
                    "ue"=>"usuario_empresa"
                ),
                "ue.id_empresa = e.id",
                array()
            )
            ->joinInner(
                array(
                    "u"=>"usuario"
                ),
                "u.id = ue.id_usuario",
                array()
            );
          $sql = $sql->where('u.rol= ?', 'empresa-admin');
            if ($email != '') {
                $sql = $sql->where('u.email= ?', $email);
            }
            if ($ruc != '' && $ruc!=10) {
                $sql = $sql->Where('e.ruc like (?)', $ruc.'%');
            } //echo $sql;exit;
        return $db->fetchRow($sql);
    }    
    public function getEmpresa($id)
    {
    $db = $this->getAdapter();
    $whereField = is_numeric($id)?'id':'slug';
    $sql = $db->select()
            ->from(
                array('e' => $this->_name),
                array('level' => 'u.level')
            )
            ->join(array ('u'=>'ubigeo'), 'e.id_ubigeo = u.id')
            ->where("e.$whereField = ?", $id);
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
            'razonsocial' => 'e.razon_social',
            'nombrecomercial' => 'e.nombre_comercial',
            'tipo_doc' => 'e.tipo_doc',
            'num_ruc' => 'e.ruc',
            'direccion' => 'e.direccion',
            'logo' => 'e.logo',
            'logo1' => 'e.logo1',
            'logo2' => 'e.logo2',
            'logo3' => 'e.logo3',
            'id_empresa' => 'e.id'
        );
        
        $fields = array_merge($extraFields, $baseFields);
        
                $sql = $db->select()
            ->from(
                array('e' => $this->_name),
                $fields
            );
                
    if ($level == Application_Model_Ubigeo::NIVEL_DISTRITO) {
        $sql = $sql->join(array ('dist'=>'ubigeo'), 'dist.id=e.id_ubigeo');
        $sql = $sql->join(array ('prov'=>'ubigeo'), 'dist.padre = prov.id');
        $sql = $sql->join(array ('dpto'=>'ubigeo'), 'prov.padre = dpto.id');
        $sql = $sql->joinLeft(array ('paisres'=>'ubigeo'), 'dpto.padre = paisres.id');
    }
    if ($level==Application_Model_Ubigeo::NIVEL_PROVINCIA) {
        $sql = $sql->join(array ('prov'=>'ubigeo'), 'prov.id = e.id_ubigeo');
        $sql = $sql->join(array ('dpto'=>'ubigeo'), 'prov.padre = dpto.id');
        $sql = $sql->joinLeft(array ('paisres'=>'ubigeo'), 'dpto.padre = paisres.id');
    }
            if ($level==Application_Model_Ubigeo::NIVEL_DEPARTAMENTO) {
                $sql = $sql->join(array ('dpto'=>'ubigeo'), 'dpto.id = e.id_ubigeo');
                $sql = $sql->joinLeft(array ('paisres'=>'ubigeo'), 'dpto.padre = paisres.id');
            }
            if ($level==Application_Model_Ubigeo::NIVEL_PAIS) {
                $sql = $sql->joinLeft(array ('paisres'=>'ubigeo'), 'paisres.id = e.id_ubigeo');
            }
            $sql->join(
                array(
                    'u' => 'usuario'),
                'u.id=e.id_usuario ',
                array('id_usuario'=>'u.id','email' => 'u.email','activo'=>'u.activo','rol'=>'u.rol')
            )->joinLeft(
                array(
                'r' => 'rubro'),
                'r.id=e.id_rubro ',
                array('nombre_rubro'=>'r.nombre','rubro'=>'r.id')
            )
        ->where("e.$whereField = ?", $id);
        $rs = $db->fetchRow($sql);
        return $rs;
    }
    
    public static function validacionRuc($value)
    {
        $options = func_get_args();
        $id = $options[2];
        $e = new Application_Model_Empresa();
        $sql = $e->select()
                ->from('empresa', 'id')
                ->where('ruc = ?', $value)
                ->limit('1');
        if ($id) {
            $sql = $sql->where('id != ?', $id);
        }
        $sql = $sql->limit('1');

        $r = $e->getAdapter()->fetchOne($sql);
        return ! (bool) $r;
    }
    public function misProcesos($id)
    {
        $nlimit = $this->_config->empresa->dashboard->misprocesos->limit;
        $e = new Application_Model_Empresa();
        $sql = $e->getAdapter()->select()
                ->from(
                    array('aw' => 'anuncio_web'),
                    array(
                          'id' => 'aw.id',
                          'puesto' => 'aw.puesto',
                          'nnuevos' => 'aw.nnuevos',
                          'ntotal' => 'aw.ntotal'
                    )
                )
                ->where('aw.id_empresa = ?', $id)
                ->where('aw.proceso_activo =?', 
                        Application_Model_AnuncioWeb::PROCESO_ACTIVO)
                ->where('aw.fh_vencimiento_proceso>=CURDATE()')
                ->where('aw.cerrado =?', 
                        Application_Model_AnuncioWeb::NO_CERRADO)
                ->where('aw.eliminado =?', 
                        Application_Model_AnuncioWeb::NO_ELIMINADO)
                ->order('aw.id desc ')
                ->limit($nlimit);
        $db = $this->getAdapter();
        return $db->fetchAll($sql);
    }
    
    public function misProcesosAdmSecundarios($administradorId)
    {
        $nlimit = $this->_config->empresa->dashboard->misprocesos->limit;
        $e = new Application_Model_Empresa();
        $sql = $e->getAdapter()->select()
                ->from(
                    array('aw' => 'anuncio_web'),
                    array(
                          'id' => 'aw.id',
                          'puesto' => 'aw.puesto',
                          'nnuevos' => 'aw.nnuevos',
                          'ntotal' => 'aw.ntotal'
                    )
                )
                ->joinInner(array('aue' => 'anuncio_usuario_empresa'), 
                        'aue.id_anuncio = aw.id', array())
                ->where('aue.id_usuario_empresa =?', $administradorId)
                ->where('aw.proceso_activo =?', 
                        Application_Model_AnuncioWeb::PROCESO_ACTIVO)
                ->where('aw.fh_vencimiento_proceso>=CURDATE()')
                ->where('aw.cerrado =?', 
                        Application_Model_AnuncioWeb::NO_CERRADO)
                ->where('aw.eliminado =?', 
                        Application_Model_AnuncioWeb::NO_ELIMINADO)
                ->order('aw.id desc ')
                ->limit($nlimit);
        $db = $this->getAdapter();
        return $db->fetchAll($sql);
    }

    public function validacionNRuc($value)
    {
        $options = func_get_args();
        $idUsuario = $options[2];
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from('empresa', 'id')
                ->where('ruc = ?', $value);
        if ($idUsuario) {
            $sql = $sql->where('id != ?', $idUsuario);
        }
        $sql = $sql->limit('1');
        $r = $db->fetchOne($sql);
        return !(bool) $r;
    }

    public function validacionCampoRepetido($campo, $value)
    {
        $options = func_get_args();
        if ($options[2] != null) {
            $idUsuario = $options[2];
            $value = $campo;
            $campo = $options[3];
        } else if ($options[2] == null && is_numeric($options[3])) {
            $idUsuario = $options[3];
        } else if ($options[2] == null && $options[3] == false) {
            $idUsuario = $options[3];
        } else {
            $idUsuario = $options[2];
            $value = $campo;
            $campo = $options[3];
        }
        
        $modelEmpresa = new Application_Model_Empresa();
        
        $sql = $modelEmpresa->getAdapter()->select()
                ->from('empresa', 'id')
                ->where($campo.' = ?', $value);
        if ($idUsuario) {
            $sql = $sql->where('id != ?', $idUsuario);
        }
        
        $sql = $sql->limit('1');
        $r = $modelEmpresa->getAdapter()->fetchOne($sql);
        return !(bool) $r;
    }
    
    public function datosParaEnteAdecsys($empresaId)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('e' => 'empresa'),
                array('doc_numero' => 'e.ruc',
                'razon_social' => 'e.razon_social',
                'razonComercial' => 'e.razon_comercial',
                'ubigeoId' => 'ifnull(ub.id_adecsys,1)')
            )
            ->join(
                array('u' => 'usuario'),
                'e.id_usuario = u.id',
                array('email' => 'u.email')
            )
            ->join(
                array('ue' => 'usuario_empresa'),
                'ue.id_usuario = u.id',
                array('nombres' => 'ue.nombres',
                'ape_pat' => 'ue.apellidos',
                'telefono' => 'ue.telefono'
                 )
            )    
            ->joinInner(
                    array('c' => 'compra'),
                'ue.id_empresa = c.id_empresa',
                array('idEmpMem' => 'c.id_empresa_membresia'))
           ->joinInner(
                    array('car' => 'compra_adecsys_ruc'),
                       'c.id = car.id_compra',
                array('IdComp' => 'car.id_compra',
                       'Nruc' => 'car.ruc',
                       'Nraz_social' => 'car.razon_social',
                       'Ntip_via' => 'car.tipo_via',
                       'fh_cr' => 'car.fh_creacion',
                       'Ndireccion'=>'car.direccion',
                       'NroPuerta'=>'car.nro_puerta',
                       'creador' => 'car.creado_por'
                    )  )
            ->joinLeft(array('ub' => 'ubigeo'), 
                 'ub.id = e.id_ubigeo',null)
            ->where('ue.creador = 1  or ue.creador = 0')
            ->where('e.id =?', $empresaId)
            ->order('c.id DESC');
        $rs = $this->getAdapter()->fetchRow($sql);
        return $rs;
    }
      public function datosParaEnteAdecsysMembresia($empresaId,$idcompra)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('e' => 'empresa'),
                array('doc_numero' => 'e.ruc',
                'razon_social' => 'e.razon_social',
                'razonComercial' => 'e.razon_comercial',
                'ubigeoId' => 'ifnull(ub.id_adecsys,1)')
            )
            ->join(
                array('u' => 'usuario'),
                'e.id_usuario = u.id',
                array('email' => 'u.email')
            )
            ->join(
                array('ue' => 'usuario_empresa'),
                'ue.id_usuario = u.id',
                array('nombres' => 'ue.nombres',
                'ape_pat' => 'ue.apellidos',
                'telefono' => 'ue.telefono')
            )
           ->joinInner(
                    array('c' => 'compra'),
                'ue.id_empresa = c.id_empresa',
                array('idEmpMem' => 'c.id_empresa_membresia'))
           ->joinInner(
                    array('car' => 'compra_adecsys_ruc'),
                'c.id = car.id_compra',
                array('IdComp' => 'car.id_compra',
                       'Nruc' => 'car.ruc',
                       'Nraz_social' => 'car.razon_social',
                       'Ntip_via' => 'car.tipo_via',
                       'fh_cr' => 'car.fh_creacion',
                       'Ndireccion'=>'car.direccion',
                       'NroPuerta'=>'car.nro_puerta',
                       'creador' => 'car.creado_por'
                    )
                   
                   
                   )
            ->joinLeft(array('ub' => 'ubigeo'), 
                 'ub.id = e.id_ubigeo',null)
            
            ->where('c.id=?',$idcompra)  
            ->where('ue.creador = 1')
            ->where('e.id =?', $empresaId);
        
        $rs = $this->getAdapter()->fetchRow($sql);
        return $rs;
    }
    
    /**
     * Retorna lista de empresas que coincidan con la razón social o RUT indicados
     * 
     * @param string $razonsocial
     * @param string $ruc
     * @param string $col
     * @param string $ord
     * @return array
     */
    public function getPaginadorBusquedaPersonalizada($razonsocial, $ruc, $col, $ord)
    {
        $paginadoBusqueda = $this->_config->administrador->gestion->paginadoBusqueda;
        $p = Zend_Paginator::factory(
            $this->getBusquedaPersonalizada(
                $razonsocial, $ruc, $col, $ord
            )
        );
        return $p->setItemCountPerPage($paginadoBusqueda);
    }    
    
    /**
     * Retorna lista de empresas que coincidan con la razón social o RUT indicados
     * (el campo estadoLF se usa para activar Look and Feel de la empresa)
     * 
     * @param string $razonsocial
     * @param string $ruc
     * @param string $col
     * @param string $ord
     * @return array
     */
    public function getBusquedaPersonalizada($razonsocial, $ruc, $col='', $ord='')
    {
        $col = $col == '' ? 'e.razon_social' : $col;
        $ord = $ord == '' ? 'DESC' : $ord;
        
        $sql = $this->getAdapter()->select()
            ->from(
                array('e'=>$this->_name),
                array('e.id', 'e.id_usuario', 'razonsocial' => 'e.razon_social', 'num_ruc' => 'e.ruc',
                    'razon_comercial' => 'e.razon_comercial', 'logo'=>'logo', 'portada' => 'portada',
                    'logo' => 'logo', 'ruc' => 'ruc' , 'estadoLF'=>'e.Look_Feel')
            )
            ->joinInner(
                array('u'=> 'usuario'), 'u.id = e.id_usuario',
                array('u.email', 'u.activo', 'u.fh_registro')
            );      
        
        if ($razonsocial == '' && $ruc == '') {
            $sql = $sql->where('e.portada = 1');
        } else {
            if ($razonsocial != '') {
                $sql = $sql->where('e.razon_social like ?', '%'.$razonsocial.'%');
            }
            if ($ruc != '') {
                $sql = $sql->where('e.ruc = ?', $ruc);
            }
        }
        $sql = $sql->order(sprintf('%s %s', $col, $ord));

        return $sql;
    }
    
    /**
     * Retorna las empresas cuyos logos se muestran en portada
     * 
     */
    public function getEmpresasPortadas($cache = true)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        
        if ($this->_cache->test($cacheId) && $cache) {
            return $this->_cache->load($cacheId);
        }
        $result='';
        $sql = $this->getAdapter()->select()
            ->from(
                array('e' => 'empresa'), 
                array(
                    'id' => 'e.id', 
                    'rs' => 'e.razon_social', 
                    'nc' => 'e.nombre_comercial',
                    'logo' => 'e.logo',
                    'url' => 'e.url_tcn'
                )
            )
            ->where('e.portada = 1');
        $result = $this->getAdapter()->fetchAll($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }
    
    public function getEmpresaMembresia($idEmpresa)
    {

        $sql = $this->getAdapter()->select()
            ->from(array('e' => $this->_name))
            ->joinLeft(
                array('em' => 'empresa_membresia'), 
                $this->getAdapter()->quoteInto(
                    'em.id_empresa = e.id AND em.estado = ?', 
                    Application_Model_EmpresaMembresia::ESTADO_VIGENTE
                ), 
                array('em_id' => 'em.id')
            )->where('e.id = ?', $idEmpresa);
        $empresa = $this->getAdapter()->fetchRow($sql);
        $empresa['membresia_info'] = null;
        if (!empty($empresa['em_id'])) {
            $obj = new Application_Model_EmpresaMembresia();
            $membresia = $obj->getDetalleEmpresaMembresia($empresa['em_id']);
            $empresa['membresia_info'] = $membresia;
        }
        return $empresa;
    }
    
    public function quitarEmpresaPortada($empresaId)
    {
        $result = $this->update(
            array('portada' => '0','prioridad_home' => NULL),
            $this->getAdapter()->quoteInto(' id = ?', $empresaId)
        );
        return $result;
    }

    public function agregarEmpresaPortada($empresaId)
    {
        $result = $this->update(
            array('portada' => '1'),
            $this->getAdapter()->quoteInto(' id = ?', $empresaId)
        );
        return $result;
    }
    
    public function getAlertaEmpresa($idEmpresa)
    {
        $sql = $this->getAdapter()
            ->select()
            ->from(
                $this->_name,
                array('prefs_emailing_avisos'=>'prefs_emailing_avisos',
                'prefs_emailing_info'=>'prefs_emailing_info')
            )
            ->where('id = ?', $idEmpresa);
        $rs = $this->getAdapter()->fetchRow($sql);
        return $rs;
    }

    public function autocomplete($q, $subset, $nivel = null)
    {
        $subsets = array(
            'prueba' => '1=1',
            'test2' => 'id < 10',
        );

        $sql = $this->getAdapter()->select()
            ->from($this->_name, array('id', 'razon_social'))
            ->where(' UPPER(razon_social) like ?', '%' . strtoupper($q) . '%')
            ->limit($this->_config->app->limitSuggest);
        if (!is_null($subset)) {
            if (!array_key_exists($subset, $subsets)) {
                throw new Zend_Exception('SUBSET inválido');
            }
            $sql = $sql->where($subsets[$subset]);
        }
        return $this->getAdapter()->fetchPairs($sql);
    }
    
    public function obtenerPorId($id, $columnas = array())
    {
        $columnas = $this->setCols($columnas);
        
        return $this->fetchRow($this->select()
            ->from($this->_name, $columnas)
            ->where('id =?', (int)$id));
    }
    
    /**
     * Retorna las empresas cuyos logos se muestran en portada
     * 
     */
    public function getEmpresasPortadasAleatorio()
    {
  
        $sql = $this->getAdapter()->select()
            ->from(
                array('e' => 'empresa'), 
                array(
                    'id' => 'id', 
                    'rs' => 'razon_social', 
                    'logo' => 'logo'
                )
            )
            ->where('portada = 1');
        
        //echo $sql;
        
        $result = $this->getAdapter()->fetchAll($sql);
        
        return $result;
    }
    
    public function obtenerEmpresasBusquedaAvanzada($descripcion) 
    {

        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        
        $descripcionOrig = $descripcion;
        $descripcionCache = strtolower($descripcion);
        $descripcionCache = str_replace('ñ', 'n', $descripcion);
        $descripcionCache = str_replace("á", "a", $descripcionCache);
        $descripcionCache = str_replace("é", "e", $descripcionCache);
        $descripcionCache = str_replace("í", "i", $descripcionCache);
        $descripcionCache = str_replace("ó", "o", $descripcionCache);
        $descripcionCache = str_replace("ú", "u", $descripcionCache);
        $descripcionCache = str_replace("ñ", "n", $descripcionCache);
        $descripcionCache = str_replace(".", "", $descripcionCache);
        $descripcionCache = str_replace("-", "", $descripcionCache);
        $descripcionCache = str_replace("/", "", $descripcionCache);
        $descripcionCache = str_replace("(", "", $descripcionCache);
        $descripcionCache = str_replace(")", "", $descripcionCache);
        $descripcionCache = str_replace("_", "", $descripcionCache);
        $descripcionCache = str_replace(",", "", $descripcionCache);
        
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . trim(str_replace(' ', '', $descripcionCache));
        
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        
         $sql = $this->getAdapter()->select()->distinct()
                ->from(array('e' => 'empresa'),array('nombre' => new Zend_Db_Expr("REPLACE(CASE WHEN 
                    e.`nombre_comercial` LIKE '%".$descripcion."%' THEN REPLACE(`e`.`nombre_comercial`,'.','') ELSE e.`razon_social` END,'-',' ')"),
                    'val' => 'e.slug_empresa'))
                ->joinInner(array('a' => 'anuncio_web'), 'a.id_empresa = e.id', null)
                ->where('a.mostrar_empresa = ?', 1)
                ->where('a.online = ?', 1)
                ->where('a.estado = ?', 'pagado')
                ->where("(e.nombre_comercial LIKE ? OR e.`razon_social` LIKE ?)",  '%'.$descripcion. '%');
        
        $result = $this->getAdapter()->fetchAll($sql);
        
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        
        return $result;
        
        
    }
    
    public function obtenerSlugBuscamas ($idEmpresa) {
        
        $sql = $this->getAdapter()->select()
                ->from(array('e' => 'empresa'),array('val' => 'e.slug_empresa'))
                ->where('id = ?', $idEmpresa);
        
        return $this->getAdapter()->fetchOne($sql);
        
    }
    
    public function getEmpresasTCN()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }

        $sql = $this->getAdapter()->select()
            ->from(array('e' => $this->_name),
                   array(
                    'id' => 'id', 
                    'rs' => 'razon_social', 
                    'nc' => 'nombre_comercial',
                    'logo' => 'logo',
                    'url' => 'url_tcn'
                ))
            ->join(
                array('em' => 'empresa_membresia'), 
                $this->getAdapter()->quoteInto(
                    'em.id_empresa = e.id'), 
                array('em_id' => 'em.id')
            )
            ->join(
                array('a' => 'anuncio_web'), 
                $this->getAdapter()->quoteInto(
                    'a.id_empresa = e.id'), 
                null
            )
                ->where('e.url_tcn IS NOT NULL')
                ->where('e.logo IS NOT NULL')
                ->where('e.logo != ""')
                ->where('em.id_membresia IN (?)', array(2,3,5,6))                  
                ->where('em.estado = ?', Application_Model_EmpresaMembresia::ESTADO_VIGENTE)
                ->where('a.mostrar_empresa = 1')
                ->where('a.online = ?', Application_Model_AnuncioWeb::ONLINE)
                ->group('e.id');
        
        $empresas = $this->getAdapter()->fetchAll($sql);
        $this->_cache->save($empresas, $cacheId, array(), $cacheEt);
        return $empresas;
    }
    
    //Nuevo requerimiento - Mostrar empresa con membresía (selecto,premium, esencial y digital) activa en home
    public function getCompanyWithMembresia($limit = null)
    {
        
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }

        $sql = $this->getAdapter()->select()
            ->from(array('e' => $this->_name),
                   array(
                    'id' => 'id', 
                    'rs' => 'UPPER(nombre_comercial)', 
                    'nc' => 'nombre_comercial',
                    'logo' => 'logo',
                    'slug' => 'slug_empresa',
                    'url' => 'url_tcn',
                    'ruc'=>'e.ruc'   
                 //   'total_aviso'=>new Zend_Db_Expr('(SELECT count(*) FROM anuncio_web aw WHERE aw.id_empresa=e.id AND online=1 AND mostrar_empresa=1 )')
                ))
//            ->join(
//                array('em' => 'empresa_membresia'), 
//                'em.id_empresa = e.id', 
//                array('em_id' => 'em.id')
//            )
             //   ->where(new Zend_Db_Expr('(SELECT count(*) FROM anuncio_web aw WHERE aw.id_empresa=e.id AND online=1 AND mostrar_empresa=1 ) >?'),0)
                ->where('e.logo is not null')
               // ->where("e.url_tcn is not null or e.url_tcn <> ''")
                ->where('e.logo != ""')
                ->where('e.portada = ?', self::PORTADA)
                ->where('e.prioridad_home > 0')
            //    ->where('em.id_membresia IN (?)', array(1,2,3,7,Application_Model_Membresia::SELECTO,Application_Model_Membresia::DIGITAL,8,6,5,4,Application_Model_Membresia::MENSUAL,12))  
             //   ->where('em.estado = ?', Application_Model_EmpresaMembresia::ESTADO_VIGENTE)
                ->order(array('e.prioridad_home desc'))
                ->group('e.id');
        
        if (!is_null($limit)) {
            $sql = $sql->limit($limit);
        }
        $empresas = $this->getAdapter()->fetchAll($sql);
        //var_dump($empresas);exit;
        $this->_cache->save($empresas, $cacheId, array(), $cacheEt);
        return $empresas;
    }
    
    public function getPaginadorEmpresasTCN($razonsocial, $ruc)
    {
        $paginadoBusqueda = $this->_config->administrador->gestion->paginadoBusqueda;
        $p = Zend_Paginator::factory(
            $this->empresasTCNPortada(
                $razonsocial, $ruc
            )
        );
        return $p->setItemCountPerPage($paginadoBusqueda);
    }
    
    //Admin - Lista las empresas membresía (selecto,premium, esencial y 
    //digital) para activarlas en el HOME
    public function empresasTCNPortada($rs, $ruc)
    {   

        $sql = $this->getAdapter()->select()
            ->from(array('e' => $this->_name),
                   array(
                    'id' => 'id', 
                    'rs' => 'razon_social', 
                    'nc' => 'nombre_comercial',
                    'ruc',
                    'portada',
                    'orden' => 'prioridad_home',
                    'logo' => 'logo',
                    'url' => 'url_tcn'
                ))
            ->join(
                array('em' => 'empresa_membresia'), 
                'em.id_empresa = e.id', 
                array('em_id' => 'em.id')
            )
                ->where('e.logo is not null')
                ->where('e.logo != ""')
                ->where('em.id_membresia IN (?)', array(1,2,3,7,Application_Model_Membresia::SELECTO,Application_Model_Membresia::DIGITAL,8,6,5,4,Application_Model_Membresia::MENSUAL,12))  
                ->where('em.estado = ?', Application_Model_EmpresaMembresia::ESTADO_VIGENTE)
                ->order(array('e.portada desc','e.prioridad_home desc'))
                ->group('e.id');

        if (!empty($rs)) {
            $sql = $sql->where('e.razon_social like ?', '%'.$rs.'%');
        }
        if (!empty($ruc)) {
            $sql = $sql->where('e.ruc = ?', $ruc);
        }
        
        return $sql;
    }
    
    //Landing Empresa: Buscador
    public function getEmpresaHome($descripcion)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};

        $arCambios = array(
            'ñ' => 'n',
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u'
        );
        $descripcionLower = trim(strtolower($descripcion));
        $descripcionTransf = strtr($descripcionLower, $arCambios);                
        $descripcionCache = preg_replace('/[^a-z0-9]/i', '', $descripcionTransf);
        
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_'.trim(str_replace(' ', '', $descripcionCache));
        //echo $cacheId;exit;

        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sqlFirst = $this->getAdapter()->select()->from(array('e' => 'empresa'),
                array(
                    'e.id',
                    'rs' => new Zend_Db_Expr('TRIM(UPPER(e.nombre_comercial))'),'e.logo','e.url_tcn',
                    'orden' => new Zend_Db_Expr(1),
                    'tcn' => new Zend_Db_Expr("CASE IFNULL(e.url_tcn,'n') WHEN 'n' THEN 0 ELSE 1 END")
                ))
                ->joinInner(array('ap' => 'api'),'e.id = ap.usuario_id', null)
                ->joinInner(array('aw' => 'anuncio_web'),'ap.usuario_id = aw.id_empresa', null)
                ->where('e.logo is not null')
                ->where("e.logo <> ''")
                ->where("aw.online = ? ",1)
                ->where("e.url_tcn is not null")
                ->where("aw.mostrar_empresa =?",1)
                ->where('ap.estado = ?','vigente')
                ->where('(e.razon_social like ? OR  e.nombre_comercial like ?)','%'.$descripcion.'%')
                ->group(array('e.id'))
                ->order(array('orden asc','tcn desc','rs asc'));
        $sql = $this->getAdapter()->fetchAll($sqlFirst);
        $this->_cache->save($sql, $cacheId, array(), $cacheEt);
        return $sql;
    }
    
    //public function getPaginator($nombre, $rubro, $limit) {
    public function getPaginator($nombre, $limit) {
                
        $p = Zend_Paginator::factory($this->getEmpresaHome($nombre));

        return $p->setItemCountPerPage($limit);

    }
        
    public function getPaginador($idEmpresa, $col, $ord, $pagina = 1) {
        
        $numeroItems = $this->_config->paginado->numeroItems;
        $ord = ($ord == 'asc' || $ord == 'desc') ? $ord : 'desc';
        $colMap = array(
            'nombre' => 'm.nombre',
            'inicio' => 'em.fh_inicio_membresia',
            'fin' => 'em.fh_fin_membresia',
            'estado' => 'em.estado'
        );
        
        $modelMembresia = new Application_Model_Membresia();
        
        $column = array_key_exists($col, $colMap) ? $colMap[$col] : 'em.id';
        $p = Zend_Paginator::factory($modelMembresia->getMembresiasByIdEmpresa($idEmpresa, $column, $ord));
        $p->setItemCountPerPage($numeroItems);
        $p->setCurrentPageNumber($pagina);
        return $p;
        
        
    }
    
    public function getInfoCompanyTCN($idEmpresa) {
        
        $sql = $this->getAdapter()->select()->from($this->_name,array(
            'id_empresa' => 'id','url_tcn','prioridad_home','portada'
            ))->where('id = ?', $idEmpresa);
        
        return $this->getAdapter()->fetchRow($sql);
        
    }
    
    public function actualizaInfoTCN($data) {
        $result = $this->update(
            array('url_tcn' => $data['url_tcn'],'prioridad_home' => $data['prioridad_home'],
                'portada' => $data['portada']),
            $this->getAdapter()->quoteInto(' id = ?', $data['id_empresa'])
        );
        
        return $result;
        
    }
    
    public function validaPrioridadEmpresaTCN($idEmp, $prioridad) {
        
        $sql = $this->getAdapter()->select()->from($this->_name, array('id'))
                ->where('portada = ?', self::PORTADA)
                ->where('id <> ?', $idEmp)
                ->where('prioridad_home = ?', $prioridad);
        
        return $this->getAdapter()->fetchOne($sql);
        
    }
    
    public function obtieneNumEmpresasTCN() {
        
        $sql = $this->getAdapter()->select()
            ->from(array('e' => $this->_name),
                   array(
                    'id'
                ))
            ->join(
                array('em' => 'empresa_membresia'), 
                $this->getAdapter()->quoteInto(
                    'em.id_empresa = e.id'), 
                array('em_id' => 'em.id')
            )
                ->where('e.logo is not null')
                ->where("e.url_tcn is not null or e.url_tcn <> ''")
                ->where('e.logo != ""')
                ->where('e.portada = ?', self::PORTADA)
                ->where('e.prioridad_home > 0')
                ->where('em.id_membresia IN (?)', array(1,2,3,7,Application_Model_Membresia::SELECTO,Application_Model_Membresia::DIGITAL,8,6,5,4,Application_Model_Membresia::MENSUAL,12))  
                ->where('em.estado = ?', Application_Model_EmpresaMembresia::ESTADO_VIGENTE)
                ->order(array('e.prioridad_home desc'))
                ->group('e.id');
        
        return $this->getAdapter()->fetchAll($sql);
        
        
    }
    
    /**
     * Activar la configuración de L&F de la empresa
     * @param  id $idEmpresa
     * @return boolean
     */
    public function cambioEstadoLookAndFeel($idEmpresa,$activo)
    {
        $result = $this->update(
            array( 'Look_Feel' => $activo),
                   $this->getAdapter()->quoteInto(' id = ?', $idEmpresa)
        );
        $this->_cache->remove('Empresa_LooFeelActivo_'.$idEmpresa);
        return $result;
    }
    /**
     * Verifica que la empresa tiene la opcion de looFeelActivo
     * @param type $Idempresa
     * @return type
     */
    public function LooFeelActivo($Idempresa) 
    {
      $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
      $cacheId = $this->_model.'_'.__FUNCTION__.'_'.$Idempresa;
      if ($this->_cache->test($cacheId)) {
         return false;
      }
      $sql = $this->getAdapter()->select()->from(
              array('e' => $this->_name),
              array('estado'=>'e.Look_Feel')
              )
            ->where('e.id=?',$Idempresa);
     $rs=(int)$this->getAdapter()->fetchOne($sql);
     $this->_cache->save($rs, $cacheId, array(), $cacheEt);
     
     return false;
    }
             
}
