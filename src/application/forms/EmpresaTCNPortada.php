<?php

class Application_Form_EmpresaTCNPortada extends App_Form
{
    protected static $_defaultWebsite= 'http://';
    
    public function init()
    {
        parent::init();

        $e = new Zend_Form_Element_Hidden('id_empresa');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);
        
        $e = new Zend_Form_Element_Text('url_tcn');
        $e->setValue(self::$_defaultWebsite);
        $e->setRequired();
        $e->errMsg = 'Url inválido';
        $this->addElement($e);

        $e = new Zend_Form_Element_Text('prioridad_home');
        $e->errMsg = 'Ingrese prioridad.';
        $this->addElement($e);
        
        $e = new Zend_Form_Element_Select('portada');
        $e->addMultiOption('1', 'Sí');
        $e->addMultiOption('0', 'No');
        $e->errMsg = 'Seleccione prioridad.';
        $this->addElement($e);
        
    }

}