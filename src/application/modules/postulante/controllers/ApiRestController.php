<?php

class Postulante_ApiRestController extends Zend_Rest_Controller {
    
    protected $_model;
    
    const API_ERROR_QUERY = 402;
    const API_ERROR_APP   = 401;
    const API_OK          = 200;
    
    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        parent::init();
    }

    public function preDispatch()
    {
        //Cualquier accion sera protegida menos index y failedauth
        $publicActions = array('index','failedauth');
        $action = $this->getRequest()->getActionName();

        if (!in_array($action, $publicActions)) {
        //Si una accion no es publica entonces verifica la autenticacion
            if ($this->_helper->HTTPAuth->doBasicHTTPAuth() == false) {
                $this->_forward('failedauth');
                return;
            }
        }
    }

    public function failedauthAction()
    {
        //public action
    }

    public function indexAction()
    {
        
    }

    public function listJobsAction() 
    {
        $xml="";
        try{
            $idPostulante = $this->_getParam("idpostulante");
            $idEmpresa = $this->_getParam("idempresa");
            $o = new Application_Model_Api();
            $result = $o->getAdapter()->fetchAll($o->getListJobs($idEmpresa, $idPostulante));
            $doc = new DOMDocument();
            $root = $doc->createElement("result");
            $this->_insertStatus($root, $doc, self::API_OK);
            $doc->appendChild($root);
            foreach ($result as $index=>$value) {
                $this->_attayToXml($doc, $root, $value, "job");
            }
            $xml = $doc->saveXML();
        } catch (Exception $e) {
            $doc = new DOMDocument();
            $root = $doc->createElement("result");
            $this->_insertStatus($root, $doc, self::API_ERROR_APP);
            $xml = $doc->saveXML();
        }
        echo $xml;
    }

    public function getJobAction()
    {
        $xml="";
        try{
            $idPostulante = $this->_getParam("idpostulante");
            $idEmpresa = $this->_getParam("idempresa");
            $idAnuncio = $this->_getParam("idanuncio");
            $o = new Application_Model_Api();
            $result = $o->getAdapter()->fetchAll($o->getJob($idEmpresa, $idPostulante, $idAnuncio));
            
            $doc = new DOMDocument();
            $root = $doc->createElement("result");
            $this->_insertStatus($root, $doc, self::API_OK);
            $doc->appendChild($root);
            foreach ($result as $index=>$value) {
                $r = $this->_attayToXml($doc, $root, $value, "job");
                //agrega las preguntas
                $idAnuncio = $result[0]["id"];
                $p = $o->getAdapter()->fetchAll($o->getPreguntas($idEmpresa, $idAnuncio));
                $preguntas = $doc->createElement("preguntas");
                $r->appendChild($preguntas);
                foreach ($p as $i=>$v) {
                    $this->_attayToXml($doc, $preguntas, $v, "p".$i);
                }
                break;
            }
            $xml = $doc->saveXML();
        } catch (Exception $e) {
            $doc = new DOMDocument();
            $root = $doc->createElement("result");
            $this->_insertStatus($root, $doc, self::API_ERROR_APP);
            $xml = $doc->saveXML();
        }
        echo $xml;
    }
    
    public function loginApplicantAction() 
    {
        $xml="";
        try{
            $email = $this->_getParam("email");
            $pswd = $this->_getParam("pswd");
            $doc = new DOMDocument();
            $root = $doc->createElement("result");
            
            //si no se le manda alguno de los parametros.
            if ($email=='NULL' || $pswd=='NULL') {
               $this->_insertStatus($root, $doc, self::API_ERROR_APP);
               $doc->appendChild($root);
            } else {
                $o = new Application_Model_Api();
                $result = $o->checkLogin($email, $pswd);
                
                if (count($result)>0) {
                    $this->_insertStatus($root, $doc, self::API_OK);
                    $doc->appendChild($root);
                    foreach ($result as $index=>$value) {
                        $this->_attayToXml($doc, $root, $value, "user");
                        break;
                    }   
                } else {
                    $this->_insertStatus($root, $doc, self::API_ERROR_QUERY);
                    $doc->appendChild($root);
                }
            }
            $xml = $doc->saveXML();
        } catch (Exception $e) {
            $doc = new DOMDocument();
            $root = $doc->createElement("result");
            $this->_insertStatus($root, $doc, self::API_ERROR_APP);
            $xml = $doc->saveXML();
        }
        echo $xml;
    }
    
    public function getApplicationsByUserAction() 
    {
        $xml="";
        try{
            $idPostulante = $this->_getParam("idpostulante");
            $doc = new DOMDocument();
            $root = $doc->createElement("result");
            if ($idPostulante=='NULL') {
                $this->_insertStatus($root, $doc, self::API_ERROR_APP);
                $doc->appendChild($root);
            } else {
                $o = new Application_Model_Api();
                $result = $o->getAdapter()->fetchAll($o->listApplicantByUser($idPostulante));
                $this->_insertStatus($root, $doc, self::API_OK);
                $doc->appendChild($root);
                foreach ($result as $index=>$value) {
                    $r = $this->_attayToXml($doc, $root, $value, "job");
                }   
            }
            $xml = $doc->saveXML();
        } catch (Exception $e) {
            $doc = new DOMDocument();
            $root = $doc->createElement("result");
            $this->_insertStatus($root, $doc, self::API_ERROR_APP);
            $xml = $doc->saveXML();
        }
        echo $xml;
    }
    
    public function postularAction() 
    {
        
    }
    
    public function _attayToXml($doc, $contenedor, $arreglo, $nombre)
    {
        $root = $doc->createElement($nombre);
        foreach ($arreglo as $index=>$value) {
            $telement = $doc->createElement($index);
            $telement->appendChild($doc->createTextNode($value));
            $root->appendChild($telement);
        }
        $contenedor->appendChild($root);
        return $root;
    }

    public function _insertStatus($root, DOMDocument $doc, $status)
    {
        $telement = $doc->createElement('status');
        $telement->appendChild($doc->createTextNode($status));
        $root->appendChild($telement);
    }


    public function postAction()
    {
        $this->getResponse()
            ->setHttpResponseCode(404)
            ->appendBody("page not found");
    }

    public function getAction()
    {
        $this->getResponse()
            ->setHttpResponseCode(404)
            ->appendBody("page not found");
    }

    

    public function putAction()
    {
        $this->getResponse()
            ->setHttpResponseCode(503)
            ->appendBody("unable to process put requests. Please try later");

    }

    public function deleteAction()
    {
        $this->getResponse()
            ->setHttpResponseCode(204);
    }
    
    
}