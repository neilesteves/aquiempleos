<?php

/**
 * Descripcion del formulario recuperar clave
 *
 * @author Jesus Fabian
 */
class Application_Form_NewRecuperarClave extends App_Form
{
    public function init()
    {
        parent::init();
        
        // Email
        $e = new Zend_Form_Element_Text('txtEmailForgot');
        $e->setRequired();
        $e->addValidator(new Zend_Validate_EmailAddress(), true);
        $e->errMsg = "No parece ser un correo electrónico valido";
        $this->addElement($e);

        // CSFR protection
        //$e = new Zend_Form_Element_Hash('recuperar_token');
//        $e = new Zend_Form_Element_Hash('hidRecoverPassword');
//       $this->addElement($e);

        //Submit
      
    }
    
    public function setType($type)
    {
        $e = new Zend_Form_Element_Hidden('rol');
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