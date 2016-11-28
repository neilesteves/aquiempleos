<?php

/**
 * Description of Form PagarAviso
 *
 * @author eanaya
 */
class Application_Form_PagarAviso extends App_Form
{
    public function init()
    {
        parent::init();
        
        //Comprobante
        $e = new Zend_Form_Element_Radio('comprobante');
        
        $this->addElement($e);

        //Submit
        $e = new Zend_Form_Element_Submit('Submit');
        $this->addElement($e);
    }
    
}