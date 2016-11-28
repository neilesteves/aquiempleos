<?php

class App_View_Helper_IdExperiencia extends Zend_View_Helper_HtmlElement
{
    /*
     * recibe parametro fecha del tipo date() pero si deseas la fecha actual
     * solo pasarle "now"
     */
    public function IdExperiencia($idRef)
    {
        
       $referenciaModel = new Application_Model_Referencia;
       $dataRef = $referenciaModel->fetchRow('id = '.$idRef);
       return $dataRef['id_experiencia'];
       
    }
}
