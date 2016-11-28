<?php

class Application_Model_Pregunta extends App_Db_Table_Abstract
{
    protected $_name = "pregunta";
    
    public function getUrlById($idEstudio)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('aw' => 'anuncio_web'), array('aw.url_id'))
            ->joinInner(array('c' => 'cuestionario'), 'c.id_anuncio_web = aw.id', array())
            ->joinInner(array('p' => $this->_name), 'c.id =p.id_cuestionario', array())
            ->where('p.id = ?', $idEstudio);
        $rs = $this->getAdapter()->fetchOne($sql);
        return $rs;
    }
    public function getByAviso($idEstudio)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('aw' => 'anuncio_web'), array( 'id'=>'aw.id','url_id'=>'aw.url_id'))
            ->joinInner(array('c' => 'cuestionario'), 'c.id_anuncio_web = aw.id', array())
            ->joinInner(array('p' => $this->_name), 'c.id =p.id_cuestionario', array())
            ->where('p.id = ?', $idEstudio);
        $rs = $this->getAdapter()->fetchAll($sql);
        return $rs[0];
    }
    public function getRespuesta($idPregunta)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('r' => 'respuesta'), array('r.id'))
            //->joinInner(array('c' => 'cuestionario'), 'c.id_anuncio_web = aw.id', array())
           // ->joinInner(array('p' => $this->_name), 'c.id =p.id_cuestionario', array())
            ->where('r.id_pregunta = ?', $idpregunta)->limit(1);
        $rs = $this->getAdapter()->fetchAll($sql);
        return $rs;
    }
    public function deletePregunta($id) 
    {
      $where=array('id=?' =>(int) $id);      
      return  $this->update(array('estado'=>0),$where);
    }
    
}