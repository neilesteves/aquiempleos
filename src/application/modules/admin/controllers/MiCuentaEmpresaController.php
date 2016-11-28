<?php


class Admin_MiCuentaEmpresaController extends App_Controller_Action_Admin
{

    //Buscamas
    private $_buscamasConsumerKey;
    private $_buscamasPublishUrl;
    private $_buscamasUrl;

    protected $_empresa;
    protected $_usuario;
    protected $_usuarioempresa;
    protected $_urlId;
    protected $_slug;

    public function init()
    {
        parent::init();
//        if (Zend_Auth::getInstance()->hasIdentity() != true) {
//            $this->_redirect('/admin');
//        }

        /* Initialize action controller here */
        $this->_empresa = new Application_Model_Empresa();
        $this->_usuarioempresa = new Application_Model_UsuarioEmpresa();
        $this->_usuario = new Application_Model_Usuario();
        $this->view->rol = $this->auth['usuario']->rol;

        $this->_config = Zend_Registry::get('config');
        $this->_cache = Zend_Registry::get('cache');

        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'myAccount')
        );
    }

    public function indexAction()
    {

    }

    public function datosEmpresaAction()
    {
        $sess = $this->getSession();
        $this->view->empresaAdminUrl =
            $this->view->url($sess->empresaAdminUrl, 'default', false);

        $this->view->menu_sel_side = self::MENU_POST_SIDE_DATOSEMPRESA;

        $this->view->idEmpresa = $idEmpresa = $this->_getParam('rel', null);
        $arrayEmpresa = $this->_empresa->getEmpresa($idEmpresa);

        $usuarioEmpresa = new Application_Model_UsuarioEmpresa();
        $_user = $usuarioEmpresa->obtenerPorUsuario($arrayEmpresa['id_usuario']);

        $arrayEM = $this->_empresa->getEmpresaMembresia($idEmpresa);

        $this->view->modulo = $this->getRequest()->getModuleName();
        $this->view->activo = $arrayEmpresa['activo'];
        $this->view->rol = $this->auth['usuario']->rol;
        $this->view->membresiaTipo = $arrayEM['membresia_info']['membresia']['m_nombre'];
        $this->view->razonsocial = $arrayEmpresa['razonsocial'];
        $this->view->controlador = $this->getRequest()->getControllerName();
        $this->view->emailMicuenta = $arrayEmpresa['email'];

        $config = Zend_Registry::get("config");
        $util = new App_Util();
        $formatSizeLogo = $util->formatSizeUnits($config->app->maxSizeLogo);
        $config->formatSizeLogo = $formatSizeLogo;
        $this->view->config = $config;
        // @codingStandardsIgnoreStart
        $this->view->numPalabraRazonComercial = $config->empresa->numeroPalabra->razoncomercial;
        $this->view->numPalabraRazonSocial = $config->empresa->numeroPalabra->razonsocial;
        //@codingStandardsIgnoreEnd

        $replaceSlug = $this->getHelper('Replace');

        $idUsuario = $arrayEmpresa['id_usuario'];

        $frmEmpresa = new Application_Form_Paso1Empresa($idUsuario);
        $frmEmpresa->validadorRuc($idEmpresa);
        $frmEmpresa->validadorNombreComercial($idEmpresa);
        $frmEmpresa->validadorRazonSocial($idEmpresa);

        $this->view->imgPhoto = $arrayEmpresa['logo2'];
        $img = $arrayEmpresa['logo'];
        $imgUno = $arrayEmpresa['logo1'];
        $imgDos = $arrayEmpresa['logo2'];

        $arrayEmpresa['pais_residencia'] = $arrayEmpresa['idpaisres'];
        $arrayEmpresa['id_departamento'] = $arrayEmpresa['iddpto'];
        $arrayEmpresa['id_provincia'] = $arrayEmpresa['idprov'];
       // $arrayEmpresa['id_distrito'] = $arrayEmpresa['iddistrito'];

        $ubigeo = new Application_Model_Ubigeo();
//        if (isset($arrayEmpresa['id_provincia']) &&
//            trim($arrayEmpresa['id_provincia']) == Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID) {
//            $arrayUbigeo = $ubigeo->getHijos(Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID);
//            $frmEmpresa->getElement('id_distrito')->addMultioptions($arrayUbigeo);
//        }
//        if (isset($arrayEmpresa['id_provincia']) &&
//            trim($arrayEmpresa['id_provincia']) == Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID) {
//            $arrayUbigeo = $ubigeo->getHijos(Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID);
//            $frmEmpresa->getElement('id_distrito')->addMultioptions($arrayUbigeo);
//        }

        $frmEmpresa->setDefaults($arrayEmpresa);
        $valPostUbigeo = '';

        if ($this->_request->isPost()) {
            $allParams = $this->_getAllParams();

            $validEmpresa = $frmEmpresa->isValid($allParams);
            if (isset($allParams['id_provincia'])) {
                $valPostUbigeo = $allParams['id_provincia'];
            }

//            if (crypt(date('dmYH'),$allParams['tok_emp_paso1']) !== $allParams['tok_emp_paso1']) {
//                $validEmpresa = false;
//                $this->getMessenger()->error('Los datos ingresados no son los correctos, por favor vuelva ha intentarlo.');
//            }
          // var_dump($validEmpresa,$frmEmpresa->getMessages());exit;
            if ($validEmpresa) {

                $utilfile = $this->_helper->getHelper('UtilFiles');
                $helperAviso = $this->_helper->getHelper('Aviso');

                $nuevosNombres = $utilfile->_renameFile($frmEmpresa, 'logotipo',
                    "image-empresa");
                $valuesEmpresa = $frmEmpresa->getValues();
                $date = date('Y-m-d H:i:s');


                try {
                    $db = $this->getAdapter();
                    $db->beginTransaction();

                    //Captura de los valores de Empresa
                    $valuesEmpresa['id_ubigeo'] = $this->_helper->Util->getUbigeo($valuesEmpresa);
                    $valuesEmpresa['ultima_actualizacion'] = $date;

                    if ($valuesEmpresa['logotipo'] == NULL) {
                        $valuesEmpresa['logotipo'] = $img;
                        $valuesEmpresa['logo1'] = $img;
                        $valuesEmpresa['logo2'] = $img;
                    } else {
                        $valuesEmpresa['logotipo'] = $nuevosNombres[0];
                        $valuesEmpresa['logo1'] = $nuevosNombres[1];
                        $valuesEmpresa['logo2'] = $nuevosNombres[2];
                        if (@$img != 'photoDefault.jpg') {
                            @unlink($this->config->urls->app->elementsLogosRoot . $img);
                            @unlink($this->config->urls->app->elementsLogosRoot . $imgUno);
                            @unlink($this->config->urls->app->elementsLogosRoot . $imgDos);
                        }
                    }

                    unset($valuesEmpresa['id_departamento']);
                    unset($valuesEmpresa['id_distrito']);
                    unset($valuesEmpresa['id_provincia']);
                    unset($valuesEmpresa['tok_emp_paso1']);
                    $valuesEmpresaDos["id_rubro"] = $valuesEmpresa["rubro"];
                    $valuesEmpresaDos["id_usuario"] = $idUsuario;
                    $valuesEmpresaDos["razon_social"] = $valuesEmpresa["razonsocial"];
                    $valuesEmpresaDos["nombre_comercial"] = $valuesEmpresa["nombrecomercial"];
                    $valuesEmpresaDos["tipo_doc"] = $valuesEmpresa["tipo_doc"];
                    $valuesEmpresaDos['slug_empresa'] = $replaceSlug->cleanSlugEmpresa($valuesEmpresa["nombrecomercial"]);
                    $valuesEmpresaDos["ruc"] = $valuesEmpresa["num_ruc"];
                    $valuesEmpresaDos["logo"] = $valuesEmpresa["logotipo"];
                    $valuesEmpresaDos["logo1"] = $valuesEmpresa["logo1"];
                    $valuesEmpresaDos["logo2"] = $valuesEmpresa["logo2"];
                    $valuesEmpresaDos["id_ubigeo"] = $valuesEmpresa["id_ubigeo"];

                    $where = $this->_empresa->getAdapter()
                        ->quoteInto('id = ?', $idEmpresa);

                    $this->_empresa->update($valuesEmpresaDos, $where);
                    $anuncio = new Application_Model_AnuncioWeb();
                    $anuncio->updateLogoAnuncio($idEmpresa,
                        $valuesEmpresaDos["logo2"]);

                    $db->commit();

                    //Actualizar avisos - Obtener avisos
                    //SELECT id FROM anuncio_web WHERE estado = 'pagado' AND online = 1 AND cerrado = 0 AND id_empresa = codigo
                    $modelAviso = new Application_Model_AnuncioWeb;
                    $dataAvisoXActualizar = $modelAviso->obtenerAvisosActivosEmpresa($idEmpresa);
                    $AvisoSolr= new Solr_SolrAviso();
                    foreach ($dataAvisoXActualizar as $infoAviso) {
                        $AvisoSolr->addAvisoSolr($infoAviso['id']);
                        //exec("curl -X POST -d 'api_key=".$this->_buscamasConsumerKey."&nid=".$infoAviso['id']."&site=".$this->_buscamasUrl."' ".$this->_buscamasPublishUrl);
                        @$this->_cache->remove('AnuncioWeb_getAvisoInfoficha_' . $infoAviso['id']);
                        @$this->_cache->remove('AnuncioWeb_getAvisoById_'.$infoAviso['id']);
//                        @$this->_cache->remove('AnuncioWeb_getAvisoRelacionadosnew_' . $infoAviso['id']);
//                        @$this->_cache->remove('AnuncioWeb_getAvisosRelacionadosAuxiliar_' . $infoAviso['id']);
                        @$this->_cache->remove('AnuncioWeb_getAvisoIdByUrl_'.$infoAviso['url_id']);
                        @$this->_cache->remove('AnuncioWeb_getAvisoIdByCreado_'.$infoAviso['url_id']);
                    }

                    $this->getMessenger()->success('Se cambiaron los datos con éxito.');
                    $this->_redirect(
                        Zend_Controller_Front::getInstance()
                            ->getRequest()->getRequestUri()
                    );
                } catch (Zend_Db_Exception $e) {
                    $db->rollBack();
                    //echo $e->getMessage();exit;
                } catch (Zend_Exception $e) {
                    $this->getMessenger()->error($this->_messageSuccess);
                    //echo $e->getMessage();exit;
                }
            } else {

//                if ($valPostUbigeo == Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID) {
//                    $arrayUbigeo = $ubigeo->getHijos(
//                        Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID
//                    );
//                    $frmEmpresa->getElement('id_distrito')->clearMultiOptions();
//                    $frmEmpresa->getElement('id_distrito')
//                        ->addMultiOption('none', 'Seleccione Distrito');
//                    $frmEmpresa->getElement('id_distrito')->addMultioptions($arrayUbigeo);
//                }
//                if ($valPostUbigeo == Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID) {
//                    $arrayUbigeo = $ubigeo->getHijos(
//                        Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID
//                    );
//                    $frmEmpresa->getElement('id_distrito')->clearMultiOptions();
//                    $frmEmpresa->getElement('id_distrito')
//                        ->addMultiOption('none', 'Seleccione Distrito');
//                    $frmEmpresa->getElement('id_distrito')->addMultioptions($arrayUbigeo);
//                }
            }
        }
        $this->view->frmEmpresa = $frmEmpresa;
    }

    public function cambioClaveAction()
    {
        $this->view->menu_sel_side = self::MENU_POST_SIDE_CAMBIOCLAVE;
        $sess = $this->getSession();
        $this->view->empresaAdminUrl =
            $this->view->url($sess->empresaAdminUrl, 'default', false);

        $this->view->idEmpresa = $idEmpresa = $this->_getParam('rel', null);
        $this->view->rol = $this->auth['usuario']->rol;
        $modelEmpresa = new Application_Model_Empresa();
        $arrayEmpresa = $modelEmpresa->getEmpresa($idEmpresa);
        $arrayEM = $this->_empresa->getEmpresaMembresia($idEmpresa);
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/micuenta.admin.js')
        );

        $this->view->membresiaTipo = $arrayEM['membresia_info']['membresia']['m_nombre'];
        $this->view->activo = $arrayEmpresa['activo'];
        $this->view->razonsocial = $arrayEmpresa['razonsocial'];
        $idUsuario = $arrayEmpresa['id_usuario'];
        $emailUsuario = $arrayEmpresa['email'];

        $formCambioClave = new Application_Form_CambioClave($idUsuario);
        $formCambioClave->validarPswd($emailUsuario,$idUsuario);

        if ($this->_request->isPost()) {

            $allParams = $this->_getAllParams();

            $validClave = $formCambioClave->isValid($allParams);

            if ($validClave) {
                $valuesClave = $formCambioClave->getValues();

                if ( crypt(date('dmYH'), $valuesClave['tok']) === $valuesClave['tok'] ) {

                    try {

                        $db = $this->getAdapter();
                        $db->beginTransaction();

                        //Captura de los datos de usuario
                        $valuesClave['pswd'] =
                            App_Auth_Adapter_AptitusDbTable::generatePassword($valuesClave['pswd']);
                        unset($valuesClave['pswd2']);
                        unset($valuesClave['oldpswd']);
                        unset($valuesClave['auth_token']);
                        unset($valuesClave['tok']);

                        $where = $this->_usuario->getAdapter()
                            ->quoteInto('id = ?', $idUsuario);
                        $this->_usuario->update($valuesClave, $where);
                        $db->commit();

                        $this->getMessenger()->success('Se cambio la clave con éxito.');

                        $this->_redirect(
                            $this->getRequest()->getRequestUri()
                        );
                    } catch (Zend_Db_Exception $e) {
                        $db->rollBack();
                        echo $e->getMessage();
                    } catch (Zend_Exception $e) {
                        $this->getMessenger()->error($this->_messageSuccess);
                        echo $e->getMessage();
                    }

                }

            } else {
                $this->getMessenger()->error("La contraseña proporcionada no coincide con la actual");
            }
        }
        $this->view->formCambioClave = $formCambioClave;
    }

    public function eliminarFotoAction()
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
                    unlink(APPLICATION_PATH . '/../public/elements/empleo/logos/' . $value['logo']);
                    unlink(APPLICATION_PATH . '/../public/elements/empleo/logos/' . $value['logo1']);
                    unlink(APPLICATION_PATH . '/../public/elements/empleo/logos/' . $value['logo2']);
                    // @codingStandardsIgnoreEnd
                    $where = $modeloEmpresa->getAdapter()->quoteInto('id = ?',
                        $param['rel']);
                    $data = array();
                    $data['logo'] = null;
                    $data['logo1'] = null;
                    $data['logo2'] = null;
                    $modeloEmpresa->update($data, $where);
                    $anuncio = new Application_Model_AnuncioWeb();
                    $anuncio->updateLogoAnuncio($param['rel'], '');
                    //Actualizar avisos - Obtener avisos
                    $modelAviso = new Application_Model_AnuncioWeb;
                    $dataAvisoXActualizar = $modelAviso->obtenerAvisosActivosEmpresa($param['rel']);

                    $helperAviso = $this->_helper->getHelper('Aviso');

                    foreach ($dataAvisoXActualizar as $infoAviso) {
                        $helperAviso->_SolrAviso->addAvisoSolr($infoAviso['id']);
                        //exec("curl -X POST -d 'api_key=" . $this->_buscamasConsumerKey . "&nid=" . $infoAviso['id'] . "&site=" . $this->_buscamasUrl . "' " . $this->_buscamasPublishUrl);
                    }

                    // Actualizar Cache:
                    $this->_cache->remove($modeloEmpresa->_model.'_getEmpresasTCN');


                    $storage = Zend_Auth::getInstance()->getStorage()->read();
                    $storage['empresa']['logo'] = '';
                    Zend_Auth::getInstance()->getStorage()->write($storage);


                    echo Zend_Json::encode(array('status' => 1,'msg' => 'Se eliminó correctamente'));
                }
            }
        }
    }

    public function busquedaGeneralEmpAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        if (!$this->_request->isPost()) {
            //throw new Zend_Exception("Request debe ser POST");
        }

        $res = $this->_helper->autocomplete($this->_getAllParams());
        $this->_response->appendBody($res);
    }

}
