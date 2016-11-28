<?php

class Postulante_RssController extends App_Controller_Action_Postulante {

    public function init() {
        parent::init();
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('xml', 'xml')->initContext('xml');
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }

    public function rssAction() {
        $aw = new Application_Model_AnuncioWeb();
        $avisos = $aw->getUltimosAvisos();
        $t = time();
        $author = array(
            'name' => 'Aptitus',
            'email' => 'rss@aptitus.pe',
            'uri' => 'http://aptitus.com',
        );
        $feed = new Zend_Feed_Writer_Feed();
        $feed->setTitle('Aptitus');
        $feed->setLink('http://aptitus.com/');
        $feed->setFeedLink('http://aptitus.com/rss', 'atom');
        $feed->addAuthor($author);
        $feed->setDateModified($t);
        foreach ($avisos as $aviso) {
            $entry = $feed->createEntry();
            $entry->setTitle($aviso['puesto']);
            $entry->setLink(
                    SITE_URL . '/' .
                    $this->view->url(
                            array('slug' => $aviso['slugaviso'], 'url_id' => $aviso['urlaviso']), 'aviso', true
                    )
            );
            $entry->addAuthor($author);
            $entry->setDateModified($t);
            $entry->setDateCreated($t);
            $desc = $aviso['descripcion'] != '' ? $aviso['descripcion'] : '(vacio)';
            $entry->setDescription($desc);
            $entry->setContent($desc);
            $feed->addEntry($entry);
        }
        $out = $feed->export('atom');
        $this->getResponse()->appendBody($out);
    }

    public function indexAction() {
        $di = new DOMImplementation();
        $doctype = $di->createDocumentType('DATA', '', 'XACOMERCIO.dtd');
        $xml = $di->createDocument('', '', $doctype);
        $dataTag = $xml->createElement('DATA');
        $mAnuncioWeb = new Application_Model_AnuncioWeb();
        $mArea = new Application_Model_Area();
        $areas = $mArea->getAreasFeed();
        $avisos = $mAnuncioWeb->getAvisosFeed();


        foreach ($avisos as $aviso) {
            $adTag = $xml->createElement('AD');

            $adTag->appendChild(
                    $xml->createElement(
                            'URL_IMAGE', $aviso['logo'] == '' ? '' : ELEMENTS_URL_IMG . $aviso['logo']
                    )
            );

            $node = $adTag->appendChild($xml->createElement('URL_AD'));
            $url = $this->view->url(array('url_id' => $aviso['url_id'], 'slug' => $aviso['slug']), 'aviso', true);
            $node->appendChild($xml->createCDATASection(SITE_URL . $url));

            $node = $adTag->appendChild($xml->createElement('TITLE_AD'));
            $node->appendChild($xml->createCDATASection($aviso['puesto']));

            $node = $adTag->appendChild($xml->createElement('DESC_AD'));
            $node->appendChild($xml->createCDATASection($aviso['funciones'] . ' ' . $aviso['responsabilidades']));

            $adTag->appendChild($xml->createElement('FEC_PUB', $aviso['fh_pub']));

            $dataTag->appendChild($adTag);
        }

        $catsTag = $xml->createElement('CATEGORIES');

        foreach ($areas as $area) {
            $catTag = $xml->createElement('CATEGORY');

            $node = $catTag->appendChild($xml->createElement('URL_CATEGORY'));
            $url = $this->view->url(array('areas' => $area['slug']), 'buscar', true);
            $node->appendChild($xml->createCDATASection(SITE_URL . $url));

            $node = $catTag->appendChild($xml->createElement('NAME_CATEGORY'));
            $node->appendChild($xml->createCDATASection($area['nombre']));


            $catsTag->appendChild($catTag);
        }
        $dataTag->appendChild($catsTag);



        $xml->appendChild($dataTag);
        $output = $xml->saveXML();
        $this->_response
                ->setHeader('Content-Type', 'text/xml; charset=utf-8')
                ->setBody($output);
    }

    public function jobsAction() {
        $xml = new DOMDocument('1.0', 'utf-8');
        $dataTag = $xml->createElement('listings');

        $mAnuncioWeb = new Application_Model_AnuncioWeb();

        $avisos = $mAnuncioWeb->getAvisosJobs();

        foreach ($avisos as $aviso) {
            $url = SITE_URL . '/ofertas-de-trabajo/' . $aviso['slugaviso'] . '-' . $aviso['url_aviso'];
            $catTag = $xml->createElement('listing');

            $node = $catTag->appendChild($xml->createElement('slug', $aviso['slugaviso']));
            $node = $catTag->appendChild($xml->createElement('title'))->appendChild($xml->createCDATASection($aviso['puesto']));
            $node = $catTag->appendChild($xml->createElement('url', $url));
            $node = $catTag->appendChild($xml->createElement('city', $aviso['ubicacion']));
            $node = $catTag->appendChild($xml->createElement('Job_category'))->appendChild($xml->createCDATASection($aviso['area']));
            $node = $catTag->appendChild($xml->createElement('description'))->appendChild($xml->createCDATASection($aviso['description']));
            $node = $catTag->appendChild($xml->createElement('listing_time', date('Y-m-d H:i:s', strtotime($aviso['fh_pub']))));
            $node = $catTag->appendChild($xml->createElement('expire_time', date('Y-m-d H:i:s', strtotime($aviso['fh_ven']))));
            $dataTag->appendChild($catTag);
        }
//        $dataTag->appendChild($catsTag);



        $xml->appendChild($dataTag);
        $output = $xml->saveXML();

        $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
                ->setBody($output);
    }
}