<?php

class Application_Model_AdecsysEnteContacto extends App_Db_Table_Abstract
{
    protected $_name = "adecsys_ente_contacto";
    
    public function registrar($params)
    {
        $data = array();
        $data['adecsys_ente_id'] = $params['adecsys_ente_id'];
        $data['nombres'] = $params['Nom_Contacto'];
        $data['apellidos'] = $params['Ape_Contacto'];
        $data['telefono'] = $params['Telf_Contacto'];
        $data['email'] = $params['Email_Contacto'];
        $data['direccion'] = $params['Dir_Contacto'];

        return $this->insert($data);
    }

}