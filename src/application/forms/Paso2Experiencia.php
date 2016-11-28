<?php

/**
 * Description of Form Paso2 Section Experiencia
 *
 * @author Jesus
 */
class Application_Form_Paso2Experiencia extends App_Form {

    private $_maxlengthEmpresa = '75';
    private $_maxlengthRubro = '75';
    private $_maxlengthPuesto = '75';
    private $_maxlengthCosto = '14';
    private $_maxlenghtAnho = '4';
    private $_listaPuestos = array();
    private $_listaTipoProyectos = array();

    public function __construct($hasHiddenId = false) {
        parent::__construct();
        if ($hasHiddenId) {
            $this->addExperienciaId();
        }
    }

    public function init() {
        parent::init();
        $this->setMethod('post');

        //Empresa
        $e = new Zend_Form_Element_Text('otra_empresa');
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
        $e = new Zend_Form_Element_Text('otro_rubro');
        $e->setAttrib('maxLength', $this->_maxlengthRubro);
        $v = new Zend_Validate_StringLength(
                array(
            'min' => '2', 'max' => $this->_maxlengthRubro,
            'encoding' => $this->_config->resources->view->charset
                )
        );
        $e->addValidator($v);
        $this->addElement($e);

        //Nombre del puesto
        $puesto = new Application_Model_Puesto();
        $this->_listaPuestos = $puesto->getPuestos();
        $e = new Zend_Form_Element_Select('id_puesto');
        $e->setRequired();
        $e->addMultiOption('0', 'Selecciona un puesto');
        $e->addMultiOptions($this->_listaPuestos);
        $v = new Zend_Validate_InArray(array_keys($this->_listaPuestos));
        $e->addValidator($v);
        $e->errMsg = $this->_mensajeRequired;
        $this->addElement($e);

        //Descipcion del puesto
        $e = new Zend_Form_Element_Text('otro_puesto');
        $e->setAttrib('maxLength', $this->_maxlengthPuesto);
        //$e->setRequired();
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
        $nivelPuesto = new Application_Model_NivelPuesto();
        $niveles = $nivelPuesto->getNiveles();
        $e = new Zend_Form_Element_Select('id_nivel_puesto');
        $e->setRequired();
        $e->addMultiOption('0', 'Selecciona un nivel');
        $e->addMultiOptions($niveles);
        $v = new Zend_Validate_InArray(array_keys($niveles));
        $e->addValidator($v);
        $e->errMsg = $this->_mensajeRequired;
        $this->addElement($e);

        //Descipcion de nivel puesto
        $e = new Zend_Form_Element_Text('otro_nivel_puesto');
        $e->setAttrib('maxLength', $this->_maxlengthPuesto);
        //$e->setRequired();
        $e->errMsg = $this->_mensajeRequired;
        $v = new Zend_Validate_StringLength(
                array(
            'min' => '2', 'max' => $this->_maxlengthPuesto,
            'encoding' => $this->_config->resources->view->charset
                )
        );
        $e->addValidator($v);
        $this->addElement($e);

        //Area
        $area = new Application_Model_Area();
        $listaAreas = $area->getAreas();
        $e = new Zend_Form_Element_Select('id_area');
        $e->setRequired();
        $e->addMultiOption('0', 'Selecciona un área');
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
        $e = new Zend_Form_Element_Select('id_tipo_proyecto');
        $e->addMultiOption('0', 'Selecciona un Tipo de Proyecto');
        $e->addMultiOptions($this->_listaTipoProyectos);
        $this->addElement($e);

        //Nombre de proyecto
        $e = new Zend_Form_Element_Text('nombre_proyecto');
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
        $e = new Zend_Form_Element_Text('costo_proyecto');
        $e->setAttrib('maxLength', $this->_maxlengthCosto);
        $e->errMsg = $this->_mensajeRequired;
        $v = new Zend_Validate_StringLength(
                array(
            'min' => '2', 'max' => $this->_maxlengthCosto,
            'encoding' => $this->_config->resources->view->charset
                )
        );
        $e->addValidator($v);
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

        //Año Inicio
        $e = new Zend_Form_Element_Select('inicio_ano');
        $e->setValue(date('Y') - 1);
        $e->setRequired();
        $e->addMultiOptions($listaAnios);
        $e->addValidator(new App_Validate_MonthAndYearBeforeThan());
        $e->errMsg = 'Fecha debe ser menor o igual a la actual.';
        $this->addElement($e);

        //Mes Fin
        $e = new Zend_Form_Element_Select('fin_mes');
        $e->setValue(1);
        $e->addMultiOptions($listaMeses);
        $this->addElement($e);

        //Año Fin
        $e = new Zend_Form_Element_Select('fin_ano');
        $e->setValue(date('Y'));
        $e->addMultiOptions($listaAnios);
        $this->addElement($e);

        //Actualmente
        $e = new Zend_Form_Element_Checkbox('en_curso');
        $e->getValue();
        $this->addElement($e);

        //Comentarios
        $e = new Zend_Form_Element_Textarea('comentarios');
        $e->setAttrib('maxLength', 500);
        $v = new Zend_Validate_StringLength(
                array(
            'max' => 500,
            'encoding' => $this->_config->resources->view->charset
                )
        );
        $e->addValidator($v);
        $e->errMsg = "Ingresar maximo " . 500 . " caracteres";
        $this->addElement($e);

        //Disabled true/false
        $e = new Zend_Form_Element_Hidden('is_disabled');
        $this->addElement($e);
    }

    public function isValid($data) {
        if (!isset($data['en_curso']) || $data['en_curso'] != 1) {
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
        
        if ($data['id_nivel_puesto'] == 10 && $data['otro_nivel_puesto'] == '') {
            $this->otro_nivel_puesto->setRequired();
            $this->otro_nivel_puesto->errMsg = $this->_mensajeRequired;
        }
        
        if ($data['id_puesto'] == Application_Model_Puesto::OTROS_PUESTO_ID) {
            $this->otro_puesto->setRequired();
            $this->otro_puesto->errMsg = $this->_mensajeRequired;
        }
            
            
            
        if (isset($data['lugar']) && $data['lugar'] == Application_Model_Experiencia::LUGAR_CAMPO) {
            $this->id_tipo_proyecto->setRequired();
            $this->id_tipo_proyecto->addValidator(new Zend_Validate_InArray(
                    array_keys($this->_listaTipoProyectos)
            ));
            $this->id_tipo_proyecto->errMsg = $this->_mensajeRequired;

            $this->nombre_proyecto->setRequired();
            $this->nombre_proyecto->addValidator(new Zend_Validate_StringLength(array(
                'min' => '2', 'max' => $this->_maxlengthEmpresa,
                'encoding' => $this->_config->resources->view->charset
            )));
            $this->nombre_proyecto->errMsg = $this->_mensajeRequired;

            $this->costo_proyecto->addValidator(new Zend_Validate_Float(array('locale' => 'en_US')));
            $this->costo_proyecto->errMsg = "Debe ingresar costo correcto";
        }
        return parent::isValid($data);
    }

    public function addExperienciaId() {
        $e = new Zend_Form_Element_Hidden('id_Experiencia');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $e->setAttrib('class', 'hidden_id');
        $this->addElement($e);
    }

    public function setHiddenId($id) {
        $e = $this->getElement('id_Experiencia');
        $e->setValue($id);
        $e->setAttrib('class', 'hidden_id');
    }
    
//    public function addIdLinkedIn() {
//        $e = new Zend_Form_Element_Hidden('codLink');
//        $e->clearDecorators();
//        $e->addDecorator('ViewHelper');
//        $this->addElement($e);
//    }

}
