<?php

class Empresa_PublicaAvisoPreferencialController extends App_Controller_Action_Empresa {

	 protected $_avisoId;
	 protected $_datosAviso;
	 protected $_cache = null;
	 protected $_idMembresia;
	 protected $_messageSuccess = 'El aviso se agregó con éxito';
	 protected $_empresa;

	 //Buscamas
	 private $_buscamasConsumerKey;
	 private $_buscamasPublishUrl;
	 private $_buscamasUrl;

	 public function init() {
		  parent::init();
		  Zend_Layout::getMvcInstance()->assign(
					 'bodyAttr', array('id' => 'myAccount', 'class' => '')
		  );
		  $this->_usuario = new Application_Model_Usuario();
		  if (isset($this->auth['usuario']->id) && $this->_usuario->hasvailBlokeo($this->auth['usuario']->id) ) {
				Zend_Auth::getInstance()->clearIdentity();
				Zend_Session::forgetMe();
				$this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
				$this->_redirect('/empresa');
		  }
		  $this->view->headMeta()->appendName(
					 "Keywords", "elige tu aviso, publica tu aviso, aviso en AquiEmpleos, " .
					 "pasos para publicar, Perfil del puesto, " .
					 "Complete su aviso Impreso, Pague su Aviso"
		  );
		  $this->view->empresaId = isset($this->auth['empresa']) ? $this->auth['empresa']['id'] : null;

		  $this->_empresa = new Application_Model_Empresa;

		  $this->_config = Zend_Registry::get('config');
		  //Buscamas
		  $this->_buscamasConsumerKey = $this->_config->apis->buscamas->consumerKey;
		  $this->_buscamasUrl = $this->_config->apis->buscamas->url;
		  $this->_buscamasPublishUrl = $this->_config->apis->buscamas->publishUrl;

		  $anuncioId = null;
		  if (!is_null($this->_getParam('republica', null)))
				$anuncioId = $this->_getParam('republica');

		  if (!is_null($this->_getParam('extiende', null)))
				$anuncioId = $this->_getParam('extiende');

		  if (!is_null($anuncioId)) {
				$anuncio = new App_Service_Validate_Ad($anuncioId);
				$usuarioEmpresa = $this->auth['usuario-empresa'];

				if (!$anuncio->isManaged($usuarioEmpresa)) {
					 $this->getMessenger()->error($anuncio->getMessage());
					 $this->_redirect('/empresa/mi-cuenta');
				}
		  }

		  $this->_cache = Zend_Registry::get('cache');

		  if (isset($this->auth['empresa'])) {
				$this->_idMembresia = $this->auth['empresa']['em_id'];
		  } else {
				$this->_idMembresia = null;
		  }

	 }

	 public function indexAction() {
		  $this->view->menu_sel = self::MENU_MI_CUENTA;
		  $this->view->menu_post_sel = self::MENU_POST_PUBLICA_AVISO;
		  $this->_redirect('empresa/publica-aviso-preferencial/paso1');
	 }

	 public function paso1Action() {
		  $config = Zend_Registry::get("config");
		  $fchPubsImp['aptitus'] = $this->_helper->Aviso->getFechaPublicacionImpresoByPaquete('aptitus');
		  $fchPubsImp['talan'] = $this->_helper->Aviso->getFechaPublicacionImpresoByPaquete('talan');
		  $fchPubsImp['combo'] = $this->_helper->Aviso->getFechaPublicacionImpresoByPaquete('combo');

		  $this->view->fchPubsImp = $fchPubsImp;

		  $fchCierreImp['aptitus'] = $this->_helper->Aviso->getFechaCierreImpresoByPaquete('aptitus');
		  $fchCierreImp['talan'] = $this->_helper->Aviso->getFechaCierreImpresoByPaquete('talan');
		  $fchCierreImp['combo'] = $this->_helper->Aviso->getFechaCierreImpresoByPaquete('combo');

		  $this->view->fchCierreImp = $fchCierreImp;


		  Zend_Layout::getMvcInstance()->assign(
					 'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
		  );
		  $this->view->headTitle()->set(
					 'Paso 1 - Elige tu aviso, Publica tu aviso | AquiEmpleos'
		  );
		  $this->view->headMeta()->appendName(
					 "Description", "Elige tu aviso, primer paso para la publicación de tu aviso en aquiempleos.com." .
					 " Los Clasificados de Empleos de La Prensa."
		  );

		  $extiende = $this->_getParam('extiende', null);
		  if (isset($extiende)) {
				$this->view->extiende = $extiende;
		  }

		  $republica = $this->_getParam('republica', null);
		  if (isset($republica)) {
				$this->getMessenger()->error('No esta permitido republicar un aviso.');
				$this->_redirect($_SERVER['HTTP_REFERER']);
				$this->view->republica = $republica;
		  }

		  $modelProducto = new Application_Model_Producto();

		  for ($i = 1; $i < 4; $i++) {
				$idProd = 5 + $i;
				if (is_null($this->_idMembresia))
					 $arrayPreferencial[] = $modelProducto->getInformacionAvisoPreferencialSinMembresia($idProd);
				else
					 $arrayPreferencial[] = $modelProducto->getInformacionAvisoPreferencial($idProd);
		  }
		  $this->view->arrayPreferencial = $arrayPreferencial;

		  $formAvisoPref = new Application_Form_Paso1AvisoPreferencial();
		  $allparams = $this->_getAllParams();

		  if (isset($this->auth) || isset($allparams['idProd'])) {
				$this->view->headScript()->appendFile(
						  $this->view->S(
									 '/js/empresa/empresa.aviso.paso1.js')
				);

		  } else {

				$id = null;
				$formRegistroRapido = new Application_Form_RegistroRapido(null);
				$formRegistroRapido->validadorEmail($id);
				$formRegistroRapido->validadorRuc($id);
				$formRegistroRapido->validadorRazonSocial($id);

				if ($this->getRequest()->isPost()) {
					 $dataPost = $this->_getAllParams();
					 $this->_redirect(
								'/empresa/publica-aviso-preferencial/paso2/tarifa/' .
								$dataPost['id_tarifa']
					 );
				}
				$mvc = Zend_Layout::getMvcInstance();
				$mvc->loginForm->return->setValue('/empresa/publica-aviso-preferencial/paso2');
				$this->view->slide = $this->_getParam('slide', 1);
				$this->view->headScript()->appendFile(
						  $this->view->S(
									 '/js/empresa/empresa.aviso.paso1.js')
				);

				Zend_Layout::getMvcInstance()->assign(
						  'registrorapido', $formRegistroRapido
				);
		  }

		  $this->view->formAvisoPref = $formAvisoPref;
		  $this->view->moneda = $config->app->moneda;
	 }

	 public function grillaPreciosAction() {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();
		  $allparams = $this->_getAllParams();

		  $idProd = $allparams['idProd'];
		  $arrayArmado = $this->_helper->AvisoPreferencial->getGrillaByProducto($idProd);

		  echo(Zend_Json::encode($arrayArmado));
	 }

