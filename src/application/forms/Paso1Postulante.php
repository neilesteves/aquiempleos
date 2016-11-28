<?php

/**
 * Description of Categoria
 *
 * @author Usuario
 */
class Application_Form_Paso1Postulante extends App_Form {

    //Max Length
    private $_maxlengthNombre = '75';
    private $_maxlengthApellido = '28';
    private $_maxlengthTelefono = '9';
    private $_maxlengthCelular = '12';
    private $_maxlengthTipoDocDni = '8';
    private $_minlengthTipoDocCe = '1';
    private $_maxlengthTipoDocCe = '12';
    private $_maxlengthPresentacion = '750';
    //listas
    Protected $_listaPais;
    protected $_listaDepartamento;
    Protected $_listaDistrito;
    //@codingStandardsIgnoreStart
    public static $_defaultWebsite = '';
    public static $_defaultPresentacion = '';
    public static $valorDocumento;
    //@codingStandardsIgnoreEnd
    private $_id;

    public function setId($i) {
        $this->_id = $i;
    }

    public function getId() {
        return $this->_id;
    }

    public function __construct($i) {
        parent::__construct();
        $this->_id = $i;

        $keyDni = 'dni#' . $this->_maxlengthTipoDocDni;
        $keyCe = 'ce#' . $this->_maxlengthTipoDocCe;

        self::$valorDocumento = array(
            $keyDni => 'DNI',
            $keyCe => 'Carné Extranjería'
        );
    }

