<?php

class App_View_Helper_IcoPostulante extends Zend_View_Helper_HtmlElement
{

    public function IcoPostulante($item)
    {
        $config = Zend_Registry::get("config");
        $dataico =
                    array(
                        $config->dashboard->sug->experiencia=>'icon icon_tie',
                        $config->dashboard->sug->estudios=>'icon icon_tie',
                        $config->dashboard->sug->idiomas=>'icon icon_speak',
                        $config->dashboard->sug->programas  =>'icon icon_mouse',
                            'Logros'=>'icon icon_medal',
                            'hobbies'=>'icon icon_guitar',
                        $config->dashboard->sug->otrosestudios=>'icon icon_books',
                            'Sugerencias'=>'icon icon_reload',
                            'Estudios'=>'icon icon_education',
                            'Datos Personales'=>'icon icon_person2',
                        $config->dashboard->sug->ubicacion  =>'icon icon_arrow',
                    );
        
        return isset($dataico[$item])?$dataico[$item]:'';
    }
  
}