<?php

class Application_Model_AreaInteres extends App_Db_Table_Abstract 
{

    protected $_name = 'area_interes';

    public function __construct($config = array()) 
    {
        $this->_empresaId = Application_Model_Usuario::getEmpresaId();
        parent::__construct($config);
    }

    public function getAreasInteresByIdPostulante($idPostulante) 
    {
        
        $sql = $this->_db->select()
          ->from($this->_name)
          ->where('id_postulante = ?', $idPostulante);          
        $result = $this->_db->fetchAll($sql);
        return $result;
    }
    public function obteneraptitud($idpostulante,$idaptitud)
    {
        $sql= $this->_db->select() ->from('aptitudes_postulante')
                ->where('id_postulante =?', $idpostulante);
//                ->where('id_aptitud =?', $idaptitud);
        if($idaptitud){
            $sql->where('id_aptitud =?', $idaptitud);
        }
        
        $result = $this->_db->fetchAll($sql);
        return $result;
    
    }
      public function obtenerAptitudesPostulante($idpostulante)
    {
        $sql= $this->_db->select() ->from(array('ap' => 'aptitudes_postulante'),
                array('mostrar'=>'a.nombre','id'=>'a.id'))
                ->joinInner(array('a' => 'aptitudes'), 'a.id = ap.id_aptitud', array()) 
                ->where('ap.id_postulante =?', $idpostulante);               
        $result = $this->_db->fetchAll($sql);
        return $result;
    
    }
    public function guardarDataAptitud($data) {
      // $dAptPost=$this->obteneraptitud($data['id_postulante'],$data['id']);
        //if(!$dAptPost){
            $aptitud['id_postulante']=$data['id_postulante'];
            $aptitud['id_aptitud']=$data['id'];
            $aptitud['estado']='1'; 
            $id=  $this->guardarAptitudes($aptitud);
            return $id;
//        }else{
//           $where = $this->getAdapter()->quoteInto('id = ?',
//            $dAptPost[0]['id']);
//            $this->updaterAptitudes($data,$where);
//        }
        
    }
    public function registarNuevoAreaInteres($datos)
    {
        $dataInsert = array(
            'created_at' => date('Y-m-d H:i:s'),
            'id_postulante' => $datos['idPostulante']                    
        );

        foreach ($datos['areas_puestos'] as $areanivel) {

            if (isset($areanivel['id_area'])) {
                $dataInsert['id_area'] = $areanivel['id_area'];
            }

            if (isset($areanivel['id_nivel_puesto'])) {
                $dataInsert['id_nivel_puesto'] = $areanivel['id_nivel_puesto'];
            }

            if (isset($areanivel['id_aptitud'])) {
                $dataInsert['id_aptitud'] = $areanivel['id_aptitud'];
            }

            if (isset($areanivel['ubigeo'])) {
                $dataInsert['ubigeo'] = $areanivel['ubigeo'];
            }

            if (isset($areanivel['price1'])) {
                $dataInsert['price1'] = $areanivel['price1'];
            }
            if (isset($areanivel['price2'])) {
                $dataInsert['price2'] = $areanivel['price2'];
            }
            $this->insert($dataInsert);
        }
    }
    
    
    public function actualizarAreaInteres($datos, $resDatos)
    {
        $datosUpdate = array(
            'modified_at' => date('Y-m-d H:i:s')
        );

        foreach ($datos['areas_puestos'] as $key => $areanivel) {

            $datosUpdate['id_area'] = (isset($areanivel['id_area'])) ? $areanivel['id_area'] : $resDatos[$key]['id_area'];
            $datosUpdate['id_nivel_puesto'] = (isset($areanivel['id_nivel_puesto'])) ? $areanivel['id_nivel_puesto'] : $resDatos[$key]['id_nivel_puesto'];
            $datosUpdate['id_aptitud'] = (isset($areanivel['id_aptitud'])) ? $areanivel['id_aptitud'] : $resDatos[$key]['id_aptitud'];
            $datosUpdate['ubigeo'] = (isset($areanivel['ubigeo'])) ? $areanivel['ubigeo'] : $resDatos[$key]['ubigeo'];
            $datosUpdate['price1'] = (isset($areanivel['price1'])) ? $areanivel['price1'] : $resDatos[$key]['price1'];
            $datosUpdate['price2'] = (isset($areanivel['price2'])) ? $areanivel['price2'] : $resDatos[$key]['price2'];

            $where = $this->getAdapter()->quoteInto('id_postulante = ?', $datos['idPostulante']);
            $where .= ' AND '.$this->getAdapter()->quoteInto('id = ?', $resDatos[$key]['id']);

            $this->update($datosUpdate, $where);
        }
        
    }
    
    public function guardarUbicacionRemunaracion($datos){
        
         $datos['modified_at']  =date('Y-m-d H:i:s');       
         $where = $this->getAdapter()->quoteInto('id_postulante = ?', $datos['id_postulante']);
         $this->update($datos, $where);
    }
   
    public function guardarAptitudes($datos){
        $db = Zend_registry::get('db');
         $tbl = new Zend_Db_Table(array('db' => $db
            , 'name' => 'aptitudes_postulante'));
        return $tbl->insert($datos);
    }
     public function updaterAptitudes($data,$where){
        
        $aptitud['id_postulante']=$data['id_postulante'];
        $aptitud['id_aptitud']=$data['id'];
        $aptitud['estado']='1';
        $db = Zend_registry::get('db');
         $tbl = new Zend_Db_Table(array('db' => $db
            , 'name' => 'aptitudes_postulante'));
         $tbl->update($aptitud,$where);
    }
    
    public function deleteAptitudes($id){ 
       $dAptPost=$this->obteneraptitud($id,0);
       if($dAptPost){
        $where = $this->getAdapter()->quoteInto('id_postulante =?', (int)$id);       
        $db = Zend_registry::get('db');
        $tbl = new Zend_Db_Table(array('db' => $db
            , 'name' => 'aptitudes_postulante'));
         $tbl->delete($where);
       }
         
    }
    public function guardarAreasInteres($datos)
    {
        if (is_array($datos) && isset($datos['idPostulante'])) {
            $resDatos = $this->getAreasInteresByIdPostulante($datos['idPostulante']);            
            if (count($resDatos) > 0) {
                if (count($datos['areas_puestos']) > count($resDatos)) {
                    $where = $this->getAdapter()->quoteInto('id_postulante = ?', $datos['idPostulante']);
                    $this->delete($where);
                    $this->registarNuevoAreaInteres($datos);
                    
                } else {
                    $this->actualizarAreaInteres($datos, $resDatos);
                }
                
            } else {                
                $this->registarNuevoAreaInteres($datos);               
            }
        }
        
    }
    
    

}
