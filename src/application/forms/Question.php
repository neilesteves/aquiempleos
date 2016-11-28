<?php
/**
 * Formulario de Cuestionario al momento de postular.
 *
 * @author Jesus Fabian
 */
class Application_Form_Question extends App_Form
{   
    public static $_defaultPresentacion = '';
    private $_maxlengthPresentacion = '750';
    private $_id;



    public function __construct($id = null) {
        parent::__construct();
        $this->_id = $id;     
    }



    public function init() {
        parent::init();
        
        $mensaje = new Zend_Form_Element_Textarea('mensaje');
        $mensaje->setValue(self::$_defaultPresentacion);
        $mensaje->addValidator(
                new Zend_Validate_StringLength(
                array(
                    'min' => '0',
                    'max' => $this->_maxlengthPresentacion,
                    'encoding' => $this->_config->resources->view->charset)
                )
        );
        $mensaje->setAttrib('data-maxlength', $this->_maxlengthPresentacion);
        $mensaje->errMsg = 'Ingrese máximo '.$this->_maxlengthPresentacion.' caracteres';
        $this->addElement($mensaje);      
        
        if(!empty($this->_id)){
        // con pregunta
        $pregunta = new Zend_Form_Element_Checkbox('pregunta');
        $pregunta->setValue(true);
        $pregunta->clearValidators();
        $this->addElement($pregunta);
        }
        $pregunta = new Zend_Form_Element_Hidden('id_mensaje');
        $pregunta->clearValidators();
        $this->addElement($pregunta);
        
        $e = new Zend_Form_Element_Hash('hidAuthTokenCuestion');     
        $this->addElement($e);
    }



    public function restMensaje(){
        $this->removeElement('pregunta');
    }
}