<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Action
 *
 * @author ronald
 */
class Api_Model_Action
{
    public $_config;
    public $_cache;
    public $_token;
    protected $_log;
    public $_Result;

    const SUCCESS = 'SUCCESS';

    //put your code here
    public function __construct()
    {
        $this->_config = Zend_Registry::get('config');
        $idtoken       = 1;
        $authStorage   = Zend_Auth::getInstance()->getStorage()->read();
       // var_dump($authStorage['empresa']);EXIT;
      ///  if (MODULE === 'EMPRESA') $idtoken = !empty ($authStorage['empresa']['id'])?$authStorage['empresa']['id']:1;
     ///   $this->_token  = $this->Generatetoken($idtoken);
        $this->_Result = array(
            'status' => false,
            'getMessage' => 'Ocurrio un error',
            'records' => array()
        );
    }

    private function CallAPI($method, $url, $data = false)
    {
        try {
            $curl = curl_init();
            switch ($method) {
                case "POST":
                    curl_setopt($curl, CURLOPT_POST, 1);

                    if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                    break;
                case "PUT":
                    curl_setopt($curl, CURLOPT_PUT, 1);
                    break;
                default:
                    if ($data)
                            $url = sprintf("%s?%s", $url,
                            http_build_query($data));
            }
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);
            curl_close($curl);
            return $result;
        } catch (Exception $exc) {
            echo $exc->getMessage();
            exit;
            return false;
        }
    }

    public function rs($method, $url, $data = false)
    {
        $rs = Zend_Json::decode($this->CallAPI($method, $url, $data));
        return $rs;
    }

    public function execute_rest($method, $url, $data = false)
    {
        $rest = $this->CallAPI($method, $url, $data);
        $r    = $this->_Result;
        if ($rest) {
            $rs = Zend_Json::decode($rest);
            if (isset($rs["_meta"]["status"]) && $rs["_meta"]["status"] == self::SUCCESS) {
                $r['status']     = true;
                $r['getMessage'] = isset($rs["records"]["userMessage"]) ? $rs["records"]["userMessage"]
                        : '';
                $r['records']    = $rs["records"];
                return $r;
            }
            $r['getMessage'] = isset($rs["records"]["userMessage"]) ? $rs["records"]["userMessage"]
                    : '';

            return $r;
        }
        return $r;
    }

    public function execute($method, $url, $data = false)
    {
        $api=$this->CallAPI($method, $url, $data);
        $rs = Zend_Json::decode($api);
        if (isset($rs["_meta"]["status"]) && $rs["_meta"]["status"] == self::SUCCESS) {
            return $rs["records"];
        }
        return $rs;
    }

    public function Generatetoken($id = 1)
    {
        if ($id) {
            $url = $this->_config->api->url.$this->_config->api->Generatetoken;


            $rs = Zend_Json::decode($this->CallAPI('GET', $url,
                        array(
                        'ide' => $id)));
            if (isset($rs["_meta"]["status"]) && $rs["_meta"]["status"] == self::SUCCESS) {
                return $rs["records"]["token"];
            }
            return false;
        }
        return false;
    }

    public function exec($method, $url, $data = false)
    {
        $rs = Zend_Json::decode($this->CallAPI($method, $url, $data));

        if (isset($rs["_meta"]["status"]) && $rs["_meta"]["status"] == self::SUCCESS) {
            return $rs;
        }

        return false;
    }

    public function getToken()
    {
        return $this->_token;
    }
}