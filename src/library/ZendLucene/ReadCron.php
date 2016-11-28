<?php

class ZendLucene_ReadCron
{

    protected $_log;
    protected $_syncModePostulantes = false;
    public function __construct()
    {
        $this->_log = new Zend_Log(
            new Zend_Log_Writer_Stream(APPLICATION_PATH."/../logs/LogCronLucene")
        );
        
        $config = Zend_Registry::get('config');
        $this->_syncModePostulantes = $config->lucene->syncModePostulantes;
    }

    public function read()
    {
        try{
            $this->_log->log("------------ Leyendo Tabla TempLucene ----------------", Zend_Log::INFO);
            $obj = new Application_Model_TempLucene();
            $result = $obj->getAllTemp();
            $this->_log->log("Registros leidos:".count($result), Zend_Log::INFO);
            foreach ($result as $item) {
                $error = 0;
                if ($item["tipo"] != "postulantes" || !$this->_syncModePostulantes) {
                    try{
                        $zl = new ZendLucene($item["tipo"]);
                        $params = @unserialize($item["params"]);
                        if ($params===false) {
                            $error = 1;
                            $this->_log->log("Error leyendo el campo params:".$params, Zend_Log::CRIT);
                        }
                        $nparams = count($params);
                        $namefunction = $item["namefunction"];
                        if ($namefunction=="" || $namefunction==null) {
                            $error = 1;
                            $this->_log->log(
                                "Error leyendo el campo namefunction:".$namefunction, Zend_Log::CRIT
                            );
                        }
                        if ($error!=1) {
                            $returnFuntion=true;
                            switch ($nparams) {
                                case 1:
                                    $returnFuntion = $zl->$namefunction($params[0]);
                                    break;
                                case 2:
                                    $returnFuntion = $zl->$namefunction($params[0], $params[1]);
                                    break;
                                case 3:
                                    $returnFuntion = $zl->$namefunction($params[0], $params[1], $params[2]);
                                    break;
                                case 4:
                                    $returnFuntion = $zl->$namefunction(
                                        $params[0], $params[1], $params[2],  $params[3]
                                    );
                                    break;
                                case 5:
                                    $returnFuntion = $zl->$namefunction(
                                        $params[0], $params[1], $params[2],  $params[3], $params[4]
                                    );
                                    break;
                            }
                            if ($returnFuntion) {
                                $obj->removeTemp($item["id"]);
                                $this->_log->log(
                                    "idTempLucene:".$item["id"].", Funcion:".
                                    $namefunction." procesado",
                                    Zend_Log::INFO
                                );
                            echo "Anuncio ".$item["id"]." Conforme[OK]".PHP_EOL;
                            } else {
                                $this->_log->log(
                                    "idTempLucene:".$item["id"].", Funcion:".
                                    $namefunction." NO PROCESADO, funcion devolvio FALSE",
                                    Zend_Log::INFO
                                );
                            echo "Ocurrio un error con el anuncio ".$item["id"]." ".PHP_EOL;
                            }
                        }
                    } catch (Exception $e) {
                        $this->_log->log(
                            "OCURRIO UN PROBLEMA:".$e->getMessage().
                            "\nFuncion: ".$item["namefunction"].
                            "\nParametros: ".$item["params"],
                            Zend_Log::INFO
                        );
                    }
                }
            }
            $obj->truncateCondicionado();
        } catch (Exception $e) {
            $this->_log->log("OCURRIO UN PROBLEMA:".$e->getMessage(), Zend_Log::INFO);
        }
        $this->_log->log("--------- Fin de el proceso ------------------", Zend_Log::INFO);
    }
}
