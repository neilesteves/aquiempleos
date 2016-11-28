<?php

class Application_Model_Tarifa extends App_Db_Table_Abstract
{
    const MEDIOPUB_APTITUS       = 'aptitus';
    const MEDIOPUB_TALAN         = 'talan';
    const MEDIOPUB_APTITUS_TALAN = 'aptitus y talan';
    const ECONOMICOS_ID          = 2;
    const PREFERENCIAL_ID        = 6;
    const DESTACADO_PLATA        = 23;
    const DESTACADO_ORO          = 24;
    const DESTACADO_CV           = 'CV Destacado';
    const GRATUITA               = 1;
    const ACTIVO                 = 1;
    const INACTIVO               = 0;

    protected $_name = "tarifa";

    public function getExtracargos($tarifaId)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('e' => 'extracargos'),
                array('codigoBeneficio' => 'b.codigo',
                'extracargoId' => 'e.id',
                'nombreBeneficio' => 'b.nombre',
                'precioExtracargo' => 'e.precio',
                'valorExtracargo' => 'e.valor')
            )
            ->joinInner(
                array('b' => 'beneficio'), 'e.id_beneficio = b.id', array()
            )
            ->where('e.id_tarifa = ?', $tarifaId);
        //echo $sql->assemble(); exit;
        $rs  = $this->getAdapter()->fetchAssoc($sql);
        return $rs;
    }

    public function listarBeneficios2($id)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('pb' => 'producto_beneficio'),
                array('nombrebeneficio' => 'b.nombre',
                'valor' => 'pb.valor',
                'idbeneficio' => 'pb.id_producto',
                'preciobeneficio' => 'b.precio',
                'adecsyscode' => 'b.adecsys_code',
                'tipobeneficio' => 'b.tipo')
            )
            ->joinInner(
                array('b' => 'beneficio'), 'pb.id_beneficio = b.id'
            )
            ->joinInner(
                array('p' => $this->_name), 'pb.id_producto = p.id'
            )
            ->where('pb.id_producto = ?', $id);
        $rs  = $this->getAdapter()->fetchAssoc($sql);

        return $rs;
    }

    public function getProductoByTarifa($idTarifa)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('t' => $this->_name),
                array(
                'id_tarifa' => 't.id',
                'medio_publicacion' => 't.medio_pub',
                'id_producto' => 'p.id',
                'tipo' => 'p.tipo',
                'meses',
                't.precio'
                )
            )
            ->joinInner(
                array('p' => 'producto'), 't.id_producto = p.id'
            )
            ->where('t.id = ?', $idTarifa);
        $rs  = $this->getAdapter()->fetchRow($sql);
        if ($rs['tipo'] == 'web') {
            $rs['tipo'] = 'soloweb';
        }
        if ($rs['medio_publicacion'] == 'aptitus y talan') {
            $rs['medio_publicacion'] = 'aptitus_talan';
        }
        return $rs;
    }

    public function getProductoByIdTarifa($idTarifa)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('t' => $this->_name), array())
            ->joinInner(array('p' => 'producto'), 't.id_producto = p.id')
            ->where('t.id = ?', $idTarifa);
        $rs  = $this->getAdapter()->fetchRow($sql);
        return $rs;
    }

    /**
     * Retorna el maximo de anuncios webs en el aviso preferencial de acuerdo
     * a la tarifa escogida
     * 
     * @param int $idTarifa
     * @return string
     */
    public function getNumeroAvisoMaximoByPreferencial($idTarifa)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('t' => $this->_name),
                array(
                'maximo_avisos' => 'ti.maximo_avisos',
                )
            )
            ->joinInner(
                array('ti' => 'tamano_impreso'), 't.id_tamano = ti.id'
            )
            ->where('t.id = ?', $idTarifa);
        $rs  = $this->getAdapter()->fetchOne($sql);
        return $rs;
    }

    public function getTarifaMembresia($tipo, $membresia)
    {

        $sql = $this->getAdapter()->select()
            ->from(
                array('t' => $this->_name),
                array(
                'id_tarifa' => 't.id',
                'medio_publicacion' => 't.medio_pub',
                'id_producto' => 'p.id',
                'tipo' => 'p.tipo'
                )
            )
            ->joinInner(
                array('p' => 'producto'), 't.id_producto = p.id'
            )
            ->where('p.nombre = ?', $membresia)
            ->where('p.tipo = ?', $tipo)
            ->where('t.activo = ?', self::ACTIVO);
        $rs  = $this->getAdapter()->fetchRow($sql);
        return $rs;
    }

    public function validarPuestoTarifa($id)
    {
        $sql = $this->select()->from(array('t' => $this->_name), array())
            ->where('t.id = ?', $id)
            ->where("t.id_tamano in (?)", array(9, 10, 11, 12));

        $rs = $this->getAdapter()->fetchAll($sql);

        if (count($rs) == 0) return true;

        return false;
    }

    public function obtenerTarifasCVDestacados()
    {
        $sql = $this->getAdapter()->select()
            ->from(array('t' => $this->_name),
                array('t.id', 't.precio', 't.meses'))
            ->joinInner(array('p' => 'producto'), 't.id_producto = p.id', null)
            ->where('t.meses in (?)', array(3, 6, 12));

        $rs = $this->getAdapter()->fetchAll($sql);
        return $rs;
    }

    public function validarTarifasCVDestacados($idTarifa)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('t' => $this->_name),
                array('t.id', 't.precio', 'p.nombre'))
            ->joinInner(array('p' => 'producto'), 't.id_producto = p.id', null)
            ->where('t.meses in (?)', array(3, 6, 12))
            ->where('t.id = ?', $idTarifa);
        //   echo $sql;exit;
        $rs  = $this->getAdapter()->fetchRow($sql);

        return $rs;
    }

    //Obtener los días de vigencia que tiene el perfil público
    public function obtenerDiasBeneficioPerfil($idTarifa)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('t' => $this->_name), array('dias' => 'pd.valor'))
            ->joinInner(array('p' => 'producto'), 't.id_producto = p.id', null)
            ->joinInner(array('pd' => 'producto_detalle'),
                'pd.id_producto = p.id', null)
            ->where('t.id = ?', $idTarifa);

        $rs = $this->getAdapter()->fetchOne($sql);

        return $rs;
    }

    //Obtener la data producctiva que se envia a adecsys con respecto a una membresia
    public function listDataAdecsysProductiva($idTarifa)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('am' => 'adecsys_membresia'),
                array(
                'Med_Pub_Id' => 'am.Med_Pub_Id',
                'Cod_Med_Pub' => 'am.Cod_Med_Pub',
                'Des_Med_Pub' => 'am.Des_Med_Pub',
                'Pub_Id' => 'am.Pub_Id',
                'Cod_Pub' => 'am.Cod_Pub',
                'Des_Pub' => 'am.Des_Pub',
                'Edi_Id' => 'am.Edi_Id',
                'Cod_Edi' => 'am.Cod_Edi',
                'Des_Edi' => 'am.Des_Edi',
                'Sec_Id' => 'am.Sec_Id',
                'Cod_Sec' => 'am.Cod_Sec',
                'Des_Sec' => 'am.Des_Sec',
                'Sub_Sec_Id' => 'am.Sub_Sec_Id',
                'Cod_Sub_Sec' => 'am.Cod_Sub_Sec',
                'Des_Sub_Sec' => 'am.Des_Sub_Sec',
                'Ubi_Id' => 'am.Ubi_Id',
                'Cod_Ubi' => 'am.Cod_Ubi',
                'Des_Ubi' => 'am.Des_Ubi',
                'Tar_Id' => 'am.Tar_Id',
                'Cod_Tar' => 'am.Cod_Tar',
                'Des_Tar' => 'am.Des_Tar',
                'Tipo_Aviso' => 'am.Tipo_Aviso',
                'Form_Pago' => 'am.Form_Pago',
                'Modulo' => 'am.Modulo',
                'Esp_Id' => 'am.Esp_Id',
                'Modulaje' => 'am.Modulaje',
                'Cod_Sede' => 'am.Cod_Sede',
                'Id_Paquete' => 'am.Id_Paquete',
                'Id_num_solicitud' => 'am.Id_num_solicitud',
                'Id_Item' => 'am.Id_Item',
                'Aplicado' => 'am.Aplicado',
                'Tipo_Contrato' => 'am.Tipo_Contrato',
                'Med_Id' => 'am.Med_Id',
                'Des_Med' => 'am.Des_Med',
                'Med_Horizontal' => 'am.Med_Horizontal',
                'Med_Vertical' => 'am.Med_Vertical',
                'Val_Moneda' => 'am.Val_Moneda',
                'Cod_Moneda' => 'am.Cod_Moneda',
                'Importe' => 'am.Valor_Importe')
            )
            ->where('am.id_tarifa = ?', $idTarifa)
            ->where('am.active = ?', self::ACTIVO);

        return $this->getAdapter()->fetchRow($sql);
    }

    public function getDetalleDestaquesTarifa($idTarifa)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('t' => $this->_name),
                array(
                'idProducto' => 'p.id',
                'precio' => 't.precio',
                 'destaque'=>'p.desc',
                'codigo' => 'b.codigo',
                'valor' => 'pd.valor',
                'descripcion' => 'b.desc',
            ))
            ->joinInner(array('p' => 'producto'), 't.id_producto = p.id',
                array())
            ->joinInner(array('pd' => 'producto_detalle'),
                'pd.id_producto = p.id', array())
            ->joinInner(array('b' => 'beneficio'), 'pd.id_beneficio = b.id',
                array())
            ->where('t.id = ?', $idTarifa);
        $rs  = $this->getAdapter()->fetchAll($sql);
     
        return $rs;
    }
}