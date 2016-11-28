<?php

/**
 * Description of Categoria
 *
 * @author Usuario
 */
class Application_Form_RegistroRapido extends App_Form
{
    private $_idUsuario;
    private $_maxlengthEmail='32';
    private $_maxlengthPwsd='32';
    private $_minlengthPwsd='6';
    private $_maxlengthNombreRa = '75';
    private $_maxlengthNumRuc = '11';
    private $_maxlengthTelefono = 9;
    
    /**
     * @var Application_Model_Empresa
     */
    private $_empresaModelo;
    
    const MSJ_ERROR_TELEFONO = 'Ingrese un numero entre 7-9 digitos';
    
    public function setIdUsuario($iu)
    {
        $this->_idUsuario = $iu;
    }
    
    public function getIdUsuario()
    {
        return $this->_idUsuario;
    }
    
    public function __construct($iu)
    {
        parent::__construct();
        $this->_idUsuario =$iu;
        $this->_empresaModelo = new Application_Model_Empresa;
    }
    
    public function init()
    {
        parent::init();
        $this->setAction('/registro/paso1');

        //Contacto
        $fContacto = new Zend_Form_Element_Text('contacto');
        $fContacto->setRequired();
        $fContacto->setAttrib('maxLength', $this->_maxlengthNombreRa);
        $fContacto->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '2', 'max' => $this->_maxlengthNombreRa,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $this->addElement($fContacto);

        // Telefono Fijo/Celular
        $fTelefonoFijoCelular = new Zend_Form_Element_Text("telefono");
        $fTelefonoFijoCelular->setRequired();
        $fTelefonoFijoCelular->setAttrib('maxLength', $this->_maxlengthTelefono);
        $fTelefonoFijoCelular->addValidator(
            new Zend_Validate_StringLength(
                array('min' => 7, 'max' => $this->_maxlengthTelefono,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $fTelefonoFijoCelular->addValidator(new Zend_Validate_Digits());
        $fTelefonoFijoCelular->errMsg = self::MSJ_ERROR_TELEFONO;
        $this->addElement($fTelefonoFijoCelular);

        // Clave
        $fClave = new Zend_Form_Element_Password('pswd');
        $fClave->setRequired();
        $fClaveVal = new Zend_Validate_NotEmpty();
        $fClave->setAttrib('maxLength', $this->_maxlengthPwsd);
        $fClave->addValidator($fClaveVal);
        $fClaveVal = new Zend_Validate_StringLength(
            array('min' => $this->_minlengthPwsd, 'max' => $this->_maxlengthPwsd,
            'encoding' => $this->_config->resources->view->charset)
        );
        
        $fClave->addValidator($fClaveVal);
        $fClave->errMsg = '¡Usa de 6 a 32 caracteres!';
        $this->addElement($fClave);
        
        // Repetir Clave
        $fRClave = new Zend_Form_Element_Password('pswd2');
        $fRClave->setRequired();
        //
        $fRClave->addValidator(new App_Validate_PasswordConfirmation());
        $fRClave->errMsg = 
            'Las contraseñas introducidas no coinciden. Vuelve a intentarlo.';
        $this->addElement($fRClave);
        
        //Return
        $e = new Zend_Form_Element_Hidden('return');
        $e->setValue(
            Zend_Controller_Front::getInstance()->getRequest()->getRequestUri()
        );
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);

    }
    public function validadorEmail($idUsuario)
    {
        // Email
        $fEmail = new Zend_Form_Element_Text('email');
        $fEmail->setAttrib('maxLength', $this->_maxlengthEmail);
        $fEmail->setRequired();
        $fEmail->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '2', 'max' => $this->_maxlengthEmail,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $fEmailVal = new Zend_Validate_EmailAddress();
       // $fEmail->addFilter(new Zend_Filter_StringToLower());
        $fEmail->addValidator($fEmailVal, true);
        $fEmail->addValidator(new Zend_Validate_NotEmpty(), true);
        $f = "Application_Model_Usuario::validacionEmail";
        $fEmailVal = new Zend_Validate_Callback(
            array('callback'=>$f,'options' => array($idUsuario, 'empresa'))
        );
        $fEmail->addValidator($fEmailVal);
        $fEmail->errMsg = "No parece ser un correo electrónico valido";
        
        $this->addElement($fEmail);
    }

    public function validadorRuc($id)
    {
        //Ruc
        $fNRuc = new Zend_Form_Element_Text('num_ruc');
        $fNRuc->setRequired();
        $fNRuc->setAttrib('maxLength', $this->_maxlengthNumRuc);
        $fNRuc->addValidator(new Zend_Validate_NotEmpty(), true);
       /* $fNRucVal  = new Zend_Validate_StringLength(
            array('min' => $this->_maxlengthNumRuc, 'max' => $this->_maxlengthNumRuc,
            'encoding' => $this->_config->resources->view->charset)
        );
        $fNRuc->addValidator($fNRucVal);
        */
        $fNRuc->addValidator(new App_Validate_Ruc());
        
        $fNRucVal = new Zend_Validate_Callback(array(
            'callback' => array($this->_empresaModelo, 'validacionRuc'),
            'options' => array($id))
        );        
        
        $fNRuc->addValidator($fNRucVal);
        $fNRuc->errMsg = "Debe ingresar de Ruc 11 Digitos";
        $this->addElement($fNRuc);
    }
    
    public function validadorRazonSocial($id)
    {
        //Razon Social
        $fRazonSocial = new Zend_Form_Element_Text('razonsocial');
        $fRazonSocial->errMsg="Debe ingresar una Razon Social";
        $fRazonSocial->setRequired();
        $fRazonSocial->setAttrib('maxLength', $this->_maxlengthNombreRa);
        $fRazonSocial->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthNombreRa,
                'encoding' => $this->_config->resources->view->charset)
            )
        );        
        
        $fRazonSocialVal = new Zend_Validate_Callback(array(
            'callback' => array($this->_empresaModelo, 'validacionCampoRepetido'),
            'options' => array($id, 'razon_social')));
        
        $fRazonSocial->addValidator($fRazonSocialVal);
        $this->addElement($fRazonSocial);
    }   
}

