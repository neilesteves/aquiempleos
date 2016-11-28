<?php

class Application_Model_Producto extends App_Db_Table_Abstract
{
    protected $_name = "producto";

    const TIPO_CLASIFICADO  = 'clasificado';
    const TIPO_PREFERENCIAL = 'preferencial';

    public function listarBeneficios($id)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('pd' => 'producto_detalle'),
                array(
                'codigo' => 'b.codigo',
                'nombrebeneficio' => 'b.nombre',
                'descbeneficio' => 'b.desc',
                'valor' => 'pd.valor',
                'idbeneficio' => 'b.id',
                'adecsyscode' => 'b.adecsys_code')
            )
            ->join(
                array('b' => 'beneficio'), 'pd.id_beneficio = b.id', array()
            )
            ->join(
                array('p' => $this->_name), 'pd.id_producto = p.id', array()
            )
            ->where('pd.id_producto = ?', $id);
        //echo $sql->assemble(); exit;
        $rs  = $this->getAdapter()->fetchAssoc($sql);

        return $rs;
    }

    public function getTarifasAvisoPreferencial($idProducto, $membresia)
    {

        $cacheId = $this->_prefix.__FUNCTION__.$idProducto;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }

        $sql = $this->getAdapter()->select()
                ->from(array('p' => $this->_name), array())//, array('p.id', 'p.desc'))
                ->joinInner(
                    array('t' => 'tarifa'), 't.id_producto = p.id',
                    array('t.id', 't.precio', 't.medio_pub')
                    //array('t.medio_pub', 't.precio', 't.id')
                )
                ->joinInner(
                    array('ti' => 'tamano_impreso'),
                    'ti.id_producto = p.id AND ti.id = t.id_tamano',
                    array('ti.descripcion', 'ti.path', 'ti.maximo_avisos', 'ti.tamano_centimetro')
                )->joinLeft(
            array('md' => 'membresia_detalle'),
            't.id_membresia_detalle = md.id', array()
        );

        if ($membresia != null) {
            $sql = $sql->joinInner(
                array('m' => 'membresia'),
                $this->getAdapter()->quoteInto('m.id = md.id_membresia AND m.id = ?',
                    $membresia), array()
            );
        } else {
            $sql = $sql->joinLeft(
                array('m' => 'membresia'), 'm.id = md.id_membresia', array()
            );
        }

        $sql = $sql->where('p.id = ?', $idProducto);

        if ($membresia == null) {
            $sql = $sql->where('t.id_membresia_detalle IS NULL');
        }

        $sql = $sql->order("maximo_avisos ASC");
        $sql = $sql->order("id ASC");
        //echo $sql->assemble();
        $rs  = $this->getAdapter()->fetchAll($sql);

        $this->_cache->save(
            $rs, $cacheId, array(),
            $this->_config->cache->AnuncioWebPreferencial->tarifa
        );
        return $rs;
    }

    public function getInformacionAvisoPreferencial($idProd)
    {
        $cacheId = $this->_prefix.__FUNCTION__.$idProd;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $adapter = $this->getAdapter();
        $sql     = "SELECT p.id, p.nombre AS descripcion, MIN(t.precio) AS valor FROM `producto` AS `p`
                INNER JOIN `tarifa` AS `t` ON t.id_producto = p.id
                INNER JOIN `tamano_impreso` AS `ti` ON ti.id_producto = p.id AND ti.id = t.id_tamano
                WHERE p.id = ".$idProd."
                GROUP BY (p.id)
                UNION ALL
                SELECT id_producto AS id, GROUP_CONCAT(descripcion SEPARATOR ',') AS descripcion, 
                '' AS valor FROM tamano_impreso
                WHERE 
                id_producto = ".$idProd."
                UNION ALL
                SELECT p.id , GROUP_CONCAT(b.nombre SEPARATOR ',') AS descripcion, 
                GROUP_CONCAT(pd.valor SEPARATOR ',') AS valor
                FROM producto p
                INNER JOIN producto_detalle pd ON pd.id_producto = p.id 
                INNER JOIN beneficio b ON b.`id` = pd.id_beneficio AND b.id IN (1,3)
                WHERE 
                p.id = ".$idProd.";";
        $stm     = $adapter->query($sql);

        $rs = $stm->fetchAll();

        $this->_cache->save(
            $rs, $cacheId, array(),
            $this->_config->cache->AnuncioWebPreferencial->informacionDePreferenciales
        );
        return $rs;
    }

    public function getInformacionAvisoPreferencialSinMembresia($idProd)
    {
        $cacheId = $this->_prefix.__FUNCTION__.$idProd;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $adapter = $this->getAdapter();
        $sql     = "SELECT p.id, p.nombre AS descripcion, MIN(t.precio) AS valor FROM `producto` AS `p`
                INNER JOIN `tarifa` AS `t` ON t.id_producto = p.id
                INNER JOIN `tamano_impreso` AS `ti` ON ti.id_producto = p.id AND ti.id = t.id_tamano
                WHERE p.id = ".$idProd." AND t.id_membresia_detalle IS NULL
                GROUP BY (p.id)
                UNION ALL
                SELECT id_producto AS id, GROUP_CONCAT(descripcion SEPARATOR ',') AS descripcion, 
                '' AS valor FROM tamano_impreso
                WHERE 
                id_producto = ".$idProd."
                UNION ALL
                SELECT p.id , GROUP_CONCAT(b.nombre SEPARATOR ',') AS descripcion, 
                GROUP_CONCAT(pd.valor SEPARATOR ',') AS valor
                FROM producto p
                INNER JOIN producto_detalle pd ON pd.id_producto = p.id 
                INNER JOIN beneficio b ON b.`id` = pd.id_beneficio AND b.id IN (1,3)
                WHERE 
                p.id = ".$idProd.";";
        $stm     = $adapter->query($sql);

        $rs = $stm->fetchAll();

        $this->_cache->save(
            $rs, $cacheId, array(),
            $this->_config->cache->AnuncioWebPreferencial->informacionDePreferencialesSinMembresia
        );
        return $rs;
    }

    public function getInformacionAvisoClasificado($idProd)
    {
        $cacheId = $this->_prefix.__FUNCTION__.$idProd;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $adapter = $this->getAdapter();
        $sqlP    = $adapter->select()
            ->from(
                array('p' => $this->_name),
                array('p.id', 'descripcion' => 'p.desc', 'valor' => 't.precio')
            )
            ->joinInner(array('t' => 'tarifa'), 't.id_producto = p.id', array())
            ->group('p.id')
            ->where('p.id = ?', $idProd);

        $sqlB = $adapter->select()
            ->from(
                array('p' => $this->_name),
                array(
                'p.id',
                'descripcion' => new Zend_Db_Expr("GROUP_CONCAT(b.`nombre` SEPARATOR ',')"),
                'valor' => new Zend_Db_Expr("GROUP_CONCAT(pd.valor SEPARATOR ',')")
                )
            )
            ->joinInner(array('pd' => 'producto_detalle'),
                'pd.id_producto = p.id', array())
            ->joinInner(array('b' => 'beneficio'),
                'b.`id` = pd.id_beneficio AND b.id IN (1,4)', array())
            ->where('p.id = ?', $idProd);

        $sqlT = $adapter->select()
            ->from(
                array('t' => 'tarifa'),
                array(
                'id' => new Zend_Db_Expr("GROUP_CONCAT(t.id SEPARATOR ',')"),
                'descripcion' => new Zend_Db_Expr("GROUP_CONCAT(t.medio_pub SEPARATOR ',')"),
                'valor' => new Zend_Db_Expr("GROUP_CONCAT(t.precio SEPARATOR ',')")
                )
            )
            ->where('t.id_producto = ?', $idProd);

        $sql = $adapter->select()
            ->union(array($sqlP, $sqlB, $sqlT), Zend_Db_Select::SQL_UNION_ALL);

        $rs = $adapter->fetchAll($sql);

        $this->_cache->save(
            $rs, $cacheId, array(),
            $this->_config->cache->AnuncioWeb->informacionDeClasificados
        );

        return $rs;
    }

    public function getInformacionAvisoWebDestacado($idProd)
    {

        $adapter = $this->getAdapter();
        $sqlP    = $adapter->select()
            ->from(
                array('p' => $this->_name),
                array(
                'p.id',
                'descripcion' => 'p.desc',
                'valor' => 't.precio',
                'id_tarifa' => 't.id',
                'medioPub' => 't.medio_pub'
                )
            )
            ->joinInner(array('t' => 'tarifa'), 't.id_producto = p.id', array())
            // ->group('p.id')
            ->where('t.activo = ?', 1)
            ->where('p.id = ?', $idProd);
        $rs      = $this->getAdapter()->fetchAll($sqlP);
        foreach ($rs as $key => $value) {
            $productosdesc          = $this->getIdProductodetalle($value['id']);
            $rs['detalle_producto'] = $productosdesc;
        }

        return $rs;
    }

    public function getTarifas($tipo = 'web')
    {
        $adapter = $this->getAdapter();
        $sqlP    = $adapter->select()
            ->from(
                array('p' => $this->_name),
                array(
                'p.id',
                'descripcion' => 'p.desc',
                'tipo' => 'p.tipo',
                'valor' => 't.precio',
                'id_tarifa' => 't.id',
                'medioPub' => 't.medio_pub'
                )
            )
            ->joinInner(array('t' => 'tarifa'), 't.id_producto = p.id', array())
            // ->group('p.id')
            ->where('t.activo = ?', 1)
            ->where('t.medio_pub =?', $tipo);
        $rs      = $this->getAdapter()->fetchAll($sqlP);
        foreach ($rs as $key => $value) {
            $productosdesc                = $this->getIdProductodetalle($value['id']);
            $rs[$key]['detalle_producto'] = $productosdesc;
        }

        return $rs;
    }

    public function getIdProductodetalle($id = 0)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('pd' => 'producto_detalle'), array('*'))
            ->joinInner(array('b' => 'beneficio'), 'b.id = pd.id_beneficio',
                array('*'))
            ->where('pd.id_producto = ? ', $id);
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getIdProductoXTarifa($idTarifa)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('p' => $this->_name), array('id'))
            ->joinInner(array('t' => 'tarifa'), 't.id_producto = p.id', array())
            ->where('t.id = ? ', $idTarifa);
        return $this->getAdapter()->fetchOne($sql);
    }

    public function obtenerTipoAviso($idTarifa)
    {
        $sql = $this->getAdapter()->select()
            ->from(array('p' => $this->_name), 'tipo')
            ->joinInner(array('t' => 'tarifa'), 't.id_producto = p.id', array())
            ->where('t.id = ? ', $idTarifa);
        return $this->getAdapter()->fetchOne($sql);
    }

    public function listarExtraCargos($id)
    {
        $sql = $this->getAdapter()->select()
            ->from(
                array('e' => 'extracargos'),
                array('codigoBeneficio' => 'b.codigo',
                'extracargoId' => 'e.id',
                'nombreBeneficio' => 'b.nombre',
                'precioExtracargo' => 'e.precio',
                'adecsysCod' => 'e.adecsys_cod',
                'adecsysCodEnvioDos' => 'e.adecsys_cod_envio_dos',
                'imagen' => 'e.imagen',
                'valorExtracargo' => 'e.valor')
            )
            ->join(
                array('b' => 'beneficio'), 'e.id_beneficio = b.id', array()
            )
            ->join(
                array('t' => 'tarifa'), 'e.id_tarifa = t.id', array()
            )
            ->where('t.id_producto = ?', $id);
        return $this->getAdapter()->fetchRow($sql);
    }

    public function getTarifasImpreso()
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
           //return $this->_cache->load($cacheId);
        }
        $dtSubCategoria=  $this->_config->subClasificcion->toArray();
        $sql  = $this->getAdapter()->select()
            ->from(array('t' => 'tarifa'), array('IdTarifa' => 't.id'))
            ->joinInner(array('p' => 'producto'), 't.id_producto = p.id',
                array('NombreProducto' => 'p.nombre', 'IdProducto' => 'p.id'))
            ->where('t.activo = ? ', 1)
            ->where('p.tipo = ? ', 'clasificado');
        $rs   = $this->getAdapter()->fetchAll($sql);
        $data = array();
        foreach ($rs as $key => $value) {
            $data[$value['IdTarifa']]=$value;
            $data[$value['IdTarifa']]['SubCategoria']=$dtSubCategoria['02'];
            $data[$value['IdTarifa']]['beneficio'] = $this->getIdProductodetalle($value['IdProducto']);
        }
         $this->_cache->save(
            $data, $cacheId, array(),
            3600
        );
       // var_dump($data);EXIT;
        return $data;
    }
}