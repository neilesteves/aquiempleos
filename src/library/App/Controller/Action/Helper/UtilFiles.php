<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of UtilFiles
 *
 * @author Solman Vaisman
 */
class App_Controller_Action_Helper_UtilFiles extends Zend_Controller_Action_Helper_Abstract
{

    public function _creaPathFoto($ext, $ancho, $alto, $calidad)
    {
        $salt = 'aptitus';
        $microTime = microtime();
        $rename = "ProfessionalProfile-" . rand(1000, 9999) . "-" .
            md5($microTime . $salt) . "_" . $ext;
        $nombrefinal = $rename . "_" . $ancho . "x" . $alto . "_q" .
            $calidad . "." . $ext;
        return $nombrefinal;
    }

    public function _creaPathFotoEmpresa($ext, $ancho, $alto, $calidad)
    {
        $salt = 'aptitus';
        $microTime = microtime();
        $rename = "Company-" . rand(1000, 9999) . "-" .
            md5($microTime . $salt) . "_" . $ext;
        $nombrefinal = $rename . "_" . $ancho . "x" . $alto . "_q" .
            $calidad . "." . $ext;
        return $nombrefinal;
    }

    /*
     * Superfuncion que devuelve un arreglo de tres posiciones con los nombres de las
     * imagenes subidas en distintos tamaños, siempre y cuando el tercer parametro
     * sea "image" si fuese otra cosa lo que hara es subir un curriculo y devuelve
     * el nombre generado de ese curriculo.
     */

