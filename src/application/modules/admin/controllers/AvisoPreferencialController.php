<?php

class Admin_AvisoPreferencialController extends App_Controller_Action_Admin
{
    protected $_avisoId;
    protected $_datosAviso;
    protected $_cache;
    
    //Buscamas
    private $_buscamasConsumerKey;
    private $_buscamasPublishUrl;
    private $_buscamasUrl;

    public function init()
    {
        parent::init();
        if ($this->_cache == null) {
            $this->_cache = Zend_Registry::get('cache');
        }
        $idAviso = $this->_getParam('rel', null);
        if (isset($idAviso)) {
            $this->_avisoId = $idAviso;
            $aviso = new Application_Model_AnuncioWeb();
            $this->_datosAviso = $aviso->getAvisoInfoById($this->_avisoId);
        }
        
        $this->_config = Zend_Registry::get('config');
    }

    public function indexAction()
    {
        $this->_redirect('/admin/gestion/avisos-preferenciales');
    }

    public function editarAction()
    {
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/empresa/empresa.aviso.paso2.js')
        );

        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'EditarAviso', 'class' => 'noMenu noMenuAdm')
        );
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/administrador/micuenta.admin.js')
        );
        
        $sess = $this->getSession();
        $this->view->avisoPreferencialAdminUrl =
            $this->view->url($sess->avisoPreferencialAdminUrl, 'default', false);

        $config = Zend_Registry::get("config");
        //@codingStandardsIgnoreStart
        $this->view->numPalabraPuesto = $config->avisopaso2->puestonumeropalabra;
        $this->view->numPalabraOtroNombre = $config->avisopaso2->mostrarnombrenumeropalabra;
        //@codingStandardsIgnoreEnd
        $this->view->modulo = $this->_request->getModuleName();

        $redirect = $this->_getParam('redirctGua', null);

        // ListaPreferencialAdmin
        $aviso = new Application_Model_AnuncioWeb();
        $this->view->div = $div = $this->_getParam('div', null);
        $this->view->avisoF = $idsClonados = $sess->avisosClonados;
        $this->view->avisoMax = $config->dividirAviso->numeroMaximo;
        $maximoClonados = count($idsClonados);

        if ($maximoClonados != 0) {
            $this->view->primero = $idsClonados['0']['id'];
        }

        if (isset($div) && $maximoClonados == $div) {
            foreach ($sess->avisosClonados as $avisoClon) {
                if ($this->_avisoId == $avisoClon['id']) {
                    $idAviso = $avisoClon['id'];
                }
            }

            $dataP = $this->view->dataPosicion = $aviso->getPosicionByAvisosClonados($idsClonados, $idAviso);
            $this->view->maximoAnuncios = $maximoClonados;
            $this->view->avisoWebId = $idAviso;
        }
        //
        // Duplicar
        $duplicar = $this->_getParam('dup');
        if ($duplicar != '') {
            $tipo = 'dup';
        }
        //

        $formEstudio = array();
        $formOtroEstudio = array();
        $formExperiencia = array();
        $formIdioma = array();
        $formPrograma = array();
        $formPregunta = array();

        $frmUbigeo = new Application_Form_Ubigeo();
        $formPuesto = new Application_Form_Paso2PublicarAviso(true);
        $datosAviso = $this->_datosAviso;

        $this->view->nombreComercial = $datosAviso['empresa_rs'];
        $this->view->puesto = $datosAviso['nombre_puesto'];
        $this->view->online = $datosAviso['online'];
        $this->view->idAviso = $datosAviso['id'];
        $datosAviso['id_aviso'] = $datosAviso['id'];
        if ($datosAviso['salario'] == null && $datosAviso['salario_min'] != null) {
            $datosAviso['salario'] = $datosAviso['salario_min'] . '-max';
        }
        $formPuesto->setHiddenId($datosAviso['id']);

        if ($datosAviso['chequeado'] != 0) {
            if ($datosAviso['empresa_nombre'] != $datosAviso['empresa_rs']) {
                if ($datosAviso['mostrar_empresa'] == 1) {
                    $datosAviso['otro_nombre_empresa'] = '';
                } else {
                    $datosAviso['otro_nombre_empresa'] = $datosAviso['empresa_rs'];
                }
            } else {
                $datosAviso['mostrar_empresa'] = true;
                //@codingStandardsIgnoreStart
                $datosAviso['otro_nombre_empresa'] = $config->avisopaso2->mostrarnombredefault;
                //@codingStandardsIgnoreEnd
            }
        } else {
            $datosAviso['otro_nombre_empresa'] = '';
        }

        $tarifaId = $datosAviso['id_tarifa'];

        if ($tarifaId == 1) {
            $formPuesto->removeTipoPuesto();
        }

        $frmUbigeo->detalleUbigeo($datosAviso['id_ubigeo']);

        $formPuesto->setDefaults($datosAviso);

        $baseFormEstudio = new Application_Form_Paso2EstudioPublicar(true);
        $managerEstudio =
            new App_Form_Manager($baseFormEstudio, 'managerEstudio');

        $baseFormOtroEstudio = new Application_Form_Paso2OtroEstudioPublicar(true);
        $managerOtroEstudio = 
            new App_Form_Manager($baseFormOtroEstudio, 'managerOtroEstudio');

        $baseFormExperiencia = new Application_Form_Paso2ExperienciaPublicar(true);
        $managerExperiencia =
            new App_Form_Manager($baseFormExperiencia, 'managerExperiencia');

        $baseFormIdioma = new Application_Form_Paso2Idioma(true);
        $managerIdioma =
            new App_Form_Manager($baseFormIdioma, 'managerIdioma');

        $baseFormPrograma = new Application_Form_Paso2Programa(true);
        $managerPrograma =
            new App_Form_Manager($baseFormPrograma, 'managerPrograma');

        $baseFormPregunta = new Application_Form_Paso2PreguntaPublicar(true);
        $managerPregunta =
            new App_Form_Manager($baseFormPregunta, 'managerPregunta');

        $datosAvisoEstudio = $aviso->getEstudioInfoByAnuncio($this->_avisoId);

        $auth = $this->auth;
        $auth['empresa']['nombre_comercial'] = $datosAviso['empresa_nombre'];
        Zend_Layout::getMvcInstance()->assign(
            'auth', $auth
        );
                $falla = 0;
                
        //exit("llego");
        
        if ($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();

            $dFunciones = $postData["funciones"];
            $dResponsabilidades = $postData["responsabilidades"];

            //@codingStandardsIgnoreStart
            $postData["funciones"] =
                preg_replace($config->avisopaso2->expresionregular, '', $postData["funciones"]);
            $postData["responsabilidades"] =
                preg_replace($config->avisopaso2->expresionregular, '', $postData["responsabilidades"]);
            //@codingStandardsIgnoreEnd

            $postData["funciones"] = str_replace("@", "", $postData["funciones"]);
            $postData["responsabilidades"] = str_replace("@", "", $postData["responsabilidades"]);

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

            if ($formPuesto->isValid($postData) &&
                $managerEstudio->isValid($postData) &&
                $managerOtroEstudio->isValid($postData) &&
                $managerExperiencia->isValid($postData) &&
                $managerIdioma->isValid($postData) &&
                $managerPrograma->isValid($postData) &&
                $frmUbigeo->isValid($postData)
            ) {
                $util = $this->_helper->getHelper('Util');
                $idUbigeo = $util->getUbigeo($postData);
                $avisoHelper = $genPassword = $this->_helper->getHelper('Aviso');
                $avisoHelper->_actualizarDatosPuesto(
                    $formPuesto, $this->_avisoId, $datosAviso['id_empresa'], $idUbigeo
                );
                $avisoHelper->_actualizarEstudios($managerEstudio, $this->_avisoId);
                $avisoHelper->_actualizarOtrosEstudios($managerOtroEstudio, $this->_avisoId);
                $avisoHelper->_actualizarExperiencas($managerExperiencia, $this->_avisoId);
                $avisoHelper->_actualizarIdioma($managerIdioma, $this->_avisoId);
                $avisoHelper->_actualizarPrograma($managerPrograma, $this->_avisoId);
                $avisoHelper->_actualizarPregunta(
                    $managerPregunta, $this->_avisoId, $datosAviso['id_empresa']
                );
                
                //Actualiza Match a Postulantes
                $mPostulacion = new Application_Model_Postulacion;
                $mPostulacion->actualizarMatchPostulantes($this->_avisoId);
                
                $avisoHelper->getSolarAviso()->addAvisoSolr($this->_avisoId);
                //exec("curl -X POST -d 'api_key=".$this->_buscamasConsumerKey."&nid=".$this->_avisoId."&site=".$this->_buscamasUrl."' ".$this->_buscamasPublishUrl);

                if ($duplicar == '') {
                    $this->getMessenger()->success('El aviso se modificó con éxito.');
                    $count = 0;
                    if (isset($div)) {
                        foreach ($sess->avisosClonados as $avisoClon) {
                            if ($this->_avisoId == $avisoClon['id']) {
                                $sigId = $sess->avisosClonados[$count + 1]['id'];
                                if (isset($redirect)) {
                                    //$this->_redirect($redirect);
                                }
                                if (isset($sigId)) {
                                    $this->_redirect(
                                        '/admin/aviso-preferencial/editar/rel/' . $sigId . '/div/' .
                                        $maximoClonados
                                    );
                                } else {
                                    $this->_redirect(
                                        '/admin/aviso-preferencial/editar/rel/' . $avisoClon['id'] .
                                        '/div/' . $maximoClonados
                                    );
                                }
                            }
                            $count++;
                        }
                    } else {
                        if (isset($redirect)) {
                            //$this->_redirect($redirect);
                        } else {
                            $this->_redirect('/admin/aviso-preferencial/editar/rel/' . $this->_avisoId);
                        }
                    }
                } else {
                    $this->getMessenger()->success('El aviso se modificó y se generó nuevo aviso con éxito.');
                    
                    $avisoHelper->getSolarAviso()->addAvisoSolr($this->_avisoId);
                    //exec("curl -X POST -d 'api_key=".$this->_buscamasConsumerKey."&nid=".$this->_avisoId."&site=".$this->_buscamasUrl."' ".$this->_buscamasPublishUrl);
                    
                    $logo = $aviso->obtenerLogo($this->_avisoId);
                    $whereLogo = $aviso->getAdapter()->quoteInto('id = ?', $this->_avisoId);
                    $aviso->update(array('logo' => $logo), $whereLogo);

                    $idDelDuplicado = Array();
                    if ($maximoClonados == 0) {
                        $idDelDuplicado['0'] = array('id' => $this->_avisoId, 'tipo' => $tipo);
                        $sess->avisosClonados = $idDelDuplicado;
                    }

                    $slugFilter = new App_Filter_Slug();
                    $_tu = new Application_Model_TempUrlId();
                    $modelAwd = new Application_Model_AnuncioWebDetalle();

                    $arrayNAw = $aviso->fetchRow($aviso->getAdapter()->quoteInto('id = ?', $this->_avisoId))->toArray();

                    $arrayNAw['chequeado'] = 0;
                    $arrayNAw['original'] = 0;
                    $arrayNAw['url_id'] = $_tu->popUrlId();
                    $arrayNAw['fh_clonacion'] = date('Y-m-d H:i:s');

                    unset($arrayNAw['id']);

                    $nuevoAvisoId = $aviso->insert($arrayNAw);

                    $where = $this->getAdapter()->quoteInto('id = ?', $nuevoAvisoId);
                    $aviso->update(array('extiende_a' => $nuevoAvisoId), $where);

                    $detalle = $modelAwd->getDetalle($this->_avisoId);
                    foreach ($detalle as $data) {
                        $id = array('id_anuncio_web' => $nuevoAvisoId);
                        $detalleCompleto = array_merge($id, $data);
                        $modelAwd->insert($detalleCompleto);
                    }

                    $dataEstudio = $aviso->getEstudioInfoByAnuncio($this->_avisoId);
                    $anuncioEstudio = new Application_Model_AnuncioEstudio();
                    foreach ($dataEstudio as $estudio) {
                        $anuncioEstudio->insert(
                            array(
                                'id_anuncio_web' => $nuevoAvisoId,
                                'id_nivel_estudio' => $estudio['id_nivel_estudio'],
                                'id_nivel_estudio_tipo' => $estudio['id_nivel_estudio_tipo'],
                                'id_carrera' => $estudio['id_carrera']
                            )
                        );
                    }
                    $dataOtroEstudio = $aviso->getOtroEstudioInfoByAnuncio($this->_avisoId);
                    foreach ($dataOtroEstudio as $otroEstudio) {
                        $anuncioEstudio->insert(
                            array(
                                'id_anuncio_web' => $nuevoAvisoId,
                                'id_nivel_estudio' => $otroEstudio['id_nivel_estudio'],
                                'id_nivel_estudio_tipo' => $otroEstudio['id_nivel_estudio_tipo'],
                                'id_carrera' => $otroEstudio['id_carrera'],
                                'otra_carrera' => $otroEstudio['otra_carrera']
                            )
                        );
                    }
                    $dataExperiencia = $aviso->getExperienciaInfoByAnuncio($this->_avisoId);
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
                    $dataIdioma = $aviso->getIdiomaInfoByAnuncio($this->_avisoId);
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
                    $dataPrograma = $aviso->getProgramaInfoByAnuncio($this->_avisoId);
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
                    $dataPregunta = $aviso->getPreguntaInfoByAnuncio($this->_avisoId);
                    if (count($dataPregunta) > 0) {
                        $cuestionario = new Application_Model_Cuestionario();
                        $cuestionarioId = $cuestionario->insert(
                            array(
                                'id_empresa' => $datosAviso['id_empresa'],
                                'id_anuncio_web' => $nuevoAvisoId,
                                'nombre' =>
                                'Cuestionario de la empresa ' . $datosAviso['empresa_nombre']
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

                    $count = 0;
                    $idsClonados = array();
                    foreach ($sess->avisosClonados as $avisoClon) {
                        $idsClonados[$count] = $avisoClon;
                        if ($avisoClon['id'] == $this->_avisoId) {
                            $count++;
                            $idsClonados[$count] = array('id' => $nuevoAvisoId, 'tipo' => $tipo);
                        }
                        $count++;
                    }
                    $sess->avisosClonados = $idsClonados;

                    $this->_redirect(
                        '/admin/aviso-preferencial/editar/rel/' . $nuevoAvisoId .
                        '/div/' . count($idsClonados) . '#listubi'
                    );
                }
            }//else{
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
                $falla = 1;
            //}
        } else {
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
                        $form->getElement('id_carrera')->setValue($d['id_carrera']);
                        $form->getElement('otra_carrera')->setValue($d['otra_carrera']);
                        $form->setElementNivelEstudio($d['id_nivel_estudio']);
                        //$form->setElementCarrera($d['id_tipo_carrera']);
                        $form->getElement('id_nivel_estudio_tipo')->setValue($d['id_nivel_estudio_tipo']);
                    } else {
                        $form->getElement('id_tipo_carrera')->setAttrib("disabled", "disabled");
                        $form->getElement('id_carrera')->setAttrib("disabled", "disabled");
                    }
                     $d['id_tipo_carrera']=$idTipoCarrera;
                    $form->setHiddenId($d['id']);
                    $formEstudio[] = $form;
                }
            }// else {
                
                //$this->view->isEstudio = true;
                //$this->view->isEditarEstudio = null;
                $form = $managerEstudio->getForm($i++);
                $formEstudio[] = $form;
            //}

            $datosAvisoOtroEstudio = $aviso->getOtroEstudioInfoByAnuncio($this->_avisoId);

            $i = 0;
            if (count($datosAvisoOtroEstudio) > 0) {
                $this->view->isOtroEstudio = true;
                $this->view->isEditarOtroEstudio = null;
                foreach ($datosAvisoOtroEstudio as $d) {
                    $form = $managerOtroEstudio->getForm($i++, $d);
                    $form->setHiddenId($d['id']);
                    $formOtroEstudio[] = $form;
                }
            }// else {
                //$this->view->isOtroEstudio = false;
                $form = $managerOtroEstudio->getForm($i++);
                $formOtroEstudio[] = $form;
            //}

            $datosAvisoExperiencia =
                $aviso->getExperienciaInfoByAnuncio($this->_avisoId);

            $i = 0;
            if (count($datosAvisoExperiencia) > 0) {
                $this->view->isExperiencia = true;
                $this->view->isEditarExperiencia = null;
                foreach ($datosAvisoExperiencia as $d) {
                    $form = $managerExperiencia->getForm($i++, $d);
                    $form->setHiddenId($d['id']);
                    $formExperiencia[] = $form;
                }
            }// else {
                //$this->view->isExperiencia = true;
                //$this->view->isEditarExperiencia = null;
                $form = $managerExperiencia->getForm($i++);
                $formExperiencia[] = $form;
            //}

            $datosAvisoIdioma = $aviso->getIdiomaInfoByAnuncio($this->_avisoId);

            $i = 0;
            if (count($datosAvisoIdioma) > 0) {
                $this->view->isIdioma = true;
                $this->view->isEditarIdioma = null;
                foreach ($datosAvisoIdioma as $d) {
                    $form = $managerIdioma->getForm($i++, $d);
                    $form->setHiddenId($d['id']);
                    $baseFormIdioma->addValidatorsIdioma();
                    $formIdioma[] = $form;
                }
            }// else {
                //$this->view->isIdioma = true;
                //$this->view->isEditarIdioma = null;
                $form = $managerIdioma->getForm($i++);
                $formIdioma[] = $form;
            //}

            $datosAvisoPrograma = $aviso->getProgramaInfoByAnuncio($this->_avisoId);

            $i = 0;
            if (count($datosAvisoPrograma) > 0) {
                $this->view->isPrograma = true;
                $this->view->isEditarPrograma = null;
                foreach ($datosAvisoPrograma as $d) {
                    $form = $managerPrograma->getForm($i++, $d);
                    $baseFormPrograma->addValidatorsPrograma();
                    $form->setHiddenId($d['id']);
                    $formPrograma[] = $form;
                }
            }// else {
                //$this->view->isPrograma = true;
                //$this->view->isEditarPrograma = null;
                $form = $managerPrograma->getForm($i++);
                $formPrograma[] = $form;
            //}

            $datosAvisoPregunta = $aviso->getPreguntaInfoByAnuncio($this->_avisoId);

            $i = 0;
            if (count($datosAvisoPregunta) > 0) {
                $this->view->isPregunta = true;
                $this->view->isEditarPregunta = null;
                foreach ($datosAvisoPregunta as $d) {
                    $form = $managerPregunta->getForm($i++, $d);
                    $form->setHiddenId($d['id']);
                    $formPregunta[] = $form;
                }
            }// else {
                //$this->view->isPregunta = null;
                //$this->view->isEditarPregunta = null;
                $form = $managerPregunta->getForm($i++);
                $formPregunta[] = $form;
            //}
        }

        $this->view->form = $formPuesto;

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
            $this->view->falla = $falla;
        $this->view->frmUbigeo = $frmUbigeo;
    }

    public function borrarExperienciaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idExperiencia = $this->_getParam('id', false);
        if ($idExperiencia) {
            $experiencia = new Application_Model_AnuncioExperiencia();
            $where = array('id=?' => $idExperiencia);
            $r = (bool) $experiencia->delete($where);
        } else {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo eliminar.'
            );
        }
        if (isset($r) && $r == true) {
            $data = array(
                'status' => 'exito',
                'msg' => 'Se elimino con exito.'
            );
        } else {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo eliminar.'
            );
        }
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarEstudioAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idEstudio = $this->_getParam('id', false);
        if ($idEstudio) {
            $estudio = new Application_Model_AnuncioEstudio();
            $where = array('id=?' => $idEstudio);
            $r = (bool) $estudio->delete($where);
        } else {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo eliminar.'
            );
        }
        if (isset($r) && $r == true) {
            $data = array(
                'status' => 'exito',
                'msg' => 'Se elimino con exito.'
            );
        } else {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo eliminar.'
            );
        }
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarIdiomaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idIdioma = $this->_getParam('id', false);
        if ($idIdioma) {
            $idioma = new Application_Model_AnuncioIdioma();
            $where = array('id=?' => $idIdioma);
            $r = (bool) $idioma->delete($where);
        } else {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo eliminar.'
            );
        }
        if (isset($r) && $r == true) {
            $data = array(
                'status' => 'exito',
                'msg' => 'Se elimino con exito.'
            );
        } else {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo eliminar.'
            );
        }

        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarProgramaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idPrograma = $this->_getParam('id', false);
        if ($idPrograma) {
            $programa = new Application_Model_AnuncioProgramaComputo();
            $where = array('id=?' => $idPrograma);
            $r = (bool) $programa->delete($where);
        } else {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo eliminar.'
            );
        }
        if (isset($r) && $r == true) {
            $data = array(
                'status' => 'exito',
                'msg' => 'Se elimino con exito.'
            );
        } else {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo eliminar.'
            );
        }

        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarPreguntaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idPregunta = $this->_getParam('id', false);
        if ($idPregunta) {
            $pregunta = new Application_Model_Pregunta();
            $where = array('id=?' => $idPregunta);
            $r = (bool) $pregunta->delete($where);
        } else {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo eliminar.'
            );
        }
        if (isset($r) && $r == true) {
            $data = array(
                'status' => 'exito',
                'msg' => 'Se elimino con exito.'
            );
        } else {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo eliminar.'
            );
        }

        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function dividirAvisoAction()
    {
        $this->_helper->layout->disableLayout();
        $idAviso = $this->_getParam('idAv');
        $dataStr = $this->_getParam('dataStr');

        $this->_cache->remove('AnuncioWeb_getAvisoInfoById_' . $idAviso);
        $this->_cache->remove('AnuncioWeb_getAvisoById_' . $idAviso);

        $idCreador = $this->auth['usuario']->id;

        $dataValue = array();
        parse_str($dataStr, $dataValue);

        $modelAw = new Application_Model_AnuncioWeb();
        $modelAwd = new Application_Model_AnuncioWebDetalle();
        $helperAviso = $this->_helper->getHelper('Aviso');
        
        $arrayAw = $modelAw->getAvisoById($idAviso);

        $this->view->id = $idAviso;
        $this->view->puesto = $arrayAw['puesto'];
        $this->view->fh_pub = $arrayAw['fcreacion'];
        $this->view->fh_vencimiento = $arrayAw['fpublicacion'];
        $this->view->razon_social = $arrayAw['nombre_empresa'];

        $sess = $this->getSession();
        $config = Zend_Registry::get('config');

        if (isset($sess->avisosClonados) && count($sess->avisosClonados) != 1) {
            $this->view->avisoF = $config->dividirAviso->numeroMaximo - count($sess->avisosClonados);
        } else {
            $this->view->avisoF = $config->dividirAviso->numeroMaximo;
        }

        if ($this->_request->isPost()) {
            //Limpia Cache de Aviso

            $primerAviso = array_slice($dataValue, 0, 1, true);
            $demasAvisos = array_slice($dataValue, 1, count($dataValue) - 1, true);

            if (str_replace(' ', '', $primerAviso['nuevoId_1']) !=
                str_replace(' ', '', $arrayAw['puesto'])) {
                $where = $modelAw->getAdapter()->quoteInto('id = ? ', $idAviso);
                $modelAw->update(
                    array(
                    'puesto' => $primerAviso['nuevoId_1'],
                    'creado_por' => $idCreador
                    ), $where
                );
                
                $helperAviso->getSolarAviso()->addAvisoSolr($idAviso);
                //exec("curl -X POST -d 'api_key=".$this->_buscamasConsumerKey."&nid=".$idAviso."&site=".$this->_buscamasUrl."' ".$this->_buscamasPublishUrl);
            }

            $_tu = new Application_Model_TempUrlId();


            $arrayIDClonados = array();

            /*
             * div = dividos
             * dup = duplicado
             * */

            $tipo = 'div';

            if (isset($sess->avisosClonados)) {
                $count = 0;
                foreach ($sess->avisosClonados as $avisoClon) {
                    $arrayIDClonados[$count] = $avisoClon;
                    $count++;
                }
            } else {
                $count = 1;
                $arrayIDClonados['0'] = array('id' => $idAviso, 'tipo' => $tipo);
            }

            foreach ($demasAvisos as $data) {
                if (trim($data) != '') {

                    //$genPassword =  $this->_helper->GenPassword;
                    $slugFilter = new App_Filter_Slug();
                    $arrayNAw = $modelAw->fetchRow($modelAw->getAdapter()->quoteInto('id = ?', $idAviso))->toArray();
                    $arrayNAw['id_area'] = null;
                    $arrayNAw['id_rubro'] = null;
                    $arrayNAw['id_nivel_puesto'] = null;
                    $arrayNAw['funciones'] = '';
                    $arrayNAw['responsabilidades'] = '';
                    $arrayNAw['salario_min'] = null;
                    $arrayNAw['salario_max'] = null;
                    $arrayNAw['fh_pub'] = null;
                    //$arrayNAw['fh_creacion'] = date('Y-m-d H:i:s');
                    $arrayNAw['fh_clonacion'] = date('Y-m-d H:i:s');
                    $arrayNAw['fh_aviso_baja'] = null;
                    $arrayNAw['fh_aviso_eliminado'] = null;
                    $arrayNAw['fh_edicion'] = null;
                    $arrayNAw['fh_vencimiento'] = null;
                    $arrayNAw['fh_vencimiento_proceso'] = null;
                    //$arrayNAw['url_id'] = $genPassword->_genPassword(5);
                    $arrayNAw['url_id'] = $_tu->popUrlId();
                    $arrayNAw['puesto'] = $data;
                    $arrayNAw['slug'] = $slugFilter->filter($data);
                    $arrayNAw['chequeado'] = '0';
                    $arrayNAw['online'] = '0';
                    $arrayNAw['borrador'] = '1';
                    $arrayNAw['proceso_activo'] = '0';
                    $arrayNAw['creado_por'] = $idCreador;
                    $arrayNAw['estado'] = Application_Model_AnuncioWeb::ESTADO_PAGADO;
                    $arrayNAw['estado_publicacion'] = '0';
                    $arrayNAw['original'] = '0';
                    unset($arrayNAw['id']);
                    $newIdAnuncio = $modelAw->insert($arrayNAw);

                    //Actualiza el campo extiende_a referenciado a el mismo
                    $where = $this->getAdapter()->quoteInto('id = ? ', $newIdAnuncio);
                    $modelAw->update(array('extiende_a' => $newIdAnuncio), $where);

                    $arrayIDClonados[$count] = array('id' => $newIdAnuncio, 'tipo' => $tipo);
                    $count++;

                    $detalle = $modelAwd->getDetalle($idAviso);
                    foreach ($detalle as $data) {
                        $id = array('id_anuncio_web' => $newIdAnuncio);
                        $detalleCompleto = array_merge($id, $data);
                        $modelAwd->insert($detalleCompleto);
                    }
                }
            }

            $sess->avisosClonados = $arrayIDClonados;
            $url = '<div id="NDirec" url="/admin/aviso-preferencial/editar/rel/' . 
                $idAviso . '/div/' . $count . '" ></div>';
            echo $url;
        }
    }

    public function eliminarAvisoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idAnuncioWeb = $this->_getParam('aviso', false);

        $sess = $this->getSession();
        $arrayIds = $sess->avisosClonados;
        $nuevoIds = array();

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

        $modelAD = new Application_Model_AnuncioWebDetalle();
        $arrayAD = $modelAD->getIdsXAnuncio($idAnuncioWeb);

        $idsEliminar = array();
        foreach ($arrayAD as $value) {
            $idsEliminar[] = $value['id'];
        }

        $where = array('id in (?)' => $idsEliminar);
        $modelAD->delete($where);

        $modelAW = new Application_Model_AnuncioWeb();
        $where = array('id = ?' => $idAnuncioWeb);
        $modelAW->delete($where);
        
        $helperAviso = $this->_helper->getHelper('Aviso');
        $helperAviso->_SolrAviso->DeleteAvisoSolr($idAnuncioWeb);

        foreach ($arrayIds as $data) {
            if ($data['id'] != $idAnuncioWeb) {
                $nuevoIds[] = $data;
            }
        }

        $sess->avisosClonados = $nuevoIds;
        $this->getMessenger()->success("El aviso ha sido eliminado.");
        $this->_redirect(
            'admin/aviso-preferencial/editar/rel/' . $nuevoIds['0']['id'] . '/div/' . count($nuevoIds)
        );
    }

}
