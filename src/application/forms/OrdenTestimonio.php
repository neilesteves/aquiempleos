<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Application_Form_OrdenTestimonio extends App_Form
{

    public function  init()
    {
        parent::init();
        $this->setAttrib('enctype', 'multipart/form-data');

        $config = Zend_Registry::get('config');
        $elementOrden = array ();
        
        for ($a = 1; $a <= $config->testimonios->cantidad->testimonio; $a++) {
            $elementOrden[$a] = $a;
        }
        $orden = new Zend_Form_Element_Select('orden');
        $orden->addMultiOptions($elementOrden);

        $this->addElement($orden);
    }

}