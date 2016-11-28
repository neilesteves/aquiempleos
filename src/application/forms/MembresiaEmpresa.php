<?php

class Application_Form_MembresiaEmpresa extends App_Form
{
    //Max
    private $_maxlengthFecha = '10';
    private $_maxlengthMonto = '10';
    private $_maxlengthMontoMod = '7';
    private $_maxlengthNroContrato = '10';
    
    public function init()
    {
        parent::init();
        
        $fIni = new Zend_Form_Element_Text('txtfecini');
        $fIni->setRequired();
        $fIni->setAttrib('maxLength', $this->_maxlengthFecha);
        $fIni->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthFecha,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        /*$f = new Zend_Validate_Date();
        $f->setFormat('dd/MM/yyyy');*/
        $fIni->addValidator(new Zend_Validate_Date('DD/MM/YYYY'));
        $fIni->errMsg = 'Fecha Incorrecta';
        $this->addElement($fIni);
        
        $fFin = new Zend_Form_Element_Text('txtfecfin');
        $fFin->setRequired();
        $fFin->setAttrib('maxLength', $this->_maxlengthFecha);
        $fFin->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthFecha,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        /*$f = new Zend_Validate_Date();
        $f->setFormat('dd/MM/yyyy');*/
        $fFin->addValidator(new Zend_Validate_Date('DD/MM/YYYY'));
        $fFin->errMsg = 'Fecha Incorrecta';
        $this->addElement($fFin);
        
        $cboMemb = new Zend_Form_Element_Select('id_membresia');
        $cboMemb->setRequired();
        $cboMemb->addMultiOption('', '.:: Seleccione ::.');
        //$cboMemb->addMultiOptions(Application_Model_Membresia::getMembresias());
        $cboMemb->errMsg = $this->_mensajeRequired;
        $this->addElement($cboMemb);
        
        $fMnto = new Zend_Form_Element_Text('txtmonto');
        $fMnto->setRequired();
        $fMnto->setAttrib('maxLength', $this->_maxlengthMonto);
        $fMnto->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthMonto,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        //$fMnto->setAttrib('readonly', true);
        /*$f = new Zend_Validate_Int();
        $fIni->addValidator($f);*/
        $fMnto->errMsg = 'Ingresar Monto';
        $this->addElement($fMnto);
        
        $cboEst = new Zend_Form_Element_Select('cboestado');
        $cboEst->setRequired();
        $cboEst->addMultiOption('', '.:: Seleccione ::.');
        $cboEst->addMultiOption('vigente', 'Activo');
        $cboEst->addMultiOption('no vigente', 'Inactivo');
        $cboEst->errMsg = $this->_mensajeRequired;
        $this->addElement($cboEst);
        
        $cboContact = new Zend_Form_Element_Select('cbotipo');
        $cboContact->setRequired();
        $cboContact->addMultiOption('', '.:: Seleccione ::.');
        $cboContact->addMultiOption('membresia', 'Membresía');
        $cboContact->addMultiOption('bonificado', 'Bonificado');
        $cboContact->errMsg = $this->_mensajeRequired;
        $this->addElement($cboContact);
        
        
        $fToken = new Zend_Form_Element_Hidden('tok');
        $fToken->setRequired();
        $tok = crypt(date('dmYH'), '$2a$07$'.md5(uniqid(rand(), true)).'$');
        $fToken->setValue($tok);
        $this->addElement($fToken);
        
        
        $fNumContrato = new Zend_Form_Element_Text('txtcontrato');
        /*$fNumContrato->setRequired();
        $fNumContrato->setAttrib('maxLength', $this->_maxlengthNroContrato);
        $fNumContrato->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthNroContrato,
                'encoding' => $this->_config->resources->view->charset)
            )
        );*/
        //$fMnto->setAttrib('readonly', true);
        /*$f = new Zend_Validate_Int();
        $fIni->addValidator($f);*/
        //$falpha = new Zend_Validate_Alnum();
        //$fNumContrato->addValidator($falpha);
        //$fNumContrato->errMsg = 'Ingresar Nro. de Contrato';
        $this->addElement($fNumContrato);
        
    }
    
    public function setValFechaInicio($value = '')
    {
        $fIni = $this->getElement('txtfecini');
        $fIni->setValue($value);
    }
    
