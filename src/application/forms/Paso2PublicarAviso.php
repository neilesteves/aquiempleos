<?php

class Application_Form_Paso2PublicarAviso extends App_Form
{

    private $_maxlengthPuesto = '120';
    private $_maxlengthOtroNombreEmpresa = '80';
    private $_maxlengthEmail = '75';
    private $_online = false;
    protected $data;
    private $_id_empresa = false;

    public function __construct( $hasHiddenId = false, $online = false, $data = null )
    {
        if(isset($online) && $online == true) {
            $this->_online = true;
        }

        if(isset($data)) {
            $this->data = $data;
        }

        parent::__construct();
        if($hasHiddenId) {
            $this->addExperienciaId();
        }
    }

    protected function getPuestos()
    {
        $preferencial = Application_Model_AnuncioWeb::TIPO_PREFERENCIAL;
        if(isset($this->data['tipoPublicacion']) && $this->data['tipoPublicacion'] == $preferencial) {
            $puesto = array(Application_Model_Puesto::OTROS_PUESTO_ID =>
                Application_Model_Puesto::OTROS_PUESTO_NAME);
            return $puesto;
        }

        $puesto = new Application_Model_Puesto();
        return $puesto->getPuestos();
    }

    public function init()
    {
        parent::init();

        //Tipo de Puesto
        $puesto = new Application_Model_Puesto();
        $listaPuesto = $puesto->getPuestos();
        unset($listaPuesto[Application_Model_Puesto::OTROS_PUESTO_ID]);
        $e = new Zend_Form_Element_Select('id_puesto');
        $e->setRequired();
        $e->addMultiOption('-1', 'Seleccionar tipo');
        $e->addMultiOptions($listaPuesto);
        $v = new Zend_Validate_InArray(array_keys($listaPuesto));
        $e->addValidator($v);
        $e->errMsg = $this->_mensajeRequired;
        $this->addElement($e);

        //Nombre del Puesto
        $e = new Zend_Form_Element_Text('nombre_puesto');
        $e->setRequired();

        $e->setAttrib('maxLength', $this->_config->avisopaso2->puestocaracteres);
        $v = new Zend_Validate_StringLength(array(
            'min' => '2', 'max' => $this->_config->avisopaso2->puestocaracteres,
            'encoding' => $this->_config->resources->view->charset
        ));
        $e->addValidator($v);
        $e->errMsg = $this->_mensajeRequired;
        $this->addElement($e);

        //Nivel del puesto
        $nivel = new Application_Model_NivelPuesto();
        //$listaNiveles = $nivel->getNiveles();
        $listaNiveles = array();
        $e = new Zend_Form_Element_Select('id_nivel_puesto');
        $e->setRequired();
        $e->addMultiOption('-1', 'Seleccionar nivel');
        $e->addMultiOptions($listaNiveles);
        $v = new Zend_Validate_InArray(array_keys($listaNiveles));
        $e->addValidator($v);
        $e->errMsg = $this->_mensajeRequired;
        $this->addElement($e);

        //Area
        $area = new Application_Model_Area();
        $listaAreas = $area->getAreasAviso();
        if(MODULE == 'admin') {
            $listaAreas = $area->getAreasAvisoAdmin();
        }

        $e = new Zend_Form_Element_Select('id_area');
        $e->setRequired();
        $e->addMultiOption('-1', 'Seleccionar area');
        $e->addMultiOptions($listaAreas);
        $v = new Zend_Validate_InArray(array_keys($listaAreas));
        $e->addValidator($v);
        $e->errMsg = $this->_mensajeRequired;
        $this->addElement($e);

        //Funciones
        $e = new Zend_Form_Element_Textarea('funciones');
        $e->setRequired();
        $e->errMsg = $this->_mensajeRequired;
        $this->addElement($e);

        //Responsabilidades
        $e = new Zend_Form_Element_Textarea('responsabilidades');
        //        $e->setRequired();
        $e->errMsg = $this->_mensajeRequired;
        $this->addElement($e);

        //Salario
        $config = Zend_Registry::get('config');
        $rango = $config->busqueda->filtros->rangoRemuneracion->toArray();
        $moneda = $config->app->moneda;
        $data = array();
        for ($i = 0; $i < count($rango); $i++) {
            if($i == 0) {
                $data['0-' . $rango[$i]] = 'Hasta ' . $moneda . $rango[$i];
            } elseif($i == count($rango) - 1) {
                $data[($rango[$i - 1] + 1) . '-' . $rango[$i]] = $moneda . ($rango[$i - 1] + 1) . ' - ' . $moneda . $rango[$i];
                $data[($rango[$i] + 1) . '-max'] = $moneda . ($rango[$i] + 1) . ' a más';
            } else {
                $data[($rango[$i - 1] + 1) . '-' . $rango[$i]] = $moneda . ($rango[$i - 1] + 1) . ' - ' . $moneda . $rango[$i];
            }
        }
        $e = new Zend_Form_Element_Select('salario');
        $e->addMultiOption('-1', 'Seleccionar salario');
        $e->addMultiOptions($data);
        $this->addElement($e);

        //mostrar salario
        $e = new Zend_Form_Element_Checkbox('mostrar_salario');
        $this->addElement($e);

        //mostrar nombre de la empresa
        $e = new Zend_Form_Element_Checkbox('mostrar_empresa_opcion');
        $this->addElement($e);

        //mostrar nombre de la empresa
        $e = new Zend_Form_Element_Checkbox('discapacidad');
        $this->addElement($e);

        //mostrar empresa
        $e = new Zend_Form_Element_Hidden('mostrar_empresa');
        $e->setValue('1');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);
        $this->addElement($e);

