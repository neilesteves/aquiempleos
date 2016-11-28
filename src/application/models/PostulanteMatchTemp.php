<?php

class Application_Model_PostulanteMatchTemp extends App_Db_Table_Abstract
{
    protected $_name = 'postulante_match_temp';

    public function getTotalPostulantes()
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(array('pmt' => $this->_name), new Zend_Db_Expr('COUNT(*)'));
        return $db->fetchRow($sql);
    }

    public function getPostulantes($offset = 0)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(array('pmt' => $this->_name), new Zend_Db_Expr("DISTINCT(pmt.id_postulante)"))
            ->join(
                array('pp' => 'postulante_profile'), 
                'pmt.id_postulante = pp.id_postulante', 
                array()
            )
            ->limit(1000, $offset);
            
        return $db->fetchAll($sql);
    }

}