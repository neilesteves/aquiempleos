<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminAvisoCallcenter
 *
 * @author ronald
 */
class Application_Form_AdminAvisoCallcenter extends App_Form
{
    //Max Length
    private $_maxlengthNombreRa = '80';
    private $_maxlengthNumRuc   = '11';
    private $_maxlengthUrlId    = '6';
    private $_maxlengthCodigoAd = '8';

    public function init()
    {
        parent::init();
        //tipo_destaque
        $destaques_impreso = array(
            'ALL' => 'Todos',
            'clasificado' => 'Lineales',
            '2' => 'Insertos',
            '1' => 'Desplegados',
        );
        $destaques         = array(
            'ALL' => 'Todos',
            '6' => 'Sin Destaque',
            '2' => 'Destaque Plata',
            '1' => 'Destaque Oro',
        );
        $estadoLs          = array(
            'ALL' => 'Todos',
            'registrado' => 'Registrado',
            'pendiente_pago' => 'Pendiente de Pago',
            'extornado' => 'Extornado',
            'pagado' => 'Pagado',
            //'publicado' => 'Publicado',
            'dado_baja' => 'Dado de Baja',
            'vencido' => 'Vencido',
            'extendido' => 'Extendido',
            'baneado' => 'Baneado',
        );
        $ftipo_destaquel   = new Zend_Form_Element_Select('tipo_destaque');
        $ftipo_destaquel->setAttrib('maxLength', $this->_maxlengthNombreRa);
        $ftipo_destaquel->addMultiOption('', 'Seleccione tipo destaque');
        $ftipo_destaquel->addMultiOptions($destaques);
        $this->addElement($ftipo_destaquel);

        //tipo_impreso
        $tipo_impreso = new Zend_Form_Element_Select('tipo_impreso');
        $tipo_impreso->setAttrib('maxLength', $this->_maxlengthNumRuc);
        $tipo_impreso->addValidator(new Zend_Validate_NotEmpty(), true);
        $tipo_impreso->addMultiOption('', 'Seleccione tipo impreso');
        $tipo_impreso->addMultiOptions($destaques_impreso);
        $this->addElement($tipo_impreso);

        //estado
        $estado = new Zend_Form_Element_Select('estado');
        $estado->addValidator(new Zend_Validate_NotEmpty(), true);
        $estado->addMultiOption('', 'Seleccione estado');
        $estado->addMultiOptions($estadoLs);
        $this->addElement($estado);

        // Fecha inicio
        $fBirthDate    = new Zend_Form_Element_Text('fh_pub');
        $fBirthDateVal = new Zend_Validate_NotEmpty();
        $fBirthDate->addValidator($fBirthDateVal, true);
        $this->addElement($fBirthDate);

        // Fecha fim
        $fBirthDate    = new Zend_Form_Element_Text('fh_pub_fin');
        $fBirthDateVal = new Zend_Validate_NotEmpty();
        $fBirthDate->addValidator($fBirthDateVal, true);
        $this->addElement($fBirthDate);

        //hash
        $e = new Zend_Form_Element_Hash('token');
        $e->setSalt(md5(uniqid(rand(), TRUE)));
        $e->setTimeout(1800); // 30 min
        $e->removeDecorator('Errors');
        $this->addElement($e);
    }
}