<?php

class Application_Model_BolsaCv extends App_Db_Table_Abstract {

    protected $_name = "bolsa_cv";
    protected $_model = "";

    const GROUP_TCN = "Trabaja con Nosotros";
    const GROUP_GENERAL = "GENERAL";

    public function __construct() {
        parent::__construct();
        $cparts = explode('_', __CLASS__);
        $this->_model = $cparts[2];
    }

    public function crearNuevoGrupo($idEmpresa, $nombre, $esEditable = true, $padre = null) {
        $this->insert(
                array('id_empresa' => $idEmpresa, 'nombre' => $nombre, 'es_editable' => $esEditable,
                    'id_bolsa_cv' => $padre, 'fecha_registro' => date("Y-m-d H:i:s"))
        );
    }

    public function cambiarNombreGrupo($idBolsaCv, $nombre) {
        if ($this->esEditable($idBolsaCv)) {
            return $this->update(
                            array('nombre' => $nombre), $this->getAdapter()->quoteInto('id = ?', $idBolsaCv)
            );
        }

        return false;
    }

    public function eliminarGrupo($idBolsaCv, $salvarCVs = false, $idBolsaGeneral = null) {
        if ($this->esEditable($idBolsaCv)) {
            if ($salvarCVs) {
                $bcvPostulanteModel = new Application_Model_BolsaCvPostulante();
                $bcvPostulanteModel->cambiarBolsaCV($idBolsaCv, $idBolsaGeneral);
            }
            return $this->delete($this->getAdapter()->quoteInto('id = ?', $idBolsaCv));
        }

        return false;
    }

    public function existeNombre($nombre, $idEmpresa) {
        $sql = $this->getAdapter()->select()
                ->from(
                        array('bcv' => $this->_name), array('cant' => 'count(bcv.id)')
                )
                ->where('REPLACE(LOWER(bcv.nombre), " ", "") LIKE REPLACE(LOWER(?), " ", "")', $nombre)
                ->where('bcv.id_empresa = ?', $idEmpresa);

        $cant = $this->getAdapter()->fetchOne($sql);

        if ($cant > 0) {
            return true;
        }

        return false;
    }

    public function existeNombre2($nombre, $idEmpresa) {
        $sql = $this->getAdapter()->select()
                ->from(
                        array('bcv' => $this->_name), array('id' => 'bcv.id')
                )
                ->where('REPLACE(LOWER(bcv.nombre), " ", "") LIKE REPLACE(LOWER(?), " ", "")', $nombre)
                ->where('bcv.id_empresa = ?', $idEmpresa);

        return $this->getAdapter()->fetchOne($sql);
    }

