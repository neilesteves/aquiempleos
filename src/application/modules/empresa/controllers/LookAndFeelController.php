<?php

class Empresa_LookAndFeelController extends App_Controller_Action_Empresa {


    private $_buscamasConsumerKey;
    private $_buscamasPublishUrl;
    private $_buscamasUrl;
    protected $_empresa;
    protected $_usuario;
    protected $_usuarioempresa;
    protected $_urlId;
    protected $_slug;
    protected $_tieneBuscador;
    protected $_tieneBolsaCVs;
    protected $_candidatosSugeridos;
    protected $_cache = null;

    public function init()
    {
        parent::init();

        $this->empresa = new Application_Model_Empresa();
        $this->usuario = new Application_Model_Usuario();
        $this->_anuncioweb = new Application_Model_AnuncioWeb();

        if (Zend_Auth::getInstance()->hasIdentity() != true) {
            $this->_redirect('/empresa');
        }
        if ($this->usuario->hasvailBlokeo($this->auth['usuario']->id) ) {
            Zend_Auth::getInstance()->clearIdentity();
            Zend_Session::forgetMe();
            $this->getMessenger()->error('Su cuenta ha sido bloqueada, comuníquese con el Administrador');
            $this->_redirect('/empresa');
        }
        if (isset($this->auth['empresa'])) {
            $this->idEmpresa = $this->auth['empresa']['id'];
            $this->usuario = $this->auth['usuario'];
        }
        Zend_Layout::getMvcInstance()->assign('bodyAttr', array('id' => 'myAccount'));
        $this->view->headLink()->appendStylesheet($this->view->S('/main/css/modules/empresa/look-and-feel.css'), 'all');
        $this->view->Look_Feel= $this->empresa->LooFeelActivo($this->auth['empresa']['id']);
        $this->_tieneBolsaCVs = false;        
     
        if(isset($this->auth['empresa']['membresia_info']['membresia']['id_membresia'])){
          $this->_tieneBolsaCVs = !empty($this->auth['empresa']['membresia_info']['beneficios']->bolsacv)?1:0;
        }
        $this->view->tieneBuscador = $this->_anuncioweb->beneficioBuscadorAptitus($this->auth);

        if (!$this->view->Look_Feel) {
          $this->getMessenger()->error('No cuenta permisos para esta opcion');
          $this->_redirect('/empresa');
        }
    }


