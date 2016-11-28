<?php

class App_Migration_Dynamodb_Delta
{
    protected $_client;
    protected $_log;
    protected $_author;
    protected $_desc;

    public function __construct($db, Zend_Log $log = null)
    {
        $this->_client = $db;
        $this->_log = $log;
    }
    
    public function createTable($params)
    {
        $this->_client->createTable($params);
        $this->_client->waitUntilTableExists(array('TableName' => $params['TableName']));
        
    }

    public function up()
    {
    }

    public function down()
    {
    }

}