<?php

class App_Service_Adecsys_RegisterTramas
{
    private $_webService    = null;
    private $_config        = null;
    private $_client        = null;
    
    private $_options       = array();
    private $_message       = null;
    private $_directory     = null;
    
    const MSJ_ERROR         = 'Ocurrio un error';
    const MSJ_REGISTER      = 'Trama registrada';
    
    const TYPE_CONSULTATION_ENTE    = '_consulEnte';
    const TYPE_REGISTER_ENTE        = '_regEnte';
    const TYPE_REGISTER_AD          = '_regAnuncio';
    
    public function __construct($webService = null)
    {
        $this->_config = Zend_Registry::get('config');        
        
        if ($this->_config->adecsys->proxy->enabled)
            $this->_options = $this->_config->adecsys->proxy->param->toArray();        

        $wsdl = $this->_config->adecsys->wsdl;

        try {
            $this->_webService = $webService;
            if (is_null($webService)) {
                $this->_webService = new Adecsys_Wrapper($wsdl, $this->_options);
            }                       
            $this->_client      = $this->_webService->getSoapClient();
        } catch (Exception $ex) {
            if (!empty($this->_config->mensaje->avisoadecsys->emails)) {
                $emailing = explode(',',$this->_config->mensaje->avisoadecsys->emails);                
                $helper = new App_Controller_Action_Helper_Mail();                
                foreach($emailing as $email) {
                    $helper->notificacionAdecsys(
                        array(
                            'to' => trim($email),
                            'mensaje' => $ex->getMessage(),
                            'trace' => $ex->getTraceAsString(),
                            'refer' => http_build_query($_REQUEST)
                            )
                    );
                }
            }
        }                
        
        $this->_directory   = APPLICATION_PATH . '/../logs/';
    }
    
    public function register($name, $type)
    {
        try {
            
            @unlink($this->_directory .$name . $type . '_envio.xml');
            file_put_contents(
                $this->_directory .
                $name . $type . '_envio.xml',
                $this->_client->getLastRequest(), FILE_APPEND
            );
            
            @unlink($this->_directory .$name . $type . '_rpta.xml');
            file_put_contents(
                $this->_directory .
                $name . $type . '_rpta.xml',
                $this->_client->getLastResponse(), FILE_APPEND
            );
        } catch (Exception $e) {
            $this->_message = $e->getMessage();
            return false;
        }
        
        return true;
    }
    
    public function registerReproceso($name, $type, $reproceso)
    {
        try {
            @unlink($this->_directory .$name . $type . '_envio_reproceso_'.$reproceso.'.xml');
            file_put_contents(
                $this->_directory .
                $name . $type . '_envio_reproceso_'.$reproceso.'.xml',
                $this->_client->getLastRequest(), FILE_APPEND
            );
            
            @unlink($this->_directory .$name . $type . '_rpta_reproceso_'.$reproceso.'.xml');
            file_put_contents(
                $this->_directory .
                $name . $type . '_rpta_reproceso_'.$reproceso.'.xml',
                $this->_client->getLastResponse(), FILE_APPEND
            );
        } catch (Exception $e) {
            $this->_message = $e->getMessage();
            return false;
        }
        
        return true;
    }
    
    public function getMessage()
    {
        return $this->_message;
    }
}