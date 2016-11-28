<?php


class Application_Model_EmpresaPostulanteBloqueado
    extends App_Db_Table_Abstract
{

    protected $_name = "empresa_postulante_bloqueado";

    /**
     * 
     * @param type $empresaId
     * @param type $postulanteId
     * @param type $columnas
     * @return type
     */
    public function listByPostulante($empresaId, $postulanteId,
        $columnas = array())
    {
        $columnas = $this->setCols($columnas);
        $sql = $this->select()
            ->from($this->_name, $columnas)
            ->where('id_empresa=?', (int) $empresaId)
            ->where("id_postulante IN (?)",$postulanteId);
        $bloqueado = $this->fetchAll($sql);

        return $bloqueado;
    }

    public function obtenerPorEmpresaYPostulante(
            $empresaId, $postulanteId, $columnas = array())
    {
        $columnas = $this->setCols($columnas);
        
        $bloqueado = $this->fetchRow($this->select()
            ->from($this->_name, $columnas)
            ->where('id_empresa=?', (int)$empresaId)
            ->where('id_postulante=?', (int)$postulanteId));
        
        return $bloqueado;
    }
    
    public function bloquear($empresa_id, $postulante_id)
    {
        $date = new Zend_Date;

        $registro = Array();
        $registro['id_empresa'] = $empresa_id;
        $registro['id_postulante'] = $postulante_id;
        $registro['fecha_bloqueo'] = $date->get('YYYY-MM-dd HH:mm:ss');

        $this->insert($registro);
    }

    public function desbloquear($empresaId, $postulanteId)
    {
        $this->delete('id_empresa = ' . (int) $empresaId .
            ' and id_postulante = ' . $postulanteId);
    }

    public function obtenerPostulantesPorEmpresa($empresaId)
    {
        $where = $this->getAdapter()->quoteInto(
                'e.principal >? OR e.principal IS NULL', 0);
        
        return $select = $this->getAdapter()->select()
                ->from(array('epb' => $this->_name),
                    array('epb.id',
                    'epb.fecha_bloqueo'))
                ->joinInner(array('p' => 'postulante'),
                    'epb.id_postulante = p.id',
                    array('p.nombres', 'p.apellidos', 'p.sexo', 'p.path_foto',
                    'p.id_usuario', 'p.celular', 'id_postulante' => 'p.id',
                    'edad' => 'FLOOR(DATEDIFF(CURDATE(),p.fecha_nac)/365)'))
                ->joinLeft(array('e' => 'estudio'), 'e.id_postulante = p.id',
                    array('id_carrera', 'otro_carrera', 'e.id_nivel_estudio',
                    'e.principal'))
                ->joinLeft(array('ne' => 'nivel_estudio'),
                    'e.id_nivel_estudio = ne.id',
                    array('nivel_nombre' => 'ne.nombre'))
                ->where('epb.id_empresa =?', (int) $empresaId)
                ->where($where)
                ->group('p.id');
    }

    public function buscar($empresaId, $criterio)
    {
        $select = $this->obtenerPostulantesPorEmpresa($empresaId);

        $select->where('concat_ws(" ", p.nombres, p.apellidos) LIKE ?',
            '%' . $criterio . '%');

        return $select;
    }

    public function ordenarBloqueados($columna, $orden, $select)
    {
        $select->order("ISNULL($columna) ASC, $columna $orden");
        return $select;
    }

}
