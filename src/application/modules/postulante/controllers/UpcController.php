<?php

class Postulante_UpcController extends App_Controller_Action_Postulante {

    private $_usuarioUpc;

    public function init() {

        $this->_usuarioUpc = new Application_Model_UsuarioUpc;
        parent::init();

        Zend_Layout::getMvcInstance()->assign(
                'bodyAttr', array('id' => 'perfilReg', 'class' => 'noMenu')
        );
    }

    //Muestra el formulario para el Landing de la UPC, al hacer el post
    // se registra al usuario y se le notifica por correo.
    public function indexAction() {

        $this->view->tipoDoc = array(1 => 'CI', 2 => 'PASAPORTE', 3 => 'CARNÉ DE EXTRANJERÍA');
        $this->view->nivel = array('DOCTORADO', 'MBA', 'MAGISTER', 'LICENCIADO O TITULADO', 'BACHILLER',
            'UNIVERSITARIO', 'TÉCNICO', 'SECUNDARIA');

        $this->view->ocupacion = array('Agricultores, ganaderos, pescadores', 'Artesanos', 'Conductores',
            'Empleados de oficina', 'Gerentes, directores, funcionarios', 'Mineros y cantero',
            'Obreros, jornaleros', 'Profesionales, técnicos y fines', 'Trabajadores de servicios', 'Trabajadores del hogar',
            'Vendedores');

        $this->view->interes = array('Liderazgo', 'Administración y Organización', 'Marketing', 'Finanzas y contabilidad',
            'Factor Humano', 'Operaciones y Logística');

        $this->view->mes = array('01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
            '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
            '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre');

        if ($this->getRequest()->isPost()) {
            $data = $this->_getAllParams();

            //Previene ataques XSS
            $filter = new Zend_Filter_StripTags();
            foreach ($data as $key => $value) {
                $data[$key] = $filter->filter($value);
            }
            $fecNac = null;

            if ($data['selDay'] > 0 && $data['selMonth'] > 0 && $data['selYear'] > 0) {
                $fecNac = $data['selYear'] . '-' . $data['selMonth'] . '-' . $data['selDay'];
            }

            $data['fecNac'] = $fecNac;

            //Validación token CSRF
            if ($this->_hash->isValid($this->_getParam('hash'))) {

                $id = $this->_usuarioUpc->registrar($data);

                $this->getMessenger()->success($data['txtNombres'] . ', sus datos fueron '
                        . 'grabados satisfactoriamente y se le envió una notificación a su correo.');

                //Envía notificación por correo
                $mail = new App_Controller_Action_Helper_Mail;
                $mail->notificacionUpc(array(
                    'to' => $data['txtEmail'],
                    'nombres' => $data['txtNombres']));
            }
        }
    }

}
