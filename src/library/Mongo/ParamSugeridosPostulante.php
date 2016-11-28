<?php

class Mongo_ParamSugeridosPostulante extends Mongo_Collection
{    
    protected $_collection = 'postulante_intereses';
    
    public function __construct()
    {
        parent::__construct();
        $this->setUpCollection($this->_collection);
    }

    
    public function getDatos($idPostulante)
    {
        if ($idPostulante) {
            $collection = $this->getCollection();
            $result = $collection->findOne(array(
                'idPostulante' => $idPostulante
            ));            
            return (array)$result;
        }        
    }
    
    
    public function borrarDatos($idPostulante)
    {
        try {                                
            if ($idPostulante) {
                $collection = $this->getCollection();
                $result = $collection->remove(array(
                    'idPostulante' => $idPostulante
                ));
                return $result;
            }
            
        } catch (Exception $ex) {            
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
            throw new Exception($ex->getMessage());
        }
    }
    
    public function guardarDatos($insert) 
    {
        try {
            
            $collection = $this->getCollection();
            $existeDatos = $this->getDatos($insert['idPostulante']);             
            
            if ($existeDatos) {

                $datos = array(
                        'area_nivel' => $insert['data'],
                        'create_at' => date('Y-m-d H:i:s')
                    );

                $collection->update(
                    array('idPostulante' => $insert['idPostulante']), 
                    array('$set' => $datos)
                    );
            } else { 
                $collection->insert(array(
                    'idPostulante' => $insert['idPostulante'],
                    'area_nivel' => $insert['data'],
                    'create_at' => date('Y-m-d H:i:s')                    
                ));                
            }
            
            return true;
            
        } catch (\Exception $ex) {            
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
            throw new \Exception($ex->getMessage());
        }
    }

    public function updateDatos($param) 
    {
        try {

            $datos = array(
                'aptitudes' => $param['aptitudes'],                
                'create_at' => date('Y-m-d H:i:s'),
                'price1' => $param['price1'],
                'price2' => $param['price2'],
                'ubigeo' => $param['ubigeo'],
                'price1_punto' => $param['price1_punto'],
                'price2_punto' => $param['price2_punto']
            );

            $collection = $this->getCollection();
            $res = $collection->update(
                array('idPostulante' => $param['idPostulante']), 
                array('$set' => $datos)
            );
            
        } catch (Exception $exc) {
            $this->_log->log($exc->getMessage().'. '.$exc->getTraceAsString(), Zend_Log::CRIT);
            throw new \Exception($exc->getMessage());
        }
            
    }
}