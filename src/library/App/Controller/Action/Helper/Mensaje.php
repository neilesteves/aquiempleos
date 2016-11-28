<?php

class App_Controller_Action_Helper_Mensaje 
    extends Zend_Controller_Action_Helper_Abstract
{
    private $_mensaje;
    private $_postulacion;
    public function __construct()
    {
        $this->_mensaje = new Application_Model_Mensaje();
        $this->_postulacion = new Application_Model_Postulacion();
        $this->_postulante = new Application_Model_Postulante();
    }
    
    public function actualizarCantMsjsPostulacion($idUsuario, $idPostulacion)
    {
        return $this->_postulacion->UpdateMsjsLeidos($idUsuario, $idPostulacion);
    }

    public function actualizarCantMsjsNotificacion($idUsuario)
    {
        return $this->_postulante->UpdateMsjsLeidos($idUsuario);
    }
}
