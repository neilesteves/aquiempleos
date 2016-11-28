<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of headJs
 *
 * @author ronald
 */
class App_View_Helper_HeadCs extends Zend_View_Helper_HtmlElement
{
    //put your code here
    public $view;
    protected $_config;
    protected $_css;
    protected $_js;

    public function HeadCs()
    {
        $this->_config = Zend_Registry::get('config');
        $this->_css    = $this->_config->css->toArray();
        $this->_js     = $this->_config->js->toArray();
        $this->view    = Zend_Layout::getMvcInstance()->getView();
        return $this;
    }

    public function js()
    {
        return isset($this->_js[MODULE][CONTROLLER][ACTION]) ? $this->_js[MODULE][CONTROLLER][ACTION]
                : array();
    }

    public function HomeEmpresa()
    {
        return isset($this->_css[MODULE][CONTROLLER][ACTION]) ? $this->_css[MODULE][CONTROLLER][ACTION]
                : array();
    }

    public function Old()
    {
        $css        = array(
            'home' => array(
                'index' => array(
                    '/css/layout.css',
                    '/css/portada.css',
                    '/css/icons.css',
                    '/css/plugins.css',
                    '/css/layout2.css',
                    '/css/class.css',
                    '/css/plugins/jquery.fancybox.css',
                    '/css/printCip.css',
                ),
                'ALL' => array(
                    '/css/layout.css'
                ),
            ),
            'mi-cuenta' => array(
                'ALL' => array(
                    '/css/layout.css',
                    '/css/portada.css',
                    '/css/icons.css',
                    '/css/plugins.css',
                    '/css/layout2.css',
                    '/css/class.css',
                    '/css/plugins/jquery.fancybox.css',
                    '/css/printCip.css',
                ),
                'index' => array(
                )
            )
        );
        $action     = (CONTROLLER == 'home') ? 'index' : 'ALL';
        $controller = (CONTROLLER == 'home') ? CONTROLLER : 'home';
        return array();
    }
}