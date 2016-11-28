<?php

class Postulante_MiCuentaController extends App_Controller_Action_Postulante
{
    protected $_experiencia;
    protected $_dominioIdioma;
    protected $_postulante;
    protected $_perfil;
    protected $_anuncioWeb;
    protected $_usuario;
    protected $_messageSuccess           = 'Sus datos se cambiaron con éxito.';
    protected $_messageSuccessActualizar = 'Sus datos se cambiaron con éxito.';
    protected $_messageSuccessRegistrar  = 'Los datos fueron actualizados correctamente.';
    protected $_messageFail              = 'Ocurrió un error al actualizar sus datos.';
    protected $_cache;
    protected $_config;
    protected $_getPostulante;
    protected $_submenuPrivacidad;
    protected $_submenuMiCuenta;
    protected $_slug;
    public $_messageError;

    const TOLERANCIA_LEVENSHTEIN_IDIOMA        = 5;
    const TOLERANCIA_LEVENSHTEIN_CARRERA       = 3;
    const TOLERANCIA_LEVENSHTEIN_NIVEL_ESTUDIO = 3;
    const TOLERANCIA_LEVENSHTEIN_INST          = 6;

    public $_meses;
    protected $idPostulante;
    protected $linkedImported;

    public function init()
    {
        parent::init();

        /* Initialize action controller here */
        $this->_experiencia   = new Application_Model_Experiencia();
        $this->_dominioIdioma = new Application_Model_DominioIdioma();
        $this->_postulante    = new Application_Model_Postulante();
        $this->_usuario       = new Application_Model_Usuario();
        $this->_perfil        = new Application_Model_PerfilDestacado();
        $this->_anuncioWeb    = new Application_Model_AnuncioWeb();
        $this->_messageError  = 'Acceso denegado';
        $this->_config        = Zend_Registry::get('config');
        $this->_cache         = Zend_Registry::get('cache');
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array(
            'id' => 'myAccount')
        );

        $this->idPostulante = null;

        if ($this->isAuth &&
            $this->auth['usuario']->rol == Application_Form_Login::ROL_POSTULANTE) {
            $this->idPostulante = $this->auth['postulante']['id'];
            $this->_slug        = $this->auth['postulante']['slug'];

            $this->_getPostulante = $this->_postulante->getPostulante($this->idPostulante);
            $information          = new Zend_Session_Namespace('information');

            if (isset($information->information)) {
                $information->information = false;
            } else {
                $information->information = true;
            }
            //  var_dump($information->information);exit;
            Zend_Layout::getMvcInstance()->assign(
                'layoutPostulante', $information->information
            );
        }
        $this->usuario = (isset($this->auth['usuario'])) ? $this->auth['usuario']
                : '';

        $this->_submenuMiCuenta = array(
            array(
                'href' => 'mi-cuenta',
                'value' => 'Mis Datos Personales',
                'action' => 'mis-datos-personales'),
            array(
                'href' => 'postulaciones',
                'value' => 'Mis Postulaciones',
                'action' => 'index'),
            array(
                'href' => 'notificaciones',
                'value' => 'Mis Notificaciones',
                'action' => 'index'),
            array(
                'href' => 'mi-cuenta',
                'value' => 'Cómo destacar más',
                'action' => 'perfil-destacado'),
        );

        $this->_submenuPrivacidad = array(
            array(
                'href' => 'mis-alertas',
                'value' => 'Alertas'),
            array(
                'href' => 'cambio-de-clave',
                'value' => 'Contraseña'),
            array(
                'href' => 'privacidad',
                'value' => 'Privacidad'),
            array(
                'href' => 'eliminar-cuenta',
                'value' => 'Eliminar Cuenta'),
        );

        $this->_meses = $this->_config->meses->mes->toArray();

        //  $this->linkedImported = new Mongo_ImportadosLinkedIn();
    }

    public function exportaPdfAction()
    {

        $slug = $this->_getParam('slug');
        if ($slug) {

            $htmlFilter = new Zend_Filter_HtmlEntities();
            $tagFilter  = new Zend_Filter_StripTags();
            $slug       = $htmlFilter->filter($slug);
            $slug       = $tagFilter->filter($slug);

            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();

            $domPdf = $this->_helper->getHelper('DomPdf');

            Zend_Layout::getMvcInstance()->assign(
                'bodyAttr', array(
                'id' => 'profilePublic')
            );
            $this->view->headLink()->appendStylesheet(
                $this->view->S(
                    '/css/profile.pdf.css')
            );
            $headLinkContainer = $this->view->headLink()->getContainer();
            unset($headLinkContainer[0]);
            unset($headLinkContainer[1]);


            $postulante = $this->_postulante->getPostulantePerfil($slug);

            if (!$postulante) {
                $this->_redirect('/');
            }

            $arNombreFile = explode('-', $slug);
            unset($arNombreFile[count($arNombreFile) - 1]);
            $nombreFile   = implode(' ', $arNombreFile);
            $nombreFile   = ucwords($nombreFile);
            $nombreFile   = str_replace(' ', '-', $nombreFile).'.pdf';


            $id = $postulante['idpostulante'];

            $perfil                               = $this->_postulante->getPerfil($id);
            $edad                                 = date('Y') - date("Y",
                    strtotime($perfil['postulante']['fecha_nac']));
            $perfil['postulante']['fotovariable'] = $perfil['postulante']['path_foto'];
            $perfil['postulante']['edad']         = $edad;
            $this->view->postulante               = $perfil;


            $html   = $this->view->render('mi-cuenta/exporta-pdf.phtml');
            $mvc    = Zend_Layout::getMvcInstance();
            //$this->_helper->layout->setLayout('perfil_publico');
            $layout = $mvc->render('perfil_publico_pdf');

            $layout = str_replace("<!--perfil-->", $html, $layout);
            $layout = str_replace("\"", "'", $layout);
            //echo $layout;exit;

            $domPdf->mostrarPDF($layout, 'A4', "portrait", $nombreFile);
        } else {
            $this->_redirect('/');
        }
    }

    public function exportaPdfNewAction()
    {

        $slug = $this->_getParam('slug');
        if ($slug) {

            $htmlFilter = new Zend_Filter_HtmlEntities();
            $tagFilter  = new Zend_Filter_StripTags();
            $slug       = $htmlFilter->filter($slug);
            $slug       = $tagFilter->filter($slug);

            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();

            $domPdf = $this->_helper->getHelper('DomPdf');

            Zend_Layout::getMvcInstance()->assign(
                'bodyAttr', array(
                'id' => 'profilePublic')
            );
            $this->view->headLink()->appendStylesheet(
                $this->view->S(
                    '/css/profile.pdf.css')
            );
            $this->view->headLink()->appendStylesheet(
                $this->view->S(
                    '/main/css/modules/postulante/all.css')
            );
            $this->view->headLink()->appendStylesheet(
                $this->view->S(
                    '/main/css/modules/postulante/mi-cuenta.css')
            );
            $headLinkContainer = $this->view->headLink()->getContainer();
            unset($headLinkContainer[0]);
            unset($headLinkContainer[1]);


            $postulante = $this->_postulante->getPostulantePerfil($slug);

            if (!$postulante) {
                $this->_redirect('/');
            }

            $arNombreFile = explode('-', $slug);
            unset($arNombreFile[count($arNombreFile) - 1]);
            $nombreFile   = implode(' ', $arNombreFile);
            $nombreFile   = ucwords($nombreFile);
            $nombreFile   = str_replace(' ', '-', $nombreFile).'.pdf';


            $id = $postulante['idpostulante'];

            $perfil = $this->_postulante->getPerfilPostulante($id);

            // var_dump($perfil);exit;
            $edad                                 = date('Y') - date("Y",
                    strtotime($perfil['postulante']['fecha_nac']));
            // var_dump($edad );exit;
            $perfil['postulante']['fotovariable'] = $perfil['postulante']['path_foto'];
            $perfil['postulante']['edad']         = $edad;
            $this->view->postulante               = $perfil;

            $html   = $this->view->render('mi-cuenta/exporta-pdf-new.phtml');
            die($html);
            exit;
            $mvc    = Zend_Layout::getMvcInstance();
            //$this->_helper->layout->setLayout('perfil_publico');
            $layout = $mvc->render('perfil_publico_pdf');

            $layout = str_replace("<!--perfil-->", $html, $layout);
            $layout = str_replace("\"", "'", $layout);
            //echo $layout;exit;

            $domPdf->mostrarPDF($layout, 'A4', "portrait", $nombreFile);
        } else {
            $this->_redirect('/');
        }
    }

    public function indexAction()
    {

        $this->_redirect(SITE_URL.'/mi-cuenta/mis-datos-personales');

        $params = $this->_getAllParams();

        $sess              = $this->getSession();
        $sess->micuentaUrl = null;
        $sess->micuentaUrl = $params;


        //Muestra mensaje de Bienvenida
        $sesionMsg = new Zend_Session_Namespace("msg_welcome");
        $sessionfb = new Zend_Session_Namespace('ulrDesFb');
        if (isset($sessionfb->urlFb)) {
            unset($sessionfb->urlFb);
        }
        if (isset($sesionMsg->welcome)) {

            $this->getMessenger()->success("¡Bienvenido!");
            unset($sesionMsg->welcome);
        }



        $verNotificacionAnuncios               = $this->config->profileMatch->postulante->notificaciones;
        $this->view->verNotificacionesAnuncios = $verNotificacionAnuncios;

        if ($verNotificacionAnuncios == 1) {
            $objAnuncioMatch    = new Application_Model_AnuncioPostulanteMatch();
            $this->view->result = $result             = $objAnuncioMatch->getAnunciosSugeridos(
                $this->auth['postulante']['id'],
                $this->config->profileMatch->postulante->nroanuncios
            );
            //modificar
            if ($result) {
                $this->view->listaAnunciosSugeridos = $result;
            }
        }

        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_INICIO;
        $this->view->isAuth        = $this->isAuth;

        if ($this->isAuth) {
            $id              = $this->idPostulante;
            $arrayPostulante = $this->_postulante->getPostulante($id);

            // Porcentajes de perfil completo
//            $porcCV = new App_Controller_Action_Helper_PorcentajeCV();
//            $porcentajes = $porcCV->getPorcentajes($arrayPostulante);
            $porcentajes             = $this->_helper->getHelper('PorcentajeCV')->getPorcentajes($arrayPostulante);
            $this->view->porcentaje  = $porcentajes['total_completado'];
            $this->view->incompletos = $porcentajes['total_incompleto'];

            $this->view->imgPhoto  = $arrayPostulante["path_foto"];
            $this->view->nombres   = $arrayPostulante["nombres"];
            $this->view->apellidos = $arrayPostulante["apellido_paterno"].' '.$arrayPostulante['apellido_materno'];
            $this->view->var       = $this->config->dashboard;
            $this->view->slug      = $arrayPostulante["slug"];

            $_mensaje             = new Application_Model_Mensaje();
            //mispostulaciones
            $this->_postulaciones = new Application_Model_Postulacion();
            $sql                  = $this->_postulaciones->
                getPostulaciones($id,
                $this->config->dashboard->npostulaciones_mostrar);

            $this->view->postulaciones = $this->_postulaciones->getAdapter()->fetchAll($sql);

            $this->view->notif_total     = $arrayPostulante["notif_leidas"] +
                $arrayPostulante["notif_no_leidas"];
            $this->view->notif_leidas    = $arrayPostulante["notif_leidas"];
            $this->view->notif_no_leidas = $arrayPostulante["notif_no_leidas"];

            //estadisticas
            $es                       = $_mensaje->getEstadisticasMsgPostulacion($this->idPostulante);
            $this->view->estadisticas = $es;

            //misnotificaciones
            $sql                        = $_mensaje->getMensajesNotificacion($arrayPostulante["idusuario"]);
            $this->view->notificaciones = $_mensaje->getAdapter()->fetchAll($sql);
            $modelVisita                = new Application_Model_Visitas();
            if (!empty($this->auth['postulante']['destacado'])) {
                $vis = $modelVisita->getVisitas($this->auth['postulante']['id'],
                    1);
                $bus = $modelVisita->getVisitas($this->auth['postulante']['id'],
                    2);
            } else {
                $vis = 0;
                $bus = 0;
            }
            $this->view->vis       = $vis;
            $this->view->bus       = $bus;
            $this->view->destacado = $this->auth['postulante']['destacado'];
        }
    }

    public function misDatosPersonalesAction()
    {
        $this->view->idPostulante = $id                       = $this->idPostulante;
        $session                  = new Zend_Session_Namespace('linkedin');
        $this->view->showImport   = false;
        Zend_Layout::getMvcInstance()->assign(
            'submenuMiCuenta', $this->_submenuMiCuenta
        );

        $importoDatos                      = true;
        $this->view->modalInfoUpdatePerfil = true;



//        $importoDatos = $this->linkedImported
//                ->isImported('datos-personales',$this->idPostulante);
//        if ($importoDatos === false) {
//            $this->view->showImport = true;
//            if (isset($session->linkedin)) {
//                $this->getMessenger()->success(
//                        "Se logró importar tus datos de linkedin."
//                );
//
//                $linkedinData = $session->linkedin;
//                $this->_linkedinSaveDatos($linkedinData,$id);
//                unset($session->linkedin);
//                $this->_redirect('/mi-cuenta/mis-datos-personales');
//            }
//        }

        $arrayPostulante = $this->_postulante->getPostulante($id);

        $arrayPostulante['fecha_nac'] = date('d/m/Y',
            strtotime($arrayPostulante['fecha_nac']));
        $img                          = $this->view->imgPhoto         = !empty($arrayPostulante['path_foto_dos'])
                ? $arrayPostulante['path_foto_dos'] : null;
        $arrayPostulante['fecha_nac'] = str_replace('-', '/',
            $arrayPostulante['fecha_nac']);
        $formPostulante               = new Application_Form_PostulanteDatosPersonales($id,
            $arrayPostulante);
        $formPostulante->setDefaults($arrayPostulante);
        $formPostulante->getElement('txtFirstLastName')->setValue($arrayPostulante['apellido_paterno']);
        $formPostulante->getElement('txtSecondLastName')->setValue($arrayPostulante['apellido_materno']);
        $formPostulante->getElement('txtBirthDay')->setValue($arrayPostulante['fecha_nac']);
        $formPostulante->getElement('selDocument')->setValue($arrayPostulante['tipo_doc']);
        $formPostulante->getElement('txtDocument')->setValue($arrayPostulante['num_doc']);
        $formPostulante->getElement('selSex')->setValue($arrayPostulante['sexoMF']);
        $formPostulante->getElement('txtTelephone')->setValue($arrayPostulante['telefono']);
        $formPostulante->getElement('txtCellphone')->setValue($arrayPostulante['celular']);
        $formPostulante->getElement('selMAritalStatus')->setValue($arrayPostulante['estado_civil']);
        if (!empty($arrayPostulante['discapacidad'])) {
            $formPostulante->getElement('chkIncapacity')->setValue(true);
            $formPostulante->getElement('selDisability')->setValue($arrayPostulante['discapacidad']);
        }
        if (!empty($arrayPostulante['conadis_code'])) {
            $formPostulante->getElement('chkConadis')->setValue(true);
            $formPostulante->getElement('txtconadisCode')->setValue($arrayPostulante['conadis_code']);
        }
        $this->view->dataperfil     = $arrayPostulante;
        $this->view->slug           = $this->auth['postulante']['slug'];
        $this->view->formPostulante = $formPostulante;
        $this->view->auth           = $this->auth;
    }

    public function misDatosPersonalesAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $id = isset($this->auth["postulante"]["id"]) ? $this->auth["postulante"]["id"]
                : 0;

        $allParams = $this->_getAllParams();
        $XSS       = new App_Util();
        $data      = $XSS->clearXSS($allParams);

        $form        = new Application_Form_PostulanteDatosPersonales($id, null);
        $data['id']  = $id;
        $isValidForm = $form->isValid($data);

        $docRepetido = 0;
        if (isset($data["txtDocument"]) && isset($data["selDocument"])) {
            $docRepetido = Application_Model_Postulante::verificarDocRepetido($data["txtDocument"],
                    $data["selDocument"], $id);
        }
        $ImgInvalida = ($data['corrupted'] == 1) ? 1 : 0;

        if ($isValidForm && $id && !$docRepetido && !$ImgInvalida) {
            $nuevosNombres = array();
            if (count($_FILES) > 0) {
                $utilfile      = $this->_helper->getHelper('UtilFiles');
                $nuevosNombres = $utilfile->_renameFile($form, 'path_foto');
            }

            try {
                if (!isset($data['chkIncapacity'])) {
                    $data['chkIncapacity'] = 0;
                }

                $data['nombres']           = ucwords($data['nombres']);
                $data['txtFirstLastName']  = ucwords($data['txtFirstLastName']);
                $data['txtSecondLastName'] = ucwords($data['txtSecondLastName']);

                $data['txtBirthDay'] = str_replace('/', '-',
                    $data['txtBirthDay']);

                $valuesPostulante                     = array();
                $valuesPostulante['nombres']          = trim($data['nombres']);
                $valuesPostulante['apellido_paterno'] = trim($data['txtFirstLastName']);
                $valuesPostulante['apellido_materno'] = trim($data['txtSecondLastName']);
                $valuesPostulante['apellidos']        = $valuesPostulante['apellido_paterno'].' '.$valuesPostulante['apellido_materno'];
//                $valuesPostulante['fecha_nac'] = date( 'Y-m-d', strtotime(
//                            date( 'd/m/Y', strtotime($data['txtBirthDay']))));
                $valuesPostulante['fecha_nac']        = date('Y-m-d',
                    strtotime(
                        date('Y', strtotime($data['txtBirthDay'])).'-'.
                        date('m', strtotime($data['txtBirthDay'])).'-'.
                        date('d', strtotime($data['txtBirthDay']))));

                $valuesPostulante['tipo_doc']     = $data["selDocument"];
                $valuesPostulante['num_doc']      = $data["txtDocument"];
                $valuesPostulante['sexo']         = $data["selSex"];
                $valuesPostulante['telefono']     = $data["txtTelephone"];
                $valuesPostulante['celular']      = $data["txtCellphone"];
                $valuesPostulante['estado_civil'] = $data["selMAritalStatus"];
                $valuesPostulante['discapacidad'] = 0;
                if ($data['chkIncapacity'] == 'on' || $data['chkIncapacity']) {
                    $valuesPostulante['discapacidad'] = isset($data['selDisability'])
                            ? $data['selDisability'] : 0;
                    $valuesPostulante['conadis_code'] = isset($data["txtconadisCode"])
                            ? $data["txtconadisCode"] : null;
                }
                if (empty($data['chkIncapacity'])) {
                    $valuesPostulante['discapacidad'] = isset($data['selDisability'])
                            ? $data['selDisability'] : 0;
                    $valuesPostulante['conadis_code'] = isset($data["txtconadisCode"])
                            ? $data["txtconadisCode"] : null;
                }
                if (isset($nuevosNombres[0]) && isset($nuevosNombres[1]) && isset($nuevosNombres[2])) {
                    $valuesPostulante['path_foto']  = $nuevosNombres[0];
                    $valuesPostulante['path_foto1'] = $nuevosNombres[1];
                    $valuesPostulante['path_foto2'] = $nuevosNombres[2];
                }
                if ($data['flagDelete'] == 1) {
                    $valuesPostulante['path_foto']  = null;
                    $valuesPostulante['path_foto1'] = null;
                    $valuesPostulante['path_foto2'] = null;
                }

                $where = $this->_postulante->getAdapter()
                    ->quoteInto('id = ?', $id);

                $valuesPostulante['ultima_actualizacion'] = date('Y-m-d H:i:s');
                $res                                      = $this->_postulante->update($valuesPostulante,
                    $where);

                $helper = $this->_helper->getHelper('RegistrosExtra');
                $helper->ActualizarPostulacion($id);
                $this->_helper->solr->addSolr($id);

                $valuesPostulante['id'] = $id;
                $helperSolr             = $this->_helper->getHelper("LogActualizacionBI");
                unset($valuesPostulante['discapacidad']);
                unset($valuesPostulante['conadis_code']);
                $dataDashwood           = $helperSolr->logActualizacionPostulantePerfil($valuesPostulante);
                if (!isset($valuesPostulante['path_foto'])) {
                    if (isset($this->auth["postulante"]["path_foto"])) {
                        $valuesPostulante['path_foto']  = $this->auth["postulante"]["path_foto"];
                        $valuesPostulante['path_foto1'] = $this->auth["postulante"]["path_foto1"];
                        $valuesPostulante['path_foto2'] = $this->auth["postulante"]["path_foto2"];
                    } else {
                        $valuesPostulante['path_foto']  = null;
                        $valuesPostulante['path_foto1'] = null;
                        $valuesPostulante['path_foto2'] = null;
                    }
                }


                $form->getElement('hidToken')->initCsrfToken();
                $response['token']                         = $form->getElement('hidToken')->getValue();
                $response['status']                        = 1;
                $response['message']                       = 'Los datos fueron actualizados correctamente';
                $response['skill']                         = $data;
                $response['iscompleted']                   = array(
                    "Datos Personales",
                    ($dataDashwood['iscompleted']) ? 1 : 0);
                $response['percent']                       = $dataDashwood['total_completado'];
                $storage                                   = Zend_Auth::getInstance()->getStorage()->read();
                $storage['postulante']['nombres']          = $valuesPostulante['nombres'];
                $storage['postulante']['apellido_paterno'] = $valuesPostulante['apellido_paterno'];
                $storage['postulante']['apellido_materno'] = $valuesPostulante['apellido_materno'];
                $storage['postulante']['sexo']             = $valuesPostulante['sexo'];
                $storage['postulante']['tipo_doc']         = $valuesPostulante['tipo_doc'];
                $storage['postulante']['num_doc']          = $valuesPostulante['num_doc'];
                $storage['postulante']['path_foto']        = $valuesPostulante['path_foto'];
                $storage['postulante']['path_foto1']       = $valuesPostulante['path_foto1'];
                $storage['postulante']['path_foto2']       = $valuesPostulante['path_foto2'];
                Zend_Auth::getInstance()->getStorage()->write($storage);
//               @$this->_cache->remove('get_perfil_postulante_' . str_replace('-', '_', $this->_slug));
//               @$this->_cache->remove('get_perfil_postulante_' . str_replace('-', '_', $this->idPostulante));
//               @$this->_cache->remove('perfil_postulante_' . str_replace('-', '_', $this->_slug));
//               @$this->_cache->remove('perfil_postulante_' . str_replace('-', '_', $id));
            } catch (Exception $exc) {
                $this->log->log($exc->getMessage().'. '.$exc->getTraceAsString(),
                    Zend_Log::ERR);
                $response = array(
                    'status' => 0,
                    'message' => 'No se pudo realizar la operación, por favor vuelva a intentarlo.');
            }
        } elseif ($docRepetido) {
            $form->getElement('hidToken')->initCsrfToken();
            $msg               = Zend_Json::encode('El documento de identidad ya existe');
            $response          = array(
                'status' => '0',
                'message' => $msg);
            $response['token'] = $form->getElement('hidToken')->getValue();
        } elseif ($ImgInvalida) {
            $form->getElement('hidToken')->initCsrfToken();
            $msg               = Zend_Json::encode('El archivo de imagen es irreconocible');
            $response          = array(
                'status' => '0',
                'message' => $msg);
            $response['token'] = $form->getElement('hidToken')->getValue();
        } else {
            $msg       = '';
            $txtFields = array(
                'nombres',
                'txtFirstLastName',
                'txtSecondLastName');
            foreach ($form->getErrors() as $key => $value) {
                if (count($value) > 0 && $key != 'hidToken') {
                    for ($i = 0; $i < count($value); $i++) {
                        if (in_array($key, $txtFields)) {
                            $data = array(
                                $key => Zend_Json::encode(htmlentities(Application_Form_PostulanteDatosPersonales::$errorsTxt[$value[$i]],
                                        ENT_HTML401, 'UTF-8')));
                        } else {
                            $data = array(
                                $key => Zend_Json::encode(htmlentities(Application_Form_PostulanteDatosPersonales::$errors[$value[$i]],
                                        ENT_HTML401, 'UTF-8')));
                        }
                    }
                    $msg.= Zend_Json::encode($data);
                }
            }
            $labels = array(
                'nombres' => 'Nombres',
                'txtFirstLastName' => 'Apellido paterno',
                'txtSecondLastName' => 'Apellido materno',
                'txtBirthDay' => 'Fecha de nacimiento',
                'txtDocument' => 'Número de documento',
                'txtTelephone' => 'Teléfono Fijo',
                'txtCellphone' => 'Celular',
                'path_foto' => 'Foto');
            foreach ($labels as $key => $value) {
                $msg = str_replace(":", ": ",
                    preg_replace('~[\\\\{}"]~', '',
                        str_replace('}{', ' | ', str_replace($key, $value, $msg))));
                $msg = str_replace($key, $value, $msg);
            }

            if (strpos($msg, 'Fecha de nacimiento')) {
                $msg = 'Su edad debe ser mayor de 18 años';
            }

            $form->getElement('hidToken')->initCsrfToken();
            $response          = array(
                'status' => '0',
                'message' => $msg);
            $response['token'] = $form->getElement('hidToken')->getValue();
        }
        $this->_response->appendBody(Zend_Json::encode($response));
    }

    public function misExperienciasAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $this->view->menu_sel_side = self::MENU_POST_SIDE_EXPERIENCIA;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;

        $hash = $this->_getParam('csrfhash');

        $this->view->headScript()->appendScript('urls.experienceF = "/mi-cuenta/borrar-experiencia-ajax";');

        $idPostulante = $this->idPostulante;

        $baseFormExperiencia = new Application_Form_Paso2Experiencia(true);
        $managerExperiencia  = new App_Form_Manager($baseFormExperiencia,
            'managerExperiencia', true);


        $formsExperiencia = array();
        $index            = 0;

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
                if (strlen($experiencia['comentarios']) > 140) {
                    $experiencia['comentarios'] = substr($experiencia['comentarios'],
                        0, 140);
                }
                $form                      = $managerExperiencia->getForm($index++,
                    $experiencia);
                $form->setHiddenId($experiencia['id_Experiencia']);
                $formsExperiencia[]        = $form;
                $this->view->isExperiencia = true;
                $this->view->isLinkedin    = true;
            }
            $formsExperiencia[] = $managerExperiencia->getForm($index++);
        } else {
            $formsExperiencia[] = $managerExperiencia->getForm(0);
        }

        if ($this->getRequest()->isPost() && $this->_hash->isValid($hash)) {
//        if ($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();

            $validExp = $managerExperiencia->isValid($postData);

            $this->view->isExperiencia = true;
            $this->view->isLinkedin    = true;
            if ($validExp) {
                $idExperienciaNew = $this->_actualizarExperienciaPostulanteAjax(
                    $managerExperiencia->getCleanPost(), $idPostulante);
                //Actualizamos postulaciones.
                $helper           = $this->_helper->getHelper("RegistrosExtra");
                $helper->ActualizarPostulacion($idPostulante);
                $helper->ActualizarExperiencias($idPostulante);

                //ACTUALIZACION DE ZENDLUCENE
                $this->_actualizarPostulanteZendLucene($idPostulante);

                $id                                       = $this->idPostulante;
                $where                                    = $this->_postulante->getAdapter()
                    ->quoteInto('id = ?', $id);
//                $db = $this->getAdapter();
//                $db->beginTransaction();
                $valuesPostulante                         = array();
                $date                                     = date('Y-m-d H:i:s');
                $valuesPostulante['ultima_actualizacion'] = $date;
                $valuesPostulante['last_update_ludata']   = $date;
                $this->_postulante->update($valuesPostulante, $where);

                echo Zend_Json::encode(array(
                    'id' => $idExperienciaNew,
                    'message' => $this->_messageSuccess,
                    'csrfhash' => CSRF_HASH));
            } else {
                $formsExperiencia = $managerExperiencia->getForms();
            }
        }
