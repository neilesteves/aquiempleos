<?php


class Application_Form_IngresaSelector extends App_Form
{
    const ROL_POSTULANTE = 'postulante';
    const ROL_EMPRESA = 'empresa';

    public function init()
    {
        parent::init();

        // POSTULANTE/EMPRESA  Radio
        $USel = new Zend_Form_Element_Radio('userPEI');
        $USel->addMultiOption("postulante", "Postulante");
        $USel->addMultiOption("empresa", "Empresa");
        $USel->setAttrib('label_class', 'ioption');
        //$USel->setValue("Pos");

        $USelVal = new Zend_Validate_NotEmpty();
        $USel->addValidator($USelVal);
        $USel->errMsg = $this->_mensajeRequired;
        $USel->setSeparator('&nbsp;&nbsp;');
        $this->addElement($USel);

    }

    public function setType($type)
    {
        if ($type == self::ROL_POSTULANTE) {
            $checked = "postulante";
        } else {
            $checked = "empresa";
        }
        $this->getElement('userPEI')->setValue($checked);
        return $this;
    }

    public static function factory($type)
    {
        $form = new self();
        return $form->setType($type);
    }

}