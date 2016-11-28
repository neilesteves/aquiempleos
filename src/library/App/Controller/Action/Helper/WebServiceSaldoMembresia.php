<?php

class App_Controller_Action_Helper_WebServiceSaldoMembresia 
    extends Zend_Controller_Action_Helper_Abstract
{
    private $_saldoConsultado;
    public function __construct()
    {
        $this->_config = Zend_Registry::get('config');
        $this->_saldoConsultado = new Zend_Soap_Client(
            $this->_config->urlpreferencial->consultaSaldo
        );
    }
    
    public function consultarSaldo($rowEmp)
    {
        $response = $this->_saldoConsultado->ContratoAdecsys_ConsultaxCodigoCliente($rowEmp);
        // @codingStandardsIgnoreStart
        $objRpta = $response->ContratoAdecsys_ConsultaxCodigoClienteResult;
        //$arrayRpta = array();
        if (isset($objRpta->oContratoDatos->BEContrato)) {
            $arrayRpta['SaldoContrato'] = $objRpta->oContratoDatos->BEContrato->SaldoContrato;
            $arrayRpta['SituacionContrato'] = $objRpta->oContratoDatos->BEContrato->SituacionContrato;
        } else {
            $arrayRpta = null;
        }
        // @codingStandardsIgnoreEnd
        return $arrayRpta ;
    }
    
}
