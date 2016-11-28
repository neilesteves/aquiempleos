<?php

class Postulante_AuthController extends App_Controller_Action
{
    protected $_loginRequiredFor = array('logout');
    protected $_messageSuccess   = '¡Bienvenido!';
    protected $_messageError     = 'Error al iniciar sesión.';

    public function loginAction()
    {
        if ($this->isAuth) {
            $this->_redirect($this->_request->getPost('return').'/');
        }
        $next = $this->getRequest()->getParam('next', $this->view->baseUrl('/'));

        if ($this->getRequest()->isPost()) {
            echo"<br>OK Request";
            Zend_Auth::getInstance()->clearIdentity();
            $login   = $this->getRequest()->getPost('userEmail', '');
            $pswd    = $this->getRequest()->getPost('userPass', '');
            $type    = $this->getRequest()->getPost('tipo', '');
            $isValid = Application_Model_Usuario::auth($login, $pswd, $type);
            if ($isValid) {

                if ($this->getRequest()->getPost('id_tarifa', '')) {
                    $session         = $this->getSession();
                    $session->tarifa = $this->getRequest()->getPost('id_tarifa',
                        '');
                }

                $config = $this->getConfig();

                if ($this->getRequest()->getPost('save', '') == '1') {
                    $objSessionNamespace = new Zend_Session_Namespace('Zend_Auth');

                    if ($type == Application_Form_Login::ROL_ADMIN) {
                        $isValid                  = Application_Model_Usuario::authAdmin($login,
                                $pswd, $type);
                        $this->sessionAdmin       = new Zend_Session_Namespace('admin');
                        $this->sessionAdmin->auth = array('usuario' => $isValid);

                        $objSessionNamespace->setExpirationSeconds($config->app->adminSessionRemember);
                        Zend_Session::rememberMe($config->app->adminSessionRemember);
                        $this->getAdapter()->update(
                            'zend_session',
                            array('lifetime' => $config->app->adminSessionRemember),
                            $this->getAdapter()->quoteInto(
                                'id = ?', Zend_Session::getId()
                            )
                        );
                    } else {
                        $objSessionNamespace->setExpirationSeconds($config->app->sessionRemember);
                        Zend_Session::rememberMe($config->app->sessionRemember);
                        $this->getAdapter()->update(
                            'zend_session',
                            array('lifetime' => $config->app->sessionRemember),
                            $this->getAdapter()->quoteInto(
                                'id = ?', Zend_Session::getId()
                            )
                        );
                    }
                    //Zend_Session::rememberMe($config->app->sessionRemember);
                } else {
                    Zend_Session::ForgetMe();
                    $this->getAdapter()->update(
                        'zend_session',
                        array('lifetime' => $config->app->session),
                        $this->getAdapter()->quoteInto(
                            'id = ?', Zend_Session::getId()
                        )
                    );
                    $config = $this->getConfig();

                    //$objSessionNamespace = new Zend_Session_Namespace('Zend_Auth');
                    //$objSessionNamespace->setExpirationSeconds($config->app->sessionRemember);
                    Zend_Session::rememberMe($config->app->sessionRemember);
                }

                $modelUsu = new Application_Model_Usuario();
                $where    = $modelUsu->getAdapter()->quoteInto("email = '$login' AND rol like '$type%'",
                    '');
                $modelUsu->update(
                    array(
                    'ultimo_login' => date('Y-m-d H:i:s'),
                    'ip' => $this->getRequest()->getServer('REMOTE_ADDR')
                    ), $where
                );

                $this->getMessenger()->success($this->_messageSuccess);
                //$this->getMessenger()->info('Bienvenido');
                //$this->getMessenger()->error('Bienvenido');
            } else {
                $modelUsu = new Application_Model_Usuario();
                $arrayUsu = $modelUsu->getUsuarioActivo($login, $type);

                if (isset($arrayUsu['activo']) && $arrayUsu['activo'] == 1) {
//                    var_dump(Zend_Layout::getMvcInstance()->getView());
                    $this->getMessenger()->error(
                        $this->_messageError.=': Datos inválidos.'
                    );
                } elseif (isset($arrayUsu['activo']) && $arrayUsu['activo'] == 0) {
                    $this->getMessenger()->error(
                        $this->_messageError.=': Usuario bloqueado.'
                    );
                } else {
                    $this->getMessenger()->error(
                        $this->_messageError.=': Usuario no registrado.'
                    );
                }

                // Message Error Login Postulante
                $messageError         = new Zend_Session_Namespace('messageError');
                $messageError->string = $this->_messageError;
            }
            if ($type == Application_Form_Login::ROL_ADMIN && !$isValid) {
                $this->_redirect(
                    $this->view->url(
                        array('module' => 'admin', 'controller' => 'home', 'action' => 'index')
                    )
                );
            }
        }
        #TODO revisar posible error
        if (strpos($this->_request->getPost('return'), '#questionsWM')) {
            $url          = explode('/', $this->_request->getPost('return'));
            $url          = explode('-', $url[2]);
            $auth         = Zend_Auth::getInstance()->getStorage()->read();
            $p            = new Application_Model_Postulacion();
            $hasPostulado = $p->hasPostuladoByUrlId($url[1],
                $auth['postulante']['id']);
            if ($hasPostulado) {
                $this->_redirect(
                    str_replace('#questionsWM', '',
                        $this->_request->getPost('return'))
                );
            }
        }
        $this->_redirect($this->_request->getPost('return'));
    }

