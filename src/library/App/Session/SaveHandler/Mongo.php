<?php
/**
 * @see Zend_Session
 */
require_once 'Zend/Session.php';

class App_Session_SaveHandler_Mongo implements Zend_Session_SaveHandler_Interface {
	
    	
    /**
     * MongoDb connection
     *
     * @var Mongo
     */
    protected $_conn;
    
    /**
     * Mongo servers
     *
     * @var string
     */
    protected $_servernames;
    
    /**
     * Mongo connection options
     *
     * @var array
     */
    protected $_connectoptions;
    
    /**
     * Mongo database
     *
     * @var MongoDB
     */
    protected $_db;
    
    /**
     * Mongo collection
     *
     * @var MongoCollection
     */
    protected $_collection;
    
    /**
     * Data for register Log
     * 
     * @var Zend_Log
     */
    protected $_log;
    
    /**
     * Constructor
     *
     * @param array|Zend_Config $config
     * @throws Zend_Session_SaveHandler_Exception
     * @return void
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
              . 'configuration options for GcLib_Zend_Session_SaveHandler_MongoDb.');
        }
        
        foreach ($config as $key => $value) {
            do {                
                switch ($key) {
                    case 'host': 
                                $this->_servernames = $value;
                                break;                    
                    case 'collection': 
                                $this->_collection = $value;
                                break;
                    default:
                        // unrecognized options passed to mongodb connection options
                        break 2;
                }
                unset($config[$key]);
            } while (false);
        }          
        $this->_db = $config['mongo']['db'];
        $this->_connectoptions = $config['mongo'];        
        if (isset($config['lifetime'])) {
            $this->_timeToLife = (int) $config['lifetime'];
        } else {
            $this->_timeToLife = (int) ini_get('session.gc_maxlifetime');
        }
        
        $this->_log = $this->getLog();
    }
	
    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct() 
    {        
        Zend_Session::writeClose();
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
     * Open a mongodb connection
     *
     * @return Mongo $_conn
     */
    public function getConnection() 
    {   
        try {
            if (!$this->_conn) {
                $this->_conn = new MongoClient('mongodb://'.$this->_servernames,$this->_connectoptions);
            }
            return $this->_conn;
        } catch (Exception $ex) {
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::EMERG);
            return false;
        }
    }
	
    /**
     * Open Session - retrieve resources
     *
     * @param string $save_path
     * @param string $name
     */
    public function open($save_path, $name) 
    {          
        $this->_conn = $this->getConnection();
        if ($this->_conn) {
            $this->_db = $this->_conn->selectDB($this->_db);
            $this->_collection = $this->_db->selectCollection($this->_collection);
            return true;
        }
        return false;
        
    }

    /**
     * Close Session - free resources
     *
     * @return boolean
     */
    public function close() {
    	//$this->_conn->close();
    	return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id) 
    {
        $return = '';
        if ($this->_conn && ($this->_collection instanceof MongoCollection)) {
            $session = $this->_collection->findOne(array('sessionid'=>$id));
            if(!empty($session)) {
                $return = $session['data'];
            }    
        }
    	return $return;
    }

    /**
     * Write Session - commit data to resource
     *
     * @param string $id
     * @param mixed $data
     * @return boolean
     */
    public function write($id, $data) 
    {           
        try {
            $newdata = array(
                'sessionid' => $id,
                'lifetime' => new MongoDate(time() + $this->_timeToLife),
                'data' => (string) $data
            );        

            $return = false;
            if ($this->_collection instanceof MongoCollection) {
                $return = (boolean) $this->_collection->update(array(
                    'sessionid' => $id
                ), $newdata, array(
                    "upsert" => true
                ));		 
            }
            return $return;
            
        } catch (Exception $ex) {
            return false;            
        }
    	
    }

    /**
     * Destroy Session - remove data from resource for
     * given session id
     *
     * @param string $id
     * @return boolean
     */
    public function destroy($id) {
    	
    	$return = (boolean) $this->_collection->remove(array(
            'sessionid' => $id
        ), array(
            "justOne" => true
        ));
    	return $return;
    }

    /**
     * Garbage Collection - remove old session data older
     * than $maxlifetime (in seconds)
     *
     * @param int $maxlifetime
     * @return boolean
     */
    public function gc($maxlifetime) 
    {
    	$this->_collection->remove(array(
            'lifetime' => array(
                    '$lte' => new MongoDate( time() + $maxlifetime )
            )
        ));
    	return true;
    }
}

