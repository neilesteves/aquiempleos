<?php

class Front_HomeController extends App_Controller_Action_Front
{

    protected $_cache = null;

    public function init()
    {
        parent::init();
    }



//    public function rutaAction()
//    {
//        $directorio = opendir(APPLICATION_PATH . '/../java/geocom/'); //ruta actual
//        while ($archivo = readdir($directorio)) { //obtenemos un archivo y luego otro sucesivamente
//            if (is_dir($archivo)) {//verificamos si es o no un directorio
//                echo "[" . $archivo . "]<br />"; //de ser un directorio lo envolvemos entre corchetes
//            } else {
//                echo $archivo . "<br />";
//            }
//        }exit;
//    }
//    public function descargaAction()
//    {
//        $params = $this->getRequest()->getParams();
//        $enlace = APPLICATION_PATH . '/../java/geocom/' . $params['name'];
//        header("Content-Disposition: attachment; filename=$enlace ");
//        header("Content-Type: application/force-download");
//        header("Content-Length: " . filesize($enlace));
//        readfile($enlace);
//        exit;
//        //INFORMATION_8894492.txt
//    }


    public function indexAction()
    {

    }

    public function queEsGallitoTrabajoAction()
    {
//Menu
        $this->view->menu_sel = self::MENU_QUE_ES_APTITUS;
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/main.informativas.css'));
        $this->view->headMeta()->appendName(
                "Description", "En nuestra página web los postulantes se contactan con las" .
                " empresas más exitosas y reconocidas del mercado en AquiEmpleos "
        );
        $this->view->headTitle()->set(
                '¿Qué es AquiEmpleos?. ' .
                '- AquiEmpleos'
        );
    }

    public function productosAction()
    {
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/main.informativas.css'));
    }

    public function publicaUnAvisoAction()
    {
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/main.informativas.css'));
    }

    public function cartaPresentacionAction()
    {
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/main.informativas.css'));
    }

    public function antecedentePolicialesAction()
    {
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/main.informativas.css'));
    }

    public function entrevistaTrabajoAction()
    {
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/main.informativas.css'));
    }

    public function comoArmoMiCvAction()
    {
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/main.informativas.css'));
    }

    public function videoTutorialAction()
    {
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/main.informativas.css'));
    }

    public function preguntasFrecuentesAction()
    {
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/main.informativas.css'));
    }

    public function escribenosAction()
    {
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/main.informativas.css'));
    }

    public function terminosCondicionesAction()
    {
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/main.informativas.css'));
    }

    public function politicaPublicacionAction()
    {
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/main.informativas.css'));
    }

    public function politicaPrivacidadAction()
    {
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/main.informativas.css'));
    }

    public function loginAction()
    {
// action body
    }

    public function logoutAction()
    {
// action body
    }

    public function porqueUsarGallitoTrabajoAction()
    {
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/main.informativas.css'));
// action body
    }

    public function terminosDeUsoAction()
    {
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/main.informativas.css'));
    }

