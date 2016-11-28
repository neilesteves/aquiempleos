<?php

class Postulante_ComprarPerfilController extends App_Controller_Action_Postulante {

    private $_compra;
    private $_perfil;
    private $_adecsysEnte;
    private $_compraAdecsysRuc;

    public function init() {

        $this->_compra = new Application_Model_Compra();
        $this->_perfil = new Application_Model_PerfilDestacado();
        $this->_adecsysEnte = new Application_Model_AdecsysEnte;
        $this->_compraAdecsysRuc = new Application_Model_CompraAdecsysRuc();
        $this->_config = Zend_Registry::get('config');
        parent::init();
    }

    public function pagarAction() {
        
        $this->_helper->viewRenderer->setNoRender();
               $this->_helper->layout->disableLayout();
        $data = $this->_getAllParams();
        //var_dump($data);exit;
        $pagoDestacado = new Zend_Session_Namespace('pago_destacado');
        if($pagoDestacado->token!=$data['token']||empty($pagoDestacado->token))
            $this->_redirect('/');
        else
            $pagoDestacado->token = md5(rand());
        $filter = new Zend_Filter_StripTags;

        //Prev. de XSS
        foreach ($data as $key => $value) {
            $data[$key] = $filter->filter($value);
        }

        //De qué pagina viene
        $path = $_SERVER['HTTP_REFERER'];
        $lenRuc = Application_Model_Compra::CARACTERES_RUC;

        //Si viene factura
        $tipoDoc = $data['radioTipoDoc'];
        $ruc = $data['txtRuc'];
        $rucAdecsys = $data['ente_ruc'];

        $pagoFactura = false;
        $rucExisteAdecsys = false;

        $sesionRUC = new Zend_Session_Namespace('pago_ruc');
        if (isset($sesionRUC->ente_ruc)) {

            unset($sesionRUC->ente_ruc);
            unset($sesionRUC->Tip_Doc);
            unset($sesionRUC->Num_Doc);
            unset($sesionRUC->RznSoc_Nombre);
            unset($sesionRUC->RznCom);
            unset($sesionRUC->Telf);
            unset($sesionRUC->Tip_Calle);
            unset($sesionRUC->Nom_Calle);
            unset($sesionRUC->Nom_Calle);
        }
     //   var_dump($data);exit;
        //Si el Ente ya existe en Adecsys
        if ($tipoDoc == Application_Model_Compra::PAGO_FACTURA && strlen($ruc) == $lenRuc && $rucAdecsys > 0) {
            $pagoFactura = true;
            $rucExisteAdecsys = true;
            //Ente ya existe en Adecsys
            $sesionRUC->ente_ruc = $rucAdecsys;
            $sesionRUC->Tip_Doc = Application_Model_Compra::RUC;
            $sesionRUC->Num_Doc = $ruc;
            $sesionRUC->RznSoc_Nombre = $data['txtName'];
            
        //Si el Ente no existe en Adecsys, se tendrá que registrar.
        } else if ($tipoDoc == Application_Model_Compra::PAGO_FACTURA && strlen($ruc) == $lenRuc && $rucAdecsys == 0) {

            $pagoFactura = true;
            $rucExisteAdecsys = false;
            
            //Nuevo ente
            $sesionRUC->ente_ruc = $rucAdecsys;
            $sesionRUC->Tip_Doc = Application_Model_Compra::RUC;
            $sesionRUC->Num_Doc = $ruc;
            $sesionRUC->RznSoc_Nombre = $data['txtSocialReason'];
            $sesionRUC->RznCom = $data['txtSocialReason'];
            $sesionRUC->Telf = $this->auth['postulante']['celular'];
            $sesionRUC->Tip_Calle = $data['selVia'];
            $sesionRUC->Nom_Calle = $data['txtLocation'];
            $sesionRUC->Num_Pta = $data['txtNroPuerta'];
        }

        $perfilDestacado = new App_Controller_Action_Helper_PerfilDestacado();
        $idPostulante = $this->auth['postulante']['id'];
        $dataPerfil = array(
            'id_postulante' => $idPostulante,
            'id_tarifa' => $data['id_tarifa'],
        );

        //Insertar el registro en la Tabla perfil_destacado
        $idPerfilDestacado = $perfilDestacado->_insertarPerfil($dataPerfil);

        $modelPerfil = new Application_Model_PerfilDestacado();
        $rowPerfil = $modelPerfil->getDatosGenerarCompra($idPerfilDestacado);
        
        //Obtener id de Adecsys_ente si el RUC ya existe en Adecsys
        $dataAE = null;
        if (isset($sesionRUC->ente_ruc)) {
            if ($sesionRUC->ente_ruc > 0) {
                $dataAE = $this->_adecsysEnte->obtenerPorCod($sesionRUC->ente_ruc);
            }
        }
        
        if (!is_null($dataAE)) {
            $rowPerfil['enteId'] = $dataAE['id'];
        }

        $rowPerfil['tipoDoc'] = $data['radioTipoDoc'];
        $rowPerfil['tipoPago'] = $data['radioTipoPago'];
        $usuario = $this->auth['usuario'];
        $rowPerfil['usuarioId'] = $usuario->id;

        if ($data['radioTipoPago'] == 'pe') {

            $rowPerfil['tipoPago'] = Application_Model_Compra::FORMA_PAGO_PAGO_EFECTIVO;
            $compraId = $perfilDestacado->generarCompraPerfil($rowPerfil);
            $rowPerfil['compraId'] = $compraId;
            $cip = $this->_helper->WebServiceCip->generarCipPerfil($rowPerfil);
        }
        
        //Solo cuando se paga por PE y es un RUC creará un registro la tabla compra_adecsys_ruc
        if ($data['radioTipoPago'] == 'pe' && $tipoDoc == Application_Model_Compra::PAGO_FACTURA) {
            //Inserta registro en compra_adecsys_ruc
            $sesionRUC->idUser = $this->auth['usuario']->id;
            $sesionRUC->compra = $compraId;
            $this->_compraAdecsysRuc->registrar($sesionRUC);   
        }
      
        switch ($data['radioTipoPago']) {
            case 'pe':
                $helper = $this->_helper->getHelper('WebServiceCip');
                $where = $this->_compra->getAdapter()->quoteInto('id = ?', $compraId);
                $this->_compra->update(array('token' => NULL), $where);
                if ($cip['numero'] == "") {
                    $this->getMessenger()->error('Intente nuevamente...');
                    $this->_redirect($path);
                }
                $where = $this->_compra->getAdapter()->quoteInto('id = ?', $compraId);
                $this->_compra->update(
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
                $rowPerfil['cip'] = $cip['numero'];
                $rowPerfil['codigoBarras'] = $codigoBarras;
                $rowPerfil['urlGeneraImagen'] = $this->getConfig()->urlsComprarAviso->CIP->generaImagen;

                $this->_compra->update(
                        array(
                    'cod_barra' => $codigoBarras
                        ), $where
                );

                $sess = $this->getSession();
                $sess->rowPerfil = $rowPerfil;

                $rowCompra = $this->_compra->getDetalleCompraPerfil($compraId);
                $usuario = $this->auth['usuario'];

                $cip= ltrim($rowPerfil['cip'], "0"); 

                $dataMail = array(
                    'to' => $usuario->email,
                    'usuario' => $usuario->email,
                    'nombre' => $rowCompra['nombreContacto'] . " " . $rowCompra['apePatContacto']. " " . $rowCompra['apeMatContacto'],
                    'anuncioPuesto' => $rowCompra['anuncioPuesto'],
                    'razonSocial' => $rowCompra['nombre_comercial'],
                    'montoTotal' => $rowCompra['montoTotal'],
                    'medioPago' =>( $rowCompra['medioPago']=='pe')?'Pago Efectivo':'',
                    'cip' =>  $cip,
                    'tipo' => 'Perfil Destacado'
                );

                try {
                    $this->_helper->mail->confirmarVoucherPagoEfectivoPerfil($dataMail);
                } catch (Exception $ex) {
                    $this->getMessenger()->error(
                            'Error al enviar el correo con los datos de la compra.' . $ex->getMessage()
                    );
                }

                $this->_redirect('/comprar-perfil/pago-efectivo');
                break;
            case'visa':
                $compraId = $perfilDestacado->generarCompraPerfil($rowPerfil);
                $sesionRUC->compra = $compraId;
                $this->_compraAdecsysRuc->registrar($sesionRUC);   
                $cadena = "OrderId=" . $compraId
                        . "|Amount=" . $rowPerfil['tarifaPrecio']
                        . "|UserId=" . $rowPerfil['usuarioId']
                        . "|UrlOk=" . $this->getConfig()->app->siteUrl
                        . "/comprar-perfil/ok"
                        . "|UrlError=" . $this->getConfig()->app->siteUrl.
                        "/perfil-destacado/paso2/tarifa/"  //cambiado                        
                        . $rowPerfil['tarifaId'] . "/error/1";
                $helper = $this->_helper->getHelper('WebServiceEncriptacion');
                $cadenaEnc = $helper->encriptaCadena($cadena);
                $this->_redirect($this->getConfig()->urlsComprarAviso->visa . "=" . $cadenaEnc . "&mp=v");
                break;
            case'mc':
                $compraId = $perfilDestacado->generarCompraPerfil($rowPerfil);
                $sesionRUC->compra = $compraId;
                $this->_compraAdecsysRuc->registrar($sesionRUC);  
                $cadena = "OrderId=" . $compraId
                        . "|Amount=" . $rowPerfil['tarifaPrecio']
                        . "|UserId=" . $rowPerfil['usuarioId']
                        . "|UrlOk=" . $this->getConfig()->app->siteUrl
                        . "/comprar-perfil/ok"
                        . "|UrlError=" . $this->getConfig()->app->siteUrl.
                        "/perfil-destacado/paso2/tarifa/"  //cambiado                        
                        . $rowPerfil['tarifaId'] . "/error/1";
                $helper = $this->_helper->getHelper('WebServiceEncriptacion');
                $cadenaEnc = $helper->encriptaCadena($cadena);
                $this->_redirect($this->getConfig()->urlsComprarAviso->visa . "=" . $cadenaEnc . "&mp=m");
                break;
        }
    }

    public function pagoEfectivoAction() {

        Zend_Layout::getMvcInstance()->assign(
                'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );

        $this->view->headLink()->appendStylesheet(
                $this->view->S('/css/empresa/empresa.layout.css'), 'all'
        );

        $this->view->headLink()->appendStylesheet(
                $this->view->S('/css/empresa/empresa.class.css'), 'all'
        );

        $sess = $this->getSession();
        $dataPerfil = $sess->rowPerfil;

      
          $dataPerfil['cip']=  ltrim($dataPerfil['cip'], "0"); 
        $idCompra = $dataPerfil['compraId'];
        $idPos = $this->auth['postulante']['id'];

        if ($this->_hasParam('id')) {
            $idCompra = $this->_getParam('id');
            //Valida sino es la compra del postunlante redirigir a home
            $dataPerfil = $this->_perfil->obtenerRegPerfilDestacadoDetalle($idPos, $idCompra);
            if (!$dataPerfil) {
                $this->getMessenger()->error('Acceso denegado');
                $this->_redirect('/mi-cuenta');
            }
            $dataPerfil['urlGeneraImagen'] = $this->getConfig()->urlsComprarAviso->CIP->generaImagen;
        }


        $fechaMax = $this->_compra->getFechaMaxPagoEfectivo($idCompra);
        $fechaMax = new Zend_Date($fechaMax);
        $this->view->perfil = $dataPerfil;
        $this->view->fhCierre = $fechaMax;
        $this->view->moneda = $this->_config->app->moneda;
        $this->view->igv = $this->_config->app->igv;
    }

    public function okPagoEfectivoAction() {
        
        Zend_Layout::getMvcInstance()->assign(
                'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );
        $cadenaEncriptada = $this->_getParam('datosEnc');
      
           try{
            $datos=array();
            $datos['encripta']=$cadenaEncriptada;
            $mongoPE = new Mongo_PeContingencia();
            $id= $mongoPE->save($datos);
            }catch(Exception $e){      
            }
        $helper = $this->_helper->getHelper('WebServiceEncriptacion');
        $cadenaDesEnc = $helper->desencriptaCadena($cadenaEncriptada);

        if (empty($cadenaDesEnc) && !isset($this->getConfig()->app->debug)) {
            throw new Zend_Exception('El valor a Desencriptar es Nulo', 500);
        }

        if (!empty($cadenaDesEnc)) {
            $arrayDatos = explode("|", $cadenaDesEnc);
            $compraId = substr($arrayDatos[1], 16);
        }

        if (isset($this->getConfig()->app->debug)) {
            $compraId = $cadenaEncriptada;
        }

        if (!$this->_compra->verificarPagado($compraId)) {
            $this->_helper->PerfilDestacado->confirmarCompraPerfil($compraId);
            try{
            $datos['fecha_edicion']=date('Y-m-d H:i:s');
            $datos['id_compra']=$compraId;
            $datos['_id']=$id;
            $mongoPE->save($datos);
            }catch(Exception $e){      
           }
        }
        
    }

    public function pagoSatisfactorioAction() {
        
        $id = $this->_getParam('compra', false);
        if (!$this->_helper->PerfilDestacado->perteneceCompraPostulante($id, $this->auth['postulante']['id'])) {
            throw new App_Exception_Permisos();
        }
        Zend_Layout::getMvcInstance()->assign(
                'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );
        $compraId = $this->_getParam('compra');
        $dataCompra = $this->_compra->getDetalleCompraPerfil($compraId);

        $fecInicio = new Zend_Date();
        $fecInicio->setLocale(Zend_Locale::ZFDEFAULT);
        $fecInicio->set($dataCompra['inicio']);
        $fecFin = new Zend_Date();
        $fecFin->setLocale(Zend_Locale::ZFDEFAULT);
        $fecFin->set($dataCompra['fin']);

        $inicio = $fecInicio->get(Zend_Date::DAY) . " " . ucfirst($fecInicio->get("MMMM")) . " " .
                $fecInicio->get(Zend_Date::YEAR);

        $fin = $fecFin->get(Zend_Date::DAY) . " " . ucfirst($fecFin->get("MMMM")) . " " .
                $fecFin->get(Zend_Date::YEAR);

        $storage = Zend_Auth::getInstance()->getStorage()->read();
        $storage['postulante']['destacado'] = 1;
        Zend_Auth::getInstance()->getStorage()->write($storage);
                        
        $this->view->compra = $dataCompra;
        $this->view->inicio = $inicio;
        $this->view->fin = $fin;
    }

    public function okAction() {

        $cadenaEncriptada = $this->_getParam('egp_data');
        $mongoPE = new Mongo_PeContingencia();
             try{
            $datos=array();
            $datos['encripta']=$cadenaEncriptada;
            $id= $mongoPE->save($datos);
             }catch(Exception $e){      
            }
        $helper = $this->_helper->getHelper('WebServiceEncriptacion');
        $cadenaDesEnc = $helper->desencriptaCadena($cadenaEncriptada);
        $arrayDatos = explode("|", $cadenaDesEnc);
        $compraId = substr($arrayDatos[1], 10);
        $token = substr($arrayDatos[0], 12);

        if (!$this->_compra->verificarPagado($compraId)) {
            $this->_helper->PerfilDestacado->confirmarCompraPerfil($compraId);
            $where = $this->_compra->getAdapter()->quoteInto('id = ?', $compraId);
            $this->_compra->update(array('token' => $token), $where);
            try{
            $datos['fecha_edicion']=date('Y-m-d H:i:s');
            $datos['id_compra']=$compraId;
            $datos['_id']=$id;
            $mongoPE->save($datos);
            }catch(Exception $e){      
           }
        }
        $this->_redirect('/comprar-perfil/pago-satisfactorio/compra/' . $compraId);
    }

    public function generarPdfAction() {

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
        $idCompra = $this->_getParam('compra');
        $rowCompra = $this->_compra->getDetalleCompraPerfil($idCompra);
        $rowCompra['urlCodigoBarras'] = $this->getConfig()->urlsComprarAviso->CIP->generaImagen;
        $rowCompra['codEncriptado'] = $this->_getParam('codEncrip');
        $this->view->compra = $rowCompra;
        
        $nombre_file = $rowCompra['cip'].'.pdf';

        $fechaMax = $this->_compra->getFechaMaxPagoEfectivo($idCompra);
        $fechaMax = new Zend_Date($fechaMax);

        $this->view->fhCierre = $fechaMax;
        $headLinkContainer = $this->view->headLink()->getContainer();
        unset($headLinkContainer[0]);
        unset($headLinkContainer[1]);

        $html = $this->view->render('comprar-perfil/imprimir-pago-efec.phtml');
        $domPdf->mostrarPDF($html, 'A4', "portrait", $nombre_file);
    }

}
