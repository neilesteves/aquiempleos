<?php

class Admin_RegistroEmpresaController extends App_Controller_Action_Admin
{
	 protected $_empresa;
	 protected $_usuario;
	 protected $_usuarioempresa;
	 protected $_url;

	 public function init()
	 {
		  parent::init();
		  $this->_empresa = new Application_Model_Empresa();
		  $this->_usuario = new Application_Model_Usuario();
		  $this->_usuarioempresa = new Application_Model_UsuarioEmpresa();
		  $this->_url = '/admin/gestion/callcenter';
		  Zend_Layout::getMvcInstance()->assign(
				'bodyAttr', array('id' => 'perfilReg','class'=>'noMenu')
		  );
	 }

	 public function indexAction()
	 {
		  $this->view->modulo = $this->getRequest()->getModuleName();
		  $this->view->controlador = $this->getRequest()->getControllerName();
		  $this->view->idEmpresa = null;

		  $config = Zend_Registry::get("config");
		  // @codingStandardsIgnoreStart
		  $this->view->numPalabraRazonComercial = $config->empresa->numeroPalabra->razoncomercial;
		  $this->view->numPalabraRazonSocial = $config->empresa->numeroPalabra->razonsocial;
		  // @codingStandardsIgnoreEnd

		  $util = new App_Util();
		  $formatSizeLogo = $util->formatSizeUnits($config->app->maxSizeLogo);
		  $config->formatSizeLogo = $formatSizeLogo;
		  $this->view->config = $config;

		  $id = null;
		  $idUsuario = null;

		  $replaceSlug = $this->getHelper('Replace');

		  $frmEmpresa = new Application_Form_Paso1Empresa($idUsuario);
		  $frmEmpresa->validadorRuc($id);
		  $frmEmpresa->validadorNombreComercial($id);
		  $frmEmpresa->validadorRazonSocial($id);

		  $ubigeo = new Application_Model_Ubigeo();
		  $arrayUbigeo = $ubigeo->getHijos(Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID);
		  //$frmEmpresa->getElement('id_distrito')->addMultioptions($arrayUbigeo);

		  $frmUsuario = new Application_Form_Paso1Usuario($idUsuario);
		  $frmUsuario->validadorEmail($idUsuario, Application_Form_Login::ROL_EMPRESA_USUARIO);
		  $frmUsuario->validadorEmail($idUsuario, Application_Form_Login::ROL_EMPRESA_ADMIN);
		  $img = $this->view->imgPhoto = '';

		  $frmAdministrador = new Application_Form_Paso1Administrador(null);
		  $valPostUbigeo = '';

		  if ($this->_request->isPost()) {
				$allParams = $this->_getAllParams();

				$validEmpresa = $frmEmpresa->isValid($allParams);
				$validUsuario = $frmUsuario->isValid($allParams);
				$validAdministrador = $frmAdministrador->isValid($allParams);
				$valPostUbigeo = $allParams['id_departamento'];


				if ($validEmpresa && $validUsuario && $validAdministrador) {

					 $utilfile =   $this->_helper->getHelper('UtilFiles');
					 $nuevosNombres = $utilfile->_renameFile($frmEmpresa, 'logotipo', "image-empresa");

					 $valuesUsuario = $frmUsuario->getValues();
					 $valuesEmpresa = $frmEmpresa->getValues();
					 $valuesAdministrador = $frmAdministrador->getValues();
					 $date = date('Y-m-d H:i:s');

					 try {
						  $db = $this->getAdapter();
						  $db->beginTransaction();

						  // Datos adicionales q no vienen del form
						  $pswd = $valuesUsuario['pswd'];
						  $valuesUsuario['salt'] = '';
						  $valuesUsuario['rol'] = Application_Form_Login::ROL_EMPRESA_ADMIN;
						  $valuesUsuario['activo'] = 1;
						  $valuesUsuario['ultimo_login'] = $date;
						  $valuesUsuario['fh_registro'] = $date;
						  $valuesUsuario['pswd'] =
								App_Auth_Adapter_AptitusDbTable::generatePassword(
									 $valuesUsuario['pswd']
								);
						  $valuesUsuario['ip'] = $this->getRequest()->getServer('REMOTE_ADDR');
						  unset($valuesUsuario['pswd2']);

						  if(isset($valuesUsuario['auth_token'])){
								unset($valuesUsuario['auth_token']);
						  }

						  $lastId = $this->_usuario->insert($valuesUsuario);


						  //Captura de los valores de Empresa
						  $valuesEmpresa['id_ubigeo'] = $this->_helper->Util->getUbigeo($valuesEmpresa);
						  $valuesEmpresa['id_usuario'] = $lastId;
						  $valuesEmpresa['verificada'] = 0;
						  $valuesEmpresa["razon_social"] = $valuesEmpresa["razonsocial"];
						  $valuesEmpresa["ruc"] = $valuesEmpresa["num_ruc"];
						  unset($valuesEmpresa["razonsocial"]);
						  unset($valuesEmpresa["num_ruc"]);

						  $valuesEmpresaDos["razon_social"] = $valuesEmpresa["razon_social"];
						  $valuesEmpresaDos["nombre_comercial"] = $valuesEmpresa["nombrecomercial"];
						  $valuesEmpresa['slug_empresa'] = $replaceSlug->cleanSlugEmpresa($valuesEmpresa["nombrecomercial"]);
						  $valuesEmpresaDos["ruc"] = $valuesEmpresa["ruc"];
						  $slug = $this->_crearSlug($valuesEmpresa, $lastId);
						  $valuesEmpresa['slug'] = $slug;
						  $valuesEmpresa['ultima_actualizacion'] = $date;
						  if ($valuesEmpresa['logotipo'] == NULL) {
								$valuesEmpresa['logotipo']=$img;
								$valuesEmpresa['logo1']=$img;
								$valuesEmpresa['logo2']=$img;
								$valuesEmpresa['logo3']=$img;
						  } else {
								$valuesEmpresa['logotipo']=$nuevosNombres[0];
								$valuesEmpresa['logo1']=$nuevosNombres[1];
								$valuesEmpresa['logo2']=$nuevosNombres[2];
								$valuesEmpresa['logo3']=$nuevosNombres[3];
						  }
						  unset($valuesEmpresa['id_departamento']);
						  unset($valuesEmpresa['id_distrito']);
						  unset($valuesEmpresa['id_provincia']);

						  $valuesEmpresaDos["id_rubro"] = $valuesEmpresa["rubro"];
						  $valuesEmpresaDos["id_usuario"] = $valuesEmpresa["id_usuario"];

						  $valuesEmpresaDos["logo"] = $valuesEmpresa["logotipo"];
						  $valuesEmpresaDos["logo1"] = $valuesEmpresa["logo1"];
						  $valuesEmpresaDos["logo2"] = $valuesEmpresa["logo2"];
						  $valuesEmpresaDos["slug"] = $valuesEmpresa["slug"];
						  $valuesEmpresaDos["slug_empresa"] = $valuesEmpresa["slug_empresa"];
						  $valuesEmpresaDos["verificada"] = $valuesEmpresa["verificada"];
						  $valuesEmpresaDos["id_ubigeo"] = $valuesEmpresa["id_ubigeo"];
						  $valuesEmpresaDos["razon_comercial"] = $valuesEmpresa["nombrecomercial"];
						  $lastIdEmpresa = $this->_empresa->insert($valuesEmpresaDos);

						  //registramos algunos datillos en la tabla categoria_postulacion
						  $extra = $this->_helper->getHelper("RegistrosExtra");
						  $extra->insertarCategoriaPostulacion($lastIdEmpresa);

						  //Usuario Empresa
						  $valuesUsuarioEmpresa["id_usuario"] = $lastId;
						  $valuesUsuarioEmpresa["id_empresa"] = $lastIdEmpresa;
						  $valuesUsuarioEmpresa["nombres"] = $allParams["nombres"];
						  $valuesUsuarioEmpresa["apellidos"] = $allParams["apellidos"];
						  $valuesUsuarioEmpresa["puesto"] = $allParams["puesto"];
						  $valuesUsuarioEmpresa["area"] = $allParams["area"];
						  $valuesUsuarioEmpresa["telefono"] = $allParams["telefono"];
						  $valuesUsuarioEmpresa["telefono2"] = $allParams["telefono2"];
						  $valuesUsuarioEmpresa["anexo"] = $allParams["anexo"];
						  $valuesUsuarioEmpresa["anexo2"] = $allParams["anexo2"];
						  $valuesUsuarioEmpresa["extension"] ="";
						  $valuesUsuarioEmpresa["creador"] = 1;
						  $this->_usuarioempresa->insert($valuesUsuarioEmpresa);

						  $db->commit();
						  $this->_helper->mail->nuevaEmpresa(
								array(
									 'to' => $valuesUsuario['email'],
									 'nombre' => $valuesUsuarioEmpresa['nombres'],
									 'empresa' => $valuesEmpresaDos['nombre_comercial']
								)
						  );

					 } catch (Zend_Db_Exception $e) {
							$db->rollBack();
							echo $e->getMessage();
					 } catch (Zend_Exception $e) {
						  $this->getMessenger()->error($this->_messageError);
						  echo $e->getMessage();
					 }

					 if ($lastIdEmpresa != null || $id != null) {
						  $this->getMessenger()->success('Registro de empresa exitoso.');
						  $this->_redirect($this->_url);
					 }
				} else {

					 if ($valPostUbigeo == Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID) {
						  $arrayUbigeo = $ubigeo->getHijos(
								Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID
						  );
						  $frmEmpresa->getElement('id_distrito')->clearMultiOptions();
						  $frmEmpresa->getElement('id_distrito')
								->addMultiOption('none', 'Seleccione Distrito');
						  $frmEmpresa->getElement('id_distrito')->addMultioptions($arrayUbigeo);
					 }
					 if ($valPostUbigeo == Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID) {
						  $arrayUbigeo = $ubigeo->getHijos(
								Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID
						  );
						  $frmEmpresa->getElement('id_distrito')->clearMultiOptions();
						  $frmEmpresa->getElement('id_distrito')
								->addMultiOption('none', 'Seleccione Distrito');
						  $frmEmpresa->getElement('id_distrito')->addMultioptions($arrayUbigeo);
					 }
				}
		  }
		  $this->view->frmEmpresa = $frmEmpresa;
		  $this->view->frmUsuario = $frmUsuario;
		  $this->view->frmAdministrador = $frmAdministrador;
	 }


