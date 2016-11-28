<?php
/**
 * Description of Util
 * 
 * @author eanaya
 *
 */
class App_Controller_Action_Helper_LogActualizacionBI extends Zend_Controller_Action_Helper_Abstract
{
    public $_experiencia='';
    public $_estudios='';
    public $_idiomas='';
    public $_programas='';
    public $_otrosestudios='';
    public $_cache='';
    public function __construct()
    {
        $this->_config = Zend_Registry::get('config');
          $this->_cache = Zend_Registry::get('cache');
//        if (isset($data['id'])) {
//            $Experiancia= new Application_Model_Experiencia();
//            $Estudio= new Application_Model_Estudio();
//            $Idioma= new Application_Model_DominioIdioma();
//            $Programa= new Application_Model_DominioProgramaComputo();        
//            $OtrosEstudios= new Application_Model_Estudio();
//
//             if ($Experiancia->getLogPostulanteExperianciaTotal($data['id'])) {
//                 $this->_experiencia+=$this->config->dashboard->peso->experiencia;
//             }
//             if ($Estudio->getLogPostulanteEstudioTotal($data['id'])) {
//                 $this->_estudios+=$this->config->dashboard->peso->estudios;
//             } 
//             
//             if ($Idioma->getLogPostulanteIdiomaTotal($data['id'])) {
//                 $this->_idiomas += ($this->config->dashboard->peso->idiomas) ;
//             } 
//             if ($Programa->getLogPostulanteProgramaTotal($data['id'])) {
//                $this->_programas += ($this->config->dashboard->peso->programas) ; 
//            }
//            
//            if ($OtrosEstudios->getLogPostulanteOtroEstudioTotal($arrayPostulante['idpostulante'])) {
//               $this->_otrosestudios += ($this->config->dashboard->peso->otrosestudios) ;
//            }
//        }
        
    }
    /**
     * 
     * Registra un log de las actualizacion por elemento que hace el cliente de registro de datos paso1
     * @param int $idPostulante
     * @param array $valuesPost
     */
    public function logActualizacionPostulantePaso1($idPostulante, $valuesPost)
    {
//       $solr= new App_Controller_Action_Helper_Solr();
//       $solr->addSolr($idPostulante);
           
       
//        $sc = new Solarium\Client($this->_config->solr);
//        $moPostulante = new Solr_SolrAbstract($sc,'postulante');        
//        $moPostulante->addPostulante($idPostulante);
//        
        
        $modelLogPostulante = new Application_Model_LogPostulante();
        $modelPostulante = new Application_Model_Postulante();
        
        $date = date('Y-m-d H:i:s');
        $arrayLogPostulante = $modelLogPostulante->getLogPostulanteDatosPaso1($idPostulante);
        $arrayPostulante = $modelPostulante->getPostulante($idPostulante);

        $data = array(
            'id_postulante' => $idPostulante,
            'fh_creacion' => $date,
            'fh_actualizacion' => $date,
        );
        
        //@codingStandardsIgnoreStart  
        $tipoElemento = array (
           Application_Model_LogPostulante::DATOS_PERSONALES,
           Application_Model_LogPostulante::WEB ,
           Application_Model_LogPostulante::PRESENTACION ,
           Application_Model_LogPostulante::FOTO 
        );
        //@codingStandardsIgnoreEnd 
         
        $codicionElemento = array(
            0 == 0,
            isset($valuesPost["website"]) && $arrayPostulante["website"] != $valuesPost["website"],
            isset($valuesPost["presentacion"]) && $arrayPostulante["presentacion"] != $valuesPost["presentacion"],
             $arrayPostulante["path_foto"] != $valuesPost["path_foto"] && $valuesPost["path_foto"] != ''
        );
        
        $codicionElementoDelete = array(
            0 != 0,
            //!isset($valuesPost["website"]) && $valuesPost["website"] == null ,
            !isset($valuesPost["website"]),
            //!isset($valuesPost["presentacion"]) && $valuesPost["presentacion"] == null,
            !isset($valuesPost["presentacion"]),
            $valuesPost["path_foto"] == ''
        );
        
        $porcentajeElemento = array (
            0,
            $this->_config->dashboard->peso->tuweb,
            $this->_config->dashboard->peso->presentacion,
            $this->_config->dashboard->peso->foto,
        );
        
        if (count($arrayLogPostulante)!= 0) {
            $count = 0;
            $maxTipoEle = count($tipoElemento);
            foreach ($arrayLogPostulante as $row) {
                $where = $modelLogPostulante->getAdapter()->quoteInto('id = ?', $row['id']);
                $count = 0;
                for ($a = $count ; $a < $maxTipoEle; $a++) {
                    if ( isset($tipoElemento[$a]) && $row['campo_modificado'] == $tipoElemento[$a] ) {
                        unset($tipoElemento[$a]);
                        if ($codicionElemento[$a]) {
                            $modelLogPostulante->update(array('fh_actualizacion' => $date ), $where);
                        } elseif ($codicionElementoDelete[$a]) {
                            $modelLogPostulante->delete($where);
                        }
                    }
                }
            }
            
            $maxTipoElemento = count($tipoElemento);
            for ($a = 1; $a < $maxTipoEle; $a++) {
                $condicion = $codicionElemento[$a];
                if ($condicion && isset($tipoElemento[$a])) {
                    $data['campo_modificado'] = $tipoElemento[$a];
                    $data['porcentaje'] = $porcentajeElemento[$a] ;
                    $modelLogPostulante->insert($data);
                }
            }
            
        } else {
            
            $data['campo_modificado'] = $tipoElemento[0];
            $data['porcentaje'] = $porcentajeElemento[0];
            
            $modelLogPostulante->insert($data);
            
            if ( isset($valuesPost['website']) && $valuesPost['website'] != null) {
                
                $data['campo_modificado'] = $tipoElemento[1];
                $data['porcentaje'] = $porcentajeElemento[1];
                $modelLogPostulante->insert($data);
            }
            
            if ( isset($valuesPost['presentacion']) && $valuesPost['presentacion'] != null) {
                
                $data['campo_modificado'] = $tipoElemento[2];
                $data['porcentaje'] = $porcentajeElemento[2];
                $modelLogPostulante->insert($data);
            }
            
            if ($valuesPost['path_foto'] != '') {
                
                $data['campo_modificado'] = $tipoElemento[3];
                $data['porcentaje'] = $porcentajeElemento[3];
                $modelLogPostulante->insert($data);
            }
        }
        
        $this->actualizarAcumulado($idPostulante);
    }
    
    
    
