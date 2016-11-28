<?php

require_once 'Excel/PHPExcel.php';


class App_Service_Excel
{

    /**
     *
     * @var App_Service_Excel
     */
    protected static $_instance;

    /**
     *
     * @var type 
     */
    protected static $_objExcel;

    /**
     *
     * @var type 
     */
    protected $_logo;

    /**
     *
     * @var type 
     */
    protected $_data;

    /**
     *
     * @var array
     */
    private $_collection = array();

    /**
     *
     * @var type 
     */
    protected $_headers = array();

    public static function getInstance()
    {
        if (null === static::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * 
     * @return type
     */
    public function getCollection()
    {
        return self::getInstance()->_collection;
    }

    public function append($objeto)
    {
        self::getInstance()->setCollection($objeto);
    }

    public function appendList($objeto)
    {
        self::getInstance()->setCollections($objeto);
    }

    protected function setCollection($objeto)
    {
        self::getInstance()->_collection[] = $objeto;
    }

    protected function setCollections($objeto)
    {
        self::getInstance()->_collection = $objeto;
    }

    /**
     * 
     * @param type $data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * 
     * @return type
     */
    public function getHeaders()
    {
        if (!is_null(self::getInstance()->_headers)) {
            return self::getInstance()->_headers;
        }
        $headers = array_keys(self::getInstance()->getCollection());
        return $headers;
    }

    public function setHeaders($array)
    {
        self::getInstance()->_headers = $array;
    }

    public function setLogo($logo)
    {
        $this->_logo = $logo;
    }

    public function getLogo()
    {
        return $this->_logo;
    }

    protected function _generateHeaders()
    {
        
    }

    protected function _generateBody()
    {
        
    }

    public function getObjectExcel()
    {
        $objPHPExcel = new PHPExcel();

        $config = Zend_Registry::get('config')->enumeraciones;

        $objPHPExcel->getProperties()->setCreator("EMPLEOBUSCO")
            ->setLastModifiedBy("EMPLEOBUSCO")
            ->setTitle("Office 2003 XLSX Test Document")
            ->setSubject("Office 2003 XLSX Test Document")
            ->setDescription("Test document for Office 2003 XLS, 
                generated using PHP classes.")
            ->setKeywords("office 2003 openxml php")
            ->setCategory("Test result file");

        if ($this->_logo && is_file($this->_logo)) {
            $objDrawing = new PHPExcel_Worksheet_Drawing();
            $objDrawing->setName('Logo');
            $objDrawing->setDescription('imagen de logo');
            $objDrawing->setPath($this->_logo);
            $objDrawing->setCoordinates('A1');
            $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
        }

        /**
         * @todo upgrade code
         */
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A5', "Exportación de Postulantes");
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A6', "Proceso:");
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A7', "Inicio de Proceso:");

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B6', $this->_data['puesto']);
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B7', $this->_data['fcreacion']);

        $fil = 10;
        $col = 0;
        
        foreach (static::getInstance()->getHeaders() as $key => $value) :
            $objPHPExcel->getActiveSheet()->setCellValue(chr(65 + $key) . $fil,
                ucwords($value));
            $col++;
        endforeach;

        $styleArray = array(
            'font' => array(
                'bold' => true,
                'size' => 13
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A5')
            ->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A6')
            ->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A7')
            ->applyFromArray($styleArray);


        $objPHPExcel->getActiveSheet()->getStyle('A' . $fil . ':' .
            chr(65 + $col) . $fil)->getFont()->setBold(true);
        $fil++;

        foreach (static::getInstance()->getCollection() as $collection) :
            $col = 0;
            foreach (static::getInstance()->getHeaders() as $key => $value) :
                $valor = $collection[$value];
                if ($value == "nombres" || $value == "apellidos") {
                    $valor = ucwords($valor);
                }
                if ($value == "etapas del proceso" && is_null($valor)) {
                    $valor = "Postulante";
                }
                if ($value == "idioma y nivel" && !is_null($valor)) {
                    $arrayValor = explode('|', $valor);
                    if (isset($arrayValor[1]) && isset($arrayValor[2])) {
                        $valor = $config->lenguajes->{$arrayValor[1]} . ' ' . $arrayValor[2];
                    }
                }
                if ($value == "programas y nivel" && !is_null($valor)) {
                    $arrayValor = explode('|', $valor);
                    if (isset($arrayValor[1]) && isset($arrayValor[2])) {
                        $valor = $config->programas_computo->{$arrayValor[1]} . ' ' . $arrayValor[2];
                    }
                }
                $objPHPExcel->getActiveSheet()->setCellValue(chr(65 + $col) . $fil,
                    $valor);
                $col++;
            endforeach;
            $fil++;
        endforeach;

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);

        
        $objPHPExcel->setActiveSheetIndex(0);

        return $objPHPExcel;
    }
    public function getObjectExcelEmpresa()
    {
        $objPHPExcel = new PHPExcel();

        $config = Zend_Registry::get('config')->enumeraciones;

        $objPHPExcel->getProperties()->setCreator("EMPLEOBUSCO")
            ->setLastModifiedBy("EMPLEOBUSCO")
            ->setTitle("Office 2003 XLSX Test Document")
            ->setSubject("Office 2003 XLSX Test Document")
            ->setDescription("Test document for Office 2003 XLS,
                generated using PHP classes.")
            ->setKeywords("office 2003 openxml php")
            ->setCategory("Test result file");

        if ($this->_logo && is_file($this->_logo)) {
            $objDrawing = new PHPExcel_Worksheet_Drawing();
            $objDrawing->setName('Logo');
            $objDrawing->setDescription('imagen de logo');
            $objDrawing->setPath($this->_logo);
            $objDrawing->setCoordinates('A1');
            $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
        }

        /**
         * @todo upgrade code
         */
//        $objPHPExcel->setActiveSheetIndex(0)
//            ->setCellValue('A5', "Exportación de Postulantes");
//        $objPHPExcel->setActiveSheetIndex(0)
//            ->setCellValue('A6', "Proceso:");
//        $objPHPExcel->setActiveSheetIndex(0)
//            ->setCellValue('A7', "Inicio de Proceso:");
//
//        $objPHPExcel->setActiveSheetIndex(0)
//            ->setCellValue('B6', $this->_data['puesto']);
//        $objPHPExcel->setActiveSheetIndex(0)
//            ->setCellValue('B7', $this->_data['fcreacion']);

        $fil = 10;
        $col = 0;

        foreach (static::getInstance()->getHeaders() as $key => $value) :
            $objPHPExcel->getActiveSheet()->setCellValue(chr(65 + $key) . $fil,
                ucwords($value));
            $col++;
        endforeach;

        $styleArray = array(
            'font' => array(
                'bold' => true,
                'size' => 13
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle('A5')
            ->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A6')
            ->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A7')
            ->applyFromArray($styleArray);


        $objPHPExcel->getActiveSheet()->getStyle('A' . $fil . ':' .
            chr(65 + $col) . $fil)->getFont()->setBold(true);
        $fil++;

        foreach (static::getInstance()->getCollection() as $collection) :
            $col = 0;
            foreach (static::getInstance()->getHeaders() as $key => $value) :
                $valor = $collection[$value];
                if ($value == "nombres" || $value == "apellidos") {
                    $valor = ucwords($valor);
                }
                if ($value == "etapas del proceso" && is_null($valor)) {
                    $valor = "Postulante";
                }
                if ($value == "idioma y nivel" && !is_null($valor)) {
                    $arrayValor = explode('|', $valor);
                    if (isset($arrayValor[1]) && isset($arrayValor[2])) {
                        $valor = $config->lenguajes->{$arrayValor[1]} . ' ' . $arrayValor[2];
                    }
                }
                if ($value == "programas y nivel" && !is_null($valor)) {
                    $arrayValor = explode('|', $valor);
                    if (isset($arrayValor[1]) && isset($arrayValor[2])) {
                        $valor = $config->programas_computo->{$arrayValor[1]} . ' ' . $arrayValor[2];
                    }
                }
                $objPHPExcel->getActiveSheet()->setCellValue(chr(65 + $col) . $fil,
                    $valor);
                $col++;
            endforeach;
            $fil++;
        endforeach;

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);


        $objPHPExcel->setActiveSheetIndex(0);

        return $objPHPExcel;
    }
    private function _checkField($key, $value)
    {
        return $value;
    }

    /**
     * 
     */
    public function exportar()
    {
        
    }

}