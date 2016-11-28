<?php

class Application_Form_Trabajo extends App_Form
{
    public function init()
    {
        parent::init();
        
        $titulo = new Zend_Form_Element_Text('titulo');
        $titulo->setRequired(true);
        
        $empresa = new Zend_Form_Element_Text('empresa');
        $rubro = new Zend_Form_Element_Select('rubro');
        
        $this->addElements(array($titulo, $empresa, $rubro));
    }
}