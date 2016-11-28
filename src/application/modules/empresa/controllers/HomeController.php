<?php

class Empresa_HomeController extends App_Controller_Action_Empresa
{

    private $_idMembresia;

    public function init()
    {
        parent::init();

        if(isset($this->auth['empresa'])) {
            $this->_idMembresia = $this->auth['empresa']['em_id'];
        } else {
            $this->_idMembresia = null;
        }
    }

    public function indexAction()
    {
        $this->view->headMetas()->setTitle("Empresas - Bolsa de Trabajo y Ofertas de Empleo en Perú - AquiEmpleos | aquiempleos.com");
        $this->view->headMetas()->setDescription("Publica tus ofertas de trabajo con las mejores tarifas y facilidades. Una solución sencilla para publicar tus avisos.");
        $this->view->headMetas()->setKeywords("Publicar un aviso, publicar en La Prensa, publicar avisos, publicar empleos");

        $empresa = new Application_Model_Empresa;
        $this->view->logos = $empresa->getEmpresasPortadas();

        $id = null;

        $this->view->urlScript = $this->getConfig()->urlsExternas->empresa->bannerPortada->urlgetByIdCompra;
       // var_dump($this->isAuth );exit;
        $this->view->isAuth = $this->isAuth;
        $modelProducto = new Application_Model_Producto();

        $postulante2 = new Application_Model_Postulante();
        $totalPostulantes = $postulante2->getCantPostulantes();

        $postulante  = new Solr_SolrPostulante();
        //$postulantes = $postulante->getPostulantes();
        $postMensuales = $postulante->getPostulantesMensuales(true);
        $avisoCollection = new Mongo_Aviso();
        $visitas         = $avisoCollection->getVisitasTodales();
        //Cantidades
        $this->view->totalPostulantes = $totalPostulantes;
        $this->view->postmensuales = $postMensuales;
        $this->view->visitas = $visitas;

        //Datos para permisos de acceso a opciones de empresa
        //$this->view->attr = $this->getDataProcesos();
       // $this->view->memb = $this->getDataPlanes();
    }

    public function queEsEmpleobuscoAction()
    {
        //Menu
        $this->view->menu_sel = self::MENU_QUE_ES_APTITUS;
        $this->view->isAuth = $this->isAuth;
    }

    public function loginAction()
    {

    }

    public function logoutAction()
    {

    }

    public function porqueUsarAptitusAction()
    {
        $this->view->headTitle()->set('Beneficios de anunciar en Empleos para empresas | Empleos');
        $this->view->headLink()->appendStylesheet(
                $this->view->S('/css/main.informativas.css')
        );

        //Menu
        $this->view->menu_sel = self::MENU_QUE_ES_APTITUS;
        $this->view->isAuth = $this->isAuth;
    }

    public function productosAction()
    {
        $config = Zend_Registry::get('config');
        $modelProducto = new Application_Model_Producto();

        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/base64.js')
        );

        //Menu
        $this->view->menu_sel = self::MENU_PRODUCTOS;
        $this->view->isAuth = $this->isAuth;

        $id = null;
        $formRegistroRapido = new Application_Form_RegistroRapido(null);
        $formRegistroRapido->validadorEmail($id);
        $formRegistroRapido->validadorRuc($id);
        $formRegistroRapido->validadorRazonSocial($id);

        if($this->getRequest()->isPost()) {
            $dataPost = $this->_getAllParams();
            $this->_redirect(
                    '/empresa/publica-aviso/paso2/tarifa/' .
                    $dataPost['id_tarifa']
            );
        }

        $mvc = Zend_Layout::getMvcInstance();
        $mvc->loginForm->return->setValue('/empresa/publica-aviso/paso2');
        $this->view->slide = $this->_getParam('slide', 1);
        $this->view->headScript()->appendFile(
                $this->view->S(
                        '/js/empresa/empresa.aviso.index.js')
        );

        Zend_Layout::getMvcInstance()->assign(
                'registrorapido', $formRegistroRapido
        );

        if(is_null($this->_idMembresia)) {
            $preferencial = $modelProducto->getInformacionAvisoPreferencialSinMembresia(
                    Application_Model_Tarifa::PREFERENCIAL_ID);
        } else {
            $preferencial = $modelProducto->getInformacionAvisoPreferencial(
                    Application_Model_Tarifa::PREFERENCIAL_ID);
        }

//        $productos = array(
//            'economicos' => $modelProducto->getInformacionAvisoClasificado(
//                    Application_Model_Tarifa::ECONOMICOS_ID
//            ),
//            'preferencial' => $preferencial,
//            'extracargo' => $modelProducto->listarExtraCargos(
//                    Application_Model_Tarifa::DESTACADO_ID),
//            'destacado' => $modelProducto->getInformacionAvisoWebDestacado(
//                    Application_Model_Tarifa::DESTACADO_ID
//            )
//        );

