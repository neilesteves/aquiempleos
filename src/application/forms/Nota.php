<?php

/**
 * Description of Form Paso2 Section Estudio
 *
 * @author Jesus
 */
class Application_Form_Nota extends App_Form
{
    
    public function __construct($hasHiddenId = false)
    {
        parent::__construct();
        if ($hasHiddenId) {
            $this->addNotaId();
        }
    }
    
    public function init()
    {
        parent::init();
        $this->setMethod('post');

        //nota
        
        $e = new Zend_Form_Element_Textarea('text');
        $this->addElement($e);

        
        $excludeExtension = array ('exe','com','php','pif','pl','jar','dll','sh');
        $a = new Zend_Form_Element_File('path');
        $a->setDestination($this->_config->urls->app->elementsNotaRoot);
        $a->addValidator(new Zend_Validate_File_Upload());
        $a->addValidator(
            new Zend_Validate_File_Size(
                array('max'=>$this->_config->app->maxSizeFile)
            )
        );
        $val = new Zend_Validate_File_ExcludeExtension($excludeExtension);
        $val->setExtension($excludeExtension);
        $val->setCase(false);
        $a->addValidator($val);
        $this->addElement($a);

    }
    
    public function addNotaId()
    {
        $e = new Zend_Form_Element_Hidden('id_nota');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);
    }
    
    public function setHiddenId($id)
    {
        $e = $this->getElement('id_nota');
        $e->setValue($id);
    }
}