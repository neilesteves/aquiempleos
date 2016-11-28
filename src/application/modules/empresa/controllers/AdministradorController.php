<?php
 
class Empresa_AdministradorController extends App_Controller_Action_Empresa
{
    protected $_empresa;
    
    /**
     * @var Application_Model_Usuario
     */
    protected $_usuario;
    protected $_anuncioweb;
    
    protected $_tieneBuscador;
    protected $_candidatosSugeridos;
    protected $_cache = null;
    
    /**
     * @var Application_Model_UsuarioEmpresa
     */
    protected $_usuarioempresa;

    public function init()
    {
        parent::init();
        if ( Zend_Auth::getInstance()->hasIdentity()!= true ) {
            $this->_redirect('/empresa');
        }
        $this->_usuario = new Application_Model_Usuario();
        
        if ($this->_usuario->hasvailBlokeo($this->auth['usuario']->id) ) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::forgetMe();
            $this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
            $this->_redirect('/empresa');
        }
//        var_dump($this->auth['empresa']['membresia_info']);exit;
        /* Initialize action controller here */
        $this->_empresa = new Application_Model_Empresa();
        $this->_usuarioempresa = new Application_Model_UsuarioEmpresa();  
        $this->_usuario = new Application_Model_Usuario();
        $this->_anuncioweb = new Application_Model_AnuncioWeb;

        Zend_Layout::getMvcInstance()->assign(
           'bodyAttr', array('id' => 'myAccount')
        );
        $this->idEmpresa = $this->auth['empresa']['id'];
        $this->usuario = $this->auth['usuario'];
        
