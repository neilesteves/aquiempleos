<?php

class Delta_0104 extends App_Migration_Delta
{

    protected $_author = 'Ronald Cutisaca Ramirez';
    protected $_desc = 'modificando el campo nivel de puesto para generalizar el sexo';

    public function up()
    {
        $sql = "UPDATE nivel_puesto SET nombre= REPLACE(nombre, '@', 'o(a)');";
        $this->_db->query($sql);
        return true;
    }

}
