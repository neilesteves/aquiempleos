<?php

Class App_Util_GenerateSitemap
{
    private $_urlBase;
    private $_records   = array();
    private $_fileName  = 'sitemap.xml';
    private $_directory;
    private $_changefreq;
    
    const NAME_SPACE = "http://www.sitemaps.org/schemas/sitemap/0.9";
    
    private function _getFileName()
    {
        return $this->_fileName;
    }
    
    public function setFileName($fileName)
    {
        $this->_fileName = $fileName;
    }
    
    private function _getRecods()
    {
        return $this->_records;
    }
    
    public function setRecods($records)
    {
        $this->_records = $records;
    }
    
    public function setUrlBase($urlBase)
    {
        $this->_urlBase = $urlBase;
    }
    
    private function _getUrlBase()
    {
        return $this->_urlBase;
    }
    
    public function setDirectory($directory)
    {
        $this->_directory = $directory;
    }
    
    private function _getDirectory()
    {
        return $this->_directory;
    }
    
    public function setChangeFreq($changeFreq)
    {
        $this->_changefreq = $changeFreq;
    }
    
    private function _getChangeFreq()
    {
        return $this->_changefreq;
    }
    
    public function save()
    {
        echo PHP_EOL . 'GENERANDO SITEMAP' . PHP_EOL . PHP_EOL;
        try {
            $xml = new DomDocument('1.0', 'UTF-8');

            $root = $xml->createElementNS(self::NAME_SPACE, 'urlset');
            $xml->appendChild($root);                       

            $records = $this->_getRecods();
            $urlBase = $this->_getUrlBase();
            $date    = new Zend_Date();
            
            foreach ($records as $record) {
                $url = $xml->createElement('url');
                $root->appendChild($url);

                $urlValue = $this->_getUrlBase($urlBase) . '/' .
                        $record->slug . '-' .
                        $record->url_id;

                $loc = $xml->createElement('loc', $urlValue);
                $url->appendChild($loc);
                
                $date->set($record['fh_pub']);
                
                $lastmod = $xml->createElement(
                        'lastmod', $date->get('YYYY-MM-ddThh:mm:ssZZZZ'));
                $url->appendChild($lastmod);

                $changefreq = $xml->createElement(
                        'changefreq', $this->_getChangeFreq());
                $url->appendChild($changefreq);                    
            }

            $xml->formatOutput = true;

            $file = realpath($this->_getDirectory()) . 
                    DIRECTORY_SEPARATOR . 
                    $this->_getFileName();

            $xml->save($file);
            echo '***Sitemap generado con exito***' . PHP_EOL;
            echo 'en :' . $file .PHP_EOL;
        } catch (Exception $e) {
            echo 'ocurrio un error :' . $e->getMessage();
        }
    }
}