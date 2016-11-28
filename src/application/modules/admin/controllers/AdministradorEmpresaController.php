<?php


class Admin_AdministradorEmpresaController
    extends App_Controller_Action_Admin
{

    protected $_empresa;
    protected $_usuario;
    protected $_usuarioempresa;

    public function init()
    {
        parent::init();

        /* Initialize action controller here */
        $this->_empresa = new Application_Model_Empresa();
        $this->_usuarioempresa = new Application_Model_UsuarioEmpresa();
        $this->_usuario = new Application_Model_Usuario();

        Zend_Layout::getMvcInstance()->assign(
            'bodyAttr', array('id' => 'myAccount')
        );
    }

    public function indexAction()
    {
        $this->view->menu_sel_side = self::MENU_POST_SIDE_ADMINISTRADORES;

        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/src/libs/jquery/jqParsley.js')
        );
        $this->view->headScript()->appendFile(
                $this->view->S(
                '/js/src/libs/jquery/jqParsley_es.js')
        );

        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/admin.cuenta.mensajes.js'
            )
        );
        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/micuenta.admin.js'
            )
        );
        $sess = $this->getSession();
        $this->view->empresaAdminUrl = $this->view->url($sess->empresaAdminUrl,
            'default', false);

        $this->view->idEmpresa = $idEmpresa = $this->_getParam('rel', null);
        $modelUsuEmp = new Application_Model_UsuarioEmpresa();
        Zend_Layout::getMvcInstance()->assign(
             'hashForm', new Application_Form_HashForm()
         );       

        $modelEmpresa = new Application_Model_Empresa();
        $arrayEmpresa = $modelEmpresa->getEmpresa($idEmpresa);
        $arrayEM = $this->_empresa->getEmpresaMembresia($idEmpresa);
        
        $administrador = new Application_Model_UsuarioEmpresa();
        $arrayAdm = $administrador->selectAdministradores($idEmpresa);
        $this->view->arrayAdm = $arrayAdm;
        $this->view->rol = 'empresa-admin';
         $this->view->id_usuario = $arrayEmpresa["id_usuario"];
        $page = $this->_getParam('page', 1);
        $paginator = Zend_Paginator::factory($arrayAdm);
        $paginator->setItemCountPerPage(5);

        $this->view->mostrando = "Mostrando "
                . $paginator->getItemCount($paginator->getItemsByPage($page)) . " de "
                . $paginator->getTotalItemCount();
        $paginator->setCurrentPageNumber($page);
        $this->view->arrayAdm = $paginator;

        $this->view->membresiaTipo = $arrayEM['membresia_info']['membresia']['m_nombre'];
        $this->view->activo = $arrayEmpresa['activo'];
        $this->view->razonsocial = $arrayEmpresa['razonsocial'];
    }

    public function borrarAdministradoresAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $anuncioUsuarioEmpresa = new Application_Model_AnuncioUsuarioEmpresa; 
        
        $idAdministrador = $this->_getParam('id', false);
        $idEmp = $this->_getParam('idEm', false);
        $tok = $this->_getParam('tok', false);        
        
        if ($idAdministrador && $idEmp && $tok) {
            $tok = urldecode($tok);
            if (crypt($idAdministrador,$tok) !== $tok) {                
                $this->getMessenger()->error('Este Administrador no puede ser Eliminado!');                
            } else {                
                $data = $this->_usuarioempresa->getUsuarioAdmCreator($idAdministrador);
                if ($data['creador'] == 0) {            
                    $anuncioUsuarioEmpresa->quitarPorAdministrador($idAdministrador);

                    //delete usuario Administrador
                    $whereUE = array('id=?' => $idAdministrador);
                    $this->_usuarioempresa->delete($whereUE);

                    //delete usuario
//                    $whereU = array('id =?' => $data['id_usuario']);
//                    $this->_usuario->delete($whereU);                                        
                    $this->_usuario->update(array(
                        'elog' => 1,
                        'activo' => 0
                    ),array(
                        'id = ?' => $data['id_usuario']
                    ));

                    $this->getMessenger()->success('Administrador Eliminado.');
                } else {
                    $this->getMessenger()->error('Este Administrador no puede ser eliminado, porque es principal!');
                }
            }
            
            
        }
        
        

        $this->_redirect('/admin/administrador-empresa/index/rel/' . $idEmp);
    }

    public function nuevoAction()
    {
        /**/ $data = '0';
          $this->_helper->layout->disableLayout();

          $this->view->action = $this->_request->getActionName();
          $this->view->controller = $this->_request->getControllerName();

          $this->view->idEmpresa = $idEmpresa = $this->_getParam('idEmp', null);

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
          $valuesUsu['rol'] = Application_Model_Rol::EMPRESA_USUARIO;
          $valuesUsu['activo'] = 1;
          $valuesUsu['ultimo_login'] = $date;
          $valuesUsu['fh_registro'] = $date;
          $valuesUsu['pswd'] =
          App_Auth_Adapter_AptitusDbTable::generatePassword(
          $valuesUsu['pswd']
          );
          unset($valuesUsu['pswd2']);

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

          $this->_usuarioempresa->insert($valuesUsuarioEmpresa);
          $db->commit();
          $data = '1';
          $utilMail = $this->_helper->mail->nuevoAdm(
          array('to' => $valuesUsu['email'], 'pswd' => $pswd )
          );

          $this->getMessenger()->success('La clave se envió al correo que registro.');
          } catch (Zend_Db_Exception $e) {
          $db->rollBack();
          $this->getMessenger()->error('Error al registrar el administrador!.');
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

          $this->view->log = $data;
          $this->view->formAdm = $formAdm;
          $this->view->formUsu = $formUsu; 
    }

    public function editarAction()
    {
        $error = 0;
        $data = '0';
        $this->_helper->layout->disableLayout();
        //armado de accion
        $this->view->action = $this->_request->getActionName();
        $this->view->controller = $this->_request->getControllerName();
        $this->view->module = $this->_request->getModuleName();

        $this->view->idEmpresa = $idEmpresa = $this->_getParam('idEmp', null);
        $idUsuAdm = $this->auth['usuario']->id;
        $idAdm = $this->_getParam('id', false);

        //deserealizacion del valor que se envia por jquery
        $dataStr = $this->_getParam('dataStr');
        $dataValue = array();
        parse_str($dataStr, $dataValue);


        $administrador = new Application_Model_UsuarioEmpresa();
        $formAdm = new Application_Form_Paso1Administrador();
        $formUsu = new Application_Form_Paso1Usuario($idUsuAdm);

        $arrayAdm = $administrador->getEditarAdministrador($idAdm);
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
                    $valuesUsu['rol'] = $arrayAdm['creador'] == 0 ?
                        Application_Form_Login::ROL_EMPRESA_USUARIO : Application_Form_Login::ROL_EMPRESA_ADMIN;
                    $valuesUsu['activo'] = 1;
                    $valuesUsu['ultimo_login'] = $date;
                    $valuesUsu['fh_registro'] = $date;
                    unset($valuesUsu['pswd']);
                    unset($valuesUsu['pswd2']);

                    $where = $this->_usuario->getAdapter()
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

                    $where = $this->_usuarioempresa->getAdapter()
                        ->quoteInto('id = ?', $idAdm);

                    $this->_usuarioempresa->update($valuesUsuarioEmpresa, $where);
                    $db->commit();
                    $data = '1';
                    $this->getMessenger()->success('Actualización exitosa.');
                    
                    //update  WS
                    /*
                    $adecsysEnte = new Application_Model_AdecsysEnte();
                    $_ente_cod = $adecsysEnte->getEntePorEmpresa($arrayEmpresa['id_empresa']);

                    $dataCliente = array(
                        'Cod_Cliente' => $_ente_cod,
                        'Tipo_Documento' => '',
                        'Numero_Documento' => '',
                        'Nombres' => $allParams["nombres"],
                        'Apellidos' => $allParams["apellidos"],
                        'Telefono' => $allParams["telefono"],
                        'Rzn_Social' => '',
                        'Rzn_Comercial' => '',
                        );*/


                } catch (Zend_Db_Exception $e) {
                    $db->rollBack();
                    $this->getMessenger()->success('Error al Actualizar el administrador.');
                    echo $e->getMessage();
                } catch (Zend_Exception $e) {
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
        $this->view->id = $idAdm;
    }
    public function darPrivilegioAdministradorAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $formHash = (object) Zend_Layout::getMvcInstance()->hashForm;
        $dataHash = array('hash'=>$this->_getParam('hash', ''));
        $idUsuAdmPri = $this->_getParam('usuarioP', false);
        $idempresa = $this->_getParam('empresa', false);
        $idUsuAdmSec = $this->_getParam('id', false);
        $arrayUsuAdmSec = $this->_usuarioempresa->getUsuarioAdmCreator($idUsuAdmSec);
        
        if ($this->auth['usuario']->rol ==Application_Form_Login::ROL_ADMIN_MASTER) {
            try {
                $db = $this->getAdapter();
                $db->beginTransaction();
                
                if (!$this->getRequest()->isPost()) {
                  //  if ($formHash->isValid($dataHash)) {
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
                        
                        $whereEmpresa = array('id =?'=> $idempresa);
                        $dataEmp['id_usuario'] = $arrayUsuAdmSec['id_usuario'];
                        $this->_empresa->update($dataEmp,$whereEmpresa);

                        $db->commit();

                        //actualizacion de session
//                        if ((bool) $success) {
//                            $storage = Zend_Auth::getInstance()->getStorage()->read();
//                            $storage['usuario']->rol = $dataUsuAdm['rol'];
//                            Zend_Auth::getInstance()->getStorage()->write($storage);
//                        }
                        $this->getMessenger()->success('Actualización exitosa.');
                   // } 
                } else {
                    $this->getMessenger()->success('No puede Actualizar.');
                }
            } catch (Zend_Db_Exception $e) {
                $db->rollBack();
                echo $e->getMessage();
                $this->getMessenger()->error('Error Actualizando Administrador.');
            } catch (Zend_Exception $e) {
                $this->getMessenger()->error('Error Actualizando Administrador .');
                echo $e->getMessage();
            }
            
        }
        $this->_redirect('/admin/administrador-empresa/index/rel/'.$idempresa);
    }

}