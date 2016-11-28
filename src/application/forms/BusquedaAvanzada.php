<?php

class Application_Form_BusquedaAvanzada extends App_Form
{
    
    const QUERY_LENGTH_MIN = 3;
    const QUERY_LENGTH_MAX = 60;

    public function init()
    {
        parent::init();
        // Texto a buscar
        $fDatosBusqueda = new Zend_Form_Element_Text('datosBusqueda');
        $fDatosBusqueda->addValidator(
            new Zend_Validate_StringLength(
            array('min' => self::QUERY_LENGTH_MIN, 'max' => self::QUERY_LENGTH_MAX,
            'encoding' => $this->_config->resources->view->charset)
            )
        );
        $this->addElement($fDatosBusqueda);
        
        $fArea1 = new Zend_Form_Element_Select('areas1');
        $fArea1->addMultiOption('0', 'Seleccionar...');
        $this->addElement($fArea1);
        
        $fArea2 = new Zend_Form_Element_Select('areas2');
        $fArea2->addMultiOption('0', 'Seleccionar...');
        $this->addElement($fArea2);
        
        $fArea3 = new Zend_Form_Element_Select('areas3');
        $fArea3->addMultiOption('0', 'Seleccionar...');
        $this->addElement($fArea3);
        
        $fNivelPuesto1 = new Zend_Form_Element_Select('nivelPuestos1');
        $fNivelPuesto1->addMultiOption('0', 'Seleccionar...');
        $this->addElement($fNivelPuesto1);
        
        $fNivelPuesto2 = new Zend_Form_Element_Select('nivelPuestos2');
        $fNivelPuesto2->addMultiOption('0', 'Seleccionar...');
        $this->addElement($fNivelPuesto2);
        
        $fNivelPuesto3 = new Zend_Form_Element_Select('nivelPuestos3');
        $fNivelPuesto3->addMultiOption('0', 'Seleccionar...');
        $this->addElement($fNivelPuesto3);
        
        $ubigeo= new Application_Model_Ubigeo();
        $prov=$ubigeo->getProvincias();
        $fProvincias = new Zend_Form_Element_Select('provincia');
        $fProvincias->addMultiOption('0', 'Seleccionar...');
        $fProvincias->addMultiOptions($prov);
        $fProvincias->setValue(3927);
        $this->addElement($fProvincias);
    }

    public function setAreas($areas,$count = 0)
    {
        $areaElement1 = $this->getElement('areas1');
        $areaElement2 = $this->getElement('areas2');
        $areaElement3 = $this->getElement('areas3');
        foreach ($areas as $val) {
            if(empty($count))
            {
                $areaElement1->addMultiOption($val['slug'], $val['label']);
                $areaElement2->addMultiOption($val['slug'], $val['label']);
                $areaElement3->addMultiOption($val['slug'], $val['label']);
            }
            else
            {                
                $areaElement1->addMultiOption($val['slug'], $val['label'].' ('.$val['count'].')');
                $areaElement2->addMultiOption($val['slug'], $val['label'].' ('.$val['count'].')');
                $areaElement3->addMultiOption($val['slug'], $val['label'].' ('.$val['count'].')');
            }
        }
    }

    public function setNivelPuestos($nivelPuestos,$count = 0)
    {
        $nivelElement1 = $this->getElement('nivelPuestos1');
        $nivelElement2 = $this->getElement('nivelPuestos2');
        $nivelElement3 = $this->getElement('nivelPuestos3');
        foreach ($nivelPuestos as $val) {
            if(empty($count))
            {
                $nivelElement1->addMultiOption($val['slug'], $val['label']);
                $nivelElement2->addMultiOption($val['slug'], $val['label']);
                $nivelElement3->addMultiOption($val['slug'], $val['label']);
            }
            else
            {
                $nivelElement1->addMultiOption($val['slug'], $val['label'].' ('.$val['count'].')');
                $nivelElement2->addMultiOption($val['slug'], $val['label'].' ('.$val['count'].')');
                $nivelElement3->addMultiOption($val['slug'], $val['label'].' ('.$val['count'].')');
            }
        }
    }
    public function setValuesAreas($areas)
    {
        foreach($areas as $k => $v)
        {   
            if($k<3)
            {
                $a = $k+1;
                $this->getElement("areas$a")->setValue($v);
            }
        }
    }
    public function setValuesNivelPuestos($areas)
    {
        foreach($areas as $k => $v)
        {   
            if($k<3)
            {
                $a = $k+1;
                $this->getElement("nivelPuestos$a")->setValue($v);
            }
        }
    }
    
}
