<?php

class App_Controller_Action_Helper_PerfilDestacado extends Zend_Controller_Action_Helper_Abstract {

    private $_model;
    private $_config;
    private $_cache;
    private $_perfil;
    private $_compra;
    private $_tarifa;
    private $_compraAdecsysCode;
    private $_adecsysTarifaPerfil;
    private $_postulante;
    private $_compraAdecsysRuc;

    public function __construct() {

        $cparts = explode('_', __CLASS__);
        $this->_model = $cparts[2];
        $this->_config = Zend_Registry::get('config');
        $this->_cache = Zend_Registry::get('cache');
        $this->auth = Zend_Auth::getInstance()->getStorage()->read();
        $this->_perfil = new Application_Model_PerfilDestacado();
        $this->_compra = new Application_Model_Compra;
        $this->_tarifa = new Application_Model_Tarifa();
        $this->_compraAdecsysRuc = new Application_Model_CompraAdecsysRuc();
        $this->_compraAdecsysCode = new Application_Model_CompraAdecsysCodigo();
        $this->_adecsysTarifaPerfil = new Application_Model_AdecsysPerfilDestacado;
        $this->_postulante = new Application_Model_Postulante;
    }

    /**
     * Inserta un registro en la tabla perfio_destacado y devuelve el id del registro
     * ingresado
     * 
     * @param array $dataPost
     * @return int
     */
    public function _insertarPerfil($dataPost) {

        $tarifa = new Application_Model_Tarifa();
        $datosTarifa = $tarifa->getProductoByTarifa($dataPost['id_tarifa']);

        $usuario = $this->auth['usuario'];
        $perfilDestacado = new Application_Model_PerfilDestacado();

        $dataPP = array(
            'id_postulante' => $dataPost['id_postulante'],
            'activo' => Application_Model_PerfilDestacado::PENDIENTE_PAGO,
            'creado_por' => $usuario->id,
            'fh_creacion' => date('Y-m-d H:i:s'),
            'estado' => Application_Model_PerfilDestacado::ESTADO_REGISTRADO,
            'id_producto' => $datosTarifa['id_producto'],
            'id_tarifa' => $datosTarifa['id_tarifa']
        );

        $idPerfil = $perfilDestacado->insert($dataPP);

        return $idPerfil;
    }

    /* Valida en adecsys si existe el CI del postulante al comprar un perfil destacado. */
    /* La función tb valida si ha ingresado un RUC verifica la existencia en Adecsys */

    public function validarDocumentoAdecsys($tipo, $numero) {

        try {
            $ws = new Zend_Soap_Client($this->_config->adecsys->wsdl);

            $params = array(
                'Tipo_Documento' => $tipo,
                'Numero_Documento' => $numero
            );

            $ret = $ws->Validar_Cliente($params);

            if (isset($ret->Validar_ClienteResult)) {
                return $ret->Validar_ClienteResult;
            }
            
        } catch (Exception $ex) {
            if (!empty($this->_config->mensaje->avisoadecsys->emails)) {
                $emailing = explode(',',$this->_config->mensaje->avisoadecsys->emails);                
                $helper = new App_Controller_Action_Helper_Mail();                
                foreach($emailing as $email) {
                    $helper->notificacionAdecsys(
                        array(
                            'to' => trim($email),
                            'mensaje' => $ex->getMessage(),
                            'trace' => $ex->getTraceAsString(),
                            'refer' => http_build_query($_REQUEST)
                            )
                    );
                }
            }
        }
        
        return null;
        
    }

    public function generarCompraPerfil($rowPerfil) {

        if ($rowPerfil['enteId'] == '') {
            $rowPerfil['enteId'] = null;
        }

        if (!isset($rowPerfil['cip'])) {
            $rowPerfil['cip'] = null;
        }

        $data = array(
            'id_tarifa' => $rowPerfil['tarifaId'],
            'id_empresa' => 0,
            'id_postulante' => $rowPerfil['postulanteId'],
            'tipo_doc' => $rowPerfil['tipoDoc'],
            'medio_pago' => $rowPerfil['tipoPago'],
            'estado' => 'pendiente_pago',
            'fh_creacion' => date('Y-m-d H:i:s'),
            'cip' => $rowPerfil['cip'],
            'precio_base' => $rowPerfil['tarifaPrecio'],
            'adecsys_ente_id' => $rowPerfil['enteId'],
            'creado_por' => $rowPerfil['usuarioId'],
            'precio_total' => $rowPerfil['tarifaPrecio']
        );

        $idCompra = $this->_compra->insert($data);
        $where = $this->_perfil->getAdapter()->quoteInto('id = ?', $rowPerfil['perfilId']);

        $this->_perfil->update(
                array(
            'estado' => 'pendiente_pago',
            'id_compra' => $idCompra,
                ), $where
        );

        return $idCompra;
    }

