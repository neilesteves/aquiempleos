<?php


/**
 * Description of Util
 * 
 * @author eanaya
 *
 */
class App_Controller_Action_Helper_Util
    extends Zend_Controller_Action_Helper_Abstract
{
    
//    private $securekey;
//    private $iv_size;
//    
//    public function init() 
//    {
//        
//        $this->iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
//        $this->securekey = hash('sha256', "()\~!@#$%^&*'+", TRUE);
//        
//        return parent::init();
//    }

    
    public function getRepetido($pattern, $post)
    {
        $currentValues = array();
        foreach ($post as $key => $value) {
            if (preg_match($pattern, $key)) {
                if (in_array($value, $currentValues) && $value != -1) {
                    return $value;
                } else {
                    $currentValues[] = $value;
                }
            }
        }
        return false;
    }

    public function getUbigeo($valuesPostulante)
    {
        $idPais = isset($valuesPostulante['pais_residencia']) ? $valuesPostulante['pais_residencia']
                : NULL;
        $idDep = isset($valuesPostulante['id_departamento']) ? $valuesPostulante['id_departamento']
                : NULL;
        $idProv = isset($valuesPostulante['id_provincia']) ? $valuesPostulante['id_provincia']
                : NULL;

        //sacar de constante en ubigeo
        $idPeru = Application_Model_Ubigeo::PERU_UBIGEO_ID;
        $idLima = Application_Model_Ubigeo::LIMA_UBIGEO_ID;
        $idLimaProv = Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID;
        $idCallaoProv = Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID;

        if ($idPais != $idPeru) {
            $idUbigeo = $idPais;
        } else {
            $idUbigeo = $valuesPostulante['id_provincia'];
        }
        return $idUbigeo;
    }
    
    
    public function limitWords($limite, $cadena) 
    {
        $texto = $cadena;
        $totPalabras = str_word_count($cadena);        
        if ($totPalabras > $limite) {
            $arPalabras = explode(' ',$cadena);
            $texto = implode(' ',array_slice($arPalabras, 0, $limite)).'...';
        }         
        
        return $texto;
        
    }
    
    
    public function encriptalo($pure_string) 
    {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $securekey = hash('sha256', "()\~!@#$%^&*'+", TRUE);
        
        $iv = mcrypt_create_iv($iv_size);
        return base64_encode($iv . mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $securekey, $pure_string, MCRYPT_MODE_CBC, $iv));
    }


    public function desencriptalo($encrypted_string) 
    {
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $securekey = hash('sha256', "()\~!@#$%^&*'+", TRUE);
        
        $input = base64_decode($encrypted_string);
        $iv = substr($input, 0, $iv_size);
        $cipher = substr($input, $iv_size);
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $securekey, $cipher, MCRYPT_MODE_CBC, $iv));
    }
 
    
    public function codifica($string)
       {
       $control = "fumoffu"; //defino la llave para encriptar la cadena, cambiarla por la que deseamos usar
       $string = $control.$string.$control; //concateno la llave para encriptar la cadena
       $string = base64_encode($string);//codifico la cadena
       return $string;
       }
    public function decodifica($string)
       {
        $string = base64_decode($string); //decodifico la cadena 
        $control = "fumoffu"; //defino la llave con la que fue encriptada la cadena,, cambiarla por la que deseamos usar
        $string = str_replace($control, "", $string); //quito la llave de la cadena
        return $string;
       }

       public function ZessionRegistro($string,$valor){
           
           $sesionRegistro = new Zend_Session_Namespace($string);
           
           
           if($valor){
                $sesionRegistro->$string=true;
                return $sesionRegistro->$string;
            }elseif(isset($sesionRegistro->$string)) {
                return $sesionRegistro->$string;
            }else{
                return false;
           } 
           return false;
       }
           
      public function _crearSlug($valuesPostulante, $lastId ,$modelo) {
        $slugFilter = new App_Filter_Slug(
                array(
            'field' => 'slug',
            'model' => $modelo
                )
        );

        $slug = $slugFilter->filter(
                $valuesPostulante['nombres'] . ' ' .
                $valuesPostulante['apellido_paterno'] . ' ' .
                $valuesPostulante['apellido_materno'] . ' ' .
                substr(md5($lastId), 0, 8)
        );
        return $slug;
    }
    
    public function _NexAction($question,$id){
        $response=null;       
        $postulante= new  Application_Model_Postulante();
        if(!$id){
            return null;
        }
        $updateCV = $postulante->hasDataForApplyJob($id);
       
            if(!$updateCV){
                $response='winUpdateCV';
            }elseif ($question ) {
                $response='questionsWM';
            }else{
               $response='postular'; 
            }
        
        return $response;        
    }
    public static function  cleanString($string)
    {

        $string = trim($string);

        $string = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $string
        );

        $string = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $string
        );

        $string = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $string
        );

        $string = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $string
        );

        $string = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $string
        );

        $string = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç'),
            array('n', 'N', 'c', 'C',),
            $string
        );

        //Esta parte se encarga de eliminar cualquier caracter extraño
        $string = str_replace(
            array("\\", "¨", "º", "-", "~","°",
                 "#", "@", "|", "!", "\"",
                 "·", "$", "%", "&", "/",
                 "(", ")", "?", "'", "¡",
                 "¿", "[", "^", "`", "]",
                 "+", "}", "{", "¨", "´",
                 ">", "< ", ";", ",", ":",",",
                 ".", " "),
            '_',
            $string
        );


        return $string;
    }
    
    public function salarios() {
        $config = Zend_Registry::get('config');
        $moneda = $config->app->moneda;
        $rango = $config->salarios->filtros->rangoRemuneracion->toArray();
        $data = array();
     
        $text="";
        for ($i = 0; $i < count($rango); $i++) {
            
           $rango[$i]=  $moneda.number_format($rango[$i]);
           //$text=$text.''.$rango[$i].',';
        }
        //var_dump(Zend_Json::encode($rango));exit;
        return ($rango);
    }
    
    public static function esMayorDeEdad($fecha) {
        $fh_nac = new DateTime(str_replace("/","-",$fecha));
        $hoy = new DateTime(date('d-m-Y'));
        $dif = $fh_nac->diff($hoy);
        $anhos = intval($dif->format('%y'));
        
        if ($anhos>=18) {
            return true;
        } else {
            return false;
        }
    }
}


