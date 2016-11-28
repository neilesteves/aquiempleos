<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SolrClient
 *
 * @author ronald
 */
class Solr_SolrClient
{

    /**
     *
     * @var string: core name 
     */
    protected $core;
    public $solrCli;
    private static $instance = array();

    //put your code here
    public function __construct()
    {
        $this->solrCli = $this->getClient();
    }

    private function _getInstance( $core )
    {
        //var_dump(self::$instance);exit;
        if(!isset(self::$instance[$core])) {
            $mConfigAviso = Zend_Registry::get('config')->solr;
            $instance = new Solarium\Client($mConfigAviso->toArray());
            $instance->setDefaultEndPoint($core);
            self::$instance[$core] = $instance;
        }
        return self::$instance[$core];
    }

    /**
     * 
     * @return \Solarium\Client
     */
    public function getClient()
    {
        return $this->_getInstance($this->core);
    }

}
