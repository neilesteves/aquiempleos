<?php

class Empresa_AvisoController extends App_Controller_Action_Empresa {

    protected $_avisoId;
    protected $_datosAviso;
    protected $_cache = null;

    public function init() {
        parent::init();
        $this->_cache = Zend_Registry::get('cache');
        $data = $this->_getAllParams();
        $this->_avisoId = $data['idPost'];

        $this->_usuario = new Application_Model_Usuario();
        if (isset($this->auth['usuario']->id) && $this->_usuario->hasvailBlokeo($this->auth['usuario']->id) ) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::forgetMe();
            $this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
            $this->_redirect('/empresa');
        }

        $anuncio = new App_Service_Validate_Ad($data['idPost']);
        $usuarioEmpresa = $this->auth['usuario-empresa'];
//echo $data['id'].'--'.var_dump($usuarioEmpresa);
        if (!$anuncio->isManaged($usuarioEmpresa)) {
            $this->getMessenger()->error($anuncio->getMessage());
            $this->_redirect('/empresa/mi-cuenta');
        }


        $aviso = new Application_Model_AnuncioWeb();
        $this->_datosAviso = $aviso->getAvisoInfoById($this->_avisoId);
        if (isset($data['idPost']) && !$this->_request->isPost()) {
            if (Application_Model_AnuncioWeb::accesoAnuncio(
                            $this->_datosAviso['id_empresa'], $this->auth
                    ) === false) {
                $this->_redirect('/empresa');
            }
        }
    }

    public function indexAction() {
        $this->view->menu_sel = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MIS_PROCESOS;
    }

    public function editarAction() {
           $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/empresa/empresa.aviso.paso2.js')
        );

        Zend_Layout::getMvcInstance()->assign(
                'bodyAttr', array('id' => 'EditarAviso', 'class' => 'noMenu')
        );

        $config = Zend_Registry::get("config");
        //@codingStandardsIgnoreStart
        $this->view->numPalabraPuesto = $config->avisopaso2->puestonumeropalabra;
        $this->view->numPalabraOtroNombre = $config->avisopaso2->mostrarnombrenumeropalabra;
        //@codingStandardsIgnoreEnd

        $aviso = new Application_Model_AnuncioWeb();

        if ($this->_getParam('back') != "") {
            $avisoBack = $this->_getParam('back');
            $this->view->back = true;
        }

        if ($this->_getParam('redirect') != "") {
            $redirect = $this->_getParam('redirect');
            $redirect = $this->_helper->Aviso->DecodeRedirect($redirect);
        }

        $formEstudio = array();
        $formOtroEstudio = array();
        $formExperiencia = array();
        $formIdioma = array();
        $formPrograma = array();
        $formPregunta = array();

        $datosAviso = $this->_datosAviso;
        $this->view->idPost = $datosAviso['id_aviso'] = $datosAviso['id'];
//        if ($datosAviso['online'] == 1 || $datosAviso['estado'] == Application_Model_AnuncioWeb::ESTADO_DADO_BAJA) {
//            $online = true;
//        } else {
//            $online = false;
//        }
        $online = false;

