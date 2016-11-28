<?php

class App_Service_Adecsys_RegisterEntity
{
    /**
     * @var Application_Model_Empresa
     */
    private $_companyModel;
    
    /**
     * @var Application_Model_Postulante
     */
    private $_postulanteModel;
    
    /**
     * @var Application_Model_EmpresaEnte
     */
    private $_companyEntityModel;
    
    /**
     * @var Application_Model_PostulanteEnte
     */
    private $_postulanteEntityModel;
    
    /**
     * @var Application_Model_AdecsysEnte
     */
    private $_adecsysEnteModel;
    
    /**
     * @var Application_Model_AdecsysContingenciaEnte
     */
    private $_adecsysContingencyEntityModel;
    
    /**
     * @var Application_Model_AdecsysContingenciaPostulanteEnte
     */
    private $_adecsysContingencyEntityPostulanteModel;
    
    /**
     * @var App_Service_Adecsys_GetEntity
     */
    private $_serviceGetEntity;
    
    /**
     * @var App_Service_RegisterTramas
     */
    private $_serviceTramas;
    
    /**
     * @var App_Util_Filter
     */
    private $_filter;
    
    /**
     * @var Application_Model_Compras
     */
    private $_purchasesModel;
    
    private $_webService    = null;
    private $_config        = null;
    private $_aptitus       = null;
    private $_documentType  = null;
    private $_options       = array();
    private $_company       = null;
    private $_postulante       = null;
    
    private $_message       = null;
    private $_codeAdecsys   = null;
    private $_adecsysEntity = null;
    private $_adecsysEnteId = null;
    private $_nameTrama     = 'aptitus';
        
    private $_onlyRegisterAptitus = false;
            
    const MSJ_REGISTERED            = 'Registro exitoso';
    const MSJ_EXIST                 = 'Ya existe en adecsys';
    const MSJ_COMPANY_NOT_EXIST     = 'No existe / No tiene usuario';
    const MSJ_POSTULANTE_NOT_EXIST     = 'No existe / No tiene usuario';
    const MSJ_ERROR                 = 'Ocurrio un error';
    const MSJ_CONTINGENCY           = 'Registrado en contingencia';
    const MSJ_DELETE_CONTINGENCY    = 'Borrado de contingencia';        
    
    public function __construct($webService = null)
    {
        
        $this->_config              = Zend_Registry::get('config');
        $this->_companyModel        = new Application_Model_Empresa;
        $this->_postulanteModel     = new Application_Model_Postulante;
        $this->_companyEntityModel  = new Application_Model_EmpresaEnte;
        $this->_postulanteEntityModel  = new Application_Model_PostulanteEnte;
        $this->_adecsysEnteModel    = new Application_Model_AdecsysEnte;
        $this->_serviceGetEntity    = new App_Service_Adecsys_GetEntity;
        $this->_filter              = new App_Util_Filter;
        $this->_purchasesModel      = new Application_Model_Compra;
        $this->_adecsysContingencyEntityModel = new Application_Model_AdecsysContingenciaEnte;
        $this->_adecsysContingencyEntityPostulanteModel = new Application_Model_AdecsysContingenciaPostulanteEnte;
        $db                         = Zend_Db_Table::getDefaultAdapter();
        
        $this->_serviceGetEntity->setNameTrama($this->_nameTrama);
        
        if ($this->_config->adecsys->proxy->enabled)
            $this->_options = $this->_config->adecsys->proxy->param->toArray();        

        $wsdl                = $this->_config->adecsys->wsdl;
        $this->_documentType = 
                $this->_config->adecsys->parametrosGlobales->Tipo_doc;
        
        $this->_webService = $webService;
        if (is_null($webService))
            $this->_webService = new Adecsys_Wrapper($wsdl, $this->_options);
        
        $this->_aptitus       = new Aptitus_Adecsys($this->_webService, $db);
        $this->_serviceTramas = new App_Service_Adecsys_RegisterTramas(
                $this->_webService);
    }
    
