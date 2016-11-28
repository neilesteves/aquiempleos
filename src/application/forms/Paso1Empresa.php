<?php

class Application_Form_Paso1Empresa extends App_Form
{

    private $_idUsuario;
    protected $_listaRubro;
    protected $_listaDistrito;
    //Max
    private $_maxlengthNombreC = '56';
    private $_maxlengthNombreRa = '56';
    private $_maxlengthNombreRu = '75';
    private $_maxlengthNumRuc = '14';

    public function setIdUsuario($iu)
    {
        $this->_idUsuario = $iu;
    }

    public function getIdUsuario()
    {
        return $this->_idUsuario;
    }

    public function __construct($iu = null)
    {
        if ($iu) {
            $this->_idUsuario = $iu;
        }
        parent::__construct();
    }

    public function init()
    {
        parent::init();
        $this->setAction('/registro-empresa/');



        // Combo Rubro
        $this->_listaRubro = new Application_Model_Rubro();
        $valores = $this->_listaRubro->getRubros();
        $fRubro = new Zend_Form_Element_Select('rubro');
        $fRubro->setRequired();
        $fRubro->addMultiOption('0', 'Seleccione rubro o sector');
        $fRubro->addMultiOptions($valores);
        $fRubroVal = new Zend_Validate_InArray(array_keys($valores));
        $fRubro->addValidator($fRubroVal);
        //$fRubro->setValue(Application_Model_Ubigeo::PERU_UBIGEO_ID);
        $fRubro->errMsg = $this->_mensajeRequired;
        $this->addElement($fRubro);

        //Logotipo
        $fLogo = new Zend_Form_Element_File('logotipo');
        $fLogo->setDestination($this->_config->urls->app->elementsLogosRoot);
        $fLogo->addValidator(
                new Zend_Validate_File_Size(array(
            'max' => $this->_config->app->maxSizeLogo))
        );
        $fLogo->addValidator('Extension', false, 'jpg,jpeg,png');

        $fLogo->getValidator('Size')->setMessage('Tamaño de Imagen debe ser menor a 500kb');
        $fLogo->getValidator('Extension')
                ->setMessage('Seleccione un archivo con extensión .jpg,.jpeg,.png');
        $this->addElement($fLogo);

        // Combo País
        $ubigeo = new Application_Model_Ubigeo();
        $valores = $ubigeo->getPaisEmpresa();
        $fPais = new Zend_Form_Element_Select('pais_residencia');
        $fPais->setRequired();
        $fPais->addMultiOption('0', 'Seleccione país');
        $fPais->addMultiOptions($valores);
        $fPaisVal = new Zend_Validate_InArray(array_keys($valores));
        $fPais->addValidator($fPaisVal);
        $fPais->setValue(Application_Model_Ubigeo::PERU_UBIGEO_ID);
        $fPais->errMsg = $this->_mensajeRequired;
        $this->addElement($fPais);

        // Combo Departamento
        $valores = $ubigeo->getDepartamentos();
        $fDepart = new Zend_Form_Element_Select('id_departamento');
        $fDepart->addMultiOption('0', 'Seleccione Región');
        $fDepart->addMultiOptions($valores);
        // $fDepartVal = new Zend_Validate_InArray(array_keys($valores));
        // $fDepart->addValidator($fDepartVal);
        $fDepart->setValue(Application_Model_Ubigeo::LIMA_UBIGEO_ID);
        $fDepart->errMsg = $this->_mensajeRequired;
        $this->addElement($fDepart);

        //Combo provincia
        $valores = $ubigeo->getProvincias();

        $fProv = new Zend_Form_Element_Select('id_provincia');
        $fProv->addMultiOption('0', 'Seleccione Ciudad');
        $fProv->addMultiOptions($valores);
//        $fProvVal = new Zend_Validate_InArray(array_keys($valores));
//        $fProv->addValidator($fProvVal);
        $fProv->setValue(Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID);
        $fProv->errMsg = $this->_mensajeRequired;
        $this->addElement($fProv);

        //tipo doc
        $valorDoc = array(
            'CI' =>'CI',
            'RUT' => 'RUC'
        );
        $fTipoDoc = new Zend_Form_Element_Select('tipo_doc');        
        $fTipoDoc->addMultiOptions($valorDoc);

        if (isset($this->_idUsuario) && MODULE != 'admin') {
            $fTipoDoc->setAttrib("disabled", "disabled");
        }      
        $this->addElement($fTipoDoc);  

//        //Combo Distrito
//        $fDistri = new Zend_Form_Element_Select('id_distrito');
//        $fDistri->addMultiOption('0', 'Seleccione Distrito');
//        $fDistri->addMultiOptions($valores);
//        $fDistriVal = new Zend_Validate_InArray(array_keys($valores));
//        $fDistri->addValidator($fDistriVal);
        //$fDistri->setValue('0016');
//        $fDistri->errMsg = $this->_mensajeRequired;
//        $fDistri->clearValidators();
        //$this->addElement($fDistri);

        $fTokenEmpresa = new Zend_Form_Element_Hidden('tok_emp_paso1');
        $tok = crypt(date('dmYH'), '$2a$07$' . md5(uniqid(rand(), true)) . '$');
        $fTokenEmpresa->setValue($tok);
        $this->addElement($fTokenEmpresa);
    }

