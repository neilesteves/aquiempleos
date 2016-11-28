<?php
/**
 * Formulario - Padre para agregar el tokena todos los formularios que extiendan de el
 */
class Application_Form_HashForm extends App_Form
{
    public function init()
    {
        $this->addElement('hash', 'hash', array('salt' => 'unique'));
    }
}