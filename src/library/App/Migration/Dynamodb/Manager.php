<?php

class App_Migration_Dynamodb_Manager
{

    private $_client;
    private $_deltaPath;
    private $_log;

    const DBVERSION_TABLE_NAME = 'apt_pre_version';
    const DBVERSION_COL_NAME = 'version';
    const PREFIX = 'delta_';
    const ZERO_FILL = 4;
    
    public function __construct($adapter, $deltaPath=".", Zend_Log $log=null)
    {
        $this
            ->setAdapter($adapter)
            ->setDeltaPath($deltaPath)
            ->setLog($log);
    }

    public function sync()
    {
        $this->_log->log("===== Sync Starts with DynamoDB =====", Zend_Log::INFO);
        $currentVersion = $this->getVersion();
        $currentVersion++;
        $success = true;
        while ($success) {
            $success = false;
            $filename = sprintf(
                "%s%0".self::ZERO_FILL."d.php", self::PREFIX, $currentVersion
            );
            $filePath = $this->_deltaPath."/".$filename;
            $fileExists = file_exists($filePath);
            if ($fileExists) {
                include($filePath);
                $className = sprintf(
                    "%s%0".self::ZERO_FILL."d", ucfirst(self::PREFIX),
                    $currentVersion
                );
                $classExists = class_exists($className);
                if ($classExists) {
                    $delta = new $className($this->_client,$this->_log);
                    $success = $delta->up();
                    if ($success) {
                        $msg = "Se Aplico el Delta $currentVersion";
                        echo $msg.PHP_EOL;
                        $this->_log->log($msg, Zend_Log::INFO);
                        $this->setVersion($currentVersion++);
                    } else {
                        $msg = "Ha fallado el Delta $currentVersion";
                        $this->_log->log($msg, Zend_Log::ERR);
                        throw new Zend_Exception($msg);
                    }
                }
            }
        }
        $this->_log->log("===== Sync Ends =====", Zend_Log::INFO);
    }

    private function setAdapter($adapter)
    {
        $config = array(
            'key' => $adapter->key,
            'secret' => $adapter->secret,
            'region' => $adapter->region,
        );
        
        $this->_client = \Aws\DynamoDb\DynamoDbClient::factory($config);
        return $this;
    }

    private function setDeltaPath($deltaPath)
    {
        $this->_deltaPath = $deltaPath;
        return $this;
    }

    private function setLog(Zend_Log $log)
    {
        $this->_log = $log;
        return $this;
    }
    
    private function existsTableVersion()
    {
        $response = $this->_client->listTables(array(
            'Limit' => 20
        ));        
        return in_array(self::DBVERSION_TABLE_NAME, $response['TableNames']);
    }
    
    public function setupVersion()
    {        
        $tablaVersion = array(
            'TableName' => self::DBVERSION_TABLE_NAME,
            'AttributeDefinitions' => array(
                array(
                    'AttributeName' => 'idVersion',
                    'AttributeType' => 'N'
                )
            ),
            'KeySchema' => array(
                array(
                    'KeyType' =>  'HASH',
                    'AttributeName' => 'idVersion'
                )
            ),
            'ProvisionedThroughput' => array(
                'NumberOfDecreasesToday' => 0,
                'WriteCapacityUnits' => 1,
                'ReadCapacityUnits' => 1
            )
        );
     
        $this->_log->log("===== Sync Starts with DynamoDB =====", \Phalcon\Logger::INFO);
        try {
            
            if ($this->existsTableVersion()) {
                $msg = 'La Tabla de versionado ya existe.';
            } else {
                $this->_client->createTable($tablaVersion);
                $this->_client->waitUntilTableExists(array('TableName' => $tablaVersion['TableName']));

                $this->_client->putItem(array(
                    'TableName' => self::DBVERSION_TABLE_NAME,
                    'Item' => array(
                        'idVersion' => array('N' => 1),                
                        'fechaActualiza' => array('S' => date('Y-m-d H:i:s')),
                        'version' => array('S' => '0')
                    ),
                    "ReturnConsumedCapacity" => "TOTAL"
                ));
                $msg = 'Se creo la tabla de versionado.';
            }
            
            echo $msg.PHP_EOL;
            $this->_log->log($msg, \Phalcon\Logger::INFO);
            
        } catch (\Exception $ex) {            
            $this->_log->log($ex->getMessage(), \Phalcon\Logger::ERROR);
            
        }
        $this->_log->log("===== Sync Ends =====", \Phalcon\Logger::INFO);
    }

    private function getVersion()
    {
        try {
            $response = $this->_client->scan(array(
                'TableName' => self::DBVERSION_TABLE_NAME                    
            ));      

            $numberVersion = (int)$response['Items'][0][self::DBVERSION_COL_NAME]['S'];
            return $numberVersion;
        } catch (Exception $ex) {
            return 0;
        }
    }

    private function setVersion($version)
    {
        try {
            $this->_client->updateItem ( array (
                'TableName' => self::DBVERSION_TABLE_NAME,
                "Key" => array (
                    "idVersion" => array (
                        "N" => 1 
                    ) 
                ),
                "ExpressionAttributeNames" => array (
                    "#NV" => self::DBVERSION_COL_NAME,
                    "#NF" => "fechaActualiza",
                ),
                "ExpressionAttributeValues" =>  array (
                    ":val1" => array('S' => (string)$version),
                    ":val2" => array('S' => date('Y-m-d H:i:s')),
                ) ,
                "UpdateExpression" => "set #NV = :val1, #NF = :val2 ",
                "ReturnValues" => "ALL_NEW" 
            ) );      
        } catch (Exception $ex) {
            return false;
        }
    }

}
