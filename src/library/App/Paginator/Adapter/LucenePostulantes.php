<?php

class App_Paginator_Adapter_LucenePostulantes implements Zend_Paginator_Adapter_Interface
{
    protected $_query;
    protected $_ord;
    protected $_col;
    protected $_ntotal=0;
    protected $_pQuery;
    protected $_filters;
    
    protected $_searchData = null;
    
    public function __construct($query, $pQuery, $filters, $col, $ord)
    {
        $this->_query = $query;
        $this->_pQuery = $pQuery;
        $this->_filters = $filters;
        $this->_ord = $ord==""?"ASC":$ord;
        $this->_col = $col==""?"none":$col;
    }

    public function getItems($offset, $itemCountPerPage)
    {
        $this->_cont++;
        if($this->_searchData != null) return $this->_searchData;
        /*
        if ($this->_query=="") {
            return array();
        }*/
        //echo "<br>$this->_cont<br>";
        $query = $this->_query;
        $pQuery = $this->_pQuery;
        $filters= $this->_filters;
        
        $page = $offset/$itemCountPerPage+1;
        
        // @codingStandardsIgnoreStart
        $path = APPLICATION_PATH."/../java/javaLucene.jar";
        $pathi = APPLICATION_PATH."/../indexes/";
        $consulta = "java -jar $path postulantes search \"$query\" \"$pQuery\" \"$filters\" $page $itemCountPerPage $pathi $this->_col $this->_ord";
        $config = Zend_Registry::get('config');
        //echo $consulta."<br>"; //exit;
        if ($config->app->showQueryLuceneP == 1) {
            echo ("<input type='hidden' value='".htmlspecialchars($consulta)."'>");
        }
        $result = exec($consulta);

        if ($config->app->showQueryLuceneP == 1) {
            echo ("<input type='hidden' value='".htmlspecialchars($result)."'>");
        }
        
        $posEnd = strpos($result, "</LuceneResult>");
        if ($posEnd != false) {
            $cad = "";
            $divResult = explode("</LuceneResult>", $result);
            for ($i = 0; $i < (count($divResult) - 1); $i++) {
                $cad = $cad.$divResult[$i]."</LuceneResult>";
            }
            $result = $cad;
        }
        
        $xml = @simplexml_load_string($result);
        
        $this->_ntotal = @intval(@$xml->ntotal);
        if ($this->_ntotal==0) { 
            return array(); 
        }
        
        $page = intval($xml->page);
        $docs = $xml->docs;
        //var_dump($xml); exit;
        $c = 0;
        $dataResult = array();
        $data = (array) $xml->docs;
        foreach ($data as $item) {
            $datRes = array();
            
            $datRes["idpostulante"] = (String) $item->idpostulante;
            $datRes["foto"] = (String) $item->foto;
            $datRes["nombres"] = (String) $item->nombres;
            $datRes["apellidos"] = (String) $item->apellidos;
            $datRes["telefono"] = (String) $item->telefono;
            $datRes["celular"] = (String) $item->celular;
            $datRes["slug"] = (String) $item->slug;
            $datRes["score"] = (String) $item->score;
            $datRes["sexo"] = (String) $item->sexoclaves;
            $datRes["edad"] = (int)$item->edad;
            $datRes["path_cv"] = (String) $item->pathcv;
            $datRes["estudios"] = (String) $item->estudios;
            $datRes["experiencia"] = (String) $item->experiencia;
            $datRes["estudios_claves"] = (String) $item->estudiosclaves;
            $datRes["carreras_claves"] = (String) $item->carreraclaves;
            $datRes["idiomas"] = (String) $item->idiomas;
            $datRes["programasclaves"] = (String) $item->programasclaves;
            $datRes["ubigeo"] = (String) $item->ubigeo;
            $datRes["ubigeoclaves"] = (String) $item->ubigeoclaves;
            $datRes["empresa"] = (String) $item->empresa;
            $datRes["cargo"] = (String)$item->puesto;
            $datRes['mayornivelestudio'] = (String) $item->mayornivelestudio;
            $datRes['mayorcarrera'] = (String) $item->mayorcarrera;
            $datRes["estudios"] = "Ninguno";
            $datRes["carrera"] = "Ninguno";
                        
            $dataResult[] = $datRes;
        }
        $this->_searchData = $dataResult;
       
        // @codingStandardsIgnoreEnd
        //var_dump($ac); exit;
        return $dataResult;
    }

    public function count()
    {
        return $this->_ntotal;
    }
}
