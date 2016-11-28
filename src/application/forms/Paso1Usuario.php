<?php

/**
 * Description of Categoria
 *
 * @author Usuario
 */
class Application_Form_Paso1Usuario extends App_Form
{
    private $_idUsuario;
    private $_maxlengthEmail='75';
    private $_maxlengthPwsd='32';
    private $_minlengthPwsd='6';

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
    }

    public function init()
    {
        parent::init();
        $this->setAction('/registro/paso1');

        // Clave
        $fClave = new Zend_Form_Element_Password('pswd');
        $fClave->setRequired(true);
        //
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
        $fRClave->setRequired(true);
        //
        $fRClave->addValidator(new App_Validate_PasswordConfirmation());
        $fRClave->errMsg =
            'Las contraseñas introducidas no coinciden. Vuelve a intentarlo.';
        $this->addElement($fRClave);

        $cHash = new Zend_Form_Element_Hash('auth_token',array('salt' => 'unique'));
        $this->addElement($cHash);

    }
    public function validadorEmail($idUsuario, $rol = 'postulante')
    {
        // Email
        $fEmail = new Zend_Form_Element_Text('email');
        $fEmail->setAttrib('maxLength', $this->_maxlengthEmail);
        $fEmail->setRequired();
        $fEmailVal = new Zend_Validate_EmailAddress(
            array("allow"=>Zend_Validate_Hostname::ALLOW_ALL),
            true
        );
        $fEmail->addFilter(new Zend_Filter_StringToLower());
        $fEmail->addValidator($fEmailVal, true);
        $fEmail->addValidator(new Zend_Validate_NotEmpty(), true);
        $f = "Application_Model_Usuario::validacionEmail";
        $fEmailVal = new Zend_Validate_Callback(
            array('callback'=>$f,'options' => array($idUsuario, $rol))
        );
        $fEmailVal->setMessage('El email ya se encuentra registrado', 'callbackValue');
        $fEmail->addValidator($fEmailVal);
        $this->addElement($fEmail);
    }
    public static $errorsEmail = array(
        'isEmpty' => 'Campo Requerido',
        'emailAddressInvalidFormat' => 'No parece ser un correo electrónico valido',
        'callbackValue' => 'El email ya se encuentra registrado'
    );
}

