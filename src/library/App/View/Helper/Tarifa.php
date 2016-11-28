<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Tarifa
 *
 * @author ronald
 */
class App_View_Helper_Tarifa extends Zend_View_Helper_Abstract
{

    //put your code here

    public function Tarifa()
    {
        return $this;
    }

    public function Etiqueta( $data )
    {
        switch($data) {
            case 'Web Destacado plata':
                return 'plata';
                break;
            case 'Web Destacado oro':
                return 'oro';
                break;
            case 'Sólo Web':
                return 'simple';
                break;
            default:
                break;
        }
        return 'simple';
    }

    public function Icon( $data )
    {
        switch($data) {
            case 'Destaque Plata':
                return 'fa fa-smile-o green';
                break;
            case 'Destaque Oro':
                return 'fa fa-smile-o s-green';
                break;
            case 'Aviso Simple':
                return 'fa fa-meh-o';
                break;
            default:
                break;
        }
        return 'simple';
    }

    public function precios( $data )
    {
        switch($data) {
            case '0.00':
                return 'Gratis';
                break;
            default:
                return '$' . $data;
                break;
        }
    }

}
