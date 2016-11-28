<?php

/**
 * Description of Util
 *
 * @author svaisman
 */
class App_View_Helper_Util extends Zend_View_Helper_HtmlElement
{
    private $_config;

    public function Util()
    {
        $this->_config = Zend_Registry::get('config');
        return $this;
    }

    public function isvalidProducto($value)
    {
          $avisoWeb = '';
        $nameproducto = $value['productoNombre'];
        if (!empty($value['anuncio_impreso'])) {

            switch ($value['destaque']) {
                case '1':
                    $avisoWeb = ' + Web Destacado oro';

                    break;
                case '2':
                    $avisoWeb = ' + Web Destacado plata';
                    break;
                default:
                    break;
            }
           // var_dump($value);
           // exit;
            // return $nameproducto .' + '.;
        }
        return $nameproducto . $avisoWeb;
    }

    public function listPostulantes($proceso)
    {
        $data = '';
        foreach ($proceso as $key => $value) {
            $data = $data.$value['idpostulacion'].'-';
        }
        return substr($data, 0, -1);
        ;
    }

    public function listPostulaciones($param)
    {
        $data = '';
        foreach ($param as $key => $value) {
            $data = $data.$value.'-';
        }
        return substr($data, 0, -1);
    }

    public function cTotalSinIv($monto, $pais = 'peru')
    {
        $data  = array();
        $total = 0;
        switch ($pais) {
            case 'peru':
                $total = $monto * (1 + $this->_config->impuestoNicaragua->iva);
                $data  = array(
                    'Total' => number_format((float) $total, 2),
                    'subTotal' => number_format((float) $monto, 2),
                    'iva' => number_format((float) ($total - $monto), 2),
                );
                break;

            default:
                break;
        }
        return $data;
    }

    public function cTotalConIv($total, $pais = 'peru')
    {
        switch ($pais) {
            case 'peru':
                /*  */
                /*  */
                $monto = ($total * 1) / (1 + $this->_config->impuestoNicaragua->iva);
                $data  = array(
                    'Total' => number_format((float) $total, 2),
                    'subTotal' => number_format((float) $monto, 2),
                    'iva' => number_format((float) ($total - $monto), 2),
                );


                /* $subtotal = ($total * 100 ) / 115;
                  $data     = array(
                  'Total' => number_format((float) $total, 2),
                  'subTotal' => number_format((float) $subtotal, 2),
                  'iva' => number_format((float) ($total - $subtotal), 2),
                  );
                 */

                break;


            default:
                break;
        }
        return $data;
    }

    public function impuesto($base, $extra, $descuento)
    {
        return number_format($this->_config->impuestoNicaragua->iva * ($base + $extra
            + $descuento), 2);
    }

    public function valueImpreso($value)
    {
        if (strlen($value) == 1) {
            return '0'.$value;
        }
        return $value;
    }

    public function codifica($redirecForm)
    {
        $helper = new App_Controller_Action_Helper_Util();
        return $helper->codifica(urlencode($redirecForm));
    }

    public function listaAreas($data)
    {
        $config = Zend_Registry::get('config');

        foreach ($data as $key => $value) {
            foreach ($this->AreasJJc() as $val) {
                if ($value["slug"] == $val) unset($data[$key]);
            }
        }
        foreach ($data as $key => $value) {
            if ($key >= $config->area->home->banner) unset($data[$key]);
        }

        return $data;
    }

    public function NombreArea($param)
    {
        //var_dump($param);exit;

        $param = trim($param);

        if ($param == 'analista-asistente') {
            return str_replace('-', ' / ', ucwords($param));
        } elseif ($param == 'jefe-supervisor') {
            return str_replace('-', ' / ', ucwords($param));
        } elseif ($param == 'tecnicos-operativos') {
            return str_replace('-', ' / ', ucwords($param));
        }
        return str_replace('-', ' ', ucwords($param));
    }

    private function AreasJJc()
    {
        $config = Zend_Registry::get('config');
        $area   = $config->areajjc;
        return $area;
    }

    public function fechaHora($fh)
    {
        $fecha = new DateTime($fh);
        if ($fecha->format("H") > 11) return $fecha->format("H:m").' pm';
        else return $fecha->format("H:m").' am';
    }

    public function fechaDiMes($fh)
    {
        $fecha = new DateTime($fh);
        $mes   = array(
            1 => 'Ene',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dic'
        );
        return $fecha->format("d").' '.$mes[$fecha->format('n')];
    }

