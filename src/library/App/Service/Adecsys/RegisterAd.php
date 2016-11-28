<?php

class App_Service_Adecsys_RegisterAd
{
    /**
     * @var App_Service_RegisterTramas
     */
    private $_serviceTramas;
    
    /**
     * @var App_Util_Filter
     */
    private $_filter;
    
    /**
     * @var Application_Model_AnuncioWeb
     */
    private $_adModel;
    
    /**
     * @var Application_Model_AnuncioImpresoDetalle
     */
    private $_adPrintedDetail;
    
    /**
     * @var Application_Model_CompraAdecsysCodigo
     */
    private $_buyAdecsysCodeModel;
    
    /**
     * @var Application_Model_AdecsysContingenciaAviso
     */
    private $_adecsysContingencyAdModel;
    
    /**
     * @var App_Service_Adecsys_RegisterEntity
     */
    private $_serviceRegisterEntity;
    
    private $_config         = null;
    private $_aptitus        = null;
    private $_webService     = null;
    private $_options        = array();
    private $_message        = null;
    private $_nameTrama      = 'aptitus';
    private $_preCodeAdecsys = null;
    private $_ad             = null;
    private $_codePrinted    = null;
    private $_documentType   = null;
    private $_date;
    
    const MSJ_ERROR                 = 'Ocurrio un error';
    const MSJ_CONTINGENCY           = 'Registrado en contingencia';
    const MSJ_DELETE_CONTINGENCY    = 'Borrado de contingencia';
    const MSJ_REGISTERED            = 'Registro exitoso';
    const MSJ_ERROR_DATA            = 'Problemas con la informacion ';
    const MSJ_ERROR_ENTE            = 'No tiene ente asignado';
    const MSJ_ERROR_AD_OLD          = 'Anuncio antiguo';
    const MSJ_ERROR_TIPO            = 'No es clasificado';
    
    public function __construct($webService = null)
    {
        $this->_adModel             = new Application_Model_AnuncioWeb;
        $this->_adPrintedDetail     = new Application_Model_AnuncioImpresoDetalle;
        $this->_buyAdecsysCodeModel = new Application_Model_CompraAdecsysCodigo;
        $this->_filter              = new App_Util_Filter;
        
        $this->_serviceRegisterEntity = new App_Service_Adecsys_RegisterEntity;
        
        $this->_adecsysContingencyAdModel = new
                Application_Model_AdecsysContingenciaAviso;
        
        $this->_config              = Zend_Registry::get('config');        
        $db                         = Zend_Db_Table::getDefaultAdapter();
        $this->_preCodeAdecsys      = $this->_config->adecsys->preCodigoAdecsys;
        
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
        
        $this->_date = new Zend_Date;
    }
    
    public function register($adId)
    {
        if (!$this->isValid($adId))
            return false;
        
        if ($this->_ad['medio_pub'] == 
                Application_Model_Tarifa::MEDIOPUB_APTITUS_TALAN) {
            $responseOne = $this->_registerAd(
                    Application_Model_CompraAdecsysCodigo::MEDIO_PUB_APTITUS_COMBO);
            
            if (!$responseOne)
                return false;
            
            $responseTwo = $this->_registerAd(
                    Application_Model_CompraAdecsysCodigo::MEDIO_PUB_TALAN_COMBO);
            
            if (!$responseTwo)
                return false;
            return true;
        }
        
        $response = $this->_registerAd($this->_ad['medio_pub']);
        
        if (!$response) 
            return false;
        
        return true;
    }
    
    public function registerReproceso($adId, $idCac, $compraId, $medioPub)
    {
        
        $this->isValidReproceso($adId);
        
        $response = $this->_registerAdReproceso($medioPub, $idCac, $adId, $compraId);
        return $response;
        
//        if ($this->_ad['medio_pub'] == 
//                Application_Model_Tarifa::MEDIOPUB_APTITUS_TALAN) {
//            $responseOne = $this->_registerAdReproceso(
//                    Application_Model_CompraAdecsysCodigo::MEDIO_PUB_APTITUS_COMBO);
//            
//            if (!$responseOne)
//                return false;
//            
//            $responseTwo = $this->_registerAdReproceso(
//                    Application_Model_CompraAdecsysCodigo::MEDIO_PUB_TALAN_COMBO);
//            
//            if (!$responseTwo)
//                return false;
//            return true;
//        }
//        
//        $response = $this->_registerAdReproceso($this->_ad['medio_pub']);
//        
//        if (!$response) 
//            return false;
//        
//        return true;
    }
    
