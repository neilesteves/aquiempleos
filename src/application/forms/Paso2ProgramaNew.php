<?php

/**
 * Description of Form Paso2 Section Estudio
 *
 * @author Jesus
 */
class Application_Form_Paso2ProgramaNew extends App_Form 
{

    protected $_listaProgramas;
    protected $_listaNiveles;
    private $_online = false;  
    
    public function __construct() {
      
        $programas = new Application_Model_ProgramaComputo();
        $this->_listaProgramas = $programas->getProgramasComputo();
        $this->_listaNiveles = Application_Model_DominioProgramaComputo::$niveles;
        parent::__construct();
     
    }

    public function init() {
        parent::init();
        $this->setMethod('post');

        //Programa de computacion
        $e = new Zend_Form_Element_Select('selProgram');
        $e->addMultiOption('', 'Selecciona un programa');
        $e->addMultiOptions($this->_listaProgramas);
        //$e = new Zend_Form_Element_Hidden('selProgram');
        $this->addElement($e);


        //Nivel Programa Computacion
        $e = new Zend_Form_Element_Select('selLevel');
        $e->addMultiOption('', 'Selecciona un nivel');
        $e->addMultiOptions($this->_listaNiveles);
        $this->addElement($e);

        //Disabled true/false
//        $e = new Zend_Form_Element_Hidden('nombre');
//        $this->addElement($e);

        //Disabled true/false
        $e = new Zend_Form_Element_Hash('hidToken');
        $e->setRequired();
        $this->addElement($e);
        
      
    }

    public function isValid($data) 
    {
    
        $this->selProgram->setRequired();
//        $this->selProgram->addValidator(new Zend_Validate_InArray(
//                array_keys($this->_listaProgramas)
//        ));
        $this->selProgram->errMsg = $this->_mensajeRequired;

        $this->selLevel->setRequired();
        $this->selLevel->addValidator(new Zend_Validate_InArray(
                array_keys($this->_listaNiveles)
        ));
        $this->selLevel->errMsg = $this->_mensajeRequired;
        return parent::isValid($data);
    }


    public static function getMensajesErrors($fom) 
    {
        
        if (count($fom->getMessages()) == 0) {
            return ;
        }
        $errors = array();
        foreach ($fom->getMessages() as $form => $error) {   
            foreach ($error as $value => $key) {
                $errors = Application_Form_RegistroComplePostulante::$errors[$value];
                         
            }          
        }
        foreach ($errors as $messages) {                   
            return $messages;
        }
        
    }
    
    
    
    public static $errors = array(
     'isEmpty' => 'Campo Requerido',
     'stringLengthInvalid' => 'Documento inválido',
     'stringLengthTooLong' => 'Documento inválido',
     'stringLengthTooShort' => 'El documento no tiene 8 caracteres',
     'stringLengthTooLong' => 'El documento no tiene 8 caracteres',
     'callbackInvalid' => 'El Número del documento ya se encuentra registrado',
     'callbackValue' => 'El Número del documento ya se encuentra registrado',
     'notSame'=>'Por favor vuelva a intentarlo',
     'missingToken'=>'Por favor vuelva a intentarlo',
     'notInArray'=>'No se encontro el registro'
        );
}