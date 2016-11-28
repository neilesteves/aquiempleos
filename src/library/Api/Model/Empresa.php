<?php

class Api_Model_Empresa extends Api_Model_Action
{
    protected $_log;

    const R = 'R';
    const E = 'E';
    const D = 'D';

    public function __construct()
    {
        parent::__construct();
        $this->_cache = Zend_Registry::get('cache');
        $this->_log   = Zend_Registry::get('log');
    }

    public function registerEmpresa($data = array())
    {
        $data['token'] = $this->_token;

        $rs = $this->execute_rest('POST',
            $this->_config->api->empresa->gestionDatosEmpresa, $data);

        if ($rs['status']) {
            $rs['message'] = 'Se registro Satisfactoriamente';
            return $rs;
        }
        return $rs;
    }

    public function uploadLogo($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->exec('POST',
            $this->_config->api->empresa->gestionFileLogo, $data);

        return $rs;
    }

    public function getProcesosActivos($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->rs('GET',
            $this->_config->api->empresa->getProcesosActivosByEmpresa, $data);

        return $rs;
    }

    public function getProcesosCerrados($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->rs('GET',
            $this->_config->api->empresa->getProcesosCerradorByEmpresa, $data);
        return $rs;
    }

    public function setSaveAvisos($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->execute_rest('POST',
            $this->_config->api->empresa->gestionAvisoDataPuesto, $data);

        return $rs;
    }

    public function setSaveAvisosEstudio($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->execute_rest('POST',
            $this->_config->api->empresa->gestionEstudioAviso, $data);

        return $rs;
    }

    public function setSaveAvisosExperiencia($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->execute_rest('POST',
            $this->_config->api->empresa->gestionExperienciaAviso, $data);

        return $rs;
    }

    public function setSaveAvisosIdioma($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->execute_rest('POST',
            $this->_config->api->empresa->gestionIdiomaAviso, $data);

        return $rs;
    }

    public function setSaveAvisosProgramas($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->execute_rest('POST',
            $this->_config->api->empresa->gestionProgramasAviso, $data);

        return $rs;
    }

    public function getProcesosBorrador($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->rs('GET',
            $this->_config->api->empresa->getProcesosBorradorByEmpresa, $data);
        return $rs;
    }

    public function listEmpresaHome()
    {
        $data = array(
            'token' => $this->_token,
            'limit' => 'ALL',
            'tipo' => 'home'
        );
        $rs   = $this->rs('GET',
            $this->_config->api->postulante->listEmpresaHome, $data);

        if (isset($rs['list'])) {
            return $rs['list'];
        }
        return array();
    }

    public function getContadoresHome()
    {
        $data = array(
            'token' => $this->_token,
        );
        $rs   = $this->rs('GET',
            $this->_config->api->postulante->getContadoresHome, $data);
        if (isset($rs['contador'])) {
            return $rs['contador'];
        }
        return array();
    }
}