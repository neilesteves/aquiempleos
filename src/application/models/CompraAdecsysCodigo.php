<?php

class Application_Model_CompraAdecsysCodigo extends App_Db_Table_Abstract
{
    protected $_name = "compra_adecsys_codigo";

    const MEDIO_PUB_APTITUS         = 'aptitus';
    const MEDIO_PUB_TALAN           = 'talan';
    const MEDIO_PUB_APTITUS_COMBO   = 'aptitus_combo';
    const MEDIO_PUB_TALAN_COMBO     = 'talan_combo';
    const MEDIO_PUB_DIARIO_LAPRENSA = 'empleo_laprensa';
    const MEDIO_PUB_DIARIO_EMPLEO = 'empleo';
    const TYPE_COMBO = 'combo';

    public function getCodAdecsysByCodCompra($idCompra)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name, array('id', 'adecsys_code'))
            ->where('id_compra = ?', $idCompra)
            ->limit('1');
        return $this->getAdapter()->fetchRow($sql);
    }

    public function asignarCodigoImpreso($id, $codigoImpreso)
    {
        $where = $this->getAdapter()->quoteInto(
            'id =?', (int) $id);

        $data                 = array();
        $data['adecsys_code'] = $codigoImpreso;
        $data['fh_envio']     = date('Y-m-d H:i:s');

        $this->update($data, $where);
    }

    public function getIDCodCompra()
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name, array('id_compra', 'adecsys_code'))
            ->where('adecsys_code IS NULL')
            ->limit('1');
//   echo $sql->assemble(); exit;
        return $this->getAdapter()->fetchAll($sql);
    }

    //Función usada para el reproceso de envío de avisos a Adecsys.
    //Inserta en la tabla compra_adecsys_codigo aquellas que no tienen adecsys y no se grabo en esta tabla.
    public function generaRegistrosFaltantes($compraSinAdecsys)
    {

        $db = $this->getAdapter();

        try {

            $db->beginTransaction();
            if (count($compraSinAdecsys) > 0) {
                //De acuerdo a la tarifa (combos, talan) se crean 1 ó 2 registros en la tabla compra_adecsys_codigo
                foreach ($compraSinAdecsys as $avisosSinAdecsys) {

                    $medioPub = $avisosSinAdecsys['medio_pub'];
                    $idCompra = $avisosSinAdecsys['id'];

                    $medAPT   = Application_Model_Tarifa::MEDIOPUB_APTITUS;
                    $medTALAN = Application_Model_Tarifa::MEDIOPUB_TALAN;

                    if ($medioPub == $medAPT) {
                        //Valida existencia de registro
                        if (!$this->validaRegistro($idCompra, $medAPT)) {
                            $this->insert(array('id_compra' => $idCompra, 'medio_publicacion' => $medAPT));
                        }
                    } else if ($medioPub == $medTALAN) {
                        //Valida existencia de registro
                        if (!$this->validaRegistro($idCompra, $medTALAN)) {
                            $this->insert(array('id_compra' => $idCompra, 'medio_publicacion' => $medTALAN));
                        }
                    } else if ($medioPub == Application_Model_Tarifa::MEDIOPUB_APTITUS_TALAN) {
                        //Valida existencia de registro
                        if (!$this->validaRegistro($idCompra, 'aptitus_combo')) {
                            $this->insert(array('id_compra' => $idCompra, 'medio_publicacion' => 'aptitus_combo'));
                        }
                        //Valida existencia de registro
                        if (!$this->validaRegistro($idCompra, 'talan_combo')) {
                            $this->insert(array('id_compra' => $idCompra, 'medio_publicacion' => 'talan_combo'));
                        }
                    }
                }
            }
            $db->commit();
        } catch (Zend_Db_Exception $e) {
            $db->rollBack();
            echo $e->getMessage();
        } catch (Zend_Exception $e) {
            echo $e->getMessage();
        }
    }

    public function obtenerRegistroCAC($idCac)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name, array('medio_publicacion'))
            ->where('id = ?', $idCac);

        return $this->getAdapter()->fetchRow($sql);
    }

    public function obtenerAdecsysCAC($idCac)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name, array('adecsys_code'))
            ->where('id = ?', $idCac);

        return $this->getAdapter()->fetchRow($sql);
    }

    public function obtenerAdecsysCACTalan($idCompra)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name, array('adecsys_code', 'id'))
            ->where('id_compra = ?', $idCompra)
            ->where('medio_publicacion = ?', 'talan_combo');

        return $this->getAdapter()->fetchRow($sql);
    }

    public function verificaTieneCombo($idCompra)
    {

        //SELECT * FROM compra_adecsys_codigo WHERE id_compra = 694802 AND medio_publicacion = 'aptitus_combo'
        $medPub = self::MEDIO_PUB_APTITUS_COMBO;

        $sql = $this->getAdapter()->select()
            ->from($this->_name, array('id'))
            ->where('id_compra = ?', $idCompra)
            ->where('medio_publicacion = ?', $medPub);

        $data = $this->getAdapter()->fetchAll($sql);

        if (count($data) > 0) return false;
        else return true;
    }

    public function getByIdCompra($id)
    {
        $result = $this->fetchAll($this->select()
                ->from($this->_name)
                ->where('id_compra =?', $id));
        if (!empty($result)) return $result->toArray();
        else return array();
    }

    //Función que valida si existe el registro en la tabla compra_adecsys_codigo
    public function validaRegistro($idCompra, $medio)
    {

        $sql = $this->getAdapter()->select()->from($this->_name, array('id'))
            ->where('id_compra = ?', $idCompra)
            ->where('medio_publicacion = ?', $medio);

        return $this->getAdapter()->fetchOne($sql);
    }
}