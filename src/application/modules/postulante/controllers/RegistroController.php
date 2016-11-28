<?php

class Postulante_RegistroController extends App_Controller_Action_Postulante
{

	 protected $_postulante;
	 protected $_usuario;
	 protected $_url;
	 protected $_config;
	 protected $_cache;

	 const TOLERANCIA_LEVENSHTEIN_IDIOMA = 5;
	 const TOLERANCIA_LEVENSHTEIN_CARRERA = 3;
	 const TOLERANCIA_LEVENSHTEIN_NIVEL_ESTUDIO = 3;
	 const TOLERANCIA_LEVENSHTEIN_INST = 6;

	 protected $_pswd;
	 protected $_messageError;
	 protected $_logError;

	 public function init()
	 {
		  parent::init();
		  $this->_config = Zend_Registry::get('config');
		  $this->_messageError = 'Acceso denegado';
		  $this->_postulante = new Application_Model_Postulante();
		  $this->_usuario = new Application_Model_Usuario();
		  $this->_url = '/registro/paso2';
		  $this->idPostulante = null;
		  $this->_cache = Zend_Registry::get('cache');
		  Zend_Layout::getMvcInstance()->assign(
					 'bodyAttr', array(
				'id' => 'perfilReg',
				'class' => 'noMenu')
		  );
		  $this->view->headMeta()->appendName(
					 "Keywords", "Registra tu CV,postula a un trabajo, " .
					 "ingresa tu cv  pasos para postular , " .
					 "paso para registrarte, portal de empleos"
		  );

		  $this->_logError = Zend_Registry::get('log');
	 }

	 public function indexAction()
	 {
		  return $this->_redirect('/');
		  //$this->_forward('paso1');
	 }

	 public function registroRapidoAction()
	 {

		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();

		  if ($this->getRequest()->isPost()) {
				$valuesUsuario = array();
				$valuesPostulante = array();

				$idUsuAdmin = isset($this->auth['usuario']->id) ? $this->auth['usuario']->id : 0;
				$formRegistroRapido = new Application_Form_RegistroRapidoPostulante($idUsuAdmin);

				$registro = $this->_getAllParams();

				if ($formRegistroRapido->isValid($registro)) {

					 // registro rapido
					 $db = $this->getAdapter();
					 $db->beginTransaction();
					 $postulante = new Application_Model_Postulante();
					 $usuario = new Application_Model_Usuario();

					 try {

						  $date = date('Y-m-d H:i:s');
						  $valuesUsuario = array(
								'salt' => '',
								'rol' => Application_Form_Login::ROL_POSTULANTE,
								'activo' => 1,
								'ultimo_login' => $date,
								'fh_registro' => $date,
								'fh_edicion'=>$date,
								'modificado_por'=>0,
								'pswd' => App_Auth_Adapter_AptitusDbTable::generatePassword($registro['pswd']),
								'ip' =>  $_SERVER["REMOTE_ADDR"],
								'confirmar' => 0,
								'email' => trim($registro['txtEmail'])
						  );

						  $lastId = $usuario->insert($valuesUsuario);

						  $valuesPostulante['nombres'] = $registro['txtName'];
						  $valuesPostulante['apellido_paterno'] = $registro['txtFirstLastName'];
						  $valuesPostulante['apellido_materno'] = $registro['txtSecondLastName'];
						  $valuesPostulante['slug'] = $this->_helper->Util->_crearSlug($valuesPostulante, $lastId, $postulante);
						  $registro['txtBirthDay'] = str_replace('/', '-', $registro['txtBirthDay']);
						  $valuesPostulante['fecha_nac'] = date('Y-m-d', strtotime(
												date('Y', strtotime($registro['txtBirthDay'])) . '-' .
												date('m', strtotime($registro['txtBirthDay'])) . '-' .
												date('d', strtotime($registro['txtBirthDay']))));

						  $valuesPostulante['id_usuario'] = $lastId;
						  $valuesPostulante['ultima_actualizacion'] = $date;
						  $valuesPostulante['tipo_doc'] = null;
						  $valuesPostulante['apellidos'] = $valuesPostulante['apellido_paterno'] . ' ' . $valuesPostulante['apellido_materno'];
						  $valuesPostulante['id_ubigeo'] = Application_Model_Ubigeo::PERU_UBIGEO_ID;

						  $lastIdPostulante = $postulante->insert($valuesPostulante);

						  if ($lastId && $lastIdPostulante) {
								$db->commit();
						  } else {
								$formRegistroRapido->getElement('auth_token')->initCsrfToken();
								return array(
									 'message' => 'Ocurrio un error al registrar. Intentelo nuevamente'.$lastId .'-'. $lastIdPostulante,
									 'status' => 0,
									 'hashToken' => $formRegistroRapido->getElement('auth_token')->getValue()
								);
								$db->rollBack();
								Zend_Auth::getInstance()->clearIdentity();
								Zend_Session::forgetMe();

								$this->_logError->log('ID: ' . $lastId . ' - ' . $lastIdPostulante, Zend_Log::CRIT);
						  }
					 } catch (Exception $exc) {
						  $db->rollBack();
						  var_dump($exc->getMessage().'-'.$exc->getTraceAsString());exit;
						  $formRegistroRapido->getElement('auth_token')->initCsrfToken();
						  return array(
								'message' => $exc->getTraceAsString(),
								'status' => 0,
								'hashToken' => $formRegistroRapido->getElement('auth_token')->getValue()
						  );
						  $this->_logError->log('Trace: ' . $exc->getTraceAsString(), Zend_Log::CRIT);
					 }

					 if ($lastId && $lastIdPostulante) {
						  $config = $this->getConfig();
						  Application_Model_Usuario::auth($valuesUsuario['email'], $registro['pswd'], $valuesUsuario['rol']);
						  $token = Application_Model_Usuario::generarToken($lastId, $config->app->tokenUserConfirma);
						  $codifica = $this->_helper->Util->codifica($token);

//                    $this->_helper->Mail->confirmaCuentaPostulante(array(
//                        'to' => $valuesUsuario['email'],
//                        'nombre' => $valuesPostulante['nombres'],
//                        'slug' => $valuesPostulante['slug'],
//                        'urlToken' => SITE_URL . '/activar-cuenta/' . $codifica
//                    ));
						  $this->_helper->mail->nuevoUsuario(
									 array(
										  'to' => $valuesUsuario['email'],
										  'user' => $valuesUsuario['email'],
										  'fr' => date('Y-m-d H:i:s'),
										  'slug' => $valuesPostulante['slug'],
										  'nombre' => ucwords($valuesPostulante['nombres']),
										  'subjectMessage' => "Bienvenido(a)"
									 )
						  );

//                  $this->getMessenger()->success('En breves momentos le llegará un correo para confirmar su cuenta y pueda ser parte del proceso de selección de postulantes.');
						  $response = array(
								'message' => 'En breves momentos le llegará un correo para confirmar su cuenta y pueda ser parte del proceso de selección de postulantes.',
								'status' => 1,
								'redirect' => '/registro/paso2'
						  );

//                  $this->getMessenger()->success('En breves momentos le llegará un correo para confirmar su cuenta y pueda ser parte del proceso de selección de postulantes.');
//                  $registro = $this->_helper->Util->ZessionRegistro('registro', true);
//                  $this->_redirect('/registro/paso2');
					 } else {
						  $formRegistroRapido->getElement('auth_token')->initCsrfToken();
						  $response = array(
								'message' => 'Ocurrio un error al registrar. Intentelo nuevamente',
								'status' => 0,
								'hashToken' => $formRegistroRapido->getElement('auth_token')->getValue()
						  );
					 }
				} else {
					 $formRegistroRapido->getElement('auth_token')->initCsrfToken();
					 $response = array(
						  'message' => $formRegistroRapido->getMensajesErrors($formRegistroRapido),
						  'status' => 0,
						  'hashToken' => $formRegistroRapido->getElement('auth_token')->getValue()
					 );
				}
		  } else {
				$response = array(
					 'message' => 'No se han recibido parametros',
					 'status' => 0
				);
		  }

		  $this->_response->appendBody(Zend_Json::encode($response));
	 }

	 public function paso1Action()
	 {
		  $this->_redirect('/');
		  $config = $this->getConfig();
		  $util = new App_Util();
		  $formatSize = $util->formatSizeUnits($config->app->maxSizeFile);
		  $config->formatSize = $formatSize;
		  $this->view->config = $config;
		  $this->view->modulo = $this->getRequest()->getModuleName();
		  $this->view->controlador = $this->getRequest()->getControllerName();
		  $this->view->headTitle()->set('Paso 1 - Registra Tus datos, Regístrate en  AquiEmpleos');
		  $this->view->headMeta()->appendName("Description", "Ingresa tus datos, primer paso para el registro en aquiempleos.com." .
					 " Los Clasificados de Empleos de La Prensa.");
		  $this->view->idPostulante = $id = $this->idPostulante;


		  if (Zend_Auth::getInstance()->hasIdentity()) {
				$this->_redirect('/registro/paso1-modificar');
		  }

		  $this->view->mes = array(
				'01' => 'Enero',
				'02' => 'Febrero',
				'03' => 'Marzo',
				'04' => 'Abril',
				'05' => 'Mayo',
				'06' => 'Junio',
				'07' => 'Julio',
				'08' => 'Agosto',
				'09' => 'Septiembre',
				'10' => 'Octubre',
				'11' => 'Noviembre',
				'12' => 'Diciembre');

		  $idUsuario = null;
		  $img = $this->view->imgPhoto = '';

		  $formPostulante = new Application_Form_Paso1Postulante($id);
		  $formUsuario = new Application_Form_Paso1Usuario($idUsuario);

		  $ubigeo = new Application_Model_Ubigeo();
		  $arrayUbigeo = $ubigeo->getHijos(Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID);
		  $formPostulante->getElement('id_distrito')->addMultioptions($arrayUbigeo);

		  //validadores del email y Dni para que no se repitan
		  $formUsuario->validadorEmail($idUsuario);
		  $id = null;
		  $formPostulante->validadorNumDoc($id);
		  $valPostUbigeo = '';
		  if ($this->_request->isPost()) {
				$allParams = $this->_getAllParams();

				$fechaNac = $allParams['selDia'] . "/" . $allParams['selMes'] . "/" . $allParams['selAnio'];
				$allParams['fecha_nac'] = $fechaNac;

				$validPostulante = $formPostulante->isValid($allParams);

				//$validUsuario = $formUsuario->isValid($allParams);

				if (isset($allParams['id_provincia'])) {
					 $valPostUbigeo = $allParams['id_provincia'];
				}


				if ($validPostulante && count($_FILES) > 0) {
					 $utilfile = $this->_helper->getHelper('UtilFiles');
					 $nuevosNombres = $utilfile->_renameFile($formPostulante, 'path_foto');
					 $valuesUsuario = $formUsuario->getValues();

					 $valuesPostulante = $formPostulante->getValues();
					 $date = date('Y-m-d H:i:s');

					 $db = $this->getAdapter();
					 $db->beginTransaction();


					 try {

						  // Datos adicionales q no vienen del form
						  $pswd = $valuesUsuario['pswd'];
						  //
						  $valuesUsuario['salt'] = '';
						  $valuesUsuario['rol'] = Application_Form_Login::ROL_POSTULANTE;
						  $valuesUsuario['activo'] = 1;
						  $valuesUsuario['ultimo_login'] = $date;
						  $valuesUsuario['fh_registro'] = $date;
						  $valuesUsuario['pswd'] = App_Auth_Adapter_AptitusDbTable::generatePassword(
												$valuesUsuario['pswd']
						  );
						  $valuesUsuario['ip'] = $this->getRequest()->getServer('REMOTE_ADDR');
						  unset($valuesUsuario['pswd2']);
						  //unset($valuesUsuario['auth_token']);

						  if (isset($valuesUsuario['auth_token'])) {
								unset($valuesUsuario['auth_token']);
						  }

						  $valuesUsuario['email'] = trim($valuesUsuario['email']);
						  $lastId = $this->_usuario->insert($valuesUsuario);

						  $slug = $this->_crearSlug($valuesPostulante, $lastId);

						  //Captura de los valores del Postulante
						  if ($valuesPostulante['website'] ==
									 Application_Form_Paso1Postulante::$_defaultWebsite) {
								unset($valuesPostulante['website']);
						  }
						  if ($valuesPostulante['presentacion'] ==
									 Application_Form_Paso1Postulante::$_defaultPresentacion) {
								unset($valuesPostulante['presentacion']);
						  }

						  $valorTipoDoc = explode('#', $valuesPostulante['tipo_doc']);
						  $valuesPostulante['tipo_doc'] = $valorTipoDoc[0];

						  $valuesPostulante['id_ubigeo'] = $this->_helper->Util->getUbigeo($valuesPostulante);
						  $valuesPostulante['id_usuario'] = $lastId;
						  $valuesPostulante['pais_nacionalidad'] = $valuesPostulante['pais_residencia'];
						  $valuesPostulante['fecha_nac'] = date(
									 'Y-m-d', strtotime(str_replace('/', '-', $valuesPostulante['fecha_nac']))
						  );

						  $valuesPostulante['sexo'] = $valuesPostulante['sexoMF'];
						  $valuesPostulante['disponibilidad_mudarse'] = '0';
						  $valuesPostulante['prefs_confidencialidad'] = '0';
						  $valuesPostulante['prefs_emailing_avisos'] = '0';
						  $valuesPostulante['prefs_emailing_info'] = '0';
						  $valuesPostulante['ultima_actualizacion'] = $date;
						  $valuesPostulante['slug'] = $slug;
						  if ($valuesPostulante['path_foto'] == NULL) {
								$valuesPostulante['path_foto'] = $img;
								$valuesPostulante['path_foto1'] = $img;
								$valuesPostulante['path_foto2'] = $img;
						  } else {
								$valuesPostulante['path_foto'] = $nuevosNombres[0];
								$valuesPostulante['path_foto1'] = $nuevosNombres[1];
								$valuesPostulante['path_foto2'] = $nuevosNombres[2];
						  }
						  unset($valuesPostulante['sexoMF']);
						  unset($valuesPostulante['id_departamento']);
						  unset($valuesPostulante['id_provincia']);
						  unset($valuesPostulante['id_distrito']);
						  $lastIdPostulante = $this->_postulante->insert($valuesPostulante);
						  $db->commit();
					 } catch (Zend_Db_Exception $e) {
						  $db->rollBack();
						  echo $e->getMessage();
					 }


					 //  var_dump($lastIdPostulante);exit;
					 try {

						  $this->_helper->LogActualizacionBI->
									 logActualizacionPostulantePaso1($lastIdPostulante, $valuesPostulante);
						  //$this->_helper->solr->addSolr($lastIdPostulante);
						  if ($valuesPostulante['sexo'] == 'M') {
								$subjectMessage = 'Bienvenido';
						  } else {
								$subjectMessage = 'Bienvenida';
						  }

						  if (isset($this->auth['usuario']) && ($valuesUsuario['email'] != $this->auth['usuario']->email)) {
								$this->_helper->mail->nuevoUsuario(
										  array(
												'to' => $valuesUsuario['email'],
												'user' => $valuesUsuario['email'],
												'fr' => $date,
												'slug' => $slug,
												'nombre' => ucwords($valuesPostulante['nombres']),
												'subjectMessage' => $subjectMessage
										  )
								);
						  }
					 } catch (Zend_Exception $e) {
						  $this->getMessenger()->error($this->_messageSuccess);
						  echo $e->getMessage();
					 } catch (Exception $e) {
						  echo $e->getMessage();
					 }

					 if ($lastIdPostulante != null || $id != null) {
						  Application_Model_Usuario::auth(
									 $valuesUsuario['email'], $pswd, $valuesUsuario['rol']
						  );
						  $this->_redirect($this->_url);
					 }
				} else {

					 if ($valPostUbigeo == Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID) {
						  $arrayUbigeo = $ubigeo->getHijos(
									 Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID
						  );
						  $formPostulante->getElement('id_distrito')->clearMultiOptions();
						  $formPostulante->getElement('id_distrito')
									 ->addMultiOption('none', 'Seleccione Distrito');
						  $formPostulante->getElement('id_distrito')->addMultioptions($arrayUbigeo);
					 }
					 if ($valPostUbigeo == Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID) {
						  $arrayUbigeo = $ubigeo->getHijos(
									 Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID
						  );
						  $formPostulante->getElement('id_distrito')->clearMultiOptions();
						  $formPostulante->getElement('id_distrito')
									 ->addMultiOption('none', 'Seleccione Distrito');
						  $formPostulante->getElement('id_distrito')->addMultioptions($arrayUbigeo);
					 }
				}
		  }
		  $this->view->sexo = 'M';
		  $this->view->formUsuario = $formUsuario;
		  $this->view->formPostulante = $formPostulante;
	 }

