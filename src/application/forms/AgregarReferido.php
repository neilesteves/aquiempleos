<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Application_Form_AgregarReferido extends App_Form
{   
    public function init()
    {
        parent::init();
        
        $maxlenghtTelefono  = $this->_config->app->maxLengthTelephone;
        $cvDestino          = $this->_config->urls->app->elementsCvRootTmp;
        $maxFileSize        = $this->_config->app->maxSizeFile;
        
        $email = new Zend_Form_Element_Text('email');
        $email->setRequired()
            ->addValidator('EmailAddress')
            ->removeDecorator('label')
            ->removeDecorator('htmlTag')
            ->addFilter(new Zend_Filter_StringToLower());
        
        $sexo = new Zend_Form_Element_Radio('sexo');        
        $sexo->setRequired()
            ->addMultiOption("M", "Masculino")
            ->addMultiOption("F", "Femenino")
            ->removeDecorator('label')
            ->removeDecorator('htmlTag')
            ->setSeparator('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;')
            ->setValue("M");

        $telefono = new Zend_Form_Element_Text('telefono');
        $telefono->setRequired()
            ->setAttrib('maxlength', $maxlenghtTelefono)
            ->removeDecorator('label')
            ->removeDecorator('htmlTag')
            ->addValidator('int')
            ->addValidator('StringLength', array('max' => $maxlenghtTelefono));
        
        $nombres = new Zend_Form_Element_Text('nombres');
        $nombres->setRequired()
            ->setAttrib('maxlength', 100)
            ->removeDecorator('label')
            ->removeDecorator('htmlTag')
            ->addValidator('StringLength', array('max' => 100));
        
        $apellidos = new Zend_Form_Element_Text('apellidos');
        $apellidos->setRequired()
            ->setAttrib('maxlength', 100)
            ->removeDecorator('label')
            ->removeDecorator('htmlTag')
            ->addValidator('StringLength', array('max' => 100));
        
        $path_cv = new Zend_Form_Element_File('path_cv');
        $path_cv->setDestination($cvDestino)
            ->addValidator('Size', false, $maxFileSize)
            ->addValidator('Count', false, 1)
            ->removeDecorator('label')
            ->removeDecorator('htmlTag')
            ->addValidator('Extension', false, 'doc,pdf,docx');        

        $this->addElement($email)
            ->addElement($sexo)
            ->addElement($telefono)
            ->addElement($nombres)
            ->addElement($apellidos)
            ->addElement($path_cv);       
    }
}