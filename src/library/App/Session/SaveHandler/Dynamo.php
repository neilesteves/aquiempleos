<?php
/**
 * @see Zend_Session
 */
require_once 'Zend/Session.php';
/**
 * @see App_Session_SaveHandler_DynamoDBSessionHandler
 */
require_once 'App/Session/SaveHandler/DynamoDBSessionHandler.php';


class App_Session_SaveHandler_Dynamo implements Zend_Session_SaveHandler_Interface {
	
    
    /**
     * @var SessionHandler
     */
    protected $sessionHandler;
    
    /**
     * @var _timeToLife
     */
    protected $_timeToLife;
    
    /**
     * Constructor
     *
     * @param SessionHandler $sessionHandler DynamoDB session handler
     */
    public function __construct($config)
    {
        if ($config instanceof Zend_Config) {
            $config = $config->toArray();
        } else if (!is_array($config)) {
            /**
             * @see Zend_Session_SaveHandler_Exception
             */
            require_once 'Zend/Session/SaveHandler/Exception.php';

            throw new Zend_Session_SaveHandler_Exception(
                '$config must be an instance of Zend_Config or array of key/value pairs containing '
              . 'configuration options for App_Session_SaveHandler_Dynamo.');
        }


        if (isset($config['lifetime'])) {
            $this->_timeToLife = (int) $config['lifetime'];
        } else {
            $this->_timeToLife = (int) ini_get('session.gc_maxlifetime');
        }

        $saveHandlerConfig = array(
            // Locking strategy used for doing session locking.
            // 'locking_strategy' => null,

            // DynamoDb client object used for performing DynamoDB
            // operations.
            //
            // Note: you most likely want to leave this alone and allow
            // the factory to fetch your configured instance of
            // DynamoDB. However, if you override it with an object, we
            // will respect that choice.
            'dynamodb_client' => \Aws\DynamoDb\DynamoDbClient::factory($config['dynamo']),

            // Name of the DynamoDB table in which to store the
            // sessions.
            'table_name' => $config['table'],

            // Name of the hash key in the DynamoDB sessions table.
            'hash_key' => 'id',

            // Lifetime of an inactive session before it should be
            // garbage collected. Similar to PHP's
            // session.gc_maxlifetime
            'session_lifetime' => $this->_timeToLife,

            // Whether or not to use DynamoDB consistent reads for
            // GetItem.
            // 'consistent_read' => true,

            // Whether or not to use PHP's session auto garbage
            // collection triggers.
            //
            // Note that you may want this to be false in production,
            // and use a separate process to garbage collect old
            // sessions.
            'automatic_gc' => true,

            // Batch size used for removing expired sessions during
            // garbage collection.
            // 'gc_batch_size' => 25,

            // Delay between service operations during garbage
            // collection.
            // 'gc_operation_delay' => 0,

            // Maximum time (in seconds) to wait to acquire a lock before giving up
            // 'max_lock_wait_time' => 10,

            // Minimum time (in microseconds) to wait between attempts to acquire a lock
            // 'min_lock_retry_microtime' => 10000,

            // Maximum time (in microseconds) to wait between attempts to acquire a lock
            // 'max_lock_retry_microtime' => 50000,
        );

        $dynamodb = \Aws\DynamoDb\DynamoDbClient::factory($config['dynamo']);        
        $saveHandlerConfig['session_locking'] = false;
        $hand = new App_Session_SaveHandler_DynamoDBSessionHandler($dynamodb, $saveHandlerConfig);                

        $this->sessionHandler = $hand;
        $this->sessionHandler->register();        
        
    }
    
    /**
     * Open a session for writing. Triggered by session_start()
     *
     * Part of the standard PHP session handler interface
     *
     * @param  string $savePath Session save path
     * @param  string $name     Session name
     *
     * @return bool Whether or not the operation succeeded
     */
    public function open($savePath, $name)
    {   
        return $this->sessionHandler->open($savePath, $name);        
    }
    
    /**
     * Close a session
     *
     * Part of the standard PHP session handler interface
     *
     * @return bool Whether or not the operation succeeded
     */
    public function close()
    {
        return $this->sessionHandler->close();        
    }
    
    /**
     * Read session data stored in DynamoDB
     *
     * Part of the standard PHP session handler interface
     *
     * @param string $id The session ID
     *
     * @return string Session data
     */
    public function read($id)
    {
        return $this->sessionHandler->read($id);
    }
        
    /**
     * Write session data to DynamoDB
     *
     * Part of the standard PHP session handler interface
     *
     * @param string $id   The session ID
     * @param string $data The serialized session data
     *
     * @return bool Whether or not the operation succeeded
     */
    public function write($id, $data)
    {
        return $this->sessionHandler->write($id, $data);        
    }
    
    /**
     * Destroy a session stored in DynamoDB
     *
     * Part of the standard PHP session handler interface
     *
     * @param string $id The session ID
     *
     * @return bool Whether or not the operation succeeded
     */
    public function destroy($id)
    {
        return $this->sessionHandler->destroy($id);
    }
    
    /**
     * Trigger garbage collection on expired sessions
     *
     * Part of the standard PHP session handler interface
     *
     * @param int $maxlifetime The value of `session.gc_maxlifetime`. Ignored.
     *
     * @return bool Whether or not the operation succeeded
     */
    public function gc($maxlifetime)
    {        
        return $this->sessionHandler->gc($maxlifetime);
    }	
    
}

