<?php

class Application_Model_AnuncioProgramaComputo extends App_Db_Table_Abstract
{
    protected $_name = "anuncio_programa_computo";

    public function getUrlById($idEstudio)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('aw' => 'anuncio_web'), array('aw.url_id'))
            ->joinInner(array('ap' => $this->_name), 'ap.id_anuncio_web = aw.id', array())
            ->where('ap.id = ?', $idEstudio);
        $rs = $this->getAdapter()->fetchOne($sql);
        return $rs;
    }
}