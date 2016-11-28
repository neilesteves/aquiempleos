<?php

class App_View_Helper_AnuncioHelper extends Zend_View_Helper_Abstract
{

	 protected $aviso;

	 public function AnuncioHelper( $aviso )
	 {
		  if(empty($aviso))
				return $this;

		  $this->aviso = $aviso;

		  return $this;
	 }

	 /**
	  *
	  * @return string
	  */
	 public function divCintillo()
	 {
		  if($this->addCintilloFinalizado()) {
				return "<div id='end-message'></div>";
		  }

		  return null;
	 }

	 public function divCintilloNew( $url )
	 {

		  if(preg_match('/(?i)msie 8/', $_SERVER['HTTP_USER_AGENT'])) {
				$view = new App_View_Helper_E();
				$url = $view->E()->getLastCommitE8('/static/main/img/job_finished.png');
		  }
		  if($this->addCintilloFinalizado()) {
				return '<div class="job_expired_box">
						  <img src=' . '"' . $url . '"' . '/>' .
						  '</div>';
		  }

		  //return false;
	 }

	 public function showBtnPostular()
	 {
		  $rules = array(
				Application_Model_AnuncioWeb::ESTADO_EXTORNADO,
				Application_Model_AnuncioWeb::ESTADO_VENCIDO,
				Application_Model_AnuncioWeb::ESTADO_DADO_BAJA,
				Application_Model_AnuncioWeb::ESTADO_BANEADO,
				Application_Model_AnuncioWeb::ESTADO_PENDIENTE_PAGO
		  );

		  if(in_array($this->aviso['estado'], $rules)) {
				return false;
		  }

		  if($this->aviso['cerrado'] == '1') {
				return false;
		  }
//        if ($this->aviso['cerrado']=='1') {
//            return false;
//        }
		  return true;
	 }

	 public function showContent()
	 {
		  $rules = array(
				Application_Model_AnuncioWeb::ESTADO_DADO_BAJA,
				Application_Model_AnuncioWeb::ESTADO_BANEADO
		  );

		  if(isset($this->aviso['estado']) && in_array($this->aviso['estado'], $rules)) {
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
		  if(isset($this->aviso['proceso_activo']) && $this->aviso['proceso_activo'] == '0') {
				return true;
		  }
		  if($this->aviso['cerrado'] == '1') {
				return true;
		  }

		  if(isset($this->aviso['estado']) && in_array($this->aviso['estado'], $rules)) {
				return true;
		  }

		  if($this->aviso['cerrado'] == '1' && in_array($this->aviso['estado'], $rules)) {
				return true;
		  }

		  return false;
	 }

	 private function addCintilloFinalizadoFichaAviso()
	 {
		  if($this->aviso['online'] != '1') {
				return true;
		  }
	 }

	 private function addCintilloBaneado()
	 {
		  $rules = array(
				Application_Model_AnuncioWeb::ESTADO_BANEADO
		  );
		  if(in_array($this->aviso['estado'], $rules)) {
				return true;
		  }
		  return false;
	 }

	 public function Totales( $total )
	 {
		  $data = '';
		  $html = '';
		  $ntotal = explode(',', number_format($total));
		  for ($i = 0; $i < count($ntotal); $i++) {
				for ($index = strlen($ntotal[$i]); $index > 0; $index--) {
					 $data = substr($ntotal[$i], -$index, 1);
					 $html.='<span class="number">' . $data . '</span>';
				}
				$html.='<span>,</span>';
		  }
		  return substr($html, 0, -14);
	 }

	 public function TotalesNew( $total )
	 {
		  $data = '';
		  $html = '';
		  $ntotal = explode(',', number_format($total));
		  for ($i = 0; $i < count($ntotal); $i++) {
				for ($index = strlen($ntotal[$i]); $index > 0; $index--) {
					 $data = substr($ntotal[$i], -$index, 1);
					 $html.='' . $data . '';
				}
		  }
		  var_dump($total, $html);
		  exit;
		  return substr($html, 0, -14);
	 }

	 /**
	  *
	  *
	  */
	 public function validaFavorito( $dataUsuario = array(), $dataAviso = array(), $isAuth = '' )
	 {
		//var_dump($dataAviso);exit;
		  $aviso = array();
		  if(isset($dataUsuario)) {
				if($isAuth === false || $dataUsuario['usuario']->rol === 'postulante') {
					 $class_fav = ($dataAviso['esFav']) ? ' selected' : '';
					 $indexado = (int) $dataAviso['buscamas'];
					 $aviso = array_merge($aviso, $dataAviso);
					 $aviso['class_fav'] = $class_fav;
					 if($dataUsuario != null && $indexado) {
						  return $this->htmFavorito($aviso);
					 }
				}
		  }
	 }

	 public function htmFavorito( $aviso )
	 {
		  return '<button href="javascript:;"
												  class="btn btn_hightlight"
												  data-page="1"
												  data-id="' . $aviso['id'] . '"
												  data-highlight="/avisos-sugeridos/agregar-favoritos-ajax"
												  data-urlaviso="' . $aviso['url_id'] . '" title="Agregar a favoritos">
										  <span class="btn_label"><i class="icon icon_star_hover animated paintStarAnimation.' . $aviso['class_fav'] . '"></i></span>
										  <span class="btn_spinner tiny"></span>
										</button>';
	 }

	 public function postular( $data )
	 {
		  $restul = array(
				'winModal' => 'login_init',
				'area_puesto_slug' => $data->aviso['area_puesto_slug'],
				'nivel_puesto_slug' => $data->aviso['nivel_puesto_slug'],
				'url_id' => $data->aviso['url_id'],
				'postulante' => 'no-logeado',
				'urlPostula' => 'javascript::',
				'modalPostular' => 'javascript::'
		  );
		  if($data->auth) {
				$restul = false;
		  }
		  if($data->auth['usuario']->rol === 'postulante' &&
					 $data->hasPostulado === false &&
					 $data->hasDesPostulado === false) {
				$urlPostula = '/postular';
				$winModal = '';
				if($data->cuestionario) {
					 $urlPostula = '#questionsWM';
					 $winModal = 'show_questions';
				}
				if($data->updateCV) {
					 $urlPostula = '#winUpdateCV';
					 $winModal = 'trigger_modal_not_enough_information';
				}
				$restul = array(
					 'winModal' => $winModal,
					 'area_puesto_slug' => $data->aviso['area_puesto_slug'],
					 'nivel_puesto_slug' => $data->aviso['nivel_puesto_slug'],
					 'url_id' => $data->aviso['url_id'],
					 'postulante' => $data->postulante,
					 'urlPostula' => $data->urlAviso . $urlPostula,
					 'modalPostular' => $data->urlAviso
				);
		  }
		  return $restul;
	 }

}
