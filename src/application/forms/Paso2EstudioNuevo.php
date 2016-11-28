<?php

class Application_Form_Paso2EstudioNuevo extends App_Form {

    private $_listaTipoCarrera;
    private $_listaCarrera;
    private $_listaPais;
    private $_maxlengthInstitucion = '75';
    private $_maxlengthColegiatura = '10';
    private $_nivelEstudio;
    private $_carrera;
    
    private $_padre = 0;

    public function __construct($hasHiddenId = false) {
        parent::__construct();
        if ($hasHiddenId) {
            $this->addEstudioId();
        }
    }

    public function init() {
        parent::init();
        $this->setMethod('post');

        //Nivel Estudios
        $this->_nivelEstudio = new Application_Model_NivelEstudio;
        $listaNiveles = $this->_nivelEstudio->getNiveles();
        $e = new Zend_Form_Element_Select('id_nivel_estudio');
        $e->setRequired();
        $e->addMultiOption('0', 'Selecciona un nivel');
        $e->addMultiOptions($listaNiveles);
        $v = new Zend_Validate_InArray(array_keys($listaNiveles));
        $e->addValidator($v);
        $e->errMsg = $this->_mensajeRequired;
        $this->addElement($e);

        //Nivel Estudios Tipo
        $e = new Zend_Form_Element_Select('id_nivel_estudio_tipo');
        $e->addMultiOption('0', 'Selecciona un tipo');
        $this->addElement($e);

        $e = new Zend_Form_Element_Text('institucion');
        $e->setAttrib('maxLength', $this->_maxlengthInstitucion);
        $e->setAttrib('param', '4');
        $e->setAttrib('model', 'institucion');
        $this->addElement($e);

        // Texto para la carrera
        $e = new Zend_Form_Element_Text('id_carrera');
        $e->setAttrib('maxLength', $this->_maxlengthInstitucion);
        $e->setAttrib('model', 'carrera');
        $e->setAttrib('param', '4');
        $this->addElement($e);

        //Fecha
        $listaMeses = Application_Model_Mes::$lista;

        //Mes Inicio
        $e = new Zend_Form_Element_Select('inicio_mes');
        $e->setValue(1);
        $e->setRequired();
        $e->addMultiOptions($listaMeses);
        $this->addElement($e);

        //Anho Inicio
        $e = new Zend_Form_Element_Text('inicio_ano');
        $e->setRequired();
        $e->addValidator(new App_Validate_MonthAndYearBeforeThan());
        $e->errMsg = 'Fecha debe ser menor o igual a la actual.';
        $e->setAttrib('maxLength', $this->_maxlengthYear);
        $e->setAttrib('placeholder', date('Y'));
        $this->addElement($e);

        //Mes Fin
        $e = new Zend_Form_Element_Select('fin_mes');
        $e->setValue(1);
        $e->addMultiOptions($listaMeses);
        $this->addElement($e);

        //Anho Fin
        $e = new Zend_Form_Element_Text('fin_ano');
        $e->setAttrib('maxLength', $this->_maxlengthYear);
        $e->setAttrib('placeholder', date('Y'));
        $this->addElement($e);

        //Actualmente
        $e = new Zend_Form_Element_Checkbox('en_curso');
        $this->addElement($e);

    }

    public function isValid($data) {
        if ($data['id_nivel_estudio'] != 1 ) {
          
            if (isset($data['en_curso'])) {
                // @codingStandardsIgnoreStart
                $this->inicio_mes->setRequired();

                $this->inicio_ano->setRequired();
                $this->inicio_ano->addValidator(
                        new App_Validate_MonthAndYearBeforeThan()
                );

                // @codingStandardsIgnoreEnd
                if ($data['en_curso'] != 1) {
                     
                    // @codingStandardsIgnoreStart
                    $this->fin_ano->addValidator(
                            new Zend_Validate_Between(
                            array('max' => date('Y'),
                        'min' => Application_Model_Anio::getMinAnio())
                            )
                    );
                    $this->fin_ano->setRequired();
                    // @codingStandardsIgnoreEnd
                }
            }
            if (isset($data['id_nivel_estudio'])) {

                if (
                        $data['id_nivel_estudio'] != 1 &&
                        $data['id_nivel_estudio'] != 2 &&
                        $data['id_nivel_estudio'] != 3
                ) {
                    // @codingStandardsIgnoreStart
                    $this->inicio_mes->setRequired();

                    $this->inicio_ano->setRequired();
                    $this->inicio_ano->addValidator(
                            new App_Validate_MonthAndYearBeforeThan()
                    );

                    $v = $this->institucion->addValidator(
                            new Zend_Validate_StringLength(
                            array(
                        'min' => '2', 'max' => $this->_maxlengthInstitucion,
                        'encoding' => $this->_config->resources->view->charset
                            )
                            )
                    );
                    $this->institucion->setRequired();
                    $this->institucion->errMsg = $this->_mensajeRequired;
                    
                    $this->id_carrera->setRequired();
                    $this->id_carrera->clearValidators();
                    $this->id_carrera->errMsg = "Seleccione una carrera";  
                    $nivel = new Application_Model_NivelEstudio;
                    $listaNivelesTipo = $nivel->getSubNiveles($data['id_nivel_estudio']);
                    $this->id_nivel_estudio_tipo->setRequired();
                    $this->id_nivel_estudio_tipo->addValidator(
                            new Zend_Validate_InArray(
                            array_keys($listaNivelesTipo)
                            )
                    );
                    $this->id_nivel_estudio_tipo->errMsg = $this->_mensajeRequired;
                    // @codingStandardsIgnoreEnd
                } 
                if ($data['id_nivel_estudio'] == 9) {

                    $this->otro_estudio->setRequired();
                    $this->otro_estudio->errMsg = "Ingresa los datos de tus otros estudios";
                    $this->id_carrera->setRequired(false);
                    $this->id_carrera->clearValidators();
                    $this->otro_carrera->setRequired(false);
                    $this->otro_carrera->clearValidators();

                }
                if (in_array($data['id_nivel_estudio'],array(2,3))) {
                    $this->institucion->setRequired();
                    $this->institucion->errMsg = "Ingrese el nombre de la institución";
                }
            }
        } else {
            $this->inicio_mes->setRequired(false);
            $this->inicio_ano->setRequired(false);
            $this->id_carrera->setRequired(false);
            $this->id_carrera->clearValidators();
        }
       
        return parent::isValid($data);
    }

    public function addEstudioId() {
        $e = new Zend_Form_Element_Hidden('id_estudio');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $e->setAttrib('class', 'hidden_id');
        $this->addElement($e);
    }

}