//        $this->view->formExperiencia = $formsExperiencia;
//        $this->view->assign('managerExperiencia', $managerExperiencia);
//
//        echo Zend_Json::encode(array(
//            'message' => $this->_messageFail,
//             'csrfhash' => CSRF_HASH));
    }

    public function misEstudiosAjaxOldAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $this->view->menu_sel_side = self::MENU_POST_SIDE_ESTUDIOS;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;

        $hash = $this->_getParam('csrfhash');

        $this->view->headScript()
            ->appendScript('urls.studyF = "/mi-cuenta/borrar-estudio";');

        $idPostulante = $this->idPostulante;

        $baseFormEstudio = new Application_Form_Paso2Estudio(true);
        $managerEstudio  = new App_Form_Manager($baseFormEstudio,
            'managerEstudio', true);


        $formsEstudio = array();
        $index        = 0;
        $estudio      = new Application_Model_Estudio();

        $arrayEstudios = $estudio->getEstudios($idPostulante);
        if (count($arrayEstudios) != 0) {
            $tipoCarreras = Application_Model_TipoCarrera::getTiposCarrerasIds();
            $carreras     = Application_Model_Carrera::getCarrerasIds();
            foreach ($arrayEstudios as $estudio) {
                if ($estudio['en_curso'] == '1') {
                    $estudio['fin_mes'] = date('n');
                }
                if ($estudio['en_curso'] == '1') {
                    $estudio['fin_ano'] = date('Y');
                }
                if (( $estudio['id_nivel_estudio'] != 1 &&
                    $estudio['id_nivel_estudio'] != 2 &&
                    $estudio['id_nivel_estudio'] != 3 &&
                    $estudio['id_nivel_estudio'] != 9)) {
                    if (empty($estudio['id_carrera']) || $estudio['id_carrera'] == "") {
                        $estudio['id_carrera'] = 15;
                    }
                    if ((!array_key_exists($estudio['id_tipo_carrera'],
                            $tipoCarreras) ||
                        !array_key_exists($estudio['id_carrera'], $carreras))) {
                        $estudio['is_disabled'] = 1;
                    }
                }
                $estudio['institucion'] = $estudio['nombre'];
                unset($estudio['nombre']);
                $form                   = $managerEstudio->getForm($index++,
                    $estudio);
                /* if (isset($estudio['id_carrera'])) {
                  $carrera = new Application_Model_Carrera();
                  //$form->getElement('otro_carrera')->setValue($carrera->getCarreraById($estudio['id_carrera']));
                  } */
                $form->setHiddenId($estudio['id_estudio']);
                $formsEstudio[]         = $form;
                $this->view->isLinkedin = true;
                $this->view->isEstudio  = true;
            }
            $formsEstudio[] = $managerEstudio->getForm($index++);
        } else {
            $formsEstudio[] = $managerEstudio->getForm(0);
        }

        if ($this->getRequest()->isPost() && $this->_hash->isValid($hash)) {
//        if ($this->getRequest()->isPost()) {
            $postData               = $this->_getAllParams();
            $validExp               = $managerEstudio->isValid($postData);
            $this->view->isLinkedin = true;
            $this->view->isEstudio  = true;
            if ($validExp) {
                $idEstudioNew = 0;
                $idEstudioNew = $this->_actualizarEstudioPostulanteAjax(
                    $managerEstudio->getCleanPost(), $idPostulante);

                $helper = $this->_helper->getHelper("RegistrosExtra");
                $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);
                $helper->ActualizarEstudios($this->auth["postulante"]["id"]);

                echo Zend_Json::encode(array(
                    'id' => $idEstudioNew,
                    'message' => $this->_messageSuccess,
                    'csrfhash' => CSRF_HASH));
                exit;
//                $this->getMessenger()->success($this->_messageSuccess);
//                $this->_redirect(
//                    Zend_Controller_Front::getInstance()
//                        ->getRequest()->getRequestUri()
//                );
            } else {
                $formsEstudio = $managerEstudio->getForms();
            }
        }

        $this->view->formEstudio = $formsEstudio;
        $this->view->assign('managerEstudio', $managerEstudio);

        echo Zend_Json::encode(array(
            'message' => $this->_messageFail,
            'csrfhash' => CSRF_HASH));
    }

    public function misIdiomasAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $hash = $this->_getParam('csrfhash');

        $this->view->headScript()
            ->appendScript('urls.languagesF = "/mi-cuenta/borrar-idioma-ajax";');

        $this->view->menu_sel_side = self::MENU_POST_SIDE_IDIOMAS;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;

        $idPostulante = $this->idPostulante;

        $baseFormIdioma = new Application_Form_Paso2Idioma(true);
        $managerIdioma  = new App_Form_Manager($baseFormIdioma, 'managerIdioma',
            true);

        $formsIdioma = array();
        $index       = 0;
        $idioma      = new Application_Model_DominioIdioma();

        $arrayIdiomas = $idioma->getDominioIdioma($idPostulante);
        if (count($arrayIdiomas) != 0) {
            foreach ($arrayIdiomas as $idioma) {
                $form                   = $managerIdioma->getForm($index++,
                    $idioma);
                $form->setHiddenId($idioma['id_dominioIdioma']);
                $form->setCabeceras($idioma['id_idioma'],
                    $idioma['nivel_idioma']);
                $form->addValidatorsIdioma();
                $formsIdioma[]          = $form;
                $this->view->isLinkedin = true;
                $this->view->isIdioma   = false;
            }
            $formsIdioma[] = $managerIdioma->getForm($index++);
        } else {
            $formsIdioma[] = $managerIdioma->getForm(0);
        }

        if ($this->getRequest()->isPost() && $this->_hash->isValid($hash)) {
            $postData = $this->_getAllParams();
            if (
                $postData['managerIdioma_0_id_idioma'] == '0' && $postData['managerIdioma_0_nivel_idioma']
                == '0' && $postData['managerIdioma_0_id_dominioIdioma'] == ''
            ) {
                unset($postData['managerIdioma_0_id_idioma']);
                unset($postData['managerIdioma_0_nivel_idioma']);
                unset($postData['managerIdioma_0_id_dominioIdioma']);
                unset($postData['managerIdioma_0_cabecera_idioma']);
                unset($postData['managerIdioma_0_cabecera_nivel']);
            }
            $util         = $this->_helper->Util;
            $pattern      = '/managerIdioma_([0-9]*)_id_idioma/';
            $hayRepetidos = $util->getRepetido($pattern, $postData);

            $validExp               = $managerIdioma->isValid($postData);
            $this->view->isLinkedin = true;
            $this->view->isIdioma   = false;

            if ((bool) $hayRepetidos) {
                $idioma      = $this->view->ItemList('idioma', $hayRepetidos);
                $this->getMessenger()->error("Idiomas Repetido: ".$idioma);
                $formsIdioma = $managerIdioma->getForms();
            } else if ($validExp) {
                $idIdiomaNew = $this->_actualizarIdiomaPostulanteAjax(
                    $managerIdioma->getCleanPost(), $idPostulante
                );

                //Actualizamos postulaciones.
                $helper = $this->_helper->getHelper("RegistrosExtra");
                $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);

                echo Zend_Json::encode(array(
                    'id' => $idIdiomaNew,
                    'message' => $this->_messageSuccess,
                    'csrfhash' => CSRF_HASH));
                exit;
//                $this->getMessenger()->success($this->_messageSuccess);
//                $this->_redirect(
//                    Zend_Controller_Front::getInstance()
//                        ->getRequest()->getRequestUri()
//                );
            } else {
                $formsIdioma = $managerIdioma->getForms();
            }
        }

        $this->view->formIdioma = $formsIdioma;
        $this->view->assign('managerIdioma', $managerIdioma);

        echo Zend_Json::encode(array(
            'message' => $this->_messageFail,
            'csrfhash' => CSRF_HASH));
    }

    public function misProgramasAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $hash = $this->_getParam('csrfhash');

        $this->view->headScript()
            ->appendScript('urls.programsF = "/mi-cuenta/borrar-programa-ajax";');

        $this->view->menu_sel_side = self::MENU_POST_SIDE_PROGRAMAS;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;

        $idPostulante = $this->idPostulante;

        $baseFormPrograma = new Application_Form_Paso2Programa(true);
        $managerPrograma  = new App_Form_Manager($baseFormPrograma,
            'managerPrograma', true);


        $formsPrograma = array();
        $index         = 0;

        $programa = new Application_Model_DominioProgramaComputo();

        $arrayProgramas = $programa->getDominioProgramaComputo($idPostulante);

        if (count($arrayProgramas) != 0) {
            foreach ($arrayProgramas as $programa) {
                $form                   = $managerPrograma->getForm($index++,
                    $programa);
                $form->setHiddenId($programa['id_dominioComputo']);
                $form->setCabeceras($programa['id_programa_computo'],
                    $programa['nivel']);
                $form->addValidatorsPrograma();
                $formsPrograma[]        = $form;
                $this->view->isPrograma = false;
            }
            $formsPrograma[] = $managerPrograma->getForm($index++);
        } else {
            $formsPrograma[] = $managerPrograma->getForm(0);
        }

        if ($this->getRequest()->isPost() && $this->_hash->isValid($hash)) {
//        if ($this->getRequest()->isPost()) {
            $postData     = $this->_getAllParams();
//            if (
//                    $postData['managerPrograma_0_id_programa_computo'] == '-1' && $postData['managerPrograma_0_nivel'] == '-1' && $postData['managerPrograma_0_id_dominioComputo'] == ''
//            ) {
//                unset($postData['managerPrograma_0_id_programa_computo']);
//                unset($postData['managerPrograma_0_nivel']);
//                unset($postData['managerPrograma_0_id_dominioComputo']);
//            }
//            Zend_Debug::dump($postData);
            $util         = $this->_helper->Util;
            $pattern      = '/managerPrograma_([0-9]*)_id_programa/';
            $hayRepetidos = $util->getRepetido($pattern, $postData);

            $validExp               = $managerPrograma->isValid($postData);
            $this->view->isPrograma = false;

            if ((bool) $hayRepetidos) {
                $programaC     = $this->view->ItemList('ProgramaComputo',
                    $hayRepetidos);
                $this->getMessenger()->error("Programas Repetido: ".$programaC);
                $formsPrograma = $managerPrograma->getForms();
            } elseif ($validExp) {
                $idProgramaNew = 0;
                $idProgramaNew = $this->_actualizarProgramaComputoPostulanteAjax(
                    $managerPrograma->getCleanPost(), $idPostulante
                );

                //Actualizamos postulaciones.
                $helper = $this->_helper->getHelper("RegistrosExtra");
                $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);

                //ACTUALIZACION DE ZENDLUCENE
                //$this->_actualizarPostulanteZendLucene($idPostulante);
                echo Zend_Json::encode(array(
                    'id' => $idProgramaNew,
                    'message' => $this->_messageSuccess,
                    'csrfhash' => CSRF_HASH));
                exit;
//                $this->getMessenger()->success($this->_messageSuccess);
//                $this->_redirect(
//                    Zend_Controller_Front::getInstance()
//                        ->getRequest()->getRequestUri()
//                );
            } else {
                $formsPrograma = $managerPrograma->getForms();
            }
        }

        $this->view->formPrograma = $formsPrograma;
        $this->view->assign('managerPrograma', $managerPrograma);

        echo Zend_Json::encode(array(
            'message' => $this->_messageFail,
            'csrfhash' => CSRF_HASH));
    }

    public function misReferenciasAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $hash = $this->_getParam('csrfhash');

        $this->view->menu_sel_side = self::MENU_POST_SIDE_REFERENCIAS;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;

        $this->view->headScript()->appendScript('urls.referenceF = "/mi-cuenta/borrar-referencia-ajax";');
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/form.referencia.js')
        );

        $this->view->nombre = $this->auth['postulante']['nombres'];


        $idPostulante       = $this->idPostulante;
        $baseFormReferencia = new Application_Form_Paso2Referencia($idPostulante,
            true);
        $managerReferencia  = new App_Form_Manager($baseFormReferencia,
            'managerReferencia', true);

        $formsReferencia = array();
        $index           = 0;
        $referencias     = new Application_Model_Referencia();

        $arrayReferencias = $referencias->getReferencias($idPostulante);
        if (count($arrayReferencias) != 0) {
            foreach ($arrayReferencias as $referencias) {
                $form                     = $managerReferencia->getForm($index++,
                    $referencias);
                $form->setHiddenId($referencias['id_referencia']);
                $formsReferencia[]        = $form;
                $this->view->isReferencia = true;
                $this->view->isLinkedin   = false;
            }
            $formsReferencia[] = $managerReferencia->getForm($index++);
        } else {
            $formsReferencia[] = $managerReferencia->getForm(0);
        }

        if ($this->getRequest()->isPost() && $this->_hash->isValid($hash)) {
            $postData                 = $this->_getAllParams();
            $validExp                 = $managerReferencia->isValid($postData);
            $this->view->isReferencia = true;
            $this->view->isLinkedin   = true;
            if ($validExp) {
                $idReferenciaNew = 0;
                $idReferenciaNew = $this->_actualizarReferenciaPostulanteAjax(
                    $managerReferencia->getCleanPost(), $idPostulante
                );
                echo Zend_Json::encode(array(
                    'id' => $idReferenciaNew,
                    'message' => $this->_messageSuccess,
                    'csrfhash' => CSRF_HASH));
                exit;
            } else {
                $formsReferencia = $managerReferencia->getForms();
            }
        }
        $this->view->formReferencia = $formsReferencia;
        $this->view->assign('managerReferencia', $managerReferencia);

        echo Zend_Json::encode(array(
            'message' => $this->_messageFail,
            'csrfhash' => CSRF_HASH));
    }

    private function getDatosFormExperience($index, &$formsExperiencia,
                                            &$managerExperiencia,
                                            $arrayExperiencias = array())
    {
        $puestos        = Application_Model_Puesto::getPuestosIds();
        $nivelesPuestos = Application_Model_NivelPuesto::getNivelesPuestosIds();
        $areas          = Application_Model_Area::getAreasIds();

        foreach ($arrayExperiencias as $experiencia) {

            if ($experiencia['en_curso'] == 1) {
                $experiencia['fin_mes'] = date('n');
                $experiencia['fin_ano'] = date('Y');
            }

            $experiencia['comentarios'] = trim($experiencia['comentarios']);
            if (strlen($experiencia['comentarios']) > 140) {
                $experiencia['comentarios'] = substr($experiencia['comentarios'],
                    0, 140);
            }
            if (!array_key_exists($experiencia['id_nivel_puesto'],
                    $nivelesPuestos) ||
                !array_key_exists($experiencia['id_area'], $areas) ||
                !array_key_exists($experiencia['id_puesto'], $puestos)) {
                $experiencia['is_disabled'] = 1;
                $experiencia['otro_puesto'] = $experiencia['nombre_puesto'];
            }

            $form               = $managerExperiencia->getForm($index++,
                $experiencia);
            $form->setHiddenId($experiencia['id_Experiencia']);
            $formsExperiencia[] = $form;
        }

        $formsExperiencia[] = $managerExperiencia->getForm($index++);

        return $formsExperiencia;
    }

    public function misExperienciasAction()
    {
        $session                = new Zend_Session_Namespace('linkedin');
        $this->view->showImport = false;
        $idPostulante           = $this->idPostulante;
        Zend_Layout::getMvcInstance()->assign(
            'submenuMiCuenta', $this->_submenuMiCuenta
        );
        $form                   = new Application_Form_MisExperiencia();
        $exp                    = new Application_Model_Experiencia();


//        $importoDatos = $this->linkedImported
//                ->isImported('experiencia',$idPostulante);
//        if ($importoDatos === false) {
//            $this->view->showImport = true;
//            if (isset($session->linkedin)) {
//                $this->getMessenger()->success(
//                        "Se logró importar tus experiencias de linkedin."
//                );
//                $linkedinData = $session->linkedin;
//                $this->_linkedinSaveExperiencia($linkedinData,$idPostulante);
//                unset($session->linkedin);
//                $this->_redirect('/mi-cuenta/mis-experiencias');
//            }
//        }


        $arrayExperiencias = $exp->getExperiencias($this->idPostulante);

        $puestos        = Application_Model_Puesto::getPuestosIds();
        $nivelesPuestos = Application_Model_NivelPuesto::getNivelesPuestosIds();
        $areas          = Application_Model_Area::getAreasIds();

        if (count($arrayExperiencias) > 0) {
            $pos = 0;
            foreach ($arrayExperiencias as $experiencia) {
                $experiencia['is_disabled'] = 0;
//               if (!array_key_exists($experiencia['id_nivel_puesto'], $nivelesPuestos) ||
//                   !array_key_exists($experiencia['id_area'], $areas) ||
//                   !array_key_exists($experiencia['id_puesto'], $puestos)) {
//                   $experiencia['is_disabled'] = 1;
//               }

                $arrayExperiencias[$pos++] = $experiencia;
            }
        }
        // var_dump($arrayExperiencias);exit;
        $this->view->itemExperiencias = $arrayExperiencias;
        $this->view->formExperiencia  = $form;
        $this->view->meses            = $this->_meses;
        $this->view->slug             = $this->auth['postulante']['slug'];
        $this->view->moneda           = $this->_config->app->moneda;
    }

    public function updateExperienciasAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $form = new Application_Form_MisExperiencia();

        $id = $this->idPostulante;

        $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());
        $response      = array();


        if ($requestValido) {
            $post = $this->getRequest()->getParams();

            $XSS     = new App_Util();
//            $post = $XSS->clearXSS( $this->getRequest()->getParams() );
            $post    = $this->getRequest()->getParams();
            $isvalir = $form->isValid($post);

            $datos                    = array();
            $datos['otra_empresa']    = $post['txtExperience'];
            $datos['otro_rubro']      = $post['txtIndustry'];
            $datos['id_nivel_puesto'] = $post['selLevelJob'];
            $datos['id_puesto']       = $post['hidJob'];
            $datos['id_area']         = $post['selLevelArea'];

            $datos['id_tipo_proyecto'] = @$post['selProjectType'];
            $datos['nombre_proyecto']  = $post['txtNameProjectType'];
            $datos['costo_proyecto']   = !empty($post['txtBudgetProjectType']) ? $post['txtBudgetProjectType']
                    : 0;

            $datos['en_curso']    = (isset($post['chkInProgress']) || !empty($post['chkInProgress']) )
                    ? 1 : 0;
            $datos['fin_mes']     = @$post['selMonthEnd'];
            $datos['fin_ano']     = @$post['txtYearEnd'];
            $datos['inicio_mes']  = $post['selMonthBegin'];
            $datos['inicio_ano']  = $post['txtYearBegin'];
            $datos['comentarios'] = substr($post['txaComments'], 0, 1500); // only 500 characters

            $datos['id_Experiencia'] = $post['hidExperiences'];

            $datos['lugar'] = $post['togglefields'] == 'lugar' ? 1 : 0;

            if (!isset($datos['id_puesto']) || (int) $datos['id_puesto'] < 1) {
                $datos['id_puesto']   = 1292;
                $datos['otro_puesto'] = $post['txtJob'];
            }

            if ($form->isValid($post)) {

                $result = $this->actualizarExperiencia($datos,
                    $this->idPostulante);
                $id     = $result['id'];
                if ($id) {
                    unset($post['controller']);
                    unset($post['hidToken']);
                    unset($post['action']);
                    unset($post['module']);


                    $objDisabled           = new stdClass();
                    $objDisabled->disabled = true;

                    $data                   = $post;
                    $data['hidExperiences'] = $id;

                    if ($datos['en_curso'] == 1) {
                        $data['txtYearEnd']  = $objDisabled;
                        $data['selMonthEnd'] = $objDisabled;
                    }

                    $data['togglefields']  = $datos['lugar'] == 1 ? 'lugar' : 'obra';
                    $data['chkInProgress'] = $datos['en_curso'];


                    if (isset($datos['fin_mes'])) {
                        $data['selMonthEndLbl'] = $this->_meses[$datos['fin_mes']];
                    }
                    $data['selMonthBeginLbl'] = $this->_meses[$datos['inicio_mes']];

                    $puesto = new Application_Model_NivelPuesto();
                    $puesto = $puesto->getNivel($data['selLevelJob']);

                    $data['selLevelJobLbl']  = $puesto['nombre'];
                    $datos['id']             = $this->idPostulante;
                    $solrAdd                 = new Solr_SolrPostulante();
                    $solrAdd->add($this->idPostulante);
                    $datos['chkInProgress']  = isset($post['chkInProgress']) ? $post['chkInProgress']
                            : '';
                    $dataporcentaje          = $this->_helper->LogActualizacionBI->logActualizacionPostulanteExperiencia($datos);
                    $helper                  = $this->_helper->getHelper("RegistrosExtra");
                    $helper->ActualizarPostulacion((int) $this->idPostulante);
                    $helper->ActualizarExperiencias((int) $this->idPostulante);
                    $response['status']      = 1;
                    $response['message']     = $result['mensaje'];
                    $response['skill']       = $data;
                    $response['iscompleted'] = array(
                        'Experiencia',
                        $dataporcentaje['iscompleted']
                    );
                    $response['percent']     = $dataporcentaje['total_completado'];
                } else {
                    $response['status']  = 0;
                    $response['message'] = 'No se pudo guardar la experiencia. Intentalo nuevamente';
                }
            } else {
                $response['status']  = 0;
                $response['message'] = $form->getMensajesErrors($form);
            }
        } else {
            $response['status'] = 0;
            $response['errors'] = 'Error al recibir parametros';
        }

        $form->getElement('hidToken')->initCsrfToken();
        $response['token'] = $form->getElement('hidToken')->getValue();


        $this->_response->appendBody(json_encode($response));
    }

    private function actualizarExperiencia($data, $idPostulante)
    {
        $idExp       = FALSE;
        $mensaje     = '';
        $experiencia = new Application_Model_Experiencia();
        if (isset($data['en_curso']) && $data['en_curso'] == 1) {
            $data['fin_mes'] = null;
            $data['fin_ano'] = null;
        }

        if (isset($data['lugar']) && $data['lugar'] == 1) {
            $data['nombre_proyecto']  = '';
            $data['costo_proyecto']   = '';
            $data['id_tipo_proyecto'] = 0;
        }

        if (isset($data['otro_puesto'])) {
            $data['otro_puesto'] = ucfirst(strtolower($data['otro_puesto']));
        }

        if (isset($data['otro_rubro'])) {
            $data['otro_rubro'] = ucfirst(strtolower($data['otro_rubro']));
        }

        if (isset($data['id_Experiencia'])) {
            $idExp = $data['id_Experiencia'];
            unset($data['id_Experiencia']);
        }

        $idExperienciaNew = FALSE;

        // si hay id, se actualiza
        if ($idExp) {
            $where            = $experiencia->getAdapter()
                    ->quoteInto('id_postulante = ?', $idPostulante).
                    $experiencia->getAdapter()
                    ->quoteInto(' and id = ?', $idExp);
            //if ($data['otra_empresa'] != '' || $data['otro_puesto'] != '' || $data['otro_rubro'] !='')
            if ($data['id_nivel_puesto'] != 0 && $data['id_puesto'] != 0 && $data['id_area']
                && $data['otra_empresa']) $experiencia->update($data, $where);
            $idExperienciaNew = $idExp;
            $mensaje          = $this->_messageSuccessActualizar;
        } else {
            $data['id_postulante'] = $idPostulante;
            try {
                $idExperienciaNew = $experiencia->insert($data);
                $mensaje          = $this->_messageSuccessRegistrar;
            } catch (Exception $x) {
                $this->log->log($x->getMessage().'. '.$x->getTraceAsString(),
                    Zend_Log::ERR);
            }
        }

        $postulante = new Application_Model_Postulante();
        $where      = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')
            ), $where
        );
        try {
            @$this->_cache->remove('Postulante_getUltimaExperiencias_'.$idPostulante);
        } catch (Exception $exc) {

        }

        return array(
            'id' => $idExperienciaNew,
            'mensaje' => $mensaje);
    }

    public function getDataExperienciasAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
            $id                    = $this->_getParam('id');
//            $form = new Application_Form_MisExperiencia();
            $mExp                  = new Application_Model_Experiencia();
            $exp                   = $mExp->getExperiencia($id);
            $objDisabled           = new stdClass();
            $objDisabled->disabled = true;

            $datos                         = array();
            $datos['txtExperience']        = $exp['otra_empresa'];
            $datos['txtIndustry']          = $exp['otro_rubro'];
            $datos['selLevelJob']          = $exp['id_nivel_puesto'];
            $datos['hidJob']               = $exp['id_puesto'];
            $datos['txtJob']               = $exp['nombre_puesto'];
            $datos['selLevelArea']         = $exp['id_area'];
            $datos['selProjectType']       = $exp['id_tipo_proyecto'];
            $datos['txtNameProjectType']   = $exp['nombre_proyecto'];
            $datos['txtBudgetProjectType'] = $exp['costo_proyecto'];
            $datos['chkInProgress']        = $exp['en_curso'];

            $datos['selMonthEnd'] = $exp['fin_mes'];

            $datos['txtYearEnd']       = $exp['fin_ano'];
            $datos['selMonthBegin']    = $exp['inicio_mes'];
            $datos['selMonthBeginLbl'] = $this->_meses[$exp['inicio_mes']];

            if (isset($exp['fin_mes'])) {
                $datos['selMonthEndLbl'] = $this->_meses[$exp['fin_mes']];
            }

            $datos['togglefields'] = $exp['lugar'] == 1 ? 'lugar' : 'obra';

            $datos['txtYearBegin']   = $exp['inicio_ano'];
            $datos['txaComments']    = $exp['comentarios'];
            $datos['hidExperiences'] = $exp['id_Experiencia'];

            if ($exp['en_curso'] == 1) {
                $datos['txtYearEnd']  = $objDisabled;
                $datos['selMonthEnd'] = $objDisabled;
            }

            if ($exp['id_puesto'] == Application_Form_MisExperiencia::OTRO_PUESTO) {
                $datos['txtJob'] = $exp['otro_puesto'];
            }

            $datos['selLevelJobLbl'] = $exp['nivel_puesto'];


            $data['status'] = 1;
