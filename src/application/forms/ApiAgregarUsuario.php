<?php
/**
 * Formulario para agregar usuarios en el API
 *
 * @author Jfabian
 */
class Application_Form_ApiAgregarUsuario extends App_Form
{
    protected static $_defaultWebsite= 'http://';
    protected $_editar;
    
    public function __construct($editar = false)
    {
        parent::__construct();
        if (isset($editar) && $editar !== false) {
            $this->_editar = $editar;
            
            $estadoData = array();
            $estadoData['vigente'] = 'Activo';
            $estadoData['dadobaja'] = 'Inactivo';
            
            $e = new Zend_Form_Element_Hidden('idUsuApi');
            $e->clearDecorators();
            $e->addDecorator('ViewHelper');
            $this->addElement($e);
            
            $e = new Zend_Form_Element_Hidden('usuario_id');
            $e->clearDecorators();
            $e->addDecorator('ViewHelper');
            $this->addElement($e);
            
            $e = new Zend_Form_Element_Select('estado');
            $e->addMultiOptions($estadoData);
            $this->addElement($e);
        }
    }
    
    public function init()
    {
        parent::init();
        
        //Dominio
        $e = new Zend_Form_Element_Text('domain');
        $e->setValue(self::$_defaultWebsite);
        $e->setRequired();
        $e->errMsg = 'Dominio inválido';
        $this->addElement($e);
        
        //Forzar comprobacion
        $e = new Zend_Form_Element_Checkbox('force_domain');
        $e->setValue(true);
        $this->addElement($e);
        
        //Usuario
        $e = new Zend_Form_Element_Text('usuario');
        $e->setRequired();
        $v = new Zend_Validate_EmailAddress();
        $e->addValidator($v);
        $e->errMsg = 'Ingrese un correo valido.';
        $this->addElement($e);
        
        //Fecha de Vigencia del API
        $e = new Zend_Form_Element_Checkbox('vigencia');
        $this->addElement($e);
        
        //Fecha de inicio
        $e = new Zend_Form_Element_Text('fecha_ini');
        $v = new Zend_Validate_Date('DD-MM-YYYY');
        $e->addValidator($v);
        $e->errMsg = 'Debe escoger una fecha valida.';
        $this->addElement($e);
        
        //Fecha fin
        $e = new Zend_Form_Element_Text('fecha_fin');
        $v = new Zend_Validate_Date('DD-MM-YYYY');
        $e->addValidator($v);
        $e->errMsg = 'Debe escoger una fecha valida.';
        $this->addElement($e);
        
        
//        //Token
//        $e = new Zend_Form_Element_Hidden('tok');        
//        $e->setRequired(); 
//        $tok = crypt(date('dmYH'), '$2a$07$'.md5(uniqid(rand(), true)).'$');
//        $e->setValue($tok);
//        $this->addElement($e);
        
    }
    
    public function isValid($data)
    {
        if ($this->_editar == false && $data['vigencia'] == 1) {
            // @codingStandardsIgnoreStart
            $this->fecha_ini->setRequired();
            $this->fecha_fin->setRequired();
            // @codingStandardsIgnoreEnd
        }
        if ($data['force_domain'] == 1) {
            $this->domain->addValidator(
                new App_Validate_Domain()
            );
        }
        if ($this->_editar == false) {
            $method = "Application_Model_Api::evaluaUsuarioByEmail";
            $v = new Zend_Validate_Callback(
                array('callback'=>$method,'options' => array($data['usuario']))
            );
            $this->usuario->addValidator($v);
        } else {
            if ($this->domain->getValue() == Application_Form_ApiAgregarUsuario::$_defaultWebsite) {
                $this->domain->clearValidators();
                $this->domain->setRequired(false);
            }
            
            $this->usuario->clearValidators();
            $this->usuario->setRequired(false);
        }
        
        
//        if ( crypt(date('dmYH'),$data['tok']) !== $data['tok'] ) {
//            return false;
//        }
        
        return parent::isValid($data);
    }
}