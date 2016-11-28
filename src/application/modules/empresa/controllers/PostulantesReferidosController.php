<?php

class Empresa_PostulantesReferidosController extends App_Controller_Action_Empresa {
    /**
     * @todo Se encuentra registrado en la base de datos
     */

    const TIPO_REFERENCIADO = 1;

    /**
     * @todo No esta registrado en la base de datos
     */
    const TIPO_REFERIDO = 2;

    /**
     * @todo Se encuentra registrado pero esta bloqueado
     */
    const TIPO_BLOQUEADO = 3;

    /**
     * @todo Se encuentra registrado pero ya postulo
     */
    const TIPO_EXISTE_POSTULACION = 4;

    /**
     * @todo Se encuentra registrado pero ya postulo
     */
    const TIPO_POSTULACION_REFERENCIADA = 5;

    /**
     * @todo Se genero la postulacion mediante una invitacion
     */
    const TIPO_INVITACION = 6;
    const MSJ_POSTULANTE_EXISTE = 'El postulante esta registrado';
    const MSJ_POSTULANTE_NO_EXISTE = 'El postulante no esta registrado';
    const MSJ_NO_EMAIL = 'No es un email';
    const MSJ_EMAIL_NO_AGREGADO = 'Este email no esta agregado';
    const MSJ_ERROR = 'Ocurrio un error';
    const MSJ_FORMULARIO_INVALIDO = 'Formulario invalido';
    const MSJ_POSTULANTE_REFERENCIADO = 'Postulante referenciado';
    const MSJ_REFERIDO_AGREGADO = 'Referido agregado';
    const MSJ_EMAIL_YA_AGREGADO = 'Este email ya se agrego';
    const MSJ_POSTULANTE_NO_REGISTRADO = 'El postulante debe estar registrado';
    const MSJ_REFERIDO_QUITADO = 'Referido quitado';
    const MSJ_REGISTRO_EXITOSO = 'Registro exitoso';
    const MSJ_POSTULACION_EXISTE = 'Ya postulo a esta anuncio';
    const SESSION_REFERIDOS = 'referidos';
    const ITEMS_PER_PAGE = 10;

    /**
     * @var Application_Model_Postulante
     */
    private $_postulanteModelo = null;

    /**
     * @var Zend_Session_Namespace
     */
    private $_session = null;

    /**
     * @var String
     */
    private $_mensaje = null;

    /**
     * @var App_Util_Filter
     */
    private $_filtro = null;

    public function init()
    {
        parent::init();
        $this->_postulanteModelo = new Application_Model_Postulante;

        $this->_usuario = new Application_Model_Usuario();
        if (isset($this->auth['usuario']->id) && $this->_usuario->hasvailBlokeo($this->auth['usuario']->id) ) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::forgetMe();
            $this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
            $this->_redirect('/empresa');
        }
        $sessionNombre = self::SESSION_REFERIDOS;

        $this->_session = new Zend_Session_Namespace($sessionNombre);

