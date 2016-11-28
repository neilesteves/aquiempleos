<?php

/**
 * Description of App_View_Helper_CortarTexto
 *
 * @author josue
 */
class App_View_Helper_CortarTexto extends Zend_View_Helper_HtmlElement{
    
    protected $_text;
    protected $_size;
    protected $_pad;
    
    public function CortarTexto($texto, $size = 20, $pad = '...'){
        $this->_text = $texto;
        $this->_size = $size;
        $this->_pad = $pad;
        
        return $this->retornarTexto();
    }
    
    public function retornarTexto(){
        return $this->getText();
    }
    
    protected function getText(){
        $palabras = explode(' ', (string)  $this->_text);
        $cont = 0;
        $texto = '';
        foreach ($palabras as $palabra) {
            if ((strlen($texto) + strlen($palabra)) > $this->_size) {
                return $texto . $this->_pad;
            } else {
                $cont += strlen($palabra);
                $texto .= ' ' . $palabra;
            }
        }
        return $texto;
    }
}