//            $data['messages']=$this->_messageSuccess;
            $data['skill']  = $datos;
        } else {
            $data['status']   = 0;
            $data['messages'] = 'No se pude realizar esta accion';
        }

        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarExperienciaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        if ($this->_hash->isValid($this->_getParam('csrfhash'))) {
            $idExperiencia = $this->_getParam('id', false);
            $data          = $this->EliminarElemento('Experiencia',
                $idExperiencia, 'experiencia');
            $extra         = array(
                'csrfhash' => CSRF_HASH,
                'status' => 0
            );

            //Actualizamos postulaciones.
            $helper           = $this->_helper->getHelper("RegistrosExtra");
            $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);
            $helper->ActualizarExperiencias($this->auth["postulante"]["id"]);
            $ModeloReferencia = new Application_Model_Referencia();
            $where            = $this->getAdapter()
                ->quoteInto('id_experiencia IN (?)', (int) $idExperiencia);
            $delete           = $ModeloReferencia->delete($where);
            //ACTUALIZACION DE ZENDLUCENE
            //$this->_actualizarPostulanteZendLucene($this->auth["postulante"]["id"]);

            $this->_response->appendBody(Zend_Json::encode($data + $extra));
        } else {
            $extra = array(
                'csrfhash' => CSRF_HASH,
                'message' => $this->_messageFail,
                'status' => 0
            );
            echo (Zend_Json::encode($extra));
        }
    }

    public function filtroPuestosAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $data = array();
        $solr = new Solr_SolrPuesto();
        $data = array(
            'status' => '0',
            "messages" => "No se encontraron resultados"
        );


        if ($this->getRequest()->isPost()) {
            $q = $this->getRequest()->getParam('value');
            if (isset($q) && strlen($q) >= 2) {
                $Items = $solr->getPuestosByName($q);
                $data  = array(
                    'status' => '1',
                    "messages" => "Sus fueron encontrados.",
                    'items' => $Items
                );
            }
        }


        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function misExperienciasActionOld()
    {

        $this->view->menu_sel_side = self::MENU_POST_SIDE_EXPERIENCIA;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;
        $this->view->action        = 'mis-datos-personales';
        $this->view->modulo        = '';
        //$session = $this->getSession();
        $session                   = new Zend_Session_Namespace('linkedin');
        $this->view->showImport    = false;

        $idPostulante = $this->idPostulante;

        $baseFormExperiencia = new Application_Form_Paso2Experiencia(true);
        $managerExperiencia  = new App_Form_Manager($baseFormExperiencia,
            'managerExperiencia', true);

        $formsExperiencia = array();
        $index            = 0;

        $experiencia       = new Application_Model_Experiencia();
        $ok                = true;
        $arrayExperiencias = $experiencia->getExperiencias($idPostulante);
        if (isset($session->linkedin)) {
            $this->view->isLinkedin = true;
            $ok                     = false;
            if (isset($session->showMessage)) {
                $this->getMessenger()->success(
                    "Se logró importar tus datos de linkedin. Por favor, ingresa o
						  selecciona los datos que no hayan coincidido exactamente con
						  los de AquiEmpleos."
                );
                unset($session->showMessage);
            }

            $linkedinData     = $session->linkedin;
            $formsExperiencia = $this->_linkedinExperiencia($linkedinData,
                $managerExperiencia);
            if (count($arrayExperiencias) == 0) {
                $formsExperiencia[] = $managerExperiencia->getForm(count($formsExperiencia));
            }
            $this->view->showImport = true;

            //print_r($session->linkedin->positions->position->{0});
            unset($session->linkedin);
            $index = count($formsExperiencia);
        }

        if (!$this->getRequest()->isPost()) {
            if (count($arrayExperiencias) != 0) {
                $puestos = Application_Model_Puesto::getPuestosIds();

                $nivelesPuestos = Application_Model_NivelPuesto::getNivelesPuestosIds();
                $areas          = Application_Model_Area::getAreasIds();
                foreach ($arrayExperiencias as $experiencia) {
                    $add = TRUE;
                    if ($experiencia['en_curso'] == '1') {
                        $experiencia['fin_mes'] = date('n');
                    }
                    if ($experiencia['en_curso'] == '1') {
                        $experiencia['fin_ano'] = date('Y');
                    }
                    if (strlen($experiencia['comentarios']) > 140) {
                        $experiencia['comentarios'] = substr($experiencia['comentarios'],
                            0, 140);
                    }
                    if (!array_key_exists($experiencia['id_nivel_puesto'],
                            $nivelesPuestos) ||
                        !array_key_exists($experiencia['id_area'], $areas) ||
                        !array_key_exists($experiencia['id_puesto'], $puestos)) {
                        $experiencia['is_disabled'] = 1;
                        $experiencia['otro_puesto'] = $experiencia['nombre_puesto'];
                    }

                    if ($add) {

                        $form                      = $managerExperiencia->getForm($index++,
                            $experiencia);
                        $form->setHiddenId($experiencia['id_Experiencia']);
                        $formsExperiencia[]        = $form;
                        $this->view->isExperiencia = true;
                        $this->view->isLinkedin    = true;
                    }
                }
                $formsExperiencia[] = $managerExperiencia->getForm($index++);
            } else {
                if ($ok && !$this->getRequest()->isPost())
                        $formsExperiencia[] = $managerExperiencia->getForm(0);
            }
        } else {
            //if ($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();

            $validExp = $managerExperiencia->isValid($postData);

            $this->view->isExperiencia = true;
            $this->view->isLinkedin    = true;

//            if ($this->_validExperiencia($managerExperiencia->getCleanPost())) {
            if ($validExp) {
                $this->_actualizarExperienciaPostulante(
                    $managerExperiencia->getCleanPost(), $idPostulante
                );

                //Actualizamos postulaciones.
                $helper = $this->_helper->getHelper("RegistrosExtra");
                $helper->ActualizarPostulacion($idPostulante);
                $helper->ActualizarExperiencias($idPostulante);

                //ACTUALIZACION DE ZENDLUCENE
                $this->_actualizarPostulanteZendLucene($idPostulante);

                $this->getMessenger()->success($this->_messageSuccess);
                $this->_redirect(
                    Zend_Controller_Front::getInstance()
                        ->getRequest()->getRequestUri()
                );


                $id                                       = $this->idPostulante;
                $where                                    = $this->_postulante->getAdapter()
                    ->quoteInto('id = ?', $id);
                $db                                       = $this->getAdapter();
                $db->beginTransaction();
                $valuesPostulante                         = array();
                $date                                     = date('Y-m-d H:i:s');
                $valuesPostulante['ultima_actualizacion'] = $date;
                $valuesPostulante['last_update_ludata']   = $date;
                $this->_postulante->update($valuesPostulante, $where);
            } else {
                $arrExp = explode(',', $postData['managerExperiencia']);
                //var_dump($arrExp);


                if (count($arrayExperiencias) != 0) {
                    $puestos = Application_Model_Puesto::getPuestosIds();

                    $nivelesPuestos = Application_Model_NivelPuesto::getNivelesPuestosIds();
                    $areas          = Application_Model_Area::getAreasIds();
                    foreach ($arrayExperiencias as $experiencia) {

                        if ($experiencia['en_curso'] == '1') {
                            $experiencia['fin_mes'] = date('n');
                        }
                        if ($experiencia['en_curso'] == '1') {
                            $experiencia['fin_ano'] = date('Y');
                        }
                        if (strlen($experiencia['comentarios']) > 140) {
                            $experiencia['comentarios'] = substr($experiencia['comentarios'],
                                0, 140);
                        }
                        if (!array_key_exists($experiencia['id_nivel_puesto'],
                                $nivelesPuestos) ||
                            !array_key_exists($experiencia['id_area'], $areas) ||
                            !array_key_exists($experiencia['id_puesto'],
                                $puestos)) {
                            $experiencia['is_disabled'] = 1;
                            $experiencia['otro_puesto'] = $experiencia['nombre_puesto'];
                        }

                        $form                      = $managerExperiencia->getForm($index++,
                            $experiencia);
                        $form->setHiddenId($experiencia['id_Experiencia']);
                        $formsExperiencia[]        = $form;
                        $this->view->isExperiencia = true;
                        $this->view->isLinkedin    = true;
                    }

                    $formsExperiencia[] = $managerExperiencia->getForm($index++);
                }


                foreach ($arrExp as $key) {
                    $managerExperiencia->removeForm($key);
                }
                //$formsExperiencia[] = $managerExperiencia->getForm($index);
            }
        }
        $this->view->formExperiencia = $formsExperiencia;
        $this->view->assign('managerExperiencia', $managerExperiencia);
    }

    function addScheme($url, $scheme = 'http://')
    {
        return parse_url($url, PHP_URL_SCHEME) === null ?
            $scheme.$url : $url;
    }

    public function miUbicacionAction()
    {
        $this->view->isAuth = $this->isAuth;
        $idPostulante       = $this->idPostulante;
        Zend_Layout::getMvcInstance()->assign(
            'submenuMiCuenta', $this->_submenuMiCuenta
        );
        $postulante         = $this->_postulante->getPostulante($idPostulante);

        $form = new Application_Form_MiUbicacion();
        $form->txtUbigeo->setValue($postulante['provincia'].', '.$postulante['dpto']);

        if ($this->getRequest()->isPost()) {

            $postData                    = $this->_getAllParams();
            $postData['txtUbigeo']       = isset($postData['txtUbigeo']) ? $postData['txtUbigeo']
                    : '--';
            $postData['rdDispProvincia'] = isset($postData['rdDispProvincia']) ? $postData['rdDispProvincia']
                    : '';
            if ($form->isValid($postData)) {
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender();

                $datos = array(
                    'id_ubigeo' => ($postData['selPais'] == Application_Model_Ubigeo::PERU_UBIGEO_ID)
                            ? $postData['txtIdUbigeo'] : $postData['selPais'],
                    'pais_residencia' => $postData['selPais'],
                    'disponibilidad_provincia_extranjero' => $postData['rdDispProvincia'],
                    'presentacion' => $postData['txtPresentacion'],
                    'facebook' => $postData['txtFacebook'],
                    'twitter' => $postData['txtTwitter']
                );


                /* if( $datos['pais_residencia'] != Application_Model_Ubigeo::PERU_UBIGEO_ID ){
                  unset($datos['id_ubigeo'] );
                  } */

                if (isset($datos['facebook']) && $datos['facebook'] != '') {
                    $datos['facebook'] = $this->addScheme($datos['facebook']);
                } else {
                    $datos['facebook'] = null;
                }


                $where = $this->_postulante
                    ->getAdapter()
                    ->quoteInto('id = ?', $idPostulante);

                $update = (int) $this->_postulante->update($datos, $where);
//                @$this->_cache->remove('get_perfil_postulante_' . str_replace('-', '_', $this->_slug));
//                @$this->_cache->remove('get_perfil_postulante_' . str_replace('-', '_', $this->idPostulante));
//                @$this->_cache->remove('perfil_postulante_' . str_replace('-', '_', $this->_slug));

                $storage = Zend_Auth::getInstance()->getStorage()->read();

                if (isset($datos['id_ubigeo'])) {
                    $storage['postulante']['id_ubigeo'] = $postData['txtIdUbigeo'];
                }
                $storage['postulante']['pais_residencia']                     = $postData['selPais'];
                $storage['postulante']['disponibilidad_provincia_extranjero'] = $postData['rdDispProvincia'];
                $storage['postulante']['presentacion']                        = $postData['txtPresentacion'];
                $storage['postulante']['facebook']                            = $postData['txtFacebook'];
                $storage['postulante']['twitter']                             = $postData['txtTwitter'];
                Zend_Auth::getInstance()->getStorage()->write($storage);


                $datos['id']    = $idPostulante;
                $dataporcentaje = $this->_helper->LogActualizacionBI->logActualizacionPostulantePerfilUbicaion($datos);
                $form->getElement('hidToken')->initCsrfToken();
                $token          = $form->getElement('hidToken')->getValue();
                $this->_helper->json(array(
                    'status' => 1,
                    'iscompleted' => array(
                        'Ubicación',
                        $dataporcentaje['iscompleted']),
                    'message' => 'Los datos fueron actualizados correctamente',
                    'token' => $token,
                    'percent' => $dataporcentaje['total_completado']
                ));
            } else {

                $form->getElement('hidToken')->initCsrfToken();
                $token = $form->getElement('hidToken')->getValue();

                $error = $form->getMensajesErrors($form);
                $error = trim($error);

                $this->_helper->json(array(
                    'status' => 0,
                    'message' => isset($error) && strlen($error) > 0 ? $error : $this->_messageError,
//                        'message'=> 'No se pudieron guardar los cambios, intente nuevamente.',
                    'token' => $token
                ));
            }
        }

//        if (isset($postulante)) {
//           $form->setDatos($postulante);
//        }

        $this->view->form = $form;
        $this->view->slug = $this->auth['postulante']['slug'];
    }

    /**
     * Lee los datos provenientes del LinkedIn relacionado a Expericencia para
     * luego insertarlo en el formulario
     *
     * @param Zend_Config_Xml $linkedinData
     * @param App_Form_Manager $mngrXp
     * @return array
     */
    private function _linkedinExperiencia(Zend_Config_Xml $linkedinData,
                                          App_Form_Manager $mngrXp)
    {

        if (isset($linkedinData->positions->total)) {
            $this->view->isExperiencia = true;
            $formExperiencia           = array();
            if ($linkedinData->positions->total > 1) {
                $i = 0;
                foreach ($linkedinData->positions->position as $empresa) {
                    $values  = array();
                    $form    = $mngrXp->getForm($i);
                    $empresa = $empresa->toArray();
                    if (isset($empresa['company']['name'])) {
                        $values['otra_empresa'] = $empresa['company']['name'];
                    }
                    if (isset($empresa['company']['industry'])) {
                        $values['otro_rubro'] = $empresa['company']['industry'];
                    }
                    if (isset($empresa['title'])) {
                        $values['otro_puesto'] = $empresa['title'];
                    }
                    if (isset($empresa['start-date']['month'])) {
                        $values['inicio_mes'] = $empresa['start-date']['month'];
                    }
                    if (isset($empresa['start-date']['year'])) {
                        $values['inicio_ano'] = $empresa['start-date']['year'];
                    }
                    if ($empresa['is-current'] == 'true') {
                        $values['en_curso'] = '1';
                        // @codingStandardsIgnoreStart
                        $form->fin_mes->setAttrib('rel', date('n'));
                        // @codingStandardsIgnoreEnd
                        $values['fin_mes']  = date('n');
                        $values['fin_ano']  = date('Y');
                    } else {
                        $values['en_curso'] = '0';
                        if (isset($empresa['end-date']['month'])) {
                            $values['fin_mes'] = $empresa['end-date']['month'];
                        } else {
                            $values['fin_mes'] = date('n');
                        }
                        if (isset($empresa['end-date']['year'])) {
                            $values['fin_ano'] = $empresa['end-date']['year'];
                        } else {
                            $values['fin_ano'] = date('Y');
                        }
                    }
                    if (isset($empresa['summary'])) {
                        $values['comentarios'] = substr($empresa['summary'], 0,
                            140);
                    }
                    //$values['lugar'] = 1;
                    $form->isValid($values);
                    //$form->setHiddenId($empresa['id']);
                    $formExperiencia[] = $form;
                    $i++;
                }
            } elseif ($linkedinData->positions->total == 0) {
                $values               = array();
                $values['inicio_ano'] = date('Y') - 1;
                $values['fin_ano']    = date('Y');
                $form                 = $mngrXp->getForm(0);
                $form->isValid($values);
                //$form->setHiddenId($empresa['id']);
                $formExperiencia[]    = $form;
            } else {
                $empresa = $linkedinData->positions->position->toArray();
                if (isset($empresa['company']['name'])) {
                    $values['otra_empresa'] = $empresa['company']['name'];
                }
                if (isset($empresa['company']['industry'])) {
                    $values['otro_rubro'] = $empresa['company']['industry'];
                }
                if (isset($empresa['title'])) {
                    $values['otro_puesto'] = $empresa['title'];
                }
                if (isset($empresa['start-date']['month'])) {
                    $values['inicio_mes'] = $empresa['start-date']['month'];
                }
                if (isset($empresa['start-date']['year'])) {
                    $values['inicio_ano'] = $empresa['start-date']['year'];
                }
                if ($empresa['is-current'] == 'true') {
                    $values['en_curso'] = '1';
                } else {
                    $values['en_curso'] = '0';
                    if (isset($empresa['end-date']['month'])) {
                        $values['fin_mes'] = $empresa['end-date']['month'];
                    } else {
                        $values['fin_mes'] = date('n');
                    }
                    if (isset($empresa['end-date']['year'])) {
                        $values['fin_ano'] = $empresa['end-date']['year'];
                    } else {
                        $values['fin_ano'] = date('Y');
                    }
                }
                if (isset($empresa['summary'])) {
                    $values['comentarios'] = $empresa['summary'];
                }
                $form              = $mngrXp->getForm(0);
                $form->isValid($values);
                //$form->setHiddenId($empresa['id']);
                $formExperiencia[] = $form;
            }
        } else {
            $formExperiencia = array(
                $mngrXp->getForm(0));
        }
        return $formExperiencia;
    }

    public function misEstudiosOriginalAction()
    {

        //$session = $this->getSession();
        $session                   = new Zend_Session_Namespace('linkedin');
        $this->view->menu_sel_side = self::MENU_POST_SIDE_ESTUDIOS;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_smel     = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;
        $this->view->action        = 'mis-datos-personales';
        $this->view->modulo        = '';
        $this->view->showImport    = false;
        $this->view->headLink()->appendStylesheet($this->view->S('/css/plugins/jquery-ui-1.9.2.custom.min.css'));

        $idPostulante = $this->idPostulante;

        $baseFormEstudio = new Application_Form_Paso2Estudio(true);
        $managerEstudio  = new App_Form_Manager($baseFormEstudio,
            'managerEstudio', true);

        $ok            = true;
        $formsEstudio  = array();
        $index         = 0;
        $estudio       = new Application_Model_Estudio();
        $arrayEstudios = $estudio->getEstudios($idPostulante);

        if (isset($session->linkedin)) {
            $ok                     = false;
            $this->view->showImport = true;
            $this->view->isLinkedin = true;
            $this->getMessenger()->success(
                "Se logró importar tus datos de linkedin. Por favor, ingresa o
					 selecciona los datos que no hayan coincidido exactamente con
					 los de AquiEmpleos."
            );
            $linkedinData           = $session->linkedin;
            $formsEstudio           = $this->_linkedinEstudio($linkedinData,
                $managerEstudio);
            if (count($arrayEstudios) == 0) {
                $formsEstudio[] = $managerEstudio->getForm(count($formsEstudio));
            }
            unset($session->linkedin);
            $index = count($formsEstudio);
        }
        if (!$this->getRequest()->isPost()) {
            if (count($arrayEstudios) != 0) {
                $tipoCarreras = Application_Model_TipoCarrera::getTiposCarrerasIds();
                $carreras     = Application_Model_Carrera::getCarrerasIds();
                foreach ($arrayEstudios as $estudio) {
                    if ($estudio['en_curso'] == '1') {
                        $estudio['fin_mes'] = date('n');
                    }
                    if ($estudio['en_curso'] == '1') {
                        $estudio['fin_ano'] = date('Y');
                    }
                    if (( $estudio['id_nivel_estudio'] != 1 &&
                        $estudio['id_nivel_estudio'] != 2 &&
                        $estudio['id_nivel_estudio'] != 3 &&
                        $estudio['id_nivel_estudio'] != 9)) {
                        if (empty($estudio['id_tipo_carrera'])) {
                            $estudio['id_tipo_carrera'] = Application_Model_TipoCarrera::OTROS_TIPO_CARRERA;
                        }
                        if (empty($estudio['id_carrera']) || $estudio['id_carrera']
                            == "") {
                            $estudio['id_carrera'] = Application_Model_Carrera::OTRO_CARRERA;
                            if (!empty($estudio['otro_carrera'])) {
                                $carreras_flip         = array_flip($carreras);
                                $estudio['id_carrera'] = ($carreras_flip[trim($estudio['otro_carrera'])])
                                        ? $carreras_flip[trim($estudio['otro_carrera'])]
                                        : 15;
                            }
                        }
                        if ((!array_key_exists($estudio['id_tipo_carrera'],
                                $tipoCarreras) ||
                            !array_key_exists($estudio['id_carrera'], $carreras))) {
                            $estudio['is_disabled'] = 1;
                        }
                    }
                    $estudio['institucion'] = $estudio['nombre'];
                    unset($estudio['nombre']);
                    $form                   = $managerEstudio->getForm($index++,
                        $estudio);
                    /* if (isset($estudio['id_carrera'])) {
                      $carrera = new Application_Model_Carrera();
                      //$form->getElement('otro_carrera')->setValue($carrera->getCarreraById($estudio['id_carrera']));
                      } */
                    /* $carrera = new Application_Model_Carrera();
                      if(!empty($estudio['id_tipo_carrera']))
                      {
                      $data = $carrera->filtrarCarrera($estudio['id_tipo_carrera']);
                      $form->getElement('id_carrera')->clearMultiOptions()->addMultiOption('-1', 'Selecciona carrera')->addMultiOptions($data);
                      } */
                    $form->setHiddenId($estudio['id_estudio']);
                    $form->setElementNivelEstudio($estudio['id_nivel_estudio']);
                    $form->setElementCarrera($estudio['id_tipo_carrera']);
                    $form->getElement('id_nivel_estudio_tipo')->setValue($estudio['id_nivel_estudio_tipo']);
                    $formsEstudio[]         = $form;
                    $this->view->isLinkedin = true;
                    $this->view->isEstudio  = true;
                }
                $formsEstudio[] = $managerEstudio->getForm($index++);
            } else {
                if ($ok && !$this->getRequest()->isPost())
                        $formsEstudio[] = $managerEstudio->getForm(0);
            }
        } else {
            //if ($this->getRequest()->isPost()) {
            $postData               = $this->_getAllParams();
            $validEst               = $managerEstudio->isValid($postData);
            $this->view->isLinkedin = true;
            $this->view->isEstudio  = true;
//            if ($this->_validEstudio($managerEstudio->getCleanPost())) {
            if ($validEst) {
                $this->_actualizarEstudioPostulante(
                    $managerEstudio->getCleanPost(), $idPostulante
                );
                $helper = $this->_helper->getHelper("RegistrosExtra");
                $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);
                $helper->ActualizarEstudios($this->auth["postulante"]["id"]);

                $this->getMessenger()->success($this->_messageSuccess);
                $this->_redirect(
                    Zend_Controller_Front::getInstance()
                        ->getRequest()->getRequestUri()
                );
            } else {
                $arrExp       = explode(',', $postData['managerEstudio']);
                foreach ($arrExp as $index)
                    $managerEstudio->removeForm($index);
                $formsEstudio = array();
                $formuEstudio = $managerEstudio->getForms();
                foreach ($formuEstudio as $k => $fe) {
                    $id_tipo_carrera = $fe->getElement('id_tipo_carrera')->getValue();
                    if (!empty($id_tipo_carrera)) {
                        //$data = $carrera->filtrarCarrera($id_tipo_carrera);
                        //$fe->getElement('id_carrera')->clearMultiOptions()->addMultiOption('0', 'Selecciona carrera')->addMultiOptions($data);
                        $fe->setElementCarrera($id_tipo_carrera);
                    }
                    $id_nivel_estudio = $fe->getElement('id_nivel_estudio')->getValue();
                    if (!empty($id_nivel_estudio)) {
                        //$data = $nivelEstudio->getSubNiveles($id_nivel_estudio);
                        //$fe->getElement('id_nivel_estudio_tipo')->clearMultiOptions()->addMultiOption('0', 'Selecciona un tipo')->addMultiOptions($data);
                        $fe->setElementNivelEstudio($id_nivel_estudio);
                    }
                    $formsEstudio[$k] = $fe;
                }
            }
        }

        $this->view->formEstudio = $formsEstudio;
        $this->view->assign('managerEstudio', $managerEstudio);
    }

    public function misIdiomasAction()
    {
        $session                = new Zend_Session_Namespace('linkedin');
        $this->view->showImport = false;
        Zend_Layout::getMvcInstance()->assign(
            'submenuMiCuenta', $this->_submenuMiCuenta
        );
        $idPostulante           = $this->idPostulante;
        $FormIdioma             = new Application_Form_Paso2IdiomaNew(true);
        $idioma                 = new Application_Model_DominioIdioma();

//        $importoDatos = $this->linkedImported
//                ->isImported('idioma',$idPostulante);
//        if ($importoDatos === false) {
//            $this->view->showImport = true;
//            if (isset($session->linkedin)) {
//                $this->getMessenger()->success(
//                        "Se logró importar tus idiomas de linkedin."
//                );
//                $linkedinData = $session->linkedin;
//                $this->_linkedinSaveIdioma($linkedinData,$idPostulante);
//                unset($session->linkedin);
//                $this->_redirect('/mi-cuenta/mis-idiomas');
//            }
//        }



        $arrayIdiomas = $idioma->getIdiomas($idPostulante);


        $this->view->lisIdioma  = $arrayIdiomas;
        $this->view->formIdioma = $FormIdioma;
        $this->view->slug       = $this->auth['postulante']['slug'];
    }

    public function updateIdiomasAjaxAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $porcCV = new App_Controller_Action_Helper_PorcentajeCV();

        //var_dump($dataporcentaje["total_completado"]);exit;
        $id            = isset($this->auth["postulante"]["id"]) ? $this->auth["postulante"]["id"]
                : 0;
        $allParams     = $this->_getAllParams();
        $ClasIdioma    = new Application_Model_DominioIdioma();
        $FormIdioma    = new Application_Form_Paso2IdiomaNew(true);
        // $FormIdioma->removeElement('tokenIdioma');
        $isvalidIdioma = $FormIdioma->isValid($allParams);
        $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());

        $response = array();
        if (!$isvalidIdioma && !$requestValido) {

            $response['status']  = 0;
            $response['message'] = Application_Form_Paso2IdiomaNew::getMensajesErrors($FormIdioma);
            $response['errors']  = $FormIdioma->getMessages();

            $FormIdioma->getElement('hidToken')->initCsrfToken();
            $response['token'] = $FormIdioma->getElement('hidToken')->getValue();
        } else {
            $isvalidIdiomaRepetido = $ClasIdioma->getIdiomaId($id,
                $allParams['selLanguage'], $allParams['hidLanguage']);

            if ($isvalidIdiomaRepetido) {
                $FormIdioma->getElement('hidToken')->initCsrfToken();
                $response['status']   = 0;
                $response['message']  = 'El idioma ya existe';
                $response['hidToken'] = $FormIdioma->getElement('hidToken')->getValue();
            } else {
                $nameidioma   = $allParams['selLanguage'];
                $resultIdioma = $this->_UpdateIdiomaPostulante($allParams, $id);
                $solrAdd      = new Solr_SolrPostulante();
                $solrAdd->add($id);
                $idIdioma     = $resultIdioma['id'];

                $data                    = array(
                    'hidLanguage' => $idIdioma,
                    'selLanguage' => $FormIdioma->selLanguage->getMultiOption($nameidioma),
                    'selLevelWritten' => $allParams['selLevelWritten'],
                    'selLevelOral' => $allParams['selLevelOral'],
                );
                $data['id']              = $id;
                $dataporcentaje          = $this->_helper->LogActualizacionBI->logActualizacionPostulanteIdioma($data);
                unset($data['id']);
                $FormIdioma->getElement('hidToken')->initCsrfToken();
                $response['token']       = $FormIdioma->getElement('hidToken')->getValue();
                //Actualizamos postulaciones.
                $helper                  = $this->_helper->getHelper("RegistrosExtra");
                $helper->ActualizarPostulacion($id);
                $response['status']      = 1;
                $response['message']     = $resultIdioma['mensaje'];
                $response['skill']       = $data;
                $response['iscompleted'] = array(
                    "Idiomas",
                    1);
//                 $dataporcentaje= $porcCV->getPorcentajes($this->_getPostulante,true);
                $response['percent']     = $dataporcentaje['total_completado'];
//                 @$this->_cache->remove('get_perfil_postulante_' . str_replace('-', '_', $this->_slug));
//                 @$this->_cache->remove('get_perfil_postulante_' . str_replace('-', '_', $this->idPostulante));
//                 @$this->_cache->remove('perfil_postulante_' . str_replace('-', '_', $this->_slug));
            }
        }

        $this->_response->appendBody(json_encode($response));
    }

    public function filtrarIdiomasAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idiomas = new Application_Model_DominioIdioma();
        $name    = $this->_getParam('idioma');
        $tok     = $this->_getParam('csrfhash');

        $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());
        $requestValido = ($requestValido && $name && $tok);
        $data          = array();
        if (!$requestValido) {
            $data = array(
                'status' => 0,
                'messages' => $this->_messageError);
        }

        if ($this->_hash->isValid($tok)) {
            $filter           = new Zend_Filter();
            $filter->addFilter(new Zend_Filter_StripTags());
            $name             = $filter->filter($name);
            $name             = strtolower($name);
            $data['status']   = '1';
            $data['messages'] = $this->_messageSuccess;
            $data             = $idiomas->getIdiomaName($name);
            $data['skill']    = $data;
        } else {
            $data = array(
                'status' => 0,
                'messages' => $this->_messageError);
        }
        $this->_response->appendBody(Zend_Json::encode($data));
        //  exit;
    }

    public function getDataIdiomasAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idiomas = new Application_Model_DominioIdioma();
        $id      = $this->_getParam('id');
        $tok     = $this->_getParam('csrfhash');

        $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());
        $requestValido = ($requestValido && $id && $tok);
        $data          = array();
        if (!$requestValido) {
            $data = array(
                'status' => 0,
                'messages' => $this->_messageError);
        }

        if ($this->_hash->isValid($tok)) {
            $filter                           = new Zend_Filter();
            $filter->addFilter(new Zend_Filter_StripTags());
            $id                               = $filter->filter($id);
            $data['status']                   = 1;
            $data['messages']                 = $this->_messageSuccess;
            $lisIdioma                        = $idiomas->getIdiomasXid($id);
            $data['skill']['hidLanguage']     = $lisIdioma[0]['id_dominioIdioma'];
            $data['skill']['selLanguage']     = $lisIdioma[0]['id_idioma'];
            $data['skill']['selLevelWritten'] = $lisIdioma[0]['selLevelWritten'];
            $data['skill']['selLevelOral']    = $lisIdioma[0]['selLevelOral'];
        } else {
            $data = array(
                'status' => 0,
                'messages' => $this->_messageError);
        }
        $this->_response->appendBody(Zend_Json::encode($data));
        //  exit;
    }

    public function misIdiomasOldAction()
    {

        $this->view->menu_sel_side = self::MENU_POST_SIDE_IDIOMAS;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;
        $this->view->action        = 'mis-datos-personales';
        $session                   = new Zend_Session_Namespace('linkedin');
        $this->view->showImport    = false;

        $idPostulante = $this->idPostulante;

        $baseFormIdioma = new Application_Form_Paso2Idioma(true);
        $managerIdioma  = new App_Form_Manager($baseFormIdioma, 'managerIdioma',
            true);

        $formsIdioma = array();
        $index       = 0;
        $idioma      = new Application_Model_DominioIdioma();

        $arrayIdiomas = $idioma->getDominioIdioma($idPostulante);
        if (isset($session->linkedin)) {
            $this->view->showImport = true;
            $this->view->isLinkedin = true;
            $this->getMessenger()->success(
                "Se logró importar tus datos de linkedin. Por favor, ingresa o
					 selecciona los datos que no hayan coincidido exactamente con
					 los de AquiEmpleos."
            );
            $linkedinData           = $session->linkedin;
            $formsIdioma            = $this->_linkedinIdioma($linkedinData,
                $managerIdioma);
            unset($session->linkedin);
            $index                  = count($formsIdioma);
        }
        if (!$this->getRequest()->isPost()) {
            if (count($arrayIdiomas) != 0) {
                foreach ($arrayIdiomas as $idioma) {
                    $form                   = $managerIdioma->getForm($index++,
                        $idioma);
                    $form->setHiddenId($idioma['id_dominioIdioma']);
                    $form->setCabeceras($idioma['id_idioma'],
                        $idioma['nivel_idioma']);
                    $form->addValidatorsIdioma();
                    $formsIdioma[]          = $form;
                    $this->view->isLinkedin = true;
                    $this->view->isIdioma   = false;
                }
                $formsIdioma[] = $managerIdioma->getForm($index++);
            } elseif (!$this->getRequest()->isPost()) {
                $formsIdioma[] = $managerIdioma->getForm(0);
            }
        } else {
            //if ($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();

            $util         = $this->_helper->Util;
            $pattern      = '/managerIdioma_([0-9]*)_id_idioma/';
            $hayRepetidos = $util->getRepetido($pattern, $postData);

            $validExp               = $managerIdioma->isValid($postData);
            $this->view->isLinkedin = true;
            $this->view->isIdioma   = false;

            if ((bool) $hayRepetidos) {
                $idioma      = $this->view->ItemList('idioma', $hayRepetidos);
                $this->getMessenger()->error("Idioma repetido: ".$idioma);
                $formsIdioma = $managerIdioma->getForms();
            } elseif ($validExp) {
                $this->_actualizarIdiomaPostulante(
                    $managerIdioma->getCleanPost(), $idPostulante
                );

                //Actualizamos postulaciones.
                $helper = $this->_helper->getHelper("RegistrosExtra");
                $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);

                $this->getMessenger()->success($this->_messageSuccess);
                $this->_redirect(
                    Zend_Controller_Front::getInstance()
                        ->getRequest()->getRequestUri()
                );
            } else {
                $arrExp      = explode(',', $postData['managerIdioma']);
                foreach ($arrExp as $index)
                    $managerIdioma->removeForm($index);
                $formsIdioma = $managerIdioma->getForms();
            }
        }

        $this->view->formIdioma = $formsIdioma;
        $this->view->assign('managerIdioma', $managerIdioma);
    }

    public function misProgramasAction()
    {

        $this->view->menu_sel_side = self::MENU_POST_SIDE_PROGRAMAS;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;
        Zend_Layout::getMvcInstance()->assign(
            'submenuMiCuenta', $this->_submenuMiCuenta
        );


//        $idPostulante = $this->idPostulante;
//        $session = new Zend_Session_Namespace('linkedin');
//        $this->view->showImport = false;
//
//        if (isset($session->linkedin)) {
//            $this->view->showImport = true;
//            $this->getMessenger()->success(
//                "Se logró importar tus datos de linkedin. Por favor, ingresa o
//      selecciona los datos que no hayan coincidido exactamente con
//      los de APTiTUS."
//            );
//            $linkedinData = $session->linkedin;
//            $this->_linkedinSaveDatos($linkedinData,$idPostulante);
//            unset($session->linkedin);
//        }



        $idPostulante = $this->idPostulante;

        $formPrograma = new Application_Form_Paso2ProgramaNew();

        $programa       = new Application_Model_DominioProgramaComputo();
        //$arrayProgramasIds = Application_Model_ProgramaComputo::getProgramasComputoIds();
        $arrayProgramas = $programa->getDominioProgramaComputo($idPostulante);

        $this->view->itemProgramas = $arrayProgramas;
        $this->view->formPrograma  = $formPrograma;
        $this->view->slug          = $this->auth['postulante']['slug'];
    }

    public function misProgramasOldAction()
    {

        $session = $this->getSession();

        $this->view->menu_sel_side = self::MENU_POST_SIDE_PROGRAMAS;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;

        $idPostulante = $this->idPostulante;

        $baseFormPrograma = new Application_Form_Paso2Programa(true);
        $managerPrograma  = new App_Form_Manager($baseFormPrograma,
            'managerPrograma', true);

        $formsPrograma = array();
        $index         = 0;

        $programa = new Application_Model_DominioProgramaComputo();

        $arrayProgramas = $programa->getDominioProgramaComputo($idPostulante);
        if (!$this->getRequest()->isPost()) {
            if (count($arrayProgramas) != 0) {
                $arrayProgramasIds = Application_Model_ProgramaComputo::getProgramasComputoIds();
                foreach ($arrayProgramas as $programa) {
                    if (!array_key_exists($programa['id_programa_computo'],
                            $arrayProgramasIds)) {
                        $programa['is_disabled'] = 1;
                    }
                    $form                   = $managerPrograma->getForm($index++,
                        $programa);
                    $form->setHiddenId($programa['id_dominioComputo']);
                    $form->setCabeceras($programa['id_programa_computo'],
                        $programa['nivel']);
                    $form->addValidatorsPrograma();
                    $formsPrograma[]        = $form;
                    $this->view->isPrograma = false;
                }
                $formsPrograma[] = $managerPrograma->getForm($index++);
            } elseif (!$this->getRequest()->isPost()) {
                $formsPrograma[] = $managerPrograma->getForm(0);
            }
        } elseif ($this->getRequest()->isPost()) {
            $postData     = $this->_getAllParams();
            $util         = $this->_helper->Util;
            $pattern      = '/managerPrograma_([0-9]*)_id_programa/';
            $hayRepetidos = $util->getRepetido($pattern, $postData);

            $validExp               = $managerPrograma->isValid($postData);
            $this->view->isPrograma = false;

            if ((bool) $hayRepetidos) {
                $programaC     = $this->view->ItemList('ProgramaComputo',
                    $hayRepetidos);
                $this->getMessenger()->error("Programas Repetido: ".$programaC);
                $formsPrograma = $managerPrograma->getForms();
            } elseif ($validExp) {
                $this->_actualizarProgramaComputoPostulante(
                    $managerPrograma->getCleanPost(), $idPostulante
                );

                //Actualizamos postulaciones.
                $helper = $this->_helper->getHelper("RegistrosExtra");
                $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);

                $this->getMessenger()->success($this->_messageSuccess);
                $this->_redirect(
                    Zend_Controller_Front::getInstance()
                        ->getRequest()->getRequestUri()
                );
            } else {
                $arrExp        = explode(',', $postData['managerPrograma']);
                //var_dump($arrExp);
                foreach ($arrExp as $index)
                    $managerPrograma->removeForm($index);
                $formsPrograma = $managerPrograma->getForms();
            }
        } else if (isset($session->linkedin)) {

            $this->view->isLinkedin = true;
            $this->view->nombre     = $this->auth['postulante']['nombres'];
            $this->getMessenger()->success(
                "Se logró importar tus datos de linkedin. Por favor, ingresa o
					 selecciona los datos que no hayan coincidido exactamente con
					 los de AquiEmpleos."
            );
            $linkedinData           = $session->linkedin;

            $formsPrograma = array(
                $managerPrograma->getForm(0));
            unset($session->linkedin);
        }

        $this->view->formPrograma = $formsPrograma;
        $this->view->assign('managerPrograma', $managerPrograma);
    }

    public function misReferenciasAction()
    {
        $idPostulante               = $this->idPostulante;
        $modelExperiencia           = new Application_Model_Experiencia();
        $this->view->existe         = false;
        $formReferencia             = new Application_Form_Paso2ReferenciaNew($idPostulante,
            false);
        $this->view->lisReferencias = array();
        if ($modelExperiencia->getLogPostulanteExperianciaTotal($idPostulante) > 0) {
            $this->view->existe         = true;
            $referencias                = new Application_Model_Referencia();
            $this->view->lisReferencias = $referencias->getReferenciasPostulante($idPostulante);
        }
        $this->view->slug = $this->auth['postulante']['slug'];
        $this->view->form = $formReferencia;
    }

    public function cambioDeClaveAction()
    {
        $this->view->menu_sel_side = self::MENU_POST_SIDE_CAMBIOCLAVE;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;
        Zend_Layout::getMvcInstance()->assign(
            'subMenuPrivacidad', $this->_submenuPrivacidad
        );

        $arrayPostulante = $this->_postulante->getPostulante($this->idPostulante);

        $idUsuario            = $arrayPostulante['id_usuario'];
        $emailUsuario         = $arrayPostulante['email'];
        $formCambioClave      = new Application_Form_CambioClave($idUsuario);
        $formCambioClave->validarPswd($emailUsuario, $idUsuario);
        $this->view->username = $emailUsuario;
        $param                = $this->_getAllParams();

        if ($this->_request->isPost()) {

            $allParams  = $this->_getAllParams();
            $validClave = ( $formCambioClave->isValid($allParams) && $this->_hash->isValid($param['csrfhash']) );

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

                    $this->getMessenger()->success('Se cambió la clave con éxito.');
                } catch (Zend_Db_Exception $e) {
                    $db->rollBack();
                    $this->log->log($e->getMessage().'. '.$e->getTraceAsString(),
                        Zend_Log::ERR);
                    $this->getMessenger()->error('Error al cambiar la clave.');
                } catch (Zend_Exception $e) {
                    $this->log->log($e->getMessage().'. '.$e->getTraceAsString(),
                        Zend_Log::ERR);
                    $this->getMessenger()->error($this->_messageSuccess);
                }
            } else {
                $this->getMessenger()->error("La contraseña proporcionada no coincide con la actual");
            }
        }
        $this->view->formCambioClave = $formCambioClave;
    }

    public function misAlertasAction()
    {
        $this->view->modulo        = $this->getRequest()->getModuleName();
        $this->view->menu_sel_side = self::MENU_POST_SIDE_ALERTAS;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;
        Zend_Layout::getMvcInstance()->assign(
            'subMenuPrivacidad', $this->_submenuPrivacidad
        );

        $idPostulante = $this->idPostulante;
        $formAlertas  = new Application_Form_MisAlertas();

        $postulante       = new Application_Model_Postulante();
        $alertaPostulante = $postulante->getAlertaPostulante($idPostulante);

        $formAlertas->setDefaults($alertaPostulante);

        if ($this->_request->isPost() && $this->_hash->isValid($this->_getParam("csrfhash"))) {
            $allParams    = $this->_getAllParams();
            $validAlertas = $formAlertas->isValid($allParams);
            if ($validAlertas) {
                $valuesAlertas = $formAlertas->getValues();
                $where         = $postulante->getAdapter()
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

    public function miPerfilAction()
    {

        $this->view->menu_sel_side = self::MENU_POST_SIDE_PERFILPUBLICO;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;
        $this->view->url_id        = $this->_getParam('url_id');
        $this->view->slug          = $this->auth['postulante']['slug'];
        $id                        = $this->auth['postulante']['id'];
        $perfil                    = $this->_postulante->getPerfilPostulante($id);

        $perfil['postulante']['fotovariable'] = $perfil['postulante']['path_foto'];

        $this->view->postulante = $perfil;

        $usuario       = $this->auth['usuario'];
        $postulante    = $this->auth['postulante'];
        $formCompartir = new Application_Form_CompartirPorMail();
        $formCompartir->setAction('/mi-cuenta/compartir');
        $formCompartir->correoEmisor->setValue($usuario->email);
        $formCompartir->hdnOculto->setValue(
            $perfil['postulante']['slug']
        );
        $formCompartir->nombreEmisor->setValue(
            ucwords($postulante['nombres']).' '.ucwords($postulante['apellidos'])
        );
        Zend_Layout::getMvcInstance()->assign(
            'compartirPorMail', $formCompartir
        );
    }

    public function compartirAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $config   = Zend_Registry::get("config");
        $dataPost = $this->_getAllParams();

        $dataPost['mensajeCompartir'] = preg_replace($config->avisopaso2->expresionregular,
            '', $dataPost['mensajeCompartir']);
        $dataPost['mensajeCompartir'] = str_replace("@", "",
            $dataPost['mensajeCompartir']);

        $urlPerfil = $this->view->url(
            array(
            'slug' => $dataPost['hdnOculto']), 'perfil_publico', true
        );

        $this->_helper->Mail->compartirPerfilPostulante(
            array(
                'to' => $dataPost['correoReceptor'],
                'nombreReceptor' => $dataPost['nombreReceptor'],
                'nombreEmisor' => $dataPost['nombreEmisor'],
                'mensajeCompartir' => $dataPost['mensajeCompartir'],
                'avisoUrl' => SITE_URL.$urlPerfil
            )
        );

        $response = array(
            'status' => 'ok',
            'msg' => 'Se compartió por correo el perfil.'
        );
        $this->_response->appendBody(Zend_Json::encode($response));
    }

    public function privacidadAction()
    {
        $this->view->menu_sel_side = self::MENU_POST_SIDE_PRIVACIDAD;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;
        Zend_Layout::getMvcInstance()->assign(
            'subMenuPrivacidad', $this->_submenuPrivacidad
        );

        $idPostulante = $this->idPostulante;

        $postulante                       = new Application_Model_Postulante();
        $privacidad                       = $postulante->getPostulantePrivacidad($idPostulante);
        $this->view->privacidadPostulante = (int) $privacidad;
        $allParams                        = $this->_getAllParams();
        if ($this->_request->isPost() && isset($allParams['csrfhash'])) {

            if ($this->_hash->isValid($allParams['csrfhash'])) {
                $where            = $postulante->getAdapter()->quoteInto('id = ?',
                    $idPostulante);
                $hoy              = date('Y-m-d H:i:s');
                $valuesPrivacidad = array(
                    'prefs_confidencialidad' => (bool) $allParams['fPrivacCP'],
                    'ultima_actualizacion' => $hoy,
                    'last_update_ludata' => $hoy
                );
                $postulante->update($valuesPrivacidad, $where);

                $sc           = new Solarium\Client($this->config->solr);
                $moPostulante = new Solr_SolrAbstract($sc, 'postulante');
                if ($valuesPrivacidad['prefs_confidencialidad']) {
                    $moPostulante->deletePostulante((int) $idPostulante);
                } else {
                    $moPostulante->addPostulante($idPostulante);
                }

                $this->getMessenger()->success('Cambio de privacidad con éxito.');
                $this->_redirect(
                    Zend_Controller_Front::getInstance()
                        ->getRequest()->getRequestUri()
                );
            }
        }
    }

    public function redesSocialesAction()
    {
        $this->view->menu_sel_side = self::MENU_POST_SIDE_REDES_SOCIALES;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;


        // action body
        $redesSociales          = new Application_Model_CuentaRs();
        $redes                  = $redesSociales->getRedesByUser($this->usuario->id);
        $this->view->isFacebook = false;
        $this->view->isGoogle   = false;
        foreach ($redes as $red) {
            if ($red['rs'] == 'facebook') {
                $this->view->isFacebook = true;
            }
            if ($red['rs'] == 'google') {
                $this->view->isGoogle = true;
            }
        }
        $config                    = $this->getConfig();
        $this->view->openUrl       = sprintf(
            $config->apis->google->openidUrl,
            $config->app->siteUrl.'/'.$config->apis->google->returnUrl,
            $config->app->siteUrl
        );
        $this->view->facebookAppId = $config->apis->facebook->appid;
        $this->view->urlFacebook   = $config->app->siteUrl
            .'/mi-cuenta/agregar-cuenta-facebook';
        $this->view->redes         = $redes;
    }

    public function agregarCuentaFacebookAction()
    {
        $code = $this->getRequest()->getParam('code', 0);
        if (empty($code)) {
            $this->_redirect('/');
        }
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $config    = $this->getConfig();
        $appId     = $config->apis->facebook->appid;
        $appSecret = $config->apis->facebook->appsecret;
        $url       = $config->app->siteUrl
            .'/mi-cuenta/agregar-cuenta-facebook';
        $tokenUrl  = "https://graph.facebook.com/v2.0/oauth/access_token?"
            ."client_id=".$appId."&redirect_uri=".urlencode($url)
            ."&client_secret=".$appSecret."&code=".$_REQUEST["code"];

        $response = file_get_contents($tokenUrl);
        $params   = null;
        parse_str($response, $params);

        $graphUrl = "https://graph.facebook.com/v2.0/me?access_token="
            .$params['access_token'];

        $facebookUser       = json_decode(file_get_contents($graphUrl));
        $data['id_usuario'] = $this->usuario->id;
        $data['rsid']       = $facebookUser->id;
        $data['rs']         = 'facebook';
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

    public function agregarCuentaGoogleAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $dataGoogle = $this->getRequest()->getParams();
        $config     = $this->getConfig();
        if (!isset($dataGoogle)) {
            $this->_redirect('/');
        } else {

            if (isset($dataGoogle['openid_mode']) &&
                $dataGoogle['openid_mode'] == 'cancel') {

                $this->_redirect('/mi-cuenta/redes-sociales');
            } else {
                $data['id_usuario'] = $this->usuario->id;
                $data['rsid']       = str_replace(
                    $config->apis->google->urlResponse, "",
                    $dataGoogle['openid_claimed_id']
                );
                $google             = new Zend_Session_Namespace('google');
                $data['rsid']       = $google->user_id;
                $data['rs']         = 'google';
                $data['screenname'] = $google->email;
                $red                = new Application_Model_CuentaRs();
                if ($red->existeGoogle($this->usuario->id) === false) {
                    $red->insert($data);
                }
                $this->_redirect('/mi-cuenta/redes-sociales');
            }
        }
    }

    public function eliminarCuentaFacebookAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $usuario = $this->usuario;
        $red     = new Application_Model_CuentaRs();
        $red->eliminarCuentaFacebookByUsuario($this->usuario->id);
        $this->_redirect('/mi-cuenta/redes-sociales');
    }

    public function eliminarCuentaGoogleAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $usuario = $this->usuario;
        $red     = new Application_Model_CuentaRs();
        $red->eliminarCuentaGoogleByUsuario($this->usuario->id);
        $this->_redirect('/mi-cuenta/redes-sociales');
    }

    public function perfilPublicoAction()
    {
        $slug   = $this->_getParam('slug');
        $perfil = $this->_postulante->getPerfilPostulante($slug);

        //var_dump($perfil);exit;
        if (!isset($this->auth['postulante'])) {
            $authSlug = '';
        } else {
            $authSlug = $this->auth['postulante']['slug'];
        }
        if ($perfil['postulante']['esConfidencial'] == 1 && $authSlug != $slug) {
            $this->_forward('warning-profile-private', 'error', 'postulante');
        }
        if ($perfil == false) {
            $this->_redirect('/');
        }
        $this->view->headTitle()->set(
            'Perfil público de '.
            $perfil['postulante']['nombres'].' '.
            $perfil['postulante']['apellidos'].
            ' | AquiEmpleos'
        );

        $this->view->headMeta()->appendName(
            'robots', 'noindex'
        );


        $this->view->headMeta()->appendName(
            "description",
            "Perfil Publico de ".$perfil['postulante']['nombres'].
            " ".$perfil['postulante']['apellidos']." en AquiEmpleos - ".
            $perfil['postulante']['presentacion']
        );

        $keywords = "Perfil Publico de ".$perfil['postulante']['nombres'].
            " ".$perfil['postulante']['apellidos'];

        if (count($perfil['experiencias']) > 0) {
            $experiencia = $perfil['experiencias'][0];
            $keywords .= ", ".$experiencia['puesto']." en ".$experiencia['empresa'];
        }

        $this->view->headMeta()->appendName("keywords", $keywords);
        $this->_helper->layout->setLayout('perfil_publico');
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array(
            'id' => 'profilePublic')
        );
        $perfil['postulante']['fotovariable'] = $perfil['postulante']['path_foto'];
        $this->view->slug                     = $slug;
        $this->view->postulante               = $perfil;
        if ($this->isAuth &&
            $this->auth['usuario']->rol == Application_Form_Login::ROL_POSTULANTE) {
            $usuario      = $this->auth['usuario'];
            $postulante   = $this->auth['postulante'];
            $correoEmisor = $usuario->email;
            $slugEmisor   = $perfil['postulante']['slug'];
            $nombreEmisor = ucwords($postulante['nombres']).' '.
                ucwords($postulante['apellidos']);
        } else {
            $correoEmisor = $slugEmisor   = $nombreEmisor = "";
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


    public function borrarEstudioAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if ($this->_hash->isValid($this->_getParam('csrfhash'))) {
            $idEstudio = $this->_getParam('id', false);
            $data      = $this->EliminarElemento('Estudio', $idEstudio);

            $postulanteId = $this->auth["postulante"]["id"];

            $estudioModelo    = new Application_Model_Estudio;
            $estudioPrincipal = $estudioModelo->obtenerEstudiosMayorPesoPorPostulante(
                $postulanteId);

            if (!empty($estudioPrincipal)) {
                $estudioModelo->actualizarEstudioPrincipal(
                    $postulanteId, $estudioPrincipal['id']);
            }

            $extra  = array(
                'csrfhash' => CSRF_HASH
            );
            //Actualizamos postulaciones.
            $helper = $this->_helper->getHelper("RegistrosExtra");
            $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);
            $helper->ActualizarEstudios($this->auth["postulante"]["id"]);

            //ACTUALIZACION DE ZENDLUCENE
            //$this->_actualizarPostulanteZendLucene($this->auth["postulante"]["id"]);

            $this->_response->appendBody(Zend_Json::encode($data + $extra));
        } else {
            $extra = array(
                'csrfhash' => CSRF_HASH,
                'message' => $this->_messageFail
            );
            echo (Zend_Json::encode($extra));
        }
    }

    public function borrarIdiomaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $hash = $this->_getParam('csrfhash');
        if ($this->_hash->isValid($hash)) {
            $idIdioma = $this->_getParam('id', false);
            $data     = $this->EliminarElemento('Idioma', $idIdioma);
            $extra    = array(
                'csrfhash' => CSRF_HASH
            );
            //Actualizamos postulaciones.
            $helper   = $this->_helper->getHelper("RegistrosExtra");
            $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);

            //ACTUALIZACION DE ZENDLUCENE
            //$this->_actualizarPostulanteZendLucene($this->auth["postulante"]["id"]);

            $this->_response->appendBody(Zend_Json::encode($data + $extra));
        } else {
            $extra = array(
                'csrfhash' => CSRF_HASH,
                'message' => $this->_messageFail
            );
            echo (Zend_Json::encode($extra));
        }
    }

    public function borrarProgramaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if ($this->_hash->isValid($this->_getParam('csrfhash'))) {
            $idPrograma = $this->_getParam('id', false);
            $data       = $this->EliminarElemento('ProgramaComputo',
                $idPrograma, 'Informática');
            $extra      = array(
                'csrfhash' => CSRF_HASH
            );
            //Actualizamos postulaciones.
            $helper     = $this->_helper->getHelper("RegistrosExtra");
            $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);

            //ACTUALIZACION DE ZENDLUCENE
            //$this->_actualizarPostulanteZendLucene($this->auth["postulante"]["id"]);

            $this->_response->appendBody(Zend_Json::encode($data + $extra));
        } else {
            $extra = array(
                'csrfhash' => CSRF_HASH,
                'message' => $this->_messageFail
            );
            echo (Zend_Json::encode($extra));
        }
    }

    public function borrarReferenciaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if ($this->_hash->isValid($this->_getParam('csrfhash'))) {
            $idReferencia = $this->_getParam('id', false);
            $data         = $this->EliminarElemento('Referencia', $idReferencia);
            $extra        = array(
                'csrfhash' => CSRF_HASH
            );
            $this->_response->appendBody(Zend_Json::encode($data + $extra));
        } else {
            $extra = array(
                'csrfhash' => CSRF_HASH,
                'message' => $this->_messageFail
            );
            echo (Zend_Json::encode($extra));
        }
    }

    public function borrarExperienciaAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idExperiencia = $this->_getParam('id', false);
        $data          = $this->EliminarElemento('Experiencia', $idExperiencia,
            'experiencia');

        if (isset($data['iscompleted'][1]) && ($data['iscompleted'][1] == 0)) {
            // $this->linkedImported->guardarDatos('experiencia',$this->idPostulante,0);
        }

        $helper = $this->_helper->getHelper("RegistrosExtra");
        $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);
        $helper->ActualizarExperiencias($this->auth["postulante"]["id"]);

        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarEstudioAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idEstudio = (int) $this->_getParam('id', false);
        $data      = $this->EliminarElemento('Estudio', $idEstudio, 'estudios');


        if (isset($data['iscompleted'][1]) && ($data['iscompleted'][1] == 0)) {
            //  $this->linkedImported->guardarDatos('estudio',$this->idPostulante,0);
        }

        $helper = $this->_helper->getHelper("RegistrosExtra");
        $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);
        $helper->ActualizarEstudios($this->auth["postulante"]["id"]);

        $postulanteId     = $this->auth["postulante"]["id"];
        $estudioModelo    = new Application_Model_Estudio;
        $estudioPrincipal = $estudioModelo->
            obtenerEstudiosMayorPesoPorPostulante($postulanteId);

        if (!empty($estudioPrincipal)) {
            $estudioModelo->actualizarEstudioPrincipal(
                $postulanteId, $estudioPrincipal['id']);
        }

        $extra = array(
            'csrfhash' => $this->_hash
        );

        $this->_response->appendBody(Zend_Json::encode($data + $extra));
    }

    public function borrarIdiomaAjaxAction()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $idIdioma = $this->_getParam('id', false);
            $data     = $this->EliminarElemento('Idioma', $idIdioma, "Idiomas");
            $helper   = $this->_helper->getHelper("RegistrosExtra");
            $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);

            if (isset($data['iscompleted'][1]) && ($data['iscompleted'][1] == 0)) {
                // $this->linkedImported->guardarDatos('idioma',$this->idPostulante,0);
            }

            $this->_response->appendBody(Zend_Json::encode($data));
        } else {
            $this->_redirect('/mi-cuenta/');
        }
    }

    public function borrarProgramaAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idPrograma = $this->_getParam('id', false);
        $data       = $this->EliminarElemento('ProgramaComputo', $idPrograma,
            "Informática");
        $helper     = $this->_helper->getHelper("RegistrosExtra");
        $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarReferenciaAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idReferencia = $this->_getParam('id', false);
        $data         = $this->EliminarElemento('Referencia', $idReferencia);
        $helper       = $this->_helper->getHelper("RegistrosExtra");
        $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarLogroAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idLogro = $this->_getParam('id', false);
        $data    = $this->EliminarElemento('Logro', $idLogro, 'Logros');
        $helper  = $this->_helper->getHelper("RegistrosExtra");
        $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);
