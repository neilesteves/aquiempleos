<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Postulante
 *
 * @author ronald
 */
class Api_Model_Postulante extends Api_Model_Action
{
    protected $_ntotalAviso;
    protected $_pag;
    protected $_filter;
    protected $_log;
    public $_config;

    const R = 'R';
    const E = 'E';
    const D = 'D';

    //put your code here
    public function __construct()
    {
        $this->_cache  = Zend_Registry::get('cache');
        $this->_log    = Zend_Registry::get('log');
        $this->_config = Zend_Registry::get('config');
        parent::__construct();
    }

    public function listLanding($data)
    {

        $rs = $this->rs('GET', $this->_config->app->api->postulante->landing,
            $data);
        return $rs;
    }

    public function listLandingEmpleo($data=array())
    {
        $cacheEt = 604800;
        $cacheId = MODULE.'_'.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $rs = $this->rs('GET', $this->_config->app->api->postulante->landing,
            $data);
        $this->_cache->save($rs, $cacheId, array(), $cacheEt);
        return $rs;
    }

    public function listLandingCasa($data=array())
    {
        $cacheEt = 604800;
        $cacheId = MODULE.'_'.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $rs = $this->rs('GET',
            $this->_config->app->api->postulante->landingcasa, $data);
        $this->_cache->save($rs, $cacheId, array(), $cacheEt);
        return $rs;
    }

    public function listLandingCarro($data=array())
    {
        $cacheEt = 604800;
        $cacheId = MODULE.'_'.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $rs = $this->rs('GET',
            $this->_config->app->api->postulante->landingcarro, $data);
        $this->_cache->save($rs, $cacheId, array(), $cacheEt);
        return $rs;
    }

    public function listAvisoDestacados()
    {
        $data = array(
            'token' => $this->_token,
            'tipoConsulta' => 'D',
            'limit' => '5'
        );
        $rs   = $this->rs('GET',
            $this->_config->api->postulante->getListAvisosRecientesDestacados,
            $data);
        if (isset($rs['list'])) {
            return $rs['list'];
        }
        return array();
    }

    public function listAvisoResientes()
    {
        $data = array(
            'token' => $this->_token,
            'tipoConsulta' => 'R',
            'limit' => '5'
        );
        $rs   = $this->rs('GET',
            $this->_config->api->postulante->getListAvisosRecientesDestacados,
            $data);
        if (isset($rs['list'])) {
            return $rs['list'];
        }
        return array();
    }

