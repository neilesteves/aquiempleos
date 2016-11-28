<?php

class Empresa_PublicaAvisoDestacadoController extends App_Controller_Action_Empresa {

	 protected $_avisoId;
	 protected $_tarifaId;
	 protected $_empresa;

	 //Buscamas
	 private $_buscamasConsumerKey;
	 private $_buscamasPublishUrl;
	 private $_buscamasUrl;

	 public function init()
	 {
		  parent::init();
		  if(isset($this->auth['empresa']['membresia_info']['membresia']))
				$this->_redirect('/empresa/publica-aviso');
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

		  $this->_empresa = new Application_Model_Empresa;

		  $this->view->headMeta()->appendName(
				"Keywords",
				"elige tu aviso, publica tu aviso, aviso en AquiEmpleos, " .
				"pasos para publicar, Perfil del puesto, " .
				"Complete su aviso Impreso, Pague su Aviso"
		  );
		  $this->view->empresaId = $this->auth['empresa']['id'];

		  $this->_config = Zend_Registry::get('config');

		  $anuncioId  = null;
		  $this->_tarifaId = 169;
		  $republica  = $this->_getParam('republica');
		  $extiende   = $this->_getParam('extiende');

		  if (!empty($republica))
				$anuncioId = $republica;

		  if (!empty($extiende))
				$anuncioId = $extiende;

		  if (!is_null($anuncioId)) {
				$anuncio        = new App_Service_Validate_Ad($anuncioId);
				$usuarioEmpresa = $this->auth['usuario-empresa'];

				if (!$anuncio->isManaged($usuarioEmpresa)) {
					 $this->getMessenger()->error($anuncio->getMessage());
					 $this->_redirect('/empresa/mi-cuenta');
				}
		  }
	 }

	 public function indexAction()
	 {
		  $this->_redirect('/empresa/publica-aviso');
		  Zend_Layout::getMvcInstance()->assign(
				'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
		  );
		  $this->view->menu_sel = self::MENU_MI_CUENTA;
		  $this->view->menu_post_sel = self::MENU_POST_PUBLICA_AVISO;
		  $this->view->moneda = $this->_config->app->moneda;

		  $this->view->headScript()->appendFile(
				$this->view->S(
					 '/js/empresa/empresa.aviso.index.js')
		  );

		  $id = null;
		  $formRegistroRapido = new Application_Form_RegistroRapido(null);
		  $formRegistroRapido->validadorEmail($id);
		  $formRegistroRapido->validadorRuc($id);
		  $formRegistroRapido->validadorRazonSocial($id);

		  $mvc = Zend_Layout::getMvcInstance();
		  $mvc->loginForm->return->setValue('/empresa/publica-aviso-destacado/paso2');

		  if ($this->_getParam('extiende') != "") {
				$extiende = $this->_getParam('extiende', false);
				if (!$this->_helper->AvisoDestacado->perteneceAvisoAEmpresa(
						  $extiende, $this->auth['empresa']['id']
					 )) {
					 throw new App_Exception_Permisos();
				}
		  }

		  if (isset($extiende) && $extiende != '') {
				$this->view->extiende = $extiende;
		  }
		  if ($this->_getParam('republica')) {
				$this->getMessenger()->error('No esta permitido republicar un aviso.');
				$this->_redirect($_SERVER['HTTP_REFERER']);
				$republica = $this->_getParam('republica');
				$this->view->republica = $republica;
		  }

		  Zend_Layout::getMvcInstance()->assign(
				'registrorapido', $formRegistroRapido
		  );
	 }

