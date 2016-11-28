<?php

class Postulante_AvisoController extends App_Controller_Action_Postulante
{
    protected $_slug;
    protected $_empresaslug;
    protected $_ubicacionslug;
    protected $_urlId;
    protected $_aviso;
    protected $_avisoId;
    protected $_urlAviso;
    protected $_creado;
    protected $_postulante;
    protected $_cache;

    public function init()
    {
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'jobPost')
        );
        parent::init();
        $this->_cache         = Zend_Registry::get('cache');
        $this->_slug          = $this->_getParam('slug');
        $this->_urlId         = $this->_getParam('url_id');
        $params               = explode("--", $this->_slug);
        $this->_slug          = isset($params[0]) ? $params[0] : '';
        $this->_empresaslug   = isset($params[1]) ? $params[1] : '';
        $this->_ubicacionslug = isset($params[2]) ? $params[2] : '';

        $a                 = new Application_Model_AnuncioWeb();
        $this->_postulante = new Application_Model_Postulante;

        $dataAviso      = $a->getAvisoIdByUrl($this->_urlId);
        $this->_avisoId = $dataAviso['id'];
        $this->_creado  = $dataAviso['creado_por'];

        $url             = $this->view->url(
            array(
            'url_id' => $this->_urlId,
            'empresaslug' => $this->_empresaslug,
            'ubicacionslug' => $this->_ubicacionslug,
            'slug' => $this->_slug
            ), 'aviso_detalle', true
        );
        $this->_urlAviso = $url;
        $this->view->url = $url;
    }

    public function indexAction()
    {
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/static/js/aviso.empleo.js')
        );
        $this->view->menu_sel = self::MENU_AVISOS;
    }

    /**
     * Lucene Ad: Esta vista se usa para generar el documento que se indexará con Zend_Lucene
     */
    public function ladAction()
    {
        $this->_helper->layout->setLayout('lad');
        $this->getRequest()->setParam('esVistaLucene', true);
        $this->_forward('ver');
    }

    /**
     * Redireccion de avisos viejos a nueva URL (SEO)
     */
    public function redirAction()
    {
        $slug  = $this->_getParam('slug');
        $urlid = $this->_getParam('url_id');
        $this->_redirect(
            $this->view->url(
                array('slug' => $slug, 'url_id' => $urlid), 'aviso', true
            )
        );
    }

    public function verAction()
    {

        $esResultado = $this->_getParam('resultado') == 'true';
        $sess        = $this->getSession();
        $facebook    = $this->_helper->AuthFacebook->Ulrlogin();
        $this->_helper->AuthFacebook->setUrlReturn($_SERVER['REDIRECT_URL']);
        Zend_Layout::getMvcInstance()->assign('facebook', $facebook);
        if (isset($sess->lastSearchResultsUrl) && $esResultado) {
            $this->view->searchResultsUrl = $sess->lastSearchResultsUrl;
            $sess->micuentaUrl            = null;
        } else {
            $this->view->searchResultsUrl = '';
            $this->view->micuentaUrl      = $sess->micuentaUrl;
        }

        $updateCV           = true;
        $idPostulante       = '';
        $this->view->module = $this->_request->getModuleName();
        $usuario            = $this->auth['usuario'];
        if ($this->auth != null && array_key_exists('postulante', $this->auth)) {
            $postulante   = $this->auth['postulante'];
            $idPostulante = $this->auth['postulante']['id'];
            $updateCV     = $this->_postulante->hasDataForApplyJobSession($this->auth['postulante']);
        } elseif ($this->auth != null && array_key_exists('empresa', $this->auth)) {
            $postulante = $this->auth['empresa'];
        } else {
            $postulante = null;
        }
        $this->view->auth = isset($this->auth) ? $this->auth : null;

        $a             = new Application_Model_AnuncioWeb();
        $empresaActivo = new Application_Model_Empresa();

        $empresa      = new Application_Model_EmpresaLookAndFeel();
        $this->_aviso = $a->getAvisoInfoficha($this->_avisoId);

        if ($this->_aviso['redireccion'] == 1) {
            if ($this->_aviso['slug'] != $this->_slug) {
                $nuevaUrl = 'ofertas-de-trabajo/'.$this->_aviso['slug'].'-'.$this->_aviso['url_id'];
                $this->_redirect($nuevaUrl, array('code' => 301));
            }
        }

        if ($this->_aviso == null) {
            throw new Zend_Controller_Action_Exception('Error no se encontró!',
            404);
            //$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
            //$this->_redirect('error/page404');
        }

        $nombreEmpresa = $this->_aviso['nombre_comercial'];
        if ($this->_aviso['mostrar_empresa'] != "0") {
            $nombreEmpresa = $this->_aviso['nombre_empresa'];
        }

        //------------------------------------------------------
        $this->_aviso['fecha'] = date("d")." de ".strtolower(App_Util::setMonth(date("m")))." del ".date("Y");
        $this->view->HeadMetas()->setTitle(
            ucwords(strtolower($this->_aviso['puesto'])).' en '.
            $this->_aviso['empresa_rs'].' - '.
            $this->_aviso['ubigeo_nombre'].' - '.
            $this->_aviso['fecha'].' | AquiEmpleos'
        );

        $this->view->headMeta()->appendName(
            "Keywords",
            ucwords(strtolower($this->_aviso['puesto'])).', '.$this->_aviso['empresa_rs'].', '.
            $this->_aviso['ubigeo_nombre'].', oferta de trabajo'
        );

        $this->_aviso['nombre_empresa'] = $nombreEmpresa;
        // $servicioOpenGraph = new App_Service_SEO_OpenGraph($this->view);
        //   $servicioOpenGraph->add($this->_aviso);

        $description = 'Trabaja como '.ucwords(strtolower($this->_aviso['puesto'])).' en '.
            $this->_aviso['empresa_rs'].', '.$this->_aviso['ubigeo_nombre'].
            '. Publicado el '.$this->_aviso['fecha'].
            '. Más opciones relacionadas en aquiempleos.com.';

        $this->view->headMeta()->appendName(
            "Description", $description
        );

        $config                     = $this->getConfig();
        $verLogoDefecto             = (bool) $config->defaultLogoEmpresa->enabled;
        $this->view->verLogoDefecto = $verLogoDefecto;
        $this->view->LogoDefecto    = 'icon-empresa-blank.png';

        $acceso                        = Application_Model_AnuncioWeb::accesoAnuncio($this->_aviso['id_empresa'],
                $this->auth);
        $this->view->acceso            = $acceso;
        $this->view->aviso             = $this->_aviso;
        $this->view->online            = true;
        $this->view->menu_sel          = self::MENU_AVISOS;
        $this->view->anunciosSugeridos = array();
        $this->view->lookAndFeel       = false;
        $tipoRolEmpresa                = 1;
        $LookAndFeelColors             = $empresa->getLookAndFeelActivo($this->_aviso['id_empresa']);

        $this->_aviso["mostra_dicenio"] = $empresaActivo->LooFeelActivo($this->_aviso['id_empresa']);
        if (isset($this->auth['usuario'])) {
            if ($this->auth['usuario']->rol == 'postulante') {
                $tipoRolEmpresa = 0;
            }
        }
        if ($this->_aviso["mostra_dicenio"] && $this->_aviso["mostrar_empresa"] == '1'
            && $LookAndFeelColors) {
            $tipoRolEmpresa          = 1;
            $this->view->lookAndFeel = 1;
        }
        $this->view->tipoRE               = $tipoRolEmpresa;
        $this->view->LookAndFeelColors    = false;
//        if($tipoRolEmpresa) {
//            if($this->view->lookAndFeel) {
//                $baseUrl = $config->s3->app->elementsBannersUrl;
//                $LookAndFeelColors['banner'] = $baseUrl . '' . $LookAndFeelColors['banner'];
//                $LookAndFeelColors['banner_alta'] = $baseUrl . '' . $LookAndFeelColors['banner_alta'];
//                $LookAndFeelColors['img_seccion'] = !empty($LookAndFeelColors['img_seccion']) ? $baseUrl . '' . $LookAndFeelColors['img_seccion'] : null;
//                Zend_Layout::getMvcInstance()->assign(
//                        'lookAndFeel', $this->view->lookAndFeel
//                );
//                Zend_Layout::getMvcInstance()->assign(
//                        'LookAndFeelColors', $LookAndFeelColors
//                );
//                $this->view->LookAndFeelColors = $LookAndFeelColors;
//                $solr = new Solr_SolrAviso();
//                $anunciosRel = $solr->getEmpresaAvisos(array('id_empresa' => $this->_aviso['id_empresa'], 'id_aviso' => $this->_aviso['id']), 10);
//                $this->view->anunciosRelacionados = $anunciosRel;
//            } else {
//                $solr = new Solr_SolrAviso();
//                $anunciosRel = $solr->getAvisoRelacionadosnew($this->_aviso, 10);
//                $this->view->anunciosRelacionados = $anunciosRel;
//            }
//        } else {
//            if(isset($this->auth['postulante'])) {
//                $solr = new Solr_SolrSugerencia();
//                $data=$solr->getListadoAvisosSugeridos(array(
//                    'id_postulante' => $this->auth['postulante']['id'],
//                    'page' => 1,
//                    'excluir_empresa' => $this->_aviso['id_empresa']
//                ));
//            $this->view->anunciosRelacionados =isset( $data['data'])? $data['data']:array();
//            }
//        }
//
        $solr                             = new Solr_SolrAviso();
        $anunciosRel                      = $solr->getAvisoRelacionadosnew($this->_aviso,
            10);
        $this->view->anunciosRelacionados = $anunciosRel;
        /**
         * Validar bien esta lógica: son estos escenarios:
         * - El Aviso no existe: se debe enviar a una pag 404
         * - El aviso existe pero es borrador, y nunca se llegó a publicar:
         *   se debe enviar a una pag 404
         * - El Aviso existe y estará online: se debe ver el aviso
         * - El Aviso estuvo online, pero ya venció: Se debe mostrar un yellow box:
         *   Este aviso ya ha expirado.
         * - El Aviso estuvo online, pero fue dado de baja: Se debe mostrar un yellow box:
         *   Este aviso ya ha expirado.
         */
        $compartirAviso                   = new Application_Form_CompartirPorMail();
        $compartirAviso->setAction($this->_urlAviso.'/compartir');

        $cuestionario = new Application_Model_Cuestionario();
        $preguntas    = $cuestionario->getPreguntasByAnuncioWeb($this->_aviso['id']);
        if (count($preguntas) > 0) {
            $this->view->cuestionario = true;
        } else {
            $this->view->cuestionario = false;
        }


        Zend_Layout::getMvcInstance()->assign(
            'question', ($this->view->cuestionario) ? true : 0
        );

        if (isset($usuario)) {
            $compartirAviso->correoEmisor->setValue($usuario->email);
            if (array_key_exists('postulante', $this->auth)) {
                $compartirAviso->nombreEmisor->setValue(
                    $postulante['nombres'].' '.$postulante['apellidos']
                );
            } else {
                $compartirAviso->nombreEmisor->setValue(
                    $postulante['razon_social']
                );
            }



            $urlQuestion = '/postular';


            if ($this->view->cuestionario) {
                $this->view->cuestionario = true;
                $formCuestionario         = new Application_Form_Cuestionario();
                $formCuestionario->setAction($this->_urlAviso.$urlQuestion);

                $e = new Zend_Form_Element_Image('imgLogo');
                if ($this->_aviso['mostrar_empresa'] != 0 && ($this->_aviso['logo_empresa']
                    != "" || $this->_aviso['logo_empresa'] != null)) {
                    $e->setAttribs(array(
                        'src' => ELEMENTS_URL_LOGOS.$this->_aviso['logo_empresa'],
                        'alt' => $this->_aviso['nombre_empresa'],
                        'title' => $this->_aviso['nombre_empresa']
                    ));
                }
                $formCuestionario->addElement($e);


                for ($i = 0; $i < count($preguntas); $i++) {
                    $e = new Zend_Form_Element_Textarea('pregunta_'.$preguntas[$i]['id']);
                    $e->setLabel($preguntas[$i]['pregunta']);
                    $formCuestionario->addElement($e);
                }

                if ($esResultado) {
                    $e = new Zend_Form_Element_Hidden('esResultado');
                    $e->setValue('true');
                    $formCuestionario->addElement($e);
                }

                Zend_Layout::getMvcInstance()->assign(
                    'formCuestionario', $formCuestionario
                );
            } else {
                $this->view->cuestionario = false;
            }
        }


        // var_dump($this->view->cuestionario);exit;
        Zend_Layout::getMvcInstance()->assign(
            'compartirPorMail', $compartirAviso
        );
        Zend_Layout::getMvcInstance()->assign(
            'url_id', $this->_urlId
        );
        Zend_Layout::getMvcInstance()->assign(
            'modalInfoUpdatePerfil', !($updateCV) ? true : false
        );

        Zend_Layout::getMvcInstance()->assign(
            'empresa', $nombreEmpresa
        );
        $idPost = isset($postulante['id']) ? $postulante['id'] : null;
        if (isset($this->auth['postulante'])) {
            Zend_Layout::getMvcInstance()->assign(
                'newCompleteRecord',
                new Application_Form_RegistroComplePostulante($idPost,
                $this->auth['postulante'])
            );
        }

        $p                        = new Application_Model_Postulacion();
        $this->view->hasPostulado = $p->hasPostulado($this->_avisoId,
            $postulante['id']);


        //  $solr                     = new Solr_SolrSugerencia();
        //$favs                     = $solr->getAnunciosfavoritos($postulante['id']);
        $favs                     = array();
        $esFav                    = array_search($this->_aviso['id'], $favs) !== FALSE;
        // variable para determinar si un postulante esta logeado
        $this->view->ispostulante = true;
        if (isset($this->auth['postulante'])) {
            $this->view->ispostulante = true; // (isset($this->auth['usuario']->rol) && $this->auth['usuario']->rol != 'postulante' && $this->module == 'postulante') ? true : false;
        }
        if (isset($this->auth['empresa'])) {
            $this->view->ispostulante = false; // (isset($this->auth['usuario']->rol) && $this->auth['usuario']->rol != 'postulante' && $this->module == 'postulante') ? true : false;
        }
        //variable para determinar si un aviso es siego
        $this->view->isNotAvisoCiego = ($this->_aviso['mostrar_empresa'] != "0" &&
            ($this->_aviso['logo_empresa'] != "" || $this->_aviso['logo_empresa']
            != null)) ? true : false;
        $this->_aviso['esFav']       = $esFav;
        $this->view->esFav           = $esFav;
        $this->view->destacado       = isset($postulante['destacado']) ? $postulante['destacado']
                : null;
        $this->view->twitterMessage  = $config->shared->twitter->message;
        $this->view->slug            = $this->_slug;
        $this->view->urlId           = $this->_urlId;
        $this->view->urlAviso        = $this->_urlAviso;
        $this->view->slugArea        = $this->_aviso['area_puesto_slug'];
        $this->view->slugNivel       = $this->_aviso['nivel_puesto_slug'];
        $this->view->updateCV        = !($updateCV) ? true : false;
        $this->view->postulante      = isset($this->auth['postulante']) ? $this->auth['postulante']['id']
                : 'no-logeado';


        /// Verificamos que no haya despostulado del aviso antes.
        $this->view->hasDesPostulado = false;
        if (isset($this->auth['postulante'])) {
            /* try {
              $despostular                 = new Mongo_DesPostulacion();
              $haDespostulado              = $despostular->hasDespostulacion($this->auth['postulante']['id'],
              $this->_aviso['id']);
              $this->view->hasDesPostulado = $haDespostulado ? true : false;
              } catch (Exception $ex) {

              } */
        }


        /**
         * @todo upgrade code
         */
        $this->view->seo = ''; // $servicioOpenGraph->_getMetas($this->_aviso);
        if ($this->_getParam('esVistaLucene', false)) {
            $this->_helper->viewRenderer->setScriptAction('ver-lucene');
        }

        $area_puesto_slug = isset($this->_aviso['area_puesto_slug']) ? $this->_aviso['area_puesto_slug']
                : '';
        Zend_Layout::getMvcInstance()->assign('slug_area', $area_puesto_slug);

        /*  */
        try {
            $busqueda = new Mongo_Aviso();
            $datos    = array(
                'auth' => isset($this->auth) ? $this->auth : null,
                'aviso' => $this->_aviso
            );
            $busqueda->save($datos);
        } catch (Exception $e) {
            
        }

        $this->view->moneda = $config->app->moneda;

        //  $this->_helper->viewRenderer('ver-default');
    }

    public function compartirAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
            $dataPost = $this->_getAllParams();

            $txtEmail      = $this->_getParam('txtEmail');
            $txtName       = $this->_getParam('txtName');
            $txtMessage    = $this->_getParam('txtMessage');
            $txtSenderName = $this->_getParam('txtSenderName');

            if ($txtEmail && $txtName && $txtMessage && $txtSenderName) {

                $txtEmail      = filter_var($txtEmail, FILTER_SANITIZE_EMAIL);
                $txtName       = filter_var($txtName, FILTER_SANITIZE_STRING);
                $txtMessage    = filter_var($txtMessage, FILTER_SANITIZE_STRING);
                $txtSenderName = filter_var($txtSenderName,
                    FILTER_SANITIZE_STRING);

                try {
                    $this->_helper->Mail->compartirAnuncioWeb(array(
                        'to' => $dataPost['txtEmail'],
                        'nombreReceptor' => $dataPost['txtName'],
                        'nombreEmisor' => $dataPost['txtSenderName'],
                        'mensajeCompartir' => $dataPost['txtMessage'],
                        'avisoUrl' => SITE_URL.$this->_urlAviso
                    ));

                    $response = array(
                        'status' => 1,
                        'msg' => 'Envio de correo exitoso'
                    );
                } catch (Exception $ex) {
                    $response = array(
                        'status' => 0,
                        'msg' => 'Envio de correo fallido'
                    );
                }
            }
        }

        $this->_response->appendBody(Zend_Json::encode($response));
    }

    public function postularAction()
    {
        if ($this->_getParam('es-resultado') == 'true') {
            $this->_urlAviso = $this->_urlAviso.'/resultado';
        }


        //Si se pierde la sesión al postular, vuelve a pedir que se logueé.
        if ($this->auth == null) {
            $this->getMessenger()->error('Error: Al iniciar sessión.');
            $this->_redirect($this->_urlAviso.'#loginP');
        }


        $a          = new Application_Model_AnuncioWeb();
        $aviso      = $a->getAvisoBySlug($this->_urlId);
        $usuario    = $this->auth['usuario'];
        $postulante = $this->auth['postulante'];

        /// Verificamos que no haya despostulado del aviso antes.
        $haDespostulado = false;
        /*  try {
          $despostular    = new Mongo_DesPostulacion();
          $haDespostulado = $despostular->hasDespostulacion($this->auth['postulante']['id'],
          $aviso['id']);
          } catch (Exception $ex) {

          } */

        if ($haDespostulado) {
            $this->getMessenger()->error('No es posible postular ya que ha decidido retirarse del proceso.');
            $this->_redirect($this->_urlAviso);
        }


        $ususarioEmp = new Application_Model_Usuario();
//        $cuentaConfirmada = $ususarioEmp->hasConfirmed($usuario->id);
//
//        if (!$cuentaConfirmada) {
//            $this->getMessenger()->error('Es necesario que confirme primero su cuenta.');
//            $this->_redirect($this->_urlAviso);
//        }



        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $dataPost = $this->_getAllParams();

        $avisoId = $this->_avisoId;

        //$idPostulante = $this->auth['postulante']['id'];

        if ($a->confirmaAvisoActivo($avisoId) == false) {
            $this->getMessenger()->error('El aviso se encuentra cerrado.');
            $this->_redirect($this->_urlAviso);
        }

        // $updateCV = $this->_postulante->hasDataForApplyJob($this->auth['postulante']['id']);
//        if(!$updateCV) {
//            $this->getMessenger()->error('Por favor actualizace sus datos.');
//            $this->_redirect($this->_urlAviso);
//        }

        $showRecomendarCV = false;
//         if (!$this->_postulante->verificarUpdateCV($idPostulante)) {
//              //  $showRecomendarCV = true;
//         }


        $p = new Application_Model_Postulacion();
        if ($p->hasPostulado($avisoId, $postulante['id']) !== false) {
            $this->getMessenger()->error('Ya has postulado a este aviso.');
            $this->_redirect($this->_urlAviso);
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

        try {
            if (!$postulacionValidador->isNull()) {
                if ($postulacionValidador->isInvited()) {

                    $postulacionDatos = $postulacionValidador->getData();
                    $idPostulacion    = $postulacionDatos['id'];

                    $postulacion->activar($idPostulacion);
                    $referenciadoModelo->postulo($email, $avisoId);
                    if (!empty($idPostulacion))
                            $historico->registrar($idPostulacion,
                            Application_Model_HistoricoPs::EVENTO_POSTULACION,
                            Application_Model_HistoricoPs::ESTADO_POSTULACION);

                    $this->_registrarMensajes($dataPost, $avisoId,
                        $idPostulacion);
                    @$this->_cache->remove('Postulacion_getIdAvisosPostulaciones_'.$postulante['id']);
                    @$this->_cache->remove('Postulacion_getProgramasBuscadorEmpresa_'.$avisoId);
                    @$this->_cache->remove('Postulacion_getIdiomasBuscadorEmpresa_'.$avisoId);
                    $this->_redirect($this->_urlAviso);
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
                    $data['activo'] = Application_Model_Postulacion::POSTULACION_BLOQUEADA;
                }

                if (App_Service_Validate_Postulant::hasReferred(
                        $email, $avisoId)) {
                    $data['referenciado'] = Application_Model_Postulacion::ES_REFERENCIADO;

                    $referenciadoModelo->postulo($email, $avisoId);
                }

                $idPostulacion = $postulacion->insert($data);
            }
        } catch (Exception $exc) {
            // var_dump($exc->getMessage());exit;
        }

        $this->_helper->Aviso->actualizarPostulantes($avisoId);
        $this->_helper->Aviso->actualizarInvitaciones($avisoId);
        $this->_helper->Aviso->actualizarNuevasPostulaciones($avisoId);

        $respuesta = new Application_Model_Respuesta();

        //Inicio graba Respuesta
        $mensaje    = new Application_Model_Mensaje();
        $anuncioWeb = new Application_Model_AnuncioWeb();

        $emailUsuarioEmpresa = $ususarioEmp->correoUsuarioxAnuncio($this->_avisoId,
            $this->_creado);
        $tieneCorreoOp       = $anuncioWeb->avisoTieneCorreoOp($this->_avisoId);
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
                        'destacado' => $postulante['destacado'],
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
                            'idAviso' => $this->_avisoId,
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
            //      var_dump($exc->getMessage());exit;

            /*
             * Enviar a un SQS y no mostrar estos detalles al usuario.
             * El correo se enviara desde el SQS
             */

//            $this->getMessenger()->error(
//                    'Error al enviar el correo con los datos de la postulación.'
//            );
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
        if (!empty($idPostulacion)) {
            $data = array(
                'id_postulacion' => $idPostulacion,
                'evento' => 'postulación',
                'fecha_hora' => date('Y-m-d H:i:s'),
                'descripcion' => Application_Model_HistoricoPs::ESTADO_POSTULACION
            );
            $historico->insert($data);
        }
        $modelAPM = new Application_Model_AnuncioPostulanteMatch();
        $idPostu  = $postulante['id'];
        @$modelAPM->update(
                array('estado' => Application_Model_AnuncioPostulanteMatch::ESTADO_POSTULA,
                'fh_postulacion' => date('Y-m-d H:i:s')),
                $this->getAdapter()->quoteInto("id_anuncio_web = $avisoId AND id_postulante = $idPostu",
                    null)
        );
        @$this->_cache->remove('Postulacion_getIdAvisosPostulaciones_'.$postulante['id']);
        @$this->_cache->remove('Postulacion_getProgramasBuscadorEmpresa_'.$avisoId);
        @$this->_cache->remove('Postulacion_getIdiomasBuscadorEmpresa_'.$avisoId);

        $this->getMessenger()->error('Postulaste a este aviso.');

        if ($this->_getParam('esResultado')) {
            $this->_redirect($this->_urlAviso.'/resultado');
        } else {
            if ($showRecomendarCV)
                    $this->_redirect($this->_urlAviso."#winUpdateCV");
            else $this->_redirect($this->_urlAviso);
        }
    }

    public function postularUpdateAction()
    {

        if ($this->_getParam('es-resultado') == 'true') {
            $this->_urlAviso = $this->_urlAviso.'/resultado';
        }
        if ($this->auth == null) {
            $this->getMessenger()->error('Error: Al iniciar sessión.');
            $this->_redirect($this->_urlAviso);
        }
        $a          = new Application_Model_AnuncioWeb();
        $aviso      = $a->getAvisoBySlug($this->_urlId);
        $usuario    = $this->auth['usuario'];
        $postulante = $this->auth['postulante'];
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $dataPost = $this->_getAllParams();

        $avisoId = $this->_avisoId;

        $idPostulante = $this->auth['postulante']['id'];



        if (!$this->_postulante->verificarUpdateCV($idPostulante)) {
            $this->_postulante->update(
                array('ultima_actualizacion' => date('Y-m-d H:i:s')),
                $this->getAdapter()->quoteInto("id = ?", $idPostulante)
            );
        }



        if ($a->confirmaAvisoActivo($avisoId) == false) {
            $this->getMessenger()->error('El aviso se encuentra cerrado.');
            $this->_redirect($this->_urlAviso);
        }

        $p = new Application_Model_Postulacion();
        if ($p->hasPostulado($avisoId, $postulante['id']) !== false) {
            $this->getMessenger()->error('Ya has postulado a este aviso.');
            $this->_redirect($this->_urlAviso);
        }

        $sessionUpdateCV = new Zend_Session_Namespace('updateCV');
//        if (!isset($sessionUpdateCV->urlAviso)) {
//            if (!$this->_postulante->verificarUpdateCV($idPostulante)) {
//                $this->_redirect($this->_urlAviso."#winUpdateCV");
//            }
//        }
//        $this->_postulante->update(
//                    array('ultima_actualizacion' => date('Y-m-d H:i:s')), $this->getAdapter()->quoteInto("id = ?", $idPostulante)
//            );

        unset($sessionUpdateCV->urlAviso);
        unset($sessionUpdateCV->mensajeStatic);


        //Update fecha actualización de postulante


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

                $this->_redirect($this->_urlAviso);
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
        $emailUsuarioEmpresa = $ususarioEmp->correoUsuarioxAnuncio($this->_avisoId,
            $this->_creado);
        $tieneCorreoOp       = $anuncioWeb->avisoTieneCorreoOp($this->_avisoId);
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
                            'idAviso' => $this->_avisoId,
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
//            else {
//
//                if ($validator->isValid($emailUsuarioEmpresa)) {
//                    $this->_helper->mail->postularAvisoEmpresa(
//                            array(
//                                'to' => $emailUsuarioEmpresa,
//                                'idAviso' => $this->_avisoId,
//                                'usuario' => $usuario->email,
//                                'nombre' => $postulante['nombres'],
//                                'apellidos' => $postulante['apellidos'] . ' ' .
//                                $postulante['apellido_paterno'] . " " . $postulante['apellido_materno'],
//                                'dni' => $postulante['num_doc'],
//                                'nivel' => $nivelMaxEstudio,
//                                'slug' => $postulante['slug'],
//                                'nombrePuesto' => $aviso['puesto'],
//                                'mostrarEmpresa' => $aviso['mostrar_empresa'],
//                                'nombreEmpresa' => $aviso['nombre_empresa'],
//                                'nombreComercial' => $aviso['nombre_comercial']
//                            )
//                    );
//                }
//            }
        } catch (Exception $ex) {
            $this->getMessenger()->error(
                'Error al enviar el correo con los datos de la postulación.'
            );
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
        @$modelAPM->update(
                array('estado' => Application_Model_AnuncioPostulanteMatch::ESTADO_POSTULA,
                'fh_postulacion' => date('Y-m-d H:i:s')),
                $this->getAdapter()->quoteInto("id_anuncio_web = $avisoId AND id_postulante = $idPostu",
                    null)
        );
        //Fin
        // @codingStandardsIgnoreEnd

        if ($this->_getParam('esResultado')) {
            $this->_redirect($this->_urlAviso.'/resultado');
        } else {
            $this->_redirect($this->_urlAviso);
        }
    }

    public function reportarAbusoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        if (!$this->getRequest()->isPost()) {
            $this->_redirect('/');
        }
        $dataPost   = $this->_getAllParams();
        $comentario = $dataPost['comentario'];

        $formReportarAbuso = new Application_Form_ReportarAbuso();
        if (!$formReportarAbuso->isValid($dataPost)) {
            $this->_redirect($this->_urlAviso.'#reportAbuse');
        }
        $a                                = new Application_Model_AnuncioWeb();
        $this->_aviso                     = $a->getAvisoBySlug($this->_urlId);
        $this->view->aviso                = $this->_aviso;
        $this->view->anunciosRelacionados = $a->getAvisoRelacionados($this->_aviso['id']);
        $serial                           = $this->view->render('aviso/ver.phtml');
        if ($dataPost['tipo_abuso'] == -1) {
            unset($dataPost['tipo_abuso']);
        } else {
            unset($dataPost['comentario']);
        }
        $dataAbuso = array(
            'id_abuso_categoria' => $dataPost['tipo_abuso'],
            'id_postulante' => $this->auth['postulante']['id'],
            'id_anuncio_web' => $this->_avisoId,
            'comentario' => $comentario, //$dataPost['comentario'],
            'fh' => date('Y-m-d H:i:s'),
            'serial' => $this->view->render('aviso/ver.phtml')
        );
        $abuso     = new Application_Model_Abuso();
        $abuso->insert($dataAbuso);
        $this->getMessenger()->success('El Abuso fue reportado con éxito.');
        $this->_redirect($this->_urlAviso);
    }

    public function postularLoginAction()
    {

        $idPostulante = $this->auth['postulante']['id'];
        if (!$this->_postulante->verificarUpdateCV($idPostulante)) {
            $this->_redirect($this->_urlAviso."#winUpdateCV");
        } else {
            $this->_redirect($this->_urlAviso."#questionsWM");
        }
    }

    public function _registrarMensajes($dataPost, $avisoId, $idPostulacion)
    {
        $mensaje   = new Application_Model_Mensaje();
        $respuesta = new Application_Model_Respuesta();
        $pregunta  = new Application_Model_Pregunta();

        $anuncioWebModelo = new Application_Model_AnuncioWeb;
        $anuncioWeb       = new Application_Model_AnuncioWeb();

        $aw            = $anuncioWeb->fetchRow($anuncioWeb->getAdapter()->quoteInto('id = ?',
                $avisoId));
        $dataRespuesta = array();
        foreach ($dataPost as $key => $value) {
            if (substr_count($key, 'pregunta') > 0) {
                $dataRespuesta['id_pregunta']    = str_replace('pregunta_', '',
                    $key);
                $dataRespuesta['id_postulacion'] = $idPostulacion;
                $dataRespuesta['respuesta']      = $value;
                $respuesta->insert($dataRespuesta);
                $idPre                           = $dataRespuesta['id_pregunta'];

                $pregu      = $pregunta->find($idPre)->toArray();
                $data       = array(
                    'de' => $aw->creado_por,
                    'para' => $this->auth['usuario']->id,
                    'fh' => date('Y-m-d H:i:s'),
                    'cuerpo' => $pregu['0']['pregunta'],
                    'tipo_mensaje' => Application_Model_Mensaje::ESTADO_PREGUNTA,
                    'leido' => 1,
                    'notificacion' => 0,
                    'id_postulacion' => $idPostulacion
                );
                $idPregunta = $mensaje->insert($data);
                $data       = array(
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
            }
        }
    }
}