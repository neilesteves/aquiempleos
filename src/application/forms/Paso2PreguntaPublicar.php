<?php

/**
 * Description of Form Paso2 Section Pregunta
 *
 * @author Jesus
 */
class Application_Form_Paso2PreguntaPublicar extends App_Form
{

    public function __construct($hasHiddenId = false)
    {
        parent::__construct();
         if ($hasHiddenId) {
            $this->addPreguntaId();
        }
    }
    
    public function init()
    { 
        //Pregunta
        $e = new Zend_Form_Element_Textarea('pregunta');
        $this->addElement($e);
         $this->getValues();
    }
    
    public function addPreguntaId()
    {
        $e = new Zend_Form_Element_Hidden('id_pregunta');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);
    }
    
    public function setHiddenId($id)
    {
               $e = $this->getElement('id_pregunta');
        $e->setValue($id);
    }
}