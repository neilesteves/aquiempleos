<?php

class Application_Model_Compra extends App_Db_Table_Abstract
{
    protected $_name = "compra";

    const FORMA_PAGO_PAGO_EFECTIVO   = 'pe';
    const FORMA_PAGO_VISA            = 'visa';
    const FORMA_PAGO_MASTER_CARD     = 'mc';
    const FORMA_PAGO_POS             = 'pos';
    const FORMA_PAGO_MEMBRESIA       = 'membresía';
    const FORMA_PAGO_CONTRATO        = 'contrato';
    const FORMA_PAGO_CREDITO         = 'crédito';
    const FORMA_PAGO_CREDOMATIC      = 'credomatic';
    const FORMA_PAGO_PUNTO_FACIL     = 'pf';
    const FORMA_PAGO_PAGO_VENTANILLA = 'pv';
    const FORMA_PAGO_GRATUITO        = 'gratuito';
    const FORMA_PAGO_AGENCIA         = 'agencia';
    const ESTADO_ANULADO             = 'anulado';
    const ESTADO_EXPIRADO            = 'expirado';
    const ESTADO_EXTORNADO           = 'extornado';
    const ESTADO_PAGADO              = 'pagado';
    const ESTADO_PENDIENTE_PAGO      = 'pendiente_pago';
    const TIPO_SOLOWEB               = 'soloweb';
    const TIPO_MEMBRESIA             = 'membresia';
    const TIPO_CLASIFICADO           = 'clasificado';
    const TIPO_PREFERENCIAL          = 'preferencial';
    const TIPO_DESTACADO             = 'destacado';
    const TIPO_CONTRATO_CREDITO      = 'R';
    const TIPO_CONTRATO_MEMBRESIA    = 'Z';
    const TIPO_CONTRATO_NINGUNO      = 'N';
    const TIPO_CONTRATO_MULTIMEDIOS  = 'E';
    const TIPO_CONTRATO_UNIPRODUCTO  = 'I';
    const TIPO_CONTRATO_CONTADO      = 'C';
    const PAGO_BOLETA                = 'boleta';
    const PAGO_FACTURA               = 'factura';
    const CARACTERES_RUC             = 11;
    CONST RUC                        = 'RUT';
    CONST DNI                        = 'CI';
    CONST MEDIO_PAGO_PF              = 'pf';