    /**
     * Confirma compra del perfil destacado, registro en Adecsys
     * También realiza el pago si el postulante ingresa RUC
     * 
     * @param int $compraId
     * @param int $registrarEnAdecsys
     */
    public function confirmarCompraPerfil($compraId, $registrarEnAdecsys = 1) {

        if (!$this->_compra->verificarUsuarioActivoPorCompra($compraId)) {
            return;
        }

        $this->actualizaValoresCompraPerfil($compraId);
        $rowCompra = $this->_compra->getDetalleCompraPerfil($compraId);

        $fecInicio = new Zend_Date();
        $fecInicio->setLocale(Zend_Locale::ZFDEFAULT);
        $fecInicio->set($rowCompra['inicio']);
        $fecFin = new Zend_Date();
        $fecFin->setLocale(Zend_Locale::ZFDEFAULT);
        $fecFin->set($rowCompra['fin']);

        $inicio = $fecInicio->get(Zend_Date::DAY) . "/" . ucfirst($fecInicio->get("MMMM")) . "/" .
                $fecInicio->get(Zend_Date::YEAR);

        $fin = $fecFin->get(Zend_Date::DAY) . "/" . ucfirst($fecFin->get("MMMM")) . "/" .
                $fecFin->get(Zend_Date::YEAR);

        $mailer = new App_Controller_Action_Helper_Mail();

        if ($registrarEnAdecsys == 1) {

            if (!$this->registrarPerfilEnAdecsys($compraId)) {
                return;
            }

            $dataMail = array(
                'to' => $rowCompra['emailContacto'],
                'usuario' => $rowCompra['emailContacto'],
                'nombres' => $rowCompra['nombreContacto'],
                'inicio' => $inicio,
                'fin' => $fin,
            );

            $mailer->confirmarCompraPerfil($dataMail);
        } elseif ($registrarEnAdecsys == 0) {

            $dataMail = array(
                'to' => $rowCompra['emailContacto'],
                'usuario' => $rowCompra['emailContacto'],
                'nombres' => $rowCompra['nombreContacto'],
                'inicio' => $inicio,
                'fin' => $fin,
            );

            $mailer->confirmarCompraPerfil($dataMail);
        }
    }

    public function actualizaValoresCompraPerfil($compraId) {

        $rowCompra = $this->_compra->getDetalleCompraPerfil($compraId);

        $idTarifa = $rowCompra['idTarifa'];
        $mesesBeneficio = $this->_tarifa->obtenerDiasBeneficioPerfil($idTarifa);

        $fecFin = new DateTime(date("Y-m-d"));

        $wherePerfil[] = $this->_perfil->getAdapter()->quoteInto('id_compra = ?', $compraId);
        $wherePerfil[] = $this->_perfil->getAdapter()->quoteInto('activo = ?', Application_Model_PerfilDestacado::PENDIENTE_PAGO);

        //Validar si tiene otro registro de perfil destacado activo 
        $dataValidaPerfil = $this->_perfil->validaPerfilDestacado($rowCompra['postulanteId']);
        $activo = Application_Model_PerfilDestacado::ACTIVO;
        $fecInicio = new DateTime(date("Y-m-d"));
        $fecFin->add(new DateInterval('P' . $mesesBeneficio . 'M'));

        if (!is_null($dataValidaPerfil)) {

            $activo = Application_Model_PerfilDestacado::EN_ESPERA;
            $fecInicio = new DateTime($dataValidaPerfil['fh_fin']);
            $fecInicio->add(new DateInterval('P' . 1 . 'D'));

            $fecFin = new DateTime($dataValidaPerfil['fh_fin']);
            $fecFin->add(new DateInterval('P' . $mesesBeneficio . 'M'));
        }


        $this->_perfil->update(
                array(
            'estado' => Application_Model_PerfilDestacado::ESTADO_PAGADO,
            'fh_inicio' => $fecInicio->format('Y-m-d H:i:s'),
            'fh_fin' => $fecFin->format('Y-m-d H:i:s'),
            'activo' => $activo
                ), $wherePerfil
        );

        $where = $this->_compra->getAdapter()->quoteInto('id = ?', $compraId);
        $this->_compra->update(
                array(
            'estado' => Application_Model_Compra::ESTADO_PAGADO,
            'fh_confirmacion' => date('Y-m-d H:i:s'),
                ), $where
        );
    }

