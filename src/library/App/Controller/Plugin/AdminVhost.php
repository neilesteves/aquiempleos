<?php

class App_Controller_Plugin_AdminVhost extends Zend_Controller_Plugin_Abstract
{

    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        parent::routeShutdown($request);
        $config = Zend_Registry::get('config');


        $shouldIUseAdmVhost = $config->get('useExclusiveVhostForAdmin', false);
        $iAmUsingAdvVhost = $config->app->adminUrl == 'http://' . $_SERVER['SERVER_NAME'];
        $iAmOnAdmMod = $request->getModuleName() == 'admin';

        if ($shouldIUseAdmVhost) {
            if ($iAmOnAdmMod && !$iAmUsingAdvVhost) {
                header('Location: '.$config->app->adminUrl);
            }

            if ($iAmUsingAdvVhost && !$iAmOnAdmMod) {
                $r = new Zend_Controller_Action_Helper_Redirector();
                $r->gotoUrl('/admin');
            }
        }
    }

}