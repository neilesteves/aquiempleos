<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of Aviso
 *
 * @author sanada
 */
class App_Controller_Action_Helper_AvisoTest extends PHPUnit_Framework_TestCase
{

    public static function main()
    {
        $suite = new PHPUnit_Framework_TestSuite("App_Controller_Action_Helper_CacheTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }
    
    

}


