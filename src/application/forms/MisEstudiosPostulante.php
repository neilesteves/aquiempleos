<?php

/**
 * Form para estudios de postulante
 *
 * @author rmontoya
 */
class Application_Form_MisEstudiosPostulante extends App_Form 
{
    private $_minlengthInstitucion = 2;
    private $_maxlengthInstitucion = 100;
    private $_minlengthCarrera = 2;
    private $_maxlengthCarrera = 100;
    private $_maxlengthAnho = 4;    
    
    private $_hasHiddenId = false;
    
    public static $errors = array(
        'notDigits' => 'Ingrese sólo números',
        'isEmpty' => 'Campo Requerido',
        'stringLengthInvalid' => 'Documento inválido',
        'stringLengthTooLong' => 'Excede número de dígitos permitido',
        'stringLengthTooShort' => 'No tiene el mínimo de dígitos requerido',
        'callbackInvalid' => 'El Número del documento ya se encuentra registrado',
        'callbackValue' => 'Campo fecha de nacimiento inválida',
        'notInArray' => 'El nivel seleccionado no es valido, por favor vuelva ha intentarlo'
    );
    
    
    
    
    public function init() 
    {
        parent::init();

        
        
            $e = new Zend_Form_Element_Hidden('hidStudy');
            $e->clearDecorators();
            $e->addDecorator('ViewHelper');
            $e->setAttrib('class', 'hidden_id');
            $this->addElement($e);
        
        
        //grado
        $modelNivelEstudio = new Application_Model_NivelEstudio();
        $listaNiveles = $modelNivelEstudio->getNiveles();
        
        $fLevelStudy = new Zend_Form_Element_Select('selLevelStudy');
        $fLevelStudy->addMultiOption("", 'Selecciona un nivel');
        $fLevelStudy->addMultiOptions($listaNiveles);
        $fLevelStudyVal = new Zend_Validate_InArray(
                array_keys($listaNiveles)
        );
        $fLevelStudy->addValidator($fLevelStudyVal);
        $this->addElement($fLevelStudy);
        
        //estado
        $fStateStudy = new Zend_Form_Element_Select('selStateStudy');
        $fStateStudy->addMultiOption("", 'Selecciona un estado');
        $this->addElement($fStateStudy);
        
        //institucion
        $fInstitution = new Zend_Form_Element_Text('txtInstitution');
        $fInstitution->setAttrib('maxLength', $this->_maxlengthInstitucion);
        $this->addElement($fInstitution);
        
        //carrera
        $fCareer = new Zend_Form_Element_Text('txtCareer');
        $fCareer->setAttrib('maxLength', $this->_maxlengthCarrera);
        $fCareer->addValidator(
                new Zend_Validate_StringLength(
                array('min' => $this->_minlengthCarrera, 'max' => $this->_maxlengthCarrera,
                    'encoding' => $this->_config->resources->view->charset)
                )
        );
        $fCareerVal = new Zend_Validate_NotEmpty();
        $fCareer->addValidator($fCareerVal);
        $this->addElement($fCareer);
        
        //inicio mes
        $listaMeses = Application_Model_Mes::$lista;
        $fMonthBegin = new Zend_Form_Element_Select('selMonthBegin');
        $fMonthBegin->addMultiOptions($listaMeses);
        $this->addElement($fMonthBegin);
        
        //inicio año
        $fYearBegin = new Zend_Form_Element_Text('txtYearBegin');
        $fYearBegin->setAttrib('maxLength', $this->_maxlengthAnho);
        $fYearBegin->addValidator(
                new Zend_Validate_StringLength(
                    array('min' => $this->_maxlengthAnho, 'max' => $this->_maxlengthAnho,
                        'encoding' => $this->_config->resources->view->charset)
                )
        );
        $fYearBeginVal = new Zend_Validate_NotEmpty();
        $fYearBegin->addValidator($fYearBeginVal);
        $this->addElement($fYearBegin);
        
        //fin mes
        $fMonthEnd = new Zend_Form_Element_Select('selMonthEnd');
        $fMonthEnd->addMultiOptions($listaMeses);
        $this->addElement($fMonthEnd);
        
        //inicio año
        $fYearEnd = new Zend_Form_Element_Text('txtYearEnd');
        $fYearEnd->setAttrib('maxLength', $this->_maxlengthAnho);
        $fYearEnd->addValidator(
                new Zend_Validate_StringLength(
                    array('min' => $this->_maxlengthAnho, 'max' => $this->_maxlengthAnho,
                        'encoding' => $this->_config->resources->view->charset)
                )
        );
        $fYearEndVal = new Zend_Validate_NotEmpty();
        $fYearEnd->addValidator($fYearEndVal);
        $this->addElement($fYearEnd);
        
        //país
        $ubigeo = new Application_Model_Ubigeo();
        $listaPaises = $ubigeo->getPaises();
        $fCountry = new Zend_Form_Element_Select('selCountry');
        $fCountry->addMultiOptions($listaPaises);
        $fCountry->setValue(2533);
        $this->addElement($fCountry);
        
        //actualmente estudiando
        $fCurrentStudent = new Zend_Form_Element_Checkbox('actualStudent');
        $fCurrentStudent->setUncheckedValue(null);
        $this->addElement($fCurrentStudent);
        
        //token
        $e = new Zend_Form_Element_Hash('hidToken');       
        $this->addElement($e);
        
        //botón
        $fButton = new Zend_Form_Element_Button('button');
        $fButton->setLabel("Guardar");
        $this->addElement($fButton);
    }
    
    
    public function isValid($data) 
    {           
        if ($data['selLevelStudy'] != 1 ) {
            if (isset($data['actualStudent']) &&  $data['actualStudent']==0) {                
                $this->selMonthBegin->setRequired();
                $this->txtYearBegin->setRequired();
//                $this->txtYearBegin->addValidator(
//                        new App_Validate_MonthAndYearBeforeThan()
//                );
                $this->selMonthEnd->setRequired(false);
                $this->txtYearEnd->setRequired(false);

            } else {
                $this->txtYearEnd->addValidator(
                        new Zend_Validate_Between(
                        array('max' => date('Y'),
                            'min' => Application_Model_Anio::getMinAnio())
                        )
                );
                $this->txtYearEnd->setRequired();
                $this->selMonthEnd->setRequired();
            }
            if (
                    $data['selLevelStudy'] != 1 &&
                    $data['selLevelStudy'] != 2 &&
                    $data['selLevelStudy'] != 3
            ) {
                $this->selMonthBegin->setRequired();
                $this->txtYearBegin->setRequired();

                //institución
                $this->txtInstitution->addValidator(                        
                        new Zend_Validate_StringLength(array(
                            'min' => $this->_minlengthInstitucion, 
                            'max' => $this->_maxlengthInstitucion,
                            'encoding' => $this->_config->resources->view->charset
                        ))
                );
                $fInstitutionVal = new Zend_Validate_NotEmpty();
                $this->txtInstitution->addValidator($fInstitutionVal);
                $this->txtInstitution->setRequired();
            } 
            
            if (in_array($data['selLevelStudy'],array(1, 2, 3, 9))) {
                $this->txtInstitution->setRequired();
            } else {
                $valNotEmpty = new Zend_Validate_NotEmpty();
                $valNotEmpty->setMessages(array(
                    Zend_Validate_NotEmpty::IS_EMPTY => 'Es necesario que seleccione el Estado de Estudios',
                    Zend_Validate_NotEmpty::INVALID => 'Es necesario que seleccione el Estado de Estudios'
                ));
                $this->selStateStudy->addValidator($valNotEmpty);                
                $this->selStateStudy->setRequired();
            }
            
        }
        //var_dump($this->validarForm($data));exit;
        /// Por algun motivo el validador de selStateStudy se altera      
        //return parent::isValid($data);
        // Validamos cada elemento con su propio validador 
        // definido o por defecto:
        return $this->validarForm($data);
        
    }

