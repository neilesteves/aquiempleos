<?php

/**
 * Interface
 *
 * @author Carlos Muñoz Ramirez, <camura8503@gmail.com>
 */

interface App_Service_Validate_Interface
{
    public function getData();
    public function setData($data);
    public function reload();
    public function getMessage();
    public function isNull();
}