        //alias de la empresa
        $e = new Zend_Form_Element_Text('otro_nombre_empresa');
        $e->setAttrib('maxLength', $this->_config->avisopaso2->mostrarnombrecaracteres);
        $e->setValue($this->_config->avisopaso2->mostrarnombredefault);
        $v = new Zend_Validate_StringLength(
                array(
            'min' => '2', 'max' => $this->_config->avisopaso2->mostrarnombrecaracteres,
            'encoding' => $this->_config->resources->view->charset
                )
        );
        $this->addElement($e);



        //ncorreo
        $fEmail = new Zend_Form_Element_Text('correo');
        $fEmail->setAttrib('maxLength', $this->_maxlengthEmail);
        $fEmailVal = new Zend_Validate_EmailAddress(
                array("allow" => Zend_Validate_Hostname::ALLOW_ALL), true
        );
        $fEmail->addFilter(new Zend_Filter_StringToLower());
        $fEmail->addValidator($fEmailVal, true);
        $this->addElement($fEmail);

        $adecsysCode = new Zend_Form_Element_Text('adecsys_code');
        $adecsysCode->setAttrib('maxLength', 8);
        $adecsysCode->addValidator(new Zend_Validate_Int());
        $this->addElement($adecsysCode);

        $adecsysCode = new Zend_Form_Element_Text('adecsys_code');
        $adecsysCode->setAttrib('maxLength', 8);
        $adecsysCode->addValidator(new Zend_Validate_Int());
        $this->addElement($adecsysCode);

