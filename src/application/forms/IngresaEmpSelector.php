<?php


class Application_Form_IngresaEmpSelector extends App_Form
{
    const ROL_POSTULANTE = 'postulante';
    const ROL_EMPRESA = 'empresa';
    
    public function init()
    {
        parent::init();
        
        // POSTULANTE/EMPRESA  Radio 
        $USel = new Zend_Form_Element_Radio('userPEEmp');
        $USel->addMultiOption("Pos", "Postulante");
        $USel->addMultiOption("Emp", "Empresa");
        //$USel->setValue("Emp");

        $USelVal = new Zend_Validate_NotEmpty();
        $USel->addValidator($USelVal);
        $USel->errMsg = $this->_mensajeRequired;
        $USel->setSeparator('');
        $USel->setAttrib('label_class', 'ioption');
        $this->addElement($USel);
        
    }
    
    public function setType($type)
    {
        if ($type == self::ROL_POSTULANTE) {
            $checked = "Pos";
        } else {
            $checked = "Emp";
        }
        $this->getElement('userPEEmp')->setValue($checked);
        return $this;
    }
    
    public static function factory($type)
    {
        $form = new self();
        return $form->setType($type);
    }
    
}