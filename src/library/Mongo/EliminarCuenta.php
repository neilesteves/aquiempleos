<?php
/**
 * Procesos de Eliminar Cuenta en Mongo
 *
 * @author denis
 */
class Mongo_EliminarCuenta extends Mongo_Collection
{
    
    protected $_collection = 'eliminarcuenta';
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
    public function save($datos)
    {
        $id = 0;
        if (count($datos) >0) {
            $datos['created_at'] = date('Y-m-d H:i:s');                    
            $datos['ip'] = $_SERVER['REMOTE_ADDR'];
            $datos['url_origen'] = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $datos['agente'] = $_SERVER['HTTP_USER_AGENT'];            
            $id = $this->guardar($datos);
        }
        return $id;
        
    }
    
    
    
        
    
}
