<?php

class Empresa_RegistroEmpresaController extends App_Controller_Action_Empresa
{
    protected $_empresa;
    protected $_usuario;
    protected $_usuarioempresa;
    protected $_url;
    protected $_messageError;
    //Buscamas
    private $_buscamasConsumerKey;
    private $_buscamasPublishUrl;
    private $_buscamasUrl;

    public function init()
    {
        parent::init();

        $this->_cache  = Zend_Registry::get('cache');
        $this->_config = Zend_Registry::get('config');

        $this->_empresa        = new Application_Model_Empresa();
        $this->_usuario        = new Application_Model_Usuario();
        $this->_usuarioempresa = new Application_Model_UsuarioEmpresa();
        $this->_messageError   = 'Acceso denegado';
        $this->_url            = '/empresa/registro-empresa/paso2';
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr',
            array(
            'id' => 'perfilReg',
            'class' => 'noMenu')
        );
    }

    public function indexAction()
    {
        $this->view->headTitle()->set('Registro de empresas en AquiEmpleos | AquiEmpleos');
        if (isset($this->auth)) {
            $this->_redirect(
                $this->view->url(
                    array(
                        'module' => 'empresa',
                        'controller' => 'mi-cuenta',
                        'action' => 'index')
                )
            );
        }
        $config                    = $this->getConfig();
        $util                      = new App_Util();
        $formatSizeLogo            = $util->formatSizeUnits($config->app->maxSizeLogo);
        $config->formatSizeLogo    = $formatSizeLogo;
        $this->view->config        = $config;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_PUBLICA_AVISO;

        $this->view->modulo      = $this->getRequest()->getModuleName();
        $this->view->controlador = $this->getRequest()->getControllerName();
        $this->view->idEmpresa   = null;

        $config                               = Zend_Registry::get("config");
        // @codingStandardsIgnoreStart
        $this->view->numPalabraRazonComercial = $config->empresa->numeroPalabra->razoncomercial;
        $this->view->numPalabraRazonSocial    = $config->empresa->numeroPalabra->razonsocial;
        // @codingStandardsIgnoreEnd

        $id        = null;
        $idUsuario = null;

        $replaceSlug = $this->getHelper('Replace');

        $frmEmpresa = new Application_Form_Paso1Empresa($idUsuario);
        $frmEmpresa->validadorRuc($id);
        $frmEmpresa->validadorNombreComercial($id);
        $frmEmpresa->validadorRazonSocial($id);

        $ubigeo = new Application_Model_Ubigeo();
        //      $arrayUbigeo = $ubigeo->getHijos(Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID);
//        $frmEmpresa->getElement('id_distrito')->addMultioptions($arrayUbigeo);

        $frmUsuario           = new Application_Form_Paso1Usuario($idUsuario);
        $frmUsuario->validadorEmail($idUsuario,
            Application_Form_Login::ROL_EMPRESA_USUARIO);
        $frmUsuario->validadorEmail($idUsuario,
            Application_Form_Login::ROL_EMPRESA_ADMIN);
        $img                  = $this->view->imgPhoto = '';

        $frmAdministrador = new Application_Form_Paso1Administrador(null);
        $valPostUbigeo    = '';

        $this->view->headScript()->appendFile($this->view->S('/eb/js/empresa/form.registro.js'));

        if ($this->_request->isPost()) {

            $allParams    = $this->_getAllParams();
           /// var_dump($allParams);
            $validEmpresa = $frmEmpresa->isValid($allParams);
         //   var_dump($frmEmpresa->rubro->getMessages());
            $frmUsuario->getElement('auth_token')->clearValidators();
            $frmUsuario->getElement('auth_token')->setRequired(false);

            $frmAdministrador->getElement('csrf_token')->clearValidators();
            $frmAdministrador->getElement('csrf_token')->setRequired(false);

            $validUsuario       = $frmUsuario->isValid($allParams);
            $validAdministrador = $frmAdministrador->isValid($allParams);
            $valPostUbigeo      = $allParams['id_departamento'];
         // var_dump($validEmpresa , $validUsuario , $validAdministrador);exit;
            if ($validEmpresa && $validUsuario && $validAdministrador) {

                $utilfile      = $this->_helper->getHelper('UtilFiles');
                $nuevosNombres = $utilfile->_renameFile($frmEmpresa, 'logotipo',
                    "image-empresa");

                $valuesUsuario       = $frmUsuario->getValues();
                $valuesEmpresa       = $frmEmpresa->getValues();

                $valuesAdministrador = $frmAdministrador->getValues();

                $date = date('Y-m-d H:i:s');

                    try {
                        // Datos adicionales q no vienen del form
                        $pswd                          = $valuesUsuario['pswd'];
                        $valuesUsuario['salt']         = '';
                        $valuesUsuario['rol']          = Application_Form_Login::ROL_EMPRESA_ADMIN;
                        $valuesUsuario['activo']       = 1;
                        $valuesUsuario['ultimo_login'] = $date;
                        $valuesUsuario['fh_registro']  = $date;
                        $valuesUsuario['fh_edicion']  = $date;
                        $valuesUsuario['pswd']         = App_Auth_Adapter_AptitusDbTable::generatePassword(
                                $valuesUsuario['pswd']
                        );
                        $valuesUsuario['ip']           = $this->getRequest()->getServer('REMOTE_ADDR');
                        unset($valuesUsuario['pswd2']);

                        if (isset($valuesUsuario['auth_token'])) {
                            unset($valuesUsuario['auth_token']);
                        }

                        $valuesUsuario['email'] = trim($valuesUsuario['email']);
                        $lastId                 = $this->_usuario->insert($valuesUsuario);

                        //Captura de los valores de Empresa
                        $valuesEmpresa['id_ubigeo']    = $this->_helper->Util->getUbigeo($valuesEmpresa);
                        $valuesEmpresa['id_usuario']   = $lastId;
                        $valuesEmpresa['verificada']   = 0;
                        $valuesEmpresa["razon_social"] = $valuesEmpresa["razonsocial"];
                        $valuesEmpresa["ruc"]          = $valuesEmpresa["num_ruc"];
                        unset($valuesEmpresa["razonsocial"]);
                        unset($valuesEmpresa["num_ruc"]);

                        $valuesEmpresaDos["razon_social"]      = $valuesEmpresa["razon_social"];
                        $valuesEmpresaDos["nombre_comercial"]  = $valuesEmpresa["nombrecomercial"];
                        $valuesEmpresaDos["tipo_doc"]          = $valuesEmpresa["tipo_doc"];
                        $valuesEmpresaDos["ruc"]               = $valuesEmpresa["ruc"];
                        $slug                                  = $this->_crearSlug($valuesEmpresa,
                            $lastId);
                        $valuesEmpresa['slug']                 = $slug;
                        $valuesEmpresa['slug_empresa']         = $replaceSlug->cleanSlugEmpresa($valuesEmpresa["nombrecomercial"]);
                        $valuesEmpresa['ultima_actualizacion'] = $date;
                        if ($valuesEmpresa['logotipo'] == NULL) {
                            $valuesEmpresa['logotipo'] = $img;
                            $valuesEmpresa['logo1']    = $img;
                            $valuesEmpresa['logo2']    = $img;
                            $valuesEmpresa['logo3']    = $img;
                        } else {
                            $valuesEmpresa['logotipo'] = $nuevosNombres[0];
                            $valuesEmpresa['logo1']    = $nuevosNombres[1];
                            $valuesEmpresa['logo2']    = $nuevosNombres[2];
                            $valuesEmpresa['logo3']    = $nuevosNombres[3];
                        }
                        unset($valuesEmpresa['id_departamento']);
                        unset($valuesEmpresa['id_distrito']);
                        unset($valuesEmpresa['id_provincia']);

                        $valuesEmpresaDos["id_rubro"]            = $valuesEmpresa["rubro"];
                        $valuesEmpresaDos["id_usuario"]          = $valuesEmpresa["id_usuario"];
                        $valuesEmpresaDos["prefs_emailing_avisos"] = '0';
                        $valuesEmpresaDos["prefs_emailing_info"] = '1';
                        $valuesEmpresaDos["logo"]                = $valuesEmpresa["logotipo"];
                        $valuesEmpresaDos["logo1"]               = $valuesEmpresa["logo1"];
                        $valuesEmpresaDos["logo2"]               = $valuesEmpresa["logo2"];
                        $valuesEmpresaDos["logo3"]               = $valuesEmpresa["logo3"];
                        $valuesEmpresaDos["slug"]                = $valuesEmpresa["slug"];
                        $valuesEmpresaDos["slug_empresa"]        = $valuesEmpresa["slug_empresa"];
                        $valuesEmpresaDos["verificada"]          = $valuesEmpresa["verificada"];
                        $valuesEmpresaDos["id_ubigeo"]           = $valuesEmpresa["id_ubigeo"];
                        $valuesEmpresaDos["razon_comercial"]     = $valuesEmpresa["nombrecomercial"];
                        $lastIdEmpresa                           = $this->_empresa->insert($valuesEmpresaDos);
//var_dump($lastIdEmpresa);exit;
                        //registramos algunos datillos en la tabla categoria_postulacion
                        $extra = $this->_helper->getHelper("RegistrosExtra");
                        $extra->insertarCategoriaPostulacion($lastIdEmpresa);

                        //Usuario Empresa
                        $valuesUsuarioEmpresa["id_usuario"] = $lastId;
                        $valuesUsuarioEmpresa["id_empresa"] = $lastIdEmpresa;
                        $valuesUsuarioEmpresa["nombres"]    = $allParams["nombres"];
                        $valuesUsuarioEmpresa["apellidos"]  = $allParams["apellidos"];
                        $valuesUsuarioEmpresa["puesto"]     = $allParams["puesto"];
                        $valuesUsuarioEmpresa["area"]       = $allParams["area"];
                        $valuesUsuarioEmpresa["telefono"]   = $allParams["telefono"];
                        $valuesUsuarioEmpresa["telefono2"]  = $allParams["telefono2"];
                        $valuesUsuarioEmpresa["anexo"]      = $allParams["anexo"];
                        $valuesUsuarioEmpresa["anexo2"]     = $allParams["anexo2"];
                        $valuesUsuarioEmpresa["extension"]  = "";
                        $valuesUsuarioEmpresa["creador"]    = 1;
                        $this->_usuarioempresa->insert($valuesUsuarioEmpresa);

                        $this->_helper->mail->nuevaEmpresa(
                            array(
                                'to' => $valuesUsuario['email'],
                                'nombre' => $valuesUsuarioEmpresa['nombres'],
                                'empresa' => $valuesEmpresaDos['nombre_comercial']
                            )
                        );
                    } catch (Zend_Db_Exception $e) {
                        echo $e->getMessage();
                    } catch (Zend_Exception $e) {
                        $this->getMessenger()->error($this->_messageSuccess);
                        echo $e->getMessage();
                    }

                    if ($lastIdEmpresa != null) {
                        Application_Model_Usuario::auth(
                            $valuesUsuario['email'], $pswd, $valuesUsuario['rol']
                        );
                        $this->_redirect($this->_url);
                    }



            } else {
                //var_dump($frmEmpresa->getMessages()); die();

                if (!empty($valPostUbigeo)) {
                    $arrayUbigeo = $ubigeo->getHijos(
                        $valPostUbigeo
                    );
                    $frmEmpresa->getElement('id_provincia')->clearMultiOptions();
                    $frmEmpresa->getElement('id_provincia')
                        ->addMultiOption('none', 'Seleccione Distrito');
                    $frmEmpresa->getElement('id_provincia')->addMultioptions($arrayUbigeo);
                }
            }
        }
        $this->view->frmEmpresa       = $frmEmpresa;
        $this->view->frmUsuario       = $frmUsuario;
        $this->view->frmAdministrador = $frmAdministrador;
    }

    public function paso2Action()
    {
        //Campaña doble de riesgo
        Zend_Layout::getMvcInstance()->assign(array(
            'trackingFacebook' => true));
    }

    public function registroRapidoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $id                 = null;
        $formRegistroRapido = new Application_Form_RegistroRapido(null);
        $formRegistroRapido->validadorEmail($id);
        $formRegistroRapido->validadorRazonSocial($id);
        $formRegistroRapido->validadorRuc($id);
        if ($this->getRequest()->isPost()) {
            $allParams           = $this->_getAllParams();
            $validRegistroRapido = $formRegistroRapido->isValid($allParams);
            if ($validRegistroRapido) {
                $_empresa        = new Application_Model_Empresa();
                $_usuario        = new Application_Model_Usuario();
                $_usuarioempresa = new Application_Model_UsuarioEmpresa();
                $date            = date('Y-m-d H:i:s');
                $lastIdEmpresa   = null;
                $pswd            = null;
                $lastId          = null;
                try {
                    $db                            = $this->getAdapter();
                    $db->beginTransaction();
                    //USUARIO
                    $valuesUsuario["email"]        = $allParams["email"];
                    $valuesUsuario['fh_registro']  = $date;
                    $valuesUsuario['ultimo_login'] = $date;
                    $valuesUsuario['activo']       = 1;
                    $valuesUsuario['rol']          = Application_Form_Login::ROL_EMPRESA_ADMIN;
                    $valuesUsuario['salt']         = '';
                    $valuesUsuario['ip']           = $this->getRequest()->getServer('REMOTE_ADDR');
                    $pswd                          = $allParams['pswd'];
                    $valuesUsuario['pswd']         = App_Auth_Adapter_AptitusDbTable::generatePassword(
                            $allParams['pswd']
                    );
                    $lastId                        = $_usuario->insert($valuesUsuario);

                    //EMPRESA
                    $valuesEmpresa["ruc"]              = $allParams["num_ruc"];
                    $valuesEmpresa["razon_social"]     = $allParams["razonsocial"];
                    $valuesEmpresa["razon_comercial"]  = $allParams["razonsocial"];
                    $slug                              = $this->_crearSlug($valuesEmpresa,
                        $lastId);
                    $valuesEmpresa['id_ubigeo']        = Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID;
                    $valuesEmpresa['id_usuario']       = $lastId;
                    $valuesEmpresa['verificada']       = 0;
                    $valuesEmpresa['slug']             = $slug;
                    $valuesEmpresa['logo']             = "";
                    $valuesEmpresa['logo1']            = "";
                    $valuesEmpresa['logo2']            = "";
                    $valuesEmpresa['logo3']            = "";
                    $valuesEmpresa["id_rubro"]         = null;
                    $valuesEmpresa["nombre_comercial"] = $valuesEmpresa["razon_social"];
                    $lastIdEmpresa                     = $_empresa->insert($valuesEmpresa);

                    //USUARIO EMPRESA
                    $valuesUsuarioEmpresa["id_usuario"] = $lastId;
                    $valuesUsuarioEmpresa["id_empresa"] = $lastIdEmpresa;
                    $valuesUsuarioEmpresa["nombres"]    = $allParams["contacto"];
                    $valuesUsuarioEmpresa["apellidos"]  = 'RRHH';
                    $valuesUsuarioEmpresa["puesto"]     = "";
                    $valuesUsuarioEmpresa["area"]       = "";
                    $valuesUsuarioEmpresa["telefono"]   = $allParams["telefono"];
                    $valuesUsuarioEmpresa["telefono2"]  = "";
                    $valuesUsuarioEmpresa["anexo"]      = "";
                    $valuesUsuarioEmpresa["anexo2"]     = "";
                    $valuesUsuarioEmpresa["extension"]  = "";
                    $valuesUsuarioEmpresa["creador"]    = 1;
                    $_usuarioempresa->insert($valuesUsuarioEmpresa);

                    //registramos algunos datillos en la tabla categoria_postulacion
                    $extra = $this->_helper->getHelper("RegistrosExtra");
                    $extra->insertarCategoriaPostulacion($lastIdEmpresa);

                    $db->commit();
                    $this->_helper->mail->nuevaEmpresa(
                        array(
                            'to' => $valuesUsuario['email'],
                            'nombre' => $valuesUsuarioEmpresa['nombres'],
                            'empresa' => $valuesEmpresa['nombre_comercial']
                        )
                    );
                    $response = array(
                        'status' => 'ok',
                        'msg' => 'Los datos fueron válidos'
                    );
                    if ($this->getRequest()->getPost('id_tarifa', '')) {
                        $session         = $this->getSession();
                        $session->tarifa = $this->getRequest()->getPost('id_tarifa',
                            '');
                    }
                } catch (Zend_Db_Exception $e) {
                    $response = array(
                        'status' => 'error',
                        'msg' => 'Datos inválidos'
                    );
                    $db->rollBack();
                } catch (Zend_Exception $e) {
                    $this->getMessenger()->error($this->_messageSuccess);
                    echo $e->getMessage();
                }
                if ($lastIdEmpresa != null || $lastId != null) {
                    Application_Model_Usuario::auth(
                        $valuesUsuario['email'], $pswd, $valuesUsuario['rol']
                    );
                }
            } else {
                $response = array(
                    'status' => 'error',
                    'msg' => 'El formulario no es válido: '
                );
            }
        }
        $this->_response->appendBody(Zend_Json::encode($response));
    }

    private function _crearSlug($valuesPostulante, $lastId)
    {
        $slugFilter = new App_Filter_Slug(
            array(
            'field' => 'slug',
            'model' => $this->_empresa)
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

        $nruc  = $this->_getParam('ndoc');
        $idEmp = $this->_getParam('idEmp');

        $_empresa = new Application_Model_Empresa();
        $isValid  = '';
        if ($idEmp != null) {
            $isValid = $_empresa->validacionNRuc($nruc, null, $idEmp);
        } else {
            $isValid = $_empresa->validacionNRuc($nruc, null, false);
        }

        $msg = 'RUC ya está registrado';
        if ($isValid) {
            $msg = 'Correcto';
        }

        $data = array(
            'status' => $isValid,
            'msg' => $msg
        );

        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function validarRazonsocialAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $requestValido = ($this->_request->isPost() && $this->_hash->isValid($this->_getParam("token")));
        $requestValido = ($requestValido && $this->getRequest()->isXmlHttpRequest() );
        //Validando token
        if ($requestValido) {

            $filter = new Zend_Filter_StripTags;
            $rs     = $filter->filter($this->_getParam('ndoc'));
            $idEmp  = $filter->filter($this->_getParam('idEmp'));

            $_empresa = new Application_Model_Empresa();
            $isValid  = '';

            //Valida que sea alfanumerico y que contenga spacio
            //preg_match_all("/[^0-9-a-zA-Z-[:space:]-ñ-áéíóúAÉÍÓÚÑñ.]/", $rs, $cantError);
            $countDigError = 0;
            /* foreach ($cantError[0] as $error) {
              $countDigError = $countDigError +1;
              } */
            $msg           = 'Razón Social ya registrado.';
            if ($countDigError == 0) {
                $config = Zend_Registry::get("config");
                $val    = $this->_helper->Contador->contadorPalabraText($rs,
                    $config->empresa->numeroPalabra->razonsocial);

                if ($val != false) {
                    if ($idEmp != null) {
                        $isValid = $_empresa->validacionCampoRepetido("razon_social",
                            $rs, null, $idEmp);
                    } else {
                        $isValid = $_empresa->validacionCampoRepetido("razon_social",
                            $rs, null, false);
                    }
                } else {
                    //$isValid = $config->empresa->numeroPalabra->razonsocial;
                    $isValid = false;
                    $msg     = 'Solo se permiten 6 palabras.';
                }
            } else {
                $msg     = 'Razón Social debe ser Alfanumérico.';
                $isValid = false;
            }


            if ($isValid) {
                $msg = 'Correcto';
            }

            $data = array(
                'status' => $isValid,
                'msg' => $msg
            );

            $this->_response->appendBody(Zend_Json::encode($data));
        } else {
            echo $this->_messageError;
        }
    }

    public function validarNombrecomercialAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $rs    = $this->_getParam('ndoc');
        $idEmp = $this->_getParam('idEmp');

        $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());
        $requestValido = ($requestValido && $rs);
        if (!$requestValido) {
            exit(0);
        }

        $config = Zend_Registry::get("config");

        //Valida que sea alfanumerico y que contenga spacio
        //preg_match_all("/[^0-9-a-zA-Z-[:space:]-ñ-áéíóúAÉÍÓÚÑñ.]/", $rs, $cantError);

        $countDigError = 0;
        /* foreach ($cantError[0] as $error) {
          $countDigError = $countDigError +1;
          } */
        $msg           = 'Nombre Comercial ya registrado.';
        if ($countDigError == 0) {
            $val = $this->_helper->Contador->contadorPalabraText($rs,
                $config->empresa->numeroPalabra->razoncomercial);

            $_empresa = new Application_Model_Empresa();

            $isValid = '';
            if ($val != false) {
                if ($idEmp != null) {
                    $isValid = $_empresa->validacionCampoRepetido("nombre_comercial",
                        $rs, null, $idEmp);
                } else {
                    $isValid = $_empresa->validacionCampoRepetido("nombre_comercial",
                        $rs, null, false);
                }
            } else {
                //$isValid = $config->empresa->numeroPalabra->razonsocial;
                $isValid = false;
                $msg     = 'Solo se permiten 6 palabras.';
            }
        } else {
            $isValid = false;
            $msg     = 'Nombre Comercial debe ser Alfanumérico.';
        }

        if ($isValid) {
            $msg = 'Correcto';
        }

        $data = array(
            'status' => $isValid,
            'msg' => $msg
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

            if ($param['rel'] != '') {
                $value = $modeloEmpresa->getEmpresa($param['rel']);
                if ($value['logo'] != null) {
                    // @codingStandardsIgnoreStart
                    unlink(APPLICATION_PATH.'/../public/elements/empleo/logos/'.$value['logo']);
                    unlink(APPLICATION_PATH.'/../public/elements/empleo/logos/'.$value['logo1']);
                    unlink(APPLICATION_PATH.'/../public/elements/empleo/logos/'.$value['logo2']);
                    // @codingStandardsIgnoreEnd
                    $where         = $modeloEmpresa->getAdapter()->quoteInto('id = ?',
                        $param['rel']);
                    $data          = array();
                    $data['logo']  = null;
                    $data['logo1'] = null;
                    $data['logo2'] = null;
                    $data['logo3'] = null;
                    $modeloEmpresa->update($data, $where);
                    $anuncio       = new Application_Model_AnuncioWeb();
                    $anuncio->updateLogoAnuncio($param['rel'], '');

                    $helperAviso = $this->_helper->getHelper('Aviso');

                    //Actualizar avisos - Obtener avisos
                    $modelAviso           = new Application_Model_AnuncioWeb;
                    $dataAvisoXActualizar = $modelAviso->obtenerAvisosActivosEmpresa($param['rel']);

                    foreach ($dataAvisoXActualizar as $infoAviso) {
                        $helperAviso->_SolrAviso->addAvisoSolr($infoAviso['id']);
                        //exec("curl -X POST -d 'api_key=" . $this->_buscamasConsumerKey . "&nid=" . $infoAviso['id'] . "&site=" . $this->_buscamasUrl . "' " . $this->_buscamasPublishUrl);
                    }

                    // Actualizar Cache:
                    $this->_cache->remove($modeloEmpresa->_model.'_getEmpresasTCN');
                    $storage                    = Zend_Auth::getInstance()->getStorage()->read();
                    $storage['empresa']['logo'] = '';
                    Zend_Auth::getInstance()->getStorage()->write($storage);

                    echo Zend_Json::encode(array(
                        'status' => 1,
                        'msg' => 'Se eliminó correctamente'));
                }
            }
        }
    }
}
