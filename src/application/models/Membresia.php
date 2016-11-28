<?php

class Application_Model_Membresia extends App_Db_Table_Abstract
{
    protected $_name = 'membresia';
    
    const MEMBRESIA_ESENCIAL = 'Membresía Esencial';
    const MEMBRESIA_SELECTO = 'Membresía Selecto';
    const MEMBRESIA_PREMIUM = 'Membresía Premium';
    const BONIFICADO_ESENCIAL = 'Bonificado Esencial';
    const BONIFICADO_SELECTO = 'Bonificado Selecto';
    const BONIFICADO_PREMIUM = 'Bonificado Premium';
    const TIPO_ESTADO_VIGENTE = 'vigente';
    const TIPO_ESTADO_NO_VIGENTE = 'no vigente';
    
    const ESENCIAL              = 1;
    //const SELECTO               = 2;
    const SELECTO               = 10;
    const PREMIUM               = 3;
    const PREMIUM_BONIFICADO    = 6;
    //const DIGITAL               = 7;
    const DIGITAL               = 9;
    const MENSUAL               = 11;
        
    const ACTIVO   = 1;
    const INACTIVO = 0;
    
    CONST M_NOMBRE_PREMIUM='Premium';
    CONST M_NOMBRE_SELECTO='Selecto';
    CONST M_NOMBRE_ESENCIAL='Esencial';
    CONST M_TIPO='membresia';
    
    public function __construct()
    {
        parent::__construct();
        $cparts = explode('_', __CLASS__);
        $this->_model = $cparts[2];
    }
    
