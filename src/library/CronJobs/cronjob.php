<?php

class CronJobs_cronjob
{

    public function despublicarAnunciosVencidos()
    {


        $fecHoy = new Zend_Date();
        $db     = new App_Db_Table_Abstract();
        error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

        $solrAviso = new Solr_SolrAviso();
//        $config = Zend_Registry::get('config');
//
//        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
//        $buscamasUrl = $config->apis->buscamas->url;
//        $buscamasPublishUrl = $config->apis->buscamas->publishUrl;

        $where = "DATE(fh_vencimiento) < CURDATE()
                  AND online = 1;";
        $sql   = "SELECT id FROM anuncio_web WHERE ".$where;
        $ids   = $db->getAdapter()->fetchCol($sql); //echo $sql;
        $sql   = "SELECT id,url_id,id_empresa FROM anuncio_web WHERE ".$where;
        $urls  = $db->getAdapter()->fetchAll($sql);

        if (count($ids) == 0) {
            echo "No hay anuncios que despublicar".PHP_EOL;
            return;
        }
        $sql = "UPDATE anuncio_web 
            SET 
            fh_aviso_baja = '".$fecHoy->toString('YYYY-MM-dd H:m:s')."', 
            estado = '".Application_Model_AnuncioWeb::ESTADO_DADO_BAJA."', 
            online = 0, 
            cerrado = '".Application_Model_AnuncioWeb::CERRADO."',
            estado_publicacion = 0 
            WHERE ".$db->getAdapter()->quoteInto('id IN (?)', $ids);
        $db->getAdapter()->query($sql);

        $cantAvisos = 0;
        //Actualiza índices en Buscamas
        foreach ($ids as $idAviso) {
            $res = $solrAviso->DeleteAvisoSolr($idAviso);

            echo 'eliminacion de avisos del solr ='.$idAviso.' - '.$res.PHP_EOL;
            $cantAvisos ++;
//            @$this->_cache->remove('AnuncioWeb_getAvisoRelacionadosnew_' . $idAviso);
//            @$this->_cache->remove('AnuncioWeb_getAvisosRelacionadosAuxiliar_' . $idAviso);
        }

        echo "En total ".$cantAvisos." se desactivaron".PHP_EOL;

        $this->_cache = Zend_Registry::get('cache');
        foreach ($urls as $url) {
            $idEmpresa           = $url['id_empresa'];
            $cacheId             = 'AnuncioWeb_getAvisoIdByUrl_Api_'.$url['url_id'].'_'.$idEmpresa;
            $cacheIdestudios     = 'AnuncioWeb_getEstudiosByAnuncio_Api_'.$url['url_id'].'_'.$idEmpresa;
            $cacheIdexperiencias = 'AnuncioWeb_getExperienciasByAnuncio_Api_'.$url['url_id'].'_'.$idEmpresa;
            $cacheIdidiomas      = 'AnuncioWeb_getIdiomasByAnuncio_Api_'.$url['url_id'].'_'.$idEmpresa;
            $cacheIdprogramas    = 'AnuncioWeb_getIdiomasByAnuncio_Api_'.$url['url_id'].'_'.$idEmpresa;
            $cachePre            = 'AnuncioWeb_getPreguntas_Api_'.$url['url_id'].'_'.$idEmpresa;

            echo 'eliminacion del Cache avisos x empresa '.$url['url_id'].PHP_EOL;
            @$this->_cache->remove('anuncio_web_'.$url['url_id']);
            @$this->_cache->remove('AnuncioWeb_getAvisoInfoficha_'.$url['id']);
            @$this->_cache->remove('AnuncioWeb_getAvisoIdByUrl_'.$url['url_id']);
            @$this->_cache->remove('AnuncioWeb_getAvisoIdByCreado_'.$url['url_id']);
            @$this->_cache->remove($cacheId);
        }









        @$this->_cache->remove('Empresa_getEmpresaHome_');
        echo "Despublicar Anuncios Vencidos[OK]".PHP_EOL;
        $this->actualizarContadoresPortada();
    }

    public function actualizarSlug()
    {
        $_areas    = new Application_Model_Area();
        $slugger   = new App_Filter_Slug();
        $dataAreas = $_areas->fetchAll()->toArray();
        foreach ($dataAreas as $area) {
            $area['slug'] = $slugger->filter($area['nombre']);
            $_areas->update($area, 'id = '.$area['id']);
        }
        $_areas = null;

        $_nivelpuesto    = new Application_Model_NivelPuesto();
        $slugger         = new App_Filter_Slug();
        $dataNivelPuesto = $_nivelpuesto->fetchAll()->toArray();
        foreach ($dataNivelPuesto as $np) {
            $np['slug'] = $slugger->filter($np['nombre']);
            $_nivelpuesto->update($np, 'id = '.$np['id']);
        }
        $_nivelpuesto = null;

        $_empresa    = new Application_Model_Empresa();
        $slugger     = new App_Filter_Slug();
        $dataEmpresa = $_empresa->fetchAll()->toArray();
        foreach ($dataEmpresa as $obj) {
            $obj['slug'] = $slugger->filter($obj['razon_social']);
            $_empresa->update($obj, 'id = '.$obj['id']);
        }
        $_empresa = null;
        return true;
    }

    public function publicarAnunciosAdecsys()
    {

//        $config = Zend_Registry::get('config');
//
//        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
//        $buscamasUrl = $config->apis->buscamas->url;
//        $buscamasPublishUrl = $config->apis->buscamas->publishUrl;
//        $_aw = new Application_Model_AnuncioWeb();
        $db          = new App_Db_Table_Abstract();
        $helperAviso = new App_Controller_Action_Helper_Aviso();
        $SolrrAviso  = new Solr_SolrAviso();

        $where = " a.origen='adecsys' 
        AND a.chequeado = 1
        AND a.estado = 'pagado'
        AND a.online = 0
        AND a.cerrado = 0
        AND (a.fh_impreso <= CURDATE() OR a.fh_impreso IS NULL)
        AND u.activo = 1 
        AND (u.rol = 'empresa-admin' or u.rol = 'empresa-usuario');";
        $sql   = "SELECT DISTINCT a.id_compra,a.id FROM anuncio_web a
    INNER JOIN empresa e ON e.id = a.id_empresa INNER JOIN usuario u ON u.id = e.id_usuario
                WHERE  ".$where;

        $dataAw = $db->getAdapter()->fetchAll($sql);
        if (!$dataAw) {
            echo "No existe aviso que publicar".PHP_EOL;
        }

        foreach ($dataAw as $key => $row) {
            $helperAviso->actualizaValoresCompraAviso($row['id_compra']);
            echo "Compra ".$row['id_compra']." Publicado [OK]".PHP_EOL;
            //$resultado = exec("curl -X POST -d 'api_key=" . $buscamasConsumerKey . "&nid=" . $row['id'] . "&site=" . $buscamasUrl . "' " . $buscamasPublishUrl);
            $resultado = $SolrrAviso->addAvisoSolr($row['id']);
            echo $resultado.PHP_EOL;
        }

        echo "Se termino el proceso".PHP_EOL;
    }

    public function publicarAnunciosAdecsysExtemporaneos()
    {
//        $_aw = new Application_Model_AnuncioWeb();
        $db          = new App_Db_Table_Abstract();
        $helperAviso = new App_Controller_Action_Helper_Aviso();
        $where       = "origen = 'adecsys'
        AND chequeado = 1
        AND estado = 'pagado'
        AND online = 0;";
        $sql         = "SELECT DISTINCT id_compra FROM anuncio_web
                WHERE ".$where;
        $dataAw      = $db->getAdapter()->fetchAll($sql);
        foreach ($dataAw as $key => $row) {
            $helperAviso->actualizaValoresCompraAvisoExtem($row['id_compra']);
            echo "Compra ".$row['id_compra']." Publicado [OK]".PHP_EOL;
        }
        echo "Se termino el proceso".PHP_EOL;
    }

    public function actualizarContadoresPortada()
    {
        echo "Actualizando Contadores de Portada....".PHP_EOL;
        $db  = new App_Db_Table_Abstract();
        $sql = "DROP TABLE IF EXISTS tempArea;";
        $db->getAdapter()->query($sql);
        $sql = "CREATE TEMPORARY TABLE tempEmpresa 
            (SELECT e.id AS idEmpresa, e.razon_social, COUNT(aw.id) AS contador 
            FROM anuncio_web aw 
            RIGHT JOIN `empresa` e ON aw.id_empresa = e.id 
            WHERE online = 1 
            GROUP BY id_empresa);";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE `empresa` SET contador_anuncios = 0;";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE `empresa` SET contador_anuncios = 
            (SELECT contador FROM tempEmpresa WHERE idEmpresa = empresa.id);";
        $db->getAdapter()->query($sql);

        $sql = "CREATE TEMPORARY TABLE tempArea 
            (SELECT a.id AS idArea, a.nombre, COUNT(aw.id) AS contador 
            FROM anuncio_web aw 
            RIGHT JOIN `area` a ON aw.id_area = a.id 
            WHERE online = 1 
            GROUP BY id_area);";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE `area` SET contador_anuncios = 0;";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE `area` SET contador_anuncios = 
            (SELECT contador FROM tempArea WHERE idArea = area.id);";
        $db->getAdapter()->query($sql);
        $sql = "DROP TABLE IF EXISTS tempnivelpuesto;";
        $db->getAdapter()->query($sql);

        $sql = "CREATE TEMPORARY TABLE tempnivelpuesto 
            (SELECT np.id AS idNivelPuesto, np.nombre, COUNT(aw.id) AS contador 
            FROM anuncio_web aw 
            RIGHT JOIN nivel_puesto np ON aw.id_nivel_puesto = np.id 
            WHERE online = 1 
            GROUP BY id_nivel_puesto);";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE nivel_puesto SET contador_anuncios = 0;";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE nivel_puesto SET contador_anuncios = 
            (SELECT contador FROM tempnivelpuesto WHERE idNivelPuesto = nivel_puesto.id);";
        $db->getAdapter()->query($sql);
        $sql = "DROP TABLE IF EXISTS tempubigeo;";
        $db->getAdapter()->query($sql);

        $sql = "CREATE TEMPORARY TABLE tempubigeo 
            (SELECT u.id AS idubigeo, u.nombre, COUNT(aw.id) AS contador, u.padre AS padre 
            FROM anuncio_web aw 
            RIGHT JOIN ubigeo u ON aw.id_ubigeo = u.id 
            WHERE online = 1 
            GROUP BY id_ubigeo);";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE ubigeo SET contador_anuncios = 0;";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE ubigeo SET contador_anuncios = 
            (SELECT contador FROM tempubigeo WHERE idubigeo = ubigeo.id);";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE ubigeo SET contador_anuncios = 
            (SELECT SUM(contador) FROM tempubigeo WHERE padre = 3285) WHERE id = 3285;";
        $db->getAdapter()->query($sql);

        $objAnuncioWeb          = new Application_Model_AnuncioWeb();
        // Contadores por Fecha de Publicación
        $dataContadoresFechaPub = $objAnuncioWeb->getCantAvisosPorFechaPublicacion();

        $objContadorFechaPublicacion = new Application_Model_ContadorFechaPublicacion();
        $objContadorFechaPublicacion->delete('1=1');

        foreach ($dataContadoresFechaPub as $value) {
            $registro = array(
                'nombre' => $value['msg'],
                'slug' => $value['slug'],
                'dias' => $value['dias'],
                'contador_anuncios' => $value['cant'],
            );
            $objContadorFechaPublicacion->insert($registro);
        }

        // Contadores por Rango de Remuneración
        $dataContadoresRangoRemuneracion = $objAnuncioWeb->getCantAvisosPorRangoRemuneracion();
        $objContadorRangoRemuneracion    = new Application_Model_ContadorRangoRemuneracion();
        $objContadorRangoRemuneracion->delete('1=1');

        foreach ($dataContadoresRangoRemuneracion as $value) {
            $registro = array(
                'nombre' => $value['msg'],
                'slug' => $value['slug'],
                'salario_min' => $value['minimo'],
                'salario_max' => $value['maximo'],
                'contador_anuncios' => $value['cant']
            );
            $objContadorRangoRemuneracion->insert($registro);
        }
        echo "Contadores de Portada Actualizados [OK]".PHP_EOL;
    }

    public function actualizarContadores()
    {
        echo "Actualizando Contadores de Portada....".PHP_EOL;
        $db = new App_Db_Table_Abstract();

        $sql = "DROP TABLE IF EXISTS tempArea;";
        $db->getAdapter()->query($sql);
        $sql = "CREATE TEMPORARY TABLE tempEmpresa 
            (SELECT e.id AS idEmpresa, e.razon_social, COUNT(aw.id) AS contador 
            FROM anuncio_web aw 
            RIGHT JOIN `empresa` e ON aw.id_empresa = e.id 
            WHERE online = 1 
            GROUP BY id_empresa);";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE `empresa` SET contador_anuncios = 0;";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE `empresa` SET contador_anuncios = 
            (SELECT contador FROM tempEmpresa WHERE idEmpresa = empresa.id);";
        $db->getAdapter()->query($sql);

        $sql = "CREATE TEMPORARY TABLE tempArea 
            (SELECT a.id AS idArea, a.nombre, COUNT(aw.id) AS contador 
            FROM anuncio_web aw 
            RIGHT JOIN `area` a ON aw.id_area = a.id 
            WHERE online = 1 
            GROUP BY id_area);";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE `area` SET contador_anuncios = 0;";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE `area` SET contador_anuncios = 
            (SELECT contador FROM tempArea WHERE idArea = area.id);";
        $db->getAdapter()->query($sql);
        $sql = "DROP TABLE IF EXISTS tempnivelpuesto;";
        $db->getAdapter()->query($sql);

        $sql = "CREATE TEMPORARY TABLE tempnivelpuesto 
            (SELECT np.id AS idNivelPuesto, np.nombre, COUNT(aw.id) AS contador 
            FROM anuncio_web aw 
            RIGHT JOIN nivel_puesto np ON aw.id_nivel_puesto = np.id 
            WHERE online = 1 
            GROUP BY id_nivel_puesto);";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE nivel_puesto SET contador_anuncios = 0;";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE nivel_puesto SET contador_anuncios = 
            (SELECT contador FROM tempnivelpuesto WHERE idNivelPuesto = nivel_puesto.id);";
        $db->getAdapter()->query($sql);
        $sql = "DROP TABLE IF EXISTS tempubigeo;";
        $db->getAdapter()->query($sql);

        $sql = "CREATE TEMPORARY TABLE tempubigeo 
            (SELECT u.id AS idubigeo, u.nombre, COUNT(aw.id) AS contador, u.padre AS padre 
            FROM anuncio_web aw 
            RIGHT JOIN ubigeo u ON aw.id_ubigeo = u.id 
            WHERE online = 1 
            GROUP BY id_ubigeo);";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE ubigeo SET contador_anuncios = 0;";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE ubigeo SET contador_anuncios = 
            (SELECT contador FROM tempubigeo WHERE idubigeo = ubigeo.id);";
        $db->getAdapter()->query($sql);
        $sql = "UPDATE ubigeo SET contador_anuncios = 
            (SELECT SUM(contador) FROM tempubigeo WHERE padre = 3285) WHERE id = 3285;";
        $db->getAdapter()->query($sql);

        $objAnuncioWeb          = new Application_Model_AnuncioWeb();
        // Contadores por Fecha de Publicación
        $dataContadoresFechaPub = $objAnuncioWeb->getCantAvisosPorFechaPublicacion();

        $objContadorFechaPublicacion = new Application_Model_ContadorFechaPublicacion();
        $objContadorFechaPublicacion->delete('1=1');

        foreach ($dataContadoresFechaPub as $value) {
            $registro = array(
                'nombre' => $value['msg'],
                'slug' => $value['slug'],
                'dias' => $value['dias'],
                'contador_anuncios' => $value['cant'],
            );
            $objContadorFechaPublicacion->insert($registro);
        }

        // Contadores por Rango de Remuneración
        $dataContadoresRangoRemuneracion = $objAnuncioWeb->getCantAvisosPorRangoRemuneracion();
        $objContadorRangoRemuneracion    = new Application_Model_ContadorRangoRemuneracion();
        $objContadorRangoRemuneracion->delete('1=1');

        foreach ($dataContadoresRangoRemuneracion as $value) {
            $registro = array(
                'nombre' => $value['msg'],
                'slug' => $value['slug'],
                'salario_min' => $value['minimo'],
                'salario_max' => $value['maximo'],
                'contador_anuncios' => $value['cant']
            );
            $objContadorRangoRemuneracion->insert($registro);
        }

        echo "Contadores de Portada Actualizados [OK]".PHP_EOL;
        //Contadores de Anuncio_web

        echo "Actualizando Contadores de Anuncio Web....".PHP_EOL;
        $fc          = new App_Controller_Action_Helper_Aviso();
        $objAnuncio  = new Application_Model_AnuncioWeb();
        $todosAvisos = $objAnuncio->getAllAvisosProcesoActivo();
        foreach ($todosAvisos as $item) {
            $fc->actualizarPostulantes($item["id"]);
            $fc->actualizarInvitaciones($item["id"]);
            $fc->actualizarNuevasPostulaciones($item["id"]);
        }

        echo "Contadores de Anuncio Web [OK]".PHP_EOL;
    }

