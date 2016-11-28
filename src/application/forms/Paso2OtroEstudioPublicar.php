<?php

class Application_Form_Paso2OtroEstudioPublicar extends App_Form {

    private $_maxlengthAnho = '4';
    private $_listaInstitucion;
    private $_listaTipoCarrera;
    private $_listaCarrera;
    private $_listaPais;
    private $_maxlengthInstitucion = '75';
    private $_online = false;

    public function __construct($hasHiddenId = false,$online=false) {
        
         if (isset($online) && $online == true) {
            $this->_online = true;
        }
        parent::__construct();
        if ($hasHiddenId) {
            $this->addEstudioId();
        }
    }

    public function init() {
        parent::init();
        $this->setMethod('post');

        //Nivel Estudios
        $nivel = new Application_Model_NivelEstudio;
        $listaNiveles = array('9'=>'');
        $e = new Zend_Form_Element_Select('id_nivel_estudio');
        $e->addMultiOptions($listaNiveles);
        $v = new Zend_Validate_InArray(array_keys($listaNiveles));
        $e->addValidator($v);
        $this->addElement($e);

        //Nivel Estudios Tipo
        $e = new Zend_Form_Element_Select('id_nivel_estudio_tipo');
        $e->setRegisterInArrayValidator(false);
        $listaNivelesTipo = $nivel->getSubNiveles(9);
        $e->addMultiOption('0', 'Selecciona un tipo');
        $e->addMultiOptions($listaNivelesTipo);
        $this->addElement($e);

        //Otros Estudios
        $e = new Zend_Form_Element_Text('otra_carrera');
        $e->setAttrib('maxLength', $this->_maxlengthInstitucion);
        $this->addElement($e);
        
        //Bloque los combos si el aviso estan online 
        if (isset($this->_online) && $this->_online == true) {
            $this->disableFileds();
        }
    }

    public function isValid($data) {
            if ($this->_online == true) {
            // @codingStandardsIgnoreStart
        

            $this->id_nivel_estudio->clearValidators();
            $this->id_nivel_estudio->setRequired(false);
            $this->id_nivel_estudio_tipo->clearValidators();
            $this->id_nivel_estudio_tipo->setRequired(false);
            $this->otra_carrera->clearValidators();
            $this->otra_carrera->setRequired(false);
               
            // @codingStandardsIgnoreEnd
        }else{
            if ($data['id_nivel_estudio_tipo'] != '0'||!empty($data['otra_carrera'])) {

                        // @codingStandardsIgnoreStart
                        $this->otra_carrera->setRequired();
                        $this->otra_carrera->errMsg = $this->_mensajeRequired;
                        // @codingStandardsIgnoreEnd
                        $nivel = new Application_Model_NivelEstudio;
                        $listaNivelesTipo = $nivel->getSubNiveles(9);
                        $this->id_nivel_estudio_tipo->setRequired();
                        $this->id_nivel_estudio_tipo->addValidator(
                                new Zend_Validate_InArray(
                                array_keys($listaNivelesTipo)
                                )
                        );
                        $this->id_nivel_estudio_tipo->errMsg = $this->_mensajeRequired;
            }
        }
        return parent::isValid($data);
    }
    public function disableFileds(){
        $this->id_nivel_estudio->setAttrib('disabled', 'disabled');
        $this->id_nivel_estudio_tipo->setAttrib('disabled', 'disabled');
       $this->otra_carrera->setAttrib('disabled', 'disabled');
        
    }
    public function addEstudioId()
    {
        $e = new Zend_Form_Element_Hidden('id_estudio');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);
    }
    
    public function setHiddenId($id)
    {
        $e = $this->getElement('id_estudio');
        $e->setValue($id);
    }

}