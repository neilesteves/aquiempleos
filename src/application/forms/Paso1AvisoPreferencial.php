<?php

class Application_Form_Paso1AvisoPreferencial extends App_Form
{
    
    public function init()
    {
        parent::init();

        // Combo contratos
        $valores = array('1'=>'con membresía','0'=>'sin membresía');
        $fMembresia = new Zend_Form_Element_Select('usoMembresia');
        $fMembresia->setRequired();
        //$fMembresia->setAttrib("onchange", "submit()");
        $fMembresia->addMultiOptions($valores);
        $fMembresiaVal = new Zend_Validate_InArray(array_keys($valores));
        $fMembresia->addValidator($fMembresiaVal);
        $this->addElement($fMembresia);
    }
    
    public function setContratos($values, $selected)
    {
        $cmb = $this->getElement("usoMembresia");
        $cmb->addMultiOptions($values);
        $cmb->setValue($selected);
        $fMembresiaVal = new Zend_Validate_InArray(array_keys($values));
        $cmb->addValidator($fMembresiaVal);
    }
}