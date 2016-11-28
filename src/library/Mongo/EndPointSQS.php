<?php
/**
 * Procesos de Despostulacion en Mongo
 *
 * @author denis
 */
class Mongo_EndPointSQS extends Mongo_Collection
{
    
    protected $_collection = 'ws_sns_endpoint';
    protected $_timeout = 5000;
    
    public function __construct()
    {
        parent::__construct($this->_timeout);
        $this->setUpCollection($this->_collection);
    }
    
        
    /*
     * Determinar si el usuario ha despostulado del proceso.
     * 
     * @param int $idPostulante     ID del Postulante
     * @param int $idAviso          ID del Aviso
     * @return boolean              true en caso que que exista, 
     *                              caso contrario false
     */
    public function getDatos($CustomUserData)
    {        
        $datos = array();
        if ($CustomUserData) {            
            try {                
                $collection = $this->getCollection();
                $res = $collection->find(array(
                    'CustomUserData' => $CustomUserData                    
                )); 
                                                                
                if ($res->count() > 0) {                    
                    $res->rewind();
                    $datos = $res->current();                    
                }                
                
            } catch (Exception $ex) {
                
            }
            
        }
        
        return $datos;
        
    }
        
    
}
