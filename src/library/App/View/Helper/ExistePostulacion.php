<?php

/**
 * Description of Util
 *
 * @author svaisman
 */
class App_View_Helper_ExistePostulacion extends Zend_View_Helper_Abstract
{
    public function ExistePostulacion($idPostulante, $idAnuncio)
    {
        
       
        $anuncioModel = new Application_Model_AnuncioWeb;
        $existePostulacion = $anuncioModel->existePostulacion($idPostulante, $idAnuncio);
        
        return $existePostulacion;
    }
}