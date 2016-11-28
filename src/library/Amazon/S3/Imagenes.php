<?php

class Amazon_S3_Imagenes extends Amazon_S3_S3ClientBase
{
   /**
    * Nivel de calidad de imagen bajo
    * 35/100
    */
   const LOW_QUALITY = 35;

   /**
    * Nivel de calidad de imagen alta
    * 75/100
    */
   const HIGHT_QUALITY = 70;



   /**
    * Subir imagen a s3
    * @param  Zend_Form_Element $element Elemento del formulario
    * @param  int $id    Identificador de la imagen
    * @param  array  $opts  Opciones de subida ( Por ejemplo los puntos a recortar de la imagen )
    * @return array|false Array con las rutas de la imagen o falso si ocurrio un error
    */
   function uploadImgEmpresa( $element, $id,  $opts = array() )
   {
       $config =  $this->getConfig();
       $this->setFolder( $config->s3->app->elementsBanners );
       $file = $element->getFileName();
       $keyImgS3=$opts['key'];
       if( $file || !empty($keyImgS3) )
       {              
         $element->receive();
         $infoFile = pathinfo($file);
         $salt = 'aptitus';
         if (!empty($keyImgS3) ) {
            $infoFile = pathinfo($config->s3->app->elementsBannersUrl.$keyImgS3);       
            if(copy($config->s3->app->elementsBannersUrl.$keyImgS3, $config->urls->app->elementsLogosRoot.$keyImgS3)) {
               $file=$config->urls->app->elementsLogosRoot.$keyImgS3;
            }
         }

         $fileExt  = $infoFile['extension'];

        
         $key  =  md5( microtime() . $salt . $id );

         // Original image
         $nameFile = sprintf(
          'ProfileEmpresa-%s_%s.%s', $key,  $fileExt, $fileExt
         );
         $paths = array();
      
         $upload = $this->uploadFile( $nameFile, $file );
         if( $upload ) {
            $paths['original'] = $nameFile;
         }

         // Cropped image
         if( $opts['process'] == 'crop' )
         {
            $utilImg = new ZendImage();

            $width  = abs( $opts['x1'] - $opts['x0'] );
            $height = abs( $opts['y1'] - $opts['y0'] );

            // calidad Normal
            $nameFileCropNormal = sprintf(
             'ProfileEmpresa-%s_%s_x_%s_%s_default_hd.%s', $key, $width, $height, $fileExt, $fileExt
            );

            $utilImg->loadImage( $file );
            $utilImg->cropExtended( $opts['x0'], $opts['y0'], $opts['x1'], $opts['y1'] );
            $utilImg->save( $file , self::HIGHT_QUALITY );

            $uploadCrop = $this->uploadFile( $nameFileCropNormal, $file );
            if( $uploadCrop ) {
               $paths['normal'] = $nameFileCropNormal;
            }

            // calidad baja
            $nameFileCropDefault= sprintf(
             'ProfileEmpresa-%s_%s_x_%s_%s_default.%s', $key, $width, $height, $fileExt, $fileExt
            );

            $utilImg->loadImage( $file );
            $utilImg->save( $file, self::LOW_QUALITY );

            $uploadCrop = $this->uploadFile( $nameFileCropDefault, $file );
            if( $uploadCrop ) {
               $paths['default'] = $nameFileCropDefault;
            }
         }

         @unlink($file);

       }
       return $paths;
   }

}
?>