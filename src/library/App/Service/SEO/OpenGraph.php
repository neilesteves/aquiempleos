<?php


/**
 * @author Carlos Muñoz <camura8503@gmail.com>
 */
class App_Service_SEO_OpenGraph
{

    /**
     * @var Zend_View_Interface
     */
    private $_view;
    private $_config = null;

    const SITE_NAME = 'Aptitus';
    const TYPE = 'article';
    const URL = 'ofertas-de-trabajo';
    const NAME_APTITUS = ' - APTiTUS.com';

    public function __construct($view)
    {
        $this->_view = $view;
        $this->_config = Zend_Registry::get('config');
    }

    public function add($data)
    {
        if (empty($data))
                throw new Zend_Exception(__CLASS__ . ': aviso esta vacio');

        $values = $this->_getMetas($data);
    //    var_dump($values);exit;
        $this->_setMetas($values);
    }

    public function _getMetas($data)
    {

        $values = array();

        $values['site'] = $this->_config->openGraph->urlSite;
        $values['type'] = self::TYPE;
        $values['title'] = $this->getTitle($data);

        /**
         * upgrade code
         */
        if ($data['mostrar_empresa'] == 1) {
            if (isset($data['logo_facebook']) && trim($data['logo_facebook']) !== "") {
                $values['image'][] = ELEMENTS_URL_LOGOS . $data['logo_facebook'];
            } else {
                $values['image'][] = $this->_config->app->mediaUrl .
                    '/images/logo_fb.jpg';
            }
        } else {
            $values['image'][] = $this->_config->app->mediaUrl .
                '/images/logo_fb.jpg';
        }


        $values['url'] = $this->_config->openGraph->urlSite . '/' .
            self::URL . '/' . $data['slug'] . '-' . $data['url_id'];

        $values['description'] = 'Trabaja como ' . ucwords(strtolower($data['puesto'])) . ' en ' . 
                $data['empresa_rs'] . ', ' .$data['ubigeo_nombre'] .
                '. Publicado el ' . $data['fecha'] . '. Más opciones relacionadas en Aptitus.com.';

        return $values;
    }

    /**
     * 
     * @todo upgrade code
     * @param type $values
     */
    public function _setMetas($values)
    {
        $this->_view->doctype(Zend_View_Helper_Doctype::XHTML11);
        $this->_view->headMeta()->setProperty('og:site_name', $values['site']);
        $this->_view->headMeta()->setProperty('og:type', $values['type']);
        $this->_view->headMeta()->setProperty('og:title', $values['title']);
        $this->_view->headMeta()->setProperty('og:url', $values['url']);
        $this->_view->headMeta()->setProperty('og:description', $values['description']);


        foreach ($values['image'] as $image) {
            $extension = explode('.', $image);
            $size = @getimagesize($image);            
            $this->_view->headMeta()->appendProperty('og:image', (string) $image);
            $this->_view->headMeta()->appendProperty('og:image:type', 'image/' . array_pop($extension));
            $this->_view->headMeta()->appendProperty('og:image:width', (string) $size[0]);
            $this->_view->headMeta()->appendProperty('og:image:height', (string) $size[1]);
        }
    }

    /**
     * 
     * @param type $data
     */
    public function getTitle($data)
    {

        if (trim($data['nombre_empresa']) == "") {
            return $data['puesto'];
        }

//        $title = $data['puesto'] . ' en ' . $data['nombre_empresa'];
        $title = ucwords(strtolower($data['puesto'])) . ' en ' .
                $data['empresa_rs'] . ' - ' .
                $data['ubigeo_nombre'] . ' - ' .
                $data['fecha'] . ' | APTiTUS';

        return $title;
    }

}


