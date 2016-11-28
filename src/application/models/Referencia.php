<?php

class Application_Model_Referencia extends App_Db_Table_Abstract
{
    protected $_name = "referencia";
    /**
     * Lista
     * @param  int $idPostulante repesenta el id de postulante
     * @return array
     */
    public function getReferencias($idPostulante)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('r' => $this->_name),
                array('id_referencia'=>'r.id',
                'listaexperiencia' => 'id_experiencia',
                'nombre' => 'r.nombre',
                'cargo' => 'r.cargo',
                'telefono' => 'r.telefono',
                'telefono2' => 'r.telefono2',
                'email'=>'r.email')
            )
            ->joinInner(array('e'=>'experiencia'), 'e.id = r.id_experiencia')
            ->where('e.id_postulante = ? ', (int)$idPostulante)
            ->order('r.id DESC');
        return ($this->getAdapter()->fetchAll($sql));
    }
       /**
     * Lista todas las referencias de un postulante
     * @param  int $idPostulante repesenta el id de postulante
     * @return array
     */
    public function getReferenciasPostulante($idPostulante)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('r' => $this->_name),
                array('id_referencia'=>'r.id',
                'id_experiencia' => 'id_experiencia',
                'nombre' => 'r.nombre',
                'cargo' => 'r.cargo',
                'telefono' => 'r.telefono',
                'telefono2' => 'r.telefono2',
                'email'=>'r.email')
            )
            ->joinInner(array('e'=>'experiencia'),
                    'e.id = r.id_experiencia', 
                    array(
                        'otro_puesto'=>'e.otro_puesto',
                        'empresa'=>'e.otra_empresa'
                        ))
            ->joinInner(array('p'=>'puesto'), 
                    'e.id_puesto = p.id', 
                    array(
                        'id_puesto'=>'p.id', 
                        'puesto'=>'p.nombre',))
            ->where('e.id_postulante = ? ', (int)$idPostulante)
            ->order('r.id DESC');
        return ($this->getAdapter()->fetchAll($sql));
    }
     /**
     * Items de referencias para formulario
     * @param  int $id repesenta el id de la referencia
     * @return array
     */
    public function getFormReferencia($id)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('r' => $this->_name),
                array('hidReference'=>'r.id',
                'selCareReference' => 'id_experiencia',
                'txtNameReference' => 'r.nombre',
                'txtPositionReference' => 'r.cargo',
                'txtTelephoneReferenceOne' => 'r.telefono',
                'txtTelephoneReferenceTwo' => 'r.telefono2',
                'txtTelephoneReferenceEmail'=>'r.email')
            )
            ->joinInner(array('e'=>'experiencia'),
                    'e.id = r.id_experiencia',array())
            ->where('r.id = ? ', (int)$id);
        $rs=$this->getAdapter()->fetchAll($sql);
        return $rs[0] ;
    }
    /**
     * Actualizar referencias de una experiencia
     * @param array $param listado de parametros de referencia
     * @return true
     */
    public function updateReferencia($param) 
    {       
        $where = $this->getAdapter()->quoteInto('id IN (?)', (int)$param['hidReference']);
        $data= array(
            'id_experiencia'=>(int)$param['selCareReference'],
            'nombre'=>$param['txtNameReference'],
            'cargo'=>$param['txtPositionReference'],
            'telefono'=>$param['txtTelephoneReferenceOne'],
            'telefono2'=>$param['txtTelephoneReferenceTwo'],
            'email'=>$param['txtTelephoneReferenceEmail']
        );
        
        $this->update($data, $where);
    }
    /**
     * Registra Referencias de una experiencias
     */
    public function insetReferencia($param) 
    {
        $data= array(
            'id_experiencia'=>(int)$param['selCareReference'],
            'nombre'=>$param['txtNameReference'],
            'cargo'=>$param['txtPositionReference'],
            'telefono'=>$param['txtTelephoneReferenceOne'],
            'telefono2'=>$param['txtTelephoneReferenceTwo'],
            'email'=>$param['txtTelephoneReferenceEmail']
        );
         
        return $this->insert($data);
    }
}