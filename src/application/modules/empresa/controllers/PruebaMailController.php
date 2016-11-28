<?php

class Empresa_PruebaMailController extends App_Controller_Action_Empresa
{
    
    public function init() 
    {
        //parent::init();
    }
    
    public function indexAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        try {
            $mailer = new App_Controller_Action_Helper_Mail();
            $dataMail = array (
                'to' => 'pauldj_9@hotmail.com',
                'usuario' => 'Paul',
                'nombre' => 'Carlos Carlos',
                'anuncioPuesto' => 'Nuevo Puesto',
                'razonSocial' => 'Club de Suscriptores',
                'montoTotal' => '1,500',
                'medioPago' => 'Pago Efectivo',
                'anuncioClase' => 'clasificado',
                'productoNombre' => 'Oro',
                'anuncioUrl' => 'http://devel.aptitus.info/ofertas-de-trabajo/aviso-primero-2uzgj',
                'fechaPago' => date('Y/m/d'),
                'anuncioFechaVencimiento' => 'del 18 may 2012 hasta 01 jun 2012',
                'fechaPublicConfirmada' => '20 may 2012',
                'medioPublicacion' => 'aptitus',
                'anuncioSlug' => '2uzgj',
                'anuncioFechaVencimientoProceso' => 'del 18 may 2012 hasta 17 jun 2012',
                'codigo_adecsys_compra' => '0'
            );
            $mailer->confirmarCompra($dataMail);
            echo 'ENVIADO... 100%';
            //$this->_redirect('/empresa');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        exit;
    }
    
    public function desbloquearEmpresaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        try {
            $mailer = new App_Controller_Action_Helper_Mail();
            $dataMail = array (
                'to' => 'pauldj_9@hotmail.com',
                'empresa' => 'Club de Suscriptores'
            );
            $mailer->desbloquearEmpresa($dataMail);
            echo 'ENVIADO... 100%';
            //$this->_redirect('/empresa');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        exit;
    }
    
    public function nuevoAdministradorEmpresaAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        try {
            $mailer = new App_Controller_Action_Helper_Mail();
            $dataMail = array (
                'to' => 'pauldj_9@hotmail.com',
                'pswd' => 'th4n5j35'
            );
            $mailer->nuevoAdm($dataMail);
            echo 'ENVIADO... 100%';
            //$this->_redirect('/empresa');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        exit;
    }
    
    public function mensajeAdminPostulanteAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        try {
            $mailer = new App_Controller_Action_Helper_Mail();
            $dataMail = array (
                'to' => 'pauldj_9@hotmail.com',
                'nombre' => 'Miguel Angel',
                'email' => 'informes@alfabetovisual.com',
                'url' => 'http://aptitus.com/'
            );
            $mailer->mensajeAdminPostulante($dataMail);
            echo 'ENVIADO... 100%';
            //$this->_redirect('/empresa');
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        exit;
    }
}
