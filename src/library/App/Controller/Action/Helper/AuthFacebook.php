<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AuthFacebook
 *
 * @author ronald
 */
class App_Controller_Action_Helper_AuthFacebook extends Zend_Controller_Action_Helper_Abstract
{
    //put your code here
    private $_config;
    private $__cache;
    private $UrlGo;
    private $_ModelUsuario;
    private $_UserFb;
    private $_urlRegistro;

    const URl = '/mi-cuenta';

    public function __construct()
    {
        $this->_ModelUsuario     = new Application_Model_Usuario();
        $this->_config           = Zend_Registry::get('config');
        $redirectUri             = $this->_config->apis->facebook->url;
        $this->_fb               = new \League\OAuth2\Client\Provider\Facebook([
            'clientId' => $this->_config->apis->facebook->appid,
            'clientSecret' => $this->_config->apis->facebook->appsecret,
            'redirectUri' => $this->_config->apis->facebook->url,
            'graphApiVersion' => 'v2.6',
        ]);
        $_SESSION['oauth2state'] = $this->_fb->getState();
        $this->_urlRegistro='';
    }

    public function setUrlReturn($url)
    {
        $_SESSION['UrlR'] = $url;
    }

    public function getUrlReturn()
    {
        return $_SESSION['UrlR'];
    }

    public function Ulrlogin()
    {

        $authUrl                 = $this->_fb->getAuthorizationUrl([
            'scope' => ['email'],
        ]);
        $_SESSION['oauth2state'] = $this->_fb->getState();
        return $authUrl;
    }

    public function LoginFb($key)
    {
        try {
            $token        = $this->_fb->getAccessToken('authorization_code',
                [
                'code' => $key
            ]);
            $facebookUser = $this->_fb->getResourceOwner($token);
            if (!empty($facebookUser->getEmail())) {
                $facebookUser = $facebookUser->toArray();

                if ($this->isvalidCP($facebookUser)) {
                    $id = $this->registerCuentP($facebookUser);
                    if ($id) {
                        $facebookUser['UrlReturn'] = $this->getUrlReturn();
                        $this->setUserFb($facebookUser);
                        return $id;
                    }
                    return false;
                }
                $facebookUser['UrlReturn'] = $this->getUrlReturn();
                $this->setUserFb($facebookUser);
                return true;
            }
            $this->_urlRegistro = '/#modalRegisterUser';
            return false;
        } catch (Exception $exc) {
            return false;
        }
    }

    public function getUserFb()
    {
        return $this->_UserFb;
    }

    public function getReturnRegistro()
    {
        return $this->_urlRegistro;
    }

    public function setUserFb($facebookUser)
    {
        $this->_UserFb = $facebookUser;
    }

    private function registerCuentP($facebookUser)
    {
        $idUser                 = $this->_ModelUsuario->registerFb($facebookUser);
        $facebookUser['idUser'] = $idUser;
        $modelPostulante        = new Application_Model_Postulante();
        return $modelPostulante->registerFb($facebookUser);
    }

    private function isvalidCP($facebookUser)
    {
        return $this->_ModelUsuario->validEmailRs($facebookUser['email'],
                Application_Form_Login::ROL_POSTULANTE);
    }
}