    public static function getMebresiasByEmpresaId($id)
    {
        $obj = new Application_Model_Membresia();
        $db = $obj->getAdapter();
        $sql = $db->select()
            ->from(
                array('m' => $obj->_name), 
                array(
                    'm_id' => 'm.id',
                    'm_nombre' => 'm.nombre',
                    'm_descripcion' => 'm.descripcion',
                    'm_creado_por' => 'm.creado_por',
                    'm_fh_creacion' => 'm.fh_creacion',
                    'm_tipo' => 'm.tipo'
                )
            )->joinInner(
                array('em' => 'empresa_membresia'),
                "m.id = em.id_membresia",
                array(
                    'em_id' => 'em.id',
                    'em_id_empresa' => 'em.id_empresa',
                    'em_id_membresia' => 'em.id_membresia',
                    'em_fh_inicio_membresia' => 'em.fh_inicio_membresia',
                    'em_fh_fin_membresia' => 'em.fh_fin_membresia',
                    'em_creado_por' => 'em.creado_por',
                    'em_fh_creacion' => 'em.fh_creacion',
                    'em_modificado_por' => 'em.modificado_por',
                    'em_fh_modificacion' => 'em.fh_modificacion',
                    'em_monto' => 'em.monto',
                    'em_estado' => 'em.estado'
                )
            )
            ->joinInner(
                array('c' => 'compra'),
                "em.id = c.id_empresa_membresia",
                array(
                    'diasPE' => new Zend_Db_Expr('IFNULL(DATEDIFF(CURRENT_TIMESTAMP(),c.fh_expiracion_cip),0)'),
                    'cip' => 'c.cip',
                    'medio' => new Zend_Db_Expr("CASE c.medio_pago WHEN 'pe' THEN 'Pago Efectivo' when 'visa' then 'Visa' when 'mc' then 'Master Card' END"),
                    'tipoDoc' => 'c.tipo_doc',
                    'compraId' => 'c.id',
                    'c_estado' => 'c.estado',
                    'c_token' => 'c.token',
                )
            )
                
                
                ->joinInner(
                array('e' => 'empresa'),
                "em.id_empresa = e.id",
                array()
            )->where('e.id = ?', $id)
            ->order(array('em_fh_creacion DESC'));
        return $db->fetchAll($sql);
    }
    
    
    public static function getMembresiasByIdEmpresa($idEmpresa, $col = '', $ord = '')
    {
        $col = $col == '' ? 'em.estado' : $col;
        $ord = $ord == '' ? 'desc' : $ord;
        
        $obj = new Application_Model_Membresia();
        $db = $obj->getAdapter();
        $sql = $db->select()
            ->from(
                array('m' => $obj->_name), 
                array(
                    'm_id' => 'm.id',
                    'm_nombre' => 'm.nombre',
                    'm_tipo' => 'm.tipo',
                    'm_descripcion' => 'm.descripcion',
                    'm_creado_por' => 'm.creado_por',
                    'm_fh_creacion' => 'm.fh_creacion',
                    'm_tipo' => 'm.tipo'
                )
            )->joinInner(
                array('em' => 'empresa_membresia'),
                "m.id = em.id_membresia",
                array(
                    'em_id' => 'em.id',
                    'em_id_empresa' => 'em.id_empresa',
                    'em_id_membresia' => 'em.id_membresia',
                    'em_fh_inicio_membresia' => 'em.fh_inicio_membresia',
                    'em_fh_fin_membresia' => 'em.fh_fin_membresia',
                    'em_creado_por' => 'em.creado_por',
                    'em_fh_creacion' => 'em.fh_creacion',
                    'em_modificado_por' => 'em.modificado_por',
                    'em_fh_modificacion' => 'em.fh_modificacion',
                    'em_monto' => new Zend_Db_Expr('em.monto+em.monto*0.18'),
                    'em_estado' => 'em.estado'
                )
            )
            ->joinLeft(
                array('c' => 'compra'),
                "em.id = c.id_empresa_membresia",
                array(
                    'diasPE' =>new Zend_Db_Expr( 'IFNULL(DATEDIFF(CURRENT_TIMESTAMP(),c.fh_expiracion_cip),0)'),
                    'cip' => 'c.cip',
                    'medio' => new Zend_Db_Expr("CASE c.medio_pago WHEN 'pe' THEN 'Pago Efectivo' when 'visa' then 'Visa' when 'mc' then 'MasterCard' END"),
                    'tipoDoc' => 'c.tipo_doc',
                    'compraId' => 'c.id',
                    'c_estado' => 'c.estado',
                    'c_token' => 'c.token',
                )
            )
            ->joinInner(
                array('e' => 'empresa'),
                "em.id_empresa = e.id",
                array()
            )
                
                
            ->where('e.id = ?', $idEmpresa)
            ->order(sprintf('%s %s', $col, $ord));
        
//        if (!is_null($limit)) {
//            $sql = $sql->limit($limit);
//        }
//        
        //echo $sql->assemble(); exit;
        
        return $db->fetchAll($sql);
    }
    
    
    public function getPaginador($idEmpresa, $col, $ord) {
        
        $limit = 5;
        $ord = $ord == 'asc' || $ord == 'desc' ? $ord : 'desc';
        $colMap = array(
            'nombre' => 'm.nombre',
            'inicio' => 'em.fh_inicio_membresia',
            'fin' => 'em.fh_fin_membresia',
            'estado' => 'em.estado'
        );
        
        $column = array_key_exists($col, $colMap) ? $colMap[$col] : 'em.id';
        $p = Zend_Paginator::factory(Application_Model_Membresia::getMembresiasByIdEmpresa($idEmpresa, $column, $ord));
        return $p->setItemCountPerPage($limit);
        
    }
    
    
    
    public static function getMembresias()
    {
        $objMemb = new Application_Model_Membresia();
        $db = $objMemb->getAdapter();
        $sql = $db->select()
            ->from(
                array('m' => $objMemb->_name), 
                array('id', 'nombre')
            );
        return $db->fetchPairs($sql);
    }
    public function  getMembresiaDetalleById($id, $isCached = false,$benficoweb=false, $active = null){
        
        if ($isCached) {
            $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        
            $cacheId = $this->_model.'_'.__FUNCTION__.'_'.$id;
            if ($this->_cache->test($cacheId)) {
                return $this->_cache->load($cacheId);
            }
        }
        
        $activo = (is_null($active) ? self::ACTIVO : $active);
        
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array('m' => $this->_name), 
                    array(
                        'id'=>'m.id',
                        'nombreProducto'=>'m.nombre',
                        'descripcion'=>'m.descripcion',
                        'tarifaPrecio'=>'m.monto',
                        'tipo'=>'m.tipo')
                    )
                ->where('m.id = ?', $id)
                ->where('m.activo = ?', $activo);
        
        $detalle= $db->fetchRow($sql);
        if(!$detalle){
            return false;
        }
        
        $sql = $db->select()
            ->from(
                array('mdet' => 'membresia_detalle'),
                array(
                    'valor'=> 'mdet.valor')
            )->where('mdet.id_membresia = ?', $detalle['id'])
             ->where('estado =?', self::ACTIVO);
        