    public function register($companyId)
    {
        $this->_codeAdecsys = null;
        $this->_message     = null;
        
        if (!$this->isValid($companyId))
            return false;

        if ($this->_onlyRegisterAptitus) {
            $this->_registerInAptitus($this->_adecsysEntity, $companyId);
            return true;
        }
                
        $entity     = $this->_getObject($this->_company);
        $typeTrama  = App_Service_Adecsys_RegisterTramas::TYPE_REGISTER_ENTE;
        $nameTrama  = $this->_nameTrama . '_empresa_' . $companyId;
        
        try {
            $response   = $this->_webService->registrarCliente($entity);
            $this->_serviceTramas->register($nameTrama, $typeTrama);
        } catch (Exception $e) {
            $this->_message = self::MSJ_ERROR;
            $this->_registerInContingency($companyId);
            $this->_serviceTramas->register($nameTrama, $typeTrama);
            return false;
        }
        
        if ($response == Adecsys_Wrapper::CODIGO_ERROR) {
            $this->_message = self::MSJ_ERROR;
            $this->_registerInContingency($companyId);
            return false;
        }
        
        $this->_codeAdecsys = $response;
        
        $this->_adecsysEntity = $this->_serviceGetEntity->get(
                $this->_documentType, $this->_company['doc_numero']);
        
        if (is_null($this->_adecsysEntity)) {
            $this->_registerInContingency($companyId);
            return false;
        }
        
        $codAdecsysEnte = $this->_registerInAptitus($this->_adecsysEntity, $companyId);
        
        $this->_purchasesModel->assignAdecsysEnte(
                $codAdecsysEnte, $companyId);
        
        return true;
        
    }
    
    //Registra ente de postulante al pagar su perfil destacado si no tiene reg en Adecsys
    public function registerEntePostulante($postulanteId, $dataDoc = null,$idCompra=null)
    {
        $this->_codeAdecsys = null;
        $this->_message     = null;
        
        if (!$this->isValidPostulante($postulanteId,null)){
            return false;
        }
            

        if ($this->_onlyRegisterAptitus) {
            $this->_registerInAptitusPostulante($this->_adecsysEntity, $postulanteId);
            return true;
        }
        $dataDireccion= $this->_purchasesModel->getDataCompraDireccion($idCompra);       
        $this->_postulante['tipo_via']=$dataDireccion['tipo_via'];
        $this->_postulante['direccion']=$dataDireccion['direccion'];
        $this->_postulante['NroPuerta']=$dataDireccion['NroPuerta'];        
        $entity     = $this->_getObjectPostulante($this->_postulante, null);        
        $typeTrama  = App_Service_Adecsys_RegisterTramas::TYPE_REGISTER_ENTE;
        $nameTrama  = $this->_nameTrama . '_perfil_' . $postulanteId;
        
        try {
            $response   = $this->_webService->registrarCliente($entity);
            $this->_serviceTramas->register($nameTrama, $typeTrama);
        } catch (Exception $e) {
            $this->_message = self::MSJ_ERROR;
            $this->_registerInContingencyPostulante($postulanteId);
            $this->_serviceTramas->register($nameTrama, $typeTrama);
            return false;
        }
        
        if ($response == Adecsys_Wrapper::CODIGO_ERROR) {
            $this->_message = self::MSJ_ERROR;
            $this->_registerInContingencyPostulante($postulanteId);
            return false;
        }
        
        $this->_codeAdecsys = $response;
        
        if (is_null($dataDoc)) {
            $dataDoc['tipo_doc'] = strtoupper($this->_postulante['tipo_doc']);
        }
        
        $this->_adecsysEntity = $this->_serviceGetEntity->get(
                $dataDoc['tipo_doc'], $this->_postulante['doc_numero']);
        
        if (is_null($this->_adecsysEntity)) {
            $this->_registerInContingencyPostulante($postulanteId);
            return false;
        }
        
        $codAdecsysEnte = $this->_registerInAptitusPostulante($this->_adecsysEntity, $postulanteId);
        
        $this->_purchasesModel->assignAdecsysEntePerfil(
                $codAdecsysEnte, $postulanteId);
        
        return true;
    }
    
