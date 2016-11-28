<?php

/**
 * Description of PostulacionesController
 *
 * @author Dennis Pozo
 */
class Postulante_NotificacionesController extends App_Controller_Action_Postulante
{

    protected $_notificaciones;
    protected $_postulacion;

    public function preDispatch()
    {
        parent::preDispatch();
        $url = $this->_getParam("url");
        if (!isset($this->auth["postulante"])) {
            $this->_redirect("#loginP-".$url);
        } else {

        }
    }

    public function init()
    {
        parent::init();
        $this->_notificaciones = new Application_Model_Mensaje();
        $this->_aw= new Application_Model_AnuncioWeb();
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
        // listado de postulaciones
//        var_dump($this->auth["postulante"]);exit;
        $this->view->menu_sel = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MIS_NOTIFICACIONES;

        Zend_Layout::getMvcInstance()->assign(
                'submenuMiCuenta', $this->_submenuMiCuenta
        );

        $page = $this->_getParam('page', 1);
        $this->view->col = $col = $this->_getParam('col', '');
        $this->view->ord = $ord = $this->_getParam('ord', 'DESC');
        $paginator = $this->_notificaciones->getPaginatorNotificacion(
            $this->auth['usuario']->id,
            $col,
            $ord
        );
        $paginator->setCurrentPageNumber($page);
        $this->view->notificaciones = $paginator;
        $count=0;$i=0;
        $postulacion='';
        $idaviso=0;
        $i = 0;
        foreach ($paginator as $value) {
            if(!$value['leido']){
                $count++;
            }
            if($i == 0){
              $idaviso=$value['id_anuncio_web'];
              $postulacion=$value['postulacion'];
            }
            $i++;
        }


        $this->view->nroregistrosnoti = $count;
        $paraPos=$this->_getParam('postulacion',null);

        $postulacion= ($this->_getParam('postulacion'))? $this->_getParam('postulacion'):$postulacion;

        $jsonoti = '';
        $msjNoti = '';
        $this->view->logopostulante=$this->auth["postulante"]["path_foto"];
        $formRest=new Application_Form_Question(1);

        if ( !empty($postulacion)  && $idaviso )
        {
            $datamsj = $this->_notificaciones->getNotificacionesPostulacion($postulacion);
            $aw = $this->_aw->getAvisoInfoficha($idaviso);
            if (count($datamsj)>0) {
                foreach ($datamsj as $key => $value) {
                      $id=$value['id'];
                }
                $formRest->getElement('id_mensaje')->setValue($id);
                $this->view->logoEmpresa = $aw['logo_empresa'];
                $this->view->datajsonoti = $datamsj;
                $this->view->puesto = $aw['puesto'];
                $this->view->empresa = $aw['nombre_comercial'];
            }
        }

        $this->view->formRest=$formRest;
        $this->view->postulante = $this->auth["postulante"]['id'];
        $this->view->msjnoti = $msjNoti;
    }

    public function newIndexAction() {
    }

    public function listarNotificacionesAction(){
        $this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNoRender();
        $idaviso = $this->_getParam('idaviso', '');
        $postulacion = $this->_getParam('postulacion', '');

        $jsonoti = '';
        $msjNoti = '';
        $this->view->logoEmpresa='';
        $this->view->logopostulante=$this->auth["postulante"]["path_foto"];
        $this->_mensajes = new Application_Model_Mensaje();

        $formRest=new Application_Form_Question(1);
        if ( !empty($postulacion) )
        {
            $resAviso = $this->_aw->getAvisoIdByUrl( $idaviso );
            if( !empty($resAviso) )
            {
                $idAviso = $resAviso['id'];
                $datamsj = $this->_notificaciones->getNotificacionesPostulacion($postulacion);
                $aw = $this->_aw->getAvisoInfoficha($idAviso);

                if (count($datamsj)>0)
                {
                    $formRest->getElement('hidAuthTokenCuestion')->initCsrfToken();
                    $data=array();
                    $cuerpo='';
                    $id='';
                    $data['imgCompany']=  !empty($aw['logo_empresa'])?ELEMENTS_URL_LOGOS.$aw['logo_empresa']:$this->view->S('/images/icon-empresa-blank.png');
                    $data['imgPerson']=
                            empty($this->auth["postulante"]["path_foto"]) ?
                        $this->view->S('/images/profile-default.jpg')
                        : ELEMENTS_URL_IMG.$this->auth["postulante"]["path_foto"];
                    $data['Company']=$aw['nombre_comercial'];
                    $data['title']=$aw['puesto'];

                    foreach ($datamsj as $value => $key) {
                        $fecha=$this->view->Util()->fechaDiMes($key['fh']);
                        $id=$key['id'];
                        $status = (bool) $this->_mensajes->marcarComoLeidoMsgPostulacion($key['id']);
                        $data['conversation'][$value]['text']=str_replace("</P>","",str_replace("<P align=center style=\"color:#3366ff\">","",str_replace("<br><br>", "", $key['cuerpo'])));
                        $data['conversation'][$value]['date']=$fecha.'' ;
                        $data['conversation'][$value]['type']= ($key['tipo_mensaje']=='pregunta')?'empresa':'postulante'   ;
                    }

                    $data['token']=$formRest->getElement('hidAuthTokenCuestion')->getValue();
                    $data['id_mensaje']=$id;
                    $data['status']='0';
                    $data['msg']='exito';

                }else {
                    $formRest->getElement('hidAuthTokenCuestion')->initCsrfToken();
                    $formRest->getElement('hidAuthTokenCuestion')->initCsrfToken();
                    $data['status']='1';
                    $data['token']=$formRest->getElement('hidAuthTokenCuestion')->getValue();
                    $data['msg']='No exiten datos';
                }

            }else
            {
                $formRest->getElement('hidAuthTokenCuestion')->initCsrfToken();
                $data['status']='1';
                $data['token']=$formRest->getElement('hidAuthTokenCuestion')->getValue();
                $data['msg']='No existe el aviso con ese id';
            }

        }else
        {
            $formRest->getElement('hidAuthTokenCuestion')->initCsrfToken();
            $data['status']='1';
            $data['token']=$formRest->getElement('hidAuthTokenCuestion')->getValue();
            $data['msg']='No exite el id postulacion';
        }

        $json = Zend_Json::encode($data);
        $this->_response->appendBody($json);

    }

