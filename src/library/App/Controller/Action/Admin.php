<?php

class App_Controller_Action_Admin extends App_Controller_Action
{
    //Admin
    const MENU_POST_SIDE_POSTULANTES = 'postulantes';
    const MENU_POST_SIDE_AVISOS = 'avisos';
    const MENU_POST_SIDE_LISTAVISOS = 'avisos-callcenter';

    const MENU_POST_SIDE_CAMBIOCLAVE = 'cambio-clave';
    const MENU_POST_SIDE_EMPRESAS= 'empresas';
    const MENU_POST_SIDE_AVISOSPREFERENCIALES= 'avisos-preferenciales';
    const MENU_POST_SIDE_CALLCENTER= 'callcenter';
    const MENU_POST_SIDE_USUARIOS = 'usuarios-admin';
    const MENU_POST_MIS_DATOS = 'mis_datos';
    const MENU_POST_SIDE_TESTIMONIOS = 'testimonios';
    const MENU_POST_SIDE_EMPRESASPORTADA = 'empresa-testimonio';

    //Postulante
    const MENU_POST_SIDE_EXPERIENCIA = 'experiencia';
    const MENU_POST_SIDE_ESTUDIOS = 'estudios';
    const MENU_POST_SIDE_OTROSESTUDIOS = 'otrosestudios';
    const MENU_POST_SIDE_IDIOMAS = 'idiomas';
    const MENU_POST_SIDE_PROGRAMAS = 'programas';
    const MENU_POST_SIDE_REFERENCIAS = 'referencias';
    const MENU_POST_SIDE_PERFILPUBLICO = 'perfilpublico';
    const MENU_POST_SIDE_DATOSPERSONALES = 'datospersonales';
    const MENU_POST_SIDE_PERFILDESTACADO = 'perfildestacado';
    const MENU_POST_SIDE_CAMBIOCLAVEPOSTULANTE = 'cambioclave';
    const MENU_POST_SIDE_REDES_SOCIALES= 'redessociales';
    const MENU_POST_SIDE_PRIVACIDAD= 'privacidad';
    const MENU_POST_SIDE_ALERTAS= 'mis_alertas';

    //Empresa
    const MENU_POST_SIDE_DATOSEMPRESA = 'datosempresa';
    const MENU_POST_SIDE_ADMINISTRADORES= 'administradores';
    const MENU_POST_SIDE_MEMBRESIA= 'membresia';

    //Admin
    const MENU_POST_SIDE_API_AGREGARUSUARIO = 'agregarusuario';
    const MENU_POST_SIDE_API_LISTARUSUARIO = 'listarusuario';

    protected $_adminLog = null;

    public function getAdminLog()
    {
        $logger = $this->getLogger();
        if (Zend_Auth::getInstance()->hasIdentity()
                && (isset($this->auth)) && array_key_exists('usuario', $this->auth)) {
            $logger->setEventItem('idusuario', $this->auth["usuario"]->id);
            $logger->setEventItem('email', $this->auth["usuario"]->email);
            $logger->setEventItem('rol', $this->auth["usuario"]->rol);
        }
        $logger->setEventItem('userip', $_SERVER['REMOTE_ADDR']);
        $logger->setEventItem('userhost', gethostbyaddr($_SERVER['REMOTE_ADDR']));
    }

    public function init()
    {
        parent::init();
        $config = $this->getConfig();

        $this->_helper->layout->setLayout('main_admin');

        $this->view->headTitle()->set(
            'Administrador - aquiempleos.com - '.$config->app->title
        );
        $this->view->headLink()->appendStylesheet(
                $this->view->S(
                '/css/main.administrador.css', 'all')
        );

        //Login
        $this->view->loginForm = Application_Form_Login::factory(Application_Form_Login::ROL_ADMIN);
        $this->view->recuperarClaveForm =
            Application_Form_RecuperarClave::factory(Application_Form_Login::ROL_ADMIN);

        $this->view->flashMessages=$this->_flashMessenger;

        //Logger DB
        $this->getAdminLog();
    }
}
