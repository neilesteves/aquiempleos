<?php


class Application_Form_PagoPos extends App_Form
{    
    
    /**
     * Listado de Bancos
     * @var array
     */
    protected $_listaBancos;
    
    /**
     * Listado de Tipos de Tarjetas de Credito
     * @var array
     */
    protected $_listaTarjetas;
    
    /**
     * Minimo de digitos del Voucher
     * @var int
     */
    protected $_minlengthVoucher = 5;
    
    /**
     * Maximo de digitos del Voucher
     * @var int
     */
    protected $_maxlengthVoucher = 16;
    
    /**
     * Minimo de digitos del Nro. de Lote
     * @var int
     */
    protected $_minlengthNroLote = 1;
    
    /**
     * Maximo de digitos del Nro. de Lote
     * @var int
     */
    protected $_maxlengthNroLote = 3;
    
    
    public function init()
    {
        parent::init();
        
        $e = new Zend_Form_Element_Hash('auth_token');                
        $e->setTimeout(3600);        
        $this->addElement($e);
        
        $e = new Zend_Form_Element_Text('voucher');
        $e->setRequired();
        $e->addValidator(
            new Zend_Validate_StringLength(
                array(
                    'min' => $this->_minlengthVoucher,
                    'max' => $this->_maxlengthVoucher,
                    'encoding' => $this->_config->resources->view->charset
                )
            )
        );
        $this->addElement($e);
        
        $e = new Zend_Form_Element_Text('payment_date');
        $e->setRequired();
        $this->addElement($e);
        
        $e = new Zend_Form_Element_Select('banco');
        $e->setRequired();
        $mBanco = new Application_Model_TarjetaBanco();        
        $this->_listaBancos = $mBanco->getBancosFormSelect();
        $e->addMultiOption('', 'Seleccionar Tarjeta/Banco');
        $e->addMultiOptions($this->_listaBancos);
        $this->addElement($e);
        
        $e = new Zend_Form_Element_Select('tarjeta');
        $e->setRequired();
        $mTarjetas = new Application_Model_TipoTarjeta();
        $this->_listaTarjetas = $mTarjetas->getTarjetasFormSelect();
        $e->addMultiOption('', 'Seleccionar Tipo');
        $e->addMultiOptions($this->_listaTarjetas);
        $this->addElement($e);
        
        $e = new Zend_Form_Element_Text('lote');
        $e->setRequired();
        $e->addValidator(
            new Zend_Validate_StringLength(
                array(
                    'min' => $this->_minlengthNroLote,
                    'max' => $this->_maxlengthNroLote,
                )
            )
        );                
        $this->addElement($e);
        
    }
    
    public function isValid($data) 
    {
        
        $vacio = new Zend_Validate_NotEmpty();
        
        if (isset($data['banco'])) {
            if (!$vacio->isValid($data['banco'])) {
             //   echo "fix0";exit;
                return false;
            }
        }
        
        if (isset($data['tarjeta'])) {
            if (!$vacio->isValid($data['tarjeta'])) {  
               // echo "fix1";exit;
                return false;
            }
        }
        
        
        if (isset($data['payment_date'])) {
            
            if (!$vacio->isValid($data['payment_date'])) {
                //echo "fix2";exit;
                return false;
            }
            
            $f = new Zend_Validate_Date();
            $f->setFormat('dd/mm/yyyy');
            if (!$f->isValid($data['payment_date'])) { 
                //echo "fix3";exit;
                return false;
            }                        
        }
        
        if (isset($data['voucher'])) {
            
            if (!$vacio->isValid($data['voucher'])) { 
                ///echo "fix4";exit;
                return false;
            }
            
            $d = new Zend_Validate_Digits();
            if (!$d->isValid($data['voucher'])) { 
                ///echo "fix5";exit;
                return false;
            }
            
//            if($data['tarjeta']=='1')
//            {
//                $l = new Zend_Validate_StringLength(array('min' => 15, 'max' => 15));
//                if (!$l->isValid($data['voucher'])) { 
//                    //echo "fix6";exit;
//                    return false;
//                }
//                /*if(substr($data['voucher'], 0, 6) != '992143'){  
//                    //echo substr($data['voucher'], 0, 6)."fix7";exit;
//                    return false;                    
//                }*/
//            }
//
//            if($data['tarjeta']=='2')
//            {
//                $l = new Zend_Validate_StringLength(array('min' => 16, 'max' => 16));
//                if (!$l->isValid($data['voucher'])) { //echo "fix8";exit;
//                    return false;
//                }                
//            }
        }
        
        if (isset($data['lote'])) {
            
            if (!$vacio->isValid($data['lote'])) { //echo "fix9";exit;
                return false;
            }
            
            $d = new Zend_Validate_Digits();
            if (!$d->isValid($data['lote'])) { //echo "fix10";exit;
                return false;
            }
        }
        
        
        
        
        return parent::isValid($data);
    }
    
    
}