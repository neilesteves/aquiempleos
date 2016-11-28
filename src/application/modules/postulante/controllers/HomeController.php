<?php

class Postulante_HomeController extends App_Controller_Action_Postulante
{
    protected $_cache          = null;
    protected $_messageSuccess = 'Sus fueron encontrados.';
    public $_messageError;

    public function init()
    {
        parent::init();
        $this->_messageError = 'Vuelva a intentarlo por favor';
        $this->_cache        = Zend_Registry::get('cache');
    }

    public function landingAction()
    {
        $this->_helper->layout->setLayout('landing');
        $this->view->headMetas()->setTitle('Encuentra Ofertas de Empleo | AquiEmpleos');
        $this->view->headMetas()->setDescription('Encuentra Ofertas de Trabajo y Empleo en aquiempleos.com');
        $this->view->headMetas()->setKeywords('empleo,trabajo,encuentra');
        $data                  = array(
            'limit' => '7'
        );

        $modelapi              = new Api_Model_Postulante();
        $dt                    = $modelapi->listLanding($data);
        $this->view->listAviso = $dt['records'];
    }

    public function widgetAction()
    {
        $dataempleo = array(
            'limit' => '7'
        );
        header('Access-Control-Allow-Origin: *');
        $modelapi = new Api_Model_Postulante();
        $dtempleo = $modelapi->listLandingEmpleo($dataempleo);
        $dtcasa   = $modelapi->listLandingCasa();
        $dtauto   = $modelapi->listLandingCarro();

        $this->view->listAviso       = $dtempleo['records'];
        $this->view->listAvisoCasa   = $dtcasa['records'];
        $this->view->listAvisoCarro = $dtauto['records'];
        $this->_helper->layout->setLayout('widget');
    }

