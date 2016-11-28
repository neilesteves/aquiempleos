<?php


class Application_Model_UsuarioEmpresa   extends App_Db_Table_Abstract {

    protected $_name = "usuario_empresa";
    
    const PRINCIPAL  = 1;
    const SECUNDARIO = 0;

    public function getAdministradores($idEmpresa)
    {
        $sql = $this->getAdapter()
            ->select()
            ->from(
                array('ue' => $this->_name),
                array('ue.id', 'ue.id_usuario', 'ue.nombres', 'ue.apellidos', 'u.email',
                'ue.creador',
                'ue.telefono', 'ue.anexo', 'ue.telefono2', 'ue.anexo2', 'u.rol')
            )->joinInner(array('u' => 'usuario'), 'u.id = ue.id_usuario',
                array())
            ->where('u.elog = ?', 0)
            ->where('ue.id_empresa = ?', $idEmpresa);
        $rs = $this->getAdapter()->fetchAll($sql);
        return $rs;
    }

    public function getUsuarioAdmCreator($idAdm)
    {
        $sql = $this->getAdapter()
            ->select()
            ->from($this->_name, array('creador', 'id_usuario'))
            ->where('id = ?', $idAdm);
        $rs = $this->getAdapter()->fetchRow($sql);
        return $rs;
    }

    public function getEditarAdministrador($idAdm)
    {
        $sql = $this->getAdapter()
            ->select()
            ->from(
                array('ue' => $this->_name),
                array('ue.id', 'ue.nombres', 'ue.apellidos', 'u.email', 'ue.creador',
                'ue.area', 'ue.puesto', 'ue.telefono', 'ue.anexo',
                'ue.telefono2', 'ue.anexo2')
            )->joinInner(array('u' => 'usuario'), 'u.id = ue.id_usuario',
                array('id_usuario' => 'u.id'))
            ->where('ue.id = ?', $idAdm);
        $rs = $this->getAdapter()->fetchRow($sql);
        return $rs;
    }
    
    public function obtenerPorId($id, $columnas = array())
    {
        $columnas = $this->setCols($columnas);
        
        return $this->fetchRow($this->select()
            ->from(array('ue' => $this->_name), $columnas)
            ->where('ue.id =?', $id));
    }
    
    public function obtenerSecundariosNoAsignados($empresaId, $anuncioId)
    {                
        $select = $this->getAdapter()->select()
            ->from('anuncio_usuario_empresa', array('id_usuario_empresa'))
            ->where('id_anuncio =?', $anuncioId);
        
        return $this->getAdapter()->fetchAll($this->getAdapter()->select()
            ->from(array('ue' => $this->_name), 
                    array('ue.id', 'ue.nombres', 'ue.apellidos'))
            ->joinInner(array('u' => 'usuario'), 'u.id = ue.id_usuario', 
                    array())
            ->where('ue.id_empresa =?', (int)$empresaId)
            ->where('u.elog =?', Application_Model_Usuario::NO_ELIMINADO)
            ->where('u.activo =?', Application_Model_Usuario::ACTIVO)
            ->where('ue.creador =?', self::SECUNDARIO)
            ->where('ue.id NOT IN ?', $select));
    }
    
    public function obtenerConUsuario($id)
    {
        return $this->getAdapter()->fetchRow($this->getAdapter()->select()
            ->from(array('ue' => $this->_name),  
                    array('ue.id', 'ue.nombres', 'ue.apellidos'))
            ->joinInner(array('u' => 'usuario'), 'u.id = ue.id_usuario', 
                    array('u.email', 'usuario_id' => 'u.id'))
            ->where('ue.id =?', $id));
    }
    
    public function setVistaNuevoProducto($id)
    {
        $res = $this->update(
                    array('vista_estadistica' => 1),
                    $this->getAdapter()->quoteInto('id = ?',$id)
                );
        return $res;
    }
    
    public function getUsuarioEmpresaByIdUsuario($idUsuario)
    {
        $sql = $this->getAdapter()
            ->select()
            ->from(
                array('ue' => $this->_name),
                array('ue.id', 'ue.nombres', 'ue.apellidos')
            )
            ->where('ue.id_usuario = ?', $idUsuario);
        $rs = $this->getAdapter()->fetchRow($sql);
        return $rs;
    }
    
    public function selectAdministradores($idEmpresa)
    {

        $sql = $this->getAdapter()
            ->select()
            ->from(
                array('ue' => $this->_name),
                array('ue.id', 'ue.id_usuario', 'ue.nombres', 'ue.apellidos', 'u.email',
                'ue.creador',
                'ue.telefono', 'ue.anexo', 'ue.telefono2', 'ue.anexo2', 'u.rol')
            )->joinInner(array('u' => 'usuario'), 'u.id = ue.id_usuario',
                array())
            ->where('u.elog = ?', 0)
            ->where('ue.id_empresa = ?', $idEmpresa);
        
        return $sql;
        
    }

    public function eliminarCuentaAdmin( $adminId, $adminIdUsuario, $nuevoAdminIdUsuario )
    {

        $AnuncioWeb = new Application_Model_AnuncioWeb();
        $Mensajes   = new Application_Model_Mensaje();
        $Notas      = new Application_Model_Nota();


        // Pasar notas al admin general(Creador) de la empresa
        $AnuncioWeb->update(
            array(
                'creado_por'=> $nuevoAdminIdUsuario,
                'modificado_por'=> $nuevoAdminIdUsuario
            ),
            $this->getAdapter()->quoteInto( 'creado_por =?', $adminIdUsuario)
        );

        // Pasar Notas al admin general(Creador) de la empresa
        $Notas->update(
            array(
                'id_usuario'=> $nuevoAdminIdUsuario
            ),
            $this->getAdapter()->quoteInto( 'id_usuario=?', $adminIdUsuario)
        );

        // Pasar Mensajes al admin general(Creador) de la empresa
        $Mensajes->update(
            array(
                'de'=> $nuevoAdminIdUsuario
            ),
            $this->getAdapter()->quoteInto( 'de=?', $adminIdUsuario)
        );

        $where = $this->getAdapter()->quoteInto('id=?', $adminId);
        $this->delete($where);

    }
    
    public function obtenerPorUsuario($idUsuario)
    {
        return $this->getAdapter()->fetchRow($this->getAdapter()->select()
            ->from(array($this->_name))            
            ->where('id_usuario =?', $idUsuario));
    }
    
}