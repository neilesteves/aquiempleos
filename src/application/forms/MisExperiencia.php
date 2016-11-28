<?php

class Application_Form_MisExperiencia extends App_Form
{

    private $_maxlengthEmpresa = '75';
    private $_maxlengthRubro = '75';
    private $_maxlengthPuesto = '75';
    private $_maxlengthCosto = '14';
    private $_maxlenghtAnho = '4';
    private $_listaPuestos = array();
    private $_listaTipoProyectos = array();
    // longitud del textarea
    private $_maxlengthTareas = '1500';

    const OTRO_PUESTO = 1292;

    public function __construct( $hasHiddenId = false )
    {
        parent::__construct();
        if($hasHiddenId) {
            $this->addExperienciaId();
        }
    }

    public static $errors = array(
        'isEmpty' => 'Campo Requerido',
        'callbackValue' => 'El Número del documento ya se encuentra registrado',
        'notSame' => 'Por favor vuelva a intentarlo',
        'missingToken' => 'Por favor vuelva a intentarlo',
        'notInArray' => 'No se encontro el registro',
        'notBetween' => 'La fecha no esta dentro del rango permitido',
        'stringLengthTooLong' => 'La cantidad de caracteres no es valida',
        'invalid' => 'Las fechas no son correctas, Por favor vuelva a intentarlo',
    );

    public function init()
    {
        parent::init();
        $this->setMethod('post');

        //Empresa
        $e = new Zend_Form_Element_Text('txtExperience');
        $e->setAttrib('maxLength', $this->_maxlengthEmpresa);
        $e->setRequired();
        $e->errMsg = $this->_mensajeRequired;
        $v = new Zend_Validate_StringLength(
                array(
            'min' => '2', 'max' => $this->_maxlengthEmpresa,
            'encoding' => $this->_config->resources->view->charset
                )
        );
        $e->addValidator($v);
        $this->addElement($e);

        //Rubro
        $e = new Zend_Form_Element_Text('txtIndustry');
        $e->setAttrib('maxLength', $this->_maxlengthRubro);

        $e->setRequired(false);
        $this->addElement($e);


        $e = new Zend_Form_Element_Text('txtJob');
        $e->setAttrib('maxLength', $this->_maxlengthPuesto);
        $e->setRequired(false);
        $e->errMsg = $this->_mensajeRequired;
        $v = new Zend_Validate_StringLength(
                array(
            'min' => '2', 'max' => $this->_maxlengthPuesto,
            'encoding' => $this->_config->resources->view->charset
                )
        );
        $e->addValidator($v);
        $this->addElement($e);

        //Nivel del puesto
        //$nivelPuesto = new Application_Model_NivelPuesto();
        //$niveles = $nivelPuesto->getNiveles();
        $niveles = array();
        $e = new Zend_Form_Element_Select('selLevelJob');
        $e->setRequired();
        $e->addMultiOption('', 'Selecciona un nivel');
        $e->addMultiOptions($niveles);
        $v = new Zend_Validate_InArray(array_keys($niveles));
        $e->addValidator($v);
        $e->errMsg = $this->_mensajeRequired;
        $this->addElement($e);

        //Area
        $area = new Application_Model_Area();
        $listaAreas = $area->getAreas();
        $e = new Zend_Form_Element_Select('selLevelArea');
        $e->setRequired();
        $e->addMultiOption('', 'Selecciona un área');
        $e->addMultiOptions($listaAreas);
        $v = new Zend_Validate_InArray(array_keys($listaAreas));
        $e->addValidator($v);
        $e->errMsg = "Es necesario ingresar una area";
        $this->addElement($e);

        //Lugar
        $e = new Zend_Form_Element_Radio('lugar');
        $e->addMultiOption(Application_Model_Experiencia::LUGAR_CAMPO, "Obra/Campo");
        $e->addMultiOption(Application_Model_Experiencia::LUGAR_OFICINA, "Oficina");
        $e->setValue(Application_Model_Experiencia::LUGAR_OFICINA);
        $e->setAttrib('label_class', 'ioption');
        //$e->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'control-group'));

        $e->setSeparator(' ');
        $this->addElement($e);

        //Tipo de proyecto
        $tipoProyecto = new Application_Model_TipoProyecto();
        $this->_listaTipoProyectos = $tipoProyecto->getTipoProyectos();
        $e = new Zend_Form_Element_Select('selProjectType');
        $e->addMultiOption('', 'Selecciona un Tipo de Proyecto');
        $e->addMultiOptions($this->_listaTipoProyectos);
        $this->addElement($e);

        //Nombre de proyecto
        $e = new Zend_Form_Element_Text('txtNameProjectType');
        $e->setAttrib('maxLength', $this->_maxlengthPuesto);
        $e->errMsg = $this->_mensajeRequired;
        $v = new Zend_Validate_StringLength(
                array(
            'min' => '2', 'max' => $this->_maxlengthPuesto,
            'encoding' => $this->_config->resources->view->charset
                )
        );
        $e->addValidator($v);
        $this->addElement($e);

        //Costo de proyecto
        $e = new Zend_Form_Element_Text('txtBudgetProjectType');
        $e->setAttrib('maxLength', $this->_maxlengthCosto);
        $e->errMsg = $this->_mensajeRequired;
        $v = new Zend_Validate_StringLength(
                array(
            'min' => '2',
            'max' => $this->_maxlengthCosto,
            'encoding' => $this->_config->resources->view->charset
                )
        );
        $e->addValidator($v);
        $this->addElement($e);

        //Fecha
        $listaMeses = Application_Model_Mes::$lista;
        $listaAnios = Application_Model_Anio::getAnios();

        //Mes Inicio
        $e = new Zend_Form_Element_Select('selMonthBegin');
        $e->setValue(1);
        $e->setRequired();
        $e->addMultiOptions($listaMeses);
        $this->addElement($e);

        //Año Inicio
        $e = new Zend_Form_Element_Text('txtYearBegin');
//        $e->setAttribs(array('placeholder'=>date('Y')-1));

        $e->errMsg = 'Fecha debe ser menor o igual a la actual.';
        $this->addElement($e);

        //Mes Fin
        $e = new Zend_Form_Element_Select('selMonthEnd');
        $e->setValue(1);
        $e->addMultiOptions($listaMeses);
        $this->addElement($e);

        //Año Fin
        $e = new Zend_Form_Element_Text('txtYearEnd');
//        $e->setAttribs(array('placeholder'=>date('Y')));
//        $e->addMultiOptions($listaAnios);
        $this->addElement($e);

        //Comentarios
        $e = new Zend_Form_Element_Textarea('txaComments');
        $e->setAttrib('maxLength', $this->_maxlengthTareas);
        $v = new Zend_Validate_StringLength(
                array(
            'max' => $this->_maxlengthTareas,
            'encoding' => $this->_config->resources->view->charset
                )
        );
        $e->addValidator($v);
        $e->errMsg = "Ingresar maximo " . $this->_config->app->cantdescExp . " caracteres";
        $this->addElement($e);



        $e = new Zend_Form_Element_Hidden('hidExperiences');
//        $e->setRequired(false);
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $e->setAttrib('class', 'hidden_id');
        $e->setValue(0);
        $this->addElement($e);


        $e = new Zend_Form_Element_Hidden('hidJob');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $e->setAttrib('class', 'hidden_id');
        $this->addElement($e);

        $e = new Zend_Form_Element_Hash('hidToken');
        $this->addElement($e);
    }

