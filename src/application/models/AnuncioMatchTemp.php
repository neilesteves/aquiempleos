<?php

class Application_Model_AnuncioMatchTemp extends App_Db_Table_Abstract
{
    protected $_name = "anuncio_match_temp";

    public function getTotalAnuncios($minimoItems = 2)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(array('amt' => $this->_name), array( 'total' => new Zend_Db_Expr('COUNT(*)')))
            ->where("(total_idioma + total_estudio + total_experiencia + total_computo) >= ? ", $minimoItems);
        return $db->fetchOne($sql);
    }

    public function getAnuncios($minimoItems = 2, $offset = 0)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('amt' => $this->_name), 
                array(
                    'amt.id',
                    'amt.id_anuncio_web',
                    'amt.id_empresa',
                    'amt.total_estudio',
                    'amt.total_experiencia',
                    'amt.total_idioma',
                    'amt.total_computo',
                    'amt.peso_estudio',
                    'amt.peso_experiencia',
                    'amt.peso_idioma',
                    'amt.peso_computo',
                    'amt.total_peso'
                )
            )
            ->where("(total_idioma + total_estudio + total_experiencia + total_computo) >= ? ", $minimoItems)
            ->limit(1000, $offset);
        
        return $db->fetchAll($sql);
    }

}