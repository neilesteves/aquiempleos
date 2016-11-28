<?php

class Amazon_Dynamo_EndPointDynModel extends Amazon_Dynamo_DynamoClientBase
{
    
    private $tableName;
    
    
    public function __construct()
    {
        parent::__construct();
        $this->tableName = $this->configTablas->endpointEquiposMoviles;
        
    }


    public function getDatos($CustomUserData)
    { 
        $result = array();        
        try {
            
            if ($CustomUserData) {
                $client = $this->getClient();
                $response = $client->scan(array(
                        'TableName' => $this->tableName,                        
                        'ExpressionAttributeValues' =>  array (
                            ':val1' => array('S' => (string)$CustomUserData),
                            ) ,
                        'FilterExpression' => '(sns_CustomUserData = :val1)',
                ));      
                
                $result = (count($response['Items']) > 0) ? $response['Items'] : array();
            }
            
        } catch (Exception $ex) {            
            return $result;
        }
        
        return $result;
        
    }
    
    
    
    
    
    
}