    public function indexAction()
    {

        $this->view->headMetas()->setTitle('Bolsa de Trabajo y Ofertas de Empleo en Perú - AquiEmpleos | aquiempleos.com');

        $this->_config                 = Zend_Registry::get('config');
        $logoFb                        = $this->_config->app->mediaUrl.'/images/logo_fb.jpg';
        $this->view->authEmpresa       = false;
        $this->view->movil             = 0;
        $this->view->postulante        = 'no-logueado';
        $this->view->isPostulanteLoged = false;
        if (isset($this->auth['usuario']) && $this->auth['usuario']->rol == 'postulante') {
            $this->view->postulante        = $this->auth['usuario']->id;
            $this->view->isPostulanteLoged = true;
            $this->view->hideRegBox        = 1;
        } elseif (isset($this->auth['usuario']) && (
            $this->auth['usuario']->rol == 'empresa-admin' ||
            $this->auth['usuario']->rol == 'empresa-usuario')) {
            $this->view->authEmpresa = $this->auth;
            $this->view->hideRegBox  = 1;
        }
        $urlUri = $this->getRequest()->getRequestUri();

        if (stristr($urlUri, '/postulante')) {
            $title = 'Postular a empleos en Perú | aquiempleos.com';
        } else {
            $title = 'Bolsa de trabajo y ofertas de trabajo | aquiempleos.com';
        }
        $keywords = "Ofertas de trabajo, Bolsas de trabajo, avisos de trabajo, "
            ."empleos peru, buscar empleo,  empleo, "
            ."búsqueda de empleo, búsqueda de trabajo, empleo La Prensa";

        $description = "Encuentra  las mejores Ofertas de Trabajo en Perú. "
            ."Actualiza tu curriculum y postula a los puestos de trabajo de acuerdo "
            ."a tu Perfil.";

        $aviso           = array(
            'image' => array($logoFb),
            'site' => (string) $this->_config->openGraph->urlSite,
            //     'type'=> App_Service_SEO_OpenGraph::TYPE,
            'title' => $title,
            'url' => (string) $this->_config->openGraph->urlSite,
            'description' => $description,
            'keywords' => $keywords
        );
        $rutaLogoDefecto = $this->config->defaultLogoEmpresa->fileName;
        $verLogoDefecto  = (bool) $this->config->defaultLogoEmpresa->enabled;

        $sesionMsg = new Zend_Session_Namespace("facebook");
        if (isset($sesionMsg->mensaje)) {
            $this->getMessenger()->error($sesionMsg->mensaje);
            unset($sesionMsg->mensaje);
        }

        $idUsuAdmin = isset($this->auth['usuario']->id) ? $this->auth['usuario']->id
                : 0;



        $this->view->getmenssages = array();


        $this->view->auth = false;

        $this->view->logoDefecto    = $rutaLogoDefecto;
        $this->view->verLogoDefecto = $verLogoDefecto;

        //Menu
        $this->view->menu_sel = self::MENU_INICIO;
        $config               = $this->getConfig();
        $params               = $this->_getAllParams();
        $params['rows']       = 6;
        $buscaMas             = new Solr_SolrAviso();
        $resultado            = $buscaMas->obtenerResultadoBuscaMas($params);
        $decode               = Zend_Json::decode($resultado);

        //$postulante                   = new Solr_SolrPostulante();
        //$this->view->totalPostulantes = $postulante->getPostulantes();
        //obtenemos la cantidad de postulantes de la base de datos
        $postulante2                  = new Application_Model_Postulante();
        $this->view->totalPostulantes = $postulante2->getCantPostulantes();


        $dataAvisoResiente     = array();
        $this->view->ntotalhoy = 0;
        if (count($decode) > 0) {
            foreach ($decode["filter"]['fecha'] as $key => $value) {
                if ($value['slug'] == 'hoy') {
                    $this->view->ntotalhoy = number_format(($decode["filter"]['fecha'][0]["count"])
                                ? (isset($decode["filter"]['fecha'][0]["count"])
                                    ? $decode["filter"]['fecha'][0]["count"] : 0)
                                : 0);
                }
            }
            $this->view->ntotal               = number_format($decode['ntotal']);
            $this->view->totalEmpresaPublican = count($decode['filter']['company_slug']);
            $areasJSON                        = $decode['filter']['area'];
            $nivelJSON                        = $decode['filter']['level'];
            $ubicacionJSON                    = $decode['filter']['location'];
            $dataAvisoResiente                = count($decode["data"]) > 0 ? $decode["data"]
                    : array();
            $areaValorDesc                    = $buscaMas->ordenarArray($areasJSON,
                'count', true, 14);
            $areaDescDesc                     = $buscaMas->ordenarArray($areasJSON,
                'label', false);
            //$areasjjc= new Application_Model_Area();
            $nivelValorDesc                   = $buscaMas->ordenarArray($nivelJSON,
                'count', true);
            $nivelDescDesc                    = $buscaMas->ordenarArray($nivelJSON,
                'label', false);

            $ubiValorDesc = $buscaMas->ordenarArrayUbicacion($ubicacionJSON,
                'count', true);
            $ubiDescDesc  = $buscaMas->ordenarArrayUbicacion($ubicacionJSON,
                'label', false);
        } else {
            $this->view->ntotal    = 0;
            $this->view->ntotalhoy = 0;
            $areaValorDesc         = array();
            $areaDescDesc          = array();
            $nivelValorDesc        = array();
            $nivelDescDesc         = array();
            $ubiValorDesc          = array();
            $ubiDescDesc           = array();
        }
//var_dump($areaDescDesc);exit;
        // action body
        $anunciosWeb = new Application_Model_AnuncioWeb();

        $this->view->baseURLLogos      = $this->config->app->elementsUrlLogos;
        $this->view->listAreasAvisos   = $areaValorDesc;
        //$this->view->groupAreas2 = $areaDescDesc;
        $this->view->groupNivelPuesto1 = $nivelValorDesc;
        $this->view->groupNivelPuesto2 = $nivelDescDesc;
        $this->view->groupDistritos1   = $ubiValorDesc;
        $this->view->groupDistritos2   = $ubiDescDesc;

        $this->view->listAvisosDestacados = $anunciosWeb->getUltimosAvisosDestacados(); //+ $anunciosWeb->getUltimosAvisosMembresias();
        $this->view->avisoResientes       = $dataAvisoResiente;
        //   var_dump($dataAvisoResiente,$decode);exit;
        $modelEmpresa                     = new Application_Model_Empresa();
        $limitTCN                         = 5;
        $logos                            = array(
            "atlantic.png", "hemco.png", "outsource.png", "unicomer.png", "sitel.png"
        );
        $dataEmpresaMembresia             = $modelEmpresa->getCompanyWithMembresia($limitTCN);
        //  var_dump($dataEmpresaMembresia);exit;
        $this->view->posicion             = 0;
        $this->view->logosempresa         = $logos;
        $this->view->tcn                  = $dataEmpresaMembresia;

        $this->view->Totaltcn = number_format(count($decode['filter']['company_slug']));
        //Cargar Formulario de busqueda Avanzada
        $form                 = new Application_Form_BuscarHome();
        $form->setAreas($areaDescDesc);
        // $form->setNivelPuestos($puesto->getPuestosSlug());
        $form->setUbicacion($ubiDescDesc);

        $this->view->form = $form;

        //Banner
        $this->view->urlScript = $this->getConfig()->urlsExternas
            ->postulante
            ->bannerPortada
            ->url;

        //Blogs de Portada
        $this->view->limite = $this->getConfig()->portadaPostulante
            ->listaArticulosInteres
            ->limite;
        $this->view->isAuth = $this->isAuth;

        // Message Error Login Postulante
        $messageError              = new Zend_Session_Namespace('messageError');
        $this->view->messageErrorP = $messageError->string;
        $messageError->setExpirationSeconds(1);
        $tokenFacebook             = new Zend_Session_Namespace('tokenFacebook');
        $this->view->tokenFacebook = $tokenFacebook->token      = md5(uniqid(rand(),
                1));

        $change = new Zend_Session_Namespace('changeHome');
        if (isset($change->state)) {
            $this->view->vowel = 'o';
            unset($change->state);
        } else {
            $this->view->vowel = 'a';
            $change->state     = 1;
        }
    }

