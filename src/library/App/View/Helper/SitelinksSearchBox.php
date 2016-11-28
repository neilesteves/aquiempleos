<?php

/**
 * Google sitelinks search box, people can reach your 
 * content more quickly from search results.
 *
 * @author backend aptitus
 */
class App_View_Helper_SitelinksSearchBox extends Zend_View_Helper_HtmlElement
{

    public function SitelinksSearchBox()
    {
        return App_Util::estaEnHomePostulante() ? file_get_contents(APPLICATION_PATH."/layouts/scripts/includes/google_sitelinks_sb.phtml") : '';
    }

}
