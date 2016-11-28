<?php

/**
 * Description of Categoria
 *
 * @author Usuario
 */
class Application_Form_AdminPostulante extends App_Form
{
    //Max Length
    private $_maxlengthNombre = '28';
    private $_maxlengthApellido = '75';
    private $_maxlengthTipoDocDni = '8';
    private $_maxlengthTipoDocCe = '10';
    private $_maxlengthEmail = '75';
    
    //@codingStandardsIgnoreStart
    public static $valorDocumento;
    //@codingStandardsIgnoreEnd
    
    
    public function init()
    {
        parent::init();

        // Nombre
        $fNames = new Zend_Form_Element_Text('nombres');
        $fNames->setAttrib('maxLength', $this->_maxlengthNombre);
        $fNames->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthNombre,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $fNamesVal = new Zend_Validate_NotEmpty();
        $fNames->addValidator($fNamesVal);
        $fNames->errMsg = '¡Ingrese Nombre Correcto!';
        $this->addElement($fNames);
        
        // Apellido
        $fSurname = new Zend_Form_Element_Text('apellidos');
        $fSurname->setAttrib('maxLength', $this->_maxlengthNombre);
        $fSurname->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthNombre,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $fSurnameVal = new Zend_Validate_NotEmpty();
        $fSurname->addValidator($fSurnameVal);
        $fSurname->errMsg = '¡Ingrese Apellido Correcto!';
        $this->addElement($fSurname);
        
        //Num Documento
        $fNDoc = new Zend_Form_Element_Text('num_doc');
        $fNDoc->addValidator(new Zend_Validate_NotEmpty(), true);
        $fNDoc->setAttrib('maxLength', $this->_maxlengthTipoDocDni);
        $fNDocVal  = new Zend_Validate_StringLength(
            array('min' => $this->_maxlengthTipoDocDni, 
                'max' => $this->_maxlengthTipoDocDni,
                'encoding' => $this->_config->resources->view->charset
            )
        );
        $fNDoc->addValidator($fNDocVal);
        $this->addElement($fNDoc);
        
        //email
        $fEmail = new Zend_Form_Element_Text('email');
        $fEmail->setAttrib('maxLength', $this->_maxlengthEmail);
        $fEmailVal = new Zend_Validate_EmailAddress(
            array("allow"=>Zend_Validate_Hostname::ALLOW_ALL),
            true
        );
        $fEmail->addFilter(new Zend_Filter_StringToLower());
        $fEmail->addValidator($fEmailVal, true);
        $fEmail->addValidator(new Zend_Validate_NotEmpty(), true);
        $fEmailVal->setMessage(
            'No parece ser un correo electrónico valido',
            Zend_Validate_EmailAddress::INVALID
        );
        $this->addElement($fEmail);
    }
}