    public function logActualizacionSugeridosPaso2($idPostulante, $campoModificar = null)
    {
        if ($idPostulante && $campoModificar) {
            $modelLog = new Application_Model_LogPostulante();
            $idLog = $modelLog->getLogPostulanteDatosPaso2($idPostulante, $campoModificar);
            $date = date('Y-m-d H:i:s');
            $data = array(
                'id_postulante' => $idPostulante,
                'fh_creacion' => $date,
                'fh_actualizacion' => $date,
                'campo_modificado' => $campoModificar,
            );
            $dataUpdate = array ('fh_actualizacion' => $date ); 
            
            if ($campoModificar === Application_Model_LogPostulante::SUGERIDOS) {
                /// Hasta que creen la tabla .....
                
            }
        
            $this->actualizarAcumulado($idPostulante);
            
        }
        
    }
    
    
    public function logActualizacionPostulantePaso2 (
        $idPostulante, $campoModificar = null, $valOriCv = null, $valNueCv = null
    )
    {
        $solrAdd= new Solr_SolrPostulante();
        $solrAdd->add($idPostulante);
        if (isset($campoModificar)) {
            $modelLog = new Application_Model_LogPostulante();
            $idLog = $modelLog->getLogPostulanteDatosPaso2($idPostulante, $campoModificar);
            $date = date('Y-m-d H:i:s');
            $data = array(
                'id_postulante' => $idPostulante,
                'fh_creacion' => $date,
                'fh_actualizacion' => $date,
                'campo_modificado' => $campoModificar,
            );
            $dataUpdate = array ('fh_actualizacion' => $date ); 
        }
    
       
        
            
        if ($campoModificar == Application_Model_LogPostulante::ESTUDIOS) {
            $modelEstudio = new Application_Model_Estudio();
            $arrayEstudio = $modelEstudio->getEstudios($idPostulante);
            $where = $modelLog->getAdapter()->quoteInto('id = ?', $idLog);
            
            if (count($arrayEstudio) >= 1 && $idLog != false) {
//                echo 'Estudio Update';
                $modelLog->update($dataUpdate, $where);
                //update
            } elseif (count($arrayEstudio) > 0 && $idLog == false) {
//                echo 'Estudio insert 1';
                $data['porcentaje'] = $this->_config->dashboard->peso->estudios;
                $modelLog->insert($data);
                //insert
            } elseif (count($arrayEstudio) == 1 && $idLog == false) {
//                echo 'Estudio insert 2';
                $data['porcentaje'] = $this->_config->dashboard->peso->estudios;
                $modelLog->insert($data);
                //insert
            } elseif (count($arrayEstudio) == 0 && $idLog != false) {
                //echo 'Estudio delete';
                $modelLog->delete($where);
                //delete
            }
        }if ($campoModificar == Application_Model_LogPostulante::OTRO_ESTUDIO) {
            $modelOtroEstudio = new Application_Model_Estudio();
            $arrayOtroEstudio = $modelOtroEstudio->getOtrosEstudios($idPostulante);
            $where = $modelLog->getAdapter()->quoteInto('id = ?', $idLog);
            
            if (count($arrayOtroEstudio) >= 1 && $idLog != false) {
                $modelLog->update($dataUpdate, $where);
            } elseif (count($arrayOtroEstudio) > 0 && $idLog == false) {
                $data['porcentaje'] = $this->_config->dashboard->peso->otrosestudios;
                $modelLog->insert($data);
            } elseif (count($arrayOtroEstudio) == 1 && $idLog == false) {
                $data['porcentaje'] = $this->_config->dashboard->peso->otrosestudios;
                $modelLog->insert($data);
            } elseif (count($arrayOtroEstudio) == 0 && $idLog != false) {
                $modelLog->delete($where);
            }
        } elseif ($campoModificar == Application_Model_LogPostulante::EXPERIENCIA) {
        
            $modelExperiencia = new Application_Model_Experiencia();
            $arrayExperiencia = $modelExperiencia->getExperiencias($idPostulante);
            $where = $modelLog->getAdapter()->quoteInto('id = ?', $idLog);
            
            if (count($arrayExperiencia) >= 1 && $idLog != false) {
                //echo 'Experiencia Update';
                $modelLog->update($dataUpdate, $where);
                //update
            } elseif (count($arrayExperiencia) > 1 && $idLog == false) {
                //echo 'Experiencia insert 1';
                $data['porcentaje'] = $this->_config->dashboard->peso->experiencia;
                $modelLog->insert($data);
                //insert
            } elseif (count($arrayExperiencia) == 1 && $idLog == false) {
                //echo 'Experiencia insert 2';
                $data['porcentaje'] = $this->_config->dashboard->peso->experiencia;
                $modelLog->insert($data);
                //insert
            } elseif (count($arrayExperiencia) == 0 && $idLog != false) {
                //echo 'Experiencia delete';
                $modelLog->delete($where);
                //delete
            }
        } elseif ($campoModificar == Application_Model_LogPostulante::IDIOMAS) {
            $modelIdioma = new Application_Model_DominioIdioma();
            $arrayIdioma = $modelIdioma->getDominioIdioma($idPostulante);
           
            $where = $modelLog->getAdapter()->quoteInto('id = ?', $idLog);
            
            if (count($arrayIdioma) > 1   && $idLog != false) {
                //echo 'Idioma Update';
                $modelLog->update($dataUpdate, $where);
                //update
            } elseif (count($arrayIdioma) > 1 && $idLog == false) {
                //echo 'Idioma insert 1';
                $data['porcentaje'] = $this->_config->dashboard->peso->idiomas;
                $modelLog->insert($data);
                //insert
            } elseif (count($arrayIdioma) == 1 && $idLog == false) {
                //echo 'Idioma insert 2';
                $data['porcentaje'] = $this->_config->dashboard->peso->idiomas;
                $modelLog->insert($data);
                //insert
            } elseif (count($arrayIdioma) == 0 && $idLog != false) {
                //echo 'Idioma delete';
                $modelLog->delete($where);
                //delete
            }
        } elseif ($campoModificar == Application_Model_LogPostulante::PROGRAMAS) {
            $modelPrograma = new Application_Model_DominioProgramaComputo();
            $arrayPrograma = $modelPrograma->getDominioProgramaComputo($idPostulante);
        
            $where = $modelLog->getAdapter()->quoteInto('id = ?', $idLog);
            
             $data['porcentaje'] = $this->_config->dashboard->peso->programas;
             
             
            if (count($arrayPrograma) >= 1 && $idLog != false && 
                !empty($arrayPrograma['id_programa_computo']) && 
                !empty($arrayPrograma['nivel']) ) {
                //echo 'Progamas update';
                $data['porcentaje'] = $this->_config->dashboard->peso->programas;
                $modelLog->update($data);
                //update
            } elseif (count($arrayPrograma) >= 1 && 
                    empty($arrayPrograma['id_programa_computo']) && 
                    empty($arrayPrograma['nivel']) && 
                    $idLog == false) {
                //echo 'Progamas insert 1';
                $data['porcentaje'] = $this->_config->dashboard->peso->programas;
                $id= $modelLog->insert($data);
                 
                //insert
            } elseif (count($arrayPrograma) == 1 && 
                !empty($arrayPrograma['id_programa_computo']) && 
                !empty($arrayPrograma['nivel'])  && 
                $idLog !== false) {
                //echo 'Progamas insert 2';
                $data['porcentaje'] = $this->_config->dashboard->peso->programas;
                $modelLog->insert($data);
                //insert
            } elseif (count($arrayPrograma) == 0 && $idLog != false) {
                //echo 'Progamas delete';
                $modelLog->delete($where);
                //delete
            }elseif( (count($arrayPrograma) == 0 && $idLog == false)){
                $modelLog->delete($where);
                //delete
            }
        }  elseif ($campoModificar == Application_Model_LogPostulante::LOGROS) {
            $modelLogro = new Application_Model_Logros();
            $arrayLogro = $modelLogro->getLogrosPostulante($idPostulante);
            
            $where = $modelLog->getAdapter()->quoteInto('id = ?', $idLog);
            
            if (count($arrayLogro) > 1   && $idLog != false) {
                $modelLog->update($dataUpdate, $where);
            } elseif (count($arrayLogro) > 1 && $idLog == false) {
                $data['porcentaje'] = $this->_config->dashboard->peso->logros;
                $modelLog->insert($data);
                //insert
            } elseif (count($arrayLogro) == 1 && $idLog == false) {
                //echo 'Idioma insert 2';
                $data['porcentaje'] = $this->_config->dashboard->peso->logros;
                $modelLog->insert($data);
                //insert
            } elseif (count($arrayLogro) == 0 && $idLog != false) {
                //echo 'Idioma delete';
                $modelLog->delete($where);
                //delete
            }
        } elseif ($campoModificar == Application_Model_LogPostulante::DATOS_PERSONALES) {
            $modelPostulante = new Application_Model_Postulante();
            $arrayPostulante = $modelPostulante->getPostulante($idPostulante);
            $datosCompletos = isset($arrayPostulante['num_doc']) &&
                                isset($arrayPostulante['sexoMF']) &&
                                isset($arrayPostulante['telefono']) &&
                                isset($arrayPostulante['celular']) &&
                                isset($arrayPostulante['estado_civil']) &&
                                isset($arrayPostulante['path_foto_dos']);
            $where = $modelPostulante->getAdapter()->quoteInto('id = ?', $idLog);
            
            if ($datosCompletos && $idLog != false) {
                $modelLog->update($dataUpdate, $where);
            } elseif ($datosCompletos && $idLog == false) {
                $data['porcentaje'] = $this->_config->dashboard->peso->perfil;
                $modelLog->insert($data);
            } elseif (!$datosCompletos && $idLog != false) {
                $modelLog->delete($where);
            }
        } elseif ($campoModificar == Application_Model_LogPostulante::SUBIR_CV) {
            
            $where = $modelLog->getAdapter()->quoteInto('id = ?', $idLog);
            
            if ($valOriCv != $valNueCv && $valNueCv != '' && $idLog != false) {
                //echo 'cv update';
                $modelLog->update($dataUpdate, $where);
                //update
            } elseif ($valNueCv != '' && $valOriCv != $valNueCv && $idLog == false) {
                //echo 'cv insert';
                $data['porcentaje'] = $this->_config->dashboard->peso->subircv;
                $modelLog->insert($data);
                //insert
            } elseif ($valNueCv == '' && $valNueCv != $valOriCv && $idLog != false) {
                //echo 'cv delete';
                $modelLog->delete($where);
                //delete
            }
        } elseif ($campoModificar == Application_Model_LogPostulante::SUGERIDOS) {
            
            $where = $modelLog->getAdapter()->quoteInto('id = ?', $idLog);
            
            if ($valOriCv != $valNueCv && $valNueCv != '' && $idLog != false) {
                //echo 'cv update';
                $modelLog->update($dataUpdate, $where);
                //update
            } elseif ($valNueCv != '' && $valOriCv != $valNueCv && $idLog == false) {
                //echo 'cv insert';
                $data['porcentaje'] = $this->_config->dashboard->peso->subircv;
                $modelLog->insert($data);
                //insert
            } elseif ($valNueCv == '' && $valNueCv != $valOriCv && $idLog != false) {
                //echo 'cv delete';
                $modelLog->delete($where);
                //delete
            }
        }//exit;
        
        $this->actualizarAcumulado($idPostulante);
        
    }
    
    
    
    
    public function logActualizacionEmpresaLogeo($valuesPost)
    {
        
        $data = array (
            'id_empresa' => $valuesPost['id_empresa'],
            'id_usuario' => $valuesPost['id_usuario'],
            'fh_login' => date('Y-m-d H:i:s')
        );
        
        $modelLogEmpresa = new Application_Model_LogEmpresa();
        $modelLogEmpresa->insert($data);
    }
    
