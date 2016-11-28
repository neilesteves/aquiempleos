<?php

/**
 * Configura los ítems del menú dinámico del portal
 */
class App_View_Helper_Menus extends Zend_View_Helper_Abstract
{
    public $view;

    public function Menus()
    {
        $this->view = Zend_Layout::getMvcInstance()->getView();
        return $this;
    }

    /**
     * lista de menúes en desktop
     * @param type $auth
     * @param type $moderador
     * @return array
     */
    public function Listjs()
    {

    }

    public function linkRegistro()
    {
        if (MODULE == 'postulante') {
            return '#modalRegisterUser';
        }
        return $this->view->url(array(
                'module' => 'empresa',
                'controller' => 'registro-empresa',
                'action' => 'index'), "default", true);
    }

    public function footerPublicarAviso()
    {
        $auth = Zend_Auth::getInstance()->getIdentity();

        $url = '#modalLoginUser';
        if (isset($auth)) {
            $url = '/empresa/publica-aviso/index/tarifa/1';
        }
        return $url;
    }

    public function footerEncontraEmpleo()
    {

        $url = '/peru/buscar';
        return $url;
    }

    public function ListMenusHeader($auth, $moderador = false)
    {
        $post = 0;

        if (isset($auth) && $moderador) {
            if (isset($auth['usuario']) && $auth['usuario']->rol == App_Controller_Action::USUARIO_POSTULANTE) {
                $Items[$post] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'mi-cuenta',
                        'action' => 'mis-alertas'), "default", true),
                    'content' => 'Alertas',
                    'title' => 'Alertas',
                    'class' => ''
                );
                $post++;
                $Items[$post] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'mi-cuenta',
                        'action' => 'cambio-de-clave'), "default", true),
                    'content' => 'Contraseña',
                    'title' => 'Contraseña',
                    'class' => ''
                );
                $post++;
                $Items[$post] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'mi-cuenta',
                        'action' => 'privacidad'), "default", true),
                    'content' => 'Privacidad',
                    'title' => 'Privacidad',
                    'class' => ''
                );
                $post++;

                $Items[$post] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'mi-cuenta',
                        'action' => 'mis-datos-personales'), "default", true),
                    'content' => 'Mi cuenta',
                    'title' => 'Mi cuenta',
                    'class' => ''
                );
                $post++;
                $Items[$post] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'mi-cuenta',
                        'action' => 'mi-perfil'), "default", true),
                    'content' => 'Perfil completo',
                    'title' => 'Perfil completo',
                    'class' => ''
                );
                $post++;
                $Items[$post] = array(
                    'href' => null,
                    'content' => null,
                    'title' => null,
                    'class' => 'separator',
                );
                $post++;
                $Items[$post] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'auth',
                        'action' => 'logout'), "logout", true),
                    'content' => 'Cerrar Sesi&oacute;n',
                    'title' => 'Cerrar Sesi&oacute;n',
                    'class' => 'log_out'
                );
                $post++;
            }
            if (isset($auth['usuario']) && in_array($auth["usuario"]->rol,
                    array(
                    App_Controller_Action::ADMIN_EMPRESA,
                    App_Controller_Action::USUARIO_EMPRESA))) {
                if (isset($auth["empresa"]["membresia_info"]["membresia"])) {
                    $Items[$post] = array(
                        'href' => $this->view->url(array(
                            'module' => 'empresa',
                            'controller' => 'mi-cuenta',
                            'action' => 'membresias'), "default", true),
                        'content' => 'Membresía : '.$auth["empresa"]["membresia_info"]["membresia"]["m_nombre"],
                        'title' => 'Membresía : '.$auth["empresa"]["membresia_info"]["membresia"]["m_nombre"],
                        'class' => ''
                    );
                    $post++;
                    $Items[$post] = array(
                        'href' => $this->view->url(array(
                            'module' => 'empresa',
                            'controller' => 'mi-cuenta',
                            'action' => 'membresias'), "default", true),
                        'content' => $auth["empresa"]["razon_comercial"],
                        'title' => $auth["empresa"]["razon_comercial"],
                        'class' => ''
                    );
                    $post++;
                } else {

                    $Items[$post] = array(
                        'href' => $this->view->url(array(
                            'module' => 'empresa',
                            'controller' => 'mi-cuenta',
                            'action' => 'mis-avisos'), "default", true),
                        'content' => $auth["usuario-empresa"]["nombres"].' '.$auth["usuario-empresa"]["apellidos"].' - '.$auth["empresa"]["razon_comercial"].'',
                        'title' => $auth["empresa"]["razon_comercial"],
                        'class' => 'size one'
                    );
                    $post++;
                }
//                $Items[$post] = array(
//                    'href' => null,
//                    'content' => null,
//                    'title' => null,
//                    'class' => 'separator',
//                );
//                $post++;
//                $Items[$post] = array(
//                    'href' => $this->view->url(array('module' => 'empresa', 'controller' => 'home', 'action' => 'index'), "default", true),
//                    'content' => 'Inicio',
//                    'title' => 'Inicio',
//                    'class' => ''
//                );
//                $post++;
//                $Items[$post] = array(
//                    'href' => $this->view->url(array('module' => 'empresa', 'controller' => 'mi-cuenta', 'action' => 'index'), "default", true),
//                    'content' => 'Mi cuenta',
//                    'title' => 'Mi cuenta',
//                    'class' => ''
//                );
//                $post++;
//                $Items[$post] = array(
//                    'href' => $this->view->url(array('module' => 'empresa', 'controller' => 'publica-aviso', 'action' => 'index','tarifa'=>1), "default", true),
//                    'content' => 'Publicar aviso',
//                    'title' => 'Publicar aviso',
//                    'class' => ''
//                );
//                $post++;
//                $Items[$post] = array(
//                    'href' => $this->view->url(array('module' => 'empresa', 'controller' => 'seleccion', 'action' => 'index'), "default", true),
//                    'content' => 'AquiEmpleos selección',
//                    'title' => 'AquiEmpleos selección',
//                    'class' => ''
//                );
                //$post++;
//                $Items[$post] = array(
//                    'href' => null,
//                    'content' => null,
//                    'title' => null,
//                    'class' => 'separator',
//                );
                //$post++;
                $Items[$post] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'auth',
                        'action' => 'logout'), "logout", true),
                    'content' => 'Cerrar sesión',
                    'title' => 'Cerrar sesi&oacute;n',
                    'class' => 'size two'
                );
                $post++;
            }
        } else {
            $Items[$post] = array(
                'href' => '#modalLoginUser',
                'content' => 'Ingresar',
                'title' => 'Ingresar',
                'class' => 'size one'
            );
            $post++;

            $Items[$post] = array(
                'href' => (MODULE == 'postulante') ? '#modalRegisterUser' : '/'.MODULE.'/registro-'.MODULE,
                'content' => 'Regístrate',
                'title' => 'Regístrate',
                'class' => 'size two'
            );
            $post++;
            $mesaje       = (MODULE == 'postulante') ? 'Soy una empresa' : 'Soy un postulante';
            $Items[$post] = array(
                'href' => (MODULE == 'postulante') ? '/empresa/' : '/',
                'content' => $mesaje,
                'title' => $mesaje,
                'class' => 'size tree'
            );
            $post++;
        }
        // var_dump($Items);exit;
        return $Items;
    }

    public function ListMH($auth, $moderador = false)
    {
        $post = 0;
        if (isset($auth) && $moderador) {
            if (isset($auth['usuario']) && $auth['usuario']->rol == App_Controller_Action::USUARIO_POSTULANTE) {
                $Items[$post] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'mi-cuenta',
                        'action' => 'mis-datos-personales'), "default", true),
                    'content' => $auth["postulante"]['nombres'].' '.$auth["postulante"]['apellido_paterno'].' '.$auth["postulante"]['apellido_materno'],
                    'title' => 'Mi cuenta',
                    'li' => 'page-scroll text text-right top-b',
                    'class' => 'size-user',
                    'email' => $auth['usuario']->email,
                    'cerrar' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'auth',
                        'action' => 'logout'), 'logout', false)
                );
                $post++;
                $mesaje       = (MODULE == 'postulante') ? 'Soy una empresa' : 'Soy un postulante';
                $Items[$post] = array(
                    'href' => (MODULE == 'postulante') ? '/empresa/' : '/',
                    'content' => $mesaje,
                    'title' => $mesaje,
                    'li' => 'page-scroll top-c',
                    'class' => 'one'
                );
                $post++;
            }
            if (isset($auth['usuario']) && in_array($auth["usuario"]->rol,
                    array(
                    App_Controller_Action::ADMIN_EMPRESA,
                    App_Controller_Action::USUARIO_EMPRESA))) {
                if (isset($auth["empresa"]["membresia_info"]["membresia"])) {
                    $Items[$post] = array(
                        'href' => $this->view->url(array(
                            'module' => 'empresa',
                            'controller' => 'mi-cuenta',
                            'action' => 'membresias'), "default", true),
                        'content' => 'Membresía : '.$auth["empresa"]["membresia_info"]["membresia"]["m_nombre"],
                        'title' => 'Membresía : '.$auth["empresa"]["membresia_info"]["membresia"]["m_nombre"],
                        'class' => ''
                    );
                    $post++;
                    $Items[$post] = array(
                        'href' => $this->view->url(array(
                            'module' => 'empresa',
                            'controller' => 'mi-cuenta',
                            'action' => 'membresias'), "default", true),
                        'content' => $auth["empresa"]["razon_comercial"],
                        'title' => $auth["empresa"]["razon_comercial"],
                        'class' => ''
                    );
                    $post++;
                } else {

                    $Items[$post] = array(
                        'href' => $this->view->url(array(
                            'module' => 'empresa',
                            'controller' => 'mi-cuenta',
                            'action' => 'mis-avisos'), "default", true),
                        'content' => $auth["usuario-empresa"]["nombres"].' '.$auth["usuario-empresa"]["apellidos"].' - '.$auth["empresa"]["razon_comercial"].'',
                        'title' => $auth["empresa"]["razon_comercial"],
                        'class' => 'size one'
                    );
                    $post++;
                }
//                $Items[$post] = array(
//                    'href' => null,
//                    'content' => null,
//                    'title' => null,
//                    'class' => 'separator',
//                );
//                $post++;
//                $Items[$post] = array(
//                    'href' => $this->view->url(array('module' => 'empresa', 'controller' => 'home', 'action' => 'index'), "default", true),
//                    'content' => 'Inicio',
//                    'title' => 'Inicio',
//                    'class' => ''
//                );
//                $post++;
//                $Items[$post] = array(
//                    'href' => $this->view->url(array('module' => 'empresa', 'controller' => 'mi-cuenta', 'action' => 'index'), "default", true),
//                    'content' => 'Mi cuenta',
//                    'title' => 'Mi cuenta',
//                    'class' => ''
//                );
//                $post++;
//                $Items[$post] = array(
//                    'href' => $this->view->url(array('module' => 'empresa', 'controller' => 'publica-aviso', 'action' => 'index','tarifa'=>1), "default", true),
//                    'content' => 'Publicar aviso',
//                    'title' => 'Publicar aviso',
//                    'class' => ''
//                );
//                $post++;
//                $Items[$post] = array(
//                    'href' => $this->view->url(array('module' => 'empresa', 'controller' => 'seleccion', 'action' => 'index'), "default", true),
//                    'content' => 'AquiEmpleos selección',
//                    'title' => 'AquiEmpleos selección',
//                    'class' => ''
//                );
                //$post++;
//                $Items[$post] = array(
//                    'href' => null,
//                    'content' => null,
//                    'title' => null,
//                    'class' => 'separator',
//                );
                //$post++;
                $Items[$post] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'auth',
                        'action' => 'logout'), "logout", true),
                    'content' => 'Cerrar sesión',
                    'title' => 'Cerrar sesi&oacute;n',
                    'class' => 'size two'
                );
                $post++;
            }
        } else {
            $Items[$post] = array(
                'href' => '#modalLoginUser',
                'content' => 'Ingresar',
                'title' => 'Ingresar',
                'li' => 'page-scroll top-c',
                'class' => 'size one'
            );
            $post++;

            $Items[$post] = array(
                'href' => (MODULE == 'postulante') ? '#modalRegisterUser' : '/'.MODULE.'/registro-'.MODULE,
                'content' => 'Regístrate',
                'title' => 'Regístrate',
                'li' => 'page-scroll top-c',
                'class' => 'size two'
            );
            $post++;
            $mesaje       = (MODULE == 'postulante') ? 'Soy una empresa' : 'Soy un postulante';
            $Items[$post] = array(
                'href' => (MODULE == 'postulante') ? '/empresa/' : '/',
                'content' => $mesaje,
                'title' => $mesaje,
                'li' => 'page-scroll top-c',
                'class' => 'one'
            );
            $post++;
        }
        // var_dump($Items);exit;
        return $Items;
    }

    /**
     *
     * @param type $value
     * @return array
     */
    public function getIconName($value)
    {
        $arrIconos = array(
            'Privacidad' => 'icon_eye',
            'Mis Datos Personales' => 'icon_user_data',
            'Mis Postulaciones' => 'icon_tick',
            'Mis Notificaciones' => 'icon_bell',
            'Cómo destacar más' => 'icon_diamond',
            'Eliminar Cuenta' => 'icon_delete'
        );

        return $arrIconos[$value];
    }

    /**
     *
     * @param type $value
     * @return string
     */
    public function getAditionalClass($value)
    {
        $arrIconos = array(
            'Cómo destacar más' => 'important'
        );

        return isset($arrIconos[$value]) ? $arrIconos[$value] : array();
    }

    /**
     * lista de menus en movil
     * @param type $auth
     * @param type $layout
     * @return array
     */
    public function ListMenusHeaderMovil($auth, $layout)
    {
        $i = 0;
        $e = 0;
        $f = 0;

        if (isset($auth) && !empty($auth)) {
            if ($auth['usuario']->rol == 'postulante') {
                $Items[$i]    = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'buscar',
                        'action' => 'index'), 'default', true),
                    'content' => 'Busqueda Avanzada',
                    'icon' => '',
                    'type' => 'form'
                );
                $i++;
                // definiendo los submenus
                $subItems[$e] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'mi-cuenta',
                        'action' => 'privacidad'), 'default', true),
                    'content' => 'Privacidad',
                    'icon' => 'icon_eye',
                    'type' => 'list'
                );
                $e++;
                $subItems[$e] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'mi-cuenta',
                        'action' => 'mis-alertas'), 'default', true),
                    'content' => 'Alertas',
                    'icon' => 'icon_alert',
                    'type' => 'list'
                );
                $e++;
                $subItems[$e] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'mi-cuenta',
                        'action' => 'cambio-de-clave'), 'default', true),
                    'content' => 'Contraseña',
                    'icon' => 'icon_key',
                    'type' => 'list'
                );
                $e++;
                $subItems[$e] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'mi-cuenta',
                        'action' => 'mi-perfil'), 'default', true),
                    'content' => 'Perfil completo',
                    'icon' => 'icon_user_file',
                    'type' => 'list'
                );
                $e++;
                if (isset($layout->subMenuPrivacidad)) {
                    foreach ($layout->subMenuPrivacidad as $value) {
                        $subItems[$e] = array(
                            'href' => $this->view->url(array(
                                'module' => 'postulante',
                                'controller' => 'mi-cuenta',
                                'action' => $value['href']), 'default', true),
                            'content' => $value['value'],
                            'class' => $this->getAditionalClass($value['value']),
                            'icon' => $this->getIconName($value['value']),
                            'type' => 'list'
                        );
                        $e++;
                    }
                }
                if (isset($layout->submenuMiCuenta)) {
                    foreach ($layout->submenuMiCuenta as $value) {
                        $subItems[$e] = array(
                            'href' => $this->view->url(array(
                                'module' => 'postulante',
                                'controller' => $value['href'],
                                'action' => $value['action']), 'default', true),
                            'content' => $value['value'],
                            'class' => $this->getAditionalClass($value['value']),
                            'icon' => $this->getIconName($value['value']),
                            'type' => 'list'
                        );
                        $e++;
                    }
                }


                $Items[$i] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'buscar',
                        'action' => 'index'), 'default', true),
                    'content' => 'Mi cuenta',
                    'ul' => $subItems,
                    'icon' => '',
                    'type' => 'sublist'
                );
                $i++;


                $Items[$i] = array(
                    'href' => $this->view->url(array(
                        'module' => 'empresa',
                        'controller' => 'home',
                        'action' => 'index'), 'default', true),
                    'content' => 'Empresa',
                    'icon' => '',
                    'type' => 'list'
                );
                $i++;
                $Items[$i] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'blog',
                        'action' => 'index'), 'default', true),
                    'content' => 'Blog',
                    'icon' => '',
                    'type' => 'list'
                );
                $i++;
                $Items[$i] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'notificaciones',
                        'action' => 'index'), 'default', true),
                    'content' => 'Mis Notificaciones',
                    'ul' => '',
                    'icon' => '',
                    'type' => 'list'
                );
                $i++;
                $Items[$i] = array(
                    'href' => $this->view->url(array(
                        'module' => 'postulante',
                        'controller' => 'avisos-sugeridos',
                        'action' => 'index'), 'default', true),
                    'content' => 'Avisos Sugeridos',
                    'icon' => '',
                    'type' => 'list'
                );
                $i++;
            }
            if (substr($auth['usuario']->rol, 0, 7) == 'empresa') {
                $Items[$i] = array(
                    'href' => $this->view->url(array(
                        'module' => 'empresa',
                        'controller' => 'home',
                        'action' => 'index'), 'default', true),
                    'content' => 'Inicio',
                    'icon' => 'icon_house',
                    'type' => 'list'
                );
                $i++;
                $Items[$i] = array(
                    'href' => $this->view->url(array(
                        'module' => 'empresa',
                        'controller' => 'mi-cuenta',
                        'action' => 'index'), 'default', true),
                    'content' => 'Mi cuenta',
                    'icon' => 'icon_user_data',
                    'type' => 'list'
                );
                $i++;
                $Items[$i] = array(
                    'href' => $this->view->url(array(
                        'module' => 'empresa',
                        'controller' => 'publica-aviso',
                        'action' => 'index'), 'default', true),
                    'content' => 'Publicar aviso',
                    'icon' => 'icon_browser_ads',
                    'type' => 'list'
                );
                $i++;
                $Items[$i] = array(
                    'href' => $this->view->url(array(
                        'module' => 'empresa',
                        'controller' => 'seleccion',
                        'action' => 'index'), "default", true),
                    'content' => 'AquiEmpleos selección',
                    'icon' => 'icon_browser_ads',
                    'type' => 'list'
                );
                $i++;
            }
            $Items[$i] = array(
                'href' => $this->view->url(array(
                    'module' => 'postulante',
                    'controller' => 'auth',
                    'action' => 'logout'), 'logout', true),
                'content' => 'Cerrar sesión',
                'icon' => 'icon_turn_on',
                'type' => 'list'
            );
            $i++;
        } else {
            $Items[$i] = array(
                'href' => '#',
                'content' => 'Ingresar',
                'class' => 'login_init',
                'icon' => 'icon_login',
                'type' => 'list'
            );
            $i++;
            if (MODULE == 'postulante') {
                $Items[$i] = array(
                    'href' => "#",
                    'content' => 'Registrarme',
                    'class' => 'register_init',
                    'icon' => 'icon_user_add',
                    'type' => 'list'
                );
                $i++;
                $Items[$i] = array(
                    'href' => '/empresa',
                    'content' => 'Soy una empresa',
                    'icon' => 'icon_nut',
                    'type' => 'list'
                );
                $i++;
            } elseif (MODULE == 'empresa') {
                $Items[$i] = array(
                    'href' => $this->view->url(array(
                        'module' => 'empresa',
                        'controller' => 'registro-empresa',
                        'action' => 'index'), 'default', true),
                    'content' => 'Registrarme',
                    'class' => '',
                    'icon' => 'icon_user_add',
                    'type' => 'list'
                );
                $i++;
                $Items[$i] = array(
                    'href' => '/',
                    'content' => 'Soy un postulante',
                    'icon' => 'icon_nut',
                    'type' => 'list'
                );
                $i++;
            }
        }
        return $Items;
    }

    /**
     *
     * @param type $dataAviso
     * @return array
     */
    public function ListMenusBuscadorAviso($dataAviso)
    {
        $i         = 0;
        $Items[$i] = array(
            'href' => $this->view->url(array(
                'module' => 'postulante',
                'controller' => 'buscar',
                'action' => 'index'), 'default', true),
            'content' => 'Inicio',
            'title' => 'Inicio'
        );
        $i++;
        if (isset($dataAviso['area_puesto_slug'])) {
            $Items[$i] = array(
                'href' => $this->view->url(array(
                    'module' => 'postulante',
                    'controller' => 'buscar',
                    'action' => 'index',
                    'areas' => $dataAviso['area_puesto_slug']), 'default', true),
                'content' => $dataAviso['area_puesto'],
                'title' => $dataAviso['area_puesto']
            );
            $i++;
        }
        if (isset($dataAviso['nivel_puesto_slug'])) {
            $Items[$i] = array(
                'href' => $this->view->url(array(
                    'module' => 'postulante',
                    'controller' => 'buscar',
                    'action' => 'index',
                    'nivel' => $dataAviso['nivel_puesto_slug']), 'default', true),
                'content' => ucwords(str_replace('-', ' ',
                        $dataAviso['nivel_puesto_slug'])),
                'title' => ucwords(str_replace('-', ' ',
                        $dataAviso['nivel_puesto_slug']))
            );
            $i++;
        }
        return $Items;
    }

    /**
     *
     * @param type $aviso
     * @param type $isNotAvisoCiego
     * @return type
     */
    public function ValiIsNotAvisoCiegoLink($aviso, $isNotAvisoCiego)
    {
        $i         = 0;
        $Items[$i] = array(
            'href' => '#',
            'title' => $this->view->escape($aviso['nombre_empresa']),
            'alt' => $this->view->escape($aviso['nombre_empresa']),
            'src' => $this->view->S('/images/icon-empresa-blank.png')
        );
        if ($isNotAvisoCiego) {
            if (isset($aviso['slug_empresa'])) {
                $Items[$i] = array(
                    'href' => '/buscar/empresa/'.$aviso['slug_empresa'],
                    'title' => $this->view->escape($aviso['nombre_empresa']),
                    'alt' => $this->view->escape($aviso['nombre_empresa']),
                    'src' => ELEMENTS_URL_LOGOS.$aviso['logo_empresa']
                );
            } else {
                $Items[$i] = array(
                    'href' => '#',
                    'title' => $this->view->escape($aviso['nombre_empresa']),
                    'alt' => $this->view->escape($aviso['nombre_empresa']),
                    'src' => ELEMENTS_URL_LOGOS.$aviso['logo_empresa']
                );
            }
        }
        return $Items;
    }
}
