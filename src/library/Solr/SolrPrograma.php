<?php

class Solr_SolrPrograma extends Solr_SolrClient
{    
    private $url    = null;
    // Nombre del core de solr
    private $core = 'programa'; 
    
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
        $mConfig = Zend_Registry::get('config')->solrProgramas;
        $this->_config = Zend_Registry::get('config');
        $this->_model = 'programas';
        $this->_cache = Zend_Registry::get('cache');
       

        $this->solrCli = new Solarium\Client($mConfig);
         
        $this->select = $this->solrCli->createSelect();     
      
    }


    
     public function getProgramaByName($name)
    { 

        $client  =    $this->solrCli ;
        $query =$this->select;
       // $filter = new Zend_Filter_Alnum(array('allowwhitespace' => true));
        $name =mb_convert_case($name,MB_CASE_TITLE, 'UTF-8');
        
        $name = str_replace(' ','\ ',$name);
       // var_dump($name);exit;
        $query->setStart(0)->setRows(10);
        $query->setQuery('nombre:"'.$name.'*"');

     
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
        
        $client  = $this->solrCli ;
        $query =$this->select;
        $query->setStart(0)->setRows(3);
        $query->setQuery("$descripcion*");
        $edismax = $query->getEDisMax();        
        $edismax->setQueryFields('slug');
        $edismax->setBoostQuery('slug:"'.$descripcion.'"');
        $response =  $client->suggester($query);
              
        foreach($response as $doc => $termResult) {   
            if(!$termResult) {
                return $termResult['id'];
            }
        }
        return false;
    }
    
    public function addPrograma($id){
         
        try {
        $sc = $this->solrCli;    
        $moAdtitud = new Solr_SolrAbstract($sc,$this->coreName);
        $modelAptitudes = new Application_Model_Aptitudes();
        $solradd = $moAdtitud->add($modelAptitudes->getSolrAptitusdesId($id));
        if($solradd===0)
            return true;
        else
            return false;
        
        } catch (Solarium\Exception\HttpException  $exc) {
           echo $exc->getMessage();
        }

            
            
    }
     public function getProgramaByIds($array=array())
    { 
         if(empty($array))
             return array();
        $client  =    $this->solrCli ;
        $query =$this->select;
        $query->setStart(0)->setRows(100);
        $programas = implode(' OR ', $array);
        $query->createFilterQuery('id')->setQuery("id:($programas)");
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
