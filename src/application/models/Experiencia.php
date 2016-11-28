<?php

class Application_Model_Experiencia extends App_Db_Table_Abstract {

    protected $_name = "experiencia";
    
    const LUGAR_CAMPO = 2;
    const LUGAR_OFICINA = 1;
    
    
    public function delete($where) 
    {
        $storage = Zend_Auth::getInstance()->getStorage()->read();
        if (isset($storage['postulante'])) {
            @$this->_cache->remove('Postulante_getUltimaExperiencias_' . $storage['postulante']['id']);
        }
                
        return parent::delete($where);
    }

    public function insert(array $data) 
    {
        if (isset($data['id_postulante'])) {
            @$this->_cache->remove('Postulante_getUltimaExperiencias_' . $data['id_postulante']);            
        }        
        return parent::insert($data);
    }

    public function update(array $data, $where) 
    {
        if (isset($data['id_postulante'])) {
            @$this->_cache->remove('Postulante_getUltimaExperiencias_' . $data['id_postulante']);            
        }        
        return parent::update($data, $where);
    }

    public function getExperiencias($idPostulante) {
        $sql = $this->_db->select()
                ->from(array('e' => 'experiencia'), array(
                    'id_Experiencia' => 'id',
                    'otra_empresa' => 'e.otra_empresa',
                    'otro_rubro' => new Zend_Db_Expr("COALESCE(e.otro_rubro,r.nombre)"),
                    'otro_puesto' => 'e.otro_puesto',
                    'id_nivel_puesto' => 'e.id_nivel_puesto',
                    'id_area' => 'e.id_area',
                    'id_puesto' => 'e.id_puesto',
                    'lugar' => 'e.lugar',
                    'id_tipo_proyecto' => 'e.id_tipo_proyecto',
                    'nombre_proyecto' => 'e.nombre_proyecto',
                    'costo_proyecto' => 'e.costo_proyecto',
                    'inicio_mes' => 'e.inicio_mes',
                    'inicio_ano' => 'e.inicio_ano',
                    'fin_mes' => 'e.fin_mes',
                    'fin_ano' => 'e.fin_ano',
                    'en_curso' => 'e.en_curso',
                    'comentarios' => 'e.comentarios',
                    'otro_nivel_puesto',
                    'nombre_puesto' => 'p.nombre',
                    'nivel_puesto' => 'np.nombre'

                ))
                ->joinleft(array('r' => 'rubro'), 'e.id_rubro = r.id')
                ->joinInner(array('p' => 'puesto'), 'e.id_puesto = p.id', array())
                ->joinInner(array('ep' => 'empresa_puesto'), 'e.id_puesto = ep.id_puesto', array())
                ->joinInner(array('np' => 'nivel_puesto'), 'e.id_nivel_puesto = np.id', array())
                ->joinInner(array('enp' => 'empresa_nivel_puesto'), 'e.id_nivel_puesto = enp.id_nivel_puesto', array())
                ->joinInner(array('a' => 'area'), 'e.id_area = a.id', array())
                ->joinInner(array('ea' => 'empresa_area'), 'e.id_area = ea.id_area', array())
//
                ->where('np.activo = 1')
                ->where('e.id_postulante = ?', $idPostulante)
                ->group('e.id')
                ->order('inicio_ano DESC')
                ->order('inicio_mes DESC');
       // echo $sql;
        return $this->_db->fetchAll($sql);
    }


    public function getExperiencia($idExperiencia) {
        $sql = $this->_db->select()
            ->from(array('e' => 'experiencia'), array(
                'id_Experiencia' => 'id',
                'otra_empresa' => 'e.otra_empresa',
                'otro_rubro' => new Zend_Db_Expr("COALESCE(e.otro_rubro,r.nombre)"),
                'otro_puesto' => 'e.otro_puesto',
                'id_nivel_puesto' => 'e.id_nivel_puesto',
                'id_area' => 'e.id_area',
                'id_puesto' => 'e.id_puesto',
                'lugar' => 'e.lugar',
                'id_tipo_proyecto' => 'e.id_tipo_proyecto',
                'nombre_proyecto' => 'e.nombre_proyecto',
                'costo_proyecto' => 'e.costo_proyecto',
                'inicio_mes' => 'e.inicio_mes',
                'inicio_ano' => 'e.inicio_ano',
                'fin_mes' => 'e.fin_mes',
                'fin_ano' => 'e.fin_ano',
                'en_curso' => 'e.en_curso',
                'comentarios' => 'e.comentarios',
                'otro_nivel_puesto',
                'nombre_puesto' => 'p.nombre',
                'nivel_puesto' => 'np.nombre'
            ))
            ->joinleft(array('r' => 'rubro'), 'e.id_rubro = r.id')
            ->joinInner(array('p' => 'puesto'), 'e.id_puesto = p.id', array())
            ->joinInner(array('ep' => 'empresa_puesto'), 'e.id_puesto = ep.id_puesto', array())
            ->joinInner(array('np' => 'nivel_puesto'), 'e.id_nivel_puesto = np.id', array())
            ->joinInner(array('enp' => 'empresa_nivel_puesto'), 'e.id_nivel_puesto = enp.id_nivel_puesto', array())
            ->joinInner(array('a' => 'area'), 'e.id_area = a.id', array())
            ->joinInner(array('ea' => 'empresa_area'), 'e.id_area = ea.id_area', array())
//
            ->where('np.activo = 1')
            ->where('e.id = ?', $idExperiencia)
            ->group('e.id')
            ->order('inicio_ano DESC')
            ->order('inicio_mes DESC');
        // echo $sql;
        return $this->_db->fetchRow($sql);
    }

