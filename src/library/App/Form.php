<?php

/**
 * Description of Form
 *
 * @author Usuario
 */
class App_Form extends Zend_Form
{
   protected $_config;
   protected $_mensajeRequired = 'Campo Requerido';

   public function init()
   {
     $this->_config = Zend_Registry::get("config");
   }

   public function getMessageError()
   {
      $err=array();
           
      foreach ($this->getMessages() as $element=> $error)
      {
        
        if( isset( $this->errors[$element])  && !is_array($this->errors[$element]))
        {
          $error_elements = $this->errors[$element];
          foreach ($error_elements as $key => $value ) {
            $error_msg= $value;
          }
        }else {
          foreach ($error as $value => $key) {
            $error_msg = isset($this->errors[$value]) ?
              $this->errors[$value] :
              $key;
          }
        }          

        $err[] = array(
         'element' => $element,
         'message' => $error_msg
        );
      }
      if( count($err) > 0 ) {
        return $err[0];
      }
      return FALSE;
   }

   public function getDataModel( $data, $arr ) {
     $map = array();
     foreach( $arr as $field => $ref) {
        if( !empty($ref) && !empty($data[$ref] ) )
         $map[$field] = $data[$ref];
     }
     return $map;
   }

}
