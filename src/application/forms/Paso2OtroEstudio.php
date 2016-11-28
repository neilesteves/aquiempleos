<?php

class Application_Form_Paso2OtroEstudio extends App_Form {

    private $_maxlengthAnho = '4';
    private $_listaInstitucion;
    private $_listaTipoCarrera;
    private $_listaCarrera;
    private $_listaPais;
    private $_maxlengthInstitucion = '75';

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
        //$v = new Zend_Validate_InArray(array_keys($listaNivelesTipo));
        //$e->addValidator($v);
        //$e->errMsg = "Debe ingresar un tipo de nivel de estudios";
        $this->addElement($e);

        //Otros Estudios
        $e = new Zend_Form_Element_Text('otro_estudio');
        $e->setAttrib('maxLength', $this->_maxlengthInstitucion);
        $this->addElement($e);

        //Institucion Estudios
//        $institucion = new Application_Model_Institucion();
//        $this->_listaInstitucion = $institucion->getInstituciones();
//        $e = new Zend_Form_Element_Select('id_institucion');
//        $e->addMultiOption('-1', 'Selecciona una institución');
//        $e->addMultiOptions($this->_listaInstitucion);
//        $e->addMultiOption('0', 'Otro');
//        $this->addElement($e);
        //Nombre Institucion Text
        //$institucion = new Application_Model_Institucion();
        //$this->_listaInstitucion = $institucion->getInstituciones();<+

        $e = new Zend_Form_Element_Text('institucion');
        $e->setAttrib('maxLength', $this->_maxlengthInstitucion);
        $e->setAttrib('param', '4');
        $e->setAttrib('model', 'institucion');
        $this->addElement($e);

        //Hidden Id Institucion
        $e = new Zend_Form_Element_Hidden('id_institucion');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);

        //Fecha
        $listaMeses = Application_Model_Mes::$lista;
        $listaAnios = Application_Model_Anio::getAnios();

        //Mes Inicio
        $e = new Zend_Form_Element_Select('inicio_mes');
        $e->setValue(1);
//        $e->setRequired();
        $e->addMultiOptions($listaMeses);
        $this->addElement($e);

        //Anho Inicio
        $e = new Zend_Form_Element_Select('inicio_ano');
        $e->setValue(date('Y') - 1);
//        $e->setRequired();
//        $e->addValidator(new App_Validate_MonthAndYearBeforeThan());
//        $e->errMsg = 'Fecha debe ser menor o igual a la actual.';
        $e->addMultiOptions($listaAnios);
        $this->addElement($e);

        //Mes Fin
        $e = new Zend_Form_Element_Select('fin_mes');
        $e->setValue(1);
        $e->addMultiOptions($listaMeses);
        $this->addElement($e);

        //Anho Fin
        $e = new Zend_Form_Element_Select('fin_ano');
        $e->setValue(date('Y'));
        $e->addMultiOptions($listaAnios);
        $this->addElement($e);

        //Actualmente
        $e = new Zend_Form_Element_Checkbox('en_curso');
        $this->addElement($e);

        // Combo País
        $this->_listaPais = new Application_Model_Ubigeo();
        $valores = $this->_listaPais->getPaises();
        $fPais = new Zend_Form_Element_Select('pais_estudio');
//        $fPais->setRequired();
        $fPais->addMultiOption('none', 'Seleccione país');
        $fPais->addMultiOptions($valores);
//        $fPaisVal = new Zend_Validate_InArray(array_keys($valores));
//        $fPais->addValidator($fPaisVal);
        $fPais->setValue(Application_Model_Ubigeo::PERU_UBIGEO_ID);
        $fPais->errMsg = $this->_mensajeRequired;
        $this->addElement($fPais);

        //Disabled true/false
        $e = new Zend_Form_Element_Hidden('is_disabled');
        $this->addElement($e);
    }

    public function isValid($data) {
        if ($data['id_nivel_estudio_tipo'] != '0' || !empty($data['otro_estudio']) || !empty($data['institucion'])) {
        $valores = $this->_listaPais->getPaises();

            if (isset($data['en_curso'])) {
                // @codingStandardsIgnoreStart
                $this->inicio_mes->setRequired();

                $this->inicio_ano->setRequired();
                $this->inicio_ano->addValidator(
                        new App_Validate_MonthAndYearBeforeThan()
                );

                $this->pais_estudio->setRequired();
                $this->pais_estudio->addValidator(
                        new Zend_Validate_InArray(
                        array_keys($valores)
                        )
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

                    // @codingStandardsIgnoreStart
                    $this->inicio_mes->setRequired();

                    $this->inicio_ano->setRequired();
                    $this->inicio_ano->addValidator(
                            new App_Validate_MonthAndYearBeforeThan()
                    );

                    $this->pais_estudio->setRequired();
                    $this->pais_estudio->addValidator(
                            new Zend_Validate_InArray(
                            array_keys($valores)
                            )
                    );
                    // @codingStandardsIgnoreEnd
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

                    // @codingStandardsIgnoreStart
                    $this->otro_estudio->setRequired();
                    $this->otro_estudio->errMsg = $this->_mensajeRequired;
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
        return parent::isValid($data);
    }

    public function addEstudioId() {
        $e = new Zend_Form_Element_Hidden('id_estudio');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $e->setAttrib('class', 'hidden_id');
        $this->addElement($e);
    }

    public function setHiddenId($id) {
        $e = $this->getElement('id_estudio');
        $e->setAttrib('class', 'hidden_id');
        $e->setValue($id);
    }

}