//        if($datosAviso['online'] == 1){
//            $pregunta=true;
//        }else{
//            $pregunta=false;
//        }
        
        $nivelPuesto = new Application_Model_NivelPuesto();
        $nivelesArea = $nivelPuesto->getNivelesByArea($datosAviso["id_area"]);

        $_niveles = array();
        foreach ($nivelesArea as $value) {            
            $_niveles[$value['id']] = $value['nombre'];
        }
        
        $pregunta=false;

        $formPuesto = new Application_Form_Paso2PublicarAviso(true, $online);
        $frmUbigeo = new Application_Form_Ubigeo($online);

        $formPuesto->salario->setAttrib("disabled", "disabled");
        $formPuesto->id_nivel_puesto->addMultiOption('-1', 'Seleccionar nivel');
        $formPuesto->id_nivel_puesto->addMultiOptions($_niveles);

        if ($datosAviso['salario'] == null && $datosAviso['salario_min'] != null) {
            $datosAviso['salario'] = $datosAviso['salario_min'] . '-max';
        }
        $formPuesto->setHiddenId($datosAviso['id']);
        if ($datosAviso['id_tarifa'] == 1) {
            $puesto = new Application_Model_Puesto();
            $listaPuesto = $puesto->getPuestos();
            $formPuesto->id_puesto->setMultiOptions($listaPuesto);
        }
        if ($datosAviso['empresa_nombre'] != $datosAviso['empresa_rs']) {
            $datosAviso['otro_nombre_empresa'] = $datosAviso['empresa_rs'];
        } else {
            $datosAviso['otro_nombre_empresa'] = $datosAviso['empresa_rs'];
            $datosAviso['mostrar_empresa'] = true;
        }

        $tarifaId = $datosAviso['id_tarifa'];

        $modelProducto = new Application_Model_Producto();
        $tipoProducto = $modelProducto->obtenerTipoAviso($tarifaId);
        $empID = Application_Model_Usuario::getEmpresaId();

        if ($empID == Application_Model_AnuncioWeb::JJC_ID) {
            if ($tipoProducto == Application_Model_AnuncioWeb::TIPO_WEB) {
                $puesto = new Application_Model_Puesto();
                $listaPuesto = $puesto->getPuestos();
                $formPuesto->id_puesto->setMultiOptions($listaPuesto);
            } elseif ($tipoProducto == Application_Model_AnuncioWeb::TIPO_PREFERENCIAL ||
                    $tipoProducto == Application_Model_AnuncioWeb::TIPO_DESTACADO){
                $formPuesto->removeTipoPuesto();
            }
        } else {
            if ($tipoProducto != Application_Model_AnuncioWeb::TIPO_CLASIFICADO) {
                $formPuesto->removeTipoPuesto();
            }

            if( $online  &&($tipoProducto == Application_Model_AnuncioWeb::TIPO_PREFERENCIAL ||
             $tipoProducto == Application_Model_AnuncioWeb::TIPO_CLASIFICADO)){
                         $formPuesto->removeTipoPuesto();
            }
        }


        $habilitaFormEstudio  = $habilitaFormExperiencia = $online;


        if ($online) {
            $tieneEstudios = $aviso->tieneAnuncioEstudios($this->_avisoId);
            if (!$tieneEstudios) {
                $habilitaFormEstudio = false;
            }

            $tieneExperiencia = $aviso->tieneAnuncioExperiencia($this->_avisoId);
            if (!$tieneExperiencia) {
                $habilitaFormExperiencia = false;
            }
        }





        $this->view->tipoAviso = $tipoProducto;
        $frmUbigeo->detalleUbigeo($datosAviso['id_ubigeo']);
        $formPuesto->isValid($datosAviso);
        $baseFormEstudio = new Application_Form_Paso2EstudioPublicar(true,false);
        $managerEstudio = new App_Form_Manager($baseFormEstudio, 'managerEstudio');

        $baseFormExperiencia = new Application_Form_Paso2ExperienciaPublicar(true, false);
        $managerExperiencia = new App_Form_Manager($baseFormExperiencia, 'managerExperiencia');

        $baseFormOtroEstudio = new Application_Form_Paso2OtroEstudioPublicar(true,$online);
        $managerOtroEstudio = new App_Form_Manager($baseFormOtroEstudio, 'managerOtroEstudio');

        $baseFormIdioma = new Application_Form_Paso2Idioma(true,$online);
        $managerIdioma = new App_Form_Manager($baseFormIdioma, 'managerIdioma');

        $baseFormPrograma = new Application_Form_Paso2Programa(true,$online);
        $managerPrograma = new App_Form_Manager($baseFormPrograma, 'managerPrograma');

        $baseFormPregunta = new Application_Form_Paso2PreguntaPublicar(true);
        $managerPregunta = new App_Form_Manager($baseFormPregunta, 'managerPregunta');

        //
        $datosAvisoEstudio = $aviso->getEstudioInfoByAnuncio($this->_avisoId);
        $datosAvisoOtroEstudio = $aviso->getOtroEstudioInfoByAnuncio($this->_avisoId);
        $datosAvisoExperiencia = $aviso->getExperienciaInfoByAnuncio($this->_avisoId);
        $datosAvisoIdioma = $aviso->getIdiomaInfoByAnuncio($this->_avisoId);
        $datosAvisoPrograma = $aviso->getProgramaInfoByAnuncio($this->_avisoId);
        $datosAvisoPreguntas = $aviso->getPreguntaInfoByAnuncio($this->_avisoId);

        if ($this->getRequest()->isPost()) {
             $postData = $this->_getAllParams();

            //Zend_Debug::dump($postData); //die();

              if($online==true && isset($redirect) && $redirect != ""){
                 $datosAviso['nombre_puesto'] = $postData['nombre_puesto'];

//                  if ($empID == Application_Model_AnuncioWeb::JJC_ID && $datosAviso['id_tarifa']==1 ) {
//                    $nompuesto=$formPuesto->id_puesto->getMultiOptions($postData['id_puesto']);
//                    $postData["nombre_puesto"] = $nompuesto[$postData['id_puesto']];
//                   }

                 if ($empID  == Application_Model_AnuncioWeb::JJC_ID && $postData['id_tarifa']==1 ) {
                    $nompuesto=$formDatos->id_puesto->getMultiOptions($postData['id_puesto']);
                    if($postData['id_puesto']==Application_Model_Puesto::OTROS_PUESTO_ID){
                        $postData["nombre_puesto"]=$postData["nombre_puesto"];
                    }else{
                        $postData["nombre_puesto"] = $nompuesto[$postData['id_puesto']];
                    }

                }

                 $datosAviso['otro_nombre_empresa'] = $postData['otro_nombre_empresa'];

                 $datosAviso['mostrar_empresa'] =  $postData['mostrar_empresa'];
                 $formPuesto->isValid($datosAviso);
                 $formPuesto->getElement('nombre_puesto')->setValue($postData["nombre_puesto"]);
                 $formPuesto->getElement('funciones')->setValue($postData['funciones']);
                 $formPuesto->getElement('responsabilidades')->setValue($postData['responsabilidades']);
                 $frmUbigeo->detalleUbigeo($datosAviso['id_ubigeo']);

                 $datosAviso['funciones']=$postData['funciones'];
                 $datosAviso['responsabilidades']=$postData['responsabilidades'];

                 $valpuesto=$formPuesto->isValidFun_Resp($datosAviso);
                 $valiUbigeo=  true;
               }else{
                   $valpuesto=$formPuesto->isValid($postData);
                   $valiUbigeo=  $frmUbigeo->isValid($postData);
               }      //print_R($datosAviso);exit;
               if($online){
         // este codio es para llenar los combos cuando hacen post
                   //estudios
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
                    }
                    $form->setElementNivelEstudio($d['id_nivel_estudio']);
                    $form->setHiddenId($d['id']);
                    $formEstudio[] = $form;
                    }
                }
                $form = $managerEstudio->getForm($i++);
                $formEstudio[] = $form;
                //experiencia

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
                //$this->view->isExperiencia= true;
                //$this->view->isExperiencia = false;
                //$this->view->isEditarExperiencia = null;

                $form = $managerExperiencia->getForm($i++);
                $formExperiencia[] = $form;

                //otros estudios
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
                //$this->view->isEstudio = true;
                //$this->view->isOtroEstudio = false;
                //$this->view->isEditarOtroEstudio = null;

                $form = $managerOtroEstudio->getForm($i++);
                $formOtroEstudio[] = $form;
           //idioma
                        $i = 0;
            if (count($datosAvisoIdioma) > 0) {
                $this->view->isIdioma = true;
                $this->view->isEditarIdioma = null;
                foreach ($datosAvisoIdioma as $d) {
                    $form = $managerIdioma->getForm($i++, $d);
                    $form->setHiddenId($d['id']);
                    $form->addValidatorsIdioma();
                    $formIdioma[] = $form;
                }
            }// else {
                //$this->view->isIdioma= true;
                //$this->view->isIdioma = false;
                //$this->view->isEditarIdioma = null;

                $form = $managerIdioma->getForm($i++);
                $formIdioma[] = $form;
            //programas
                           $i = 0;
            if (count($datosAvisoPrograma) > 0) {
                $this->view->isPrograma = true;
                $this->view->isEditarPrograma = null;
                foreach ($datosAvisoPrograma as $d) {
                    $form = $managerPrograma->getForm($i++, $d);
                    $form->setHiddenId($d['id']);
                    $form->addValidatorsPrograma();
                    $formPrograma[] = $form;
                }
            }// else {
                //$this->view->isPrograma = true;
                //$this->view->isPrograma = false;
                //$this->view->isEditarPrograma = null;

                $form = $managerPrograma->getForm($i++);
                $formPrograma[] = $form;
          // pregunta
                    $i = 0;
            if (count($datosAvisoPreguntas) > 0) {
                $this->view->isPregunta = true;
                $this->view->isEditarPregunta = null;
                foreach ($datosAvisoPreguntas as $d) {
                    $form = $managerPregunta->getForm($i++, $d);
                    $form->setHiddenId($d['id']);
                    $formPregunta[] = $form;
                }
            }// else {
                //$this->view->isPregunta = null;
                //$this->view->isEditarPregunta = null;
                $form = $managerPregunta->getForm($i++);
                if ($online == true)
                    $form->getElement('pregunta')->setAttrib("disabled", "disabled");
                $formPregunta[] = $form;

                          $validExperiencia = true;
                  $validEstudio = true;
                   $validOtroEstudio = true;
                   $validIdioma = true;
                   $validPrograma = true;
                  $validPregunta = true;
               }else{

                   $validExperiencia = $managerExperiencia->isValid($postData);
                  $validEstudio = $managerEstudio->isValid($postData);
                   $validOtroEstudio = $managerOtroEstudio->isValid($postData);
                   $validIdioma = $managerIdioma->isValid($postData);
                   $validPrograma = $managerPrograma->isValid($postData);
                  $validPregunta = $managerPregunta->isValid($postData);

               }
            $formEstudio = $managerEstudio->getForms();
            $formOtroEstudio = $managerOtroEstudio->getForms();
            $formExperiencia = $managerExperiencia->getForms();
            $formIdioma = $managerIdioma->getForms();
            $formPrograma = $managerPrograma->getForms();
           $formPregunta = $managerPregunta->getForms();

            $this->view->isEstudio = true;
            $this->view->isExperiencia = true;
            $this->view->isOtroEstudio = true;
            $this->view->isIdioma = true;
            $this->view->isPrograma = true;
            $this->view->isPregunta = true;
            if ($valpuesto &&
                $validExperiencia &&
                $validEstudio &&
                $validOtroEstudio &&
                $validIdioma &&
                $validPrograma &&
                $valiUbigeo &&$validPregunta
            ) {
                $util = $this->_helper->getHelper('Util');
                $idUbigeo = $datosAviso['id_ubigeo'];

                if($online == false){
                    $idUbigeo = $util->getUbigeo($postData);
                }                

                $avisoHelper = $genPassword = $this->_helper->getHelper('Aviso');
                $idusuario= $this->auth['usuario']->id;
                $avisoHelper->_actualizarDatosPuesto($formPuesto, $this->_avisoId, null, $idUbigeo,$idusuario);
                    if ($online != true) {
                        $avisoHelper->_actualizarEstudios($managerEstudio, $this->_avisoId);
                        $avisoHelper->_actualizarOtrosEstudios($managerOtroEstudio, $this->_avisoId);
                        $avisoHelper->_actualizarExperiencas($managerExperiencia, $this->_avisoId);
                        $avisoHelper->_actualizarIdioma($managerIdioma, $this->_avisoId);
                        $avisoHelper->_actualizarPrograma($managerPrograma, $this->_avisoId);
                        $avisoHelper->_actualizarPregunta($managerPregunta, $this->_avisoId);
                    }
                @$this->_cache->remove('AnuncioWeb_getFullAvisoById_' . $this->_avisoId);
                @$this->_cache->remove('AnuncioWeb_getAvisoInfoficha_' . $this->_avisoId);
                @$this->_cache->remove('AnuncioWeb_getAvisoById_'.$this->_avisoId);
                @$this->_cache->remove('AnuncioWeb_getAvisoInfoById_'.$this->_avisoId);
                @$this->_cache->remove('AnuncioWeb_getAvisoInfo_' . $datosAviso['url_id']);
                @$this->_cache->remove('anuncio_web_'.$datosAviso['url_id']);
//                @$this->_cache->remove('AnuncioWeb_getAvisoRelacionadosnew_' . $this->_avisoId);
//                @$this->_cache->remove('AnuncioWeb_getAvisosRelacionadosAuxiliar_' . $this->_avisoId);
                @$this->_cache->remove('AnuncioWeb_getAvisoIdByUrl_'.$datosAviso['url_id']);
                @$this->_cache->remove('AnuncioWeb_getAvisoIdByCreado_'.$datosAviso['url_id']);
                @$this->_cache->remove('Cuestionario_getPreguntasByAnuncioWeb_'.$this->_avisoId);

                if ($avisoBack && $tipoProducto == Application_Model_AnuncioWeb::TIPO_DESTACADO ) {

                    $this->_redirect('/empresa/publica-aviso-destacado/paso3/aviso/' . $this->_avisoId);
                }

                if (isset($avisoBack) && $avisoBack != "") {
                    $this->_redirect('/empresa/publica-aviso/paso3/aviso/' . $this->_avisoId);
                } elseif (isset($redirect) && $redirect != "") {
                    $this->getMessenger()->success('El aviso se modificó con éxito.');
                    $this->_redirect($redirect);
                }
            } else {

                if($online!=true  && !$managerOtroEstudio->isEmptyLastForm())
                {

                    $ind = count($managerOtroEstudio->getForms());
                    $formOtroEstudio[$ind] = $managerOtroEstudio->getForm($ind);
                }
                if($online!=true &&  /*$validExperiencia && */!$managerExperiencia->isEmptyLastForm())
                {
                    $ind = count($managerExperiencia->getForms());
                    $formExperiencia[$ind] = $managerExperiencia->getForm($ind);
                }
                if($online!=true  && !$managerIdioma->isEmptyLastForm())
                {
                    $ind = count($managerIdioma->getForms());
                    $formIdioma[$ind] = $managerIdioma->getForm($ind);
                }
                if($online!=true && /*$validPrograma && */!$managerPrograma->isEmptyLastForm())
                {
                    $ind = count($managerPrograma->getForms());
                    $formPrograma[$ind] = $managerPrograma->getForm($ind);
                }
                if($online!=true  && /*$validPregunta && */!$managerPregunta->isEmptyLastForm())
                {
                    $ind = count($managerPregunta->getForms());
                    $formPregunta[$ind] = $managerPregunta->getForm($ind);
                }
                /*$arrExp = explode(',', $postData['managerExperiencia']);
                foreach($arrExp as $index)
                    $managerExperiencia->removeForm($index);
                $arrExp = explode(',', $postData['managerEstudio']);
                foreach($arrExp as $index)
                    $managerEstudio->removeForm($index);
                $arrExp = explode(',', $postData['managerIdioma']);
                foreach($arrExp as $index)
                    $managerIdioma->removeForm($index);
                $arrExp = explode(',', $postData['managerPrograma']);
                foreach($arrExp as $index)
                    $managerPrograma->removeForm($index);*/
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
                if($online!=true &&/*$validEstudio && */!$managerEstudio->isEmptyLastForm())
                {
                    $ind = count($managerEstudio->getForms());
                    $formEstudio[$ind] = $managerEstudio->getForm($ind);
                }
            }
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
                    }
                    $form->setElementNivelEstudio($d['id_nivel_estudio']);
                    $form->setHiddenId($d['id']);
                    $formEstudio[] = $form;
                }
            }// else {
                //$this->view->isEstudio = true;
                //$this->view->isEstudio = false;
                //$this->view->isEditarEstudio = null;

                $form = $managerEstudio->getForm($i++);
                $formEstudio[] = $form;
            //}


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
                //$this->view->isEstudio = true;
                //$this->view->isOtroEstudio = false;
                //$this->view->isEditarOtroEstudio = null;

                $form = $managerOtroEstudio->getForm($i++);
                $formOtroEstudio[] = $form;



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
                //$this->view->isExperiencia= true;
                //$this->view->isExperiencia = false;
                //$this->view->isEditarExperiencia = null;

                $form = $managerExperiencia->getForm($i++);
                $formExperiencia[] = $form;



            $i = 0;
            if (count($datosAvisoIdioma) > 0) {
                $this->view->isIdioma = true;
                $this->view->isEditarIdioma = null;
                foreach ($datosAvisoIdioma as $d) {
                    $form = $managerIdioma->getForm($i++, $d);
                    $form->setHiddenId($d['id']);
                    $form->addValidatorsIdioma();
                    $formIdioma[] = $form;
                }
            }// else {
                //$this->view->isIdioma= true;
                //$this->view->isIdioma = false;
                //$this->view->isEditarIdioma = null;

                $form = $managerIdioma->getForm($i++);
                $formIdioma[] = $form;



            $i = 0;
            if (count($datosAvisoPrograma) > 0) {
                $this->view->isPrograma = true;
                $this->view->isEditarPrograma = null;
                foreach ($datosAvisoPrograma as $d) {

                    $form = $managerPrograma->getForm($i++, $d);
                    $form->setHiddenId($d['id']);
                    $form->addValidatorsPrograma();
                    $formPrograma[] = $form;
                }
            }// else {
                //$this->view->isPrograma = true;
                //$this->view->isPrograma = false;
                //$this->view->isEditarPrograma = null;

                $form = $managerPrograma->getForm($i++);
                $formPrograma[] = $form;


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
                if ($online == true)
                    $form->getElement('pregunta')->setAttrib("disabled", "disabled");
                $formPregunta[] = $form;
            //}
        }
       // echo $datosAviso['estado'];
        if($datosAviso['online'] == 1 || $datosAviso['estado'] == Application_Model_AnuncioWeb::ESTADO_DADO_BAJA){
                  $this->view->online = $online;
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

    public function republicarAction() {
        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/empresa/empresa.aviso.paso2.js')
        );

        $aviso = new Application_Model_AnuncioWeb();

        $frmUbigeo = new Application_Form_Ubigeo();
        $formEstudio = array();
        $formExperiencia = array();
        $formIdioma = array();
        $formPrograma = array();
        $formPregunta = array();

        $formPuesto = new Application_Form_Paso2PublicarAviso(true);
        $datosAviso = $this->_datosAviso;
        $datosAviso['id_aviso'] = $datosAviso['id'];
        if ($datosAviso['salario'] == null && $datosAviso['salario_min'] != null) {
            $datosAviso['salario'] = $datosAviso['salario_min'] . '-max';
        }
        $formPuesto->setHiddenId($datosAviso['id']);
        $formPuesto->isValid($datosAviso);

        $baseFormEstudio = new Application_Form_Paso2EstudioPublicar(true);
        $managerEstudio = new App_Form_Manager($baseFormEstudio, 'managerEstudio');

        $baseFormExperiencia = new Application_Form_Paso2ExperienciaPublicar(true);
        $managerExperiencia = new App_Form_Manager($baseFormExperiencia, 'managerExperiencia');

        $baseFormIdioma = new Application_Form_Paso2Idioma(true);
        $managerIdioma = new App_Form_Manager($baseFormIdioma, 'managerIdioma');

        $baseFormPrograma = new Application_Form_Paso2Programa(true);
        $managerPrograma = new App_Form_Manager($baseFormPrograma, 'managerPrograma');

        $baseFormPregunta = new Application_Form_Paso2PreguntaPublicar(true);
        $managerPregunta = new App_Form_Manager($baseFormPregunta, 'managerPregunta');

        $datosAvisoEstudio = $aviso->getEstudioInfoByAnuncio($this->_avisoId);

        if ($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();

            $validEstudio = $managerEstudio->isValid($postData);
            $validExperiencia = $managerExperiencia->isValid($postData);
            $validIdioma = $managerIdioma->isValid($postData);
            $validPrograma = $managerPrograma->isValid($postData);
            $validPregunta = $managerPregunta->isValid($postData);
            $frmUbigeo->isValid($postData);

            $formEstudio = $managerEstudio->getForms();
            $formExperiencia = $managerExperiencia->getForms();
            $formIdioma = $managerIdioma->getForms();
            $formPrograma = $managerPrograma->getForms();
            $formPregunta = $managerPregunta->getForms();

            $this->view->isEstudio = $validEstudio;
            $this->view->isExperiencia = $validExperiencia;
            $this->view->isIdioma = $validIdioma;
            $this->view->isPrograma = $validPrograma;
            $this->view->isPregunta = $validPregunta;

            if ($formPuesto->isValid($postData) &&
                    $managerEstudio->isValid($postData) &&
                    $managerExperiencia->isValid($postData) &&
                    $managerIdioma->isValid($postData) &&
                    $managerPrograma->isValid($postData) &&
                    $frmUbigeo->isValid($postData)
            ) {
                $util = $this->_helper->getHelper('Util');
                $idUbigeo = $util->getUbigeo($postData);
                $this->_actualizarDatosPuesto($formPuesto, $this->_avisoId, null, $idUbigeo);
                $this->_actualizarEstudios($managerEstudio, $this->_avisoId);
                $this->_actualizarExperiencas($managerExperiencia, $this->_avisoId);
                $this->_actualizarIdioma($managerIdioma, $this->_avisoId);
                $this->_actualizarPrograma($managerPrograma, $this->_avisoId);
                $this->_actualizarPregunta($managerPregunta, $this->_avisoId);
                $this->_redirect('/empresa/publica-aviso/paso3/aviso/' . $this->_avisoId);
            }
        } else {
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
                           $form->getElement('otra_carrera')->setValue($d['otra_carrera']);
                    }
                    $form->setHiddenId($d['id']);
                    $formEstudio[] = $form;
                }
            } else {
                $form = $managerEstudio->getForm($i++);
                $formEstudio[] = $form;
            }

            $datosAvisoExperiencia = $aviso->getExperienciaInfoByAnuncio($this->_avisoId);

            $i = 0;
            if (count($datosAvisoExperiencia) > 0) {
                $this->view->isExperiencia = false;
                foreach ($datosAvisoExperiencia as $d) {
                    $form = $managerExperiencia->getForm($i++, $d);
                    $form->setHiddenId($d['id']);
                    $formExperiencia[] = $form;
                }
            } else {
                $form = $managerExperiencia->getForm($i++);
                $formExperiencia[] = $form;
            }

            $datosAvisoIdioma = $aviso->getIdiomaInfoByAnuncio($this->_avisoId);

            $i = 0;
            if (count($datosAvisoIdioma) > 0) {
                $this->view->isIdioma = false;
                foreach ($datosAvisoIdioma as $d) {
                    $form = $managerIdioma->getForm($i++, $d);
                    $form->setHiddenId($d['id']);
                    $baseFormIdioma->addValidatorsIdioma();
                    $formIdioma[] = $form;
                }
            } else {
                $form = $managerIdioma->getForm($i++);
                $formIdioma[] = $form;
            }

            $datosAvisoPrograma = $aviso->getProgramaInfoByAnuncio($this->_avisoId);

            $i = 0;
            if (count($datosAvisoPrograma) > 0) {
                $this->view->isPrograma = false;
                foreach ($datosAvisoPrograma as $d) {
                    $form = $managerPrograma->getForm($i++, $d);
                    $form->setHiddenId($d['id']);
                    $baseFormPrograma->addValidatorsPrograma();
                    $formPrograma[] = $form;
                }
            } else {
                $form = $managerPrograma->getForm($i++);
                $formPrograma[] = $form;
            }

            $datosAvisoPregunta = $aviso->getPreguntaInfoByAnuncio($this->_avisoId);

            $i = 0;
            if (count($datosAvisoPregunta) > 0) {
                $this->view->isPregunta = false;
                foreach ($datosAvisoPregunta as $d) {
                    $form = $managerPregunta->getForm($i++, $d);
                    $form->setHiddenId($d['id']);
                    $formPregunta[] = $form;
                }
            } else {
                $form = $managerPregunta->getForm($i++);
                $formPregunta[] = $form;
            }
        }

        $this->view->form = $formPuesto;

        $this->view->formEstudio = $formEstudio;
        $this->view->assign('managerEstudio', $managerEstudio);

        $this->view->formExperiencia = $formExperiencia;
        $this->view->assign('managerExperiencia', $managerExperiencia);

        $this->view->formIdioma = $formIdioma;
        $this->view->assign('managerIdioma', $managerIdioma);

        $this->view->formPrograma = $formPrograma;
        $this->view->assign('managerPrograma', $managerPrograma);

        $this->view->formPregunta = $formPregunta;
        $this->view->assign('managerPregunta', $managerPregunta);
    }

    public function borrarExperienciaAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idExperiencia = $this->_getParam('id', false);
        if ($idExperiencia) {
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
        if (isset($r) && $r == true) {
            $data = array(
                'status' => 'ok',
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

    public function borrarEstudioAction() {    //  echo 'no entra';exit;
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idEstudio = $this->_getParam('id', false);

        if ($idEstudio) {

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
        if (isset($r) && $r == true) {
             //echo 'hola';
            $data = array(
                'status' => 'ok',
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

    public function borrarIdiomaAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idIdioma = $this->_getParam('id', false);
        if ($idIdioma) {
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
        if (isset($r) && $r == true) {
            $data = array(
                'status' => 'ok',
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

    public function borrarProgramaAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idPrograma = $this->_getParam('id', false);
        if ($idPrograma) {
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
        if (isset($r) && $r == true) {
            $data = array(
                'status' => 'ok',
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
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        try {

            $idPregunta =(int) $this->_getParam('id', false);
            if ($idPregunta) {
                $pregunta = new Application_Model_Pregunta();
                $aviso= $pregunta->getByAviso($idPregunta);
              //  $TRes=$pregunta->getRespuesta($idPregunta);
//                $where = array('id=?' =>$idPregunta);
                $r = (bool) $pregunta->deletePregunta($idPregunta);
            } else {
                 $data =  'No se pudo eliminar.'       ;
                 echo $data; exit;
            }
            if (isset($r) && $r == true) {
                $data = array(
                    'status' => 'ok',
                    'msg' => 'Se elimino con exito.'
                );
            } else {
                $data =  'No se pudo eliminar.'       ;
                 echo $data; exit;

            }
            @$this->_cache->remove('Cuestionario_getPreguntasByAnuncioWeb_'. $aviso['id']);
            @$this->_cache->remove('anuncio_web_' . $aviso['url_id']);
        } catch (Exception $exc) {
          $data =  'No se pudo eliminar.'       ;

          $this->log->log($exc->getMessage().'. '.$exc->getTraceAsString(),Zend_Log::ERR);
           echo $data; exit;
        }
        $this->_response->appendBody(Zend_Json::encode($data));

    }

}

//modificado
