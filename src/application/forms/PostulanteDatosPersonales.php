<?php

/**
 * Form para datos personales de postulante
 *
 * @author rmontoya
 */
class Application_Form_PostulanteDatosPersonales extends App_Form {

    private $_minlengthNombre = '2';
    private $_maxlengthNombre = '75';
    private $_maxlengthApellidoPaterno = '28';
    private $_maxlengthApellidoMaterno = '28';
    private $_maxlengthTipoDocDni = '14';
    private $_minlengthTipoDocCe = '6';
    private $_maxlengthTipoDocCe = '14';
    private $_minlengthTelefono = '6';
    private $_maxlengthTelefono = '12';
    private $_minlengthCelular = '8';
    private $_maxlengthCelular = '12';
    private $_id;
    protected $_Tipo_Discapacidad;
    protected $_conadis;
    public function setId($i) {
        $this->_id = $i;
    }

    public function getId() {
        return $this->_id;
    }

    public function __construct($i,$conadis) {
        if(isset($conadis) && count($conadis)>0){
            $this->_conadis = $conadis;
        }
        $this->_id = $i;
        parent::__construct();

     
                   

       
    }

    public function init() {
        parent::init();

        //nombres
        $fNames = new Zend_Form_Element_Text('nombres');
        $fNames->setAttrib('maxLength', $this->_maxlengthNombre);
        $fNames->addValidator(
                new Zend_Validate_StringLength(
                array('min' => $this->_minlengthNombre, 'max' => $this->_maxlengthNombre,
            'encoding' => $this->_config->resources->view->charset)
                )
        );
        $fNamesVal = new Zend_Validate_NotEmpty();
        $fNames->addValidator($fNamesVal);
        $fNames->errMsg = 'Se requiere su nombre';
        $this->addElement($fNames);

        //apellido paterno
        $fLastnameP = new Zend_Form_Element_Text('txtFirstLastName');
        $fLastnameP->setRequired();
        $fLastnameP->setAttrib('maxLength', $this->_maxlengthApellidoPaterno);
        $fLastnameP->addValidator(
                new Zend_Validate_StringLength(
                array('min' => $this->_minlengthNombre, 'max' => $this->_maxlengthApellidoPaterno,
            'encoding' => $this->_config->resources->view->charset)
                )
        );
        $fLastnamePVal = new Zend_Validate_NotEmpty();
        $fLastnameP->addValidator($fLastnamePVal);
        $fLastnameP->errMsg = 'Se requiere su apellido paterno';
        $this->addElement($fLastnameP);

        //apellido materno
        $fLastnameM = new Zend_Form_Element_Text('txtSecondLastName');
        $fLastnameM->setRequired();
        $fLastnameM->setAttrib('maxLength', $this->_maxlengthApellidoMaterno);
        $fLastnameM->addValidator(
                new Zend_Validate_StringLength(
                array('min' => $this->_minlengthNombre, 'max' => $this->_maxlengthApellidoMaterno,
                    'encoding' => $this->_config->resources->view->charset)
                )
        );
        $fLastnameMVal = new Zend_Validate_NotEmpty();
        $fLastnameM->addValidator($fLastnameMVal);
        $fLastnameM->errMsg = 'Se requiere su apellido materno';
        $this->addElement($fLastnameM);

        //foto
        $fPhoto = new Zend_Form_Element_File('path_foto');
        $fPhoto->setRequired(false);
        $fPhoto->setDestination($this->_config->urls->app->elementsImgRoot);
        $fPhoto->addValidator(
            new Zend_Validate_File_Size(array('max' => $this->_config->app->maxSizeFile))
        );
        $fPhoto->addValidator('Extension', false, 'jpg,jpeg,png');
        $this->addElement($fPhoto);
        
        //fecha nacimiento
        $fBirthDate = new Zend_Form_Element_Text('txtBirthDay');
        $fBirthDate->setRequired();
        $fBirthDateVal = new Zend_Validate_NotEmpty();
        $fBirthDate->addValidator($fBirthDateVal, true);
        $fBirthDate->addValidator(new Zend_Validate_Date('DD/MM/YYYY'));
        $this->addElement($fBirthDate);       

        //tipo documento
        $fTipoDoc = new Zend_Form_Element_Select('selDocument');
        $fTipoDoc->addMultiOption('dni', $this->_config->app->dni);
        $fTipoDoc->addMultiOption('ce', "Carné de extranjería");
        $this->addElement($fTipoDoc);
        
        //número de documento
        $fNumDoc = new Zend_Form_Element_Text('txtDocument');
        $fNumDoc->setAttrib('maxLength', 14);
        $fNumDoc->addValidator(
                new Zend_Validate_StringLength(
                array('min' => $this->_minlengthTipoDocCe, 'max' => $this->_maxlengthTipoDocCe,
            'encoding' => $this->_config->resources->view->charset)
                )
        );
        $this->addElement($fNumDoc);
        
        //sexo
        $fSexo = new Zend_Form_Element_Select('selSex');
        $fSexo->addMultiOption("M", "Masculino");
        $fSexo->addMultiOption("F", "Femenino");
        $this->addElement($fSexo);

        //teléfono fijo
        $fTelefono = new Zend_Form_Element_Text('txtTelephone');
        $fTelefono->addValidator(
            new Zend_Validate_StringLength(
                array('min' => $this->_minlengthTelefono, 'max' => $this->_maxlengthTelefono)
            )
        );
        $fTelefonoVal = new Zend_Validate_Digits();
        $fTelefono->addValidator($fTelefonoVal);
        $this->addElement($fTelefono);

        //celular
        $fCelular = new Zend_Form_Element_Text('txtCellphone');
        $fCelular->addValidator(
            new Zend_Validate_StringLength(
                array('min' => $this->_minlengthCelular, 'max' => $this->_maxlengthCelular)
            )
        );
        $fCelularVal = new Zend_Validate_Digits();
        $fCelular->addValidator($fCelularVal);
        $this->addElement($fCelular);

        //estado civil
        $fEstCvil = new Zend_Form_Element_Select('selMAritalStatus');
        $fEstCvil->addMultiOptions(Application_Model_Postulante::$estadoCivil);
        $fEstCvilVal = new Zend_Validate_InArray(
                array_keys(Application_Model_Postulante::$estadoCivil)
        );
        $fEstCvil->addValidator($fEstCvilVal);
        $this->addElement($fEstCvil);
        
        
        //conadis_code
        $fconadis_code = new Zend_Form_Element_Text('txtconadisCode');
        $fconadis_code->setAttrib('maxLength', 12);
        $fconadis_code->addValidator(
                new Zend_Validate_StringLength(
                array('min' => $this->_minlengthNombre, 'max' => $this->_maxlengthNombre,
            'encoding' => $this->_config->resources->view->charset)
                )
        );
          $fconadis_code->errMsg = 'Se requiere su codigo';
        $this->addElement($fconadis_code);
           //token
        $e = new Zend_Form_Element_Checkbox('chkConadis');       
        $this->addElement($e);
        
                //select 
        $selDisability = new Zend_Form_Element_Select('selDisability');
        $selDisability->addMultiOption('', "-- Seleccione --");
        $selDisability->addMultiOptions(Application_Model_Postulante::$tipoDiscapacidad);
        $this->addElement($selDisability);
           //incapacidad conades
        $e = new Zend_Form_Element_Checkbox('chkIncapacity');       
        $this->addElement($e);
        
        
        
      
        //token
        $e = new Zend_Form_Element_Hash('hidToken');       
        $this->addElement($e);
        
        //botón
        $fButton = new Zend_Form_Element_Button('button');
        $fButton->setLabel("Guardar");
        $this->addElement($fButton);
        if(isset($this->_conadis) && empty($this->_conadis['conadis_code']) ){
           $this->txtconadisCode->setAttrib("disabled", "disabled");
        }
        if(isset($this->_conadis) && empty($this->_conadis['discapacidad']) ){
           $this->selDisability->setAttrib("disabled", "disabled");
        }

                    
    }

