<?php

/**
 * Description of Hace
 *
 * @author eanaya
 */
class App_View_Helper_NombreArea extends Zend_View_Helper_HtmlElement
{

    /**
     * @link http://css-tricks.com/snippets/php/time-ago-function/
     * @param  String
     * @return string
     */
    public function NombreArea($slug)
    {
        switch ($slug) {
          
            case 'almacen':
                $nombre='all';
                break;
            
            case 'banca':
                $nombre='all';
                break;
            
            case 'comunicaciones':
                $nombre='';
                break;
            
            case 'consultoria':
                $nombre='consultoria';
                break;
            
            case 'control-aseguramiento-calidad':
                $nombre='all';
                break;           
            case 'ingenieria':
                $nombre='ingenieria';
                break;
            
            case 'mantenimiento-equipos-maquinarias':
                $nombre='all';
                break;
            
            case 'medios-digitales-internet':
                $nombre='all';
                break;           
            
            case 'otros':
                $nombre='otros';
                break;        
            
            default:
                $nombre=$slug;
                break;
        }
        return $nombre;
    }
    
}