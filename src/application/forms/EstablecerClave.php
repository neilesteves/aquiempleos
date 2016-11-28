<?php

/**
 * Description Reiniciar Clave
 *
 * @author Jesus Fabian
 */
class Application_Form_EstablecerClave extends App_Form
{
    public function init()
    {
        parent::init();
        
        // Nueva Clave
        $fNewClave = new Zend_Form_Element_Password('password');
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
        $fConfirmClave = new Zend_Form_Element_Password('password2');
        $fConfirmClave->setRequired();
        $fValidClaveMaxMin = new Zend_Validate_StringLength(
            array('min' => 6, 'max' => 32,
            'encoding' => $this->_config->resources->view->charset)
        );
        $fConfirmClave->addValidator(
            new App_Validate_PasswordConfirmation(
                array('match-field' => 'password')
            )
        );
        $fConfirmClave->errMsg =
            'Las contraseñas introducidas no coinciden. Vuelve a intentarlo.';
        $this->addElement($fConfirmClave);

        // Token
        $e = new Zend_Form_Element_Hidden('token');
        $e->setRequired();
        $this->addElement($e);

        //Submit
        $e = new Zend_Form_Element_Submit('Submit');
        $this->addElement($e);
    }

    public function isValid($data)
    {
        if (!Application_Model_Usuario::isValidToken($data['token'])) {
            return false;
        }
        return parent::isValid($data);
    }
}