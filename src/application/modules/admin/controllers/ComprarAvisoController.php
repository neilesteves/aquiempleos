<?php


class Admin_ComprarAvisoController
    extends App_Controller_Action_Admin
{

    protected $_url = "/admin/";
    
    //Buscamas
    private $_buscamasConsumerKey;
    private $_buscamasPublishUrl;
    private $_buscamasUrl;

    public function init()
    {
        $this->_compra = new Application_Model_Compra();
        
        $this->_config = Zend_Registry::get('config');        
        
        parent::init();
    }

    public function indexAction()
    {
        $this->view->menu_sel = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_PUBLICA_AVISO;
    }

    public function pagoEfectivoAction()
    {
        /* Se cambia el Css de administrador x Empresa */
        $containerHead = $this->view->headLink()->getContainer();

        unset($containerHead[count($containerHead) - 1]);

        $this->view->headLink()->appendStylesheet(
            $this->view->S('/css/empresa/empresa.layout.css'), 'all'
        );
        
        $this->view->headLink()->appendStylesheet(
            $this->view->S('/css/empresa/empresa.class.css'), 'all'
            
        );

        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/empresa/empresa.misprocesos.js')
        );

        $sess = $this->getSession();
        $this->view->rol = $this->auth["usuario"]->rol;
         $pos=   strlen($sess->rowAnuncio['cip'])/2;
        $sess->rowAnuncio['cip']=substr($sess->rowAnuncio['cip'],  $pos);
        $this->view->anuncioWeb = $sess->rowAnuncio;
        $medioPublicacion = $sess->rowAnuncio['medioPublicacion'];
        if ($medioPublicacion == 'aptitus y talan') $medioPublicacion = 'combo';

        $this->view->fhCierre = $this->_helper->Aviso->getFechaCierreImpresoByPaquete($medioPublicacion);
        $this->view->moneda = $this->_config->app->moneda;
        $this->view->igv = $this->_config->app->igv;
    }

    public function okPagoEfectivoAction()
    {
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );
        $cadenaEncriptada = $this->_getParam('datosEnc');
        $helper = $this->_helper->getHelper('WebServiceEncriptacion');
        $cadenaDesEnc = $helper->desencriptaCadena($cadenaEncriptada);
        $arrayDatos = explode("|", $cadenaDesEnc);
        $compraId = substr($arrayDatos[1], 16);
        $this->_helper->aviso->confirmarCompraAviso($compraId);
        $this->_redirect($this->_url . 'comprar-aviso/pago-satisfactorio/compra/' . $compraId);
    }

    public function pagoSatisfactorioAction()
    {
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/empresa/empresa.aviso.paso4.js')
        );
        $compraId = $this->_getParam('compra');

        $dataCompra = $this->_compra->getDetalleCompraAnuncio($compraId);
        
        $helperAviso = $this->_helper->getHelper('Aviso');
        
        //Indexa aviso a Buscamas
        $awi = new Application_Model_AnuncioWeb;
        $dataAviso = $awi->avisosPorIdCompra($compraId);
        foreach ($dataAviso as $infoAviso) {
            $helperAviso->_SolrAviso->addAvisoSolr($infoAviso['id']);
            //exec("curl -X POST -d 'api_key=".$this->_buscamasConsumerKey."&nid=".$infoAviso['id']."&site=".$this->_buscamasUrl."' ".$this->_buscamasPublishUrl);
        }


        $contratoSeleccionado = null;
        $tipoContrato = null;
        $saldoFinal = null;
        if (isset($this->auth["anuncioImpreso"])) {
            if ($this->auth["anuncioImpreso"]["id"] == $dataCompra["anuncioImpresoId"]) {
                if (isset($this->auth["anuncioImpreso"]["contratoSeleccionado"])) {
                    $contratoSeleccionado = $this->auth["anuncioImpreso"]["contratoSeleccionado"];
                    if ($contratoSeleccionado["ModalidadContrato"] == Application_Model_Compra::TIPO_CONTRATO_MEMBRESIA)
                            $tipoContrato = "MembresÃ­a";
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

        $this->view->compra = $dataCompra;
        $this->view->tipoContrato = $tipoContrato;
        $this->view->saldoFinal = $saldoFinal;

        //colocardo temporalmente para pruebas
    }

    public function okAction()
    {
        $cadenaEncriptada = $this->_getParam('egp_data');
        $helper = $this->_helper->getHelper('WebServiceEncriptacion');
        $cadenaDesEnc = $helper->desencriptaCadena($cadenaEncriptada);
        $arrayDatos = explode("|", $cadenaDesEnc);
        $compraId = substr($arrayDatos[1], 10);
        $this->_helper->aviso->confirmarCompraAviso($compraId);
        $this->_redirect($this->_url . 'comprar-aviso/pago-satisfactorio/compra/' . $compraId);
    }

    public function pagarAction()
    {
        $idAnuncio = $this->_getParam('aviso');

        $tipo = $this->_getParam('tipoAviso');

        $session = $this->getSession();
        $idEmpresa = $session->empresaBusqueda['idempresa'];

        $modelEmpresa = new Application_Model_Empresa();
        $arrayEmpresa = $modelEmpresa->getEmpresaMembresia($idEmpresa);
        //SE DEBE MODIFICAR
        if ($tipo == 'preferencial') {
            $path = 'publicar-aviso-preferencial/paso4/impreso/';
        } else {
            $path = 'publicar-aviso/paso4/aviso/';
        }
        $rowAnuncio = array();
        if ($tipo == 'preferencial') {
            $this->_ai = new Application_Model_AnuncioImpreso();
            $rowAnuncio = $this->_ai->getDatosPagarAnuncioImpreso($idAnuncio);
        } else {
            $this->_aw = new Application_Model_AnuncioWeb();
            $rowAnuncio = $this->_aw->getDatosGenerarCompra($idAnuncio);
        }

        $allParams = $this->_getAllParams();
        $extraCargos = array();
        $totalExtracargos = 0;
        foreach ($allParams as $key => $value) {
            if (substr($key, 0, 3) == "xc_") {
                $index = substr($key, 3);
                $extraCargos[$index] = $value;
                $rowAnuncio['extracargosComprados'][$index] = $rowAnuncio['extracargos'][$index];
                $totalExtracargos +=
                    $rowAnuncio['extracargosComprados'][$index]['precioExtracargo'];
            }
        }
        $rowAnuncio['totalPrecio'] = $rowAnuncio['tarifaPrecio'] + $totalExtracargos;
        $rowAnuncio['tipoDoc'] = $this->_getParam('radioTipoDoc');
        $rowAnuncio['tipoPago'] = $this->_getParam('radioTipoPago');

        $modelUsuario = new Application_Model_Usuario();
        $usuario = $modelUsuario->getUsuarioId($arrayEmpresa["id_usuario"]);
        $rowAnuncio['usuarioId'] = $usuario->id;

        $tieneContrato = false;
        $precioContrato = null;
        $contratoSeleccionado = null;
        $tipoContrato = null;
        //Actualizamos el dato prioridad en la tabla anuncio_web
        if ($rowAnuncio['tipo'] == 'preferencial') {
            $objEmpMem = new Application_Model_EmpresaMembresia();
            //if ($objEmpMem->getExistsActive($this->auth['empresa']['id'])) {
            //$this->auth->;
            $contratos = array();
            $esVip = false;
            $tieneMembresia = false;
            $tieneCredito = false;


            if (isset($this->auth["anuncioImpreso"])) {
                $dataAnuncio = $this->auth["anuncioImpreso"];
                if ($dataAnuncio["id"] == $idAnuncio) {
                    $contratos = $dataAnuncio["contratos"];
                    $esVip = $dataAnuncio["esVip"];
                    $tieneMembresia = $dataAnuncio["tieneMembresia"];
                    $tieneContrato = $dataAnuncio["tieneContrato"];
                    $tieneCredito = $dataAnuncio["tieneCredito"];
                } else {
                    //var_dump("ERROR: Los ids son diferentes");
                    //error
                    exit;
                }
            } else {
                //var_dump("ERROR: Not set AnuncioImpreso");
                //error
                exit;
            }


            if ($tieneContrato) {
                $precioContrato = $dataAnuncio["precioContrato"];
                $tipoContrato = $dataAnuncio["tipoContrato"];
                $contratoSeleccionado = $this->seleccionarContrato($contratos,
                    $tipoContrato);
            }

            if ($this->_getParam('usoMembresia') == "1" || $this->_getParam('radioTipoPago')
                == "credito") {

                if ($this->_getParam('usoMembresia') == "1") {
                    $rowAnuncio['tipoPago'] = Application_Model_Compra::FORMA_PAGO_MEMBRESIA;
                    $tipoContrato = Application_Model_Compra::TIPO_CONTRATO_MEMBRESIA;
                } elseif ($this->_getParam('radioTipoPago') == "credito") {
                    $rowAnuncio['tipoPago'] = Application_Model_Compra::FORMA_PAGO_CREDITO;
                    $tipoContrato = Application_Model_Compra::TIPO_CONTRATO_CREDITO;
                }

                $contratoSeleccionado = $this->seleccionarContrato($contratos,
                    $tipoContrato);
                
                $moneda = $this->_config->app->moneda;
                if ($contratoSeleccionado["MontoAPagar"] <= 0) {
                    $this->getMessenger()->error("El precio del anuncio debe ser un monto mayor a $moneda 0.");
                    $this->_redirect('/admin/publicar-aviso-preferencial/paso4/impreso/' . $idAnuncio);
                }

                if ($contratoSeleccionado["SaldoInicial"] < $contratoSeleccionado["MontoAPagar"]
                    && !$esVip) {
                    $this->getMessenger()->error(
                        "El precio del anuncio es mayor al saldo disponible de su membresÃ­a."
                    );
                    $this->_redirect('/admin/publicar-aviso-preferencial/paso4/impreso/' . $idAnuncio);
                }

                $rowAnuncio['totalPrecio'] = $contratoSeleccionado["MontoAPagar"];
                $rowAnuncio['tipoContrato'] = $contratoSeleccionado["ModalidadContrato"];
                $rowAnuncio['nroContrato'] = $contratoSeleccionado["NroContrato"];

                $compraId = $this->_helper->aviso->generarCompraAnuncio($rowAnuncio);
                $this->_helper->aviso->confirmarCompraAviso($compraId, 1);
                //Actualizando id membresia a Anuncio Web
                $objAW = new Application_Model_AnuncioWeb();
                $where = $objAW->getAdapter()->quoteInto('id_anuncio_impreso = ?',
                    $rowAnuncio['anuncioImpresoId']);
                $okUpdateP = $objAW->update(
                    array(
                    'prioridad' => $this->auth['empresa']['prioridad'],
                    'id_empresa_membresia' => $this->auth['empresa']['em_id']
                    ), $where
                );
                //Actualizando id membresia a Anuncio Impreso
                $objAI = new Application_Model_AnuncioImpreso();
                $where = $objAI->getAdapter()->quoteInto('id = ?',
                    $rowAnuncio['anuncioImpresoId']);
                $okUpdateP = $objAI->update(
                    array(
                    'id_empresa_membresia' => $this->auth['empresa']['em_id']
                    ), $where
                );

                $this->auth["anuncioImpreso"]["contratoSeleccionado"] = $contratoSeleccionado;
                $this->auth["anuncioImpreso"]["contratos"] = null;
                Zend_Auth::getInstance()->getStorage()->write($this->auth);
                $this->_redirect('/admin/comprar-aviso/pago-satisfactorio/compra/' . $compraId);
            }
        }

        if ($tieneContrato) {
            $rowAnuncio['totalPrecio'] = $contratoSeleccionado["MontoAPagar"];
            $rowAnuncio['tipoContrato'] = $contratoSeleccionado["ModalidadContrato"];
            $rowAnuncio['nroContrato'] = $contratoSeleccionado["NroContrato"];
        }

        switch ($this->_getParam('radioTipoPago')) {
            case 'pe':
                $rowAnuncio['tipoPago'] = Application_Model_Compra::FORMA_PAGO_PAGO_EFECTIVO;
                $compraId = $this->_helper->aviso->generarCompraAnuncio($rowAnuncio);
                $rowAnuncio['compraId'] = $compraId;
                $helper = $this->_helper->getHelper('WebServiceCip');
                $cip = $helper->generarCip($rowAnuncio);
                if ($cip['numero'] == "") {
                    $this->_helper->flashMessenger('Intente Nuevamente...');
                    $this->_redirect('/admin/' . $path . $idAnuncio); //cambiado
                }
                $where = $this->_compra->getAdapter()->quoteInto('id = ?',
                    $compraId);
                $okUpdateP = $this->_compra->update(
                    array(
                    'cip' => $cip['numero'],
                    'fh_expiracion_cip' => $cip['fechaExpiracion']
                    ), $where
                );
                $cadena = "cip=" . $cip['numero']
                    . "|capi=" . $this->getConfig()->configCip->capi
                    . "|cclave=" . $this->getConfig()->configCip->clave;
                $helper = $this->_helper->getHelper('WebServiceEncriptacion');
                $codigoBarras = $helper->encriptaCadena($cadena);
                $rowAnuncio['cip'] = $cip['numero'];
                $rowAnuncio['codigoBarras'] = $codigoBarras;
                $rowAnuncio['urlGeneraImagen'] =
                    $this->getConfig()->urlsComprarAviso->CIP->generaImagen;
                $sess = $this->getSession();
                $sess->rowAnuncio = $rowAnuncio;

                $rowCompra = $this->_compra->getDetalleCompraAnuncio($compraId);
                $usuario = $this->auth['usuario'];


                if ($rowCompra['tipoAnuncio'] == Application_Model_Compra::TIPO_SOLOWEB
                    ||
                    $rowCompra['tipoAnuncio'] == Application_Model_Compra::TIPO_CLASIFICADO) {

                    $tipo = ( Application_Model_Compra::TIPO_SOLOWEB ==
                        $rowCompra['tipoAnuncio'] ? '' : 'económico' );

                    $dataMail = array(
                        'to' => $usuario->email,
                        'usuario' => $usuario->email,
                        'nombre' => $rowCompra['nombreContacto'] . " " . $rowCompra['nombreContacto'],
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
                        $this->_helper->mail->confirmarVoucherPagoEfectivo($dataMail);
                    } catch (Exception $ex) {
                        $this->getMessenger()->error(
                            'Error al enviar el correo con los datos de la compra.'
                        );
                    }
                } else if ($rowCompra['tipoAnuncio'] == Application_Model_Compra::TIPO_PREFERENCIAL) {


                    $dataMail = array(
                        'to' => $rowCompra['emailContacto'],
                        'usuario' => $rowCompra['emailContacto'],
                        'titulo' => $rowCompra['tituloAnuncioImpreso'],
                        //ucfirst($rowCompra['anuncioClase'])." ".$rowCompra['tamanio'],
                        'nombre' => $rowCompra['nombreContacto'] . " " . $rowCompra['nombreContacto'],
                        'nroPuestos' => count($rowCompra['anunciosWeb']),
                        'razonSocial' => $rowCompra['nombre_comercial'],
                        'montoTotal' => $rowCompra['montoTotal'],
                        'medioPago' =>
                        ($rowCompra['medioPago'] == "pe" ? "Pago Efectivo" : $rowCompra['medioPago']
                            == "pe"),
                        'anuncioClase' => ucfirst($rowCompra['anuncioClase']),
                        'tipoAviso' => $rowCompra['tamanio'] . " (" . $rowCompra['tamanioCentimetros'] . " cm.)",
                        'productoNombre' => $rowCompra['productoNombre'],
                        'cip' => $rowCompra['cip']
                    );
                    try {
                        $this->_helper->mail->confirmarVoucherPagoEfectivoPreferencial($dataMail);
                    } catch (Exception $ex) {
                        $this->getMessenger()->error(
                            'Error al enviar el correo con los datos de la compra.'
                        );
                    }
                }

//                $sess->setExpirationHops(3, 'rowAnuncio');
                $this->_redirect('/admin/comprar-aviso/pago-efectivo/');
                break;
            case'visa':
                $compraId = $this->_helper->aviso->generarCompraAnuncio($rowAnuncio);
                $cadena = "OrderId=" . $compraId
                    . "|Amount=" . $rowAnuncio['totalPrecio']
                    . "|UserId=" . $rowAnuncio['usuarioId']
                    . "|UrlOk=" . $this->getConfig()->app->siteUrl
                    . "/empresa/comprar-aviso/ok"
                    . "|UrlError=" . $this->getConfig()->app->siteUrl
                    . "/empresa/" . $path //cambiado
                    . $rowAnuncio['anuncioId'] . "/error/1";
                $helper = $this->_helper->getHelper('WebServiceEncriptacion');
                $cadenaEnc = $helper->encriptaCadena($cadena);
                $this->_redirect(
                    $this->getConfig()->urlsComprarAviso->visa . "=" . $cadenaEnc . "&mp=v"
                );
                break;
            case'mc':
                $compraId = $this->_helper->aviso->generarCompraAnuncio($rowAnuncio);
                $cadena = "OrderId=" . $compraId
                    . "|Amount=" . $rowAnuncio['totalPrecio']
                    . "|UserId=" . $rowAnuncio['usuarioId']
                    . "|UrlOk=" . $this->getConfig()->app->siteUrl
                    . "/empresa/comprar-aviso/ok"
                    . "|UrlError=" . $this->getConfig()->app->siteUrl
                    . "/empresa/" . $path //cambiado
                    . $rowAnuncio['anuncioId'] . "/error/1";
                $helper = $this->_helper->getHelper('WebServiceEncriptacion');
                $cadenaEnc = $helper->encriptaCadena($cadena);
                $this->_redirect(
                    $this->getConfig()->urlsComprarAviso->visa . "=" . $cadenaEnc . "&mp=m"
                );
                break;
        }
    }

    public function generarPdfAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $domPdf = $this->_helper->getHelper('DomPdf');
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
        $rowCompra = $this->_compra->getDetalleCompraAnuncio($this->_getParam('compra'));
        $rowCompra['urlCodigoBarras'] = $this->getConfig()->urlsComprarAviso->CIP->generaImagen;
        $rowCompra['codEncriptado'] = $this->_getParam('codEncrip');
        $this->view->compra = $rowCompra;
        
        $nombre_file = 'pago-efectivo.pdf';
        if (isset($rowCompra['cip'])) {
            $nombre_file = $rowCompra['cip'].'.pdf';
        }
        
        $headLinkContainer = $this->view->headLink()->getContainer();
        unset($headLinkContainer[0]);
        unset($headLinkContainer[1]);

        $html = $this->view->render('comprar-aviso/imprimir-pago-efec.phtml');
        $domPdf->mostrarPDF($html, 'A4', "portrait", $nombre_file);
    }

    public function seleccionarContrato($contratos, $tipo)
    {
        $contratoSeleccionado = array();
        foreach ($contratos as $contra) {
            if ($contra["TipoContrato"] == $tipo)
                    $contratoSeleccionado = $contra;
        }
        return $contratoSeleccionado;
    }

}

