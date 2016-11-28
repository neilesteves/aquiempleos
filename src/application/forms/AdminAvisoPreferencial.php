<?php

/**
 * Description of Categoria
 *
 * @author Usuario
 */
class Application_Form_AdminAvisoPreferencial extends App_Form
{
    //Max Length
    private $_maxlengthPuesto = '56';
    private $_maxlengthNumRuc = '11';
    private $_maxlengthCodigoAd = '8';
    public function init()
    {
        parent::init();
        
        //Nombre del Puesto
        $e = new Zend_Form_Element_Text('nom_puesto');
        $e->setAttrib('maxLength', $this->_maxlengthPuesto);
        $v = new Zend_Validate_StringLength(
            array(
                'min' => '2', 'max' => $this->_maxlengthPuesto,
                'encoding' => $this->_config->resources->view->charset
            )
        );
        $e->errMsg = "Debe ingresar un nombre de puesto";
        $e->addValidator($v);
        $this->addElement($e);
        
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
        //
        $fBirthDateVal = new Zend_Validate_NotEmpty();
        $fBirthDate->addValidator($fBirthDateVal, true);
        $this->addElement($fBirthDate);
        
        
        $listaOrigen = array(
        Application_Model_CompraAdecsysCodigo::MEDIO_PUB_APTITUS => 'Aptitus',
        Application_Model_CompraAdecsysCodigo::MEDIO_PUB_TALAN => 'El Talan' 
        );
        $e = new Zend_Form_Element_Select('origen');
        $e->addMultiOptions($listaOrigen);
        $v = new Zend_Validate_InArray(array_keys($listaOrigen));
        $e->addValidator($v);
        $this->addElement($e);
        
        $e = new Zend_Form_Element_Checkbox('tipobusq');
        $this->addElement($e);
        
        
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
        
        
    }

}

