<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Application_Form_BuscarTestimonio extends App_Form
{

    function init()
    {
        parent::init();
        $this->setAction('');
        $this->setAttrib('enctype', 'multipart/form-data');

        $empresa = new Zend_Form_Element_Text('empresa');
        $empresa->errMsg = "Debe ingresar el nombre de la Empresa.";
        //$empresa->setRequired(true);
        $this->addElement($empresa);

        $referente = new Zend_Form_Element_Text('referente');
        $referente->errMsg = "Debe ingresar la referencia.";
        //$referente->setRequired(true);
        $this->addElement($referente);

        
    }

}