<?php

class Delta_0101 extends App_Migration_Delta
{
    protected $_author = 'Ronald Cutisaca Ramirez';
    protected $_desc = 'cambiar avisos';

    public function up()
    {
        $this->_db->query("UPDATE anuncio_web SET online=0;");
        return true;
    }
}