//        $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
//                $this->auth["postulante"]["id"], Application_Model_LogPostulante::LOGROS
//        );
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function borrarOtroEstudioAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idOtroEstudio = $this->_getParam('id', false);
        $data          = $this->EliminarElemento('OtroEstudio', $idOtroEstudio,
            'OtroEstudio');
        $helper        = $this->_helper->getHelper("RegistrosExtra");
        $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);

        $this->_response->appendBody(Zend_Json::encode($data));
    }
    /*
     * Elimina el elemento del Formulario ...
     *
     * @param string $valor     Nombre del elemento a eliminar
     * @param int $idElemento   ID del elemento a eliminar
     * @return array
     */

    public function EliminarElemento($valor, $idElemento, $accion = '')
    {

        $var               = '';
        $valoresPermitidos = array(
            'Experiencia',
            'Estudio',
            'Idioma',
            'ProgramaComputo',
            'Referencia',
            'Logro',
            'Hobby',
            'OtroEstudio',
        );

        if (in_array($valor, $valoresPermitidos)) {
            $var = '_eliminar'.$valor.'Postulante';
        }

        $ok             = ($this->_request->isPost()) ? $this->$var($idElemento)
                : false;
        $dataLog        = array(
            'id' => $this->auth["postulante"]["id"]);
        $dataporcentaje = array();
        if ($accion == 'experiencia') {
            $accion         = 'Experiencia';
            $dataporcentaje = $this->_helper->LogActualizacionBI->logActualizacionPostulanteExperiencia($dataLog);
        } elseif ($accion == 'estudios') {
            $accion         = 'Estudios';
            $dataporcentaje = $this->_helper->LogActualizacionBI->logActualizacionPostulanteEstudio($dataLog);
        } elseif ($accion == 'Informática') {
            $dataporcentaje = $this->_helper->LogActualizacionBI->logActualizacionPostulanteProgramas($dataLog);
        } elseif ($accion == 'Idiomas') {
            $dataporcentaje = $this->_helper->LogActualizacionBI->logActualizacionPostulanteIdioma($dataLog);
        } elseif ($accion == 'OtroEstudio') {
            $accion         = 'Otros Estudios';
            $dataporcentaje = $this->_helper->LogActualizacionBI->logActualizacionPostulanteOtrosEstudios($dataLog);
        } elseif ($accion == 'Logros') {
            $dataporcentaje = $this->_helper->LogActualizacionBI->logActualizacionPostulantelogros($dataLog);
        }
        $data = array(
            'status' => $ok ? 1 : 0,
            'iscompleted' => array(
                $accion,
                $dataporcentaje['iscompleted']),
            'percent' => $dataporcentaje['total_completado'],
            'message' => $ok ? 'Sus datos fueron eliminados correctamente' : 'Hubo un error, intente nuevamente'
        );
//       @$this->_cache->remove('get_perfil_postulante_' . str_replace('-', '_', $this->_slug));
//       @$this->_cache->remove('get_perfil_postulante_' . str_replace('-', '_', $this->idPostulante));
//       @$this->_cache->remove('perfil_postulante_' . str_replace('-', '_', $this->_slug));

        return $data;
    }

    //funciones Privadas
    private function _crearSlug($valuesPostulante, $lastId)
    {
        $slugFilter = new App_Filter_Slug(
            array(
            'field' => 'slug',
            'model' => $this->_postulante)
        );

        $slug = $slugFilter->filter(
            $valuesPostulante['nombres'].' '.
            $valuesPostulante['apellidos'].' '.
            substr(md5($lastId), 0, 8)
        );
        return $slug;
    }

    private function _renameFile($formPostulante, $pathFoto)
    {
        $file        = $formPostulante->$pathFoto->getFileName();
        $nuevoNombre = '';

        if ($file != null) {
            $microTime      = microtime();
            $salt           = 'EMPLEOBUSCO';
            $nombreOriginal = pathinfo($file);
            $rename         = md5($microTime.$salt).
                '.'.$nombreOriginal['extension'];
            $nuevoNombre    = $rename;
            $formPostulante->$pathFoto->addFilter('Rename', $nuevoNombre);
            $formPostulante->$pathFoto->receive();
        }

        return $nuevoNombre;
    }

    //Mantenimiento Experiencia


    private function _actualizarExperienciaPostulante(
    $managerCleanPost, $idPostulante
    )
    {

        //$session = $this->getSession();
        $session          = new Zend_Session_Namespace('linkedin');
        $count            = 0;
        $idExperienciaNew = 0;

        foreach ($managerCleanPost as $form) {

            $data  = $form;
            $idExp = $data['id_Experiencia'];

            if (isset($session->linkedin)) {
                $val = $session->linkedin->positions->position->toArray();
                foreach ($val as $linkedin) {
                    if ($linkedin['id'] == $idExp) $idExp = '';
                }
            }

            $experiencia = new Application_Model_Experiencia();
            if ($data['en_curso'] == 1) {
                $data['fin_mes'] = null;
                $data['fin_ano'] = null;
            }

            if ($data['lugar'] == 1) {
                $data['nombre_proyecto']  = '';
                $data['costo_proyecto']   = 0;
                $data['id_tipo_proyecto'] = 0;
            }

            $data['otro_puesto'] = ucfirst(strtolower($data['otro_puesto']));
            unset($data['id_Experiencia']);
            unset($data['is_disabled']);

            if ($idExp) {
                $where = $experiencia->getAdapter()
                        ->quoteInto('id_postulante = ?', $idPostulante).
                        $experiencia->getAdapter()
                        ->quoteInto(' and id = ?', $idExp);
                //if ($data['otra_empresa'] != '' || $data['otro_puesto'] != '' || $data['otro_rubro'] !='')
                if ($data['id_nivel_puesto'] != 0 && $data['id_puesto'] != 0 && $data['id_area']
                    && $data['otra_empresa'])
                        $experiencia->update($data, $where);
            } else {
                $data['id_postulante'] = $idPostulante;

                if ($data['id_nivel_puesto'] != 0 && $data['id_area'] != 0 && $data['otra_empresa']
                    != '' && $data['id_puesto'] != 0) {
                    $idExperienciaNew = $experiencia->insert($data);
                }
            }
        }


        $postulante = new Application_Model_Postulante();
        $where      = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')), $where
        );
        $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
            $idPostulante, Application_Model_LogPostulante::EXPERIENCIA
        );

        return $idExperienciaNew;
    }

    //Validación Experiencia
    private function _validExperiencia($managerCleanPost)
    {

        $save = true;
        foreach ($managerCleanPost as $form) {
            $data = $form;
            if ($data['id_nivel_puesto'] == 0 || $data['id_area'] == 0 ||
                $data['otra_empresa'] == '' || $data['id_puesto'] == 0) {
                $save = false;
            }
        }

        return $save;
    }

    //Validación Estudio
    private function _validEstudio($managerCleanPost)
    {

        $save = true;
        foreach ($managerCleanPost as $form) {

            $data = $form;
            if ($data['id_nivel_estudio'] == 0 || $data['pais_estudio'] == 0)
                    $save = false;

            if (($data['id_nivel_estudio'] == 4 || $data['id_nivel_estudio'] == 5
                || $data['id_nivel_estudio'] == 6 || $data['id_nivel_estudio'] == 7
                || $data['id_nivel_estudio'] == 8 || $data['id_nivel_estudio'] == 10
                || $data['id_nivel_estudio'] == 11 || $data['id_nivel_estudio'] == 12)
                && $data['id_carrera'] == 0) $save = false;
        }

        return $save;
    }

    private function _eliminarExperienciaPostulante($idExperiencia)
    {
        if ($idExperiencia) {
            $experiencia = new Application_Model_Experiencia();
            $where       = array(
                'id=?' => $idExperiencia);
            $r           = (bool) $experiencia->delete($where);
            $postulante  = new Application_Model_Postulante();
            $where       = $postulante->getAdapter()->quoteInto('id = ?',
                $this->idPostulante);
            $postulante->update(
                array(
                'ultima_actualizacion' => date('Y-m-d H:i:s'),
                'last_update_ludata' => date('Y-m-d H:i:s')), $where
            );
//            $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
//                    $this->idPostulante, Application_Model_LogPostulante::EXPERIENCIA
//            );
        } else {
            $r = false;
        }
        return $r;
    }

    //Mantenimiento Estudio
    private function _actualizarEstudioPostulante(
    $managerCleanPost, $idPostulante
    )
    {
        $instituciones      = new Application_Model_Institucion();
        $carreras           = new Application_Model_Carrera();
        $listaInstituciones = $instituciones->getInstituciones();
        $listaCarreras      = $carreras->getCarreras();

        $idEstudioNew = 0;
        $count        = 0;
        foreach ($managerCleanPost as $form) {

            $data    = $form;
            $idEst   = $data['id_estudio'];
            $estudio = new Application_Model_Estudio();
            if ($data['id_nivel_estudio'] == 1) {
                $data['id_carrera']       = null;
                $data['en_curso']         = 0;
                $data['otro_institucion'] = null;
                $data['pais_estudio']     = 0;
            } else {
                $data['en_curso']         = (bool) $data['en_curso'];
                $data['otro_institucion'] = $data['institucion'];
            }
            unset($data['id_estudio']);
            unset($data['institucion']);
            unset($data['is_disabled']);

            if (!isset($data['id_institucion']) || $data['id_institucion'] == 0 || $data['id_institucion']
                == '') {
                $data['id_institucion'] = null;
            } else {
                if (array_key_exists($data['id_institucion'],
                        $listaInstituciones)) {
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
            if (array_key_exists($data['id_carrera'], $listaCarreras) && $data['id_carrera']
                != 15) {
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
                        ->quoteInto('id_postulante = ?', $idPostulante).
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

        $estudioModelo    = new Application_Model_Estudio;
        $estudioPrincipal = $estudioModelo->obtenerEstudiosMayorPesoPorPostulante($idPostulante);

        if (!empty($estudioPrincipal)) {
            $estudioModelo->actualizarEstudioPrincipal($idPostulante,
                $estudioPrincipal['id']);
        }

        $postulante = new Application_Model_Postulante();
        $where      = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')
            ), $where
        );
        $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
            $idPostulante, Application_Model_LogPostulante::ESTUDIOS
        );

        return $idEstudioNew;
    }

    private function _eliminarEstudioPostulante($idEstudio)
    {
        if ($idEstudio) {
            $estudio = new Application_Model_Estudio();
            $where   = array(
                'id=?' => $idEstudio);
            $r       = (bool) $estudio->delete($where);

            $postulante = new Application_Model_Postulante();
            $wherePost  = $postulante->getAdapter()->quoteInto('id = ?',
                $this->idPostulante);
            $postulante->update(array(
                'ultima_actualizacion' => date('Y-m-d H:i:s'),
                'last_update_ludata' => date('Y-m-d H:i:s')
                ), $wherePost
            );
//            $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
//                    $this->idPostulante, Application_Model_LogPostulante::ESTUDIOS
//            );
        } else {
            $r = false;
        }
        return $r;
    }

    //Mantenimiento Idioma
    private function _actualizarIdiomaPostulante($managerCleanPost,
                                                 $idPostulante)
    {

        $idIdiomaNew = 0;
        $count       = 0;
        foreach ($managerCleanPost as $form) {

            $data   = $form;
            $idEst  = $data['id_dominioIdioma'];
            unset($data['cabecera_idioma']);
            unset($data['cabecera_nivel']);
            unset($data['is_disabled']);
            $idioma = new Application_Model_DominioIdioma();
            unset($data['id_dominioIdioma']);
            if ($data['nivel_idioma'] != "0" && $data['id_idioma'] != "0") {
                if ($idEst) {
                    $data['nivel_lee']     = $data['nivel_idioma'];
                    $data['nivel_escribe'] = $data['nivel_idioma'];
                    $data['nivel_hablar']  = $data['nivel_idioma'];
                    unset($data['nivel_idioma']);

                    $where = $idioma->getAdapter()
                            ->quoteInto('id_postulante = ?', $idPostulante).
                            $idioma->getAdapter()
                            ->quoteInto(' and id = ?', $idEst);
                    $idioma->update($data, $where);
                } else {
                    $data['id_postulante'] = $idPostulante;
                    $data['nivel_lee']     = $data['nivel_idioma'];
                    $data['nivel_escribe'] = $data['nivel_idioma'];
                    $data['nivel_hablar']  = $data['nivel_idioma'];
                    unset($data['nivel_idioma']);

                    $idIdiomaNew = $idioma->insert($data);
                }
            }
        }
        $postulante = new Application_Model_Postulante();
        $where      = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')), $where
        );
        $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
            $idPostulante, Application_Model_LogPostulante::IDIOMAS
        );
        return $idIdiomaNew;
    }

    //Mantenimiento Idioma
    private function _UpdateIdiomaPostulante($data, $idPostulante)
    {


        $idIdiomaNew = 0;
        $count       = 0;
        //   foreach ($managerCleanPost as $data =>$key) {
        $idEst       = 0;
        //$data = $form;
        if (isset($data['hidLanguage'])) {
            $idEst = $data['hidLanguage'];
            unset($data['hidLanguage']);
        }
        $idioma = new Application_Model_DominioIdioma();

        if ($idEst) {
            $dataIdioma['id_idioma']     = isset($data['selLanguage']) ? $data['selLanguage']
                    : 'es';
            $dataIdioma['nivel_lee']     = $data['selLevelOral'];
            $dataIdioma['nivel_escribe'] = $data['selLevelWritten'];
            $dataIdioma['nivel_hablar']  = $data['selLevelOral'];
            unset($data['selLanguage']);
            $where                       = $idioma->getAdapter()
                    ->quoteInto('id_postulante = ?', $idPostulante).
                    $idioma->getAdapter()
                    ->quoteInto(' and id = ?', $idEst);
            $idioma->update($dataIdioma, $where);
            $idIdiomaNew                 = $idEst;
            $result['id']                = $idEstudio;
            $mensaje                     = $this->_messageSuccessActualizar;
        } else {
            $dataIdioma['id_idioma']     = isset($data['selLanguage']) ? $data['selLanguage']
                    : 'es';
            $dataIdioma['id_postulante'] = $idPostulante;
            $dataIdioma['nivel_lee']     = $data['selLevelOral'];
            $dataIdioma['nivel_escribe'] = $data['selLevelWritten'];
            $dataIdioma['nivel_hablar']  = $data['selLevelOral'];
            $idIdiomaNew                 = $idioma->insert($dataIdioma);
            $mensaje                     = $this->_messageSuccessRegistrar;
        }

        $postulante = new Application_Model_Postulante();
        $where      = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')), $where
        );

        return array(
            'id' => $idIdiomaNew,
            'mensaje' => $mensaje);
    }

    private function _UpdateLogroPostulante($data, $idPostulante)
    {


        $idIdiomaNew = 0;
        $count       = 0;
        //   foreach ($managerCleanPost as $data =>$key) {
        $idEst       = 0;
        //$data = $form;
        if (isset($data['hidLanguage'])) {
            $idEst = $data['hidLanguage'];
            unset($data['hidLanguage']);
        }
        $idioma = new Application_Model_DominioIdioma();

        if ($idEst) {
            $dataIdioma['id_idioma']     = isset($data['selLanguage']) ? $data['selLanguage']
                    : 'es';
            $dataIdioma['nivel_lee']     = $data['selLevelOral'];
            $dataIdioma['nivel_escribe'] = $data['selLevelWritten'];
            $dataIdioma['nivel_hablar']  = $data['selLevelOral'];
            unset($data['selLanguage']);
            $where                       = $idioma->getAdapter()
                    ->quoteInto('id_postulante = ?', $idPostulante).
                    $idioma->getAdapter()
                    ->quoteInto(' and id = ?', $idEst);
            $idioma->update($dataIdioma, $where);
            $idIdiomaNew                 = $idEst;
        } else {
            $dataIdioma['id_idioma']     = isset($data['selLanguage']) ? $data['selLanguage']
                    : 'es';
            $dataIdioma['id_postulante'] = $idPostulante;
            $dataIdioma['nivel_lee']     = $data['selLevelOral'];
            $dataIdioma['nivel_escribe'] = $data['selLevelWritten'];
            $dataIdioma['nivel_hablar']  = $data['selLevelOral'];
            $idIdiomaNew                 = $idioma->insert($dataIdioma);
        }

        $postulante = new Application_Model_Postulante();
        $where      = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')), $where
        );
        $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
            $idPostulante, Application_Model_LogPostulante::IDIOMAS
        );
        return $idIdiomaNew;
    }

    private function _eliminarIdiomaPostulante($idIdioma)
    {
        if ($idIdioma) {
            $idioma = new Application_Model_DominioIdioma();
            $where  = array(
                'id=?' => $idIdioma);
            $r      = (bool) $idioma->delete($where);

            $postulante = new Application_Model_Postulante();
            $where      = $postulante->getAdapter()->quoteInto('id = ?',
                $this->idPostulante);
            $postulante->update(
                array(
                'ultima_actualizacion' => date('Y-m-d H:i:s'),
                'last_update_ludata' => date('Y-m-d H:i:s')), $where
            );
//            $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
//                    $this->idPostulante, Application_Model_LogPostulante::IDIOMAS
//            );
        } else {
            $r = false;
        }
        return $r;
    }

    //Mantenimiento Programa Computo
    private function _actualizarProgramaComputoPostulante(
    $managerCleanPost, $idPostulante
    )
    {
        $idProgramaNew = 0;
        $count         = 0;
        foreach ($managerCleanPost as $form) {

            $data  = $form;
            $idEst = $data['id_dominioComputo'];

            $programa = new Application_Model_DominioProgramaComputo();
            unset($data['id_dominioComputo']);
            unset($data['cabecera_programa']);
            unset($data['cabecera_nivel']);
            unset($data['is_disabled']);
            unset($data['nombre']);

            if ($data['nivel'] != "0" && $data['id_programa_computo'] != "0") {
                if ($idEst) {

                    $where = $programa->getAdapter()
                            ->quoteInto('id_postulante = ?', $idPostulante).
                            $programa->getAdapter()
                            ->quoteInto(' and id = ?', $idEst);
                    $programa->update($data, $where);
                } else {
                    $data['id_postulante'] = $idPostulante;
                    $idProgramaNew         = $programa->insert($data);
                }
            }
        }
        $postulante = new Application_Model_Postulante();
        $where      = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')), $where
        );
        $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
            $idPostulante, Application_Model_LogPostulante::PROGRAMAS
        );
        return $idProgramaNew;
    }

    private function _eliminarProgramaComputoPostulante($idPrograma)
    {
        if ($idPrograma) {
            $programa   = new Application_Model_DominioProgramaComputo();
            $where      = array(
                'id=?' => $idPrograma);
            $r          = (bool) $programa->delete($where);
            $postulante = new Application_Model_Postulante();
            $where      = $postulante->getAdapter()->quoteInto('id = ?',
                $this->idPostulante);
            $postulante->update(
                array(
                'ultima_actualizacion' => date('Y-m-d H:i:s'),
                'last_update_ludata' => date('Y-m-d H:i:s')
                ), $where
            );
//            $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
//                    $this->idPostulante, Application_Model_LogPostulante::PROGRAMAS
//            );
        } else {
            $r = false;
        }
        return $r;
    }

    //Mantenimiento Referencia
    private function _actualizarReferenciaPostulante(
    $managerCleanPost, $idPostulante
    )
    {

        $valor           = false;
        $idReferenciaNew = 0;
        $count           = 0;
        foreach ($managerCleanPost as $form) {

            $data                   = $form;
            $data['id_experiencia'] = $data['listaexperiencia'];
            $idRef                  = $data['id_referencia'];

            $referencia = new Application_Model_Referencia();
            unset($data['id_referencia']);
            unset($data['listaexperiencia']);
            if ($idRef) {
                if ($data['id_experiencia'] != 0) {
                    $where = $referencia->getAdapter()
                        ->quoteInto('id = ?', $idRef);
                    $referencia->update($data, $where);
                }
            } else if ($data['id_experiencia'] == 0 && $data['nombre'] != '') {
                $valor = true;
            } else {
                if ($data['id_experiencia'] != 0 && $data['nombre'] != '' && $data['cargo']
                    != '' && $data['telefono'] != '')
                        $referencia->insert($data);
            }
        }

        return $valor;
        $postulante      = new Application_Model_Postulante();
        $where           = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $idReferenciaNew = $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')), $where);
        return $idReferenciaNew;
    }

    private function _eliminarReferenciaPostulante($idReferencia)
    {
        if ($idReferencia) {
            $referencia = new Application_Model_Referencia();
            $where      = array(
                'id=?' => $idReferencia);
            $r          = (bool) $referencia->delete($where);
            $postulante = new Application_Model_Postulante();
            $where      = $postulante->getAdapter()->quoteInto('id = ?',
                $this->idPostulante);
            $postulante->update(
                array(
                'ultima_actualizacion' => date('Y-m-d H:i:s'),
                'last_update_ludata' => date('Y-m-d H:i:s')
                ), $where);
        } else {
            $r = false;
        }
        return $r;
    }

    private function _eliminarLogroPostulante($idLogro)
    {
        if ($idLogro) {
            $logro      = new Application_Model_Logros();
            $where      = array(
                'id=?' => $idLogro);
            $r          = (bool) $logro->delete($where);
            $postulante = new Application_Model_Postulante();
            $where      = $postulante->getAdapter()->quoteInto('id = ?',
                $this->idPostulante);
            $postulante->update(
                array(
                'ultima_actualizacion' => date('Y-m-d H:i:s'),
                'last_update_ludata' => date('Y-m-d H:i:s')), $where
            );
//            $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
//                    $this->idPostulante, Application_Model_LogPostulante::LOGROS
//            );
        } else {
            $r = false;
        }
        return $r;
    }

    //Busquedas

    public function busquedaGeneralAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $param = $this->_getAllParams();

        if (!$this->_request->isPost()) {
            throw new Zend_Exception("Request debe ser POST");
        }

        if ($this->_hash->isValid($param['csrfhash'])) {
            $res = $this->_helper->autocomplete($this->_getAllParams());
            $this->_response->appendBody($res);
        } else {
            echo $this->_messageError;
        }
    }

    public function _actualizarPostulanteZendLucene($idPostulante)
    {
        //Actualizacion ZENDLUCENE ------------------------------------
        //POSTULANTE
        /*
          $objPostulante = new Application_Model_Postulante();

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

    public function anunciosSugeridosAction()
    {
        $this->view->menu_sel      = self::MENU_AVISOS;
        $this->view->menu_post_sel = self::MENU_POST_INICIO;
        $this->view->recortaraviso = $this->config->busqueda->recortaraviso;

        $params = $this->_getAllParams();

        $sess              = $this->getSession();
        $sess->micuentaUrl = $params;

        $verNotificacionAnuncios               = $this->config->profileMatch->postulante->notificaciones;
        $this->view->verNotificacionesAnuncios = $verNotificacionAnuncios;

        $objAnuncioMatch = new Application_Model_AnuncioPostulanteMatch();

        $result = $objAnuncioMatch->getAnunciosSugeridos(
            $this->auth['postulante']['id']
        );

        if ($verNotificacionAnuncios == 1 && count($result) != 0) {

            $this->view->listaAnunciosSugeridos = $result;
        } else {
            $this->_redirect('/mi-cuenta/');
        }
    }

    public function importarDatosAction()
    {
        $session  = new Zend_Session_Namespace('linkedin');
        $redirect = $this->_getParam('param1');

        $redirectUrlNamespace = new Zend_Session_Namespace('redirectUrl');
        if ($redirect != "") {
            $redirectUrlNamespace->redirectUrl = ('/mi-cuenta/'.$redirect);
        }


        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $config = $this->getConfig();

        $options = array(
            'version' => '1.0',
            'signatureMethod' => 'HMAC-SHA1',
            'localUrl' => $config->app->siteUrl.'/mi-cuenta/importar-datos',
            'callbackUrl' => $config->app->siteUrl.'/mi-cuenta/importar-datos',
            'requestTokenUrl' => $config->apis->linkedin->requestTokenUrl,
            'userAuthorizationUrl' =>
            $config->apis->linkedin->userAuthorizationUrl,
            'accessTokenUrl' =>
            $config->apis->linkedin->accessTokenUrl,
            'consumerKey' => $config->apis->linkedin->consumerKey,
            'consumerSecret' => $config->apis->linkedin->consumerSecret
        );

        $consumer = new Zend_Oauth_Consumer($options);
        if ($_REQUEST['oauth_problem'] != "") {
            $this->getMessenger()->error(
                'En estos momentos el servicio de importación no se encuentra
					 disponible.'
            );

            if (isset($redirectUrlNamespace->redirectUrl)) {
                $this->_redirect($redirectUrlNamespace->redirectUrl);
            } else {
                $this->_redirect('/mi-cuenta/mis-datos-personales');
            }
        }

        if (!isset($_SESSION['ACCESS_TOKEN'])) {
            if (!empty($_GET)) {
                $token                     = $consumer->getAccessToken(
                    $_GET, unserialize($_SESSION['REQUEST_TOKEN'])
                );
                $_SESSION ['ACCESS_TOKEN'] = serialize($token);
            } else {
                $token                     = $consumer->getRequestToken();
                $_SESSION['REQUEST_TOKEN'] = serialize($token);
                $consumer->redirect();
            }
        } else {
            $token                     = unserialize($_SESSION['ACCESS_TOKEN']);
            $_SESSION ['ACCESS_TOKEN'] = null;
        }
        $client = $token->getHttpClient($options);
        $client->setHeaders('Accept-Language', 'es-ES');

        $client->setUri($config->apis->linkedin->urlImportData);
        $client->setMethod(Zend_Http_Client::GET);
        $response             = $client->request();
        $content              = $response->getBody();
        $data                 = new Zend_Config_Xml($content);
        $session->linkedin    = $data;
        $session->showMessage = true;

        if (isset($data)) {
            $this->_redirect($redirectUrlNamespace->redirectUrl);
            unset($redirectUrlNamespace->redirectUrl);
        }
    }

    public function importarDatosActualizaAction()
    {

        $session = new Zend_Session_Namespace('linkedin');
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $config  = $this->getConfig();

        $options = array(
            'version' => '1.0',
            'signatureMethod' => 'HMAC-SHA1',
            'localUrl' => $config->app->siteUrl.'/mi-cuenta/importar-datos-actualiza',
            'callbackUrl' => $config->app->siteUrl.'/mi-cuenta/importar-datos-actualiza',
            'requestTokenUrl' => $config->apis->linkedin->requestTokenUrl,
            'userAuthorizationUrl' =>
            $config->apis->linkedin->userAuthorizationUrl,
            'accessTokenUrl' =>
            $config->apis->linkedin->accessTokenUrl,
            'consumerKey' => $config->apis->linkedin->consumerKey,
            'consumerSecret' => $config->apis->linkedin->consumerSecret
        );

        $consumer = new Zend_Oauth_Consumer($options);
        if ($_REQUEST['oauth_problem'] != "") {
            $this->getMessenger()->error(
                'En estos momentos el servicio de importación no se encuentra
					 disponible.'
            );
            $this->_redirect('/mi-cuenta/actualiza');
        }
        if (!isset($_SESSION['ACCESS_TOKEN'])) {
            if (!empty($_GET)) {
                $token                     = $consumer->getAccessToken(
                    $_GET, unserialize($_SESSION['REQUEST_TOKEN'])
                );
                $_SESSION ['ACCESS_TOKEN'] = serialize($token);
            } else {
                $token                     = $consumer->getRequestToken();
                $_SESSION['REQUEST_TOKEN'] = serialize($token);
                $consumer->redirect();
            }
        } else {
            $token                     = unserialize($_SESSION['ACCESS_TOKEN']);
            $_SESSION ['ACCESS_TOKEN'] = null;
        }
        $client = $token->getHttpClient($options);
        $client->setHeaders('Accept-Language', 'es-ES');

        $client->setUri($config->apis->linkedin->urlImportData);
        $client->setMethod(Zend_Http_Client::GET);
        $response          = $client->request();
        $content           = $response->getBody();
        $data              = new Zend_Config_Xml($content);
        $session->linkedin = $data;

        $this->_redirect('/mi-cuenta/actualiza');
    }

    private function _compararInstitucion($nombreInstitucion)
    {
        if ($nombreInstitucion == '') {
            return -1;
        }
        /*
         * Hace la comparacion de la institucion ingresada por LinkedIn,
         * con lo obtenido en la base de datos.
         *
          $institucion = new Application_Model_Institucion();
          $evalue = $institucion->compararInstitucion($nombreInstitucion);
          if ($evalue['comparacion'] > self::TOLERANCIA_LEVENSHTEIN_INST) {
          $evalue['id'] = -1;
          $evalue['nombre'] = $nombreInstitucion;
          } */
        $evalue['id']     = -1;
        $evalue['nombre'] = $nombreInstitucion;
        return $evalue;
    }

    /**
     * Compara el nombre de la carrera retornado por LinkedIn, para obtener
     * el valor mas cercano de la base de datos
     *
     * @param string $nombreCarrera
     * @return int
     */
    private function _compararCarrera($nombreCarrera)
    {
        if ($nombreCarrera == '') {
            return -1;
        }
        $carrera = new Application_Model_Carrera();
        $evalue  = $carrera->compararCarrera($nombreCarrera);
        if ((int) $evalue['comparacion'] >= self::TOLERANCIA_LEVENSHTEIN_CARRERA) {
            return -1;
        }
        return $evalue['id'];
    }

    private function _compararNivelEstudio($nivelEstudio)
    {
        if ($nivelEstudio == '') {
            return -1;
        }
        $estudio = new Application_Model_NivelEstudio();
        $evalue  = $estudio->compararNivelEstudio($nivelEstudio);
        if ($evalue['comparacion'] >= self::TOLERANCIA_LEVENSHTEIN_NIVEL_ESTUDIO) {
            return -1;
        }
        return $evalue['id'];
    }

    /**
     * Compara el nombre del idioma retornado por LinkedIn, para obtener el
     * valor mas cercano de la base de datos
     *
     * @param string $nombreIdioma
     * @return int
     */
    private function _compararIdioma($nombreIdioma)
    {
        $idioma       = new Application_Model_Idioma();
        $listaIdiomas = $idioma->getIdiomas();
        $min          = self::TOLERANCIA_LEVENSHTEIN_IDIOMA;
        $value        = 0;
        foreach ($listaIdiomas as $i => $d) {
            $evalue = levenshtein($d, $nombreIdioma);
            if ($evalue < $min) {
                $min   = $evalue;
                $value = $i;
            }
        }
        return $value;
    }

    /**
     * Lee los datos provenientes del LinkedIn relacionado a Estudios para
     * luego insertarlo en el formulario
     *
     * @param Zend_Config_Xml $linkedinData
     * @param App_Form_Manager $mngrEstudio
     * @return array
     */
    private function _linkedinEstudio(Zend_Config_Xml $linkedinData,
                                      App_Form_Manager $mngrEstudio)
    {
        if (isset($linkedinData->educations->total)) {
            $this->view->isEstudio = true;
            $formEstudio           = array();
            if ($linkedinData->educations->total > 1) {
                $i = 0;
                foreach (
                $linkedinData->educations->education as $educacion
                ) {
                    $values    = array();
                    $form      = $mngrEstudio->getForm($i);
                    $educacion = $educacion->toArray();
                    if (isset($educacion['start-date']['year'])) {
                        $values['inicio_ano'] = $educacion['start-date']['year'];
                    }
                    if (isset($educacion['end-date']['year'])) {
                        if ($educacion['end-date']['year'] <= date('Y')) {
                            $values['fin_ano'] = $educacion['end-date']['year'];
                        } elseif ($educacion['end-date']['year'] > date('Y')) {
                            $values['fin_ano']  = date('Y');
                            $values['en_curso'] = '1';
                        }
                    }
                    if (isset($educacion['degree'])) {
                        $values['id_nivel_estudio'] = $this->_compararNivelEstudio(
                            $educacion['degree']
                        );
                    }


                    if (isset($educacion['school-name'])) {
                        $evalueInstitucion        = $this->_compararInstitucion(
                            $educacion['school-name']
                        );
                        $values['id_institucion'] = $evalueInstitucion['id'];
                        $values['institucion']    = $evalueInstitucion['nombre'];
                    }
                    if (isset($educacion['field-of-study'])) {
                        $values['id_carrera'] = $this->_compararCarrera(
                            $educacion['field-of-study']
                        );
                    }
                    if (isset($values['id_institucion'])) {
                        $form->isValid($values);
                        $formEstudio[] = $form;
                        //$managerEstudio->getForm($i, $values);
                    }
                    //Zend_Debug::dump($values);
                    $i++;
                }
            } elseif ($linkedinData->educations->total == 0) {
                $form          = $mngrEstudio->getForm(0);
                $form->isValid(array());
                $formEstudio[] = $form;
            } else {
                $form      = $mngrEstudio->getForm(0);
                $educacion = $linkedinData->educations->education->toArray();
                if (isset($educacion['start-date']['year'])) {
                    $values['inicio_ano'] = $educacion['start-date']['year'];
                } else {
                    $values['inicio_ano'] = date('Y') - 1;
                }
                if (isset($educacion['end-date']['year'])) {
                    if ($educacion['end-date']['year'] <= date('Y')) {
                        $values['fin_ano'] = $educacion['end-date']['year'];
                    } elseif ($educacion['end-date']['year'] > date('Y')) {
                        $values['fin_ano']  = date('Y');
                        $values['en_curso'] = '1';
                    }
                } else {
                    $values['fin_ano'] = date('Y');
                }
                if (isset($educacion['degree'])) {
                    $values['id_nivel_estudio'] = $this->_compararNivelEstudio(
                        $educacion['degree']
                    );
                }
                $evalueInstitucion = -1;
                if (isset($educacion['school-name'])) {
                    $evalueInstitucion        = $this->_compararInstitucion(
                        $educacion['school-name']
                    );
                    $values['id_institucion'] = $evalueInstitucion['id'];
                    $values['institucion']    = $evalueInstitucion['nombre'];
                }
                if (isset($educacion['field-of-study'])) {
                    $values['id_carrera'] = $this->_compararCarrera($educacion['field-of-study']);
                }

                $form->isValid($values);
                $formEstudio[] = $form;
                //$formEstudio[] = $managerEstudio->getForm(0, $values);
            }
        } else {
            $formEstudio = array(
                $mngrEstudio->getForm(0));
        }
        return $formEstudio;
    }

    /**
     * Lee los datos provenientes del LinkedIn relacionado a Idioma para
     * luego insertarlo en el formulario
     *
     * @param Zend_Config_Xml $linkedinData
     * @param App_Form_Manager $managerIdioma
     * @return array
     */
    private function _linkedinIdioma(Zend_Config_Xml $linkedinData,
                                     App_Form_Manager $managerIdioma)
    {
        if (isset($linkedinData->languages->total)) {
            $this->view->isIdioma = true;
            $formIdioma           = array();
            $values               = array();
            if ($linkedinData->languages->total > 1) {
                $i = 0;
                foreach ($linkedinData->languages->language as $idioma) {
                    $form   = $managerIdioma->getForm($i);
                    $idioma = $idioma->toArray();
                    $evalue = $this->_compararIdioma($idioma['language']['name']);
                    if ($evalue != -1) {
                        $values['id_idioma']    = $evalue;
                        $values['nivel_idioma'] = '-1';
                        $form->setCabeceras($values['id_idioma'],
                            $values['nivel_idioma']);
                        $form->isValid($values);
                        $formIdioma[]           = $form;
                        $this->view->isIdioma   = $this->view->isIdioma && false;
                        //$managerIdioma->getForm($i, $values);
                        $i++;
                    } else {
                        $this->view->isIdioma = $this->view->isIdioma && true;
                    }
                }
            } elseif ($linkedinData->languages->total == 0) {
                $form         = $managerIdioma->getForm(0);
                $formIdioma[] = $form;
            } else {
                $form   = $managerIdioma->getForm(0);
                $idioma = $linkedinData->languages->language->toArray();
                if (isset($idioma['language']['name'])) {
                    $evalue = $this->_compararIdioma($idioma['language']['name']);
                    if ($evalue != -1) {
                        $values['id_idioma']    = $evalue;
                        $values['nivel_idioma'] = '-1';
                        $form->setCabeceras($values['id_idioma'],
                            $values['nivel_idioma']);
                        $form->isValid($values);
                        $this->view->isIdioma   = $this->view->isIdioma && false;
                    } else {
                        $form                 = $managerIdioma->getForm(0);
                        $this->view->isIdioma = $this->view->isIdioma && true;
                    }
                    $formIdioma[] = $form;
                }
            }
        } else {
            $form       = $managerIdioma->getForm(0);
            $formIdioma = array(
                $form);
        }
        return $formIdioma;
    }

    //Mantenimiento Experiencia
    private function _actualizarExperienciaPostulanteAjax(
    $managerCleanPost, $idPostulante
    )
    {
        //print_r($managerCleanPost);exit;
        $count            = 0;
        $idExperienciaNew = 0;

        $arrayID = array();
        foreach ($managerCleanPost as $form) {

            $data  = $form;
            $idExp = $data['id_Experiencia'];

            $experiencia = new Application_Model_Experiencia();
            if ($data['en_curso'] == 1) {
                $data['fin_mes'] = null;
                $data['fin_ano'] = null;
            }
            $data['otro_puesto'] = ucfirst(strtolower($data['otro_puesto']));
            unset($data['id_Experiencia']);
            unset($data['is_disabled']);

            if ($idExp) {
                $where = $experiencia->getAdapter()
                        ->quoteInto('id_postulante = ?', $idPostulante).
                        $experiencia->getAdapter()
                        ->quoteInto(' and id = ?', $idExp);
                //if ($data['otra_empresa'] != '' || $data['otro_puesto'] != '' || $data['otro_rubro'] !='')
                if ($data['id_nivel_puesto'] != 0)
                        $experiencia->update($data, $where);
                //$arrayID[] = $idExp;
            } else {
                $data['id_postulante'] = $idPostulante;

                if ($data['id_nivel_puesto'] != 0 && $data['id_area'] != 0 && $data['otra_empresa']
                    != '') {
                    $idExperienciaNew = $experiencia->insert($data);
                    //$arrayID[] = $idExperienciaNew;
                }
            }
        }
        $postulante = new Application_Model_Postulante();
        $where      = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')), $where
        );
        $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
            $idPostulante, Application_Model_LogPostulante::EXPERIENCIA
        );

        //$idPostulante
        $modelExperiencia = new Application_Model_Experiencia;
        $dataExperiencia  = $modelExperiencia->obtenerIdExperiencia($idPostulante);
        //print_r($dataExperiencia);exit;
        //$arrayID = array();
        foreach ($dataExperiencia as $key => $value)
            foreach ($value as $id)
                $arrayID[]        = $id;

        return $arrayID;