    //Registra ente en Adecsys cuando no se tiene al realizar el pago del perfil destacado
    public function registerEntePostulanteNuevo($postulanteId, $idCompra)
    {
        $this->_codeAdecsys = null;
        $this->_message     = null;
                
        $sesionRUC = new Zend_Session_Namespace('pago_ruc');
        
        $entity = new Adecsys_Request_RegistrarCliente();
        $entity->Tipo_Documento = $sesionRUC->Tip_Doc;
        $entity->Numero_Documento = $sesionRUC->Num_Doc;
        $entity->Nombres_RznSocial = $sesionRUC->RznSoc_Nombre;
        $entity->Nombre_RznComc = $sesionRUC->RznSoc_Nombre;
        $entity->Tipo_Calle = $sesionRUC->Tip_Calle;
        $entity->Telefono = $sesionRUC->Telf;
        $entity->Nombre_Calle = $sesionRUC->Nom_Calle;
        $entity->Ape_Paterno = $sesionRUC->RznSoc_Nombre;
        $entity->Ape_Materno = $sesionRUC->RznSoc_Nombre;
        
        //Obtener registro de compra_adecsys_ruc
        $car = new Application_Model_CompraAdecsysRuc;
        $data = $car->obteneterRegistroByCompraPostulante($postulanteId, $idCompra);
        
        if ($data) {
            //Setea valores
            $entity->Tipo_Documento = Application_Model_CompraAdecsysRuc::RUC;
            $entity->Numero_Documento = $data['ruc'];
            $entity->Nombres_RznSocial = $data['razon_social'];
            $entity->Nombre_RznComc = $data['razon_social'];
            $entity->Tipo_Calle = $data['tipo_via'];
            $entity->Telefono = $data['telefono'];
            $entity->Nombre_Calle = $data['direccion'];
            $entity->Ape_Paterno = $data['razon_social'];
            $entity->Ape_Materno = $data['razon_social'];
            $entity->Numero_Puerta = (int) $data['nro_puerta'];

        }
        
        unset($sesionRUC->ente_ruc);
        unset($sesionRUC->Tip_Doc);
        unset($sesionRUC->Num_Doc);
        unset($sesionRUC->RznSoc_Nombre);
        unset($sesionRUC->RznCom);
        unset($sesionRUC->Telf);
        unset($sesionRUC->Tip_Calle);
        unset($sesionRUC->Nom_Calle);
                
        $typeTrama  = App_Service_Adecsys_RegisterTramas::TYPE_REGISTER_ENTE;
        $nameTrama  = $this->_nameTrama . '_perfil_' . $postulanteId;
        
        try {
            $response   = $this->_webService->registrarCliente($entity);
            $this->_serviceTramas->register($nameTrama, $typeTrama);
        } catch (Exception $e) {
            $this->_message = self::MSJ_ERROR;
            $this->_registerInContingencyPostulante($postulanteId);
            $this->_serviceTramas->register($nameTrama, $typeTrama);
            return false;
        }
        
        if ($response == Adecsys_Wrapper::CODIGO_ERROR) {
            $this->_message = self::MSJ_ERROR;
            $this->_registerInContingencyPostulante($postulanteId);
            return false;
        }
        
        $this->_codeAdecsys = $response;
        
        $this->_adecsysEntity = $this->_serviceGetEntity->get(
                $entity->Tipo_Documento, $entity->Numero_Documento);
        
        
        $codAdecsysEnte = $this->_registerInAptitusPostulante($this->_adecsysEntity, $postulanteId);
        
        $this->_purchasesModel->assignAdecsysEntePerfil(
                $codAdecsysEnte, $postulanteId);
        
        return $codAdecsysEnte;
        
    }
    
    private function _registerInAptitus($entityRegister, $companyId)
    {
        $this->_adecsysEnteId = $this->_adecsysEnteModel->registrar(
                $entityRegister);
        
        $this->_companyEntityModel->registrar(
                $companyId, $this->_adecsysEnteId);
        
        $this->_message = self::MSJ_REGISTERED . ' - codigo ' . 
                $this->_codeAdecsys; 
        
        $this->_deleteContingency($companyId);
        
        return $this->_adecsysEnteId;
    }
    
