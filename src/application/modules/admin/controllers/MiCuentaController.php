<?php


class Admin_MiCuentaController extends App_Controller_Action_Admin {

    protected $_postulante;
    protected $_usuario;
    protected $_perfil;
    protected $_messageSuccess = 'Sus datos se cambiaron con éxito.';

    public function init() {
        parent::init();
//        if (Zend_Auth::getInstance()->hasIdentity() != true &&
//                $this->getRequest()->action != 'perfil-publico' &&
//                $this->getRequest()->action != 'exporta-pdf' && 
//                $this->getRequest()->action != 'mis-datos-personales' &&
//                $this->getRequest()->action != 'mis-experiencias') {            
//            $this->_redirect('/');
//        }

        /* Initialize action controller here */
        $this->_postulante = new Application_Model_Postulante();
        $this->_usuario = new Application_Model_Usuario();
        $this->_perfil = new Application_Model_PerfilDestacado();

        Zend_Layout::getMvcInstance()->assign(
                'bodyAttr', array('id' => 'myAccount')
        );
        $this->_postulante = new Application_Model_Postulante();
        if ($this->isAuth &&
                $this->auth['usuario']->rol == Application_Form_Login::ROL_POSTULANTE) {
            $this->idPostulante = $this->auth['postulante']['id'];
        }
        $this->usuario = $this->auth['usuario'];
    }


    public function bumeranAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();


