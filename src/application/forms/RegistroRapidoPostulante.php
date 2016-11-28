<?php

/**
 * Description Registro rapido
 *
 * @author Postulante
 */
class Application_Form_RegistroRapidoPostulante extends App_Form
{
    private $_idUsuario;
    private $_maxlengthEmail='38';
    private $_maxlengthPwsd='32';
    private $_minlengthPwsd='6';
    private $_maxlengthNombreRa = '75';
    private $_maxlengthNumRuc = '11';
    private $_maxlengthTelefono = 9;
    Protected $_listaPais;
    protected $_listaDepartamento;
    Protected $_listaDistrito;
    public static $valorDocumento;

    const MIN_EDAD = 18;

    /**
     * @var Application_Form_RegistroRapidoPostulante
     */
    private $_empresaModelo;
    
    const MSJ_ERROR_TELEFONO = 'Ingrese un numero entre 7-9 digitos';
    
    public function setIdUsuario($iu)
    {
        $this->_idUsuario = $iu;
    }
    
    public function getIdUsuario()
    {
        return $this->_idUsuario;
    }
    
    public function __construct($iu =null)
    {
        parent::__construct();
        $this->_idUsuario =$iu;
        $this->_empresaModelo = new Application_Model_Empresa();
        
        $keyDni = 'dni' . $this->_maxlengthTipoDocDni;
        $keyCe = 'ce' . $this->_maxlengthTipoDocCe;

        self::$valorDocumento = array(
            $keyDni => 'DNI',
            $keyCe => 'Carnet Extranjería'
        );
    }
    
    public function init()
    {
        parent::init();
        
        //nombre
        $txtName = new Zend_Form_Element_Text('txtName');
        $txtName->setRequired();
        
        $txtName->setAttrib('maxLength', 75);
        $txtName->setAttrib('minlength', 2);
        $valtxtNameStringlength=new Zend_Validate_StringLength(
                array('min' => '2', 'max' => '75',
                'encoding' => $this->_config->resources->view->charset)
            );
        $valtxtNameStringlength->setMessage('El campo nombre requiere más de 2 caracteres');
        $txtName->addValidator($valtxtNameStringlength);
        $txtName->setValue('');
        $this->addElement($txtName);

        // apellido Materno
        $txtFirstLastNamer = new Zend_Form_Element_Text('txtFirstLastName');
        $txtFirstLastNamer->setRequired();
        $txtFirstLastNamer->setAttrib('maxLength', 28);
        $txtFirstLastNamer->setAttrib('minlength', 2);
        $txtFirstLastNamerStringlength=new Zend_Validate_StringLength(
                array('min' => '2', 'max' => '75',
                'encoding' => $this->_config->resources->view->charset)
            );
        $txtFirstLastNamerStringlength->setMessage('El campo Apellido Paterno requiere más de 2 caracteres');
        $txtFirstLastNamer->addValidator($txtFirstLastNamerStringlength);
        $txtFirstLastNamer->setRequired();
        $FirstLastNamer = new Zend_Validate_NotEmpty();
        $txtFirstLastNamer->addValidator($FirstLastNamer);
        $txtFirstLastNamer->errMsg ='¡Se requiere su apellido Materno!';
        $this->addElement($txtFirstLastNamer);
        
        
          // apellido materno
        $txtSecondLastNamer = new Zend_Form_Element_Text("txtSecondLastName");
        $txtSecondLastNamer->setRequired();
        $txtSecondLastNamer->setAttrib('maxLength', 28);
        $txtSecondLastNamer->setAttrib('minlength', 2);
        $txtSecondLastNamerStringlength=new Zend_Validate_StringLength(
               array('min' => '2', 'max' => '75',
               'encoding' => $this->_config->resources->view->charset)
           );
        $txtSecondLastNamerStringlength->setMessage('Se requiere más de 2 caracteres');
        $txtSecondLastNamer->addValidator($txtSecondLastNamerStringlength )   ; 
        $txtSecondLastNamer->setRequired();
        $SecondLastNamer = new Zend_Validate_NotEmpty();
        $txtSecondLastNamer->addValidator($SecondLastNamer);
        $txtSecondLastNamer->errMsg ='¡Se requiere su apellido Paterno!';
        $this->addElement($txtSecondLastNamer);
        
        
          // Fecha
        $fBirthDate = new Zend_Form_Element_Text('txtBirthDay');
        $fBirthDate->setRequired();
        
        // Validacion de Fecha de Nacimiento, deberia de ser mayor a 16 años.
        $fBirthDateVal = new Zend_Validate_NotEmpty();
      //  $fBirthDate->addValidator(new Zend_Validate_Date('YYYY/DD/MM'));
        $fBirthDate->addValidator($fBirthDateVal, true);
        $validador = new Zend_Validate_Callback(function($date) {            
            $dia ='';
            $dia = $date;
            $now = new Zend_Date();
            $bd = new Zend_Date($date);
            $rest = $bd->isEarlier($now);                              
            if ($rest) {
                if (strtotime(date('d-m-Y', strtotime('-18 year'))) >= strtotime(str_replace('/','-',$dia)) ) {
                    return true;
                } else {
                    return false;
                }
            }
            return $rest;
        });
        
        $validador->setMessage('Solo se permiten mayores de 18 años.');     
        $fBirthDate->addValidator($validador, true);        
        
        $this->addElement($fBirthDate);

        // Clave
        $fClave = new Zend_Form_Element_Password('pswd');
        $fClave->setAttrib('maxLength', $this->_maxlengthPwsd);
        $fClave->setAttrib('minlength', $this->_minlengthPwsd);

        $fClave->setRequired();         
        $fClaveNotEmpty = new Zend_Validate_NotEmpty();
        $fClave->addValidator($fClaveNotEmpty);
        $fClaveStringLength = new Zend_Validate_StringLength(
                array(                    
                    'min' => $this->_minlengthPwsd, 
                    'max' => $this->_maxlengthPwsd,
                    'encoding' => $this->_config->resources->view->charset
                )
        );
        $fClaveStringLength->setMessage('Las contraseñas deben de ser de '.$this->_minlengthPwsd.' a '.$this->_maxlengthPwsd.' caracteres.');
        $fClave->addValidator($fClaveStringLength);
      
        $this->addElement($fClave);
        
        // Repetir Clave
        $fRClave = new Zend_Form_Element_Password('pswd2');
        $fRClave->setRequired();
        $fRClave->setAttrib('maxLength', 32);
        $fRClave->setAttrib('minlength', 3);
        $fRclavePasswordConfirmation=new App_Validate_PasswordConfirmation();
        $fRclavePasswordConfirmation->setMessage('Las contraseñas introducidas no coinciden. Vuelve a intentarlo');
        $fRClave->addValidator(new App_Validate_PasswordConfirmation());
        $fRClave->errMsg = 
            'Las contraseñas introducidas no coinciden. Vuelve a intentarlo.';
        $this->addElement($fRClave);
        
        
        $e = new Zend_Form_Element_Hash('auth_token');
        $hash_blow_fish = crypt('l0gInAquiEmpleos', '$2a$07$'.md5(uniqid(rand(), true)).'$');
        $e->setAttrib('id','auth_token');
        $e->setValue($hash_blow_fish);        
        $e->setErrorMessages(array(
            'Identical' => 'Por favor vuelva ha intentar enviar los datos.'
        ));
        $this->addElement($e);

        $this->validadorEmail( $this->_idUsuario );
//        $e = new Zend_Form_Element_Hash('hidToken');
//        $this->addElement($e);

    }
    
    
    