	 public function paso2Action() {
		  $session = $this->getSession();
		  $t = new Application_Model_Tarifa();
		  $aviso = new Application_Model_AnuncioWeb();
		  $anuncioImpreso = new Application_Model_AnuncioImpreso();

		  $puestoOtherId = Application_Model_Puesto::OTROS_PUESTO_ID;
		  $config = Zend_Registry::get("config");
		  // @codingStandardsIgnoreStart
		  $this->view->numPalabraPuesto = $config->avisopaso2->puestonumeropalabra;
		  $this->view->numPalabraOtroNombre = $config->avisopaso2->mostrarnombrenumeropalabra;
		  //@codingStandardsIgnoreEnd
		  $redirect = $this->_getParam('redirctGua', null);
		  if ($this->_getParam('aviso') != "") {
				$avisoId = $this->_getParam('aviso', false);
				if (!$this->_helper->Aviso->perteneceAvisoAEmpresa($avisoId, $this->auth['empresa']['id'])) {
					 throw new App_Exception_Permisos();
				}
		  }

		  if ($this->_getParam('preferencial') != "") {
				$preferencial = $this->_getParam('preferencial');
		  }

		  if ($this->_getParam('extiende') != "") {
				$extiende = $this->_getParam('extiende');
		  }

		  if ($this->_getParam('republica') != "") {
				$republica = $this->_getParam('republica');
		  }

		  if ($this->_getParam('abrir') != "") {
				$preferencial = $this->_getParam('abrir');
				$data = $aviso->getPosicionByAvisoPreferencial($preferencial);
				$ai = $anuncioImpreso->getDataAnuncioImpreso($preferencial);
				$tarifaId = $ai['id_tarifa'];
				$maximoAnuncios = $t->getNumeroAvisoMaximoByPreferencial($tarifaId);

				if ($data['totalReady'] < $maximoAnuncios) {
					 $this->_redirect('/empresa/publica-aviso-preferencial/paso2/preferencial/' . $data['anuncioImpreso']);
				} else {
					 $this->_redirect(
								'/empresa/publica-aviso-preferencial/paso2/aviso/' . $data['data'][$data['totalReady'] - 1]['id']
					 );
				}
		  }

		  if ($this->_getParam('id_tarifa') != null) {
				$tarifaId = $this->_getParam('id_tarifa');
		  } elseif ($this->_getParam('tarifa') != null) {
				$tarifaId = $this->_getParam('tarifa');
		  } elseif ($this->_getParam('aviso') != "") {
				$datosAviso = $aviso->getFullAvisoById($avisoId);
				$ai = $anuncioImpreso->getDataAnuncioImpreso($datosAviso['id_anuncio_impreso']);
				$tarifaId = $ai['id_tarifa'];
		  } elseif (isset($preferencial) && $preferencial != null) {
				$ai = $anuncioImpreso->getDataAnuncioImpreso($preferencial);
				$tarifaId = $ai['id_tarifa'];
		  }
		  if (isset($tarifaId)) {
				$tarifaModel = new Application_Model_Tarifa();
				$productoData = $tarifaModel->getProductoByTarifa($tarifaId);
				$arrayArmado = $this->_helper->AvisoPreferencial->getGrillaByProducto($productoData['id_producto']);
				foreach ($arrayArmado['id'] as $key => $value) {
					 foreach ($value as $fila => $item) {
						  if ($item == $tarifaId) {
								$columnaTarifa = $key;
								$filaTarifa = $fila;
						  }
					 }
				}

				$filaTarifa = $filaTarifa + 1;
				$idEmpresa = $this->auth['empresa']['id'];
				$idUsua=$this->auth["usuario"]->id;

				Zend_Layout::getMvcInstance()->assign(
						  'productoId', $productoData['id_producto']
				);
				Zend_Layout::getMvcInstance()->assign(
						  'urlTarifa', 'precio' . $filaTarifa . '/' . $columnaTarifa
				);

				if (isset($extiende) && $extiende != '') {
					 Zend_Layout::getMvcInstance()->assign(
								'extiende', $extiende
					 );
				}

				if (isset($republica) && $republica != '') {
					 Zend_Layout::getMvcInstance()->assign(
								'republica', $republica
					 );
				}
		  }

		  if (!isset($tarifaId) || $tarifaId == null && !$this->_getParam('id_producto')) {
				$this->_redirect('/empresa/publica-aviso-preferencial/paso1');
		  }
		  $modelTarifa = new Application_Model_Tarifa();
		  $this->view->producto = $modelTarifa->getProductoByTarifa($tarifaId);

		  $this->view->maximoAnuncios = $t->getNumeroAvisoMaximoByPreferencial($tarifaId);

		  if (isset($avisoId)) {
				Zend_Layout::getMvcInstance()->assign(
						  'bodyAttr', array('id' => 'EditarAviso', 'class' => 'noMenu')
				);
		  } else {
				Zend_Layout::getMvcInstance()->assign(
						  'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
				);
		  }

		  if (isset($avisoId)) {
				$this->view->dataPosicion = $aviso->getPosicionByAviso($avisoId);
				$this->view->avisoWebId = $avisoId;
		  } elseif (isset($preferencial)) {
				$this->view->dataPosicion = $aviso->getPosicionByAvisoPreferencial($preferencial);
		  } else {
				$this->view->dataPosicion = $aviso->getPosicionByAviso();
		  }

		  $anuncioImpresoId = $this->view->dataPosicion['anuncioImpreso'];
		  $this->view->headScript()->appendFile($this->view->S('/js/empresa/empresa.aviso.paso2.js'));

		  $frmUbigeo = new Application_Form_Ubigeo();
		  $frmUbigeo->detalleUbigeo($this->auth['empresa']['id_ubigeo']);
		  $formDatos = new Application_Form_Paso2PublicarAviso(
					 false, false, array('tipoPublicacion' => Application_Model_AnuncioWeb::TIPO_PREFERENCIAL)
		  );

		  //Validar medida: Si es mayor a 6x3 muestra Gerencia sino no.
		  $gerencia = Application_Model_NivelPuesto::GERENCIA;
		  if ($t->validarPuestoTarifa($tarifaId))
				$formDatos->getElement('id_nivel_puesto')->removeMultiOption($gerencia);

		  //@codingStandardsIgnoreStart
		  $dataDefault = '';
		  $dataDefault .= ($frmUbigeo->pais_residencia->getValue() != '') ? $frmUbigeo->pais_residencia->getValue() : '-1';
		  $dataDefault .= ', ';
		  $dataDefault .= ($frmUbigeo->id_departamento->getValue() != '') ? $frmUbigeo->id_departamento->getValue() : '-1';
		  $dataDefault .= ', ';
		  $dataDefault .= ($frmUbigeo->id_provincia->getValue() != '') ? $frmUbigeo->id_provincia->getValue() : '-1';
		  $dataDefault .= ', ';
		  $dataDefault .= ($frmUbigeo->id_distrito->getValue() != '') ? $frmUbigeo->id_distrito->getValue() : '-1';
		  $frmUbigeo->pais_residencia->setAttrib('data-ubigeo', $dataDefault);

		  $formDatos->id_puesto->setValue($puestoOtherId);
		  $formDatos->id_tarifa->setValue($tarifaId);
		  $formDatos->removeTipoPuesto();
		  //@codingStandardsIgnoreEnd

		  $dataPosicion = $this->view->dataPosicion;

		  //Verificar si tiene logo
		  if ($this->auth['empresa']['logo'] == '') {
				$this->view->showLogoEmpresa = true;
				$frmEmpresa = new Application_Form_Paso1Empresa();
				$frmEmpresa->removeElement('rubro');
				$frmEmpresa->removeElement('pais_residencia');
				$frmEmpresa->removeElement('id_departamento');
				$frmEmpresa->removeElement('id_provincia');
				$frmEmpresa->removeElement('id_distrito');
				$frmEmpresa->removeElement('num_ruc');
				$frmEmpresa->removeElement('nombrecomercial');
				$frmEmpresa->removeElement('razonsocial');

		  }

		  if (!isset($avisoId) && ($dataPosicion['totalReady'] == $this->view->maximoAnuncios)) {
				$dataAviso = $dataPosicion['data'][$this->view->maximoAnuncios - 1];
				$avisoId = $dataAviso['id'];
		  }

		  if (isset($avisoId) || isset($extiende) || isset($republica)) {
				$baseFormEstudio = new Application_Form_Paso2EstudioPublicar(true);
				$managerEstudio = new App_Form_Manager($baseFormEstudio, 'managerEstudio');

				$baseFormExperiencia = new Application_Form_Paso2ExperienciaPublicar(true);
				$managerExperiencia = new App_Form_Manager($baseFormExperiencia, 'managerExperiencia');

				$baseFormOtroEstudio = new Application_Form_Paso2OtroEstudioPublicar(true);
				$managerOtroEstudio = new App_Form_Manager($baseFormOtroEstudio, 'managerOtroEstudio');

				$baseFormIdioma = new Application_Form_Paso2Idioma(true);
				$managerIdioma = new App_Form_Manager($baseFormIdioma, 'managerIdioma');

				$baseFormPrograma = new Application_Form_Paso2Programa(true);
				$managerPrograma = new App_Form_Manager($baseFormPrograma, 'managerPrograma');

				$baseFormPregunta = new Application_Form_Paso2PreguntaPublicar(true);
				$managerPregunta = new App_Form_Manager($baseFormPregunta, 'managerPregunta');
		  } else {
				$baseFormEstudio = new Application_Form_Paso2EstudioPublicar();
				$managerEstudio = new App_Form_Manager($baseFormEstudio, 'managerEstudio');

				$baseFormExperiencia = new Application_Form_Paso2ExperienciaPublicar();
				$managerExperiencia = new App_Form_Manager($baseFormExperiencia, 'managerExperiencia');

				$baseFormOtroEstudio = new Application_Form_Paso2OtroEstudioPublicar();
				$managerOtroEstudio = new App_Form_Manager($baseFormOtroEstudio, 'managerOtroEstudio');

				$baseFormIdioma = new Application_Form_Paso2Idioma();
				$managerIdioma = new App_Form_Manager($baseFormIdioma, 'managerIdioma');

				$baseFormPrograma = new Application_Form_Paso2Programa();
				$managerPrograma = new App_Form_Manager($baseFormPrograma, 'managerPrograma');

				$baseFormPregunta = new Application_Form_Paso2PreguntaPublicar();
				$managerPregunta = new App_Form_Manager($baseFormPregunta, 'managerPregunta');
		  }

		  $formEstudio = array();
		  $formOtroEstudio = array();
		  $formExperiencia = array();
		  $formIdioma = array();
		  $formPrograma = array();
		  $formPregunta = array();

		  if ($this->getRequest()->isPost()) {
				$postData = $this->_getAllParams();

				$tarifaId = $this->_getParam('id_tarifa');
				$modelProducto = new Application_Model_Producto;

				$tipoProducto = $modelProducto->obtenerTipoAviso($tarifaId);
				if ($tipoProducto != Application_Model_AnuncioWeb::TIPO_CLASIFICADO) {
					 $formDatos->removeTipoPuesto();
				}

				$postData['id_puesto'] = $puestoOtherId;

				$dFunciones = $postData["funciones"];
				$dResponsabilidades = $postData["responsabilidades"];

				//@codingStandardsIgnoreStart
				$postData["funciones"] = preg_replace($config->avisopaso2->expresionregular, '', $postData["funciones"]);
				$postData["responsabilidades"] = preg_replace($config->avisopaso2->expresionregular, '', $postData["responsabilidades"]);
				//@codingStandardsIgnoreEnd

				$postData["funciones"] = str_replace("@", "", $postData["funciones"]);
				$postData["responsabilidades"] = str_replace("@", "", $postData["responsabilidades"]);

				unset($session->tarifa);
					  if(isset( $postData["managerEstudio"])){
								unset($postData["managerEstudio"]);
						  }
						  if(isset( $postData["managerExperiencia"])){
								unset($postData["managerExperiencia"]);
						  }
						  if(isset( $postData["managerOtroEstudio"])){
								unset($postData["managerOtroEstudio"]);
						  }
						  if(isset( $postData["managerIdioma"])){
								unset($postData["managerIdioma"]);
						  }
						  if(isset( $postData["managerPrograma"])){
								unset($postData["managerPrograma"]);
						  }
				$validEstudio = $managerEstudio->isValid($postData);
				$validOtroEstudio = $managerOtroEstudio->isValid($postData);
				$validExperiencia = $managerExperiencia->isValid($postData);
				$validIdioma = $managerIdioma->isValid($postData);
				$validPrograma = $managerPrograma->isValid($postData);
				$validPregunta = $managerPregunta->isValid($postData);
				$frmUbigeo->isValid($postData);

				$formEstudio = $managerEstudio->getForms();
				$formOtroEstudio = $managerOtroEstudio->getForms();
				$formExperiencia = $managerExperiencia->getForms();
				$formIdioma = $managerIdioma->getForms();
				$formPrograma = $managerPrograma->getForms();
				$formPregunta = $managerPregunta->getForms();

				$this->view->isEstudio = true;
				$this->view->isOtroEstudio = true;
				$this->view->isExperiencia = true;
				$this->view->isIdioma = true;
				$this->view->isPrograma = true;
				$this->view->isPregunta = true;

				if ($formDatos->isValid($postData) &&
						  $managerEstudio->isValid($postData) &&
						  $managerOtroEstudio->isValid($postData) &&
						  $managerExperiencia->isValid($postData) &&
						  $managerIdioma->isValid($postData) &&
						  $managerPrograma->isValid($postData) &&
						  $frmUbigeo->isValid($postData)
				) {
					 $postData['id_usuario']=$idUsua;
					 $postData['id_empresa'] = $this->auth['empresa']['id'];
					 $postData['id_empresa_membresia'] = $this->auth['empresa']['em_id'];

					 $config = $this->getConfig();
					 $anuncioWebModel = new Application_Model_AnuncioWeb;
					 $dataPrioridad = $anuncioWebModel->prioridadAviso('preferencial', $this->auth['empresa']['id']);
					 $prioridad = $dataPrioridad['prioridad'];
					 $postData['prioridad'] = $prioridad;

					 if ($this->_getParam('tarifa') != null) {

						  $helperAvisoPreferencial = $this->_helper->getHelper('AvisoPreferencial');

						  if (isset($extiende) && $extiende != null) {
								$idAvisoPreferencial = $helperAvisoPreferencial
										  ->_insertarNuevoAvisoImpreso(
										  $postData, $managerEstudio, $managerExperiencia, $managerIdioma, $managerPrograma, $managerPregunta, $managerOtroEstudio, null, $extiende
								);

						  } elseif (isset($republica) && $republica != null) {
								$idAvisoPreferencial = $helperAvisoPreferencial
										  ->_insertarNuevoAvisoImpreso(
										  $postData, $managerEstudio, $managerExperiencia, $managerIdioma, $managerPrograma, $managerPregunta, $managerOtroEstudio, null, $republica, '1'
								);
						  } else {
								$idAvisoPreferencial = $helperAvisoPreferencial
										  ->_insertarNuevoAvisoImpreso(
										  $postData, $managerEstudio, $managerExperiencia, $managerIdioma, $managerPrograma, $managerPregunta, $managerOtroEstudio
								);
						  }

						  $idAviso = $helperAvisoPreferencial->getAnuncioId();

						  if (isset($extiende) && $extiende != null) {
								//Actualiza Match a Postulantes
								$mPostulacion = new Application_Model_Postulacion;
								$mPostulacion->actualizarMatchPostulantes($idAviso);
						  }


						  $usuarioEmpresa = $this->auth['usuario-empresa'];

						  $anuncioUsuarioEmpresaModelo = new Application_Model_AnuncioUsuarioEmpresa;

						  $servicio = new App_Service_Validate_UserCompany;
						  if (!$servicio->isCreator($usuarioEmpresa)) {
								$anuncioUsuarioEmpresaModelo->asignar(
										  $usuarioEmpresa['id'], $idAviso);
						  }

						  if ($this->auth['empresa']['logo'] == '') {
								$utilfile = $this->_helper->getHelper('UtilFiles');
								$nuevosNombres = $utilfile->_renameFile($frmEmpresa, 'logotipo', "image-empresa");
								//Sube logotipo y actualiza avisos activos
								if (is_array($nuevosNombres)) {

									 $valuesEmpresa['logo'] = $nuevosNombres[0];
									 $valuesEmpresa['logo1'] = $nuevosNombres[1];
									 $valuesEmpresa['logo2'] = $nuevosNombres[2];
									 $valuesEmpresa['logo3'] = $nuevosNombres[3];


									 $where = $this->_empresa->getAdapter()
										  ->quoteInto('id = ?', $idEmpresa);
									 $this->_empresa->update($valuesEmpresa, $where);

									 //Actualiza logo en Zend_Auth
									 $storage = Zend_Auth::getInstance()->getStorage()->read();
									 $storage['empresa']['logo'] = $nuevosNombres[0];
									 Zend_Auth::getInstance()->getStorage()->write($storage);

									 $anuncio = new Application_Model_AnuncioWeb();
									 $anuncio->updateLogoAnuncio($idEmpresa, $valuesEmpresa["logo2"]);

									 $modelAviso = new Application_Model_AnuncioWeb;
									 $dataAvisoXActualizar = $modelAviso->obtenerAvisosActivosEmpresa($idEmpresa);
									 foreach ($dataAvisoXActualizar as $infoAviso) {
										  $helperAvisoPreferencial->_SolrAviso->addAvisoSolr($infoAviso['id']);
										  //exec("curl -X POST -d 'api_key=" . $this->_buscamasConsumerKey . "&nid=" . $infoAviso['id'] . "&site=" . $this->_buscamasUrl . "' " . $this->_buscamasPublishUrl);
									 }
								}
						  }

						  $this->getMessenger()->success($this->_messageSuccess);
						  $this->_redirect(
									 '/empresa/publica-aviso-preferencial/paso2/preferencial/' .
									 $idAvisoPreferencial
						  );
					 } elseif (isset($preferencial) && $preferencial != null) {
						  if ($dataPosicion['totalReady'] == $this->view->maximoAnuncios) {
								$util = $this->_helper->getHelper('Util');
								$idUbigeo = $util->getUbigeo($postData);
								$avisoHelper = $this->_helper->getHelper('Aviso');

								$avisoHelper->_actualizarDatosPuesto($formDatos, $avisoId, null, $idUbigeo);
								$avisoHelper->_actualizarEstudios($managerEstudio, $avisoId);
								$avisoHelper->_actualizarExperiencas($managerExperiencia, $avisoId);
								$avisoHelper->_actualizarIdioma($managerIdioma, $avisoId);
								$avisoHelper->_actualizarPrograma($managerPrograma, $avisoId);
								$avisoHelper->_actualizarPregunta($managerPregunta, $avisoId);
								$this->getMessenger()->success($this->_messageSuccess);
								$this->_redirect(
										  '/empresa/publica-aviso-preferencial/paso2/preferencial/' .
										  $anuncioImpresoId . "/back/1"
								);
						  } else {
								$idAvisoPreferencial = $this->_helper->getHelper('AvisoPreferencial')
										  ->_insertarNuevoAvisoWebPreferencial(
										  $postData, $preferencial, $managerEstudio, $managerExperiencia, $managerIdioma, $managerPrograma, $managerPregunta, $managerOtroEstudio
								);

								$usuario = $this->auth['usuario'];
								$this->_helper->Aviso->extenderAviso($idAvisoPreferencial, $usuario->id);
								$this->_helper->Aviso->extenderReferidos($idAvisoPreferencial);

								$idAviso = $this->_helper->getHelper('AvisoPreferencial')
										  ->getAnuncioId();

								$usuarioEmpresa = $this->auth['usuario-empresa'];

								$anuncioUsuarioEmpresaModelo = new Application_Model_AnuncioUsuarioEmpresa;

								$servicio = new App_Service_Validate_UserCompany;
								if (!$servicio->isCreator($usuarioEmpresa)) {
									 $anuncioUsuarioEmpresaModelo->asignar(
												$usuarioEmpresa['id'], $idAviso);
								}

								$this->getMessenger()->success($this->_messageSuccess);
								$count = count($dataPosicion['data']) + 1;
								if ($count == $this->view->maximoAnuncios || $postData['contpaso3'] == 1) {
									 $this->_redirect(
												'/empresa/publica-aviso-preferencial/paso3/impreso/' . $dataPosicion['anuncioImpreso']
									 );
								} else {
									 $this->_redirect(
												'/empresa/publica-aviso-preferencial/paso2/preferencial/' .
												$idAvisoPreferencial
									 );
								}
						  }
					 } elseif (isset($avisoId) && $avisoId != null) {
						  $util = $this->_helper->getHelper('Util');
						  $idUbigeo = $util->getUbigeo($postData);
						  $avisoHelper = $this->_helper->getHelper('Aviso');

						  $avisoHelper->_actualizarDatosPuesto($formDatos, $avisoId, null, $idUbigeo);
						  $avisoHelper->_actualizarEstudios($managerEstudio, $avisoId);
						  $avisoHelper->_actualizarExperiencas($managerExperiencia, $avisoId);
						  $avisoHelper->_actualizarIdioma($managerIdioma, $avisoId);
						  $avisoHelper->_actualizarPrograma($managerPrograma, $avisoId);
						  $avisoHelper->_actualizarPregunta($managerPregunta, $avisoId);
						  $this->getMessenger()->success($this->_messageSuccess);

						  $count = 0;
						  foreach ($dataPosicion['data'] as $posicion) {
								$count++;
								if ($posicion['id'] == $avisoId && $count == $this->view->maximoAnuncios) {
									 $this->_redirect(
												'/empresa/publica-aviso-preferencial/paso2/preferencial/' .
												$anuncioImpresoId . "/back/1"
									 );
								}
						  }
						  if (isset($redirect)) {
						  } else {
								$this->_redirect(
										  '/empresa/publica-aviso-preferencial/paso2/preferencial/' .
										  $anuncioImpresoId
								);
						  }
					 } else {
						  $this->_redirect(
									 '/empresa/publica-aviso-preferencial/paso2/preferencial/' .
									 $anuncioImpresoId
						  );
					 }
				}
				else {
					 if(/*$validOtroEstudio && */!$managerOtroEstudio->isEmptyLastForm())
					 {
						  $ind = count($managerOtroEstudio->getForms());
						  $formOtroEstudio[$ind] = $managerOtroEstudio->getForm($ind);
					 }
					 if(/*$validExperiencia && */!$managerExperiencia->isEmptyLastForm())
					 {
						  $ind = count($managerExperiencia->getForms());
						  $formExperiencia[$ind] = $managerExperiencia->getForm($ind);
					 }
					 if(/*$validIdioma && */!$managerIdioma->isEmptyLastForm())
					 {
						  $ind = count($managerIdioma->getForms());
						  $formIdioma[$ind] = $managerIdioma->getForm($ind);
					 }
					 if(/*$validPrograma && */!$managerPrograma->isEmptyLastForm())
					 {
						  $ind = count($managerPrograma->getForms());
						  $formPrograma[$ind] = $managerPrograma->getForm($ind);
					 }
					 if(/*$validPregunta && */!$managerPregunta->isEmptyLastForm())
					 {
						  $ind = count($managerPregunta->getForms());
						  $formPregunta[$ind] = $managerPregunta->getForm($ind);
					 }

					 $formuEstudio = $managerEstudio->getForms();
					 $formEstudio = array();
					 foreach($formuEstudio as $ke => $fe)
					 {
						  $id_tipo_carrera = $fe->getElement('id_tipo_carrera')->getValue();
						  $fe->setElementCarrera($id_tipo_carrera);
						  $id_nivel_estudio = $fe->getElement('id_nivel_estudio')->getValue();
						  $fe->setElementNivelEstudio($id_nivel_estudio);
						  $formEstudio[$ke]=$fe;
					 }
					 if(/*$validEstudio && */!$managerEstudio->isEmptyLastForm())
					 {
						  $ind = count($managerEstudio->getForms());
						  $formEstudio[$ind] = $managerEstudio->getForm($ind);
					 }
				}

				/* validamos para enviarlo al paso3 */
				if (!empty($redirect)) {
					 $data1 = $this->_getParam('nombre_puesto', '');
					 $data2 = $this->_getParam('id_nivel_puesto', '-1');
					 $data3 = $this->_getParam('id_area', '-1');
					 $data4 = $this->_getParam('funciones', '');
					 $data5 = $this->_getParam('responsabilidades', '');
					 if ($data1 != '' && $data2 != '-1' && $data3 != '-1' &&
								$data4 != '' && $data5 != '') {
						  $this->_redirect($redirect);
					 } elseif ($data1 != '' || $data2 != '-1' || $data3 != '-1' ||
								$data4 != '' || $data5 != '') {

					 } else {
						  $this->_redirect($redirect);
					 }
				}
		  } elseif (isset($preferencial) && $preferencial != "") {
				if ($dataPosicion['totalReady'] == $this->view->maximoAnuncios) {
					 $back = $this->_getParam('back', '0');
					 foreach ($dataPosicion['data'] as $posicion) {
						  if ($posicion['id'] == $avisoId) {
								if ($back == 1) {
									 $this->_redirect(
												'/empresa/publica-aviso-preferencial/paso3/impreso/' . $dataPosicion['anuncioImpreso']
									 );
								}
						  }
					 }

					 $datosAviso = $aviso->getAvisoInfoById($avisoId);
					 $datosAviso['id_aviso'] = $datosAviso['id'];
					 $this->view->isEstudio = false;
					 if ($datosAviso['salario'] == null && $datosAviso['salario_min'] != null) {
						  $datosAviso['salario'] = $datosAviso['salario_min'] . '-max';
					 }
					 if ($datosAviso['mostrar_empresa'] == 0) {
						  $datosAviso['otro_nombre_empresa'] = $datosAviso['empresa_rs'];
						  unset($datosAviso['empresa_rs']);
					 }
					 $datosAviso['id_tarifa'] = $tarifaId;
					 $formDatos->isValid($datosAviso);
					 $frmUbigeo->detalleUbigeo($datosAviso['id_ubigeo']);
					 unset($session->tarifa);
					 $datosAvisoEstudio = $aviso->getEstudioInfoByAnuncio($avisoId);
					 $i = 0;
					 if (count($datosAvisoEstudio) > 0) {
						  $this->view->isEstudio = false;
						  $this->view->isEditarEstudio = true;
						  foreach ($datosAvisoEstudio as $d) {
								$form = $managerEstudio->getForm($i++, $d);
								if (isset($d['id_carrera'])) {
									 $carrera = new Application_Model_Carrera();
									 $idTipoCarrera = $carrera->getTipoCarreraXCarrera($d['id_carrera']);
									 $carreras = $carrera->filtrarCarrera($idTipoCarrera);
									 $form->getElement('id_carrera')->addMultioptions($carreras);
								}
								$form->setHiddenId($d['id']);
								$formEstudio[] = $form;
						  }
					 } else {
						  $this->view->isEstudio = false;
						  $this->view->isEditarEstudio = null;
						  $form = $managerEstudio->getForm($i++);
						  $formEstudio[] = $form;
					 }

					 $datosAvisoExperiencia = $aviso->getExperienciaInfoByAnuncio($avisoId);

					 $i = 0;
					 if (count($datosAvisoExperiencia) > 0) {
						  $this->view->isExperiencia = false;
						  $this->view->isEditarExperiencia = true;
						  foreach ($datosAvisoExperiencia as $d) {
								$form = $managerExperiencia->getForm($i++, $d);
								$form->setHiddenId($d['id']);
								$formExperiencia[] = $form;
						  }
					 } else {
						  $this->view->isExperiencia = false;
						  $this->view->isEditarExperiencia = null;
						  $form = $managerExperiencia->getForm($i++);
						  $formExperiencia[] = $form;
					 }

					 $datosAvisoIdioma = $aviso->getIdiomaInfoByAnuncio($avisoId);

					 $i = 0;
					 if (count($datosAvisoIdioma) > 0) {
						  $this->view->isIdioma = false;
						  $this->view->isEditarIdioma = true;
						  foreach ($datosAvisoIdioma as $d) {
								$form = $managerIdioma->getForm($i++, $d);
								$form->setHiddenId($d['id']);
								$formIdioma[] = $form;
						  }
					 } else {
						  $this->view->isIdioma = false;
						  $this->view->isEditarIdioma = null;
						  $form = $managerIdioma->getForm($i++);
						  $formIdioma[] = $form;
					 }

					 $datosAvisoPrograma = $aviso->getProgramaInfoByAnuncio($avisoId);

					 $i = 0;
					 if (count($datosAvisoPrograma) > 0) {
						  $this->view->isPrograma = false;
						  $this->view->isEditarPrograma = true;
						  foreach ($datosAvisoPrograma as $d) {
								$form = $managerPrograma->getForm($i++, $d);
								$form->setHiddenId($d['id']);
								$formPrograma[] = $form;
						  }
					 } else {
						  $this->view->isPrograma = false;
						  $this->view->isEditarPrograma = null;
						  $form = $managerPrograma->getForm($i++);
						  $formPrograma[] = $form;
					 }

					 $datosAvisoPregunta = $aviso->getPreguntaInfoByAnuncio($avisoId);
					 $i = 0;
					 if (count($datosAvisoPregunta) > 0) {
						  $this->view->isPregunta = false;
						  $this->view->isEditarPregunta = true;
						  foreach ($datosAvisoPregunta as $d) {
								$form = $managerPregunta->getForm($i++, $d);
								$form->setHiddenId($d['id']);
								$formPregunta[] = $form;
						  }
					 } else {
						  $this->view->isPregunta = null;
						  $this->view->isEditarPregunta = null;
						  $form = $managerPregunta->getForm($i++);
						  $formPregunta[] = $form;
					 }
				} else {
					 $formOtroEstudio[] = $managerOtroEstudio->getForm(0);
					 $formEstudio[] = $managerEstudio->getForm(0);
					 $formExperiencia[] = $managerExperiencia->getForm(0);
					 $formIdioma[] = $managerIdioma->getForm(0);
					 $formPrograma[] = $managerPrograma->getForm(0);
					 $formPregunta[] = $managerPregunta->getForm(0);
					 $this->view->isEstudio = false;
					 $this->view->isOtroEstudio = false;
					 $this->view->isExperiencia = false;
					 $this->view->isIdioma = false;
					 $this->view->isPrograma = false;
				}
		  } elseif (isset($avisoId) && $avisoId != "") {

				$datosAviso = $aviso->getAvisoInfoById($avisoId);
				$datosAviso['id_aviso'] = $datosAviso['id'];
				if ($datosAviso['salario'] == null && $datosAviso['salario_min'] != null) {
					 $datosAviso['salario'] = $datosAviso['salario_min'] . '-max';
				}
				if ($datosAviso['mostrar_empresa'] == 0) {
					 $datosAviso['otro_nombre_empresa'] = $datosAviso['empresa_rs'];
					 unset($datosAviso['empresa_rs']);
				}
				$datosAviso['id_tarifa'] = $tarifaId;
				$formDatos->isValid($datosAviso);
				$frmUbigeo->detalleUbigeo($datosAviso['id_ubigeo']);
				unset($session->tarifa);
				$datosAvisoEstudio = $aviso->getEstudioInfoByAnuncio($avisoId);
				$i = 0;
				if (count($datosAvisoEstudio) > 0) {
					 $this->view->isEstudio = true;
					 $this->view->isEditarEstudio = null;
					 foreach ($datosAvisoEstudio as $d) {
						  $form = $managerEstudio->getForm($i++, $d);
						  if (isset($d['id_carrera'])) {
								$carrera = new Application_Model_Carrera();
								$idTipoCarrera = $carrera->getTipoCarreraXCarrera($d['id_carrera']);
								$carreras = $carrera->filtrarCarrera($idTipoCarrera);
								$form->getElement('id_tipo_carrera')->setValue($idTipoCarrera);
								$form->getElement('id_carrera')->addMultioptions($carreras);
						  }
						  $form->setElementNivelEstudio($d['id_nivel_estudio']);
						  $form->setHiddenId($d['id']);
						  $formEstudio[] = $form;                }
				}
					 $form = $managerEstudio->getForm($i++);
					 $formEstudio[] = $form;

				$datosAvisoExperiencia = $aviso->getExperienciaInfoByAnuncio($avisoId);

				$i = 0;
				if (count($datosAvisoExperiencia) > 0) {
					 $this->view->isExperiencia = true;
					 $this->view->isEditarExperiencia = null;
					 foreach ($datosAvisoExperiencia as $d) {
						  $form = $managerExperiencia->getForm($i++, $d);
						  $form->setHiddenId($d['id']);
						  $formExperiencia[] = $form;
					 }
				}
					 $form = $managerExperiencia->getForm($i++);
					 $formExperiencia[] = $form;

				$datosAvisoOtroEstudio = $aviso->getOtroEstudioInfoByAnuncio($avisoId);

				$i = 0;
				if (count($datosAvisoOtroEstudio) > 0) {
					 $this->view->isOtroEstudio = true;
					 $this->view->isEditarOtroEstudio = null;
					 foreach ($datosAvisoOtroEstudio as $d) {
						  $form = $managerOtroEstudio->getForm($i++, $d);
						  $form->setHiddenId($d['id']);
						  $formOtroEstudio[] = $form;
					 }
				}
					 $form = $managerOtroEstudio->getForm($i++);
					 $formOtroEstudio[] = $form;

				$datosAvisoIdioma = $aviso->getIdiomaInfoByAnuncio($avisoId);

				$i = 0;
				if (count($datosAvisoIdioma) > 0) {
					 $this->view->isIdioma = true;
					 $this->view->isEditarIdioma = null;
					 foreach ($datosAvisoIdioma as $d) {
						  $form = $managerIdioma->getForm($i++, $d);
						  $form->setHiddenId($d['id']);
						  $formIdioma[] = $form;
					 }
				}
					 $form = $managerIdioma->getForm($i++);
					 $formIdioma[] = $form;

				$datosAvisoPrograma = $aviso->getProgramaInfoByAnuncio($avisoId);

				$i = 0;
				if (count($datosAvisoPrograma) > 0) {
					 $this->view->isPrograma = true;
					 $this->view->isEditarPrograma = null;
					 foreach ($datosAvisoPrograma as $d) {
						  $form = $managerPrograma->getForm($i++, $d);
						  $form->setHiddenId($d['id']);
						  $formPrograma[] = $form;
					 }
				}
					 $form = $managerPrograma->getForm($i++);
					 $formPrograma[] = $form;

				$datosAvisoPregunta = $aviso->getPreguntaInfoByAnuncio($avisoId);
				$i = 0;
				if (count($datosAvisoPregunta) > 0) {
					 $this->view->isPregunta = true;
					 $this->view->isEditarPregunta = null;
					 foreach ($datosAvisoPregunta as $d) {
						  $form = $managerPregunta->getForm($i++, $d);
						  $form->setHiddenId($d['id']);
						  $formPregunta[] = $form;
					 }
				}
					 $form = $managerPregunta->getForm($i++);
					 $formPregunta[] = $form;
		  } elseif (isset($extiende) && $extiende != "") {

				$avisoId = $extiende;

				$datosAviso = $aviso->getAvisoInfoById($avisoId);
				$datosAviso['id_aviso'] = $datosAviso['id'];
				if ($datosAviso['salario'] == null && $datosAviso['salario_min'] != null) {
					 $datosAviso['salario'] = $datosAviso['salario_min'] . '-max';
				}
				if ($datosAviso['mostrar_empresa'] == 0) {
					 $datosAviso['otro_nombre_empresa'] = $datosAviso['empresa_rs'];
					 unset($datosAviso['empresa_rs']);
				}

				if ($datosAviso['id_puesto'] != $puestoOtherId) {
					 $datosAviso['id_puesto'] = $puestoOtherId;
				}

				$datosAviso['id_tarifa'] = $tarifaId;
				$formDatos->isValid($datosAviso);
				$frmUbigeo->detalleUbigeo($datosAviso['id_ubigeo']);
				unset($session->tarifa);
				$datosAvisoEstudio = $aviso->getEstudioInfoByAnuncio($avisoId);

				$i = 0;
				if (count($datosAvisoEstudio) > 0) {
					 $this->view->isEstudio = true;
					 $this->view->isEditarEstudio = null;
					 foreach ($datosAvisoEstudio as $d) {
						  $form = $managerEstudio->getForm($i++, $d);
						  if (isset($d['id_carrera'])) {
								$carrera = new Application_Model_Carrera();
								$idTipoCarrera = $carrera->getTipoCarreraXCarrera($d['id_carrera']);
								$carreras = $carrera->filtrarCarrera($idTipoCarrera);
								$form->getElement('id_tipo_carrera')->setValue($idTipoCarrera);
								$form->getElement('id_carrera')->addMultioptions($carreras);
						  }
						  $form->setElementNivelEstudio($d['id_nivel_estudio']);
						  $form->setHiddenId($d['id']);
						  $formEstudio[] = $form;
					 }
				}
					 $form = $managerEstudio->getForm($i++);
					 $formEstudio[] = $form;

				$datosAvisoExperiencia = $aviso->getExperienciaInfoByAnuncio($avisoId);

				$i = 0;
				if (count($datosAvisoExperiencia) > 0) {
					 $this->view->isExperiencia = true;
					 $this->view->isEditarExperiencia = null;
					 foreach ($datosAvisoExperiencia as $d) {
						  $form = $managerExperiencia->getForm($i++, $d);
						  $form->setHiddenId($d['id']);
						  $formExperiencia[] = $form;
					 }
				}
					 $form = $managerExperiencia->getForm($i++);
					 $formExperiencia[] = $form;

				$datosAvisoOtroEstudio = $aviso->getOtroEstudioInfoByAnuncio($avisoId);

				$i = 0;
				if (count($datosAvisoOtroEstudio) > 0) {
					 $this->view->isOtroEstudio = true;
					 $this->view->isEditarOtroEstudio = null;
					 foreach ($datosAvisoOtroEstudio as $d) {
						  $form = $managerOtroEstudio->getForm($i++, $d);
						  $form->setHiddenId($d['id']);
						  $formOtroEstudio[] = $form;
					 }
				}
					 $form = $managerOtroEstudio->getForm($i++);
					 $formOtroEstudio[] = $form;

				$datosAvisoIdioma = $aviso->getIdiomaInfoByAnuncio($avisoId);

				$i = 0;
				if (count($datosAvisoIdioma) > 0) {
					 $this->view->isIdioma = true;
					 $this->view->isEditarIdioma = null;
					 foreach ($datosAvisoIdioma as $d) {
						  $form = $managerIdioma->getForm($i++, $d);
						  $form->setHiddenId($d['id']);
						  $formIdioma[] = $form;
					 }
				}
					 $form = $managerIdioma->getForm($i++);
					 $formIdioma[] = $form;

				$datosAvisoPrograma = $aviso->getProgramaInfoByAnuncio($avisoId);

				$i = 0;
				if (count($datosAvisoPrograma) > 0) {
					 $this->view->isPrograma = true;
					 $this->view->isEditarPrograma = null;
					 foreach ($datosAvisoPrograma as $d) {
						  $form = $managerPrograma->getForm($i++, $d);
						  $form->setHiddenId($d['id']);
						  $formPrograma[] = $form;
					 }
				}
					 $form = $managerPrograma->getForm($i++);
					 $formPrograma[] = $form;

				$datosAvisoPregunta = $aviso->getPreguntaInfoByAnuncio($avisoId);
				$i = 0;
				if (count($datosAvisoPregunta) > 0) {
					 $this->view->isPregunta = true;
					 $this->view->isEditarPregunta = null;
					 foreach ($datosAvisoPregunta as $d) {
						  $form = $managerPregunta->getForm($i++, $d);
						  $form->setHiddenId($d['id']);
						  $formPregunta[] = $form;
					 }
				}
					 $form = $managerPregunta->getForm($i++);
					 $formPregunta[] = $form;
		  } elseif (isset($republica) && $republica != "") {

				$avisoId = $republica;

				$datosAviso = $aviso->getAvisoInfoById($avisoId);
				$datosAviso['id_aviso'] = $datosAviso['id'];
				if ($datosAviso['salario'] == null && $datosAviso['salario_min'] != null) {
					 $datosAviso['salario'] = $datosAviso['salario_min'] . '-max';
				}
				if ($datosAviso['mostrar_empresa'] == 0) {
					 $datosAviso['otro_nombre_empresa'] = $datosAviso['empresa_rs'];
					 unset($datosAviso['empresa_rs']);
				}

				if ($datosAviso['id_puesto'] != $puestoOtherId) {
					 $datosAviso['id_puesto'] = $puestoOtherId;
				}

				$datosAviso['id_tarifa'] = $tarifaId;
				$formDatos->isValid($datosAviso);
				$frmUbigeo->detalleUbigeo($datosAviso['id_ubigeo']);
				unset($session->tarifa);
				$datosAvisoEstudio = $aviso->getEstudioInfoByAnuncio($avisoId);

				$i = 0;
				if (count($datosAvisoEstudio) > 0) {
					 $this->view->isEstudio = false;
					 $this->view->isEditarEstudio = true;
					 foreach ($datosAvisoEstudio as $d) {
						  $form = $managerEstudio->getForm($i++, $d);
						  if (isset($d['id_carrera'])) {
								$carrera = new Application_Model_Carrera();
								$idTipoCarrera = $carrera->getTipoCarreraXCarrera($d['id_carrera']);
								$carreras = $carrera->filtrarCarrera($idTipoCarrera);
								$form->getElement('id_carrera')->addMultioptions($carreras);
						  }
						  $form->setHiddenId($d['id']);
						  $formEstudio[] = $form;
					 }
				} else {
					 $this->view->isEstudio = false;
					 $this->view->isEditarEstudio = null;
					 $form = $managerEstudio->getForm($i++);
					 $formEstudio[] = $form;
				}

				$datosAvisoExperiencia = $aviso->getExperienciaInfoByAnuncio($avisoId);

				$i = 0;
				if (count($datosAvisoExperiencia) > 0) {
					 $this->view->isExperiencia = false;
					 $this->view->isEditarExperiencia = true;
					 foreach ($datosAvisoExperiencia as $d) {
						  $form = $managerExperiencia->getForm($i++, $d);
						  $form->setHiddenId($d['id']);
						  $formExperiencia[] = $form;
					 }
				} else {
					 $this->view->isExperiencia = false;
					 $this->view->isEditarExperiencia = null;
					 $form = $managerExperiencia->getForm($i++);
					 $formExperiencia[] = $form;
				}

				$datosAvisoIdioma = $aviso->getIdiomaInfoByAnuncio($avisoId);

				$i = 0;
				if (count($datosAvisoIdioma) > 0) {
					 $this->view->isIdioma = false;
					 $this->view->isEditarIdioma = true;
					 foreach ($datosAvisoIdioma as $d) {
						  $form = $managerIdioma->getForm($i++, $d);
						  $form->setHiddenId($d['id']);
						  $formIdioma[] = $form;
					 }
				} else {
					 $this->view->isIdioma = false;
					 $this->view->isEditarIdioma = null;
					 $form = $managerIdioma->getForm($i++);
					 $formIdioma[] = $form;
				}

				$datosAvisoPrograma = $aviso->getProgramaInfoByAnuncio($avisoId);

				$i = 0;
				if (count($datosAvisoPrograma) > 0) {
					 $this->view->isPrograma = false;
					 $this->view->isEditarPrograma = true;
					 foreach ($datosAvisoPrograma as $d) {
						  $form = $managerPrograma->getForm($i++, $d);
						  $form->setHiddenId($d['id']);
						  $formPrograma[] = $form;
					 }
				} else {
					 $this->view->isPrograma = false;
					 $this->view->isEditarPrograma = null;
					 $form = $managerPrograma->getForm($i++);
					 $formPrograma[] = $form;
				}

				$datosAvisoPregunta = $aviso->getPreguntaInfoByAnuncio($avisoId);
				$i = 0;
				if (count($datosAvisoPregunta) > 0) {
					 $this->view->isPregunta = false;
					 $this->view->isEditarPregunta = true;
					 foreach ($datosAvisoPregunta as $d) {
						  $form = $managerPregunta->getForm($i++, $d);
						  $form->setHiddenId($d['id']);
						  $formPregunta[] = $form;
					 }
				} else {
					 $this->view->isPregunta = null;
					 $this->view->isEditarPregunta = null;
					 $form = $managerPregunta->getForm($i++);
					 $formPregunta[] = $form;
				}
		  } else {
				$formOtroEstudio[] = $managerOtroEstudio->getForm(0);
				$formEstudio[] = $managerEstudio->getForm(0);
				$formExperiencia[] = $managerExperiencia->getForm(0);
				$formIdioma[] = $managerIdioma->getForm(0);
				$formPrograma[] = $managerPrograma->getForm(0);
				$formPregunta[] = $managerPregunta->getForm(0);

				$this->view->isEstudio = false;
				$this->view->isOtroEstudio = false;
				$this->view->isExperiencia = false;
				$this->view->isIdioma = false;
				$this->view->isPrograma = false;
		  }

		  if ($this->auth['empresa']['logo'] == '') {
				$this->view->frmEmpresa = $frmEmpresa;
		  }

		  $this->view->form = $formDatos;

		  $this->view->formEstudio = $formEstudio;
		  $this->view->assign('managerEstudio', $managerEstudio);

		  $this->view->formOtroEstudio = $formOtroEstudio;
		  $this->view->assign('managerOtroEstudio', $managerOtroEstudio);

		  $this->view->formExperiencia = $formExperiencia;
		  $this->view->assign('managerExperiencia', $managerExperiencia);

		  $this->view->formIdioma = $formIdioma;
		  $this->view->assign('managerIdioma', $managerIdioma);

		  $this->view->formPrograma = $formPrograma;
		  $this->view->assign('managerPrograma', $managerPrograma);

		  $this->view->formPregunta = $formPregunta;
		  $this->view->assign('managerPregunta', $managerPregunta);

		  $this->view->frmUbigeo = $frmUbigeo;
	 }

