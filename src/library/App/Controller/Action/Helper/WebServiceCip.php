<?php

class App_Controller_Action_Helper_WebServiceCip extends Zend_Controller_Action_Helper_Abstract {

    private $_clienteCip;

    public function __construct() {
        $this->_config = Zend_Registry::get('config');
        $this->_clienteCip = new Zend_Soap_Client(
                $this->_config->urlsComprarAviso->CIP->url
        );
    }

    public function generarCip($rowAnuncio) {
        //$d = new Zend_Date();
        $d = new DateTime(date("Y-m-d"));
        //$d->add($this->_config->cip->diasVigencia, Zend_date::DAY);
        $d->add(new DateInterval('P' . $this->_config->cip->diasVigencia . 'D'));
        //$rowAnuncio['fechaExpiracion'] = $d->toString('dd/MM/YYYY');
        $rowAnuncio['fechaExpiracion'] = $d->format('d/m/Y');
        //$rowAnuncio['fechaExpericacion'] = $d->toString('YYYY-MM-dd');
        $params = array(
            "CAPI" => $this->_config->configCip->capi,
            "CClave" => $this->_config->configCip->clave,
            "Email" => $rowAnuncio['empresaMail'],
            "Password" => "",
            "Xml" => $this->generarXmlEncriptado($rowAnuncio)
        );

        try {
            $response = $this->_clienteCip->GenerarCip(array('request' => $params));

            @unlink(APPLICATION_PATH . '/../logs/CIP_' . $rowAnuncio['compraId'] . '_GenerarCip_envio.xml');
            file_put_contents(
                    APPLICATION_PATH . '/../logs/CIP_' .
                    $rowAnuncio['compraId'] . '_GenerarCip_envio.xml', $this->_clienteCip->getLastRequest(), FILE_APPEND
            );

            @unlink(APPLICATION_PATH . '/../logs/CIP_' . $rowAnuncio['compraId'] . '_GenerarCip_rpta.xml');
            file_put_contents(
                    APPLICATION_PATH . '/../logs/CIP_' .
                    $rowAnuncio['compraId'] . '_GenerarCip_rpta.xml', $this->_clienteCip->getLastResponse(), FILE_APPEND
            );
        } catch (Exception $ex) {

            @unlink(APPLICATION_PATH . '/../logs/ErrorCIP_' . $rowAnuncio['compraId'] . '_GenerarCip_envio.xml');
            file_put_contents(
                    APPLICATION_PATH . '/../logs/ErrorCIP_' .
                    $rowAnuncio['compraId'] . '_GenerarCip_envio.xml', $this->_clienteCip->getLastRequest(), FILE_APPEND
            );

            @unlink(APPLICATION_PATH . '/../logs/ErrorCIP_' . $rowAnuncio['compraId'] . '_GenerarCip_rpta.xml');
            file_put_contents(
                    APPLICATION_PATH . '/../logs/ErrorCIP_' .
                    $rowAnuncio['compraId'] . '_GenerarCip_rpta.xml', $this->_clienteCip->getLastResponse(), FILE_APPEND
            );
            //var_dump($ex);
            //error ws generar pago efectivo
            exit;
        }


        // @codingStandardsIgnoreStart
        $objRpta = $response->GenerarCIPResult;
        $cip = $objRpta->CIP;
        // @codingStandardsIgnoreEnd
        $rpta = array(
            'numero' => $cip,
            //'fechaExpiracion' => $d->toString('YYYY-MM-dd')
            'fechaExpiracion' => $d->format('Y-m-d')
        );
        return $rpta;
    }

