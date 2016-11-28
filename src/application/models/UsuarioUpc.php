<?php

class Application_Model_UsuarioUpc extends App_Db_Table_Abstract {

    protected $_name = "usuario_upc";
    
    const RECIBIR_INFO = 1;
    const NO_RECIBIR_INFO = 0;

    public function registrar($data) {
        
        $tipoDoc = array(1 => 'CI', 2 => 'PASAPORTE', 3 => 'CARNÉ DE EXTRANJERÍA');
        
        $dataReg = array();
        $dataReg['nacionalidad'] = $data['rdNacionalidad'];
        $dataReg['nombres'] = $data['txtNombres'];
        $dataReg['ape_pat'] = $data['txtApePat'];
        $dataReg['ape_mat'] = $data['txtApeMat'];
        $dataReg['tipo_doc'] = $tipoDoc[$data['selDocument']];
        $dataReg['numero_doc'] = $data['txtDocument'];
        $dataReg['sexo'] = $data['rdSexo'];
        $dataReg['fh_nacimiento'] = $data['fecNac'];
        $dataReg['celular'] = $data['txtCelphone'];
        $dataReg['email'] = $data['txtEmail'];
        $dataReg['nivel_academico'] = $data['selAcademicLevel'];
        $dataReg['ocupacion'] = $data['selOcupation'];
        $dataReg['area_interes'] = $data['selInteres'];
        $dataReg['fh_registro'] = date('Y-m-d H:i:s');

        if (isset($data['rdEmailing'])) {
            $dataReg['recibir_info'] = self::RECIBIR_INFO;
        } else {
            $dataReg['recibir_info'] = self::NO_RECIBIR_INFO;
        }
        
        return $this->insert($dataReg);
    }
    
    public function getUsuariosTCN($dia = null)
    {
        
        $sql = $this->getAdapter()->select()
            ->from($this->_name);
        if ($dia) {
            $sql->where('DATE(fh_registro) = ? ', $dia);
        }
        
        $result = $this->getAdapter()->fetchAll($sql);
        return $result;
        
    }
    

}