        if (isset($this->auth["empresa"]))
        {  
          $this->view->tieneBuscador = $this->_anuncioweb->beneficioBuscadorAptitus($this->auth);
          $this->_tieneBolsaCVs=0;
          if(isset($this->auth['empresa']['membresia_info']['membresia']['id_membresia'])){
            $this->_tieneBolsaCVs = !empty($this->auth['empresa']['membresia_info']['beneficios']->bolsacv)?1:0;
          }        
          $this->view->tieneBolsaCVs = $this->_tieneBolsaCVs;
        }
          $this->view->Look_Feel= $this->_empresa->LooFeelActivo($this->auth['empresa']['id'])    ;

    }

    public function indexAction()
    {
        $this->view->menu_sel_side = self::MENU_POST_SIDE_ADMINISTRADORES;
        $this->view->menu_post_sel = self::MENU_POST_MIS_DATOS;
        $this->view->menu_sel = self::MENU_MI_CUENTA;
        $this->view->isAuth = $this->isAuth;
        
        $beneficios = $this->auth['empresa']['membresia_info']['beneficios'];
        $codigo     = Application_Model_Beneficio::CODE_REASIGNAR_PROCESOS;

        $asignarProcesos = false;
        if (isset($beneficios->$codigo)) {
            $asignarProcesos = true;
        }

        $this->view->headScript()->appendFile($this->view->S('/js/src/libs/jquery/jqParsley.js'));
        $this->view->headScript()->appendFile($this->view->S('/js/src/libs/jquery/jqParsley_es.js'));
        $this->view->headScript()->appendFile($this->view->S('/js/empresa/empresa.cuenta.mensajes.js'));
        $this->view->headScript()->appendFile($this->view->S('/js/administrador/administradores.admin.js'));

        $idEmpresa = $this->auth['empresa']['id'];
        $administrador = new Application_Model_UsuarioEmpresa();
        $arrayAdm = $administrador->selectAdministradores($idEmpresa);
        $this->view->arrayAdm = $arrayAdm;
        
        $page = $this->_getParam('page', 1);
        $paginator = Zend_Paginator::factory($arrayAdm);
        $paginator->setItemCountPerPage(5);

        $this->view->mostrando = "Mostrando "
                . $paginator->getItemCount($paginator->getItemsByPage($page)) . " de "
                . $paginator->getTotalItemCount();

        $paginator->setCurrentPageNumber($page);


        $this->view->arrayAdm = $paginator;

        $this->view->idUsuLog           = $this->auth['usuario-empresa']['id'];
        $this->view->rol                = $this->auth['usuario']->rol;
        $this->view->creador            = $this->auth['usuario-empresa']['creador'];
        $this->view->asignarProcesos    = $asignarProcesos;
    }

    public function borrarAdministradoresAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $adminId    = $this->_getParam('id', false);
        $usuarioLogeado  = $this->auth['usuario-empresa'];
        $administrador   = new App_Service_Validate_UserCompany($adminId);
        
        $this->getMessenger()->error('No se puede eliminar');
        
        // --------------------------------------------------> 
        // DA: [02-06-2014 15:00]                     
        // Si $administradorId es invalido no deberia de hacer nada
        if ( $administrador->isNull() ) {
            $this->_redirect('/empresa/administrador');            
        }
        // --------------------------------------------------<
        
        $administradorData  = $administrador->getData();



        if (!$administrador->isCreator($usuarioLogeado)) {
            $this->_redirect('/empresa/administrador');
        }

        if ($adminId == $usuarioLogeado['id']) {
            $this->_redirect('/empresa/administrador');
        }

        if (!$administrador->belongsTo($usuarioLogeado['id_empresa'])) {
            $this->_redirect('/empresa/administrador');
        }

        $logins = $this->_usuario->getRegistrosLogicosAct( $administradorData['id_usuario'] );
        $db     = $this->getAdapter();

        $adminIdUsuario = (int)$administradorData['id_usuario'];

        try {


            $empresa  = $this->_empresa->getEmpresa( $this->idEmpresa ) ;
            $nuevoAdminIdUsuario = $empresa['id_usuario'];

            
            $this->getMessenger()->clearCurrentMessages();
            $this->getMessenger()->success('Administrador Eliminado.');
          
            if ( count($logins)== 0 )
            {

                $usuarioEmpresa = new Application_Model_UsuarioEmpresa();
                $usuarioEmpresa->eliminarCuentaAdmin($adminId, $adminIdUsuario, $nuevoAdminIdUsuario);

                $this->_redirect('/empresa/administrador');
            }

            $this->_usuario->update(
                array('elog'=> Application_Model_Usuario::ELIMINADO),
                $db->quoteInto('id =?', $adminIdUsuario)
            );
            
        } catch (Exception $ex) {
            exit( $ex->getMessage() );
            $this->getMessenger()->clearCurrentMessages();
            $this->getMessenger()->error('No se puede eliminar');
        }

        $this->_redirect('/empresa/administrador');
    }
    
    public function darPrivilegioAdministradorAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $formHash = (object) Zend_Layout::getMvcInstance()->hashForm;
        $dataHash = array('hash'=>$this->_getParam('hash', ''));
        $idUsuAdmPri = $this->auth['usuario']->id;
        
        $idUsuAdmSec = $this->_getParam('id', false);
        $arrayUsuAdmSec = $this->_usuarioempresa->getUsuarioAdmCreator($idUsuAdmSec);
        
        if ($this->auth['usuario']->rol ==Application_Form_Login::ROL_EMPRESA_ADMIN) {
            try {
                $db = $this->getAdapter();
                $db->beginTransaction();
                
                if ($this->_request->isPost()) {
                    if ($formHash->isValid($dataHash)) {
                        //Actualizacion AdmPrincipal en empresa_usuario y usuario
                        $whereA = array('id_usuario = ?'=> $idUsuAdmPri);
                        $data['creador'] = '0';
                        $this->_usuarioempresa->update($data, $whereA);

                        $whereA = array('id =?'=> $idUsuAdmPri );
                        $dataUsuAdm['rol'] = Application_Form_Login::ROL_EMPRESA_USUARIO;
                        $this->_usuario->update($dataUsuAdm, $whereA);

                        //Actualizacion AdmSecundario en empresa_usuario y usuario
                        $whereU = array('id =?'=> $idUsuAdmSec );
                        $data['creador'] = '1';
                        $this->_usuarioempresa->update($data, $whereU);

                        $whereU = array ('id =?' => $arrayUsuAdmSec['id_usuario']);
                        $dataUsu['rol'] = Application_Form_Login::ROL_EMPRESA_ADMIN;
                        $success = $this->_usuario->update($dataUsu, $whereU);
                        
                        $whereEmpresa = array('id =?'=> $this->auth['empresa']['id']);
                        $dataEmp['id_usuario'] = $arrayUsuAdmSec['id_usuario'];
                        $this->_empresa->update($dataEmp,$whereEmpresa);

                        $db->commit();

                        //actualizacion de session
                        if ((bool) $success) {
                            $storage = Zend_Auth::getInstance()->getStorage()->read();
                            $storage['usuario']->rol = $dataUsuAdm['rol'];
                            Zend_Auth::getInstance()->getStorage()->write($storage);
                        }
                        $this->getMessenger()->success('Actualización exitosa.');
                    } else {
                        $this->getMessenger()->error('Formulario Invalido.');
                    }
                } else {
                    $this->getMessenger()->success('No puede Actualizar.');
                }
            } catch (Zend_Db_Exception $e) {
                $db->rollBack();
                echo $e->getMessage();
                $this->getMessenger()->error('Error Actualizando Administrador.');
            } catch (Zend_Exception $e) {
                $this->getMessenger()->error($this->_messageSuccess);
                echo $e->getMessage();
            }
            
        }
        $this->_redirect('/empresa/administrador');
    }
    
    public function nuevoAction()
    {
        $data = '0';
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('editar');
        
        $this->view->action = $this->_request->getActionName();
        $this->view->controller = $this->_request->getControllerName();
        
        $idEmpresa = $this->auth['empresa']['id'];
        $idUsuario = null;
        $dataStr =$this->_getParam('dataStr');
        $dataValue = array();
        parse_str($dataStr, $dataValue);
        
        $formAdm = new Application_Form_Paso1Administrador();
        $formUsu = new Application_Form_Paso1Usuario($idUsuario);
        $formUsu->validadorEmail($idUsuario, 'empresa');

        if ($this->_request->isPost()) {
            
            $allParams = $this->_getAllParams();
            $genPassword =$this->_helper->getHelper('GenPassword');
            $pswd = $genPassword->_genPassword();
            $allParams = array_merge($allParams, $dataValue);
            $allParams['pswd'] = $pswd; 
            $allParams['pswd2'] = $pswd;
            unset($allParams['dataStr']);
            $validAdm = $formAdm->isValid($allParams);
            $formUsu->removeElement('auth_token');
            $validUsu = $formUsu->isValid($allParams);
            
            $usu = new Application_Model_Usuario();
            $noEsEmailRepetido = $usu->existeEmailUsuarioAdmin($allParams['email']);

            if ($validAdm && $validUsu && $noEsEmailRepetido) {
                $valuesUsuarioEmpresa = $formAdm->getValues();
                $valuesUsu = $formUsu->getValues();
                $date = date('Y-m-d H:i:s');
                
                try {
                    $db = $this->getAdapter();
                    $db->beginTransaction();

                    // Datos adicionales q no vienen del form
                    $valuesUsu['salt'] = '';
                    $valuesUsu['rol'] = Application_Form_Login::ROL_EMPRESA_USUARIO;
                    $valuesUsu['activo'] = 1;
                    $valuesUsu['ultimo_login'] = $date;
                    $valuesUsu['fh_registro'] = $date;
                    $valuesUsu['pswd'] =
                        App_Auth_Adapter_AptitusDbTable::generatePassword(
                            $valuesUsu['pswd']
                        );
                    unset($valuesUsu['pswd2']);
                    
                    unset($valuesUsu['auth_token']);
                    $lastId = $this->_usuario->insert($valuesUsu);
                    
                    //Usuario Empresa
                    $valuesUsuarioEmpresa["id_usuario"] = $lastId;
                    $valuesUsuarioEmpresa["id_empresa"] = $idEmpresa;
                    $valuesUsuarioEmpresa["nombres"] =  $allParams["nombres"];
                    $valuesUsuarioEmpresa["apellidos"] = $allParams["apellidos"];
                    $valuesUsuarioEmpresa["puesto"] = $allParams["puesto"];
                    $valuesUsuarioEmpresa["area"] = $allParams["area"];
                    $valuesUsuarioEmpresa["telefono"] = $allParams["telefono"];
                    $valuesUsuarioEmpresa["telefono2"] = $allParams["telefono2"];
                    $valuesUsuarioEmpresa["anexo"] = $allParams["anexo"];
                    $valuesUsuarioEmpresa["anexo2"] = $allParams["anexo2"];
                    $valuesUsuarioEmpresa["extension"] ="";
                    $valuesUsuarioEmpresa["creador"] = 0;
                    unset($valuesUsuarioEmpresa["csrf_token"]);
                    
                    $this->_usuarioempresa->insert($valuesUsuarioEmpresa);
                    
                        
                    $nombreEmpresa = $this->auth['empresa']['nombre_comercial'];
                    
                    $db->commit();
                    $data = '1';
                    $this->_helper->mail->nuevoAdm(
                        array(
                            'to' => $valuesUsu['email'],
                            'nombre' => $allParams["nombres"],
                            'empresa' => $nombreEmpresa,
                            'pswd' => $pswd
                            )
                    );
                    
                    $this->getMessenger()->success('La clave se envió al correo que registró.');
                    
                    
                }catch (Zend_Exception $e) {
                    
                    $this->getMessenger()->error($this->_messageSuccess);
                    echo $e->getMessage();
                }

            } else {
              $error = 1;
            }
            if ($data == '0') {
                $data = '-1';
            }
        }

        $this->view->log = $data;
        $this->view->error = $error;
        $this->view->formAdm = $formAdm;
        $this->view->formUsu = $formUsu;
        
    }
    
    public function editarAction()
    {
        $data = '0';
        $this->_helper->layout->disableLayout();
        //armado de accion
        $this->view->action = $this->_request->getActionName();
        $this->view->controller = $this->_request->getControllerName();
        $this->view->module = $this->_request->getModuleName();
        
        $idUsuario = $this->usuario->id;
        $idEmpresa = $this->auth['empresa']['id'];
        $idAdm = $this->_getParam('id', false);
        
        $token = urldecode($this->_getParam('tok', false));
        
        if ($idAdm && $token) {
            if (crypt($idAdm,$token) !== $token)  {
                exit;
            }
        }
        
        
        //deserealizacion del valor que se envia por jquery
        $dataStr =$this->_getParam('dataStr');
        $dataValue = array();
        parse_str($dataStr, $dataValue);
        

        $administrador = new Application_Model_UsuarioEmpresa();
        $formAdm = new Application_Form_Paso1Administrador();
        $formUsu = new Application_Form_Paso1Usuario($idUsuario);
        
        $arrayAdm = $administrador->getEditarAdministrador($idAdm);
        
        if ($arrayAdm) {
            
            $idUsuario = $arrayAdm['id_usuario'];
        
            $formUsu->validadorEmail($idUsuario, 'empresa');
            $formAdm->setDefaults($arrayAdm);
            $formUsu->setDefaults($arrayAdm);
            if ($this->_request->isPost()) {
                $allParams = $this->_getAllParams();
                $allParams = array_merge($allParams, $dataValue);
                unset($allParams['dataStr']);
                $allParams['pswd'] = $allParams['pswd2'] = 'pruebapass'; 

                $validAdm = $formAdm->isValid($allParams);
                $formUsu->removeElement('auth_token');
                $validUsu = $formUsu->isValid($allParams);

                if ($validAdm && $validUsu) {
                    $valuesUsuarioEmpresa = $formAdm->getValues();
                    $valuesUsu = $formUsu->getValues();
                    $date = date('Y-m-d H:i:s');

                    try {

                        $db = $this->getAdapter();
                        $db->beginTransaction();

                        // Datos adicionales q no vienen del form
                        $valuesUsu['salt'] = '';
                        $valuesUsu['rol'] = $arrayAdm['creador']==0? 
                            Application_Form_Login::ROL_EMPRESA_USUARIO:Application_Form_Login::ROL_EMPRESA_ADMIN;
                        $valuesUsu['activo'] = 1;
                        $valuesUsu['ultimo_login'] = $date;
                        $valuesUsu['fh_edicion'] = $date;
                        unset($valuesUsu['pswd']);
                        unset($valuesUsu['pswd2']);

                        $where=$this->_usuario->getAdapter()
                            ->quoteInto('id = ?', $idUsuario);

                        $this->_usuario->update($valuesUsu, $where);

                        //Usuario Empresa
                        $valuesUsuarioEmpresa["nombres"] = $allParams["nombres"];
                        $valuesUsuarioEmpresa["apellidos"] = $allParams["apellidos"];
                        $valuesUsuarioEmpresa["puesto"] = $allParams["puesto"];
                        $valuesUsuarioEmpresa["area"] = $allParams["area"];
                        $valuesUsuarioEmpresa["telefono"] = $allParams["telefono"];
                        $valuesUsuarioEmpresa["telefono2"] = $allParams["telefono2"];
                        $valuesUsuarioEmpresa["anexo"] = $allParams["anexo"];
                        $valuesUsuarioEmpresa["anexo2"] = $allParams["anexo2"];
                        unset($valuesUsuarioEmpresa["csrf_token"]);

                        $where=$this->_usuarioempresa->getAdapter()
                            ->quoteInto('id = ?', $idAdm);

                        $this->_usuarioempresa->update($valuesUsuarioEmpresa, $where);
                        $db->commit();
                        $data = '1';
                        $this->getMessenger()->success('Actualización exitosa.');


                    } catch (Zend_Db_Exception $e) {
                         $db->rollBack();
                         $this->getMessenger()->success('Error al Actualizar el administrador.');
                         echo $e->getMessage();
                    } catch (Zend_Exception $e) {
                        $this->getMessenger()->error($this->_messageSuccess);
                        echo $e->getMessage();
                    }
                }
                if ($data == '0') {
                    $data = '-1';
                }
            }
        
            
        } else {
            $this->_redirect('/empresa/mi-cuenta');
        }
        
         
        $this->view->log = $data;
        
        $this->view->formAdm = $formAdm;
        $this->view->formUsu = $formUsu;
        $this->view->id = $idAdm;
    }
}