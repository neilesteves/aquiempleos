<?php

class Application_Form_TestRuc extends App_Form
{
    public function init()
    {
        parent::init();
       
        //Prueba Ruc
        $e = new Zend_Form_Element_Text('ruc');
        $e->setLabel("RUT: ");
        $v = new App_Validate_Ruc();
        $e->addValidator($v);
        $this->addElement($e);
        
        $this->addElement(new Zend_Form_Element_Submit('enviar'));
    }
}