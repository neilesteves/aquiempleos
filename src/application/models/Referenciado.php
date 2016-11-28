<?php


class Application_Model_Referenciado extends App_Db_Table_Abstract
{

    protected $_name = "referenciado";

    const ESTADO_NO_POSTULO = 1;
    const ESTADO_POSTULO = 2;
    const TIPO_REFERIDO = 1; //no estubo registrad
    const TIPO_REFERENCIADO = 2; //estubo registrado
    const NO_NOTIFICADO = 0;
    const NOTIFICADO = 1;

    public function buscarReferenciadoXEmailYAnuncio($email, $idAnuncio)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array("p" => $this->_name),
                array(
                "id" => "p.id",
                "nombres" => "p.nombre",
                "apellidos" => "p.apellidos",
            ))
            ->where('p.email = ?', $email)
            ->where('p.id_anuncio_web = ?', $idAnuncio);
        $result = $this->getAdapter()->fetchRow($sql);
        return $result;
    }

    public function registrar($registro)
    {
        $cols = $this->_getCols();
        $registro = array_intersect_key($registro, array_flip($cols));

        $this->insert($registro);
    }

    public function obtenerPorEmailYAnuncio(
    $email, $anuncioId, $columnas = array())
    {
        $columnas = $this->setCols($columnas);

        return $this->fetchRow($this->select()
                    ->from($this->_name, $columnas)
                    ->where('email =?', $email)
                    ->where('id_anuncio_web =?', $anuncioId));
    }

    public function obtenerReferenciado(
    $email, $anuncioId, $columnas = array())
    {
        $columnas = $this->setCols($columnas);

        return $this->fetchRow($this->select()
                    ->from($this->_name, $columnas)
                    ->where('email =?', $email)
                    ->where('id_anuncio_web =?', $anuncioId)
                    ->where('tipo =?', self::TIPO_REFERENCIADO));
    }

    public function obtenerReferido(
    $email, $anuncioId, $columnas = array())
    {
        $columnas = $this->setCols($columnas);

        return $this->fetchRow($this->select()
                    ->from($this->_name, $columnas)
                    ->where('email =?', $email)
                    ->where('id_anuncio_web =?', $anuncioId)
                    ->where('tipo =?', self::TIPO_REFERIDO));
    }

    public function obtenerReferidos(
    $anuncioId, $columnas = array())
    {
        $columnas = $this->setCols($columnas);

        return $this->fetchAll($this->select()
                    ->from($this->_name, $columnas)
                    ->where('id_anuncio_web =?', $anuncioId)
                    ->where('tipo =?', self::TIPO_REFERIDO));
    }

    public function obtenerReferenciados(
    $anuncioId, $columnas = array())
    {
        $columnas = $this->setCols($columnas);

        return $this->fetchAll($this->select()
                    ->from($this->_name, $columnas)
                    ->where('id_anuncio_web =?', $anuncioId)
                    ->where('tipo =?', self::TIPO_REFERENCIADO));
    }

    public function postulo($email, $anuncioId)
    {
        $datos = array();
        $datos['estado'] = self::ESTADO_POSTULO;

        $where = array();
        $where[] = $this->_db->quoteInto('email = ?', $email);
        $where[] = $this->_db->quoteInto('id_anuncio_web = ?', $anuncioId);

        $this->update($datos, $where);
    }

    public function listar($anuncio_id)
    {
        try {
            $whereEstudio = $this->getAdapter()->quoteInto(
                'e.principal >? OR e.principal IS NULL', 0);

            $wherePostulante = $this->getAdapter()->quoteInto(
                'u.rol  = ?', Application_Model_Usuario::ROL_POSTULANTE);

            $whereTipoReferido = $this->getAdapter()->quoteInto(
                'r.tipo  = ?', Application_Model_Referenciado::TIPO_REFERIDO);

            $whereTipoReferenciado = $this->getAdapter()->quoteInto(
                'r.tipo  = ?', Application_Model_Referenciado::TIPO_REFERENCIADO);

            $sql1 = $this->select()->from(array('r' => 'referenciado'),
                    array('r.id', 'r.email',
                    'r.sexo', 'r.nombre', 'r.apellidos', 'r.telefono', 'match', 'estado',
                    'r.tipo',
                    'edad' => new Zend_Db_Expr('""'),
                    'path_foto' => new Zend_Db_Expr('""'),
                    'celular' => new Zend_Db_Expr('""'),
                    'id_postulante' => new Zend_Db_Expr('""'),
                    'slug' => new Zend_Db_Expr('""'),
                    'ubigeo_nombre' => new Zend_Db_Expr('""'),
                    'id_carrera' => new Zend_Db_Expr('""'),
                    'carrera_nombre' => new Zend_Db_Expr('""'),
                    'id_nivel_estudio' => new Zend_Db_Expr('""'),
                    'principal' => new Zend_Db_Expr('""'),
                    'nivel_nombre' => new Zend_Db_Expr('""')
                    )
                )
                ->where($whereTipoReferido)
                ->where('r.id_anuncio_web =?', $anuncio_id);

            $sql2 = $this->getAdapter()->select()
                ->from(array('r' => 'referenciado'),
                    array('r.id',
                    'r.email',
                    'r.sexo', 'r.nombre', 'r.apellidos',
                    'r.telefono', 'match', 'estado',
                    'r.tipo')
                )
                ->joinLeft(array('u' => 'usuario'), 'r.email = u.email', array())
                ->joinLeft(array('p' => 'postulante'), 'p.id_usuario = u.id',
                    array(
                    'edad' => 'FLOOR(DATEDIFF(CURDATE(),p.fecha_nac)/365)',
                    'p.path_foto', 'p.celular', 'id_postulante' => 'p.id', 'p.slug')
                )
                ->joinLeft(array('ub' => 'ubigeo'), 'p.id_ubigeo = ub.id',
                    array('ubigeo_nombre' => 'ub.nombre'))
                ->joinLeft(array('e' => 'estudio'), 'e.id_postulante = p.id',
                    array('id_carrera', 'carrera_nombre' => 'otro_carrera', 'e.id_nivel_estudio',
                    'e.principal'))
                ->joinLeft(array('ne' => 'nivel_estudio'),
                    'e.id_nivel_estudio = ne.id',
                    array('nivel_nombre' => 'ne.nombre'))
                ->where($whereEstudio)
                ->where($wherePostulante)
                ->where($whereTipoReferenciado)
                ->where('r.id_anuncio_web =?', $anuncio_id);

            $sql = $this->getAdapter()->select()->union(array($sql1, $sql2));
            return $this->getAdapter()->fetchAll($sql);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    public function registrado($email, $anuncioId = null)
    {
        $datos = array();
        $datos['tipo'] = self::TIPO_REFERENCIADO;

        $where = array();
        $where[] = $this->_db->quoteInto('email = ?', $email);

        if (!is_null($anuncioId))
                $where[] = $this->_db->quoteInto('id_anuncio_web = ?',
                $anuncioId);

        $this->update($datos, $where);
    }

    public function obtenerNoNotificados()
    {
        return $this->getAdapter()->select()
                ->from(array('r' => $this->_name),
                    array('r.id', 'r.email', 'r.nombre',
                    'r.apellidos', 'r.tipo'))
                ->joinInner(array('a' => 'anuncio_web'),
                    'a.id = r.id_anuncio_web',
                    array('a.puesto', 'a.url_id', 'a.empresa_rs', 'a.slug'))
                ->where('r.notificado =?', self::NO_NOTIFICADO);
    }

    public function notificado($id)
    {
        $data = array();
        $data['notificado'] = self::NOTIFICADO;

        $where = $this->getAdapter()->quoteInto('id =?', $id);

        $this->update($data, $where);
    }
    
    public function obtenerFullPorAnuncio($anuncioId)
    {
        return $this->fetchAll($this->select()
            ->from($this->_name)
            ->where('id_anuncio_web =?', (int)$anuncioId))->toArray();
    }
}