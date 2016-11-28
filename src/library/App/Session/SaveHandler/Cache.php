<?php
require_once 'Zend/Session.php';

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Memcached
 *
 * @author ronald
 */
class App_Session_SaveHandler_Cache implements Zend_Session_SaveHandler_Interface
{
    private $maxlifetime = 3600;
    public $cache        = '';
    public $_logError    = '';

    public function __construct($cacheHandler)
    {
//        $mc = new Memcached();
//        $mc->addServer("localhost", 11211);
        $this->cache       = Zend_Registry::get('cache');
        $this->_logError   = $this->getLog();
        $this->maxlifetime = 86400;
    }

    public function open($save_path, $name)
    {

        return true;
    }

    public function close()
    {
        // session_commit();
        return true;
    }

    public function read($id)
    {
        //$this->log->info($id);
        if (!($data = $this->cache->load($id))) {
            return '';
        } else {
            return $data;
        }
    }

    protected function getLog()
    {
        return Zend_Registry::get('log');
    }

    public function write($id, $sessionData)
    {
        //if ($this->cache->test($id)) {
        $this->cache->save($sessionData, $id, array(), $this->maxlifetime);
        // }
        return true;
    }

    public function destroy($id)
    {
        //  session_destroy();
        $this->cache->remove($id);
        return true;
    }

    public function gc($notusedformemcache)
    {
                return true;

    }
}
