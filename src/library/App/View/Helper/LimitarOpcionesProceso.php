<?php

class App_View_Helper_LimitarOpcionesProceso extends Zend_View_Helper_Abstract
{
    
    protected $_Empresa;
    protected $_tipoAviso;
    protected $_config;
    
    protected $_tooltip1 = 'Solo para avisos pagados.';
    protected $_tooltip2 = 'Solo para Empresas con Planes de Membresía.';
    protected $_tooltip_membresia_mensual= 'Solo para Empresas con Planes de Membresía a partir de digital.';

    protected $_inactive = 'inactive';
    protected $_tooltip = 'tooltip';
    protected $_tooltipRight = 'tool-right'; 
    
    const ACTIVO = 1;
    const INACTIVO = 0;

    public function LimitarOpcionesProceso($empresa,$tipoAviso)
    {
        $this->_Empresa = $empresa;
        $this->_tipoAviso = $tipoAviso;
        $this->_config = Zend_Registry::get('config');
       
        return $this;
    }
    
    /**
     * Función que retorna el tipo A,B o C
     * @return string
     */
    public function getTipoEmpresa ()
    {
        if (!is_null($this->_Empresa) )
            return 'C';
        else {
            if ($this->_tipoAviso == Application_Model_AnuncioWeb::TIPO_SOLOWEB)
                return 'A';
            else //Avisos destacados, económicos y preferenciales
                return 'B';
        }
    }

    public function showPestanasProceso()
    {
        $accesos = array();
        //Si tiene membresía
         if (!is_null($this->_Empresa)){
             $accesos = $this->_config->limitarOpciones->membresia->pes->toArray();
         } else {
             $accesos =  $this->_config->limitarOpciones->{$this->_tipoAviso}->pes->toArray();
         }
         
         foreach ($accesos as $key => $value) {
            if ($value == self::INACTIVO) {
                $accesos[$key] = $this->_inactive. ' '.$this->_tooltip;
            } else {
                $accesos[$key] = '';
            }
         }
         
         if ($this->getTipoEmpresa() == 'A')
            $accesos ['tooltip'] = "data-tool ='".$this->_tooltip1."'";
         else
            $accesos ['tooltip'] = '';
         
         return $accesos;
        
    }
    
    public function showBotonesProceso()
    {
        
        $accesos = array();
        //Si tiene membresía
         if (!is_null($this->_Empresa)){
             $accesos = $this->_config->limitarOpciones->membresia->btn->toArray();
         } else {
             $accesos = $this->_config->limitarOpciones->{$this->_tipoAviso}->btn->toArray();
         }
         
         foreach ($accesos as $key => $value) {
            if ($value == self::INACTIVO) {
                if ($key == 'moverEtapa')
                    $accesos[$key] = array('tooltip' =>$this->_inactive. ' '.$this->_tooltip,'texto' => "data-tool ='".$this->_tooltip1."'");
                else if ($key == 'acciones' || $key == 'regReferidos' || 
                        $key == 'regReferidos' || $key == 'buscarAptitus' || $key == 'listReferidos')
                    $accesos[$key] = array('tooltip' =>$this->_inactive. ' '.$this->_tooltip,'texto' => "data-tool ='".$this->_tooltip2."'");
            } else {
                $accesos[$key] = array('tooltip' => '', 'texto' => '');
            }
                
            }
            
            return $accesos;
        
    }
    
    public function showAccionesProceso()
    {
        $accesos = array();
        //Si tiene membresía
         if (!is_null($this->_Empresa) ){
             $accesos = $this->_config->limitarOpciones->membresia->col->toArray();
         } else {
             $accesos = $this->_config->limitarOpciones->{$this->_tipoAviso}->col->toArray();
         }
         
         foreach ($accesos as $key => $value) {
            if ($value == self::INACTIVO) {
                if ($key == 'moverEtapa')
                    $accesos[$key] = array('tooltip' =>$this->_inactive. ' '.$this->_tooltip,'texto' => "data-tool ='".$this->_tooltip1."'");
                else if ($key == 'acciones')
                    $accesos[$key] = array('tooltip' =>$this->_inactive. ' '.$this->_tooltip.' '.$this->_tooltipRight,'texto' => "data-tool ='".$this->_tooltip2."'");
            } else {
                $accesos[$key] = array('tooltip' => '', 'texto' => '');
            }        
        }
            
            return $accesos;
                
    }
    
