<?php

class App_Controller_Action_Helper_Aviso extends Zend_Controller_Action_Helper_Abstract
{
    private $_aw;
    private $_ai;
    private $_avisoId;
    private $_postulacion;
    private $_empresa;
    private $_config;
    private $_anuncioWeb;
    private $_adecysEnte;
    private $_empresaEnte;
    private $_compraAdecsysCode;
    private $_compra;
    //Buscamas
    private $_buscamasConsumerKey;
    private $_buscamasPublishUrl;
    private $_buscamasUrl;
    //Solr
    public $_SolrAviso;

    const MSG_SCOT_CONTENIDO = "Diseño listo, enviado desde Aptitus";

    /**
     * @var Zend_Cache
     */
    protected $_cache = null;

    public function __construct()
    {
        if ($this->_cache == null) {
            $this->_cache = Zend_Registry::get('cache');
        }
        $this->_postulacion = new Application_Model_Postulacion();
        $this->_adecsysEnte = new Application_Model_AdecsysEnte();
        $this->_empresaEnte = new Application_Model_EmpresaEnte();
        $this->_compra      = new Application_Model_Compra();
        $this->_aw          = new Application_Model_AnuncioWeb();
        $this->_awd         = new Application_Model_AnuncioWebDetalle();
        $this->_ai          = new Application_Model_AnuncioImpreso();
        $this->_empresa     = new Application_Model_Empresa();

        $this->_SolrAviso = new Solr_SolrAviso();

        $this->_config = Zend_Registry::get('config');

        $this->_compraAdecsysCode = new Application_Model_CompraAdecsysCodigo();
        if (isset($_SESSION)) {
            $this->auth    = Zend_Auth::getInstance()->getStorage()->read();
            $this->session = new Zend_Session_Namespace('aptitus');
        }
    }

    /**
     *
     * @param type $compraId
     * @param type $registrarEnAdecsys
     * @return void
     */
    public function confirmarCompraAviso($compraId, $registrarEnAdecsys = 0)
    {
        if (!$this->_compra->verificarUsuarioActivoPorCompra($compraId)) {
            return;
        }

        if (!$this->_compra->tieneAviso($compraId)) {
            $compra = $this->_compra->getById($compraId);

            //buscamos el id de la última compra
            $idAnuncioWeb = NULL;
            $ultimaCompra = '';

            if ($compra["tipo_anuncio"] == Application_Model_AnuncioWeb::TIPO_PREFERENCIAL) {
                $ultimaCompra = $this->_ai->getCompra($compra['id_anuncio_web']);
            } else {
                $ultimaCompra = $this->_aw->getCompra($compra['id_anuncio_web']);
            }

            //seteamos la compra por pagar al aviso (o avisos)
            $whereAnuncioWeb = $this->_aw->getAdapter()->quoteInto('id_compra=?',
                $ultimaCompra);
            $okUpdateP       = $this->_aw->update(
                array('id_compra' => $compraId), $whereAnuncioWeb
            );
            if ($compra["tipo_anuncio"] == Application_Model_AnuncioWeb::TIPO_PREFERENCIAL
                || $compra["tipo_anuncio"] == Application_Model_AnuncioWeb::TIPO_CLASIFICADO) {
                echo "222".PHP_EOL;
                $whereAnuncioImpreso = $this->_ai->getAdapter()->quoteInto('id_compra=?',
                    $ultimaCompra);
                $okUpdateP           = $this->_ai->update(
                    array('id_compra' => $compraId), $whereAnuncioImpreso
                );
            }
        }

        $this->actualizaValoresCompraAviso($compraId);
        $rowCompra     = $this->_compra->getDetalleCompraAnuncio($compraId);
        $this->_SolrAviso->addAvisoSolr($rowCompra['anuncioId']);
        //Verifica si tiene correo opcional en el aviso
        $anuncioWeb    = new Application_Model_AnuncioWeb();
        $tieneCorreoOp = $anuncioWeb->avisoTieneCorreoOp($rowCompra['anuncioId']);
        //Inserta las postulaciones al extender el aviso
        if ($rowCompra['anuncioClase'] != Application_Model_AnuncioWeb::TIPO_SOLOWEB) {
            foreach ($rowCompra['anunciosWeb'] as $data) {
                if (isset($data['extiende_a']) && $data['extiende_a'] != $data['id']
                    && $data['republicado'] == 0) {
                    //Extender
                    $this->extenderAviso($data['id'], $rowCompra['usuario']);
                } elseif (isset($data['extiende_a']) && $data['extiende_a'] != $data['id']
                    && $data['republicado'] == 1) {
                    //Republica
                    $this->_aw->bajaAnuncioWeb($data['extiende_a'], $data['id'],
                        $rowCompra['usuario']);
                }
            }
        } else {
            if (isset($rowCompra['extiendeA']) && $rowCompra['extiendeA'] != $rowCompra['anuncioId']
                && $rowCompra['republicado'] == 0) {
                //Extender
                $this->extenderAviso($rowCompra['anuncioId'],
                    $rowCompra['usuario']);
            } elseif (isset($rowCompra['extiendeA']) && $rowCompra['extiendeA'] != $rowCompra['anuncioId']
                && $rowCompra['republicado'] == 1) {
                //Republica
                $this->_aw->bajaAnuncioWeb($rowCompra['extiendeA'],
                    $rowCompra['anuncioId'], $rowCompra['usuario']);
            }
        }
        $tipo = '';
        switch ($rowCompra['medioPago']) {
            case 'credomatic':
                $tipo = 'Credomatic';

                break;
            case 'pf':
                $tipo = 'Punto facil';

                break;
            case 'pv':
                $tipo = 'Pago de Ventanilla';

                break;
            default:
                break;
        }
        $nameprioridad = '';

        switch ($rowCompra['anuncioPrioridad']) {
            case '1':
                $nameprioridad = 'Web destacado Oro';

                break;
            case '2':
                $nameprioridad = 'Web destacado Plata';
                break;
            default:
                $nameprioridad = 'Aviso gratuito';
                break;
        }
        $dataMail = array(
            'to' => $rowCompra['emailContacto'],
            'usuario' => $rowCompra['emailContacto'],
            'tipo_doc' => $rowCompra['tipo_doc'],
            'numDocumento' => $rowCompra['numDocumento'],
            'nombre' => $rowCompra['nombreContacto']." ".$rowCompra['apePatContacto'],
            'anuncioPuesto' => $rowCompra['anuncioPuesto'],
            'razonSocial' => $rowCompra['nombre_comercial'],
            'montoTotal' => (float) $rowCompra['montoWeb'] + (float) $rowCompra['montoImpreso'],
            'medioPago' => $tipo,
            'anuncioClase' => $rowCompra['anuncioClase'],
            'productoNombre' => $rowCompra['productoNombre'],
            'anuncioUrl' => $rowCompra['anuncioUrl'],
            'fechaPago' => $rowCompra['fechaPago'],
            'anuncioFechaVencimiento' => $rowCompra['anuncioFechaVencimiento'],
            'fechaPublicConfirmada' => $rowCompra['fechaPublicConfirmada'],
            'medioPublicacion' => $rowCompra['medioPublicacion'],
            'anuncioSlug' => $rowCompra['anuncioSlug'],
            'prioridad' => $nameprioridad,
            'anuncioFechaVencimientoProceso' => $rowCompra['anuncioFechaVencimientoProceso'],
            'codigo_adecsys_compra' => $rowCompra['adecsys_code'],
            'compraId' => $rowCompra['compraId'],
            'tipo' => $rowCompra['tipoAnuncio']
        );
        $mailer   = new App_Controller_Action_Helper_Mail();
        //  $mailer->confirmarCompra($dataMail);
        @$this->_cache->remove('AnuncioWeb_getAvisoInfoficha_'.$rowCompra['anuncioId']);
        @$this->_cache->remove('AnuncioWeb_getAvisoById_'.$rowCompra['anuncioId']);
        @$this->_cache->remove('AnuncioWeb_getAvisoIdByUrl_'.$rowCompra['anuncioUrl']);
        @$this->_cache->remove('AnuncioWeb_getAvisoIdByCreado_'.$rowCompra['anuncioUrl']);
    }

    // @codingStandardsIgnoreEnd
    public function generarCompraAnuncio($rowAnuncio)
    {
        if ($rowAnuncio['enteId'] == '') {
            $rowAnuncio['enteId'] = null;
        }
        if (!isset($rowAnuncio['cip'])) {
            $rowAnuncio['cip'] = null;
        }
         if (empty($rowAnuncio['CorreoAdmin'])) {
            $rowAnuncio['CorreoAdmin'] = '';
        }
        $data = array(
            'id_tarifa' => $rowAnuncio['tarifaId'],
            'id_empresa' => $rowAnuncio['empresaId'],
            'tipo_doc' => $rowAnuncio['tipoDoc'],
            'medio_pago' => $rowAnuncio['tipoPago'],
            'estado' => 'pendiente_pago',
            'fh_creacion' => date('Y-m-d H:i:s'),
            'cip' => $rowAnuncio['cip'],
            'precio_base' => $rowAnuncio['tarifaPrecio'],
            'adecsys_ente_id' => $rowAnuncio['enteId'], //Cambiar por el id del ente seleccionado
            'creado_por' => $rowAnuncio['usuarioId'],
            'precio_total' => $rowAnuncio['totalPrecio'],
            'precio_total_impreso' => $rowAnuncio['precio_total_impreso'],
            'tipo_anuncio' => $rowAnuncio['tipo_medio_publicacion'],
            'id_anuncio_web' => $rowAnuncio['anuncioId'],
            'correo_admin'=>$rowAnuncio['CorreoAdmin']
        );

        if (isset($rowAnuncio['tipoContrato'])) {
            $data["tipo_contrato"] = $rowAnuncio['tipoContrato'];
            $data["nro_contrato"]  = $rowAnuncio['nroContrato'];
        }

        $idCompra = $this->_compra->insert($data);

        if (empty($rowAnuncio['anuncioImpresoId'])) {
            $where = $this->_aw->getAdapter()->quoteInto('id = ?',
                $rowAnuncio['anuncioId']);
        } else {
            //$where = $this->_aw->getAdapter()->quoteInto('id_anuncio_impreso = ?', $rowAnuncio['anuncioImpresoId']);
            $where = $this->_aw->getAdapter()->quoteInto(
                'id_anuncio_impreso = '.$rowAnuncio['anuncioImpresoId'].' and (estado = "'.
                Application_Model_AnuncioWeb::ESTADO_REGISTRADO.'" or estado = "'.
                Application_Model_AnuncioWeb::ESTADO_PENDIENTE_PAGO.'")', null
            );
        }
        $estado = $rowAnuncio['medio_pago_web'];
        if ($rowAnuncio['medio_pago_web'] != Application_Model_AnuncioWeb::MEDIO_PAGO_BONIFICADO) {
            $estado = $rowAnuncio['tipoPago'];
        }
        $okUpdateP = $this->_aw->update(
            array(
            'estado' => 'pendiente_pago',
            'medio_pago' => $estado,
            'id_compra' => $idCompra,
            ), $where
        );
        return $idCompra;
    }
    public function actualizaValoresCompraAviso($compraId)
    {
        $rowCompra = $this->_compra->getDetalleCompraAnuncio($compraId);
        $diasAnuncio   = $diasProceso   = '';
        $fecVenAnuncio = new DateTime(date("Y-m-d"));
        $fecVenProceso = new DateTime(date("Y-m-d"));
        $ndiaspub      = Application_Model_Beneficio::CODE_NDIASPUB;
        if (array_key_exists($ndiaspub, $rowCompra['beneficios'])) {
            $diasAnuncio = !empty($rowCompra['beneficios'][$ndiaspub]['valor']) ?
                $rowCompra['beneficios'][$ndiaspub]['valor'] : 5;
        } else {
            $diasAnuncio = !empty($this->_config->anuncioperiodo->ndiaspub->valor)
                    ?
                $this->_config->anuncioperiodo->ndiaspub->valor : 5;
        }
        $fecVenAnuncio->add(new DateInterval('P'.$diasAnuncio.'D'));
        $ndiasproc = Application_Model_Beneficio::CODE_NDIASPROC;

        if (array_key_exists($ndiasproc, $rowCompra['beneficios'])) {
            $diasProceso = !empty($rowCompra['beneficios'][$ndiasproc]['valor'])
                    ?
                $rowCompra['beneficios'][$ndiasproc]['valor'] : 180;
        } else {
            $diasProceso = !empty($this->_config->anuncioperiodo->ndiasproc->valor)
                    ?
                $this->_config->anuncioperiodo->ndiasproc->valor : 180;
        }
        $fecVenProceso->add(new DateInterval('P'.$diasProceso.'D'));

        $db              = $this->_aw->getAdapter();
        // @codingStandardsIgnoreStart
        $where1          = $db->quoteInto('id_compra = ?', $compraId);
        $where2          = $db->quoteInto('chequeado = ?', 1);
        $where3          = $db->quoteInto('online = ?', 0);
        $whereAnuncioWeb = $where1.' AND '.$where2.' AND '.$where3;

        $anuncioWebModel = new Application_Model_AnuncioWeb;
        $prioridad       = $anuncioWebModel->prioridadAviso($rowCompra['beneficios']);
        $ndiasPrioridad  = $anuncioWebModel->DiasprioridadAviso($rowCompra['beneficios']);
        $empId           = $rowCompra['empresaId'];

        $diaActual         = date('Y-m-d H:i:s');
        $fechaVenPrioridad = date('Y-m-d H:i:s',
            strtotime('+'.$ndiasPrioridad.' day', strtotime($diaActual)));

        $prioridad_de_tipo = $this->getTipoDePrioridadPorTarifaId(
            $rowCompra['empresaId'], $rowCompra['idTarifa'],
            $rowCompra['origen'], $rowCompra['tipoAnuncio']
        );
        $destaquehome      = ($prioridad == 1) ? 1 : 0;
        $okUpdateP         = $this->_aw->update(
            array(
            'estado' => Application_Model_AnuncioWeb::ESTADO_PAGADO,
            'fh_pub' => date('Y-m-d H:i:s'),
            'estado_publicacion' => 1,
            'fh_vencimiento' => $fecVenAnuncio->format('Y-m-d'),
            'online' => 1,
            'borrador' => 0,
            'prioridad_ndias_busqueda' => $ndiasPrioridad,
            'prioridad' => $prioridad,
            'destacado' => $destaquehome,
            'prioridad_de_tipo' => $prioridad_de_tipo,
            'fh_vencimiento_prioridad' => $fechaVenPrioridad,
            'fh_vencimiento_proceso' => $fecVenProceso->format('Y-m-d'),
            'proceso_activo' => 1,
            ), $whereAnuncioWeb
        );

        // redundamos para asegurar el cambio del estado del aviso.
        $okUpdateP = $this->_aw->update(
            array(
            'estado' => Application_Model_AnuncioWeb::ESTADO_PAGADO
            ), $whereAnuncioWeb
        );


        /* Paul verificar el campo estado debeira ser de anuncio impreso */
        $whereAnuncioImpreso = $db->quoteInto('id_compra = ?', $compraId);
        $okUpdateP           = $this->_ai->update(
            array(
            'estado' => Application_Model_AnuncioWeb::ESTADO_PAGADO,
            ), $whereAnuncioImpreso
        );

        $where     = $this->_compra->getAdapter()->quoteInto('id = ?', $compraId);
        $okUpdateP = $this->_compra->update(
            array(
            'estado' => 'pagado',
            'fh_confirmacion' => date('Y-m-d H:i:s'),
            ), $where
        );
    }

    public function actualizaDatosImpresoCompraAviso($compraId)
    {

    }

