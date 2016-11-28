<?php
class Solr_SolrAbstract
{
    private $solrCli    = null;
    private $select     = null;
    private $res        = null;
    private $core       = '';
    public $fieldsParent= array();
    public $fieldsChild = array();

    const cantRows = 20;
    const facetLimit = 33554431;

    public function __construct(\Solarium\Client $solrCli, $core=FALSE)
    {
        if(!$core) throw new \Exception('SolrAbstract['.__LINE__.']:Core name void!');
        $this->core    = $core;
        $this->select  = $solrCli->createSelect();
        $this->solrCli = $solrCli;

        return $this;
    }
    
    /**
     *
     */
    public function setFilter()
    {/*
        $this->select = $this->solrCli->createQuery()->;
        $this->select->
        new \SolrClient\Controller\*/
    }

    /**
     * Estableciendo nuestro query String para busqueda en solr
     */
    public function setQuery($query='*:*')
    {
        //$this->select = $this->solrCli->createSelect();
        $this->select->setQuery($query);

        return $this;
    }

    /**
     * Estableciendo los fields del core del solar para nuestra respuesta.
     */
    public function setFields($fields=null)
    {
        if(is_null($this->select)) throw new \Exception('SolrAbstract['.__LINE__.']:$this->select is null');

        if(!is_null($fields)) $this->select->setFields($fields);

        return $this;
    }

    /**
     * Establece los fields para el grupo(parent)
     */
    public function setFieldsParent($fields=array())
    {
        if(!is_array($fields)) throw new \Exception('SolrAbstract['.__LINE__.']:$fields no array');
        $this->fieldsParent = $fields;

        return $this;
    }

    /**
     * Establece los fields para los items del grupo(childs)
     */
    public function setFieldsChilds($fields=array())
    {
        if(!is_array($fields)) throw new \Exception('SolrAbstract['.__LINE__.']:$fields no array');
        $this->fieldsChild = $fields;

        return $this;
    }

    /**
     * @page int numero de pagina que se requiere.
     * @rows int cantidad de registros que se necesita
     * @desf int cantidad que falto en la pagina anterior para ser una pagina.
     *           (Cuando es una paginacion de 2 cores y en una pagina puede haber
     *           registros del primer y segundo core).
     * @return array Mostrando $rows resultados iniciando en $start
     */
    public function getData($page=1, $rows=20, $desf=0)
    {
        $start = self::cantRows*($page-1)-$desf;
        if(!$this->select) throw new \Exception('SolrAbstract['.__LINE__.']:$this->select is null use setQuery!');
        $this->select->setStart($start)->setRows($rows);
        $facetSet = $this->select->getFacetSet();
        $facetSet->createFacetField('ubi')->setField('ubigeo_claves')->setLimit(self::facetLimit);
        $facetSet->createFacetField('ne')->setField('estudios_claves')->setLimit(self::facetLimit);
        $facetSet->createFacetField('oe')->setField('otros_estudios')->setLimit(self::facetLimit);
        $facetSet->createFacetField('tc')->setField('tipo_carrera_claves')->setLimit(self::facetLimit);
        $facetSet->createFacetField('id')->setField('idiomas')->setLimit(self::facetLimit);
        $facetSet->createFacetField('pr')->setField('programas_claves')->setLimit(self::facetLimit);
        $facetSet->createFacetField('se')->setField('sexo')->setLimit(self::facetLimit);
        $facetSet->createFacetField('dis')->setField('conadis_code')->setLimit(self::facetLimit);
        $facet = $facetSet->createFacetMultiQuery('ex');
        $facet->createQuery('0-0', 'experiencia:0');
        $facet->createQuery('1-3', 'experiencia:[1 TO 3]');
        $facet->createQuery('4-6', 'experiencia:[4 TO 6]');
        $facet->createQuery('7-12', 'experiencia:[7 TO 12]');
        $facet->createQuery('13-24', 'experiencia:[13 TO 24]');
        $facet->createQuery('25-36', 'experiencia:[25 TO 36]');
        $facet->createQuery('37-48', 'experiencia:[37 TO 48]');
        $facet->createQuery('49-60', 'experiencia:[49 TO 60]');
        $facet->createQuery('61-600', 'experiencia:[61 TO *]');
        //$facet->setSort(Solarium\QueryType\Select\Query\Component\Facet\Field::SORT_COUNT);
        $facet1 = $facetSet->createFacetMultiQuery('ed');
        $facet1->createQuery('15-20', 'edad:[15 TO 20]');
        $facet1->createQuery('21-25', 'edad:[21 TO 25]');
        $facet1->createQuery('26-30', 'edad:[26 TO 30]');
        $facet1->createQuery('31-35', 'edad:[31 TO 35]');
        $facet1->createQuery('36-40', 'edad:[36 TO 40]');
        $facet1->createQuery('41-45', 'edad:[41 TO 45]');
        $facet1->createQuery('46-mas', 'edad:[46 TO *]');
        //$facet1->setSort(Solarium\QueryType\Select\Query\Component\Facet\Field::SORT_COUNT);
        //$this->res = $this->solrCli->execute($this->select);
        $this->res = $this->solrCli->select($this->select);        
        $aRes = array('total'=>$this->res->getNumFound(), 'rows'=>array(),
            'ubi'=>$this->res->getFacetSet()->getFacet('ubi'),
            'tc'=>$this->res->getFacetSet()->getFacet('tc'),
            'ne'=>$this->res->getFacetSet()->getFacet('ne'),
            'oe'=>$this->res->getFacetSet()->getFacet('oe'),
            'id'=>$this->res->getFacetSet()->getFacet('id'),
            'pr'=>$this->res->getFacetSet()->getFacet('pr'),
            'ex'=>$this->res->getFacetSet()->getFacet('ex'),
            'ed'=>$this->res->getFacetSet()->getFacet('ed'),
            'se'=>$this->res->getFacetSet()->getFacet('se'),
            'dis'=>$this->res->getFacetSet()->getFacet('dis')
                );
        foreach ($this->res as $doc) {
            /*$row = $doc->getFields();
            $dp = array();
            if(count($p)) foreach($p as $i) $dp[$i]=isset($row[$i])?$row[$i]:'';
            else $dp=$row;
            $aRes['rows'][]=$dp;*/
            $aRes['rows'][] = $doc->getFields();
        }

        return $aRes;
    }