//        return $arrayID;
    }

    //Mantenimiento Estudio
    private function _actualizarEstudioPostulanteAjax(
    $managerCleanPost, $idPostulante
    )
    {
        $instituciones      = new Application_Model_Institucion();
        $carreras           = new Application_Model_Carrera();
        $listaInstituciones = $instituciones->getInstituciones();
        $listaCarreras      = $carreras->getCarreras();

        $idEstudioNew = 0;
        $count        = 0;
        $arrayID      = array();
        foreach ($managerCleanPost as $form) {

            $data    = $form;
            $idEst   = $data['id_estudio'];
            $estudio = new Application_Model_Estudio();
            if ($data['id_nivel_estudio'] == 1) {
                $data['id_carrera']       = null;
                $data['en_curso']         = 0;
                $data['otro_institucion'] = null;
                $data['pais_estudio']     = 0;
            } else {
                $data['en_curso']         = (bool) $data['en_curso'];
                $data['otro_institucion'] = $data['institucion'];
            }
            unset($data['id_estudio']);
            unset($data['institucion']);
            unset($data['is_disabled']);

            if ($data['id_institucion'] == 0 || $data['id_institucion'] == '') {
                $data['id_institucion'] = null;
            } else {
                if (array_key_exists($data['id_institucion'],
                        $listaInstituciones)) {
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
//                        $data['id_carrera'] = 15;
//                    } else {
//                        $data['id_tipo_carrera'] = null;
//                    }
//                }
//            }
            if (!isset($data['colegiatura_numero'])) {
                $data['colegiatura_numero'] = null;
            }
            if (array_key_exists($data['id_carrera'], $listaCarreras) && $data['id_carrera']
                != 15) {
                $data['otro_carrera'] = $listaCarreras[$data['id_carrera']];
            }
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
                $where     = $estudio->getAdapter()
                        ->quoteInto('id_postulante = ?', $idPostulante).
                        $estudio->getAdapter()
                        ->quoteInto(' and id = ?', $idEst);
                if ($data['id_nivel_estudio'] != 0)
                        $estudio->update($data, $where);
                $arrayID[] = $idEst;
            } else {
                $data['id_postulante'] = $idPostulante;
                if ($data['id_nivel_estudio'] != 0) {

                    if ($data['id_nivel_estudio'] == 1) {
                        $idEstudioNew = $estudio->insert($data);
                        $arrayID[]    = $idEstudioNew;
                    } else if ($data['id_nivel_estudio'] == 2 || $data['id_nivel_estudio']
                        == 3) {
                        $idEstudioNew = $estudio->insert($data);
                        $arrayID[]    = $idEstudioNew;
                    } else if ($data['id_nivel_estudio'] == 9 && $data['otro_estudio']
                        != '' && $data['otro_institucion'] != '') {
                        $idEstudioNew = $estudio->insert($data);
                        $arrayID[]    = $idEstudioNew;
                    } else {
                        if ($data['otro_institucion'] != '' && $data['otro_carrera']
                            != '' && $data['id_tipo_carrera'] != '') {
                            $idEstudioNew = $estudio->insert($data);
                            $arrayID[]    = $idEstudioNew;
                        }
                    }
                }
            }
        }

        $estudioModelo    = new Application_Model_Estudio;
        $estudioPrincipal = $estudioModelo->obtenerEstudiosMayorPesoPorPostulante($idPostulante);

        if (!empty($estudioPrincipal)) {
            $estudioModelo->actualizarEstudioPrincipal($idPostulante,
                $estudioPrincipal['id']);
        }

        $postulante = new Application_Model_Postulante();
        $where      = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')
            ), $where
        );
