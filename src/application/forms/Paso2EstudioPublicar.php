<?php

/**
 * Description of Form Paso2 Section Estudio
 *
 * @author Jesus
 */
class Application_Form_Paso2EstudioPublicar extends App_Form
{
    private $_maxlengthAnho = '4';
    private $_listaNiveles;
    private $_listaTipoCarrera;
    private $_listaCarreras;
    private $_nivelEstudio;
    private $_carrera;
     private $_online=false;
    public function __construct($hasHiddenId = false,$online=false)
    {
        if (isset($online) && $online == true) {
            $this->_online = true;
        }
        parent::__construct();
        if ($hasHiddenId) {
            $this->addEstudioId();
            //$this->disableFileds();
            
        }
      
    }
    
    public function init()
    {
        parent::init();
        $this->setMethod('post');
        
        $this->_nivelEstudio = new Application_Model_NivelEstudio;
        $this->_carrera = $carrera = new Application_Model_Carrera();

        //Nivel Estudios
        $this->_nivelEstudio = $nivel = new Application_Model_NivelEstudio;
        $this->_listaNiveles = $nivel->getNivelesAviso();
        $e = new Zend_Form_Element_Select('id_nivel_estudio');
        $e->addMultiOption('0', 'Seleccionar un nivel');
        $e->addMultiOptions($this->_listaNiveles);
        $e->setRequired(false);

        $nivelesSinDetalle = $nivel->getNivelesSinDetalle();
        $rel = "{disableds: [";
        foreach ($nivelesSinDetalle as $nsd) {
            $rel .= $nsd['id'].",";
        }
        $rel .= "]}";
        $e->setAttrib('rel', $rel);
        $this->addElement($e);

        //Nivel Estudios Tipo
        $e = new Zend_Form_Element_Select('id_nivel_estudio_tipo');
        $e->setRegisterInArrayValidator(false);
        $listaNivelesTipo = $nivel->getSubNiveles();
        $e->addMultiOption('0', 'Selecciona un tipo');
        $e->setRequired(false);
        //$e->addMultiOptions($listaNivelesTipo);
        $this->addElement($e);

        //Tipo de Carrera
        $tipoCarrera = new Application_Model_TipoCarrera();
        $this->_listaTipoCarrera = $tipoCarrera->getTiposCarreras();
        $e = new Zend_Form_Element_Select('id_tipo_carrera');
        $e->addMultiOption('0', 'Selecciona tipo de carrera');
        $e->addMultiOptions($this->_listaTipoCarrera);
        $e->setRequired(false);
        $this->addElement($e);

        //Carrera
        $this->_carrera = $carrera = new Application_Model_Carrera();
        $this->_listaCarreras = $carrera->getCarreras();
        $e = new Zend_Form_Element_Select('id_carrera');
        $e->addMultiOption('0', 'Seleccionar carrera');
        $e->setRequired(false);
        //$e->addMultiOptions($this->_listaCarreras);
        $this->addElement($e);
        // Texto para la carrera
        $e = new Zend_Form_Element_Text('otra_carrera');
        
        $e->setAttrib('maxLength', $this->_maxlengthInstitucion);
       //$e->setAttrib('readonly', 'true');
        $e->setRequired(false);
        $this->addElement($e);
        
        //Bloque los combos si el aviso estan online 
        if (isset($this->_online) && $this->_online == true) {
            $this->disableFileds();
        }
        
    }
    
    public function disableFileds(){
//        $this->id_tipo_carrera->setAttrib('disabled', 'disabled');
//        $this->id_carrera->setAttrib('disabled', 'disabled');
//       $this->id_nivel_estudio_tipo->setAttrib('disabled', 'disabled');
//        $this->id_nivel_estudio->setAttrib('disabled', 'disabled');
        
    }
    