        $this->_filtro = new App_Util_Filter;
    }

    public function ingresarAction()
    {
        $this->_helper->layout->disableLayout();
    }

    public function listarAction()
    {
        $this->_helper->layout->disableLayout();

        $anuncioId = $this->_getParam('id', null);
        $paginaActual = $this->_getParam('pagina', 1);

        $servicio = new App_Service_UpdateTypeReferred;
        $servicio->updateAll($anuncioId);

        $referenciadoModelo = new Application_Model_Referenciado;

        $postulantesReferidos =
            Zend_Paginator::factory($referenciadoModelo->Listar($anuncioId));

        $postulantesReferidos->setItemCountPerPage(self::ITEMS_PER_PAGE);
        $postulantesReferidos->setCurrentPageNumber($paginaActual);

        $totalReferidos =
            $postulantesReferidos->getTotalItemCount();
        $totalReferidosEnRango = 0;

        if ($totalReferidos > 0) {
            $paginas = $postulantesReferidos->getPagesInRange(
                1, $paginaActual);

            foreach ($paginas as $pagina) {
                $numeroDeItems = $postulantesReferidos
                        ->setCurrentPageNumber($pagina)->getCurrentItemCount();

                $totalReferidosEnRango += $numeroDeItems;
            }
        }

        $this->view->pagina = $paginaActual;
        $this->view->anuncioId = $anuncioId;
        $this->view->postulantesReferidos = $postulantesReferidos;
        $this->view->totalReferidos = $totalReferidos;
        $this->view->totalRefereridosPagina = $totalReferidosEnRango;
    }

    public function verificarAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $postulanteEmail = $this->_getParam('email', null);
        $anuncioId = $this->_getParam('anuncio', null);
        $empresaId = $this->auth['empresa']['id'];

        $session = $this->_getSession($anuncioId);

        $respuesta = array();

        if (!$this->_validarEmail($postulanteEmail, $session)) {
            $respuesta['estado'] = self::PROCESO_INCOMPLETO;
            $respuesta['mensaje'] = $this->_mensaje;
            $this->_response->appendBody(Zend_Json::encode($respuesta));
            return;
        }

        if (App_Service_Validate_Postulant::hasReferred(
                $postulanteEmail, $anuncioId)) {
            $respuesta['estado'] = self::PROCESO_INCOMPLETO;
            $respuesta['mensaje'] =
                App_Service_Validate_Postulant::getStaticMessage();

            $this->_response->appendBody(Zend_Json::encode($respuesta));
            return;
        }

        if (App_Service_Validate_User::isRegister($postulanteEmail)) {
            $postulanteModelo = new Application_Model_Postulante;

            $postulante = $postulanteModelo->buscarPostulantexEmail(
                $postulanteEmail);




            $config = $this->getConfig();
            // foto
            $foto = $postulante['path_foto'];
            if( NULL == $foto) {
              $postulante['path_foto'] = $config->app->mediaUrl.'/images/profile-default.jpg';
            }else{
              $postulante['path_foto'] = $config->app->elementsUrlImg.$foto;
            }


            $postulante['presentacion'] =
                $this->_filtro->escapeAlnum($postulante['presentacion']);

            $postulante['apellidos'] = $postulante['apellido_paterno'] .' '. $postulante['apellido_materno'];
            $respuesta['estado'] = self::PROCESO_EXITOSO;
            $respuesta['postulante'] = $postulante;
            $respuesta['tipo'] =
                $this->_obtenerComportamiento(
                $postulante, $empresaId, $anuncioId);
            $respuesta['mensaje'] = $this->_mensaje;

            if ($respuesta['tipo'] == self::TIPO_POSTULACION_REFERENCIADA)
                    $respuesta['estado'] = self::PROCESO_INCOMPLETO;

            $this->_response->appendBody(Zend_Json::encode($respuesta));
            return;
        }

        $respuesta['estado'] = self::PROCESO_EXITOSO;
        $respuesta['tipo'] = self::TIPO_REFERIDO;
        $respuesta['mensaje'] = self::MSJ_POSTULANTE_NO_EXISTE;
        $respuesta['postulante'] = null;
        $this->_response->appendBody(Zend_Json::encode($respuesta));
    }

    public function referenciarAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $postulanteEmail = $this->_getParam('email', null);
        $anuncioId = $this->_getParam('anuncio-id', null);
        $empresaId = $this->auth['empresa']['id'];

        $respuesta = array();

        $validador = new App_Validate_PostulantReference(
            $empresaId, $anuncioId);

        if (!$validador->isValid($postulanteEmail)) {
            $respuesta['estado'] = self::PROCESO_INCOMPLETO;
            $respuesta['mensaje'] = $validador->getMessage();
            $this->_response->appendBody(Zend_Json::encode($respuesta));
            return;
        }

        $postulante = $validador->getPostulant();
        $postulante['nombre'] = $postulante['nombres'];
        $postulante['apellidos'] = $postulante['apellido_paterno'] .' '. $postulante['apellido_materno'];
        $postulante['curriculo'] = $postulante['path_cv'];
        $postulante['action'] =
            App_Service_RegisterReferrals::ACTION_REFERENCE;

        $postulante['presentacion'] =
            $this->_filtro->escapeAlnum($postulante['presentacion']);

        $this->_addSession(
            $anuncioId, $postulante);

        $respuesta['estado'] = self::PROCESO_EXITOSO;
        $respuesta['postulante'] = $postulante;
        $respuesta['mensaje'] = self::MSJ_POSTULANTE_REFERENCIADO;
        $this->_response->appendBody(Zend_Json::encode($respuesta));
    }

    public function agregarAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $formularioReferido = new Application_Form_AgregarReferido;

        $postulanteEmail = $this->_getParam('email', null);
        $anuncioId = $this->_getParam('anuncio', null);

        $validador = new App_Validate_PostulantReferred($anuncioId);

        if (!$validador->isValid($postulanteEmail)) {
            $respuesta['estado'] = self::PROCESO_INCOMPLETO;
            $respuesta['mensaje'] = $validador->getMessage();
            $this->_response->appendBody(Zend_Json::encode($respuesta));
            return;
        }

        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();

            $referido = array();
            $postulantes = $this->_getSession($anuncioId);

            if ($this->_hasAdded($postulanteEmail, $postulantes))
                    $referido = $postulantes[$postulanteEmail];

            if ($formularioReferido->isValid($post)) {
                $utilfile = $this->_helper->getHelper('UtilFiles');

                $cv = array();
                $cv['postulante']['nombres'] = $post['nombres'];
                $cv['postulante']['apellidos'] = $post['apellidos'];


                $referido['tipo'] = self::TIPO_REFERIDO;
                $referido['email'] = $post['email'];
                $referido['sexo'] = $post['sexo'];
                $referido['nombres'] = $post['nombres'];
                $referido['nombre'] = $post['nombres'];
                $referido['apellidos'] = $post['apellidos'];
                $referido['telefono'] = $post['telefono'];
                $referido['action'] =
                    App_Service_RegisterReferrals::ACTION_REGISTER;

                $nombreArchivo = $utilfile->_renameFile(
                    $formularioReferido, "path_cv", $cv);

                if (!empty($nombreArchivo))
                        $referido['curriculo'] = $nombreArchivo;

                $this->_addSession(
                    $anuncioId, $referido);

                $respuesta['estado'] = self::PROCESO_EXITOSO;
                $respuesta['postulante'] = $referido;
                $respuesta['mensaje'] = self::MSJ_REFERIDO_AGREGADO;
                $this->_response->appendBody(Zend_Json::encode($respuesta));
                return;
            }
        }

        $respuesta['estado'] = self::PROCESO_INCOMPLETO;
        $respuesta['mensaje'] = self::MSJ_FORMULARIO_INVALIDO;
        $respuesta['errores'] = $formularioReferido->getMessages();
        $this->_response->appendBody(Zend_Json::encode($respuesta));
        return;
    }

    public function obtenerFormularioAction()
    {
        $this->_helper->layout->disableLayout();
        $formulario = new Application_Form_AgregarReferido;

        $this->view->formulario = $formulario;
    }

    public function quitarAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $postulanteEmail = $this->_getParam('email');
        $anuncioId = $this->_getParam('anuncio');

        if ($this->_deleteSession($anuncioId, $postulanteEmail)) {
            $respuesta['estado'] = self::PROCESO_EXITOSO;
            $respuesta['mensaje'] = self::MSJ_REFERIDO_QUITADO;
            $this->_response->appendBody(Zend_Json::encode($respuesta));
            return;
        }

        $respuesta['estado'] = self::PROCESO_INCOMPLETO;
        $respuesta['mensaje'] = self::MSJ_EMAIL_NO_AGREGADO;
        $this->_response->appendBody(Zend_Json::encode($respuesta));
        return;
    }

    public function mostrarAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $anuncioId = $this->_getParam('id');

        $respuesta = $this->_getSession($anuncioId);

        $this->_response->appendBody(Zend_Json::encode($respuesta));
    }

    public function registrarAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $anuncioId = $this->_getParam('anuncio');
        $empresaId = $this->auth['empresa']['id'];

        $postulantes = $this->_getSession($anuncioId);

        $referrals = new App_Service_RegisterReferrals(
            $empresaId, $anuncioId, $postulantes);

        $noRegistrados = $referrals->registerAll();

        if (count($noRegistrados) > 0) {
            $respuesta['estado'] = self::PROCESO_INCOMPLETO;
            $respuesta['mensaje'] = $referrals->getErrors();
            $this->_response->appendBody(Zend_Json::encode($respuesta));
            $this->_session->procesos[$anuncioId] = $noRegistrados;
            return;
        }

        $this->_session->unsetAll();

        $respuesta['estado'] = self::PROCESO_EXITOSO;
        $this->_response->appendBody(Zend_Json::encode($respuesta));
    }

    private function _validarEmail($email, $postulantes)
    {

        if (!$this->_isEmail($email)) return FALSE;

        if ($this->_hasAdded($email, $postulantes)) return FALSE;

        return TRUE;
    }

    private function _isEmail($email)
    {
        $validador = new Zend_Validate_EmailAddress;

        if (!$validador->isValid($email)) {
            $this->_mensaje = self::MSJ_NO_EMAIL;
            return FALSE;
        }

        return TRUE;
    }

    private function _hasAdded($email, $postulantes)
    {
        if (array_key_exists($email, $postulantes)) {
            $this->_mensaje = self::MSJ_EMAIL_YA_AGREGADO;
            return TRUE;
        }

        return FALSE;
    }

    private function _getSession($anuncioId)
    {
        if (!isset($this->_session->procesos)) {
            $this->_session->procesos = array();
        }

        if (!isset($this->_session->procesos[$anuncioId])) {
            $this->_session->procesos[$anuncioId] = array();
        }

        return $this->_session->procesos[$anuncioId];
    }

    private function _addSession($anuncioId, $postulante)
    {
        $postulanteEmail = $postulante['email'];

        $session = $this->_getSession($anuncioId);

        $session[$postulanteEmail] = $postulante;

        $this->_session->procesos[$anuncioId] = $session;
    }

    private function _deleteSession($anuncioId, $email)
    {
        $session = $this->_getSession($anuncioId);

        if (array_key_exists($email, $session)) {
            unset($session[$email]);
            $this->_session->procesos[$anuncioId] = $session;
            return TRUE;
        }

        return FALSE;
    }

    private function _obtenerComportamiento(
    $postulanteDatos, $empresaId, $anuncioId)
    {
        $postulante = new App_Service_Validate_Postulant;
        $postulante->setData($postulanteDatos);

        if ($postulante->isBlocked($empresaId)) {
            $this->_mensaje = $postulante->getMessage();
            return self::TIPO_BLOQUEADO;
        }

        $postulacion = new App_Service_Validate_Postulation_Postulant(
            $anuncioId, $postulanteDatos['id']);

        if (!$postulacion->isNull()) {
            if ($postulacion->isReferred()) {
                $this->_mensaje = $postulacion->getMessage();
                return self::TIPO_POSTULACION_REFERENCIADA;
            }

            if ($postulacion->isInvited()) {
                $this->_mensaje = $postulacion->getMessage();
                return self::TIPO_INVITACION;
            }

            $this->_mensaje = self::MSJ_POSTULACION_EXISTE;
            return self::TIPO_EXISTE_POSTULACION;
        }

        $this->_mensaje = self::MSJ_POSTULANTE_EXISTE;
        return self::TIPO_REFERENCIADO;
    }

}