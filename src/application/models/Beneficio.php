<?php

class Application_Model_Beneficio extends App_Db_Table_Abstract
{
    protected $_name = "beneficio";
    
    const CODE_NDIASPUB             = 'ndiaspub';
    const CODE_NDIASPROC            = 'ndiasproc';
    const CODE_BUSCADOR             = 'buscador';
    const CODE_BOLSACV              = 'bolsacv';
    const CODE_DESC_APTITUS         = 'descaptitus';
    const CODE_DESC_TALAN           = 'desctalan';
    const CODE_DESC_COMBO           = 'desccombo';
    const CODE_NDIASPRIO            = 'ndiasprio';
    const CODE_REASIGNAR_PROCESOS   = 'rprocesos';
    
    public function obtenerPorCodigo($codigo)
    {
        return $this->fetchRow($this->select()
            ->where('codigo =?', $codigo));
    }
}