    /*
     * Validamos cada elemento con su propio validador asignado
     * 
     * @param array $data       Datos del form a validarse
     * @retun bool              true, si todo es correcto, caso contrario false.
     */
    protected function validarForm($data)
    {   
        $ok = true;                
        foreach ($data as $name => $value) {               
            if (null !== $this->getElement($name)) {
                $validations = $this->getElement($name)->getValidators();
                foreach ($validations as $nameValida => $valida) {
             //       var_dump($value,$nameValida,$valida->isValid($value));
                    $ok = $valida->isValid($value) && $ok;                                        
                }            
            }            
        }
//        ;exit;
//        if ($data['selLevelStudy'] != 1 ) {
//            if (isset($data['actualStudent']) && $data['actualStudent']==0) {
//                $dateStart = new Zend_Date();
//                $dateStart->set($data['txtYearBegin'], Zend_Date::YEAR);
//                $dateStart->set($data['selMonthBegin'], Zend_Date::MONTH);
//
//                $dateEnd = new Zend_Date();
//                $dateEnd->set(2, Zend_Date::DAY);
//                $dateEnd->set(date('Y'), Zend_Date::YEAR);
//                $dateEnd->set(date('m'), Zend_Date::MONTH);
//
//                if ( $dateStart->isEarlier($dateEnd)) {
//                    $ok = ($ok && true);
//                } else {
//                    $this->getElement('txtYearBegin')->setErrorMessages(array(
//                        'invalid' => 'Fecha debe ser menor o igual a la actual.'
//                    ));
//                }
//
//
//            }
//        }
                
        return $ok;
    }
    
}
