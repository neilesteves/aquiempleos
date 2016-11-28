<?php

class Application_Model_AdecsysEnte extends App_Db_Table_Abstract
{
    protected $_name = "adecsys_ente";

    const DOCUMENTO_RUC    = 'RUT';
    const DOCUMENTO_DNI    = 'CI';
    const PERSONA_NATURAL  = 'N';
    const PERSONA_JURIDICA = 'J';

    public function registrar($ente)
    {
        $data                      = array();
        $data['ente_cod']          = $ente->Id;
        $data['doc_tipo']          = strtoupper($ente->Tip_Doc);
        $data['doc_numero']        = $ente->Num_Doc;
        $data['ape_pat']           = $ente->Ape_Pat;
        $data['ape_mat']           = $ente->Ape_Mat;
        $data['nombres']           = $ente->Pre_Nom;
        $data['razon_social']      = $ente->RznSoc_Nombre;
        $data['tipo_persona']      = $ente->Tip_Per;
        $data['email']             = $ente->Email;
        $data['telefono']          = $ente->Telf;
        $data['ciudad_adecys_cod'] = $ente->Ciudad;
        $data['urb_tipo']          = $ente->Tip_Cen_Pob;
        $data['urb_nombre']        = $ente->Nom_Cen_Pob;
        $data['direc_cod']         = $ente->Cod_Direccion;
        $data['calle_tipo']        = $ente->Tip_Calle;
        $data['calle_nombre']      = $ente->Nom_Calle;
        $data['calle_num']         = $ente->Num_Pta;
        $data['estado']            = $ente->Est_Act;

        return $this->insert($data);
    }

    public function registrarEnte($ente)
    {
        $data                      = array();
        $data['ente_cod']          = $ente['cliente_id'];
        $data['doc_tipo']          = $ente['Tipo_Documento'];
        $data['doc_numero']        = $ente['Numero_Documento'];
        $data['ape_pat']           = $ente['Apellidos'];
        $data['ape_mat']           = '';
        $data['nombres']           = $ente['Nombres'];
        $data['razon_social']      = $ente['Rzn_Social'];
        $data['tipo_persona']      = '';
        $data['email']             = $ente['Contactos'][0]['Email_Contacto'];
        $data['telefono']          = $ente['Contactos'][0]['Telf_Contacto'];
        $data['ciudad_adecys_cod'] = '';
        $data['urb_tipo']          = '';
        $data['urb_nombre']        = '';
        $data['direc_cod']         = '';
        $data['calle_tipo']        = '';
        $data['calle_nombre']      = '';
        $data['calle_num']         = '';
        $data['estado']            = '';

        return $this->insert($data);
    }

    public function updateEnte($data, $id)
    {
        $where = $this->getAdapter()->quoteInto('id = ?', $id);

        return $this->update($data, $where);
    }

    public function obtenerPorDocumento($documento)
    {
        return $this->fetchRow($this->select()
                    ->from(array('ae' => $this->_name),
                        array('ae.id', 'ae.doc_numero'))
                    ->where('ae.doc_numero =?', $documento));
    }

    public function obtenerPorCod($cod)
    {
        return $this->fetchRow($this->select()
                    ->from($this->_name, array('id'))
                    ->where('ente_cod = ?', $cod));
    }

    public function obtenerEnteAPT($tipo, $documento)
    {
        return $this->fetchRow($this->select()
                    ->from($this->_name)
                    ->where('doc_tipo = ?', $tipo)
                    ->where('doc_numero =?', $documento));
    }

