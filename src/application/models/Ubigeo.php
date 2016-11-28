<?php

class Application_Model_Ubigeo extends App_Db_Table_Abstract
{
    const NIVEL_PAIS = 0;
    const NIVEL_DEPARTAMENTO = 1;
    const NIVEL_PROVINCIA = 2;
    const NIVEL_DISTRITO = 3;
    const PERU_UBIGEO_ID = 2533; //2533;
    const CALLAO_UBIGEO_ID = 3285;
    const LIMA_UBIGEO_ID = 3926;
    const LIMA_PROVINCIA_UBIGEO_ID = 3927;
    const CALLAO_PROVINCIA_UBIGEO_ID = 3286;
    const DEPARTAMENTO_CAPITAL = 'Lima';
    const SLUG_PAIS = 'peru';

    protected $_name = "ubigeo";

    /**
     * Retorna una lista los paises en dos campos id y nombre
     *
     * @return array fetchPairs
     */
    public function getPaises()
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->getAdapter()->select()
            ->from(
                array('u' => 'ubigeo'),
                array('id','nombre')
            )
            ->where('u.padre is null')
            ->order('nombre');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
            $rs,
            $cacheId,
            array(),
            $this->_config->cache->Ubigeo->getPaises
        );
        //return array();
        return $rs;

       //return array(4586=>"Afghanistán", 4587=>"Albania", 4588=>"Alemania", 4589=>"Andorra", 4590=>"Angola", 4591=>"Anguilla", 4592=>"Antigua y Barbuda", 4593=>"Arabia Saudita", 4594=>"Argelia", 4595=>"Argentina", 4596=>"Armenia", 4597=>"Australia", 4598=>"Austria", 4599=>"Azerbayán", 4600=>"Bahamas", 4601=>"Bahrein", 4602=>"Bangladesh", 4603=>"Barbados", 4604=>"Belarús", 4605=>"Bélgica", 4606=>"Belice", 4607=>"Benin", 4608=>"Bhután", 4609=>"Birmania", 4610=>"Bolivia", 4611=>"Bosnia-Herzegovina", 4612=>"Botswana", 4613=>"Brasil", 4614=>"Brunei", 4615=>"Bulgaria", 4616=>"Burkina Faso", 4617=>"Burundi", 4618=>"Cabo Verde", 4619=>"Camboya", 4620=>"Camerún", 4621=>"Canadá", 4622=>"Chad", 4623=>"Chile", 4624=>"China", 4625=>"Chipre", 4626=>"Colombia", 4627=>"Comoras", 4628=>"Congo", 4629=>"Corea del Norte", 4630=>"Corea del Sur", 4631=>"Costa de Marfil", 4632=>"Costa Rica", 4633=>"Croacia", 4634=>"Cuba", 4635=>"Dinamarca", 4636=>"Djibouti", 4637=>"Ecuador", 4638=>"Egipto", 4639=>"El Salvador", 4640=>"Emiratos Arabes Unidos", 4641=>"Eritrea", 4642=>"Eslovaquia", 4643=>"Eslovenia", 4644=>"España", 4645=>"Estados Unidos", 4646=>"Estonia", 4647=>"Etiopia", 4648=>"Fiji", 4649=>"Filipinas", 4650=>"Finlandia", 4651=>"Francia", 4652=>"Gabon", 4653=>"Gambia", 4654=>"Georgia", 4655=>"Ghana", 4657=>"Granada", 4656=>"Grecia", 4658=>"Guatemala", 4659=>"Guinea", 4661=>"Guinea Ecuatorial", 4660=>"Guinea-Bissau", 4662=>"Guyana", 4663=>"Haití", 4664=>"Honduras", 4665=>"Hungría", 4667=>"India", 4668=>"Indonesia", 4669=>"Iran", 4670=>"Iraq", 4671=>"Irlanda", 4666=>"Islandia", 4672=>"Israel", 4673=>"Italia", 4674=>"Jamaica", 4675=>"Japón", 4676=>"Jordania", 4677=>"Kazajstán", 4678=>"Kenia", 4679=>"Kirguistán", 4680=>"Kiribati", 4681=>"Kuwait", 4682=>"Laos", 4685=>"Lesotho", 4683=>"Letonia", 4684=>"Libano", 4686=>"Liberia", 4687=>"Libia", 4688=>"Liechtenstein", 4689=>"Lituania", 4690=>"Luxemburgo", 4691=>"Macedonia", 4692=>"Madagascar", 4694=>"Malasia", 4693=>"Malawi", 4695=>"Maldivas", 4696=>"Mali", 4697=>"Malta", 4698=>"Marruecos", 4699=>"Marshall", 4700=>"Mauricio", 4701=>"Mauritania", 4702=>"México", 4703=>"Micronesia", 4704=>"Moldova", 4705=>"Mónaco", 4706=>"Mongolia", 4707=>"Mozambique", 4708=>"Namibia", 4709=>"Naurú", 4710=>"Nepal", 4711=>"Nicaragua", 4712=>"Niger", 4713=>"Nigeria", 4714=>"Noruega", 4715=>"Nueva Zelandia", 4716=>"Omán", 4717=>"Países Bajos", 4718=>"Pakistán", 4719=>"Palau", 4720=>"Panamá", 4721=>"Papúa-Nueva Guinea", 4722=>"Paraguay", 2533=>"Perú", 4723=>"Polonia", 4724=>"Portugal", 4725=>"Qatar", 4726=>"Reino Unido", 4727=>"Rep. Centroafricana", 4728=>"Rep. Checa", 4729=>"Rep. Dominicana", 4730=>"Ruanda", 4731=>"Rumania", 4732=>"Rusia", 4733=>"Salomon Islands", 4734=>"Samoa", 4736=>"San Cristóbal-Nevis", 4735=>"San Marino", 4737=>"Santa Lucía", 4738=>"Santa Sede (Vaticano)", 4739=>"São Tomé y Principe", 4741=>"Senegal", 4742=>"Seychelles", 4743=>"Sierra Leona", 4744=>"Singapur", 4745=>"Siria", 4746=>"Somalia", 4747=>"Sri Lanka", 4740=>"St. Vincente las Grenadinas", 4748=>"Sudáfrica", 4749=>"Sudán", 4750=>"Suecia", 4751=>"Suiza", 4752=>"Suriname", 4753=>"Swazilandia", 4754=>"Tailandia", 4755=>"Taiwán", 4756=>"Tanzania", 4757=>"Tayikistán", 4758=>"Togo", 4759=>"Tonga", 4760=>"Trinidad y Tobago", 4761=>"Túnez", 4762=>"Turkmenistan", 4763=>"Turquia", 4764=>"Tuvalu", 4765=>"Ucrania", 4766=>"Uganda", 4767=>"Uruguay", 4768=>"Uzbekistán", 4769=>"Vanuatu", 4770=>"Venezuela", 4771=>"Vietnam", 4772=>"Yémen", 4773=>"Yugoslavia", 4774=>"Zambia", 4775=>"Zimbabwe" );
    }

    public function getPaisesSlug()
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->getAdapter()->select()
            ->from(
                array('u' => 'ubigeo'),
                array('slug_ubigeo','nombre')
            )
            ->where('u.padre is null')
            ->order('nombre');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
            $rs,
            $cacheId,
            array(),
            $this->_config->cache->Ubigeo->getPaises
        );

        return $rs;

       //return array(4586=>"Afghanistán", 4587=>"Albania", 4588=>"Alemania", 4589=>"Andorra", 4590=>"Angola", 4591=>"Anguilla", 4592=>"Antigua y Barbuda", 4593=>"Arabia Saudita", 4594=>"Argelia", 4595=>"Argentina", 4596=>"Armenia", 4597=>"Australia", 4598=>"Austria", 4599=>"Azerbayán", 4600=>"Bahamas", 4601=>"Bahrein", 4602=>"Bangladesh", 4603=>"Barbados", 4604=>"Belarús", 4605=>"Bélgica", 4606=>"Belice", 4607=>"Benin", 4608=>"Bhután", 4609=>"Birmania", 4610=>"Bolivia", 4611=>"Bosnia-Herzegovina", 4612=>"Botswana", 4613=>"Brasil", 4614=>"Brunei", 4615=>"Bulgaria", 4616=>"Burkina Faso", 4617=>"Burundi", 4618=>"Cabo Verde", 4619=>"Camboya", 4620=>"Camerún", 4621=>"Canadá", 4622=>"Chad", 4623=>"Chile", 4624=>"China", 4625=>"Chipre", 4626=>"Colombia", 4627=>"Comoras", 4628=>"Congo", 4629=>"Corea del Norte", 4630=>"Corea del Sur", 4631=>"Costa de Marfil", 4632=>"Costa Rica", 4633=>"Croacia", 4634=>"Cuba", 4635=>"Dinamarca", 4636=>"Djibouti", 4637=>"Ecuador", 4638=>"Egipto", 4639=>"El Salvador", 4640=>"Emiratos Arabes Unidos", 4641=>"Eritrea", 4642=>"Eslovaquia", 4643=>"Eslovenia", 4644=>"España", 4645=>"Estados Unidos", 4646=>"Estonia", 4647=>"Etiopia", 4648=>"Fiji", 4649=>"Filipinas", 4650=>"Finlandia", 4651=>"Francia", 4652=>"Gabon", 4653=>"Gambia", 4654=>"Georgia", 4655=>"Ghana", 4657=>"Granada", 4656=>"Grecia", 4658=>"Guatemala", 4659=>"Guinea", 4661=>"Guinea Ecuatorial", 4660=>"Guinea-Bissau", 4662=>"Guyana", 4663=>"Haití", 4664=>"Honduras", 4665=>"Hungría", 4667=>"India", 4668=>"Indonesia", 4669=>"Iran", 4670=>"Iraq", 4671=>"Irlanda", 4666=>"Islandia", 4672=>"Israel", 4673=>"Italia", 4674=>"Jamaica", 4675=>"Japón", 4676=>"Jordania", 4677=>"Kazajstán", 4678=>"Kenia", 4679=>"Kirguistán", 4680=>"Kiribati", 4681=>"Kuwait", 4682=>"Laos", 4685=>"Lesotho", 4683=>"Letonia", 4684=>"Libano", 4686=>"Liberia", 4687=>"Libia", 4688=>"Liechtenstein", 4689=>"Lituania", 4690=>"Luxemburgo", 4691=>"Macedonia", 4692=>"Madagascar", 4694=>"Malasia", 4693=>"Malawi", 4695=>"Maldivas", 4696=>"Mali", 4697=>"Malta", 4698=>"Marruecos", 4699=>"Marshall", 4700=>"Mauricio", 4701=>"Mauritania", 4702=>"México", 4703=>"Micronesia", 4704=>"Moldova", 4705=>"Mónaco", 4706=>"Mongolia", 4707=>"Mozambique", 4708=>"Namibia", 4709=>"Naurú", 4710=>"Nepal", 4711=>"Nicaragua", 4712=>"Niger", 4713=>"Nigeria", 4714=>"Noruega", 4715=>"Nueva Zelandia", 4716=>"Omán", 4717=>"Países Bajos", 4718=>"Pakistán", 4719=>"Palau", 4720=>"Panamá", 4721=>"Papúa-Nueva Guinea", 4722=>"Paraguay", 2533=>"Perú", 4723=>"Polonia", 4724=>"Portugal", 4725=>"Qatar", 4726=>"Reino Unido", 4727=>"Rep. Centroafricana", 4728=>"Rep. Checa", 4729=>"Rep. Dominicana", 4730=>"Ruanda", 4731=>"Rumania", 4732=>"Rusia", 4733=>"Salomon Islands", 4734=>"Samoa", 4736=>"San Cristóbal-Nevis", 4735=>"San Marino", 4737=>"Santa Lucía", 4738=>"Santa Sede (Vaticano)", 4739=>"São Tomé y Principe", 4741=>"Senegal", 4742=>"Seychelles", 4743=>"Sierra Leona", 4744=>"Singapur", 4745=>"Siria", 4746=>"Somalia", 4747=>"Sri Lanka", 4740=>"St. Vincente las Grenadinas", 4748=>"Sudáfrica", 4749=>"Sudán", 4750=>"Suecia", 4751=>"Suiza", 4752=>"Suriname", 4753=>"Swazilandia", 4754=>"Tailandia", 4755=>"Taiwán", 4756=>"Tanzania", 4757=>"Tayikistán", 4758=>"Togo", 4759=>"Tonga", 4760=>"Trinidad y Tobago", 4761=>"Túnez", 4762=>"Turkmenistan", 4763=>"Turquia", 4764=>"Tuvalu", 4765=>"Ucrania", 4766=>"Uganda", 4767=>"Uruguay", 4768=>"Uzbekistán", 4769=>"Vanuatu", 4770=>"Venezuela", 4771=>"Vietnam", 4772=>"Yémen", 4773=>"Yugoslavia", 4774=>"Zambia", 4775=>"Zimbabwe" );
    }

    /**
     * Retorna una lista los departamentos del Perú en dos campos id y nombre
     * @return array fetchPairs
     */
    public function getDepartamentos()
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->getAdapter()->select()
            ->from(
                array('u' => 'ubigeo'),
                array('id','nombre')
            )
            ->where('u.padre = ? ', self::PERU_UBIGEO_ID)
            ->order('nombre');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
            $rs,
            $cacheId,
            array(),
            $this->_config->cache->Ubigeo->getDepartamentos
        );
        return $rs;
    }

    /**
     *
     Retorna una lista las Provincias de Lima Perú en dos campos id y nombre
     * @return array fetchPairs
     */
    public function getProvincias()
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->getAdapter()->select()
            ->from(
                array('u' => 'ubigeo'),
                array('id','nombre')
            )
            ->where('u.padre = ?', self::LIMA_UBIGEO_ID)
            ->order('nombre');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
            $rs,
            $cacheId,
            array(),
            $this->_config->cache->Ubigeo->getProvincias
        );
        return $rs;
    }


    /**
     * Retorna una lista los distritos de Lima Perú en dos campos id y nombre
     * @return array fetchPairs
     */
    public function getDistritos()
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->getAdapter()->select()
            ->from(
                array('u' => 'ubigeo'),
                array('id','nombre')
            )
            ->where('u.padre = ?', self::LIMA_PROVINCIA_UBIGEO_ID)
            ->order('nombre');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
            $rs,
            $cacheId,
            array(),
            $this->_config->cache->Ubigeo->getDistritos
        );
        return $rs;
    }

    public function getDistritosCallao()
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->getAdapter()->select()
            ->from(
                array('u' => 'ubigeo'),
                array('id','nombre')
            )
            ->where('u.padre = ?', self::CALLAO_PROVINCIA_UBIGEO_ID)
            ->order('nombre');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
            $rs,
            $cacheId,
            array(),
            $this->_config->cache->Ubigeo->getDistritosCallao
        );
        return $rs;
    }

    public function getDistritosCallaoByBusqueda()
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->getAdapter()->select()
            ->from(
                array('u' => 'ubigeo'),
                array('id','nombre', 'search_name')
            )
            ->where('u.padre = ?', self::CALLAO_PROVINCIA_UBIGEO_ID)
            ->order('nombre');
        $rs = $this->getAdapter()->fetchAssoc($sql);
        $this->_cache->save(
            $rs,
            $cacheId,
            array(),
            $this->_config->cache->Ubigeo->getDistritosCallao
        );
        return $rs;
    }

    public function getHijos($idPadre)
    {
        $cacheId = $this->_prefix.__FUNCTION__.'_'.$idPadre;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->select()
            ->from($this->_name, array('id', 'nombre'))
            ->where('padre = ?', $idPadre)
            ->order('nombre');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
            $rs,
            $cacheId,
            array(),
            $this->_config->cache->Carrera->getHijos
        );
        return $rs;
    }


        public function getHijosSulg($idPadre)
    {
        $cacheId = $this->_prefix.__FUNCTION__.'_'.$idPadre;
//        if ($this->_cache->test($cacheId)) {
//            return $this->_cache->load($cacheId);
//        }
        $sql = $this->select()
            ->from($this->_name, array('id'))
            ->where('padre = ?', $idPadre)
            ->order('nombre');
        $rs = $this->getAdapter()->fetchAll($sql);
//        $this->_cache->save(
//            $rs,
//            $cacheId,
//            array(),
//            $this->_config->cache->Carrera->getHijosSulg
//        );
        return $rs;
    }
    public function getUbicacionByName($ubicacion)
    {
        $solrubigeo= new Solr_SolrUbigeo();
        $rs = $solrubigeo->getUbicacionByName($ubicacion, 5);
        if($rs==500){
           $cacheId = $this->_prefix.__FUNCTION__.'_'.App_Controller_Action_Helper_Util::cleanString($ubicacion);;
            if ($this->_cache->test($cacheId)) {
               return $this->_cache->load($cacheId);
           }
          $rs= $this->getDetalleUbigeoName($ubicacion);
          $this->_cache->save(
               $rs,
               $cacheId,
               array(),
               $this->_config->cache->Ubigeo->getUbicacionByName
           );
        }

        return $rs;
    }
    public function getDetalleUbigeo($id)
    {
        $db = $this->getAdapter();
        $whereField = is_numeric($id)?'id':'slug';
        $sql = $db->select()
                ->from(
                    array('u' => 'ubigeo'),
                    array('level' => 'u.level')
                )
                ->where("u.id = ?", $id);
        $level = $db->fetchOne($sql);
        $empty = new Zend_Db_Expr("''");
        switch ($level) {
            case Application_Model_Ubigeo::NIVEL_PAIS:
                $fields = array(
                    'iddistrito' => $empty,
                    'distrito' => $empty,
                    'idprov' => $empty,
                    'provincia' => $empty,
                    'iddpto' => $empty,
                    'dpto' => $empty,
                    'idpaisres' => 'paisres.id',
                    'paisres' => 'paisres.nombre',
                );
                break;
            case Application_Model_Ubigeo::NIVEL_DEPARTAMENTO:
                $fields = array(
                    'iddistrito' => $empty,
                    'distrito' => $empty,
                    'idprov' => $empty,
                    'provincia' => $empty,
                    'iddpto' => 'dpto.id',
                    'dpto' => 'dpto.nombre',
                    'idpaisres' => 'paisres.id',
                    'paisres' => 'paisres.nombre',
                );
                break;
            case Application_Model_Ubigeo::NIVEL_PROVINCIA:
                $fields = array(
                    'iddistrito' => $empty,
                    'distrito' => $empty,
                    'idprov' => 'prov.id',
                    'provincia' => 'prov.nombre',
                    'iddpto' => 'dpto.id',
                    'dpto' => 'dpto.nombre',
                    'idpaisres' => 'paisres.id',
                    'paisres' => 'paisres.nombre',
                );
                break;
            case Application_Model_Ubigeo::NIVEL_DISTRITO:
                $fields = array(
                    'iddistrito' => 'dist.id',
                    'distrito' => 'dist.nombre',
                    'idprov' => 'prov.id',
                    'provincia' => 'prov.nombre',
                    'iddpto' => 'dpto.id',
                    'dpto' => 'dpto.nombre',
                    'idpaisres' => 'paisres.id',
                    'paisres' => 'paisres.nombre',
                );
                break;
        }

        $fields['name'] = 'u.display_name';
        $sql = $db->select()
            ->from(
                array('u' => $this->_name),
                $fields
            );

            if ($level==Application_Model_Ubigeo::NIVEL_DISTRITO) {
                $sql = $sql->join(array ('dist'=>'ubigeo'), 'dist.id= '.$id);
                $sql = $sql->join(array ('prov'=>'ubigeo'), 'dist.padre = prov.id');
                $sql = $sql->join(array ('dpto'=>'ubigeo'), 'prov.padre = dpto.id');
                $sql = $sql->joinLeft(array ('paisres'=>'ubigeo'), 'dpto.padre = paisres.id');
            }
            if ($level==Application_Model_Ubigeo::NIVEL_PROVINCIA) {
                $sql = $sql->join(array ('prov'=>'ubigeo'), 'prov.id = '.$id);
                $sql = $sql->join(array ('dpto'=>'ubigeo'), 'prov.padre = dpto.id');
                $sql = $sql->joinLeft(array ('paisres'=>'ubigeo'), 'dpto.padre = paisres.id');
            }
            if ($level==Application_Model_Ubigeo::NIVEL_DEPARTAMENTO) {
                $sql = $sql->join(array ('dpto'=>'ubigeo'), 'dpto.id = '.$id);
                $sql = $sql->joinLeft(array ('paisres'=>'ubigeo'), 'dpto.padre = paisres.id');
            }
            if ($level==Application_Model_Ubigeo::NIVEL_PAIS) {
                $sql = $sql->joinLeft(array ('paisres'=>'ubigeo'), 'paisres.id = '.$id);
            }
        $rs = $db->fetchRow($sql);
        return $rs;
    }



    public function getDetalleUbigeoName($name)
    {
       $db = new App_Db_Table_Abstract();
       $fields = array(
                    'id' => 'dist.id',
                    'mostrar' => new Zend_Db_Expr("CONCAT_WS(', ',LCASE(dist.nombre),LCASE(prov.nombre),LCASE(dpto.nombre) )")
                );
        $where=  $db->getAdapter()->quoteInto('dist.display_name LIKE ?', "$name%");
        $sql = $this->getAdapter()->select()
            ->from(
                array('u' => $this->_name),
                $fields
            )->join(array ('dist'=>'ubigeo'),  $where ,array())
             ->join(array ('prov'=>'ubigeo'), 'dist.padre = prov.id' ,array())
             ->join(array ('dpto'=>'ubigeo'), 'prov.padre = dpto.id',array())
             ->joinLeft(array ('paisres'=>'ubigeo'), 'dpto.padre = paisres.id',array())
             ->where('dist.level = ?', 3)
             ->order('dist.nombre')
             ->group('dist.id')
             ->limit(5) ;

        $rs =  $this->getAdapter()->fetchAll($sql);
        return $rs;
    }

    public function getDetalleUbigeoById($id)
    {
        $db = new App_Db_Table_Abstract();
        $empty = new Zend_Db_Expr("''");
        $fields = array(
            'id' => 'dist.id',
            'nombre' => new Zend_Db_Expr("CONCAT_WS(', ',(dist.nombre),(prov.nombre),(dpto.nombre) )")
        );
        $where=  $db->getAdapter()->quoteInto('dist.id = ?', $id);
        $sql = $this->getAdapter()->select()
            ->from(
                array('u' => $this->_name),
                $fields
            )->join(array ('dist'=>'ubigeo'),  $where ,array())
            ->join(array ('prov'=>'ubigeo'), 'dist.padre = prov.id' ,array())
            ->join(array ('dpto'=>'ubigeo'), 'prov.padre = dpto.id',array())
            ->joinLeft(array ('paisres'=>'ubigeo'), 'dpto.padre = paisres.id',array())
            ->where('dist.level = ?', 3)
            ->order('dist.nombre')
            ->group('dist.id')
            ->limit(1) ;
        $rs =  $this->getAdapter()->fetchRow($sql);
        return $rs;
    }

    public function getIndexSearch($ubigeos)
    {
        $sql = $this->getAdapter()->select()
            ->from($this->_name, 'index_name')
            ->where('search_name in (?)', $ubigeos);
        return $this->getAdapter()->fetchAll($sql);
    }

    public function getHijosId($idUbigeo)
    {
        $sql = $this->select()
            ->from($this->_name, array('id', 'nombre'))
            ->where('padre = ?', $idUbigeo)
            ->order('nombre');
        return $this->getAdapter()->fetchAll($sql);
    }

    public function listadoUbicacionBuscadorBuscaMas($ubicacionJSON) {

        $arrayUbi1 = array();
        $arrayUbi2 = array();
        $dataUbi1 = $ubicacionJSON;
        $dataUbi2 = $ubicacionJSON;

        arsort($dataUbi1);
        ksort($dataUbi2);

        $contador = 0;
        foreach ($dataUbi1 as $key => $value) {
            $dataUbi = $this->fetchRow('nombre = "'.$key.'"');
            if ($dataUbi != null) {

                $arrayUbi1[$contador]['ind'] = $dataUbi['id'];
                $arrayUbi1[$contador]['cant'] = $value;
                $arrayUbi1[$contador]['slug'] = str_replace(' ','-', $dataUbi['search_name']);
                $arrayUbi1[$contador]['msg'] = $key;
                $contador ++;
            }

        }

        $contador = 0;
        foreach ($dataUbi2 as $key => $value) {
            $dataUbi = $this->fetchRow('nombre = "'.$key.'"');
            if ($dataUbi != null) {

                $arrayUbi2[$contador]['ind'] = $dataUbi['id'];
                $arrayUbi2[$contador]['cant'] = $value;
                $arrayUbi2[$contador]['slug'] = str_replace(' ','-', $dataUbi['search_name']);
                $arrayUbi2[$contador]['msg'] = $dataUbi['nombre'];
                $contador ++;
            }

        }

        $data[0] = $arrayUbi1;
        $data[1] = $arrayUbi2;

        return $data;

    }

    public function getDisplayNameUbigeo($nombre)
    {

        $sql = $this->getAdapter()->select()
            ->from($this->_name, 'display_name')
            ->where('index_name like ?', '%'.$nombre.'%')
            ->where('contador_anuncios > ?', 0);

        return $this->getAdapter()->fetchRow($sql);

     }

    public function getPaisEmpresa()
    {

        $sql = $this->getAdapter()->select()
            ->from(
                array('u' => 'ubigeo'),
                array('id','nombre')
            )
            ->where('u.padre is null')
            ->where('id = ?', self::PERU_UBIGEO_ID)
            ->order('nombre');
        $rs = $this->getAdapter()->fetchPairs($sql);

        return $rs;

    }

    public function getDepartamentoSEOBuscador($distritos)
    {
        $sql = $this->getAdapter()->select()->from('ubigeo','count(1)')
                ->where('nombre in (?)',$distritos)
                ->where('padre = ?', self::LIMA_PROVINCIA_UBIGEO_ID);
        //echo $sql;
        return $this->getAdapter()->fetchOne($sql);


    }

    /**
     * Retorna una lista de países que coinciden con la palabra ingresada
     *
     * @return array fetchPairs
     */
    public function getPaisByName($name)
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->getAdapter()->select()
            ->from(
                array('u' => 'ubigeo'),
                array('id','nombre')
            )
            ->where('u.padre is null')
            ->where('u.nombre like "'.$name.'%"')
            ->order('nombre');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
            $rs,
            $cacheId,
            array(),
            $this->_config->cache->Ubigeo->getPaises
        );
        return $rs;
    }

    public function getUbigeo($id)
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->getAdapter()->select()
            ->from(
                array('u' => 'ubigeo')
            )
            ->where('u.id = ?', $id);
        $rs = $this->getAdapter()->fetchRow($sql);
        $this->_cache->save(
            $rs,
            $cacheId,
            array(),
            $this->_config->cache->Ubigeo->getPaises
        );
        return $rs;
    }
    public function getProvinciasPeru()
    {
        $cacheId = $this->_prefix.__FUNCTION__;
        if ($this->_cache->test($cacheId)) {
            return $this->_cache->load($cacheId);
        }
        $sql = $this->getAdapter()->select()
            ->from(
                array('u' => 'ubigeo'),
                array('slug' => "REPLACE(LCASE(u.nombre),' ','-')",'nombre')
            )
            ->where('u.level = ?', self::NIVEL_PROVINCIA)
            ->order('nombre');
        $rs = $this->getAdapter()->fetchPairs($sql);
        $this->_cache->save(
            $rs,
            $cacheId,
            array(),
            $this->_config->cache->Ubigeo->getProvincias
        );
        return $rs;
    }
       public function getubigeosolr()
    {
        $adapter = $this->getAdapter();
        $sql="SELECT
 `pais`.`id`     AS `pais_id`,
 `pais`.`nombre` AS `pais_nombre`,
 `dpto`.`id`     AS `dpto_id`,
 `dpto`.`nombre` AS `dpto_nombre`,
 `prov`.`id`     AS `prov_id`,
 `prov`.`nombre` AS `prov_nombre`,
 `prov`.`id`    AS `dist_id`,
 `prov`.`nombre`  AS `dist_nombre`,
 CONCAT_WS(' ',LCASE(`prov`.`nombre`),LCASE(`dpto`.`nombre`),LCASE(`pais`.`nombre`)) AS `ubicacion`,
 CONCAT_WS(', ',LCASE(`prov`.`nombre`),LCASE(`dpto`.`nombre`),LCASE(`pais`.`nombre`)) AS `mostrar`
FROM (((`ubigeo` `pais`
    LEFT JOIN `ubigeo` `dpto`
      ON ((`dpto`.`padre` = `pais`.`id`)))
   LEFT JOIN `ubigeo` `prov`
     ON ((`prov`.`padre` = `dpto`.`id`))))
WHERE (`pais`.`level` = 0)
      AND (`dpto`.`level` = 1)
      AND (`prov`.`level` = 2)"
              ;
        //die($sql); exit;
        $stm = $adapter->query($sql);
        $stmt = $stm->fetchAll();
        return $stmt;
    }
}
