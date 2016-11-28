<?php

/**
 * Description of Form Paso3 Publicar Aviso
 *
 * @author Jesus
 */
class Application_Form_Paso3PublicarAviso extends App_Form
{
    public function init()
    {
        parent::init();
        $e = new Zend_Form_Element_Textarea('texto');
        $e->setRequired();
        $e->errMsg = "El texto introducido no es valido.";
        $this->addElement($e);
        
        $e = new Zend_Form_Element_Hidden('id_aviso');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);
        
        $e = new Zend_Form_Element_Hash('tok');        
        $e->setTimeout(900); //15 minutos
        $this->addElement($e);
        
    }
    
    /**
     * Validador de maximo de palabras segun producto
     * 
     * @param int $maxPalabras
     */
    public function agregarValidadorPalabras($maxPalabras)
    {
        $texto = $this->texto->getValue();
        $this->texto->addValidator(
            new Zend_Validate_Callback(
                function($texto) use ($maxPalabras)
                {
                    $helper = new App_Controller_Action_Helper_Contador();
                    $cantPalabras = $helper->contarPalabras($texto);
                    return $maxPalabras >= $cantPalabras;
                }
            )
        );
    }
}