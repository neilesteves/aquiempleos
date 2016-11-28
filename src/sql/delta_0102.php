<?php

class Delta_0102 extends App_Migration_Delta
{

    protected $_author = 'Ronald Cutisaca Ramirez';
    protected $_desc = 'Agregar tabla para pagos con el api';

    public function up()
    {
        $this->_db->query(
                "CREATE TABLE api_medios (
            `id` INT(11) NOT NULL AUTO_INCREMENT  PRIMARY KEY,
            `user_cli` CHAR(2),
            `password` VARCHAR(100),
            `fh_creacion` DATETIME NOT NULL,
            `fh_modificado` DATETIME DEFAULT NULL,
            `estado` TINYINT(1) NOT NULL
          ) ENGINE=INNODB AUTO_INCREMENT=95810 DEFAULT CHARSET=utf8;
        ");
        return true;
    }

}
