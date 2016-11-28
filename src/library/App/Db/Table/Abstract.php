<?php
/**
 * Description of Abstract
 * @author dpozo
 */
class App_Db_Table_Abstract extends Zend_Db_Table_Abstract
{
    protected $_config;
    protected $_prefix;
    protected $_db;
    protected $_log;
    protected $_cache;

    public function __construct($config = array()) 
    {
        //parent::__construct($config);
        $this->_config = Zend_Registry::get('config');
        $this->_log = Zend_Registry::get('log');
        $this->_prefix = $this->_name.'_';
        $this->_db = $this->getAdapter();
        $this->_cache = Zend_Registry::get('cache');
        
        if (isset($_SESSION) && $this->_config->useExclusiveDbConnectionForAdmin == true
            && Zend_Auth::getInstance()->hasIdentity()) {
            $storage = Zend_Auth::getInstance()->getStorage()->read();
            if (isset($storage['usuario']->rol) && 
            $storage['usuario']->rol == Application_Form_Login::ROL_ADMIN) {
                $dbconfig = $this->_config->resources->db;
                $params = $dbconfig->params->toArray();
                $dbadmin = $this->_config->dbadmin;
                $params['username'] = $dbadmin->username;   
                $params['password'] = $dbadmin->password;
                $db = Zend_Db::factory($dbconfig->adapter, $params);
                $this->setDefaultAdapter($db);
            }
        }
        
        parent::__construct($config);
    }
    
    
    /**
     * @author Carlos Muñoz Ramirez <camura8503@gmail.com>
     * @param Array $getCols columns name
     * @return Array columns nanme
     */
    public function setCols($getCols) 
    {
        $cols = $this->_getCols();
        if (count($getCols) > 0) {
            $cols = array_intersect($this->_getCols() , $getCols);
        }
        
        return $cols;
    }
}
