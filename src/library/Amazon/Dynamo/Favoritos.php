<?php

class Amazon_Dynamo_Favoritos extends Amazon_Dynamo_DynamoClientBase
{
    
    protected $configKey;
    protected $configSecret;
    protected $configRegion;
    
    protected $client;
    
    public function __construct()
    {
        parent::__construct();
        $this->tableName = $this->configTablas->favoritos;        
    }
    
    public function getDatos($idPostulante)
    { 
        $favoritos = array();        
        try {
            if ($idPostulante) {
                $client = $this->getClient();                 
                $response = $client->query(array(
                        'TableName' => $this->tableName,                                            
                        'IndexName' => 'idPostulante-index',
                        'Select' =>  'ALL_ATTRIBUTES',
                        'KeyConditions' =>  array(
                            'idPostulante' => array(
                                'AttributeValueList' => array(
                                    array('N' => (int)$idPostulante)
                                ),
                                'ComparisonOperator' => 'EQ'
                            )                            
                        ),
                    'QueryFilter' => array(
                        'estado' => array(
                            'AttributeValueList' => array(
                                array('N' => 1)
                            ),
                            'ComparisonOperator' => 'EQ'
                        )
                    )
                )); 
                $favoritos = (count($response['Items']) > 0) ? $response['Items'] : array();                
                
            }
            
        } catch (Exception $ex) {                                   
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
            return $favoritos;
        }
        
        return $favoritos;
        
    }
    
    public function guardarDatos($insert= array()) 
    {
        try {
            
            if (empty($insert['idPostulante']) || empty($insert['id_aviso']) 
                || empty($insert['idAviso'])) {
                return false;
            }
            
            $client = $this->getClient();
            $item = array_merge(array(
                 'fechaRegistro' => date('Y-m-d H:i:s')
                 ), $insert
            );
            
            // Para otros metodos para guardar un Item:
            $res = $client->putItem(array(
                    'TableName' => $this->tableName,
                    'Item' => $client->formatAttributes(array(                                                    
                        'idPostulante' => (int)$item['idPostulante'],
                        'id_aviso' => $item['id_aviso'], 
                        'idAviso' => $item['idAviso'], 
                        'estado' => 1, 
                        'origen' => 'web',
                        'fechaRegistro' =>$item['fechaRegistro']                        
                    )),
                    'ReturnConsumedCapacity' => 'TOTAL'
             ));
            return $res;
            
        } catch (\Exception $ex) {            
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
            return false;
        }
    }

    public function borrarAviso($param)
    {
        $rest=array();
        try {
            $client = $this->getClient();
            $dataEliminado= $this->getDatosPostulante($param);
            if(count($dataEliminado)>0){
                $rest=  $client->updateItem(array(
                    "TableName" =>  $this->tableName,
                    "Key" =>  array(
                        '_id' => array (
                            'S' => (string)$dataEliminado[0]['_id']['S']
                        )
                    ),
                    'ExpressionAttributeValues' =>  array (
                        ':val1' => array('N' => 0)
                    ) ,
                    'UpdateExpression' => 'set estado = :val1',
                    "ReturnValues" => 'NONE'
                ));
                return $rest;
            }
        } catch (Exception $ex) {
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
        }
    }
    public function borrarDatosAviso($param)
    {
        try {        
            $client = $this->getClient();
            $dataFavoritos = $this->getDatosPostulante($param);
            if (count($dataFavoritos) > 0) {
            $rest = $client->updateItem(array(
                "TableName" =>  $this->tableName,
                "Key" =>  array(
                    'idPostulante' => array (
                        'N' => (int)$dataFavoritos[0]['idPostulante']['N']                        
                    ),
                    'fechaRegistro' => array (
                        'S' => (string)$dataFavoritos[0]['fechaRegistro']['S'] 
                    )     

                    
                ),
                'ExpressionAttributeValues' =>  array (
                    ':val1' => array('N' => 0)                    
                ) ,
                'UpdateExpression' => 'set estado = :val1',
                "ReturnValues" => 'NONE'
            ));
                return $rest;
            }else {
                return FALSE;
            }
        } catch (Exception $ex) {
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
        }
    }
    
    
    public function getDatosPostulante($paran)
    { 
        $favoritos = array();        
        try {
            if (!isset($paran['idPostulante']) && !isset($paran['id_aviso'])) {
                return $favoritos;
            }
            
            if ($paran) {
                $client = $this->getClient();                 
                $favoritos = $client->query(array(
                    'TableName' => $this->tableName,                                            
                    'IndexName' => 'idPostulante-index',
                    'Select' =>  'ALL_ATTRIBUTES',
                    'KeyConditions' =>  array(
                        'idPostulante' => array(
                            'AttributeValueList' => array(
                                array('N' => $paran['idPostulante'])
                            ),
                            'ComparisonOperator' => 'EQ'
                        )                                                
                    ),
                    'QueryFilter' => array(
                        'estado' => array(
                            'AttributeValueList' => array(
                                array('N' => 1)
                            ),
                            'ComparisonOperator' => 'EQ'
                        ),
                        'id_aviso' => array(
                            'AttributeValueList' => array(
                                array('S' => $paran['id_aviso'])
                            ),
                            'ComparisonOperator' => 'EQ'
                        )
                    )
                    
                ));
                
                return $favoritos["Items"];
                
            }
            
        } catch (Exception $ex) { 
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString, Zend_Log::CRIT);
            return $favoritos;
        }
        
        return $favoritos;
        
    }


}
