<?php


class Application_Form_MiUbicacion extends App_Form
{
    protected $_ubigeo;
    protected $_listaPais;
    protected $_listaDepartamento;
    protected $_listaProvincia;
    protected $_listaDistrito;
    protected $_listaDistritoCallao;
    private   $_online = false;

    const REGEX_FACEBOOK = '/^(http\:\/\/|https\:\/\/)?(?:www\.)?(facebook|fb)\.com\/(?:(?:\w\.)*#!\/)?(?:pages\/)?(?:[\w\-\.]*\/)*([\w\-\.]*)/';
    const REGEX_TWITTER_URL  = '/^(http\:\/\/|https\:\/\/)?(?:www\.)?twitter\.com\/(\@)?[A-Za-z0-9_]+$/';
    const REGEX_TWITTER_USER = '/^\@[A-Za-z0-9_]+$/';

    public static $errors = array(
        'isEmpty' => 'Campo Requerido',
        'callbackValue' => 'El Número del documento ya se encuentra registrado',
        'notSame'=>'Por favor vuelva a intentarlo',
        'missingToken'=>'Por favor vuelva a intentarlo',
        'notInArray'=>'No se encontro el registro',
        'notBetween' => 'La fecha no esta dentro del rango permitido',
        'invalid' => 'Las fechas no son correctas, Por favor vuelva a intentarlo',

        'txtIdUbigeo' => array(
            'isEmpty' => 'La ubicacion es incorrecta',
        ),
        'txtTwitter' => array(
            'regexNotMatch' => "La dirección de su twitter es incorrecta"
        ),

        'txtFacebook'=> array(
            'regexNotMatch' => "La dirección de su facebook es incorrecta"
        )
    );


    public function __construct()
    {
        $this->_ubigeo = new Application_Model_Ubigeo();
        $this->_listaPais = $this->_ubigeo->getPaises();
        parent::__construct();
    }
    
    public function init()
    {
        parent::init();

        $fPais = new Zend_Form_Element_Select('selPais');
        $fPais->setRequired();
        $fPais->addMultiOption('0', 'Seleccione país');
        $fPais->addMultiOptions($this->_listaPais);
        
        $fPaisVal = new Zend_Validate_InArray(array_keys($this->_listaPais));
        $fPaisVal->setMessages(array(
            Zend_Validate_InArray::NOT_IN_ARRAY => 'El país seleccionado no es correcto'
        ));
        
        $fPais->addValidator($fPaisVal);
        $fPais->setValue(Application_Model_Ubigeo::PERU_UBIGEO_ID);        
        $fPais->errMsg = $this->_mensajeRequired;
        $this->addElement($fPais);


        $validaEntero = new Zend_Validate_Int();
        $validaEntero->setMessages(array(
            Zend_Validate_Int::INVALID => 'El valor no es correcto',
            Zend_Validate_Int::NOT_INT => 'El valor no es correcto'
        ));

        $e = new Zend_Form_Element_Hidden('txtIdUbigeo');
        $e->addValidator($validaEntero);
        $e->clearDecorators();
        $e->setRequired(false);
        $e->addDecorator('ViewHelper');
        $e->setAttrib('class', 'hidden_id');
        $this->addElement($e);


        $fubig = new Zend_Form_Element_Text('txtUbigeo');
        $e->setRequired(false);
        $fubig->addValidator(new Zend_Validate_NotEmpty(), true);
        $this->addElement($fubig);


        $fprov = new Zend_Form_Element_Checkbox('rdDispProvincia');
        $fprov->setValue(false);
        $fprov->clearValidators();
        $this->addElement($fprov);


        //
        $ffb = new Zend_Form_Element_Text('txtFacebook');
        $ffb->setRequired(false);
        $this->addElement($ffb);


        $ftw = new Zend_Form_Element_Text('txtTwitter');
        $ftw->setRequired(false);
        $this->addElement($ftw);

        $fpres = new Zend_Form_Element_Textarea('txtPresentacion');
        $fpres->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '0',
                      'max' => $this->_maxlengthPresentacion,
                     'encoding' => $this->_config->resources->view->charset)
            )
        );

        $this->addElement($fpres);
        
        $e = new Zend_Form_Element_Hash('hidToken');     
        $e->setTimeout(3600000);

        $this->addElement($e);



    }
    
    public function isValid($data)
    {       
//        if(!empty($data['txtUbigeo']) && 
//                strlen($data['txtUbigeo']) > 0 )
//        {
//            $p = new Application_Model_Ubigeo();
//            $res = $p->getDetalleUbigeoById( $data['txtIdUbigeo'] );
//
//            $nomb   = strtolower(trim( str_replace(',', '', $res['nombre'])));
//            $nomb_c = strtolower(trim( str_replace(',', '', $data['txtUbigeo'])));
//
//            if( $nomb != $nomb_c ) {
//                $data['txtIdUbigeo'] = NULL;
//                $this->txtIdUbigeo->setRequired();
//            }
//        }

        if( isset($data['txtFacebook']) && strlen($data['txtFacebook']) > 0) {
            $this->txtFacebook->setRequired();
            $this->txtFacebook->setAttrib('maxLength', 120);
            $this->txtFacebook->addValidator('regex', false, array(
                'pattern' => self::REGEX_FACEBOOK,
                'messages' => array()
            ));
        }

        if( isset($data['txtTwitter']) && strlen($data['txtTwitter']) > 0)
        {
            $this->txtTwitter->setRequired();
            $this->txtTwitter->setAttrib('maxLength', 120);

            $user = $data['txtTwitter'];
            if( !strpos($user, '://') || !strpos($user, 'www.') ) {
                $regex = self::REGEX_TWITTER_USER;
            }else{
                $regex = self::REGEX_TWITTER_URL;
            }

            $this->txtTwitter->addValidator('regex', false, array(
                'pattern' => $regex,
            ));
        }

        return parent::isValid($data);
    }

    public function setDatos($postulante)
    {
        if( isset( $postulante['pais_residencia'] )) {
           $this->selPais->setValue($postulante['pais_residencia']);
        }

       $this->txtFacebook->setValue($postulante['facebook']);
       $this->txtTwitter->setValue($postulante['twitter']);
       $this->rdDispProvincia->setValue( (bool)$postulante['disponibilidad_provincia_extranjero'] );
       $this->txtPresentacion->setValue( $postulante['presentacion'] );

       $ubig = new Application_Model_Ubigeo();
       $ubig = $ubig->getDetalleUbigeoById($postulante['id_ubigeo']);

        if( $postulante['pais_residencia'] == Application_Model_Ubigeo::PERU_UBIGEO_ID ) {
            $this->txtUbigeo->setValue(ucfirst($ubig['nombre']));
            $this->txtIdUbigeo->setValue($postulante['id_ubigeo']);
        }else {
            if ( isset($postulante['pais_residencia']) ) {
                $this->txtUbigeo->setAttrib('disabled', 'disabled');
            }
        }


    }

    public static function getMensajesErrors($fom)
    {


        $errors=array();
        foreach ($fom->getMessages() as $element=> $error) {

            if ( array_key_exists($element, self::$errors) && is_array( self::$errors[$element] )) {
                $_errors = self::$errors[$element];
                rsort($_errors);
                foreach ($_errors as $key => $value ) {
                    $errors = $value;
                }

            } else {

                rsort($error);
                foreach ($error as $value => $key) {
                    if (array_key_exists($value, self::$errors)) {
                        $errors = self::$errors[$value];
                    }
                }
            }


        }
        return $errors;

    }

}