    public function validadorRuc($id)
    {
        //Ruc
        $fNRuc = new Zend_Form_Element_Text('num_ruc');
        $fNRuc->setRequired();
        $fNRuc->setAttrib('maxLength', $this->_maxlengthNumRuc);
        $fNRuc->addValidator(new Zend_Validate_NotEmpty(), true);
        // $fNRuc->addValidator(new App_Validate_Ruc());

        $f = "Application_Model_Empresa::validacionRuc";
        $fNRucVal = new Zend_Validate_Callback(
                array(
            'callback' => $f,
            'options' => array(
                $id))
        );
        if (isset($this->_idUsuario) && MODULE != 'admin') {
            $fNRuc->setAttrib("readonly", "readonly");
        }
        $fNRuc->addValidator($fNRucVal);
        $this->addElement($fNRuc);
    }

    public function validadorNombreComercial($id)
    {
        //Nombre Comercial
        $fNombreComercial = new Zend_Form_Element_Text('nombrecomercial');
        $fNombreComercial->setRequired();
        $fNombreComercial->addValidator('NotEmpty', false, array(
            'messages' => 'Debe ingresar un Nombre Comercial.'));
        $fNombreComercial->setAttrib('maxLength', $this->_config->empresa->maxlength->razoncomercial);
        $fNombreComercial->addValidator(
                new Zend_Validate_StringLength(
                array(
            'min' => '1',
            'max' => $this->_config->empresa->maxlength->razoncomercial,
            'encoding' => $this->_config->resources->view->charset)
                )
        );
        $f = "Application_Model_Empresa::validacionCampoRepetido";
        $fNombreComercialVal = new Zend_Validate_Callback(
                array(
            'callback' => $f,
            'options' => array(
                $id,
                'nombre_comercial'))
        );
        $fNombreComercialVal->setMessage("Razon Social ya registrada.");
        $fNombreComercial->addValidator($fNombreComercialVal);
        $this->addElement($fNombreComercial);
    }

    public function validadorRazonSocial($id)
    {
        //Razon Social
        $fRazonSocial = new Zend_Form_Element_Text('razonsocial');
        $fRazonSocial->setRequired();
        $fRazonSocial->addValidator('NotEmpty', false, array(
            'messages' => 'Debe ingresar una Razon Social.'));
        $fRazonSocial->setAttrib('maxLength', $this->_config->empresa->maxlength->razonsocial);
        $fRazonSocial->addValidator(
                new Zend_Validate_StringLength(
                array(
            'min' => '1',
            'max' => $this->_config->empresa->maxlength->razonsocial,
            'encoding' => $this->_config->resources->view->charset)
                )
        );

        $f = "Application_Model_Empresa::validacionCampoRepetido";
        $fRazonSocialVal = new Zend_Validate_Callback(
                array(
            'callback' => $f,
            'options' => array(
                $id,
                'razon_social'))
        );

        $fRazonSocialVal->setMessage("Nombre Comercial ya registrada.");
        $fRazonSocial->addValidator($fRazonSocialVal);
        if (isset($this->_idUsuario) && MODULE != 'admin') {
            $fRazonSocial->setAttrib("readonly", "readonly");
        }
        $this->addElement($fRazonSocial);
    }

