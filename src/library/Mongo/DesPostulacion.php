<?php
/**
 * Procesos de Despostulacion en Mongo
 *
 * @author denis
 */
class Mongo_DesPostulacion extends Mongo_Collection
{
    
    protected $_collection = 'despostulacion';
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
        $datos['created_at'] = date('Y-m-d H:i:s');        
        $datos['origin'] = 'web';        
        $id = $this->guardar($datos);
        return $id;
    }
    
    
    /*
     * Determinar si el usuario ha despostulado del proceso.
     * 
     * @param int $idPostulante     ID del Postulante
     * @param int $idAviso          ID del Aviso
     * @return boolean              true en caso que que exista, 
     *                              caso contrario false
     */
    public function hasDespostulacion($idPostulante, $idAviso)
    {        
        $haDespostulado = false;        
        if ($idPostulante && $idAviso) {
            
            try {
                
                $collection = $this->getCollection();
                $res = $collection->find(array(
                    'id_anuncio_web' => $idAviso,
                    'id_postulante' => $idPostulante
                ));                
                $haDespostulado = ($res->count() > 0) ? true : false;
                
            } catch (Exception $ex) {                
                return $haDespostulado;
            }
            
        }
        
        return $haDespostulado;
        
    }
        
    
}
