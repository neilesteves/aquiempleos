<?php
class Empresa_ComprarMembresiaAnualController extends App_Controller_Action_Empresa {
		private $_config ;
		private $_idMembresia;
		private $_idempresa;
		private $_tipoVia;

		protected $_avisoId;
		protected $_em;
		protected $_mebMem;
		protected $_EmpresaMembresia;
		protected $_peContingenciCompra;
		protected $_adecsysEnte;

		protected $_cache = null;
		private $_compra;
		const RUC = 'RUC';

	 public function init()
	 {
		  parent::init();
		  $this->_usuario = new Application_Model_Usuario();
		  if (isset($this->auth['usuario']->id) && $this->_usuario->hasvailBlokeo($this->auth['usuario']->id) ) {
				Zend_Auth::getInstance()->clearIdentity();
				Zend_Session::forgetMe();
				$this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
				$this->_redirect('/empresa');
		  }

			$this->_cache = Zend_Registry::get('cache');
			$this->_compra = new Application_Model_Compra();
			$this->_peContingenciCompra= new Application_Model_PeContingenciaCompra();
			$this->_EmpresaMembresia= new Application_Model_EmpresaMembresia();
			$this->_config = Zend_Registry::get('config');
		  if (isset($this->auth)) {
			  // var_dump($this->_getParam('MemBresia'));


			 $this->_adecsysEnte = new Application_Model_AdecsysEnte;

			  $this->_tarifa= new Application_Model_Tarifa();
			  $this->_meb= new Application_Model_Membresia();
			  $this->_tarifa = new Application_Model_Tarifa;
			  $this->_tipoVia = new Application_Model_TipoVia;
		  }
	 }


	 public function paso1Action()
	 {
		  $idmem = (int)$this->_getParam('membresia');
		  $membresiasPermitidas = array(
				Application_Model_Membresia::MENSUAL,
				Application_Model_Membresia::DIGITAL,
				Application_Model_Membresia::SELECTO,
				Application_Model_Membresia::PREMIUM
		  );

		  if (!in_array($idmem, $membresiasPermitidas)) {
				$this->_redirect('/');
		  }

		  $seoTitle = 'Membresía Anual - Pague su Membresía Anual, Publica tu aviso en aquiempleos.com';
		  $seoMeta = "Pague su Membresia Anual en aquiempleos.com.";
		  $textoMembresia = 'Compra de Membresía Anual';
		  if ($idmem == Application_Model_Membresia::DIGITAL || $idmem == Application_Model_Membresia::MENSUAL) {
				$seoTitle = 'Membresía - Pague su Membresía, Publica tu aviso en aquiempleos.com';
				$seoMeta = "Pague su Membresia en aquiempleos.com.";
				$textoMembresia = 'Compra de Membresía';
		  }

		  $this->view->headTitle()->set($seoTitle);
		  $this->view->headMeta()->appendName(
				"Description",
				$seoMeta.
				" Los Clasificados de Empleos de La Prensa."
		  );


		  Zend_Layout::getMvcInstance()->assign('bodyAttr',
				array('id' => 'perfilReg', 'class' => 'noMenu')
		  );



		  $this->_mebMem = $this->_meb->getMembresiaDetalleById($idmem, true,true);

		  if(!$this->_mebMem){
				 $this->_redirect('/');
		  }
		  $this->_mebMem['nombreEmpresa']=  $this->auth['empresa']['razon_social'];
		  $this->_mebMem['empresaRuc']=  $this->auth['empresa']['ruc'];
		  $this->view->mebMem =  $this->_mebMem;
			//Valida CI si existe en Adecsys

		  $validaRUC = $this->_helper->Aviso->validarDocumentoAdecsys(Application_Model_Compra::RUC,$this->_mebMem['empresaRuc']);
		  $formFactura= new Application_Form_FacturacionDatos();
		  $sessionDatosPasarela->factura=$validaRUC;
		  if (empty($validaRUC)) {
				$this->view->val_ruc =0;
				$this->view->ruc = $this->auth['empresa']['ruc'];
				$this->view->razonsocial = $this->auth['empresa']['razon_social'];
				$dataEmpresa['txtRuc']=$this->auth['empresa']['ruc'];
				$dataEmpresa['txtName']=$this->auth['empresa']['razon_social'];
				$formFactura->setDefaults($dataEmpresa);
				$formFactura->setreadonly($dataEmpresa);
		  } else {
				$formFactura->setDefaults($validaRUC);
				$formFactura->setreadonly($validaRUC);
		  }
		  $this->view->Formfacturacion=$formFactura;

		  $this->view->medioCompra =  isset($this->mebMem['medioPublicacion']) ? $this->mebMem['medioPublicacion'] : '';
		  $this->view->textoMembresia = $textoMembresia;


		  $pagoMembresia = new Zend_Session_Namespace('tokenMemprecia');
		  $pagoMembresia->tokenMemprecia = md5(rand());
		  $this->view->tokenMemprecia = $pagoMembresia->tokenMemprecia;
		  $this->view->moneda = $this->_config->app->moneda;
		  $this->view->igv = $this->_config->app->igv;

	 }

