<?php

class Application_Model_Anio extends App_Db_Table_Abstract
{
    
    /**
     * Retorna el menor año de la lista
     * 
     * @return int
     */
    public static function getMinAnio()
    {
        return 1950;
    }
    
    /** 
     * Lista de anios permitidos por el sistema
     * 
     * @return string
     */
    
    public static function getAnios()
    {
        $lista = array();
        $minAnio = self::getMinAnio();
        for ($i = $minAnio; $i <= date('Y'); $i++) {
            $lista[$i] = $i;
        }
        return $lista;
    }
}