    public function indexAction()
    {
       $form = new Application_Form_LookAndFeel();
       $mLookFeel = new Application_Model_EmpresaLookAndFeel();
       $ubigeo = new Application_Model_Ubigeo();

       $s3Img = new Amazon_S3_Imagenes();
       $config = $this->getConfig();
       $lookAndFeel = $mLookFeel->getLookAndFeelEmpresa( $this->idEmpresa );
       $dataUbicacion=$ubigeo->getDetalleUbigeo($this->auth['empresa']['id_ubigeo']);
       $this->view->ubicacion='';
       if (!empty($dataUbicacion['distrito']) && !empty($dataUbicacion['provincia'])) {
            $this->view->ubicacion=$dataUbicacion['distrito'].', '.$dataUbicacion['provincia'].', '.$dataUbicacion['dpto'].', '.$dataUbicacion['name'];
       }
       if (empty($dataUbicacion['distrito']) ) {
           $this->view->ubicacion=$dataUbicacion['provincia'].', '.$dataUbicacion['name'];
       }
       if (empty($dataUbicacion['provincia']) ) {
           $this->view->ubicacion=$dataUbicacion['dpto'].', '.$dataUbicacion['name'];
       }
       if( $this->_request->isPost()  )
       {            
           
          $params=$this->_getAllParams();

          $params['txtSlogan']=utf8_decode($params['txtSlogan']);
          $params['txtTitleColumn']=utf8_decode($params['txtTitleColumn']);
       
          $params['txaDescription']=utf8_decode(trim($params['txaDescription']));
          $params['hiddenAddress']=utf8_decode($params['hiddenAddress']);
          $params['txtAddress']=utf8_decode($params['txtAddress']);
          $data =  array_merge( $this->_getAllParams(), $_FILES);
          
          $data['banner_val']=$lookAndFeel['banner'];
          if( $form->isValid($data) )
          {                       
              $data['txtID']=(int)$data['txtID'];
              $dataModel = $form->getDataModel( $data,
                array(
                'bg_primary' => 'txtPrimaryColor',
                'bg_secondary' => 'txtSecondaryColor',
                'eslogan' => 'txtSlogan',
                'titulo_sidebar' => 'txtTitleColumn',
                'link_video' => 'txtVideo',
                'latitud' => 'hiddenPickLat',
                'longitud' => 'hiddenPickLng',
                'direccion' => 'txtAddress',
                'latitud' => 'hiddenPickLat',
                'longitud' => 'hiddenPickLng',
                 'descripcion'   =>'txaDescription',
                 'id' => 'txtID'
             ));
             $optsBanner['key']= empty($data['main_banner']['name'])?$lookAndFeel['banner_original']:null;
             if( !$lookAndFeel ) {
               $optsBanner['key']=new Zend_Db_Expr("NULL");
               $dataModel['fh_creacion'] = date('Y-m-d H:i:s');
             }
             $dataModel['id_empresa'] = $this->idEmpresa;
             $dataModel['id_usuario'] = $this->usuario->id;
             $dataModel['estado'] = Application_Model_EmpresaLookAndFeel::BORRADOR;
             if ($data['btnSubmit']==Application_Form_LookAndFeel::PUBLICADO) {
                 $dataModel['estado'] = Application_Model_EmpresaLookAndFeel::ACTIVO;
             }
             $dataModel['fh_modificacion'] = date('Y-m-d H:i:s');
             $dataModel['mostrar_mapa'] = $data['chkShowMap'] == 'on' ? 1: (($data['chkShowMap'])?1:0);

             if ( !isset($dataModel['mostrar_mapa']) || !$dataModel['mostrar_mapa']) {
                $dataModel['direccion'] = new Zend_Db_Expr("NULL");
             }

             if( $data['chkAddressOption'] == '1' ) {
                $dataModel['direccion'] = new Zend_Db_Expr("NULL");
             }

             /**
              * ------------------
              * Upload imagenes S3
              * -------------------
              */

             $optsBanner =  $this->getParametrosCrop( 'banner', $data['main_image_coords'] );
             $imgBanner = $s3Img->uploadImgEmpresa($form->main_banner, $this->idEmpresa , $optsBanner);
             if( count($imgBanner) > 0 )
             {
                $dataModel['img_banner'] = $imgBanner['default'];
                $dataModel['img_banner_normal'] = $imgBanner['normal'];
                $dataModel['img_banner_original'] = $imgBanner['original'];

                $lookAndFeel['banner'] = $dataModel['img_banner'];
                $lookAndFeel['banner_original'] = $dataModel['img_banner_original'];
               
             }
             unset($optsBanner['key']);
             $optsSeccion =  $this->getParametrosCrop( 'seccion', $data['optional_image_coords'] );
             $imgSeccion = $s3Img->uploadImgEmpresa( $form->img_cover, $this->idEmpresa, $optsSeccion);
             if( count($imgSeccion) > 0 )
             {
                $dataModel['img_seccion'] = $imgSeccion['default'];
                $dataModel['img_seccion_normal'] = $imgSeccion['normal'];
                $dataModel['img_seccion_original'] = $imgSeccion['original'];

                $lookAndFeel['img_seccion'] = $dataModel['img_seccion'];
                $lookAndFeel['img_seccion_original'] = $dataModel['img_seccion_original'];

             }
             if (count($imgSeccion)==0 && !empty($lookAndFeel['img_seccion'])) {
                $key=array($lookAndFeel['img_seccion'],$lookAndFeel['img_seccion_original'],$lookAndFeel['img_seccion_alta']);
                if ($s3Img->deleteFile($key)) {
                    $dataModel['img_seccion'] = new Zend_Db_Expr("NULL");
                    $dataModel['img_seccion_normal'] = new Zend_Db_Expr("NULL");
                    $dataModel['img_seccion_original'] = new Zend_Db_Expr("NULL");
                    $this->view->img_optional = $this->view->S('/main/img/look_and_feel/preview_optional_image.jpg');
                }
              
             }
             $resultId = $mLookFeel->editarLookAndFeel( $dataModel );
             if( $resultId ) {
               $lookAndFeel = $mLookFeel->getLookAndFeelEmpresa( $this->idEmpresa );
               $this->getMessenger()->success( 'Se guardaron los cambios correctamente');
             }

          } else {
             $error = $form->getMessageError();
             $element = $form->getElement( $error['element'] )->getLabel();
             $this->getMessenger()->error( $element . ' : '.$error['message'] );
          }

       }
       $this->view->img_banner = $this->view->S('/main/img/look_and_feel/preview_main_image.jpg');
       $this->view->img_optional = $this->view->S('/main/img/look_and_feel/preview_optional_image.jpg');
       $this->view->config = 1;

       $this->view->latitud = '-12.0553442';
       $this->view->longitud = '-77.0451853';
       $baseUrl = $config->s3->app->elementsBannersUrl;

       if ($lookAndFeel) {
         $this->view->latitud = $lookAndFeel['latitud'];
         $this->view->longitud = $lookAndFeel['longitud'];
         $this->view->config = ($lookAndFeel['estado']=='2') ? 0 : 1;
         if (!empty($lookAndFeel['banner'])) {
            $this->view->img_banner =  $baseUrl . $lookAndFeel['banner'];
            $this->view->img_banner_original =  $baseUrl . $lookAndFeel['banner_original'];
         }
         if (!empty($lookAndFeel['img_seccion'])) {
            $this->view->img_optional = $baseUrl . $lookAndFeel['img_seccion'];
            $this->view->img_optional_original = $baseUrl . $lookAndFeel['img_seccion_original'];
         }
       } 
       $this->view->menu_post_sel = self::MENU_POST_LOOK_AND_FEEL;
       $form->setDefaultData($lookAndFeel);
       $this->view->form=$form;
    }

    private function getParametrosCrop( $image, $values )
    {
       $options = array();
       $options['process'] = 'crop';
       if( $values && strpos($values, ',') !== false )
       {
         $values = str_replace( array('[', ']'), array(), $values );
         list($x0,$y0,$x1,$y1) = explode( ',', $values );
         $options['x0'] = (int)$x0;
         $options['y0'] = (int)$y0;
         $options['x1'] = (int)$x1;
         $options['y1'] = (int)$y1;

       } else {

         $config = $this->getConfig();
         $profile = $config->s3->app->profileEmpresa;

         $options['x0'] = 0;
         $options['y0'] = 0;
         $options['x1'] = (int)$profile->{$image}->minwidth;
         $options['y1'] = (int)$profile->{$image}->minheight;
       }
       return $options;
    }

}
