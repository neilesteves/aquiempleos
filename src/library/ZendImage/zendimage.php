<?php
/*
 * Libreria ZendImage para todo lo que tenga que ver con Imagenes xD
 * Author: Solman Vaisman Gonzalez.
 * Email: Solman28@hotmail.com
 */
class ZendImage
{

    var $image;
    var $type;
    var $width;
    var $height;

    var $name;

    //---Método de leer la imagen
    function loadImage($name)
    {
        $info = @getimagesize($name);
        $this->width = $info[0];
        $this->height = $info[1];
        $this->type = $info[2];
        $this->name = $name;

        // exif data
        switch($this->type)
        {
            case IMAGETYPE_JPEG:
                $this->image = imagecreatefromjpeg($name);
                break;
            case IMAGETYPE_GIF:
                $this->image = imagecreatefromgif($name);
                break;
            case IMAGETYPE_PNG:
                $this->image = imagecreatefrompng($name);
                break;
        }
    }

    function fixImageOrientation()
    {
        if( function_exists('exif_read_data') )
        {
            $exif = @exif_read_data($this->name);

            if (!empty($exif['Orientation']))
            {
                switch ($exif['Orientation'])
                {
                    case 3:
                        $this->image = imagerotate($this->image, 180, 0);
                        break;

                    case 6:
                        $this->image = imagerotate($this->image, -90, 0);
                        break;

                    case 8:
                        $this->image = imagerotate($this->image, 90, 0);
                        break;
                }
            }
        }
    }

    //---Método de guardar la imagen
    function save($name, $quality = 100)
    {
        $this->fixImageOrientation();

        switch ($this->type) {
        case IMAGETYPE_JPEG:
            imagejpeg($this->image, $name, $quality);
            break;
        case IMAGETYPE_GIF:
            imagegif($this->image, $name);
            break;
        case IMAGETYPE_PNG:
            $pngquality = floor(($quality - 10) / 10);
            imagepng($this->image, $name, $pngquality);
            break;
        }
    }

    //---Método de mostrar la imagen sin salvarla
    function show()
    {
        switch($this->type){
        case IMAGETYPE_JPEG:
            imagejpeg($this->image);
            break;
        case IMAGETYPE_GIF:
            imagegif($this->image);
            break;
        case IMAGETYPE_PNG:
            imagepng($this->image);
            break;
        }
    }

    //---Método de redimensionar la imagen sin deformarla
    function resize($value, $prop)
    {
        $propValue = ($prop == 'width') ? $this->width : $this->height;
        $propVersus = ($prop == 'width') ? $this->height : $this->width;

        $pcent = $value / $propValue;
        $valueVersus = $propVersus * $pcent;
        $image = ($prop == 'width') ? imagecreatetruecolor($value, $valueVersus) :
                imagecreatetruecolor($valueVersus, $value);

        //=== inicio procesar png transparente ===//
//        imageAntiAlias($image,true);
        imagealphablending($image, false);
        imagesavealpha($image,true);
        $transparent = imagecolorallocatealpha($image, 255, 255, 255, 0);
        if ($prop == 'width') {
            $valueX = $value;
            $valueY = $valueVersus;
        } else {
            $valueX = $valueVersus;
            $valueY = $value;
        }
        for($x=0;$x<$valueX;$x++) {
          for($y=0;$y<$valueY;$y++) {
            imageSetPixel( $image, $x, $y, $transparent );
          }
        }
        //=== fin procesar png transparente ===//

        switch ($prop) {
            case 'width':
                imagecopyresampled(
                    $image, $this->image, 0, 0, 0, 0, $value, $valueVersus, $this->width,
                    $this->height
                );
                break;

            case 'height':
                imagecopyresampled(
                    $image, $this->image, 0, 0, 0, 0, $valueVersus, $value, $this->width,
                    $this->height
                );
                break;
        }

        //---Actualizar la imagen y sus dimensiones

        $this->width = imagesx($image);
        $this->height = imagesy($image);
        $this->image = $image;
    }

    //--Método extendido de extraer una sección de la imagen sin deformarla
    function cropExtended( $x1, $y1, $x2, $y2 )
    {
        if ($x2 < $x1) {
          list($x1, $x2) = array($x2, $x1);
        }
        if ($y2 < $y1) {
          list($y1, $y2) = array($y2, $y1);
        }

        $crop_width = $x2 - $x1;
        $crop_height = $y2 - $y1;

        // Perform crop
        $image = imagecreatetruecolor($crop_width, $crop_height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagecopyresampled($image, $this->image, 0, 0, $x1, $y1, $crop_width, $crop_height, $crop_width, $crop_height);

        $this->image = $image;
    }


    //---Método de extraer una sección de la imagen sin deformarla
    function crop($cwidth, $cheight, $pos = 'center')
    {
        if ($cwidth > $cheight) {
            $this->resize($cwidth, 'width');
        } else {
            $this->resize($cheight, 'height');
        }
        $image = imagecreatetruecolor($cwidth, $cheight);
        switch($pos) {
            case 'center':
            imagecopyresampled(
                $image, $this->image, 0, 0, abs(($this->width - $cwidth) / 2),
                abs(($this->height - $cheight) / 2), $cwidth, $cheight, $cwidth, $cheight
            );
                break;

            case 'left':
            imagecopyresampled(
                $image, $this->image, 0, 0, 0, abs(($this->height - $cheight) / 2),
                $cwidth, $cheight, $cwidth, $cheight
            );
                break;

            case 'right':
            imagecopyresampled(
                $image, $this->image, 0, 0,
                $this->width - $cwidth, abs(($this->height - $cheight) / 2),
                $cwidth, $cheight, $cwidth, $cheight
            );
                break;

            case 'top':
            imagecopyresampled(
                $image, $this->image, 0, 0, abs(($this->width - $cwidth) / 2), 0, $cwidth,
                $cheight, $cwidth, $cheight
            );
                break;

            case 'bottom':
            imagecopyresampled(
                $image, $this->image, 0, 0, abs(($this->width - $cwidth) / 2),
                $this->height - $cheight, $cwidth, $cheight, $cwidth, $cheight
            );
                break;
        }
        $this->image = $image;
    }

}
