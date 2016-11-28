<?php

class Application_Model_AnuncioExperiencia extends App_Db_Table_Abstract
{
    protected $_name = "anuncio_experiencia";

    
    public function getUrlById($idExperiencia)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('aw' => 'anuncio_web'), array('aw.url_id'))
            ->joinInner(array('ae' => $this->_name), 'ae.id_anuncio_web = aw.id', array())
            ->where('ae.id = ?', $idExperiencia);
        $rs = $this->getAdapter()->fetchOne($sql);
        return $rs;
    }
    
    public function getAnuncioExperienciaByIdAnuncioWeb($anuncioId)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('ae' => $this->_name), array('ae.id')) 
            ->where('ae.id_anuncio_web = ?', $anuncioId);        
        $rs = $this->getAdapter()->fetchOne($sql);           
        return $rs;
    }
    
}