    public function logActualizacionBuscadorAviso($params, $idEmpresa, $idTipoBusqueda)
    {
        $parametros = $this->_config->buscadorempresa->param;
         
        $data = array (
            'id_empresa' => $idEmpresa,
            'tipo_busqueda' => $idTipoBusqueda
        );
        
        if (isset($params['tipo'])) {
            if (isset($params['niveldeestudios']) && $params['niveldeestudios'] != '' 
                && $parametros->nivelestudios == $params['tipo']) {
                //echo 'Nivel Estudio';
                
                $data['tipo_filtro'] = $parametros->nivelestudios;
                $this->mantenimientoBusqueda($data, $params['niveldeestudios']);
            } 
            
            if (isset($params['tipodecarrera']) && $params['tipodecarrera'] != '' 
                && $parametros->tipodecarrera == $params['tipo']) {
                //echo 'Nivel Carrera';
                
                $data['tipo_filtro'] = $parametros->tipodecarrera;
                $this->mantenimientoBusqueda($data, $params['tipodecarrera']);
            } 
            
            if (isset($params['experiencia']) && $params['experiencia'] != '' 
                && $parametros->experiencia == $params['tipo']) {
                //echo 'Nivel Experiencia';
                
                $data['tipo_filtro'] = $parametros->experiencia;
                $this->mantenimientoBusqueda($data, $params['experiencia']);
            } 
            
            if (isset($params['idiomas']) && $params['idiomas'] != '' 
                && $parametros->idiomas == $params['tipo']) {
                //echo 'Nivel Idioma';
                
                $data['tipo_filtro'] = $parametros->idiomas;
                $this->mantenimientoBusqueda($data, $params['idiomas']);
            } 
            
            if (isset($params['programas']) && $params['programas'] != '' 
                && $parametros->programas == $params['tipo']) {
                //echo 'Nivel Programa';
                
                $data['tipo_filtro'] = $parametros->programas;
                $this->mantenimientoBusqueda($data, $params['programas']);
            } 
            
            if (isset($params['edad']) && $params['edad'] != '' 
                && $parametros->edad == $params['tipo']) {
                //echo 'Nivel Edad';
                
                $data['tipo_filtro'] = $parametros->edad;
                $this->mantenimientoBusqueda($data, $params['edad']);
            } 
            
            if (isset($params['sexo']) && $params['sexo'] != '' 
                && $parametros->sexo == $params['tipo']) {
                //echo 'Nivel Ubicacion';
                
                $data['tipo_filtro'] = $parametros->sexo;
                $this->mantenimientoBusqueda($data, $params['sexo']);
            }
            
            if (isset($params['ubicacion']) && $params['ubicacion'] != '' 
                && $parametros->ubicacion == $params['tipo']) {
                //echo 'Nivel Ubicacion';
                
                $data['tipo_filtro'] = $parametros->ubicacion;
                $this->mantenimientoBusqueda($data, $params['ubicacion']);
            }
            
            if (isset($params['query']) && $params['query'] != '' 
                && $parametros->query == $params['tipo']) {
                //echo 'Nivel Query';
                
                $data['tipo_filtro'] = $parametros->query;
                $this->mantenimientoBusqueda($data, null);
                
            }
            if (isset($params['conadis_code']) && $params['conadis_code'] != '' 
                && $parametros->conadis_code == $params['tipo']) {
                //echo 'Nivel Query';
                
                $data['tipo_filtro'] = $parametros->conadis_code;
                $this->mantenimientoBusqueda($data, null);
                
            }
        }
    }
    