//        $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
//                $idPostulante, Application_Model_LogPostulante::ESTUDIOS
//        );

        return $arrayID;
    }

    //Mantenimiento Idioma
    private function _actualizarIdiomaPostulanteAjax(
    $managerCleanPost, $idPostulante
    )
    {
        $idIdiomaNew = 0;
        $count       = 0;
        $arrayID     = array();
        foreach ($managerCleanPost as $form) {

            $data  = $form;
            $idEst = $data['id_dominioIdioma'];
            unset($data['cabecera_idioma']);
            unset($data['cabecera_nivel']);

            $idioma = new Application_Model_DominioIdioma();
            unset($data['id_dominioIdioma']);
            if ($data['nivel_idioma'] != 0 && $data['id_idioma'] != 0) {
                if ($idEst) {
                    $data['nivel_lee']     = $data['nivel_idioma'];
                    $data['nivel_escribe'] = $data['nivel_idioma'];
                    $data['nivel_hablar']  = $data['nivel_idioma'];
                    unset($data['nivel_idioma']);

                    $where     = $idioma->getAdapter()
                            ->quoteInto('id_postulante = ?', $idPostulante).
                            $idioma->getAdapter()
                            ->quoteInto(' and id = ?', $idEst);
                    $idioma->update($data, $where);
                    $arrayID[] = $idEst;
                } else {
                    $data['id_postulante'] = $idPostulante;
                    $data['nivel_lee']     = $data['nivel_idioma'];
                    $data['nivel_escribe'] = $data['nivel_idioma'];
                    $data['nivel_hablar']  = $data['nivel_idioma'];
                    unset($data['nivel_idioma']);

                    $idIdiomaNew = $idioma->insert($data);
                    $arrayID[]   = $idIdiomaNew;
                }
            }
        }
        $postulante = new Application_Model_Postulante();
        $where      = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')), $where
        );
        $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
            $idPostulante, Application_Model_LogPostulante::IDIOMAS
        );

        return $arrayID;
    }

    //Mantenimiento Programa Computo
    private function _actualizarProgramaComputoPostulanteAjax(
    $managerCleanPost, $idPostulante
    )
    {
        $idProgramaNew = 0;
        $count         = 0;
        $arrayID       = array();
        foreach ($managerCleanPost as $form) {

            $data  = $form;
            $idEst = $data['id_dominioComputo'];

            $programa = new Application_Model_DominioProgramaComputo();
            unset($data['id_dominioComputo']);
            unset($data['cabecera_programa']);
            unset($data['cabecera_nivel']);
            unset($data['is_disabled']);
            unset($data['nombre']);

            if ($data['nivel'] != 0 && $data['id_programa_computo'] != 0) {
                if ($idEst) {

                    $where     = $programa->getAdapter()
                            ->quoteInto('id_postulante = ?', $idPostulante).
                            $programa->getAdapter()
                            ->quoteInto(' and id = ?', $idEst);
                    $programa->update($data, $where);
                    $arrayID[] = $idEst;
                } else {
                    $data['id_postulante'] = $idPostulante;
                    $idProgramaNew         = $programa->insert($data);
                    $arrayID[]             = $idProgramaNew;
                }
            }
        }
        $postulante = new Application_Model_Postulante();
        $where      = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')), $where
        );
        $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
            $idPostulante, Application_Model_LogPostulante::PROGRAMAS
        );

        return $arrayID;
    }

    //Mantenimiento Referencia
    private function _actualizarReferenciaPostulanteAjax(
    $managerCleanPost, $idPostulante
    )
    {

        $valor           = false;
        $idReferenciaNew = 0;
        $count           = 0;
        $arrayID         = array();
        foreach ($managerCleanPost as $form) {

            $data                   = $form;
            $data['id_experiencia'] = $data['listaexperiencia'];
            $idRef                  = $data['id_referencia'];

            $referencia = new Application_Model_Referencia();
            unset($data['id_referencia']);
            unset($data['listaexperiencia']);
            if ($idRef) {
                if ($data['id_experiencia'] != -1) {
                    $where     = $referencia->getAdapter()
                        ->quoteInto('id = ?', $idRef);
                    $referencia->update($data, $where);
                    $arrayID[] = $idRef;
                }
            } else if ($data['id_experiencia'] == -1 && $data['nombre'] != '') {
                $valor = true;
            } else {
                if ($data['id_experiencia'] != -1 && $data['nombre'] != '' && $data['cargo']
                    != '' && $data['telefono'] != '')
                        $newReferencia = $referencia->insert($data);
                $arrayID[]     = $newReferencia;
            }
        }

        //return $valor;
        $postulante      = new Application_Model_Postulante();
        $where           = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $idReferenciaNew = $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')), $where);

        return $arrayID;
    }

    public function getFacebookAction()
    {
        $this->_redirect(SITE_URL.'/mi-cuenta');
    }

    public function updateCvAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $sessionUpdateCV           = new Zend_Session_Namespace('updateCV');
        $sessionUpdateCV->urlAviso = $_SERVER['HTTP_REFERER'];

        $this->_redirect('mi-cuenta/mis-datos-personales');
    }

    private function _postular($urlId)
    {

        $a          = new Application_Model_AnuncioWeb();
        $dataAviso  = $a->getAvisoIdByUrl($urlId);
        $aviso      = $a->getAvisoInfoficha($dataAviso['id']);
        $usuario    = $this->auth['usuario'];
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
                array(
                'ultima_actualizacion' => date('Y-m-d H:i:s')),
                $this->getAdapter()->quoteInto("id = ?", $idPostulante)
            );
        }

        $funciones            = $this->_helper->getHelper("RegistrosExtra");
        $match                = $funciones->PorcentajeCoincidencia($avisoId,
            $postulante['id']);
        $nivelestudioscarrera = $funciones->MejorNivelEstudiosCarrera($postulante['id']);

        $postulacion        = new Application_Model_Postulacion();
        $anuncioWebModelo   = new Application_Model_AnuncioWeb;
        $referenciadoModelo = new Application_Model_Referenciado;
        $historico          = new Application_Model_HistoricoPs();
        $postulanteId       = $postulante['id'];
        $email              = $usuario->email;

        $postulacionValidador = new
            App_Service_Validate_Postulation_Postulant(
            $avisoId, $postulanteId);

        if (!$postulacionValidador->isNull()) {
            if ($postulacionValidador->isInvited()) {

                $postulacionDatos = $postulacionValidador->getData();
                $idPostulacion    = $postulacionDatos['id'];


                $postulacion->activar($idPostulacion);
                $referenciadoModelo->postulo($email, $avisoId);
                $historico->registrar($idPostulacion,
                    Application_Model_HistoricoPs::EVENTO_POSTULACION,
                    Application_Model_HistoricoPs::ESTADO_POSTULACION);
            }
        } else {
            $data = array(
                'id_postulante' => $postulante['id'],
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
                $avisoId, array(
                'id_empresa'));

            $empresaId = $anuncioWeb['id_empresa'];

            $postulanteValidador = new App_Service_Validate_Postulant;
            $postulanteValidador->setData($postulante);

            if ($postulanteValidador->isBlocked($empresaId)) {
                $data['activo'] = Application_Model_Postulacion::POSTULACION_BLOQUEADA;
            }

            if (App_Service_Validate_Postulant::hasReferred(
                    $email, $avisoId)) {
                $data['referenciado'] = Application_Model_Postulacion::ES_REFERENCIADO;

                $referenciadoModelo->postulo($email, $avisoId);
            }

            $idPostulacion = $postulacion->insert($data);
        }

        $this->_helper->Aviso->actualizarPostulantes($avisoId);
        $this->_helper->Aviso->actualizarInvitaciones($avisoId);
        $this->_helper->Aviso->actualizarNuevasPostulaciones($avisoId);

        $respuesta = new Application_Model_Respuesta();

        //Inicio graba Respuesta
        $mensaje    = new Application_Model_Mensaje();
        $anuncioWeb = new Application_Model_AnuncioWeb();

        $ususarioEmp         = new Application_Model_Usuario;
        $emailUsuarioEmpresa = $ususarioEmp->correoUsuarioxAnuncio($avisoId,
            $creado);
        $tieneCorreoOp       = $anuncioWeb->avisoTieneCorreoOp($avisoId);
        $pos                 = new Application_Model_Postulante;
        $nivelMaxEstudio     = $pos->nivelMaximoEstudio($postulante['id_usuario']);
        $pregunta            = new Application_Model_Pregunta();

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
                            'apellidos' => $postulante['apellidos'].' '.
                            $postulante['apellido_paterno']." ".$postulante['apellido_materno'],
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
            $this->log->log($ex->getMessage().'. '.$ex->getTraceAsString(),
                Zend_Log::ERR);
        }
        $aw = $anuncioWeb->fetchRow($anuncioWeb->getAdapter()->quoteInto('id = ?',
                $avisoId));
        //Fin

        $dataRespuesta = array();
        foreach ($dataPost as $key => $value) {
            if (substr_count($key, 'pregunta') > 0) {
                $dataRespuesta['id_pregunta']    = str_replace('pregunta_', '',
                    $key);
                $dataRespuesta['id_postulacion'] = $idPostulacion;
                $dataRespuesta['respuesta']      = $value;
                $respuesta->insert($dataRespuesta);
                $idPre                           = $dataRespuesta['id_pregunta'];
                //Inicio Grabar Pregunta
                $pregu                           = $pregunta->find($idPre)->toArray();
                $data                            = array(
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
                $idPregunta                      = $mensaje->insert($data);
                $data                            = array(
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
        $data     = array(
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
        $idPostu  = $postulante['id'];
        @$this->_cache->remove('Postulacion_getIdAvisosPostulaciones_'.$postulante['id']);


        @$modelAPM->update(
                array(
                'estado' => Application_Model_AnuncioPostulanteMatch::ESTADO_POSTULA,
                'fh_postulacion' => date('Y-m-d H:i:s')),
                $this->getAdapter()->quoteInto("id_anuncio_web = $avisoId AND id_postulante = $idPostu",
                    null)
        );
        @$this->_cache->remove('Postulacion_getProgramasBuscadorEmpresa_'.$avisoId);
        @$this->_cache->remove('Postulacion_getIdiomasBuscadorEmpresa_'.$avisoId);
    }

    public function actualizaAction()
    {

        if (Zend_Auth::getInstance()->hasIdentity() != true) {
            $this->_redirect('/registro-postulante/paso1');
        }
        $idPostulante       = $this->idPostulante = $this->auth['postulante']['id'];

        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array(
            'id' => 'myAccount')
        );

        $this->view->headTitle()->set(
            'Actualización de datos del Postulante'
        );
        $this->view->headMeta()->appendName(
            "Description",
            "Actualiza tu Perfil profesional, para postular a las ofertas en ".
            "aquiempleos.com.  Los Clasificados de Empleos de La Prensa."
        );

        $this->view->headLink()->appendStylesheet($this->view->S('/css/plugins/jquery-ui-1.9.2.custom.min.css'));
        $this->view->return = '';

        $session             = new Zend_Session_Namespace('linkedin');
        $baseFormExperiencia = new Application_Form_Paso2Experiencia(true);
        $managerExperiencia  = new App_Form_Manager($baseFormExperiencia,
            'managerExperiencia');

        $baseFormEstudio = new Application_Form_Paso2Estudio(true);
        $managerEstudio  = new App_Form_Manager($baseFormEstudio,
            'managerEstudio');

        $baseFormOtroEstudio = new Application_Form_Paso2OtroEstudio(true);
        $managerOtroEstudio  = new App_Form_Manager($baseFormOtroEstudio,
            'managerOtroEstudio');

        $baseFormIdioma = new Application_Form_Paso2Idioma(true);
        $managerIdioma  = new App_Form_Manager($baseFormIdioma, 'managerIdioma');

        $baseFormPrograma = new Application_Form_Paso2Programa(true);
        $managerPrograma  = new App_Form_Manager($baseFormPrograma,
            'managerPrograma');

        $form        = new Application_Form_Paso2();
        $formAlertas = new Application_Form_MisAlertas();

        $formsExperiencia = $formExperiencia  = array();
        $formsEstudio     = $formEstudio      = array();
        $formsOtroEstudio = $formOtroEstudio  = array();
        $formsIdioma      = $formIdioma       = array();
        $formsPrograma    = $formPrograma     = array();
        $indexExp         = 0;
        $indexEst         = 0;
        $indexIdi         = 0;
        $indexPro         = 0;
        $indexOtroEst     = 0;

        // Listar experiencias
        $experiencia = new Application_Model_Experiencia();

        $arrayExperiencias = $experiencia->getExperiencias($idPostulante);
        if (count($arrayExperiencias) != 0) {
            $puestos        = Application_Model_EmpresaPuesto::getPuestosIds();
            $nivelesPuestos = Application_Model_EmpresaNivelPuesto::getNivelesPuestosIds();
            $areas          = Application_Model_EmpresaArea::getAreasIds();
            foreach ($arrayExperiencias as $experiencia) {
                if ($experiencia['en_curso'] == '1') {
                    $experiencia['fin_mes'] = date('n');
                }
                if ($experiencia['en_curso'] == '1') {
                    $experiencia['fin_ano'] = date('Y');
                }
                if (strlen($experiencia['comentarios']) > 140) {
                    $experiencia['comentarios'] = substr($experiencia['comentarios'],
                        0, 140);
                }

                if (empty($experiencia['id_puesto']) || $experiencia['id_puesto']
                    == '') {
                    $experiencia['id_puesto'] = Application_Model_EmpresaPuesto::OTROS_PUESTO_ID;
                }

                $add = TRUE;
                if (!array_key_exists($experiencia['id_nivel_puesto'],
                        $nivelesPuestos) ||
                    !array_key_exists($experiencia['id_area'], $areas) ||
                    !array_key_exists($experiencia['id_puesto'], $puestos)) {
                    $experiencia['is_disabled'] = 1;
                    $experiencia['otro_puesto'] = $experiencia['nombre_puesto'];
                }

                if ($add) {
//                    if ($experiencia['otro_puesto'] != $puestos[$experiencia['id_puesto']]) {
//                        $experiencia['id_puesto'] = Application_Model_EmpresaPuesto::OTROS_PUESTO_ID;
//                    }

                    $formExp                   = $managerExperiencia->getForm($indexExp++,
                        $experiencia);
                    $formExp->setHiddenId($experiencia['id_Experiencia']);
                    $formsExperiencia[]        = $formExp;
                    $this->view->isExperiencia = true;
                    $this->view->isLinkedin    = true;
                }
            }
            $formsExperiencia[] = $managerExperiencia->getForm($indexExp++);
        } else {
            $formsExperiencia[] = $managerExperiencia->getForm(0);
        }

        // Listar estudios
        $estudio = new Application_Model_Estudio();

        $arrayEstudios = $estudio->getEstudios($idPostulante);
        if (count($arrayEstudios) != 0) {
            $tipoCarreras = Application_Model_TipoCarrera::getTiposCarrerasIds();
            $carreras     = Application_Model_Carrera::getCarrerasIds();

            foreach ($arrayEstudios as $estudio) {
                if ($estudio['en_curso'] == '1') {
                    $estudio['fin_mes'] = date('n');
                }
                if ($estudio['en_curso'] == '1') {
                    $estudio['fin_ano'] = date('Y');
                }
                $add = TRUE;
                if ($estudio['id_nivel_estudio'] != 1 &&
                    $estudio['id_nivel_estudio'] != 2 &&
                    $estudio['id_nivel_estudio'] != 3 &&
                    $estudio['id_nivel_estudio'] != 9) {
                    if (empty($estudio['id_tipo_carrera'])) {
                        $estudio['id_tipo_carrera'] = Application_Model_TipoCarrera::OTROS_TIPO_CARRERA;
                    }
                    if (empty($estudio['id_carrera']) || $estudio['id_carrera'] == "") {
                        $estudio['id_carrera'] = Application_Model_Carrera::OTRO_CARRERA;
                        if (!empty($estudio['otro_carrera'])) {
                            $carreras_flip         = array_flip($carreras);
                            $estudio['id_carrera'] = ($carreras_flip[trim($estudio['otro_carrera'])])
                                    ? $carreras_flip[trim($estudio['otro_carrera'])]
                                    : 15;
                        }
                    }
                    if ((!array_key_exists($estudio['id_tipo_carrera'],
                            $tipoCarreras) ||
                        !array_key_exists($estudio['id_carrera'], $carreras))) {
                        $add = FALSE;
                    }
                }
                if ($add) {
                    $estudio['institucion'] = $estudio['nombre'];
                    unset($estudio['nombre']);
                    $formEst                = $managerEstudio->getForm($indexEst++,
                        $estudio);
                    $formEst->setHiddenId($estudio['id_estudio']);
                    $formEst->setElementNivelEstudio($estudio['id_nivel_estudio']);
                    $formEst->setElementCarrera($estudio['id_tipo_carrera']);
                    $formEst->getElement('id_nivel_estudio_tipo')->setValue($estudio['id_nivel_estudio_tipo']);
                    $formsEstudio[]         = $formEst;
                    $this->view->isLinkedin = true;
                    $this->view->isEstudio  = true;
                }
            }
            $formsEstudio[] = $managerEstudio->getForm($indexEst++);
        } else {
            $formsEstudio[] = $managerEstudio->getForm(0);
        }

        // Listar otros estudios
        $otroEstudio = new Application_Model_Estudio();

        $arrayOtroEstudios = $otroEstudio->getOtrosEstudios($idPostulante);
        if (count($arrayOtroEstudios) != 0) {
            $tipoCarreras = Application_Model_TipoCarrera::getTiposCarrerasIds();
            $carreras     = Application_Model_Carrera::getCarrerasIds();

            foreach ($arrayOtroEstudios as $otroEstudio) {
                if ($otroEstudio['en_curso'] == '1') {
                    $otroEstudio['fin_mes'] = date('n');
                }
                if ($otroEstudio['en_curso'] == '1') {
                    $otroEstudio['fin_ano'] = date('Y');
                }
                $add = TRUE;

                if ($otroEstudio['id_nivel_estudio'] == 9) {
                    if (empty($otroEstudio['id_tipo_carrera'])) {
                        $otroEstudio['id_tipo_carrera'] = Application_Model_TipoCarrera::OTROS_TIPO_CARRERA;
                    }
                    if (empty($otroEstudio['id_carrera']) || $otroEstudio['id_carrera']
                        == "") {
                        $otroEstudio['id_carrera'] = Application_Model_Carrera::OTRO_CARRERA;
                        if (!empty($otroEstudio['otro_carrera'])) {
                            $carreras_flip             = array_flip($carreras);
                            $otroEstudio['id_carrera'] = ($carreras_flip[trim($otroEstudio['otro_carrera'])])
                                    ? $carreras_flip[trim($otroEstudio['otro_carrera'])]
                                    : 15;
                        }
                    }
                    if ((!array_key_exists($otroEstudio['id_tipo_carrera'],
                            $tipoCarreras) ||
                        !array_key_exists($otroEstudio['id_carrera'], $carreras))) {
                        $add = FALSE;
                    }
                }

                //if ($add) {
                $otroEstudio['institucion'] = $otroEstudio['nombre'];
                unset($otroEstudio['nombre']);
                $formOtrosEst               = $managerOtroEstudio->getForm($indexOtroEst++,
                    $otroEstudio);
                $formOtrosEst->setHiddenId($otroEstudio['id_estudio']);
                $formsOtroEstudio[]         = $formOtrosEst;
                $this->view->isOtroEstudio  = false;
                //}
            }
            $formsOtroEstudio[] = $managerOtroEstudio->getForm($indexOtroEst++);
        } else {
            $formsOtroEstudio[] = $managerOtroEstudio->getForm(0);
        }

        // Listar idiomas
        $idioma = new Application_Model_DominioIdioma();

        $arrayIdiomas = $idioma->getDominioIdioma($idPostulante);
        if (count($arrayIdiomas) != 0) {
            foreach ($arrayIdiomas as $idioma) {
                $formIdi                = $managerIdioma->getForm($indexIdi++,
                    $idioma);
                $formIdi->setHiddenId($idioma['id_dominioIdioma']);
                $formIdi->setCabeceras($idioma['id_idioma'],
                    $idioma['nivel_idioma']);
                $formIdi->addValidatorsIdioma();
                $formsIdioma[]          = $formIdi;
                $this->view->isLinkedin = true;
                $this->view->isIdioma   = false;
            }
            $formsIdioma[] = $managerIdioma->getForm($indexIdi++);
        } else {
            $formsIdioma[] = $managerIdioma->getForm(0);
        }

        // Listar programas
        $programa = new Application_Model_DominioProgramaComputo();

        $arrayProgramas = $programa->getDominioProgramaComputo($idPostulante);

        if (count($arrayProgramas) != 0) {
            $arrayProgramasIds = Application_Model_EmpresaProgramaComputo::getEmpresaProgramasComputoIds();
            foreach ($arrayProgramas as $programa) {
                $add = TRUE;
                if (!array_key_exists($programa['id_programa_computo'],
                        $arrayProgramasIds)) {
                    $add = FALSE;
                }
                if ($add) {
                    $formPro                = $managerPrograma->getForm($indexPro++,
                        $programa);
                    $formPro->setHiddenId($programa['id_dominioComputo']);
                    $formPro->setCabeceras($programa['id_programa_computo'],
                        $programa['nivel']);
                    $formPro->addValidatorsPrograma();
                    $formsPrograma[]        = $formPro;
                    $this->view->isPrograma = false;
                }
            }
            $formsPrograma[] = $managerPrograma->getForm($indexPro++);
        } else {
            $formsPrograma[] = $managerPrograma->getForm(0);
        }

        $postulante      = new Application_Model_Postulante();
        $getCV           = $postulante->getCv($idPostulante);
        $sessionUpdateCV = new Zend_Session_Namespace('updateCV');

        if ($this->getRequest()->isPost()) {
            $postData           = $this->_getAllParams();
            $this->view->isEdit = true;

            $this->view->isExperiencia = true;
            $this->view->isEstudio     = true;
            $this->view->isOtroEstudio = false;
            $this->view->isIdioma      = false;
            $this->view->isPrograma    = false;
            $managerEstudio->isValid($postData);
            $managerExperiencia->isValid($postData);
            $validAlertas              = $formAlertas->isValid($postData);
            $managerIdioma->isValid($postData);
            $managerPrograma->isValid($postData);
            $managerOtroEstudio->isValid($postData);
            foreach ($managerIdioma->getForms() as $formIdi) {
                // @codingStandardsIgnoreStart
                if ($formIdi->nivel_idioma->hasErrors()) {
                    $this->view->isIdioma = false;
                }
                // @codingStandardsIgnoreEnd
            }
            foreach ($managerPrograma->getForms() as $formProg) {
                if ($formProg->nivel->hasErrors()) {
                    $this->view->isPrograma = false;
                }
            }
            foreach ($managerOtroEstudio->getForms() as $formOE) {
                if ($formOE->id_nivel_estudio_tipo->hasErrors() ||
                    $formOE->otro_estudio->hasErrors() ||
                    $formOE->institucion->hasErrors()
                ) {
                    $this->view->isOtroEstudio = false;
                }
            }

            if ($managerExperiencia->isValid($postData) &&
                $managerEstudio->isValid($postData) &&
                $managerOtroEstudio->isValid($postData) &&
                $managerIdioma->isValid($postData) &&
                $managerPrograma->isValid($postData) &&
                $form->isValid($postData) && $validAlertas) {

                $this->_actualizarExperienciaPostulante($managerExperiencia->getCleanPost(),
                    $idPostulante);
                $this->_actualizarEstudioPostulante($managerEstudio->getCleanPost(),
                    $idPostulante);
                $this->_actualizarOtroEstudioPostulante($managerOtroEstudio->getCleanPost(),
                    $idPostulante);
                $this->_actualizarIdiomaPostulante($managerIdioma->getCleanPost(),
                    $idPostulante);
                $this->_actualizarProgramaComputoPostulante($managerPrograma->getCleanPost(),
                    $idPostulante);

                //$this->_guardarCv($form, $idPostulante, $this->auth);
                $this->_guardarOpcionesPostulante($formAlertas->getValues(),
                    $idPostulante);

                //Actualizamos todas las postulacion (Match)
                $helper = $this->_helper->getHelper("RegistrosExtra");
                $helper->ActualizarPostulacion($idPostulante);

                $mensajeAviso     = "Gracias por actualizar tu perfil profesional. Ahora tendrás mejores oportunidades para que más empresas puedan contactar contigo.";
                $mensajedashboard = $this->config->recomendar->mensaje;


                if (isset($sessionUpdateCV->urlAviso)) {
                    //unset($sessionUpdateCV->urlAviso);
                    if ($sessionUpdateCV->tipo == 'aviso') {
                        $this->getMessenger()->success($mensajeAviso);
                        unset($sessionUpdateCV->tipo);
                        $sesionMsg = new Zend_Session_Namespace("msg_welcome");
                        unset($sesionMsg->welcome);
                    } else if ($sessionUpdateCV->tipo == 'publicidad') {
                        $this->getMessenger()->success($mensajedashboard);
                        unset($sessionUpdateCV->tipo);
                        $sesionMsg = new Zend_Session_Namespace("msg_welcome");
                        unset($sesionMsg->welcome);
                    } else if ($sessionUpdateCV->tipo == 'perfil-destacado') {
                        $this->_redirect($sessionUpdateCV->rutaperfildestacado);
                    }
                    $this->_redirect('/mi-cuenta/listo');
                } else {
                    $this->getMessenger()->success('Sus datos fueron actualizados satisfactoriamente.');
                    $this->_redirect('/mi-cuenta');
                }


                if (isset($postData['return']) && !empty($postData['return'])) {
                    $this->view->return = $postData['return'];
                }
            } else {
                $arrExp           = explode(',', $postData['managerExperiencia']);
                foreach ($arrExp as $index)
                    $managerExperiencia->removeForm($index);
                $arrExp           = explode(',', $postData['managerEstudio']);
                foreach ($arrExp as $index)
                    $managerEstudio->removeForm($index);
                $arrExp           = explode(',', $postData['managerIdioma']);
                foreach ($arrExp as $index)
                    $managerIdioma->removeForm($index);
                $arrExp           = explode(',', $postData['managerPrograma']);
                foreach ($arrExp as $index)
                    $managerPrograma->removeForm($index);
                $formsExperiencia = $managerExperiencia->getForms();
                $carrera          = new Application_Model_Carrera();
                $nivelEstudio     = new Application_Model_NivelEstudio();
                $formuEstudio     = $managerEstudio->getForms();
                $formsEstudio     = array();
                foreach ($formuEstudio as $fe) {
                    $id_tipo_carrera = $fe->getElement('id_tipo_carrera')->getValue();
                    if (!empty($id_tipo_carrera)) {
                        //$data = $carrera->filtrarCarrera($id_tipo_carrera);
                        //$fe->getElement('id_carrera')->clearMultiOptions()->addMultiOption('0', 'Selecciona carrera')->addMultiOptions($data);
                        $fe->setElementCarrera($id_tipo_carrera);
                    }
                    $id_nivel_estudio = $fe->getElement('id_nivel_estudio')->getValue();
                    if (!empty($id_nivel_estudio)) {
                        //$data = $nivelEstudio->getSubNiveles($id_nivel_estudio);
                        //$fe->getElement('id_nivel_estudio_tipo')->clearMultiOptions()->addMultiOption('0', 'Selecciona un tipo')->addMultiOptions($data);
                        $fe->setElementNivelEstudio($id_nivel_estudio);
                    }
                    $formsEstudio[] = $fe;
                }
                $formsOtroEstudio = $managerOtroEstudio->getForms();
                $formsIdioma      = $managerIdioma->getForms();
                $formsPrograma    = $managerPrograma->getForms();
            }
        } else if (isset($session->linkedin)) {
            $this->view->isLinkedin = true;
            $this->getMessenger()->success(
                "Se logró importar tus datos de linkedin. Por favor, ingresa o
					 selecciona los datos que no hayan coincidido exactamente con
					 los de AquiEmpleos."
            );
            $linkedinData           = $session->linkedin;
            $formsExperiencia       = $this->_linkedinExperiencia($linkedinData,
                $managerExperiencia);
            $formsEstudio           = $this->_linkedinEstudio($linkedinData,
                $managerEstudio);
            $formsOtroEstudio       = array(
                $managerOtroEstudio->getForm(0));
            $formsIdioma            = $this->_linkedinIdioma($linkedinData,
                $managerIdioma);
            $formsPrograma          = array(
                $managerPrograma->getForm(0));

            $formsExperiencia[] = $managerExperiencia->getForm(count($formsExperiencia));
            $formsEstudio[]     = $managerEstudio->getForm(count($formsEstudio));
            unset($session->linkedin);
        }

        $this->view->formExperiencia = $formsExperiencia;
        $this->view->assign('managerExperiencia', $managerExperiencia);

        $this->view->formEstudio = $formsEstudio;
        $this->view->assign('managerEstudio', $managerEstudio);

        $this->view->formOtroEstudio = $formsOtroEstudio;
        $this->view->assign('managerOtroEstudio', $managerOtroEstudio);

        $this->view->formIdioma = $formsIdioma;
        $this->view->assign('managerIdioma', $managerIdioma);

        $this->view->formPrograma = $formsPrograma;
        $this->view->assign('managerPrograma', $managerPrograma);

        $this->view->form = $form;

        $this->view->formAlertas = $formAlertas;

        $this->view->cv      = 0;
        $this->view->path_cv = '';
        $this->view->moneda  = $this->_config->app->moneda;


        if (isset($getCV['path_cv']) && strlen(trim($getCV['path_cv'])) > 0) {

            $this->view->cv      = 1;
            $this->view->path_cv = $getCV['path_cv'];
        }
    }

    private function _guardarOpcionesPostulante(array $dataPost, $idPostulante)
    {
        $postulante = new Application_Model_Postulante();
        $postulante->update(
            array(
            'prefs_emailing_avisos' =>
            $dataPost['prefs_emailing_avisos'],
            'prefs_emailing_info' =>
            $dataPost['prefs_emailing_info']
            ), $postulante->getAdapter()->quoteInto('id = ?', $idPostulante)
        );
    }

    private function _guardarCv(Application_Form_Paso2 $form, $idPostulante,
                                $auth)
    {
        $utilfile   = $this->_helper->getHelper('UtilFiles');
        $pathCv     = $utilfile->_renameFile($form, 'pathCv', $auth);
        $postulante = new Application_Model_Postulante();
        if ($pathCv != "") {
            $postulante->update(
                array(
                'path_cv' =>
                $pathCv
                ), $postulante->getAdapter()->quoteInto('id = ?', $idPostulante)
            );
        }
    }

    public function misOtrosEstudiosOldAction()
    {

        //$session = $this->getSession();
        $session                   = new Zend_Session_Namespace('linkedin');
        $this->view->menu_sel_side = self::MENU_POST_SIDE_OTROSESTUDIOS;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_smel     = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;
        $this->view->action        = 'mis-datos-personales';
        $this->view->showImport    = false;

        $idPostulante = $this->idPostulante;

        $baseFormEstudio                                              = new Application_Form_Paso2OtroEstudio(true);
        $nivel                                                        = new Application_Model_NivelEstudio;
        $listaNivelesTipo                                             = $nivel->getSubNiveles(9);
        $ubigeo                                                       = new Application_Model_Ubigeo();
        $paises                                                       = $ubigeo->getPaises();
        $baseFormEstudio->getElement('id_nivel_estudio_tipo')->addValidator(new Zend_Validate_InArray(array_keys($listaNivelesTipo)));
        $baseFormEstudio->getElement('id_nivel_estudio_tipo')->errMsg = "Debe ingresar un tipo de nivel de estudios";
        $baseFormEstudio->getElement('otro_estudio')->setRequired();
        $baseFormEstudio->getElement('otro_estudio')->errMsg          = "Ingresa los datos de tus otros estudios";
        $baseFormEstudio->getElement('institucion')->setRequired();
        $baseFormEstudio->getElement('institucion')->errMsg           = "Debe ingresar institucion de estudios";
        $baseFormEstudio->getElement('pais_estudio')->addValidator(new Zend_Validate_InArray(array_keys($paises)));
        $baseFormEstudio->getElement('pais_estudio')->errMsg          = "Campo Requerido";
        $managerOtrosEstudio                                          = new App_Form_Manager($baseFormEstudio,
            'managerOtrosEstudio', true);

        $ok            = true;
        $formsEstudio  = array();
        $index         = 0;
        $estudio       = new Application_Model_Estudio();
        $arrayEstudios = $estudio->getOtrosEstudios($idPostulante);
        if (isset($session->linkedin)) {
            $ok                     = false;
            $this->view->showImport = true;
            $this->view->isLinkedin = true;
            $this->getMessenger()->success(
                "Se logró importar tus datos de linkedin. Por favor, ingresa o
					 selecciona los datos que no hayan coincidido exactamente con
					 los de AquiEmpleos."
            );
            $linkedinData           = $session->linkedin;
            $formsEstudio           = $this->_linkedinEstudio($linkedinData,
                $managerOtrosEstudio);
            unset($session->linkedin);
            $index                  = count($formsEstudio);
        }
        if (!$this->getRequest()->isPost()) {
            if (count($arrayEstudios) != 0) {
                $tipoCarreras = Application_Model_TipoCarrera::getTiposCarrerasIds();
                $carreras     = Application_Model_Carrera::getCarrerasIds();
                foreach ($arrayEstudios as $estudio) {
                    if ($estudio['en_curso'] == '1') {
                        $estudio['fin_mes'] = date('n');
                    }
                    if ($estudio['en_curso'] == '1') {
                        $estudio['fin_ano'] = date('Y');
                    }
                    if (( $estudio['id_nivel_estudio'] == 9)) {
                        if (empty($estudio['id_tipo_carrera'])) {
                            $estudio['id_tipo_carrera'] = Application_Model_TipoCarrera::OTROS_TIPO_CARRERA;
                        }
                        if (empty($estudio['id_carrera']) || $estudio['id_carrera']
                            == "") {
                            $estudio['id_carrera'] = Application_Model_Carrera::OTRO_CARRERA;
                            if (!empty($estudio['otro_carrera'])) {
                                $carreras_flip         = array_flip($carreras);
                                $estudio['id_carrera'] = ($carreras_flip[trim($estudio['otro_carrera'])])
                                        ? $carreras_flip[trim($estudio['otro_carrera'])]
                                        : 15;
                            }
                        }
                        if ((!array_key_exists($estudio['id_tipo_carrera'],
                                $tipoCarreras) ||
                            !array_key_exists($estudio['id_carrera'], $carreras))) {
                            $estudio['is_disabled'] = 1;
                        }
                    }
                    $estudio['institucion']    = $estudio['nombre'];
                    unset($estudio['nombre']);
                    $form                      = $managerOtrosEstudio->getForm($index++,
                        $estudio);
                    /* if (isset($estudio['id_carrera'])) {
                      $carrera = new Application_Model_Carrera();
                      //$form->getElement('otro_carrera')->setValue($carrera->getCarreraById($estudio['id_carrera']));
                      } */
                    $form->setHiddenId($estudio['id_estudio']);
                    $formsEstudio[]            = $form;
                    $this->view->isLinkedin    = true;
                    $this->view->isOtroEstudio = false;
                }
                $formsEstudio[] = $managerOtrosEstudio->getForm($index++);
            } else {
                if ($ok && !$this->getRequest()->isPost())
                        $formsEstudio[] = $managerOtrosEstudio->getForm(0);
            }
        } else {
            //if ($this->getRequest()->isPost()) {
            $postData                  = $this->_getAllParams();
            $validEst                  = $managerOtrosEstudio->isValid($postData);
            $this->view->isLinkedin    = true;
            $this->view->isOtroEstudio = false;
//            if ($this->_validEstudio($managerOtrosEstudio->getCleanPost())) {
            if ($validEst) {
                $this->_actualizarEstudioPostulante(
                    $managerOtrosEstudio->getCleanPost(), $idPostulante
                );
                $helper = $this->_helper->getHelper("RegistrosExtra");
                $helper->ActualizarPostulacion($this->auth["postulante"]["id"]);

                $this->getMessenger()->success($this->_messageSuccess);
                $this->_redirect(
                    Zend_Controller_Front::getInstance()
                        ->getRequest()->getRequestUri()
                );
            } else {
                $arrExp       = explode(',', $postData['managerOtroEstudio']);
                foreach ($arrExp as $index)
                    $managerOtrosEstudio->removeForm($index);
                $formsEstudio = $managerOtrosEstudio->getForms();
            }
        }

        $this->view->formEstudio = $formsEstudio;
        $this->view->assign('managerEstudio', $managerOtrosEstudio);
    }

    //Mantenimiento Otro Estudio
    private function _actualizarOtroEstudioPostulante(
    $managerCleanPost, $idPostulante
    )
    {
        $instituciones      = new Application_Model_Institucion();
        $carreras           = new Application_Model_Carrera();
        $listaInstituciones = $instituciones->getInstituciones();
        $listaCarreras      = $carreras->getCarreras();

        $idEstudioNew = 0;
        $count        = 0;
        foreach ($managerCleanPost as $form) {

            $data = $form;
            if ($data['id_nivel_estudio_tipo'] != 0 && !empty($data['otro_estudio'])
                && !empty($data['institucion']) && $data['pais_estudio'] != 'none') {
                $idEst   = $data['id_estudio'];
                $estudio = new Application_Model_Estudio();
                if ($data['id_nivel_estudio'] == 1) {
                    $data['id_carrera']       = null;
                    $data['en_curso']         = 0;
                    $data['otro_institucion'] = null;
                    $data['pais_estudio']     = 0;
                } else {
                    $data['en_curso']         = (bool) $data['en_curso'];
                    $data['otro_institucion'] = $data['institucion'];
                }
                unset($data['id_estudio']);
                unset($data['institucion']);
                unset($data['is_disabled']);

                if ($data['id_institucion'] == 0 || $data['id_institucion'] == '') {
                    $data['id_institucion'] = null;
                } else {
                    if (array_key_exists($data['id_institucion'],
                            $listaInstituciones)) {
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
                if (array_key_exists($data['id_carrera'], $listaCarreras) && $data['id_carrera']
                    != 15) {
                    $data['otro_carrera'] = $listaCarreras[$data['id_carrera']];
                }
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
                            ->quoteInto('id_postulante = ?', $idPostulante).
                            $estudio->getAdapter()
                            ->quoteInto(' and id = ?', $idEst);
                    if ($data['id_nivel_estudio'] != 0)
                            $estudio->update($data, $where);
                } else {
                    $data['id_postulante'] = $idPostulante;
                    if ($data['id_nivel_estudio'] != 0)
                            $idEstudioNew          = $estudio->insert($data);
                }
            }
        }

        $estudioModelo    = new Application_Model_Estudio;
        $estudioPrincipal = $estudioModelo->obtenerEstudiosMayorPesoPorPostulante($idPostulante);

        if (!empty($estudioPrincipal)) {
            $estudioModelo->actualizarEstudioPrincipal($idPostulante,
                $estudioPrincipal['id']);
        }

        $postulante = new Application_Model_Postulante();
        $where      = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')
            ), $where
        );
        $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
            $idPostulante, Application_Model_LogPostulante::ESTUDIOS
        );

        return $idEstudioNew;
    }

    public function updateInfoAction()
    {

        if (strpos($_SERVER['HTTP_REFERER'], 'ofertas-de-trabajo') !== false) {

            $redirect = str_replace('#winUpdateCV', '', $_SERVER['HTTP_REFERER']);

            $idPostulante = $this->auth['postulante']['id'];
            if (!$this->_postulante->verificarUpdateCV($idPostulante)) {
                $this->_postulante->update(
                    array(
                    'ultima_actualizacion' => date('Y-m-d H:i:s')),
                    $this->getAdapter()->quoteInto("id = ?", $idPostulante)
                );
            }

            $this->_redirect($redirect);
        } else {
            exit("Acceso denegado");
        }
    }

    public function perfilDestacadoAction()
    {

        $this->view->menu_sel_side = self::MENU_POST_SIDE_PERFILDESTACADO;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;

        $idPostulante = $this->auth['postulante']['id'];

        $page            = $this->_getParam('page', 1);
        $this->view->col = $col             = $this->_getParam('col', '');
        $this->view->ord = $ord             = $this->_getParam('ord', 'asc');
        $paginator       = $this->_perfil->getPaginator(
            $idPostulante, $col, $ord
        );

        $paginado = $this->config->perfilDestacado->paginado;

        $paginator->setItemCountPerPage($paginado);
        $paginator->setCurrentPageNumber($page);
        $this->view->perfil = $paginator;
        $this->view->moneda = $this->config->app->moneda;
    }

    public function listoAction()
    {

        $id                 = $this->idPostulante = $this->auth['postulante']['id'];

        if (Zend_Auth::getInstance()->hasIdentity() != true) {
            $this->_redirect('/registro/paso1');
        }

        $this->view->headTitle()->set(
            'Paso 3 - Listo!, Actualización de datos en  AquiEmpleos'
        );

        $sessionUpdateCV = new Zend_Session_Namespace('updateCV');
        $urlId           = $sessionUpdateCV->urlId;

        if (!isset($urlId)) {
            $this->_redirect('/registro/paso1');
        }
        //Obtener el nombre
        $dataAviso          = $this->_anuncioWeb->obtenerNombreAviso($urlId);
        $this->view->puesto = ucfirst($dataAviso['puesto']);


        $anunciosWeb = new Application_Model_AnuncioWeb();

        $avisosrelacionados = $anunciosWeb->getAvisosRelacionadosPasoTres($id);
        if (count($avisosrelacionados) == 0) {
            $avisosrelacionados = $anunciosWeb->getAvisosRelacionadosAuxiliar($id,
                15);
        }

        //Validar si tiene Perfil Destacado Activo
        $validaPD             = $this->_perfil->validaPerfilDestacado($id);
        $this->view->validaPd = true;
        if (is_null($validaPD)) {
            $this->view->validaPd = false;
        }

        $this->view->avisosrelacionados = $avisosrelacionados;

        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr',
            array(
            'id' => 'home2',
            'class' => array(
                'dark wide mobile noMenu'))
        );

        $config            = $this->getConfig();
        $searchUrlBuscamas = $config->apis->buscamas->searchUrl;
        $apiKeyBuscamas    = $config->apis->buscamas->consumerKey;

        $url       = $searchUrlBuscamas.$apiKeyBuscamas.'/start/0/count/20';
        $buscaMas  = $this->_helper->getHelper('BuscaMas');
        $resultado = $buscaMas->obtenerResultadoBuscaMasCache($url);

        $decode = Zend_Json::decode($resultado);

        $areasJSON     = $decode['filter']['area'];
        $nivelJSON     = $decode['filter']['level'];
        $ubicacionJSON = $decode['filter']['location'];

        $areaValorDesc = $buscaMas->ordenarArray($areasJSON, 'count', true);
        $areaDescDesc  = $buscaMas->ordenarArray($areasJSON, 'label', false);

        $nivelValorDesc = $buscaMas->ordenarArray($nivelJSON, 'count', true);
        $nivelDescDesc  = $buscaMas->ordenarArray($nivelJSON, 'label', false);

        $ubiValorDesc = $buscaMas->ordenarArray($ubicacionJSON, 'count', true);
        $ubiDescDesc  = $buscaMas->ordenarArray($ubicacionJSON, 'label', false);

        $this->view->groupAreas1       = $areaDescDesc;
        $this->view->groupAreas2       = $areaValorDesc;
        $this->view->groupNivelPuesto1 = $nivelDescDesc;
        $this->view->groupNivelPuesto2 = $nivelValorDesc;
        $this->view->groupDistritos1   = $ubiDescDesc;
        $this->view->groupDistritos2   = $ubiValorDesc;

        $form = new Application_Form_BuscarHome();
        $form->setAreas($areaDescDesc);
        $form->setNivelPuestos($nivelDescDesc);
        $form->setUbicacion($ubiDescDesc);

        $this->view->form = $form;
    }

    /**
     * Actualiza los datos del postulante desde el modal de falta de datos que
     * aparece al Postular
     */
    public function actualizarDatosPerfilAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        // exit;
        $id = isset($this->auth["postulante"]["id"]) ? $this->auth["postulante"]["id"]
                : 0;

        $dataRequest   = $this->_getAllParams();
        $requestValido = $this->getRequest()->isXmlHttpRequest();
        $redirect      = 0;
        if (isset($dataRequest['redirect'])) {
            $redirect = 1;
            unset($dataRequest['redirect']);
        }

        if (!$id || !$requestValido) {
            exit;
        }
        $form      = new Application_Form_RegistroComplePostulante($id,
            $this->auth["postulante"]);
        $distritos = new Application_Model_Ubigeo();

        $question    = isset($dataRequest['question']) ? $dataRequest['question']
                : null;
        $isValidForm = $form->isValid($dataRequest);

        if ($isValidForm && $id) {
            try {
                $valuesPostulante['tipo_doc']               = $dataRequest['selDocumento'];
                $valuesPostulante['num_doc']                = $dataRequest['txtNumero'];
                $valuesPostulante['sexo']                   = $dataRequest['selGenero'];
                ;
                $valuesPostulante['ultima_actualizacion']   = date('Y-m-d H:i:s');
                $valuesPostulante['pais_residencia']        = $dataRequest['selPais'];
                $valuesPostulante['pais_nacionalidad']      = $dataRequest['selPais'];
                $valuesPostulante['id_ubigeo']              = ($dataRequest['selPais']
                    == Application_Model_Ubigeo::PERU_UBIGEO_ID) ? $dataRequest['txtIdBusqueda']
                        : $dataRequest['selPais'];
                $where                                      = $this->_postulante->getAdapter()
                    ->quoteInto('id = ?', $id);
                $rest                                       = $this->_postulante->update($valuesPostulante,
                    $where);
                $storage                                    = Zend_Auth::getInstance()->getStorage()->read();
                $storage['postulante']['sexo']              = $valuesPostulante['sexo'];
                $storage['postulante']['tipo_doc']          = $valuesPostulante['tipo_doc'];
                $storage['postulante']['num_doc']           = $valuesPostulante['num_doc'];
                $storage['postulante']['id_ubigeo']         = $valuesPostulante['id_ubigeo'];
                $storage['postulante']['pais_residencia']   = $valuesPostulante['pais_residencia'];
                $storage['postulante']['pais_nacionalidad'] = $valuesPostulante['pais_nacionalidad'];
                Zend_Auth::getInstance()->getStorage()->write($storage);
                $this->_helper->solr->addSolr($id);
                if ($rest) {
                    $response = array(
                        'status' => '1',
                        'msg' => 'Los datos fueron válidos');
                }
            } catch (Exception $exc) {
                $this->log->log($exc->getMessage().'. '.$exc->getTraceAsString(),
                    Zend_Log::ERR);
                $response = array(
                    'status' => '0',
                    'msg' => 'error');
            }
        } else {
            $msg = '';

            foreach ($form->getErrors() as $key => $value) {
                if (count($value) > 0 && $key != 'tokenhiden') {
                    for ($i = 0; $i < count($value); $i++) {
                        $data = array(
                            $key => Zend_Json::encode(htmlentities(Application_Form_RegistroComplePostulante::$errors[$value[$i]],
                                    ENT_QUOTES | ENT_HTML401, 'UTF-8')));
                    }
                    $msg.= Zend_Json::encode($data);
                }
            }

            $form->getElement('tokenhiden')->initCsrfToken();
            $response          = array(
                'status' => '0',
                'msg' => $msg);
            $response['token'] = $form->getElement('tokenhiden')->getValue();
        }
        $usuario          = $this->auth['usuario'];
        $ususarioEmp      = new Application_Model_Usuario;
        $cuentaConfirmada = $ususarioEmp->hasConfirmed($usuario->id);

        $helper               = new App_Controller_Action_Helper_Util();
        $response['newmodal'] = $helper->_NexAction($question, $id);
        if ($redirect) {
            $response['urlRedirect'] = SITE_URL.'/perfil-destacado';
        }


        if (!$redirect && $response['newmodal'] == 'postular') {
            if ($cuentaConfirmada) {
                $this->_postular($dataRequest['ulr_id']);
            }
        }
        $this->_response->appendBody(Zend_Json::encode($response));
    }

    public function eliminarCuentaAction()
    {
        $this->view->menu_sel_side = self::MENU_POST_SIDE_ELIMINAR_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->isAuth        = $this->isAuth;
        Zend_Layout::getMvcInstance()->assign(
            'subMenuPrivacidad', $this->_submenuPrivacidad
        );

        $formEliminarCuenta = new Application_Form_EliminarCuenta();

        if ($this->_request->isPost()) {
            $allParams = $this->_getAllParams();

            if ($formEliminarCuenta->isValid($allParams)) {

                $arrayPostulante = $this->_postulante->getPostulante($this->idPostulante);

                $idUsuario = $arrayPostulante['id_usuario'];

                $emailUsuario    = $arrayPostulante['email'];
                $isValidPassword = Application_Model_Usuario::validacionPswd($allParams['txtPassword'],
                        false, $emailUsuario, $idUsuario);

                if ($isValidPassword === true) {
                    try {

                        $db = $this->getAdapter();
                        $db->beginTransaction();

                        $postulante      = new Application_Model_Postulante();
                        $wherePostulante = $postulante->getAdapter()
                            ->quoteInto('id = ?', $this->idPostulante);
                        $postulante->update(array(
                            'ultima_actualizacion' => date('Y-m-d H:i:s'),
                            'last_update_ludata' => date('Y-m-d H:i:s'),
                            'prefs_confidencialidad' => 1,
                            'solr' => 0
                            ), $wherePostulante);


                        $usuario      = new Application_Model_Usuario();
                        $whereUsuario = $usuario->getAdapter()
                            ->quoteInto('id = ?', $idUsuario);
                        $usuario->update(array(
                            'elog' => 1,
                            'activo' => 0
                            ), $whereUsuario);
                        $db->commit();
                    } catch (Exception $ex) {
                        $db->rollBack();
                        $this->log->log($ex->getMessage().'. '.$ex->getTraceAsString(),
                            Zend_Log::ERR);
                        throw new Exception($ex->getMessage());
                    }

                    try {
                        $mongoEliminarcuenta = new Mongo_EliminarCuenta();
                        $datos               = array(
                            'usuario' => $this->auth,
                            'chxReassons' => $allParams['chxReassons'],
                            'txaReasons' => $allParams['txaReasons'],
                        );
                        $mongoEliminarcuenta->save($datos);

                        $sc           = new Solarium\Client($this->config->solr);
                        $moPostulante = new Solr_SolrAbstract($sc, 'postulante');
                        $moPostulante->deletePostulante((int) $this->idPostulante);
                    } catch (Exception $ex) {
                        $this->log->log($ex->getMessage().'. '.$ex->getTraceAsString(),
                            Zend_Log::ERR);
                        throw new Exception($ex->getMessage());
                    }


                    Zend_Auth::getInstance()->clearIdentity();
                    Zend_Session::forgetMe();
                    $dataFacebook       = new Zend_Session_Namespace('dataFacebook');
                    $dataFacebook->data = array();
                    $dataLinkedin       = new Zend_Session_Namespace('dataLinkedin');
                    $dataLinkedin->data = array();


                    $this->getMessenger()->success('Cuenta eliminada con éxito.');
                    $this->_redirect('/');
                } else {
                    $this->getMessenger()->error('La contraseña ingresada no es valida.');
                }

                $this->_redirect(
                    Zend_Controller_Front::getInstance()
                        ->getRequest()->getRequestUri()
                );
            }
        }

        $this->view->formEliminarCuenta = $formEliminarCuenta;
    }

    public function misEstudiosAction()
    {
        $this->view->isAuth     = $this->isAuth;
        $session                = new Zend_Session_Namespace('linkedin');
        $this->view->showImport = false;
        Zend_Layout::getMvcInstance()->assign(
            'submenuMiCuenta', $this->_submenuMiCuenta
        );
        $idPostulante           = $this->idPostulante;
        $importoDatos           = true;
//        $importoDatos = $this->linkedImported
//                ->isImported('estudio',$idPostulante);
//        if ($importoDatos === false) {
//            $this->view->showImport = true;
//            if (isset($session->linkedin)) {
//                $this->getMessenger()->success(
//                    "Se logró importar tus estudios de linkedin."
//                );
//                $linkedinData = $session->linkedin;
//                $this->_linkedinSaveEstudio($linkedinData,$idPostulante);
//                unset($session->linkedin);
//                $this->_redirect('/mi-cuenta/mis-estudios');
//            }
//        }



        $formEstudio           = new Application_Form_MisEstudiosPostulante();
        $estudio               = new Application_Model_Estudio();
        $arrayEstudios         = $estudio->getEstudiosNuevo($idPostulante);
        $tipoCarrerasAptitus   = Application_Model_TipoCarrera::getTiposCarrerasIds();
        $carrerasAptitus       = Application_Model_Carrera::getCarrerasIds();
        $resEstudiosPostulante = array();
        foreach ($arrayEstudios as $key => $itemEstudio) {
            $item                = $itemEstudio;
            $item['is_disabled'] = 0;
            if (!empty($item['id_tipo_carrera'])) {
                if (!in_array((int) $item['id_nivel_estudio'],
                        array(
                        1,
                        2,
                        3,
                        9))) {
                    if ((!array_key_exists($item['id_tipo_carrera'],
                            $tipoCarrerasAptitus) ||
                        !array_key_exists($item['id_carrera'], $carrerasAptitus))) {
                        $item['is_disabled'] = 1;
                    }
                }
            }



            $resEstudiosPostulante[] = $item;
        }

        $formEstudio->removeElement('hidStudy');
        $this->view->formEstudiosPostulante = $formEstudio;
        $this->view->lisEstudios            = $resEstudiosPostulante;

        $maxExperienceYear = $this->config->misEstudios->maxExperienceYears;

        $dateTime = new DateTime('now');
        $maxYear  = $dateTime->format('Y');

        $yearMin = $dateTime->sub(new DateInterval('P100Y'));
        $minYear = $yearMin->format('Y');

        $this->view->maxYear = $maxYear;
        $this->view->minYear = $minYear;

        $script           = 'yOSON.currentDate="'.date('Y-m').'";';
        $script .= 'yOSON.maxExperienceYears='.$maxExperienceYear.';';
        $this->view->headScript()->appendScript($script);
        $this->view->slug = $this->auth['postulante']['slug'];
    }

    public function filtrarCarreraAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $valCarrera = trim($this->_getParam('value', ''));
        $token      = $this->_getParam('csrfhash');

        $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());
        $paramsValido  = ($requestValido && $valCarrera && $token);
        if ($requestValido && $paramsValido) {
            if ($this->_hash->isValid($token)) {
                $filter     = new Zend_Filter_StripTags();
                $valCarrera = $filter->filter($valCarrera);
                $solrCarrea = new Solr_SolrCarrera();
                $Items      = $solrCarrea->getCarreraByName($valCarrera,
                    $this->config->autocomplete->items);
                $data       = array(
                    'status' => '1',
                    "messages" => "Sus fueron encontrados.",
                    'items' => $Items
                );
            } else {
                $data = array(
                    'status' => '0',
                    "messages" => "No se encontraron resultados",
                    // 'items' => array()
                );
            }
            $this->_response->appendBody(Zend_Json::encode($data));
        }
    }

    public function filtrarInstitucionAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $valInstitucion = trim($this->_getParam('value', ''));
        $token          = $this->_getParam('csrfhash');

        $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());
        $requestDatos  = ($valInstitucion && $token);

        if ($requestValido && $requestDatos) {
            if ($this->_hash->isValid($token) && strlen($valInstitucion) >= 3) {
                $solrInstitucion = new Solr_SolrInstitucion();
                $filter          = new Zend_Filter_StripTags();
                $valInstitucion  = $filter->filter(strtolower($valInstitucion));
                $Items           = $solrInstitucion->getInstitucionByName($valInstitucion,
                    $this->_config->autocomplete->items);
                $data            = array(
                    'status' => '1',
                    "messages" => "Sus fueron encontrados.",
                    'items' => $Items
                );
            } else {
                $data = array(
                    'status' => '0',
                    "messages" => "No se encontraron resultados",
                    // 'items' => array()
                );
            }
            $this->_response->appendBody(Zend_Json::encode($data));
        } else {
            exit;
        }
    }

    public function misEstudiosAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idPostulante = isset($this->auth['postulante']['id']) ? $this->auth['postulante']['id']
                : 0;

        $allParams = $this->_getAllParams();
        $XSS       = new App_Util();
        $data      = $allParams;
        if (!isset($data['actualStudent'])) {
            $data['actualStudent'] = 0;
        } else {
            $data['actualStudent'] = 1;
        }



        $formEstudio = new Application_Form_MisEstudiosPostulante();
        $formEstudio->removeElement('hidStudy');
        //  $formEstudio->removeElement('hidToken');
        $isValidForm = $formEstudio->isValid($data);

        if ($isValidForm && $idPostulante) {
            try {
//                $db = $this->getAdapter();
//                $db->beginTransaction();

                $rs              = $this->_actualizarPostulanteEstudio($data,
                    $idPostulante);
                $id_estudio      = $rs['id'];
                $mensaje         = $rs['mensaje'];
                $helper          = $this->_helper->getHelper('RegistrosExtra');
                $helper->ActualizarPostulacion($idPostulante);
                $helper->ActualizarEstudios($idPostulante);
//                $db->commit();
                /// Para devolver los textos:
                $txtNivelEstudio = $formEstudio->selLevelStudy->getMultiOption($data['selLevelStudy']);

                $objDisabled = '';
                if (isset($data['selStateStudy'])) {
                    $estados_estudio  = new Application_Model_NivelEstudio();
                    $txtEstadoEstudio = '('.$estados_estudio->getNombreNivelById($data['selStateStudy']).')';
                } else {

                    $txtEstadoEstudio = ($data['hidStudy'] == 0) ? '' : $objDisabled;
                }

                $txtMesInicio = $formEstudio->selMonthBegin->getMultiOption($data['selMonthBegin']);
                if (!isset($data['selCountry'])) {
                    $data['selCountry'] = 2533;
                }
                $txtPais = $formEstudio->selCountry->getMultiOption($data['selCountry']);

                $dataForm = array(
                    'hidStudy' => $id_estudio,
                    'selLevelStudy' => $txtNivelEstudio,
                    'selStateStudy' => (empty($txtEstadoEstudio)) ? $objDisabled
                            : $txtEstadoEstudio,
                    'txtInstitution' => $data['txtInstitution'],
                    'txtYearBegin' => $data['txtYearBegin'],
                    'selMonthBegin' => $txtMesInicio,
                    'selCountry' => $txtPais,
                    'actualStudent' => $data['actualStudent']
                );

                if (isset($data['txtCareer']) && !empty($data['txtCareer'])) {
                    $dataForm['txtCareer'] = $data['txtCareer'];
                } else {
                    $dataForm['txtCareer'] = ($data['hidStudy'] == 0) ? '' : $objDisabled;
                }

                if (isset($data['txtYearEnd']) && isset($data['selMonthEnd'])) {
                    $dataForm['txtYearEnd']  = $data['txtYearEnd'];
                    $txtMesFinal             = $formEstudio->selMonthEnd->getMultiOption($data['selMonthEnd']);
                    $dataForm['selMonthEnd'] = $txtMesFinal;
                } else {
                    $dataForm['txtYearEnd']  = $objDisabled;
                    $dataForm['selMonthEnd'] = $objDisabled;
                }


                if (isset($data['actualStudent']) && $data['actualStudent'] != 0) {
                    $dataForm['txtYearEnd']  = $objDisabled;
                    $dataForm['selMonthEnd'] = $objDisabled;
                }


                $formEstudio->getElement('hidToken')->initCsrfToken();
                $response['token']   = $formEstudio->getElement('hidToken')->getValue();
                $response['status']  = 1;
                $response['message'] = $mensaje;
                $response['skill']   = $dataForm;
                $data['id']          = $idPostulante;
                $dataporcentaje      = $this->_helper->LogActualizacionBI->logActualizacionPostulanteEstudio($data);
                try {
                    $response['percent']     = (((int) $dataporcentaje['total_completado']));
                    $response['iscompleted'] = array(
                        'Estudios',
                        1);
                } catch (Exception $ex) {
                    throw new Zend_Exception('Porcentajes Estudios del Postulante: '.$ex->getMessage());
                }
                //   $solrAdd = new Solr_SolrPostulante();
                //    $solrAdd->add($idPostulante);
//                @$this->_cache->remove('get_perfil_postulante_' . str_replace('-', '_', $this->_slug));
//                @$this->_cache->remove('get_perfil_postulante_' . str_replace('-', '_', $this->idPostulante));
//                @$this->_cache->remove('perfil_postulante_' . str_replace('-', '_', $this->_slug));
//                @$this->_cache->remove('perfil_postulante_' . str_replace('-', '_', $this->idPostulante));
            } catch (Zend_Exception $exc) {
                var_dump($exc->getTraceAsString());
                Exit;
                $response['percent'] = 0;
                $this->log->log($exc->getMessage().'. '.$exc->getTraceAsString(),
                    Zend_Log::ERR);
            } catch (Exception $exc) {
                $this->log->log($exc->getMessage().'. '.$exc->getTraceAsString(),
                    Zend_Log::ERR);
                $response = array(
                    'status' => '0',
                    'message' => 'No se pudo realizar la operación, por favor vuelva a intentarlo.'
                );
            }
        } else {
            $msg = '';
            foreach ($formEstudio->getErrors() as $key => $value) {
                if (count($value) > 0 && $key != 'hidToken') {
                    for ($i = 0; $i < count($value); $i++) {
                        // Application_Form_PostulanteDatosPersonales
                        $a    = htmlentities(Application_Form_MisEstudiosPostulante::$errors[$value[$i]],
                            ENT_HTML401, 'UTF-8');
                        $data = array(
                            $key => Zend_Json::encode($a));
                    }
                    $msg.= Zend_Json::encode($data);
                }
            }
            $formEstudio->getElement('hidToken')->initCsrfToken();
            $response = array(
                'status' => '0',
                'message' => $msg,
                'token' => $formEstudio->getElement('hidToken')->getValue()
            );
        }

        $this->_response->appendBody(Zend_Json::encode($response));
    }

    /**
     * Guarda los datos del formulario de Estudio
     * @param array $data Datos a guardar
     * @param int $idPostulante Id del postulante
     * @return int
     */
    private function _actualizarPostulanteEstudio($data, $idPostulante)
    {
        $instituciones               = new Application_Model_Institucion();
        $carreras                    = new Application_Model_Carrera();
        $estudio                     = new Application_Model_Estudio();
        $listaInstituciones          = $instituciones->getInstituciones();
        $listaCarreras               = $carreras->getCarreras();
        //  var_dump($data);Exit;
        $idEstudio                   = 0;
        $valEstudio                  = array();
        $valEstudio['id_postulante'] = $idPostulante;
//        var_dump($data);exit;
        $valEstudio['pais_estudio']  = isset($data['selCountry']) ? $data['selCountry']
                : 2533;
        if (in_array($data['selLevelStudy'],
                array(
                1,
                2,
                3,
                9))) {
            $valEstudio['id_nivel_estudio_tipo'] = null;
            $valEstudio['en_curso']              = 0;
            $valEstudio['otro_institucion']      = null;
        } else {
            $valEstudio['en_curso'] = (int) $data['actualStudent'];
        }

        $valEstudio['id_nivel_estudio'] = $data['selLevelStudy'];
        if (isset($data['selStateStudy'])) {
            $valEstudio['id_nivel_estudio_tipo'] = $data['selStateStudy'];
        }
        $busq_inst                      = array_search(trim($data['txtInstitution']),
            $listaInstituciones);
        $id_institucion                 = ($busq_inst === false) ? null : $busq_inst;
        $valEstudio['id_institucion']   = $id_institucion;
        $valEstudio['otro_institucion'] = $data['txtInstitution'];

        if (isset($data['hidCareer']) && !empty($data['hidCareer'])) {
            $busq_carr  = array_search(trim($data['txtCareer']), $listaCarreras);
            $id_carrera = ($busq_carr === false) ? null : $busq_carr;
            if ($id_carrera && $id_carrera != Application_Model_Carrera::OTRO_CARRERA) {
                $valEstudio['id_carrera']      = $id_carrera;
                //tipo carrera
                $tipoC                         = new Application_Model_Carrera();
                $valEstudio['id_tipo_carrera'] = $tipoC->getTipoCarreraXCarrera($id_carrera);
            } else {
                $valEstudio['id_carrera'] = Application_Model_Carrera::OTRO_CARRERA;
            }
            $valEstudio['otro_carrera'] = (isset($data['txtCareer'])) ? $data['txtCareer']
                    : '';
        } else {
            $valEstudio['id_carrera']   = Application_Model_Carrera::OTRO_CARRERA;
            $valEstudio['otro_carrera'] = (isset($data['txtCareer'])) ? $data['txtCareer']
                    : '';
        }


        $valEstudio['inicio_mes'] = $data['selMonthBegin'];
        $valEstudio['inicio_ano'] = $data['txtYearBegin'];
        if (isset($data['selMonthEnd']) && isset($data['txtYearEnd'])) {
            $valEstudio['fin_mes'] = $data['selMonthEnd'];
            $valEstudio['fin_ano'] = $data['txtYearEnd'];
        }
        $valEstudio['pais_estudio'] = isset($data['selCountry']) ? $data['selCountry']
                : 2533;

        //Si es primaria o secundaria
        if ($data['selLevelStudy'] == 2 || $data['selLevelStudy'] == 3) {
            unset($valEstudio['otro_estudio']);
        }

        if ($data['actualStudent'] == 1) {
            $valEstudio['en_curso'] = 1;
            unset($data['selMonthEnd']);
            unset($data['txtYearEnd']);
        } else {
            $valEstudio['en_curso'] = 0;
        }

        $result = array();
        if ($data['hidStudy']) {
            $where             = $estudio->getAdapter()
                    ->quoteInto('id_postulante = ?', $idPostulante).
                    $estudio->getAdapter()
                    ->quoteInto(' and id = ?', $data['hidStudy']);
            $estudio->update($valEstudio, $where);
            $idEstudio         = $data['hidStudy'];
            $result['id']      = $idEstudio;
            $result['mensaje'] = $this->_messageSuccessActualizar;
        } else {

            $idEstudio         = $estudio->insert($valEstudio);
            $result['id']      = $idEstudio;
            $result['mensaje'] = $this->_messageSuccessRegistrar;
        }

       /* $estudioModelo    = new Application_Model_Estudio;
        $estudioPrincipal = $estudioModelo->obtenerEstudiosMayorPesoPorPostulante($idPostulante);

        if (!empty($estudioPrincipal)) {
            $estudioModelo->actualizarEstudioPrincipal($idPostulante,
                $estudioPrincipal['id']);
        }
        */
        $postulante = new Application_Model_Postulante();
        $where      = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')
            ), $where
        );
        try {
            @$this->_cache->remove('Postulante_getEstudios_'.$idPostulante);
        } catch (Exception $exc) {

        }

        return $result;
    }

    public function getDataEstudiosAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $estudios = new Application_Model_Estudio();
        $id       = $this->_getParam('id');
        $tok      = $this->_getParam('csrfhash');
        //var_dump($estudios);exit;
        $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());
        $requestValido = ($requestValido && $id && $tok);

        $data = array();
        if (!$requestValido) {
            $data = array(
                'status' => 0,
                'messages' => $this->_messageError);
        }

        $filter = new Zend_Filter_StripTags();
        $id     = $filter->filter($id);

        $lisEstudio           = $estudios->getEstudioXId($id);
        $estados_estudio      = new Application_Model_NivelEstudio();
        $resultEstadosEstudio = $estados_estudio->getSubNivelesPadre($lisEstudio[0]['id_nivel_estudio']);
        $dataEstadosEstudio   = array();
        foreach ($resultEstadosEstudio as $item) {
            $dataEstadosEstudio[$item['id']] = $item['nombre'];
        }

        $objDisabled           = new stdClass();
        $objDisabled->disabled = true;
        $disabled              = $objDisabled;
        $selStateStudy         = '';
        if (empty($lisEstudio[0]['id_nivel_estudio_tipo'])) {
            $selStateStudy = $disabled;
        } else {
            $selStateStudy = $lisEstudio[0]['id_nivel_estudio_tipo'];
        }

        $inicio_mes = '';
        if (empty($lisEstudio[0]['inicio_mes'])) {
            $inicio_mes = $disabled;
        } else {
            $inicio_mes = $lisEstudio[0]['inicio_mes'];
        }



        $pais_estudio = '';
        if (empty($lisEstudio[0]['pais_estudio'])) {
            $pais_estudio = $disabled;
        } else {
            $pais_estudio = $lisEstudio[0]['pais_estudio'];
        }

        $otro_carrera = '';
        if (empty($lisEstudio[0]['otro_carrera'])) {
            $otro_carrera = $disabled;
        } else {
            $otro_carrera = $lisEstudio[0]['otro_carrera'];
        }


        $fin_ano = '';
        if (empty($lisEstudio[0]['fin_ano'])) {
            $fin_ano = $disabled;
        } else {
            $fin_ano = $lisEstudio[0]['fin_ano'];
        }

        $fin_mes = '';
        if (empty($lisEstudio[0]['fin_mes'])) {
            $fin_mes = $disabled;
        } else {
            $fin_mes = $lisEstudio[0]['fin_mes'];
        }

        //var_dump($lisEstudio); exit;

        $data['status']                 = 1;
        $data['messages']               = $this->_messageSuccess;
        $data['skill']['hidStudy']      = $lisEstudio[0]['id'];
        $data['skill']['selLevelStudy'] = $lisEstudio[0]['id_nivel_estudio'];
        $data['skill']['selStateStudy'] = $selStateStudy;

        $data['skill']['selStateStudyCombo'] = $dataEstadosEstudio;

        $data['skill']['txtInstitution'] = $lisEstudio[0]['otro_institucion'];
        $data['skill']['txtCareer']      = $otro_carrera;
        if (!array_key_exists($lisEstudio[0]['id_carrera'],
                array(
                15,
                50,
                65,
                69,
                76,
                86,
                94,
                101,
                103,
                125,
                130,
                152,
                158,
                184,
                206,
                207))) {
            $data['skill']['hidCareer'] = $lisEstudio[0]['id_carrera'];
        }

        $data['skill']['selMonthBegin'] = $inicio_mes;
        $data['skill']['txtYearBegin']  = $lisEstudio[0]['inicio_ano'];
        if ($lisEstudio[0]['en_curso'] == 0) {
            $data['skill']['selMonthEnd'] = $fin_mes;
            $data['skill']['txtYearEnd']  = $fin_ano;
        } else {
            $data['skill']['selMonthEnd'] = $disabled;
            $data['skill']['txtYearEnd']  = $disabled;
        }

        $data['skill']['selCountry']    = $pais_estudio;
        $data['skill']['actualStudent'] = $lisEstudio[0]['en_curso'];

        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function filtrarEstadoEstudioAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $id_nivel_estudio = $this->_getParam('id_nivel_estudio');
        $token            = $this->_getParam('token');

        $estados_estudio = new Application_Model_NivelEstudio();
        $requestValido   = ($this->getRequest()->isXmlHttpRequest() && $this->_hash->isValid($token));
        if (!$requestValido) {
            exit;
        }

        $data = array();
        if ($this->_hash->isValid($token)) {
            $resultData = $estados_estudio->getSubNivelesPadre($id_nivel_estudio);
            if (count($resultData) > 0) {
                foreach ($resultData as $item) {
                    $data[$item['id']] = $item['nombre'];
                }
            } else {
                $data = array(
                    '0' => 'No se encontraron resultados'
                );
            }
        }

        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function filtrarNivelAreaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $id_area = $this->_getParam('id_area');
        $token   = $this->_getParam('token');

        $nivelPuesto = new Application_Model_NivelPuesto();

        $requestValido = ($this->getRequest()->isXmlHttpRequest());

        if (!$requestValido) {
            exit;
        }

        $data       = array();
        //if ($this->_hash->isValid($token)) {
        $resultData = $nivelPuesto->getNivelesByArea($id_area);
        if (count($resultData) > 0) {
            foreach ($resultData as $item) {
                $data[$item['id']] = $item['nombre'];
            }
        } else {
            $data = array(
                '0' => 'No se encontraron resultados'
            );
        }
        //}

        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function misLogrosAction()
    {
//        $session = new Zend_Session_Namespace('linkedin');
        $this->view->showImport = false;
        Zend_Layout::getMvcInstance()->assign(
            'submenuMiCuenta', $this->_submenuMiCuenta
        );
//        $idPostulante = $this->idPostulante;
        $formLogros             = new Application_Form_PostulanteLogros();
        $ModelLogros            = new Application_Model_Logros();
//        if (isset($session->linkedin)) {
//            $this->view->showImport = true;
//            $this->getMessenger()->success(
//                    "Se logró importar tus datos de linkedin. Por favor, ingresa o
//                selecciona los datos que no hayan coincidido exactamente con
//                los de APTiTUS."
//            );
//            $linkedinData = $session->linkedin;
//            $this->_linkedinSaveLogro($linkedinData,$idPostulante);
//            unset($session->linkedin);
//        }
        $rslogros               = $ModelLogros->getLogrosPostulante($this->idPostulante);
        $this->view->lislogros  = $rslogros;
        $this->view->form       = $formLogros;
        $this->view->slug       = $this->auth['postulante']['slug'];
    }

    public function misLogrosAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $id        = $this->idPostulante;
        $allParams = $this->_getAllParams();
        $dataForm  = App_Util::clearXSS($allParams);

        $ModelLogros   = new Application_Model_Logros();
        $formLogros    = new Application_Form_PostulanteLogros(true);
        $isvalidLogro  = $formLogros->isValid($dataForm);
        $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());

        $response = array();
        if ($isvalidLogro && $requestValido) {
            $valLogro = $dataForm['hidAchievements'];

            if (!$valLogro) {
                $idLogro = $ModelLogros->insert(array(
                    'id_postulante' => $id,
                    'logro' => $dataForm['txtPrize'],
                    'institucion' => $dataForm['txtInstitution'],
                    'ano' => $dataForm['txtDateAchievement'],
                    'mes' => $dataForm['selDate'],
                    'descripcion' => $dataForm['txtDescription']
                ));
                $mensaje = $this->_messageSuccessRegistrar;
            } else {
                $where   = $ModelLogros->getAdapter()->quoteInto('id_postulante = ?',
                        $id).
                    $ModelLogros->getAdapter()->quoteInto(' and id = ?',
                        $valLogro);
                $ModelLogros->update(
                    array(
                    'logro' => $dataForm['txtPrize'],
                    'institucion' => $dataForm['txtInstitution'],
                    'ano' => $dataForm['txtDateAchievement'],
                    'mes' => $dataForm['selDate'],
                    'descripcion' => $dataForm['txtDescription']
                    ), $where);
                $idLogro = $dataForm['hidAchievements'];
                $mensaje = $this->_messageSuccessActualizar;
            }
            $helper = $this->_helper->getHelper("RegistrosExtra");
            $helper->ActualizarPostulacion($id);


            $meses          = App_Util::getMonths();
            $data           = array(
                'hidAchievements' => $idLogro,
                'txtPrize' => $dataForm['txtPrize'],
                'txtInstitution' => $dataForm['txtInstitution'],
                'txtDateAchievement' => $dataForm['txtDateAchievement'],
                'selDate' => $dataForm['selDate'],
                'txtDescription' => $dataForm['txtDescription'],
                'txtMonth' => $meses[$dataForm['selDate']],
            );
            $data['id']     = $this->idPostulante;
            $dataporcentaje = $this->_helper->LogActualizacionBI->logActualizacionPostulanteLogros($data);
            unset($data['id']);

            $formLogros->getElement('hidToken')->initCsrfToken();
            $response['token']       = $formLogros->getElement('hidToken')->getValue();
            $response['status']      = 1;
            $response['message']     = $mensaje;
            $response['skill']       = $data;
            $response['iscompleted'] = array(
                "Logros",
                1);
            $response['percent']     = $dataporcentaje['total_completado'];
        } else {
            $response['status']  = 0;
            $response['message'] = Application_Form_PostulanteLogros::getMensajesErrors($formLogros);
            $formLogros->getElement('hidToken')->initCsrfToken();
            $response['token']   = $formLogros->getElement('hidToken')->getValue();
        }
        $this->_response->appendBody(json_encode($response));
    }

    public function getDataLogrosAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $logro = new Application_Model_Logros();
        $id    = $this->_getParam('id');

        $tok = $this->_getParam('csrfhash');

        $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());
        $requestValido = ($requestValido && $id && $tok);
        $data          = array();
        if (!$requestValido) {
            $data = array(
                'status' => 0,
                'messages' => $this->_messageError);
        }

        $filter                              = new Zend_Filter();
        $filter->addFilter(new Zend_Filter_StripTags());
        $id                                  = $filter->filter($id);
        $data['status']                      = 1;
        $data['messages']                    = $this->_messageSuccess;
        $lisLogros                           = $logro->getLogroXId($id);
        $data['skill']['hidAchievements']    = $id;
        $data['skill']['txtPrize']           = $lisLogros[0]['logro'];
        $data['skill']['txtInstitution']     = $lisLogros[0]['institucion'];
        $data['skill']['txtDateAchievement'] = $lisLogros[0]['ano'];
        $data['skill']['selDate']            = $lisLogros[0]['mes'];
        $data['skill']['txtDescription']     = $lisLogros[0]['descripcion'];

        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function getDataProgramasAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $programa      = new Application_Model_DominioProgramaComputo();
        $id            = $this->_getParam('id');
        $tok           = $this->_getParam('csrfhash');
        $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());
        $requestValido = ($requestValido && $id && $tok);
        $data          = array();
        if (!$requestValido) {
            $data = array(
                'status' => 0,
                'messages' => $this->_messageError);
        }

        if ($this->_hash->isValid($tok)) {
            $filter                       = new Zend_Filter();
            $filter->addFilter(new Zend_Filter_StripTags());
            $id                           = $filter->filter($id);
            $data['status']               = 1;
            $data['messages']             = $this->_messageSuccess;
            $lisprograma                  = $programa->getProgramaComputo($id);
            $data['skill']['hidPrograms'] = $lisprograma[0]['id_dominioComputo'];
//            $data['skill']['selProgram']=$lisprograma[0]['id_programa_computo'];
            $data['skill']['txtProgram']  = $lisprograma[0]['nombre'];
            $data['skill']['selProgram']  = $lisprograma[0]['id_programa_computo'];
            $data['skill']['selLevel']    = str_replace("á", "a", $lisprograma[0]['nivel']);
        } else {
            $data = array(
                'status' => 0,
                'messages' => $this->_messageError);
        }
        $this->_response->appendBody(Zend_Json::encode($data));
        //  exit;
    }

    public function updateProgramasAjaxAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $allParams = $this->_getAllParams();

        $id            = $this->auth['postulante']['id'];
        $response      = array();
        $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());

        if (isset($allParams['selProgram']) && isset($allParams['hidPrograms']) && $requestValido) {

            $FormProgramas    = new Application_Form_Paso2ProgramaNew(true);
            $isvalidProgramas = $FormProgramas->isValid($allParams);

            if ($isvalidProgramas === false) {

                $FormProgramas->getElement('hidToken')->initCsrfToken();
                $response = array(
                    'status' => 0,
                    'message' => 'Por favor vuelva ha intentarlo',
                    'token' => $FormProgramas->getElement('hidToken')->getValue()
                );
            } else {

                $porcCV          = new App_Controller_Action_Helper_PorcentajeCV();
                $ClasPrograma    = new Application_Model_DominioProgramaComputo();
                $isvalidRepetido = $ClasPrograma->getProgramaExisteRepetido(
                    $id, $allParams['selProgram'], $allParams['hidPrograms']
                );

                if ($isvalidRepetido === true) {
                    $FormProgramas->getElement('hidToken')->initCsrfToken();
                    $response = array(
                        'status' => 0,
                        'message' => 'El programa ya existe',
                        'token' => $FormProgramas->getElement('hidToken')->getValue()
                    );
                } else {

                    $requestProgramas = $this->_updateProgramaComputoPostulante($allParams,
                        $id);
                    $idProgramas      = $requestProgramas['id'];

                    $lisprograma = $ClasPrograma->getProgramaComputo($idProgramas);
                    $data        = array(
                        'hidPrograms' => $idProgramas,
                        'txtProgram' => $lisprograma[0]['nombre'],
                        'selProgram' => $lisprograma[0]['id_programa_computo'],
                        'selLevel' => $FormProgramas->selLevel->getMultiOption($allParams['selLevel']),
                    );

                    //Actualizamos postulaciones.
                    $helper         = $this->_helper->getHelper('RegistrosExtra');
                    $helper->ActualizarPostulacion($id);
                    $data['id']     = $id;
                    $solrAdd        = new Solr_SolrPostulante();
                    $solrAdd->add($id);
                    $dataporcentaje = $this->_helper->LogActualizacionBI->logActualizacionPostulanteProgramas($data);
                    unset($data['id']);
                    $FormProgramas->getElement('hidToken')->initCsrfToken();

                    $response = array(
                        'token' => $FormProgramas->getElement('hidToken')->getValue(),
                        'status' => 1,
                        'message' => $requestProgramas['mensaje'],
                        'skill' => $data,
                        'iscompleted' => array(
                            'Informática',
                            1
                        ),
                        'percent' => $dataporcentaje['total_completado']
                    );
                }
            }
        }

        $this->_response->appendBody(json_encode($response));
    }

    private function _updateProgramaComputoPostulante($data, $idPostulante
    )
    {
        $idProgramaNew = 0;
        $count         = 0;
        $arrayID       = array();
        $idPro         = $data['hidPrograms'];
        $programa      = new Application_Model_DominioProgramaComputo();

        if ($idPro) {
            $dataPrograma['id_programa_computo'] = $data['selProgram'];
            $dataPrograma['nivel']               = $data['selLevel'];
            $where                               = $programa->getAdapter()
                    ->quoteInto('id_postulante = ?', $idPostulante).
                    $programa->getAdapter()
                    ->quoteInto(' and id = ?', $idPro);
            $programa->update($dataPrograma, $where);
            $arrayID                             = $idPro;
            $mensaje                             = $this->_messageSuccessActualizar;
        } else {
            $dataPrograma['id_postulante']       = $idPostulante;
            $dataPrograma['id_programa_computo'] = $data['selProgram'];
            $dataPrograma['nivel']               = $data['selLevel'];
            $idProgramaNew                       = $programa->insert($dataPrograma);
            $arrayID                             = $idProgramaNew;
            $mensaje                             = $this->_messageSuccessRegistrar;
        }
        $postulante = new Application_Model_Postulante();
        $where      = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')), $where
        );
