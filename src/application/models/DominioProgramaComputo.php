<?php

class Application_Model_DominioProgramaComputo extends App_Db_Table_Abstract {

    protected $_name = "dominio_programa_computo";
    public static $niveles = array(
        'basico' => 'Básico',
        'intermedio' => 'Intermedio',
        'avanzado' => 'Avanzado'
    );
    protected $_empresaId = 1;

    public function __construct($config = array()) {
        if(empty($config['cron']))
            $this->_empresaId = Application_Model_Usuario::getEmpresaId();
        parent::__construct($config);
    }

    public function getDominioProgramaComputo($idPostulante) {
        $sql = $this->_db->select()
                ->from(array('pc' => 'programa_computo'), array(
                    'id_dominioComputo' => 'dpc.id',
                    'id_programa_computo' => 'pc.id',
                    'pc.nombre',
                    'dpc.nivel'
                ))
                ->joinInner(array('dpc' => 'dominio_programa_computo'), 'pc.id = dpc.id_programa_computo', array())
                ->where('dpc.id_postulante = ?', $idPostulante)
                ->group('dpc.id')
                ->order('dpc.id');
       // echo $sql;exit;
        return $this->_db->fetchAll($sql);
    }
    public function getProgramaComputo($id) {
        $sql = $this->_db->select()
                ->from(array('pc' => 'programa_computo'), array(
                    'id_dominioComputo' => 'dpc.id',
                    'id_programa_computo' => 'pc.id',
                    'pc.nombre',
                    'nivel'=>'dpc.nivel'
                ))
                ->joinInner(array('dpc' => 'dominio_programa_computo'), 'pc.id = dpc.id_programa_computo', array())
                ->where('dpc.id = ?', $id);               
   
        return $this->_db->fetchAll($sql);
    }

    public function getPrograma($id) {
        $sql = $this->_db->select()
            ->from(array('pc' => 'programa_computo'), array(
                'id_dominioComputo' => 'dpc.id',
                'id_programa_computo' => 'pc.id',
                'pc.nombre',
                'nivel'=>'dpc.nivel'
            ))
            ->joinInner(array('dpc' => 'dominio_programa_computo'), 'pc.id = dpc.id_programa_computo', array())
            ->where('pc.id = ?', $id);

        return $this->_db->fetchAll($sql);
    }
    
    
    public function getProgramaExisteRepetido($idPostulante,$programa,$dominioProgramaComputo) 
    {    
        $sql = $this->_db->select()
                ->from(array('pc' => 'programa_computo'), array(
                    'id_dominioComputo' => 'dpc.id',
                    'id_programa_computo' => 'pc.id',
                    'pc.nombre',
                    'dpc.nivel'
                ))
                ->joinInner(array('dpc' => 'dominio_programa_computo'), 'pc.id = dpc.id_programa_computo', array())
                ->where('dpc.id_postulante = ?', $idPostulante)
                ->where('dpc.id_programa_computo = ?', $programa);
               
        if ($dominioProgramaComputo) {
            $sql->where('dpc.id != ?', $dominioProgramaComputo);
        }
        
        $rs=$this->_db->fetchAll($sql);      
        return (count($rs)>0) ? true : false;
    }
    
    
    public function getDominioProgramaComputo_old($idPostulante) {
        $sql = $this->getAdapter()->select()
                ->from(
                        $this->_name, array('id_dominioComputo' => 'id',
                    'id_programa_computo' => 'id_programa_computo',
                    'nivel' => 'nivel')
                )
                ->where('id_postulante = ? ', $idPostulante);

        return ($this->getAdapter()->fetchAll($sql));
    }

    public function get() {
        return self::$niveles;
    }
    public function getLogPostulanteProgramaTotal ($idPostulante )
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name,array(
                'id'
               //     'num' => new Zend_Db_Expr('count(1)'),      
                ))
            ->where('id_postulante = ?',(int) $idPostulante)
            ->limit(1);                   
        $res= $this->getAdapter()->fetchAll($sql);
        return (count($res)>0)?true:false;
    }
}