    public function getGruposSinPostulante($idPostulante, $idEmpresa) {
        $sql = $this->getAdapter()->select()
                ->from(
                        array('bcv' => $this->_name), array('id' => 'bcv.id', 'nombre' => "bcv.nombre")
                )->joinLeft(
                        array("bcvp" => "bolsa_cv_postulante"), "bcv.id = bcvp.id_bolsa_cv AND bcvp.id_postulante = " . $idPostulante, array("idBolsaPostulante" => "bcvp.id")
                )
                ->where('bcv.id_empresa = ?', $idEmpresa)
                ->where('(NOT bcvp.id_postulante = ? OR bcvp.id_postulante IS NULL)', $idPostulante)
                ->order("bcv.id");
        //echo $sql->assemble(); exit;
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getGruposEmpresaSinPostulantes($idEmpresa, $idPostulantes) {
        $sql = $this->getAdapter()->select()
                ->from(
                        array('bcv' => $this->_name), array('id' => 'bcv.id', 'idEmpresa' => 'bcv.id_empresa', 'cant' => "COUNT(bcvp.id_postulante)",
                    'nombre' => 'bcv.nombre', "editable" => 'bcv.es_editable')
                )->joinLeft(
                        array("bcvp" => "bolsa_cv_postulante"), $this->getAdapter()->quoteInto(
                                "bcv.id = bcvp.id_bolsa_cv AND bcvp.id_postulante in (?) ", $idPostulantes
                        ), array("idBolsaPostulante" => "bcvp.id")
                )->where('bcv.id_empresa = ?', $idEmpresa)
                ->group("bcv.id")
                ->having("cant < ?", count($idPostulantes))
                ->order("bcv.id");
        //echo $sql->assemble(); exit;
        $grupos = $this->getAdapter()->fetchAll($sql);

        return $grupos;
    }

    /**
     * 
     * @param type $idempresa
     */
    public function createGrupoTcn($idempresa) {
        $getIdGrupo = $this->existeNombre(self::GROUP_TCN, $idempresa);
        if (empty($getIdGrupo)) {
            $this->crearNuevoGrupo($idempresa, self::GROUP_TCN, FALSE);
        }
    }

    public function createGrupoGeneral($idempresa) {
        $getIdGrupo = $this->existeNombre('GENERAL', $idempresa);
        if (empty($getIdGrupo)) {
            $this->crearNuevoGrupo($idempresa, 'GENERAL', FALSE);
        }
    }

    public function getGruposEmpresa($idEmpresa) {
        $sql = $this->getAdapter()->select()
                ->from(
                        array("bcv" => $this->_name), array('id' => 'id', 'idEmpresa' => 'id_empresa',
                    'nombre' => 'nombre', "editable" => 'es_editable')
                )->joinLeft(
                        array("bcvp" => "bolsa_cv_postulante"), "bcvp.id_bolsa_cv = bcv.id", array("cant" => "count(bcvp.id)")
                )->where('bcv.id_empresa = ?', $idEmpresa)
                ->group("bcv.id")
                ->order("bcv.nombre");

        $this->createGrupoGeneral($idEmpresa);
        $grupos = $this->getAdapter()->fetchAll($sql);


        return $grupos;
    }

    public function getGrupo($idGrupo) {
        $sql = $this->getAdapter()->select()
                        ->from(
                                array($this->_name), array('id' => 'id', 'idEmpresa' => 'id_empresa',
                            'nombre' => 'nombre', "editable" => 'es_editable')
                        )->where('id = ?', $idGrupo);

        return $this->getAdapter()->fetchRow($sql);
    }

    public function esEditable($idGrupo) {
        $sql = $this->getAdapter()->select()
                        ->from(
                                array($this->_name), array("editable" => 'es_editable')
                        )->where('id = ?', $idGrupo);

        $esEditable = $this->getAdapter()->fetchOne($sql);
        if ($esEditable == "1") {
            return true;
        }

        return false;
    }

    public function getGrupoGeneralEmpresa($idEmpresa) {
        $sql = $this->getAdapter()->select()
                ->from(
                        array($this->_name), array('id' => 'id', 'idEmpresa' => 'id_empresa',
                    'nombre' => 'nombre', "editable" => 'es_editable')
                )->where('id_empresa = ?', $idEmpresa)
                ->where('nombre = ?', self::GROUP_GENERAL)
                ->where("es_editable = 0")
                ->limit(1);
        return $this->getAdapter()->fetchRow($sql);
    }

    public function perteneceGrupoAEmpresa($idGrupo, $idEmpresa) {
        $sql = $this->_db->select()
                ->from(array('bcv' => $this->_name), array('cant' => 'count(bcv.id)'))
                ->where('bcv.id = ?', $idGrupo)
                ->where('bcv.id_empresa = ?', $idEmpresa);

        $cant = $this->_db->fetchOne($sql);

        if ($cant > 0) {
            return true;
        }

        return false;
    }

    public function getPuestosEmpresa($idGrupo = 1) {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
       
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $idGrupo;
       //  var_dump($cacheId) ;exit;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->_db->select()
                ->from(array('bcp' => 'bolsa_cv_postulante'), array('pe.id', 'pe.nombre'))
                ->joinInner(array('e' => 'experiencia'), 'e.id_postulante = bcp.id_postulante', array())
                ->joinInner(array('pe' => 'puesto'), 'pe.id = e.id_puesto', array())
                ->where('bcp.id_bolsa_cv = ?', $idGrupo)
                ->group('pe.id')
                ->order('pe.nombre');
        $puestos = $this->_db->fetchAll($sql);
        $this->_cache->save($puestos, $cacheId, array(), $cacheEt);
        return $puestos;
    }

    public function getNivelPuestosEmpresa($idGrupo = 1) {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $idGrupo;
        
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->_db->select()
                ->from(array('bc' => 'bolsa_cv'), array('np.id', 'np.nombre'))
                ->joinInner(array('bcp' => 'bolsa_cv_postulante'), 'bc.id = bcp.id_bolsa_cv', array())
                ->joinInner(array('e' => 'experiencia'), 'e.id_postulante = bcp.id_postulante', array())
                ->joinInner(array('np' => 'nivel_puesto'), 'np.id = e.id_nivel_puesto', array())
                ->where('bc.id = ?', $idGrupo)
                ->group('np.id')
                ->order('np.nombre');
        $nivelpuestos = $this->_db->fetchAll($sql);
        $this->_cache->save($nivelpuestos, $cacheId, array(), $cacheEt);
        return $nivelpuestos;
    }

    public function getAreaEmpresa($idGrupo = 1) {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $idGrupo;
     
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->_db->select()
                ->from(array('bc' => 'bolsa_cv'), array('a.id', 'a.nombre'))
                ->joinInner(array('bcp' => 'bolsa_cv_postulante'), 'bc.id = bcp.id_bolsa_cv', array())
                ->joinInner(array('e' => 'experiencia'), 'e.id_postulante = bcp.id_postulante', array())
                ->joinInner(array('a' => 'area'), 'a.id = e.id_area', array())
                ->where('bc.id = ?', $idGrupo)
                ->group('a.id')
                ->order('a.nombre');
        $area = $this->_db->fetchAll($sql);
        $this->_cache->save($area, $cacheId, array(), $cacheEt);
        return $area;
    }

    public function getNivelEstudioEmpresa($idGrupo = 1,$otros='') {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $idGrupo;
        if($otros==true){
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_1' . $idGrupo;
        }
        //var_dump($cacheId) ;exit;
           
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        
        
        if($otros==true){
                 $sql = $this->_db->select()
                ->from(array('bc' => 'bolsa_cv'), array('ne.id', 'ne.nombre'))
                ->joinInner(array('bcp' => 'bolsa_cv_postulante'), 'bc.id = bcp.id_bolsa_cv', array())
                ->joinInner(array('e' => 'estudio'), 'e.id_postulante = bcp.id_postulante', array())
                ->joinInner(array('ne' => 'nivel_estudio'), 'ne.id = e.id_nivel_estudio', array())
                ->where('bc.id = ?', $idGrupo)
               ->where("ne.padre LIKE ?", "%9%")
                ->group('ne.id')
                ->order('ne.nombre');
        }else{
               $sql = $this->_db->select()
                ->from(array('bc' => 'bolsa_cv'), array('ne.id', 'ne.nombre'))
                ->joinInner(array('bcp' => 'bolsa_cv_postulante'), 'bc.id = bcp.id_bolsa_cv', array())
                ->joinInner(array('e' => 'estudio'), 'e.id_postulante = bcp.id_postulante', array())
                ->joinInner(array('ne' => 'nivel_estudio'), 'ne.id = e.id_nivel_estudio', array())
                ->joinInner(array('empre' => 'empresa_nivel_estudio'), 'empre.id_nivel_estudio = ne.id', array())
                ->where('bc.id = ?', $idGrupo)
                ->where('empre.id_empresa = ?', 1)
                ->group('ne.id')
                ->order('ne.nombre');
        }
        
     
        $nivelestudios = $this->_db->fetchAll($sql);
        $this->_cache->save($nivelestudios, $cacheId, array(), $cacheEt);
        return $nivelestudios;
    }
    
    

    public function getTipoCarreraEmpresa($idGrupo = 1) {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $idGrupo;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->_db->select()
                ->from(array('bc' => 'bolsa_cv'), array('tc.id', 'tc.nombre'))
                ->joinInner(array('bcp' => 'bolsa_cv_postulante'), 'bc.id = bcp.id_bolsa_cv', array())
                ->joinInner(array('e' => 'estudio'), 'e.id_postulante = bcp.id_postulante', array())
                ->joinInner(array('tc' => 'tipo_carrera'), 'tc.id = e.id_tipo_carrera', array())
                ->where('bc.id = ?', $idGrupo)
                ->group('tc.id')
                ->order('tc.nombre');
        $tipocarrera = $this->_db->fetchAll($sql);
        $this->_cache->save($tipocarrera, $cacheId, array(), $cacheEt);
        return $tipocarrera;
    }

    public function getExperienciaEmpresa($idGrupo = 1) {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $idGrupo;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $arreglo = array();

        $sql = $this->getAdapter()->select()
                ->from(array('bolsa_cv_postulante'), array('id' => 'id'))
                ->where('id_bolsa_cv = ?', $idGrupo);
        $postulantes = $this->_db->fetchAll($sql);

        if (count($postulantes) > 0) {
            $config = Zend_Registry::get('config');
            $rango = $config->experiencia->tiempo->rango->toArray();
            $keys = array_keys($rango);
            $i = 0;
            for ($i = 0; $i < count($keys) - 1; $i++) {
                if ($i == 0) {
                    $arreglo[$i]["nombre"] = $i . " - " . $rango[$keys[$i]];
                    $arreglo[$i]["id"] = $i . "-" . $keys[$i];
                } else {
                    $arreglo[$i]["nombre"] = $rango[$keys[$i - 1]] . " - " . $rango[$keys[$i]];
                    $arreglo[$i]["id"] = $keys[$i - 1] . "-" . $keys[$i];
                }
            }
            //$arreglo[$i]["nombre"]=$rango[$keys[$i]]." - Más";
            $arreglo[$i]["nombre"] = $rango[$keys[$i]];
            $arreglo[$i]["id"] = $keys[$i] . "-600";
        }

        $this->_cache->save($arreglo, $cacheId, array(), $cacheEt);
        return $arreglo;
    }

    public function getIdiomasEmpresa($idGrupo = 1) {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $idGrupo;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $i = array();

        $sql = $this->getAdapter()->select()
                ->from(array('bolsa_cv_postulante'), array('id' => 'id'))
                ->where('id_bolsa_cv = ?', $idGrupo);
        $postulantes = $this->_db->fetchAll($sql);

        if (count($postulantes) > 0) {
            $idiomas = $this->_config->enumeraciones->lenguajes->toArray();
            $x = 0;
            foreach ($idiomas as $index => $item) {
                $i[$x]["id"] = $index;
                $i[$x]["nombre"] = $item;
                $x++;
            }
        }
        $this->_cache->save($i, $cacheId, array(), $cacheEt);
        return $i;
    }

    public function getProgramaEmpresa($idGrupo = 1) {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $idGrupo;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->_db->select()
                ->from(array('bcp' => 'bolsa_cv_postulante'), array('pc.id', 'pc.nombre'))
                ->joinInner(array('dpc' => 'dominio_programa_computo'), 'bcp.id_postulante = dpc.id_postulante', array())
                ->joinInner(array('pc' => 'programa_computo'), 'dpc.id_programa_computo = pc.id', array())
                ->where('bcp.id_bolsa_cv = ?', $idGrupo)
                ->group('pc.id')
                ->order('pc.nombre');
        $result = $this->_db->fetchAll($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getCarreraEmpresa($idGrupo = 1) {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $idGrupo;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->_db->select()
                ->from(array('bcp' => 'bolsa_cv_postulante'), array('c.id', 'c.nombre'))
                ->joinInner(array('e' => 'estudio'), 'bcp.id_postulante = e.id_postulante', array())
                ->joinInner(array('c' => 'carrera'), 'c.id = e.id_carrera', array())
                ->where('bcp.id_bolsa_cv = ?', $idGrupo)
                ->group('c.id')
                ->order('c.nombre');
        $result = $this->_db->fetchAll($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getTipoProyectoEmpresa($idGrupo = 1) {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $idGrupo;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->_db->select()
                ->from(array('bcp' => 'bolsa_cv_postulante'), array('tp.id', 'tp.nombre'))
                ->joinInner(array('e' => 'experiencia'), 'bcp.id_postulante = e.id_postulante', array())
                ->joinInner(array('tp' => 'tipo_proyecto'), 'tp.id = e.id_tipo_proyecto', array())
                ->where('bcp.id_bolsa_cv = ?', $idGrupo)
                ->group('tp.id')
                ->order('tp.nombre');
        $result = $this->_db->fetchAll($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function getUbicacionEmpresa($idGrupo = 1) {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $idGrupo;
     
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->_db->select()
                ->from(array('bcp' => 'bolsa_cv_postulante'), array('u.id', 'u.nombre'))
                ->joinInner(array('p' => 'postulante'), 'p.id = bcp.id_postulante', array())
                ->joinInner(array('u' => 'ubigeo'), 'u.id = p.id_ubigeo', array())
                ->where('bcp.id_bolsa_cv = ?', $idGrupo)
                ->group('u.id')
                ->order('u.nombre');
        $result = $this->_db->fetchAll($sql);
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }

    public function listarPostulantes(
    $idgrupo = '', $col = '', $ord = '', $puestos = '', $nivelpuesto = '', $area = '', $nivelestudio = '', $tipocarrera = '', $experiencia = '', $idiomas = '', $programas = '', $carreras = '', $tipodeproyecto = '', $ubicacion = '', $conadis = '', $q = ''
    ) {
        if (empty($idgrupo)) {
            return array();
        }

        $identifier = $idgrupo;
        $identifier .= (is_array($puestos)) ? implode('', $puestos) : '';
        $identifier .= (is_array($nivelpuesto)) ? implode('', $nivelpuesto) : '';
        $identifier .= (is_array($area)) ? implode('', $area) : '';
        $identifier .= (is_array($nivelestudio)) ? implode('', $nivelestudio) : '';
        $identifier .= (is_array($tipocarrera)) ? implode('', $tipocarrera) : '';
        $identifier .= (is_array($experiencia)) ? implode('', $experiencia) : '';
        $identifier .= (is_array($idiomas)) ? implode('', $idiomas) : '';
        $identifier .= (is_array($programas)) ? implode('', $programas) : '';
        $identifier .= (is_array($tipodeproyecto)) ? implode('', $tipodeproyecto) : '';
        $identifier .= (is_array($ubicacion)) ? implode('', $ubicacion) : '';
        $identifier .= (is_array($conadis)) ? implode('', $conadis) : '';
        $identifier .= (is_array($q)) ? implode('', $q) : '';

        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . md5($identifier);
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }

        if ($q != "") {
            $q = str_replace("%", "", $q);
            $q = str_replace("\\", "", $q);
            $q = str_replace("*", "", $q);
            //$q = strtolower($q);
        }

        $col = ($col == '') ? 'nodefinido string ASC' : $col . " string " . $ord;

        $order = $col;
        $query = "(bcp.id_bolsa_cv = {$idgrupo})";

        //Busqueda por Puesto
        if ($puestos != "") {
            $str = implode(',', $puestos);
            $query .= ' AND (exp.id_puesto IN (' . $str . ')) ';
        }

        //Busqueda por Nivel Puesto
        if ($nivelpuesto != "") {
            $str = implode(',', $nivelpuesto);
            $query .= ' AND (exp.id_nivel_puesto IN (' . $str . ')) ';
        }

        //Busqueda por Area
        if ($area != "") {
            $str = implode(',', $area);
            $query .= ' AND (exp.id_area IN (' . $str . ')) ';
        }
        //Busca por discapacidad
        if ($conadis != "") {            
            $query .= ' AND (p.conadis_code IS NOT NULL) ';
        }
        //Busqueda por Nivel Estudio
        if ($nivelestudio != "") {
       $qe='';
             $query.= ' AND (';
                    for ($i = 0; $i < count($nivelestudio); $i++) {
                        
                       // $ne = $ne . " ne.id =" . $nivelestudio[$i] . " OR ";
                        $nivel1=strstr($nivelestudio[$i], ',', true);;
                        $SubnivelEstudio=trim(strstr($nivelestudio[$i], ','),',');
                         if($nivel1!='9'){ 
                        if($SubnivelEstudio != '0'){
                            $nivel2="AND (est.id_nivel_estudio_tipo= ".$SubnivelEstudio.") OR ";
                        }
                        
                        if($SubnivelEstudio == '0'){
                            if($nivel1==1 ||$nivel1==2 || $nivel1==3 ){
                                $nivel2="OR ";
                              $nivel1=$nivel1;
                             }else{
                               $nivel2="OR ";
                              $nivel1=0;
                             }

                        }
                        $query.= "(est.id_nivel_estudio =" . $nivel1 . ")  ".$nivel2 ;  
                         }else{
                        $nivel2="AND (es.id_nivel_estudio_tipo= ".$SubnivelEstudio.") OR";
                        $ne= $ne. "((es.id_nivel_estudio =" . $nivel1 . ") ".$nivel2 ;  
                         }
                   }
                 
                $query= substr($query, 0, strlen($query) -3).')';   
        }
        
//      if($niveldeOtrosestudios!=''){
//                          $nes = "";
//                        
//                    for ($i = 0; $i < count($niveldeOtrosestudios); $i++) {
//                        
//                       // $ne = $ne . " ne.id =" . $nivelestudio[$i] . " OR ";
//                        $nivelotros1=strstr($niveldeOtrosestudios[$i], ',', true);;
//                        $nivelotros2=trim(strstr($niveldeOtrosestudios[$i], ','),',');
//                        $nivel2="AND (est.id_nivel_estudio_tipo= ".$nivelotros2.") OR ";
//                        $query.= "(est.id_nivel_estudio =" . $nivelotros1 . ") ".$nivel2 ;  
//                         
//                    }
//                     $query = substr($query, 0, strlen($query) - 3);   
//                      
//                }
        //echo $query;
        //Busqueda por Tipo Carrera
        if ($tipocarrera != "") {
            $str = implode(',', $tipocarrera);
            $query .= ' AND (est.id_tipo_carrera IN (' . $str . ')) ';
        }

        //Dominio programa computo
        if ($experiencia != "") {
            $str_exp = array();
            foreach ($experiencia as $exp) {
                list($ini, $fin) = explode('-', $exp);
                $str_exp[] = '(((exp.fin_ano - exp.inicio_ano)*12)+(exp.fin_mes - exp.inicio_mes))';
            }
            $str = implode(' OR ', $str_exp);
            $query .= ' AND (' . $str . ') ';
        }

        //Dominio programa computo
        if ($idiomas != "") {
            $str = implode('","', $idiomas);
            $query .= ' AND (di.id_idioma IN ("' . $str . '")) ';
        }

        //Dominio programa computo
        if ($programas != "") {
            $str = implode(',', $programas);
            $query .= ' AND (dpc.id_programa_computo IN (' . $str . ')) ';
        }

        //Busqueda por Tipo Carrera
        if ($carreras != "") {
            $str = implode(',', $carreras);
            $query .= ' AND (est.id_carrera IN (' . $str . ')) ';
        }

        //Busqueda por Tipo Carrera
        if ($tipodeproyecto != "") {
            $str = implode(',', $tipodeproyecto);
            $query .= ' AND (exp.id_tipo_proyecto IN (' . $str . ')) ';
        }

        //Busqueda por Tipo Carrera
        if ($ubicacion != "") {
            $str = implode(',', $ubicacion);
            $query .= ' AND (p.id_ubigeo IN (' . $str . ')) ';
        }

        //Busqueda por textoy campos relacionados
        if ($q != "" && !empty($q)) {
            $fields = array(
                'p.nombres',
                'p.apellidos',
                'p.apellido_paterno',
                'p.apellido_materno',
                'p.num_doc'
            );
            $nq = array();
            foreach ($q as $v) {
                foreach ($fields as $field) {
                    $nq[] = $field . ' LIKE "' . $v . '%"';
                }
            }
            $str = implode(' OR ', $nq);
            $query .= ' AND (' . $str . ') ';
        }

        $sql = $this->_db->select()
                ->from(array('bcp' => 'bolsa_cv_postulante'), array('bcp.id', 'idPostulante' => 'bcp.id_postulante'))
                ->joinInner(array('p' => 'postulante'), 'bcp.id_postulante = p.id', array())
                ->joinLeft(array('exp' => 'experiencia'), 'bcp.id_postulante = exp.id_postulante', array())
                ->joinLeft(array('est' => 'estudio'), 'bcp.id_postulante = est.id_postulante', array())
                ->joinLeft(array('dpc' => 'dominio_programa_computo'), 'bcp.id_postulante = dpc.id_postulante', array())
                ->joinLeft(array('di' => 'dominio_idioma'), 'bcp.id_postulante = di.id_postulante', array())
                ->group('bcp.id')
                ->where($query);
       // var_dump($query);exit;
        //echo $sql;exit;
        $postulantes = $this->_db->fetchAll($sql);
        
     //   echo $sql;exit;
       $this->_cache->save($postulantes, $cacheId, array(), $cacheEt);
        return $postulantes;
    }
    public function getPaginatorBolsaCVs($pagData) {
        $paginado = $this->_config->empresa->bolsacvs->paginadopostulantes;
        $p = Zend_Paginator::factory($pagData);
        return $p->setItemCountPerPage($paginado);
    }
    public function getPostulanteDisc($idGrupo = 1) {
//        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
//        $cacheId = $this->_model . '_' . __FUNCTION__ . '_' . $idGrupo;
//        if ($this->_cache->test($cacheId)) {
//            return $this->_cache->load($cacheId);
//        }
        $sql = $this->_db->select()
                ->from(array('bc' => 'bolsa_cv'), array(
                    'p.id',
                  'nombre'=>  'p.conadis_code'))
                ->joinInner(array('bcp' => 'bolsa_cv_postulante'), 'bc.id = bcp.id_bolsa_cv', array())
                ->joinInner(array('p' => 'postulante'), 'p.id = bcp.id_postulante', array())               
                ->where('bc.id = ?', $idGrupo)   
                ->where('p.discapacidad >= ?', 1) 
                ->limit(1);
        $Pdisc = $this->_db->fetchAll($sql);
        if (count($Pdisc)>0) {
            return array(0=>array('id'=>'1','nombre'=>'Con discapacidad / Conadis'));
        }       
        return array();
    }

    public function getCvsUltimoMes() {
        $fechaIni = date('Y-m-d H:i:s',strtotime('-1 month'));
        $sql = $this->_db->select()
                ->from(
                        array("bcv" => $this->_name), array('id' => 'id', 'idEmpresa' => 'id_empresa',
                    'nombre' => 'nombre', "fecha_registro" => "fecha_registro","editable" => 'es_editable')
                )
                ->where('fecha_registro >= ?', $fechaIni);
        $result = $this->_db->fetchAll($sql);
        return $result;

    }

}