    private function _registerInAptitusPostulante($entityRegister, $postulanteId)
    {
        $this->_adecsysEnteId = $this->_adecsysEnteModel->registrar(
                $entityRegister);
        
        $this->_postulanteEntityModel->registrar(
                $postulanteId, $this->_adecsysEnteId);
        
        $this->_message = self::MSJ_REGISTERED . ' - codigo ' . 
                $this->_codeAdecsys; 
        
        $this->_deleteContingencyPostulante($postulanteId);
        
        return $this->_adecsysEnteId;
    }
    
    private function _registerInContingency($companyId)
    {
        $contingency = 
                $this->_adecsysContingencyEntityModel->obtenerPorEmpresa(
                        $companyId);
        if (!isset($contingency)) {
            $this->_adecsysContingencyEntityModel->registrar($companyId);
            $this->_message .= ' - ' . self::MSJ_CONTINGENCY;
        }
    }
    
    private function _registerInContingencyPostulante($postulanteId)
    {
        $contingency = 
                $this->_adecsysContingencyEntityPostulanteModel->obtenerPorPostulante(
                        $postulanteId);
        if (!isset($contingency)) {
            $this->_adecsysContingencyEntityPostulanteModel->registrar($postulanteId);
            $this->_message .= ' - ' . self::MSJ_CONTINGENCY;
        }
    }
    
    private function _deleteContingency($companyId)
    {
        $contingency = 
                $this->_adecsysContingencyEntityModel->obtenerPorEmpresa(
                        $companyId);
        if (isset($contingency)) {
            $this->_adecsysContingencyEntityModel->quitarPorEmpresa(
                    $companyId);
            $this->_message .= ' - ' . self::MSJ_DELETE_CONTINGENCY;
        }
    }
    
    private function _deleteContingencyPostulante($postulanteId)
    {
        $contingency = 
                $this->_adecsysContingencyEntityPostulanteModel->obtenerPorPostulante(
                        $postulanteId);
        if (isset($contingency)) {
            $this->_adecsysContingencyEntityPostulanteModel->quitarPorPostulante(
                    $postulanteId);
            $this->_message .= ' - ' . self::MSJ_DELETE_CONTINGENCY;
        }
    }
    
    private function _assignPurchases()
    {
        $where = $db->quoteInto('id = ?', (int)$compraId);

        $this->_compra->update(
                array('adecsys_ente_id' => $adecsysEnteId), $where);
    }
    
    public function isValid($companyId)
    {
        $this->_codeAdecsys         = null;
        $this->_onlyRegisterAptitus = false;
        
        $this->_company = $this->_companyModel->datosParaEnteAdecsys(
                $companyId);                

        if (empty($this->_company)) {
            $this->_message = self::MSJ_COMPANY_NOT_EXIST;
            return false;
        }
        
        $this->_adecsysEntity = $this->_serviceGetEntity->get(
                $this->_documentType, $this->_company['doc_numero']);        
        
        $entityAptitus = $this->_adecsysEnteModel->obtenerPorDocumento(
                $this->_company['doc_numero']);

        if (!is_null($this->_adecsysEntity) && isset($entityAptitus)) {
            $this->_message     = 
                    self::MSJ_EXIST . ' - codigo ' . $this->_adecsysEntity->Id . 
                    ' - adecsys_ente_id ' . $entityAptitus->id;
            $this->_codeAdecsys = $this->_adecsysEntity->Id;
            return false;
        }

        if (!is_null($this->_adecsysEntity) && !isset($entityAptitus)) {
            $this->_codeAdecsys = $this->_adecsysEntity->Id;
            $this->_onlyRegisterAptitus = true;
        }

        return true;
    }
    
