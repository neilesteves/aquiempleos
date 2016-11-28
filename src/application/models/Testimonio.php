<?php

class Application_Model_Testimonio extends App_Db_Table_Abstract
{

    const ESTADO_ACTIVO = 'activo';
    const ESTADO_INACTIVO = 'inactivo';
    protected $_name = "testimonios";
    
    private $_model = null;

    public function __construct()
    {
        parent::__construct();
        $cparts = explode('_', __CLASS__);
        $this->_model = $cparts[2];
    }
    
    public function getPaginadorBusquedaPersonalizada($empresa, $referente, $col, $ord)
    {
        $paginadoBusqueda = $this->_config->administrador->gestion->paginadoBusqueda;
        $p = Zend_Paginator::factory(
            $this->getBusquedaPersonalizada(
                $empresa, $referente, $col, $ord
            )
        );
        return $p->setItemCountPerPage($paginadoBusqueda);
    }

    public function getBusquedaPersonalizada($empresa, $referente, $col='', $ord='')
    {
        $col = $col == '' ? 'e.estado' : $col;
        $ord = $ord == '' ? 'ASC' : $ord;

        $sql = $this->getAdapter()->select()
            ->from(
                array('e'=>$this->_name),
                array('e.id', 'e.id_usuario', 'e.testimonio', 'e.referente', 'e.orden', 'e.estado',
                      'razon_social'=>'e.razonsocial', 'num_ruc'=>'e.referente')
            )
            ->joinInner(
                array('u'=> 'usuario'), 'u.id = e.id_usuario',
                array('u.email', 'u.activo', 'u.fh_registro')
            );
        if ($empresa != null) {
            $sql = $sql->where('e.razonsocial like ?', '%'.$empresa.'%');
        }
        if ($referente != null) {
            $sql = $sql->where('e.referente like ?', '%'.$referente.'%');
        }
        /*if(($ord == '') && ($col == '')){
            $sql = $sql->order(' e.estado ASC ');
            $sql = $sql->order(' e.orden ASC ');
        }*/
        $sql = $sql->order(sprintf('%s %s', $col, $ord));
        $sql = $sql->order(' orden ASC ');
        // -- echo $sql; exit;
//        return $this->getAdapter()->fetchAll($sql);
        
        return $sql;

    }

