<?php


/**
 * Validaciones sobre un postulante
 *
 * @author Carlos Muñoz Ramirez, <camura8503@gmail.com>
 */
class App_Service_Validate_User extends App_Service_Validate
{

    const NOT_REGISTERED = 'notRegistered';
    const NOT_REGISTERED_MSJ = 'Este email no esta registrado';

    protected $_messageTemplates = array(
        self::IS_NULL => "El usuario no esta registrado"
    );

    static public function isRegister($email)
    {
        $userModel = new Application_Model_Usuario;
        $postulant = $userModel->obtenerPorEmail($email, array('id'));

        if (!isset($postulant)) {
            self::$staticTypeError = self::NOT_REGISTERED;
            self::$staticMessageError = self::NOT_REGISTERED_MSJ;
            return false;
        }

        return true;
    }

}