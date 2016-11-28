<?php

/**
 * Description of CompartirAviso
 *
 * @author Jesus Fabian
 */
class Application_Form_CompartirPorMail extends App_Form
{
    public function init()
    {
        parent::init();
        //Nombre emisor
        $e = new Zend_Form_Element_Text('nombreEmisor');
        $e->setRequired();
        $this->addElement($e);
        
        //Correo emiso
        $e = new Zend_Form_Element_Text('correoEmisor');
        $e->setRequired();
        $v = new Zend_Validate_EmailAddress();
        $e->addValidator($v);
        $this->addElement($e);
        
        //Nombre receptor
        $e = new Zend_Form_Element_Text('nombreReceptor');
        $e->setRequired();
        $this->addElement($e);
        
        //Correo receptor
        $e = new Zend_Form_Element_Text('correoReceptor');
        $e->setRequired();
        $v = new Zend_Validate_EmailAddress();
        $e->addValidator($v);
        $this->addElement($e);
        
        //Mensaje
        $e = new Zend_Form_Element_Textarea('mensajeCompartir');
        $this->addElement($e);
        
        //Campo Oculto
        $e = new Zend_Form_Element_Hidden('hdnOculto');
        $this->addElement($e);
        
        //Campo Oculto
        $e = new Zend_Form_Element_Hash('fCAtok',array('salt' => md5(uniqid())));
        $e->setTimeout(600); //10min.
        $this->addElement($e);
        
        //Submit
        $e = new Zend_Form_Element_Submit('Submit');
        $e->setLabel('Enviar');
        $this->addElement($e);
    }
}