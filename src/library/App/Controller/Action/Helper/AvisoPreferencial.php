<?php


class App_Controller_Action_Helper_AvisoPreferencial
    extends Zend_Controller_Action_Helper_Abstract
{

    private $_anuncioImpresoId;
    private $_anuncioWebId;
    
    public $_SolrAviso;
    
    public function __construct()
    {
        $this->_SolrAviso= new Solr_SolrAviso();
    }
    

    /**
     * Inserta un nuevo aviso preferencial con su primer anuncio web.
     * 
     * @param array $dataPost
     * @param App_Form_Manager $managerEstudio
     * @param App_Form_Manager $managerExperiencia
     * @param App_Form_Manager $managerIdioma
     * @param App_Form_Manager $managerPrograma
     * @param App_Form_Manager $managerPregunta
     * 
     * @return int
     */
    public function _insertarNuevoAvisoImpreso(
    array $dataPost, App_Form_Manager $managerEstudio,
        App_Form_Manager $managerExperiencia, App_Form_Manager $managerIdioma,
        App_Form_Manager $managerPrograma, App_Form_Manager $managerPregunta,
        App_Form_Manager $managerOtroEstudio,
        $idEmpresa = null, $extiende = null, $republica = null
    )
    {
        $action = $this->getActionController();
        $tarifa = new Application_Model_Tarifa();
        $anuncioImpresoModel = new Application_Model_AnuncioImpreso();
        $anuncioWebModel = new Application_Model_AnuncioWeb();
        $datosTarifa = $tarifa->getProductoByTarifa($dataPost['id_tarifa']);

        $util = $action->getHelper('Util');
        $dataPost['id_ubigeo'] = $util->getUbigeo($dataPost);

        $avisoHelper = $action->getHelper('Aviso');

        $anuncioImpresoData = array(
            'fh_creacion' => date('Y-m-d H:i:s'),
            'titulo' => $datosTarifa['nombre'] . '-' . date('Y-m-d'),
            'id_producto' => $datosTarifa['id_producto'],
            'estado' => 'registrado',
            'id_tarifa' => $datosTarifa['id_tarifa'],
            'id_empresa' => $dataPost['id_empresa'],
            'tipo' => Application_Model_Producto::TIPO_PREFERENCIAL
        );
        $this->_anuncioImpresoId = $anuncioImpresoModel->insert(
            $anuncioImpresoData
        );

        $this->_anuncioWebId = $avisoHelper->_insertarNuevoPuesto($dataPost,
            $extiende, $idEmpresa, $republica);
        $avisoHelper->_insertarPreguntas($managerPregunta, $idEmpresa);
        $avisoHelper->_insertarEstudios($managerEstudio);
        $avisoHelper->_insertarOtrosEstudios($managerOtroEstudio);
        $avisoHelper->_insertarExperiencia($managerExperiencia);
        $avisoHelper->_insertarIdiomas($managerIdioma);
        $avisoHelper->_insertarPrograma($managerPrograma);
        unset($dataPost['id_empresa']);
        unset($dataPost['logo_empresa']);
        unset($dataPost['nombre_comercial']);
        unset($dataPost['id_empresa_membresia']);


        $where = $anuncioWebModel->getAdapter()
            ->quoteInto('id = ?', $this->_anuncioWebId);
        $anuncioWebModel->update(
            array(
            'id_anuncio_impreso' => $this->_anuncioImpresoId
            ), $where
        );
        return $this->_anuncioImpresoId;
    }

    /**
     * Inserta un nuevo anuncio web dentro de un anuncio preferencial
     * 
     * @param array $dataPost
     * @param int $anuncioPreferencial
     * @param App_Form_Manager $managerEstudio
     * @param App_Form_Manager $managerExperiencia
     * @param App_Form_Manager $managerIdioma
     * @param App_Form_Manager $managerPrograma
     * @param App_Form_Manager $managerPregunta
     * 
     * @return int
     */
    public function _insertarNuevoAvisoWebPreferencial(
    array $dataPost, $anuncioPreferencial, App_Form_Manager $managerEstudio,
        App_Form_Manager $managerExperiencia, App_Form_Manager $managerIdioma,
        App_Form_Manager $managerPrograma, App_Form_Manager $managerPregunta,
        App_Form_Manager $managerOtroEstudio,   
        $idEmpresa = null 
    )
    {

        $action = $this->getActionController();
        //$tarifa = new Application_Model_Tarifa();
        //$anuncioImpresoModel = new Application_Model_AnuncioImpreso();
        $anuncioWebModel = new Application_Model_AnuncioWeb();
        //$datosTarifa = $tarifa->getProductoByTarifa($dataPost['id_tarifa']);

        $util = $action->getHelper('Util');
        $dataPost['id_ubigeo'] = $util->getUbigeo($dataPost);

        $avisoHelper = $action->getHelper('Aviso');

        $this->_anuncioImpresoId = $anuncioPreferencial;
        //$idEmpresa = isset($idEmpresa) ? $idEmpresa : NULL;
        $this->_anuncioWebId = $avisoHelper->_insertarNuevoPuesto($dataPost,
            $idEmpresa);
        $avisoHelper->_insertarPreguntas($managerPregunta, $idEmpresa);
        $avisoHelper->_insertarEstudios($managerEstudio);
        $avisoHelper->_insertarOtrosEstudios($managerOtroEstudio);
        $avisoHelper->_insertarExperiencia($managerExperiencia);
        $avisoHelper->_insertarIdiomas($managerIdioma);
        $avisoHelper->_insertarPrograma($managerPrograma);

        $where = $anuncioWebModel->getAdapter()
            ->quoteInto('id = ?', $this->_anuncioWebId);
        $anuncioWebModel->update(
            array(
            'id_anuncio_impreso' => $this->_anuncioImpresoId
            ), $where
        );
        return $this->_anuncioImpresoId;
    }

    /**
     * Retorna un array con datos de la tarifa para un producto determinado.
     * 
     * @param int $productoId
     * @return array
     */
    public function getGrillaByProducto($productoId)
    {
        $usoMembresia = '0';
        $modelProducto = new Application_Model_Producto();
        $arrayArmado = array();
        $arrayProducto =
            $modelProducto->getTarifasAvisoPreferencial($productoId,
            $usoMembresia != 0 ? $this->_idMembresia : null);

        $i = 0;
        foreach ($arrayProducto as $row) {
            if ($row['medio_pub'] == Application_Model_Tarifa::MEDIOPUB_APTITUS) {
                $arrayArmado['id'][$i / 3][] = $row['id'];
                $arrayArmado['plan'][] = $row['descripcion'];
                $arrayArmado['precio1'][] = number_format($row['precio'], '2',
                    '.', ',');
                $arrayArmado['path'][] = $row['path'];
                $arrayArmado['maximo_avisos'][] = $row['maximo_avisos'];
                $arrayArmado['tamano_centimetro'][] = $row['tamano_centimetro'];
                $i++;
            } elseif ($row['medio_pub'] == Application_Model_Tarifa::MEDIOPUB_TALAN) {
                $arrayArmado['id'][$i / 3][] = $row['id'];
                $arrayArmado['precio2'][] = number_format($row['precio'], '2',
                    '.', ',');
                $i++;
            } else {
                $arrayArmado['id'][$i / 3][] = $row['id'];
                $arrayArmado['precio3'][] = number_format($row['precio'], '2',
                    '.', ',');
                $i++;
            }
        }

        return $arrayArmado;
    }
    
    public function getAnuncioId()
    {
        return $this->_anuncioWebId;
    }
}