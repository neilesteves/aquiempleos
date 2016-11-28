<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MedioPago
 *
 * @author Computer
 */
class Application_Model_MedioPago extends App_Model_Enum
{

    public function get()
    {
        return array(
            Application_Model_Compra::FORMA_PAGO_PAGO_EFECTIVO => 'Pago Efectivo',
            Application_Model_Compra::FORMA_PAGO_VISA => 'Visa',
            Application_Model_Compra::FORMA_PAGO_MASTER_CARD => 'Master Card',
            Application_Model_Compra::FORMA_PAGO_GRATUITO => 'Gratuito',
            Application_Model_Compra::FORMA_PAGO_AGENCIA => 'Agencia',
            Application_Model_Compra::FORMA_PAGO_MEMBRESIA => 'Membresía',
            Application_Model_Compra::FORMA_PAGO_CREDITO => 'Crédito',
            Application_Model_Compra::FORMA_PAGO_CREDOMATIC => 'Credomatic',
            Application_Model_Compra::FORMA_PAGO_PUNTO_FACIL => 'Punto facil',
            Application_Model_Compra::FORMA_PAGO_PAGO_VENTANILLA => 'Pago Ventanilla'
        );
    }
}