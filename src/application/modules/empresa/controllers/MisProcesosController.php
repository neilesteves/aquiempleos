<?php

class Empresa_MisProcesosController extends App_Controller_Action_Empresa
{
    //Buscamas
    private $_buscamasConsumerKey;
    private $_buscamasPublishUrl;
    private $_buscamasUrl;
    protected $_anuncioweb;
    protected $_empresa;
    protected $_tieneBuscador;
    protected $_tieneBolsaCVs;

    CONST MSJ_BLOQUEO_EXITOSO    = 'Postulante Bloqueado';
    CONST MSJ_BLOQUEO_REDUNDANTE = 'El postulante ya se encuentra bloqueado';
    CONST MSJ_BLOQUEO_INCOMPLETO = 'Ocurrio un error';
    CONST MSJ_AVISO_AMPLIADO     = 'Este aviso no se puede ampliar';

    public function init()
    {
        parent::init();
        if (Zend_Auth::getInstance()->hasIdentity() != true) {
            $this->_redirect('/empresa');
        }
        $this->_usuario = new Application_Model_Usuario();
        if (isset($this->auth['usuario']->id) && $this->_usuario->hasvailBlokeo($this->auth['usuario']->id)) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::forgetMe();
            $this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
            $this->_redirect('/empresa');
        }
        if (isset($this->auth['usuario']) &&
            $this->auth['usuario']->rol == Application_Model_Usuario::ROL_POSTULANTE) {
            $this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
            $this->_redirect('/');
        }
        $this->_anuncioweb = new Application_Model_AnuncioWeb();
        $this->_empresa    = new Application_Model_Empresa();

        $this->idEmpresa = $this->auth['empresa']['id'];
        $this->usuario   = $this->auth['usuario'];

        $this->_config = Zend_Registry::get('config');

        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'myAccount', 'class' => array(''))
        );

        $this->_tieneBolsaCVs = 0;
        if (isset($this->auth['empresa']['membresia_info']['membresia']['id_membresia'])) {
            $this->_tieneBolsaCVs = !empty($this->auth['empresa']['membresia_info']['beneficios']->bolsacv)
                    ? 1 : 0;
        }
        $this->view->tieneBolsaCVs = $this->_tieneBolsaCVs;
        $this->view->tieneBuscador = $this->_anuncioweb->beneficioBuscadorAptitus($this->auth);
        $this->view->Look_Feel     = $this->_empresa->LooFeelActivo($this->auth['empresa']['id']);
    }

    public function indexAction()
    {
        $verSugerenciaCandidatos             = $this->config->profileMatch->empresa->sugerencias;
        $this->view->verSugerenciaCandidatos = $verSugerenciaCandidatos;

        if ($this->_getParam('extender') != '') {
            Zend_Layout::getMvcInstance()->assign(
                'extender', $this->_getParam('extender')
            );
        }
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.misprocesos.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.pestana.js')
        );
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MIS_PROCESOS;
        $this->view->menu_sel_side = self::MENU_POST_SIDE_PROCESOS_ACTIVOS;

        $anuncioId           = $this->_getParam('cerrar', null);
        $usuarioEmpresaDatos = $this->auth['usuario-empresa'];

        $this->view->empresaMembresia = isset($this->auth['empresa']['membresia_info']['membresia']['id_membresia'])
                ? $this->auth['empresa']['membresia_info']['membresia']['id_membresia']
                : NULL;

        if (!is_null($anuncioId)) {
            $anuncio = new App_Service_Validate_Ad($anuncioId);
            if ($anuncio->isManaged($usuarioEmpresaDatos)) {
                if ($this->_hash->isValid($this->_getParam('csrfhash'))) {
                    $this->_anuncioweb->cerrarProcesoActivo($anuncioId);
                    $arrayInfoAW                          = $this->_anuncioweb->getUrlInfoById($anuncioId);
                    //$helperAviso = $this->_helper->getHelper('Aviso');
                    // $helperAviso->getSolarAviso()->DeleteAvisoSolr($anuncioId);
                    $modelSorl                            = new Solr_SolrAviso();
                    $modelSorl->delete($anuncioId);
                    $cache                                = $this->getCache();
                    $anuncio_web_cache                    = 'anuncio_web_'.$arrayInfoAW['url_id'];
                    $AnuncioWeb_getAvisoInfoById_cache    = 'AnuncioWeb_getAvisoInfoById_'.$anuncioId;
                    $AnuncioWeb_getAvisoInfoficha_cache   = 'AnuncioWeb_getAvisoInfoficha_'.$anuncioId;
                    $Empresa_getEmpresaHome_cache         = 'Empresa_getEmpresaHome_';
                    $AnuncioWeb_getAvisoById_cache        = 'AnuncioWeb_getAvisoById_'.$anuncioId;
                    $AnuncioWeb_getAvisoIdByUrl_cache     = 'AnuncioWeb_getAvisoIdByUrl_'.$arrayInfoAW['url_id'];
                    $AnuncioWeb_getAvisoIdByCreado__cache = 'AnuncioWeb_getAvisoIdByCreado_'.$arrayInfoAW['url_id'];
                    $AnuncioWeb_getAvisoIdByUrl_cache_api = 'AnuncioWeb_getAvisoIdByUrl_Api_'.$arrayInfoAW['url_id'].'_'.$this->auth["empresa"]["id"];

                    $idEmpresa           = $this->auth['empresa']['id'];
                    $cacheId             = 'AnuncioWeb_getAvisoIdByUrl_Api_'.$arrayInfoAW['url_id'].'_'.$idEmpresa;
                    $cacheIdestudios     = 'AnuncioWeb_getEstudiosByAnuncio_Api_'.$arrayInfoAW['url_id'].'_'.$idEmpresa;
                    $cacheIdexperiencias = 'AnuncioWeb_getExperienciasByAnuncio_Api_'.$arrayInfoAW['url_id'].'_'.$idEmpresa;
                    $cacheIdidiomas      = 'AnuncioWeb_getIdiomasByAnuncio_Api_'.$arrayInfoAW['url_id'].'_'.$idEmpresa;
                    $cacheIdprogramas    = 'AnuncioWeb_getIdiomasByAnuncio_Api_'.$arrayInfoAW['url_id'].'_'.$idEmpresa;
                    $cachePre            = 'AnuncioWeb_getPreguntas_Api_'.$arrayInfoAW['url_id'].'_'.$idEmpresa;

                    @$cache->remove($cacheId);

                    if ($cache->test($AnuncioWeb_getAvisoIdByUrl_cache_api)) {
                        @$cache->remove($anuncio_web_cache);
                    }
                    if ($cache->test($anuncio_web_cache)) {
                        @$cache->remove($anuncio_web_cache);
                    }
                    if ($cache->test($AnuncioWeb_getAvisoInfoById_cache)) {
                        @$cache->remove($AnuncioWeb_getAvisoInfoById_cache);
                    }
                    if ($cache->test($AnuncioWeb_getAvisoInfoficha_cache)) {
                        @$cache->remove($AnuncioWeb_getAvisoInfoficha_cache);
                    }
                    if ($cache->test($Empresa_getEmpresaHome_cache)) {
                        @$cache->remove($Empresa_getEmpresaHome_cache);
                    }
                    if ($cache->test($AnuncioWeb_getAvisoById_cache)) {
                        @$cache->remove($AnuncioWeb_getAvisoById_cache);
                    }
                    if ($cache->test($AnuncioWeb_getAvisoIdByUrl_cache)) {
                        @$cache->remove($AnuncioWeb_getAvisoIdByUrl_cache);
                    }
                    if ($cache->test($AnuncioWeb_getAvisoIdByCreado__cache)) {
                        @$cache->remove($AnuncioWeb_getAvisoIdByCreado__cache);
                    }

                    $this->getMessenger()->success('El aviso se cerro satisfactoriamente');
                    //exec("curl -X POST -d 'api_key=".$this->_buscamasConsumerKey."&nid=".$anuncioId."&site=".$this->_buscamasUrl."' ".$this->_buscamasPublishUrl);
                }
            }
        }

        $page = $this->_getParam('page', 1);
        $id   = $this->auth["empresa"]["id"];

        $this->view->col = $col             = $this->_getParam('col', '');
        $this->view->ord = $ord             = $this->_getParam('ord', 'DESC');

        $selectAvisos = $this->_anuncioweb->getMisProcesosActivos(
            $id, $col, $ord);

        $beneficios = $this->auth['empresa']['membresia_info']['beneficios'];
        $codigo     = Application_Model_Beneficio::CODE_REASIGNAR_PROCESOS;

        if (isset($beneficios->$codigo)) {
            $usuarioEmpresa = new App_Service_Validate_UserCompany;

            if (!$usuarioEmpresa->isCreator($usuarioEmpresaDatos)) {
                $selectAvisos = $this->_anuncioweb->obtenerAvisosPorAdministradorSecundario(
                    $id, $usuarioEmpresaDatos['id'], $col, $ord);
            };
        }

        //Beneficios avisos web destacado
        $dataDCVS                   = $this->_anuncioweb->webDestacadoBeneficioCVS($this->auth['empresa']['id']);
        $this->_avisosWebDestacados = count($dataDCVS);

//        $this->_tieneBolsaCVs = false;
//        if ($this->_avisosWebDestacados > 0)
//            $this->_tieneBolsaCVs = true;

        $this->view->tieneBolsaCVs = $this->_tieneBolsaCVs;

        $paginado = $this->config->empresa->misprocesos->paginadoactivos;

        $paginator = Zend_Paginator::factory($selectAvisos);

        $paginator->setItemCountPerPage($paginado);

        $this->view->mostrando       = "Mostrando "
            .$paginator->getItemCount($paginator->getItemsByPage($page))." de "
            .$paginator->getTotalItemCount();
        $paginator->setCurrentPageNumber($page);
        $this->view->procesosactivos = $paginator;
        $path                        = $this->getRequest()->getServer('REQUEST_URI');
        $this->view->redirect        = $this->_helper->Aviso->EncodeRedirect($path);
    }

    private function membresiaActual()
    {
        $mMembresia = new Application_Model_Membresia();

        $this->view->respMembresia = 0;

        if (isset($this->auth['empresa']['membresia_info']['membresia']['m_nombre'])) {
            $m_nombre = $this->auth['empresa']['membresia_info']['membresia']['m_nombre'];
            $m_tipo   = $this->auth['empresa']['membresia_info']['membresia']['m_tipo'];

            if ($mMembresia::M_TIPO == $m_tipo) {
                if ($mMembresia::M_NOMBRE_PREMIUM == $m_nombre ||
                    $mMembresia::M_NOMBRE_SELECTO == $m_nombre) {
                    $this->view->respMembresia = 1;
                }
            }
        }
    }

    public function procesosCerradosAction()
    {
        $verSugerenciaCandidatos             = $this->config->profileMatch->empresa->sugerencias;
        $this->view->verSugerenciaCandidatos = $verSugerenciaCandidatos;

        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.pestana.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.misprocesos.js')
        );
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MIS_PROCESOS;
        $this->view->menu_sel_side = self::MENU_POST_SIDE_PROCESOS_CERRADOS;

        $page = $this->_getParam('page', 1);
        $id   = $this->auth["empresa"]["id"];

        $this->view->col = $col             = $this->_getParam('col', '');
        $this->view->ord = $ord             = $this->_getParam('ord');

        $usuarioEmpresaDatos = $this->auth['usuario-empresa'];

        $selectAvisos = $this->_anuncioweb->obtenerMisProcesosCerrados(
            $id, $col, $ord);

        $beneficios = $this->auth['empresa']['membresia_info']['beneficios'];
        $codigo     = Application_Model_Beneficio::CODE_REASIGNAR_PROCESOS;

        if (isset($beneficios->$codigo)) {
            $usuarioEmpresa = new App_Service_Validate_UserCompany;

            if (!$usuarioEmpresa->isCreator($usuarioEmpresaDatos)) {
                $selectAvisos = $this->_anuncioweb->obtenerAvisosCerradosPorAdministradorSecundario(
                    $id, $usuarioEmpresaDatos['id'], $col, $ord);
            };
        }

        $paginado = $this->config->empresa->misprocesos->paginadoactivos;

        $paginator = Zend_Paginator::factory($selectAvisos);
        $paginator->setItemCountPerPage($paginado);

        $this->view->mostrando        = "Mostrando "
            .$paginator->getItemCount($paginator->getItemsByPage($page))." de "
            .$paginator->getTotalItemCount();
        $paginator->setCurrentPageNumber($page);
        $this->view->procesoscerrados = $paginator;
    }

    public function borradoresAction()
    {
        $verSugerenciaCandidatos             = $this->config->profileMatch->empresa->sugerencias;
        $this->view->verSugerenciaCandidatos = $verSugerenciaCandidatos;

        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.pestana.js')
        );
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MIS_PROCESOS;
        $this->view->menu_sel_side = self::MENU_POST_SIDE_PROCESOS_BORRADORES;

        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.misprocesos.js')
        );

        $page = $this->_getParam('page', 1);
        $id   = $this->auth["empresa"]["id"];

        $eliminar = $this->_getParam('eliminar', false);

