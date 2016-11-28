<?php

class App_View_Helper_TrackingPixelFacebook  extends Zend_View_Helper_HtmlElement
{

    public function TrackingPixelFacebook($tracking)
    {
        $txtPF = '';
        
        if (isset($tracking)) {
            //$txtPF = file_get_contents(APPLICATION_PATH."/layouts/scripts/includes/facebook_script.phtml");
        }
        return  $txtPF;
    }

}