        $this->view->headTitle()->set('Productos de  Empresa | Empleos');
        $this->view->assign('productos', $productos);
        $this->view->moneda = $config->app->moneda;
    }

    public function membresiaAnualAction()
    {
        $config = Zend_Registry::get('config');
        //Menu
        $this->view->menu_sel = self::MENU_PRODUCTOS;
        $this->view->isAuth = $this->isAuth;

        $rol = '';
        if($this->auth) {
            if($this->auth['usuario']->rol == Application_Model_Usuario::ROL_EMPRESA_ADMIN ||
                    $this->auth['usuario']->rol == Application_Model_Usuario::ROL_EMPRESA_USUARIO) {
                $rol = $this->auth['usuario']->rol;
            } else {
                $rol = Application_Model_Usuario::ROL_POSTULANTE;
            }
        }

        $modelMembresia = new Application_Model_Membresia();
        $memDigital = $modelMembresia->getMembresiaDetalleById(Application_Model_Membresia::DIGITAL, false, true);
        $memSelect = $modelMembresia->getMembresiaDetalleById(Application_Model_Membresia::SELECTO, false, true);
        $memPremium = $modelMembresia->getMembresiaDetalleById(Application_Model_Membresia::PREMIUM, false, true);
        $memMensual = $modelMembresia->getMembresiaDetalleById(Application_Model_Membresia::MENSUAL, false, true);

        if($memMensual) {
            $membresiasActivas['mensual'] = $memMensual;
        }

        if($memDigital) {
            $membresiasActivas['digital'] = $memDigital;
        }

        if($memSelect) {
            $membresiasActivas['select'] = $memSelect;
        }

        if($memPremium) {
            $membresiasActivas['premium'] = $memPremium;
        }


        $this->view->headTitle()->set('Membresía anual para empresas | Empleos');
        $this->view->membresias = $membresiasActivas;
        $this->view->rol = $rol;
        $this->view->moneda = $config->app->moneda;
        $this->view->igv = $config->app->igv;
    }

    public function nuevoEnAquiEmpleosAction()
    {
        if($this->isAuth) {
            $this->_redirect('/empresa/');
        }

        $this->view->headTitle()->set('Regístrese en  Empresa | Empleos');
        $this->view->headLink()->appendStylesheet(
                $this->view->S(
                        '/css/main.informativas.css')
        );
        //Menu
        $this->view->menu_sel = self::MENU_NUEVO_EN_APTITUS;
        $this->view->isAuth = $this->isAuth;
    }

    public function queEsAquiEmpleosEmpresaAction()
    {
        $this->view->headTitle()->set('Información de  Empresa | Empleos');
        $this->view->headLink()->appendStylesheet(
                $this->view->S(
                        '/css/main.informativas.css')
        );
        //Menu
        $this->view->menu_sel = self::MENU_QUE_ES_APTITUS_EMPRESA;
        $this->view->isAuth = $this->isAuth;
    }

    public function terminosDeUsoAction()
    {
        $this->view->headLink()->appendStylesheet(
                $this->view->S(
                        '/css/main.informativas.css')
        );
    }

    public function politicaPrivacidadAction()
    {
        $config = Zend_Registry::get('config');
        $this->view->headLink()->appendStylesheet(
                $this->view->S(
                        '/css/main.informativas.css')
        );
        $this->view->dni = $config->app->dni;
    }

    public function contactenosAction()
    {
        $this->view->headLink()->appendStylesheet(
                $this->view->S('/css/main.informativas.css')
        );
        $this->view->headScript()->appendFile(
                $this->view->S('/js/contacto.js')
        );

        $this->view->modulo = $this->_request->getModuleName();
        $formContactenos = new Application_Form_Contacto;
        if($this->_request->isPost()) {
            $this->view->ispost = $val = true;
            $values = $this->getRequest()->getPost();
            $formContactenos->setDefaults($values);

            if($formContactenos->isValid($values)):
                $recaptcha = new Zend_Service_ReCaptcha(
                        $this->getConfig()->recaptcha->publickey, $this->getConfig()->recaptcha->privatekey
                );
                $result = $recaptcha->verify(
                        $values['recaptcha_challenge_field'], $values['recaptcha_response_field']
                );

                if(!$result->isValid()) {
                    try {
                        //enviar Mail
                        $this->view->ispost = false;
                        $values['to'] = $this->getConfig()->resources->mail->contactanos->empresa;
                        $tipodoc = explode('#', $values['tipo_documento']);
                        $values['tipo_documento'] = $tipodoc[0];
                        $this->_helper->Mail->contactoPortal($values);
                        $this->getMessenger()->success('Mensaje enviado correctamente.');
                        $this->_redirect(
                                Zend_Controller_Front::getInstance()
                                        ->getRequest()->getRequestUri()
                        );
                    } catch(Exception $e) {
                        $this->getMessenger()->error($e->getMessage());
                        echo $e->getMessage();
                    }
                }
            endif;
        }
        $this->view->formContacto = $formContactenos;
    }

    public function seleccionAction()
    {
        $this->view->headTitle()->set('Selección servicio para empresas | Empleos');

        //Menu
        $this->view->menu_sel = self::MENU_SELECCION;
    }

    //Envía correo a Comercial sobre APTiTUS Selección
    public function sendSeleccionAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $formHash = (object) Zend_Layout::getMvcInstance()->hashForm;
        $dataHash = array('hash' => $this->_getParam('hash', ''));
        $config = Zend_Registry::get('config');
        $correoEnvioSeleccion = $config->correoSeleccion->seleccion->email;
        $data = $this->_getAllParams();

        //Validación token CSRF
        if($formHash->isValid($dataHash)) {
            try {
                //XSS
                $filter = new Zend_Filter_StripTags;
                foreach ($data as $key => $value) {
                    $data[$key] = $filter->filter($value);
                }
                $data['empresa'] = $data['txtCompany'];
                $data['tele_empresa'] = $data['txtPhoneCompany'];
                $data['nom_contacto'] = $data['txtContact'];
                $data['tele_contacto'] = $data['txtPhone'];
                $data['email'] = $data['txtEmail'];
                $data['consulta'] = $data['txaMessage'];

                $emailing = explode(',', $correoEnvioSeleccion);
                foreach ($emailing as $email) {
                    if(!empty($email)) {
                        $data['to'] = $email;
                        $this->_helper->Mail->contactarSeleccion($data);
                    }
                }
                echo Zend_Json::encode(array('status' => 'ok'));
            } catch(Exception $ex) {
                echo Zend_Json::encode(array('status' => $ex->getMessage()));
            }
        } else {
            echo Zend_Json::encode(array('status' => 'error'));
        }
    }

    public function testSolrAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $modsolr = new Solr_SolrAviso();

        var_dump($modsolr->ActualizaFechaProcesoSolr(1401979));
        exit;
    }

    /**
     * Retorna los atributos necesarios para configurar enlace de Gestión de
     * procesos en home empresa.
     *
     * @return array
     */
    private function getDataProcesos()
    {
        $opcProcesos = array();
        $opcProcesos['disableClass'] = "";
        $opcProcesos['tooltipText'] = "";
        $opcProcesos['url'] = "javascript:;";
        $opcProcesos['traking'] = "";
        $opcProcesos['loginClass'] = "";
        $opcProcesos['urlImpreso'] = "javascript:;";

        if(!isset($this->auth)) {
            $opcProcesos['traking'] = "ga('send','event','logueo','menu_superior','loguearse');";
            $opcProcesos['loginClass'] = "login_init";
            return $opcProcesos;
        }
        if($this->auth['usuario']->rol == Application_Model_Usuario::ROL_POSTULANTE) {
            $opcProcesos['disableClass'] = "is_disabled";
            $opcProcesos['tooltipText'] = "Sólo para empresas";
        } elseif(in_array($this->auth['usuario']->rol, array(Application_Model_Usuario::ROL_EMPRESA,
                    Application_Model_Usuario::ROL_EMPRESA_ADMIN,
                    Application_Model_Usuario::ROL_EMPRESA_USUARIO))) {
            $opcProcesos['url'] = "empresa/mis-procesos";
            $opcProcesos['urlImpreso'] = "/empresa/publica-aviso";
        } else {
            $opcProcesos['traking'] = "ga('send','event','logueo','menu_superior','loguearse');";
            $opcProcesos['loginClass'] = "login_init";
        }
        return $opcProcesos;
    }

    /**
     * Retorna los valores para describir los planes de aviso y membresías en la
     * sección Conoce nuestros planes
     *
     * @return array
     */
    private function getDataPlanes()
    {
        $moneda = $this->config->app->moneda;
        $dataMemb = array();

        $dataMemb[0]['title'] = "Publicar Aviso Único";
        $dataMemb[0]['before_price'] = "$moneda 60*";
        $dataMemb[0]['now_price'] = "$moneda 18*";
        $dataMemb[0]['number_list'] = 13;
        $dataMemb[0]['item_list'] = array("1 Aviso", "Administración y filtros de CVs", "Cada aviso dura 30 días");
        $dataMemb[0]['href'] = "javascript:;";
        $dataMemb[0]['loginClass'] = "";
        $dataMemb[0]['label'] = "COMPRAR";
        $dataMemb[0]['tooltipText'] = "";

        $dataMemb[1]['title'] = "Membresía Mensual";
        $dataMemb[1]['now_price'] = "$moneda 350*";
        $dataMemb[1]['number_list'] = 13;
        $dataMemb[1]['item_list'] = array("Avisos web ilimitados", "Administración y filtros de CVs", "Cada aviso dura 30 días");
        $dataMemb[1]['href'] = "javascript:;";
        $dataMemb[1]['loginClass'] = "";
        $dataMemb[1]['label'] = "COMPRAR";
        $dataMemb[1]['tooltipText'] = "";

        $dataMemb[2]['title'] = "Membresía </br>Trimestral";
        $dataMemb[2]['now_price'] = "$moneda 944*";
        $dataMemb[2]['number_list'] = 13;
        $dataMemb[2]['item_list'] = array("Avisos web ilimitados", "Administración y filtros de CVs", "Cada aviso dura 30 días", "Destaque de 7 días", "Aplicación 'Trabaja con nosotros'", "Acceso a Base Aptitus", "Exportación de CVs a Excel");
        $dataMemb[2]['href'] = "javascript:;";
        $dataMemb[2]['loginClass'] = "";
        $dataMemb[2]['label'] = "COMPRAR";
        $dataMemb[2]['tooltipText'] = "";

        $dataMemb[3]['title'] = "Membresías Anuales";
        $dataMemb[3]['now_price'] = "Consultar";
        $dataMemb[3]['number_list'] = 12;
        $dataMemb[3]['item_list'] = array("Avisos web ilimitados", "Administración y filtros de CVs", "Cada aviso dura 30 días", "Destaque de 14 días", "Aplicación 'Trabaja con nosotros'", "Acceso a Base Aptitus", "Exportación de CVs a Excel", "Destaque en Home", "Publicación en redes sociales", "Mailing de procesos", "Usuarios secundarios independientes", "Descuentos para impresos, 'Marca empleador' y Suscripción a Aptitus x G");
        $dataMemb[3]['href'] = "javascript:;";
        $dataMemb[3]['loginClass'] = "btn_contact";
        $dataMemb[3]['label'] = "CONSULTAR";
        $dataMemb[3]['tooltipText'] = "";
        if(!isset($this->auth)) {
             for ($j = 0; $j < 3; $j++) {
                $dataMemb[$j]['href'] = "javascript:;";
                $dataMemb[$j]['loginClass'] = "login_init";
                $dataMemb[$j]['loginEvent'] = "ga('send','event','logueo','menu_superior','loguearse');";
            }
            return $dataMemb;
        }
        if($this->auth['usuario']->rol == Application_Model_Usuario::ROL_POSTULANTE) {
            for ($i = 0; $i < 4; $i++) {
                $dataMemb[$i]['tooltipText'] = "Sólo para empresas";
                $dataMemb[$i]['loginEvent'] = "";
                $dataMemb[$i]['loginClass'] = "";
            }
        } elseif(in_array($this->auth['usuario']->rol, array(Application_Model_Usuario::ROL_EMPRESA, Application_Model_Usuario::ROL_EMPRESA_ADMIN, Application_Model_Usuario::ROL_EMPRESA_USUARIO))) {
            $dataMemb[0]['href'] = "/empresa/publica-aviso-destacado/paso2/";
            $dataMemb[0]['loginEvent'] = "ga('send', 'event', 'P1_Destacado', '" . $this->auth['empresa']['id'] . "' , 'P1_Destacado');";
            $dataMemb[0]['itemprop'] = "url";
            $dataMemb[1]['href'] = "/empresa/comprar-membresia-anual/paso1/membresia/11";
            $dataMemb[1]['loginEvent'] = "ga('send', 'event', 'P1_Gratuito', '' , 'P1_Gratuito');";
            $dataMemb[1]['itemprop'] = "url";
            $dataMemb[2]['href'] = "/empresa/comprar-membresia-anual/paso1/membresia/9";
            $dataMemb[2]['loginEvent'] = "ga('send', 'event', 'P1_Gratuito', '' , 'P1_Gratuito');";
            $dataMemb[2]['itemprop'] = "url";
        } else {
            for ($j = 0; $j < 3; $j++) {
                $dataMemb[$j]['href'] = "javascript:;";
                $dataMemb[$j]['loginClass'] = "login_init";
                $dataMemb[$j]['loginEvent'] = "ga('send','event','logueo','menu_superior','loguearse');";
            }
        }

        return $dataMemb;
    }

}