    public function queEsAptitusAction()
    {
        //Menu
        $this->view->menu_sel = self::MENU_QUE_ES_APTITUS;
        $this->view->headLink()->appendStylesheet(
            $this->view->S(
                '/css/main.informativas.css')
        );
        $this->view->headMeta()->appendName(
            "Description",
            "En nuestra página web los postulantes se contactan con las".
            " empresas más exitosas y reconocidas del mercado en empleoBusco.com "
        );
        $this->view->headTitle()->set('Buscar trabajo | EMPLEOS');
        // action body
    }

    public function ingresarMovilAction()
    {

    }

    public function sql1Action()
    {
        $cj = new CronJobs_cronjob();
        $cj->sql1();
        exit;
    }

    public function politicaPrivacidadAction()
    {
        $config          = Zend_Registry::get('config');
        $this->view->headLink()->appendStylesheet(
            $this->view->S(
                '/css/main.informativas.css')
        );
        $this->view->dni = $config->app->dni;
    }

    public function porqueUsarAptitusAction()
    {
        $this->view->headTitle()->set('Beneficios de anunciar en EMPLEOS | EMPLEOS');
        $this->view->headLink()->appendStylesheet(
            $this->view->S(
                '/css/main.informativas.css')
        );
    }

    public function terminosDeUsoAction()
    {
        $this->view->headLink()->appendStylesheet(
            $this->view->S(
                '/css/main.informativas.css')
        );
    }

    private function sendMailFromContactenos($values)
    {
        try {
            //enviar Mail
            $this->view->ispost       = false;
            $values['to']             = $this->getConfig()->resources->mail->contactanos->postulante;
            $tipodoc                  = explode('#', $values['tipo_documento']);
            $values['tipo_documento'] = $tipodoc[0];
            $this->_helper->Mail->contactoPortal($values);
            $this->getMessenger()->success('Mensaje enviado correctamente');
            $this->_redirect(
                Zend_Controller_Front::getInstance()
                    ->getRequest()->getRequestUri()
            );
        } catch (Exception $e) {
//            $this->getMessenger()->error($e->getMessage());
//            echo $e->getMessage();
            echo 'No pudimos enviar, por favor vuelva ha intentarlo';
        }
    }

