<?php

/**
 * Description of Util
 *
 * @author svaisman
 */
class App_View_Helper_VerAviso extends Zend_View_Helper_Abstract
{
    protected $caracteres;
    protected $_aviso;

    public function VerAviso($data = array())
    {
        $this->_aviso = $data;
        return $this;
    }

    public function estudio($estudio)
    {
        $mesaje = '';
        foreach ($estudio as $key => $value) {
            echo $this->uc_latin1($value['nivel_estudio"'].' '.$value['nivel_estudio_tipo"'].' en '.$value['carrera']).'<br/>';
        }
    }

    public function requisitos($value, $tipo)
    {
        switch ($tipo) {
            case 'estudios':
                if (empty($value['nivel_estudio_tipo'])) return $value['nivel_estudio'];

                $carrera = $value['carrera'];
                if (empty($value['carrera'])) $carrera = $value['otra_carrera'];
                return $value['nivel_estudio'].' '.$value['nivel_estudio_tipo'].' en '.$carrera;

                break;
            case 'experiencias':
                return 'En el area de '.$value['nombre_area'].$this->transformarMeses($value['experiencia']);

                break;
            case 'programas':
                return $value['nombre_programa'].' con nivel '.$value['nivel_programa'];
                break;
            case 'idiomas':
                return $value['nombre'].' con nivel '.$value['nivel_idioma'];

                break;
            default:
                break;
        }
    }

    public function onclick($auth, $slug, $urlId)
    {
        if (!$auth) {
            $url = $this->view->url(
                array(
                'url_id' => $urlId,
                'slug' => $slug
                ), 'postularAviso', true
            );
            return 'onclick="window.location.href = '.$url.'"';
        }
    }

    public function modal($auth, $postulado = true, $question = false)
    {
        if (!$auth) {
            return '#modalLoginUser';
        }
        if ($postulado) {
            return '';
        }
        if ($question) {
            return '#modal_comentarios';
        }
        if (!$postulado) {
            return $this->view->url(
                    array(
                    'url_id' => $this->_aviso['url_id'],
                    'slug' => $this->_aviso['slug']
                    ), 'postularAviso', true
            );
        }
        return '#modal_comentarios';
    }

    public function medalla()
    {

        switch ($this->_aviso['prioridad']) {
            case '1':
                return 'medal1.svg';
                break;
            case '2':
                return 'medal2.svg';
                break;
            default:
                return null;
                break;
        }
    }

    public function medaList()
    {

        switch ($this->_aviso['prioridad']) {
            case '1':
                return '-a';
                break;
            case '2':
                return '-b';
                break;
            default:
                return '';
                break;
        }
    }

    public function experincia($experiencia)
    {
        $mesaje = '';
        foreach ($experiencia as $key => $value) {
            echo 'Experiencia: en el área de '.$this->uc_latin1($value['nombre_area'].' con '.$value['experiencia']).'<br/>';
        }
    }

    public function programas($programas)
    {
        $mesaje = '';
        foreach ($programas as $key => $value) {
            echo 'Manejo de programas:'.$this->uc_latin1($value['nombre_programa'].' a nivel '.$value['nivel_programa']).'<br/>';
        }
    }

    public function idioma($idioma)
    {
        $mesaje = '';
        foreach ($idioma as $key => $value) {
            echo 'Idioma '.$this->uc_latin1($value['idioma'].' a nivel '.$value['nivel']).'<br/>';
        }
    }

    protected function uc_latin1($str)
    {
        $str = strtolower(strtr($str, 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝ',
                'àáâãäåæçèéêëìíîïðñòóôõöøùúûüý'));
        $str = strtr($str, array("ß" => "SS"));
        $str = ucfirst($str);
        return $str;
    }

    public function detalle($detalle, $num)
    {
        if (strlen($detalle) < 300) {
            return $detalle;
        }
        return mb_substr($detalle, 0, $num, 'utf-8').' .......';
    }

    public function tipoAvios()
    {
        switch ($this->_aviso['prioridad']) {
            case '1':
                return 'box-subtitular-d-oro';
                break;
            case '2':
                return 'box-subtitular-d-plata';
                break;
            default:
                return 'box-subtitular';
                break;
        }
    }

    public function transformarMeses($meses)
    {
        if($meses >= 12){
            $anios = ($meses)/12;
            $anios = round($anios,0);

            if($anios==1)   return ' con '.$anios.' año de experiencia';  
            if($anios >= 5)  return ' con '.$anios.' años o más de experiencia';
            
            return ' con '.$anios.' años de experiencia';
    
        }
        else{
            return $meses == 0 ? ' sin experiencia': ' con '.$meses . ' meses de experiencia';
        }
    }

    public function remuneracion($salario_min,$salario_max,$moneda)
    {
        if(empty($salario_min) && empty($salario_max)){
            return ' - ';
        }
        if(empty($salario_min)){
            return "$moneda 0 - $moneda $salario_max";
        }
        if (empty($salario_max)) {
            return "$moneda $salario_min - A más";
        }

        return "$moneda $salario_min - $moneda $salario_max";
    }
}