<?php


/**
 * validaciones sobre una postulacion
 *
 * @author Carlos Muñoz Ramirez, <camura8503@gmail.com>
 */
class App_Service_Validate_Postulation
    extends App_Service_Validate
{

    const INVITED = 'invited';
    const REFERENCE = 'reference';
    const BLOCKED = 'blocked';
    const IS_NULL = 'null';
    const HAS_POSTULATED = 'Ya postulo a este anuncio';

    protected $_messageTemplates = array(
        self::INVITED   => "Es una invitacion",
        self::REFERENCE => "La postulacion esta referenciada",
        self::BLOCKED => "La postulacion esta bloqueada",
        self::IS_NULL => "No se encontro la postulacion"
    );
    
    protected $_modelName = 'Application_Model_Postulacion';
    
    public function isInvited($data = array())
    {
        $postulation = $this->getData($data);
        
        if ($postulation['invitacion'] == 
                Application_Model_Postulacion::INVITADO) {
            $this->_error(self::INVITED);
            return TRUE;
        }
        return FALSE;
    }
    
    public function isReferred($data = array())
    {
        $postulation = $this->getData($data);
        
        if ($postulation['referenciado'] == 
                Application_Model_Postulacion::ES_REFERENCIADO) {
            $this->_error(self::REFERENCE);
            return TRUE;
        }
        return FALSE;
    }
    
    public function isBlocked($data = array())
    {
        $postulation = $this->getData($data);
        
        if ($postulation['estado'] == 
                Application_Model_Postulacion::POSTULACION_BLOQUEADA) {
            $this->_error(self::BLOCKED);
            return TRUE;
        }
        return FALSE;
    }

    static public function hasPostulated($adId, $postulantId)
    {
        $postulationModel = new Application_Model_Postulacion;

        $postulation = $postulationModel->obtenerRefenreciada(
            $adId, $postulantId, array('id'));

        if (isset($postulation)) {
            self::$staticMessageError = self::HAS_POSTULATED;
            self::$staticData = $postulation->toArray();
            return TRUE;
        }

        return FALSE;
    }
}
