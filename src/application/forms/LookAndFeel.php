<?php
/**
* Form Look And Feel Empresa
*
* PHP version 5
*
* @category  Class
* @package   Zend_Frameword
* @author    Ronald Cutisaca <ronald.cutisaca@clicksandbricks.pe>
* @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
* @license   Copyright (c) 2015, Zend Technologies.
* @version   GIT: <2eb2f0d73c69ed157f3071b10d3d32e36964e1c6>
* @link      http://aptitus.com/empresa/look-and-feel
*/
class Application_Form_LookAndFeel extends App_Form
{
    /**
     *
     * @var type 
     */
    protected $fileS3Banner;
    /**
     * Valor que identifica el maximo numero de caractares
     * para el cambo Slogan
     * @var int
     */
    private $_maxlengthSlogan=24;

    /**
      * Valor que identifica el maximo numero de caracteres
      * para el campo TitleColumm
      * @var int
     */
    private $_maxlengthTitleColumn=40;

    /**
     * Estado de configuracion
     * Cuando ya se encuentra publicada
     */
    const PUBLICADO = 'publicar';

    /**
       * Estado de la configuracion
       * Cuando aun es borrador
    */
    public $errors = array(
    'isEmpty' => 'Campo Requerido',
    'notSame'=>'Por favor vuelva a intentarlo',
    'missingToken'=>'Por favor vuelva a intentarlo',
    'notInArray'=>'No se encontro el registro',
    'fileImageSizeWidthTooBig' => "Las dimensiones de la imagen no son correctas",
    'fileImageSizeWidthTooSmall'=> "Las dimensiones de la imagen no son correctas",
    'fileImageSizeHeightTooBig' => "Las dimensiones de la imagen no son correctas",
    'fileImageSizeHeightTooSmall'=> "Las dimensiones de la imagen no son correctas",
    'fileImageSizeNotDetected' => "No se puede obtener el tamaño de la imagen",
    'fileImageSizeNotReadable' => "Error al leer la imagen",
    'txtPrimaryColor' => array(
        'regexNotMatch' => "No parece ser un color valido"
    ),
    'txtVideo' => array(
      'regexNotMatch' => "Solo se permiten enlaces de youtube o vimeo"
    ),

    'txtSecondaryColor'=> array(
       'regexNotMatch' => "No parece ser un color valido"
    ),
    'main_banner'=> array(
       'fileUploadErrorNoFile' => "El banner no ha sido subido",
       'fileImageSizeWidthTooBig' => "Las dimensiones de la imagen no son correctas",
       'fileImageSizeWidthTooSmall'=> "Las dimensiones de la imagen no son correctas",
       'fileImageSizeHeightTooBig' => "Las dimensiones de la imagen no son correctas",
       'fileImageSizeHeightTooSmall'=> "Las dimensiones de la imagen no son correctas",
       'fileImageSizeNotDetected' => "No se puede obtener el tamaño de la imagen",
       'fileImageSizeNotReadable' => "Error al leer la imagen"
    ),
    'img_cover'=> array(
       'fileUploadErrorNoFile' => "La imagen no ha sido subida",
       'fileImageSizeWidthTooBig' => "Las dimensiones de la imagen no son correctas",
       'fileImageSizeWidthTooSmall'=> "Las dimensiones de la imagen no son correctas",
       'fileImageSizeHeightTooBig' => "Las dimensiones de la imagen no son correctas",
       'fileImageSizeHeightTooSmall'=> "Las dimensiones de la imagen no son correctas",
       'fileImageSizeNotDetected' => "No se puede obtener el tamaño de la imagen",
       'fileImageSizeNotReadable' => "Error al leer la imagen"
    )
    );

    /**
     * Expresiones regulares
     * @var array
     */
    private $_patterm= array(
      'pattern' => '/#([a-fA-F0-9]{3}){1,2}\b/'
    );

    /**
     * Etiquetas
     * @var array
     */
    private $_main_banner= array(  
      'class'=>'input_image',
      'data-ratio'=>'13.78:3',
      'data-width'=>'398',
      'data-height'=>'80',
      'data-flag'=>'main'
    );

    private $_img_cover= array(
      'class'=>'input_image',
      'data-ratio'=>'3:2',
      'data-width'=>'300',
      'data-height'=>'200',
      'data-flag'=>'optional'
    );

