<?php

use Firebase\JWT\JWT;

class App_Controller_Action_Helper_JWT extends Zend_Controller_Action_Helper_Abstract
{ 
    protected $secretKey = "empleoBusco";
    
    public function encode($data)
    {
       $jwt = JWT::encode(
            $data,
            $this->secretKey,
            'HS512'
        );

       return $jwt;
    }

    public function decode($data)
    {
        return JWT::decode($data, $this->secretKey, array('HS512'));
    }
    
}


