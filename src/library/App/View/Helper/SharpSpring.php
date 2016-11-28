<?php

class App_View_Helper_SharpSpring extends Zend_View_Helper_HtmlElement
{

    public function SharpSpring()
    {
        return file_get_contents(APPLICATION_PATH."/layouts/scripts/includes/sharp_pring.phtml"); 
    }

}

