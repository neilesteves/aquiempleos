<?php

class Application_Model_Mensaje extends App_Db_Table_Abstract
{

    protected $_name = "mensaje";
    const ESTADO_INVITACION = 'invitación';
    const ESTADO_MENSAJE = 'mensaje';
    const ESTADO_MODERACION = 'moderación';
    const ESTADO_PREGUNTA = 'pregunta';


    /**
     * Retorna los Mensajes de una postulación
     * @param int $idPostulacion
     * @return array fetchAll
     */
    public function getMensajesPostulacion($idUsuario,$idPostulacion)
    {
        $sql = $this->_db->select()
                ->from(
                    array('preg' => $this->_name),
                    array('id' => 'preg.id',
                            'leido' => 'preg.leido',
                            'respondido' => 'preg.respondido',
                            'cuerpo' => 'preg.cuerpo',
                            'fh' => 'preg.fh',
                            'tipo_mensaje'=>'preg.tipo_mensaje')
                )
                ->joinleft(
                    array('rpta' => $this->_name),
                    'preg.id = rpta.padre',
                    array('respuesta' => 'rpta.cuerpo')
                )
                ->where('preg.id_postulacion = ?', $idPostulacion)
                ->where('preg.padre is null')
                ->where('preg.para = ?', $idUsuario)
                ->where('preg.notificacion = 0')
                ->order('preg.fh DESC');
        $rs = $this->_db->fetchAll($sql);

        return $rs;
    }

    public function getEstadisticasMsgPostulacion($idPostulante)
    {
        $nmensajes = 0;
        $nrespondidos = 0;
        $nleidos = 0;

        $sql = $this->_db->select()
                ->from(
                    array('p' => 'postulacion'),
                    array('nleidos' => 'sum(p.msg_leidos)',
                    'nnoleidos' => 'sum(p.msg_no_leidos)',
                    'msgporresponder' => 'sum(p.msg_por_responder)')
                )
                ->joinInner(
                    array('aw' => 'anuncio_web'),
                    'aw.id = p.id_anuncio_web',
                    array('id_anuncio' => 'aw.id')
                )
                ->where('p.id_postulante =?', $idPostulante)
                ->where('aw.online = 1');
        $nmensajes = $this->_db->fetchAll($sql);

        //echo $sql->assemble(); exit;
        return array($nmensajes[0]["nleidos"]+$nmensajes[0]["nnoleidos"],
                     $nmensajes[0]["nnoleidos"],
                     $nmensajes[0]["msgporresponder"]);
    }

    /**
     *
     * @getMensajesNotificacionNew
     */
    public function getMensajesNotificacionNew($idUsuario, $col='', $ord='')
    {
        $limit = $this->_config->dashboard->nnotificaciones;
        $col = $col == '' ? 'fh-msg' : $col;
        $ord = $ord == '' ? 'DESC' : $ord;
        $sql = $this->_db->select()
                ->from(
                    array('m' => $this->_name),
                    array('id-msg' => 'm.id',
                            'leido' => 'm.leido',
                            'cuerpo' => 'm.cuerpo',
                            'fh-msg' => 'm.fh',
                            'tipo_mensaje'=>'m.tipo_mensaje')
                )
                ->joinleft(
                    array('p' => 'postulacion'),
                    'm.id_postulacion = p.id',
                    array('postulacion'=>'p.id')
                )
                ->joinleft(
                    array('aw' => 'anuncio_web'),
                    'p.id_anuncio_web = aw.id',
                    array(
                        'empresa_rs-post' => 'aw.empresa_rs' ,
                        'puesto'=>'aw.puesto',
                        'idaviso'=>'aw.url_id',
                        'id_anuncio_web' => 'aw.id',
                        'logo'=>'aw.logo'
                    )
                )
                ->where('p.activo in (0,1)')
                ->where('m.para = ?', $idUsuario)
                ->where('m.notificacion in (0,1)')
                ->group('p.id')
                ->order(sprintf('%s %s', $col, $ord))
                ->limit($limit);
        //$rs = $this->_db->fetchAll($sql);
      //  echo $sql;exit;
        return $sql;
    }