    public function impresoFechas($id)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('ai' => 'anuncio_impreso_detalle'),
                array(
                'fh' => 'ai.fh_impreso',
                'destaque' => 'ai.destaque',
            ))
            ->where('codigo = ?', 'fecha')
            ->where('ai.id_anuncio_impreso = ?', $id);
        $res = $this->getAdapter()->fetchAll($sql);
        return $res;
    }

    public function IdContacto($id)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('aec' => 'adecsys_ente_contacto'),
                array(
                'Id_Contacto' => 'ai.Id_Contacto',
            ))
            ->where('ai.id = ?', $id);
        $res = $this->getAdapter()->fetchOne($sql);
        return $res;
    }

    public function getEnvioAviso($compraId)
    {
        $sql       = $this->getAdapter()->select()
            ->from(
                array('c' => 'compra'),
                array(
                'Cod_Cliente' => 'ae.ente_cod',
                'id_adecsys_ente_contacto' => "c.id_adecsys_ente_contacto",
                'Tit_Web' => 'aw.puesto',
                'Total_Web' => 'c.precio_total',
                'Total_Impreso' => 'c.precio_total_impreso',
                'referenciaPago' => 'c.cip',
                'Texto_Web' => new Zend_Db_Expr("CONCAT( aw.funciones ,' ', aw.responsabilidades)"),
                'Form_Pago' => new Zend_Db_Expr(
                    "CASE c.medio_pago
                            WHEN 'credomatic' THEN 'C'
                            WHEN 'pf' THEN 'PF'
                            WHEN 'pv' THEN 'PV'
                            END"
                ),
                'Email_Usuario'=>'c.correo_admin'
                )
            )
            ->joinInner(
                array('ae' => 'adecsys_ente'), 'c.adecsys_ente_id = ae.id',
                array(
                'tipo_doc' => 'ae.doc_tipo',
                'doc_numero' => 'ae.doc_numero'
                )
            )
            ->joinInner(
                array('aw' => 'anuncio_web'), 'aw.id_compra = c.id',
                array(
                'fh_ini_web' => 'aw.fh_pub',
                'fh_fin_web' => 'aw.fh_vencimiento_prioridad',
                'dias' => 'aw.prioridad_ndias_busqueda',
                'prioridad' => 'aw.prioridad',
                )
            )
            ->joinLeft(
                array('ai' => 'anuncio_impreso'),
                'aw.id_anuncio_impreso = ai.id',
                array(
                'IdImpreso' => 'ai.id',
                'Texto_Aviso' => 'ai.texto',
                'Id_Seccion' => 'ai.Id_Seccion',
                'Cod_Estilo' => 'ai.Cod_Estilo',
                'Cod_Color' => 'ai.Cod_Color',
                'Cod_Fondo' => 'ai.Cod_Fondo',
                'Cant_Fotos' => 'ai.Cant_Fotos',
                'Prensiguia' => 'ai.Prensiguia',
                'DiarioHoy' => 'ai.DiarioHoy',
                'Url_Imagen' => 'ai.path_img',
                'Id_SubSeccion' => 'ai.Id_SubSeccion'
                )
            )
            ->where('c.id = ?', $compraId);
        $rsAnuncio = $this->getAdapter()->fetchRow($sql);
//        $rsAnuncio['IdImpreso']     = !empty($rsAnuncio['IdImpreso']) ? $rsAnuncio['IdImpreso']
//                : '';
        //    $rsAnuncio['Form_Pago']     = 'C';
        $rsAnuncio['Tipo_anuncio']  = 'C';
        $rsAnuncio['No_Orden']      = '';
        $rsAnuncio['No_Aceptacion'] = '';
        $rsAnuncio['Tit_Aviso']     = '';
        $rsAnuncio['Prensiguia']    = !empty($rsAnuncio['Prensiguia']) ? true : false;
        $rsAnuncio['DiarioHoy']     = !empty($rsAnuncio['DiarioHoy']) ? true : false;

        $rsAnuncio['Texto_Aviso']   = !empty($rsAnuncio['Texto_Aviso']) ? $rsAnuncio['Texto_Aviso']
                : '';
        $rsAnuncio['Cod_Estilo']    = !empty($rsAnuncio['Cod_Estilo']) ? $rsAnuncio['Cod_Estilo']
                : '';
        $rsAnuncio['Cod_Color']     = !empty($rsAnuncio['Cod_Color']) ? $rsAnuncio['Cod_Color']
                : '';
        $rsAnuncio['Cod_Fondo']     = !empty($rsAnuncio['Cod_Fondo']) ? $rsAnuncio['Cod_Fondo']
                : '';
        $rsAnuncio['Cant_Fotos']    = !empty($rsAnuncio['Cant_Fotos']) ? $rsAnuncio['Cant_Fotos']
                : '';
        $rsAnuncio['Invertido']     = false;
        $rsAnuncio['Internacional'] = false;
        $rsAnuncio['Posicion']      = "00";
        $rsAnuncio['Alto']          = "0";
        $rsAnuncio['Ancho']         = "00";
        $rsAnuncio['Cod_Extra']     = "00";
        $rsAnuncio['Costo_extra']   = 0;
        $rsAnuncio['Cod_Descuento'] = "00";
        $rsAnuncio['Descuento']     = 0;
        switch ($rsAnuncio['prioridad']) {
            case '1':
            case '2':
                $rsAnuncio['Web'] = true;
                break;

            default:
                $rsAnuncio['Web'] = false;
                break;
        }

        //web
        // $rsAnuncio['Importe_Web']        = "";
        //   $rsAnuncio['Impuesto_Web']       = "";
        //     $rsAnuncio['Total_Web']          = "";
        //impreso
//        $rsAnuncio['Importe']            = "";
//        $rsAnuncio['Impuesto']           = "";
//        $rsAnuncio['Total']              = "";
        $rsAnuncio['Fechas_Publicacion'] = '';

        if (!empty($rsAnuncio['IdImpreso'])) {
            $idImpreso = $rsAnuncio['IdImpreso'];
            $fhimpreso = array();
            $fh        = $this->impresoFechas($idImpreso);
            foreach ($fh as $key => $value) {
                $fhimpreso[$key] = array(
                    'Fecha' => str_replace(" ", "T", $value['fh']),
                    'Destacado' => ($value['destaque'] == '1') ? true : false
                );
            }
            $rsAnuncio['Fechas_Publicacion'] = $fhimpreso;
            // unset($rsAnuncio['IdImpreso']);
        }

        if (!empty($rsAnuncio['id_adecsys_ente_contacto'])) {
            $rsAnuncio['Id_Contacto'] = $this->IdContacto($rsAnuncio['id_adecsys_ente_contacto']);
            unset($rsAnuncio['id_adecsys_ente_contacto']);
        }
        $rsAnuncio['Fechas_Publicacion_web'] = array();
        if (!empty($rsAnuncio['fh_ini_web']) && !empty($rsAnuncio['fh_fin_web'])) {
            $fecha       = $rsAnuncio['fh_ini_web'];
            $publicfecha = array();
            $i           = 0;
            for ($index = 1; $index <= (int) $rsAnuncio['dias']; $index++) {
                $dia                          = $index;
                $nuevafecha                   = strtotime("+$dia day",
                    strtotime($fecha));
                $nuevafecha                   = date('Y-m-d', $nuevafecha).'T00:00:00';
                $publicfecha[$i]['Fecha']     = $nuevafecha;
                $publicfecha[$i]['Destacado'] = true;
                $i++;
            }
            $rsAnuncio['Fechas_Publicacion_web'] = $publicfecha;
        }
        $rsAnuncio['Fec_Registro'] = str_replace(" ", "T", date('Y-m-d H:i:s'));

        return $rsAnuncio;
    }

    public function getEntePorEmpresa($empresaId)
    {

        $sql = $this->getAdapter()->select()
            ->from(array('ae' => $this->_name), array('*'))
            ->joinInner(
                array('ee' => 'empresa_ente'), 'ae.id = ee.ente_id', array()
            )
            ->where('ee.empresa_id = ?', $empresaId);

        $data = $this->getAdapter()->fetchRow($sql);

        return $data;
    }
}