<?php

class Application_Model_AnuncioWebDetalle extends App_Db_Table_Abstract
{
    protected $_name = "anuncio_web_detalle";
    
    const CODIGO_NUMERO_PALABRAS = 'npalabras';
    
    public function getDetalle($idAnuncioWeb)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('awd' => 'anuncio_web_detalle'),
                array('codigo','descripcion','valor','precio')
            )
            ->where('id_anuncio_Web = ?', $idAnuncioWeb);
        $result = $this->getAdapter()->fetchAll($sql);
        return $result;
    }
    
    public function getIdsXAnuncio ($idAnuncioWeb)
    {
        $sql = $this->getAdapter()
            ->select()
            ->from($this->_name, 'id')
            ->where('id_anuncio_web = ?', $idAnuncioWeb);
        return $this->getAdapter()->fetchAll($sql);
    }
}