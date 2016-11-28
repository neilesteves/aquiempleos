<?php


class Application_Form_InvitarBusqueda extends App_Form
{
    //listas
    Protected $_listaProcesos;
    protected  $_id;
    protected  $_buscador;
    
    public function __construct($auth)
    {
        $this->_id = $auth["empresa"]["id"];
        $this->_buscador=(!empty($auth['empresa']['membresia_info']['beneficios']->buscador) ||
                $auth['empresa']['membresia_info']['membresia']['id_membresia']==11)?1:0;
        parent::__construct();
    }
    
    public function init()
    {
        parent::init();

        $this->_listaProcesos = new Application_Model_AnuncioWeb();
        $valores = array();
        if($this->_buscador){
          $valores = $this->_listaProcesos->getAvisosInvitar($this->_id);
        } else {
          $valores = $this->_listaProcesos->getAvisosInvitarPreferencial($this->_id);
        }
        $fAviso = new Zend_Form_Element_Select('aviso');
        $fAviso->setRequired();
        $fAviso->addMultiOption('none', 'Seleccione Aviso');
        foreach ($valores as $v) {
            $fAviso->addMultiOption($v["id"], $v["puesto"]);
        }
        $fAvisoVal = new Zend_Validate_InArray(array_keys($valores));
        $fAviso->addValidator($fAvisoVal);
        $fAviso->setValue("none");
        $fAviso->errMsg = $this->_mensajeRequired;
        $this->addElement($fAviso);
        
        
        $e = new Zend_Form_Element_Hidden('tok');
        $e->setRequired();
        $hash_blow_fish = crypt(date('dmYH'), '$2a$07$'.md5(uniqid(rand(), true)).'$');
        $e->setValue($hash_blow_fish);
        $this->addElement($e);
        
    }
    
    public function isValid($data)
    {
        $esValido = parent::isValid($data);
        
        if (!(crypt(__CLASS__.date('dmYH'), $data['tok']) === $data['tok'])) {
            $esValido = false;
        }
        
        return $esValido;
        
    }
  
    
    
   
}

