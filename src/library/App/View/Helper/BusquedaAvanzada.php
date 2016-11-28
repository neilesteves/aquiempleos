<?php


class App_View_Helper_BusquedaAvanzada extends Zend_View_Helper_Abstract
{

    protected $aviso;
    protected $_param;
    public function BusquedaAvanzada($param)
    {   
        $this->_param=$param;
       return $this;
    }

    /**
     * 
     * @return string
     */
    public function Salario($item)
    {
        $html='';
        foreach ($item as $k => $v) { 
            $check=in_array($k,  $this->_param->pR) ? "checked" : "";
            $html.='<fieldset class="advanced_search_radio_inline"><label><input id="radRemuneration" name="radRemuneration" value="'.$k.'" type="radio"'.$check.'>'.$v.'</label></fieldset>';
        }
        return $html;
    }

    public function Fecha($fecha)
    {
         $html='';
         foreach ($fecha as $k => $v) {
                $check=in_array($k,  $this->_param->pF) ? "checked" : "";
               $html.='<fieldset class="advanced_search_radio_inline"><label><input id="radDate" name="radDate" value="'.$k.'" type="radio"'.$check.'>'.$v.'</label></fieldset>';
         }
        return $html;

    }

    public function showContent()
    {
        $rules = array(
            Application_Model_AnuncioWeb::ESTADO_DADO_BAJA,
            Application_Model_AnuncioWeb::ESTADO_BANEADO
        );
        
        if (isset($this->aviso['estado']) && in_array($this->aviso['estado'], $rules)) {
            return false;
        }

        return true;
    }

    private function addCintilloFinalizado()
    {
        $rules = array(
            Application_Model_AnuncioWeb::ESTADO_EXTORNADO,
            Application_Model_AnuncioWeb::ESTADO_VENCIDO,
            Application_Model_AnuncioWeb::ESTADO_DADO_BAJA,
            Application_Model_AnuncioWeb::ESTADO_PENDIENTE_PAGO,
            Application_Model_AnuncioWeb::ESTADO_BANEADO
        );
        if (isset($this->aviso['proceso_activo']) && $this->aviso['proceso_activo'] == '0') {
            return true;
        }
        if ($this->aviso['cerrado'] == '1') {
            return true;
        }
        
        if (isset($this->aviso['estado']) && in_array($this->aviso['estado'], $rules)) {
            return true;
        }

        if ( $this->aviso['cerrado'] == '1'   && in_array($this->aviso['estado'], $rules)) {
            return true;
        }

        return false;
    }
    private function addCintilloFinalizadoFichaAviso(){
        if ($this->aviso['online'] != '1') {
            return true;
        }
    }
    private function addCintilloBaneado()
    {
        $rules = array(
            Application_Model_AnuncioWeb::ESTADO_BANEADO
        );
        if (in_array($this->aviso['estado'], $rules)) {
            return true;
        }
        return false;
    }
    public function Totales($total){
        $data='';
        $html='';
        $ntotal=  explode(',', number_format($total));
        for ($i = 0; $i < count($ntotal); $i++) {
           for ($index = strlen($ntotal[$i]); $index > 0 ; $index--) {           
            $data= substr($ntotal[$i], -$index,1); 
            $html.='<span class="number">'.$data.'</span>';
            
            }
            $html.='<span>,</span>';
        }       
        return  substr($html, 0, -14);
    }

}