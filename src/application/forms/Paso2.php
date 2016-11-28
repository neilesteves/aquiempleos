<?php

/**
 * Description of Form Paso2
 *
 * @author Jesus
 */
class Application_Form_Paso2 extends App_Form
{
    public function init()
    {
        parent::init();
        //Checkbox Experiencia
        $e = new Zend_Form_Element_Checkbox('fNoExp');
        $this->addElement($e);

        //Checkbox Estudios
        $e = new Zend_Form_Element_Checkbox('fNoEst');
        $this->addElement($e);

        //Input con direccion del CV
//        $e = new Zend_Form_Element_Text('repCV');
//        $e->setValue("Puedes subir tu archivo PDF, DOC o DOCX");
//        $this->addElement($e);
//
//        //Archivo CV
//        $e = new Zend_Form_Element_File('pathCv');
//        $e->setDestination($this->_config->urls->app->elementsCvRoot);
//        $v = new Zend_Validate_File_Upload();
//        $e->addValidator($v);
//        //3145728
//        $v = new Zend_Validate_File_Size(array('max'=>$this->_config->app->maxSizeFile)); //2MB
//        $e->addValidator($v);
//        $e->addValidator('Extension', false, 'doc,pdf,docx');
//        $e->errMsg = "El archivo es incorrecto";
//        $this->addElement($e);

        //Submit
        $e = new Zend_Form_Element_Submit('Submit');
        $this->addElement($e);
    }
    
    public static $errorsCv = array(
        'fileExtensionFalse' => 'Archivo debe tener extensiones .doc, .docx, .pdf',
        'fileSizeTooBig' => 'Tamaño de archivo sobrepasa el limite permitido.',
        'fileUploadErrorIniSize' => 'Tamaño de archivo sobrepasa el limite permitido.'
    );
}