	 private function _crearSlug($valuesPostulante, $lastId)
	 {
		  $slugFilter = new App_Filter_Slug(
				array('field' => 'slug',
				'model' => $this->_empresa )
		  );

		  $slug = $slugFilter->filter(
				$valuesPostulante['razon_social'].' '.
				$valuesPostulante['ruc'].' '.
				substr(md5($lastId), 0, 8)
		  );
		  return $slug;
	 }

	 public function validarRucAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();

		  $nruc = $this->_getParam('ndoc');
		  $idEmp = $this->_getParam('idEmp');

		  $_empresa = new Application_Model_Empresa();
		  $isValid ='';
		  if ($idEmp!= null) {
				$isValid = $_empresa->validacionNRuc($nruc, null, $idEmp);
		  } else {
				$isValid = $_empresa->validacionNRuc($nruc, null, false);
		  }

		  $data = array(
				'status' => $isValid
		  );
		  $this->_response->appendBody(Zend_Json::encode($data));
	 }
	 public function validarRazonsocialAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();

		  $rs = $this->_getParam('ndoc');
		  $idEmp = $this->_getParam('idEmp');
		  $_empresa = new Application_Model_Empresa();
		  $isValid ='';

		  //Valida que sea alfanumerico y que contenga spacio
		  preg_match_all("/[^0-9-a-zA-Z-[:space:]-ñ-áéíóúAÉÍÓÚÑñ]/", $rs, $cantError);

		  $countDigError = 0;
		  foreach ($cantError[0] as $error) {
				$countDigError = $countDigError +1;
		  }

		  if ($countDigError == 0) {
				$config = Zend_Registry::get("config");
				$val = $this->_helper->Contador->contadorPalabraText($rs, $config->empresa->numeroPalabra->razonsocial);

				if ($val != false) {
					 if ($idEmp!= null) {
						  $isValid = $_empresa->validacionCampoRepetido("razon_social", $rs, null, $idEmp);
					 } else {
						  $isValid = $_empresa->validacionCampoRepetido("razon_social", $rs, null, false);
					 }
				} else {
					 $isValid = $config->empresa->numeroPalabra->razonsocial;
				}
		  } else {
				$isValid = 'error';
		  }

		  $data = array(
				'status' => $isValid
		  );
		  $this->_response->appendBody(Zend_Json::encode($data));
	 }
	 public function validarNombrecomercialAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();

		  $rs = $this->_getParam('ndoc');
		  $idEmp = $this->_getParam('idEmp');

		  $config = Zend_Registry::get("config");

		  //Valida que sea alfanumerico y que contenga spacio
		  preg_match_all("/[^0-9-a-zA-Z-[:space:]-ñ-áéíóúAÉÍÓÚÑñ]/", $rs, $cantError);

		  $countDigError = 0;
		  foreach ($cantError[0] as $error) {
				$countDigError = $countDigError +1;
		  }

		  if ($countDigError == 0) {
				$val = $this->_helper->Contador->contadorPalabraText($rs, $config->empresa->numeroPalabra->razoncomercial);

				$_empresa = new Application_Model_Empresa();

				$isValid ='';
				if ($val != false) {
					 if ($idEmp!= null) {
						  $isValid = $_empresa->validacionCampoRepetido("nombre_comercial", $rs, null, $idEmp);
					 } else {
						  $isValid = $_empresa->validacionCampoRepetido("nombre_comercial", $rs, null, false);
					 }
				} else {
					 $isValid = $config->empresa->numeroPalabra->razonsocial;
				}
		  } else {
				$isValid = 'error';
		  }

		  $data = array(
				'status' => $isValid
		  );
		  $this->_response->appendBody(Zend_Json::encode($data));
	 }

	 public function eliminarfotoAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();
		  $param = $this->_getAllParams();
		  if ($this->_request->isPost()) {

				$modeloEmpresa = new Application_Model_Empresa();

				$session = $this->getSession();
				if ($session->__isset("tmp_img")) {
					 @unlink($session->__get("tmp_img"));
				}

				if ($param['rel']!= '') {
					 $value = $modeloEmpresa->getEmpresa($param['rel']);
					 if ($value['logo']!= null) {
						  // @codingStandardsIgnoreStart
						  unlink(APPLICATION_PATH.'/../public/elements/empleo/logos/'.$value['logo']);
						  unlink(APPLICATION_PATH.'/../public/elements/empleo/logos/'.$value['logo1']);
						  unlink(APPLICATION_PATH.'/../public/elements/empleo/logos/'.$value['logo2']);
						  // @codingStandardsIgnoreEnd
						  $where = $modeloEmpresa->getAdapter()->quoteInto('id = ?', $param['rel']);
						  $data = array();
						  $data['logo'] = null;
						  $data['logo1'] = null;
						  $data['logo2'] = null;
						  $modeloEmpresa->update($data, $where);
						  $anuncio = new Application_Model_AnuncioWeb();
						  $anuncio->updateLogoAnuncio($param['rel'], '');
					 }
				}
		  }

	 }

}
