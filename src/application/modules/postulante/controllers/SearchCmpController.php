<?php

class Postulante_SearchCmpController extends App_Controller_Action_Postulante {

	 protected $_anuncioweb = null;

	 public function init() {
		  parent::init();
		  Zend_Layout::getMvcInstance()->assign(
					 'bodyAttr', array('id' => 'searchPag', 'class' => array(''))
		  );

		  if ($this->_anuncioweb == null) {
				$this->_anuncioweb = new Application_Model_AnuncioWeb();
		  }
	 }

	 public function busquedaAction() {

		  $this->_helper->layout->disableLayout();

		  $rutaLogoDefecto = $this->config->defaultLogoEmpresa->fileName;
		  $verLogoDefecto = (bool) $this->config->defaultLogoEmpresa->enabled;

		  $this->view->logoDefecto = $rutaLogoDefecto;
		  $this->view->verLogoDefecto = $verLogoDefecto;

		  $areas = $this->_getParam($this->config->busqueda->urls->areas, "");
		  $nivel = $this->_getParam($this->config->busqueda->urls->nivel, "");
		  $fechapub = $this->_getParam($this->config->busqueda->urls->fechapub, "");
		  $remuneracion = $this->_getParam($this->config->busqueda->urls->remuneracion, "");
		  $ubicacion = $this->_getParam($this->config->busqueda->urls->ubicacion, "");
		  $urlid = $this->_getParam($this->config->busqueda->urls->urlid, "");
		  $query = urldecode($this->_getParam($this->config->busqueda->urls->query, ""));
		  $query = str_replace("<", "", $query);
		  $query = str_replace(">", "", $query);
		  $limpia = new Zend_Filter_StripTags;
		  $query = $limpia->filter($query);

		  $filtros = array($this->config->busqueda->urls->areas => $areas,
				$this->config->busqueda->urls->nivel => $nivel,
				$this->config->busqueda->urls->fechapub => $fechapub,
				$this->config->busqueda->urls->remuneracion => $remuneracion,
				$this->config->busqueda->urls->ubicacion => $ubicacion);


		  //SEO
		  $filterTitle = '';
		  $filterTitle = substr($filterTitle, 0, strlen($filterTitle) - 2);
		  $ubicacionCxense = '';

		  if ($query == '' && $filterTitle == '') {
				$this->view->headTitle()->set(
						  'AquiEmpleos - ' . $this->getConfig()->app->title
				);
		  } else {
				$this->view->headTitle()->set(
						  $query . $filterTitle . ' | AquiEmpleos'
				);
		  }

		  if ($empresa != "") {
				if ($nivel != "" && $ubicacion != "") {
					 $filterTitle = "Las mejores Ofertas de " .
								"trabajo de " . str_replace("-", " ", $nivel) . " en " .
								str_replace("-", " ", $ubicacion) . ". " . $filterTitle;
				}
				$this->view->headMeta()->appendName(
						  "Description", $filterTitle . "- aquiempleos.com"
				);
		  } elseif ($query == "") {
				$this->view->headMeta()->appendName(
						  "Description", $filterTitle .
						  ", Encuentra más ofertas en los clasificados de empleo del" .
						  " Grupo La Prensa"
				);
		  } else {
				$this->view->headMeta()->appendName(
						  "Description", "Las mejores Ofertas de trabajo de: $query - aquiempleos.com"
				);
		  }

		  $facetsAreas = explode("--", $areas);
		  $facetsNivel = explode("--", $nivel);
		  $facetsFechapub = explode("--", $fechapub);
		  $facetsRemuneracion = explode("--", $remuneracion);
		  $facetsUbicacion = explode("--", $ubicacion);

		  $nfacetsAreas = count($facetsAreas);
		  $nfacetsNivel = count($facetsNivel);
		  $nfacetsFechapub = count($facetsFechapub);
		  $nfacetsRemuneracion = count($facetsRemuneracion);
		  $nfacetsUbicacion = count($facetsUbicacion);

		  //Logica de Facets -------------
		  $msgFacets = array();
		  if ($nfacetsAreas > 0 && $facetsAreas[0] != "") {
				if ($nfacetsAreas == 1) {
					 $modelAreas = new Application_Model_Area();
					 $result = $modelAreas->fetchRow(
								$modelAreas->getAdapter()->quoteInto("slug=?", $facetsAreas[0])
					 );
					 $msgFacets["areas"]["msg"] = "Áreas," . $result->nombre;
				} else {
					 $msgFacets["areas"]["msg"] = "Áreas, " . $nfacetsAreas . " Areas";
				}
				$msgFacets["areas"]["filtro"] = $areas;
				$msgFacets["areas"]["param"] = $this->config->busqueda->urls->areas;
		  }
		  if ($nfacetsNivel > 0 && $facetsNivel[0] != "") {
				if ($nfacetsNivel == 1) {
					 $modelNivel = new Application_Model_NivelPuesto();
					 $result = $modelNivel->fetchRow(
								$modelNivel->getAdapter()->quoteInto("slug=?", $facetsNivel[0])
					 );
					 $msgFacets["nivel"]["msg"] = "Nivel," . $result->nombre;
				} else {
					 $msgFacets["nivel"]["msg"] = "Nivel, " . $nfacetsNivel . " Seleccionados";
				}
				$msgFacets["nivel"]["filtro"] = $nivel;
				$msgFacets["nivel"]["param"] = $this->config->busqueda->urls->nivel;
		  }
		  if ($nfacetsFechapub > 0 && $facetsFechapub[0] != "") {
				if ($nfacetsFechapub == 1) {
					 $msgFacets["fechapub"]["msg"] = "Fecha," . $facetsFechapub[0];
				} else {
					 $msgFacets["fechapub"]["msg"] = "Fecha, " . $nfacetsFechapub . " Varios";
				}
				$msgFacets["fechapub"]["filtro"] = $fechapub;
				$msgFacets["fechapub"]["param"] = $this->config->busqueda->urls->fechapub;
		  }
		  if ($nfacetsRemuneracion > 0 && $facetsRemuneracion[0] != "") {
				if ($nfacetsRemuneracion == 1) {
					 if ($facetsRemuneracion[0] == "0") {
						  $msgFacets["remuneracion"]["msg"] = "Remuneración, Sin especificar";
					 } else {
						  $msgFacets["remuneracion"]["msg"] = "Remuneración," . $facetsRemuneracion[0];
					 }
				} else {
					 $msgFacets["remuneracion"]["msg"] = "Remuneración, " .
								$nfacetsRemuneracion . " Seleccionados";
				}
				$msgFacets["remuneracion"]["filtro"] = $remuneracion;
				$msgFacets["remuneracion"]["param"] = $this->config->busqueda->urls->remuneracion;
		  }
		  if ($nfacetsUbicacion > 0 && $facetsUbicacion[0] != "") {
				if ($nfacetsUbicacion == 1) {
					 $modelUbicacion = new Application_Model_Ubigeo();
					 $ubiDes = str_replace('-','',$facetsUbicacion[0]);
					 $result = $modelUbicacion->getDisplayNameUbigeo($ubiDes);
					 $msgFacets["ubicacion"]["msg"] = "Ubicación," . $result["display_name"];
					 //$ubicacionCxense = $result["nombre"];
				} else {
					 $msgFacets["ubicacion"]["msg"] = "Ubicación, " . $nfacetsUbicacion . " Seleccionados";
				}

				$msgFacets["ubicacion"]["filtro"] = $ubicacion;
				$msgFacets["ubicacion"]["param"] = $this->config->busqueda->urls->ubicacion;
		  }

		  $this->view->msg_facets = $msgFacets;
		  $this->view->query = $query;

		  $this->view->menu_sel = self::MENU_AVISOS;
		  $this->view->isAuth = $this->isAuth;
		  $this->view->constantes = $this->config->busqueda->urls;
		  $this->view->recortaraviso = $this->config->busqueda->recortaraviso;

		  $postulanteId = isset($this->auth["postulante"]["id"]) ? $this->auth["postulante"]["id"] : null;

		  $queryFinal = '';

		  //Búsqueda por cxense con buscamas
		  //Caja de Busqueda por puesto empresa etc
		  if ($query != '') {
				$query = str_replace(" ", "+", $query);
				$queryFinal .= '/query/' . strtolower($query);
		  }

		  //Fecha de publicación (No contemplado en el Cxense)
//        if ($fechapub != '')
//            $queryFinal .= '/fecha-publicacion/'.strtolower($fechapub);
		  //Áreas
		  if ($areas != '')
				$queryFinal .= '/area/' . strtolower($areas);

		  //Nivel
		  if ($nivel != '')
				$queryFinal .= '/level/' . strtolower($nivel);

		  //Ubicacion
		  if ($ubicacion != '')
				$queryFinal .= '/location/'.strtolower($ubicacion);
				//$queryFinal .= '/location/' . $ubicacionCxense;


		  $paginadoAviso = $this->config->buscadoravisos->buscador->paginadoavisos;
		  $page = $this->_getParam('page', 1);
		  $paginadoCxense = "/start/0/count/20";
		  if ($page > 1) {
				$ini = $page * $paginadoAviso - $paginadoAviso;
				$fin = $paginadoAviso;
				$paginadoCxense = "/start/" . $ini . "/count/" . $fin;
		  }

		  $query2 = $queryFinal;
		  $queryFinal .= $paginadoCxense;

		  //echo $queryFinal;
		  $config = $this->getConfig();

		  $searchUrlBuscamas = $config->apis->buscamas->searchUrl;
		  $apiKeyBuscamas = $config->apis->buscamas->consumerKey;

		  $url = $searchUrlBuscamas . $apiKeyBuscamas . $queryFinal;
		  $buscaMas = $this->_helper->getHelper('BuscaMas');
		  $resultado = $buscaMas->obtenerResultadoBuscaMas($url);

		  $decode = Zend_Json::decode($resultado);
		  $nroAvisos = count($decode['data']);

		  if ($nroAvisos == 0) {
				$queryFinal = $query2. "/start/0/count/20";
				$url = $searchUrlBuscamas . $apiKeyBuscamas . $queryFinal;
				$resultado = $buscaMas->obtenerResultadoBuscaMas($url);
				$page = 1;
				$decode = Zend_Json::decode($resultado);
		  }

		  $totalPages = ceil($decode['ntotal'] / $decode['count']);

		  $this->view->totalPage = $totalPages;
		  $this->view->pageActual = $page;
		  $this->view->anuncioswebs = $decode['data'];
		  $this->view->nroreg = $decode['ntotal'];

		  $totalAvisos = $decode['ntotal'];
		  $avisosPage = count($decode['data']);

		  $this->view->mostrando = "Mostrando "
					 . $avisosPage . " de "
					 . $totalAvisos . " resultado" . ($totalAvisos != 1 ? "s" : "");
	 }

	 private function _prepare($data, $n) {

		  $dataChunks = array_chunk($data, $n);
		  $nchunks = count($dataChunks);
		  $ocultos = $nchunks > 1 ? array_slice($dataChunks, 1, $nchunks - 1) : array();

		  return array(
				'visible' => count($dataChunks) ? $dataChunks[0] : array(),
				'ocultos' => $ocultos
		  );

	 }

}
