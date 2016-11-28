<?php

class Amazon_Dynamo_MisHobbies extends Amazon_Dynamo_DynamoClientBase
{
    
    private $tableName;
    
    
    public function __construct()
    {
        parent::__construct();
        $this->tableName = $this->configTablas->misHobbies;
        
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
//                $response = $client->scan(array(
//                        'TableName' => $this->tableName,                        
//                        'ExpressionAttributeValues' =>  array (
//                            ':val1' => array('S' => (string)$idPostulante),                            
//                            ) ,
//                        'FilterExpression' => '(idPostulante = :val1)',
//                ));      
                
                $response = $client->scan(array(
                        'TableName' => $this->tableName,                                            
                        'IndexName' => 'idPostulante-index',
                        'Select' =>  'ALL_ATTRIBUTES',
                        'KeyConditions' =>  array(
                            'idPostulante' => array(
                                'AttributeValueList' => array(
                                    array('S' =>$idPostulante)
                                ),
                                'ComparisonOperator' => 'EQ'
                            )                            
                        )
                ));      
                
                $areas = (count($response['Items']) > 0) ? $response['Items'] : array();
            }
            
        } catch (Exception $ex) {            
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


             $item = array_merge(
               array(
                  'created_at' => date('Y-m-d H:i:s')
               ),
               $insert
             );

                // Para otros metodos para guardar un Item:
             $client->putItem(array(
                    'TableName' => $this->tableName,
                    'Item' => $client->formatAttributes($item),
                    'ReturnConsumedCapacity' => 'TOTAL'
             ));
            
            return true;
            
        } catch (\Exception $ex) {

            exit( $ex );
            throw new \Exception($ex->getMessage());
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
            die($exc->getMessage());
            throw new \Exception($exc->getMessage());
        }
            
    }
    
    
    
}