    public function loginPeAction()
    {
        if ($this->isAuth) {
            $this->_redirect($this->_request->getPost('return').'/');
        }
        $next  = $this->getRequest()->getParam('next', $this->view->baseUrl('/'));
        $param = $this->_getAllParams();
        //  var_dump($param);exit;
        if ($this->getRequest()->isPost()) {
            echo"<br>OK Request";
            Zend_Auth::getInstance()->clearIdentity();
            $login   = $param['txtUser'];
            $pswd    = $param['txtPasswordLogin'];
            $type    = $param['tipo'];
            $isValid = Application_Model_Usuario::auth($login, $pswd, $type);
            // var_dump($isValid);exit;
            if ($isValid) {

                if ($this->getRequest()->getPost('id_tarifa', '')) {
                    $session         = $this->getSession();
                    $session->tarifa = $this->getRequest()->getPost('id_tarifa',
                        '');
                }

                $config = $this->getConfig();

                if ($this->getRequest()->getPost('save', '') == '1') {
                    $objSessionNamespace = new Zend_Session_Namespace('Zend_Auth');

                    if ($type == Application_Form_Login::ROL_ADMIN) {
                        $isValid                  = Application_Model_Usuario::authAdmin($login,
                                $pswd, $type);
                        $this->sessionAdmin       = new Zend_Session_Namespace('admin');
                        $this->sessionAdmin->auth = array('usuario' => $isValid);

                        $objSessionNamespace->setExpirationSeconds($config->app->adminSessionRemember);
                        Zend_Session::rememberMe($config->app->adminSessionRemember);
                        $this->getAdapter()->update(
                            'zend_session',
                            array('lifetime' => $config->app->adminSessionRemember),
                            $this->getAdapter()->quoteInto(
                                'id = ?', Zend_Session::getId()
                            )
                        );
                    } else {
                        $objSessionNamespace->setExpirationSeconds($config->app->sessionRemember);
                        Zend_Session::rememberMe($config->app->sessionRemember);
                        $this->getAdapter()->update(
                            'zend_session',
                            array('lifetime' => $config->app->sessionRemember),
                            $this->getAdapter()->quoteInto(
                                'id = ?', Zend_Session::getId()
                            )
                        );
                    }
                    //Zend_Session::rememberMe($config->app->sessionRemember);
                } else {
                    Zend_Session::ForgetMe();
                    $this->getAdapter()->update(
                        'zend_session',
                        array('lifetime' => $config->app->session),
                        $this->getAdapter()->quoteInto(
                            'id = ?', Zend_Session::getId()
                        )
                    );
                    $config = $this->getConfig();

                    //$objSessionNamespace = new Zend_Session_Namespace('Zend_Auth');
                    //$objSessionNamespace->setExpirationSeconds($config->app->sessionRemember);
                    Zend_Session::rememberMe($config->app->sessionRemember);
                }

                $modelUsu = new Application_Model_Usuario();
                $where    = $modelUsu->getAdapter()->quoteInto("email = '$login' AND rol like '$type%'",
                    '');
                $modelUsu->update(
                    array(
                    'ultimo_login' => date('Y-m-d H:i:s'),
                    'ip' => $this->getRequest()->getServer('REMOTE_ADDR')
                    ), $where
                );

                $this->getMessenger()->success($this->_messageSuccess);
                //$this->getMessenger()->info('Bienvenido');
                //$this->getMessenger()->error('Bienvenido');
            } else {
                $modelUsu = new Application_Model_Usuario();
                $arrayUsu = $modelUsu->getUsuarioActivo($login, $type);

                if (isset($arrayUsu['activo']) && $arrayUsu['activo'] == 1) {
//                    var_dump(Zend_Layout::getMvcInstance()->getView());
                    $this->getMessenger()->error(
                        $this->_messageError.=': Datos inválidos.'
                    );
                } elseif (isset($arrayUsu['activo']) && $arrayUsu['activo'] == 0) {
                    $this->getMessenger()->error(
                        $this->_messageError.=': Usuario bloqueado.'
                    );
                } else {
                    $this->getMessenger()->error(
                        $this->_messageError.=': Usuario no registrado.'
                    );
                }

                // Message Error Login Postulante
                $messageError         = new Zend_Session_Namespace('messageError');
                $messageError->string = $this->_messageError;
            }
            if ($type == Application_Form_Login::ROL_ADMIN && !$isValid) {
                $this->_redirect(
                    $this->view->url(
                        array('module' => 'admin', 'controller' => 'home', 'action' => 'index')
                    )
                );
            }
        }
        #TODO revisar posible error
        if (strpos($this->_request->getPost('return'), '#questionsWM')) {
            $url          = explode('/', $this->_request->getPost('return'));
            $url          = explode('-', $url[2]);
            $auth         = Zend_Auth::getInstance()->getStorage()->read();
            $p            = new Application_Model_Postulacion();
            $hasPostulado = $p->hasPostuladoByUrlId($url[1],
                $auth['postulante']['id']);
            if ($hasPostulado) {
                $this->_redirect(
                    str_replace('#questionsWM', '',
                        $this->_request->getPost('return'))
                );
            }
        }
        $this->_redirect($this->_request->getPost('return'));
    }

