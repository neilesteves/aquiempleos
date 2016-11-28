<?php

/**
 *
 */
class App_Auth_Adapter_AuthDev implements Zend_Auth_Adapter_Interface
{
    
    private $users;
    private $isOk;
    
    public function __construct() 
    {        
        
        $this->isOk = false;
        $this->users = array(
            'developer' => date('d-m-Y').'$+'
        );
        
        $this->validarAcceso();
                        
    }


    public function authenticate() 
    {        
        if (true === $this->isOk) {
            return new Zend_Auth_Result(
                Zend_Auth_Result::SUCCESS,
                array('denis'),
                array('Authentication successful.')
            );
        } else {
            return new Zend_Auth_Result(
                Zend_Auth_Result::FAILURE,
                array(null),
                array('Error.')
            );
        }
        
    }
        
    private function http_digest_parse($txt)
    {                
        $needed_parts = array(
            'nonce'     =>  1, 
            'nc'        =>  1, 
            'cnonce'    =>  1, 
            'qop'       =>  1, 
            'username'  =>  1, 
            'uri'       =>  1, 
            'response'  =>  1
        );
        
        $data = array();
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

        
        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }
        
        $ok =  ($needed_parts==0 ? false : $data);        
        return $ok;
    }
    
    public static function autenticarDev()
    {
        $auth = Zend_Auth::getInstance();
        $res = $auth->authenticate(new App_Auth_Adapter_AuthDev);
        return $res->isValid();
    }

    
    
    private function validarAcceso()
    {
        $realm ='Area restringida';
        if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="'.$realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
            exit;            
        }
                  
        $a = $data = $this->http_digest_parse($_SERVER['PHP_AUTH_DIGEST']);
        
        if (!($a) || !isset($this->users[$data['username']])) {
            unset($_SERVER['PHP_AUTH_DIGEST']);
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="'.$realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
            exit;
        }
                
        $A1 = md5($data['username'] . ':' . $realm . ':' . $this->users[$data['username']]);
        $A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
        $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

        //var_dump($data['response'], $valid_response); exit;
        if ($data['response'] != $valid_response) {
            unset($_SERVER['PHP_AUTH_DIGEST']);
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="'.$realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
            exit;
        }
        
        $this->isOk = true;        
        
    }
    

    
}

