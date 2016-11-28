<?php

class Application_Form_PostulanteLogros extends App_Form {
    private $_minlengthLogro = '3';
    private $_maxlengthLogro = '80';
    private $_minlengthInstitucion = '3';
    private $_maxlengthInstitucion = '80';
    private $_maxlengthDescripcion = '200';
    private $_minlengthAno = '1910';

    public function __construct($id=null) {
        parent::__construct();
    }

    public function init() {
        parent::init();
         $this->_minlengthAno=date("Y")-100;
        //logro
        $fPrize = new Zend_Form_Element_Text('txtPrize');
        $fPrize->setAttrib('maxLength', $this->_maxlengthLogro);
        $fPrize->setAttrib('minlength', $this->_minlengthLogro);
        $fPrize->addValidator(
            new Zend_Validate_StringLength(
                array(
                    'min' => $this->_minlengthLogro,
                    'max' => $this->_maxlengthLogro,
                    'encoding' => $this->_config->resources->view->charset
                )
            )
        );
        $fPrize->setRequired();
        $this->addElement($fPrize);
        
        //institución
        $fInstitucion = new Zend_Form_Element_Text('txtInstitution');
        $fInstitucion->setAttrib('maxLength', $this->_maxlengthInstitucion);
        $fInstitucion->setAttrib('minlength', $this->_minlengthInstitucion);
        $fInstitucion->addValidator(
            new Zend_Validate_StringLength(
                array(
                    'min' => $this->_minlengthInstitucion, 
                    'max' => $this->_maxlengthInstitucion,
                    'encoding' => $this->_config->resources->view->charset
                )
            )
        ); 
        $fInstitucion->setRequired();
        $this->addElement($fInstitucion);
        
        //año
        $fDateAchievement = new Zend_Form_Element_Text('txtDateAchievement');
        $fDateAchievement->setAttrib('min', $this->_minlengthAno);
        $fDateAchievement->setAttrib('max', date("Y"));
        $numerico = new Zend_Validate_Digits();
        $fDateAchievement->addValidator(
            new Zend_Validate_Between(
                array(
                    'min' => $this->_minlengthAno, 
                    'max' => date("Y")
                )
            )
        );
        $fDateAchievement->addValidator($numerico);
        $fDateAchievement->setRequired();
        $this->addElement($fDateAchievement);
        
        //mes
        $fDate = new Zend_Form_Element_Select('selDate');        
        $meses = App_Util::getMonths();
        $fDate->addMultiOption('', 'Selecciona un mes');
        $fDate->addMultiOptions($meses);
        $fDate->setRequired();
        $this->addElement($fDate);
        
        //descripción
        $fDescription = new Zend_Form_Element_Textarea('txtDescription');   
        $fDescription->setAttrib('maxlength', $this->_maxlengthDescripcion);
        $fDescription->addValidator(
            new Zend_Validate_StringLength(
                array(
                    'max' => $this->_maxlengthDescripcion
                )
            )
        );
        $this->addElement($fDescription);
        
        $fToken = new Zend_Form_Element_Hash('hidToken');
        $this->addElement($fToken);
        
        $fIdAchievements = new Zend_Form_Element_Hidden('hidAchievements');
        $fIdAchievements->setValue('0');
        $this->addElement($fIdAchievements);
        
        //botón
        $fButton = new Zend_Form_Element_Button('button');
        $fButton->setLabel("Guardar");
        $this->addElement($fButton);
    }

    public function isValid($data) {      
        return parent::isValid($data);
    }
    
    public static function getMensajesErrors($form) { 
        if (count($form->getMessages() ) == 0) {
            return ;
        }
        $errors=array();
        foreach ($form->getMessages() as $f => $error) {   
            foreach ($error as $value=>$key) {           
                return Application_Form_PostulanteLogros::$errors[$value];          
            }          
        } 
        return $errors[$f];
    }

    public static $errors = array(
        'isEmpty' => 'Campo Requerido',
        'stringLengthInvalid' => 'Documento inválido',
        'stringLengthTooLong' => 'Documento inválido',
        'stringLengthTooShort' => 'El documento no tiene 8 caracteres',
        'stringLengthTooLong' => 'El documento no tiene 8 caracteres',
        'callbackInvalid' => 'El número del documento ya se encuentra registrado',
        'callbackValue' => 'El número del documento ya se encuentra registrado',
        'notSame'=>'Por favor vuelva a intentarlo',
        'missingToken'=>'Por favor vuelva a intentarlo',
        'notInArray'=>'No se encontró el registro',
        'notAlpha'=>'Ingrese solamente letras'
    );
}