        $areas = array(
            '1003' => 'Aplicaciones Laborales',
            ''=> '',

        );


    }

    public function exportaPdfAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $domPdf = $this->_helper->getHelper('DomPdf');

        Zend_Layout::getMvcInstance()->assign(
                'bodyAttr', array('id' => 'profilePublic')
        );
        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/postulante.perfil.publico.js')
        );
        $this->view->headLink()->appendStylesheet(
                $this->view->S(
                        '/css/profile.pdf.css')
        );
        $headLinkContainer = $this->view->headLink()->getContainer();
        unset($headLinkContainer[0]);
        unset($headLinkContainer[1]);

        $slug = $this->_getParam('slug');
        $postulante = $this->_postulante->getPostulantePerfil($slug);

        $id = $postulante['idpostulante'];

        $perfil = $this->_postulante->getPerfil($id);

        $perfil['postulante']['fotovariable'] = $perfil['postulante']['path_foto_uno'];
        $this->view->postulante = $perfil;

        $html = $this->view->render('mi-cuenta/exporta-pdf.phtml');
        $mvc = Zend_Layout::getMvcInstance();
        //$this->_helper->layout->setLayout('perfil_publico');
        $layout = $mvc->render('perfil_publico_pdf');

        $layout = str_replace("<!--perfil-->", $html, $layout);
        $layout = str_replace("\"", "'", $layout);
        //echo $layout;exit;

        $domPdf->mostrarPDF($layout, 'A4', "portrait", "curriculo.pdf");
    }

    public function indexAction() {
//        // dashboard
//        $this->view->headScript()->appendFile(
//            $this->mediaUrl.'/js/postulante.dashboard.js'
//        );
//
//        $this->view->menu_sel = self::MENU_MI_CUENTA;
//        $this->view->menu_post_sel = self::MENU_POST_INICIO;
//        $this->view->isAuth = $this->isAuth;
//
//        if ($this->isAuth) {
//            $porcentaje=0;
//            $id = $this->idPostulante;
//            $arrayPostulante = $this->_postulante->getPostulante($id);
//            $_foto = $arrayPostulante["path_foto"];
//            $_experiencia = new Application_Model_Experiencia();
//            $_estudios = new Application_Model_Estudio();
//            $_idiomas = new Application_Model_Idioma();
//            $_programas = new Application_Model_DominioProgramaComputo();
//            $_presentacion = $arrayPostulante["presentacion"];
//            $_tuweb = $arrayPostulante["website"];
//            $_pathcv = $arrayPostulante["path_cv"];
//
//
//            $nex = count($_experiencia->getExperiencias($this->idPostulante));
//            $nes = count($_estudios->getEstudios($this->idPostulante));
//            $nid = count($_idiomas->getIdiomas($this->idPostulante));
//            $npc = count($_programas->getDominioProgramaComputo($this->idPostulante));
//            $incompletos = array();
//            $indice=0;
//
//            if ($_pathcv!="") {
//                $porcentaje+=$this->config->dashboard->peso->subircv;
//            } else {
//                if ($indice<$this->config->dashboard->nimportantes) {
//                    $incompletos[$indice]=$this->config->dashboard->sug->subircv;
//                    $indice++;
//                }
//            }
//            if ($nex>0) {
//                $porcentaje+=$this->config->dashboard->peso->experiencia;
//            } else {
//                if ($indice<$this->config->dashboard->nimportantes) {
//                    $incompletos[$indice]=$this->config->dashboard->sug->experiencia;
//                    $indice++;
//                }
//            }
//            if ($nes>0) {
//                $porcentaje+=$this->config->dashboard->peso->estudios;
//            } else {
//                if ($indice<$this->config->dashboard->nimportantes) {
//                    $incompletos[$indice]=$this->config->dashboard->sug->estudios;
//                    $indice++;
//                }
//            }
//            if ($nid>0) {
//                $porcentaje+=$this->config->dashboard->peso->idiomas;
//            } else {
//                if ($indice<$this->config->dashboard->nimportantes) {
//                    $incompletos[$indice]=$this->config->dashboard->sug->idiomas;
//                    $indice++;
//                }
//            }
//            if ($npc>0) {
//                $porcentaje+=$this->config->dashboard->peso->programas;
//            } else {
//                if ($indice<$this->config->dashboard->nimportantes) {
//                    $incompletos[$indice]=$this->config->dashboard->sug->programas;
//                    $indice++;
//                }
//            }
//            if ($_presentacion!="") {
//                $porcentaje+=$this->config->dashboard->peso->presentacion;
//            } else {
//                if ($indice<$this->config->dashboard->nimportantes) {
//                    $incompletos[$indice]=
//                                $this->config->dashboard->sug->presentacion;
//                    $indice++;
//                }
//            }
//            if ($_tuweb!="") {
//                $porcentaje+=$this->config->dashboard->peso->tuweb;
//            } else {
//                if ($indice<$this->config->dashboard->nimportantes) {
//                    $incompletos[$indice]=$this->config->dashboard->sug->tuweb;
//                    $indice++;
//                }
//            }
//            if ($_foto!="photoDefault.jpg" && $_foto!="") {
//                $porcentaje+=$this->config->dashboard->peso->foto;
//            } else {
//                if ($indice<$this->config->dashboard->nimportantes) {
//                    $incompletos[$indice]=$this->config->dashboard->sug->foto;
//                    $indice++;
//                }
//            }
//
//            $this->view->imgPhoto = $arrayPostulante["path_foto"];
//            $this->view->nombres = $arrayPostulante["nombres"];
//            $this->view->apellidos = $arrayPostulante["apellidos"];
//            $this->view->porcentaje = $porcentaje;
//            $this->view->incompletos = $incompletos;
//            $this->view->var = $this->config->dashboard;
//            $this->view->slug = $arrayPostulante["slug"];
//
//            $_mensaje = new Application_Model_Mensaje();
//            //mispostulaciones
//            $this->_postulaciones = new Application_Model_Postulacion();
//            $this->view->postulaciones = $this->_postulaciones->
//            getPostulaciones($id, $this->config->dashboard->npostulaciones_mostrar);
//            $this->view->notif_total = $arrayPostulante["notif_leidas"]+
//                                       $arrayPostulante["notif_no_leidas"];
//            $this->view->notif_leidas = $arrayPostulante["notif_leidas"];
//            $this->view->notif_no_leidas = $arrayPostulante["notif_no_leidas"];
//
//            //estadisticas
//            $es = $_mensaje->getEstadisticasMsgPostulacion($this->idPostulante);
//            $this->view->estadisticas =$es;
//            //misnotificaciones
//            $this->view->notificaciones = $_mensaje->
//                                          getMensajesNotificacion($arrayPostulante["idusuario"]);
//        }
//
    }

    public function misDatosPersonalesAction() {
        $config = $this->getConfig();
        $util = new App_Util();
        $formatSize = $util->formatSizeUnits($config->app->maxSizeFile);
        $config->formatSize = $formatSize;
        $this->view->config = $config;

        $sess = $this->getSession();
        $this->view->urlvolver = $this->view->url($sess->postulanteAdminUrl, 'default', false);

        $this->view->modulo = $this->getRequest()->getModuleName();
        $this->view->controlador = $this->getRequest()->getControllerName();
        $this->view->rol = $this->auth['usuario']->rol;

        $this->view->headScript()->appendFile($this->view->S('/js/administrador/micuenta.admin.js'));
        
        $this->view->menu_sel_side = self::MENU_POST_SIDE_DATOSPERSONALES;

        $this->view->idPostulante = $id = $this->_getParam('rel', null);
        $arrayPostulante = $this->_postulante->getPostulante($id);        
        $img = $this->view->imgPhoto = $arrayPostulante['path_foto_uno'];
        $imgUno = $arrayPostulante['path_foto_uno'];
        $imgDos = $arrayPostulante['path_foto_dos'];

        $this->view->slug = $arrayPostulante['slug'];
        $this->view->activo = $arrayPostulante['activo'];
        $this->view->nombres = $arrayPostulante['nombres'];
        $this->view->apellidos = $arrayPostulante['apellido_paterno'].' '.$arrayPostulante['apellido_materno'];

        $idUsuario = $arrayPostulante['id_usuario'];
        $this->view->emailMicuenta = $arrayPostulante['email'];
        
        $this->view->mes = array('01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
            '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
            '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre');
        
        $formPostulante = new Application_Form_Paso1Postulante($id);
        $formPostulante->removeElement('path_foto');

        //validadores del email y Dni para que no se repitan
        $formPostulante->validadorNumDoc($id);

        //Usuario
        if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_SOPORTE ||
                $this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_MASTER) {
            $formUsuario = new Application_Form_Paso1Usuario($idUsuario);
            $formUsuario->validadorEmail($idUsuario);
            $formUsuario->setDefault('email', $arrayPostulante['email']);
        }

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
        $this->view->sexo=$arrayPostulante['sexoMF'];
        $formPostulante->setDefaults($arrayPostulante);
        $valPostUbigeo = '';

        if ($this->_request->isPost()) {
            $allParams = $this->_getAllParams();

            $fechaNac = $allParams['selDia']."/".$allParams['selMes']."/".$allParams['selAnio'];
            $allParams['fecha_nac'] = $fechaNac;

            $validPostulante = $formPostulante->isValid($allParams);

            $condicion = true;

            if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_SOPORTE ||
                    $this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_MASTER) {
                $formUsuario->removeElement('pswd');
                $formUsuario->removeElement('pswd2');
                $validUsuario = $formUsuario->isValid($allParams);

                if (isset($allParams['auth_token'])) {
                    unset($allParams['auth_token']);
                }

                $condicion = (isset($validUsuario) && $validUsuario);
            }

            if (isset($allParams['id_provincia'])) {
                $valPostUbigeo = $allParams['id_provincia'];
            }

            if ($validPostulante && ($condicion)) {
                $valuesPostulante = $formPostulante->getValues();
                $date = date('Y-m-d H:i:s');
                try {
                    $db = $this->getAdapter();
                    $db->beginTransaction();

                    $lastId = $idUsuario;

                    if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_SOPORTE ||
                            $this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_MASTER) {
                        $valuesUsuario = $formUsuario->getValues();
                        $modelUsuario = new Application_Model_Usuario();
                        $where = $modelUsuario->getAdapter()->quoteInto('id = ?', $idUsuario);

                        if (isset($valuesUsuario['auth_token'])) {
                            unset($valuesUsuario['auth_token']);
                        }

                        $valuesUsuario['email'] = trim($valuesUsuario['email']);
                        $modelUsuario->update($valuesUsuario, $where);
                    }

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
                            'Y-m-d', strtotime(
                                    str_replace('/', '-', $valuesPostulante['fecha_nac'])
                            )
                    );

                    $valorTipoDoc = explode('#', $valuesPostulante['tipo_doc']);
                    $valuesPostulante['tipo_doc'] = $valorTipoDoc[0];

                    $valuesPostulante['sexo'] = $valuesPostulante['sexoMF'];
                    $valuesPostulante['ultima_actualizacion'] = $date;
                    $valuesPostulante['last_update_ludata'] = $date;
                    $valuesPostulante['id_ubigeo'] = $this->_helper->Util->getUbigeo($valuesPostulante);

                    $where = $this->_postulante->getAdapter()
                            ->quoteInto('id = ?', $id);

                    unset($valuesPostulante['sexoMF']);
                    unset($valuesPostulante['id_departamento']);
                    unset($valuesPostulante['id_distrito']);
                    unset($valuesPostulante['id_provincia']);
                    unset($valuesPostulante['prefs_emailing']);


                    $this->_postulante->update($valuesPostulante, $where);
                    $valuesPostulante['id']=$id;
                    $rest=  $this->_helper->LogActualizacionBI->logActualizacionPostulantePerfil($valuesPostulante);
                    unset($valuesPostulante['id']);
                    $db->commit();

                    $this->getMessenger()->success($this->_messageSuccess);
                    // Updating session data
                    $this->_redirect(
                            Zend_Controller_Front::getInstance()
                                    ->getRequest()->getRequestUri()
                    );
                } catch (Zend_Db_Exception $e) {
                    $db->rollBack();
                    $this->getMessenger()->error($this->_messageSuccess);
                    echo $e->getMessage();
                } catch (Zend_Exception $e) {
                    $this->getMessenger()->error($this->_messageSuccess);
                    echo $e->getMessage();
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
        if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_SOPORTE ||
                $this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_MASTER) {
            $this->view->formUsuario = $formUsuario;
        }
        $this->view->formPostulante = $formPostulante;
    }

    public function misExperienciasAction() {
        $this->view->action = 'mis-experiencias';
        $this->view->modulo = 'admin/';
        $sess = $this->getSession();
        $this->view->urlvolver = $this->view->url($sess->postulanteAdminUrl, 'default', false);

        $this->view->menu_sel_side = self::MENU_POST_SIDE_EXPERIENCIA;
        $this->view->isAuth = $this->isAuth;

        // action body
        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/administrador/micuenta.admin.js')
        );

        $this->view->idPostulante = $idPostulante = $this->_getParam('rel', null);
        $modelPostulante = new Application_Model_Postulante();
        $arrayPostulante = $modelPostulante->getPostulante($idPostulante);
        $this->view->slug = $arrayPostulante['slug'];
        $this->view->activo = $arrayPostulante['activo'];
        $this->view->nombres = $arrayPostulante['nombres'];
        $this->view->apellidos = $arrayPostulante['apellido_paterno'].' '.$arrayPostulante['apellido_materno'];

        $baseFormExperiencia = new Application_Form_Paso2Experiencia(true);
        $managerExperiencia = new App_Form_Manager($baseFormExperiencia, 'managerExperiencia', true);

        $formsExperiencia = array();
        $index = 0;

        $experiencia = new Application_Model_Experiencia();

        $arrayExperiencias = $experiencia->getExperiencias($idPostulante);
        if (count($arrayExperiencias) != 0) {
            foreach ($arrayExperiencias as $experiencia) {
                if ($experiencia['en_curso'] == '1') {
                    $experiencia['fin_mes'] = date('n');
                }
                if ($experiencia['en_curso'] == '1') {
                    $experiencia['fin_ano'] = date('Y');
                }
                $form = $managerExperiencia->getForm($index++, $experiencia);
                $form->setHiddenId($experiencia['id_Experiencia']);
                $formsExperiencia[] = $form;
                $this->view->isExperiencia = true;
                $this->view->isLinkedin = true;
            }
            $formsExperiencia[] = $managerExperiencia->getForm($index++);
        } else {
            $formsExperiencia[] = $managerExperiencia->getForm(0);
        }

        if ($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();

            $validExp = $managerExperiencia->isValid($postData);
            $this->view->isExperiencia = true;
            $this->view->isLinkedin = true;
            if ($validExp && $this->_hash->isValid($postData['csrfhash_a_tok'])) {
                $this->_actualizarExperienciaPostulante(
                        $managerExperiencia->getCleanPost(), $idPostulante
                );
                //Actualizamos postulaciones.
                $helper = $this->_helper->getHelper("RegistrosExtra");
                $helper->ActualizarPostulacion($idPostulante);

                $this->getMessenger()->success($this->_messageSuccess);
                $this->_redirect(
                        Zend_Controller_Front::getInstance()
                                ->getRequest()->getRequestUri()
                );
            } else {
                $arrExp = explode(',', $postData['managerExperiencia']);
                //var_dump($arrExp);
                foreach($arrExp as $index)
                    $managerExperiencia->removeForm($index);
                $formsExperiencia = $managerExperiencia->getForms();
            }
        }
        $this->view->formExperiencia = $formsExperiencia;
        $this->view->assign('managerExperiencia', $managerExperiencia);
    }

    public function misEstudiosAction() {
        $this->view->action = 'mis-estudios';
        $this->view->modulo = 'admin/';
        $sess = $this->getSession();
        $this->view->urlvolver = $this->view->url($sess->postulanteAdminUrl, 'default', false);

        $this->view->menu_sel_side = self::MENU_POST_SIDE_ESTUDIOS;
        $this->view->isAuth = $this->isAuth;

        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/administrador/micuenta.admin.js')
        );
        $this->view->headLink()->appendStylesheet($this->view->S('/css/plugins/jquery-ui-1.9.2.custom.min.css'));

        $this->view->idPostulante = $idPostulante = $this->_getParam('rel', null);

        $modelPostulante = new Application_Model_Postulante();
        $arrayPostulante = $modelPostulante->getPostulante($idPostulante);        
        $this->view->slug = $arrayPostulante['slug'];
        $this->view->activo = $arrayPostulante['activo'];
        $this->view->nombres = $arrayPostulante['nombres'];
        $this->view->apellidos = $arrayPostulante['apellido_paterno'].' '.$arrayPostulante['apellido_materno'];

        $baseFormEstudio = new Application_Form_Paso2Estudio(true);
        $managerEstudio = new App_Form_Manager($baseFormEstudio, 'managerEstudio', true);

        $formsEstudio = array();
        $index = 0;
        $estudio = new Application_Model_Estudio();

        $arrayEstudios = $estudio->getEstudios($idPostulante);
        if (count($arrayEstudios) != 0) {
            foreach ($arrayEstudios as $estudio) {
                if ($estudio['en_curso'] == '1') {
                    $estudio['fin_mes'] = date('n');
                }
                if ($estudio['en_curso'] == '1') {
                    $estudio['fin_ano'] = date('Y');
                }
                $estudio['institucion'] = $estudio['nombre'];
                unset($estudio['nombre']);
                $form = $managerEstudio->getForm($index++, $estudio);
                 if (isset($estudio['id_carrera'])) {
                  $carrera = new Application_Model_Carrera();
                  $idTipoCarrera = $carrera->getTipoCarreraXCarrera($estudio['id_carrera']);
                  $carreras = $carrera->filtrarCarrera($idTipoCarrera);
                  $form->getElement('id_carrera')->addMultioptions($carreras);
                  } 
                $form->setElementNivelEstudio($estudio['id_nivel_estudio']);
                $form->getElement('id_nivel_estudio_tipo')->setValue($estudio['id_nivel_estudio_tipo']);
                $form->setHiddenId($estudio['id_estudio']);
                $formsEstudio[] = $form;
                $this->view->isLinkedin = true;
                $this->view->isEstudio = true;
            }
            $formsEstudio[] = $managerEstudio->getForm($index++);
        } else {
            $formsEstudio[] = $managerEstudio->getForm(0);
        }

        if ($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();
            $validExp = $managerEstudio->isValid($postData);
            $this->view->isLinkedin = true;
            $this->view->isEstudio = true;
            if ($validExp && $this->_hash->isValid($postData['csrfhash_a_tok'])) {
                $this->_actualizarEstudioPostulante(
                        $managerEstudio->getCleanPost(), $idPostulante
                );

                //Actualizamos postulaciones.
                $helper = $this->_helper->getHelper("RegistrosExtra");
                $helper->ActualizarPostulacion($idPostulante);


                $this->getMessenger()->success($this->_messageSuccess);
                $this->_redirect(
                        Zend_Controller_Front::getInstance()
                                ->getRequest()->getRequestUri()
                );
            } else {
                $arrExp = explode(',', $postData['managerEstudio']);
                foreach($arrExp as $index)
                    $managerEstudio->removeForm($index);
                $formsEstudio = array();
                $formuEstudio = $managerEstudio->getForms();
                foreach($formuEstudio as $fe)
                {
                    $id_tipo_carrera = $fe->getElement('id_tipo_carrera')->getValue();
                    if(!empty($id_tipo_carrera))
                    {
                        //$data = $carrera->filtrarCarrera($id_tipo_carrera);
                        //$fe->getElement('id_carrera')->clearMultiOptions()->addMultiOption('0', 'Selecciona carrera')->addMultiOptions($data);              
                        $fe->setElementCarrera($id_tipo_carrera);
                    }
                    $id_nivel_estudio = $fe->getElement('id_nivel_estudio')->getValue();
                    if(!empty($id_nivel_estudio))
                    {
                        //$data = $nivelEstudio->getSubNiveles($id_nivel_estudio);
                        //$fe->getElement('id_nivel_estudio_tipo')->clearMultiOptions()->addMultiOption('0', 'Selecciona un tipo')->addMultiOptions($data);              
                        $fe->setElementNivelEstudio($id_nivel_estudio);
                    }
                    $formsEstudio[]=$fe;
                }                
            }
        }

        $this->view->formEstudio = $formsEstudio;
        $this->view->assign('managerEstudio', $managerEstudio);
    }

    public function misIdiomasAction() {
        $this->view->action = 'mis-idiomas';
        $this->view->modulo = 'admin/';
        $sess = $this->getSession();
        $this->view->urlvolver = $this->view->url($sess->postulanteAdminUrl, 'default', false);


        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/administrador/micuenta.admin.js')
        );

        $this->view->menu_sel_side = self::MENU_POST_SIDE_IDIOMAS;
        $this->view->isAuth = $this->isAuth;

        $this->view->idPostulante = $idPostulante = $this->_getParam('rel', null);

        $modelPostulante = new Application_Model_Postulante();
        $arrayPostulante = $modelPostulante->getPostulante($idPostulante);
        $this->view->slug = $arrayPostulante['slug'];
        $this->view->activo = $arrayPostulante['activo'];
        $this->view->nombres = $arrayPostulante['nombres'];
        $this->view->apellidos = $arrayPostulante['apellido_paterno'].' '.$arrayPostulante['apellido_materno'];

        $baseFormIdioma = new Application_Form_Paso2Idioma(true);
        $managerIdioma = new App_Form_Manager($baseFormIdioma, 'managerIdioma', true);

        $formsIdioma = array();
        $index = 0;
        $idioma = new Application_Model_DominioIdioma();

        $arrayIdiomas = $idioma->getDominioIdioma($idPostulante);
        if (count($arrayIdiomas) != 0) {
            foreach ($arrayIdiomas as $idioma) {
                $form = $managerIdioma->getForm($index++, $idioma);
                $form->setHiddenId($idioma['id_dominioIdioma']);
                $form->setCabeceras($idioma['id_idioma'], $idioma['nivel_idioma']);
                $form->addValidatorsIdioma();
                $formsIdioma[] = $form;
                $this->view->isLinkedin = true;
                $this->view->isIdioma = false;
            }
            $formsIdioma[] = $managerIdioma->getForm($index++);
        } else {
            $formsIdioma[] = $managerIdioma->getForm(0);
        }

        if ($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();
            $util = $this->_helper->Util;
            $pattern = '/managerIdioma_([0-9]*)_id_idioma/';
            $hayRepetidos = $util->getRepetido($pattern, $postData);

            $validExp = $managerIdioma->isValid($postData);
            $this->view->isLinkedin = true;
            $this->view->isIdioma = false;
            
            if ((bool) $hayRepetidos) {
                $idioma = $this->view->ItemList('idioma', $hayRepetidos);
                $this->getMessenger()->error("Idiomas Repetido: " . $idioma);
                $formsIdioma = $managerIdioma->getForms();
            } elseif ($validExp  && $this->_hash->isValid($postData['csrfhash_a_tok'])) {
                $this->_actualizarIdiomaPostulante(
                        $managerIdioma->getCleanPost(), $idPostulante
                );
                 $data['id']=$idPostulante;
                $this->_helper->LogActualizacionBI->logActualizacionPostulanteIdioma(
                       $data
                );
                //Actualizamos postulaciones.
                $helper = $this->_helper->getHelper("RegistrosExtra");
                $helper->ActualizarPostulacion($idPostulante);

                //ACTUALIZACION DE ZENDLUCENE
                //$this->_actualizarPostulanteZendLucene($idPostulante);

                $this->getMessenger()->success($this->_messageSuccess);
                $this->_redirect(Zend_Controller_Front::getInstance()->getRequest()->getRequestUri());
            } else {
                $arrExp = explode(',', $postData['managerIdioma']);
                foreach($arrExp as $index)
                    $managerIdioma->removeForm($index);
                $formsIdioma = $managerIdioma->getForms();
            }
        }

        $this->view->formIdioma = $formsIdioma;
        $this->view->assign('managerIdioma', $managerIdioma);
    }

    public function misProgramasAction() {
        $this->view->action = 'mis-programas';
        $this->view->modulo = 'admin/';
        $sess = $this->getSession();
        $this->view->urlvolver = $this->view->url($sess->postulanteAdminUrl, 'default', false);

        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/administrador/micuenta.admin.js')
        );

        $this->view->menu_sel_side = self::MENU_POST_SIDE_PROGRAMAS;
        $this->view->isAuth = $this->isAuth;

        $this->view->idPostulante = $idPostulante = $this->_getParam('rel', null);

        $modelPostulante = new Application_Model_Postulante();
        $arrayPostulante = $modelPostulante->getPostulante($idPostulante);
        $this->view->slug = $arrayPostulante['slug'];
        $this->view->activo = $arrayPostulante['activo'];
        $this->view->nombres = $arrayPostulante['nombres'];
        $this->view->apellidos = $arrayPostulante['apellido_paterno'].' '.$arrayPostulante['apellido_materno'];

        $baseFormPrograma = new Application_Form_Paso2Programa(true);
        $managerPrograma = new App_Form_Manager($baseFormPrograma, 'managerPrograma', true);

        $formsPrograma = array();
        $index = 0;

        $programa = new Application_Model_DominioProgramaComputo();

        $arrayProgramas = $programa->getDominioProgramaComputo($idPostulante);

        if (count($arrayProgramas) != 0) {
//            $arrayProgramasIds = Application_Model_ProgramaComputo::getProgramasComputoIds();
            foreach ($arrayProgramas as $programa) {
//                if (!array_key_exists($programa['id_programa_computo'], $arrayProgramasIds)) {
//                    $programa['is_disabled'] = 1;
//                }
                $form = $managerPrograma->getForm($index++, $programa);
                $form->setHiddenId($programa['id_dominioComputo']);
                $form->setCabeceras($programa['id_programa_computo'], $programa['nivel']);
                $form->addValidatorsPrograma();
                $formsPrograma[] = $form;
                $this->view->isPrograma = false;
            }
            $formsPrograma[] = $managerPrograma->getForm($index++);
        } else {
            $formsPrograma[] = $managerPrograma->getForm(0);
        }

        if ($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();
//            if (
//                    $postData['managerPrograma_0_id_programa_computo'] == '-1' && $postData['managerPrograma_0_nivel'] == '-1' && $postData['managerPrograma_0_id_dominioComputo'] == ''
//            ) {
//                unset($postData['managerPrograma_0_id_programa_computo']);
//                unset($postData['managerPrograma_0_nivel']);
//                unset($postData['managerPrograma_0_id_dominioComputo']);
//            }
            $util = $this->_helper->Util;
            $pattern = '/managerPrograma_([0-9]*)_id_programa/';
            $hayRepetidos = $util->getRepetido($pattern, $postData);

            $validExp = $managerPrograma->isValid($postData);
            $this->view->isPrograma = false;

            if ((bool) $hayRepetidos) {
                $programaC = $this->view->ItemList('ProgramaComputo', $hayRepetidos);
                $this->getMessenger()->error("Programas Repetido: " . $programaC);
                $formsPrograma = $managerPrograma->getForms();
            } elseif ($validExp && $this->_hash->isValid($postData['csrfhash_a_tok'])) {
                $this->_actualizarProgramaComputoPostulante(
                        $managerPrograma->getCleanPost(), $idPostulante
                );

                //Actualizamos postulaciones.
                $helper = $this->_helper->getHelper("RegistrosExtra");
                $helper->ActualizarPostulacion($idPostulante);

                $this->getMessenger()->success($this->_messageSuccess);
                $this->_redirect(
                        Zend_Controller_Front::getInstance()
                                ->getRequest()->getRequestUri()
                );
            } else {
                $arrExp = explode(',', $postData['managerPrograma']);
                //var_dump($arrExp);
                foreach($arrExp as $index)
                    $managerPrograma->removeForm($index);
                $formsPrograma = $managerPrograma->getForms();
            }
        }

        $this->view->formPrograma = $formsPrograma;
        $this->view->assign('managerPrograma', $managerPrograma);
    }

    //referencia
    public function misReferenciasAction() {
        $this->view->action = 'mis-referencias';
        $this->view->modulo = 'admin/';
        $sess = $this->getSession();
        $this->view->urlvolver = $this->view->url($sess->postulanteAdminUrl, 'default', false);
        $this->view->menu_sel_side = self::MENU_POST_SIDE_REFERENCIAS;

        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/administrador/micuenta.admin.js')
        );

        $this->view->idPostulante = $idPostulante = $this->_getParam('rel', null);

        $modelPostulante = new Application_Model_Postulante();
        $arrayPostulante = $modelPostulante->getPostulante($idPostulante);

        $this->view->activo = $arrayPostulante['activo'];
        $this->view->nombres = $arrayPostulante['nombres'];
        $this->view->apellidos = $arrayPostulante['apellido_paterno'].' '.$arrayPostulante['apellido_materno'];
        $this->view->slug = $arrayPostulante['slug'];

        $baseFormReferencia = new Application_Form_Paso2Referencia($idPostulante, true);
        $managerReferencia = new App_Form_Manager($baseFormReferencia, 'managerReferencia', true);

        $formsReferencia = array();
        $index = 0;
        $referencias = new Application_Model_Referencia();

        $arrayReferencias = $referencias->getReferencias($idPostulante);
        if (count($arrayReferencias) != 0) {
            foreach ($arrayReferencias as $referencias) {
                $form = $managerReferencia->getForm($index++, $referencias);
                $form->setHiddenId($referencias['id_referencia']);
                $formsReferencia[] = $form;
                $this->view->isReferencia = true;
                $this->view->isLinkedin = false;
            }
            $formsReferencia[] = $managerReferencia->getForm($index++);
        } else {
            $formsReferencia[] = $managerReferencia->getForm(0);
        }

        if ($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();
            $validExp = $managerReferencia->isValid($postData);
            $this->view->isReferencia = true;
            $this->view->isLinkedin = true;
            if ($validExp && $this->_hash->isValid($postData['csrfhash_a_tok'])) {                
                $this->_actualizarReferenciaPostulante(
                        $managerReferencia->getCleanPost(), $idPostulante
                );
                $this->getMessenger()->success($this->_messageSuccess);
                $this->_redirect(
                        Zend_Controller_Front::getInstance()
                                ->getRequest()->getRequestUri()
                );
            } else {                
                $arrExp = explode(',', $postData['managerReferencia']);
                //var_dump($arrExp);
                foreach($arrExp as $index)
                    $managerReferencia->removeForm($index);
                $formsReferencia = $managerReferencia->getForms();
            }
        }


        $this->view->formReferencia = $formsReferencia;
        $this->view->assign('managerReferencia', $managerReferencia);
    }

    public function cambioDeClaveAction() {
        $sess = $this->getSession();
        $this->view->urlvolver = $this->view->url($sess->postulanteAdminUrl, 'default', false);

        $this->view->menu_sel_side = self::MENU_POST_SIDE_CAMBIOCLAVEPOSTULANTE;
        $this->view->isAuth = $this->isAuth;

        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/administrador/micuenta.admin.js')
        );

        $this->view->idPostulante = $idPostulante = $this->_getParam('rel', null);

        $arrayPostulante = $this->_postulante->getPostulante($idPostulante);
        $this->view->slug = $arrayPostulante['slug'];
        $this->view->activo = $arrayPostulante['activo'];
        $this->view->nombres = $arrayPostulante['nombres'];
        $this->view->apellidos = $arrayPostulante['apellido_paterno'].' '.$arrayPostulante['apellido_materno'];

        $idUsuario = $arrayPostulante['id_usuario'];
        $emailUsuario = $arrayPostulante['email'];
        $formCambioClave = new Application_Form_CambioClave($idUsuario);
        $formCambioClave->validarPswd($emailUsuario, $idUsuario);
        
        $allParams = $this->_getAllParams();

        if ($this->_request->isPost()) {
            
            $validClave = ($formCambioClave->isValid($allParams)  && $this->_hash->isValid($allParams['csrfhash']));
            if ($validClave) {
                $valuesClave = $formCambioClave->getValues();
                try {

                    $db = $this->getAdapter();
                    $db->beginTransaction();

                    //Captura de los datos de usuario
                    $valuesClave['pswd'] = App_Auth_Adapter_AptitusDbTable::generatePassword($valuesClave['pswd']);
                    unset($valuesClave['pswd2']);
                    unset($valuesClave['oldpswd']);
                    unset($valuesClave['auth_token']);
                    unset($valuesClave['tok']);
                    unset($valuesClave['csrfhash']);

                    $where = $this->_usuario->getAdapter()
                            ->quoteInto('id = ?', $idUsuario);
                    $this->_usuario->update($valuesClave, $where);
                    $db->commit();

                    $this->getMessenger()->success('Se cambio la clave con éxito.');
                } catch (Zend_Db_Exception $e) {
                    $db->rollBack();
                    $this->getMessenger()->error('Error al cambiar la clave.');
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

    public function misAlertasAction() {

        $sess = $this->getSession();
        $this->view->urlvolver = $this->view->url($sess->postulanteAdminUrl, 'default', false);

        $this->view->modulo = $this->getRequest()->getModuleName();
        $this->view->menu_sel_side = self::MENU_POST_SIDE_ALERTAS;
        $this->view->isAuth = $this->isAuth;

        $this->view->idPostulante = $idPostulante = $this->_getParam('rel', null);
        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/administrador/micuenta.admin.js')
        );

        $formAlertas = new Application_Form_MisAlertas();

        $postulante = new Application_Model_Postulante();
        $alertaPostulante = $postulante->getAlertaPostulante($idPostulante);

        $arrayPostulante = $postulante->getPostulante($idPostulante);
        $this->view->slug = $arrayPostulante['slug'];
        $this->view->activo = $arrayPostulante['activo'];
        $this->view->nombres = $arrayPostulante['nombres'];
        $this->view->apellidos = $arrayPostulante['apellido_paterno'].' '.$arrayPostulante['apellido_materno'];

        $formAlertas->setDefaults($alertaPostulante);

        if ($this->_request->isPost()) {
            $allParams = $this->_getAllParams();
            $validAlertas = $formAlertas->isValid($allParams);
            if ($validAlertas) {
                $valuesAlertas = $formAlertas->getValues();
                $where = $postulante->getAdapter()
                        ->quoteInto('id = ?', $idPostulante);
                $postulante->update($valuesAlertas, $where);

                $this->getMessenger()->success('Cambio Satisfactorio.');

                $this->_redirect(
                        Zend_Controller_Front::getInstance()
                                ->getRequest()->getRequestUri()
                );
            }
        }

        $this->view->formAlertas = $formAlertas;
    }

    public function miPerfilAction() {
//        $this->view->headScript()->appendFile(
//            $this->config->app->mediaUrl . '/js/postulante.perfil.publico.js'
//        );
//        $this->view->menu_sel_side=self::MENU_POST_SIDE_PERFILPUBLICO;
//        $this->view->isAuth = $this->isAuth;
//        $this->view->url_id = $this->_getParam('url_id');
//        $this->view->slug = $this->auth['postulante']['slug'];
//        $id = $this->auth['postulante']['id'];
//        $perfil = $this->_postulante->getPerfil($id);
//        $perfil['postulante']['fotovariable'] = $perfil['postulante']['path_foto'];
//        $this->view->postulante = $perfil;
//
//        $usuario = $this->auth['usuario'];
//        $postulante = $this->auth['postulante'];
//        $formCompartir = new Application_Form_CompartirPorMail();
//        $formCompartir->setAction('/mi-cuenta/compartir');
//        $formCompartir->correoEmisor->setValue($usuario->email);
//        $formCompartir->hdnOculto->setValue(
//            $perfil['postulante']['slug']
//        );
//        $formCompartir->nombreEmisor->setValue(
//            ucwords($postulante['nombres']).' '.ucwords($postulante['apellidos'])
//        );
//        Zend_Layout::getMvcInstance()->assign(
//            'compartirPorMail',
//            $formCompartir
//        );
    }

    public function compartirAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $dataPost = $this->_getAllParams();
        $urlPerfil = $this->view->url(
                array('slug' => $dataPost['hdnOculto']), 'perfil_publico', true
        );

        $this->_helper->Mail->sendMailPerfilPostulante(
                $dataPost, SITE_URL . $urlPerfil
        );
        //*** USAR __call para envio de mail ****//


        $response = array(
            'status' => 'ok',
            'msg' => 'Se envió el correo'
        );
        $this->_response->appendBody(Zend_Json::encode($response));
        //$this->_redirect('mi-cuenta/mi-perfil');
    }

    public function privacidadAction() {
        $sess = $this->getSession();
        $this->view->urlvolver = $this->view->url($sess->postulanteAdminUrl, 'default', false);
        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/administrador/micuenta.admin.js')
        );
        $this->view->menu_sel_side = self::MENU_POST_SIDE_PRIVACIDAD;
        $this->view->isAuth = $this->isAuth;

        $this->view->idPostulante = $idPostulante = $this->_getParam('rel', null);

        $formPrivacidad = new Application_Form_Privacidad();

        $postulante = new Application_Model_Postulante();
        $arrayPostulante = $postulante->getPostulante($idPostulante);
        $this->view->slug = $arrayPostulante['slug'];
        $this->view->activo = $arrayPostulante['activo'];
        $this->view->nombres = $arrayPostulante['nombres'];
        $this->view->apellidos = $arrayPostulante['apellido_paterno'].' '.$arrayPostulante['apellido_materno'];

        $privacidad = $postulante->getPostulantePrivacidad($idPostulante);

        $formPrivacidad->setDefault('fPrivacCP', (int) $privacidad);

        if ($this->_request->isPost()) {
            $allParams = $this->_getAllParams();
            $validPrivacidad = $formPrivacidad->isValid($allParams);
            if ($validPrivacidad) {

                $valuesPrivacidad = $formPrivacidad->getValues();

                $valuesPrivacidad['prefs_confidencialidad'] = (bool) $valuesPrivacidad['fPrivacCP'];
                unset($valuesPrivacidad['fPrivacCP']);

                $where = $postulante->getAdapter()
                        ->quoteInto('id = ?', $idPostulante);
                $postulante->update($valuesPrivacidad, $where);
                $sc = new Solarium\Client($this->config->solr);
                $moPostulante = new Solr_SolrAbstract($sc,'postulante');                        
                if($valuesPrivacidad['prefs_confidencialidad'])
                    $moPostulante->deletePostulante((int)$idPostulante);
                else
                    $moPostulante->addPostulante($idPostulante);                    
                $this->getMessenger()->success('Cambio de privacidad con éxito.');
                $this->_redirect(
                        Zend_Controller_Front::getInstance()
                                ->getRequest()->getRequestUri()
                );
            }
        }

        $this->view->formPrivacidad = $formPrivacidad;
    }

    public function redesSocialesAction() {
        $sess = $this->getSession();
        $this->view->urlvolver = $this->view->url($sess->postulanteAdminUrl, 'default', false);
        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/administrador/micuenta.admin.js')
        );
        $this->view->menu_sel_side = self::MENU_POST_SIDE_REDES_SOCIALES;
        $this->view->isAuth = $this->isAuth;

        $this->view->idPostulante = $idPostulante = $this->_getParam('rel', null);
        $modelPostulante = new Application_Model_Postulante();
        $arrayPostulante = $modelPostulante->getPostulante($idPostulante);
        $this->view->slug = $arrayPostulante['slug'];
        $this->view->activo = $arrayPostulante['activo'];
        $this->view->nombres = $arrayPostulante['nombres'];
        $this->view->apellidos = $arrayPostulante['apellido_paterno'].' '.$arrayPostulante['apellido_materno'];

        // action body
        $redesSociales = new Application_Model_CuentaRs();
        $redes = $redesSociales->getRedesByUser($arrayPostulante['id_usuario']);
        $this->view->isFacebook = false;
        $this->view->isGoogle = false;
        foreach ($redes as $red) {
            if ($red['rs'] == 'facebook') {
                $this->view->isFacebook = true;
            }
            if ($red['rs'] == 'google') {
                $this->view->isGoogle = true;
            }
        }
        $config = $this->getConfig();
        $this->view->openUrl = sprintf(
                $config->apis->google->openidUrl, $config->app->siteUrl . '/' . $config->apis->google->returnUrl, $config->app->siteUrl
        );
        $this->view->facebookAppId = $config->apis->facebook->appid;
        $this->view->urlFacebook = $config->app->siteUrl
                . '/mi-cuenta/agregar-cuenta-facebook';
        $this->view->redes = $redes;
    }

    public function agregarCuentaFacebookAction() {

        $code = $this->getRequest()->getParam('code', 0);
        if (empty($code)) {
            $this->_redirect('/');
        }
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $config = $this->getConfig();
        $appId = $config->apis->facebook->appid;
        $appSecret = $config->apis->facebook->appsecret;
        $url = $config->app->siteUrl
                . '/mi-cuenta/agregar-cuenta-facebook';
        $tokenUrl = "https://graph.facebook.com/v2.0/oauth/access_token?"
                . "client_id=" . $appId . "&redirect_uri=" . urlencode($url)
                . "&client_secret=" . $appSecret . "&code=" . $_REQUEST["code"];

        $response = file_get_contents($tokenUrl);
        $params = null;
        parse_str($response, $params);

        $graphUrl = "https://graph.facebook.com/v2.0/me?access_token="
                . $params['access_token'];

        $facebookUser = json_decode(file_get_contents($graphUrl));
        $data['id_usuario'] = $this->usuario->id;
        $data['rsid'] = $facebookUser->id;
        $data['rs'] = 'facebook';
        if (isset($facebookUser->username)) {
            $data['screenname'] = $facebookUser->username;
        } else {
            $data['screenname'] = $facebookUser->name;
        }
        $red = new Application_Model_CuentaRs();
        if ($red->existeFacebook($this->usuario->id) === false) {
            $red->insert($data);
        }
        $this->_redirect('/mi-cuenta/redes-sociales');
    }

    public function agregarCuentaGoogleAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $dataGoogle = $this->getRequest()->getParams();
        $config = $this->getConfig();
        if (!isset($dataGoogle)) {
            $this->_redirect('/');
        } else {
            $data['id_usuario'] = $this->usuario->id;
            $data['rsid'] = str_replace(
                    $config->apis->google->urlResponse, "", $dataGoogle['openid_claimed_id']
            );
            $data['rs'] = 'google';
            $data['screenname'] = $dataGoogle['openid_ext1_value_email'];
            $red = new Application_Model_CuentaRs();
            if ($red->existeGoogle($this->usuario->id) === false) {
                $red->insert($data);
            }
            $this->_redirect('/mi-cuenta/redes-sociales');
        }
    }

    public function eliminarCuentaFacebookAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $usuario = $this->usuario;
        $red = new Application_Model_CuentaRs();
        $red->eliminarCuentaFacebookByUsuario($this->usuario->id);
        $this->_redirect('/mi-cuenta/redes-sociales');
    }

    public function eliminarCuentaGoogleAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $usuario = $this->usuario;
        $red = new Application_Model_CuentaRs();
        $red->eliminarCuentaGoogleByUsuario($this->usuario->id);
        $this->_redirect('/mi-cuenta/redes-sociales');
    }

    public function perfilPublicoAction() {
        $idPostulante = $this->_getParam('rel', null);

        $modelPostulante = new Application_Model_Postulante();
        $arrayPostulante = $modelPostulante->getPostulante($idPostulante);

        $slug = $arrayPostulante['slug'];
        $perfil = $this->_postulante->getPerfil($slug);
        if ($perfil == false) {
            $this->_redirect('/');
        }
        $this->view->headTitle()->set(
                'Perfil público de ' .
                $perfil['postulante']['nombres'] . ' ' .
                $perfil['postulante']['apellidos'] .
                ' | AquiEmpleos'
        );

        $this->view->headMeta()->appendName(
                "description", "Perfil Publico de " . $perfil['postulante']['nombres'] .
                " " . $perfil['postulante']['apellidos'] . " en AquiEmpleos - " .
                $perfil['postulante']['presentacion']
        );

        $keywords = "Perfil Publico de " . $perfil['postulante']['nombres'] .
                " " . $perfil['postulante']['apellidos'];

        if (count($perfil['experiencias']) > 0) {
            $experiencia = $perfil['experiencias'][0];
            $keywords .= ", " . $experiencia['puesto'] . " en " . $experiencia['empresa'];
        }

        $this->view->headMeta()->appendName("keywords", $keywords);
        $this->_helper->layout->setLayout('perfil_publico');
        Zend_Layout::getMvcInstance()->assign(
                'bodyAttr', array('id' => 'profilePublic')
        );
        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/postulante.perfil.publico.js')
        );
        $this->view->headLink()->appendStylesheet(
                $this->view->S(
                        '/css/profile.public.css')
        );
        $perfil['postulante']['fotovariable'] = $perfil['postulante']['path_foto'];
        $this->view->slug = $slug;
        $this->view->postulante = $perfil;
        if ($this->isAuth &&
                $this->auth['usuario']->rol == Application_Form_Login::ROL_POSTULANTE) {
            $usuario = $this->auth['usuario'];
            $postulante = $this->auth['postulante'];
            $correoEmisor = $usuario->email;
            $slugEmisor = $perfil['postulante']['slug'];
            $nombreEmisor = ucwords($postulante['nombres']) . ' ' .
                    ucwords($postulante['apellidos']);
        } else {
            $correoEmisor = $slugEmisor = $nombreEmisor = "";
        }
        $formCompartir = new Application_Form_CompartirPorMail();
        $formCompartir->setAction('/mi-cuenta/compartir');
        $formCompartir->correoEmisor->setValue($correoEmisor);
        $formCompartir->hdnOculto->setValue($slugEmisor);
        $formCompartir->nombreEmisor->setValue($nombreEmisor);
        Zend_Layout::getMvcInstance()->assign(
                'compartirPorMail', $formCompartir
        );
    }

    //Eliminar

    public function borrarExperienciaAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $valueExperiencia = $this->_getAllParams();

        $data = $this->EliminarElemento('Experiencia', $valueExperiencia['id']);

        //Actualizamos postulaciones.
        $helper = $this->_helper->getHelper("RegistrosExtra");
        $helper->ActualizarPostulacion($valueExperiencia['idPost']);
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarEstudioAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $valueEstudio = $this->_getAllParams();
        $data = $this->EliminarElemento('Estudio', $valueEstudio['id']);

        //Actualizamos postulaciones.
        $helper = $this->_helper->getHelper("RegistrosExtra");
        $helper->ActualizarPostulacion($valueEstudio['idPost']);

        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarIdiomaAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $valueIdioma = $this->_getAllParams();
        $data = $this->EliminarElemento('Idioma', $valueIdioma['id']);

        //Actualizamos postulaciones.
        $helper = $this->_helper->getHelper("RegistrosExtra");
        $helper->ActualizarPostulacion($valueIdioma['idPost']);

        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarProgramaAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $valuePrograma = $this->_getAllParams();
        $data = $this->EliminarElemento('ProgramaComputo', $valuePrograma['id']);

        //Actualizamos postulaciones.
        $helper = $this->_helper->getHelper("RegistrosExtra");
        $helper->ActualizarPostulacion($valuePrograma['idPost']);

        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarReferenciaAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $valueReferencia = $this->_getAllParams();

        $data = $this->EliminarElemento('Referencia', $valueReferencia['id']);
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function EliminarElemento($valor, $idElemento) {
        $var = '';

        if ($valor == 'Experiencia') {
            $var = '_eliminar' . $valor . 'Postulante';
        }
        if ($valor == 'Estudio') {
            $var = '_eliminar' . $valor . 'Postulante';
        }
        if ($valor == 'Idioma') {
            $var = '_eliminar' . $valor . 'Postulante';
        }
        if ($valor == 'ProgramaComputo') {
            $var = '_eliminar' . $valor . 'Postulante';
        }
        if ($valor == 'Referencia') {
            $var = '_eliminar' . $valor . 'Postulante';
        }
        if ($this->_request->isPost()) {
            $ok = $this->$var($idElemento);
        } else {
            $ok = false;
        }
        $data = array(
            'status' => $ok ? 'ok' : 'error',
            'msg' => $ok ? 'Se borró ok' : 'Hubo un Error'
        );

        return $data;
    }

    //funciones Privadas
    private function _crearSlug($valuesPostulante, $lastId) {
        $slugFilter = new App_Filter_Slug(
                array('field' => 'slug',
            'model' => $this->_postulante)
        );

        $slug = $slugFilter->filter(
                $valuesPostulante['nombres'] . ' ' .
                $valuesPostulante['apellidos'] . ' ' .
                substr(md5($lastId), 0, 8)
        );
        return $slug;
    }

    private function _renameFile($formPostulante, $pathFoto) {
        $file = $formPostulante->$pathFoto->getFileName();
        $nuevoNombre = '';

        if ($file != null) {
            $microTime = microtime();
            $salt = 'aptitus';
            $nombreOriginal = pathinfo($file);
            $rename = md5($microTime . $salt) .
                    '.' . $nombreOriginal['extension'];
            $nuevoNombre = $rename;
            $formPostulante->$pathFoto->addFilter('Rename', $nuevoNombre);
            $formPostulante->$pathFoto->receive();
        }

        return $nuevoNombre;
    }

    //Mantenimiento Experiencia
    private function _actualizarExperienciaPostulante(
    $managerCleanPost, $idPostulante
    ) {
        $count = 0;
        foreach ($managerCleanPost as $form) {

            $data = $form;
            $idExp = $data['id_Experiencia'];

            $experiencia = new Application_Model_Experiencia();
            if ($data['en_curso'] == 1) {
                $data['fin_mes'] = null;
                $data['fin_ano'] = null;
            }
            unset($data['id_Experiencia']);
            unset($data['is_disabled']);
            unset($data['csrfhash_a_tok']);
            
            if ($idExp) {
                $where = $experiencia->getAdapter()
                                ->quoteInto('id_postulante = ?', $idPostulante) .
                                $experiencia->getAdapter()
                                ->quoteInto(' and id = ?', $idExp);
                //if ($data['otra_empresa'] != '' || $data['otro_puesto'] != '' || $data['otro_rubro'] !='')
                if ($data['id_nivel_puesto'] != -1)
                    $experiencia->update($data, $where);
            } else {
                $data['id_postulante'] = $idPostulante;
                if ($data['id_nivel_puesto'] != -1) {
                    $experiencia->insert($data);
                }
            }
            $postulante = new Application_Model_Postulante();
            $where = $postulante->getAdapter()->quoteInto('id = ?', $idPostulante);
            $postulante->update(
                    array('ultima_actualizacion' => date('Y-m-d H:i:s'),
                'last_update_ludata' => date('Y-m-d H:i:s')
                    ), $where);
        }
        
        
          $valuesPostulante['id']= $this->idPostulante;
         $rest=  $this->_helper->LogActualizacionBI->logActualizacionPostulanteExperiencia($valuesPostulante);
        try {
          @$this->_cache->remove('Postulante_getUltimaExperiencias_'.$idPostulante);
          
        } catch (Exception $exc) { }
        
        unset($valuesPostulante['id']);
    }

    private function _eliminarExperienciaPostulante($idExperiencia) {
        if ($idExperiencia) {
            $experiencia = new Application_Model_Experiencia();
            $where = array('id=?' => $idExperiencia);
            $r = (bool) $experiencia->delete($where);
        } else {
            $r = false;
        }
        return $r;
    }

    //Mantenimiento Estudio
    private function _actualizarEstudioPostulante(
    $managerCleanPost, $idPostulante
    ) {
     $instituciones = new Application_Model_Institucion();
        $carreras = new Application_Model_Carrera();
        $listaInstituciones = $instituciones->getInstituciones();
        $listaCarreras = $carreras->getCarreras();

        $idEstudioNew = 0;
        $count = 0;
        foreach ($managerCleanPost as $form) {

            $data = $form;
            $idEst = $data['id_estudio'];
            $estudio = new Application_Model_Estudio();
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
            unset($data['csrfhash_a_tok']);

            if (!isset($data['id_institucion']) || $data['id_institucion'] == 0 || $data['id_institucion'] == '') {
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

            if ($idEst) {
                $where = $estudio->getAdapter()
                                ->quoteInto('id_postulante = ?', $idPostulante) .
                                $estudio->getAdapter()
                                ->quoteInto(' and id = ?', $idEst);
                if ($data['id_nivel_estudio'] != 0)
                    $estudio->update($data, $where);
            } else {
                $data['id_postulante'] = $idPostulante;
                
                if ($data['id_carrera'] != Application_Model_Carrera::OTRO_CARRERA)
                    $data['otro_carrera'] = '';
                
                if ($data['id_nivel_estudio'] != 0)
                    $idEstudioNew = $estudio->insert($data);
            }
        }

        $estudioModelo = new Application_Model_Estudio;
        $estudioPrincipal = $estudioModelo->obtenerEstudiosMayorPesoPorPostulante($idPostulante);

        if (!empty($estudioPrincipal)) {
            $estudioModelo->actualizarEstudioPrincipal($idPostulante, $estudioPrincipal['id']);
        }

        $postulante = new Application_Model_Postulante();
        $where = $postulante->getAdapter()->quoteInto('id = ?', $idPostulante);
        $postulante->update(
                array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')
                ), $where
        );
//        $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
//                $idPostulante, Application_Model_LogPostulante::ESTUDIOS
//        );
         $valuesPostulante['id']= $this->idPostulante;
         $rest=  $this->_helper->LogActualizacionBI->logActualizacionPostulanteEstudio($valuesPostulante);
          unset($valuesPostulante['id']);
        return $idEstudioNew;
    }

    private function _eliminarEstudioPostulante($idEstudio) {
        if ($idEstudio) {
            $estudio = new Application_Model_Estudio();
            $where = array('id=?' => $idEstudio);
            $r = (bool) $estudio->delete($where);
        } else {
            $r = false;
        }
        return $r;
    }

    //Mantenimiento Idioma
    private function _actualizarIdiomaPostulante(
    $managerCleanPost, $idPostulante
    ) {
        $count = 0;
        $idioma = new Application_Model_DominioIdioma();
        foreach ($managerCleanPost as $form) {

            $data = $form;
            $idEst = $data['id_dominioIdioma'];

            unset($data['id_dominioIdioma']);
            unset($data['cabecera_idioma']);
            unset($data['cabecera_nivel']);
            unset($data['is_disabled']);
            unset($data['csrfhash_a_tok']);

            if ($idEst) {
                $data['nivel_lee'] = $data['nivel_idioma'];
                $data['nivel_escribe'] = $data['nivel_idioma'];
                $data['nivel_hablar'] = $data['nivel_idioma'];
                unset($data['nivel_idioma']);

                $where = $idioma->getAdapter()
                                ->quoteInto('id_postulante = ?', $idPostulante) .
                                $idioma->getAdapter()
                                ->quoteInto(' and id = ?', $idEst);
                $idioma->update($data, $where);
            } else {
                $data['id_postulante'] = $idPostulante;
                $data['nivel_lee'] = $data['nivel_idioma'];
                $data['nivel_escribe'] = $data['nivel_idioma'];
                $data['nivel_hablar'] = $data['nivel_idioma'];
                unset($data['nivel_idioma']);

                $idioma->insert($data);
            }
        }
        $postulante = new Application_Model_Postulante();
        $where = $postulante->getAdapter()->quoteInto('id = ?', $idPostulante);
        $postulante->update(
                array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')
                ), $where);
    }

    private function _eliminarIdiomaPostulante($idIdioma) {
        if ($idIdioma) {
            $idioma = new Application_Model_DominioIdioma();
            $where = array('id=?' => $idIdioma);
            $r = (bool) $idioma->delete($where);
        } else {
            $r = false;
        }
        return $r;
    }

    //Mantenimiento Programa Computo
    private function _actualizarProgramaComputoPostulante(
    $managerCleanPost, $idPostulante
    ) {
        $idProgramaNew = 0;
        $count = 0;
        foreach ($managerCleanPost as $form) {

            $data = $form;
            $idEst = $data['id_dominioComputo'];

            $programa = new Application_Model_DominioProgramaComputo();
            unset($data['id_dominioComputo']);
            unset($data['cabecera_programa']);
            unset($data['cabecera_nivel']);
            unset($data['is_disabled']);
            unset($data['nombre']);
            unset($data['csrfhash_a_tok']);


            if ($data['nivel'] != -1 && $data['id_programa_computo'] != -1) {
                if ($idEst) {

                    $where = $programa->getAdapter()
                                    ->quoteInto('id_postulante = ?', $idPostulante) .
                                    $programa->getAdapter()
                                    ->quoteInto(' and id = ?', $idEst);
                    $programa->update($data, $where);
                } else {
                    $data['id_postulante'] = $idPostulante;
                    $idProgramaNew = $programa->insert($data);
                }
            }
        }
        $postulante = new Application_Model_Postulante();
        $where = $postulante->getAdapter()->quoteInto('id = ?', $idPostulante);
        $postulante->update(
                array('ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')), $where
        );
        $data['id']=$idPostulante;
        $this->_helper->LogActualizacionBI->logActualizacionPostulanteProgramas(
               $data
        );
        return $idProgramaNew;
    }

    private function _eliminarProgramaComputoPostulante($idPrograma) {
        if ($idPrograma) {
            $programa = new Application_Model_DominioProgramaComputo();
            $where = array('id=?' => $idPrograma);
            $r = (bool) $programa->delete($where);
        } else {
            $r = false;
        }
        return $r;
    }

    //Mantenimiento Referencia
    private function _actualizarReferenciaPostulante(
    $managerCleanPost, $idPostulante
    ) {
        $count = 0;
        foreach ($managerCleanPost as $form) {

            $data = $form;
            $data['id_experiencia'] = $data['listaexperiencia'];
            $idRef = $data['id_referencia'];

            $referencia = new Application_Model_Referencia();
            unset($data['id_referencia']);
            unset($data['listaexperiencia']);
            unset($data['is_disabled']);
            unset($data['csrfhash_a_tok']);
            if ($idRef) {
                $where = $referencia->getAdapter()
                        ->quoteInto('id = ?', $idRef);
                $referencia->update($data, $where);
            } else {
                $referencia->insert($data);
            }
        }
        $postulante = new Application_Model_Postulante();
        $where = $postulante->getAdapter()->quoteInto('id = ?', $idPostulante);
        $postulante->update(
                array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')
                ), $where);
    }

    private function _eliminarReferenciaPostulante($idReferencia) {
        if ($idReferencia) {
            $referencia = new Application_Model_Referencia();
            $where = array('id=?' => $idReferencia);
            $r = (bool) $referencia->delete($where);
        } else {
            $r = false;
        }
        return $r;
    }

    //Busquedas

    public function busquedaGeneralAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if (!$this->_request->isPost()) {
            //throw new Zend_Exception("Request debe ser POST");
        }
        $res = $this->_helper->autocomplete($this->_getAllParams());
        $this->_response->appendBody($res);
    }

    public function _actualizarPostulanteZendLucene($idPostulante) {
        //Actualizacion ZENDLUCENE ------------------------------------
        //POSTULANTE

        $objPostulante = new Application_Model_Postulante();
        /*

          $resultado = $objPostulante->getCaracteristicasPostulanteZendLucene($idPostulante);
          $zl = new ZendLucene();
          $arrayZL["empresa"]          =  $resultado[0]["descripcion"];
          $arrayZL["puesto"]           =  $resultado[1]["descripcion"];
          $arrayZL["estudios"]         =  $resultado[3]["descripcion"];
          $arrayZL["estudiosclaves"]   =  $resultado[4]["descripcion"];
          $arrayZL["carreraclaves"]    =  $resultado[5]["descripcion"];
          $arrayZL["experiencia"]      =  $zl->SumaCadena($resultado[6]["descripcion"], "-");
          $arrayZL["idiomas"]          =  $resultado[7]["descripcion"];
          $arrayZL["programasclaves"]  =  $resultado[8]["descripcion"];
          $arrayZL["nivelpuesto"]      =  $resultado[9]["descripcion"];
          $arrayZL["area"]             =  $resultado[10]["descripcion"];

          $zl->updateIndexPostulante($idPostulante, $arrayZL);
         */
        //-------------------------------------------------------------
    }
    public function misOtrosEstudiosAction() {
      
          $this->view->action = 'mis-otros-estudios';
        $this->view->modulo = 'admin/';
        $sess = $this->getSession();
        $this->view->urlvolver = $this->view->url($sess->postulanteAdminUrl, 'default', false);

        $this->view->menu_sel_side = self::MENU_POST_SIDE_OTROSESTUDIOS;
        $this->view->isAuth = $this->isAuth;

        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/administrador/micuenta.admin.js')
        );

        $this->view->idPostulante = $idPostulante = $this->_getParam('rel', null);

        $modelPostulante = new Application_Model_Postulante();
        $arrayPostulante = $modelPostulante->getPostulante($idPostulante);
        $this->view->slug = $arrayPostulante['slug'];
        $this->view->activo = $arrayPostulante['activo'];
        $this->view->nombres = $arrayPostulante['nombres'];
        $this->view->apellidos = $arrayPostulante['apellido_paterno'].' '.$arrayPostulante['apellido_materno'];

        $baseFormEstudio = new Application_Form_Paso2OtroEstudio(true);
        $managerEstudio = new App_Form_Manager($baseFormEstudio, 'managerEstudio', true);

        $formsEstudio = array();
        $index = 0;
        $estudio = new Application_Model_Estudio();

        $arrayEstudios = $estudio->getOtrosEstudios($idPostulante);
        if (count($arrayEstudios) != 0) {
            $tipoCarreras = Application_Model_TipoCarrera::getTiposCarrerasIds();
            $carreras = Application_Model_Carrera::getCarrerasIds();
            foreach ($arrayEstudios as $estudio) {
                   if ($estudio['en_curso'] == '1') {
                    $estudio['fin_mes'] = date('n');
                }
                if ($estudio['en_curso'] == '1') {
                    $estudio['fin_ano'] = date('Y');
                }
                if (( $estudio['id_nivel_estudio'] == 9)) {
                    if (empty($estudio['id_tipo_carrera'])){
                        $estudio['id_tipo_carrera'] = Application_Model_TipoCarrera::OTROS_TIPO_CARRERA;
                    }
                    if (empty($estudio['id_carrera']) || $estudio['id_carrera'] == "") {
                        $estudio['id_carrera'] = Application_Model_Carrera::OTRO_CARRERA;
                        if (!empty($estudio['otro_carrera'])) {
                            $carreras_flip = array_flip($carreras);
                            $estudio['id_carrera'] = ($carreras_flip[trim($estudio['otro_carrera'])]) ? $carreras_flip[trim($estudio['otro_carrera'])] : 15;
                        }
                    }
                    if ((!array_key_exists($estudio['id_tipo_carrera'], $tipoCarreras) ||
                            !array_key_exists($estudio['id_carrera'], $carreras))) {
                       // $estudio['is_disabled'] = 1;
                    }
                }
                $estudio['institucion'] = $estudio['nombre'];
                unset($estudio['nombre']);
                $form = $managerEstudio->getForm($index++, $estudio);
                /* if (isset($estudio['id_carrera'])) {
                  $carrera = new Application_Model_Carrera();
                  $idTipoCarrera = $carrera->getTipoCarreraXCarrera($estudio['id_carrera']);
                  $carreras = $carrera->filtrarCarrera($idTipoCarrera);
                  $form->getElement('id_carrera')->addMultioptions($carreras);
                  } */
                $form->setHiddenId($estudio['id_estudio']);
                $formsEstudio[] = $form;
                $this->view->isLinkedin = true;
                $this->view->isOtroEstudio = false;
            }
            $formsEstudio[] = $managerEstudio->getForm($index++);
        } else {
            $formsEstudio[] = $managerEstudio->getForm(0);
        }

        if ($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();
            $validExp = $managerEstudio->isValid($postData);
            $this->view->isLinkedin = true;
            $this->view->isOtroEstudio = false;
            if ($validExp  && $this->_hash->isValid($postData['csrfhash_a_tok'])) {
                $this->_actualizarEstudioPostulante(
                        $managerEstudio->getCleanPost(), $idPostulante
                );

                //Actualizamos postulaciones.
                $helper = $this->_helper->getHelper("RegistrosExtra");
                $helper->ActualizarPostulacion($idPostulante);


                $this->getMessenger()->success($this->_messageSuccess);
                $this->_redirect(
                        Zend_Controller_Front::getInstance()
                                ->getRequest()->getRequestUri()
                );
            } else {
                $formsEstudio = $managerEstudio->getForms();
            }
        }

        $this->view->formEstudio = $formsEstudio;
        $this->view->assign('managerEstudio', $managerEstudio);
    }
    
    public function perfilDestacadoAction() {
        
        $sess = $this->getSession();
        $this->view->urlvolver = $this->view->url($sess->postulanteAdminUrl, 'default', false);

        $this->view->modulo = $this->getRequest()->getModuleName();
        $this->view->controlador = $this->getRequest()->getControllerName();
        $this->view->rol = $this->auth['usuario']->rol;

        $this->view->menu_sel_side = self::MENU_POST_SIDE_PERFILDESTACADO;

        $this->view->idPostulante = $id = $this->_getParam('rel', null);
        
        $arrayPostulante = $this->_postulante->getPostulante($id);        
        $this->view->slug = $arrayPostulante['slug'];
        $this->view->activo = $arrayPostulante['activo'];
        $this->view->nombres = $arrayPostulante['nombres'];
        $this->view->apellidos = $arrayPostulante['apellido_paterno'].' '.$arrayPostulante['apellido_materno'];
        $this->view->emailMicuenta = $arrayPostulante['email'];


        $page = $this->_getParam('page', 1);
        $this->view->col = $col = $this->_getParam('col', '');
        $this->view->ord = $ord = $this->_getParam('ord', 'asc');
        $paginator = $this->_perfil->getPaginator(
                $id, $col, $ord
        );

        $paginado = $this->config->perfilDestacado->paginado;
        
        $paginator->setItemCountPerPage($paginado);
        $paginator->setCurrentPageNumber($page);
        $this->view->perfil = $paginator;
        $this->view->moneda = $this->config->app->moneda;
        
    }



}
