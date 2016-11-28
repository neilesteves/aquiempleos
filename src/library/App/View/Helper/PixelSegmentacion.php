<?php

class App_View_Helper_PixelSegmentacion extends Zend_View_Helper_HtmlElement {

    public function PixelSegmentacion() {
        
        $script = '';
        //MODULE, CONTROLLER, ACTION
        if (MODULE === "postulante" AND CONTROLLER === "home" AND ACTION === "index"){
            $script = include(APPLICATION_PATH."/layouts/scripts/includes/segment_pixel.phtml");
        }
        return $script;
    }

}
