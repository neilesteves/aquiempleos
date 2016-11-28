<?php


class Mongo_PorcentajePostulante extends Mongo_Collection
{
    
    protected $_collection = 'porcentaje_postulante';
    protected $_timeout = 5000;
    
    public function __construct()
    {
        parent::__construct($this->_timeout);
        $this->setUpCollection($this->_collection);
    }
    
    
    /*
     * Guarda los datos a la coleccion de mongo.
     * 
     * @param array $datos      Datos de la postulacion a guardarse.
     * @retun int               Retorna entero mayor a cero en caso de exito, 
     *                          caso contrario retorna 0.
     */
    public function savePorcentaje($datos)
    {
        $datos['modified_at'] = date('Y-m-d H:i:s');                
        $id = $this->guardar($datos);
        return $id;
    }
    
    
    public function getPorcentaje($idPostulante)
    {
        if ($idPostulante) {
            $collection = $this->getCollection();
            $result = $collection->find(array(
                'idPostulante' => $idPostulante
            ));
            return $result;
        }
        
    }
    
        
    
}
