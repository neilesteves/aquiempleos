<?php

class App_Controller_Action_Helper_Api 
    extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * 
     * Agrega usuarios para usar el API
     * @param array $postData
     */
    
    public function insertarUsuario($postData)
    {
        $fechaIni = null;
        if (isset($postData['fecha_ini']) && $postData['fecha_ini'] != '') {
            $fechaIni = date('Y-m-d', strtotime(str_replace('/', '-', $postData['fecha_ini'])));
        }
        
        $fechaFin = null;
        if (isset($postData['fecha_fin']) && $postData['fecha_fin'] != '') {
            $fechaFin = date('Y-m-d', strtotime(str_replace('/', '-', $postData['fecha_fin'])));
        }
        if ($postData['domain'] != null) {
            $postData['ip'] = $this->getRealIp($postData['domain']);
        } else {
            $postData['ip'] = null;
        }
        $empresaModel = new Application_Model_Empresa();
        $empresa = $empresaModel->getEmpresaByEmail($postData['usuario'], '');
        $apiModel = new Application_Model_Api();
        $apiModel->insert(
            array (
                'force_domain' => $postData['force_domain'], 
                'domain' => $postData['domain'], 
                'ip' => $postData['ip'], 
                'username' => substr(md5($postData['usuario']), 0, 16), 
                'password' => hash('crc32', base64_encode(md5(rand()))), 
                'mensaje' => substr(md5(rand()), 0, 5), 
                'vigencia' => $postData['vigencia'], 
                'fecha_ini' => $fechaIni, 
                'fecha_fin' => $fechaFin, 
                'estado' => $postData['estado'], 
                'usuario_id' => $empresa['idempresa'], 
                'fecha_registro' => date('Y-m-d H:i:s')
            )
        );
    }
    
    public function getRealIp($domain)
    {
        $domain = str_replace("http://", "", $domain);
        $domain = str_replace("www.", "", $domain);
        $domain = str_replace("/", "", $domain);
        $dnsData = @dns_get_record($domain);
        
        if (!isset($dnsData) || $dnsData == false) {
            return false;
        }
        
        $dataIp = array();
        
        foreach ($dnsData as $dns) {
            if (isset($dns['ip'])) {
                $dataIp[] = $dns['ip'];
            }
        }
        $strIp = "";
        for ($i = 0; $i < count($dataIp) - 1; $i++) {
            $strIp .= $dataIp[$i].'|';
        }
        if (isset($dataIp[count($dataIp)-1])) {
            $strIp .= $dataIp[count($dataIp)-1];
        }
        return $strIp;
    }
    
    public function actualizarUsuario($postData)
    {
        $fechaIni = null;
        if (isset($postData['fecha_ini']) && $postData['fecha_ini'] != '') {
            $fechaIni = date('Y-m-d', strtotime(str_replace('/', '-', $postData['fecha_ini'])));
        }
        
        $fechaFin = null;
        if (isset($postData['fecha_fin']) && $postData['fecha_fin'] != '') {
            $fechaFin = date('Y-m-d', strtotime(str_replace('/', '-', $postData['fecha_fin'])));
        }
        
        if ($fechaIni != "" && $fechaFin != "") {
            $postData['vigencia'] = 1;
        }
        
        if ($postData['domain'] != null) {
            $postData['ip'] = $this->getRealIp($postData['domain']);
        } else {
            $postData['ip'] = null;
        }
        
        if ($postData['force_domain'] != 1) {
            $postData['ip'] = null;
        }
        
        if (!isset($postData['usuario_id'])) {
            $empresaModel = new Application_Model_Empresa();
            $empresa = $empresaModel->getEmpresaByEmail($postData['usuario'], '');
            $idEmp = $empresa['id'];
        } else {
            $idEmp = $postData['usuario_id'];
        }
        
        $apiModel = new Application_Model_Api();
        $newDataPost = array (
            'force_domain' => $postData['force_domain'], 
            'domain' => $postData['domain'], 
            'ip' => $postData['ip'],  
            'fecha_ini' => $fechaIni, 
            'fecha_fin' => $fechaFin, 
            'vigencia' => $postData['vigencia'],  
            'usuario_id' => $idEmp, 
            'fecha_registro' => date('Y-m-d H:i:s'), 
            'fecha_modificacion' => date('Y-m-d H:i:s'), 
        );
        
        if (isset($postData['estado'])) {
            $newDataPost['estado'] = "vigente"; 
            if($newDataPost['estado']=='dadobaja'){
                $newDataPost['estado']==$newDataPost['estado'];
            }
        }
       
        
        $apiModel->update(
            $newDataPost, 
            $apiModel->getAdapter()->quoteInto('id = ?', $postData['idUsuApi'])
        );
    }
    
    
    public function CreaRegistroApi($id){
                 $modelEmpresa = new Application_Model_Empresa();
                 $modelApi = new Application_Model_Api();
                 
                 $fechainicio = date('Y-m-d H:i:s');
                 $fechafin = strtotime ('+1 year' , strtotime ( $fechainicio ) ) ;
                 $fechafin = date ( 'Y-m-d H:i:s' , $fechafin );
                 $arrayEmp = $modelEmpresa->getEmpresa($id);
                 $arrayApi = $modelApi->getDatosByIdEmpresa($id);
                 
                 
                 
                 $dataPost = array(
                        'force_domain' => isset($arrayApi['id']) ? $arrayApi['force_domain']
                                : null,
                        'domain' => isset($arrayApi['id']) ? $arrayApi['domain']
                                : null,
                        'fecha_ini' => $fechainicio,
                        'fecha_fin' => $fechafin,
                        'vigencia' => '1',
                        'usuario' => $arrayEmp['email'],
                        'idempresa' => $id,
                        'estado' => 'vigente'
                    );
                
              $this->insertarUsuario($dataPost);
        
        
    }
}