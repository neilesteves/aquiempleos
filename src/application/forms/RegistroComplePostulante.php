<?php

/**
 * Description Registro rapido
 *
 * @author Postulante
 */
class Application_Form_RegistroComplePostulante extends App_Form
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
    /**
     * @var Application_Form_RegistroRapidoPostulante
     */
    private $_empresaModelo;
    private $_postulanteData;


    const MSJ_ERROR_TELEFONO = 'Ingrese un numero entre 7-9 digitos';
    
    public function setIdUsuario($iu)
    {
        $this->_idUsuario = $iu;
    }
    
    public function getIdUsuario()
    {
        return $this->_idUsuario;
    }
    
    public function __construct($iu =null,$dataPostulante= array())
    {
        
        if (count($dataPostulante)>0) {
          $this->_postulanteData=$dataPostulante;
        }
        $this->_idUsuario =$iu;
        parent::__construct();
        mb_internal_encoding("UTF-8");
        
        
    }
    
    public function init()
    {
        parent::init();
      // Sexo
        $fSexo = new Zend_Form_Element_Select('selGenero');
        $fSexo->setRequired();
        $fSexo->addMultiOption("M", "Masculino");
        $fSexo->addMultiOption("F", "Femenino");
        $fSexo->setValue("M");
       // $fSexo->errMsg =  'Campo Requerido';
       // $fSexo->setSeparator('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        $this->addElement($fSexo);
 
         // tipo de documento
        $fSelDoc = new Zend_Form_Element_Select('selDocumento');
        $fSelDoc->setRequired();
        $fSelDoc->addMultiOption( 'dni' . $this->_maxlengthTipoDocDni, "CI");
        $fSelDoc->addMultiOption('ce' . $this->_maxlengthTipoDocCe, "Carnet Extranjería");
        $fSelDocVal = new Zend_Validate_InArray(array_keys(
                array(
                    'dni' . $this->_maxlengthTipoDocDni => 'DNI',
                    'ce' . $this->_maxlengthTipoDocCe => 'Carnet Extranjería'
                )));
       
        $fSelDoc->addValidator($fSelDocVal);
        $this->addElement($fSelDoc);
        // número de documento
        $fNDoc = new Zend_Form_Element_Text('txtNumero');
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
                array('callback' => $f, 'options' => array($fSelDoc,  $this->_idUsuario))
        );
        $fNDoc->addValidator($fNDocVal);
        $this->addElement($fNDoc);        
        
         // Combo País
        $this->_listaPais = new Application_Model_Ubigeo();
        $valores = $this->_listaPais->getPaises();
        $fPais = new Zend_Form_Element_Select('selPais');
        $fPais->setRequired();
        $fPais->addMultiOptions($valores);
        $fPais->setValue(Application_Model_Ubigeo::PERU_UBIGEO_ID);
        $fPaisVal = new Zend_Validate_InArray(array_keys($valores));
        $fPais->addValidator($fPaisVal);
      
        $fPais->errMsg = $this->_mensajeRequired;
        $this->addElement($fPais);
        
        
          // distrito, provincia , departamento , 
        $txtubigeo = new Zend_Form_Element_Text("txtUbicacion");
        $txtubigeo->setRequired();
        $txtubigeo->setAttrib('maxLength', 150);
        $txtubigeo->setAttrib('minlength', 2);
        $txtubigeo->setRequired();
        $SecondLastNamer = new Zend_Validate_NotEmpty();
        $txtubigeo->addValidator($SecondLastNamer);
        $txtubigeo->addValidator(
            new Zend_Validate_StringLength(
                array('min' => 3, 'max' => 150,
                'encoding' => $this->_config->resources->view->charset)
            )
        );        
       
        $txtubigeo->errMsg ='¡Se requiere su ubicacion!';
        $this->addElement($txtubigeo);
        
        
        $e = new Zend_Form_Element_Hash('tokenhiden');       
        $e->setTimeout(3600);
        // $e->initCsrfToken();  
        $this->addElement($e);
        
        if (isset($this->_idUsuario) && isset($this->_postulanteData)) {
//            $post = new Application_Model_Postulante();
//            $arrayPostulante = $post->getPostulante($this->_idUsuario);        
            
            $this->getElement('selGenero')->setValue($this->_postulanteData['sexo']);
            $this->getElement('txtNumero')->setValue($this->_postulanteData['num_doc']);
            if(isset($this->_postulanteData['pais_residencia']))
              $this->getElement('selPais')->setValue($this->_postulanteData['pais_residencia']);
              $this->getElement('selDocumento')->setValue($this->_postulanteData['tipo_doc']);
            
            if ($this->_postulanteData['pais_residencia'] == Application_Model_Ubigeo::PERU_UBIGEO_ID) {
                $ubg = new Application_Model_Ubigeo();
                $dataUbigeo = $ubg->getDetalleUbigeoById($this->_postulanteData['id_ubigeo']);
                $this->getElement('txtUbicacion')->setValue($dataUbigeo['nombre']);
            }
        }
        
        
    }
    
    
    public function remuneracionUbigeo() 
    {
       $this->removeElement('selGenero');
       $this->removeElement('selDocumento');
       $this->removeElement('txtNumero');
       $this->removeElement('selPais');
       $remuneracion = new Zend_Form_Element_Hidden('txtremuneracion');
       $remuneracion->setRequired(true);
       $remuneracion->setValue($this->_config->salarios->default);
       $this->addElement($remuneracion);
       
       $ubigeo = new Zend_Form_Element_Hidden('ubigeo'); 
       $ubigeo->setRequired(true);
       $ubigeo->setValue("3970");
       $ubigeo->addValidator(new Zend_Validate_NotEmpty(), true) ;
       $this->addElement($ubigeo);
        
    }
    
    public function isValidRemUbi($data){    
        $this->getElement('txtremuneracion')->addValidator(
            new Zend_Validate_StringLength(
                array('min' => 1, 'max' => 150,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $this->getElement('ubigeo')->addValidator(new Zend_Validate_NotEmpty(), true) ;        
      
        return parent::isValid($data);
    }
            

    public function isValid($data) {
      
        $validate= new Zend_Validate_NotEmpty();
        $this->getElement('selGenero')->addValidator($validate);

            $f = "Application_Model_Postulante::validacionDocumento";
            $fNDocVal = new Zend_Validate_Callback(
                    array('callback' => $f, 'options' => array($data['selDocumento'],  $this->_idUsuario))
            );
            $this->getElement('txtNumero')->addValidator($fNDocVal);
        
        if($data['selDocumento']=='dni'){
           $this->getElement('txtNumero')->addValidator(new Zend_Validate_NotEmpty(), true);
           
           $fStringLength = new Zend_Validate_StringLength(array('min' => 8,'max' => 8,'encoding' => $this->_config->resources->view->charset));
           $this->getElement('txtNumero')->addValidator($fStringLength);
        }
        if($data['selPais']!=Application_Model_Ubigeo::PERU_UBIGEO_ID){
             $this->txtUbicacion->setRequired(false);
        }
        
        $this->getElement('selDocumento')->addValidator($validate);
        $this->getElement('selPais')->addValidator($validate);
        
        return parent::isValid($data);
    }
    
    public static function getMensajesErrors($fom) { 
      if(count($fom->getMessages() ) == 0){
          return ;
      }
      foreach ($fom->getMessages() as $key => $value) {             
          return Application_Form_RegistroComplePostulante::$errors[$key];
      }       
    }
    
    
    
       public static $errors = array(
        'isEmpty' => 'Campo Requerido',
        'stringLengthInvalid' => 'Documento inválido',
        'stringLengthTooLong' => 'Documento inválido',
        'stringLengthTooShort' => 'El documento no tiene 8 caracteres',
        'stringLengthTooLong' => 'El documento no tiene 8 caracteres',
        'callbackInvalid' => 'El Número del documento ya se encuentra registrado',
        'callbackValue' => 'El Número del documento ya se encuentra registrado',
        'notSame'=>'Por favor vuelva a intentarlo'
    );

 
}