    public function showFilterProceso()
    {
        $accesos = '';
        //Si tiene membresía
         if (!is_null($this->_Empresa)) 
             $accesos = $this->_config->limitarOpciones->membresia->filtros;
         else {
             $accesos = $valor = $this->_config->limitarOpciones->{$this->_tipoAviso}->filtros;
         }
         
         if ($accesos == self::INACTIVO)
            $accesos = "<div class='box-message'>".$this->_tooltip1."</div>
                    <div class='load-mask'></div>";
         else
            $accesos = '';
         
         return $accesos;
        
    }
    
    public function showExportExcel($idAviso)
    {
        //Si tiene membresía
         if (!empty($this->_Empresa) && $this->_Empresa!=1 ) {
            return "<a class='lineEmpRgt'  href=".SITE_URL."/empresa/mis-procesos/exportar-proceso/id/".$idAviso.">Exportar a Excel</a>";
         } else {
            return "<a class='lineEmpRgt'  href=".SITE_URL."/empresa/mis-procesos/exportar-proceso/id/".$idAviso.">Exportar a Excel</a>";
         }
         
        
    }
    
    public function tipoEmpresa (){
        return $this->getTipoEmpresa();
    }

        public function showBtnInvitar($idPostulante,$idPostulacion)
             
    {
                
        if ($this->getTipoEmpresa() == 'A') {
           return "<li class='left'><a class='left inactive tooltip  btn btn-option ' data-tool='$this->_tooltip1'>Invitar</a></li>";
        }
        if ($this->getTipoEmpresa() == 'B') {
           return "<li class='left'><a class='left inactive tooltip  btn btn-option ' data-tool='$this->_tooltip2'>Invitar</a></li>";
        }
        if ($this->getTipoEmpresa() == 'C') {
           return "<li class='left'><a class='winModal winInvitarProceso left  btn btn-option' rel='$idPostulante' idpostulacion='".$idPostulacion."' href='#winInvitarProceso'>Invitar</a></li>";
        }

        return null;
    }
    
     public function showMoverEtapa()
             
    {
                
        if ($this->getTipoEmpresa() == 'A') {
            return "<li class='left'><a  class=' aLinkFlechaT cntFGrisT left  btn btn-option inactive tooltip' data-tool='$this->_tooltip1'>Mover a etapa <span class='flechaGrisT upFlechaEP'></span></a></li>";
         }
        if ($this->getTipoEmpresa() == 'B') {
            return "<li id='aLinkFlechaTV' class='left'><a  class='aLinkFlechaT cntFGrisT left  btn btn-option' href='#'>Mover a etapa <span class='flechaGrisT upFlechaEP'></span></a></li>";
        }
        if ($this->getTipoEmpresa() == 'C') {
            return "<li id='aLinkFlechaTV' class='left'><a  class='aLinkFlechaT cntFGrisT left  btn btn-option' href='#'>Mover a etapa <span class='flechaGrisT upFlechaEP'></span></a></li>";
                    
            
        }

        return null;
    }
    public function showEnviarCarpeta($idPostulante,$idPostulacion)
             
    {
        if($this->_Empresa==1){
            return '';
        }
        if($this->_Empresa==11){
            return '';
        }
        if ($this->getTipoEmpresa() == 'A') {
            return "<li class='left'><a class='left noScrollTop envPostulanteABolsa  btn btn-option  inactive tooltip '  name='btnEnvABolsa' data-tool='$this->_tooltip1'>Enviar a Carpetas de CVs</a></li>";
        }
        if ($this->getTipoEmpresa() == 'B') {
            return "<li class='left'><a class='left noScrollTop envPostulanteABolsa  btn btn-option  inactive tooltip' name='btnEnvABolsa' data-tool='$this->_tooltip2'>Enviar a Carpetas de CVs</a></li>";
        }
        if ($this->getTipoEmpresa() == 'C') {
            return "<li class='left'><a class='left noScrollTop envPostulanteABolsa  btn btn-option ' id='btnEnvABolsa' name='btnEnvABolsa' rel='$idPostulante' idpostulacion='".$idPostulacion."' href=''>Enviar a Carpetas de CVs</a></li>";
        }

        return null;
    }
    public function showBloquearCandidato($idPostulante,$idPostulacion)
             
