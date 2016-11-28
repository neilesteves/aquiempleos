<?php

class Empresa_AjaxController extends App_Controller_Action_Empresa
{
    
  public function envioCorreoMembresiaAction()
  {
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setNoRender();

    $config = Zend_Registry::get('config');
    $email = $config->empresa->membresia->email->contacto;
    $data = $this->_getAllParams();  
    
    $response = array();
    $status = 0;
    $msg = '';
    
    if ($this->_hash->isValid($this->_getParam('hash', ''))) {
      $status = $this->sendMailFromCorreoMembresia($data, $email);
      $msg = ($status==0)?"No se pudo realizar el envío":"Se envió la consulta";
    } else {
      $status = 0;
      $msg = "Intente nuevamente";
    }
    $response['status'] = $status;
    $response['msg'] = $msg;
    $response['token'] = CSRF_HASH;
    $this->_response->appendBody(Zend_Json::encode($response));
  }
  
  private function sendMailFromCorreoMembresia($data, $email)
  {
    try {
      //XSS
      $filter = new Zend_Filter_StripTags;
      foreach ($data as $key => $value) {
          $data[$key] = $filter->filter($value);
      }

      $data['tipo'] = ucfirst($data['hidMembresia']);
      $data['texto'] = 'la';
      $data['empresa'] = $data['txtCompany'];
      $data['contacto'] = $data['txtContact'];
      $data['telefono'] = $data['txtPhone'];
      $data['consulta'] = $data['txaMessage'];
      $data['correo'] = $data['txtEmail'];
      $data['to'] = $email;

      if ($data['hidMembresia'] == '') {
        $data['texto'] = 'una';
        $data['tipo'] = '';
      }
      $this->_helper->Mail->contactarMembresia($data);
      
      return 1;
    } catch (Exception $ex) {
      
      return 0;
    }
  }
}