<?php

class Postulante_PerfilDestacadoController extends App_Controller_Action_Postulante {

    private $_tarifa;
    private $_tipoVia;

    const DNI = 'CI';
    const RUC = 'RUC';
    const CEX = 'CEX'; //Carné de extranjería

    protected $_postulante;
    
    public function preDispatch() {
        parent::preDispatch();
        $url = $this->_getParam("url");
        if ($url != "" && !isset($this->auth["postulante"])) {
            $this->_redirect("#loginP-" . $url);
        } else {
            if ($url != "") {
                $this->_redirect(base64_decode($url));
            }
        }
    }

    public function init() {
        parent::init();
        $this->_tarifa = new Application_Model_Tarifa;
        $this->_tipoVia = new Application_Model_TipoVia;
        $this->_postulante = new Application_Model_Postulante;
    }

    public function indexAction() {
        
    }

    public function paso2Action() 
    {
        $config = Zend_Registry::get('config');
        $data = $this->_getAllParams();
    
        
        
        if (!$this->_hasParam('tarifa')) {
            $this->getMessenger()->success('Debe seleccionar un tipo de Perfil Destacado');
            $this->_redirect("/perfil-destacado");
        }

        $updateCV = $this->_postulante->hasDataForApplyJob($this->auth['postulante']['id']);                
        if(!$updateCV){
            $this->getMessenger()->success('Debe completar los datos de tu Perfil y tu Ubicación para obtener tu perfil destacado');
            $this->_redirect("/mi-cuenta/mis-datos-personales"); 
        }

        $idTarifa = $data['tarifa'];
        $dataTarifa = $this->_tarifa->validarTarifasCVDestacados($idTarifa);
     //   var_dump($dataTarifa);exit;
        $tipoDocAuth = $this->auth['postulante']['tipo_doc'];
        //Permite  CI, RUC y carné de extranjería(CEX)
        $tipoDoc = ($tipoDocAuth == 'ce') ? self::CEX : self::DNI;
        $numDoc = $this->auth['postulante']['num_doc'];
        

        //Validar Tarifa CV Destacado
        if (!$dataTarifa) {
            $this->getMessenger()->success('La tarifa no pertenece a un CV Destacado');
            $this->_redirect("/perfil-destacado");
        }

        //Valida CI si existe en Adecsys
        $validaDNI = $this->_helper->PerfilDestacado->validarDocumentoAdecsys($tipoDoc, $numDoc);

        if (empty($validaDNI)) {
            $this->view->val_dni = 0;
        } else {
            $this->view->val_dni = $validaDNI->Id;
        }
        
        //Actualizar ente en APT si en Adecsys es diferente, siempre y cuando exista en ADECSYS Y EN APT
        $adecsysValida = $this->_helper->getHelper('AdecsysValida');
        $tipDoc = strtoupper($this->auth['postulante']['tipo_doc']);
        $dni = $this->auth['postulante']['num_doc'];
        $adecsysValida->compareEnte($tipDoc,$dni);

        $this->view->via = $this->_tipoVia->lista();
        $this->view->id_tarifa = $idTarifa;
        $this->view->precio = $dataTarifa['precio'];
        $this->view->producto = $dataTarifa['nombre'];
        $this->view->dni = $numDoc;
        $this->view->nombres = $this->auth['postulante']['nombres'] . " " . $this->auth['postulante']['apellido_paterno'] .
                " " . $this->auth['postulante']['apellido_materno'];
        $pagoDestacado = new Zend_Session_Namespace('pago_destacado');
        $pagoDestacado->token = $this->view->token = md5(rand());        
        $this->view->moneda = $config->app->moneda;
    }

    //Valida si el RUC ya existe en adecsys, si es así devuelve valor, sino habilita cajas de texto
    //para registrar el ente con ese RUC
    //Se activa solo al ingresar el carácter 11
    public function validaRucAdecsysAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        //Solo peticiones ajax segura
        if (!$this->getRequest()->isXmlHttpRequest()) {
            exit("Acceso denegado");
        }

        $data = $this->_getAllParams();
        $hash = $data['csrfhash'];

        //if ($this->_hash->isValid($hash)) {

            //Prevención de XSS
            $filter = new Zend_Filter_StripTags;
            $ruc = $filter->filter($data['ruc']);

            //Validación de Token
            //WS para validar la existencia del ente con ese RUC en Adecsys
            $validaRUC = $this->_helper->PerfilDestacado->validarDocumentoAdecsys(self::RUC, $ruc);
            $enteId = $validaRUC->Id;
            $nombreEmpresa = $validaRUC->RznSoc_Nombre;
            $tipoVia = $validaRUC->Tip_Calle;
            $direccion = $validaRUC->Tip_Calle . " " . $validaRUC->Nom_Calle . " " . $validaRUC->Num_Pta;

            $dataEmpresa = array(
                'id' => $enteId,
                'nombreEmpresa' => $nombreEmpresa,
                'via' => $tipoVia,
                'dir' => $direccion
            );

            if (is_null($validaRUC)) {
                $dataEmpresa['id'] = 0;
                $dataEmpresa['success'] = 0;
                $dataEmpresa['msg'] = 'No existe en Adecsys';
                echo Zend_Json::encode($dataEmpresa);
            } else {
                $dataEmpresa['success'] = 1;
                $dataEmpresa['msg'] = 'Ya está registrado en Adecsys';
                echo Zend_Json::encode($dataEmpresa);
            }
        //} 
    }
    
     //Valida si el RUC ya existe en adecsys, si es así devuelve valor, sino habilita cajas de texto
    //para registrar el ente con ese RUC
    //Se activa solo al ingresar el carácter 11
    public function validaRucAdecsysPostulanteAction() {

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        //Solo peticiones ajax segura
        if (!$this->getRequest()->isXmlHttpRequest()) {
            exit("Acceso denegado");
        }

        $data = $this->_getAllParams();
        $hash = $data['csrfhash'];

        //if ($this->_hash->isValid($hash)) {

            //Prevención de XSS
            $filter = new Zend_Filter_StripTags;
            $ruc = $filter->filter($data['ruc']);

            //Validación de Token
            //WS para validar la existencia del ente con ese RUC en Adecsys
            $validaRUC = $this->_helper->PerfilDestacado->validarDocumentoAdecsys(self::RUC, $ruc);
            $enteId = $validaRUC->Id;
            $nombreEmpresa = $validaRUC->RznSoc_Nombre;
            $tipoVia = $validaRUC->Tip_Calle;
            $direccion = $validaRUC->Tip_Calle . " " . $validaRUC->Nom_Calle . " " . $validaRUC->Num_Pta;
            $numberDoor=  !empty($validaRUC->Num_Pta)?$validaRUC->Num_Pta:0;
            $data = array(
                'id' => $enteId,
                'nameCompany' => $nombreEmpresa,
                'typeVia' => $tipoVia,
                'address' => $direccion,
                'numberDoor'=>$numberDoor
            );

            if (is_null($validaRUC)) {                
                $dataEmpresa['status'] = 0;
                $dataEmpresa['token'] = CSRF_HASH;
                $dataEmpresa['message'] = 'No existe en Adecsys';
                 $dataEmpresa['data']=array();
                echo Zend_Json::encode($dataEmpresa);
            } else {
                $dataEmpresa['status'] = 1;
                $dataEmpresa['token'] = CSRF_HASH;
                $dataEmpresa['message'] = 'Ya está registrado en Adecsys';
                $dataEmpresa['data']=$data;
                echo Zend_Json::encode($dataEmpresa);
            }
        //} 
    }

}
