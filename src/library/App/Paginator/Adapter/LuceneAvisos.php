<?php

class App_Paginator_Adapter_LuceneAvisos implements Zend_Paginator_Adapter_Interface
{
    protected $_query;
    protected $_pQuery;
    protected $_filters;
    protected $_idPostulante = null;
    protected $_ntotal=0;
    protected $_count = 0;
    
    protected $_searchData = null;
    
    public function __construct($idPostulante, $query, $pQuery, $filters)
    {
        $this->_query = $query;
        $this->_pQuery = $pQuery;
        $this->_filters = $filters;
        $this->_idPostulante = $idPostulante;
    }

    public function getItems($offset, $itemCountPerPage)
    {
        $this->_count++;
        
        if($this->_searchData != null) return $this->_searchData;
        
        $query = $this->_query;
        $pQuery = $this->_pQuery;
        $filters = $this->_filters;
        
        $page = $offset/$itemCountPerPage+1;
        
        
        $path = APPLICATION_PATH."/../java/javaLucene.jar";
        $pathi = APPLICATION_PATH."/../indexes/";
        $consulta = "java -jar $path avisos search \"$query\" \"$pQuery\" \"$filters\" $page $itemCountPerPage $pathi";

        $config = Zend_Registry::get('config');
        if ($config->app->showQueryLuceneA == 1) {
            echo ('<input type="hidden" value="'.htmlspecialchars($consulta).'">');
        }
        $result = exec($consulta);

        if ($config->app->showQueryLuceneA == 1) {
            echo ('<input type="hidden" value="'.htmlspecialchars($result).'">');
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
        //echo $result; exit;
        $xml = @simplexml_load_string($result);
        
        $this->_ntotal = @intval(@$xml->ntotal);
        //var_dump($xml); exit;
        if ($this->_ntotal==0) { 
            return array(); 
        }
        
        $page = intval($xml->page);
        $docs = $xml->docs;
        
        $c = 0;
        $ac = ""; 
        $dataResult = array();
        $data = (array) $xml->docs;
        $idsAnuncios = "0, ";
        $anuncioModel = new Application_Model_AnuncioWeb();
        foreach ($data as $dat) {
            $datRes = array();
            // @codingStandardsIgnoreStart
            $datRes['awid'] = (String) $dat->awid;
            $datRes['logoanuncio'] = (String) $dat->logoanuncio;
            $datRes['empresa_rs'] = (String) $dat->empresa_rs;
            $datRes['display_name'] = (String) $dat->ubicacion;
            $datRes['funciones'] = str_replace("--&13--", "\n", (String) $dat->funciones);
            $datRes['responsabilidades'] = str_replace("--&13--", "\n", (String) $dat->responsabilidades);
            $datRes['slug_aviso'] = (String) $dat->slug_aviso;
            $datRes['fecha_publicacion'] = (String) $dat->fecha_publicacion;
            $datRes['id_anuncio_web'] = (String) $dat->id_anuncio_web;
            $datRes['puesto'] = (String) $dat->puesto;
            $datRes['dias_fp'] = (String) $dat->dias_fp;
            $datRes['prioridad'] = (String) $dat->prioridad;
            if($this->_idPostulante != null && $anuncioModel->existePostulacion($this->_idPostulante, $datRes['awid'])) {
                $datRes['idpostulante'] = $this->_idPostulante;
            } else {
                $datRes['idpostulante'] = null;
            }
            $idsAnuncios .= $datRes['awid'].", ";
            // @codingStandardsIgnoreEnd
            $dataResult[] = $datRes;
        }
        
        
        //var_dump($dataResult);
        /*$rsop = $anuncioModel->ordenarAvisosBusqueda(
            $dataResult,
            $query
        );*/
        
        $this->_searchData = $dataResult;
        //$this->_searchData = $rsop;
        return $dataResult;
    }

    public function count()
    {
        return $this->_ntotal;
    }
}