    {
                
        if ($this->getTipoEmpresa() == 'A') {
            return "<li class='left'><a class='left  btn btn-option  inactive tooltip' data-tool='$this->_tooltip1'>Bloquear Candidato</a></li>";
        }
        if ($this->getTipoEmpresa() == 'B') {
            return "<li class='left'><a class='left  btn btn-option  inactive tooltip' data-tool='$this->_tooltip2'>Bloquear Candidato</a></li>";
        }
        if ($this->getTipoEmpresa() == 'C') {
            return "<li class='left'><a href='javascript:;' rel='$idPostulante' idpostulacion='$idPostulacion' class=' btn btn-option left blcandidate'>Bloquear Candidato</a></li>";
        }

        return null;
    }
    //<a class="left winModal icoShareCP icoComp icoSpt" href="#shareMail">Compartir por E-mail</a>
    public function showEnviarEmail()             
    {                
        if ($this->getTipoEmpresa() == 'A') {
            return "<a class='left icoComp icoSpt inactive tooltip' data-tool='$this->_tooltip1' style='text-decoration:none; color:grey'>Compartir por E-mail</a>";
        }
        if ($this->getTipoEmpresa() == 'B') {
            return "<a class='left winModal icoComp icoSpt' href='#shareMail'>Compartir por E-mail</a>";
        }
        if ($this->getTipoEmpresa() == 'C') {
            return "<a class='left winModal icoComp icoSpt' href='#shareMail'>Compartir por E-mail</a>";
        }

        return null;
    }
    public function showCheckBox()
   {
        if ($this->getTipoEmpresa()=='A'){
         return"<div id='cntChecksE'  data-tool='$this->_tooltip1' class='inactive tooltip'>
    			<label class='labelEPL left'><input type='checkbox' disabled class='checkEPL noBdr'/> <span class='spanEPL'>Historia</span></label>
    			<label class='labelEPL left'><input type='checkbox' disabled class='checkEPL noBdr'/> <span class='spanEPL'>Notas</span></label>
    			<label class='labelEPL left'><input type='checkbox' disabled class='checkEPL noBdr'/> <span class='spanEPL'>Mensaje</span></label>
    		</div>";
         
        }
        if ($this->getTipoEmpresa()=='B'){
           return"<div id='cntChecksE'  data-tool='$this->_tooltip2' class='inactive tooltip'>
    			<label class='labelEPL left'><input type='checkbox' disabled class='checkEPL noBdr'/> <span class='spanEPL'>Historia</span></label>
    			<label class='labelEPL left'><input type='checkbox' disabled class='checkEPL noBdr'/> <span class='spanEPL'>Notas</span></label>
    			<label class='labelEPL left'><input type='checkbox' disabled class='checkEPL noBdr'/> <span class='spanEPL'>Mensaje</span></label>
    		</div>";
        }
        if ($this->getTipoEmpresa()=='C'){
            return"<div id='cntChecksE'>
    			<label id='primerFiltro' for='pFiltroEPL' class='labelEPL left'><input id='pFiltroEPL' type='checkbox' class='checkEPL noBdr' checked='checked' /> <span class='spanEPL'>Historia</span></label>
    			<label id='tercerFiltro' for='tFiltroEPL' class='labelEPL left'><input id='tFiltroEPL' type='checkbox' class='checkEPL noBdr' checked='checked' /> <span class='spanEPL'>Notas</span></label>
    			<label id='segundoFiltro' for='sFiltroEPL' class='labelEPL left'><input id='sFiltroEPL' type='checkbox' class='checkEPL noBdr' checked='checked' /> <span class='spanEPL'>Mensaje</span></label>
    		</div>"; 
        }
        
   }
   
    public function showAddNote()
   {
      

        if ($this->getTipoEmpresa()=='A'){
         return "<a disabled='disabled' class='left inactive tooltip' data-tool = '$this->_tooltip2' >Añadir Nota</a>
                <a href='javascript:disabled' disabled='disabled' class='right inactive tooltip' data-tool = '$this->_tooltip2' id='btnAddMsjEPL' >Añadir Mensaje</a>";
         
        }
        if ($this->getTipoEmpresa()=='B'){
           return "<a class='left inactive tooltip' data-tool = '$this->_tooltip2' >Añadir Nota</a>
                <a class='right inactive tooltip' data-tool = '$this->_tooltip2' id='btnAddMsjEPL'>Añadir Mensaje</a>";
         
        }
        if ($this->getTipoEmpresa()=='C'){
            return "<a href='#' class='left btnAddNoteE' id='btnAddNoteEPL'>Añadir Nota</a>
        		<a href='#' class='right btnAddMsjE' id='btnAddMsjEPL'>Añadir Mensaje</a>"; 
        }
        
   }
  
}