    public function urlAvisoFavoritosAjax($tipo)
    {
        $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
//        var_dump($tipo);exit;
        if ($tipo == Postulante_AvisosSugeridosController::sugerencias) {
            $rel = $view->url(array(
                'module' => 'postulante',
                'controller' => 'avisos-sugeridos',
                'action' => 'agregar-favoritos-ajax')); //'/avisos-sugeridos/avisos-sugeridos/eliminar-anuncio-sugerido-ajax';
        } elseif ($tipo == Postulante_AvisosSugeridosController::favoritos) {
            $rel = '';
        } elseif ($tipo == Postulante_AvisosSugeridosController::eliminados) {
            $rel = $view->url(array(
                'module' => 'postulante',
                'controller' => 'avisos-sugeridos',
                'action' => 'agregar-favoritos-eliminado-ajax'));
        }
        return $rel;
    }

    public function urlAvisoEliminadosAjax($tipo)
    {
        $view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');

        if ($tipo == Postulante_AvisosSugeridosController::sugerencias) {
            $rel = $view->url(array(
                'module' => 'postulante',
                'controller' => 'avisos-sugeridos',
                'action' => 'eliminar-anuncio-sugerido-ajax')); //'/avisos-sugeridos/avisos-sugeridos/eliminar-anuncio-sugerido-ajax';
        } elseif ($tipo == Postulante_AvisosSugeridosController::favoritos) {
            $rel = $view->url(array(
                'module' => 'postulante',
                'controller' => 'avisos-sugeridos',
                'action' => 'agregar-eliminado-favoritos-ajax')); //'/avisos-sugeridos/avisos-sugeridos/eliminar-anuncio-sugerido-ajax';
        } elseif ($tipo == Postulante_AvisosSugeridosController::eliminados) {
            $rel = '';
        }
        return $rel;
    }

