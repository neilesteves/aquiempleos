<?php

class Application_Model_PostulanteProfile extends App_Db_Table_Abstract
{
    protected $_name = "postulante_profile";
    
    public function getNumPostulanteProcesado()
    {
        $sql = $this->getAdapter()->select()
            ->from(array('pf' => $this->_name), 'pf.id_postulante')
            ->joinInner(
                array ('pmt' => 'postulante_match_temp'), 
                'pf.id_postulante = pmt.id_postulante',
                array()
            )
            ->group('pf.id_postulante');
        return $this->getAdapter()->fetchAll($sql);
    }
}