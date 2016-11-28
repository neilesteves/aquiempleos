<?php

class Mongo_ImportadosLinkedIn extends Mongo_Collection
{    
    protected $_collection = 'importados_linkedin';
    
    public function __construct()
    {
        parent::__construct();
        $this->setUpCollection($this->_collection);
    }

    
    public function isImported($seccion, $idPostulante)
    {
        if ($idPostulante) {
            try {
                $result = $this->getDatos($idPostulante);
                if ($result->count() > 0) {
                    $result->rewind();
                    $arr = $result->current();                    
                    return ($arr[$seccion] === 1);
                }
                return false;
            } catch (Exception $ex) {
                $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
                return false;
            }
        }
        return false;
    }
    
    public function getDatos($idPostulante)
    {
        if ($idPostulante) {
            $collection = $this->getCollection();
            $result = $collection->find(array(
                'idPostulante' => $idPostulante
            ));
            return $result;
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
    
    public function guardarDatos($seccion, $idPostulante, $isNuevo = 1) 
    {        
        try {
            
            $collection = $this->getCollection();
            $datosGuardados = $this->getDatos($idPostulante);             
            $datosDefault = array(
                'estudio' => 0,
                'experiencia' => 0,
                'idioma' => 0,                
                'datos-personales' => 0
            );
            
            if ($datosGuardados->count() > 0) {
                $datosGuardados->rewind();
                $datos = $datosGuardados->current();
                $datosUpdate = $datos;
                $datosUpdate[$seccion] = $isNuevo;
                $datosUpdate['modified_at'] = date('Y-m-d H:i:s');
                unset($datosUpdate['_id']);
                $collection->update(array(
                    '_id' => $datos['_id']
                ), $datosUpdate);
            } else {
                $datosDefault[$seccion] = $isNuevo;
                $collection->insert(array(
                    'create_at' => date('Y-m-d H:i:s'),
                    'idPostulante' => $idPostulante,
                    'estudio' => $datosDefault['estudio'],
                    'experiencia' => $datosDefault['experiencia'],
                    'idioma' => $datosDefault['idioma'],
                    'datos-personales' => $datosDefault['datos-personales']                    
                ));
            }
            
            return true;
            
        } catch (\Exception $ex) {            
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
            throw new \Exception($ex->getMessage());
        }
    }
   
    
}
