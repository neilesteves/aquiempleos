<?php

class Empresa_MiCuentaController extends App_Controller_Action_Empresa
{
    private $_buscamasConsumerKey;
    private $_buscamasPublishUrl;
    private $_buscamasUrl;
    protected $_empresa;
    protected $_usuario;
    protected $_usuarioempresa;
    protected $_urlId;
    protected $_slug;
    protected $_tieneBuscador;
    protected $_candidatosSugeridos;
    protected $_cache = null;

    public function init()
    {
        parent::init();

        $this->_usuario = new Application_Model_Usuario();

        if (Zend_Auth::getInstance()->hasIdentity() != true) {
            $this->_redirect('/empresa');
        }
        if ($this->_usuario->hasvailBlokeo($this->auth['usuario']->id)) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::forgetMe();
            $this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
            $this->_redirect('/empresa');
        }

        $this->_cache  = Zend_Registry::get('cache');
        $this->_config = Zend_Registry::get('config');

        /* Initialize action controller here */
        $this->_usuarioempresa = new Application_Model_UsuarioEmpresa();
        $this->_usuario        = new Application_Model_Usuario();

        Zend_Layout::getMvcInstance()->assign('bodyAttr',
            array('id' => 'myAccount'));
        $this->_urlId               = $this->_getParam('url_id');
        $this->_slug                = $this->_getParam('slug');
        $this->_empresa             = new Application_Model_Empresa();
        $this->_anuncioweb          = new Application_Model_AnuncioWeb();
        $config                     = Zend_Registry::get('config');
        $this->_candidatosSugeridos = $config->profileMatch->empresa->sugerencias;
        $this->_tieneBolsaCVs       = false;

        if (isset($this->auth['empresa']))
                $this->idEmpresa = $this->auth['empresa']['id'];
        $this->usuario   = $this->auth['usuario'];