    private function _registerAd($typePublish)
    {
        $buyAdecsysCodeId = $this->_registerInAptitus($typePublish);
        
        $typePublishAdecsys = $typePublish;
        $combo              = null;
        
        if ($typePublish == 
                Application_Model_CompraAdecsysCodigo::MEDIO_PUB_APTITUS_COMBO) {
            $typePublishAdecsys = 
                    Application_Model_CompraAdecsysCodigo::MEDIO_PUB_APTITUS;
            $combo = Application_Model_CompraAdecsysCodigo::TYPE_COMBO;
        }
        
        if ($typePublish == 
                Application_Model_CompraAdecsysCodigo::MEDIO_PUB_TALAN_COMBO) {
            $typePublishAdecsys = 
                    Application_Model_CompraAdecsysCodigo::MEDIO_PUB_TALAN;
            $combo = Application_Model_CompraAdecsysCodigo::TYPE_COMBO;
        }
        
        $adecsysAd = $this->_getObject(
                $buyAdecsysCodeId, $typePublishAdecsys, $combo);
        
        $extraArray = array(); $descuento = array();
        if ($typePublish != 
                Application_Model_CompraAdecsysCodigo::MEDIO_PUB_TALAN_COMBO) {
            $extra = $this->_getExtra($this->_ad['id_impreso']);
            foreach ($extra as $value) {
                $extraArray[] = $value['adecsys_cod'];
            }
            
        }
        $this->_config              = Zend_Registry::get('config');  
        if(isset($this->_config->extracargosAvisos)){
               $desct = $this->_descuento($this->_ad['id_impreso'],$typePublish);
               foreach ($desct as $value) {
                $descuento[] = $value['adecsys_cod'];
               }
        }
        $extraArray=array_merge($extraArray, $descuento);
        
        if (!$this->_registerInAdecsys($adecsysAd, $extraArray)) {
            $this->_registerInContingency($this->_ad['id']);
            return false;            
        }
        
        $this->_deleteContingency($this->_ad['id']);
        
        $this->_assignCodePrinted($buyAdecsysCodeId);
        
        return true;
    }
    
    private function _registerAdReproceso($typePublish, $idCac, $adId, $compraId)
    {
        $buyAdecsysCodeId = $idCac;
        
        //Obtener id aviso impreso
        $anuncioImpreso = new Application_Model_AnuncioImpreso;
        $dataAI = $anuncioImpreso->obtenerIdCompra($compraId);
        $idAI = $dataAI['id'];
        
        
        $typePublishAdecsys = $typePublish;
        $combo              = null;
        
        if ($typePublish == 
                Application_Model_CompraAdecsysCodigo::MEDIO_PUB_APTITUS_COMBO) {
            $typePublishAdecsys = 
                    Application_Model_CompraAdecsysCodigo::MEDIO_PUB_APTITUS;
            $combo = Application_Model_CompraAdecsysCodigo::TYPE_COMBO;
        }
        
        if ($typePublish == 
                Application_Model_CompraAdecsysCodigo::MEDIO_PUB_TALAN_COMBO) {
            $typePublishAdecsys = 
                    Application_Model_CompraAdecsysCodigo::MEDIO_PUB_TALAN;
            $combo = Application_Model_CompraAdecsysCodigo::TYPE_COMBO;
        }
        
        $adecsysAd = $this->_getObjectReproceso(
                $buyAdecsysCodeId, $typePublishAdecsys, $combo);
        
        $extraArray = array();$descuento = array();
//        if ($typePublish != 
//                Application_Model_CompraAdecsysCodigo::MEDIO_PUB_TALAN_COMBO) {
            $extra = $this->_getExtra($idAI);
            
            foreach ($extra as $value) {
                $extraArray[] = $value['adecsys_cod'];
            }
        //}
        $this->_config              = Zend_Registry::get('config');  
            if(isset($this->_config->extracargosAvisos)){
                   $desct = $this->_descuento($this->_ad['id_impreso'],$typePublish);
                   foreach ($desct as $value) {
                    $descuento[] = $value['adecsys_cod'];
                   }
            }
        $extraArray=array_merge($extraArray, $descuento);
        if (!$this->_registerInAdecsysReproceso($adecsysAd, $compraId, $idCac, $extraArray)) {
            $this->_registerInContingency($adId);
            return false;            
        }
        
        $this->_deleteContingency($adId);
        
        $this->_assignCodePrinted($buyAdecsysCodeId);
        
        return true;
    }
    
