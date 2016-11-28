<?php

/**
 * Description of Attribs
 *
 * @author eanaya
 */
class App_View_Helper_GoogleAnalytics extends Zend_View_Helper_HtmlElement
{

    public function GoogleAnalytics()
    {
        return file_get_contents(APPLICATION_PATH."/layouts/scripts/includes/google_analytics.phtml"); 
    }

}