    public function setValFechaFin($value = '')
    {
        $fFin = $this->getElement('txtfecfin');
        $fFin->setValue($value);
    }
    
    public function setValMembresia($value = '')
    {
        $cboMemb = $this->getElement('id_membresia');
        $cboMemb->setValue($value);
    }
    
    public function setLoadMembresiaByTipo($tipo = '', $value = '')
    {
        $config = Zend_Registry::get("config");
        $activo = $config->configMembresia->soloActivos;
        
        $cboMem = $this->getElement('id_membresia');
        $cboMem->addMultiOptions(Application_Model_Membresia::getMembresiasByTipo($activo, $tipo));
        $cboMem->setValue($value);
    }
    
    public function isValid($data)
    {
        $arrayDateIni = explode('/', $data['txtfecini']);
        $arrayDateFin = explode('/', $data['txtfecfin']);
        
        $digitales = array(
            7,
            Application_Model_Membresia::MENSUAL,
            Application_Model_Membresia::DIGITAL
        );
        
        //Empty
        if (count($arrayDateIni) == 1 && strlen($arrayDateIni['0']) == 0 ) {
            $this->getElement('txtfecini')->isError = 1;
            return false;
        }
        
        //datos incompletos
        if (count($arrayDateIni)!= 3 && strlen($arrayDateIni['0']) != 0) {
            $this->getElement('txtfecini')->isError = 2;
            return false;
        }
        
        if (count($arrayDateFin) == 1 && strlen($arrayDateFin['0']) == 0) {
            $this->getElement('txtfecfin')->isError = 1;
            return false;
        }
        
        //Validacion Fecha Fin > Fecha Inicio
        if (count($arrayDateFin)!= 3) {
            $this->getElement('txtfecfin')->isError = 2;
            return false;
        }
        
        if (count($arrayDateIni) == 3 && count($arrayDateFin)== 3) {
            $fInicio = new DateTime(str_replace('/', '-', $data['txtfecini']));
            $fFin = new DateTime(str_replace('/', '-', $data['txtfecfin']));
            $fDiaActual = new DateTime(date('d-m-Y'));
            if ($fFin<$fInicio) {
                $this->getElement('txtfecfin')->isError = 3;
                return false;
            }
            if ($fFin < $fDiaActual) {
                $this->getElement('txtfecfin')->isError = 3;
                return false;
            }
            
        }
        
        if (isset($data['txtmonto']) && !in_array($data['id_membresia'],$digitales)) {
            $arrayMonto = explode('.', $data['txtmonto']);
            $maxCount = count($arrayMonto);
            
            if (strlen($arrayMonto['0']) >=8 ) {
                $this->getElement('txtmonto')->clearValidators();
                $this->getElement('txtmonto')->addValidator(
                    new Zend_Validate_StringLength(
                        array(
                            'min' => '1', 
                            'max' => $this->_maxlengthMontoMod,
                            'encoding' => $this->_config->resources->view->charset
                        )
                    ) 
                );
                $this->getElement('txtmonto')->errMsg ="Monto Erroneo";
            } else {
                $this->getElement('txtmonto')->clearValidators();
                $this->getElement('txtmonto')->addValidator(
                    new Zend_Validate_StringLength(
                        array(
                            'min' => '1', 
                            'max' => $this->_maxlengthMonto,
                            'encoding' => $this->_config->resources->view->charset
                        )
                    ) 
                );
            }
        }
        
        
        if (isset($data['cboestado']) && ($data['cboestado']=='vigente')) {
            
            if ( ($data['cbotipo'] == 'membresia') && (!in_array($data['id_membresia'],$digitales)) ) {
                
                $this->getElement('txtcontrato')->clearValidators();
                $this->getElement('txtcontrato')->setRequired();
                $this->getElement('txtcontrato')->addValidator(
                    new Zend_Validate_StringLength(
                        array(
                            'min' => '1', 
                            'max' => $this->_maxlengthNroContrato,
                            'encoding' => $this->_config->resources->view->charset
                        )
                    ) 
                );
                $this->getElement('txtcontrato')->errMsg ="Ingresar Nro. de Contrato";
                
            } else {
                $this->getElement('txtcontrato')->clearValidators();
            }
        }
        
        // Validamos el token enviado
        if ( crypt(date('dmYH'), $data['tok']) !== $data['tok'] ) {
            return false;
        }
        
        
        return parent::isValid($data);
    }

}

