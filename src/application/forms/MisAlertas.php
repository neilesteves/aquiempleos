<?php

/**
 * Description of Mis alertas
 *
 * @author Julio Florian
 */
class Application_Form_MisAlertas extends App_Form
{
    
    public function init()
    {
        parent::init();
        // Privacidad
        $e = new Zend_Form_Element_Checkbox('prefs_emailing_avisos');
        $e->setValue('1');
        $this->addElement($e);
        

        //Recibir informacion
        $e = new Zend_Form_Element_Checkbox('prefs_emailing_info');
        $e->setValue('1');
        $this->addElement($e);
        
        
        //Tecibir información del mercado laboral.
        $e = new Zend_Form_Element_Checkbox('prefs_emailing_mercado');
        $e->setValue('1');
        $this->addElement($e);
        
    }
}
