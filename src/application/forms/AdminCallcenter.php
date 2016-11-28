<?php

/**
 * Description of Categoria
 *
 * @author Usuario
 */
class Application_Form_AdminCallcenter extends App_Form
{
    //Max Length
     private $_maxlengthEmail = '75';
     private $_maxlengthTipoDocDni = '14';
     private $_maxlengthTipoDocRuc = '14';
    
    //@codingStandardsIgnoreStart
    public static $valorDocumento;
    //@codingStandardsIgnoreEnd
    public function __construct()
    {
        parent::__construct();
        $keyDni = 'dni#'. $this->_maxlengthTipoDocDni;
        $keyRuc = 'ruc#'.$this->_maxlengthTipoDocRuc;
        
        self::$valorDocumento = array(
            $keyRuc =>'RUC',
            $keyDni => 'CI'
        );
    }
    
    public function init()
    {
        parent::init();

        //email
        $fEmail = new Zend_Form_Element_Text('txtEmailCliente');
        $fEmail->setAttrib('maxLength', $this->_maxlengthEmail);
        $fEmailVal = new Zend_Validate_EmailAddress(
            array("allow"=>Zend_Validate_Hostname::ALLOW_ALL),
            true
        );
        $fEmail->addFilter(new Zend_Filter_StringToLower());
        $fEmail->addValidator($fEmailVal, true);
        $fEmail->addValidator(new Zend_Validate_NotEmpty(), true);
        $fEmailVal->setMessage(
            'No parece ser un correo electrónico valido',
            Zend_Validate_EmailAddress::INVALID
        );
        $this->addElement($fEmail);
    }
    
    public function validadorNumDoc()
    {
        
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
        $fNDoc->setAttrib('maxLength', $this->_maxlengthTipoDocRuc);
        $fNDocVal  = new Zend_Validate_StringLength(
            array('min' => $this->_maxlengthTipoDocRuc, 
                'max' => $this->_maxlengthTipoDocRuc,
                'encoding' => $this->_config->resources->view->charset
            )
        );
        $fNDoc->addValidator($fNDocVal);
//        
//        $f = "Application_Model_Postulante::validacionDocumento";
//        $fNDocVal = new Zend_Validate_Callback(
//            array('callback'=>$f,'options' => array($fSelDoc,$id))
//        );
//        $fNDoc->addValidator($fNDocVal);
        
        $this->addElement($fNDoc);
    }
}

