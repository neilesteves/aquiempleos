<?php

class App_Controller_Action_Helper_PorcentajeCV extends Zend_Controller_Action_Helper_Abstract
{
    private $config;
    private $auth;
    
    public function __construct() 
    {
        $this->config = Zend_Registry::get('config');
        $this->auth = $this->auth = Zend_Auth::getInstance()->getStorage()->read();
    }
    
    /**
     * Calcula el porcentaje registrado del perfil del postulante y los ítems que falta registrar.
     * Devuelve un array con el porcentaje en la posición 0 y los ítems que faltan registrar en 
     * la posición 1.
     * 
     * @param array $arrayPostulante    Arreglo que debe contener al menos los campos:
     *                                  idpostulante, path_cv, presentacion, website, path_foto.
     * 
     * @param boolen $separados         Con el valor de true retorna con los porcentajes por separado
     *                                  con el valor de false retorna el acumulado, 
     *                                  el valor por defecto es false
     * @return array
     */
    public function getPorcentajes($arrayPostulante, $separados = false,$totalSugerencia=false)
    {   
        //return $this->getPorcentageMongo($arrayPostulante, true);
        return $this->getPorcentajesFromPostulante($arrayPostulante, $separados,$totalSugerencia);
    }
    
    
    protected function getTotalFromOtrosEstudios($idPostulante)
    {
        $total = 0;
        if ($idPostulante) {
            $modelEstudios = new Application_Model_Estudio();
            $otrosEstudios = $modelEstudios->getOtrosEstudios($idPostulante);
            $total = count($otrosEstudios);
        }
        return $total;
    }
    
    
    public function tienePerfilCompleto($idPostulante)
    {
        $ok = false;
        if ($idPostulante) {
            $modelPostulante = new Application_Model_Postulante();
            $res = $modelPostulante->getPostulante($idPostulante);
            
            if ($res) {
                
                  $okNombres = (!empty($res['nombres']));
                  $okApellidoPaterno = (!empty($res['apellido_paterno']));
                  $okApellidoMaterno = (!empty($res['apellido_materno']));
                  $okFechaNac = (!empty($res['fecha_nac']));
                  $okTipoDoc = (!empty($res['tipo_doc']));
                  $okNumDoc = (!empty($res['num_doc']));
                  $okSexoMF = (!empty($res['sexoMF']));
                  $okTelefono = (!empty($res['telefono']));
                  $okCelular = (!empty($res['celular']));
                  $okEstadoCivil = (!empty($res['estado_civil']));
                  $okPathFotoDos = (!empty($res['path_foto_dos']));
                  
                  $ok = (
                    $okNombres && $okApellidoPaterno && $okApellidoMaterno && 
                    $okFechaNac && $okTipoDoc && $okNumDoc && $okSexoMF && 
                    $okTelefono && $okCelular && $okEstadoCivil && 
                    $okPathFotoDos 
                  );
            }
            
            
        }
        return $ok;
        
    }
    
    
    
