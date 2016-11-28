<?php

class Application_Model_AnuncioUsuarioEmpresa extends App_Db_Table_Abstract
{
    protected $_name = "anuncio_usuario_empresa";
    
    public function asignar($usuarioEmpresaId, $anuncioId)
    {
        $hoy = new Zend_Date;
        $data['id_usuario_empresa']  = $usuarioEmpresaId;
        $data['id_anuncio']          = $anuncioId;        
        $data['fecha_asignacion']    = $hoy->get('YYYY-MM-dd HH:mm:ss');
        $this->insert($data);
    }
    
    public function obtenerPorAnuncioYUsuario(
            $anuncioId, $usuarioEmpresaId, $columnas = array())
    {
        $columnas = $this->setCols($columnas);
        
        return $this->fetchRow($this->select()
            ->from($this->_name, $columnas)
            ->where('id_anuncio =?', (int)$anuncioId)
            ->where('id_usuario_empresa =?', (int)$usuarioEmpresaId));
    }   
    
    public function obtenerAnunciosPorUsuario($usuarioEmpresaId)
    {
        return $this->getAdapter()->select()
            ->from(array('aue' => $this->_name), array('aue.id', 
                'fecha_asignacion'))
            ->joinInner(array('a' => 'anuncio_web'), 'aue.id_anuncio = a.id', 
                    array('anuncio_id' => 'a.id', 'a.puesto'))
            ->where('aue.id_usuario_empresa =?', $usuarioEmpresaId)
            ->where('a.online =?', Application_Model_AnuncioWeb::ONLINE)
            ->where('a.fh_vencimiento >= CURDATE()');
    }
    
    public function quitar($usuarioEmpresaId, $anuncioId)
    {
        $where[] = $this->getAdapter()->quoteInto('id_usuario_empresa =?', 
                $usuarioEmpresaId);
        $where[] = $this->getAdapter()->quoteInto('id_anuncio =?', $anuncioId);
        
        return $this->delete($where);
    }
    
    public function quitarPorAnuncio($anuncioId)
    {
        $where = $this->getAdapter()->quoteInto('id_anuncio =?', $anuncioId);
        $this->delete($where);
    }        
    
    public function obtenerAdministrador($anuncioId)
    {
        return $this->getAdapter()->fetchRow($this->getAdapter()->select()
            ->from(array('aue' => $this->_name), array('aue.id'))
            ->joinInner(array('ue' => 'usuario_empresa'), 
                    'aue. id_usuario_empresa = ue.id', array(
                        'usuario_empresa_id' => 'ue.id','ue.nombres', 'ue.apellidos'))
            ->joinInner(array('u' => 'usuario'), 'u.id = ue.id_usuario', 
                array('u.email'))
            ->where('aue.id_anuncio =?', (int)$anuncioId));
    }
    
    public function quitarPorAdministrador($administradorId)
    {
        $where = $this->getAdapter()->quoteInto(
                'id_usuario_empresa =?', (int)$administradorId);
        $this->delete($where);
    }
}