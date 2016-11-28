<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class App_View_Helper_Listarsubnivel extends Zend_View_Helper_Abstract
{
  
    public function Listarsubnivel($itemId,$parametro,$metodo='',$true='')
    {  
      $url=   Zend_Controller_Front::getInstance()->getRequest()->getParams();
        if(in_array($itemId,array(4,8,10,13) )){
            
            $subnivel= $this->SubnivelEstudio($itemId);
            //var_dump($subnivel);
        foreach ($subnivel as $value => $key) {
            //$i++;
            if($parametro=='NIVEL DE ESTUDIOS'){
            echo ' <label class="ioption">
                 <input value="'.$itemId.',' . $key["id"].'" name="filtroE1" type="checkbox" class="'.$this->checbuscador($url).'">
                
                     <span> '.$key['nombre'].'   </span>
                       
                </label>    

                ';
            }
        }
                  

        }
    
        
        /**/
    }
    
    public function SubnivelEstudio($id){
        $db =  Zend_Db_Table::getDefaultAdapter();
        $sql = $db->select()->from(array('ne' => 'nivel_estudio'), array('id', 'nombre'))
               ->where("ne.padre != ''")
               ->where("ne.padre <> ?", 9)
               ->order('nombre');
       if(!empty($id))
          $sql->where("ne.padre LIKE ?", "%$id%");
       $data=  $db->fetchAll($sql);
       return $data ;
      
    }
    
    public function TipoGet($metodo,$item,$key,$i){
        return '
    <a href="#" >   
    <input rel="'.$metodo.'/'.$item['id'].',' . $key["id"].'" id="filtro'.$i. '" name="filtroE1" type="checkbox" style=" position: initial; "  class="checkN ">
     </a>';
    }
     public function Tipoajax($metodo,$item,$key,$i){
        return '<input rel="'.$metodo.'/'.$item.',' . $key.'" id="filtro'.$i. '" name="filtroE1" type="checkbox" style=" position: initial; "  class="checkN checkbuscador">
';
    }
    
    public function checbuscador($url){
        if($url['controller']=='buscador-aptitus'){
            return '';
        }else{
            return 'checkN checkbuscador' ;
        }
    }
    
}