    public function getExperiencias_old($idPostulante) {
        $db = $this->getAdapter();
        $sql = $db->select()
                ->from(
                        array('e' => $this->_name), array(
                    'id_Experiencia' => 'id',
                    'otra_empresa' => 'e.otra_empresa',
                    'otro_rubro' => new Zend_Db_Expr("COALESCE(e.otro_rubro,r.nombre)"),
                    'otro_puesto' => 'e.otro_puesto',
                    'id_nivel_puesto' => 'e.id_nivel_puesto',
                    'id_area' => 'e.id_area',
                    'inicio_mes' => 'e.inicio_mes',
                    'inicio_ano' => 'e.inicio_ano',
                    'fin_mes' => 'e.fin_mes',
                    'fin_ano' => 'e.fin_ano',
                    'en_curso' => 'e.en_curso',
                    'comentarios' => 'e.comentarios'
                        )
                )
                ->joinleft(
                        array('r' => 'rubro'), 'e.`id_rubro` = r.id'
                )
                ->where('e.id_postulante = ?', $idPostulante)
                ->order('inicio_ano DESC')
                ->order('inicio_mes DESC');
        return $db->fetchAll($sql);
    }

    public function getExperienciaMiCuenta($idPostulante) {
        $listaExperiencia = array();
        foreach ($this->getExperiencias($idPostulante) as $valor) {
            $puesto = $valor['otro_puesto'];
            if ($valor['id_puesto'] != Application_Model_Puesto::OTROS_PUESTO_ID) {
                $puesto = strtolower ($valor['nombre_puesto']);
                $puesto = ucfirst($puesto);
            }
                
            $listaExperiencia[$valor['id_Experiencia']] = $puesto . " en " . $valor['otra_empresa'];
        }
        return $listaExperiencia;
    }

    public function verificarEmpresaLinkendUno($nomEmpresa, $idPos) {

        $db = $this->getAdapter();
        $sql = $db->select()->from($this->_name)
                ->where("otra_empresa = ?", $nomEmpresa)
                ->where('id_postulante = ?', $idPos);
        return $db->fetchAll($sql);
    }

    public function verificarEmpresaLinkendDos($nomEmpresa, $idPos, $mes, $ano) {
        $db = $this->getAdapter();
        $sql = $db->select()->from($this->_name)
                ->where("otra_empresa like ?", '%' . $nomEmpresa . '%')
                ->where('id_postulante = ?', $idPos)
                ->where('inicio_mes = ?', $mes)
                ->where('inicio_ano = ?', $ano);
        return $db->fetchAll($sql);
    }

    public function obtenerIdExperiencia($postulante) {

        $db = $this->getAdapter();
        $sql = $db->select()->from($this->_name,'id')
                ->where("id_postulante = ?",$postulante)
                ->order('inicio_ano DESC')
                ->order('inicio_mes DESC');
        
        return $db->fetchAll($sql);
    }
    public function obtenerMesesExperiencia($postulanteId) {
        $adapter = $this->getAdapter();
        $postulanteId = $adapter->quote($postulanteId);       
        $sqlE = "SELECT IFNULL(SUM(
            (IFNULL(ex.fin_ano,YEAR(CURRENT_DATE()))
            *12+IFNULL(ex.fin_mes,MONTH(CURRENT_DATE())))-
            (ex.inicio_ano*12+ex.inicio_mes)),0)
            FROM experiencia ex
            WHERE ex.id_postulante=$postulanteId";
        $stmp = $adapter->query($sqlE);
        $stmp->execute();
        return $stmp->fetchColumn();         
    }
    public function getLogPostulanteExperianciaTotal ($idPostulante )
    {
        if( !is_numeric($idPostulante) || !$idPostulante > 0 ) {
            return false;
        }

        $sql = $this->getAdapter()->select()
            ->from($this->_name,array(
                'id'
                //    'num' => new Zend_Db_Expr('count(1)'),
                ))
            ->where('id_postulante = ?', (int)$idPostulante)
            ->limit(1);

        $res= $this->getAdapter()->fetchAll($sql);
        return (count($res)>0)?true:false;
    }
    public function getExperienciaReferencias($idPostulante) {
        $listaExperiencia = array();
        $ModelReferencias= new Application_Model_Referencia();
        foreach ($ModelReferencias->getReferenciasPostulante($idPostulante) as $valor) {
            $puesto = $valor['otro_puesto'];
            if ($valor['id_puesto'] != Application_Model_Puesto::OTROS_PUESTO_ID) {
                $puesto = strtolower ($valor['nombre_puesto']);
                $puesto = ucfirst($puesto);
            }                
            $listaExperiencia[$valor['id_experiencia']] = $puesto . " en " . $valor['empresa'];
        }
        return $listaExperiencia;
    }
}