	 public function pagoEfectivoAction()
	 {

		  Zend_Layout::getMvcInstance()->assign(
					 'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
		  );
		  $this->view->headScript()->appendFile(
					 $this->view->S(
								'/js/empresa/empresa.misprocesos.js')
		  );


		  $sess = $this->getSession();
		  $dataMembresia = $sess->rowAnuncio;

		  $idCompra = $dataMembresia['compraId'];
		  $idEmpresa = $this->auth['empresa']['id'];

		  if ($this->_hasParam('id')) {
				$idCompra = $this->_getParam('id');
				//Valida sino es la compra de la membresia sino para  redirigir a home
				$dataMembresia = $this->_EmpresaMembresia->getDetalleEmpresaMembresiaByIdCompra($idCompra, $idEmpresa);
				if (!$dataMembresia) {
					 $this->getMessenger()->error('Acceso denegado');
					 $this->_redirect('/empresa/mi-cuenta');
				}
				$dataMembresia['urlGeneraImagen'] = $this->getConfig()->urlsComprarAviso->CIP->generaImagen;
		  }

		  $dataMembresia['cip']=  ltrim( $dataMembresia['cip'], "0");

		  $fechaMax = $this->_compra->getFechaMaxPagoEfectivo($idCompra);
		  $fechaMax = new Zend_Date($fechaMax);

		  $this->view->membresia = $dataMembresia;
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
					 $datos['fecha_creacion'] = date('Y-m-d H:i:s');
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
		 if (!$this->_compra->verificarPagado($compraId)) {
			$this->_helper->Membresia->confirmarCompraMembresia($compraId);
					 try{
					  $datos['fecha_edicion']=date('Y-m-d H:i:s');
					  $datos['id_compra']=$compraId;
					  $datos['_id']=$id;
					  $mongoPE = new Mongo_PeContingencia();
					  $mongoPE->save($datos);
					  }catch(Exception $e){
						  }
		  }
		 $dataMembresia = $this->_EmpresaMembresia->getMembresiaCompraDetalle($compraId);

	  $this->view->membresi=$dataMembresia;
	 }