    public function isValid($data)
    {  
       
    if ($this->_online == true) {
            // @codingStandardsIgnoreStart
        

            $this->id_tipo_carrera->clearValidators();
            $this->id_tipo_carrera->setRequired(false);
            $this->id_carrera->clearValidators();
            $this->id_carrera->setRequired(false);
            $this->id_nivel_estudio_tipo->clearValidators();
            $this->id_nivel_estudio_tipo->setRequired(false);
            $this->id_nivel_estudio->clearValidators();
            $this->id_nivel_estudio->setRequired(false); 
            $valid=true;
            // @codingStandardsIgnoreEnd
        }else{  
        
        if ($data['id_nivel_estudio'] != 0 && (isset($data['id_carrera']) && $data['id_carrera'] != 0)) {
            // @codingStandardsIgnoreStart
           $this->id_nivel_estudio->addValidator(
                new Zend_Validate_InArray(array_keys($this->_listaNiveles))
            );
            $this->id_nivel_estudio->errMsg = 
                "Campo Requerido";
            
              $this->id_tipo_carrera->addValidator(
                    new Zend_Validate_InArray(
                        array_keys($this->_listaTipoCarrera)
                    )
                );
                $this->id_tipo_carrera->errMsg = "Campo Requerido";
            // @codingStandardsIgnoreEnd
        }
      
        if (
            $data['id_nivel_estudio'] != 0 && 
            $data['id_nivel_estudio'] != 1 && 
            $data['id_nivel_estudio'] != 2 && 
            $data['id_nivel_estudio'] != 3
            ) {
                $this->id_tipo_carrera->addValidator(
                    new Zend_Validate_InArray(
                        array_keys($this->_listaTipoCarrera)
                    )
                );
                $this->id_tipo_carrera->errMsg = "Campo Requerido";
                
                $carrera = new Application_Model_Carrera();
                
                if (isset($data['id_tipo_carrera']))
                    $listaCarreras = $carrera->filtrarCarrera($data['id_tipo_carrera']);
                else
                    $listaCarreras = $carrera->getCarreras();
                
                // @codingStandardsIgnoreStart
                $this->id_carrera->addMultiOptions($listaCarreras);
                $this->id_carrera->addValidator(
                    new Zend_Validate_InArray(array_keys($listaCarreras))
                );
                $this->id_carrera->errMsg = "Campo Requerido";
                $nivel = new Application_Model_NivelEstudio;
                $listaNivelesTipo = $nivel->getSubNiveles($data['id_nivel_estudio']);
                $this->id_nivel_estudio_tipo->setRequired();
                $this->id_nivel_estudio_tipo->addValidator(
                        new Zend_Validate_InArray(
                        array_keys($listaNivelesTipo)
                        )
                );
                $this->id_nivel_estudio_tipo->errMsg = "Campo Requerido";
                // @codingStandardsIgnoreEnd
                    if (isset($data['id_carrera'])) {
                        if ($data['id_carrera'] == Application_Model_Carrera::OTRO_CARRERA) {
                            $v = $this->otra_carrera->addValidator(
                                new Zend_Validate_StringLength(
                                array(
                            'min' => '2', 'max' => $this->_maxlengthInstitucion,
                            'encoding' => $this->_config->resources->view->charset
                                )
                                )
                        );

                        $this->otra_carrera->setRequired();
                        $this->otra_carrera->errMsg = "Debe ingresar el texto";
                        }
                    }
            
        }
      
      /*if($data['id_nivel_estudio']==0 ||  (isset($data['id_nivel_estudio_tipo']) && $data['id_nivel_estudio_tipo'] == 0) || 
           (isset($data['id_tipo_carrera']) && $data['id_tipo_carrera'] == 0) || (isset($data['id_carrera']) && $data['id_carrera'] ==0)){
                   // @codingStandardsIgnoreStart
            $this->id_nivel_estudio->addValidator(
                new Zend_Validate_InArray(array_keys($this->_listaNiveles))
            );
            $this->id_nivel_estudio->errMsg = 
                "Campo Requerido";
      
            $nivel = new Application_Model_NivelEstudio;
                $listaNivelesTipo = $nivel->getSubNiveles($data['id_nivel_estudio']);
                $this->id_nivel_estudio_tipo->addValidator(
                        new Zend_Validate_InArray(
                        array_keys($listaNivelesTipo)
                        )
                );
                $this->id_nivel_estudio_tipo->errMsg = "Campo Requerido";
                // @codingStandardsIgnoreEnd
            
 
                    $this->id_tipo_carrera->addValidator(
                    new Zend_Validate_InArray(
                        array_keys($this->_listaTipoCarrera)
                    )
                );
                $this->id_tipo_carrera->errMsg = "Campo Requerido";
       
                 $carrera = new Application_Model_Carrera();
                 $listaCarreras = $carrera->getCarreras();
                
                // @codingStandardsIgnoreStart
                $this->id_carrera->addMultiOptions($listaCarreras);
                $this->id_carrera->addValidator(
                    new Zend_Validate_InArray(array_keys($listaCarreras)));
                        
            
                $this->id_carrera->errMsg = "Campo Requerido";
        }*/
        
       $valid = parent::isValid($data);
        }
//        if (!$this->hasValues($data))
//            $valid = false;
        
        return $valid;
    }
    
    public function addEstudioId()
    {
        $e = new Zend_Form_Element_Hidden('id_estudio');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);
    }
    
    public function setHiddenId($id)
    {
        $e = $this->getElement('id_estudio');
        $e->setValue($id);
    }
    
    public function hasValues($data)
    {
        if ($data['id_nivel_estudio'] != 0 && $data['id_tipo_carrera'] != 0 &&
                $data['id_carrera'] != 0 && $data['id_nivel_estudio_tipo'] != 0)
            return true;
        return false;
    }
    public function setElementNivelEstudio($padre) {       
        $e = $this->getElement('id_nivel_estudio_tipo');
        $e->addMultiOption('0', 'Selecciona un tipo');
        if(!empty($padre))
        {
            $listaNivelesTipo = $this->_nivelEstudio->getSubNiveles($padre);
            $e->addMultiOptions($listaNivelesTipo);
        }               
    }
    public function setElementCarrera($padre) {        
        $e = $this->getElement('id_carrera');
        $e->clearMultiOptions();
        $e->addMultiOption('0', 'Selecciona carrera');
        if(!empty($padre))
        {
            $listaNivelesTipo = $this->_carrera->filtrarCarrera($padre);
            $e->addMultiOptions($listaNivelesTipo);
        }         
    }    
}
//modificado