      public function getMensajesNotificacion($idUsuario, $col='', $ord='')
    {
        $limit = $this->_config->dashboard->nnotificaciones;
        $col = $col == '' ? 'fh-msg' : $col;
        $ord = $ord == '' ? 'DESC' : $ord;
        $sql = $this->_db->select()
                ->from(
                    array('m' => $this->_name),
                    array('id-msg' => 'm.id',
                            'leido' => 'm.leido',
                            'cuerpo' => 'm.cuerpo',
                            'fh-msg' => 'm.fh',
                            'tipo_mensaje'=>'m.tipo_mensaje')
                )
                ->joinleft(
                    array('p' => 'postulacion'),
                    'm.id_postulacion = p.id',
                    array()
                )
                ->joinleft(
                    array('aw' => 'anuncio_web'),
                    'p.id_anuncio_web = aw.id',
                    array('empresa_rs-post' => 'aw.empresa_rs' ,'puesto'=>'aw.puesto')
                )
                ->where('m.para = ?', $idUsuario)
                ->where('m.notificacion = 1')
                ->order(sprintf('%s %s', $col, $ord))
                ->limit($limit);
        //$rs = $this->_db->fetchAll($sql);
        return $sql;
    }

    /**
     * Retorna un registro con los campos del mensaje
     *
     * @param int $idMensaje
     * @return array fetchRow
     */
    public function getMensaje($idMensaje)
    {
        //Obtener el mensaje
        $rowMensaje = $this->find($idMensaje)->toArray();
        return $rowMensaje[0];
    }

    /**
     * Actualiza el estado del campo leido y actualiza la tabla postulacion
     * con el total de mensajes leidos y no leidos
     *
     * @param int $idPostulacion
     * @return Sin datos de retorno
     */
    public function marcarComoLeidoMsgPostulacion($idMensaje)
    {
        $rowMensaje = $this->find($idMensaje)->toArray();
        if ($rowMensaje[0]['leido'] == 0) {
            $where = $this->_db->quoteInto('id = ?', $idMensaje);
            $okUpdateP = $this->update(array('leido' => '1'), $where);
            $this->_postulaciones = new Application_Model_Postulacion();
            $okUpdateM = $this->_postulaciones->updateMsjsLeidos(
                $rowMensaje[0]['para'],
                $rowMensaje[0]['id_postulacion']
            );
            return $okUpdateP && $okUpdateM;
        }
    }

        public function marcarComoLeidoMsgsPostulacion($idMensaje)
    {
        $rowMensaje = $this->find($idMensaje)->toArray();
        if ($rowMensaje[0]['leido'] == 0) {
            $where = $this->_db->quoteInto('id = ?', $idMensaje);
            $okUpdateP = $this->update(array('leido' => '1'), $where);
            $this->_postulaciones = new Application_Model_Postulacion();
            $okUpdateM = $this->_postulaciones->updateMsjsLeidos(
                $rowMensaje[0]['para'],
                $rowMensaje[0]['id_postulacion']
            );
            return $okUpdateP && $okUpdateM;
        }
    }


    /**
     *
     * @param type $idMensaje
     */
    public function marcarComoLeidoMsgNotificacion($idMensaje)
    {
        $rowMensaje = $this->find($idMensaje)->toArray();
        if ($rowMensaje[0]['leido'] == 0) {
            $where = $this->_db->quoteInto('id = ?', $idMensaje);
            $okUpdateM = $this->update(array('leido' => '1'), $where);
            $this->_postulante = new Application_Model_Postulante();
            $okUpdateP = $this->_postulante->updateMsjsLeidos(
                $rowMensaje[0]['para'],
                $rowMensaje[0]['id_postulacion']
            );
            return $okUpdateM && $okUpdateP;
        }
    }

    public function getPaginator($idUsuario, $col, $ord)
    {
        $limit = $this->_config->notificacionesPostulante->paginador->items;
        $colMap = array(
            'fecha' => 'fh-msg',
            'enviadopor' => 'aw.empresa_rs',
            'tipo' => 'tipo_mensaje',
        );
        $column = array_key_exists($col, $colMap) ? $colMap[$col] : 'aw.puesto' ;
        $p = Zend_Paginator::factory($this->getMensajesNotificacion($idUsuario, $column, $ord));
        return $p->setItemCountPerPage($limit);
    }