    public function isValid($data)
    {

        //validacion razon social
        //$fAlpha = new Zend_Validate_Alnum(true);
        // /^[\w.-]*$/
        $fAlpha = new Zend_Validate_Regex("/^[0-9-a-zA-Z-[:space:]-ñ-áéíóúAÉÍÓÚÑñ.]*$/");
        $fAlpha->setMessage('Debe ingresar solo caracteres y/o números');
        //$this->getElement('razonsocial')->addValidator($fAlpha);
        $f = "App_Controller_Action_Helper_Contador::contadorPalabraText";
        $fval = new Zend_Validate_Callback(
                array(
            'callback' => $f,
            'options' => array(
                $data['razonsocial'],
                $this->_config->empresa->numeroPalabra->razonsocial)
                )
        );
        $fval->setMessage("Debe ingresar máximo " . $this->_config->empresa->numeroPalabra->razonsocial . " palabras.");
        $this->getElement('razonsocial')->addValidator($fval);


        //validacion razon comercial
        //$fAlpha = new Zend_Validate_Alnum(true);
        $fAlpha = new Zend_Validate_Regex("/^[0-9-a-zA-Z-[:space:]-ñ-áéíóúAÉÍÓÚÑñ.]*$/");

        $fAlpha->setMessage('Debe ingresar solo caracteres y/o números');
        //$this->getElement('razonsocial')->addValidator($fAlpha);
        //$this->getElement('nombrecomercial')->addValidator($fAlpha);
        $f = "App_Controller_Action_Helper_Contador::contadorPalabraText";
        $fval = new Zend_Validate_Callback(
                array(
            'callback' => $f,
            'options' => array(
                $data['nombrecomercial'],
                $this->_config->empresa->numeroPalabra->razoncomercial
            )
                )
        );
        $fval->setMessage("Debe ingresar máximo " . $this->_config->empresa->numeroPalabra->razoncomercial . " palabras.");
        $this->getElement('nombrecomercial')->addValidator($fval);


        //validacion Ubigeo
        $distritos = new Application_Model_Ubigeo();

        /*   if (isset($data['id_provincia']) &&
          trim($data['id_provincia']) == Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID) {
          $this->_listaDistrito = $distritos->getHijos(Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID);

          // @codingStandardsIgnoreStart
          $this->id_distrito->addValidator(
          new Zend_Validate_InArray(array_keys($this->_listaDistrito))
          );
          // @codingStandardsIgnoreEnd
          } */
        /*
          if (isset($data['id_provincia']) &&
          trim($data['id_provincia']) == Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID) {
          $this->_listaDistrito = $distritos->getHijos(Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID);

          // @codingStandardsIgnoreStart
          $this->id_distrito->addValidator(
          new Zend_Validate_InArray(array_keys($this->_listaDistrito))
          );
          // @codingStandardsIgnoreEnd
          }
         */
        $resultDatadepartamento = $distritos->getHijos($data['pais_residencia']);
        $resultData = $distritos->getHijos($data['id_departamento']);
//        $this->id_departamento->addMultiOptions($resultDatadepartamento);
//
//        $this->id_departamento->addValidator(
//                new Zend_Validate_InArray(array_keys($resultDatadepartamento))
//        );
        //var_dump($resultData);


        $this->id_provincia->addValidator(
                new Zend_Validate_InArray(array_keys($resultData))
        );
        $this->id_provincia->addMultiOptions($resultData);
//         var_dump($resultDatadepartamento,$resultData);EXIT;
        $this->id_provincia->clearValidators();
        $this->id_provincia->setRequired(false);
        return parent::isValid($data);
    }

    public function setreadonlyEmpresa()
    {
        
    }

    public static $errorsRuc = array(
        'callbackValue' => 'Ruc Registrado',
        'invalidruc' => 'Debe ingresar un Ruc válido.',
        'isEmpty' => 'Debe ingresar Ruc de 11 Digitos.',
        'not11digits' => 'Debe ingresar Ruc de 11 Digitos.'
    );

}
