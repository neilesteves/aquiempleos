<?php

class Postulante_SubirCvController
    extends App_Controller_Action_Postulante
{

    public function init()
    {
        parent::init();
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'myAccount')
        );
    }

    public function indexAction(){
        $this->view->menu_sel = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_SUBE_CV;
        $formCv = new Application_Form_SubirCurriculo();
        $nuevoname = "";
        $id = $this->auth['postulante']['id'];
        $this->_postulante = new Application_Model_Postulante();
        $cv = $this->_postulante->getPostulante($id);
        $pathcv = $cv["path_cv"];
        
        if ($this->_request->isPost()) {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $allParams = $this->_getAllParams();
            $validaForm = $formCv->isValid($allParams);
            if ($validaForm) {
                $utilfile = $this->_helper->getHelper('UtilFiles');
                $nuevoname = $utilfile->_renameFile($formCv, "path_cv",
                    $this->auth);
                $postulante = new Application_Model_Postulante();
                $arrayPostulante = $postulante->getPostulante($id);

                //unlink($this->config->urls->app->elementsCvRoot.$cv["path_cv"]);
                $data = array(
                    'path_cv' => $nuevoname,
                    'ultima_actualizacion' => date('Y-m-d H:i:s'),
                    'last_update_ludata' => date('Y-m-d H:i:s')
                );
                $a = $postulante->update(
                    $data, $postulante->getAdapter()->quoteInto('id = ?', $id)
                );
                if ($a) {
                    $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
                        $id, Application_Model_LogPostulante::SUBIR_CV,
                        $arrayPostulante['path_cv'], $nuevoname
                    );
                    
                    echo Zend_Json::encode(
                        array(
                            'status' => 1, 
                            'message' => 'Currículo guardado con éxito.',
                            'urlFile' => ELEMENTS_URL_CVS . $nuevoname
                            ));

                }
                $pathcv = $nuevoname;
            } else {
                echo Zend_Json::encode(
                    array(
                        'status' => 0, 
                        'message' => 'Error al guardar currículo con éxito.'));
            }
        } else {
            $pathcv = $cv["path_cv"];
        }
        
        $this->view->postulante = $this->auth['postulante'];
        $this->view->usuario    = $this->auth['usuario'];
        $this->view->path_cv    = $pathcv;
        $this->view->frm        = $formCv;
    }

    public function eliminarCvAction()
    {
        
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $mensaje = "";
        if ($this->_hash->isValid($this->_getParam('csrfhash'))) {
            $idPostulante = $this->auth['postulante']['id'];
            $postulante = new Application_Model_Postulante();

            $arrayPostulante = $postulante->getPostulante($idPostulante);

            $postulante->eliminarCv($idPostulante);
            $mensaje = "Curriculo eliminado con éxito.";
            $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
                $idPostulante, Application_Model_LogPostulante::SUBIR_CV,
                $arrayPostulante['path_cv'], ''
            );
            echo Zend_Json::encode(array("status" => 1,'message' => $mensaje));
        } else {
            $mensaje = "Se ha producido un error.";
            echo Zend_Json::encode(array("status" => 0,'message' => $mensaje));
        }    
        
    }
    
    private function _postular($urlId) {
        
        $a = new Application_Model_AnuncioWeb();
        $aviso = $a->getAvisoBySlug($urlId);
        $usuario = $this->auth['usuario'];
        $postulante = $this->auth['postulante'];

        $dataPost = $this->_getAllParams();

        $avisoId = $aviso['id'];

        if ($a->confirmaAvisoActivo($avisoId) == false) {
            $this->getMessenger()->error('El aviso se encuentra cerrado.');
        }

        $p = new Application_Model_Postulacion();
        if ($p->hasPostulado($avisoId, $postulante['id']) !== false) {
            $this->getMessenger()->error('Ya has postulado a este aviso.');
        }
        
        $idPostulante = $this->auth['postulante']['id'];
        
        $creado = $a->getAvisoIdByCreado($urlId);
        
        //Update fecha actualización de postulante
        if (!$this->_postulante->verificarUpdateCV($idPostulante)) {
            $this->_postulante->update(
                    array('ultima_actualizacion' => date('Y-m-d H:i:s')), $this->getAdapter()->quoteInto("id = ?", $idPostulante)
            );
        }

        $funciones = $this->_helper->getHelper("RegistrosExtra");
        $match = $funciones->PorcentajeCoincidencia($avisoId, $postulante['id']);
        $nivelestudioscarrera = $funciones->MejorNivelEstudiosCarrera($postulante['id']);

        $postulacion = new Application_Model_Postulacion();
        $anuncioWebModelo = new Application_Model_AnuncioWeb;
        $referenciadoModelo = new Application_Model_Referenciado;
        $historico = new Application_Model_HistoricoPs();
        $postulanteId = $postulante['id'];
        $email = $usuario->email;

        $postulacionValidador = new
                App_Service_Validate_Postulation_Postulant(
                $avisoId, $postulanteId);

        if (!$postulacionValidador->isNull()) {
            if ($postulacionValidador->isInvited()) {

                $postulacionDatos = $postulacionValidador->getData();
                $idPostulacion = $postulacionDatos['id'];


                $postulacion->activar($idPostulacion);
                $referenciadoModelo->postulo($email, $avisoId);
                $historico->registrar($idPostulacion, Application_Model_HistoricoPs::EVENTO_POSTULACION, Application_Model_HistoricoPs::ESTADO_POSTULACION);
            }
        } else {
            $data = array('id_postulante' => $postulante['id'],
                'id_anuncio_web' => $avisoId,
                'id_categoria_postulacion' => null,
                'fh' => date('Y-m-d H:i:s'),
                'msg_leidos' => '0',
                'msg_no_leidos' => '0',
                'msg_por_responder' => '0',
                'match' => $match,
                'es_nuevo' => '1',
                'nivel_estudio' => $nivelestudioscarrera["nivelestudios"],
                'carrera' => $nivelestudioscarrera["carrera"]);

            $anuncioWeb = $anuncioWebModelo->obtenerPorId(
                    $avisoId, array('id_empresa'));

            $empresaId = $anuncioWeb['id_empresa'];

            $postulanteValidador = new App_Service_Validate_Postulant;
            $postulanteValidador->setData($postulante);

            if ($postulanteValidador->isBlocked($empresaId)) {
                $data['activo'] =
                        Application_Model_Postulacion::POSTULACION_BLOQUEADA;
            }

            if (App_Service_Validate_Postulant::hasReferred(
                            $email, $avisoId)) {
                $data['referenciado'] =
                        Application_Model_Postulacion::ES_REFERENCIADO;

                $referenciadoModelo->postulo($email, $avisoId);
            }

            $idPostulacion = $postulacion->insert($data);
        }

        $this->_helper->Aviso->actualizarPostulantes($avisoId);
        $this->_helper->Aviso->actualizarInvitaciones($avisoId);
        $this->_helper->Aviso->actualizarNuevasPostulaciones($avisoId);

        $respuesta = new Application_Model_Respuesta();

        //Inicio graba Respuesta
        $mensaje = new Application_Model_Mensaje();
        $anuncioWeb = new Application_Model_AnuncioWeb();

        $ususarioEmp = new Application_Model_Usuario;
        $emailUsuarioEmpresa = $ususarioEmp->correoUsuarioxAnuncio($avisoId, $creado);
        $tieneCorreoOp = $anuncioWeb->avisoTieneCorreoOp($avisoId);
        $pos = new Application_Model_Postulante;
        $nivelMaxEstudio = $pos->nivelMaximoEstudio($postulante['id_usuario']);
        $pregunta = new Application_Model_Pregunta();

        try {
            $validator = new Zend_Validate_EmailAddress();

            if ($validator->isValid($usuario->email)) {
                $this->_helper->mail->postularAviso(
                        array(
                            'to' => $usuario->email,
                            'usuario' => $usuario->email,
                            'nombre' => ucwords($postulante['nombres']),
                            'nombrePuesto' => $aviso['puesto'],
                            'mostrarEmpresa' => $aviso['mostrar_empresa'],
                            'nombreEmpresa' => $aviso['nombre_empresa'],
                            'nombreComercial' => $aviso['nombre_comercial']
                        )
                );
            }

            //Si tiene correo adicional solo se envía a ese correo,
            // sino solo al usuario empreaa
            //Modificar cuando se tenga apepat apemat
            if ($tieneCorreoOp != '') {

                if ($validator->isValid($tieneCorreoOp)) {
                    $this->_helper->mail->postularAvisoEmpresa(
                            array(
                                'to' => $tieneCorreoOp,
                                'idAviso' => $avisoId,
                                'usuario' => $usuario->email,
                                'nombre' => $postulante['nombres'],
                                'apellidos' => $postulante['apellidos'] . ' ' .
                                $postulante['apellido_paterno'] . " " . $postulante['apellido_materno'],
                                'dni' => $postulante['num_doc'],
                                'nivel' => $nivelMaxEstudio,
                                'slug' => $postulante['slug'],
                                'nombrePuesto' => $aviso['puesto'],
                                'mostrarEmpresa' => $aviso['mostrar_empresa'],
                                'nombreEmpresa' => $aviso['nombre_empresa'],
                                'nombreComercial' => $aviso['nombre_comercial']
                            )
                    );
                }
            } 
        } catch (Exception $ex) {            
            /*
             * Enviar a un SQS y no mostrar estos detalles al usuario.
             * El correo se enviara desde el SQS
             */
            
//            $this->getMessenger()->error(
//                    'Error al enviar el correo con los datos de la postulación.'
//            );
        }
        $aw = $anuncioWeb->fetchRow($anuncioWeb->getAdapter()->quoteInto('id = ?', $avisoId));
        //Fin

        $dataRespuesta = array();
        foreach ($dataPost as $key => $value) {
            if (substr_count($key, 'pregunta') > 0) {
                $dataRespuesta['id_pregunta'] =
                        str_replace('pregunta_', '', $key);
                $dataRespuesta['id_postulacion'] = $idPostulacion;
                $dataRespuesta['respuesta'] = $value;
                $respuesta->insert($dataRespuesta);
                $idPre = $dataRespuesta['id_pregunta'];
                //Inicio Grabar Pregunta
                $pregu = $pregunta->find($idPre)->toArray();
                $data = array(
                    // @codingStandardsIgnoreStart
                    'de' => $aw->creado_por,
                    // @codingStandardsIgnoreEnd
                    'para' => $this->auth['usuario']->id,
                    'fh' => date('Y-m-d H:i:s'),
                    'cuerpo' => $pregu['0']['pregunta'],
                    'tipo_mensaje' => Application_Model_Mensaje::ESTADO_PREGUNTA,
                    'leido' => 1,
                    'notificacion' => 0,
                    'id_postulacion' => $idPostulacion
                );
                $idPregunta = $mensaje->insert($data);
                $data = array(
                    'de' => $this->auth['usuario']->id,
                    'padre' => $idPregunta,
                    'para' => $aw['creado_por'],
                    'fh' => date('Y-m-d H:i:s'),
                    'cuerpo' => $value,
                    'tipo_mensaje' => Application_Model_Mensaje::ESTADO_MENSAJE,
                    'leido' => 0,
                    'notificacion' => 0,
                    'id_postulacion' => $idPostulacion
                );
                $mensaje->insert($data);
                //fin
            }
        }
        //Inicio historico
        $data = array(
            'id_postulacion' => $idPostulacion,
            'evento' => 'postulación',
            'fecha_hora' => date('Y-m-d H:i:s'),
            'descripcion' => Application_Model_HistoricoPs::ESTADO_POSTULACION
        );
        $historico->insert($data);
        //fin Historico
        // @codingStandardsIgnoreStart
        //Inicio anuncio_postulante_match
        $modelAPM = new Application_Model_AnuncioPostulanteMatch();
        $idPostu = $postulante['id'];
        @$modelAPM->update(
                        array('estado' => Application_Model_AnuncioPostulanteMatch::ESTADO_POSTULA,
                    'fh_postulacion' => date('Y-m-d H:i:s')), $this->getAdapter()->quoteInto("id_anuncio_web = $avisoId AND id_postulante = $idPostu", null)
        );
    }

}