    /**
     *
     * @param type $compraId
     */
    public function registrarAvisoEnAdecsys($compraId)
    {
        $options = array();
        if (isset($this->_config->adecsys->proxy->enabled) && $this->_config->adecsys->proxy->enabled) {
            $options = $this->_config->adecsys->proxy->param->toArray();
        }

        $ws      = new Adecsys_Wrapper($this->_config->adecsys->wsdl, $options);
        $cliente = $ws->getSoapClient();
        $db      = Zend_Db_Table::getDefaultAdapter();
        $aptitus = new Aptitus_Adecsys($ws, $db);

        //Cabecera
        $rowAnuncio = $this->_compra->getDetalleCompraAnuncio($compraId);

        if ($rowAnuncio['enteId'] == null) {

            $servicioRegistrarEnte = new App_Service_Adecsys_RegisterEntity($ws);
            $nombreTrama           = 'compra_'.$compraId;
            $servicioRegistrarEnte->setNameTrama($nombreTrama);

            if (!$servicioRegistrarEnte->register($rowAnuncio['empresaId'])) {
                $adecsysContingencyAdModel = new
                    Application_Model_AdecsysContingenciaAviso;
                $adecsysContingencyAdModel->registrar($rowAnuncio['anuncioId']);
                //return false;
            }
            $log = Zend_Registry::get('log');
            $log->info(var_export($rowAnuncio, true));

            $rowAnuncio = $this->_compra->getDetalleCompraAnuncio($compraId);
        }

        $registerAd = new App_Service_Adecsys_RegisterAd($ws);

        if ($rowAnuncio['tipoAnuncio'] == Application_Model_Compra::TIPO_SOLOWEB
            ||
            $rowAnuncio['tipoAnuncio'] == Application_Model_Compra::TIPO_CLASIFICADO) {

            $nombreTrama = 'compra_'.$compraId;
            $registerAd->setNameTrama($nombreTrama);

            if (!$registerAd->register($rowAnuncio['anuncioId'])) return false;
        } else if ($rowAnuncio['tipoAnuncio'] == Application_Model_Compra::TIPO_PREFERENCIAL
            ||
            $rowAnuncio['tipoAnuncio'] == Application_Model_Compra::TIPO_DESTACADO) {

            if ($rowAnuncio['tipoAnuncio'] == Application_Model_Compra::TIPO_PREFERENCIAL)
                    $this->logicaRegistroAnuncioPreferencial($rowAnuncio);
            else if ($rowAnuncio['tipoAnuncio'] == Application_Model_Compra::TIPO_DESTACADO)
                    $this->logicaRegistroAnuncioDestacado($rowAnuncio);
        }

        $idAviso = $rowAnuncio['anuncioId'];

        // $this->_SolrAviso->addAvisoSolr($idAviso);
        //Actualizar índices Buscamas
        //$resultado = exec("curl -X POST -d 'api_key=".$this->_buscamasConsumerKey."&nid=".$idAviso."&site=".$this->_buscamasUrl."' ".$this->_buscamasPublishUrl);
        $this->_anuncioWeb = null;
        return true;
    }

    public function getSubArrayByKeyValues($array, $keyValues, $sensitive = true)
    {
        $subArray = array();

        if (!$sensitive) {
            $arrayD = array();
            foreach ($array as $key => $data) {
                $keyD         = strtolower($key);
                $arrayD[$key] = $array[$key];
            }
            $array = $arrayD;
        }

        foreach ($keyValues as $key) {
            if (!$sensitive) {
                $key = strtolower($key);
            }
            if (isset($array[$key])) {
                $subArray[$key] = $array[$key];
            }
        }

        return $subArray;
    }

    public function verificarArchivoAdjuntoEnScot($idImpreso)
    {
        $ws      = new Zend_Soap_Client($this->_config->SCOT->wsdl);
        $cliente = $ws->getSoapClient();

        $dataConfig['cod_sede']   = $this->_config->parametrosSCOT->aptitus->general->cod_sede;
        $dataConfig['id_impreso'] = $idImpreso;

        $result = $this->callWSLeerArchivoAdjunto($ws, $dataConfig);

        $result = (array) $result;

        return $result['LeerArchivoAdjuntoResult'];
    }

    public function callWSLeerArchivoAdjunto($ws, $params)
    {
        $response = null;

        try {
            $response = $ws->LeerArchivoAdjunto(
                array(
                    "IdImpreso" => $params['id_impreso'],
                    "CodSede" => $params['cod_sede']
                )
            );
            file_put_contents(
                APPLICATION_PATH.'/../logs/LeerArchivo_'.
                $params['id_impreso'].'_envio.xml', $ws->getLastRequest(),
                FILE_APPEND
            );
            file_put_contents(
                APPLICATION_PATH.'/../logs/LeerArchivo_'.
                $params['id_impreso'].'_rpta.xml', $ws->getLastResponse(),
                FILE_APPEND
            );
        } catch (Exception $ex) {
            file_put_contents(
                APPLICATION_PATH.'/../logs/LeerArchivo_ERROR_'.
                $params['anuncioImpresoId'].'_envio.xml', $ws->getLastRequest(),
                FILE_APPEND
            );
            file_put_contents(
                APPLICATION_PATH.'/../logs/LeerArchivo_ERROR_'.
                $params['anuncioImpresoId'].'_rpta.xml', $ws->getLastResponse(),
                FILE_APPEND
            );
        }
        return $response;
    }

    public function registrarPreferencialEnSCOT($rowAnuncio)
    {
        $log              = Zend_Registry::get('log');
        $ws               = new Zend_Soap_Client($this->_config->SCOT->wsdl);
        $objAdecsysTarifa = new Application_Model_AdecsysTarifa();

        $tamanio        = strtoupper($rowAnuncio["tamanio"]);
        $cod_subseccion = isset($rowAnuncio["cod_subseccion"]) ? $rowAnuncio["cod_subseccion"]
                : NULL;

        $funcion = "registrarOT";

        $dataExt                      = array();
        $dataExt["dsc_mail_to"]       = $rowAnuncio["emailContacto"];
        $dataExt["dsc_mail_contacto"] = $rowAnuncio["emailContacto"];
        $dataExt["tlf_contacto"]      = $rowAnuncio["telefonoContacto"];
        $dataExt["cod_cliente"]       = $rowAnuncio["codigoEnte"];
        $dataExt["dsc_tituloaviso"]   = $rowAnuncio["productoNombre"]." ".$rowAnuncio["tamanio"];
        $dataExt["dsc_observacion"]   = $rowAnuncio["notaDiseno"]." . . . . . ";
        $dataExt["id_medida"]         = $rowAnuncio["medida"];
        $colRow                       = explode("x", $rowAnuncio["tamanio"]);
        $dataExt["nro_mod"]           = $colRow[0];
        $dataExt["nro_col"]           = $colRow[1];

        $dataExt["nom_contacto"] = $rowAnuncio["nombreContacto"]." ".$rowAnuncio["apePatContacto"];
        $dataExt["d_fechapub"]   = $rowAnuncio["fechaPublicConfirmada"];

        //plantilla
        $dataExt["id_plantilla"] = $rowAnuncio['codigo_scot'];

        if ($rowAnuncio['codigo_scot'] != null) {
            $dataExt["id_aviso_imp"] = $rowAnuncio["anuncioImpresoId"];
            $dataExt["html_aviso"]   = $rowAnuncio["textoAnuncioImpreso"];
            $dataExt["id_plantilla"] = $rowAnuncio['codigo_scot'];
        } else {
            //TODO id_plantilla con valor 0 provisionalmente

            $dataExt["id_plantilla"] = '0';
            $dataExt["id_aviso_imp"] = $rowAnuncio["anuncioImpresoId"];
            $dataExt["html_aviso"]   = null;
        }

        $fechaCierre = new Zend_Date($rowAnuncio["fechaPublicConfirmada"],
            "YYYY-MM-dd");

        $dataExt["fch_iniciopub"] = $rowAnuncio["fechaPublicConfirmada"];
        $dataExt["fch_finpub"]    = $rowAnuncio["fechaPublicConfirmada"];


        $resultTalan   = null;
        $resultAptitus = null;

        if ($rowAnuncio["medioPublicacion"] == Application_Model_Tarifa::MEDIOPUB_APTITUS) {
            $tipo = Application_Model_Tarifa::MEDIOPUB_APTITUS;

            $dataExt["cod_interno"] = $rowAnuncio["nroAdecsysAptitus"];
            $dataExt["cod_aviso"]   = $rowAnuncio["correlativoAptitus"];

            $diaCierre                  = $this->_config->cierre->aptitus->dia;
            $fechaCierre->setWeekday($diaCierre);
            $dataExt["fch_cierreaviso"] = $fechaCierre->get("YYYY-MM-dd");

            $dataTamAptitus = (array) $objAdecsysTarifa->getByTipocodSubseccionTamanio($tipo,
                    $cod_subseccion, $tamanio);

            $paramsScot = $this->_config->parametrosSCOT->$tipo->general->toArray();

            $paramsScot['id_subseccion'] = $dataTamAptitus['Sub_Sec_Id'];

            if (isset($rowAnuncio['tipo_diseno']) &&
                $rowAnuncio['tipo_diseno'] ==
                Application_Model_AnuncioImpreso::TIPO_DISENIO_PROPIO) {
                $paramsScot['html_aviso'] = self::MSG_SCOT_CONTENIDO;
            }

            $dataConfig = $this->getSubArrayByKeyValues(
                $paramsScot,
                $this->_config->parametrosSCOT->$funcion->toArray(), false
            );

            $dataConfig = $dataConfig + $this->getSubArrayByKeyValues(
                    $dataTamAptitus,
                    $this->_config->parametrosSCOT->$funcion->toArray(), false
                ) + $dataExt;

            $resultAptitus = $this->callWSRegistrarOT($ws, $dataConfig,
                $rowAnuncio, $tipo);
        } else if ($rowAnuncio["medioPublicacion"] == Application_Model_Tarifa::MEDIOPUB_TALAN) {
            $tipo = Application_Model_Tarifa::MEDIOPUB_TALAN;

            $dataExt["cod_interno"] = $rowAnuncio["nroAdecsysTalan"];
            $dataExt["cod_aviso"]   = $rowAnuncio["correlativoTalan"];

            $diaCierre                  = $this->_config->cierre->talan->dia;
            $fechaCierre->setWeekday($diaCierre);
            $dataExt["fch_cierreaviso"] = $fechaCierre->get("YYYY-MM-dd");

            $dataTamTalan = (array) $objAdecsysTarifa->getByTipoTamanio($tipo,
                    $tamanio);

            $paramsScot                  = $this->_config->parametrosSCOT->$tipo->general->toArray();
            $paramsScot['id_subseccion'] = $dataTamTalan['Sub_Sec_Id'];

            if (isset($rowAnuncio['tipo_diseno']) &&
                $rowAnuncio['tipo_diseno'] ==
                Application_Model_AnuncioImpreso::TIPO_DISENIO_PROPIO) {
                $paramsScot['html_aviso'] = self::MSG_SCOT_CONTENIDO;
            }

            $dataConfig = $this->getSubArrayByKeyValues(
                $paramsScot,
                $this->_config->parametrosSCOT->$funcion->toArray(), false
            );

            $dataConfig = $dataConfig + $this->getSubArrayByKeyValues(
                    $dataTamTalan,
                    $this->_config->parametrosSCOT->$funcion->toArray(), false
                ) + $dataExt;

            $resultTalan = $this->callWSRegistrarOT($ws, $dataConfig,
                $rowAnuncio, $tipo);
        } else if ($rowAnuncio["medioPublicacion"] == Application_Model_Tarifa::MEDIOPUB_APTITUS_TALAN) {
            $tipo  = Application_Model_Tarifa::MEDIOPUB_APTITUS."Combo";
            $tipoT = Application_Model_Tarifa::MEDIOPUB_APTITUS;

            $dataExt["cod_interno"] = $rowAnuncio["nroAdecsysAptitus"];
            $dataExt["cod_aviso"]   = $rowAnuncio["correlativoAptitus"];

            $diaCierre                  = $this->_config->cierre->aptitus->dia;
            $fechaCierre->setWeekday($diaCierre);
            $dataExt["fch_cierreaviso"] = $fechaCierre->get("YYYY-MM-dd");

            $dataTamAptitus = (array) $objAdecsysTarifa->getByTipocodSubseccionTamanio($tipo,
                    $cod_subseccion, $tamanio);

            $paramsScot                  = $this->_config->parametrosSCOT->$tipoT->general->toArray();
            $paramsScot['id_subseccion'] = $dataTamAptitus['Sub_Sec_Id'];

            if (isset($rowAnuncio['tipo_diseno']) &&
                $rowAnuncio['tipo_diseno'] ==
                Application_Model_AnuncioImpreso::TIPO_DISENIO_PROPIO) {
                $paramsScot['html_aviso'] = self::MSG_SCOT_CONTENIDO;
            }

            $dataConfig = $this->getSubArrayByKeyValues(
                $paramsScot,
                $this->_config->parametrosSCOT->$funcion->toArray(), false
            );

            $dataConfig = $dataConfig + $this->getSubArrayByKeyValues(
                    $dataTamAptitus,
                    $this->_config->parametrosSCOT->$funcion->toArray(), false
                ) + $dataExt;

            $resultAptitus = $this->callWSRegistrarOT($ws, $dataConfig,
                $rowAnuncio, $tipo);

            $tipo  = Application_Model_Tarifa::MEDIOPUB_TALAN."Combo";
            $tipoT = Application_Model_Tarifa::MEDIOPUB_TALAN;

            $dataExt["cod_interno"] = $rowAnuncio["nroAdecsysTalan"];
            $dataExt["cod_aviso"]   = $rowAnuncio["correlativoTalan"];

            //Si tiene medida de talán
            if (isset($rowAnuncio['medida_talan'])) {
                $dataExt['id_medida'] = $rowAnuncio['medida_talan'];
            }


            $diaCierre                  = $this->_config->cierre->talan->dia;
            $fechaCierre->setWeekday($diaCierre);
            $dataExt["fch_cierreaviso"] = $fechaCierre->get("YYYY-MM-dd");

            $dataTamTalan = (array) $objAdecsysTarifa->getByTipoTamanio($tipo,
                    $tamanio);

            $paramsScotTalan                  = $this->_config->parametrosSCOT->$tipoT->general->toArray();
            $paramsScotTalan['id_subseccion'] = $dataTamTalan['Sub_Sec_Id'];
            if (isset($rowAnuncio['tipo_diseno']) &&
                $rowAnuncio['tipo_diseno'] ==
                Application_Model_AnuncioImpreso::TIPO_DISENIO_PROPIO) {
                $paramsScotTalan['html_aviso'] = self::MSG_SCOT_CONTENIDO;
            }

            $dataConfig = $this->getSubArrayByKeyValues(
                $paramsScotTalan,
                $this->_config->parametrosSCOT->$funcion->toArray(), false
            );

            $dataConfig = $dataConfig + $this->getSubArrayByKeyValues(
                    $dataTamTalan,
                    $this->_config->parametrosSCOT->$funcion->toArray(), false
                ) + $dataExt;


            $resultTalan = $this->callWSRegistrarOT($ws, $dataConfig,
                $rowAnuncio, $tipo);
        }
        if ($resultAptitus != null || $resultTalan != null) {
            $modelAI = new Application_Model_AnuncioImpreso();

            $nroOTApt   = null;
            $nroOTTalan = null;
            $linkApt    = null;
            $linkTalan  = null;


            if ($resultAptitus != null) {
                $resultAptitus = (array) $resultAptitus;
                $resultAptitus = (array) $resultAptitus["Registar_OTResult"];
                $nroOTApt      = $resultAptitus["nro_ot"];
                $linkApt       = isset($resultAptitus["link_adjuntar"]) ?
                    $resultAptitus["link_adjuntar"] : NULL;
            }

            if ($resultTalan != null) {
                $resultTalan = (array) $resultTalan;
                $resultTalan = (array) $resultTalan["Registar_OTResult"];
                $nroOTTalan  = $resultTalan["nro_ot"];
                $linkTalan   = isset($resultTalan["link_adjuntar"]) ?
                    $resultTalan["link_adjuntar"] : NULL;
            }

            $modelAI->setCodScotYUrlScot($nroOTApt, $nroOTTalan, $linkApt,
                $linkTalan, $rowAnuncio["anuncioImpresoId"]);
        }
    }

    private function callWSRegistrarOT($ws, $params, $rowAnuncio,
                                       $tipoPublicacion)
    {
        $response = null;

        try {
            $response = $ws->Registar_OT(array("odatos" => $params));
            file_put_contents(
                APPLICATION_PATH.'/../logs/Impreso_'.$tipoPublicacion.'_'.
                $rowAnuncio['anuncioImpresoId'].'_Registar_OT_envio.xml',
                $ws->getLastRequest(), FILE_APPEND
            );
            file_put_contents(
                APPLICATION_PATH.'/../logs/Impreso_'.$tipoPublicacion.'_'.
                $rowAnuncio['anuncioImpresoId'].'_Registar_OT_rpta.xml',
                $ws->getLastResponse(), FILE_APPEND
            );
        } catch (Exception $ex) {
            file_put_contents(
                APPLICATION_PATH.'/../logs/Impreso_ERROR_'.$tipoPublicacion.'_'.
                $rowAnuncio['anuncioImpresoId'].'_Registar_OT_envio.xml',
                $ws->getLastRequest(), FILE_APPEND
            );
            file_put_contents(
                APPLICATION_PATH.'/../logs/Impreso_ERROR_'.$tipoPublicacion.'_'.
                $rowAnuncio['anuncioImpresoId'].'_Registar_OT_rpta.xml',
                $ws->getLastResponse(), FILE_APPEND
            );
        }
        return $response;
    }