        if (isset($this->auth["empresa"])) {

            $this->view->verSugerenciaCandidatos = $this->_candidatosSugeridos;
            $this->view->tieneBuscador           = $this->_anuncioweb->beneficioBuscadorAptitus($this->auth);
            $this->view->tieneBuscadorAptitus    = $this->view->tieneBuscador;
            $this->_tieneBolsaCVs                = 0;
            if (isset($this->auth['empresa']['membresia_info']['membresia']['id_membresia'])) {
                $this->_tieneBolsaCVs = !empty($this->auth['empresa']['membresia_info']['beneficios']->bolsacv)
                        ? 1 : 0;
            }
            $this->view->tieneBolsaCVs = $this->_tieneBolsaCVs;
        }
        $this->view->Look_Feel = array(); // $this->_empresa->LooFeelActivo($this->auth['empresa']['id'])    ;
    }

    public function indexAction()
    {

        $catPostulacion = new Application_Model_CategoriaPostulacion;
        $idEmpresa      = $this->auth['empresa']['id'];

        $this->view->verSugerenciaCandidatos = $this->_candidatosSugeridos;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_INICIO;
        $this->view->isAuth        = $this->isAuth;
        $tieneBolsaCVs             = false;
        if ($this->isAuth) {

            $porcentaje           = 0;
            $id                   = $this->idEmpresa;
            // $arrayEmpresa = $this->_empresa->getIdEmpresa($id);
            $this->view->imgPhoto = ''; //$arrayEmpresa["logo"];
            $this->view->nombres  = $this->auth['empresa']["razon_social"];

            $empresa                   = new Application_Model_Empresa();
            $this->view->postulaciones = $empresa->misProcesos($this->idEmpresa);

            $beneficios = $this->auth['empresa']['membresia_info']['beneficios'];
            $codigo     = Application_Model_Beneficio::CODE_REASIGNAR_PROCESOS;
            $usuario    = $this->auth['usuario-empresa'];

            $usuarioLogeado = new App_Service_Validate_UserCompany;
            if (isset($beneficios->$codigo) &&
                !$usuarioLogeado->isCreator($usuario)) {
                $this->view->postulaciones = $empresa->misProcesosAdmSecundarios(
                    $usuario['id']);
            }
            $codeBuscador = Application_Model_Beneficio::CODE_BUSCADOR;

            $this->_anuncioweb = new Application_Model_AnuncioWeb();


            $this->view->nbuscador   = $beneficios;
            $this->view->nprocesos   = $this->config->portadaEmpresa->nprocesos;
            $this->view->ncaracteres = $this->config->portadaEmpresa->ncaracteres;
        }
        if ($tieneBolsaCVs && $this->view->tieneBuscador) {
            $this->view->tieneBeneficios = true;
        } else {
            $this->view->tieneBeneficios = false;
        }


        $pass              = new Zend_Session_Namespace('pass');
        $pass->token       = $this->view->token = md5(rand());
    }

    private function _validarAccesoBaseCvs($fechaPublicacion)
    {

        $fecVenProceso = new DateTime($fechaPublicacion);
        //$diasProceso = 30 variable para configurar en el ini
        $fecVenProceso->add(new DateInterval('P'.'4'.'D'));
        $fecVen        = $fecVenProceso->format('Y-m-d');
        $fecHoy        = date("Y-m-d");

        if ($fecHoy <= $fecVen) {
            $result = 1;
        } else {
            $result = 0;
        }

        return $result;
    }

    public function datosEmpresaAction()
    {
        $usuarioEmpresaDatos = $this->auth['usuario-empresa'];
        $usuarioEmpresa      = new App_Service_Validate_UserCompany;
        ///if($this->auth['empresa']['membresia_info']['beneficios']->busqueda);
        if (!$usuarioEmpresa->isCreator($usuarioEmpresaDatos)) {
            $this->_redirect('/empresa');
        }
        $this->view->menu_sel_side = self::MENU_POST_SIDE_DATOSEMPRESA;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;

        $config                               = Zend_Registry::get("config");
        $util                                 = new App_Util();
        $formatSizeLogo                       = $util->formatSizeUnits($config->app->maxSizeLogo);
        $config->formatSizeLogo               = $formatSizeLogo;
        $this->view->config                   = $config;
        // @codingStandardsIgnoreStart
        $this->view->numPalabraRazonComercial = $config->empresa->numeroPalabra->razoncomercial;
        $this->view->numPalabraRazonSocial    = $config->empresa->numeroPalabra->razonsocial;
        //@codingStandardsIgnoreEnd

        $replaceSlug = $this->getHelper('Replace');

        $this->view->modulo        = $this->getRequest()->getModuleName();
        $this->view->controlador   = $this->getRequest()->getControllerName();
        $this->view->emailMicuenta = $this->usuario->email;
        $idEmpresa      = $this->auth['empresa']['id'];
        $this->idEmpresa=$idEmpresa;
        $this->view->idEmpresa = $id                    = $this->idEmpresa;
        $arrayEmpresa          = $this->_empresa->getEmpresa($id);

        // $arrayEmpresa=  array_merge($arrayEmpresa, $this->auth['empresa']);
        $idUsuario             = $this->auth['empresa']['id_usuario'];
        $frmEmpresa            = new Application_Form_Paso1Empresa($idUsuario);
        $frmEmpresa->validadorRuc($id);
        $frmEmpresa->validadorNombreComercial($id);
        $frmEmpresa->validadorRazonSocial($id);

        $formUsuario = new Application_Form_Paso1Usuario($idUsuario);
        $formUsuario->validadorEmail($idUsuario, 'empresa');
        $this->view->imgPhoto = $this->auth['empresa']['logo2'];
        $img                  = $this->auth['empresa']['logo'];
        $imgUno               = $this->auth['empresa']['logo1'];
        $imgDos               = $this->auth['empresa']['logo2'];
        $imgTres              = $this->auth['empresa']['logo3'];

        $arrayEmpresa['pais_residencia'] = $arrayEmpresa['idpaisres'];
        $arrayEmpresa['id_departamento'] = $arrayEmpresa['iddpto'];
        $arrayEmpresa['id_provincia']    = $arrayEmpresa['idprov'];

        $ubigeo = new Application_Model_Ubigeo();


        $arrayUbigeo = $ubigeo->getHijos($arrayEmpresa['id_departamento']);
        $frmEmpresa->getElement('id_provincia')->addMultioptions($arrayUbigeo);


        $frmEmpresa->setDefaults($arrayEmpresa);
        $formUsuario->setDefault('email', $arrayEmpresa['email']);

        $valPostUbigeo = '';
        if ($this->_request->isPost()) {


            $allParams    = $this->_getAllParams();
            $validEmpresa = $frmEmpresa->isValid($allParams);
            $formUsuario->removeElement('pswd');
            $formUsuario->removeElement('pswd2');
            $validUsuario = $formUsuario->isValid($allParams);
            //$condicion = (isset($validUsuario) && $validUsuario);

            if (isset($allParams['auth_token'])) {
                unset($allParams['auth_token']);
            }

            if (isset($allParams['id_provincia'])) {
                $valPostUbigeo = $allParams['id_provincia'];
            }

            if ($validEmpresa && $validUsuario) {

                $utilfile    = $this->_helper->getHelper('UtilFiles');
                $helperAviso = $this->_helper->getHelper('Aviso');

                $nuevosNombres              = $utilfile->_renameFile($frmEmpresa,
                    'logotipo', "image-empresa");
                $valuesEmpresa              = $frmEmpresa->getValues();
                $util                       = $this->_helper->getHelper('Util');
                $valuesEmpresa["id_ubigeo"] = $util->getUbigeo($allParams);
                $date                       = date('Y-m-d H:i:s');
                // var_dump($valuesEmpresa["id_ubigeo"]);exit;
                //var_dump($nuevosNombres);exit;
                /// $db = $this->getAdapter();
                try {

                  //  $db->beginTransaction();

                    //
                    $valuesUsuario = $formUsuario->getValues();
                    $modelUsuario  = new Application_Model_Usuario();
                    $where         = $modelUsuario->getAdapter()->quoteInto('id = ?',
                        $idUsuario);

                    if (isset($valuesUsuario['auth_token'])) {
                        unset($valuesUsuario['auth_token']);
                    }

                    $valuesUsuario['email']                      = trim($valuesUsuario['email']);
                    $modelUsuario->update($valuesUsuario, $where);
                    //
                    //Captura de los valores de Empresa
                    $valuesEmpresa['id_ubigetAvisoInfofichageo'] = $this->_helper->Util->getUbigeo($valuesEmpresa);
                    $valuesEmpresa['ultima_actualizacion']       = $date;

                    //Verificar si hay imagen o no para actualizar los avisos
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
                        if (@$img != 'photoDefault.jpg') {
                            unlink($this->config->urls->app->elementsLogosRoot.$img);
                            unlink($this->config->urls->app->elementsLogosRoot.$imgUno);
                            unlink($this->config->urls->app->elementsLogosRoot.$imgDos);
                            unlink($this->config->urls->app->elementsLogosRoot.$imgTres);
                        }
                    }

                    unset($valuesEmpresa['id_departamento']);
                    unset($valuesEmpresa['id_distrito']);
                    unset($valuesEmpresa['id_provincia']);
                    $valuesEmpresaDos["id_rubro"]         = $valuesEmpresa["rubro"];
                    $valuesEmpresaDos["id_usuario"]       = $idUsuario;
                    $valuesEmpresaDos["razon_social"]     = $valuesEmpresa["razonsocial"];
                    $valuesEmpresaDos["nombre_comercial"] = $valuesEmpresa["nombrecomercial"];
                    $valuesEmpresaDos["tipo_doc"]         = (!empty($valuesEmpresa["tipo_doc"]))?$valuesEmpresa["tipo_doc"]:$this->auth['empresa']['tipo_doc'];
                    $valuesEmpresaDos['slug_empresa']     = $replaceSlug->cleanSlugEmpresa($valuesEmpresa["nombrecomercial"]);
                    $valuesEmpresaDos["ruc"]              = $valuesEmpresa["num_ruc"];
                    $valuesEmpresaDos["logo"]             = $valuesEmpresa["logotipo"];
                    $valuesEmpresaDos["logo1"]            = $valuesEmpresa["logo1"];
                    $valuesEmpresaDos["logo2"]            = $valuesEmpresa["logo2"];
                    $valuesEmpresaDos["logo3"]            = $valuesEmpresa["logo3"];

                    $valuesEmpresaDos["id_ubigeo"] = $valuesEmpresa["id_ubigeo"];

                    $where   = $this->_empresa->getAdapter()
                        ->quoteInto('id = ?', $id);

                    $this->_empresa->update($valuesEmpresaDos, $where);
                    $anuncio = new Application_Model_AnuncioWeb();

                    $anuncio->updateLogoAnuncio($id, $valuesEmpresaDos["logo2"]);
                    //$db->commit();

                    //Actualizar avisos - Obtener avisos
                    $modelAviso           = new Application_Model_AnuncioWeb;
                    $dataAvisoXActualizar = $modelAviso->obtenerAvisosActivosEmpresa($id);
                    $this->_SolrAviso     = new Solr_SolrAviso();
                   // var_dump($dataAvisoXActualizar);exit;
                    foreach ($dataAvisoXActualizar as $infoAviso) {
                     //   $this->_SolrAviso->addAvisoSolr($infoAviso['id']);
                        @$this->_cache->remove('AnuncioWeb_getAvisoInfoficha_'.$infoAviso['id']);
                      //  @$this->_cache->remove('AnuncioWeb_getAvisoById_'.$infoAviso['id']);
                       // @$this->_cache->remove('AnuncioWeb_getAvisoIdByUrl_'.$infoAviso['url_id']);
                      //  @$this->_cache->remove('AnuncioWeb_getAvisoIdByCreado_'.$infoAviso['url_id']);
                    }
                    @$this->_cache->remove('Empresa_getEmpresaHome_');
                    //@$this->_cache->remove('Empresa_getCompanyWithMembresia');
                    $storage                                = Zend_Auth::getInstance()->getStorage()->read();
                    $storage['empresa']['razon_social']     = $valuesEmpresaDos['razon_social'];
                    $storage['empresa']['nombre_comercial'] = $valuesEmpresaDos['nombre_comercial'];
                    $storage['empresa']['logo']             = $valuesEmpresaDos['logo1'];
                    $storage['empresa']['logo2']            = $valuesEmpresaDos['logo2'];
                    $storage['empresa']["id_ubigeo"]        = $valuesEmpresaDos["id_ubigeo"];
                    Zend_Auth::getInstance()->getStorage()->write($storage);


                    $this->getMessenger()->success('Los datos se actualizaron con éxito.');

                    $this->_redirect(
                        Zend_Controller_Front::getInstance()->getRequest()->getRequestUri()
                    );

                } catch (Zend_Db_Exception $e) {
                  //  $db->rollBack();
                    echo $e->getMessage();
                } catch (Zend_Exception $e) {
                    $this->getMessenger()->error($this->_messageSuccess);
                    echo $e->getMessage();
                }
            } else {

            }
        }

        $this->view->formUsuario = $formUsuario;
        $this->view->frmEmpresa  = $frmEmpresa;
    }

    public function misAvisosAction()
    {
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.cuenta.mensajes.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.misprocesos.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/avisos.admin.js')
        );

        $beneficios = $this->auth['empresa']['membresia_info']['beneficios'];
        $codigo     = Application_Model_Beneficio::CODE_REASIGNAR_PROCESOS;

        $asignarProcesos = false;
        if (isset($beneficios->$codigo)) {
            $asignarProcesos = true;
        }

        $this->view->sonAvisosInactivos = $valActivo                      = $this->_getParam('inactivos',
            false);

        $this->view->menu_sel_side   = self::MENU_POST_SIDE_MIS_AVISOS;
        $this->view->menu_post_sel   = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel        = self::MENU_MI_CUENTA;
        $this->view->isAuth          = $this->isAuth;
        $this->view->asignarProcesos = $asignarProcesos;

        $this->view->razon_social = $this->auth['empresa']['razon_social'];
        $this->view->Valaction    = $this->_request->getActionName();

        $anuncioWeb = new Application_Model_AnuncioWeb;

        $this->view->col = $col             = $this->_getParam('col', '');
        $this->view->ord = $ord             = $this->_getParam('ord', 'DESC');

        $page = $this->_getParam('page', 1);

        $usuarioEmpresaDatos = $this->auth['usuario-empresa'];

        if (!$valActivo) {
            $select    = $anuncioWeb->getAvisosActivos(
                $this->idEmpresa, $activo    = 1,
                Application_Model_AnuncioWeb::ESTADO_PAGADO, $eliminado = 0,
                $col, $ord
            );
        } else {
            $select    = $anuncioWeb->getAvisosActivos(
                $this->idEmpresa, $activo    = 0,
                Application_Model_AnuncioWeb::ESTADO_DADO_BAJA, $eliminado = 0,
                $col, $ord
            );
        }

        $paginado = $this->config->empresa->misavisos->paginadoavisos;

        $creador        = true;
        $usuarioEmpresa = new App_Service_Validate_UserCompany;
        $usuarioEmpresa->setData($usuarioEmpresaDatos);

        $beneficios = $this->auth['empresa']['membresia_info']['beneficios'];
        $codigo     = Application_Model_Beneficio::CODE_REASIGNAR_PROCESOS;

        if (isset($beneficios->$codigo)) {
            if (!$usuarioEmpresa->isCreator()) {
                $select  = $this->_anuncioweb->getAvisosActivosPorAdministrador(
                    $select, $usuarioEmpresaDatos['id']);
                $creador = false;
            };
        }


        $paginator = Zend_Paginator::factory($select);
        $paginator->setItemCountPerPage($paginado);

        $this->view->mostrando = "Mostrando "
            .$paginator->getItemCount($paginator->getItemsByPage($page))." de "
            .$paginator->getTotalItemCount();
        $paginator->setCurrentPageNumber($page);

        $this->view->arrayAviso = $paginator;

        $this->view->creador = $creador;
        $this->view->moneda  = $this->_config->app->moneda;

        $this->view->redirect = $this->_helper->Aviso->EncodeRedirect('empresa/mi-cuenta/mis-avisos');
    }

    public function cambioClaveAction()
    {
        $this->view->menu_sel_side = self::MENU_POST_SIDE_CAMBIOCLAVE;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;

        $idUsuario    = $this->usuario->id;
        $emailUsuario = $this->usuario->email;

        $formCambioClave = new Application_Form_CambioClave($idUsuario);
        $formCambioClave->validarPswd($emailUsuario, $idUsuario);

        if ($this->_request->isPost()) {

            $allParams  = $this->_getAllParams();
            $validClave = ($formCambioClave->isValid($allParams) && $this->_hash->isValid($allParams['csrfhash']) );
            if ($validClave) {
                $valuesClave = $formCambioClave->getValues();
                try {

                    $db = $this->getAdapter();
                    $db->beginTransaction();

                    //Captura de los datos de usuario
                    $valuesClave['pswd'] = App_Auth_Adapter_AptitusDbTable::generatePassword($valuesClave['pswd']);
                    unset($valuesClave['pswd2']);
                    unset($valuesClave['tok']);
                    unset($valuesClave['oldpswd']);
                    unset($valuesClave['auth_token']);
                    unset($valuesClave['csrfhash']);

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
            } else {
                $this->getMessenger()->error("La contraseña proporcionada no coincide con la actual");
            }
        }
        $this->view->formCambioClave = $formCambioClave;
    }

    //Mantenimiento Avisos
    public function bajaAvisoAction()
    {
        $config = Zend_Registry::get('config');

        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
        $buscamasUrl         = $config->apis->buscamas->url;
        $buscamasPublishUrl  = $config->apis->buscamas->publishUrl;

        $anuncioW = new Application_Model_AnuncioWeb();

        $helperAviso = $this->_helper->getHelper('Aviso');

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idAviso    = $this->_getParam('id', null);
        $this->_cache->remove('AnuncioWeb_getAvisoInfoById_'.$idAviso);
        $this->_cache->remove('AnuncioWeb_getAvisoById_'.$idAviso);
        $arrayAviso = $anuncioW->getAvisoById($idAviso);

        $anuncio        = new App_Service_Validate_Ad($idAviso);
        $usuarioEmpresa = $this->auth['usuario-empresa'];

        if (!$anuncio->isManaged($usuarioEmpresa)) {
            $this->getMessenger()->error($anuncio->getMessage());
            $this->_redirect('/empresa/mi-cuenta');
        }

        try {
            $db = $this->getAdapter();
            $db->beginTransaction();

            $value['fh_aviso_baja']  = date('Y-m-d H:i:s');
            $value['online']         = '0';
            $value['estado']         = Application_Model_AnuncioWeb::ESTADO_DADO_BAJA;
            $value['modificado_por'] = $this->auth['usuario-empresa']['id_usuario'];

            $where = $anuncioW->getAdapter()
                ->quoteInto('id = ?', $idAviso);
            $anuncioW->update($value, $where);

            $db->commit();
            $modelSorlAviso= new Solr_SolrAviso();
            $modelSorlAviso->delete($idAviso);
            //exec("curl -X POST -d 'api_key=" . $buscamasConsumerKey . "&nid=" . $idAviso . "&site=" . $buscamasUrl . "' " . $buscamasPublishUrl);

            @$this->_cache->remove('AnuncioWeb_getAvisoInfoById_'.$idAviso);
            @$this->_cache->remove('AnuncioWeb_getFullAvisoById_'.$idAviso);
            @$this->_cache->remove('AnuncioWeb_getAvisoById_'.$idAviso);
            @$this->_cache->remove('AnuncioWeb_getAvisoInfoficha_'.$arrayAviso['url_id']);
            @$this->_cache->remove('anuncio_web_'.$arrayAviso['url_id']);
//            @$this->_cache->remove('AnuncioWeb_getAvisoRelacionadosnew_' . $idAviso);
//            @$this->_cache->remove('AnuncioWeb_getAvisosRelacionadosAuxiliar_' . $idAviso);
            @$this->_cache->remove('AnuncioWeb_getAvisoIdByUrl_'.$arrayAviso['url_id']);
            @$this->_cache->remove('AnuncioWeb_getAvisoIdByCreado_'.$arrayAviso['url_id']);

            $this->getMessenger()->success('Aviso Actualizado.');
            $this->_redirect($_SERVER['HTTP_REFERER']);
        } catch (Zend_Db_Exception $e) {
            $db->rollBack();
            $this->getMessenger()->error('Error al Actulizar.');
            echo $e->getMessage();
        } catch (Zend_Exception $e) {
            $this->getMessenger()->error($this->_messageSuccess);
            echo $e->getMessage();
        }
    }

    public function eliminarAvisoAction()
    {
        $config = Zend_Registry::get('config');

        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
        $buscamasUrl         = $config->apis->buscamas->url;
        $buscamasPublishUrl  = $config->apis->buscamas->publishUrl;

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idAviso = $this->_getParam('id', null);

        $anuncio     = new App_Service_Validate_Ad($idAviso);
        $helperAviso = $this->_helper->getHelper('Aviso');

        $usuarioEmpresa = $this->auth['usuario-empresa'];

        if (!$anuncio->isManaged($usuarioEmpresa)) {
            $this->getMessenger()->error($anuncio->getMessage());
            $this->_redirect('/empresa/mi-cuenta');
        }

        try {
            $db = $this->getAdapter();
            $db->beginTransaction();

            $value['eliminado']          = '1';
            $value['online']             = '0';
            $value['proceso_activo']     = '0';
            $value['fh_aviso_eliminado'] = date('Y-m-d H:i:s');

            $anuncioW = new Application_Model_AnuncioWeb();
            $where    = $anuncioW->getAdapter()
                ->quoteInto('id = ?', $idAviso);
            $anuncioW->update($value, $where);

            $db->commit();

            $helperAviso->_SolrAviso->DeleteAvisoSolr($idAviso);
            //exec("curl -X POST -d 'api_key=" . $buscamasConsumerKey . "&nid=" . $idAviso . "&site=" . $buscamasUrl . "' " . $buscamasPublishUrl);

            $this->getMessenger()->success('Aviso eliminado satisfactoriamente.');
            $this->_redirect($_SERVER['HTTP_REFERER']);
        } catch (Zend_Db_Exception $e) {
            $db->rollBack();
            $this->getMessenger()->error('Error al Eliminar el aviso.');
            echo $e->getMessage();
        } catch (Zend_Exception $e) {
            $this->getMessenger()->error($this->_messageSuccess);
            echo $e->getMessage();
        }
    }

    public function verAvisoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->view->module = $this->_request->getModuleName();


        $params = $this->_getAllParams();
        //$usuario = $this->auth['usuario'];
        /* if ($this->auth != null && array_key_exists('postulante', $this->auth)) {
          $usuarioRol = $this->auth['postulante'];
          } else {
          if (isset($this->auth['empresa']))
          $usuarioRol = $this->auth['empresa'];
          } */
        $idAw   = $params['id'];

        $a            = new Application_Model_AnuncioWeb();
        $this->_aviso = $a->getAvisoBySlugByIdAw($this->_urlId, $idAw);

        $config = $this->getConfig();
        //$this->view->anunciosRelacionados = $a->getAvisoRelacionados($this->_aviso['id']);

        $acceso               = Application_Model_AnuncioWeb::accesoAnuncio($this->_aviso['id_empresa'],
                $this->auth);
        $this->view->acceso   = $acceso;
        $this->view->aviso    = $this->_aviso;
        $this->view->online   = true;
        $this->view->menu_sel = self::MENU_AVISOS;
        if ($this->_aviso == null) {
            $this->_redirect('/');
        }
        if ($this->_aviso['online'] == '0') {
            $this->view->online = false;
            return;
        }

        Zend_Layout::getMvcInstance()->assign(
            'empresa', $this->_aviso['nombre_empresa']
        );
        //$p = new Application_Model_Postulacion();

        $this->view->slug   = $this->_slug;
        $this->view->urlId  = $this->_urlId;
        $this->view->logo   = $this->_aviso['logo_empresa'];
        $this->view->moneda = $config->app->moneda;
    }

    public function verAvisoPreferencialAction()
    {
        $this->_helper->layout->disableLayout();
        $idAnuncioPreferencial = $this->_getParam('preferencial');
        $t                     = new Application_Model_Tarifa();
        $anuncioImpreso        = new Application_Model_AnuncioImpreso();
        $aviso                 = new Application_Model_AnuncioWeb();

        $dataAvisos    = $aviso->getPosicionByAvisoPreferencial($idAnuncioPreferencial);
        $ai            = $anuncioImpreso->getDataAnuncioImpreso($idAnuncioPreferencial);
        $tarifaId      = $ai['id_tarifa'];
        $arrayDataAjax = array();

        $this->view->maximoAnuncios = $t->getNumeroAvisoMaximoByPreferencial($tarifaId);
        $this->view->dataPosicion   = $aviso->getPosicionByAviso($dataAvisos['data'][0]['id']);

        for ($i = 1; $i <= $this->view->maximoAnuncios; $i++) {
            if (isset($dataAvisos['data'][$i - 1]['id'])) {
                $urlInfo                        = $aviso->getUrlInfoById($dataAvisos['data'][$i
                    - 1]['id']);
                $arrayDataAjax['data-ajax'][$i] = '/empresa/mi-cuenta/ver-aviso/'.$urlInfo['url_id'].'/'.$urlInfo['slug'].
                    '/'.$dataAvisos['data'][$i - 1]['id'];
            }
        }

        $this->view->module   = $this->_request->getModuleName();
        $this->view->dataAjax = $arrayDataAjax;

        $usuario = $this->auth['usuario'];
        if ($this->auth != null && array_key_exists('postulante', $this->auth)) {
            $usuarioRol = $this->auth['postulante'];
        } else {
            if (isset($this->auth['empresa']))
                    $usuarioRol = $this->auth['empresa'];
        }
        $this->_aviso = $aviso->getAvisoById($dataAvisos['data'][0]['id']);
        $config       = $this->getConfig();

        $acceso               = Application_Model_AnuncioWeb::accesoAnuncio($this->_aviso['id_empresa'],
                $this->auth);
        $this->view->acceso   = $acceso;
        $this->view->aviso    = $this->_aviso;
        $this->view->online   = true;
        $this->view->menu_sel = self::MENU_AVISOS;

        if ($this->_aviso == null) {
            $this->_redirect('/');
        }
        if ($this->_aviso['online'] == '0') {
            $this->view->online = false;
            return;
        }

        Zend_Layout::getMvcInstance()->assign(
            'empresa', $this->_aviso['nombre_empresa']
        );
        $p = new Application_Model_Postulacion();

        $this->view->slug   = $this->_slug;
        $this->view->urlId  = $this->_urlId;
        $this->view->logo   = $this->_aviso['logo_empresa'];
        $this->view->moneda = $config->app->moneda;
    }

    public function membresiasAction()
    {
        $this->view->menu_sel_side = self::MENU_POST_SIDE_MIS_MEMBRESIAS;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;
//        var_dump($this->auth['empresa']);
        //$membresias = Application_Model_Membresia::getMebresiasByEmpresaId($this->auth['empresa']['id']);
        //$this->view->membresias = $membresias;


        $idEmpresa = $this->auth['empresa']['id'];

        $page            = $this->_getParam('page', 1);
        $this->view->col = $col             = $this->_getParam('col', '');
        $this->view->ord = $ord             = $this->_getParam('ord', 'desc');
        $paginator       = $this->_empresa->getPaginador(
            $idEmpresa, $col, $ord, $page
        );

        $paginado = $this->config->membresias->paginado;

        $paginator->setItemCountPerPage($paginado);
        $paginator->setCurrentPageNumber($page);
        $this->view->membresias = $paginator;
        $this->view->moneda     = $this->config->app->moneda;
    }

    public function detalleEmpresaMembresiaAction()
    {
        $this->_helper->layout->disableLayout();
        $idEmpresaMembresia  = $this->_getParam('idEmpMem', 1);
        $objEmpresaMembresia = new Application_Model_EmpresaMembresia();
        $detalleMembresia    = $objEmpresaMembresia->getDetalleEmpresaMembresia($idEmpresaMembresia,
            true);
        $this->view->detalle = $detalleMembresia;
    }

    public function misAlertasAction()
    {
        $this->view->menu_sel_side = self::MENU_POST_SIDE_MIS_ALERTAS;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;

        $this->view->modulo = $this->getRequest()->getModuleName();
        $idEmpresa          = $this->idEmpresa;

//        $this->view->listaPostulantesSugeridos = $this->candidatosSugeridos(
//            $this->_candidatosSugeridos, $idEmpresa
//        );

        $formAlertas = new Application_Form_MisAlertas();

        $empresa = new Application_Model_Empresa();


        if ($this->_candidatosSugeridos == "Mostrar Avisos" && $this->_tieneBuscador
            == false) {
            $formAlertas->getElement('prefs_emailing_avisos')->setAttrib('disabled',
                'disabled');
            $empresa->update(
                array('prefs_emailing_avisos' => '0'),
                $this->getAdapter()->quoteInto('id = ?', $idEmpresa)
            );
        } elseif ($this->_candidatosSugeridos != "Mostrar Avisos" && $this->_tieneBuscador
            == false) {
            $formAlertas->getElement('prefs_emailing_avisos')->setAttrib('disabled',
                'disabled');
            $empresa->update(
                array('prefs_emailing_avisos' => '0'),
                $this->getAdapter()->quoteInto('id = ?', $idEmpresa)
            );
        }

        $alertaEmpresa = $empresa->getAlertaEmpresa($idEmpresa);
        $formAlertas->setDefaults($alertaEmpresa);

        if ($this->_request->isPost() && $this->_hash->isValid($this->_getParam("csrfhash"))) {
            $allParams    = $this->_getAllParams();
            $validAlertas = $formAlertas->isValid($allParams);
            if ($validAlertas) {
                $formAlertas->removeElement('prefs_emailing_mercado');

                $valuesAlertas = $formAlertas->getValues();
//                var_dump($valuesAlertas);exit;
                $where         = $empresa->getAdapter()
                    ->quoteInto('id = ?', $idEmpresa);
                $empresa->update($valuesAlertas, $where);

                $this->getMessenger()->success('Los datos se actualizaron con éxito.');

                $this->_redirect(
                    Zend_Controller_Front::getInstance()
                        ->getRequest()->getRequestUri()
                );
            }
        }

        $this->view->formAlertas = $formAlertas;
    }

    public function candidatosSugeridos($flagCandidato, $idEmpresa)
    {
        if ($flagCandidato == 1) {
            $objAnuncioMatch = new Application_Model_AnuncioPostulanteMatch();
            $result          = $objAnuncioMatch->getPostulantesSugeridos($idEmpresa);

            if ($result) {
                return $result;
            } else {
                return "Mostrar Avisos";
            }
        }
    }

    public function cargafotoAction()
    {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $tipo = $this->getRequest()->getParam("tipo");

        //Agregar ID
        //$idEmpresa = $this->auth;

        $img = 'logotipo';
        $url = ELEMENTS_URL_LOGOS;

        $r = $this->getRequest();
        if ($r->isPost()) {
            $session = $this->getSession();
            if ($session->__isset("tmp_img")) {
                @unlink($session->__get("tmp_img"));
            }
            $tamanomax = $r->__get("filesize");
            $tamano    = $_FILES[$img]['size'];

            if ($tamano <= $tamanomax) {
                $utilfile      = $this->_helper->getHelper('UtilFiles');
                $archivo       = $_FILES[$img]['name'];
                $tipo          = $utilfile->_devuelveExtension($archivo);
                $nombre        = "temp_".time().".".$tipo;
                $nombrearchivo = "elements/empleo/img/temp/".$nombre;
                $session->__set("tmp_img", $nombrearchivo);
                move_uploaded_file($_FILES[$img]['tmp_name'], $nombrearchivo);
                $imgx          = new ZendImage();
                $imgx->loadImage(APPLICATION_PATH."/../public/".$nombrearchivo);

                echo Zend_Json::encode(array(
                    'status' => 1,
                    'new_image' => $nombre,
                    'url' => '/'.$nombrearchivo,
                    'id' => $nombre,
                    'msg' => '',
                    'name' => $nombre
                ));
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
                }
            }
        }
    }

    public function subirLogoAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idEmpresa = $this->auth['empresa']['id'];

        if ($this->auth['empresa']['logo'] == '') {

            $frmEmpresa    = new Application_Form_Paso1Empresa();
            $utilfile      = $this->_helper->getHelper('UtilFiles');
            $helperAviso   = $this->_helper->getHelper('Aviso');
            $nuevosNombres = $utilfile->_renameFile($frmEmpresa, 'logotipo',
                "image-empresa");

            //Sube logotipo
            if (is_array($nuevosNombres)) {
                $valuesEmpresa['logo']  = $nuevosNombres[0];
                $valuesEmpresa['logo1'] = $nuevosNombres[1];
                $valuesEmpresa['logo2'] = $nuevosNombres[2];
                $valuesEmpresa['logo3'] = $nuevosNombres[3];

                $where   = $this->_empresa->getAdapter()->quoteInto('id = ?',
                    $idEmpresa);
                $this->_empresa->update($valuesEmpresa, $where);
                $anuncio = new Application_Model_AnuncioWeb();

                $anuncio->updateLogoAnuncio($idEmpresa, $valuesEmpresa["logo2"]);

                //Actualiza avisos activos
                $modelAviso       = new Application_Model_AnuncioWeb;
                $avisosActualizar = $modelAviso->obtenerAvisosActivosEmpresa($idEmpresa);

                foreach ($avisosActualizar as $infoAviso) {
                    $helperAviso->_SolrAviso->addAvisoSolr($infoAviso['id']);
                    //exec("curl -X POST -d 'api_key=" . $this->_buscamasConsumerKey . "&nid=" . $infoAviso['id'] . "&site=" . $this->_buscamasUrl . "' " . $this->_buscamasPublishUrl);
                }
            }
        }

        echo Zend_Json::encode(array('success' => 1, 'msg' => 'El logo fue cargado satisfactoriamente.'));
    }
}