    public function mantenimientoBusqueda ($data, $valueFiltro)
    {
        
        $modelLogBusqueda = new Application_Model_LogBusqueda;
        $date = date('Y-m-d H:i:s');
        
        $niveldeestudios = $valueFiltro;
        $arrayRow = explode("--", $niveldeestudios);
        $count = count($arrayRow);

        for ($i = $count-1; $i < $count; $i++) {
            $data['tipo_opcion_id'] = $arrayRow[$i];
            $arrayLogBusqueda = $modelLogBusqueda->getLogBusquedaXIdEmpresa($data);
            unset($data['tipo_opcion_id']);
        }
        
        if ($arrayLogBusqueda == false) {
            //echo '1º insert';
            $data['fh_registro'] = $date;
            $data['fh_actualizacion'] = $date;
            $data['contador'] = 1 ;
            
            for ($i = $count-1; $i < $count; $i++) {
                $data['tipo_opcion_id'] = $arrayRow[$i];
                $modelLogBusqueda->insert($data);
            }
        } else {
            if (date('Ymd', strtotime($arrayLogBusqueda['fh_registro'])) != date('Ymd', strtotime($date))) {
                //echo '2do insert';
                $data['fh_registro'] = $date;
                $data['fh_actualizacion'] = $date;
                $data['contador'] = 1 ;
                
                for ($i = $count-1; $i < $count; $i++) {
                    $data['tipo_opcion_id'] = $arrayRow[$i];
                    $modelLogBusqueda->insert($data);
                }
            } else {
                //echo 'update';
                $data['fh_actualizacion'] = $date;
                $data['contador'] = $arrayLogBusqueda['contador']+1;
                
                $where = $modelLogBusqueda->getAdapter()->quoteInto('id = ? ', $arrayLogBusqueda['id']);
                
                for ($i = $count-1; $i < $count; $i++) {
                    $modelLogBusqueda->update($data, $where);
                }
            }
        }
    }