    private function _registerInAdecsys($adAdecsys, $extra = array())
    {
        $datePublish = new Zend_Date();       
        $datePublish->setDate($this->_ad['fh_pub_confirmada'], 'YYYY-MM-dd');
        
        $typeTrama = App_Service_Adecsys_RegisterTramas::TYPE_REGISTER_AD;
        $nameTrama = $this->_nameTrama . '_anuncioAdecsys_' . $adAdecsys->Cod_Aviso;
        try {
            $response = $this->_aptitus->publicarAviso(
                    $datePublish, $adAdecsys, $extra);
            $this->_serviceTramas->register($nameTrama, $typeTrama);
        } catch (Exception $e) {
            $this->_message = self::MSJ_ERROR . ': ' . $e->getMessage();
            $this->_serviceTramas->register($nameTrama, $typeTrama);
            return false;
        }
        
        if ($response == Adecsys_Wrapper::CODIGO_ERROR) {
            $this->_message = self::MSJ_ERROR;
            return false;
        }
        
        $this->_codePrinted = $response;
        
        $this->_message = self::MSJ_REGISTERED . ' - codigo impreso :' . $response;                
        
        return true;
    }
    
    private function _registerInAdecsysReproceso($adAdecsys, $idCompra, $idCac, $extra = array())
    {
        $datePublish = new Zend_Date();       
        $datePublish->setDate($this->_ad['fh_pub_confirmada'], 'YYYY-MM-dd');
        
        $typeTrama = App_Service_Adecsys_RegisterTramas::TYPE_REGISTER_AD;
        $nameTrama = $this->_nameTrama . '_anuncioAdecsys_' . $adAdecsys->Cod_Aviso;
        
        $adecsysReproceso = new Application_Model_AdecsysReproceso;
        $dataAR = $adecsysReproceso->validarExistencia($idCompra, $idCac);
        $numRep = 1;
        if (count($dataAR) > 0)
            $numRep = $dataAR[0]['cant_reproceso_adecsys'] + 1;
            
        try {
            //Grabó el código
            $response = $this->_aptitus->publicarAvisoReproceso(
                    $datePublish, $adAdecsys, $idCompra, $idCac, $extra);
            $this->_serviceTramas->registerReproceso($nameTrama, $typeTrama, $numRep);
        } catch (Exception $e) {
            //No se pudo grabar
            $this->_message = self::MSJ_ERROR . ': ' . $e->getMessage();
            $this->_serviceTramas->registerReproceso($nameTrama, $typeTrama, $numRep);
            return false;
        }
        
        if ($response == Adecsys_Wrapper::CODIGO_ERROR) {
            $this->_message = self::MSJ_ERROR;
            return false;
        }
        
        $this->_codePrinted = $response;
        
        $this->_message = self::MSJ_REGISTERED . ' - codigo impreso :' . $response;                
        
        return true;
    }
    
    private function _registerInAptitus($typePublish)
    {
        $data = array();
        $data['id_compra']          = $this->_ad['id_compra'];
        $data['medio_publicacion']  = $typePublish;
        
        return $this->_buyAdecsysCodeModel->insert($data);
    }
    
    private function _registerInAptitusDestacado($typePublish,$idCompra)
    {
        $data = array();
        $data['id_compra']          = $idCompra;
        $data['medio_publicacion']  = $typePublish;
        
        return $this->_buyAdecsysCodeModel->insert($data);
    }
    
