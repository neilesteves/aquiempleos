<?php


class Application_Form_RegistrarReferenciado extends App_Form
{

    private $_maxlengthNombreC = '56';
    private $_maxlengthApellidosC = '36';
    private $_maxlengthEmail = '50';
    private $_maxlengthTelefono = '30';
    
    public function __construct()
    {
        parent::__construct();
    }
    public function init()
    {
        parent::init();
        $this->setAction('/subir-cv');
        $this->setAttrib('enctype', 'multipart/form-data');

        // Email
        $fEmail = new Zend_Form_Element_Text('email');
        //$fEmail->setAttrib('maxLength', $this->_maxlengthEmail);
        //$fEmail->setRequired();
        
        //$fEmailVal = new Zend_Validate_EmailAddress(
        //    array("allow"=>Zend_Validate_Hostname::ALLOW_ALL),
        //    true
        //);
        //$fEmail->addFilter(new Zend_Filter_StringToLower());
        //$fEmail->addValidator($fEmailVal, true);
        //$fEmail->addValidator(new Zend_Validate_NotEmpty(), true);
        //$fEmail->errMsg = 'No parece ser un correo electrónico valido';
        $this->addElement($fEmail);

        // Sexo
        $fSexo = new Zend_Form_Element_Radio('sexoMF');
        $fSexo->addMultiOption("M", "Masculino");
        $fSexo->addMultiOption("F", "Femenino");
        $fSexo->setValue("M");
        $fSexoVal = new Zend_Validate_NotEmpty();
        $fSexo->addValidator($fSexoVal);
        $fSexo->errMsg = $this->_mensajeRequired;
        $fSexo->setSeparator('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        $this->addElement($fSexo);

        // Telefono Fijo/Cel
        $fTlfFC = new Zend_Form_Element_Text('telefono');
        $fTlfFC->setRequired();
        $fTlfFC->setAttrib('maxLength', $this->_maxlengthTelefono);
        $fTlfFC->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '0', 'max' => $this->_maxlengthTelefono,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $fTlfFCVal = new Zend_Validate_NotEmpty();
        $fTlfFC->addValidator($fTlfFCVal);
        $fTlfFC->errMsg = $this->_mensajeRequired;
        $this->addElement($fTlfFC);

        //Nombre 
        $fNombre = new Zend_Form_Element_Text('nombre');
        $fNombre->errMsg = $this->_mensajeRequired;
        $fNombre->setRequired();
        $fNombre->setAttrib('maxLength', $this->_maxlengthNombreC);
        $fNombre->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthNombreC,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        
        $val = new Zend_Validate_Alpha();
        $val->setMessage("No parce que solo fueran caracteres", Zend_Validate_Alpha::NOT_ALPHA);
        $val->setAllowWhiteSpace(true);
        $fNombre->addValidator($val);
        $this->addElement($fNombre);

        //Apellidos 
        $fApellidos = new Zend_Form_Element_Text('apellidos');
        $fApellidos->errMsg = $this->_mensajeRequired;
        $fApellidos->setRequired();
        $fApellidos->setAttrib('maxLength', $this->_maxlengthApellidosC);
        $fApellidos->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthApellidosC,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        
        $val = new Zend_Validate_Alpha();
        $val->setMessage("No parce que solo fueran caracteres", Zend_Validate_Alpha::NOT_ALPHA);
        $val->setAllowWhiteSpace(true);
        $fApellidos->addValidator($val);
        $this->addElement($fApellidos);

        //Curriculo
        $fCv = new Zend_Form_Element_File('path_cv');
        $fCv->setDestination($this->_config->urls->app->elementsCvRoot);
        $fCv->addValidator(
            new Zend_Validate_File_Size(array('max'=>$this->_config->app->maxSizeFile))
        );
        $fCv->addValidator('Extension', false, 'doc,pdf,docx');
        $fCv->addValidator('Count', false, array('min' =>1, 'max' => 1));
        $fCv->getValidator('Size')->setMessage('El límite del archivo es de 2MB');
        $fCv->getValidator('Extension')
            ->setMessage('Seleccione un archivo con extensión .doc,.pdf,.docx');
        $fCv->getValidator('Count')
            ->setMessage('Seleccione un archivo');
        $this->addElement($fCv);
    }
}