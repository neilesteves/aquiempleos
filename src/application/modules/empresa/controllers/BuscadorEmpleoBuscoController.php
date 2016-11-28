<?php

class Empresa_BuscadorAquiEmpleosController extends App_Controller_Action_Empresa
{

    protected $_anuncioweb;
    protected $_tieneBuscador;
    protected $_tieneBolsaCVs;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->_usuario = new Application_Model_Usuario();
    }

    public function init()
    {
        parent::init();
        $this->_anuncioweb = new Application_Model_AnuncioWeb();
        $this->_empresa = new Application_Model_Empresa();
        $this->_usuario = new Application_Model_Usuario();
        if($this->_usuario->hasvailBlokeo($this->auth['usuario']->id)) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::forgetMe();
            $this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
            $this->_redirect('/empresa');
        }


        $this->_tieneBolsaCVs = false;

        if(isset($this->auth['empresa']['membresia_info']['membresia']['id_membresia'])) {
            $this->_tieneBolsaCVs = !empty($this->auth['empresa']['membresia_info']['beneficios']->bolsacv) ? 1 : 0;
        }
        $this->view->Look_Feel = $this->_empresa->LooFeelActivo($this->auth['empresa']['id']);
        $this->view->tieneBolsaCVs = $this->_tieneBolsaCVs;
        $this->view->tieneBuscador = $this->_anuncioweb->beneficioBuscadorAptitus($this->auth);
    }

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->S('/css/plugins/jquery-ui-1.9.2.custom.min.css'));
        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/empresa/empresa.buscador.js')
        );

        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/empresa/empresa.bolsacvsgeneral.js')
        );

        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/empresa/perfil.postulante.js')
        );
        //var_dump($this->auth['empresa']['membresia_info']['beneficios']);exit;
        $tieneBuscadorAptitus = false;
        if(isset($this->auth['empresa']['membresia_info']['beneficios']->buscador)) {
            $tieneBuscadorAptitus = true;
        }
        if(isset($this->auth['empresa']['membresia_info']['membresia']['id_membresia']) && $this->auth['empresa']['membresia_info']['membresia']['id_membresia'] == 11) {
            $tieneBuscadorAptitus = true;
        }
        $this->view->option = $tieneBuscadorAptitus;
        //Compartir Mail
        $formCompartir = new Application_Form_CompartirPorMail();
        $formCompartir->setAction('/mi-cuenta/compartir');
        $formCompartir->correoEmisor->setValue($this->auth['usuario']->email);

        $formCompartir->nombreEmisor->setValue(
                ucwords($this->auth['empresa']['razon_social'])
        );
        Zend_Layout::getMvcInstance()->assign(
                'compartirPorMail', $formCompartir
        );

        $this->view->menu_sel = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_BUSCA_APTITUS;

        $empID = Application_Model_Usuario::getEmpresaId();


        if($empID == Application_Model_AnuncioWeb::JJC_ID)
            ;
        else {
            $empID = 1;
        }