    /**
     *
     * @param array $rowAnuncio
     */
    public function logicaRegistroAnuncioPreferencial($rowAnuncio)
    {
        $log    = Zend_Registry::get('log');
        $config = Zend_Registry::get("config");
        $log->info(var_export($rowAnuncio, TRUE));

        $ws               = new Zend_Soap_Client($this->_config->adecsysPreferenciales->wsdl);
        $objAdecsysTarifa = new Application_Model_AdecsysTarifa();

        $params                                = array();
        $params["Registro_Aviso_Pref_InputBE"] = array();

        $contrato = $this->getFlagsContrato($rowAnuncio);

        $log->info('Contrato => ');
        $log->info(var_export($contrato, TRUE));

        $tamanio        = strtoupper($rowAnuncio["tamanio"]);
        $cod_subseccion = isset($rowAnuncio["cod_subseccion"]) ? $rowAnuncio["cod_subseccion"]
                : NULL;

        $funcion = "registrarAviso";

        $dataExt                     = array();
        $dataExt["Ape_Mat_Contacto"] = trim($rowAnuncio["apeMatContacto"]) == ""
                ? "-" : $rowAnuncio["apeMatContacto"];
        $dataExt["Ape_Pat_Contacto"] = trim($rowAnuncio["apePatContacto"]) == ""
                ? "-" : $rowAnuncio["apePatContacto"];

        $dataExt["Tit_Aviso"]      = $rowAnuncio["tituloAnuncioImpreso"];
        $dataExt["Cod_Cliente"]    = $rowAnuncio["codigoEnte"];
        $dataExt["Email_Contacto"] = $rowAnuncio["emailContacto"];
        $dataExt["Fec_Registro"]   = date("Y-m-d");
        $dataExt["Importe"]        = $rowAnuncio["montoTotal"];
        $dataExt["Nom_Contacto"]   = trim($rowAnuncio["nombreContacto"]) == "" ? "-"
                : $rowAnuncio["nombreContacto"];

        $dataExt["Num_Doc"]         = $rowAnuncio["numDocumento"];
        $dataExt["RznSoc_Nombre"]   = $rowAnuncio["razonSocial"];
        $dataExt["Telf_Contacto"]   = $rowAnuncio["telefonoContacto"];
        $dataExt["Tip_Doc"]         = $rowAnuncio["tipoDocumento"];
        $dataExt["Contenido_Aviso"] = $rowAnuncio["textoAnuncioImpreso"].".";

        $dataExt["Cod_Contrato"]  = $rowAnuncio["nroContrato"];
        $dataExt["Tipo_Contrato"] = $rowAnuncio["tipoContrato"];

        $dataExt["Puestos_Aviso"]                   = array();
        $dataExt["Puestos_Aviso"]["Puesto_AvisoBE"] = array();


        //* Puesto_Id  	char(10)		: Id del Puesto(del maestro) 	default : 0
        //* Esp_Id  	integer	x	: Id de la Especialidad (del maestro)
        //* Ind_Id  	integer		: Id de la Industria (del maestro)	default : 0
        //* Cod_Dpto  	char(6)	X	: Codigo de Departamento Adecsys
        //* Des_Aviso  	varchar(255)	x	: Titulos de l avisos

        foreach ($rowAnuncio["anunciosWeb"] as $anuncio) {

            $aviso                                        = array();
            // @codingStandardsIgnoreStart
            $aviso["Puesto_Id"]                           = $config->adecsysParametrosDefault->preferenciales->puesto_id;
            $aviso["Esp_Id"]                              = $config->adecsysParametrosDefault->preferenciales->esp_id;
            $aviso["Ind_Id"]                              = $config->adecsysParametrosDefault->preferenciales->ind_id;
            $aviso["Cod_Dpto"]                            = $config->adecsysParametrosDefault->preferenciales->cod_dpto;
            // @codingStandardsIgnoreEnd
            $aviso["Des_Aviso"]                           = $anuncio["puesto"];
            $dataExt["Puestos_Aviso"]["Puesto_AvisoBE"][] = $aviso;
        }

        //data de prueba
        $dataExt["Prim_Fec_Pub"]       = $rowAnuncio["fechaPublicConfirmada"];
        $dataExt["Fechas_Pub_Aviso"]   = array();
        $dataExt["Fechas_Pub_Aviso"][] = $rowAnuncio["fechaPublicConfirmada"];
        $dataExt["Cant_Fechas_Pub"]    = 1;

        $fechaPub = new Zend_Date($rowAnuncio["fechaPublicConfirmada"],
            "YYYY-MM-dd");

        $dataExt["Fechas_Pub"] = $fechaPub->get("dd/MM/YYYY");

        $dataExt["Des_Adicional"] = $rowAnuncio["nombre_comercial"];

        $dataExt["Modulaje"]         = "";
        $dataExt["Id_Paquete"]       = "";
        $dataExt["Id_num_solicitud"] = "";
        $dataExt["Id_Item"]          = "";
        $dataExt["Aplicado"]         = "";

        $nroAdecsys = '';

        if ($rowAnuncio["medioPublicacion"] == Application_Model_Tarifa::MEDIOPUB_APTITUS) {

            $tipo = Application_Model_Tarifa::MEDIOPUB_APTITUS;

            //Valida que no exista registro en compra_adecsys_codigo
            $cac = $this->_compraAdecsysCode->validaRegistro($rowAnuncio['compraId'],
                $tipo);

            if (!$cac) {

                $correlativoAptitus = $this->_compraAdecsysCode->insert(
                    array('id_compra' => $rowAnuncio['compraId'], 'medio_publicacion' => $tipo)
                );
            } else {
                $correlativoAptitus = $cac;
            }

            if (isset($config->extracargosAvisos) && count($rowAnuncio["extracargos"])
                > 0) {
                $extracargos = '';
                foreach ($rowAnuncio["extracargos"] as $key => $value) {
                    if (isset($key) && $key == 'descuento-'.$tipo) {
                        $extracargos = $value['adecsys_cod'];
                    }
                }
                $dataExt["Cod_ExtraCargos"] = $extracargos;
            }

            $dataExt["Cod_Aviso"]             = $correlativoAptitus;
            $rowAnuncio["correlativoAptitus"] = $correlativoAptitus;

            $dataTamAptitus       = (array) $objAdecsysTarifa->getByTipocodSubseccionTamanio($tipo,
                    $cod_subseccion, $tamanio);
            $rowAnuncio['medida'] = $dataTamAptitus['Med_Id'];

            $dataConfig = $this->getSubArrayByKeyValues(
                $this->_config->adecsysPreferenciales->$tipo->general->toArray(),
                $this->_config->adecsysPreferenciales->$funcion->toArray()
            );

            $dataConfig = $dataConfig + $this->getSubArrayByKeyValues(
                    $dataTamAptitus,
                    $this->_config->adecsysPreferenciales->$funcion->toArray()
                ) + $dataExt;

            $params["Registro_Aviso_Pref_InputBE"][] = array_merge($contrato,
                $dataConfig);

            $response = $this->callWSRegistrarAvisoPreferencial($ws, $params,
                $rowAnuncio, $tipo);

            //$log->info('hola prueba'.$this->_config->adecsysPreferenciales->wsdl);
            // @codingStandardsIgnoreStart
            if ($response->RegistrarAvisosResult->lisBEAvisoResponseDatos
                ->BEAvisoResponseDatos->oRegistroError->errorCodigo == 0) {
                $nroAdecsys = $response->RegistrarAvisosResult->lisBEAvisoResponseDatos
                    ->BEAvisoResponseDatos->sNroAdecsys;

                $where = $this->_compraAdecsysCode->getAdapter()->quoteInto('id = ?',
                    $correlativoAptitus);

                $okUpdateAw = $this->_compraAdecsysCode->update(
                    array(
                    'medio_publicacion' => $tipo,
                    'adecsys_code' => $nroAdecsys
                    ), $where
                );

                $mAnuncioImpreso = new Application_Model_AnuncioImpreso();

                $whereAi = $mAnuncioImpreso->getAdapter()->quoteInto('id = ?',
                    $rowAnuncio['anuncioImpresoId']);

                $okUpdateAi = $okUpdateAw && $mAnuncioImpreso->update(
                        array(
                        'codigo_adecsys' => $nroAdecsys
                        ), $whereAi
                );

                $rowAnuncio["nroAdecsysAptitus"] = $nroAdecsys;
            }
            // @codingStandardsIgnoreEnd
        } else if ($rowAnuncio["medioPublicacion"] == Application_Model_Tarifa::MEDIOPUB_TALAN) {
            $extracargos = '';
            $tipo        = Application_Model_Tarifa::MEDIOPUB_TALAN;
            if (isset($config->extracargosAvisos) && count($rowAnuncio["extracargos"])
                > 0) {
                foreach ($rowAnuncio["extracargos"] as $key => $value) {
                    if (isset($key) && $key == 'descuento-'.$tipo) {
                        $extracargos = $value['adecsys_cod'];
                    }
                }
                $dataExt["Cod_ExtraCargos"] = $extracargos;
            }


            //Valida que no exista registro en compra_adecsys_codigo
            $cac = $this->_compraAdecsysCode->validaRegistro($rowAnuncio['compraId'],
                $tipo);

            if (!$cac) {

                $correlativoTalan = $this->_compraAdecsysCode->insert(
                    array('id_compra' => $rowAnuncio['compraId'], 'medio_publicacion' => $tipo)
                );
            } else {
                $correlativoTalan = $cac;
            }


            $dataExt["Cod_Aviso"]           = $correlativoTalan;
            $rowAnuncio["correlativoTalan"] = $correlativoTalan;

            $dataTamTalan         = (array) $objAdecsysTarifa->getByTipoTamanio($tipo,
                    $tamanio);
            $rowAnuncio['medida'] = $dataTamTalan['Med_Id'];

            $dataConfig = $this->getSubArrayByKeyValues(
                $this->_config->adecsysPreferenciales->$tipo->general->toArray(),
                $this->_config->adecsysPreferenciales->$funcion->toArray()
            );

            $dataConfig = $dataConfig + $this->getSubArrayByKeyValues(
                    $dataTamTalan,
                    $this->_config->adecsysPreferenciales->$funcion->toArray()
                ) + $dataExt;

            $params["Registro_Aviso_Pref_InputBE"][] = $contrato + $dataConfig;

            $response = $this->callWSRegistrarAvisoPreferencial($ws, $params,
                $rowAnuncio, $tipo);
            // @codingStandardsIgnoreStart
            if ($response->RegistrarAvisosResult->lisBEAvisoResponseDatos
                ->BEAvisoResponseDatos->oRegistroError->errorCodigo == 0) {
                $nroAdecsys = $response->RegistrarAvisosResult->lisBEAvisoResponseDatos
                    ->BEAvisoResponseDatos->sNroAdecsys;
                $where      = $this->_compraAdecsysCode->getAdapter()->quoteInto('id = ?',
                    $correlativoTalan);
                $okUpdateP  = $this->_compraAdecsysCode->update(
                    array(
                    'medio_publicacion' => $tipo,
                    'adecsys_code' => $nroAdecsys
                    ), $where
                );

                $mAnuncioImpreso = new Application_Model_AnuncioImpreso();

                $where = $mAnuncioImpreso->getAdapter()->quoteInto('id = ?',
                    $rowAnuncio['anuncioImpresoId']);

                $okUpdateP                     = $okUpdateP && $mAnuncioImpreso->update(
                        array(
                        'codigo_adecsys' => $nroAdecsys
                        ), $where
                );
                $rowAnuncio["nroAdecsysTalan"] = $nroAdecsys;
            }
            // @codingStandardsIgnoreEnd
        } else if ($rowAnuncio["medioPublicacion"] == Application_Model_Tarifa::MEDIOPUB_APTITUS_TALAN) {
            $extracargos = '';
            $tipo        = Application_Model_Tarifa::MEDIOPUB_APTITUS."Combo";
            $tipoT       = Application_Model_Tarifa::MEDIOPUB_APTITUS;
            if (isset($config->extracargosAvisos) && count($rowAnuncio["extracargos"])
                > 0) {
                $extracargosAptitus = "";
                $extracargosTalan   = "";
                foreach ($rowAnuncio["extracargos"] as $key => $value) {
                    if (isset($key) && trim($key) == 'descuento-aptitus') {
                        $extracargosAptitus = $value['adecsys_cod'];
                    }
                    if (isset($key) && trim($key) == 'descuento-talan') {
                        $extracargosTalan = $value['adecsys_cod'];
                    }
                }
                $log->info('descuento-aptitua :'.$extracargosAptitus);
                $log->info('descuento-talan :  '.$extracargosTalan);
                ;
                $dataExt["Cod_ExtraCargos"] = $rowAnuncio["extracargos"]['descuento-'.$tipoT]['adecsys_cod'];
            }


            //Valida que no exista registro en compra_adecsys_codigo
            $cac = $this->_compraAdecsysCode->validaRegistro($rowAnuncio['compraId'],
                $tipoT);

            if (!$cac) {

                $correlativoAptitus = $this->_compraAdecsysCode->insert(
                    array('id_compra' => $rowAnuncio['compraId'], 'medio_publicacion' => $tipoT)
                );
            } else {
                $correlativoAptitus = $cac;
            }


            $dataExt["Cod_Aviso"]             = $correlativoAptitus;
            $rowAnuncio["correlativoAptitus"] = $correlativoAptitus;
            //$rowAnuncio['extracargos'];
            $dataTamAptitus                   = (array) $objAdecsysTarifa->getByTipocodSubseccionTamanio($tipo,
                    $cod_subseccion, $tamanio);
            $rowAnuncio['medida']             = $dataTamAptitus['Med_Id'];

            $dataConfig = $this->getSubArrayByKeyValues(
                $this->_config->adecsysPreferenciales->$tipoT->general->toArray(),
                $this->_config->adecsysPreferenciales->$funcion->toArray()
            );

            $dataConfig = $dataConfig + $this->getSubArrayByKeyValues(
                    $dataTamAptitus,
                    $this->_config->adecsysPreferenciales->$funcion->toArray()
                ) + $dataExt;

            $params["Registro_Aviso_Pref_InputBE"][] = $contrato + $dataConfig;

            $response = $this->callWSRegistrarAvisoPreferencial($ws, $params,
                $rowAnuncio, $tipo);

            // @codingStandardsIgnoreStart
            if (isset($response->RegistrarAvisosResult->lisBEAvisoResponseDatos
                    ->BEAvisoResponseDatos->oRegistroError->errorCodigo) &&
                $response->RegistrarAvisosResult->lisBEAvisoResponseDatos
                ->BEAvisoResponseDatos->oRegistroError->errorCodigo == 0) {

                $nroAdecsys      = $response->RegistrarAvisosResult->lisBEAvisoResponseDatos
                    ->BEAvisoResponseDatos->sNroAdecsys;
                $where           = $this->_compraAdecsysCode->getAdapter()->quoteInto('id = ?',
                    $correlativoAptitus);
                $okUpdateP       = $this->_compraAdecsysCode->update(
                    array(
                    'medio_publicacion' => Application_Model_CompraAdecsysCodigo::MEDIO_PUB_APTITUS_COMBO,
                    'adecsys_code' => $nroAdecsys
                    ), $where
                );
                $mAnuncioImpreso = new Application_Model_AnuncioImpreso();

                $where                           = $mAnuncioImpreso->getAdapter()->quoteInto('id = ?',
                    $rowAnuncio['anuncioImpresoId']);
                $okUpdateP                       = $okUpdateP && $mAnuncioImpreso->update(
                        array(
                        'codigo_adecsys' => $nroAdecsys
                        ), $where
                );
                $rowAnuncio["nroAdecsysAptitus"] = $nroAdecsys;
            }


            $tipo  = Application_Model_Tarifa::MEDIOPUB_TALAN."Combo";
            $tipoT = Application_Model_Tarifa::MEDIOPUB_TALAN;

            //Valida que no exista registro en compra_adecsys_codigo
            $cac = $this->_compraAdecsysCode->validaRegistro($rowAnuncio['compraId'],
                $tipoT);

            if (!$cac) {

                $correlativoTalan = $this->_compraAdecsysCode->insert(
                    array('id_compra' => $rowAnuncio['compraId'], 'medio_publicacion' => $tipoT)
                );
            } else {
                $correlativoTalan = $cac;
            }

            $dataExt["Cod_Aviso"] = $correlativoTalan;

            if (isset($config->extracargosAvisos) && count($rowAnuncio["extracargos"])
                > 0) {
                $dataExt["Cod_ExtraCargos"] = $extracargosTalan;
            }

            $dataTamTalan               = (array) $objAdecsysTarifa->getByTipoTamanio($tipo,
                    $tamanio);
            $rowAnuncio['medida_talan'] = $dataTamTalan['Med_Id'];

            $dataConfig = $this->getSubArrayByKeyValues(
                $this->_config->adecsysPreferenciales->$tipoT->general->toArray(),
                $this->_config->adecsysPreferenciales->$funcion->toArray()
            );

            $dataConfig = $dataConfig + $this->getSubArrayByKeyValues(
                    $dataTamTalan,
                    $this->_config->adecsysPreferenciales->$funcion->toArray()
                ) + $dataExt;

            $params                                  = array();
            $params["Registro_Aviso_Pref_InputBE"]   = array();
            $params["Registro_Aviso_Pref_InputBE"][] = $contrato + $dataConfig;

            $response = $this->callWSRegistrarAvisoPreferencial($ws, $params,
                $rowAnuncio, $tipo);
            // @codingStandardsIgnoreStart
            if ($response->RegistrarAvisosResult->lisBEAvisoResponseDatos
                ->BEAvisoResponseDatos->oRegistroError->errorCodigo == 0) {
                $nroAdecsys = $response->RegistrarAvisosResult->lisBEAvisoResponseDatos
                    ->BEAvisoResponseDatos->sNroAdecsys;
                $where      = $this->_compraAdecsysCode->getAdapter()->quoteInto('id = ?',
                    $correlativoTalan);
                $okUpdateP  = $this->_compraAdecsysCode->update(
                    array(
                    'medio_publicacion' => Application_Model_CompraAdecsysCodigo::MEDIO_PUB_TALAN_COMBO,
                    'adecsys_code' => $nroAdecsys
                    ), $where
                );

                $rowAnuncio["nroAdecsysTalan"] = $nroAdecsys;
            }
            // @codingStandardsIgnoreEnd
        }

        $nro = intval($nroAdecsys);
        if ($nro > 0) {
            $this->registrarPreferencialEnSCOT($rowAnuncio);
        }
    }

