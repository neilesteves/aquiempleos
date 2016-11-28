<?php

/* ------------------------------------------------------------------
 * CLASE API CONTROLLER CONTROLA TODAS LAS PETICIONES API REST
 *    todos los parametros con * en la parte derecha son Obligatorios.
 * ------------------------------------------------------------------
 */

class Postulante_ApiController extends App_Controller_Action_Postulante
{
    protected $_usuario;
    protected $_password;
    protected $_mensaje;
    protected $_ip;
    protected $_idEmpresa;

    public function init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        parent::init();
    }

    public function preDispatch()
    {
        $api = $this->_getParam("key");
        if (isset($api)) {

            $llave = "qwerty";
            //desencriptar Key y asignar valores
           /*$usuario = "solman28@hotmail.com";
           $password = md5("123456");
           $aleatorio = rand(11111, 99999);
           $llave = "qwerty";

            echo $password; echo "<br>";
            $enc = $this->encrypt("$usuario$$password$$aleatorio", $llave);
            var_dump($enc);
            */
            //user: solman28@gmail.com password:123456 numeroaleatorio
            //7ODj0tPiq6m3zeHo5tLg0aDX6N6byqOk2tXamKuostPYmqvV29PcmqjZqaauy6Sk36mvmNeYsaKtnKc=
            $encrip = $this->decrypt($api, $llave);
            $arreglo = explode("$", $encrip);
            if (count($arreglo)==3) {
                $this->_usuario  = $arreglo[0];
                $this->_password = $arreglo[1];
                $this->_mensaje  = $arreglo[2];
                $this->_ip  = self::getRealIP();

                //consulta a la Base de datos para sacar idEmpresa
                $this->_idEmpresa = 1;
            } else {
                $this->_response->setRedirect("/error");
            }
        } else {
            $this->_response->setRedirect("/");
        }
    }

    /* ----------------------------------------------------------------------
     *  LISTA DE ANUNCIOS WEB: /api/list-jobs/
     * ----------------------------------------------------------------------
     * Parametros:
     *             key : key encriptada de la empresa *
     *             idp : id del postulante para sacar anuncios web con "ya postulaste"
     *                   en caso ya haya postulado
     *
     *  status:
     *          200: todo OK
     *          401: Error en la consulta
     */
    public function listJobsAction()
    {
        $idpostulante = $this->_getParam("idp", "NULL");
        $client = new Zend_Http_Client("http://devel.aptitus.info/api-rest/list-jobs");
        $client->setAuth($this->_usuario, $this->_password, Zend_Http_Client::AUTH_BASIC);
        $client->setParameterPost('idpostulante', $idpostulante);
        $client->setParameterPost('idempresa', $this->_idEmpresa);
        $result = $client->request(Zend_Http_Client::POST);

        $output = $result->getBody();
        $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
            ->setBody($output);
    }

    /*---------------------------------------------------------------------
     *  VER ANUNCIO WEB :  /api/get-job/
     * --------------------------------------------------------------------
     *
     * Parametros:
     *             key : key encriptada de la empresa *
     *             idp : id del postulante para sacar anuncios web con "ya postulaste"
     *                   en caso ya haya postulado
     *             ida : el url_id de el anuncio web para poder extraer el anuncio web. *
     * status:
     *          200: todo OK
     *          401: Error en la consulta
     */
    public function getJobAction()
    {
        $idpostulante = $this->_getParam("idp", "NULL");
        $idanuncio = $this->_getParam("ida", "NULL");
        $client = new Zend_Http_Client("http://devel.aptitus.info/api-rest/get-job");
        $client->setAuth($this->_usuario, $this->_password, Zend_Http_Client::AUTH_BASIC);
        $client->setParameterPost('idpostulante', $idpostulante);
        $client->setParameterPost('idempresa', $this->_idEmpresa);
        $client->setParameterPost('idanuncio', $idanuncio);
        $result = $client->request(Zend_Http_Client::POST);

        $output = $result->getBody();
        $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
            ->setBody($output);
    }

    /* -----------------------------------------------------------------
     * LOGUEO DE POSTULANTE: /api/login-applicant/
     * -----------------------------------------------------------------
     * Parametros:
     *            key : key encriptada de la empresa *
     *            email:   el email de el postulante que desea loguearse *
     *            pswd :   el password de el usuario que desea loguearse *
     *
     * status:
     *          200: todo OK
     *          401: Error en la consulta
     *          402: Consulta no devolvio nada
     */
    public function loginApplicantAction()
    {
        $email = $this->_getParam("email", "NULL");
        $pass = $this->_getParam("pswd", "NULL");
        $client = new Zend_Http_Client("http://devel.aptitus.info/api-rest/login-applicant");
        $client->setAuth($this->_usuario, $this->_password, Zend_Http_Client::AUTH_BASIC);
        $client->setParameterPost('email', $email);
        $client->setParameterPost('pswd', $pass);
        $result = $client->request(Zend_Http_Client::POST);
        $output = $result->getBody();
        $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
            ->setBody($output);
    }

    /* -----------------------------------------------------------------
     * LOGUEO DE POSTULANTE: /api/get-applications-by-user/
     * -----------------------------------------------------------------
     * Parametros:
     *            key : key encriptada de la empresa *
     *            idp:   el email de el postulante que desea loguearse *
     *
     * status:
     *          200: todo OK
     *          401: Error en la consulta
     *          402: Consulta no devolvio nada
     */
    public function getApplicationsByUserAction()
    {
        $idpostulante = $this->_getParam("idp", "NULL");
        $client = new Zend_Http_Client(
            "http://devel.aptitus.info/api-rest/get-applications-by-user"
        );
        $client->setAuth($this->_usuario, $this->_password, Zend_Http_Client::AUTH_BASIC);
        $client->setParameterPost('idpostulante', $idpostulante);
        $result = $client->request(Zend_Http_Client::POST);
        $output = $result->getBody();
        $this->_response->setHeader('Content-Type', 'text/xml; charset=utf-8')
            ->setBody($output);
    }

    /* -------------------------------------------------------------------------------
     * POSTULAR: /api/postulate/                                 POST
     * Parametros:
     *            key : key encriptada de la empresa *
     *            idp:   id del postulante *
     *            ida :  id del anuncio web *
     *            preguntas : lista de preguntas
     *
     * -------------------------------------------------------------------------------
     */
    public function postulateAction()
    {

    }

    public function indexAction()
    {
       $this->_response->setRedirect("/");
    }
