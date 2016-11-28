<?php


class Default_CacheController
    extends Zend_Controller_Action
{

    public function indexAction()
    {
        // action body
    }

    /**
     * @todo crear una administracion de cache en el Admin
     */
    public function cleanAction()
    {
        $this->_helper->layout->disableLayout();
        $cachePage = Zend_Registry::get('CachePage');

        $cachePage->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array('search'));
        $this->view->assign('respuesta', "Limpieza Completada");
    }

}