    public function generarCipPerfil($rowPerfil) {
        
        $d = new DateTime(date("Y-m-d"));
        $d->add(new DateInterval('P' . $this->_config->cip->diasVigenciaPerfil . 'D'));
        $rowPerfil['fechaExpiracion'] = $d->format('d/m/Y');
        $params = array(
            "CAPI" => $this->_config->configCip->capi,
            "CClave" => $this->_config->configCip->clave,
            "Email" => $rowPerfil['postulanteMail'],
            "Password" => "",
            "Xml" => $this->generarXmlEncriptadoPerfil($rowPerfil)
        );

        try {
            $response = $this->_clienteCip->GenerarCip(array('request' => $params));

            @unlink(APPLICATION_PATH . '/../logs/CIP_' . $rowPerfil['compraId'] . '_GenerarCipPerfil_envio.xml');
            file_put_contents(
                    APPLICATION_PATH . '/../logs/CIP_' .
                    $rowPerfil['compraId'] . '_GenerarCipPerfil_envio.xml', $this->_clienteCip->getLastRequest(), FILE_APPEND
            );

            @unlink(APPLICATION_PATH . '/../logs/CIP_' . $rowPerfil['compraId'] . '_GenerarCipPerfil_rpta.xml');
            file_put_contents(
                    APPLICATION_PATH . '/../logs/CIP_' .
                    $rowPerfil['compraId'] . '_GenerarCipPerfil_rpta.xml', $this->_clienteCip->getLastResponse(), FILE_APPEND
            );
        } catch (Exception $ex) {

            @unlink(APPLICATION_PATH . '/../logs/ErrorCIP_' . $rowPerfil['compraId'] . '_GenerarCipPerfil_envio.xml');
            file_put_contents(
                    APPLICATION_PATH . '/../logs/ErrorCIP_' .
                    $rowPerfil['compraId'] . '_GenerarCipPerfil_envio.xml', $this->_clienteCip->getLastRequest(), FILE_APPEND
            );

            @unlink(APPLICATION_PATH . '/../logs/ErrorCIP_' . $rowPerfil['compraId'] . '_GenerarCipPerfil_rpta.xml');
            file_put_contents(
                    APPLICATION_PATH . '/../logs/ErrorCIP_' .
                    $rowPerfil['compraId'] . '_GenerarCipPerfil_rpta.xml', $this->_clienteCip->getLastResponse(), FILE_APPEND
            );
            exit;
        }


        // @codingStandardsIgnoreStart
        $objRpta = $response->GenerarCIPResult;
        $cip = $objRpta->CIP;
        // @codingStandardsIgnoreEnd
        $rpta = array(
            'numero' => $cip,
            'fechaExpiracion' => $d->format('Y-m-d')
        );
        return $rpta;
    }
  public function generarCipCompraMembresia($rowAnuncio)
    {
        $d = new DateTime(date("Y-m-d"));
        $d->add(new DateInterval('P'.$this->_config->cip->diasVigenciaMembresia.'D'));
        $rowAnuncio['fechaExpiracion'] = $d->format('d/m/Y');
        $params = array(
            "CAPI" => $this->_config->configCip->capi,
            "CClave" => $this->_config->configCip->clave,
            "Email" => $rowAnuncio['empresaMail'],
            "Password" => "",
            "Xml" => $this->generarXmlEncriptadoCompraMembresia($rowAnuncio)
        );
        
        try{
            $response = $this->_clienteCip->GenerarCip(array('request' => $params));
            file_put_contents(
                APPLICATION_PATH . '/../logs/CIP_'. 
                $rowAnuncio['compraId'].'_GenerarCip_envio.xml', $this->_clienteCip->getLastRequest(), FILE_APPEND
            );
            file_put_contents(
                APPLICATION_PATH . '/../logs/CIP_'. 
                $rowAnuncio['compraId'].'_GenerarCip_rpta.xml', $this->_clienteCip->getLastResponse(), FILE_APPEND
            );
        } catch (Exception $ex) {
            file_put_contents(
                APPLICATION_PATH . '/../logs/ErrorCIP_'. 
                $rowAnuncio['compraId'].'_GenerarCip_envio.xml', $this->_clienteCip->getLastRequest(), FILE_APPEND
            );
            file_put_contents(
                APPLICATION_PATH . '/../logs/ErrorCIP_'. 
                $rowAnuncio['compraId'].'_GenerarCip_rpta.xml', $this->_clienteCip->getLastResponse(), FILE_APPEND
            );
            exit;
        }
        
        
        // @codingStandardsIgnoreStart
        $objRpta = $response->GenerarCIPResult;
        $cip = $objRpta->CIP;
        // @codingStandardsIgnoreEnd
        $rpta = array(
            'numero' => $cip,
            'fechaExpiracion' => $d->format('Y-m-d')
        );
        return $rpta;
    }

    public function eliminarCip($cip) {
        $params = array(
            "CAPI" => $this->_config->configCip->capi,
            "CClave" => $this->_config->configCip->clave,
            "CIP" => $cip,
            "InfoRequest" => ''
        );
        $response = $this->_clienteCip->EliminarCIP(array('request' => $params));
        //echo $response->GenerarCIPResult;
        // @codingStandardsIgnoreStart
        return $response->EliminarCIPResult;
        // @codingStandardsIgnoreEnd
    }

