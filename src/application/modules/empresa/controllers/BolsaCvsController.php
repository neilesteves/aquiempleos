<?php

class Empresa_BolsaCvsController extends App_Controller_Action_Empresa {

    /**
     *
     * @var Application_Model_BolsaCv
     */
    protected $_bolsaCVModel;
    public $_cache;
    public $empresa;

    /**
     *
     * @var Application_Model_BolsaCvPostulante
     */
    protected $_bolsaCVPostulanteModel;

    public function init() {
        parent::init();
        $this->empresa = new Application_Model_Empresa();

        $this->_usuario = new Application_Model_Usuario();
        if (isset($this->auth['usuario']->id) && $this->_usuario->hasvailBlokeo($this->auth['usuario']->id) ) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::forgetMe();
            $this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
            $this->_redirect('/empresa');
        }

        $codeBolsaCv = Application_Model_Beneficio::CODE_BOLSACV;

        $this->_bolsaCVModel = new Application_Model_BolsaCv();
        $this->_bolsaCVPostulanteModel = new Application_Model_BolsaCvPostulante();

        $this->_anuncioweb = new Application_Model_AnuncioWeb();
  
        $this->_cache=Zend_Registry::get('cache');

        
        $this->_tieneBolsaCVs=0;
        if(isset($this->auth['empresa']['membresia_info']['membresia']['id_membresia'])){
          $this->_tieneBolsaCVs = !empty($this->auth['empresa']['membresia_info']['beneficios']->bolsacv)?1:1;
        }
       $this->view->Look_Feel= $this->empresa->LooFeelActivo($this->auth['empresa']['id'])    ;
       $this->view->tieneBolsaCVs =  $this->_tieneBolsaCVs;
    }

    public function indexAction() {
      
      

        $this->view->headScript()->appendFile(
                $this->view->S('/js/empresa/perfil.postulante.js')
        );

        $this->view->headScript()->appendFile(
                $this->view->S('/js/empresa/empresa.bolsacvs.js')
        );

        $codeBuscador = Application_Model_Beneficio::CODE_BUSCADOR;

        $this->_anuncioweb = new Application_Model_AnuncioWeb();
      

        $tieneBuscadorAptitus = false;
        $tieneBolsaCVs = false;
        $this->view->tieneBuscador = $this->_anuncioweb->beneficioBuscadorAptitus($this->auth);    

        $page = $this->_getParam('page', 1);
        defined('MAX_CHARS_GROUP_NAME') || define('MAX_CHARS_GROUP_NAME', $this->config->bolsaCvs->nombreGrupo->maxChars);


        $this->view->menu_sel = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_BOLSA_CVS;
        $this->view->isAuth = $this->isAuth;

        $idEmpresa = $this->auth['empresa']['id'];
        if ($this->isAuth) {
            
        }

        $gruposEmpresa = $this->_bolsaCVModel->getGruposEmpresa($idEmpresa);

        $this->view->gruposEmpresa = $gruposEmpresa;
        $this->view->postulantes = array();

        $idGrupo = $this->_getParam('id');

        $idGrupoGeneral = $this->_bolsaCVModel->getGrupoGeneralEmpresa($idEmpresa);
        $idGrupoGeneral = $idGrupoGeneral["id"];

        if ($idGrupo == "" || $idGrupo == null) {
            $idGrupo = $idGrupoGeneral;
        } else {
            if (!$this->_bolsaCVModel->perteneceGrupoAEmpresa($idGrupo, $idEmpresa)) {
                $this->getMessenger()->error('No tiene permisos sobre el grupo.');
                $idGrupo = $idGrupoGeneral;
            }
        }
        
        $bolsaCvs = new Application_Model_BolsaCv();
        $puestos = $bolsaCvs->getPuestosEmpresa($idGrupo);
        $nivelpuestos = $bolsaCvs->getNivelPuestosEmpresa($idGrupo);
        $areas = $bolsaCvs->getAreaEmpresa($idGrupo);
        $niveldeestudios = $bolsaCvs->getNivelEstudioEmpresa($idGrupo);
        $nivelOtrosestudios = $bolsaCvs->getNivelEstudioEmpresa($idGrupo,true);
        $tipodecarrera = $bolsaCvs->getTipoCarreraEmpresa($idGrupo);
        $experiencia = $bolsaCvs->getExperienciaEmpresa($idGrupo);
        $idiomas = $bolsaCvs->getIdiomasEmpresa($idGrupo);
        $programas = $bolsaCvs->getProgramaEmpresa($idGrupo);
        $carreras = $bolsaCvs->getCarreraEmpresa($idGrupo);
        $tipodeproyecto = $bolsaCvs->getTipoProyectoEmpresa($idGrupo);
        $ubicacion = $bolsaCvs->getUbicacionEmpresa($idGrupo);
        $conadis=   $bolsaCvs->getPostulanteDisc($idGrupo);

        $this->view->dataFiltros = array(
            "puestos" => array(
                'titulo' => $this->config->buscadorempresa->urls->puestos,
                'param' => $this->config->buscadorempresa->param->puestos,
                'icon' => 'icon_star',
                'data' => $this->_prepare($puestos, 7),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "niveldepuestos" => array(
                'titulo' => $this->config->buscadorempresa->urls->niveldepuestos,
                'param' => $this->config->buscadorempresa->param->niveldepuestos,
                'icon' => 'icon_star',
                'data' => $this->_prepare($nivelpuestos, 7),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "areas" => array(
                'titulo' => $this->config->buscadorempresa->urls->areas,
                'param' => $this->config->buscadorempresa->param->areas,
                'icon' => 'icon_star',
                'data' => $this->_prepare($areas, 7),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "niveldeestudios" => array(
                'titulo' => $this->config->buscadorempresa->urls->niveldeestudios,
                'param' => $this->config->buscadorempresa->param->niveldeestudios,
                'icon' => 'icon_medal',
                'data' => $this->_prepare($niveldeestudios, 7),
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
                'data' => $this->_prepare($tipodecarrera, 7),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "experiencia" => array(
                'titulo' => $this->config->buscadorempresa->urls->experiencia,
                'param' => $this->config->buscadorempresa->param->experiencia,
                'icon' => 'icon_star',
                'data' => $this->_prepare($experiencia, 8),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "idiomas" => array(
                'titulo' => $this->config->buscadorempresa->urls->idiomas,
                'param' => $this->config->buscadorempresa->param->idiomas,
                'icon' => 'icon_message',
                'data' => $this->_prepare($idiomas, 7),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "programas" => array(
                'titulo' => $this->config->buscadorempresa->urls->programas,
                'param' => $this->config->buscadorempresa->param->programas,
                'icon' => 'icon_monitor',
                'data' => $this->_prepare($programas, 7),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "carreras" => array(
                'titulo' => $this->config->buscadorempresa->urls->carreras,
                'param' => $this->config->buscadorempresa->param->carreras,
                'icon' => 'icon_star',
                'data' => $this->_prepare($carreras, 7),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "tipodeproyecto" => array(
                'titulo' => $this->config->buscadorempresa->urls->tipodeproyecto,
                'param' => $this->config->buscadorempresa->param->tipodeproyecto,
                'icon' => 'icon_star',
                'data' => $this->_prepare($tipodeproyecto, 7),
                'expandible' => 1,
                'constantes' => $this->config->buscadorempresa->param,
                'enumeraciones' => $this->config->enumeraciones
            ),
            "ubicacion" => array(
                'titulo' => $this->config->buscadorempresa->urls->ubicacion,
                'param' => $this->config->buscadorempresa->param->ubicacion,
                'icon' => 'icon_position',
                'data' => $this->_prepare($ubicacion, 7),
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

        $nombreGrupo = "";
        $esEditable = false;
           //    var_dump($gruposEmpresa);exit;
        foreach ($gruposEmpresa as $grupo) {
            if ($idGrupo == $grupo["id"]) {
                $nombreGrupo = $grupo["nombre"];
                $esEditable = $grupo["editable"];
            }
        }

        $postulanteModel = new Application_Model_Postulante();

        
        $datosPostulantes = $postulanteModel->getDataPostulantes($idGrupo);

        $dataPostulantes = array();
        for ($i = 0; $i < count($datosPostulantes); $i++) {
            $datosPostulantes[$i]["idBolsaPostulante"] = $datosPostulantes[$i]["idpostulante"];

            $dataPostulantes[] = $datosPostulantes[$i];
        }

        $paginator = $postulanteModel->getPaginatorBolsaCVs($dataPostulantes);

        $ptic = $paginator->getTotalItemCount();
        $contAnterior = ($page - 1) * $this->config->empresa->bolsacvs->paginadopostulantes;
        $contActual = $ptic - $contAnterior;
        $viewCount = $this->config->empresa->bolsacvs->paginadopostulantes;

        if ($contActual < $viewCount) {
            $viewCount = $contActual;
        }

        $this->view->mostrando = "Mostrando " . $viewCount . " de " . $ptic . " resultado" . ($ptic != 1 ? "s" : "");
        $paginator->setCurrentPageNumber($page);
        foreach ($paginator as $item)
        {
            if(!empty($item['destacado']))
            {
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

        $this->view->postulantes = $paginator;
        $this->view->nombreGrupo = $nombreGrupo;
        $this->view->esEditable = $esEditable;
        $this->view->idGrupo = $idGrupo;
        $this->view->page = $page;
        $this->view->col = "id";
        $this->view->ord = "ASC";

        //Compartir Mail
        $formCompartir = new Application_Form_CompartirPorMail();
        $formCompartir->setAction('/mi-cuenta/compartir');
        $formCompartir->correoEmisor->setValue($this->auth['usuario']->email);

        $formCompartir->nombreEmisor->setValue(
                ucwords($this->auth['empresa']['razon_social'])
        );
        Zend_Layout::getMvcInstance()->assign('compartirPorMail', $formCompartir);
    }

    public function filtroAjaxAction() {
        $this->_helper->layout->disableLayout();

        $this->view->col = $col = $this->_getParam('col', '');
        $this->view->ord = $ord = $this->_getParam('ord');
        $page = $this->_getParam('page', 1);
        $check = $this->_getParam('check');
        $idEmpresa = $this->auth["empresa"]["id"];
        $this->view->pagina = $page;

        //cadenas del BUSCADOR
        $param = $this->config->buscadorempresa->param;
        $puestos = $this->_getParam($param->puestos);
        $niveldepuestos = $this->_getParam($param->niveldepuestos);
        $areas = $this->_getParam($param->areas);
        $niveldeestudios = $this->_getParam($param->niveldeestudios);
        $tipodecarrera = $this->_getParam($param->tipodecarrera);
        $experiencia = $this->_getParam($param->experiencia);
        $idiomas = $this->_getParam($param->idiomas);
        $programas = $this->_getParam($param->programas);
        $carreras = $this->_getParam($param->carreras);
        $edad = $this->_getParam($param->edad);
        $sexo = $this->_getParam($param->sexo);
        $tipodeproyecto = $this->_getParam($param->tipodeproyecto, "");
        $ubicacion = $this->_getParam($param->ubicacion, "");
        $conadis = $this->_getParam($param->conadis_code, "");
        $idgrupo = $this->_getParam($param->idgrupo);
        $query = htmlentities(utf8_decode($this->_getParam($param->query, "")));

        $cadenabusqueda = (($query != "") ? ($param->query . "/" . $query . "/") : "") .
                (($puestos != "") ? ($param->puestos . "/" . $puestos . "/") : "") .
                (($niveldepuestos != "") ? ($param->niveldepuestos . "/" . $niveldepuestos . "/") : "") .
                (($areas != "") ? ($param->areas . "/" . $areas . "/") : "") .
                (($niveldeestudios != "") ? ($param->niveldeestudios . "/" . $niveldeestudios . "/") : "") .
                (($tipodecarrera != "") ? ($param->tipodecarrera . "/" . $tipodecarrera . "/") : "") .
                (($experiencia != "") ? ($param->experiencia . "/" . $experiencia . "/") : "") .
                (($idiomas != "") ? ($param->idiomas . "/" . $idiomas . "/") : "") .
                (($programas != "") ? ($param->programas . "/" . $programas . "/") : "") .
                (($carreras != "") ? ($param->carreras . "/" . $carreras . "/") : "") .
                (($edad != "") ? ($param->edad . "/" . $edad . "/") : "") .
                (($sexo != "") ? ($param->sexo . "/" . $sexo . "/") : "") .
                (($tipodeproyecto != "") ? ($param->tipodeproyecto . "/" . $tipodeproyecto . "/") : "").
                (($ubicacion != "") ? ($param->ubicacion . "/" . $ubicacion . "/") : "").
                 (($conadis != "") ? ($param->conadis_code . "/" . $conadis . "/") : "");
        $this->view->cadenabusqueda = substr($cadenabusqueda, 0, strlen($cadenabusqueda) - 1);

        //fin cadenas BUSCADOR
        if ($check != 'false' && $page == 1) {

            $this->_helper->LogActualizacionBI->logActualizacionBuscadorAviso(
                    $this->_getAllParams(), $idEmpresa, Application_Model_LogBusqueda::TIPO_BUSCADOR_APTITUS
            );
        }

        $_puestos = ($puestos != "") ? explode("--", $puestos) : "";
        $_niveldepuestos = ($niveldepuestos != "") ? explode("--", $niveldepuestos) : "";
        $_areas = ($areas != "") ? explode("--", $areas) : "";
        $_niveldeestudios = ($niveldeestudios != "") ? explode("--", $niveldeestudios) : "";
        $_tipodecarrera = ($tipodecarrera != "") ? explode("--", $tipodecarrera) : "";
        $_experiencia = ($experiencia != "") ? explode("--", $experiencia) : "";
        $_idiomas = ($idiomas != "") ? explode("--", $idiomas) : "";
        $_programas = ($programas != "") ? explode("--", $programas) : "";
        $_carreras = ($carreras != "") ? explode("--", $carreras) : "";
        $conadis = ($conadis != "") ? explode("--", $conadis) : "";
//        $_sexo = ($sexo != "") ? explode("--", $sexo) : "";
        $_tipodeproyecto = ($tipodeproyecto != "") ? explode("--", $tipodeproyecto) : "";
        $_ubicacion = ($ubicacion != "") ? explode("--", $ubicacion) : "";

//        $_idanuncioweb = $this->_getParam("idanuncio");
//        $_idniveldepuestos = $this->_getParam("idniveldepuestos");
//        $_idarea = $this->_getParam("idarea");

        $_query = (!empty($query) && $query != "undefined") ? explode(" ", $query) : "";

        $id = '';
        if ($_ubicacion != "")
            foreach ($_ubicacion as $value => $key) {
                if ($_ubicacion[$value] == 3285) {

                    $id = $_ubicacion[$value];
                    unset($_ubicacion[$value]);

                    $modelUbigeo = new Application_Model_Ubigeo();
                    $arrayUbigeo = $modelUbigeo->getHijosId($id);
                    foreach ($arrayUbigeo as $data) {
                        $newCallao[] = $data['id'];
                    }
                }
            }

        if (isset($newCallao)) {
            $_ubicacion = array_merge($_ubicacion, $newCallao);
        }

        $gruposEmpresa = $this->_bolsaCVModel->getGruposEmpresa($idEmpresa);

        $this->view->gruposEmpresa = $gruposEmpresa;
        $this->view->postulantes = array();

//        $nombreGrupo = "";
        $esEditable = false;

        foreach ($gruposEmpresa as $grupo) {
            if ($idgrupo == $grupo["id"]) {
//                $nombreGrupo = $grupo["nombre"];
                $esEditable = $grupo["editable"];
            }
        }
       // var_dump($_query);exit;
        $postulantes = $this->_bolsaCVModel->listarPostulantes(
                $idgrupo,
                $col,
                $ord,
                $_puestos, 
                $_niveldepuestos,
                $_areas,
                $_niveldeestudios,
                $_tipodecarrera, 
                $_experiencia, 
                $_idiomas, 
                $_programas, 
                $_carreras, 
                $_tipodeproyecto, 
                $_ubicacion,
                $conadis,
                $_query
                );

         $idsPostulante = array();
//
        foreach ($postulantes as $key => $pos) {
            $idsPostulante[] = $pos["idPostulante"];
        }
        $postulanteModel = new Application_Model_Postulante();
        $datosPostulantes = $postulanteModel->getDataPostulantes($idgrupo,null,null,$idsPostulante);
        for ($i = 0; $i < count($datosPostulantes); $i++) {
            $datosPostulantes[$i]["idBolsaPostulante"] = $datosPostulantes[0]["idpostulante"];

            $dataPostulantes[] = $datosPostulantes[$i];
        }
        $paginator = $postulanteModel->getPaginatorBolsaCVs($dataPostulantes);

        $ptic = $paginator->getTotalItemCount();
        $contAnterior = ($page - 1) * $this->config->empresa->bolsacvs->paginadopostulantes;
        $contActual = $ptic - $contAnterior;
        $viewCount = $this->config->empresa->bolsacvs->paginadopostulantes;

        if ($contActual < $viewCount) {
            $viewCount = $contActual;
        }

        $this->view->mostrando = "Mostrando " . $viewCount . " de " . $ptic . " resultado" . ($ptic != 1 ? "s" : "");
        //$this->view->mostrando = $ptic." resultado".($ptic != 1?"s":"");
        $paginator->setCurrentPageNumber($page);

        $this->view->query = $query;
        $this->view->postulantes = $paginator;
//        $this->view->nombreGrupo = $nombreGrupo;
        $this->view->esEditable = $esEditable;
        $this->view->idGrupo = $idgrupo;
        $this->view->page = $page;
        $this->view->col = "id";
        $this->view->ord = "ASC";

        //Compartir Mail
        $formCompartir = new Application_Form_CompartirPorMail();
        $formCompartir->setAction('/mi-cuenta/compartir');
        $formCompartir->correoEmisor->setValue($this->auth['usuario']->email);

        $formCompartir->nombreEmisor->setValue(
                ucwords($this->auth['empresa']['razon_social'])
        );
        Zend_Layout::getMvcInstance()->assign('compartirPorMail', $formCompartir);

//        $this->view->mostrando = "Mostrando "
//                . $paginator->getItemCount($paginator->getItemsByPage($page)) . " de "
//                . $paginator->getTotalItemCount();

        $pagina = $this->view->render('bolsa-cvs/_main_contenido.phtml');
        echo $pagina;
        die();
    }

    public function contenidoAjaxAction() {
        $this->_helper->layout->disableLayout();

        $page = $this->_getParam('page', 1);
        $col = $this->_getParam('col', null);
        $ord = $this->_getParam('ord', null);
        // dashboard
        //Menu
        if ($col == "nivel_estudio") {
            
        }
        $this->view->menu_sel = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_BOLSA_CVS;
        $this->view->isAuth = $this->isAuth;

        $idEmpresa = $this->auth['empresa']['id'];
  

        $this->view->postulantes = array();

        $idGrupo = $this->_getParam('id');

        if (!$this->_bolsaCVModel->perteneceGrupoAEmpresa($idGrupo, $idEmpresa)) {
            exit;
        }


        $postulantes = $this->_bolsaCVPostulanteModel->postulantesPorGrupo($idGrupo);

        $idsPostulante = array();

        foreach ($postulantes as $pos) {
            $idsPostulante[] = $pos["idPostulante"];
        }

        $postulanteModel = new Application_Model_Postulante();
        $datosPostulantes = $postulanteModel->getDataPostulantes($idGrupo, $col, $ord);
        
//        if (count($idsPostulante) > 0) {
//            $datosPostulantes = $postulanteModel->getDataPostulantes($idsPostulante, $col, $ord);
//            /* for ($j = 1 ; $j < 66 ; $j++) {
//              $datosPostulantes[$j-1]["apellidos"] = "nro ".$j;
//              $datosPostulantes[] = $datosPostulantes[$j-1];
//              } */
//        } else {
//            $datosPostulantes = array();
//        }
        $dataPostulantes = array();
        for ($i = 0; $i < count($datosPostulantes); $i++) {
            $datosPostulantes[$i]["idBolsaPostulante"] = $datosPostulantes[0]["idpostulante"]; //$postulantes[$i]["id"];

            $dataPostulantes[] = $datosPostulantes[$i];
        }

        $paginator = $postulanteModel->getPaginatorBolsaCVs($dataPostulantes);

        $ptic = $paginator->getTotalItemCount();
        $contAnterior = ($page - 1) * $this->config->empresa->bolsacvs->paginadopostulantes;
        $contActual = $ptic - $contAnterior;
        $viewCount = $this->config->empresa->bolsacvs->paginadopostulantes;
        if ($contActual < $viewCount) {
            $viewCount = $contActual;
        }
        $this->view->mostrando = "Mostrando " . $viewCount . " de " . $ptic . " resultado" . ($ptic != 1 ? "s" : "");
        //$this->view->mostrando = $ptic." resultado".($ptic != 1?"s":"");
        $paginator->setCurrentPageNumber($page);

        $this->view->postulantes = $paginator;
        $this->view->idGrupo = $idGrupo;
        $this->view->page = $page;
        $this->view->col = $col;
        $this->view->ord = $ord;

        $pagina = $this->view->render('bolsa-cvs/_main_contenido.phtml');

        echo $pagina;
        die();
    }

    public function gruposEmpresaAction() {
        $this->_helper->layout->disableLayout();

        $idEmpresa = $this->auth['empresa']['id'];

        $gruposEmpresa = $this->_bolsaCVModel->getGruposEmpresa($idEmpresa);
        $this->view->gruposEmpresa = $gruposEmpresa;

        $pagina = $this->view->render('bolsa-cvs/_main_grupos.phtml');

        echo $pagina;
        die();
    }

    public function agregarGrupoAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $nombre = $this->_getParam('nombre', "");
        $token = $this->_getParam('tok_bolsa_grp','');
        
        if ($this->_hash->isValid($token))  {
                                    
            $idEmpresa = $this->auth['empresa']['id'];
            $maxChars = $this->config->bolsaCvs->nombreGrupo->maxChars;
            if ($maxChars == null || $maxChars == "") {
                $maxChars = -1;
            }

            if ($nombre == null || trim($nombre) == "") {
                $error = "Debe ingresar un nombre de grupo válido.";
            } else if ($maxChars > -1 && mb_strlen(trim($nombre)) > $maxChars) {
                $error = "El nombre de grupo debe tener menos de " . $maxChars . " caracteres.";
            } else {
                $nombre = trim($nombre);
                while (stripos($nombre, "  ")) {
                    $nombre = str_replace("  ", " ", $nombre);
                }
                if (!$this->_bolsaCVModel->existeNombre($nombre, $idEmpresa)) {
                    $this->_bolsaCVModel->crearNuevoGrupo($idEmpresa, $nombre, true);
                    $data = array(
                        'status' => 'ok',
                        'msg' => 'Se agregó un nuevo grupo',                        
                    );
                } else {
                    $error = 'El nombre del grupo ya existe';
                }
            }
                                    
        } else {
            
            $error = 'Los datos no son válidos, por favor vuelve a intentarlo.';
            
        }
        
        

        if (!isset($data)) {
            $data = array(
                'status' => 'error',
                'msg' => $error,                
            );
        }
        
//        $token = $this->_hash->getHash();        
//        $data['tok'] = $token;

        $this->_response->appendBody(Zend_Json::encode($data));
        return $data;
    }

    public function eliminarGrupoAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idEmpresa = $this->auth['empresa']['id'];
        $idBolsaCV = $this->_getParam('idGrupo');
        $salvarCVs = $this->_getParam('salvarCvs');

        $idGrupoGeneral = $this->_bolsaCVModel->getGrupoGeneralEmpresa($idEmpresa);
        $idGrupoGeneral = $idGrupoGeneral["id"];

        if ($this->_bolsaCVModel->eliminarGrupo($idBolsaCV, $salvarCVs, $idGrupoGeneral)) {
            $data = array(
                'status' => 'ok',
                'msg' => 'El grupo se eliminó correctamente'
            );
        } else {
            $data = array(
                'status' => 'error',
                'msg' => 'El grupo no se eliminó correctamente'
            );
        }
        $this->_response->appendBody(Zend_Json::encode($data));
        return $data;
    }

    public function modificarGrupoAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idGrupo = $this->_getParam('idGrupo');
        $nombre = $this->_getParam('nombre', "");
        $idEmpresa = $this->auth['empresa']['id'];
        $maxChars = $this->config->bolsaCvs->nombreGrupo->maxChars;
        if ($maxChars == null || $maxChars == "") {
            $maxChars = -1;
        }

        if ($nombre == null || trim($nombre) == "") {
            $error = "Debe ingresar un nombre de grupo válido.";
        } else if ($maxChars > -1 && mb_strlen(trim($nombre)) > $maxChars) {
            $error = "El nombre de grupo debe tener menos de " . $maxChars . " caracteres.";
        } else {
            $nombre = trim($nombre);
            while (stripos($nombre, "  ")) {
                $nombre = str_replace("  ", " ", $nombre);
            }

            if (!$this->_bolsaCVModel->existeNombre($nombre, $idEmpresa)) {
                if ($this->_bolsaCVModel->cambiarNombreGrupo($idGrupo, $nombre)) {
                    $data = array(
                        'status' => 'ok',
                        'msg' => 'Se modificó el nombre del grupo.'
                    );
                } else {
                    $error = "No se pudo modificar el nombre del grupo.";
                }
            } else {
                $error = 'Ya existe un grupo con este nombre.';
            }
        }

        if (!isset($data)) {
            $data = array(
                'status' => 'error',
                'msg' => $error
            );
        }

        $this->_response->appendBody(Zend_Json::encode($data));
        return $data;
    }

    public function getGruposAction() {
        $this->_helper->layout->disableLayout();

        $idEmpresa = $this->auth['empresa']['id'];
        $idPostulante = $this->_getParam("idPostulante", null);
        $idPostulantes = $this->_getParam("idPostulantes", null);
        $idGrupo = $this->_getParam("idGrupo", null);
        
        if (!$this->getRequest()->isXmlHttpRequest()) {
            exit;
        }
        

        if ($idGrupo != null) {
            if ($idPostulante != null) {
                if (!$this->_bolsaCVPostulanteModel->existePostulanteEnGrupo($idGrupo, $idPostulante)) {
                    echo "-1";
                    die();
                    return;
                }
            }
            $this->view->idGrupoActual = $idGrupo;
        } else {
            $this->view->idGrupoActual = -1;
        }

        if ($idPostulante != null) {
            $grupos = $this->_bolsaCVModel->getGruposSinPostulante($idPostulante, $idEmpresa);
        } else if ($idPostulantes != null) {
            $grupos = $this->_bolsaCVModel->getGruposEmpresaSinPostulantes($idEmpresa, $idPostulantes);
        } else {
            $grupos = $this->_bolsaCVModel->getGruposEmpresa($idEmpresa);
        }
        $pass = new Zend_Session_Namespace('pass');
        $pass->tokenBolsa = $this->view->tokenBolsa = md5(rand());
        
        $this->view->gruposEmpresa = $grupos;
        $pagina = $this->view->render('bolsa-cvs/_lista_grupos.phtml');


        echo $pagina;
        die();
    }

    public function agregarPostulanteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idsPostulantes = $this->_getParam('idsPostulantes');
        $idsGruposDestino = $this->_getParam('idsGruposDestino');
        $tokenBolsa = $this->_getParam('tokenBolsa');
        $pass = new Zend_Session_Namespace('pass');
        if($pass->tokenBolsa!=$tokenBolsa||empty($pass->tokenBolsa))
            $this->_redirect('/empresa/mi-cuenta');
        else
            $pass->tokenBolsa = md5(rand());

        foreach ($idsPostulantes as $idPostulante) {
            $this->_bolsaCVPostulanteModel->agregarPostulante($idPostulante, $idsGruposDestino);
        }
         if(isset($idsGruposDestino)){
             foreach ($idsGruposDestino as $idBolsa) {
                    @$this->_cache->remove('BolsaCv_getPuestosEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getNivelPuestosEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getAreaEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getNivelEstudioEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getNivelEstudioEmpresa_1'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getTipoCarreraEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getExperienciaEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getIdiomasEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getProgramaEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getCarreraEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getTipoProyectoEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getUbicacionEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_listarPostulantes_'.md5($idBolsa) ); 
                    
                   // @$this->_cache->remove('BolsaCvPostulante_listarDataPostulantes_'.md5($idBolsa) ); 

             }
            
        }
        $mensaje = 'El candidato fue agregado correctamente a la Bolsa de CVs.';
        if (count($idsPostulantes) > 1) {
            $mensaje = 'Los postulantes fueron agregados correctamente a la Bolsa de CVs.';
        }
        $data = array(
            'status' => 'ok',
            'msg' => $mensaje
        );

        $this->_response->appendBody(Zend_Json::encode($data));
        return $data;
    }

    public function moverPostulanteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idGrupoOrigen = $this->_getParam('idGrupoOrigen', null);
        $idsPostulantes = $this->_getParam('idsPostulantes');
        $idsGruposDestino = $this->_getParam('idsGruposDestino');

        foreach ($idsPostulantes as $idPostulante) {
            $this->_bolsaCVPostulanteModel->agregarPostulante($idPostulante, $idsGruposDestino);
            if ($idGrupoOrigen != null) {
                $this->_bolsaCVPostulanteModel->eliminarPostulanteDeGrupo($idPostulante, $idGrupoOrigen);
            }
        }
        if(isset($idsGruposDestino)){
             foreach ($idsGruposDestino as $idBolsa) {
                    @$this->_cache->remove('BolsaCv_getPuestosEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getNivelPuestosEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getAreaEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getNivelEstudioEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getNivelEstudioEmpresa_1'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getTipoCarreraEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getExperienciaEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getIdiomasEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getProgramaEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getCarreraEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getTipoProyectoEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_getUbicacionEmpresa_'.$idBolsa );
                    @$this->_cache->remove('BolsaCv_listarPostulantes_'.md5($idBolsa) ); 
            }            
        }        
        if(isset($idGrupoOrigen) && $idGrupoOrigen != null) {
            @$this->_cache->remove('BolsaCv_getPuestosEmpresa_'.$idGrupoOrigen );
            @$this->_cache->remove('BolsaCv_getNivelPuestosEmpresa_'.$idGrupoOrigen );
            @$this->_cache->remove('BolsaCv_getAreaEmpresa_'.$idGrupoOrigen );
            @$this->_cache->remove('BolsaCv_getNivelEstudioEmpresa_'.$idGrupoOrigen );
            @$this->_cache->remove('BolsaCv_getNivelEstudioEmpresa_1'.$idGrupoOrigen );
            @$this->_cache->remove('BolsaCv_getTipoCarreraEmpresa_'.$idGrupoOrigen );
            @$this->_cache->remove('BolsaCv_getExperienciaEmpresa_'.$idGrupoOrigen );
            @$this->_cache->remove('BolsaCv_getIdiomasEmpresa_'.$idGrupoOrigen );
            @$this->_cache->remove('BolsaCv_getProgramaEmpresa_'.$idGrupoOrigen );
            @$this->_cache->remove('BolsaCv_getCarreraEmpresa_'.$idGrupoOrigen );
            @$this->_cache->remove('BolsaCv_getTipoProyectoEmpresa_'.$idGrupoOrigen );
            @$this->_cache->remove('BolsaCv_getUbicacionEmpresa_'.$idGrupoOrigen ); 
           @$this->_cache->remove('BolsaCv_listarPostulantes_'.md5($idGrupoOrigen) ); 
        }
        $msgTipo = ($idGrupoOrigen == null) ? "copiado" : "movido";
        if (count($idsPostulantes) > 1) {
            $msgTipo = ($idGrupoOrigen == null) ? "copiados" : "movidos";
        }

        $mensaje = 'El postulante fue ' . $msgTipo . ' correctamente.';
        if (count($idsPostulantes) > 1) {
            $mensaje = 'Los postulantes fueron ' . $msgTipo . ' correctamente.';
        }
        $data = array(
            'status' => 'ok',
            'msg' => $mensaje
        );

        $this->_response->appendBody(Zend_Json::encode($data));
        return $data;
    }

    public function eliminarPostulanteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idGrupoOrigen = $this->_getParam('idGrupoOrigen');
        $idsPostulantes = $this->_getParam('idsPostulantes');

        foreach ($idsPostulantes as $idPostulante) {
            $this->_bolsaCVPostulanteModel->eliminarPostulanteDeGrupo($idPostulante, $idGrupoOrigen);
        }
        if(isset($idGrupoOrigen)){
            // foreach ($idGrupoOrigen as $idBolsa) {
                    @$this->_cache->remove('BolsaCv_getPuestosEmpresa_'.$idGrupoOrigen );
                    @$this->_cache->remove('BolsaCv_getNivelPuestosEmpresa_'.$idGrupoOrigen );
                    @$this->_cache->remove('BolsaCv_getAreaEmpresa_'.$idGrupoOrigen );
                    @$this->_cache->remove('BolsaCv_getNivelEstudioEmpresa_'.$idGrupoOrigen );
                    @$this->_cache->remove('BolsaCv_getNivelEstudioEmpresa_1'.$idGrupoOrigen );
                    @$this->_cache->remove('BolsaCv_getTipoCarreraEmpresa_'.$idGrupoOrigen );
                    @$this->_cache->remove('BolsaCv_getExperienciaEmpresa_'.$idGrupoOrigen );
                    @$this->_cache->remove('BolsaCv_getIdiomasEmpresa_'.$idGrupoOrigen );
                    @$this->_cache->remove('BolsaCv_getProgramaEmpresa_'.$idGrupoOrigen );
                    @$this->_cache->remove('BolsaCv_getCarreraEmpresa_'.$idGrupoOrigen );
                    @$this->_cache->remove('BolsaCv_getTipoProyectoEmpresa_'.$idGrupoOrigen );
                    @$this->_cache->remove('BolsaCv_getUbicacionEmpresa_'.$idGrupoOrigen ); 
                   @$this->_cache->remove('BolsaCv_listarPostulantes_'.md5($idGrupoOrigen) ); 
           //  }
            
        }
        $mensaje = 'El candidato fue eliminado del grupo.';
        if (count($idsPostulantes) > 1) {
            $mensaje = 'Los candidatos fueron eliminados del grupo.';
        }
        $data = array(
            'status' => 'ok',
            'msg' => $mensaje
        );

        $this->_response->appendBody(Zend_Json::encode($data));
        return $data;
    }

    public function anadirNotasBolsaAction() {
        $this->_helper->layout->disableLayout();
        $this->view->form = new Application_Form_Nota();
        $pagina = $this->view->render('bolsa-cvs/anadirNotas.phtml');

        echo $pagina;
        die();
    }

    public function registrarNotasBolsaAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $config = Zend_Registry::get("config");

        $idPostulante = $this->_getParam("idPostulante", null);
        $idEmpresa = $this->auth['empresa']['id'];
        $idPostulante = $this->_getParam("idPostulante", null);
        $nota = $this->_getParam("nota", "");
        $idUsuario = $this->auth['usuario']->id;
        $nota = trim($nota);

        //@codingStandardsIgnoreStart
        $nota = preg_replace($config->avisopaso2->expresionregular, '', $nota);
        //@codingStandardsIgnoreEnd

        $nota = str_replace("@", "", $nota);
        $empresaPostulanteModel = new Application_Model_EmpresaPostulante();
        $idEP = $empresaPostulanteModel->getEmpresaPostulante($idEmpresa, $idPostulante);

        if ($nota != "" && $idEP != null) {
            $notaBolsaModel = new Application_Model_NotaPostulanteBolsa();
            $notaBolsaModel->crear($idEP, $idUsuario, $nota);

            $data = array(
                'status' => 'ok',
                'msg' => 'La nota se agregó correctamente.'
            );
        } else {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo agregar la nota correctamente.'
            );
        }

        $this->_response->appendBody(Zend_Json::encode($data));
        return $data;
    }

    public function editarNotaBolsaAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idEmpresa = $this->auth['empresa']['id'];
        $idNota = $this->_getParam("idNota", null);
        $nota = $this->_getParam("nota", "");
        $nota = trim($nota);

        if ($nota != "" && $idEmpresa != null && $idNota != null) {
            $notaBolsaModel = new Application_Model_NotaPostulanteBolsa();
            $notaAntigua = $notaBolsaModel->getNotaBolsa($idNota);
            if (strtolower($notaAntigua["nota"]) == strtolower($nota)) {
                $data = array(
                    'status' => 'warning',
                    'msg' => 'Debe cambiar el texto de la nota.'
                );
            } else {
                if ($notaBolsaModel->editar($idNota, $nota)) {
                    $data = array(
                        'status' => 'ok',
                        'msg' => 'La nota se editó correctamente.'
                    );
                }
            }
        }

        if (!isset($data)) {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo editar la nota correctamente.'
            );
        }
        $this->_response->appendBody(Zend_Json::encode($data));
        return $data;
    }

    public function getVistaNotaBolsaAction() {
        $this->_helper->layout->disableLayout();

        $idEmpresa = $this->auth['empresa']['id'];
        $idNota = $this->_getParam("idNota", null);
        $idPostulante = $this->_getParam("idPostulante", null);
        $notaPostulanteModel = new Application_Model_NotaPostulanteBolsa();
        $nota = array();

        if ($idPostulante != null) {
            $empresaPostulanteModel = new Application_Model_EmpresaPostulante();
            $idEP = $empresaPostulanteModel->getEmpresaPostulante($idEmpresa, $idPostulante);
        }

        if ($idNota == null) {
            $nota = $notaPostulanteModel->getUltimaNotaPostulanteEmpresa($idEP);
        } else {
            $nota = $notaPostulanteModel->getNotaBolsa($idNota);
        }

        $form = new Application_Form_Nota(true);

        if ($nota != null) {
            $nota['text'] = $nota['nota'];
            $nota['mostrar'] = true;
            $nota['contar'] = count($notaPostulanteModel->getNotasPostulanteEmpresa($idEP));

            $form->setHiddenId($nota['id']);
            $form->setDefaults($nota);
        }

        $this->view->form = $form;
        $this->view->nota = $nota;

        $pagina = $this->view->render('_partials/_nota_bolsa.phtml');

        echo $pagina;
        die();
    }

    public function eliminarNotaBolsaAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idEmpresa = $this->auth['empresa']['id'];
        $idNota = $this->_getParam("idNota", null);

        if ($idEmpresa != null && $idNota != null) {
            $notaBolsaModel = new Application_Model_NotaPostulanteBolsa();
            if ($notaBolsaModel->eliminar($idNota)) {
                $data = array(
                    'status' => 'ok',
                    'msg' => 'La nota se eliminó correctamente.'
                );
            }
        }

        if (!isset($data)) {
            $data = array(
                'status' => 'error',
                'msg' => 'No se pudo eliminar la nota correctamente.'
            );
        }

        $this->_response->appendBody(Zend_Json::encode($data));
        return $data;
    }

    public function perfilPostulanteAction() {
        $idPostulante = $this->_getParam('id');
        //-----------------------------------------------------------------------------
        $modeloPostulanteBloqueado = new Application_Model_EmpresaPostulanteBloqueado();

        $getBloqueado = $modeloPostulanteBloqueado->
                obtenerPorEmpresaYPostulante(
                (int) $this->auth['empresa']['id'], $idPostulante, array('id' => 'id')
        );

        $this->view->btnBloqueado = 0;

        if ((int) $getBloqueado['id'] > 0) {
            $this->view->btnBloqueado = 1;
        }
        //-----------------------------------------------------------------------------
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        /*
          $this->view->menu_sel = self::MENU_MI_CUENTA;
          $this->view->menu_post_sel = self::MENU_POST_MIS_PROCESOS;
         */

        Zend_Layout::getMvcInstance()->assign(
                'bodyAttr', array('id' => 'profilePublic')
        );
     
        $idGrupo = $this->_getParam('idGrupo');
        $grupo = $this->_bolsaCVModel->getGrupo($idGrupo);
        $this->view->nombreGrupo = $grupo["nombre"];

        //Postulante Datos

        $postulante = new Application_Model_Postulante();
        $perfil = $postulante->getPerfil($idPostulante);

        if ($perfil == false) {
            $this->_redirect('/');
        }

        if(!empty($perfil['postulante']['destacado']))
        {
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


        $postulacionesModel = new Application_Model_Postulacion();
        $dataPostulaciones = $postulacionesModel->getPostulacionesByEmpresaYPostulante($idPostulante, $idEmpresa);
        $nombreProcesos = "";

        $mensajeNombreProcesos = "Actualmente el postulante se encuentra como candidato en ";
        $verMensajeProcesos = true;
        if (count($dataPostulaciones) > 1) {
            $mensajeNombreProcesos .= "los procesos:";
        } else if (count($dataPostulaciones) == 1) {
            $mensajeNombreProcesos .= "el proceso:";
        } else {
            $verMensajeProcesos = false;
        }

        if ($verMensajeProcesos) {
            foreach ($dataPostulaciones as $dataPostulacion) {
                $nombreProcesos = $nombreProcesos . $dataPostulacion["puesto"] . ", ";
            }

            if (strlen($nombreProcesos) > 2) {
                $nombreProcesos = substr($nombreProcesos, 0, strlen($nombreProcesos) - 2);
            }
        }

        $perfil['postulante']['mensajeProcesos'] = array();
        $perfil['postulante']['mensajeProcesos']['verMensajeProcesos'] = $verMensajeProcesos;
        $perfil['postulante']['mensajeProcesos']['mensajeNombreProcesos'] = $mensajeNombreProcesos;
        $perfil['postulante']['mensajeProcesos']['nombreProcesos'] = $nombreProcesos;



        //Datos Para Perfil
        $perfil['postulante']['fotovariable'] = $perfil['postulante']['path_foto_uno'];
        $perfil['postulante']['verLastUpdate'] = true;

        $this->view->postulante = $perfil;
        $usuario = $this->auth['usuario'];

        $formCompartir = new Application_Form_CompartirPorMail();
        $formCompartir->setAction('/mi-cuenta/compartir');
        $formCompartir->correoEmisor->setValue($usuario->email);
        $formCompartir->hdnOculto->setValue(
                $perfil['postulante']['slug']
        );     
 
        $formCompartir->nombreEmisor->setValue(
                ucwords($this->auth['empresa']['razon_social'])       
        );
        Zend_Layout::getMvcInstance()->assign(
                'compartirPorMail', $formCompartir
        );

        //Notas
        $baseFormNota = new Application_Form_Nota(true);

        $formsNota = array();
        $arrayNotas = array();
        $index = 0;

        $empresaPostulanteModel = new Application_Model_EmpresaPostulante();
        $idEP = $empresaPostulanteModel->getEmpresaPostulante($idEmpresa, $idPostulante);


        $idUsuario = $this->auth['usuario']->id;
        $notaPostulanteModel = new Application_Model_NotaPostulanteBolsa();
        $arrayNotas = $notaPostulanteModel->getNotasPostulanteEmpresa($idEP);
        $count = $max = count($arrayNotas);
        if ($count != 0) {
            foreach ($arrayNotas as $key => $nota) {
                //$index++;
                $nota['text'] = $nota['nota'];
                if ($count == $max) {
                    $nota['mostrar'] = true;
                    $nota['contar'] = $count;
                } else {
                    $nota['mostrar'] = false;
                    $nota['contar'] = $count;
                }
                $count--;
                $form = new Application_Form_Nota(true);
                $form->setHiddenId($nota['id']);
                $form->setDefaults($nota);
                $formsNota[] = $form;
                $valNotas[$key] = $nota;
            }
        } else {
            $valNotas = null;
        }

        $emptyFormNota = new Application_Form_Nota(true);
        $emptyNota = array(
            'fecha' => date('Y-m-d H:i:s'),
            'text' => null,
            'path_original' => null,
            'id_nota' => -1,
            'mostrar' => true
        );

        $this->view->formNota = $formsNota;
        $this->view->notas = $valNotas;
        $this->view->emptyFormNota = $emptyFormNota;
        $this->view->emptyNotas = $emptyNota;


        /*
          $html = $this->render('perfil-publico-emp');

          $res = array(
          'html' => $html,
          );
         */

        $pagina = $this->view->render('bolsa-cvs/perfil-postulante-old.phtml');

        echo $pagina;
        die();
    }

    private function _prepare($data, $n) {
        $dataChunks = array_chunk($data, $n);
        $nchunks = count($dataChunks);
        $ocultos = $nchunks > 1 ? array_slice($dataChunks, 1, $nchunks - 1) : array(
        );
        return array(
            'visible' => count($dataChunks) ? $dataChunks[0] : array(),
            'ocultos' => $ocultos
        );
    }

    public function exportarResultadoAction() {
        $id = $this->getRequest()->getParam("idgrupo", false);

        $postulantes = $this->_bolsaCVPostulanteModel->postulantesPorGrupo($id, FALSE);

        $dataPostulantes = $this->_bolsaCVPostulanteModel->listarDataPostulantes($postulantes);

        $headers = array('DNI', 'Nombres', 'Apellidos', 'Edad', 'Sexo',
            'Telefono celular', 'Telefono fijo', 'Lugar de Residencia', 'Nivel estudio',
            'Carrera', 'Nombre de la Institución', 'Nivel de Ingles');

        App_Service_Excel::getInstance()->setHeaders($headers);
        App_Service_Excel::getInstance()->appendList(array_values($dataPostulantes));
        App_Service_Excel::getInstance()->setLogo(
                APPLICATION_PATH . '/../public/static/images/emailing/logo.png'
        );

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Lista de Postulantes.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter(App_Service_Excel::getInstance()->getObjectExcel(), 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function exportarResultadoFiltroAction() {
        $this->_helper->layout->disableLayout();

        $this->view->col = $col = $this->_getParam('col', '');
        $this->view->ord = $ord = $this->_getParam('ord');

        //cadenas del BUSCADOR
        $param = $this->config->buscadorempresa->param;
        $puestos = $this->_getParam($param->puestos);
        $niveldepuestos = $this->_getParam($param->niveldepuestos);
        $areas = $this->_getParam($param->areas);
        $niveldeestudios = $this->_getParam($param->niveldeestudios);
        $tipodecarrera = $this->_getParam($param->tipodecarrera);
        $experiencia = $this->_getParam($param->experiencia);
        $idiomas = $this->_getParam($param->idiomas);
        $programas = $this->_getParam($param->programas);
        $carreras = $this->_getParam($param->carreras);
        $tipodeproyecto = $this->_getParam($param->tipodeproyecto, "");
        $ubicacion = $this->_getParam($param->ubicacion, "");
        $idgrupo = $this->_getParam($param->idgrupo);
        $query = htmlentities(utf8_decode($this->_getParam($param->query, "")));

        $_puestos = ($puestos != "") ? explode("--", $puestos) : "";
        $_niveldepuestos = ($niveldepuestos != "") ? explode("--", $niveldepuestos) : "";
        $_areas = ($areas != "") ? explode("--", $areas) : "";
        $_niveldeestudios = ($niveldeestudios != "") ? explode("--", $niveldeestudios) : "";
        $_tipodecarrera = ($tipodecarrera != "") ? explode("--", $tipodecarrera) : "";
        $_experiencia = ($experiencia != "") ? explode("--", $experiencia) : "";
        $_idiomas = ($idiomas != "") ? explode("--", $idiomas) : "";
        $_programas = ($programas != "") ? explode("--", $programas) : "";
        $_carreras = ($carreras != "") ? explode("--", $carreras) : "";
        $_tipodeproyecto = ($tipodeproyecto != "") ? explode("--", $tipodeproyecto) : "";
        $_ubicacion = ($ubicacion != "") ? explode("--", $ubicacion) : "";

        $_query = (!empty($query) && $query != "undefined") ? explode(" ", $query) : "";

        $id = '';
        if ($_ubicacion != "")
            foreach ($_ubicacion as $value => $key) {
                if ($_ubicacion[$value] == 3285) {

                    $id = $_ubicacion[$value];
                    unset($_ubicacion[$value]);

                    $modelUbigeo = new Application_Model_Ubigeo();
                    $arrayUbigeo = $modelUbigeo->getHijosId($id);
                    foreach ($arrayUbigeo as $data) {
                        $newCallao[] = $data['id'];
                    }
                }
            }

        if (isset($newCallao)) {
            $_ubicacion = array_merge($_ubicacion, $newCallao);
        }

        $postulantes = $this->_bolsaCVModel->listarPostulantes(
                $idgrupo, $col, $ord, $_puestos, $_niveldepuestos, $_areas, $_niveldeestudios, $_tipodecarrera, $_experiencia, $_idiomas, $_programas, $_carreras, $_tipodeproyecto, $_ubicacion, $_query);
        
        $dataPostulantes = $this->_bolsaCVPostulanteModel->listarDataPostulantes($postulantes);

        $headers = array('DNI', 'Nombres', 'Apellidos', 'Edad', 'Sexo',
            'Telefono celular', 'Telefono fijo', 'Lugar de Residencia', 'Nivel estudio',
            'Carrera', 'Nombre de la Institución', 'Nivel de Ingles');

        App_Service_Excel::getInstance()->setHeaders($headers);
        App_Service_Excel::getInstance()->appendList(array_values($dataPostulantes));
        App_Service_Excel::getInstance()->setLogo(
                APPLICATION_PATH . '/../public/static/images/emailing/logo.png'
        );
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Lista de Postulantes.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter(App_Service_Excel::getInstance()->getObjectExcel(), 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

}
