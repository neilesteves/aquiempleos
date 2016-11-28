<?php

/**
 * Description of Util para enviar y sobreescribir etiquetas para los titles o 
 * metas
 *
 * @author ronald
 */
class App_View_Helper_HeadMetas extends Zend_View_Helper_Abstract
{
  protected $_config;
  protected $_helper;
  protected $_headMetad;

  public function HeadMetas()
    {
      $this->_config=Zend_Registry::get('config');
     
      $this->_headMetad=$this->_config->headMeta->app->toArray();
      return $this;
    }
    /**
     * Funcion que imprime el title correspondiente
     * @return type
     */
    public function getTitle() 
    {
      $this->_helper = new Zend_View_Helper_Layout();
      $layout= $this->_helper->layout();
      if(is_array($layout->headMeta)){
        return $layout->headMeta['title'];
      }
      $headMeta=$this->_headMetad;
      return $headMeta['title'];
    }
    /**
     * Funcion que captura el valor del tiltle
     * @param type $title
     */
    public function setTitle($title) 
    {      
      $this->_helper = new Zend_View_Helper_Layout();
      $headMeta=$this->_helper->layout()->headMeta;
      $headMeta['title']=$title;
      Zend_Layout::getMvcInstance()->assign(
              'headMeta', $headMeta
        );
      
    }
    /**
     * 
     * @param type $descripcion
     */
    public function setDescription($descripcion) 
    {
       $this->_helper = new Zend_View_Helper_Layout();
      $headMeta=$this->_helper->layout()->headMeta;
       $headMeta['description']=$descripcion;
       Zend_Layout::getMvcInstance()->assign(
              'headMeta', $headMeta
        );
    }
    /**
     * Funcion que retorna las descripcion de un meta
     * @return type 
     */
    public function getDescription() 
    {
      $this->_helper = new Zend_View_Helper_Layout();
      $layout= $this->_helper->layout();
      if(is_array($layout->headMeta)){
        return $layout->headMeta['description'];
      }
      $headMeta=$this->_headMetad;
      return $headMeta['description'];
    }
    /**
     * 
     * @param type $keywords
     */
    public function setKeywords($keywords) 
    {
      $this->_helper = new Zend_View_Helper_Layout();
      $headMeta=$this->_helper->layout()->headMeta;
      $headMeta['keywords']=$keywords;
      Zend_Layout::getMvcInstance()->assign(
             'headMeta', $headMeta
       );
    }
    /**
     * 
     * @return type
     */
    public function getKeywords() 
    {
      $this->_helper = new Zend_View_Helper_Layout();
      $layout= $this->_helper->layout();
      if(is_array($layout->headMeta)){
        return $layout->headMeta['keywords'];
      }
      $headMeta=$this->_headMetad;
      return $headMeta['keywords'];
    }
    /**
     * 
     * @return string
     */
    public function getImgeOg() 
    {
      $this->_helper = new Zend_View_Helper_Layout();
      $layout= $this->_helper->layout();
      $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
      $meta=$layout->headMeta['ImgeOg'];
      $meta['image'] =$view->S($meta['image']);
      $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
      $meta['url']=SITE_URL.$view->url($params, null, true);
      if(is_array($meta)){
        return $meta;
      }
      $this->_headMetad['image'] =$view->S($layout->headMeta['ImgeOg']['image']);
      $headMeta=$this->_headMetad;
      return $headMeta['ImgeOg'];
    }
}