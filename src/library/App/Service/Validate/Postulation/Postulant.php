<?php

/**
 * validaciones sobre una postulacion
 *
 * @author Carlos Muñoz Ramirez, <camura8503@gmail.com>
 */

class App_Service_Validate_Postulation_Postulant 
    extends App_Service_Validate_Postulation
{
    private $_adId              = null;
    private $_postulantId       = null;    
    
    public function __construct($adId, $postulantId)
    {
        $this->_model = new Application_Model_Postulacion;
        
        $this->_adId         = $adId;
        $this->_postulantId  = $postulantId;
        
        $this->_getData();
    }
    
    protected function _getData()
    {
        $postulation = $this->_model->obtenerPorAnuncioYPostulante(
                $this->_adId, $this->_postulantId);

        if (!isset($postulation))
            return;

        $this->_data = $postulation->toArray();
    }
}