//        $this->_helper->LogActualizacionBI->logActualizacionPostulantePaso2(
//                $idPostulante, Application_Model_LogPostulante::PROGRAMAS
//        );

        return array(
            'id' => $arrayID,
            'mensaje' => $mensaje);
    }

    //Mantenimiento Idioma
    private function _UpdateReferenciaPostulante($data, $idPostulante)
    {
        $data['listaexperiencia'] = $data['selCareReference'];
        $data['nombre']           = $data['txtNameReference'];
        $data['cargo']            = $data['txtPositionReference'];
        $data['telefono']         = $data['txtTelephoneReferenceOne'];
        $data['telefono2']        = $data['txtTelephoneReferenceTwo'];
        $data['email']            = $data['txtTelephoneReferenceEmail'];
        unset($data['selCareReference']);
        unset($data['txtNameReference']);
        unset($data['txtPositionReference']);
        unset($data['txtTelephoneReferenceOne']);
        unset($data['txtTelephoneReferenceTwo']);
        unset($data['txtTelephoneReferenceEmail']);
        $data['id_experiencia']   = $data['listaexperiencia'];
        $idRef                    = $data['id_referencia'];

        $referencia = new Application_Model_Referencia();
        unset($data['id_referencia']);
        unset($data['listaexperiencia']);
        if ($idRef) {
            if ($data['id_experiencia'] != 0) {
                $where = $referencia->getAdapter()
                    ->quoteInto('id = ?', $idRef);
                $referencia->update($data, $where);
            }
        } else if ($data['id_experiencia'] == 0 && $data['nombre'] != '') {
            $valor = true;
        } else {
            if ($data['id_experiencia'] != 0 && $data['nombre'] != '' && $data['cargo']
                != '' && $data['telefono'] != '') $referencia->insert($data);
        }
    }

    public function pdfAction()
    {
        $slug = $this->_getParam('slug');
        try {

            if ($slug) {
                $htmlFilter = new Zend_Filter_HtmlEntities();
                $tagFilter  = new Zend_Filter_StripTags();

                $slug = $htmlFilter->filter($slug);

                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer->setNoRender();

                $domPdf = $this->_helper->getHelper('DomPdf');

                Zend_Layout::getMvcInstance()->assign(
                    'bodyAttr',
                    array(
                    'id' => 'profilePublic')
                );

                $headLinkContainer = $this->view->headLink()->getContainer();

                unset($headLinkContainer[0]);
                unset($headLinkContainer[1]);

                $postulante = $this->_postulante->getPostulantePerfil($slug);

                if (!$postulante) {
                    $this->_redirect('/');
                }

                $id         = $slug;
                $nombreFile = $this->_crearSlug($postulante, $id);
                $nombreFile = str_replace(' ', '-', $nombreFile).'.pdf';

                $id = $slug;

                $perfil                               = $this->_postulante->getPerfilPostulante($id);
                $perfil['postulante']['fotovariable'] = $perfil['postulante']['path_foto'];

                $this->view->postulante = $perfil;
                $this->view->headMeta()->appendHttpEquiv('Content-Type',
                    'text/html; charset=utf-8');
                $html                   = $this->view->render('mi-cuenta/pdf.phtml');
                //$html                   = $this->view->render('mi-cuenta/exporta-pdf-new.phtml');
                $mvc                    = Zend_Layout::getMvcInstance();
                $layout                 = $mvc->render('perfil_publico_pdf');

                //     $layout = str_replace("<!--perfil-->", $html, $layout);
                //   $layout = str_replace("\"", "'", $layout);
                //  var_dump($perfil);exit;
                // echo $html;exit;
                $flagDownload = $this->_getParam('download');
                if (isset($flagDownload) && $flagDownload == 1) {
                    $domPdf->descargarPDF($html, 'A4', "portrait", $nombreFile);
                } else {
                    $domPdf->mostrarPDF($html, 'A4', "portrait", $nombreFile);
                }
            } else {
                $this->_redirect('/');
            }
        } catch (Exception $exc) {
            // var_dump($exc->getMessage().'. '.$exc->getTraceAsString());exit;
            $this->log->log($exc->getMessage().'. '.$exc->getTraceAsString(),
                Zend_Log::ERR);
            $this->_redirect('/');
        }
    }

    private function _linkedinSaveIdioma(Zend_Config_Xml $linkedinUser,
                                         $idPostulante)
    {
        if (!empty($linkedinUser->languages->total)) {
            $modelIdioma = new Application_Model_DominioIdioma();
            $arrIdioma   = array();
            if ($linkedinUser->languages->total === '1') {
                $arrIdioma[] = $linkedinUser->languages->language->toArray();
            } else {
                foreach ($linkedinUser->languages->language as $idioma) {
                    $arrIdioma[] = $idioma->toArray();
                }
            }
            $languages = array(
                'elementary' => 'basico',
                'limited-working' => 'basico',
                'limited_working' => 'basico',
                'professional_working' => 'basico',
                'full_professional' => 'intermedio',
                'native_or_bilingual' => 'avanzado',
            );

            $arrIdioma['id'] = $idPostulante;
            $this->_helper->LogActualizacionBI->logActualizacionPostulanteIdioma($arrIdioma);
            unset($arrIdioma['id']);
            $inserted        = 0;
            foreach ($arrIdioma as $idioma) {
                $values = array(
                    'id_postulante' => $idPostulante
                );
                $evalue = $this->_compararIdioma($idioma['language']['name']);
                if ($evalue) {
                    $values['id_idioma'] = $evalue;
                    $nivelIdioma         = $idioma['proficiency']['level'];
                    if (isset($nivelIdioma) && !is_null($nivelIdioma) && !empty($nivelIdioma)) {
                        $level = in_array($idioma['proficiency']['level'],
                                $languages) ?
                            $languages[$idioma['proficiency']['level']] : 'basico';
                    } else {
                        $level = 'basico';
                    }

                    $values['nivel_lee']     = $level;
                    $values['nivel_escribe'] = $level;
                    $values['nivel_hablar']  = $level;
                    $inserted += $modelIdioma->insert($values);
                }
            }


            if ($inserted) {
                // $this->linkedImported->guardarDatos('idioma',$idPostulante);
            }
        }
    }

    private function _linkedinSaveEstudio(Zend_Config_Xml $linkedinUser,
                                          $idPostulante)
    {
        if (!empty($linkedinUser->educations->total)) {
            $modelEstudio = new Application_Model_Estudio();
            $arrEducacion = array();
            if ($linkedinUser->educations->total === '1') {
                $arrEducacion[] = $linkedinUser->educations->education->toArray();
            } else {
                foreach ($linkedinUser->educations->education as $educacion) {
                    $arrEducacion[] = $educacion->toArray();
                }
            }
            $inserted = 0;
            foreach ($arrEducacion as $educacion) {
                $values = array(
                    'id_postulante' => $idPostulante,
                    'nombre' => '',
                    'inicio_mes' => '12',
                    'pais_estudio' => 2533
                );
                if (isset($educacion['start-date']['year'])) {
                    $values['inicio_ano'] = $educacion['start-date']['year'];
                } else {
                    $values['inicio_ano'] = date('Y');
                }
                if (isset($educacion['end-date']['year'])) {
                    if ($educacion['end-date']['year'] <= date('Y')) {
                        $values['fin_ano'] = $educacion['end-date']['year'];
                    } elseif ($educacion['end-date']['year'] > date('Y')) {
                        $values['fin_ano']  = date('Y');
                        $values['en_curso'] = '1';
                    }
                }
                if (isset($educacion['degree'])) {
                    $values['id_nivel_estudio'] = $this->_compararNivelEstudio(
                        $educacion['degree']
                    );
                }
                if (isset($educacion['school-name'])) {
                    $evalueInstitucion          = $this->_compararInstitucion(
                        $educacion['school-name']
                    );
                    //$values['id_institucion'] = $evalueInstitucion['id'];
                    $values['otro_institucion'] = $evalueInstitucion['nombre'];
                }
                if (isset($educacion['field-of-study'])) {
                    $id_carrera = $this->_compararCarrera(
                        $educacion['field-of-study']
                    );

                    if (!empty($id_carrera)) {
                        $values['id_carrera'] = $id_carrera;
                    }
                }

                if (isset($values['id_carrera']) && (int) $values['id_carrera'] < 0) {
                    //var_dump('1',$educacion, $values);
                    continue;
                }

                if (isset($values['id_nivel_estudio']) && (int) $values['id_nivel_estudio']
                    < 0) {
                    //var_dump('2',$educacion, $values);
                    continue;
                }

                $inserted += $modelEstudio->insert($values);
            }


            if ($inserted) {
                //$this->linkedImported->guardarDatos('estudio',$idPostulante);
            }
        }
    }

    private function _linkedinSaveDatos(Zend_Config_Xml $linkedinUser,
                                        $idPostulante)
    {

        $date             = ('Y-m-d H:i:s');
        $valuesPostulante = array();
        //$valuesPostulante['nombres'] = (!empty($linkedinUser->{'first-name'}))?$linkedinUser->{'first-name'}:'';
        //$apellidos = $linkedinUser->{'last-name'};
        //$valuesPostulante['apellidos'] = $apellidos;
        //$arrApellidos = explode(' ', $apellidos,2);
        //$valuesPostulante['apellido_paterno'] = (!empty($arrApellidos[0]))?$arrApellidos[0]:'';
        //$valuesPostulante['apellido_materno'] = (!empty($arrApellidos[1]))?$arrApellidos[1]:'';
        if (!empty($linkedinUser->{'date-of-birth'})) {
            $year                          = $linkedinUser->{'date-of-birth'}->year;
            $month                         = $linkedinUser->{'date-of-birth'}->month;
            $day                           = $linkedinUser->{'date-of-birth'}->day;
            $valuesPostulante['fecha_nac'] = "$year-$month-$day";
        }

//          var_dump(($valuesPostulante));exit;
        if (!empty($linkedinUser->{'phone-numbers'}->total)) {
            if ($linkedinUser->{'phone-numbers'}->{'phone-number'}->{'phone-type'}
                === 'mobile') {
                $valuesPostulante['celular'] = $linkedinUser->{'phone-numbers'}->{'phone-number'}->{'phone-number'};
            } else {
                $valuesPostulante['telefono'] = $linkedinUser->{'phone-numbers'}->{'phone-number'}->{'phone-number'};
            }
        }
        $valuesPostulante['ultima_actualizacion'] = $date;

        $where                  = $this->_postulante->getAdapter()
            ->quoteInto('id = ?', $idPostulante);
        $actualizados           = $this->_postulante->update($valuesPostulante,
            $where);
        $valuesPostulante['id'] = $idPostulante;
        $this->_helper->LogActualizacionBI->logActualizacionPostulantePerfil($valuesPostulante);

        if ($actualizados > 0) {
            //  $this->linkedImported->guardarDatos('datos-personales',$idPostulante);
        }
    }

    private function _linkedinSaveLogro(Zend_Config_Xml $linkedinUser,
                                        $idPostulante)
    {
        /*
         * Este proceso aun no es estable y no es usado....
         */
        if (!empty($linkedinUser->{'honors-awards'}->total)) {
            $ModelLogros = new Application_Model_Logros();
            $arrLogro    = array();
            if ($linkedinUser->{'honors-awards'}->total === '1') {
                $arrLogro[] = $linkedinUser->{'honors-awards'}->{'honor-award'}->toArray();
            } else {
                foreach ($linkedinUser->{'honors-awards'}->{'honor-award'} as $logro) {
                    $arrLogro[] = $logro->toArray();
                }
            }

            foreach ($arrLogro as $logro) {
                $values                = array(
                    'id_postulante' => $idPostulante
                );
                $values['logro']       = $logro['name'];
                $values['institucion'] = $logro['issuer'];
                //$ModelLogros->insert($values);
            }
        }
    }

    private function _linkedinSaveExperiencia(Zend_Config_Xml $linkedinUser,
                                              $idPostulante)
    {
        if (!empty($linkedinUser->positions->total)) {
            $modelExperiencia = new Application_Model_Experiencia();
            $arrEmpresa       = array();
            if ($linkedinUser->positions->total === '1') {
                $arrEmpresa[] = $linkedinUser->positions->position->toArray();
            } else {
                foreach ($linkedinUser->positions->position as $empresa) {
                    $arrEmpresa[] = $empresa->toArray();
                }
            }

            $inserted = 0;
            foreach ($arrEmpresa as $empresa) {
                $values = array(
                    'id_postulante' => $idPostulante,
                    'id_nivel_puesto' => 10,
                    'id_area' => 26,
                    'id_puesto' => 1292
                );
                if (isset($empresa['company']['name'])) {
                    $values['otra_empresa'] = $empresa['company']['name'];
                } else {
                    $values['otra_empresa'] = '';
                }
                if (isset($empresa['company']['industry'])) {
                    $values['otro_rubro'] = $empresa['company']['industry'];
                }
                if (isset($empresa['title'])) {
                    $values['otro_puesto'] = $empresa['title'];
                }
                if (isset($empresa['start-date']['month'])) {
                    $values['inicio_mes'] = $empresa['start-date']['month'];
                } else {
                    $values['inicio_mes'] = date('n');
                }
                if (isset($empresa['start-date']['year'])) {
                    $values['inicio_ano'] = $empresa['start-date']['year'];
                } else {
                    $values['inicio_ano'] = date('Y');
                }
                if ($empresa['is-current'] == 'true') {
                    $values['en_curso'] = '1';
                    $values['fin_mes']  = date('n');
                    $values['fin_ano']  = date('Y');
                } else {
                    $values['en_curso'] = '0';
                    if (isset($empresa['end-date']['month'])) {
                        $values['fin_mes'] = $empresa['end-date']['month'];
                    } else {
                        $values['fin_mes'] = date('n');
                    }
                    if (isset($empresa['end-date']['year'])) {
                        $values['fin_ano'] = $empresa['end-date']['year'];
                    } else {
                        $values['fin_ano'] = date('Y');
                    }
                }
                if (isset($empresa['summary'])) {
                    $values['comentarios'] = substr($empresa['summary'], 0, 140);
                }
                $dataporcentaje = $this->_helper->LogActualizacionBI->logActualizacionPostulanteExperiencia($values);
                $inserted += $modelExperiencia->insert($values);
            }

            if ($inserted) {
                //  $this->linkedImported->guardarDatos('experiencia',$idPostulante);
            }
        }
    }

    public function misOtrosEstudiosAction()
    {

        $this->view->isAuth     = $this->isAuth;
        $session                = new Zend_Session_Namespace('linkedin');
        $this->view->showImport = false;
        Zend_Layout::getMvcInstance()->assign(
            'submenuMiCuenta', $this->_submenuMiCuenta
        );

//        if (isset($session->linkedin)) {
//            $this->view->showImport = true;
//            $this->getMessenger()->success(
//                "Se logró importar tus datos de linkedin. Por favor, ingresa o
//                selecciona los datos que no hayan coincidido exactamente con
//                los de APTiTUS."
//            );
//            $linkedinData = $session->linkedin;
//            $this->_linkedinSaveDatos($linkedinData, $this->idPostulante);
//            unset($session->linkedin);
//        }
        $idPostulante       = $this->idPostulante;
        $formOtroEstudio    = new Application_Form_PostulanteOtroEstudio();
        $otroEstudio        = new Application_Model_Estudio();
        $arrayOtrosEstudios = $otroEstudio->getOtrosEstudios($idPostulante);

        $this->view->formOtroEstudio  = $formOtroEstudio;
        $this->view->lisOtrosEstudios = $arrayOtrosEstudios;
        $this->view->slug             = $this->auth['postulante']['slug'];
    }

    public function misOtrosEstudiosAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idPostulante = isset($this->auth['postulante']['id']) ? $this->auth['postulante']['id']
                : 0;

        $XSS  = new App_Util();
        $data = $this->_getAllParams();

        $formEstudio = new Application_Form_PostulanteOtroEstudio();
        $isValidForm = $formEstudio->isValid($data);

        if ($isValidForm && $idPostulante) {
            $db = $this->getAdapter();
            $db->beginTransaction();
            try {


                $ResquestEstudio = $this->_actualizarPostulanteOtroEstudio($data,
                    $idPostulante);
                $id_estudio      = $ResquestEstudio['id'];
                $helper          = $this->_helper->getHelper('RegistrosExtra');
                $helper->ActualizarPostulacion($idPostulante);
                $helper->ActualizarEstudios($idPostulante);

                $data['hidOtherStudy']       = $id_estudio;
                $data['selOtherMonthBegins'] = App_Util::setMonth($data['selOtherMonthBegins']);
                if (isset($data['selOtherMonthEnd'])) {
                    $data['selOtherMonthEnd'] = App_Util::setMonth($data['selOtherMonthEnd']);
                    $data['actuallyStudying'] = 0;
                } else {
                    $est                      = new Application_Model_Estudio();
                    $estudio                  = $est->getEstudioXId($id_estudio);
                    $data['actuallyStudying'] = $estudio[0]["en_curso"];
                }

                $formEstudio->getElement('hidToken')->initCsrfToken();
                $response['token']   = $formEstudio->getElement('hidToken')->getValue();
                $response['status']  = 1;
                $response['message'] = $ResquestEstudio['mensaje'];
                $response['skill']   = $data;
                $datos['id']         = $idPostulante;
                $db->commit();
                $solrAdd             = new Solr_SolrPostulante();
                $solrAdd->add($idPostulante);
                $dataporcentaje = $this->_helper->LogActualizacionBI->logActualizacionPostulanteOtrosEstudios($datos);
                unset($datos['id']);
                if ($data['hidOtherStudy'] == 0) {
                    try {
                        $response['percent']     = $dataporcentaje['total_completado'];
                        $response['iscompleted'] = array(
                            'Otros Estudios',
                            1);
                    } catch (Exception $ex) {
                        throw new Zend_Exception('Porcentajes Estudios del Postulante: '.$ex->getMessage());
                    }
                } else {
                    $response['percent']     = $dataporcentaje['total_completado'];
                    $response['iscompleted'] = array(
                        'Otros Estudios',
                        1);
                }
            } catch (Zend_Exception $exc) {
                $this->log->log($exc->getMessage().'. '.$exc->getTraceAsString(),
                    Zend_Log::ERR);
                $response['percent'] = 0;
                $response            = array(
                    'status' => '0',
                    'message' => $exc->getMessage()
                );
            } catch (Exception $exc) {
                $db->rollBack();
                $this->log->log($exc->getMessage().'. '.$exc->getTraceAsString(),
                    Zend_Log::ERR);
                $response = array(
                    'status' => '0',
                    'message' => $exc->getMessage() //'No se pudo realizar la operación, por favor vuelva a intentarlo.'
                );
            }
        } else {
            $msg = '';
            foreach ($formEstudio->getErrors() as $key => $value) {
                if (count($value) > 0 && $key != 'hidToken') {
                    for ($i = 0; $i < count($value); $i++) {
                        // Application_Form_PostulanteDatosPersonales
                        $a    = htmlentities(Application_Form_MisEstudiosPostulante::$errors[$value[$i]],
                            ENT_HTML401, 'UTF-8');
                        $data = array(
                            $key => Zend_Json::encode($a));
                    }
                    $msg.= Zend_Json::encode($data);
                }
            }
            $formEstudio->getElement('hidToken')->initCsrfToken();
            $response = array(
                'status' => '0',
                'message' => $msg,
                'token' => $formEstudio->getElement('hidToken')->getValue()
            );
        }
        $this->_response->appendBody(Zend_Json::encode($response));
    }

    /**
     * Guarda los datos del formulario de Estudio
     * @param array $data Datos a guardar
     * @param int $idPostulante Id del postulante
     * @return int
     */
    private function _actualizarPostulanteOtroEstudio($data, $idPostulante)
    {
        $instituciones      = new Application_Model_Institucion();
        $estudio            = new Application_Model_Estudio();
        $listaInstituciones = $instituciones->getInstituciones();

        $idEstudio                                = 0;
        $valOtherEstudio                          = array();
        $valOtherEstudio['id_postulante']         = $idPostulante;
        $valOtherEstudio['id_nivel_estudio']      = Application_Model_NivelEstudio::OTRO_ESTUDIO;
        $valOtherEstudio['otro_estudio']          = $data['txtOtherName'];
        $valOtherEstudio['interrumpidos']         = '0';
        $valOtherEstudio['id_nivel_estudio_tipo'] = $data['selOtherType'];
        $valOtherEstudio['pais_estudio']          = $data['selOtherCountry'];
        $valOtherEstudio['inicio_mes']            = $data['selOtherMonthBegins'];
        $valOtherEstudio['inicio_ano']            = $data['txtOtherYearBegins'];

        $busq_inst                           = array_search(trim($data['txtOtherInstitution']),
            $listaInstituciones);
        $id_institucion                      = ($busq_inst === false) ? null : $busq_inst;
        $valOtherEstudio['id_institucion']   = $id_institucion;
        $valOtherEstudio['otro_institucion'] = $data['txtOtherInstitution'];

        if (isset($data['selOtherMonthEnd']) && isset($data['txtOtherYearEnd'])) {
            $valOtherEstudio['fin_mes']  = $data['selOtherMonthEnd'];
            $valOtherEstudio['fin_ano']  = $data['txtOtherYearEnd'];
            $valOtherEstudio['en_curso'] = 0;
        } else {
            $valOtherEstudio['fin_mes']  = null;
            $valOtherEstudio['fin_ano']  = null;
            $valOtherEstudio['en_curso'] = 1;
        }

        if ($data['hidOtherStudy'] != 0) {
            $where     = $estudio->getAdapter()
                    ->quoteInto('id_postulante = ?', $idPostulante).
                    $estudio->getAdapter()
                    ->quoteInto(' and id = ?', $data['hidOtherStudy']);
            $estudio->update($valOtherEstudio, $where);
            $idEstudio = $data['hidOtherStudy'];
            $mensaje   = $this->_messageSuccessActualizar;
        } else {
            $idEstudio = $estudio->insert($valOtherEstudio);
            $mensaje   = $this->_messageSuccessRegistrar;
        }


//        $estudioModelo    = new Application_Model_Estudio;
//        $estudioPrincipal = $estudioModelo->obtenerEstudiosMayorPesoPorPostulante($idPostulante);
//
//        if (!empty($estudioPrincipal)) {
//            $estudioModelo->actualizarEstudioPrincipal($idPostulante,
//                $estudioPrincipal['id']);
//        }

        $postulante = new Application_Model_Postulante();
        $where      = $postulante->getAdapter()->quoteInto('id = ?',
            $idPostulante);
        $postulante->update(
            array(
            'ultima_actualizacion' => date('Y-m-d H:i:s'),
            'last_update_ludata' => date('Y-m-d H:i:s')
            ), $where
        );
        return array(
            'id' => $idEstudio,
            'mensaje' => $mensaje);
    }

    public function getDataOtrosEstudiosAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $estudios = new Application_Model_Estudio();
        $id       = $this->_getParam('id');
        $tok      = $this->_getParam('csrfhash');

        $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());
        $requestValido = ($requestValido && $id && $tok);
        $data          = array();
        if (!$requestValido) {
            $data = array(
                'status' => 0,
                'messages' => $this->_messageError);
        }

        $filter                               = new Zend_Filter();
        $filter->addFilter(new Zend_Filter_StripTags());
        $id                                   = $filter->filter($id);
        $data['status']                       = 1;
        $data['messages']                     = $this->_messageSuccess;
        $lisEstudio                           = $estudios->getEstudioXId($id);
        $data['skill']['hidOtherStudy']       = $lisEstudio[0]['id'];
        $data['skill']['txtOtherName']        = $lisEstudio[0]['otro_estudio'];
        $data['skill']['selOtherType']        = $lisEstudio[0]['id_nivel_estudio_tipo'];
        $data['skill']['txtOtherInstitution'] = $lisEstudio[0]['otro_institucion'];
        $data['skill']['selOtherCountry']     = $lisEstudio[0]['pais_estudio'];
        $data['skill']['selOtherMonthBegins'] = $lisEstudio[0]['inicio_mes'];
        $data['skill']['txtOtherYearBegins']  = $lisEstudio[0]['inicio_ano'];
        $data['skill']['actuallyStudying']    = $lisEstudio[0]['en_curso'];
        if ($lisEstudio[0]['en_curso'] == 1) {
            $staDsb                            = new stdClass();
            $staDsb->disabled                  = TRUE;
            $data['skill']['selOtherMonthEnd'] = $staDsb;
            $data['skill']['txtOtherYearEnd']  = $staDsb;
        } else {
            $data['skill']['selOtherMonthEnd'] = $lisEstudio[0]['fin_mes'];
            $data['skill']['txtOtherYearEnd']  = $lisEstudio[0]['fin_ano'];
        }
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    /**
     * Elimina otros estudios de un postulante y actualiza en la tabla postulante
     * la ultima acutalizacion realizada
     * @return true si es true esta eliminado el registro y si es falso no
     */
    private function _eliminarOtroEstudioPostulante($idOtroEstudio)
    {
        if ($idOtroEstudio) {
            $estudio    = new Application_Model_Estudio();
            $where      = array(
                'id=?' => $idOtroEstudio);
            $r          = (bool) $estudio->delete($where);
            $postulante = new Application_Model_Postulante();
            $where      = $postulante->getAdapter()
                ->quoteInto('id = ?', $this->idPostulante);
            $postulante->update(
                array(
                'ultima_actualizacion' => date('Y-m-d H:i:s'),
                'last_update_ludata' => date('Y-m-d H:i:s')), $where
            );
        } else {
            $r = false;
        }
        return $r;
    }

    /**
     * Retorna lista de países para autocomplete
     * @return type json
     */
    public function filtrarPaisAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $valPais = trim($this->_getParam('value', ''));
        $token   = $this->_getParam('csrfhash');

        $requestValido = ($this->getRequest()->isPost() &&
            $this->getRequest()->isXmlHttpRequest());
        $paramsValido  = ($requestValido && $valPais && $token);
        if ($requestValido && $paramsValido) {
            $data = array(
                'id' => 0,
                'mostrar' => 'No se encontraron resultados'
            );
            if ($this->_hash->isValid($token) && strlen($valPais) >= 3) {
                $ubigeo  = new Application_Model_Ubigeo();
                $filter  = new Zend_Filter_StripTags();
                $value   = strtolower($valPais);
                $valPais = $filter->filter($value);
                $data    = $ubigeo->getPaisByName($valPais);
            } else {
                $data = array(
                    'id' => 0,
                    'mostrar' => 'No se encontraron resultados'
                );
            }
            $this->_response->appendBody(Zend_Json::encode($data));
        }
    }

    /**
     * Filtrara programas de solr
     * @return type json
     * */
    public function filtrarProgramasAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $programa = $this->_getParam('value');
        $tok      = $this->_getParam('csrfhash');

        $requestValido = ($this->getRequest()->isPost() &&
            $this->getRequest()->isXmlHttpRequest());
        $requestValido = ($requestValido && $programa);

        if ($requestValido) {
            $filter = new Zend_Filter();
            $filter->addFilter(new Zend_Filter_StripTags());

            $programa = $filter->filter($programa);
            $programa = strtolower($programa);

            $solr  = new Solr_SolrPrograma();
            $items = $solr->getProgramaByName($programa);
            $data  = array(
                'status' => '1',
                "messages" => "Sus fueron encontrados.",
                'items' => $items
            );
            $this->_response->appendBody(Zend_Json::encode($data));
        } else {
            $data = array(
                'status' => '0',
                "messages" => "No se encontraron resultados",
            );
            $this->_response->appendBody(Zend_Json::encode($data));
        }
    }

    /**
     * Actualizando referencias
     * @return type json
     */
    public function referenciaAjaxAction()
    {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $allParams                 = $this->_getAllParams();
            $idP                       = $this->idPostulante;
            $hidReference              = '';
            $ModeloReferencia          = new Application_Model_Referencia();
            $formReferencia            = new Application_Form_Paso2ReferenciaNew($idP,
                false);
            $allParams['hidReference'] = isset($allParams['hidReference']) ? $allParams['hidReference']
                    : 0;
            $tok                       = $this->_getParam('csrfhash');
            $data                      = array(
                'status' => 0,
                "message" => $this->_messageFail,
                'iscompleted' => array(
                    'Referencia',
                    1
                ),
                'percent' => 0
            );
            $requestValido             = ($this->getRequest()->isPost() &&
                $this->getRequest()->isXmlHttpRequest());

            if ($requestValido) {
                if ($formReferencia->isValid($allParams)) {
                    if ($allParams['hidReference']) {
                        $idReference     = $ModeloReferencia->updateReferencia($allParams);
                        $hidReference    = (int) $allParams['hidReference'];
                        $data['message'] = $this->_messageSuccessActualizar;
                    } else {
                        $data['message'] = $this->_messageSuccessRegistrar;
                        $hidReference    = $ModeloReferencia->insetReferencia($allParams);
                    }
                    $data['status'] = 1;

                    unset($allParams['controller']);
                    unset($allParams['action']);
                    unset($allParams['module']);
                    $skill['hidReference']               = $hidReference;
                    $skill['selCareReference']           = $formReferencia->selCareReference->getMultiOption($allParams['selCareReference']);
                    $formReferencia->getElement('hidToken')->initCsrfToken();
                    $skill['txtNameReference']           = $allParams['txtNameReference'];
                    $skill['txtPositionReference']       = $allParams['txtPositionReference'];
                    $skill['txtTelephoneReferenceOne']   = $allParams['txtTelephoneReferenceOne'];
                    $skill['txtTelephoneReferenceTwo']   = $allParams['txtTelephoneReferenceTwo'];
                    $skill['txtTelephoneReferenceEmail'] = $allParams['txtTelephoneReferenceEmail'];
                    $data['token']                       = $formReferencia
                            ->getElement('hidToken')->getValue();
                    unset($allParams['hidToken']);
                    $data['skill']                       = $skill;
                } else {
                    $formReferencia->getElement('hidToken')->initCsrfToken();
                    $data['status']  = 0;
                    $data['token']   = $formReferencia
                            ->getElement('hidToken')->getValue();
                    $mensajes        = "Intente nuevamente";
                    $data['message'] = $formReferencia
                        ->setErrosReferencias($mensajes);
                }
            }
        } catch (Exception $exc) {
            $idP            = $this->idPostulante;
            $formReferencia = new Application_Form_Paso2ReferenciaNew($idP,
                false);
            $formReferencia->getElement('hidToken')->initCsrfToken();
            $mensaje        = $exc->getMessage().'.'.$exc->getTraceAsString();
            $this->log->log($mensaje, Zend_Log::ERR);
            $data           = array(
                'status' => 0,
                "message" => "Vuelva a intentarlo."
            );
            $data['token']  = $formReferencia->getElement('hidToken')->getValue();
        }
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    /**
     * Metodo ajax de eliminar referencia
     * @return type json
     */
    public function eliminarReferenciaAction()
    {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $allParams        = $this->_getAllParams();
            $tok              = $this->_getParam('token');
            $ModeloReferencia = new Application_Model_Referencia();
            $requestValido    = ($this->getRequest()->isPost() &&
                $this->getRequest()->isXmlHttpRequest());
            $data             = array(
                'status' => 1,
                "message" => $this->_messageFail,
                'iscompleted' => array(
                    'Referencia',
                    1
                ),
                'percent' => 0
            );
            if ($requestValido) {
                if ($this->_hash->isValid($tok)) {
                    $where           = $this->getAdapter()
                        ->quoteInto('id IN (?)', (int) $allParams['id']);
                    $delete          = $ModeloReferencia->delete($where);
                    $data['status']  = 1;
                    $data['message'] = "Sus datos se eliminaron con éxito.";
                    unset($allParams['controller']);
                    unset($allParams['action']);
                    unset($allParams['module']);
                }
            }
        } catch (Exception $exc) {
            $error = $exc->getMessage().'.'.$exc->getTraceAsString();
            $this->log->log($error, Zend_Log::ERR);
            $data  = array(
                'status' => 0,
                "message" => "Vuelva a intentarlo."
            );
        }
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    /**
     * Metodo que obtine items para el formulario
     * @return json listado del formulario
     */
    public function getReferenciasAjaxAction()
    {
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $allParams        = $this->_getAllParams();
            $id               = (int) $allParams['id'];
            $ModeloReferencia = new Application_Model_Referencia();
            $tok              = $this->_getParam('csrfhash');
            $data             = array(
                'status' => 0,
                'message' => $this->_messageError);
            if ($this->_hash->isValid($tok)) {
                $data['status']  = 1;
                $data['message'] = $this->_messageSuccess;
                $data['skill']   = $ModeloReferencia->getFormReferencia($id);
            }
        } catch (Exception $exc) {
            $error = $exc->getMessage().'.'.$exc->getTraceAsString();
            $this->log->log($error, Zend_Log::ERR);
            $data  = array(
                'status' => 0,
                'message' => $this->_messageError);
        }
        $this->_response->appendBody(Zend_Json::encode($data));
    }
}