    public function isValidPostulante($postulanteId, $dataDoc = null)
    {
        $this->_codeAdecsys         = null;
        $this->_onlyRegisterAptitus = false;
        
        $this->_postulante = $this->_postulanteModel->datosParaEnteAdecsysPostulante(
                $postulanteId);                

        if (empty($this->_postulante)) {
            $this->_message = self::MSJ_POSTULANTE_NOT_EXIST;
            return false;
        }

        if (is_null($dataDoc)) {
            $dataDoc['tipo_doc'] = strtoupper($this->_postulante['tipo_doc']);
            $dataDoc['doc_numero'] = $this->_postulante['doc_numero'];
        }
        
        $this->_adecsysEntity = $this->_serviceGetEntity->get(
                $dataDoc['tipo_doc'], $dataDoc['doc_numero']);        
        
        $entityAptitus = $this->_adecsysEnteModel->obtenerPorDocumento(
                $dataDoc['doc_numero']);

        if (!is_null($this->_adecsysEntity) && isset($entityAptitus)) {
            $this->_message     = 
                    self::MSJ_EXIST . ' - codigo ' . $this->_adecsysEntity->Id . 
                    ' - adecsys_ente_id ' . $entityAptitus->id;
            $this->_codeAdecsys = $this->_adecsysEntity->Id;
            return false;
        }

        if (!is_null($this->_adecsysEntity) && !isset($entityAptitus)) {
            $this->_codeAdecsys = $this->_adecsysEntity->Id;
            $this->_onlyRegisterAptitus = true;
        }

        return true;
    }
    
    private function _getObject($data)
    {
        $entity                    = $this->_aptitus->getNuevoEnte();
        $entity->Tipo_Documento    = $this->_documentType;
        $entity->Numero_Documento  = (int)$data['doc_numero'];
        $entity->Ape_Paterno       = $this->_filter->escapeAlnum($data['ape_pat']);
        $entity->Ape_Materno       = $this->_filter->escapeAlnum($data['ape_pat']);
        $entity->Nombres_RznSocial = $this->_filter->escapeAlnum(
                $data['razon_social']);
        $entity->Email             = $data['email'];
        $entity->Telefono          = $this->_filter->clearTelephone(
                $data['telefono']);
        $entity->CodCiudad         = (int)$data['ubigeoId'];
        $entity->Tipo_Calle = $data['Ntip_via'];
        $entity->Nombre_Calle =  $data['Ndireccion'];
        $entity->Numero_Puerta =  $data['NroPuerta'];
        $entity->Nombre_RznComc    = $this->_filter->escapeAlnum(
                $data['razonComercial']);
        
        if (empty($data['razonComercial'])) {
            $entity->Nombre_RznComc    = $this->_filter->escapeAlnum(
                    $data['razon_social']);
        }
        return $entity;
    }
    
    private function _getObjectPostulante($data, $dataDoc = null)
    {
        if (is_null($dataDoc)) {
            $dataDoc['tipo'] = strtoupper($data['tipo_doc']);
        }
       
        $entity                    = $this->_aptitus->getNuevoEnte();
        $entity->Tipo_Documento    = $dataDoc['tipo'];
        $entity->Numero_Documento  = $data['doc_numero'];
        $entity->Ape_Paterno       = $this->_filter->escapeAlnum($data['ape_pat']);
        $entity->Ape_Materno       = $this->_filter->escapeAlnum($data['ape_pat']);
        $entity->Nombres_RznSocial = $this->_filter->escapeAlnum(
                $data['razon_social']);
        $entity->Email             = $data['email'];
        $entity->Telefono          = $this->_filter->clearTelephone(
                $data['telefono']);
        $entity->CodCiudad         = (int)$data['ubigeoId'];
        $entity->Nombre_RznComc    = $this->_filter->escapeAlnum(
                $data['razonComercial']);
        
        if (empty($data['razonComercial'])) {
            $entity->Nombre_RznComc = $this->_filter->escapeAlnum(
                    $data['razon_social']);
        }
        $entity->Tipo_Calle = $data['tipo_via'];
        $entity->Nombre_Calle =  $data['direccion'];
        $entity->Numero_Puerta = (int) $data['NroPuerta'];
        
        return $entity;
    }
    
    public function getMessage()
    {
        return $this->_message;
    }
    
    public function getCodeAdecsys()
    {
        return $this->_codeAdecsys;
    }
    
    public function getAdecsysEnteId()
    {
        return $this->_adecsysEnteId;
    }
    
    public function setNameTrama($name)
    {
        $this->_nameTrama = $name;
        $this->_serviceGetEntity->setNameTrama($name);
    }

}
