<?php

class Application_Form_FacturacionDatos extends App_Form
{
    //Max
    private $_maxlengtruc = '12';
    private $_maxlengthMonto = '10';
    private $_maxlengthMontoMod = '7';
    private $_maxlengthNroContrato = '10';
    private $_dataAdecsys;
    
 
    public function init()
    {
        parent::init();
        
        $txtRuci = new Zend_Form_Element_Text('txtRuc');
        $txtRuci->setRequired();
        $txtRuci->setAttrib('maxLength', $this->_maxlengtruc);
        $txtRuci->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengtruc,
                'encoding' => $this->_config->resources->view->charset)
            )
        );     
        $txtRuci->errMsg = 'ruc Incorrecta';
        $this->addElement($txtRuci);
        
        $ftxtName = new Zend_Form_Element_Text('txtName');
        $ftxtName->setRequired();
        $ftxtName->setAttrib('maxLength', 30);
        $ftxtName->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '2', 'max' => 30,
                'encoding' => $this->_config->resources->view->charset)
            )
        );    
        $ftxtName->errMsg = 'razon comercial Incorrecta';
        $this->addElement($ftxtName);
        
        
        $tipovia = new Application_Model_TipoVia();
        $selVia = new Zend_Form_Element_Select('selVia');
        $selVia->setRequired();
        $selVia->addMultiOption('', '.:: Seleccione ::.');
        $selVia->addMultiOptions($tipovia->listaTipoVia());
        $selVia->errMsg = $this->_mensajeRequired;
        $this->addElement($selVia);
        
        $txtLocation = new Zend_Form_Element_Text('txtLocation');
        $txtLocation->setRequired();
        $txtLocation->setAttrib('maxLength', 40);
        $txtLocation->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => 40,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $txtLocation->errMsg = 'Ingresar locacion';
        $this->addElement($txtLocation);

        $txtNroPuerta = new Zend_Form_Element_Text('txtNroPuerta');
        $txtNroPuerta->setRequired();
        $txtNroPuerta->setAttrib('maxLength', 10);
        $txtNroPuerta->addValidator(
            new Zend_Validate_StringLength(
                array('min' => 1, 'max' => 10,
                    'encoding' => $this->_config->resources->view->charset)
            )
        );
        $txtNroPuerta->errMsg = 'Ingresar Nro de puerta';
        $this->addElement($txtNroPuerta);
        
       $ente_ruc = new Zend_Form_Element_Hidden('ente_ruc');
       $this->addElement($ente_ruc);
       $ruc_adecsys = new Zend_Form_Element_Hidden('ruc_adecsys');
       $this->addElement($ruc_adecsys);
    }
    
    public function setreadonly($data) {
        if(isset($data['txtRuc']) &&!empty($data['txtRuc'])){
            $this->txtRuc->setAttrib("readonly", "readonly");
        }
        if(isset($data['txtName']) &&!empty($data['txtName'])){
            $this->txtName->setAttrib("readonly", "readonly");
        }
        if(isset($data['selVia']) &&!empty($data['selVia'])){
            $this->selVia->setAttrib("disabled", "disabled");  
        }
        if(isset($data['txtLocation']) && !empty($data['txtLocation'])){
            $this->txtLocation->setAttrib("readonly", "readonly");
        }
        if(isset($data['txtNroPuerta']) && !empty($data['txtNroPuerta'])){
            $this->txtNroPuerta->setAttrib("readonly", "readonly");
        }
        if (isset($data['txtNroPuerta']) && $data['txtNroPuerta']=='0') {
          $this->txtNroPuerta->setAttrib("readonly", "readonly");
        }
        
        
    }
    

}