    //Genera el cod de adecsys y registra el perfil
    public function registrarPerfilEnAdecsys($compraId) {

        $options = array();
        if (isset($this->_config->adecsys->proxy->enabled) && $this->_config->adecsys->proxy->enabled) {
            $options = $this->_config->adecsys->proxy->param->toArray();
        }

        try {
            $ws = new Adecsys_Wrapper($this->_config->adecsys->wsdl, $options);
            $cliente = $ws->getSoapClient();
            $db = Zend_Db_Table::getDefaultAdapter();
            $aptitus = new Aptitus_Adecsys($ws, $db);
        } catch (Exception $ex) {
            if (!empty($this->_config->mensaje->avisoadecsys->emails)) {
                $emailing = explode(',',$this->_config->mensaje->avisoadecsys->emails);                
                $helper = new App_Controller_Action_Helper_Mail();                
                foreach($emailing as $email) {
                    $helper->notificacionAdecsys(
                        array(
                            'to' => trim($email),
                            'mensaje' => $ex->getMessage(),
                            'trace' => $ex->getTraceAsString(),
                            'refer' => http_build_query($_REQUEST)
                            )
                    );
                }
            }
        }
        

        $rowPerfil = $this->_compra->getDetalleCompraPerfil($compraId);
        $medioPago = $rowPerfil['medioPago'];
        $dataCAR = $this->_compraAdecsysRuc->getRegistroByCompra($compraId);

        $sesionRUC = new Zend_Session_Namespace('pago_ruc');
        $servicioRegistrarEnte = new App_Service_Adecsys_RegisterEntity($ws);

        if (($rowPerfil['enteId'] == null && is_null($dataCAR)) ||
                ($rowPerfil['enteId'] == null && !isset($sesionRUC->ente_ruc))) {

            $nombreTrama = 'compra_perfil_' . $compraId;
            $servicioRegistrarEnte->setNameTrama($nombreTrama);

            if (!$servicioRegistrarEnte->registerEntePostulante($rowPerfil['postulanteId'], null,$compraId)) {

                $adecsysContingencyPostulante = new Application_Model_AdecsysContingenciaPerfil;
                $adecsysContingencyPostulante->registrar($rowPerfil['perfilId']);
                return false;
            }

            $rowPerfil = $this->_compra->getDetalleCompraPerfil($compraId);
        }

        //Verifica ente si PE
        if (!is_null($dataCAR)) {
            //Si el ente RUC ya existe en Adecsys
            $rowPerfil['tipoDocumento'] = 'RUC';
            $rowPerfil['numDocumento'] = $dataCAR['ruc'];
            //$rowPerfil['codigoEnte'] = $sesionRUC->ente_ruc;
            $rowPerfil['razonSocial'] = $dataCAR['razon_social'];

            $tipDoc = 'RUC';
            $numDocumento = $dataCAR['ruc'];
            $razonSocial = $dataCAR['razon_social'];
            //Registrar el ente en Adecsys
            $rowPerfil['codigoEnte'] = $servicioRegistrarEnte->registerEntePostulanteNuevo($rowPerfil['postulanteId'], $compraId);
            $rowPerfil = $this->_compra->getDetalleCompraPerfil($compraId);
            $rowPerfil['tipoDocumento'] = $tipDoc;
            $rowPerfil['numDocumento'] = $numDocumento;
            $rowPerfil['razonSocial'] = $razonSocial;
            $rowPerfil['apePatContacto'] = $razonSocial;
            $rowPerfil['apeMatContacto'] = $razonSocial;

        }
        
        //Si es diferente a PE obtiene sesión
        if (isset($sesionRUC->ente_ruc) && $medioPago != Application_Model_Compra::FORMA_PAGO_PAGO_EFECTIVO) {
             //Si el ente RUC ya existe en Adecsys
            $rowPerfil['tipoDocumento'] = $sesionRUC->Tip_Doc;
            $rowPerfil['numDocumento'] = $sesionRUC->Num_Doc;
            $rowPerfil['codigoEnte'] = $sesionRUC->ente_ruc;
            $rowPerfil['razonSocial'] = $sesionRUC->RznSoc_Nombre;
            if ($sesionRUC->ente_ruc > 0) {

                unset($sesionRUC->ente_ruc);
                unset($sesionRUC->Tip_Doc);
                unset($sesionRUC->Num_Doc);
                unset($sesionRUC->RznSoc_Nombre);
                unset($sesionRUC->RznCom);
                unset($sesionRUC->Telf);
                unset($sesionRUC->Tip_Calle);
                unset($sesionRUC->Nom_Calle);
           } else if ($sesionRUC->ente_ruc == 0) { //Se registrará el ente en Adecsys
                $tipDoc = $sesionRUC->Tip_Doc;
                $numDocumento = $sesionRUC->Num_Doc;
                $razonSocial = $sesionRUC->RznSoc_Nombre;
                //Registrar el ente en Adecsys
                $rowPerfil['codigoEnte'] = $servicioRegistrarEnte->registerEntePostulanteNuevo($rowPerfil['postulanteId'], $compraId);
                $rowPerfil = $this->_compra->getDetalleCompraPerfil($compraId);
                $rowPerfil['tipoDocumento'] = $tipDoc;
                $rowPerfil['numDocumento'] = $numDocumento;
                $rowPerfil['razonSocial'] = $razonSocial;
                $rowPerfil['apePatContacto'] = $razonSocial;
                $rowPerfil['apeMatContacto'] = $razonSocial;
                
            }
        }

        $this->registroPerfil($rowPerfil);

        return true;
    }

