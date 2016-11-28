<?php


class Application_Form_Ubigeo extends App_Form
{
    protected $_ubigeo;
    protected $_listaPais;
    protected $_listaDepartamento;
    protected $_listaProvincia;
    protected $_listaDistrito;
    protected $_listaDistritoCallao;
    private $_online = false;
    
    public function __construct($online = false)
    {
        $this->_ubigeo = new Application_Model_Ubigeo();
        $this->_listaPais = $this->_ubigeo->getPaises();
        $this->_listaDepartamento = $this->_ubigeo->getDepartamentos();
        $this->_listaProvincia = $this->_ubigeo->getProvincias();
      //  $this->_listaDistrito = $this->_ubigeo->getDistritos();
       // $this->_listaDistritoCallao = $this->_ubigeo->getDistritosCallao();
        if (isset($online) && $online == true) {
            $this->_online = true;
        }
        parent::__construct();
    }
    
    public function init()
    {
        parent::init();
        // Combo País
        $fPais = new Zend_Form_Element_Select('pais_residencia');
        $fPais->setRequired();
        $fPais->addMultiOption('0', 'Seleccione país');
        $fPais->addMultiOptions($this->_listaPais);
        $fPaisVal = new Zend_Validate_InArray(array_keys($this->_listaPais));
        $fPais->addValidator($fPaisVal);
        $fPais->setValue(Application_Model_Ubigeo::PERU_UBIGEO_ID);
        $fPais->errMsg = $this->_mensajeRequired;
        $this->addElement($fPais);

        // Combo Departamento
        $fDepart = new Zend_Form_Element_Select('id_departamento');
        $fDepart->addMultiOption('0', 'Seleccione Región');
        $fDepart->addMultiOptions($this->_listaDepartamento);
        $fDepartVal = new Zend_Validate_InArray(array_keys($this->_listaDepartamento));
        $fDepart->addValidator($fDepartVal);
        $fDepart->setValue(Application_Model_Ubigeo::LIMA_UBIGEO_ID);
        $fDepart->errMsg = $this->_mensajeRequired;
        $this->addElement($fDepart);

        //Combo provincia
        $fProv = new Zend_Form_Element_Select('id_provincia');
        $fProv->addMultiOption('0', 'Seleccione Ciudad');
        $fProv->addMultiOptions($this->_listaProvincia);
        //$fProvVal = new Zend_Validate_InArray(array_keys($this->_listaProvincia));
        //$fProv->addValidator($fProvVal);

        $fProv->setValue(Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID);
        //$fProv->errMsg = $this->_mensajeRequired;
        $this->addElement($fProv);

        //Combo Distrito
//        $fDistri = new Zend_Form_Element_Select('id_distrito');
//        $fDistri->addMultiOption('0', 'Seleccione Distrito');
//        $fDistri->addMultiOptions($this->_listaDistrito);
//        $fDistri->errMsg = $this->_mensajeRequired;
//        $fDistri->clearValidators();
//        $this->addElement($fDistri);
//        
        if (isset($this->_online) && $this->_online == true) {
            // @codingStandardsIgnoreStart
            $this->pais_residencia->setAttrib("disabled", "disabled");
            $this->id_departamento->setAttrib("disabled", "disabled");
            $this->id_provincia->setAttrib("disabled", "disabled");
          //  $this->id_distrito->setAttrib("disabled", "disabled");
            // @codingStandardsIgnoreEnd
        }
    }
    
    public function isValid($data)
    {
        $ubigeo = new Application_Model_Ubigeo();
        $distritos = $ubigeo->getHijos($data['id_departamento']);

        $this->id_provincia->addValidator(
            new Zend_Validate_InArray(array_keys($distritos))
        );

        /*if (isset($data['id_provincia']) && 
            trim($data['id_provincia']) == Application_Model_Ubigeo::LIMA_PROVINCIA_UBIGEO_ID) {
            // @codingStandardsIgnoreStart
//            $this->id_distrito->clearValidators();
//            $this->id_distrito->addValidator(
//                new Zend_Validate_InArray(array_keys($this->_listaDistrito))
//            );
//            $this->id_distrito->clearMultiOptions();
//            $this->id_distrito->addMultiOption('0', 'Seleccione Distrito');
//            $this->id_distrito->addMultiOptions($this->_listaDistrito);
            // @codingStandardsIgnoreEnd
        }*/
        /*if (isset($data['id_provincia']) && 
            trim($data['id_provincia']) == Application_Model_Ubigeo::CALLAO_PROVINCIA_UBIGEO_ID) {
            // @codingStandardsIgnoreStart
//            $this->id_distrito->clearValidators();
//            $this->id_distrito->addValidator(
//                new Zend_Validate_InArray(array_keys($this->_listaDistritoCallao))
//            );
//            $this->id_distrito->clearMultiOptions();
//            $this->id_distrito->addMultiOption('0', 'Seleccione Distrito');
//            $this->id_distrito->addMultiOptions($this->_listaDistritoCallao);
            // @codingStandardsIgnoreEnd
        }*/
        
        if ($this->_online == true) {
            // @codingStandardsIgnoreStart
            $this->pais_residencia->clearValidators();
            $this->pais_residencia->setRequired(false);
            // @codingStandardsIgnoreEnd
        }
        
        $this->id_provincia->setRequired(false);

        return parent::isValid($data);
    }
    
    public function detalleUbigeo($ubigeoId = null)
    {

        if ($ubigeoId != null) {
            $detalle = $this->_ubigeo->getDetalleUbigeo($ubigeoId);
            //Zend_Debug::dump($detalle); die();

            $data['pais_residencia'] = $detalle['idpaisres'];
            $data['id_departamento'] = $detalle['iddpto'];
            $data['id_provincia'] = $detalle['idprov'];

            $distritos = $this->_ubigeo->getHijos($detalle['iddpto']);
            $this->id_provincia->addMultiOptions($distritos);
            
            $this->isValid($data);
        }
    }
}