    public function getEstadoCuentaPagados($id, $limit = null, $col = '',
                                           $ord = '')
    {
        $col = $col == '' ? 'fh_creacion' : $col;
        $ord = $ord == '' ? 'DESC' : $ord;

        $sqlTwo = $this->getAdapter()->select()
            ->from(
                array('c' => $this->_name),
                array('fechaCreacion' => "DATE_FORMAT(c.fh_creacion,'%d/%m/%Y %H:%i:%S')",
                'c.fh_creacion',
                'medioPago' => 'c.medio_pago',
                'precioTotal' => new Zend_Db_Expr("(c.precio_total + c.precio_total_impreso)"),
                'precioBase' => 'c.precio_base',
                'compraId' => 'c.id',
                'comprobante' => 'c.tipo_doc',
                'anuncioUrl' => new Zend_Db_Expr("''"))
            )
            ->joinInner(
                array('ai' => 'anuncio_impreso'), 'ai.id_compra = c.id',
                array(
                'anuncioId' => new Zend_Db_Expr("''"),
                'puestoAnuncio' => 'ai.titulo',
                'slug' => new Zend_Db_Expr("''"),
                'urlScotAptitus' => 'ai.url_source_aptitus',
                'urlScotTalan' => 'ai.url_source_talan',
                'anuncio_impreso' => 'ai.id',
                'destaque' => new Zend_Db_Expr("''"),
                'tipoAnuncio' => 'ai.tipo'
                )
            )
            ->joinLeft(
                array('p' => 'producto'), 'ai.id_producto = p.id',
                array('productoNombre' => 'p.nombre')
            )
            ->joinLeft(
                array('t' => 'tarifa'), 'ai.id_tarifa = t.id',
                array('medioPublicacion' => 't.medio_pub',
                'tamanoId' => 'id_tamano')
            )
            ->joinLeft(
                array('ti' => 'tamano_impreso'), 't.id_tamano = ti.id',
                array('medida' => 'ti.medida_tarifa',
                'tamanio' => 'ti.descripcion',
                'tamanioCentimetros' => 'ti.tamano_centimetro')
            )
            ->where('c.id_empresa = ?', $id)
            ->where("c.estado = 'pagado'")
            ->where("ai.tipo = 'preferencial'");
        //->order(sprintf('%s %s', $col, $ord));

        $sql = $this->getAdapter()->select()
            ->from(
                array('c' => $this->_name),
                array('fechaCreacion' => "DATE_FORMAT(c.fh_creacion,'%d/%m/%Y %H:%i:%S')",
                'c.fh_creacion',
                'medioPago' => 'c.medio_pago',
                'precioTotal' => new Zend_Db_Expr("(c.precio_total + c.precio_total_impreso)"),
                'precioBase' => 'c.precio_base',
                'compraId' => 'c.id',
                'comprobante' => 'c.tipo_doc')
            )
            ->joinInner(
                array('aw' => 'anuncio_web'), 'aw.id_compra = c.id',
                array(
                'anuncioId' => 'aw.id',
                'anuncioUrl' => 'aw.url_id',
                'puestoAnuncio' => 'aw.puesto',
                'slug' => 'aw.slug',
                'destaque' => 'aw.prioridad',
                'urlScotAptitus' => new Zend_Db_Expr("''"),
                'urlScotTalan' => new Zend_Db_Expr("''"),
                'anuncio_impreso' => 'aw.id_anuncio_impreso',
                'tipoAnuncio' => 'aw.tipo'
                )
            )
            ->joinLeft(
                array('p' => 'producto'), 'aw.id_producto = p.id',
                array('productoNombre' => 'p.nombre')
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
            /* ->joinLeft(
              array('ai'=>'anuncio_impreso'),
              'ai.id = aw.id_anuncio_impreso',
              array('titulo', 'fh_creacion', 'estado_impreso' => 'estado')
              ) */
            ->where('c.id_empresa = ?', $id)
            ->where("c.estado = 'pagado'")
            ->where('aw.eliminado != 1')
            ->where("aw.tipo = 'soloweb' OR aw.tipo = 'clasificado' OR aw.tipo='destacado'");
        //->order(sprintf('%s %s', $col, $ord));
        if (!is_null($limit)) {
            $sql = $sql->limit($limit);
        }

        $select = $this->getAdapter()->select()->union(array($sql, $sqlTwo))
            ->order(sprintf('%s %s', $col, $ord));
        //->order(1);
        //echo $select->assemble(); exit;
        //$rs = $this->getAdapter()->fetchAssoc($sql);
        //return $sql;
        return $select;
    }

    public function getEstadoCuentaEnProceso($id, $limit = null, $col = '',
                                             $ord = '')
    {
        $col = $col == '' ? 'fh_creacion' : $col;
        $ord = $ord == '' ? 'DESC' : $ord;

        $sqlTwo = $this->getAdapter()->select()
            ->from(
                array('c' => $this->_name),
                array('fechaCreacion' => "DATE_FORMAT(c.fh_creacion,'%d/%m/%Y %H:%i:%S')",
                'c.fh_creacion',
                'estadoCompra' => 'c.estado',
                'compraId' => 'c.id',
                'fechaExpiracionCip' => 'c.fh_expiracion_cip',
                'cipCompra' => 'c.cip')
            )
            ->joinInner(
                array('ai' => 'anuncio_impreso'), 'ai.id_compra = c.id',
                array(
                'anuncioId' => new Zend_Db_Expr("''"),
                'puestoAnuncio' => 'ai.titulo',
                'slug' => new Zend_Db_Expr("''"),
                'anuncioUrl' => new Zend_Db_Expr("''"),
                'anuncioId' => 'ai.id',
                'tipoAnuncio' => 'ai.tipo'
                )
            )
            ->joinInner(
                array('p' => 'producto'), 'ai.id_producto = p.id',
                array('productoNombre' => 'p.nombre')
            )
            ->joinLeft(
                array('t' => 'tarifa'), 'ai.id_tarifa = t.id',
                array('medioPublicacion' => 't.medio_pub',
                'tamanoId' => 'id_tamano')
            )
            ->joinLeft(
                array('ti' => 'tamano_impreso'), 't.id_tamano = ti.id',
                array('medida' => 'ti.medida_tarifa',
                'tamanio' => 'ti.descripcion',
                'tamanioCentimetros' => 'ti.tamano_centimetro')
            )
            ->where('c.id_empresa = ?', $id)
            ->where("c.estado != 'pagado'")
            ->where("c.estado != 'anulado'")
            ->where("ai.tipo = 'preferencial'");
        //->order(sprintf('%s %s', $col, $ord));

        $sql = $this->getAdapter()->select()
            ->from(
                array('c' => $this->_name),
                array('fechaCreacion' => "DATE_FORMAT(c.fh_creacion,'%d/%m/%Y %H:%i:%S')",
                'c.fh_creacion',
                'estadoCompra' => 'c.estado',
                'compraId' => 'c.id',
                'fechaExpiracionCip' => 'c.fh_expiracion_cip',
                'cipCompra' => 'c.cip')
            )
            ->joinInner(
                array('aw' => 'anuncio_web'), 'aw.id_compra = c.id',
                array(
                'anuncioId' => 'aw.id',
                'puestoAnuncio' => 'aw.puesto',
                'slug' => 'aw.slug',
                'anuncioUrl' => 'aw.url_id',
                'anuncioId' => 'aw.id',
                'tipoAnuncio' => 'aw.tipo'
                )
            )
            ->joinInner(
                array('p' => 'producto'), 'aw.id_producto = p.id',
                array('productoNombre' => 'p.nombre')
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
            ->where('c.id_empresa = ?', $id)
            ->where('aw.eliminado != 1')
            ->where("c.estado != 'pagado'")
            ->where("c.estado != 'anulado'")
            ->where("aw.tipo = 'soloweb' OR aw.tipo = 'clasificado' OR aw.tipo= 'destacado'");
        if (!is_null($limit)) {
            $sql = $sql->limit($limit);
        }

        $select = $this->getAdapter()->select()->union(array($sql, $sqlTwo))
            ->order(sprintf('%s %s', $col, $ord));
        ;
        return $select;
    }

    public function getDetalleCompraAnuncio($compraId)
    {
        $log       = Zend_Registry::get('log');
        /* Primera Query */
        $sql       = $this->getAdapter()->select()
            ->from(
                array('c' => 'compra'),
                array('fechaPago' => 'fh_confirmacion',
                'compraId' => 'c.id',
                'IdTarifa' => 'c.id_tarifa',
                'compraEstado' => 'c.estado',
                'montoTotal' => 'c.precio_total',
                'montoWeb' => 'c.precio_total',
                'montoImpreso' => 'c.precio_total_impreso',
                'tipoAnuncio' => 'c.tipo_anuncio',
                'medioPago' => 'c.medio_pago',
                'cip' => 'c.cip',
                'comprobante' => 'c.tipo_doc',
                'compraEstado' => 'c.estado',
                'nroContrato' => 'c.nro_contrato',
                'tipoContrato' => 'c.tipo_contrato',
                'usuario' => 'c.creado_por',
                'empresaId' => 'c.id_empresa',
                'enteId' => 'c.adecsys_ente_id',
                'idAnuncioWeb' => 'c.id_anuncio_web')
            )
            ->joinInner(
                array('u' => 'usuario'), 'c.creado_por = u.id',
                array('emailContacto' => 'u.email',
                'userId' => 'u.id')
            )
            ->joinInner(
                array('e' => 'empresa'), 'c.id_empresa = e.id',
                array('numeroDoc' => 'e.ruc',
                'razonSocial' => 'e.razon_social',
                'nombre_comercial' => 'e.nombre_comercial',
                'numDocumento' => 'e.ruc',
                'tipo_doc' => 'e.tipo_doc',
                'nombre_comercial' => 'e.nombre_comercial')
            )
            ->joinLeft(
                array('aw' => 'anuncio_web'), 'aw.id_compra = c.id',
                array(
                'anuncioId' => 'aw.id',
                'anuncioPuesto' => 'aw.puesto',
                'anuncioSlug' => 'aw.slug',
                'anuncioUrl' => 'aw.url_id',
                'extiendeA' => 'aw.extiende_a',
                'anuncioClase' => 'aw.tipo',
                'anuncioPrioridad' => 'aw.prioridad',
                'anuncioTarifaId' => 'aw.id_tarifa',
                'anuncioFunciones' => 'aw.funciones',
                'anuncioResponsabilidades' => 'aw.responsabilidades',
                'anuncioFechaVencimiento' => 'aw.fh_vencimiento',
                'anuncioFechaVencimientoProceso' => 'aw.fh_vencimiento_proceso',
                'anuncioFechaCreacion' => 'aw.fh_creacion',
                'anuncioEstado' => 'aw.estado',
                'anuncioPublicado' => 'aw.online',
                'anuncioImpresoId' => 'aw.id_anuncio_impreso',
                'idEmpresaMembresia' => 'aw.id_empresa_membresia',
                'tipoAnuncio' => 'aw.tipo',
                'republicado' => 'aw.republicado',
                'idTarifa' => 'aw.id_tarifa',
                'origen' => 'aw.origen'
                )
            )
            ->joinLeft(
                array('p' => 'producto'), 'aw.id_producto = p.id',
                array(
                'productoNombre' => 'p.desc',
                'idProducto' => 'p.id',
                'producto' => 'p.nombre'
                )
            )
            ->joinLeft(
                array('pto' => 'puesto'), 'aw.id_puesto = pto.id',
                array(
                'puestoTipo' => 'pto.tipo',
                'puestoIdEspecialidad' => 'pto.id_especialidad',
                'puestoAdecsysCode' => 'pto.adecsys_code',
                'nombreTipoPuesto' => 'pto.nombre'
                )
            )
            ->joinLeft(
                array('t' => 'tarifa'), 'aw.id_tarifa = t.id',
                array('medioPublicacion' => 't.medio_pub',
                'tamanoId' => 't.id_tamano')
            )
            ->joinLeft(
                array('ti' => 'tamano_impreso'), 't.id_tamano = ti.id',
                array('medida' => 'ti.medida_tarifa',
                'tamanio' => 'ti.descripcion',
                'tamanioCentimetros' => 'ti.tamano_centimetro')
            )
//            ->joinLeft(
//                array('tm' => 'tamano_impreso'),
//                't.id_tamano = tm.id',
//                array('tamanio' => 'tm.descripcion',
//                    'tamanioCentimetros' => 'tm.tamano_centimetro')
//            )
            ->joinLeft(
                array('ae' => 'adecsys_ente'), 'c.adecsys_ente_id = ae.id',
                array('codigoEnte' => 'ae.ente_cod',
                'tipoDocumento' => 'ae.doc_tipo')
            )
            ->joinLeft(
                array('ue' => 'usuario_empresa'),
                'c.creado_por = ue.id_usuario',
                array('nombreContacto' => 'ue.nombres',
                'apeMatContacto' => 'ue.apellidos',
                'apePatContacto' => 'ue.apellidos',
                'telefonoContacto' => 'ue.telefono',
                'telefonoContacto2' => 'ue.telefono2')
            )
            ->joinLeft(
                array('ai' => 'anuncio_impreso'),
                'aw.id_anuncio_impreso = ai.id',
                array('anuncioImpresoId' => 'ai.id',
                'fechaPublicConfirmada' => 'ai.fh_pub_confirmada',
                'textoAnuncioImpreso' => 'ai.texto',
                'codigoAdecsys' => 'ai.codigo_adecsys',
                'codScotAptitus' => 'ai.cod_scot_aptitus',
                'codScotTalan' => 'ai.cod_scot_talan',
                'urlScotAptitus' => 'ai.url_scot_aptitus',
                'urlScotTalan' => 'ai.url_scot_talan',
                'notaDiseno' => 'ai.nota_diseno',
                'tituloAnuncioImpreso' => 'ai.titulo',
                'cod_subseccion',
                'tipo_diseno')
            )
            ->joinLeft(
                array('pl' => 'plantilla'), 'pl.id = ai.id_plantilla',
                array('pl.codigo_scot')
            )
            ->where('c.id = ?', $compraId);
        $rsAnuncio = $this->getAdapter()->fetchRow($sql);

        $log->info("============ start SQL =============");
        $log->info($sql->assemble());
        $log->info("============ end SQL =============");

//        if($rsAnuncio['IdTarifa']==1){
//            return $rsAnuncio ;
//        }

        if (!$rsAnuncio) {
            throw new Zend_Exception('El Recurso se encuentra vacio ', 500);
        }
        //query de tipo de aviso impreso
        /* Segundo Query */
        $sql2 = $this->getAdapter()->select()
            ->from(
                array('cac' => 'compra_adecsys_codigo'), array('adecsys_code')
            )
            ->where('cac.id_compra = ?', $rsAnuncio['compraId']);

        $rs2                       = $this->getAdapter()->fetchRow($sql2);
        $rsAnuncio['adecsys_code'] = $rs2['adecsys_code'];

        /* Segundo Query */
        $sql = $this->getAdapter()->select()
            ->from(
                array('awd' => 'anuncio_web_detalle'),
                array('codigo', 'descripcion', 'valor', 'precio')
            )
            ->where('awd.id_anuncio_web = ?', $rsAnuncio['anuncioId']);

        $rs = $this->getAdapter()->fetchAssoc($sql);

        $rsAnuncio['beneficios'] = $rs;
        //////////
        if ($rsAnuncio['tipoAnuncio'] == Application_Model_AnuncioWeb::TIPO_PREFERENCIAL) {
            $rsAnuncio['anuncioPuesto'] = $rsAnuncio['tituloAnuncioImpreso'];
        }

        if ($rsAnuncio['anuncioImpresoId'] != '') {
            $sql                      = $this->getAdapter()->select()
                ->from(
                    array('aid' => 'anuncio_impreso_detalle'),
                    array('codigo', 'descripcion', 'valor', 'precio', 'adecsys_cod',
                    'adecsys_cod_envio_dos')
                )
                ->where('aid.id_anuncio_impreso = ?',
                $rsAnuncio['anuncioImpresoId']);
            $log->info("=============extracargos===================");
            $log->info($sql->assemble());
            $rs                       = $this->getAdapter()->fetchAssoc($sql);
            $rsAnuncio['extracargos'] = $rs;

            $sql                      = $this->getAdapter()->select()
                ->from(
                    array('aw' => 'anuncio_web')
                )
                ->where('aw.id_anuncio_impreso = ?',
                $rsAnuncio['anuncioImpresoId']);
            $rs                       = $this->getAdapter()->fetchAssoc($sql);
            $log->info("Anuncios web => ");
            $log->info($sql->assemble());
            $rsAnuncio['anunciosWeb'] = $rs;
            //////////
        } else {
            $rsAnuncio['extracargos'] = null;
            if (!empty($rsAnuncio['anuncioId'])) {
                $sql                      = $this->getAdapter()->select()
                    ->from(
                        array('aw' => 'anuncio_web')
                    )
                    ->where('aw.id = ?', $rsAnuncio['anuncioId']);
                $rs                       = $this->getAdapter()->fetchAssoc($sql);
                $log->info("Anuncios web => ");
                $log->info($sql->assemble());
                $rsAnuncio['anunciosWeb'] = $rs;
            } else {
                $rsAnuncio['anunciosWeb'] = array();
            }
        }

        return $rsAnuncio;
    }

    public function getDetalleCompraMembresiaByMembresia($id)
    {

        $sql = $this->getAdapter()->select()
            ->from('compra', 'id')
            ->where('id_empresa_membresia = ?', $id);

        $compra = $this->getAdapter()->fetchRow($sql);
        if (count($compra) > 0) {
            return $this->getDetalleCompraMembresia($compra['id']);
        }
        return false;
    }

    public function getDetalleCompraMembresia($compraId)
    {
        $log       = Zend_Registry::get('log');
        /* Primera Query */
        $sql       = $this->getAdapter()->select()
            ->from(
                array('c' => 'compra'),
                array('fechaPago' => 'fh_confirmacion',
                'compraId' => 'c.id',
                'IdTarifa' => 'c.id_tarifa',
                'compraEstado' => 'c.estado',
                'montoTotal' => 'c.precio_total',
                'tipoAnuncio' => 'c.tipo_anuncio',
                'medioPago' => 'c.medio_pago',
                'cip' => 'c.cip',
                'comprobante' => 'c.tipo_doc',
                'compraEstado' => 'c.estado',
                'nroContrato' => 'c.nro_contrato',
                'tipoContrato' => 'c.tipo_contrato',
                'usuario' => 'c.creado_por',
                'empresaId' => 'c.id_empresa',
                'enteId' => 'c.adecsys_ente_id',
                'IdEmprMemb' => 'c.id_empresa_membresia',
                'tokenCompra' => 'c.token'
                )
            )
            ->joinInner(
                array('car' => 'compra_adecsys_ruc'), 'c.id = car.id_compra',
                array('IdComp' => 'car.id_compra',
                'Nruc' => 'car.ruc',
                'Nraz_social' => 'car.razon_social',
                'Ntip_via' => 'car.tipo_via',
                'fh_cr' => 'car.fh_creacion',
                'creador' => 'car.creado_por'
                )
            )
            ->joinLeft(
                array('cac' => 'compra_adecsys_codigo'), 'cac.id_compra = c.id',
                array(
                'codigoAdecsys' => 'cac.adecsys_code',
                //'tipoDocumentoEnte' => 'ae.doc_tipo'
                )
            )
            ->joinInner(
                array('u' => 'usuario'), 'c.creado_por = u.id',
                array(
                'emailContacto' => 'u.email',
                'nombreContacto' => 'u.nombre',
                'apellidoContacto' => 'u.apellido',
                'userId' => 'u.id')
            )
            ->joinInner(
                array('e' => 'empresa'), 'c.id_empresa = e.id',
                array('numeroDoc' => 'e.ruc',
                'razonSocial' => 'e.razon_social',
                'nombre_comercial' => 'e.nombre_comercial',
                'numDocumento' => 'e.ruc',
                'nombre_comercial' => 'e.nombre_comercial')
            )
            ->joinInner(
                array('em' => 'empresa_membresia'),
                'c.id_empresa_membresia = em.id',
                array(
                'fechaCreacionMembresia' => 'em.fh_creacion',
                'fechaInicioMembresia' => 'em.fh_inicio_membresia',
                'fechaFinMembresia' => 'em.fh_fin_membresia',
                'token' => 'em.token',
                'EmEstado' => 'em.estado',
                'nroContratoMembresia' => 'em.nro_contrato',
                'monto' => 'em.monto',
                )
            )
            ->joinInner(
                array('m' => 'membresia'), 'em.id_membresia = m.id',
                array(
                'nombreMembresia' => 'm.nombre',
                'idMembresia' => 'm.id',
                'descripcionMembresia' => 'm.descripcion',
                )
            )
            ->joinInner(
                array('t' => 'tarifa'), 'c.id_tarifa = t.id',
                array('medioPublicacion' => 't.medio_pub',
                'IdTarifa' => 't.id',
                'tamanoId' => 't.id_tamano')
            )
            ->joinInner(
                array('p' => 'producto'), 't.id_producto = p.id',
                array('productoNombre' => 'p.desc',
                'idProducto' => 't.id_producto',
                'producto' => 'p.nombre',
                'Tipoproducto' => 'p.tipo')
            )
            ->joinLeft(
                array('ae' => 'adecsys_ente'), 'c.adecsys_ente_id = ae.id',
                array(
                'codigoEnte' => 'ae.ente_cod',
                'tipoDocumento' => 'ae.doc_tipo',
                )
            )
            ->joinLeft(
                array('ue' => 'usuario_empresa'),
                'c.creado_por = ue.id_usuario',
                array('nombreContacto' => 'ue.nombres',
                'apeMatContacto' => 'ue.apellidos',
                'apePatContacto' => 'ue.apellidos',
                'telefonoContacto' => 'ue.telefono',
                'telefonoContacto2' => 'ue.telefono2')
            )
            ->where('c.id = ?', $compraId);
        $rsAnuncio = $this->getAdapter()->fetchRow($sql);

        $log->info("============ start SQL =============");
        $log->info($sql->assemble());
        $log->info("============ end SQL =============");

        return $rsAnuncio;
    }

    public function getDetalleCompraMembresiaByToken($token)
    {

        /* Primera Query */
        $sql = $this->getAdapter()->select()
            ->from(
                array('c' => 'compra'),
                array('fechaPago' => 'fh_confirmacion',
                'compraId' => 'c.id',
                'montoTotal' => 'c.precio_total',
                //'tipoAnuncio' => 'c.tipo_anuncio',
                'medioPago' => 'c.medio_pago',
                'cip' => 'c.cip',
                'IdEmprMemb' => 'c.id_empresa_membresia',
                'usuario' => 'c.creado_por',
                'estadoCompra' => 'c.estado',
                'tokenCompra' => 'c.token',
                )
            )
            ->joinLeft(
                array('ae' => 'adecsys_ente'), 'c.adecsys_ente_id = ae.id',
                array(
                'codigoEnte' => 'ae.ente_cod',
                //'tipoDocumentoEnte' => 'ae.doc_tipo'
                )
            )
            ->joinLeft(
                array('cac' => 'compra_adecsys_codigo'), 'cac.id_compra = c.id',
                array(
                'codigoAdecsys' => 'cac.adecsys_code',
                //'tipoDocumentoEnte' => 'ae.doc_tipo'
                )
            )
            ->joinInner(
                array('u' => 'usuario'), 'c.creado_por = u.id',
                array('emailContacto' => 'u.email',
                'userId' => 'u.id')
            )
            ->joinInner(
                array('e' => 'empresa'), 'c.id_empresa = e.id',
                array('numeroDoc' => 'e.ruc',
                'razonSocial' => 'e.razon_social',
                'nombre_comercial' => 'e.nombre_comercial',
                'numDocumento' => 'e.ruc',
                'nombre_comercial' => 'e.nombre_comercial')
            )
            ->joinInner(
                array('em' => 'empresa_membresia'),
                'c.id_empresa_membresia = em.id',
                array(
                'em_idempresa' => 'em.id_empresa',
                'token' => 'em.token',
                'nroContratoMembresia' => 'em.nro_contrato',
                'monto' => 'em.monto',
                'fechaInicioMembresia' => 'em.fh_inicio_membresia',
                'fechaFinMembresia' => 'em.fh_fin_membresia',
                )
            )
            ->joinInner(
                array('m' => 'membresia'), 'em.id_membresia = m.id',
                array(
                'nombreMembresia' => 'm.nombre',
                'descripcionMembresia' => 'm.descripcion',
                )
            )
            ->where('em.token = ?', $token);

        $rsAnuncio = $this->getAdapter()->fetchRow($sql);

        if ($rsAnuncio) {
            $sql2 = $this->getAdapter()->select()
                ->from(array(
                    'e' => 'empresa',
                ))
                ->joinLeft(
                    array('c' => 'compra'), 'c.creado_por = e.id_usuario',
                    array(
                    'razonSocial' => 'e.razon_social',
                    )
                )
                ->where('c.id = ?', $rsAnuncio['compraId']);

            $rsCompraEmpresa                       = $this->getAdapter()->fetchRow($sql2);
            $rsAnuncio['razonSocialCompraEmpresa'] = $rsCompraEmpresa['razonSocial'];
        }



        return $rsAnuncio;
    }

    public function getDetalleCompraAnuncioPreferencial($compraId)
    {
        $sql       = $this->getAdapter()->select()
            ->from(
                array('c' => 'compra'),
                array('fechaPago' => 'fh_confirmacion',
                'compraId' => 'c.id',
                'montoTotal' => 'c.precio_total',
                'medioPago' => 'c.medio_pago',
                'cip' => 'c.cip',
                'comprobante' => 'c.tipo_doc',
                'compraEstado' => 'c.estado',
                'usuario' => 'c.creado_por',
                'empresaId' => 'c.id_empresa',
                'enteId' => 'c.adecsys_ente_id')
            )
            ->joinInner(
                array('u' => 'usuario'), 'c.creado_por = u.id',
                array('emailContacto' => 'u.email')
            )
            ->joinInner(
                array('e' => 'empresa'), 'c.id_empresa = e.id',
                array('numeroDoc' => 'e.ruc',
                'razonSocial' => 'e.razon_social',
                'nombre_comercial' => 'e.nombre_comercial',
                'numDocumento' => 'e.ruc',
                'nombre_comercial' => 'e.nombre_comercial')
            )
            ->joinInner(
                array('aw' => 'anuncio_web'), 'aw.id_compra = c.id',
                array('anuncioId' => 'aw.id',
                'anuncioPuesto' => 'aw.puesto',
                'anuncioSlug' => 'aw.slug',
                'anuncioUrl' => 'aw.url_id',
                'extiendeA' => 'aw.extiende_a',
                'anuncioClase' => 'aw.tipo',
                'anuncioTarifaId' => 'aw.id_tarifa',
                'anuncioFunciones' => 'aw.funciones',
                'anuncioResponsabilidades' => 'aw.responsabilidades',
                'anuncioFechaVencimiento' => 'aw.fh_vencimiento',
                'anuncioFechaVencimientoProceso' => 'aw.fh_vencimiento_proceso',
                'medioPublicacion' => 'aw.medio_publicacion',
                'anuncioImpresoId' => 'aw.id_anuncio_impreso')
            )
            ->joinLeft(
                array('tp' => 'puesto'), 'tp.id = aw.id_puesto',
                array(
                //'nombreTipoPuesto' => 'tp.nombre',
                'tipoPuesto' => 'tp.tipo')
            )
            ->joinLeft(
                array('p' => 'producto'), 'aw.id_producto = p.id',
                array('productoNombre' => 'p.desc',
                'producto' => 'p.nombre')
            )
            ->joinLeft(
                array('pto' => 'puesto'), 'aw.id_puesto = pto.id',
                array(
                'nombreTipoPuesto' => 'pto.nombre',
                'puestoTipo' => 'pto.tipo',
                'puestoIdEspecialidad' => 'pto.id_especialidad',
                'puestoAdecsysCode' => 'pto.adecsys_code'
                )
            )
            ->joinLeft(
                array('t' => 'tarifa'), 'aw.id_tarifa = t.id',
                array('medioPublicacion' => 't.medio_pub')
            )
            ->joinLeft(
                array('ae' => 'adecsys_ente'), 'c.adecsys_ente_id = ae.id',
                array('codigoEnte' => 'ae.ente_cod',
                'tipoDocumento' => 'ae.doc_tipo')
            )
            ->joinLeft(
                array('ue' => 'usuario_empresa'),
                'c.creado_por = ue.id_usuario',
                array('nombreContacto' => 'ue.nombres',
                'apeMatContacto' => 'ue.apellidos',
                'apePatContacto' => 'ue.apellidos',
                'telefonoContacto' => 'ue.telefono',
                'telefonoContacto2' => 'ue.telefono2')
            )
            ->joinLeft(
                array('ai' => 'anuncio_impreso'),
                'aw.id_anuncio_impreso = ai.id',
                array('anuncioImpresoId' => 'ai.id',
                'fechaPublicConfirmada' => 'ai.fh_pub_confirmada',
//                    'fechaPublicConfirmada'=>'DATE_FORMAT(ai.fh_pub_confirmada,"%d/%m/%Y")' ,
                'textoAnuncioImpreso' => 'ai.texto')
            )
            ->where('c.id = ?', $compraId);
        $rsAnuncio = $this->getAdapter()->fetchRow($sql);
        $sql       = $this->getAdapter()->select()
            ->from(
                array('awd' => 'anuncio_web_detalle'),
                array('codigo', 'descripcion', 'valor', 'precio')
            )
            ->where('awd.id_anuncio_web = ?', $rsAnuncio['anuncioId']);
        $rs        = $this->getAdapter()->fetchAssoc($sql);

        $rsAnuncio['beneficios'] = $rs;
        if ($rsAnuncio['anuncioImpresoId'] != '') {
            $sql                      = $this->getAdapter()->select()
                ->from(
                    array('aid' => 'anuncio_impreso_detalle'),
                    array('codigo', 'descripcion', 'valor', 'precio', 'adecsys_cod',
                    'adecsys_cod_envio_dos')
                )
                ->where('aid.id_anuncio_impreso = ?',
                $rsAnuncio['anuncioImpresoId']);
            //echo $sql->assemble(); exit;
            $rs                       = $this->getAdapter()->fetchAssoc($sql);
            $rsAnuncio['extracargos'] = $rs;
        } else {
            $rsAnuncio['extracargos'] = null;
        }
        return $rsAnuncio;
    }

    public function getPaginatorPagados($id, $col, $ord)
    {
        $limit  = $this->_config->paginadoresEmpresa->estadoCuenta->pagados->items;
        $ord    = $ord == 'ASC' || $ord == 'DESC' ? $ord : 'DESC';
        $colMap = array(
            'fecha' => 'fechaCreacion',
            'aviso' => 'puestoAnuncio',
            'tipo' => 'productoNombre',
            'forma-pago' => 'medioPago',
            'monto' => 'precioTotal',
            'comprobante' => 'comprobante'
        );
        $column = array_key_exists($col, $colMap) ? $colMap[$col] : '';
        $r      = $this->getEstadoCuentaPagados($id, null, $column, $ord);
        //echo $r->assemble();
        //exit;
        $p      = Zend_Paginator::factory($r);
        return $p->setItemCountPerPage($limit);
    }

    public function getPaginatorEnProceso($id, $col, $ord)
    {
        $limit  = $this->_config->paginadoresEmpresa->estadoCuenta->enProceso->items;
        $ord    = $ord == 'ASC' || $ord == 'DESC' ? $ord : 'DESC';
        $colMap = array(
            'fecha' => 'fechaCreacion',
            'aviso' => 'puestoAnuncio',
            'tipo' => 'productoNombre',
            'estado' => 'estadoCompra',
            'compra' => 'cipCompra',
        );
        $column = array_key_exists($col, $colMap) ? $colMap[$col] : '';
        $p      = Zend_Paginator::factory($this->getEstadoCuentaEnProceso($id,
                    null, $column, $ord));
        return $p->setItemCountPerPage($limit);
    }

    public function perteneceCompraEmpresa($idCompra, $idEmpresa)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('c' => 'compra'), array('id' => 'id')
            )
            ->where('id = ?', $idCompra)
            ->where('id_empresa = ?', $idEmpresa)
            ->limit(1);
        return $this->getAdapter()->fetchOne($sql);
    }

