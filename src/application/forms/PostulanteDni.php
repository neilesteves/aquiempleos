<?php


class Application_Form_PostulanteDni extends App_Form
{
	private $_maxlengthTipoDocDni = '8';
	private $_maxlengthNumRuc = '11';
        
        const ROL_POSTULANTE = 'postulante';
        const ROL_EMPRESA = 'empresa';
	
    public function init()
    {
        parent::init();

        //Dni
        $e = new Zend_Form_Element_Text('num_doc');
        $e->setRequired();
        $e->addValidator(new Zend_Validate_NotEmpty(), true);
        $e->setAttrib('maxLength', $this->_maxlengthTipoDocDni);
        $this->addElement($e);

        //Ruc
        $fNRuc = new Zend_Form_Element_Text('num_ruc');
        $fNRuc->setRequired();
        $fNRuc->addValidator(new Zend_Validate_NotEmpty(), true);
        $fNRuc->setAttrib('maxLength', $this->_maxlengthNumRuc);
        $this->addElement($fNRuc);

        //Submit
        $e = new Zend_Form_Element_Submit('Validar');
        $this->addElement($e);
    }
    
    public function setType($type)
    {
        $e = new Zend_Form_Element_Hidden('tipo');
        $e->setValue($type);
        $this->addElement($e);
//        if ($type == Application_Form_Login::ROL_POSTULANTE) {
//            $emailMsg = 'Ingresa tu e-mail';
//        } else {
//            $emailMsg = 'Ingrese su e-mail';
//        }
//        $this->getElement('email')->setValue($emailMsg);
        return $this;
    }
    
    public static function factory($type)
    {
        $form = new self();
        return $form->setType($type);
    }
    
}