    public function actualizarAcumulado($idPostulante)
    {
        $modelLogPostulante = new Application_Model_LogPostulante();
        $arrayLogPostulante = $modelLogPostulante->getLogPostulanteDatos($idPostulante);
        
        $acumulado = 0;
        foreach ($arrayLogPostulante as $row) {
            $acumulado = $acumulado + $row['porcentaje'];
            $data = array(
                'acumulado' => $acumulado
            );
            $where = $modelLogPostulante->getAdapter()->quoteInto('id = ?', $row['id']);
            $modelLogPostulante->update($data, $where);
            
        }
    }
    
    public function logActualizacionPostulanteDashwood ($id,$tipo,$porcentaje,$total){ 
       //$solr= new App_Controller_Action_Helper_Solr();
      // $solr->addSolr($id);
        $modelLogPostulante = new Application_Model_LogPostulante();
        $arrayLogPostulante = $modelLogPostulante->getLogPostulanteTipo($id,$tipo);
        //var_dump($arrayLogPostulante,$total);exit;
        if ($arrayLogPostulante) {
            $data['id_postulante']=$id;
            $data['fh_creacion']=$arrayLogPostulante[0]['fh_creacion']; 
            $data['fh_actualizacion']=date('Y-m-d H:i:s'); 
            $data['campo_modificado']=$tipo;              
            $data['porcentaje']=$porcentaje; 
            $data['acumulado']=$total;              
            $where = $modelLogPostulante->getAdapter()->quoteInto('id = ?', $arrayLogPostulante[0]['id']);
            $loga = $modelLogPostulante->update($data,$where);
        } else {
            $data['id_postulante']=$id;
            $data['fh_creacion']=date('Y-m-d H:i:s'); 
            $data['fh_actualizacion']=date('Y-m-d H:i:s'); 
            $data['campo_modificado']=$tipo;              
            $data['porcentaje']=$porcentaje; 
            $data['acumulado']=$total;            
            $loga= $modelLogPostulante->insert($data); 
        }
        $data['iscompleted']  =0;
        if($data['acumulado']>=$data['porcentaje']){
          $data['iscompleted']  =1;
        }
        $data['total_completado']=$modelLogPostulante->getLogPostulanteTotal($id);
      
        return $data;
    }
    