    /**
     * Graba el registro del perfil destacado envía info a adecsys y actualiza reg en Solr
     * @param array $rowPerfil 
     */
    public function registroPerfil($rowPerfil) {

        $log = Zend_Registry::get('log');
        $config = Zend_Registry::get("config");
        $log->info(var_export($rowPerfil, TRUE));

        $ws = new Zend_Soap_Client($this->_config->adecsys->wsdl);

        $params = array();
        $params["Registrar_Aviso_Pref"] = array();

        $tipo = Application_Model_CompraAdecsysCodigo::MEDIO_PUB_APTITUS;
        $texto = 'Perfil Destacado';

        $dataExt = array();
        $dataExt["Ape_Mat_Contacto"] = trim($rowPerfil["apeMatContacto"]) == "" ? "-" : $rowPerfil["apeMatContacto"];
        $dataExt["Ape_Pat_Contacto"] = trim($rowPerfil["apePatContacto"]) == "" ? "-" : $rowPerfil["apePatContacto"];

        $dataExt["Tit_Aviso"] = '.';
        $dataExt["Cod_Cliente"] = $rowPerfil["codigoEnte"];
        $dataExt["Email_Contacto"] = $rowPerfil["emailContacto"];
        $dataExt["Fec_Registro"] = date("Y-m-d");
        $dataExt["Nom_Contacto"] = trim($rowPerfil["nombreContacto"]) == "" ? "-" : $rowPerfil["nombreContacto"];

        $dataExt["Num_Doc"] = $rowPerfil["numDocumento"];
        $dataExt["RznSoc_Nombre"] = $rowPerfil["razonSocial"];
        $dataExt["Telf_Contacto"] = trim($rowPerfil["telefonoContacto"]) == "" ? "-" : $rowPerfil["telefonoContacto"];
        $dataExt["Tip_Doc"] = $rowPerfil["tipoDocumento"];
        $dataExt["Contenido_Aviso"] = $texto;

        $dataExt["Cod_Contrato"] = $rowPerfil["nroContrato"];

        $dataExt["Puestos_Aviso"] = array();
        $dataExt["Puestos_Aviso"]["Puesto_AvisoBE"] = array();

        $aviso = array();
        $aviso["Puesto_Id"] = $config->adecsysParametrosDefault->preferenciales->puesto_id;
        $aviso["Esp_Id"] = $config->adecsysParametrosDefault->preferenciales->esp_id;
        $aviso["Ind_Id"] = $config->adecsysParametrosDefault->preferenciales->ind_id;
        $aviso["Cod_Dpto"] = $config->adecsysParametrosDefault->preferenciales->cod_dpto;
        $aviso["Des_Aviso"] = $texto;

        $dataExt["Puestos_Aviso"]["Puesto_AvisoBE"][] = $aviso;
        $dataExt["Prim_Fec_Pub"] = date('Y-m-d');
        $dataExt["Fechas_Pub_Aviso"][] = date('Y-m-d');

        $dataExt["Fechas_Pub"] = date('d/m/Y');

        $dataExt["Cant_Fechas_Pub"] = 1;
        $dataExt["Des_Adicional"] = $rowPerfil["nombre_comercial"];

        $nroAdecsys = '';

        $correlativoAptitus = $this->_compraAdecsysCode->insert(
                array(
                    'id_compra' => $rowPerfil['compraId'],
                    'medio_publicacion' => $tipo
                )
        );

        $dataExt["Cod_Aviso"] = $correlativoAptitus;
        $rowPerfil["correlativoAptitus"] = $correlativoAptitus;
        $dataExt['Correlativo'] = 1;
        $dataExt['Aplicado'] = 'false';

        $dataAdecsysTarifaPerfil = $this->_adecsysTarifaPerfil->obtenerTarifaAdecsysPerfil($rowPerfil['idTarifa']);

        $dataConfig = array_merge($dataAdecsysTarifaPerfil, $dataExt);

        $response = $this->callWSRegistrarPerfil($ws, $dataConfig, $rowPerfil);

        if (is_null($response)) {
            return false;
        }
        
        if (Zend_Validate::is($response->Registrar_Aviso_PrefResult, 'Int')) {
            $nroAdecsys = $response->Registrar_Aviso_PrefResult;

            //Actualizar la tabla compra_adecsys_codigo con el codAdecsys generado
            $where = $this->_compraAdecsysCode->getAdapter()->quoteInto('id = ?', $correlativoAptitus);
            $this->_compraAdecsysCode->update(
                    array(
                'medio_publicacion' => $tipo,
                'adecsys_code' => $nroAdecsys
                    ), $where
            );

            //Actualizar la tabla perfil_destacado con el codAdecsys generado
            $where = $this->_perfil->getAdapter()->quoteInto('id_compra = ?', $rowPerfil['compraId']);
            $this->_perfil->update(
                    array(
                'adecsys_code' => $nroAdecsys
                    ), $where
            );

            //Actualizar campo en la tabla postulante
            $where = $this->_postulante->getAdapter()->quoteInto('id = ?', $rowPerfil['postulanteId']);
            $this->_postulante->update(
                    array(
                'destacado' => Application_Model_Postulante::DESTACADO,
                'prefs_confidencialidad' => Application_Model_Postulante::CONFIDENCIALIDAD,
                'ultima_actualizacion' => date('Y-m-d H:i:s')
                    ), $where
            );

            // insertando al el postulante al Solr
            $Solr = new App_Controller_Action_Helper_Solr();
            $Solr->addSolr($rowPerfil['postulanteId']);

            //Actualiza la sesión
            $storage = Zend_Auth::getInstance()->getStorage()->read();
            $storage['postulante']['destacado'] = Application_Model_PerfilDestacado::ACTIVO;
            Zend_Auth::getInstance()->getStorage()->write($storage);


            //Enviamos la info del postulante a Solr para que considere el destaque en las búsquedas
            $sc = new Solarium\Client($this->_config->solr);
            $moPostulante = new Solr_SolrAbstract($sc, 'postulante');
            $moPostulante->addPostulante($rowPerfil['postulanteId']);

            $rowPerfil["nroAdecsysAptitus"] = $nroAdecsys;
        }
    }