//--------------------------------------------------------------------------------------

    public function encrypt($string, $key)
    {
        $result = '';
        for ($i=0; $i<strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)+ord($keychar));
            $result.=$char;
        }
        return base64_encode($result);
    }

    public function decrypt($string, $key)
    {
        $result = '';
        $string = base64_decode($string);
        for ($i=0; $i<strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)-ord($keychar));
            $result.=$char;
        }
        return $result;
    }

    public static function getRealIP()
    {
        if ( isset ($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '' ) {
            $clientip = (!empty($_SERVER['REMOTE_ADDR']) ) ? $_SERVER['REMOTE_ADDR'] :
            ( ( !empty($_ENV['REMOTE_ADDR']) )?$_ENV['REMOTE_ADDR']:false );
            $entries = split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);
            reset($entries);
            while (list(, $entry) = each($entries)) {
                $entry = trim($entry);
                if ( preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $entry, $iplist) ) {
                $privateip = array(
                '/^0\./',
                '/^127\.0\.0\.1/',
                '/^192\.168\..*/',
                '/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
                '/^10\..*/');
                $foundip = preg_replace($privateip, $clientip, $iplist[1]);
                    if ($clientip != $foundip) {
                        $clientip = $foundip;
                        break;
                    }
                }
            }
        } else {
            $clientip =( !empty($_SERVER['REMOTE_ADDR']))?$_SERVER['REMOTE_ADDR']:((!empty($_ENV['REMOTE_ADDR']))?
            $_ENV['REMOTE_ADDR']:false );
        }
        return $clientip;
    }

}
