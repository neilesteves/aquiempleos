<?php

class Application_Form_Paso2IdiomaNew extends App_Form {

    protected $_listaIdionas;
    protected $_listaNiveles;
    private $_online = false;  
    public static $_listaIdiona=array('');
    public function __construct($id=null) {
   
        parent::__construct();
//        if ($hasHiddenId) {
//            $this->addIdiomaId();
//        }
//        $this->addCabeceraOriginal();
    }

    public function init() {
        parent::init();
        $this->setMethod('post');
          //Idiomas
        $idioma = new Application_Model_Idioma();
        $e = new Zend_Form_Element_Select('selLanguage');
        $this->_listaIdionas = $idioma->getIdiomas();
        $e->addMultiOption('', 'Selecciona un idioma');
        $e->addMultiOptions($this->_listaIdionas);
        $e->setRequired();
        $this->addElement($e);
        
        //nivel escrito
        $idioma = new Application_Model_Idioma();
        $this->_listaNiveles = Application_Model_DominioIdioma::$niveles;
        $e = new Zend_Form_Element_Select('selLevelWritten');
        $e->addMultiOption('', 'Selecciona un nivel de escritura');
        $e->addMultiOptions($this->_listaNiveles);
        $this->addElement($e);

        //Nivel oral
        $this->_listaNiveles = Application_Model_DominioIdioma::$niveles;
        $e = new Zend_Form_Element_Select('selLevelOral');
        $e->addMultiOption('', 'Selecciona un nivel de hablado');
        $e->addMultiOptions($this->_listaNiveles);
        $this->addElement($e);
        
        $e = new Zend_Form_Element_Hash('hidToken');
        $this->addElement($e);
      
    }
    
    public function misIdiomas($id=''){
     
            //Bloque los combos si el aviso estan online 
    }

    public function isValid($data) {      
           // if ($data['id_idioma'] != '0' || $data['nivel_idioma'] != '0') {
        
        $this->selLanguage->setRequired();
        $this->selLanguage->addValidator(new Zend_Validate_InArray(
                array_keys($this->_listaIdionas)
        ));
        
        $this->selLevelWritten->setRequired();
        $this->selLevelWritten->addValidator(new Zend_Validate_InArray(
                array_keys($this->_listaNiveles)
        ));
        $this->selLevelWritten->errMsg = $this->_mensajeRequired;
        
        $this->selLevelOral->setRequired();
        $this->selLevelOral->addValidator(new Zend_Validate_InArray(
                array_keys($this->_listaNiveles)
        ));
        $this->selLevelOral->errMsg = $this->_mensajeRequired;
        
        if(!isset($data['selLanguage'])){
            return false;
        }
        if(!isset($data['hidLanguage'])){
            return false;
        }
       
            //}
      
        return parent::isValid($data);
    }
    public static function getMensajesErrors($fom) { 
      if(count($fom->getMessages() ) == 0){
          return ;
      }
    
      $errors=array();
      $mesaje='';
      foreach ($fom->getMessages() as $form => $error) {   
        foreach ($error as $value=>$key) { 
            
            $errors= Application_Form_Paso2IdiomaNew::$errors[$value];            
        }          
      }
      
      return $errors;
      
    }

    public static $errors = array(
     'isEmpty' => 'Campo Requerido',
     'callbackValue' => 'El Número del documento ya se encuentra registrado',
     'notSame'=>'Por favor vuelva a intentarlo',
     'missingToken'=>'Por favor vuelva a intentarlo',
     'notInArray'=>'No se encontro el registro'
        );



}