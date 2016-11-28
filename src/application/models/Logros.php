<?php

class Application_Model_Logros extends App_Db_Table_Abstract {

    protected $_name = "logros";

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
    
    public function getLogrosPostulante($idpostulante) {
        $chooseMonth = " CASE l.mes
                WHEN '1' THEN 'Enero'
                WHEN '2' THEN 'Febrero'
                WHEN '3' THEN 'Marzo'
                WHEN '4' THEN 'Abril'
                WHEN '5' THEN 'Mayo'
                WHEN '6' THEN 'Junio'
                WHEN '7' THEN 'Julio'
                WHEN '8' THEN 'Agosto'
                WHEN '9' THEN 'Setiembre'
                WHEN '10' THEN 'Octubre'
                WHEN '11' THEN 'Noviembre'
                WHEN '12' THEN 'Diciembre'
                END ";
        
        $sql = $this->getAdapter()->select()
                ->from(array('l'=>$this->_name), array(
                    'id_logros'=>'l.id',
                    'txtPrize'=>'l.logro',
                    'txtInstitution'=>'l.institucion',
                    'txtDateAchievement'=>'l.ano',
                    'selDate'=>'l.mes',
                    'txtDescription'=>'l.descripcion',
                    'txtMonth' => new Zend_Db_Expr($chooseMonth)
                    ))
                ->where('id_postulante =?', $idpostulante);
        return $this->getAdapter()->fetchAll($sql);
    }

    /**
     * Retorna los datos de un logro
     * @param int $id Id del logro
     * @return array
     */
    public function getLogroXId($id)
    {
        $sql = $this->getAdapter()->select()
                ->from(array('e' => $this->_name),array('*'))
                ->where('e.id = ? ', $id);
        return ($this->getAdapter()->fetchAll($sql));
    }
    public function getLogPostulanteLogrosTotal ($idPostulante )
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name,array(
                'id'
                  //  'num' => new Zend_Db_Expr('count(1)'),      
                ))
            ->where('id_postulante = ?', $idPostulante)
             ->limit(1) ;
                         
        $res= $this->getAdapter()->fetchAll($sql);
        return (count($res)>0)?true:false;
    }
}
