<?php

class Application_Model_PerfilDestacado extends App_Db_Table_Abstract {

    protected $_name = "perfil_destacado";

    const ESTADO_REGISTRADO = 'registrado';
    const ESTADO_PENDIENTE_PAGO = 'pendiente_pago';
    const ESTADO_EXTORNADO = 'extornado';
    const ESTADO_PAGADO = 'pagado';
    const ESTADO_DADO_BAJA = 'dado_baja';
    const ESTADO_VENCIDO = 'vencido';
    const ESTADO_EXTENDIDO = 'extendido';
    //Estados del perfil destacado
    const ACTIVO = 1;
    const EN_ESPERA = 2; //Cuando se tiene en espera, osea ya tiene un perfil activo. Al vencer este se activa autom.
    const VENCIDO = 3;
    const PENDIENTE_PAGO = 4;
    const CERRADO = 1;
    const PROCESO_ACTIVO = 1;
    const NO_CERRADO = 0;
    const NO_ELIMINADO = 0;

    private $_model = null;

    public function __construct() {
        parent::__construct();
        $cparts = explode('_', __CLASS__);
        $this->_model = $cparts[2];
    }

    public function getDatosGenerarCompra($idPerfil) {

        $sql = $this->getAdapter()->select()
                ->from(
                        array('pd' => 'perfil_destacado'), array(
                    'perfilId' => 'pd.id',
                    'id' => 'pd.id',
                    'postulanteId' => 'pd.id_postulante',
                    'productoId' => 'pd.id_producto',
                    'tarifaId' => 'pd.id_tarifa',
                    'nombres' => 'pos.nombres',
                    'apellidos' => "concat(pos.apellido_paterno,' ',pos.apellido_materno)"
                        )
                )
                ->joinInner(array('pos' => 'postulante'), 'pos.id = pd.id_postulante', array())
                ->joinLeft(
                        array('c' => 'compra'), 'pd.id_compra = c.id', array('estadoCompra' => 'c.estado',
                    'medioPago' => 'c.medio_pago')
                )
                ->join(
                        array('t' => 'tarifa'), 'pd.id_tarifa = t.id', array('tarifaPrecio' => 't.precio',
                    'medioPublicacion' => 't.medio_pub')
                )
                ->join(
                        array('p' => 'producto'), 't.id_producto = p.id', array('nombreProducto' => 'p.nombre')
                )
                ->join(
                        array('u' => 'usuario'), 'pd.creado_por = u.id', array('postulanteMail' => 'u.email',
                    'usuarioId' => 'u.id')
                )
                ->where('pd.id = ?', $idPerfil);
        $rsPerfil = $this->getAdapter()->fetchRow($sql);

        $sql = $this->getAdapter()->select()
                ->from(
                        array('pd' => 'producto_detalle'), array(
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
                ->where('pd.id_producto = ?', $rsPerfil['productoId']);
        $rs = $this->getAdapter()->fetchAssoc($sql);
        $rsPerfil['beneficios'] = $rs;

        //Bloque del Ente
        $sql = $this->getAdapter()->select()
                ->from(
                        array('pe' => 'postulante_ente'), array('enteId' => 'ente_id')
                )
                ->where('id_postulante = ?', $rsPerfil['postulanteId'])
                ->where('esta_activo = 1');
        $enteId = $this->getAdapter()->fetchOne($sql);
        $rsPerfil['enteId'] = $enteId;
        return $rsPerfil;
    }

    //Verifica si ya tiene un perfil destacado activo
    public function validaPerfilDestacado($idPostulante) {

        $sql = $this->getAdapter()->select()
                ->from($this->_name, array('fh_inicio' => 'max(fh_inicio)', 'fh_fin' => 'max(fh_fin)'))
                ->where('id_postulante = ?', $idPostulante)
                ->where('estado = ?', self::ESTADO_PAGADO);

        $data = $this->getAdapter()->fetchRow($sql);

        if (isset($data['fh_inicio'])) {
            if (!is_null($data['fh_inicio']))
                return $data;
        }

        return null;
    }

    public function obtenerRegPerfilDestacado($idPostulante) {

        $sql = $this->getAdapter()->select()->from(array('pd' => $this->_name))
                ->joinInner(array('t' => 'tarifa'), 't.id = pd.id_tarifa', array('t.precio',
                    'inicio' => "CONCAT(DATE_FORMAT(pd.fh_inicio,'%d'),'-',DATE_FORMAT(pd.fh_inicio,'%m'),'-',YEAR(pd.fh_inicio))",
                    'tipoDoc' => 'c.tipo_doc',
                    'c.cip',
                    'medio' => new Zend_Db_Expr("CASE c.medio_pago WHEN 'pe' THEN 'Pago Efectivo' when 'visa' then 'Visa' when 'mc' then 'Master Card' END"),
                    'fin' => "CONCAT(DATE_FORMAT(pd.fh_fin,'%d'),'-',DATE_FORMAT(pd.fh_fin,'%m'),'-',YEAR(pd.fh_fin))",
                    'meses' => new Zend_Db_Expr("CASE t.meses WHEN 12 THEN '1 año' when 3 then '3 meses' ELSE '6 meses' END"),
                    'activo' => new Zend_Db_Expr("CASE activo WHEN 1 THEN 'Activo' WHEN 2 THEN 'Pendiente' WHEN 3 THEN 'Vencido' when 4 then 'Pendiente Pago' END")))
                ->joinInner(array('c' => 'compra'), 'c.id = pd.id_compra', array('fh_confirmacion' => "CONCAT(DATE_FORMAT(fh_confirmacion,'%d'),'-',DATE_FORMAT(fh_confirmacion,'%m'),'-',"
                    . "YEAR(fh_confirmacion),' ',DATE_FORMAT(fh_confirmacion,'%H'),':',DATE_FORMAT(fh_confirmacion,'%i'))"))
                ->where('pd.id_postulante = ?', $idPostulante)
                ->order(array('pd.activo asc', 'pd.fh_inicio asc'));

        return $this->getAdapter()->fetchAll($sql);
    }
    
    public function obtenerRegPerfilDestacadoDetalle($idPostulante, $idCompra) {

        $sql = $this->getAdapter()->select()->from(array('pd' => $this->_name))
                ->joinInner(array('t' => 'tarifa'), 't.id = pd.id_tarifa', array('tarifaPrecio' => 't.precio',
                    'compraId' => 'c.id',
                    'inicio' => "CONCAT(DATE_FORMAT(pd.fh_inicio,'%d'),'-',DATE_FORMAT(pd.fh_inicio,'%m'),'-',YEAR(pd.fh_inicio))",
                    'tipoDoc' => 'c.tipo_doc','c.cip','codigoBarras' => 'c.cod_barra',
                    'medio' => new Zend_Db_Expr("CASE c.medio_pago WHEN 'pe' THEN 'Pago Efectivo' when 'visa' then 'Visa' when 'mc' then 'Master Card' END"),
                    'fin' => "CONCAT(DATE_FORMAT(pd.fh_fin,'%d'),'-',DATE_FORMAT(pd.fh_fin,'%m'),'-',YEAR(pd.fh_fin))",
                    'meses' => new Zend_Db_Expr("CASE t.meses WHEN 12 THEN '1 año' when 3 then '3 meses' ELSE '6 meses' END"),
                    'activo' => new Zend_Db_Expr("CASE activo WHEN 1 THEN 'Activo' WHEN 2 THEN 'Pendiente' WHEN 3 THEN 'Vencido' when 4 then 'Pendiente Pago' END")))
                ->joinInner(array('c' => 'compra'), 'c.id = pd.id_compra', array('fh_confirmacion' => "CONCAT(DATE_FORMAT(fh_confirmacion,'%d'),'-',DATE_FORMAT(fh_confirmacion,'%m'),'-',"
                    . "YEAR(fh_confirmacion),' ',DATE_FORMAT(fh_confirmacion,'%H'),':',DATE_FORMAT(fh_confirmacion,'%i'))"))
                ->joinInner(array('p' => 'producto'), 'p.id = t.id_producto', array('nombreProducto' => 'p.nombre'))
                ->where('pd.id_postulante = ?', $idPostulante)
                ->where('c.id = ?', $idCompra)
                ->order(array('pd.activo asc', 'pd.fh_inicio asc'));

        return $this->getAdapter()->fetchRow($sql);
    }

    public function getPerfilDestacado($idPostulante, $limit = null, $col = '', $ord = '') {

        $col = $col == '' ? 'pd.activo' : $col;
        $ord = $ord == '' ? 'desc' : $ord;

        $sql = $this->getAdapter()->select()->from(array('pd' => $this->_name))
                ->joinInner(array('t' => 'tarifa'), 't.id = pd.id_tarifa', array('t.precio',
                    'inicio' => "CONCAT(DATE_FORMAT(pd.fh_inicio,'%d'),'-',DATE_FORMAT(pd.fh_inicio,'%m'),'-',YEAR(pd.fh_inicio))",
                    'tipoDoc' => 'c.tipo_doc','c.cip','compraId' => 'c.id',
                    'diasPE' => 'IFNULL(DATEDIFF(CURRENT_TIMESTAMP(),c.fh_expiracion_cip),0)',
                    'medio' => new Zend_Db_Expr("CASE c.medio_pago WHEN 'pe' THEN 'Pago Efectivo' when 'visa' then 'Visa' when 'mc' then 'Master Card' END"),
                    'fin' => "CONCAT(DATE_FORMAT(pd.fh_fin,'%d'),'-',DATE_FORMAT(pd.fh_fin,'%m'),'-',YEAR(pd.fh_fin))",
                    'meses' => new Zend_Db_Expr("CASE t.meses WHEN 12 THEN '1 año' when 3 then '3 meses' ELSE '6 meses' END"),
                    'activo' => new Zend_Db_Expr("CASE pd.activo WHEN 1 THEN 'Activo' WHEN 2 THEN 'Pendiente' WHEN 3 THEN 'Vencido' when 4 then 'Por pagar' END")))
                ->joinInner(array('c' => 'compra'), 'c.id = pd.id_compra', array('fh_confirmacion' => "CONCAT(DATE_FORMAT(fh_confirmacion,'%d'),'-',DATE_FORMAT(fh_confirmacion,'%m'),'-',"
                    . "YEAR(fh_confirmacion),' ',DATE_FORMAT(fh_confirmacion,'%H'),':',DATE_FORMAT(fh_confirmacion,'%i'))"))
                ->where('pd.id_postulante = ?', $idPostulante)
                ->order(sprintf('%s %s', $col, $ord));
        //->order(array('pd.activo asc', 'pd.fh_inicio asc'));

        if (!is_null($limit)) {
            $sql = $sql->limit($limit);
        }

        return $sql;
    }

    public function getPaginator($idPostulante, $col, $ord) {
        
        $limit = 100;
        $ord = $ord == 'asc' || $ord == 'desc' ? $ord : 'desc';
        $colMap = array(
            'tipo' => 't.meses',
            'inicio' => 'pd.fh_inicio',
            'fin' => 'pd.fh_fin',
            'activo' => 'pd.activo'
        );
        $column = array_key_exists($col, $colMap) ? $colMap[$col] : 'pd.activo';
        
     //   var_dump($column);exit;
        $p = Zend_Paginator::factory($this->getPerfilDestacado($idPostulante, null, $column, $ord));
        return $p->setItemCountPerPage($limit);
        
    }
    
    //Obtiene el registro del perfil destacado que se encuentra activo
    public function obtenerRegPerfilDestacadoActivo($idPostulante) {

        $sql = $this->getAdapter()->select()->from(array('pd' => $this->_name))
                ->joinInner(array('t' => 'tarifa'), 't.id = pd.id_tarifa', array('t.precio',
                    'inicio' => "CONCAT(DATE_FORMAT(pd.fh_inicio,'%d'),'-',DATE_FORMAT(pd.fh_inicio,'%m'),'-',YEAR(pd.fh_inicio))",
                    'tipoDoc' => 'c.tipo_doc',
                    'c.cip','u.email','p.nombres',
                    'medio' => new Zend_Db_Expr("CASE c.medio_pago WHEN 'pe' THEN 'Pago Efectivo' when 'visa' then 'Visa' when 'mc' then 'Master Card' END"),
                    'fin' => "CONCAT(DATE_FORMAT(pd.fh_fin,'%d'),'-',DATE_FORMAT(pd.fh_fin,'%m'),'-',YEAR(pd.fh_fin))",
                    'meses' => new Zend_Db_Expr("CASE t.meses WHEN 12 THEN '1 año' when 3 then '3 meses' ELSE '6 meses' END"),
                    'activo' => new Zend_Db_Expr("CASE pd.activo WHEN 1 THEN 'Activo' WHEN 2 THEN 'Pendiente' WHEN 3 THEN 'Vencido' when 4 then 'Pendiente Pago' END")))
                ->joinInner(array('c' => 'compra'), 'c.id = pd.id_compra', array('fh_confirmacion' => "CONCAT(DATE_FORMAT(fh_confirmacion,'%d'),'-',DATE_FORMAT(fh_confirmacion,'%m'),'-',"
                    . "YEAR(fh_confirmacion),' ',DATE_FORMAT(fh_confirmacion,'%H'),':',DATE_FORMAT(fh_confirmacion,'%i'))"))
                ->joinInner(array('u' => 'usuario'), 'u.id = c.creado_por' ,null)
                ->joinInner(array('p' => 'postulante'), 'p.id = pd.id_postulante' ,null)
                ->where('pd.id_postulante = ?', $idPostulante)
                ->where('pd.activo = ?',self::ACTIVO)
                ->order(array('pd.activo asc', 'pd.fh_inicio asc'));

        return $this->getAdapter()->fetchRow($sql);
    }

}