    public function logicaRegistroAnuncioDestacado($rowAnuncio)
    {
        $log    = Zend_Registry::get('log');
        $config = Zend_Registry::get("config");
        $log->info(var_export($rowAnuncio, TRUE));

        $ws = new Zend_Soap_Client($this->_config->adecsys->wsdl);

        $params                         = array();
        $params["Registrar_Aviso_Pref"] = array();

        $dataExt                     = array();
        $dataExt["Ape_Mat_Contacto"] = trim($rowAnuncio["apeMatContacto"]) == ""
                ? "-" : $rowAnuncio["apeMatContacto"];
        $dataExt["Ape_Pat_Contacto"] = trim($rowAnuncio["apePatContacto"]) == ""
                ? "-" : $rowAnuncio["apePatContacto"];

        $dataExt["Tit_Aviso"]      = '.';
        $dataExt["Cod_Cliente"]    = $rowAnuncio["codigoEnte"];
        $dataExt["Email_Contacto"] = $rowAnuncio["emailContacto"];
        $dataExt["Fec_Registro"]   = date("Y-m-d");
        $dataExt["Nom_Contacto"]   = trim($rowAnuncio["nombreContacto"]) == "" ? "-"
                : $rowAnuncio["nombreContacto"];
        $extracargos               = array();
        foreach ($rowAnuncio['beneficios'] as $key => $value) {
            if ($key == 'FWAP-AW70%')
                    $extracargos[] = $rowAnuncio['beneficios'][$key]['codigo'];
        }
        $dataExt["Cod_ExtraCargos"] = implode(',', $extracargos);
        $dataExt["Num_Doc"]         = $rowAnuncio["numDocumento"];
        $dataExt["RznSoc_Nombre"]   = $rowAnuncio["razonSocial"];
        $dataExt["Telf_Contacto"]   = $rowAnuncio["telefonoContacto"];
        $dataExt["Tip_Doc"]         = $rowAnuncio["tipoDocumento"];
        $dataExt["Contenido_Aviso"] = $rowAnuncio["textoAnuncioImpreso"].".";

        $dataExt["Cod_Contrato"] = $rowAnuncio["nroContrato"];

        $dataExt["Puestos_Aviso"]                   = array();
        $dataExt["Puestos_Aviso"]["Puesto_AvisoBE"] = array();

        $aviso              = array();
        $aviso["Puesto_Id"] = $config->adecsysParametrosDefault->preferenciales->puesto_id;
        $aviso["Esp_Id"]    = $config->adecsysParametrosDefault->preferenciales->esp_id;
        $aviso["Ind_Id"]    = $config->adecsysParametrosDefault->preferenciales->ind_id;
        $aviso["Cod_Dpto"]  = $config->adecsysParametrosDefault->preferenciales->cod_dpto;
        $aviso["Des_Aviso"] = $rowAnuncio['anuncioPuesto'];

        $dataExt["Puestos_Aviso"]["Puesto_AvisoBE"][] = $aviso;
        $dataExt["Prim_Fec_Pub"]                      = date('Y-m-d');
        $dataExt["Fechas_Pub_Aviso"][]                = date('Y-m-d');

        $dataExt["Fechas_Pub"] = date('d/m/Y');

        $dataExt["Cant_Fechas_Pub"] = 1;
        $dataExt["Des_Adicional"]   = $rowAnuncio["nombre_comercial"];

        $nroAdecsys = '';

        //Valida que no exista registro en compra_adecsys_codigo
        $cac = $this->_compraAdecsysCode->validaRegistro($rowAnuncio['compraId'],
            'aptitus');

        if (!$cac) {

            $correlativoAptitus = $this->_compraAdecsysCode->insert(
                array('id_compra' => $rowAnuncio['compraId'], 'medio_publicacion' => 'aptitus')
            );
        } else {
            $correlativoAptitus = $cac;
        }


        $dataExt["Cod_Aviso"]             = $correlativoAptitus;
        $rowAnuncio["correlativoAptitus"] = $correlativoAptitus;
        $dataExt['Correlativo']           = 1;
        $dataExt["Importe"]               = 50.85; //Definido en adecsys.ini
        $dataExt['Aplicado']              = 'false';

        $dataConfig = array_merge($this->_config->adecsys->destacado->aptitus->toArray(),
            $dataExt);

        //$params["Registro_Aviso_Pref_InputBE"][] = $dataConfig;
        $response = $this->callWSRegistrarAvisoDestacado($ws, $dataConfig,
            $rowAnuncio, 'aptitus');

        if (Zend_Validate::is($response->Registrar_Aviso_PrefResult, 'Int')) {
            $nroAdecsys = $response->Registrar_Aviso_PrefResult;

            $where = $this->_compraAdecsysCode->getAdapter()->quoteInto('id = ?',
                $correlativoAptitus);

            $okUpdateAw = $this->_compraAdecsysCode->update(
                array(
                'medio_publicacion' => 'aptitus',
                'adecsys_code' => $nroAdecsys
                ), $where
            );

            $mAnuncioImpreso = new Application_Model_AnuncioImpreso();

            $whereAi = $mAnuncioImpreso->getAdapter()->quoteInto('id = ?',
                $rowAnuncio['anuncioImpresoId']);

            $okUpdateAi = $okUpdateAw && $mAnuncioImpreso->update(
                    array(
                    'codigo_adecsys' => $nroAdecsys
                    ), $whereAi
            );

            $rowAnuncio["nroAdecsysAptitus"] = $nroAdecsys;
        }
    }

    private function callWSRegistrarAvisoPreferencial($ws, $params, $rowAnuncio,
                                                      $tipoPublicacion)
    {
        $response = null;

        try {
            $response = $ws->RegistrarAvisos(array('listDatosAviso' => $params));
            file_put_contents(
                APPLICATION_PATH.'/../logs/Compra_'.$tipoPublicacion.'_'.
                $rowAnuncio['compraId'].'_RegistrarAvisos_envio.xml',
                $ws->getLastRequest(), FILE_APPEND
            );
            file_put_contents(
                APPLICATION_PATH.'/../logs/Compra_'.$tipoPublicacion.'_'.
                $rowAnuncio['compraId'].'_RegistrarAvisos_rpta.xml',
                $ws->getLastResponse(), FILE_APPEND
            );
        } catch (Exception $ex) {
            file_put_contents(
                APPLICATION_PATH.'/../logs/Compra_'.$tipoPublicacion.'_'.
                $rowAnuncio['compraId'].'_RegistrarAvisos_error_envio.xml',
                $ws->getLastRequest(), FILE_APPEND
            );
            file_put_contents(
                APPLICATION_PATH.'/../logs/Compra_'.$tipoPublicacion.'_'.
                $rowAnuncio['compraId'].'_RegistrarAvisos_error_rpta.xml',
                $ws->getLastResponse(), FILE_APPEND
            );
        }

        return $response;
    }

    private function callWSRegistrarAvisoDestacado($ws, $params, $rowAnuncio,
                                                   $tipoPublicacion)
    {
        $response = null;

        try {
            $response = $ws->Registrar_Aviso_Pref(array('oRegistroAvisoPref' => $params));
            file_put_contents(
                APPLICATION_PATH.'/../logs/Compra_'.$tipoPublicacion.'_'.
                $rowAnuncio['compraId'].'_RegistrarAvisos_envio.xml',
                $ws->getLastRequest(), FILE_APPEND
            );
            file_put_contents(
                APPLICATION_PATH.'/../logs/Compra_'.$tipoPublicacion.'_'.
                $rowAnuncio['compraId'].'_RegistrarAvisos_rpta.xml',
                $ws->getLastResponse(), FILE_APPEND
            );
        } catch (Exception $ex) {
            file_put_contents(
                APPLICATION_PATH.'/../logs/Compra_'.$tipoPublicacion.'_'.
                $rowAnuncio['compraId'].'_RegistrarAvisos_error_envio.xml',
                $ws->getLastRequest(), FILE_APPEND
            );
            file_put_contents(
                APPLICATION_PATH.'/../logs/Compra_'.$tipoPublicacion.'_'.
                $rowAnuncio['compraId'].'_RegistrarAvisos_error_rpta.xml',
                $ws->getLastResponse(), FILE_APPEND
            );
        }

        return $response;
    }

    public function logicaRegistroAnuncioClasificadoYWeb($rowAnuncio, $aptitus,
                                                         $cliente)
    {
        $log = Zend_Registry::get('log');
        $log->info(var_export($rowAnuncio, TRUE));

        $tarifa              = $rowAnuncio['anuncioTarifaId'];
        $rowAnuncio['combo'] = null;

        $rowAnuncio['medioPublicacionAdecsys'] = $rowAnuncio['medioPublicacion'];
        $correlativo                           = $this->_compraAdecsysCode->insert(
            array(
                'id_compra' => $rowAnuncio['compraId'],
                'medio_publicacion' => $rowAnuncio['medioPublicacionAdecsys']
            )
        );
        //Lógica clasificado = destacados
        $this->registrarAvisoClasificado($correlativo, $aptitus, $rowAnuncio,
            $cliente);
    }

//    public function logicaRegistroAnuncioDestacado($rowAnuncio, $aptitus,
//        $cliente)
//    {
//        $log = Zend_Registry::get('log');
//        $log->info(var_export($rowAnuncio, TRUE));
//
//        $tarifa = $rowAnuncio['anuncioTarifaId'];
//        $rowAnuncio['combo'] = null;
//
//
//            $rowAnuncio['medioPublicacionAdecsys'] = $rowAnuncio['medioPublicacion'];
//            $correlativo = $this->_compraAdecsysCode->insert(
//                array(
//                    'id_compra' => $rowAnuncio['compraId'],
//                    'medio_publicacion' => 'aptitus'
//                )
//            );
//            //Modificar esta función
//            $this->registrarAvisoDestacado($correlativo, $aptitus,
//                $rowAnuncio, $cliente);
//
//    }

    public function calcularTarifaPreferencialAdecsys($webService, $cliente,
                                                      $aptitus, $data, $idCompra)
    {
        try {
            $calculoTar = $webService->calcularAviso($data);

            file_put_contents(
                APPLICATION_PATH.'/../logs/compra_'.
                $rowAnuncio['compraId'].'_CalculoTarifa_envio.xml',
                $cliente->getLastRequest(), FILE_APPEND
            );
            file_put_contents(
                APPLICATION_PATH.'/../logs/compra_'.
                $rowAnuncio['compraId'].'_CalculoTarifa_rpta.xml',
                $cliente->getLastResponse(), FILE_APPEND
            );
        } catch (Exception $ex) {
            //return;
        }
    }

    public function registrarCodigoEnte($webService, $cliente, $aptitus,
                                        $rowAnuncio, $compraId)
    {
        $tipoDoc = $this->_config->adecsys->parametrosGlobales->Tipo_doc;

        try {
            $ente = $webService->validarCliente($tipoDoc,
                $rowAnuncio['numeroDoc']);
            if (isset($rowAnuncio['compraId'])) {

                @unlink(APPLICATION_PATH.'/../logs/compra_'.
                        $rowAnuncio['compraId'].'_consulEnte_envio.xml');
                file_put_contents(
                    APPLICATION_PATH.'/../logs/compra_'.
                    $rowAnuncio['compraId'].'_consulEnte_envio.xml',
                    $cliente->getLastRequest(), FILE_APPEND
                );

                @unlink(APPLICATION_PATH.'/../logs/compra_'.
                        $rowAnuncio['compraId'].'_consulEnte_rpta.xml');
                file_put_contents(
                    APPLICATION_PATH.'/../logs/compra_'.
                    $rowAnuncio['compraId'].'_consulEnte_rpta.xml',
                    $cliente->getLastResponse(), FILE_APPEND
                );
            } else {

                @unlink(APPLICATION_PATH.'/../logs/registroEnte_'.
                        $rowAnuncio['anuncioId'].'_consulEnte_envio.xml');
                file_put_contents(
                    APPLICATION_PATH.'/../logs/registroEnte_'.
                    $rowAnuncio['anuncioId'].'_consulEnte_envio.xml',
                    $cliente->getLastRequest(), FILE_APPEND
                );

                @unlink(APPLICATION_PATH.'/../logs/registroEnte_'.
                        $rowAnuncio['anuncioId'].'_consulEnte_rpta.xml');
                file_put_contents(
                    APPLICATION_PATH.'/../logs/registroEnte_'.
                    $rowAnuncio['anuncioId'].'_consulEnte_rpta.xml',
                    $cliente->getLastResponse(), FILE_APPEND
                );
            }
        } catch (Exception $e) {
            //return;
        }
        if ($ente != null) {
            $dataEnte = array(
                // @codingStandardsIgnoreStart
                'ente_cod' => $ente->Id,
                'doc_tipo' => $ente->Tip_Doc,
                'doc_numero' => $ente->Num_Doc,
                'ape_pat' => $ente->Ape_Pat,
                'ape_mat' => $ente->Ape_Mat,
                'nombres' => $ente->Pre_Nom,
                'razon_social' => $ente->RznSoc_Nombre,
                'tipo_persona' => $ente->Tip_Per,
                'email' => $ente->Email,
                'telefono' => $ente->Telf,
                'ciudad_adecys_cod' => $ente->Ciudad,
                'urb_tipo' => $ente->Tip_Cen_Pob,
                'urb_nombre' => $ente->Nom_Cen_Pob,
                'direc_cod' => $ente->Cod_Direccion,
                'calle_tipo' => $ente->Tip_Calle,
                'calle_nombre' => $ente->Nom_Calle,
                'calle_num' => $ente->Num_Pta,
                'estado' => $ente->Est_Act
                //@codingStandardsIgnoreEnd
            );
            $enteId   = $this->_adecsysEnte->insert($dataEnte);
            $this->_empresaEnte->insert(
                array(
                    'ente_id' => $enteId,
                    'empresa_id' => $rowAnuncio['empresaId'],
                    'esta_activo' => 1,
                    'fh_creacion' => date('Y-m-d H:i:s')
                )
            );
        } else {
            $dataParaEnte               = $this->_empresa->datosParaEnteAdecsys(
                $rowAnuncio['empresaId']);
            $filter                     = new App_Util_Filter;
            $newEnte                    = $aptitus->getNuevoEnte();
            //@codingStandardsIgnoreStart
            $newEnte->Tipo_Documento    = $tipoDoc;
            $newEnte->Numero_Documento  = $dataParaEnte['doc_numero'];
            $newEnte->Ape_Paterno       = $filter->escapeAlnum(
                $dataParaEnte['ape_pat']);
            $newEnte->Ape_Materno       = $filter->escapeAlnum(
                $dataParaEnte['ape_pat']);
            $newEnte->Nombres_RznSocial = $filter->escapeAlnum(
                $dataParaEnte['razon_social']);
            $newEnte->Email             = $dataParaEnte['email'];
            $newEnte->Telefono          = $filter->clearTelephone(
                $dataParaEnte['telefono']);
            $newEnte->CodCiudad         = $dataParaEnte['ubigeoId'];
            $newEnte->Nombre_RznComc    = $filter->escapeAlnum(
                $dataParaEnte['razon_social']);

            try {
                $codEnte = $webService->registrarCliente($newEnte);
                if (isset($rowAnuncio['compraId'])) {

                    @unlink(APPLICATION_PATH.'/../logs/compra_'.
                            $rowAnuncio['compraId'].'_regEnte_envio.xml');
                    file_put_contents(
                        APPLICATION_PATH.'/../logs/compra_'.
                        $rowAnuncio['compraId'].'_regEnte_envio.xml',
                        $cliente->getLastRequest(), FILE_APPEND
                    );

                    @unlink(APPLICATION_PATH.'/../logs/compra_'.
                            $rowAnuncio['compraId'].'_regEnte_rpta.xml');
                    file_put_contents(
                        APPLICATION_PATH.'/../logs/compra_'.
                        $rowAnuncio['compraId'].'_regEnte_rpta.xml',
                        $cliente->getLastResponse(), FILE_APPEND
                    );
                } else {

                    @unlink(APPLICATION_PATH.'/../logs/registroEnte_'.
                            $rowAnuncio['anuncioId'].'_regEnte_envio.xml');
                    file_put_contents(
                        APPLICATION_PATH.'/../logs/registroEnte_'.
                        $rowAnuncio['anuncioId'].'_regEnte_envio.xml',
                        $cliente->getLastRequest(), FILE_APPEND
                    );

                    @unlink(APPLICATION_PATH.'/../logs/registroEnte_'.
                            $rowAnuncio['anuncioId'].'_regEnte_rpta.xml');
                    file_put_contents(
                        APPLICATION_PATH.'/../logs/registroEnte_'.
                        $rowAnuncio['anuncioId'].'_regEnte_rpta.xml',
                        $cliente->getLastResponse(), FILE_APPEND
                    );
                }
            } catch (Exception $e) {
                return;
            }

            if ($codEnte == Adecsys_Wrapper::CODIGO_ERROR) {
                $contingenciaModelo = new Application_Model_AdecsysContingenciaEnte;
                $contingenciaModelo->registrar($rowAnuncio['empresaId']);
                return false;
            }

            //@codingStandardsIgnoreEnd
            $datosEnte = array(
                'ente_cod' => $codEnte,
                'doc_tipo' => $tipoDoc,
                'doc_numero' => $dataParaEnte['doc_numero'],
                'email' => $dataParaEnte['email'],
                'nombres' => $dataParaEnte['razon_social'],
                'ape_pat' => $dataParaEnte['ape_pat'],
                'telefono' => $dataParaEnte['telefono']
            );
            $enteId    = $this->_adecsysEnte->insert($datosEnte);
            $this->_empresaEnte->insert(
                array(
                    'ente_id' => $enteId,
                    'empresa_id' => $rowAnuncio['empresaId'],
                    'esta_activo' => 1,
                    'fh_creacion' => date('Y-m-d H:i:s')
                )
            );
        }


        if (isset($compraId)) {
            $where     = $this->_compra->getAdapter()->quoteInto('id = ?',
                $compraId);
            $okUpdateP = $this->_compra->update(
                array(
                'adecsys_ente_id' => $enteId,
                ), $where
            );
        }
        return true;
    }

