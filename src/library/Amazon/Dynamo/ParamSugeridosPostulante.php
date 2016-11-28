<?php

class Amazon_Dynamo_ParamSugeridosPostulante extends Amazon_Dynamo_DynamoClientBase
{
    
    private $tableName;
    
    
    public function __construct()
    {
        parent::__construct();
        $this->tableName = $this->configTablas->sugeridosPostulante;        
        
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
        $areas = array();           
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
                                    array('S' =>(string)$idPostulante)
                                ),
                                'ComparisonOperator' => 'EQ'
                            )                            
                        )
                ));                                
                
//                $response = $client->scan(array(
//                        'TableName' => $this->tableName,                        
//                        'ExpressionAttributeValues' =>  array (
//                            ':val1' => array('S' => (string)$idPostulante),                            
//                            ) ,
//                        'FilterExpression' => '(idPostulante = :val1)',
//                ));      
                
                $areas = (count($response['Items']) > 0) ? $response['Items'][0] : array();
            }
            
        } catch (Exception $ex) {                        
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
            return $areas;
        }
        
        return $areas;
        
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
            throw new Exception($ex->getMessage());
        }
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
            $existeDatos = $this->getDatos($insert['idPostulante']); 
            $dataSerialized = serialize($insert['data']);
            if (count($existeDatos) > 0) {                
                /// Actualizamos los datos:                
               
                $client->updateItem(array(
                    "TableName" => $this->tableName,
                    "Key" => $client->formatAttributes(array(                            
                        'idPostulante' => $insert['idPostulante'] 
                    )),
                    'ExpressionAttributeNames' => array (
                        "#E"=>"aptitudes",
                        "#F" => 'area_nivel'
                    ),
                    'ExpressionAttributeValues' =>  array (
                        ':val1' => array("S" =>$dataSerialized),
                        ':val2' => array("S" => $dataSerialized)
                    ) ,
                    'UpdateExpression' => 'set #E = :val1, #F = :val2',
                    'ReturnValues' => 'UPDATED_NEW' 
                ));                
            } else {
                // Para otros metodos para guardar un Item:
                $client->putItem(array(
                       'TableName' => $this->tableName,
                       'Item' => $client->formatAttributes(array(                                                    
                           'idPostulante' => $insert['idPostulante'],
                           'area_nivel' => $dataSerialized, 
                           'created_at' => date('Y-m-d H:i:s')                        
                       )),
                       'ReturnConsumedCapacity' => 'TOTAL'
                ));
            }
                                    
            return true;
            
        } catch (\Exception $ex) {            
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
            throw new \Exception($ex->getMessage());
        }
    }
   
    
    
    public function updateDatos($param) 
    {
        
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
                    "#punto1" => "price1_punto",
                    "#punto2" => "price2_punto",
                    "#C" => "ubigeo",
                   // "#D" => "txtUbicacion",
                    "#E"=>"aptitudes"
                ),
                'ExpressionAttributeValues' =>  array (
                    ':val1' => array("S" => $param['price1']),
                    ':val2' => array("S" => $param['price2']),
                    ':val1_punto' => array("N" => (int)$param['price1_punto']),
                    ':val2_punto' => array("N" => (int)$param['price2_punto']),
                    ':val3' => array("S" => $param['ubigeo']),
                  //  ':val4' => array("S" => $param['txtUbicacion']),
                    ':val5' => array("S" => $param['aptitudes'])
                ) ,
                'UpdateExpression' => 'set #A = :val1, #B = :val2, #punto1 = :val1_punto, #punto2 = :val2_punto, #C = :val3, #E = :val5',
                'ReturnValues' => 'ALL_NEW' 
            ));
            return $response['Attributes'];
        } catch (Exception $exc) {            
            $this->_log->log($exc->getMessage().'. '.$exc->getTraceAsString(), Zend_Log::CRIT);
            throw new \Exception($exc->getMessage());
        }
            
    }
    
    
    
}
