<?php

/**
 * Description of Form Login
 *
 * @author Jesus
 */
class Application_Form_Login extends App_Form
{
    const ROL_POSTULANTE = 'postulante';
    const ROL_EMPRESA = 'empresa';
    const ROL_EMPRESA_ADMIN = 'empresa-admin'; // creador
    const ROL_EMPRESA_USUARIO = 'empresa-usuario'; // no creador
    const ROL_ADMIN = 'admin';
    const ROL_ADMIN_MASTER = 'admin-master'; //prueba
    const ROL_ADMIN_CALLCENTER = 'admin-callcenter';

    const ROL_ADMIN_TESTIMONIOS = 'admin-testimonio';
    const ROL_ADMIN_EMPRESASPORTADA = 'admin-empresas-portada';

    const ROL_ADMIN_SOPORTE = 'admin-soporte';
    const ROL_ADMIN_DIGITADOR = 'admin-digitador';
    const ROL_ADMIN_MODERADOR = 'admin-moderador';

    public function init()
    {
        parent::init();

        // Email
        $e = new Zend_Form_Element_Text('userEmail');
        $e->setRequired();
        //$e->setAttrib("autocomplete", "off");
        $e->addValidator(new Zend_Validate_EmailAddress(), true);
        $e->addValidator(new Zend_Validate_NotEmpty(), true);
        $e->errMsg = 'No parece ser un correo electrónico valido';
        $this->addElement($e);

        // Clave
        $e = new Zend_Form_Element_Password('userPass');
        $e->setRequired();
        $e->addValidator(new Zend_Validate_NotEmpty());
        $v = new Zend_Validate_StringLength(
            array('min' => 6, 'max' => 32,
            'encoding' => $this->_config->resources->view->charset)
        );
        $e->addValidator($v);
        $e->setValue('Ingresa tu clave');
        $e->errMsg = '¡Usa de 6 a 32 caracteres!';
        $this->addElement($e);

        //Checkbox save Session
        $e = new Zend_Form_Element_Checkbox('save');
        $e->setValue(false);
        $this->addElement($e);

        // CSFR protection
        //$e = new Zend_Form_Element_Hash('auth_token');
        $e = new Zend_Form_Element_Hidden('auth_token');
        $hash_blow_fish = crypt('l0gInAPtiTus', '$2a$07$'.md5(uniqid(rand(), true)).'$');
        $e->setValue($hash_blow_fish);
        $this->addElement($e);

        $e = new Zend_Form_Element_Hidden('return');
        $return = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();

        if ($return == '/empresa' || $return == '/empresa/') {
            $return = '/empresa/mi-cuenta';
        }
        
        if ($return == '/admin' || $return == '/admin/') {            
            $return = '/admin/gestion';
        }
        
        if ($return == '/') {
            $return .= 'mi-cuenta';
        }
        
        if ($return == '/registro') {
            $return = '/mi-cuenta';
        }
        
        $retur = explode('/', $return);
        if ($retur[1]=='ofertas-de-trabajo') {
          //$return = '/mi-cuenta';
        }
        
        if( $retur[1]=='buscar'){
          $return = '/buscar';
        }

        if ( $retur[1]=='empresa'){
            $return = '/mi-cuenta';
        }
        
       
        $e->setValue($return);
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);

        $e = new Zend_Form_Element_Hidden('returnFB');
        $returnFacebok = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
        $helper= new App_Controller_Action_Helper_Util();

        if ($returnFacebok == '/empresa' || $returnFacebok == '/empresa/') {            
             $returnFacebok = str_replace('/', '', $returnFacebok);
            $returnFacebok = '/'.$returnFacebok.'/mi-cuenta';

        }
        if ($returnFacebok == '/admin' || $returnFacebok == '/admin/') {            
              $returnFacebok = str_replace('/', '', $returnFacebok);
              $returnFacebok = '/'.$returnFacebok.'/gestion';

        }
        if ($returnFacebok == '/') {
            $returnFacebok .= 'mi-cuenta';
        }
        if ($returnFacebok == '/registro') {
            $returnFacebok = '/mi-cuenta';
        }
        
        $returnFb = explode('/', $returnFacebok );
        if ($returnFb[1] == 'ofertas-de-trabajo') {
            $returnFacebok=urlencode($helper->codifica(($returnFacebok)));
        }
          $retur=explode('/', $return );
        if ($returnFb[1]=='buscar') {          
            $returnFacebok=urlencode($helper->codifica(($returnFacebok)));
        }

        if ($returnFb[1]=='empresa') {
            $returnFacebok = '/mi-cuenta';
        }
                      
        $e->setValue($returnFacebok);
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);

        //Submit
        $e = new Zend_Form_Element_Submit('Ingresar');
        $e->setLabel('Ingresar');
        $this->addElement($e);
    }

    public function setType($type)
    {
        $e = new Zend_Form_Element_Hidden('tipo');
        $e->setValue($type);
        $this->addElement($e);
        if ($type == self::ROL_POSTULANTE) {
            $emailMsg = 'Ingresa tu e-mail';
        } else {
            $emailMsg = 'Ingrese su e-mail';
        }
        $this->getElement('userEmail')->setAttrib('placeholder',$emailMsg);
        return $this;
    }

    public static function factory($type)
    {
        $form = new self();
        return $form->setType($type);
    }

}