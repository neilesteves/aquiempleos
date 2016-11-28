<?php

class App_Controller_Action_Helper_HTTPAuth extends Zend_Controller_Action_Helper_Abstract
{
    public function doBasicHTTPAuth()
    {
        $path = APPLICATION_PATH . '/configs/pswd';
        $resolver = new Zend_Auth_Adapter_Http_Resolver_File($path);
        $config = array(
            'accept_schemes' => 'basic',
            'realm' => 'www.devel.aptitus.info',
            'digest_domains' => '/index',
            'nonce_timeout' => 3600,
        );
        $adapter = new Zend_Auth_Adapter_Http($config);
        $adapter->setBasicResolver($resolver);

        $request = $this->getRequest();
        $response = $this->getResponse();
        $adapter->setRequest($request);
        $adapter->setResponse($response);

        $result = $adapter->authenticate();

        if (!$result->isValid()) {
             // Bad userame/password, or canceled password prompt
            return false;
        } else {
            return true;
        }
    }

}