    public function registrarAvisoSoloWeb($correlativo, $aptitus, $rowAnuncio,
                                          $client)
    {
        $ad                                           = $aptitus->getAvisoPref(); // obteniendo una plantilla de aviso
        $fecha                                        = date('Y-m-d');
        // @codingStandardsIgnoreStart
        $ad->Cod_Cliente                              = $rowAnuncio['codigoEnte'];
        $ad->Num_Doc                                  = $rowAnuncio['numDocumento'];
        $ad->RznSoc_Nombre                            = $rowAnuncio['razonSocial'];
        $ad->Tit_Aviso                                = $rowAnuncio['anuncioPuesto'];
        $ad->Nom_Contacto                             = $rowAnuncio['nombreContacto'];
        $ad->Ape_Pat_Contacto                         = $rowAnuncio['apePatContacto'];
        $ad->Ape_Mat_Contacto                         = $rowAnuncio['apeMatContacto'];
        $ad->Telf_Contacto                            = $rowAnuncio['telefonoContacto'];
        $ad->Email_Contacto                           = $rowAnuncio['emailContacto'];
        $ad->Cod_Aviso                                = $correlativo;
        $ad->Puestos_Aviso->Puesto_AvisoBE->Puesto_Id = $rowAnuncio['puestoAdecsysCode'];
        $ad->Puestos_Aviso->Puesto_AvisoBE->Esp_Id    = '0';
        $ad->Puestos_Aviso->Puesto_AvisoBE->Ind_Id    = '0';
        $ad->Puestos_Aviso->Puesto_AvisoBE->Des_Aviso = $rowAnuncio['anuncioFunciones'].
            //@codingStandardsIgnoreEnd
            " ".$rowAnuncio['anuncioFunciones'];
        try {
            $codigoImpreso = $aptitus->publicarAvisoPreferencial($fecha, $ad);
            $l             = Zend_Registry::get('log');
            $l->debug(
                "Código retornado de Adecsys: ".$codigoImpreso
            );
            file_put_contents(
                APPLICATION_PATH.'/../logs/adecsys/lastRequest_'.
                $correlativo.'.xml', $client->getLastRequest()
            );
            file_put_contents(
                APPLICATION_PATH.'/../logs/adecsys/lastResponse_'.
                $correlativo.'.xml', $client->getLastResponse()
            );
        } catch (Exception $ex) {
            echo "error";
        }
        $where     = $this->_compraAdecsysCode->getAdapter()->quoteInto('id = ?',
            $correlativo);
        $okUpdateP = $this->_compraAdecsysCode->update(
            array(
            'adecsys_code' => $codigoImpreso
            ), $where
        );
        return $codigoImpreso;
    }

    public function registrarAvisoClasificado($correlativo, $aptitus,
                                              $rowAnuncio, $client)
    {
        $log                  = Zend_Registry::get('log');
        $ad                   = $aptitus->getAvisoEc(); // obteniendo una plantilla de aviso
        $fecha                = new Zend_Date();
        $fecha->setDate($rowAnuncio['fechaPublicConfirmada'], 'YYYY-MM-dd');
        //$fecha = $fecha->toString('YYYY-MM-dd');
        // @codingStandardsIgnoreStart
        $ad->Cod_Aviso        = $correlativo;
        $ad->Cod_Cliente      = $rowAnuncio['codigoEnte']; // Código ente
        $ad->Tip_Doc          = $rowAnuncio['tipoDocumento'];
        $ad->Num_Doc          = $rowAnuncio['numDocumento'];
        $ad->RznSoc_Nombre    = $rowAnuncio['razonSocial'];
        // Datos de contacto (Usuario que publica el aviso)
        $ad->Nom_Contacto     = $rowAnuncio['nombreContacto'];
        $ad->Ape_Pat_Contacto = $rowAnuncio['apePatContacto'];
        $ad->Ape_Mat_Contacto = $rowAnuncio['apeMatContacto'];
        if ($rowAnuncio['telefonoContacto'] == '') {
            $telefono = $rowAnuncio['telefonoContacto2'];
        } else {
            $telefono = $rowAnuncio['telefonoContacto'];
        }
        $ad->Telf_Contacto  = $telefono;
        $ad->Email_Contacto = $rowAnuncio['emailContacto'];
        // Datos del aviso

        $ad->Des_Puesto_Titulo       = $rowAnuncio['nombreTipoPuesto'];
        $ad->Texto_Aviso             = $rowAnuncio['textoAnuncioImpreso'].' '.$this->_config->adecsys->preCodigoAdecsys;
        $ad->Num_Palabras            = $rowAnuncio['beneficios']['npalabras']['valor'];
        $ad->Puesto_Aviso->Puesto_Id = $rowAnuncio['puestoAdecsysCode'];
        $ad->Puesto_Aviso->Esp_Id    = $rowAnuncio['puestoIdEspecialidad'];
        $extracargos                 = array();
        foreach ($rowAnuncio['extracargos'] as $key => $value) {
            $extracargos[] = $rowAnuncio['extracargos'][$key]['adecsys_cod'];
        }

        $vHelperCast = new App_View_Helper_LuceneCast();
        $nombreTipo  = $vHelperCast->LuceneCast($rowAnuncio['productoNombre']);
        $ad          = $aptitus->completeAd(
            $ad, $rowAnuncio['puestoTipo'], $rowAnuncio['medioPublicacion'],
            $nombreTipo, $rowAnuncio['combo']
        );

        try {
            $codigoImpreso = $aptitus->publicarAviso($fecha, $ad, $extracargos);

            $log->info(
                "Código retornado de Adecsys: ".$codigoImpreso
            );
            $log->info(var_export($client->getLastRequest(), TRUE));
            file_put_contents(
                APPLICATION_PATH.'/../logs/compra_'.
                $rowAnuncio['compraId'].'_regAnuncio_'.$correlativo.'_envio.xml',
                $client->getLastRequest(), FILE_APPEND
            );
            file_put_contents(
                APPLICATION_PATH.'/../logs/compra_'.
                $rowAnuncio['compraId'].'_regAnuncio_'.$correlativo.'_rpta.xml',
                $client->getLastResponse(), FILE_APPEND
            );
            $log->info(var_export($client->getLastResponse(), TRUE));
        } catch (Exception $ex) {
            echo PHP_EOL.$ex->getMessage().PHP_EOL;
            print_r($ex->getTraceAsString());
        }
        $where = $this->_compraAdecsysCode->getAdapter()->quoteInto('id = ?',
            $correlativo);

        $okUpdateP = $this->_compraAdecsysCode->update(
            array(
            'medio_publicacion' => $rowAnuncio['medioPublicacionAdecsys'],
            'adecsys_code' => $codigoImpreso
            ), $where
        );
        return $codigoImpreso;
    }

    //Cambiar con los datos del adecsys destacado
    public function registrarAvisoDestacado($correlativo, $aptitus, $rowAnuncio,
                                            $client)
    {
        $log                  = Zend_Registry::get('log');
        $ad                   = $aptitus->getAvisoDestacado(); // obteniendo una plantilla de aviso
        $fecha                = new Zend_Date();
        //$fechaActual = date('Y-m-d H:i:s');
        //$fecha->setDate($rowAnuncio['fechaPublicConfirmada'], 'YYYY-MM-dd');
        //$fecha->setDate($fechaActual, 'YYYY-MM-dd');
        $fecha                = $fecha->toString('YYYY-MM-dd');
        // @codingStandardsIgnoreStart
        $ad->Cod_Aviso        = $correlativo;
        $ad->Cod_Cliente      = $rowAnuncio['codigoEnte']; // Código ente
        $ad->Tip_Doc          = $rowAnuncio['tipoDocumento'];
        $ad->Num_Doc          = $rowAnuncio['numDocumento'];
        $ad->RznSoc_Nombre    = $rowAnuncio['razonSocial'];
        // Datos de contacto (Usuario que publica el aviso)
        $ad->Nom_Contacto     = $rowAnuncio['nombreContacto'];
        $ad->Ape_Pat_Contacto = $rowAnuncio['apePatContacto'];
        $ad->Ape_Mat_Contacto = $rowAnuncio['apeMatContacto'];
        if ($rowAnuncio['telefonoContacto'] == '') {
            $telefono = $rowAnuncio['telefonoContacto2'];
        } else {
            $telefono = $rowAnuncio['telefonoContacto'];
        }
        $ad->Telf_Contacto  = $telefono;
        $ad->Email_Contacto = $rowAnuncio['emailContacto'];
        // Datos del aviso

        $ad->Des_Puesto_Titulo       = $rowAnuncio['nombreTipoPuesto'];
        $ad->Texto_Aviso             = $rowAnuncio['textoAnuncioImpreso'].' '.$this->_config->adecsys->preCodigoAdecsys;
        $ad->Num_Palabras            = $rowAnuncio['beneficios']['npalabras']['valor'];
        $ad->Puesto_Aviso->Puesto_Id = $rowAnuncio['puestoAdecsysCode'];
        $ad->Puesto_Aviso->Esp_Id    = $rowAnuncio['puestoIdEspecialidad'];
        $extracargos                 = array();
        foreach ($rowAnuncio['extracargos'] as $key => $value) {
            $extracargos[] = $rowAnuncio['extracargos'][$key]['adecsys_cod'];
        }

        $tipoProducto = 'destacado';
        $medioPub     = 'aptitus';

        $ad = $aptitus->completeAdDestacado($ad, $tipoProducto, $medioPub);

        try {
            //Fecha
            $codigoImpreso = $aptitus->publicarAvisoDestacado($fecha, $ad,
                $extracargos);

            $log->info(
                "Código retornado de Adecsys: ".$codigoImpreso
            );
            $log->info(var_export($client->getLastRequest(), TRUE));
            file_put_contents(
                APPLICATION_PATH.'/../logs/compra_'.
                $rowAnuncio['compraId'].'_regAnuncio_'.$correlativo.'_envio.xml',
                $client->getLastRequest(), FILE_APPEND
            );
            file_put_contents(
                APPLICATION_PATH.'/../logs/compra_'.
                $rowAnuncio['compraId'].'_regAnuncio_'.$correlativo.'_rpta.xml',
                $client->getLastResponse(), FILE_APPEND
            );
            $log->info(var_export($client->getLastResponse(), TRUE));
        } catch (Exception $ex) {
            echo PHP_EOL.$ex->getMessage().PHP_EOL;
            print_r($ex->getTraceAsString());
        }
        $where = $this->_compraAdecsysCode->getAdapter()->quoteInto('id = ?',
            $correlativo);

        $okUpdateP = $this->_compraAdecsysCode->update(
            array(
            'medio_publicacion' => $rowAnuncio['medioPublicacionAdecsys'],
            'adecsys_code' => $codigoImpreso
            ), $where
        );
        return $codigoImpreso;
    }

    /**
     * Extiende un aviso, cambia los datos y migra las postulaciones
     *
     * @param int $avisoId
     * @param int $idUsuario
     */
    public function extenderAviso($avisoId, $idUsuario)
    {
        //Verificar si es un anuncio esta vencido
        $avisoOrigen = $this->_aw->getAvisoExtendido($avisoId);
        $this->_postulacion->extenderPostulaciones($avisoOrigen['extiende_a'],
            $avisoId);
        $tipo        = $this->_aw->getTipoAnuncioById($avisoOrigen['extiende_a']);
        if ($tipo == Application_Model_AnuncioWeb::TIPO_SOLOWEB ||
            $tipo == Application_Model_AnuncioWeb::TIPO_CLASIFICADO ||
            $tipo == Application_Model_AnuncioWeb::TIPO_PREFERENCIAL ||
            $tipo == Application_Model_AnuncioWeb::TIPO_DESTACADO)
                $this->_aw->bajaAnuncioWeb($avisoOrigen['extiende_a'], $avisoId,
                $idUsuario);
        $this->actualizarPostulantes($avisoId);
        $this->actualizarNuevasPostulaciones($avisoId);
    }

    public function extenderReferidos($avisoId)
    {
        $avisoOrigen    = $this->_aw->getAvisoExtendido($avisoId);
        $referidoModelo = new Application_Model_Referenciado;
        $referidos      = $referidoModelo->obtenerFullPorAnuncio($avisoOrigen['extiende_a']);

        foreach ($referidos as $referido) {
            unset($referido['id']);
            $referido['id_anuncio_web'] = $avisoId;
            $referidoModelo->registrar($referido);
        }
    }

    public function confirmarPago($compraId)
    {
        // AUN FALTA IMPLEMENTAR

        if ("extiende_a") {
            $this->extenderAviso($avisoId, $idUsuario);
        }

        //actualizar campos de pago anuncio compra
    }

    /**
     * Obtiene la cantidad de postulantes por cada anuncio y lo actualiza en la
     * tabla anuncio_web
     *
     * @param int $avisoId
     */
    public function actualizarPostulantes($avisoId)
    {
        $postulacion = new Application_Model_Postulacion();
        $aviso       = new Application_Model_AnuncioWeb();
        $where       = $aviso->getAdapter()->quoteInto("id = ?", $avisoId);
        $aviso->update(
            array('ntotal' => $postulacion->getPostulantesByAviso($avisoId)),
            $where
        );
    }

    /**
     * Obtiene la cantidad de invitaciones de un anuncio web y lo actualiza en la
     * tabla anuncio_wen
     *
     * @param int $avisoId
     */
    public function actualizarInvitaciones($avisoId)
    {
        $postulacion = new Application_Model_Postulacion();
        $aviso       = new Application_Model_AnuncioWeb();
        $where       = $aviso->getAdapter()->quoteInto("id = ?", $avisoId);
        $aviso->update(
            array('ninvitaciones' => $postulacion->getInvitacionesByAviso($avisoId)),
            $where
        );
    }

    /**
     * Obtiene la cantidad de nuevas postulaciones por anuncio web y lo actualiza
     * en la tabla anuncio_web
     *
     * @param int $avisoId
     */
    public function actualizarNuevasPostulaciones($avisoId)
    {
        $postulacion = new Application_Model_Postulacion();
        $aviso       = new Application_Model_AnuncioWeb();
        $where       = $aviso->getAdapter()->quoteInto("id = ?", $avisoId);
        $aviso->update(
            array('nnuevos' => $postulacion->getNuevasPostulacionesByAviso($avisoId)),
            $where
        );
    }

    /**
     * Obtiene la cantidad de mensajes no leidos por postulacion y lo actualiza
     * en la tabla anuncio_web y postulacion
     *
     * @param int $avisoId
     * @param int $postulacionId
     */
    public function actualizarMsgRsptNoLeidos($avisoId, $postulacionId)
    {
        $postulacion = new Application_Model_Postulacion();
        $aviso       = new Application_Model_AnuncioWeb();
        $where       = $aviso->getAdapter()->quoteInto("id = ?", $avisoId);

        $aviso->update(
            array('nmsjrespondidos' => $postulacion->getMsgRsptNoLeidosByAviso($avisoId)),
            $where
        );

        $where         = $postulacion->getAdapter()->quoteInto("id = ?",
            $postulacionId);
        $msgrespondido = $postulacion->getMsgRsptNoLeidosXPostulacion($postulacionId);
        $postulacion->update(
            array('msg_respondido' =>
            $msgrespondido
            ), $where
        );
        /* $zl = new ZendLucene();
          $zl->updateIndexPostulaciones($postulacionId, "msgrespondido", $msgrespondido); */
    }

