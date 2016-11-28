<?php


class Admin_GestionController
    extends App_Controller_Action_Admin
{

    protected $_messageSuccess = 'Actualización exitosa.';
    protected $_messageError = 'Error al momento de guardar.';
    protected $_cache = null;

    public function init()
    {
        parent::init();

        if ($this->_cache == null) {
            $this->_cache = Zend_Registry::get('cache');
        }

        if (Zend_Auth::getInstance()->hasIdentity() != true) {
            $this->_redirect('/admin');
        }
        $this->view->rol = $this->auth['usuario']->rol;
    }

    public function indexAction()
    {
        if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_CALLCENTER) {
            $this->_forward('callcenter');
        } elseif ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_DIGITADOR) {
            $this->_forward('avisos-preferenciales');
        } else {
            $this->_forward('postulantes');
        }
    }

    public function postulantesAction()
    {
        if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_CALLCENTER
            ||
            $this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_DIGITADOR) {
            $this->_redirect('/admin/gestion');
        }
        // action body
        $this->view->menu_sel_side = self::MENU_POST_SIDE_POSTULANTES;
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/micuenta.admin.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/gestion.admin.js'
            )
        );
        $paginator = array();

        $formPostulante = new Application_Form_AdminPostulante();
        $this->view->formAdmin = $formPostulante;

        $params = $this->_getAllParams();
        $valid = $formPostulante->isValid($params);
        $valuesPost = $formPostulante->getValues();
        if ($valid && ($valuesPost['nombres'] != null ||
            $valuesPost['apellidos'] != null ||
            $valuesPost['email'] != null ||
            $valuesPost['num_doc'] != null)
        ) {
            $sess = $this->getSession();
            $this->view->postulanteAdminUrl = $sess->postulanteAdminUrl = $params;

            $this->view->col = $col = $this->_getParam('col', '');
            $this->view->ord = $ord = $this->_getParam('ord', 'DESC');
            $page = $this->_getParam('page', 1);

            $modelPostulante = new Application_Model_Postulante();
            $paginator =
                $modelPostulante
                ->getPaginadorBusquedaPersonalizada(
                $valuesPost['nombres'], $valuesPost['apellidos'],
                $valuesPost['num_doc'], $valuesPost['email'], $col, $ord
            );
            $this->view->mostrando = "Mostrando "
                . $paginator->getItemCount($paginator->getItemsByPage($page)) . " de "
                . $paginator->getTotalItemCount();
            $paginator->setCurrentPageNumber($page);
            $this->view->pagina = $paginator->getCurrentPageNumber();
        }
        $this->view->arrayBusqueda = $paginator;
    }

    public function avisosAction()
    {
        $this->view->menu_sel_side = self::MENU_POST_SIDE_AVISOS;
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/micuenta.admin.js'
            )
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/gestion.admin.js'
            )
        );

        if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_CALLCENTER) {
            $this->_redirect('/admin/gestion');
        }
        $paginator = array();

        $formAviso = new Application_Form_AdminAviso();
        $this->view->formAviso = $formAviso;
        $params = $this->_getAllParams();
        if (isset($params['tipobusq']) && $params['tipobusq'] == 1) {
            $formAviso->getElement('cod_ade')->setRequired();
        }
        $valid = $formAviso->isValid($params);
        $valuesPost = $formAviso->getValues();

        if ($valid && ($valuesPost['url_id'] != null ||
            $valuesPost['razonsocial'] != null ||
            $valuesPost['num_ruc'] != null ||
            $valuesPost['fh_pub'] != null ||
            $valuesPost['cod_ade'] != '' ||
            $valuesPost['tipobusq'] != 0)
        ) {
            $sess = $this->getSession();
            $this->view->avisoAdminUrl = $sess->avisoAdminUrl = $params;

            if (isset($params['fh_pub'])) {
                $newFhPub = explode('-', $params['fh_pub']);
                $fhPub = $newFhPub[0] . '/' . $newFhPub[1] . '/' . $newFhPub[2];
                $formAviso->setDefault('fh_pub', $fhPub);
            }
            $this->view->col = $col = $this->_getParam('col', '');
            $this->view->ord = $ord = $this->_getParam('ord', 'DESC');
            $page = $this->_getParam('page', 1);
            $modelAw = new Application_Model_AnuncioWeb();
            $fhPub = $valuesPost['fh_pub'];
            if ($fhPub != null) {
                $fhPub = date(
                    'Y-m-d',
                    strtotime($valuesPost['fh_pub'] == null ? null : $valuesPost['fh_pub'])
                );
            } else {
                $fhPub = null;
            }
            $paginator =
                $modelAw
                ->getPaginadorBusquedaPersonalizada(
                $valuesPost['url_id'], $valuesPost['razonsocial'],
                $valuesPost['num_ruc'], $valuesPost['cod_ade'],
                isset($valuesPost['tipobusq']) ? $valuesPost['tipobusq'] : '0',
                $fhPub, $col, $ord
            );
            $this->view->mostrando = "Mostrando "
                . $paginator->getItemCount($paginator->getItemsByPage($page)) . " de "
                . $paginator->getTotalItemCount();
            $paginator->setCurrentPageNumber($page);
            $this->view->pagina = $paginator->getCurrentPageNumber();
        }
        $this->view->arrayBusqueda = $paginator;
    }

    public function empresasAction()
    {
        if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_CALLCENTER
            ||
            $this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_DIGITADOR) {
            $this->_redirect('/admin/gestion');
        }
        $this->view->menu_sel_side = self::MENU_POST_SIDE_EMPRESAS;
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/micuenta.admin.js'
            )
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/gestion.admin.js'
            )
        );

        $paginator = array();

        $formEmpresa = new Application_Form_AdminEmpresa();
        $this->view->formAdmin = $formEmpresa;

        $params = $this->_getAllParams();
        $valid = $formEmpresa->isValid($params);
        $valuesPost = $formEmpresa->getValues();
        if ($valid && ($valuesPost['razonsocial'] != '' ||
            $valuesPost['num_ruc'] != '')
        ) {
            $sess = $this->getSession();
            $this->view->empresaAdminUrl = $sess->empresaAdminUrl = $params;

            $this->view->col = $col = $this->_getParam('col', '');
            $this->view->ord = $ord = $this->_getParam('ord', 'DESC');
            $page = $this->_getParam('page', 1);

            $modelAw = new Application_Model_Empresa();
            $paginator =
                $modelAw
                ->getPaginadorBusquedaPersonalizada(
                $valuesPost['razonsocial'], $valuesPost['num_ruc'], $col, $ord
            );
            $this->view->mostrando = "Mostrando " .
                $paginator->getItemCount($paginator->getItemsByPage($page)) . " de " .
                $paginator->getTotalItemCount();
            $paginator->setCurrentPageNumber($page);
            $this->view->pagina = $paginator->getCurrentPageNumber();
        }
        $this->view->arrayBusqueda = $paginator;
    }

    public function cambioClaveAction()
    {
        $this->view->menu_sel_side = self::MENU_POST_SIDE_CAMBIOCLAVE;

        $this->view->headScript()->appendFile(
            $this->view->S('/js/form.paso1.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/gestion.admin.js'
            )
        );

        $idUsuario = $this->auth['usuario']->id;
        $emailUsuario = $this->auth['usuario']->email;

        $formCambioClave = new Application_Form_CambioClave($idUsuario);
        $formCambioClave->validarPswd($emailUsuario, $idUsuario);

        if ($this->_request->isPost()) {

            $allParams = $this->_getAllParams();

            $validClave = $formCambioClave->isValid($allParams);

            if ($validClave) {
                $valuesClave = $formCambioClave->getValues();
                try {

                    $db = $this->getAdapter();
                    $db->beginTransaction();

                    //Captura de los datos de usuario
                    $valuesClave['pswd'] =
                        App_Auth_Adapter_AptitusDbTable::generatePassword($valuesClave['pswd']);
                    unset($valuesClave['pswd2']);
                    unset($valuesClave['oldpswd']);

                    $modelUsuario = new Application_Model_Usuario();
                    $where = $modelUsuario->getAdapter()
                        ->quoteInto('id = ?', $idUsuario);
                    $modelUsuario->update($valuesClave, $where);
                    $db->commit();

                    $this->getMessenger()->success('Se cambio la clave con éxito.');

                    $this->_redirect(
                        $this->getRequest()->getRequestUri()
                    );
                } catch (Zend_Db_Exception $e) {
                    $db->rollBack();
                    echo $e->getMessage();
                } catch (Zend_Exception $e) {
                    $this->getMessenger()->error($this->_messageSuccess);
                    echo $e->getMessage();
                }
            }
        }
        $this->view->formCambioClave = $formCambioClave;
    }

    public function bloquearPostulanteAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $params = $this->_getAllParams();
        $data = array();

        if ($this->_request->isPost()) {

            $modelPostulante = new Application_Model_Postulante();
            $arrayPostulante = $modelPostulante->getPostulante($params['idPost']);
            $where = $this->getAdapter()->quoteInto('id = ?',
                $arrayPostulante['id_usuario']);
            $data['activo'] = 0;

            $modelUsuario = new Application_Model_Usuario();
            $val = $modelUsuario->update($data, $where);
            $this->getMessenger()->success($this->_messageSuccess);

            $this->_helper->mail->bloquearPostulante(
                array(
                    'to' => $arrayPostulante['email'],
                    'user' => $arrayPostulante['email'],
                    'fr' => date('Y-m-d H:i:s'),
                    'nombre' => ucwords($arrayPostulante['nombres']),
                )
            );
        }
    }

    public function desbloquearPostulanteAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $params = $this->_getAllParams();
        $data = array();

        if ($this->_request->isPost()) {

            $modelPostulante = new Application_Model_Postulante();
            $arrayPostulante = $modelPostulante->getPostulante($params['idPost']);
            $where = $this->getAdapter()->quoteInto('id = ?',
                $arrayPostulante['id_usuario']);
            $data['activo'] = 1;

            $modelUsuario = new Application_Model_Usuario();
            $val = $modelUsuario->update($data, $where);
            $this->getMessenger()->success($this->_messageSuccess);

            $this->_helper->mail->desbloquearPostulante(
                array(
                    'to' => $arrayPostulante['email'],
                    'user' => $arrayPostulante['email'],
                    'fr' => date('Y-m-d H:i:s'),
                    'nombre' => ucwords($arrayPostulante['nombres']),
                )
            );
        }
    }

    public function bloquearEmpresaAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $params = $this->_getAllParams();
        $idEmp = $params['idEmp'];

        $data = array();
        $arrayUsuAdm = array();

        if ($this->_request->isPost()) {
            $modelUsuEmp = new Application_Model_UsuarioEmpresa();
            $arrayUsuEmp = $modelUsuEmp->getAdministradores($idEmp);

            foreach ($arrayUsuEmp as $data) {
                $arrayUsuAdm[] = $data['id_usuario'];
            }

            $where = $this->getAdapter()->quoteInto('id in (?) ', $arrayUsuAdm);
            $modelUsuario = new Application_Model_Usuario();
            $val = $modelUsuario->update(array('activo' => 0), $where);

            $modelEmpresa = new Application_Model_Empresa();
            $arrayEmpresa = $modelEmpresa->getEmpresa($idEmp);

            $modelAnuncioWeb = new Application_Model_AnuncioWeb();
            $arrayAnuncioWeb = $modelAnuncioWeb->getAdapter()->fetchAll(
                $modelAnuncioWeb->getAvisosActivos(
                    $idEmp, '1', Application_Model_AnuncioWeb::ESTADO_PAGADO,
                    '0', '', ''
                )
            );

            foreach ($arrayAnuncioWeb as $newAw) {

                $idAviso = $newAw['id'];

                $dataAw['online'] = '0';
                $dataAw['modificado_por'] = $this->auth['usuario']->id;
                $dataAw['fh_edicion'] = date('Y-m-d H:i:s');
                $dataAw['fh_aviso_baja'] = date('Y-m-d H:i:s');
                $dataAw['estado_anterior'] = $newAw['estado'];
                $dataAw['estado'] = Application_Model_AnuncioWeb::ESTADO_BANEADO;
                $where = $this->getAdapter()->quoteInto('id = ?', $idAviso);
                $modelAnuncioWeb->update($dataAw, $where);

                /*
                  $zl = new ZendLucene();
                  $zl->eliminarDocumentoAviso($idAviso);
                 */

                @$this->_cache->remove('AnuncioWeb_getAvisoById_' . $idAviso);
                @$this->_cache->remove('AnuncioWeb_getAvisoInfoById_' . $idAviso);
                @$this->_cache->remove('AnuncioWeb_getAvisoInfoficha_' . $idAviso);

                $this->_cache->remove('anuncio_web_' . $newAw['id_anuncio_web']);
//                @$this->_cache->remove('AnuncioWeb_getAvisoRelacionadosnew_' . $idAviso);
//                @$this->_cache->remove('AnuncioWeb_getAvisosRelacionadosAuxiliar_' . $idAviso);
                @$this->_cache->remove('AnuncioWeb_getAvisoIdByUrl_'.$newAw['url_id']);
                @$this->_cache->remove('AnuncioWeb_getAvisoIdByCreado_'.$newAw['url_id']);
            }

            $this->_helper->mail->bloquearEmpresa(
                array(
                    'to' => $arrayEmpresa['email'],
                    'empresa' => $arrayEmpresa['razonsocial']
                )
            );
        }
    }

    public function desbloquearEmpresaAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $params = $this->_getAllParams();
        $idEmp = $params['idEmp'];

        $data = array();
        $arrayUsuAdm = array();

        if ($this->_request->isPost()) {
            $modelUsuEmp = new Application_Model_UsuarioEmpresa();
            $arrayUsuEmp = $modelUsuEmp->getAdministradores($idEmp);

            foreach ($arrayUsuEmp as $data) {
                $arrayUsuAdm[] = $data['id_usuario'];
            }

            $where = $this->getAdapter()->quoteInto('id in (?) ', $arrayUsuAdm);
            $modelUsuario = new Application_Model_Usuario();
            $val = $modelUsuario->update(array('activo' => 1), $where);

            $modelEmpresa = new Application_Model_Empresa();
            $arrayEmpresa = $modelEmpresa->getEmpresa($idEmp);

            $modelAnuncioWeb = new Application_Model_AnuncioWeb();
            $arrayAnuncioWeb = $modelAnuncioWeb->
                getAvisoBaneadoXEmpresa($idEmp,
                Application_Model_AnuncioWeb::ESTADO_BANEADO);

            foreach ($arrayAnuncioWeb as $newAw) {
                $idAviso = $newAw['id'];

                //validación de Fecha
                $list = explode('/', $newAw['fpublicacion']);
                $arrayFechaVen = $list[2] . $list[1] . $list[0];

                $listDos = explode('/', date('d/m/Y'));
                $arrayFechaAct = $listDos[2] . $listDos[1] . $listDos[0];

                if ((int) $arrayFechaVen >= (int) $arrayFechaAct) {
                    $dataAw['online'] = '1';
                } else {
                    $dataAw['online'] = '0';
                }

                $dataAw['modificado_por'] = $this->auth['usuario']->id;
                $dataAw['fh_edicion'] = date('Y-m-d H:i:s');
                $dataAw['fh_aviso_baja'] = null;
                $dataAw['estado'] = $newAw['estado_anterior'];
                $dataAw['estado_anterior'] = null;
                $where = $this->getAdapter()->quoteInto('id = ?', $idAviso);
                $modelAnuncioWeb->update($dataAw, $where);

                /*
                  $zl = new ZendLucene();
                  $zl->agregarNuevoDocumentoAviso($idAviso);
                 */
                $this->_cache->remove('AnuncioWeb_getAvisoById_' . $idAviso);
                $this->_cache->remove('AnuncioWeb_getAvisoInfoById_' . $idAviso);
                @$this->_cache->remove('AnuncioWeb_getAvisoInfoficha_' . $idAviso);

                $this->_cache->remove('anuncio_web_' . $newAw['url_id']);
                
//                @$this->_cache->remove('AnuncioWeb_getAvisoRelacionadosnew_' . $idAviso);
//                @$this->_cache->remove('AnuncioWeb_getAvisosRelacionadosAuxiliar_' . $idAviso);
                @$this->_cache->remove('AnuncioWeb_getAvisoIdByUrl_'.$newAw['url_id']);
                @$this->_cache->remove('AnuncioWeb_getAvisoIdByCreado_'.$newAw['url_id']);
            }
            $this->_helper->mail->desbloquearEmpresa(
                array(
                    'to' => $arrayEmpresa['email'],
                    'empresa' => $arrayEmpresa['razonsocial']
                )
            );
        }
    }

    public function bloquearAvisoAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idAviso = $this->_getParam('idEmp', null);

        $data = array();

        if ($this->_request->isPost()) {

            $modelAw = new Application_Model_AnuncioWeb();
            $arrayAw = $modelAw->getAvisoById($idAviso);

            $data['online'] = '0';
            $data['modificado_por'] = $this->auth['usuario']->id;
            $data['fh_edicion'] = date('Y-m-d H:i:s');
            $data['fh_aviso_baja'] = date('Y-m-d H:i:s');
            $data['estado_anterior'] = $arrayAw['estado'];
            $data['estado'] = Application_Model_AnuncioWeb::ESTADO_BANEADO;

            $where = $this->getAdapter()->quoteInto('id = ?', $idAviso);
            $modelAw->update($data, $where);
            /*
              $zl = new ZendLucene();
              $zl->eliminarDocumentoAviso($idAviso);
             */
            $modelEmpresa = new Application_Model_Empresa();
            $arrayEmpresa = $modelEmpresa->getEmpresa($arrayAw['id_empresa']);
            @$this->_cache->remove('AnuncioWeb_getAvisoInfoficha_' . $idAviso);
            $this->_cache->remove('AnuncioWeb_getAvisoById_' . $idAviso);
            $this->_cache->remove('AnuncioWeb_getAvisoInfoById_' . $idAviso);
            $this->_cache->remove('anuncio_web_' . $arrayAw['url_id']);
//            @$this->_cache->remove('AnuncioWeb_getAvisoRelacionadosnew_' . $idAviso);
//            @$this->_cache->remove('AnuncioWeb_getAvisosRelacionadosAuxiliar_' . $idAviso);
            @$this->_cache->remove('AnuncioWeb_getAvisoIdByUrl_'.$arrayAw['url_id']);
            @$this->_cache->remove('AnuncioWeb_getAvisoIdByCreado_'.$arrayAw['url_id']);

            $this->_helper->mail->bloquearAviso(
                array(
                    'to' => $arrayEmpresa['email'],
                    'empresa' => $arrayAw['nombre_empresa'],
                    'puesto' => $arrayAw['tipo_puesto']
                )
            );
        }
    }

    public function desbloquearAvisoAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idAviso = $this->_getParam('idEmp', null);

        $data = array();

        if ($this->_request->isPost()) {

            $modelAw = new Application_Model_AnuncioWeb();
            $arrayAw = $modelAw->getAvisoById($idAviso);

            $list = explode('/', $arrayAw['fpublicacion']);
            $arrayFechaVen = $list[2] . $list[1] . $list[0];

            $listDos = explode('/', date('d/m/Y'));
            $arrayFechaAct = $listDos[2] . $listDos[1] . $listDos[0];

            if ((int) $arrayFechaVen >= (int) $arrayFechaAct) {
                $data['online'] = '1';
            } else {
                $data['online'] = '0';
            }

            $data['modificado_por'] = $this->auth['usuario']->id;
            $data['fh_edicion'] = date('Y-m-d H:i:s');
            $data['fh_aviso_baja'] = null;
            $data['estado'] = $arrayAw['estado_anterior'];
            $data['estado_anterior'] = null;

            $where = $this->getAdapter()->quoteInto('id = ?', $idAviso);
            $modelAw->update($data, $where);
            /*
              $zl = new ZendLucene();
              $zl->agregarNuevoDocumentoAviso($idAviso);
             */
            $modelEmpresa = new Application_Model_Empresa();
            $arrayEmpresa = $modelEmpresa->getEmpresa($arrayAw['id_empresa']);

            $this->_cache->remove('AnuncioWeb_getAvisoById_' . $idAviso);
            $this->_cache->remove('AnuncioWeb_getAvisoInfoById_' . $idAviso);
            $this->_cache->remove('anuncio_web_' . $arrayAw['url_id']);
            @$this->_cache->remove('AnuncioWeb_getAvisoInfoficha_' . $idAviso);
//            @$this->_cache->remove('AnuncioWeb_getAvisoRelacionadosnew_' . $idAviso);
//            @$this->_cache->remove('AnuncioWeb_getAvisosRelacionadosAuxiliar_' . $idAviso);
            @$this->_cache->remove('AnuncioWeb_getAvisoIdByUrl_'.$arrayAw['url_id']);
            @$this->_cache->remove('AnuncioWeb_getAvisoIdByCreado_'.$arrayAw['url_id']);

            $this->_helper->mail->desbloquearAviso(
                array(
                    'to' => $arrayEmpresa['email'],
                    'empresa' => $arrayAw['nombre_empresa'],
                    'puesto' => $arrayAw['tipo_puesto']
                )
            );
        }
    }

    public function agregarMensajeAction()
    {
        $this->_helper->layout->disableLayout();

        $idAdmin = $this->auth['usuario']->id;
        $formMensaje = new Application_Form_Mensajes();
        $modelMen = new Application_Model_Mensaje();

        $params = $this->_getAllParams();
        $modelPostulante = new Application_Model_Postulante();
        $this->view->idPostulante = $idPostulante = $params['idPost'];
        $arrayPostulante = $modelPostulante->getPostulante($idPostulante);
        $idUsuario = $arrayPostulante['idusuario'];
        if ($this->_request->isPost()) {
            $mensaje = $this->_getAllParams();
            $validMen = $formMensaje->isValid($mensaje);
            $valuesMensaje = $formMensaje->getValues($mensaje);
            if ($valuesMensaje['cuerpo'] != "") {

                $fecha = date('Y-m-d H:i:s');
                $valuesMensaje['de'] = $idAdmin;
                $valuesMensaje['para'] = $idUsuario;
                $valuesMensaje['fh'] = $fecha;
                $tipomensaje = $this->_getParam("tipo_mensaje");
                $valuesMensaje['tipo_mensaje'] = Application_Model_Mensaje::ESTADO_MENSAJE;

                $valuesMensaje['leido'] = 0;
                $valuesMensaje['respondido'] = 0;
                $valuesMensaje['notificacion'] = 1;
                unset($valuesMensaje['id_mensaje']);

                $a = $modelMen->insert($valuesMensaje);

                $nurl = base64_encode("/notificaciones/");
                $this->_helper->mail->mensajeAdminPostulante(
                    array(
                        'to' => $arrayPostulante['email'],
                        'email' => $arrayPostulante['email'],
                        'nombre' => ucwords($arrayPostulante['nombres']),
                        //'url' => $this->config->app->siteUrl."/notificaciones/index/url/".$nurl
                        'url' => $this->config->app->siteUrl . "/notificaciones/index/mensaje/" . $a
                    )
                );
                //actualizamos postulaciones
                $this->_helper->Mensaje->actualizarCantMsjsNotificacion(
                    $idUsuario
                );
            }
            exit;
        }
        $this->view->form = $formMensaje;
    }

    public function avisosPreferencialesAction()
    {
        if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_CALLCENTER
            ||
            $this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_SOPORTE
            ||
            $this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_MODERADOR) {
            $this->_redirect('/admin/gestion');
        }
        $this->view->menu_sel_side = self::MENU_POST_SIDE_AVISOSPREFERENCIALES;
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/micuenta.admin.js'
            )
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/gestion.admin.js'
            )
        );
        $paginator = array();
        $formAvisosPref = new Application_Form_AdminAvisoPreferencial();
        $this->view->formAvisoPref = $formAvisosPref;

        $params = $this->_getAllParams();
        if (isset($params['tipobusq']) && $params['tipobusq'] == 1) {
            $formAvisosPref->getElement('cod_ade')->setRequired();
        }
        $valid = $formAvisosPref->isValid($params);
        $valuesPost = $formAvisosPref->getValues();
        if ($valid && ($valuesPost['nom_puesto'] != '' ||
            $valuesPost['num_ruc'] != '' ||
            $valuesPost['fh_pub'] != '' ||
            $valuesPost['origen'] != '' ||
            $valuesPost['cod_ade'] != '' ||
            $valuesPost['tipobusq'] != 0 )
        ) {
            $sess = $this->getSession();
            $this->view->avisoPreferencialAdminUrl = $sess->avisoPreferencialAdminUrl
                = $params;

            if (isset($params['fh_pub']) && $params['fh_pub'] != '') {
                $newFhPub = explode('-', $params['fh_pub']);
                $fhPub = $newFhPub[0] . '/' . $newFhPub[1] . '/' . $newFhPub[2];
                $formAvisosPref->setDefault('fh_pub', $fhPub);
            }
            $this->view->col = $col = $this->_getParam('col', '');
            $this->view->ord = $ord = $this->_getParam('ord', 'DESC');
            $page = $this->_getParam('page', 1);

            $modelAw = new Application_Model_AnuncioWeb();
            $fhPub = $valuesPost['fh_pub'];

            if ($fhPub != null) {
                $fhPub = date(
                    'Y-m-d',
                    strtotime($valuesPost['fh_pub'] == null ? null : $valuesPost['fh_pub'])
                );
            } else {
                $fhPub = null;
            }

            $paginator =
                $modelAw
                ->getPaginadorBusquedaPersonalizadaPreferencial(
                $valuesPost['nom_puesto'], $valuesPost['num_ruc'], $fhPub,
                $valuesPost['origen'], $valuesPost['cod_ade'],
                isset($valuesPost['tipobusq']) ? $valuesPost['tipobusq'] : '0',
                $col, $ord
            );
            $this->view->mostrando = "Mostrando "
                . $paginator->getItemCount($paginator->getItemsByPage($page)) . " de "
                . $paginator->getTotalItemCount();
            $paginator->setCurrentPageNumber($page);
            $this->view->pagina = $paginator->getCurrentPageNumber();
        }

        $this->view->arrayBusqueda = $paginator;
    }

    public function callcenterAction()
    {
        if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_MASTER
            ||
            $this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_CALLCENTER) {

            $this->view->headScript()->appendFile(
                $this->view->S(
                    '/js/administrador/gestion.admin.js'
                )
            );
            $this->view->headScript()->appendFile(
                $this->view->S(
                    '/js/administrador/gestion.callcenter.js'
                )
            );
            $this->view->menu_sel_side = self::MENU_POST_SIDE_CALLCENTER;
            $formCallcenter = new Application_Form_AdminCallcenter();
            $formCallcenter->validadorNumDoc();
            $this->view->formCallCenter = $formCallcenter;
        } else {
            $this->_redirect('/admin/gestion');
        }
    }

    public function buscarEmailCallcenterAction()
    {
        //$this->getAdminLog();
        $session = $this->getSession();
        $this->_helper->layout()->disableLayout();
        $allParams = $this->_getAllParams();
        $txtemail = $allParams["txtemail"];
        $numTipo = $allParams['numTipo'];
        $ruc = $allParams['valorTipo'];
        if (isset($txtemail)) {
            if ($numTipo == 8) {
                $ruc = '10' . $ruc;
            }
            $empresa = new Application_Model_Empresa();
            $cliente = $empresa->getEmpresaByEmail($txtemail, $ruc);

            if ($cliente != null) {
                $this->view->cliente = $cliente;
                $llamadas = new Application_Model_LlamadasCallcenter();
                $this->view->listaContactos = $llamadas->getAllByEmpresa($cliente["idempresa"]);
                $this->getLogger()->info(
                    "Intento de busqueda Encontrada de Email en Callcenter: " . $txtemail
                );
                $this->view->resultBusqueda = true;
                $session->empresaBusqueda = $cliente;
            } else {
                $this->view->resultBusqueda = false;
                $this->getLogger()->info(
                    "Intento de busqueda Fallida de Email en Callcenter: " . $txtemail
                );
            }
        }
    }

    public function llamadaClienteAction()
    {
        $this->_helper->layout()->disableLayout();
        $llamadas = new Application_Model_LlamadasCallcenter();
        $idEmpresa = $this->_getParam("idempresa");
        $idUsuario = $this->auth["usuario"]->id;
        $fecha = new DateTime("now");
        $fechaMostrar = $fecha->format("d/m/Y H:i:s");
        $llamadas->insert(
            array(
                "id_empresa" => $idEmpresa,
                "id_usuario" => $idUsuario,
                "fecha_registro" => $fecha->format("Y-m-d H:i:s")
            )
        );
        echo $fechaMostrar;
        die();
    }

    public function usuariosAdminAction()
    {
        if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_MASTER) {

            $this->view->headScript()->appendFile(
                $this->view->S(
                    '/js/administrador/admin.users.js'
                )
            );
            $this->view->menu_sel_side = self::MENU_POST_SIDE_USUARIOS;

            $allParams = $this->_getAllParams();

            $sess = $this->getSession();
            $this->view->usuarioAdmin = $sess->usuarioAdmin = $allParams;

            $this->view->col = $col = $this->_getParam('col', '');
            $this->view->ord = $ord = $this->_getParam('ord', 'DESC');
            $page = $this->_getParam('page', 1);

            $modelUsuario = new Application_Model_Usuario();

            $paginator =
                $modelUsuario->getPaginadorBusquedaAdministrador($col, $ord);
            $this->view->mostrando = "Mostrando "
                . $paginator->getItemCount($paginator->getItemsByPage($page)) . " de "
                . $paginator->getTotalItemCount();
            $paginator->setCurrentPageNumber($page);
            $this->view->pagina = $paginator->getCurrentPageNumber();

            $this->view->arrayBusqueda = $paginator;
        } else {
            $this->_redirect('/admin/gestion');
        }
    }

    public function nuevoUsuarioAdminAction()
    {
        $this->_helper->layout->disableLayout();
        $err = 0;
        $this->view->action = $this->_request->getActionName();
        $this->view->controller = $this->_request->getControllerName();

        $allParams = $this->_getAllParams();

        if (isset($allParams['idUsuAdmin'])) {
            $this->view->idUsuAdmin = $idUsuAdmin = $allParams['idUsuAdmin'];
        } else {
            $this->view->idUsuAdmin = $idUsuAdmin = null;
        }


        $formUsuAdmin = new Application_Form_AdminUsuario();
        $formUsuario = new Application_Form_Paso1Usuario($idUsuAdmin);
        $formUsuario->validadorEmail($idUsuAdmin,
            Application_Form_Login::ROL_ADMIN);
        $modelUsuario = new Application_Model_Usuario();

        if ($idUsuAdmin != null || $idUsuAdmin != '') {
            $arrayUsuAdmin = $modelUsuario->getUsuarioId($idUsuAdmin);
            $formUsuAdmin->setDefaults(get_object_vars($arrayUsuAdmin));
            $formUsuario->setDefaults(get_object_vars($arrayUsuAdmin));
        }

        if ($this->_request->isPost()) {
            $data = $this->_getAllParams();
            $valid = $formUsuAdmin->isValid($data);
            if ($data['pswd'] == '' && ($idUsuAdmin != null || $idUsuAdmin != '')) {
                $formUsuario->getElement('pswd')->setRequired(false);
                $formUsuario->getElement('pswd2')->setRequired(false);
            } else {
                $formUsuario->getElement('pswd')->setRequired(true);
                $formUsuario->getElement('pswd2')->setRequired(true);
            }
            $validUsu = $formUsuario->isValid($data);
            if ($valid && $validUsu) {
                try {
                    $db = $this->getAdapter();
                    $db->beginTransaction();
                    $arrayUsuario = $formUsuario->getValues();
                    $arrayValues = $formUsuAdmin->getValues();

                    $arrayValues['email'] = $arrayUsuario['email'];
                    $arrayValues['pswd'] = App_Auth_Adapter_AptitusDbTable::generatePassword(
                            $arrayUsuario['pswd']
                    );
                    $arrayValues['salt'] = '';
                    $arrayValues['ultimo_login'] = date('Y-m-d H:i:s');
                    $arrayValues['ip'] = $this->getRequest()->getServer('REMOTE_ADDR');
                    unset($arrayUsuario['pswd2']);

                    if ($idUsuAdmin != null || $idUsuAdmin != '') {
                        $where = $modelUsuario->getAdapter()->quoteInto('id = ?',
                            $idUsuAdmin);
                        $modelUsuario->update($arrayValues, $where);
                    } else {
                        $arrayValues['fh_registro'] = date('Y-m-d H:i:s');
                        $modelUsuario->insert($arrayValues);
                    }
                    $db->commit();
                    $err = 1;
                } catch (Zend_Db_Exception $e) {
                    $db->rollBack();
                    $this->getMessenger()->error('Error al registrar el administrador.');
                    echo $e->getMessage();
                } catch (Zend_Exception $e) {
                    $this->getMessenger()->error($this->_messageSuccess);
                    echo $e->getMessage();
                }
            } else {
                $err = -1;
            }
        }

        $this->view->error = $err;
        $this->view->formUsuAdmin = $formUsuAdmin;
        $this->view->formUsuario = $formUsuario;
    }

    public function bloquearUsuarioAdminAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idUsuAdmin = $this->_getParam('idUsuAdm', null);

        $data = array();

        if ($this->_request->isPost()) {

            $modelUsuario = new Application_Model_Usuario();

            $data['activo'] = 0;
            $data['fh_edicion'] = date('Y-m-d H:i:s');
            $data['modificado_por'] = $this->auth['usuario']->id;

            $where = $this->getAdapter()->quoteInto('id = ?', $idUsuAdmin);
            $modelUsuario->update($data, $where);
        }
    }

    public function desbloquearUsuarioAdminAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idUsuAdmin = $this->_getParam('idUsuAdm', null);
        $data = array();

        if ($this->_request->isPost()) {

            $modelUsuario = new Application_Model_Usuario();

            $data['activo'] = 1;
            $data['fh_edicion'] = date('Y-m-d H:i:s');
            $data['modificado_por'] = $this->auth['usuario']->id;

            $where = $this->getAdapter()->quoteInto('id = ?', $idUsuAdmin);
            $modelUsuario->update($data, $where);
        }
    }

}