    public function importarTablasAdecsys()
    {

        $db      = Zend_Db_Table::getDefaultAdapter();
        $config  = Zend_Registry::get('config');
        $options = array();
        if (isset($config->adecsys->proxy->enabled) && $config->adecsys->proxy->enabled) {
            $options = $config->adecsys->proxy->param->toArray();
        }

        try {
            $ws                = new Adecsys_Wrapper($config->adecsys->wsdl,
                $options);
            $client            = $ws->getSoapClient();
            $this->_aptitusObj = new Aptitus_Adecsys($ws, $db);
        } catch (Exception $ex) {
            if (!empty($config->mensaje->avisoadecsys->emails)) {
                $emailing = explode(',', $config->mensaje->avisoadecsys->emails);
                $helper   = new App_Controller_Action_Helper_Mail();
                foreach ($emailing as $email) {
                    $helper->notificacionAdecsys(
                        array(
                            'to' => trim($email),
                            'mensaje' => $ex->getMessage(),
                            'trace' => $ex->getTraceAsString(),
                            'refer' => http_build_query($_REQUEST)
                        )
                    );
                }
            }
        }


        // Activar log en consola
        $log = new Zend_Log(new Zend_Log_Writer_Stream('php://output'));
        $this->_aptitusObj->setLog($log);

        define('CONSOLE_PATH', realpath(dirname(__FILE__).'/../../logs/jobs'));
        $response = $ws->obtenerEspecialidades();

        //echo count($response->EspecialidadBE);exit;
        $remoteEspecialties = array();
        // @codingStandardsIgnoreStart
        foreach ($response->EspecialidadBE as $especialidad) {
            $remoteEspecialties[$especialidad->Esp_Id] = $especialidad->Des_Esp;
        }
        //@codingStandardsIgnoreEnd
        $totalImportados = count($remoteEspecialties);
        //exit;
        file_put_contents(CONSOLE_PATH.'/lastRequest.xml',
            $client->getLastRequest());
        file_put_contents(CONSOLE_PATH.'/lastResponse.xml',
            $client->getLastResponse());
        $objEspecialidad = new Application_Model_Especialidad();
        $db              = new App_Db_Table_Abstract();
        $sql             = "SELECT id FROM especialidad where id < 9998;";
        $data            = $db->getAdapter()->query($sql)->fetchAll();
        $totalActuales   = count($data);
        if ($totalImportados > $totalActuales) {
            $dif = $totalImportados - $totalActuales;
            for ($i = 0; $i < $dif; $i++) {
                $totalActuales++;
                $data = array(
                    'id' => $totalActuales,
                    'nombre' => $remoteEspecialties[$totalActuales]
                );
                echo "Agregó ".$remoteEspecialties[$totalActuales].PHP_EOL;
                $objEspecialidad->insert($data);
            }
        } else {
            echo "No hay actualizaciones".PHP_EOL;
            ;
        }
        echo "Importar Tablas de Adecsys[OK]".PHP_EOL;
    }

    public function llenarBufferUrlIds()
    {
        $_tu = new Application_Model_TempUrlId();
        $db  = new App_Db_Table_Abstract();

        $genPassword = new App_Controller_Action_Helper_GenPassword();

        //$sql = "SELECT count(url_id) FROM temp_urlid";
        //$cantUrlGenerateds = $db->getAdapter()->fetchCol($sql);
        //$cantUrlGenerateds = $cantUrlGenerateds[0];

        $config              = Zend_Registry::get('config');
        $maxUrlIdsToGenerate = $config->maxUrlIdsToGenerate;

        //list($usec, $sec) = explode(' ', microtime());
        //$number = $sec + $usec;
        //echo "empieza. ".$number.PHP_EOL;



        $sqlDos       = "SELECT url_id FROM temp_urlid";
        $urlGeneradas = $db->getAdapter()->fetchCol($sqlDos);

        $cantUrlGenerateds = count($urlGeneradas);

        //list($usec, $sec) = explode(' ', microtime());
        //$tiempo = $sec + $usec - $number;
        //echo "trajo consultas. ".$tiempo.PHP_EOL;
        if ($cantUrlGenerateds < $maxUrlIdsToGenerate) {
            $sql            = "SELECT url_id FROM anuncio_web";
            $urlRegistradas = $db->getAdapter()->fetchCol($sql);
        }

        while ($cantUrlGenerateds < $maxUrlIdsToGenerate) {
            do {
                $urlId = $genPassword->_genPassword(5);
                /*
                  $sql = "SELECT id FROM anuncio_web

                  WHERE url_id like '".$urlId."'";
                  $idsAnuncio = $db->getAdapter()->fetchCol($sql);


                  $sqlDos = "SELECT url_id FROM temp_urlid
                  WHERE url_id like '".$urlId."'";
                  $urlids = $db->getAdapter()->fetchCol($sqlDos);
                 */

                $existeAnuncio  = in_array($urlId, $urlRegistradas);
                $existeGenerado = in_array($urlId, $urlGeneradas);
                //if (count($idsAnuncio) > 0) {
                //if ($existeAnuncio) {
                //    echo $cantUrlGenerateds.": ".$urlId." repetido en tabla avisos".PHP_EOL;
                //}
                //if (count($urlids) > 0) {
                //if ($existeGenerado) {
                //    echo $cantUrlGenerateds.": ".$urlId." repetido en buffer".PHP_EOL;
                //}
            } while ($existeAnuncio || $existeGenerado);
            $_tu->insert(array('url_id' => $urlId));
            $cantUrlGenerateds++;

            //list($usec, $sec) = explode(' ', microtime());
            //$tiempo = $sec + $usec - $number;
            //echo $cantUrlGenerateds.": ".$urlId." time: ".$tiempo.PHP_EOL;

            $urlGeneradas[] = $urlId;
        }

        //list($usec, $sec) = explode(' ', microtime());
        //$number = $sec + $usec - $number;
        //echo $number.PHP_EOL;

        echo "Se termino el proceso".PHP_EOL;
    }

    // cron de correccion de index de postulantes de la tabla temp_lucene
    /*
     * Pasa que la tabla zendlucene no tenia el campo params del tipo TEXT ,lo tenia como
     * varchar(400) y no guardaba todo los datos asi que se hizo esta funcion para ingresar
     * los datos updateIndexPostulante y recrear esa tabla.
     */
    public function corregirIndexPostulante()
    {
        $objTemp = new Application_Model_TempLucene();
        $result  = $objTemp->getPostulantesFaltantes();
        foreach ($result as $item) {
            $namefunction = $item["namefunction"];
            $idupdate     = $item["idupdate"];
            $idinsert     = $item["idinsert"];
            if ($namefunction == "updateIndexPostulante") {
                $id              = $this->getNumeroCorregido($idupdate);
                $modelPostulante = new Application_Model_Postulante();
                $id              = -87;
                $objPostulante   = $modelPostulante->find($id);
                if ($objPostulante->count() > 0) {
                    //actualizacion --------------------------------------------
                    $arrayZL["idpostulante"] = $objPostulante[0]->id;
                    $arrayZL["foto"]         = $objPostulante[0]->path_foto;
                    $arrayZL["nombres"]      = $objPostulante[0]->nombres;
                    $arrayZL["apellidos"]    = $objPostulante[0]->apellidos;
                    $arrayZL["telefono"]     = $objPostulante[0]->telefono;
                    $arrayZL["slug"]         = $objPostulante[0]->slug;
                    $arrayZL["sexo"]         = $objPostulante[0]->sexo;
                    $fi                      = new DateTime();
                    $ff                      = new DateTime($objPostulante[0]->fecha_nac);
                    $arrayZL["edad"]         = $ff->diff($fi)->format('%y');
                    $arrayZL["fechanac"]     = $objPostulante[0]->fecha_nac;
                    $arrayZL["sexoclaves"]   = $objPostulante[0]->sexo;
                    $arrayZL["ubigeoclaves"] = $objPostulante[0]->id_ubigeo;

                    $ubi               = new Application_Model_Ubigeo();
                    $r                 = $ubi->find($objPostulante[0]->id_ubigeo);
                    $arrayZL["ubigeo"] = $r[0]->nombre;

                    //$zl = new ZendLucene();
                    //$zl->updateIndexPostulante($id, $arrayZL);
                    //-----------------------------------------------------------
                }
            }
        }
        echo "reestructuracion OK".PHP_EOL;
        $sql     = "
                DROP TABLE IF EXISTS `temp_lucene`;
                CREATE TABLE `temp_lucene` (
                  `id` INT(8) NOT NULL AUTO_INCREMENT,
                  `tipo` ENUM('avisos','postulantes','postulaciones') DEFAULT NULL,
                  `params` TEXT,
                  `namefunction` VARCHAR(150) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=MYISAM AUTO_INCREMENT=1 COMMENT=''
                                ROW_FORMAT=DEFAULT
                                CHARSET=latin1
                                COLLATE=latin1_swedish_ci;
            ";
        $adapter = new Application_Model_TempLucene();
        $adapter->getAdapter()->query($sql);
        echo "TABLA temp_lucene CREADA NUEVAMENTE".PHP_EOL;
    }

    public function getNumeroCorregido($n)
    {
        $numeros = "0123456789";
        $nfinal  = "";
        $n       = trim($n);
        for ($i = 0; $i < strlen($n); $i++) {
            $letra = substr($n, $i, 1);
            $na    = count(explode($letra, $numeros));
            if ($na > 1) $nfinal.=$letra;
            else break;
        }
        return $nfinal;
    }

    public function bloquearAvisosXIdEmpresaContadores($strIdEmp)
    {

        $config = Zend_Registry::get('config');

        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
        $buscamasUrl         = $config->apis->buscamas->url;
        $buscamasPublishUrl  = $config->apis->buscamas->publishUrl;

        $arrayIdEmp      = explode(",", $strIdEmp);
        $modelAnuncioWeb = new Application_Model_AnuncioWeb();
        $modelEmpresa    = new Application_Model_Empresa();
        $modelUsuEmp     = new Application_Model_UsuarioEmpresa();
        $modelUsuario    = new Application_Model_Usuario();

        foreach ($arrayIdEmp as $idEmp) {

            $arrayIdEmp = $modelEmpresa->getEmpresa($idEmp);
            //$zl = new ZendLucene();
            if ($arrayIdEmp != false) {

                $arrayUsuEmp = $modelUsuEmp->getAdministradores($idEmp);
                foreach ($arrayUsuEmp as $dataUE) {
                    $arrayUsuAdmin[] = $dataUE['id_usuario'];
                }
                $whereUsuEmp = $modelUsuEmp->getAdapter()->quoteInto('id in (?)',
                    $arrayUsuAdmin);
                $val         = $modelUsuario->update(array('activo' => 0),
                    $whereUsuEmp);

                echo 'Empresa '.$idEmp.' ha sido baneada. '.PHP_EOL;
                $sqlEmp = $modelAnuncioWeb->getAdapter()
                    ->select()
                    ->from('anuncio_web', array('url_id', 'id'))
                    ->where('id_empresa = ? ', $idEmp)
                    ->where('online = 1')
                    ->where('estado = ?',
                    Application_Model_AnuncioWeb::ESTADO_PAGADO);

                $urls = $modelAnuncioWeb->getAdapter()->fetchAll($sqlEmp);

                $_cache = null;

                foreach ($urls as $dataUrl) {
                    //$zl->eliminarDocumentoAviso($dataUrl['id']);
                    echo 'eliminacion del Cache avisos x empresa '.$dataUrl['url_id'].PHP_EOL;

                    $this->_cache = Zend_Registry::get('cache');
                    $this->_cache->remove('anuncio_web_'.$dataUrl['url_id']);
                }

                $sql = "UPDATE anuncio_web 
                        SET 
                            online = 0,
                            estado = '".Application_Model_AnuncioWeb::ESTADO_BANEADO."', 
                            estado_anterior = '".Application_Model_AnuncioWeb::ESTADO_PAGADO."',
                            fh_edicion = SYSDATE(),
                            fh_aviso_baja = SYSDATE()
                        WHERE id IN ( 
                            SELECT tmp.id 
                            FROM (SELECT aw.id
                                FROM anuncio_web AS aw 
                                LEFT JOIN compra AS c ON aw.id_compra = c.id 
                                INNER JOIN producto AS p ON aw.id_producto = p.id 
                                LEFT JOIN anuncio_impreso AS ai ON aw.id_anuncio_impreso = ai.id 
                                WHERE (aw.online = '1') AND (aw.borrador = 0) AND (aw.estado = 'pagado') 
                                AND (aw.id_empresa = ".$idEmp." ) AND (aw.eliminado = '0') 
                                ORDER BY aw.fh_pub DESC) 
                            AS tmp
                        )";

                $modelAnuncioWeb->getAdapter()->query($sql);

                foreach ($urls as $dataUrl) {
                    //$resultado = exec("curl -X POST -d 'api_key=" . $buscamasConsumerKey . "&nid=" . $dataUrl['id'] . "&site=" . $buscamasUrl . "' " . $buscamasPublishUrl);
                    $modelAnuncioWeb->getSolarAviso()->DeleteAvisoSolr($dataUrl['id']);
                }

                echo 'Avisos de la empresa '.$idEmp.' están baneados.'.PHP_EOL;
            } else {
                echo 'La empresa '.$idEmp.' no existe.'.PHP_EOL;
            }
        }
    }

    public function autorizarApi()
    {
        // TODO se comento porque se va a modificar. 
        /*
          $apiModel = new Application_Model_Api();
          $sqlEmp = $apiModel->getAdapter()
          ->select()
          ->from('api', array('id', 'username', 'password'));
          $dataApi = $apiModel->getAdapter()->fetchAll($sqlEmp);
          $config = Zend_Registry::get('config');
          $dirPswd = $config->app->direccionServidorProyectoAptitusApi;
          $dirConfig = realpath($dirPswd . '/application/configs/pswd');
          $filas = '';

          foreach ($dataApi as $api) {
          $filas .= $api['username'] . ':' . $config->app->apiUrl . ':' . $api['password'] . "\n";
          }
          file_put_contents($dirConfig, $filas);
         */
    }

    /**
     * Cron que realiza la baja de membresías
     */
    public function revisarMembresia()
    {
        $mailer        = new App_Controller_Action_Helper_Mail();
        $db            = new App_Db_Table_Abstract();
        $membesiaModel = new Application_Model_EmpresaMembresia();

        $cache = Zend_Registry::get('cache');

        $sqlEmp        = $membesiaModel->getAdapter()
            ->select()->from(
                'empresa_membresia',
                array('id', 'id_empresa', 'id_membresia',
                'fh_inicio_membresia', 'fh_fin_membresia', 'estado')
            )
            ->where('estado = ?', 'vigente');
        $dataMembresia = $membesiaModel->getAdapter()->fetchAll($sqlEmp);
        $actual        = new Zend_Date();
        $actual->set(0, Zend_Date::HOUR);
        $actual->set(0, Zend_Date::MINUTE);
        $actual->set(0, Zend_Date::SECOND);

        $config              = Zend_Registry::get('config');
        $concopy             = $config->cron->membresia->email->concopia;
        $nDiasMembresia      = $config->cron->membresia->notificacion->dias;
        $enableSendMembresia = $config->cron->membresia->notificacion->activo;
        $current             = new Zend_Date(date('Y-m-d'), 'YYYY-MM-dd');

        $nro               = 0;
        $algun_actualizado = false;

        foreach ($dataMembresia as $membresia) {
            $nro++;
            $empresaModel    = new Application_Model_Empresa;
            $dataEmpre       = $empresaModel->fetchRow('id = '.$membresia['id_empresa']);
            $nombreEmpresa   = $dataEmpre['razon_social'];
            $NombreComercial = $dataEmpre['razon_comercial'];

            if (strtotime($membresia['fh_fin_membresia']) < $actual->getTimestamp()) {

                $membresia['estado']          = 'no vigente';
                $membresia['fh_modificacion'] = date("Y-m-d H:i:s");
                $actualizados                 = $membesiaModel->update($membresia,
                    'id = '.$membresia['id']);

                $algun_actualizado = true;
                $activados         = $membesiaModel->activarSiguienteMembresia($membresia['id_empresa'],
                    $membresia['fh_fin_membresia']);

                if ($activados) {
                    echo '--> Membresía activada de id_empresa = '.$membresia['id_empresa']."\n";
                } else {
                    // enviar todas las postulaciones al primer nivel "postulantes"
                    $postulaciones = new Application_Model_Postulacion();
                    $postulaciones->reiniciarNivelPostulacion($membresia['id_empresa']);
                }
                echo 'Se actualizó la membresía con ID = '.$membresia['id']."\n";
            }
            //verificamos si la membresía está por vencer
            $dateFF = new Zend_Date($membresia['fh_fin_membresia'], 'YYYY-MM-dd');

            $days  = $dateFF->sub($current)->toValue();
            $ndias = ceil($days / 86400);

            if ($enableSendMembresia == 1 && $ndias == $nDiasMembresia) {
                $objuse    = new Application_Model_Usuario();
                $rsuse     = $objuse->getUsuarioByIdEmpresa($membresia['id_empresa']);
                $objfecIni = new Zend_Date($membresia['fh_inicio_membresia']);
                $objfecFin = new Zend_Date($membresia['fh_fin_membresia']);

                $modelUsuEmp = new Application_Model_UsuarioEmpresa();
                $arrayAdm    = $modelUsuEmp->getAdministradores($membresia['id_empresa']);

                $datamail = array(
                    'to' => $rsuse['email'],
                    'fecini' => $objfecIni->toString('dd/MM/yyyy'),
                    'fecfin' => $objfecFin->toString('dd/MM/yyyy'),
                    'id_empresa' => $membresia['id_empresa'],
                    'email' => $rsuse['email'],
                    'nrodias' => $nDiasMembresia,
                    'nombre_empresa' => $nombreEmpresa,
                    'Nombre_Comercial' => $NombreComercial
                );

                switch ($membresia['id_membresia']) {
//                    case 1: {
//                            //$mailer->vencerMembresiaEsencial($datamail);
//                            $datamail['to'] = $concopy;
//                            $mailer->vencerMembresiaEsencial($datamail);
//                            foreach ($arrayAdm as $user) {
//                                $datamail['to'] = $user['email'];
//                                $mailer->vencerMembresiaEsencial($datamail);
//                            }
//                            break;
//                        }
                    case Application_Model_Membresia::SELECTO: {
                            //$mailer->vencerMembresiaSelecto($datamail);
                            $datamail['to'] = $concopy;
                            $mailer->vencerMembresiaSelecto($datamail);
                            foreach ($arrayAdm as $user) {
                                $datamail['to'] = $user['email'];
                                $mailer->vencerMembresiaSelecto($datamail);
                            }
                            break;
                        }
                    case Application_Model_Membresia::PREMIUM: {
                            //$mailer->vencerMembresiaPremium($datamail);
                            $datamail['to'] = $concopy;
                            $mailer->vencerMembresiaPremium($datamail);
                            foreach ($arrayAdm as $user) {
                                $datamail['to'] = $user['email'];
                                $mailer->vencerMembresiaPremium($datamail);
                            }
                            break;
                        }
                }

                echo $nro." Notificacion Membresia Enviada \n";
            }
        }

        if ($algun_actualizado) {
            // Actualizar Cache:
            // zend_cache---Empresa_getEmpresasTCN getCompanyWithMembresia
            $cache->remove('Empresa_getEmpresasTCN');
            $cache->remove('Empresa_getCompanyWithMembresia');
        }

        echo "se realizó los cambios con éxito \n";
    }

    // @codingStandardsIgnoreEnd
    public function darDeBajaMembresias()
    {
        $fecHoy = new Zend_Date();
        $db     = new App_Db_Table_Abstract();

        $where         = " WHERE DATE(e.fh_fin_membresia) < CURDATE() AND e.estado = 'vigente';";
        $sql           = "SELECT e.id AS em_id FROM empresa_membresia AS e ".$where;
        $idsMembresias = $db->getAdapter()->fetchCol($sql);

        if (count($idsMembresias) == 0) {
            echo "No hay membresias para dar de baja".PHP_EOL;
            return;
        }

        $sql = "UPDATE empresa_membresia
                SET estado = 'no vigente'
                WHERE ".$db->getAdapter()->quoteInto('id IN (?)',
                $idsMembresias);
        $db->getAdapter()->query($sql);

        // enviar todas las postulaciones al primer nivel "postulantes"
        $sql      = "SELECT e.id_empresa AS em_id FROM empresa_membresia AS e
                WHERE ".$db->getAdapter()->quoteInto('id IN (?)',
                $idsMembresias);
        $empresas = $db->getAdapter()->fetchCol($sql);