    public function actualizarMsgRsptPerfil($idAw, $idPostulacion)
    {
        $modelMsj   = new Application_Model_Mensaje();
        $idMensajes = $modelMsj->getIdMensajesRsptaXPostulacion($idPostulacion);
        $where      = $modelMsj->getAdapter()->quoteInto('id in (?)',
            (int) $idMensajes);

        $modelMsj->update(array('leido' => 1), $where);

        $postulacion = new Application_Model_Postulacion();

        $aviso = new Application_Model_AnuncioWeb();
        $where = $aviso->getAdapter()->quoteInto("id = ?", (int) $idAw);

        $msjNoLeidos = $postulacion->getMsgRsptNoLeidosByAviso($idAw);
        $aviso->update(
            array('nmsjrespondidos' => $msjNoLeidos), $where
        );

        $where                    = $postulacion->getAdapter()->quoteInto("id = ?",
            $idPostulacion);
        $msgNoLeidoPorPostulacion = $postulacion->getMsgRsptNoLeidosXPostulacion($idPostulacion);
        $postulacion->update(array(
            'msg_respondido' => $msgNoLeidoPorPostulacion
            ), $where
        );
    }

    /**
     * Guarda un registro en la tabla anuncio_web y devuelve el ID del anuncio
     * ingresado
     *
     * @param array $dataPost
     * @param String $extiende
     * @return int
     */
    public function _insertarNuevoPuesto(array $dataPost, $extiende = null,
                                         $emp = "", $republica = null)
    {
        $tarifa      = new Application_Model_Tarifa();
        $datosTarifa = $tarifa->getProductoByTarifa($dataPost['id_tarifa']);

        if ($dataPost['salario'] == -1) {
            $salario[0] = null;
            $salario[1] = null;
        } else {
            $salario = explode('-', $dataPost['salario']);
            if ($salario[1] == 'max') {
                $salario[1] = null;
            }
        }
        $usuario = $this->auth['usuario'];
        if (($emp != "" || $emp != null) && is_array($emp)) {
            $empresa = $emp;
        } elseif (isset($this->auth['empresa'])) {
            $empresa = $this->auth['empresa'];
        } else {
            $empresa = "";
        }

        if ($empresa == "") {
            $empresa['logo']             = $dataPost['logo_empresa'];
            $empresa['id']               = $dataPost['id_empresa'];
            $empresa['nombre_comercial'] = $dataPost['nombre_comercial'];
        }

        if ($dataPost['mostrar_empresa'] == 1) {
            $nombreEmpresa = $empresa['nombre_comercial'];
        } else {
            $nombreEmpresa = $dataPost['otro_nombre_empresa'];
        }
        $anuncionWeb = new Application_Model_AnuncioWeb();
        $slugFilter  = new App_Filter_Slug();
        $stFilter    = new Zend_Filter_StripTags();
        //$genPassword = $action->getHelper('GenPassword');
        $_tu         = new Application_Model_TempUrlId();

        $anuncio = array_merge(
            array('extiende_a' => $extiende),
            array(
            'id_puesto' => isset($dataPost['id_puesto']) ? $dataPost['id_puesto']
                    : Application_Model_Puesto::OTROS_PUESTO_ID,
            'id_empresa_membresia' => isset($dataPost['id_empresa_membresia']) ? $dataPost['id_empresa_membresia']
                    : NULL,
            'id_producto' => isset($dataPost['id_producto']) ? $dataPost['id_producto']
                    : NULL,
            'puesto' => $stFilter->filter($dataPost['nombre_puesto']),
            'id_area' => $dataPost['id_area'],
            'id_nivel_puesto' => $dataPost['id_nivel_puesto'],
            'funciones' => $dataPost['funciones'],
            'responsabilidades' => $dataPost['responsabilidades'],
            'mostrar_salario' => $dataPost['mostrar_salario'],
            'mostrar_empresa' => $dataPost['mostrar_empresa'],
            'salario_min' => $salario[0],
            'salario_max' => $salario[1],
            'online' => '0',
            'borrador' => '1',
            'destacado' => 0,
            'id_empresa' => $empresa['id'],
            'id_ubigeo' => $dataPost['id_ubigeo'],
            'slug_pais' => $dataPost['slugpais'],
            'fh_creacion' => date('Y-m-d H:i:s'),
            'fh_edicion' => date('Y-m-d H:i:s'),
            'creado_por' => (!empty($usuario->id)) ? $usuario->id : $dataPost['id_usuario'],
            //'url_id' => $genPassword->_genPassword(5),
            'prioridad' => $dataPost['prioridad'],
            'url_id' => $_tu->popUrlId(),
            'slug' => $slugFilter->filter($dataPost['nombre_puesto']),
            'empresa_rs' => $nombreEmpresa,
            'estado' => Application_Model_AnuncioWeb::ESTADO_REGISTRADO,
            'origen' => 'apt_2',
            'id_tarifa' => $datosTarifa['id_tarifa'],
            'id_producto' => $datosTarifa['id_producto'],
            'tipo' => $datosTarifa['tipo'],
            'medio_publicacion' => $datosTarifa['medio_publicacion'],
            'logo' => $empresa["logo"],
            'discapacidad' => isset($dataPost["discapacidad"]) ? $dataPost["discapacidad"]
                    : 0,
            'chequeado' => 1,
            'correo' => isset($dataPost['correo']) ? $dataPost['correo'] : ''
            )
        );

        if ($republica != null) {
            $anuncio['republicado'] = $republica;
        }
        if (empty($anuncio['id_ubigeo'])) {
            $modelEmpresa         = new Application_Model_Empresa();
            $dEmp                 = $modelEmpresa->obtenerPorId($anuncio['id_empresa'],
                array('id_ubigeo'));
            if (!empty($dEmp['id_ubigeo']))
                    $anuncio['id_ubigeo'] = $dEmp['id_ubigeo'];
            else $anuncio['id_ubigeo'] = 3928;
        }
        //var_dump($anuncio);exit;
        $this->_avisoId = $anuncionWeb->insert($anuncio);

        if ($extiende == null) {
            $where = $anuncionWeb->getAdapter()->quoteInto('id = ?',
                $this->_avisoId);
            $anuncionWeb->update(array('extiende_a' => $this->_avisoId), $where);
        }

        if ($extiende != null && is_null($republica)) {
            $arrayAnunciOld = $anuncionWeb->getAvisoInfoById($extiende);
            $where          = $anuncionWeb->getAdapter()->quoteInto('id = ?',
                $this->_avisoId);
            $anuncionWeb->update(
                array(
                'url_id' => $arrayAnunciOld['url_id'],
                'extiende_a' => $arrayAnunciOld['id'],
                'adecsys_code' => $arrayAnunciOld['adecsys_code']
                ), $where
            );
            //$this->_SolrAviso->DeleteAvisoSolr($extiende);
            $this->extenderReferidos($this->_avisoId);
        }

        $idAviso = $this->_avisoId;
        $this->_SolrAviso->addAvisoSolr($idAviso);
        //Actualizar índices de buscamas
        //$resultado = exec("curl -X POST -d 'api_key=".$this->_buscamasConsumerKey."&nid=".$idAviso."&site=".$this->_buscamasUrl."' ".$this->_buscamasPublishUrl);

        return $this->_avisoId;
    }

    /**
     * Registra los estudios relacionados al anuncio
     *
     * @param App_Form_Manager $managerEstudio
     */
    public function _insertarEstudios(App_Form_Manager $managerEstudio)
    {
        $estudio       = new Application_Model_AnuncioEstudio();
        //        $filter = new App_Filter_Slug();
        $formsEstudios = $managerEstudio->getForms();
        foreach ($formsEstudios as $form) {
            $data = $form->getValues();
            if (($data["id_nivel_estudio"] != null && $data['id_carrera']) && ($data['id_nivel_estudio']
                != -1 && $data['id_carrera'] != -1)) {
                $otracar = null;
                if ($data['id_carrera'] != null) {
                    $validaotroCar = $estudio->getValCarrera($data['id_carrera']);
                    if ($validaotroCar['nombre'] == 'Otros' && $data['otra_carrera']
                        != '' && $data['id_carrera'] != '')
                            $otracar       = $data['otra_carrera'];
                    else $otracar       = null;
                }
                //$otracar = $filter->sanear_string($otracar);


                $estudio->insert(
                    array(
                        'id_anuncio_web' => $this->_avisoId,
                        'id_nivel_estudio' => $data['id_nivel_estudio'],
                        'id_nivel_estudio_tipo' => $data['id_nivel_estudio_tipo'],
                        'id_carrera' => $data['id_carrera'],
                        'otra_carrera' => $otracar
                    )
                );
            }
        }
    }

    /**
     * Registra las experiencias relacionados a un anuncio web
     *
     * @param App_Form_Manager $managerExperiencia
     */
    public function _insertarExperiencia(App_Form_Manager $managerExperiencia)
    {
        $experiencia       = new Application_Model_AnuncioExperiencia();
        $formsExperiencias = $managerExperiencia->getForms();
        foreach ($formsExperiencias as $form) {
            $data = $form->getValues();

            if ($data['id_nivel_puesto'] != 0 && $data['id_area'] != 0) {
                $experiencia->insert(
                    array(
                        'id_anuncio_web' => $this->_avisoId,
                        'id_nivel_puesto' => $data['id_nivel_puesto'],
                        'id_area' => $data['id_area'],
                        'experiencia' => $data['experiencia']
                    )
                );
            }
        }
    }

    /**
     * Registra los programas de computo asociados a un anuncio web
     *
     * @param App_Form_Manager $managerPrograma
     */
    public function _insertarPrograma(App_Form_Manager $managerPrograma)
    {
        $programa       = new Application_Model_AnuncioProgramaComputo();
        $formsProgramas = $managerPrograma->getForms();
        foreach ($formsProgramas as $form) {
            $data = $form->getValues();
            if ($data['id_programa_computo'] != "0" && $data['nivel'] != "0") {
                $programa->insert(
                    array(
                        'id_programa_computo' => $data['id_programa_computo'],
                        'id_anuncio_web' => $this->_avisoId,
                        'nivel' => $data['nivel']
                    )
                );
            }
        }
    }

    /**
     * Registra los idiomas relacionas a un anuncio web
     *
     * @param App_Form_Manager $managerIdioma
     */
    public function _insertarIdiomas(App_Form_Manager $managerIdioma)
    {
        $idioma       = new Application_Model_AnuncioIdioma();
        $formsIdiomas = $managerIdioma->getForms();
        foreach ($formsIdiomas as $form) {
            $data = $form->getValues();
            if ($data['id_idioma'] != "0" && $data['nivel_idioma'] != "0") {
                $idioma->insert(
                    array(
                        'id_idioma' => $data['id_idioma'],
                        'id_anuncio_web' => $this->_avisoId,
                        'nivel' => $data['nivel_idioma']
                    )
                );
            }
        }
    }

    /**
     * Registra las preguntas y genera un cuestionario para la empresa
     *
     * @param App_Form_Manager $managerPregunta
     */
    public function _insertarPreguntas(App_Form_Manager $managerPregunta,
                                       $idEmpresa = null)
    {

        if (isset($idEmpresa)) {
            $modelEmpresa                = new Application_Model_Empresa();
            $empresa                     = $modelEmpresa->getEmpresa($idEmpresa);
            $empresa['nombre_comercial'] = $empresa['nombrecomercial'];
        } else {
            $empresa = $this->auth['empresa'];
        }

        $formsPreguntas = $managerPregunta->getForms();
        foreach ($formsPreguntas as $fpreg) {
            $data = $fpreg->getValues();
            if ($data['pregunta'] != "") {
                $cuestionario   = new Application_Model_Cuestionario();
                $cuestionarioId = $cuestionario->insert(
                    array(
                        'id_empresa' => isset($idEmpresa) ? $empresa['id_empresa']
                                : $empresa['id'],
                        'id_anuncio_web' => $this->_avisoId,
                        'nombre' =>
                        'Cuestionario de la empresa '.$empresa['nombre_comercial']
                    )
                );
                break;
            }
        }
        foreach ($formsPreguntas as $form) {
            $data = $form->getValues();
            if ($data['pregunta'] != "") {
                $pregunta = new Application_Model_Pregunta();
                $pregunta->insert(
                    array(
                        'id_cuestionario' => $cuestionarioId,
                        'pregunta' => $data['pregunta']
                    )
                );
            }
        }
    }

    public function _crearSlug($valuesPostulante, $lastId)
    {
        $slugFilter = new App_Filter_Slug(
            array('field' => 'slug',
            'model' => $this->_empresa)
        );

        $slug = $slugFilter->filter(
            $valuesPostulante['razon_social'].' '.
            $valuesPostulante['ruc'].' '.
            substr(md5($lastId), 0, 8)
        );
        return $slug;
    }

    /**
     * Actualiza los datos del anuncio web
     *
     * @param Application_Form_Paso2PublicarAviso $formPuesto
     * @param int $idAviso
     */
    public function _actualizarDatosPuesto(
    Application_Form_Paso2PublicarAviso $formPuesto, $idAviso,
    $idEmpresa = null, $idUbigeo = null, $nomUsu = null, $idusuario = null
    )
    {

        $aviso      = new Application_Model_AnuncioWeb();
        $arrayAviso = $aviso->getAvisoById($idAviso);
        $slugFilter = new App_Filter_Slug();
        $stFilter   = new Zend_Filter_StripTags();

        @$this->_cache->remove('AnuncioWeb_getAvisoInfoById_'.$idAviso);
        @$this->_cache->remove('AnuncioWeb_getAvisoById_'.$idAviso);
        //$this->_cache->remove('anuncio_web_'.$arrayAviso['url_id']);

        if (strpos($this->getFrontController()->getModuleDirectory(), 'admin')) {
            $admin = true;
        }
        $data       = $formPuesto->getValues();
        $arrayAviso = $aviso->getAvisoById($idAviso);
        if (isset($data['salario']) && $data['salario'] == -1) {
            $salario[0] = null;
            $salario[1] = null;
        } else {
            $salario = explode('-', $data['salario']);
            if (isset($salario[1]) && $salario[1] == 'max') {
                $salario[1] = null;
            }
        }

        if (isset($idEmpresa)) {
            $modelEmpresa                = new Application_Model_Empresa();
            $empresa                     = $modelEmpresa->getEmpresa($idEmpresa);
            $empresa['nombre_comercial'] = $empresa['nombrecomercial'];
            unset($empresa['nombrecomercial']);
        } else {
            $empresa = $this->auth['empresa'];
        }

        $logo = '';
        if ($data['mostrar_empresa'] == 1) {
            $logo          = $aviso->obtenerLogo($idAviso);
            $nombreEmpresa = $empresa['nombre_comercial'];
        } else {
            $logo          = '';
            $nombreEmpresa = $data['otro_nombre_empresa'];
        }


        $where = $aviso->getAdapter()
            ->quoteInto('id = ?', $idAviso);
        if ($arrayAviso['online'] == 1 && !isset($admin)) {
            $aviso->update(
                array(
                'puesto' => $stFilter->filter($data['nombre_puesto']),
                'funciones' => $data['funciones'],
                'responsabilidades' => $data['responsabilidades'],
                'id_ubigeo' => $idUbigeo,
                'id_area' => $data['id_area'],
                'empresa_rs' => $nombreEmpresa,
                'mostrar_empresa' => $data['mostrar_empresa'],
                'fh_edicion' => date('Y-m-d H:i:s'),
                'slug' => $slugFilter->filter($data['nombre_puesto']),
                'discapacidad' => isset($data["discapacidad"]) ? $data["discapacidad"]
                        : 0,
                //'correo' => $data['correo']
                ), $where
            );
        } else {

            $aviso->update(
                array(
                'id_puesto' => isset($data['id_puesto']) ? $data['id_puesto'] : Application_Model_Puesto::OTROS_PUESTO_ID,
                'puesto' => $stFilter->filter($data['nombre_puesto']),
                'empresa_rs' => $nombreEmpresa,
                'id_ubigeo' => $idUbigeo,
                'id_area' => isset($data['id_area']) ? $data['id_area'] : null,
                'id_nivel_puesto' => $data['id_nivel_puesto'],
                'funciones' => $data['funciones'],
                'responsabilidades' => $data['responsabilidades'],
                'mostrar_salario' => $data['mostrar_salario'],
                'mostrar_empresa' => $data['mostrar_empresa'],
                'salario_min' => $salario[0],
                'salario_max' => $salario[1],
                'fh_edicion' => date('Y-m-d H:i:s'),
                'chequeado' => '1',
                'correo' => $data['correo'],
                'logo' => $logo,
                'adecsys_code' => $data['adecsys_code'],
                'discapacidad' => isset($data["discapacidad"]) ? $data["discapacidad"]
                        : 0,
                ), $where
            );

            $mailer = new App_Controller_Action_Helper_Mail();
            //Enviar coreo si es diferente los adecsys_code y no es vacío el que viene por POST
            if ($arrayAviso['adecsys_code'] <> $data['adecsys_code'] && !empty($data['adecsys_code'])) {
                //Obtener correos
                //$nombreUsuario = $this->auth['usuario']->nombre. " ".$this->auth['usuario']->apellido;
                $emailing = explode(',', $this->_config->asociaAdecsys->correo);
                $dataMail = array(
                    'adecsys' => $data['adecsys_code'],
                    'puesto' => $data['nombre_puesto'],
                    'usuAdmin' => $nomUsu
                );
                foreach ($emailing as $email) {
                    if (!empty($email)) {
                        $dataMail['to'] = $email;
                        $mailer->asociarAvisoAdecsys($dataMail);
                    }
                }
            }
        }

        if ($arrayAviso['online'] == 0 && $arrayAviso['borrador'] == 1) {

            $aviso->update(
                array(
                'slug' => $slugFilter->filter($data['nombre_puesto'])
                ), $where
            );
        }
        // var_dump($idEmpresa,$this->auth['usuario']->id);exit;
        if (isset($idEmpresa) && isset($idusuario) && !empty($idusuario)) {
            $aviso->update(array('creado_por' => $data['id_usuario']), $where);
        }

        //Actualizar índices
        //$resultado = exec("curl -X POST -d 'api_key=".$this->_buscamasConsumerKey."&nid=".$idAviso."&site=".$this->_buscamasUrl."' ".$this->_buscamasPublishUrl);
        $this->_SolrAviso->addAvisoSolr($idAviso);
        $idEmpresa           = $arrayAviso['id_empresa'];
        $cacheId             = 'AnuncioWeb_getAvisoIdByUrl_Api_'.$arrayAviso['url_id'].'_'.$idEmpresa;
        $cacheIdestudios     = 'AnuncioWeb_getEstudiosByAnuncio_Api_'.$arrayAviso['url_id'].'_'.$idEmpresa;
        $cacheIdexperiencias = 'AnuncioWeb_getExperienciasByAnuncio_Api_'.$arrayAviso['url_id'].'_'.$idEmpresa;
        $cacheIdidiomas      = 'AnuncioWeb_getIdiomasByAnuncio_Api_'.$arrayAviso['url_id'].'_'.$idEmpresa;
        $cacheIdprogramas    = 'AnuncioWeb_getIdiomasByAnuncio_Api_'.$arrayAviso['url_id'].'_'.$idEmpresa;
        $cachePre            = 'AnuncioWeb_getPreguntas_Api_'.$arrayAviso['url_id'].'_'.$idEmpresa;




        @$this->_cache->remove($cacheId);
        @$this->_cache->remove($cacheIdestudios);
        @$this->_cache->remove($cacheIdexperiencias);
        @$this->_cache->remove($cacheIdidiomas);
        @$this->_cache->remove($cacheIdprogramas);
        @$this->_cache->remove($cachePre);



        @$this->_cache->remove('AnuncioWeb_getAvisoInfo_'.$arrayAviso['url_id']);
        @$this->_cache->remove('AnuncioWeb_getAvisoInfo_'.$arrayAviso['url_id']);
        @$this->_cache->remove('AnuncioWeb_getAvisoInfoficha_'.$arrayAviso['id']);
        @$this->_cache->remove('AnuncioWeb_getAvisoInfoById_'.$idAviso);
        @$this->_cache->remove('AnuncioWeb_getFullAvisoById_'.$idAviso);
        @$this->_cache->remove('AnuncioWeb_getAvisoById_'.$idAviso);
        @$this->_cache->remove('anuncio_web_'.$arrayAviso['url_id']);
        @$this->_cache->remove('anuncio_web_'.$arrayAviso['url_id'].'_'.$arrayAviso['id']);
    }

