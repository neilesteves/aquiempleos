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
class Application_Model_EstadoCompra extends App_Model_Enum
{
    public function get()
    {
        return array(
            Application_Model_Compra::ESTADO_ANULADO => 'Anulado',
            Application_Model_Compra::ESTADO_EXPIRADO => 'Expirado',
            Application_Model_Compra::ESTADO_EXTORNADO => 'Extornado',
            Application_Model_Compra::ESTADO_PAGADO => 'Pagado',
            Application_Model_Compra::ESTADO_PENDIENTE_PAGO => 'Pendiente de Pago'
        );
    }
}
