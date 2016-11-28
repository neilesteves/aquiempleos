<?php

class App_Exception_Permisos extends Zend_Exception
{
    public function __construct($msg = '', $code = 0, Exception $previous = null)
    {
        parent::__construct("No tienes permisos. " . $msg, $code, $previous);
    }

}
