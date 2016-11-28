<?php


class App_View_Helper_PostuloAvisoRelacionado extends Zend_View_Helper_HtmlElement
{
     
    public function PostuloAvisoRelacionado($aviso)
    {
        $db =  Zend_Db_Table::getDefaultAdapter();
        $auth = Zend_Auth::getInstance()->getIdentity();
        
        if (!isset($auth['postulante'])) {
            return false;
        }
        
        $idPostulante = isset($auth['postulante']->id) ? $auth['postulante']->id : 0;
        $sql = $db->select()
                ->from('postulacion', 'activo')
                ->where('id_anuncio_web = ?', $aviso)
                ->where('id_postulante = ?', $idPostulante)
                ->where('activo = ?', 1);
        
        $data = $db->fetchCol($sql);

        $yaPostuloAvisoRelacionado = (count($data) > 0) ? true : false;
        return $yaPostuloAvisoRelacionado;
         
    }
}