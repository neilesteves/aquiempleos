<?php

class Application_Model_AnuncioEstudio extends App_Db_Table_Abstract
{
    protected $_name = "anuncio_estudio";
    
    public function getUrlById($idEstudio)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('aw' => 'anuncio_web'), array('aw.url_id'))
            ->joinInner(array('ae' => $this->_name), 'ae.id_anuncio_web = aw.id', array())
            ->where('ae.id = ?', $idEstudio);
        $rs = $this->getAdapter()->fetchOne($sql);
        return $rs;
    }
    
    public function getValCarrera($id)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('c' => 'carrera'), array('c.nombre')) 
            ->where('c.id = ?', $id);
        $rs = $this->getAdapter()->fetchRow($sql);
        return $rs;
       
    }
    
    public function getAnuncioEstudioByIdAnuncioWeb ($anuncioId)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('ae' => $this->_name), array('ae.id')) 
            ->where('ae.id_anuncio_web = ?', $anuncioId);
        $rs = $this->getAdapter()->fetchOne($sql);
                
        return $rs;
        
    }

    

}