    public function getListAreasHome($limit = 'ALL')
    {
        $data = array(
            'token' => $this->_token,
            'limit' => $limit
        );
        $rs   = $this->rs('GET',
            $this->_config->api->postulante->getListAreasHome, $data);
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

    public function buscadorAvisos($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->rs('GET',
            $this->_config->api->postulante->buscadorAvisos, $data);
        if (isset($rs['data'])) {
            return $rs;
        }
        return array('data' => array(), 'ntotal' => 1, 'count' => 1, 'filter' => array(
                'areas' => array(), 'nivel' => array(), 'ubigeo' => array(), 'fecha' => array(),
                'salario' => array()));
    }

    public function gestionPostulante($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->execute_rest('POST',
            $this->_config->api->postulante->gestionPostulante, $data);
        if ($rs['status']) {
            $rs['getMessage'] = 'Se actualizó el registro.';
            return $rs;
        }
        return $rs;
    }

    public function gestionOlvidePasword($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->execute_rest('GET',
            $this->_config->api->olvideContrasena, $data);
        if ($rs['status']) {
            $rs['getMessage'] = 'Se envio correctamente';
            return $rs;
        }
        return $rs;
    }

    public function getDetailByEmpresa($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->rs('GET',
            $this->_config->api->aviso->getDetailByEmpresa, $data);
        if (isset($rs['list'])) {
            return $rs['list'];
        }
        return array();
    }

    public function getRelacionados($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->rs('GET',
            $this->_config->api->aviso->getRelacionados, $data);
        if (isset($rs['list'])) {
            return $rs['list'];
        }
        return array();
    }

    public function getPreguntasAviso($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->rs('GET', $this->_config->api->aviso->pregunta,
            $data);
        if (isset($rs['list'])) {
            return $rs['list'];
        }
        return array();
    }

    public function haspostulado($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->execute('GET',
            $this->_config->api->aviso->haspostulado, $data);
        if (isset($rs['_meta']['status'])) {
            return array('status' => false, 'Message' => $rs['records']);
        }
        return array('status' => true, 'data' => $rs);
    }

    public function listPostulaciones($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->rs('GET',
            $this->_config->api->postulante->getListPostulaciones, $data);
        if (isset($rs['list'])) {
            return $rs['list'];
        }
        return array();
    }

    public function RegisterEstudios($data = array())
    {
        $data['token']  = $this->_token;
        $data['accion'] = self::R;
        $rs             = $this->execute_rest('POST',
            $this->_config->api->postulante->saveEstudio, $data);
        if ($rs['status']) {
            $rs['getMessage'] = 'Se Registro el Registro';
            return $rs;
        }
        return $rs;
    }

    public function ActualizarEstudios($data = array())
    {
        $data['token']  = $this->_token;
        $data['accion'] = self::E;
        $rs             = $this->execute_rest('POST',
            $this->_config->api->postulante->saveEstudio, $data);
        if ($rs['status']) {
            $rs['getMessage'] = 'Se actualizó el registro.';
            return $rs;
        }
        return $rs;
    }

    public function ActualizarExperiencia($data = array())
    {
        $data['token']  = $this->_token;
        $data['accion'] = self::E;
        $rs             = $this->execute_rest('POST',
            $this->_config->api->postulante->saveExperiencia, $data);
        if ($rs['status']) {
            $rs['getMessage'] = 'Se actualizó el registro.';
            return $rs;
        }
        return $rs;
    }

    public function RegisterExperiencia($data = array())
    {
        $data['token']  = $this->_token;
        $data['accion'] = self::R;
        $rs             = $this->execute_rest('POST',
            $this->_config->api->postulante->saveExperiencia, $data);
        if ($rs['status']) {
            $rs['getMessage'] = 'Se actualizó el registro.';
            return $rs;
        }
        return $rs;
    }

    public function DeleteEstudios($data = array())
    {
        $data['token']  = $this->_token;
        $data['accion'] = self::D;
        $rs             = $this->execute_rest('POST',
            $this->_config->api->postulante->saveEstudio, $data);
        if ($rs['status']) {
            $rs['getMessage'] = 'Se elimino el registro';
            return $rs;
        }
        return $rs;
    }

    public function listarNotificaciones($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->rs('GET',
            $this->_config->api->postulante->listarNotificaciones, $data);

        if (!empty($rs['list'])) {
            return $rs['list'];
        }
        return array();
    }

    public function editarContrasena($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->execute('POST',
            $this->_config->api->postulante->editarContrasena, $data);
        if (isset($rs['_meta']['status'])) {
            return array('status' => false, 'Message' => $rs['records']["userMessage"]);
        }
        return array('status' => true, 'data' => $rs);
    }

    public function uploadCv($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->exec('POST',
            $this->_config->api->postulante->uploadCv, $data);

        return $rs;
    }

    public function getPerfilPublico($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->execute_rest('GET',
            $this->_config->api->postulante->getPerfilPublico, $data);
        if (!empty($rs["records"]['list'])) {
            return $rs["records"]['list']["postulante"];
        }
        return array();
    }

    public function ActualizarProgramas($data = array())
    {
        $data['token']  = $this->_token;
        $data['accion'] = self::E;
        $rs             = $this->execute_rest('POST',
            $this->_config->api->postulante->saveProgramas, $data);
        if ($rs['status']) {
            $rs['getMessage'] = 'Se actualizó el registro.';
            return $rs;
        }
        return $rs;
    }

    public function RegisterProgramas($data = array())
    {
        $data['token']  = $this->_token;
        $data['accion'] = self::R;
        $rs             = $this->execute_rest('POST',
            $this->_config->api->postulante->saveProgramas, $data);
        if ($rs['status']) {
            $rs['getMessage'] = 'Se Registro el Registro';
            return $rs;
        }
        return $rs;
    }

    public function DeleteProgramas($data = array())
    {
        $data['token']  = $this->_token;
        $data['accion'] = self::D;
        $rs             = $this->execute_rest('POST',
            $this->_config->api->postulante->saveProgramas, $data);
        if ($rs['status']) {
            $rs['getMessage'] = 'Se elimino el registro';
            return $rs;
        }
        return $rs;
    }

    public function DeleteExperiencia($data = array())
    {
        $data['token']  = $this->_token;
        $data['accion'] = self::D;
        $rs             = $this->execute_rest('POST',
            $this->_config->api->postulante->saveExperiencia, $data);
        if ($rs['status']) {
            $rs['getMessage'] = 'Se elimino el registro';
            return $rs;
        }
        return $rs;
    }

    public function login($email, $pasword, $modulo = 'P')
    {

        if ($modulo == 'empresa') {
            $modulo = 'E';
        } else {
            $modulo = 'P';
        }
        $data = array(
            'token' => $this->_token,
            'email' => $email,
            'clave' => $pasword,
            'rol' => $modulo
        );
        $rs   = $this->execute_rest('GET',
            $this->_config->api->postulante->login, $data);
        //  var_dump((object)$rs);exit;
        if ($rs['status']) {
            $rs['getMessage'] = 'Se elimino el registro';
            return (object) $rs;
        }
        return (object) $rs;
    }

    public function saveIdioma($data = array())
    {
        $data['token'] = $this->_token;

        $rs = $this->execute('POST',
            $this->_config->api->postulante->saveIdioma, $data);
        if (isset($rs['_meta']['status'])) {
            return array('status' => false, 'Message' => $rs['records']);
        }
        return array('status' => true, 'data' => $rs, 'Message' => 'Se registro Satisfactoriamente');
    }

    public function saveReferencia($data = array())
    {
        $data['token'] = $this->_token;

        $rs = $this->execute('POST',
            $this->_config->api->postulante->saveReferencia, $data);
        if (isset($rs['_meta']['status'])) {
            return array('status' => false, 'Message' => $rs['records']);
        }
        return array('status' => true, 'data' => $rs, 'Message' => 'Se registro Satisfactoriamente');
    }

    public function uploadLogo($data = array())
    {
        $data['token'] = $this->_token;
        $rs            = $this->execute_rest('POST',
            $this->_config->api->postulante->uploadCv, $data);
        if ($rs['status']) {
            $rs['getMessage'] = 'Se Actualizo Imagen';
            return $rs;
        }
        return $rs;
    }
}