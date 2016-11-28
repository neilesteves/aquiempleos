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
class Application_Model_MedioPublicacion extends App_Model_Enum
{
    public function get()
    {
        return array(
            Application_Model_Tarifa::MEDIOPUB_APTITUS => 'APTiTUS',
            Application_Model_Tarifa::MEDIOPUB_TALAN => 'El Talán',
            Application_Model_Tarifa::MEDIOPUB_APTITUS_TALAN => 'APTiTUS y El Talán'
        );
    }
}