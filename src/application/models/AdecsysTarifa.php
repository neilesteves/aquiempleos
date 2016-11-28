<?php


class Application_Model_AdecsysTarifa
    extends App_Db_Table_Abstract
{

    protected $_name = "adecsys_tarifa";

    const TIPO_TALAN = "talan";
    const TIPO_APTITUS = "aptitus";
    const TIPO_APTITUS_COMBO = "aptitusCombo";
    const TIPO_TALAN_COMBO = "talanCombo";
    
    const ACTIVO = 1;
    const INACTIVO = 0;

    /**
     * 
     * @param string $tipo
     * @return type
     */
    public function listByTipo($tipo)
    {
        $query = $this->getAdapter();
        $where = $query->quoteInto(" tipo = ?", $tipo);
        $sql = $query->select()->from($this->_name)
            ->where($where);
        return $query->fetchAll($sql);
    }

    /**
     * 
     * @param string $tipo
     * @param string $codSubseccion
     * @param string $tamanio
     * @return object
     */
    public function getByTipocodSubseccionTamanio($tipo, $codSubseccion,
        $tamanio)
    {
        $query = $this->getAdapter();
        $sql = $query->select()->from($this->_name,
                array(
                'Med_Vertical',
                'Med_Horizontal',
                'Ubi_Id',
                'Cod_Ubi',
                'Des_Ubi',
                'Tar_Id',
                'Cod_Tar',
                'Des_Tar',
                'Med_Id',
                'Des_Med',
                'Sub_Sec_Id',
                'Cod_Sub_Sec',
                'Des_Sub_Sec')
            )
            ->where("tipo = ?", $tipo)
            ->where("tamanio = ?", $tamanio)
            ->where("Cod_Sub_Sec = ?", $codSubseccion)
            ->where('active = ?', self::ACTIVO);
        //echo $sql;exit;
        return $query->fetchRow($sql, Zend_Db::FETCH_OBJ);
    }

    /**
     * 
     * @param string $tipo
     * @param string $tamanio
     * @return object
     */
    public function getByTipoTamanio($tipo, $tamanio)
    {
        $query = $this->getAdapter();
        $sql = $query->select()->from($this->_name,
                array(
                'Med_Vertical',
                'Med_Horizontal',
                'Ubi_Id',
                'Cod_Ubi',
                'Des_Ubi',
                'Tar_Id',
                'Cod_Tar',
                'Des_Tar',
                'Med_Id',
                'Des_Med',
                'Sub_Sec_Id',
                'Cod_Sub_Sec',
                'Des_Sub_Sec')
            )
            ->where("tipo = ?", $tipo)
            ->where("tamanio = ?", $tamanio)
            ->where('active = ?', self::ACTIVO);
        
        return $query->fetchRow($sql, Zend_Db::FETCH_OBJ);
    }

}