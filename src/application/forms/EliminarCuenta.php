<?php


class Application_Form_EliminarCuenta extends App_Form
{
    
    public function init()
    {
        parent::init();
                
        $valNotEmpty = new Zend_Validate_NotEmpty();
        
        // Motivo
        $e = new Zend_Form_Element_Radio('chxReassons');
        $e->addMultiOptions(array(
            'No encuentro trabajo en AquiEmpleos'=>'No encuentro trabajo en AquiEmpleos',
            'Me preocupa la privacidad de mis datos'=>'Me preocupa la privacidad de mis datos',
            'No sé cómo utilizar AquiEmpleos' => 'No sé cómo utilizar AquiEmpleos',
            'Me han robado la cuenta' => 'Me han robado la cuenta',
            'Tengo otra cuenta de AquiEmpleos' => 'Tengo otra cuenta de AquiEmpleos',
            'Esto es temporal, volveré. ' => 'Esto es temporal, volveré. ',
            'Recibo demasiados mensajes de correo electrónico, invitaciones e información de AquiEmpleos' => 'Recibo demasiados mensajes de correo electrónico, invitaciones e información de AquiEmpleos',
            'Otros' => 'Otros'
            
        ));
        $e->setRequired();
        $e->errMsg = 'Es necesario que seleccione el motivo por el cual se eliminara su cuenta.';
        $this->addElement($e);
        
        
        $e = new Zend_Form_Element_Textarea('txaReasons');        
        $this->addElement($e);
        
        $e = new Zend_Form_Element_Password('txtPassword');
        $e->setRequired();
        $e->addValidator($valNotEmpty);
        $e->errMsg = 'Es necesario que ingrese  su contraseña actual.';
        $this->addElement($e);
        
        $e = new Zend_Form_Element_Checkbox('confirmation');        
        $e->setRequired();
        $e->setChecked(false);        
        $e->addValidator($valNotEmpty);
        $e->errMsg = 'Es necesario que nos confirme si realmente desea eliminar su cuenta.';
        $this->addElement($e);
        
        $e = new Zend_Form_Element_Hash('hash_remove_account');                
        $e->errMsg = 'Por favor vuelva ha intentarlo.';
        $this->addElement($e);
                
            
    }
    
    
    public function isValid($data) 
    {   
        $isValid = parent::isValid($data);
        if ($data['confirmation'] == 0)
        {
            $this->getElement('confirmation')->setErrors(array(
                'required' => 'Es necesario que nos confirme si realmente desea eliminar su cuenta.'                
            ));
            $isValid = false;
        }
        
        
        
        return $isValid;
    }
    

}

