<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AnuncioImpreso
 *
 * @author ronald
 */
class Application_Form_PublicarAvisoImpresoLineal extends App_Form
{
    private $_data;

    //put your code here
    public function __construct($data = array())
    {
        parent::__construct();
        $this->_data = $data;
    }

    public function init()
    {
        parent::init();
        // TexTArea Presentación
        //Logotipo
        $fimgImpreso = new Zend_Form_Element_File('path_foto');
        $fimgImpreso->setAttrib('class', 'inputN opacity btnPink right');
        $fimgImpreso->setDestination($this->_config->urls->app->elementsImpreso);
        $fimgImpreso->addValidator(
            new Zend_Validate_File_Size(array(
            'max' => 4194304))
        );
        $fimgImpreso->addValidator('Extension', false, 'jpg,jpeg,png');

        $fimgImpreso->getValidator('Size')->setMessage('Tamaño de Imagen debe ser menor a 500kb');
        $fimgImpreso->getValidator('Extension')
            ->setMessage('Seleccione un archivo con extensión .jpg,.jpeg,.png');
        $this->addElement($fimgImpreso);


        $texto = new Zend_Form_Element_Textarea('texto');
        $texto->setAttrib('rows', '6');
        $texto->setRequired();
        $this->addElement($texto);
    }

    public function addRadio($form, $filtro)
    {
        $e = new Zend_Form_Element_Radio($form);
        $i = 0;
        foreach ($this->_data["beneficio"] as $key => $value) {
            if ($filtro == $value['codigo']) {
                $val = $value['valor'];
                $nombre= $value['nombre'];
                if (strlen($value['valor']) == 1) {
                    $val = '0'.$value['valor'];
                }
                
                $e->setRequired();
                $e->addMultiOption($val,$nombre);
            }
        }
        $this->addElement($e);
    }
}