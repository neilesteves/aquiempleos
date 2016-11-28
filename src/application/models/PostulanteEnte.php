<?php

class Application_Model_PostulanteEnte extends App_Db_Table_Abstract
{
    protected $_name = "postulante_ente";
    
    const ACTIVO = 1;
    
    public function registrar($idPos,  $enteId)
    {
        $data   = array();
        $fecha  = new Zend_Date();
        
        $data['ente_id']        = $enteId;
        $data['id_postulante']     = $idPos;
        $data['esta_activo']    = self::ACTIVO;
        $data['fh_creacion']    = $fecha->get('YYYY-MM-dd HH:mm:ss');
        $this->insert($data);
    }
    
    public function getRegistroEnte($idPostulante)
    {
        $sql = $this->getAdapter()->select()
            ->from(array($this->_name, array('ente_id')))
                ->where('id_postulante = ?', $idPostulante);
                
        $data = $this->getAdapter()->fetchRow($sql);
        
        $postulante = new Application_Model_Postulante;
        $adecsysEnte = new Application_Model_AdecsysEnte;
        $dataPost = $postulante->find($idPostulante)->toArray();
        
        $dni = $dataPost[0]['num_doc'];
        
        if (isset($data['ente_id'])) {
            return $data['ente_id'];
        } else {
            //Con el CI se obtiene el id de adecsys
            $dataAE = $adecsysEnte->obtenerPorDocumento($dni);
            if (is_null($dataAE)) {
                return null;
            } else {
                return $dataAE['id'];
            }
            
        }
        
        return null;
    }
}