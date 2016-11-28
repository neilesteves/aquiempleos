<?php

/**
 * Description of SlugFilter
 *
 * @author Usuario
 */
class App_Filter_Separate implements Zend_Filter_Interface
{
    public function filter($value)
    {
        $value = str_replace("-", " ", $value);
        $value = str_replace("_", " ", $value);
        $value = str_replace(".", " ", $value);
        $value = str_replace(",", " ", $value);
        $value = str_replace("+", " ", $value);
        return $value;
    }
}