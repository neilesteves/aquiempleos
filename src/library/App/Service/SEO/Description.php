<?php
/**
 * @author Carlos Muñoz <camura8503@gmail.com>
 */

class App_Service_SEO_Description
{
    /**
     * @var Zend_View_Interface
     */
    private $_view;    
    
    /**
     * @var App_Util_Filter
     */
    private $_filter;
    
    private $_config    = null;
    
    const NUMBER_CHARACTERS = 197;
    const POINTS            = '...';
    
    public function __construct($view)
    {
        $this->_view    = $view;
        $this->_filter  = new App_Util_Filter;
        $this->_config  = Zend_Registry::get('config');
    }
    
    public function add($data)
    {
        if (empty($data))
            throw new Zend_Exception(__CLASS__ . ': aviso esta vacio');        
                
        $description = $this->_getDescription($data);
        
        $this->_setMetas($description);
    }
    
    protected function _setMetas($description)
    {
         $this->_view->headMeta()->appendName(
                "Description", $description
            );
    }
    
    protected function _getDescription($data)
    {
        $description = $data['funciones'] . ' ' . $data['responsabilidades'];
        
        $description = $this->_filter->clearToSEO($description);
        
        if (strlen($description) < self::NUMBER_CHARACTERS)
            return $description;
        
        $description = substr(
                $description,0 ,
                strrpos(substr($description, 0, self::NUMBER_CHARACTERS), ' '));
        
        return $description . self::POINTS;
    }
}