	 public function paso1Action()
	 {
		  Zend_Layout::getMvcInstance()->assign(
				'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
		  );
		  $this->view->headTitle()->set(
				'Paso 1 - Elige tu aviso, Publica tu aviso | AquiEmpleos'
		  );
		  $this->view->headMeta()->appendName(
				"Description",
				"Elige tu aviso, primer paso para la publicación de tu aviso en aquiempleos.com." .
				" Los Clasificados de Empleos de La Prensa."
		  );

		  $fchPubsImp['aptitus'] = $this->_helper->Aviso->getFechaPublicacionImpresoByPaquete('aptitus');
		  $fchPubsImp['talan'] = $this->_helper->Aviso->getFechaPublicacionImpresoByPaquete('talan');
		  $fchPubsImp['combo'] = $this->_helper->Aviso->getFechaPublicacionImpresoByPaquete('combo');
		  $this->view->fchPubsImp = $fchPubsImp;


		  $fchCierreImp['aptitus'] = $this->_helper->Aviso->getFechaCierreImpresoByPaquete('aptitus');
		  $fchCierreImp['talan'] = $this->_helper->Aviso->getFechaCierreImpresoByPaquete('talan');
		  $fchCierreImp['combo'] = $this->_helper->Aviso->getFechaCierreImpresoByPaquete('combo');

		  $this->view->fchCierreImp = $fchCierreImp;

		  $modelProducto = new Application_Model_Producto();
		  for ($i = 1; $i < 4; $i++) {
				$idProd = 1 + $i;
				$arrayClasificado[] = $modelProducto->getInformacionAvisoClasificado($idProd);
		  }

		  $this->view->arrayClasificado = $arrayClasificado;
		  $this->view->moneda = $this->_config->app->moneda;


		  $session = $this->getSession();
		  if ($this->_getParam('extiende') != "") {
				$this->view->extiende = $extiende = $this->_getParam('extiende',
					 false);
				if (!$this->_helper->AvisoDestacado->perteneceAvisoAEmpresa(
						  $extiende, $this->auth['empresa']['id']
					 )) {
					 throw new App_Exception_Permisos();
				}
		  }
		  if ($this->_getParam('republica')) {
				$this->view->republica = $republica = $this->_getParam('republica');
		  }
		  $id = null;
		  $formRegistroRapido = new Application_Form_RegistroRapido(null);
		  $formRegistroRapido->validadorEmail($id);
		  $formRegistroRapido->validadorRuc($id);
		  $formRegistroRapido->validadorRazonSocial($id);

		  if ($this->getRequest()->isPost()) {
				$dataPost = $this->_getAllParams();
				$session->producto = $dataPost['id_tarifa'];
				if (isset($extiende) && $extiende != "") {
					 $this->_redirect(
						  '/empresa/publica-aviso-destacado/paso2/tarifa/' .
						  $dataPost['id_tarifa'] . '/extiende/' . $extiende
					 );
				}
				if (isset($republica) && $republica != "") {
					 $this->_redirect(
						  '/empresa/publica-aviso-destacado/paso2/tarifa/' .
						  $dataPost['id_tarifa'] . '/republica/' . $republica
					 );
				}
				$this->_redirect(
					 '/empresa/publica-aviso-destacado/paso2/tarifa/' .
					 $dataPost['id_tarifa']
				);
		  }

		  $mvc = Zend_Layout::getMvcInstance();
		  $mvc->loginForm->return->setValue('/empresa/publica-aviso-destacado/paso2');
		  $this->view->slide = $this->_getParam('slide', 1);
		  $this->view->headScript()->appendFile(
				$this->view->S(
					 '/js/empresa/empresa.aviso.paso1.js')
		  );

		  Zend_Layout::getMvcInstance()->assign(
				'registrorapido', $formRegistroRapido
		  );
	 }

