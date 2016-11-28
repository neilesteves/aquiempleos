<?php


class Application_Form_ReportarAbuso extends App_Form
{
    public function init()
    {
        parent::init();
        
        //Elementos de la opcion
        $categoriaAbuso = new Application_Model_AbusoCategoria();
        $listacategorias = $categoriaAbuso->getCategoriasAviso();
        $e = new Zend_Form_Element_Radio('tipo_abuso');
        $e->addMultiOptions($listacategorias);
        $e->addMultiOption('-1', 'Otro motivo.');
        $this->addElement($e);
        
        //Descripcion
        $e = new Zend_Form_Element_Textarea('comentario');
        $e->setValue('Detalla el motivo que has seleccionado (Opcional)');
        $this->addElement($e);
        
        //Token
        $e = new Zend_Form_Element_Hash('tok');
        $e->setTimeout(300); // 5min
        $this->addElement($e);
              
    }

    public function isValid($data)
    {
        // @codingStandardsIgnoreStart
        $message = "Detalla el motivo que has seleccionado (Opcional)";
        if ($data['tipo_abuso'] == -1) {
            $this->comentario->errMsg = $this->_mensajeRequired;
            $this->comentario->addValidator(
                new App_Validate_NoEquals($message)
            );
        }
        // @codingStandardsIgnoreEnd
        return parent::isValid($data);
    }
}