<?php
class App_Controller_Action_Postulante extends App_Controller_Action {

	const MENU_INICIO = 'inicio';
	const MENU_AVISOS = 'avisos';
	const MENU_MI_CUENTA = 'mi_cuenta';
        const MENU_PERFIL_DESTACADO = 'perfil_destacado';
	const MENU_QUE_ES_APTITUS = 'que_es_aptitus';
	const MENU_POST_INICIO = 'inicio_postulante';
	const MENU_POST_MIS_DATOS = 'mis_datos';
	const MENU_POST_MIS_POSTULACIONES = 'mis_postulaciones';
	const MENU_POST_MIS_NOTIFICACIONES = 'mis_notificaciones';
	const MENU_POST_MIS_ALERTAS = 'mis_alertas';
	const MENU_POST_SUBE_CV = 'sube_cv';
	const MENU_POST_SIDE_EXPERIENCIA = 'experiencia';
	const MENU_POST_SIDE_ESTUDIOS = 'estudios';
	const MENU_POST_SIDE_CURSOS = 'cursos';
	const MENU_POST_SIDE_IDIOMAS = 'idiomas';
	const MENU_POST_SIDE_OTROSESTUDIOS = 'otrosestudios';
	const MENU_POST_SIDE_PROGRAMAS = 'programas';
	const MENU_POST_SIDE_REFERENCIAS = 'referencias';
	const MENU_POST_SIDE_PERFILPUBLICO = 'perfilpublico';
	const MENU_POST_SIDE_DATOSPERSONALES = 'datospersonales';
        const MENU_POST_SIDE_MISDATOSPERSONALES = 'mi-cuenta/mis-datos-personales';
        const MENU_POST_SIDE_MISPOSTULACIONES = 'postulaciones/index';
        const MENU_POST_SIDE_MISNOTIFICACONES = 'notificaciones/index';
        const MENU_POST_SIDE_MISDATOS_UBICACION = 'mi-cuenta/mi-ubicacion';
	const MENU_POST_SIDE_MISDATOS_EXPERIENCIAS = 'mi-cuenta/mis-experiencias';
	const MENU_POST_SIDE_MISDATOS_ESTUDIOS = 'mi-cuenta/mis-estudios';
	const MENU_POST_SIDE_MISDATOS_OTROESTUDIOS = 'mi-cuenta/mis-otros-estudios';
	const MENU_POST_SIDE_MISDATOS_IDIOMAS = 'mi-cuenta/mis-idiomas';
	const MENU_POST_SIDE_MISDATOS_PROGRAMAS = 'mi-cuenta/mis-programas';
	const MENU_POST_SIDE_MISDATOS_LOGROS = 'mi-cuenta/mis-logros';
        const SolrAvisoCacheId='solrAvisohomepostulante';


     const MENU_POST_SIDE_CAMBIOCLAVE = 'cambioclave';
        const MENU_POST_SIDE_PERFILDESTACADO = 'perfildestacado';
	const MENU_POST_SIDE_REDES_SOCIALES = 'redessociales';
	const MENU_POST_SIDE_PRIVACIDAD = 'privacidad';
        const MENU_POST_SIDE_ELIMINAR_CUENTA = 'eliminar_cuenta';
	const MENU_POST_SIDE_ALERTAS = 'mis_alertas';
	const MENU_POST_MIS_RECOMENDACIONES = 'mis_recomendaciones';
	const MENU_POST_SIDE_PEDIRRECOMENDACIONES = 'pedirrecomendaciones';
	const MENU_POST_SIDE_SOLICITADAS = 'solicitadas';
	const MENU_POST_SIDE_REALIZADAS = 'realizadas';
	const MENU_POST_SIDE_RECIBIDOS = 'recibidos';

        const sugerencias = 'sugeridos';
        const favoritos ='favoritos';
        const eliminados ='eliminados';

	public function init() {
		parent::init ();
		$config = $this->getConfig ();

		$this->view->headTitle ()->set ($config->app->title );

		Zend_Layout::getMvcInstance ()->assign ( 'AppFacebook', $config->apis->facebook );
		Zend_Layout::getMvcInstance ()->assign ( 'urlAuthFacebook', $config->app->siteUrl . '/registro/facebook' );
		Zend_Layout::getMvcInstance ()->assign ( 'urlAuthAppFacebook', $config->app->siteUrl . '/auth/validacion-facebook' );
		//Zend_Layout::getMvcInstance ()->assign ( 'urlAuthAppGoogle', sprintf ( $config->apis->google->openidUrl, $config->app->siteUrl . $config->apis->google->returnUrlAuth, $config->app->siteUrl ) );

		Zend_Layout::getMvcInstance ()->assign ( 'loginForm', Application_Form_Login::factory ( Application_Form_Login::ROL_POSTULANTE ) );
		Zend_Layout::getMvcInstance ()->assign ( 'registroSelectorForm', Application_Form_RegistroSelector::factory ( Application_Form_RegistroSelector::ROL_POSTULANTE ) );
		Zend_Layout::getMvcInstance ()->assign ( 'ingresaSelectorForm', Application_Form_IngresaSelector::factory ( Application_Form_IngresaSelector::ROL_POSTULANTE ) );
		Zend_Layout::getMvcInstance ()->assign ( 'ingresaEmpSelectorForm', Application_Form_IngresaEmpSelector::factory ( Application_Form_IngresaEmpSelector::ROL_POSTULANTE ) );
		Zend_Layout::getMvcInstance ()->assign ( 'postulanteDni', Application_Form_PostulanteDni::factory(Application_Form_PostulanteDni::ROL_POSTULANTE ));


        $this->setMediaPlannigData();
		$this->view->flashMessages = $this->_flashMessenger;


        /// refactor flux
        $vUtil = new App_View_Helper_Util();

        Zend_Layout::getMvcInstance ()->assign ('robots', 'index,follow');
        Zend_Layout::getMvcInstance ()->assign ('SEOCanonical', "");
        Zend_Layout::getMvcInstance ()->assign ('SEONext', "");
        Zend_Layout::getMvcInstance ()->assign ('SEOPrev', "");
            $form= new Application_Form_RegistroRapidoPostulante();
            $idusuario=isset($this->auth['usuario']->id)?$this->auth['usuario']->id:null;
            $form->validadorEmail($idusuario,'postulante');
              Zend_Layout::getMvcInstance()->assign(
                'formRegistroRapido',
                 $form

            );
	}

