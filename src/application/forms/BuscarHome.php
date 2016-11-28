<?php

class Application_Form_BuscarHome extends App_Form
{

    protected $_anuncios;

    const QUERY_LENGTH_MIN = 3;
    const QUERY_LENGTH_MAX = 60;

    public static $mensajeCodigoImpreso = '¡Tengo el código impreso!';

    public function init()
    {
        parent::init();
        // Texto a buscar
        $fDatosBusqueda = new Zend_Form_Element_Text('txtDescription');
//        $fDatosBusqueda->addValidator(
//            new Zend_Validate_StringLength(
//            array('min' => self::QUERY_LENGTH_MIN, 'max' => self::QUERY_LENGTH_MAX,
//            'encoding' => $this->_config->resources->view->charset)
//            )
//        );
        $this->addElement($fDatosBusqueda);

        // Buscar por urlId
        $e = new Zend_Form_Element_Text('urlId');
        $e->setValue(self::$mensajeCodigoImpreso);
//        $e->addValidator(
//            new Zend_Validate_StringLength(
//            array('min' => 0, 'max' => 12,
//            'encoding' => $this->_config->resources->view->charset)
//            )
//        );
        $this->addElement($e);

        // Combo Áreas
        $fArea = new Zend_Form_Element_Select('selArea');
        $fArea->addMultiOption('', 'Selecciona un área');
        $this->addElement($fArea);

        // Combo Nivel de Puesto
        $fNivelPuesto = new Zend_Form_Element_Select('nivelPuestos');
        $fNivelPuesto->addMultiOption('none', 'Selecciona un puesto');
        $this->addElement($fNivelPuesto);

        $this->_anuncios = new Application_Model_AnuncioWeb();
//        $departamento = $this->_anuncios->getGroupUbiPortadaDepartamento(
//                Application_Model_Ubigeo::PERU_UBIGEO_ID, $ubicacion);

        //$valores = $this->_anuncios->getDepartamentos();

        $ubigeo = new Application_Model_Ubigeo();
        $valores = $ubigeo->getPaisesSlug();

        // Combo Ubicacion
        $fUbicaciones = new Zend_Form_Element_Select('pais');
        $fUbicaciones->addMultiOption('none', 'Ubicación');
        if(count($valores) > 0) {
            $fUbicaciones->addMultiOptions($valores);
        }
        $fUbicaciones->setValue(Application_Model_Ubigeo::SLUG_PAIS);

        $this->addElement($fUbicaciones);

        // Submit
        $fButton = new Zend_Form_Element_Button('button');
        $fButton->setLabel('Buscar');
        $this->addElement($fButton);
    }

    public function setAreas( $areas )
    {
        $areaElement = $this->getElement('selArea');
        foreach ($areas as $val) {
            $areaElement->addMultiOption(
                    $val['slug'], $val['label'] . ' (' . $val['count'] . ')'
            );
        }
    }

    public function setNivelPuestos( $nivelPuestos )
    {
        $areaElement = $this->getElement('nivelPuestos');
        foreach ($nivelPuestos as $val) {
            $areaElement->addMultiOption($val['slug'], $val['label']);
        }
    }

    public function setUbicacion( $ubicacion )
    {
        $ubicacionElement = $this->getElement('selCity');
    }

}
