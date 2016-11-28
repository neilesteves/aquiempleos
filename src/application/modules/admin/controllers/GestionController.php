<?php

class Admin_GestionController extends App_Controller_Action_Admin
{
    protected $_messageSuccess = 'Actualización exitosa.';
    protected $_messageError   = 'Error al momento de guardar.';
    protected $_cache          = null;

    public function init()
    {
        parent::init();

        if ($this->_cache == null) {
            $this->_cache = Zend_Registry::get('cache');
        }

        //if ( Zend_Auth::getInstance()->hasIdentity()!= true ) {
        if (!$this->isAuth) {
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
        $this->view->navegapostulante = false;
        $this->view->menu_sel_side    = self::MENU_POST_SIDE_POSTULANTES;
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/micuenta.admin.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/gestion.admin.js')
        );
        $paginator                    = array();

        $formPostulante        = new Application_Form_AdminPostulante();
        $this->view->formAdmin = $formPostulante;

        $params     = $this->_getAllParams();
        $valid      = $formPostulante->isValid($params);
        $valuesPost = $formPostulante->getValues();
        if ($valid && ($valuesPost['nombres'] != null ||
            $valuesPost['apellidos'] != null ||
            $valuesPost['email'] != null ||
            $valuesPost['num_doc'] != null)
        ) {
            $sess                           = $this->getSession();
            $this->view->postulanteAdminUrl = $sess->postulanteAdminUrl       = $params;

            $this->view->navegapostulante = $this->entarNavegarComoPostulante($valuesPost['email'],
                $valuesPost['num_doc']);

            $this->view->col = $col             = $this->_getParam('col', '');
            $this->view->ord = $ord             = $this->_getParam('ord', 'DESC');
            $page            = $this->_getParam('page', 1);

            $modelPostulante       = new Application_Model_Postulante();
            $paginator             = $modelPostulante
                ->getPaginadorBusquedaPersonalizada(
                $valuesPost['nombres'], $valuesPost['apellidos'],
                $valuesPost['num_doc'], $valuesPost['email'], $col, $ord
            );
            $this->view->mostrando = "Mostrando "
                .$paginator->getItemCount($paginator->getItemsByPage($page))." de "
                .$paginator->getTotalItemCount();
            $paginator->setCurrentPageNumber($page);
            $this->view->pagina    = $paginator->getCurrentPageNumber();
        }
        $this->view->arrayBusqueda = $paginator;
    }

    public function avisosAction()
    {
        $this->view->menu_sel_side = self::MENU_POST_SIDE_AVISOS;
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/micuenta.admin.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/gestion.admin.js')
        );

