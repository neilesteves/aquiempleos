<?php

class Delta_0103 extends App_Migration_Delta
{

    protected $_author = 'Ronald Cutisaca Ramirez';
    protected $_desc = 'Agregar tabla para pagos con el api';

    public function up()
    {
        $sql = "UPDATE anuncio_web SET online = '0'";
        $this->_db->query($sql);
        return true;
    }

}