    private function _registerInContingency($adId)
    {
        $contingency = 
                $this->_adecsysContingencyAdModel->obtenerPorAviso(
                        $adId);
        if (!isset($contingency)) {
            $this->_adecsysContingencyAdModel->registrar($adId);
            $this->_message .= ' - ' . self::MSJ_CONTINGENCY;
        }
    }
    
    private function _deleteContingency($adId)
    {
        $contingency = 
                $this->_adecsysContingencyAdModel->obtenerPorAviso(
                        $adId);
        if (isset($contingency)) {
            $this->_adecsysContingencyAdModel->quitarPorAviso(
                    $adId);
            $this->_message .= ' - ' . self::MSJ_DELETE_CONTINGENCY;
        }
    }
    
    private function _assignCodePrinted($buyAdecsysCodeId)
    {
        $this->_buyAdecsysCodeModel->asignarCodigoImpreso(
                $buyAdecsysCodeId, $this->_codePrinted);
    }
    
    private function _getExtra($printedId)
    {
        $extra = array();
        if (is_null($printedId))
            return $extra;
        
        return $this->_adPrintedDetail->obtenerPorAnuncio($printedId);
    }
    private function _descuento($printedId,$medio)
    {
        $extra = array();
        if (is_null($printedId))
            return $extra;
        
        $medidepublicacion='descuento';
        if($medio==Application_Model_CompraAdecsysCodigo::MEDIO_PUB_APTITUS_COMBO){
            $medidepublicacion='descuento-aptitus';
        }
        if($medio==Application_Model_CompraAdecsysCodigo::MEDIO_PUB_TALAN_COMBO){
            $medidepublicacion='descuento-talan';
        }
        if($medio==Application_Model_CompraAdecsysCodigo::MEDIO_PUB_TALAN){
            $medidepublicacion='descuento-talan';
        }
        if($medio == Application_Model_CompraAdecsysCodigo::MEDIO_PUB_APTITUS){
            $medidepublicacion='descuento-aptitus';
        }
        return $this->_adPrintedDetail->obtenerDescuentos($printedId,$medidepublicacion);
    }
    public function isValid($adId)
    {
        $this->_ad = $this->_adModel->obtenerParaAdecsys($adId);
        $this->_message = self::MSJ_ERROR_DATA;
        
        if (empty($this->_ad))
            return false;
        
        if ($this->_ad['tipo_anuncio'] !=
                Application_Model_Compra::TIPO_CLASIFICADO) {
            $this->_message = self::MSJ_ERROR_TIPO;
            return false;
        }
        
        if (is_null($this->_ad['medio_pub']))
            return false;
        
        if (is_null($this->_ad['fh_pub_confirmada']))
            return false;
        
        if ($this->_ad['fh_pub_confirmada'] < $this->_date->get('YYYY-MM-dd')) {
            $this->_message = self::MSJ_ERROR_AD_OLD;
            $this->_deleteContingency($this->_ad['id']);
            return false;
        }
        
        if (is_null($this->_ad['ente_cod'])) {
            $this->_message = self::MSJ_ERROR_ENTE;
            if (!$this->_registerEnte())
                return false;
        }
        
        return true;
    }
    
    public function isValidReproceso($adId)
    {
        $this->_ad = $this->_adModel->obtenerParaAdecsysReproceso($adId);
        $this->_message = self::MSJ_ERROR_DATA;
        
        if (empty($this->_ad))
            return false;
        
        if ($this->_ad['tipo_anuncio'] !=
                Application_Model_Compra::TIPO_CLASIFICADO) {
            $this->_message = self::MSJ_ERROR_TIPO;
            return false;
        }
        
        if (is_null($this->_ad['medio_pub']))
            return false;
        
        if (is_null($this->_ad['fh_pub_confirmada']))
            return false;
        
        if ($this->_ad['fh_pub_confirmada'] < $this->_date->get('YYYY-MM-dd')) {
            $this->_message = self::MSJ_ERROR_AD_OLD;
            $this->_deleteContingency($this->_ad['id']);
            return false;
        }
        
        if (is_null($this->_ad['ente_cod'])) {
            $this->_message = self::MSJ_ERROR_ENTE;
            if (!$this->_registerEnte())
                return false;
        }
        
        return true;
    }
    