        if (count($empresas)) {
            $postulaciones = new Application_Model_Postulacion();
            foreach ($empresas as $idEmpresa) {
                $postulaciones->reiniciarNivelPostulacion($idEmpresa);
            }
        }

        echo count($idsMembresias)." Membresias dadas de baja[OK]".PHP_EOL;
    }

    public function calculaMatchAvisosPostulante()
    {
        ini_set('max_execution_time', 3600);
        $db                       = new App_Db_Table_Abstract();
        $config                   = Zend_Registry::get('config');
        $minOpcionesAviso         = $config->profileMatch->aviso->opcionesMinimo;
        $tiposAviso               = $config->profileMatch->aviso->empresa->tipos;
        $diasUltimoLogin          = $config->profileMatch->postulante->diasUltimologin;
        $minimoDatosProfesionales = $config->profileMatch->postulante->datosProfesionalesMinimo;
        $porcentajeMatch          = $config->profileMatch->match->porcentajeMinimo;
        $limitAviso               = 1000;
        $limitPostulante          = 1000;

        $total = Application_Model_AnuncioWeb::getTotalAnunciosForMatch($minOpcionesAviso,
                explode(',', $tiposAviso));

        $p      = new App_Util_Console_ProgressBar(
            'Avisos Procesados %fraction% [%bar%] %percent% ETA: %estimate%',
            '=>', '-', 120, $total
        );
        $i      = 0;
        $p->update($i++);
        $date   = date('Y-m-d');
        while (count(
            $avisos = Application_Model_AnuncioWeb::
            getAnunciosForMatch($minOpcionesAviso, explode(',', $tiposAviso),
                $limitAviso)
        ) > 0) {
            foreach ($avisos as $aviso) {
                $offsetPostulante = 0;
                try {
                    $db->getAdapter()->beginTransaction();
                    while (count(
                        $postulantes = Application_Model_Postulante
                        ::getPostulantesMatchingAviso(
                            $aviso['id'], $porcentajeMatch, $diasUltimoLogin,
                            $minimoDatosProfesionales, $limitPostulante,
                            $offsetPostulante
                        )
                    ) > 0) {
                        $offsetPostulante = $offsetPostulante + 1000;
                        foreach ($postulantes as $postulante) {
                            $prospecto = array(
                                'id_postulante' => $postulante['id'],
                                'id_anuncio_web' => $aviso['id'],
                                'nombres' => $postulante['nombres'],
                                'apellidos' => $postulante['apellidos'],
                                'postulante_slug' => $postulante['postulante_slug'],
                                'sexo' => $postulante['sexo'],
                                'telefono' => $postulante['telefono'],
                                'celular' => $postulante['celular'],
                                'email' => $postulante['email'],
                                'puesto_nombre' => $aviso['puesto_nombre'],
                                'puesto' => $aviso['puesto'],
                                'anuncio_web_slug' => $aviso['anuncio_web_slug'],
                                'url_id' => $aviso['url_id'],
                                'empresa_razon_social' => $aviso['razon_social'],
                                'empresa_rs' => $aviso['empresa_rs'],
                                'fh_pub' => $aviso['fh_pub'],
                                'fh_vencimiento' => $aviso['fh_vencimiento'],
                                'mostrar_empresa' => $aviso['mostrar_empresa'],
                                'tipo' => $aviso['tipo'],
                                'match' => $postulante['apmatch'],
                            );
                            $db->getAdapter()->insert('anuncio_postulante_match',
                                $prospecto);
                            echo $postulante['id'].' '.$postulante['apmatch'].' '.
                            $postulante['nombres'].PHP_EOL;
                        }
                    }
                    $db->getAdapter()->update(
                        'anuncio_web',
                        array('match_calculado' => 1, 'fh_proceso_match' => $date)
                    );
                    $db->getAdapter()->commit();
                    $p->update($i++);
                } catch (Exception $e) {
                    $db->getAdapter()->commit();
                }
            }
        }
    }

    public function notificacionAvisosPorVencer()
    {
        $mailer       = new App_Controller_Action_Helper_Mail();
        $objAW        = new Application_Model_AnuncioWeb();
        $sql          = $objAW->getAdapter()
                ->select()
                ->from(
                    array('aw' => 'anuncio_web'),
                    array(
                    'id', 'puesto', 'fh_vencimiento',
                    'fh_vencimiento_proceso', 'id_empresa',
                    'fh_pub', 'url_id', 'id_puesto', 'slug'
                    )
                )->join(
                array('e' => 'empresa'), 'aw.id_empresa = e.id',
                array('nombre_comercial')
            )->join(
            array('u' => 'usuario'), 'e.id_usuario = u.id', array('email')
        );
        $data         = $objAW->getAdapter()->fetchAll($sql);
        //var_dump($data); exit;
        $config       = Zend_Registry::get('config');
        $nDiasAnuncio = $config->cron->anuncio->notificacion->dias;
        $nDiasProceso = $config->cron->proceso->notificacion->dias;

        $enableAnuncio = $config->cron->anuncio->notificacion->activo;
        $enableProceso = $config->cron->proceso->notificacion->activo;

        $nro     = 0;
        $current = new Zend_Date(date('Y-m-d'), 'YYYY-MM-dd');
        try {
            //Notificacion Aviso Vencido
            if ($enableAnuncio == 1) {
                foreach ($data as $key => $value) {
                    $nro++;
                    $dateFV = new Zend_Date($value['fh_vencimiento'],
                        'YYYY-MM-dd');
                    $days   = $dateFV->sub($current)->toValue();
                    $ndias  = ceil($days / 86400);
                    //echo $ndias."\n";
                    if ($ndias == $nDiasAnuncio) {
                        $dataMail = array(
                            'to' => $value['email'],
                            'nombrePuesto' => $value['puesto'],
                            'dias' => $ndias,
                            'empresa' => $value['nombre_comercial'],
                            'usuario' => $value['email'],
                            'fechaPub' => $value['fh_pub'],
                            'fechaVenc' => $value['fh_vencimiento'],
                            'fechaVencPro' => $value['fh_vencimiento_proceso'],
                            'urlId' => $value['url_id'],
                            'slug' => $value['slug'],
                            'avisoId' => $value['id_puesto']
                        );
                        $mailer->avisoVencer($dataMail);
                        echo $nro.' Notificacion Anuncio Enviado'; //break;
                    }
                }
            }

            //Notificacion Proceso Vencido
            if ($enableProceso == 1) {
                foreach ($data as $key => $value) {
                    $nro++;
                    $dateFV = new Zend_Date($value['fh_vencimiento_proceso'],
                        'YYYY-MM-dd');
                    $days   = $dateFV->sub($current)->toValue();
                    $ndias  = ceil($days / 86400);
                    //echo $ndias."\n";
                    if ($ndias == $nDiasProceso) {
                        $dataMail = array(
                            'to' => $value['email'],
                            'nombrePuesto' => $value['puesto'],
                            'dias' => $ndias,
                            'empresa' => $value['nombre_comercial'],
                            'usuario' => $value['email'],
                            'fechaPub' => $value['fh_pub'],
                            'fechaVenc' => $value['fh_vencimiento_proceso'],
                            'urlId' => $value['url_id'],
                            'avisoId' => $value['id_puesto']
                        );
                        $mailer->procesoVencer($dataMail);
                        echo $nro.' Notificacion Proceso Enviado'; //exit;
                    }
                }
            }
        } catch (Exception $exc) {
            echo $exc->getMessage();
        }
    }

    public function actualizarPrioridadResultadoBusqueda()
    {

        $config = Zend_Registry::get('config');

        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
        $buscamasUrl         = $config->apis->buscamas->url;
        $buscamasPublishUrl  = $config->apis->buscamas->publishUrl;


        $aw = new Application_Model_AnuncioWeb();
        $db = $aw->getAdapter();

        $id                  = 0;
        $resultado           = 1;
        //Actualizar índices en Buscamas
        $sql                 = $aw->select()->from('anuncio_web', 'id')
                ->where('online = ?', 1)
                ->where('fh_vencimiento_prioridad < NOW()')
                ->where('prioridad_ndias_busqueda >= ?', 0)
                ->where('prioridad < ?', 6)->query();
        $avisosPorActualizar = $sql->fetchAll();
        $awSolr              = new Solr_SolrAviso();
        foreach ($avisosPorActualizar as $dataAviso) {
            try {
                $wherePrioridad = $db->quoteInto('id=?', (int) $dataAviso['id']);
                $dataprioridad  = array(
                    'prioridad' => 6,
                    'prioridad_de_tipo' => 'web'
                );
                $db->beginTransaction();
                $id             = $aw->update($dataprioridad, $wherePrioridad);
                $dataPrioridad  = array(
                    'prioridad' => $dataprioridad['prioridad'],
                    'id' => (int) $dataAviso['id']
                );
                $resultado      = $awSolr->ActualizaPrioridad($dataPrioridad);
                if ($id && !$resultado) {
                    echo 'id Aviso actualizado :'.$dataAviso['id'].PHP_EOL;
                    $db->commit();
                }
                echo 'OK'.PHP_EOL;
            } catch (Exception $exc) {
                $db->rollBack();
                echo 'Error: '.$exc->getMessage().PHP_EOL;
            }
        }
    }

    public function matchAvisosPostulantes()
    {
        $config                   = Zend_Registry::get('config');
        $minOpcionesAviso         = $config->profileMatch->aviso->opcionesMinimo;
        $tiposAviso               = $config->profileMatch->aviso->empresa->tipos;
        $diasRestantesAviso       = $config->profileMatch->aviso->vencimiento->diasrestantes;
        $diasUltimoLogin          = $config->profileMatch->postulante->diasUltimologin;
        $minimoDatosProfesionales = $config->profileMatch->postulante->datosProfesionalesMinimo;
        $porcentajeMatch          = $config->profileMatch->match->porcentajeMinimo;

        $cnx = new App_Db_Table_Abstract();
        $db  = $cnx->getAdapter();

        echo "Preparando tablas...".PHP_EOL;
        $aw  = new Application_Model_AnuncioWeb();
        $ap  = new Application_Model_AnuncioProfile();
        $pp  = new Application_Model_PostulanteProfile();
        $amt = new Application_Model_AnuncioMatchTemp();
        $pmt = new Application_Model_PostulanteMatchTemp();
        $db->beginTransaction();

        $aw->update(array('match_calculado' => 0),
            $db->quoteInto('match_calculado = ?', 1));

        $ap->delete('1=1');
        $pp->delete('1=1');
        $amt->delete('1=1');
        $pmt->delete('1=1');
        $db->commit();

        echo "Procesando perfil de anuncios...".PHP_EOL;
        $whereAnuncio  = Application_Model_AnuncioPostulanteMatch::
            getQueryAnunciosForMatch(explode(',', $tiposAviso),
                $diasRestantesAviso);
        $sqlAnuncio    = Application_Model_AnuncioPostulanteMatch::
            getAnunciosPrint($whereAnuncio);
        $sqlNewTableAW = Application_Model_AnuncioPostulanteMatch::
            getAnuncioNewTable($whereAnuncio);

        $insertNewTableAW = $db->query(
            "INSERT INTO anuncio_match_temp (`id_empresa`, `id_anuncio_web`, `total_estudio`,
            `total_experiencia`, `total_idioma`, `total_computo`, `peso_estudio`,
            `peso_experiencia`, `peso_idioma`, `peso_computo`, `total_peso`)".$sqlNewTableAW->assemble()
        );

        $queryAnuncio = $db->query(
            "INSERT INTO anuncio_profile  (`id_anuncio_web`,`item`,`print`,`bottom`,`top`) ".$sqlAnuncio->assemble()
        );

        echo "Procesando perfil de postulantes...".PHP_EOL;
        Application_Model_AnuncioPostulanteMatch::markPostulantesForMatch($diasUltimoLogin);
        $wherePostulante = Application_Model_AnuncioPostulanteMatch::
            getQueryPostulantesForMatch();
        $sqlPostulante   = Application_Model_AnuncioPostulanteMatch::
            getPostulantesPrint($wherePostulante);
        $sqlNewTableP    = Application_Model_AnuncioPostulanteMatch::
            getPostulanteNewTable($wherePostulante);