    public function logActualizacionPostulantePerfil($data) {
        $completado=0;
        $countcomple=0;
        
        foreach ($data as $value) {            
            if(!empty($value)){
                $countcomple++;
            }
        }
        
       if(count($data)==$countcomple )
       {
           $completado=$this->_config->dashboard->peso->perfil; 
        }
     return $this->logActualizacionPostulanteDashwood($data['id'], Application_Model_LogPostulante::DATOS_PERSONALES,$this->_config->dashboard->peso->perfil, $completado);
    }
    public function logActualizacionPostulantePerfilUbicaion($data) {
       $completado=0;
       $countcomple=0;

        unset($data['facebook']);
        unset($data['twitter']);
        unset($data['presentacion']);
        unset($data['disponibilidad_provincia_extranjero']);

        foreach ($data as $value) {  
            if(!empty($value)){
                $countcomple++;                
            }
        }
        
        if($countcomple==  count($data) ){
           $completado=$this->_config->dashboard->peso->ubicacion; 
        }
     return $this->logActualizacionPostulanteDashwood($data['id'], Application_Model_LogPostulante::UBICACION,$this->_config->dashboard->peso->ubicacion, $completado);
    }


    /**
     * @param $data
     * @return mixed
     */
    public function logActualizacionPostulanteSugerencias($data) {
       $completado=0;
       $countcomple=0;

        //var_dump();exit;
       if(isset($data["area_nivel"])){
           $completado=$this->_config->dashboard->peso->sugeridos;
       }

        /*if(count($data)>= 2){
           $completado=$this->_config->dashboard->peso->sugeridos; 
        }*/
        
     return $this->logActualizacionPostulanteDashwood($data['id'], Application_Model_LogPostulante::SUGERIDOS,$this->_config->dashboard->peso->sugeridos, $completado);
    }

