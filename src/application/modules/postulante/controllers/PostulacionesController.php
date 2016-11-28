<?php
/**
 * Description of PostulacionesController
 *
 * @author Dennis Pozo
 */
class Postulante_PostulacionesController extends App_Controller_Action_Postulante
{

	 public function preDispatch()
	 {
		  parent::preDispatch();
		  $url = $this->_getParam("url");
		  if ($url!="" && !isset($this->auth["postulante"])) {
				$this->_redirect("#loginP-".$url);
		  } else {
				if ($url != "") {
					 $ruta = $this->_getAllParams();
					 $x = "";

					 foreach ($ruta as $key => $item) {
						  if ($key != "controller" && $key != "action" && $key != "module" && $key != "url") {
								$x.="/" . $key . "/" . $item;
						  }
					 }
					 if ($x != "") {
						  $this->_redirect("/postulaciones#" . $x);
					 }
					 else {
						  $this->_redirect(base64_decode($url));
					 }
				}
		  }
	 }

	 public function init()
	 {
		  parent::init();
		  $this->_postulaciones = new Application_Model_Postulacion();
		  Zend_Layout::getMvcInstance()->assign(
				'bodyAttr', array('id'=>'myAccount')
		  );
		  $this->_submenuMiCuenta = array(
				array('href'=>'mi-cuenta','value'=>'Mis Datos Personales','action'=>'mis-datos-personales'),
				array('href'=>'postulaciones','value'=>'Mis Postulaciones','action'=>'index'),
				array('href'=>'notificaciones','value'=>'Mis Notificaciones','action'=>'index'),
				array('href'=>'mi-cuenta','value'=>'Cómo destacar más','action'=>'perfil-destacado'),
		  );
	 }

	 public function indexAction()
	 {
		  $this->view->menu_sel = self::MENU_MI_CUENTA;
		  $this->view->menu_post_sel = self::MENU_POST_MIS_POSTULACIONES;
		  $page = $this->_getParam('page', 1);
		  $this->view->col = $col = $this->_getParam('col', '');
		  $this->view->ord = $ord = $this->_getParam('ord', '');
		  Zend_Layout::getMvcInstance()->assign(
				'submenuMiCuenta', $this->_submenuMiCuenta
		  );

		  $idPostulante = $this->auth['postulante']['id'];
		  $idUsuario = $this->auth['postulante']['id_usuario'];

		  $verLogoDefecto = (bool) $this->config->defaultLogoEmpresa->enabled;
		  $rutaLogoDefecto = $this->config->defaultLogoEmpresa->fileName;

		  $paginator = $this->_postulaciones->getPaginator(
				$idPostulante,
				$col,
				$ord
		  );

		  // Fechas del proceso de postulación
		  $modelVisitas = new Application_Model_Visitas();
		  $modelMensajes = new Application_Model_Mensaje();
		  $arrEstados = array();

		  $arrNotas=array();
		  $paginator->setCurrentPageNumber($page);

		  foreach ($paginator as $item) {
				$arrEstados[$item['idpostulacion']]['postulo'] = $this->view->util()->fechaDiMes($item['fecha']);
				$visita = $modelVisitas->getPrimeraVisita($idPostulante, $item['id_empresa'], 1, $item['p.id_anuncio_web'] );
				if (isset($visita[0]) && isset($visita[0]['fecha_busqueda'])) {
					$arrEstados[$item['idpostulacion']]['vio_cv'] = $this->view->util()->fechaDiMes($visita[0]['fecha_busqueda']);
				}

				$mensajes = $modelMensajes->getMensajesByUsuarioPostulacion($idUsuario, $item['idpostulacion'], true);
				foreach ($mensajes as $mensaje) {
					$arrEstados[$item['idpostulacion']][$mensaje['tipo_mensaje']][] = $this->view->util()->fechaDiMes($mensaje['fh']);
				}

				$notificaciones = $modelMensajes->getNMensajesPostulacion( $item['idpostulacion']);
				$arrNotas[$item['idpostulacion']] =$notificaciones;
		  }

		  $paginator->estados = $arrEstados;
		  $this->view->notas = $arrNotas;
		  $this->view->id_postulante = $idPostulante;
		  $this->view->postulaciones = $paginator;
		  $this->view->verLogoDefecto = $verLogoDefecto;
		  $this->view->logoDefecto = $rutaLogoDefecto;
		  $this->view->paginaActual = $page;

		  if (count($paginator) > 0) {
				Zend_Layout::getMvcInstance()->assign('modalDespostular', true);
		  }
	 }