    public function getPaginatorNotificacion($idUsuario, $col, $ord)
    {
        $limit = $this->_config->notificacionesPostulante->paginador->items;
        $colMap = array(
            'fecha' => 'fh-msg',
            'enviadopor' => 'aw.empresa_rs',
            'tipo' => 'tipo_mensaje',
        );
        $column = array_key_exists($col, $colMap) ? $colMap[$col] :  'm.fh'  ;
        $p = Zend_Paginator::factory($this->getMensajesNotificacionNew($idUsuario, $column, $ord));
        return $p->setItemCountPerPage($limit);
    }
    //para tener pregunta y respuesta... asi como preguntas nomas
    public function getMensajesPregunta($idPostulacion)
    {
        $sql = $this->_db->select()
                ->from(
                    array('preg' => $this->_name),
                    array('id_mensaje' => 'preg.id',
                            'leido' => 'preg.leido',
                            'respondido' => 'preg.respondido',
                            'cuerpo' => 'preg.cuerpo',
                            'fecha' => 'preg.fh',
                            'tipo_mensaje'=>'preg.tipo_mensaje')
                )
                ->joinleft(
                    array('rpta' => $this->_name),
                    'preg.id = rpta.padre',
                    array('respuesta' => 'rpta.cuerpo',
                          'respuesta_fecha' => 'rpta.fh')
                )
                ->where('preg.id_postulacion = ?', $idPostulacion)
                ->where('preg.padre is null')
                ->where('preg.notificacion = 0')
                ->order('fecha desc');
        $rs = $this->_db->fetchAll($sql);
        return $rs;
    }

    /* Cuenta cuantos mensajes no leidos tiene la postulacion*/
    public function getNMensajesPostulacion($idPostulacion)
    {
        $sql = $this->_db->select()
                ->from(
                    array('msj' => $this->_name),
                    array('n'=>'COUNT(msj.id)')
                )
                ->where('msj.id_postulacion=?', $idPostulacion)
                ->where('msj.leido=0');
        $rs = $this->_db->fetchAll($sql);
        return $rs[0]["n"];
    }

    public function getIdMensajesRsptaXPostulacion($idPostulacion)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('m1' => 'mensaje'),
                array('id'=>'m2.id')
            )
            ->joinInner(array('m2'=>'mensaje'), 'm2.padre = m1.id', array())
            ->where('m2.leido = ?', 0)
            ->where('m1.id_postulacion = ?', $idPostulacion);

        return $this->getAdapter()->fetchAll($sql);
    }

    public function getMensajeNotifica($idMensaje)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('m' => $this->_name),
                array('id-msg' => 'm.id',
                        'leido' => 'm.leido',
                        'cuerpo' => 'm.cuerpo',
                        'fh-msg' => 'm.fh',
                        'tipo_mensaje'=>'m.tipo_mensaje')
            )
            ->joinleft(
                array('p' => 'postulacion'),
                'm.id_postulacion = p.id',
                array()
            )
            ->joinleft(
                array('aw' => 'anuncio_web'),
                'p.id_anuncio_web = aw.id',
                array('empresa_rs-post' => 'aw.empresa_rs')
            )
            ->where('m.id = ?', $idMensaje)
            ->where('m.notificacion = 1');

        $rs = $db->fetchRow($sql);
        return $rs;
    }
    public function getNotificacionesPostulacion($idPostulacion)
    {
        $sql = $this->_db->select()
                ->from(
                    array('preg' => $this->_name),
                    array('id' => 'preg.id',
                            'leido' => 'preg.leido',
                            'respondido' => 'preg.respondido',
                            'cuerpo' =>  new Zend_Db_Expr("GROUP_CONCAT( `preg`.`cuerpo`SEPARATOR '<br/>')"),
                            'fh' => 'preg.fh',
                            'date' =>  new Zend_Db_Expr('DATE(preg.fh)'),
                            'tipo_mensaje'=>'preg.tipo_mensaje')
                )
                ->where('preg.id_postulacion = ?', $idPostulacion)
               ->group('preg.fh')
               ->group('preg.id')
                ->order('preg.fh ASC');
        $rs = $this->_db->fetchAll($sql);
        return $rs;
    }



    public function getMensajesByUsuarioPostulacion($idUsuario,$idPostulacion, $group = false)
    {
        $sql = $this->_db->select()
                ->from(
                    array('preg' => $this->_name),
                    array(
                        'id' => 'preg.id',
                        'fh' => 'preg.fh',
                        'tipo_mensaje'=>'preg.tipo_mensaje'
                    )
                )
                ->joinleft(
                    array('rpta' => $this->_name),
                    'preg.id = rpta.padre',
                    array('respuesta' => 'rpta.cuerpo')
                )
                ->where('preg.id_postulacion = ?', $idPostulacion)
                ->where('preg.padre is null')
                ->where('preg.para = ?', $idUsuario)
                ->where('preg.notificacion = 0')
                ->order('preg.fh ASC');

        if ($group) {
            $sql->group("DATE_FORMAT(preg.fh,'%Y-%m-%d')");
        }

        $sql->limit(6);

        $rs = $this->_db->fetchAll($sql);
        return $rs;
    }


}

