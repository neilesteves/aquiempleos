<?php
/**
 * Modelo Look And Feel Empresa
 * @category Zend
 * @package Zend_Form
 * @author Aecca <Aecca@example.com> 
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Application_Model_EmpresaLookAndFeel extends App_Db_Table_Abstract
{
    protected $_name = "empresa_look_feel";

    /**
     * Estado de configuracion
     * Cuando ya se encuentra activa
     */
    const ACTIVO = '2';

    /**
     * Estado de configuracion
     * Cuando ya se encuentra publicada
     */
    const PUBLICADO = '1';

    /**
     * Estado de la configuracion
     * Cuando aun es borrador
     */
    const BORRADOR = "0";
    
    /**
     *Variable para obtenere el nombre del modelo
     * @var type 
     */
    private $_model = null;
    
    /**
     * Variable que genera el aliaz
     * @var array 
     */
    private $_modelEmpresa;


    /**
     * Constructor del modelo
     */
    public function __construct()
    {
        $this->_modelEmpresa=array(
            'elof'=> $this->_name
        );
        parent::__construct();
        $cparts = explode('_', __CLASS__);
        $this->_model = $cparts[2];
    }

    /**
     * Editar la configuracion L&F de una empresa
     *  Si la configuracion ya esta registrada, solo se edita,
     *  en caso contrario, se agrega.
     *
     * @param  array $data Datos de la configuracion
     * @return int|boolean Could be an int, could be a string
     */
    public function editarLookAndFeel($data)
    {     
        $id=false;
        if (!is_numeric($data['id'])) {
            $id= $this->insert($data);
        } else {
            $where=$this->getAdapter()->quoteInto('id = ?', $data['id']);
            $result = $this->update($data, $where);         
            $id= $data['id'];         
        }
        $EmprLAFA='EmpresaLookAndFeel_getLookAndFeelActivo_'.$data['id_empresa'];
        $EmprLAFE='EmpresaLookAndFeel_getLookAndFeelEmpresa_'.$data['id_empresa'];
        $this->_cache->remove($EmprLAFA);
        $this->_cache->remove($EmprLAFE);
        return $id;
    }  

    /**
     * Retorna la configuracion de Look And Feel
     * de una determinada empresa solo el activo
     * @param  int $idEmpresa ID de la empresa
     * @return array|void
     */
    public function getLookAndFeelActivo($idEmpresa)
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__."_".$idEmpresa;
        if ($this->_cache->test($cacheId) ) {
            return $this->_cache->load($cacheId);
        }
        $baseFields=array(
                   'id'=>'elof.id',
                   'bg_primary'=>'elof.bg_primary',
                   'bg_secondary'=>'elof.bg_secondary',
                   'youtube'=>'elof.link_video',
                   'youtube_id'=>'elof.link_video',
                   'img_seccion'=>'elof.img_seccion',
                   'img_seccion_original'=>'elof.img_seccion_original',
                   'img_seccion_alta'=>'elof.img_seccion_normal',
                   'texto'=>'elof.descripcion',
                   'banner_original'=>'elof.img_banner_original',
                   'banner'=>'elof.img_banner',
                   'banner_alta'=>'elof.img_banner_normal',
                   'ciudad'=>'elof.direccion',
                   'descripcion'=>'elof.descripcion',
                   'background'=>'elof.bg_primary',
                   'latitud'=>'elof.latitud',
                   'longitud'=>'elof.longitud',
                   'eslogan'=>'elof.eslogan',
                   'titulo_sidebar'=>'elof.titulo_sidebar',
                   'mostrar_mapa'=>'elof.mostrar_mapa'
        );
        $sql = $this->getAdapter()->select()
            ->from($this->_modelEmpresa, $baseFields)
            ->where('elof.id_empresa=?', (int)$idEmpresa)
            ->where('elof.estado=?', self::ACTIVO);      
        $result = $this->getAdapter()->fetchRow($sql);
        $bt_primari=$result['bg_primary'];
        if ($result) {
            $result['background']=str_replace("#", "", $bt_primari);                 
        }
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }
    
    /**
     * lista de configuracion para una aviso sin importar el estado.
     * 
     * @param int $IdEmp id empresa.
     * @return array retorna datos de configuracion
     */
    public function getLookAndFeelEmpresa($IdEmp) 
    {
        $cacheEt = $this->_config->cache->{$this->_model}->{__FUNCTION__};
        $cacheId = $this->_model.'_'.__FUNCTION__."_".$IdEmp;
        if ($this->_cache->test($cacheId) ) {
            return $this->_cache->load($cacheId);
        }
        $baseFields=array(
           'id'=>'elof.id',
           'bg_primary'=>'elof.bg_primary',
           'bg_secondary'=>'elof.bg_secondary',
           'youtube'=>'elof.link_video',
           'youtube_id'=>'elof.link_video',
           'img_seccion'=>'elof.img_seccion',
           'img_seccion_original'=>'elof.img_seccion_original',
           'img_seccion_alta'=>'elof.img_seccion_normal',
           'texto'=>'elof.descripcion',
           'banner_original'=>'elof.img_banner_original',
           'banner'=>'elof.img_banner',
           'banner_alta'=>'elof.img_banner_normal',
           'ciudad'=>'elof.direccion',
           'descripcion'=>'elof.descripcion',
           'background'=>'elof.bg_primary',
           'latitud'=>'elof.latitud',
           'longitud'=>'elof.longitud',
           'eslogan'=>'elof.eslogan',
           'titulo_sidebar'=>'elof.titulo_sidebar',
           'mostrar_mapa'=>'elof.mostrar_mapa',
           'estado'=>'elof.estado'
        );
        $sql = $this->getAdapter()->select()
            ->from($this->_modelEmpresa, $baseFields)
            ->where('elof.id_empresa=?', (int)$IdEmp)
            ->order('elof.id DESC');
        $result = $this->getAdapter()->fetchRow($sql); 
        $this->_cache->save($result, $cacheId, array(), $cacheEt);
        return $result;
    }
}