//        echo $sqlPostulante->assemble();exit;
        $sqlNewTableP = $db->query(
            "INSERT INTO postulante_match_temp  (`id_postulante`) ".$sqlNewTableP->assemble()
        );

        $queryPostulante = $db->query(
            "INSERT INTO postulante_profile  (`id_postulante`,`item`,`print`) ".$sqlPostulante->assemble()
        );

        echo 'OK'.PHP_EOL;
    }

    public function comparacionMatchPostulante()
    {
        $config           = Zend_Registry::get('config');
        $pesoIdioma       = $config->profileMatch->match->peso->idioma;
        $pesoComputo      = $config->profileMatch->match->peso->computo;
        $pesoExperiencia  = $config->profileMatch->match->peso->experiencia;
        $pesoEstudio      = $config->profileMatch->match->peso->estudio;
        $avisoMinimoItems = $config->profileMatch->aviso->opcionesMinimo;

        $cnx    = new App_Db_Table_Abstract();
        $db     = $cnx->getAdapter();
        $ap     = new Application_Model_AnuncioMatchTemp();
        $pp     = new Application_Model_PostulanteMatchTemp();
        $apm    = new Application_Model_AnuncioPostulanteMatch();
        $totalA = $ap->getTotalAnuncios($avisoMinimoItems);
        //$arrayIdPostulante = $pp->getTotalPostulantes();

        $offsetAnuncios = 0;

        $dbTable  = new Zend_Db_Table();
        $modelAPM = new Application_Model_AnuncioPostulanteMatch();
        if (!empty($totalA)) {
            $p        = new App_Util_Console_ProgressBar(
                'Avisos Procesados %fraction% [%bar%] %percent% ETA: %estimate%',
                '=>', '-', 120, $totalA
            );
            $i        = 1;
            while ($anuncios = $ap->getAnuncios($avisoMinimoItems,
            $offsetAnuncios)) {
                foreach ($anuncios as $anuncio) {

                    $offset     = 0;
                    $arrayMatch = $apm->matchAnuncios(
                        $anuncio['id_empresa'], $anuncio['id_anuncio_web'],
                        $anuncio['peso_idioma'], $anuncio['peso_computo'],
                        $anuncio['peso_estudio'], $anuncio['peso_experiencia'],
                        $anuncio['total_peso'],
                        $config->profileMatch->match->porcentajeMinimo, $offset
                    );
                    $p->update($i++);
                }
                $offsetAnuncios += 1000;
            }
            // @codingStandardsIgnoreEnd
            echo PHP_EOL.PHP_EOL.'Match Completado'.PHP_EOL;
        } else {
            echo PHP_EOL.PHP_EOL.'No hay registros para procesar'.PHP_EOL;
        }
    }

    public function borrarCvsTemporales()
    {
        $config           = Zend_Registry::get('config');
        $diretoryTemporal = realpath($config->urls->app->elementsCvRootTmp);

        array_map('unlink',
            glob(
                $diretoryTemporal.DIRECTORY_SEPARATOR.'*.*'));
    }

    public function generateSitemap()
    {
        $adWebModel = new Application_Model_AnuncioWeb;
        $sitemap    = new App_Util_GenerateSitemap;

        $config = Zend_Registry::get('config');

        //$domain = $config->app->siteUrl;
        $domain = $config->sitemap->domain;

        $url        = $config->sitemap->url;
        $changeFreq = $config->sitemap->changeFreq;
        $directory  = $config->urls->app->publicRoot;

        try {
            $avisosPorLote = $config->sitemap->numeroAvisos;

            $numAviso = $adWebModel->numAvisos();

            $totalPages = ceil($numAviso / $avisosPorLote);

            echo PHP_EOL.'Inicio: '.date('h:i:s').PHP_EOL;

            $ini = 0;

            for ($i = 1; $i <= $totalPages; $i++) {

                $xmlBuscador = $directory.'sitemap_avisos_'.$i.'.xml';
                if (file_exists($xmlBuscador)) unlink($xmlBuscador);

                $ads = $adWebModel->obtenerVarios(
                    $ini, $avisosPorLote,
                    array('id', 'puesto', 'url_id', 'slug', 'fh_pub'));


                $urlBase = $domain.'/'.$url;

                $sitemap->setFileName('sitemap_avisos_'.$i.'.xml');
                $sitemap->setRecods($ads);
                $sitemap->setDirectory($directory);
                $sitemap->setUrlBase($urlBase);
                $sitemap->setChangeFreq($changeFreq);

                $sitemap->save();

                $ini += $avisosPorLote;
            }


            //Generar sitemap
            $cacheArchivo = $directory.'sitemap.xml';
            if (file_exists($cacheArchivo)) {
                unlink($cacheArchivo);
            }

            $sitemapUrl              = 'http://www.sitemaps.org/schemas/sitemap/0.9';
            $dom                     = new DOMDocument('1.0', 'utf-8');
            $dom->preserveWhiteSpace = false;
            $dom->appendChild($dom->createElementNS($sitemapUrl, 'sitemapindex'));
            $sxe                     = simplexml_import_dom($dom);

            if ($totalPages > 0) {
                for ($i = 1; $i <= $totalPages; $i++) {
                    $xmlBuscador = 'sitemap_avisos_'.$i.'.xml';
                    if (file_exists($directory.$xmlBuscador)) {
                        $ad = $sxe->addchild("sitemap");
                        $ad->addChild("loc", $domain.'/'.$xmlBuscador);
                    }
                }
            }

            $url_node = $sxe->addchild("url");
            $url_node->addChild("loc", $domain.'/ultimos');
            $url_node->addChild("lastmod", date('c'));
            $url_node->addChild("changefreq", $changeFreq);
            $url_node->addChild("priority", 0.5);

            $dom->formatOutput = true;

            $dom->save($cacheArchivo);

            echo PHP_EOL.'SITEMAP GENERADO SATISFACTORIAMENTE';
            echo PHP_EOL.'Fin: '.date('h:i:s').PHP_EOL;
        } catch (Exception $e) {
            echo 'ocurrio un error :'.$e->getMessage();
        }
    }

    public function notifyReferrals()
    {
        $referralsModel = new Application_Model_Referenciado;

        $select = $referralsModel->obtenerNoNotificados();

        $paginator = Zend_Paginator::factory($select);
        $paginator->setItemCountPerPage(100);

        $pages      = $paginator->getPages();
        $totalPages = $pages->pageCount;

        echo PHP_EOL.'INICIANDO ENVIO'.PHP_EOL.PHP_EOL;

        if ($paginator->getTotalItemCount() == 0) {
            echo 'No hay referidos por notificar'.PHP_EOL;
            return;
        }

        for ($page = 1; $page <= $totalPages; $page++) {
            $referrals = $paginator->getItemsByPage(1);
            foreach ($referrals as $referred) {
                $mail            = array();
                $mail['subject'] = 'Aptitus';
                $mail['url_id']  = $referred['url_id'];
                $mail['slug']    = $referred['slug'];
                $mail['to']      = $referred['email'];
                $mail['puesto']  = $referred['puesto'];
                $mail['empresa'] = $referred['empresa_rs'];
                $mail['nombre']  = $referred['nombre'].' '.
                    $referred['apellidos'];

                $sendEmail = new App_Controller_Action_Helper_Mail();

                $tipo = $referred['tipo'];

                try {
                    if ($tipo == Application_Model_Referenciado::TIPO_REFERIDO)
                            $sendEmail->invitarReferido($mail);

                    if ($tipo == Application_Model_Referenciado::TIPO_REFERENCIADO)
                            $sendEmail->invitarReferenciado($mail);

                    $referralsModel->notificado($referred['id']);
                    echo $referred['email'].' ha sido notificado'.PHP_EOL;
                } catch (Exception $e) {
                    echo PHP_EOL.'OCURRIO UN ERROR: '.
                    $e->getMessage().PHP_EOL;
                    return;
                }
                unset($mail);
            }
            unset($referrals);
        }
    }

    //Ejecutarse una vez por día
    public function enviarListadoPostulantesEmpresa()
    {
        $mailer = new App_Controller_Action_Helper_Mail();

        $config              = Zend_Registry::get('config');
        $primeraNotificacion = $config->primera->notificacion->dias->publicacion
            - 1;
        $segundaNotificacion = $config->segunda->notificacion->dias->publicacion
            - 5;

        $diasPrimNotif  = $config->primera->notificacion->dias->publicacion;
        $diasSegunNotif = $config->segunda->notificacion->dias->publicacion;

        $actual = new Zend_Date();
        $actual->set(0, Zend_Date::HOUR);
        $actual->set(0, Zend_Date::MINUTE);
        $actual->set(0, Zend_Date::SECOND);

        $anuncioWeb     = new Application_Model_AnuncioWeb;
        $postulante     = new Application_Model_Postulante;
        $usuarioEmpresa = new Application_Model_Usuario;

        $dataAvisos = $anuncioWeb->AvisosActivos('TODOS');
        $validator  = new Zend_Validate_EmailAddress();

        //$dp = $postulante->postulantesxAviso(726824);
        $correos = 0;
        foreach ($dataAvisos as $avisos) {

            $idAviso = $avisos['id'];
            $creado  = $avisos['creado_por'];

            $fh_pub = new Zend_Date($avisos['fh_pub']);
            $fh_pub->set(0, Zend_Date::HOUR);
            $fh_pub->set(0, Zend_Date::MINUTE);
            $fh_pub->set(0, Zend_Date::SECOND);

            $fh_fin_proceso = new Zend_Date($avisos['fecha_proceso']);
            $fh_fin_proceso->set(0, Zend_Date::HOUR);
            $fh_fin_proceso->set(0, Zend_Date::MINUTE);
            $fh_fin_proceso->set(0, Zend_Date::SECOND);

            //5to dia de publicacion cuenta el mismo dia
            $fh_pub->addDay($primeraNotificacion);
            if ($fh_pub == $actual) {
                $listaPostulantes = $postulante->postulantesxAviso($idAviso);

                //Validar que haya postulantes para enviar correo
                if (!empty($listaPostulantes)) {
                    //Enviar mail
                    $totalPostulantes    = count($postulante->totalPostulantesxAviso($idAviso));
                    $emailUsuarioEmpresa = $usuarioEmpresa->correoUsuarioxAnuncio($idAviso,
                        $creado);
                    $nombreEmpresa       = $usuarioEmpresa->nombreEmpresaxAnuncio($idAviso);


                    $titulo   = 'Srs. '.$nombreEmpresa.', su aviso '.$avisos['puesto'].
                        ' ha recibido <span style="color:#006c9d">'.
                        $totalPostulantes.' <br> postulantes</span>
                            en los últimos '.$diasPrimNotif.' días.';
                    $dataMail = array(
                        'to' => $emailUsuarioEmpresa,
                        'titulo' => $titulo,
                        'idAviso' => $idAviso,
                        'nombrePuesto' => $avisos['puesto'],
                        'postulante' => $listaPostulantes,
                        'numPostulantes' => count($listaPostulantes)
                    );

                    if ($validator->isValid($emailUsuarioEmpresa)) {
                        $mailer->listaPostulanteAviso($dataMail);
                        $correos++;
                    }

                    if ($validator->isValid($avisos['correo'])) {
                        $dataMail['to'] = $avisos['correo'];
                        $mailer->listaPostulanteAviso($dataMail);
                    }
                }
            }

            //10mo dia de publicacion
            $fh_pub->addDay($segundaNotificacion);
            if ($fh_pub == $actual) {
                $listaPostulantes = $postulante->postulantesxAviso($idAviso);

                //Validar que haya postulantes para enviar correo
                if (!empty($listaPostulantes)) {
                    //Enviar mail
                    $totalPostulantes    = count($postulante->totalPostulantesxAviso($idAviso));
                    $emailUsuarioEmpresa = $usuarioEmpresa->correoUsuarioxAnuncio($idAviso,
                        $creado);
                    $nombreEmpresa       = $usuarioEmpresa->nombreEmpresaxAnuncio($idAviso);

                    $titulo   = 'Srs. '.$nombreEmpresa.', su aviso '.$avisos['puesto'].
                        ' ha recibido <span style="color:#006c9d">'.
                        $totalPostulantes.' <br> postulantes</span>
                            en los últimos '.$diasSegunNotif.' días.';
                    $dataMail = array(
                        'to' => $emailUsuarioEmpresa,
                        'titulo' => $titulo,
                        'idAviso' => $idAviso,
                        'nombrePuesto' => $avisos['puesto'],
                        'postulante' => $listaPostulantes,
                        'numPostulantes' => count($listaPostulantes)
                    );

                    if ($validator->isValid($emailUsuarioEmpresa)) {
                        $mailer->listaPostulanteAviso($dataMail);
                    }


                    if ($validator->isValid($avisos['correo'])) {
                        $dataMail['to'] = $avisos['correo'];
                        $mailer->listaPostulanteAviso($dataMail);
                    }
                }
            }

            //fin del proceso
            if ($fh_fin_proceso == $actual) {
                $listaPostulantes = $postulante->postulantesxAviso($idAviso);

                //Validar que haya postulantes para enviar correo
                if (!empty($listaPostulantes)) {
                    //Enviar mail
                    $totalPostulantes    = count($postulante->totalPostulantesxAviso($idAviso));
                    $emailUsuarioEmpresa = $usuarioEmpresa->correoUsuarioxAnuncio($idAviso,
                        $creado);
                    $nombreEmpresa       = $usuarioEmpresa->nombreEmpresaxAnuncio($idAviso);

                    $dataMail = array(
                        'to' => $emailUsuarioEmpresa,
                        'titulo' => 'El proceso del aviso '.$avisos['puesto'].
                        ' ha finalizado.Tiene '.$totalPostulantes.' postulantes.',
                        'idAviso' => $idAviso,
                        'nombrePuesto' => $avisos['puesto'],
                        'postulante' => $listaPostulantes,
                        'numPostulantes' => count($listaPostulantes)
                    );

                    if ($validator->isValid($emailUsuarioEmpresa)) {
                        $mailer->listaPostulanteAviso($dataMail);
                    }

                    if ($validator->isValid($avisos['correo'])) {
                        $dataMail['to'] = $avisos['correo'];
                        $mailer->listaPostulanteAviso($dataMail);
                    }
                }
            }
        }

        echo "se enviaron las notificaciones éxito \n";
    }

    //Cron que genera los avisos que se encuentran activos en APTiTUS y los indexa a Buscamas
    public function buscamasIndexar()
    {

        $anuncioWeb = new Application_Model_AnuncioWeb;

        $config = Zend_Registry::get('config');

//        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
//        $buscamasUrl = $config->apis->buscamas->url;
//        $buscamasPublishUrl = $config->apis->buscamas->publishUrl;

        echo "Inicio: ".date('H:i:s').PHP_EOL;

        $dataAvisos = $anuncioWeb->servicioRestBuscaMasIndexar();

        $contador         = 0;
        $numAvisoBuscamas = 0;
        $lote             = 5000;

        foreach ($dataAvisos as $avisos) {
            $idAviso   = $avisos['source_id'];
            $resultado = $anuncioWeb->getSolarAviso()->addAvisoSolr($idAviso);
            //Ejecutar indexación a buscamas por cada aviso 
            //$resultado = exec("curl -X POST -d 'api_key=" . $buscamasConsumerKey . "&nid=" . $idAviso . "&site=" . $buscamasUrl . "' " . $buscamasPublishUrl);
            echo $resultado.PHP_EOL;
            $contador++;
            $numAvisoBuscamas++;
            sleep(1);

            //Cada vez que llega a indexarse 5000 avisos se hace un corte
            // de 5 minutos y vuelve a indexarse los siguientes avisos
            if ($contador == $lote) { //5000
                sleep(300); //5 MINUTOS CUANDO LLEGUE A 5000
                $contador = 0;
            }
        }

        echo "Se indexaron ".$numAvisoBuscamas." avisos a Buscamas.".PHP_EOL;
        echo "Fin: ".date('H:i:s').PHP_EOL;
    }

    //Cron que actualiza la prioridad a los avisos e indexa y actualiza la prioridad en buscamas
    public function actualizarPrioridadAvisosBuscamas()
    {

        //$config = Zend_Registry::get('config');
//        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
//        $buscamasUrl = $config->apis->buscamas->url;
//        $buscamasPublishUrl = $config->apis->buscamas->publishUrl;

        try {
            $aw     = new Application_Model_AnuncioWeb();
            $avisos = $aw->avisosActualizarPrioridadBuscamMas();

            $db = $aw->getAdapter();
            $db->beginTransaction();

            foreach ($avisos as $aviso) {

                $idAviso = $aviso['id'];

                $data  = array(
                    'prioridad' => 6,
                    'prioridad_de_tipo' => 'web'
                );
                $where = array('id = ?' => $idAviso);
                $aw->update($data, $where);
            }

            $db->commit();
            $awSolr = new Solr_SolrAviso();
            foreach ($avisos as $aviso) {
                $idAviso   = $aviso['id'];
                //$resultado = exec("curl -X POST -d 'api_key=" . $buscamasConsumerKey . "&nid=" . $idAviso . "&site=" . $buscamasUrl . "' " . $buscamasPublishUrl);
                $resultado = $awSolr->addAvisoSolr($idAviso);
                echo $idAviso.'-'.$resultado.'|'.PHP_EOL;
            }

            echo 'OK'.PHP_EOL;
        } catch (Exception $exc) {
            $db->rollBack();
            echo 'Error: '.$exc->getMessage().PHP_EOL;
        }
    }

    //Actualiza avisos a buscamas que fueron modificados por BD
    public function buscamasActualizarAvisos()
    {

        //$config = Zend_Registry::get('config');
//        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
//        $buscamasUrl = $config->apis->buscamas->url;
//        $buscamasPublishUrl = $config->apis->buscamas->publishUrl;

        try {
            $aw     = new Application_Model_AnuncioWeb();
            $avisos = $aw->avisosActualizadoBDBuscamas();

            $contadorAvisos = 0;

            foreach ($avisos as $aviso) {

                $idAviso = $aviso['id'];

                $data  = array('buscamas' => 1);
                $where = array('id = ?' => $idAviso);
                $aw->update($data, $where);
                $contadorAvisos++;
            }

            foreach ($avisos as $aviso) {
                $idAviso   = $aviso['id'];
                //$resultado = exec("curl -X POST -d 'api_key=" . $buscamasConsumerKey . "&nid=" . $idAviso . "&site=" . $buscamasUrl . "' " . $buscamasPublishUrl);
                $resultado = $aw->getSolarAviso()->addAvisoSolr($idAviso);
                echo $resultado.PHP_EOL;
            }


            echo 'Se indexaron en total '.$contadorAvisos.' avisos en Solar'.PHP_EOL;
            echo 'OK'.PHP_EOL;
        } catch (Exception $exc) {
            echo 'Error: '.$exc->getMessage().PHP_EOL;
        }
    }

    public function buscamasIndexarTest()
    {

        $anuncioWeb = new Application_Model_AnuncioWeb;

        //$config = Zend_Registry::get('config');
//        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
//        $buscamasUrl = $config->apis->buscamas->url;
//        $buscamasPublishUrl = $config->apis->buscamas->publishUrl;

        echo "Inicio: ".date('H:i:s').PHP_EOL;

        //Obtiene id de avisos para su indexación
        $dataAvisos = $anuncioWeb->avisosIndexarNew();
        //echo count($dataAvisos);exit;
        foreach ($dataAvisos as $avisos) {
            $idAviso   = $avisos['id'];
            $resultado = $anuncioWeb->getSolarAviso()->addAvisoSolr($idAviso);
            //Ejecutar indexación a buscamas por cada aviso 
            //$resultado = exec("curl -X POST -d 'api_key=" . $buscamasConsumerKey . "&nid=" . $idAviso . "&site=" . $buscamasUrl . "' " . $buscamasPublishUrl);
            echo $resultado.PHP_EOL;
        }

        echo "Fin: ".date('H:i:s').PHP_EOL;
    }

    public function buscamasSendSql($sql)
    {

        $anuncioWeb = new Application_Model_AnuncioWeb;

        //$config = Zend_Registry::get('config');
//        $buscamasConsumerKey = $config->apis->buscamas->consumerKey;
//        $buscamasUrl = $config->apis->buscamas->url;
//        $buscamasPublishUrl = $config->apis->buscamas->publishUrl;

        echo "Inicio: ".date('H:i:s').PHP_EOL;
        echo $sql;
        //Obtiene id de avisos para su indexación
        $dataAvisos = $anuncioWeb->avisosIndexarNew();
        foreach ($dataAvisos as $avisos) {
            $idAviso   = $avisos['id'];
            $resultado = $anuncioWeb->getSolarAviso()->addAvisoSolr($idAviso);
            //Ejecutar indexación a buscamas por cada aviso 
            //$resultado = exec("curl -X POST -d 'api_key=" . $buscamasConsumerKey . "&nid=" . $idAviso . "&site=" . $buscamasUrl . "' " . $buscamasPublishUrl);
            echo $resultado.PHP_EOL;
        }

        echo "Fin: ".date('H:i:s').PHP_EOL;
    }

    public function ActualizaconAdecsys()
    {

        $CompraAdecsysCodigo = new Application_Model_CompraAdecsysCodigo();


        $rs = $CompraAdecsysCodigo->getIDCodCompra();

        foreach ($rs as $compra) {
            $this->_helper->AdecysContingencia->registrarAvisoEnAdecsys($compra['id'],
                $compra['adecsys_code']);
        }
    }

    //Genera el reproceso de avisos a adecsys
    public function enviarAvisoAdecsys()
    {

        //Tablas
        $compra                 = new Application_Model_Compra;
        $empresaEnte            = new Application_Model_EmpresaEnte;
        $compraAdecsysCodigo    = new Application_Model_CompraAdecsysCodigo;
        $adecsysReprocesoModelo = new Application_Model_AdecsysReproceso;

        //Obtiene todas las compras que no tienen registro en la la tabla compra_adecsys_codigo
        $compraSinAdecsys = $compra->obtenerComprasSinAdecsys();
        // $compraAdecsysCodigo->generaRegistrosFaltantes($compraSinAdecsys);
//var_dump($adecsysReprocesoModelo->validaGeneraEnvioAdecsys(1622798, 400570));exit;
        //Obtiene todos los registros que no se han generado el código de adecsys
        $dataCompra       = $compra->obtenerRegistroFaltantesCompraAdecsysCodigo();
        $reprocesoAdecsys = new App_Controller_Action_Helper_AdecsysReproceso();
        // var_dump($reprocesoAdecsys->registrarAvisoEnAdecsys(694880, 75824, 'aptitus'));
        $cant             = 0;
        //      var_dump($reprocesoAdecsys->registrarAvisoEnAdecsys(627186, 55795, 'aptitus'));exit;
//var_dump($compra->getDetalleCompraAnuncioReproceso(1652572));
//var_dump($empresaEnte->getRegistroEnte(118567));



        foreach ($dataCompra as $registro) {
            $idCompra    = $registro['id_compra'];
            $idCac       = $registro['id_cac'];
            $medPub      = $registro['mediopub'];
            $adecsysEnte = $registro['adecsys_ente_id'];
            $idEmpresa   = $registro['id_empresa'];

            //Si no tiene adecsys_ente_id se actualiza
            //Update reproceso adecsys_ente_id is null
            if (is_null($adecsysEnte)) {
                $enteId = $empresaEnte->getRegistroEnte($idEmpresa);
                if (!is_null($enteId)) {
                    $where = $compra->getAdapter()->quoteInto('id = ?',
                        $idCompra);
                    $compra->update(array('adecsys_ente_id' => $enteId), $where);
                }
            }

            $validaEstadoAdecsys = $adecsysReprocesoModelo->validaGeneraEnvioAdecsys($idCompra,
                $idCac);

            //var_dump($validaEstadoAdecsys );
            if ($validaEstadoAdecsys) {
                $reprocesoAdecsys->registrarAvisoEnAdecsys($idCompra, $idCac,
                    $medPub);
                $cant++;
            }
        }

        echo PHP_EOL."Se procesaron  ".$cant." registros (Adecsys)";
    }

    //Genera el reproceso de avisos a adecsys
    public function enviarAvisoScot()
    {

        //Obtener los avisos preferenciales sin el medioPub talan_combo
        //Validar que primero exista Cod Adecsys
        $compra                 = new Application_Model_Compra;
        $adecsysReprocesoModelo = new Application_Model_AdecsysReproceso;
        $reprocesoAdecsys       = new App_Controller_Action_Helper_AdecsysReproceso();
        $dataScot               = $compra->obtenerRegistroFaltantesCompraScot();
        $cant                   = 0;

        foreach ($dataScot as $registro) {
            $idCompra = $registro['id_compra'];
            $idCac    = $registro['id_cac'];
            $medPub   = $registro['medPub'];
            $adecsys  = $registro['adecsys_code'];

            $validaEstadoScot = $adecsysReprocesoModelo->validaGeneraEnvioScot($idCompra,
                $idCac);
            if ($validaEstadoScot) {
                $reprocesoAdecsys->registrarAvisoEnScot($idCompra, $idCac,
                    $medPub, $adecsys);
                $cant++;
            }
        }

        echo PHP_EOL."Se procesaron  ".$cant." registros (SCOT)";
    }

    //Genera el reproceso de perfil destacado a adecsys
    public function enviarPerfilAdecsys($home = 10)
    {

        //Tablas
        $compra                 = new Application_Model_Compra;
        $postulanteEnte         = new Application_Model_PostulanteEnte;
        $adecsysReprocesoModelo = new Application_Model_AdecsysReproceso;

        //Obtiene todos los registros que no se han generado el código de adecsys
        $dataCompra       = $compra->obtenerRegistroFaltantesPerfilCompraAdecsysCodigo($home);
        $reprocesoAdecsys = new App_Controller_Action_Helper_AdecsysReproceso();

        $cant = 0;

        foreach ($dataCompra as $registro) {
            $idCompra     = $registro['id_compra'];
            $idCac        = $registro['id_cac'];
            $idPostulante = $registro['id_postulante'];
            $medPub       = 'aptitus';
            $adecsysEnte  = $registro['adecsys_ente_id'];

            //Si no tiene adecsys_ente_id se actualiza
            //Update reproceso adecsys_ente_id is null
            if (is_null($adecsysEnte)) {
                $enteId = $postulanteEnte->getRegistroEnte($idPostulante);
                if (!is_null($enteId)) {
                    $where = $compra->getAdapter()->quoteInto('id = ?',
                        $idCompra);
                    $compra->update(array('adecsys_ente_id' => $enteId), $where);
                }
            }

            $validaEstadoAdecsys = $adecsysReprocesoModelo->validaGeneraEnvioAdecsys($idCompra,
                $idCac);
            if ($validaEstadoAdecsys) {
                $reprocesoAdecsys->registrarPerfilEnAdecsys($idCompra, $idCac,
                    $medPub);
                $cant++;
            }
        }

        echo PHP_EOL."Se procesaron  ".$cant." registros (Perfil Adecsys)";
    }

    //Genera el reproceso de membresía web a adecsys
    public function enviarMembresiaAdecsys()
    {

        //Tablas
        $compra                 = new Application_Model_Compra;
        $empresaEnte            = new Application_Model_EmpresaEnte;
        $compraAdecsysCodigo    = new Application_Model_CompraAdecsysCodigo;
        $adecsysReprocesoModelo = new Application_Model_AdecsysReproceso;

        //Obtiene todos los registros que no se han generado el código de adecsys
        $dataCompra       = $compra->obtenerRegistroFaltantesMembresiaCompraAdecsysCodigo();
        $reprocesoAdecsys = new App_Controller_Action_Helper_AdecsysReproceso();


        $cant = 0;

        foreach ($dataCompra as $registro) {
            $idCompra    = $registro['id_compra'];
            $idCac       = $registro['id_cac'];
            $idEmpresa   = $registro['id_empresa'];
            $adecsysEnte = $registro['adecsys_ente_id'];

            //Si no tiene adecsys_ente_id se actualiza
            //Update reproceso adecsys_ente_id is null
            if (is_null($adecsysEnte)) {
                $enteId = $empresaEnte->getRegistroEnte($idEmpresa);
                if (!is_null($enteId)) {
                    $where = $compra->getAdapter()->quoteInto('id = ?',
                        $idCompra);
                    $compra->update(array('adecsys_ente_id' => $enteId), $where);
                }
            }

            $validaEstadoAdecsys = $adecsysReprocesoModelo->validaGeneraEnvioAdecsys($idCompra,
                $idCac);
            if ($validaEstadoAdecsys) {
                $reprocesoAdecsys->registrarMembresiaEnAdecsys($idCompra, $idCac);
                $cant++;
            }
        }

        echo PHP_EOL."Se procesaron  ".$cant." registros (Membresías web Adecsys)";
    }

    public function envioReprocesoAdecsysScot()
    {

        echo "Inicio: ".date('H:i:s').PHP_EOL;
        $this->enviarAvisoAdecsys();
        $this->enviarAvisoScot();

        //Perfil Destacado
        $this->enviarPerfilAdecsys();
        //Membresía web
        $this->enviarMembresiaAdecsys();

        echo PHP_EOL."Fin: ".date('H:i:s').PHP_EOL;
    }

    public function indexacion_solar()
    {
        $postulanteNuevo = new Application_Model_Postulante();
        $SolrNuevo       = $postulanteNuevo->getPostulanteNuevoSolar();
        $solr            = new App_Controller_Action_Helper_Solr();
        //   var_dump($SolrNuevo);exit;

        foreach ($SolrNuevo as $value) {

            //  echo "Se indexo el postulante : " . $value['idPos'];exit;


            if ($value['publico'] == 0) {
                $solr->addSolr($value['idPos']);
                echo "Se indexo el postulante en el Sor : ".$value['idPos'].PHP_EOL;
            } else {
                $solr->deleteSolar($value['idPos']);
                echo "Se elimino el postulante del Solr : ".$value['idPos'].PHP_EOL;
            }
        }
    }

    public function enviarCorreoUPC()
    {
        $mailer = new App_Controller_Action_Helper_Mail();

        $modUsuarioTCN = new Application_Model_UsuarioUpc();
        $oFecha        = new DateTime(date('Y-m-d'));
        $oFecha->sub(new DateInterval('P1D'));
        $yesterday     = $oFecha->format('Y-m-d');
        $FechaAyer     = $oFecha->format('d/m/Y');

        $dataUsuarios = $modUsuarioTCN->getUsuariosTCN($yesterday);

        $config = Zend_Registry::get('config');

        if (count($dataUsuarios) > 0) {

            $headers = array(
                'nombres', 'ape_pat', 'ape_mat', 'nacionalidad', 'tipo_doc',
                'numero_doc', 'sexo', 'fh_nacimiento', 'celular', 'email',
                'nivel_academico', 'ocupacion', 'recibir_info', 'fh_registro',
                'area_interes'
            );

            App_Service_Excel::getInstance()->setHeaders($headers);
            App_Service_Excel::getInstance()->appendList(array_values($dataUsuarios));
            App_Service_Excel::getInstance()->setLogo(
                APPLICATION_PATH.'/data/images/logos/logo-aptitus-excel.jpg'
            );

            App_Service_Excel::getInstance()->setData(array(
                'puesto' => 'Listado de usuario registrados',
                'fcreacion' => date('d/m/Y'),
            ));

            $objWriter = PHPExcel_IOFactory::createWriter(App_Service_Excel::getInstance()->getObjectExcel(),
                    'Excel5');

            $filename = APPLICATION_PATH.'/../cache/archivo-'.date('d-m-Y').'.xls';

            $objWriter->save($filename);

            try {

                $addBcc = $config->tcn->upc->bccNotificacionReporte->toArray();

                $dataMail = array(
                    'nombres' => $config->tcn->upc->nombresNotificacionReporte,
                    'to' => $config->tcn->upc->emailNotificacionReporte,
                    'addBcc' => $addBcc[0],
                    'fecha' => $FechaAyer,
                    'adjuntoFile' => $filename
                );

                $mailer->notificacionUpcReporte($dataMail);
                echo 'Reporte de Usuarios del '.$yesterday.' enviados.';
            } catch (Exception $ex) {
                echo 'Error: '.$ex->getMessage();
            }

            @unlink($filename);
        } else {
            echo 'No existen usuario para envio del reporte del '.$yesterday;
        }
    }

    public function notificacionSemanal()
    {
        $config          = Zend_Registry::get('config');
        $postulanteModel = new Application_Model_Postulante();
        $sendEmail       = new App_Controller_Action_Helper_Mail();
        $modePorcentaje  = new App_Controller_Action_Helper_PorcentajeCV();
        /* $_experiencia = new Application_Model_Experiencia();
          $_estudios = new Application_Model_Estudio();
          $_idiomas = new Application_Model_DominioIdioma(array('cron' => 1));
          $_programas = new Application_Model_DominioProgramaComputo(array('cron' => 1)); */
        $ini             = date('Y-m-d',
            strtotime('-2 Monday', strtotime('tomorrow'))); //two Mondays ago
        $fin             = date('Y-m-d',
            strtotime('-1 Monday', strtotime('tomorrow'))); //last Monday
        $select          = $postulanteModel->getPostulantesDestacadosConVisitas($ini,
            $fin);
        // $select=array(0=>1248405);
        foreach ($select as $idPost) {

            $row = array('id_postulante' => $idPost,
                'fh_inicio' => $ini,
                'fh_fin' => $fin
            );

            $mail                  = $postulanteModel->getNotificacionSemanalPorIdPostulante($idPost,
                $ini, $fin);
            $mail['visitas']       = $postulanteModel->getNotificacionSemanalVisitasPorIdPostulante($row);
            $mail['busquedas']     = $postulanteModel->getNotificacionSemanalBusquedasPorIdPostulante($row);
            $mail['postulaciones'] = $postulanteModel->getNotificacionSemanalPostulacionesPorIdPostulante($row);
            $mail['invitaciones']  = $postulanteModel->getNotificacionSemanalInvitacionesPorIdPostulante($row);
            $mail['leidas']        = $postulanteModel->getNotificacionSemanalLeidasPorIdPostulante($row);
            $mail['mensajes']      = $postulanteModel->getNotificacionSemanalMensajesPorIdPostulante($row);

            $ini_dia         = date('d', strtotime($ini));
            $ini_mes         = date('m', strtotime($ini));
            $fin_dia         = date('d', strtotime($fin));
            $fin_mes         = date('m', strtotime($fin));
            $aMes            = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo',
                'Junio', 'Julio'
                , 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
            if ($ini_mes == $fin_mes) $texto           = '';
            else $texto           = "de {$aMes[$ini_mes - 1]}";
            $mail['periodo'] = "Del $ini_dia $texto al $fin_dia de {$aMes[$fin_mes
                - 1]}";

            $porcentaje = 0;


            $arraypost = $postulanteModel->getPostulanteForPorcentaje($idPost);

            $porcentaje = $modePorcentaje->getPorcentajes($arraypost, true, true);
            // var_dump($porcentaje);exit;

            /*    $nex = count($_experiencia->getExperiencias($idPost));
              $nes = count($_estudios->getEstudios($idPost));
              $nid = count($_idiomas->getDominioIdioma($idPost));
              $npc = count($_programas->getDominioProgramaComputo($idPost));
              $_foto = $mail["path_foto"];
              $_presentacion = $mail["presentacion"];
              $_tuweb = $mail["website"];
              $_pathcv = $mail["path_cv"];
              $incompletos = array();
              $indice = 0;

              if ($_pathcv != "") {
              $porcentaje+=$config->dashboard->peso->subircv;
              } else {
              if ($indice < $config->dashboard->nimportantes) {
              $incompletos[$indice] = $config->dashboard->sug->subircv;
              $indice++;
              }
              }
              if ($nex > 0) {
              $porcentaje+=$config->dashboard->peso->experiencia;
              } else {
              if ($indice < $config->dashboard->nimportantes) {
              $incompletos[$indice] = $config->dashboard->sug->experiencia;
              $indice++;
              }
              }
              if ($nes > 0) {
              $porcentaje+=$config->dashboard->peso->estudios;
              } else {
              if ($indice < $config->dashboard->nimportantes) {
              $incompletos[$indice] = $config->dashboard->sug->estudios;
              $indice++;
              }
              }
              if ($nid > 0) {
              $porcentaje+=$config->dashboard->peso->idiomas;
              } else {
              if ($indice < $config->dashboard->nimportantes) {
              $incompletos[$indice] = $config->dashboard->sug->idiomas;
              $indice++;
              }
              }
              if ($npc > 0) {
              $porcentaje+=$config->dashboard->peso->programas;
              } else {
              if ($indice < $config->dashboard->nimportantes) {
              $incompletos[$indice] = $config->dashboard->sug->programas;
              $indice++;
              }
              }
              if ($_presentacion != "") {
              $porcentaje+=$config->dashboard->peso->presentacion;
              } else {
              if ($indice < $config->dashboard->nimportantes) {
              $incompletos[$indice] = $config->dashboard->sug->presentacion;
              $indice++;
              }
              }
              if ($_tuweb != "") {
              $porcentaje+=$config->dashboard->peso->tuweb;
              } else {
              if ($indice < $config->dashboard->nimportantes) {
              $incompletos[$indice] = $config->dashboard->sug->tuweb;
              $indice++;
              }
              }
              if ($_foto != "photoDefault.jpg" && $_foto != "") {
              $porcentaje+=$config->dashboard->peso->foto;
              } else {
              if ($indice < $config->dashboard->nimportantes) {
              $incompletos[$indice] = $config->dashboard->sug->foto;
              $indice++;
              }
              }
             */
            $mail['porcentaje']  = $porcentaje;
            $mail['incompletos'] = $porcentaje['total_incompleto'];

            //  var_dump()
            $mail['var']    = $config->dashboard;
            $mail['addBcc'] = $config->emailing->bccNotificacionSemanal;
            try {
                //$data = $sendEmail->notificacionSemanal($mail);
                //file_put_contents("$idPost.html", $data);
                $sendEmail->notificacionSemanal($mail);
                echo 'Postulante con id '.$idPost.' ha sido notificado'.PHP_EOL;
                // exit;
            } catch (Exception $e) {
                echo PHP_EOL.'OCURRIO UN ERROR: '.
                $e->getMessage().PHP_EOL;
                return;
            }
        }
    }

    public function notificacionFinal()
    {

        $postulanteModel = new Application_Model_Postulante();
        $perfilDestacado = new Application_Model_PerfilDestacado();
        $sendEmail       = new App_Controller_Action_Helper_Mail();
        $select          = $postulanteModel->getPerfilDestacadoPorVencer();

        foreach ($select as $row) {
            $mail                  = $postulanteModel->getNotificacionFinalPorIdPostulante($row);
            $mail['visitas']       = $postulanteModel->getNotificacionFinalPorIdPostulanteVisitas($row);
            $mail['busquedas']     = $postulanteModel->getNotificacionFinalPorIdPostulanteBusquedas($row);
            $mail['postulaciones'] = $postulanteModel->getNotificacionFinalPorIdPostulantePostulaciones($row);
            $mail['invitaciones']  = $postulanteModel->getNotificacionFinalPorIdPostulanteInvitaciones($row);
            $mail['procesos']      = $postulanteModel->getNotificacionFinalPorIdPostulanteProcesos($row);
            $mail['mensajes']      = $postulanteModel->getNotificacionFinalPorIdPostulanteMensajes($row);
            $mail['responder']     = $postulanteModel->getNotificacionFinalPorIdPostulanteResponder($row);
            $dataPD                = $perfilDestacado->obtenerRegPerfilDestacadoActivo($row['id_postulante']);

            $dataExp = array(
                'to' => $dataPD['email'],
                'nombre' => $dataPD['nombres'],
                'inicio' => $dataPD['inicio'],
                'fin' => $dataPD['fin']
            );

            $mail['periodo'] = date('d/m/Y', strtotime($row['fh_inicio'])).' - '.date('d/m/Y',
                    strtotime($row['fh_fin']));
            $mail['addBcc']  = $config->emailing->bccNotificacionFinal;
            try {
                $sendEmail->notificacionFinal($mail);
                //Notificación expiración de Perfil Destacado
                $sendEmail->expiraPerfilDestacado($dataExp);

                echo 'Postulante con id '.$row['id_postulante'].' ha sido notificado'.PHP_EOL;
            } catch (Exception $e) {
                echo PHP_EOL.'Ocurrió un error: '.
                $e->getMessage().PHP_EOL;
                return;
            }
        }
    }

    public function caducarPerfilDestacado()
    {
        $postulanteModel = new Application_Model_Postulante();
        $perfilModel     = new Application_Model_PerfilDestacado();
        $select          = $postulanteModel->getPerfilDestacadoVenceHoy();
        $db              = $perfilModel->getAdapter();
        $config          = Zend_Registry::get('config');
        $sc              = new Solarium\Client($config->solr);
        $moPostulante    = new Solr_SolrAbstract($sc, 'postulante');
        foreach ($select as $row) {
            try {
                $perfil['activo'] = Application_Model_PerfilDestacado::VENCIDO;
                $perfil['estado'] = Application_Model_PerfilDestacado::ESTADO_VENCIDO;
                $where            = $db->quoteInto("id = ?", $row['id']);
                $perfilModel->update($perfil, $where);
                echo 'Perfil destacado con id '.$row['id'].' ha sido caducado'.PHP_EOL;
                $idPD             = $postulanteModel->getProximoPerfilDestacado($row['id_postulante']);
                if (empty($idPD)) {
                    $postulante['destacado'] = Application_Model_Postulante::NO_DESTACADO;
                    $whereP                  = $db->quoteInto("id = ?",
                        $row['id_postulante']);
                    $res                     = $postulanteModel->update($postulante,
                        $whereP);
                    if (!empty($res))
                            $moPostulante->addPostulante($row['id_postulante']);
                    echo 'Postulante con id '.$row['id_postulante'].' ha caducado su perfil destacado'.PHP_EOL;
                }
                else {
                    $perfilA['activo'] = Application_Model_PerfilDestacado::ACTIVO;
                    $whereA            = $db->quoteInto("id = ?", $idPD);
                    $perfilModel->update($perfilA, $whereA);
                    echo 'Perfil destacado con id '.$idPD.' ha sido activado'.PHP_EOL;
                }
            } catch (Exception $e) {
                echo PHP_EOL.'OCURRIO UN ERROR: '.
                $e->getMessage().PHP_EOL;
                return;
            }
        }
    }
    /*
     * Obtiene las fechas de vencimiento tanto del Proceso 
     * como de la publicacion del aviso.      
     * 
     * @param int $idAnuncio    ID del Anuncio
     * @access private     
     * @return array
     */

    private function getFechaVencimientosAnuncioByIdAnuncio($idAnuncio)
    {
        $diasAnuncio   = $diasProceso   = '';
        $fecVenAnuncio = new DateTime(date("Y-m-d"));
        $fecVenProceso = new DateTime(date("Y-m-d"));

        $db     = new App_Db_Table_Abstract();
        $config = Zend_Registry::get('config');
        $sql    = $db->getAdapter()
            ->select()
            ->from(array('awd' => 'anuncio_web_detalle'),
                array('codigo', 'descripcion', 'valor', 'precio'))
            ->where('awd.id_anuncio_web = ?', $idAnuncio);

        $rwBeneficios = $db->getAdapter()->fetchAll($sql);


        $ndiaspub = Application_Model_Beneficio::CODE_NDIASPUB;
        if (array_key_exists($ndiaspub, $rwBeneficios)) {
            $diasAnuncio = !empty($rwBeneficios[$ndiaspub]['valor']) ?
                $rwBeneficios[$ndiaspub]['valor'] : 5;
        } else {
            $diasAnuncio = !empty($config->anuncioperiodo->ndiaspub->valor) ?
                $config->anuncioperiodo->ndiaspub->valor : 5;
        }
        $fecVenAnuncio->add(new DateInterval('P'.$diasAnuncio.'D'));



        $ndiasproc = Application_Model_Beneficio::CODE_NDIASPROC;
        if (array_key_exists($ndiasproc, $rwBeneficios)) {
            $diasProceso = !empty($rwBeneficios[$ndiasproc]['valor']) ?
                $rwBeneficios[$ndiasproc]['valor'] : 30;
        } else {
            $diasProceso = !empty($config->anuncioperiodo->ndiasproc->valor) ?
                $config->anuncioperiodo->ndiasproc->valor : 30;
        }
        $fecVenProceso->add(new DateInterval('P'.$diasProceso.'D'));


        return array(
            'fh_vencimiento' => $fecVenAnuncio->format('Y-m-d'),
            'fh_vencimiento_proceso' => $fecVenProceso->format('Y-m-d'),
        );
    }
    /*
     * Corrije los avisos publicados que tienen el estado pendiente de pago y 
     * en la compra con el estado de pagado. El aviso pertenece a empresa con 
     * membresia.     
     *      
     * @access public     
     * @return void
     */

    public function fixAvisosGratuitosMembresia()
    {
        $modelAviso = new Application_Model_AnuncioWeb();
        $avisos     = $modelAviso->getAvisosGratuitosPendientePago();


        echo 'Buscando Avisos Gratuitos con pendiente de pago: '."\n";
        foreach ($avisos as $aviso) {

            $dataPrioridad  = $modelAviso->prioridadAviso($aviso['tipoAnuncio'],
                $aviso['id_empresa']);
            $prioridad      = $dataPrioridad['prioridad'];
            $ndiasPrioridad = $dataPrioridad['dias'];


            $diaActual         = date('Y-m-d H:i:s');
            $fechaVenPrioridad = date('Y-m-d H:i:s',
                strtotime('+'.$ndiasPrioridad.' day', strtotime($diaActual)));

            $fechasVencimiento = $this->getFechaVencimientosAnuncioByIdAnuncio($aviso['id']);


            $corregido = $modelAviso->update(array(
                'estado' => 'pagado',
                'buscamas' => 0,
                'fh_pub' => date('Y-m-d H:i:s'),
                'prioridad' => $prioridad,
                'prioridad_ndias_busqueda' => $ndiasPrioridad,
                'fh_vencimiento' => $fechasVencimiento['fh_vencimiento'],
                'fh_vencimiento_proceso' => $fechasVencimiento['fh_vencimiento_proceso'],
                'fh_vencimiento_prioridad' => $fechaVenPrioridad,
                ), 'id = '.$aviso['id']);


            if ($corregido) {
                echo 'Aviso '.$aviso['id'].' actualizado.'."\n";
            }
        }
    }

    public function verificarEntesAdecsys($file = null)
    {

        if (null === $file) {
            exit;
        }

        $ws = new Zend_Soap_Client('http://wsadecsys.ec.pe/IntegracionAptitus.asmx?wsdl');

        if (($handle = fopen($file, 'r')) !== false) {
            $output     = '';
            $fileOutput = str_replace('.csv', '.txt.csv', $file);
            $header     = fgetcsv($handle, 9999, "\t");
            while (($data       = fgetcsv($handle, 9999, "\t")) !== false) {

                $enteCodSupuesto = $data[1];

                $response = $ws->Validar_Cliente(array(
                    'Tipo_Documento' => $data[2],
                    'Numero_Documento' => $data[3]
                ));


                if (isset($response->Validar_ClienteResult) && isset($response->Validar_ClienteResult->Id)) {
                    if ($enteCodSupuesto == $response->Validar_ClienteResult->Id)
                            ;
                    else {
                        //echo 'ID '.$data[0].' es INCORRECTO ('.$data[2].': '.$data[3].').'.PHP_EOL;
                        $output .= $data[1]."\t".$data[2]."\t".$data[3].PHP_EOL;
                    }
                } else {
                    //echo 'ID '.$data[0].' No se encuentra en Adecsys ('.$data[2].': '.$data[3].'). '.PHP_EOL;
                    $output .= $data[1]."\t".$data[2]."\t".$data[3]."\t".'*'.PHP_EOL;
                }


                sleep(2);

                unset($data);
            }
            fclose($handle);

            file_put_contents($fileOutput, $output);
        }
    }

    public function exeXmlMitula()
    {
        echo "START:cron_mitula".PHP_EOL;

        // Consulta SQL para jalar los avisos
        echo "Consultando la BD ...".PHP_EOL;
        $sqlQuery = "
        SELECT a.Id,
        CONCAT('http://aptitus.com/ofertas-de-trabajo/',`a`.`slug`,'-',`a`.`url_id`) AS `url`,
        a.puesto AS title,
        CONCAT(`a`.`funciones`,' ',`a`.`responsabilidades`) AS `content`,
        u.nombre AS city_area,
        p.nombre AS city,
        d.nombre AS region,
        IF(a.mostrar_salario = '0',NULL,CONCAT(REPLACE(REPLACE(REPLACE(FORMAT(a.salario_min,0), ',', ':'), '.', ','), ':', '.'),'-',REPLACE(REPLACE(REPLACE(FORMAT(IF(a.salario_min='10001','15000',a.salario_max),0), ',', ':'), '.', ','), ':', '.'))) AS salary,
        TRIM(REPLACE(`a`.`empresa_rs`,'.','')) AS `company`,
        a.fh_pub AS DATE,
        a.fh_vencimiento AS expiration_date,
        GROUP_CONCAT(DISTINCT CONCAT_WS(' ',ne.nombre,net.nombre,IF(ca.nombre!='','en',''),ca.nombre)) AS studies,
        GROUP_CONCAT(DISTINCT CONCAT_WS(' ',IF(ar.nombre!='','en',''),ar.nombre,IF(e.experiencia!='0',CONCAT_WS(' ','con',IF(e.experiencia>12,CONCAT(FORMAT(e.experiencia/12,0),' años'),IF(e.experiencia=12,'1 año',CONCAT(e.experiencia,' meses'))),'de experiencia'),''))) AS experience
        FROM `anuncio_web` a
        INNER JOIN ubigeo u ON a.id_ubigeo = u.Id
        INNER JOIN ubigeo p ON u.padre = p.Id
        INNER JOIN ubigeo d ON p.padre = d.Id
        LEFT JOIN anuncio_estudio ae ON ae.id_anuncio_web = a.Id AND ae.id_nivel_estudio > 1
        LEFT JOIN nivel_estudio ne ON ae.`id_nivel_estudio` = ne.Id
        LEFT JOIN nivel_estudio net ON ae.`id_nivel_estudio_tipo` = net.Id
        LEFT JOIN carrera ca ON ae.id_carrera = ca.Id
        LEFT JOIN anuncio_experiencia e ON e.id_anuncio_web = a.Id 
        LEFT JOIN area ar ON e.id_area = ar.Id
        WHERE a.online = 1 AND a.`cerrado` = 0 AND a.`estado` = 'pagado'
        GROUP BY a.Id";

        $sqlCant = "SELECT count(sel.Id) AS cant FROM ($sqlQuery) sel";

        $db    = new App_Db_Table_Abstract();
        /* $config = Zend_Registry::get('config');
          $dbParams = $config->resources->db->params;

          echo $n."Parametros de conexion";
          echo $n."---------------------------------------------------------";
          echo $n."host    : ".$dbParams->host.
          $n."username: ".$dbParams->username.
          $n."password: ".$dbParams->password.
          $n."dbname  : ".$dbParams->dbname.$n."$n"; */
        /*
          $cn = mysqli_connect($dbParams->host,$dbParams->username,$dbParams->password,$dbParams->dbname,3307);
          mysqli_set_charset($cn, 'utf8'); // evita error - json_encode():Invalid UTF-8 sequence in argument
          $rCant = mysqli_query($cn,"SELECT count(sel.Id) AS cant FROM ($sqlQuery) sel");
          $aCant = mysqli_fetch_row($rCant);
          $cant = (int)$aCant[0]; */
        $aCant = $db->getAdapter()->fetchRow($sqlCant); //var_dump($aCant); exit;
        $cant  = (int) $aCant['cant']; //var_dump($cant); exit;
        // //$rs = mysql_query($sqlQuery, $cn);
        // //$rs = mysql_unbuffered_query($sqlQuery, $cn);

        /* if(!mysqli_real_query($cn, $sqlQuery)){ echo mysqli_error($cn); exit; }
          $rs = mysqli_use_result($cn); */

        $rows = $db->getAdapter()->fetchAll($sqlQuery); //$cant = count($rows);

        $nom = 'mitula';

        $this->createXmlSeo($rows, $cant, $nom);

        echo "END:cron_mitula".PHP_EOL;
    }

    public function exeXmljobs()
    {
        echo "START:jobs".PHP_EOL;
        //  var_dump(SITE_URL);exit;
        // Consulta SQL para jalar los avisos
        echo "Consultando la BD ...".PHP_EOL;
        $sqlQuery = "
           SELECT
	 a.id AS Id,
        a.puesto AS title,
         CONCAT('".SITE_URL."/ofertas-de-trabajo/',`a`.`slug`,'-',`a`.`url_id`)  AS url,
        p.nombre AS location,
        TRIM(REPLACE(a.empresa_rs,'.','')) AS company,
        (e.url_tcn) AS company_url,
        CONCAT(`a`.`funciones`,' ',`a`.`responsabilidades`) AS `description`,       
        IF(a.mostrar_salario = '0',NULL,CONCAT(REPLACE(REPLACE(REPLACE(FORMAT(a.salario_min,0), ',', ':'), '.', ','), ':', '.'),'-',REPLACE(REPLACE(REPLACE(FORMAT(IF(a.salario_min='10001','15000',a.salario_max),0), ',', ':'), '.', ','), ':', '.'))) AS salary   ,     

         CONCAT('".SITE_URL."/ofertas-de-trabajo/',`a`.`slug`,'-',`a`.`url_id`) AS `apply_url`
        FROM `anuncio_web` a  
        INNER JOIN ubigeo u ON a.id_ubigeo = u.Id
        INNER JOIN ubigeo p ON u.padre = p.Id
        INNER JOIN ubigeo d ON p.padre = d.Id  
        LEFT JOIN empresa e ON e.id = a.id_empresa      
       	      
        WHERE a.online = 1 AND a.`cerrado` = 0 AND a.`estado` = 'pagado'
        GROUP BY a.id  
        ";

        $sqlCant = "SELECT count(sel.Id) AS cant FROM ($sqlQuery) sel";


        $db    = new App_Db_Table_Abstract();
        /* $config = Zend_Registry::get('config');
          $dbParams = $config->resources->db->params;

          echo $n."Parametros de conexion";
          echo $n."---------------------------------------------------------";
          echo $n."host    : ".$dbParams->host.
          $n."username: ".$dbParams->username.
          $n."password: ".$dbParams->password.
          $n."dbname  : ".$dbParams->dbname.$n."$n"; */
        /*
          $cn = mysqli_connect($dbParams->host,$dbParams->username,$dbParams->password,$dbParams->dbname,3307);
          mysqli_set_charset($cn, 'utf8'); // evita error - json_encode():Invalid UTF-8 sequence in argument
          $rCant = mysqli_query($cn,"SELECT count(sel.Id) AS cant FROM ($sqlQuery) sel");
          $aCant = mysqli_fetch_row($rCant);
          $cant = (int)$aCant[0]; */
        $aCant = $db->getAdapter()->fetchRow($sqlCant); //var_dump($aCant); exit;

        $cant = (int) $aCant['cant']; //var_dump($cant); exit;
        // //$rs = mysql_query($sqlQuery, $cn);
        // //$rs = mysql_unbuffered_query($sqlQuery, $cn);

        /* if(!mysqli_real_query($cn, $sqlQuery)){ echo mysqli_error($cn); exit; }
          $rs = mysqli_use_result($cn); */

        $rows = $db->getAdapter()->fetchAll($sqlQuery); //$cant = count($rows);

        $nom = 'jobs';

        $this->createXmlSeoJobs($rows, $cant, $nom);

        echo "END:cron_mitula".PHP_EOL;
    }

    public function exeXmlTrovit()
    {
        echo "START: Cron Xml Trovit".PHP_EOL;

        // Consulta SQL para jalar los avisos
        echo "-> Consultando la BD ...".PHP_EOL;
        $time = explode(" ", microtime());
        $time = $time[1];

        $sqlQuery = "
        SELECT 
        a.Id AS id,
        CONCAT('http://aptitus.com/ofertas-de-trabajo/',`a`.`slug`,'-',`a`.`url_id`) AS `url`,
        LOWER(TRIM(a.puesto)) AS title,
        LOWER(CONCAT(a.funciones,' ',a.responsabilidades)) AS `content`,
        LOWER(REPLACE(TRIM(a.empresa_rs),'.','')) AS `company`,
        GROUP_CONCAT(DISTINCT CONCAT_WS(' ',IF(ar.nombre!='','en',''),ar.nombre,IF(e.experiencia!='0',CONCAT_WS(' ','con',IF(e.experiencia>12,CONCAT(FORMAT(e.experiencia/12,0),' años'),IF(e.experiencia=12,'1 año',CONCAT(e.experiencia,' meses'))),'de experiencia'),''))) AS experience,
        GROUP_CONCAT(DISTINCT CONCAT_WS(' ',ne.nombre,net.nombre,IF(ca.nombre!='','en',''),ca.nombre)) AS studies,
        ar.nombre AS category,
        IF(a.mostrar_salario = '0',NULL,CONCAT(REPLACE(REPLACE(REPLACE(FORMAT(a.salario_min,0), ',', ':'), '.', ','), ':', '.'),'-',REPLACE(REPLACE(REPLACE(FORMAT(IF(a.salario_min='10001','15000',a.salario_max),0), ',', ':'), '.', ','), ':', '.'))) AS salary,
        u.nombre AS city_area,
        p.nombre AS city,
        d.nombre AS region,
        ('".date('Y-m-d H:i:s')."') AS date,
        a.fh_vencimiento AS expiration_date
        FROM anuncio_web a
        INNER JOIN ubigeo u ON a.id_ubigeo = u.Id 
            AND a.online = 1 AND a.cerrado = 0 AND a.estado = 'pagado' 
            AND CHARACTER_LENGTH(TRIM(a.puesto)) >= 3 
            AND CHARACTER_LENGTH(TRIM(a.funciones)) >= 31 
        INNER JOIN ubigeo p ON u.padre = p.Id
        INNER JOIN ubigeo d ON p.padre = d.Id
        LEFT JOIN anuncio_estudio ae ON ae.id_anuncio_web = a.Id 
            AND ae.id_nivel_estudio > 1
        LEFT JOIN nivel_estudio ne ON ae.id_nivel_estudio = ne.Id
        LEFT JOIN nivel_estudio net ON ae.id_nivel_estudio_tipo = net.Id
        LEFT JOIN carrera ca ON ae.id_carrera = ca.Id
        LEFT JOIN anuncio_experiencia e ON e.id_anuncio_web = a.Id 
        LEFT JOIN area ar ON e.id_area = ar.Id
        GROUP BY a.Id";
        // Consulta SQL para jalar la cantidad total de avisos
        $sqlCant  = "SELECT count(sel.Id) AS cant FROM ($sqlQuery) sel";

        $db = new App_Db_Table_Abstract();

        //Obtenemos todos los registros
        $rows = $db->getAdapter()->fetchAll($sqlQuery);

        //Cantidad Total de registros
        $aCant = $db->getAdapter()->fetchRow($sqlCant);
        $cant  = (int) $aCant['cant'];

        echo "-> Uso de memoria: ".number_format(memory_get_peak_usage() / (1024
            * 1024), 2)."MB".PHP_EOL;
        $time2 = explode(" ", microtime());
        $time2 = $time2[1];
        echo "-> Tiempo de ejecucion: ".number_format($time2 - $time)."s".PHP_EOL;
        echo "-> Fin de la Consulta BD ...".PHP_EOL;

        $nom = 'trovit';
        $this->createXmlSeo($rows, $cant, $nom);

        echo "END: Cron Xml Trovit".PHP_EOL;
    }

    public function createXmlSeo($rows, $cant, $nom)
    {
        define('DS', DIRECTORY_SEPARATOR);
        define('_SRC_', dirname(dirname(dirname(__FILE__))));
        define('_PUBLIC_', _SRC_.DS.'public');
        define('_SEO_', _PUBLIC_.DS.'seo');
        // Crando directorio "seo" dentro de public/
        if (!file_exists(_SEO_)) {
            echo "-> Directorio seo no existe! ".PHP_EOL."Creando ...".PHP_EOL;
            if (!mkdir(_SEO_, 0775)) {
                echo "-> No se pudo crear directorio seo!!!".PHP_EOL;
                exit;
            }
            echo "-> Directorio seo creado!!!".PHP_EOL;
        }

        echo "-> Creando el archivo XML ...".PHP_EOL;

        $xmlDoc = new \DOMDocument('1.0', 'utf-8');

        // creando <trovit />
        $trovit = $xmlDoc->appendChild($xmlDoc->createElement($nom));

        $k = 1;

        foreach ($rows as $row) {

            $boleano = true;

            if ($row['title']) {
                // quitamos el doble espacio 
                $row['title'] = str_replace('  ', ' ', $row['title']);
                // Palabras no permitidas
                $find         = include __DIR__.'/cronTrovitPalabrasNoPermitidas.php';
                $row['title'] = str_replace($find, '', $row['title']);

                if (empty($row['title'])) {
                    $boleano = false;
                }
//                elseif (strlen($row['title']) < 5) {
//                    $boleano = false;
//                } 
            }

            // el titulo no deben exceder los 42 caracteres por palabra
//            if ($row['title']) { 
//                $array_cadena = explode(' ', $row['title']);
//                foreach ($array_cadena as $palabra) {
//                    if (strlen($palabra) > 42) {
//                        $boleano = false;
//                        break;
//                    }
//                }
//            }
            // el contenido no deben exceder los 42 caracteres por palabra
//            if ($row['content']) {
//                $array_cadena = explode(' ', $row['content']);
//                foreach ($array_cadena as $palabra) {
//                    if (strlen($palabra) > 42) {
//                        $boleano = false;
//                        break;
//                    }
//                }
//            }

            if ($boleano) {
                // Creando <ad /> y adicionando a <trovit />
                $ad = $trovit->appendChild($xmlDoc->createElement('ad'));
                // adicionando elementos dentro de <ad />
                foreach ($row as $i => $v) {
                    if (trim($v)) {
                        if ($i == 'content') {
                            // filtramos caracteres extraños
                            $v = preg_replace('/[^.,;-\sa-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ]+/u',
                                '', $v);
                            // quitamos el doble espacio 
                            $v = str_replace('  ', ' ', $v);
                        }

                        // Palabras no permitidas
                        $find = include __DIR__.'/cronTrovitPalabrasNoPermitidas.php';
                        $v    = str_replace($find, '---', $v);

                        if (!mb_check_encoding($v, 'UTF-8')) {
                            $v = mb_convert_encoding($v, 'UTF-8');
                        }

                        //Creamos el Elemento y su contenido
                        $ele = $xmlDoc->createElement($i);
                        $ele->appendChild($xmlDoc->createCDATASection($v));
                        $ad->appendChild($ele);
                    }
                }
            }

            if (PHP_SAPI == 'cli') {
                $this->show_status($k++, $cant);
            } //usleep(100000);
        }

        // Creando el archivo xml
        $xmlDoc->formatOutput = TRUE;

        $strings_xml = $xmlDoc->saveXML();

        try {
            if ($xmlDoc->save(_SEO_.DS.$nom.".xml") === FALSE) {
                echo "-> No se pudo guardar!".PHP_EOL;
            } else {
                echo "-> Se guardo archivo $nom.xml".PHP_EOL;
            }
        } catch (Exception $exc) {
            echo $exc->getMessage().PHP_EOL.$exc->getTraceAsString();
        }
    }

    public function createXmlSeoJobs($rows, $cant, $nom)
    {
        define('DS', DIRECTORY_SEPARATOR);
        define('_SRC_', dirname(dirname(dirname(__FILE__))));
        define('_PUBLIC_', _SRC_.DS.'public');
        define('_SEO_', _PUBLIC_.DS.'seo');
        $db = new App_Db_Table_Abstract();
        // Crando directorio "seo" dentro de public/
        if (!file_exists(_SEO_)) {
            echo "-> Directorio seo no existe! ".PHP_EOL."Creando ...".PHP_EOL;
            if (!mkdir(_SEO_, 0775)) {
                echo "-> No se pudo crear directorio seo!!!".PHP_EOL;
                exit;
            }
            echo "-> Directorio seo creado!!!".PHP_EOL;
        }

        echo "-> Creando el archivo XML ...".PHP_EOL;

        $xmlDoc = new \DOMDocument('1.0', 'utf-8');

        // creando <jobs />
        $trovit = $xmlDoc->appendChild($xmlDoc->createElement($nom));

        $k = 1;

        foreach ($rows as $row) {
            unset($row['Id']);
            $boleano = true;

            if ($row['title']) {
                // quitamos el doble espacio 
                $row['title'] = str_replace('  ', ' ', $row['title']);
                // Palabras no permitidas
                $find         = include __DIR__.'/cronTrovitPalabrasNoPermitidas.php';
                $row['title'] = str_replace($find, '', $row['title']);

                if (empty($row['title'])) {
                    $boleano = false;
                } elseif (strlen($row['title']) < 5) {
                    $boleano = false;
                }
            }

            if ($boleano) {
                // Creando <ad /> y adicionando a <trovit />
                $ad = $trovit->appendChild($xmlDoc->createElement('job'));
                // adicionando elementos dentro de <ad />
                foreach ($row as $i => $v) {
                    //  var_dump(!empty($v));
                    if (!empty($v)) {
                        $v        = trim($v);
                        $contacto = array();
                        /*  if( $i=='contact' ){
                          if( !empty($v)){
                          $data='';
                          $data=  explode( '#', $row['contact'])  ;
                          if(!empty($data[0])){
                          $contacto['name'] =$data[0];
                          }
                          if(!empty($data[1])){
                          $contacto['email']=$data[1];
                          }
                          $telefono=substr(($data[2]),0,4);
                          if($telefono!='000'){
                          $contacto['phone']=$data[2];
                          }
                          }

                          } */
                        if ($i == 'description') {
                            // filtramos caracteres extraños
                            $v = preg_replace('/[^.,;-\sa-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ]+/u',
                                '', $v);
                            // quitamos el doble espacio 
                            $v = str_replace('  ', ' ', $v);
                        }

                        // Palabras no permitidas
                        $find = include __DIR__.'/cronTrovitPalabrasNoPermitidas.php';
                        $v    = str_replace($find, '---', $v);

                        if (!mb_check_encoding($v, 'UTF-8')) {
                            $v = mb_convert_encoding($v, 'UTF-8');
                        }

                        /* if($contacto){
                          $v=$contacto;
                          } */
                        //Creamos el Elemento y su contenido   
                        $ele = $xmlDoc->createElement($i);
                        /*   if ($i =='contact' &&  is_array($v)) {
                          foreach ($v as $d => $vs) {
                          $eles= $xmlDoc->createElement($d);
                          $eles->appendChild($xmlDoc->createCDATASection($vs));
                          $ele->appendChild($eles);
                          }
                          }else {
                          $ele->appendChild($xmlDoc->createCDATASection($v));
                          } */
                        $ele->appendChild($xmlDoc->createCDATASection($v));
                        $ad->appendChild($ele);
                    }
                }
            }
            //  var_dump($k,$cant);exit;

            if (PHP_SAPI == 'cli') {
                $this->show_status($k++, $cant);
            } //usleep(100000);
        }

        // Creando el archivo xml
        $xmlDoc->formatOutput = TRUE;
        $strings_xml          = $xmlDoc->saveXML();

        try {
            if ($xmlDoc->save(_SEO_.DS.$nom.".xml") === FALSE) {
                echo "-> No se pudo guardar!".PHP_EOL;
            } else {
                echo "-> Se guardo archivo $nom.xml".PHP_EOL;
            }
        } catch (Exception $exc) {
            echo $exc->getMessage().PHP_EOL.$exc->getTraceAsString();
        }
    }

    private function show_status($done, $total, $size = 30)
    {
        static $start_time;

        // if we go over our bound, just ignore it
        if ($done > $total) return;

        if (empty($start_time)) $start_time = time();

        $now  = time();
        $perc = (double) ($done / $total);
        $bar  = floor($perc * $size);

        $status_bar = "\r[";
        $status_bar.=str_repeat("=", $bar);
        if ($bar < $size) {
            $status_bar.=">";
            $status_bar.=str_repeat(" ", $size - $bar);
        } else {
            $status_bar.="=";
        }

        $disp    = number_format($perc * 100, 0);
        $status_bar.="] $disp%  $done/$total ";
        $rate    = ($now - $start_time) / $done;
        $left    = $total - $done;
        $eta     = round($rate * $left, 2);
        $elapsed = $now - $start_time;

        $status_bar.= "remaining: ".number_format($eta)."sec  elapsed: ".number_format($elapsed)."sec";

        echo "$status_bar  ";

        flush();

        if ($done == $total) echo PHP_EOL;
    }

    public function indexacion_solar_aviso($option = null)
    {
        $aw             = new Application_Model_AnuncioWeb();
        $db             = new App_Db_Table_Abstract();
        $SolrNuevoAviso = $aw->getIndexarSolr();
        $AvisoSolr      = new Solr_SolrAviso();
        if (!empty($option)) {
            echo 'holas';
        }
        foreach ($SolrNuevoAviso as $value => $id) {
            if ($id['eliminado'] == 0 && $id['cerrado'] == 0 && $id['borrador'] == 0) {
                $return = $AvisoSolr->addAvisoSolr($id['source_id']);
                if (!$return) {
                    $aw->update(array('buscamas' => 1),
                        'id = '.$db->getAdapter()->quoteInto('id = (?)',
                            $id['source_id']));
                    echo "Se indexo el aviso en Sor : ".$id['source_id'].PHP_EOL;
                }
            } else {
                $AvisoSolr->DeleteAvisoSolr($id['source_id']);
                $aw->update(array('buscamas' => 0),
                    'id = '.$db->getAdapter()->quoteInto('id = (?)',
                        $id['source_id']));
                echo "Se eliminio el aviso en Sor : ".$id['source_id'].PHP_EOL;
            }
        }
    }

    public function cronDNIRepetidos($total)
    {
        set_time_limit(0);

        $db = new App_Db_Table_Abstract();
        $db = $db->getAdapter();

        if (is_numeric($total)) {
            $limit = " LIMIT $total";
        } else {
            $limit = '';
        }

        $sql = "SELECT
                  num_doc,
                  MAX( u.ultimo_login ) AS max,
                  GROUP_CONCAT(p.id SEPARATOR ',') AS ids,
                  GROUP_CONCAT(u.ultimo_login SEPARATOR  ',') as ultimos_login,
                  COUNT(p.id) AS repetidos
                FROM postulante p
                INNER JOIN usuario u ON ( u.id = p.id_usuario )
                WHERE LENGTH(p.num_doc)=8 AND p.num_doc NOT IN (
                  '', '00000000', '000000000',
                  '000000', '0000000000', '000000000000000', '000000001', '00000001', '00000003',
                  '00000004', '00000010', '00000011', '00000020', '00000078', '00001'
                )
                GROUP BY p.num_doc
                HAVING repetidos > 1".$limit;



        $res = $db->fetchAll($sql);

        foreach ($res as $item) {

            $ids      = explode(',', $item['ids']);
            $ann      = explode(',', $item['ultimos_login']);
            $excluido = array_search($item['max'], $ann);

            if (isset($ids[$excluido])) {
                unset($ids[$excluido]);
            }

            $sql = "UPDATE postulante SET num_doc=0 WHERE id IN (".implode(',',
                    $ids).")";
            $db->query($sql);

            echo "El CI <b>{$item['num_doc']}</b>  se elimino de los siguientes postulantes : "
            .implode(' , ', $ids)
            .'<br />';
        }
    }

    public function cronRUCRepetidos($ruc)
    {
        set_time_limit(0);
        $db  = new App_Db_Table_Abstract();
        $db  = $db->getAdapter();
        $app = new App_Validate_Ruc();
        if (strlen(trim($ruc)) == 11) {
            // verificar que exista
            $empModel = new Application_Model_Empresa();
            $p        = $empModel->getEmpresaByEmail('', $ruc);
            if ($p) {
                // traer todos as empresas que existan
                $sql = "SELECT id FROM empresa WHERE ruc={$ruc}";
                $res = $db->fetchAll($sql);
                if (count($res) > 1) {
                    $ids = array();
                    $out = "El RUC <b>{$ruc}</b> se elimino de las siguientes empresas : <br>";
                    $out .='<ul>';
                    foreach ($res as $item) {
                        $id    = $item['id'];
                        $out .="<li>{$id}</li>";
                        $ids[] = $id;
                    }
                    $out .='</ul>';
                    $sql = "UPDATE empresa SET ruc=' ' WHERE id IN (".implode(',',
                            $ids).")";
                    $stm = $db->query($sql);
                    if ($stm->rowCount() > 0) {
                        echo $out;
                    } else {
                        echo "Ocurrio un error al eliminar el RUC: {$ruc}, intentalo nuevamente.";
                    }
                } else {
                    echo "El RUC : {$ruc}, solo esta asociado a una empresa : $p[idempresa].";
                }
            } else {
                echo "No exista una empresa con RUC : {$ruc}.";
            }
        } else {
            echo "El RUC {$ruc} no es correcto.";
            return FALSE;
        }
    }

    public function cronFixAdecsys($wa, $wt)
    {

        $compra              = new Application_Model_Compra;
        $compraAdecsysCodigo = new Application_Model_CompraAdecsysCodigo;
        $anuncioImpreso      = new Application_Model_AnuncioImpreso;
        $anuncioWeb          = new Application_Model_AnuncioWeb;
        $dataCompra          = $compra->obtenerRegistroFaltantesCompraAdecsysCodigoNuevo();
        $medAPT              = Application_Model_Tarifa::MEDIOPUB_APTITUS;
        $medTALAN            = Application_Model_Tarifa::MEDIOPUB_TALAN;
        $cant                = 0;
        $total               = count($dataCompra);
        $db                  = $compra->getAdapter();
        $db->beginTransaction();
        try {
            $msg = "Se procesaran $total compras".PHP_EOL;
            foreach ($dataCompra as $registro) {
                $idCompra         = $registro['id'];
                $adecsysEnte      = $registro['adecsys_ente_id'];
                $enteId           = $registro['ad_ente_id'];
                $fhPubConfirmada  = $registro['fh_pub_confirmada'];
                $idAnuncioImpreso = $registro['id_anuncio_impreso'];
                $medioPub         = $registro['medio_pub'];
                $cant++;
                $msg.="=====Compra $idCompra ($cant de $total)=====".PHP_EOL;
                if (is_null($adecsysEnte)) {
                    $where = $compra->getAdapter()->quoteInto('id = ?',
                        $idCompra);
                    $compra->update(array('adecsys_ente_id' => $enteId), $where);
                    $msg.="Se actualizo el adecsys_ente_id a $enteId".PHP_EOL;
                }
                if (is_null($fhPubConfirmada) && !is_null($idAnuncioImpreso)) {
                    $where   = $anuncioImpreso->getAdapter()->quoteInto('id = ?',
                        $idAnuncioImpreso);
                    if ($medioPub == $medAPT) $w       = $wa;
                    else $w       = $wt;
                    $proxDom = date('Y-m-d', strtotime("+$w sunday"));
                    $anuncioImpreso->update(array('fh_pub_confirmada' => $proxDom),
                        $where);
                    $msg.="Se actualizo la fh_pub_confirmada a $proxDom al impreso $idAnuncioImpreso".PHP_EOL;
                }
                $arrayAW = $anuncioWeb->getByIdCompra($idCompra);
                foreach ($arrayAW as $dataAW) {
                    if ($idAnuncioImpreso == $dataAW['id']) {
                        $where = $anuncioWeb->getAdapter()->quoteInto('id = ?',
                            $dataAW['id']);
                        $anuncioWeb->update(array('id_compra' => null), $where);
                        $msg.="Se actualizo el id_compra a null al aviso {$dataAW['id']}".PHP_EOL;
                    }
                }

                if ($medioPub == $medAPT) {
                    //Valida existencia de registro
                    if (!$compraAdecsysCodigo->validaRegistro($idCompra, $medAPT)) {
                        $compraAdecsysCodigo->insert(array('id_compra' => $idCompra,
                            'medio_publicacion' => $medAPT));
                        $msg.="Se inserto compra_adecsys_codigo para $medAPT".PHP_EOL;
                    }
                } else if ($medioPub == $medTALAN) {
                    //Valida existencia de registro
                    if (!$compraAdecsysCodigo->validaRegistro($idCompra,
                            $medTALAN)) {
                        $compraAdecsysCodigo->insert(array('id_compra' => $idCompra,
                            'medio_publicacion' => $medTALAN));
                        $msg.="Se inserto compra_adecsys_codigo para $medTALAN".PHP_EOL;
                    }
                } else if ($medioPub == Application_Model_Tarifa::MEDIOPUB_APTITUS_TALAN) {
                    //Valida existencia de registro
                    if (!$compraAdecsysCodigo->validaRegistro($idCompra,
                            'aptitus_combo')) {
                        $compraAdecsysCodigo->insert(array('id_compra' => $idCompra,
                            'medio_publicacion' => 'aptitus_combo'));
                        $msg.="Se inserto compra_adecsys_codigo para aptitus_combo".PHP_EOL;
                    }
                    //Valida existencia de registro
                    if (!$compraAdecsysCodigo->validaRegistro($idCompra,
                            'talan_combo')) {
                        $compraAdecsysCodigo->insert(array('id_compra' => $idCompra,
                            'medio_publicacion' => 'talan_combo'));
                        $msg.="Se inserto compra_adecsys_codigo para talan_combo".PHP_EOL;
                    }
                }
                $msg.="=====Fin Compra $idCompra=====".PHP_EOL;
            }
            $msg.= "Se procesaron  ".$cant." compras".PHP_EOL;
            $db->commit();
        } catch (Zend_Db_Exception $e) {
            $db->rollBack();
            $msg = $e->getMessage().PHP_EOL.$e->getTraceAsString();
        } catch (Zend_Exception $e) {
            $msg = $e->getMessage().PHP_EOL.$e->getTraceAsString();
        }
        $msgMail  = nl2br($msg);
        echo $msgMail;
        $mail     = new App_Controller_Action_Helper_Mail();
        $dataMail = array(
            'to' => 'aespinoza@clicksandbricks.pe',
            'razonSocial' => $msgMail,
            'tipoAnuncio' => ',reporte fix adecsys'
        );
        $mail->adecsysAviso($dataMail);
    }

    /**
     * En el caso de existir extornos, dado que Adecsys no soporta extornos, deberán 
     * ser enviados por email con el siguiente subject: Extornos del día $dd-MM-YY 
     * 3 veces al día a las 09:00am, 12:00am, 06:00 todos los días al email 
     * info@aptitus.com.pe, en su defecto sea enviado apenas se realice el extorno.
     */
    public function enviarExtornosPE()
    {
        $modCompra = new Application_Model_Compra();
        $compras   = $modCompra->getExtornados();
        $data      = array();

        if (count($compras)) {
            foreach ($compras as $item) {
                $rowCompra                          = $modCompra->getDetalleCompraAnuncio($item['id']);
                $data[$item['id']]['tipoAnuncio']   = $rowCompra['tipoAnuncio'];
                $data[$item['id']]['cip']           = $rowCompra['cip'];
                $data[$item['id']]['total']         = $rowCompra['montoTotal'];
                $data[$item['id']]['medioPago']     = $rowCompra['medioPago'];
                $data[$item['id']]['razonSocial']   = $rowCompra['razonSocial'];
                $data[$item['id']]['numeroDoc']     = $rowCompra['numeroDoc'];
                $data[$item['id']]['empresaId']     = $rowCompra['empresaId'];
                $data[$item['id']]['emailContacto'] = $rowCompra['emailContacto'];
            }
            $config = Zend_Registry::get('config');

            $mail     = new App_Controller_Action_Helper_Mail();
            $dataMail = array(
                'to' => $config->extorno->info->email,
                'data' => $data,
                'hoy' => date("d-m-Y")
            );
            $mail->enviarExtornos($dataMail);
        }
    }

    public function SolrUbigeo()
    {
        $config      = Zend_Registry::get('config');
        $adapter     = Zend_Db_Table::getDefaultAdapter();
        $modelUbigeo = new Solr_SolrUbigeo();

        $sql = "
        SELECT
            `pais`.`id`     AS `pais_id`,
            `pais`.`nombre` AS `pais_nombre`,
            `dpto`.`id`     AS `dpto_id`,
            `dpto`.`nombre` AS `dpto_nombre`,
            `prov`.`id`     AS `prov_id`,
            `prov`.`nombre` AS `prov_nombre`,
            `dist`.`id`     AS `dist_id`,
            `dist`.`nombre` AS `dist_nombre`,
            REPLACE(LCASE(`dist`.`nombre`),' ','-') AS `dist_nombre_slug`,
            CONCAT_WS(' ',LCASE(`dist`.`nombre`),LCASE(`prov`.`nombre`),LCASE(`dpto`.`nombre`),LCASE(`pais`.`nombre`)) AS `ubicacion`,
            CONCAT_WS(', ',LCASE(`dist`.`nombre`),LCASE(`prov`.`nombre`),LCASE(`dpto`.`nombre`),LCASE(`pais`.`nombre`)) AS `mostrar`
           FROM (((`ubigeo` `pais`
               LEFT JOIN `ubigeo` `dpto`
                 ON ((`dpto`.`padre` = `pais`.`id`)))
              LEFT JOIN `ubigeo` `prov`
                ON ((`prov`.`padre` = `dpto`.`id`)))
             LEFT JOIN `ubigeo` `dist`
               ON ((`dist`.`padre` = `prov`.`id`)))
           WHERE ((`pais`.`level` = 0)
                 AND (`dpto`.`level` = 1)
                 AND (`prov`.`level` = 2)
                 AND (`dist`.`level` = 3))
        ";
        $tmp = $adapter->fetchAll($sql);
    }

    public function sql1()
    {
        $db = new App_Db_Table_Abstract();

        $sql = '
                    UPDATE institucion SET estado = 0 WHERE id_ubigeo = 2533;
UPDATE institucion SET estado = 0 WHERE id_ubigeo IN (SELECT id FROM ubigeo WHERE padre = 2533);
UPDATE institucion SET estado = 0 WHERE id_ubigeo IN (SELECT id FROM ubigeo WHERE padre IN (SELECT id FROM ubigeo WHERE padre = 2533));
UPDATE institucion SET estado = 0 WHERE id_ubigeo IN (SELECT id FROM ubigeo WHERE padre IN (SELECT id FROM ubigeo WHERE padre IN (SELECT id FROM ubigeo WHERE padre = 2533)));';
        $db->getAdapter()->query($sql);
    }

    public function pagoPf()
    {
        $modCompra  = new Application_Model_Compra();
        $data       = $modCompra->getCompraPf();
        $helperMail = new App_Controller_Action_Helper_Mail();
        foreach ($data as $key => $value) {
            $helper    = new App_Controller_Action_Helper_WSNicaraguaAviso();
            $helper->envioPfAvisoWeb($value['id']);
            $rowCompra = $modCompra->getDetalleCompraAnuncio($value['id']);
            if (!empty($rowCompra['adecsys_code'])) {
                $tipo = '';
                switch ($rowCompra['medioPago']) {
                    case 'credomatic':
                        $tipo = 'Credomatic';

                        break;
                    case 'pf':
                        $tipo = 'Punto facil';

                        break;
                    case 'pv':
                        $tipo = 'Pago de Ventanilla';

                        break;
                    default:
                        break;
                }
                $nameprioridad = '';
                switch ($rowCompra['anuncioPrioridad']) {
                    case '1':
                        $nameprioridad = 'Web destacado Oro';

                        break;
                    case '2':
                        $nameprioridad = 'Web destacado Plata';
                        break;
                    default:
                        $nameprioridad = 'Aviso gratuito';
                        break;
                }
                $dataMail = array(
                    'to' => $rowCompra['emailContacto'],
                    'usuario' => $rowCompra['emailContacto'],
                    'tipo_doc' => $rowCompra['tipo_doc'],
                    'numDocumento' => $rowCompra['numDocumento'],
                    'nombre' => $rowCompra['nombreContacto']." ".$rowCompra['apePatContacto'],
                    'anuncioPuesto' => $rowCompra['anuncioPuesto'],
                    'razonSocial' => $rowCompra['nombre_comercial'],
                    'montoTotal' => (float) $rowCompra['montoWeb'] + (float) $rowCompra['montoImpreso'],
                    'medioPago' => $tipo,
                    'anuncioClase' => $rowCompra['anuncioClase'],
                    'productoNombre' => $rowCompra['productoNombre'],
                    'anuncioUrl' => $rowCompra['anuncioUrl'],
                    'fechaPago' => $rowCompra['fechaPago'],
                    'anuncioFechaVencimiento' => $rowCompra['anuncioFechaVencimiento'],
                    'fechaPublicConfirmada' => $rowCompra['fechaPublicConfirmada'],
                    'medioPublicacion' => $rowCompra['medioPublicacion'],
                    'anuncioSlug' => $rowCompra['anuncioSlug'],
                    'prioridad' => $nameprioridad,
                    'anuncioFechaVencimientoProceso' => $rowCompra['anuncioFechaVencimientoProceso'],
                    'codigo_adecsys_compra' => $rowCompra['adecsys_code'],
                    'compraId' => $rowCompra['compraId'],
                    'tipo' => $rowCompra['tipoAnuncio']
                );
                $helperMail->confirmarCompra($dataMail);
                echo "Se envio el siguiente Aviso {$value['id']} [OK]".PHP_EOL;
            }
                        echo "No se envio el siguiente Aviso {$value['id']} [OK]".PHP_EOL;

        }
    }

    public function Reproceso()
    {
        $modCompra = new Application_Model_Compra();
        $data      = $modCompra->getCompraFail();
        foreach ($data as $key => $value) {
            $helper = new App_Controller_Action_Helper_WSNicaraguaAviso();
            // if ($value['id'] != 1088) {
            $helper->envioAvisoWebReproceso($value['id']);
            //}
            echo "Se envio el siguiente Aviso {$value['id']} [OK]".PHP_EOL;
        }
    }
}