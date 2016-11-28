<?php

class Application_Model_AdecsysReproceso extends App_Db_Table_Abstract {

    protected $_name = "adecsys_reproceso";

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_GENERADO = 'generado';

    public function validarExistencia($idCompra, $idCac) {

        $sql = $this->getAdapter()->select()
                ->from($this->_name)
                ->where('id_compra =?', $idCompra)
                ->where('id_cac = ?', $idCac);
//echo $sql ;exit;
        return $this->getAdapter()->fetchAll($sql);
    }

    public function nroReprocesosAdecsys($idCompra, $idCac) {

        $dataARValida = $this->validarExistencia($idCompra, $idCac);
        $numRep = 0;
        if (count($dataARValida) > 0)
            $numRep = $dataARValida[0]['cant_reproceso_adecsys'];

        return $numRep;
    }

    public function nroReprocesosScot($idCompra, $idCac) {

        $dataARValida = $this->validarExistencia($idCompra, $idCac);
        $numRep = 0;
        if (count($dataARValida) > 0)
            $numRep = $dataARValida[0]['cant_reproceso_scot'];

        return $numRep;
    }

    //Obtiene estado del reproceso de Adecsys y SCOT para que lo tome el cron de envío
    public function validaEstado($idCompra, $idCac, $tipoEstado = 'estado_adecsys') {

        $dataARValida = $this->validarExistencia($idCompra, $idCac);
        $estado = self::ESTADO_PENDIENTE;
        if (count($dataARValida) > 0)
            $estado = $dataARValida[0][$tipoEstado];

        return $estado;
    }

    //Valida si e tiene reproceso, si ya llegó al 5 reproceso o si el estado es aún pendiente para que 
    // se envíe en el cron
    public function validaGeneraEnvioAdecsys($idCompra, $idCac) {

        $config = Zend_Registry::get('config');
        $nroReprocesosMaxAdecsys = $config->reprocesoAdecsys->envioAdecsys;

        $dataARValida = $this->validarExistencia($idCompra, $idCac);
        $numRep = 0;
        if (count($dataARValida) > 0) {
            $numRep = $dataARValida[0]['cant_reproceso_adecsys'];
                $estadoAdecsys = $this->validaEstado($idCompra, $idCac);
                if ($estadoAdecsys == self::ESTADO_GENERADO)
                    return false;
                else
                    return true;
        }

        if ($numRep == 0)
            return true;
    }
    
    //Valida si e tiene reproceso, si ya llegó al 5 reproceso o si el estado es aún pendiente para que 
    // se envíe en el cron
    public function validaGeneraEnvioScot($idCompra, $idCac) {

        $config = Zend_Registry::get('config');
        $nroReprocesosMaxAdecsys = $config->reprocesoAdecsys->envioSCOT;

        $dataARValida = $this->validarExistencia($idCompra, $idCac);
        $numRep = 0;
        if (count($dataARValida) > 0) {
            $numRep = $dataARValida[0]['cant_reproceso_scot'];
                $estadoAdecsys = $this->validaEstado($idCompra, $idCac, 'estado_scot');
                if ($estadoAdecsys == self::ESTADO_GENERADO)
                    return false;
                else
                    return true;

        }

        if ($numRep == 0)
            return true;
    }

}