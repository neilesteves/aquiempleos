<?php

class Application_Model_AnuncioProfile extends App_Db_Table_Abstract
{
    protected $_name = "anuncio_profile";

    public function getNumAnuncioProcesado($minimoItems = 2)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('ap' => $this->_name), 'ap.id_anuncio_web')
            ->joinInner(
                array('amt' => 'anuncio_match_temp'), 
                'amt.id_anuncio_web = ap.id_anuncio_web',
                array('amt.id_empresa','total_estudio', 'total_idioma', 'total_experiencia', 'total_computo')
            )
            ->where()
            ->group('ap.id_anuncio_web');
        return $this->getAdapter()->fetchAll($sql);
    }
}