<?php

class App_View_Helper_AvisoExtraCargo extends Zend_View_Helper_HtmlElement {

    protected $dataAnuncio;
    public $view;

    public function AvisoExtraCargo($aviso, $retornarMontos = false) {
        $this->dataAnuncio = $aviso;
        $this->view = Zend_Layout::getMvcInstance()->getView();
        if($aviso['tipo']==Application_Model_Compra::TIPO_DESTACADO)
            return $this->retornarHtmlDestacado();
        
        if ($retornarMontos) {
            return $this->retornarMontos();
        } else {
            return $this->retornarHtml();
        }

        
    }
    
    public function retornarMontos()
    {
        $montos = $this->getCalcularMontos($this->dataAnuncio);
        return $montos;
    }

    /*
     * aptitus =_ muestra etracargos sinaviso
     * talan no muestra extraCargosd
     * si es aptitus_talan muestra extra cargos con elsiguiente mensaje
     */
    public function retornarHtml() {
        $config = Zend_Registry::get('config');
        $moneda = $config->app->moneda;
        $extraCargos = "";
        if (count($this->dataAnuncio["extracargos"]) > 0) {

            if ($this->dataAnuncio['medioPublicacion'] != 'talan') {
                $extraCargosAdicional = '<div class="price_detail row"><span class="txt_small flt_left">El precio incluye I.G.V.</span><div class="price"><span class="first">Precio: </span>'.$moneda.' <span>' . $this->dataAnuncio["tarifaPrecio"] . ' </span></div></div>';

                $extraCargosAdicional .= '<h6 class="mT20">Agregue destaques para el impreso</h6>' . $this->getAvisoAptitus();

                foreach ($this->dataAnuncio["extracargos"] as $i => $item) {
                    
                    $extraCargosAdicional .= '
                    <label class="ioption">
                        <label for="xc_' . $i . '">' . $item['nombreBeneficio'] . ' ('.$moneda.' ' . $item['precioExtracargo'] . ')</label>
                        <input value="' . $item['precioExtracargo'] . '" name="xc_' . $i . '" id="xc_' . $i . '" type="checkbox" class="checkEmpP4" rel="' . $item['precioExtracargo'] . '"/>
                        <a rel="' . MEDIA_URL . '/images/empresa/extracargos/' . $item['imagen'] . '" class="winModal noScrollTop imgExtraCargos mL20" href="#ejemploext">Ver Ejemplo</a>
                    </label>';
                }
            }
        }
        $aviso=($this->dataAnuncio["tipo"]=='membresia')?'':'<a rel="' . $this->view->url(array('slug' => $this->dataAnuncio["slug_anuncio"], 'url_id' => $this->dataAnuncio["url"], 'id' => $this->dataAnuncio["anuncioId"]), 'avisoEmpresa', true) . '" href="#winVerProceso" class="winModal">Mire su aviso web</a>';
        if($this->dataAnuncio["tipo"]=='membresia'){
        $monto= $this->getCalcular($this->dataAnuncio);
        $extraCargos .= '<div id="totalAsideP4Emp" class="alignR">
                            <span class="left viewLinkEmpB">
                            '.$aviso.'
                            </span>
                            '.$monto.' 
                            </div>';
        return $extraCargos;
        }else{
        $extraCargos .= '<span class="txt_small flt_left">El precio incluye I.G.V.</span><div class="price"><span class="first">Precio: </span>'.$moneda.' <span id="priceTotP4" data-number="'. $this->dataAnuncio["data-number"] .'">' . $this->dataAnuncio["tarifaPrecio"] . '</span></div>';

        $verAviso = '<p class="row"><a rel="' . $this->view->url(array('slug' => $this->dataAnuncio["slug_anuncio"], 'url_id' => $this->dataAnuncio["url"], 'id' => $this->dataAnuncio["anuncioId"]), 'avisoEmpresa', true) . '" href="#winVerProceso" class="winModal view-more">Mire su aviso web</a></p>';

        return $verAviso . (isset($extraCargosAdicional) ? $extraCargosAdicional : '') .'<div class="price_detail row">' . $extraCargos . '</div>';
        }
        
     }
    