    private $_chk=array(
       'label' => 'Label',
       'decorators' => array(
           'ViewHelper', array('label', array(
                            'class'=>'label_text','placement' => 'prepend'
                        )),
            array( array(
               'rows' => 'HtmlTag'
               ),
              array(
              'tag' => 'div',
              'class' => 'radio'
               )),
       ),
       'multioptions' => array (
           1 => 'Usar dirección señalada en el registro',
           2 => 'Otra dirección'
       ),
    );

    protected $profileEmpresa;
    protected $regexYoutubeVideo;
    protected $regexVimeoVideo;
    protected $regexColorHex;


    /**
     * Initialize formulario (usando extencion de la clase)
     *
     * @return void
     */
    public function init()
    {
         parent::init();

         $this->regexYoutubeVideo='/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?'
                . '(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)'
                . '?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&">]+)/i';
         $this->regexVimeoVideo='#^(https?://)?(www.)?(player.)?vimeo.com/'
                . '([a-z]*/)*([0-9]{6,11})[?]?.*#';
         $this->regexColorHex='/#([a-fA-F0-9]{3}){1,2}\b/';

         $this->profileEmpresa = $this->_config->s3->app->profileEmpresa;
         $isIE9  = (bool) preg_match('/msie [1-9]./i', $_SERVER['HTTP_USER_AGENT'] );


         // txtID
         $hid = new Zend_Form_Element_Hidden('txtID');
         $this->addElement($hid);

         // Color Primario
         $pColor = new Zend_Form_Element_Hidden('txtPrimaryColor');
         $pColor->setLabel('Color Primario');
         $pColor->setRequired();
         $pColor->addValidator(new Zend_Validate_NotEmpty(), true);
         $pColor->addValidator('regex', false, $this->_patterm);
         $pColor->setAttrib('id', "txtPrimaryColor");
         $pColor->setAttrib('data-id', "primary");
         $pColor->setAttrib('class', "color_picker");
         $pColor->setAttrib('required', '');
         $pColor->setValue("#000");
         $this->addElement($pColor);

         //Color Secundario
         $sColor = new Zend_Form_Element_Hidden('txtSecondaryColor');
         $sColor->setLabel('Color Secundario');
         $sColor->setRequired();
         $sColor->addValidator(new Zend_Validate_NotEmpty(), true);
         $sColor->addValidator('regex', false, $this->_patterm);
         $sColor->setAttrib('id', "txtSecondaryColor");
         $sColor->setAttrib('data-id', "secondary");
         $sColor->setAttrib('class', "color_picker");
         $sColor->setAttrib('required', '');
         $sColor->setValue("#f7c52d");
         $this->addElement($sColor);

         //baner principal
         $main_banner = new Zend_Form_Element_File('main_banner');
         $main_banner->setLabel('Imagen principal');
         $main_banner->setRequired();
         $main_banner->setAttribs($this->_main_banner);

         $main_banner->setDestination($this->_config->urls->app->elementsLogosRoot);
         $valFile_Size=array(
             'max' => $this->_config->app->maxSizeLogo
         );
         $valmain_banner=new Zend_Validate_File_Size($valFile_Size);
         $msg="El tamaño del archivo excede el valor definido";
         $valmain_banner->setMessage($msg);
         $main_banner->addValidator($valmain_banner);
         $main_banner->addValidator('Extension', false, 'jpg,jpeg,png');
         $main_banner->getValidator('Size');

         $ImageSizeBanner= array(
              'minwidth'  => $this->profileEmpresa->banner->minwidth  ,
              'minheight' => $this->profileEmpresa->banner->minheight,
              'maxwidth'  => $this->profileEmpresa->banner->maxwidth,
              'maxheight' => $this->profileEmpresa->banner->maxheight
         );
         $main_banner->addValidator('ImageSize', false, $ImageSizeBanner);
         $this->addElement($main_banner);

         // slogan
         $txtSlogan = new Zend_Form_Element_Text('txtSlogan');
         $txtSlogan->setLabel('Slogan');
         $txtSlogan->setRequired();
         $txtSlogan->addValidator(new Zend_Validate_NotEmpty(), true);
         $txtSlogan->setAttrib('maxLength', 24);
         $txtSlogan->setAttrib('required', '');
         $SloganStringLength=array(
             'min' => 1,
             'max' => 24,
             'encoding' => $this->_config->resources->view->charset
         );
         $valSlogan= new Zend_Validate_StringLength($SloganStringLength);
         $txtSlogan->addValidator($valSlogan);
         $this->addElement($txtSlogan);

         // Titulo Columna
         $txtTitleColumn = new Zend_Form_Element_Text('txtTitleColumn');
         $txtTitleColumn->setLabel('Titulo Columna');
         $txtTitleColumn->addValidator(new Zend_Validate_NotEmpty(), true);
         $txtTitleColumn->setAttrib('maxLength', 40);
         $txtTitleColumn->setAttrib('placeholder', "Conócenos");
         $txtTitleColumn->setAttrib('required', '');
         $txtTitleColumn->setRequired();
         $TitleColumStrgLen= array(
            'min' => '1', 'max' => $this->maxlengthTitleColumn,
            'encoding' => $this->_config->resources->view->charset
         );
         $valTitleColumnn= new Zend_Validate_StringLength($TitleColumStrgLen);
         $valTitleColumnn->setMessage($this->error);
         $txtTitleColumn->addValidator($valTitleColumnn);
         $this->addElement($txtTitleColumn);

         // Link Video
         $txtVideo = new Zend_Form_Element_Text('txtVideo');
         $txtVideo->setLabel('Link Video');
         $txtVideo->addValidator(new Zend_Validate_NotEmpty(), true);
         $txtVideo->setAttrib('maxLength', 600);
         $txtVideo->setAttrib('placeholder', "Link de YouTube o Vimeo");
         $VideoStringLength=array(
             'min' => '1', 'max' =>600,
             'encoding' => $this->_config->resources->view->charset
         );
         $valVideo= new Zend_Validate_StringLength($VideoStringLength);
         $valVideo->setMessage($this->error);
         $txtVideo->addValidator($valVideo);
         $this->addElement($txtVideo);

         // Descripcion
         $txaDescription = new Zend_Form_Element_Textarea('txaDescription');
         $txaDescription->setLabel('Descripcion');
         $txaDescription->setRequired();
         $txaDescription->addValidator(new Zend_Validate_NotEmpty(), true);
         $txaDescription->setAttrib('required', '');
         $DescStringLength=array(
             'min' => '1', 'max' =>1400,
             'encoding' => $this->_config->resources->view->charset
         );
         $valDescription= new Zend_Validate_StringLength($DescStringLength);
         $valDescription->setMessage($this->error);
         $txaDescription->addValidator($valDescription);
         $this->addElement($txaDescription);

         // Dirección
         $txtAddress = new Zend_Form_Element_Text('txtAddress');
         $txtAddress->setLabel('Dirección');
         $txtAddress->addValidator(new Zend_Validate_NotEmpty(), true);
         $txtAddress->setAttrib('maxLength', 600);
         $AddressStringLength= array(
             'min' => '1', 'max' =>600,
             'encoding' => $this->_config->resources->view->charset
         );
         $valAddress= new Zend_Validate_StringLength($AddressStringLength);
         $valAddress->setMessage($this->error);
         $txtAddress->addValidator($valAddress);
         $this->addElement($txtAddress);

         // imagen opcional
         $img_cover = new Zend_Form_Element_File('img_cover');
         $img_cover->setLabel('Imagen opcional');
         $img_cover->setRequired()->getMessages('El archivo Img no ha sido subido');
         $messageString="El archivo Img no ha sido subido";
         $valRequiered=new Zend_Validate_NotEmpty();
         $valRequiered->setMessage($messageString);
         $img_cover->addValidator($valRequiered, true);
         $img_cover->setAttribs($this->_img_cover);

//         if( $isIE9 ) {
//           $this->profileEmpresa->seccion->maxwidth =  $this->profileEmpresa->seccion->minwidth;
//           $this->profileEmpresa->seccion->maxheight = $this->profileEmpresa->seccion->minheight;
//         }
         $img_coverSize=  array(
              'minwidth'  => $this->profileEmpresa->seccion->minwidth  ,
              'minheight' => $this->profileEmpresa->seccion->minheight,
              'maxwidth'  => $this->profileEmpresa->seccion->maxwidth,
              'maxheight' => $this->profileEmpresa->seccion->maxheight
         );
         $img_cover->addValidator('ImageSize', false, $img_coverSize);
         $img_cover->setDestination($this->_config->urls->app->elementsLogosRoot);
         $img_coverFile_Size=array(
             'max' => $this->_config->app->maxSizeLogo
         );
         $valimg_cover=new Zend_Validate_File_Size($img_coverFile_Size);
         $msgImg_cover='El tamaño del archivo excede el valor definido';
         $valimg_cover->setMessage($msgImg_cover);
         $img_cover->addValidator($valimg_cover);
         $img_cover->addValidator('Extension', false, 'jpg,jpeg,png');
         $msgSizeimg_cove='Tamaño de Imagen debe ser menor a 500kb';
         $img_cover->getValidator('Size')->setMessage($msgSizeimg_cove);
         $msgExtenimg_cove='Seleccione un archivo con extensión .jpg,.jpeg,.png';
         $img_cover->getValidator('Extension')->setMessage($msgExtenimg_cove);
         $this->addElement($img_cover);

         // hiddenPickLat
         $hiddenPickLat = new Zend_Form_Element_Hidden('hiddenPickLat');
         $hiddenPickLat->setValue("12.1248138");
         $this->addElement($hiddenPickLat);

         // hiddenPickLng
         $hiddenPickLng = new Zend_Form_Element_Hidden('hiddenPickLng');
         $hiddenPickLng->setValue("76.9829017");
         $this->addElement($hiddenPickLng);

         //chkShowMap
         $chkShowMapSwitch = new Zend_Form_Element_Checkbox('chkShowMap');
         $chkShowMapSwitch->setRequired(false);
         $chkShowMapSwitch->setOptions(array('on'=>'on'));
         $chkShowMapSwitch->setValue('on');
         $chkShowMapSwitch->setAttrib("id", "chkShowMapSwitch");
         $chkShowMapSwitch->setAttrib("class", "onoffswitch_checkbox");
         $this->addElement($chkShowMapSwitch);

         //chkAddressOption
         $chkAddrOpt= new Zend_Form_Element_Radio('chkAddressOption', $this->_chk);
         $chkAddrOpt->setAttrib('id', "chkAddress");
         $this->addElement($chkAddrOpt);
    }
    /**
     * Funcion de validacion de campos del formulario
     * @param array $data datos del formulario
     * @return boolean
     */
    public function isValid($data)
    {    
        $buscar=array(chr(13).chr(10), "\r\n", "\n", "\r");
        $reemplazar=array(" ", " ", " ", " ");
        $data['txaDescription']=str_ireplace($buscar,$reemplazar,$data['txaDescription']);
        $this->txtID->setRequired(false);
        if (!empty($data['txtSlogan'])) {
            $this->txtSlogan->setRequired(true);
        }
        if (!empty($data['txtTitleColumn'])) {
            $this->txtSlogan->setRequired(true);
        }
        if (!empty($data['txtVideo'])) {
            $ckVimeo = strpos($data['txtVideo'], 'vimeo');
            $regex=array(
               'pattern' => ($ckVimeo) ?
                   $this->regexVimeoVideo :
                   $this->regexYoutubeVideo
            );
            $this->txtVideo->addValidator('regex', false, $regex);
            $this->txtVideo->setRequired(true);
        }
        if (empty($data['img_cover']['name'])) {
           $this->img_cover->setRequired(false);
        }
        $this->fileS3Banner=!empty($data['banner_val'])?true:false;

        if ($this->fileS3Banner) {
           $this->main_banner->setRequired(false);
        }
        return parent::isValid($data);
    }
    /**
     * Funcion que llena los datos al formulario
     * @param type $data
     * @return type Description
     */
    public function setDefaultData($data)
    {
        if ($data) {
            $this->txtID->setValue($data['id']);
            if ($data['mostrar_mapa']==1) {
                $this->chkShowMap->setValue('on');
                $this->chkShowMap->setAttrib('checked', 'checked');
                if (!empty($data['ciudad'])) {
                    $this->chkAddressOption->setValue( 2 );
                }else {
                    $this->chkAddressOption->setValue( 1 );
                }
            }
            $this->main_banner->setRequired(false);
            $this->img_cover->setRequired(false);
            $defauls=array(
              'txtID'=>$data['id'],
              'txtPrimaryColor'=>$data['bg_primary'],
              'txtSecondaryColor'=>$data['bg_secondary'],
              'txtSlogan'=>$data['eslogan'],
              'txtTitleColumn'=>$data['titulo_sidebar'],
              'txtVideo'=>$data['youtube'],
              'main_banner'=>$data['banner_original'],
              'txaDescription'=>$data['descripcion'],
              'txtAddress'=>$data['ciudad'],
              'hiddenPickLat'=>$data['latitud'],
              'hiddenPickLng'=>$data['longitud']
            );
            $this->setDefaults($defauls);
        }else {
          $this->chkAddressOption->setAttrib( 'required', '' );
          $this->chkAddressOption->setValue( 1 );
        }
    }
}