    public function contactenosAction()
    {
        $this->view->headLink()->appendStylesheet(
            $this->view->S(
                '/css/main.informativas.css')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/contacto.js')
        );
        $this->view->modulo = $this->_request->getModuleName();
        $formContactenos    = new Application_Form_Contacto;

        if ($this->_request->isPost()) {

            $this->view->ispost = true;
            $values             = $this->getRequest()->getPost();
            $formContactenos->setDefaults($values);

            if ($formContactenos->isValid($values)):
                $recaptcha = new Zend_Service_ReCaptcha(
                    $this->getConfig()->recaptcha->publickey,
                    $this->getConfig()->recaptcha->privatekey
                );
                $result    = $recaptcha->verify(
                    $values['recaptcha_challenge_field'],
                    $values['recaptcha_response_field']
                );

                if (!$result->isValid()) {

                    $this->sendMailFromContactenos($values);
                }

            endif;
        }
        $this->view->formContacto = $formContactenos;
    }

    public function cronReferidoAction()
    {
        $cj = new CronJobs_cronjob();
        $cj->notifyReferrals();
    }

    public function sitemapAction()
    {
        $cj = new CronJobs_cronjob();
        $cj->generateSitemap();
        exit;
    }

    public function solrUbigeoAction()
    {
        $cj = new Solr_SolrUbigeo();

        $model  = new Application_Model_Ubigeo();
        $params = $model->getubigeosolr();
        foreach ($params as $key => $value) {
            $cj->addUbigeo($value);
        }

        exit;
    }

    private function sendMailFromCorreoMembresia($data, $email)
    {
        try {

            //XSS
            $filter = new Zend_Filter_StripTags;
            foreach ($data as $key => $value) {
                $data[$key] = $filter->filter($value);
            }

            $data['tipo']     = ucfirst($data['hidMembresia']);
            $data['texto']    = 'la';
            $data['empresa']  = $data['txtCompany'];
            $data['contacto'] = $data['txtContact'];
            $data['telefono'] = $data['txtPhone'];
            $data['consulta'] = $data['txaMessage'];
            $data['correo']   = $data['txtEmail'];
            $data['to']       = $email;

            if ($data['hidMembresia'] == '') {
                $data['texto'] = 'una';
                $data['tipo']  = '';
            }


            $this->_helper->Mail->contactarMembresia($data);
            echo Zend_Json::encode(array('status' => 'ok'));
        } catch (Exception $ex) {
            echo Zend_Json::encode(array('status' => $ex->getMessage()));
        }
    }

    public function envioCorreoMembresiaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $formHash = (object) Zend_Layout::getMvcInstance()->hashForm;
        $dataHash = array('hash' => $this->_getParam('hash', ''));
        $config   = Zend_Registry::get('config');
        $email    = $config->cron->membresia->email->concopia;
        $data     = $this->_getAllParams();

