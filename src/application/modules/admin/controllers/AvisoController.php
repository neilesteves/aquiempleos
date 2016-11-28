<?php

class Admin_AvisoController extends App_Controller_Action_Admin
{

    protected $_avisoId;
    protected $_datosAviso;
    protected $_cache = null;

    public function init()
    {
        parent::init();
        $this->_cache = Zend_Registry::get('cache');

        $idAw = $this->_getParam('rel', null);
        $idAwId = $this->_getParam('id', null);

        if(isset($idAw)) {
            $this->_avisoId = $idAw;
            $aviso = new Application_Model_AnuncioWeb();
            $this->_datosAviso = $aviso->getAvisoInfoById($this->_avisoId);
        } elseif(isset($idAwId)) {
            $this->_avisoId = $idAwId;
            $aviso = new Application_Model_AnuncioWeb();
            $this->_datosAviso = $aviso->getAvisoInfoById($this->_avisoId);
        }
    }

    public function indexAction()
    {
        $this->_redirect('/admin/gestion/avisos');
    }

    public function editarAction()
    {
        $back = $this->_getParam('back', null);

        if(isset($back)) {
            $this->_helper->viewRenderer('editar-publicacion');
            Zend_Layout::getMvcInstance()->assign(
                    'bodyAttr', array('id' => 'EditarAviso', 'class' => 'noMenu')
            );
        } else {
            Zend_Layout::getMvcInstance()->assign(
                    'bodyAttr', array('id' => 'EditarAviso', 'class' => 'noMenu noMenuAdm')
            );
        }

        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/empresa/empresa.aviso.paso2.js')
        );

        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/administrador/micuenta.admin.js')
        );

        $sess = $this->getSession();
        if(isset($sess->avisoAdminUrl)) {
            $this->view->avisoAdminUrl = $this->view->url($sess->avisoAdminUrl, 'default', false);
        }

        $config = Zend_Registry::get("config");
        //@codingStandardsIgnoreStart
        $this->view->numPalabraPuesto = $config->avisopaso2->puestonumeropalabra;
        $this->view->numPalabraOtroNombre = $config->avisopaso2->mostrarnombrenumeropalabra;
        //@codingStandardsIgnoreEnd
        $this->view->modulo = $this->_request->getModuleName();

        $aviso = new Application_Model_AnuncioWeb();

        $formEstudio = array();
        $formOtroEstudio = array();
        $formExperiencia = array();
        $formIdioma = array();
        $formPrograma = array();
        $formPregunta = array();

        $datosAviso = $this->_datosAviso;
        if($datosAviso['online'] == 1) {
            $this->view->online = true;
            $online = true;
        } else {
            $online = false;
        }
        $formPuesto = new Application_Form_Paso2PublicarAviso(true);
        $frmUbigeo = new Application_Form_Ubigeo();

        $tarifaId = $datosAviso['id_tarifa'];
        $modelProducto = new Application_Model_Producto;