	 public function paso2Action()
	 {
		  $session = $this->getSession();

		  if ($this->_getParam('id_tarifa') != null) {
				$tarifaId = $this->_getParam('id_tarifa');
		  } elseif ($session->tarifa != null) {
				$tarifaId = $session->tarifa;
		  } else {
				$tarifaId = $this->_getParam('tarifa');
		  }

		  $tarifaId = $this->_tarifaId;
		  $idEmpresa = $this->auth['empresa']['id'];
		  $idUsua=$this->auth["usuario"]->id;

		  $this->view->tarifa = $tarifaId;

		  Zend_Layout::getMvcInstance()->assign(
				'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
		  );
		  $this->view->headTitle()->set(
				'Paso 2- Ingrese el perfil del puesto, Publica tu aviso en  AquiEmpleos'
		  );
		  $this->view->headMeta()->appendName(
				"Description",
				"Ingrese el Perfil del puesto, segundo paso para la publicación de " .
				"tu aviso en aquiempleos.com.  Los Clasificados de Empleos de La Prensa."
		  );

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

		  if ($this->_getParam('extiende') != "") {
				$extiende = $this->_getParam('extiende', false);
				if (!$this->_helper->Aviso->perteneceAvisoAEmpresa(
						  $extiende, $this->auth['empresa']['id']
					 )) {
					 throw new App_Exception_Permisos();
				}
		  }
		  if ($this->_getParam('republica') != "") {
				$republica = $this->_getParam('republica');
		  }
		  if ($tarifaId == null && !$this->_getParam('id_producto')) {
				$this->_redirect('/empresa/publica-aviso/paso1');
		  }
		  $this->view->headScript()->appendFile($this->view->S('/js/empresa/empresa.aviso.paso2.js'));


		  $config = Zend_Registry::get("config");
		  // @codingStandardsIgnoreStart
		  $this->view->numPalabraPuesto = $config->avisopaso2->puestonumeropalabra;
		  $this->view->numPalabraOtroNombre = $config->avisopaso2->mostrarnombrenumeropalabra;
		  //@codingStandardsIgnoreEnd

		  $frmUbigeo = new Application_Form_Ubigeo();
		  $frmUbigeo->detalleUbigeo($this->auth['empresa']['id_ubigeo']);
		  $formDatos = new Application_Form_Paso2PublicarAviso();
		  //@codingStandardsIgnoreStart
		  $formDatos->id_tarifa->setValue($tarifaId);
		  //@codingStandardsIgnoreEnd

		  if ($tarifaId == 169) {
				$formDatos->removeTipoPuesto();
		  }

		  $baseFormEstudio = new Application_Form_Paso2EstudioPublicar();
		  $managerEstudio =
				new App_Form_Manager($baseFormEstudio, 'managerEstudio');

		  $baseFormExperiencia = new Application_Form_Paso2ExperienciaPublicar();
		  $managerExperiencia =
				new App_Form_Manager($baseFormExperiencia, 'managerExperiencia');

		  $baseFormOtroEstudio = new Application_Form_Paso2OtroEstudioPublicar();
		  $managerOtroEstudio = new App_Form_Manager($baseFormOtroEstudio, 'managerOtroEstudio');

		  $baseFormIdioma = new Application_Form_Paso2Idioma();
		  $managerIdioma =
				new App_Form_Manager($baseFormIdioma, 'managerIdioma');

		  $baseFormPrograma = new Application_Form_Paso2Programa();
		  $managerPrograma =
				new App_Form_Manager($baseFormPrograma, 'managerPrograma');

		  $baseFormPregunta = new Application_Form_Paso2PreguntaPublicar();
		  $managerPregunta =
				new App_Form_Manager($baseFormPregunta, 'managerPregunta');

		  $formEstudio = array();
		  $formOtroEstudio = array();
		  $formExperiencia = array();
		  $formIdioma = array();
		  $formPrograma = array();
		  $formPregunta = array();

		  if ($this->getRequest()->isPost()) {
				$postData = $this->_getAllParams();

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
				//@codingStandardsIgnoreStart
				$postData["funciones"] =
					 preg_replace($config->avisopaso2->expresionregular, '',
					 $postData["funciones"]);
				$postData["responsabilidades"] =
					 preg_replace($config->avisopaso2->expresionregular, '',
					 $postData["responsabilidades"]);
				//@codingStandardsIgnoreEnd

				$postData["funciones"] = str_replace("@", "", $postData["funciones"]);
				$postData["responsabilidades"] = str_replace("@", "",
					 $postData["responsabilidades"]);

				unset($session->tarifa);

				$validEstudio = $managerEstudio->isValid($postData);
				$validOtroEstudio = $managerOtroEstudio->isValid($postData);
				$validExperiencia = $managerExperiencia->isValid($postData);
				$validIdioma = $managerIdioma->isValid($postData);
				$validPrograma = $managerPrograma->isValid($postData);
				$validPregunta = $managerPregunta->isValid($postData);
				$valubigeo= $frmUbigeo->isValid($postData);

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
					 $managerPrograma->isValid($postData)
				) {
					  $postData['id_usuario']=$idUsua;

					 // @codingStandardsIgnoreStart
					 $avisoHelper = $genPassword = $this->_helper->getHelper('AvisoDestacado');
					 $util = $this->_helper->getHelper('Util');
					 $postData['id_ubigeo'] = $util->getUbigeo($postData);

					 $modelAviso = new Application_Model_AnuncioWeb;
					 //$dataEmpresa = $modelAviso->prioridadEmpresaAvisoDestacado($this->auth['empresa']['id']);
					 //$prioridad = $dataEmpresa['prioridad'];

					 $postData['prioridad'] = 1;


					 // @codingStandardsIgnoreEnd
					 if (isset($extiende)) {
						  $avisoId = $avisoHelper->_insertarNuevoPuesto($postData,
								$extiende);
						  $usuario = $this->auth['usuario'];
						  $this->_helper->Aviso->extenderAviso($avisoId, $usuario->id);
						  $this->_helper->Aviso->extenderReferidos($avisoId);
					 } elseif (isset($republica)) {
						  $avisoId = $avisoHelper->_insertarNuevoPuesto($postData,
								$republica, "", '1');
					 } else {
						  $avisoId = $avisoHelper->_insertarNuevoPuesto($postData);
					 }

					 $usuarioEmpresa = $this->auth['usuario-empresa'];

					 $anuncioUsuarioEmpresaModelo =
								new Application_Model_AnuncioUsuarioEmpresa;

					 $servicio = new App_Service_Validate_UserCompany;
					 if (!$servicio->isCreator($usuarioEmpresa)) {
						  $anuncioUsuarioEmpresaModelo->asignar(
								$usuarioEmpresa['id'], $avisoId);
					 }

					 $avisoHelper->_insertarPreguntas($managerPregunta);
					 $avisoHelper->_insertarEstudios($managerEstudio);
					 $avisoHelper->_insertarOtrosEstudios($managerOtroEstudio);
					 $avisoHelper->_insertarExperiencia($managerExperiencia);
					 $avisoHelper->_insertarIdiomas($managerIdioma);
					 $avisoHelper->_insertarPrograma($managerPrograma);
					 $params = "";

					 if (isset($extiende)) {
						  //Actualiza Match a Postulantes
						  $mPostulacion = new Application_Model_Postulacion;
						  $mPostulacion->actualizarMatchPostulantes($avisoId);
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
									 $avisoHelper->_SolrAviso->addAvisoSolr($infoAviso['id']);
									 //exec("curl -X POST -d 'api_key=" . $this->_buscamasConsumerKey . "&nid=" . $infoAviso['id'] . "&site=" . $this->_buscamasUrl . "' " . $this->_buscamasPublishUrl);
								}
						  }
					 }

