<?php

class Empresa_ComprarAvisoController extends App_Controller_Action_Empresa
{
    //Buscamas
    private $_buscamasConsumerKey;
    private $_buscamasPublishUrl;
    private $_buscamasUrl;
    private $_aw;
    private $_compra;
    private $_conf;

    CONST PROCESSOR_ID = 80007765;

    public function init()
    {
        parent::init();
        $this->_usuario = new Application_Model_Usuario();

        if ($this->_usuario->hasvailBlokeo($this->auth['usuario']->id)) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::forgetMe();
            $this->getMessenger()
                ->error(Application_Model_Usuario::MSG_USUARIO_BLOQUEDO);
            $this->_redirect('/empresa');
        }
        $this->_conf           = Zend_Registry::get('config');
        $this->_compra         = new Application_Model_Compra();
        $this->_anuncioWeb     = new Application_Model_AnuncioWeb;
        $this->_aw             = new Application_Model_AnuncioWeb;
        $this->_anuncioImpreso = new Application_Model_AnuncioImpreso;
        $this->_compraAdecsys  = new Application_Model_CompraAdecsysCodigo;

        $this->_config = Zend_Registry::get('config');
    }

    public function indexAction()
    {
        $this->_redirect('/empresa');
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_PUBLICA_AVISO;
    }

    public function pagoEfectivoAction()
    {
        $misprocesosJS = '/js/empresa/empresa.misprocesos.js';
        Zend_Layout::getMvcInstance()
            ->assign('bodyAttr',
                array(
                'id' => 'perfilReg',
                'class' => 'noMenu'));
        $this->view->headScript()
            ->appendFile($this->view->S($misprocesosJS));
        $sess          = $this->getSession();

        if (isset($sess->rowAnuncio['cip']) && !empty($sess->rowAnuncio['cip'])) {
            $sess->rowAnuncio['cip'] = ltrim($sess->rowAnuncio['cip'], "0");
            $this->view->anuncioWeb  = $sess->rowAnuncio;
            $this->view->igv         = $this->_config->app->igv;
            $medioPublicacion        = $sess->rowAnuncio['medioPublicacion'];
            if ($medioPublicacion == 'aptitus y talan') {
                $medioPublicacion = 'combo';
            }
            $this->view->fhCierre = $this->_helper
                ->Aviso->getFechaCierreImpresoByPaquete($medioPublicacion);
        } else {
            $this->_redirect('/empresa');
        }
    }

    public function okPagoEfectivoAction()
    {
        Zend_Layout::getMvcInstance()
            ->assign('bodyAttr',
                array(
                'id' => 'perfilReg',
                'class' => 'noMenu'));
        $cadenaEncriptada = $this->_getParam('datosEnc');

        try {
            $datos                   = array();
            $datos['encripta']       = $cadenaEncriptada;
            $datos['fecha_creacion'] = date('Y-m-d H:i:s');
            $mongoPE                 = new Mongo_PeContingencia();
            $id                      = $mongoPE->save($datos);
        } catch (Exception $e) {

        }
        $helper       = $this->_helper->getHelper('WebServiceEncriptacion');
        $cadenaDesEnc = $helper->desencriptaCadena($cadenaEncriptada);

        if (empty($cadenaDesEnc) && !isset($this->getConfig()->app->debug)) {
            throw new Zend_Exception('El valor a Desencriptar es Nulo', 500);
        }

        if (!empty($cadenaDesEnc)) {
            $arrayDatos = explode("|", $cadenaDesEnc);
            $compraId   = substr($arrayDatos[1], 16);
        }

        if (isset($this->getConfig()->app->debug)) {
            $compraId = $cadenaEncriptada;
        }

        if (!$this->_compra->verificarPagado($compraId)) {
            $this->_helper->aviso->confirmarCompraAviso($compraId);

            $sessionAdmin = new Zend_Session_Namespace('admin');
            try {
                $datos['fecha_edicion'] = date('Y-m-d H:i:s');
                $datos['id_compra']     = $compraId;
                $datos['_id']           = $id;
                $mongoPE                = new Mongo_PeContingencia();
                $mongoPE->save($datos);
                $mCompra                = new Mongo_Compra();
                $datos                  = array(
                    'empresa' => $this->auth,
                    'admin' => $sessionAdmin->auth,
                    'aviso' => $this->_anuncioWeb->getByIdCompra($compraId),
                    'impreso' => $this->_anuncioImpreso->getByIdCompra($compraId),
                    'compra_adecsys' => $this->_compraAdecsys->getByIdCompra($compraId),
                    'compra' => $this->_compra->getById($compraId)
                );
                $mCompra->save($datos);
            } catch (Exception $e) {

            }
        }
    }

    public function pagoSatisfactorioAction()
    {
        $id          = $this->_getParam('compra', false);
        $helperAviso = $this->_helper->getHelper('Aviso');
        $idEmpresa   = $this->auth['empresa']['id'];
        if (!$helperAviso->perteneceCompraAEmpresa($id, $idEmpresa)) {
            throw new App_Exception_Permisos();
        }
        Zend_Layout::getMvcInstance()->assign('bodyAttr',
            array(
            'id' => 'perfilReg',
            'class' => 'noMenu'));
        $compraId   = $this->_getParam('compra');
        $dataCompra = $this->_compra->getDetalleCompraAnuncio($compraId);

        //var_dump($dataCompra);die();

        $idAnuncioWeb = $dataCompra['idAnuncioWeb'];
        $idTarifa = $dataCompra['idTarifa'];
        $medioPago = $dataCompra['medioPago'];

        $destaque = $dataCompra['beneficios']['destaque']['valor'];
        switch ($destaque) {
            case 1:
                $destaque = "oro";
                break;
            case 2:
                $destaque = "Plata";
                break;

            case 6:
                $destaque = "Simple";
                break;

            default:
                $destaque = "";
                break;
        }

        $this->view->tipoAviso = $destaque;

        if($dataCompra['anunciosWeb'][$idAnuncioWeb]['medio_pago'] == Application_Model_AnuncioWeb::MEDIO_PAGO_BONIFICADO){
            $this->view->promo = true;

            if($medioPago == 'pf'){
                $msg = '<p>Felicidades %s, tu puedes obtener un Destaque %s por la publicación de tu impreso de $%s del aviso Web simple <span class="name-aviso">%s</span>.</p>
                <p>Sólo deberás de realizar el pago con el código %s en los canales de Pago que tiene PuntoFácil</p>';

                $promoMsg = sprintf($msg, $dataCompra['nombreContacto'],
                    $destaque, $dataCompra['montoImpreso'], $dataCompra['anuncioPuesto'], $dataCompra['anunciosWeb'][$idAnuncioWeb]['id_compra']);

                $this->view->promoMsg = $promoMsg;

            }elseif($medioPago == 'pv'){
                $msg = '<p>Felicidades %s, tu puedes obtener un Destaque %s por la publicación de tu impreso de $%s del aviso Web simple <span class="name-aviso">%s</span>.</p>
                <p>Sólo deberás de realizar el pago con el código %s en los canales de Pago de La Prensa</p>';

                $promoMsg = sprintf($msg, $dataCompra['nombreContacto'],
                    $destaque, $dataCompra['montoImpreso'], $dataCompra['anuncioPuesto'], $dataCompra['anunciosWeb'][$idAnuncioWeb]['id_compra']);

                $this->view->promoMsg = $promoMsg;

            }else{
                $msg = '<p>Felicidades %s, has obtenido un Destaque %s por la publicación de tu impreso de $%s del aviso Web simple <span class="name-aviso">%s</span>.</p>';

                $promoMsg = sprintf($msg, $dataCompra['nombreContacto'],
                    $destaque, $dataCompra['montoImpreso'], $dataCompra['anuncioPuesto']);
                $this->view->promoMsg = $promoMsg;
            }
        }

//        $mailer = new App_Controller_Action_Helper_Mail();
//
//        $dataMail = array(
//            'to' => $dataCompra['emailContacto'],
//            'usuario' => $dataCompra['emailContacto'],
//            'nombre' => $dataCompra['nombreContacto'] . " " . $dataCompra['apePatContacto'],
//            'anuncioPuesto' => $dataCompra['anuncioPuesto'],
//            'razonSocial' => $dataCompra['nombre_comercial'],
//            'montoTotal' => $dataCompra['montoTotal'],
//            'medioPago' => $dataCompra['medioPago'],
//            'anuncioClase' => $dataCompra['anuncioClase'],
//            'productoNombre' => $dataCompra['productoNombre'],
//            'anuncioUrl' => $dataCompra['anuncioUrl'],
//            'fechaPago' => $dataCompra['fechaPago'],
//            'anuncioFechaVencimiento' => $dataCompra['anuncioFechaVencimiento'],
//            'fechaPublicConfirmada' => $dataCompra['fechaPublicConfirmada'],
//            'medioPublicacion' => $dataCompra['medioPublicacion'],
//            'anuncioSlug' => $dataCompra['anuncioSlug'],
//            'anuncioFechaVencimientoProceso' => $dataCompra['anuncioFechaVencimientoProceso'],
//            'codigo_adecsys_compra' => '',
//            'tipo' => $dataCompra['tipoAnuncio']
//        );
//
//
//        $mailSession = new Zend_Session_Namespace();
//        if (!isset($mailSession->send)) {
//            $mailer->confirmarCompra($dataMail);
//            $mailSession->send = true;
//        }
        //Solo para avisos web destacado
//        if ($dataCompra['tipoAnuncio'] == Application_Model_Compra::TIPO_DESTACADO){
//            //Es ampliado extendido
//            if ($dataCompra['anuncioId'] != $dataCompra['extiendeA']){
//                $idAvisoNew = $dataCompra['anuncioId'];
//                $extendido = $dataCompra['extiendeA'];
//                //$dataAW = $this->_anuncioWeb->avisosExtendidosDestacados($idAvisoNew,$extendido);
//                //foreach ($dataAW as $infoAviso) {
//                    //Actualizar avisos
//                    $whereAw = $this->_anuncioWeb->getAdapter()->quoteInto('id = ?', $extendido);
//                    $update = $this->_anuncioWeb->update(
//                            array('online' => 0), $whereAw);
//
//                    $resultado = exec("curl -X POST -d 'api_key=".$this->_buscamasConsumerKey."&nid=".$extendido."&site=".$this->_buscamasUrl."' ".$this->_buscamasPublishUrl);
//                //}
//
//            }
//        }
        //Index aviso a Buscamas


        $contratoSeleccionado = null;
        $tipoContrato         = null;
        $saldoFinal           = null;
        if (isset($this->auth["anuncioImpreso"])) {
            if ($this->auth["anuncioImpreso"]["id"] == $dataCompra["anuncioImpresoId"]) {
                if (isset($this->auth["anuncioImpreso"]["contratoSeleccionado"])) {
                    $contratoSeleccionado = $this->auth["anuncioImpreso"]["contratoSeleccionado"];
                    if ($contratoSeleccionado["ModalidadContrato"] == Application_Model_Compra::TIPO_CONTRATO_MEMBRESIA)
                            $tipoContrato         = "Membresía";
                    if ($contratoSeleccionado["ModalidadContrato"] ==
                        Application_Model_Compra::TIPO_CONTRATO_MULTIMEDIOS ||
                        $contratoSeleccionado["ModalidadContrato"] ==
                        Application_Model_Compra::TIPO_CONTRATO_UNIPRODUCTO) {
                        $tipoContrato = "Contrato";
                    }
                    $saldoFinal = $contratoSeleccionado["SaldoFinal"];
                }
            }
        }


        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.aviso.paso4.js')
        );

        $this->view->compra       = $dataCompra;
        $this->view->tipoContrato = $tipoContrato;
        $this->view->saldoFinal   = $saldoFinal;
        $this->view->moneda       = $this->_config->app->moneda;
        //colocardo temporalmente para pruebas
    }

    public function okAction()
    {

        $cadenaEncriptada = $this->_getParam('egp_data');
        try {
            $datos                   = array();
            $datos['encripta']       = $cadenaEncriptada;
            $datos['fecha_creacion'] = date('Y-m-d H:i:s');
            $mongoPE                 = new Mongo_PeContingencia();
            $id                      = $mongoPE->save($datos);
        } catch (Exception $e) {

        }
        $helper       = $this->_helper->getHelper('WebServiceEncriptacion');
        $cadenaDesEnc = $helper->desencriptaCadena($cadenaEncriptada);
        $arrayDatos   = explode("|", $cadenaDesEnc);
        $compraId     = substr($arrayDatos[1], 10);
        $token        = substr($arrayDatos[0], 12);

        if (!$this->_compra->verificarPagado($compraId)) {
            $this->_helper->aviso->confirmarCompraAviso($compraId);
            $where     = $this->_compra->getAdapter()->quoteInto('id = ?',
                $compraId);
            $okUpdateP = $this->_compra->update(array(
                'token' => $token), $where);

            $sessionAdmin = new Zend_Session_Namespace('admin');
            try {
                $datos['fecha_edicion'] = date('Y-m-d H:i:s');
                $datos['id_compra']     = $compraId;
                $datos['_id']           = $id;
                $mongoPE                = new Mongo_PeContingencia();
                $mongoPE->save($datos);
                $mCompra                = new Mongo_Compra();
                $datos                  = array(
                    'empresa' => $this->auth,
                    'admin' => $sessionAdmin->auth,
                    'aviso' => $this->_anuncioWeb->getByIdCompra($compraId),
                    'impreso' => $this->_anuncioImpreso->getByIdCompra($compraId),
                    'compra_adecsys' => $this->_compraAdecsys->getByIdCompra($compraId),
                    'compra' => $this->_compra->getById($compraId)
                );
                $mCompra->save($datos);
            } catch (Exception $e) {

            }
        }

        $this->_redirect('/empresa/comprar-aviso/pago-satisfactorio/compra/'.$compraId);
    }

    /**
     * @todo refactorizar Metodo se llama demasiadas instancias
     */
    public function pagarWebAction()
    {
        try {
            $allParams = $this->_getAllParams();
            //  var_dump($allParams);
            $_ccexp    = $allParams['mes'].$allParams['anio'];
            $sessionAdmin   = new Zend_Session_Namespace('admin');
            $correo='';
            if (isset($sessionAdmin->auth["usuario"]->email )) {
                $correo=$sessionAdmin->auth["usuario"]->email ;
            }
            $modelAviso  = new Application_Model_AnuncioWeb();
            $modelTarifa = new Application_Model_Tarifa();

            $producto                           = $modelTarifa->getProductoByTarifa($allParams['idtarifa']);
            $data                               = array(
                'id_tarifa' => $producto['id_tarifa'],
                'id_producto' => $producto['id_producto']
            );
            $whereU                             = array(
                'id =?' => $allParams['aviso']);
            $modelAviso->update($data, $whereU);
            $rowAnuncio                         = $modelAviso->getDatosWebGenerarCompra($allParams['aviso']);
            $rowAnuncio['totalPrecio']          = number_format((float) $allParams['monto_web'],
                2);
            $rowAnuncio['tipoDoc']              = 'factura';
            $rowAnuncio['tipoPago']             = $allParams['radioTipoPago'];
            $rowAnuncio['precio_total_impreso'] = number_format((float) $allParams['monto_impreso'],
                2);
            $usuario                            = $this->auth['usuario'];
            $rowAnuncio['usuarioId']            = $usuario->id;
            $rowAnuncio['CorreoAdmin']=$correo;
            $compraId                           = $this->_helper->aviso->generarCompraAnuncio($rowAnuncio);
            if ($allParams['radioTipoPago'] == "puntofacil") {

                $this->_redirect('/empresa/comprar-aviso/pago-satisfactorio/compra/'.$compraId);
            } elseif ($allParams['radioTipoPago'] == "credomatic") {
                //credomatic
                $key_id         = $this->getConfig()->credomatic->key_id;
                $key            = $this->getConfig()->credomatic->key;
                $userCredomatic = $this->getConfig()->credomatic->username;

                //               app.pagar.test
                $amount = (float) $allParams['monto_web'] + (float) $allParams['monto_impreso'];
                $tp     = $this->view->Util()->cTotalConIv($amount);
                $amount = $tp['Total'];

                if (isset($this->_conf->app->pagar->test)) {
                    $amount = $this->_conf->app->pagar->test;
                }
                $orderid = $compraId;
                $time    = time();

                $hash = MD5($orderid."|".$amount."|".$time."|".$key);

                $client = new Zend_Http_Client();
                $client->setUri($this->getConfig()->credomatic->url);

                $log = new Mongo_PaymentLog();

                $parameters = array(
                    'username' => $userCredomatic,
                    'type' => 'auth',
                    'key_id' => $key_id,
                    'hash' => $hash,
                    'time' => $time,
                    'amount' => $amount,
                    'orderid' => $orderid,
                    'processor_id' =>(string)self::PROCESSOR_ID,
                    'ccnumber' => $allParams['ccnumber'],
                    'ccexp' => $_ccexp,
                );
                //var_dump($parameters);exit;
                $log->save($parameters);

                $client->setMethod(Zend_Http_Client::POST);
                $client->setParameterPost($parameters);

                $response   = $client->request();
                //$data = parse_url($response->getBody(), PHP_URL_QUERY);
                $data       = parse_str($response->getBody(), $output);
                //var_dump($output);exit;
                $log->save($output);
                $targeta    = $rest       = substr($parameters['ccnumber'], -4);
                $dataCompra = array(
                    'cip' => $targeta// $output['transactionid'],
                );
                if ($output['response_code'] == 100 && $this->_validarHash($output)) {
                    $venta = $this->_credomaticCapturaVenta($output,
                        $allParams['ccnumber']);

                    if ($venta['response_code'] == 100) {
                        $where     = $this->_compra->getAdapter()->quoteInto(
                            'id = ?', (int) $compraId);
                        $this->_compra->update($dataCompra, $where);
                        $this->_helper->aviso->confirmarCompraAviso($compraId, 0);
                        $this->_helper->WSNicaraguaAviso->envioAvisoWeb($compraId);
                        $rowCompra = $this->_compra->getDetalleCompraAnuncio($compraId);
                        if (!empty($rowCompra['adecsys_code'])) {
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
                            $this->_helper->mail->confirmarCompra($dataMail);
                        }



                        $this->_redirect('/empresa/comprar-aviso/pago-satisfactorio/compra/'.$compraId);
                    }
                    //$this->_credomaticAnular($output, $allParams['ccnumber']);
                } else {
                    $data   = array(
                        'id_compra' => NULL,
                    );
                    $whereU = array(
                        'id =?' => $allParams['aviso']);
                    $modelAviso->update($data, $whereU);

                    $this->getMessenger()->error("No se ha podido realizar el pago con su tarjeta. Por favor, verifique los datos de la tarjeta e intente nuevamente. ".$output['responsetext']);

                    $this->_redirect('/empresa/publica-aviso/paso2/token/'.$allParams['token']);
                }
            }

            //$this->_helper->aviso->confirmarCompraAviso($compraId, 0);
            $this->_redirect('/empresa/comprar-aviso/pago-satisfactorio/compra/'.$compraId);
        } catch (Exception $ex) {
            // var_dump()
            $this->getMessenger()->error('Por favor vuelva a intentarlo.'.$ex->getMessage());
            $this->_redirect('/empresa/publica-aviso/paso2/token/'.$allParams['token']);
        }
    }

    public function pagarTestAction()
    {
        $allParams = $this->_getAllParams();
        $idcompra  = $allParams['idcompra'];
        $data      = array(
            'tipo_doc' => 'RUT',
            'doc_numero' => 'J4455555553343'
        );
        echo $this->_helper->WSNicaraguaAviso->getCodeContacto($data);

        exit;
        exit;
    }

    private function _credomaticCapturaVenta($data, $cc)
    {
        $key_id         = $this->getConfig()->credomatic->key_id;
        $key            = $this->getConfig()->credomatic->key;
        $userCredomatic = $this->getConfig()->credomatic->username;

        $amount = $data['amount'];
        $time   = time();

        $hash = MD5(""."|".$amount."|".$time."|".$key);

        $client = new Zend_Http_Client();
        $client->setUri($this->getConfig()->credomatic->url);

        $parameters = array(
            'username' => $userCredomatic,
            'type' => 'sale',
            'key_id' => $key_id,
            'hash' => $hash,
            'time' => $time,
            'amount' => $amount,
            //'orderid' => $data['orderid'],
            'transactionid' => $data['transactionid'],
            'processor_id' => self::PROCESSOR_ID,
            'ccnumber' => $cc,
            'amount' => $amount,
        );

        $client->setMethod(Zend_Http_Client::POST);
        $client->setParameterPost($parameters);

        $parameters['orderid'] = $data['orderid'];
        $log                   = new Mongo_PaymentLog();
        $log->save($parameters);
        $response              = $client->request();
        $_data                 = parse_str($response->getBody(), $output);
        $output['orderid']     = $data['orderid'];
        $log->save($output);
        return $output;
    }

    private function _credomaticAnular($data, $cc)
    {
        $key_id         = $this->getConfig()->credomatic->key_id;
        $key            = $this->getConfig()->credomatic->key;
        $userCredomatic = $this->getConfig()->credomatic->username;

        $amount = $data['amount'];
        $time   = time();

        $hash = MD5(""."|".$amount."|".$time."|".$key);

        $client = new Zend_Http_Client();
        $client->setUri($this->getConfig()->credomatic->url);

        $parameters = array(
            'username' => $userCredomatic,
            'type' => 'void',
            'key_id' => $key_id,
            'hash' => $hash,
            'time' => $time,
            'amount' => $amount,
            //'orderid' => $data['orderid'],
            'transactionid' => $data['transactionid'],
            'processor_id' => '',
            'ccnumber' => $cc,
        );

        /*  $log = new Mongo_PaymentLog();
          $log->save($parameters); */

        $client->setMethod(Zend_Http_Client::POST);
        $client->setParameterPost($parameters);

        $response = $client->request();
        $data     = parse_str($response->getBody(), $output);
        //  $log->save($output);

        return $output;
    }

    private function _validarHash($data)
    {

        $key = $this->getConfig()->credomatic->key;

        $hash = MD5($data['orderid']."|".$data['amount']."|".$data['?response']."|".$data['transactionid']."|"
            .$data['avsresponse']."|".$data['cvvresponse']."|".$data['time']."|".$key);

        if ($hash == $data['hash']) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @todo refactorizar Metodo se llama demasiadas instancias
     */
    public function pagarAction()
    {

        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $idAnuncio        = $this->_getParam('aviso');
            $sessionAdmin     = new Zend_Session_Namespace('admin');
            $isSessionAdmin   = ($sessionAdmin->auth ? true : false);
            //DA DE BAJA A ANUNCIOS QUE TAMBIEN SON EXTENSIONES DEL MISMO ORIGEN
            $aviso            = new Application_Model_AnuncioWeb();
            $CompraAdecsysRuc = new Application_Model_CompraAdecsysRuc();
            $tipo             = $this->_getParam('tipoAviso');
            $allParams        = $this->_getAllParams();

            $sessionDatosPasarela = NEW Zend_Session_Namespace('facturaDatos');
            //SE DEBE MODIFICAR
            if ($tipo == Application_Model_AnuncioWeb::TIPO_PREFERENCIAL) {
                $path = 'publica-aviso-preferencial/paso4/impreso/';
            } else if ($tipo == Application_Model_AnuncioWeb::TIPO_DESTACADO) {
                $path = 'publica-aviso-destacado/paso3/aviso/';
            } else {
                $path = 'publica-aviso/paso4/aviso/';
            }
            $validaRUC = $sessionDatosPasarela->factura;

            if ($validaRUC == 'Error') {
                $this->_redirect('/empresa/'.$path.$idAnuncio);
            }

            $interm = 'validaRUC';
            if (empty($validaRUC)) {
                $interm = 'allParams';
            }
            $rowsAdecsyruc = array(
                'ruc' => ${$interm}['txtRuc'],
                'razon_social' => ${$interm}['txtName'],
                'tipo_via' => ${$interm}['selVia'],
                'direccion' => ${$interm}['txtLocation'],
                'nro_puerta' => ${$interm}['txtNroPuerta'],
                'creado_por' => $this->auth['usuario']->id
            );

            $rowAnuncio = array();

            if ($tipo == Application_Model_AnuncioWeb::TIPO_PREFERENCIAL) {
                $this->_ai  = new Application_Model_AnuncioImpreso();
                $rowAnuncio = $this->_ai->getDatosPagarAnuncioImpreso($idAnuncio);
            } else {
                $this->_aw  = new Application_Model_AnuncioWeb();
                $rowAnuncio = $this->_aw->getDatosGenerarCompra($idAnuncio);
            }
            $allParams = $this->_getAllParams();



            $extraCargos      = array();
            $totalExtracargos = 0;

            foreach ($allParams as $key => $value) {

                if (substr($key, 0, 3) == "xc_") {
                    $index                                      = substr($key, 3);
                    $extraCargos[$index]                        = $value;
                    $rowAnuncio['extracargosComprados'][$index] = $rowAnuncio['extracargos'][$index];
                    if ($rowAnuncio['tipo'] == Application_Model_AnuncioWeb::TIPO_DESTACADO)
                            $totalExtracargos += $rowAnuncio['tarifaPrecio'] * $rowAnuncio['extracargosComprados'][$index]['valorExtracargo']
                            / 100;
                    else
                            $totalExtracargos += $rowAnuncio['extracargosComprados'][$index]['precioExtracargo'];
                }
            }

            /**
             * sacamos el cod_subseccion de todos los anuncios_web
             * @author Luis Alberto Mayta <slovacus@gmail.com>
             */
            $cod_subseccion            = isset($rowAnuncio['anunciosWeb']['0']['cod_subseccion'])
                    ? $rowAnuncio['anunciosWeb']['0']['cod_subseccion'] : NULL;
            if ($rowAnuncio['tipo'] == Application_Model_AnuncioWeb::TIPO_DESTACADO)
                    $rowAnuncio['totalPrecio'] = $rowAnuncio['tarifaPrecio'] - $totalExtracargos;
            else
                    $rowAnuncio['totalPrecio'] = $rowAnuncio['tarifaPrecio'] + $totalExtracargos;

            $rowAnuncio['tipoDoc']  = $this->_getParam('radioTipoDoc');
            $rowAnuncio['tipoPago'] = $this->_getParam('radioTipoPago');
            if (isset($this->auth['usuario'])) {
                $usuario                 = $this->auth['usuario'];
                $rowAnuncio['usuarioId'] = $usuario->id;
            }

            //Actualizamos el dato prioridad en la tabla anuncio_web
            $tieneContrato        = false;
            $precioContrato       = null;
            $contratoSeleccionado = null;
            $tipoContrato         = null;

            //Actualizamos el dato prioridad en la tabla anuncio_web
            if ($rowAnuncio['tipo'] == Application_Model_AnuncioWeb::TIPO_PREFERENCIAL) {

                $objAI  = new Application_Model_AnuncioImpreso();
                $objAW  = new Application_Model_AnuncioWeb();
                $helper = new App_Controller_Action_Helper_Aviso();

                $whereAi = $objAI->getAdapter()->quoteInto('id = ?',
                    $rowAnuncio['anuncioImpresoId']);

                $whereAw = $objAW->getAdapter()->quoteInto('id_anuncio_impreso = ?',
                    $rowAnuncio['anuncioImpresoId']);

                $okUpdateAi = $objAI->update(
                    array(
                    'cod_subseccion' => $cod_subseccion
                    ), $whereAi);

                $contratos      = array();
                $esVip          = false;
                $tieneMembresia = false;
                $tieneCredito   = false;

                if (isset($this->auth["anuncioImpreso"])) {
                    $dataAnuncio = $this->auth["anuncioImpreso"];
                    if ($dataAnuncio["id"] == $idAnuncio) {
                        $contratos      = $dataAnuncio["contratos"];
                        $esVip          = $dataAnuncio["esVip"];
                        $tieneMembresia = $dataAnuncio["tieneMembresia"];
                        $tieneContrato  = $dataAnuncio["tieneContrato"];
                        $tieneCredito   = $dataAnuncio["tieneCredito"];
                    }
                }

                $dataPrioridad = $aviso->prioridadAviso($rowAnuncio['tipo'],
                    $this->auth['empresa']['id']);
                $prioridad     = $dataPrioridad['prioridad'];
                $okUpdateP     = $objAW->update(
                    array(
                    'prioridad' => $prioridad
                    ), $whereAw
                );


                if ($tieneContrato) {
                    $precioContrato       = $dataAnuncio["precioContrato"];
                    $tipoContrato         = $dataAnuncio["tipoContrato"];
                    $contratoSeleccionado = $this->seleccionarContrato(
                        $contratos, $tipoContrato, $allParams['radioTipoPago']
                    );
                }

                //Inicio - Seleccion es Credito
                if ($this->_getParam('radioTipoPago') == "credito") {
                    if ($this->_getParam('radioTipoPago') == "credito" && ($tipoContrato
                        == "" || is_null($tipoContrato))) {
                        $rowAnuncio['tipoPago'] = Application_Model_Compra::FORMA_PAGO_CREDITO;
                        $tipoContrato           = Application_Model_Compra::TIPO_CONTRATO_CREDITO;
                    }

                    //RE-SELECCION DE CONTRATO AL SER EL TIPO DE CONTRATO DEL CLIENTE CREDITO Y FORMA DE PAGO CREDITO
                    $contratoSeleccionado = $this->seleccionarContrato(
                        $contratos, $tipoContrato, $allParams['radioTipoPago']
                    );

                    $moneda = $this->_config->app->moneda;
                    if ($contratoSeleccionado["MontoAPagar"] <= 0) {
                        $this->getMessenger()->error("El precio del anuncio debe ser un monto mayor a $moneda 0.");
                        $this->_redirect('/empresa/publica-aviso-preferencial/paso4/impreso/'.$idAnuncio);
                    }

                    if ($contratoSeleccionado["SaldoInicial"] < $contratoSeleccionado["MontoAPagar"]) {
                        $this->getMessenger()->errorout(
                            "El precio del anuncio es mayor al saldo disponible.".
                            " Contáctese con el administrador y/o asesor."
                        );
                        $this->_redirect('/empresa/publica-aviso-preferencial/paso4/impreso/'.$idAnuncio);
                    }

                    $rowAnuncio['totalPrecio']  = $contratoSeleccionado["MontoAPagar"];
                    $rowAnuncio['tipoContrato'] = $contratoSeleccionado["ModalidadContrato"];
                    $rowAnuncio['nroContrato']  = $contratoSeleccionado["NroContrato"];

                    /*
                     * se aplica descuento solo si estas navegando como usuario
                     */
                    $descuentos = 0;
                    if ($isSessionAdmin && isset($allParams['selDiscount']) && isset($this->_config->extracargosAvisos)) {
                        $rowAnuncio['selDiscount'] = (int) $allParams['selDiscount'];
                        $rowAnuncio['descuentos']  = $this->_helper->aviso->descuentos($rowAnuncio);
                        $descuentos                = $rowAnuncio['totalPrecio'] * (100
                            / (int) $allParams['selDiscount']);
                    }
                    $rowAnuncio['totalPrecio']  = $rowAnuncio['totalPrecio'] - $descuentos;
                    $compraId                   = $this->_helper->aviso->generarCompraAnuncio($rowAnuncio);
                    $rowsAdecsyruc['id_compra'] = $compraId;
                    $CompraAdecsysRuc->registrarCompraAviso($rowsAdecsyruc);
                    $this->_helper->aviso->confirmarCompraAviso($compraId, 1,
                        $contratoSeleccionado);

                    //Actualizando id membresia a Anuncio Web

                    $okUpdateP = $objAW->update(array(
                        'id_empresa_membresia' => $this->auth['empresa']['em_id']),
                        $whereAw);

                    //Actualizando id membresia a Anuncio Impreso

                    $okUpdateAi = $objAI->update(
                        array(
                        'id_empresa_membresia' => $this->auth['empresa']['em_id']
                        ), $whereAi);

                    $this->auth["anuncioImpreso"]["contratoSeleccionado"] = $contratoSeleccionado;
                    $this->auth["anuncioImpreso"]["contratos"]            = null;
                    Zend_Auth::getInstance()->getStorage()->write($this->auth);
                    $this->_redirect('/empresa/comprar-aviso/pago-satisfactorio/compra/'.$compraId);
                }
            } else {

                $objAW  = new Application_Model_AnuncioWeb();
                $where  = $objAW->getAdapter()->quoteInto('id_anuncio_impreso = ?',
                    $rowAnuncio['anuncioImpresoId']);
                $helper = new App_Controller_Action_Helper_Aviso();

                $dataPrioridad = $aviso->prioridadAviso($rowAnuncio['tipo'],
                    $rowAnuncio['empresaId']);
                $prioridad     = $dataPrioridad['prioridad'];
                //$prioridad = $helper->getOrdenPrioridad($rowAnuncio['tipo'], $this->auth['empresa']['id']);


                $where = $objAW->getAdapter()->quoteInto('id_anuncio_impreso = ?',
                    $rowAnuncio['anuncioImpresoId']);

                //Prioridad si los avisos son destacados
                if ($rowAnuncio['tipo'] == Application_Model_AnuncioWeb::TIPO_DESTACADO) {

                    $modelAviso  = new Application_Model_AnuncioWeb;
                    $dataEmpresa = $modelAviso->prioridadEmpresaAvisoDestacado($this->auth['empresa']['id']);
                    $prioridad   = $dataEmpresa['prioridad'];
                }


                $okUpdateP = $objAW->update(
                    array(
                    'prioridad' => $prioridad
                    ), $where
                );
            }


            if ($tieneContrato) {
                $rowAnuncio['totalPrecio']  = $contratoSeleccionado["MontoAPagar"];
                $rowAnuncio['tipoContrato'] = $contratoSeleccionado["ModalidadContrato"];
                $rowAnuncio['nroContrato']  = $contratoSeleccionado["NroContrato"];
            }


            $idImpreso = null;
            $modelAI   = new Application_Model_AnuncioImpreso();
            if ($rowAnuncio['tipo'] == Application_Model_AnuncioWeb::TIPO_CLASIFICADO) {
                $idImpreso = $aviso->getIdImpresoByIdAviso($idAnuncio);
            } else {
                $idImpreso = $idAnuncio;
            }

            $arrayCip   = $modelAI->getCipByIdImpreso($idImpreso);
            /*
             * se aplica descuento solo si estas navegando como usuario
             *
             * round(1.95583, 2);
             */
            $descuentos = 0;
            if ($isSessionAdmin && isset($allParams['selDiscount']) && isset($this->_config->extracargosAvisos)) {
                $rowAnuncio['selDiscount'] = $allParams['selDiscount'];
                $rowAnuncio['descuentos']  = $this->_helper->aviso->descuentos($rowAnuncio);
                $descuentos                = $rowAnuncio['totalPrecio'] * round(((int) $allParams['selDiscount']
                        / 100), 2);
            }
            $rowAnuncio['totalPrecio'] = $rowAnuncio['totalPrecio'] - $descuentos;

            if ($arrayCip != false && $arrayCip['cip'] != null) {

                if ($this->_getParam('radioTipoPago') == 'pe') {
                    $rowAnuncio['tipoPago'] = Application_Model_Compra::FORMA_PAGO_PAGO_EFECTIVO;

                    $compraId               = $this->_helper->aviso->generarCompraAnuncio($rowAnuncio);
                    $rowAnuncio['compraId'] = $compraId;
                    $cip                    = $this->_helper->WebServiceCip->generarCip($rowAnuncio);
                }

//                $this->_helper->WebServiceCip->eliminarCip($arrayCip['cip']);
            } elseif (isset($arrayCip) && $arrayCip['cip'] == null &&
                $this->_getParam('radioTipoPago') == 'pe') {
                $rowAnuncio['tipoPago'] = Application_Model_Compra::FORMA_PAGO_PAGO_EFECTIVO;

                $compraId = $this->_helper->aviso->generarCompraAnuncio($rowAnuncio);

                $rowAnuncio['compraId'] = $compraId;

                $cip = $this->_helper->WebServiceCip->generarCip($rowAnuncio);
            }

            switch ($this->_getParam('radioTipoPago')) {
                case 'pe':
                    $helper    = $this->_helper->getHelper('WebServiceCip');
                    $where     = $this->_compra->getAdapter()->quoteInto('id = ?',
                        $compraId);
                    $okUpdateP = $this->_compra->update(array(
                        'token' => NULL), $where);
                    if ($cip['numero'] == "") {
                        $this->_helper->flashMessenger('Intente Nuevamente...');
                        $this->_redirect('/empresa/'.$path.$idAnuncio); //cambiado
                    }
                    $where        = $this->_compra->getAdapter()->quoteInto('id = ?',
                        $compraId);
                    $okUpdateP    = $this->_compra->update(
                        array(
                        'cip' => $cip['numero'],
                        'fh_expiracion_cip' => $cip['fechaExpiracion']
                        ), $where
                    );
                    $cadena       = "cip=".$cip['numero']
                        ."|capi=".$this->getConfig()->configCip->capi
                        ."|cclave=".$this->getConfig()->configCip->clave;
                    $helper       = $this->_helper->getHelper('WebServiceEncriptacion');
                    $codigoBarras = $helper->encriptaCadena($cadena);


                    $rowAnuncio['cip']             = $cip['numero'];
                    $rowAnuncio['codigoBarras']    = $codigoBarras;
                    $rowAnuncio['urlGeneraImagen'] = $this->getConfig()->urlsComprarAviso->CIP->generaImagen;
                    $sess                          = $this->getSession();
                    $sess->rowAnuncio              = $rowAnuncio;
                    $rowsAdecsyruc['id_compra']    = $compraId;
                    $CompraAdecsysRuc->registrarCompraAviso($rowsAdecsyruc);
                    $rowCompra                     = $this->_compra->getDetalleCompraAnuncio($compraId);

                    //Update a aviso con Id Compra
                    $whereUpdate = $this->_aw->getAdapter()->quoteInto('id = ?',
                        $idAnuncio);
                    $aviso->update(
                        array(
                        'id_compra' => $compraId,
                        ), $whereUpdate
                    );

                    $usuario = $this->auth['usuario'];
                    if ($rowCompra['tipoAnuncio'] == Application_Model_Compra::TIPO_SOLOWEB
                        ||
                        $rowCompra['tipoAnuncio'] == Application_Model_Compra::TIPO_CLASIFICADO) {

                        $tipo = ( Application_Model_Compra::TIPO_SOLOWEB ==
                            $rowCompra['tipoAnuncio'] ? '' : 'económico' );

                        $dataMail = array(
                            'to' => $usuario->email,
                            'usuario' => $usuario->email,
                            'nombre' => $rowCompra['nombreContacto']." ".$rowCompra['apePatContacto'],
                            'anuncioPuesto' => $rowCompra['anuncioPuesto'],
                            'razonSocial' => $rowCompra['nombre_comercial'],
                            'montoTotal' => $rowCompra['montoTotal'],
                            'medioPago' => $rowCompra['medioPago'],
                            'cip' => $rowCompra['cip'],
                            'slug' => $rowCompra['anuncioSlug'],
                            'urlId' => $rowCompra['anuncioUrl'],
                            'tipo' => $tipo
                        );

                        try {
                            $this->_helper->mail->confirmarVouchcodigoerPagoEfectivo($dataMail);
                        } catch (Exception $ex) {
                            $this->getMessenger()->error(
                                'Error al enviar el correo con los datos de la compra.'
                            );
                        }
                    } else if ($rowCompra['tipoAnuncio'] == Application_Model_Compra::TIPO_PREFERENCIAL
                        ||
                        $rowCompra['tipoAnuncio'] == Application_Model_Compra::TIPO_DESTACADO) {
                        $tipoAviso = $rowCompra['tipoAnuncio'];
                        $dataMail  = array(
                            'to' => $rowCompra['emailContacto'],
                            'usuario' => $rowCompra['emailContacto'],
                            'titulo' => $rowCompra['tituloAnuncioImpreso'],
                            //ucfirst($rowCompra['anuncioClase'])." ".$rowCompra['tamanio'],
                            'nombre' => $rowCompra['nombreContacto']." ".$rowCompra['apePatContacto'],
                            'nroPuestos' => (isset($rowCompra['anunciosWeb']) ? count($rowCompra['anunciosWeb'])
                                    : 0 ),
                            'razonSocial' => $rowCompra['nombre_comercial'],
                            'montoTotal' => $rowCompra['montoTotal'],
                            'medioPago' =>
                            ($rowCompra['medioPago'] == "pe" ? "Pago Efectivo" : $rowCompra['medioPago']
                                == "pe"),
                            'anuncioClase' => ucfirst($rowCompra['anuncioClase']),
                            'tipoAviso' => $rowCompra['tamanio']." (".$rowCompra['tamanioCentimetros']." cm.)",
                            'productoNombre' => $rowCompra['productoNombre'],
                            'cip' => $rowCompra['cip']
                        );
                        try {
                            if ($tipoAviso == Application_Model_Compra::TIPO_PREFERENCIAL) {
                                $this->_helper->mail->confirmarVoucherPagoEfectivoPreferencial($dataMail);
                            } else if ($tipoAviso == Application_Model_Compra::TIPO_DESTACADO) {
                                $dataMail['titulo'] = $rowCompra['anuncioPuesto'];
                                $this->_helper->mail->confirmarVoucherPagoEfectivoDestacado($dataMail);
                            }
                        } catch (Exception $ex) {
                            $this->getMessenger()->error('Error al enviar el correo con los datos de la compra.');
                        }
                    }

                    $sessionAdmin = new Zend_Session_Namespace('admin');
                    try {
                        $mCompra = new Mongo_Compra();
                        $datos   = array(
                            'empresa' => $this->auth,
                            'admin' => $sessionAdmin->auth,
                            'aviso' => $this->_anuncioWeb->getByIdCompra($compraId),
                            'impreso' => $this->_anuncioImpreso->getByIdCompra($compraId),
                            'compra_adecsys' => $this->_compraAdecsys->getByIdCompra($compraId),
                            'compra' => $this->_compra->getById($compraId)
                        );
                        $mCompra->save($datos);
                    } catch (Exception $e) {

                    }

                    $this->_redirect('/empresa/comprar-aviso/pago-efectivo/');
                    break;
                case'visa':
                    $compraId                   = $this->_helper->aviso->generarCompraAnuncio($rowAnuncio);
                    $rowsAdecsyruc['id_compra'] = $compraId;
                    $CompraAdecsysRuc->registrarCompraAviso($rowsAdecsyruc);
                    $cadena                     = "OrderId=".$compraId
                        ."|Amount=".$rowAnuncio['totalPrecio']
                        ."|UserId=".$rowAnuncio['usuarioId']
                        ."|UrlOk=".$this->getConfig()->app->siteUrl
                        ."/empresa/comprar-aviso/ok"
                        ."|UrlError=".$this->getConfig()->app->siteUrl
                        ."/empresa/".$path //cambiado
                        .$rowAnuncio['anuncioId']."/error/1";
                    $helper                     = $this->_helper->getHelper('WebServiceEncriptacion');
                    $cadenaEnc                  = $helper->encriptaCadena($cadena);
                    $sessionAdmin               = new Zend_Session_Namespace('admin');
                    try {
                        $mCompra = new Mongo_Compra();
                        $datos   = array(
                            'empresa' => $this->auth,
                            'admin' => $sessionAdmin->auth,
                            'aviso' => $this->_anuncioWeb->getByIdCompra($compraId),
                            'impreso' => $this->_anuncioImpreso->getByIdCompra($compraId),
                            'compra_adecsys' => $this->_compraAdecsys->getByIdCompra($compraId),
                            'compra' => $this->_compra->getById($compraId)
                        );
                        $mCompra->save($datos);
                    } catch (Exception $e) {

                    }
                    $this->_redirect($this->getConfig()->urlsComprarAviso->visa."=".$cadenaEnc."&mp=v");
                    break;
                case'mc':
                    $compraId                   = $this->_helper->aviso->generarCompraAnuncio($rowAnuncio);
                    $rowsAdecsyruc['id_compra'] = $compraId;
                    $CompraAdecsysRuc->registrarCompraAviso($rowsAdecsyruc);
                    $cadena                     = "OrderId=".$compraId
                        ."|Amount=".$rowAnuncio['totalPrecio']
                        ."|UserId=".$rowAnuncio['usuarioId']
                        ."|UrlOk=".$this->getConfig()->app->siteUrl
                        ."/empresa/comprar-aviso/ok"
                        ."|UrlError=".$this->getConfig()->app->siteUrl
                        ."/empresa/".$path //cambiado
                        .$rowAnuncio['anuncioId']."/error/1";
                    $helper                     = $this->_helper->getHelper('WebServiceEncriptacion');
                    $cadenaEnc                  = $helper->encriptaCadena($cadena);
                    $sessionAdmin               = new Zend_Session_Namespace('admin');

                    //                $impreso = ( $this->_anuncioImpreso->getByIdCompra($compraId) ?
                    //                        $this->_anuncioImpreso->getByIdCompra($compraId)->toArray() : null);
                    try {
                        $mCompra = new Mongo_Compra();
                        $datos   = array(
                            'empresa' => $this->auth,
                            'admin' => $sessionAdmin->auth,
                            'aviso' => $this->_anuncioWeb->getByIdCompra($compraId),
                            'impreso' => $this->_anuncioImpreso->getByIdCompra($compraId),
                            'compra_adecsys' => $this->_compraAdecsys->getByIdCompra($compraId),
                            'compra' => $this->_compra->getById($compraId)
                        );
                        $mCompra->save($datos);
                    } catch (Exception $e) {

                    }
                    $this->_redirect($this->getConfig()->urlsComprarAviso->visa."=".$cadenaEnc."&mp=m");
                    break;

                case 'pos':
                    $sessionAdmin = new Zend_Session_Namespace('admin');
                    if ($sessionAdmin->auth) {

                        $compraId                   = $this->_helper->aviso->generarCompraAnuncio($rowAnuncio);
                        $rowsAdecsyruc['id_compra'] = $compraId;
                        $CompraAdecsysRuc->registrarCompraAviso($rowsAdecsyruc);

                        $sess     = $this->getSession();
                        $datosPos = $sess->rowDataPos;

                        $where     = $this->_compra->getAdapter()->quoteInto('id = ?',
                            $compraId);
                        $okUpdateP = $this->_compra->update(array(
                            'nro_voucher' => $datosPos['nro_voucher'],
                            'fecha_pago_pos' => $datosPos['fecha_pago_pos'],
                            'id_tarjeta_banco' => $datosPos['id_tarjeta_banco'],
                            'id_tipo_tarjeta' => $datosPos['id_tipo_tarjeta'],
                            'lote' => $datosPos['lote'],
                            ), $where);

                        unset($sess->rowDataPos);
                        $compra = $this->_compra->getById($compraId);
                        $ip     = $this->getRequest()->getServer('REMOTE_ADDR');
                        try {
                            $mCompra = new Mongo_Compra();
                            $datos   = array(
                                'empresa' => $this->auth,
                                'admin' => $sessionAdmin->auth,
                                'aviso' => $this->_anuncioWeb->getByIdCompra($compraId),
                                'impreso' => $this->_anuncioImpreso->getByIdCompra($compraId),
                                'compra_adecsys' => $this->_compraAdecsys->getByIdCompra($compraId),
                                'compra' => $compra,
                                'ip' => $ip
                            );
                            $mCompra->save($datos);
                        } catch (Exception $e) {

                        }
                        try {
                            $mBanco             = new Application_Model_TarjetaBanco();
                            $listaBancos        = $mBanco->getBancosFormSelect();
                            $mTarjetas          = new Application_Model_TipoTarjeta();
                            $listaTarjetas      = $mTarjetas->getTarjetasFormSelect();
                            $util               = new App_Controller_Action_Helper_Util();
                            $token              = $util->codifica("$compraId|{$compra['id_empresa']}");
                            $dataMail           = array(
                                'token' => $token,
                                'empresa' => $this->auth,
                                'admin' => $sessionAdmin->auth,
                                'voucher' => $datosPos['nro_voucher'],
                                'datosPos' => $datosPos,
                                'listaBancos' => $listaBancos,
                                'listaTarjetas' => $listaTarjetas,
                                'compra' => $compra,
                                'ip' => $ip
                            );
                            $addEmail           = $this->_config->pos->
                                administrador->generacion->email;
                            $dataMail['to']     = $addEmail;
                            $dataMail['addBcc'] = $this->_config->pos->
                                administrador->generacion->bcc;
                            $this->_helper->mail->notificacionAdminEnvioPos($dataMail);
                            //$data = $this->_helper->mail->notificacionAdminEnvioPos($dataMail);
                            //file_put_contents("$compraId.html", $data);
                            $this->getMessenger()->success('Aviso pendiente de activación.');
                        } catch (Exception $ex) {
                            $this->getMessenger()->error('Error al enviar el correo de activación.');
                            //$this->getMessenger()->error($ex->getMessage());
                        }
                        $this->_redirect('/empresa/comprar-aviso/pago-satisfactorio/compra/'.$compraId);
                    }


                    break;
            }
        } catch (Exception $ex) {
            $this->getMessenger()->error('Por favor vuelva a intentarlo.');
        }
    }

    public function generarPdfAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $domPdf                       = $this->_helper->getHelper('DomPdf');
        $this->view->headLink()->appendStylesheet(
            $this->view->S(
                '/css/default.css')
        );
        $this->view->headLink()->appendStylesheet(
            $this->view->S(
                '/css/layout.css')
        );
        $this->view->headLink()->appendStylesheet(
            $this->view->S(
                '/css/class.css')
        );
        $rowCompra                    = $this->_compra->getDetalleCompraAnuncio($this->_getParam('compra'));
        $rowCompra['urlCodigoBarras'] = $this->getConfig()->urlsComprarAviso->CIP->generaImagen;
        $rowCompra['codEncriptado']   = $this->_getParam('codEncrip');
        $this->view->compra           = $rowCompra;

        $nombre_file = 'pago-efectivo.pdf';
        if (isset($rowCompra['cip'])) {
            $nombre_file = $rowCompra['cip'].'.pdf';
        }

        $medioPublicacion     = $rowCompra['medioPublicacion'];
        if ($medioPublicacion == 'aptitus y talan') $medioPublicacion     = 'combo';
        $this->view->fhCierre = $this->_helper->Aviso->getFechaCierreImpresoByPaquete($medioPublicacion);
        $headLinkContainer    = $this->view->headLink()->getContainer();
        unset($headLinkContainer[0]);
        unset($headLinkContainer[1]);

        $html = $this->view->render('comprar-aviso/imprimir-pago-efec.phtml');
        $domPdf->mostrarPDF($html, 'A4', "portrait", $nombre_file);
    }

    public function seleccionarContrato($contratos, $tipo, $tipoPago)
    {
        if ($tipoPago == 'pe' || $tipoPago == 'visa' || $tipoPago == 'mc') {
            $tipo = 'C';
        }

        $contratoSeleccionado = array();
        foreach ($contratos as $contrato) {
            if ($tipo == Application_Model_Compra::TIPO_CONTRATO_CREDITO) {
                if ($contrato["FormaPago"] == Application_Model_Compra::TIPO_CONTRATO_CREDITO
                    &&
                    ($contrato["ModalidadContrato"] == Application_Model_Compra::TIPO_CONTRATO_CREDITO
                    ||
                    $contrato["ModalidadContrato"] == Application_Model_Compra::TIPO_CONTRATO_MULTIMEDIOS
                    ||
                    $contrato["ModalidadContrato"] == Application_Model_Compra::TIPO_CONTRATO_MEMBRESIA)) {
                    $contratoSeleccionado = $contrato;
                    break;
                }
            } else {
                if ($contrato["ModalidadContrato"] == $tipo ||
                    $contrato["ModalidadContrato"] == Application_Model_Compra::TIPO_CONTRATO_MEMBRESIA
                    ||
                    $contrato["ModalidadContrato"] == Application_Model_Compra::TIPO_CONTRATO_MULTIMEDIOS) {
                    $contratoSeleccionado = $contrato;
                    break;
                }
            }
        }

        return $contratoSeleccionado;
    }

    public function pagoPosAction()
    {
        // exit(0);
        $sessionAdmin = new Zend_Session_Namespace('admin');
        if ($sessionAdmin->auth) {
            $request    = $this->getRequest();
            $tokenValid = $this->_hash->isValid($request->getParam('token', null));

            if ($request->isPost() && $request->isXmlHttpRequest() && $tokenValid) {
                $this->_helper->layout->disableLayout();
                $formPos          = new Application_Form_PagoPos();
                $this->view->form = $formPos;
                echo $this->view->render('_partials/modal_boxes/_form_modal_pos.phtml');
            }
        }
        exit(0);
    }

    public function validaDataPosAction()
    {
        //exit("testtt");
        $this->_helper->layout->disableLayout();
        $sessionAdmin = new Zend_Session_Namespace('admin');
        if ($sessionAdmin->auth) {
            $request = $this->getRequest();
            if ($request->isPost() && $request->isXmlHttpRequest()) {
                $postData = $request->getParams();

                $formPOS = new Application_Form_PagoPos();

                if ($formPOS->isValid($postData)) {

                    $modelCompra     = new Application_Model_Compra();
                    $yaExisteVoucher = $modelCompra->existeCompraPOSVoucher($postData['voucher'],
                        $postData['banco']);

                    if (!$yaExisteVoucher) {
                        $arFecha   = explode('/', $postData['payment_date']);
                        $fechaPago = $arFecha[2].'-'.$arFecha[1].'-'.$arFecha[0];

                        $validFecha   = new Zend_Date($fechaPago);
                        $esFechaMayor = $validFecha->compare(new Zend_Date(date('Y-m-d')));

                        $fecha3d = new Zend_Date(date('Y-m-d'));
                        $fecha3  = $fecha3d->subDay(3);

                        if ($esFechaMayor > 0) {
                            $dataJSON = array(
                                'status' => 0,
                                'message' => 'La fecha de pago no debe ser mayor a la actual.',
                            );
                        } else {
                            $sess             = $this->getSession();
                            $sess->rowDataPos = array(
                                'nro_voucher' => $postData['voucher'],
                                'fecha_pago_pos' => $fechaPago,
                                'id_tarjeta_banco' => $postData['banco'],
                                'id_tipo_tarjeta' => $postData['tarjeta'],
                                'lote' => $postData['lote'],
                            );

                            $dataJSON = array(
                                'status' => 1,
                                'message' => 'El aviso fue canselado',
                            );

                            if ($validFecha->compare($fecha3) == -1) {
                                $dataJSON = array(
                                    'status' => 0,
                                    'message' => 'La fecha no debe ser menor a 3 días',
                                );
                            }
                        }
                    } else {

                        $dataJSON = array(
                            'status' => 0,
                            'message' => 'El Voucher ya existe.',
                        );
                    }
                } else {

                    $dataJSON = array(
                        'status' => 0,
                        'message' => 'Los datos ingresados no son válidos. Por favor vuelva a intentarlo.',
                    );
                }

                $tokenNuevo        = $formPOS->getElement('auth_token');
                $tokenNuevo->initCsrfToken();
                $dataJSON['token'] = $tokenNuevo->getHash();

                echo $this->_response->appendBody(Zend_Json::encode($dataJSON));
            }
        }

        exit(0);
    }

    public function okPosAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $token = $this->_getParam('token');
        if (!$token) {
            $this->_redirect('/');
        }
        $util      = new App_Controller_Action_Helper_Util();
        $tokenDeco = $util->decodifica($token);
        $arrVal    = explode('|', $tokenDeco);
        $compraId  = (int) $arrVal[0];
        $idEmpresa = (int) $arrVal[1];
        $estado    = $this->_compra->verificarPOS($compraId, $idEmpresa);
        if (!empty($estado) && $estado != Application_Model_Compra::ESTADO_PAGADO) {
            try {
                $this->_helper->aviso->confirmarCompraAviso($compraId);
                $awi       = new Application_Model_AnuncioWeb;
                $dataAviso = $awi->avisosPorIdCompra($compraId);
                foreach ($dataAviso as $infoAviso) {
                    $this->_helper->aviso->getSolarAviso()->addAvisoSolr($infoAviso['id']);
                    //$resultado = exec("curl -X POST -d 'api_key=".$this->_buscamasConsumerKey."&nid=".$infoAviso['id']."&site=".$this->_buscamasUrl."' ".$this->_buscamasPublishUrl);
                }
                echo "Compra $compraId activada<br/>";
                $flag = true;
            } catch (Exception $ex) {
                echo "No se pudo activar la Compra $compraId";
                $flag = false;
            }
            if ($flag) {
                $compra = $this->_compra->getById($compraId);
                $ip     = $this->getRequest()->getServer('REMOTE_ADDR');
                try {
                    $sessionAdmin = new Zend_Session_Namespace('admin');
                    $mCompra      = new Mongo_Compra();
                    $datos        = array(
                        'empresa' => $this->auth,
                        'admin' => $sessionAdmin->auth,
                        'aviso' => $this->_anuncioWeb->getByIdCompra($compraId),
                        'impreso' => $this->_anuncioImpreso->getByIdCompra($compraId),
                        'compra_adecsys' => $this->_compraAdecsys->getByIdCompra($compraId),
                        'compra' => $compra,
                        'ip' => $ip
                    );
                    $mCompra->save($datos);
                } catch (Exception $e) {

                }
                try {
                    $dataMail           = array(
                        'voucher' => $compra['nro_voucher'],
                        'compra' => $compra
                    );
                    $addEmail           = $this->_config->pos->
                        administrador->activacion->email;
                    $dataMail['to']     = $addEmail;
                    $dataMail['addBcc'] = $this->_config->pos->
                        administrador->activacion->bcc;
                    $this->_helper->mail->notificacionAdminActivacionPos($dataMail);
                    //$data = $this->_helper->mail->notificacionAdminActivacionPos($dataMail);
                    //file_put_contents("$compraId.html", $data);
                    echo 'Correo de confirmación enviado.';
                } catch (Exception $ex) {
                    echo 'Error al enviar el correo de confirmación.';
                }
            }
        } else {
            $this->_redirect('/');
        }
    }

    /**
     *
     * Pago efectivo consulta este método para extornar pagos
     *
     */
    public function extornoPagoEfectivoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr',
            array(
            'id' => 'perfilReg',
            'class' => 'noMenu')
        );
        $cadenaEncriptada = $this->_getParam('datosEnc');

        try {
            $datos                   = array();
            $datos['encripta']       = $cadenaEncriptada;
            $datos['fecha_creacion'] = date('Y-m-d H:i:s');
            $mongoPE                 = new Mongo_PeExtorno();
            $id                      = $mongoPE->save($datos);
        } catch (Exception $e) {

        }
        $helper       = $this->_helper->getHelper('WebServiceEncriptacion');
        $cadenaDesEnc = $helper->desencriptaCadena($cadenaEncriptada);

        if (empty($cadenaDesEnc) && !isset($this->getConfig()->app->debug)) {
            throw new Zend_Exception('El valor a Desencriptar es Nulo', 500);
        }

        if (!empty($cadenaDesEnc)) {
            $arrayDatos = explode("|", $cadenaDesEnc);
            $compraId   = substr($arrayDatos[1], 16);
        }

        if (isset($this->getConfig()->app->debug)) {
            $compraId = $cadenaEncriptada;
        }

        if ($this->_compra->verificarPagado($compraId)) {
            $res = $this->_helper->aviso->extornarCompraAviso($compraId);

            $rowCompra = $this->_compra->getDetalleCompraAnuncio($compraId);

            $data                  = array();
            $data['tipoAnuncio']   = $rowCompra['tipoAnuncio'];
            $data['cip']           = $rowCompra['cip'];
            $data['total']         = $rowCompra['montoTotal'];
            $data['medioPago']     = $rowCompra['medioPago'];
            $data['razonSocial']   = $rowCompra['razonSocial'];
            $data['numeroDoc']     = $rowCompra['numeroDoc'];
            $data['empresaId']     = $rowCompra['empresaId'];
            $data['emailContacto'] = $rowCompra['emailContacto'];

            //notificación
            $mail     = new App_Controller_Action_Helper_Mail();
            $dataMail = array(
                'to' => $this->getConfig()->extorno->info->email,
                'data' => $data,
                'cip' => $rowCompra['cip']
            );
            $mail->notificacionExtorno($dataMail);

            $sessionAdmin = new Zend_Session_Namespace('admin');
            try {
                $datos['fecha_edicion'] = date('Y-m-d H:i:s');
                $datos['id_compra']     = $compraId;
                $datos['_id']           = $id;

                $mCompra = new Mongo_CompraExtorno();
                $datos   = array(
                    'empresa' => $this->auth,
                    'admin' => $sessionAdmin->auth,
                    'aviso' => $this->_anuncioWeb->getByIdCompra($compraId),
                    'impreso' => $this->_anuncioImpreso->getByIdCompra($compraId),
                    'compra_adecsys' => $this->_compraAdecsys->getByIdCompra($compraId),
                    'compra' => $this->_compra->getById($compraId)
                );
                $mCompra->save($datos);
            } catch (Exception $e) {

            }
        }
    }

    //Valida si el RUC ya existe en adecsys, si es así devuelve valor, sino habilita cajas de texto
    //para registrar el ente con ese RUC
    //Se activa solo al ingresar el carácter 11
    public function validaRucAdecsysAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        //Solo peticiones ajax segura
        if (!$this->getRequest()->isXmlHttpRequest()) {
            exit("Acceso denegado");
        }

        $data = $this->_getAllParams();
        $hash = $data['csrfhash'];

        //if ($this->_hash->isValid($hash)) {
        //Prevención de XSS
        $filter = new Zend_Filter_StripTags;
        $ruc    = $filter->filter($data['ruc']);

        //Validación de Token
        //WS para validar la existencia del ente con ese RUC en Adecsys
        $validaRUC     = $this->_helper->PerfilDestacado->validarDocumentoAdecsys(self::RUC,
            $ruc);
        $enteId        = $validaRUC->Id;
        $nombreEmpresa = $validaRUC->RznSoc_Nombre;
        $tipoVia       = $validaRUC->Tip_Calle;
        $direccion     = $validaRUC->Tip_Calle." ".$validaRUC->Nom_Calle." ".$validaRUC->Num_Pta;

        $dataEmpresa = array(
            'id' => $enteId,
            'nombreEmpresa' => $nombreEmpresa,
            'via' => $tipoVia,
            'dir' => $direccion
        );

        if (is_null($validaRUC)) {
            $dataEmpresa['id']      = 0;
            $dataEmpresa['success'] = 0;
            $dataEmpresa['msg']     = 'No existe en Adecsys';
            echo Zend_Json::encode($dataEmpresa);
        } else {
            $dataEmpresa['success'] = 1;
            $dataEmpresa['msg']     = 'Ya está registrado en Adecsys';
            echo Zend_Json::encode($dataEmpresa);
        }
        //}
    }
}
