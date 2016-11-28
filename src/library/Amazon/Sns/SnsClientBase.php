<?php

class Amazon_Sns_SnsClientBase
{
    
    protected $configKey;
    protected $configSecret;
    protected $configRegion;        
    protected $client;
    
    public function __construct() 
    {
        $configDyn = Zend_Registry::get('config');
       
        $this->configKey  = $configDyn->sqs->config->key;
        $this->configSecret = $configDyn->sqs->config->secret;
        $this->configRegion = $configDyn->sqs->config->region;                
    
        $config = array(
            'key' => $this->configKey,
            'secret' => $this->configSecret,
            'region' => $this->configRegion,
        );
        
        $this->client = \Aws\Sqs\SqsClient::factory($config);                        
        
    }
    
    public function getClient()
    {
        return $this->client;
    }
    
    


}
