<?php


class Admin_MembresiaEmpresaController extends App_Controller_Action_Admin
{

    protected $_empresa;
    protected $_usuario;
    protected $_usuarioempresa;
     protected $_cache = null;
    CONST GRUPO_DE_TRABAJO = "Trabaja con Nosotros";

    public function init()
    {
        parent::init();
        $this->_cache = Zend_Registry::get('cache');
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
        $this->view->menu_sel_side = self::MENU_POST_SIDE_MEMBRESIA;

        $this->view->headScript()->appendFile(
            $this->view->S(
                '/js/administrador/micuenta.admin.js')
        );

        $this->view->headLink()->appendStylesheet(
            $this->view->S(
                '/js/datepicker/themes/redmond/ui.all.css', 'all')
        );

        $sess = $this->getSession();
        $this->view->empresaAdminUrl = $this->view->url($sess->empresaAdminUrl,
            'default', false);

        $this->view->idEmpresa = $idEmpresa = $this->_getParam('rel', null);
        $this->view->rol = $this->auth['usuario']->rol;
                       
        $modelEmpresa = new Application_Model_Empresa();
        $arrayEmpresa = $modelEmpresa->getEmpresa($idEmpresa);
        $arrayEM = $this->_empresa->getEmpresaMembresia($idEmpresa);


        $this->view->activo = $arrayEmpresa['activo'];

        $this->view->razonsocial = $arrayEmpresa['razonsocial'];
        $this->view->membresiaTipo = $arrayEM['membresia_info']['membresia']['m_nombre'];

        
        $page = $this->_getParam('page', 1);
        $this->view->col = $col = $this->_getParam('col', '');
        $this->view->ord = $ord = $this->_getParam('ord', 'desc');
        
        $modelMembresia = new Application_Model_Membresia();
                       
        
        $paginator = $modelMembresia->getPaginador(
                $idEmpresa, $col, $ord
        );
        
        $config = Zend_Registry::get('config');
        $paginado = $config->membresias->paginado;        
        $paginator->setItemCountPerPage($paginado);
        $paginator->setCurrentPageNumber($page);
        
        
        $this->view->membresiasEmp = $paginator;
        
//      $this->view->membresiasEmp =
//            Application_Model_Membresia::getMebresiasByEmpresaId($idEmpresa);

        $objEmpMe = new Application_Model_EmpresaMembresia();
        $this->view->activoMembresia = $objEmpMe->getExistsActive($idEmpresa);
    }

    public function listaMembresiasAction()
    {
        $this->_helper->layout->disableLayout();

        $idEmpresa = $this->_getParam('paramIdE', null);
        $this->view->membresiasEmp =
            Application_Model_Membresia::getMebresiasByEmpresaId($idEmpresa);
        $config = Zend_Registry::get('config');
        $this->view->moneda = $config->app->moneda;
    }

