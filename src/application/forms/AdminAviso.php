<?php

/**
 * Description of Categoria
 *
 * @author Usuario
 */
class Application_Form_AdminAviso extends App_Form
{
    //Max Length
    private $_maxlengthNombreRa = '80';
    private $_maxlengthNumRuc = '11';
    private $_maxlengthUrlId = '6';
    private $_maxlengthCodigoAd = '8';
    
    public function init()
    {
        parent::init();
       
        //ID
        $fUrlId = new Zend_Form_Element_Text('url_id');
        $fUrlId->errMsg="Debe ingresar un ID correcto";
        $fUrlId->setAttrib('maxLength', $this->_maxlengthUrlId);
        $fUrlId->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthUrlId,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $this->addElement($fUrlId);
        

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
        $fNRuc->errMsg = "Debe ingresar un número de 11 dígitos";
        $this->addElement($fNRuc);
        
        // Fecha
        $fBirthDate = new Zend_Form_Element_Hidden('fh_pub');
        $fBirthDateVal = new Zend_Validate_NotEmpty();
        $fBirthDate->addValidator($fBirthDateVal, true);
        $this->addElement($fBirthDate);
        
        //check
        $e = new Zend_Form_Element_Checkbox('tipobusq');
        $this->addElement($e);
        
        //text
        $e = new Zend_Form_Element_Text('cod_ade');
        $e->setAttrib('maxLength', $this->_maxlengthCodigoAd);
        $v = new Zend_Validate_StringLength(
            array(
                'min' => '2', 'max' => $this->_maxlengthCodigoAd,
                'encoding' => $this->_config->resources->view->charset
            )
        );
        $e->addValidator($v);
        $this->addElement($e);
        //hash
        $e = new Zend_Form_Element_Hash('token');
        $e->setSalt(md5(uniqid(rand(), TRUE)));
        $e->setTimeout(1800); // 30 min
        $e->removeDecorator('Errors');
        $this->addElement($e);
    }

}