//        $postulacion = new Application_Model_Postulacion();
//        $nivelestudios = $postulacion->getNivelesEstudiosBuscadorEmpresaNuevo(true);
//        $otrosestudios = $postulacion->getOtrosEstudiosBuscadorEmpresa();
//        $edades = $postulacion->getEdadesBuscadorEmpresa();
//        $sexo = $postulacion->getSexoBuscadorEmpresa();
//        $idiomas = $postulacion->getIdiomasBuscadorEmpresa();
//        $programas = $postulacion->getProgramasBuscadorEmpresa("", true, $empID);
//        $anosexperiencia = $postulacion->getAnosExperienciasBuscadorEmpresa();
//        $tipocarrera = $postulacion->getTipoCarreraBuscadorEmpresa("", true);
//        $ubicacion = $postulacion->getUbicacionBuscadorEmpresa();

        $anuncio = new Application_Model_AnuncioWeb();


        $this->view->idPostulacion = $idAviso = $this->_getParam("id");
        $this->view->activarInvitar = $tieneBuscadorAptitus;
        if($idAviso != "") {
            $anuncio = new Application_Model_AnuncioWeb();
            $a = $anuncio->getAvisoById($idAviso);
            $this->view->idNivelPuesto = $a["id_nivel_puesto"];
            $this->view->idArea = $a["id_area"];
            if(empty($a['online']))
                $this->view->activarInvitar = false;
            $this->view->puesto = $a["puesto"];
        }

        $params = $this->getRequest()->getParams();
        if(!isset($params['text']))
            $params['text'] = '';
        $this->view->get = $get = true;
        /* else
          $this->view->get = $get = false; */
        $pass = new Zend_Session_Namespace('pass');
        $resultado = array();
        $postDis = 1;
        $modelPostulante = new Solr_SolrPostulante();
        // var_dump($modelPostulante->search());exit;
        $resultado=$modelPostulante->search($params);
        $nivelestudios=$resultado['filter']['estudios_claves'];
        $this->view->proceso = $resultado;
        $this->view->mostrando = "Mostrando "
                . count($resultado['rows']) . " de "
                . $resultado['ntotal'];
          //  var_dump($resultado);exit;
        //$this->view->msg_facets = $resultado["filter"];
        if($get) {
            $postDis = 0;

            /** Se quita a peticion de Vladimir
             *
              if($pass->token!=$params['token']||empty($pass->token))
              $this->_redirect('/empresa/mi-cuenta');
             */
            $this->view->col = $col = $this->_getParam('col', '');
            $this->view->ord = $ord = $this->_getParam('ord');
            $aptitudes = $this->_getParam('tags');
            $modeAptitud = new Solr_SolrAptitud();
            $this->view->aptitudes = $modeAptitud->getAptitudByIds($aptitudes);
            $this->view->text = strip_tags($params['text']);
           // $moPostulante->setOrder($col, $ord);
            if(isset($params['page']))
                $page = $params['page'];
            else
                $page = 1;
            $resultado = $resultado;
            foreach ($resultado['rows'] as $item) {
                if(!empty($item['destacado'])) {
                    $data = array();
                    $data['id_postulante'] = $item['idpostulante'];
                    $data['id_empresa'] = $this->auth['empresa']['id'];
                    $data['tipo'] = 2;
                    $data['id_aviso'] = 0;
                    $data['fecha_busqueda'] = date('Y-m-d H:i:s');
                    $visitas = new Application_Model_Visitas();
                    $res = $visitas->insert($data);
                }
            }
            $this->view->proceso = $resultado;
        $this->view->mostrando = "Mostrando "
                . count($resultado['rows']) . " de "
                . $resultado['total'];
            try {
                $busqueda = new Mongo_BusquedaPostulante();
                if($busqueda->isConnected()) {
                    $datos = array(
                        'auth' => $this->auth,
                        'cantidad_de_resultados' => $resultado['total']
                    );
                    $busqueda->save($datos);
                }
            } catch(Exception $e) {
                
            }
            //Condicional
            $arrayTags = array(
                'niveldeestudios',
                'niveldeOtrosestudios',
                'tipodecarrera',
                'experiencia',
                'idiomas',
                'programas',
                'edad',
                'sexo',
                'ubicacion'
            );
            $msgFacets = array();

            foreach ($arrayTags as $tag) {
                if($tag == 'niveldeestudios' && isset($params[$tag])) {
                    $arrNE = $params[$tag];
                    $contadorTags = 0;
                    $excluidos = array('10-00', '08-00', '13-00', '04-00');
                    foreach ($arrNE as $ne) {
                        if(!in_array($ne, $excluidos)) {
                            $contadorTags++;
                        }
                    }
                } else {
                    $contadorTags = isset($params[$tag]) ? count($params[$tag]) : null;
                }


                if(!empty($contadorTags)) {
                    $s = '';
                    if($contadorTags > 1) {
                        $s = 's';
                    }
                    $msgFacets[$tag] = ucfirst(strtolower($this->config->buscadorempresa->urls->$tag)) . "," . $contadorTags . " Seleccionado$s";
                }
            }
            $this->view->msg_facets = $msgFacets;
            $alertas = array();
            if(isset($this->auth['empresa'])) {
                $id_propietario = $this->auth['empresa']['id'];
                $tipo = 'Empresa';
                $alerta = new Application_Model_Alerta();
                $alertas = $alerta->getAlertas($id_propietario, $tipo);
            }
            $this->view->alertas = $alertas;
            $nivelestudios = $this->_prepareFilters($nivelestudios, $resultado['ne'], true, true);
            $otrosestudios = $this->_prepareFilters($otrosestudios, $resultado['oe'], true);
            $tipocarrera = $this->_prepareFilters($tipocarrera, $resultado['tc'], true);
            $ubicacion = $this->_prepareFilters($ubicacion, $resultado['ubi'], true);
            $anosexperiencia = $this->_prepareFilters($anosexperiencia, $resultado['ex']);
            $edades = $this->_prepareFilters($edades, $resultado['ed']);
            $idiomas = $this->_prepareFilters($idiomas, $resultado['id'], true);
            $programas = $this->_prepareFilters($programas, $resultado['pr'], true);
            $conadis = array(
                array('id' => 0)
            );
            $sexo = $this->_prepareFilters($sexo, $resultado['se']);
            $discapacidad = $this->_prepareFilters($conadis, $resultado['dis']);
            $cont = array();
            foreach ($resultado['dis'] as $value) {
                $cont[] = $value;
            }
            $conadis = array();
        }
        if(isset($discapacidad[$postDis]['total']) && $discapacidad[$postDis]['total'] > 0) {
            $conadis = array(
                0 => array(
                    'id' => '1',
                    'nombre' => "Con discapacidad / Conadis ({$discapacidad[$postDis]['total']})"
                )
            );
        }
        $this->view->dataFiltros = array(
            "niveldeestudios" => array(
                'titulo' => $this->config->buscadorempresa->urls->niveldeestudios,
                'param' => $this->config->buscadorempresa->param->niveldeestudios,
                'data' => $nivelestudios,
                'expandible' => 0,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "otrosestudios" => array(
                'titulo' => $this->config->buscadorempresa->urls->niveldeOtrosestudios,
                'param' => $this->config->buscadorempresa->param->niveldeOtrosestudios,
                'data' => $otrosestudios,
                'expandible' => 0,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "tipodecarrera" => array(
                'titulo' => $this->config->buscadorempresa->urls->tipodecarrera,
                'param' => $this->config->buscadorempresa->param->tipodecarrera,
                'data' => $this->_prepare($tipocarrera, 10),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "experiencia" => array(
                'titulo' => $this->config->buscadorempresa->urls->experiencia,
                'param' => $this->config->buscadorempresa->param->experiencia,
                'data' => $anosexperiencia,
                'expandible' => 0,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "idiomas" => array(
                'titulo' => $this->config->buscadorempresa->urls->idiomas,
                'param' => $this->config->buscadorempresa->param->idiomas,
                'data' => $idiomas,
                'expandible' => 0,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "programas" => array(
                'titulo' => $this->config->buscadorempresa->urls->programas,
                'param' => $this->config->buscadorempresa->param->programas,
                'data' => $programas,
                'expandible' => 0,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "edad" => array(
                'titulo' => $this->config->buscadorempresa->urls->edad,
                'param' => $this->config->buscadorempresa->param->edad,
                'data' => $edades,
                'expandible' => 0,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "sexo" => array(
                'titulo' => $this->config->buscadorempresa->urls->sexo,
                'param' => $this->config->buscadorempresa->param->sexo,
                'data' => $sexo,
                'expandible' => 0,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "ubicacion" => array(
                'titulo' => $this->config->buscadorempresa->urls->ubicacion,
                'param' => $this->config->buscadorempresa->param->ubicacion,
                'data' => $this->_prepare($ubicacion, 10),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "conadis" => array(
                'titulo' => $this->config->buscadorempresa->urls->conadis_code,
                'param' => $this->config->buscadorempresa->param->conadis_code,
                'data' => $this->_prepare($conadis, 7),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->conadis_code,
                'enumeraciones' => $this->config->enumeraciones
            )
        );

        $pass->token = $this->view->token = md5(rand());
        //Validar avisos
        //Sino tiene membresía
    }

    public function buscadorAjaxAction()
    {
        $this->_helper->layout->disableLayout();
        //$zl = new ZendLucene();

        $this->view->col = $col = $this->_getParam('col', '');
        $this->view->ord = $ord = $this->_getParam('ord');
        $page = $this->_getParam('page', 1);
        $check = $this->_getParam('check');
        $idEmpresa = $this->auth["empresa"]["id"];
        $this->view->pagina = $page;

        //cadenas del BUSCADOR
        $param = $this->config->buscadorempresa->param;
        $niveldeestudios = $this->_getParam($param->nivelestudios);
        $tipodecarrera = $this->_getParam($param->tipodecarrera);
        $experiencia = $this->_getParam($param->experiencia);
        $idiomas = $this->_getParam($param->idiomas);
        $programas = $this->_getParam($param->programas);
        $edad = $this->_getParam($param->edad);
        $sexo = $this->_getParam($param->sexo);
        $ubicacion = $this->_getParam($param->ubicacion, "");
        $query = htmlentities(utf8_decode($this->_getParam($param->query)));

        $cadenabusqueda = (($query != "") ?
                        ($param->query . "/" . $query . "/") : "") .
                (($niveldeestudios != "") ?
                        ($param->nivelestudios . "/" . $niveldeestudios . "/") : "") .
                (($tipodecarrera != "") ?
                        ($param->tipodecarrera . "/" . $tipodecarrera . "/") : "") .
                (($experiencia != "") ?
                        ($param->experiencia . "/" . $experiencia . "/") : "") .
                (($idiomas != "") ?
                        ($param->idiomas . "/" . $idiomas . "/") : "") .
                (($programas != "") ?
                        ($param->programas . "/" . $programas . "/") : "") .
                (($edad != "") ?
                        ($param->edad . "/" . $edad . "/") : "") .
                (($sexo != "") ?
                        ($param->sexo . "/" . $sexo . "/") : "") .
                (($ubicacion != "") ?
                        ($param->ubicacion . "/" . $ubicacion . "/") : "");
        //echo "<br>".$cadenabusqueda."<br>";
        $this->view->cadenabusqueda = substr($cadenabusqueda, 0, strlen($cadenabusqueda) - 1);

        //fin cadenas BUSCADOR
        if($check != 'false' && $page == 1) {

            $this->_helper->LogActualizacionBI->logActualizacionBuscadorAviso(
                    $this->_getAllParams(), $idEmpresa, Application_Model_LogBusqueda::TIPO_BUSCADOR_APTITUS
            );
        }

        $_niveldeestudios = ($niveldeestudios != "") ? explode("--", $niveldeestudios) : "";
        $_tipodecarrera = ($tipodecarrera != "") ? explode("--", $tipodecarrera) : "";
        $_experiencia = ($experiencia != "") ? explode("--", $experiencia) : "";
        $_idiomas = ($idiomas != "") ? explode("--", $idiomas) : "";
        $_programas = ($programas != "") ? explode("--", $programas) : "";
        $_edad = ($edad != "") ? explode("--", $edad) : "";
        $_sexo = ($sexo != "") ? explode("--", $sexo) : "";
        $_ubicacion = ($ubicacion != "") ? explode("--", $ubicacion) : "";

        $_idanuncioweb = $this->_getParam("idanuncio");
        $_idnivelpuesto = $this->_getParam("idnivelpuesto");
        $_idarea = $this->_getParam("idarea");

        /* if ($this->config->confpaginas->javalucene==1 && $query!="") {
          $query = $this->view->LuceneCast($query);
          } */
        $id = '';
        if($_ubicacion != "")
            foreach ($_ubicacion as $value => $key) {
                if($_ubicacion[$value] == 3285) {

                    $id = $_ubicacion[$value];
                    unset($_ubicacion[$value]);

                    $modelUbigeo = new Application_Model_Ubigeo();
                    $arrayUbigeo = $modelUbigeo->getHijosId($id);
                    foreach ($arrayUbigeo as $data) {
                        $newCallao[] = $data['id'];
                    }
                }
            }

        if(isset($newCallao)) {
            $_ubicacion = array_merge($_ubicacion, $newCallao);
        }
        /* $_ubicacion = '';
          $max = count($newArray);
          $count = 1;
          foreach ($newArray as $value) {
          $_ubicacion =$_ubicacion.$value;
          if ($max != $count) {
          $_ubicacion = $_ubicacion.'--';
          }
          } */
        $opcion = "";

        $pQuery = $this->view->LuceneCast($query);
        $queryLu = $this->view->QueryLucene($pQuery);

        $paginator = $this->_anuncioweb->getPaginatorListarPostulantes(
                $col, $ord, $opcion, $_niveldeestudios, $_tipodecarrera, $_experiencia, $_idiomas, $_programas, $_edad, $_sexo, $_ubicacion, $queryLu, $pQuery, $_idanuncioweb, $_idnivelpuesto, $_idarea
        );

        $paginator->setCurrentPageNumber($page);
        $this->view->proceso = $paginator;
        $this->view->mostrando = "Mostrando "
                . $paginator->getItemCount($paginator->getItemsByPage($page)) . " de "
                . $paginator->getTotalItemCount();

        $pagina = $this->view->render('buscador-aptitus/_mainbuscador.phtml');
        echo $pagina;
        exit;
    }

    public function perfilPublicoEmpAction()
    {

        $this->view->tieneBuscador = $this->_tieneBuscador;
        $idPostulante = $this->_getParam('id');
        //----------------------------------------------------------------------------------
        $modeloPostulanteBloqueado = new Application_Model_EmpresaPostulanteBloqueado();
        $this->view->empresaMembresia = $this->auth['empresa']['membresia_info']['membresia']['id_membresia'];

        $getBloqueado = $modeloPostulanteBloqueado->
                obtenerPorEmpresaYPostulante(
                (int) $this->auth['empresa']['id'], $idPostulante, array('id' => 'id')
        );

        $this->view->btnBloqueado = 0;

        if((int) $getBloqueado['id'] > 0) {
            $this->view->btnBloqueado = 1;
        }

        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/empresa/empresa.bolsacvsgeneral.js')
        );
        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/empresa/perfil.postulante.js')
        );
        //----------------------------------------------------------------------------------
        //$this->_helper->layout()->disableLayout();
        //$this->_helper->viewRenderer->setNoRender();
        $this->view->menu_sel = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MIS_PROCESOS;
        Zend_Layout::getMvcInstance()->assign(
                'bodyAttr', array('id' => 'profilePublic')
        );

        $idAPM = $this->_getParam('idAPM', null);
        if($idAPM != null) {
            $where = $this->getAdapter()->quoteInto("id_anuncio_web = $idAPM AND id_postulante = $idPostulante", null);
            $modelAPM = new Application_Model_AnuncioPostulanteMatch();
            $modelAPM->update(array('leido' => '0'), $where);
        }
        //Postulante Datos
        $postulante = new Application_Model_Postulante();
        $perfil = $postulante->getPerfil($idPostulante);

        if($perfil == false) {
            $this->_redirect('/');
        }

        //Empresa Datos
        $idEmpresa = $this->auth["empresa"]["id"];

        //Datos Para Perfil
        $perfil['postulante']['fotovariable'] = $perfil['postulante']['path_foto_uno'];
        $perfil['postulante']['actionName'] = $this->_request->getActionName();
        $perfil['postulante']['id_categoria_postulacion'] = 0;
        $perfil['postulante']['descartado'] = 0;
        $perfil['postulante']['match'] = 100;

        unset($perfil["postulante"]["actionName"]);
        $perfil["postulante"]["verLastUpdate"] = true;
        $this->view->postulante = $perfil;
        $usuario = $this->auth['usuario'];

        $formCompartir = new Application_Form_CompartirPorMail();
        $formCompartir->setAction('/mi-cuenta/compartir');
        $formCompartir->correoEmisor->setValue($usuario->email);
        $formCompartir->hdnOculto->setValue($perfil['postulante']['slug']);
        $formCompartir->nombreEmisor->setValue(
                ucwords($perfil['postulante']['nombres']) . ' ' .
                ucwords($perfil['postulante']['apellidos'])
        );
        Zend_Layout::getMvcInstance()->assign('compartirPorMail', $formCompartir);
        $this->view->idPostulante = $idPostulante;
        $html = $this->render('perfil-publico-emp');

        $res = array('html' => $html,);
        //$this->_response->appendBody(Zend_Json_Encoder::encode($res));
    }

    public function invitarAction()
    {
        $this->_helper->layout()->disableLayout();

        if($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
            $form = new Application_Form_InvitarBusqueda($this->auth);
            $idaviso = $this->_getParam("idaviso");
            if(isset($idaviso)) {
                $form->getElement('aviso')->setValue($idaviso);
                $aviso = new Application_Model_AnuncioWeb();
                $resaviso = $aviso->getAvisoById($idaviso);
                $this->view->aviso = $resaviso;
            }
            $this->view->frmInvitar = $form;
        } else {
            $this->_redirect('/empresa/mi-cuenta');
        }
    }

    public function mostrarProcesoAction()
    {
        $this->_helper->layout()->disableLayout();

        if($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
            $idaviso = $this->_getParam("idaviso");
            $tok = $this->_getParam("tok");

            if(!empty($idaviso) && isset($idaviso)) {

                if(crypt(date('dmYH'), $tok) !== $tok) {
                    exit;
                }

                $aviso = new Application_Model_AnuncioWeb();
                $resaviso = $aviso->getAvisoById($idaviso);
                $this->view->aviso = $resaviso;

                $ntok = crypt(__CLASS__ . date('dmYH'), '$2a$07$' . md5(uniqid(rand(), true)) . '$');
                $this->view->ntok = $ntok;
                $this->view->moneda = $this->config->app->moneda;
            }
        } else {
            $this->_redirect('/empresa/mi-cuenta');
        }
    }

    public function invitarPostulanteAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        if($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost())
            ;
        else {
            $this->_redirect('/empresa/mi-cuenta');
        }

        $idaviso = $this->_getParam("idaviso");
        $tok = $this->_getParam("tok");

        if(isset($idaviso) && isset($tok)) {

            if(crypt(date('dmYH'), $tok) !== $tok) {
                die('BAD|Los datos no son validos.');
            }

            $postulantes = $this->_getParam("postulantes");
            if(!empty($postulantes)) {
                $postulantesBloqueados = new Application_Model_EmpresaPostulanteBloqueado();
                $postulanteIds = implode(',', $postulantes);

                $listPostulantesBloqueados = $postulantesBloqueados->listByPostulante(
                        $this->auth["empresa"]["id"], $postulantes, array('id_postulante'));

                if($listPostulantesBloqueados) {
                    $postulantesB = array();

                    foreach ($listPostulantesBloqueados->toArray() as $key => $value) {
                        $postulantesB[] = $value['id_postulante'];
                    }

                    foreach ($postulantes as $key => $value) {
                        if(in_array($value, $postulantesB)) {
                            unset($postulantes[$key]);
                        }
                    }
                }
            }


            $npostulantes = count($postulantes);
            $n = 0;
            $numT = 0;
            $listapostulantesOtrosErrores = "";
            $listapostulantes = "";
            foreach ($postulantes as $idPostulante) {
                $aw = new Application_Model_AnuncioWeb();
                $x = $aw->getInvitacionesPostulante($idPostulante, $idaviso);

                if(count($x) > 0) {
                    $listapostulantes.=$x[0]["nombres"] . ", ";
                    $numT++;
                } else {
                    $db = $this->getAdapter();
                    try {

                        $db->beginTransaction();

                        $modelAw = new Application_Model_AnuncioWeb();
                        $aw = $modelAw->fetchRow($modelAw->getAdapter()->quoteInto('id = ?', $idaviso));

                        $modelPostulacion = new Application_Model_Postulacion();
                        $modelPostulante = new Application_Model_Postulante();
                        $postulante = $modelPostulante->getPostulantePerfil($idPostulante);
                        //Postulacion
                        $funciones = $this->_helper->getHelper("RegistrosExtra");
                        $match = $funciones->PorcentajeCoincidencia($idaviso, $idPostulante);
                        $nivelestudioscarrera = $funciones->MejorNivelEstudiosCarrera($idPostulante);

                        $idPostulacion = $modelPostulacion->insert(
                                array('id_postulante' => $idPostulante,
                                    'id_anuncio_web' => $idaviso,
                                    'id_categoria_postulacion' => null,
                                    'fh' => date('Y-m-d H:i:s'),
                                    'fh_invitacion' => date('Y-m-d H:i:s'),
                                    'activo' => '0',
                                    'invitacion' => '1',
                                    'msg_leidos' => '0',
                                    'msg_no_leidos' => '0',
                                    'msg_por_responder' => '0',
                                    'match' => $match,
                                    'es_nuevo' => '1',
                                    'nivel_estudio' => $nivelestudioscarrera["nivelestudios"],
                                    'carrera' => $nivelestudioscarrera["carrera"])
                        );

                        $this->_helper->Aviso->actualizarPostulantes($idaviso);
                        $this->_helper->Aviso->actualizarInvitaciones($idaviso);
                        $this->_helper->Aviso->actualizarNuevasPostulaciones($idaviso);

                        //envio de correo.
                        //Postulante
                        $modelPostulante = new Application_Model_Postulante();
                        $arrayPostulante = $modelPostulante->getPostulante($idPostulante);
                        $arrayAnuncioWeb = $modelAw->getAvisoById($idaviso);
                        // @codingStandardsIgnoreStart
                        $modelAPM = new Application_Model_AnuncioPostulanteMatch();
                        $whereMatch = array();
                        $whereMatch[] = $this->getAdapter()->quoteInto('id_postulante = ?', $idPostulante);
                        $whereMatch[] = $this->getAdapter()->quoteInto('id_anuncio_web = ?', $idaviso);
                        $modelAPM->update(
                                array('estado' => Application_Model_AnuncioPostulanteMatch::ESTADO_POSTULA,
                            'fh_postulacion' => date('Y-m-d H:i:s')), $this->getAdapter()->quoteInto("id_anuncio_web = $idaviso and id_postulante = $idPostulante", null)
                        );

                        //Invitacion y envio de Mensaje Postulante
                        $modelMsj = new Application_Model_Mensaje();
                        $cuerpo = $aw['empresa_rs'] . ' ha iniciado el proceso de Selección para el puesto de ' . $aw['puesto'] . '. ';
                        $cuerpo.= 'la empresa vio tu perfil en la base de datos de AquiEmpleos y quiere invitarte a que seas parte de este proceso. ';
                        $cuerpo.= '<a href="';
                        $cuerpo.= SITE_URL . $this->view->url(array('module' => 'postulante',
                                    'controller' => 'aviso', 'action' => 'ver', 'url_id' => $aw['url_id'],
                                    'slug' => $aw['slug']), 'aviso', true);
                        $cuerpo.= '"><b>Ver Aviso y Postular</b></a>';

                        $data = array(
                            'de' => $aw->creado_por,
                            'para' => $arrayPostulante['idusuario'],
                            'fh' => date('Y-m-d H:i:s'),
                            'cuerpo' => $cuerpo,
                            'tipo_mensaje' => Application_Model_Mensaje::ESTADO_INVITACION,
                            'leido' => 0,
                            'notificacion' => 1,
                            'id_postulacion' => $idPostulacion
                        );

                        $idPregunta = $modelMsj->insert($data);
                        $avisoHelper = $this->_helper->getHelper('Mensaje');
                        $avisoHelper->actualizarCantMsjsNotificacion($arrayPostulante['idusuario']);

                        $this->_helper->mail->invitarPostular(
                                array(
                                    'to' => $arrayPostulante['email'],
                                    'email' => $arrayPostulante['email'],
                                    'slug' => $arrayAnuncioWeb['slug'],
                                    'url_id' => $arrayAnuncioWeb['url_id'],
                                    'nombre' => ucwords($arrayPostulante['nombres']),
                                    'empresa' => $arrayAnuncioWeb['nombre_comercial'],
                                    'puesto' => $arrayAnuncioWeb['puesto'],
                                    'mostrarEmpresa' => $arrayAnuncioWeb['mostrar_empresa']
                                )
                        );

                        $n++;
                        $db->commit();
                    } catch(Zend_Db_Exception $e) {
                        $db->rollBack();
                        if(count($x) > 0) {
                            $listapostulantesOtrosErrores.=$x[0]["nombres"] . ", ";
                        }
                    }
                }
            }
            if($npostulantes == 0 && $n == 0) {
                echo "BAD|El postulante Esta bloqueado para este proceso.";
                exit;
            }

            if($npostulantes == $n) {
                echo "OK|OK";
            } else {
                if(strlen($listapostulantesOtrosErrores) == 1) {
                    echo "BAD|Error al enviar la invitación al postulante: " .
                    substr($listapostulantesOtrosErrores, 0, count($listapostulantesOtrosErrores) - 2) . ".";
                } elseif(strlen($listapostulantesOtrosErrores) > 1) {
                    echo "BAD|Error al enviar las invitaciones a los postulantes: " .
                    substr($listapostulantesOtrosErrores, 0, count($listapostulantesOtrosErrores) - 2) . ".";
                } else {
                    $listapostulantes = substr(trim($listapostulantes), 0, -1); //sacamos la ultima coma

                    if($npostulantes <= 1) {
                        echo "BAD|El postulante ya fue invitado: " . $listapostulantes . '.';
                    } elseif($npostulantes == $numT) {
                        echo "BAD|Los postulantes ya fueron invitados: " . $listapostulantes . '.';
                    } else {
                        echo "BAD|Se invitaron correctamente a los postulantes seleccionados,
									 de los cuales ya se había invitado a: " . $listapostulantes . '.';
                    }
                }
            }
        }

        exit;
    }

    private function _prepare( $data, $n )
    {
        $dataChunks = array_chunk($data, $n);
        $nchunks = count($dataChunks);
        $ocultos = $nchunks > 1 ? array_slice($dataChunks, 1, $nchunks - 1) : array(
        );
        return array(
            'visible' => count($dataChunks) ? $dataChunks[0] : array(),
            'ocultos' => $ocultos
        );
    }

    public function perfilPublicoEmpSolrAction()
    {


        if($this->auth['empresa']['membresia_info']['beneficios']->buscador) {
            $tieneBuscadorAptitus = true;
        }
        if($this->auth['empresa']['membresia_info']['membresia']['id_membresia'] == 11) {
            $tieneBuscadorAptitus = true;
        }
        $this->view->option = $tieneBuscadorAptitus;
        $slug = $this->_getParam('slug');
        $postulante = new Application_Model_Postulante();
        $dataPostulante = $postulante->obtenerPorSlug($slug);
        $idPostulante = $dataPostulante['id'];
        //----------------------------------------------------------------------------------
        $modeloPostulanteBloqueado = new Application_Model_EmpresaPostulanteBloqueado();
        $this->view->empresaMembresia = $this->auth['empresa']['membresia_info']['membresia']['id_membresia'];

        $getBloqueado = $modeloPostulanteBloqueado->
                obtenerPorEmpresaYPostulante(
                (int) $this->auth['empresa']['id'], $idPostulante, array('id' => 'id')
        );

        $this->view->btnBloqueado = 0;

        if((int) $getBloqueado['id'] > 0) {
            $this->view->btnBloqueado = 1;
        }

        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/empresa/empresa.bolsacvsgeneral.js')
        );
        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/empresa/perfil.postulante.js')
        );
        //----------------------------------------------------------------------------------
        //$this->_helper->layout()->disableLayout();
        //$this->_helper->viewRenderer->setNoRender();
        $this->view->menu_sel = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MIS_PROCESOS;
        Zend_Layout::getMvcInstance()->assign(
                'bodyAttr', array('id' => 'profilePublic')
        );

        $idAPM = $this->_getParam('idAPM', null);
        if($idAPM != null) {
            $where = $this->getAdapter()->quoteInto("id_anuncio_web = $idAPM AND id_postulante = $idPostulante", null);
            $modelAPM = new Application_Model_AnuncioPostulanteMatch();
            $modelAPM->update(array('leido' => '0'), $where);
        }
        //Postulante Datos
        $perfil = $postulante->getPerfil($idPostulante);

        if($perfil == false) {
            $this->_redirect('/');
        }

        if(!empty($perfil['postulante']['destacado'])) {
            $data = array();
            $data['id_postulante'] = $idPostulante;
            $data['id_empresa'] = $this->auth['empresa']['id'];
            $data['tipo'] = 1;
            $data['id_aviso'] = 0;
            $data['fecha_busqueda'] = date('Y-m-d H:i:s');
            $visitas = new Application_Model_Visitas();
            $res = $visitas->insert($data);
        }

        //Empresa Datos
        $idEmpresa = $this->auth["empresa"]["id"];

        //Datos Para Perfil
        $perfil['postulante']['fotovariable'] = $perfil['postulante']['path_foto_uno'];
        $perfil['postulante']['actionName'] = $this->_request->getActionName();
        $perfil['postulante']['id_categoria_postulacion'] = 0;
        $perfil['postulante']['descartado'] = 0;
        $perfil['postulante']['match'] = 100;

        unset($perfil["postulante"]["actionName"]);
        $perfil["postulante"]["verLastUpdate"] = true;
        $this->view->postulante = $perfil;
        $usuario = $this->auth['usuario'];
        $formCompartir = new Application_Form_CompartirPorMail();
        $formCompartir->setAction('/mi-cuenta/compartir');
        $formCompartir->correoEmisor->setValue($usuario->email);
        $formCompartir->hdnOculto->setValue($perfil['postulante']['slug']);
        $formCompartir->nombreEmisor->setValue(
                ucwords($this->auth['empresa']['razon_social'])
        );
        Zend_Layout::getMvcInstance()->assign('compartirPorMail', $formCompartir);
        $pass = new Zend_Session_Namespace('pass');
        $this->view->token = $pass->token;
        $this->view->idPostulante = $idPostulante;
        $html = $this->render('perfil-publico-emp');

//        $res = array('html' => $html,);
        //$this->_response->appendBody(Zend_Json_Encoder::encode($res));
    }

    private function _prepareFilters( $data, $facet, $extra = false, $reverse = false )
    {
        if($extra) {
            $res = array();

            foreach ($facet as $k => $v) {
                if(isset($v)) {
                    $arrIndices = explode('#-#', $k);
                    foreach ($arrIndices as $ind) {
                        if(isset($res[$ind]) === false) {
                            $res[$ind] = 0;
                        }
                        $res[$ind]+=$v;
                    }
                }
            }
            arsort($res);
            $facet = $res;
        }
        $result = array();
        if($reverse) {
            foreach ($facet as $m => $n) {
                $arrM = explode('-', $m);
                if($arrM[1] != '00') {

                    if(!isset($facet["{$arrM[0]}-00"])) {
                        $facet["{$arrM[0]}-00"] = 0;
                    }

                    $facet["{$arrM[0]}-00"] += $n;
                }
            }

            foreach ($data as $k => $v) {
                if(!empty($facet[$k])) {
                    $v['total'] = $facet[$k];
                    $result[] = $v;
                }
            }
        } else {
            foreach ($facet as $value => $count) {
                if(!empty($count) && array_key_exists($value, $data)) {
                    $data[$value]['total'] = $count;
                    $result[] = $data[$value];
                }
            }
        }
        return $result;
    }

    public function agregarAlertaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();


        if($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost())
            ;
        else {
            $this->_redirect('/empresa/mi-cuenta');
        }


        if($this->_hash->isValid($this->_getParam('token'))) {
            $nombre = trim($this->_getParam('nombre', ''));
            //$urls = parse_url(trim($this->_getParam('url','')));
            //var_dump( $url);exit;
            //$url =$urls["path"].$urls["query"];
            $url = trim($this->_getParam('url', ''));
            if(empty($nombre) || empty($url)) {

                $mensaje = 'Datos no son válidos.';
                $estado = 0;
            } else {

                $totalAlertas = 0;
                if(empty($url)) {
                    $mensaje = 'Url vacia';
                    $estado = 0;
                } else {
                    if(isset($this->auth['empresa'])) {
                        $data = array();
                        $data['nombre'] = $nombre;
                        $data['url'] = $url;
                        $data['id_propietario'] = $this->auth['empresa']['id'];
                        $data['tipo'] = 'Empresa';
                        $alerta = new Application_Model_Alerta();


                        $id_propietario = $this->auth['empresa']['id'];
                        $tipo = 'Empresa';
                        $alertas = $alerta->getAlertas($id_propietario, $tipo);
                        $totalAlertas = count($alertas);
                        if($totalAlertas >= Application_Model_Alerta::LIMITES_DE_ALERTAS) {
                            $mensaje = 'Excede el limite de alertas';
                            $estado = 0;
                            $totalAlertas = 0;
                        } else {


                            $res = $alerta->insert($data);
                            if(!empty($res)) {
                                $mensaje = 'Tu alerta ha sido guardada satisfactoriamente';
                                $estado = 1;
                                $id = $res;
                                $pass = new Zend_Session_Namespace('pass');
                                $uri = $data['url'];
                                $purl = parse_url($uri);
                                parse_str($purl['query'], $vars);
                                unset($vars['token']);
                                $queryString = http_build_query($vars);
                                $url = 'http://' . $purl['host'] . $purl['path'] . '?' . $queryString . '&token=' . $pass->token;
                            } else {
                                $mensaje = 'No se pudo guardar';
                                $estado = 0;
                            }
                        }
                    } else {
                        $mensaje = 'No es empresa';
                        $estado = 0;
                    }
                }
            }
        } else {
            $mensaje = 'Token invalido';
            $estado = 0;
        }
        echo (Zend_Json::encode(array(
            'mensaje' => $mensaje,
            'estado' => $estado,
            'id' => $id,
            'url' => $url
        )));
    }

    public function eliminarAlertaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        if($this->_hash->isValid($this->_getParam('token'))) {
            $id = $this->_getParam('id');
            if(empty($id)) {
                $mensaje = 'Alerta no existe';
                $estado = 0;
            } else {
                $alerta = new Application_Model_Alerta();
                $res = $alerta->delete("id = $id");
                if(!empty($res)) {
                    $mensaje = 'Tu alerta ha sido eliminada satisfactoriamente';
                    $estado = 1;
                } else {
                    $mensaje = 'No se pudo eliminar';
                    $estado = 0;
                }
            }
        } else {
            $mensaje = 'Token invalido';
            $estado = 0;
        }
        echo (Zend_Json::encode(array(
            'mensaje' => $mensaje,
            'estado' => $estado
        )));
    }

}
