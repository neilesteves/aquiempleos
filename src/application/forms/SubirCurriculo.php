<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SubirCurriculo
 *
 * @author Computer
 */
class Application_Form_SubirCurriculo extends App_Form
{
    
    public function __construct()
    {
        parent::__construct();
    }
    public function init()
    {
        parent::init();
        $this->setAction('/subir-cv');
        $this->setAttrib('enctype', 'multipart/form-data');
        $fCv = new Zend_Form_Element_File('path_cv');
        $fCv->setDestination($this->_config->urls->app->elementsCvRoot);
        $fCv->addValidator(
            new Zend_Validate_File_Size(array('max'=>$this->_config->app->maxSizeFile))
        );
        $fCv->addValidator('Extension', false, 'doc,pdf,docx');
        $fCv->addValidator('Count', false, array('min' =>1, 'max' => 1));
        $fCv->getValidator('Size')->setMessage('El límite del archivo es de 2MB');
        $fCv->getValidator('Extension')
            ->setMessage('Seleccione un archivo con extensión .doc,.pdf,.docx');
        $fCv->getValidator('Count')
            ->setMessage('Seleccione un archivo');
        $this->addElement($fCv);
    }
}