    protected function tieneUbicacionCompleta($idPostulante)
    {
        $ok = false;
        if ($idPostulante) {
            $modelPostulante = new Application_Model_Postulante();
            $res = $modelPostulante->getPostulante($idPostulante);
            
            if ($res) {
                
                  $okPaisResidencia = (!empty($res['pais_residencia']));
                  $okDisponibilidad = (!empty($res['disponibilidad_provincia_extranjero']));
                  $okPresentacion = (!empty($res['presentacion']));
                  $okFacebook = (!empty($res['facebook']));
                  $okTwitter = (!empty($res['twitter']));                  
                  
                  $ok = ( $okPaisResidencia && $okDisponibilidad && 
                    $okPresentacion && $okFacebook && $okTwitter 
                  );
            }
            
            
        }
        return $ok;
        
    }
    
    
    public function getPorcentageMongo($arrayPostulante, $separados = false)
    {
        $datosPorcentaje = array();
        $mongoPorcentaje = new Mongo_PorcentajePostulante();
        $result =  $mongoPorcentaje->getPorcentaje($arrayPostulante['idpostulante']);        
        
        if ($result->count() < 0) {
            $result->rewind();
            $aRes = $result->current();
            $datosPorcentaje = array(
                'porcentaje' => $aRes['porcentaje'],
                'idPostulante' => $aRes['idPostulante']
            );            
        } else {
            $datosPorcentaje = $this->getPorcentajesFromPostulanteFromMySQL($arrayPostulante, true);
            $datosPorcentaje['idPostulante'] = $arrayPostulante['idpostulante'];            
            $mongoPorcentaje->savePorcentaje($datosPorcentaje);
        }
               
        $total_completado = 0;
        $pesos = $this->config->dashboard->peso->toArray();        
        $nomPesos = $this->config->dashboard->peso->title->toArray();
        
        $datosAdicionales = array(
            'sugeridos' => array(
                'completed' => false,
                'url' => SITE_URL.'/registro/paso2',
                'title' => 'Cambiar Sugerencias',
                'icon' => 'icon_reload',
                'action' => 'avisos-sugeridos'
            ),
            'perfil' => array(
                'completed' => false,
                'url' => SITE_URL.'/mi-cuenta/mis-datos-personales',
                'title' => 'Perfil',
                'icon' => 'icon_person2',
                'action' => 'mis-datos-personales'
            ),
            'ubicacion' => array(
                'completed' => false,
                'url' => SITE_URL.'/mi-cuenta/mi-ubicacion',
                'title' => 'Ubicación',
                'icon' => 'icon_arrow',
                'action' => 'mi-ubicacion'
            ),
            'experiencia' => array(
                'completed' => false,
                'url' => SITE_URL.'/mi-cuenta/mis-experiencias',
                'title' => 'Experiencia',
                'icon' => 'icon_tie',
                'action' => 'mis-experiencias'
            ),
            'estudios' => array(
                'completed' => false,
                'url' => SITE_URL.'/mi-cuenta/mis-estudios',
                'title' => 'Estudios',
                'icon' => 'icon_education',
                'action' => 'mis-estudios'
            ),
            'idiomas' => array(
                'completed' => false,
                'url' => SITE_URL.'/mi-cuenta/mis-idiomas',
                'title' => 'Idiomas',
                'icon' => 'icon_speak',
                'action' => 'mis-idiomas'
            ),
            'programas' => array(
                'completed' => false,
                'url' => SITE_URL.'/mi-cuenta/mis-programas',
                'title' => 'Informática',
                'icon' => 'icon_mouse',
                'action' => 'mis-programas'
            ),
            'otrosestudios' => array(
                'completed' => false,
                'url' => SITE_URL.'/mi-cuenta/mis-otros-estudios',
                'title' => 'Otros Estudios',
                'icon' => 'icon_books',
                'action' => 'mis-otros-estudios'
            ),
            'logros' => array(
                'completed' => false,
                'url' => SITE_URL.'/mi-cuenta/mis-logros',
                'title' => 'Logros',
                'icon' => 'icon_medal',
                'action' => 'mis-logros'
            ),
        );
        
        foreach ($datosPorcentaje['porcentaje'] as $item) {
            $total_completado += $item['total'];
        }
        
        $total_incompleto = array();        
        $porcentajeAdicionales = array();
        foreach ($datosPorcentaje['porcentaje'] as $key => $value) {
            $completado = false;
            if (array_key_exists($key, $pesos)) {
                if ((int)$value['total'] <= 0) {
                    $total_incompleto[] = array(
                        'item' => $nomPesos[$key],
                        'porcentaje' => $pesos[$key]
                    );
                } else {
                    $completado = true;
                }
                
                if ($separados) {
                    $datosAdicionales[$key]['total']  = $value['total'];
                    $datosAdicionales[$key]['completed'] = $completado;            
                    $porcentajeAdicionales[$key] = $datosAdicionales[$key];
                }
                

            }
            
        }
        
        $porcentajePostulante['total_completado'] = $total_completado;
        $porcentajePostulante['total_incompleto'] = $total_incompleto;
        
        if ($separados) {
            $porcentajePostulante['porcentaje'] = $porcentajeAdicionales;
            $porcentajePostulante['postulante'] = array(
                'idpostulante' => $this->auth['postulante']['id'],
                'id_ubigeo' => $this->auth['postulante']['id_ubigeo'],
                'twitter' => $this->auth['postulante']['twitter'],
                'facebook' => $this->auth['postulante']['facebook'],
                'nombres' => $this->auth['postulante']['nombres'],
                'apellido_paterno' => $this->auth['postulante']['apellido_paterno'],
                'apellido_materno' => $this->auth['postulante']['apellido_materno'],
                'path_foto' => $this->auth['postulante']['path_foto'],
                'fecha_nac' => $this->auth['postulante']['fecha_nac'],
                'num_doc' => $this->auth['postulante']['num_doc'],
                'sexoMF' => $this->auth['postulante']['sexo'],
                'telefono' => $this->auth['postulante']['telefono'],
                'estado_civil' => $this->auth['postulante']['estado_civil'],
            );
            $solr = new Solr_SolrSugerencia();
            $resul = $solr->getListadoAvisosSugeridos(array(
                'id_postulante' => $arrayPostulante['idpostulante']
            ));
            
            $resultSugerencias = array('total' => $resul['ntotal']);   
            $porcentajePostulante['sugerencias'] = $resultSugerencias;
            
        }
        
        return $porcentajePostulante;
    }
    
    
    public function getPorcentajesFromPostulanteFromMySQL($arrayPostulante, $separados = false)
    {
        $modelLogPostulante = new Application_Model_LogPostulante();
        $Experiancia= new Application_Model_Experiencia();
        $Estudio= new Application_Model_Estudio();
        $Idioma= new Application_Model_DominioIdioma();
        $Programa= new Application_Model_DominioProgramaComputo();        
        $OtrosEstudios= new Application_Model_Estudio();
        $logs = $modelLogPostulante->getLogPostulanteDatos($arrayPostulante['idpostulante']);
        
        
        //$dynAreasInteres = new Amazon_Dynamo_ParamSugeridosPostulante();
        $dynAreasInteres = new Mongo_ParamSugeridosPostulante();
        $getAreaInteres = $dynAreasInteres->getDatos($arrayPostulante['idpostulante']);  
        
        $porcentajeExperiencia = 0;
        $porcentajeEstudios = 0;
        $porcentajeIdiomas = 0;
        $porcentajeProgramas = 0;
        $porcentajeUbicacion = 0;
        $porcentajeLogros = 0;
        $porcentajeHobbies = 0;
        $porcentajeOtrosestudios=0;
        $porcentajeSugeridos = 0;
        $porcentajePerfil = 0;
        $porcentajeOtrosEstudios = 0;                
        $res = array();
        
       
        foreach ($logs as $item) {
            
            switch ($item['campo_modificado']) {
                case 'experiencia':  
                    if($item['acumulado']==$item['porcentaje'])  {
                        $porcentajeExperiencia += ($item['acumulado']) ;   
                    }  elseif ($item['acumulado']>$item['porcentaje']) {
                        $porcentajeExperiencia+=$this->config->dashboard->peso->experiencia;                        
                    }  elseif ($item['acumulado']==0  && $Experiancia->getLogPostulanteExperianciaTotal($arrayPostulante['idpostulante'])) {
                        $porcentajeExperiencia+=$this->config->dashboard->peso->experiencia;
                    }
                       
                                     
                    break;
                case 'estudios':  
                     if($item['acumulado']==$item['porcentaje']) {
                        $porcentajeEstudios += ($item['acumulado']) ;
                     } elseif ($item['acumulado']>$item['porcentaje']) {
                         $porcentajeEstudios += ($this->config->dashboard->peso->estudios) ;
                     } 
                     elseif ($item['acumulado']==0  && $Estudio->getLogPostulanteEstudioTotal($arrayPostulante['idpostulante'])) {
                        $porcentajeEstudios+=$this->config->dashboard->peso->estudios;
                     } 
                     else {
                         $porcentajeEstudios+=$this->config->dashboard->peso->estudios;
                    }
                   
                    break;
                case 'idiomas':  
                     if($item['acumulado']==$item['porcentaje']) {
                          $porcentajeIdiomas += ($item['acumulado']) ;  
                     }  elseif ($item['acumulado']>$item['porcentaje']) {
                          $porcentajeIdiomas += ($this->config->dashboard->peso->idiomas) ;
                     }  elseif ($item['acumulado']==0  &&  
                             $Idioma->getLogPostulanteIdiomaTotal($arrayPostulante['idpostulante'])) {
                          $porcentajeIdiomas += ($this->config->dashboard->peso->idiomas) ;
                     }  else {
                          $porcentajeIdiomas += ($this->config->dashboard->peso->idiomas) ;
                     }
                    break;
                case 'programas':  
                     if ($item['acumulado']==$item['porcentaje']) {
                         $porcentajeProgramas += ($item['acumulado']) ;  
                     }  elseif ($item['acumulado']>$item['porcentaje']) {
                         $porcentajeProgramas += ($this->config->dashboard->peso->programas) ;
                     }  elseif ($item['acumulado']==0 && 
                             $Programa->getLogPostulanteProgramaTotal($arrayPostulante['idpostulante'])) {
                         $porcentajeProgramas += ($this->config->dashboard->peso->programas) ; 
                     }  else
                         $porcentajeProgramas += ($item['acumulado']) ;
                    
                    break;
                 case 'otroestudio':  
                     if ($item['acumulado']==$item['porcentaje']) {
                         $porcentajeOtrosEstudios+= ($item['acumulado']) ;
                     } elseif ($item['acumulado']>$item['porcentaje']) {
                         $porcentajeOtrosEstudios+= ($this->config->dashboard->peso->otrosestudios) ;
                     } elseif ($item['acumulado']==0 && 
                             $OtrosEstudios->getLogPostulanteOtroEstudioTotal($arrayPostulante['idpostulante'])) {
                          $porcentajeOtrosEstudios+= ($this->config->dashboard->peso->otrosestudios) ;
                     } else {
                          $porcentajeOtrosEstudios+= ($this->config->dashboard->peso->otrosestudios) ;
                     }
                    
                    break;
                case 'ubicacion':
                     if ($item['acumulado']>$item['porcentaje']) {
                         $porcentajeUbicacion+= ($this->config->dashboard->peso->ubicacion) ;
                     }  else {
                         $porcentajeUbicacion+= ($item['acumulado']) ;
                     }                   
                    break;
                case 'logros':  
                     if ($item['acumulado']>$item['porcentaje']) {
                         $porcentajeLogros+= ($this->config->dashboard->peso->logros) ;
                     }  else {
                         $porcentajeLogros+= ($item['acumulado']) ;
                     }
                    break;
//                case 'hobbies':  
//                    $porcentajeHobbies += ($item['porcentaje']) ;
//                    break;
                /*case 'sugeridos':
                    if ($item['acumulado']>$item['porcentaje']) {
                         $porcentajeSugeridos+= ($this->config->dashboard->peso->sugeridos) ;
                     } elseif ($item['acumulado']==0 &&
                        count($getAreaInteres)>10) {
                        $porcentajeSugeridos+= ($this->config->dashboard->peso->sugeridos) ;
                    }  else {
                         $porcentajeSugeridos+= ($item['acumulado']) ;
                     }
                    break;*/
                case 'datospersonales':  
                    if ($item['acumulado']>$item['porcentaje']) {
                         $porcentajePerfil+= ($this->config->dashboard->peso->perfil) ;
                     }  else {
                         $porcentajePerfil+= ($item['acumulado']) ;
                     }
                    break;
              
               
            }
        }
        if($porcentajeSugeridos==0){            
            if(count($getAreaInteres)>=10){
                $porcentajeSugeridos+=$this->config->dashboard->peso->sugeridos;
            }
        }
    
        if($porcentajeUbicacion==0){
            if(!empty($arrayPostulante['id_ubigeo'])  &&  !empty($arrayPostulante['twitter']) &&   !empty($arrayPostulante['facebook'])){
                $porcentajeUbicacion+= ($this->config->dashboard->peso->ubicacion) ;
            }
        }
       
        if($porcentajePerfil==0){
             if (!empty($arrayPostulante['nombres']) &&  !empty($arrayPostulante['apellido_paterno']) && 
                !empty($arrayPostulante['apellido_materno']) && !empty($arrayPostulante['path_foto'])&&
                !empty($arrayPostulante['fecha_nac']) &&
                !empty($arrayPostulante['num_doc'])  && !empty($arrayPostulante['sexoMF']) &&
                !empty($arrayPostulante['telefono'])  &&  !empty($arrayPostulante['estado_civil'])) {
                 $porcentajePerfil += ($this->config->dashboard->peso->perfil) ;
             }
        }
        if(!$porcentajeEstudios){
            if($Estudio->getLogPostulanteEstudioTotal($arrayPostulante['idpostulante'])){
               $porcentajeEstudios+=$this->config->dashboard->peso->estudios; 
            }
            
        }       
        
        if(!$porcentajeExperiencia){
            if( $Experiancia->getLogPostulanteExperianciaTotal($arrayPostulante['idpostulante'])){
               $porcentajeExperiencia+=$this->config->dashboard->peso->experiencia;
            }            
        }
        if(!$porcentajeProgramas){
            if(  $Programa->getLogPostulanteProgramaTotal($arrayPostulante['idpostulante'])){
               $porcentajeProgramas+=$this->config->dashboard->peso->programas;
            }            
        }
        if(!$porcentajeOtrosEstudios){
            if( $OtrosEstudios->getLogPostulanteOtroEstudioTotal($arrayPostulante['idpostulante'])){
              $porcentajeOtrosEstudios+= ($this->config->dashboard->peso->otrosestudios) ;
            }            
        }
        if(!$porcentajeIdiomas){
            if(   $Idioma->getLogPostulanteIdiomaTotal($arrayPostulante['idpostulante'])){
             $porcentajeIdiomas += ($this->config->dashboard->peso->idiomas) ;
            }            
        }
       
        
        if (true === $separados) {
            $res['porcentaje'] = array(
                'sugeridos' => array(
                    'total' => $porcentajeSugeridos,                    
                ),
                'perfil' => array(
                    'total' => $porcentajePerfil,                    
                ),
                'ubicacion' => array(
                    'total' => $porcentajeUbicacion,                    
                ),
                'experiencia' => array(
                    'total' => $porcentajeExperiencia,
                ),
                'estudios' => array(
                    'total' => $porcentajeEstudios,                    
                ),
                'otrosestudios' => array(
                    'total' => $porcentajeOtrosEstudios,                    
                ),
                'idiomas' => array(
                    'total' => $porcentajeIdiomas,                    
                ),
                'programas' => array(
                    'total' => $porcentajeProgramas,                    
                ),
                'logros' => array(
                    'total' => $porcentajeLogros,
                )
//                ),
//                'hobbies' => array(
//                    'total' => $porcentajeHobbies,
//                    'completed' => ($porcentajeHobbies > 0) ? true : false,
//                    'url' => SITE_URL.'/mi-cuenta/mis-hobbies',
//                    'title' => 'Hobbies',
//                    'icon' => 'icon_guitar',
//                    'action' => 'mis-hobbies'
//                ),

            );
            
                                        
        
        }
        
        return $res;
        
        
    }
    
    

