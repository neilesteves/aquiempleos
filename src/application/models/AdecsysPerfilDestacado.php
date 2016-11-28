<?php

class Application_Model_AdecsysPerfilDestacado extends App_Db_Table_Abstract {

    protected $_name = "adecsys_perfil_destacado";

    const ACTIVO = 1;
    const INACTIVO = 0;

    /**
     * 
     * @param string $tipo
     * @param string $tamanio
     * @return object
     */
    public function getByTipoTamanio($tipo, $tamanio) {
        
        $query = $this->getAdapter();
        $sql = $query->select()->from($this->_name)
                ->where("tipo = ?", $tipo)
                ->where("tamanio = ?", $tamanio)
                ->where('active = ?', self::ACTIVO);

        return $query->fetchRow($sql);
        
    }
    
    public function obtenerTarifaAdecsysPerfil($idTarifa) {
        
        $query = $this->getAdapter();
        $sql = $query->select()->from($this->_name,array('Med_Pub_Id','Cod_Med_Pub','Des_Med_Pub',
            'Pub_Id','Cod_Pub','Des_Pub','Edi_Id','Cod_Edi','Des_Edi','Sec_Id','Cod_Sec','Des_Sec',
            'Sub_Sec_Id','Cod_Sub_Sec','Des_Sub_Sec','Ubi_Id','Cod_Ubi','Des_Ubi','Tar_Id','Cod_Tar',
            'Des_Tar','Tipo_Aviso','Form_Pago','Modulo','Esp_Id','Esp_Id','Modulaje','Cod_Sede','Id_Paquete',
            'Id_num_solicitud', 'Id_Item','Aplicado','Tipo_Contrato','Med_Id','Des_Med','Med_Horizontal',
            'Med_Vertical','Val_Moneda','Cod_Moneda','Importe' => 'Valor_Importe'))
                ->where("id_tarifa = ?", $idTarifa)
                ->where('active = ?', self::ACTIVO);

        return $query->fetchRow($sql);
        
    }

}
