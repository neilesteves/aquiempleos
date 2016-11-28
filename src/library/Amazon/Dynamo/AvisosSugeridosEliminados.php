<?php

class Amazon_Dynamo_AvisosSugeridosEliminados extends Amazon_Dynamo_DynamoClientBase
{
    
    private $tableName;
    
    
    public function __construct()
    {
        parent::__construct();
        $this->tableName = $this->configTablas->postulantesSugeridosEliminados;
        
    }


    /*
     * Obtiene las areas e Interes del postulante de DynamoDB
     * 
     * @param int $idPostulante     ID del Postulante
     * @return array                Retorna un array con los atributos 
     *                              y propiedades de Dynamo, caso contrario 
     *                              si no se encuentra retorna un array.
     */
    public function getDatos($idPostulante)
    { 
        $eliminados = array();        
        try {
            
            if ($idPostulante) {
                $client = $this->getClient();
//                $query = array(
//                    'TableName' => $this->tableName,                
//                    'ScanFilter' => array(
//                        'idPostulante' => array(
//                            'ComparisonOperator' => \Aws\DynamoDb\Enum\ComparisonOperator::EQ,
//                            'AttributeValueList' => array(
//                                array( 'S' => $idPostulante)
//                            )
//                        ),
//                        'estado' => array(
//                            'ComparisonOperator' => \Aws\DynamoDb\Enum\ComparisonOperator::EQ,
//                            'AttributeValueList' => array(
//                                array( 'N' => 1 )
//                            )
//                        ),
//                    ),                
//                    'ReturnConsumedCapacity' => 'NONE'                
//                );                                         
//                $response = $client->scan($query);
                
                $response = $client->query(array(
                        'TableName' => $this->tableName,                                            
                        'IndexName' => 'idPostulante-index',
                        'Select' =>  'ALL_ATTRIBUTES',
                        'KeyConditions' =>  array(
                            'idPostulante' => array(
                                'AttributeValueList' => array(
                                    array('S' =>(string) $idPostulante)
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
                $eliminados = (count($response['Items']) > 0) ? $response['Items'] : array();
                
            }
            
        } catch (Exception $ex) {
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
            return $eliminados;
        }
        
        return $eliminados;
        
    }
    
    
    
    /*
     * Elimina los datos de Areas e Niveles del Postulante en DynamoDB
     * 
     * @param int $idPostulante     ID del Postulante
     * @return array                Retorna los antiguos datos antes de 
     *                              borrarse, caso contrario retorna  
     *                              un error de excepcion.
     * 
     */
    public function borrarDatos($idPostulante)
    {
        try {                                
            if ($idPostulante) {
                $client = $this->getClient();
                $response = $client->DeleteItem(array(
                        'TableName' => $this->tableName,
                        'Key' => $client->formatAttributes(array(                            
                                'idPostulante' => $idPostulante 
                         )),
                        'ConditionExpression' =>  'idPostulante = :val1 ',
                        'ExpressionAttributeValues' => array (
                            ':val1' => array('S' => (string)$idPostulante),                            
                        ),
                        'ReturnValues' => 'ALL_OLD'
                ));      
                return $response;
            }
            
        } catch (Exception $ex) {            
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
        }
    }
    
    
    public function borrarDatosAviso($param)
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
//    public function borrarDatosAviso($param)
//    {
//        try {                                
//            if ($idaviso) {
//                $client = $this->getClient();
//                $response = $client->DeleteItem(array(
//                        'TableName' => $this->tableName,
//                        'Key' => $client->formatAttributes(array(                            
//                                'idPostulante' => $idPostulante 
//                         )),
//                        'ConditionExpression' =>  'idAnuncioWeb = :val1 ',
//                        'ExpressionAttributeValues' => array (
//                            ':val1' => array('S' => (string)$idaviso),                            
//                        ),
//                        'ReturnValues' => 'ALL_OLD'
//                ));      
//                return $response;
//            }
//            
//        } catch (Exception $ex) {            
//            throw new Exception($ex->getMessage());
//        }
//    }
    
   
    public function getDatosPostulante($paran)
    { 
        $response = array();        
           
        try {
         
            if ($paran) {
               $client = $this->getClient();                 
//               $query = array(
//                   'TableName' => $this->tableName,                
//                    'ScanFilter' => array(
//                        'idPostulante' => array(
//                            'ComparisonOperator' => \Aws\DynamoDb\Enum\ComparisonOperator::EQ,
//                            'AttributeValueList' => array(
//                                array( 'S' =>(string) $paran['idPostulante'])
//                            )
//                        ),
//                        'idAnuncioWeb' => array(
//                            'ComparisonOperator' => \Aws\DynamoDb\Enum\ComparisonOperator::EQ,
//                            'AttributeValueList' => array(
//                                array( 'S' =>(string)$paran['id_aviso'] )
//                            )
//                        ),
//                        'estado' => array(
//                            'ComparisonOperator' => \Aws\DynamoDb\Enum\ComparisonOperator::EQ,
//                            'AttributeValueList' => array(
//                                array( 'N' => 1 )
//                            )
//                        ),
//                    ),                
//                    'ReturnConsumedCapacity' => 'NONE'                
//                );                                        
//                $response = $client->scan($query); 
               
               $response = $client->query(array(
                        'TableName' => $this->tableName,                                            
                        'IndexName' => 'idPostulante-index',
                        'Select' =>  'ALL_ATTRIBUTES',
                        'KeyConditions' =>  array(
                            'idPostulante' => array(
                                'AttributeValueList' => array(
                                    array('S' => $paran['idPostulante'])
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
                            'idAnuncioWeb' => array(
                                'AttributeValueList' => array(
                                    array('S' => (string)$paran['id_aviso'] )
                                ),
                                'ComparisonOperator' => 'EQ'
                            )
                        )
                )); 

            }
          
        } catch (Exception $ex) { 
           $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString, Zend_Log::CRIT);
           return $response;
        }
        return $response["Items"];
        
    }
    
    
    /*
     * Guarda las areas y niveles de interes del postulante en DynamoDB.
     * 
     * @param array $insert     Datos de los campos para el registro:
     *                           array( ID del Postulante, 
     *                              ID del Nivel, ID Area )
     * @return void / Exception         
     */
    public function guardarDatos($insert) 
    {
        try {
            $client = $this->getClient();
            $item = array_merge(array(
                    'created_at' => date('Y-m-d H:i:s')
                ),
                $insert
            );
            $item['estado']=1;
            // Para otros metodos para guardar un Item:
            $client->putItem(array(
                    'TableName' => $this->tableName,
                    'Item' => $client->formatAttributes($item),
                    'ReturnConsumedCapacity' => 'TOTAL'
             ));            
            return true;
            
        } catch (\Exception $ex) {
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
            return false;
        }
    }
   
    public function updateDatos($param) {
        try {
            $client = $this->getClient();           
           $response= $client->updateItem(array(
                "TableName" => $this->tableName,
                "Key" =>         
                $client->formatAttributes(array(                            
                                'idPostulante' => $param['idPostulante'] 
                         ))             
                ,
                'ExpressionAttributeNames' => array (
                    "#A" => "price1",
                    "#B" => "price2",
                    "#C" => "ubigeo",
                    "#D" => "txtUbicacion"
                ),
                'ExpressionAttributeValues' =>  array (
                    ':val1' => array("S" => $param['price1']),
                    ':val2' => array("S" => $param['price2']),
                    ':val3' => array("S" => $param['ubigeo']),
                    ':val4' => array("S" => $param['txtUbicacion'])
                ) ,
                'UpdateExpression' => 'set #A = :val1, #B = :val2, #C = :val3, #D = :val4',
                'ReturnValues' => 'ALL_NEW' 
            ));
            return $response['Attributes'];
        } catch (Exception $exc) {
            $this->_log->log($exc->getMessage().'. '.$exc->getTraceAsString(), Zend_Log::CRIT);
            throw new \Exception($exc->getMessage());
        }
            
    }
    
    
    
}
