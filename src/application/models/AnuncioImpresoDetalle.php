<?php

class Application_Model_AnuncioImpresoDetalle extends App_Db_Table_Abstract
{
    protected $_name = "anuncio_impreso_detalle";
    
    public function obtenerPorAnuncio($anuncioId)
    {
        $config = Zend_Registry::get('config');
        if(isset($config->extracargosAvisos)){
            return $this->fetchAll($this->select()
            ->from(array('aid' => $this->_name), array('adecsys_cod'))
            ->where('id_anuncio_impreso =?', (int)$anuncioId)
            ->where("codigo NOT IN('descuento-talan','descuento-aptitus')"))->toArray();
  
        }
        return $this->fetchAll($this->select()
            ->from(array('aid' => $this->_name), array('adecsys_cod'))
            ->where('id_anuncio_impreso =?', (int)$anuncioId))->toArray();
    }
    
     public function obtenerDescuentos($anuncioId,$medidepublicacion)
    {
        return $this->fetchAll($this->select()
            ->from(array('aid' => $this->_name), array('adecsys_cod'))
            ->where('id_anuncio_impreso =?', (int)$anuncioId)
            ->where('codigo =?',"$medidepublicacion"))->toArray();
    }
}