        if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_CALLCENTER) {
            $this->_redirect('/admin/gestion');
        }

        $this->view->model = 'empresa';

        $paginator = array();

        $formAviso             = new Application_Form_AdminAviso();
        $this->view->formAviso = $formAviso;
        $params                = $this->_getAllParams();
        if (isset($params['tipobusq']) && $params['tipobusq'] == 1) {
            $formAviso->getElement('cod_ade')->setRequired();
        }
        $paramsAuxiliar = $params;



        $valid      = $formAviso->isValid($params);
        $valuesPost = $formAviso->getValues();



        if ($valid && ($valuesPost['url_id'] != null ||
            $valuesPost['razonsocial'] != null ||
            $valuesPost['num_ruc'] != null ||
            $valuesPost['fh_pub'] != null ||
            $valuesPost['cod_ade'] != '' ||
            $valuesPost['tipobusq'] != 0)
        ) {

//            var_dump( $valuesPost );
//            exit;

            $sess                      = $this->getSession();
            $this->view->avisoAdminUrl = $sess->avisoAdminUrl       = $params;

            if (isset($params['fh_pub'])) {
                $newFhPub = explode('-', $params['fh_pub']);
                $fhPub    = $newFhPub[0].'/'.$newFhPub[1].'/'.$newFhPub[2];
                $formAviso->setDefault('fh_pub', $fhPub);
            }
            $this->view->col = $col             = $this->_getParam('col', '');
            $this->view->ord = $ord             = $this->_getParam('ord', 'DESC');
            $page            = $this->_getParam('page', 1);
            $modelAw         = new Application_Model_AnuncioWeb();
            $fhPub           = $valuesPost['fh_pub'];
            if ($fhPub != null) {
                $fhPub = date(
                    'Y-m-d',
                    strtotime($valuesPost['fh_pub'] == null ? null : $valuesPost['fh_pub'])
                );
            } else {
                $fhPub = null;
            }
            $paginator             = $modelAw
                ->getPaginadorBusquedaPersonalizada(
                $valuesPost['url_id'], $valuesPost['razonsocial'],
                $valuesPost['num_ruc'], $valuesPost['cod_ade'],
                isset($valuesPost['tipobusq']) ? $valuesPost['tipobusq'] : '0',
                $fhPub, $col, $ord
            );
            $this->view->mostrando = "Mostrando "
                .$paginator->getItemCount($paginator->getItemsByPage($page))." de "
                .$paginator->getTotalItemCount();
            $paginator->setCurrentPageNumber($page);
            $this->view->pagina    = $paginator->getCurrentPageNumber();
        }

        if (isset($paramsAuxiliar['razonsocial']) && $paramsAuxiliar['razonsocial']
            != '') {
            $modelEmp                      = new Application_Model_Empresa();
            $arrayEmp                      = $modelEmp->getEmpresa($paramsAuxiliar['razonsocial']);
            $this->view->dataId            = $paramsAuxiliar['razonsocial'];
            $this->view->dataAuto          = $paramsAuxiliar['razonsocial'] = $arrayEmp['razonsocial'];
        }
        $formAviso->setDefaults($paramsAuxiliar);
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
                '/js/administrador/micuenta.admin.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/gestion.admin.js')
        );

        $params = $this->_getAllParams();

        $formEmpresa           = new Application_Form_AdminEmpresa();
        $this->view->formAdmin = $formEmpresa;

        $params = $this->_getAllParams();

        $paginator = array();

        $valid      = $formEmpresa->isValid($params);
        $valuesPost = $formEmpresa->getValues();
        if ($valid && ($valuesPost['razonsocial'] != '' || $valuesPost['num_ruc']
            != '')) {
            $sess                        = $this->getSession();
            $this->view->empresaAdminUrl = $sess->empresaAdminUrl       = $params;
            $this->view->col             = $col                         = $this->_getParam('col',
                '');
            $this->view->ord             = $ord                         = $this->_getParam('ord',
                'DESC');
            $page                        = $this->_getParam('page', 1);

            $modelAw               = new Application_Model_Empresa();
            $paginator             = $modelAw->getPaginadorBusquedaPersonalizada(
                $valuesPost['razonsocial'], $valuesPost['num_ruc'], $col, $ord
            );
            $this->view->mostrando = "Mostrando ".
                $paginator->getItemCount($paginator->getItemsByPage($page))." de ".
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
            $this->view->S(
                '/js/administrador/gestion.admin.js')
        );

        $idUsuario    = $this->auth['usuario']->id;
        $emailUsuario = $this->auth['usuario']->email;

        $formCambioClave = new Application_Form_CambioClave($idUsuario);
        $formCambioClave->validarPswd($emailUsuario, $idUsuario);

        if ($this->getRequest()->isPost()) {

            $allParams = $this->_getAllParams();

            $validClave = $formCambioClave->isValid($allParams);

            if ($validClave) {
                $valuesClave = $formCambioClave->getValues();
                try {

                    $db = $this->getAdapter();
                    $db->beginTransaction();

                    //Captura de los datos de usuario
                    $valuesClave['pswd'] = App_Auth_Adapter_AptitusDbTable::generatePassword($valuesClave['pswd']);
                    unset($valuesClave['pswd2']);
                    unset($valuesClave['oldpswd']);
                    unset($valuesClave['tok']);

                    $modelUsuario = new Application_Model_Usuario();
                    $where        = $modelUsuario->getAdapter()
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
            } else {
                $this->getMessenger()->error("La contraseña proporcionada no coincide con la actual");
            }
        }
        $this->view->formCambioClave = $formCambioClave;
    }

    public function bloquearPostulanteAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $params           = $this->_getAllParams();
        $data             = array();
        $arrIdPost        = explode('|', $params['idPost']);
        $params['idPost'] = $arrIdPost[0];
        // var_dump($arrIdPost);exit;
        $token            = $arrIdPost[1];
        $gp               = new Zend_Session_Namespace('gestionPostulantes');
        $clavegp          = 'gestionPostulantes'.$params['idPost'];

        if ($this->_request->isPost() && $token == $gp->$clavegp && !empty($token)) {

            $modelPostulante = new Application_Model_Postulante();
            $arrayPostulante = $modelPostulante->getPostulante($params['idPost']);
            $where           = $this->getAdapter()->quoteInto('id = ?',
                $arrayPostulante['id_usuario']);
            $data['activo']  = 0;
            $modelUsuario    = new Application_Model_Usuario();
            $val             = $modelUsuario->update($data, $where);


            $moPostulante = new Solr_SolrPostulante();
            $moPostulante->delete($arrayPostulante['id']);
            $this->getMessenger()->success($this->_messageSuccess);

            $this->_helper->mail->bloquearPostulante(
                array(
                    'to' => $arrayPostulante['email'],
                    'user' => $arrayPostulante['email'],
                    'fr' => date('Y-m-d H:i:s'),
                    'nombre' => ucwords($arrayPostulante['nombres']),
                )
            );
        } else {
            $this->getMessenger()->error('error');
        }
    }

    public function desbloquearPostulanteAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $params           = $this->_getAllParams();
        $data             = array();
        $arrIdPost        = explode('|', $params['idPost']);
        $params['idPost'] = $arrIdPost[0];
        $token            = $arrIdPost[1];
        $gp               = new Zend_Session_Namespace('gestionPostulantes');
        $clavegp          = 'gestionPostulantes'.$params['idPost'];

        if ($this->_request->isPost() && $token == $gp->$clavegp && !empty($token)) {

            $modelPostulante = new Application_Model_Postulante();
            $arrayPostulante = $modelPostulante->getPostulante($params['idPost']);
            $where           = $this->getAdapter()->quoteInto('id = ?',
                $arrayPostulante['id_usuario']);
            $data['activo']  = 1;
            $data['elog']    = 0;

            $modelUsuario    = new Application_Model_Usuario();
            $val             = $modelUsuario->update($data, $where);
//            $sc = new Solarium\Client($this->config->solr);
//            $moPostulante = new Solr_SolrAbstract($sc,'postulante');
//            $moPostulante->addPostulante($params['idPost']);
            $modelPostulante = new Solr_SolrPostulante();
            $modelPostulante->add($params['idPost']);
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
        $config = Zend_Registry::get('config');

        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
        $buscamasUrl         = $config->apis->buscamas->url;
        $buscamasPublishUrl  = $config->apis->buscamas->publishUrl;
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $params = $this->_getAllParams();
        $idEmp  = $params['idEmp'];
        $token  = (isset($params['tok']) ? $params['tok'] : false);

        $data        = array();
        $arrayUsuAdm = array();

        if ($this->_request->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            if (crypt($idEmp, $token) !== $token) {
                exit;
            }




            $modelUsuEmp = new Application_Model_UsuarioEmpresa();
            $arrayUsuEmp = $modelUsuEmp->getAdministradores($idEmp);

            foreach ($arrayUsuEmp as $data) {
                $arrayUsuAdm[] = $data['id_usuario'];
            }

            $where        = $this->getAdapter()->quoteInto('id in (?) ',
                $arrayUsuAdm);
            $modelUsuario = new Application_Model_Usuario();
            $dataUsuario  = array('fh_edicion' => date('Y-m-d H:i:s'), 'activo' => 0);
            $modelUsuario->update($dataUsuario, $where);

            $modelEmpresa = new Application_Model_Empresa();
            $arrayEmpresa = $modelEmpresa->getEmpresa($idEmp);

            $modelAnuncioWeb = new Application_Model_AnuncioWeb();
            $arrayAnuncioWeb = $modelAnuncioWeb->getAdapter()->fetchAll(
                $modelAnuncioWeb->getAvisosActivos(
                    $idEmp, '1', Application_Model_AnuncioWeb::ESTADO_PAGADO,
                    '0', '', ''
                )
            );


            if (!$arrayAnuncioWeb) {
                exit;
            }
            $helperAviso = $this->_helper->getHelper('Aviso');
            foreach ($arrayAnuncioWeb as $newAw) {

                $idAviso = $newAw['id'];

                $dataAw['online']          = '0';
                $dataAw['modificado_por']  = $this->auth['usuario']->id;
                $dataAw['fh_edicion']      = date('Y-m-d H:i:s');
                $dataAw['fh_aviso_baja']   = date('Y-m-d H:i:s');
                $dataAw['estado_anterior'] = $newAw['estado'];
                $dataAw['estado']          = Application_Model_AnuncioWeb::ESTADO_BANEADO;
                $where                     = $this->getAdapter()->quoteInto('id = ?',
                    $idAviso);
                $modelAnuncioWeb->update($dataAw, $where);

                $helperAviso->getSolarAviso()->DeleteAvisoSolr($idAviso);
                /*
                  $zl = new ZendLucene();
                  $zl->eliminarDocumentoAviso($idAviso);
                 */
                @$this->_cache->remove('AnuncioWeb_getAvisoInfoficha_'.$idAviso);
                @$this->_cache->remove('AnuncioWeb_getAvisoById_'.$idAviso);
                @$this->_cache->remove('AnuncioWeb_getAvisoInfoById_'.$idAviso);
                @$this->_cache->remove('anuncio_web_'.$newAw['id_anuncio_web']);
//                @$this->_cache->remove('AnuncioWeb_getAvisoRelacionadosnew_' . $idAviso);
//                @$this->_cache->remove('AnuncioWeb_getAvisosRelacionadosAuxiliar_' . $idAviso);
                @$this->_cache->remove('AnuncioWeb_getAvisoIdByUrl_'.$newAw['url_id']);
                @$this->_cache->remove('AnuncioWeb_getAvisoIdByCreado_'.$newAw['url_id']);
            }

            $this->_helper->mail->bloquearEmpresa(
                array(
                    'to' => $arrayEmpresa['email'],
                    'empresa' => $arrayEmpresa['nombrecomercial']
                )
            );
        }
    }

    public function desbloquearEmpresaAction()
    {
        $config = Zend_Registry::get('config');

        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
        $buscamasUrl         = $config->apis->buscamas->url;
        $buscamasPublishUrl  = $config->apis->buscamas->publishUrl;

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $params = $this->_getAllParams();
        $idEmp  = $params['idEmp'];
        $token  = (isset($params['tok']) ? $params['tok'] : null);

        $data        = array();
        $arrayUsuAdm = array();

        if ($this->_request->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            if (crypt($idEmp, $token) !== $token) {
                exit;
            }


            $modelUsuEmp = new Application_Model_UsuarioEmpresa();
            $arrayUsuEmp = $modelUsuEmp->getAdministradores($idEmp);

            foreach ($arrayUsuEmp as $data) {
                $arrayUsuAdm[] = $data['id_usuario'];
            }

            $where        = $this->getAdapter()->quoteInto('id in (?) ',
                $arrayUsuAdm);
            $modelUsuario = new Application_Model_Usuario();
            $dataUsuario  = array('fh_edicion' => date('Y-m-d H:i:s'), 'activo' => 1);
            $modelUsuario->update($dataUsuario, $where);

            $modelEmpresa = new Application_Model_Empresa();
            $arrayEmpresa = $modelEmpresa->getEmpresa($idEmp);

            $modelAnuncioWeb = new Application_Model_AnuncioWeb();
            $arrayAnuncioWeb = $modelAnuncioWeb->
                getAvisoBaneadoXEmpresa($idEmp,
                Application_Model_AnuncioWeb::ESTADO_BANEADO);

            $helperAviso = new Solr_SolrAviso();
            if (!$arrayAnuncioWeb) {
                exit;
            }
            foreach ($arrayAnuncioWeb as $newAw) {
                $idAviso = $newAw['id'];

                //validación de Fecha
                if ($newAw['fpublicacion'] != null) {
                    $list          = explode('/', $newAw['fpublicacion']);
                    $arrayFechaVen = $list[2].$list[1].$list[0];

                    $arrayFhVen = strtotime($list[0].'/'.$list[1].'/'.$list[2]);
                    $arrayfhhoy = strtotime(date('d/m/Y'));

//                    $listDos = explode('/', date('d/m/Y'));
//                    $arrayFechaAct = $listDos[2].$listDos[1].$listDos[0];

                    if ((int) $arrayFhVen >= (int) $arrayfhhoy) {
                        $dataAw['online'] = '1';
                    } else {
                        $dataAw['online'] = '0';
                    }
                } else {
                    $dataAw['online'] = '0';
                }

                $dataAw['modificado_por']  = $this->auth['usuario']->id;
                $dataAw['fh_edicion']      = date('Y-m-d H:i:s');
                $dataAw['fh_aviso_baja']   = null;
                $dataAw['estado']          = $newAw['estado_anterior'];
                $dataAw['estado_anterior'] = null;
                $where                     = $this->getAdapter()->quoteInto('id = ?',
                    $idAviso);
                $modelAnuncioWeb->update($dataAw, $where);
                if ($dataAw['online'] == 1)
                        $helperAviso->addAvisoSolr($idAviso);
                //Actualizar índices a Buscamas
                //exec("curl -X POST -d 'api_key=".$buscamasConsumerKey."&nid=".$newAw['id']."&site=".$buscamasUrl."' ".$buscamasPublishUrl);

                /*
                  $zl = new ZendLucene();
                  $zl->agregarNuevoDocumentoAviso($idAviso);
                 */
                @$this->_cache->remove('AnuncioWeb_getAvisoInfoficha_'.$idAviso);
                @$this->_cache->remove('AnuncioWeb_getAvisoById_'.$idAviso);
                @$this->_cache->remove('AnuncioWeb_getAvisoInfoById_'.$idAviso);
                @$this->_cache->remove('anuncio_web_'.$newAw['url_id']);
//                @$this->_cache->remove('AnuncioWeb_getAvisoRelacionadosnew_' . $idAviso);
//                @$this->_cache->remove('AnuncioWeb_getAvisosRelacionadosAuxiliar_' . $idAviso);
                @$this->_cache->remove('AnuncioWeb_getAvisoIdByUrl_'.$newAw['url_id']);
                @$this->_cache->remove('AnuncioWeb_getAvisoIdByCreado_'.$newAw['url_id']);
            }
            $this->_helper->mail->desbloquearEmpresa(
                array(
                    'to' => $arrayEmpresa['email'],
                    'empresa' => $arrayEmpresa['nombrecomercial']
                )
            );
        }
    }

    public function bloquearAvisoAction()
    {
        $config = Zend_Registry::get('config');

        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
        $buscamasUrl         = $config->apis->buscamas->url;
        $buscamasPublishUrl  = $config->apis->buscamas->publishUrl;

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idAviso = $this->_getParam('idEmp', null);

        $data = array();

        if ($this->_request->isPost()) {

            $modelAw = new Application_Model_AnuncioWeb();
            $arrayAw = $modelAw->getAvisoById($idAviso);

            $data['online']          = '0';
            $data['modificado_por']  = $this->auth['usuario']->id;
            $data['fh_edicion']      = date('Y-m-d H:i:s');
            $data['fh_aviso_baja']   = date('Y-m-d H:i:s');
            $data['estado_anterior'] = $arrayAw['estado'];
            $data['estado']          = Application_Model_AnuncioWeb::ESTADO_BANEADO;

            $where = $this->getAdapter()->quoteInto('id = ?', $idAviso);
            $modelAw->update($data, $where);

            $helperAviso = new Solr_SolrAviso();
            $helperAviso->delete($idAviso);

            //exec("curl -X POST -d 'api_key=".$buscamasConsumerKey."&nid=".$idAviso."&site=".$buscamasUrl."' ".$buscamasPublishUrl);

            $modelEmpresa                  = new Application_Model_Empresa();
            $arrayEmpresa                  = $modelEmpresa->getEmpresa($arrayAw['id_empresa']);
            $AnuncioWeb_getAvisoInfoficha_ = 'AnuncioWeb_getAvisoInfoficha_'.$idAviso;
            if ($this->_cache->test($AnuncioWeb_getAvisoInfoficha_)) {
                $this->_cache->remove($AnuncioWeb_getAvisoInfoficha_);
            }
            $AnuncioWeb_getAvisoById = 'AnuncioWeb_getAvisoById_'.$idAviso;
            if ($this->_cache->test($AnuncioWeb_getAvisoById)) {
                $this->_cache->remove($AnuncioWeb_getAvisoById);
            }

            $AnuncioWeb_getAvisoInfoById = 'AnuncioWeb_getAvisoInfoById_'.$idAviso;
            if ($this->_cache->test($AnuncioWeb_getAvisoInfoById)) {
                $this->_cache->remove($AnuncioWeb_getAvisoInfoById);
            }


            $AnuncioWeb_getAvisoInfo = 'AnuncioWeb_getAvisoInfo_'.$arrayAw['url_id'];
            if ($this->_cache->test($AnuncioWeb_getAvisoInfo)) {
                $this->_cache->remove($AnuncioWeb_getAvisoInfo);
            }

            $anuncio_web = 'anuncio_web_'.$arrayAw['url_id'];
            if ($this->_cache->test($anuncio_web)) {
                $this->_cache->remove($anuncio_web);
            }
            $Empresa_getEmpresaHome = 'Empresa_getEmpresaHome';
            if ($this->_cache->test($Empresa_getEmpresaHome)) {
                $this->_cache->remove($Empresa_getEmpresaHome);
            }

            $AnuncioWeb_getAvisoIdByUrl = 'AnuncioWeb_getAvisoIdByUrl_';
            if ($this->_cache->test($AnuncioWeb_getAvisoIdByUrl)) {
                $this->_cache->remove($AnuncioWeb_getAvisoIdByUrl);
            }

            $AnuncioWeb_getAvisoIdByUrl = 'AnuncioWeb_getAvisoIdByUrl_'.$arrayAw['url_id'];
            if ($this->_cache->test($AnuncioWeb_getAvisoIdByUrl)) {
                $this->_cache->remove($AnuncioWeb_getAvisoIdByUrl);
            }
            $AnuncioWeb_getAvisoIdByCreado = 'AnuncioWeb_getAvisoIdByCreado_'.$arrayAw['url_id'];
            if ($this->_cache->test($AnuncioWeb_getAvisoIdByCreado)) {
                $this->_cache->remove($AnuncioWeb_getAvisoIdByCreado);
            }
            $this->_helper->mail->bloquearAviso(
                array(
                    'to' => $arrayEmpresa['email'],
                    'empresa' => $arrayAw['nombre_comercial'],
                    'puesto' => $arrayAw['tipo_puesto']
                )
            );
        }
    }

    public function desbloquearAvisoAction()
    {
        $config = Zend_Registry::get('config');

        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
        $buscamasUrl         = $config->apis->buscamas->url;
        $buscamasPublishUrl  = $config->apis->buscamas->publishUrl;

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idAviso = $this->_getParam('idEmp', null);

        $data = array();

        if ($this->_request->isPost()) {

            $modelAw = new Application_Model_AnuncioWeb();
            $arrayAw = $modelAw->getAvisoById($idAviso);

            $modelEmpresa = new Application_Model_Empresa();
            $arrayEmpresa = $modelEmpresa->getEmpresa($arrayAw['id_empresa']);

            if ($arrayEmpresa['activo'] != 0) {
                if ($arrayAw['fpublicacion'] != null) {
                    $list          = explode('/', $arrayAw['fpublicacion']);
                    $arrayFechaVen = $list[2].$list[1].$list[0];

                    $listDos       = explode('/', date('d/m/Y'));
                    $arrayFechaAct = $listDos[2].$listDos[1].$listDos[0];

                    if ((int) $arrayFechaVen >= (int) $arrayFechaAct) {
                        $data['online'] = '1';
                    } else {
                        $data['online'] = '0';
                    }
                } else {
                    $data['online'] = '0';
                }


                $data['modificado_por']  = $this->auth['usuario']->id;
                $data['fh_edicion']      = date('Y-m-d H:i:s');
                $data['fh_aviso_baja']   = null;
                $data['estado']          = $arrayAw['estado_anterior'];
                $data['estado_anterior'] = null;

                $where = $this->getAdapter()->quoteInto('id = ?', $idAviso);
                $modelAw->update($data, $where);

                $helperAviso = $this->_helper->getHelper('Aviso');
                $helperAviso->_SolrAviso->addAvisoSolr($idAviso);

                //exec("curl -X POST -d 'api_key=".$buscamasConsumerKey."&nid=".$idAviso."&site=".$buscamasUrl."' ".$buscamasPublishUrl);

                $this->_cache->remove('AnuncioWeb_getAvisoById_'.$idAviso);
                $this->_cache->remove('AnuncioWeb_getAvisoInfoById_'.$idAviso);
                $this->_cache->remove('AnuncioWeb_getAvisoInfo_'.$arrayAw['url_id']);
                $this->_cache->remove('anuncio_web_'.$arrayAw['url_id']);
                @$this->_cache->remove('Empresa_getEmpresaHome_');
                $this->_helper->mail->desbloquearAviso(
                    array(
                        'to' => $arrayEmpresa['email'],
                        'empresa' => $arrayAw['nombre_comercial'],
                        'puesto' => $arrayAw['tipo_puesto']
                    )
                );
            } else {
                echo 'Empresa Bloqueada';
            }
        }
    }
    /* Acción que muestra el aviso en el HOME */

    public function mostrarAvisoPortadaAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $numDestacados = $this->config->avisosportada->destacados;
        $idAviso       = $this->_getParam('idAviso', null);
        $data          = array();

        if ($this->getRequest()->isPost()) {

            $modelAw   = new Application_Model_AnuncioWeb();
            $destacado = Application_Model_AnuncioWeb::DESTACADO;

            //Obtiene cuantos avisos en el home se tiene
            $validaUpdate = $modelAw->obtieneNumAvisosHome($idAviso);

            if ($validaUpdate) {
                $data['destacado'] = $destacado;
                $where             = $this->getAdapter()->quoteInto('id = ?',
                    $idAviso);
                $modelAw->update($data, $where);

                $this->_cache->remove('AnuncioWeb_getUltimosAvisosDestacados');

                //Enviar respuesta en el caso ya tenga 3 avisos en la portada
                echo Zend_Json::encode(array('success' => 1, 'msg' => "El aviso ya se encuentra en la portada."));
            } else {
                echo Zend_Json::encode(array('success' => 0, 'msg' => "El número máximo de avisos "
                    ."en el HOME es ".$numDestacados.". No se pudo mostrar el aviso en el HOME."));
            }
        }
    }
    /* Acción que quita el aviso del HOME */

    public function quitarAvisoPortadaAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idAviso = $this->_getParam('idAviso', null);
        $data    = array();

        if ($this->getRequest()->isPost()) {

            $modelAw     = new Application_Model_AnuncioWeb();
            $dataAW      = $modelAw->find($idAviso)->toArray();
            $noDestacado = Application_Model_AnuncioWeb::NO_DESTACADO;

            //Verifica si estuvo destacado
            $valorDest = $dataAW[0]['destacado'];
            if ($valorDest == $noDestacado) {

                echo Zend_Json::encode(array('success' => 0, 'msg' => "Aviso actualizado."));
            } else {

                $data['destacado'] = $noDestacado;
                $where             = $this->getAdapter()->quoteInto('id = ?',
                    $idAviso);
                $modelAw->update($data, $where);
                $this->_cache->remove('AnuncioWeb_getUltimosAvisosDestacados');

                //Enviar respuesta en el caso ya tenga 3 avisos en la portada
                echo Zend_Json::encode(array('success' => 1, 'msg' => "El aviso ya se retiró de la portada."));
            }
        }
    }

    public function agregarMensajeAction()
    {
        $this->_helper->layout->disableLayout();

        $idAdmin     = $this->auth['usuario']->id;
        $formMensaje = new Application_Form_Mensajes();
        $modelMen    = new Application_Model_Mensaje();

        $params                   = $this->_getAllParams();
        $modelPostulante          = new Application_Model_Postulante();
        $this->view->idPostulante = $idPostulante             = $params['idPost'];
        $arrayPostulante          = $modelPostulante->getPostulante($idPostulante);
        $idUsuario                = $arrayPostulante['idusuario'];
        if ($this->_request->isPost()) {
            $mensaje  = $this->_getAllParams();
            $validMen = $formMensaje->isValid($mensaje);
            if ($validMen) {
                $valuesMensaje = $formMensaje->getValues($mensaje);
                if (trim($valuesMensaje['cuerpo']) != "") {

                    $fecha                         = date('Y-m-d H:i:s');
                    $valuesMensaje['de']           = $idAdmin;
                    $valuesMensaje['para']         = $idUsuario;
                    $valuesMensaje['fh']           = $fecha;
                    $tipomensaje                   = $this->_getParam("tipo_mensaje");
                    $valuesMensaje['tipo_mensaje'] = Application_Model_Mensaje::ESTADO_MENSAJE;

                    $valuesMensaje['leido']        = 0;
                    $valuesMensaje['respondido']   = 0;
                    $valuesMensaje['notificacion'] = 1;
                    unset($valuesMensaje['id_mensaje']);
                    unset($valuesMensaje['token']);

                    $a = $modelMen->insert($valuesMensaje);

                    $nurl = base64_encode("/notificaciones/");
                    $this->_helper->mail->mensajeAdminPostulante(
                        array(
                            'to' => $arrayPostulante['email'],
                            'email' => $arrayPostulante['email'],
                            'nombre' => ucwords($arrayPostulante['nombres']),
                            //'url' => $this->config->app->siteUrl."/notificaciones/index/url/".$nurl
                            'url' => $this->config->app->siteUrl."/notificaciones/index/mensaje/".$a
                        )
                    );
                    //actualizamos postulaciones
                    $this->_helper->Mensaje->actualizarCantMsjsNotificacion(
                        $idUsuario
                    );
                }
                exit;
            }
        }
        $this->view->form = $formMensaje;
    }

    public function avisosPreferencialesAction()
    {
        if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_CALLCENTER
            ||
            //$this->auth['usuario']->rol ==Application_Form_Login::ROL_ADMIN_SOPORTE ||
            $this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_MODERADOR) {
            $this->_redirect('/admin/gestion');
        }
        $this->view->menu_sel_side = self::MENU_POST_SIDE_AVISOSPREFERENCIALES;
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/micuenta.admin.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/gestion.admin.js')
        );
        $paginator                 = array();
        $formAvisosPref            = new Application_Form_AdminAvisoPreferencial();
        $this->view->formAvisoPref = $formAvisosPref;



        $params = $this->_getAllParams();
        if (isset($params['tipobusq']) && $params['tipobusq'] == 1) {
            $formAvisosPref->getElement('cod_ade')->setRequired();
        }
        $valid      = $formAvisosPref->isValid($params);
        $valuesPost = $formAvisosPref->getValues();
        if ($valid && ($valuesPost['nom_puesto'] != '' ||
            $valuesPost['num_ruc'] != '' ||
            $valuesPost['fh_pub'] != '' ||
            $valuesPost['origen'] != '' ||
            $valuesPost['cod_ade'] != '' ||
            $valuesPost['tipobusq'] != 0 )
        ) {

//            print_r($valuesPost);
//            exit;
            $sess                                  = $this->getSession();
            $sess->avisosClonados                  = null;
            $this->view->avisoPreferencialAdminUrl = $sess->avisoPreferencialAdminUrl
                = $params;

            if (isset($params['fh_pub']) && $params['fh_pub'] != '') {
                $newFhPub = explode('-', $params['fh_pub']);
                $fhPub    = $newFhPub[0].'/'.$newFhPub[1].'/'.$newFhPub[2];
                $formAvisosPref->setDefault('fh_pub', $fhPub);
            }
            $this->view->col = $col             = $this->_getParam('col', '');
            $this->view->ord = $ord             = $this->_getParam('ord', 'DESC');
            $page            = $this->_getParam('page', 1);

            $modelAw = new Application_Model_AnuncioWeb();
            $fhPub   = $valuesPost['fh_pub'];

            if ($fhPub != null) {
                $fhPub = date(
                    'Y-m-d',
                    strtotime($valuesPost['fh_pub'] == null ? null : $valuesPost['fh_pub'])
                );
            } else {
                $fhPub = null;
            }

            $paginator             = $modelAw
                ->getPaginadorBusquedaPersonalizadaPreferencial(
                $valuesPost['nom_puesto'], $valuesPost['num_ruc'], $fhPub,
                $valuesPost['origen'], $valuesPost['cod_ade'],
                isset($valuesPost['tipobusq']) ? $valuesPost['tipobusq'] : '0',
                $col, $ord
            );
            $this->view->mostrando = "Mostrando "
                .$paginator->getItemCount($paginator->getItemsByPage($page))." de "
                .$paginator->getTotalItemCount();
            $paginator->setCurrentPageNumber($page);
            $this->view->pagina    = $paginator->getCurrentPageNumber();
        }

        $this->view->arrayBusqueda = $paginator;
    }

    public function callcenterAction()
    {
        if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_MASTER
            ||
            $this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_SOPORTE
            ||
            $this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_CALLCENTER) {

            $this->view->headScript()->appendFile($this->view->S('/js/administrador/gestion.callcenter.js'));
            $this->view->menu_sel_side  = self::MENU_POST_SIDE_CALLCENTER;
            $formCallcenter             = new Application_Form_AdminCallcenter();
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

        $request = $this->getRequest();
        if ($request->isPost() && $request->isXmlHttpRequest()) ;
        else {
            exit(0);
        }

        $allParams              = $this->_getAllParams();
        $txtemail               = $allParams["txtemail"];
        $numTipo                = $allParams['numTipo'];
        $ruc                    = $allParams['valorTipo'];
        $token                  = $allParams['token'];
        $this->view->navegarUsu = false;
        if (isset($txtemail) && $this->_hash->isValid($token)) {
            if ($numTipo == 8) {
                //$ruc = '10'.$ruc;
            }
            $empresa = new Application_Model_Empresa();
            $cliente = $empresa->getEmpresaByEmail($txtemail, $ruc);
            if ($cliente != null) {
                $this->view->cliente        = $cliente;
                $llamadas                   = new Application_Model_LlamadasCallcenter();
                $this->view->listaContactos = $llamadas->getAllByEmpresa($cliente["idempresa"]);
//                $this->getLogger()->info(
//                    "Intento de búsqueda Encontrada de Email en Callcenter: ".$txtemail
//                );
                $this->view->resultBusqueda = true;
                $session->empresaBusqueda   = $cliente;

                $usuario = $this->entrarNavegarComoUsuario($cliente['id']);

                $this->view->navegarUsu = $usuario;
            } else {
                $this->salirNavegarComoUsuario();

                $this->view->resultBusqueda = false;
//                $this->getLogger()->info(
//                    "Intento de búsqueda Fallida de Email en Callcenter: ".$txtemail
//                );
            }
        }
    }

    private function entrarNavegarComoUsuario($idEmpresa = null)
    {
        if ($idEmpresa) {
            $mUsuario = new Application_Model_Usuario();
            return $mUsuario->navegarComoUsuario($idEmpresa);
        }
    }

    /**
     * Funcion que te permite navegar con una cuenta de postulante
     * @param int $idpostulante
     * @return bolean true
     */
    private function entarNavegarComoPostulante($email, $doc_numero)
    {
        $mUsuario = new Application_Model_Usuario();

        if ($email) {
            return $mUsuario->navegarComoPostulante($email, $doc_numero);
        }
        if ($doc_numero) {
            return $mUsuario->navegarComoPostulante($email, $doc_numero);
        }
        return false;
    }

    private function salirNavegarComoUsuario()
    {
        $sessionUpdateCV = new Zend_Session_Namespace('updateCV');
        unset($sessionUpdateCV->urlAviso);
        unset($sessionUpdateCV->urlAviso);
        unset($sessionUpdateCV->tipo);

        $sesionRUC = new Zend_Session_Namespace('pago_ruc');
        unset($sesionRUC->ente_ruc);
        unset($sesionRUC->Tip_Doc);
        unset($sesionRUC->Num_Doc);
        unset($sesionRUC->RznSoc_Nombre);
        unset($sesionRUC->RznCom);
        unset($sesionRUC->Telf);
        unset($sesionRUC->Tip_Calle);
        unset($sesionRUC->Nom_Calle);
        unset($sesionRUC->idUser);
        unset($sesionRUC->compra);

        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::forgetMe();
    }

    public function llamadaClienteAction()
    {
        $this->_helper->layout()->disableLayout();
        $llamadas     = new Application_Model_LlamadasCallcenter();
        $idEmpresa    = $this->_getParam("idempresa");
        $idUsuario    = $this->auth["usuario"]->id;
        $fecha        = new DateTime("now");
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
        if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_MASTER
            ||
            $this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_SOPORTE
        ) {

            $this->view->headScript()->appendFile(
                $this->view->S(
                    '/js/administrador/admin.users.js')
            );
            $this->view->menu_sel_side = self::MENU_POST_SIDE_USUARIOS;

            $allParams = $this->_getAllParams();

            $sess                     = $this->getSession();
            $this->view->usuarioAdmin = $sess->usuarioAdmin       = $allParams;

            $this->view->col = $col             = $this->_getParam('col', '');
            $this->view->ord = $ord             = $this->_getParam('ord', 'DESC');
            $page            = $this->_getParam('page', 1);

            $modelUsuario = new Application_Model_Usuario();

            $paginator             = $modelUsuario->getPaginadorBusquedaAdministrador($col,
                $ord);
            $this->view->mostrando = "Mostrando "
                .$paginator->getItemCount($paginator->getItemsByPage($page))." de "
                .$paginator->getTotalItemCount();
            $paginator->setCurrentPageNumber($page);
            $this->view->pagina    = $paginator->getCurrentPageNumber();

            $this->view->arrayBusqueda = $paginator;
        } else {
            $this->_redirect('/admin/gestion');
        }
    }

    public function nuevoUsuarioAdminAction()
    {
        $this->_helper->layout->disableLayout();
        $err                    = 0;
        $this->view->action     = $this->_request->getActionName();
        $this->view->controller = $this->_request->getControllerName();

        $allParams       = $this->_getAllParams();
        $filter          = new Zend_Filter_StripTags;
        foreach ($allParams as $key => $value)
            $allParams[$key] = $filter->filter($value);

        if (isset($allParams['idUsuAdmin'])) {
            $this->view->idUsuAdmin = $idUsuAdmin             = $allParams['idUsuAdmin'];
        } else {
            $this->view->idUsuAdmin = $idUsuAdmin             = null;
        }

        $formUsuAdmin = new Application_Form_AdminUsuario();
        $formUsuario  = new Application_Form_Paso1Usuario($idUsuAdmin);
        $formUsuario->removeElement('auth_token');
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

            foreach ($data as $key => $value)
                $data[$key] = $filter->filter($value);

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
                    $db           = $this->getAdapter();
                    $db->beginTransaction();
                    $arrayUsuario = $formUsuario->getValues();
                    $arrayValues  = $formUsuAdmin->getValues();

                    $arrayValues['email'] = $arrayUsuario['email'];
                    if ($arrayUsuario['pswd'] != '') {
                        $arrayValues['pswd'] = App_Auth_Adapter_AptitusDbTable::generatePassword(
                                $arrayUsuario['pswd']
                        );
                    }
                    $arrayValues['salt']         = '';
                    $arrayValues['ultimo_login'] = date('Y-m-d H:i:s');
                    $arrayValues['ip']           = $this->getRequest()->getServer('REMOTE_ADDR');
                    unset($arrayUsuario['pswd2']);
                    unset($arrayValues['tok']);

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

        $this->view->error        = $err;
        $this->view->formUsuAdmin = $formUsuAdmin;
        $this->view->formUsuario  = $formUsuario;
    }

    public function bloquearUsuarioAdminAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idUsuAdmin = $this->_getParam('idUsuAdm', null);
        $token      = $this->_getParam('tok', null);

        $data = array();

        if ($this->_request->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            if (crypt($idUsuAdmin, $token) !== $token) {
                exit;
            }

            $modelUsuario = new Application_Model_Usuario();

            $data['activo']         = 0;
            $data['fh_edicion']     = date('Y-m-d H:i:s');
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
        $token      = $this->_getParam('tok', null);
        $data       = array();

        if ($this->_request->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            if (crypt($idUsuAdmin, $token) !== $token) {
                exit;
            }

            $modelUsuario = new Application_Model_Usuario();

            $data['activo']         = 1;
            $data['fh_edicion']     = date('Y-m-d H:i:s');
            $data['modificado_por'] = $this->auth['usuario']->id;

            $where = $this->getAdapter()->quoteInto('id = ?', $idUsuAdmin);
            $modelUsuario->update($data, $where);
        }
    }

    public function testimoniosAction()
    {
        if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_CALLCENTER
            ||
            $this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_TESTIMONIOS) {
            $this->_redirect('/admin/gestion');
        }
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/gestion.testimonio.js')
        );

        $this->view->maxTes = $this->config->testimonios->cantidad->testimonio;

        $this->view->menu_sel_side = self::MENU_POST_SIDE_TESTIMONIOS;

        $formBuscarTestimonio             = new Application_Form_BuscarTestimonio();
        $this->view->formBuscarTestimonio = $formBuscarTestimonio;

        $params    = $this->_getAllParams();
        //$validBuscarTestimonio = $formBuscarTestimonio->isValid($params);
        //--Seccion del Listado *************
        $paginator = array();

        //$params = $this->_getAllParams();
        $valid      = $formBuscarTestimonio->isValid($params);
        $valuesPost = $formBuscarTestimonio->getValues();
        if ($valid) {
            $sess                        = $this->getSession();
            $this->view->empresaAdminUrl = $sess->empresaAdminUrl       = $params;

            $this->view->col = $col             = $this->_getParam('col', '');
            $this->view->ord = $ord             = $this->_getParam('ord', 'ASC');
            $page            = $this->_getParam('page', 1);

            $modelAw   = new Application_Model_Testimonio();
            $paginator = $modelAw->getPaginadorBusquedaPersonalizada(
                strtolower($valuesPost['empresa']),
                strtolower($valuesPost['referente']), $col, $ord
            );

            $this->view->mostrando = "Mostrando ".
                $paginator->getItemCount($paginator->getItemsByPage($page))." de ".
                $paginator->getTotalItemCount();
            $paginator->setCurrentPageNumber($page);
            $this->view->pagina    = $paginator->getCurrentPageNumber();
        }
        $verifTestimoniosActivos             = $modelAw->getTestimoniosActivos();
        $this->view->verifTestimoniosActivos = $verifTestimoniosActivos;
        $this->view->arrayBusqueda           = $paginator;
    }

    public function empresasPortadaAction()
    {

        if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_CALLCENTER
            ||
            $this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_TESTIMONIOS) {
            $this->_redirect('/admin/gestion');
        }

        $this->view->menu_sel_side = self::MENU_POST_SIDE_EMPRESASPORTADA;

        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/gestion.empresa.portada.js')
        );

        $empresaModel = new Application_Model_Empresa;

        $params                = $this->_getAllParams();
        $formEmpresa           = new Application_Form_AdminEmpresa();
        $this->view->formAdmin = $formEmpresa;

        $paginator = array();

        $valid      = $formEmpresa->isValid($params);
        $valuesPost = $formEmpresa->getValues();
        if ($valid) {
            $sess                        = $this->getSession();
            $this->view->empresaAdminUrl = $sess->empresaAdminUrl       = $params;
            $this->view->col             = $col                         = $this->_getParam('col',
                '');
            $this->view->ord             = $ord                         = $this->_getParam('ord',
                'DESC');
            $page                        = $this->_getParam('page', 1);

            $paginator = $empresaModel->getPaginadorEmpresasTCN(
                $valuesPost['razonsocial'], $valuesPost['num_ruc']
            );

            $this->view->mostrando = "Mostrando ".
                $paginator->getItemCount($paginator->getItemsByPage($page))." de ".
                $paginator->getTotalItemCount();
            $paginator->setCurrentPageNumber($page);
            $this->view->pagina    = $paginator->getCurrentPageNumber();
        }
        $this->view->arrayBusqueda = $paginator;
    }

    public function agregarPortadaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $empresaId = $this->_getParam('idEmpresa');

        $objEmpresaPortada = new Application_Model_Empresa();
        $result            = $objEmpresaPortada->agregarEmpresaPortada($empresaId);
        if ($result) {
            $objEmpresaPortada->getEmpresasPortadas(false);
        }

        $this->_cache->remove('Empresa_getCompanyWithMembresia');
    }

    public function quitarPortadaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);


        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            $empresaId = $this->_getParam('idEmpresa');
            $tok       = $this->_getParam('tok');

            if ($empresaId && $tok) {

                if (crypt($empresaId, $tok) === $tok) {

                    $objEmpresaPortada = new Application_Model_Empresa();
                    $result            = $objEmpresaPortada->quitarEmpresaPortada($empresaId);
                    if ($result) {
                        $objEmpresaPortada->getEmpresasPortadas(false);
                    }

                    $this->_cache->remove('Empresa_getCompanyWithMembresia');
                }
            }
        }
    }

    public function navegarComoUsuarioAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $response = $this->getRequest();
        if ($response->isPost() && $response->isXmlHttpRequest()) {

            $tok       = $this->_getParam('token', null);
            $arTok     = explode('-', $tok);
            $idEmpresa = (int) base64_decode($arTok[1]);

            $res = array(
                'status' => 0,
                'message' => 'Por favor vuelva ha intentarlo.',
                'token' => $this->_hash
            );

            if ($idEmpresa && $this->_hash->isValid($arTok[0])) {
                $mUsuario = new Application_Model_Usuario();
                $mUsuario->navegarComoUsuario($idEmpresa);
                $res      = array(
                    'status' => 1,
                );
            }

            echo $this->_response->appendBody(Zend_Json::encode($res));
        }

        exit(0);
    }

    public function getCompanyTcnAction()
    {

        $this->_helper->layout->disableLayout();
        $err = 0;

        $idEmp              = $this->_getParam('id_empresa');
        $empresaModel       = new Application_Model_Empresa();
        $dataTCN            = $empresaModel->getInfoCompanyTCN($idEmp);
        $oldPortada         = $dataTCN['portada'];
        $formEmpresaPortada = new Application_Form_EmpresaTCNPortada(true);
        $formEmpresaPortada->isValid($dataTCN);

        if ($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();

            //Validar que no exista la misma prioridad
            $newPortada          = $postData['portada'];
            $prioridad           = $postData['prioridad_home'];
            $dataValidaPrioridad = $empresaModel->validaPrioridadEmpresaTCN($idEmp,
                $prioridad);

            $dataEmpHome     = $empresaModel->obtieneNumEmpresasTCN();
            $numEmpresasHome = count($dataEmpHome);
            $limite          = $this->config->empresaTcn->numEmpresa;

            $domainMod    = strtolower(substr($postData['url_tcn'], 0, 3));
            $domainModDos = strtolower(substr($postData['url_tcn'], 0, 7));

            if ($domainMod != 'www' && $domainModDos != Application_Form_Paso1Postulante::$_defaultWebsite) {
                $postData['url_tcn'] = Application_Form_Paso1Postulante::$_defaultWebsite.$postData['url_tcn'];
            }

            if (!empty($dataValidaPrioridad)) {
                $err = 3;
            } else {
                //Solo 20 en portada según config
                if ($postData['portada'] == 1 && $numEmpresasHome == $limite && $oldPortada
                    <> $newPortada) {
                    $err = 4;
                } else {
                    if (empty($postData['url_tcn']) || empty($postData['prioridad_home'])) {
                        $err = 2;
                    } else {
                        $postData['url_tcn'] = str_replace('http://', "",
                            $postData['url_tcn']);
                        $postData['url_tcn'] = str_replace('https://', "",
                            $postData['url_tcn']);
                        $postData['url_tcn'] = "http://".$postData['url_tcn'];
                        $empresaModel->actualizaInfoTCN($postData);
                        $this->_cache->remove('Empresa_getCompanyWithMembresia');
                        $err                 = 1;
                    }
                }
            }
        }

        $this->view->formCompanyTCN = $formEmpresaPortada;
        $this->view->error          = $err;
    }

    public function activarLookAndFeelAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $idEmpresa = $this->_getParam('idEmp', null);
        $token     = $this->_getParam('tok', null);

        if ($token && !empty($idEmpresa)) {

            $empresa = new Application_Model_Empresa();
            if ($empresa->cambioEstadoLookAndFeel($idEmpresa,
                    Application_Model_EmpresaLookAndFeel::PUBLICADO)) {
                $data['status']  = 1;
                $data['message'] = 'Se activó el Look&Feel de la empresa';
            } else {
                $data['status']  = 0;
                $data['message'] = 'Por favor vuelva a intentarlo';
            }
        } else {
            $data['status']  = 0;
            $data['message'] = 'Parámetros incorrectos';
        }
        $this->_response->appendBody(json_encode($data));
    }

    public function desactivarLookAndFeelAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idEmpresa = $this->_getParam('idEmp', null);
        $token     = $this->_getParam('tok', null);

        if ($token && !empty($idEmpresa)) {
            $empresa = new Application_Model_Empresa();
            if ($empresa->cambioEstadoLookAndFeel($idEmpresa,
                    Application_Model_EmpresaLookAndFeel::BORRADOR)) {
                $data['status']  = 1;
                $data['message'] = 'Se desactivó el Look&Feel de la empresa';
            } else {
                $data['status']  = 0;
                $data['message'] = 'Por favor vuelva a intentarlo';
            }
        } else {
            $data['status']  = 0;
            $data['message'] = 'Parámetros incorrectos';
        }
        $this->_response->appendBody(json_encode($data));
    }

    public function destacarAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idAviso         = $this->_getParam('idAviso', null);
        $token           = $this->_getParam('tok', null);
        $tipoDestaque    = $this->_getParam('tipo_destaque', null);
        $data            = array();
        $data['status']  = 0;
        $data['message'] = 'Parámetros incorrectos';
        if ($this->_hash->isValid($token)) {
            if (!empty($idAviso)) {
                $data    = array();
                $id      = $idAviso;
                $ModelAw = new Application_Model_AnuncioWeb();

                //$ModelAw->getAvisoInfoficha($idAviso);
                if ($tipoDestaque == 1) {
                    $fecha           = date('Y-m-d');
                    $nuevafecha      = strtotime('+1 month', strtotime($fecha));
                    $nuevafecha      = date('Y-m-d', $nuevafecha);
                    $databd          = array(
                        'prioridad_ndias_busqueda' => 30,
                        'prioridad' => 1,
                        'destacado' => 1,
                        'prioridad_de_tipo' => 'web',
                          'medio_pago'=>'destaque',
                        'fh_vencimiento_prioridad' => $nuevafecha,
                        'proceso_activo' => 1,
                    );
                    $data['status']  = 1;
                    $data['message'] = 'Se dio un destaque oro';
                }
                if ($tipoDestaque == 2) {
                    $fecha           = date("Y-m-d H:i:s");
                    $nuevafecha      = strtotime('+1 month', strtotime($fecha));
                    $nuevafecha      = date('Y-m-j', $nuevafecha);
                    $databd          = array(
                        'prioridad_ndias_busqueda' => 30,
                        'prioridad' => 2,
                        'destacado' => 0,
                        'prioridad_de_tipo' => 'web',
                        'medio_pago'=>'destaque',
                        'fh_vencimiento_prioridad' => $nuevafecha,
                        'proceso_activo' => 1,
                    );
                    $data['status']  = 1;
                    $data['message'] = 'Se dio un destaque plata';
                }
                $whereAnuncioWeb = $this->getAdapter()->quoteInto('id =?',
                    (int) $id);

                $okUpdateP  = $ModelAw->update(
                    $databd, $whereAnuncioWeb
                );
                $modelSolar = new Solr_SolrAviso();
                $modelSolar->addAvisoSolr($id);
                $this->getMessenger()->success('Se Actualizo el aviso');
            } else {
                $data['status']  = 0;
                $data['message'] = 'Parámetros incorrectos';
            }
        }


        $this->_response->appendBody(json_encode($data));
    }

    public function avisosCallcenterAction()
    {
        $this->view->menu_sel_side = self::MENU_POST_SIDE_LISTAVISOS;
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/micuenta.admin.js')
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/gestion.admin.js')
        );
        $this->view->headLink()->appendStylesheet(
            $this->view->S(
                '/js/datepicker/themes/redmond/ui.all.css', 'all')
        );
        if ($this->auth['usuario']->rol == Application_Form_Login::ROL_ADMIN_CALLCENTER) {
            $this->_redirect('/admin/gestion');
        }

        $this->view->model = 'empresa';

        $paginator = array();

        $formAviso             = new Application_Form_AdminAvisoCallcenter();
        $this->view->formAviso = $formAviso;
        $params                = $this->_getAllParams();
        $valid              = $formAviso->isValid($params);
        $valuesPost         = $formAviso->getValues();
        //   if ($this->getRequest()->isPost()) {
        $paramsAuxiliar     = $params;
        $postData           = $this->_getAllParams();
        $sess               = $this->getSession();
        $fhPub              = null;
        $this->view->params = $postData;
        if (!empty($params['fh_pub'])) {
           
            $newFhPub   = explode('-', $params['fh_pub']);
            $fhPub      = $newFhPub[2].'/'.$newFhPub[1].'/'.$newFhPub[0];
            $fhPubimput = $newFhPub[0].'/'.$newFhPub[1].'/'.$newFhPub[2];
            //var_dump($params['fh_pub'],$fhPub);exit;
              //var_dump($fhPubimput);exit;
            $formAviso->setDefault('fh_pub', $fhPubimput);
            if ($fhPub != null) {
                $fhPub = date(
                    'Y-m-d', strtotime($fhPub)
                );
            } else {
                $fhPub = date("Y-m-d");
            }
        }
        else{
            $fhPub = date("Y-m-d");
            $nuevafecha = strtotime ( '-1 Month +1 day' , strtotime ( $fhPub ) ) ;
            $fhPub = date ( 'Y-m-j' , $nuevafecha );

            $newFhPub   = explode('-', $fhPub);
            $fhPubimput = $newFhPub[2].'/'.$newFhPub[1].'/'.$newFhPub[0];
            $formAviso->setDefault('fh_pub', $fhPubimput);
        }
        $fhPubFin = null;
        if (!empty($params['fh_pub_fin'])) {

            $newFhPub      = explode('-', $params['fh_pub_fin']);
            $fhPubFin      = $newFhPub[2].'/'.$newFhPub[1].'/'.$newFhPub[0];
            $fhPubFinimput = $newFhPub[0].'/'.$newFhPub[1].'/'.$newFhPub[2];
            $formAviso->setDefault('fh_pub_fin', $fhPubFinimput);
            if ($fhPubFin != null) {
                $fhPubFin = date(
                    'Y-m-d', strtotime($fhPubFin)
                );
            } else {
                $fhPubFin = date("Y-m-d");
            }
        }
        else{
            $fhPubFin = date("Y-m-d");
            $newFhPub      = explode('-', $fhPubFin);
            $fhPubFinimput      = $newFhPub[2].'/'.$newFhPub[1].'/'.$newFhPub[0];
            $formAviso->setDefault('fh_pub_fin', $fhPubFinimput);
        }
        $tipoDestaque = null;
        if (!empty($params['tipo_destaque'])) {
            $tipoDestaque = $params['tipo_destaque'];
            $formAviso->setDefault('tipo_destaque', $tipoDestaque);
        }
        $tipoImpreso = null;
        if (!empty($params['tipo_impreso'])) {
            $tipoImpreso = $params['tipo_impreso'];
             $formAviso->setDefault('tipo_impreso', $tipoImpreso);
        }
        $estadoweb = null;
        if (!empty($params['estado'])) {
            $estadoweb = $params['estado'];
                         $formAviso->setDefault('estado', $estadoweb);

        }
        $this->view->col           = $col                       = $this->_getParam('col',
            '');
        $this->view->ord           = $ord                       = $this->_getParam('ord',
            'DESC');
        $this->view->avisoAdminUrl = $sess->avisoAdminUrl       = $params;
        $page                      = $this->_getParam('page', 1);
        if (!empty($fhPub) || !empty($tipoDestaque) || !empty($tipoImpreso) || !empty($estadoweb)) {
            $modelAw   = new Application_Model_AnuncioWeb();
            $tipoaviso = null;
            $paginator = $modelAw
                ->getPaginadorBusquedaPersonalizadaAviso(
                $fhPub, $fhPubFin, $tipoDestaque, $tipoImpreso, $estadoweb,
                $col, $ord
            );

            $this->view->mostrando = "Mostrando "
                .$paginator->getItemCount($paginator->getItemsByPage($page))." de "
                .$paginator->getTotalItemCount();
            $paginator->setCurrentPageNumber($page);
            $this->view->pagina    = $paginator->getCurrentPageNumber();


           // $formAviso->setDefaults($paramsAuxiliar);
            $this->view->arrayBusqueda = $paginator;
        }
    }

    public function exportarAvisosAction()
    {
        $id     = $this->getRequest()->getParam("id", false);
        $params = $this->_getAllParams();
        if (!empty($params['fh_pub'])) {
            $newFhPub = explode('-', $params['fh_pub']);
            $fhPub    = $newFhPub[2].'/'.$newFhPub[1].'/'.$newFhPub[0];
           // $formAviso->setDefault('fh_pub', $params['fh_pub']);
            if ($fhPub != null) {
                $fhPub = date(
                    'Y-m-d', strtotime($fhPub)
                );
            } else {
                $fhPub = null;
            }
        }
        if (!empty($params['fh_pub_fin'])) {

            $newFhPub      = explode('-', $params['fh_pub_fin']);
            $fhPubFin      = $newFhPub[2].'/'.$newFhPub[1].'/'.$newFhPub[0];
            $fhPubFinimput = $newFhPub[0].'/'.$newFhPub[1].'/'.$newFhPub[2];
           // $formAviso->setDefault('fh_pub_fin', $fhPubFinimput);
            if ($fhPubFin != null) {
                $fhPubFin = date(
                    'Y-m-d', strtotime($fhPubFin)
                );
            } else {
                $fhPubFin = null;
            }
        }
        $tipoDestaque = null;
        if (!empty($params['tipo_destaque'])) {
            $tipoDestaque = $params['tipo_destaque'];
        }
        $tipoImpreso = null;
        if (!empty($params['tipo_impreso'])) {
            $tipoImpreso = $params['tipo_impreso'];
        }
        $estadoweb = null;
        if (!empty($params['estado'])) {
            $estadoweb = $params['estado'];
        }
        $this->view->col = $col             = $this->_getParam('col', '');
        $this->view->ord = $ord             = $this->_getParam('ord', 'DESC');
        $modelAnuncioWeb = new Application_Model_AnuncioWeb();
     
        switch ($col) {
            case 'Fecha_de_Publicacion':
                $col = 'Fecha de Publicación';

                break;
            case 'Fecha_de_cierre':
                $col = 'Fecha de cierre/fin';
                break;
            default:
                $col='';
                $ord='';
                break;
        }

        $dataAnuncio = $modelAnuncioWeb->getFilterAvisoExport($fhPub, $fhPubFin,
            $tipoDestaque, $tipoImpreso, $estadoweb, $col, $ord);
        //getFilterAviso
        $headers     = array(
            'Portal',
            'Fecha de Publicación',
            'Fecha de cierre/fin',
            'Tipo de Destaque Web',
            'Medio de Pago Web',
            'Monto Web',
            'Tipo de Aviso Impreso',
            'Medio de Pago Impreso',
            'Monto Impreso',
            'Correo',
            'Titulo del Aviso',
            'Estado',
        );
        //  var_dump(array_values($dataAnuncio));exit;
        App_Service_Excel::getInstance()->setHeaders($headers);
        App_Service_Excel::getInstance()->appendList(array_values($dataAnuncio));
        App_Service_Excel::getInstance()->setLogo(
            APPLICATION_PATH.'/../public/static/img/logo_aquiesta.png'
        );
        App_Service_Excel::getInstance()->setData($dataAnuncio);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Lista de Aviso.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter(
                App_Service_Excel::getInstance()->getObjectExcelEmpresa(),
                'Excel5');
        $objWriter->save('php://output');
        exit;
    }
}