        //Validación token CSRF
        if ($formHash->isValid($dataHash)) {

            $this->sendMailFromCorreoMembresia($data, $email);
        } else {
            exit('Acceso denegado');
        }
    }

    //Muestra vista para poder adquirir el servicio de cv destacado solo para los postulantes.
    public function perfilDestacadoAction()
    {

        $this->_redirect('/');

        $this->view->headTitle()->set('Perfil destacado | EMPLEOS');
        $this->view->menu_sel = self::MENU_PERFIL_DESTACADO;
        //Envía sesion para mostrar login
        $modelTarifa          = new Application_Model_Tarifa();
        $dataTarifaCV         = $modelTarifa->obtenerTarifasCVDestacados();

        $rol = '';
        if ($this->auth) {
            if ($this->auth['usuario']->rol == Application_Model_Usuario::ROL_POSTULANTE)
                    $rol = $this->auth['usuario']->rol;
            else $rol = Application_Model_Usuario::ROL_EMPRESA;
        }

        $this->view->rol    = $rol;
        $this->view->tarifa = $dataTarifaCV;
    }

    //No modificar esta función es para realizar pruebas
    public function testCronAdecsysAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $param = $this->_getParam('t');

        $cj   = new CronJobs_cronjob();
        $home = $this->_getParam('d', 10);
        if ($param == 'aviso') {
            $cj->enviarAvisoAdecsys();
        } else if ($param == 'scot') {
            $cj->enviarAvisoScot();
        } else if ($param == 'perfil') {
            $cj->enviarPerfilAdecsys($home);
        } else if ($param == 'membresia') {
            $cj->enviarMembresiaAdecsys();
        } else if ($param == 'indexacion') {
            $cj->indexacion_solar_aviso();
        } else if ($param == 'prioridad') {
            $cj->actualizarPrioridadAvisosBuscamas();
        } else {
            echo "Param incorrecto.";
        }
    }

    public function filtrarTipoEstudioAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $param = $this->_getAllParams();

        if ($this->_hash->isValid($param['csrfhash'])) {

            $filtro             = new Zend_Filter_StripTags;
            $idTipoNivelEstudio = $filtro->filter($this->_getParam('id_nivel_estudio'));
            $nivelEstudio       = new Application_Model_NivelEstudio();
            $data               = $nivelEstudio->getSubNivelesPadreSelect($idTipoNivelEstudio);

            $cont = 0;
            foreach ($data as $value) {
                $data[$cont] = array($data[$cont]['peso'] => array('id' => $data[$cont]['id'],
                        'nombre' => $data[$cont]['nombre']));
                unset($data[$cont]['id']);
                unset($data[$cont]['nombre']);
                unset($data[$cont]['peso']);
                $cont++;
            }

            $this->_response->appendBody(Zend_Json::encode($data));
        } else {
            echo $this->_messageError;
        }
    }

    public function limpiarCacheAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->_cache->clean(Zend_Cache::CLEANING_MODE_ALL);

        echo "< -- Cache was cleanned succes -- >";
    }

    public function bajaMembresiaAction()
    {
        $cj = new CronJobs_cronjob();
        $cj->revisarMembresia();
        exit;
    }

    public function cronReporteUpcAction()
    {
        $cj = new CronJobs_cronjob();
        $cj->enviarCorreoUPC();
        exit;
    }

    public function avisoGratuitoMembresiaAction()
    {

        $cj = new CronJobs_cronjob();
        $cj->fixAvisosGratuitosMembresia();
        exit;
    }

    public function pubAvisoAdecsysAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $cj = new CronJobs_cronjob();
        $cj->publicarAnunciosAdecsys();
    }

    public function generateSitemapAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $cj = new CronJobs_cronjob();
        $cj->generateSitemap();
    }

    public function doblesderiesgoAction()
    {

    }

    public function cronSitemapAction()
    {
        $cj = new CronJobs_cronjob();
        $cj->generateSitemap();
        exit;
    }

    public function despublicarAction()
    {
        $cj = new CronJobs_cronjob();
        $cj->despublicarAnunciosVencidos();
        exit;
    }

    public function notiSemanalAction()
    {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        try {
            $cj = new CronJobs_cronjob();
            $cj->notificacionSemanal();
        } catch (Exception $ex) {
            echo Zend_Json::encode(array('status' => $ex->getMessage()));
        }
    }

    public function cronDnisRepetidosAction()
    {

        $total = $this->_getParam('total', 'todos');

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $job = new CronJobs_cronjob();
        $job->cronDNIRepetidos($total);
        exit;
    }

    public function cronRucsRepetidosAction()
    {

        $total = $this->_getParam('ruc', FALSE);

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $job = new CronJobs_cronjob();
        $job->cronRUCRepetidos($total);
        exit;
    }

    public function cronJobsAction()
    {
        //echo system('df -h');exit;
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $job = new CronJobs_cronjob();
        $job->exeXmljobs();
        exit;
    }

    public function cronFixAdecsysAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $wa  = $this->_getParam('wa', 1);
        $wt  = $this->_getParam('wt', 1);
        $job = new CronJobs_cronjob();
        $job->cronFixAdecsys($wa, $wt);
        exit;
    }

    public function updateGenderAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $limit = $this->_getParam('n');

        $postulante = new Application_Model_Postulante();
        $post       = $postulante->getPostulantesSinSexo($limit);

        $idsDetected   = '';
        $idsUndetected = '';
        foreach ($post as $value) {
            $rawData    = file_get_contents('http://api.genderize.io?name='.$value['nombre']);
            $parsedData = ((array) json_decode($rawData));

            if (isset($parsedData['probability']) && $parsedData['probability'] >= 0.68) {
                $sexo                     = ($parsedData['gender'] == 'female') ? 'F'
                        : 'M';
                //update
                $where                    = $postulante->getAdapter()->quoteInto('id = ?',
                    $value['id']);
                $valuesPostulante['sexo'] = $sexo;
                $postulante->update($valuesPostulante, $where);
                $idsDetected .= $value['id'].',';
            } else {
                $idsUndetected .= $value['id'].',';
            }
        }
        $detectados   = ($idsDetected != '') ? $idsDetected : "Ninguno";
        $noDetectados = ($idsUndetected != '') ? $idsUndetected : "Ninguno";

        echo "Ids actualizados: ".$detectados;
        echo "</br>Ids NO actualizados: ".$noDetectados;
    }

    public function mapaSitioAction()
    {
        $this->view->headLink()->appendStylesheet(
            $this->view->S(
                '/css/main.informativas.css')
        );
        $columnas   = 4;
        $util       = new App_Util();
        $solrAviso  = new Solr_SolrAviso();
        $infoAvisos = $solrAviso->getInfoAvisos();

        $provincias = $infoAvisos['ubicacion'];
        $areas      = $infoAvisos['areas'];
        $carreras   = $infoAvisos['carreras'];
        $puestos    = $infoAvisos['puestos'];
        $empresas   = $infoAvisos['empresas'];

        $arrPuestos = array();
        foreach ($puestos as $k => $v) {
            $arrPuestos[$k] = $util->divideArray($v, $columnas);
        }

        $arrEmpresas = array();
        foreach ($empresas as $k => $v) {
            $arrEmpresas[$k] = $util->divideArray($v, $columnas);
        }

        $this->view->provincias = $util->divideArray($provincias, $columnas);
        $this->view->areas      = $util->divideArray($areas, $columnas);
        $this->view->carreras   = $util->divideArray($carreras, $columnas);
        $this->view->puestos    = $arrPuestos;
        $this->view->empresas   = $arrEmpresas;
    }

    public function filtrarAvisosAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        try {
            //$name=$this->_getParam('filtro', "");
            $aviso            = new Solr_SolrAviso();
            $name             = $this->_getParam('value');
            // var_dump($name);exit;
            $movil            = $this->_getParam('mobile');
            $token            = $this->_getParam('csrfhash');
            //     if ($this->_hash->isValid($token) ) {
            $filter           = new Zend_Filter();
            $filter->addFilter(new Zend_Filter_StripTags());
            $name             = $filter->filter($name);
            $data['status']   = '1';
            $data['messages'] = $this->_messageSuccess;
            $dataAviso        = $aviso->getAvisoByPuesto($name, $movil);
            $data['items']    = $dataAviso;
            //   } else {
            // $data=array('status'=>0,'messages'=>$this->_messageError);
            // }
        } catch (Exception $exc) {
            $data = array('status' => 0, 'messages' => $this->_messageError);
        } catch (Exception $exc) {
            $data = array('status' => 0, 'messages' => $this->_messageError);
        }
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function dynamoAction()
    {

        try {
            $client = \Aws\DynamoDb\DynamoDbClient::factory(array(
                    'key' => 'AKIAJ3CYPYDFZYCJWKLA',
                    'secret' => '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',
                    'region' => 'us-east-1',
            ));


            $tabla    = array(
                'TableName' => 'apt_dev_sesiones_demo',
                'AttributeDefinitions' => array(
                    array(
                        'AttributeName' => 'id',
                        'AttributeType' => 'S'
                    ),
                    array(
                        'AttributeName' => 'expires',
                        'AttributeType' => 'N'
                    ),
                ),
                'KeySchema' => array(
                    array(
                        'AttributeName' => 'id',
                        'KeyType' => 'HASH'
                    ),
                ),
                'GlobalSecondaryIndexes' => array(
                    array(
                        'IndexName' => 'expires-index',
                        'Projection' => array(
                            'ProjectionType' => 'ALL'
                        ),
                        'ProvisionedThroughput' => array(
                            'NumberOfDecreasesToday' => 0,
                            'WriteCapacityUnits' => 1,
                            'ReadCapacityUnits' => 1
                        ),
                        'KeySchema' => array(
                            array(
                                'KeyType' => 'HASH',
                                'AttributeName' => 'expires'
                            )
                        )
                    ),
                ),
                'ProvisionedThroughput' => array(
                    'ReadCapacityUnits' => 5,
                    'WriteCapacityUnits' => 6
                )
            );
            $response = $client->createTable($tabla);
            var_dump($response);
            exit;
        } catch (\Exception $ex) {
            die($ex->getMessage());
        }
    }

    public function correoAction()
    {
        $data['titulo']       = 'clicks';
        $data['mensaje']      = 'dasfdasfasdfdsfdsfdsf';
        $data['nom_contacto'] = 'ronald'; // $data['txtContact'];
        $data['recupera']     = '1';
        $data['email']        = 'ronaldfox2015@gmail.com';
        $data['token']        = 'dfadsrfr5eeereswo';
        $data['to']           = 'ronaldfox2015@gmail.com';
        $this->_helper->Mail->contactarSeleccion($data);
        // El mensaje
        $mensaje              = "Línea 1\r\nLínea 2\r\nLínea 3";

// Si cualquier línea es más larga de 70 caracteres, se debería usar wordwrap()
        $mensaje = wordwrap($mensaje, 70, "\r\n");

// Enviarlo
        $email = 0; ///mail('ronald.cutisaca@clicksandbricks.pe', 'Mi título', $mensaje);

        var_dump($data, $email);
        exit;
    }

    public function createElementAction()
    {
        $img     = APPLICATION_PATH."/../public/elements/empleo/img/";
        $logo    = APPLICATION_PATH."/../public/elements/empleo/logos/";
        $cv      = APPLICATION_PATH."/../public/elements/empleo/cvs/";
        $notas   = APPLICATION_PATH."/../public/elements/empleo/notas/";
        $impreso = APPLICATION_PATH."/../public/elements/empleo/impreso/";
        if (!file_exists($img)) {
            mkdir($img, 0777, true);
        }
        if (!file_exists($logo)) {
            mkdir($logo, 0777, true);
        }
        if (!file_exists($cv)) {
            mkdir($cv, 0777, true);
        }
        if (!file_exists($impreso)) {
            mkdir($impreso, 0777, true);
        }
        exit;
    }

    public function configsAction()
    {

        $ruta = APPLICATION_PATH.'/configs';
        $this->listar_archivos($ruta);
        exit;
    }

    public function listar_archivos($carpeta)
    {
        if (is_dir($carpeta)) {
            if ($dir = opendir($carpeta)) {
                while (($archivo = readdir($dir)) !== false) {
                    if ($archivo != '.' && $archivo != '..' && $archivo != '.htaccess') {
                        // var_dump($carpeta.$archivo);Exit;
                        $array_ini = parse_ini_file($carpeta.'/'.$archivo);
                        print_r('---------- '.$archivo.' -------------');
                        print_r($array_ini);
                        // echo '<li><a target="_blank" href="'.$carpeta.'/'.$archivo.'">'.$archivo.'</a></li>';
                    }
                }
                closedir($dir);
            }
        }
    }

    public function solrPostulanteAction()
    {
        $param           = $this->_getAllParams();
        $modelPostulante = new Solr_SolrPostulante();
        var_dump($modelPostulante->add($param['id']));
        exit;
    }

    public function wsNicaraguaAction()
    {
        $param = $this->_getAllParams();

        $this->_helper->WebServiceNicaragua->calular($param);
        var_dump($param);
        exit;
    }

    public function createPrivateAction()
    {
        $notas = APPLICATION_PATH."/configs/private-dev.ini.bkp";
        var_dump(file_exists($notas));
        exit;

        if (!file_exists($notas)) {
            $ini = APPLICATION_PATH."/configs/private-dev.ini.bkp";
            rename("/tmp/archivo_tmp.txt",
                "/home/user/login/docs/mi_archivo.txt");
        }
    }

    public function getpostulanteAction()
    {
        $param = $this->_getAllParams();
        $id    = $param['id'];

        $solar = new Solr_SolrPostulante();
        $solar->add($id);
        exit;
    }

    public function compraPfAction()
    {
        $param = $this->_getAllParams();
        $job   = new CronJobs_cronjob();
        $job->pagoPf();
        exit;
    }

    public function reprocesoAction()
    {
        $param = $this->_getAllParams();
        $job   = new CronJobs_cronjob();
        $job->Reproceso();
        exit;
    }
}
