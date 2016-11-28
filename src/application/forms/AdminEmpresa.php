<?php

class Application_Form_AdminEmpresa extends App_Form
{
    private $_idUsuario;
    protected $_listaRubro;
    
    //Max
    private $_maxlengthNombreRa = '56';
    private $_maxlengthNumRuc = '14';
    
    public function init()
    {
        parent::init();
        $this->setAction('/registro-empresa/');
        
        //Razon Social   
        $fRazonSocial = new Zend_Form_Element_Text('razonsocial');
        $fRazonSocial->errMsg="Debe ingresar una Razon Social";
        $fRazonSocial->setAttrib('maxLength', $this->_maxlengthNombreRa);
        $fRazonSocial->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthNombreRa,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $this->addElement($fRazonSocial);
        
        //Ruc
        $fNRuc = new Zend_Form_Element_Text('num_ruc');
        $fNRuc->setAttrib('maxLength', $this->_maxlengthNumRuc);
        $fNRuc->addValidator(new Zend_Validate_NotEmpty(), true);
        $fNRucVal  = new Zend_Validate_StringLength(
            array('min' => $this->_maxlengthNumRuc, 'max' => $this->_maxlengthNumRuc,
                'encoding' => $this->_config->resources->view->charset)
        );
        $fNRuc->addValidator($fNRucVal);
        $fNRuc->errMsg = "Debe ingresar de Ruc 14 Digitos";
        $this->addElement($fNRuc);
    }
}

