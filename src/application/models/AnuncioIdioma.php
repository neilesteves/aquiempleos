<?php

class Application_Model_AnuncioIdioma extends App_Db_Table_Abstract
{
    protected $_name = "anuncio_idioma";

    public function getUrlById($idEstudio)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('aw' => 'anuncio_web'), array('aw.url_id'))
            ->joinInner(array('ai' => $this->_name), 'ai.id_anuncio_web = aw.id', array())
            ->where('ai.id = ?', $idEstudio);
        $rs = $this->getAdapter()->fetchOne($sql);
        return $rs;
    }
    
}