	 public function pagoSatisfactorioAction()
	 {
		  $id = $this->_getParam('compra', false);
		  if (!$this->_helper->Aviso->perteneceCompraAEmpresa($id, $this->auth['empresa']['id'])) {
				throw new App_Exception_Permisos();
		  }$emp = new Application_Model_Empresa();
//        var_dump( $emp->getEmpresaMembresia(
//                            $this->auth['id']
//                    ));

//         if (!isset($this->auth['empresa']['membresia_info'])) {
//
//
//                  $this->auth['empresa']=$emp->getEmpresaMembresia(
//                            $this->auth['id']
//                    );
//        }
		  Zend_Layout::getMvcInstance()->assign(
					 'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
		  );
		  $compraId = $this->_getParam('compra');
		  $dataCompra = $this->_compra->getDetalleCompraMembresia($compraId);

		  $textoMembresia = 'Compra de Membresía Anual';
		  if ($dataCompra['idMembresia'] == Application_Model_Membresia::DIGITAL || $dataCompra['idMembresia'] == Application_Model_Membresia::MENSUAL) {
				$textoMembresia = 'Compra de Membresía';
		  }

		  // Actualizar en la session con el nombre de la memebresia ACTUAL y ACTIVA:
//        $auth = Zend_Auth::getInstance()->getStorage()->read();
//        var_dump($auth['empresa']['membresia_info']);
		  // Actualizar esta session:
		  // $auth['empresa']['membresia_info']['membresia']['id_membresia']


		  $contratoSeleccionado = null;
		  $tipoContrato = null;
		  $saldoFinal = null;
		  if (isset($this->auth["anuncioImpreso"])) {
				if ($this->auth["anuncioImpreso"]["id"] == $dataCompra["anuncioImpresoId"]) {
					 if (isset($this->auth["anuncioImpreso"]["contratoSeleccionado"])) {
						  $contratoSeleccionado = $this->auth["anuncioImpreso"]["contratoSeleccionado"];
						  if ($contratoSeleccionado["ModalidadContrato"] == Application_Model_Compra::TIPO_CONTRATO_MEMBRESIA)
								$tipoContrato = "Membresía";
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

		  $this->view->textoMembresia = $textoMembresia;
		  $this->view->compra = $dataCompra;
		  $this->view->tipoContrato = $tipoContrato;
		  $this->view->saldoFinal = $saldoFinal;
		  $this->view->moneda = $this->_config->app->moneda;
	 }

	  public function okAction() {

		  $cadenaEncriptada = $this->_getParam('egp_data');
				try{
				$datos=array();
				$datos['encripta']=$cadenaEncriptada;
				$datos['fecha_creacion'] = date('Y-m-d H:i:s');
				$mongoPE = new Mongo_PeContingencia();
				$id= $mongoPE->save($datos);
					  }catch(Exception $e){
						  }
		  $helper = $this->_helper->getHelper('WebServiceEncriptacion');
		  $cadenaDesEnc = $helper->desencriptaCadena($cadenaEncriptada);
		  $arrayDatos = explode("|", $cadenaDesEnc);
		  $compraId = substr($arrayDatos[1], 10);
		  $token = substr($arrayDatos[0], 12);

		  if (!$this->_compra->verificarPagado($compraId)) {
			  $where = $this->_compra->getAdapter()->quoteInto('id = ?', $compraId);
			  $this->_compra->update(array(
					'token' =>$token
						 ), $where);
			  $this->_helper->Membresia->confirmarCompraMembresia($compraId);

			  try {
				$datos['fecha_edicion']=date('Y-m-d H:i:s');
				$datos['id_compra']=$compraId;
				$datos['_id']=$id;
				$mongoPE = new Mongo_PeContingencia();
				$mongoPE->save($datos);
			  }catch(Exception $e){
			  }
		  }

		  $this->_redirect('/empresa/comprar-membresia-anual/pago-satisfactorio/compra/' . $compraId);
	 }

	 public function pagoMembresiaAction()
	 {
		  $pagoMembresia = new Zend_Session_Namespace('tokenMemprecia');
		  $CompraAdecsysRuc= new Application_Model_CompraAdecsysRuc();
		  $allParams = $this->_getAllParams();
		  $idmem = $allParams['MemBresia'];
		  $rowMembresia = array();
		  $path = $_SERVER['HTTP_REFERER'];

		  if ($allParams['token']==$pagoMembresia->tokenMemprecia) {
			 $pagoMembresia->tokenMemprecia =  md5(rand());
		  } else {
			 $this->_redirect("/empresa/comprar-membresia-anual/paso1/membresia/$idmem");
		  }


		  $filter = new Zend_Filter_StripTags;

		  //Prev. de XSS
		  foreach ($allParams as $key => $value) {
				$allParams[$key] = $filter->filter($value);
		  }


		  //De qué pagina viene


			 $validaRUC = $this->_helper->Membresia->validarDocumentoAdecsys(Application_Model_Compra::RUC,  $allParams['txtRuc']);


		 if (empty($validaRUC)) {
				$rowsAdecsyruc['ruc']=$allParams['txtRuc'];
				$rowsAdecsyruc['razon_social']=$allParams['txtName'];
				$rowsAdecsyruc['tipo_via']=$allParams['selVia'];
				$rowsAdecsyruc['direccion']=$allParams['txtLocation'];
				$rowsAdecsyruc['nro_puerta']=$allParams['txtNroPuerta'];
				$rowsAdecsyruc['creado_por']=$this->auth['usuario']->id;
		  }else{
				$rowsAdecsyruc['ruc']=$validaRUC->Num_Doc;
				$rowsAdecsyruc['razon_social']=$validaRUC->RznSoc_Nombre;
				$rowsAdecsyruc['tipo_via']=$validaRUC->Nom_Calle;;
				$rowsAdecsyruc['direccion']=$validaRUC->Nom_Calle;
				$rowsAdecsyruc['creado_por']=$this->auth['usuario']->id;
		  }

		  $this->_mebMem = $this->_meb->getMembresiaDetalleById($idmem);
		  //suma de fechas

				//Obtener id de Adecsys_ente si el RUC ya existe en Adecsys


		  $fechainicio = date('Y-m-d H:i:s');
		  $fechafin = strtotime ('+1 year' , strtotime ( $fechainicio ) ) ;
		  $fechafin = date ( 'Y-m-d H:i:s' , $fechafin );

		  $this->_mebMem['nombreEmpresa']=  $this->auth['empresa']['razon_social'];
		  $this->_mebMem['empresaRuc']=  $this->auth['empresa']['ruc'];
		  //agregando parametros para la membresia
		  $this->_mebMem['txtfecini']= $fechainicio;
		  $this->_mebMem['idmembresia']= $this->_mebMem['id'];
		  $this->_mebMem['txtfecfin']=$fechafin  ;
		  $this->_mebMem['txtmonto']= $this->_mebMem['tarifaPrecio'];
		  $this->_mebMem['idEmpresa']=$this->auth['empresa']['id'] ;
		  $this->_mebMem['cboestado']= 'no vigente';

		  $idEmMem= $this->saveMembresiaEmpresa($this->_mebMem);
		  $this->_mebMem['idEmMem'] = $idEmMem;


		  $rowMembresia = $this->_mebMem;

		  $cod_subseccion = isset($rowMembresia['anunciosWeb']['0']['cod_subseccion']) ? $rowMembresia['anunciosWeb']['0']['cod_subseccion'] : NULL;
		  $rowMembresia['tipoDoc'] = $allParams['radioTipoDoc'];
		  $rowMembresia['tipoPago'] = $allParams['radioTipoPago'];
		  $usuario = $this->auth['usuario'];
		  $rowMembresia['usuarioId'] = $usuario->id;
		  $rowMembresia['totalPrecio'] = str_replace(',', '', $rowMembresia['txtmonto']);
		  $rowMembresia['tarifaPrecio']=$rowMembresia['totalPrecio'];
		  $rowMembresia['empresaRazonSocial']=$allParams['txtName'];
		  //Actualizamos el dato prioridad en la tabla anuncio_web
		  $tieneContrato = false;
		  $precioContrato = null;
		  $contratoSeleccionado = null;
		  $tipoContrato = null;


		  //pregunta el ente
		  $dataAE = null;
		  if (isset($allParams['txtRuc'])) {
				if ($allParams['txtRuc'] > 0) {
					 $dataAE = $this->_adecsysEnte->obtenerPorDocumento($allParams['txtRuc']);
				}
		  }

		  if (!is_null($dataAE)) {
				$rowMembresia['enteId'] = $dataAE['id'];
		  }else{
				$rowMembresia['enteId'] = '';

		  }
		  if ($tieneContrato) {
				$rowMembresia['totalPrecio'] = $contratoSeleccionado["MontoAPagar"];
				$rowMembresia['tipoContrato'] = $contratoSeleccionado["ModalidadContrato"];
				$rowMembresia['nroContrato'] = $contratoSeleccionado["NroContrato"];
		  }


		  $idImpreso = null;


		  $arrayCip=null;

		  $rowMembresia['totalPrecio']=round(($this->_mebMem['txtmonto']*$this->_config->adecsys->igv)+$this->_mebMem['txtmonto'],2);
		  //var_dump($rowMembresia['totalPrecio']); exit;

				if ($arrayCip == null && $this->_getParam('radioTipoPago') == 'pe') {
				$rowMembresia['tipoPago'] = Application_Model_Compra::FORMA_PAGO_PAGO_EFECTIVO;
				$tarifa= $this->_tarifa->getTarifaMembresia($rowMembresia['tipo'],$rowMembresia['nombreProducto']);
				$rowMembresia['tarifaId'] =$tarifa['id_tarifa'];
				$rowMembresia['medioPublicacion']=$tarifa['medio_publicacion'];
				$rowMembresia['empresaId']=$rowMembresia['idEmpresa'];
				$rowMembresia['empresaMail']=$this->auth['usuario']->email;
				$rowMembresia['tarifaPrecioBase'] = $rowMembresia['totalPrecio'];


				$compraId = $this->_helper->Membresia->generarCompraMembresia($rowMembresia);
				$rowsAdecsyruc['id_compra']=$compraId;
				$CompraAdecsysRuc->registrarCompraMembresia($rowsAdecsyruc);
				$rowMembresia['compraId'] = $compraId;
				$cip = $this->_helper->WebServiceCip->generarCipCompraMembresia($rowMembresia);

		  }

		  switch ($this->_getParam('radioTipoPago')) {

				case 'pe':

					 $helper = $this->_helper->getHelper('WebServiceCip');

					 if ($cip['numero'] == "") {
						  $this->_helper->flashMessenger('Intente Nuevamente...');
						  $this->_redirect('/empresa/comprar-membresia-anual/paso1/membresia/' . $idmem); //cambiado
					 }

					 $whereCompra = $this->_compra->getAdapter()->quoteInto('id = ?', $compraId);
					 $okUpdateP = $this->_compra->update(
						  array(
								'cip' => $cip['numero'],
								'fh_expiracion_cip' => $cip['fechaExpiracion']
						  ),
						  $whereCompra
					 );

					 $whereEM = $this->_EmpresaMembresia->getAdapter()->quoteInto('id = ?', $idEmMem);
					 $okUpdateEp = $this->_EmpresaMembresia->update(
						  array(
								'estado' => 'por pagar',
						  ),
						  $whereEM
					 );

					 $cadena = "cip=" . $cip['numero']
								. "|capi=" . $this->getConfig()->configCip->capi
								. "|cclave=" . $this->getConfig()->configCip->clave;
					 $helper = $this->_helper->getHelper('WebServiceEncriptacion');
					 $codigoBarras = $helper->encriptaCadena($cadena);
					 $rowMembresia['cip'] = $cip['numero'];
					 $rowMembresia['codigoBarras'] = $codigoBarras;
					 $rowMembresia['urlGeneraImagen'] =
								$this->getConfig()->urlsComprarAviso->CIP->generaImagen;


					 $this->_compra->update(
								array(
									 'cod_barra' => $codigoBarras
								), $whereCompra
					 );


					 //$rowCompra = $this->_compra->getDetalleCompraMembresia($compraId);

					 $usuario = $this->auth['usuario'];
					 //$rowCompra['tipoProducto']  = $rowMembresia['tipo'];

					 $sess = $this->getSession();
					 $sess->rowAnuncio = $rowMembresia;

					 $this->_redirect('/empresa/comprar-membresia-anual/pago-efectivo/');
					 break;

				case'visa':

					 $rowMembresia['tipoPago'] = Application_Model_Compra::FORMA_PAGO_VISA;
//            var_dump($rowMembresia['tipo'],$rowMembresia['nombreProducto']);exit;
					 $tarifa= $this->_tarifa->getTarifaMembresia($rowMembresia['tipo'],$rowMembresia['nombreProducto']);
			  //
					 $rowMembresia['tarifaId'] =$tarifa['id_tarifa'];
					 $rowMembresia['medioPublicacion']=$tarifa['medio_publicacion'];
					 $rowMembresia['empresaId']=$rowMembresia['idEmpresa'];
					 $rowMembresia['empresaMail']=$this->auth['usuario']->email;

					 $compraId = $this->_helper->Membresia->generarCompraMembresia($rowMembresia);
					 $rowsAdecsyruc['id_compra']=$compraId;

					 $CompraAdecsysRuc->registrarCompraMembresia($rowsAdecsyruc);
					 $cadena = "OrderId=" . $compraId
								. "|Amount=" . $rowMembresia['totalPrecio']
								. "|UserId=" . $rowMembresia['usuarioId']
								. "|UrlOk=" . $this->getConfig()->app->siteUrl
								. "/empresa/comprar-membresia-anual/ok"
								. "|UrlError=" .$this->getConfig()->app->siteUrl.
								"/empresa/comprar-membresia-anual/paso1/membresia/"  //cambiado
								. $idmem . "/error/1";


					 $helper = $this->_helper->getHelper('WebServiceEncriptacion');
					 $cadenaEnc = $helper->encriptaCadena($cadena);
					 $this->_redirect($this->getConfig()->urlsComprarAviso->visa . "=" . $cadenaEnc . "&mp=v");
					 break;

				case'mc':

					 $rowMembresia['tipoPago'] = Application_Model_Compra::FORMA_PAGO_MASTER_CARD;
			 //  var_dump($rowMembresia['tipo'],$rowMembresia['nombreProducto']);exit;
					 $tarifa= $this->_tarifa->getTarifaMembresia($rowMembresia['tipo'],$rowMembresia['nombreProducto']);
			  //
					 $rowMembresia['tarifaId'] =$tarifa['id_tarifa'];
					 $rowMembresia['medioPublicacion']=$tarifa['medio_publicacion'];
					 $rowMembresia['empresaId']=$rowMembresia['idEmpresa'];
					 $rowMembresia['empresaMail']=$this->auth['usuario']->email;
					 $compraId = $this->_helper->Membresia->generarCompraMembresia($rowMembresia);
					 $rowsAdecsyruc['id_compra']=$compraId;
					 $CompraAdecsysRuc->registrarCompraMembresia($rowsAdecsyruc);
					 $cadena = "OrderId=" . $compraId
								. "|Amount=" . $rowMembresia['totalPrecio']
								. "|UserId=" . $rowMembresia['usuarioId']
								. "|UrlOk=" . $this->getConfig()->app->siteUrl
								. "/empresa/comprar-membresia-anual/ok"
								. "|UrlError=" . $this->getConfig()->app->siteUrl.
								"/empresa/comprar-membresia-anual/paso1/membresia/"  //cambiado
								. $idmem . "/error/1"; //cambiado  ;
					 $helper = $this->_helper->getHelper('WebServiceEncriptacion');
					 $cadenaEnc = $helper->encriptaCadena($cadena);
					 $this->_redirect($this->getConfig()->urlsComprarAviso->visa . "=" . $cadenaEnc . "&mp=m");
					 break;
		  }

		  exit(0);

	 }


	 public function saveMembresiaEmpresa($data = '')
	 {
		  $date = date('Y-m-d H:i:s');

		  try {


				$modelEmpresa = new Application_Model_Empresa();
				$modelApi = new Application_Model_Api();
				$idM = $data['id'];
				$idMembresia= $idM ;
				$empMem=  $this->_EmpresaMembresia->getEmpresaMemSig($data['idEmpresa']);

					 $duracionMeses = $this->_config->membresias->digital->duracion;

					 if(count($empMem)>0){
						  $fechainicio = $empMem[0]['fh_fin'];
						  //Verifica si es empresa com membresía DIGITAL
						  if ($idMembresia == Application_Model_Membresia::DIGITAL) {
								$fechafin = strtotime ('+'.$duracionMeses.' month' , strtotime ( $fechainicio ) ) ;
						  } else if ($idMembresia == Application_Model_Membresia::MENSUAL) {
								$fechafin = strtotime ('+1 month' , strtotime ( $fechainicio ) ) ;
						  } else {
								$fechafin = strtotime ('+1 year' , strtotime ( $fechainicio ) ) ;
						  }

						  $fechafin = date ( 'Y-m-d H:i:s' , $fechafin );
					 }else {
						  $fechainicio = date('Y-m-d H:i:s');
						  //Verifica si es empresa com membresía DIGITAL
						  if ($idMembresia == Application_Model_Membresia::DIGITAL) {
								$fechafin = strtotime ('+'.$duracionMeses.' month' , strtotime ( $fechainicio ) ) ;
						  } else if ($idMembresia == Application_Model_Membresia::MENSUAL) {
								$fechafin = strtotime ('+1 month' , strtotime ( $fechainicio ) ) ;
						  } else {
								$fechafin = strtotime ('+1 year' , strtotime ( $fechainicio ) ) ;
						  }
						  $fechafin = date ( 'Y-m-d H:i:s' , $fechafin );
					 }

						  $feciniM =  $fechainicio;
						  $fecfinM =  $fechafin;

						  $mntoM = str_replace(',', '', $data['txtmonto']);

						  $objfecIni = new Zend_Date($feciniM);
						  $objfecFin = new Zend_Date($fecfinM);

						  $objEmpMemb = new Application_Model_EmpresaMembresia();

						  $idEM = $objEmpMemb->insert(
								array(
									 'id_empresa' => $data['idEmpresa'],
									 'id_membresia' => $idM,
									 'fh_inicio_membresia' => $objfecIni->toString('yyyy-MM-dd HH:mm:ss'),
									 'fh_fin_membresia' => $objfecFin->toString('yyyy-MM-dd HH:mm:ss'),
									 'creado_por' => $this->auth['usuario']->id,
									 'fh_creacion' => $date,
									 //'modificado_por'=>$this->auth['usuario']->id,
									 //'fh_modificacion'=>$date,
									 'monto' => $mntoM,
									 'estado' => $data['cboestado']
								)
						  );

						  $objMemEmpDet = new Application_Model_MembresiaEmpresaDetalle();
						  $objMemDet = new Application_Model_MembresiaDetalle();
						  @$this->_cache->remove('MembresiaDetalle_getDetalleByMembresia' . $idM);
						  $rsMD = $objMemDet->getDetalleByMembresiaPago($idM);

						  foreach ($rsMD as $key => $value) {
								$objMemEmpDet->insert(
									 array(
										  'id_empresa_membresia' => $idEM,
										  'id_membresia' => $idM,
										  'id_beneficio' => $value['id_beneficio'],
										  'codigo' => $value['codigo'],
										  'nombre' => $value['nombre'],
										  'descripcion' => $value['desc'],
										  'valor' => $value['valor'],
										  'tipo_beneficio' => $value['tipo_beneficio'],
										  'fh_creacion' => $date,
										  'creado_por' => $this->auth['usuario']->id
									 )
								);
						  }

						  $arrayEmp = $modelEmpresa->getEmpresa($data['idEmpresa']);
						  $arrayApi = $modelApi->getDatosByIdEmpresa($data['idEmpresa']);

						  $dataPost = array(
								'force_domain' => isset($arrayApi['id']) ? $arrayApi['force_domain']
										  : null,
								'domain' => isset($arrayApi['id']) ? $arrayApi['domain']
										  : null,
								'fecha_ini' => $objfecIni->toString('yyyy-MM-dd HH:mm:ss'),
								'fecha_fin' => $objfecFin->toString('yyyy-MM-dd HH:mm:ss'),
								'vigencia' => '1',
								'usuario' => $arrayEmp['email'],
								'idempresa' => $data['idEmpresa'],
								'estado' => 'vigente'
						  );

						  if ($data['cboestado'] != Application_Model_Membresia::TIPO_ESTADO_VIGENTE) {
							 //  $dataPost['estado'] = 'dadobaja';
						  }
						  $bolsaCv = new Application_Model_BolsaCv();

						  $bolsaCv->createGrupoGeneral($data['idEmpresa']);
						  if ($idM == Application_Model_Membresia::PREMIUM || $idM == 6 || $idM == Application_Model_Membresia::SELECTO ) {

								if ($dataPost['estado'] == 'vigente') {
									 $bolsaCv->createGrupoTcn($data['idEmpresa']);
								}

								if (!isset($arrayApi['id'])) {
								  //  $this->_helper->Api->insertarUsuario($dataPost);
								} else {
									 $dataPost['idUsuApi'] = $arrayApi['id'];
									// $this->_helper->Api->actualizarUsuario($dataPost);

								}
						  }

						  return $idEM ;




				/* $db->commit();
				  $this->getMessenger()->success('Se cambiaron los datos con éxito.'); */
		  } catch (Zend_Db_Exception $e) {
				echo $e->getMessage();

		  } catch (Zend_Exception $e) {
				$this->getMessenger()->error($this->_messageSuccess);
				echo $e->getMessage();
		  }
	 }


 public function validaRucAdecsysAction() {

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
				$ruc = $filter->filter($data['ruc']);

				//Validación de Token

				//WS para validar la existencia del ente con ese RUC en Adecsys
				$validaRUC = $this->_helper->Membresia->validarDocumentoAdecsys(self::RUC, $ruc);
				$enteId = $validaRUC->Id;
				$nombreEmpresa = $validaRUC->RznSoc_Nombre;
				$tipoVia = $validaRUC->Tip_Calle;
				$direccion = $validaRUC->Tip_Calle . " " . $validaRUC->Nom_Calle . " " . $validaRUC->Num_Pta;

				$dataEmpresa = array(
					 'id' => $enteId,
					 'nombreEmpresa' => $nombreEmpresa,
					 'via' => $tipoVia,
					 'dir' => $direccion
				);

				if (is_null($validaRUC)) {
					 $dataEmpresa['id'] = 0;
					 $dataEmpresa['success'] = 0;
					 $dataEmpresa['msg'] = 'No existe en Adecsys';
					 echo Zend_Json::encode($dataEmpresa);
				} else {
					 $dataEmpresa['success'] = 1;
					 $dataEmpresa['msg'] = 'Ya está registrado en Adecsys';
					 echo Zend_Json::encode($dataEmpresa);
				}
		  //}
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

		  $idCompra = $this->_getParam('compra');

		  $rowCompra = $this->_compra->getDetalleCompraMembresia($idCompra);
		  $rowCompra['urlCodigoBarras'] = $this->getConfig()->urlsComprarAviso->CIP->generaImagen;
		  $rowCompra['codEncriptado'] = $this->_getParam('codEncrip');
		  $this->view->compra = $rowCompra;

		  $nombre_file = 'pago-efectivo.pdf';
		  if (isset($rowCompra['cip'])) {
				$nombre_file = $rowCompra['cip'].'.pdf';
		  }

		  $fechaMax = $this->_compra->getFechaMaxPagoEfectivo($idCompra);
		  $fechaMax = new Zend_Date($fechaMax);

		  $this->view->fhCierre = $fechaMax;
		  $headLinkContainer = $this->view->headLink()->getContainer();
		  unset($headLinkContainer[0]);
		  unset($headLinkContainer[1]);

		  $html = $this->view->render('comprar-membresia-anual/imprimir-pago-efec.phtml');
		  $domPdf->mostrarPDF($html, 'A4', "portrait", $nombre_file);
	 }


}
