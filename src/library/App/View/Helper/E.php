<?php

/**
 * E view Helpers is a Loader for Element files
 * providing ability to read prefix from config
 * and suffix a versioning query string
 *
 */
class App_View_Helper_E extends Zend_View_Helper_HtmlElement
{
    protected static $lastCommit;

    public function __construct()
    {
        $lc_file = APPLICATION_PATH.'/../last_commit';
        if (is_readable($lc_file)) {
            if (!isset(self::$lastCommit)) {
                self::$lastCommit = trim(file_get_contents($lc_file));
            }
        }
    }

    /**
     * @param  String
     * @return string
     */
    public function E()
    {
        return $this;
    }

    public function getLastCommit()
    {
        return '?v='.self::$lastCommit;
    }

    public function getLastCommitE8($file = "")
    {
        if (!$file) {
            return $this;
        }

        return SITE_URL.$file.'?v='.self::$lastCommit;
    }

    public function banner1($file = "")
    {

        if (!$file) {
            return $this;
        }
        $tablet_browser = 0;
        $mobile_browser = 0;
        $body_class     = $file;

        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i',
                strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $tablet_browser++;
          //  $body_class = "/eb/img/postulante/slider/sp-1-movil.jpg";
        }

        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i',
                strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $mobile_browser++;
          //  $body_class = "/eb/img/postulante/slider/sp-1-movil.jpg";
        }

        if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),
                'application/vnd.wap.xhtml+xml') > 0) or ( (isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
            $mobile_browser++;
            //$body_class = "/eb/img/postulante/slider/sp-1-movil.jpg";
        }
        return MEDIA_URL.$body_class.'?v='.self::$lastCommit;
    }

    public function banner2($file = "")
    {

        if (!$file) {
            return $this;
        }
        $tablet_browser = 0;
        $mobile_browser = 0;
        $body_class     = $file;

        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i',
                strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $tablet_browser++;
        //    $body_class = "/eb/img/postulante/slider/sp-2-movil.jpg";
        }

        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i',
                strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $mobile_browser++;
          //  $body_class = "/eb/img/postulante/slider/sp-2-movil.jpg";
        }

        if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),
                'application/vnd.wap.xhtml+xml') > 0) or ( (isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
            $mobile_browser++;
            //$body_class = "/eb/img/postulante/slider/sp-2-movil.jpg";
        }
        return MEDIA_URL.$body_class.'?v='.self::$lastCommit;
    }

    public function getlastcommitElementLogos($file = "")
    {

        if (empty($file)) {
            return MEDIA_URL.'eb/img/photoEmpDefault.png?v='.$file.self::$lastCommit;
            ;
        }
        return ELEMENTS_URL_LOGOS.$file;
    }

    public function logolandin($file)
    {
         if (empty($file)) {
            return MEDIA_URL.'/eb/img/photoEmpDefault.png?v='.$file.self::$lastCommit;
        }
        return $file;
    }
    public function getElementLogos($file = "")
    {
        if (empty($file)) {
            return MEDIA_URL.'/eb/img/photoEmpDefault.png?v='.$file.self::$lastCommit;
            ;
        }

        return ELEMENTS_URL_LOGOS.$file;
    }
}
