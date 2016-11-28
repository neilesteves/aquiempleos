<?php

class Application_Form_Paso1Administrador extends App_Form
{
    private $_idAdministrador;
    
    private $_maxlengthNombre = '75';
    private $_maxlengthApellido = '75';
    private $_maxlengthPuesto = '75';
    private $_maxlengthArea = '75';
    private $_maxlengthTelefono = 14;
    private $_maxlengthAnexo = '5';
    private $_maxlengthEmail = '32';
    
    const MSJ_ERROR_TELEFONO = 'Ingrese un numero entre 7-14 digitos';
    
    public function __construct()
    {
        parent::__construct();
        $this->addElement('hash', 'csrf_token', array('salt' => get_class($this)) );
    }
    
    public function init()
    {
        parent::init();
        $this->setAction('/registro-empresa/');
        
        // Nombres
        $fNombres = new Zend_Form_Element_Text('nombres');
        $fNombres->errMsg="Debe ingresar un Nombre";
        $fNombres->setRequired();
        $fNombres->setAttrib('maxLength', $this->_maxlengthNombre);
        $fNombres->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthNombre,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $this->addElement($fNombres);

        // Apellidos
        $fApellidos = new Zend_Form_Element_Text('apellidos');
        $fApellidos->errMsg="Debe ingresar un Apellido";
        $fApellidos->setRequired();
        $fApellidos->setAttrib('maxLength', $this->_maxlengthApellido);
        $fApellidos->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthApellido,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $this->addElement($fApellidos);

        // Puesto
        $fPuesto = new Zend_Form_Element_Text("puesto");
        $fPuesto->setAttrib('maxLength', $this->_maxlengthPuesto);
        $fPuesto->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthPuesto,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $this->addElement($fPuesto);
        
        // Area
        $fArea = new Zend_Form_Element_Text("area");
        $fArea->setAttrib('maxLength', $this->_maxlengthArea);
        $fArea->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthArea,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $this->addElement($fArea);
        
        // Telefono Fijo/Celular
        $fTelefonoFijoCelular = new Zend_Form_Element_Text("telefono");
        $fTelefonoFijoCelular->setRequired();
        $fTelefonoFijoCelular->setAttrib('maxLength', $this->_maxlengthTelefono);
        $fTelefonoFijoCelular->addValidator(
            new Zend_Validate_StringLength(
                array('min' => 8, 'max' => $this->_maxlengthTelefono,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $fTelefonoFijoCelular->addValidator(new Zend_Validate_Digits());
        $fTelefonoFijoCelular->errMsg = self::MSJ_ERROR_TELEFONO;
        $this->addElement($fTelefonoFijoCelular);
        
        //  Anexo
        $fTelefonoAnexo = new Zend_Form_Element_Text("anexo");
        $fTelefonoAnexo->setAttrib('maxLength', $this->_maxlengthAnexo);
        /*$fTelefonoAnexo->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '2', 'max' => $this->_maxlengthAnexo)
            )
        );*/
        $fTelefonoAnexo->errMsg = "Ingrese un N° de Anexo Valido";
        $this->addElement($fTelefonoAnexo);
        
        // Telefono Fijo/Celular Anexo
        $fTelefonoFijoCelular = new Zend_Form_Element_Text("telefono2");
        $fTelefonoFijoCelular->setAttrib('maxLength', $this->_maxlengthTelefono);
        $fTelefonoFijoCelular->addValidator(
            new Zend_Validate_StringLength(
                array('min' => 8, 'max' => $this->_maxlengthTelefono,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $fTelefonoFijoCelular->addValidator(new Zend_Validate_Digits());
       /* $fTelefonoFijoCelular->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '2', 'max' => $this->_maxlengthTelefono)
            )
        );*/
        $fTelefonoFijoCelular->errMsg = self::MSJ_ERROR_TELEFONO;
        $this->addElement($fTelefonoFijoCelular);
        
        // Anexo2
        $fTelefonoAnexo = new Zend_Form_Element_Text("anexo2");
        $fTelefonoAnexo->setAttrib('maxLength', $this->_maxlengthAnexo);
        /*$fTelefonoAnexo->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '2', 'max' => $this->_maxlengthAnexo)
            )
        );*/
        $fTelefonoAnexo->errMsg = "Ingrese un N° de Anexo Valido";
        $this->addElement($fTelefonoAnexo);
        
    }
    
}