    public function loginAjaxAction()
    {

        $estalogueado = $this->isAuth;
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest())
                ;
        else {
            exit(0);
        }
        $type = $this->getRequest()->getPost('tipo', '');
        if ($type == 'admin') {
            if (isset($this->sessionAdmin) && $this->sessionAdmin->auth) {
                $estalogueado = true;
            } else {
                $estalogueado = false;
            }
        } else {
            $estalogueado = $this->isAuth;
        }

        if (!$estalogueado) {
            if ($type != 'admin') Zend_Auth::getInstance()->clearIdentity();
            $login = $this->getRequest()->getPost('userEmail', '');
            $pswd  = $this->getRequest()->getPost('userPass', '');
            $type  = $this->getRequest()->getPost('tipo', '');

            $token = $this->getRequest()->getPost('auth_token', '');


            $tokenValid = false;
            if (crypt('l0gInAPtiTus', $token) == $token) {
                $tokenValid = true;
            }


            $frmLogin    = new Application_Form_Login();
            $dataRequest = $this->getRequest()->getPost();
            $isValidForm = $frmLogin->isValid($dataRequest);

            if ($tokenValid && $isValidForm) {
                if ($type != 'admin')
                        $isValid = Application_Model_Usuario::auth($login,
                            $pswd, $type);
                else {
                    $isValid                  = Application_Model_Usuario::authAdmin($login,
                            $pswd, $type);
                    $this->sessionAdmin       = new Zend_Session_Namespace('admin');
                    $this->sessionAdmin->auth = array('usuario' => $isValid);
                }

                if ($isValid) {

//                    $util = $this->_helper->getHelper('Util');
//                    $datosLogin = $util->encriptalo('login@|@'.$login.'@|@'.$pswd);
//
//                    $sesLoginRemote = new Zend_Session_Namespace('aptitusLogin');
//                    $sesLoginRemote->data = urlencode($datosLogin);
                    try {
                        $mLogin   = new Mongo_Login();
                        if ($type != 'admin')
                                $dataAuth = Zend_Auth::getInstance()->getStorage()->read();
                        else $dataAuth = $this->sessionAdmin->auth;
                        $datos    = array(
                            'id_usuario' => $dataAuth['usuario']->id,
                            'tipo_usuario' => $dataAuth['usuario']->rol
                        );
                        $mLogin->save($datos);
                    } catch (Exception $e) {

                    }

                    $response           = array(
                        'status' => 'ok',
                        'msg' => 'Los datos fueron validos'
                    );
                    $sesionMsg          = new Zend_Session_Namespace("msg_welcome");
                    $sesionMsg->welcome = $this->_messageSuccess;

                    if ($this->getRequest()->getPost('id_tarifa', '')) {
                        $session         = $this->getSession();
                        $session->tarifa = $this->getRequest()->getPost('id_tarifa',
                            '');
                    }

                    $config = $this->getConfig();
                    $idZs   = Zend_Session::getId();

                    if ($this->getRequest()->getPost('save', '') == '1') {
                        $objSessionNamespace = new Zend_Session_Namespace('Zend_Auth');

                        if ($type == Application_Form_Login::ROL_ADMIN) {
                            Zend_Session::rememberMe($config->app->adminSessionRemember);
                            $objSessionNamespace->setExpirationSeconds($config->app->adminSessionRemember);
                            $this->getAdapter()->update(
                                'zend_session',
                                array('lifetime' => $config->app->adminSessionRemember),
                                $this->getAdapter()->quoteInto('id = ?', $idZs)
                            );
                        } else {
                            Zend_Session::rememberMe($config->app->sessionRemember);
                            $objSessionNamespace->setExpirationSeconds($config->app->sessionRemember);
                            $this->getAdapter()->update(
                                'zend_session',
                                array('lifetime' => $config->app->sessionRemember),
                                $this->getAdapter()->quoteInto('id = ?', $idZs)
                            );
                        }
                    } else {
                        Zend_Session::ForgetMe();
                        $this->getAdapter()->update(
                            'zend_session',
                            array('lifetime' => $config->app->session),
                            $this->getAdapter()->quoteInto('id = ?', $idZs)
                        );
                    }

                    $modelUsu = new Application_Model_Usuario();
                    $where    = $modelUsu->getAdapter()->quoteInto("email = '$login' AND rol like '$type%'",
                        '');
                    $modelUsu->update(
                        array(
                        'ultimo_login' => date('Y-m-d H:i:s'),
                        'ip' => $this->getRequest()->getServer('REMOTE_ADDR')
                        ), $where
                    );
                } else {

//                    if ($type === Application_Form_Login::ROL_EMPRESA) {
//                        $modelUsuario = new Application_Model_Usuario();
//                        $estaBloqueado = $modelUsuario->esCuentaBloqueada($login, $type);
//
//                        if ($estaBloqueado) {
//                            $response = array(
//                                'status' => 'error',
//                                'msg' => 'Su cuenta se encuentra bloqueada, por favor comuníquese con el administrador de su empresa.',
//                            );
//                        } else {
//                            $response = array(
//                                'status' => 'error',
//                                'msg' => 'Datos inválidos',
//                            );
//                        }
//
//                    } else {
//                        $response = array(
//                            'status' => 'error',
//                            'msg' => 'Datos inválidos',
//                        );
//                    }


                    $response = array(
                        'status' => 'error',
                        'msg' => 'Datos inválidos',
                    );
                }
            } else {
                $response = array(
                    'status' => 'error',
                    'msg' => 'Datos inválidos',
                );
            }
        }

