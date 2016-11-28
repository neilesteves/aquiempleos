<?php

class Application_Model_EmpresaEnte extends App_Db_Table_Abstract
{
    protected $_name = "empresa_ente";
    
    const ACTIVO = 1;
    
    public function getEmpresaEnteXIdEmpresa($idEmpresa)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('ee'=> $this->_name), array())
            ->joinInner(
                array('ae' => 'adecsys_ente'),
                $this->getAdapter()->quoteInto('ee.empresa_id = ae.id AND ee.empresa_id  = ?', $idEmpresa),
                array('ae.ente_cod')
            );
        return $this->getAdapter()->fetchOne($sql);
    }
    
    public function registrar($empresaId,  $enteId)
    {
        $data   = array();
        $fecha  = new Zend_Date();
        
        $data['ente_id']        = $enteId;
        $data['empresa_id']     = $empresaId;
        $data['esta_activo']    = self::ACTIVO;
        $data['fh_creacion']    = $fecha->get('YYYY-MM-dd HH:mm:ss');
        
        return $this->insert($data);
    }
    
    public function getRegistroEnte($idEmpresa)
    {
        $sql = $this->getAdapter()->select()
            ->from(array($this->_name, array('ente_id')))
                ->where('empresa_id = ?', $idEmpresa);
                
        $data = $this->getAdapter()->fetchRow($sql);
        ///echo $sql;exit;

        if (isset($data['ente_id'])) {
            return $data['ente_id'];
        }
        
        return null;
    }
}