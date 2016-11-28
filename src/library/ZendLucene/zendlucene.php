<?php
/**
 * Libreria para manejo de Zend Search Lucene
 */
class ZendLucene
{
    //BUSCADOR PARA PROCESO SELECCION
    protected $_index;
    protected $_ruta;

    //BUSCADOR PARA POSTULANTES EMPRESA
    protected $_indexpostulantes;
    protected $_rutapostulantes;

    //BUSCADOR PARA AVISOS
    /**
     * @var Zend_Search_Lucene_Interface
     */
    protected $_indexavisos;
    protected $_rutaavisos;

    //N ZEROS
    protected $_nzeros;

    //N PAGINADOS
    protected $_nPaginadoBuscadorPostulaciones=1000;
    protected $_nPaginadoBuscadorUsuarios=1000;


    protected $_specialSearchChars = '"';

    protected $_config;
    
    protected $_wsMode = false;
    protected $_readOnly = false;
    protected $_syncPostulantesMode = false;
    
    //BUSCADOR PARA AVISOS
    /**
     * @var ZendLucene_TempWriter
     */
    protected $_encolador;
    protected $_logCron;
    protected $_logErrorOpen;
    /**
     * @todo Mejorar para que la lectura de los indices solo se haga para el indice necesario,
     * Actualmente se hace un ::open() a los 3. Debe ser solo al que se requiere
     */
    
    public function  __construct($index='ALL')
    {
        $this->_config = Zend_Registry::get("config");
        
        $this->_nzeros=10;
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(
            new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num()
        );
        /*Zend_Search_Lucene_Analysis_Analyzer::setDefault(
            new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive()
        );*/
        $this->_logCron = new Zend_Log(
            new Zend_Log_Writer_Stream(APPLICATION_PATH."/../logs/LogCronLucene")
        );
        
        $this->_logErrorOpen = new Zend_Log(
            new Zend_Log_Writer_Stream(APPLICATION_PATH."/../logs/LogErrorOpenLucene.txt")
        );
        
        $this->_encolador = new ZendLucene_TempWriter();
        $this->_wsMode = $this->_config->lucene->wsMode;
        $this->_readOnly = $this->_config->lucene->readOnly;
        $this->_syncPostulantesMode = $this->_config->lucene->syncModePostulantes;
        /*
        $this->_ruta = $this->_config->lucene->index->postulaciones;
        if ($index=='ALL' || $index=='postulaciones') {
             if (file_exists($this->_ruta)) {
                if (is_readable($this->_ruta)) {
                    try {
                        $this->_index = Zend_Search_Lucene::open($this->_ruta);
                    } catch (Exception $ex) {
                        $this->_logErrorOpen->log($ex->getMessage(), Zend_Log::CRIT);
                    }
                    
                } else {
                    throw new Zend_Exception(
                        "No se pueden leer los indices ($index) de Zend_Lucene"
                    );
                }
            } else {
                $this->_logErrorOpen->log("El Documento ($this->_ruta) de Zend_Lucene No Existe", Zend_Log::CRIT);
            }
        }
         
         */
/*
        $this->_rutapostulantes = $this->_config->lucene->index->postulantes;
        if ($index=='ALL' || $index=='postulantes') {
            if (file_exists($this->_rutapostulantes)) {
                if ( is_readable($this->_rutapostulantes) ) {
                    try {
                        $this->_indexpostulantes = Zend_Search_Lucene::open($this->_rutapostulantes);
                    } catch (Exception $ex) {
                        $this->_logErrorOpen->log($ex->getMessage(), Zend_Log::CRIT);
                    }
                } else {
                    throw new Zend_Exception(
                        "No se pueden leer los indices ($index) de Zend_Lucene"
                    );
                    $this->_logErrorOpen->log("No se pueden leer los indices ($index) de Zend_Lucene", Zend_Log::CRIT);
                }
            } else {
                $this->_logErrorOpen->log(
                    "El Documento ($this->_rutapostulantes) de Zend_Lucene No Existe", Zend_Log::CRIT
                );
            }
        }
*/
        /*
        if ($index=='ALL' || $index=='avisos') {
            $this->_rutaavisos = $this->_config->lucene->index->avisos;
            if ( file_exists($this->_rutaavisos)) {
                if ( is_readable($this->_rutaavisos) ) {
                    try {
                        $this->_indexavisos = Zend_Search_Lucene::open($this->_rutaavisos);
                    } catch (Exception $ex) {
                        $this->_logErrorOpen->log($ex->getMessage(), Zend_Log::CRIT);
                    }
                } else {
                    throw new Zend_Exception(
                        "No se pueden leer los indices ($index) de Zend_Lucene"
                    );
                    $this->_logErrorOpen->log("No se pueden leer los indices ($index) de Zend_Lucene", Zend_Log::CRIT);
                }
            } else {
                $this->_logErrorOpen->log("El Documento ($this->_rutaavisos) de Zend_Lucene No Existe", Zend_Log::CRIT);
            }
        }
         
         */
    }

    function optimize_Indexes($n)
    {
        echo "Optimizando Indice de ".$n."....".PHP_EOL;
        try {
            switch ($n) {
                case "postulaciones":
                    $this->_index->optimize();
                    break;
                case "postulantes":
                    $this->_indexpostulantes->optimize();
                    break;
                case "avisos":
                    $this->_indexavisos->optimize();
                    break;
            }
        } catch (Exception $e) {
            echo $e->getMessage().PHP_EOL;
            echo $e->getTraceAsString().PHP_EOL;
        }
        echo "Indice de ".$n." ha sido Optimizado ........... [OK]".PHP_EOL;
    }
    function load_Indexes($n)
    {
            echo "Creando Indices para Zend Lucene en proyecto APTiTUS...\n";
        try {
            switch ($n) {
                case "postulaciones":
                    $this->_index = Zend_Search_Lucene::create($this->_ruta);
                    $this->makeIndexesPostulantes();
                    echo "Indice de Buscador de Proceso de Seleccion.........[OK]".PHP_EOL;
                    break;
                case "postulantes":
                    $this->_indexpostulantes = Zend_Search_Lucene::create($this->_rutapostulantes);
                    $this->makeIndexesUsuarios();
                    echo "Indice de Buscador de Postulantes en Empresa.......[OK]".PHP_EOL;
                    break;
                case "avisos":
                    $this->_indexavisos = Zend_Search_Lucene::create($this->_rutaavisos);
                    $this->makeIndexesAvisos();
                    echo "Indice de Buscador de Avisos.......................[OK]".PHP_EOL;
                    break;
                case "postulaciones2":
                    $this->_indexpostulantes = Zend_Search_Lucene::create($this->_rutapostulantes);
                    $this->makeIndexesPostulantes2();
                    echo "Indice de Buscador de Proceso de Seleccion 2.......[OK]".PHP_EOL;
                    break;
            }
        } catch (Zend_Search_Lucene_Exception $e) {
            echo $e->getMessage().PHP_EOL;
            echo $e->getTraceAsString().PHP_EOL;
        } catch (Zend_Search_Exception $e) {
            echo $e->getMessage().PHP_EOL;
            echo $e->getTraceAsString().PHP_EOL;
        } catch (Zend_Exception $e) {
            echo $e->getMessage().PHP_EOL;
            echo $e->getTraceAsString().PHP_EOL;
        } catch (Exception $e) {
            echo $e->getMessage().PHP_EOL;
            echo $e->getTraceAsString().PHP_EOL;
        }
    }

//    private function init()
//    {
//    }
//    function index_exists()
//    {
//        if (0) {
//            return true;
//        } else {
//            return false;
//        }
//    }

