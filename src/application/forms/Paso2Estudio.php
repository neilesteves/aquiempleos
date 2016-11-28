<?php

class Application_Form_Paso2Estudio extends App_Form {

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

        //Colegiatura
        //$e = new Zend_Form_Element_Checkbox('colegiatura');
        //$this->addElement($e);

        //Numero colegiatura
        $e = new Zend_Form_Element_Text('colegiatura_numero');
        $e->setAttrib('maxLength', $this->_maxlengthColegiatura);
        $e->addValidator(new Zend_Validate_Int());
        $e->errMsg = 'Ingrese número';
        $v = new Zend_Validate_StringLength(array(
            'max' => $this->_maxlengthInstitucion,
            'encoding' => $this->_config->resources->view->charset
        ));
        $e->addValidator($v);
        $this->addElement($e);


        $e = new Zend_Form_Element_Text('institucion');
        $e->setAttrib('maxLength', $this->_maxlengthInstitucion);
        $e->setAttrib('param', '4');
        $e->setAttrib('model', 'institucion');
        $this->addElement($e);

        //Hidden Id Institucion
//        $e = new Zend_Form_Element_Hidden('id_institucion');
//        $e->setRequired(false);
////        $e->clearDecorators();
////        $e->addDecorator('ViewHelper');
//        $this->addElement($e);

        //Tipo de Carrera
        $tipoCarrera = new Application_Model_TipoCarrera();
        $this->_listaTipoCarrera = $tipoCarrera->getTiposCarreras();
        $e = new Zend_Form_Element_Select('id_tipo_carrera');
        $e->addMultiOption('0', 'Selecciona tipo de carrera');
        $e->addMultiOptions($this->_listaTipoCarrera);
        $this->addElement($e);

        //Carrera
        $this->_carrera = $carrera = new Application_Model_Carrera();
        $this->_listaCarrera = $carrera->getCarreras();
        $e = new Zend_Form_Element_Select('id_carrera');
        $e->addMultiOption('0', 'Selecciona carrera');
        //$e->addMultiOptions($this->_listaCarrera);
//        foreach ($this->_listaCarrera as $dataCarrera) {
//            $e->addOption($dataCarrera['id'], $dataCarrera['nombre'], array('rel' => $dataCarrera['id_tipo_carrera']));
//        }
        $this->addElement($e);

        // Texto para la carrera
        $e = new Zend_Form_Element_Text('otro_carrera');
        $e->setAttrib('maxLength', $this->_maxlengthInstitucion);
        $e->setAttrib('model', 'carrera');
        $e->setAttrib('param', '4');
        $this->addElement($e);

        //Fecha
        $listaMeses = Application_Model_Mes::$lista;
        $listaAnios = Application_Model_Anio::getAnios();

        //Mes Inicio
        $e = new Zend_Form_Element_Select('inicio_mes');
        $e->setValue(1);
        $e->setRequired();
        $e->addMultiOptions($listaMeses);
        $this->addElement($e);

        //Anho Inicio
        $e = new Zend_Form_Element_Select('inicio_ano');
        $e->setValue(date('Y') - 1);
        $e->setRequired();
        $e->addValidator(new App_Validate_MonthAndYearBeforeThan());
        $e->errMsg = 'Fecha debe ser menor o igual a la actual.';
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
        $fPais->setRequired();
        $fPais->addMultiOption('0', 'Seleccione país');
        $fPais->addMultiOptions($valores);
        $fPaisVal = new Zend_Validate_InArray(array_keys($valores));
        $fPais->addValidator($fPaisVal);
        $fPais->setValue(Application_Model_Ubigeo::PERU_UBIGEO_ID);
        $fPais->errMsg = $this->_mensajeRequired;
        $this->addElement($fPais);

        //Disabled true/false
        $e = new Zend_Form_Element_Hidden('is_disabled');
        $this->addElement($e);
    }

    public function isValid($data) {
        $valores = $this->_listaPais->getPaises();

        if ($data['id_nivel_estudio'] != 1 ) {
          
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

                    $this->pais_estudio->setRequired();
                    $this->pais_estudio->addValidator(
                            new Zend_Validate_InArray(
                            array_keys($valores)
                            )
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


                    if (isset($data['id_carrera'])) {
                        if ($data['id_carrera'] == Application_Model_Carrera::OTRO_CARRERA) {
                            $v = $this->otro_carrera->addValidator(
                                new Zend_Validate_StringLength(
                                array(
                            'min' => '2', 'max' => $this->_maxlengthInstitucion,
                            'encoding' => $this->_config->resources->view->charset
                                )
                                )
                        );

                        $this->otro_carrera->setRequired();
                        }
                    }
                        
                    $this->id_tipo_carrera->addValidator(
                            new Zend_Validate_InArray(
                            array_keys($this->_listaTipoCarrera) 
                            )
                    );
                    $this->id_tipo_carrera->errMsg = $this->_mensajeRequired;
                    
                    $this->id_carrera->setRequired();
                    $this->id_carrera->clearValidators();
                    $this->id_carrera->addValidator(
                            new Zend_Validate_InArray(
                            array_keys($this->_listaCarrera)
                            )
                    );
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
                } else {
                    // @codingStandardsIgnoreStart
//                    $this->institucion->setAttrib("disabled", "disabled");
//                    $this->id_carrera->setAttrib("disabled", "disabled");
//                    $this->id_tipo_carrera->setAttrib("disabled", "disabled");
//                    $this->id_nivel_estudio_tipo->setAttrib("disabled", "disabled");
                    // @codingStandardsIgnoreEnd
                }
                if ($data['id_nivel_estudio'] == 9) {

                    $this->otro_estudio->setRequired();
                    $this->otro_estudio->errMsg = "Ingresa los datos de tus otros estudios";
                    $this->id_tipo_carrera->setRequired(false);
                    $this->id_tipo_carrera->clearValidators();
                    $this->id_carrera->setRequired(false);
                    $this->id_carrera->clearValidators();
                    $this->otro_carrera->setRequired(false);
                    $this->otro_carrera->clearValidators();

//                    $this->otro_carrera->setAttrib("disabled", "disabled");
//                    $this->id_tipo_carrera->setAttrib("disabled", "disabled");

                }
                if (in_array($data['id_nivel_estudio'],array(2,3))) {
                    $this->institucion->setRequired();
                    $this->institucion->errMsg = "Ingrese el nombre de la institución";
                }
            }
        } else {
            $this->inicio_mes->setRequired(false);
            $this->inicio_ano->setRequired(false);
            $this->pais_estudio->setRequired(false);
            $this->pais_estudio->clearValidators();
            $this->id_tipo_carrera->setRequired(false);
            $this->id_tipo_carrera->clearValidators();
            $this->id_carrera->setRequired(false);
            $this->id_carrera->clearValidators();
            $this->otro_carrera->setRequired(false);
            $this->otro_carrera->clearValidators();
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
    
    public function setElementNivelEstudio($padre) {
        
        $e = $this->getElement('id_nivel_estudio_tipo');
        $e->addMultiOption('0', 'Selecciona un tipo');
        if(!empty($padre))
        {
            $listaNivelesTipo = $this->_nivelEstudio->getSubNiveles($padre);
            $e->addMultiOptions($listaNivelesTipo);
        }
        
        
    }
    public function setElementCarrera($padre) {
        
        $e = $this->getElement('id_carrera');
        $e->addMultiOption('0', 'Selecciona carrera');
        if(!empty($padre))
        {
            $listaNivelesTipo = $this->_carrera->filtrarCarrera($padre);
            $e->addMultiOptions($listaNivelesTipo);
        }
        
        
    }

}
