<?php

/**
 * Form para otros estudios de postulante
 *
 * @author rmontoya
 */
class Application_Form_PostulanteOtroEstudio extends App_Form {

    private $_minlengthNombre = '3';
    private $_maxlengthNombre = '75';
    private $_maxlengthInstitucion = '75';
    private $_maxlengthAnho = '4';
    private $_idOtroEstudio = 9;

    public function __construct() {
        parent::__construct();
    }

    public function init() {
        parent::init();

        //nombre
        $fName = new Zend_Form_Element_Text('txtOtherName');
        $fName->setAttrib('minLength', $this->_minlengthNombre);
        $fName->setAttrib('maxLength', $this->_maxlengthNombre);
        $fName->addValidator(
                new Zend_Validate_StringLength(
                array('min' => $this->_minlengthNombre, 'max' => $this->_maxlengthNombre,
            'encoding' => $this->_config->resources->view->charset)
                )
        );
        $fNameVal = new Zend_Validate_NotEmpty();
        $fName->addValidator($fNameVal);
        $fName->errMsg = 'Se requiere su nombre';
        $this->addElement($fName);

        //otra institución
        $fInstitution = new Zend_Form_Element_Text('txtOtherInstitution');
        $fInstitution->setAttrib('maxLength', $this->_maxlengthInstitucion);
        $this->addElement($fInstitution);

        //tipo
        $fOtherType = new Zend_Form_Element_Select('selOtherType');
        $nivelEstudio = new Application_Model_NivelEstudio();
        $listaTipos = $nivelEstudio->getTipoOtroEstudio($this->_idOtroEstudio);

        $fOtherType->addMultiOptions($listaTipos);
        $fOtherTypeclass = new Zend_Validate_InArray(array_keys($listaTipos));
        $fOtherType->addValidator($fOtherTypeclass);
        $this->addElement($fOtherType);

        //país
        $ubigeo = new Application_Model_Ubigeo();
        $listaPaises = $ubigeo->getPaises();
        $fCountry = new Zend_Form_Element_Select('selOtherCountry');
        $fCountry->addMultiOptions($listaPaises);
        $fCountry->setValue(2533);
        $this->addElement($fCountry);

        //inicio mes
        $listaMeses = Application_Model_Mes::$lista;
        $fMonthBegin = new Zend_Form_Element_Select('selOtherMonthBegins');
        $fMonthBegin->addMultiOptions($listaMeses);
        $this->addElement($fMonthBegin);

        //inicio año
        $fYearBegin = new Zend_Form_Element_Text('txtOtherYearBegins');
        $fYearBegin->setAttrib('maxLength', $this->_maxlengthAnho);
        $fYearBegin->setAttrib('max', date("Y"));
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
        $fMonthEnd = new Zend_Form_Element_Select('selOtherMonthEnd');
        $fMonthEnd->addMultiOptions($listaMeses);
        $this->addElement($fMonthEnd);

        //inicio año
        $fYearEnd = new Zend_Form_Element_Text('txtOtherYearEnd');
        $fYearEnd->setAttrib('maxLength', $this->_maxlengthAnho);
        $fYearEnd->setAttrib('min', $this->_minAnho);
        $fYearEnd->setAttrib('max', date("Y"));
        $fYearEnd->addValidator(
                new Zend_Validate_StringLength(
                    array('min' => $this->_maxlengthAnho, 'max' => $this->_maxlengthAnho,
                        'encoding' => $this->_config->resources->view->charset)
                )
        );
        $fYearEndVal = new Zend_Validate_NotEmpty();
        $fYearEnd->addValidator($fYearEndVal);
        $this->addElement($fYearEnd);

        //actualmente estudiando
        $fCurrentStudent = new Zend_Form_Element_Checkbox('actuallyStudying');
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

    public function isValid($data) {
        return parent::isValid($data);
    }

    public static $errors = array(
        'notDigits' => 'Ingrese sólo números',
        'isEmpty' => 'Campo Requerido',
        'stringLengthInvalid' => 'Documento inválido',
        'stringLengthTooLong' => 'Excede número de dígitos permitido',
        'stringLengthTooShort' => 'No tiene el mínimo de dígitos requerido',
        'callbackInvalid' => 'El Número del documento ya se encuentra registrado',
        'callbackValue' => 'Campo fecha de nacimiento inválida',
    );
}
