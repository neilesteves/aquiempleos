<?php

class Application_Model_DominioIdioma extends App_Db_Table_Abstract
{
    protected $_name = "dominio_idioma";
    
    protected $_empresaId = 1;
    
    public static $niveles = array(
        'básico' => 'Básico',
        'intermedio' => 'Intermedio',
        'avanzado' => 'Avanzado'
    );
    
    public function __construct($config = array()) {
        //var_dump($config);die();
        if(empty($config['cron']))
            $this->_empresaId = Application_Model_Usuario::getEmpresaId();
        parent::__construct($config);
    }
    
    public function getDominioIdioma($idPostulante)
    {
        $sql = $this->getAdapter()->select()
                ->from(
                    $this->_name,
                    array('id_dominioIdioma'=>'id',
                    'id_idioma'=>'id_idioma',
                    'nivel_idioma'=>'nivel_lee')
                )
                ->where('id_postulante = ? ', $idPostulante);

        return ($this->getAdapter()->fetchAll($sql));
    }
    
    public function getIdiomas($idPostulante)
    {
        $sql = $this->getAdapter()->select()
                ->from(
                    array('didm' =>$this->_name),
                    array('id_dominioIdioma'=>'didm.id',
                    'id_idioma'=>'didm.id_idioma',
                     'nombreIdioma'=>'idm.nombre',
                    'selLevelWritten'=>'didm.nivel_escribe',
                    'selLevelOral'=>'didm.nivel_hablar'
                        )
                )->joinInner(array('idm' => 'idioma'), 'didm.id_idioma = idm.id_slug',array())
                ->group('didm.id_idioma')
                ->where('id_postulante = ? ', $idPostulante);

        return ($this->getAdapter()->fetchAll($sql));
    }
     public function verificarIdioma($idIdioma, $idPos) {
        //echo $idIdioma ;die;
        $db = $this->getAdapter();
        $sql = $db->select()->from($this->_name)
                ->where("id_idioma = ?",$idIdioma)
                ->where('id_postulante = ?', $idPos);
        return $db->fetchAll($sql);
        
    }
    
    public function get()
    {
        return self::$niveles;
    }

    
    public function getDominioIdiomaS() {
        $sql = $this->_db->select()
                ->from(array('di' => 'idioma'), array('di.id', 'di.nombre'))
                ->joinInner(array('epc' => 'empresa_idioma'), 'di.id = epc.id_idioma', array())
                ->group('di.id')
                ->order('di.nombre');
        if ($this->_empresaId === TRUE) {
            $result = $this->_db->fetchPairs($sql);
            return $result;
        }
        $sql->where('epc.id_empresa = ?', $this->_empresaId);
        $result = $this->_db->fetchPairs($sql);
        if (count($result) <= 0) {
            $sql->orWhere('epc.id_empresa = 1');
            $result = $this->_db->fetchPairs($sql);
        }
        return $result;
    }
    
    public static function getDominioIdiomaIds() {
        $obj = new Application_Model_DominioIdioma();
        return $obj->getDominioIdiomaS();
    }
    public function getIdiomaName($name) {
        $db = $this->getAdapter();
        $sql = $db->select()->from('idioma',
                array('nombre','id_slug'
                        ))
                ->where("nombre  like (?) ",'%' . $name . '%');
                
        return $db->fetchAll($sql);
    }
    public function getIdiomaId($id,$idIdioma,$idDidioma) {
        $sql = $this->getAdapter()->select()
                ->from(
                    array('didm' =>$this->_name),
                    array('id'=>'didm.id' ))
                ->where('didm.id_idioma = ? ', $idIdioma)
                ->where('didm.id_postulante = ? ', $id);
        if($idDidioma){
           $sql->where('didm.id != ?', $idDidioma) ;
        }
        $rs= $this->getAdapter()->fetchAll($sql);
        return (count($rs)>0)?true:false;
    }
    public function getIdiomasXid($id)
    {
        $sql = $this->getAdapter()->select()
                ->from(
                    array('didm' =>$this->_name),
                    array('id_dominioIdioma'=>'didm.id',
                    'id_idioma'=>'didm.id_idioma',
                    'nombreIdioma'=>'idm.nombre',
                    'selLevelWritten'=>'didm.nivel_escribe',
                    'selLevelOral'=>'didm.nivel_hablar'
                        )
                )->joinInner(array('idm' => 'idioma'), 'didm.id_idioma = idm.id_slug',array())
                ->where('didm.id = ? ', $id);

        return ($this->getAdapter()->fetchAll($sql));
    }
    
    public function getLogPostulanteIdiomaTotal ($idPostulante )
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name,array(
                    'id',      
                ))
            ->where('id_postulante = ?', (int)$idPostulante)   
            ->limit(1) ;                   
        $res= $this->getAdapter()->fetchAll($sql);
        return (count($res)>0)?true:false;
    }
}