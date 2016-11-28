<?php

/**
 * Description Registro rapido
 *
 * @author Postulante
 */
class Application_Form_CompraPerfilDestacado extends App_Form
{
    private $_id_potulante;
    private $_maxlengthEmail='38';
    private $_maxlengthPwsd='32';
    private $_minlengthPwsd='6';
    private $_maxlengthNombreRa = '75';
    private $_maxlengthTipoDocDni = '11';
    private $_maxlengthTelefono = 9;
    Protected $_listaPais;
    protected $_listaDepartamento;
    Protected $_listaDistrito;
    public static $valorDocumento;
    /**
     * @var Application_Form_RegistroRapidoPostulante
     */
    private $_empresaModelo;
    
    const MSJ_ERROR_TELEFONO = 'Ingrese un numero entre 7-9 digitos';
    
       public function __construct($iu =null)
    {
        parent::__construct();
        $this->_id_potulante =$iu;    
        
      
    }

    public function init()
    {
        parent::init();
        
        // tipo de recivo
        $e= new Zend_Form_Element_Radio('radioTipoDoc');
        $e //->setLabel('ioption')
           ->addMultiOptions(array(
        'boleta' => 'Boleta',
        'factura' => 'Factura'
      ))
            ->removeDecorator('Label'  )    
             ->removeDecorator('htmlTag')
        //    ->setSeparator('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;')
            ->setValue("boleta");
        $this->addElement($e);
         
        //nombre
        $txtApellidoNombre = new Zend_Form_Element_Text('txtApellidoNombre');
        $txtApellidoNombre->setRequired();        
        $this->addElement($txtApellidoNombre);
       
        // número de documento
        $fNDoc = new Zend_Form_Element_Text('txtDni');
        $fNDoc->setRequired();
        $fNDoc->addValidator(new Zend_Validate_NotEmpty(), true);
        $fNDoc->setAttrib('maxLength', $this->_maxlengthTipoDocDni);
//        $fNDocVal = new Zend_Validate_StringLength(
//                array('min' => $this->_maxlengthTipoDocDni,
//            'max' => $this->_maxlengthTipoDocDni,
//            'encoding' => $this->_config->resources->view->charset
//                )
//        );
        //$fNDoc->addValidator($fNDocVal);

        $f = "Application_Model_Postulante::validacionDocumento";
        $fNDocVal = new Zend_Validate_Callback(
                array('callback' => $f, 'options' => array('dni',  $this->_id_potulante))
        );
        $fNDoc->addValidator($fNDocVal);
        $this->addElement($fNDoc);    
        
        $e = new Zend_Form_Element_Hash('auth_token');
        $hash_blow_fish = crypt('l0gInAPtiTus', '$2a$07$'.md5(uniqid(rand(), true)).'$');
        $e->setValue($hash_blow_fish);        
        $e->setErrorMessages(array(
            'Identical' => 'Por favor vuelva ha intentar enviar los datos.'
        ));
        $this->addElement($e);

    }
    
    
    

    //Errores Estaticos

    public static $errors = array(
     'isEmpty' => 'Campo Requerido',
     'stringLengthInvalid' => 'Documento inválido',
     'stringLengthTooLong' => 'Documento inválido',
     'stringLengthTooShort' => 'El documento no tiene 8 caracteres',
     'stringLengthTooLong' => 'El documento no tiene 8 caracteres',
     'callbackInvalid' => 'El email ya se encuentra registrado',
     'callbackValue' => 'El email ya se encuentra registrado',
     'notSame'=>'Por favor vuelva a intentarlo',
     'missingToken'=>'Por favor vuelva a intentarlo',
     'notInArray'=>'No se encontro el registro',
     'emailAddressDotAtom'=>'No es un formato de correo valido',
     'emailAddressQuotedString'=>'No es un formato de correo valido',
     'notSame'=>'Por favor vuelva a intentarlo',
    'dateInvalidDate'=>'No es una fecha válida'
        
    );



}