    /**
     *@return Zend_Layout
     */
    public function getLayoutView() {
        return Zend_Layout::getMvcInstance();
    }

    /**
     * @todo Segmentacion de vistas para e-planning
     */
    public function setMediaPlannigData()
    {

        $data = array();
        $loggedIn = isset($this->auth) && isset($this->auth['usuario'])  ;
        $permite=false;

        switch(CONTROLLER) {

            // Datos e-planning vista home
            case 'home' :
              $permite=TRUE;

                break;

            case 'mi-cuenta' :
                  $permite=TRUE;
                  $data['seccion'] = 'perfil';
                break;

            // Datos e-planning vista aviso
            case 'aviso' :
                $permite=TRUE;
                if( !$loggedIn ) {

                    $XSS = new App_Util();
                    $params = $XSS->clearXSS( $this->_getAllParams() );
                    $urlId = $params['url_id'];
                  

                    $modelAnuncio = new Application_Model_AnuncioWeb();
                    $dataAviso=$modelAnuncio->getAvisoIdByUrl($urlId);
                    $aviso = $modelAnuncio->getAvisoInfoficha( $dataAviso['id'] );

                    $data['niveles'] = $aviso['nivel_puesto_nombre'];
                    $data['area'] = $aviso['area_puesto'];
                    $data['id_anunciante'] = $aviso['id_empresa'];
                    $data['ubicacion'] = $aviso['ubigeo_nombre'];
                    $data['empresa'] = $aviso['nombre_empresa'];
                    $data['rubro'] = isset($aviso['rubro_empresa']) ? $aviso['rubro_empresa'] : '';
                }
                break;

            // Datos e-planning vista busqueda
            case 'buscar' :
                $permite=TRUE;
                if( !$loggedIn ) {

                    $XSS = new App_Util();
                    $params = $XSS->clearXSS( $this->_getAllParams() );
                    $data['busqueda'] = isset($params['q']) ? $params['q'] : '';
                }

                break;


            // En todos los demas casos
            default : break;
        }

        if( $loggedIn && $permite ) {
           $data = array_merge( $data, $this->getMediaEplanningData_Usuario());
        }
//        var_dump($data);exit;
        Zend_Layout::getMvcInstance()->assign(array('paramsUser' => $data));
    }

    public function getMediaEplanningData_Usuario()
    {
        $data = array();

        if( isset($this->auth['postulante']) ) {

            $postulante   = $this->auth['postulante'];
            $postulanteId = $postulante['id'];
            $modelPostulante = new Application_Model_Postulante();
            $resOtrosEstudios = $modelPostulante->getTodosOtrosEstudios($postulanteId);
            $resEstudios = $modelPostulante->getEstudios($postulanteId, true);
            $ultimaExperiencia = $modelPostulante->getUltimaExperiencias($postulanteId);

            $otrosEstudios = array();
            foreach ($resOtrosEstudios as $item) {
                $otrosEstudios[] = $item['titulo'];
            }

            $institucion = array();
            $carrera = array();
            foreach ($resEstudios as $item) {
                $institucion[] = $item['institucion'];
                $carrera[] = $item['otro_carrera'];
            }

            $mejor_nivel_estudio = explode('/', $postulante['mejor_nivel_estudio'] );
            $grado = $mejor_nivel_estudio[0];
            $estado = isset($mejor_nivel_estudio[1]) ? $mejor_nivel_estudio[1] : '';

            $fi = new DateTime("now");
            $ff = new DateTime($this->auth['postulante']['fecha_nac']);
            $edad = $ff->diff($fi)->format('%y');


            $data['id_usuario'] = 'usuario';
            $data['grado'] = $grado;
            $data['otros_estudios'] = implode('|', $otrosEstudios);
            $data['estado'] = $estado;
            $data['institucion'] = implode('|', $otrosEstudios);
            $data['carrera'] = implode(',', $carrera);
            $data['ult_experiencia'] = $ultimaExperiencia;
            $data['genero'] = $this->auth['postulante']['sexo'];
            $data['edad'] = $edad;

        }else if( isset($this->auth['empresa']) ) {
           $data['id_usuario'] = 'empresa';
        }

        $data = array_filter($data);
        return $data;
    }



}