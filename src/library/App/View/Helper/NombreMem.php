<?php

class App_View_Helper_NombreMem extends Zend_View_Helper_HtmlElement
{
    public function NombreMem($id)
    {
        $modelMembresia = new Application_Model_Membresia;
        $nombreMembresia = $modelMembresia->getNombreMembresia($id);
        
        if (is_null($nombreMembresia) || empty($nombreMembresia))
            $nombreMembresia = 'Cuenta gratuita';
        
        return $nombreMembresia;
    }
}