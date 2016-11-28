<?php

/**
 * Description of Categoria
 *
 * @author Usuario
 */
class Application_Form_AdminUsuario extends App_Form
{
    //Max Length
    private $_maxlengthNombre = '28';
    private $_maxlengthApellido = '75';
    private $_maxlengthTipoDocDni = '8';
    private $_maxlengthTipoDocCe = '10';
    private $_maxlengthEmail = '75';
    
    //@codingStandardsIgnoreStart
    public static $valorDocumento;
    //@codingStandardsIgnoreEnd
    
    
    public function init()
    {
        parent::init();

        
        // Nombre
        $fNames = new Zend_Form_Element_Text('nombre');
        $fNames->setRequired();
        $fNames->setAttrib('maxLength', $this->_maxlengthNombre);
        $fNames->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthNombre,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        
        $fNamesVal = new Zend_Validate_NotEmpty();
        $fNames->addValidator($fNamesVal);
        $fNames->errMsg = '¡Ingrese Nombre Correcto!';
        $this->addElement($fNames);
        
        // Apellido
        $fSurname = new Zend_Form_Element_Text('apellido');
        $fSurname->setRequired();
        $fSurname->setAttrib('maxLength', $this->_maxlengthNombre);
        $fSurname->addValidator(
            new Zend_Validate_StringLength(
                array('min' => '1', 'max' => $this->_maxlengthNombre,
                'encoding' => $this->_config->resources->view->charset)
            )
        );
        $fSurnameVal = new Zend_Validate_NotEmpty();
        $fSurname->addValidator($fSurnameVal);
        $fSurname->errMsg = '¡Ingrese Apellido Correcto!';
        $this->addElement($fSurname);

        //ROL
        $arrayRol = 
            array(
                Application_Form_Login::ROL_ADMIN_CALLCENTER => 'Call Center',
                Application_Form_Login::ROL_ADMIN_DIGITADOR => 
                    ucfirst(substr(Application_Form_Login::ROL_ADMIN_DIGITADOR, 6)),
                Application_Form_Login::ROL_ADMIN_MASTER => 
                    ucfirst(substr(Application_Form_Login::ROL_ADMIN_MASTER, 6)),
                Application_Form_Login::ROL_ADMIN_MODERADOR => 
                    ucfirst(substr(Application_Form_Login::ROL_ADMIN_MODERADOR, 6)),
                Application_Form_Login::ROL_ADMIN_SOPORTE => 
                    ucfirst(substr(Application_Form_Login::ROL_ADMIN_SOPORTE, 6))
            );
        $cboEstado = new Zend_Form_Element_Select('rol');
        $cboEstado->addMultiOptions($arrayRol);
        $cboEstadoVal = new Zend_Validate_InArray(array_keys($arrayRol));
        $cboEstado->addValidator($cboEstadoVal);
        $this->addElement($cboEstado);
        
        //estado
        $cboEstado = new Zend_Form_Element_Select('activo');
        $cboEstado->addMultiOption('1', 'Activo');
        $cboEstado->addMultiOption('0', 'Inactivo');
        $this->addElement($cboEstado);
                
        //token
        $fToken = new Zend_Form_Element_Hidden('tok');
        $fToken->setRequired();
        $tok = crypt(date('dmYH'), '$2a$07$'.md5(uniqid(rand(), true)).'$');
        $fToken->setValue($tok);
        $this->addElement($fToken);
        
        
    }
    
    public function isValid($data) {
        
        if (crypt(date('dmYH'), $data['tok']) !== $data['tok']) {
            return false;
        }
        
        return parent::isValid($data);
    }
}