    public function isMobile()
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',
                $useragent) || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',
                substr($useragent, 0, 4));
    }

    public function isIE8()
    {
        return preg_match('/(?i)msie 8/', $_SERVER['HTTP_USER_AGENT']);
    }

    public function Ico_filter($valor)
    {
        $valor = trim($valor);
        $ico   = array(
            'NIVEL DE ESTUDIOS' => 'icon_medal',
            'OTRO ESTUDIOS' => 'icon_education',
            'TIPO DE CARRERA' => 'icon_star',
            'EXPERIENCIA' => 'icon_star',
            'IDIOMAS' => 'icon_message',
            'PROGRAMAS' => 'icon_monitor',
            'EDAD' => 'icon_star',
            'SEXO' => 'icon_star',
            'UBICACION' => 'icon_position',
            'OTROS' => 'icon_star'
        );

        if (isset($ico[$valor])) {
            return $ico[$valor];
        }

        return "icon_star";
    }

    public static function fontsIE8()
    {
        if (preg_match('/(?i)msie 8/', $_SERVER['HTTP_USER_AGENT'])) {
            return include(APPLICATION_PATH."/layouts/scripts/includes/fonts_ie.phtml");
        }
    }

    public function getMobileClass()
    {
        return ($this->isMobile()) ? '_mobile' : '';
    }

    /**
     * Devuelve html con número de avisos activos
     *
     * @param int $n Total de avisos activos
     * @return string
     */
    public function printTotalAvisos($n)
    {
        if ($n > 0) {
            return '<div><span data-number="'.$n.'" id="stadistics1">'.$n.'</span><span>Avisos activos</span></div>';
        }
    }

    /**
     * Devuelve html con número de avisos publicados hoy
     *
     * @param int $n Total de avisos publicados hoy
     * @return string
     */
    public function printTotalAvisosHoy($n)
    {
        if ($n > 0) {
            return '<div><span data-number="'.$n.'" id="stadistics2">'.$n.'</span><span>Avisos publicados hoy</span></div>';
        }
    }

    /**
     * Devuelve html con número de empresas con avisos activos
     *
     * @param int $n Total de empresas con avisos activos
     * @return string
     */
    public function printTotalEmpresasPublicando($n)
    {
        if ($n > 0) {
            return '<div><span data-number="'.$n.'" id="stadistics3">'.$n.'</span><span>Empresas publicando</span></div>';
        }
    }

    /**
     * Devuelve html con el botón de registro para parte inferior del home
     *
     * @param bool $ocultar
     * @return string Html de botón Registrar
     */
    public function mostrarBotonRegistroHome($ocultar)
    {
        if (!$ocultar) {
            return '<div class="right_wrapper"><p>Solo te tomará unos minutos</p><button class="btn btn_tertiary register_init">Regístrate</button></div>';
        }
    }

    /**
     * Devuelve un TRUE si existe un usario logeado como empresa
     * @return True devuelve un valor de verdad
     */
    public function activoEmpresa($layout)
    {
        if (isset($layout->auth) && isset($layout
                ->auth["usuario"]) && in_array($layout
                ->auth["usuario"]->rol,
                array(
                App_Controller_Action::ADMIN_EMPRESA,
                App_Controller_Action::USUARIO_EMPRESA))) {

            return true;
        }
        return false;
    }

    public function ValidaEpannning()
    {
        $actions_enabled   = array(
            'mis-datos-personales',
            'mi-ubicacion',
            'mis-experiencias',
            'mis-estudios',
            'mis-otros-estudios',
            'mis-idiomas',
            'mis-programas',
            'mis-logros',
            'mi-perfil'
        );
        $controlle_enabled = array(
            'mi-cuenta',
            "buscar"
        );
        $isMyAccount       = MODULE == 'postulante' && in_array(CONTROLLER,
                $controlle_enabled) &&
            in_array(ACTION, $actions_enabled);
        if ($isMyAccount) {
            return true;
        }
        return false;
    }

    /**
     * Funcion que validad el sitio que te encuentras por el lado de empresa
     * @return true
     */
    public function getEmpresaValid()
    {

        $enabled = array(
            'home' => array(
                'index' => 0
            ),
            'registro-empresa' => array(
                'index' => 0
            ),
            'publica-aviso' => array(
                'paso1' => 0,
                'paso2' => 0
            ),
            'comprar-aviso' => array(
                'pago-satisfactorio' => 0
            )
        );
        return MODULE == 'empresa' && isset($enabled[CONTROLLER][ACTION]);
    }

    /**
     * Funcion que validad el sitio que te encuentras por el lado de empresa
     * @return true
     */
    public function getPostulanteMainValid()
    {

        $enabled = array(
            'home' => array(
                'index' => 0,
                'landing' => 0,
                'widget'=>0
            ),
            'registro-empresa' => array(
                'index' => 0
            ),
            'buscar' => array(
                'index' => 0
            ),
            'aviso' => array(
                'ver' => 0
            ),
            'postulaciones' => array(
                'index' => 0
            ),
            'error' => array(
                'error' => 0
            )
        );
        return (MODULE == 'postulante' && isset($enabled[CONTROLLER][ACTION]));
    }

    /**
     * Funcion que valida si esta en el modulo postulante
     * @return true
     */
    public function getPostulanteValid()
    {
        $enabled = array(
            'home' => array(
                'perfil-destacado' => 0,
                'porque-usar-aptitus' => 0,
                'politica-privacidad' => 0,
                'index' => 0,
                'landing' => 0,
                'widget'=>0
            ),
            'buscar' => array(
                'index' => 0
            ),
            'postulaciones' => array(
                'index' => 0
            ),
            'aviso' => array(
                'ver' => 0
            )
            ,
            'error' => array(
                'error' => 0
            )
        );
        if (CONTROLLER == 'auth') {
            return false;
        }

        return MODULE == 'postulante' && !isset($enabled[CONTROLLER][ACTION]);
    }

    /**
     *
     * @param type $elemet
     * @return type
     */
    public function LimpiarEtiquetas($elemet)
    {


        $elemet = str_replace('id="main_banner-label"',
            'style="width: 0px;height: 0px;"', $elemet);
        $elemet = str_replace('class="errors"',
            ' style="width: 0px;height: 0px;"', $elemet);
        return $elemet;
    }

    /**
     * Retorna verdadero si se encuentra en la ficha del aviso
     *
     * @return boolean
     */
    public function validaFichaAviso()
    {
        if (MODULE == 'postulante' && CONTROLLER == 'aviso' && ACTION == 'ver') {
            return true;
        }
        return false;
    }

    public static function cleanString($string)
    {

        $string = trim($string);

        $string = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $string
        );

        $string = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $string
        );

        $string = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $string
        );

        $string = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $string
        );

        $string = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $string
        );

        $string = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $string
        );

        //Esta parte se encarga de eliminar cualquier caracter extraño
        $string = str_replace(
            array(" ", "\\", "¨", "º", "-", "~", "°",
            "#", "@", "|", "!", "\"",
            "·", "$", "%", "&", "/",
            "(", ")", "?", "'", "¡",
            "¿", "[", "^", "`", "]",
            "+", "}", "{", "¨", "´",
            ">", "< ", ";", ",", ":", ",",
            ".", " "), '-', $string
        );
        return $string;
    }
}
