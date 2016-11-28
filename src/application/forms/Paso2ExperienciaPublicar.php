<?php

/**
 * Description of Form Paso2 Section Experiencia
 *
 * @author Jesus
 */
class Application_Form_Paso2ExperienciaPublicar extends App_Form
{

    private $_niveles;
    private $_listaAreas;
    private $_online = false;

    public function __construct( $hasHiddenId = false, $online = false )
    {
        if(isset($online) && $online == true) {
            $this->_online = true;
        }
        parent::__construct();
        if($hasHiddenId) {
            $this->addExperienciaId();
        }
    }

    public function init()
    {
        parent::init();
        $this->setMethod('post');

        //Nivel del puesto
        $nivelPuesto = new Application_Model_NivelPuesto();
        $this->_niveles = $nivelPuesto->getNiveles();
        $e = new Zend_Form_Element_Select('id_nivel_puesto');
        $e->addMultiOption('0', 'Seleccionar un nivel');
        $e->addMultiOptions($this->_niveles);        
        $e->setRequired(false);
        $this->addElement($e);

        //Area
        $area = new Application_Model_Area();
        $this->_listaAreas = $area->getAreas();
        $e = new Zend_Form_Element_Select('id_area');
        $e->addMultiOption('0', 'Seleccionar un área');
        $e->addMultiOptions($this->_listaAreas);
        $e->setRequired(false);
        $this->addElement($e);

        //Tiempo de experiencia
        $config = Zend_Registry::get('config');
        $rango = $config->experiencia->tiempo->rango->toArray();
        $e = new Zend_Form_Element_Select('experiencia');
        $e->addMultiOptions($rango);
        $this->addElement($e);

        //Bloque los combos si el aviso estan online 
        if(isset($this->_online) && $this->_online == true) {
            $this->disableFileds();
        }
    }

    public function isValid( $data )
    {
        $nivelPuesto = new Application_Model_NivelPuesto();

        $resultData = $nivelPuesto->getNivelesByAreaSelect($data['id_area']);
        if($this->_online == true) {
            // @codingStandardsIgnoreStart

            $this->id_nivel_puesto->clearValidators();
            $this->id_nivel_puesto->setRequired(false);
            $this->id_area->clearValidators();
            $this->id_area->setRequired(false);
            $this->experiencia->clearValidators();
            $this->experiencia->setRequired(false);
  $valid=true;
            // @codingStandardsIgnoreEnd
        } else {

            if($data['id_nivel_puesto'] != 0 || $data['id_area'] != 0) {
            
                // @codingStandardsIgnoreStart
                /*$this->id_nivel_puesto->addValidator(
                    new Zend_Validate_InArray(array_keys($resultData))
                );

                $this->id_nivel_puesto->errMsg = "Es necesario ingresar un nivel";
*/
                /*$this->id_area->addValidator(
                        new Zend_Validate_InArray(array_keys($this->_listaAreas))
                );
                $this->id_area->errMsg = "Es necesario ingresar una area";*/

                // @codingStandardsIgnoreEnd
            }

            $valid = parent::isValid($data);

            



            if($valid == false) {

                /*$this->id_nivel_puesto->addValidator(
                    new Zend_Validate_InArray(array_keys($resultData))
                );*/
                $this->id_nivel_puesto->errMsg = "Es necesario ingresar un nivel";
                /*$this->id_area->addValidator(
                        new Zend_Validate_InArray(array_keys($this->_listaAreas))
                );*/
                $this->id_area->errMsg = "Es necesario ingresar un area";
            }
        }
        return $valid;
    }

    public function addExperienciaId()
    {
        $e = new Zend_Form_Element_Hidden('id_Experiencia');
        $e->clearDecorators();
        $e->addDecorator('ViewHelper');
        $this->addElement($e);
    }

    public function setHiddenId( $id )
    {
        $e = $this->getElement('id_Experiencia');
        $e->setValue($id);
    }

    public function disableFileds()
    {
//        $this->id_nivel_puesto->setAttrib('disabled', 'disabled');
//        $this->id_area->setAttrib('disabled', 'disabled');
//        $this->experiencia->setAttrib('disabled', 'disabled');
    }

    public function hasValues( $data )
    {
        if($data['id_nivel_puesto'] != 0 && $data['id_area'] != 0)
            return true;
        return false;
    }

}