    public function getTestimoniosActivos()
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from($this->_name)
            ->where("estado = 'activo'");
        $result = $db->fetchAll($sql);
        return count($result);
    }

    public function registrarTestimonio($testimonio, $nroTestimonios)
    {
        $procR = false;
        $db = $this->getAdapter();

        $sql = $db->select()
            ->from($this->_name)
            ->where('orden = ?', $testimonio['orden']);
        $result = $db->fetchRow($sql);
        //var_dump($testimonio); exit;
        if (($result) && ($testimonio['estado'] != 'inactivo')) {
            $sql = $this->select()
                ->from($this->_name)
                ->where("estado = 'activo'")
                ->order(' estado ASC')
                ->order(' orden ASC');
            $resultOne = $db->fetchAll($sql);
            //var_dump($result1);
            $pos = 0;
            for ($a = 0; $a < $nroTestimonios; $a++) {
                //echo $result1[$a]['orden'];
                if (@$resultOne[$a]['orden'] != ($a + 1)) {
                    $pos = ($a+1);
                    break;
                }
                if ($a == 50) { 
                    exit;
                }
            }
            $resultTwo = $this->insert($testimonio);
            if ($pos != 0) {
                $resultThree = $this->update(
                    array(
                        'orden' => $pos
                    ), $this->getAdapter()->quoteInto('id = ?', $result['id'])
                );
            } else {
                $resultThree = $this->update(
                    array(
                        'estado' => 'inactivo',
                        'orden' => '0'
                    ), $this->getAdapter()->quoteInto('id = ?', $result['id'])
                );
            }
            
            if (($resultTwo == true) && ($resultThree == true)) {
                $procR = true;
            }
        } else {
            if ($testimonio['estado'] == 'inactivo') {
                $testimonio['orden'] = '0';
                $resultOne = $this->insert($testimonio);
                if ($resultOne == true) {
                    $procR = true;
                }
            } else {
                $resultOne = $this->insert($testimonio);
                if ($resultOne == true) {
                    $procR = true;
                }
            }
        }
        return $procR;
    }

    public function  getTestimonioById($testimonioId)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from($this->_name)
            ->where('id = ?', $testimonioId);
        return $db->fetchRow($sql);
    }

    public function updateTestimonio($data, $id)
    {        
        $procR = false;
        //--------- Si su ESTADO es ACTIVO y ORDEN diferente de 0
        if (($data['estado'] != 'inactivo') && ($data['orden'] != 0)) {
            //-- Obteniendo id de testimonio que tiene el orden            
            $db = $this->getAdapter();
            $sql = $db->select()
                ->from($this->_name)
                ->where('orden = ?', $data['orden']);
            $result = $db->fetchRow($sql);
            if ($result) {
                $db = $this->getAdapter();
                $sql = $db->select()
                    ->from($this->_name)
                    ->where('id = ?', $id);
                $resultTwo = $db->fetchRow($sql);

                //--- Si los ID de la busqueda por ORDEN y ID recuperado son iguales
                if ($result['id'] == $id) {
                    $resultFour = $this->update(
                        array(
                            'razonsocial' => $data['razonsocial'],
                            'ubicacion' => $data['ubicacion'],
                            'testimonio' => $data['testimonio'],
                            'referente' => $data['referente'],
                            'estado' => $data['estado'],
                            'cargo' => $data['cargo'],
                            'fecha_modificacion' => $data['fecha_modificacion'],
                            'orden' => $result['orden']
                            ), $this->getAdapter()->quoteInto('id = ?', $id)
                    );                    
                    //if ($resultFour == true) {
                        $procR = true;
                    //}
                } else {
                    $resultThree = $this->update(
                        array(
                            'estado' => 'activo',
                            'orden' => $resultTwo['orden']
                            ), $this->getAdapter()->quoteInto('id = ?', $result['id'])
                    );
                    $resultFour = $this->update(
                        array(
                            'razonsocial' => $data['razonsocial'],
                            'ubicacion' => $data['ubicacion'],
                            'testimonio' => $data['testimonio'],
                            'referente' => $data['referente'],
                            'estado' => $data['estado'],
                            'cargo' => $data['cargo'],
                            'fecha_modificacion' => $data['fecha_modificacion'],
                            'orden' => $result['orden']
                            ), $this->getAdapter()->quoteInto('id = ?', $id)
                    );
                    if (($resultThree == true) || ($resultFour == true)) {
                        $procR = true;
                    }
                }                
            } else {
                $resultFour = $this->update(
                    array(
                        'razonsocial' => $data['razonsocial'],
                        'ubicacion' => $data['ubicacion'],
                        'testimonio' => $data['testimonio'],
                        'referente' => $data['referente'],
                        'estado' => $data['estado'],
                        'cargo' => $data['cargo'],
                        'fecha_modificacion' => $data['fecha_modificacion'],
                        'orden' => $data['orden']
                        ), $this->getAdapter()->quoteInto('id = ?', $id)
                );                
                if ($resultFour == true) {
                    $procR = true;
                }
            }
        //---- SI el estado es Inactivo y orden mayor a 0
        } else {
            $resultOne = $this->update(
                array(
                    'razonsocial' => $data['razonsocial'],
                    'ubicacion' => $data['ubicacion'],
                    'testimonio' => $data['testimonio'],
                    'referente' => $data['referente'],
                    'cargo' => $data['cargo'],
                    'fecha_modificacion' => $data['fecha_modificacion'],
                    'estado' => $data['estado'],
                    'orden' => $data['orden']
                    ), $this->getAdapter()->quoteInto('id = ?', $id)
            );
            //if ($resultOne == true) {
                $procR = true;
            //}
        }
        /* --------------------------------------------------------------------------
        $result = false;
        $result = $this->update($data, $this->getAdapter()->quoteInto('id = ?', $id));
        if ($result) {
            $result = true;
        }
        return $result;
        --------------------------------------------------------------------------- */
        
        return $procR;
    }

    public function desactivarTestimonio($testimonioId)
    {        
        return $this->update(
            array('estado' => 'inactivo', 'orden' => '0'),
            $this->getAdapter()->quoteInto('id = ?', $testimonioId)
        );
        
    }
    
    public function ordenDisponibleTestimonio($estado)
    {
        /*$db = $this->getAdapter();
        $sql = $db->select('orden')
            ->from($this->_name)
            ->where('estado = ?', $estado)
            ->order('orden ASC');
        return $db->fetchAll($sql);*/
    }

    public function actualizarOrdenTestimonio($testimonioId, $orden, $nroTestimonios)
    {
        $procR = false;
        $db = $this->getAdapter();
        //-- Obteniendo id de testimonio que tiene el orden
        $sql = $db->select()
            ->from($this->_name)
            ->where('orden = ?', $orden);
        $result = $db->fetchRow($sql);
        if ($result) {
            $sql = $this->select()
                ->from($this->_name)
                ->where("estado = 'activo'")
                ->order(' estado ASC')
                ->order(' orden ASC');
            $resultOne = $db->fetchAll($sql);
            //var_dump($result1);
            $pos = 0;
            for ($a = 0; $a < $nroTestimonios; $a++) {
                //echo $result1[$a]['orden'];
                if (@$resultOne[$a]['orden'] != ($a + 1)) {
                    $pos = ($a+1);
                    break;
                }
                if ($a == 50) {
                    exit;
                }
            }
            $resultTwo = $this->update(
                array(
                    'estado'=>'activo',
                    'orden'=>$orden
                    ), $this->getAdapter()->
                quoteInto('id = ?', $testimonioId)
            );
            $resultThree = $this->update(
                array(
                    'orden' => $pos
                    ), $this->getAdapter()->quoteInto('id = ?', $result['id'])
            );
            if (($resultTwo == true) && ($resultThree == true)) {
                $procR = true;
            }
        } else {
            $resultOne = $this->update(
                array(
                    'estado' => 'activo',
                    'orden' => $orden
                    ), $this->getAdapter()->quoteInto('id = ?', $testimonioId)
            );
            if ($resultOne == true) {
                $procR = true;
            }
        }
        return $procR;
    }

    public function listarTestimonios()
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from($this->_name)
            ->where("estado = 'activo'")
            ->order(' estado ASC')
            ->order(' orden ASC')
            ->limit($this->_config->testimonios->cantidad->testimonio);
        $result = $db->fetchAll($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

}