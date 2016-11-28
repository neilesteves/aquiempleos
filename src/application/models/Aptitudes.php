<?php

class Application_Model_Aptitudes extends App_Db_Table_Abstract
{
     protected $_name = 'aptitudes';
     private $_model = null;
    
    
    public static function getAptitusdesName($nombre) 
    {
        
        $db = new App_Db_Table_Abstract();
        $sql = $db->getAdapter()->select()
                ->from('aptitudes', array('id','mostrar'=> 'nombre'))
                ->where('nombre like (?)','%'.$nombre.'%')
                ->order('nombre');
        $rs = $db->getAdapter()->fetchAll($sql);       
        return $rs;
    }
    
    public static function getAptitusdesId($id) 
    {
        $db = new App_Db_Table_Abstract();
        $sql = $db->getAdapter()->select()
                ->from('aptitudes', array('id','mostrar'=> 'nombre'))
                ->where('id = ?',$id)
                ->order('nombre');
        $rs = $db->getAdapter()->fetchAll($sql);
        
        
        return ($rs)?$rs[0]:0;
    }
       public function getSolrAptitusdesId($id) 
    {
            $id = (int)$id;
        $adapter = $this->getAdapter();
        $sql="SELECT id,CONCAT(UPPER(LEFT(nombre,1)),SUBSTR(nombre,2))AS nombre,slug,estado,LOWER(nombre)AS nombre_busqueda FROM aptitudes WHERE estado=1 AND id=".$id;
          $stm = $adapter->query($sql);
        return $stm->fetch(Zend_Db::FETCH_ASSOC);         
;
    }
    public static function agregarAptitusdes($data) 
    {
        $modsolr= new Solr_SolrAptitud();
        $data['nombre']= ucwords(strtolower($data['nombre']));
        $id=$modsolr->obtenerRepetido($data['slug']);
        if($id){
            return $id;
        }
        
        $db = new App_Db_Table_Abstract();
        $db->getAdapter()->insert('aptitudes',$data);
        $rs = $db->getAdapter()->lastInsertId();
        $data['id']=$rs;
       
        if($modsolr->addAptitud($rs)){
        
           return $rs;
        }
         
        
    }
}