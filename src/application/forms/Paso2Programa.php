<?php

/**
 * Description of Form Paso2 Section Estudio
 *
 * @author Jesus
 */
class Application_Form_Paso2Programa extends App_Form {

    protected $_listaProgramas;
    protected $_listaNiveles;
   private $_online = false;    
    public function __construct($hasHiddenId = false,$online=false) {
        if (isset($online) && $online == true) {
            $this->_online = true;
        }
        $programas = new Application_Model_ProgramaComputo();
        $this->_listaProgramas = $programas->getProgramasComputo();
        $this->_listaNiveles = Application_Model_DominioProgramaComputo::$niveles;
        parent::__construct();
        if ($hasHiddenId) {
            $this->addProgramaId();
            $this->addCabeceraOriginal();
        }
    }

    public function init() {
        parent::init();
        $this->setMethod('post');

        //Programa de computacion
        $e = new Zend_Form_Element_Select('id_programa_computo');
        $e->addMultiOption('0', 'Selecciona un programa');
        $e->addMultiOptions($this->_listaProgramas);
        $this->addElement($e);

        //Nivel Programa Computacion
        $e = new Zend_Form_Element_Select('nivel');
        $e->addMultiOption('0', 'Selecciona un nivel');
        $e->addMultiOptions($this->_listaNiveles);
        $this->addElement($e);

        //Disabled true/false
        $e = new Zend_Form_Element_Hidden('nombre');
        $this->addElement($e);

        //Disabled true/false
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
        

            $this->id_programa_computo->clearValidators();
            $this->id_programa_computo->setRequired(false);
            $this->nivel->clearValidators();
            $this->nivel->setRequired(false);
            
            // @codingStandardsIgnoreEnd
        }else{
                if ($data['id_programa_computo'] != '0' || $data['nivel'] != '0') {
                    $this->id_programa_computo->setRequired();
                    $this->id_programa_computo->addValidator(new Zend_Validate_InArray(
                            array_keys($this->_listaProgramas)
                    ));
                    $this->id_programa_computo->errMsg = $this->_mensajeRequired;

                    $this->nivel->setRequired();
                    $this->nivel->addValidator(new Zend_Validate_InArray(
                            array_keys($this->_listaNiveles)
                    ));
                    $this->nivel->errMsg = $this->_mensajeRequired;
                }
        }
        return parent::isValid($data);
    }

    public function addProgramaId() {
        $e = new Zend_Form_Element_Hidden('id_dominioComputo');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $e->setAttrib('class', 'hidden_id');
        $this->addElement($e);
    }

    public function addCabeceraOriginal() {
        $e = new Zend_Form_Element_Hidden('cabecera_programa');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);
        $e = new Zend_Form_Element_Hidden('cabecera_nivel');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);
    }

    public function setHiddenId($id) {
        $e = $this->getElement('id_dominioComputo');
        $e->setAttrib('class', 'hidden_id');
        $e->setValue($id);
    }

    public function setCabeceras($idioma, $nivel) {
        $e = $this->getElement('cabecera_programa');
        $e->setValue($idioma);
        $e = $this->getElement('cabecera_nivel');
        $e->setValue($nivel);
    }

    public function addValidatorsPrograma() {

        // @codingStandardsIgnoreStart
        $this->getElement('id_programa_computo')->removeValidator('InArray')->addValidator(
                new Zend_Validate_InArray(array_keys($this->_listaProgramas))
        );

        $this->getElement('nivel')->addValidator(
                new Zend_Validate_InArray(
                array_keys(Application_Model_DominioProgramaComputo::$niveles)
                )
        );
        // @codingStandardsIgnoreEnd
    }
     public function disableFileds(){
        $this->id_programa_computo->setAttrib('disabled', 'disabled');
        $this->nivel->setAttrib('disabled', 'disabled');
     
        
    }
}