    protected function getCalcular($datos){
        $this->_config = Zend_Registry::get('config');
        $moneda = $this->_config->app->moneda;
        $subtotal= $datos["tarifaPrecio"] ;
        $igv= $datos["tarifaPrecio"]* $this->_config->adecsys->igv;
        $total=($subtotal+$igv);
        return ($datos["tipo"]=='membresia')?'
        <div class="textCntRelAsid spanDVF right"><span class="textRelAsid textAVFD">Sub</span> <span id="priceEmPP4" class="priceP4 priceNMV">'.$moneda.'<span id="priceTotP4" >' .number_format($subtotal,2 ). '</span></span></div>
        <div class="textCntRelAsid spanDVF right"><span class="textRelAsid textAVFD">Igv</span> <span id="priceEmPP4" class="priceP4 priceNMV">'.$moneda.'<span id="priceTotP4" >' . number_format($igv,2). '</span></span></div>
        <div class="textCntRelAsid spanDVF right"><span class="textRelAsid textAVFD">Total</span> <span id="priceEmPP4" class="priceP4 priceNMV">'.$moneda.'<span id="priceTotP4" >' .number_format($total,2) . '</span></span></div>         
':'<div class="textCntRelAsid spanDVF right"><span class="textRelAsid textAVFD">Total</span> <span id="priceEmPP4" class="priceP4 priceNMV">'.$moneda.'<span id="priceTotP4" data-number="'.$datos["data-number"].'">' . $datos["tarifaPrecio"] . '</span></span></div>';
       
    }
    
    protected function getCalcularMontos($datos)
    {
        $this->_config = Zend_Registry::get('config');
        $subtotal = $datos['tarifaPrecio'] ;
        $igv = $datos['tarifaPrecio'] * $this->_config->adecsys->igv;
        $total = ($subtotal + $igv);
        
        $montos = array(
            'subtotal' => number_format($subtotal,2 ),
            'igv' => number_format($igv,2),
            'total' => number_format($total,2 ),
        );
        
        return $montos;       
    }

    protected function getAvisoAptitus() {
        $aviso_msg = '';
        if ($this->dataAnuncio['medioPublicacion'] == 'aptitus y talan') {
            $aviso_msg .= '<span class="txtTarjM bold">Los destaques solo aplican para AquiEmpleos del diario La Prensa.</span><br><br>';
        }
        return $aviso_msg;
    }
    public function retornarHtmlDestacado() {
        $config = Zend_Registry::get('config');
        $moneda = $config->app->moneda;
        $extraCargos = "";
        $totalExtraCargos = 0;
        if (count($this->dataAnuncio["extracargos"]) > 0) {

            if ($this->dataAnuncio['medioPublicacion'] != 'talan') {
                $extraCargosAdicional = '<div class="price_detail row"><span class="txt_small flt_left">El precio incluye I.G.V.</span><div class="price"><span class="first">Precio: </span>'.$moneda.' <span class="through">' . $this->dataAnuncio["tarifaPrecio"] . ' </span></div></div>';

                $extraCargosAdicional .= '<h6 class="mT20"></h6>' . $this->getAvisoAptitus();

                foreach ($this->dataAnuncio["extracargos"] as $i => $item) {
                    $precioExtracargo = number_format($this->dataAnuncio["tarifaPrecio"]*$item['valorExtracargo']/100,2);
                    $extraCargosAdicional .= '
                    <label class="">
                        <label for="xc_' . $i . '">' . $item['nombreBeneficio'] . ' ('.$moneda.' ' . $precioExtracargo . ')</label>
                        <input value="' . $precioExtracargo . '" name="xc_' . $i . '" id="xc_' . $i . '" type="hidden"/>
                    </label>';
                    $totalExtraCargos+=$precioExtracargo;
}
            }
        }
        $tarifaPrecioNeto = number_format($this->dataAnuncio["tarifaPrecio"]-$totalExtraCargos,2);
        $extraCargos .= '<span class="txt_small flt_left">El precio incluye I.G.V.</span><div class="price"><span class="first">Precio: </span>'.$moneda.' <span id="priceTotP4" data-number="'. $this->dataAnuncio["data-number"] .'">' . $tarifaPrecioNeto . '</span></div>';

        $verAviso = '<p class="row"><a rel="' . $this->view->url(array('slug' => $this->dataAnuncio["slug_anuncio"], 'url_id' => $this->dataAnuncio["url"], 'id' => $this->dataAnuncio["anuncioId"]), 'avisoEmpresa', true) . '" href="#winVerProceso" class="winModal view-more">Mire su aviso web</a></p>';

        return $verAviso . (isset($extraCargosAdicional) ? $extraCargosAdicional : '') .'<div class="price_detail row">' . $extraCargos . '</div>';        
        
     }

}