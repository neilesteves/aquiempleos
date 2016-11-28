<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Application_Form_RegistrarTestimonio extends App_Form
{

    public function init()
    {
        parent::init();
        //$this->setAction('');//('registrar-testimonio');
        $this->setAttrib('enctype', 'multipart/form-data');

        $config = Zend_Registry::get('config');    
        $elementOrden = array ();
        for ($a = 1; $a <= $this->_config->testimonios->cantidad->testimonio; $a++) {
            //if ($a == 0) {
                $elementOrden[$a] = 'Seleccionar';
            //} else {
                $elementOrden[$a] = $a;
            //}
        }
        $nombreEmpresa = new Zend_Form_Element_Text('razonsocial');
        $nombreEmpresa->addValidator(new Zend_Validate_NotEmpty());
        $nombreEmpresa->errMsg = "Debe ingresar el nombre de la Empresa.";
        $nombreEmpresa->setRequired(true);
        $this->addElement($nombreEmpresa);

        $ubicacion = new Zend_Form_Element_Text('ubicacion');
        $ubicacion->addValidator(new Zend_Validate_NotEmpty());
        $ubicacion->errMsg = "Debe ingresar la Ubicación.";
        $ubicacion->setRequired(true);
        $this->addElement($ubicacion);

        $testimonio = new Zend_Form_Element_Textarea('testimonio');
        $testimonio->addValidator(new Zend_Validate_NotEmpty());
        $testimonio->errMsg = "Debe ingresar el testimonio.";
        $testimonio->setRequired(true);
        $this->addElement($testimonio);

        $referente = new Zend_Form_Element_Text('referente');
        $referente->addValidator(new Zend_Validate_NotEmpty());
        $referente->errMsg = "Debe ingresar la referencia.";
        $referente->setRequired(true);
        $this->addElement($referente);
        
        $fToken = new Zend_Form_Element_Hidden('tok');        
        $tok = crypt(date('dmYH'), '$2a$07$'.md5(uniqid(rand(), true)).'$');
        $fToken->setValue($tok);
        $fToken->setRequired();
        $this->addElement($fToken);

        $cargo = new Zend_Form_Element_Text('cargo');
        $cargo->addValidator(new Zend_Validate_NotEmpty());
        $cargo->errMsg = "Debe ingresar el cargo.";
        $cargo->setRequired(true);
        $this->addElement($cargo);

        $estado = new Zend_Form_Element_Select('estado');
        
        $estado->addMultiOptions(
            array(
                //'0' => 'Seleccionar',
                Application_Model_Testimonio::ESTADO_ACTIVO => 'Activo',
                Application_Model_Testimonio::ESTADO_INACTIVO => 'Inactivo'
                )
        );
        $this->addElement($estado);

        $orden = new Zend_Form_Element_Select('orden');
        $orden->addMultiOptions($elementOrden);
        $this->addElement($orden);

        $registrar = new Zend_Form_Element_Submit('Grabar');
        $this->addElement($registrar);

    }
    
    public function isValid($data) 
    {
        
        if ( crypt(date('dmYH'),$data['tok'])  !== $data['tok'] ) {
            return false;
        }
        
        return parent::isValid($data);
    }

}