    public function verificarPagado($idCompra)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('compra'), array('estado')
            )
            ->where("id = ?", $idCompra)
            ->where("estado = ?", self::ESTADO_PAGADO)
            ->limit(1);
        return $this->getAdapter()->fetchOne($sql) == self::ESTADO_PAGADO;
    }

    public function verificarUsuarioActivoPorCompra($idCompra)
    {
        $sql   = $this->getAdapter()->select()
            ->from(
                array('u' => 'usuario'), array('idUsuario' => 'u.activo')
            )
            ->joinInner(
                array('c' => 'compra'), 'c.creado_por = u.id', array()
            )
            ->where("c.id = ?", $idCompra)
            ->where("u.activo = ?", 1)
            ->limit(1);
        $valor = $this->getAdapter()->fetchAll($sql);
        $cant  = count($valor);
        if ($cant > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function assignAdecsysEnte($adecsysEnteId, $empresaId)
    {
        $where = $this->getAdapter()->quoteInto(
            'id_empresa = ?', (int) $empresaId);

        $this->update(
            array('adecsys_ente_id' => $adecsysEnteId), $where);
    }

    public function assignAdecsysEntePerfil($adecsysEnteId, $postulanteId)
    {
        $where = $this->getAdapter()->quoteInto(
            'id_postulante = ?', (int) $postulanteId);

        $this->update(
            array('adecsys_ente_id' => $adecsysEnteId), $where);
    }

    //Obtiene las compras que no tiene registro en la tabla compra_adecsys_codigo
    public function obtenerComprasSinAdecsys()
    {
        $sql = $this->getAdapter()->select()->from(array('c' => 'compra'),
                array('id', 'id_tarifa', 't.medio_pub'))
            ->joinLeft(array('cac' => 'compra_adecsys_codigo'),
                'c.id = cac.id_compra', null)
            ->joinInner(array('t' => 'tarifa'), 't.id = c.id_tarifa', null)
            ->where('cac.id is null')
            ->where('c.estado = ?', self::ESTADO_PAGADO)
            ->where('c.id_tarifa <> ?', Application_Model_Tarifa::GRATUITA)
            ->where('c.medio_pago in (?)',
                array(self::FORMA_PAGO_VISA, self::FORMA_PAGO_PAGO_EFECTIVO,
                self::FORMA_PAGO_MASTER_CARD, self::FORMA_PAGO_CREDITO))
            ->order('c.id asc');

        return $this->getAdapter()->fetchAll($sql);
    }

    //Obtiene las registros de la tabla compra_adecsys_codigo que no generaron cod de adecsys u
    //ocurrió algún error por parte de la BD de Adecsys (Informix)
    public function obtenerRegistroFaltantesCompraAdecsysCodigo()
    {

        $sql = $this->getAdapter()->select()->from(array('cac' => 'compra_adecsys_codigo'),
                array(
                'id_cac' => 'cac.id', 'id_compra' => 'c.id', 'p.tipo',
                'mediopub' => 'cac.medio_publicacion', 'p.tipo', 'c.adecsys_ente_id',
                'c.id_empresa'
            ))
            ->joinInner(array('c' => 'compra'), 'c.id = cac.id_compra', null)
            ->joinInner(array('t' => 'tarifa'), 't.id = c.id_tarifa', null)
            ->joinInner(array('p' => 'producto'), 'p.id = t.id_producto', null)
            ->where('cac.adecsys_code is null or cac.adecsys_code = 0 or cac.adecsys_code like ?',
                '%Informix%')
            ->where('c.estado = ?', self::ESTADO_PAGADO)
            ->where('c.id_tarifa <> ?', Application_Model_Tarifa::GRATUITA)
            ->where('c.medio_pago in (?)',
                array(self::FORMA_PAGO_VISA, self::FORMA_PAGO_PAGO_EFECTIVO,
                self::FORMA_PAGO_MASTER_CARD, self::FORMA_PAGO_CREDITO, self::FORMA_PAGO_POS))
            ->where('DATEDIFF(CURDATE(),c.fh_confirmacion) <= ?', 10)
            ->where('year(c.fh_confirmacion) >= ?', date('Y'))
            ->where('t.meses is null')
            ->where('c.id_empresa_membresia is null')
            //->where('cac.id = ?', 76512)
            ->order('cac.id desc');
        //->limit(2);
//echo $sql;exit;
        return $this->getAdapter()->fetchAll($sql);
    }

    //Función que obtiene datos para realizar el envío de los datos cuando un adecsys llega al 5 reproceso
    public function obtenerDatosEnvioEmail($idCompra, $idCac)
    {

        $sql = $this->getAdapter()->select()->from(array('c' => 'compra'),
                    array('precio' => 'c.precio_total', 'id_tarifa', 'id_producto' => 'p.id',
                    'e.ruc', 'e.razon_social', 'ue.nombres',
                    'ue.apellidos', 'adecsys_ente_id' => 'ae.id'))
                ->joinInner(array('cac' => 'compra_adecsys_codigo'),
                    'c.id = cac.id_compra', null)
                ->joinInner(array('t' => 'tarifa'), 't.id = c.id_tarifa', null)
                ->joinInner(array('p' => 'producto'), 'p.id = t.id_producto',
                    null)
                ->joinInner(array('e' => 'empresa'), 'e.id = c.id_empresa', null)
                ->joinInner(array('ue' => 'usuario_empresa'),
                    'ue.id_usuario = e.id_usuario', null)
                ->joinLeft(array('ae' => 'adecsys_ente'),
                    'ae.doc_numero = e.ruc', null)
                ->where('c.id = ?', $idCompra)
                ->where('cac.id = ?', $idCac)
                ->order('cac.id desc')->limit(1);

        return $this->getAdapter()->fetchAll($sql);
    }

    //Función que obtiene datos para realizar el envío de los datos cuando un adecsys llega al 5 reproceso
    public function obtenerDatosEnvioPerfilEmail($idCompra, $idCac)
    {

        $sql = $this->getAdapter()->select()->from(array('c' => 'compra'),
                    array('precio' => 'c.precio_total', 'id_tarifa', 'id_producto' => 'p.id',
                    'pos.num_doc', 'pos.nombres',
                    'apellidos' => "CONCAT(pos.`apellido_paterno`,' ',pos.`apellido_materno`)",
                    'adecsys_ente_id' => 'ae.id'))
                ->joinInner(array('cac' => 'compra_adecsys_codigo'),
                    'c.id = cac.id_compra', null)
                ->joinInner(array('t' => 'tarifa'), 't.id = c.id_tarifa', null)
                ->joinInner(array('p' => 'producto'), 'p.id = t.id_producto',
                    null)
                ->joinInner(array('pos' => 'postulante'),
                    'pos.id = c.id_postulante', null)
                ->joinLeft(array('ae' => 'adecsys_ente'),
                    'ae.doc_numero = pos.num_doc', null)
                ->where('c.id = ?', $idCompra)
                ->where('cac.id = ?', $idCac)
                ->order('cac.id desc')->limit(1);

        return $this->getAdapter()->fetchAll($sql);
    }

    //Obtiene los registro que no se han generado cod SCOT
    public function obtenerRegistroFaltantesCompraScot()
    {

        $sql = $this->getAdapter()->select()->from(array('ai' => 'anuncio_impreso'),
                array('id_cac' => 'cac.id', 'id_compra' => 'c.id', 'adecsys_code' => 'cac.adecsys_code',
                'medPub' => 'cac.medio_publicacion'))
            ->joinInner(array('c' => 'compra'), 'c.id = ai.id_compra', null)
            ->joinInner(array('t' => 'tarifa'), 't.id = c.id_tarifa', null)
            ->joinInner(array('p' => 'producto'), 't.id_producto = p.id', null)
            ->joinInner(array('cac' => 'compra_adecsys_codigo'),
                'cac.id_compra = c.id', null)
            ->where('p.tipo = ?', self::TIPO_PREFERENCIAL)
            ->where('c.estado = ?', self::ESTADO_PAGADO)
            ->where('c.id_tarifa <> ?', Application_Model_Tarifa::GRATUITA)
            ->where('year(c.fh_confirmacion) >= ?', date('Y'))
            ->where('cac.medio_publicacion <> ?',
                Application_Model_CompraAdecsysCodigo::MEDIO_PUB_TALAN_COMBO)
            ->where('(cac.adecsys_code is not null and cac.adecsys_code <> 0 and cac.adecsys_code not like ?)',
                '%Informix%')
            ->where('(cod_scot_aptitus IS NULL AND cod_scot_talan IS NULL)')
            ->where('ai.cod_scot_aptitus is null')
            ->where('c.medio_pago in (?)',
                array(self::FORMA_PAGO_VISA, self::FORMA_PAGO_PAGO_EFECTIVO,
                self::FORMA_PAGO_MASTER_CARD, self::FORMA_PAGO_CREDITO))
            ->where('DATEDIFF(CURDATE(),c.fh_confirmacion) <= ?', 10)
            ->where('t.meses is null')
            //->where('c.id = ?', $id)
            ->order('c.id desc');

        return $this->getAdapter()->fetchAll($sql);
    }

    public function getDetalleCompraPerfil($compraId)
    {
        $log      = Zend_Registry::get('log');
        $sql      = $this->getAdapter()->select()
            ->from(
                array('c' => 'compra'),
                array('fechaPago' => 'fh_confirmacion',
                'compraId' => 'c.id',
                'postulanteId' => 'c.id_postulante',
                'compraEstado' => 'c.estado',
                'montoTotal' => 'c.precio_total',
                'tipoAnuncio' => 'c.tipo_anuncio',
                'medioPago' => 'c.medio_pago',
                'cip' => 'c.cip',
                'comprobante' => 'c.tipo_doc',
                'compraEstado' => 'c.estado',
                'nroContrato' => 'c.nro_contrato',
                'tipoContrato' => 'c.tipo_contrato',
                'usuario' => 'c.creado_por',
                'empresaId' => 'c.id_empresa',
                'enteId' => 'c.adecsys_ente_id')
            )
            ->joinInner(
                array('u' => 'usuario'), 'c.creado_por = u.id',
                array('emailContacto' => 'u.email',
                'userId' => 'u.id')
            )
            ->joinInner(
                array('pd' => 'perfil_destacado'), 'pd.id_compra = c.id',
                array(
                'perfilId' => 'pd.id',
                'perfilTarifaId' => 'pd.id_tarifa',
                'perfilFechaInicio' => 'pd.fh_inicio',
                'perfilFechaFin' => 'pd.fh_fin',
                'inicio' => "CONCAT(DATE_FORMAT(pd.fh_inicio,'%d'),'-',DATE_FORMAT(pd.fh_inicio,'%m'),'-',YEAR(pd.fh_inicio))",
                'fin' => "CONCAT(DATE_FORMAT(pd.fh_fin,'%d'),'-',DATE_FORMAT(pd.fh_fin,'%m'),'-',YEAR(pd.fh_fin))",
                'perfilFechaCreacion' => 'pd.fh_creacion',
                'perfilEstado' => 'pd.estado',
                'idTarifa' => 'pd.id_tarifa'
                )
            )
            ->joinLeft(
                array('p' => 'producto'), 'pd.id_producto = p.id',
                array(
                'productoNombre' => 'p.nombre',
                'idProducto' => 'p.id',
                'producto' => 'p.nombre'
                )
            )
            ->joinLeft(
                array('t' => 'tarifa'), 'pd.id_tarifa = t.id',
                array('medioPublicacion' => 't.medio_pub',
                'meses' => new Zend_Db_Expr("CASE t.meses WHEN 12 THEN '1 año' when 6 then '6 meses' when 3 then '3 meses' END"))
            )
            ->joinLeft(
                array('ae' => 'adecsys_ente'), 'c.adecsys_ente_id = ae.id',
                array('codigoEnte' => 'ae.ente_cod',
                'tipoDocumento' => 'ae.doc_tipo')
            )
            ->joinLeft(
                array('pos' => 'postulante'), 'c.creado_por = pos.id_usuario',
                array('nombreContacto' => 'pos.nombres',
                'apeMatContacto' => 'pos.apellido_materno',
                'apePatContacto' => 'pos.apellido_paterno',
                'telefonoContacto' => 'pos.telefono',
                'telefonoContacto2' => 'pos.celular',
                'razonSocial' => "UPPER(CONCAT(pos.apellido_paterno,' ',pos.apellido_materno,', ',pos.nombres))",
                'nombre_comercial' => "UPPER(CONCAT(pos.apellido_paterno,' ',pos.apellido_materno,', ',pos.nombres))",
                'numeroDoc' => 'ae.doc_numero',
                'numDocumento' => 'ae.doc_numero')
            )
            ->where('c.id = ?', $compraId);
        $rsPerfil = $this->getAdapter()->fetchRow($sql);

        if (!$rsPerfil) {
            throw new Zend_Exception('El Recurso se encuentra vacio ', 500);
        }

        return $rsPerfil;
    }

    public function perteneceCompraPostulante($idCompra, $idPostulante)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('c' => 'compra'), array('id' => 'id')
            )
            ->where('id = ?', $idCompra)
            ->where('id_postulante = ?', $idPostulante)
            ->limit(1);
        return $this->getAdapter()->fetchOne($sql);
    }

    //Obtiene la fecha en que vence el CIP
    public function getFechaMaxPagoEfectivo($idCompra)
    {

        $sql = $this->getAdapter()
            ->select()->from($this->_name, array('fh_expiracion_cip'))
            ->where('id = ?', $idCompra);

        return $this->getAdapter()->fetchOne($sql);
    }

    //Obtiene las registros de la tabla compra_adecsys_codigo que no generaron cod de adecsys u
    //ocurrió algún error por parte de la BD de Adecsys (Informix)
    public function obtenerRegistroFaltantesPerfilCompraAdecsysCodigo($home)
    {

        $sql = $this->getAdapter()->select()->from(array('cac' => 'compra_adecsys_codigo'),
                array('id_cac' => 'cac.id', 'id_compra' => 'c.id', 'p.tipo',
                'mediopub' => 'cac.medio_publicacion', 'p.tipo', 'c.id_postulante',
                'c.adecsys_ente_id'))
            ->joinInner(array('c' => 'compra'), 'c.id = cac.id_compra', null)
            ->joinInner(array('t' => 'tarifa'), 't.id = c.id_tarifa', null)
            ->joinInner(array('p' => 'producto'), 'p.id = t.id_producto', null)
            ->where('cac.adecsys_code is null or cac.adecsys_code = 0 or cac.adecsys_code like ?',
                '%Informix%')
            ->where('c.estado = ?', self::ESTADO_PAGADO)
            ->where('c.id_tarifa <> ?', Application_Model_Tarifa::GRATUITA)
            ->where('c.medio_pago in (?)',
                array(self::FORMA_PAGO_VISA, self::FORMA_PAGO_PAGO_EFECTIVO,
                self::FORMA_PAGO_MASTER_CARD, self::FORMA_PAGO_CREDITO))
            ->where('DATEDIFF(CURDATE(),c.fh_confirmacion) <= ?', $home)
            //->where('year(c.fh_confirmacion) >= ?', date('Y'))
            ->where('t.meses is not null')
            //->where('cac.id = ?', 75698)
            ->order('cac.id desc');
        return $this->getAdapter()->fetchAll($sql);
    }

    //Obtiene la información de la compra de un aviso para generar el cod Adecsys con el reproceso
    public function getDetalleCompraAnuncioReproceso($compraId)
    {

        $log       = Zend_Registry::get('log');
        $sql       = $this->getAdapter()->select()
            ->from(
                array('c' => 'compra'),
                array('fechaPago' => 'fh_confirmacion',
                'compraId' => 'c.id',
                'compraEstado' => 'c.estado',
                'montoTotal' => 'c.precio_total',
                'tipoAnuncio' => 'c.tipo_anuncio',
                'medioPago' => 'c.medio_pago',
                'cip' => 'c.cip',
                'comprobante' => 'c.tipo_doc',
                'compraEstado' => 'c.estado',
                'nroContrato' => 'c.nro_contrato',
                'tipoContrato' => 'c.tipo_contrato',
                'usuario' => 'c.creado_por',
                'empresaId' => 'c.id_empresa',
                'enteId' => 'c.adecsys_ente_id')
            )
            ->joinInner(
                array('u' => 'usuario'), 'c.creado_por = u.id',
                array('emailContacto' => 'u.email',
                'userId' => 'u.id')
            )
            ->joinInner(
                array('e' => 'empresa'), 'c.id_empresa = e.id',
                array('numeroDoc' => 'ae.doc_tipo',
                'razonSocial' => 'e.razon_social',
                'nombre_comercial' => 'e.nombre_comercial',
                'numDocumento' => 'ae.doc_numero',
                'nombre_comercial' => 'e.nombre_comercial')
            )
            ->joinInner(
                array('aw' => 'anuncio_web'), 'aw.id_compra = c.id',
                array(
                'anuncioId' => 'aw.id',
                'anuncioPuesto' => 'aw.puesto',
                'anuncioSlug' => 'aw.slug',
                'anuncioUrl' => 'aw.url_id',
                'extiendeA' => 'aw.extiende_a',
                'anuncioClase' => 'aw.tipo',
                'anuncioTarifaId' => 'aw.id_tarifa',
                'anuncioFunciones' => 'aw.funciones',
                'anuncioResponsabilidades' => 'aw.responsabilidades',
                'anuncioFechaVencimiento' => 'aw.fh_vencimiento',
                'anuncioFechaVencimientoProceso' => 'aw.fh_vencimiento_proceso',
                'anuncioFechaCreacion' => 'aw.fh_creacion',
                'anuncioEstado' => 'aw.estado',
                'anuncioPublicado' => 'aw.online',
                'anuncioImpresoId' => 'aw.id_anuncio_impreso',
                'idEmpresaMembresia' => 'aw.id_empresa_membresia',
                'tipoAnuncio' => 'aw.tipo',
                'republicado' => 'aw.republicado',
                'idTarifa' => 'aw.id_tarifa',
                'origen' => 'aw.origen'
                )
            )
            ->joinLeft(
                array('p' => 'producto'), 'aw.id_producto = p.id',
                array(
                'productoNombre' => 'p.desc',
                'idProducto' => 'p.id',
                'producto' => 'p.nombre'
                )
            )
            ->joinLeft(
                array('pto' => 'puesto'), 'aw.id_puesto = pto.id',
                array(
                'puestoTipo' => 'pto.tipo',
                'puestoIdEspecialidad' => 'pto.id_especialidad',
                'puestoAdecsysCode' => 'pto.adecsys_code',
                'nombreTipoPuesto' => 'pto.nombre'
                )
            )
            ->joinLeft(
                array('t' => 'tarifa'), 'aw.id_tarifa = t.id',
                array('medioPublicacion' => 't.medio_pub',
                'tamanoId' => 't.id_tamano')
            )
            ->joinLeft(
                array('ti' => 'tamano_impreso'), 't.id_tamano = ti.id',
                array('medida' => 'ti.medida_tarifa',
                'tamanio' => 'ti.descripcion',
                'tamanioCentimetros' => 'ti.tamano_centimetro')
            )
            ->joinLeft(
                array('ae' => 'adecsys_ente'), 'c.adecsys_ente_id = ae.id',
                array('codigoEnte' => 'ae.ente_cod',
                'tipoDocumento' => 'ae.doc_tipo')
            )
            ->joinLeft(
                array('ue' => 'usuario_empresa'),
                'c.creado_por = ue.id_usuario',
                array('nombreContacto' => 'ue.nombres',
                'apeMatContacto' => 'ue.apellidos',
                'apePatContacto' => 'ue.apellidos',
                'telefonoContacto' => new Zend_Db_Expr("IFNULL(ue.telefono,'000000')"),
                'telefonoContacto2' => 'ue.telefono2')
            )
            ->joinLeft(
                array('ai' => 'anuncio_impreso'),
                'aw.id_anuncio_impreso = ai.id',
                array('anuncioImpresoId' => 'ai.id',
                'fechaPublicConfirmada' => 'ai.fh_pub_confirmada',
                'textoAnuncioImpreso' => 'ai.texto',
                'codigoAdecsys' => 'ai.codigo_adecsys',
                'codScotAptitus' => 'ai.cod_scot_aptitus',
                'codScotTalan' => 'ai.cod_scot_talan',
                'urlScotAptitus' => 'ai.url_scot_aptitus',
                'urlScotTalan' => 'ai.url_scot_talan',
                'notaDiseno' => 'ai.nota_diseno',
                'tituloAnuncioImpreso' => 'ai.titulo',
                'cod_subseccion',
                'tipo_diseno')
            )
            ->joinLeft(
                array('pl' => 'plantilla'), 'pl.id = ai.id_plantilla',
                array('pl.codigo_scot')
            )
            ->where('c.id = ?', $compraId);
        $rsAnuncio = $this->getAdapter()->fetchRow($sql);
//echo $sql;exit;
        $log->info("============ start SQL =============");
        $log->info($sql->assemble());
        $log->info("============ end SQL =============");

        if (!$rsAnuncio) {
            throw new Zend_Exception('El Recurso se encuentra vacio ', 500);
        }

        $sql = $this->getAdapter()->select()
            ->from(
                array('awd' => 'anuncio_web_detalle'),
                array('codigo', 'descripcion', 'valor', 'precio')
            )
            ->where('awd.id_anuncio_web = ?', $rsAnuncio['anuncioId']);

        $rs = $this->getAdapter()->fetchAssoc($sql);

        $log->info($sql->assemble());

        $rsAnuncio['beneficios'] = $rs;
        if ($rsAnuncio['tipoAnuncio'] == Application_Model_AnuncioWeb::TIPO_PREFERENCIAL) {
            $rsAnuncio['anuncioPuesto'] = $rsAnuncio['tituloAnuncioImpreso'];
        }

        if ($rsAnuncio['anuncioImpresoId'] != '') {
            $sql                      = $this->getAdapter()->select()
                ->from(
                    array('aid' => 'anuncio_impreso_detalle'),
                    array('codigo', 'descripcion', 'valor', 'precio', 'adecsys_cod',
                    'adecsys_cod_envio_dos')
                )
                ->where('aid.id_anuncio_impreso = ?',
                $rsAnuncio['anuncioImpresoId']);
            $log->info("=============extracargos===================");
            $log->info($sql->assemble());
            $rs                       = $this->getAdapter()->fetchAssoc($sql);
            $rsAnuncio['extracargos'] = $rs;

            $sql = $this->getAdapter()->select()
                ->from(
                    array('aw' => 'anuncio_web')
                )
                ->where('aw.id_anuncio_impreso = ?',
                $rsAnuncio['anuncioImpresoId']);
            $rs  = $this->getAdapter()->fetchAssoc($sql);

            //      echo $sql;exit;

            $log->info("Anuncios web => ");
            $log->info($sql->assemble());
            $rsAnuncio['anunciosWeb'] = $rs;
        } else {
            $rsAnuncio['extracargos'] = null;
        }

        return $rsAnuncio;
    }

    public function registraNroContratoMembresia($token, $nroContrato, $ip = '',
                                                 $idemp = null, $idempMe = null)
    {

        //$obj = new App_Db_Table_Abstract();
        $db = $this->getAdapter();

        try {
            $filter      = new Zend_Filter_Alnum();
            $nroContrato = $filter->filter($nroContrato);
            $EmMe        = new Application_Model_EmpresaMembresia();


            $empMem = $EmMe->getEmpresaMemSig($idemp, $idempMe);

            if (count($empMem) > 0) {
                $fechainicio = $empMem[0]['fh_fin'];
                $fechafin    = strtotime('+1 year', strtotime($fechainicio));
                $fechafin    = date('Y-m-d H:i:s', $fechafin);
            } else {
                $fechainicio = date('Y-m-d H:i:s');
                $fechafin    = strtotime('+1 year', strtotime($fechainicio));
                $fechafin    = date('Y-m-d H:i:s', $fechafin);
            }

            $where = $this->getAdapter()->quoteInto('token = ?', $token);
            $where .= " AND estado = 'pagado' ";

            $sql = "UPDATE empresa_membresia ".
                " SET nro_contrato = '$nroContrato', ip = '$ip' ".
                ', fh_inicio_membresia = "'.$fechainicio.'"'.
                ', fh_fin_membresia = "'.$fechafin.'"'.
                ' WHERE '.$where;

            $db->query($sql);
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    //Reproceso: Membresía Web Obtiene las registros de la tabla compra_adecsys_codigo que no generaron cod de adecsys u
    //ocurrió algún error por parte de la BD de Adecsys (Informix)
    public function obtenerRegistroFaltantesMembresiaCompraAdecsysCodigo()
    {

        $sql = $this->getAdapter()->select()->from(array('cac' => 'compra_adecsys_codigo'),
                array('id_cac' => 'cac.id', 'id_compra' => 'c.id', 'p.tipo',
                'mediopub' => 'cac.medio_publicacion', 'p.tipo', 'c.id_empresa',
                'c.adecsys_ente_id'))
            ->joinInner(array('c' => 'compra'), 'c.id = cac.id_compra', null)
            ->joinInner(array('t' => 'tarifa'), 't.id = c.id_tarifa', null)
            ->joinInner(array('p' => 'producto'), 'p.id = t.id_producto', null)
            ->where('cac.adecsys_code is null or cac.adecsys_code = 0 or cac.adecsys_code like ?',
                '%Informix%')
            ->where('c.estado = ?', self::ESTADO_PAGADO)
            ->where('c.id_tarifa <> ?', Application_Model_Tarifa::GRATUITA)
            ->where('c.medio_pago in (?)',
                array(self::FORMA_PAGO_VISA, self::FORMA_PAGO_PAGO_EFECTIVO,
                self::FORMA_PAGO_MASTER_CARD, self::FORMA_PAGO_CREDITO))
            ->where('DATEDIFF(CURDATE(),c.fh_confirmacion) <= ?', 10)
            ->where('year(c.fh_confirmacion) >= ?', date('Y'))
            ->where('c.id_empresa_membresia is not null')
            ->order('cac.id desc');

        return $this->getAdapter()->fetchAll($sql);
    }

    public function getById($id)
    {
        $result = $this->fetchRow($this->select()
                ->from($this->_name)
                ->where('id =?', $id));
        if (!empty($result)) return $result->toArray();
        else return array();
    }

    /**
     * 
     * Valida si existe el voucher o ya se encuentra registrado.
     * El voucher es unico por banco.
     * 
     * @access public 
     * @param string $nro_voucher       Nro de Voucher 
     * @return boolean 
     * 
     */
    public function existeCompraPOSVoucher($nro_voucher, $banco)
    {
        $sql = $this->getAdapter()
            ->select()->from($this->_name, array('id'))
            ->where('nro_voucher = ?', $nro_voucher)
            ->where('id_tarjeta_banco = ?', $banco);

        $idCompra = $this->getAdapter()->fetchOne($sql);
        return ($idCompra ? true : false);
    }

    public function verificarPOS($idCompra, $idEmpresa)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('compra'), array('estado')
            )
            ->where("id = ?", $idCompra)
            ->where("id_empresa = ?", $idEmpresa)
            ->where("medio_pago = ?", self::FORMA_PAGO_POS)
            ->limit(1);
        return $this->getAdapter()->fetchOne($sql);
    }

    public function obtenerRegistroFaltantesCompraAdecsysCodigoNuevo()
    {

        $sql = $this->getAdapter()->select()->from(array('c' => 'compra'),
                array(
                'c.id',
                'ad_ente_id' => 'ae.id', 'id_anuncio_impreso' => 'ai.id',
                't.medio_pub', 'c.adecsys_ente_id',
                'ai.fh_pub_confirmada'
            ))
            ->joinInner(array('t' => 'tarifa'), 't.id = c.id_tarifa', null)
            ->joinLeft(array('cac' => 'compra_adecsys_codigo'),
                'c.id = cac.id_compra', null)
            ->joinLeft(array('ai' => 'anuncio_impreso'), 'c.id = ai.id_compra',
                null)
            ->joinLeft(array('e' => 'empresa'), 'e.id = c.id_empresa', null)
            ->joinLeft(array('ae' => 'adecsys_ente'), 'e.ruc = ae.doc_numero',
                null)
            ->where('cac.adecsys_code is null or cac.adecsys_code = 0 or cac.adecsys_code like ?',
                '%Informix%')
            ->where('c.estado = ?', self::ESTADO_PAGADO)
            ->where('c.id_tarifa <> ?', Application_Model_Tarifa::GRATUITA)
            ->where('c.medio_pago in (?)',
                array(self::FORMA_PAGO_VISA, self::FORMA_PAGO_PAGO_EFECTIVO,
                self::FORMA_PAGO_MASTER_CARD, self::FORMA_PAGO_CREDITO, self::FORMA_PAGO_POS))
            ->where('DATEDIFF(CURDATE(),c.fh_confirmacion) <= ?', 10)
            ->where('year(c.fh_confirmacion) >= ?', date('Y'))
            ->where('t.meses is null')
            ->where('c.id_empresa_membresia is null')
            ->order('c.id');

//echo $sql;exit;
        return $this->getAdapter()->fetchAll($sql);
    }

    public function tieneAviso($idCompra)
    {
        $sql = $this->getAdapter()
            ->select()
            ->from(array('c' => 'compra'), array('id'))
            ->joinInner(array('aw' => 'anuncio_web'), 'aw.id_compra = c.id',
                null)
            ->where("c.id = ?", $idCompra)
            ->where("length(aw.id) > ?", 6)
            ->limit(1);
        $res = $this->getAdapter()->fetchAll($sql);

        return count($res) ? true : false;
    }

    public function setAvisoRelacionado($idCompra, $idAnuncioWeb)
    {
        $where     = $this->getAdapter()->quoteInto('id = ?', $idCompra);
        $okUpdateP = $this->update(
            array('id_anuncio_web' => $idAnuncioWeb), $where
        );
    }

    /**
     * Retorna los id de las compras en ESTADO_EXTORNADO 
     * @return array
     */
    public function getExtornados()
    {
        $sql = $this->getAdapter()
            ->select()
            ->from(array('c' => 'compra'), array('id'))
            ->joinInner(array('aw' => 'anuncio_web'), 'aw.id_compra = c.id',
                null)
            ->where("c.estado = ?", self::ESTADO_EXTORNADO)
            ->where("MONTH(c.fh_extorno) = ?", date("n"))
            ->where("YEAR(c.fh_extorno) = ?", date("Y"))
            ->where("DAY(c.fh_extorno) = ?", date("j"))
            ->group("c.id")
        ;
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * Retorna los CIP de las compras con estado Pendiente de pago
     * 
     * @param int $idAnuncioWeb Id de aviso
     * @return array Arreglo con los cip
     */
    public function getCipComprasPendientesPago($idAnuncioWeb)
    {
        $sql = $this->getAdapter()
            ->select()
            ->from(array('c' => 'compra'), array('cip'))
            ->where("c.estado = ?", self::ESTADO_PENDIENTE_PAGO)
            ->where("c.id_anuncio_web = ?", $idAnuncioWeb)
        ;
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getDataCompraDireccion($id)
    {
        $sql = $this->getAdapter()
            ->select()
            ->from(array('car' => 'compra_adecsys_ruc'),
                array('car.tipo_via', 'car.direccion', 'NroPuerta' => 'car.nro_puerta'))
            ->where("car.id_compra = ?", (int) $id);
        return $this->getAdapter()->fetchRow($sql);
    }

    public function getCompraPf()
    {
        $sql = $this->getAdapter()
            ->select()
            ->from(array('c' => 'compra'), array('id' => 'c.id'))
            ->joinInner(array('cac' => 'compra_adecsys_codigo'),
                'c.id = cac.id_compra', array())
            ->where("c.estado = ?", self::ESTADO_PAGADO)
            ->where("c.medio_pago = ?", self::MEDIO_PAGO_PF)
            ->where("adecsys_code 	IS NULL")
            ->limit(10);
        $res = $this->getAdapter()->fetchAll($sql);
        return $res;
    }

    public function getCompraFail()
    {
        $sql = $this->getAdapter()
            ->select()
            ->from(array('aw' => 'anuncio_web'),
                array(
                'id' => 'c.id'))
            ->joinInner(array('c' => 'compra'), 'c.id = aw.id_compra', array())
            ->joinLeft(array('cac' => 'compra_adecsys_codigo'),
                'c.id = cac.id_compra', array())
            ->where("c.estado = ?", self::ESTADO_PAGADO)
            ->where("c.medio_pago = ?", 'credomatic')
            ->where("cac.adecsys_code 	IS NULL")
            ->limit(10);
        //echo $sql;exit;
        $res = $this->getAdapter()->fetchAll($sql);
        return $res;
    }
}