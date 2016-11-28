<?php

use Aws\S3\S3Client;
use Aws\S3\Enum\CannedAcl as Permission;

class Amazon_S3_S3ClientBase
{
   /**
    * Bucket Name
    * @var string
    */
   protected $bucket;

   /**
    * Endpoint Bucket
    * @var String
    */
   protected $cdn;

   /**
    * Folder donde almacenar los objetos
    * @var String
    */
   protected $folder;

   /**
    * Client s3
    * @var \Aws\S3\S3Client
    */
   protected $client;

   /**
    * Custom error
    * @var String
    */
   protected $error;
   /**
    *Custom log
    * @var type 
    */
   protected $log;


   public function __construct()
   {
      $client = $this->getConfig()->s3->client;
      $this->log=  $this->getLog();
      $this->client  = S3Client::factory( $client->toArray() );
   }


   /**
    * Return config application
    * @return mixed
    */
   public static function getConfig()
   {
      return Zend_Registry::get('config');
   }

   /**
    * Retornar nombre del bucket
    * @return string
    */
   public static function getBucket()
   {
      return self::getConfig()->s3->app->bucket;
   }

   /**
    * Obtener el nombre del fichero ( incluyendo el directorio donde se encuentra )
    * @param string $key
    * @return string
    */
   public function getName( $key)
   {
      if( $this->folder ) {
          $key = rtrim( $this->folder, '/') . '/'.$key;
      }
      return $key;
   }

   /**
    * Establecer folder donde se guardara
    * @param string $folder
    */
   public function setFolder( $folder )
   {
      $this->folder = $folder;
   }


   /**
    * Upload File to Bucket S3
    * @param  string $name  Nombre del archivo
    * @param  string $path  Ruta absoluta del archivo
    * @param  array  $metadata Datos adicionales del archivo
    * @return array|boolean retorna datos del objeto subido, o falso en caso contrario
    */
   public function uploadFile( $name, $path, $opts = array() )
   {
      try
      {
        $result = $this->client->putObject(
           array_merge(
            array(
            'Bucket' => $this->getBucket(),
            'Key'   => $this->getName( $name ),
            'SourceFile'  => $path,
            'ACL'=>  Permission::PUBLIC_READ
           ), $opts )
        );
        return $result;

      }catch(Exception $ex) {
        $this->error = $ex->getMessage();
        $this->log->info($ex->getMessage().'. '.$ex->getTraceAsString(),Zend_Log::ERR);
        return FALSE;
      }
   }

   /**
    * Retornar error
    * @return String
    */
   public function getError() {
      return $this->error;
   }
   public function deleteFile( $key ) 
   {
     try { 
       $result = $this->client->deleteObjects(          
          array(
            'Bucket'  => $this->getBucket(),
            'Objects' => array(
                array('Key' => $key[0]),
                array('Key' => $key[1]),
                array('Key' => $key[2])
            )
          )
       );
       return $result;
     } catch (Exception $ex) {
       $this->log->info($ex->getMessage().'. '.$ex->getTraceAsString(),Zend_Log::ERR);
       $this->error = $ex->getMessage();
       return false;
     }
   
   }
   /**
     * Retorna el objeto Zend_Log de la aplicación
     *
     * @return Zend_Log
     */
    public function getLog()
    {
        return Zend_Registry::get('log');
    }
}
?>
