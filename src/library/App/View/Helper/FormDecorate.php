<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FormDecorate
 *
 * @author ronald
 */
class App_View_Helper_FormDecorate extends Zend_View_Helper_Abstract
{

    //put your code here
    public function FormDecorate($form, $class = '')
    {
        //var_dump(htmlspecialchars($form));Exit;
        $label = str_replace("<label", '<div class="'.$class.'" ><label ',
            $form);
        $div   = str_replace('</label>', '</label> </div>', $label);
        $div2  = str_replace('<br />', '', $div);
        $dt    = str_replace('<dt id="color-label">&#160;</dt>', '', $div2);
        return $div2;
    }
}