	 public function paso3Action() {

		  $this->view->dataRuc = $this->auth['empresa']['ruc'];

		  Zend_Layout::getMvcInstance()->assign(
					 'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
		  );
		  $this->view->headScript()->appendFile(
					 $this->view->S(
								'/js/tiny_mce/tiny_mce.js')
		  );

		  $this->view->headScript()->appendFile(
					 $this->view->S(
								'/js/empresa/empresa.aviso.paso3.js')
		  );

		  $allParams = $this->_getAllParams();
		  if (!isset($allParams['impreso'])) {
				$this->_redirect('/empresa/publica-aviso-preferencial/paso1');
		  }

		  $this->view->idImpreso = $idImpreso = $allParams['impreso'];
		  $formPaso = new Application_Form_Paso3AvisoPreferencial();

		  $modelAnuncioImp = new Application_Model_AnuncioImpreso();
		  $arrayPuestoTitulo = $modelAnuncioImp->getDetalleAvisoPreferencialImpreso($idImpreso);

		  $rowAnuncio = $modelAnuncioImp->getDatosPagarAnuncioImpreso($idImpreso);
		  $modelTarifa = new Application_Model_Tarifa();
		  $this->view->producto = $modelTarifa->getProductoByTarifa($rowAnuncio['tarifaId']);
		  $this->_config = Zend_Registry::get('config');
		  $this->view->scotPlantilla = $this->_config->SCOT->control->plantilla;

		  if ($rowAnuncio['medioPublicacion'] == Application_Model_CompraAdecsysCodigo::MEDIO_PUB_APTITUS) {
				$this->view->cantAviso = $this->_config->cantidadAviso->aptitus;
		  } elseif ($rowAnuncio['medioPublicacion'] == Application_Model_CompraAdecsysCodigo::MEDIO_PUB_TALAN) {
				$this->view->cantAviso = $this->_config->cantidadAviso->talan;
		  } else {
				$this->view->cantAviso = $this->_config->cantidadAviso->combo;
		  }

		  if ($rowAnuncio['enteId'] == null || $rowAnuncio['enteId'] == false) {
				$rowAnuncio['numeroDoc'] = $rowAnuncio['empresaRuc'];

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
						  foreach ($emailing as $email) {
								$this->_helper->mail->notificacionAdecsys(
									 array(
										  'to' => $email,
										  'mensaje' => $ex->getMessage(),
										  'trace' => $ex->getTraceAsString(),
										  'refer' => $this->getRequest()->getRequestUri()
										  )
								);
						  }
					 }
				}
				$empresaEnte = new Application_Model_EmpresaEnte();
				$enteId = $empresaEnte->getRegistroEnte((int)$rowAnuncio['empresaId']);
				  if (is_null($enteId)) {
						$this->_helper->aviso->registrarCodigoEnte($ws, $cliente, $aptitus, $rowAnuncio, null);
				  }
		  }

		  $resultDesAnunImp = $modelAnuncioImp->getInfoAnuncioImpreso($allParams['impreso']);
		  $numcharaveragebyword = $this->config->avisoimpreso->numero->caracteres->promxpalabra;

		  $this->view->maxCaracteresText = $this->config->avisoimpreso->tamano->maxcaracteres->
					 {$resultDesAnunImp['descripcion']}->texto * $numcharaveragebyword;
		  $this->view->maxCaracteresLeft = $this->config->avisoimpreso->tamano->maxcaracteres->
					 {$resultDesAnunImp['descripcion']}->planLeft * $numcharaveragebyword;
		  $this->view->maxCaracteresRight = $this->config->avisoimpreso->tamano->maxcaracteres->
					 {$resultDesAnunImp['descripcion']}->planRight * $numcharaveragebyword;

		  $datosAnuncioInfo = $modelAnuncioImp->verifAnuncioImpreso($allParams['impreso']);
		  $dataContenido = $dataDisenador = '';
		  $idPlantilla = 0;
		  $maxCaracteres = 0;
		  $dataTipoDiseno = '';
		  if ($datosAnuncioInfo) {
				$idPlantilla = $datosAnuncioInfo['id_plantilla'];
				$dataContenido = $datosAnuncioInfo['texto'];
				$dataDisenador = $datosAnuncioInfo['nota_diseno'];
				$dataTipoDiseno = $datosAnuncioInfo['tipo_diseno'];
				if ((1 <= $idPlantilla) && ($idPlantilla <= 4)) {
					 $maxCaracteres = $this->config->avisoimpreso->tamano->maxcaracteres->
								{$resultDesAnunImp['descripcion']}->texto;
				}
				if ((5 <= $idPlantilla) && ($idPlantilla <= 6)) {
					 $maxCaracteres = $this->config->avisoimpreso->tamano->maxcaracteres->
								{$resultDesAnunImp['descripcion']}->planLeft;
				}
				if ($idPlantilla == 7) {
					 $maxCaracteres = $this->config->avisoimpreso->tamano->maxcaracteres->
								{$resultDesAnunImp['descripcion']}->planRight;
				}
		  }
		  $this->view->idPlantilla = $idPlantilla;
		  $this->view->dataContenido = $dataContenido;
		  $this->view->dataDisenador = $dataDisenador;
		  $this->view->maxCaracteres = $maxCaracteres;
		  $this->view->dataTipoDiseno = $dataTipoDiseno;

		  $modelPlantilla = new Application_Model_Plantilla();
		  $arrayPlantilla = $modelPlantilla->getPlantillas();

		  $arrayPuestoDefaults = $modelAnuncioImp->getDataAnuncioPreferencialImpreso($idImpreso);

		  if ($arrayPuestoDefaults['id_plantilla'] != null && $arrayPuestoDefaults['tipo_diseno']) {
				$arrayPuestoDefaults['contenido_aviso'] = $arrayPuestoDefaults['texto'];
				$this->view->ca = $arrayPuestoDefaults['id_plantilla'];
				unset($arrayPuestoDefaults['texto']);
				$formPaso->setDefaults($arrayPuestoDefaults);
		  } elseif ($arrayPuestoDefaults['tipo_diseno'] != Application_Model_AnuncioImpreso::TIPO_DISENIO_PROPIO ||
					 $arrayPuestoDefaults['tipo_diseno'] != Application_Model_AnuncioImpreso::TIPO_DISENIO_PRE_DISENIADO) {
				$arrayPuestoDefaults['contenido_aviso'] = $arrayPuestoDefaults['texto'];
				unset($arrayPuestoDefaults['texto']);
				$formPaso->setDefaults($arrayPuestoDefaults);
		  }

		  if ($this->_request->isPost()) {
				if ($allParams['tipo_diseno'] == Application_Model_AnuncioImpreso::TIPO_DISENIO_PRE_DISENIADO) {
					 $formPaso->getElement('contenido_aviso')->setRequired(true);

					 $arrayReqPlan = $modelPlantilla->requiereAdjuntoByIdPlantilla($allParams['inputImgAdv']);

					 if ($arrayReqPlan['contiene_logo'] == 1) {
						  $data = $this->_helper->Aviso->verificarArchivoAdjuntoEnScot($idImpreso);
						  $condicion = $data != 0;
					 } else {
						  $condicion = true;
					 }
				} else {
					 $formPaso->getElement('contenido_aviso')->setRequired(false);
					 $data = $this->_helper->Aviso->verificarArchivoAdjuntoEnScot($idImpreso);
					 $condicion = $data != 0;
				}

				if ($condicion) {
					 $formValid = $formPaso->isValid($allParams);
					 if ($formValid) {
						  $formValue = $formPaso->getValues();
						  if ($allParams['tipo_diseno'] != 'propio') {
								$formValue['id_plantilla'] = $allParams['inputImgAdv'];
								$formValue['texto'] = $formValue['contenido_aviso'];
						  } else {
								$formValue['id_plantilla'] = null;
								$formValue['texto'] = null;
								$formValue['nota_diseno'] = null;
						  }
						  $formValue['tipo_diseno'] = $allParams['tipo_diseno'];
						  unset($formValue['contenido_aviso']);
						  $where = $modelAnuncioImp->getAdapter()->quoteInto('id = ?', $idImpreso);
						  $modelAnuncioImp->update($formValue, $where);

						  $this->_redirect('/empresa/publica-aviso-preferencial/paso4/impreso/' . $idImpreso);
					 }
				} else {
					 $this->getMessenger()->error('Debe adjuntar el diseño de su aviso impreso.');
				}
		  }

		  $this->view->plantilla = $arrayPlantilla;
		  $this->view->PuestoTitulo = $arrayPuestoTitulo;
		  if ($arrayPuestoDefaults['tipo_diseno'] == null) {
				$formPaso->getElement("tipo_diseno")->setValue("propio");
		  }
		  $this->view->form = $formPaso;
	 }

	 public function paso4Action() {
		  $config = Zend_Registry::get("config");
		  $anuncioImpresoId = $this->_getParam('impreso');
		  $sessionDatosPasarela = new Zend_Session_Namespace('facturaDatos');

		  $impresoOk = $this->_helper->Aviso->validarAdjuntoScot($anuncioImpresoId);

		  if (!$impresoOk) {
				$this->getMessenger()->error('Debe adjuntar el diseño de su aviso impreso.');
				$this->_redirect('/empresa/publica-aviso-preferencial/paso3/impreso/' . $anuncioImpresoId);
		  }

		  $this->view->headMeta()->appendName(
					 "Description", "Pague su Aviso, cuarto paso para la publicación de tu aviso en aquiempleos.com." .
					 " Los Clasificados de Empleos de La Prensa."
		  );

		  $this->view->headTitle()->set('Paso 4 - Pague su Aviso, Publica tu aviso en AquiEmpleos');

		  $this->view->headLink()->appendStylesheet(
				$this->view->S(
					 '/js/datepicker/themes/redmond/ui.all.css', 'all')
		  );

		  if ($this->_getParam('error') == 1) {
				$this->getMessenger()->error(
						  'El Pago no se procesó correctamente. Intente nuevamente en unos minutos,
					 de lo contrario, consulte con el Administrador del Sistema.'
				);
		  }
		  $this->view->module = $this->getRequest()->getModuleName();
		  $this->view->controller = $this->getRequest()->getControllerName();
		  $this->view->anuncioImpresoId = $anuncioImpresoId;

		  /* SE USA AL MOMENTO DE PAGAR */
		  $formAvisoPref = new Application_Form_Paso1AvisoPreferencial();


		  $modelAnuncioImpreso = new Application_Model_AnuncioImpreso();
		  $this->view->afectaMembresia = $afectaMembresia = $modelAnuncioImpreso->getAfectaMembresiaProductoXidImpreso($anuncioImpresoId);

		  $this->view->membresia = $idMembresia = $this->auth['empresa']['em_id'];

		  $this->view->rol = $a = $this->getRequest()->getModuleName();

		  if ($afectaMembresia != false || $afectaMembresia != 0) {
				if ($idMembresia != null) {
					 $modelEmpresaEnte = new Application_Model_EmpresaEnte();
					 $idEmpresaEnte = $modelEmpresaEnte->getEmpresaEnteXIdEmpresa($this->auth['empresa']['id']);

					 if (isset($idEmpresaEnte)) {
						  $rowEnvio['iCodCliente'] = $idEmpresaEnte;
						  $rowEnvio['iValor'] = '0';
					 } else {
						  //realizara la consulta haci adecsys si existe la empresa, si no lo registra, otro caso es que
						  //exista la empresa pero aun no esta en la tabla adecsys_ente.
					 }

					 //Consulta del descuento aviso
					 $this->view->precioConDescuento = 1.00;
				}
		  }
		  if (empty($anuncioImpresoId)) {
				$this->_redirect('/empresa/publica-aviso-preferencial/paso1');
		  } else {
				$this->view->headScript()->appendFile(
						  $this->view->S(
									 '/js/empresa/empresa.aviso.paso4.js')
				);
				Zend_Layout::getMvcInstance()->assign(
						  'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
				);
				$this->_ai = new Application_Model_AnuncioImpreso();
				$rowAnuncio = $this->_ai->getDatosPagarAnuncioImpreso($anuncioImpresoId);
				$modelTarifa = new Application_Model_Tarifa();
				$this->view->producto = $modelTarifa->getProductoByTarifa($rowAnuncio['tarifaId']);

				$dataWSConsulta = array();
				$dataWSConsulta["Tip_Doc"] = "RUC";
				$dataWSConsulta["Num_Doc"] = $this->auth["empresa"]["ruc"];

				$medioPublicacion = $rowAnuncio['medioPublicacion'];
				if ($medioPublicacion == 'aptitus y talan') {
					 $medioPublicacion = 'combo';
				}
				$this->view->medioPublicacion = $medioPublicacion;

				$cierre = $this->config->cierre->toArray();
				$cierre[$medioPublicacion]['hora'];
				$fecImpre = new Zend_Date();
				$fecImpre->setLocale(Zend_Locale::ZFDEFAULT);
				$fecImpre->set($cierre[$medioPublicacion]['dia'], Zend_Date::WEEKDAY_DIGIT);
				$fecImpre->set($cierre[$medioPublicacion]['hora'], Zend_Date::HOUR);
				$fecImpre->set(0, Zend_Date::MINUTE);
				$fecImpre->set(0, Zend_Date::SECOND);
				$fecCierre = clone $fecImpre;
				$now = date('Y-m-d H:i:s');
				if ($fecCierre->isEarlier($now, 'YYYY-MM-dd h:m:s')) {
					 $fecCierre->add(7, Zend_Date::DAY);
					 $fecImpre->add(7, Zend_Date::DAY);
				}

				$this->view->fhCierre = $this->_helper->Aviso->getFechaCierreImpresoByPaquete($medioPublicacion);

				$fecImpre->set(0, Zend_Date::HOUR);
				if ($cierre[$medioPublicacion]['semanaActual'] == 0) {
					 $fecImpre->add(7, Zend_Date::DAY);
				}

				$fecImpre->set($cierre['aptitus']['diaPublicacion'], Zend_Date::WEEKDAY_DIGIT);

				$this->view->fechaImpreso = $dataWSConsulta["Prim_Fec_Pub"] = $fecImpre->toString('YYYY-MM-dd');

				$dataWSConsulta["Fechas_Pub_Aviso"] = array();
				$dataWSConsulta["Fechas_Pub_Aviso"][] = $fecImpre->toString('YYYY-MM-dd');
				$dataWSConsulta["ImporteAviso"] = $rowAnuncio["tarifaPrecio"];

				$dataExt = array();
				$dataExt["tamano"] = $rowAnuncio["tamano"];
				$dataExt["medidaTarifa"] = $rowAnuncio["medidaTarifa"];
				$dataExt["medioPublicacion"] = $rowAnuncio["medioPublicacion"];
				$dataExt["anuncioImpresoId"] = $rowAnuncio["anuncioImpresoId"];
				$dataExt["cod_subseccion"] = $rowAnuncio['anunciosWeb']['0']['cod_subseccion'];

				if (isset($this->auth["empresa"]["membresia_info"]["membresia"])) {
					 switch ($this->auth["empresa"]["membresia_info"]["membresia"]["m_tipo"]) {
						  case "membresia" :
								$dataExt["modalidadEmpresa"] = "M";
								break;
						  case "bonificado" :
								$dataExt["modalidadEmpresa"] = "C";
								break;
						  default :
								$dataExt["modalidadEmpresa"] = "N";
								break;
					 }
				} else {
					 $dataExt["modalidadEmpresa"] = "N";
				}

				//Actualizar ente en APT si en Adecsys es diferente, siempre y cuando exista en ADECSYS Y EN APT
				$adecsysValida = $this->_helper->getHelper('AdecsysValida');
				$tipoDoc = Application_Model_AdecsysEnte::DOCUMENTO_RUC;
				$ruc = $this->auth['empresa']['ruc'];
				$adecsysValida->compareEnte($tipoDoc,$ruc);

				$dataConsulta = array();
				$dataConsulta["dataWS"] = $dataWSConsulta;
				$dataConsulta["dataExt"] = $dataExt;

				$resultConsulta = $this->_helper->WebServiceConsultaPrecioAnuncioPref->consulta($dataConsulta);
				$esVip = $resultConsulta["esVip"];
				$contratos = $resultConsulta["contratos"];

				if ($contratos == null || count($contratos) <= 0) {
					 $contratos = array();
					 $contraNormal = array();
					 $contraNormal["FormaPago"] = "C";
					 $contraNormal["NroContrato"] = "";
					 $contraNormal["ModalidadContrato"] = "N";
					 $contraNormal["SaldoInicial"] = 0;
					 $contraNormal["SaldoFinal"] = 0;
					 $contraNormal["MontoAPagar"] = $rowAnuncio['tarifaPrecio'];

					 $contratos[] = $contraNormal;
				}

				$dataContratosCompra = $this->_helper
						  ->WebServiceConsultaPrecioAnuncioPref->dataVistaContratos($contratos, $rowAnuncio["tarifaPrecio"]);
				$contratos = $dataContratosCompra["contratos"];

				$this->view->contratos = $dataContratosCompra["preciosContratos"];
				$this->view->tieneCredito = $dataContratosCompra["tieneCredito"];
				$this->view->tieneMembresia = $dataContratosCompra["tieneMembresia"];
				$this->view->tieneContrato = $dataContratosCompra["tieneContrato"];
				$this->view->precioContrato = $dataContratosCompra["precioContrato"];

				$this->auth["anuncioImpreso"] = array();
				$this->auth["anuncioImpreso"]["id"] = $anuncioImpresoId;
				$this->auth["anuncioImpreso"]["esVip"] = $esVip;
				$this->auth["anuncioImpreso"]["tipoContrato"] = $dataContratosCompra["tipoContrato"];
				$this->auth["anuncioImpreso"]["tieneCredito"] = $dataContratosCompra["tieneCredito"];
				$this->auth["anuncioImpreso"]["tieneMembresia"] = $dataContratosCompra["tieneMembresia"];
				$this->auth["anuncioImpreso"]["tieneContrato"] = $dataContratosCompra["tieneContrato"];
				$this->auth["anuncioImpreso"]["precioContrato"] = $dataContratosCompra["precioContrato"];
				$this->auth["anuncioImpreso"]["contratos"] = $contratos;
				Zend_Auth::getInstance()->getStorage()->write($this->auth);


				$this->view->formAvisoPref = $formAvisoPref;

				$descuento = $rowAnuncio["tarifaPrecio"] - $contratos[0]['MontoAPagar'];
				if ($descuento <= 0)
					 $descuento = 0; //$descuento*=-1;//
				$this->view->precioConDescuento = $contratos[0]['MontoAPagar'];
				$this->view->descuentoAnuncio = $descuento;
				$this->view->saldo = $contratos[0]['SaldoInicial'];
				$this->view->tamano = $rowAnuncio['tamano'];
				$rowAnuncio['tipo_paquete'] = strtoupper(trim(str_replace('Preferencial', '', $rowAnuncio['nombreProducto'])));
				$rowAnuncio['nombre_tipo_paquete'] = 'Preferencial' . strtoupper(trim(str_replace('Preferencial', '', $rowAnuncio['nombreProducto'])));

				$this->view->dataAnuncio = $rowAnuncio;
				 $validaRUC = $this->_helper->Aviso->validarDocumentoAdecsys(Application_Model_Compra::RUC,$ruc);
		  $formFactura= new Application_Form_FacturacionDatos();
		  $sessionDatosPasarela->factura=$validaRUC;

		  if (empty($validaRUC)) {
				$this->view->val_ruc =0;
				$this->view->ruc = $this->auth['empresa']['ruc'];
				$this->view->razonsocial = $this->auth['empresa']['razon_social'];
				$dataEmpresa['txtRuc']=$ruc;
				$dataEmpresa['txtName']=$this->auth['empresa']['razon_social'];
				$formFactura->setDefaults($dataEmpresa);
				$formFactura->setreadonly($dataEmpresa);
		  } else {
				$formFactura->setDefaults($validaRUC);
				$formFactura->setreadonly($validaRUC);
		  }
		  $tipo=$rowAnuncio['tipo'];
		  $aviso=$rowAnuncio['medioPublicacion'];
		  if ($aviso == "aptitus y talan" ) {
					$tipos=  explode(' y ',$aviso);
					$aviso=$tipos[0];
		  }
		  $this->view->Formfacturacion=$formFactura;
		  $sessionAdmin = new Zend_Session_Namespace('admin');
		  $this->view->isSessionAdmin = ($sessionAdmin->auth ? true : false);
		  $DescuentoAviso   =  $this->_config->extracargosAvisos->toArray();
		  $descuentototal= isset($DescuentoAviso[$tipo][$aviso]["descuentos"]["valor"])?$DescuentoAviso[$tipo][$aviso]["descuentos"]["valor"]:'';
		  $this->view->Descuentos=($this->view->isSessionAdmin)?$descuentototal:array()  ;
		  $this->view->moneda = $config->app->moneda;
		  }
	 }

	 public function duplicarAvisoAction() {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();
		  $usuario = $this->auth['usuario'];
		  $empresa = $this->auth['empresa'];
		  $idAnuncioWeb = $this->_getParam('aviso', false);
		  $avisoWebModelo = new Application_Model_AnuncioWeb();
		  $data = $avisoWebModelo->getFullAvisoById($idAnuncioWeb);
		  $slugFilter = new App_Filter_Slug();
		  $_tu = new Application_Model_TempUrlId();

		  if ($data['mostrar_empresa'] == 0) {
				$empresaRs = $data['empresa_rs'];
		  } else {
				$empresaRs = $empresa['nombre_comercial'];
		  }

		  $nuevoAvisoId = $avisoWebModelo->insert(
					 array(
						  'id_puesto' => $data['id_puesto'],
						  'id_producto' => $data['id_producto'],
						  'puesto' => $data['puesto'],
						  'id_area' => $data['id_area'],
						  'id_nivel_puesto' => $data['id_nivel_puesto'],
						  'funciones' => $data['funciones'],
						  'responsabilidades' => $data['responsabilidades'],
						  'mostrar_salario' => $data['mostrar_salario'],
						  'mostrar_empresa' => $data['mostrar_empresa'],
						  'salario_min' => $data['salario_min'],
						  'salario_max' => $data['salario_max'],
						  'online' => '0',
						  'borrador' => '1',
						  'id_empresa' => $empresa['id'],
						  'id_ubigeo' => $data['id_ubigeo'],
						  'fh_creacion' => date('Y-m-d H:i:s'),
						  'fh_edicion' => date('Y-m-d H:i:s'),
						  'creado_por' => $usuario->id,
						  'url_id' => $_tu->popUrlId(),
						  'slug' => $slugFilter->filter($data['puesto']),
						  'empresa_rs' => $empresaRs,
						  'estado' => 'registrado',
						  'origen' => 'apt_2',
						  'id_tarifa' => $data['id_tarifa'],
						  'id_producto' => $data['id_producto'],
						  'tipo' => $data['tipo'],
						  'medio_publicacion' => $data['medio_publicacion'],
						  'logo' => $data["logo"],
						  'id_anuncio_impreso' => $data['id_anuncio_impreso'],
						  'chequeado' => 1,
						  'correo' => $data['correo']
					 )
		  );

		  $where = $this->getAdapter()->quoteInto('id = ?', $nuevoAvisoId);
		  $avisoWebModelo->update(array('extiende_a' => $nuevoAvisoId), $where);

		  $dataEstudio = $avisoWebModelo->getEstudioInfoByAnuncio($idAnuncioWeb);
		  $anuncioEstudio = new Application_Model_AnuncioEstudio();
		  foreach ($dataEstudio as $estudio) {
				$anuncioEstudio->insert(
						  array(
								'id_anuncio_web' => $nuevoAvisoId,
								'id_nivel_estudio' => $estudio['id_nivel_estudio'],
								'id_carrera' => $estudio['id_carrera']
						  )
				);
		  }
		  $dataExperiencia = $avisoWebModelo->getExperienciaInfoByAnuncio($idAnuncioWeb);
		  $anuncioExperiencia = new Application_Model_AnuncioExperiencia();
		  foreach ($dataExperiencia as $experiencia) {
				$anuncioExperiencia->insert(
						  array(
								'id_anuncio_web' => $nuevoAvisoId,
								'id_nivel_puesto' => $experiencia['id_nivel_puesto'],
								'id_area' => $experiencia['id_area'],
								'experiencia' => $experiencia['experiencia']
						  )
				);
		  }
		  $dataIdioma = $avisoWebModelo->getIdiomaInfoByAnuncio($idAnuncioWeb);
		  $anuncioIdioma = new Application_Model_AnuncioIdioma();
		  foreach ($dataIdioma as $idioma) {
				$anuncioIdioma->insert(
						  array(
								'id_idioma' => $idioma['id_idioma'],
								'id_anuncio_web' => $nuevoAvisoId,
								'nivel' => $idioma['nivel_idioma']
						  )
				);
		  }
		  $dataPrograma = $avisoWebModelo->getProgramaInfoByAnuncio($idAnuncioWeb);
		  $anuncioPrograma = new Application_Model_AnuncioProgramaComputo();
		  foreach ($dataPrograma as $programa) {
				$anuncioPrograma->insert(
						  array(
								'id_programa_computo' => $programa['id_programa_computo'],
								'id_anuncio_web' => $nuevoAvisoId,
								'nivel' => $programa['nivel']
						  )
				);
		  }
		  $dataPregunta = $avisoWebModelo->getPreguntaInfoByAnuncio($idAnuncioWeb);
		  if (count($dataPregunta) > 0) {
				$cuestionario = new Application_Model_Cuestionario();
				$cuestionarioId = $cuestionario->insert(
						  array(
								'id_empresa' => $empresa['id'],
								'id_anuncio_web' => $nuevoAvisoId,
								'nombre' =>
								'Cuestionario de la empresa ' . $empresa['nombre_comercial']
						  )
				);
				$anuncioPregunta = new Application_Model_Pregunta();
				foreach ($dataPregunta as $pregunta) {
					 $anuncioPregunta->insert(
								array(
									 'id_cuestionario' => $cuestionarioId,
									 'pregunta' => $pregunta['pregunta']
								)
					 );
				}
		  }
		  $this->_redirect(
					 'empresa/publica-aviso-preferencial/paso2/preferencial/' .
					 $data['id_anuncio_impreso']
		  );
	 }

	 public function eliminarAvisoAction($idAnuncioWeb) {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();
		  $idAnuncioWeb = $this->_getParam('aviso', false);

		  $anuncioEstudio = new Application_Model_AnuncioEstudio();
		  $where = array('id_anuncio_web = ?' => $idAnuncioWeb);
		  $anuncioEstudio->delete($where);

		  $anuncioExperiencia = new Application_Model_AnuncioExperiencia();
		  $where = array('id_anuncio_web = ?' => $idAnuncioWeb);
		  $anuncioExperiencia->delete($where);

		  $anuncioIdioma = new Application_Model_AnuncioIdioma();
		  $where = array('id_anuncio_web = ?' => $idAnuncioWeb);
		  $anuncioIdioma->delete($where);

		  $anuncioPrograma = new Application_Model_AnuncioProgramaComputo();
		  $where = array('id_anuncio_web = ?' => $idAnuncioWeb);
		  $anuncioPrograma->delete($where);

		  $cuestionario = new Application_Model_Cuestionario();
		  $cuestionarioId = $cuestionario->getCuestionarioByAnuncioWeb($idAnuncioWeb);

		  $pregunta = new Application_Model_Pregunta();
		  $where = array('id_cuestionario = ?' => $cuestionarioId);
		  $pregunta->delete($where);

		  if (isset($cuestionarioId) && $cuestionarioId != null) {
				$where = array('id_anuncio_web = ?' => $idAnuncioWeb);
				$cuestionario->delete($where);
		  }

		  $avisoWebModelo = new Application_Model_AnuncioWeb();
		  $data = $avisoWebModelo->getPosicionByAviso($idAnuncioWeb);
		  $where = array('id = ?' => $idAnuncioWeb);
		  $avisoWebModelo->delete($where);
		  $this->getMessenger()->success("El aviso ha sido eliminado.");
		  $this->_redirect(
					 'empresa/publica-aviso-preferencial/paso2/preferencial/' .
					 $data['anuncioImpreso']
		  );
	 }


	 public function paso4MetodoPagoPosAction()
	 {

		  $sessionAdmin = new Zend_Session_Namespace('admin');
		  if ($sessionAdmin->auth) {
				$this->_helper->layout->disableLayout();
				echo $this->view->render('_partials/modal_boxes/_form_modal_pos.phtml');

		  }
		  exit(0);


	 }



}
