<?php


class Application_Form_Privacidad extends App_Form
{
    
    public function init()
    {
        parent::init();
        // Privacidad
        $e = new Zend_Form_Element_Radio('fPrivacCP');
        $e->addMultiOptions(array(
            '0'=>'',
            '1'=>''
        ));
                
        
        $v = new Zend_Validate_NotEmpty();
        $e ->addValidator($v);
        $e->errMsg = $this->_mensajeRequired;
        $e->setSeparator(
                
            '   <div class="option_info">
                    <p>Público</p>
                    <p>Los datos básicos de tu perfil estarán siempre visibles desde cualquier acceso desde nuestro portal. </p>
                    <a href="javascript:;">Otras opciones de privacidad </a>
                 </div>
            </label>
        </div>
        
        <div class="form_control">
            <label class="ioption" for="fPrivac0-1"> '
                              
        );
        $this->addElement($e);
    }

}

