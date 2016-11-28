<?php


class Admin_PublicarAvisoController
    extends App_Controller_Action_Admin
{

    protected $_avisoId;
    protected $_datosAviso;

    public function init()
    {
        parent::init();

        $containerHead = $this->view->headLink()->getContainer();

        unset($containerHead[count($containerHead) - 1]);

        $this->view->headLink()->appendStylesheet(
            $this->view->S('/css/empresa/empresa.layout.css'), 'all'
        );
        
        $this->view->headLink()->appendStylesheet(
            $this->view->S('/css/empresa/empresa.class.css'), 'all'
        );
    }

    public function indexAction()
    {
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );
        $this->view->rol = $this->auth['usuario']->rol;
        //$this->_redirect('admin/publicar-aviso/paso1');
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/administrador/empresa.aviso.index.js') 
        );
    }

    public function paso1Action()
    {
        $config = Zend_Registry::get("config");
        $session = $this->getSession();
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );

        $fchPubsImp['aptitus'] = $this->_helper->Aviso->getFechaPublicacionImpresoByPaquete('aptitus');
        $fchPubsImp['talan'] = $this->_helper->Aviso->getFechaPublicacionImpresoByPaquete('talan');
        $fchPubsImp['combo'] = $this->_helper->Aviso->getFechaPublicacionImpresoByPaquete('combo');
        $this->view->fchPubsImp = $fchPubsImp;

        $fchCierreImp['aptitus'] = $this->_helper->Aviso->getFechaCierreImpresoByPaquete('aptitus');
        $fchCierreImp['talan'] = $this->_helper->Aviso->getFechaCierreImpresoByPaquete('talan');
        $fchCierreImp['combo'] = $this->_helper->Aviso->getFechaCierreImpresoByPaquete('combo');
        $this->view->fchCierreImp = $fchCierreImp;

        $this->view->slide = $this->_getParam('slide', 1);
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/empresa/empresa.aviso.paso1.js')
        );
        $modelProducto = new Application_Model_Producto();
        for ($i = 1; $i < 4; $i++) {
            $idProd = 1 + $i;
            $arrayClasificado[] = $modelProducto->getInformacionAvisoClasificado($idProd);
        }

        $this->view->arrayClasificado = $arrayClasificado;

        if ($this->getRequest()->isPost()) {
            $dataPost = $this->_getAllParams();
            $allParams = $this->_getAllParams();
            $session->producto = $dataPost['id_tarifa'];

            $this->_redirect(
                '/admin/publicar-aviso/paso2/tarifa/' .
                $dataPost['id_tarifa']
            );
        }
        $this->view->rol = $this->auth['usuario']->rol;
        $this->view->moneda = $config->app->moneda;
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

        $this->view->tarifa = $tarifaId;


        $modelProducto = new Application_Model_Producto();
        $idProd = $modelProducto->getIdProductoXTarifa($tarifaId);

        $value = $this->_helper->getHelper('Aviso')
            ->accesoPublicarAvisoAdmin($idProd, $this->auth['usuario']->rol);

        if ($value != true) {
            $this->_redirect('admin/publicar-aviso/');
        }

        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );
        if ($this->_getParam('extiende') != "") {
            $extiende = $this->_getParam('extiende');
        }
        if ($this->_getParam('republica') != "") {
            $republica = $this->_getParam('republica');
        }
        if ($tarifaId == null && !$this->_getParam('id_producto')) {
            $this->_redirect('/admin/publicar-aviso/index');
        }
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/empresa/empresa.aviso.paso2.js')
        );
        /* $this->view->headScript()
          ->appendFile($this->mediaUrl.'/js/empresa/admin.aviso.paso4.js');
         */
        $config = Zend_Registry::get("config");
        //@codingStandardsIgnoreStart
        $this->view->numPalabraPuesto = $config->avisopaso2->puestonumeropalabra;
        $this->view->numPalabraOtroNombre = $config->avisopaso2->mostrarnombrenumeropalabra;
        //@codingStandardsIgnoreEnd
        $this->view->modulo = $this->_request->getModuleName();
        $this->view->nombreComercial = $session->empresaBusqueda['nombre_comercial'];

        $frmUbigeo = new Application_Form_Ubigeo();

        $modelEmpresa = new Application_Model_Empresa();
        $idEmpresa = $session->empresaBusqueda['idempresa'];
        $arrayEmpresa = $modelEmpresa->datosParaEnteAdecsys($idEmpresa);
        $frmUbigeo->detalleUbigeo($arrayEmpresa['ubigeoId']);

        $formDatos = new Application_Form_Paso2PublicarAviso();
        //@codingStandardsIgnoreStart
        $formDatos->id_tarifa->setValue($tarifaId);
        //@codingStandardsIgnoreEnd

        if ($tarifaId == 1) {
            $formDatos->removeTipoPuesto();
        }

        $baseFormEstudio = new Application_Form_Paso2EstudioPublicar();
        $managerEstudio =
            new App_Form_Manager($baseFormEstudio, 'managerEstudio');

        $baseFormOtroEstudio = new Application_Form_Paso2OtroEstudioPublicar();
        $managerOtroEstudio =
            new App_Form_Manager($baseFormOtroEstudio, 'managerOtroEstudio');

        $baseFormExperiencia = new Application_Form_Paso2ExperienciaPublicar();
        $managerExperiencia =
            new App_Form_Manager($baseFormExperiencia, 'managerExperiencia');

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
            
            $dFunciones = $postData["funciones"];
            $dResponsabilidades = $postData["responsabilidades"];

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
                $avisoHelper = $genPassword = $this->_helper->getHelper('Aviso');
                $util = $this->_helper->getHelper('Util');
                $postData['id_ubigeo'] = $util->getUbigeo($postData);
                $config = $this->getConfig();
                if ($tarifaId == 1) {
                    $prioridad = (empty($config->prioridad->anuncio->soloweb)) ?
                        6 : $config->prioridad->anuncio->soloweb;
                    $postData['prioridad'] = $prioridad;
                } else {
//                    $prioridad = (empty($config->prioridad->anuncio->clasificado))
//                            ?
//                        5 : $config->prioridad->anuncio->clasificado;
//                    $postData['prioridad'] = $prioridad;
                    $anuncioWebModel = new Application_Model_AnuncioWeb;
                    $dataPrioridad = $anuncioWebModel->prioridadAviso('clasificado', $idEmpresa);
                    $prioridad = $dataPrioridad['prioridad'];
                    $postData['prioridad'] = $prioridad;
                }
                if (isset($extiende)) {
                    $aw = new Application_Model_AnuncioWeb();
                    $origen = $aw->getAvisoExtendido($extiende);
                    $extiende = $origen['extiende_a'];
                    $avisoId = $avisoHelper->_insertarNuevoPuesto(
                        $postData, $extiende, $session->empresaBusqueda
                    );
                } else {
                    $avisoId = $avisoHelper->_insertarNuevoPuesto(
                        $postData, null, $session->empresaBusqueda
                    );
                }
                $avisoHelper->_insertarPreguntas($managerPregunta, $idEmpresa);
                $avisoHelper->_insertarEstudios($managerEstudio);
                $avisoHelper->_insertarOtrosEstudios($managerOtroEstudio);
                $avisoHelper->_insertarExperiencia($managerExperiencia);
                $avisoHelper->_insertarIdiomas($managerIdioma);
                $avisoHelper->_insertarPrograma($managerPrograma);
                $params = "";
                if ($extiende != "") {
                    $params .= "/extiende/" . $extiende;
                }
                if ($republica != "") {
                    $params .= "/republica/" . $republica;
                }
                $this->_redirect('/admin/publicar-aviso/paso3/aviso/' . $avisoId . $params);
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
//                $arrExp = explode(',', $postData['managerExperiencia']);
//                foreach($arrExp as $index)
//                    $managerExperiencia->removeForm($index);
//                $arrExp = explode(',', $postData['managerEstudio']);
//                foreach($arrExp as $index)
//                    $managerEstudio->removeForm($index);
//                $arrExp = explode(',', $postData['managerIdioma']);
//                foreach($arrExp as $index)
//                    $managerIdioma->removeForm($index);
//                $arrExp = explode(',', $postData['managerPrograma']);
//                foreach($arrExp as $index)
//                    $managerPrograma->removeForm($index);
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
                    }
                    $formEstudio[] = $form;
                }
            } else {
                $this->view->isEstudio = false;
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
                $this->view->isExperiencia = false;
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
                $this->view->isIdioma = false;
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
                $this->view->isPrograma = false;
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
        } else {
            $formEstudio[] = $managerEstudio->getForm(0);
            $formOtroEstudio[] = $managerOtroEstudio->getForm(0);
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
        $aw = new Application_Model_AnuncioWeb();
        $avisoId = $this->_getParam('aviso');
        $session = $this->getSession();
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );
        if ($avisoId == null && !$this->_getParam('id_aviso')) {
            $this->_redirect('/admin/publicar-aviso/paso1');
        }
        $anuncio = $aw->getAvisoById($avisoId);
        $this->view->dataAnuncio = $dataAnuncio = $aw->getDatosPagarAnuncio($avisoId);

        if ($anuncio['id_producto'] == '1') {
            $this->_redirect('/admin/publicar-aviso/paso4/aviso/' . $avisoId);
        }
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/administrador/admin.paso3.js')
        );

        $config = $this->getConfig();

        $form = new Application_Form_Paso3PublicarAviso();
        //@codingStandardsIgnoreStart
        $form->id_aviso->setValue($avisoId);
        //@codingStandardsIgnoreEnd
        if ($this->_getParam('republica') != "") {
            $republica = $this->_getParam('republica');
            $anuncioImpreso = $aw->getAnuncioImpreso($republica);
            $form->texto->setValue($anuncioImpreso['texto']);
        }

        if ($dataAnuncio['anuncioImpresoId'] != null) {
            $ai = new Application_Model_AnuncioImpreso();
            $impresoData = $ai->getDataAnuncioImpreso(
                $dataAnuncio['anuncioImpresoId']
            );
            $form->texto->setValue($impresoData['texto']);
        }

        $this->view->form = $form;
        $prod = new Application_Model_Producto();
        $beneficios = $prod->listarBeneficios($anuncio["id_producto"]);
        $this->view->anuncio = $anuncio;
        $this->view->npalabras = $beneficios["npalabras"];

        $d = new Zend_Date();
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
        $this->view->moneda = $config->app->moneda;
        if ($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();
            $form->agregarValidadorPalabras($beneficios["npalabras"]);
            if ($form->isValid($postData)) {
                $avisoImpreso = new Application_Model_AnuncioImpreso();
                if ($dataAnuncio['anuncioImpresoId'] == null) {
                    $avisoImpresoId = $avisoImpreso->insert(
                        array(
                            'id_empresa' => $dataAnuncio['empresaId'],
                            'texto' => $postData['texto'],
                            'fh_creacion' => date('Y-m-d H:i:s'),
                            'fh_pub_estimada' => $d->get(Zend_Date::DATETIME)
                        )
                    );

                    $where = $aw->getAdapter()
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

                $this->_redirect('/admin/publicar-aviso/paso4/aviso/' . $avisoId);
            }
        }
    }

    public function paso4Action()
    {
        $avisoId = $this->_getParam('aviso');
        if ($this->_getParam('error') == 1) {
            $this->getMessenger()->error(
                'El Pago no se procesó correctamente. Intente nuevamente en unos minutos, 
                de lo contario, consulte con el Administrador del Sistema.'
            );
        }
        if ($avisoId == null) {
            $this->_redirect('/admin/publicar-aviso/paso1');
        } else {
            $this->view->headScript()->appendFile(
                    $this->view->S(
                    '/js/administrador/admin.aviso.paso4.js')
            );
            Zend_Layout::getMvcInstance()->assign(
                'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
            );
            $this->_aw = new Application_Model_AnuncioWeb();
            $rowAnuncio = $this->_aw->getDatosPagarAnuncio($avisoId);
            if ((int) $rowAnuncio['tarifaPrecio'] <= 0) {
                $rowAnuncio = $this->_aw->getDatosGenerarCompra($avisoId);
                $rowAnuncio['totalPrecio'] = 0;
                $rowAnuncio['tipoDoc'] = '';
                $rowAnuncio['tipoPago'] = 'gratuito';
                $usuario = $this->auth['usuario'];
                //$rowAnuncio['usuarioId'] = $usuario->id;
                $compraId = $this->_helper->aviso->generarCompraAnuncio($rowAnuncio);
                $this->_helper->aviso->confirmarCompraAviso($compraId, 0);
                //$this->_redirect('/admin/comprar-aviso/pago-satisfactorio/compra/'.$compraId);
                $this->_redirect('/admin/gestion');
            }
            if ($rowAnuncio['estadoCompra'] == 'pagado') {
                $this->_redirect('/admin/publicar-aviso/paso1');
            }
            $medioPublicacion = $rowAnuncio['medioPublicacion'];
            if ($medioPublicacion == 'aptitus y talan') {
                //    $medioPublicacion = 'talan';
                $medioPublicacion = 'combo';
            }

            $this->view->fechaImpreso = $this->_helper->Aviso->getFechaPublicacionImpresoByPaquete($medioPublicacion);
            $this->view->fhCierre = $this->_helper->Aviso->getFechaCierreImpresoByPaquete($medioPublicacion);
            $this->view->dataAnuncio = $rowAnuncio;
            $this->view->medioPublicacion = $rowAnuncio['medioPublicacion'];
        }
    }

}