    public function consultaCip($cip) {
        $params = array(
            "CAPI" => $this->_config->configCip->capi,
            "CClave" => $this->_config->configCip->clave,
            "CIP" => $cip,
            "InfoRequest" => ''
        );
        $response = $this->_clienteCip->ConsultarCIP(array('request' => $params));
        //echo $response->GenerarCIPResult;
        // @codingStandardsIgnoreStart
        return $response->ConsultarCIPResult;
        // @codingStandardsIgnoreEnd
    }
   
    private function generarXmlEncriptadoCompraMembresia($rowAnuncio)
    {
    
            $urlOK="/empresa/comprar-membresia-anual/ok-pago-efectivo";
            $UrlError="/empresa/comprar-membresia-anual/pago-membresia/Membresia/". $rowAnuncio['idmembresia'];
        
       

        $objDom = new DOMDocument('1.0', 'UTF-8');
        $objDom->preserveWhiteSpace = false;
        $objDom->formatOutput = false;
        $root = $objDom->appendChild($objDom->createElement("OrdenPago"));
        $sxe = simplexml_import_dom($objDom);
        $sxe->addChild("IdMoneda", 1);
        $sxe->addChild("Total", $rowAnuncio['totalPrecio']);
        $sxe->addChild("MerchantId", "APT");
        $sxe->addChild("OrdenIdComercio", $rowAnuncio['compraId']);
        $sxe->addChild(
                "UrlOk", htmlspecialchars(
                        $this->_config->app->siteUrl . $urlOK
                )
        );
        $sxe->addChild(
                "UrlError", htmlspecialchars(
                        $this->_config->app->siteUrl .$UrlError
                                       )
        );
        $sxe->addChild("MailComercio", ""); //CUAL ES???
        $sxe->addChild("FechaAExpirar", $rowAnuncio['fechaExpiracion'] . " 00:00:00");
        $sxe->addChild("UsuarioId", $rowAnuncio['usuarioId']);
        $sxe->addChild("DataAdicional", "");
        $sxe->addChild("UsuarioNombre", $rowAnuncio['empresaRazonSocial']);
        $sxe->addChild("UsuarioApellidos", $rowAnuncio['empresaRazonSocial']);
        $sxe->addChild("UsuarioLocalidad", "");
        $sxe->addChild("UsuarioProvincia", "");
        $sxe->addChild("UsuarioPais", "");
        $sxe->addChild("UsuarioAlias", "");
        $sxe->addChild("UsuarioEmail", $rowAnuncio['empresaMail']); //= E-mail del Service
        $ad = $sxe->addchild("Detalles");
        $detalle = $ad->addChild("Detalle");
        $detalle->addChild("Cod_Origen", "");
        $detalle->addChild("TipoOrigen", "");
        $detalle->addChild(
                "ConceptoPago", "Aptitus " .
                $rowAnuncio['nombreProducto'] . " #" .
                $rowAnuncio['compraId']
        );
        $detalle->addChild("Importe", $rowAnuncio['totalPrecio']);
        $detalle->addChild("Campo1", "");
        $detalle->addChild("Campo2", "");
        $detalle->addChild("Campo3", "");
        $objDom->loadXML($sxe->asXML());
        
        $criptex = new App_Controller_Action_Helper_WebServiceEncriptacion();
        return $criptex->encriptaCadena($objDom->saveXML());
    }
        private function generarXmlEncriptado($rowAnuncio)
    {
    
   
            $urlOK="/empresa/comprar-aviso/ok-pago-efectivo";
            $UrlError="/empresa/publica-aviso/paso4/aviso/". $rowAnuncio['anuncioId'];
        
       
        $objDom = new DOMDocument('1.0', 'UTF-8');
        $objDom->preserveWhiteSpace = false;
        $objDom->formatOutput = false;
        $root = $objDom->appendChild($objDom->createElement("OrdenPago"));
        $sxe = simplexml_import_dom($objDom);
        $sxe->addChild("IdMoneda", 1);
        $sxe->addChild("Total", $rowAnuncio['totalPrecio']);
        $sxe->addChild("MerchantId", "APT");
        $sxe->addChild("OrdenIdComercio", $rowAnuncio['compraId']);
        $sxe->addChild(
            "UrlOk", htmlspecialchars(
                $this->_config->app->siteUrl.$urlOK
            )
        );
        $sxe->addChild(
            "UrlError", htmlspecialchars(
                $this->_config->app->siteUrl.$UrlError
            )
        );
        $sxe->addChild("MailComercio", ""); //CUAL ES???
        $sxe->addChild("FechaAExpirar", $rowAnuncio['fechaExpiracion']." 00:00:00");
        $sxe->addChild("UsuarioId", $rowAnuncio['usuarioId']);
        $sxe->addChild("DataAdicional", "");
        $sxe->addChild("UsuarioNombre", $rowAnuncio['empresaRazonSocial']);
        $sxe->addChild("UsuarioApellidos", $rowAnuncio['empresaRazonComercial']);
        $sxe->addChild("UsuarioLocalidad", "");
        $sxe->addChild("UsuarioProvincia", "");
        $sxe->addChild("UsuarioPais", "");
        $sxe->addChild("UsuarioAlias", "");
        $sxe->addChild("UsuarioEmail", $rowAnuncio['empresaMail']); //= E-mail del Service
        $ad = $sxe->addchild("Detalles");
        $detalle = $ad->addChild("Detalle");
        $detalle->addChild("Cod_Origen", "");
        $detalle->addChild("TipoOrigen", "");
        $detalle->addChild(
            "ConceptoPago", "Aptitus ".
            $rowAnuncio['nombreProducto']." #".
            $rowAnuncio['compraId']
        );
        $detalle->addChild("Importe", $rowAnuncio['totalPrecio']);
        $detalle->addChild("Campo1", "");
        $detalle->addChild("Campo2", "");
        $detalle->addChild("Campo3", "");
        $objDom->loadXML($sxe->asXML());
        
        $criptex = new App_Controller_Action_Helper_WebServiceEncriptacion();
        return $criptex->encriptaCadena($objDom->saveXML());
    }

