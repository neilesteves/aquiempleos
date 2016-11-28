<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Description of Membresia
 *
 * @author Favio Condori
 */
class Application_Model_EmpresaMembresia extends App_Db_Table_Abstract
{
    protected $_name = 'empresa_membresia';
    
    const ESTADO_VIGENTE = 'vigente';
    const ESTADO_NO_VIGENTE = 'no vigente';
    const ESTADO_PAGADO = 'pagado';
    const ESTADO_POR_PAGAR = 'por pagar';

    public function getDetalleEmpresaMembresia($id,$buqueda =false)
    {
        $db = $this->getAdapter();
        $detalle = array();
        $sql = $db->select()
            ->from(
                array('em' => $this->_name), 
                array(
                    'fh_inicio_membresia', 
                    'fh_fin_membresia', 
                    'id_membresia', 
                    'monto', 
                    'nro_contrato', 
                    'estado'
                )
            )->join(
                array('m' => 'membresia'), 
                'em.id_membresia = m.id',
                array('m_nombre' => 'm.nombre',
                'm_tipo' => 'tipo')
            )->where('em.id = ?', $id);
            
        $detalle['membresia'] = $db->fetchRow($sql);
        
        $sql = $db->select()
            ->from(
                array('em' => $this->_name), array()
            )->join(
                array('med' => 'membresia_empresa_detalle'),
                'med.id_empresa_membresia = em.id',
                array(
                    'med_nombre' => 'med.nombre', 
                    'med_descripcion' => 'med.descripcion', 
                    'med_tipo_beneficio' => 'med.tipo_beneficio', 
                    'med_codigo' => 'med.codigo', 
                    'med_valor' => 'med.valor'
                )
            )->where('em.id = ?', $id)
            ->order('med.codigo DESC')
            ->order('med.id_beneficio ASC');
        
      
        if($buqueda){
                $sql->where("med.codigo IN ('memprem-web','memprem-imp','memprem-adic','memsele-web','memsele-imp','memsele-adic','memesen-web','memesen-imp','memesen-adic','memdigi','memmens')");
         }
        
        $detalle['beneficios'] = $db->fetchAll($sql);
        
        return $detalle;
    }
    
    
    public function getDetalleEmpresaMembresiaByIdCompra($idCompra, $idEmpresa = null)
    {
        $db = $this->getAdapter();
        $detalle = array();
        $sql = $db->select()
            ->from(
                array('em' => $this->_name), 
                array(
                    'fh_inicio_membresia', 
                    'fh_fin_membresia', 
                    'id_membresia', 
                    'monto', 
                    'estado'
                )
            )->join(
                array('m' => 'membresia'), 
                'em.id_membresia = m.id',
                array('m_nombre' => 'm.nombre',
                'm_tipo' => 'tipo')
            )
            ->join(
                array('c' => 'compra'), 
                'em.id = c.id_empresa_membresia',
                array(
                    'cip' => 'c.cip',
                    'totalPrecio' => 'c.precio_total',
                    'codigoBarras' => 'c.cod_barra'
                )
            )
            ->where('c.id = ?', $idCompra);
        
        if ($idEmpresa) {
            $sql->where('em.id_empresa', $idEmpresa);
        }
          
        $detalle = $db->fetchRow($sql);
        //$detalle['membresia'] = $db->fetchRow($sql);
        
        /*$sql = $db->select()
            ->from(
                array('em' => $this->_name), array()
            )->join(
                array('med' => 'membresia_empresa_detalle'),
                'med.id_empresa_membresia = em.id',
                array(
                    'med_nombre' => 'med.nombre', 
                    'med_descripcion' => 'med.descripcion', 
                    'med_tipo_beneficio' => 'med.tipo_beneficio', 
                    'med_codigo' => 'med.codigo', 
                    'med_valor' => 'med.valor'
                )
            )->where('em.id = ?', $id)
            ->order('med_descripcion ASC');
        //echo $sql->assemble();
        $detalle['beneficios'] = $db->fetchAll($sql);*/
        
        return $detalle;
    }
    
    
    public function getRow($id)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(
                array('em' => $this->_name), array('*')
            )->where('em.id = ?', $id);
        return $db->fetchRow($sql);
    }
    
    public function getExistsActive($idempresa, $exceptDigital = false)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(array('em'=> $this->_name), array('estado'))
            ->where('em.id_empresa = ?', $idempresa);
        
        if ($exceptDigital) {
            $sql->where('em.id_membresia NOT IN (?,?,?)',array(7,Application_Model_Membresia::DIGITAL,Application_Model_Membresia::MENSUAL) );
        }
        
        $rs = $db->fetchAll($sql);
        $exist = false;
        foreach ($rs as $value) {
            if ($value['estado']==Application_Model_EmpresaMembresia::ESTADO_VIGENTE) {
                return true;
//                $exist = true;
//                break;
            }
        }
        return $exist;
    }
    
    
       public function getEmpresaMemSig($idempresa,$id=null)
    {
        
       
           
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(array('em'=> $this->_name), array( 
                    'fh_ini'=>'em.fh_inicio_membresia', 
                   'fh_fin'=> 'em.fh_fin_membresia', 
                    'id'=>'em.id'))
            ->where('em.id_empresa = ?', $idempresa)
            ->where('em.estado = ?', 'vigente');       
        
        $vigente = $db->fetchAll($sql);
        
        
        $db = $this->getAdapter();
        $sql = $db->select()
            ->from(array('em'=> $this->_name), array( 
                    'fh_ini'=>'em.fh_inicio_membresia', 
                   'fh_fin'=> 'em.fh_fin_membresia', 
                    'id'=>'em.id'))
            ->where('em.id_empresa = ?', $idempresa)
            ->where('em.estado = ?', 'pagado')       ;
       if($id!=null){
        $sql->where('em.id!=?',$id);
       }
        $sql->order('em.fh_fin_membresia DESC');
        $pagado = $db->fetchAll($sql);
        
        $rs = array();
        if(count($vigente)>0){
           $rs= $vigente;
        }
        
         if(count($pagado)>0){
           $rs= $pagado;
        }
        
        
        return $rs;
    }
    
    
    public function getDetalleEmpresaMembresiaActivaByIdEmpresa($idEmpresa)
    {
        $db = $this->getAdapter();
        $detalle = array();
        $sql = $db->select()
            ->from(
                array('em' => $this->_name), 
                array(
                    'fh_inicio_membresia', 
                    'fh_fin_membresia', 
                    'id_membresia', 
                    'monto', 
                    'estado'
                )
            )->joinInner(
                array('m' => 'membresia'), 
                'em.id_membresia = m.id',
                array('m_nombre' => 'm.nombre',
                'm_tipo' => 'tipo')
            )->where('em.estado = ?', 'vigente')
            ->where('em.id_empresa = ?', $idEmpresa);
            
        $detalle['membresia'] = $db->fetchRow($sql);
//            var_dump($detalle['membresia']);
        if (empty($detalle['membresia'])) {
            return false;
        }
        
        $sql = $db->select()
            ->from(
                array('em' => $this->_name), array()
            )->join(
                array('med' => 'membresia_empresa_detalle'),
                'med.id_empresa_membresia = em.id',
                array(
                    'med_nombre' => 'med.nombre', 
                    'med_descripcion' => 'med.descripcion', 
                    'med_tipo_beneficio' => 'med.tipo_beneficio', 
                    'med_codigo' => 'med.codigo', 
                    'med_valor' => 'med.valor'
                )
            )->where('em.id_empresa = ?', $idEmpresa)
            ->where('em.id_membresia = ?', $detalle['membresia']['id_membresia'])
             ->order(' med_descripcion ASC');
//        echo $sql->assemble();exit;
        $detalle['beneficios'] = $db->fetchAll($sql);
        return $detalle;
    }
     public function getMembresiaCompraDetalle($idcompra)
    {
        $db = $this->getAdapter();
    
        $sql = $db->select()
            ->from(
                array('em' => $this->_name), 
                array(
                    'fh_inicio_membresia', 
                    'fh_fin_membresia', 
                    'id_membresia', 
                    'monto', 
                    'estado'
                )
            )->join(
                array('m' => 'membresia'), 
                'em.id_membresia = m.id',
                array('m_nombre' => 'm.nombre',
                'm_tipo' => 'tipo')
                    
                    
            )
                
                
            ->join(
                array('c' => 'compra'), 
                'em.id = c.id_empresa_membresia',
                array('estadoCompra' => 'c.estado'))    
          
            ->where('c.id = ?', $idcompra);
         
        $detalle = $db->fetchRow($sql);
     
       
        return $detalle;
    }
    
    /**
     * 
     * Activar la siguiente membresia de la empresa 
     * con  el estado vigente
     *
     * @access public
     * @param int $idEmpresa    Id de la empresa
     * @param int $fecha        Fecha de inicio de la membresia
     * @return int
     */        
    public function activarSiguienteMembresia($idEmpresa, $fecha)
    {
        
        $db = $this->getAdapter();
        $updated = 0;
        
        /// Activara las membresias excepto digital al estado Vigente
        $where = $db->quoteInto('em.id_empresa = ?', $idEmpresa);
        $where .= ' AND em.nro_contrato <> "" ';
        $where .= ' AND '.$db->quoteInto('em.estado = ?', Application_Model_EmpresaMembresia::ESTADO_PAGADO);
        $where .= ' AND em.fh_inicio_membresia > "'.$fecha.'" ';
        $where .= ' AND (em.id_membresia <> '.Application_Model_Membresia::DIGITAL.' OR em.id_membresia <> 7 OR em.id_membresia <> '.Application_Model_Membresia::MENSUAL.')';        
        
        $sql = $db->select()->from(
                    array('em' => 'empresa_membresia'),
                    array('em_id' => 'em.id')
                )->joinInner(
                    array('c' => 'compra'), 
                    'c.id_empresa_membresia = em.id', 
                    array('c_IdCompra' => 'c.id',
                          'idEmp'=>'c.id_empresa')
                )->where($where)->order('em.fh_creacion ASC');
        
        $membresia = $db->fetchRow($sql);        
        if ($membresia) {                             
            $updated = $this->update(
                array(
                    'estado' => Application_Model_EmpresaMembresia::ESTADO_VIGENTE
                ),
                $db->quoteInto('id = ?', $membresia['em_id'])
            );
            
            if ($updated > 0) {
                // Notificar a Facturacion y a la empresa de la activacion de su 
                // membresia comprada:
                $helper= new App_Controller_Action_Helper_Api();
                $helper->CreaRegistroApi($membresia['idEmp']);
                
                
                //$this->notificarFacturacionMembresia($membresia['c_IdCompra']);
                $this->notificarActivacionMembresia($membresia['c_IdCompra']);
                
                echo 'Notificaciones enviadas a Facturacion y al Cliente (Activacion)'.PHP_EOL;
                
                return $updated;
            }                                             
        } 
        
        // Si tiene membresia activa que no sea digital
        if (!$this->getExistsActive($idEmpresa,true)) {
            echo 'Tiene Membresias que no son digitales '.PHP_EOL;
            $updated = $this->ActivarSiguienteMembresiaDigital($idEmpresa);
        }
        
        return $updated;
    }
    
    /**
     * 
     * Activar y notificar de la siguiente membresia Digital de la empresa 
     * con  el estado pagado
     *
     * @access public
     * @param int $idEmpresa    Id de la empresa
     * @param int $fecha        Fecha de inicio de la membresia
     * @return void
     */        
    public function ActivarSiguienteMembresiaDigital($idEmpresa,$idEmp = null, $pasarela = false)
    {
        
        $db = $this->getAdapter();        
        $sqlEmpMem = $db->select()
                ->from(
                    array(
                        'em' => 'empresa_membresia'
                    ), 
                    array(
                        'em_id' => 'em.id',
                        'em_idMembresia' => 'em.id_membresia',
                        'em_idEmpresa' => 'em.id_empresa'
                    )
                )
                ->joinInner(
                    array(
                        'c'=> 'compra'
                    ), 
                    'c.id_empresa_membresia = em.id',
                    array(
                        'c_idCompra' => 'c.id'
                    )
                )
                ->where('em.estado = ?', Application_Model_EmpresaMembresia::ESTADO_PAGADO)
                ->where('em.id_empresa = ?',$idEmpresa)
                ->order('em.fh_inicio_membresia ASC');
        
        if (!$pasarela) {
            $sqlEmpMem->where('em.id_membresia  IN (?,?,?)',array(7,Application_Model_Membresia::DIGITAL,Application_Model_Membresia::MENSUAL) );
        }
        
        if ($idEmp!=null) {
            $sqlEmpMem->where('em.id = ?',$idEmp);
        }
                                
        $membresia = $db->fetchRow($sqlEmpMem);
      //  echo $sqlEmpMem;exit;
        $config = Zend_Registry::get('config');
        if($membresia['em_idMembresia'] == Application_Model_Membresia::MENSUAL)
            $meses = 1;
        else
        $meses = $config->membresias->digital->duracion;
        if(!$membresia){
            return false ;
        }
        
        
        if (!$pasarela) {
           $actualizado = $this->update(
                array(
                    'estado' => Application_Model_EmpresaMembresia::ESTADO_VIGENTE,
                    'fh_inicio_membresia' => new Zend_Db_Expr('NOW()'),
                    'fh_fin_membresia' => new Zend_Db_Expr('DATE_ADD(NOW(), INTERVAL '.$meses.' MONTH)'),
                ),
                $db->quoteInto('id = ?',(int) $membresia['em_id'])
            );
        } else {
            $tieneMembr = $this->tieneMembresias($idEmpresa,$membresia['em_id']);            
            if(!$tieneMembr) {
                // Empresa nueva:
                $actualizado = $this->update(
                    array(
                        'estado' => Application_Model_EmpresaMembresia::ESTADO_VIGENTE,
                    ),
                    $db->quoteInto('id = ?',(int) $membresia['em_id'])                        
                );
                
            }   
            
            if ($idEmp!=null) {
                $this->notificarFacturacionMembresia($membresia['c_idCompra'],true);                 
            }                
            
            $actualizado = 0;
        }
        

        if ($actualizado > 0) {
            
            if ($idEmp!=null) {
                $this->notificarFacturacionMembresia($membresia['c_idCompra'],true);                 
            }
            $this->notificarActivacionMembresia($membresia['c_idCompra']);
            if (php_sapi_name() == 'cli') {
                if ($idEmp!=null) {
                echo 'Notificacion de Activacion de Membresia Digital enviada idMembresia = ' . $membresia['em_id'] . "\n";
                }
            }             

        }
        
        return $actualizado;
        
    }
    
    
    /**
     * 
     * Activar la membresia si y solo si la membresia se encuentra 
     * con el estado de pagago.     
     *
     * @access public
     * @param int $token    Token de EmpresaMembresia     
     * @return int
     */  
    public function activarMembresiaByToken($token)
    {
        $where = $this->getAdapter()->quoteInto('token = ?', $token);
        $where .= ' AND nro_contrato <> "" ';
        $where .= ' AND '.$this->getAdapter()->quoteInto('estado = ?', Application_Model_EmpresaMembresia::ESTADO_PAGADO);
        $updated = 
                $this->update(
                        array(
                            'estado' => Application_Model_EmpresaMembresia::ESTADO_VIGENTE
                        ),
                        $where
                );
        
        return $updated;
        
    }
    
    /**
     * 
     * Enviar email al usuario-empresa que realizo la compra de la membresia
     *
     * @access public
     * @param int $idCompra                 Id de la compra     
     * @return void
     */
    public function notificarActivacionMembresia($idCompra)
    {
        $modelCompra =  new Application_Model_Compra();
        $compra = $modelCompra->getDetalleCompraMembresia($idCompra);
        if ($compra) {
            $mailer = new App_Controller_Action_Helper_Mail();
                        
            // Notificacion a la empresa de la activacion de la membresia                        
            $modelEmpresaMembresia = new Application_Model_MembresiaEmpresaDetalle();
            $beneficios = $modelEmpresaMembresia->obtenerBeneficiosPorEmpresaMembresia(
                    $compra['IdEmprMemb'],true
            );

            $modelUsuarioEmpresa = new Application_Model_UsuarioEmpresa();
            $usuarioEmpresa = $modelUsuarioEmpresa->getUsuarioEmpresaByIdUsuario(
                    $compra['usuario']
            );
            
            $idMembresia = $compra['idMembresia'];
            $config = Zend_Registry::get('config');
            $meses = $config->membresias->digital->duracion;
            
            $asunto = 'Activación de Membresía Anual';
            $tiempo = ' un año ';
            if ($idMembresia == Application_Model_Membresia::DIGITAL) {
                $asunto = 'Activación de Membresía';
                $tiempo = ' '.$meses.' meses ';
            }
            if ($idMembresia == Application_Model_Membresia::MENSUAL) {
                $asunto = 'Activación de Membresía';
                $tiempo = ' 1 mes ';
            }
            
            $dataAdmin = array(            
                'Asunto'=> $asunto,
                'nombreUsuario'  => $usuarioEmpresa['nombres'],
                'inicio_plan'  => date('d/m/Y', strtotime($compra['fechaInicioMembresia'])),
                'fin_plan'  => date('d/m/Y', strtotime($compra['fechaFinMembresia'])),
                'usuario' => $usuarioEmpresa['nombres'],
                'empresa' => $compra['razonSocial'],                    
                'nombreMembresia' =>$compra['nombreMembresia'],
                'beneficios' => $beneficios,
                'tipoMembresia' => $compra['idMembresia'],
                'tiempo' => $tiempo
            );

            $dataAdmin['to'] = $compra['emailContacto'];                                            
            $mailer->notificacionAdminMembresiaActiva($dataAdmin);

        }
        
    }
    
    
    /**
     * 
     * Enviar email para notificar la facturacion de la membresia
     *
     * @access public
     * @param int $idCompra                 Id de la compra
     * @param boolean $membresiaDigital     Si es Memb. Digital, 
     *                                      por defecto es false
     * @return void
     */
    public function notificarFacturacionMembresia($idCompra, $membresiaDigital = false)
    {
        
        $modelCompra =  new Application_Model_Compra();
        $compra = $modelCompra->getDetalleCompraMembresia($idCompra);
        if ($compra) {
            $mailer = new App_Controller_Action_Helper_Mail();
            $config = Zend_Registry::get('config');
            
            // Notificacion a Facturacion                        
            $dataFacturacion = array(            
                'Asunto'=>'FACTURAR EL ADECSYS '.$compra['codigoAdecsys'].' (PLAN DE MEMBRESIA '.strtoupper($compra['nombreMembresia']).')',
                'razonSocial' => $compra['razonSocial'],
                'razonSocialCompraEmpresa' => $compra['razonSocial'],                            
                'nombreMembresia' => strtoupper($compra['nombreMembresia']),
                'nroContrato' => ($membresiaDigital ? 'No requiere' : $compra['nroContratoMembresia']),
                'ente' => $compra['codigoEnte'],
                'ruc' => $compra['numDocumento'],
                'fechaPago' => date('d/m/Y', strtotime($compra['fechaPago'])),
                'cip' => $compra['cip'],
                'importe' => $compra['montoTotal'],
                'adecsys' => $compra['codigoAdecsys'],
                'tokenCompra' => $compra['tokenCompra'],
                'descripcionMembresia' => $compra['descripcionMembresia'],
            );

            $administrador = $config->membresias->administrador;            
            $dataFacturacion['to'] = $administrador->facturacion->email;
            $dataFacturacion['addBcc'] = $administrador->facturacion->emailcopia;
            $mailer->notificacionFacturacionMembresia($dataFacturacion);
            
        }

        
        
    }
    
    
    public function existeNroContrato($nroContrato, $id = null)
    {
        $db = $this->getAdapter();
                
        $filter = new Zend_Filter_Alnum();
        $nroContrato = $filter->filter($nroContrato);

        $sql = $db->select()
                ->from(array('em'=> $this->_name), array('id'))
                ->where('em.nro_contrato = ?', $nroContrato);
        if ($id) {
            $sql->where('em.id != ?',$id);
        }
        $rows = $db->fetchAll($sql);
       if ($id) {
            return (count($rows) > 1) ? true : false;
        } else {
            return (count($rows) >= 1) ? true : false;
        }
        
        
    }
    
    public function tieneMembresias($idEmpresa, $excepto = null)
    {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(array('em'=> $this->_name), array('id'))
                ->where('em.id_empresa = ?', $idEmpresa)
                ->where(new Zend_Db_Expr("em.estado IN ('".Application_Model_EmpresaMembresia::ESTADO_VIGENTE."','".Application_Model_EmpresaMembresia::ESTADO_PAGADO."')"));
        if (!empty($excepto)) {
            $sql->where('em.id <> ?',$excepto);                    
        }        
        $rows = $db->fetchRow($sql);
        return $rows;
                
    }
    
    
}