    /**
     * @return int Restorna la cantidad de coincidencias para el query especificado
     */
    public function getNumRes()
    {
        if(!$this->res) throw new \Exception('SolrAbstract['.__LINE__.'] res is null!');
        return $this->res->getNumFound();
    }
    /**
     * Estableciendo el orden para nuestra respuesta.
     */
    public function setOrder($col='',$ord)
    {
        if(is_null($this->select)) throw new \Exception('SolrAbstract['.__LINE__.']:$this->select is null');

        if(!empty($col)) 
        {            
            $this->select->addSort($col,$ord);
            //if($col == 'nombres')
            //    $this->select->addSort('apellidos',$ord);                
        }
        else
        {
            $this->select->addSort('destacado','DESC');
            $this->select->addSort('fecha_cv_update','DESC');                            
        }
        return $this;
    }

    public function addpost(){

            $client = $this->getSolrClient();  
            $client->setDefaultEndPoint($this->core);  
            $update = $client->createUpdate();
         // create a new document for the data
           $doc = $update->createDocument();
         foreach ($datapostulacion  as $key => $value) {
              $doc->$key =  $value;
           }
           $update->addDocument($doc);
           $update->addCommit();

           // this executes the query and returns the result
           $result = $client->update($update);
          if($result->getStatus()===0)
            return true;

            return  false;
    }
    
    public function addPostulante($id)
    {
        $moPostulante = new Application_Model_Postulante();
        $val=$moPostulante->valperfil($id);
        if($val=='1'){
            return 0;
        }
        $params = $moPostulante->solr($id);
//var_dump($params); die("*************");
        if(!$params)
        {
            return 0;
        }
        else
        {
            //$params['det_estudios'] = $moPostulante->getSolrPostulanteEstudios($params['idpostulante']);
            //$params['det_experiencias'] = $moPostulante->getSolrPostulanteExperiencias($params['idpostulante']);
            //$params['det_otros_estudios'] = $moPostulante->getSolrPostulanteOtrosEstudios($params['idpostulante']);
            //$params['det_idiomas'] = $moPostulante->getSolrPostulanteIdiomas($params['idpostulante']);
            //$params['det_programas'] = $moPostulante->getSolrPostulanteProgramas($params['idpostulante']);
            $params['det_aptitudes'] = $moPostulante->getSolrPostulanteAptitudes($params['idpostulante']);
        }



        $update = $this->solrCli->createUpdate();
        $doc1 = $update->createDocument();
        foreach($params as $k => $v){
  //      echo $k." -- ".$v; 
            $doc1->$k = $v;
        }
//die();
        // var_dump( $doc1);exit;   

        $update->addDocument($doc1);
        $update->addCommit();  
       

        $result = $this->solrCli->update($update);    


            //var_dump( $result->getStatus());exit;    
        return $result->getStatus();
    }

    public function deletePostulante($id)
    {
       $moPostulante = new Application_Model_Postulante();
        $val=$moPostulante->valperfil($id);
        if($val=='0'){
            return 0;
        }
      
        $update = $this->solrCli->createUpdate();
        $update->addDeleteQuery("idpostulante:$id");
        $update->addCommit();
        $result = $this->solrCli->update($update);    
        return $result->getStatus();
    }
    
    
    public function add($params)
    {
       
        $update = $this->solrCli->createUpdate();
        $doc1 = $update->createDocument();
        foreach($params as $k => $v)
            $doc1->$k = $v;
        $update->addDocument($doc1);
        $update->addCommit();
        $result = $this->solrCli->update($update);   
        $select = $this->solrCli->createSelect();
        $select->setQuery("id_anuncio_web:{$params['id_anuncio_web']}");
        $resultset = $this->solrCli->select($select);
        $nro = $resultset->getNumFound();
        if(empty($nro))
            return 1;
        else
            return 0;
        //return $result->getStatus();
    }
    
    
         public function addAptitud($params)
    {
       
        $update = $this->solrCli->createUpdate();
        $doc1 = $update->createDocument();
        foreach($params as $k => $v)
            $doc1->$k = $v;
        $update->addDocument($doc1);
        $update->addCommit();
        $result = $this->solrCli->update($update);   
       
        if(empty($result))
            return 1;
        else
            return 0;
        //return $result->getStatus();
    }
    public function delete($id,$campo)
    {   
        $update = $this->solrCli->createUpdate();
        $update->addDeleteQuery("$campo:$id");
        $update->addCommit();
        $result = $this->solrCli->update($update);    
        return $result->getStatus();
    }

}
