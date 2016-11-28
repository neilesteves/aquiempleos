<?php

/**
 * Description of Util
 *
 * @author eanaya
 */
class App_Util
{
    private $_dniTorucFixedValues = array(5,4,3,2,7,6,5,4,3,2);
    
    public function dni2ruc(String $dni)
    {
        $dni = str_pad($dni, 8, '0', STR_PAD_LEFT);
        if (strlen($dni) == 8) {
            throw new Zend_Exception("El CI debe tener 8 dígitos");
        }
        $sum = 0;
        foreach ( $this->_dniTorucFixedValues as $key => $value ) {
            $sum += intval($dni[$key]) * $value;
        }
        $validationDigit = 11 - ($sum % 11);
        return '10' . $dni . $validationDigit;
    }
    
    public function dateEst($fecha)
    {
        $nMes = array('Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
        $dFecha = explode('-', $fecha);
        $nroMes = (int)$dFecha[1];
        return "$dFecha[2] {$nMes[$nroMes - 1]}";
    }
    
    public function array_column($array, $column)
    {    
        $a2 = array();
        array_map(function ($a1) use ($column, &$a2) {
            if (isset($a1[$column])) {
                array_push($a2, $a1[$column]);
            }            
        }, $array);
        return $a2;
    }
    
    public function setFormatDate($fecha)
    {
        $meses = array(
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
            'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        );
        
        $d = new DateTime($fecha);                
        
        $formatFecha = 
            $d->format('d'). 
            ' de '.$meses[$d->format('n')-1].
            ' del '.$d->format('Y');        

        return $formatFecha;
    }
    
    public function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }    
    
    public static function setMonth($mes)
    {
        if(empty($mes))
            return '';
        $meses = array(
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
            'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        );
        return ($meses[$mes-1]);
    }
    
    public static function getMonths() {
        $mes[1]="Enero";
        $mes[2]="Febrero";
        $mes[3]="Marzo";
        $mes[4]="Abril";
        $mes[5]="Mayo";
        $mes[6]="Junio";
        $mes[7]="Julio";
        $mes[8]="Agosto";
        $mes[9]="Septiembre";
        $mes[10]="Octubre";
        $mes[11]="Noviembre";
        $mes[12]="Diciembre";
        return $mes;
    }
    
    public static function clearXSS($data= array()){
        $etiquetas="";
        $datos= array();
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $datos2 = array();
                foreach ($value as $key2 => $item) {
                    $datos2[$key2] = strip_tags($item);
                }
                $datos[$key]=$datos2;
                unset($datos2);
            } else {
                $datos[$key]=strip_tags($value);
            }
        }
        return $datos ;
    }
    
    public static function fieldRequired($params, $obligatories)
    {
        foreach ($obligatories as $obliga) {
            if (!isset($params[$obliga])) {
                return false;
            }
        }
        return true;
    }
    
    
    public function paramSolr($data)
    {
        $arr = array();
        $data = str_replace(array(':','='), '', $data);
        $arrData = explode('--', $data);
        foreach($arrData as $item){
            $item = trim($item);
            if( strlen($item) < 2 || FALSE == $item || NULL == $item ) {
                continue;
            }
//            $arr[] = $item;
            $arr[]  = preg_replace('/([^a-z]+)$/i', '', $item);
        }
        $arrData = array_filter($arr);
        return count($arrData) > 0 ? implode(' OR ', $arrData) : array();
    }
    
    public function paramSolrUbigeo($data)
    {
        $arr = array();
        $data = str_replace(array(':','='), '', $data);
        $arrData = explode('--', $data);
        foreach($arrData as $item){
            $item = trim($item);
            if( strlen($item) < 2 || FALSE == $item || NULL == $item ) {
                continue;
            }
//            $arr[] = $item;/[^a-zñÑáéíóúÚ0-9-]/i
            $arr[]  = preg_replace('/([^a-zñÑáéíóúÚ]+)$/i', '', $item);
        }
        $arrData = array_filter($arr);
        return count($arrData) > 0 ? implode(' OR ', $arrData) : array();
    }
    public static function calculaEdad($birthDate) 
    {
        $today = new Zend_Date();
        $dateOfBirth = new Zend_Date($birthDate, 'Y-m-d');
        $todayLeap = $birthLeap = 0;
        /*** Considering the leap years if the birth date is on 1st march or later. ***/
        if ($dateOfBirth->get('M') > 2) {
            /*** Checking if the birth's year is a leap year. ***/
            if ($dateOfBirth->get(Zend_Date::LEAPYEAR)) {
                $birthLeap = 1;
            }

            /*** Checking if today's year is a leap year. ***/
            if ($today->get(Zend_Date::LEAPYEAR)) {
                $todayLeap = 1;
            }
        }

        $age = $today->get('Y') - $dateOfBirth->get('Y');

        if ($today->get('D') - $todayLeap < $dateOfBirth->get('D') - $birthLeap) {
            $age--;
        }
        return $age;
    }
    public function divideArray($array,$columnas)
    {
        $total = count($array);
        $cantidad = ceil($total/$columnas);
        $arr = array();
        $i = 0;
        foreach($array as $k => $v)
        {
            $arr[$i/$cantidad][$k]=$v;
            $i++;
        }
        return $arr;
    }
    
    public static function estaEnHomePostulante() {
        if (MODULE == 'postulante' && CONTROLLER == 'home' && ACTION == 'index') {
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Reemplaza caracteres especiales de slug
     * 
     * @param string $slugOriginal Slug en el que se buscará caracteres especiales
     * @return string 
     */
    public static function validateUrlSlug($slugOriginal) {
      $especiales = array("&",",",'-----','----','---',"--");
      $reemplazos = array("and","","-","-","-","-");
      $slug = str_replace($especiales, $reemplazos, $slugOriginal);
      
      return $slug;
    }
    
    public static function validateSlugEmpresa($slugOriginal, $mostrarEmpresa) 
    {
        return ($mostrarEmpresa!=1)?"importante-empresa":$slugOriginal;
    }
}