    private function addDocumentPostulantes($value)
    {
        //@codingStandardsIgnoreStart
            $doc = new Zend_Search_Lucene_Document();
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('idanuncioweb', $value["idanuncioweb"])
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('idpostulante', $value["idpostulante"])
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('idpostulacion', $value["idpostulacion"])
            );
            $doc->addField(
                Zend_Search_Lucene_Field::UnIndexed('foto', $value["foto"])
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('nombres', $value["nombres"], "UTF-8")
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('apellidos', $value["apellidos"], "UTF-8")
            );
            $doc->addField(Zend_Search_Lucene_Field::Keyword('telefono', $value["telefono"]));
            $doc->addField(Zend_Search_Lucene_Field::Keyword('celular', $value["celular"]));
            $doc->addField(Zend_Search_Lucene_Field::UnIndexed('slug', $value["slug"]));
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('msgporresponder', $value["msg_por_responder"])
            );
            $doc->addField(Zend_Search_Lucene_Field::Keyword('sexo', $value["sexo"]));
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('edad', $this->fillZeroField($value["edad"]))
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword(
                    'fecha_nac',
                    $this->fillZeroField($value["fecha_nac"])
                )
            );
            $doc->addField(Zend_Search_Lucene_Field::Keyword('match', $value["match"]));
            $doc->addField(
                Zend_Search_Lucene_Field::Text('nivelestudio', $value["nivel_estudio"], "UTF-8")
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('nivel_estudio', $value["nivel_estudio"], "UTF-8")
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('carrera', $value["carrera"], "UTF-8")
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('carrerap', $value["carrerap"], "UTF-8")
            );
            $doc->addField(
                Zend_Search_Lucene_Field::UnIndexed('pathcv', $value["path_cv"])
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword(
                    'msgnoleidos', $this->fillZeroField($value["msg_no_leidos"])
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword(
                    'msgrespondido', $this->fillZeroField($value["msg_respondido"])
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('esnuevo', $value["es_nuevo"])
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('invitacion', $value["invitacion"])
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('referenciado', $value["referenciado"])
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword(
                    'idcategoriapostulacion',
                    is_null($value["id_categoria_postulacion"])?0:$value["id_categoria_postulacion"]
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('descartado', $value["descartado"])
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('estudios', $value["estudios"], "UTF-8")
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('estudiosclaves', $value["estudios_claves"])
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('carreraclaves', $value["carrera_claves"])
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'experiencia',
                    $this->fillZeroField($this->SumaCadena($value["experiencia"], "-"))
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('idiomas', $value["idiomas"])
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('programasclaves', $value["programas_claves"])
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('sexoclaves', $value["sexo_claves"])
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('ubigeoclaves', $value["ubigeo_claves"])
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('ubigeo', $this->fillZeroField($value["ubigeo"]))
            );
            $doc->addField(
                Zend_Search_Lucene_Field::keyword('online', $value["online"])
            );

            $this->_index->addDocument($doc);
            $doc = null;
       //@codingStandardsIgnoreEnd
    }
    private function addDocumentPostulantesZL($value)
    {
        //@codingStandardsIgnoreStart
            $doc = new Zend_Search_Lucene_Document();
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('idanuncioweb', $value->idanuncioweb)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('idpostulante', $value->idpostulante)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('idpostulacion', $value->idpostulacion)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::UnIndexed('foto', $value->foto)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('nombres', $value->nombres, "UTF-8")
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('apellidos', $value->apellidos, "UTF-8")
            );
            $doc->addField(Zend_Search_Lucene_Field::Keyword('telefono', $value->telefono));
            $doc->addField(Zend_Search_Lucene_Field::Keyword('celular', $value->celular));
            $doc->addField(Zend_Search_Lucene_Field::UnIndexed('slug', $value->slug));
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('msgporresponder', $value->msgporresponder)
            );
            $doc->addField(Zend_Search_Lucene_Field::Keyword('sexo', $value->sexo));
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('edad', $value->edad)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword(
                    'fecha_nac',
                    $value->fecha_nac
                )
            );
            $doc->addField(Zend_Search_Lucene_Field::Keyword('match', $value->match));
            $doc->addField(
                Zend_Search_Lucene_Field::Text('nivelestudio', $value->nivelestudio, "UTF-8")
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('nivel_estudio', $value->nivel_estudio, "UTF-8")
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('carrera', $value->carrera, "UTF-8")
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('carrerap', $value->carrerap, "UTF-8")
            );
            $doc->addField(
                Zend_Search_Lucene_Field::UnIndexed('pathcv', $value->pathcv)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword(
                    'msgnoleidos', $value->msgnoleidos
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword(
                    'msgrespondido', $value->msgrespondido
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('esnuevo', $value->esnuevo)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('invitacion', $value->invitacion)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('referenciado', $value->referenciado)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword(
                    'idcategoriapostulacion',
                    $value->idcategoriapostulacion
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('descartado', $value->descartado)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('estudios', $value->estudios, "UTF-8")
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('estudiosclaves', $value->estudiosclaves)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('carreraclaves', $value->carreraclaves)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'experiencia', $value->experiencia
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('idiomas', $value->idiomas)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('programasclaves', $value->programasclaves)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('sexoclaves', $value->sexoclaves)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('ubigeoclaves', $value->ubigeoclaves)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('ubigeo', $value->ubigeo)
            );
            @$doc->addField(
                Zend_Search_Lucene_Field::keyword('online', $value->online)
            );
            $this->_index->addDocument($doc);
       //@codingStandardsIgnoreEnd
    }

    function agregarindicesavisos($inicio, $cantidad, $total)
    {
        $_anuncio = new Application_Model_AnuncioWeb();
        $result = $_anuncio->getRellenarIndexPostulaciones($inicio, $cantidad);
        foreach ($result as $key=>$value) {
            try {
                $this->addDocumentPostulantes($value);
            } catch(Zend_Search_Lucene_Document_Exception $ex) {
                echo "Error Linea:(".$ex->getLine().") archivo:(".$ex->getFile().") 
                      Agregando Postulacion a ZL: ".$ex->getMessage();
            }
        }
        unset($result);

        $time = new DateTime();
        echo "[".($inicio+$cantidad)." de $total] Indices de Postulaciones..............."
            .((($inicio+$cantidad)/$total)*100)." % Completado a las:"
            .$time->format("d/m/Y H:i:s").PHP_EOL;
        $time = null;
    }
    
    private function makeIndexesPostulantes()
    {
        $_anuncio = new Application_Model_AnuncioWeb();
        $n = $_anuncio->getCountPostulaciones();
        $n = $n["0"]["n"];
        echo "\nNPostulantes: ".$n.PHP_EOL;
        $valor = $this->_nPaginadoBuscadorPostulaciones;
        if ($n<$valor) {
            $valor = $n;
        }
        
        for ($i=0;$i<$n;$i+=$valor) {
            $result = $_anuncio->getRellenarIndexPostulaciones($i, $valor);
            foreach ($result as $key=>$value) {
                try {
                    $this->addDocumentPostulantes($value);
                } catch(Zend_Search_Lucene_Document_Exception $ex) {
                    echo "Error Linea:(".$ex->getLine().") archivo:(".$ex->getFile().") 
                          Agregando Postulacion a ZL: ".$ex->getMessage();
                }
            }
            unset($result);

            $time = new DateTime();
            echo "[".($i+$valor)." de $n] Indices de Postulaciones..............."
                .((($i+$valor)/$n)*100)." % Completado a las:".$time->format("d/m/Y H:i:s").PHP_EOL;
            $time = null;
        }
        $this->_index->commit();
        $this->_index->optimize();
        $timeFinal = new DateTime("now");
        echo PHP_EOL."Finaliza proceso a las:".$timeFinal->format("d/m/Y H:i:s").PHP_EOL.PHP_EOL;
    }

    function makeIndexesUsuarios()
    {
        $_anuncio = new Application_Model_AnuncioWeb();
        $n = $_anuncio->getCountPostulantes();

        $n = $n["0"]["n"];
        echo PHP_EOL."NPostulaciones: ".$n.PHP_EOL;
        $timeActual = new DateTime();
        echo "Iniciando a las: ".$timeActual->format("d/m/Y H:i:s").PHP_EOL;
        $valor = $this->_nPaginadoBuscadorUsuarios;
        if ($n<$valor) {
            $valor = $n;
        }
        $modelPostulante = new Application_Model_Postulante();
        for ($i=0;$i<$n;$i+=$valor) {
            $result = $_anuncio->getRellenarIndexUsuarios($i, $valor);
            foreach ($result as $key=>$value) {
                try {
                    $this->addDocumentUsuarios($value);
                } catch(Zend_Search_Lucene_Document_Exception $ex) {
                    echo "Error Linea:(".$ex->getLine().") archivo:(".$ex->getFile().")
                          Agregando Postulacion a ZL: ".$ex->getMessage();
                }
            }
            $modelPostulante->sincronizaPostulantes($i, $valor);
            $time = new DateTime();
            echo "[".($i+$valor)." de $n] Indices de Postulantes ..............."
                .((($i+$valor)/$n)*100)." % Completado a las:".$time->format("d/m/Y H:i:s").PHP_EOL;
        }
        
        $this->_indexpostulantes->commit();
        $this->_indexpostulantes->optimize();
        
        $timeFinal = new DateTime("now");
        echo "\nFinaliza proceso a las:".$timeFinal->format("d/m/Y H:i:s").PHP_EOL.PHP_EOL;
    }
    
    
    
    /**
     * @author eanaya
     */
    function makeIndexesAvisos()
    {
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $mAnuncio = new Application_Model_AnuncioWeb();
        $sql = $mAnuncio->select()->from(
            $mAnuncio->info('name'), array(
                'id', 'url_id'
            )
        )->where('online=1');
        $anunciosActivos = $db->fetchPairs($sql);
        $cont = 1;
        $total = count($anunciosActivos);
        foreach ($anunciosActivos as $awid => $urlId) {
            $urlHelper = new Zend_View_Helper_Url();
            $url = $urlHelper->url(array('url_id'=>$urlId), 'lucene_ad', true);
            
            if (1) { // obteniendo el HTML con Zend_Client
                $client = new Zend_Http_Client();
                $client->setConfig(array('timeout'=>60));
                $client->setUri(SITE_URL.$url);
                $html = $client->request(Zend_Http_Client::GET)->getBody();
                $client = null;
            } else { // obteniendo el HTML con Zend_Controller_Front
                $req = new Zend_Controller_Request_Http();
                $req->setRequestUri($url);
                $f = Zend_Controller_Front::getInstance();
                $f->setRequest($req);
                $f->returnResponse(true);
                $html = $f->dispatch()->getBody();
                $req->clearParams();
                $f->clearParams();
            }
            $doc = Zend_Search_Lucene_Document_Html::loadHTML($html, false, "UTF-8");
            $doc->addField(Zend_Search_Lucene_Field::keyword('awid', $awid));
            $doc->addField(Zend_Search_Lucene_Field::keyword('urlId', $urlId));
            $this->_indexavisos->addDocument($doc);
            $doc = null;
            $time = new DateTime();
            echo $cont." de $total Avisos ..............".$time->format("d/m/Y H:i:s").PHP_EOL;
            $time = null;
            /*file_put_contents(
                APPLICATION_PATH."/../indexes/html/".date('HmdHis').
                "-$urlId.html", $html
            );*/
            $cont++;
        }
        $this->_indexavisos->commit();
        $this->_indexavisos->optimize();
    }

    public function agregarNuevosDocumentosAvisos($idsAvisos, $replicar = true, $encolar = true)
    {
        if ($encolar && $this->_readOnly) {
            $this->_encolador->encolarElemento(
                'avisos', array($idsAvisos, false, false), __FUNCTION__
            );
            return true;
        } else {
            $aw = new Application_Model_AnuncioWeb();

            $dataAnuncios = array();
            if (file_exists($this->_rutaavisos) && is_readable($this->_rutaavisos)) {
                $indexavisos = Zend_Search_Lucene::open($this->_rutaavisos);
                foreach ($idsAvisos as $id) {
                    echo $id."<--".PHP_EOL;
                    $awid = $id;

                    $urlId = $aw->getAvisoById($awid);
                    $urlId = $urlId['url_id'];

                    $urlHelper = new Zend_View_Helper_Url();
                    $url = $urlHelper->url(array('url_id'=>$urlId), 'lucene_ad', true);

                    // obteniendo el HTML con Zend_Client
                    $client = new Zend_Http_Client();
                    $urlSite = SITE_URL.$url;
                    $client->setUri(SITE_URL.$url);
                    $html = $client->request(Zend_Http_Client::GET)->getBody();
                    $client = null;

                    $anuncio = array();
                    $anuncio["awid"] = $awid;
                    $anuncio["urlId"] = $urlId;
                    $anuncio["urlSite"] = $urlSite;
                    $dataAnuncios[] = $anuncio;

                    $doc = Zend_Search_Lucene_Document_Html::loadHTML($html, false, "UTF-8");
                    $doc->addField(Zend_Search_Lucene_Field::keyword('awid', $awid));
                    $doc->addField(Zend_Search_Lucene_Field::keyword('urlId', $urlId));
                    $indexavisos->addDocument($doc);
                    $doc = null;
                }
                $indexavisos->commit();

                if (count($idsAvisos) > 0) {
                    $start = $this->getMicroTime();
                    $delay = $this->_config->lucene->timeout;
                    $fila = null;
                    while ($fila == null || count($fila) <= 0) {
                        $fila = $indexavisos->find("awid:".$awid);
                        $end = $this->getMicroTime();

                        if ($end > ($start + $delay)) {
                            $this->_logCron->log(
                                "Error al agregar anuncios web: ", Zend_Log::CRIT
                            );
                            break;
                        }
                    }
                } else {
                    return false;
                }
                
                if ($replicar && $this->_wsMode) {
                    foreach ($this->_config->deploymentInstances as $instanceName => $ip) {
                        if ($ip !== $this->_config->deploymentCurrentInstance) {
                            $wsc = new Zend_Soap_Client("http://".$ip."/api?wsdl");
                            //var_dump($wsc->getWsdl());
                            //$wsc->setUri();
                            $wsc->agregarDocumentosAvisos($dataAnuncios);
                        }
                    }
                }
            }
        }
    }
    
    public function agregarNuevoDocumentoAviso($idAviso, $replicar = true, $encolar = true)
    {
        if ($encolar && $this->_readOnly) {
            $this->_encolador->encolarElemento(
                'avisos', array($idAviso, false, false), __FUNCTION__
            );
            return true;
        } else {
            $aw = new Application_Model_AnuncioWeb();
            if (file_exists($this->_rutaavisos) && is_readable($this->_rutaavisos)) {
                $indexavisos = Zend_Search_Lucene::open($this->_rutaavisos);
                $awid = $idAviso;

                $urlId = $aw->getAvisoById($awid);
                $urlId = $urlId['url_id'];

                $urlHelper = new Zend_View_Helper_Url();
                $url = $urlHelper->url(array('url_id'=>$urlId), 'lucene_ad', true);

                // obteniendo el HTML con Zend_Client
                $client = new Zend_Http_Client();
                $urlSite = SITE_URL.$url;
                $client->setUri(SITE_URL.$url);
                $html = $client->request(Zend_Http_Client::GET)->getBody();
                $client = null;

                $doc = Zend_Search_Lucene_Document_Html::loadHTML($html, false, "UTF-8");
                $doc->addField(Zend_Search_Lucene_Field::keyword('awid', $awid));
                $doc->addField(Zend_Search_Lucene_Field::keyword('urlId', $urlId));

                $indexavisos->addDocument($doc);
                $indexavisos->commit();
                
                $start = $this->getMicroTime();
                $delay = $this->_config->lucene->timeout;
                $fila = null;
                while ($fila == null || count($fila) <= 0) {
                    $fila = $indexavisos->find("awid:".$awid);
                    $end = $this->getMicroTime();

                    if ($end > ($start + $delay)) {
                        $this->_logCron->log(
                            "Error al agregar anuncio web, idanuncioweb: ".
                            $awid, Zend_Log::CRIT
                        );
                        return false;
                        break;
                    }
                }

                if ($replicar && $this->_wsMode) {
                    foreach ($this->_config->deploymentInstances as $instanceName => $ip) {
                        if ($ip !== $this->_config->deploymentCurrentInstance) {
                            $wsc = new Zend_Soap_Client("http://".$ip."/api?wsdl");
                            //var_dump($wsc->getWsdl());
                            //$wsc->setUri();
                            $wsc->agregarDocumentoAviso($urlSite, $awid, $urlId);
                        }
                    }
                }
                
                return true;
            }
            return false;
        }
    }

    public function agregarDocumentoAviso($urlSite, $awid, $urlId)
    {
        try{
            if (file_exists($this->_rutaavisos) && is_readable($this->_rutaavisos)) {
                $client = new Zend_Http_Client();
                $client->setUri($urlSite);
                $html = $client->request(Zend_Http_Client::GET)->getBody();
                $client = null;

                $doc = Zend_Search_Lucene_Document_Html::loadHTML($html, false, "UTF-8");
                $doc->addField(Zend_Search_Lucene_Field::keyword('awid', $awid));
                $doc->addField(Zend_Search_Lucene_Field::keyword('urlId', $urlId));

                $indexavisos = Zend_Search_Lucene::open($this->_rutaavisos);
                $indexavisos->addDocument($doc);
                $indexavisos->commit();
                return true;
            }
        } catch (Exception $ex) {
            return false;
        }
        
        return false;
    }
    
    public function eliminarDocumentoAviso($idAviso, $replicar = true, $encolar = true)
    {
        if ($encolar && $this->_readOnly) {
            $this->_encolador->encolarElemento(
                'avisos', array($idAviso, false, false), __FUNCTION__
            );
            return true;
        } else {
            $aw = new Application_Model_AnuncioWeb();
            if (file_exists($this->_rutaavisos) && is_readable($this->_rutaavisos)) {
                $indexavisos = Zend_Search_Lucene::open($this->_rutaavisos);
                $fila = $indexavisos->find("awid:".$idAviso);
                
                foreach ($fila as $hit) {
                    $indexavisos->delete($hit->id);
                }
                
                $indexavisos->commit();
                
                /*
                if ($replicar && $this->_wsMode) {
                    foreach($this->_config->deploymentInstances as $instanceName => $ip){
                        if($ip !== $this->_config->deploymentCurrentInstance){
                            $wsc = new Zend_Soap_Client("http://".$ip."/api?wsdl");
                            //var_dump($wsc->getWsdl());
                            //$wsc->setUri();
                            $wsc->agregarDocumentoAviso($urlSite, $awid, $urlId);
                        }
                    }
                }
                */
                return true;
            }
            return false;
        }
    }
    
    public function eliminarDocumentosAvisos($idsAvisos, $replicar = true, $encolar = true)
    {
        if ($encolar && $this->_readOnly) {
            $cant = count($idsAvisos);
            $i = 0;
            while ($cant > 0) {
                $tam = 0;
                if ($cant > 20) {
                    $tam = 20;
                } else {
                    $tam = $cant; 
                }
                
                $subArray = array();
                for ($j = $i; $j < $i+$tam; $j++) {
                    $subArray = $idsAvisos[$j];
                }
                $i = $i + $tam;
                $cant = $cant - $tam;
                $this->_encolador->encolarElemento(
                    'avisos', array($subArray, false, false), __FUNCTION__
                );
            }
            
            return true;
        } else {
            $aw = new Application_Model_AnuncioWeb();
            if (file_exists($this->_rutaavisos) && is_readable($this->_rutaavisos)) {
                $indexavisos = Zend_Search_Lucene::open($this->_rutaavisos);
                try {
                    foreach ($idsAvisos as $id) {
                        $fila = $indexavisos->find("awid:".$id);

                        foreach ($fila as $hit) {
                            $indexavisos->delete($hit->id);
                        }
                    }
                    $indexavisos->commit();
                }catch (Exception $ex) {
                    //var_dump($ex);
                }

                /*
                if ($replicar && $this->_wsMode) {
                    foreach($this->_config->deploymentInstances as $instanceName => $ip){
                        if($ip !== $this->_config->deploymentCurrentInstance){
                            $wsc = new Zend_Soap_Client("http://".$ip."/api?wsdl");
                            //var_dump($wsc->getWsdl());
                            //$wsc->setUri();
                            $wsc->agregarDocumentoAviso($urlSite, $awid, $urlId);
                        }
                    }
                }
                */
                return true;
            }
            return false;
        }
    }
    
    function addDocumentUsuarios($value)
    {
        //@codingStandardsIgnoreStart
            $doc = new Zend_Search_Lucene_Document();
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('idpostulante', $value["idpostulante"], "UTF-8")
            );
            $doc->addField(
                Zend_Search_Lucene_Field::UnIndexed('foto', $value["foto"], "UTF-8")
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'nombres', 
                    $this->sinAcento($this->ifnull($value["nombres"],"")),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'apellidos', 
                    $this->sinAcento($this->ifnull($value["apellidos"])),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'telefono', 
                    $this->ifnull($value["telefono"],""),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'celular', 
                    $this->ifnull($value["celular"],""),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::UnIndexed(
                        'slug',
                        $this->ifnull($value["slug"]),
                        "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'sexo',
                    $this->ifnull($value["sexo"]),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'edad',
                    $this->fillZeroField(
                        $this->ifnull($value["edad"],"0")
                    ),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'fecha_nac',
                    $this->fillZeroField($this->ifnull($value["fecha_nac"],"0")),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::UnIndexed(
                    'pathcv', 
                    $this->ifnull($value["path_cv"]),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'estudios',
                    $this->sinAcento($this->ifnull($value["estudios"])),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'estudiosclaves',
                    $this->ifnull($value["estudios_claves"]),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'carreraclaves', 
                    $this->ifnull($value["carrera_claves"]),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'experiencia',
                    $this->fillZeroField(
                        $this->SumaCadena($this->ifnull($value["experiencia"]), "-")
                    ),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'idiomas',
                    $this->ifnull($value["idiomas"]),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'programasclaves',
                    $this->ifnull($value["programas_claves"]),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'sexoclaves',
                    $this->ifnull($value["sexo_claves"]),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword(
                    'ubigeoclaves', 
                    $this->ifnull($value["ubigeo_claves"],"0"),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'ubigeo',
                    $this->sinAcento($this->ifnull($value["ubigeo"])), "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                   'empresa', 
                   $this->sinAcento($this->ifnull($value["empresa"])),
                   "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'puesto', 
                    $this->sinAcento($this->ifnull($value["puesto"])),
                    "UTF-8"
                )
            );

            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'area',
                    $this->ifnull($value["area"]),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'nivelpuesto',
                    $this->ifnull($value["nivel_puesto"]),
                    "UTF-8"
                )
            );

            try{
                $this->_indexpostulantes->addDocument($doc);
            } catch(Exception $ex) {
                var_dump($doc);
                var_dump($ex);
            }
       //@codingStandardsIgnoreEnd
    }

    function addDocumentUsuariosZL($value)
    {
        //@codingStandardsIgnoreStart
            $doc = new Zend_Search_Lucene_Document();
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('idpostulante', $value->idpostulante)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::UnIndexed('foto', $value->foto)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'nombres', $this->sinAcento($value->nombres), "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'apellidos', $this->sinAcento($value->apellidos), "UTF-8"
                )
            );
            $doc->addField(Zend_Search_Lucene_Field::Keyword('telefono', $value->telefono));
            $doc->addField(Zend_Search_Lucene_Field::UnIndexed('slug', $value->slug));
            $doc->addField(Zend_Search_Lucene_Field::Keyword('sexo', $value->sexo));
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('edad', $this->fillZeroField($value->edad))
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword(
                    'fecha_nac',
                    $this->fillZeroField($value->fecha_nac)
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::UnIndexed('pathcv', $value->pathcv)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'estudios', $this->sinAcento($value->estudios), "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('estudiosclaves', $value->estudiosclaves)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('carreraclaves', $value->carreraclaves)
            );
            
            $doc->addField(
                Zend_Search_Lucene_Field::Text(
                    'experiencia',
                    $this->fillZeroField($value->experiencia),
                    "UTF-8"
                )
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('idiomas', $value->idiomas)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('programasclaves', $value->programasclaves)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('sexoclaves', $value->sexoclaves)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Keyword('ubigeoclaves', $value->ubigeoclaves)
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('ubigeo', $this->sinAcento($value->ubigeo))
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('empresa', $this->sinAcento($value->empresa))
            );
            $doc->addField(
                Zend_Search_Lucene_Field::Text('puesto', $this->sinAcento($value->puesto))
            );
            
            $doc->addField(Zend_Search_Lucene_Field::Text('area', $this->ifnull($value->area)));

            $doc->addField(
                Zend_Search_Lucene_Field::Text('nivelpuesto', $this->ifnull($value->nivelpuesto))
            );

            $this->_indexpostulantes->addDocument($doc);
       //@codingStandardsIgnoreEnd
    }
    
    public function queryAvisos2($query)
    {
        /*
        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');
        $userQuery = Zend_Search_Lucene_Search_QueryParser::parse(utf8_encode($query));
        $query = new Zend_Search_Lucene_Search_Query_Boolean();
        $query->addSubquery($userQuery, true);
        */
        
        $result = @$this->_indexavisos->find($query);
        
        //var_dump($result);
        //exit;
        $resultAwIds = array();
        foreach ($result as $value) {
            //$d = $value->getDocument();
            //var_dump($d->getFieldNames());
            //var_dump($d->getField('awid')->value);
            //var_dump($d->getField('urlId')->value);
            //var_dump($d->getField('title')->value);
            $resultAwIds[] = (int) $value->getDocument()->getField('awid')->value;
        }
        
        return $resultAwIds;
    }

    public function queryAvisos($query)
    {
        
        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');
        //$result = @$this->_indexavisos->find($query);
        $userQuery = Zend_Search_Lucene_Search_QueryParser::parse($query);
        $query = new Zend_Search_Lucene_Search_Query_Boolean();
        $query->addSubquery($userQuery, true);
        
        
        $result = @$this->_indexavisos->find($query);
        
        $resultAwIds = array();
        $scores = array();
        foreach ($result as $value) {
            //$d = $value->getDocument();
            //var_dump($d);
            //var_dump($d->getFieldNames());
            //exit;
            //var_dump($d->getField('awid')->value);
            //var_dump($d->getField('urlId')->value);
            //var_dump($d->getField('title')->value);
            $awid = (int) $value->getDocument()->getField('awid')->value;
            $resultAwIds[] = $awid;
            $scores[$awid] = $value->score;
        }
        $sessionlucene = new Zend_Session_Namespace('lucene');
        $sessionlucene->scores = $scores;
        $sessionlucene->setExpirationHops(1, 'scores');
        return $resultAwIds;
    }
    
    function generarIndicePostulacionesxAviso($idAnuncioWeb)
    {
        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('UTF-8');
        $query = "idanuncioweb:".$idAnuncioWeb;
        //echo $query; exit;
        $q = Zend_Search_Lucene_Search_QueryParser::parse($query);
        Zend_Search_Lucene::setResultSetLimit(2000);
        $result = $this->_index->find(
            $q
        );
        $nresult = count($result);
        //var_dump($nresult); exit;
        if ($nresult==0) {
            $anuncio = new Application_Model_AnuncioWeb();
            $result = $anuncio->getRellenarIndexPostulacionesxAnuncio(null, null, $idAnuncioWeb);
            foreach ($result as $key=>$value) {
                try {
                    $this->addDocumentPostulantes($value);
                } catch(Zend_Search_Lucene_Document_Exception $ex) {
                    /*echo "Error Linea:(".$ex->getLine().") archivo:(".$ex->getFile().")
                          Agregando Postulacion a ZL: ".$ex->getMessage();*/
                }
            }
            unset($result);
            $this->_index->commit();
        }
    }
    
    function queryPostulantes($query, $order)
    {
        $aSort = explode(" ", $order[0]);
        $bSort = explode(" ", $order[1]);
        //Zend_Search_Lucene::setResultSetLimit(1000);
        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('UTF-8');
        
        $q = Zend_Search_Lucene_Search_QueryParser::parse($query);
        //echo $q; exit;
        $result = $this->_index->find(
            $q,
            $aSort[0],
            ($aSort[1]=="int"?SORT_NUMERIC:($aSort[1]=="string")?SORT_STRING:""),
            ($aSort[2]=="ASC"?SORT_ASC:SORT_DESC),
            $bSort[0],
            ($bSort[1]=="int"?SORT_NUMERIC:($bSort[1]=="string")?SORT_STRING:""),
            ($bSort[2]=="ASC"?SORT_ASC:SORT_DESC)
        );
        $data = "";
        if ($this->_index->count()) {
            $c = 0;
            foreach ($result as $item) {
                $data[$c]["id"] = $item->idanuncioweb;
                $data[$c]["idpostulante"] = $item->idpostulante;
                $data[$c]["idpostulacion"] = $item->idpostulacion;
                $data[$c]["foto"] = $item->foto;
                $data[$c]["nombres"] = $item->nombres;
                $data[$c]["apellidos"] = $item->apellidos;
                $data[$c]["telefono"] = $item->telefono;
                $data[$c]["celular"] = $item->celular;
                $data[$c]["slug"] = $item->slug;
                $data[$c]["score"] = $item->score;
                $data[$c]["msg_por_responder"] = (int)$item->msgporresponder;
                $data[$c]["sexo"] = $item->sexoclaves;
                $data[$c]["edad"] = (int)$item->edad;
                $data[$c]["match"] = (int)$item->match;
                $data[$c]["nivel_estudio"] = $item->nivelestudio;
                $data[$c]["path_cv"] = $item->pathcv;
                $data[$c]["msg_no_leidos"] = (int)$item->msgnoleidos;
                $data[$c]["msg_respondido"] = (int)$item->msgrespondido;
                $data[$c]["es_nuevo"] = (int)$item->esnuevo;
                $data[$c]["invitacion"] = (int)$item->invitacion;
                $data[$c]["referenciado"] = (int)$item->referenciado;
                $data[$c]["idcategoriapostulacion"] = $item->idcategoriapostulacion;
                $data[$c]["descartado"] = (int)$item->descartado;
                $data[$c]["estudios"] = (int)$item->estudios;
                $data[$c]["experiencia"] = $item->experiencia;
                $data[$c]["carrera"] = $item->carrerap;
                $data[$c]["idcategoriapostulacion"] = $item->idcategoriapostulacion;
                $data[$c]["online"] = $item->online;
                $data[$c]["estudios_claves"] = $item->estudiosclaves;
                $data[$c]["carreras_claves"] = $item->carreraclaves;
                $data[$c]["idiomas"] = $item->idiomas;
                $data[$c]["programasclaves"] = $item->programasclaves;
                $data[$c]["ubigeoclaves"] = $item->ubigeoclaves;
                $c++;
            }
        }
        return $data;
    }

    function queryUsuarios($query, $order)
    {
        if (!$query) return array();
        
        $aSort = explode(" ", $order[0]);
        $bSort = explode(" ", $order[1]);
        $limit = $this->_config->empresa->misprocesos->paginadobuscadorpostulantes;
        Zend_Search_Lucene::setResultSetLimit(250);
        //Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('UTF-8');
        $q = Zend_Search_Lucene_Search_QueryParser::parse($query);
        //var_dump($order); exit;
/*
 *
 * $result = $this->_indexpostulantes->find(
            $q,
            $aSort[0],
            ($aSort[1]=="int"?SORT_NUMERIC:
            ($aSort[1]=="string")?SORT_STRING:""),
            ($aSort[2]=="ASC"?SORT_ASC:SORT_DESC),
            $bSort[0],
            ($bSort[1]=="int"?SORT_NUMERIC:
            ($bSort[1]=="string")?SORT_STRING:""),
            ($bSort[2]=="ASC"?SORT_ASC:SORT_DESC)
        );
 */
        if ($aSort[0]=="nodefinido") {
            $result = $this->_indexpostulantes->find(
                $q
            );
        } else {
            $result = $this->_indexpostulantes->find(
                $q,
                $aSort[0],
                ($aSort[1]=="int"?SORT_NUMERIC:
                ($aSort[1]=="string")?SORT_STRING:""),
                ($aSort[2]=="ASC"?SORT_ASC:SORT_DESC)
            );
        }
        $data = "";
        if ($this->_indexpostulantes->count()) {
            $c = 0;
            foreach ($result as $index=>$item) {
                //var_dump($item); exit;
                //if ($index>$limit) break;
                $data[$c]["idpostulante"] = $item->idpostulante;
                $data[$c]["foto"] = $item->foto;
                $data[$c]["nombres"] = $item->nombres;
                $data[$c]["apellidos"] = $item->apellidos;
                $data[$c]["telefono"] = $item->telefono;
                $data[$c]["celular"] = $item->celular;
                $data[$c]["slug"] = $item->slug;
                $data[$c]["score"] = $item->score;
                $data[$c]["sexo"] = $item->sexoclaves;
                $data[$c]["edad"] = (int)$item->edad;
                $data[$c]["path_cv"] = $item->pathcv;
                $data[$c]["estudios"] = (int)$item->estudios;
                $data[$c]["experiencia"] = $item->experiencia;
                $data[$c]["estudios_claves"] = $item->estudiosclaves;
                $data[$c]["carreras_claves"] = $item->carreraclaves;
                $data[$c]["idiomas"] = $item->idiomas;
                $data[$c]["programasclaves"] = $item->programasclaves;
                $data[$c]["ubigeoclaves"] = $item->ubigeoclaves;
                $data[$c]["empresa"] = $item->empresa;
                $data[$c]["cargo"] = $item->puesto;
                
                $c++;
            }
        }
        return $data;
    }

    function getPath()
    {
        return $this->_ruta;
    }

    function getIndex()
    {
        return $this->_index;
    }

    function fillZeroField($field, $nzeros='')
    {
        $n = strlen($field);
        $newfield=$field;
        for($i=0;$i<(($nzeros==''?$this->_nzeros:$nzeros)-$n);$i++) $newfield="0".$newfield;
        return $newfield;
    }
    
    function SumaCadena($cadena, $separador)
    {
        $suma=0;
        $arreglo = explode($separador, $cadena);
        for ($i=0;$i<count($arreglo);$i++) {
            $suma+=$arreglo[$i];
        }
        return $suma;
    }
    
    function sinAcento($campo)
    {
        $campo = str_replace("á", "a", $campo);
        $campo = str_replace("é", "e", $campo);
        $campo = str_replace("í", "i", $campo);
        $campo = str_replace("ó", "o", $campo);
        $campo = str_replace("ú", "u", $campo);
        return $campo;
    }
    
    function ifnull($campo, $null="")
    {
        return ($campo==null)?$null: str_replace("'", "", str_replace("`", "", $campo));
    }
    
    
    function updateIndexPostulaciones(
        $idPostulacion, $campo, $valor, $replicar = true, $encolar = true
    )
    {
        if ($encolar && $this->_readOnly) {
            $this->_encolador->encolarElemento(
                'postulaciones', array($idPostulacion, $campo, $valor, false, false), __FUNCTION__
            );
            return true;
        } else {
            $fila = $this->_index->find("idpostulacion:".$idPostulacion);
            foreach ($fila as $hit) {
                $this->_index->delete($hit->id);
            }
            
            if ($fila != null && count($fila) > 0) {
                $fila[0]->$campo=$valor;
                $this->addDocumentPostulantesZL($fila[0]);
                $this->_index->commit();
                //$this->_index->optimize();
                
                $start = $this->getMicroTime();
                $delay = $this->_config->lucene->timeout;
                $fila = null;
                while ($fila == null || count($fila) <= 0) {
                    $fila = $this->_index->find("idpostulacion:".$idPostulacion);
                    $end = $this->getMicroTime();

                    if ($end > ($start + $delay)) {
                        $this->_logCron->log(
                            "Error al actualizar el postulante, idpostulacion: ".
                            $idPostulacion, Zend_Log::CRIT
                        );
                        break;
                    }
                }
            

                if ($replicar && $this->_wsMode) {
                    foreach ($this->_config->deploymentInstances as $instanceName => $ip) {
                        if ($ip !== $this->_config->deploymentCurrentInstance) {
                            $wsc = new Zend_Soap_Client("http://".$ip."/api?wsdl");
                            //var_dump($wsc->getWsdl());
                            //$wsc->setUri();
                            $wsc->actualizarDocumentosPostulaciones($idPostulacion, $campo, $valor);
                        }
                    }
                }
                
        
                return true;
            }
            
            return false;
        }
    }
    
    function updateIndexPostulacionesxidAnuncioWeb(
        $idAnuncioWeb, $campo, $valor, $replicar = true, $encolar = true
    )
    {
        if ($encolar && $this->_readOnly) {
            $this->_encolador->encolarElemento(
                'postulaciones', array($idAnuncioWeb, $campo, $valor, false, false), __FUNCTION__
            );
            return true;
        } else {
            $fila = $this->_index->find("idanuncioweb:".$idAnuncioWeb);
            if ($fila != null && count($fila) > 0) {
                foreach ($fila as $hit) {
                    $this->_index->delete($hit->id);
                    $hit->$campo=$valor;
                    $this->addDocumentPostulantesZL($hit);
                }

                $this->_index->commit();
                //$this->_index->optimize();
                
                $start = $this->getMicroTime();
                $delay = $this->_config->lucene->timeout;
                $fila = null;
                while ($fila == null || count($fila) <= 0) {
                    $fila = $this->_index->find("idanuncioweb:".$idAnuncioWeb);
                    $end = $this->getMicroTime();

                    if ($end > ($start + $delay)) {
                        $this->_logCron->log(
                            "Error al actualizar las postulaciones, idAnuncioWeb: ".
                            $idAnuncioWeb, Zend_Log::CRIT
                        );
                        break;
                    }
                }
                
                if ($replicar && $this->_wsMode) {
                    foreach ($this->_config->deploymentInstances as $instanceName => $ip) {
                        if ($ip !== $this->_config->deploymentCurrentInstance) {
                            $wsc = new Zend_Soap_Client("http://".$ip."/api?wsdl");
                            //var_dump($wsc->getWsdl());
                            //$wsc->setUri();
                            $wsc->actualizarDocumentosPostulacionesxidAnuncioWeb(
                                $idAnuncioWeb, $campo, $valor
                            );
                        }
                    }
                }
                
                return true;
            }
            
            return false;
        }
    }
    
    function updateIndexPostulacionesxidPostulante(
        $idPostulante, $campo, $valor, $replicar = true, $encolar = true
    )
    {
        if ($encolar && $this->_readOnly) {
            $this->_encolador->encolarElemento(
                'postulaciones', array($idPostulante, $campo, $valor, false, false), __FUNCTION__
            );
        } else {
            $fila = $this->_index->find("idpostulante:".$idPostulante);
            
            if ($fila != null && count($fila) > 0) {
                foreach ($fila as $hit) {
                    $this->_index->delete($hit->id);
                    $hit->$campo=$valor;
                    $this->addDocumentPostulantesZL($hit);
                }
                $this->_index->commit();
                //$this->_index->optimize();
                
                $start = $this->getMicroTime();
                $delay = $this->_config->lucene->timeout;
                $fila = null;
                while ($fila == null || count($fila) <= 0) {
                    $fila = $this->_index->find("idpostulante:".$idPostulante);
                    $end = $this->getMicroTime();

                    if ($end > ($start + $delay)) {
                        $this->_logCron->log(
                            "Error al actualizar las postulaciones, idpostulante: ".
                            $idPostulante, Zend_Log::CRIT
                        );
                        break;
                    }
                }
                
                if ($replicar && $this->_wsMode) {
                    foreach ( $this->_config->deploymentInstances as $instanceName => $ip) {
                        if ($ip !== $this->_config->deploymentCurrentInstance) {
                            $wsc = new Zend_Soap_Client("http://".$ip."/api?wsdl");
                            //var_dump($wsc->getWsdl());
                            //$wsc->setUri();
                            $wsc->actualizarDocumentosPostulacionesxidPostulante(
                                $idPostulante, $campo, $valor
                            );
                        }
                    }
                }
                
                return true;
            }
            
            return false;
        }
    }
    
    function insertarIndexPostulaciones($objPostulacion, $replicar = true, $encolar = true)
    {
        if ($encolar && $this->_readOnly) {
            $this->_encolador->encolarElemento(
                'postulaciones', array($objPostulacion, false, false), __FUNCTION__
            ); 
            return true;
        } else {
            $o = new stdClass();
            foreach ($objPostulacion as $key => $value) {
                $o->$key = $value;
            }
            $this->addDocumentPostulantesZL($o);
            $this->_index->commit();
            //$this->_index->optimize();
            
            $start = $this->getMicroTime();
            $delay = $this->_config->lucene->timeout;
            $fila = null;
            while ($fila == null || count($fila) <= 0) {
                $fila = $this->_index->find("idpostulacion:".$objPostulacion["idpostulacion"]);
                $end = $this->getMicroTime();

                if ($end > ($start + $delay)) {
                    $this->_logCron->log(
                        "Error al agregar postulacion, idpostulacion: "
                        .$objPostulacion["idpostulacion"], Zend_Log::CRIT
                    );
                    return false;
                    break;
                }
            }

            if ($replicar && $this->_wsMode) {
                foreach ($this->_config->deploymentInstances as $instanceName => $ip) {
                    if ($ip !== $this->_config->deploymentCurrentInstance) {
                        $wsc = new Zend_Soap_Client("http://".$ip."/api?wsdl");
                        //var_dump($wsc->getWsdl());
                        //$wsc->setUri();
                        $wsc->agregarDocumentoPostulacion($objPostulacion);
                    }
                }
            }

            return true;
        }
    }
    
    function duplicarIndexPostulaciones($idPostulacion, $valor, $replicar = true, $encolar = true)
    {
        if ($encolar && $this->_readOnly) {
            $this->_encolador->encolarElemento(
                'postulaciones', array($idPostulacion, $valor, false, false), __FUNCTION__
            ); 
            return true;
        } else {
            $fila = $this->_index->find("idpostulacion:".$idPostulacion);
            
            if ($fila != null && count($fila) > 0) {
                foreach ($valor as $key=>$item) {
                    $fila[0]->$key=$item;
                }
                $this->addDocumentPostulantesZL($fila[0]);
                $this->_index->commit();
                //$this->_index->optimize();
                
                $start = $this->getMicroTime();
                $delay = $this->_config->lucene->timeout;
                $fila = null;
                while ($fila == null || count($fila) <= 0) {
                    $fila = $this->_index->find("idpostulacion:".$idPostulacion);
                    $end = $this->getMicroTime();

                    if ($end > ($start + $delay)) {
                        $this->_logCron->log(
                            "Error al duplicar la postulacion, idpostulacion: ".
                            $idPostulacion, Zend_Log::CRIT
                        );
                        break;
                    }
                }

                if ($replicar && $this->_wsMode) {
                    foreach ($this->_config->deploymentInstances as $instanceName => $ip) {
                        if ($ip !== $this->_config->deploymentCurrentInstance) {
                            $wsc = new Zend_Soap_Client("http://".$ip."/api?wsdl");
                            //var_dump($wsc->getWsdl());
                            //$wsc->setUri();
                            $wsc->duplicarDocumentoPostulacion($idPostulacion, $valor);
                        }
                    }
                }
                
                return true;
            }

            return false;
        }
    }

    //Funciones para el Buscador de USUARIO ------------------------------
    function insertarIndexPostulante($objPostulante, $replicar = true, $encolar = true)
    {
        if ($encolar && $this->_readOnly) {
            if (!$this->_syncPostulantesMode) {
                $this->_encolador->encolarElemento(
                    'postulantes', array($objPostulante, false, false), __FUNCTION__
                );
            }
            return true;
        } else {
            $this->addDocumentUsuarios($objPostulante);
            $this->_indexpostulantes->commit();
            
            
            $start = $this->getMicroTime();
            $delay = $this->_config->lucene->timeout;
            $fila = null;
            while ($fila == null || count($fila) <= 0) {
                $fila = $this->_indexpostulantes->find(
                    "idpostulante:".$objPostulante["idpostulante"]
                );
                $end = $this->getMicroTime();
                
                if ($end > ($start + $delay)) {
                    $this->_logCron->log(
                        "Error al agregar el postulante, idpostulante: ".
                        $objPostulante["idpostulante"], Zend_Log::CRIT
                    );
                    return false;
                    break;
                }
            }
            
            //$this->_indexpostulantes->optimize();

            if ($replicar && $this->_wsMode) {
                foreach ($this->_config->deploymentInstances as $instanceName => $ip) {
                    if ($ip !== $this->_config->deploymentCurrentInstance) {
                        $wsc = new Zend_Soap_Client("http://".$ip."/api?wsdl");
                        //var_dump($wsc->getWsdl());
                        //$wsc->setUri();
                        $wsc->agregarDocumentoPostulante($objPostulante);
                    }
                }
            }

            return true;
        }
    }
    
    function updateIndexPostulante($idPostulante, $valores, $replicar = true, $encolar = true)
    {
        if ($encolar && $this->_readOnly) {
            if (!$this->_syncPostulantesMode) {
                $this->_encolador->encolarElemento(
                    'postulantes', array($idPostulante, $valores, false, false), __FUNCTION__
                );
            }
            return true;
        } else {
            $fila = $this->_indexpostulantes->find("idpostulante:".$idPostulante);
            foreach ($fila as $hit) {
                $this->_indexpostulantes->delete($hit->id);
            }

            if ($fila != null && count($fila) > 0) {
                foreach ($valores as $key=>$v) {
                    $fila[0]->$key=$v;
                }

                $this->addDocumentUsuariosZL($fila[0]);
                $this->_indexpostulantes->commit();
                
                $start = $this->getMicroTime();
                $delay = $this->_config->lucene->timeout;
                $fila = null;
                while ($fila == null || count($fila) <= 0) {
                    $fila = $this->_indexpostulantes->find("idpostulante:".$idPostulante);
                    $end = $this->getMicroTime();

                    if ($end > ($start + $delay)) {
                        $this->_logCron->log(
                            "Error al actualizar el postulante, idpostulante: ".
                            $objPostulante["idpostulante"], Zend_Log::CRIT
                        );
                        break;
                    }
                }
            
                // $this->_indexpostulantes->optimize();

                if ($replicar && $this->_wsMode) {
                    foreach ($this->_config->deploymentInstances as $instanceName => $ip) {
                        if ($ip !== $this->_config->deploymentCurrentInstance) {
                            $wsc = new Zend_Soap_Client("http://".$ip."/api?wsdl");
                            //var_dump($wsc->getWsdl());
                            //$wsc->setUri();
                            $wsc->actualizarDocumentoPostulante($idPostulante, $valores);
                        }
                    }
                }
                
                return true;
            }
           
            return false;
        }
    }
    
    function hasIndexPostulante($idPostulante)
    {
        $fila = $this->_indexpostulantes->find("idpostulante:".$idPostulante);
        if (count($fila) > 0) {
            return true;
        }
        return false;
    }
    
    function deleteIndexPostulante($idPostulante)
    {
        $fila = $this->_indexpostulantes->find("idpostulante:".$idPostulante);
        foreach ($fila as $hit) {
            $this->_indexpostulantes->delete($hit->id);
        }
    }
    
    function commitIndexes($n)
    {
        echo "Commit a indice de ".$n."....".PHP_EOL;
        try {
            switch ($n) {
                case "postulaciones":
                    $this->_index->commit();
                    break;
                case "postulantes":
                    $this->_indexpostulantes->commit();
                    break;
                case "avisos":
                    $this->_indexavisos->commit();
                    break;
            }
        } catch (Exception $e) {
            echo $e->getMessage().PHP_EOL;
            echo $e->getTraceAsString().PHP_EOL;
        }
        echo "Se hizo commit al indice de ".$n."........... [OK]".PHP_EOL;
    }
    
    function commitAndOptimize_Indexes($n, $lastId)
    {
        $tiempo = time() + 0;
        echo "Commiteando Indice de ".$n."....".PHP_EOL;
        try {
            switch ($n) {
                case "postulaciones":
                    $this->_index->commit();
                    //$this->_index->optimize();
                    break;
                case "postulantes":
                    echo microtime();
                    $this->_indexpostulantes->commit();
                    $i = 0;
                    while ($this->hasIndexPostulante($lastId) == false) {
                        $i++;
                    }
                    echo $i.PHP_EOL;
                    echo microtime();
                    echo (time() - $tiempo).PHP_EOL;
                    echo "Optimizando Indice de ".$n."....".PHP_EOL;
                    $this->_indexpostulantes->optimize();
                    echo "Termino optimize".PHP_EOL;
                    break;
                case "avisos":
                    $this->_indexavisos->commit();
                    //$this->_indexavisos->optimize();
                    break;
            }
        } catch (Exception $e) {
            echo $e->getMessage().PHP_EOL;
            echo $e->getTraceAsString().PHP_EOL;
        }
        echo "Indice de ".$n." ha sido Commiteado y Optimizado ........... [OK]".PHP_EOL;
    }
    
    function getMicroTime()
    {
        $mt = explode(' ', microtime());
        return $mt[0] + $mt[1];
    }
}