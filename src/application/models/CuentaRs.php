<?php

class Application_Model_CuentaRs extends App_Db_Table_Abstract
{
    protected $_name = "cuenta_rs";

    /**
     * Retorna la informacion de redes sociales de un determinado usuario.
     * 
     * @param int $userId
     * @return array
     */
    public function getRedesByUser($userId)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, array('id', 'rsid', 'rs', 'screenname'))
                ->where('id_usuario = ?', $userId);
        return $db->fetchAll($sql);
    }

    /**
     * Retorna el ID del usuario segun la red social que se ingresa
     * 
     * @param int $redSocialId
     * @param string $tipoRedSocial
     */
    public function getUserIdByRedSocial($redSocialId, $tipoRedSocial)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, array('id_usuario'))
                ->where('rsid = ?', $redSocialId)
                ->where('rs = ?', $tipoRedSocial);
        return $db->fetchOne($sql);
    }

    /**
     * Elimina la cuenta de Facebook asociada a un usuario.
     * 
     * @param int $userId
     * @return bool
     */
    public function eliminarCuentaFacebookByUsuario($userId)
    {
        $db = $this->getAdapter();
        $where = array('id_usuario = ?' => $userId, 'rs = ?' => 'facebook');
        $db->delete($this->_name, $where);
        return true;
    }

    /**
     * Elimina la cuenta de Google asociada a un usuario.
     * 
     * @param int $userId
     * @return bool
     */
    public function eliminarCuentaGoogleByUsuario($userId)
    {
        $db = $this->getAdapter();
        $where = array('id_usuario = ?' => $userId, 'rs = ?' => 'google');
        $db->delete($this->_name, $where);
        return true;
    }

    /**
     * Consulta si existe alguna cuenta facebook asociado a un usuario
     * 
     * @param int $userId
     * @return bool
     */
    public function existeFacebook($userId)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, array('rs'))
                ->where('id_usuario = ?', $userId)
                ->where('rs = ?', 'facebook');
        $rs = $db->fetchRow($sql);
        if ($rs == null) {
            return false;
        }
        return true;
    }

    /**
     * Consulta si existe alguna cuenta google asociado a un usuario
     * 
     * @param int $userId
     * @return bool
     */
    public function existeGoogle($userId)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, array('rs'))
                ->where('id_usuario = ?', $userId)
                ->where('rs = ?', 'google');
        $rs = $db->fetchRow($sql);
        if ($rs == null) {
            return false;
        }
        return true;
    }
    public function getUserIdByRedSocialEmail($redSocialId, $tipoRedSocial)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, array('id_usuario'))
                ->where('screenname = ?', $redSocialId)
                ->where('rs = ?', $tipoRedSocial);
        return $db->fetchOne($sql);
    }
    
}