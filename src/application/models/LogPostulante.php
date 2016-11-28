<?php

class Application_Model_LogPostulante extends App_Db_Table_Abstract
{
    protected $_name = "postulante_log_actualizacion";
    
    const DATOS_PERSONALES = 'datospersonales';
    const SUBIR_CV = 'subircv';
    const WEB = 'tuweb';
    const PRESENTACION = 'presentacion';
    const FOTO = 'foto';
    const EXPERIENCIA = 'experiencia';
    const ESTUDIOS = 'estudios';
    const IDIOMAS = 'idiomas';
    const PROGRAMAS = 'programas';
    const OTRO_ESTUDIO = 'otroestudio';
    
    const SUGERIDOS = 'sugeridos';
    const UBICACION = 'ubicacion';
    const LOGROS = 'logros';
    const HOBBIES = 'hobbies';
   
    
    public function getLogPostulanteDatosPaso1 ($idPostulante)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('pla' => $this->_name), array('pla.id', 'pla.campo_modificado'))
            ->joinInner(
                array('p'=>'postulante'), 
                $this->getAdapter()->quoteInto('p.id = pla.id_postulante AND pla.id_postulante = ?', $idPostulante),
                array()
            );
                
        return $this->getAdapter()->fetchAll($sql);
    }
    
    public function getLogPostulanteDatosPaso2 ($idPostulante, $campoModificar) 
    {
        $sql = $this->getAdapter()->select()
            ->from(array('pla' => $this->_name), array('pla.id'))
            ->joinInner(
                array('p'=>'postulante'), 
                $this->getAdapter()->quoteInto(
                    'p.id = pla.id_postulante AND pla.campo_modificado = ? ', $campoModificar
                ),
                array()
            )->where('pla.id_postulante = ?', $idPostulante);
        return $this->getAdapter()->fetchOne($sql);
    }
    
    public function getLogPostulanteDatos ($idPostulante )
    {
        if ( !is_numeric($idPostulante) ) {
            return array();
        }

        $sql = $this->getAdapter()->select()
            ->from($this->_name)
            ->where('id_postulante = ?', $idPostulante)
            ->order('fh_creacion ASC');
            
        return $this->getAdapter()->fetchAll($sql);
    }
    
      public function getLogPostulanteTipo ($idPostulante,$tipo )
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name)
            ->where('id_postulante = ?', $idPostulante)
            ->where('campo_modificado = ?',$tipo )
            ->order('id ASC');

        return $this->getAdapter()->fetchAll($sql);
    }
    
       public function getLogPostulanteTotal2 ($idPostulante )
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name,array(
                    'num' => new Zend_Db_Expr('sum(acumulado)'),      
                ))
            ->where('id_postulante = ?', $idPostulante);
            //->order('fh_creacion ASC');
//            echo $sql;exit;
        return $this->getAdapter()->fetchOne($sql);
    }
    
    public function getLogPostulanteTotal($idPostulante) {
            $Experiancia= new Application_Model_Experiencia();
            $Estudio= new Application_Model_Estudio();
            $Idioma= new Application_Model_DominioIdioma();
            $Programa= new Application_Model_DominioProgramaComputo();        
            $OtrosEstudios= new Application_Model_Estudio();
            $Logros= new Application_Model_Logros();
            $postulante= new Application_Model_Postulante();
            $dataPos= $postulante->getPostulantePerfil($idPostulante);
            //$dynAreasInteres = new Amazon_Dynamo_ParamSugeridosPostulante();
            $dynAreasInteres = new Mongo_ParamSugeridosPostulante();
            $getAreaInteres = $dynAreasInteres->getDatos($idPostulante);  
            $acump=0;
            $acumpEx=0;$acumpUbi=0;$acumpEs=0;$acumpId=0;$acumpPr=0;$acumpOEs=0;$acumpLo=0;$acumpSu=0;$acumpPer=0;
            $this->config=Zend_Registry::get('config');
            
            
             if ($Experiancia->getLogPostulanteExperianciaTotal($idPostulante)) {
                 $acump+=$this->config->dashboard->peso->experiencia;
                 $acumpEx+=$this->config->dashboard->peso->experiencia;
             }
             if ($Estudio->getLogPostulanteEstudioTotal($idPostulante)) {
                 $acump+=$this->config->dashboard->peso->estudios;
                 $acumpEs+=$this->config->dashboard->peso->estudios;
             } 
             
             if ($Idioma->getLogPostulanteIdiomaTotal($idPostulante)) {
                 $acump+= ($this->config->dashboard->peso->idiomas) ;
                 $acumpId+= ($this->config->dashboard->peso->idiomas) ;
             } 
             if ($Programa->getLogPostulanteProgramaTotal($idPostulante)) {
                $acump += ($this->config->dashboard->peso->programas) ; 
                $acumpPr += ($this->config->dashboard->peso->programas) ; 
            }
            
            if ($OtrosEstudios->getLogPostulanteOtroEstudioTotal($idPostulante)) {
               $acump += ($this->config->dashboard->peso->otrosestudios) ;
               $acumpOEs += ($this->config->dashboard->peso->otrosestudios) ;
            }
            
            if ($Logros->getLogPostulanteLogrosTotal($idPostulante)) {
               $acump += ($this->config->dashboard->peso->logros) ;
               $acumpLo += ($this->config->dashboard->peso->logros) ;
            }
            
            $count=0;                
            if(isset($getAreaInteres["area_nivel"])){
              $count++;
            }
          
            if($count>0){
                $acump+=$this->_config->dashboard->peso->sugeridos;
                $acumpSu+=$this->_config->dashboard->peso->sugeridos;
            }

             if (!empty($dataPos['id_ubigeo']) /* && !empty($dataPos['twitter']) &&   !empty($dataPos['facebook']) */) {
                $acump += ($this->config->dashboard->peso->ubicacion) ;
                $acumpUbi += ($this->config->dashboard->peso->ubicacion) ;
            }
            if (!empty($dataPos['nombres']) &&  !empty($dataPos['apellidos']) && 
                !empty($dataPos['fecha_nac']) && !empty($dataPos['num_doc'])  &&
                !empty($dataPos['sexoMF']) &&
                !empty($dataPos['fijo'])  &&
                !empty($dataPos['estado_civil']) &&
                !empty($dataPos['celular'])  
                    
                ) {
                $acump += ($this->config->dashboard->peso->perfil) ;
                $acumpPer += ($this->config->dashboard->peso->perfil) ;
            }
    

            $total=$acumpEx+$acumpEs+$acumpId+$acumpPr+$acumpOEs+$acumpLo+$acumpSu+$acumpPer+$acumpUbi;

///        var_dump($getAreaInteres,$total,$acumpEx,$acumpEs,$acumpId,$acumpPr,$acumpOEs,$acumpLo,$acumpSu,$acumpPer,$acumpUbi);exit;

        return $total;
    }
}