//        if ($tarifaId==1) {
//            $formPuesto->removeTipoPuesto();
//        }
//        
//        if ($tarifaId==158) {
//            $formPuesto->removeTipoPuesto();
//        }

        $tipoProducto = $modelProducto->obtenerTipoAviso($tarifaId);
        if($tipoProducto != Application_Model_AnuncioWeb::TIPO_CLASIFICADO) {
            $formPuesto->removeTipoPuesto();
        }

        $this->view->nombreComercial = $datosAviso['empresa_rs'];
        $this->view->puesto = $datosAviso['nombre_puesto'];
        $this->view->estado = $datosAviso['estado'];
        $this->view->idAviso = $datosAviso['id'];
        $datosAviso['id_aviso'] = $datosAviso['id'];
        if($datosAviso['salario'] == null && $datosAviso['salario_min'] != null) {
            $datosAviso['salario'] = $datosAviso['salario_min'] . '-max';
        }
        $formPuesto->setHiddenId($datosAviso['id']);

        if($datosAviso['empresa_nombre'] != $datosAviso['empresa_rs']) {
            $datosAviso['otro_nombre_empresa'] = $datosAviso['empresa_rs'];
        } else {
            $datosAviso['mostrar_empresa'] = true;
            //@codingStandardsIgnoreStart
            $datosAviso['otro_nombre_empresa'] = $config->avisopaso2->mostrarnombredefault;
            //@codingStandardsIgnoreEnd
        }

        $frmUbigeo->detalleUbigeo($datosAviso['id_ubigeo']);
        $nivelPuesto = new Application_Model_NivelPuesto();
        $nivelesArea = $nivelPuesto->getNivelesByArea($datosAviso["id_area"]);

        $_niveles = array();
        foreach ($nivelesArea as $value) {
            $_niveles[$value['id']] = $value['nombre'];
        }
        $formPuesto->isValid($datosAviso);
        $formPuesto->id_nivel_puesto->addMultiOption('-1', 'Seleccionar nivel');
        $formPuesto->id_nivel_puesto->addMultiOptions($_niveles);
        $baseFormEstudio = new Application_Form_Paso2EstudioPublicar(true);
        $managerEstudio = new App_Form_Manager($baseFormEstudio, 'managerEstudio');

        $baseFormOtroEstudio = new Application_Form_Paso2OtroEstudioPublicar(true);
        $managerOtroEstudio = new App_Form_Manager($baseFormOtroEstudio, 'managerOtroEstudio');

        $baseFormExperiencia = new Application_Form_Paso2ExperienciaPublicar(true);
        $managerExperiencia = new App_Form_Manager($baseFormExperiencia, 'managerExperiencia');

        $baseFormIdioma = new Application_Form_Paso2Idioma(true);
        $managerIdioma = new App_Form_Manager($baseFormIdioma, 'managerIdioma');

        $baseFormPrograma = new Application_Form_Paso2Programa(true);
        $managerPrograma = new App_Form_Manager($baseFormPrograma, 'managerPrograma');

        $baseFormPregunta = new Application_Form_Paso2PreguntaPublicar(true);
        $managerPregunta = new App_Form_Manager($baseFormPregunta, 'managerPregunta');

        $datosAvisoEstudio = $aviso->getEstudioInfoByAnuncio($this->_avisoId);

        $auth = $this->auth;
        $auth['empresa']['nombre_comercial'] = $datosAviso['empresa_nombre'];

        Zend_Layout::getMvcInstance()->assign(
                'auth', $auth
        );


        if($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();

            $tipoProducto = $modelProducto->obtenerTipoAviso($postData['id_tarifa']);

            if($tipoProducto != Application_Model_AnuncioWeb::TIPO_CLASIFICADO) {
                $formPuesto->removeTipoPuesto();
            }

            $dFunciones = $postData["funciones"];
            $dResponsabilidades = $postData["responsabilidades"];

            //@codingStandardsIgnoreStart
            $postData["funciones"] = preg_replace($config->avisopaso2->expresionregular, '', $postData["funciones"]);
            $postData["responsabilidades"] = preg_replace($config->avisopaso2->expresionregular, '', $postData["responsabilidades"]);
            //@codingStandardsIgnoreEnd

            $postData["funciones"] = str_replace("@", "", $postData["funciones"]);
            $postData["responsabilidades"] = str_replace("@", "", $postData["responsabilidades"]);

            $validPuesto = $formPuesto->isValid($postData);
            $validEstudio = $managerEstudio->isValid($postData);
            $validOtroEstudio = $managerOtroEstudio->isValid($postData);
            $validExperiencia = $managerExperiencia->isValid($postData);
            $validIdioma = $managerIdioma->isValid($postData);
            $validPrograma = $managerPrograma->isValid($postData);
            $validPregunta = $managerPregunta->isValid($postData);
            //  $frmUbigeo->isValid($postData);

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
           
            var_dump($validPuesto,
                    $validEstudio,
                    $validOtroEstudio,
                    $validExperiencia,
                    $validIdioma,
                    $validPrograma
            );
            
                if($validPuesto &&
                    $validEstudio &&
                    $validOtroEstudio &&
                    $validExperiencia &&
                    $validIdioma &&
                    $validPrograma
            ) {
                $avisoHelper = $genPassword = $this->_helper->getHelper('Aviso');
                $util = $this->_helper->getHelper('Util');
                $idUbigeo = $util->getUbigeo($postData);
                $nomUsuario = $this->auth['usuario']->nombre . " " . $this->auth['usuario']->apellido;
                //$formPuesto['id_usuario']= $datosAviso['creado_por'];
                $avisoHelper->_actualizarDatosPuesto(
                        $formPuesto, $this->_avisoId, $datosAviso['id_empresa'], $idUbigeo, $nomUsuario
                );
                $avisoHelper->_actualizarEstudios($managerEstudio, $this->_avisoId);
                $avisoHelper->_actualizarOtrosEstudios($managerOtroEstudio, $this->_avisoId);
                $avisoHelper->_actualizarExperiencas($managerExperiencia, $this->_avisoId);
                $avisoHelper->_actualizarIdioma($managerIdioma, $this->_avisoId);
                $avisoHelper->_actualizarPrograma($managerPrograma, $this->_avisoId);
                $avisoHelper->_actualizarPregunta(
                        $managerPregunta, $this->_avisoId, $datosAviso['id_empresa']
                );
              //  var_dump($this->_avisoId);exit;
                if(isset($back)) {
                    $this->_redirect('/admin/publicar-aviso/paso3/aviso/' . $this->_avisoId);
                } else {
                    //Actualiza Match a Postulantes
                    $mPostulacion = new Application_Model_Postulacion;
                    $mPostulacion->actualizarMatchPostulantes($this->_avisoId);

                    $this->getMessenger()->success('El aviso se modificó con éxito.');
                    $this->_redirect('/admin/aviso/editar/rel/' . $this->_avisoId);
                }
            } //else {
            if(/* $validOtroEstudio && */!$managerOtroEstudio->isEmptyLastForm()) {
                $ind = count($managerOtroEstudio->getForms());
                $formOtroEstudio[$ind] = $managerOtroEstudio->getForm($ind);
            }
            if(/* $validExperiencia && */!$managerExperiencia->isEmptyLastForm()) {
                $ind = count($managerExperiencia->getForms());
                $formExperiencia[$ind] = $managerExperiencia->getForm($ind);
            }
            if(/* $validIdioma && */!$managerIdioma->isEmptyLastForm()) {
                $ind = count($managerIdioma->getForms());
                $formIdioma[$ind] = $managerIdioma->getForm($ind);
            }
            if(/* $validPrograma && */!$managerPrograma->isEmptyLastForm()) {
                $ind = count($managerPrograma->getForms());
                $formPrograma[$ind] = $managerPrograma->getForm($ind);
            }
            if(/* $validPregunta && */!$managerPregunta->isEmptyLastForm()) {
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
            foreach ($formuEstudio as $ke => $fe) {
                $id_tipo_carrera = $fe->getElement('id_tipo_carrera')->getValue();
                $fe->setElementCarrera($id_tipo_carrera);
                $id_nivel_estudio = $fe->getElement('id_nivel_estudio')->getValue();
                $fe->setElementNivelEstudio($id_nivel_estudio);
                $formEstudio[$ke] = $fe;
            }
            if(/* $validEstudio && */!$managerEstudio->isEmptyLastForm()) {
                $ind = count($managerEstudio->getForms());
                $formEstudio[$ind] = $managerEstudio->getForm($ind);
            }
            //}
        } else {
            $i = 0;
            if(count($datosAvisoEstudio) > 0) {
                $this->view->isEstudio = true;
                $this->view->isEditarEstudio = null;
                foreach ($datosAvisoEstudio as $d) {
                    $form = $managerEstudio->getForm($i++, $d);
                    if(isset($d['id_carrera'])) {
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
                    $form->setHiddenId($d['id']);
                    $formEstudio[] = $form;
                }
            }// else {
            //$this->view->isEstudio = false;
            //$this->view->isEditarEstudio = null;
            $form = $managerEstudio->getForm($i++);
            $formEstudio[] = $form;
            //}

            $datosAvisoOtroEstudio = $aviso->getOtroEstudioInfoByAnuncio($this->_avisoId);

            $i = 0;
            if(count($datosAvisoOtroEstudio) > 0) {
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

            $datosAvisoExperiencia = $aviso->getExperienciaInfoByAnuncio($this->_avisoId);

            $i = 0;
            if(count($datosAvisoExperiencia) > 0) {
                $this->view->isExperiencia = true;
                $this->view->isEditarExperiencia = null;
                foreach ($datosAvisoExperiencia as $d) {
                    $form = $managerExperiencia->getForm($i++, $d);
                    $form->setHiddenId($d['id']);
                    $formExperiencia[] = $form;
                }
            }// else {
            //$this->view->isExperiencia = false;
            //$this->view->isEditarExperiencia = null;
            $form = $managerExperiencia->getForm($i++);
            $formExperiencia[] = $form;
            //}

            $datosAvisoIdioma = $aviso->getIdiomaInfoByAnuncio($this->_avisoId);

            $i = 0;
            if(count($datosAvisoIdioma) > 0) {
                $this->view->isIdioma = true;
                $this->view->isEditarIdioma = null;
                foreach ($datosAvisoIdioma as $d) {
                    $form = $managerIdioma->getForm($i++, $d);
                    $form->setHiddenId($d['id']);
                    $baseFormIdioma->addValidatorsIdioma();
                    $formIdioma[] = $form;
                }
            }// else {
            //$this->view->isIdioma = false;
            //$this->view->isEditarIdioma = null;
            $form = $managerIdioma->getForm($i++);
            $formIdioma[] = $form;
            //}

            $datosAvisoPrograma = $aviso->getProgramaInfoByAnuncio($this->_avisoId);

            $i = 0;
            if(count($datosAvisoPrograma) > 0) {
                $this->view->isPrograma = true;
                $this->view->isEditarPrograma = null;
                foreach ($datosAvisoPrograma as $d) {
                    $form = $managerPrograma->getForm($i++, $d);
                    $form->setHiddenId($d['id']);
                    $baseFormPrograma->addValidatorsPrograma();
                    $formPrograma[] = $form;
                }
            }// else {
            //$this->view->isPrograma = false;
            //$this->view->isEditarPrograma = null;
            $form = $managerPrograma->getForm($i++);
            $formPrograma[] = $form;
            //}

            $datosAvisoPregunta = $aviso->getPreguntaInfoByAnuncio($this->_avisoId);

            $i = 0;
            if(count($datosAvisoPregunta) > 0) {
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

        $this->view->frmUbigeo = $frmUbigeo;
    }

    public function borrarExperienciaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idExperiencia = $this->_getParam('id', false);
        if($idExperiencia) {
            $urlId = $this->_helper->Aviso->getUrlIdGeneralPostulante(
                    $idExperiencia, 'Experiencia'
            );

            $experiencia = new Application_Model_AnuncioExperiencia();
            $where = array('id=?' => $idExperiencia);
            $r = (bool) $experiencia->delete($where);
        } else {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo eliminar.'
            );
        }
        if(isset($r) && $r == true) {
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
        $this->_cache->remove('anuncio_web_' . $urlId);
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarEstudioAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idEstudio = $this->_getParam('id', false);
        if($idEstudio) {
            $urlId = $this->_helper->Aviso->getUrlIdGeneralPostulante($idEstudio, 'Estudio');
            $estudio = new Application_Model_AnuncioEstudio();
            $where = array('id=?' => $idEstudio);
            $r = (bool) $estudio->delete($where);
        } else {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo eliminar.'
            );
        }
        if(isset($r) && $r == true) {
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
        $this->_cache->remove('anuncio_web_' . $urlId);
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarIdiomaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idIdioma = $this->_getParam('id', false);
        if($idIdioma) {
            $urlId = $this->_helper->Aviso->getUrlIdGeneralPostulante($idIdioma, 'Idioma');
            $idioma = new Application_Model_AnuncioIdioma();
            $where = array('id=?' => $idIdioma);
            $r = (bool) $idioma->delete($where);
        } else {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo eliminar.'
            );
        }
        if(isset($r) && $r == true) {
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
        $this->_cache->remove('anuncio_web_' . $urlId);
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarProgramaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idPrograma = $this->_getParam('id', false);
        if($idPrograma) {
            $urlId = $this->_helper->Aviso->getUrlIdGeneralPostulante(
                    $idPrograma, 'ProgramaComputo'
            );
            $programa = new Application_Model_AnuncioProgramaComputo();
            $where = array('id=?' => $idPrograma);
            $r = (bool) $programa->delete($where);
        } else {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo eliminar.'
            );
        }
        if(isset($r) && $r == true) {
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
        $this->_cache->remove('anuncio_web_' . $urlId);
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarPreguntaAction()
    {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $idPregunta = $this->_getParam('id', false);
            if($idPregunta) {
                $pregunta = new Application_Model_Pregunta();
                $aviso = $pregunta->getByAviso($idPregunta);
                $r = (bool) $pregunta->deletePregunta($idPregunta);
            } else {
                echo "No se pudo eliminar.";
                exit;
            }
            if(isset($r) && $r == true) {
                $data = array(
                    'status' => 'ok',
                    'msg' => 'Se elimino con exito.'
                );
            } else {
                echo "No se pudo eliminar.";
                exit;
            }
            @$this->_cache->remove('Cuestionario_getPreguntasByAnuncioWeb_' . $aviso['id']);
            @$this->_cache->remove('anuncio_web_' . $urlId);
        } catch(Exception $exc) {
            $this->log->log($exc->getMessage() . '. ' . $exc->getTraceAsString(), Zend_Log::ERR);
            echo "No se pudo eliminar.";
            exit;
        }


        $this->_response->appendBody(Zend_Json::encode($data));
    }

}
