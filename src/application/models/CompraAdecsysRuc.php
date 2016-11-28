<?php

class Application_Model_CompraAdecsysRuc extends App_Db_Table_Abstract {

    protected $_name = "compra_adecsys_ruc";
    
    const RUC = 'RUT';

    //Obtiene registro buscado por el id_compra
    public function getRegistroByCompra($idCompra) {

        $sql = $this->getAdapter()->select()->from($this->_name)
                ->where('id_compra = ?', $idCompra);

        $data = $this->getAdapter()->fetchRow($sql);

        if ($data) {
            return $data;
        }

        return null;
    }

    public function registrar($reg) {

        $data['id_compra'] = $reg->compra;
        $data['ruc'] = $reg->Num_Doc;
        $data['razon_social'] = $reg->RznSoc_Nombre;
        $data['tipo_via'] = $reg->Tip_Calle;
        $data['direccion'] = $reg->Nom_Calle;
        $data['creado_por'] = $reg->idUser;
        $data['fh_creacion'] = date('Y-m-d H:i:s');
        $data['nro_puerta'] = $reg->Num_Pta;
        return $this->insert($data);
    }

    public function obteneterRegistroByCompraPostulante($idPos, $idCompra) {

        $sql = $this->getAdapter()->select()->from(array('c' => 'compra'), 
                    array(
                        'car.ruc',
                        'car.razon_social',
                        'car.tipo_via',
                        'car.direccion',
                          'car.direccion','car.nro_puerta',
                        'telefono' => new Zend_Db_Expr("IF(p.telefono = '','00000',p.telefono)")
                    )
                )
                ->joinInner(array('car' => $this->_name), 'car.id_compra = c.id', null)
                ->joinInner(array('p' => 'postulante'), 'p.id = c.id_postulante', null)
                ->where('c.id_postulante = ?', $idPos)
                ->where('c.id = ?', $idCompra);

        $data = $this->getAdapter()->fetchRow($sql);
        
        if ($data) {
            return $data;
        }
        
        return null;
        
        
    }
    
    public function registrarCompraMembresia($data)
    {
      $data['fh_creacion'] = date('Y-m-d H:i:s');
      return $this->insert($data);
    }
    public function registrarCompraAviso($data) 
    {
      $data['fh_creacion'] = date('Y-m-d H:i:s');     
      return $this->insert($data);
    }

}