    private function _registerEnte()
    {
        $response = $this->_serviceRegisterEntity->register(
                $this->_ad['id_empresa']);
        $this->_message .= ' - Ente :' . $this->_serviceRegisterEntity->getMessage();
        if (!$response)
            return false;

        $this->_ad['ente_cod'] = 
                $this->_serviceRegisterEntity->getCodeAdecsys();
        
        return true;
    }
    
    private function _getObject(
            $buyAdecsysCodeId, $typePublishAdecsys, $combo = null)
    {
        $ad     = $this->_aptitus->getAvisoEc();
        $data   = $this->_ad;

        $ad->Cod_Aviso          = $buyAdecsysCodeId;
        $ad->Cod_Cliente        = $data['ente_cod'];
        $ad->Tip_Doc            = $this->_documentType;
        $ad->Num_Doc            = $data['ruc'];
        $ad->RznSoc_Nombre      = $this->_filter->escapeAlnum($data['razon_social']);
        $ad->Nom_Contacto       = $this->_filter->escapeAlnum($data['nombres']);
        $ad->Ape_Pat_Contacto   = $this->_filter->escapeAlnum($data['apellidos']);
        $ad->Ape_Mat_Contacto   = $this->_filter->escapeAlnum($data['apellidos']);
        $ad->Telf_Contacto      = $this->_filter->clearTelephone($data['telefono']);
        if ($data['telefono'] == '')
            $ad->Telf_Contacto  = $this->_filter->clearTelephone($data['telefono2']);

        $ad->Email_Contacto     = $data['email'];
        $ad->Des_Puesto_Titulo  = $data['puesto_nombre'];
        $ad->Texto_Aviso        = $data['texto'] . ' ' . $this->_preCodeAdecsys;
        $ad->Num_Palabras       = $data['numero_palabras'];
        
        $ad->Puesto_Aviso->Puesto_Id = $data['puesto_adecsys_code'];
        $ad->Puesto_Aviso->Esp_Id    = $data['id_especialidad'];            

        $vHelperCast = new App_View_Helper_LuceneCast();
        $nameProduct = $vHelperCast->LuceneCast($data['producto_nombre']);
        
        $ad = $this->_aptitus->completeAd(
            $ad, $data['puesto_tipo'], $typePublishAdecsys,
            $nameProduct, $combo
        );

        return $ad;          
    }
    
    private function _getObjectReproceso(
            $buyAdecsysCodeId, $typePublishAdecsys, $combo = null)
    {
        $ad     = $this->_aptitus->getAvisoEc();
        $data   = $this->_ad;

        $ad->Cod_Aviso          = $buyAdecsysCodeId;
        $ad->Cod_Cliente        = $data['ente_cod'];
        $ad->Tip_Doc            = $this->_documentType;
        $ad->Num_Doc            = $data['ruc'];
        $ad->RznSoc_Nombre      = $this->_filter->escapeAlnum($data['razon_social']);
        $ad->Nom_Contacto       = $this->_filter->escapeAlnum($data['nombres']);
        $ad->Ape_Pat_Contacto   = $this->_filter->escapeAlnum($data['apellidos']);
        $ad->Ape_Mat_Contacto   = $this->_filter->escapeAlnum($data['apellidos']);
        $ad->Telf_Contacto      = $this->_filter->clearTelephone($data['telefono']);
        if ($data['telefono'] == '')
            $ad->Telf_Contacto  = $this->_filter->clearTelephone($data['telefono2']);

        $ad->Email_Contacto     = $data['email'];
        $ad->Des_Puesto_Titulo  = $data['puesto_nombre'];
        $ad->Texto_Aviso        = $data['texto'] . ' ' . $this->_preCodeAdecsys;
        $ad->Num_Palabras       = $data['numero_palabras'];
        
        $ad->Puesto_Aviso->Puesto_Id = $data['puesto_adecsys_code'];
        $ad->Puesto_Aviso->Esp_Id    = $data['id_especialidad'];            

        $vHelperCast = new App_View_Helper_LuceneCast();
        $nameProduct = $vHelperCast->LuceneCast($data['producto_nombre']);
        
        $ad = $this->_aptitus->completeAd(
            $ad, $data['puesto_tipo'], $typePublishAdecsys,
            $nameProduct, $combo
        );

        return $ad;          
    }
        