    /**
     * Actualiza los estudios de un anuncio web
     *
     * @param App_Form_Manager $managerEstudio
     * @param int $idAviso
     */
    public function _actualizarEstudios(App_Form_Manager $managerEstudio,
                                        $idAviso)
    {
        $formEstudio = $managerEstudio->getForms();
        foreach ($formEstudio as $form) {
            $data    = $form->getValues();
            $idEst   = $data['id_estudio'];
            $estudio = new Application_Model_AnuncioEstudio();
            unset($data['id_estudio']);
            if ($data['id_nivel_estudio'] != 0 && $data['id_carrera'] != 0) {
                if ($data['id_carrera'] == 0) {
                    $data['id_carrera'] = null;
                }
                $otracar = null;
                if ($data['id_carrera'] != null) {
                    $validaotroCar = $estudio->getValCarrera($data['id_carrera']);
                    if ($validaotroCar['nombre'] == 'Otros' && $data['otra_carrera']
                        != '' && $data['id_carrera'] != '')
                            $otracar       = $data['otra_carrera'];
                    else $otracar       = null;
                }
                if ($idEst) {
                    $where = $estudio->getAdapter()
                            ->quoteInto('id_anuncio_web = ?', $idAviso).
                            $estudio->getAdapter()
                            ->quoteInto(' and id = ?', $idEst);
                    $estudio->update(
                        array(
                        'id_nivel_estudio' => $data['id_nivel_estudio'],
                        'id_nivel_estudio_tipo' => $data['id_nivel_estudio_tipo'],
                        'id_carrera' => $data['id_carrera'],
                        'otra_carrera' => $otracar
                        ), $where
                    );
                } else {
                    $estudio->insert(
                        array(
                            'id_anuncio_web' => $idAviso,
                            'id_nivel_estudio' => $data['id_nivel_estudio'],
                            'id_nivel_estudio_tipo' => $data['id_nivel_estudio_tipo'],
                            'id_carrera' => $data['id_carrera'],
                            'otra_carrera' => $otracar
                        )
                    );
                }
            }
        }
    }

    /**
     * Actualiza las experiencias de un anuncio web
     *
     * @param App_Form_Manager $managerExperiencia
     * @param int $idAviso
     */
    public function _actualizarExperiencas(App_Form_Manager $managerExperiencia,
                                           $idAviso)
    {
        $formsExperiencia = $managerExperiencia->getForms();

        foreach ($formsExperiencia as $form) {
            $data  = $form->getValues();
            $idExp = $data['id_Experiencia'];

            $experiencia = new Application_Model_AnuncioExperiencia();

            unset($data['id_Experiencia']);
            if ($data['id_nivel_puesto'] != "" && $data['id_area'] != "") {

                if ($idExp) {
                    $where = $experiencia->getAdapter()
                            ->quoteInto('id_anuncio_web = ?', $idAviso).
                            $experiencia->getAdapter()
                            ->quoteInto(' and id = ?', $idExp);
                    $experiencia->update(
                        array(
                        'id_nivel_puesto' => $data['id_nivel_puesto'],
                        'id_area' => $data['id_area'],
                        'experiencia' => $data['experiencia']
                        ), $where
                    );
                } else {
                    $idExperiencia = $experiencia->insert(
                        array(
                            'id_anuncio_web' => $idAviso,
                            'id_nivel_puesto' => $data['id_nivel_puesto'],
                            'id_area' => $data['id_area'],
                            'experiencia' => $data['experiencia']
                        )
                    );
                }
            }
        }
    }

    /**
     * Actualizar el idioma del anuncio
     *
     * @param App_Form_Manager $managerIdioma
     * @param int $idAviso
     */
    public function _actualizarIdioma(App_Form_Manager $managerIdioma, $idAviso)
    {
        $formIdioma = $managerIdioma->getForms();
        foreach ($formIdioma as $form) {
            $data   = $form->getValues();
            $idIdi  = $data['id_dominioIdioma'];
            $idioma = new Application_Model_AnuncioIdioma();
            unset($data['id_dominioIdioma']);
            if (!empty($data['id_idioma']) && !empty($data['nivel_idioma'])) {
                if ($idIdi) {
                    $where = $idioma->getAdapter()
                            ->quoteInto('id_anuncio_web = ?', $idAviso).
                            $idioma->getAdapter()
                            ->quoteInto(' and id = ?', $idIdi);
                    $idioma->update(
                        array(
                        'id_idioma' => $data['id_idioma'],
                        'nivel' => $data['nivel_idioma']
                        ), $where
                    );
                } else {
                    $idioma->insert(
                        array(
                            'id_idioma' => $data['id_idioma'],
                            'id_anuncio_web' => $idAviso,
                            'nivel' => $data['nivel_idioma']
                        )
                    );
                }
            }
        }
    }

    /**
     * Actualiza los programas de nu anuncio web
     *
     * @param App_Form_Manager $managerPrograma
     * @param int $idAviso
     */
    public function _actualizarPrograma(App_Form_Manager $managerPrograma,
                                        $idAviso)
    {
        $formPrograma = $managerPrograma->getForms();
        foreach ($formPrograma as $form) {

            $data   = $form->getValues();
            $idProg = $data['id_dominioComputo'];
            if (!empty($data['id_programa_computo']) && !empty($data['nivel'])) {
                $programa = new Application_Model_AnuncioProgramaComputo();
                unset($data['id_dominioComputo']);

                if ($idProg) {

                    $where = $programa->getAdapter()
                            ->quoteInto('id_anuncio_web = ?', $idAviso).
                            $programa->getAdapter()
                            ->quoteInto(' and id = ?', $idProg);
                    $programa->update(
                        array(
                        'id_programa_computo' => $data['id_programa_computo'],
                        'nivel' => $data['nivel']
                        ), $where
                    );
                } else {
                    $programa->insert(
                        array(
                            'id_programa_computo' => $data['id_programa_computo'],
                            'id_anuncio_web' => $idAviso,
                            'nivel' => $data['nivel']
                        )
                    );
                }
            }
        }
    }

    public function _actualizarPregunta(App_Form_Manager $managerPregunta,
                                        $idAviso, $idEmpresa = null)
    {
        $formPregunta = $managerPregunta->getForms();
        $cuestionario = new Application_Model_Cuestionario();
        if (!$cuestionario->getPreguntasByAnuncioWeb($idAviso)) {
            if (isset($idEmpresa)) {
                $modelEmpresa                = new Application_Model_Empresa();
                $empresa                     = $modelEmpresa->getEmpresa($idEmpresa);
                $empresa['nombre_comercial'] = $empresa['nombrecomercial'];
            } else {
                $empresa = $this->auth['empresa'];
            }
            $cuestionarioId = $cuestionario->insert(
                array(
                    'id_empresa' => isset($idEmpresa) ? $empresa['id_empresa'] : $empresa['id'],
                    'id_anuncio_web' => $idAviso,
                    'nombre' =>
                    'Cuestionario de la empresa '.$empresa['nombre_comercial']
                )
            );
        } else {
            $cuestionarioId = $cuestionario->getCuestionarioByAnuncioWeb($idAviso);
        }
        foreach ($formPregunta as $form) {
            $data   = $form->getValues();
            $idPreg = $data['id_pregunta'];
            if ($data['pregunta'] != "") {
                $pregunta = new Application_Model_Pregunta();
                if ($idPreg) {
                    $where = $pregunta->getAdapter()
                            ->quoteInto('id_cuestionario = ?', $cuestionarioId).
                            $pregunta->getAdapter()
                            ->quoteInto(' and id = ?', $idPreg);
                    $pregunta->update(
                        array(
                        'pregunta' => $data['pregunta'],
                        'estado' => 1
                        ), $where
                    );
                } else {
                    $pregunta->insert(
                        array(
                            'id_cuestionario' => $cuestionarioId,
                            'pregunta' => $data['pregunta'],
                            'estado' => 1
                        )
                    );
                }
            }
        }
    }

    /**
     *
     * @param int $idCompra
     * @param int $idEmpresa
     * @return boolean
     */
    public function perteneceCompraAEmpresa($idCompra, $idEmpresa)
    {
        $cant = $this->_compra->perteneceCompraEmpresa($idCompra, $idEmpresa);
        if (is_numeric($idCompra) && $cant > 0) {
            return true;
        }
        return false;
    }

    public function perteneceAvisoAEmpresa($idAviso, $idEmpresa)
    {
        $cant = $this->_aw->perteneceAvisoEmpresa($idAviso, $idEmpresa);
        if (is_numeric($idAviso) && $cant > 0) {
            return true;
        }
        return false;
    }

    public function perteneceAvisoImpresoEmpresa($idAvisoImpreso, $idEmpresa)
    {
        $cant = $this->_ai->perteneceAvisoImpresoEmpresa($idAvisoImpreso,
            $idEmpresa);
        if (is_numeric($idAvisoImpreso) && $cant > 0) {
            return true;
        }
        return false;
    }

    public function getUrlIdGeneralPostulante($idData, $tipoModelo)
    {
        $nomModel = '';
        if ('Pregunta' == $tipoModelo) {
            $nomModel = 'Application_Model_'.ucfirst($tipoModelo);
        } else {
            $nomModel = 'Application_Model_Anuncio'.ucfirst($tipoModelo);
        }
        $model = new $nomModel();
        $urlId = $model->getUrlById($idData);
        return $urlId;
    }

    /**
     * Genera una URL para hacer una redireccion en el editar aviso
     *
     * @param unknown_type $url
     */
    public function EncodeRedirect($url)
    {
        $url = str_replace('/', '*', $url);
        return base64_encode($url);
    }

    public function DecodeRedirect($url)
    {
        $url = base64_decode($url);
        $url = str_replace('*', '/', $url);
        return $url;
    }

