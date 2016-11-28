<?php

/**
 * Description of Form Mensaje
 *
 * @author Julio
 */
class Application_Form_Mensajes extends App_Form
{
    
    public function __construct($hasHiddenId = false)
    {
        parent::__construct();
        if ($hasHiddenId) {
            $this->addMensajeId();
        }
    }
    
    public function init()
    {
        parent::init();
        $this->setMethod('post');

        //mensaje
        $e = new Zend_Form_Element_Checkbox('tipo_mensaje');
        $e->setValue('0');
        $this->addElement($e);
        
        
        $e = new Zend_Form_Element_Textarea('cuerpo');
        $this->addElement($e);

        $e = new Zend_Form_Element_Hash('token');
        $this->addElement($e);        
    }
    
    public function addMensajeId()
    {
        $e = new Zend_Form_Element_Hidden('id_mensaje');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);
    }
    
    public function setHiddenId($id)
    {
        $e = $this->getElement('id_mensaje');
        $e->setValue($id);
    }
}