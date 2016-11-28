<?php

class Empresa_PublicaAvisoController extends App_Controller_Action_Empresa
{
    protected $_avisoId;
    protected $_empresa;
    private $_tipoVia;
    private $_config;
    //Buscamas
    private $_buscamasConsumerKey;
    private $_buscamasPublishUrl;
    private $_buscamasUrl;

    public function init()
    {
        parent::init();

        $this->_usuario = new Application_Model_Usuario();
        $this->_tipoVia = new Application_Model_TipoVia;
        $this->_empresa = new Application_Model_Empresa;

        $this->_config = Zend_Registry::get('config');

        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'myAccount', 'class' => '')
        );
        $this->view->headMeta()->appendName(
            "Keywords",
            "elige tu aviso, publica tu aviso, aviso en AquiEmpleos, ".
            "pasos para publicar, Perfil del puesto, ".
            "Complete su aviso Impreso, Pague su Aviso"
        );
        $this->view->empresaId = isset($this->auth['empresa']['id']) ?
            $this->auth['empresa']['id'] : '';

        $anuncioId = null;
        $republica = $this->_getParam('republica');
        $extiende  = $this->_getParam('extiende');

        if (!empty($republica)) $anuncioId = $republica;

        if (!empty($extiende)) $anuncioId = $extiende;

        if (!is_null($anuncioId)) {
            $anuncio        = new App_Service_Validate_Ad($anuncioId);
            $usuarioEmpresa = $this->auth['usuario-empresa'];

            if (!$anuncio->isManaged($usuarioEmpresa)) {
                $this->getMessenger()->error($anuncio->getMessage());
                $this->_redirect('/empresa/mi-cuenta');
            }
        }
        if (isset($this->auth['usuario']->id) && $this->_usuario->hasvailBlokeo($this->auth['usuario']->id)) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::forgetMe();
            $this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
            $this->_redirect('/empresa');
        }
    }


    public function indexAction()
    {
        $mailSession = new Zend_Session_Namespace();
        unset($mailSession->send);

        $session = $this->getSession();
        //$filter = new App_Filter_Slug();
        if ($this->_getParam('id_tarifa') != null) {
            $tarifaId = $this->_getParam('id_tarifa');
        } elseif ($session->tarifa != null) {
            $tarifaId = $session->tarifa;
        } else {
            $tarifaId = $this->_getParam('tarifa');
        }
        $this->view->menu_sel      = self::MENU_PUBLICAAVISO;
        $this->view->menu_post_sel = self::MENU_PUBLICAAVISO;
//        if($tarifaId==1&&!isset($this->auth['empresa']['membresia_info']['membresia']))
//            $this->_redirect('/empresa/publica-aviso');
        if ($tarifaId == 169 && isset($this->auth['empresa']['membresia_info']['membresia']))
                $this->_redirect('/empresa/publica-aviso');
        $idEmpresa                 = $this->auth['empresa']['id'];
        $idUsua                    = $this->auth["usuario"]->id;
        $this->view->tarifa        = $tarifaId;

        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );
        $this->view->headTitle()->set(
            'Ingrese el perfil del puesto, Publica tu aviso en AquiEmpleos'
        );
        $this->view->headMeta()->appendName(
            "Description",
            "Ingrese el Perfil del puesto, segundo paso para la publicación de ".
            "tu aviso en aquiempleos.com.  Los Clasificados de Empleos de La Prensa."
        );
        if ($this->_getParam('republica') != "") {
            $republica = $this->_getParam('republica');
        }
        if ($tarifaId == null && !$this->_getParam('id_producto')) {
            $this->_redirect('/empresa/publica-aviso/index/tarifa/1');
        }
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.aviso.paso2.js')
        );

        $config                           = Zend_Registry::get("config");
        // @codingStandardsIgnoreStart
        $this->view->numPalabraPuesto     = $config->avisopaso2->puestonumeropalabra;
        $this->view->numPalabraOtroNombre = $config->avisopaso2->mostrarnombrenumeropalabra;
        //@codingStandardsIgnoreEnd
        $frmUbigeo                        = new Application_Form_Ubigeo();
        //  $frmUbigeo->detalleUbigeo($this->auth['empresa']['id_ubigeo']);
        $formDatos                        = new Application_Form_Paso2PublicarAviso();
        //@codingStandardsIgnoreStart
        $formDatos->id_tarifa->setValue($tarifaId);
        //@codingStandardsIgnoreEnd
        //Verificar si tiene logo
        if ($this->auth['empresa']['logo'] == '') {
            $this->view->showLogoEmpresa = true;
            $frmEmpresa                  = new Application_Form_Paso1Empresa();
            $frmEmpresa->removeElement('rubro');
            $frmEmpresa->removeElement('pais_residencia');
            $frmEmpresa->removeElement('id_departamento');
            $frmEmpresa->removeElement('id_provincia');
            $frmEmpresa->removeElement('id_distrito');
            $frmEmpresa->removeElement('num_ruc');
            $frmEmpresa->removeElement('nombrecomercial');
            $frmEmpresa->removeElement('razonsocial');
        }

        $modelProducto        = new Application_Model_Producto();
        $tipoProducto         = $modelProducto->obtenerTipoAviso($tarifaId);
        $modelTarifa          = new Application_Model_Tarifa();
        $this->view->producto = $modelTarifa->getProductoByTarifa($tarifaId);
        $empID                = Application_Model_Usuario::getEmpresaId();
        $this->view->lock     = 1;
        if ($empID == Application_Model_AnuncioWeb::JJC_ID) {

            if ($tipoProducto == Application_Model_AnuncioWeb::TIPO_WEB) {
                $puesto           = new Application_Model_Puesto();
                $listaPuesto      = $puesto->getPuestos();
                $this->view->lock = 0;
                $formDatos->id_puesto->setMultiOptions(
                    array('-1' => 'Seleccionar tipo')
                );
                $formDatos->id_puesto->addMultiOptions($listaPuesto);
            }
        } else {
            if ($tipoProducto != Application_Model_AnuncioWeb::TIPO_CLASIFICADO) {
                $formDatos->removeTipoPuesto();
            }
        }

        $baseFormEstudio = new Application_Form_Paso2EstudioPublicar(false,
            false);
        $managerEstudio  = new App_Form_Manager($baseFormEstudio,
            'managerEstudio');

        $baseFormExperiencia = new Application_Form_Paso2ExperienciaPublicar(false,
            false);
        $managerExperiencia  = new App_Form_Manager($baseFormExperiencia,
            'managerExperiencia');

        $baseFormOtroEstudio = new Application_Form_Paso2OtroEstudioPublicar();
        $managerOtroEstudio  = new App_Form_Manager($baseFormOtroEstudio,
            'managerOtroEstudio');

        $baseFormIdioma = new Application_Form_Paso2Idioma();
        $managerIdioma  = new App_Form_Manager($baseFormIdioma, 'managerIdioma');

        $baseFormPrograma = new Application_Form_Paso2Programa();
        $managerPrograma  = new App_Form_Manager($baseFormPrograma,
            'managerPrograma');

        $baseFormPregunta = new Application_Form_Paso2PreguntaPublicar();
        $managerPregunta  = new App_Form_Manager($baseFormPregunta,
            'managerPregunta');

        $formEstudio     = array();
        $formOtroEstudio = array();
        $formExperiencia = array();
        $formIdioma      = array();
        $formPrograma    = array();
        $formPregunta    = array();
        $falla           = 0;
        if ($this->getRequest()->isPost()) {

            $nompuesto = '';
            $postData  = $this->_getAllParams();
//var_dump($postData);
//die(" --------->post! ");

            $postData["funciones"]         = preg_replace($config->avisopaso2->expresionregular,
                '', $postData["funciones"]);
            $postData["responsabilidades"] = preg_replace($config->avisopaso2->expresionregular,
                '', $postData["responsabilidades"]);
            $postData["funciones"]         = str_replace("@", "",
                $postData["funciones"]);
            $postData["responsabilidades"] = str_replace("@", "",
                $postData["responsabilidades"]);

            unset($session->tarifa);
            /* if (isset($postData["managerEstudio"])) {
              unset($postData["managerEstudio"]);
              } */
            if (isset($postData["managerExperiencia"])) {
                unset($postData["managerExperiencia"]);
            }
            if (isset($postData["managerOtroEstudio"])) {
                unset($postData["managerOtroEstudio"]);
            }
            if (isset($postData["managerIdioma"])) {
                unset($postData["managerIdioma"]);
            }
            if (isset($postData["managerPrograma"])) {
                unset($postData["managerPrograma"]);
            }

            $validEstudio     = $managerEstudio->isValid($postData);
            $validOtroEstudio = $managerOtroEstudio->isValid($postData);
            $validExperiencia = $managerExperiencia->isValid($postData);
            $validIdioma      = $managerIdioma->isValid($postData);
            $validPrograma    = $managerPrograma->isValid($postData);
            $validPregunta    = $managerPregunta->isValid($postData);

//var_dump($validEstudio, $validOtroEstudio, $validExperiencia, $validIdioma, $validPrograma, $validPregunta);

            $formEstudio     = $managerEstudio->getForms();
            $formOtroEstudio = $managerOtroEstudio->getForms();
            $formExperiencia = $managerExperiencia->getForms();
            $formIdioma      = $managerIdioma->getForms();
            $formPrograma    = $managerPrograma->getForms();
            $formPregunta    = $managerPregunta->getForms();

            $this->view->isEstudio     = true;
            $this->view->isOtroEstudio = true;
            $this->view->isExperiencia = true;
            $this->view->isIdioma      = true;
            $this->view->isPrograma    = true;
            $this->view->isPregunta    = true;
            if ($formDatos->isValid($postData) &&
                $validEstudio &&
                $validExperiencia &&
                $validOtroEstudio &&
                $validIdioma &&
                $validPrograma &&
                $frmUbigeo->isValid($postData)
            ) {
                // @codingStandardsIgnoreStart
                $postData['id_usuario'] = $idUsua;
                $avisoHelper            = $genPassword            = $this->_helper->getHelper('Aviso');
                $anuncioWebModel        = new Application_Model_AnuncioWeb();
                $ubigeo                 = new Application_Model_Ubigeo();
                $util                   = $this->_helper->getHelper('Util');
                $postData['id_ubigeo']  = $util->getUbigeo($postData);

                $_pais                = $ubigeo->getUbigeo($postData['pais_residencia']);
                $postData['slugpais'] = $_pais['slug_ubigeo'];

                if ($tarifaId == 1) {
                    $postData['prioridad'] = $avisoHelper->getOrdenPrioridad('soloweb',
                        $this->auth['empresa']['id']);
                } else {
                    $dataPrioridad         = $anuncioWebModel->prioridadAviso('clasificado',
                        $this->auth['empresa']['id']);
                    $prioridad             = $dataPrioridad['prioridad'];
                    $postData['prioridad'] = $prioridad;
                }

                // @codingStandardsIgnoreEnd
                if (isset($extiende)) {
                    $avisoId = $avisoHelper->_insertarNuevoPuesto($postData,
                        $extiende);
                    $usuario = $this->auth['usuario'];
                    $avisoHelper->extenderAviso($avisoId, $usuario->id);
                    $avisoHelper->extenderReferidos($avisoId);
                } elseif (isset($republica)) {
                    $avisoId = $avisoHelper->_insertarNuevoPuesto($postData,
                        $republica, "", '1');
                } else {
                    $avisoId = $avisoHelper->_insertarNuevoPuesto($postData);
                }

                $usuarioEmpresa = $this->auth['usuario-empresa'];

                $anuncioUsuarioEmpresaModelo = new Application_Model_AnuncioUsuarioEmpresa;

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

                if (isset($extiende)) {
                    //Actualiza Match a Postulantes
                    $mPostulacion = new Application_Model_Postulacion;
                    $mPostulacion->actualizarMatchPostulantes($avisoId);
                }

                if ($this->auth['empresa']['logo'] == '') {
                    $utilfile      = $this->_helper->getHelper('UtilFiles');
                    $nuevosNombres = $utilfile->_renameFile($frmEmpresa,
                        'logotipo', "image-empresa");
                    //Sube logotipo y actualiza avisos activos
                    if (is_array($nuevosNombres)) {

                        $valuesEmpresa['logo']  = $nuevosNombres[0];
                        $valuesEmpresa['logo1'] = $nuevosNombres[1];
                        $valuesEmpresa['logo2'] = $nuevosNombres[2];
                        $valuesEmpresa['logo3'] = $nuevosNombres[3];


                        $where = $this->_empresa->getAdapter()
                            ->quoteInto('id = ?', $idEmpresa);
                        $this->_empresa->update($valuesEmpresa, $where);

                        //Actualiza logo en Zend_Auth
                        $storage                    = Zend_Auth::getInstance()->getStorage()->read();
                        $storage['empresa']['logo'] = $nuevosNombres[0];
                        Zend_Auth::getInstance()->getStorage()->write($storage);

                        $anuncio = new Application_Model_AnuncioWeb();
                        $anuncio->updateLogoAnuncio($idEmpresa,
                            $valuesEmpresa["logo2"]);

                        $modelAviso           = new Application_Model_AnuncioWeb;
                        $dataAvisoXActualizar = $modelAviso->obtenerAvisosActivosEmpresa($idEmpresa);
                        foreach ($dataAvisoXActualizar as $infoAviso) {
                            $avisoHelper->_SolrAviso->addAvisoSolr($infoAviso['id']);
                            //exec("curl -X POST -d 'api_key=" . $this->_buscamasConsumerKey . "&nid=" . $infoAviso['id'] . "&site=" . $this->_buscamasUrl . "' " . $this->_buscamasPublishUrl);
                        }
                    }
                }

                $params = "";
                if (isset($extiende) && $extiende != "") {
                    $params .= "/extiende/".$extiende;
                }
                if (isset($republica) && $republica != "") {
                    $params .= "/republica/".$republica;
                }


                // $this->_redirect('/empresa/mi-cuenta');
                $this->_redirect('/empresa/publica-aviso/paso1/aviso/'.$avisoId.$params);
                // $this->_redirect('/empresa/publica-aviso/paso4/aviso/' . $avisoId . $params);
            } else {
                if (/* $validOtroEstudio && */!$managerOtroEstudio->isEmptyLastForm()) {
                    $ind                   = count($managerOtroEstudio->getForms());
                    $formOtroEstudio[$ind] = $managerOtroEstudio->getForm($ind);
                }
                if (/* $validExperiencia && */!$managerExperiencia->isEmptyLastForm()) {
                    $ind                   = count($managerExperiencia->getForms());
                    $formExperiencia[$ind] = $managerExperiencia->getForm($ind);
                }
                if (/* $validIdioma && */!$managerIdioma->isEmptyLastForm()) {
                    $ind              = count($managerIdioma->getForms());
                    $formIdioma[$ind] = $managerIdioma->getForm($ind);
                }
                if (/* $validPrograma && */!$managerPrograma->isEmptyLastForm()) {
                    $ind                = count($managerPrograma->getForms());
                    $formPrograma[$ind] = $managerPrograma->getForm($ind);
                }
                if (/* $validPregunta && */!$managerPregunta->isEmptyLastForm()) {
                    $ind                = count($managerPregunta->getForms());
                    $formPregunta[$ind] = $managerPregunta->getForm($ind);
                }

                $formuEstudio = $managerEstudio->getForms();
                $formEstudio  = array();
                foreach ($formuEstudio as $ke => $fe) {
                    $id_tipo_carrera  = $fe->getElement('id_tipo_carrera')->getValue();
                    $fe->setElementCarrera($id_tipo_carrera);
                    $id_nivel_estudio = $fe->getElement('id_nivel_estudio')->getValue();
                    $fe->setElementNivelEstudio($id_nivel_estudio);
                    $formEstudio[$ke] = $fe;
                }
                $falla = 1;
                if (/* $validEstudio && */!$managerEstudio->isEmptyLastForm()) {
                    $ind               = count($managerEstudio->getForms());
                    $formEstudio[$ind] = $managerEstudio->getForm($ind);
                }
            }
        } elseif (isset($republica) && $republica != "") {
            $aviso                  = new Application_Model_AnuncioWeb();
            $datosAviso             = $aviso->getAvisoInfoById($republica);
            $datosAviso['id_aviso'] = $datosAviso['id'];
            if ($datosAviso['salario'] == null && $datosAviso['salario_min'] != null) {
                $datosAviso['salario'] = $datosAviso['salario_min'].'-max';
            }
            if ($datosAviso['mostrar_empresa'] == 0) {
                $datosAviso['otro_nombre_empresa'] = $datosAviso['empresa_rs'];
                unset($datosAviso['empresa_rs']);
            }
            $datosAviso['id_tarifa'] = $tarifaId;
            $formDatos->isValid($datosAviso);
            unset($session->tarifa);
            $datosAvisoEstudio       = $aviso->getEstudioInfoByAnuncio($republica);
            $i                       = 0;
            if (count($datosAvisoEstudio) > 0) {
                $this->view->isEstudio = false;
                foreach ($datosAvisoEstudio as $d) {
                    $form = $managerEstudio->getForm($i++, $d);
                    if (isset($d['id_carrera'])) {
                        $carrera       = new Application_Model_Carrera();
                        $idTipoCarrera = $carrera->getTipoCarreraXCarrera($d['id_carrera']);
                        $carreras      = $carrera->filtrarCarrera($idTipoCarrera);
                        $form->getElement('id_carrera')->addMultioptions($carreras);
                        $form->getElement('otra_carrera')->setValue($d['otra_carrera']);
                    }
                    $d['id_tipo_carrera'] = $idTipoCarrera;
                    $form->isValid($d);
                    $formEstudio[]        = $form;
                }
            } else {
                $form          = $managerEstudio->getForm($i++);
                $formEstudio[] = $form;
            }

            $datosAvisoExperiencia = $aviso->getExperienciaInfoByAnuncio($republica);

            $i = 0;
            if (count($datosAvisoExperiencia) > 0) {
                $this->view->isExperiencia = false;
                foreach ($datosAvisoExperiencia as $d) {
                    $form              = $managerExperiencia->getForm($i++, $d);
                    $formExperiencia[] = $form;
                }
            } else {
                $form              = $managerExperiencia->getForm($i++);
                $formExperiencia[] = $form;
            }

            $datosAvisoIdioma = $aviso->getIdiomaInfoByAnuncio($republica);

            $i = 0;
            if (count($datosAvisoIdioma) > 0) {
                $this->view->isIdioma = false;
                foreach ($datosAvisoIdioma as $d) {
                    $form         = $managerIdioma->getForm($i++, $d);
                    $formIdioma[] = $form;
                }
            } else {
                $form         = $managerIdioma->getForm($i++);
                $formIdioma[] = $form;
            }

            $datosAvisoPrograma = $aviso->getProgramaInfoByAnuncio($republica);

            $i = 0;
            if (count($datosAvisoPrograma) > 0) {
                $this->view->isPrograma = false;
                foreach ($datosAvisoPrograma as $d) {
                    $form           = $managerPrograma->getForm($i++, $d);
                    $formPrograma[] = $form;
                }
            } else {
                $form           = $managerPrograma->getForm($i++);
                $formPrograma[] = $form;
            }

            $datosAvisoPregunta = $aviso->getPreguntaInfoByAnuncio($republica);

            $i = 0;
            if (count($datosAvisoPregunta) > 0) {
                $this->view->isPregunta = false;
                foreach ($datosAvisoPregunta as $d) {
                    $form           = $managerPregunta->getForm($i++, $d);
                    $formPregunta[] = $form;
                }
            } else {
                $form           = $managerPregunta->getForm($i++);
                $formPregunta[] = $form;
            }
        } elseif (isset($extiende) && $extiende != "") {
            $aviso                  = new Application_Model_AnuncioWeb();
            $datosAviso             = $aviso->getAvisoInfoById($extiende);
            $datosAviso['id_aviso'] = $datosAviso['id'];
            if ($datosAviso['salario'] == null && $datosAviso['salario_min'] != null) {
                $datosAviso['salario'] = $datosAviso['salario_min'].'-max';
            }
            if ($datosAviso['mostrar_empresa'] == 0) {
                $datosAviso['otro_nombre_empresa'] = $datosAviso['empresa_rs'];
                unset($datosAviso['empresa_rs']);
            }
            $datosAviso['id_tarifa'] = $tarifaId;
            $formDatos->isValid($datosAviso);
            unset($session->tarifa);
            $datosAvisoEstudio       = $aviso->getEstudioInfoByAnuncio($extiende);
            $i                       = 0;
            if (count($datosAvisoEstudio) > 0) {
                $this->view->isEstudio = true;
                foreach ($datosAvisoEstudio as $d) {
                    $form = $managerEstudio->getForm($i++, $d);
                    if (isset($d['id_carrera'])) {
                        $carrera       = new Application_Model_Carrera();
                        $idTipoCarrera = $carrera->getTipoCarreraXCarrera($d['id_carrera']);
                        $carreras      = $carrera->filtrarCarrera($idTipoCarrera);
                        $form->getElement('id_tipo_carrera')->setValue($idTipoCarrera);
                        $form->getElement('id_carrera')->addMultioptions($carreras);
                        $form->getElement('otra_carrera')->setValue($d['otra_carrera']);

                        $form->setElementNivelEstudio($d['id_nivel_estudio']);
                        $form->getElement('id_nivel_estudio_tipo')->setValue($d['id_nivel_estudio_tipo']);
                    }

                    $d['id_tipo_carrera'] = $idTipoCarrera;
                    $form->isValid($d);
                    $formEstudio[]        = $form;
                }
            }
            $form          = $managerEstudio->getForm($i++);
            $formEstudio[] = $form;

            $datosAvisoOtroEstudio = $aviso->getOtroEstudioInfoByAnuncio($extiende);

            $i = 0;
            if (count($datosAvisoOtroEstudio) > 0) {
                $this->view->isOtroEstudio = true;
                foreach ($datosAvisoOtroEstudio as $d) {
                    $form              = $managerOtroEstudio->getForm($i++, $d);
                    $formOtroEstudio[] = $form;
                }
            }
            $form              = $managerOtroEstudio->getForm($i++);
            $formOtroEstudio[] = $form;

            $datosAvisoExperiencia = $aviso->getExperienciaInfoByAnuncio($extiende);

            $i = 0;

            if (count($datosAvisoExperiencia) > 0) {
                $this->view->isExperiencia = true;
                foreach ($datosAvisoExperiencia as $d) {
                    $form              = $managerExperiencia->getForm($i++, $d);
                    $formExperiencia[] = $form;
                }
            }
            $form              = $managerExperiencia->getForm($i++);
            $formExperiencia[] = $form;

            $datosAvisoIdioma = $aviso->getIdiomaInfoByAnuncio($extiende);

            $i = 0;
            if (count($datosAvisoIdioma) > 0) {
                $this->view->isIdioma = true;
                foreach ($datosAvisoIdioma as $d) {
                    $form         = $managerIdioma->getForm($i++, $d);
                    $formIdioma[] = $form;
                }
            }
            $form         = $managerIdioma->getForm($i++);
            $formIdioma[] = $form;

            $datosAvisoPrograma = $aviso->getProgramaInfoByAnuncio($extiende);

            $i = 0;
            if (count($datosAvisoPrograma) > 0) {
                $this->view->isPrograma = true;
                foreach ($datosAvisoPrograma as $d) {
                    $form           = $managerPrograma->getForm($i++, $d);
                    $formPrograma[] = $form;
                }
            }
            $form           = $managerPrograma->getForm($i++);
            $formPrograma[] = $form;

            $datosAvisoPregunta = $aviso->getPreguntaInfoByAnuncio($extiende);

            $i = 0;
            if (count($datosAvisoPregunta) > 0) {
                $this->view->isPregunta = true;
                foreach ($datosAvisoPregunta as $d) {
                    $form           = $managerPregunta->getForm($i++, $d);
                    $formPregunta[] = $form;
                }
            }
            $form           = $managerPregunta->getForm($i++);
            $formPregunta[] = $form;
        } else {
            $formEstudio[]             = $managerEstudio->getForm(0);
            $formOtroEstudio[]         = $managerOtroEstudio->getForm(0);
            $formExperiencia[]         = $managerExperiencia->getForm(0);
            $formIdioma[]              = $managerIdioma->getForm(0);
            $formPrograma[]            = $managerPrograma->getForm(0);
            $formPregunta[]            = $managerPregunta->getForm(0);
            $this->view->isEstudio     = false;
            $this->view->isOtroEstudio = false;
            $this->view->isExperiencia = false;
            $this->view->isIdioma      = false;
            $this->view->isPrograma    = false;
        }

        if ($this->auth['empresa']['logo'] == '') {
            $this->view->frmEmpresa = $frmEmpresa;
        }

        $this->view->idAnunciante = $this->auth['usuario-empresa']['id_usuario'];
        $this->view->form         = $formDatos;

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
        $this->view->falla     = $falla;
    }

    public function paso1Action()
    {
        $modelProducto  = new Application_Model_Producto();
        $aviso          = $this->_getParam('aviso');
        $sessionAdmin   = new Zend_Session_Namespace('admin');
        $isSessionAdmin = ($sessionAdmin->auth ? true : false);
        $dataimpreso    = $modelProducto->getTarifasImpreso();

        $formImpresoLineal           = new Application_Form_PublicarAvisoImpresoLineal($dataimpreso[$this->_config->tarifas->impresolineal]);
        $formImpresoLineal->addRadio('estilo', 'impreso-estilo');
        $formImpresoLineal->addRadio('Fondo', 'impreso-fondo');
        $formImpresoLineal->addRadio('color', 'impreso-color');
        $this->view->idTarifaImpreso = $this->_config->tarifas->impresolineal;
        if ($aviso == null) {
            $this->_redirect('/empresa/publica-aviso/index/tarifa/1');
        }

        if ($this->getRequest()->isPost()) {
            $postData  = $this->_getAllParams();
            $encode    = $this->_helper->JWT->encode($postData);
            $this->_aw = new Application_Model_AnuncioWeb();
            $this->_aw->update(array('id_tarifa' => $postData['id_tarifa']),
                array('id =?' => $postData['aviso']));
            switch ($postData['id_tarifa']) {
                case 1:
                    $rowAnuncio                           = $this->_aw->getDatosGenerarCompra($postData['aviso']);
                    $rowAnuncio['totalPrecio']            = 0;
                    $rowAnuncio['tipoDoc']                = 'boleta';
                    $rowAnuncio['tipoPago']               = 'gratuito';
                    $rowAnuncio['medio_pago_web']         = 'gratuito';
                    $rowAnuncio['precio_total_impreso']   = 0;
                    $rowAnuncio['tipo_medio_publicacion'] = 'gratuito';
                    $usuario                              = $this->auth['usuario'];
                    $rowAnuncio['usuarioId']              = $usuario->id;
                    $compraId                             = $this->_helper->aviso->generarCompraAnuncio($rowAnuncio);
                    $this->_helper->aviso->confirmarCompraAviso($compraId, 0);
                    if ($rowAnuncio['tipoPago'] == 'gratuito') {

                        $this->_ubigeo = new Application_Model_Ubigeo();
                        $detalle       = $this->_ubigeo->getDetalleUbigeo($this->auth['empresa']['id_ubigeo']);
                        $ubicacion     = $detalle['paisres'];
                        if (!empty($detalle['provincia'])) {
                            $ubicacion = $detalle['provincia'];
                        }
                        $url = $this->view->url(
                            array(
                            'url_id' => $rowAnuncio['url'],
                            'empresaslug' => $this->view->Util()->cleanString(strtolower($rowAnuncio['empresaslug'])),
                            'ubicacionslug' => $this->view->Util()->cleanString(strtolower($ubicacion)),
                            'slug' => $rowAnuncio['slug']
                            ), 'aviso_detalle', true
                        );

                        $dataEmail = array(
                            'to' => $rowAnuncio['empresaMail'],
                            'nombrePuesto' => $rowAnuncio['AnuncioPuesto'],
                            'urlAviso' => SITE_URL.$url,
                            'logo' => $this->view->E()->getElementLogos($rowAnuncio['AnuncioLogo']),
                            'alt' => $rowAnuncio['nombreEmpresa']
                        );
                        $this->_helper->mail->avisoPublicado($dataEmail);
                    }
                    $this->_redirect('/empresa/comprar-aviso/pago-satisfactorio/compra/'.$compraId);
                    break;
                case $this->_config->tarifas->impresolineal:

                    if ($formImpresoLineal->isValid($postData)) {
                        $nameimg     = $this->_helper->UtilFiles->_renameFileImg($formImpresoLineal,
                            'path_foto');
                        $dataImpreso = array();
                        foreach ($postData["fecha_impreso"] as $key => $value) {
                            $exp           = explode('|', $value);
                            $f             = explode('-', $exp[1]);
                            $fh            = $f[2].'-'.$this->view->Util()->valueImpreso($f[1]).'-'.$this->view->Util()->valueImpreso($f[0]); //$f[0];
                            $dataImpreso[] = array(
                                'fh_impreso' => $fh,
                                'destaque' => ($exp[0] == '1') ? true : false
                            );
                        }
                        $avisoImpreso        = new Application_Model_AnuncioImpreso();
                        $avisoImpresoDetalle = new Application_Model_AnuncioImpresoDetalle();
                        $avisoId             = $aviso;
                        $cantf               = 0;
                        $cantdp              = false;
                        $cantdh              = false;
                        if (!empty($postData["path_foto"])) {
                            $cantf = 1;
                        }
                        if (isset($postData["dp"])) {
                            if ($postData["dp"] == 1) {
                                $cantdp = true;
                            }
                        }
                        if (isset($postData["dh"])) {
                            if ($postData["dh"] == 1) {
                                $cantdh = true;
                            }
                        }

                        $avisoImpresoId = $avisoImpreso->insert(
                            array(
                                'id_empresa' => $this->auth["empresa"]['id'],
                                'texto' => $postData['texto'],
                                'fh_creacion' => date('Y-m-d H:i:s'),
                                'fh_pub_estimada' => date('Y-m-d H:i:s'),
                                'Id_Seccion' => $postData['clasificacion'],
                                'Id_SubSeccion' => $postData['subclasificacion'],
                                'Cod_Estilo' => $postData['estilo'],
                                'Cod_Color' => $postData['color'],
                                'Cod_Fondo' => $postData['Fondo'],
                                'Cant_Fotos' => $cantf,
                                'Prensiguia' => $cantdp,
                                'DiarioHoy' => $cantdh,
                                'path_img' => $nameimg,
                                'estado' => Application_Model_AnuncioWeb::ESTADO_REGISTRADO
                            )
                        );
                        foreach ($dataImpreso as $key => $value) {
                            $avisoImpresoDetalle->insert(array(
                                'id_anuncio_impreso' => $avisoImpresoId,
                                'codigo' => 'fecha',
                                'fh_impreso' => $value['fh_impreso'],
                                'destaque' => $value['destaque'],
                            ));
                        }
                        $where = $this->_aw->getAdapter()
                            ->quoteInto('id = ?', $avisoId);
                        $this->_aw->update(
                            array(
                            'id_anuncio_impreso' => $avisoImpresoId
                            ), $where
                        );
                    } else {
                        $this->_redirect('/empresa/publica-aviso/paso1/aviso/564'.$aviso);
                    }

                    //promo destaque
                    if ($postData['tarifa'] == 1) {
                        if ($postData['precio_ai'] > 12 && $postData['precio_ai']
                            <= 24) {
                            //destaque plata
                            $postData['tarifa'] = 171;
                            $where              = $this->_aw->getAdapter()
                                ->quoteInto('id = ?', $avisoId);
                            $this->_aw->update(
                                array(
                                'medio_pago' => Application_Model_AnuncioWeb::MEDIO_PAGO_BONIFICADO
                                ), $where
                            );
                        }
                        if ($postData['precio_ai'] > 24) {
                            //destaque oro
                            $postData['tarifa'] = 172;
                            $where              = $this->_aw->getAdapter()
                                ->quoteInto('id = ?', $avisoId);
                            $this->_aw->update(
                                array(
                                'medio_pago' => Application_Model_AnuncioWeb::MEDIO_PAGO_BONIFICADO
                                ), $where
                            );
                        }
                    }
                    $this->_helper->Aviso->addDestaquesWeb(
                        $postData['tarifa'], $aviso
                    );
                    $this->_redirect('/empresa/publica-aviso/paso2/token/'.$encode);
                    break;
                default:
                    $this->_helper->Aviso->addDestaquesWeb(
                        $postData['tarifa'], $aviso
                    );
                    $this->_redirect('/empresa/publica-aviso/paso2/token/'.$encode);
                    break;
            }
        }

        $this->view->productos = $modelProducto->getTarifas();
        if ($isSessionAdmin) {
            //$this->view->idTarifaImpreso = 0;
        }
        $this->view->formImpresoLineal = $formImpresoLineal;
        $this->view->isauthAdmin       = $isSessionAdmin;
        //  var_dump($dataimpreso);exit;
        $this->view->data              = $dataimpreso;
        $this->view->idAviso           = $aviso;
    }

    public function paso2Action()
    {
        $token         = $this->_getParam('token');
        $data          = $this->_helper->JWT->decode($token);
        $avisoId       = $data->aviso;
        $tarifaId      = $data->id_tarifa;
        $sessionAdmin  = new Zend_Session_Namespace('admin');
        $this->_aw     = new Application_Model_AnuncioWeb();
        $rowAnuncio    = $this->_aw->getDatosPagarAnuncio($avisoId);
        $modelTarifa   = new Application_Model_Tarifa();
        $modelProducto = new Application_Model_Producto();
        $_producto     = $modelTarifa->getProductoByTarifa($rowAnuncio['tarifaId']);
        $_producto_web = $modelTarifa->getDetalleDestaquesTarifa($data->tarifa);

        $formFactura            = new Application_Form_FacturacionDatos();
        $dataEmpresa['txtRuc']  = $this->auth['empresa']['ruc'];
        $dataEmpresa['txtName'] = $this->auth['empresa']['razon_social'];
        $formFactura->setDefaults($dataEmpresa);
        $formFactura->setreadonly($dataEmpresa);

        $this->view->headScript()->appendFile(
            $this->view->S(
                '/eb/js/bootstrap-select.min.js')
        );
        $_detalle_producto_impreso = array();
        if (isset($data->fecha_impreso)) {
            $totalDestaque = 0;
            $dp            = 0;
            if (isset($data->dp)) {
                $pf = 'en Prensiguia';
            };
            if (isset($data->dp) && isset($data->dh)) {
                $pf = 'en Prensiguia y Diario Hoy.';
            };
            foreach ($data->fecha_impreso as $key => $value) {
                $exp = explode('|', $value);
                if ($exp[0] == '1') {
                    $totalDestaque++;
                }
                $dp++;
            }
            $_detalle_producto_impreso = array(
                0 => "$totalDestaque días de destaque Impreso.",
                1 => "$dp días de publicación en $pf",
            );
        }

        $this->view->isSessionAdmin           = ($sessionAdmin->auth ? true : false);
        $this->view->token                    = $token;
        $this->view->producto                 = $_producto;
        $this->view->detalle_producto_web     = $_producto_web;
        $this->view->detalle_producto_impreso = $_detalle_producto_impreso;
        $this->view->dataAnuncio              = $rowAnuncio;
        $this->view->medioPublicacion         = $rowAnuncio['medioPublicacion'];
        $this->view->idtarifa                 = $tarifaId;
        $this->view->Formfacturacion          = $formFactura;
        $this->view->montoWeb                 = $data->precio_aw;
        $this->view->montoImpreso             = $data->precio_ai;
        $this->view->ruc                      = $this->auth['empresa']['ruc'];
        $this->view->razonsocial              = $this->auth['empresa']['razon_social'];
    }

    public function paso3Action()
    {
        $aw      = new Application_Model_AnuncioWeb();
        $avisoId = $this->_getParam('aviso');
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );
        $this->view->headTitle()->set(
            'Paso 3 - Complete su aviso Impreso, Publica tu aviso en AquiEmpleos'
        );
        $this->view->headMeta()->appendName(
            "Description",
            "Complete su aviso Impreso, tercer paso para la publicación de tu aviso ".
            "en aquiempleos.com. Los Clasificados de Empleos de La Prensa."
        );
        if ($avisoId == null && !$this->_getParam('id_aviso')) {
            $this->_redirect('/empresa/publica-aviso/paso1');
        }
        $anuncio                 = $aw->getAvisoById($avisoId);
        $dataAnuncio             = $aw->getDatosPagarAnuncio($avisoId);
        $this->view->dataAnuncio = $dataAnuncio;
        $modelTarifa             = new Application_Model_Tarifa();
        $this->view->producto    = $modelTarifa->getProductoByTarifa($anuncio['id_tarifa']);
        if ($anuncio['id_producto'] == '1') {
            $this->_redirect('/empresa/publica-aviso/paso4/aviso/'.$avisoId);
        }
        $config = $this->getConfig();

        $form = new Application_Form_Paso3PublicarAviso();
        //@codingStandardsIgnoreStart
        $form->id_aviso->setValue($avisoId);
        //@codingStandardsIgnoreEnd
        if ($this->_getParam('republica') != "") {
            $republica      = $this->_getParam('republica');
            $anuncioImpreso = $aw->getAnuncioImpreso($republica);
            $form->texto->setValue($anuncioImpreso['texto']);
        }
        if ($dataAnuncio['anuncioImpresoId'] != null) {
            $ai          = new Application_Model_AnuncioImpreso();
            $impresoData = $ai->getDataAnuncioImpreso(
                $dataAnuncio['anuncioImpresoId']
            );
            $form->texto->setValue($impresoData['texto']);
        }
        $this->view->form      = $form;
        $prod                  = new Application_Model_Producto();
        $beneficios            = $prod->listarBeneficios($anuncio["id_producto"]);
        $this->view->anuncio   = $anuncio;
        $this->view->npalabras = $beneficios["npalabras"];
        $this->view->moneda    = $this->_config->app->moneda;

        $d      = new Zend_Date();
        $diaPub = $d->get(Zend_Date::DAY) + $config->cierre->publicacion -
            $d->get(Zend_Date::WEEKDAY_DIGIT);
        if ($d->get(Zend_Date::WEEKDAY_DIGIT) == $config->cierre->dia) {
            if ($d->get(Zend_Date::HOUR) >= $config->cierre->hora) {
                $diaPub = $diaPub + 7;
            }
        } elseif ($d->get(Zend_Date::WEEKDAY_DIGIT) > $config->cierre->dia) {
            $diaPub = $diaPub + 7;
        }
        $d->set($diaPub, Zend_Date::DAY);
        $this->view->diaPub = ucfirst($d->get(Zend_Date::DATE_FULL));

        if ($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();
            $form->agregarValidadorPalabras($beneficios["npalabras"]);
            if ($form->isValid($postData)) {
                $avisoImpreso = new Application_Model_AnuncioImpreso();
                if ($dataAnuncio['anuncioImpresoId'] == null) {
                    $avisoImpresoId = $avisoImpreso->insert(
                        array(
                            'id_empresa' => $anuncio['id_empresa'],
                            'texto' => $postData['texto'],
                            'fh_creacion' => date('Y-m-d H:i:s'),
                            'fh_pub_estimada' => $d->get(Zend_Date::DATETIME)
                        )
                    );
                    $where          = $aw->getAdapter()
                        ->quoteInto('id = ?', $avisoId);
                    $aw->update(
                        array(
                        'id_anuncio_impreso' => $avisoImpresoId
                        ), $where
                    );
                } else {
                    $where = $aw->getAdapter()
                        ->quoteInto('id = ?', $dataAnuncio['anuncioImpresoId']);
                    $avisoImpreso->update(
                        array(
                        'texto' => $postData['texto']
                        ), $where
                    );
                }

                $this->_redirect('/empresa/publica-aviso/paso4/aviso/'.$avisoId);
            }
        }
    }

    public function paso4Action()
    {
        $this->_redirect('/');

        $avisoId              = $this->_getParam('aviso');
        $sessionAdmin         = new Zend_Session_Namespace('admin');
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
            "Pague su Aviso, cuarto paso para la publicación de tu aviso en aquiempleos.com.".
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
        $this->_aw  = new Application_Model_AnuncioWeb();
        $rowAnuncio = $this->_aw->getDatosPagarAnuncio($avisoId);
//        if(!isset($this->auth['empresa']['membresia_info']['membresia'])&&$rowAnuncio['tarifaId']==Application_Model_Tarifa::GRATUITA)
//        {
//            $this->getMessenger()->error('Se requiere tener una membresia para activar un aviso gratuito');
//            $this->_redirect('/empresa/mis-procesos');
//        }
        if ($rowAnuncio['tarifaId'] == 158) {
            $this->getMessenger()->error('El aviso Web destacado ya no esta disponible para su publicación');
            $this->_redirect('/empresa/mis-procesos');
        }
        $modelTarifa          = new Application_Model_Tarifa();
        $this->view->producto = $modelTarifa->getProductoByTarifa($rowAnuncio['tarifaId']);

        $medioPublicacion = $rowAnuncio['medioPublicacion'];

        if ($rowAnuncio['medioPublicacion'] == 'aptitus y talan') {
            $medioPublicacion = 'combo';
        }

        if ((int) $rowAnuncio['tarifaPrecio'] <= 0) {
            $rowAnuncio                = $this->_aw->getDatosGenerarCompra($avisoId);
            $rowAnuncio['totalPrecio'] = 0;
            $rowAnuncio['tipoDoc']     = '';
            $rowAnuncio['tipoPago']    = 'gratuito';
            $usuario                   = $this->auth['usuario'];
            $rowAnuncio['usuarioId']   = $usuario->id;
            $compraId                  = $this->_helper->aviso->generarCompraAnuncio($rowAnuncio);
            $this->_helper->aviso->confirmarCompraAviso($compraId, 0);
            $this->_redirect('/empresa/comprar-aviso/pago-satisfactorio/compra/'.$compraId);
        }

        if ($rowAnuncio['estadoCompra'] == 'pagado') {
            $this->_redirect('/empresa/publica-aviso/paso1');
        }

        $cierre               = $this->config->cierre->toArray();
        $fecImpre             = new Zend_Date();
        $fecImpre->setLocale(Zend_Locale::ZFDEFAULT);
        $fecImpre->set($cierre[$medioPublicacion]['dia'],
            Zend_Date::WEEKDAY_DIGIT);
        $fecImpre->set($cierre[$medioPublicacion]['hora'], Zend_Date::HOUR);
        $fecImpre->set(0, Zend_Date::MINUTE);
        $fecImpre->set(0, Zend_Date::SECOND);
        $this->view->fhCierre = $fecImpre->toString('EEEE d MMMM / h:m a');
        $fecCierre            = clone $fecImpre;
        $now                  = date('Y-m-d H:i:s');
        $fecImpre->set(0, Zend_Date::HOUR);
        if ($fecCierre->isEarlier($now, 'YYYY-MM-dd h:m:s')) {
            $fecCierre->add(7, Zend_Date::DAY);
            $fecImpre->add(7, Zend_Date::DAY);
        }

        //Actualizar ente en APT si en Adecsys es diferente, siempre y cuando exista en ADECSYS Y EN APT
        $adecsysValida = $this->_helper->getHelper('AdecsysValida');
        $tipoDoc       = Application_Model_AdecsysEnte::DOCUMENTO_RUC;
        $ruc           = $this->auth['empresa']['ruc'];
        $adecsysValida->compareEnte($tipoDoc, $ruc);

        $this->view->fhCierre = $this->_helper->Aviso->getFechaCierreImpresoByPaquete($medioPublicacion);

        if ($cierre[$medioPublicacion]['semanaActual'] == 0) {
            $fecImpre->add(7, Zend_Date::DAY);
        }
        $fecImpre->set($cierre[$medioPublicacion]['diaPublicacion'],
            Zend_Date::WEEKDAY_DIGIT);
        $this->view->fechaImpreso = $fecImpre->toString('YYYY-MM-dd');

        $fechaVencimiento           = new Zend_Date($rowAnuncio['fechaCreacion'],
            'YYYY-MM-dd', Zend_Locale::ZFDEFAULT);
        $fechaVencimiento->add('15', Zend_Date::DAY);
        $this->view->fechaCierreWeb = $fechaVencimiento->toString('YYYY-MM-dd');

        $rowAnuncio['tipo_paquete']        = strtoupper(trim(str_replace('Clasificado',
                    '', $rowAnuncio['nombreProducto'])));
        $rowAnuncio['nombre_tipo_paquete'] = 'Clasificado'.strtoupper(trim(str_replace('Clasificado',
                        '', $rowAnuncio['nombreProducto'])));
        $rowAnuncio['data-number']         = str_replace(",", " ",
            $rowAnuncio['tarifaPrecio']);

        $this->view->dataAnuncio      = $rowAnuncio;
        $this->view->medioPublicacion = $rowAnuncio['medioPublicacion'];

        $validaRUC                     = $this->_helper->Aviso->validarDocumentoAdecsys(Application_Model_Compra::RUC,
            $ruc);
        $formFactura                   = new Application_Form_FacturacionDatos();
        $sessionDatosPasarela->factura = $validaRUC;

        if (empty($validaRUC)) {
            $this->view->val_ruc     = 0;
            $this->view->ruc         = $this->auth['empresa']['ruc'];
            $this->view->razonsocial = $this->auth['empresa']['razon_social'];
            $dataEmpresa['txtRuc']   = $ruc;
            $dataEmpresa['txtName']  = $this->auth['empresa']['razon_social'];
            $formFactura->setDefaults($dataEmpresa);
            $formFactura->setreadonly($dataEmpresa);
        } else {
            $formFactura->setDefaults($validaRUC);
            $formFactura->setreadonly($validaRUC);
        }
        $this->view->Formfacturacion = $formFactura;

        $this->view->isSessionAdmin = ($sessionAdmin->auth ? true : false);
        $tipo                       = $rowAnuncio['tipo'];
        $aviso                      = $rowAnuncio['medioPublicacion'];
        if ($aviso == "aptitus y talan") {
            $tipos = explode(' y ', $aviso);
            $aviso = $tipos[0];
        }
        $DescuentoAviso         = $this->_config->extracargosAvisos->toArray();
        $descuentototal         = isset($DescuentoAviso[$tipo][$aviso]["descuentos"]["valor"])
                ? $DescuentoAviso[$tipo][$aviso]["descuentos"]["valor"] : '';
        $this->view->Descuentos = ($this->view->isSessionAdmin) ? $descuentototal
                : array();
    }

    public function cacularImpresoWebAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $data          = array(
            'status' => false,
            'msg' => 'Registro no encontrado',
            'data' => array()
        );
        $idtarifa      = $this->_getParam('IdTarifa', false);
        $codPosicion   = '00';
        $invertido     = false;
        $internacional = false;
        $tipoaviso     = 'C';
        if ($idtarifa) {

        }
        $foto = $this->_getParam('foto', false);
        $cnf  = 0;
        if ($foto == 'true') {
            $cnf = (int) 1;
        }
        $dp = false;
        $dh = false;
        if ($this->_getParam('dh', false) == 'true') {
            $dh = true;
        }
        if ($this->_getParam('dp', false) == 'true') {
            $dp = true;
        }
        $tdia = 0;
        if ($this->_getParam('tdias', false)) {
            $tdia = $this->_getParam('tdias', false);
        }
        $ddia = 0;
        if ($this->_getParam('tdestacados', false)) {
            $ddia = $this->_getParam('tdias', false);
        }
        try {
            $params         = array(
                'tipoAnuncio' => $tipoaviso,
                'codEstilo' => $this->_getParam('estilo', false),
                'codColor' => $this->_getParam('color', false),
                'codFondo' => $this->_getParam('Fondo', false),
                'codPosicion' => $codPosicion,
                'invertido' => $invertido,
                'internacional' => $internacional,
                'prensiguia' => $dp,
                'diariohoy' => $dh,
                'texto' => $this->_getParam('texto', false),
                'alto' => 0,
                'ancho' => 0,
                'diasPublicados' => $tdia,
                'diasDestacado' => $ddia,
                'cantFotos' => $cnf,
            );
            $rsWs           = $this->_helper->WebServiceNicaraguaConsulta->consultar($params);
            $data['status'] = true;
            $data['msg']    = 'Se encontro registro';
            $data['data']   = $this->view->Util()->cTotalSinIv($rsWs->Base);
        } catch (Exception $exc) {
            $this->_response->appendBody(Zend_Json::encode($data));
        }
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function cacularAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $monto = $this->_getParam('monto', false);
        $data  = array(
            'status' => false,
            'msg' => 'Registro no encontrado',
            'data' => $this->view->Util()->cTotalConIv($monto)
        );
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function cacularConIvaAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $monto = $this->_getParam('monto', false);
        $data  = array(
            'status' => false,
            'msg' => 'Registro no encontrado',
            'data' => $this->view->Util()->cTotalConIv($monto)
        );
        $this->_response->appendBody(Zend_Json::encode($data));
    }
}
