<?php

class App_Controller_Action_Helper_Replace extends Zend_Controller_Action_Helper_Abstract
{

    public function cleanString($cadena)
    {
        
        $texto = strtolower($cadena);
        $texto = trim(str_replace(' ', '', $texto));
        $texto = str_replace('0', '', $texto);
        $texto = str_replace('1', '', $texto);
        $texto = str_replace('2', '', $texto);
        $texto = str_replace('3', '', $texto);
        $texto = str_replace('4', '', $texto);
        $texto = str_replace('5', '', $texto);
        $texto = str_replace('6', '', $texto);
        $texto = str_replace('7', '', $texto);
        $texto = str_replace('8', '', $texto);
        $texto = str_replace('9', '', $texto);
        $texto = str_replace('ñ', 'n', $texto);
        $texto = str_replace("á", "a", $texto);
        $texto = str_replace("é", "e", $texto);
        $texto = str_replace("í", "i", $texto);
        $texto = str_replace("ó", "o", $texto);
        $texto = str_replace("ú", "u", $texto);
        $texto = str_replace("ñ", "n", $texto);
        $texto = str_replace(".", "", $texto);
        $texto = str_replace("-", "", $texto);
        $texto = str_replace("/", "", $texto);
        $texto = str_replace("(", "", $texto);
        $texto = str_replace(")", "", $texto);
        $texto = str_replace("_", "", $texto);
        $texto = str_replace(",", "", $texto);
        
        return $texto;
    }
    
    /*
    Genera el slug para la búsqueda de empresa en Búsqueda avanzada
     * return $texto 
    */
    public function cleanSlugEmpresa($cadena)
    {
        
        $texto = strtolower($cadena);
        $texto = trim($texto);
        $texto = str_replace(' ', '-', $texto);
        $texto = str_replace('ñ', 'n', $texto);
        $texto = str_replace("á", "a", $texto);
        $texto = str_replace("é", "e", $texto);
        $texto = str_replace("í", "i", $texto);
        $texto = str_replace("ó", "o", $texto);
        $texto = str_replace("ú", "u", $texto);
        $texto = str_replace("--", " ", $texto);
        $texto = str_replace(" ", "-", $texto);
        $texto = str_replace(".", "", $texto);
        
        return $texto;
    }
    
    
}
