<?php
/**
 * Description of PostulacionesController
 *
 * @author eanaya
 */
class Postulante_MisAlertasController extends App_Controller_Action_Postulante
{
    
    public function init()
    {
        parent::init();
    }
    
    public function indexAction()
    {
        // listado de notificaciones
        $this->view->menu_sel = self::MENU_MI_CUENTA;
        $this->view->menu_post_sel = self::MENU_POST_MIS_NOTIFICACIONES;
    }
    
}

