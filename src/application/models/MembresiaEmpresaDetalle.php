<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Description of DetalleMembresiaEmpresa
 *
 * @author Favio Condori
 */
class Application_Model_MembresiaEmpresaDetalle extends App_Db_Table_Abstract
{
    protected $_name = 'membresia_empresa_detalle';
    
    const ACTIVO   = 1;
    const INACTIVO = 0;
    
    public function obtenerBeneficioPorEmpresa($empresaId, $beneficioCodigo)
    {
        return $this->getAdapter()->fetchRow($this->getAdapter()->select()
            ->from(array('med' => $this->_name), array('med.id'))
            ->joinInner(array('em' => 'empresa_membresia'), 
                    'em.id = med.id_empresa_membresia', array())
            ->joinInner(array('b' => 'beneficio'), 
                    'b.id = med.id_beneficio', array())
            ->where('em.id_empresa =?', $empresaId)
            ->where('em.estado =?', 
                    Application_Model_EmpresaMembresia::ESTADO_VIGENTE)
            ->where('b.codigo =?', $beneficioCodigo));
    }
    
    public function obtenerBeneficioPorId($id, $beneficioCodigo)
    {
        return $this->getAdapter()->fetchRow($this->getAdapter()->select()
            ->from(array('med' => $this->_name), array('med.id'))
            ->joinInner(array('em' => 'empresa_membresia'), 
                    'em.id = med.id_empresa_membresia', array())
            ->joinInner(array('b' => 'beneficio'), 
                    'b.id = med.id_beneficio', array())
            ->where('em.id =?', $id)
            ->where('em.estado =?', 
                    Application_Model_EmpresaMembresia::ESTADO_VIGENTE)
            ->where('b.codigo =?', $beneficioCodigo));
    }
    
    public function obtenerBeneficiosPorEmpresaMembresia($id_empresa_membresia,$benficoweb)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('med' => $this->_name), array('med.id','med.descripcion'))
            ->joinInner(array('em' => 'empresa_membresia'), 
                    'em.id = med.id_empresa_membresia', array())            
            ->where('med.id_empresa_membresia = ?', $id_empresa_membresia)
            
            ->order('med.codigo DESC')
            ->order('med.id_beneficio ASC');
          if($benficoweb){
           $sql->where("med.codigo IN ('memprem-web','memprem-imp','memprem-adic','memsele-web','memsele-imp','memsele-adic','memesen-web','memesen-imp','memesen-adic','memdigi')");
        }
        return $this->getAdapter()->fetchAll($sql);
            
    }
    
}