    public function logActualizacionPostulanteEstudio($data) 
    {
        $completado = 0;
        $countcomple = 0;
        $Estudio= new Application_Model_Estudio();
        $modelLogPostulante = new Application_Model_LogPostulante();

        
        if (!isset($data['id'])) {
            return array(
                'id_postulante'=> null,
                'fh_creacion'=>date('Y-m-d H:i:s'),
                'fh_actualizacion'=>date('Y-m-d H:i:s'),
                'campo_modificado'=>Application_Model_LogPostulante::ESTUDIOS,
                'porcentaje'=>$this->_config->dashboard->peso->estudios,
                'acumulado'=>0,
                'iscompleted'=>0,
                'total_completado'=>$modelLogPostulante->getLogPostulanteTotal($data['id']),
            );
        }
        
        
        if (!$Estudio->getLogPostulanteEstudioTotal($data['id'])) {
            $where = $modelLogPostulante->getAdapter()->quoteInto("id_postulante = ? AND campo_modificado='estudios'", $data['id']);
            $modelLogPostulante->delete($where);
            return array(
                'id_postulante'=>$data['id'],
                'fh_creacion'=>date('Y-m-d H:i:s'),
                'fh_actualizacion'=>date('Y-m-d H:i:s'),
                'campo_modificado'=>Application_Model_LogPostulante::ESTUDIOS,
                'porcentaje'=>$this->_config->dashboard->peso->estudios,
                'acumulado'=>0,
                'iscompleted'=>0,
                'total_completado'=>$modelLogPostulante->getLogPostulanteTotal($data['id']),
            );
        }
        
        if (isset($data['hidStudy'])) {
           unset($data['hidStudy']);
        }
        if (isset($data['hidInstitution'])) {
           unset($data['hidInstitution']);
        }
        if (isset($data['actualStudent'])) {
           unset($data['actualStudent']);
        }
        if (empty($data['hidCareer'])) {
           unset($data['hidCareer']);
        }    
        foreach ($data as $value) {            
            if(!empty($value)  ){
                $countcomple++;
            }
        }
        if (count($data)==$countcomple) {
           $completado=$this->_config->dashboard->peso->estudios; 
        }
        return $this->logActualizacionPostulanteDashwood(
                $data['id'], 
                Application_Model_LogPostulante::ESTUDIOS, 
                $this->_config->dashboard->peso->estudios, 
                $completado
        );
    }
   
  
  public function logActualizacionPostulanteExperiencia($data) {
      
      if (!isset($data['id'])) {
          $data['id'] = $data['id_postulante'];
      }      
        $completado=0;
        $countcomple=0;
        $Experiancia= new Application_Model_Experiencia();
        $modelLogPostulante = new Application_Model_LogPostulante();
        if(!$Experiancia->getLogPostulanteExperianciaTotal($data['id'])){
            $where = $modelLogPostulante->getAdapter()->quoteInto("id_postulante = ? AND campo_modificado='experiencia'", $data['id']);
            $modelLogPostulante->delete($where);
            return array(
                'id_postulante'=>$data['id'],
                'fh_creacion'=>date('Y-m-d H:i:s'),
                'fh_actualizacion'=>date('Y-m-d H:i:s'),
                'campo_modificado'=>Application_Model_LogPostulante::EXPERIENCIA,
                'porcentaje'=>$this->_config->dashboard->peso->experiencia,
                'acumulado'=>0,
                'iscompleted'=>0,
                'total_completado'=>$modelLogPostulante->getLogPostulanteTotal($data['id']),
                );
        }
        
       
         unset($data['id_tipo_proyecto']);
         unset($data['comentarios']);
     
        if (isset($data['nombre_proyecto']) || !empty($data['nombre_proyecto'])) {
           unset($data['nombre_proyecto']);
        }
        if (isset($data['costo_proyecto']) || !empty($data['costo_proyecto'])) {
           unset($data['costo_proyecto']);
        }
        if (isset($data['id_Experiencia']) || !empty($data['id_Experiencia'])) {
           unset($data['id_Experiencia']);
        }
        if (isset($data['en_curso']) || !empty($data['en_curso'])) {
           unset($data['en_curso']);
        }
        if (isset($data['lugar']) || !empty($data['lugar'])) {
           unset($data['lugar']);
        }
        if (isset($data['chkInProgress']) && ($data['chkInProgress']=='on'  || empty($data['chkInProgress']) ) ) {
             unset($data['fin_mes']);
             unset($data['fin_ano']);
             unset($data['chkInProgress']);
        }else{
             unset($data['chkInProgress']);
        }
        foreach ($data as $value) { 
              
            if(!empty($value)  ){
               $countcomple++;
            }
        }
       if(count($data)==$countcomple){
           $completado=$this->_config->dashboard->peso->experiencia; 
        }
    
     return $this->logActualizacionPostulanteDashwood($data['id'], Application_Model_LogPostulante::EXPERIENCIA,$this->_config->dashboard->peso->experiencia, $completado);
    }
  