    private function callWSRegistrarPerfil($ws, $params, $rowPerfil) {

        $response = null;
        try {
            $response = $ws->Registrar_Aviso_Pref(array('oRegistroAvisoPref' => $params));
            file_put_contents(
                    APPLICATION_PATH . '/../logs/Compra_Perfil_' .
                    $rowPerfil['compraId'] . '_RegistrarPerfil_envio.xml', $ws->getLastRequest(), FILE_APPEND
            );
            file_put_contents(
                    APPLICATION_PATH . '/../logs/Compra_Perfil_' .
                    $rowPerfil['compraId'] . '_RegistrarPerfil_rpta.xml', $ws->getLastResponse(), FILE_APPEND
            );
        } catch (Exception $ex) {
            file_put_contents(
                    APPLICATION_PATH . '/../logs/Compra_Perfil_' .
                    $rowPerfil['compraId'] . '_RegistrarPerfil_error_envio.xml', $ws->getLastRequest(), FILE_APPEND
            );
            file_put_contents(
                    APPLICATION_PATH . '/../logs/Compra_Perfil_' .
                    $rowPerfil['compraId'] . '_RegistrarPerfil_error_rpta.xml', $ws->getLastResponse(), FILE_APPEND
            );
        }

        return $response;
    }

    /**
     * Verifica si el idCompra le pertence al postulante
     * @param int $idCompra
     * @param int $idPostulante
     * @return boolean
     */
    public function perteneceCompraPostulante($idCompra, $idPostulante) {
        $cant = $this->_compra->perteneceCompraPostulante($idCompra, $idPostulante);
        if (is_numeric($idCompra) && $cant > 0) {
            return true;
        }
        return false;
    }

}
