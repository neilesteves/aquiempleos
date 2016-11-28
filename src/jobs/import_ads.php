<?php

include '_init_.php';

$opts = array(
    'verbose|v' => "Muestra el detalle de las operaciones en pantalla.",
    'date|d-s' => 'Fecha de la que se importarán avisos. Formato yyyy-mm-dd',
    'module|m-s' => 'Módulo a importar E=Aptitus, T=Talán. Se importan ambos si no se indica.',
    'rango|r-r' => 'validacion.'
);
$opt = new Zend_Console_Getopt($opts);
$date = $opt->getOption('d');

if (!$date) {
    $date = date('Y-m-d');
}

$db = Zend_registry::get('db');
$config = Zend_Registry::get('config');

$options = array();
if ( isset($config->adecsys->proxy->enabled) && $config->adecsys->proxy->enabled) {
    $options = $config->adecsys->proxy->param->toArray();
}

$logName = sprintf('%s/../logs/jobs/import-%s.txt', APPLICATION_PATH, $date);
$log = new Zend_Log(new Zend_Log_Writer_Stream($logName, 'a'));

if ($opt->getOption('v')) {
    $log->addWriter(new Zend_Log_Writer_Stream('php://output'));
}

try {
    $log->info("WSDL: " . $config->adecsys->wsdl);

    $ws = new Adecsys_Wrapper($config->adecsys->wsdl, $options);
    $adecsys = new Aptitus_Adecsys($ws, $db);
    $adecsys->setLog($log);
    $modulo = $opt->getOption('m');
    if ($opt->getOption('r')=='validar') {
        $adecsys->_isValidAvisos($date, $modulo);exit;
    } 
    if (in_array($modulo, array(Aptitus_Adecsys::MOD_APTITUS, Aptitus_Adecsys::MOD_TALAN))) {
        $adecsys->importarAvisos($date, $modulo);
    } else {
        $adecsys->importarAvisos($date, Aptitus_Adecsys::MOD_APTITUS);
        $adecsys->importarAvisos($date, Aptitus_Adecsys::MOD_TALAN);
    }

} catch (Exception $ex) {
        var_dump($ex->getMessage());exit;

    $log->err($ex->getMessage());
    $log->err($ex);
    
}
