<?php


/**
 * Description of SlugFilter
 *
 * @author Usuario
 */
class App_Filter_Slug
    implements Zend_Filter_Interface
{

    /**
     * Method used to generate slug
     *
     * @param string $value String to generate its slug
     *
     * @return string Generated slug
     */
    protected $_replace = '-';

    public function __construct($config = array())
    {
        if (!empty($config)) {
            $this->setConfig($config);
        }
    }

    /**
     * 
     * @param type $value
     * @return string
     */
    public function filter($value)
    {
        $value = str_replace(
            array("á", "é", "í", "ó", "ú", "ä", "ë", "ï", "ö", "ü", "ñ"),
            array("a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "n"),
            mb_strtolower($value, 'UTF-8')
        );
        // Generate slug by removing unwanted (other than alphanumeric 
        //and dash [-]) characters from the string
        $value = preg_replace('/[^a-z0-9-]/i', $this->_replace, $value);
        $value = preg_replace('/-[-]*/', $this->_replace, $value);
        $value = preg_replace('/-$/', '', $value);
        $value = preg_replace('/^-/', '', $value);
        return $value;
    }
     public function filter1($value)
    {
        $value = str_replace(
            array("á", "é", "í", "ó", "ú", "ä", "ë", "ï", "ö", "ü", /*"ñ"*/),
            array("a", "e", "i", "o", "u", "a", "e", "i", "o", "u", /*"n"*/),
            mb_strtolower($value, 'UTF-8')
        );
        // Generate slug by removing unwanted (other than alphanumeric 
        //and dash [-]) characters from the string
        $value = preg_replace('/[^a-zñ0-9-]/i', $this->_replace, $value);
        $value = preg_replace('/-[-]*/', $this->_replace, $value);
        $value = preg_replace('/-$/', '', $value);
        $value = preg_replace('/^-/', '', $value);        
        return $value;
    }
    public function filterUbicacion($value)
    {
   
        $value = preg_replace('/[^a-zñÑáéíóúÚ0-9-]/i', $this->_replace, ($value));
        $value = preg_replace('/-$/', '', $value);
        $value = preg_replace('/^-/', '', $value); 
       // var_dump(utf8_decode($value));
        return  $value;
    }
      public function clean($value)
    {
        $value = preg_replace("/\r\n+|\r+|\n+|\t+/i", " ", $value);
        return $value;        
    }

    /**
     * 
     * @param array $config
     */
    public function setConfig(array $config)
    {
        if (empty($config)) {
            throw new Zend_Exception("Error el Valor Config esta vacio", '500');
        }

        if (isset($config['replace'])) {
            $this->_replace = $config['replace'];
        }
    }
    
     function sanear_string($string)
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
        array(/*'ñ', 'Ñ', */'ç', 'Ç'),
        array(/*'n', 'N', */'c', 'C',),
        $string
    );
    $string = strip_tags($string);
    $string = stripslashes($string);
    //$string = htmlentities($string);
    //Esta parte se encarga de eliminar cualquier caracter extraño
    $string = str_replace(
        array("\\", "¨", "º", "-", "~",
             "#", "@", "|", "!", "\"",
             "·", "$", "%", "&", "/",
             "(", ")", "?", "'", "¡",
             "¿", "[", "^", "`", "]",
             "+", "}", "{", "¨", "´",
             ">", "< ", ";", ",", ":",'"',"'",
             "."),
        '',
        $string
    );

    return $string;
}

}