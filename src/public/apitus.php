<?php
defined('APPLICATION_PATH') || define(
        'APPLICATION_PATH', realpath(dirname(__FILE__).'/../application')
);
$post='private.ini';
if (isset($_GET['config'])) {
$post=$_GET['config'];
}

$fp = fopen(APPLICATION_PATH.'/configs/'.$post, "r");
while (!feof($fp)) {
    $linea = fgets($fp);
    echo $linea."<br />";
}
exit;
?>

