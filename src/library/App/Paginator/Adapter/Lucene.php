<?php

class App_Paginator_Adapter_Lucene implements Zend_Paginator_Adapter_Interface
{
    protected $_query;
    protected $_ord;
    protected $_col;
    protected $_ntotal=0;

    public function __construct($query, $col, $ord)
    {
        $this->_query = $query;
        $this->_ord = $ord==""?"ASC":$ord;
        $this->_col = $col==""?"idpostulante":$col;
    }

    public function getItems($offset, $itemCountPerPage)
    {
        if ($this->_query=="") {
            return array();
        }
        
        $query = $this->_query;
        $page = $offset/$itemCountPerPage+1;
        
        
        $path = APPLICATION_PATH."/../java/javaLucene.jar";
        $pathi = APPLICATION_PATH."/../indexes/";
        $consulta = "java -jar $path \"$query\" $page $pathi $itemCountPerPage $this->_col $this->_ord";
        //var_dump($consulta); exit;
        $result = exec($consulta);
        $xml = @simplexml_load_string($result);
        
        $this->_ntotal = @intval(@$xml->ntotal);
        if ($this->_ntotal==0) { 
            return array(); 
        }
        
        $page = intval($xml->page);
        $docs = $xml->docs;
        //var_dump($xml); exit;
        $c = 0;
        $ac = "";
        for ($i=$offset; $i<$offset+$itemCountPerPage; $i++) {
            $ndoc = "doc".$i;
            $item = $docs->$ndoc;
            if (count($item)==0) break;
            $data[$c]["idpostulante"] = $item->idpostulante;
            $data[$c]["foto"] = $item->foto;
            $data[$c]["nombres"] = $item->nombres;
            $data[$c]["apellidos"] = $item->apellidos;
            $data[$c]["telefono"] = $item->telefono;
            $data[$c]["slug"] = $item->slug;
            $data[$c]["score"] = $item->score;
            $data[$c]["sexo"] = $item->sexoclaves;
            $data[$c]["edad"] = (int)$item->edad;
            $data[$c]["path_cv"] = $item->pathcv;
            $data[$c]["estudios"] = $item->estudios;
            $data[$c]["experiencia"] = $item->experiencia;
            $data[$c]["estudios_claves"] = $item->estudiosclaves;
            $data[$c]["carreras_claves"] = $item->carreraclaves;
            $data[$c]["idiomas"] = $item->idiomas;
            $data[$c]["programasclaves"] = $item->programasclaves;
            $data[$c]["ubigeo"] = $item->ubigeo;
            $data[$c]["ubigeoclaves"] = $item->ubigeoclaves;
            $data[$c]["empresa"] = $item->empresa;
            $data[$c]["cargo"] = $item->puesto;
            $data[$c]["estudios"] = "Ninguno";
            $data[$c]["carrera"] = "Ninguno";
            
            $arr = explode("-", $item->estudios);
            if (count($arr)>0) {
                if (trim($arr[0])=="") {
                    $data[$c]["estudios"] = "Ninguno";
                } else {
                    $data[$c]["estudios"] = $arr[0];
                }
            } else {
                $data[$c]["estudios"]="Ninguno";
            }
            
            $arr = explode("-", $item->estudiosclaves);
            if (count($arr)>0) {
                $carr = new Application_Model_Estudio();
                $r = $carr->getNivelCarrera($arr[0], $item->idpostulante);
                
                if (!$r) {
                    $data[$c]["carrera"] = "Ninguno";
                } else {
                    if ($r["descripcion"]==null) {
                        if ($r["otracarrera"]==null) {
                            $data[$c]["carrera"] = "Ninguno";
                        } else {
                            $data[$c]["carrera"] = $r["otracarrera"];
                        }
                    } else {
                        $data[$c]["carrera"] = $r["descripcion"];
                    }
                }
            }
            
            $c++;
        }
        //var_dump($ac); exit;
        return $data;
    }

    public function count()
    {
        return $this->_ntotal;
    }
}
