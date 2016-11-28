<?php

class App_Session_SaveHandler_DynamoDBSessionHandler
{
    /**
     * @var AmazonDynamoDB The DyanamoDB client.
     */
    protected $_dynamodb = null;

    /**
     * @var string The session save path (see <php:session_save_path()>).
     */
    protected $_save_path = null;

    /**
     * @var string The session name (see <php:session_name()>).
     */
    protected $_session_name = null;

    /**
     * @var boolean Keeps track of if the session is open.
     */
    protected $_open_session = null;

    /**
     * @var boolean Keeps track of whether the session is open.
     */
    protected $_session_written = false;

    /**
     * @var string The name of the DynamoDB table in which to store sessions.
     */
    protected $_table_name = 'sessions';

    /**
     * @var string The name of the hash key in the DynamoDB sessions table.
     */
    protected $_hash_key = 'id';

    /**
     * @var integer The lifetime of an inactive session before it should be garbage collected.
     */
    protected $_session_lifetime = 0;

    /**
     * @var boolean Whether or not the session handler should do consistent reads from DynamoDB.
     */
    protected $_consistent_reads = true;

    /**
     * @var boolean Whether or not the session handler should do session locking.
     */
    protected $_session_locking = true;

    /**
     * @var integer Maximum time, in seconds, that the session handler should take to acquire a lock.
     */
    protected $_max_lock_wait_time = 30;

    /**
     * @var integer Minimum time, in microseconds, that the session handler should wait to retry acquiring a lock.
     */
    protected $_min_lock_retry_utime = 10000;

    /**
     * @var integer Maximum time, in microseconds, that the session handler should wait to retry acquiring a lock.
     */
    protected $_max_lock_retry_utime = 100000;

    /**
     * @var array Type for casting the configuration options.
     */
    protected static $_option_types = array(
        'table_name'           => 'string',
        'hash_key'             => 'string',
        'session_lifetime'     => 'integer',
        'consistent_reads'     => 'boolean',
        'session_locking'      => 'boolean',
        'max_lock_wait_time'   => 'integer',
        'min_lock_retry_utime' => 'integer',
        'max_lock_retry_utime' => 'integer',
    );
    
    
    private $_logError;



    /**
     * Initializes the session handler and prepares the configuration options.
     *
     * @param \Aws\DynamoDb\DynamoDbClient $dynamodb (Required) An instance of the DynamoDB client.
     * @param array $options (Optional) Configuration options.
     */
    public function __construct(\Aws\DynamoDb\DynamoDbClient $dynamodb, array $options = array())
    {
        // Store the AmazonDynamoDB client for use in the session handler
        $this->_dynamodb = $dynamodb;

        // Do type conversions on options and store the values
        foreach ($options as $key => $value) {
            if (isset(self::$_option_types[$key])) {
                settype($value, self::$_option_types[$key]);
                $this->{'_' . $key} = $value;
            }
        }

        // Make sure the lifetime is positive. Use the gc_maxlifetime otherwise
        if ($this->_session_lifetime <= 0) {
            $this->_session_lifetime = (integer) ini_get('session.gc_maxlifetime');
        }
        
        $this->_logError = $this->getLog();
    }

    /**
     * Destruct the session handler and make sure the session gets written.
     *
     * NOTE: It is usually better practice to call <code>session_write_close()</code>
     * manually in your application as soon as session modifications are complete. This
     * is especially true if session locking is enabled (which it is by default).
     *
     * @see http://php.net/manual/en/function.session-set-save-handler.php#refsect1-function.session-set-save-handler-notes
     */
    public function __destruct()
    {
            session_write_close();
    }

    /**
     * Log for error session
     * 
     * @return Zend_Log
     */
    protected function getLog()
    {
        return Zend_Registry::get('log');
    }
    
    /**
     * Register DynamoDB as a session handler.
     *
     * Uses the PHP-provided method to register this class as a session handler.
     *
     * @return DynamoDBSessionHandler Chainable.
     */
    public function register()
    {
        session_set_save_handler(
                array($this, 'open'),
                array($this, 'close'),
                array($this, 'read'),
                array($this, 'write'),
                array($this, 'destroy'),
                array($this, 'garbage_collect')
        );

        return $this;
    }

    /**
     * Checks if the session is open and writable.
     *
     * @return boolean Whether or not the session is still open for writing.
     */
    public function is_session_open()
    {
        return (boolean) $this->_open_session;
    }