    public function validadorEmail($idUsuario, $rol = 'postulante')
    {
        // Email     
        $fEmail = new Zend_Form_Element_Text('txtEmail');
       // $fEmail->setAttrib('type', 'email');
       //  $fEmail->setAttrib('type', 'email');
        //$fEmail->setAttrib('maxLength', 75);
        $fEmail->setRequired();
//        $fEmailVal = new Zend_Validate_EmailAddress(
//        );
//        $fEmailVal->setMessage('El email invalido', 'emailAddressDotAtom');
        $fEmail->addFilter(new Zend_Filter_StringToLower());
//        $fEmailVal->setMessage('El email invalido', 'emailAddressQuotedString');
//        $fEmail->addValidator($fEmailVal, true);
        $fEmail->addValidator(new Zend_Validate_NotEmpty(), true);
        $f = 'Application_Model_Usuario::validacionEmail';
        
        $fEmailVal = new Zend_Validate_Callback(array(
            'callback' => $f,
            'options' => array(
                $idUsuario, $rol
            )
        ));
        
        $fEmailVal->setMessage('El email ya se encuentra registrado', 'callbackValue');
        $fEmail->addValidator($fEmailVal);
        $this->addElement($fEmail);
        
    }
     public static function getMensajesErrors($fom) { 
      if(count($fom->getMessages() ) == 0){
          return ;
      }
      $errors=array();
           // var_dump($fom->getMessages());exit;
      foreach ($fom->getMessages() as $form => $error) {   
        foreach ($error as $value=>$key) {     
//           echo $value;
//            $errors[$form][0]= Application_Form_RegistroRapidoPostulante::$errors[$value];     
            return $key;          
            
        }          
      } 
       // return $errors[$form];
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

