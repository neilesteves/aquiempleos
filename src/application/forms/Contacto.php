<?php

class Application_Form_Contacto extends App_Form
{
    //Max Length
    private $_maxlengthNames = '50';
    private $_maxlengthApellidos = '70';
    private $_maxlengthMensaje = '450';
    private $_maxlengthNroDoc = '8';
    private $_maxlengthTelefonos = '20';
    private $_maxlengthEmail = '60';
    
    public function init()
    {
        parent::init();
        
        $fNameCT = new Zend_Form_Element_Text('fNameCT');
        $fNameCT->setRequired();
        $fNameCT->setAttrib('maxLength', $this->_maxlengthNames);
        $fNameCT->addValidator(
            new Zend_Validate_StringLength(array('min'=>1,'max'=> $this->_maxlengthNames))
        );
        $fNameCT->errMsg = 'Se requieren tus Nombres';
        $this->addElement($fNameCT);
        
        $fApellidosCT = new Zend_Form_Element_Text('fApellidosCT');
        $fApellidosCT->setRequired();
        $fApellidosCT->setAttrib('maxLength', $this->_maxlengthApellidos);
        $fApellidosCT->addValidator(
            new Zend_Validate_StringLength(array('min'=>1,'max'=> $this->_maxlengthApellidos))
        );
        $fApellidosCT->errMsg = 'Se requieren tus Apellidos';
        $this->addElement($fApellidosCT);
        
        $fTlfCT = new Zend_Form_Element_Text('fTlfCT');
        $fTlfCT->setRequired();
        $fTlfCT->setAttrib('maxLength', $this->_maxlengthTelefonos);
        $fTlfCT->addValidator(
            new Zend_Validate_StringLength(
                array('min'=>1,'max'=> $this->_maxlengthTelefonos)
            )
        );
        $fTlfCT->errMsg = 'Se requiere tu Nro Telefonico / Celular';
        $this->addElement($fTlfCT);
        
        $fEmailCT = new Zend_Form_Element_Text('fMailCT');
        $fEmailVal = new Zend_Validate_EmailAddress(
            array("allow" => Zend_Validate_Hostname::ALLOW_ALL)
        );
        $fEmailCT->setRequired();
        $fEmailCT->addFilter(new Zend_Filter_StringToLower());
        $fEmailCT->addValidator($fEmailVal, true);
        $fEmailCT->setAttrib('maxLength', $this->_maxlengthEmail);
        $fEmailCT->errMsg = 'No parece ser un correo electrónico valido';
        $this->addElement($fEmailCT);
        
        $cboDocumento = new Zend_Form_Element_Select('tipo_documento');
        $cboDocumento->addMultiOption('DNI#'.$this->_maxlengthNroDoc, 'DNI');
        $cboDocumento->addMultiOption(
            'Carnet Extranjería#'.$this->_maxlengthNroDoc, 'Carné Extranjería'
        );
        $cboDocumento->setRequired();
        $this->addElement($cboDocumento);
        
        
        $fSubjectCT = new Zend_Form_Element_Select('fSubjectCT');
        $fSubjectCT->addMultiOption('Consulta', 'Consulta');
        $fSubjectCT->addMultiOption('Sugerencia', 'Sugerencia');
        $fSubjectCT->setRequired();
        $this->addElement($fSubjectCT);
        
        
        $fDocCT = new Zend_Form_Element_Text('fDocCT');
        $fDocCT->setRequired();
        $fDocCT->addValidator(new Zend_Validate_NotEmpty());
        $fDocCT->addValidator(new Zend_Validate_Int());
        $fDocCT->setAttrib('maxLength', $this->_maxlengthNroDoc);
        $fDocCT->errMsg = 'Ingrese nro de documento valido';
        $this->addElement($fDocCT);
        
        $fMsjCT = new Zend_Form_Element_Textarea('fMsjCT');
        $fMsjCT->setAttrib('rows', 6);
        $fMsjCT->setAttrib('maxLength', $this->_maxlengthMensaje);
        $fMsjCT->addValidator(
            new Zend_Validate_StringLength(array('min'=>1,'max'=>$this->_maxlengthMensaje))
        );
        $fMsjCT->setRequired();
        $fMsjCT->addValidator(new Zend_Validate_NotEmpty());
        $fMsjCT->errMsg = 'Se requiere un mensaje';
        $this->addElement($fMsjCT);
        
        $publickey = $this->_config->recaptcha->publickey;
        $privatekey = $this->_config->recaptcha->privatekey;
        
        //Translate in your language
        $recaptcha = new Zend_Service_ReCaptcha($publickey, $privatekey);
        $recaptcha->setOption('theme', 'clean');
        $recaptcha->setOption('lang', 'es');
        
        $captcha = new Zend_Form_Element_Captcha(
            'challenge', 
            array(
                'label' => 
                    'Por favor, escribe el código de seguridad tal como se muestra en la imagen',
                'captcha' => 'ReCaptcha',
                'captchaOptions' => array(
                    'captcha' => 'ReCaptcha',
                    'service' => $recaptcha
                )
            )
        );
        $captcha->setErrorMessages(array('incorrect-captcha-sol'=>'Codigo Incorrecto'));
        $captcha->addDecorator('Label', array('class'=>'fnormal black Trebuchet'));
        $this->addElement($captcha);
        
        $this->setAction('');
        $this->setMethod('post');
    }
}
