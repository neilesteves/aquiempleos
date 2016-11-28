<?php


class App_View_Helper_Headerbuttons extends Zend_View_Helper_HtmlElement
{
    public function Headerbuttons($modulo, $auth, $class = null)
    {

        if ($class=='class') {
          return  $this->Classbuttons($modulo);
        } else {

            $url = Zend_Controller_Front::getInstance()->getRequest()->getParams();
            $fiuter = new App_Util_Filter();
            if ($modulo == "empresa") {
                $hrefMod = '#loginP';
                $titleMod = 'Ingrese';                
            } else {
                $hrefMod = '#loginP';
                $titleMod = 'Ingresa';
            }

            $registrar = ($modulo == "empresa") ? 'Regístrese' : 'Regístrate';
            $welcomeHTML = '';
            
            if (isset($auth['postulante'])) {
                $welcome = ($auth['postulante']['sexo']== 'M') ? 'Bienvenido' : 'Bienvenida';
                $welcomeHTML =  $welcome .', <a href="/mi-cuenta">'.$fiuter->escape(ucfirst($auth['postulante']['nombres'])) .' '. $fiuter->escape(ucfirst($auth['postulante']['apellido_paterno'])) .' '. $fiuter->escape(ucfirst($auth['postulante']['apellido_materno'])) . '</a>';
            } elseif (isset($auth['empresa'])) {
                $membresia='';
                if (isset($auth['empresa']['membresia_info']['membresia']['id_membresia'])) { 
                    $membresia = $auth['empresa']['membresia_info']['membresia']['id_membresia'];
                }
                $welcomeHTML = 'Bienvenido(a), <a href="/empresa/mi-cuenta">'.$fiuter->escape(ucfirst($auth['usuario-empresa']["nombres"]))." ".$fiuter->escape(ucfirst($auth['usuario-empresa']["apellidos"]))."</a><br/><span>( ".$fiuter->escape($auth['empresa']["nombre_comercial"]).")</span><br/><span>".
                $this->NombreMem($fiuter->escape($membresia))."</span>"; 
            }

            if ($url['controller']!='auth') {
                  if (is_null($auth)) { 
                      return  '
                          <li><button type="button" class="btn btn-default btn_register">'.$registrar.'</button></li>
                          <li><a href="'.$hrefMod.'" title="" id="btnLogin" class="login_modal btn btn-secondary">'.$titleMod.'</a></li>';
                  } else {
                      return'
                          <li class="welcome-name">'.$welcomeHTML.'</li>
                         <li class="logout"><a href="'.$this->url(array(), 'logout', true).'">Salir</a></li>';
                  }
            }

        }
    }
        
        
    public function url(array $urlOptions = array(), $name = null, $reset = false, $encode = true)
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        return $router->assemble($urlOptions, $name, $reset, $encode);
    }
    
    
    public function NombreMem($id)
    {
        $modelMembresia = new Application_Model_Membresia;
        $nombreMembresia = $modelMembresia->getNombreMembresia($id);
        
        if (is_null($nombreMembresia) || empty($nombreMembresia)) {
            $nombreMembresia = 'Cuenta gratuita';
        }
        
        return $nombreMembresia;
    }
    
    
    public function Classbuttons($modulo)
    {
        $dot='';
        if ($modulo== "empresa") {
           $dot = " dotEmp";
        } 
        return $dot;
        
    }
}