    public function leerMensajeAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $this->_mensajes = new Application_Model_Mensaje();
        $idMensaje = $this->_getParam('idMensaje');

/*        $this->view->mensaje = $this->_mensajes->getMensaje($idMensaje);
        $this->view->enviadoPor = $this->_getParam('enviadoPor');
        $this->view->fecha = $this->_getParam('fecha');
        $this->view->tipo = $this->_getParam('tipo');*/

        $status = (bool) $this->_mensajes->marcarComoLeidoMsgNotificacion($idMensaje);
        $data = array(
            'status' => $status
        );
        $json = Zend_Json::encode($data);
        $this->_response->appendBody($json);

    }

    public function guardarRptaAction()
    {
        try {

            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $this->_mensajes = new Application_Model_Mensaje();
            $dataRequest = $this->_getAllParams();
            $id = $dataRequest['id_mensaje'];
            $mensaje = $this->_mensajes->getMensaje($id);
            $formMensaje = new Application_Form_Question(true);
            $pos = new Application_Model_Postulacion();
            $postulacion = $pos->getPostulacion($mensaje['id_postulacion']);

            if ($formMensaje->isValid($dataRequest) &&  $mensaje && $postulacion ) {
            $dataRequest['txt-rpta']=$dataRequest['mensaje'];

            $data = array(
                'padre' => $id,
                'de' => $this->auth['usuario']->id,
                'para' => $mensaje['de'],
                'fh' => date('Y-m-d H:i:s'),
                'leido' => 0,
                'cuerpo' => $dataRequest['txt-rpta'],
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
            //obteniendo conversaciones
            $formRest=new Application_Form_Question(1);
            $datamsj = $this->_notificaciones->getNotificacionesPostulacion($postulacion['id']);
            $aw = $this->_aw->getDataAviso($postulacion['id_anuncio_web']);
            $postulante = new Application_Model_Postulante();
//            $post = $postulante->getPostulanteByUsarioId($postulacion['id_postulante']);

            $formRest->getElement('hidAuthTokenCuestion')->initCsrfToken();
            $dataConversation = array();
            $id='';
//            var_dump($post["path_foto"]);exit;
            $logoPostulante = empty($this->auth["postulante"]["path_foto"]) ?
                    $this->view->S('/images/profile-default.jpg')
                    : ELEMENTS_URL_IMG.$this->auth["postulante"]["path_foto"];

            $logoEmpresa = empty($aw['logo_empresa']) ?
                    $this->view->S('/images/icon-empresa-blank.png')
                    : ELEMENTS_URL_LOGOS.$aw['logo_empresa'];

            $dataConversation['imgCompany'] = $logoEmpresa;
            $dataConversation['imgPerson'] = $logoPostulante;
            foreach ($datamsj as $value => $key) {
                $fecha=$this->view->Util()->fechaDiMes($key['fh']);
                $id=$key['id'];
                $dataConversation['conversation'][$value]['text']=$key['cuerpo'];
                $dataConversation['conversation'][$value]['date']=$fecha.'' ;
                $dataConversation['conversation'][$value]['type']= ($key['tipo_mensaje']=='pregunta')?'empresa':'postulante'   ;
            }
            $dataConversation['token']=$formRest->getElement('hidAuthTokenCuestion')->getValue();
            $dataConversation['id_mensaje']=$id;

            $data = array(
                'status' => $status,
                'conversation' => $dataConversation,
            );
            $json = Zend_Json::encode($data);


            $objUsuario = new Application_Model_Usuario();
            $rowUsuario = $objUsuario->getUsuarioPostulacionCreador($mensaje['id_postulacion'],$mensaje['de']);

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
          $this->_response->appendBody($json);
      } else {
           $formMensaje->getElement('hidAuthTokenCuestion')->initCsrfToken();
            $data = array(
                'status' => false,
                'error' => 'No se pudo enviar el mensaje correctamente, intente nuevamente',
                'token'=>$formMensaje->getElement('hidAuthTokenCuestion')->getValue(),
                'conversation' => array()
            );
            $json = Zend_Json::encode($data);
            $this->_response->appendBody($json);
      }
      } catch (Exception $exc) {
           $error= $exc->getTraceAsString();
          $this->log->log($error, Zend_Log::ERR);
      }

    }

}