    public function isValid( $data )
    {

        $nivelPuesto = new Application_Model_NivelPuesto();
        if(!isset($data['chkInProgress'])) {

            $this->txtYearBegin->addValidator(new App_Validate_MonthAndYearBeforeThan());
            $this->txtYearBegin->setRequired(true);
            $this->selMonthEnd->setRequired(true);

            // @codingStandardsIgnoreStart
            $this->txtYearEnd->addValidator(
                    new Zend_Validate_Between(
                    array('max' => date('Y'),
                'min' => Application_Model_Anio::getMinAnio())
                    )
            );
            $this->txtYearEnd->setRequired();
        } else {

//            $this->selMonthBegin->clearValidators();
            $this->txtYearBegin->clearValidators();

            $this->txtYearEnd->setRequired(false)->clearValidators();
            $this->selMonthEnd->setRequired(false)->clearValidators();

//            $this->removeElement('txtYearEnd');
//            $this->removeElement('selMonthEnd');
        }


        $niveles = $nivelPuesto->getNivelesByAreaParis($data['selLevelArea']);
        $v = new Zend_Validate_InArray(array_keys($niveles));
        $this->selLevelJob->addValidator($v);
        $this->selLevelJob->addMultiOptions($niveles);
        if($data['hidJob'] == Application_Model_Puesto::OTROS_PUESTO_ID) {
            $this->txtJob->setRequired();
            $this->txtJob->errMsg = $this->_mensajeRequired;
        }
        if(isset($data['rdLugar']) && $data['rdLugar'] == Application_Model_Experiencia::LUGAR_CAMPO) {
            $this->selProjectType->setRequired();
            $this->selProjectType->addValidator(new Zend_Validate_InArray(
                    array_keys($this->_listaTipoProyectos)
            ));
            $this->selProjectType->errMsg = $this->_mensajeRequired;

            $this->txtNameProjectType->setRequired();
            $this->txtNameProjectType->addValidator(new Zend_Validate_StringLength(array(
                'min' => '2', 'max' => $this->_maxlengthEmpresa,
                'encoding' => $this->_config->resources->view->charset
            )));
            $this->txtNameProjectType->errMsg = $this->_mensajeRequired;

            $this->txtBudgetProjectType->addValidator(new Zend_Validate_Float(array('locale' => 'en_US')));
            $this->txtBudgetProjectType->errMsg = "Debe ingresar costo correcto";
        }
        return parent::isValid($data);
    }

    public static function getMensajesErrors( $fom )
    {
        if(count($fom->getMessages()) == 0) {
            return;
        }

        $errors = array();
        $mesaje = '';
        foreach ($fom->getMessages() as $form => $error) {
            foreach ($error as $value => $key) {
                $errors = self::$errors[$value];
            }
        }
        return $errors;
    }

}
