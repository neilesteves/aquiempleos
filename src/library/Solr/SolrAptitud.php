<?php

class Solr_SolrAptitud
{    
    private $url    = null;
    // Nombre del core de solr
    private $coreName = 'actitud'; 
    
    private $_config;
    private $_model;
    private $_cache;
    private $solrCli    = null;
    private $select     = null;
    private $valServer;
    private $validador;
    
    public function __construct()
    {
        $this->valServer=false;
        $this->helperSever = new App_Controller_Action_Helper_Solr();
        $mConfig = Zend_Registry::get('config')->solrAptitud;
//        if(!$this->helperSever->valServidorSor($mConfig)){
//            $this->valServer=true;
//        }
        $this->_config = Zend_Registry::get('config');
        $this->_model = 'actitud';
        $this->_cache = Zend_Registry::get('cache');
       
        
        //var_dump($mConfig);exit;
        $this->solrCli = new Solarium\Client($mConfig);
         
        $this->select = $this->solrCli->createSelect();     
      
    }

    
    /* 
     * Adicionar el Aviso en el Solar
     * 
     * @param Int $id   ID del Aviso.
     * @return Int      Retorna 0 en caso de exito, caso contrario un entero.
     * 
     */

    
    /* 
     * Elimina el Aviso en el Solar
     * 
     * @param Int $id   ID del Aviso.
     * @return Int      Retorna 0 en caso de exito, caso contrario un entero.
     * 
     */
    
    

    
     public function getAptitudByName($name)
    { 
        $name = str_replace(
               array("\\", "¨", "º", "-", "~",
                    "#", "@", "|", "!", "\"",
                    "·", "$", "%", "&", "/",
                    "(", ")", "?", "'", "¡",
                    "¿", "[", "^", "`", "]",
                    "+", "}", "{", "¨", "´",
                    ">", "< ", ";", ",", ":",'"',"'",
                    "."),
               '',
               $name
           );
        $client  =    $this->solrCli ;
        $query =$this->select;
        $name = str_replace(' ','\ ',$name);
        $query->setStart(0)->setRows(3);
        $query->setQuery('nombre:"'.$name.'*"');
        $query->addSorts(
                    array(
                          'nombre'=>'asc'
                    )
            );
        $response =  $client->suggester($query);
       
        $results = array();        
        foreach ($response as $term => $termResult) {
            foreach($termResult as $k => $v){
              $results[$term]['id']= $termResult['id'];
              $results[$term]['mostrar'] = $termResult['nombre'];
            }
        }
        return $results;        
    }
   

    public function obtenerRepetido($descripcion) 
    {      
        
        $client  =    $this->solrCli ;
        $query =$this->select;
        $query->setStart(0)->setRows(3);
        $query->setQuery("$descripcion");
        $edismax = $query->getEDisMax();        
        $edismax->setQueryFields('slug');
        $edismax->setBoostQuery('slug:"'.$descripcion.'"');
        $response =  $client->suggester($query);
        $result['ntotal'] = (int) $response->getNumFound();   
        foreach($response as $doc => $termResult) {   
            if($termResult) {
                return $result['ntotal'];
            }
        }       
        return false;
    }
    
    public function addAptitud($id){
         
        try {
        $sc = $this->solrCli;    
        $moAdtitud = new Solr_SolrAbstract($sc,$this->coreName); 
        $modelAptitudes = new Application_Model_Aptitudes();
        $solradd = $moAdtitud->addAptitud($modelAptitudes->getSolrAptitusdesId($id));
        if($solradd===0)
            return true;
        else 
            return false;
        
        } catch (Solarium\Exception\HttpException  $exc) {
          // echo $exc->getMessage();
        }

            
            
    }
     public function getAptitudByIds($array=array())
    { 
         if(empty($array))
             return array();
        $client  =    $this->solrCli ;
        $query =$this->select;
        $query->setStart(0)->setRows(100);
        $aptitudes = implode(' OR ', $array);
        $query->createFilterQuery('id')->setQuery("id:($aptitudes)");
        $query->addSorts(
                    array(
                          'nombre'=>'asc'
                    )
            );
        $response =  $client->execute($query);
       
        $results = array();        
        foreach ($response as $term => $termResult) {
             
         //var_dump($termResult,$name);exit;
            foreach($termResult as $k => $v){
              $results[$term]['id']= $termResult['id'];
              $results[$term]['mostrar'] = $termResult['nombre'];
            }
        }
        return $results;        
    }

}
