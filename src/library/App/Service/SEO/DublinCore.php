<?php

/**
 * @author denis.arosquipa@ec.pe
 */
class App_Service_SEO_DublinCore
{

    /**
     * @var Zend_View_Interface
     */
    private $_view;
    private $_config = null;

    const SITE_NAME = 'Aptitus';
    const URL = 'ofertas-de-trabajo';
    const NAME_APTITUS = ' - APTiTUS.com';

    public function __construct($view)
    {
        $this->_view = $view;
        $this->_config = Zend_Registry::get('config');
    }

    public function add($data = '')
    {
        if (empty($data)) {
                throw new Zend_Exception(__CLASS__ . ': aviso esta vacio');
        }

        $values = $this->_getMetas($data);
        $this->_setMetas($values);
    }

    public function _getMetas($data)
    {

        $values = array();

        $values['site'] = $this->_config->openGraph->urlSite;
        $values['type'] = self::TYPE;
        $values['title'] = $this->getTitle($data) . self::NAME_APTITUS;
        $values['keywords'] = $data['keywords'];
        $values['description'] = 'Oferta de trabajo: ' . $this->getTitle($data) . ' - ' .
            $data['ubigeo_nombre'] .
            '. El lugar donde las empresas buscan al mejor talento';

        return $values;
    }

    /**
     * 
     * @todo upgrade code
     * @param type $values
     */
    public function _setMetas($values)
    {
        
        $this->_view->headMeta()->setProperty('DC.Title', $values['title']);
        $this->_view->headMeta()->setProperty('DC.Contributor', $values['site']);
        $this->_view->headMeta()->setProperty('DC.Creator', $values['site']);
        $this->_view->headMeta()->setProperty('DC.Description', $values['description']);
        $this->_view->headMeta()->setProperty('DC.Language', 'es');
        $this->_view->headMeta()->setProperty('DC.Publisher', $values['site']);
        $this->_view->headMeta()->setProperty('DC.Keywords', $values['keywords']);
        $this->_view->headMeta()->setProperty('DC.Subject', $values['title']);
        
    }

    /**
     * 
     * @param type $data
     */
    private function getTitle($data)
    {
        if (trim($data['nombre_empresa']) == "") {
            return $data['puesto'];
        }
        $title = $data['puesto'] . ' en ' . $data['nombre_empresa'];
        return $title;
    }

}


