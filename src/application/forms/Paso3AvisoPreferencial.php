<?php

class Application_Form_Paso3AvisoPreferencial extends App_Form
{
    private $_maxlengthContenidoAviso = '500';
    private $_maxlengthNotaDiseno = '500';
    
    public function init()
    {
        parent::init();
    
        // Combo País
        $fRadioMultiOptions = new Zend_Form_Element_Radio('tipo_diseno');
        $fRadioMultiOptions->setRequired();
        $fRadioMultiOptions->addMultiOptions(
            array(
                Application_Model_AnuncioImpreso::TIPO_DISENIO_PROPIO =>
                    " Quiero usar un diseñado que ya tengo listo. (Enviar archivos)", 
                Application_Model_AnuncioImpreso::TIPO_DISENIO_PRE_DISENIADO =>
                    " Quiero usar una plantilla y enviar archivos para el diseño del aviso."
//                Application_Model_AnuncioImpreso::TIPO_DISEÑO_SCOT =>
//                    " Quiero usar una plantilla sin enviar ningún archivo extra." 
            )
        );
        $fRadioMultiOptions->setAttrib('label_class', 'labelSPAP');
        $fRadioMultiOptions->setSeparator('');
        $this->addElement($fRadioMultiOptions);
                        
        $fContenidoAviso = new Zend_Form_Element_Textarea('contenido_aviso');
        $fContenidoAviso->setAttrib('maxlength', $this->_maxlengthContenidoAviso);
        //$fContenidoAviso->setValue('Contenido');
        $this->addElement($fContenidoAviso);
        
        $fNotaDisenadorAviso = new Zend_Form_Element_Textarea('nota_diseno');
        $fNotaDisenadorAviso->setAttrib('maxlength', $this->_maxlengthNotaDiseno);
        //$fNotaDisenadorAviso->setValue('Escribir nota');
        $this->addElement($fNotaDisenadorAviso);
        
    }
}