    public function accesoPublicarAvisoAdmin($idProd, $rol)
    {
        if (Application_Form_Login::ROL_ADMIN_SOPORTE == $rol) {
            $idProdAcceso = 1;
            if ($idProdAcceso == $idProd) {
                return true;
            }
        } elseif (Application_Form_Login::ROL_ADMIN_CALLCENTER == $rol) {
            $idProdAcceso = '2,3,4';
            $var          = explode(',', $idProdAcceso);
            foreach ($var as $row) {
                if ($row == $idProd) {
                    return true;
                }
            }
        } elseif (Application_Form_Login::ROL_ADMIN_MASTER == $rol) {
            $idProdAcceso = '1,2,3,4';
            $var          = explode(',', $idProdAcceso);
            foreach ($var as $row) {
                if ($row == $idProd) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getFechaPublicacionImpresoByPaquete($paquete)
    {
        $cierre   = $this->_config->cierre->toArray();
        $fecNow   = new Zend_Date();
        $fecNow->setLocale(new Zend_Locale(Zend_Locale::ZFDEFAULT));
        $fecVenc  = clone $fecNow;
        $fecVenc->set($cierre[$paquete]['dia'], Zend_Date::WEEKDAY_DIGIT);
        $fecVenc->set($cierre[$paquete]['hora'], Zend_Date::HOUR);
        $fecVenc->set(0, Zend_Date::MINUTE);
        $fecVenc->set(0, Zend_Date::SECOND);
        $fecImpre = clone $fecVenc;
        $fecImpre->set(0, Zend_Date::HOUR);
        if ($cierre[$paquete]['semanaActual'] == 0) {
            $fecImpre->add(7, Zend_Date::DAY);
        }
        $fecImpre->set($cierre[$paquete]['diaPublicacion'],
            Zend_Date::WEEKDAY_DIGIT);
        if ($fecNow->isLater($fecVenc)) {
            $fecImpre->add(7, Zend_Date::DAY);
        }
        return $fecImpre;
    }

    public function getFechaCierreImpresoByPaquete($paquete)
    {
        $cierre    = $this->_config->cierre->toArray();
        $cierre[$paquete]['hora'];
        $fecCierre = new Zend_Date();
        $fecCierre->setLocale(Zend_Locale::ZFDEFAULT);
        $fecCierre->set($cierre[$paquete]['dia'], Zend_Date::WEEKDAY_DIGIT);
        $fecCierre->set($cierre[$paquete]['hora'], Zend_Date::HOUR);
        $fecCierre->set(0, Zend_Date::MINUTE);
        $fecCierre->set(0, Zend_Date::SECOND);
        $now       = date('Y-m-d H:i:s');
        if ($fecCierre->isEarlier($now, 'YYYY-MM-dd h:m:s')) {
            $fecCierre->add(7, Zend_Date::DAY);
        }
        return $fecCierre;
    }

    /**
     *
     * @param int $idEmpresa
     * @param string $idProducto
     */
    public function getDiasPrioridadResultado($idEmpresa, $idProducto = null)
    {
        $empresaMembresia = new Application_Model_EmpresaMembresia();
        $membresia        = $empresaMembresia->getDetalleEmpresaMembresiaActivaByIdEmpresa($idEmpresa);
        if (!empty($membresia['membresia'])) {
            foreach ($membresia['beneficios'] as $beneficio) {
                if ($beneficio['med_codigo'] == 'ndiasprio')
                        return $beneficio['med_valor'];
            }
        }
        $producto   = new Application_Model_Producto();
        $beneficios = $producto->listarBeneficios($idProducto);
        foreach ($beneficios as $beneficio) {
            if ($beneficio['codigo'] == 'ndiasprio') return $beneficio['valor'];
        }
        return 0;
    }

    /**
     *
     * @param type $tipoAnuncio
     * @param type $idEmpresa
     * @return type
     */
    public function getOrdenPrioridad($tipoAnuncio, $idEmpresa)
    {
//        $empresaMembresia = new Application_Model_EmpresaMembresia();
//        $membresia = $empresaMembresia->getDetalleEmpresaMembresiaActivaByIdEmpresa($idEmpresa);
//
//        if (!empty($membresia['membresia'])) {
//            foreach ($membresia['beneficios'] as $beneficio) {
//                if ($beneficio['med_codigo'] == 'prioridad')
//                        return $beneficio['med_valor'];
//            }
//        }
        $config    = Zend_Registry::get('config');
        $prioridad = 6;
        switch ($tipoAnuncio) {
            case 'preferencial':
                $prioridad = (empty($config->prioridad->anuncio->preferencial)) ?
                    4 : $config->prioridad->anuncio->preferencial;
                break;
            case 'clasificado':
                $prioridad = (empty($config->prioridad->anuncio->clasificado)) ?
                    5 : $config->prioridad->anuncio->clasificado;
                break;
        }
        return $prioridad;
    }

    public function validarAdjuntoScot($idImpreso)
    {

        $obj = new Application_Model_AnuncioImpreso();

        $impreso   = $obj->getDataAnuncioPreferencialImpreso($idImpreso);
        $condicion = false;
        if ($impreso['tipo_diseno'] == Application_Model_AnuncioImpreso::TIPO_DISENIO_PRE_DISENIADO) {
            $modelPlantilla = new Application_Model_Plantilla();
            $arrayReqPlan   = $modelPlantilla->requiereAdjuntoByIdPlantilla($impreso['id_plantilla']);

            if ($arrayReqPlan['contiene_logo'] == 1) {
                $data      = $this->verificarArchivoAdjuntoEnScot($idImpreso);
                $condicion = $data != 0;
            } else {
                $condicion = true;
            }
            $texto = trim(strip_tags($impreso['texto']));
            if (
                strlen($texto) == 0 ||
                ($texto) == "" ||
                empty($texto)
            ) {
                $condicion = false;
            }
        } else {
            $data      = $this->verificarArchivoAdjuntoEnScot($idImpreso);
            $condicion = $data != 0;
        }
        return $condicion;
    }

    public function getTipoDePrioridadPorTarifaId($idEmpresa, $idTarifa = 1,
                                                  $origen = 'apt_2',
                                                  $tipo = 'web')
    {
        $empresaMembresia = new Application_Model_EmpresaMembresia();
        $membresia        = $empresaMembresia->getDetalleEmpresaMembresiaActivaByIdEmpresa($idEmpresa);

        if (!empty($membresia['membresia']) && $origen != 'adecsys') {
            $tarifa   = new Application_Model_Tarifa();
            $producto = $tarifa->getProductoByIdTarifa($idTarifa);
            return $producto['tipo'];
        } elseif (!empty($membresia['membresia']) && $origen == 'adecsys') {
            return $tipo;
        }
        return 'web';
    }

    private function getFlagsContrato($rowAnuncio)
    {

        if (!is_null($rowAnuncio['tipoContrato']) && $rowAnuncio['tipoContrato']
            != "") {
            $contrato["Tipo_Contrato"] = $rowAnuncio['tipoContrato'];
        } else {
            $contrato["Tipo_Contrato"] = 'N';
        }

        switch ($rowAnuncio['medioPago']) {
            case 'pe':
            case 'visa':
            case 'mc':
                $contrato['Form_Pago'] = 'C';
                break;
            default:
                $contrato['Form_Pago'] = 'R';
        }

        return $contrato;
    }

    public function pruebaBuscamas()
    {
        echo "KEY: ".$this->_buscamasConsumerKey."<br>";
        echo "PublishUrl: ".$this->_buscamasPublishUrl."<br>";
        echo "URL: ".$this->_buscamasUrl."<br>";

        echo "CURL _INIT: "."curl -X POST -d 'api_key=".$this->_buscamasConsumerKey."&nid=6&site=".$this->_buscamasUrl."' ".$this->_buscamasPublishUrl;
    }

    /**
     * Registra los estudios relacionados al anuncio
     *
     * @param App_Form_Manager $managerEstudio
     */
    public function _insertarOtrosEstudios(App_Form_Manager $managerEstudio)
    {
        $estudio       = new Application_Model_AnuncioEstudio();
        //  $filter = new App_Filter_Slug();
        $formsEstudios = $managerEstudio->getForms();
        foreach ($formsEstudios as $form) {
            $data = $form->getValues();
            if ($data['id_nivel_estudio_tipo'] != 0 && !empty($data['otra_carrera'])) {
                //        $data['otra_carrera'] = $filter->sanear_string($data['otra_carrera']);

                $estudio->insert(
                    array(
                        'id_anuncio_web' => $this->_avisoId,
                        'id_nivel_estudio' => 9,
                        'id_nivel_estudio_tipo' => $data['id_nivel_estudio_tipo'],
                        'otra_carrera' => $data['otra_carrera']
                    )
                );
            }
        }
    }

    /**
     * Actualiza los estudios de un anuncio web
     *
     * @param App_Form_Manager $managerEstudio
     * @param int $idAviso
     */
    public function _actualizarOtrosEstudios(App_Form_Manager $managerEstudio,
                                             $idAviso)
    {
        //    $filter = new App_Filter_Slug();

        $formEstudio = $managerEstudio->getForms();
        foreach ($formEstudio as $form) {
            $data    = $form->getValues();
            $idEst   = $data['id_estudio'];
            $estudio = new Application_Model_AnuncioEstudio();
            unset($data['id_estudio']);
            if ($data['id_nivel_estudio_tipo'] != 0 && !empty($data['otra_carrera'])) {
                //$data['otra_carrera'] = $filter->sanear_string($data['otra_carrera']);

                if ($idEst) {
                    $where = $estudio->getAdapter()
                            ->quoteInto('id_anuncio_web = ?', $idAviso).
                            $estudio->getAdapter()
                            ->quoteInto(' and id = ?', $idEst);
                    $estudio->update(
                        array(
                        'id_nivel_estudio_tipo' => $data['id_nivel_estudio_tipo'],
                        'otra_carrera' => $data['otra_carrera']
                        ), $where
                    );
                } else {
                    $estudio->insert(
                        array(
                            'id_anuncio_web' => $idAviso,
                            'id_nivel_estudio' => 9,
                            'id_nivel_estudio_tipo' => $data['id_nivel_estudio_tipo'],
                            'otra_carrera' => $data['otra_carrera']
                        )
                    );
                }
            }
        }
    }

    public function Desplublicar_Aviso_Solr()
    {

    }

    public function getSolarAviso()
    {
        return $this->_SolrAviso;
    }
    /*
     * Actualizar El historico del Aviso en el proceso de postulacion,
     * los contadores: Nro. de Postulantes, Nro. de Invitaciones.
     *
     * @param int $idAnuncionWeb        ID del Aviso
     * @return void
     */

    protected function actualizarAvisoDePostulacion($idAnuncioWeb = null)
    {

        if ($idAnuncioWeb) {
            try {
                $this->actualizarPostulantes($idAnuncioWeb);
                $this->actualizarInvitaciones($idAnuncioWeb);
                $this->actualizarNuevasPostulaciones($idAnuncioWeb);
            } catch (\Exception $ex) {
                throw new \Exception($ex->getMessage());
            }
        }
    }
    /*
     * Despostular.
     * Permite al postulante salir o quitarse de la postulacion.
     * Para esto Se debera:
     *
     * - Verificar que la postulacion en conjunto con el postulante existan.
     * - Cambiar de estado a la postulacion.
     * - Registramos en el Historico depostulaciones con el estado de restituir.
     * - Actualizamos los contadores de postulantes en el anuncion web
     * - Guardamos en mongo la Despostulacion para poder validar mas adelante.
     *   Ya no podra postular nuevamente.
     *
     * @param Int $idPostulacion        ID de la Postulacion.
     * @param Int $idPostulante         ID del Postulante.
     * @return Array                   Array si es el proceso es correcto
     *                                  Caso contrario retornara false.
     *
     */

    public function desPostular($idPostulacion, $id_anuncio_web, $idPostulante)
    {

        try {

            /// Cambiamos de estado a la postulacion:
            $this->_postulacion->updatePostulacion($idPostulacion,
                array(
                'activo' => 0
            ));


            /// Registramos en Mongo la desPostulacion
            /// para validar en las postulaciones:
            $despostular = new Mongo_DesPostulacion();
            $inserted    = $despostular->save(array(
                'id_anuncio_web' => $id_anuncio_web,
                'idpostulacion' => $idPostulacion,
                'id_postulante' => $idPostulante,
            ));


            if ($inserted) {

                $mHistorico = new Application_Model_HistoricoPs();
                /// Actualizamos los contadores del Anuncion Web:
                $this->actualizarAvisoDePostulacion($id_anuncio_web);

                /// Registro de Historico:
                $mHistorico->registrar(
                    $idPostulacion,
                    Application_Model_HistoricoPs::EVENTO_RESTITUIR,
                    Application_Model_HistoricoPs::EVENTO_POSTULACION
                );

                return true;
            }
        } catch (\Exception $ex) {
            throw new \Exception('Notice registro Despostular: '.$ex->getMessage());
        }

        return false;
    }

    public function extornarCompraAviso($compraId)
    {
        if (!$this->_compra->verificarUsuarioActivoPorCompra($compraId)) {
            return false;
        }

        if (!$this->_compra->tieneAviso($compraId)) {
            return false;
        }

        $ok = $this->actualizaValoresCompraExtorno($compraId);

        if ($ok) {
            $rowCompra = $this->_compra->getDetalleCompraAnuncio($compraId);
            @$this->_cache->remove('AnuncioWeb_getAvisoInfoficha_'.$rowCompra['anuncioUrl']);
            @$this->_cache->remove('AnuncioWeb_getAvisoById_'.$rowCompra['anuncioId']);
//            @$this->_cache->remove('AnuncioWeb_getAvisoRelacionadosnew_' . $rowCompra['anuncioId']);
//            @$this->_cache->remove('AnuncioWeb_getAvisosRelacionadosAuxiliar_' . $rowCompra['anuncioId']);
            @$this->_cache->remove('AnuncioWeb_getAvisoIdByUrl_'.$rowCompra['anuncioUrl']);
            @$this->_cache->remove('AnuncioWeb_getAvisoIdByCreado_'.$rowCompra['anuncioUrl']);

            return true;
        };

        return false;
    }

    /**
     *
     * Actualiza la compra en estado Pagada al estado Pendiente de pago.
     * Actualiza también el aviso web e impreso correspondientes.
     *
     * @param int $compraId
     * @throws bool
     */
    public function actualizaValoresCompraExtorno($compraId)
    {
        try {
            //extornando anuncio web
            $db              = $this->_aw->getAdapter();
            $where1          = $db->quoteInto('id_compra = ?', $compraId);
            $where2          = $db->quoteInto('online = ?', 1);
            $whereAnuncioWeb = $where1.' AND '.$where2;

            $okUpdateP = $this->_aw->update(
                array(
                'estado' => Application_Model_AnuncioWeb::ESTADO_EXTORNADO,
                'estado_publicacion' => 0,
                'online' => 0,
                'borrador' => 1,
                'proceso_activo' => 0,
                ), $whereAnuncioWeb
            );

            // redundamos para asegurar el cambio del estado del aviso.
            $okUpdateP = $this->_aw->update(
                array(
                'estado' => Application_Model_AnuncioWeb::ESTADO_EXTORNADO,
                ), $whereAnuncioWeb
            );

            //extornando anuncio impreso
            $whereAnuncioImpreso = $db->quoteInto('id_compra = ?', $compraId);
            $okUpdateP           = $this->_ai->update(
                array(
                'estado' => Application_Model_AnuncioWeb::ESTADO_EXTORNADO,
                ), $whereAnuncioImpreso
            );

            //extornando compra
            $whereCompra = $this->_compra->getAdapter()->quoteInto('id = ?',
                $compraId);
            $okUpdateP   = $this->_compra->update(
                array(
                'estado' => Application_Model_Compra::ESTADO_EXTORNADO,
                'fh_extorno' => date("Y-m-d H:i:s"),
                ), $whereCompra
            );

            return true;
        } catch (Exception $ex) {
            throw new Exception('No se pudo actualizar la compra: '.$ex->getMessage());
        }
        return false;
    }

    public function Descuentos($rowAnuncio)
    {
        $extraDescuentos = array();
        $avisos          = array();
        $tipo            = !empty($rowAnuncio['tipo']) ? $rowAnuncio['tipo'] : null;
        $aviso           = !empty($rowAnuncio['medioPublicacion']) ? $rowAnuncio['medioPublicacion']
                : null;

        $DescuentoAviso = isset($this->_config->extracargosAvisos) ? $this->_config->extracargosAvisos->toArray()
                : 0;
        if ($DescuentoAviso && $rowAnuncio['selDiscount'] > 0) {

            if ($aviso == "aptitus y talan") {

                $avisos = explode(' y ', $aviso);
                foreach ($avisos as $key => $value) {
                    if (!isset($DescuentoAviso[$tipo][$value])) {
                        return array();
                    }
                    $extraDescuentos[$key]['valor']       = $DescuentoAviso[$tipo][$value]["descuentos"]['valor'][$rowAnuncio['selDiscount']];
                    $extraDescuentos[$key]['codigo']      = "descuento-".$value;
                    $extraDescuentos[$key]['adecsys_cod'] = $DescuentoAviso[$tipo][$value]["descuentos"]['adecsys_cod'][$rowAnuncio['selDiscount']];
                    $extraDescuentos[$key]['descripcion'] = $DescuentoAviso[$tipo][$value]["descuentos"]['descripcion'][$rowAnuncio['selDiscount']];
                }
            } else {
                if (!isset($DescuentoAviso[$tipo][$aviso])) {
                    return array();
                }
                $extraDescuentos[0]['valor']       = $DescuentoAviso[$tipo][$aviso]["descuentos"]['valor'][$rowAnuncio['selDiscount']];
                $extraDescuentos[0]['codigo']      = "descuento-".$aviso;
                $extraDescuentos[0]['adecsys_cod'] = $DescuentoAviso[$tipo][$aviso]["descuentos"]['adecsys_cod'][$rowAnuncio['selDiscount']];
                $extraDescuentos[0]['descripcion'] = $DescuentoAviso[$tipo][$aviso]["descuentos"]['descripcion'][$rowAnuncio['selDiscount']];
            }
        }
        return $extraDescuentos;
    }
    /* Valida en adecsys si existe el CI del postulante al comprar un perfil destacado. */
    /* La función tb valida si ha ingresado un RUC verifica la existencia en Adecsys */

    public function validarDocumentoAdecsys($tipo, $numero)
    {

        try {

            $ws     = NEW Zend_Soap_Client($this->_config->adecsys->wsdl);
            $params = array(
                'Tipo_Documento' => $tipo,
                'Numero_Documento' => $numero
            );

            $ret = $ws->Validar_Cliente($params);

            IF (isset($ret->Validar_ClienteResult)) {
                $validaRUC = $ret->Validar_ClienteResult;
                return array(
                    'txtRuc' => $validaRUC->Num_Doc,
                    'txtName' => $validaRUC->RznSoc_Nombre,
                    'selVia' => !empty($validaRUC->Tip_Calle) ? $validaRUC->Tip_Calle
                            : 'SV',
                    'txtLocation' => !empty($validaRUC->Tip_Calle) ? $validaRUC->Nom_Calle
                            : 'SIN VIA',
                    'txtNroPuerta' => !empty($validaRUC->Num_Pta) ? $validaRUC->Num_Pta
                            : '0'
                );
            }
        } catch (Exception $ex) {
            IF (!empty($this->_config->mensaje->avisoadecsys->emails)) {
                $emailing = explode(',',
                    $this->_config->mensaje->avisoadecsys->emails);
                $helper   = NEW App_Controller_Action_Helper_Mail();
                foreach ($emailing AS $email) {
                    $helper->notificacionAdecsys(
                        array(
                            'to' => TRIM($email),
                            'mensaje' => $ex->getMessage(),
                            'trace' => $ex->getTraceAsString(),
                            'refer' => http_build_query($_REQUEST)
                        )
                    );
                }
            }
            RETURN "Error";
        }


        RETURN array();
    }

    /**
     * Anula cip de compras con estado Pendiente de pago
     *
     * @param int $idAnuncioWeb Id del anuncio web
     */
    public function _anularCipDeComprasPendientes($idAnuncioWeb)
    {
        $cips = $this->_compra->getCipComprasPendientesPago($idAnuncioWeb);

        if (count($cips) > 0) {
            $ws = new App_Controller_Action_Helper_WebServiceCip();
            foreach ($cips as $val) {
                $ws->eliminarCip($val["cip"]);
            }
        }
    }

    public function addDestaquesWeb($idTarifa, $idaviso)
    {
        $tarifaDestaque = new Application_Model_Tarifa();
        $detalleTarifa  = $tarifaDestaque->getDetalleDestaquesTarifa($idTarifa);
        foreach ($detalleTarifa as $key => $value) {
            $this->_awd->insert(
                array(
                    'id_anuncio_web' => $idaviso,
                    'codigo' => $value['codigo'],
                    'valor' => $value['valor'],
                    'descripcion' => $value['descripcion'],
                    'precio' => $value['precio'],
                )
            );
        }
    }
}