    public function init() {
        parent::init();

        // Foto
        $fPhoto = new Zend_Form_Element_File('path_foto');
        //
        $fPhoto->setDestination($this->_config->urls->app->elementsImgRoot);
        $fPhoto->addValidator(
                new Zend_Validate_File_Size(array('max' => $this->_config->app->maxSizeFile))
        );
        $fPhoto->addValidator('Extension', false, 'jpg,jpeg,png,gif');
        $this->addElement($fPhoto);

        // Nombre
        $fNames = new Zend_Form_Element_Text('nombres');
        $fNames->setAttrib('maxLength', $this->_maxlengthNombre);
        $fNames->addValidator(
                new Zend_Validate_StringLength(
             array('min' => '2', 'max' => $this->_maxlengthNombre,
            'encoding' => $this->_config->resources->view->charset)
                )
        );
        $fNames->setRequired();
        //
        $fNamesVal = new Zend_Validate_NotEmpty();
        $fNames->addValidator($fNamesVal);
        $fNames->errMsg = '¡Se requiere su nombre!';
        $this->addElement($fNames);

        /** /
        // Apellido segun lo conversado solo se tomara apellido
        $fSurname = new Zend_Form_Element_Text('apellidos');
        $fSurname->setRequired();
        //
        $fSurname->setAttrib('maxLength', $this->_maxlengthNombre);
        $fSurname->addValidator(
                new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthNombre,
            'encoding' => $this->_config->resources->view->charset)
                )
        );
        $fSurnameVal = new Zend_Validate_NotEmpty();
        $fSurname->addValidator($fSurnameVal);
        $fSurname->errMsg = '¡Se requiere su apellido!';
        $this->addElement($fSurname);
          /* */
        // Apellido segun lo conversado solo se tomara apellido paterno y materno
        $fLastnameP = new Zend_Form_Element_Text('apellido_paterno');
        $fLastnameP->setRequired();
        //
        $fLastnameP->setAttrib('maxLength', $this->_maxlengthApellido);
        $fLastnameP->addValidator(
                new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthApellido,
            'encoding' => $this->_config->resources->view->charset)
                )
        );
        $fLastnamePVal = new Zend_Validate_NotEmpty();
        $fLastnameP->addValidator($fLastnamePVal);
        $fLastnameP->errMsg = '¡Se requiere su apellido!';
        $this->addElement($fLastnameP);

        $fLastnameM = new Zend_Form_Element_Text('apellido_materno');
        $fLastnameM->setRequired();
        //
        $fLastnameM->setAttrib('maxLength', $this->_maxlengthApellido);
        $fLastnameM->addValidator(
                new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthApellido,
            'encoding' => $this->_config->resources->view->charset)
                )
        );
        $fLastnameMVal = new Zend_Validate_NotEmpty();
        $fLastnameM->addValidator($fLastnameMVal);
        $fLastnameM->errMsg = '¡Se requiere su apellido!';
        $this->addElement($fLastnameM);

        // Fecha
        $fBirthDate = new Zend_Form_Element_Hidden('fecha_nac');
        $fBirthDate->setRequired();
        //
        $fBirthDateVal = new Zend_Validate_NotEmpty();
        $fBirthDate->addValidator(new Zend_Validate_Date('DD/MM/YYYY'));
        $fBirthDate->addValidator($fBirthDateVal, true);
        $validador = new Zend_Validate_Callback(
                function($date) {
            $now = new Zend_Date();
            $bd = new Zend_Date($date);
            return $bd->isEarlier($now);
        }
        );
        $fBirthDate->addValidator($validador, true);
        $this->addElement($fBirthDate);

        // Sexo
        $fSexo = new Zend_Form_Element_Radio('sexoMF');
        $fSexo->addMultiOption("M", "Masculino");
        $fSexo->addMultiOption("F", "Femenino");
        $fSexo->setValue("M");

        //
        $fSexoVal = new Zend_Validate_NotEmpty();
        $fSexo->addValidator($fSexoVal);
        $fSexo->errMsg = $this->_mensajeRequired;
        $fSexo->setSeparator('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        $this->addElement($fSexo);

        // Telefono Fijo/Cel
        $fTlfFC = new Zend_Form_Element_Text('telefono');
        //
        $fTlfFC->setAttrib('maxLength', $this->_maxlengthTelefono);
        $fTlfFC->addValidator(
                new Zend_Validate_StringLength(
                array('min' => '0', 'max' => $this->_maxlengthTelefono,
            'encoding' => $this->_config->resources->view->charset)
                )
        );
        $fTlfFCVal = new Zend_Validate_NotEmpty();
        $fTlfFC->addValidator($fTlfFCVal);
        $this->addElement($fTlfFC);

        // Telefono Fijo/Cel 2
        $fTlfFC = new Zend_Form_Element_Text('celular');
        $fTlfFC->setAttrib('maxLength', $this->_maxlengthTelefono);
        $fTlfFC->addValidator(
                new Zend_Validate_StringLength(
                array('min' => '0', 'max' => $this->_maxlengthCelular,
            'encoding' => $this->_config->resources->view->charset)
                )
        );
        $this->addElement($fTlfFC);

        // Combo Estado Civil
        $fEstCvil = new Zend_Form_Element_Select('estado_civil');
        $fEstCvil->setRequired();
        $fEstCvil->addMultiOptions(Application_Model_Postulante::$estadoCivil);
        $fEstCvilVal = new Zend_Validate_InArray(
                array_keys(Application_Model_Postulante::$estadoCivil)
        );
        $fEstCvil->addValidator($fEstCvilVal);
        $fEstCvil->errMsg = $this->_mensajeRequired;
        $this->addElement($fEstCvil);

        // Combo País
        $this->_listaPais = new Application_Model_Ubigeo();
        $valores = $this->_listaPais->getPaises();
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
        $this->_listaDepartamento = new Application_Model_Ubigeo();
        $valores = $this->_listaDepartamento->getDepartamentos();
        $fDepart = new Zend_Form_Element_Select('id_departamento');
        $fDepart->addMultiOption('0', 'Seleccione Departamento');
        $fDepart->addMultiOptions($valores);
        $fDepartVal = new Zend_Validate_InArray(array_keys($valores));
        $fDepart->addValidator($fDepartVal);
        $fDepart->setValue(Application_Model_Ubigeo::LIMA_UBIGEO_ID);
        $fDepart->errMsg = $this->_mensajeRequired;
        $this->addElement($fDepart);

        //provincia
        $valores = $this->_listaDepartamento->getProvincias();
        $fProv = new Zend_Form_Element_Select('id_provincia');
        $fProv->addMultiOption('0', 'Seleccione Provincia');
        $fProv->addMultiOptions($valores);
        $fProvVal = new Zend_Validate_InArray(array_keys($valores));
        $fProv->addValidator($fProvVal);
        $fProv->setValue(Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID);
        $fProv->errMsg = $this->_mensajeRequired;
        $this->addElement($fProv);

        // Combo Distrito
        $fDistri = new Zend_Form_Element_Select('id_distrito');
        $fDistri->addMultiOption('0', 'Seleccione Distrito');
        $fDistri->errMsg = $this->_mensajeRequired;
        $fDistri->clearValidators();
        $this->addElement($fDistri);

        // Checkbox Disponible provincia/extranjero
        $fProvExtra = new Zend_Form_Element_Checkbox('disponibilidad_provincia_extranjero');
        $fProvExtra->clearValidators();
        $this->addElement($fProvExtra);

        //Sitio Web
        $fUrlST = new Zend_Form_Element_Text('website'); 
        $fUrlST->setValue(self::$_defaultWebsite);
        
        //$uri->addValidator(new Zend_Validate_URI());
        
        $fUrlST->addValidator(
                new App_Validate_Uri()
        );
        $fUrlST->errMsg = 'Sitio web inválido.';
        $this->addElement($fUrlST);

        // TexTArea Presentación
        $presentMC = new Zend_Form_Element_Textarea('presentacion');
        $presentMC->setValue(self::$_defaultPresentacion);
        $presentMC->addValidator(
                new Zend_Validate_StringLength(
                array('min' => '0', 'max' => $this->_maxlengthPresentacion,
            'encoding' => $this->_config->resources->view->charset)
                )
        );
        $presentMC->errMsg = 'Ingrese máximo 750 caracteres';
        $this->addElement($presentMC);

        $this->addElement('submit', 'sbmt');
    }

    public function isValid($data) {
        $distritos = new Application_Model_Ubigeo();
        if (isset($data['id_provincia']) &&
                trim($data['id_provincia']) == Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID) {
            $this->_listaDistrito = $distritos->getHijos(Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID);

            // @codingStandardsIgnoreStart
            $this->id_distrito->addValidator(
                    new Zend_Validate_InArray(array_keys($this->_listaDistrito))
            );
            // @codingStandardsIgnoreEnd
        }
        if (isset($data['id_provincia']) &&
                trim($data['id_provincia']) == Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID) {
            $this->_listaDistrito = $distritos->getHijos(Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID);

            // @codingStandardsIgnoreStart
            $this->id_distrito->addValidator(
                    new Zend_Validate_InArray(array_keys($this->_listaDistrito))
            );
            // @codingStandardsIgnoreEnd
        }

        if ($data['tipo_doc'] == 'dni#' . $this->_maxlengthTipoDocDni) {
            // @codingStandardsIgnoreStart
            $this->num_doc->addValidator(
                    new Zend_Validate_StringLength(
                    array('min' => $this->_maxlengthTipoDocDni,
                'max' => $this->_maxlengthTipoDocDni,
                'encoding' => $this->_config->resources->view->charset
                    )
                    )
            );
            // @codingStandardsIgnoreEnd
        } elseif ($data['tipo_doc'] == 'ce#' . $this->_maxlengthTipoDocCe) {
            // @codingStandardsIgnoreStart
            $this->num_doc->addValidator(
                    new Zend_Validate_StringLength(
                    array('min' => $this->_minlengthTipoDocCe,
                'max' => $this->_maxlengthTipoDocCe,
                'encoding' => $this->_config->resources->view->charset
                    )
                    )
            );
            // @codingStandardsIgnoreEnd
        }
        return parent::isValid($data);
    }

    public function validadorNumDoc($id) {

        // Combo Documento
        $fSelDoc = new Zend_Form_Element_Select('tipo_doc');
        $fSelDoc->setRequired();
        $fSelDoc->addMultiOptions(self::$valorDocumento);
        $fSelDocVal = new Zend_Validate_InArray(array_keys(self::$valorDocumento));
        $fSelDoc->addValidator($fSelDocVal);
        $this->addElement($fSelDoc);

        $fNDoc = new Zend_Form_Element_Text('num_doc');
        $fNDoc->setRequired();

        $fNDoc->addValidator(new Zend_Validate_NotEmpty(), true);
        $fNDoc->setAttrib('maxLength', $this->_maxlengthTipoDocDni);
        $fNDocVal = new Zend_Validate_StringLength(
                array('min' => $this->_maxlengthTipoDocDni,
            'max' => $this->_maxlengthTipoDocDni,
            'encoding' => $this->_config->resources->view->charset
                )
        );
        $fNDoc->addValidator($fNDocVal);

        $f = "Application_Model_Postulante::validacionDocumento";
        $fNDocVal = new Zend_Validate_Callback(
                array('callback' => $f, 'options' => array($fSelDoc, $id))
        );
        $fNDoc->addValidator($fNDocVal);


        $this->addElement($fNDoc);
    }

    //Errores Estaticos
    public static $errors = array(
        'isEmpty' => 'Campo Requerido',
        'stringLengthTooShort' => 'Documento inválido',
        'stringLengthTooLong' => 'Documento inválido',
        'callbackValue' => 'El Número del documento ya se encuentra registrado'
    );
    public static $errorsPhoto = array(
        'fileExtensionFalse' => 'Archivo debe tener extensiones .jpg| .png| .jpeg| .gif',
        'fileSizeTooBig' => 'Tamaño de archivo sobrepasa el limite Permitido.',
    );
    public static $errorsFechaNac = array(
        'callbackValue' => 'Ingrese una fecha válida.',
        'isEmpty' => 'Campo Requerido.',
        'dateFalseFormat' => 'Ingrese una fecha válida.',
        'dateInvalidDate' => 'Ingrese una fecha válida.',
        'callbackInvalid' => 'Ingrese una fecha válida.'
    );

}
