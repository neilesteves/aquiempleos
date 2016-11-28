<?php

class Application_Form_Paso2ReferenciaNew extends App_Form
{
    private $_maxlengthNombre = '75';
    private $_maxlengthCargo = '70';
    private $_maxlengthTelefono = '70';
    private $_maxlengthtxtNameReference = '70';
    
    protected $_MensajeReferenciaStringLength = 'El campo debe tener más de 70 caracteres';
    protected $_idPostulante;
    
    public function __construct($idPostulante, $hasHiddenId = false  )
    {
        $this->_idPostulante = $idPostulante;
        
        parent::__construct();
        if ($hasHiddenId) {
            $this->addReferenciaId();
        }
    }
    
    public function init()
    {
        parent::init();
        $this->setMethod('post');

        //
        $experiencia = new Application_Model_Experiencia();
        $listaExperiencia = $experiencia->getExperienciaMiCuenta($this->_idPostulante);
        $e = new Zend_Form_Element_Select('selCareReference');
        $e->setRequired();
        $v = new Zend_Validate_InArray(array_keys($listaExperiencia));
        $e->addValidator($v);
        $e->errMsg = $this->_mensajeRequired;
        $e->addMultiOptions($listaExperiencia);
        
        $this->addElement($e);
        
        //Nombre
        $fSurname = new Zend_Form_Element_Text('txtNameReference');
        $fSurname->setRequired();
        $fSurname->setAttrib('maxLength', $this->_maxlengthtxtNameReference);
        $fSurnameValStrin=new Zend_Validate_StringLength(  
                array('min' => '0', 'max' => $this->_maxlengthtxtNameReference,
                'encoding' => $this->_config->resources->view->charset)
            );
        $fSurnameValStrin->getMessages($this->_MensajeReferenciaStringLength);
        $fSurname->addValidator($fSurnameValStrin);
        $fSurnameVal = new Zend_Validate_NotEmpty();
        $fSurnameVal->setMessage("Ingrese un nombre válido.");
        $fSurname->addValidator($fSurnameVal);
        $this->addElement($fSurname);
        
        //puesto o cargo
        $fcargo = new Zend_Form_Element_Text('txtPositionReference');
        $fcargo->setRequired();
        $fcargo->setAttrib('maxLength', $this->_maxlengthCargo);
        $fcargoValStrin=new Zend_Validate_StringLength(  
                array('min' => '0', 'max' => $this->_maxlengthCargo,
                'encoding' => $this->_config->resources->view->charset)
            );
        $fcargoValStrin->setMessage($this->_MensajeReferenciaStringLength);
        $fcargo->addValidator($fcargoValStrin);
        $fcargo->errMsg = 'Ingrese un cargo válido';
        $this->addElement($fcargo);
        
        // Telefono
        $fTlfFC = new Zend_Form_Element_Text('txtTelephoneReferenceOne');
        $fTlfFC->setRequired();
        $fTlfFC->setAttrib('minLength', 6);
        $fTlfFC->setAttrib('maxLength', $this->_maxlengthTelefono);
        $fTlfFCValStrin=new Zend_Validate_StringLength(  
                array('min' => '0', 'max' => $this->_maxlengthTelefono,
                'encoding' => $this->_config->resources->view->charset)
            );
        $fTlfFCValStrin->setMessage($this->_MensajeReferenciaStringLength);
        $fTlfFC->addValidator($fTlfFCValStrin);
        
        $fTlfFCVal = new Zend_Validate_NotEmpty();
        $fTlfFCVal->setMessage($this->_mensajeRequired);
        $fTlfFC->addValidator($fTlfFCVal);
        $this->addElement($fTlfFC);
        
        // Telefono 2
        $fTlf = new Zend_Form_Element_Text('txtTelephoneReferenceTwo');
        $fTlf->setAttrib('minLength', 6);
        $fTlf->setAttrib('maxLength', $this->_maxlengthTelefono);
        $fTlfValStrin=new Zend_Validate_StringLength(  
                array('min' => '0', 'max' => $this->_maxlengthTelefono,
                'encoding' => $this->_config->resources->view->charset)
            );
        $fTlfValStrin->setMessage($this->_MensajeReferenciaStringLength);
        $fTlf->addValidator($fTlfValStrin);
         
        $this->addElement($fTlf);

        //email
        $fEmail = new Zend_Form_Element_Text('txtTelephoneReferenceEmail');
        $fEmailVal = new Zend_Validate_EmailAddress();
        $fEmail->setAttrib('minLength', 6);
        $fEmail->setAttrib('maxLength', 70);
        $fEmailVal->setMessage("No parece ser un correo electrónico valido");
        $fEmail->addValidator($fEmailVal, true);
        $this->addElement($fEmail);
        
        
        $e = new Zend_Form_Element_Hash('hidToken');    
        $e->setTimeout(3600);
        $this->addElement($e);
    }
    
    public function addReferenciaId()
    {
        $e = new Zend_Form_Element_Hidden('id_referencia');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');       
        $e->setAttrib('class', 'hidden_id');
        $this->addElement($e);
    }
    /**
     * Funcion que crea el una etiqueta hidden para el id referencia
     * @param int $id valor del hidden
     * @return type Description
     */
    public function setHiddenId($id)
    {
        $e = $this->getElement('id_referencia');
        $e->setAttrib('class', 'hidden_id');
        $e->setValue($id);
    }
    /**
     * Funcion que retorna errores personalizados
     * @param array $formRefencia Errores del propio zend
     * @return String personalizando errores
     */
    public function setErrosReferencias($formRefencia)
    {
        foreach ($formRefencia as $key => $value) {
            foreach ($value as $k => $v) {
                return $v;
            }
            
        }
        
    }
    public static $errors = array
        (
        'isEmpty' => 'Campo Requerido',
        'stringLengthInvalid' => 'Documento inválido',
        'stringLengthTooShort' => 'El documento no tiene 8 caracteres',
        'stringLengthTooLong' => 'El documento no tiene 8 caracteres',
        'callbackInvalid' => 'El Número del documento ya se encuentra registrado',
        'callbackValue' => 'El Número del documento ya se encuentra registrado',
        'notSame'=>'Por favor vuelva a intentarlo',
        'missingToken'=>'Por favor vuelva a intentarlo',
        'notInArray'=>'No se encontro el registro'
        );
}