//        $modelAI = new Application_Model_AnuncioImpreso();
//        $arrayAI = $modelAI->getIdAvisosByIdImpreso($eliminar);

        $arrayAI = $this->_anuncioweb->getAvisoById($eliminar);

        //if ($this->_helper->Aviso->perteneceAvisoImpresoEmpresa($eliminar, $id)) {
        if ($this->_helper->Aviso->perteneceAvisoAEmpresa($eliminar, $id)) {

            if ($this->_hash->isValid($this->_getParam('csrfhash'))) {

                $this->_anuncioweb->eliminarProcesoActivo($arrayAI['id']);

                $arrayCip = $this->_anuncioweb->getCipByIdImpreso($eliminar);
                if ($arrayCip != false && $arrayCip['cip'] != null) {
                    $this->_helper->WebServiceCip->eliminarCip($arrayCip['cip']);
                }
            }
        } elseif (!empty($arrayAI)) {
            if ($this->_hash->isValid($this->_getParam('csrfhash'))) {

                $this->_anuncioweb->eliminarProcesoActivo($arrayAI['id']);

                $arrayCip = $this->_anuncioweb->getCipByIdImpreso($eliminar);
                if ($arrayCip != false && $arrayCip['cip'] != null) {
                    $this->_helper->WebServiceCip->eliminarCip($arrayCip['cip']);
                }
            }
        }
        $this->view->col = $col             = $this->_getParam('col', '');
        $this->view->ord = $ord             = $this->_getParam('ord', 'DESC');

        $paginator             = $this->_anuncioweb->getPaginatorProcesosBorradores(
            $id, $col, $ord
        );
        $this->view->mostrando = "Mostrando "
            .$paginator->getItemCount($paginator->getItemsByPage($page))." de "
            .$paginator->getTotalItemCount();
        $paginator->setCurrentPageNumber($page);

        $this->view->procesosborradores = $paginator;
        $this->view->redirect           = $this->_helper->Aviso->EncodeRedirect('empresa/mis-procesos/borradores');
    }

    public function verHistorialAction()
    {
        $this->_helper->layout->disableLayout();

        $id = $this->_getParam('id');
        if ($id != "") {
            $historial = $this->_anuncioweb->getHistorial($id);
            $anuncio   = $this->_anuncioweb->getAvisoById($id);

            $this->view->historial = $historial;
            $this->view->anuncio   = $anuncio;
        }
    }

    public function verProcesoAction()
    {

        $config            = $this->getConfig();
        $cantidadRegistros = 5;//$config->app->registroReferidos->numeroRegistros;

        $id = $this->getRequest()->getParam("id", false);

        if (!is_numeric($id)) {
            $this->getMessenger()->error('El proceso solicitado no existe o no es válido');
            $this->_redirect('/empresa/mis-procesos/');
        }

        $procesConfig = "var ProcessConfig = ".
            Zend_Json_Encoder::encode(
                array(
                    'idaviso' => $id,
                    'cntreferidos' => $cantidadRegistros
                )
        );

        $this->view->headScript()->appendScript($procesConfig);

        $anuncio = new App_Service_Validate_Ad($id);

        $usuarioEmpresa = $this->auth['usuario-empresa'];

        if (!$anuncio->isManaged($usuarioEmpresa)) {
            $this->getMessenger()->error($anuncio->getMessage());
            $this->_redirect('/empresa/mi-cuenta');
        }

        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/perfil.postulante.js')
        );

        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.verproceso.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.misprocesos.js')
        );

        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.bolsacvsgeneral.js')
        );

        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'profilePublic')
        );

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

        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MIS_PROCESOS;
        $this->view->col           = $col                       = $this->_getParam('col',
            '');
        $this->view->ord           = $ord                       = $this->_getParam('ord');
        $page                      = $this->_getParam('page', 1);
        $idEmpresa                 = $this->auth["empresa"]["id"];
        $categoria                 = $this->_getParam('categoria');

        $nombreCat = '';
        if (!empty($categoria)) {
            $categoriaPostulacion = new Application_Model_CategoriaPostulacion();
            $nombreCategoria      = $categoriaPostulacion->fetchRow('id ='.$categoria);
            $nombreCat            = $nombreCategoria['nombre'];
        }

        $this->view->categoria = $categoria;
        $paginator             = $this->_anuncioweb->getPaginatorListarProceso($id,
            $col, $ord, $categoria);
        $paginator->setCurrentPageNumber($page);
        $data                  = array();
        foreach ($paginator as $item) {
            $data[] = $item;
            if (!empty($item['destacado'])) {
                $data                   = array();
                $data['id_postulante']  = $item['idpostulante'];
                $data['id_empresa']     = $this->auth['empresa']['id'];
                $data['tipo']           = 2;
                $data['id_aviso']       = $item['id'];
                $data['fecha_busqueda'] = date('Y-m-d H:i:s');
                $visitas                = new Application_Model_Visitas();
                $res                    = $visitas->insert($data);
            }
        }

        $this->view->dtproceso    = $data;
        $this->view->proceso      = $paginator;
        $this->view->idanuncio    = $id;
        $this->view->pagina       = $page;
        $postulacion              = new Application_Model_Postulacion();
        $this->view->npostulantes = $postulacion->getPostulantesByAviso($id,
            "1", true);
        $this->view->estadistica  = true;



        $this->view->mostrando = "Mostrando "
            .$paginator->getItemCount($paginator->getItemsByPage($page))." de "
            .$paginator->getTotalItemCount();

        $categoriapostulacion                      = new Application_Model_CategoriaPostulacion();
        $this->view->categoriaPostulacion          = $categoriapostulacion->getCategoriaPostulacion(
            $idEmpresa
        );
        //   var_dump(  $this->view->categoriaPostulacion,$idEmpresa  );Exit;
        $this->view->numeroPostulacionesxCategoria = $categoriapostulacion->getNumeroCategoriasPostulaciones(
            $id, $idEmpresa, true
        );

        $_aviso              = new Application_Model_AnuncioWeb();
        $registroAviso       = $_aviso->getAvisoById($id, FALSE);
        $this->view->idAviso = $id;
        $tipoAviso           = $registroAviso['tipo'];

        $this->view->assign('aviso', $registroAviso);
        $this->view->url_id = $registroAviso["url_id"];
        $this->view->slug   = $registroAviso["slug"];
        $this->view->id     = $registroAviso["id"];

        $this->view->vigenciaAvisoPreferencial = $_aviso->vigenciaAvisoPreferencial($id);


        //Limitar
        $this->view->tipoAviso        = $tipoAviso;
        $this->view->empresaMembresia = isset($this->auth['empresa']['membresia_info']['membresia']['id_membresia'])
                ? $this->auth['empresa']['membresia_info']['membresia']['id_membresia']
                : NULL;
        $nivelestudios                = $postulacion->getNivelesEstudiosBuscadorEmpresa(true);
        $nivelOtrosestudios           = $postulacion->getNivelesOrtrosEstudiosBuscadorEmpresa(true,
            9);
        $edades                       = $postulacion->getEdadesBuscadorEmpresa($id);
        $sexo                         = $postulacion->getSexoBuscadorEmpresa();
        $idiomas                      = $postulacion->getIdiomasBuscadorEmpresa($id);
        $programas                    = $postulacion->getProgramasBuscadorEmpresa($id);
        $anosexperiencia              = $postulacion->getAnosExperienciasBuscadorEmpresa($id);
        $tipocarrera                  = $postulacion->getTipoCarreraBuscadorEmpresa($id);
        $ubicacion                    = $postulacion->getUbicacionBuscadorEmpresa($id);
        $origenPostulacion            = $postulacion->getOrigenPostulacion($paginator);
        $conadis                      = $postulacion->getPostulacionDiscapacidad($paginator);


        $this->view->dataFiltros = array(
            "niveldeestudios" => array(
                'titulo' => $this->config->buscadorempresa->urls->niveldeestudios,
                'param' => $this->config->buscadorempresa->param->niveldeestudios,
                'icon' => 'icon_medal',
                'data' => $this->_prepare($nivelestudios, 7),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "niveldeOtrosestudios" => array(
                'titulo' => $this->config->buscadorempresa->urls->niveldeOtrosestudios,
                'param' => $this->config->buscadorempresa->param->niveldeOtrosestudios,
                'icon' => 'icon_education',
                'data' => $this->_prepare($nivelOtrosestudios, 7),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "tipodecarrera" => array(
                'titulo' => $this->config->buscadorempresa->urls->tipodecarrera,
                'param' => $this->config->buscadorempresa->param->tipodecarrera,
                'icon' => 'icon_star',
                'data' => $this->_prepare($tipocarrera, 10),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "experiencia" => array(
                'titulo' => $this->config->buscadorempresa->urls->experiencia,
                'param' => $this->config->buscadorempresa->param->experiencia,
                'icon' => 'icon_star',
                'data' => $anosexperiencia,
                'expandible' => 0,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "idiomas" => array(
                'titulo' => $this->config->buscadorempresa->urls->idiomas,
                'param' => $this->config->buscadorempresa->param->idiomas,
                'icon' => 'icon_message',
                'data' => $idiomas,
                'expandible' => 0,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "programas" => array(
                'titulo' => $this->config->buscadorempresa->urls->programas,
                'param' => $this->config->buscadorempresa->param->programas,
                'icon' => 'icon_monitor',
                'data' => $programas,
                'expandible' => 0,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "edad" => array(
                'titulo' => $this->config->buscadorempresa->urls->edad,
                'param' => $this->config->buscadorempresa->param->edad,
                'icon' => 'icon_star',
                'data' => $edades,
                'expandible' => 0,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "sexo" => array(
                'titulo' => $this->config->buscadorempresa->urls->sexo,
                'param' => $this->config->buscadorempresa->param->sexo,
                'icon' => 'icon_star',
                'data' => $sexo,
                'expandible' => 0,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "ubicacion" => array(
                'titulo' => $this->config->buscadorempresa->urls->ubicacion,
                'param' => $this->config->buscadorempresa->param->ubicacion,
                'icon' => 'icon_position',
                'data' => $this->_prepare($ubicacion, 10),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "postulacion" => array(
                'titulo' => $this->config->buscadorempresa->urls->postulacion,
                'param' => $this->config->buscadorempresa->param->postulacion,
                'icon' => 'icon_star',
                'data' => $this->_prepare($origenPostulacion, 7),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "conadis" => array(
                'titulo' => $this->config->buscadorempresa->urls->conadis_code,
                'param' => $this->config->buscadorempresa->param->conadis_code,
                'icon' => 'icon_star',
                'data' => $this->_prepare($conadis, 7),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->conadis_code,
                'enumeraciones' => $this->config->enumeraciones
            )
        );

        //Logica para restringir las acciones en ver proceso
        $anuncio            = $_aviso->find($id);
        $f                  = $anuncio[0]->fh_vencimiento_proceso;
        if ($f == "0000-00-00" || $f == null) $f                  = "now";
        $fv                 = new DateTime($f);
        $fa                 = new DateTime("now");
        $dias               = $fa < $fv;
        $this->view->online = $dias == true ? 1 : 0;
        $this->view->puesto = $anuncio[0]->puesto;

        //---------------------------------------------------------------------
        $this->membresiaActual();
        //---------------------------------------------------------------------
        $pass              = new Zend_Session_Namespace('pass');
        $this->view->token = $pass->token;
    }

    public function getNumeroCategoriasPostulacionesAjaxAction()
    {
        $categoriapostulacion          = new Application_Model_CategoriaPostulacion();
        $idEmpresa                     = $this->auth["empresa"]["id"];
        $id                            = $this->getRequest()->getParam("id");
        $categoriaPostulacion          = $categoriapostulacion->getCategoriaPostulacion(
            $idEmpresa
        );
        $numeroPostulacionesxCategoria = $categoriapostulacion->getNumeroCategoriasPostulaciones($id,
            $idEmpresa);

        $postulacion  = new Application_Model_Postulacion();
        $nPostulantes = $postulacion->getPostulantesByAviso($id, "1");
        $arreglo      = "";
        foreach ($categoriaPostulacion as $item) {
            $arreglo[] = $numeroPostulacionesxCategoria[$item["id"]];
        }

        $arreglo[] = array("id" => '-1', "n" => $nPostulantes);

        echo json_encode($arreglo);
        exit;
    }

    public function verProcesoAjaxAction()
    {

        $this->_helper->layout->disableLayout();
        //$zl = new ZendLucene();
        $id              = $this->getRequest()->getParam("id");
        $this->view->col = $col             = $this->_getParam('col', '');
        $this->view->ord = $ord             = $this->_getParam('ord');
        $page            = $this->_getParam('page', 1);
        $check           = $this->_getParam('check');
        $idEmpresa       = $this->auth["empresa"]["id"];
        $categoria       = $this->_getParam('categoria');
        $opcion          = $this->_getParam('listaropcion');
        $conadis         = $this->_getParam('conadis_code');

        $anuncio        = new App_Service_Validate_Ad((int) $id);
        $usuarioEmpresa = (array) $this->auth['usuario-empresa'];
        if (!$anuncio->isManaged($usuarioEmpresa)) {
            $this->getMessenger()->error($anuncio->getMessage());
            $this->_redirect('/empresa/mi-cuenta');
        }

        $registroAviso                = $this->_anuncioweb->getAvisoById($id);
        $tipoAviso                    = $registroAviso['tipo'];
        $empresaMembresia             = isset($this->auth['empresa']['membresia_info']['membresia']['id_membresia'])
                ? $this->auth['empresa']['membresia_info']['membresia']['id_membresia']
                : NULL;
        $this->view->empresaMembresia = $empresaMembresia;
        $viewHelperFlag               = new App_View_Helper_LimitarOpcionesProceso();

        $this->view->flagAcciones = $viewHelperFlag->LimitarOpcionesProceso($empresaMembresia,
                $tipoAviso)
            ->showAccionesProceso();

        $this->view->idanuncio   = $id;
        $this->view->pagina      = $page;
        $this->view->categoria   = $categoria;
        $this->view->opcionlista = $opcion;

        $categoriapostulacion             = new Application_Model_CategoriaPostulacion();
        $this->view->categoriaPostulacion = $categoriapostulacion->getCategoriaPostulacion(
            $idEmpresa
        );

        //Mover a etapa
        $opcionA = $this->_getParam('opcion');
        if (isset($opcionA)) {
            $valores     = $this->_getParam('valores');
            $action      = $this->_helper->getHelper("RegistrosExtra");
            $postulacion = new Application_Model_Postulacion();
            foreach ($valores as $item) {
                $action->moverAEtapaPostulacion($item, $opcionA);
                //$zl->updateIndexPostulaciones($item, 'idcategoriapostulacion', $opcionA);
                $postulacion->update(
                    array('es_nuevo' => 0),
                    $postulacion->getAdapter()->quoteInto('id = ?', $item)
                );
            }
        }
        //--- fin mover etapa.$cadenabusqueda
        //Descartar postulacion
        $opcionB = $this->_getParam('descartar');
        if (isset($opcionB)) {
            $valores = $this->_getParam('descartar');
            $action  = $this->_helper->getHelper("RegistrosExtra");
            foreach ($valores as $item) {
                $action->descartarPostulacion($item);
                $this->_helper->Aviso->actualizarPostulantes($id);
                //$zl->updateIndexPostulaciones($item, 'descartado', '1');
            }
        }
        //--- fin descartar postulacion
        //Restituir postulacion
        $opcionC = $this->_getParam('restituir');
        if (isset($opcionC)) {
            $valores = $this->_getParam('restituir');
            $action  = $this->_helper->getHelper("RegistrosExtra");
            foreach ($valores as $item) {
                $action->restituirPostulacion($item);
                $this->_helper->Aviso->actualizarPostulantes($id);
            }
        }
        //--- fin restituir postulacion
        //Mas Acciones postulacion
        $opcionD = $this->_getParam('masacciones');
        if (isset($opcionD)) {
            $valores = $this->_getParam('masacciones');
            $flag    = $this->_getParam('flag');
            if ($flag == 0 || $flag == 1) {
                $opcion = $this->_getParam('opcionactual');
            }
            $modelo = new Application_Model_Postulacion();

            foreach ($valores as $item) {
                $modelo->marcarPostulacion($item, $flag);
                //$zl->updateIndexPostulaciones($item, 'esnuevo', $flag);
            }

            $avisoHelper = $this->_helper->getHelper('Aviso');
            $avisoHelper->actualizarNuevasPostulaciones($id);
        }
        //--- fin mas acciones postulacion
        //cadenas del BUSCADOR
        $param                = $this->config->buscadorempresa->param;
        $niveldeestudios      = $this->_getParam($param->niveldeestudios);
        $niveldeOtrosestudios = $this->_getParam($param->niveldeOtrosestudios);
        $tipodecarrera        = $this->_getParam($param->tipodecarrera);
        $experiencia          = $this->_getParam($param->experiencia);
        $idiomas              = $this->_getParam($param->idiomas);
        $programas            = $this->_getParam($param->programas);
        $edad                 = $this->_getParam($param->edad);
        $sexo                 = $this->_getParam($param->sexo);
        $ubicacion            = $this->_getParam($param->ubicacion);
        $origenPostulacion    = $this->_getParam($param->postulacion);
        $conadis_code         = $this->_getParam($param->conadis_code);
        $query                = $this->_getParam($param->query);
        $cadenabusqueda       = (($query != "") ?
                ($param->query."/".$query."/") : "").
            (($niveldeestudios != "") ?
                ($param->niveldeestudios."/".$niveldeestudios."/") : "").
            (($niveldeOtrosestudios != "") ?
                ($param->niveldeOtrosestudios."/".$niveldeOtrosestudios."/") : "").
            (($tipodecarrera != "") ?
                ($param->tipodecarrera."/".$tipodecarrera."/") : "").
            (($experiencia != "") ?
                ($param->experiencia."/".$experiencia."/") : "").
            (($idiomas != "") ?
                ($param->idiomas."/".$idiomas."/") : "").
            (($programas != "") ?
                ($param->programas."/".$programas."/") : "").
            (($edad != "") ?
                ($param->edad."/".$edad."/") : "").
            (($sexo != "") ?
                ($param->sexo."/".$sexo."/") : "").
            (($ubicacion != "") ?
                ($param->ubicacion."/".$ubicacion."/") : "").
            (($origenPostulacion != "") ?
                ($param->postulacion."/".$origenPostulacion) : "");

        $this->view->cadenabusqueda = $cadenabusqueda;
        //fin cadenas BUSCADOR
        if ($check != 'false' && $page == 1) {
            $this->_helper->LogActualizacionBI->logActualizacionBuscadorAviso(
                $this->_getAllParams(), $idEmpresa,
                Application_Model_LogBusqueda::TIPO_BUSCADOR_PROCESO
            );
        }

        $_niveldeestudios      = ($niveldeestudios != "") ? explode("--",
                $niveldeestudios) : "";
        $_niveldeOtrosestudios = ($niveldeOtrosestudios != "") ? explode("--",
                $niveldeOtrosestudios) : "";
        $_tipodecarrera        = ($tipodecarrera != "") ? explode("--",
                $tipodecarrera) : "";
        $_experiencia          = ($experiencia != "") ? explode("--",
                $experiencia) : "";
        $_idiomas              = ($idiomas != "") ? explode("--", $idiomas) : "";
        $_programas            = ($programas != "") ? explode("--", $programas) : "";
        $_edad                 = ($edad != "") ? explode("--", $edad) : "";
        $_sexo                 = ($sexo != "") ? explode("--", $sexo) : "";
        $_ubicacion            = ($ubicacion != "") ? explode("--", $ubicacion) : "";
        $_postulacion          = ($origenPostulacion != "") ? explode("--",
                $origenPostulacion) : "";
        $_conadis              = ($conadis_code != "") ? explode("--",
                $conadis_code) : "";
        $paginator             = $this->_anuncioweb->getPaginatorListarProceso(
            $id, $col, $ord, $categoria, $opcion, $_niveldeestudios,
            $_niveldeOtrosestudios, $_tipodecarrera, $_experiencia, $_idiomas,
            $_programas, $_edad, $_sexo, $_ubicacion, $query, $_postulacion,
            $_conadis
        );
        $paginator->setCurrentPageNumber($page);
        $dt                    = array();
        foreach ($paginator as $item) {
            $dt[] = $item;
            if (!empty($item['destacado'])) {
                $data                   = array();
                $data['id_postulante']  = $item['idpostulante'];
                $data['id_empresa']     = $this->auth['empresa']['id'];
                $data['tipo']           = 2;
                $data['id_aviso']       = $item['id'];
                $data['fecha_busqueda'] = date('Y-m-d H:i:s');
                $visitas                = new Application_Model_Visitas();
                $res                    = $visitas->insert($data);
            }
        }
        $this->view->dtproceso = $dt;
        $this->view->proceso   = $paginator;
        $this->view->mostrando = "Mostrando "
            .$paginator->getItemCount($paginator->getItemsByPage($page))." de "
            .$paginator->getTotalItemCount();

        $_aviso             = new Application_Model_AnuncioWeb();
        $anuncio            = $_aviso->find($id);
        $f                  = $anuncio[0]->fh_vencimiento_proceso;
        if ($f == "0000-00-00" || $f == null) $f                  = "now";
        $fv                 = new DateTime($f);
        $fa                 = new DateTime("now");
        $dias               = $fa < $fv;
        $this->view->online = $dias == true ? 1 : 0;

        //echo $dias; exit;

        $pagina = $this->view->render('mis-procesos/_main_verproceso.phtml');

        echo $pagina;
        die();
    }

    public function agregarNotasVerProcesoAction()
    {
        //echo "hola";die;
        $this->_helper->layout->disableLayout();
        $config = Zend_Registry::get("config");
        $nota   = $this->_getAllParams();

        $idPostulacion = $nota['id'];
        $idAnuncio     = $nota['idanuncio'];

        $this->view->id = $idPostulacion;

        $this->view->idAnuncio = $idAnuncio;

        $idUsuario = $this->auth['usuario']->id;

        $objPostulacion = new Application_Model_Postulacion();
        $row            = $objPostulacion->getPostulacionPostulante($idPostulacion);

        $this->view->nombrePostulante = $row["nombres"]." ".$row["apellidos"];

        $objAnuncio                = new Application_Model_AnuncioWeb();
        $row                       = $objAnuncio->find($idAnuncio);
        $this->view->nombreAnuncio = $row[0]["puesto"];

        $this->view->fecha = date("d/m/Y");
        $formNota          = new Application_Form_Nota();

        $texto = $this->_getParam("text");
        if ((trim($texto) != "" || $formNota->path->getFileName() != null)) {

            //@codingStandardsIgnoreStart
            $nota["text"] = preg_replace($config->avisopaso2->expresionregular,
                '', $nota["text"]);
            //@codingStandardsIgnoreEnd

            $nota["text"] = str_replace("@", "", $nota["text"]);
            $modelNota    = new Application_Model_Nota();
            $validNota    = $formNota->isValid($nota);
//            if ($validNota) {
            //rename
            $file         = $formNota->path->getFileName();
            //echo"hola";die();
            if ($file != null) {
                $nombreOriginal = pathinfo($file);
                $ext            = $nombreOriginal['extension'];

                $rename      = date('YmdHis')."_".md5(time());
                $nuevoNombre = $rename.".".$ext;

                $formNota->path->addFilter('Rename', $nuevoNombre);
                $formNota->path->receive();
            } else {
                $nombreOriginal = null;
                $nuevoNombre    = null;
            }

            $valuesNota                   = $formNota->getValues($nota);
            $valuesNota['id_postulacion'] = $idPostulacion;
            $valuesNota['id_usuario']     = $idUsuario;
            $valuesNota['fh']             = date('Y-m-d H:i:s');
            $valuesNota['path_original']  = $nombreOriginal['basename'];
            $valuesNota['path']           = $nuevoNombre;
            unset($valuesNota['fecha']);
            $success                      = $modelNota->insert($valuesNota);
            exit;
            //          }
        }
        $this->view->formNota = $formNota;
    }

    public function agregarMensajeVerProcesoAction()
    {
        $this->_helper->layout->disableLayout();

        $idEmpresa     = $this->auth['usuario']->id;
        $formMensaje   = new Application_Form_Mensajes();
        $modelMen      = new Application_Model_Mensaje();
        $postulaciones = $this->_getParam("postulaciones");
        if ($this->_request->isPost() && isset($postulaciones)) {
            $mensaje                 = $this->_getAllParams();
            $validMen                = $formMensaje->isValid($mensaje);
            $valuesMensaje           = $formMensaje->getValues($mensaje);
            $valuesMensaje["cuerpo"] = str_replace("@", "",
                $valuesMensaje["cuerpo"]);

            if ($valuesMensaje['cuerpo'] != "") {
                $fecha               = date('Y-m-d H:i:s');
                $valuesMensaje['de'] = $idEmpresa;

                $valuesMensaje['fh']           = $fecha;
                $tipomensaje                   = $this->_getParam("tipo_mensaje");
                $valuesMensaje['tipo_mensaje'] = $tipomensaje == "false" ?
                    Application_Model_Mensaje::ESTADO_MENSAJE :
                    Application_Model_Mensaje::ESTADO_PREGUNTA;

                $valuesMensaje['leido']        = 0;
                $valuesMensaje['respondido']   = 0;
                $valuesMensaje['notificacion'] = 0;
                unset($valuesMensaje['id_mensaje']);
                unset($valuesMensaje['token']);

                $obj = new Application_Model_Postulacion();
                foreach ($postulaciones as $item) {
                    $valuesMensaje['id_postulacion'] = $item;
                    $row                             = $obj->getPostulacionPostulante($item);
                    $idUsuario                       = $row["idusuario"];
                    $valuesMensaje['para']           = $idUsuario;
                    $modelMen->insert($valuesMensaje);

                    //enviamos emilio.
                    $modelPostulacion = new Application_Model_Postulacion();
                    $arrayPostulacion = $modelPostulacion->getPostulacion($item);

                    $modelPostulante = new Application_Model_Postulante();
                    $arrayPostulante = $modelPostulante->getPostulante(
                        $arrayPostulacion['id_postulante']
                    );
                    $modelAw         = new Application_Model_AnuncioWeb();
                    $arrayAnuncioWeb = $modelAw->getAvisoById(
                        $arrayPostulacion['id_anuncio_web']
                    );
                    $url             = $this->view->url(
                        array(
                        'slug' => $arrayAnuncioWeb['slug'],
                        'url_id' => $arrayAnuncioWeb['url_id']
                        ), 'aviso', true
                    );
                    //$nurl = base64_encode("/postulaciones/index/url/1".$url);
                    $this->_helper->mail->mensajePostulante(
                        array(
                            'to' => $arrayPostulante['email'],
                            'email' => $arrayPostulante['email'],
                            'nombre' => ucwords($arrayPostulante['nombres']),
                            'empresa' => $arrayAnuncioWeb['nombre_comercial'],
                            'mostrarEmpresa' => $arrayAnuncioWeb['mostrar_empresa'],
                            'puesto' => $arrayAnuncioWeb['puesto'],
                            'mensaje' => $valuesMensaje['cuerpo']
                        //,'url' => $this->config->app->siteUrl."/postulaciones/index/url/".$nurl
                        )
                    );

                    //actualizamos postulaciones
                    $this->_helper->Mensaje->actualizarCantMsjsPostulacion(
                        $idUsuario, $item
                    );
                }
                exit;
            }
        }
        $this->view->form = $formMensaje;
    }

    private function _prepare($data, $n)
    {
        $dataChunks = array_chunk($data, $n);
        $nchunks    = count($dataChunks);
        $ocultos    = $nchunks > 1 ? array_slice($dataChunks, 1, $nchunks - 1) : array(
            );
        return array(
            'visible' => count($dataChunks) ? $dataChunks[0] : array(),
            'ocultos' => $ocultos
        );
    }

    public function perfilPublicoEmpAction()
    {
        $idAviso                      = $this->_getParam('idAviso');
        $this->view->idaviso=$idAviso;
        $_aviso                       = new Application_Model_AnuncioWeb();
        $registroAviso                = $_aviso->getAvisoById($idAviso, FALSE);
        $tipoAviso                    = $registroAviso['tipo'];
        $this->view->tipoAviso        = $tipoAviso;
        $this->view->empresaMembresia = (!empty($this->auth['empresa']['membresia_info']['membresia']['id_membresia']))
                ? $this->auth['empresa']['membresia_info']['membresia']['id_membresia']
                : "";
        //echo $tipoAviso;die;

        $postulacion               = new Application_Model_Postulacion();
        //Postulación Datos
        //-----------------------------------------------------------------------------
        $idPostulacion             = $this->_getParam('id');
        $arrayPostulacion          = $postulacion->getPostulacion($idPostulacion);
        $modeloPostulanteBloqueado = new Application_Model_EmpresaPostulanteBloqueado();

        $getBloqueado = $modeloPostulanteBloqueado->
            obtenerPorEmpresaYPostulante(
            (int) $this->auth['empresa']['id'],
            $arrayPostulacion['id_postulante'], array('id' => 'id')
        );

        $this->view->btnBloqueado = 0;

        if ((int) $getBloqueado['id'] > 0) {
            $this->view->btnBloqueado = 1;
        }

        //-----------------------------------------------------------------------------
        $modelAw      = new Application_Model_AnuncioWeb();
        $codeBuscador = Application_Model_Beneficio::CODE_BUSCADOR;


        $tieneBuscadorAptitus = false;

        if (!empty($this->auth['empresa']['membresia_info']['beneficios']->buscador)) {
            $tieneBuscadorAptitus = true;
        }

        $this->view->option   = $tieneBuscadorAptitus;
        $Idpost=$this->_getParam('id');
        $this->view->idactual = $Idpost;
         $idPostulante = $arrayPostulacion['id_postulante'];
        $postulaciones = $this->_getParam("postulaciones", "NULL");
        $this->view->paginado=array();
        if ($postulaciones  != 'NULL') {
            $pagespostulaciones = explode("-", $postulaciones);
            $this->view->paginado=$pagespostulaciones;
        }

        $ids = $this->_getParam("ids", "NULL");
        if ($ids != "NULL") {
            $siguiente = explode("-", $ids);
            if (count($siguiente) > 0) {
                $this->view->siguiente = $siguiente[0];
                if (count($siguiente) > 1) {
                    $ids             = array_reverse($siguiente);
                    $nuevo           = array_splice($ids, 0, count($ids) - 1);
                    $nuevo           = implode("-", array_reverse($nuevo));
                    $this->view->ids = $nuevo;
                } else {
                    $this->view->ids = "NULL";
                }
            }
        }

        $idsback     = $this->_getParam("idsback", "NULL");


        $this->view->idsback = $idsback;
        /// var_dump($idsback);exit;
        /**/
        if ($idsback != "NULL") {
            $anterior = explode("-", $idsback);
            if (count($anterior) > 0) {
                $anterior             = array_reverse($anterior);
                $this->view->anterior = $anterior[0];
                if (count($anterior) > 1) {
                    $idsback             = array_reverse($anterior);
                    $nuevo               = implode("-",
                        array_splice($idsback, 0, count($idsback) - 1));
                    $this->view->idsback = $nuevo;
                } else {
                    $this->view->idsback = "NULL";
                }
            }
        }


        //$zl = new ZendLucene();
        //$this->_helper->layout()->disableLayout();
        //$this->_helper->viewRenderer->setNoRender();
        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MIS_PROCESOS;
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'profilePublic')
        );

        $procesConfig = "var ProcessConfig = ".
            Zend_Json_Encoder::encode(
                array(
                    'idaviso' => $idAviso,
                    'cntreferidos' => ''
                )
        );

        $this->view->headScript()->appendScript($procesConfig);



        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.verproceso.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.misprocesos.js')
        );

        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.bolsacvsgeneral.js')
        );

        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/perfil.postulante.js')
        );

//            $this->view->headScript()->appendFile(
//                    $this->view->S(
//                            '/js/src/modulo/empresa.js')
//            );
        $this->view->headScript()->appendScript("AptitusPerfil();");

        if ($arrayPostulacion['es_nuevo'] == 1) {
            $postulacion->update(
                array('es_nuevo' => 0),
                $postulacion->getAdapter()->quoteInto('id = ?', $idPostulacion)
            );
            //$zl->updateIndexPostulaciones($idPostulacion, 'esnuevo', "0");
        }


        //Postulante Datos

        $postulante   = new Application_Model_Postulante();
        $perfil       = $postulante->getPerfil($idPostulante);

        if ($perfil == false) {
            $this->_redirect('/');
        }

        if (!empty($perfil['postulante']['destacado'])) {
            if ($arrayPostulacion['es_nuevo'] == 1) {

//                    try {
//                        /// Envio al SQS para notificacion PUSH
//                        $objMensaje['usuario'] = 'postulante';
//                        $objMensaje['nombre'] = mb_convert_case($perfil['postulante']['nombres'], MB_CASE_TITLE);
//                        $objMensaje['id'] = $perfil['postulante']['slug'];
//                        $objMensaje['tipo'] = 'vio-tu-perfil';
//                        $objMensaje['empresa'] = mb_convert_case($registroAviso['nombre_empresa'], MB_CASE_TITLE);
//                        $colas = new Amazon_Sqs_NotificacionPostulante();
//                        $mensaje = base64_encode(json_encode($objMensaje));
//                        $colas->addCola($mensaje, true);
//                    } catch (\Exception $ex) {
//                        $this->log->log($ex->getMessage().'. '.$ex->getTraceAsString(),Zend_Log::ERR);
//                    }

                $this->_helper->mail->notificacionPerfil(
                    array(
                        'to' => $perfil['postulante']['email'],
                        'nombre' => ucwords($perfil['postulante']['nombres']),
                        'nombrePuesto' => $registroAviso['puesto'],
                        'mostrarEmpresa' => $registroAviso['mostrar_empresa'],
                        'nombreEmpresa' => $registroAviso['nombre_empresa'],
                        'nombreComercial' => $registroAviso['nombre_comercial']
                    )
                );
            }

            $data                   = array();
            $data['id_postulante']  = $idPostulante;
            $data['id_empresa']     = $this->auth['empresa']['id'];
            $data['tipo']           = 1;
            $data['id_aviso']       = $idAviso;
            $data['fecha_busqueda'] = date('Y-m-d H:i:s');
            $visitas                = new Application_Model_Visitas();
            $res                    = $visitas->insert($data);
        }

        //Empresa Datos
        $idEmpresa    = $this->auth["empresa"]["id"];
        $idAnuncioW   = $arrayPostulacion['id_anuncio_web'];
        //$modelAw = new Application_Model_AnuncioWeb();
        $arrayEmpresa = $modelAw->getAvisoById($idAnuncioW);

        $avisoHelper = $this->_helper->getHelper('Aviso');

        $avisoHelper->actualizarNuevasPostulaciones($idAnuncioW);

        if ($arrayPostulacion['msg_respondido'] != 0) {
            $avisoHelper->actualizarMsgRsptPerfil($idAnuncioW, $idPostulacion);
        }

        //ListarCategoria de Etapas
        $categoriapostulacion             = new Application_Model_CategoriaPostulacion();
        $arrayCategoria                   = $categoriapostulacion->getCategoriaPostulacion($idEmpresa);
        $this->view->categoriaPostulacion = $arrayCategoria;

        foreach ($arrayCategoria as $data) {
            if ($data['id'] == $arrayPostulacion['id_categoria_postulacion']) {
                $perfil['postulante']['etapa_actual'] = $data['nombre'];
            }
        }

        //Datos Para Perfil
        $perfil['postulante']['fotovariable']             = $perfil['postulante']['path_foto_uno'];
        $perfil['postulante']['actionName']               = $this->_request->getActionName();
        $perfil['postulante']['id_categoria_postulacion'] = $arrayPostulacion['id_categoria_postulacion'];
        $perfil['postulante']['descartado']               = $arrayPostulacion['descartado'];
        $perfil['postulante']['match']                    = $arrayPostulacion['match'];

        $this->view->postulante = $perfil;
        $usuario                = $this->auth['usuario'];

        $formCompartir = new Application_Form_CompartirPorMail();
        $formCompartir->setAction('/empresa/mis-procesos/compartir');
        $formCompartir->correoEmisor->setValue($usuario->email);
        $formCompartir->hdnOculto->setValue(
            $perfil['postulante']['slug']
        );
        $formCompartir->nombreEmisor->setValue(
            ucwords($perfil['postulante']['nombres']).' '.
            ucwords($perfil['postulante']['apellidos'])
        );
        Zend_Layout::getMvcInstance()->assign(
            'compartirPorMail', $formCompartir
        );

        $this->view->puesto        = $arrayEmpresa['puesto'];
        $this->view->slug          = $arrayEmpresa['slug'];
        $this->view->url_id        = $arrayEmpresa['url_id'];
        $this->view->id            = $arrayEmpresa['id'];
        $this->view->idPostulacion = $idPostulacion;

        //Notas

        $formsNota = array();

        $notas      = new Application_Model_Nota();
        $arrayNotas = $notas->getNotasPostulaciones($idPostulacion);
        $count      = $max        = count($arrayNotas);
        if ($count != 0) {
            foreach ($arrayNotas as $key => $nota) {
                //$index++;
                if ($count == $max) {
                    $nota['mostrar'] = true;
                    $nota['contar']  = $count;
                } else {
                    $nota['mostrar'] = false;
                    $nota['contar']  = $count;
                }
                $count--;
                $form           = new Application_Form_Nota(true);
                $form->setHiddenId($nota['id_nota']);
                $form->setDefaults($nota);
                $formsNota[]    = $form;
                $valNotas[$key] = $nota;
            }
        } else {
            $valNotas = null;
        }
        $emptyFormNota = new Application_Form_Nota(true);
        $emptyNota     = array(
            'fecha' => date('Y-m-d H:i:s'),
            'text' => null,
            'path_original' => null,
            'id_nota' => -1,
            'mostrar' => true
        );

        $this->view->formNota      = $formsNota;
        $this->view->notas         = $valNotas;
        $this->view->emptyFormNota = $emptyFormNota;
        $this->view->emptyNotas    = $emptyNota;

        //mensajes
        $formsMensaje  = array();
        $mensajes      = new Application_Model_Mensaje();
        $arrayMensajes = $mensajes->getMensajesPregunta($idPostulacion);
        $count         = $max           = count($arrayMensajes);

        if ($count != 0) {
            foreach ($arrayMensajes as $key => $mensaje) {

                if ($count == $max) {
                    $mensaje['mostrar'] = true;
                    $mensaje['contar']  = $count;
                } else {
                    $mensaje['mostrar'] = false;
                    $mensaje['contar']  = $count;
                }
                $count--;
                $tempTipoMensaje         = $mensaje['tipo_mensaje'];
                $mensaje['tipo_mensaje'] = $mensaje['tipo_mensaje'] == Application_Model_Mensaje::ESTADO_PREGUNTA
                        ? '1' : '0';
                $form                    = new Application_Form_Mensajes(true);
                $form->setHiddenId($mensaje['id_mensaje']);
                $form->setDefaults($mensaje);
                $formsMensaje[]          = $form;
                $mensaje['tipo_mensaje'] = $tempTipoMensaje;
                $valMensaje[$key]        = $mensaje;
            }
        } else {
            $valMensaje = null;
        }
        $emptyFormMensaje = new Application_Form_Mensajes(true);
        $emptyMensaje     = array(
            'fecha' => date('Y-m-d H:i:s'),
            'path_original' => null,
            'cuerpo' => null,
            'id_mensaje' => -1,
            'tipo_mensaje' => Application_Model_Mensaje::ESTADO_MENSAJE,
            'mostrar' => true,
            'contar' => $count++
        );

        $this->view->formMensaje       = $formsMensaje;
        $this->view->mensajes          = $valMensaje;
        $this->view->emptyFormMensajes = $emptyFormMensaje;
        $this->view->emptyMensajes     = $emptyMensaje;

        //Historico
        $historico      = new Application_Model_HistoricoPs();
        $arrayHistorico = $historico->getHistoricoPostulacion($idPostulacion);
        $frmHistorico   = array();
        if ($arrayHistorico != 0) {
            foreach ($arrayHistorico as $historico) {
                $frmHistorico[] = $historico;
            }
        }
        $this->view->arrayHistorico = $frmHistorico;

        $anuncio            = $_aviso->find($idAnuncioW);
        $f                  = $anuncio[0]->fh_vencimiento_proceso;
        if ($f == "0000-00-00" || $f == null) $f                  = "now";
        $fv                 = new DateTime($f);
        $fa                 = new DateTime("now");
        $dias               = $fa < $fv;
        $this->view->online = $dias == true ? 1 : 0;

        $html = $this->render('perfil-publico-emp');
        $res  = array(
            'html' => $html,
        );
    }

    public function compartirAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $config   = Zend_Registry::get("config");
        $dataPost = $this->_getAllParams();
        if ($this->_request->isPost()) {
            $dataPost['mensajeCompartir'] = preg_replace($config->avisopaso2->expresionregular,
                '', $dataPost['mensajeCompartir']);
            $dataPost['mensajeCompartir'] = str_replace("@", "",
                $dataPost['mensajeCompartir']);

            $urlPerfil = $this->view->url(
                array('slug' => $dataPost['hdnOculto']), 'perfil_publico', true
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
            $this->getMessenger()->success('Se envio corectamente.');
            $this->_redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function guardarNotaAction()
    {
        //echo 'asdasd';exit;
        $this->_helper->layout->disableLayout();
        $valNotas      = array();
        $nota          = $this->_getAllParams();
        $idNota        = $nota['id_nota'];
        $idPostulacion = $nota['iP'];
        $idUsuario     = $this->auth['usuario']->id;

        $formNota = new Application_Form_Nota(true);

        if ($this->_request->isPost()) {

            $modelNota = new Application_Model_Nota();
            $validNota = $formNota->isValid($nota);
            $arrayNota = $modelNota->getNotaPostulacion($idNota);
            $lastPath  = $arrayNota['path'];
            if ($validNota) {
                //rename
                $file = $formNota->path->getFileName();
                if (count($file) != 0) {
                    $nombreOriginal = pathinfo($file);
                    $ext            = $nombreOriginal['extension'];

                    $rename      = date('YmdHis')."_".md5(time());
                    $nuevoNombre = $rename.".".$ext;

                    $formNota->path->addFilter('Rename', $nuevoNombre);
                    $formNota->path->receive();
                    if (!$this->config->urls->app->elementsNotaRoot.$lastPath) {
                        unlink($this->config->urls->app->elementsNotaRoot.$lastPath);
                    }
                } else {
                    if ($lastPath != null) {
                        $nombreOriginal = $arrayNota['path_original'];
                        $nuevoNombre    = $lastPath;
                    } else {
                        $nombreOriginal = null;
                        $nuevoNombre    = null;
                    }
                }

                if ($idNota != null) {
                    //save
                    $valuesNota                  = $formNota->getValues($nota);
                    $valuesNota['fh']            = date('Y-m-d H:i:s');
                    $valuesNota['path_original'] = count($file) != 0 ? $nombreOriginal['basename']
                            : $nombreOriginal;
                    $valuesNota['path']          = $nuevoNombre;
                    unset($valuesNota['id_nota']);
                    unset($valuesNota['fecha']);

                    $where   = $modelNota->getAdapter()
                        ->quoteInto('id = ?', $idNota);
                    $success = $modelNota->update($valuesNota, $where);
                } else {

                    $valuesNota                   = $formNota->getValues($nota);
                    $valuesNota['id_postulacion'] = $idPostulacion;
                    $valuesNota['id_usuario']     = $idUsuario;
                    $valuesNota['fh']             = date('Y-m-d H:i:s');
                    $valuesNota['path_original']  = $nombreOriginal['basename'];
                    $valuesNota['path']           = $nuevoNombre;
                    unset($valuesNota['id_nota']);
                    unset($valuesNota['fecha']);

                    $success = $modelNota->insert($valuesNota);
                    $idNota  = $modelNota->getAdapter()->lastInsertId('nota',
                        'id');
                }

                $formNota  = $arrayNota = $modelNota->getNotaPostulacion($idNota);
                $form      = new Application_Form_Nota(true);
                $form->setHiddenId($idNota);
                $form->setDefaults($formNota);
                $formNota  = $form;

                $arrayNota['mostrar'] = true;
                $arrayNota['contar']  = 0;
                $valNotas             = $arrayNota;
            }
        }
        $this->view->formNota = $formNota;
        $this->view->notas    = $valNotas;
    }

    public function extenderProcesoAction()
    {
        $config  = $this->getConfig();
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $usuario = $this->auth['usuario'];
        $empresa = $this->auth['empresa'];
        $avisoId = $this->_getParam('aviso', false);

        $anuncio        = new App_Service_Validate_Ad($avisoId);
        $usuarioEmpresa = $this->auth['usuario-empresa'];

        if (!$anuncio->isManaged($usuarioEmpresa)) {
            $this->getMessenger()->error($anuncio->getMessage());
            $this->_redirect('/empresa/mi-cuenta');
        }

        //if (!$this->_helper->Aviso->perteneceAvisoAEmpresa($avisoId, $empresa['id']) || !$this->_hash->isValid($this->_getParam('csrfhash'))
        if (!$this->_helper->Aviso->perteneceAvisoAEmpresa($avisoId,
                $empresa['id'])
        ) {
            throw new App_Exception_Permisos();
        }
        $this->_redirect("/empresa/publica-aviso/index/extiende/$avisoId");

        $aviso = new Application_Model_AnuncioWeb();

        $tieneEstudios    = $aviso->tieneAnuncioEstudios($avisoId);
        $tieneExperiencia = $aviso->tieneAnuncioExperiencia($avisoId);
        if (!$tieneEstudios) {
            $this->getMessenger()->error('Por favor es necesario que actualice sus Estudios.');
        }

        if (!$tieneExperiencia) {
            $this->getMessenger()->error('Por favor es necesario que actualice sus Experiencia.');
        }




        $data = $aviso->getFullAvisoById($avisoId);

        if ((!$tieneEstudios) || (!$tieneExperiencia)) {
            $this->_redirect('/empresa/publica-aviso/paso2/tarifa/'.$data['id_tarifa'].'/extiende/'.$avisoId);
        }


        $idNewImpreso = null;

        if ($data['tipo'] != Application_Model_AnuncioWeb::TIPO_SOLOWEB) {
            //Inicio Crea un Nuevo Impreso
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

            if (!empty($data['id_anuncio_impreso']) && !is_null($data['id_anuncio_impreso'])) {
                $modelAI      = new Application_Model_AnuncioImpreso();
                $arrayImpreso = $modelAI->getDataAnuncioImpreso($data['id_anuncio_impreso']);
                $idNewImpreso = $modelAI->insert(
                    array(
                        'id_empresa' => $empresa['id'],
                        'texto' => $arrayImpreso['texto'],
                        'fh_creacion' => date('Y-m-d H:i:s'),
                        'fh_pub_estimada' => $d->get(Zend_Date::DATETIME)
                    )
                );
            }
        }

        //Fin

        /* $aw = new Application_Model_AnuncioWeb();
          $origen = $aw->getAvisoExtendido($avisoId);
          $extiende = $origen['extiende_a']; */

        $slugFilter = new App_Filter_Slug();
        $_tu        = new Application_Model_TempUrlId();
        if ($data['mostrar_empresa'] == 0) {
            $empresaRs = $data['empresa_rs'];
        } else {
            $empresaRs = $empresa['nombre_comercial'];
        }
        $helper    = $this->_helper->getHelper("Aviso");
        $prioridad = $helper->getOrdenPrioridad($data['tipo'],
            $data['id_empresa']);

        //Prioridad si los avisos son destacados
        if ($data['tipo'] == Application_Model_AnuncioWeb::TIPO_DESTACADO) {

            $modelAviso  = new Application_Model_AnuncioWeb;
            $dataEmpresa = $modelAviso->prioridadEmpresaAvisoDestacado($data['id_empresa']);
            $prioridad   = $dataEmpresa['prioridad'];
            //$ndiasPrioridad = $dataEmpresa['dias'];
        }

        $nuevoAvisoId = $aviso->insert(
            array(
                'id_puesto' => $data['id_puesto'],
                'id_producto' => $data['id_producto'],
                'puesto' => $data['puesto'],
                'id_area' => $data['id_area'],
                'id_nivel_puesto' => $data['id_nivel_puesto'],
                'funciones' => $data['funciones'],
                'responsabilidades' => $data['responsabilidades'],
                'mostrar_salario' => $data['mostrar_salario'],
                'mostrar_empresa' => $data['mostrar_empresa'],
                'salario_min' => $data['salario_min'],
                'salario_max' => $data['salario_max'],
                'online' => '0',
                'borrador' => '1',
                'id_empresa' => $empresa['id'],
                'id_ubigeo' => $data['id_ubigeo'],
                'fh_creacion' => date('Y-m-d H:i:s'),
                'fh_edicion' => date('Y-m-d H:i:s'),
                'creado_por' => $usuario->id,
                'url_id' => $data['url_id'],
                'slug' => $slugFilter->filter($data['puesto']),
                'empresa_rs' => $empresaRs,
                'estado' => Application_Model_AnuncioWeb::ESTADO_REGISTRADO,
                'origen' => 'apt_2',
                'id_tarifa' => $data['id_tarifa'],
                'id_producto' => $data['id_producto'],
                'tipo' => $data['tipo'],
                'medio_publicacion' => $data['medio_publicacion'],
                'logo' => $empresa["logo"],
                'id_anuncio_impreso' => $idNewImpreso,
                'extiende_a' => $avisoId,
                'chequeado' => 1,
                'prioridad' => $prioridad,
                'correo' => $data["correo"]
            )
        );

        $dataEstudio    = $aviso->getEstudioInfoByAnuncio($avisoId);
        $anuncioEstudio = new Application_Model_AnuncioEstudio();
        foreach ($dataEstudio as $estudio) {
            $anuncioEstudio->insert(
                array(
                    'id_anuncio_web' => $nuevoAvisoId,
                    'id_nivel_estudio' => $estudio['id_nivel_estudio'],
                    'id_carrera' => $estudio['id_carrera'],
                    'id_nivel_estudio_tipo' => $estudio['id_nivel_estudio_tipo']
                )
            );
        }
        $dataExperiencia    = $aviso->getExperienciaInfoByAnuncio($avisoId);
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
        $dataIdioma    = $aviso->getIdiomaInfoByAnuncio($avisoId);
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
        $dataPrograma    = $aviso->getProgramaInfoByAnuncio($avisoId);
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
        $dataPregunta = $aviso->getPreguntaInfoByAnuncio($avisoId);
        if (count($dataPregunta) > 0) {
            $cuestionario    = new Application_Model_Cuestionario();
            $cuestionarioId  = $cuestionario->insert(
                array(
                    'id_empresa' => $empresa['id'],
                    'id_anuncio_web' => $nuevoAvisoId,
                    'nombre' =>
                    'Cuestionario de la empresa '.$empresa['nombre_comercial']
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

        $beneficios = $this->auth['empresa']['membresia_info']['beneficios'];
        $codigo     = Application_Model_Beneficio::CODE_REASIGNAR_PROCESOS;

        if (isset($beneficios->$codigo)) {
            $anuncioUsuarioEmpresaModelo = new
                Application_Model_AnuncioUsuarioEmpresa;
            $anuncioUsuarioEmpresaModelo->asignar(
                $usuarioEmpresa['id'], $nuevoAvisoId);
        }
        $cache = $this->getCache();
        @$cache->remove('anuncio_web_'.$data['url_id']);
        @$cache->remove('AnuncioWeb_getAvisoInfoById_'.$nuevoAvisoId);
        @$cache->remove('AnuncioWeb_getAvisoInfoficha_'.$data['id']);
        @$cache->remove('Empresa_getEmpresaHome_');
        @$cache->remove('AnuncioWeb_getAvisoById_'.$nuevoAvisoId);
//        @$cache->remove('AnuncioWeb_getAvisoRelacionadosnew_' . $nuevoAvisoId);
//        @$cache->remove('AnuncioWeb_getAvisosRelacionadosAuxiliar_' . $nuevoAvisoId);
        @$cache->remove('AnuncioWeb_getAvisoIdByUrl_'.$data['url_id']);
        @$cache->remove('AnuncioWeb_getAvisoIdByCreado_'.$data['url_id']);

        $this->_helper->Aviso->extenderAviso($nuevoAvisoId, $usuario->id);
        $this->_helper->Aviso->extenderReferidos($nuevoAvisoId);
        $sesion = $this->getSession();
        $sesion->__set("idanuncioextendido", $avisoId);

        if ($data['tipo'] == Application_Model_AnuncioWeb::TIPO_DESTACADO)
                $this->_redirect('/empresa/publica-aviso-destacado/paso3/aviso/'.$nuevoAvisoId);
        else
                $this->_redirect('/empresa/publica-aviso/paso4/aviso/'.$nuevoAvisoId);
    }

    public function confirmarExtensionAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $avisoId = $this->_getParam('aviso');
        $usuario = $this->auth['usuario'];
        $this->_helper->Aviso->extenderAviso($avisoId, $usuario->id);
    }

    //Notas
    public function borrarNotaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idNota = $this->_getParam('rel', false);

        if ($this->_request->isPost()) {
            $ok = $this->eliminarNota($idNota);
        } else {
            $ok = false;
        }

        $data = array(
            'status' => $ok ? 'ok' : 'error',
            'msg' => $ok ? 'Se borró ok' : 'Hubo un Error'
        );
        $this->_response->appendBody(Zend_Json::encode($data));
        return $data;
    }

    public function archivoAdjuntoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        if (isset($this->auth['empresa'])) {
            $idNota    = $this->_getParam('id');
            $modelNota = new Application_Model_Nota();
            $arrayNota = $modelNota->getNotaPostulacion($idNota);

            if ((bool) $arrayNota) {
                $ruta = ELEMENTS_URL_NOTAS.$arrayNota['path'];

                $dataAr = file_get_contents($ruta);
                $this->_response->appendBody($dataAr);
                $this->_response->setHeader(
                    "Content-Disposition",
                    ' attachment; filename="'.$arrayNota['path_original'].'"'
                );
            } else {
                return false;
            }
        }
    }

    public function eliminarNota($idNota)
    {
        if ($idNota) {
            $modelNota = new Application_Model_Nota();
            $arrayNota = $modelNota->getNotaPostulacion($idNota);
            if ($arrayNota['path'] != null) {
                unlink($this->config->urls->app->elementsNotaRoot.$arrayNota['path']);
            }
            $where = array('id=?' => $idNota);
            $r     = (bool) $modelNota->delete($where);
        } else {
            $r = false;
        }
        return $r;
    }

    public function eliminarAdjuntoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $a         = -1;
        $idNota    = $this->_getParam('rel', null);
        $modelNota = new Application_Model_Nota();
        if (true) {

            $arrayNota = $modelNota->getNotaPostulacion($idNota);

            if ($arrayNota['path'] != null) {
                unlink($this->config->urls->app->elementsNotaRoot.$arrayNota['path']);
                $modelNota->update(
                    array('path' => null, 'path_original' => null,
                    'id_usuario' => $this->auth['usuario']->id,
                    'fh' => date('Y-m-d H:i:s')
                    ), $modelNota->getAdapter()->quoteInto('id = ?', $idNota)
                );
                $a = 1;
            }
        }
        $this->_response->appendBody(Zend_Json::decode($a));
    }

    //Mensaje
    public function guardarMensajeAction()
    {
        $this->_helper->layout->disableLayout();

        $gerParams     = $this->_getAllParams();
        $idPostulacion = $gerParams['iP'];
        $idUsuario     = $gerParams['iU'];
        $idEmpresa     = $this->auth['usuario']->id;

        $formMensaje = new Application_Form_Mensajes(true);



        $modelMen = new Application_Model_Mensaje();
        if ($this->getRequest()->isPost()) {
            $mensaje  = $this->getRequest()->getPost();
            $formMensaje->removeElement('token');
            $validMen = $formMensaje->isValid($mensaje);

            if ($validMen) {
                $valuesMensaje = $formMensaje->getValues($mensaje);
                $fecha         = date('Y-m-d H:i:s');

                $valuesMensaje['de']             = $idEmpresa;
                $valuesMensaje['para']           = $idUsuario;
                $valuesMensaje['fh']             = $fecha;
                $valuesMensaje['tipo_mensaje']   = $valuesMensaje['tipo_mensaje']
                    == '0' ?
                    Application_Model_Mensaje::ESTADO_MENSAJE :
                    Application_Model_Mensaje::ESTADO_PREGUNTA;
                $valuesMensaje['leido']          = 0;
                $valuesMensaje['respondido']     = 0;
                $valuesMensaje['notificacion']   = 0;
                $valuesMensaje['id_postulacion'] = $idPostulacion;
                unset($valuesMensaje['id_mensaje']);
                unset($valuesMensaje['token']);
                $idPregunta                      = $modelMen->insert($valuesMensaje);

                if ($idPregunta != null) {

                    $modelPostulacion = new Application_Model_Postulacion();
                    $arrayPostulacion = $modelPostulacion->getPostulacion($idPostulacion);


                    $msjHelper = $this->_helper->getHelper('Mensaje');
                    $msjHelper->actualizarCantMsjsPostulacion($idUsuario,
                        $idPostulacion);

                    $modelPostulante = new Application_Model_Postulante();
                    $arrayPostulante = $modelPostulante->getPostulante(
                        $arrayPostulacion['id_postulante']
                    );

                    $modelAw         = new Application_Model_AnuncioWeb();
                    $arrayAnuncioWeb = $modelAw->getAvisoById($arrayPostulacion['id_anuncio_web']);


//                    $url = $this->view->url(
//                            array(
//                        'slug' => $arrayAnuncioWeb['slug'],
//                        'url_id' => $arrayAnuncioWeb['url_id']
//                            ), 'aviso', true
//                    );

                    $this->_helper->mail->mensajePostulante(
                        array(
                            'to' => $arrayPostulante['email'],
                            'email' => $arrayPostulante['email'],
                            'nombre' => ucwords($arrayPostulante['nombres']),
                            'empresa' => $arrayAnuncioWeb['nombre_comercial'],
                            'mostrarEmpresa' => $arrayAnuncioWeb['mostrar_empresa'],
                            'puesto' => $arrayAnuncioWeb['puesto'],
                            'mensaje' => $valuesMensaje['cuerpo']
                        //,'url' => $this->config->app->siteUrl."/postulaciones/index/url/".$nurl
                        )
                    );
                }

                $formMensaje->setDefaults($valuesMensaje);
            }


            $formMensaje->setHiddenId($idPregunta);
        }


        //$form = new Application_Form_Mensajes(true);

        $valuesMensaje['id_mensaje']  = $idPregunta;
        $valuesMensaje['fecha']       = $valuesMensaje['fh'];
        $valuesMensaje['postulacion'] = $idPostulacion;
        $valuesMensaje['mostrar']     = true;
        unset($valuesMensaje['id_postulacion']);
        unset($valuesMensaje['fh']);

        if ($valuesMensaje['tipo_mensaje'] ==
            Application_Model_Mensaje::ESTADO_PREGUNTA) {
            $valuesMensaje['respuesta'] = '';
        }

        $valuesMensaje['contar'] = 0;
        $valMensaje              = $valuesMensaje;

        $this->view->formMensaje = $formMensaje;
        $this->view->mensaje     = $valMensaje;
    }
    /*
     * Etapas y Descartado Mi perfil
     *
     */

    public function moverEtapaPerfilAction()
    {

        $a      = -1;
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $opcion = $this->_getParam('rel', null);
        $valor  = $this->_getParam('rol', null);

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($opcion && $valor) {
                $action = $this->_helper->getHelper("RegistrosExtra");
                $action->moverAEtapaPostulacion($valor, $opcion);
                //$zl->updateIndexPostulaciones($valor, 'idcategoriapostulacion', $opcion);
                $a      = 1;
            }
        } else {
            exit(0);
        }

        $this->_response->appendBody(Zend_Json::decode($a));
    }

    //Descartar postulacion
    public function descartaPerfilAction()
    {
        //$zl = new ZendLucene();
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $a             = -1;
        $idpostulacion = $this->_getParam('rol');
        $action        = $this->_helper->getHelper("RegistrosExtra");
        $idDes         = $action->descartarPostulacion($idpostulacion);

        $modelPostulacion = new Application_Model_Postulacion();
        $arrayPostulacion = $modelPostulacion->getPostulacion($idpostulacion);

        $this->_helper->Aviso->actualizarPostulantes($arrayPostulacion['id_anuncio_web']);

        //$zl->updateIndexPostulaciones($valor, 'descartado', '1');

        if ($idDes != null) {
            $a = 1;
        }
        $this->_response->appendBody(Zend_Json::decode($a));
    }

    /**
     * invitar a un Anuncio Web
     */
    public function invitarProcesoAction()
    {
        $this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNoRender();
        $idPostulante  = $this->_getParam('id');
        $idPostulacion = $this->_getParam('idpostulacionactual');
        $idEmpresa     = $this->auth['empresa']['id'];

        $aw            = new Application_Model_AnuncioWeb();
        $arrayProcesos = $aw->getAvisosNoPostulados($idPostulante, $idEmpresa);

        $this->view->idPostulante  = $idPostulante;
        $this->view->idPostulacion = $idPostulacion;

        $tokenCSRF         = new Zend_Session_Namespace('token');
        $this->view->token = $tokenCSRF->token  = md5(uniqid(rand(), 1));

        //----------------------------------------------------------------------------------
        $modeloPostulanteBloqueado = new Application_Model_EmpresaPostulanteBloqueado();

        $getBloqueado = $modeloPostulanteBloqueado->
            obtenerPorEmpresaYPostulante(
            (int) $this->auth['empresa']['id'], $idPostulante,
            array('id' => 'id')
        );

        $this->view->btnBloqueado = self::PROCESO_EXITOSO;

        if ((int) $getBloqueado['id'] > 0) {
            $this->view->btnBloqueado = self::PROCESO_INCOMPLETO;
        } else {
            $this->view->procesos = $arrayProcesos;
        }
        //----------------------------------------------------------------------------------
    }

    public function enviarInvitacionAction()
    {
        $a                   = 0;
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idAw                = $this->_getParam('idAw');
        $idPostulante        = $this->_getParam('idPos');
        $idPostulacionactual = $this->_getParam('idPostulacion');

        $token = $this->_getParam('tok');

        $modelPostulacion = new Application_Model_Postulacion();

        $existePos = $modelPostulacion->postulacionActual($idAw, $idPostulante);


        $tokenCSRF = new Zend_Session_Namespace('token');
        if ($tokenCSRF->token != $token) {
            return 0;
        }


        $db = $this->getAdapter();

        if (count($existePos) == 0) {
            try {
                $db->beginTransaction();

                //AnuncioWeb
                $modelAw = new Application_Model_AnuncioWeb();
                $aw      = $modelAw->getDataAvisoInvitacion($idAw);

                $avisoHelper          = $this->_helper->getHelper('Aviso');
                $avisoHelper->actualizarInvitaciones($idAw);
                // @codingStandardsIgnoreStart
                $modelAPM             = new Application_Model_AnuncioPostulanteMatch();
                $whereMatch           = array();
                $whereMatch[]         = $this->getAdapter()->quoteInto('id_postulante = ?',
                    $idPostulante);
                $whereMatch[]         = $this->getAdapter()->quoteInto('id_anuncio_web = ?',
                    $idAw);
                $modelAPM->update(
                    array('estado' => Application_Model_AnuncioPostulanteMatch::ESTADO_POSTULA,
                    'fh_postulacion' => date('Y-m-d H:i:s')), $whereMatch
                );
                // @codingStandardsIgnoreEnd
                //Postulacion
                $funciones            = $this->_helper->getHelper("RegistrosExtra");
                $match                = $funciones->PorcentajeCoincidencia($idAw,
                    $idPostulante);
                $nivelestudioscarrera = $funciones->MejorNivelEstudiosCarrera($idPostulante);

                $idPostulacion = $modelPostulacion->insert(
                    array('id_postulante' => $idPostulante,
                        'id_anuncio_web' => $idAw,
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

                /* REGISTRAMOS POSTULACION EN ZEND LUCENE */
                /* $zl = new ZendLucene();
                  $azl["idanuncioweb"]    =  $idAw;
                  $azl["idpostulacion"]   =  $idPostulacion;
                  $azl["msgporresponder"] =  "0";
                  $azl["match"]           =  $match;
                  $azl["msgnoleidos"]     =  "0";
                  $azl["msgrespondido"]   =  "0";
                  $azl["esnuevo"]         =  "1";
                  $azl["invitacion"]      =  "1";
                  $azl["idcategoriapostulacion"] = "0";
                  $azl["descartado"]      =  "0";
                  $azl["nivel_estudio"]   =  $nivelestudioscarrera["nivelestudios"];
                  $azl["nivelestudio"]    =  $nivelestudioscarrera["nivelestudios"];
                  $azl["carrerap"]        =  $nivelestudioscarrera["carrera"];
                  $zl->duplicarIndexPostulaciones($idPostulacionactual, $azl); */
                /* FIN DE ZEND LUCENE */


                $this->_helper->Aviso->actualizarPostulantes($idAw);
                $this->_helper->Aviso->actualizarInvitaciones($idAw);
                $this->_helper->Aviso->actualizarNuevasPostulaciones($idAw);
                //$this->_helper->Aviso->actualizarMsgNoLeidos($idAw);
                //Postulante
                $modelPostulante = new Application_Model_Postulante();
                $arrayPostulante = $modelPostulante->getPostulante($idPostulante);
                $arrayAnuncioWeb = $modelAw->getAvisoById($idAw);

                //Invitacion y envio de Mensaje Postulante
                $modelMsj = new Application_Model_Mensaje();
                // @codingStandardsIgnoreStart
//                $cuerpo = $aw['empresa_rs'] . ' ha iniciado el proceso de Selección para el puesto de ' . $aw['puesto'] . ',
//                        la empresa vió tu perfil en la base de datos de aquiempleos.com y quiere invitarte a que seas parte de este proceso.
//                        <br><br>
//                        <P align=center style="color:#3366ff">
//                        ' . '<a href="' .
//                        SITE_URL . $this->view->url(array('module' => 'postulante', 'controller' => 'aviso',
//                            'action' => 'ver', 'url_id' => $aw['url_id'], 'slug' => $aw['slug']), 'aviso', true) .
//                        '">
//                        <b>Ver Aviso y Postular</b>
//                        </a>
//                        </P>';
                $cuerpo   = $aw['empresa_rs'].' ha iniciado el proceso de Selección para el puesto de '.$aw['puesto'].',
								la empresa vio tu perfil en la base de datos de aquiempleos.com y quiere invitarte a que seas parte de este proceso.
								'.'<a href="'.
                    SITE_URL.$this->view->url(array('slug' => $aw['slugaviso'], 'empresaslug' => $aw['empresaslug'],
                        'ubicacionslug' => $aw['ubicacionslug'], 'url_id' => $aw['url_id']),
                        'aviso_detalle', true).
                    '">
								<b>Ver Aviso y Postular</b>
								</a>
					 ';
                // @codingStandardsIgnoreEnd
                $data     = array(
                    // @codingStandardsIgnoreStart
                    'de' => $aw->creado_por,
                    // @codingStandardsIgnoreEnd
                    'para' => $arrayPostulante['idusuario'],
                    'fh' => date('Y-m-d H:i:s'),
                    'cuerpo' => $cuerpo,
                    'tipo_mensaje' => Application_Model_Mensaje::ESTADO_INVITACION,
                    'leido' => 0,
                    'notificacion' => 1,
                    'id_postulacion' => $idPostulacion
                );
                $modelMsj->insert($data);
                $this->_helper->getHelper('Mensaje')
                    ->actualizarCantMsjsNotificacion($arrayPostulante['idusuario']);
                $db->commit();
                if ($idPostulacion != null) {
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
                    $a = 1;
                }
            } catch (Zend_Db_Exception $e) {
                $db->rollBack();
                $a = -1;
            } catch (Zend_Exception $e) {
                $this->getMessenger()->error($this->_messageSuccess);
                echo $e->getMessage();
            }
        }
        $this->_response->appendBody(Zend_Json::decode($a));
    }

    public function registrarReferenciadoAction()
    {
        $this->_helper->layout->disableLayout();

        $form        = new Application_Form_RegistrarReferenciado();
        $params      = $this->_getAllParams();
        $emailOculto = $this->_getParam("emailoculto");

        //registro de NUEVO referenciado

        if (isset($emailOculto) && $this->_request->isPost()) {
            if ($form->isValid($params)) {
                $utilfile                        = $this->_helper->getHelper('UtilFiles');
                $auth["postulante"]["nombres"]   = $this->_getParam("nombre");
                $auth["postulante"]["apellidos"] = $this->_getParam("apellidos");
                $nuevoname                       = $utilfile->_renameFile($form,
                    "path_cv", $auth);
                $idanuncio                       = $this->_getParam("idanuncio");

                $valores = $form->getValues();
                $modelo  = new Application_Model_Referenciado();
                $modelo->insert(
                    array(
                        "email" => $emailOculto,
                        "sexo" => $valores["sexoMF"],
                        "nombre" => $valores["nombre"],
                        "apellidos" => $valores["apellidos"],
                        "id_anuncio_web" => $idanuncio,
                        "telefono" => $valores["telefono"],
                        "curriculo" => $nuevoname,
                        "estado" => "1",
                        "fecha_creacion" => date("Y-m-d H:i:s")
                    )
                );

                /* REGISTRAMOS POSTULACION EN ZEND LUCENE */
                /* $zl = new ZendLucene();
                  $azl["idanuncioweb"]    =  $idanuncio;
                  $azl["idpostulacion"]   =  "-1";
                  $azl["idpostulante"]    =  "-2";
                  $azl["foto"]            =  "";
                  $azl["nombres"]         =  $valores["nombre"];
                  $azl["apellidos"]       =  $valores["apellidos"];
                  $azl["telefono"]        =  $valores["telefono"];
                  $azl["slug"]            =  "";
                  $azl["msgporresponder"] =  "0";
                  $azl["sexoclaves"]      =  $valores['sexoMF'];

                  $azl["edad"]            =  "0";
                  $azl["fecha_nac"]       =  "";
                  $azl["match"]           =  "100";
                  $azl["nivel_estudio"]   =  "";
                  $azl["nivelestudio"]    =  "";
                  $azl["carrerap"]        =  "";
                  $azl["pathcv"]          =  $nuevoname;

                  $azl["msgnoleidos"]     =  "0";
                  $azl["msgrespondido"]   =  "0";
                  $azl["esnuevo"]         =  "1";
                  $azl["invitacion"]      =  "0";
                  $azl["idcategoriapostulacion"]="0";
                  $azl["descartado"]      =  "0";
                  $azl["referenciado"]      =  "1";
                  $azl["ubigeoclaves"]    =  "0";

                  $azl["ubigeo"]          =  "";
                  $azl["online"]          =  "1";
                  $azl["sexo"]            =  $valores['sexoMF']=="M"?"Masculino":"Femenino";

                  $azl["carrera"]          =  "";
                  $azl["estudios"]         =  "";
                  $azl["estudiosclaves"]   =  "";
                  $azl["carreraclaves"]    =  "";
                  $azl["experiencia"]      =  "";
                  $azl["idiomas"]          =  "";
                  $azl["programasclaves"]  =  "";

                  $zl->insertarIndexPostulaciones($azl); */
                //FIN ZEND LUCENE

                echo "GOOD";
            } else {
                echo "BAD";
            }
        }

        //------------------------------------------------------------------------
        $modelPostulante = new Application_Model_Postulante();
        $postulacion     = new Application_Model_Postulacion();

        $email     = $this->_getParam("txtemail");
        $idAnuncio = $this->_getParam("idAw");
        $form->getElement("email")->setValue($email)->setIgnore(false);

        $this->view->result          = "-1";
        $this->view->frmReferenciado = $form;
        /* BUSQUEDA DE POSTULANTES Y REFERENCIADOS EN EL ANUNCIO POR EMAIL
         * 0 Sin resultados
         * 1 Con Resultados Postulante
         * 2 Con Resultados Referenciados
         * 3 Ya Postularon
         * 4 Postulante bloqueado
         * 5 Ya Postularon, ya refirieron
         */

        if (isset($email)) {
            $result = $modelPostulante->buscarPostulantexEmail($email);
            //-----------------------------------------------------------------------------
            if ((int) $result['id'] > 0) {
                //verifico si es un postulante bloqueado
                $bloqueado = new Application_Model_EmpresaPostulanteBloqueado();

                $data['id']   = 'id';
                $getBloqueado = $bloqueado->
                    obtenerPorEmpresaYPostulante($this->auth['empresa']['id'],
                    (int) $result['id'], $data);

                $this->view->result = "1";

                $this->view->objPostulante = $result;

                if ((int) $getBloqueado['id'] > 0) {
                    $this->view->result       = "4";
                    $this->view->msgYaPostulo = "El postulante se encuentra bloqueado";
                    return;
                }

                //verifico si este postulante postulo al aviso
                $respPostulacion = $postulacion->postulacionActual($idAnuncio,
                    (int) $result['id']);

                if ((int) $respPostulacion['id'] > 0) {
                    if ((int) $respPostulacion['invitacion'] > 0 && (int) $respPostulacion['activo']
                        == 0) {
                        $this->view->result       = "6";
                        $this->view->msgYaPostulo = "El postulante se encuentra invitado";
                        return;
                    } elseif ((int) $respPostulacion['referenciado'] > 0) {
                        $this->view->result       = "5";
                        $this->view->msgYaPostulo = "Ya referenciastes a este postulante";
                        return;
                    } else {
                        $this->view->result       = "3";
                        $this->view->msgYaPostulo = "El postulante ya se encuentra dentro del proceso";
                        return;
                    }
                }
            } else {
                //verifico si es un referido existen en tabla referido
                $modeldos           = new Application_Model_Referenciado();
                $resultReferenciado = $modeldos->
                    buscarReferenciadoXEmailYAnuncio(
                    $email, $idAnuncio);

                if ($resultReferenciado != null) {
                    $this->view->nombreReferenciado = $resultReferenciado["nombres"]." ".$resultReferenciado["apellidos"];
                    $this->view->result             = "2";
                } else {
                    $this->view->result = "0";
                }
            }
            //------------------------------------------------------------------------
        }

        $this->view->email = $email;
        //------------------------------------------------------------------------
        //registro de referenciado EXISTENTE
        $idpostulante      = $this->_getParam("idPostulante");

        if (isset($idpostulante) && $this->_request->isPost()) {
            $respPostulacion = $postulacion->postulacionActual($idAnuncio,
                $idpostulante);
            if ((int) $respPostulacion['id'] > 0) {
                //---------------------------------------------------------------------
                $postulacion          = new Application_Model_Postulacion();
                $data['referenciado'] = 1;
                $postulacion->updatePostulacion((int) $respPostulacion['id'],
                    $data);

                $this->_helper->viewRenderer->setNoRender();
                $dataJson['msg'] = "Se ha referenciado ha este postulante";
                $this->_response->appendBody(Zend_Json::encode($dataJson));
                return;
                //---------------------------------------------------------------------
            } else {
                $funciones            = $this->_helper->getHelper("RegistrosExtra");
                $match                = $funciones->PorcentajeCoincidencia($idAnuncio,
                    $idpostulante);
                $nivelestudioscarrera = $funciones->MejorNivelEstudiosCarrera($idpostulante);

                $data = array('id_postulante' => $idpostulante,
                    'id_anuncio_web' => $idAnuncio,
                    'id_categoria_postulacion' => null,
                    'fh' => date('Y-m-d H:i:s'),
                    'msg_leidos' => '0',
                    'msg_no_leidos' => '0',
                    'msg_por_responder' => '0',
                    'match' => $match,
                    'es_nuevo' => '1',
                    'referenciado' => '1',
                    'nivel_estudio' => $nivelestudioscarrera["nivelestudios"],
                    'carrera' => $nivelestudioscarrera["carrera"]);

                $postulanteModelo = new Application_Model_Postulante;

                $postulante = $postulanteModelo->obtenerUsuario($idpostulante);

                $referenciadoModelo = new Application_Model_Referenciado;

                $referido = $referenciadoModelo->buscarReferenciadoXEmailYAnuncio(
                    $postulante['email'], $idAnuncio);

                if (!empty($referido)) {
                    $data['referenciado'] = Application_Model_Postulacion::ES_REFERENCIADO;
                    $referenciadoModelo->postulo($referido['id']);
                }

                $idPostulacion = $postulacion->insert($data);

                $this->_helper->viewRenderer->setNoRender();
                $dataJson['msg'] = "Referente agregado Correctamente";
                $this->_response->appendBody(Zend_Json::encode($dataJson));
                return;
            }
        }
    }

    public function decisionAvisoAction()
    {
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.misprocesos.js')
        );


        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MIS_PROCESOS;

        $idProd          = $this->_getParam('idP', null);
        $this->view->rel = $idAviso         = $this->_getParam('rel', null);

        if (!$this->_helper->Aviso->perteneceAvisoAEmpresa($idAviso,
                $this->auth['empresa']['id'])) {
            throw new App_Exception_Permisos();
        }

        $modelAW            = new Application_Model_AnuncioWeb();
        $arrayAW            = $modelAW->getAvisoById($idAviso);
        $this->view->url_id = $arrayAW['url_id'];
        $this->view->slug   = $arrayAW['slug'];
        $this->view->puesto = $arrayAW['puesto'];
        $this->view->moneda = $this->_config->app->moneda;

        if (isset($idProd) && ($idProd != '' && $idProd != 5) && $idProd <= 8) {

            $modelProducto = new Application_Model_Producto();

            if ($idProd <= 4) {
                $arrayProdDetalle[] = $modelProducto->getInformacionAvisoClasificado($idProd);
                $this->view->tipo   = Application_Model_AnuncioWeb::TIPO_CLASIFICADO;
                $this->view->idProd = $idProd;
            } else {
                $arrayProdDetalle[] = $modelProducto->getInformacionAvisoPreferencial($idProd);
                $this->view->tipo   = Application_Model_AnuncioWeb::TIPO_PREFERENCIAL;
                $this->view->idProd = $idProd;
            }
            $this->view->arrayProdDetalle = $arrayProdDetalle;
        } else if ($idProd == 21 || $idProd == 13) { //Web destacado
            $modelProducto                = new Application_Model_Producto();
            $arrayProdDetalle[]           = $modelProducto->getInformacionAvisoWebDestacado($idProd);
            //$this->view->tipo = Application_Model_AnuncioWeb::TIPO_DESTACADO;
            //$arrayProdDetalle[] = $modelProducto->getInformacionAvisoClasificado($idProd);
            $this->view->tipo             = Application_Model_AnuncioWeb::TIPO_CLASIFICADO;
            $this->view->idProd           = $idProd;
            $this->view->arrayProdDetalle = $arrayProdDetalle;
        } else {
            $this->_redirect('empresa/mis-procesos');
        }
        $this->_redirect("/empresa/publica-aviso/index/extiende/$idAviso");
    }

    public function candidatosSugeridosAction()
    {
        $verSugerenciaCandidatos = $this->config->profileMatch->empresa->sugerencias;

        $this->view->verSugerenciaCandidatos = $verSugerenciaCandidatos;

        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.pestana.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.sugeridos.js')
        );

        $this->view->menu_sel_side = self::MENU_POST_SIDE_CANDIDATOS_SUGERIDOS;
        $this->view->menu_post_sel = self::MENU_POST_MIS_PROCESOS;
        $this->view->menu_sel      = self::MENU_MI_CUENTA;

        $objPostulanteSugerido = new Application_Model_AnuncioPostulanteMatch();

        //--- Tabla con Paginacion --
        $paginator                   = array();
        $params                      = $this->_getAllParams();
        $sess                        = $this->getSession();
        $this->view->empresaAdminUrl = $sess->empresaAdminUrl       = $params;

        $this->view->col = $col             = $this->_getParam('col', 'fh_pub');
        $this->view->ord = $ord             = $this->_getParam('ord', 'DESC');
        $page            = $this->_getParam('page', 1);

        $paginator = $objPostulanteSugerido->getPaginadorBusquedaPersonalizada(
            $this->auth['empresa']['id'], $col, $ord
        );

        $this->view->mostrando     = "Mostrando ".
            $paginator->getItemCount($paginator->getItemsByPage($page))." de ".
            $paginator->getTotalItemCount();
        $paginator->setCurrentPageNumber($page);
        $this->view->pagina        = $paginator->getCurrentPageNumber();
        $this->view->arrayBusqueda = $paginator;
        //-- Fin de Tabla --
    }

    public function detalleCandidatosAction()
    {
        $verSugerenciaCandidatos             = $this->config->profileMatch->empresa->sugerencias;
        $this->view->verSugerenciaCandidatos = $verSugerenciaCandidatos;

        $anuncioId                = $this->_getParam('id', null);
        $anuncioModel             = new Application_Model_AnuncioWeb;
        $this->view->anuncioId    = $anuncioId;
        $this->view->photoD       = $this->mediaUrl;
        $this->view->puesto_Aviso = $this->_getParam('puesto_Aviso');
        $this->view->photoDN      = ELEMENTS_URL_CVS;
        $dataAviso                = $anuncioModel->find($anuncioId)->current();
        $this->view->anuncio      = $dataAviso;
        $params                   = $this->_getAllParams();

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
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.misprocesos.js')
        );

        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.pestana.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.cadidatos.sugeridos.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/empresa.bolsacvsgeneral.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/empresa/perfil.postulante.js')
        );

        $this->view->menu_sel_side   = self::MENU_POST_SIDE_CANDIDATOS_SUGERIDOS;
        $this->view->menu_post_sel   = self::MENU_POST_MIS_PROCESOS;
        $this->view->menu_sel        = self::MENU_MI_CUENTA;
        $this->view->anuncioId       = $anuncioId;
        $this->view->empresaAdminUrl = $sess->empresaAdminUrl       = $params;

        $objPostulanteSugerido       = new Application_Model_AnuncioPostulanteMatch();
        //--- Tabla con Paginacion --
        $paginator                   = array();
        $sess                        = $this->getSession();
        $this->view->empresaAdminUrl = $sess->empresaAdminUrl       = $params;

        $this->view->col = $col             = $this->_getParam('col', '');
        $this->view->ord = $ord             = $this->_getParam('ord', 'DESC');
        $page            = $this->_getParam('page', 1);

        $paginator = $objPostulanteSugerido->getPaginadorBusquedaAnuncioPostulantes(
            $params['id'], $col, $ord, null
        );

        $this->view->mostrando     = "Mostrando ".
            $paginator->getItemCount($paginator->getItemsByPage($page))." de ".
            $paginator->getTotalItemCount();
        $paginator->setCurrentPageNumber($page);
        $this->view->pagina        = $paginator->getCurrentPageNumber();
        $this->view->arrayBusqueda = $paginator;

        //-- Fin de Tabla --
    }

    public function detalleCandidatosAjaxAction()
    {
        $this->_helper->layout->disableLayout();

        $this->view->col     = $col                 = $this->_getParam('col', '');
        $this->view->ord     = $ord                 = $this->_getParam('ord');
        $page                = $this->_getParam('page', 1);
        $check               = $this->_getParam('check');
        $tipoLeido           = $this->_getParam('listaropcion', null);
        $this->view->photoD  = $this->mediaUrl;
        $this->view->photoDN = ELEMENTS_URL_CVS;

        $this->view->pagina      = $page;
        $anuncioId               = $this->_getParam('id');
        $this->view->anuncioId   = $anuncioId;
        $this->view->opcionlista = $tipoLeido;

        $opcion                    = "";
        $objAnuncioPostulanteMatch = new Application_Model_AnuncioPostulanteMatch();
        $paginator                 = $objAnuncioPostulanteMatch->getPaginadorBusquedaAnuncioPostulantes(
            $anuncioId, $col, $ord, $tipoLeido
        );

        $paginator->setCurrentPageNumber($page);
        $this->view->proceso       = $paginator;
        $this->view->mostrando     = "Mostrando "
            .$paginator->getItemCount($paginator->getItemsByPage($page))." de "
            .$paginator->getTotalItemCount();
        $this->view->arrayBusqueda = $paginator;
        $pagina                    = $this->view->render('mis-procesos/_maincandidatosSugeridos.phtml');

        echo $pagina;
        die();
        //-- Fin de Tabla --
    }

    public function quitarPostulantesSugeridosAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $postulanteId = $this->_getParam('data');
        $array        = explode(',', $postulanteId);
        $cant         = count(explode(',', $postulanteId));

        if ($this->_request->isPost()) {
            $objAnuncioPostulanteMatch = new Application_Model_AnuncioPostulanteMatch();
            foreach ($array as $data) {
                $arrayIds = explode('-', $data);
                $result   = $objAnuncioPostulanteMatch->quitarPostulantesSugeridos($arrayIds['0'],
                    $arrayIds['1']);
            }
        }
        $this->_response->appendBody(Zend_Json::encode($cant));
    }

    public function listaDetalleCandidatosAction()
    {
        $this->_helper->layout->disableLayout();

        $objPostulanteSugerido       = new Application_Model_AnuncioPostulanteMatch();
        //--- Tabla con Paginacion --
        $paginator                   = array();
        $params                      = $this->_getAllParams();
        $sess                        = $this->getSession();
        $this->view->empresaAdminUrl = $sess->empresaAdminUrl       = $params;
        $this->view->col             = $col                         = $this->_getParam('col',
            '');
        $this->view->ord             = $ord                         = $this->_getParam('ord',
            'ASC');
        $page                        = $this->_getParam('page', 1);

        $paginator = $objPostulanteSugerido->getPaginadorBusquedaPersonalizada(
            $params['id'], $col, $ord
        );

        $this->view->mostrando     = "Mostrando ".
            $paginator->getItemCount($paginator->getItemsByPage($page))." de ".
            $paginator->getTotalItemCount();
        $paginator->setCurrentPageNumber($page);
        $this->view->pagina        = $paginator->getCurrentPageNumber();
        $this->view->arrayBusqueda = $paginator;
        //-- Fin de Tabla --
    }

    public function candidatosSugeridosAjaxAction()
    {
        $this->_helper->layout->disableLayout();

        $this->view->col         = $col                     = $this->_getParam('col',
            '');
        $this->view->ord         = $ord                     = $this->_getParam('ord');
        $page                    = $this->_getParam('page', 1);
        $check                   = $this->_getParam('check');
        $this->view->photoD      = $this->mediaUrl;
        $this->view->photoDN     = ELEMENTS_URL_CVS;
        $this->view->opcionlista = $tipoLeido               = $this->_getParam('listaropcion',
            null);


        $this->view->pagina    = $page;
        $anuncioId             = $this->_getParam('anuncioId');
        $this->view->anuncioId = $anuncioId;

        $opcion                    = "";
        $objAnuncioPostulanteMatch = new Application_Model_AnuncioPostulanteMatch();
        $paginator                 = $objAnuncioPostulanteMatch->getPaginadorBusquedaAnuncioPostulantes(
            $anuncioId, $col, $ord, $tipoLeido
        );

        $paginator->setCurrentPageNumber($page);
        $this->view->proceso   = $paginator;
        $this->view->mostrando = "Mostrando "
            .$paginator->getItemCount($paginator->getItemsByPage($page))." de "
            .$paginator->getTotalItemCount();

        $pagina = $this->view->render('mis-procesos/_maincandidatosSugeridos.phtml');

        echo $pagina;
        die();
    }

    public function validarExtendidoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $data           = $this->_getAllParams();
        $idAw           = $data['id'];
        $idEmpresa      = $this->auth['empresa']['id'];
        $anuncio        = new App_Service_Validate_Ad($idAw);
        $usuarioEmpresa = $this->auth['usuario-empresa'];

        if (!$anuncio->isManaged($usuarioEmpresa)) {
            $this->getMessenger()->error($anuncio->getMessage());
            $this->_redirect('/empresa/mi-cuenta');
        }

        $hash  = $this->_hash->getValue();
        $array = array('hash' => $hash);
        if ($this->_request->isPost()) {
            $modelAw = new Application_Model_AnuncioWeb();
            $arrayAw = $modelAw->getAnuncioAmpliado($idAw, $idEmpresa);
            if ($arrayAw == false) {

                //Valida si el resto es mayor a 3
                $anuncio = $modelAw->getAvisoById($idAw);
                if ($anuncio['online'] == 0) {
                    $array['val'] = true;
                    //     var_dump( $array);
                    $this->_response->appendBody(Zend_Json::encode($array));
                } else {
                    $fechaAnuncio = new Zend_Date($anuncio['fpublicacion'],
                        'YYYY-MM-dd', Zend_Locale::ZFDEFAULT);
                    $diasAntes    = (int) $this->config->ampliarAviso->extension->diasantes;
                    $fechaAnuncio->subDay($diasAntes);
                    $now          = new Zend_Date();
                    if ($fechaAnuncio->isEarlier($now) || $fechaAnuncio->isToday()) {
                        $array['val'] = true;
                        $this->_response->appendBody(Zend_Json::encode($array));
                    } else {
                        $array['val']  = false;
                        $array['cond'] = $diasAntes;
                        $this->_response->appendBody(Zend_Json::encode($array));
                    }
                }
            } else {
                $array['val'] = false;
                $this->_response->appendBody(Zend_Json::encode($array));
            }
        }
    }

    public function exportarProcesoAction()
    {
        $id = $this->getRequest()->getParam("id", false);

        $dataEmp = $this->auth;

        // if (isset($dataEmp['empresa']['membresia_info']['membresia']) &&
        //    $dataEmp['empresa']['membresia_info']['membresia']['estado'] == 'vigente') {
        //  $anuncio = new App_Service_Validate_Ad($id);

        $usuarioEmpresa = $this->auth['usuario-empresa'];

//            if (!$anuncio->isManaged($usuarioEmpresa)) {
//                $this->getMessenger()->error($anuncio->getMessage());
//                $this->_redirect('/empresa/mi-cuenta');
//            }

        $servicio = new App_Service_UpdateTypeReferred();
        $servicio->updateAll($id);

        $modelPostulacion = new Application_Model_Postulacion();
        $modelAnuncioWeb  = new Application_Model_AnuncioWeb();
        $dataAnuncio      = $modelAnuncioWeb->getAvisoById($id, FALSE);

        $headers     = array('etapas del proceso', 'nombres', 'apellidos', 'dni',
            'email', 'edad',
            'sexo',
            'telefono celular', 'telefono fijo', 'lugar de residencia', 'nivel estudio',
            'carrera', 'nombre de la institución', 'idioma y nivel', 'programas y nivel',
            'fecha de postulación', 'ultima empresa donde trabajó', 'nivel de puesto',
            'nombre de puesto');
        $postulantes = $modelPostulacion->listPostulantesByAviso($id);

        App_Service_Excel::getInstance()->setHeaders($headers);
        App_Service_Excel::getInstance()->appendList(array_values($postulantes));
        App_Service_Excel::getInstance()->setLogo(
            APPLICATION_PATH.'/../public/static/img/logo_aquiesta.png'
        );
        App_Service_Excel::getInstance()->setData($dataAnuncio);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Lista de Postulantes.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter(
                App_Service_Excel::getInstance()->getObjectExcel(), 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function bloquearPostulanteAction()
    {
        //die();
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $empresaPostulanteBloqueadoModelo = new Application_Model_EmpresaPostulanteBloqueado;
        $postulacionModelo                = new Application_Model_Postulacion;
        $historicoPsModelo                = new Application_Model_HistoricoPs;
        $anuncioWebModelo                 = new Application_Model_AnuncioWeb;

        $postulante_id = $this->_getParam('postulante-id');
        $empresa_id    = $this->auth['empresa']['id'];

        $bloqueado = $empresaPostulanteBloqueadoModelo->obtenerPorEmpresaYPostulante(
            $empresa_id, $postulante_id, array('id'));

        $respuesta = Array();

        if (isset($bloqueado)) {
            $respuesta['estado']  = self::PROCESO_REDUNDANTE;
            $respuesta['mensaje'] = self::MSJ_BLOQUEO_REDUNDANTE;
            $this->_response->appendBody(Zend_Json::encode($respuesta));
            return;
        }

        $postulaciones = $postulacionModelo->obtenerPorEmpresaYPostulante(
            $empresa_id, $postulante_id, array('id', 'id_anuncio_web'));
        // die();

        $db = $this->getAdapter();

        //$db->beginTransaction();

        try {
            $empresaPostulanteBloqueadoModelo->bloquear(
                $empresa_id, $postulante_id);

            foreach ($postulaciones as $postulacion) {
                $postulacionModelo->bloquear($postulacion['id']);
                $historicoPsModelo->registarBloqueo($postulacion['id']);

                $numeroDePostulantes = $postulacionModelo->getPostulantesByAviso(
                    $postulacion['id_anuncio_web']);
                $anuncioWebModelo->actualizarPostulantes(
                    $postulacion['id_anuncio_web'], $numeroDePostulantes);
            }
        } catch (Zend_Db_Exception $e) {
            //$db->rollBack();
            $respuesta['estado']  = self::PROCESO_INCOMPLETO;
            $respuesta['mensaje'] = self::MSJ_BLOQUEO_INCOMPLETO;
            $this->_response->appendBody(Zend_Json::encode($respuesta));
            return;
        }

        $respuesta['estado']  = self::PROCESO_EXITOSO;
        $respuesta['mensaje'] = self::MSJ_BLOQUEO_EXITOSO;
        $this->_response->appendBody(Zend_Json::encode($respuesta));
    }

    public function mensajesAction()
    {
        $postulacionId = $this->_getParam('postulacion', null);

        $postulacionModelo = new Application_Model_Postulacion;
        $mensajeModelo     = new Application_Model_Mensaje;

        $empresa = $this->auth['empresa'];

        $postulacion = $postulacionModelo->obtenerDetalle(
            $postulacionId, $empresa['id']);

        if (empty($postulacion)) {
            throw new Zend_Controller_Action_Exception('Error no se encontró!',
            404);
            //$this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
            //$this->_redirect('error/page404');
        }

        $mensajes = $mensajeModelo->getMensajesPregunta($postulacionId);

        $this->view->menu_sel_side = self::MENU_POST_SIDE_PROCESOS_ACTIVOS;
        if ($postulacion['anuncio_cerrado'] ==
            Application_Model_AnuncioWeb::CERRADO) {
            $this->view->menu_sel_side = self::MENU_POST_SIDE_PROCESOS_CERRADOS;
        }

        $this->view->menu_sel      = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MIS_PROCESOS;
        $this->view->mensajes      = $mensajes;
        $this->view->postulacion   = $postulacion;
        $this->view->empresa       = $empresa;
    }

    private function _validarAccesoBaseCvs($fechaPublicacion)
    {

        $fecVenProceso = new DateTime($fechaPublicacion);
        //$diasProceso = 30 variable para configurar en el ini
        $fecVenProceso->add(new DateInterval('P'.'4'.'D'));
        $fecVen        = $fecVenProceso->format('Y-m-d');
        $fecHoy        = date("Y-m-d");
        if ($fecHoy <= $fecVen) {
            $result = 1;
        } else {
            $result = 0;
        }

        return $result;
    }

    public function estadisticasAction()
    {
        $idAviso = $this->getRequest()->getParam("id", false);
        $anuncio = new App_Service_Validate_Ad($idAviso);

        $usuarioEmpresa = $this->auth['usuario-empresa'];

        if (!$anuncio->isManaged($usuarioEmpresa)) {
            $this->getMessenger()->error($anuncio->getMessage());
            $this->_redirect('/empresa/mi-cuenta');
        }



        try {

            $avisoWeb = $this->_anuncioweb->getAvisoParaEstadisticas($idAviso);
            $urlid    = $avisoWeb['url_id'];

            $aw     = $this->_anuncioweb->getAvisoBySlugByIdAw($urlid, $idAviso);
            $acceso = Application_Model_AnuncioWeb::accesoAnuncio($aw['id_empresa'],
                    $this->auth);

            $postulacionModel = new Application_Model_Postulacion();
            $postulaciones    = $postulacionModel->getPostulantesByDia($idAviso);
            $postulantes      = $postulacionModel->getDataPostulantesByAviso($idAviso);

            $avisoCollection = new Mongo_Aviso();
            $visitas         = $avisoCollection->getVisitasByDia($urlid);


            $visitasOrd = array();
            if (!empty($visitas['retval'])) {
                foreach ($visitas['retval'] as $vis)
                    $visitasOrd[$vis['fecha']] = $vis;
            } elseif (!empty($postulaciones)) {
                foreach ($postulaciones as $pos) {
                    if (isset($pos['fecha'])) {
                        $visitasOrd[$pos['fecha']] = array('fecha' => $pos['fecha'],
                            'total' => 0);
                    }
                }
            }

            if (count($postulaciones) == 0 && count($visitas['retval']) == 0) {
                $postulaciones = array('fecha' => date('Y-m-d H:m:s'), 'total' => 0);
            }

            ksort($visitasOrd);
            $data[] = array('Dia', 'Vistos', 'Postulantes');

            $dataInitial = $data;

            $util = new App_Util();
            $post = 0;
            $vi   = 0;
            $tp   = 0;
            $tv   = 0;
            if (count($visitasOrd) > 0) {
                foreach ($visitasOrd as $vis) {
                    $post          = isset($postulaciones[$vis['fecha']]) ? (int) $postulaciones[$vis['fecha']]['total']
                            : 0;
                    $vi            = (int) $vis['total'];
                    $dFecha        = $util->dateEst($vis['fecha']);
                    $tp            = $tp + $post;
                    $tv            = $tv + $vi;
                    $data[]        = array($dFecha, $vi, $post);
                    $dataInitial[] = array($dFecha, 0, 0);
                }
            } else {
                $dataInitial[] = array('', 0, 0);
                $data[]        = array('', 0, 0);
            }



            $sexo    = $util->array_column($postulantes, 'sexo');
            $cSexo[] = array('Sexo', 'Total');

            foreach ($sexo as $key => $value) {
                if (empty($value)) {
                    unset($sexo[$key]);
                }
            }

            $acSexo = array_count_values($sexo);
            $aSV    = array('M' => 'Hombres', 'F' => 'Mujeres');
            foreach ($acSexo as $k => $v) {
                $cSexo[] = array($aSV[$k], $v);
            }
            $edad = $util->array_column($postulantes, 'edad');


            $cEdad[] = array('Edad', 'Total');
            $acEdad1 = array_count_values($edad);


            $acEdad = array();
            foreach ($acEdad1 as $k => $v) {

                $indice          = $k / 5;
                $dato            = isset($acEdad[$indice]) ? $acEdad[$indice] : '';
                $acEdad[$indice] = $dato + $v;
            }
            foreach ($acEdad as $k => $v) {
                $ini     = $k * 5;
                $fin     = $k * 5 + 4;
                $cEdad[] = array("De $ini a $fin años", $v);
            }



            $estudios    = $util->array_column($postulantes, 'estudios');
            $cEstudios[] = array('Estudios', 'Total');
            foreach ($estudios as $key => $value) {
                if (empty($value)) {
                    unset($estudios[$key]);
                }
            }
            $acEstudios = array_count_values($estudios);


            foreach ($acEstudios as $k => $v) {
                $cEstudios[] = array($k, $v);
            }

            $categoria = $util->array_column($postulantes, 'categoria');
            foreach ($categoria as $key => $value) {
                if (empty($value)) {
                    unset($categoria[$key]);
                }
            }
            $acCategoria = array_count_values($categoria);
            $cCategoria  = array();
            foreach ($acCategoria as $k => $v) {
                $d            = explode('|', $k);
                $cCategoria[] = array($d, $v);
            }
            $fechainicio = $avisoWeb['fh_pub'];



            $fechafin = date("Y-m-d H:m:s",
                strtotime($avisoWeb['fh_vencimiento']));

            $avisoWeb['fechaInicio'] = $util->setFormatDate($fechainicio);
            $avisoWeb['fechaFin']    = $util->setFormatDate($fechafin);


            $time       = strtotime($avisoWeb['fh_vencimiento']);
            $timeActual = strtotime(date('Y-m-d'));
            $totaldias  = date('d', ($time - $timeActual));

            $porcentaje = round(($totaldias / 30) * 100);

            $path                 = $this->getRequest()->getServer('REQUEST_URI');
            $this->view->redirect = $this->_helper->Aviso->EncodeRedirect($path);

            $this->view->acceso = $acceso;
            $this->view->aw     = $aw;
            $this->view->slug   = $aw['slug'];
            $this->view->urlId  = $urlid;
            $this->view->logo   = $aw['logo_empresa'];
            $this->view->online = true;

            $this->view->diasProceso = $porcentaje;
            $this->view->aviso       = $avisoWeb;
            $this->view->idAviso     = $idAviso;
            $this->view->tdias       = $totaldias;


            $this->view->tvisitas       = $tv;
            $this->view->tpostulaciones = $tp;
            $this->view->categoria      = $cCategoria;
            $this->view->dataInitial    = Zend_Json::encode($dataInitial);
            $this->view->data           = Zend_Json::encode($data);
            $this->view->sexo           = Zend_Json::encode($cSexo);
            $this->view->edad           = Zend_Json::encode($cEdad);
            $this->view->estudios       = Zend_Json::encode($cEstudios);
        } catch (Exception $ex) {
            $dataMail = array(
                'to' => 'ronald.cutisaca@ec.pe',
                'razonSocial' => $ex->getMessage(),
                'tipoAnuncio' => ',no es un aviso es estadisticas'
            );

            $this->_helper->mail->adecsysAviso($dataMail);
            $this->getMessenger()->error("El buscador esta en Mantenimiento");
            $this->_redirect('/empresa/mis-procesos/ver-proceso/id/'.$idAviso);
        }
    }
}
