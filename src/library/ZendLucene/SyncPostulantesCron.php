<?php

class ZendLucene_SyncPostulantesCron
{

    protected $_log;
    protected $_syncModePostulantes = false;
    protected $_nSyncRows = 100;
    
    public function __construct()
    {
        $this->_log = new Zend_Log(
            new Zend_Log_Writer_Stream(APPLICATION_PATH."/../logs/LogSyncPostulantesCronLucene")
        );
        
        $config = Zend_Registry::get('config');
        $this->_syncModePostulantes = $config->lucene->syncModePostulantes;
    }

    public function syncPostulantes()
    {
        $tiempo = time() + 0;
        //echo time().PHP_EOL;// return;
        $this->_log->log(
            "--------- Inicia proceso de sincronizacion de postulantes ".date('d-m-Y H:i:s').
            " ------------------", Zend_Log::INFO
        );
        if ($this->_syncModePostulantes) {
            try{
                $this->_log->log(
                    "------------ Leyendo Postulantes Desincronizados ----------------", 
                    Zend_Log::INFO
                );
                $obj = new Application_Model_Postulante();
                $result = $obj->getPostulantesDesincronizados($this->_nSyncRows);
                $this->_log->log("Registros leidos:".count($result), Zend_Log::INFO);
                $zl = new ZendLucene('postulantes');
                $idsSync = array();
                
                while (is_array($result) && count($result) > 0) {
                    foreach ($result as $item) {
                        try {
                            if ($item["accion"] == "update") {
                                $zl->deleteIndexPostulante($item["idpostulante"]);
                            }
                            $zl->addDocumentUsuarios($item);
                            $idsSync[] = $item["idpostulante"];
                            //echo "Agregado: ".$item["idpostulante"]." edad: ".$item["edad"].PHP_EOL;
                        } catch(Zend_Search_Lucene_Document_Exception $ex) {
                            $this->_log->log(
                                "Error Linea:(".$ex->getLine().") archivo:(".$ex->getFile().") ".
                                "Agregando Postulacion a ZL: ".$ex->getMessage(), 
                                Zend_Log::ERR
                            );
                        }
                    }
                    
                    try {
                        $obj->sincronizaPostulante($idsSync);
                        //echo "Sincronizados: ".count($idsSync).PHP_EOL;
                        $idsSync = array();
                    } catch (Exception $e) {
                        $this->_log->log(
                            "ERROR AL SINCRONIZAR POSTULANTES".
                            " =>".$e->getMessage(), 
                            Zend_Log::INFO
                        );
                    }
                    
                    $result = $obj->getPostulantesDesincronizados($this->_nSyncRows);
                }
                
               //echo (time() - $tiempo).PHP_EOL;
               $zl->commitIndexes("postulantes");
               //echo (time() - $tiempo).PHP_EOL;
            } catch (Exception $e) {
                $this->_log->log("OCURRIO UN PROBLEMA:".$e->getMessage(), Zend_Log::INFO);
            }
        }
        //echo (time() - $tiempo).PHP_EOL;
        $this->_log->log("--------- Fin de el proceso ------------------", Zend_Log::INFO);
    }
}