    private function _getObjectDestacado($buyAdecsysCodeId,$rowAnuncio)
    {
        $ad     = $this->_aptitus->getAvisoDestacado();
        //$data   = $this->_ad;

        $ad->Cod_Aviso          = $buyAdecsysCodeId;
        $ad->Cod_Cliente        = $rowAnuncio['codigoEnte'];
        $ad->Tip_Doc            = $rowAnuncio['tipoDocumento'];
        $ad->Num_Doc            = $rowAnuncio['numDocumento'];
        $ad->RznSoc_Nombre      = $this->_filter->escapeAlnum($rowAnuncio['razonSocial']);
        $ad->Nom_Contacto       = $this->_filter->escapeAlnum($rowAnuncio['nombreContacto']);
        $ad->Ape_Pat_Contacto   = $this->_filter->escapeAlnum($rowAnuncio['apePatContacto']);
        $ad->Ape_Mat_Contacto   = $this->_filter->escapeAlnum($rowAnuncio['apeMatContacto']);
        //$ad->Telf_Contacto      = $this->_filter->clearTelephone($rowAnuncio['telefono']);
        if ($rowAnuncio['telefonoContacto'] == '')
            $ad->Telf_Contacto  = $this->_filter->clearTelephone($rowAnuncio['telefonoContacto2']);
        else
            $ad->Telf_Contacto  = $this->_filter->clearTelephone($rowAnuncio['telefonoContacto']);

        $ad->Email_Contacto     = $rowAnuncio['emailContacto'];
        $ad->Des_Puesto_Titulo  = $rowAnuncio['nombreTipoPuesto'];
        $ad->Texto_Aviso        = $rowAnuncio['textoAnuncioImpreso'] . ' ' . $this->_preCodeAdecsys;
        $ad->Num_Palabras       = 10;//$rowAnuncio['numero_palabras'];
        
        $ad->Puesto_Aviso->Puesto_Id = $rowAnuncio['puestoAdecsysCode'];
        $ad->Puesto_Aviso->Esp_Id    = 0;//$rowAnuncio['puestoIdEspecialidad'];            

        $ad = $this->_aptitus->completeAdDestacado($ad, 'destacado','aptitus');

        return $ad;          
    }
    
    public function setNameTrama($name)
    {
        $this->_nameTrama = $name;
    }
    
    public function getCodePrinted()
    {
        return $this->_codePrinted;
    }
    
    public function getMessage()
    {
        return $this->_message;
    }
    
    private function _registerInAdecsysDestacado($adAdecsys)
    {
        $datePublish = new Zend_Date();       
        //$datePublish->setDate($this->_ad['fh_pub_confirmada'], 'YYYY-MM-dd');
        
        $typeTrama = App_Service_Adecsys_RegisterTramas::TYPE_REGISTER_AD;
        $nameTrama = $this->_nameTrama . '_anuncioAdecsys_' . $adAdecsys->Cod_Aviso;
        try {
            $response = $this->_aptitus->publicarAvisoDestacado(
                    $datePublish, $adAdecsys);
            $this->_serviceTramas->register($nameTrama, $typeTrama);
        } catch (Exception $e) {
            $this->_message = self::MSJ_ERROR . ': ' . $e->getMessage();
            $this->_serviceTramas->register($nameTrama, $typeTrama);
            return false;
        }
        
        if ($response == Adecsys_Wrapper::CODIGO_ERROR) {
            $this->_message = self::MSJ_ERROR;
            return false;
        }
        
        $this->_codePrinted = $response;
        
        $this->_message = self::MSJ_REGISTERED . ' - codigo impreso :' . $response;                
        
        return true;
    }
    
    public function _registerAdDestacado($rowAnuncio) 
    {
        $buyAdecsysCodeId = $this->_registerInAptitusDestacado('aptitus',$rowAnuncio['compraId']);
        
        $adecsysAd = $this->_getObjectDestacado(
                $buyAdecsysCodeId, $rowAnuncio);
        
        $this->_registerInAdecsysDestacado($adecsysAd);
        
    }
    
}