        if ($estalogueado) {
            $responsedos = array(
                'status' => 'error',
                'msg' => 'Debe cerrar la sesión actual'
            );
            $this->_response->appendBody(Zend_Json::encode($responsedos));
        } else {
            $this->_response->appendBody(Zend_Json::encode($response));
        }
    }

    public function newLoginAjaxAction()
    {

        $estalogueado = $this->isAuth;
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $question     = $this->getRequest()->getPost('question');


        if (!$this->getRequest()->isPost() && !$this->getRequest()->isXmlHttpRequest()) {
            exit(0);
        }

        $type = $this->getRequest()->getPost('tipo', '');
        if ($type == 'admin') {
            if (isset($this->sessionAdmin) && $this->sessionAdmin->auth) {
                $estalogueado = true;
            } else {
                $estalogueado = false;
            }
        } else {
            $estalogueado = $this->isAuth;
        }

        if (!$estalogueado) {
            if ($type != 'admin') {
                Zend_Auth::getInstance()->clearIdentity();
            }
            $login = $this->getRequest()->getPost('txtUser', '');
            $pswd  = $this->getRequest()->getPost('txtPasswordLogin', '');
            $type  = $this->getRequest()->getPost('tipo', '');

            $frmLogin    = new Application_Form_LoginNew();
            $dataRequest = $this->getRequest()->getPost();
            $isValidForm = $frmLogin->isValid($dataRequest);

            if ($isValidForm) {
                unset($dataRequest['hidAuthToken']);

                if ($type != 'admin') {
                    $isValid = Application_Model_Usuario::auth($login, $pswd,
                            $type);
                } else {
                    $isValid = Application_Model_Usuario::authAdmin($login,
                            $pswd, $type);

                    $this->sessionAdmin       = new Zend_Session_Namespace('admin');
                    $this->sessionAdmin->auth = array('usuario' => $isValid);
                }
                if ($isValid) {
                    $response = array(
                        'status' => '1',
                        'msg' => 'Los datos fueron validos',
                    );

                    if ($question) {
                        $authStorage          = Zend_Auth::getInstance()->getStorage()->read();
                        $response['newmodal'] = $this->_helper->Util->_NexAction($question,
                            $authStorage["postulante"]["id"]);
                    }
                    $sesionMsg          = new Zend_Session_Namespace("msg_welcome");
                    $sesionMsg->welcome = $this->_messageSuccess;

                    if ($this->getRequest()->getPost('id_tarifa')) {
                        $session         = $this->getSession();
                        $session->tarifa = $this->getRequest()->getPost('id_tarifa',
                            '');
                    }

                    $config = $this->getConfig();
                    $idZs   = Zend_Session::getId();

                    if ($this->getRequest()->getPost('save') == 1) {
                        $objSessionNamespace = new Zend_Session_Namespace('Zend_Auth');

                        if ($type == Application_Form_Login::ROL_ADMIN) {
                            Zend_Session::rememberMe($config->app->adminSessionRemember);
                            $objSessionNamespace->setExpirationSeconds($config->app->adminSessionRemember);
                            $this->getAdapter()->update(
                                'zend_session',
                                array('lifetime' => $config->app->adminSessionRemember),
                                $this->getAdapter()->quoteInto('id = ?', $idZs)
                            );
                        } else {
                            Zend_Session::rememberMe($config->app->sessionRemember);
                            $objSessionNamespace->setExpirationSeconds($config->app->sessionRemember);
                            $this->getAdapter()->update(
                                'zend_session',
                                array('lifetime' => $config->app->sessionRemember),
                                $this->getAdapter()->quoteInto('id = ?', $idZs)
                            );
                        }
                    } else {
                        Zend_Session::ForgetMe();
                        $this->getAdapter()->update(
                            'zend_session',
                            array('lifetime' => $config->app->session),
                            $this->getAdapter()->quoteInto('id = ?', $idZs)
                        );
                    }

                    $modelUsu = new Application_Model_Usuario();
                    $where    = $modelUsu->getAdapter()->quoteInto("email = '$login' AND rol like '$type%'",
                        '');
                    $modelUsu->update(
                        array(
                        'ultimo_login' => date('Y-m-d H:i:s'),
                        'ip' => $this->getRequest()->getServer('REMOTE_ADDR')
                        ), $where
                    );
                } else {
                    $frmLogin->getElement('hidAuthToken')->initCsrfToken();
                    $response = array(
                        'status' => '0',
                        'msg' => 'El email '.$login.' es incorrecto',
                        'hashToken' => $frmLogin->getElement('hidAuthToken')->getValue()
                    );
                }
            } else {
                $frmLogin->getElement('hidAuthToken')->initCsrfToken();
                $response = array(
                    'status' => '0',
                    'msg' => 'Datos inválidos',
                    'hashToken' => $frmLogin->getElement('hidAuthToken')->getValue()
                );
            }
        }

        if ($estalogueado) {
            $responsedos = array(
                'status' => '0',
                'msg' => 'Debe cerrar la sesión actual'
            );
            $this->_response->appendBody(Zend_Json::encode($responsedos));
        } else {
            $this->_response->appendBody(Zend_Json::encode($response));
        }
    }

    public function logoutAction()
    {

        $next = $this->getRequest()->getParam('next', $this->view->baseUrl('/'));
        if (isset($this->auth[Application_Form_Login::ROL_EMPRESA])) {
            $next = $this->getRequest()->getParam('next',
                $this->view->baseUrl('/empresa/'));
        }

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
        $dataFacebook       = new Zend_Session_Namespace('dataFacebook');
        $dataFacebook->data = array();
        $dataLinkedin       = new Zend_Session_Namespace('dataLinkedin');
        $dataLinkedin->data = array();
        $this->_redirect($next);
    }

    public function loginFacebookAction()
    {
        $code = $this->getRequest()->getParam('code', 0);
        if ($this->_helper->AuthFacebook->LoginFb($code)) {
            $userFb = $this->_helper->AuthFacebook->getUserFb();
            Application_Model_Usuario::authRS(
                $userFb['email']
            );
            $this->_redirect($userFb['UrlReturn']);
        }
        if (!empty($this->_helper->AuthFacebook->getReturnRegistro())) {
             $this->_redirect($this->_helper->AuthFacebook->getReturnRegistro());
        }
        $this->getMessenger()->error('No se puedo registrar su cuenta.');
        $this->_redirect('/');
    }

    public function validacionGoogleAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $config     = $this->getConfig();
        $dataGoogle = $this->getRequest()->getParams();

        //  $goURL = (isset($dataGoogle['go']) ? $dataGoogle['go'] : null);

        $helper       = new App_Controller_Action_Helper_Util();
        $redirect_uri = urldecode($helper->decodifica(($dataGoogle['go'])));
        $pathAviso    = explode('/', $redirect_uri);
        $goURL        = $redirect_uri;


        if (!isset($dataGoogle)) {

            if ($goURL) {
                $goURL = urldecode($goURL);
                $this->_redirect($goURL);
            } else {
                $this->_redirect('/');
            }
        }
        if ($dataGoogle['openid_mode'] == 'cancel') {

            if ($goURL) {
                $goURL = urldecode($goURL);
                $this->_redirect($goURL);
            } else {
                $this->_redirect('/');
            }
        }
        $rsId   = str_replace(
            $config->apis->google->urlResponse, "",
            $dataGoogle['openid_claimed_id']
        );
        $google = new Zend_Session_Namespace('google');
        $rsId   = $google->user_id;
        $red    = new Application_Model_CuentaRs();
        $userId = $red->getUserIdByRedSocial(
            $rsId, 'google'
        );
        if ($userId == null) {
            $this->getMessenger()->error(
                $this->_messageError.
                ': No hay una cuenta asociada para el usuario '.
                $dataGoogle['openid_ext1_value_email']
            );
        } else {
            $this->_guardarSesion($userId);
            $this->getMessenger()->success($this->_messageSuccess);
        }

        if ($goURL) {
            $goURL = urldecode($goURL);
            $this->_redirect($goURL);
        } else {
            $this->_redirect('/');
        }
    }

    public function recuperarClaveAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest())
                ;
        else {
            $this->_redirect('/');
        }


        $dataPost = $this->getRequest()->getParams();

        if ($dataPost['email'] == null) {
            $this->_redirect('/');
        }
        $u       = new Application_Model_Usuario();
        $usuario = $u->getIdByEmailRol($dataPost['email'], $dataPost['rol']);

        $hash      = new Zend_Form_Element_Hash('csrf_hash',
            array('salt' => 'exitsalt'));
        $hash->setTimeout(120); // 2min
        $hash->initCsrfToken();
        $hashToken = $hash->getValue();

        $idUsuario     = $usuario['id'];
        $sesOlvidoPass = new Zend_Session_Namespace('olvidoPassword_'.$idUsuario);
        $sesOlvidoPass->setExpirationSeconds(1800);

        $form = new Application_Form_RecuperarClave();
        $form->getElement('recuperar_token')->setValue($hashToken);
        $form->removeElement('recuperar_token');

        if ($usuario !== false && $form->isValid($dataPost) === true) {

            if ($sesOlvidoPass->enviado && ($sesOlvidoPass->enviado == $idUsuario)) {

                $data = array(
                    'status' => 'error',
                    'msg' => 'Ya se envió el correo',
                    'hash_token' => $hashToken
                );
            } else {

                $config = $this->getConfig();
                $token  = Application_Model_Usuario::generarToken(
                        $usuario['id'], $config->app->tokenUser
                );
                if ($dataPost['rol'] == Application_Form_Login::ROL_POSTULANTE) {
                    $p          = new Application_Model_Postulante();
                    $postulante = $p->getSlugByUsuarioId($usuario['id']);
                    $this->_helper->Mail->recuperarContrasenaPostulante(
                        array(
                            'to' => $dataPost['email'],
                            'nombre' => $postulante['nombres'],
                            'slug' => $postulante['slug'],
                            'urlToken' => $token
                        )
                    );
                } elseif ($dataPost['rol'] == Application_Form_Login::ROL_EMPRESA) {
                    $e       = new Application_Model_Empresa();
                    $empresa = $e->getNombreByUsuarioId($usuario['id']);
                    $this->_helper->Mail->recuperarContrasenaEmpresa(
                        array(
                            'to' => $dataPost['email'],
                            'empresa' => $empresa['nombre_comercial'],
                            'urlToken' => $token
                        )
                    );
                } elseif ($dataPost['rol'] == Application_Form_Login::ROL_ADMIN) {
                    $u       = new Application_Model_Usuario();
                    $usuario = $u->getUsuarioId($usuario['id']);
                    $this->_helper->Mail->recuperarContrasenaAdministrador(
                        array(
                            'to' => $dataPost['email'],
                            'nombre' => $usuario->nombre,
                            'urlToken' => $token
                        )
                    );
                }

                if ($token) {
                    $data                   = array(
                        'status' => 'ok',
                        'msg' => 'Se envió el correo',
                        'hash_token' => $hashToken
                    );
                    $sesOlvidoPass->enviado = $idUsuario;
                } else {
                    $data = array(
                        'status' => 'error',
                        'msg' => 'Formulario no valido',
                        'hash_token' => $hashToken
                    );
                    unset($sesOlvidoPass->enviado);
                }
            }
        } else {

            $data = array(
                'status' => 'error',
                'msg' => 'Formulario no valido',
                'hash_token' => $hashToken
            );
            unset($sesOlvidoPass->enviado);
        }



        if ($usuario === false) {
            $data = array(
                'status' => 'mailinvalid',
                'msg' => 'El e-mail ingresado debe estar asociado a una cuenta',
                'hash_token' => $hashToken
            );
            unset($sesOlvidoPass->enviado);
        }

        $this->_response->appendBody(Zend_Json::encode($data));
    }

    public function newRecuperarClaveAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        if ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest()) {
            $dataPost = $this->getRequest()->getParams();
            if ($dataPost['txtEmailForgot'] == null) {
                $this->_redirect('/');
            }
            $u             = new Application_Model_Usuario();
            $usuario       = $u->getIdByEmailRol($dataPost['txtEmailForgot'],
                $dataPost['rol']);
            $idUsuario     = $usuario['id'];
            $form          = new Application_Form_NewRecuperarClave();
            $sesOlvidoPass = new Zend_Session_Namespace('olvidoPassword_'.$idUsuario);
            $sesOlvidoPass->setExpirationSeconds(1800);

            if ($usuario !== false && $form->isValid($dataPost) === true) {
                if (isset($usuario['activo']) && $usuario['activo'] == 1) {
                    if ($sesOlvidoPass->enviado && ($sesOlvidoPass->enviado == $idUsuario)) {
///                        $form->getElement('hidRecoverPassword')->initCsrfToken();
                        $data = array(
                            'status' => '0',
                            'msg' => 'Ya se envió el correo',
//                            'hashToken' => $form->getElement('hidRecoverPassword')->getValue()
                        );
                    } else {

                        $config = $this->getConfig();
                        $token  = Application_Model_Usuario::generarToken(
                                $usuario['id'], $config->app->tokenUser
                        );
                        if ($dataPost['rol'] == Application_Form_Login::ROL_POSTULANTE) {
                            $p          = new Application_Model_Postulante();
                            $postulante = $p->getSlugByUsuarioId($usuario['id']);
                            $this->_helper->Mail->recuperarContrasenaPostulante(
                                array(
                                    'to' => $dataPost['txtEmailForgot'],
                                    'nombre' => $postulante['nombres'],
                                    'slug' => $postulante['slug'],
                                    'urlToken' => $token
                                )
                            );
                        } elseif ($dataPost['rol'] == Application_Form_Login::ROL_EMPRESA) {
                            $e       = new Application_Model_Empresa();
                            $empresa = $e->getNombreByUsuarioId($usuario['id']);
                            $this->_helper->Mail->recuperarContrasenaEmpresa(
                                array(
                                    'to' => $dataPost['txtEmailForgot'],
                                    'empresa' => $empresa['nombre_comercial'],
                                    'urlToken' => $token
                                )
                            );
                        } elseif ($dataPost['rol'] == Application_Form_Login::ROL_ADMIN) {
                            $u       = new Application_Model_Usuario();
                            $usuario = $u->getUsuarioId($usuario['id']);
                            $this->_helper->Mail->recuperarContrasenaAdministrador(
                                array(
                                    'to' => $dataPost['txtEmailForgot'],
                                    'nombre' => $usuario->nombre,
                                    'urlToken' => $token
                                )
                            );
                        }
                          //  $form->getElement('hidRecoverPassword')->initCsrfToken();
                            $data                   = array(
                                'status' => '1',
                                'msg' => 'Se envió el correo',
                               // 'hashToken' => $form->getElement('hidRecoverPassword')->getValue()
                            );
                            $sesOlvidoPass->enviado = $idUsuario;
                    }
                } else {
                    $usuario = false;
                }
            } else {
           //     $form->getElement('hidRecoverPassword')->initCsrfToken();
                $data = array(
                    'status' => '0',
                    'msg' => 'Formulario no valido',
                 //   'hashToken' => $form->getElement('hidRecoverPassword')->getValue()
                );
                unset($sesOlvidoPass->enviado);
            }



            if ($usuario === false) {
           //     $form->getElement('hidRecoverPassword')->initCsrfToken();
                $data = array(
                    'status' => '0',
                    'msg' => 'El e-mail ingresado debe estar asociado a una cuenta',
          //          'hashToken' => $form->getElement('hidRecoverPassword')->getValue()
                );
                unset($sesOlvidoPass->enviado);
            }

            $this->_response->appendBody(Zend_Json::encode($data));
        } else {
            $this->_redirect('/');
        }
    }

    public function generarClaveAction()
    {
        Zend_Layout::getMvcInstance()->setLayout('simple');
        $token = $this->getRequest()->getParam('key', 0);
        $user  = Application_Model_Usuario::isValidToken($token);
        if ($user === false) {
            $this->_redirect('/auth/token-invalido');
        }
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );
        $form = new Application_Form_EstablecerClave();
        $form->token->setValue($token);
        if ($this->getRequest()->isPost()) {
            $postData = $this->_getAllParams();
            if ($form->isValid($postData)) {
                Application_Model_Usuario::setNewPassword(
                    $user['id'], $this->getRequest()->getPost('password')
                );
                $this->_redirect('/auth/exito-cambio-clave');
            }
        }
        $this->view->form = $form;
    }

    public function tokenInvalidoAction()
    {
        Zend_Layout::getMvcInstance()->setLayout('simple');
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );
    }

    public function exitoCambioClaveAction()
    {
        Zend_Layout::getMvcInstance()->setLayout('simple');
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );
    }

    /**
     * A partir del ID del usuario, obtiene la demas informacion y lo almacena
     * en la sesion.
     *
     * @param int $userId
     * @return bool
     */
    private function _guardarSesion($userId)
    {
        $u           = new Application_Model_Usuario();
        $usuario     = $u->getUsuarioId($userId);
        $auth        = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session());
        // TODO: sacar posible sql inj
        $postulante  = new Application_Model_Postulante();
        $related     = $postulante
            ->fetchRow('id_usuario = '.$userId)
            ->toArray();
        $authStorage = $auth->getStorage();
        $authStorage->write(
            array(
                'usuario' => $usuario,
                'postulante' => $related
            )
        );
        return true;
    }

    public static function validacionLogin($email, $pswd)
    {
        $adapter = new App_Auth_Adapter_AptitusDbTable(
            $this->getAdapter()
        );

        $adapter->setIdentity($email);
        $adapter->setCredential($pswd);

        $auth   = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session());
        $result = $auth->authenticate($adapter);

        if ($result->isValid()) {
            # TODO : Guardar datos en sesión
            $user                   = $adapter->getResultRowObject();
            $session                = $this->getSession();
            $session->authenticated = true;
            $session->user          = $user;

            # TODO : Actualizar campo last_login
            $valor = true;
        } else {
            $valor = false;
        }
        return $valor;
    }

    public function logoutaAction()
    {

        $next = $this->getRequest()->getParam('next',
            $this->view->baseUrl('/admin'));
        Zend_Session::namespaceUnset('admin');
        if (isset($this->auth[Application_Form_Login::ROL_EMPRESA])) {
            $next = $this->getRequest()->getParam('next',
                $this->view->baseUrl('/empresa/'));
        }

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
        $this->_redirect($next);
    }

    public function catchtokenAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $params = $this->getRequest()->getParams();
        $token  = $params['access_token'];
        $json   = file_get_contents("https://www.googleapis.com/oauth2/v1/tokeninfo?access_token=$token");
        $data   = json_decode($json);
        if ($data->audience == '364621363692-4phcg6ige9afrepnbpo43qn6nb3j7072.apps.googleusercontent.com') {
            $google          = new Zend_Session_Namespace('google');
            $google->email   = $data->email;
            $google->user_id = $data->user_id;
            $this->_redirect($params['state']);
        } else {
            $this->_redirect(SITE_URL);
        }
    }

    public function plusAction()
    {
        
    }

    public function confirmacionActivacionCuentaAction()
    {
        Zend_Layout::getMvcInstance()->setLayout('simple');
        $token       = $this->getRequest()->getParam('key', 0);
        $desencripta = $this->_helper->Util->decodifica($token);
        $user        = Application_Model_Usuario::isValidToken($desencripta);
        $Postulante  = new Application_Model_Postulante();
        if ($user) {
            $data = Application_Model_Usuario::setactivacion($user['id']);
            $pos  = $Postulante->fetchRow($Postulante->getAdapter()->quoteInto('id_usuario = ?',
                    $user['id']));
            $this->_helper->solr->addSolr($pos->id);
            $this->_redirect('/auth/exito-confirmacion-cuenta');
        } else {
            $this->_redirect('/auth/fallo-confirmacion-cuenta');
        }


//        Zend_Layout::getMvcInstance()->assign(
//            'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
//        );
//        $form = new Application_Form_EstablecerClave();
//        $form->token->setValue($token);
//        if ($this->getRequest()->isPost()) {
//            $postData = $this->_getAllParams();
//            if ($form->isValid($postData)) {
//                Application_Model_Usuario::setNewPassword(
//                    $user['id'],
//                    $this->getRequest()->getPost('password')
//                );
//                $this->_redirect('/auth/exito-cambio-clave');
//            }
//        }
//        $this->view->form = $form;
    }

    public function exitoConfirmacionCuentaAction()
    {
        Zend_Layout::getMvcInstance()->setLayout('simple');
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );
    }

    public function falloConfirmacionCuentaAction()
    {
        Zend_Layout::getMvcInstance()->setLayout('simple');
        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );
    }

    private function _crearSlug($valuesPostulante, $lastId)
    {
        $slugFilter = new App_Filter_Slug(
            array('field' => 'slug',
            'model' => $this->_postulante)
        );

        $slug = $slugFilter->filter(
            $valuesPostulante['nombres'].' '.
            $valuesPostulante['apellidos'].' '.
            substr(md5($lastId), 0, 8)
        );
        return $slug;
    }
}