	 public function listaMensajesAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->view->empresa = $this->_getParam('empresa');
		  $this->view->puesto = $this->_getParam('puesto');
		  $this->view->fecha = $this->_getParam('fecha');
		  $this->_mensajes = new Application_Model_Mensaje();
		  $this->view->mensajes = $this->_mensajes->getMensajesPostulacion(
				$this->auth['usuario']->id, $this->_getParam('idPostulacion')
		  );
	 }

	 public function leerMensajeAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();
		  $idMensaje = $this->_getParam('id-mensaje');
		  $this->_mensajes = new Application_Model_Mensaje();
		  $status = (bool) $this->_mensajes->marcarComoLeidoMsgPostulacion($idMensaje);
		  $data = array(
				'status' => $status
		  );
		  $json = Zend_Json::encode($data);
		  $this->_response->appendBody($json);
	 }

	 public function guardarRptaAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();
		  $this->_mensajes = new Application_Model_Mensaje();
		  $id = $this->_getParam('id-mensaje');
		  $mensaje = $this->_mensajes->getMensaje($id);

		  $postulacion = $this->_postulaciones->getPostulacion($mensaje['id_postulacion']);

		  $data = array(
				'padre' => $id,
				'de' => $this->auth['usuario']->id,
				'para' => $mensaje['de'],
				'fh' => date('Y-m-d H:i:s'),
				'leido' => 0,
				'cuerpo' => $this->_getParam('txt-rpta'),
				'id_postulacion' => $mensaje['id_postulacion']
		  );
		  $status = (bool) $this->_mensajes->insert($data);
		  $where = $this->_mensajes->getAdapter()->quoteInto('id = ?', $id);
		  $okUpdateP = $this->_mensajes->update(array('respondido' => '1'), $where);

		  $this->_helper->Mensaje->actualizarCantMsjsPostulacion(
				$this->auth['usuario']->id,
				$mensaje['id_postulacion']
		  );


		  $this->_helper->Aviso->actualizarMsgRsptNoLeidos(
				$postulacion['id_anuncio_web'],
				$mensaje['id_postulacion']
		  );

		  $data = array(
				'status' => $status
		  );
		  $json = Zend_Json::encode($data);
		  $this->_response->appendBody($json);

		  $objUsuario = new Application_Model_Usuario();
		  $rowUsuario = $objUsuario->getUsuarioPostulacion($mensaje['id_postulacion']);

		  $idUsuarioEmpresa = $mensaje['de'];
		  $modelUsuario = new Application_Model_Usuario;
		  $dataUsuarioEmpresa = $modelUsuario->obtenerNombre($idUsuarioEmpresa);

		  $this->_helper->mail->respuestaMensaje(
				array (
					 'to' => $rowUsuario[0]['empresa_email'],
					 'usuario' => $rowUsuario[0]['email'],
					 'nombre' => ucwords($rowUsuario[0]['nombres'])." ".ucwords($rowUsuario[0]['apellidos']),
					 'nombrePuesto' => strtoupper($rowUsuario[0]['puesto']),
					 'empresa' => $rowUsuario[0]['nombre_comercial'],
					 'idPuesto' => $postulacion['id_anuncio_web'],
					 'id_postulacion' => $mensaje['id_postulacion'],
					 'nomUsuEmpresa' => $dataUsuarioEmpresa['nombres']
				)
		  );


	 }

	 public function eliminarPostulacionAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();

		  $idPostulacion = $this->_getParam('id');

		  if ($idPostulacion) {

				try {
					 $modelPostulacion = new Application_Model_Postulacion();
					 $postulacion = $modelPostulacion->getPostulacion($idPostulacion);

					 $id_anuncio_web = $postulacion['id_anuncio_web'];
					 $idPostulante = $this->auth['postulante']['id'];

					 $this->_helper->Aviso->desPostular($idPostulacion, $id_anuncio_web, $idPostulante);

					 $modelAW = new Application_Model_AnuncioWeb();
					 $avisoActivo = $modelAW->estadoActivo($id_anuncio_web);

					 if ($avisoActivo) {
						  $this->notificarDespostulacion($id_anuncio_web);
					 }

					 $this->getMessenger()->success('Se despostuló del proceso satisfactoriamente.');

				} catch (Exception $ex) {
					 $this->getMessenger()->error('Ocurrió un error. No se realizó del proceso .');
				}


		  } else {
				$this->getMessenger()->error('Ocurrió un error. Error de datos.');
		  }

		  $this->_redirect('/postulaciones');
	 }

	 private function notificarDespostulacion($id_anuncio_web)
	 {
		  if ($id_anuncio_web) {

				$modelAnuncioWeb = new Application_Model_AnuncioWeb();
				$aviso = $modelAnuncioWeb->getAvisoById($id_anuncio_web, false);

				$to = trim($aviso['correo']);
				$validatorEmail = new Zend_Validate_EmailAddress($to);
				if (empty($to) || (!$validatorEmail->isValid($to)) ) {
					 $modelUsuario = new Application_Model_Usuario();
					 $usuarioEmpresa = $modelUsuario->getUsuarioId($aviso['creado_por']);
					 $to = $usuarioEmpresa->email;
				}

				$nombres = $this->auth['postulante']['nombres'];
				$apellidos = (!empty($this->auth['postulante']['apellidos']) ) ?
					 $this->auth['postulante']['apellidos'] :
					 $this->auth['postulante']['apellido_paterno'] . ' ' .
					 $this->auth['postulante']['apellido_materno'];

				$this->_helper->mail->notificacionDespostularEmpresa(
						  array(
								'to' => $to,
								'nombres' => $nombres,
								'apellidos' => $apellidos,
								'urlAviso' => '/ofertas-de-trabajo/'.$aviso['slug'].'-'.$aviso['url_id'],
								'puesto' => $aviso['puesto']
						  )
				);
		  }
	 }

}
