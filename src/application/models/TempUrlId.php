<?php

class Application_Model_TempUrlId extends App_Db_Table_Abstract
{
    protected $_name = "temp_urlid";

    public function popUrlId()
    {
        //$sql = "SELECT url_id FROM temp_urlid limit 1";
        //var_dump(microtime());
        $db = new App_Db_Table_Abstract();
        $query = "SELECT COUNT(url_id) cantidad FROM temp_urlid";
        $cantidad = $db->getAdapter()->fetchCol($query);
        $cantidad = $cantidad[0];
        
        if ($cantidad > 0) {
            $nro = rand(0, 12000000) % $cantidad;
            
            $sql = $this->getAdapter()->select()
            ->from(
                $this->_name,
                array('url' => 'url_id')
            )
            ->limit(1, $nro);
            
            $result = $this->getAdapter()->fetchOne($sql);
            
            $this->delete("url_id like '".$result."'");
            
            //var_dump($nro);
            //var_dump($cantidad);
        } else {
            $genPassword = new App_Controller_Action_Helper_GenPassword();
            $db = new App_Db_Table_Abstract();
            do {
                $urlId = $genPassword->_genPassword(5);
                $sql = "SELECT id FROM anuncio_web 
                        WHERE url_id like '".$urlId."'";
                $idsAnuncio = $db->getAdapter()->fetchCol($sql);
            } while (count($idsAnuncio) > 0);
            
            $result = $urlId;
        } 
        //var_dump(microtime());
        return $result;
    }
}