    /**
     * Delegates to <code>session_start()</code>
     *
     * @return DynamoDBSessionHandler Chainable.
     */
    public function open_session()
    {
        session_start();
        return $this;
    }

    /**
     * Delegates to <code>session_commit()</code>
     *
     * @return DynamoDBSessionHandler Chainable.
     */
    public function close_session()
    {
        session_commit();
        return $this;
    }

    /**
     * Delegates to <code>session_destroy()</code>
     *
     * @return DynamoDBSessionHandler Chainable.
     */
    public function destroy_session()
    {
        session_destroy();
        return $this;
    }

    /**
     * Open a session for writing. Triggered by <php:session_start()>.
     *
     * Part of the standard PHP session handler interface.
     *
     * @param string $save_path (Required) The session save path (see <php:session_save_path()>).
     * @param string $session_name (Required) The session name (see <php:session_name()>).
     * @return boolean Whether or not the operation succeeded.
     */
    public function open($save_path, $session_name)
    {
        $this->_save_path    = $save_path;
        $this->_session_name = $session_name;
        $this->_open_session = session_id();

        return true;
    }

    /**
     * Close a session from writing
     *
     * Part of the standard PHP session handler interface
     *
     * @return boolean Success
     */
    public function close()
    {
        try {
            if (!$this->_session_written) {

                // Ensure that the session is unlocked even if the write did not happen
                $id = $this->_open_session;
                $this->_dynamodb->updateItem(array(
                    'TableName'        => $this->_table_name,
                    'Key'              => $this->_dynamodb->formatAttributes(array($this->_hash_key => $this->_id($id))),                
                    'ExpressionAttributeValues' =>  array (
                        ':val1' => array('N' => time() + $this->_session_lifetime),
                        ':val2' => array('S' => \Aws\DynamoDb\Enum\AttributeAction::DELETE)
                    ),
                    'UpdateExpression' => 'set expires = :val1, vlock = :val2 '

                ));

                $this->_session_written = true;
            }
            
        } catch (Exception $ex) {
            $this->_logError->log('Session: '.$ex->getMessage(), Zend_Log::CRIT);
            $this->_session_written = false;
        }

        $this->_open_session = null;
        return $this->_session_written;
    }

    /**
     * Read a session stored in DynamoDB
     *
     * Part of the standard PHP session handler interface
     *
     * @param string $id (Required) The session ID.
     * @return string The session data.
     */
    public function read($id)
    {
        $result = '';

        try {
            
            // Get the session data from DynamoDB (acquire a lock if locking is enabled)
            if ($this->_session_locking) {
                $response = $this->_lock_and_read($id);
                $node_name = 'Attributes';
            }
            else {                   
                $response = $this->_dynamodb->getItem(array(
                    'TableName'      => $this->_table_name,
                    'Key'            => $this->_dynamodb->formatAttributes(array('id' => $this->_id($id))),
                    'ConsistentRead' => $this->_consistent_reads,
                ));
                $node_name = 'Item';
            }

            if ($response) {
                $resultado = $response->toArray();
                if (isset($resultado[$node_name]) && isset($resultado[$node_name]['expires']) && isset($resultado[$node_name]['data'])) {
                    // Check the expiration date before using
                    if ($resultado[$node_name]['expires'] > time()) {
                        $result = $resultado[$node_name]['data']['S'];
                    } else {
                        $this->destroy($id);
                    }
                }
            }
        } catch (Exception $ex) {
            $this->_logError->log('Session: '.$ex->getMessage(), Zend_Log::CRIT);
        }

        return $result;
    }

    /**
     * Write a session to DynamoDB.
     *
     * Part of the standard PHP session handler interface.
     *
     * @param string $id (Required) The session ID.
     * @param string $data (Required) The session data.
     * @return boolean Whether or not the operation succeeded.
     */
    public function write($id, $data)
    {
        try {            
            // Write the session data to DynamoDB
            $items = array(
                $this->_hash_key => $this->_id($id),
                'expires'        => (string)(time() + $this->_session_lifetime),
                'data'           => (string)$data,
            );

            if (empty($items['data'])) {
                unset($items['data']);
            }

            $this->_dynamodb->putItem(array(
                'TableName' => $this->_table_name,
                'Item'      => $this->_dynamodb->formatAttributes($items),
                'ReturnConsumedCapacity' => 'TOTAL'
            ));            
            $this->_session_written = true;            
        } catch (Exception $ex) {
            $this->_logError->log('Session: '.$ex->getMessage(), Zend_Log::CRIT);
            $this->_session_written = false;
        }
        return $this->_session_written;
    }

