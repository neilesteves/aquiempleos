<?php

class Mongo_AvisosSugeridosEliminados extends Mongo_Collection
{
	protected $_collection = 'apt_dev_postulantes_sugeridos_eliminados';
    
    public function __construct()
    {
        parent::__construct();
        $this->setUpCollection($this->_collection);
    }       
    
    public function getDatos($idPostulante)
    {
        if ($idPostulante) {
            $collection = $this->getCollection();
            $result = $collection->find(array(
                'idPostulante' => $idPostulante,
                'estado' => 1
            ));
            return $result;
        }
    }

    public function guardarDatos($data) 
    {
        try {
            
            $collection = $this->getCollection();                        
                    
            $collection->insert(array(
                '_id' => $data['_id'],
                'idAnuncioWeb' => $data['idAnuncioWeb'],
                'idPostulante' => $data['idPostulante'],
                'estado' => 1,
                'create_at' => date('Y-m-d H:i:s')                    
            ));
            
            return true;
            
        } catch (\Exception $ex) {            
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
            throw new \Exception($ex->getMessage());
        }
    }

    public function borrarDatosAviso($param)
    {
    	$rest = array();

        try {
        	   
            $dataEliminado = $this->getDatosPostulante($param);
            if($dataEliminado){

            	$collection = $this->getCollection();
	            
	           	$rest = $collection->remove(array(
	           		'_id' => $dataEliminado['_id']
	            ));

	            return $rest;
            }
        } catch (Exception $ex) {   
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
            throw new \Exception($ex->getMessage());
        }
    }

    public function getDatosPostulante($param)
    { 
        $response = array();        
           
        try {         
            if ($param) {
            	$collection = $this->getCollection();
	            $response = $collection->findOne(array(
	                'idPostulante' => $param['idPostulante'],
	                'estado' => 1,
	                'idAnuncioWeb' => $param['id_aviso']
	            ));
            }
          
        } catch (Exception $ex) { 
           $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString, Zend_Log::CRIT);
           return $response;
        }

        return $response;        
    }
}