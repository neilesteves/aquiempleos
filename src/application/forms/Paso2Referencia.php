<?php

class Application_Form_Paso2Referencia extends App_Form
{
    private $_maxlengthNombre = '75';
    private $_maxlengthCargo = '75';
    private $_maxlengthTelefono = '30';
    
    protected $_idPostulante;
    
    public function __construct($idPostulante, $hasHiddenId = false  )
    {
        $this->_idPostulante = $idPostulante;
        
        parent::__construct();
        if ($hasHiddenId) {
            $this->addReferenciaId();
        }
    }
    
    public function init()
    {
        parent::init();
        $this->setMethod('post');

        //
        $experiencia = new Application_Model_Experiencia();
        $listaExperiencia = $experiencia->getExperienciaMiCuenta($this->_idPostulante);
        $e = new Zend_Form_Element_Select('listaexperiencia');
        $e->setRequired();
        $v = new Zend_Validate_InArray(array_keys($listaExperiencia));
        $e->addValidator($v);
        $e->addMultiOption('0', 'Seleccione una experiencia');
        $e->errMsg = $this->_mensajeRequired;
        $e->addMultiOptions($listaExperiencia);
        
        $this->addElement($e);
        
        //Nombre
        $fSurname = new Zend_Form_Element_Text('nombre');
        $fSurname->setRequired();
        $fSurname->setAttrib('maxLength', $this->_maxlengthNombre);
        $fSurname->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '0', 'max' => $this->_maxlengthNombre,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $fSurnameVal = new Zend_Validate_NotEmpty();
        $fSurname->addValidator($fSurnameVal);
        $fSurname->errMsg = 'Ingrese un nombre válido.';
        $this->addElement($fSurname);
        
        //puesto o cargo
        $fcargo = new Zend_Form_Element_Text('cargo');
        $fcargo->setRequired();
        $fcargo->setAttrib('maxLength', $this->_maxlengthCargo);
        $fcargo->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '0', 'max' => $this->_maxlengthCargo,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $fcargo->errMsg = 'Ingrese un cargo válido';
        $this->addElement($fcargo);
        
        // Telefono
        $fTlfFC = new Zend_Form_Element_Text('telefono');
        $fTlfFC->setRequired();
        $fTlfFC->setAttrib('maxLength', $this->_maxlengthTelefono);
        $fTlfFC->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '6', 'max' => $this->_maxlengthTelefono,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        
        $fTlfFCVal = new Zend_Validate_NotEmpty();
        $fTlfFC->addValidator($fTlfFCVal);
        $fTlfFC->errMsg = $this->_mensajeRequired;
        $this->addElement($fTlfFC);
        
        // Telefono 2
        $fTlf = new Zend_Form_Element_Text('telefono2');
        $fTlf->setAttrib('maxLength', $this->_maxlengthTelefono);
        $fTlf->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '6', 'max' => $this->_maxlengthTelefono,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $this->addElement($fTlf);

        //email
        $fEmail = new Zend_Form_Element_Text('email');
        $fEmailVal = new Zend_Validate_EmailAddress();
        $fEmail->setAttrib('maxLength', 75);
        $fEmail->addValidator($fEmailVal, true);
        $fEmail->errMsg = "No parece ser un correo electrónico valido";
        $this->addElement($fEmail);
    }
    
    public function addReferenciaId()
    {
        $e = new Zend_Form_Element_Hidden('id_referencia');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $e->setAttrib('class', 'hidden_id');
        $this->addElement($e);
    }
    
    public function setHiddenId($id)
    {
        $e = $this->getElement('id_referencia');
        $e->setAttrib('class', 'hidden_id');
        $e->setValue($id);
    }
}