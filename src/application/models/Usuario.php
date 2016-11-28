<?php

class Application_Model_Usuario extends App_Db_Table_Abstract
{

    const ROL_POSTULANTE = 'postulante';
    const ROL_EMPRESA = 'empresa';
    const ROL_EMPRESA_ADMIN = 'empresa-admin'; // creador
    const ROL_EMPRESA_USUARIO = 'empresa-usuario'; // no creador
    const ROL_ADMIN = 'admin';
    const ROL_ADMIN_MASTER = 'admin-master'; //prueba
    const ROL_ADMIN_CALLCENTER = 'admin-callcenter';
    const MSG_USUARIO_BLOQUEDO = 'Su cuenta ha sido bloqueada, comuníquese con el Administrador';
    const ELIMINADO = 1;
    const NO_ELIMINADO = 0;
    const ACTIVO = 1;
    const INACTIVO = 0;

    protected $_name = "usuario";

 

    public function getIdByEmailRol($email, $rol)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, array(
                    'id',
                    'rol',
                    'token_activacion',
                    'activo'))
                ->where('email = ?', $email);

        if ($rol == Application_Form_Login::ROL_POSTULANTE) {
            $sql = $sql->where('rol = ?', Application_Form_Login::ROL_POSTULANTE);
        }

        if ($rol == Application_Form_Login::ROL_EMPRESA) {
            $sql = $sql->where(
                    'rol in (?) ', array(
                Application_Form_Login::ROL_EMPRESA_ADMIN,
                Application_Form_Login::ROL_EMPRESA_USUARIO
                    )
            );
        }

        if ($rol == Application_Form_Login::ROL_ADMIN) {
            $sql = $sql->where(
                    'rol in (?)', array(
                Application_Form_Login::ROL_ADMIN_MASTER,
                Application_Form_Login::ROL_ADMIN_CALLCENTER,
                Application_Form_Login::ROL_ADMIN_DIGITADOR,
                Application_Form_Login::ROL_ADMIN_MODERADOR,
                Application_Form_Login::ROL_ADMIN_SOPORTE
                    )
            );
        }

        //echo $sql->assemble();
        return $db->fetchRow($sql);
    }

    public function getUsuarioMail($id)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, 'email')
                ->where('id = ?', $id);
        return $db->fetchOne($sql);
    }

    public function getUsuarioId($id)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, '*')
                ->where('id = ?', $id);
        $rs = $db->fetchAll($sql, array(), Zend_Db::FETCH_OBJ); // Row($sql);
        return $rs[0];
    }

    public static function validacionEmail($value)
    {
        $options = func_get_args();
        $idUsuario = $options[2];
        $rol = $options[3];
        /* if ($rol == 'empresa') {
          $rol = 'empresa-admin';
          } */
       
        $o = new Application_Model_Usuario();
        $sql = $o->select()
                ->from('usuario', 'id')
                ->where('email = ?', $value);
        if ($rol == 'empresa' ||
                $rol == Application_Form_Login::ROL_EMPRESA_ADMIN ||
                $rol == Application_Form_Login::ROL_EMPRESA_USUARIO) {
            $sql = $sql->where(
                    'rol IN (?)', array(
                Application_Form_Login::ROL_EMPRESA_ADMIN,
                Application_Form_Login::ROL_EMPRESA_USUARIO
                    )
            );
        } else if ($rol == 'postulante') {
            $sql = $sql->where('rol = ?', Application_Form_Login::ROL_POSTULANTE);
        } else if ($rol == 'admin') {
            $sql = $sql->where(
                    'rol in (?)', array(
                Application_Form_Login::ROL_ADMIN_MASTER,
                Application_Form_Login::ROL_ADMIN_CALLCENTER,
                Application_Form_Login::ROL_ADMIN_DIGITADOR,
                Application_Form_Login::ROL_ADMIN_MODERADOR,
                Application_Form_Login::ROL_ADMIN_SOPORTE
                    )
            );
        }


        if ($idUsuario) {
            $sql = $sql->where('id != ?', $idUsuario);
        }
        $sql = $sql->limit('1');
        $r = $o->getAdapter()->fetchOne($sql);
        return !(bool) $r;
    }

    public function validacionNDoc($value)
    {
        $options = func_get_args();
        $idUsuario = $options[2];
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from('postulante', 'id')
                ->where('num_doc = ?', $value)
                ->where('tipo_doc = ?', 'dni');
        if ($idUsuario) {
            $sql = $sql->where('id != ?', $idUsuario);
        }
        $sql = $sql->limit('1');
        $r = $db->fetchOne($sql);
        return !(bool) $r;
    }

    public static function validacionPswd($value)
    {
        $options = func_get_args();
        $login = $options[2];
        $idUsuario = $options[3];
        $rawPassword = $value;
        $encPassword = self::valuePswd($login, $idUsuario);

        $valor = App_Auth_Adapter_AptitusDbTable::checkPassword($rawPassword, $encPassword);

        return $valor;
    }

    public static function valuePswd($email, $idUsuario)
    {
        $u = new Application_Model_Usuario();
        $sql = $u->select()
                ->from('usuario', array(
                    'pswd' => 'pswd'))
                ->where('id = ?', $idUsuario)
                ->where('email = ?', $email)
                ->limit('1');

        return $u->getAdapter()->fetchOne($sql);
    }

    public static function auth($login, $pswd, $type, $writeStorage = true)
    {
        $adapter = Zend_Db_Table::getDefaultAdapter();
        $authAdapter = new App_Auth_Adapter_AptitusDbTable($adapter);
        $authAdapter->setIdentity($login);
        $authAdapter->setCredential($pswd);
        $authAdapter->setRol($type);
        $auth = Zend_Auth::getInstance();
        $auth->setStorage(new Zend_Auth_Storage_Session());
        $authResult = $auth->authenticate($authAdapter);
        $related = null;

        $isValid = $authResult->isValid();
        if ($isValid && $writeStorage) {
            if ($type == Application_Form_Login::ROL_POSTULANTE ||
                    $type == Application_Form_Login::ROL_EMPRESA_ADMIN ||
                    $type == Application_Form_Login::ROL_EMPRESA_USUARIO ||
                    $type == Application_Form_Login::ROL_EMPRESA ||
                    $type == Application_Form_Login::ROL_ADMIN) {
                if ($type == Application_Form_Login::ROL_EMPRESA_ADMIN ||
                        $type == Application_Form_Login::ROL_EMPRESA_USUARIO ||
                        $type == Application_Form_Login::ROL_EMPRESA) {
                    $ue = new Application_Model_UsuarioEmpresa();
                    $class = "Application_Model_Empresa";
                    $model = new $class();
                    $type = Application_Form_Login::ROL_EMPRESA;
                    $usuario = $authAdapter->getResultRowObject(null, 'pswd');
                    $db = Zend_Db_Table::getDefaultAdapter();
                    $usuarioEmpresa = $ue->fetchRow(
                            $db->quoteInto(
                                    'id_usuario = ?', $usuario->id
                            )
                    );
                    if (isset($usuarioEmpresa) && isset($usuarioEmpresa->id_empresa)) {
                        $empresa = $model->getEmpresaMembresia(
                                // @codingStandardsIgnoreStart
                                $usuarioEmpresa->id_empresa
                                // @codingStandardsIgnoreEnd
                        );

                        $beneficios = new stdClass();
                        $valores = new stdClass();
                        if (!empty($empresa['em_id']) && !empty($empresa['membresia_info'])) {
                            foreach ($empresa['membresia_info']['beneficios'] as $beneficio) {
                                $beneficios->{$beneficio['med_codigo']} = 1;
                                $valores->{$beneficio['med_codigo']} = $beneficio['med_valor'];
                            }
                        }
                        $empresa['membresia_info']['beneficios'] = $beneficios;

                        if (isset($beneficios->prioridad)) {
                            $empresa["prioridad"] = $valores->prioridad;
                        } else {
                            $empresa["prioridad"] = 4;
                        }
                    }
                    $related = $empresa;
                    $helper = new App_Controller_Action_Helper_LogActualizacionBI();
                    $helper->logActualizacionEmpresaLogeo($usuarioEmpresa->toArray());
                } elseif ($type == Application_Form_Login::ROL_ADMIN) {

                    $ue = new Application_Model_Usuario();
                    $type = Application_Form_Login::ROL_ADMIN;
                    $usuario = $authAdapter->getResultRowObject(null, 'pswd');
                    $class = "Application_Model_Usuario";
                    $model = new $class();
                    $db = Zend_Db_Table::getDefaultAdapter();
                    $usuarioAdministrador = $ue->fetchRow(
                            $db->quoteInto(
                                    'id = ?', $usuario->id
                            )
                    );
                } else {
                    $class = "Application_Model_" . ucfirst($type);
                    $model = new $class();
                    $usuario = $authAdapter->getResultRowObject(null, 'pswd');
                    $related = $model->fetchRow('id_usuario = ' . $usuario->id)->toArray();
                }
            }

            $authStorage = $auth->getStorage();

            if ($type == Application_Form_Login::ROL_EMPRESA_ADMIN ||
                    $type == Application_Form_Login::ROL_EMPRESA_USUARIO ||
                    $type == Application_Form_Login::ROL_EMPRESA) {
                $authStorage->write(
                        array(
                            'usuario' => $usuario,
                            $type => $related,
                            'usuario-empresa' => $usuarioEmpresa->toArray()
                        )
                );
            } else {
                if ($type == Application_Form_Login::ROL_ADMIN) {
                    $authStorage->write(
                            array(
                                'usuario' => $usuario
                            )
                    );
                } else {

                    $authStorage->write(
                            array(
                                'usuario' => $usuario,
                                $type => $related
                            )
                    );
                }
            }
        }



        return $isValid;
    }

    /**
     * Funcion que agrega un token a la cuenta de usuario, para que pueda
     * recuperar su clave
     * 
     * @param string $emailUser
     * @param int $lifetime
     * @return string
     */
    public static function generarToken($idUser, $lifetime)
    {
        $u = new Application_Model_Usuario();
        $sql = $u->select()
                ->from('usuario', 'id')
                ->where('id = ?', $idUser);
        $sql = $sql->limit('1');
        $userId = $u->getAdapter()->fetchOne($sql);
        $token = sha1(uniqid(rand(), 1));
        if ($userId == null) {
            return false;
        }
        $u->update(
                array(
            'token_activacion' => $token,
            'token_expiracion' => date('Y-m-d H:i:s', time() + $lifetime)
                ), $u->getAdapter()->quoteInto('id = ?', $idUser)
        );
        return $token;
    }

    /**
     * Valida el token de un usuario
     * 
     * @param string $token
     */
    public static function isValidToken($token = null)
    {
        if ($token == null) {
            return false;
        }
        $u = new Application_Model_Usuario();
        $sql = $u->select()
                ->from('usuario', array(
                    'id',
                    'token_expiracion'))
                ->where('token_activacion = ?', $token);
        $sql = $sql->limit('1');
        $user = $u->getAdapter()->fetchRow($sql);
        if ($user == null) {
            return false;
        }
        if (time() > strtotime($user['token_expiracion'])) {
            return false;
        }
        return $user;
    }

    /**
     * Ingresa un nuevo password al usuario
     * 
     * @param int $userId
     * @param string $newPswd
     * @return bool
     */
    public static function setNewPassword($userId, $newPswd)
    {
        $u = new Application_Model_Usuario();
        $newPswd = App_Auth_Adapter_AptitusDbTable::generatePassword($newPswd);
        $u->update(
                array(
            'pswd' => $newPswd,
            'token_expiracion' => date('Y-m-d H:i:s'),
            'confirmar' => 1), $u->getAdapter()->quoteInto('id = ?', $userId)
        );
        return true;
    }

    /**
     * Activa tu cuenta de usuario
     * 
     * @param int $userId
     * @param string $newPswd
     * @return bool
     */
    public static function setactivacion($userId)
    {
        $u = new Application_Model_Usuario();
        $dato = $u->update(
                array(
            'confirmar' => 1,
            'token_activacion' => null,
            'token_expiracion' => null), $u->getAdapter()->quoteInto('id = ?', $userId)
        );
        return $dato;
    }

    /*
     * Funcion que obtiene el usuario y el postulante con el idUsuario
     */

    public function getUsuarioPostulacion($idPostulacion)
    {
        $sql = $this->getAdapter()->select()
                ->from(
                        array(
                    'p' => 'postulacion'), array(
                    'idpostulacion' => 'p.id',
                    'idpostulante' => 'p.id_postulante',
                    'idanuncioweb' => 'p.id_anuncio_web')
                )
                ->joinInner(
                        array(
                    "pos" => "postulante"), "pos.id = p.id_postulante", array(
                    "nombres" => "pos.nombres",
                    "apellidos" => "pos.apellidos")
                )
                ->joinInner(
                        array(
                    "u" => "usuario"), "u.id=pos.id_usuario", array(
                    "email" => "u.email")
                )
                ->joinInner(
                        array(
                    "a" => "anuncio_web"), "a.id = p.id_anuncio_web", array(
                    "puesto" => "a.puesto")
                )
                ->joinInner(
                        array(
                    "e" => "empresa"), "a.id_empresa = e.id", array(
                    "razonsocial" => "e.razon_social",
                    "nombre_comercial" => "e.nombre_comercial",
                    "empresa_email" => new
                    Zend_Db_Expr(" (SELECT `s`.`email` FROM `usuario` AS `s` WHERE `s`.id = `e`.`id_usuario`) ")
                        )
                )
                ->where("p.id=?", $idPostulacion);
        $rs = $this->getAdapter()->fetchAll($sql);
        return $rs;
    }

    public function getUsuarioPostulacionCreador($idPostulacion, $creador)
    {
        $sql = $this->getAdapter()->select()
                ->from(
                        array(
                    'p' => 'postulacion'), array(
                    'idpostulacion' => 'p.id',
                    'idpostulante' => 'p.id_postulante',
                    'idanuncioweb' => 'p.id_anuncio_web')
                )
                ->joinInner(
                        array(
                    "pos" => "postulante"), "pos.id = p.id_postulante", array(
                    "nombres" => "pos.nombres",
                    "apellidos" => "pos.apellidos")
                )
                ->joinInner(
                        array(
                    "u" => "usuario"), "u.id=pos.id_usuario", array(
                    "email" => "u.email")
                )
                ->joinInner(
                        array(
                    "a" => "anuncio_web"), "a.id = p.id_anuncio_web", array(
                    "puesto" => "a.puesto")
                )
                ->joinInner(
                        array(
                    "e" => "empresa"), "a.id_empresa = e.id", array(
                    "razonsocial" => "e.razon_social",
                    "nombre_comercial" => "e.nombre_comercial"
                        )
                )
                ->joinInner(
                        array(
                    "uaw" => "usuario"), "uaw.id=" . (int) $creador, array(
                    "empresa_email" => "uaw.email")
                )
                ->where("p.id=?", $idPostulacion);
        $rs = $this->getAdapter()->fetchAll($sql);
        return $rs;
    }

    public function getUsuarioActivo($email, $tipo)
    {
        $sql = $this->getAdapter()->select()
                ->from($this->_name, 'activo')
                ->where('email = ?', $email);
        if ($tipo == 'empresa' ||
                $tipo == Application_Form_Login::ROL_EMPRESA_ADMIN ||
                $tipo == Application_Form_Login::ROL_EMPRESA_USUARIO) {
            $sql = $sql->where(
                    'rol IN (?)', array(
                Application_Form_Login::ROL_EMPRESA_ADMIN,
                Application_Form_Login::ROL_EMPRESA_USUARIO
                    )
            );
        } else if ($tipo == 'postulante') {
            $sql = $sql->where('rol = ?', Application_Form_Login::ROL_POSTULANTE);
        } else if ($tipo == 'admin') {
            $sql = $sql->where(
                    'rol in (?)', array(
                Application_Form_Login::ROL_ADMIN,
                Application_Form_Login::ROL_ADMIN_CALLCENTER,
                Application_Form_Login::ROL_ADMIN_DIGITADOR,
                Application_Form_Login::ROL_ADMIN_MASTER,
                Application_Form_Login::ROL_ADMIN_MODERADOR,
                Application_Form_Login::ROL_ADMIN_SOPORTE
                    )
            );
        }
        return $this->getAdapter()->fetchRow($sql, array(), Zend_Db::FETCH_ASSOC);
    }

    public function getPaginadorBusquedaAdministrador($col, $ord)
    {
        $paginadoBusqueda = $this->_config->administrador->gestion->paginadoBusqueda;
        $p = Zend_Paginator::factory(
                        $this->getBusquedaAdministrador(
                                $col, $ord
                        )
        );
        return $p->setItemCountPerPage($paginadoBusqueda);
    }

    public function getBusquedaAdministrador($col = '', $ord = '')
    {
        $col = $col == '' ? 'u.fh_registro' : $col;
        $ord = $ord == '' ? 'DESC' : $ord;

        $sql = $this->getAdapter()
                ->select()
                ->from(
                        array(
                    'u' => $this->_name), array(
                    'id',
                    'email',
                    'nombre',
                    'apellido',
                    'rol',
                    'activo',
                    'fh_registro')
                )
                ->where('u.rol like "admin%"');
        $sql = $sql->order(sprintf('%s %s', $col, $ord));

        return $sql;
    }

    public function getRegistrosLogicosAct($idUsuario)
    {
        $sql = $this->getAdapter()
                ->select()
                ->from(
                        array(
                    'ela' => 'empresa_log_actualizacion'), array(
                    '*')
                )
                ->where('ela.id_usuario = ?', $idUsuario);
        $rs = $this->getAdapter()->fetchAll($sql);
        return $rs;
    }

    public function getUsuarioByIdEmpresa($idEmpresa)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'u' => $this->_name), '*')
                ->joinInner(array(
                    'e' => 'empresa'), 'e.id_usuario = u.id', array())
                ->where('e.id = ?', $idEmpresa);
        $rs = $db->fetchRow($sql);
        return $rs;
    }

    public function getEliminarCuentaAptitus($idUsuario)
    {
//        $db = $this->getAdapter();
//        $usuario_empresa = new Application_Model_UsuarioEmpresa();
//
//        // Eliminar de tabla Usuario
//        $where = $db->quoteInto('id=?', (int)$idUsuario);
//        $usuario_empresa->delete($where);
    }

    /**
     * 
     * @param int $idEmpresa
     * @return array
     */
    public function getUsuarioAdminByIdEmpresa($idEmpresa)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'u' => $this->_name), '*')
                ->joinInner(array(
                    'e' => 'empresa'), 'e.id_usuario = u.id', array())
                ->where('e.id = ?', $idEmpresa)
                ->where('u.rol = ?', self::ROL_EMPRESA_ADMIN);
        $rs = $db->fetchRow($sql);
        return $rs;
    }

    public function obtenerPorEmail($email, $columnas = array())
    {
        $columnas = $this->setCols($columnas);

        return $this->fetchRow($this->select()
                                ->from($this->_name, $columnas)
                                ->where('email =?', $email)
                                ->where('rol =?', self::ROL_POSTULANTE)
        );
    }

    public function correoUsuarioxAnuncio($idAviso, $creado)
    {

        $sql = $this->getAdapter();
        return $sql->select()->from(array(
                            'a' => 'anuncio_web'), null)
                        ->joinInner(array(
                            'e' => 'empresa'), 'e.id = a.id_empresa', null)
                        ->joinInner(array(
                            'u' => $this->_name), 'u.id = e.id_usuario', 'email')
                        ->where('a.id = ?', $idAviso)
                        ->limit(1)->query()->fetchColumn();
    }

    public function nombreEmpresaxAnuncio($idAviso)
    {

        $sql = $this->getAdapter();
        return $sql->select()->from(array(
                            'a' => 'anuncio_web'), null)
                        ->joinInner(array(
                            'e' => 'empresa'), 'a.id_empresa = e.id', 'e.razon_social')
                        ->where('a.id = ?', $idAviso)->limit(1)->query()->fetchColumn();
    }

    public static function getEmpresaId()
    {
        $storage = Zend_Auth::getInstance()->getStorage()->read();
        $empresaId = 1;
        $data = 1;
        //Zend_Debug::dump($storage);
//        if (isset($storage['empresa'])) { // Empresa
//            $empresaId = $storage['empresa']['id'];
//        }
//        if (isset($storage['group']) && $storage['group'] == 'admin') { // Empresa
//            $empresaId = TRUE;
//        }
//        return $empresaId;

        if (!empty($storage))
            $rol = $storage['usuario']->rol;
        else
            $rol = null;

        if ($rol == Application_Model_Usuario::ROL_EMPRESA_ADMIN ||
                $rol == Application_Model_Usuario::ROL_EMPRESA_USUARIO) {
            $data = $storage['empresa']['id'];
        }

        if ($rol == Application_Model_Usuario::ROL_ADMIN_MASTER ||
                $rol == Application_Model_Usuario::ROL_ADMIN_CALLCENTER)
            $data = TRUE;

        return $data;
    }

    //Obtener nombres
    public function obtenerNombre($idUsuario)
    {

        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'u' => $this->_name), array(
                    'ue.nombres'))
                ->joinInner(array(
                    'ue' => 'usuario_empresa'), 'ue.id_usuario = u.id', array())
                ->where('u.id = ?', $idUsuario);
        $rs = $db->fetchRow($sql);

        return $rs;
    }

    public function getUsuarioMaster()
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, 'email')
                ->where('rol = ?', 'admin-master');
        return $db->fetchAll($sql);
    }

    public function tieneTokenActivacion()
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, 'token_activacion')
                ->where('rol = ?', 'admin-master');
        return $db->fetchAll($sql);
    }

    public function esCuentaBloqueada($login, $rol)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, 'id')
                ->where('email = ?', $login)
                ->where('rol = ?', $rol)
                ->where('activo = 1')
                ->where('elog = 1');

        $res = $db->fetchOne($sql);

        $bloqueado = ($res) ? false : true;

        return $bloqueado;
    }

    public static function authAdmin($login, $pswd, $type)
    {
        $auth = Zend_Auth::getInstance();
        $data = $auth->getStorage()->read();
        $adapter = Zend_Db_Table::getDefaultAdapter();
        $authAdapter = new App_Auth_Adapter_AptitusDbTable($adapter);
        $authAdapter->setIdentity($login);
        $authAdapter->setCredential($pswd);
        $authAdapter->setRol($type);
        $auth->setStorage(new Zend_Auth_Storage_Session());
        $authResult = $auth->authenticate($authAdapter);
        $isValid = $authResult->isValid();
        if ($isValid) {
            $usuario = $authAdapter->getResultRowObject(null, 'pswd');
        } else {
            $usuario = $isValid;
        }
        $authStorage = $auth->getStorage();
        $authStorage->write($data);
        return $usuario;
    }

    public function navegarComoUsuario($idEmpresa)
    {
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        //$contentAuth = $authStorage->read();                


        $ue = new Application_Model_UsuarioEmpresa();
        $usuarioEmpresa = $ue->fetchRow('id_empresa = ' . $idEmpresa . ' AND creador = 1');
        if (!$usuarioEmpresa) {
            return false;
        }

        $mEmpresa = new Application_Model_Empresa();
        $empresa = $mEmpresa->getEmpresaMembresia($idEmpresa);
        $beneficios = new stdClass();
        $valores = new stdClass();
        if (!empty($empresa['em_id']) && !empty($empresa['membresia_info'])) {
            foreach ($empresa['membresia_info']['beneficios'] as $beneficio) {
                $beneficios->{$beneficio['med_codigo']} = 1;
                $valores->{$beneficio['med_codigo']} = $beneficio['med_valor'];
            }
        }
        $empresa['membresia_info']['beneficios'] = $beneficios;

        if (isset($beneficios->prioridad)) {
            $empresa["prioridad"] = $valores->prioridad;
        } else {
            $empresa["prioridad"] = 4;
        }
        $arUsuarioEmpresa = $usuarioEmpresa->toArray();

        $usuario = $this->getUsuarioId($arUsuarioEmpresa['id_usuario']);
        $contentAuth['usuario'] = $usuario;
        $contentAuth['empresa'] = $empresa;
        $contentAuth['usuario-empresa'] = $arUsuarioEmpresa;
        $authStorage->write($contentAuth);
        return true;
    }

    public function updateSesionMembresia($idEmpresa)
    {

        $modelEmpresa = new Application_Model_Empresa;
        $empresa = $modelEmpresa->getEmpresaMembresia($idEmpresa);
        $beneficios = new stdClass();
        $valores = new stdClass();
        if (!empty($empresa['em_id']) && !empty($empresa['membresia_info'])) {
            foreach ($empresa['membresia_info']['beneficios'] as $beneficio) {
                $beneficios->{$beneficio['med_codigo']} = 1;
                $valores->{$beneficio['med_codigo']} = $beneficio['med_valor'];
            }
        }
        $empresa['membresia_info']['beneficios'] = $beneficios;

        if (isset($beneficios->prioridad)) {
            $empresa["prioridad"] = $valores->prioridad;
        } else {
            $empresa["prioridad"] = 4;
        }

        return $empresa;
    }

    public static function authRS($login)
    {
        $auth = Zend_Auth::getInstance();
        $modelU = new Application_Model_Usuario();
        $model = new Application_Model_Postulante();
        $usuario = $modelU->obtenerPorEmail($login, array(
            'id',
            'email',
            'salt',
            'rol',
            'token_activacion',
            'token_expiracion',
            'activo',
            'ultimo_login',
            'fh_edicion',
            'fh_registro',
            'nombre',
            'apellido',
            'ip',
            'importado',
            'modificado_por',
            'elog'));
        $object = new stdClass();
        foreach ($usuario as $key => $value)
            $object->$key = $value;
        $related = $model->fetchRow('id_usuario = ' . $object->id)->toArray();
        $authStorage = $auth->getStorage();
        $authStorage->write(
                array(
                    'usuario' => $object,
                    'postulante' => $related
                )
        );
    }

    public function hasConfirmed($idPostulante)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, array(
                    'id'))
                ->where('id = ?  AND confirmar = 1', $idPostulante);
        $rs = $db->fetchRow($sql);
        return ($rs != false);
    }

    public function hasConfirmedIdPost($idPostulante)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array(
                    'u' => $this->_name), array(
                    'id'))
                ->joinInner(array(
                    'p' => 'postulante'), 'u.id=p.id_usuario', array())
                ->where('p.id = ?  AND u.confirmar = 1', $idPostulante);
        $rs = $db->fetchRow($sql);
        return ($rs != false);
    }

    public function hasvailBlokeo($idusuario)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, array(
                    'id'))
                ->where('id = ?  AND activo = 0', (int) $idusuario);
        $rs = $db->fetchRow($sql);
        return ($rs != false);
    }

    public function navegarComoPostulante($email, $doc_numero = null)
    {
        try {
            $auth = Zend_Auth::getInstance();
            $modelU = new Application_Model_Usuario();
            $model = new Application_Model_Postulante();
            if (!empty($doc_numero) && empty($email)) {
                $email = $model->EmailPostulantedoc($doc_numero);
            }



            $usuario = $modelU->obtenerPorEmail($email, array(
                'id',
                'email',
                'salt',
                'rol',
                'token_activacion',
                'token_expiracion',
                'activo',
                'ultimo_login',
                'fh_edicion',
                'fh_registro',
                'nombre',
                'apellido',
                'ip',
                'importado',
                'modificado_por',
                'elog'));
            $object = new stdClass();
            foreach ($usuario as $key => $value)
                $object->$key = $value;
            $related = $model->fetchRow('id_usuario = ' . $object->id)->toArray();
            $authStorage = $auth->getStorage();
            $authStorage->write(
                    array(
                        'usuario' => $object,
                        'postulante' => $related
                    )
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function existeEmailUsuarioAdmin($email)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from($this->_name, array(
                    'id'))
                ->where('email = ?', $email)
                ->where('rol <> ?', 'empresa-admin')
                ->where('rol <> ?', 'empresa-usuario');
        $rs = $db->fetchRow($sql);

        return (count($rs));
    }

    public function registerFb($facebookUser)
    {
        $date = date('Y-m-d H:i:s');
        $valuesUsuario = array();
        $valuesUsuario['email'] = $facebookUser['email'];
        $pswd = uniqid();
        $valuesUsuario['pswd'] = App_Auth_Adapter_AptitusDbTable::generatePassword(
                        $pswd
        );
        $valuesUsuario['salt'] = '';
        $valuesUsuario['rol'] = Application_Form_Login::ROL_POSTULANTE;
        $valuesUsuario['activo'] = 1;
        $valuesUsuario['ultimo_login'] = $date;
        $valuesUsuario['fh_edicion'] = $date;
        $valuesUsuario['fh_registro'] = $date;
        $valuesUsuario['ip'] = $_SERVER["REMOTE_ADDR"];
        $valuesUsuario['confirmar'] = 1;
        $valuesUsuario['modificado_por'] = 1;
        $lastId = $this->insert($valuesUsuario);
        return $lastId;
    }

    public static function validEmailRs($email, $tipo)
    {
        $rol = $tipo;
        $o = new Application_Model_Usuario();
        $sql = $o->select()
                ->from('usuario', 'id')
                ->where('email = ?', $email);
        if ($rol == 'empresa' ||
                $rol == Application_Form_Login::ROL_EMPRESA_ADMIN ||
                $rol == Application_Form_Login::ROL_EMPRESA_USUARIO) {
            $sql = $sql->where(
                    'rol IN (?)', array(
                Application_Form_Login::ROL_EMPRESA_ADMIN,
                Application_Form_Login::ROL_EMPRESA_USUARIO
                    )
            );
        } else if ($rol == 'postulante') {
            $sql = $sql->where('rol = ?', Application_Form_Login::ROL_POSTULANTE);
        } else if ($rol == 'admin') {
            $sql = $sql->where(
                    'rol in (?)', array(
                Application_Form_Login::ROL_ADMIN_MASTER,
                Application_Form_Login::ROL_ADMIN_CALLCENTER,
                Application_Form_Login::ROL_ADMIN_DIGITADOR,
                Application_Form_Login::ROL_ADMIN_MODERADOR,
                Application_Form_Login::ROL_ADMIN_SOPORTE
                    )
            );
        }
        $sql = $sql->limit('1');
        $r = $o->getAdapter()->fetchOne($sql);
        return !(bool) $r;
    }

}
