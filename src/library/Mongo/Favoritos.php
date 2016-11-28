<?php

class Mongo_Favoritos extends Mongo_Collection
{
	protected $_collection = 'apt_dev_favoritos';
    
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
                'idPostulante' => $data['idPostulante'],
                'id_aviso' => $data['id_aviso'],
                'idAviso' => $data['idAviso'],
                'estado' => 1,
				'origen' => 'web',
				'fechaRegistro' => date('Y-m-d H:i:s')
            ));
            
            return true;
            
        } catch (\Exception $ex) {
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
            throw new \Exception($ex->getMessage());
        }
    }

    public function borrarDatosAviso($datos)
    {
    	try {
            
            $collection = $this->getCollection();           

           	$collection->remove(array(
           		'idPostulante' => $datos['idPostulante'], 
           		'id_aviso' => $datos['id_aviso']
            ));
            
            return true;
            
        } catch (\Exception $ex) {            
            $this->_log->log($ex->getMessage().'. '.$ex->getTraceAsString(), Zend_Log::CRIT);
            throw new \Exception($ex->getMessage());
        }
    }
}