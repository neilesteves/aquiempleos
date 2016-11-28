<?php

/**
 * Retorna los dias restantes a una determinada fecha agregando el texto 
 * caracteristico determinando si la fecha ya paso o aun no.
 *
 * @author Jesus Fabian
 */
class App_View_Helper_DiasRestantes extends Zend_View_Helper_HtmlElement
{
    public function DiasRestantes($fecha)
    {
        if ($fecha == "") {
            return "";
        }
      
        $time = strtotime($fecha);
        $timeActual = strtotime(date('Y-m-d'));
       // $restante = $time - $timeActual;
        //  var_dump(date('Y-m-d', $restante));
          
        $datetime1 = new DateTime(date('Y-m-d'));
        $datetime2 = new DateTime($fecha);
        $interval = $datetime1->diff($datetime2);
        $restante= $interval->format('%a'); 
          
        if ($restante >= 0) {
            if ($restante == 0) {
                $str = "Hoy";
            } else {
                $str = "Faltan ".$restante." días";
            }
        } else {
            $restante = abs($restante);
            if ($restante == 86400) {
                $str = "Ayer";
            } else {
                $str = "Hace ".$restante." días";
            }
        }
        return '<span class="dateData" title="'.$fecha.'">'.$str.'</span>';
    }
    
}