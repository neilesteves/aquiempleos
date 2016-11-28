<?php

class Amazon_Sqs_SqsClientBase
{
    
    protected $configKey;
    protected $configSecret;
    protected $configRegion;    
    protected $configColas;
    protected $queueUrl;
    
    protected $client;
    
    public function __construct() 
    {
        $configDyn = Zend_Registry::get('config');
       
        $this->configKey  = $configDyn->sqs->config->key;
        $this->configSecret = $configDyn->sqs->config->secret;
        $this->configRegion = $configDyn->sqs->config->region;        
        $this->configColas = $configDyn->sqs->config->cola;
        $this->queueUrl = $configDyn->sqs->config->queueUrl;
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
