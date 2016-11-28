<?php

Class App_Util_Filter
{
    private $_tags      = null;
    private $_lines     = null;
    private $_html      = null;
    private $_alnumAW   = null;
    private $_alnum     = null;
    
    public function __construct() 
    {
        $this->_tags    = new Zend_Filter_StripTags;
        $this->_lines   = new Zend_Filter_StripNewlines;
        $this->_html    = new Zend_Filter_HtmlEntities;
        $this->_alnumAW   = new Zend_Filter_Alnum(
                array('allowwhitespace' => true));
        $this->_alnum   = new Zend_Filter_Alnum;
    }       
    
    public function escapeAlnum($data)
    {
        $noLines    = $this->_lines->filter($data);
        $alnum      = $this->_alnumAW->filter($noLines);
        $data       = $alnum;
        
        return $data;
    }
    
    public function clearTelephone($value)
    {
        $this->_alnum->filter($value);
        $value = substr($value, 0, 8);
        
        return $value;
    }
    
    public function clearToSEO($value)
    {
        $value = $this->_tags->filter($value);
        $value = $this->_lines->filter($value);
        return $value;
    }
    
    public function escape($value)
    {
        return $this->_tags->filter($value);
    }
        
        /**
    * Return sub string sin etiquetas HTML y puntos suspensivos al final
    * @param $string String 
    * @param $length Largo que queremos el substring
    * @return String con ...
    */

    public function getSubString($string, $length=NULL)
    {
        //Si no se especifica la longitud por defecto es 40
        if ($length == NULL)
            $length = 40;
        //Primero eliminamos las etiquetas html y luego cortamos el string
        $stringDisplay = substr(strip_tags($string), 0, $length);
     
        return $stringDisplay;
    }
}