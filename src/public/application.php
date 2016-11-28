<?php
defined('APPLICATION_PATH')
    || define(
        'APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application')
    );
   $fp = fopen(APPLICATION_PATH . '/configs/application.ini', "r");
        while(!feof($fp)) {
            $linea = fgets($fp);
            echo $linea . "<br />";
        }
     exit;
     ?>