    public function isValid($data) {
        //validando documento de identidad según el tipo
        // $form->removeElement('txtconadisCode');
        
        $this->txtconadisCode->clearValidators();
        $this->txtconadisCode->setRequired(false);
        
        $this->chkConadis->clearValidators();
        $this->chkConadis->setRequired(false);

        $this->chkIncapacity->clearValidators();
        $this->chkIncapacity->setRequired(false);
        if ($data['selDocument'] == 'dni') {
            $fNumDocVal = new Zend_Validate_Digits();
//            $fNumDoc->addValidator($fNumDocVal);
            #$this->txtDocument->addValidator($fNumDocVal);
            $this->txtDocument->addValidator(
                    new Zend_Validate_StringLength(
                        array('min' => $this->_maxlengthTipoDocDni,
                            'max' => $this->_maxlengthTipoDocDni,
                            'encoding' => $this->_config->resources->view->charset
                        )
                    )
            );
        } elseif ($data['selDocument'] == 'ce') { 
            $this->txtDocument->addValidator(
                    new Zend_Validate_StringLength(
                        array('min' => $this->_minlengthTipoDocCe,
                            'max' => $this->_maxlengthTipoDocCe,
                            'encoding' => $this->_config->resources->view->charset
                        )
                    )
            );
        }
        
        //validando que postulante tenga edad>=18
        $fval = new Zend_Validate_Callback(
                array('callback' => array(
                    'App_Controller_Action_Helper_Util','esMayorDeEdad'), 
                    'options' => $data['txtBirthDay'],
                )
        );
        $fval->setMessage('Campo fecha de nacimiento inválida');
        $this->getElement('txtBirthDay')->addValidator($fval);

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
        'notInArray' => 'El nivel seleccionado no es valido, por favor vuelva ha intentarlo',
        'fileExtensionFalse' => 'El formato de imagen es inválido. Sólo se permite jpg, jpeg, gif, png',
        'fileUploadErrorIniSize' => 'El tamaño de la imagen excede el tamaño permitido'
    );
    
    public static $errorsTxt = array(
        'stringLengthInvalid' => 'Longitud inválida',
        'stringLengthTooLong' => 'Excede número de letras',
        'stringLengthTooShort' => 'No tiene el mínimo de letras requerido',
    );
}
