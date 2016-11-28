<?php

class Postulante_EncuentraTuPasionController extends App_Controller_Action_Postulante 
{
 
  protected $_cache = null;
  protected $_config = null;
  protected $_modelDescargas=null;
  protected $_mongoDescarga=null;
  protected $_Emcripta="aptitus";
  public function init()
  {
    $this->_config=Zend_Registry::get('config');
   // parent::init();
    
  }
  
  public function indexAction()
  {
    $this->_helper->layout->disableLayout();
    $descarga = new Zend_Session_Namespace('descarga');
    $descarga->tokendescarga=md5(rand());
    $descarga->encripta=  $this->_helper->Util->codifica($this->_Emcripta);
    $this->view->token=$descarga->tokendescarga;
    $browser = getenv("HTTP_USER_AGENT");
    $this->view->ruta=SITE_URL.'/encuentra-tu-pasion/reproduccion/reproduccion.mp3';
    if (preg_match("/MSIE/i", "$browser"))
    {
      $this->view->ruta=$this->_config->landing->encuentra_tu_pacion->link.$this->_config->landing->encuentra_tu_pacion->ringtone;
    }
  }
  public function descargaAction() {
    $descarga = new Zend_Session_Namespace('descarga');
    $data = $this->_getAllParams();
    $mongoDescarga= new Mongo_Descargas();
    if ($data['token']==$descarga->tokendescarga) {
      try {
          $path=null;
          if ($data['tipo']=="mp3") {
              $path= $this->_config->landing->encuentra_tu_pacion->mp3;
          }
          if ($data['tipo']=="ringtone") {
              $path= $this->_config->landing->encuentra_tu_pacion->ringtone;
          }
          if (empty($path)) {
             $this->_redirect('/encuentra-tu-pasion');
          }    
          $data= array('tipo'=>$data['tipo'],'num'=>1);
          $id =  $mongoDescarga->save($data); 
          $archivo = basename($path);
          $ruta = $this->_config->landing->encuentra_tu_pacion->link.$path;
          header("Content-type: application/octet-stream");
          header("Content-Disposition: attachment; filename=\"$path\"\n");
          $fp=fopen("$ruta", "r");
          fpassthru($fp);
      } catch (Exception $exc) {
         $this->log->error($exc->getMessage().'. '.$exc->getTraceAsString(),Zend_Log::ERR);
         $this->_redirect('/encuentra-tu-pasion');
      }

          
    } else {
      $this->_redirect('/encuentra-tu-pasion');
    }
    
  }
  public function reproduccionAction()
  {
      $descarga = new Zend_Session_Namespace('descarga');
      $path= $this->_config->landing->encuentra_tu_pacion->reproduccion;
      $dest=  $this->_helper->Util->decodifica($descarga->encripta);
      if ( $this->_helper->Util->decodifica($descarga->encripta)==$this->_Emcripta) {
          $nombre_fichero=APPLICATION_PATH.'/../public/static/landing/media/'.$path;
          $fichero_texto = fopen ($nombre_fichero, "r");          
          $contenido_fichero = fread($fichero_texto, filesize($nombre_fichero));
          // unset($descarga->encripta);
          echo $contenido_fichero;
      }  else {
          $this->_redirect('/encuentra-tu-pasion');
      }
      exit;
  }
 }
