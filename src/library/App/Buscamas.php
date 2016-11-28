<?php

class App_Buscamas extends Zend_Application {

    public function enviar($id) {
        $anuncioWeb = new Application_Model_AnuncioWeb;
        $datos = $anuncioWeb->servicioRestBuscaMas($id);
        
        //Si tiene más de una carrera
        $datos['carrera'] = explode(',', $datos['carrera']);
        foreach ($datos['carrera'] as $key => $value) 
            $data[] = $value;
        
        $datos['carrera'] = $data;
        try {

            $data = array('code' => 1,'description' => 'OK' ,'response' => $datos);
            
        } catch (Exception $exc) {
            $data = array('code' => 0,'description' => 'ERROR','response' => $exc->getMessage());
        }
        
        return $data;
    }

}