        //producto
        $e = new Zend_Form_Element_Hidden('id_tarifa');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);

        // CSFR protection
        $e = new Zend_Form_Element_Hash('token');
        $e->setSalt(md5(uniqid(rand(), TRUE)));
        $e->setTimeout(1800); // 30 min
        $this->addElement($e);

        if(isset($this->_online) && $this->_online == true) {
            // @codingStandardsIgnoreStart
            $this->id_nivel_puesto->setAttrib("disabled", "disabled");
            $this->id_area->setAttrib("disabled", "disabled");
            $this->salario->setAttrib("disabled", "disabled");
            $this->mostrar_salario->setAttrib("disabled", "disabled");
            // @codingStandardsIgnoreEnd
        }
    }

    public function isValid( $data )
    {
        $nivelPuesto = new Application_Model_NivelPuesto();
        $resultData = $nivelPuesto->getNivelesByAreaSelect($data['id_area']);
        $this->id_nivel_puesto->addMultiOptions($resultData);

        $this->id_nivel_puesto->addValidator(
                new Zend_Validate_InArray(array_keys($resultData))
        );
        $str = $this->getView()->getScriptPaths();
        $admin = strpos($str[0], 'admin');
        $this->discapacidad->clearValidators();
        $this->discapacidad->setRequired(false);
        $this->token->clearValidators();
        
        if($admin != false) {
            /*$this->id_nivel_puesto->clearValidators();
            $this->id_nivel_puesto->setRequired(false);*/

            $this->id_nivel_puesto->addValidator(
                    new Zend_Validate_InArray(array_keys($resultData))
            );

            $this->token->clearValidators();
        }
        if($data['id_tarifa'] == 1 && isset($this->id_puesto)) {
            $puesto = new Application_Model_Puesto();
            $listaPuesto = $puesto->getPuestos();
            $this->id_puesto->addMultiOptions($listaPuesto);
            $this->id_puesto->addValidator(new Zend_Validate_InArray(array_keys($listaPuesto)));
        }
        
        if($this->_online == true) {
            // @codingStandardsIgnoreStart
            if(isset($this->id_puesto)) {
                $this->id_puesto->clearValidators();
                $this->id_puesto->setRequired(false);
            }

            $this->nombre_puesto->clearValidators();
            $this->nombre_puesto->setRequired(false);
            $this->id_nivel_puesto->clearValidators();
            $this->id_nivel_puesto->setRequired(false);
            $this->id_area->clearValidators();
            $this->id_area->setRequired(false);
            $this->otro_nombre_empresa->clearValidators();
            $this->otro_nombre_empresa->setRequired(false);
            // @codingStandardsIgnoreEnd
        } else {


            if($admin == false) {
                // $this->getElement('nombre_puesto')->addValidator(new Zend_Validate_Alpha(true));
                $objContador = new App_Controller_Action_Helper_Contador();
                $methodVariable = array($objContador, 'contadorPalabraText');
                $fval = new Zend_Validate_Callback(
                        array(
                    'callback' => $methodVariable, 'options' => array($data['nombre_puesto'],
                        $this->_config->avisopaso2->puestonumeropalabra)
                        )
                );
                $this->getElement('nombre_puesto')->addValidator($fval);
            }
            if($data['mostrar_empresa'] == 1) {
                $this->getElement('otro_nombre_empresa')->clearValidators();
                $this->getElement('otro_nombre_empresa')->setRequired(false);
            } else {
                if($admin == false) {
                    $objContador = new App_Controller_Action_Helper_Contador();
                    $methodVariable = array($objContador, 'contadorPalabraText');
                    $fval = new Zend_Validate_Callback(
                            array(
                        'callback' => $methodVariable, 'options' => array($data['otro_nombre_empresa'],
                            $this->_config->avisopaso2->mostrarnombrenumeropalabra)
                            )
                    );
                    $this->getElement('otro_nombre_empresa')->addValidator($fval);
                }
            }
        }
        return parent::isValid($data);
    }

    public function addExperienciaId()
    {
        $e = new Zend_Form_Element_Hidden('id_aviso');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);
    }

    public function setHiddenId( $id )
    {
        $e = $this->getElement('id_aviso');
        $e->setValue($id);
    }

    public static function errorsOtroNombre( $x )
    {
        $config = Zend_Registry::get("config");
        // @codingStandardsIgnoreStart
        $variable = array(
            'notAlpha' => 'Debe ingresar solo texto.',
            'isEmpty' => 'Debe ingresar un nombre válido.',
            'callbackValue' => 'Debe ingresar un máximo de ' .
            $config->avisopaso2->mostrarnombrenumeropalabra . ' palabras.'
        );
        // @codingStandardsIgnoreEnd
        return $variable[$x];
    }

    public static function errorsOtroPuesto( $x )
    {
        $config = Zend_Registry::get("config");
        // @codingStandardsIgnoreStart
        $variable = array(
            'notAlpha' => 'Debe ingresar solo texto.',
            'isEmpty' => 'Debe ingresar un nombre válido.',
            'callbackValue' => 'Debe ingresar un máximo de ' .
            $config->avisopaso2->puestonumeropalabra . ' palabras.'
        );
        // @codingStandardsIgnoreEnd
        if(isset($variable[$x])) {
            return $variable[$x];
        } else {
            return 'Ingrese un texto valido';
        }
    }

    public function removeTipoPuesto()
    {
        $this->removeElement('id_puesto');
    }

    public function isValidFun_Resp( $data )
    {

        if($data['funciones'] == '' || $data['nombre_puesto'] == '') {

            $this->nombre_puesto->setRequired(true);
            $this->nombre_puesto->errMsg = $this->_mensajeRequired;

            $this->funciones->setRequired(true);
            $this->funciones->errMsg = $this->_mensajeRequired;

            //$this->responsabilidades->setRequired(true);
            //$this->responsabilidades->errMsg = $this->_mensajeRequired;

            return parent::isValid($data);
        } else {
            return true;
        }
    }

    public static $errorsEmail = array(
        'isEmpty' => 'Campo Requerido',
        'emailAddressInvalidFormat' => 'No parece ser un correo electrónico valido',
        'callbackValue' => 'El email ya se encuentra registrado'
    );

}

//modificado