    public function contactenosAction()
    {
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/main.informativas.css'));
        $this->view->headScript()->appendFile($vers->version('/js/contacto.js'));
        $this->view->modulo = $this->_request->getModuleName();
        $formContactenos = new Application_Form_Contacto;

        if($this->_request->isPost()) {
            $this->view->ispost = true;
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
                        $values['to'] = $this->getConfig()->resources->mail->contactanos->postulante;
                        $tipodoc = explode('#', $values['tipo_documento']);
                        $values['tipo_documento'] = $tipodoc[0];
                        $this->_helper->Mail->contactoPortal($values);
                        $this->getMessenger()->success('Mensaje enviado correctamente');
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

    public function validarImagenAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $ruta = $this->_getParam("ruta");

        $data = array();
        $data["isValid"] = false;
        if($this->view->ValidarTamanioImagen($ruta, 60)) {
            $data["isValid"] = true;
        }
        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function riesgoAction()
    {
        $empresa = new Application_Model_Empresa;
        $this->view->logos = $empresa->getEmpresasPortadas();

        $vers = new App_View_Helper_Version();
        $this->view->headScript()->appendFile($vers->version('/js/swfobject/swfobject.js'));
        $this->view->headScript()->appendFile($vers->version('/js/postulante.riesgo.js'));

        $rutaLogoDefecto = $this->config->defaultLogoEmpresa->fileName;
        $verLogoDefecto = (bool) $this->config->defaultLogoEmpresa->enabled;

        $this->view->logoDefecto = $rutaLogoDefecto;
        $this->view->verLogoDefecto = $verLogoDefecto;

        $this->view->headMeta()->appendName(
                "description", "Encuentra  las mejores Ofertas de Trabajo en el Perú. Sube tu CV "
                . "y postula a los puestos de trabajo de acuerdo a tu Perfil."
        );
        $this->view->headMeta()->appendName(
                "keywords", "Ofertas de trabajo, Bolsas de trabajo, avisos de trabajo, "
                . "empleos peru, buscar empleo, aquiempleos, "
                . "búsqueda de empleo, búsqueda de trabajo."
        );

        $this->view->headScript()->appendFile($vers->version('/js/base64.js'));

        $this->view->headMeta()->appendHttpEquiv(
                "Content-Language", "es"
        );
//Menu
        $this->view->menu_sel = self::MENU_INICIO;

// action body
        $anunciosWeb = new Application_Model_AnuncioWeb();
        $areasAlf = $anunciosWeb->getGroupAreaOrdenAlf();
        $areasNum = $anunciosWeb->getGroupAreaOrdenNum();
        $this->view->groupAreas1 = $areasNum;
        $this->view->groupAreas2 = $areasAlf;
        $nivelAlf = $anunciosWeb->getGroupNivelPuestoOrdenAlf();
        $nivelNum = $anunciosWeb->getGroupNivelPuestoOrdenNum();
        $idsUbigeo = array(
            Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID,
            Application_Model_Ubigeo::LIMA_UBIGEO_ID,
            Application_Model_Ubigeo::PERU_UBIGEO_ID
        );
        $ubicacionesNum = $anunciosWeb->getGroupUbicacion($idsUbigeo, 2);
        $ubicacionesAlf = $anunciosWeb->getGroupUbicacion($idsUbigeo, 1);
//        $ubicaciones = $anunciosWeb->getCantAvisosPorRangoRemuneracion();
        $this->view->groupDistritos1 = $ubicacionesNum;
        $this->view->groupDistritos2 = $ubicacionesAlf;
        $this->view->groupNivelPuesto1 = $nivelNum;
        $this->view->groupNivelPuesto2 = $nivelAlf;
        $this->view->groupUltimosAvisos = $anunciosWeb->getUltimosAvisos();

//Cargar Formulario de busqueda Avanzada
        $form = new Application_Form_BuscarHome();
        $form->setAreas($areasAlf);
        $form->setNivelPuestos($nivelAlf);

        $this->view->form = $form;

//Banner
        $this->view->urlScript = $this->getConfig()
                ->urlsExternas->postulante->bannerPortada->url;

//Blogs de Portada
        $this->view->limite = $this->getConfig()->portadaPostulante
                ->listaArticulosInteres->limite;
        $this->view->isAuth = $this->isAuth;

//$this->generarLogosAleatorios();
// Message Error Login Postulante
        $messageError = new Zend_Session_Namespace('messageError');
        $this->view->messageErrorP = $messageError->string;
        $messageError->setExpirationSeconds(1);
    }

    public function mensajeEnviadoAction()
    {
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/landing.css'));
    }

    public function limpiarCacheAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->_cache->clean(Zend_Cache::CLEANING_MODE_ALL);
        echo "Cache was cleanned succes";
    }

    public function mailAction()
    {
        $this->_helper->layout->disableLayout();
    }

    public function contactoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        Zend_Layout::getMvcInstance()->setLayout('contacto');
        $vers = new App_View_Helper_Version();
        $this->view->headLink()->appendStylesheet($vers->version('/css/contacto.css'));
    }

    public function testAvisoAvitadAction()
    {
        $this->_helper->layout->disableLayout();
//  $this->_helper->viewRenderer->setNoRender();exit;
        $aviso = new App_Controller_Action_Helper_Aviso();
        $params = $this->_getAllParams();
        $ws = new App_Controller_Action_Helper_WebServiceGallito();
        if(isset($params['idcompra'])) {
            try {
                $cip = $ws->consultaCipGallito($params['cip']);
                if($cip == "C") {
                    $aviso->actualizaValoresCompraAviso($params['idcompra']);
                } else {
                    echo "no es valido el cip";
                }
            } catch(Exception $e) {
                echo $e->getMessage();
            }
        }

        exit;
    }

    public function credomaticAction()
    {
        $this->_helper->layout->disableLayout();
    }

    public function testAction()
    {
        $key_id = $this->getConfig()->credomatic->key_id;
        $key = $this->getConfig()->credomatic->key;
        $username = $this->getConfig()->credomatic->username;

        $orderid = 'test';
        $amount = '1.00';
        $time = time();

        $hash = MD5($orderid . "|" . $amount . "|" . $time . "|" . $key);

        $client = new Zend_Http_Client();
        $client->setUri($this->getConfig()->credomatic->url);

        $client->setMethod(Zend_Http_Client::POST);
        $client->setParameterPost(array(
            'username' => $username,
            'type' => 'auth',
            'key_id' => $key_id,
            'hash' => $hash,
            'time' => $time,
            'amount' => $amount,
            'orderid' => $orderid,
            'processor_id' => '',
            'ccnumber' => '4072210290536663',
            'ccexp' => '0318',
        ));

        $response = $client->request();
        echo $response->getBody();
        $response = parse_str($response->getBody(), $output);

        echo "<pre>-------------";
        print_r($output);


        die("123");
        $this->_helper->layout->disableLayout();
    }

}