    public function getPorcentajesFromPostulante($arrayPostulante, $separados = false,$totalSugerencia=false)
    {
        $modelLogPostulante = new Application_Model_LogPostulante();
        $Experiancia= new Application_Model_Experiencia();
        $Estudio= new Application_Model_Estudio();
        $Idioma= new Application_Model_DominioIdioma();
        $Programa= new Application_Model_DominioProgramaComputo();        
        $OtrosEstudios= new Application_Model_Estudio();
        $logs = $modelLogPostulante->getLogPostulanteDatos($arrayPostulante['idpostulante']);
        $params['id_postulante']= $arrayPostulante['idpostulante'];
        $resultSugerencias=array('ntotal'=>0);
        if(!$totalSugerencia){
            $solr = new Solr_SolrSugerencia();
            $resul=$solr->getListadoAvisosSugeridos($params);
            $resultSugerencias = array(
                'ntotal'=>$resul['ntotal']
                    );
        }

        //$dynAreasInteres = new Amazon_Dynamo_ParamSugeridosPostulante();        
        $dynAreasInteres = new Mongo_ParamSugeridosPostulante();
        $getAreaInteres = $dynAreasInteres->getDatos($arrayPostulante['idpostulante']);

        $porcentajeExperiencia = 0;
        $porcentajeEstudios = 0;
        $porcentajeIdiomas = 0;
        $porcentajeProgramas = 0;
        $porcentajeUbicacion = 0;
        $porcentajeLogros = 0;
        $porcentajeHobbies = 0;
        $porcentajeOtrosestudios=0;
        $porcentajeSugeridos = 0;
        $porcentajePerfil = 0;
        $porcentajeOtrosEstudios = 0;        
        $incompletos = array();        
        $res = array();
        
       
        foreach ($logs as $item) {
            
            switch ($item['campo_modificado']) {
                case 'experiencia':  
                    if($item['acumulado']==$item['porcentaje'])  {
                        $porcentajeExperiencia += ($item['acumulado']) ;   
                    }  elseif ($item['acumulado']>$item['porcentaje']) {
                        $porcentajeExperiencia+=$this->config->dashboard->peso->experiencia;                        
                    }  elseif ($item['acumulado']==0  && $Experiancia->getLogPostulanteExperianciaTotal($arrayPostulante['idpostulante'])) {
                        $porcentajeExperiencia+=$this->config->dashboard->peso->experiencia;
                    }
                       
                                     
                    break;
                case 'estudios':  
                     if($item['acumulado']==$item['porcentaje']) {
                        $porcentajeEstudios += ($item['acumulado']) ;
                     } elseif ($item['acumulado']>$item['porcentaje']) {
                         $porcentajeEstudios += ($this->config->dashboard->peso->estudios) ;
                     } 
                     elseif ($item['acumulado']==0  && $Estudio->getLogPostulanteEstudioTotal($arrayPostulante['idpostulante'])) {
                        $porcentajeEstudios+=$this->config->dashboard->peso->estudios;
                     } 
                     else {
                         $porcentajeEstudios+=$this->config->dashboard->peso->estudios;
                    }
                   
                    break;
                case 'idiomas':  
                     if($item['acumulado']==$item['porcentaje']) {
                          $porcentajeIdiomas += ($item['acumulado']) ;  
                     }  elseif ($item['acumulado']>$item['porcentaje']) {
                          $porcentajeIdiomas += ($this->config->dashboard->peso->idiomas) ;
                     }  elseif ($item['acumulado']==0  &&  
                             $Idioma->getLogPostulanteIdiomaTotal($arrayPostulante['idpostulante'])) {
                          $porcentajeIdiomas += ($this->config->dashboard->peso->idiomas) ;
                     }  else {
                          $porcentajeIdiomas += ($this->config->dashboard->peso->idiomas) ;
                     }
                    break;
                case 'programas':  
                     if ($item['acumulado']==$item['porcentaje']) {
                         $porcentajeProgramas += ($item['acumulado']) ;  
                     }  elseif ($item['acumulado']>$item['porcentaje']) {
                         $porcentajeProgramas += ($this->config->dashboard->peso->programas) ;
                     }  elseif ($item['acumulado']==0 && 
                             $Programa->getLogPostulanteProgramaTotal($arrayPostulante['idpostulante'])) {
                         $porcentajeProgramas += ($this->config->dashboard->peso->programas) ; 
                     }  else
                         $porcentajeProgramas += ($item['acumulado']) ;
                    
                    break;
                 case 'otroestudio':  
                     if ($item['acumulado']==$item['porcentaje']) {
                         $porcentajeOtrosEstudios+= ($item['acumulado']) ;
                     } elseif ($item['acumulado']>$item['porcentaje']) {
                         $porcentajeOtrosEstudios+= ($this->config->dashboard->peso->otrosestudios) ;
                     } elseif ($item['acumulado']==0 && 
                             $OtrosEstudios->getLogPostulanteOtroEstudioTotal($arrayPostulante['idpostulante'])) {
                          $porcentajeOtrosEstudios+= ($this->config->dashboard->peso->otrosestudios) ;
                     } else {
                          $porcentajeOtrosEstudios+= ($this->config->dashboard->peso->otrosestudios) ;
                     }
                    
                    break;
                case 'ubicacion':
                     if ($item['acumulado']>$item['porcentaje']) {
                         $porcentajeUbicacion+= ($this->config->dashboard->peso->ubicacion) ;
                     }  else {
                         $porcentajeUbicacion+= ($item['acumulado']) ;
                     }
                    break;
                case 'logros':  
                     if ($item['acumulado']>$item['porcentaje']) {
                         $porcentajeLogros+= ($this->config->dashboard->peso->logros) ;
                     }  else {
                         $porcentajeLogros+= ($item['acumulado']) ;
                     }
                    break;
//                case 'hobbies':  
//                    $porcentajeHobbies += ($item['porcentaje']) ;
//                    break;
                /*case 'sugeridos':
                    if ($item['acumulado']>$item['porcentaje']) {
                         $porcentajeSugeridos+= ($this->config->dashboard->peso->sugeridos) ;
                     } elseif ($item['acumulado']==0 &&
                        count($getAreaInteres)>10) {
                        $porcentajeSugeridos+= ($this->config->dashboard->peso->sugeridos) ;
                    }  else {
                         $porcentajeSugeridos+= ($item['acumulado']) ;
                     }
                    break;*/
                case 'datospersonales':  
                    if ($item['acumulado']>$item['porcentaje']) {
                         $porcentajePerfil+= ($this->config->dashboard->peso->perfil) ;
                     }  else {
                         $porcentajePerfil+= ($item['acumulado']) ;
                     }
                    break;
              
               
            }
        }
        if($porcentajeSugeridos==0){
            if(count($getAreaInteres)>0){
                if(isset($getAreaInteres["area_nivel"])){
                    $porcentajeSugeridos+=$this->config->dashboard->peso->sugeridos;
                }

            }
        }
    
        if($porcentajeUbicacion==0){
            if(!empty($arrayPostulante['id_ubigeo'])    /* && !empty($arrayPostulante['twitter']) &&   !empty($arrayPostulante['facebook']) */){
                $porcentajeUbicacion+= ($this->config->dashboard->peso->ubicacion) ;
            }
        }

        if($porcentajePerfil==0){
             if (!empty($arrayPostulante['nombres']) &&  !empty($arrayPostulante['apellido_paterno']) && 
                !empty($arrayPostulante['apellido_materno']) &&
                 //!empty($arrayPostulante['path_foto'])&&
                !empty($arrayPostulante['fecha_nac']) &&
                !empty($arrayPostulante['num_doc'])  && !empty($arrayPostulante['sexoMF']) &&
                !empty($arrayPostulante['telefono'])  &&  !empty($arrayPostulante['estado_civil'])) {
                 $porcentajePerfil += ($this->config->dashboard->peso->perfil) ;
             }
        }
        
        if(!$porcentajeEstudios){
            if($Estudio->getLogPostulanteEstudioTotal($arrayPostulante['idpostulante'])){
               $porcentajeEstudios+=$this->config->dashboard->peso->estudios; 
            }
            
        }       
        
        if(!$porcentajeExperiencia){
            if( $Experiancia->getLogPostulanteExperianciaTotal($arrayPostulante['idpostulante'])){
               $porcentajeExperiencia+=$this->config->dashboard->peso->experiencia;
            }            
        }
        if(!$porcentajeProgramas){
            if(  $Programa->getLogPostulanteProgramaTotal($arrayPostulante['idpostulante'])){
               $porcentajeProgramas+=$this->config->dashboard->peso->programas;
            }            
        }
        if(!$porcentajeOtrosEstudios){
            if( $OtrosEstudios->getLogPostulanteOtroEstudioTotal($arrayPostulante['idpostulante'])){
              $porcentajeOtrosEstudios+= ($this->config->dashboard->peso->otrosestudios) ;
            }            
        }
        if(!$porcentajeIdiomas){
            if(   $Idioma->getLogPostulanteIdiomaTotal($arrayPostulante['idpostulante'])){
             $porcentajeIdiomas += ($this->config->dashboard->peso->idiomas) ;
            }            
        }
       


        if ($porcentajeExperiencia <= 0) {
            $incompletos[2]['item'] = $this->config->dashboard->sug->experiencia;
            $incompletos[2]['porcentaje'] =  $this->config->dashboard->peso->experiencia;
            $incompletos[2]['link'] = SITE_URL.'/mi-cuenta/mis-experiencias';
        }
        
        if ($porcentajeOtrosEstudios <= 0) {
            $incompletos[1]['item'] = $this->config->dashboard->sug->otrosestudios;
            $incompletos[1]['porcentaje'] = $this->config->dashboard->peso->otrosestudios;
            $incompletos[1]['link'] = SITE_URL.'/mi-cuenta/mis-otros-estudios';
        }   
      
        
        if ($porcentajeEstudios <= 0) {
            $incompletos[3]['item'] = $this->config->dashboard->sug->estudios;
            $incompletos[3]['porcentaje'] =  $this->config->dashboard->peso->estudios;
            $incompletos[3]['link'] = SITE_URL.'/mi-cuenta/mis-estudios';
        }
        
        if ($porcentajeIdiomas <= 0) {
            $incompletos[4]['item'] = $this->config->dashboard->sug->idiomas;
            $incompletos[4]['porcentaje'] =  $this->config->dashboard->peso->idiomas;
            $incompletos[4]['link'] = SITE_URL.'/mi-cuenta/mis-idiomas';
        }
        
        if ($porcentajeProgramas <= 0) {
            $incompletos[5]['item'] = $this->config->dashboard->sug->programas;
            $incompletos[5]['porcentaje'] =  $this->config->dashboard->peso->programas;
            $incompletos[5]['link'] = SITE_URL.'/mi-cuenta/mis-programas';
        }
        
        if ($porcentajeUbicacion <= 0) {
            $incompletos[6]['item'] = $this->config->dashboard->sug->ubicacion;
            $incompletos[6]['porcentaje'] =  $this->config->dashboard->peso->ubicacion;
            $incompletos[6]['link'] = SITE_URL.'/mi-cuenta/mi-ubicacion';
        }
        
        if ($porcentajeLogros <= 0) {
            $incompletos[7]['item'] = $this->config->dashboard->sug->logros;
            $incompletos[7]['porcentaje'] =  $this->config->dashboard->peso->logros;
            $incompletos[7]['link'] = SITE_URL.'/mi-cuenta/mis-logros';
        }
        
//        if ($porcentajeHobbies <= 0) {
//            $incompletos[8]['item'] = $this->config->dashboard->sug->hobbies;
//            $incompletos[8]['porcentaje'] =  $this->config->dashboard->peso->hobbies;
//        }
        
        if ($porcentajePerfil <= 0) {
            $incompletos[9]['item'] = $this->config->dashboard->sug->perfil;
            $incompletos[9]['porcentaje'] =  $this->config->dashboard->peso->perfil;
            $incompletos[9]['link'] = SITE_URL.'/mi-cuenta/mis-datos-personales';
        }
        
        if ($porcentajeSugeridos <= 0) {
            $incompletos[10]['item'] =$this->config->dashboard->sug->sugerencias;
            $incompletos[10]['porcentaje'] =  $this->config->dashboard->peso->sugeridos;
            $incompletos[10]['link'] = SITE_URL.'/registro/paso2';
        }
        $res['total_completado'] = ($porcentajeExperiencia + 
                $porcentajeEstudios + $porcentajeIdiomas + 
                $porcentajeProgramas + $porcentajeUbicacion +
                $porcentajeLogros +  $porcentajeSugeridos +
                $porcentajePerfil + $porcentajeOtrosEstudios
        );
        $res['total_incompleto'] = $incompletos;
        if (true === $separados) {
            $res['porcentaje'] = array(
                'sugerencias' => array(
                    'total' => $this->config->dashboard->peso->sugeridos,
                    'completed' => ($porcentajeSugeridos > 0) ? true : false,
                    'url' => SITE_URL.'/registro/paso2',
                    'title' => 'Cambiar Sugerencias',
                    'icon' => 'icon_reload',
                    'action' => 'avisos-sugeridos'
                ),
                'perfil' => array(
                    'total' => $this->config->dashboard->peso->perfil,
                    'completed' => ($porcentajePerfil > 0) ? true : false,
                    'url' => SITE_URL.'/mi-cuenta/mis-datos-personales',
                    'title' => 'Datos Personales',
                    'icon' => 'icon_person2',
                    'action' => 'mis-datos-personales'
                ),
                'ubicacion' => array(
                    'total' => $this->config->dashboard->peso->ubicacion,
                    'completed' => ($porcentajeUbicacion > 0) ? true : false,
                    'url' => SITE_URL.'/mi-cuenta/mi-ubicacion',
                    'title' => 'Ubicación',
                    'icon' => 'icon_arrow',
                    'action' => 'mi-ubicacion'
                ),
                'experiencia' => array(
                    'total' => $this->config->dashboard->peso->experiencia,
                    'completed' => ($porcentajeExperiencia > 0) ? true : false,
                    'url' => SITE_URL.'/mi-cuenta/mis-experiencias',
                    'title' => 'Experiencia',
                    'icon' => 'icon_tie',
                    'action' => 'mis-experiencias'
                ),
                'estudios' => array(
                    'total' => $this->config->dashboard->peso->estudios,
                    'completed' => ($porcentajeEstudios > 0) ? true : false,
                    'url' => SITE_URL.'/mi-cuenta/mis-estudios',
                    'title' => 'Estudios',
                    'icon' => 'icon_education',
                    'action' => 'mis-estudios'
                ),
                'otrosestudios' => array(
                    'total' => $this->config->dashboard->peso->otrosestudios,
                    'completed' => ($porcentajeOtrosEstudios > 0) ? true : false,
                    'url' => SITE_URL.'/mi-cuenta/mis-otros-estudios',
                    'title' => 'Otros Estudios',
                    'icon' => 'icon_books',
                    'action' => 'mis-otros-estudios'
                ),
                'Idiomas' => array(
                    'total' => $this->config->dashboard->peso->idiomas,
                    'completed' => ($porcentajeIdiomas > 0) ? true : false,
                    'url' => SITE_URL.'/mi-cuenta/mis-idiomas',
                    'title' => 'Idiomas',
                    'icon' => 'icon_speak',
                    'action' => 'mis-idiomas'
                ),
                'Informática' => array(
                    'total' => $this->config->dashboard->peso->programas,
                    'completed' => ($porcentajeProgramas > 0) ? true : false,
                    'url' => SITE_URL.'/mi-cuenta/mis-programas',
                    'title' => 'Informática',
                    'icon' => 'icon_mouse',
                    'action' => 'mis-programas'
                ),
                'Logros' => array(
                    'total' => $this->config->dashboard->peso->logros,
                    'completed' => ($porcentajeLogros > 0) ? true : false,
                    'url' => SITE_URL.'/mi-cuenta/mis-logros',
                    'title' => 'Logros',
                    'icon' => 'icon_medal',
                    'action' => 'mis-logros'
                    ),
                'Referencias' => array(
                   // 'total' => 0,
                    //'completed' => false,
                    'url' => SITE_URL.'/mi-cuenta/mis-referencias',
                    'title' => 'Referencias',
                    'icon' => 'icon_references',
                    'action' => 'mis-Referencias'
                    )


            );
          if($porcentajeExperiencia==0){
              $res['porcentaje']['Referencias']['oculto'] =true;                       
          }
          $res['sugerencias']  = array('total' => $resultSugerencias['ntotal'] );                      
          $res['postulante'] = $arrayPostulante;
        
        }
        
        return $res;
        
        
    }
    
    
    
}
