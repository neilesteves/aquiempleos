<?php

class Application_Model_Api extends App_Db_Table_Abstract
{
    protected $_name = "api";

    public function getListJobs($idEmpresa, $idpostulante)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array('awid'=>'aw.id',
                    'puesto'=>'aw.puesto',
                    'slugAviso'=>'aw.slug',
                    'fechapublicacion'=>'aw.fh_pub',
                    'tipoaviso'=>'aw.tipo',
                    'diasfp'=>'DATEDIFF(CURDATE(),aw.fh_pub)',
                    'funciones'=>'aw.funciones',
                    'responsabilidades'=>'aw.responsabilidades',
                    'mostrarempresa' => 'aw.mostrar_empresa',
                    //'logoanuncio' => 'aw.logo',
                    'empresars' => 'aw.empresa_rs',
                    'idanuncioweb' => 'aw.url_id',
                    'idproducto' => 'aw.id_producto')
            )
            ->join(
                array('e' => 'empresa'),
                'aw.id_empresa = e.id and
                e.id = '.$idEmpresa,
                array()
            )
            ->joinleft(
                array('np' => 'nivel_puesto'),
                'aw.id_nivel_puesto = np.id',
                array()
            )
            ->joinleft(
                array('a' => 'area'),
                'aw.id_area = a.id',
                array()
            )
            ->joinleft(
                array('p' => 'postulacion'),
                'p.id_anuncio_web = aw.id and
                 p.id_postulante = '.$idpostulante,
                array('idpostulante'=>  new Zend_Db_Expr('IFNULL(p.id_postulante,0)'))
            )
            ->where('aw.online = 1 ')
            ->order('fh_pub DESC');


        return $sql;
    }


    public function getJob($idEmpresa, $idpostulante, $urlId)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('aw' => 'anuncio_web'),
                array(
                    'id'                => 'aw.id',
                    'puesto'            => 'aw.puesto',
                    'funciones'         => 'aw.funciones',
                    'responsabilidades' => 'aw.responsabilidades',
                    'slug'              => 'aw.slug',
                    'idempresa'         => 'aw.id_empresa',
                    'salarioMin'        => 'aw.salario_min',
                    'salarioMax'        => 'aw.salario_max',
                    'url_id'            => 'aw.url_id',
                    'mostrarSalario'    => 'aw.mostrar_salario',
                    'online'            => 'aw.online',
                    'borrador'          => 'aw.borrador',
                    'mostrarEmpresa'    => 'aw.mostrar_empresa',
                    'nombreEmpresa'     => 'aw.empresa_rs',
                    'logoEmpresa'       => 'aw.logo',
                    'ciudad'            => 'u.display_name',
                    'areaPuesto'        => 'a.nombre',
                    'areaPuestoSlug'    => 'a.slug',
                    'nivelPuestoSlug'   => 'np.slug',
                    'redireccion'       => 'aw.redireccion'
                )
            )
            ->joinLeft(
                array('e' => 'empresa'),
                'aw.id_empresa = e.id', array()
            )
            ->joinLeft(
                array('u' => 'ubigeo'),
                'aw.id_ubigeo = u.id', array()
            )
            ->joinLeft(
                array('a' => 'area'),
                'aw.id_area = a.id', array()
            )
            ->joinLeft(
                array('np' => 'nivel_puesto'),
                'aw.id_nivel_puesto = np.id', array()
            );
            
        
        if ($idpostulante!="NULL") {
            $sql = $sql->joinleft(
                array('p' => 'postulacion'),
                'p.id_anuncio_web = aw.id',
                array('idpostulante'=>  new Zend_Db_Expr('IFNULL(p.id_postulante,0)'))
            ); 
            //idpostulante
            $where = $this->getAdapter()->quoteInto('p.id_postulante=?', $idpostulante);
            $sql = $sql->where($where);
        }

        //idempresa
        $where = $this->getAdapter()->quoteInto('aw.id_empresa=?', $idEmpresa);
        $sql = $sql->where($where);

        //anuncio web
        $where = $this->getAdapter()->quoteInto('aw.url_id=?', $urlId);
        $sql = $sql->where($where);

        return $sql;
    }

    public function getPreguntas($idEmpresa, $idAnuncio)
    {
        $adapter = $this->getAdapter();
        $sql = $adapter->select()
                ->from(
                    array('c' => 'cuestionario'),
                    array(
                        'idpregunta' => 'p.id',
                        'descripcion' => 'p.pregunta'
                    )
                )
                ->join(
                    array('p' => 'pregunta'),
                    'c.id = p.id_cuestionario',
                    array()
                )
                ->where($this->getAdapter()->quoteInto('c.id_empresa = ?', $idEmpresa))
                ->where($this->getAdapter()->quoteInto('c.id_anuncio_web = ?', $idAnuncio));
        return $sql;
    }
    
    public function checkLogin($mail, $pswd) 
    {
        $adapter = $this->getAdapter();
        $sql = $adapter->select()
                ->from(
                    array("u"=>"usuario"),
                    array(
                        "id" => "p.id",
                        "pswd" => "u.pswd"
                    )
                )
                ->join(
                    array("p"=>"postulante"),
                    "p.id_usuario = u.id",
                    array(
                        "nombres"=>"p.nombres",
                        "apellidos"=>"p.apellidos",
                        "sexo"=>"p.sexo"
                    )
                )
                ->where(
                    $adapter->quoteInto(
                        "UPPER(email)=?", 
                        new Zend_Db_Expr("UPPER('".$mail."')")
                    )
                )
                ->where("rol='postulante'");
        
        $result = $adapter->fetchAll($sql);
        if (count($result)>0) {
            if (self::checkPassword($pswd, $result[0]["pswd"])) {
                unset($result[0]["pswd"]);
                return $result;
            } else {
                return array();
            }
        } else {
            return array();
        }
    }
    
    public static function checkPassword($rawPassword, $encPassword)
    {
        $parts = explode('$', $encPassword);
        if (count($parts) != 3) {
            return false;
        }
        
        $algo = strtolower($parts[0]);
        $salt = $parts[1];
        $encPass = $parts[2];
        
        $credentialEnc = '';
        if ($algo == 'sha1') {
            $credentialEnc = sha1($salt . $rawPassword, false);
        } else {
            $credentialEnc = md5($salt . $rawPassword, false);
        }
        
        return $credentialEnc == $encPass;
    }
    
    public function listApplicantByUser($idPostulante) 
    {
        $adapter = $this->getAdapter();
        $sql = $adapter->select()
                ->from(
                    array('p' => 'postulacion'),
                    array(
                        'idanuncioweb'  =>'p.id_anuncio_web',
                        'url_id'        =>'aw.url_id',
                        'puesto'        => 'aw.puesto',
                        'empresars'     => 'aw.empresa_rs'
                    )
                )
                ->join(
                    array('aw' => 'anuncio_web'),
                    'aw.id = p.id_anuncio_web',
                    array()
                )
                ->where($this->getAdapter()->quoteInto('p.id_postulante = ?', $idPostulante));
        return $sql;
    }
    
    
    
    
    
    
    //funciones para el mantenimiento de el API en ADMIN
    public function getListApi($col="", $ord="", $idEmpresa = null) 
    {
        $col = $col==""?"a.id":$col;
        $ord = $ord==""?"ASC":$ord;
        
        $adapter = $this->getAdapter();
        $sql = $adapter->select()
            ->from(
                array("a"=>$this->_name),
                array(
                    "id"           => "a.id",
                    "forcedomain"  => "a.force_domain",
                    "domain"       => "a.domain",
                    "username"     => "a.username",
                    "mensaje"      => "a.mensaje",
                    "vigencia"     => "a.vigencia",
                    "fechaini"     => new Zend_Db_Expr("DATE_FORMAT(a.fecha_ini,'%d/%m/%Y')"),
                    "fechafin"     => new Zend_Db_Expr("DATE_FORMAT(a.fecha_fin,'%d/%m/%Y')"),
                    "estado"       => new Zend_Db_Expr("UPPER(a.estado)"),
                    "idusuario"    => "a.usuario_id",
                    "fhregistro"   => "a.fecha_registro",
                    "fhmodificacion"  => "a.fecha_modificacion",
                    "nombre_empresa" => "e.razon_comercial"
                )
            )->join(
                array('e' => 'empresa'),
                'a.usuario_id = e.id',
                array()
            );
            
        if (isset($idEmpresa)) {
            $sql = $sql->where('usuario_id = ?', $idEmpresa);
        }
        
        $sql = $sql->order("$col $ord");
        return $sql;
    }
    
    public function listarApi($col="", $ord="", $idEmpresa = null) 
    {
        $paginado = $this->_config->administrador->api->listado;
        $p = Zend_Paginator::factory(
            $this->getListApi(
                $col, $ord, $idEmpresa
            )
        );
        return $p->setItemCountPerPage($paginado);
    }
    
    public function evaluaUsuarioByEmail()
    {
        $options = func_get_args();
        $emailUsuario = $options[2];
        
        $empresaModel = new Application_Model_Empresa();
        $empresa = $empresaModel->getEmpresaByEmail($emailUsuario, '');
        $empresaId = $empresa['idempresa'];
        if ($empresaId === null) {
            return false;
        }
        $adapter = new Application_Model_Api();
        $sql = $adapter->getAdapter()->select()
                ->from(
                    array('a' => 'api'),
                    array(
                        'id'  =>'a.id',
                    )
                )
                ->where($adapter->getAdapter()->quoteInto('a.usuario_id = ?', $empresaId));
        $result = $adapter->getAdapter()->fetchAll($sql);
        if (count($result) > 0) {
            return false;
        }
        return true;
    }
    
    /**
     * Dar de baja al usuario de un API
     * 
     * @param int $idApi
     */
    public function darDeBaja($idApi)
    {
        $where = $this->getAdapter()->quoteInto('id = ?', $idApi);

        $this->update(
            array(
                'estado' => 'dadobaja', 
                'fecha_modificacion' => date('Y-m-d H:i:s')
            ), 
            $where
        );
    }
    
    /**
     * Activar usuario de un API
     * 
     * @param int $idApi
     */
    
    public function activar($idApi)
    {
        $where = $this->getAdapter()->quoteInto('id = ?', $idApi);

        $this->update(
            array(
                'estado' => 'vigente', 
                'fecha_modificacion' => date('Y-m-d H:i:s')
            ), 
            $where
        );
    }
    
    /**
     * Retorna todos los datos del API
     * 
     * @param int $idApi
     */
    public function getDatosByApi($idApi)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('a' => 'api')
            )
            ->where('a.id = ?', $idApi)
            ->join(
                array('e' => 'empresa'),
                'a.usuario_id = e.id',
                array('razon_comercial')
            )
            ->join(
                array('u' => 'usuario'),
                'e.id_usuario = u.id',
                array('email')
            );
       return $db->fetchRow($sql);
    }
    
    public function getDatosByIdEmpresa($idEmpresa)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name)
            ->where('usuario_id = ?', $idEmpresa);
        return $this->getAdapter()->fetchRow($sql);
    }
}