        $sql->join(array('benef'=>'beneficio'), 
                'benef.id = mdet.id_beneficio', 
              array(
                  'codigo'=>'benef.codigo',
                  'descbeneficio'=>'benef.desc'));
        if($benficoweb){
           $sql->where("benef.codigo IN ('memprem-web','memprem-imp','memprem-adic','memsele-web','memsele-imp','memsele-adic','memesen-web','memesen-imp','memesen-adic','memdigi','memdigi-adic','memmens')");
        }      
        $benficoweb=$db->fetchAll($sql);       
        $detalle['beneficios'] = $benficoweb;
        
        if ($isCached) {
            $this->_cache->save($detalle, $cacheId, array(), $cacheEt);
        }
        
        return $detalle;        
    }
    
    public function getMembresiaById($id)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(array('m' => $this->_name), array('*'))
            ->where('id = ?', $id);
        return $db->fetchRow($sql);
    }
    
    
    
    public static function getMembresiasAndConsumoTotalByEmpresaId($id)
    {
        $objSubquery = new Application_Model_AnuncioWeb();
        $sqlSubquery = $objSubquery->getAdapter()
            ->select()
            ->from(
                array( 'aw' => 'anuncio_web'),
                array('id_anuncio_impreso' => 'DISTINCT(ai.id)','id_empresa_membresia' => 'em.id')
            )->joinInner(
                array('c' => 'compra'),
                'aw.id_compra = c.id AND c.estado IN ("pagado","dado_baja")',
                array()
            )->joinInner(
                array('ai' => 'anuncio_impreso'), 
                'ai.id_compra = c.id',
                array('c.precio_total')
            )->joinInner(
                array('em' => 'empresa_membresia'), 
                $objSubquery->getAdapter()->quoteInto('aw.id_empresa_membresia = em.id and em.id_empresa = ?', $id),
                array()
            );
        
        $obj = new Application_Model_Membresia();
        $db = $obj->getAdapter();
        $sql = $db->select()
            ->from(
                array('e' => 'empresa'),
                array() 
            )->joinLeft(
                array('em' => 'empresa_membresia'),
                'em.id_empresa = e.id',
                array(
                    'em_id' => 'em.id',
                    'em_id_empresa' => 'em.id_empresa',
                    'em_id_membresia' => 'em.id_membresia',
                    'em_fh_inicio_membresia' => 'em.fh_inicio_membresia',
                    'em_fh_fin_membresia' => 'em.fh_fin_membresia',
                    'em_creado_por' => 'em.creado_por',
                    'em_fh_creacion' => 'em.fh_creacion',
                    'em_modificado_por' => 'em.modificado_por',
                    'em_fh_modificacion' => 'em.fh_modificacion',
                    'em_monto' => new Zend_Db_Expr('em.monto+em.monto*0.18'),
                    'em_estado' => 'em.estado'
                )
            )
            ->joinLeft(
                array('ai' => 'anuncio_impreso'), 
                'ai.id_empresa = e.id', 
                array()
            )
            ->joinLeft(
                array('subq' => new Zend_Db_Expr('(' . $sqlSubquery->assemble() . ')')), 
                'subq.id_anuncio_impreso = ai.`id` AND subq.id_empresa_membresia = em.id',
                array('c_total' => new Zend_Db_Expr('SUM(subq.precio_total)'))
            )
            ->joinInner(
                array('m' => $obj->_name),
                'm.id = em.id_membresia',
                array(
                    'm_id' => 'm.id',
                    'm_nombre' => 'm.nombre',
                    'm_descripcion' => 'm.descripcion',
                    'm_creado_por' => 'm.creado_por',
                    'm_fh_creacion' => 'm.fh_creacion',
                    'm_tipo' => 'm.tipo'
                )
            )->where('e.id = ?', $id)
            ->order(array('em_estado'))
            ->order(array('em.fh_inicio_membresia desc'))->group('em.id');
        
        return $db->fetchAll($sql);
    }
    
    public static function getAvisosConsumidosByMembresiaId($id)
    {
        $obj = new Application_Model_Membresia();
        $db = $obj->getAdapter();
        $sql = $db->select()
            ->from(array('aw' => 'anuncio_web'), array('DISTINCT(ai.id)'))
            ->joinInner(
                array('c' => 'compra'), 
                'aw.id_compra = c.id AND c.estado IN ("pagado","dado_baja")', 
                array('c.fh_confirmacion', 'c.precio_total')
            )
            ->joinInner(
                array('ai' => 'anuncio_impreso'), 
                'ai.id_compra = c.id', 
                array('aviso' => 'ai.titulo', 'ai.tipo')
            )
            ->joinInner(array('em' => 'empresa_membresia'), 'aw.id_empresa_membresia = em.id', array())
            ->where('em.id = ?', $id)
            ->order('fh_confirmacion DESC');
        return $db->fetchAll($sql);
    }
    
    
    public static function getInfoMembresiaPorEmpresaById($id)
    {
        $objSubquery = new Application_Model_AnuncioWeb();
        $sqlSubquery = $objSubquery->getAdapter()
            ->select()
            ->from(
                array( 'aw' => 'anuncio_web'),
                array('id_anuncio_impreso' => 'DISTINCT(ai.id)','id_empresa_membresia' => 'em.id')
            )->joinInner(
                array('c' => 'compra'),
                'aw.id_compra = c.id AND c.estado IN ("pagado","dado_baja")',
                array()
            )->joinInner(
                array('ai' => 'anuncio_impreso'), 
                'ai.id_compra = c.id',
                array('c.precio_total')
            )->joinInner(
                array('em' => 'empresa_membresia'), 
                $objSubquery->getAdapter()->quoteInto('aw.id_empresa_membresia = em.id and em.id = ?', $id),
                array()
            );
        
        $obj = new Application_Model_Membresia();
        $db = $obj->getAdapter();
        $sql = $db->select()
            ->from(
                array('e' => 'empresa'),
                array('e_nombre' => 'e.nombre_comercial') 
            )->joinLeft(
                array('em' => 'empresa_membresia'),
                'em.id_empresa = e.id',
                array(
                    'em_id' => 'em.id',
                    'em_id_empresa' => 'em.id_empresa',
                    'em_id_membresia' => 'em.id_membresia',
                    'em_fh_inicio_membresia' => 'em.fh_inicio_membresia',
                    'em_fh_fin_membresia' => 'em.fh_fin_membresia',
                    'em_creado_por' => 'em.creado_por',
                    'em_fh_creacion' => 'em.fh_creacion',
                    'em_modificado_por' => 'em.modificado_por',
                    'em_fh_modificacion' => 'em.fh_modificacion',
                    'em_monto' => 'em.monto',
                    'em_estado' => 'em.estado'
                )
            )
            ->joinLeft(
                array('ai' => 'anuncio_impreso'), 
                'ai.id_empresa = e.id', 
                array()
            )
            ->joinInner(
                array('subq' => new Zend_Db_Expr('(' . $sqlSubquery->assemble() . ')')), 
                'subq.id_anuncio_impreso = ai.`id` AND subq.id_empresa_membresia = em.id',
                array('c_total' => new Zend_Db_Expr('SUM(subq.precio_total)'))
            )
            ->joinInner(
                array('m' => $obj->_name),
                'm.id = em.id_membresia',
                array(
                    'm_id' => 'm.id',
                    'm_nombre' => 'm.nombre',
                    'm_descripcion' => 'm.descripcion',
                    'm_creado_por' => 'm.creado_por',
                    'm_fh_creacion' => 'm.fh_creacion',
                    'm_tipo' => 'm.tipo'
                )
            )->where('em.id = ?', $id)
            ->order(array('em.id DESC'))->group('em.id');
        return $db->fetchRow($sql);
    }
    
    public static function getMembresiasByTipo ($activo, $tipo = '%')
    {
        $objMemb = new Application_Model_Membresia();
        $db = $objMemb->getAdapter();
        $sql = $db->select()
            ->from(
                array('m' => $objMemb->_name),
                array('id', 'nombre')
            )->where('tipo = ?', $tipo);
        if ($activo == 1) {
            $sql = $sql->where('activo = ?', $activo);
        }
        return $db->fetchPairs($sql);
    }
    
    public function getNombreMembresia ($id) 
    {
        $db = $this->getAdapter();
        $select =  $this->getAdapter()->select()->from($this->_name,
                array(
                    'nombre_mem' => new Zend_Db_Expr("CONCAT('Membresía: ',IF(tipo = 'membresia','','Bonificado '),'',UPPER(nombre))"))
                )->where('id = ?', $id);
        return $db->fetchOne($select);
        
    }
}