    public function _renameFile(Zend_Form $form, $path, $auth = "image")
    {
        $file = $form->$path->getFileName();
        $nuevoNombre = '';
        if ($file != null) {
            $microTime = microtime();
            $salt = 'aptitus';
            $nombreOriginal = pathinfo($file);
            $rename = "";

            $config = Zend_Registry::get("config");

            if ($auth == "image") {
                $rename = "imgimpreso-" . rand(1000, 9999) . "-" .
                    md5($microTime . $salt) . "_" . $nombreOriginal['extension'];

                $nuevoNombre = $rename . "." . $nombreOriginal['extension'];
                $form->$path->addFilter('Rename', $nuevoNombre);
                $form->$path->receive();

                $ancho = $config->fotousuario->tamano->micuenta->w;
                $alto = $config->fotousuario->tamano->micuenta->h;
                $calidad = $config->fotousuario->calidadcompresion;

                $nombrefinal = $this->_creaPathFoto(
                    $nombreOriginal['extension'], $ancho, $alto, $calidad
                );

                $this->_redimensionar_jpeg(
                    $config->urls->app->elementsImgRoot . $nuevoNombre,
                    $config->urls->app->elementsImgRoot .
                    $nombrefinal, $ancho, $alto, $calidad
                );
                $ancho = $config->fotousuario->tamano->perfil->w;
                $alto = $config->fotousuario->tamano->perfil->h;
                $nombrefinalDos = $this->_creaPathFoto(
                    $nombreOriginal['extension'], $ancho, $alto, $calidad
                );
                $this->_redimensionar_jpeg(
                    $config->urls->app->elementsImgRoot . $nuevoNombre,
                    $config->urls->app->elementsImgRoot . "/" .
                    $nombrefinalDos, $ancho, $alto, $calidad
                );
                $ancho = $config->fotousuario->tamano->empresa->w;
                $alto = $config->fotousuario->tamano->empresa->h;
                $nombrefinalTres = $this->_creaPathFoto(
                    $nombreOriginal['extension'], $ancho, $alto, $calidad
                );
                $this->_redimensionar_jpeg(
                    $config->urls->app->elementsImgRoot . $nuevoNombre,
                    $config->urls->app->elementsImgRoot . "/" .
                    $nombrefinalTres, $ancho, $alto, $calidad
                );
                unlink($config->urls->app->elementsImgRoot . $nuevoNombre);
                return array($nombrefinal, $nombrefinalDos, $nombrefinalTres);
            } else {
                if ($auth == "image-empresa") {
                    $rename = "ProfessionalProfile-" . rand(1000, 9999) . "-" .
                        md5($microTime . $salt) . "_" . $nombreOriginal['extension'];

                    $nuevoNombre = $rename . "." . $nombreOriginal['extension'];
                    $form->$path->addFilter('Rename', $nuevoNombre);
                    $form->$path->receive();

                    $ancho = $config->fotoempresa->tamano->empresalogo->w;
                    $alto = $config->fotoempresa->tamano->empresalogo->h;

                    $calidad = $config->fotoempresa->calidadcompresion;

                    $nombrefinal = $this->_creaPathFotoEmpresa(
                        $nombreOriginal['extension'], $ancho, $alto, $calidad
                    );

                    $this->_redimensionar_jpeg(
                        $config->urls->app->elementsLogosRoot . $nuevoNombre,
                        $config->urls->app->elementsLogosRoot .
                        $nombrefinal, $ancho, $alto, $calidad
                    );

                    $ancho = $config->fotoempresa->tamano->empresalogo->w;
                    $alto = $config->fotoempresa->tamano->empresalogo->h;

                    $nombrefinalDos = $this->_creaPathFotoEmpresa(
                        $nombreOriginal['extension'], $ancho, $alto, $calidad
                    );

                    $this->_redimensionar_jpeg(
                        $config->urls->app->elementsLogosRoot . $nuevoNombre,
                        $config->urls->app->elementsLogosRoot . "/" .
                        $nombrefinalDos, $ancho, $alto, $calidad
                    );

                    $ancho = $config->fotoempresa->tamano->empresalogo->w;
                    $alto = $config->fotoempresa->tamano->empresalogo->h;


                    $nombrefinalTres = $this->_creaPathFotoEmpresa(
                        $nombreOriginal['extension'], $ancho, $alto, $calidad
                    );

                    $this->_redimensionar_jpeg(
                        $config->urls->app->elementsLogosRoot . $nuevoNombre,
                        $config->urls->app->elementsLogosRoot . "/" .
                        $nombrefinalTres, $ancho, $alto, $calidad
                    );

                    $ancho = $config->fotoempresa->tamano->facebook->w;
                    $alto = $config->fotoempresa->tamano->facebook->h;

                    $nombrefinalCuatro = $this->_creaPathFotoEmpresa(
                        $nombreOriginal['extension'], $ancho, $alto, $calidad
                    );
                    $this->_redimensionar_jpeg(
                        $config->urls->app->elementsLogosRoot . $nuevoNombre,
                        $config->urls->app->elementsLogosRoot . "/" .
                        $nombrefinalCuatro, $ancho, $alto, $calidad
                    );

                    unlink($config->urls->app->elementsLogosRoot . $nuevoNombre);
                    return array($nombrefinal, $nombrefinalDos, $nombrefinalTres,
                        $nombrefinalCuatro);
                } else {
                    $rename = "APTITUS_" . str_replace(' ', '_', $auth["postulante"]["nombres"])
//                        . "_" . str_replace(' ', '_', $auth["postulante"]["apellidos"])
                        . "_" . str_replace(' ', '_', $auth["postulante"]["apellido_paterno"])
                        . "_" . str_replace(' ', '_', $auth["postulante"]["apellido_materno"])
                        . "_" . rand(1000, 9999) . "." . $nombreOriginal['extension'];

                    $nuevoNombre = $rename;
                    $form->$path->addFilter('Rename', $nuevoNombre);
                    $form->$path->receive();
                }
            }
        }
        return $nuevoNombre;
    }
   public function _renameFileImg(Zend_Form $form, $path, $auth = "impreso")
    {
        $file = $form->$path->getFileName();
        $nuevoNombre = '';
        if ($file != null) {
            $microTime = microtime();
            $salt = 'aptitus';
            $nombreOriginal = pathinfo($file);
            $rename = "";

            $config = Zend_Registry::get("config");

            if ($auth == "impreso") {
                $rename = "ProfessionalProfile-" . rand(1000, 9999) . "-" .
                    md5($microTime . $salt) . "_" . $nombreOriginal['extension'];

                $nuevoNombre = $rename . "." . $nombreOriginal['extension'];
                $form->$path->addFilter('Rename', $nuevoNombre);
                $form->$path->receive();
                return $nuevoNombre; 
            }
        }
        return $nuevoNombre;
    }
    public function _devuelveExtension($filename)
    {
        $filename = strtolower($filename);
        $exts = @split("[/\\.]", $filename);
        $n = count($exts) - 1;
        $exts = $exts[$n];
        return $exts;
    }

    /* ----------------------------------------
     * Funcion que redimensiona una imagen
     * --------------------------------------- */

    public function _redimensionar_jpeg($imgOriginal, $imgNueva,
        $imgNuevaAnchura, $imgNuevaAltura, $imgNuevaCalidad)
    {
        $img = new ZendImage();
        $img->loadImage($imgOriginal);

        if ($img->width > $img->height) $img->resize($imgNuevaAnchura, 'width');
        else $img->resize($imgNuevaAltura, 'height');

        $img->save($imgNueva);
    }

}