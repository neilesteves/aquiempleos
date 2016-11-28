<?php

/**
 * Description of Categoria
 *
 * @author Dennis Pozo
 */
class Application_Form_CambioClave extends App_Form
{

    private $_idUsuario;
    
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

        // Nueva Clave
        $fNewClave = new Zend_Form_Element_Password('pswd');
        $fNewClave->setRequired();
        $fValidClaveMaxMin = 
            new Zend_Validate_StringLength(
                array('min' => 6, 'max' => 32,
                'encoding' => $this->_config->resources->view->charset)
            );
        $fNewClave->addValidator($fValidClaveMaxMin);
        $fNewClave->errMsg = '¡Usa de 6 a 32 caracteres!';
        $this->addElement($fNewClave);

        // Repetir Clave
        $fConfirmClave = new Zend_Form_Element_Password('pswd2');
        $fConfirmClave->setRequired();
        $fValidClaveMaxMin = new Zend_Validate_StringLength(
            array('min' => 6, 'max' => 32,
            'encoding' => $this->_config->resources->view->charset)
        );
        $fConfirmClave->addValidator(
            new App_Validate_PasswordConfirmation(
                array('match-field' => 'pswd')
            )
        );
        $fConfirmClave->errMsg =
            'Las contraseñas introducidas no coinciden. Vuelve a intentarlo.';
        $this->addElement($fConfirmClave);
        
        
        $fToken = new Zend_Form_Element_Hidden('tok');
        $fToken->setRequired();
        $tok = crypt(date('dmYH'), '$2a$07$'.md5(uniqid(rand(), true)).'$');
        $fToken->setValue($tok);                
        $this->addElement($fToken);
        
        
    }
    
    public function validarPswd($emailUsuario, $idUsuario)
    {
        // Clave Anterior
        //$idUsuario = App_Auth_Adapter_AptitusDbTable::checkPassword($idUsuario);
        $fClaveAnterior = new Zend_Form_Element_Password('oldpswd');
        $fClaveAnterior->setRequired();
        $fClaveAnterior->addValidator(new Zend_Validate_NotEmpty(), true);
        $f = "Application_Model_Usuario::validacionPswd";
        $options = array(
            $emailUsuario,
            $idUsuario
        );
        $fClaveAnteriorVal = new Zend_Validate_Callback(
            array('callback'=>$f,'options' => $options)
        );
        $fClaveAnterior->addValidator($fClaveAnteriorVal);
        $fClaveAnterior->errMsg = 
        'La contraseña proporcionada no coincide con la actual.';
        $this->addElement($fClaveAnterior);
    }

}

