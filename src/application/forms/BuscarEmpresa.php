<?php

class Application_Form_BuscarEmpresa extends App_Form
{
    
    protected $_listaRubro;
    
    private $_maxlengthNombreRa = '50';
    
    public function init()
    {
        parent::init();
         $this->setMethod('post');
        $fRazonSocial = new Zend_Form_Element_Text('razonsocial');
        $fRazonSocial->errMsg="Debe ingresar un texto";
        $fRazonSocial->setAttrib('maxLength', $this->_maxlengthNombreRa);
        $fRazonSocial->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthNombreRa,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $this->addElement($fRazonSocial);
        
        $mRubro = new Application_Model_Rubro();
        $this->_listaRubro = $mRubro->getRubrosLanding();
        $rubro = new Zend_Form_Element_Select('id_rubro');
        $rubro->addMultiOption('0', 'Todos');
        $rubro->addMultiOptions($this->_listaRubro);
        $this->addElement($rubro);
        

    }
}