	 public function validarEmailAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();

		  $email = $this->_getParam('email');
		  $idPostulante = $this->_getParam('rol', null);
		  $arrayPostulante = '';

		  if ($idPostulante != null) {
				$modelPostulante = new Application_Model_Postulante();
				$arrayPostulante = $modelPostulante->getPostulante($idPostulante);
				$id = $arrayPostulante['id_usuario'];
		  } elseif (Zend_Auth::getInstance()->hasIdentity() && $idPostulante == null) {
				$authData = Zend_Auth::getInstance()->getStorage()->read();
				$id = $authData['usuario']->id;
		  } else {
				$id = null;
		  }

		  $validator = new Zend_Validate_EmailAddress();
		  if ($validator->isValid($email)) {
				$_usuario = new Application_Model_Usuario();
				$module = $this->_getParam('modulo');
				$isValid = $_usuario->validacionEmail($email, null, $id, $module);

				$msg = 'Email ya existe';
				if ($isValid) {
					 $msg = 'Email correcto';
				}

				$data = array(
					 'status' => $isValid,
					 'msg' => $msg
				);
		  } else {
				$data = array(
					 'status' => 0,
					 'msg' => 'Email incorrecto'
				);
		  }

		  $this->_response->appendBody(Zend_Json::encode($data));
	 }

	 public function validarDniAction()
	 {

		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();

		  $ndoc = $this->_getParam('ndoc');
		  $idPost = $this->_getParam('idPost');
		  $isValid = '';

		  if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

				$ndoc = $this->_getParam('ndoc');
				$idPost = $this->_getParam('idPost');
				$token = $this->_getParam('token');
				$isValid = '';


				if ($this->_hash->isValid($token)) {
					 if ($idPost != null) {
						  $isValid = $this->_usuario->validacionNDoc($ndoc, null, $idPost);
					 } else {
						  $isValid = $this->_usuario->validacionNDoc($ndoc, null, $this->auth["postulante"]["id"]);
					 }
				} else {
					 exit(0);
				}
				$msg = 'CI ya existe';
				if ($isValid) {
					 $msg = 'CI correcto';
				}

				$data = array(
					 'status' => $isValid,
					 'msg' => $msg
				);
				$this->_response->appendBody(Zend_Json::encode($data));
		  } else {
				exit(0);
		  }
	 }

	 public function paso1ModificarAction()
	 {
		  if (Zend_Auth::getInstance()->hasIdentity() != true) {
				$this->_redirect('/');
		  }

		  $updateCV = $this->_postulante->hasDataForApplyJob($this->auth['postulante']['id']);
		  if (!$updateCV) {
				$this->getMessenger()->success('Debe completar los datos de tu Perfil y tu Ubicación para obtener tu perfil destacado');
		  }
		  $this->_redirect("/mi-cuenta/mis-datos-personales");




		  $texto = 'Registra';
		  $data = $this->_getAllParams();

		  $sessionUpdateCV = new Zend_Session_Namespace('updateCV');
		  if (isset($_SERVER['HTTP_REFERER'])) {
				if (strpos($_SERVER['HTTP_REFERER'], 'ofertas-de-trabajo') !== false) {
					 $url = str_replace("#winUpdateCV", '', $_SERVER['HTTP_REFERER']);
					 $sessionUpdateCV->urlAviso = true;
					 $sessionUpdateCV->urlId = substr($url, strlen($url) - 5, strlen($url));
					 $sessionUpdateCV->tipo = 'aviso';
					 $texto = 'Actualiza';
					 $this->view->update = 1;
				}
				if (strpos($_SERVER['HTTP_REFERER'], 'perfil-destacado') !== false) {
					 $url = str_replace("#winUpdateCV", '', $_SERVER['HTTP_REFERER']);
					 $sessionUpdateCV->urlId = substr($url, strlen($url) - 5, strlen($url));
					 $sessionUpdateCV->tipo = 'perfil-destacado';
					 $sessionUpdateCV->urlAviso = true;
					 $sessionUpdateCV->_url = '/perfil-destacado/paso2/tarifa/' . $data['tarifa'];
					 $texto = 'Actualiza';
					 $this->view->update = 1;
				}
				if ($_SERVER['HTTP_REFERER'] == SITE_URL . '/') {
					 $sessionUpdateCV->urlAviso = true;
					 $sessionUpdateCV->urlId = '';
					 $sessionUpdateCV->tipo = 'publicidad';
					 $texto = 'Actualiza';
					 $this->view->update = 1;
				}
		  }
		  $this->view->texto = true;
		  $this->view->modulo = $this->getRequest()->getModuleName();
		  $this->view->controlador = $this->getRequest()->getControllerName();

		  $this->view->headTitle()->set(
					 'Paso 1 - ' . $texto . ' Tus datos, Regístrate en  AquiEmpleos'
		  );
		  $this->view->headMeta()->appendName(
					 "Description", "Ingresa tus datos, primer paso para el registro en aquiempleos.com." .
					 " Los Clasificados de Empleos de La Prensa."
		  );

		  $this->_helper->viewRenderer('paso1');
		  $this->view->idPostulante = $id = $this->auth['postulante']['id'];
		  $arrayPostulante = $this->_postulante->getPostulante($id);


		  $this->view->sexo = (!empty($arrayPostulante['sexoMF'])) ? $arrayPostulante['sexoMF'] : 'M';

		  $this->view->mes = array(
				'01' => 'Enero',
				'02' => 'Febrero',
				'03' => 'Marzo',
				'04' => 'Abril',
				'05' => 'Mayo',
				'06' => 'Junio',
				'07' => 'Julio',
				'08' => 'Agosto',
				'09' => 'Septiembre',
				'10' => 'Octubre',
				'11' => 'Noviembre',
				'12' => 'Diciembre'
		  );

		  //img es la foto que se va a mostrar.
		  $img = $this->view->imgPhoto = $arrayPostulante['path_foto_uno'];
		  $idUsuario = $arrayPostulante['id_usuario'];
		  $usuarioMail = $this->_usuario->getUsuarioMail($idUsuario);
		  $dataUsuario = $this->_usuario->getUsuarioId($id);

		  $formPostulante = new Application_Form_Paso1Postulante($id);
		  $formUsuario = new Application_Form_Paso1Usuario($idUsuario);

		  $formPostulante->removeElement('txtPassword');
		  $formPostulante->removeElement('txtRepeatPassword');

		  //validadores del email y Dni para que no se repitan
		  $formPostulante->validadorNumDoc($id);
		  $formUsuario->validadorEmail($idUsuario);

		  //Valores que se coloca en el formulario.
		  foreach (array_keys(Application_Form_Paso1Postulante::$valorDocumento) as $valor) {
				$valor = explode('#', $valor);
				if ($arrayPostulante['tipo_doc'] == $valor[0]) {
					 $arrayPostulante['tipo_doc'] = $arrayPostulante['tipo_doc'] . '#' . $valor[1];
				}
		  }
		  $arrayPostulante['fecha_nac'] = date('d/m/Y', strtotime($arrayPostulante['fecha_nac']));
		  $arrayPostulante['pais_residencia'] = $arrayPostulante['idpaisres'];
		  $arrayPostulante['id_departamento'] = $arrayPostulante['iddpto'];
		  $arrayPostulante['id_provincia'] = $arrayPostulante['idprov'];
		  $arrayPostulante['id_distrito'] = $arrayPostulante['iddistrito'];

		  $ubigeo = new Application_Model_Ubigeo();
		  if (isset($arrayPostulante['id_provincia']) &&
					 trim($arrayPostulante['id_provincia']) == Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID) {
				$arrayUbigeo = $ubigeo->getHijos(Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID);
				$formPostulante->getElement('id_distrito')->addMultioptions($arrayUbigeo);
		  }
		  if (isset($arrayPostulante['id_provincia']) &&
					 trim($arrayPostulante['id_provincia']) == Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID) {
				$arrayUbigeo = $ubigeo->getHijos(Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID);
				$formPostulante->getElement('id_distrito')->addMultioptions($arrayUbigeo);
		  }

		  if ($arrayPostulante['website'] == null) {
				$arrayPostulante['website'] = Application_Form_Paso1Postulante::$_defaultWebsite;
		  }
		  if ($arrayPostulante['presentacion'] == null) {
				$arrayPostulante['presentacion'] = Application_Form_Paso1Postulante::$_defaultPresentacion;
		  }

		  $formPostulante->setDefaults($arrayPostulante);
		  $formUsuario->setDefault('email', $usuarioMail);
		  $valPostUbigeo = '';

		  if (isset($sessionUpdateCV->urlAviso)) {
				$this->view->update = 1;
				$pswd = $dataUsuario->pswd;
				$formUsuario->removeElement('pswd');
				$formUsuario->removeElement('pswd2');
		  }

		  if ($this->_request->isPost()) {

				$sessionUpdateCV = new Zend_Session_Namespace('updateCV');
				if (isset($sessionUpdateCV->urlAviso)) {
					 $this->view->update = 1;
					 $pswd = $dataUsuario->pswd;
					 $formUsuario->removeElement('pswd');
					 $formUsuario->removeElement('pswd2');
				}

				$allParams = $this->_getAllParams();

				$fechaNac = $allParams['selDia'] . "/" . $allParams['selMes'] . "/" . $allParams['selAnio'];
				$allParams['fecha_nac'] = $fechaNac;

				$validPostulante = $formPostulante->isValid($allParams);
				$validUsuario = $formUsuario->isValid($allParams);


				if (isset($allParams['id_provincia'])) {
					 $valPostUbigeo = $allParams['id_provincia'];
				}

				if ($validPostulante && $validUsuario) {
					 $utilfile = $this->_helper->getHelper('UtilFiles');
					 $nuevoNombre = $utilfile->_renameFile($formPostulante, "path_foto");

					 $valuesUsuario = $formUsuario->getValues();
					 $valuesPostulante = $formPostulante->getValues();
					 $date = date('Y-m-d H:i:s');

					 if (isset($sessionUpdateCV->urlAviso)) {
						  unset($valuesUsuario['auth_token']);
						  unset($valuesPostulante['auth_token']);
					 }

					 try {
						  $db = $this->getAdapter();
						  $db->beginTransaction();

						  //Captura de los datos de usuario
						  if (!isset($sessionUpdateCV->urlAviso)) {
								$pswd = $valuesUsuario['pswd'];
						  }

						  $valuesUsuario['salt'] = '';
						  $valuesUsuario['rol'] = Application_Form_Login::ROL_POSTULANTE;
						  $valuesUsuario['activo'] = 1;

						  if (!isset($sessionUpdateCV->urlAviso)) {
								$valuesUsuario['pswd'] = App_Auth_Adapter_AptitusDbTable::generatePassword(
													 $valuesUsuario['pswd']
								);
						  }

						  unset($valuesUsuario['pswd2']);


						  unset($valuesUsuario['auth_token']);
						  unset($valuesPostulante['auth_token']);

						  $where = $this->_usuario->getAdapter()
									 ->quoteInto('id = ?', $idUsuario);
						  $this->_usuario->update($valuesUsuario, $where);

						  $slug = $this->_crearSlug($valuesPostulante, $idUsuario);

						  //Captura de los valores del Postulante
						  if ($valuesPostulante['website'] ==
									 Application_Form_Paso1Postulante::$_defaultWebsite) {
								$valuesPostulante['website'] = null;
						  }
						  if ($valuesPostulante['presentacion'] ==
									 Application_Form_Paso1Postulante::$_defaultPresentacion) {
								$valuesPostulante['presentacion'] = null;
						  }
						  $valuesPostulante['pais_nacionalidad'] = $valuesPostulante['pais_residencia'];
						  $valuesPostulante['fecha_nac'] = date(
									 'Y-m-d', strtotime(str_replace('/', '-', $valuesPostulante['fecha_nac']))
						  );

						  $valorTipoDoc = explode('#', $valuesPostulante['tipo_doc']);
						  $valuesPostulante['tipo_doc'] = $valorTipoDoc[0];

						  $valuesPostulante['sexo'] = $valuesPostulante['sexoMF'];
						  $valuesPostulante['disponibilidad_mudarse'] = '0';
						  $valuesPostulante['prefs_confidencialidad'] = '0';

						  if (!isset($sessionUpdateCV->urlAviso)) {
								$valuesPostulante['ultima_actualizacion'] = $date;
						  }

						  $valuesPostulante['slug'] = $slug;

						  $valuesPostulante['id_ubigeo'] = $this->_helper->Util->getUbigeo($valuesPostulante);

						  $where = $this->_postulante->getAdapter()
									 ->quoteInto('id = ?', $id);

						  if ($valuesPostulante['path_foto'] == NULL) {
								$valuesPostulante['path_foto'] = $img;
						  } else {
								$valuesPostulante['path_foto'] = $nuevoNombre[0];
								$valuesPostulante['path_foto1'] = $nuevoNombre[1];
								$valuesPostulante['path_foto2'] = $nuevoNombre[2];
								if ($img != 'photoDefault.jpg') {
									 unlink(APPLICATION_PATH . '/../public/elements/empleo/img/' . $img);
								}
						  }
						  unset($valuesPostulante['sexoMF']);
						  unset($valuesPostulante['id_departamento']);
						  unset($valuesPostulante['id_distrito']);
						  unset($valuesPostulante['id_provincia']);
						  unset($valuesPostulante['prefs_emailing']);

						  $this->_postulante->update($valuesPostulante, $where);
						  $this->_helper->solr->addSolr($id);

						  $db->commit();
					 } catch (Zend_Db_Exception $e) {
						  $db->rollBack();
						  echo $e->getMessage();
					 } catch (Zend_Exception $e) {
						  $this->getMessenger()->error($this->_messageSuccess);
						  echo $e->getMessage();
					 }

					 $storage = Zend_Auth::getInstance()->getStorage()->read();
					 $storage['postulante']['nombres'] = $valuesPostulante['nombres'];
					 $storage['postulante']['apellido_paterno'] = $valuesPostulante['apellido_paterno'];
					 $storage['postulante']['apellido_materno'] = $valuesPostulante['apellido_materno'];
					 $storage['postulante']['sexo'] = $valuesPostulante['sexo'];
					 $storage['postulante']['tipo_doc'] = $valuesPostulante['tipo_doc'];
					 $storage['postulante']['num_doc'] = $valuesPostulante['num_doc'];
					 Zend_Auth::getInstance()->getStorage()->write($storage);

					 if (isset($sessionUpdateCV->urlAviso)) {
						  if ($sessionUpdateCV->tipo == 'perfil-destacado') {
								$this->_redirect($sessionUpdateCV->_url);
						  } else {
								$this->_redirect('/mi-cuenta/actualiza');
						  }
						  $this->_redirect('/mi-cuenta/actualiza');
					 } else {
						  if ($sessionUpdateCV->tipo == 'perfil-destacado') {
								$this->getMessenger()->success('Gracias por completar tus datos');
								$this->_redirect($sessionUpdateCV->_url);
						  } else {
								$this->_redirect($this->_url);
						  }
						  $this->_redirect($this->_url);
					 }
				} else {

					 if ($valPostUbigeo == Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID) {
						  $arrayUbigeo = $ubigeo->getHijos(
									 Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID
						  );
						  $formPostulante->getElement('id_distrito')->clearMultiOptions();
						  $formPostulante->getElement('id_distrito')
									 ->addMultiOption('none', 'Seleccione Distrito');
						  $formPostulante->getElement('id_distrito')->addMultioptions($arrayUbigeo);
					 }
					 if ($valPostUbigeo == Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID) {
						  $arrayUbigeo = $ubigeo->getHijos(
									 Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID
						  );
						  $formPostulante->getElement('id_distrito')->clearMultiOptions();
						  $formPostulante->getElement('id_distrito')
									 ->addMultiOption('none', 'Seleccione Distrito');
						  $formPostulante->getElement('id_distrito')->addMultioptions($arrayUbigeo);
					 }
				}
		  }
		  $this->view->formPostulante = $formPostulante;
		  $this->view->formUsuario = $formUsuario;
	 }

	 public function paso2Action()
	 {
		  $this->view->hide = 'hide';
		  if (Zend_Auth::getInstance()->hasIdentity() != true) {
				$this->_redirect('/');
		  }

		  $idPostulante = $this->idPostulante = $this->auth['postulante']['id'];

		  $this->view->headTitle()->set(
					 'Paso 2 - Ingresa tu Perfil Profesional, Regístrate en  AquiEmpleos'
		  );
		  $this->view->headMeta()->appendName(
					 "Description", "Registra tu Perfil profesional, segundo paso para el registro en " .
					 "aquiempleos.com.  Los Clasificados de Empleos La Prensa."
		  );


		  //$dynAreasInteres = new Amazon_Dynamo_ParamSugeridosPostulante();
		  $dynAreasInteres = new Mongo_ParamSugeridosPostulante();
		  $areasInteres = array();
		  $dynInteres = $dynAreasInteres->getDatos($idPostulante);

		  //if (count($dynInteres) > 0) {
		  if ($dynInteres) {

				$dynInteresAreas = $dynInteres['area_nivel'];

				for ($index = 0; $index < count($dynInteresAreas); $index++) {
					 foreach ($dynInteresAreas[$index] as $key => $item) {
						  $areasInteres[$key][$item['area']["id"]] = $item['nivel'];
					 }
				}
				//$dynInteresAreas = (array) unserialize($dynInteres['area_nivel']);
		  }

		  $modelArea = new Application_Model_Area();
		  $areas = $modelArea->getAreasToRegistro();
		  unset($areas[6]);
		  unset($areas[19]);

		  $modelNivelPuesto = new Application_Model_NivelPuesto();
		  $niveles = $modelNivelPuesto->getNivelesToRegistro();

		  $niveles_area = array();

		  foreach ($areas as $key => $area) {
				$return = array();
				foreach ($niveles as $val) {
					 if ($area['id'] == $val['id_area']) {
						  $return['area'] = $area['id'];
						  $return['niveles'][] = $val;
					 }
				}
				if (!empty($return))
					 $niveles_area[] = $return;
		  }

		  foreach ($areas as $key => $area) {
				foreach ($areasInteres as $id => $item) {
					 if (array_key_exists($area['id'], $item)) {
						  $areas[$key]['selected'][$id] = $areasInteres[$id][$area['id']];
					 }
				}
		  }

		  $this->view->areas = $areas;
		  $this->view->niveles = $niveles_area;

		  if ($this->getRequest()->isPost()) {
				$postData = $this->_getAllParams();
				$hasDataPost = (isset($postData['areas']) && isset($postData['niveles']) && (count($postData['areas']) <= 3) && (count($postData['niveles']) <= 3)
						  );

				if ($hasDataPost) {
					 if ($this->_hash->isValid($postData['hash'])) {
						  $dataInsert = array();
						  $datadyn = array();
						  foreach ($postData['areas'] as $key => $area) {
								if (!empty($area) && isset($postData['niveles'][$key]) && !empty($postData['niveles'][$key])
								) {
									 $arArea = explode('_', $area);
									 $arNivellist = explode(',', $postData['niveles'][$key]);
									 foreach ($arNivellist as $k => $value) {
										  $arNivel[$k] = explode('_', $value);
										  $dataInsert[$k] = array(
												'area' => array(
													 'id' => $arArea[0],
													 'name' => $arArea[1]
												),
												'nivel' => array(
													 'id' => $arNivel[$k][0],
													 'name' => $arNivel[$k][1],
												)
										  );
									 }
									 $datadyn[$key] = $dataInsert;
								}
						  }

						  try {
								$dynAreasInteres->guardarDatos(array(
									 'idPostulante' => $idPostulante,
									 'data' => $datadyn
								));

								$dynInteres = $dynAreasInteres->getDatos($idPostulante);
						  } catch (Exception $ex) {
								$this->log->log($exc->getMessage() . '. ' . $exc->getTraceAsString(), Zend_Log::ERR);
								$this->getMessenger()->error('Vuelva ha intentarlo por favor.');
								$this->_redirect('/registro/paso2');
						  }


						  $datadyn['id'] = $idPostulante;
						  $datadyn['area_nivel'] = serialize($datadyn);
						  $this->_helper->LogActualizacionBI
									 ->logActualizacionPostulanteSugerencias($datadyn);
						  $this->_redirect('/registro/paso3');
					 } else {
						  $this->getMessenger()->error('Vuelva ha intentarlo por favor.');
						  $this->_redirect('/registro/paso2');
					 }
				} else {
					 $this->getMessenger()->error('Es necesario que seleccione sus áreas de interes.');
					 $this->_redirect('/registro/paso2');
				}
		  }
	 }

	 public function paso2ActionOld()
	 {
		  $this->view->hide = 'hide';
		  if (Zend_Auth::getInstance()->hasIdentity() != true) {
				$this->_redirect('/registro/paso1');
		  }

		  $idPostulante = $this->idPostulante = $this->auth['postulante']['id'];

		  $session = $this->getSession();
		  $this->view->headTitle()->set(
					 'Paso 2 - Ingresa tu Perfil Profesional, Regístrate en  AquiEmpleos'
		  );
		  $this->view->headMeta()->appendName(
					 "Description", "Registra tu Perfil profesional, segundo paso para el registro en " .
					 "aquiempleos.com.  Los Clasificados de Empleos de La Prensa."
		  );

		  $this->view->headScript()->appendScript('action = "paso2";');
		  $this->view->headLink()->appendStylesheet($this->view->S('/css/plugins/jquery-ui-1.9.2.custom.min.css'));


		  $baseFormExperiencia = new Application_Form_Paso2Experiencia();
		  $managerExperiencia = new App_Form_Manager($baseFormExperiencia, 'managerExperiencia');

		  $baseFormIdioma = new Application_Form_Paso2Idioma();
		  $managerIdioma = new App_Form_Manager($baseFormIdioma, 'managerIdioma');

		  $baseFormEstudio = new Application_Form_Paso2Estudio();
		  $options = $baseFormEstudio->id_nivel_estudio->getMultiOptions();
		  unset($options[1]);
		  $baseFormEstudio->id_nivel_estudio->setMultiOptions($options);
		  $managerEstudio = new App_Form_Manager($baseFormEstudio, 'managerEstudio');

		  $baseFormOtroEstudio = new Application_Form_Paso2OtroEstudio();
		  $managerOtroEstudio = new App_Form_Manager($baseFormOtroEstudio, 'managerOtroEstudio');

		  $baseFormPrograma = new Application_Form_Paso2Programa();
		  $managerPrograma = new App_Form_Manager($baseFormPrograma, 'managerPrograma');

		  $form = new Application_Form_Paso2();
		  $formAlertas = new Application_Form_MisAlertas();

		  $estudioModelo = new Application_Model_Estudio;

		  $formExperiencia = array();
		  $formEstudio = array();
		  $formOtroEstudio = array();
		  $formIdioma = array();
		  $formPrograma = array();

		  if ($this->getRequest()->isPost()) {

				$postData = $this->_getAllParams();

				$this->view->isExperiencia = true;
				$this->view->isEstudio = true;
				$this->view->isOtroEstudio = true;
				$this->view->isIdioma = true;
				$this->view->isPrograma = true;

				$validAlertas = $formAlertas->isValid($postData);

				if ($this->getRequest()->getPost('fNoExp') == '1') {
					 $this->view->isExperiencia = false;
				}

				if ($this->getRequest()->getPost('fNoEst') == '1') {
					 $this->view->isEstudio = false;
				}

				$managerIdioma->isValid($postData);
				$managerPrograma->isValid($postData);
				$managerOtroEstudio->isValid($postData);
				foreach ($managerIdioma->getForms() as $formIdi) {
					 // @codingStandardsIgnoreStart
					 if ($formIdi->nivel_idioma->hasErrors()) {
						  $this->view->isIdioma = false;
					 }
					 // @codingStandardsIgnoreEnd
				}
				foreach ($managerPrograma->getForms() as $formProg) {
					 if ($formProg->nivel->hasErrors()) {
						  $this->view->isPrograma = false;
					 }
				}
				foreach ($managerOtroEstudio->getForms() as $formOE) {
					 if ($formOE->id_nivel_estudio_tipo->hasErrors() || $formOE->otro_estudio->hasErrors() || $formOE->institucion->hasErrors() || $formOE->pais_estudio->hasErrors()) {
						  $this->view->isOtroEstudio = false;
					 }
				}
				$validExp = true;
				if ($this->getRequest()->getPost('fNoExp') == 0) {
					 $validExp = $managerExperiencia->isValid($postData);
				} else {
					 $formExperiencia[] = $managerExperiencia->getForm(0);
					 $form->setDefault('fNoExp', 1);
				}

				$validEst = true;
				if ($this->getRequest()->getPost('fNoEst') == 0) {
					 $validEst = $managerEstudio->isValid($postData);
				} else {
					 $formEstudio[] = $managerEstudio->getForm(0);
					 $form->setDefault('fNoEst', 1);
				}

				//print_r($postData);

				if ($validExp && $validEst &&
						  $managerOtroEstudio->isValid($postData) &&
						  $managerIdioma->isValid($postData) &&
						  $managerPrograma->isValid($postData) &&
						  $form->isValid($postData) && $validAlertas) {

					 $helper = $this->_helper->getHelper("RegistrosExtra");
					 if ($this->getRequest()->getPost('fNoExp') == 0) {
						  $this->_guardarExperienciaPostulante($managerExperiencia, $idPostulante);
						  $helper->ActualizarExperiencias($idPostulante);
					 }
					 if ($this->getRequest()->getPost('fNoEst') == 0) {
						  $this->_guardarEstudiosPostulante($managerEstudio, $idPostulante);
						  $helper->ActualizarEstudios($idPostulante);
					 }

					 $this->_guardarOtrosEstudiosPostulante($managerOtroEstudio, $idPostulante);
					 $this->_guardarIdiomasPostulante($managerIdioma, $idPostulante);
					 $this->_guardarProgramasPostulante($managerPrograma, $idPostulante);

					 $objPostulante = new Application_Model_Postulante();

					 $estudioPrincipal = $estudioModelo->obtenerEstudiosMayorPesoPorPostulante($idPostulante);

					 if (isset($estudioPrincipal)) {
						  $estudioModelo->actualizarEstudioPrincipal($idPostulante, $estudioPrincipal['id']);
					 }

					 $this->_guardarOpcionesPostulante($formAlertas->getValues(), $idPostulante);
					 $this->_helper->solr->addSolr($idPostulante);
					 $this->_redirect('/registro/paso3');
				} else {
					 $formuExperiencia = $managerExperiencia->getForms();
					 $formExperiencia = array();
					 foreach ($formuExperiencia as $j => $fe) {
						  $formExperiencia[$j] = $fe;
					 }
					 if ($validExp && $this->getRequest()->getPost('fNoExp') == 0)
						  $formExperiencia[$j + 1] = $managerExperiencia->getForm($j + 1);
					 $carrera = new Application_Model_Carrera();
					 $nivelEstudio = new Application_Model_NivelEstudio();
					 $formuEstudio = $managerEstudio->getForms();
					 $formEstudio = array();
					 foreach ($formuEstudio as $k => $fe) {
						  $id_tipo_carrera = $fe->getElement('id_tipo_carrera')->getValue();
						  $fe->setElementCarrera($id_tipo_carrera);
						  $id_nivel_estudio = $fe->getElement('id_nivel_estudio')->getValue();
						  $fe->setElementNivelEstudio($id_nivel_estudio);
						  $formEstudio[$k] = $fe;
					 }
					 if ($validEst && $this->getRequest()->getPost('fNoEst') == 0)
						  $formEstudio[$k + 1] = $managerEstudio->getForm($k + 1);
					 $formOtroEstudio = $managerOtroEstudio->getForms();
					 $formIdioma = $managerIdioma->getForms();
					 $formPrograma = $managerPrograma->getForms();
				}
		  } else if (isset($session->linkedin)) {
				$this->view->isLinkedin = true;
				$this->getMessenger()->success(
						  "Se logró importar tus datos de linkedin. Por favor, ingresa o
		  selecciona los datos que no hayan coincidido exactamente con
		  los de AquiEmpleos."
				);
				$linkedinData = $session->linkedin;
				$formExperiencia = $this->_linkedinExperiencia($linkedinData, $managerExperiencia);
				$formEstudio = $this->_linkedinEstudio($linkedinData, $managerEstudio);
				$formIdioma = $this->_linkedinIdioma($linkedinData, $managerIdioma);
				$formPrograma = array(
					 $managerPrograma->getForm(0));
				$formOtroEstudio = array(
					 $managerOtroEstudio->getForm(0));


				$formExperiencia[] = $managerExperiencia->getForm(count($formExperiencia));
				$formEstudio[] = $managerEstudio->getForm(count($formEstudio));

				unset($session->linkedin);

				$estudioPrincipal = $estudioModelo->obtenerEstudiosMayorPesoPorPostulante($idPostulante);

				if (isset($estudioPrincipal)) {
					 $estudioModelo->actualizarEstudioPrincipal($idPostulante, $estudioPrincipal['id']);
				}
		  } else {
				$formExperiencia[] = $managerExperiencia->getForm(0);
				$formEstudio[] = $managerEstudio->getForm(0);
				$formOtroEstudio[] = $managerOtroEstudio->getForm(0);
				$formIdioma[] = $managerIdioma->getForm(0);
				$formPrograma[] = $managerPrograma->getForm(0);
		  }

		  $this->view->formExperiencia = $formExperiencia;
		  $this->view->assign('managerExperiencia', $managerExperiencia);

		  $this->view->formEstudio = $formEstudio;
		  $this->view->assign('managerEstudio', $managerEstudio);

		  $this->view->formOtroEstudio = $formOtroEstudio;
		  $this->view->assign('managerOtroEstudio', $managerOtroEstudio);

		  $this->view->formIdioma = $formIdioma;
		  $this->view->assign('managerIdioma', $managerIdioma);

		  $this->view->formPrograma = $formPrograma;
		  $this->view->assign('managerPrograma', $managerPrograma);

		  $this->view->form = $form;
		  $this->view->formAlertas = $formAlertas;
	 }

	 public function importarDatosAction()
	 {
		  $session = $this->getSession();
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();
		  $config = $this->getConfig();

		  $options = array(
				'version' => '1.0',
				'signatureMethod' => 'HMAC-SHA1',
				'localUrl' => $config->app->siteUrl . '/registro/importar-datos',
				'callbackUrl' => $config->app->siteUrl . '/registro/importar-datos',
				'requestTokenUrl' => $config->apis->linkedin->requestTokenUrl,
				'userAuthorizationUrl' =>
				$config->apis->linkedin->userAuthorizationUrl,
				'accessTokenUrl' =>
				$config->apis->linkedin->accessTokenUrl,
				'consumerKey' => $config->apis->linkedin->consumerKey,
				'consumerSecret' => $config->apis->linkedin->consumerSecret
		  );

		  $consumer = new Zend_Oauth_Consumer($options);
		  if ($_REQUEST['oauth_problem'] != "") {
				$this->getMessenger()->error(
						  'En estos momentos el servicio de importación no se encuentra
		  disponible.'
				);
				$this->_redirect('/registro/paso2');
		  }
		  if (!isset($_SESSION['ACCESS_TOKEN'])) {
				if (!empty($_GET)) {
					 $token = $consumer->getAccessToken(
								$_GET, unserialize($_SESSION['REQUEST_TOKEN'])
					 );
					 $_SESSION ['ACCESS_TOKEN'] = serialize($token);
				} else {
					 $token = $consumer->getRequestToken();
					 $_SESSION['REQUEST_TOKEN'] = serialize($token);
					 $consumer->redirect();
				}
		  } else {
				$token = unserialize($_SESSION['ACCESS_TOKEN']);
				$_SESSION ['ACCESS_TOKEN'] = null;
		  }
		  $client = $token->getHttpClient($options);
		  $client->setHeaders('Accept-Language', 'es-ES');

		  $client->setUri($config->apis->linkedin->urlImportData);
		  $client->setMethod(Zend_Http_Client::GET);
		  $response = $client->request();
		  $content = $response->getBody();
		  $data = new Zend_Config_Xml($content);
		  $session->linkedin = $data;
		  $this->_redirect('/registro/paso2');
	 }

	 public function paso3OldAction()
	 {

		  $id = $this->idPostulante = $this->auth['postulante']['id'];

		  if (Zend_Auth::getInstance()->hasIdentity() != true) {
				$this->_redirect('/registro/paso1');
		  }

		  //Campaña doble de riesgo
		  Zend_Layout::getMvcInstance()->assign(array(
				'trackingFacebook' => true));

		  $this->view->headTitle()->set(
					 'Paso 3 - Listo!, Regístrate en  AquiEmpleos'
		  );
		  $this->view->headMeta()->appendName(
					 "Description", "Bienvenido [Nombre], ya eres parte de aquiempleos.com." .
					 " Los Clasificados de Empleos de La Prensa."
		  );

		  $anunciosWeb = new Application_Model_AnuncioWeb();

		  $avisosrelacionados = $anunciosWeb->getAvisosRelacionadosPasoTres($id);
		  if (count($avisosrelacionados) == 0) {
				$avisosrelacionados = $anunciosWeb->getAvisosRelacionadosAuxiliar($id, 15);
		  }
		  //echo "comercial"; exit;
		  $this->view->avisosrelacionados = $avisosrelacionados;

		  Zend_Layout::getMvcInstance()->assign(
					 'bodyAttr', array(
				'id' => 'home2',
				'class' => array(
					 'dark wide mobile noMenu'))
		  );

		  $config = $this->getConfig();

		  $searchUrlBuscamas = $config->apis->buscamas->searchUrl;
		  $apiKeyBuscamas = $config->apis->buscamas->consumerKey;


		  $url = $searchUrlBuscamas . $apiKeyBuscamas . '/start/0/count/20';
		  $buscaMas = $this->_helper->getHelper('BuscaMas');
		  $resultado = $buscaMas->obtenerResultadoBuscaMasCache($url);

		  $decode = Zend_Json::decode($resultado);

		  $areasJSON = $decode['filter']['area'];
		  $nivelJSON = $decode['filter']['level'];
		  $ubicacionJSON = $decode['filter']['location'];

		  $areaValorDesc = $buscaMas->ordenarArray($areasJSON, 'count', true);
		  $areaDescDesc = $buscaMas->ordenarArray($areasJSON, 'label', false);

		  $nivelValorDesc = $buscaMas->ordenarArray($nivelJSON, 'count', true);
		  $nivelDescDesc = $buscaMas->ordenarArray($nivelJSON, 'label', false);

		  $ubiValorDesc = $buscaMas->ordenarArray($ubicacionJSON, 'count', true);
		  $ubiDescDesc = $buscaMas->ordenarArray($ubicacionJSON, 'label', false);

		  $this->view->groupAreas1 = $areaDescDesc;
		  $this->view->groupAreas2 = $areaValorDesc;
		  $this->view->groupNivelPuesto1 = $nivelDescDesc;
		  $this->view->groupNivelPuesto2 = $nivelValorDesc;
		  $this->view->groupDistritos1 = $ubiDescDesc;
		  $this->view->groupDistritos2 = $ubiValorDesc;

		  $form = new Application_Form_BuscarHome();
		  $form->setAreas($areaDescDesc);
		  $form->setNivelPuestos($nivelDescDesc);
		  $form->setUbicacion($ubiDescDesc);

		  $this->view->form = $form;
	 }

	 public function paso3Action()
	 {
		  $this->_redirect('/mi-cuenta/');
		  $moneda = $this->_config->app->moneda;
		  $idPostulante = $this->auth['postulante']['id'];
		  $slug = $this->auth['postulante']['slug'];
		  //$dynAreasInteres = new Amazon_Dynamo_ParamSugeridosPostulante();
		  $dynAreasInteres = new Mongo_ParamSugeridosPostulante();
		  $ubigeo = new Application_Model_Ubigeo();
		  //$AreasInteres = new Application_Model_Aptitudes();
		  $form = new Application_Form_RegistroComplePostulante($idPostulante, $this->auth['postulante']);
		  $modelAreaInteres = new Application_Model_AreaInteres();
		  $form->remuneracionUbigeo();
		  $slugFilter = new App_Filter_Slug();
		  $getAreaInteres = $dynAreasInteres->getDatos($idPostulante);
		  $aptitudes = $modelAreaInteres->obtenerAptitudesPostulante($idPostulante);
		  $getAreaInteres['aptitudes'] = array();
		  if ($aptitudes) {
				$getAreaInteres['aptitudes'] = $aptitudes;
		  }
		  $getAreaInteres['ubigeo'] = '';
		  //if(is_numeric($getAreaInteres['ubigeo']['S'])){
		  if (isset($getAreaInteres['ubigeo']) && is_numeric($getAreaInteres['ubigeo'])) {
				$getAreaInteres['ubigeo'] = '';
		  }
		  //$getAreaInteres['location']=(isset($getAreaInteres['ubigeo']['S']) && !empty($getAreaInteres['ubigeo']['S']) ) ? (array) unserialize($getAreaInteres['ubigeo']['S']):array();
		  $getAreaInteres['location'] = (isset($getAreaInteres['ubigeo']) && !empty($getAreaInteres['ubigeo']) ) ? $getAreaInteres['ubigeo'] : array();

		  $form->removeElement('txtUbicacion');
		  //unset($getAreaInteres['aptitudes']['S']);
		  #unset($getAreaInteres['aptitudes']);
		  //if (isset($getAreaInteres['price1']['S']) && isset($getAreaInteres['price2']['S'])) {
		  if (isset($getAreaInteres['price1']) && isset($getAreaInteres['price2'])) {
				//$remuneracion = '$'.$getAreaInteres['price1']['S'].'-$'.$getAreaInteres['price2']['S'];
				$remuneracion = $moneda . $getAreaInteres['price1'] . '-' . $moneda . $getAreaInteres['price2'];
				$form->getElement('txtremuneracion')->setValue($remuneracion);
		  }

		  $arrRem = $this->config->salarios->filtros->rangoRemuneracion->toArray();
		  $remuneracionDefault = explode('-', str_replace(array(
				'S/',
				'$',
				' '), '', $this->config->salarios->default));
		  $this->view->salariosDefault = array(
				array_search($remuneracionDefault[0], $arrRem),
				array_search($remuneracionDefault[1], $arrRem)
		  );

		  $this->view->getAreaInteres = $getAreaInteres;

		  if ($this->_request->isPost()) {

				$allParams = $this->_getAllParams();
				$allParams = App_Util::clearXSS($allParams);
				$dataUbigeo = array();
				try {
					 $obligatories = array(
						  'txtremuneracion',
						  //  'aptitudes',
						  'ubigeo'
					 );

					 if (!App_Util::fieldRequired($allParams, $obligatories)) {
						  throw new Exception('Por favor vuelva a intentarlo');
					 }

					 $remuneracion = array(
						  0,
						  0);
					 if (strpos($allParams['txtremuneracion'], '-') != FALSE) {
						  $remuneracion = explode('-', str_replace(array(
								'$',
								',',
								'S/'), '', $allParams['txtremuneracion']));
					 } else {
						  throw new Exception('Por favor vuelva a intentarlo');
					 }

					 $puntoMin = array_search($remuneracion[0], $arrRem);
					 $puntoMax = array_search($remuneracion[1], $arrRem);

					 $listAptitudes = array();

					 if ($form->isValidRemUbi($allParams)) {
						  if (count($allParams['ubigeo']) > 8) {
								$this->getMessenger()->error('Solo puedes guardar 8 items de ubicación');
								$this->_redirect('/registro/paso3');
						  }

						  $modelAreaInteres->deleteAptitudes($idPostulante);
						  if (!isset($allParams['aptitudes'])) {
								$allParams['aptitudes'] = array();
						  }
						  if (count($allParams['aptitudes']) > 0) {
								foreach ($allParams['aptitudes'] as $key => $value) {
									 /// $value = $filter->filter($value);
									 if (!is_numeric($value)) {
										  $apitudes = array(
												'nombre' => $value,
												'slug' => $slugFilter->filter($value),
												'estado' => 1,
										  );
										  $allParams['aptitudes'][$key] = Application_Model_Aptitudes::agregarAptitusdes($apitudes);
										  $apitudes['id'] = $allParams['aptitudes'][$key];
										  $listAptitudes[] = $apitudes;
									 }
									 $dataAptitud['id'] = $allParams['aptitudes'][$key];
									 $dataAptitud['id_postulante'] = $idPostulante;
									 $modelAreaInteres->guardarDataAptitud($dataAptitud);
								}
						  }


						  $allParams['ubigeo'] = array_unique($allParams['ubigeo']);
						  foreach ($allParams['ubigeo'] as $key => $value) {
								$dataUbigeo[$key] = $ubigeo->getDetalleUbigeoById($value);
						  }

						  $this->_helper->solr->addSolr($idPostulante);
						  $data = array(
								'aptitudes' => $allParams['aptitudes'],
								'price1' => $remuneracion[0],
								'price2' => $remuneracion[1],
								'price1_punto' => $puntoMin,
								'price2_punto' => $puntoMax,
								//   'txtUbicacion' => serialize($dataUbigeo),
								'ubigeo' => $dataUbigeo,
								'idPostulante' => $idPostulante
						  );

						  $dynAreasInteres = new Mongo_ParamSugeridosPostulante();
						  $dynAreasInteres->updateDatos($data);

						  $data['id_aptitud'] = implode(',', $allParams['aptitudes']);
						  $data['id_postulante'] = $idPostulante;

						  $this->getMessenger()->success('Gracias por registrarse en aquiempleos.com');
						  $this->_redirect('/');
					 }
				} catch (Exception $exc) {
					 $this->log->log($exc->getMessage() . '. ' . $exc->getTraceAsString(), Zend_Log::ERR);
					 $this->getMessenger()->error('Por favor vuelva a intentarlo');
					 $this->_redirect('/registro/paso3');
				}
		  }
		  $this->view->form = $form;
	 }

	 public function politicasPrivacidadAction()
	 {
		  //Politicas de Privacidad;
	 }

	 /**
	  * Lee los datos provenientes del LinkedIn relacionado a Expericencia para
	  * luego insertarlo en el formulario
	  *
	  * @param Zend_Config_Xml $linkedinData
	  * @param App_Form_Manager $mngrXp
	  * @return array
	  */
	 private function _linkedinExperiencia(Zend_Config_Xml $linkedinData, App_Form_Manager $mngrXp)
	 {
		  if (isset($linkedinData->positions->total)) {
				$this->view->isExperiencia = true;
				$formExperiencia = array();
				if ($linkedinData->positions->total > 1) {
					 //  $i = 0;
					 $total = $linkedinData->positions->total + 1;


					 foreach ($linkedinData->positions->position as $empresa) {
						  $values = array();
						  $form = $mngrXp->getForm($i);
						  $empresa = $empresa->toArray();
						  if (isset($empresa['company']['name'])) {
								$values['otra_empresa'] = $empresa['company']['name'];
						  }
						  if (isset($empresa['company']['industry'])) {
								$values['otro_rubro'] = $empresa['company']['industry'];
						  }
						  if (isset($empresa['title'])) {
								$values['otro_puesto'] = $empresa['title'];
						  }
						  if (isset($empresa['start-date']['month'])) {
								$values['inicio_mes'] = $empresa['start-date']['month'];
						  }
						  if (isset($empresa['start-date']['year'])) {
								$values['inicio_ano'] = $empresa['start-date']['year'];
						  }
						  if ($empresa['is-current'] == 'true') {
								$values['en_curso'] = '1';
								// @codingStandardsIgnoreStart
								$form->fin_mes->setAttrib('rel', date('n'));
								// @codingStandardsIgnoreEnd
								$values['fin_mes'] = date('n');
								$values['fin_ano'] = date('Y');
						  } else {
								$values['en_curso'] = '0';
								if (isset($empresa['end-date']['month'])) {
									 $values['fin_mes'] = $empresa['end-date']['month'];
								} else {
									 $values['fin_mes'] = date('n');
								}
								if (isset($empresa['end-date']['year'])) {
									 $values['fin_ano'] = $empresa['end-date']['year'];
								} else {
									 $values['fin_ano'] = date('Y');
								}
						  }
						  if (isset($empresa['summary'])) {
								$values['comentarios'] = substr($empresa['summary'], 0, 135);
						  }
						  $form->isValid($values);
						  ///array_push($form, $mngrXp->getForm($total));
						  $formExperiencia[] = $form;
						  $i++;
					 }
				} elseif ($linkedinData->positions->total == 0) {
					 $values = array();
					 $values['inicio_ano'] = date('Y') - 1;
					 $values['fin_ano'] = date('Y');
					 $form = $mngrXp->getForm(0);
					 $form->isValid($values);
					 $formExperiencia[] = $form;
				} else {
					 $empresa = $linkedinData->positions->position->toArray();
					 if (isset($empresa['company']['name'])) {
						  $values['otra_empresa'] = $empresa['company']['name'];
					 }
					 if (isset($empresa['company']['industry'])) {
						  $values['otro_rubro'] = $empresa['company']['industry'];
					 }
					 if (isset($empresa['title'])) {
						  $values['otro_puesto'] = $empresa['title'];
					 }
					 if (isset($empresa['start-date']['month'])) {
						  $values['inicio_mes'] = $empresa['start-date']['month'];
					 }
					 if (isset($empresa['start-date']['year'])) {
						  $values['inicio_ano'] = $empresa['start-date']['year'];
					 }
					 if ($empresa['is-current'] == 'true') {
						  $values['en_curso'] = '1';
					 } else {
						  $values['en_curso'] = '0';
						  if (isset($empresa['end-date']['month'])) {
								$values['fin_mes'] = $empresa['end-date']['month'];
						  } else {
								$values['fin_mes'] = date('n');
						  }
						  if (isset($empresa['end-date']['year'])) {
								$values['fin_ano'] = $empresa['end-date']['year'];
						  } else {
								$values['fin_ano'] = date('Y');
						  }
					 }
					 if (isset($empresa['summary'])) {
						  $values['comentarios'] = substr($empresa['summary'], 0, 135);
					 }
					 $form = $mngrXp->getForm(0);
					 $form->isValid($values);
					 $formExperiencia[] = $form;
				}
		  } else {
				$formExperiencia = array(
					 $mngrXp->getForm(0));
		  }
		  return $formExperiencia;
	 }

	 /**
	  * Lee los datos provenientes del LinkedIn relacionado a Estudios para
	  * luego insertarlo en el formulario
	  *
	  * @param Zend_Config_Xml $linkedinData
	  * @param App_Form_Manager $mngrEstudio
	  * @return array
	  */
	 private function _linkedinEstudio(Zend_Config_Xml $linkedinData, App_Form_Manager $mngrEstudio)
	 {
		  if (isset($linkedinData->educations->total)) {
				$this->view->isEstudio = true;
				$formEstudio = array();
				if ($linkedinData->educations->total > 1) {
					 $i = 0;
					 foreach (
					 $linkedinData->educations->education as $educacion
					 ) {
						  $values = array();
						  $form = $mngrEstudio->getForm($i);
						  $educacion = $educacion->toArray();
						  if (isset($educacion['start-date']['year'])) {
								$values['inicio_ano'] = $educacion['start-date']['year'];
						  }
						  if (isset($educacion['end-date']['year'])) {
								if ($educacion['end-date']['year'] <= date('Y')) {
									 $values['fin_ano'] = $educacion['end-date']['year'];
								} elseif ($educacion['end-date']['year'] > date('Y')) {
									 $values['fin_ano'] = date('Y');
									 $values['en_curso'] = '1';
								}
						  }
						  if (isset($educacion['degree'])) {
								$values['id_nivel_estudio'] = $this->_compararNivelEstudio(
										  $educacion['degree']
								);
						  }


						  if (isset($educacion['school-name'])) {
								$evalueInstitucion = $this->_compararInstitucion(
										  $educacion['school-name']
								);
								$values['id_institucion'] = $evalueInstitucion['id'];
								$values['institucion'] = $evalueInstitucion['nombre'];
						  }
						  if (isset($educacion['field-of-study'])) {
								$values['id_carrera'] = $this->_compararCarrera(
										  $educacion['field-of-study']
								);
						  }
						  if (isset($values['id_institucion'])) {
								$form->isValid($values);
								$formEstudio[] = $form;
								//$managerEstudio->getForm($i, $values);
						  }
						  //Zend_Debug::dump($values);
						  $i++;
					 }
				} elseif ($linkedinData->educations->total == 0) {
					 $form = $mngrEstudio->getForm(0);
					 $form->isValid(array());
					 $formEstudio[] = $form;
				} else {
					 $form = $mngrEstudio->getForm(0);
					 $educacion = $linkedinData->educations->education->toArray();
					 if (isset($educacion['start-date']['year'])) {
						  $values['inicio_ano'] = $educacion['start-date']['year'];
					 } else {
						  $values['inicio_ano'] = date('Y') - 1;
					 }
					 if (isset($educacion['end-date']['year'])) {
						  if ($educacion['end-date']['year'] <= date('Y')) {
								$values['fin_ano'] = $educacion['end-date']['year'];
						  } elseif ($educacion['end-date']['year'] > date('Y')) {
								$values['fin_ano'] = date('Y');
								$values['en_curso'] = '1';
						  }
					 } else {
						  $values['fin_ano'] = date('Y');
					 }
					 if (isset($educacion['degree'])) {
						  $values['id_nivel_estudio'] = $this->_compararNivelEstudio(
									 $educacion['degree']
						  );
					 }
					 $evalueInstitucion = 0;
					 if (isset($educacion['school-name'])) {
						  $evalueInstitucion = $this->_compararInstitucion(
									 $educacion['school-name']
						  );
						  $values['id_institucion'] = $evalueInstitucion['id'];
						  $values['institucion'] = $evalueInstitucion['nombre'];
					 }
					 if (isset($educacion['field-of-study'])) {
						  $values['id_carrera'] = $this->_compararCarrera($educacion['field-of-study']);
					 }

					 $form->isValid($values);
					 $formEstudio[] = $form;
					 //$formEstudio[] = $managerEstudio->getForm(0, $values);
				}
		  } else {
				$formEstudio = array(
					 $mngrEstudio->getForm(0));
		  }
		  return $formEstudio;
	 }

	 /**
	  * Lee los datos provenientes del LinkedIn relacionado a Idioma para
	  * luego insertarlo en el formulario
	  *
	  * @param Zend_Config_Xml $linkedinData
	  * @param App_Form_Manager $managerIdioma
	  * @return array
	  */
	 private function _linkedinIdioma(Zend_Config_Xml $linkedinData, App_Form_Manager $managerIdioma)
	 {
		  if (isset($linkedinData->languages->total)) {
				$this->view->isIdioma = true;
				$formIdioma = array();
				$values = array();
				if ($linkedinData->languages->total > 1) {
					 $i = 0;
					 foreach ($linkedinData->languages->language as $idioma) {
						  $form = $managerIdioma->getForm($i);
						  $idioma = $idioma->toArray();
						  $evalue = $this->_compararIdioma($idioma['language']['name']);
						  if ($evalue != 0) {
								$values['id_idioma'] = $evalue;
								$values['nivel_idioma'] = '0';
								$form->setCabeceras($values['id_idioma'], $values['nivel_idioma']);
								$form->isValid($values);
								$formIdioma[] = $form;
								$this->view->isIdioma = $this->view->isIdioma && false;
								//$managerIdioma->getForm($i, $values);
								$i++;
						  } else {
								$this->view->isIdioma = $this->view->isIdioma && true;
						  }
					 }
				} elseif ($linkedinData->languages->total == 0) {
					 $form = $managerIdioma->getForm(0);
					 $formIdioma[] = $form;
				} else {
					 $form = $managerIdioma->getForm(0);
					 $idioma = $linkedinData->languages->language->toArray();
					 if (isset($idioma['language']['name'])) {
						  $evalue = $this->_compararIdioma($idioma['language']['name']);
						  if ($evalue != 0) {
								$values['id_idioma'] = $evalue;
								$values['nivel_idioma'] = '0';
								$form->setCabeceras($values['id_idioma'], $values['nivel_idioma']);
								$form->isValid($values);
								$this->view->isIdioma = $this->view->isIdioma && false;
						  } else {
								$form = $managerIdioma->getForm(0);
								$this->view->isIdioma = $this->view->isIdioma && true;
						  }
						  $formIdioma[] = $form;
					 }
				}
		  } else {
				$form = $managerIdioma->getForm(0);
				$formIdioma = array(
					 $form);
		  }
		  return $formIdioma;
	 }

	 /**
	  * Compara el nombre del idioma retornado por LinkedIn, para obtener el
	  * valor mas cercano de la base de datos
	  *
	  * @param string $nombreIdioma
	  * @return int
	  */
	 private function _compararIdioma($nombreIdioma)
	 {
		  $idioma = new Application_Model_Idioma();
		  $listaIdiomas = $idioma->getIdiomas();
		  $min = self::TOLERANCIA_LEVENSHTEIN_IDIOMA;
		  $value = 0;
		  foreach ($listaIdiomas as $i => $d) {
				$evalue = levenshtein($d, $nombreIdioma);
				if ($evalue < $min) {
					 $min = $evalue;
					 $value = $i;
				}
		  }
		  return $value;
	 }

	 /**
	  * Compara el nombre de la institucion retornado por LinkedIn, para obtener
	  * el valor mas cercano de la base de datos
	  *
	  * @param string $nombreInstitucion
	  * @return int
	  */
	 private function _compararInstitucion($nombreInstitucion)
	 {
		  if ($nombreInstitucion == '') {
				return 0;
		  }
		  /*
			* Hace la comparacion de la institucion ingresada por LinkedIn,
			* con lo obtenido en la base de datos.
			*
			 $institucion = new Application_Model_Institucion();
			 $evalue = $institucion->compararInstitucion($nombreInstitucion);
			 if ($evalue['comparacion'] > self::TOLERANCIA_LEVENSHTEIN_INST) {
			 $evalue['id'] = -1;
			 $evalue['nombre'] = $nombreInstitucion;
			 } */
		  $evalue['id'] = 0;
		  $evalue['nombre'] = $nombreInstitucion;
		  //$evalue['nombre'] = $nombreInstitucion;
		  return $evalue;
	 }

	 /**
	  * Compara el nombre de la carrera retornado por LinkedIn, para obtener
	  * el valor mas cercano de la base de datos
	  *
	  * @param string $nombreCarrera
	  * @return int
	  */
	 private function _compararCarrera($nombreCarrera)
	 {
		  if ($nombreCarrera == '') {
				return 0;
		  }
		  $carrera = new Application_Model_Carrera();
		  $evalue = $carrera->compararCarrera($nombreCarrera);
		  if ($evalue['comparacion'] >= self::TOLERANCIA_LEVENSHTEIN_CARRERA) {
				return 0;
		  }
		  return $evalue['id'];
	 }

	 private function _compararNivelEstudio($nivelEstudio)
	 {
		  if ($nivelEstudio == '') {
				return 0;
		  }
		  $estudio = new Application_Model_NivelEstudio();
		  $evalue = $estudio->compararNivelEstudio($nivelEstudio);
		  if ($evalue['comparacion'] >= self::TOLERANCIA_LEVENSHTEIN_NIVEL_ESTUDIO) {
				return 0;
		  }
		  return $evalue['id'];
	 }

	 private function _crearSlug($valuesPostulante, $lastId)
	 {
		  $slugFilter = new App_Filter_Slug(
					 array(
				'field' => 'slug',
				'model' => $this->_postulante
					 )
		  );

		  $slug = $slugFilter->filter(
					 $valuesPostulante['nombres'] . ' ' .
					 $valuesPostulante['apellido_paterno'] . ' ' .
					 $valuesPostulante['apellido_materno'] . ' ' .
					 substr(md5($lastId), 0, 8)
		  );
		  return $slug;
	 }

	 /**
	  * Registra las experiencias del postulante en la base de datos.
	  *
	  * @param App_Form_Manager $managerExperiencia
	  */
	 private function _guardarExperienciaPostulante(
	 App_Form_Manager $managerExperiencia, $idPostulante
	 )
	 {
		  $formsExperiencia = $managerExperiencia->getForms();
		  foreach ($formsExperiencia as $form) {
				$data = $form->getValues();
				$data['id_postulante'] = $idPostulante;

				if (!isset($data['lugar']))
					 $data['lugar'] = 1;

				unset($data['is_disabled']);
				$experiencia = new Application_Model_Experiencia();



				$experiencia->insert($data);
		  }
		  $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
					 $idPostulante, Application_Model_LogPostulante::EXPERIENCIA
		  );
	 }

	 /**
	  * Registra los estudios del postulante en la base de datos.
	  *
	  * @param App_Form_Manager $managerEstudio
	  */
	 private function _guardarEstudiosPostulante(
	 App_Form_Manager $managerEstudio, $idPostulante
	 )
	 {
		  $instituciones = new Application_Model_Institucion();
		  $carreras = new Application_Model_Carrera();
		  $listaInstituciones = $instituciones->getInstituciones();
		  $listaCarreras = $carreras->getCarreras();
		  $formsEstudios = $managerEstudio->getForms();
		  foreach ($formsEstudios as $form) {

				$data = $form->getValues();

				$data['id_postulante'] = $idPostulante;
				if ($data['id_nivel_estudio'] == 1) {
					 $data['id_carrera'] = null;
					 $data['en_curso'] = 0;
					 $data['otro_institucion'] = null;
					 $data['pais_estudio'] = 0;
				} else {
					 $data['en_curso'] = (bool) $data['en_curso'];
					 $data['otro_institucion'] = $data['institucion'];
				}
				unset($data['id_estudio']);
				unset($data['institucion']);
				unset($data['is_disabled']);

				if ($data['id_institucion'] == 0 || $data['id_institucion'] == '') {
					 $data['id_institucion'] = null;
				} else {
					 if (array_key_exists($data['id_institucion'], $listaInstituciones)) {
						  if ($listaInstituciones[$data['id_institucion']] != $data['otro_institucion']) {
								unset($data['id_institucion']);
						  }
					 }
				}
//            if ($data['id_carrera'] == -1 || $data['id_carrera'] == '') {
//                $data['id_carrera'] = null;
//            } else {
//                if (array_key_exists($data['id_carrera'], $listaCarreras)) {
//                    if ($listaCarreras[$data['id_carrera']] != $data['otro_carrera']) {
//                        $data['id_carrera'] = null;
//                    } else {
//                        $data['id_tipo_carrera'] = null;
//                    }
//                }
//            }
				if (!isset($data['colegiatura_numero'])) {
					 $data['colegiatura_numero'] = null;
				}
				if (array_key_exists($data['id_carrera'], $listaCarreras) && $data['id_carrera'] != 15) {
					 $data['otro_carrera'] = $listaCarreras[$data['id_carrera']];
				}

				if ($data['id_carrera'] != Application_Model_Carrera::OTRO_CARRERA)
					 $data['otro_carrera'] = '';

				if ($data['id_nivel_estudio'] != 9) {
					 unset($data['otro_estudio']);
				}
				if ($data['id_carrera'] == 0 || $data['id_carrera'] == "") {
					 $data['id_carrera'] = null;
				}

				//Si es primaria o secundaria
				if ($data['id_nivel_estudio'] == 2 || $data['id_nivel_estudio'] == 3)
					 unset($data['otro_estudio']);

				$estudio = new Application_Model_Estudio();
				if ($data['id_nivel_estudio'] != 0)
					 $estudio->insert($data);

				$this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
						  $idPostulante, Application_Model_LogPostulante::ESTUDIOS
				);
		  }
	 }

	 /**
	  * Registra los idiomas del postulante en la base de datos.
	  *
	  * @param App_Form_Manager $managerIdioma
	  */
	 private function _guardarIdiomasPostulante(
	 App_Form_Manager $managerIdioma, $idPostulante
	 )
	 {
		  $formsIdiomas = $managerIdioma->getForms();
		  $validaDuplicidad = false;
		  foreach ($formsIdiomas as $form) {
				$dataPost = $form->getValues();
				if ($dataPost['id_idioma'] != "0") {
					 if (!is_null($dataPost['id_idioma'])) {
						  $data['id_idioma'] = $dataPost['id_idioma'];
						  $data['nivel_lee'] = $dataPost['nivel_idioma'];
						  $data['nivel_escribe'] = $dataPost['nivel_idioma'];
						  $data['nivel_hablar'] = $dataPost['nivel_idioma'];
						  $data['id_postulante'] = $idPostulante;
						  $idioma = new Application_Model_DominioIdioma();
						  $idioma->insert($data);
					 }
				}
		  }
		  $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
					 $idPostulante, Application_Model_LogPostulante::IDIOMAS
		  );
	 }

	 /**
	  * Registra los programas de computo del postulante en la base de datos.
	  *
	  * @param App_Form_Manager $managerPrograma
	  */
	 private function _guardarProgramasPostulante(
	 App_Form_Manager $managerPrograma, $idPostulante
	 )
	 {
		  $formsProgramas = $managerPrograma->getForms();
		  foreach ($formsProgramas as $form) {
				$data = $form->getValues();
				unset($data['nombre']);
				unset($data['is_disabled']);
				if ($data['id_programa_computo'] != 0) {
					 if (!is_null($data['id_programa_computo'])) {
						  $data['id_postulante'] = $idPostulante;
						  $programa = new Application_Model_DominioProgramaComputo();
						  $programa->insert($data);
					 }
				}
		  }
		  $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
					 $idPostulante, Application_Model_LogPostulante::PROGRAMAS
		  );
	 }

	 /**
	  * Registra las opciones del postulante en la base de datos.
	  *
	  * @param array $dataPost
	  */
	 private function _guardarOpcionesPostulante(array $dataPost, $idPostulante)
	 {
		  $postulante = new Application_Model_Postulante();
		  $postulante->update(
					 array(
				'prefs_emailing_avisos' =>
				$dataPost['prefs_emailing_avisos'],
				'prefs_emailing_info' =>
				$dataPost['prefs_emailing_info']
					 ), $postulante->getAdapter()->quoteInto('id = ?', $idPostulante)
		  );
	 }

	 /**
	  * Almacena la direccion del CV subido en la base de datos
	  *
	  * @param Application_Form_Paso2 $form
	  */
	 private function _guardarCv(Application_Form_Paso2 $form, $idPostulante, $auth)
	 {
		  $utilfile = $this->_helper->getHelper('UtilFiles');
		  $pathCv = $utilfile->_renameFile($form, 'pathCv', $auth);
		  $arrayPostulante = $this->_postulante->getPostulante($idPostulante);

		  $this->_postulante->update(
					 array(
				'path_cv' =>
				$pathCv
					 ), $this->_postulante->getAdapter()->quoteInto('id = ?', $idPostulante)
		  );

		  $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
					 $idPostulante, Application_Model_LogPostulante::SUBIR_CV, $arrayPostulante['path_cv'], $pathCv
		  );
	 }

	 public function cargafotoAction()
	 {

		  $this->_helper->layout()->disableLayout();
		  $this->_helper->viewRenderer->setNoRender(true);
		  $modulo = $this->getRequest()->getParam("modulo");
		  $tipo = $this->getRequest()->getParam("tipo");

		  $img = ($tipo == "emp") ? 'logotipo' : 'path_foto';
		  $url = ($tipo == "pos") ? ELEMENTS_URL_IMG : ELEMENTS_URL_LOGOS;

		  $r = $this->getRequest();
		  if ($r->isPost()) {
				$session = $this->getSession();
				if ($session->__isset("tmp_img")) {
					 @unlink($session->__get("tmp_img"));
				}
				$tamanomax = $r->__get("filesize");
				$tamano = $_FILES[$img]['size'];
				$admitidos = array(
					 'image/jpg',
					 'image/jpeg',
					 'image/pjpeg',
					 'image/png',
					 'image/x-png');

				//forzando archivo corrupto (tipo de imagen = null)
//            if(empty($_FILES[$img]["type"])) {
//                $pos = strpos($_FILES[$img]["name"], '.');
//                if ($pos) {
//                    $posExt = substr($_FILES[$img]["name"], $pos+1, strlen($_FILES[$img]["name"])-$pos);
//                    $posTipo = array_search($posExt, array(0=>'jpg',1=>'jpeg',2=>'png',3=>'gif'));
//                    if ($posTipo>=0) {
//                        $_FILES[$img]["type"] = $admitidos[$posTipo];
//                    }
//                }
//            }

				if ($tamano <= $tamanomax) {
					 $tipo = $_FILES[$img]["type"];

					 if (array_search($tipo, $admitidos) !== FALSE) {
						  $utilfile = $this->_helper->getHelper('UtilFiles');
						  $archivo = $_FILES[$img]['name'];
						  $tipo = $utilfile->_devuelveExtension($archivo);
						  $extends = array(
								"php",
								"java");
						  if (in_array($tipo, $extends)) {
								echo Zend_Json::encode(array(
									 'status' => 0,
									 'new_image' => '',
									 'url' => '',
									 'id' => $nombre,
									 'msg' => 'ERROR',
									 'name' => $nombre
								));
								die();
						  }
						  $nombre = "temp_" . time() . "." . $tipo;
						  $nombrearchivo = "elements/empleo/img/" . $nombre;
						  $session->__set("tmp_img", $nombrearchivo);
						  move_uploaded_file($_FILES[$img]['tmp_name'], $nombrearchivo);
						  $imgx = new ZendImage();
						  $imgx->loadImage(APPLICATION_PATH . "/../public/" . $nombrearchivo);
						  $imgx->save(APPLICATION_PATH . "/../public/" . $nombrearchivo);

						  echo Zend_Json::encode(array(
								'status' => 1,
								'new_image' => $nombre,
								'url' => '/' . $nombrearchivo,
								'id' => $nombre,
								'msg' => '',
								'name' => $nombre
						  ));
					 } elseif ($_FILES[$img]["type"] == '' || empty($_FILES[$img]["type"])) {
						  echo Zend_Json::encode(array(
								'status' => 0,
								'new_image' => $nombre,
								'url' => '',
								'id' => $nombre,
								'msg' => 'Formato de archivo irreconocible',
								'name' => 'Corrupted'
						  ));
					 } else {
						  echo Zend_Json::encode(array(
								'status' => 0,
								'new_image' => '',
								'url' => '',
								'id' => $nombre,
								'msg' => 'Tipo de archivo no permitido',
								'name' => $nombre
						  ));
					 }
				} else {
					 echo Zend_Json::encode(array(
						  'status' => 0,
						  'new_image' => '',
						  'url' => '',
						  'id' => $nombre,
						  'msg' => 'Tamaño de archivo sobrepasa el limite Permitido',
						  'name' => $nombre
					 ));
				}
		  } else {
				echo Zend_Json::encode(array(
					 'status' => 0,
					 'new_image' => '',
					 'url' => '',
					 'id' => $nombre,
					 'msg' => 'ERROR',
					 'name' => $nombre
				));
		  }
		  die();
	 }

	 public function eliminarfotoAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();
		  $param = $this->_getAllParams();

		  if ($this->_request->isPost() /* && $this->_hash->isValid($param['csrfhash']) */) {
				$modeloPostulante = new Application_Model_Postulante();

				$session = $this->getSession();
				if ($session->__isset("tmp_img")) {
					 @unlink($session->__get("tmp_img"));
				}
				if ($param['rel'] != '') {
					 $value = $modeloPostulante->getPostulante($param['rel']);
					 if ($value['path_foto'] != null) {
						  // @codingStandardsIgnoreStart
						  @unlink(APPLICATION_PATH . '/../public/elements/empleo/cvs/' . $value['path_foto']);
						  @unlink(APPLICATION_PATH . '/../public/elements/empleo/cvs/' . $value['path_foto_uno']);
						  @unlink(APPLICATION_PATH . '/../public/elements/empleo/cvs/' . $value['path_foto_dos']);
						  // @codingStandardsIgnoreEnd
						  $where = $modeloPostulante->getAdapter()->quoteInto('id = ?', $param['rel']);
						  $data['path_foto'] = "";
						  $data['path_foto1'] = "";
						  $data['path_foto2'] = "";
						  $modeloPostulante->update($data, $where);
					 }
					 echo Zend_Json::encode(array(
						  'status' => 1,
						  'msg' => 'Se eliminó correctamente'));
				}
		  } else {
				echo Zend_Json::encode(array(
					 'status' => 0,
					 'msg' => 'Error en la eliminación'));
		  }
	 }

	 public function filtrarCarreraAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();

		  $param = $this->_getAllParams();

		  if ($this->_hash->isValid($param['csrfhash'])) {

				$filtro = new Zend_Filter_StripTags;
				$idTipoCarrera = $filtro->filter($this->_getParam('id_tipo_carrera'));

				$carrera = new Application_Model_Carrera();
				$data = $carrera->filtrarCarrera($idTipoCarrera);
				$this->_response->appendBody(Zend_Json::encode($data));
		  } else {
				echo $this->_messageError;
		  }
	 }

	 public function filtrarDistritosAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();
		  $distritos = new Application_Model_Ubigeo();
		  $idUbigeo = $this->_getParam('id_ubigeo');
		  $tok = $this->_getParam('csrfhash');

		  $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());
		  $requestValido = ($requestValido && $idUbigeo && $tok);

		  if (!$requestValido) {
				exit;
		  }

		  if ($this->_hash->isValid($tok)) {
				if ($idUbigeo > 0) {
					 $data = $distritos->getHijos($idUbigeo);
				}
				if ($idUbigeo == Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID) {
					 $data = $distritos->getHijos(Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID);
				}
				if ($idUbigeo == Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID) {
					 $data = $distritos->getHijos(Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID);
				}
				$this->_response->appendBody(Zend_Json::encode($data));
		  } else {
				echo $this->_messageError;
		  }
	 }

	 public function obtenerTokenAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();
		  /* $refer = $this->getRequest()->getHeader('Referer');
			 $okRefer = stristr($refer,SITE_URL); */

		  if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
				$this->_hash->setTimeout(3600);
				$this->_hash->initCsrfToken();
				$csrfhash = $this->_hash->getValue();
				defined('CSRF_HASH') || define('CSRF_HASH', $csrfhash);

				$this->_response->appendBody(Zend_Json::encode($csrfhash));
		  } else {
				exit(0);
		  }


		  //echo $csrfhash;
	 }

	 /**
	  * Registra los otros estudios del postulante en la base de datos.
	  *
	  * @param App_Form_Manager $managerEstudio
	  */
	 private function _guardarOtrosEstudiosPostulante(
	 App_Form_Manager $managerEstudio, $idPostulante
	 )
	 {
		  $instituciones = new Application_Model_Institucion();
		  $carreras = new Application_Model_Carrera();
		  $listaInstituciones = $instituciones->getInstituciones();
		  $listaCarreras = $carreras->getCarreras();
		  $formsEstudios = $managerEstudio->getForms();
		  foreach ($formsEstudios as $form) {
				$data = $form->getValues();
				if ($data['id_nivel_estudio_tipo'] != 0 && !empty($data['otro_estudio']) && !empty($data['institucion']) && $data['pais_estudio'] != 'none') {
					 $data['id_postulante'] = $idPostulante;
					 $data['otro_institucion'] = $data['institucion'];
					 unset($data['institucion']);
					 unset($data['is_disabled']);
					 if ($data['id_institucion'] == "0" || $data['id_institucion'] == "") {
						  $data['id_institucion'] = null;
					 } else {
						  if (array_key_exists($data['id_institucion'], $listaInstituciones)) {
								if ($listaInstituciones[$data['id_institucion']] != $data['otro_institucion']) {
									 unset($data['id_institucion']);
								}
						  }
						  if ($data['id_nivel_estudio'] != 9) {
								unset($data['otro_estudio']);
						  }
					 }
					 if ($data['id_carrera'] == "0" || $data['id_carrera'] == "") {
						  $data['id_carrera'] = null;
					 } else {
						  if (array_key_exists($data['id_carrera'], $listaCarreras)) {
								if ($listaCarreras[$data['id_carrera']] != $data['otro_carrera']) {
									 $data['id_carrera'] = null;
								} else {
									 $data['id_tipo_carrera'] = null;
								}
						  }
					 }

					 if ($data['colegiatura'] == "0" || $data['colegiatura'] == "")
						  $data['colegiatura_numero'] = 0;

					 $estudio = new Application_Model_Estudio();
					 $estudio->insert($data);
				}
				$this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
						  $idPostulante, Application_Model_LogPostulante::ESTUDIOS
				);
		  }
	 }

	 public function filtrarNivelesAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();
	 }

	 public function facebookAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();
		  $state = $this->getRequest()->getParam('state');
		  $code = $this->getRequest()->getParam('code');
		  $config = $this->getConfig();
		  $appId = $config->apis->facebook->appid;
		  $appSecret = $config->apis->facebook->appsecret;
		  $url = $config->app->siteUrl
					 . '/registro/facebook';

		  $goURL = $this->getRequest()->getParam('go', null);
		  $paramsGo = $goURL;
		  $helper = new App_Controller_Action_Helper_Util();
		  $redirect_uri = urldecode($helper->decodifica(($goURL)));
		  $pathAviso = explode('/', $redirect_uri);
		  $goURL = $redirect_uri;
		  $apiURL = '';

		  if (!empty($goURL)) {
				$apiURL = '/go/' . $paramsGo;
		  }

		  if ($pathAviso[1] == 'ofertas-de-trabajo') {
				$goURL = '/' . $pathAviso[1] . '/' . $pathAviso[2];
		  }
		  $url.=$apiURL;

		  $tokenFacebook = new Zend_Session_Namespace('tokenFacebook');
		  if ($state === $tokenFacebook->token) {
				$url = "https://graph.facebook.com/oauth/access_token?client_id=$appId&redirect_uri=$url&client_secret=$appSecret&code=$code";
				$response = file_get_contents($url);
				$params = null;
				parse_str($response, $params);
				$graphUrl = "https://graph.facebook.com/me?fields=email&access_token=" . $params['access_token'];
				$facebookUser = json_decode(file_get_contents($graphUrl));
				if (!empty($facebookUser->email)) {
					 $modelUsuario = new Application_Model_Usuario();
					 $isValid = $modelUsuario->validacionEmail($facebookUser->email, null, null, Application_Form_Login::ROL_POSTULANTE);
					 if ($isValid) {
						  //$db = $this->getAdapter();
						  $db = Zend_Db_Table_Abstract::getDefaultAdapter();
						  $db->beginTransaction();
						  try {
								$date = date('Y-m-d H:i:s');
								$valuesUsuario = array();
								$valuesUsuario['email'] = $facebookUser->email;
								$pswd = uniqid();
								$valuesUsuario['pswd'] = App_Auth_Adapter_AptitusDbTable::generatePassword(
													 $pswd
								);
								$valuesUsuario['salt'] = '';
								$valuesUsuario['rol'] = Application_Form_Login::ROL_POSTULANTE;
								$valuesUsuario['activo'] = 1;
								$valuesUsuario['ultimo_login'] = $date;
								$valuesUsuario['fh_edicion'] = $date;
								$valuesUsuario['fh_registro'] = $date;
								$valuesUsuario['ip'] = $this->getRequest()->getServer('REMOTE_ADDR');
								$valuesUsuario['confirmar'] = 1;
								$lastId = $modelUsuario->insert($valuesUsuario);
								$valuesPostulante = array();
								$valuesPostulante['id_usuario'] = $lastId;
								$valuesPostulante['nombres'] = (!empty($facebookUser->first_name)) ? $facebookUser->first_name : '';
								$apellidos = $facebookUser->last_name;
								$valuesPostulante['apellidos'] = $apellidos;
								$arrApellidos = explode(' ', $apellidos, 2);
								$valuesPostulante['apellido_paterno'] = (!empty($arrApellidos[0])) ? $arrApellidos[0] : '';
								$valuesPostulante['apellido_materno'] = (!empty($arrApellidos[1])) ? $arrApellidos[1] : '';
								$gender = array(
									 'male' => 'M',
									 'female' => 'F');
								$valuesPostulante['sexo'] = $gender[$facebookUser->gender];
								$slug = $this->_crearSlug($valuesPostulante, $lastId);
								$valuesPostulante['disponibilidad_mudarse'] = '0';
								$valuesPostulante['prefs_confidencialidad'] = '0';
								$valuesPostulante['prefs_emailing_avisos'] = '0';
								$valuesPostulante['prefs_emailing_info'] = '0';
								$valuesPostulante['ultima_actualizacion'] = $date;
								$valuesPostulante['slug'] = $slug;
								$valuesPostulante['path_foto'] = '';
								$valuesPostulante['path_foto1'] = '';
								$valuesPostulante['path_foto2'] = '';
								$modelPostulante = new Application_Model_Postulante();
								$lastIdPostulante = $modelPostulante->insert($valuesPostulante);
								$db->commit();
						  } catch (Zend_Db_Exception $e) {
								$this->getMessenger()->error($this->_messageSuccess);
								$db->rollBack();
								echo $e->getMessage();
						  } catch (Zend_Exception $e) {
								$this->getMessenger()->error($this->_messageSuccess);
								$db->rollBack();
								echo $e->getMessage();
						  }
						  if ($lastIdPostulante != null || $id != null) {
								$this->_helper->LogActualizacionBI->
										  logActualizacionPostulantePaso1($lastIdPostulante, $valuesPostulante);
								$this->_helper->solr->addSolr($lastIdPostulante);
								if ($valuesPostulante['sexo'] == 'M') {
									 $subjectMessage = 'Bienvenido';
								} else {
									 $subjectMessage = 'Bienvenida';
								}
								if ($valuesUsuario['email'] != $this->auth['usuario']->email) {
									 $this->_helper->mail->nuevoUsuario(
												array(
													 'to' => $valuesUsuario['email'],
													 'user' => $valuesUsuario['email'],
													 'fr' => $date,
													 'slug' => $slug,
													 'nombre' => ucwords($valuesPostulante['nombres']),
													 'subjectMessage' => $subjectMessage
												)
									 );
								}
								/* Application_Model_Usuario::auth(
								  $valuesUsuario['email'], $pswd, $valuesUsuario['rol']
								  ); */
								Application_Model_Usuario::authRS(
										  $valuesUsuario['email']
								);
								if (!empty($goURL)) {
									 $this->_redirect($goURL);
								}
								$this->_redirect('/buscar');
						  }
					 } else {
						  Application_Model_Usuario::authRS(
									 $facebookUser->email
						  );
						  if (!empty($goURL)) {
								$this->_redirect($goURL);
						  }
						  $this->_redirect('/buscar');
					 }
				} else {
					 $dataFacebook = new Zend_Session_Namespace('dataFacebook');
					 $arrFacebook = array();
					 $arrFacebook['txtName'] = $facebookUser->first_name;
					 $apellidos = $facebookUser->last_name;
					 $arrApellidos = explode(' ', $apellidos, 2);
					 $arrFacebook['txtFirstLastName'] = $arrApellidos[0];
					 $arrFacebook['txtSecondLastName'] = $arrApellidos[1];
					 $dataFacebook->data = $arrFacebook;
					 $this->getMessenger()->error("La cuenta de facebook no tiene un email valido");
					 $this->_redirect('/');
				}
		  } else {
				$this->getMessenger()->error("Token invalido");
				$this->_redirect('/');
		  }
	 }

	 public function linkedinAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();
		  $config = $this->getConfig();
		  $options = array(
				'version' => '1.0',
				'signatureMethod' => 'HMAC-SHA1',
				'localUrl' => $config->app->siteUrl . '/registro/linkedin',
				'callbackUrl' => $config->app->siteUrl . '/registro/linkedin',
				'requestTokenUrl' => $config->apis->linkedin->requestTokenUrl,
				'userAuthorizationUrl' =>
				$config->apis->linkedin->userAuthorizationUrl,
				'accessTokenUrl' =>
				$config->apis->linkedin->accessTokenUrl,
				'consumerKey' => $config->apis->linkedin->consumerKey,
				'consumerSecret' => $config->apis->linkedin->consumerSecret
		  );
		  $consumer = new Zend_Oauth_Consumer($options);
		  if (isset($_REQUEST['oauth_problem']) && $_REQUEST['oauth_problem'] != "") {
				$this->getMessenger()->error(
						  'En estos momentos el servicio de registro no se encuentra
		  disponible.'
				);
				if ($_REQUEST['oauth_problem'] == 'user_refused') {
					 $dataLinkedin = new Zend_Session_Namespace('dataLinkedin');
					 $dataLinkedin->data = array();
				}
				$this->_redirect('/');
		  }
		  if (!isset($_SESSION['ACCESS_TOKEN'])) {
				if (!empty($_GET)) {
					 $token = $consumer->getAccessToken(
								$_GET, unserialize($_SESSION['REQUEST_TOKEN'])
					 );
					 $_SESSION ['ACCESS_TOKEN'] = serialize($token);
				} else {
					 $token = $consumer->getRequestToken();
					 $_SESSION['REQUEST_TOKEN'] = serialize($token);
					 $consumer->redirect();
				}
		  } else {
				$token = unserialize($_SESSION['ACCESS_TOKEN']);
				$_SESSION ['ACCESS_TOKEN'] = null;
		  }
		  $client = $token->getHttpClient($options);
		  $client->setHeaders('Accept-Language', 'es-ES');
		  $client->setUri($config->apis->linkedin->urlImportData);
		  $client->setMethod(Zend_Http_Client::GET);
		  $response = $client->request();
		  $content = $response->getBody();
		  $linkedinUser = new Zend_Config_Xml($content);
		  //var_dump($linkedinUser);die();
		  if (!empty($linkedinUser->{'email-address'})) {
				$modelUsuario = new Application_Model_Usuario();
				$isValid = $modelUsuario->validacionEmail($linkedinUser->{'email-address'}, null, null, Application_Form_Login::ROL_POSTULANTE);
				if ($isValid) {
					 //$db = $this->getAdapter();
					 $db = Zend_Db_Table_Abstract::getDefaultAdapter();
					 $db->beginTransaction();
					 try {
						  $date = date('Y-m-d H:i:s');
						  $valuesUsuario = array();
						  $valuesUsuario['email'] = $linkedinUser->{'email-address'};
						  $pswd = uniqid();
						  $valuesUsuario['pswd'] = App_Auth_Adapter_AptitusDbTable::generatePassword(
												$pswd
						  );
						  $valuesUsuario['salt'] = '';
						  $valuesUsuario['rol'] = Application_Form_Login::ROL_POSTULANTE;
						  $valuesUsuario['activo'] = 1;
						  $valuesUsuario['ultimo_login'] = $date;
						  $valuesUsuario['fh_edicion'] = $date;
						  $valuesUsuario['fh_registro'] = $date;
						  $valuesUsuario['ip'] = $this->getRequest()->getServer('REMOTE_ADDR');
						  $valuesUsuario['confirmar'] = 1;
						  $lastId = $modelUsuario->insert($valuesUsuario);
						  $valuesPostulante = array();
						  $valuesPostulante['id_usuario'] = $lastId;
						  $country = array(
								'pe' => '2533');
						  $valuesPostulante['pais_residencia'] = $country[$linkedinUser->location->country->code];
						  $valuesPostulante['id_ubigeo'] = $country[$linkedinUser->location->country->code];
						  $valuesPostulante['nombres'] = (!empty($linkedinUser->{'first-name'})) ? $linkedinUser->{'first-name'} : '';
						  $apellidos = $linkedinUser->{'last-name'};
						  $valuesPostulante['apellidos'] = $apellidos;
						  $arrApellidos = explode(' ', $apellidos, 2);
						  $valuesPostulante['apellido_paterno'] = (!empty($arrApellidos[0])) ? $arrApellidos[0] : '';
						  $valuesPostulante['apellido_materno'] = (!empty($arrApellidos[1])) ? $arrApellidos[1] : '';
						  $slug = $this->_crearSlug($valuesPostulante, $lastId);
						  if (!empty($linkedinUser->{'date-of-birth'})) {
								$year = $linkedinUser->{'date-of-birth'}->year;
								$month = $linkedinUser->{'date-of-birth'}->month;
								$day = $linkedinUser->{'date-of-birth'}->day;
								$valuesPostulante['fecha_nac'] = "$year-$month-$day";
						  }
						  if (!empty($linkedinUser->{'phone-numbers'}->total)) {
								if ($linkedinUser->{'phone-numbers'}->{'phone-number'}->{'phone-type'} === 'mobile')
									 $valuesPostulante['celular'] = $linkedinUser->{'phone-numbers'}->{'phone-number'}->{'phone-number'};
								else
									 $valuesPostulante['telefono'] = $linkedinUser->{'phone-numbers'}->{'phone-number'}->{'phone-number'};
						  }
						  $valuesPostulante['disponibilidad_mudarse'] = '0';
						  $valuesPostulante['prefs_confidencialidad'] = '0';
						  $valuesPostulante['prefs_emailing_avisos'] = '0';
						  $valuesPostulante['prefs_emailing_info'] = '0';
						  $valuesPostulante['ultima_actualizacion'] = $date;
						  $valuesPostulante['slug'] = $slug;
						  $valuesPostulante['path_foto'] = '';
						  $valuesPostulante['path_foto1'] = '';
						  $valuesPostulante['path_foto2'] = '';
						  $valuesPostulante['direccion'] = $linkedinUser->{'main-address'};
						  $modelPostulante = new Application_Model_Postulante();
						  $lastIdPostulante = $modelPostulante->insert($valuesPostulante);
						  if (!empty($linkedinUser->educations->total)) {
								$modelEstudio = new Application_Model_Estudio();
								$arrEducacion = array();
								if ($linkedinUser->educations->total === '1') {
									 $arrEducacion[] = $linkedinUser->educations->education->toArray();
								} else {
									 foreach (
									 $linkedinUser->educations->education as $educacion
									 ) {
										  $arrEducacion[] = $educacion->toArray();
									 }
								}
								foreach ($arrEducacion as $educacion) {
									 $values = array(
										  'id_postulante' => $lastIdPostulante,
										  'nombre' => '',
										  'inicio_mes' => '12',
										  'pais_estudio' => 2533
									 );
									 if (isset($educacion['start-date']['year'])) {
										  $values['inicio_ano'] = $educacion['start-date']['year'];
									 } else {
										  $values['inicio_ano'] = date('Y');
									 }
									 if (isset($educacion['end-date']['year'])) {
										  if ($educacion['end-date']['year'] <= date('Y')) {
												$values['fin_ano'] = $educacion['end-date']['year'];
										  } elseif ($educacion['end-date']['year'] > date('Y')) {
												$values['fin_ano'] = date('Y');
												$values['en_curso'] = '1';
										  }
									 }
									 if (isset($educacion['degree'])) {
										  $values['id_nivel_estudio'] = $this->_compararNivelEstudio(
													 $educacion['degree']
										  );
									 }
									 if (isset($educacion['school-name'])) {
										  $evalueInstitucion = $this->_compararInstitucion(
													 $educacion['school-name']
										  );
										  //$values['id_institucion'] = $evalueInstitucion['id'];
										  $values['otro_institucion'] = $evalueInstitucion['nombre'];
									 }
									 if (isset($educacion['field-of-study'])) {
										  $id_carrera = $this->_compararCarrera(
													 $educacion['field-of-study']
										  );
										  if (!empty($id_carrera))
												$values['id_carrera'] = $id_carrera;
									 }
									 $modelEstudio->insert($values);
								}
						  }
						  if (!empty($linkedinUser->positions->total)) {
								$modelExperiencia = new Application_Model_Experiencia();
								$arrEmpresa = array();
								if ($linkedinUser->positions->total === '1') {
									 $arrEmpresa[] = $linkedinUser->positions->position->toArray();
								} else {
									 foreach ($linkedinUser->positions->position as $empresa) {
										  $arrEmpresa[] = $empresa->toArray();
									 }
								}
								foreach ($arrEmpresa as $empresa) {
									 $values = array(
										  'id_postulante' => $lastIdPostulante,
										  'id_nivel_puesto' => 10,
										  'id_area' => 26,
										  'id_puesto' => 1292
									 );
									 if (isset($empresa['company']['name'])) {
										  $values['otra_empresa'] = $empresa['company']['name'];
									 } else {
										  $values['otra_empresa'] = '';
									 }
									 if (isset($empresa['company']['industry'])) {
										  $values['otro_rubro'] = $empresa['company']['industry'];
									 }
									 if (isset($empresa['title'])) {
										  $values['otro_puesto'] = $empresa['title'];
									 }
									 if (isset($empresa['start-date']['month'])) {
										  $values['inicio_mes'] = $empresa['start-date']['month'];
									 } else {
										  $values['inicio_mes'] = date('n');
									 }
									 if (isset($empresa['start-date']['year'])) {
										  $values['inicio_ano'] = $empresa['start-date']['year'];
									 } else {
										  $values['inicio_ano'] = date('Y');
									 }
									 if ($empresa['is-current'] == 'true') {
										  $values['en_curso'] = '1';
										  $values['fin_mes'] = date('n');
										  $values['fin_ano'] = date('Y');
									 } else {
										  $values['en_curso'] = '0';
										  if (isset($empresa['end-date']['month'])) {
												$values['fin_mes'] = $empresa['end-date']['month'];
										  } else {
												$values['fin_mes'] = date('n');
										  }
										  if (isset($empresa['end-date']['year'])) {
												$values['fin_ano'] = $empresa['end-date']['year'];
										  } else {
												$values['fin_ano'] = date('Y');
										  }
									 }
									 if (isset($empresa['summary'])) {
										  $values['comentarios'] = substr($empresa['summary'], 0, 140);
									 }
									 $modelExperiencia->insert($values);
								}
						  }
						  if (!empty($linkedinUser->languages->total)) {
								$modelIdioma = new Application_Model_DominioIdioma();
								$arrIdioma = array();
								if ($linkedinUser->languages->total === '1') {
									 $arrIdioma[] = $linkedinUser->languages->language->toArray();
								} else {
									 foreach ($linkedinUser->languages->language as $idioma) {
										  $arrIdioma[] = $idioma->toArray();
									 }
								}
								$languages = array(
									 'elementary' => 'basico',
									 'limited-working' => 'basico',
									 'professional_working' => 'basico',
									 'full_professional' => 'intermedio',
									 'native_or_bilingual' => 'avanzado',
								);
								foreach ($arrIdioma as $idioma) {
									 $values = array(
										  'id_postulante' => $lastIdPostulante
									 );
									 $evalue = $this->_compararIdioma($idioma['language']['name']);
									 if ($evalue != -1) {
										  $values['id_idioma'] = $evalue;
										  if (isset($idioma['proficiency']['level']))
												$level = $languages[$idioma['proficiency']['level']];
										  else
												$level = 'basico';
										  $values['nivel_lee'] = $level;
										  $values['nivel_escribe'] = $level;
										  $values['nivel_hablar'] = $level;
										  $modelIdioma->insert($values);
									 }
								}
						  }
						  $db->commit();
					 } catch (Zend_Db_Exception $e) {
						  $this->getMessenger()->error($this->_messageSuccess);
						  $db->rollBack();
						  echo $e->getMessage();
					 } catch (Zend_Exception $e) {
						  $this->getMessenger()->error($this->_messageSuccess);
						  $db->rollBack();
						  echo $e->getMessage();
					 }
					 if ($lastIdPostulante != null || $id != null) {
						  $this->_helper->LogActualizacionBI->
									 logActualizacionPostulantePaso1($lastIdPostulante, $valuesPostulante);
						  $this->_helper->solr->addSolr($lastIdPostulante);
						  $subjectMessage = 'Bienvenido';
						  if ($valuesUsuario['email'] != $this->auth['usuario']->email) {
								$this->_helper->mail->nuevoUsuario(
										  array(
												'to' => $valuesUsuario['email'],
												'user' => $valuesUsuario['email'],
												'fr' => $date,
												'slug' => $slug,
												'nombre' => ucwords($valuesPostulante['nombres']),
												'subjectMessage' => $subjectMessage
										  )
								);
						  }
						  /* Application_Model_Usuario::auth(
							 $valuesUsuario['email'], $pswd, $valuesUsuario['rol']
							 ); */
						  Application_Model_Usuario::authRS(
									 $valuesUsuario['email']
						  );
						  $this->_redirect('/registro/paso3');
					 }
				} else {
					 Application_Model_Usuario::authRS(
								$linkedinUser->{'email-address'}
					 );
					 $this->_redirect('/mi-cuenta');
				}
		  } else {
				$dataLinkedin = new Zend_Session_Namespace('dataLinkedin');
				$arrLinkedin = array();
				$arrLinkedin['txtName'] = $linkedinUser->{'first-name'};
				$apellidos = $linkedinUser->{'last-name'};
				$arrApellidos = explode(' ', $apellidos, 2);
				$arrLinkedin['txtFirstLastName'] = $arrApellidos[0];
				$arrLinkedin['txtSecondLastName'] = $arrApellidos[1];
				$year = $linkedinUser->{'date-of-birth'}->year;
				$month = $linkedinUser->{'date-of-birth'}->month;
				$day = $linkedinUser->{'date-of-birth'}->day;
				$arrLinkedin['txtBirthDay'] = "$day/$month/$year";
				$dataLinkedin->data = $arrLinkedin;
				$this->getMessenger()->error("La cuenta de linkedin no tiene un email valido");
				$this->_redirect('/');
		  }
	 }

	 public function filtrarUbigeoAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();
		  $distritos = new Application_Model_Ubigeo();
		  $ubicacion = $this->_getParam('value');
		  $tok = $this->_getParam('csrfhash');

		  $data = array(
				'status' => '0',
				"messages" => $this->_messageError
		  );

		  $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());
		  $requestValido = ($requestValido && $ubicacion && $tok);
		  if (!$requestValido) {
				exit;
		  }

		  if ($this->_hash->isValid($tok)) {

				$filter = new Zend_Filter();
				$filter->addFilter(new Zend_Filter_StripTags());
				$ubicacion = $filter->filter($ubicacion);
				$ubicacion = strtolower($ubicacion);
				$Items = $distritos->getUbicacionByName($ubicacion, 3);
				$data = array(
					 'status' => '1',
					 "messages" => "Sus fueron encontrados.",
					 'items' => $Items
				);
		  }


		  $this->_response->appendBody(Zend_Json::encode($data));
	 }

	 public function areasInteresAction()
	 {


		  $areas = new Application_Model_Area();
		  $NivelPuesto = new Application_Model_NivelPuesto();
		  $this->view->areasInteres = $areas->getAreasInteres();
		  $this->view->nivelpuesto = $NivelPuesto->getNiveles_old();
	 }

	 public function filtrarAptitudesAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();
		  $aptitud = $this->_getParam('value');
		  $tok = $this->_getParam('csrfhash');

		  //$Aptitudes= new Application_Model_Aptitudes();
		  $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());
		  $requestValido = ($requestValido && $aptitud/* && $tok */);
		  if (!$requestValido) {
				exit;
		  }
		  try {

				// if ($this->_hash->isValid($tok)) {
				$filter = new Zend_Filter();
				$filter->addFilter(new Zend_Filter_StripTags());
				$aptitud = $filter->filter($aptitud);
				$aptitud = strtolower($aptitud);
				$modeAptitud = new Solr_SolrAptitud();
				$Items = $modeAptitud->getAptitudByName($aptitud);
				$data = array(
					 'status' => '1',
					 "messages" => "Sus fueron encontrados.",
					 'items' => $Items
				);
//             } else {
//                 $data=array();
//                 $data['status']=0;
//                 $data['messages']=$this->_messageError;
//             }
		  } catch (Solarium\Exception\HttpException $excS) {
				$data = array(
					 'status' => '0',
					 "messages" => $this->_messageError
				);
		  } catch (Exception $exc) {
				$data = array(
					 'status' => '0',
					 "messages" => $this->_messageError
				);
		  }




		  $this->_response->appendBody(Zend_Json::encode($data));
	 }

}
