<?php


class Application_Model_AnuncioImpreso
    extends App_Db_Table_Abstract
{

    const TIPO_DISENIO_PROPIO = 'propio';
    const TIPO_DISENIO_PRE_DISENIADO = 'pre_diseniado';

//    const TIPO_DISEÑO_SCOT = 'por_scot';
    protected $_name = "anuncio_impreso";

    /**
     * Retorna los datos de un anuncio impreso de acuerdo al ID
     * 
     * @param int $anuncioImpresoId
     */
    public function getDataAnuncioImpreso($anuncioImpresoId)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name, array('id', 'texto', 'id_tarifa'))
            ->where('id = ?', $anuncioImpresoId);

        return $this->getAdapter()->fetchRow($sql);
    }

    public function getDetalleAvisoPreferencialImpreso($idImpreso)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('ai' => $this->_name), array('ai.id'))
            ->joinInner(array('p' => 'producto'), 'p.id = ai.id_producto',
                array('p.nombre'))
            ->joinInner(array('t' => 'tarifa'), 't.id = ai.id_tarifa',
                array('t.medio_pub'))
            ->joinInner(array('ti' => 'tamano_impreso'), 'ti.id = t.id_tamano',
                array('ti.descripcion'))
            ->where('ai.id = ?', $idImpreso);

        return $this->getAdapter()->fetchRow($sql);
    }

    public function getDataAnuncioPreferencialImpreso($anuncioImpresoId)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name,
                array('id_plantilla', 'texto', 'tipo_diseno', 'nota_diseno'))
            ->where('id = ?', $anuncioImpresoId);
        return $this->getAdapter()->fetchRow($sql);
    }

    public function getDatosPagarAnuncioImpreso($id)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('ai' => 'anuncio_impreso'),
                array('nombreEmpresa' => 'e.razon_social',
                'anuncioId' => 'ai.id',
                //'url' => 'url_id',
                //'slug' => 'slug',
                'empresaId' => 'ai.id_empresa',
                //'slug_anuncio' => 'ai.slug',
                'productoId' => 'ai.id_producto',
                'anuncioImpresoId' => 'ai.id',
                'tarifaId' => 'ai.id_tarifa',
                'tipo' => 'ai.tipo')
            )
            ->joinLeft(
                array('c' => 'compra'), 'ai.id_compra = c.id',
                array('estadoCompra' => 'c.estado',
                'medioPago' => 'c.medio_pago',
                'idCompra' => 'c.id',
                    )
            )
            ->joinInner(
                array('t' => 'tarifa'), 'ai.id_tarifa = t.id',
                array(
                'tarifaPrecio' => 't.precio',
                'medioPublicacion' => 't.medio_pub'
                )
            )
            ->joinInner(
                array('ti' => ''), 't.id_tamano = ti.id',
                array(
                'tamanoId' => 'ti.id',
                'tamano' => 'ti.descripcion',
                'medidaTarifa' => 'ti.medida_tarifa',
                'tamanoCentimetros' => 'ti.tamano_centimetro'
                )
            )
            ->joinInner(
                array('p' => 'producto'), 't.id_producto = p.id',
                array('nombreProducto' => 'p.nombre')
            )
            ->joinInner(
                array('e' => 'empresa'), 'ai.id_empresa = e.id',
                array('empresaRuc' => 'e.ruc',
                'empresaRazonSocial' => 'e.razon_social',
                'empresaRazonComercial' => 'e.nombre_comercial')
            )
            ->joinInner(
                array('u' => 'usuario'), 'e.id_usuario = u.id',
                array('empresaMail' => 'u.email',
                'usuarioId' => 'u.id')
            )
            ->where('ai.id = ?', $id);