    public function getValidMembresiaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $idEmp = $this->_getParam('paramIdE', '');
        $objEmpMe = new Application_Model_EmpresaMembresia();
        $result['active'] = $objEmpMe->getExistsActive($idEmp);
        $this->_response->appendBody(Zend_Json::encode($result));
    }

    public function getMembresiasTipoAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $config = Zend_Registry::get("config");
        $activo = $config->configMembresia->soloActivos;

        $idTip = $this->_getParam('idtipo', '');
        $result = Application_Model_Membresia::getMembresiasByTipo($activo,
                $idTip);
        $this->_response->appendBody(Zend_Json::encode($result));
    }

    public function getDataMembresiaAction()
    {
        $this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNoRender();

        $idMem = $this->getRequest()->getPost('idMem');
        $objDetMem = new Application_Model_MembresiaDetalle();
        @$this->_cache->remove('MembresiaDetalle_getDetalleByMembresia' . $idMem);
        if (in_array($idMem, array(Application_Model_Membresia::PREMIUM,Application_Model_Membresia::SELECTO,Application_Model_Membresia::DIGITAL,Application_Model_Membresia::MENSUAL))) {
            $ds = $objDetMem->getDetalleByMembresia($idMem,true);
        } else {
            $ds = $objDetMem->getDetalleByMembresia($idMem);
        }
        
        
        
        $this->view->beneficios = $ds;
        $objMemb = new Application_Model_Membresia();
        $dataM = $objMemb->getMembresiaById($idMem);
        $this->view->monto = number_format($dataM['monto'], 2, '.', '');
    }

    public function operaMembresiaAction()
    {
        $this->_helper->layout->disableLayout();

        $idMem = $this->getRequest()->getParam('idMem', '');
        $idEmp = $this->getRequest()->getParam('idEmp', '');
        $opera = $this->getRequest()->getParam('opera', '');
        $filter = new Zend_Filter_Int();  
        $idMem = $filter->filter($idMem);
        $this->view->opera = $opera;
        $this->view->idM = $idMem;
        $this->view->idE = $idEmp;

        $frmMemb = new Application_Form_MembresiaEmpresa();

        $config = Zend_Registry::get("config");
        $activo = $config->configMembresia->soloActivos;
        $moneda = $config->app->moneda;

        if ($activo == 0) {
            $frmMemb->getElement('cbotipo')->removeMultiOption('bonificado');
        }

        $frmMemb->setLoadMembresiaByTipo(
            $this->_getParam('cbotipo', ''), ''
        );

        $this->view->beneficios = array();

        $this->view->proceso = 0;
        
        $objMemEmp = new Application_Model_EmpresaMembresia();
        
        if ($this->_request->isPost() && $this->getRequest()->isXmlHttpRequest()) {

            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
        
            $allParams = $this->_getAllParams();
            $validMemb = $frmMemb->isValid($allParams);                                   
            //var_dump($validMemb, $frmMemb->getMessages()); exit;
            if ($validMemb) {
                $this->view->proceso = 1;
                $valuesM = $frmMemb->getValues();
                $valuesM['opera'] = $allParams['opera'];
                $valuesM['idEmpresa'] = $allParams['idEmp'];
                $valuesM['idEmpMem'] = $allParams['idMem'];
                $tipoMembresia = $valuesM['cbotipo'];
                
                $esDigital = ($allParams['id_membresia']==7 || $allParams['id_membresia']== Application_Model_Membresia::DIGITAL || $allParams['id_membresia']== Application_Model_Membresia::MENSUAL) ;               
                if (!$esDigital) {                     
                    $yaExisteContrato = $objMemEmp->existeNroContrato($valuesM['txtcontrato'], $valuesM['idEmpMem']);            
                    if ($yaExisteContrato && ($tipoMembresia === 'membresia') ) {
                        $result = array(
                            'status' => false,
                            'message' => 'El Nro. de Contrato ya existe.'
                        );
                        $this->_response->appendBody(Zend_Json::encode($result));
                        return json_encode($result);
                    } 

                }
                
                $updated = $this->saveMembresia($valuesM);
                if ($updated) {
                    $result = array(
                        'status' => true,
                        'message' => (($valuesM['opera'] == 'N') ? 'Registro Satisfactorio' : 'Actualizacion satisfactoria')
                    );
                } else {
                    $result = array(
                        'status' => false,
                        'message' => 'No se pudo guardar. Por favor vuelva ha intentarlo'
                    );
                }
                
                
                
            } else {
                $result = array(
                    'status' => false,
                    'message' => $frmMemb->getErrorMessages() ? $frmMemb->getErrorMessages() : 'Vuelva ha intentarlo'
                );

            }
            
            
            $this->_response->appendBody(Zend_Json::encode($result));
            return json_encode($result);
            
        } else {
            if (!empty($idMem)) {
                $this->view->opera = 'E';                
                $rsDeta = $objMemEmp->getDetalleEmpresaMembresia($idMem);
                /*
                  $fecIn = new DateTime($rsDeta['membresia']['fh_inicio_membresia']);
                  $fecFi = new DateTime($rsDeta['membresia']['fh_fin_membresia']);

                  $arrayMembresia['txtfecini'] = $fecIn->format('d/m/Y');
                  $arrayMembresia['txtfecfin'] = $fecFi->format('d/m/Y');
                 */

                $fecIn = new Zend_Date($rsDeta['membresia']['fh_inicio_membresia']);
                $fecIn->setLocale(Zend_Locale::ZFDEFAULT);

                $fecFi = new Zend_Date($rsDeta['membresia']['fh_fin_membresia']);
                $fecFi->setLocale(Zend_Locale::ZFDEFAULT);

                $arrayMembresia['txtfecini'] = $fecIn->toString('dd/MM/yyyy');
                $arrayMembresia['txtfecfin'] = $fecFi->toString('dd/MM/yyyy');

                $arrayMembresia['txtmonto'] = number_format($rsDeta['membresia']['monto'],
                    2, '.', '');
                $arrayMembresia['cboestado'] = $rsDeta['membresia']['estado'];
                $arrayMembresia['cbotipo'] = $rsDeta['membresia']['m_tipo'];
                $arrayMembresia['txtcontrato'] = $rsDeta['membresia']['nro_contrato'];

                $frmMemb->setLoadMembresiaByTipo(
                    $rsDeta['membresia']['m_tipo'],
                    $rsDeta['membresia']['id_membresia']
                );
                $frmMemb->setDefaults($arrayMembresia);

                $this->view->beneficios = $rsDeta['beneficios'];
            } else {
                $this->view->opera = 'N';
            }
        }
        $this->view->frmMembresia = $frmMemb;
        $this->view->moneda = $moneda;
    }

    public function saveMembresia($data = '')
    {
        $date = date('Y-m-d H:i:s');
        $db = $this->getAdapter();
        try {
            $db->beginTransaction();

            $modelEmpresa = new Application_Model_Empresa();
            $modelApi = new Application_Model_Api();
            
            $config = Zend_Registry::get('config');
            $administradorPortal = explode(',',$config->membresias->administrador->portal->email);

            switch ($data['opera']) {
                case 'N':
                    $feciniM = $data['txtfecini'];
                    $fecfinM = $data['txtfecfin'];
                    $idM = $data['id_membresia'];
                    $mntoM = str_replace(',', '', $data['txtmonto']);
                    $objfecIni = new Zend_Date($feciniM);
                    $objfecFin = new Zend_Date($fecfinM);

                    $objEmpMemb = new Application_Model_EmpresaMembresia();

                    $idEM = $objEmpMemb->insert(
                        array(
                            'id_empresa' => $data['idEmpresa'],
                            'id_membresia' => $idM,
                            'fh_inicio_membresia' => $objfecIni->toString('yyyy-MM-dd HH:mm:ss'),
                            'fh_fin_membresia' => $objfecFin->toString('yyyy-MM-dd HH:mm:ss'),
                            'creado_por' => $this->auth['usuario']->id,
                            'fh_creacion' => $date,
                            //'modificado_por'=>$this->auth['usuario']->id,
                            //'fh_modificacion'=>$date,
                            'nro_contrato' => $data['txtcontrato'],
                            'monto' => $mntoM,
                            'estado' => $data['cboestado']
                        )
                    );
                                        
                    $objMemEmpDet = new Application_Model_MembresiaEmpresaDetalle();
                    $objMemDet = new Application_Model_MembresiaDetalle();
                    @$this->_cache->remove('MembresiaDetalle_getDetalleByMembresia' . $idM);
                    @$this->_cache->remove('Empresa_getEmpresaHome_');

                    $rsMD = $objMemDet->getDetalleByMembresiaPago($idM);

                    foreach ($rsMD as $key => $value) {
                        $objMemEmpDet->insert(
                            array(
                                'id_empresa_membresia' => $idEM,
                                'id_membresia' => $idM,
                                'id_beneficio' => $value['id_beneficio'],
                                'codigo' => $value['codigo'],
                                'nombre' => $value['nombre'],
                                'descripcion' => $value['desc'],
                                'valor' => $value['valor'],
                                'tipo_beneficio' => $value['tipo_beneficio'],
                                'fh_creacion' => $date,
                                'creado_por' => $this->auth['usuario']->id
                            )
                        );
                    }

                    $arrayEmp = $modelEmpresa->getEmpresa($data['idEmpresa']);
                    $arrayApi = $modelApi->getDatosByIdEmpresa($data['idEmpresa']);
                    $dataPost = array(
                        'force_domain' => isset($arrayApi['id']) ? $arrayApi['force_domain']
                                : null,
                        'domain' => isset($arrayApi['id']) ? $arrayApi['domain']
                                : null,
                        'fecha_ini' => $objfecIni->toString('yyyy-MM-dd HH:mm:ss'),
                        'fecha_fin' => $objfecFin->toString('yyyy-MM-dd HH:mm:ss'),
                        'vigencia' => '1',
                        'usuario' => $arrayEmp['email'],
                        'idempresa' => $data['idEmpresa'],
                        'estado' => 'vigente'
                    );
                    
                    if ($data['cboestado'] != Application_Model_Membresia::TIPO_ESTADO_VIGENTE) {
                      //  $dataPost['estado'] = 'dadobaja';
                    }
                    $bolsaCv = new Application_Model_BolsaCv();

                    $bolsaCv->createGrupoGeneral($data['idEmpresa']);
                    if ($idM == Application_Model_Membresia::PREMIUM || $idM == 6 || $idM == Application_Model_Membresia::SELECTO || $idM == 5) {
                        if ($dataPost['estado'] == 'vigente') {
                            $bolsaCv->createGrupoTcn($data['idEmpresa']);
                        }

                        if (!isset($arrayApi['id'])) {
                            $this->_helper->Api->insertarUsuario($dataPost);
                        } else {
                            $dataPost['idUsuApi'] = $arrayApi['id'];
                            $this->_helper->Api->actualizarUsuario($dataPost);
                        }
                    }

                    $db->commit();
                    //echo '<div id="cntMsjStatus"> Registro grabado satisfactoriamente.</div>';

                    if ($data['cboestado'] == 'vigente') {
                        //Enviar formato de mail
                        $objuse = new Application_Model_Usuario();
                        $rsuse = $objuse->getUsuarioAdminByIdEmpresa($data['idEmpresa']);

                        $modelUsuEmp = new Application_Model_UsuarioEmpresa();
                        $modelEmpresaMembresia = new Application_Model_MembresiaEmpresaDetalle();
                        $beneficios = $modelEmpresaMembresia->obtenerBeneficiosPorEmpresaMembresia($idEM);
                        
                        $datamail = array(
                            'to' => $rsuse['email'],
                            'fecini' => $feciniM,
                            'fecfin' => $fecfinM,
                            'id_empresa' => $data['idEmpresa'],
                            'email' => $rsuse['email'],
                            'beneficios' => $beneficios
                        );
                        switch ($idM) {
                           // case 1: 
                               //     $this->_helper->mail->activarMembresiaEsencial($datamail);
                               //     break;                                
                            case Application_Model_Membresia::SELECTO: 
                                    $this->_helper->mail->activarMembresiaSelecto($datamail);
                                    break;
                            case Application_Model_Membresia::PREMIUM: 
                                    $this->_helper->mail->activarMembresiaPremium($datamail);
                                    break;                                
                        }                        
                    }
                    
                    
                    // Enviar Notificacion a administradores del Portal
                    $modelMembresia = new Application_Model_Membresia();
                    $membresia = $modelMembresia->getMembresiaById($idM);
                    $dataMembMail = array(
                        'Asunto'=>'EDICIÓN Y/O REGISTRO DE UN PLAN DE MEMBRESÍA',
                        'to' => $administradorPortal,
                        'vigencia' => $feciniM.' - '.$fecfinM,
                        'monto' => $mntoM,
                        'planMembresia' => 'Membresía '.$membresia['nombre'],
                        'usuario' => $this->auth['usuario']->email,
                        'empresa' => $arrayEmp['razonsocial'],
                        'estado' => $data['cboestado'],
                        'nroContrato' => $data['txtcontrato']
                    );
                    $this->_helper->mail->notificacionNuevaMembresia($dataMembMail);
                    
                    break;
                case 'E':

                    $feciniM = $data['txtfecini'];
                    $fecfinM = $data['txtfecfin'];
                    $idM = $data['id_membresia'];
                    $mntoM = str_replace(',', '', $data['txtmonto']);
                    $objfecIni = new Zend_Date($feciniM);
                    $objfecFin = new Zend_Date($fecfinM);

                    $objEmpMemb = new Application_Model_EmpresaMembresia();

                    $rsDatos = $objEmpMemb->getRow($data['idEmpMem']);

                    if ($rsDatos['id_membresia'] != $idM) {
                        $objMemEmpDet = new Application_Model_MembresiaEmpresaDetalle();
                        $objMemEmpDet->delete(
                            "id_empresa_membresia = '" . $data['idEmpMem'] . "' 
                             and id_membresia = '" . $rsDatos['id_membresia'] . "'"
                        );
                        $objMemDet = new Application_Model_MembresiaDetalle();
                        $rsMD = $objMemDet->getDetalleByMembresiaPago($idM);

                        foreach ($rsMD as $key => $value) {
                            $objMemEmpDet->insert(
                                array(
                                    'id_empresa_membresia' => $data['idEmpMem'],
                                    'id_membresia' => $idM,
                                    'id_beneficio' => $value['id_beneficio'],
                                    'codigo' => $value['codigo'],
                                    'nombre' => $value['nombre'],
                                    'descripcion' => $value['desc'],
                                    'valor' => $value['valor'],
                                    'tipo_beneficio' => $value['tipo_beneficio'],
                                    'fh_creacion' => $date,
                                    'creado_por' => $this->auth['usuario']->id
                                //,'fh_modificacion'=>$date
                                )
                            );
                        }
                    }
                    
                    $objEmpMemb->update(
                        array(
                        'id_membresia' => $idM,
                        'fh_inicio_membresia' => $objfecIni->toString('yyyy-MM-dd HH:mm:ss'),
                        'fh_fin_membresia' => $objfecFin->toString('yyyy-MM-dd HH:mm:ss'),
                        'modificado_por' => $this->auth['usuario']->id,
                        'fh_modificacion' => $date,
                        'monto' => $mntoM,
                        'nro_contrato' => $data['txtcontrato'],
                        'estado' => $data['cboestado']
                        ), "id = '" . $data['idEmpMem'] . "'"
                    );
                    
                    $bolsaCv = new Application_Model_BolsaCv();
                    $arrayEmp = $modelEmpresa->getEmpresa($data['idEmpresa']);                    
                    $arrayApi = $modelApi->getDatosByIdEmpresa($data['idEmpresa']);
                    
                    if ($idM == Application_Model_Membresia::PREMIUM || $idM == 6 || $idM == Application_Model_Membresia::SELECTO || $idM == 5) {
                        $dataPost = array(
                            'force_domain' => isset($arrayApi['id']) ? $arrayApi['force_domain']
                                    : null,
                            'domain' => isset($arrayApi['id']) ? $arrayApi['domain']
                                    : null,
                            'fecha_ini' => $objfecIni->toString('yyyy-MM-dd HH:mm:ss'),
                            'fecha_fin' => $objfecFin->toString('yyyy-MM-dd HH:mm:ss'),
                            'vigencia' => '1',
                            'usuario' => $arrayEmp['email'],
                            'idempresa' => $data['idEmpresa']
                        );

                        if ($data['cboestado'] != Application_Model_Membresia::TIPO_ESTADO_VIGENTE) {
                            $dataPost['estado'] = 'vigente';
                        } else {
                            $dataPost['estado'] = 'vigente';
                        }

                        if ($arrayApi != false) {
                            $dataPost['idUsuApi'] = $arrayApi['id'];
                            $this->_helper->Api->actualizarUsuario($dataPost);
                        } elseif ($arrayApi == false) {
                            $this->_helper->Api->insertarUsuario($dataPost);
                        } else {
                            //$modelApi->darDeBaja($arrayApi['id']);
                        }

                        if ($dataPost['estado'] == 'vigente') {
                            $bolsaCv->createGrupoTcn($data['idEmpresa']);
                        }
                    } else {
                       // $modelApi->darDeBaja($arrayApi['id']);
                    }
                    @$this->_cache->remove('Empresa_getEmpresaHome_');
                    $db->commit();
                    //echo '<div id="cntMsjStatus"> Se cambiaron los datos con éxito.</div>';
                    
                    
                    
                    if (($rsDatos['id_membresia'] != $idM || $rsDatos['estado'] != $data['cboestado'])
                        && $data['cboestado'] == 'vigente') {
                        //Enviar formato de mail
                        $objuse = new Application_Model_Usuario();
                        $rsuse = $objuse->getUsuarioAdminByIdEmpresa($data['idEmpresa']);

                        $modelUsuEmp = new Application_Model_UsuarioEmpresa();
                        $modelEmpresaMembresia = new Application_Model_MembresiaEmpresaDetalle();
                        $beneficios = $modelEmpresaMembresia->obtenerBeneficiosPorEmpresaMembresia($data['idEmpMem']);
                        
                        $datamail = array(
                            'to' => $rsuse['email'],
                            'fecini' => $feciniM,
                            'fecfin' => $fecfinM,
                            'id_empresa' => $data['idEmpresa'],
                            'email' => $rsuse['email'],
                            'beneficios' => $beneficios
                        );
                        switch ($idM) {
//                            case 1: 
//                                    $this->_helper->mail->activarMembresiaEsencial($datamail);
//                                    break;
                                
                          case Application_Model_Membresia::SELECTO: 

                                    $this->_helper->mail->activarMembresiaSelecto($datamail);
                                    break;                                
                            case Application_Model_Membresia::PREMIUM: 
                                    $this->_helper->mail->activarMembresiaPremium($datamail);
                                    break;                                
                        }
                    }
                    
                    
                    
                    
                    // Enviar Notificacion a administradores del Portal
                    
                            
                    $fFormIniAntes = $objfecIni->toString('yyyy-MM-dd');
                    $fFormFinAntes = $objfecFin->toString('yyyy-MM-dd');
                    
                    $fTableIniAntes = date('Y-m-d',strtotime($rsDatos['fh_inicio_membresia']));
                    $fTableFinAntes = date('Y-m-d',strtotime($rsDatos['fh_fin_membresia']));
                    
                    if ( $rsDatos['id_membresia'] != $idM || 
                            $rsDatos['estado'] != $data['cboestado'] || 
                            $rsDatos['nro_contrato'] != $data['txtcontrato'] || 
                            $rsDatos['monto'] != $data['txtmonto'] || 
                            $fFormIniAntes != $fTableIniAntes || 
                            $fFormFinAntes != $fTableFinAntes 
                        ) {
                        
                        $modelMembresia = new Application_Model_Membresia();
                        $membresiaAntes = $modelMembresia->getMembresiaById($rsDatos['id_membresia']);
                        $membresiaDespues = $modelMembresia->getMembresiaById($idM);

                        $fechaIniAntes = date('d/m/Y',strtotime($rsDatos['fh_inicio_membresia']));
                        $fechaFinAntes = date('d/m/Y',strtotime($rsDatos['fh_fin_membresia']));

                        $dataMembMail = array(
                            'Asunto'=>'EDICIÓN Y/O REGISTRO DE UN PLAN DE MEMBRESÍA',
                            'to' => $administradorPortal,                        
                            'usuario' => $this->auth['usuario']->email,
                            'empresa' => $arrayEmp['razonsocial'],
                            'antes'  => array(
                                'vigencia' => $fechaIniAntes.' - '.$fechaFinAntes,
                                'monto' => number_format($rsDatos['monto'], 2, '.', ','),
                                'planMembresia' => 'Membresía '.$membresiaAntes['nombre'],
                                'estado' => ucfirst($rsDatos['estado']),
                                'nroContrato' => $rsDatos['nro_contrato']
                            ),
                            'despues' => array(
                                'vigencia' => $feciniM.' - '.$fecfinM,
                                'monto' => $mntoM,
                                'planMembresia' => 'Membresía '.$membresiaDespues['nombre'],
                                'estado' => ucfirst($data['cboestado']),
                                'nroContrato' => $data['txtcontrato']
                            ),


                        );
                        $this->_helper->mail->notificacionEdicionMembresia($dataMembMail);
                        
                    }
                    
                    
                    break;
                default:
                    //echo '<div id="cntMsjStatus"> Error.</div>';
                    break;
            }
            
            return true;

            /* $db->commit();
              $this->getMessenger()->success('Se cambiaron los datos con éxito.'); */
        } catch (Zend_Db_Exception $e) {
            //echo $e->getMessage();
            //echo '<div id="cntMsjStatus"> Error.</div>';
            $db->rollBack();
            return false;
        } catch (Zend_Exception $e) {
            //$this->getMessenger()->error($this->_messageSuccess);
            //echo $e->getMessage();
            return false;
        }
    }

    // @codingStandardsIgnoreEnd

}