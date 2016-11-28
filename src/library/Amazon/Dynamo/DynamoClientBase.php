<?php

class Amazon_Dynamo_DynamoClientBase
{
    
    protected $configKey;
    protected $configSecret;
    protected $configRegion;    
    protected $configTablas;    
    protected $client;
    public $_log;
    
    public function __construct() 
    {
        $configDyn = Zend_Registry::get('config');
        $this->_log = $this->getLog();
       
        $this->configKey  = $configDyn->dynamodb->config->key;
        $this->configSecret = $configDyn->dynamodb->config->secret;
        $this->configRegion = $configDyn->dynamodb->config->region;        
        $this->configTablas = $configDyn->dynamodb->config->tabla;
    
        $config = array(
            'key' => $this->configKey,
            'secret' => $this->configSecret,
            'region' => $this->configRegion,
        );
        
        $this->client = \Aws\DynamoDb\DynamoDbClient::factory($config);                        
        
    }
    
    public function getClient()
    {
        return $this->client;
    }
    
    
    public function getLog()
    {
        return Zend_Registry::get('log');
    }
    


}