					 if (isset($extiende) && $extiende != "") {
						  $params .= "/extiende/" . $extiende;
					 }
					 if (isset($republica) && $republica != "") {
						  $params .= "/republica/" . $republica;
					 }
					 $this->_redirect('/empresa/publica-aviso-destacado/paso3/aviso/' . $avisoId . $params);
				} else {
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
		  } elseif (isset($republica) && $republica != "") {
				$aviso = new Application_Model_AnuncioWeb();
				$datosAviso = $aviso->getAvisoInfoById($republica);
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
				unset($session->tarifa);
				$datosAvisoEstudio = $aviso->getEstudioInfoByAnuncio($republica);
				$i = 0;
				if (count($datosAvisoEstudio) > 0) {
					 $this->view->isEstudio = false;
					 foreach ($datosAvisoEstudio as $d) {
						  $form = $managerEstudio->getForm($i++, $d);
						  if (isset($d['id_carrera'])) {
								$carrera = new Application_Model_Carrera();
								$idTipoCarrera = $carrera->getTipoCarreraXCarrera($d['id_carrera']);
								$carreras = $carrera->filtrarCarrera($idTipoCarrera);
								$form->getElement('id_carrera')->addMultioptions($carreras);
								$form->getElement('otra_carrera')->setValue($d['otra_carrera']);
						  }
						  $formEstudio[] = $form;
					 }
				} else {
					 $form = $managerEstudio->getForm($i++);
					 $formEstudio[] = $form;
				}

				$datosAvisoExperiencia =
					 $aviso->getExperienciaInfoByAnuncio($republica);

				$i = 0;
				if (count($datosAvisoExperiencia) > 0) {
					 $this->view->isExperiencia = false;
					 foreach ($datosAvisoExperiencia as $d) {
						  $form = $managerExperiencia->getForm($i++, $d);
						  $formExperiencia[] = $form;
					 }
				} else {
					 $form = $managerExperiencia->getForm($i++);
					 $formExperiencia[] = $form;
				}

				$datosAvisoIdioma = $aviso->getIdiomaInfoByAnuncio($republica);

				$i = 0;
				if (count($datosAvisoIdioma) > 0) {
					 $this->view->isIdioma = false;
					 foreach ($datosAvisoIdioma as $d) {
						  $form = $managerIdioma->getForm($i++, $d);
						  $formIdioma[] = $form;
					 }
				} else {
					 $form = $managerIdioma->getForm($i++);
					 $formIdioma[] = $form;
				}

				$datosAvisoPrograma = $aviso->getProgramaInfoByAnuncio($republica);

				$i = 0;
				if (count($datosAvisoPrograma) > 0) {
					 $this->view->isPrograma = false;
					 foreach ($datosAvisoPrograma as $d) {
						  $form = $managerPrograma->getForm($i++, $d);
						  $formPrograma[] = $form;
					 }
				} else {
					 $form = $managerPrograma->getForm($i++);
					 $formPrograma[] = $form;
				}

				$datosAvisoPregunta = $aviso->getPreguntaInfoByAnuncio($republica);

				$i = 0;
				if (count($datosAvisoPregunta) > 0) {
					 $this->view->isPregunta = false;
					 foreach ($datosAvisoPregunta as $d) {
						  $form = $managerPregunta->getForm($i++, $d);
						  $formPregunta[] = $form;
					 }
				} else {
					 $form = $managerPregunta->getForm($i++);
					 $formPregunta[] = $form;
				}
		  } elseif (isset($extiende) && $extiende != "") {
				$aviso = new Application_Model_AnuncioWeb();
				$datosAviso = $aviso->getAvisoInfoById($extiende);
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
				unset($session->tarifa);
				$datosAvisoEstudio = $aviso->getEstudioInfoByAnuncio($extiende);
				$i = 0;
				if (count($datosAvisoEstudio) > 0) {//echo "hjol";
					 $this->view->isEstudio = true;
					 foreach ($datosAvisoEstudio as $d) {
						  $form = $managerEstudio->getForm($i++, $d);
						  if (isset($d['id_carrera'])) {
								$carrera = new Application_Model_Carrera();
								$idTipoCarrera = $carrera->getTipoCarreraXCarrera($d['id_carrera']);
								$carreras = $carrera->filtrarCarrera($idTipoCarrera);
								$form->getElement('id_tipo_carrera')->setValue($idTipoCarrera);
								$form->getElement('id_carrera')->addMultioptions($carreras);
								$form->getElement('otra_carrera')->setValue($d['otra_carrera']);

								$form->setElementNivelEstudio($d['id_nivel_estudio']);
								$form->getElement('id_nivel_estudio_tipo')->setValue($d['id_nivel_estudio_tipo']);
						  }
						  $d['id_tipo_carrera']=$idTipoCarrera;
						  $form->isValid($d) ;
						  $formEstudio[] = $form;
					 }
				}
					 $form = $managerEstudio->getForm($i++);
					 $formEstudio[] = $form;

				$datosAvisoOtroEstudio = $aviso->getOtroEstudioInfoByAnuncio($extiende);

				$i = 0;
				if (count($datosAvisoOtroEstudio) > 0) {
					 $this->view->isOtroEstudio = true;
					 foreach ($datosAvisoOtroEstudio as $d) {
						  $form = $managerOtroEstudio->getForm($i++, $d);
						  $formOtroEstudio[] = $form;
					 }
				}
					 $form = $managerOtroEstudio->getForm($i++);
					 $formOtroEstudio[] = $form;

				$datosAvisoExperiencia =
					 $aviso->getExperienciaInfoByAnuncio($extiende);

				$i = 0;
				if (count($datosAvisoExperiencia) > 0) {
					 $this->view->isExperiencia = true;
					 foreach ($datosAvisoExperiencia as $d) {
						  $form = $managerExperiencia->getForm($i++, $d);
						  $formExperiencia[] = $form;
					 }
				}
					 $form = $managerExperiencia->getForm($i++);
					 $formExperiencia[] = $form;

				$datosAvisoIdioma = $aviso->getIdiomaInfoByAnuncio($extiende);

				$i = 0;
				if (count($datosAvisoIdioma) > 0) {
					 $this->view->isIdioma = true;
					 foreach ($datosAvisoIdioma as $d) {
						  $form = $managerIdioma->getForm($i++, $d);
						  $formIdioma[] = $form;
					 }
				}
					 $form = $managerIdioma->getForm($i++);
					 $formIdioma[] = $form;

				$datosAvisoPrograma = $aviso->getProgramaInfoByAnuncio($extiende);

				$i = 0;
				if (count($datosAvisoPrograma) > 0) {
					 $this->view->isPrograma = true;
					 foreach ($datosAvisoPrograma as $d) {
						  $form = $managerPrograma->getForm($i++, $d);
						  $formPrograma[] = $form;
					 }
				}
					 $form = $managerPrograma->getForm($i++);
					 $formPrograma[] = $form;

				$datosAvisoPregunta = $aviso->getPreguntaInfoByAnuncio($extiende);

				$i = 0;
				if (count($datosAvisoPregunta) > 0) {
					 $this->view->isPregunta = true;
					 foreach ($datosAvisoPregunta as $d) {
						  $form = $managerPregunta->getForm($i++, $d);
						  $formPregunta[] = $form;
					 }
				}
					 $form = $managerPregunta->getForm($i++);
					 $formPregunta[] = $form;
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

		  if ($tarifaId != 169) {
				if ((isset($extiende) && $formDatos->getElement('id_puesto')->getValue()
					 == '1292') ||
					 (isset($republica) && $formDatos->getElement('id_puesto')->getValue()
					 == '1292')) {
					 $formDatos->getElement('id_puesto')->setValue('-1');
				}
		  }

		  if ($this->auth['empresa']['logo'] == '') {
				$this->view->frmEmpresa = $frmEmpresa;
		  }

		  $this->view->idAnunciante = $this->auth['usuario-empresa']['id_usuario'];

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

	 public function paso3Action()
	 {
		  $avisoId = $this->_getParam('aviso');
		  $sessionDatosPasarela = new Zend_Session_Namespace('facturaDatos');

			if (!$this->_helper->Aviso->perteneceAvisoAEmpresa($avisoId,
					  $this->auth['empresa']['id'])) {
				 throw new App_Exception_Permisos();
			}

			if ($this->_getParam('error') == 1) {
				 $this->getMessenger()->error(
					  'El Pago no se procesó correctamente. Intente nuevamente en unos minutos,
					  de lo contario, consulte con el Administrador del Sistema.'
				 );
			}

			$this->view->headTitle()->set('Paso 3 - Pague su Aviso, Publica tu aviso en AquiEmpleos');
			$this->view->headMeta()->appendName(
				 "Description",
				 "Pague su Aviso, cuarto paso para la publicación de tu aviso en aquiempleos.com." .
				 " Los Clasificados de Empleos de La Prensa."
			);

			if ($avisoId == null) {
				 $this->_redirect('/empresa/publica-aviso/paso1');
			}
		  $this->view->headLink()->appendStylesheet(
				$this->view->S(
					 '/js/datepicker/themes/redmond/ui.all.css', 'all')
		  );
			$this->view->headScript()->appendFile(
				 $this->view->S(
					  '/js/empresa/empresa.aviso.paso4.js')
			);
			Zend_Layout::getMvcInstance()->assign('bodyAttr',
				 array('id' => 'perfilReg', 'class' => 'noMenu'));
			$this->_aw = new Application_Model_AnuncioWeb();
			$rowAnuncio = $this->_aw->getDatosPagarAnuncio($avisoId);

			$medioPublicacion = $rowAnuncio['medioPublicacion'];

			if ($rowAnuncio['medioPublicacion'] == 'aptitus y talan') {
				 $medioPublicacion = 'combo';
			}

			if ((int) $rowAnuncio['tarifaPrecio'] <= 0) {
				 $rowAnuncio = $this->_aw->getDatosGenerarCompra($avisoId);
				 $rowAnuncio['totalPrecio'] = 0;
				 $rowAnuncio['tipoDoc'] = '';
				 $rowAnuncio['tipoPago'] = 'gratuito';
				 $usuario = $this->auth['usuario'];
				 $rowAnuncio['usuarioId'] = $usuario->id;
				 $compraId = $this->_helper->AvisoDestacado->generarCompraAnuncio($rowAnuncio);
				 $this->_helper->AvisoDestacado->confirmarCompraAvisoDestacado($compraId, 0);
				 $this->_redirect('/empresa/comprar-aviso/pago-satisfactorio/compra/' . $compraId);
			}

			if ($rowAnuncio['estadoCompra'] == 'pagado') {
				 $this->_redirect('/empresa/publica-aviso/paso1');
			}
			$cierre = $this->config->cierre->toArray();
			$fecImpre = new Zend_Date();
			$fecImpre->setLocale(Zend_Locale::ZFDEFAULT);
			$fecImpre->set($cierre[$medioPublicacion]['dia'],
				 Zend_Date::WEEKDAY_DIGIT);
			$fecImpre->set($cierre[$medioPublicacion]['hora'], Zend_Date::HOUR);
			$fecImpre->set(0, Zend_Date::MINUTE);
			$fecImpre->set(0, Zend_Date::SECOND);
			$this->view->fhCierre = $fecImpre->toString('EEEE d MMMM / h:m a');
			$fecCierre = clone $fecImpre;
			$now = date('Y-m-d H:i:s');
			$fecImpre->set(0, Zend_Date::HOUR);
			if ($fecCierre->isEarlier($now, 'YYYY-MM-dd h:m:s')) {
				 $fecCierre->add(7, Zend_Date::DAY);
				 $fecImpre->add(7, Zend_Date::DAY);
			}

		  //Actualizar ente en APT si en Adecsys es diferente, siempre y cuando exista en ADECSYS Y EN APT
		  $adecsysValida = $this->_helper->getHelper('AdecsysValida');
		  $tipoDoc = Application_Model_AdecsysEnte::DOCUMENTO_RUC;
		  $ruc = $this->auth['empresa']['ruc'];
		  $adecsysValida->compareEnte($tipoDoc,$ruc);

			$this->view->fhCierre = $this->_helper->Aviso->getFechaCierreImpresoByPaquete($medioPublicacion);

			if ($cierre[$medioPublicacion]['semanaActual'] == 0) {
				 $fecImpre->add(7, Zend_Date::DAY);
			}
			$fecImpre->set($cierre[$medioPublicacion]['diaPublicacion'],
				 Zend_Date::WEEKDAY_DIGIT);
			$this->view->fechaImpreso = $fecImpre->toString('YYYY-MM-dd');

			$fechaVencimiento = new Zend_Date($rowAnuncio['fechaCreacion'],
				 'YYYY-MM-dd', Zend_Locale::ZFDEFAULT);
			$fechaVencimiento->add('15', Zend_Date::DAY);
			$this->view->fechaCierreWeb = $fechaVencimiento->toString('YYYY-MM-dd');

			$rowAnuncio['tipo_paquete'] = strtoupper(trim(str_replace('Clasificado', '', $rowAnuncio['nombreProducto'])));
			$rowAnuncio['nombre_tipo_paquete'] = 'Clasificado'.strtoupper(trim(str_replace('Clasificado', '', $rowAnuncio['nombreProducto'])));

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
		  $this->view->Formfacturacion=$formFactura;


			$sessionAdmin = new Zend_Session_Namespace('admin');
			$this->view->isSessionAdmin = ($sessionAdmin->auth ? true : false);
			$this->view->dataAnuncio = $rowAnuncio;
			$this->view->medioPublicacion = $rowAnuncio['medioPublicacion'];
	 }

	 public function paso4Action()
	 {
		  $avisoId = $this->_getParam('aviso');
		  $sessionDatosPasarela = new Zend_Session_Namespace('facturaDatos');
		  if (!$this->_helper->Aviso->perteneceAvisoAEmpresa($avisoId,
					 $this->auth['empresa']['id'])) {
				throw new App_Exception_Permisos();
		  }

		  if ($this->_getParam('error') == 1) {
				$this->getMessenger()->error(
					 'El Pago no se procesó correctamente. Intente nuevamente en unos minutos,
					 de lo contario, consulte con el Administrador del Sistema.'
				);
		  }


		  $this->view->headTitle()->set('Paso 4 - Pague su Aviso, Publica tu aviso en AquiEmpleos');
		  $this->view->headMeta()->appendName(
				"Description",
				"Pague su Aviso, cuarto paso para la publicación de tu aviso en aquiempleos.com." .
				" Los Clasificados de Empleos de La Prensa."
		  );

		  if ($avisoId == null) {
				$this->_redirect('/empresa/publica-aviso/paso1');
		  }

		  $this->view->headScript()->appendFile(
				$this->view->S(
					 '/js/empresa/empresa.aviso.paso4.js')
		  );
		  Zend_Layout::getMvcInstance()->assign('bodyAttr',
				array('id' => 'perfilReg', 'class' => 'noMenu'));
		  $this->_aw = new Application_Model_AnuncioWeb();
		  $rowAnuncio = $this->_aw->getDatosPagarAnuncio($avisoId);

		  $medioPublicacion = $rowAnuncio['medioPublicacion'];

		  if ($rowAnuncio['medioPublicacion'] == 'aptitus y talan') {
				$medioPublicacion = 'combo';
		  }

		  if ((int) $rowAnuncio['tarifaPrecio'] <= 0) {
				$rowAnuncio = $this->_aw->getDatosGenerarCompra($avisoId);
				$rowAnuncio['totalPrecio'] = 0;
				$rowAnuncio['tipoDoc'] = '';
				$rowAnuncio['tipoPago'] = 'gratuito';
				$usuario = $this->auth['usuario'];
				$rowAnuncio['usuarioId'] = $usuario->id;
				$compraId = $this->_helper->AvisoDestacado->generarCompraAnuncio($rowAnuncio);
				$this->_helper->AvisoDestacado->confirmarCompraAvisoDestacado($compraId, 0);
				$this->_redirect('/empresa/comprar-aviso/pago-satisfactorio/compra/' . $compraId);
		  }

		  if ($rowAnuncio['estadoCompra'] == 'pagado') {
				$this->_redirect('/empresa/publica-aviso/paso1');
		  }
		  $cierre = $this->config->cierre->toArray();
		  $fecImpre = new Zend_Date();
		  $fecImpre->setLocale(Zend_Locale::ZFDEFAULT);
		  $fecImpre->set($cierre[$medioPublicacion]['dia'],
				Zend_Date::WEEKDAY_DIGIT);
		  $fecImpre->set($cierre[$medioPublicacion]['hora'], Zend_Date::HOUR);
		  $fecImpre->set(0, Zend_Date::MINUTE);
		  $fecImpre->set(0, Zend_Date::SECOND);
		  $this->view->fhCierre = $fecImpre->toString('EEEE d MMMM / h:m a');
		  $fecCierre = clone $fecImpre;
		  $now = date('Y-m-d H:i:s');
		  $fecImpre->set(0, Zend_Date::HOUR);
		  if ($fecCierre->isEarlier($now, 'YYYY-MM-dd h:m:s')) {
				$fecCierre->add(7, Zend_Date::DAY);
				$fecImpre->add(7, Zend_Date::DAY);
		  }

		  $this->view->fhCierre = $this->_helper->Aviso->getFechaCierreImpresoByPaquete($medioPublicacion);

		  if ($cierre[$medioPublicacion]['semanaActual'] == 0) {
				$fecImpre->add(7, Zend_Date::DAY);
		  }
		  $fecImpre->set($cierre[$medioPublicacion]['diaPublicacion'],
				Zend_Date::WEEKDAY_DIGIT);
		  $this->view->fechaImpreso = $fecImpre->toString('YYYY-MM-dd');

		  $fechaVencimiento = new Zend_Date($rowAnuncio['fechaCreacion'],
				'YYYY-MM-dd', Zend_Locale::ZFDEFAULT);
		  $fechaVencimiento->add('15', Zend_Date::DAY);
		  $this->view->fechaCierreWeb = $fechaVencimiento->toString('YYYY-MM-dd');

		  $rowAnuncio['tipo_paquete'] = strtoupper(trim(str_replace('Clasificado', '', $rowAnuncio['nombreProducto'])));
		  $rowAnuncio['nombre_tipo_paquete'] = 'Clasificado'.strtoupper(trim(str_replace('Clasificado', '', $rowAnuncio['nombreProducto'])));
		  $ruc = $this->auth['empresa']['ruc'];
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
		  $this->view->Formfacturacion=$formFactura;
		  $this->view->dataAnuncio = $rowAnuncio;
		  $this->view->medioPublicacion = $rowAnuncio['medioPublicacion'];
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
