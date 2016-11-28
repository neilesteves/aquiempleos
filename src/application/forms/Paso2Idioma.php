<?php

class Application_Form_Paso2Idioma extends App_Form {

    protected $_listaIdionas;
    protected $_listaNiveles;
    private $_online = false;    
    public function __construct($hasHiddenId = false,$online=false) {
       if (isset($online) && $online == true) {
            $this->_online = true;
        }
        parent::__construct();
        if ($hasHiddenId) {
            $this->addIdiomaId();
        }
        $this->addCabeceraOriginal();
    }

    public function init() {
        parent::init();
        $this->setMethod('post');

        //Idiomas
        $idioma = new Application_Model_Idioma();
        $this->_listaIdionas = $idioma->getIdiomas();
        $e = new Zend_Form_Element_Select('id_idioma');
        $e->addMultiOption('0', 'Selecciona un idioma');
        $e->addMultiOptions($this->_listaIdionas);
        $this->addElement($e);

        //Nivel Idioma
        $this->_listaNiveles = Application_Model_DominioIdioma::$niveles;
        $e = new Zend_Form_Element_Select('nivel_idioma');
        $e->addMultiOption('0', 'Selecciona un nivel');
        $e->addMultiOptions($this->_listaNiveles);
        $this->addElement($e);
        
        $e = new Zend_Form_Element_Hidden('is_disabled');
        $this->addElement($e);
            //Bloque los combos si el aviso estan online 
        if (isset($this->_online) && $this->_online == true) {
            $this->disableFileds();
        }
        
    }

    public function isValid($data) {
           if ($this->_online == true) {
            // @codingStandardsIgnoreStart
        

            $this->id_idioma->clearValidators();
            $this->id_idioma->setRequired(false);
            $this->nivel_idioma->clearValidators();
            $this->nivel_idioma->setRequired(false);
            
            // @codingStandardsIgnoreEnd
        }else{
            if ($data['id_idioma'] != '0' || $data['nivel_idioma'] != '0') {
                $this->id_idioma->setRequired();
                $this->id_idioma->addValidator(new Zend_Validate_InArray(
                        array_keys($this->_listaIdionas)
                ));
                $this->id_idioma->errMsg = $this->_mensajeRequired;


                $this->nivel_idioma->setRequired();
                $this->nivel_idioma->addValidator(new Zend_Validate_InArray(
                        array_keys($this->_listaNiveles)
                ));
                $this->nivel_idioma->errMsg = $this->_mensajeRequired;
            }
       }
        return parent::isValid($data);
    }

    public function addIdiomaId() {
        $e = new Zend_Form_Element_Hidden('id_dominioIdioma');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $e->setAttrib('class', 'hidden_id');
        $this->addElement($e);
    }

    public function addCabeceraOriginal() {
        $e = new Zend_Form_Element_Hidden('cabecera_idioma');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);
        $e = new Zend_Form_Element_Hidden('cabecera_nivel');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);
    }

    public function setHiddenId($id) {
        $e = $this->getElement('id_dominioIdioma');
        $e->setAttrib('class', 'hidden_id');
        $e->setValue($id);
    }

    public function setCabeceras($idioma, $nivel) {
        $e = $this->getElement('cabecera_idioma');
        $e->setValue($idioma);
        $e = $this->getElement('cabecera_nivel');
        $e->setValue($nivel);
    }

    public function addValidatorsIdioma() {

        // @codingStandardsIgnoreStart
        $this->id_idioma->addValidator(
                new Zend_Validate_InArray(array_keys($this->_listaIdionas))
        );

        $this->nivel_idioma->addValidator(
                new Zend_Validate_InArray(
                array_keys(Application_Model_DominioIdioma::$niveles)
                )
        );
        // @codingStandardsIgnoreEnd
    }
        public function disableFileds(){
        $this->id_idioma->setAttrib('disabled', 'disabled');
        $this->nivel_idioma->setAttrib('disabled', 'disabled');
     
        
    }

}