    /**
     * Delete a session stored in DynamoDB.
     *
     * Part of the standard PHP session handler interface.
     *
     * @param string $id (Required) The session ID.
     * @param boolean $garbage_collect_mode (Optional) Whether or not the handler is doing garbage collection.
     * @return boolean Whether or not the operation succeeded.
     */
    public function destroy($id, $garbage_collect_mode = false)
    {
        try {
            // Make sure we don't prefix the ID a second time
            if (!$garbage_collect_mode) {
                $id = $this->_id($id);
            }

            $delete_options = array(
                'TableName' => $this->_table_name,
                'Key'       => $this->_dynamodb->formatAttributes(array('id' => $id)),
            );

            // Make sure not to garbage collect locked sessions
            if ($garbage_collect_mode && $this->_session_locking) {
                $delete_options['Expected'] = array('vlock' => array('Exists' => false));
            }

            // Send the delete request to DynamoDB
            $this->_dynamodb->deleteItem($delete_options);
            $this->_session_written = true;
            
        } catch (Exception $ex) {
            $this->_logError->log('Session: '.$ex->getMessage(), Zend_Log::CRIT);
            $this->_session_written = false;
        }
        return $this->_session_written;
    }

    /**
     * Performs garbage collection on the sessions stored in the DynamoDB table.
     *
     * Part of the standard PHP session handler interface.
     *
     * @param integer $maxlifetime (Required) The value of <code>session.gc_maxlifetime</code>. Ignored.
     * @return boolean Whether or not the operation succeeded.
     */
    public function garbage_collect($maxlifetime = null)
    {
        try {
            // Send a search request to DynamoDB looking for expired sessions
            $response = $this->_dynamodb->scan(array(
                'TableName'  => $this->_table_name,
                'ScanFilter' => array(
                    'expires' => array(
                        'AttributeValueList' => array($this->_dynamodb->formatValue(time())),
                        'ComparisonOperator' => \Aws\DynamoDb\Enum\ComparisonOperator::LT,
                    )
                ),
            ));        
            // Delete the expired sessions
            if ($response) {
                $deleted = array();

                $resultados = $response->toArray();            
                // Get the ID of and delete each session that is expired
                foreach ($resultados['Items'] as $item) {
                    $id = (string) $item[$this->_hash_key][\Aws\DynamoDb\Enum\Type::STRING];
                    $deleted[$id] = $this->destroy($id, true);
                }            
                // Return true if all of the expired sessions were deleted
                return (array_sum($deleted) === count($deleted));
            }
            return false;
        } catch (Exception $ex) {
            $this->_logError->log('Session: '.$ex->getMessage(), Zend_Log::CRIT);
            return false;
        }
        
    }

    /**
     * Prefix the session ID with the session name and prepare for DynamoDB usage
     *
     * @param string $id (Required) The session ID.
     * @return array The HashKeyElement value formatted as an array.
     */
    protected function _id($id)
    {
        return trim($this->_session_name . '_' . $id, '_');
    }

    /**
     * Acquires a lock on a session in DynamoDB using conditional updates.
     *
     * WARNING: There is a <code>while(true);</code> in here.
     *
     * @param string $id (Required) The session ID.
     * @return CFResponse The response from DynamoDB.
     */
    protected function _lock_and_read($id)
    {
        
        $now = time();
        $timeout = $now + $this->_max_lock_wait_time;
        do {

            try {
                // Acquire the lock            
                $response = $this->_dynamodb->updateItem(array(
                    'TableName'        => $this->_table_name,
                    'Key'              => $this->_dynamodb->formatAttributes(array('id' => $this->_id($id))),
                    //'AttributeUpdates' => $this->_dynamodb->formatAttributes(array('lock' => $this->_id($id), 'update')),
                    'ExpressionAttributeValues' =>  array (
                        ':val1' => array('S' => $this->_id($id))
                    ) ,
                    'UpdateExpression' => 'set vlock = :val1',                    
                    'ReturnValues'     => 'ALL_NEW',
                ));            

                // If lock succeeds (or times out), exit the loop, otherwise sleep and try again
                if ($response || $now >= $timeout) {
                    return $response;
                } else {
                    usleep(rand($this->_min_lock_retry_utime, $this->_max_lock_retry_utime));
                    $now = time();
                } 
            } catch (Exception $ex) {
                $this->_logError->log('Session: '.$ex->getMessage(), Zend_Log::CRIT);
                return null;
            }

        }
        while (true);
        
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
        return TRUE;
    }	
    
}