//        echo $sql->assemble(); exit;
        $rsAnuncio = $this->getAdapter()->fetchRow($sql);

        $sql = $this->getAdapter()->select()
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
            ->joinInner(
                array('b' => 'beneficio'), 'pd.id_beneficio = b.id', array()
            )
            ->joinInner(
                array('p' => 'producto'), 'pd.id_producto = p.id', array()
            )
            ->where('pd.id_producto = ?', $rsAnuncio['productoId']);
        $rs = $this->getAdapter()->fetchAssoc($sql);

        $rsAnuncio['beneficios'] = $rs;

        $sql = $this->getAdapter()
            ->select()->from(
                array('aw' => 'anuncio_web'), array('id', 'id_nivel_puesto'))
            ->joinInner(
                array('np' => 'nivel_puesto'), 'np.id = aw.id_nivel_puesto',
                array('cod_subseccion', 'peso')
            )
            ->where('id_anuncio_impreso = ?', $id)
            ->order('peso');
        $rsIds = $this->getAdapter()->fetchAll($sql);

        $rsAnuncio['anunciosWeb'] = $rsIds;

        $sql = $this->getAdapter()->select()
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
            ->joinInner(
                array('b' => 'beneficio'), 'e.id_beneficio = b.id', array()
            )
            ->where('e.id_tarifa = ?', $rsAnuncio['tarifaId']);
        //echo $sql->assemble(); exit;
        $rs = $this->getAdapter()->fetchAssoc($sql);
        $rsAnuncio['extracargos'] = $rs;

        $sql = $this->getAdapter()->select()
            ->from(
                array('ee' => 'empresa_ente'), array('enteId' => 'ee.ente_id')
            )
            ->where('ee.empresa_id = ?', $rsAnuncio['empresaId'])
            ->where('ee.esta_activo = 1');
        $enteId = $this->getAdapter()->fetchOne($sql);
        $rsAnuncio['enteId'] = $enteId;
        return $rsAnuncio;
    }

    function getAfectaMembresiaProductoXidImpreso($idImpreso)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('ai' => $this->_name), array())
            ->joinInner(
                array('p' => 'producto'), 'p.id = ai.id_producto',
                array('p.afecta_membresia')
            )
            ->where('ai.id = ? ', $idImpreso);

        return $this->getAdapter()->fetchOne($sql);
    }

    function existeCodScot($codScot, $tipo)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name, array('id', 'texto', 'id_tarifa'));
        if ($tipo == 1) {
            $sql = $sql->where('cod_scot_aptitus = ? ', $codScot);
        } else if ($tipo == 2) {
            $sql = $sql->where('cod_scot_talan = ? ', $codScot);
        }

        $data = $this->getAdapter()->fetchAll($sql);
        if ($data && (count($data) > 0)) {
            return true;
        }

        return false;
    }

    function existeUrlScot($codScot, $urlScot, $tipo)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name, array('id', 'texto', 'id_tarifa'));
        if ($tipo == 1) {
            $sql = $sql->where('cod_scot_aptitus = ? ', $codScot)
                ->where('url_scot_aptitus = ? ', $urlScot);
        } else if ($tipo == 2) {
            $sql = $sql->where('cod_scot_talan = ? ', $codScot)
                ->where('url_scot_talan = ? ', $urlScot);
        }

        $data = $this->getAdapter()->fetchAll($sql);
        if ($data && (count($data) > 0)) {
            return true;
        }

        return false;
    }

    function existeUrlSource($codScot, $urlSource, $tipo)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name, array('id', 'texto', 'id_tarifa'));
        if ($tipo == 1) {
            $sql = $sql->where('cod_scot_aptitus = ? ', $codScot)
                ->where('url_source_aptitus = ? ', $urlSource);
        } else if ($tipo == 2) {
            $sql = $sql->where('cod_scot_talan = ? ', $codScot)
                ->where('url_source_talan = ? ', $urlSource);
        }

        $data = $this->getAdapter()->fetchAll($sql);
        if ($data && (count($data) > 0)) {
            return true;
        }

        return false;
    }

    public function setURLSourceByCodScot($codScot, $urlSource)
    {
        if ($this->existeCodScot($codScot, 1)) {
            if (!$this->existeUrlSource($codScot, $urlSource, 1)) {
                return $this->update(
                        array('url_source_aptitus' => $urlSource),
                        $this->getAdapter()->quoteInto('cod_scot_aptitus = ?',
                            $codScot)
                );
            } else {
                return 3;
            }
        } elseif ($this->existeCodScot($codScot, 2)) {
            if (!$this->existeUrlSource($codScot, $urlSource, 2)) {
                return $this->update(
                        array('url_source_talan' => $urlSource),
                        $this->getAdapter()->quoteInto('cod_scot_talan = ?',
                            $codScot)
                );
            } else {
                return 3;
            }
        } else {
            return 2;
        }
    }

    public function setCodScotYUrlScot($codScotApt, $codScotTalan, $urlScotApt,
        $urlScotTalan, $idAnuncioImpreso)
    {
        return $this->update(
                array('url_scot_aptitus' => $urlScotApt, 'cod_scot_aptitus' => $codScotApt,
                'url_scot_talan' => $urlScotTalan, 'cod_scot_talan' => $codScotTalan),
                $this->getAdapter()->quoteInto('id = ?', $idAnuncioImpreso)
        );
    }

    public function getInfoAnuncioImpreso($idAnuncioImp)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('ai' => 'anuncio_impreso'))
            ->joinInner(array('t' => 'tarifa'), 'ai.id_tarifa = t.id')
            ->joinInner(array('ti' => 'tamano_impreso'), 't.id_tamano = ti.id')
            ->where('ai.id = ?', $idAnuncioImp);
        $data = $this->getAdapter()->fetchRow($sql);
        if ($data && (count($data) > 0)) {
            return $data;
        }
    }

    public function verifAnuncioImpreso($idAnuncioImp)
    {
        $sql = $this->getAdapter()->select()
            ->from('anuncio_impreso')
            ->where("id = ?", $idAnuncioImp);
        $data = $this->getAdapter()->fetchRow($sql);
        if ($data && (count($data))) {
            return $data;
        }
        return false;
    }

    public function getIdAvisosByIdImpreso($idImpreso)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('ai' => $this->_name), array())
            ->joinInner(array('aw' => 'anuncio_web'),
                'ai.id = aw.id_anuncio_impreso', 'id')
            ->where('ai.id = ?', $idImpreso);
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getCipByIdImpreso($idImpreso)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('ai' => $this->_name), array('idImpreso' => 'ai.id')
            )
            ->joinInner(
                array('c' => 'compra'), 'c.id = ai.id_compra',
                array('idCompra' => 'c.id', 'c.cip')
            )
            ->where('ai.id = ?', $idImpreso);
        return $this->getAdapter()->fetchRow($sql);
    }

    public function perteneceAvisoImpresoEmpresa($idAvisoImpreso, $idEmpresa)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('aw' => $this->_name), array('id' => 'id')
            )
            ->where('id = ?', $idAvisoImpreso)
            ->where('id_empresa = ?', $idEmpresa)
            ->limit(1);
        return $this->getAdapter()->fetchOne($sql);
    }
    
    public function obtenerIdCompra($idCompra)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name, array('id'))
            ->where('id_compra = ?', $idCompra);

        return $this->getAdapter()->fetchRow($sql);
    }
    public function getByIdCompra($id) {
        $result = $this->fetchRow($this->select()
                                ->from($this->_name)
                                ->where('id_compra =?', $id));
        if(!empty($result))
            return $result->toArray();
        else
            return array();
    }

    public function getCompra($idAviso) 
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(
                    array('ai' => 'anuncio_impreso'), array('ai.id_compra')
                )
                ->where('ai.id = ?', $idAviso)
                ->order('ai.id DESC');
        return $this->getAdapter()->fetchOne($sql);
    }
}