    private function generarXmlEncriptadoPerfil($rowPerfil) {
        //var_dump($rowAnuncio);
        $objDom = new DOMDocument('1.0', 'UTF-8');
        $objDom->preserveWhiteSpace = false;
        $objDom->formatOutput = false;
        $root = $objDom->appendChild($objDom->createElement("OrdenPago"));
        $sxe = simplexml_import_dom($objDom);
        $sxe->addChild("IdMoneda", 1);
        $sxe->addChild("Total", $rowPerfil['tarifaPrecio']);
        $sxe->addChild("MerchantId", "APT");
        $sxe->addChild("OrdenIdComercio", $rowPerfil['compraId']);
        $sxe->addChild(
                "UrlOk", htmlspecialchars(
                        $this->_config->app->siteUrl . "/comprar-perfil/ok-pago-efectivo"
                )
        );
        $sxe->addChild(
                "UrlError", htmlspecialchars(
                        $this->_config->app->siteUrl . "/perfil-destacado/paso2/tarifa/" .
                        $rowPerfil['tarifaId']
                )
        );
        $sxe->addChild("MailComercio", ""); //CUAL ES???
        $sxe->addChild("FechaAExpirar", $rowPerfil['fechaExpiracion'] . " 00:00:00");
        $sxe->addChild("UsuarioId", $rowPerfil['usuarioId']);
        $sxe->addChild("DataAdicional", "");
        $sxe->addChild("UsuarioNombre", $rowPerfil['nombres']);
        $sxe->addChild("UsuarioApellidos", $rowPerfil['apellidos']);
        $sxe->addChild("UsuarioLocalidad", "");
        $sxe->addChild("UsuarioProvincia", "");
        $sxe->addChild("UsuarioPais", "");
        $sxe->addChild("UsuarioAlias", "");
        $sxe->addChild("UsuarioEmail", $rowPerfil['postulanteMail']); //= E-mail del Service
        $ad = $sxe->addchild("Detalles");
        $detalle = $ad->addChild("Detalle");
        $detalle->addChild("Cod_Origen", "");
        $detalle->addChild("TipoOrigen", "");
        $detalle->addChild(
                "ConceptoPago", "Aptitus " .
                $rowPerfil['nombreProducto'] . " #" .
                $rowPerfil['compraId']
        );
        $detalle->addChild("Importe", $rowPerfil['tarifaPrecio']);
        $detalle->addChild("Campo1", "");
        $detalle->addChild("Campo2", "");
        $detalle->addChild("Campo3", "");
        $objDom->loadXML($sxe->asXML());
        $criptex = new App_Controller_Action_Helper_WebServiceEncriptacion();
        return $criptex->encriptaCadena($objDom->saveXML());
    }

}