  public function logActualizacionPostulanteProgramas($data) {

        $completado=0;$countcomple=0;
        $Programa= new Application_Model_DominioProgramaComputo();
        $modelLogPostulante = new Application_Model_LogPostulante();
        if(!$Programa->getLogPostulanteProgramaTotal($data['id'])){
            $where = $modelLogPostulante->getAdapter()->quoteInto("id_postulante = ? AND campo_modificado='programas'", $data['id']);
            $modelLogPostulante->delete($where);
            return array(
                'id_postulante'=>$data['id'],
                'fh_creacion'=>date('Y-m-d H:i:s'),
                'fh_actualizacion'=>date('Y-m-d H:i:s'),
                'campo_modificado'=>Application_Model_LogPostulante::PROGRAMAS,
                'porcentaje'=>$this->_config->dashboard->peso->programas,
                'acumulado'=>0,
                'iscompleted'=>0,
                'total_completado'=>$modelLogPostulante->getLogPostulanteTotal($data['id']),
                );
        }      
        foreach ($data as $value) {            
            if(!empty($value)  ){
                $countcomple++;
            }
        }
       if(count($data)==$countcomple){
           $completado=$this->_config->dashboard->peso->programas; 
        }
     return $this->logActualizacionPostulanteDashwood($data['id'], Application_Model_LogPostulante::PROGRAMAS,$this->_config->dashboard->peso->programas, $completado);
    }
  
    
  public function logActualizacionPostulanteIdioma($data) {
    
      
        $completado=0;$countcomple=0;
        $Idioma = new Application_Model_DominioIdioma();
        $modelLogPostulante = new Application_Model_LogPostulante();
        
        if (!isset($data['id'])) {
            return array(
                'id_postulante' => null,
                'fh_creacion' => date('Y-m-d H:i:s'),
                'fh_actualizacion' => date('Y-m-d H:i:s'),
                'campo_modificado' => Application_Model_LogPostulante::IDIOMAS,
                'porcentaje' => $this->_config->dashboard->peso->idiomas,
                'acumulado' => 0,
                'iscompleted' => 0,
                'total_completado' => $modelLogPostulante->getLogPostulanteTotal($data['id']),
            );
        }
        
         
        if (!$Idioma->getLogPostulanteIdiomaTotal($data['id'])) {
            $where = $modelLogPostulante->getAdapter()->quoteInto("id_postulante = ? AND campo_modificado='idiomas'", $data['id']);
            $modelLogPostulante->delete($where);
            return array(
                'id_postulante'=>$data['id'],
                'fh_creacion'=>date('Y-m-d H:i:s'),
                'fh_actualizacion'=>date('Y-m-d H:i:s'),
                'campo_modificado'=>Application_Model_LogPostulante::IDIOMAS,
                'porcentaje'=>$this->_config->dashboard->peso->idiomas,
                'acumulado'=>0,
                'iscompleted'=>0,
                'total_completado'=>$modelLogPostulante->getLogPostulanteTotal($data['id']),
                );
        }      
        foreach ($data as $value) {            
            if(!empty($value)  ){
                $countcomple++;
            }
        }
        
        
        if(count($data)==$countcomple){
           $completado=$this->_config->dashboard->peso->idiomas; 
        }
     return $this->logActualizacionPostulanteDashwood($data['id'], Application_Model_LogPostulante::IDIOMAS,$this->_config->dashboard->peso->idiomas, $completado);
    }
    
 public function logActualizacionPostulanteOtrosEstudios($data) {

     $completado=0;$countcomple=0;
        $OtrosEstudios= new Application_Model_Estudio();
        $modelLogPostulante = new Application_Model_LogPostulante();
        if(!$OtrosEstudios->getLogPostulanteOtroEstudioTotal($data['id'])){
            $where = $modelLogPostulante->getAdapter()->quoteInto("id_postulante = ? AND campo_modificado='otroestudio'", $data['id']);
            $modelLogPostulante->delete($where);
            return array(
                'id_postulante'=>$data['id'],
                'fh_creacion'=>date('Y-m-d H:i:s'),
                'fh_actualizacion'=>date('Y-m-d H:i:s'),
                'campo_modificado'=>Application_Model_LogPostulante::OTRO_ESTUDIO,
                'porcentaje'=>$this->_config->dashboard->peso->otrosestudios,
                'acumulado'=>0,
                'iscompleted'=>0,
                'total_completado'=>$modelLogPostulante->getLogPostulanteTotal($data['id']),
                );
        }      
        foreach ($data as $value) {            
            if(!empty($value)  ){
                $countcomple++;
            }
        }
        if(count($data)==$countcomple){
           $completado=$this->_config->dashboard->peso->otrosestudios; 
        }
     return $this->logActualizacionPostulanteDashwood($data['id'], Application_Model_LogPostulante::OTRO_ESTUDIO,$this->_config->dashboard->peso->otrosestudios, $completado);
    }

    
 public function logActualizacionPostulanteLogros($data) {
        $completado=0;$countcomple=0;
        $Logros= new Application_Model_Logros();
        $modelLogPostulante = new Application_Model_LogPostulante();
        if(!$Logros->getLogPostulanteLogrosTotal($data['id'])){
            $where = $modelLogPostulante->getAdapter()->quoteInto("id_postulante = ? AND campo_modificado='logros'", $data['id']);
            $modelLogPostulante->delete($where);
            return array(
                'id_postulante'=>$data['id'],
                'fh_creacion'=>date('Y-m-d H:i:s'),
                'fh_actualizacion'=>date('Y-m-d H:i:s'),
                'campo_modificado'=>Application_Model_LogPostulante::LOGROS,
                'porcentaje'=>$this->_config->dashboard->peso->logros,
                'acumulado'=>0,
                'iscompleted'=>0,
                'total_completado'=>$modelLogPostulante->getLogPostulanteTotal($data['id']),
                );
        }      
        foreach ($data as $value) {            
            if(!empty($value)  ){
                $countcomple++;
            }
        }
        if(count($data)==$countcomple){
           $completado=$this->_config->dashboard->peso->logros; 
        }
     return $this->logActualizacionPostulanteDashwood($data['id'], Application_Model_LogPostulante::LOGROS,$this->_config->dashboard->peso->logros, $completado);
    }
}
