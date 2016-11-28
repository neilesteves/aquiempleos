<?php

class Postulante_BuscarController extends App_Controller_Action_Postulante {

	 protected $_anuncioweb = null;

	 const CARACTERES_IMPRESO = 13;
	 const ERROR_CARACTERES_IMPRESO =
				'Codigo de impreso debe ser menor a 14 caracteres';
	 const ERROR_CODIGO_IMPRESO = 'Debe ingresar numeros y/o letras';

	 public function init() {
		  parent::init();
		  Zend_Layout::getMvcInstance()->assign(
					 'bodyAttr', array('id' => 'searchPag', 'class' => array(''))
		  );

		  Zend_Layout::getMvcInstance()->assign( "robots", "weee");

		  if ($this->_anuncioweb == null) {
				$this->_anuncioweb = new Application_Model_AnuncioWeb();
		  }
	 }

	 public function indexAction()
	 {

		  $this->quitarCapasNoUsadas();
		  $limiteFiltro = 9;
		  $busquedaAdecsys='';
		  // Deberia de aceptar SOLO caracteres y ademas las tildes eñes, etc.
		  $params = $this->_getAllParams();
		//  var_dump($params);exit;
		  $prmAreasnext = $this->getRequest()->getParam('areas');
		  $return= $this->redirecionAreas($prmAreasnext);
		  if($return['return']){
			 $params['areas']=$return['param'];
			 $url = $this->view->url($params, null, true);
			 $this->_redirect($url,array('code'=>301));
		  }
		  $config = $this->getConfig();

		//  $apiKeyBuscamas = $config->apis->buscamas->consumerKey;

		  $paginadoAviso = $this->config->buscadoravisos->buscador->paginadoavisos;
		  $ordenEplaning = $this->config->eplaning->banner->buscador->orden;

		  $buscaMas = new Solr_SolrAviso();

		  $rutaLogoDefecto = 'photoEmpDefault.png';
		  $verLogoDefecto = 'photoEmpDefault.png';

		  $this->view->logoDefecto = $rutaLogoDefecto;
		  $this->view->verLogoDefecto = $verLogoDefecto;
		  $this->view->ordenEplaning = $ordenEplaning;

		  $url = SITE_URL . $this->view->url($this->_getAllParams());
		  $sess = $this->getSession();
		  $sess->lastSearchResultsUrl = $url;

		  $postulanteId = isset($this->auth["postulante"]["id"]) ? $this->auth["postulante"]["id"] : null;
		  $this->view->idPostulante = $postulanteId;

		  if (!$busquedaAdecsys) {
				$codeAdecsys = false;

				if (!$codeAdecsys)    {

					 $areas = $this->_getParam($this->config->busqueda->urls->areas, "");
					 $nivel = $this->_getParam($this->config->busqueda->urls->nivel, "");
					 $fechapub = $this->_getParam($this->config->busqueda->urls->fechapub, "");
					 $remuneracion = $this->_getParam($this->config->busqueda->urls->remuneracion, "");

					 $destacado = $this->_getParam('destacado');

					 $ubicacion = $this->_getParam($this->config->busqueda->urls->ubicacion, "");
					 $query = trim(urldecode($this->_getParam($this->config->busqueda->urls->query, "")));
					 $empresa = $this->_getParam($this->config->busqueda->urls->empresa, "");

					 $discapacidad = $this->_getParam($this->config->busqueda->urls->discapacidad, "");


					 $query = str_replace("<", "", $query);
					 $query = str_replace(">", "", $query);
					 $limpia = new Zend_Filter_StripTags;
					 $query = $limpia->filter($query);

					 $filtros = array($this->config->busqueda->urls->areas => $areas,
						  $this->config->busqueda->urls->nivel => $nivel,
						  $this->config->busqueda->urls->fechapub => $fechapub,
						  $this->config->busqueda->urls->remuneracion => $remuneracion,
						  $this->config->busqueda->urls->ubicacion => $ubicacion,
						  $this->config->busqueda->urls->discapacidad => $discapacidad);


					 //SEO
					 $filterTitle = '';
					 $ubicacionCxense = '';
					 foreach ($filtros as $filtro => $f) {
						  if ($filtro == 'empresa') {
								$f = explode("--", $f);
								foreach ($f as $i) {
									 $i = preg_replace("([0-9]+)", "", $i);
									 $i = preg_replace("(--[a-z0-9]{1,8}+)", "", $i);
									 $filterTitle .= $query ? ', ' : '' . str_replace("-", " ", $i) . ', ';
								}
						  } elseif ($filtro == 'Discapacidad' ) {
								 $filterTitle .=  'Encuentra trabajos para discapacitados ';
						  } else {

						  }
					 }
					 $filterTitle = substr($filterTitle, 0, strlen($filterTitle) - 2);

					 $prmUbicacion = $this->getRequest()->getParam('ubicacion');
					 $prmAreas = $this->getRequest()->getParam('areas');
					 $prmNivel = $this->getRequest()->getParam('nivel');
					 $prmSalario = $this->getRequest()->getParam('remuneracion');
					 $prmEmpresa = $this->getRequest()->getParam('empresa');
					 $prmCarrera = $this->getRequest()->getParam('carrera');
					 $prmDiscapacitado = $this->getRequest()->getParam('discapacidad');

					 $pagina = '';
					 $prmQuery = '';
					 if ( (int)$this->_getParam('page') > 0) {
						  $pagina = ' pag ' . (int)$this->_getParam('page');
					 }

					 $paramConsulta = '';
					 if (!empty($query)) {
						  $paramConsulta = strtolower($query);
						  $prmQuery = ' para ' . ucwords($query);
					 }

					 if (isset($prmSalario)) {
						  if($prmSalario=='mas-10000')
								$prmSalario = ' con salario ' . str_replace('-',' de ',$prmSalario) . ' soles';
						  else
								$prmSalario = ' con salario entre ' . str_replace('-',' y ',$prmSalario) . ' soles';
					 }

					 if (isset($prmCarrera)) {
						  $modelCarrera = new Application_Model_Carrera();
						  $dtc = $modelCarrera->getCarreraBySlug($prmCarrera);
						  $slugPrmCarrera = '';
						  if(count($dtc)) {
								$prmCarrera = $dtc['nombre_carrera'];
								$slugPrmCarrera = $dtc['slug_carrera'];
						  }
					 }

					 if (isset($prmAreas)) {

						  $prmAreas = str_replace('--', ', ', $prmAreas);
						  $prmAreas = str_replace('-', ' ', $prmAreas);
						  $prmAreas = ' en ' . ucwords($prmAreas);
					 }

					 if (isset($prmEmpresa)) {

						  $prmEmpresa = str_replace('--', ', ', $prmEmpresa);
						  $prmEmpresa = str_replace('-', ' ', $prmEmpresa);
						  $prmEmpresa = ' en empresa ' . ucwords($prmEmpresa);
					 }

					 //SEO Title Distrito
					 if (isset($prmUbicacion)) {

						  $distritosExplode = explode('--', $prmUbicacion);
						  $nroDistritos = count($distritosExplode);
						  $validaDistrito = $prmUbicacion;

						  $prmUbicacion = str_replace('--', ', ', $prmUbicacion);

						  if ($nroDistritos >= 3) {
								//Si pertenecen a un mismo departamento muestra el nombre
								//Sino no muestra
								$ubigeoModel = new Application_Model_Ubigeo;
								$validaDistrito = str_replace('--', ',', $validaDistrito);
								$validaDistrito = str_replace('-', ' ', $validaDistrito);
								$validaDistrito = explode(',',$validaDistrito);
								$nroReg = $ubigeoModel->getDepartamentoSEOBuscador($validaDistrito);
								if ($nroReg == $nroDistritos) {
									 $prmUbicacion = ' en ' .Application_Model_Ubigeo::DEPARTAMENTO_CAPITAL;
									 $ciudadUbicacion = Application_Model_Ubigeo::DEPARTAMENTO_CAPITAL;
								} else {
									 $ciudadUbicacion = '';
									 $prmUbicacion = '';
								}
						  } else {
								$prmUbicacion = str_replace('-', ' ', $prmUbicacion);
								$ciudadUbicacion = strtolower($prmUbicacion);
								$prmUbicacion = ' en ' . ucwords($prmUbicacion);


						  }

					 }

					 $paramNivel = '';
					 if (isset($prmNivel)) {
						  $prmNivel = str_replace('--', ', ', $prmNivel);
						  $paramNivel = strtolower($prmNivel);
						  $prmNivel = ' para ' . ucwords($prmNivel);
					 }


					 //SEO DE DISCAPACITADO
					 $prmDiscapacitados = (isset($prmDiscapacitado)) ? ' para discapacitados' : '';

					 /*
					  * Construccion del titulo - SEO
					  */
					 $ceoTitle = trim($prmQuery .'' .$prmUbicacion .'' .$prmAreas .'' .$prmNivel .'' .$prmCarrera .'' .$prmSalario .'' .$prmEmpresa . ''.$prmDiscapacitados);
					 if (empty($ceoTitle)) {
						  $url = $this->getRequest()->getRequestUri();
						  if (stristr($url,'/buscar/empresa')) {
								$this->view->headTitle()->set('Buscar empleos por empresa | EMPLEOS ');
						  } else {
								$this->view->headTitle()->set('Buscar empleos | EMPLEOS');
						  }

					 } else {

						  $soloCarrera = trim($prmQuery .'' .$prmUbicacion .'' .$prmAreas .'' .$prmNivel .'' .$prmSalario .'' .$prmEmpresa);
						  if (empty($soloCarrera) && !empty($prmCarrera)) {

								$como = array('chef','guia-oficial-de-turismo');
								$de = array('teleoperador');

								$enOdDe = in_array($slugPrmCarrera, $como) ? 'como' : ( in_array($slugPrmCarrera, $de) ? 'de' : 'en');

								$this->view->headTitle()->set(
									 'Trabajo '.$enOdDe.' '.$prmCarrera.' | EMPLEOS'
								);
						  } else {

								$soloUbicacion = trim($prmQuery .''.$prmAreas .'' .$prmNivel .'' .$prmCarrera .'' .$prmSalario .'' .$prmEmpresa);
								if (empty($soloUbicacion) && !empty($prmUbicacion)) {

									 $titleUbicacion = array(
										  'lima' => 'Trabajo en Lima',
										  'ica' => 'Trabajo en Ica',
										  'cusco' => 'Trabajos Cusco',
										  'arequipa' => 'Trabajos en Arequipa',
										  'cercado de lima' => 'Trabajos en Lima',
										  'tacna' => 'Empleos Tacna',
										  'lambayeque' => 'Empleos Lambayeque',
										  'cajamarca' => 'Trabajos en Cajamarca',
										  'los olivos' => 'Buscar trabajo en Los Olivos',
										  'cañete' => 'Trabajo en Cañete',
										  'san juan de lurigancho' => 'Trabajo en San Juan de Lurigancho',
										  'ucayali' => 'Trabajo en Pucallpa',
										  'comas' => 'Trabajo en Comas',
										  'piura' => 'Trabajo en Piura'
									 );

									 if (isset($titleUbicacion[$ciudadUbicacion])) {
										  $this->view->headTitle()->set(
												$titleUbicacion[$ciudadUbicacion]. ' | AquiEmpleos'
										  );
									 } else {
										  $this->view->headTitle()->set(
												'Bolsa de trabajo ' .$ceoTitle.'' .$pagina. ' | AquiEmpleos'
										  );
									 }

								} else {

									 $soloNivel = trim($prmQuery .'' .$prmUbicacion .'' .$prmAreas .'' .$prmCarrera .'' .$prmSalario .'' .$prmEmpresa);
									 if (empty($soloNivel) && !empty($prmNivel)) {
										  $tituloNivel = array(
												'practicante' => 'Practicas Preprofesionales',
										  );

										  if (isset($tituloNivel[$paramNivel])) {
												$this->view->headTitle()->set(
													 $tituloNivel[$paramNivel]. ' | AquiEmpleos'
												);
										  } else {
												$this->view->headTitle()->set(
													 'Bolsa de trabajo ' .$ceoTitle.'' .$pagina. ' | AquiEmpleos'
												);
										  }
									 } else {
										  $soloQuery = trim($prmUbicacion .'' .$prmAreas .'' .$prmNivel .'' .$prmCarrera .'' .$prmSalario .'' .$prmEmpresa);
										  if (empty($soloQuery) && !empty($prmQuery)) {

												$tituloQuery = array(
													 'empleos en lima' => 'Empleos en Perú',
													 'oportunidades laborales' => 'Oportunidades Laborales',
													 'trujillo' => 'Empleos Managua',
													 'medio tiempo' => 'Trabajo de medio tiempo',
												);
												if (isset($tituloQuery[$paramConsulta])) {
													 $this->view->headTitle()->set(
														  $tituloQuery[$paramConsulta]. ' | AquiEmpleos'
													 );

												} else {
													 $this->view->headTitle()->set(
														  'Bolsa de trabajo ' .$ceoTitle.'' .$pagina. ' | AquiEmpleos'
													 );
												}

										  } else {
												$this->view->headTitle()->set(
													 'Bolsa de trabajo ' .$ceoTitle.'' .$pagina. ' | AquiEmpleos'
												);
										  }
									 }
								}
						  }
					 }

					 if ($empresa != "") {
						  if ($nivel != "" && $ubicacion != "") {
								$filterTitle = "Las mejores Ofertas de " .
										  "trabajo de " . str_replace("-", " ", $nivel) . " en " .
										  str_replace("-", " ", $ubicacion) . ". " . $filterTitle;
						  }
						  $this->view->headMeta()->appendName(
									 "Description", 'Bolsa de trabajo ' .$ceoTitle.'' .$pagina. " | aquiempleos.com"
						  );
					 } elseif ($query == "") {
						  $this->view->headMeta()->appendName(
									 "Description", $filterTitle .
									 ", Encuentra más ofertas en los clasificados de empleo del" .
									 " Grupo La Prensa"
						  );
					 } else {
						  $this->view->headMeta()->appendName(
									 "Description", "Las mejores Ofertas de trabajo de: $query | aquiempleos.com"
						  );
					 }

				$this->view->query = $query;

				$this->view->menu_sel = self::MENU_AVISOS;
				$this->view->isAuth = $this->isAuth;
				$this->view->constantes = $this->config->busqueda->urls;
				$this->view->recortaraviso = $this->config->busqueda->recortaraviso;

				$queryFinal = '';

				//Búsqueda por cxense con buscamas
				if ($query != '') {
					 $query = str_replace(" ", "+", $query);
					 $queryFinal .= '/query/' . strtolower($query);
				}

				//Variables enviadas a Buscamas
				//Si no viene filtro de FecPub por defecto 30 días
				if (empty($fechapub)) {
					 $fhPub = 'ultimo-mes';
				}

				//Fecha de publicación
				if ($fechapub != '') {
					 $queryFinal .= '/publication_date/'.strtolower($fechapub);
				} else {
					 $queryFinal .= '/publication_date/'.strtolower($fhPub);
				}

				//Pedido
				$stringUrl = '';

				//Áreas
				if ($areas != '') {
					 $queryFinal .= '/area/' . strtolower($areas);
					 $stringUrl .= '/areas/'. strtolower($areas);
				}


				//Nivel
				if ($nivel != '') {
					 $queryFinal .= '/level/' . strtolower($nivel);
					 $stringUrl .= '/nivel/'. strtolower($nivel);
				}


				//Ubicacion
				if ($ubicacion != '') {
					 $queryFinal .= '/location/' . strtolower($ubicacion);
					 $stringUrl .= '/ubicacion/'. strtolower($ubicacion);
				}

					//Discapaciadad
				if ($discapacidad != '') {
					 $queryFinal .= '/discapacidad/1';
				}

				$carreras = $this->_getParam('carrera');
				if ($carreras != '')
					 $queryFinal .= '/carrera/' . strtolower($carreras);

				$company = $this->_getParam('empresa');
				if ($company != '')
					 $queryFinal .= '/company/' . strtolower(trim($company));

				//echo $queryFinal;
				$sendRem = '';
				$remuneracion = $this->_getParam('remuneracion');
				//echo $remuneracion;
				if ($remuneracion != '') {
					 if ($remuneracion == 'sin-esp') {
						  //$queryFinal .= '/smin/0/smax/0';
						  $sendRem = 1;
					 } else if ($remuneracion == '0-750') {
						  $queryFinal .= '/smin/1/smax/750';
						  $sendRem = 2;
					 } else if ($remuneracion == '751-1500') {
						  $queryFinal .= '/smin/751/smax/1500';
						  $sendRem = 3;
					 } else if ($remuneracion == '1501-3000') {
						  $queryFinal .= '/smin/1501/smax/3000';
						  $sendRem = 4;
					 } else if ($remuneracion == '3001-6000') {
						  $queryFinal .= '/smin/3001/smax/6000';
						  $sendRem = 5;
					 } else if ($remuneracion == '6001-10000') {
						  $queryFinal .= '/smin/6001/smax/10000';
						  $sendRem = 6;
					 } else if ($remuneracion == 'mas-10000') {
						  $queryFinal .= '/smin/10001/smax/15000';
						  $sendRem = 7;
					 }

				}

				$page = $this->_getParam('page', 1);
				if(!is_numeric($page))
					 $page = 1;
				$paginadoCxense = "/start/0/count/20";

				if ($page > 1) {
					 $ini = $page * $paginadoAviso - $paginadoAviso;
					 $fin = $paginadoAviso;
					 $paginadoCxense = "/start/" . $ini . "/count/" . $fin;
				}

				$query2 = $queryFinal;
				$queryFinal .= $paginadoCxense;

//            $searchUrlBuscamasBusqueda = $config->apis->buscamas->searchUrl;
			//   $url = $searchUrlBuscamasBusqueda . $apiKeyBuscamas . $queryFinal;

				$resultadoBusqueda = $buscaMas->obtenerResultadoBuscaMas($params);
				$decodeBusqueda = Zend_Json::decode($resultadoBusqueda);
				$nroAvisos = count($decodeBusqueda['data']);

				if ($nroAvisos == 0) {
					 $queryFinal = $query2 . "/start/0/count/20";
					 //$url = $searchUrlBuscamasBusqueda . $apiKeyBuscamas . $queryFinal;
					 $params['page'] = 1;
					 $buscaMas = new Solr_SolrAviso();
					 $resultadoBusqueda = $buscaMas->obtenerResultadoBuscaMas($params);
					 $page = 1;
					 $decodeBusqueda = Zend_Json::decode($resultadoBusqueda);
				}
				//if($decodeBusqueda['ntotal'] == 1)
				  //  $this->_redirect($decodeBusqueda['data'][0]['url']);

				//echo "No reportar como bug, solo se imprme para pruebas: ".$url;

				$totalPages = ceil($decodeBusqueda['ntotal'] / $decodeBusqueda['count']);

				//echo $totalPages;
				//EL tope de avisos en el Buscador es de 25000
				if ($totalPages > 1251)
					 $totalPages = 1251;

				//SEO Canonical
				$url = 'http://'.$_SERVER['SERVER_NAME'].$this->getRequest()->getRequestUri();

				$queryBuscar =substr($url,strlen($url) - 7,7);
				$queryBuscarSin =substr($url,strlen($url) - 6,6);

				$canonical = false;
				if ($queryBuscar == '/q/') {
					 $url = str_replace('/q/', '', $url);
					 $canonical = true;
				}

				 if ($queryBuscarSin == '/q') {
					 $url = str_replace('/q', '', $url);
					 $canonical = true;
				}

				$obtenerUltimoCaracter = substr($url,strlen($url) - 1 ,1);
				if ($obtenerUltimoCaracter == '/') {
					 $url = substr($url,0, strlen($url) - 1);
					 $canonical = true;
				}

				if ($this->_hasParam('page')) {
					 $quitar = '/page/'. $this->_getParam('page');
					 $url = str_replace($quitar, '', $url);
					 $canonical = true;
				}

				if ($canonical)
					 Zend_Layout::getMvcInstance()->assign(array('SEOCanonical' =>
						  '<link rel="canonical" href="'.$url.'" />'));
					 //$this->view->headLink()->headLink(array('href' => $url,'rel' => 'canonical'),'APPEND');

				//SEO Paginación
				$urlPage = 'http://'.$_SERVER['SERVER_NAME'].$this->getRequest()->getRequestUri();
				$obtenerUltimoCaracterPage = substr($urlPage,strlen($urlPage) - 1 ,1);
				if ($obtenerUltimoCaracterPage == '/') {

					 $urlPage = substr($urlPage,0, strlen($urlPage) - 1);

				}

				if ($page > 1 && $page < $totalPages) {

					 $quitar = '/page/'. $page;
					 $num = $page - 1;
					 $urlPage = str_replace($quitar, '', $urlPage);
					 $urlPage .= '/page/'.$num;
					 Zend_Layout::getMvcInstance()->assign(array('SEOPrev' =>
						  '<link rel="prev" href="'.$urlPage.'" />'));

					 $quitar = '/page/'. $num;
					 $num = $page + 1;
					 $urlPage = str_replace($quitar, '', $urlPage);
					 $urlPage .= '/page/'.$num;
					 Zend_Layout::getMvcInstance()->assign(array('SEONext' =>
						  '<link rel="next" href="'.$urlPage.'" />'));

				} else if ($page == 1 && $totalPages > 1){
					 $quitar = '/page/'. $page;
					 $num = $page + 1;
					 $urlPage = str_replace($quitar, '', $urlPage);
					 $urlPage .= '/page/'.$num;
					 Zend_Layout::getMvcInstance()->assign(array('SEONext' =>
						  '<link rel="next" href="'.$urlPage.'" />'));

				} else if ($page == $totalPages && $totalPages > 1) {
					 $quitar = '/page/'. $page;
					 $num = $page - 1;
					 $urlPage = str_replace($quitar, '', $urlPage);
					 $urlPage .= '/page/'.$num;
					 Zend_Layout::getMvcInstance()->assign(array('SEOPrev' =>
						  '<link rel="prev" href="'.$urlPage.'" />'));
				}

				$areasJSON = $decodeBusqueda['filter']['area'];
				$nivelJSON = $decodeBusqueda['filter']['level'];
				$ubicacionJSON = $decodeBusqueda['filter']['location'];
				$companyJSON = $decodeBusqueda['filter']['company_slug'];
				$discapacidadJSON = $decodeBusqueda['filter']['discapacidad'];

			  //ar_dump($decodeBusqueda['filter']);
				$carreraJSON = array();

				$areaValorDesc = $buscaMas->ordenarArray($areasJSON, 'count', true);

				$areaDescDesc = $buscaMas->ordenarArray($areasJSON, 'label', false);

				$nivelValorDesc = $buscaMas->ordenarArray($nivelJSON, 'count', true);
				$nivelDescDesc = $buscaMas->ordenarArray($nivelJSON, 'label', false);

				$ubiValorDesc = $buscaMas->ordenarArrayUbicacion($ubicacionJSON, 'count', true);
				$ubiDescDesc = $buscaMas->ordenarArrayUbicacion($ubicacionJSON, 'label', false);

				$comValorDesc = $buscaMas->ordenarArray($companyJSON, 'count', true);
				$carreraValorDesc = $buscaMas->ordenarArray($carreraJSON, 'count', true);

				$discapacidadValorDesc = $buscaMas->ordenarArray($discapacidadJSON, 'count', true);
				$stringBuscador = '';

				if (count($nivelValorDesc) == 0)
					 if ($nivel != '') {
						  $stringBuscador .= '/nivel/'.strtolower($nivel);
					 }

				if (count($areaValorDesc) == 0)
					 if ($areas != '') {
						  $stringBuscador .= '/areas/'.strtolower($areas);
					 }

				if (count($ubiValorDesc) == 0)
					 if ($ubicacion != '') {
						  $stringBuscador .= '/ubicacion/'.strtolower($ubicacion);
					 }

				$this->view->headScript()->appendScript("var detailSearch = {company:'".  $company."',
					 career: '".$carreras."',
					 other: '".$stringBuscador."'};");

				//Condicional
				$facetsAreas = explode("--", $areas);
				$facetsNivel = explode("--", $nivel);
				$facetsFechapub = explode("--", $fechapub);
				$facetsRemuneracion = explode("--", $remuneracion);
				$facetsUbicacion = explode("--", $ubicacion);
				$facetsCompany = explode("--", $company);
				$facetsCarrera = explode("--", $carreras);
				$facetDiscapacidad= $discapacidadValorDesc;
				$nfacetsAreas = count($facetsAreas);
				$nfacetsNivel = count($facetsNivel);
				$nfacetsFechapub = count($facetsFechapub);
				$nfacetsRemuneracion = count($facetsRemuneracion);
				$nfacetsUbicacion = count($facetsUbicacion);
				$nfacetsCompany = count($facetsCompany);
				$nfacetsCarrera = count($facetsCarrera);
				$nfacetsDiscapacidad = count($facetDiscapacidad);


				$msgFacets = array();
				$nCar = count($areaValorDesc);
				$nNiv = count($nivelValorDesc);
				$nUbi = count($ubiValorDesc);
				$nCom = count($comValorDesc);
				$nCarrera = count($carreraValorDesc);
				$nDiscapacidad = count(explode("--", $prmDiscapacitado));


				if ($nfacetsAreas > 0 && $facetsAreas[0] != "") {
				if ($nCar == 1) {
					 $msgFacets["areas"]["msg"] = "Áreas," . $areaValorDesc[0]['label'];
				}
				else if ($nCar == 0) {
					 $msgFacets["areas"]["msg"] = "Áreas," . "0 Área";
				}
				else if ($nCar > 1) {
					 $msgFacets["areas"]["msg"] = "Áreas," . $nCar. " Seleccionados";
				}
					 $msgFacets["areas"]["filtro"] = $areas;
					 $msgFacets["areas"]["param"] = $this->config->busqueda->urls->areas;
				}

				//Nivel
				if ($nfacetsNivel > 0 && $facetsNivel[0] != "") {
				if ($nNiv == 1) {
					 $msgFacets["nivel"]["msg"] = "Nivel," . $nivelValorDesc[0]['label'];
				}
				else if ($nNiv == 0) {
					 $msgFacets["nivel"]["msg"] = "Nivel, 0 Nivel";
				}
				else if ($nNiv > 1) {
					 $msgFacets["nivel"]["msg"] = "Nivel," . $nNiv. " Seleccionados";
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

				if ($sendRem > 1) {
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
				}

				if ($nfacetsUbicacion > 0 && $facetsUbicacion[0] != "") {
					 if ($nUbi == 1) {
						  $msgFacets["ubicacion"]["msg"] = "Ubicación," . $ubiValorDesc[0]['label'];
					 }
					 else if ($nUbi == 0) {
						  $msgFacets["ubicacion"]["msg"] = "Ubicación, 0 Ubicación";
					 }
					 else if ($nUbi > 1) {
						  $msgFacets["ubicacion"]["msg"] = "Ubicación," . $nUbi. " Seleccionadas";
					 }
						  $msgFacets["ubicacion"]["filtro"] = $ubicacion;
						  $msgFacets["ubicacion"]["param"] = $this->config->busqueda->urls->ubicacion;
				}

				if ($nfacetsCompany > 0 && $facetsCompany[0] != "") {
					 if ($nCom == 1) {
						  $msgFacets["empresa"]["msg"] = "Empresa," . str_replace('-',' ',  strtoupper($comValorDesc[0]['label']));
					 }
					 else if ($nCom == 0) {
						  $msgFacets["empresa"]["msg"] = "Empresa, 0 Empresa";
					 }
					 else if ($nCom > 1) {
						  $msgFacets["empresa"]["msg"] = "Empresa," . $nCom. " Seleccionadas";
					 }
						  $msgFacets["empresa"]["filtro"] = $company;
						  $msgFacets["empresa"]["param"] = $this->config->busqueda->urls->empresa;
				}

				if ($nfacetsCarrera> 0 && $facetsCarrera[0] != "") {
					 if (count($facetsCarrera) == 1) {
						  if (substr(strtoupper($facetsCarrera[0]),0,5) == 'OTROS'){
								$msgFacets["carrera"]["msg"] = "Carrera, Otros";
						  } else {
								$msgFacets["carrera"]["msg"] = "Carrera," . str_replace('-',' ',  strtoupper($facetsCarrera[0]));
						  }

					 }
					 else if (count($facetsCarrera) == 0) {
						  $msgFacets["carrera"]["msg"] = "Carrera, 0 Carrera";
					 }
					 else if (count($facetsCarrera) > 1) {
						  $msgFacets["carrera"]["msg"] = "Carrera," . count($facetsCarrera). " Seleccionadas";
					 }
						  $msgFacets["carrera"]["filtro"] = $carreras;
						  $msgFacets["carrera"]["param"] = 'carrera';
				}
				if($nDiscapacidad) {
					 if(!$nDiscapacidad) {
						  $msgFacets["discapacidad"]["msg"] = "Otros , 0 discapacidad";
					 }
					 if($nDiscapacidad && !empty($discapacidad) ) {
						  $msgFacets["discapacidad"]["msg"] = "Otros, con discapacidad";
						  $msgFacets["discapacidad"]["filtro"] =$discapacidad;
						  $msgFacets["discapacidad"]["param"] = 'discapacidad';
					 }

				}
				$this->view->msg_facets = $msgFacets;

				$arrayFechaPublicacion['visible'] = $decodeBusqueda['filter']['fecha'];
				$arrayDiscapacidad['visible']= $discapacidadValorDesc;
				$arraySalario['visible'] = $decodeBusqueda['filter']['salario'];
				$this->view->dataFiltros = $f = array(
					  'areas' => array(
								'titulo' => 'ÁREA',
								'titulo2' => 'ÁREA',
								'ico' => 'icon_star',
								'pfijof1' => 'AC',
								'pfijof2' => 'FAC',
								'datos' => $this->_prepare($areaValorDesc, $limiteFiltro),
								'datos2' => $this->_prepare($areaDescDesc, $limiteFiltro),
								'param' => $this->config->busqueda->urls->areas,
								'filtro' => $areas,
								'filtros' => $filtros,
								'constantes' => $this->config->busqueda->urls,
								'remuneracion' => $sendRem
						  ),
						  'nivel' => array(
								'titulo' => 'NIVEL',
								'titulo2' => 'NIVEL',
								'ico' => 'icon_medal',
								'pfijof1' => 'SNiv',
								'pfijof2' => 'FNi',
								'datos' => $this->_prepare($nivelValorDesc, $limiteFiltro),
								'datos2' => $this->_prepare($nivelDescDesc, $limiteFiltro),
								'param' => $this->config->busqueda->urls->nivel,
								'filtro' => $nivel,
								'filtros' => $filtros,
								'constantes' => $this->config->busqueda->urls,
								'remuneracion' => $sendRem
						  ),
						  array(
								'titulo' => 'SALARIO',
								'ico' => 'icon_money',
								'pfijof1' => 'Sal',
								'datos' => $arraySalario,
								'param' => $this->config->busqueda->urls->remuneracion,
								'filtro' => $remuneracion,
								'filtros' => $filtros,
								'constantes' => $this->config->busqueda->urls,
								'remuneracion' => $sendRem
						  ),
						  array(
								'titulo' => 'UBICACIÓN',
								'titulo2' => 'UBICACIÓN',
								'ico' => 'icon_position',
								'pfijof1' => 'SUbi',
								'pfijof2' => 'FUbi',
								'datos' => $this->_prepare($ubiValorDesc, $limiteFiltro),
								'datos2' => $this->_prepare($ubiDescDesc, $limiteFiltro),
								'param' => $this->config->busqueda->urls->ubicacion,
								'filtro' => $ubicacion,
								'filtros' => $filtros,
								'constantes' => $this->config->busqueda->urls,
								'remuneracion' => $sendRem
						  ),
						  array(
								'titulo' => 'FECHA',
								'ico' => 'icon_calendar',
								'pfijof1' => 'SFdP',
								'pfijof2' => 'FFdeP',
								'datos' => $arrayFechaPublicacion,
								'param' => $this->config->busqueda->urls->fechapub,
								'filtro' => $fechapub,
								'filtros' => $filtros,
								'constantes' => $this->config->busqueda->urls,
								'remuneracion' => $sendRem
						  ),
						  array(
								'titulo' => 'OTROS',
								'ico' => 'icon_calendar',
								'pfijof1' => 'SFdP',
								'pfijof2' => 'FFdeP',
								'datos' => $arrayDiscapacidad,
								'param' => $this->config->busqueda->urls->discapacidad,
								'filtro' => $discapacidad,
								'filtros' => $filtros,
								'constantes' => $this->config->busqueda->urls,
								'remuneracion' => $sendRem
						  )

				);


				$this->view->totalPage = $totalPages;
				$this->view->pageActual = $page;
				$this->view->anuncioswebs = $decodeBusqueda['data'];

				$this->view->nroreg = $decodeBusqueda['ntotal'];
				$this->view->remuneracion = $sendRem;

				$totalAvisos = $decodeBusqueda['ntotal'];
				$avisosPage = count($decodeBusqueda['data']);
				if(empty($totalAvisos))
					 Zend_Layout::getMvcInstance()->assign(
						  'robots', 'noindex'
					 );
					 $this->view->mostrando = "Mostrando "
						  . $avisosPage . " de "
						  . $totalAvisos . " resultado" . ($totalAvisos != 1 ? "s" : "");


					 try {
						  $busqueda = new Mongo_BusquedaAviso();
						  $datos = array(
								'auth' =>isset($this->auth)?$this->auth:'',
								'cantidad_de_resultados' => $totalAvisos
						  );

						  if (isset($facetsAreas) && !empty($facetsAreas) && count($facetsAreas) > 0) {
								if (!empty($facetsAreas[0]) > 0) {
									 $datos['busqueda']['areas'] = $facetsAreas;
								}
						  }

						  if (isset($facetsNivel) && !empty($facetsNivel) && count($facetsNivel) > 0) {
								if (!empty($facetsNivel[0]) > 0) {
									 $datos['busqueda']['nivel'] = $facetsNivel;
								}
						  }

						  if (isset($facetsFechapub) && !empty($facetsFechapub) && count($facetsFechapub) > 0) {
								if (!empty($facetsFechapub[0]) > 0) {
									 $datos['busqueda']['fechapub'] = $facetsFechapub;
								}
						  }

						  if (isset($facetsRemuneracion) && !empty($facetsRemuneracion) && count($facetsRemuneracion) > 0) {
								if (!empty($facetsRemuneracion[0]) > 0) {
									 $datos['busqueda']['remuneracion'] = $facetsRemuneracion;
								}
						  }

						  if (isset($facetsUbicacion) && !empty($facetsUbicacion) && count($facetsUbicacion) > 0) {
								if (!empty($facetsUbicacion[0]) > 0) {
									 $datos['busqueda']['ubicacion'] = $facetsUbicacion;
								}
						  }

						  if (isset($facetsCompany) && !empty($facetsCompany) && count($facetsCompany) > 0) {
								if (!empty($facetsCompany[0]) > 0) {
									 $datos['busqueda']['company'] = $facetsCompany;
								}
						  }

						  if (isset($facetsCarrera) && !empty($facetsCarrera) && count($facetsCarrera) > 0) {
								if (!empty($facetsCarrera[0]) > 0) {
									 $datos['busqueda']['carrera'] = $facetsCarrera;
								}
						  }
						  if (isset($facetDiscapacidad) && !empty($facetDiscapacidad) && count($facetDiscapacidad) > 0) {
								if (!empty($facetDiscapacidad[0]) > 0) {
									 $datos['busqueda']['discapacidad'] = $facetDiscapacidad;
								}
						  }

						  $busqueda->save($datos);
					 } catch(Exception $e){ }

				}

				$this->view->headScript()->appendFile(
					$this->view->S('/eb/js/buscar.js')
				);
				$this->view->pais = $this->getRequest()->getParam('pais');

				$avanzada = "";
				if(!empty($query))
					 $avanzada.="/".$this->config->busqueda->urls->query."/$query";
				if(!empty($areas))
					 $avanzada.="/".$this->config->busqueda->urls->areas."/$areas";
				if(!empty($nivel))
					 $avanzada.="/".$this->config->busqueda->urls->nivel."/$nivel";
				if(!empty($remuneracion))
					 $avanzada.="/".$this->config->busqueda->urls->remuneracion."/$remuneracion";
				if(!empty($ubicacion))
					 $avanzada.="/".$this->config->busqueda->urls->ubicacion."/$ubicacion";
				if(!empty($fechapub))
					 $avanzada.="/".$this->config->busqueda->urls->fechapub."/$fechapub";
				$this->view->avanzada = $avanzada;
		  }


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

	 public function busquedaAvanzadaAction()
	 {
		  $this->quitarCapasNoUsadas();

		  $config = $this->getConfig();
		  $areas = $this->_getParam($this->config->busqueda->urls->areas, "");
		  $nivel = $this->_getParam($this->config->busqueda->urls->nivel, "");
		  $fechapub = $this->_getParam($this->config->busqueda->urls->fechapub, "");
		  $remuneracion = $this->_getParam($this->config->busqueda->urls->remuneracion, "");
		  $ubicacion = $this->_getParam($this->config->busqueda->urls->ubicacion, "");
		  $query = urldecode($this->_getParam($this->config->busqueda->urls->query, ""));

		  $searchUrlBuscamas = $config->apis->buscamas->searchUrl;
		  $apiKeyBuscamas = $config->apis->buscamas->consumerKey;
		  $url = $searchUrlBuscamas . $apiKeyBuscamas . '/start/0/count/20';
		  //$buscaMas = $this->_helper->getHelper('BuscaMas');
		  //$resultado = $buscaMas->obtenerResultadoBuscaMas($url);
		  //$decode = Zend_Json::decode($resultado);
		  $buscaMas = new Solr_SolrAviso();
		  $resultado = $buscaMas->obtenerCarreraSearchAdvanced();
		  $decode = $resultado;
		  $areasJSON = $decode['filter']['area'];
		  $nivelJSON = $decode['filter']['level'];
		  $ubicacionJSON = $decode['filter']['location'];

		  $areaDescDesc = $buscaMas->ordenarArray($areasJSON, 'label', false);
		  $nivelDescDesc = $buscaMas->ordenarArray($nivelJSON, 'label', false);
		  $ubiValorDesc = $buscaMas->ordenarArrayUbicacion($ubicacionJSON, 'count', true);

		  $form = new Application_Form_BusquedaAvanzada();
		  $form->setAreas($areaDescDesc,1);
		  $form->setNivelPuestos($nivelDescDesc,1);
		  $pA = explode('--', $areas);
		  $form->setValuesAreas($pA);
		  $pN = explode('--', $nivel);
		  $form->setValuesNivelPuestos($pN);


//       var_dump($ubiValorDesc);exit;

		  $this->view->form = $form;
		  $this->view->ubicacion = $ubiValorDesc;
		  $this->view->pU = explode('--', $ubicacion);
		  $this->view->pF = explode('--', $fechapub);
		  $this->view->pR = explode('--', $remuneracion);
		  $this->view->pC = $query;

		  //$modelCarrera = new Application_Model_Carrera;
		  //$this->view->carrera = $buscaMas->obtenerCarreraSearchAdvanced();
		  $this->view->carrera = $resultado['filter']['carrera'];
		  $this->view->fecha = $resultado['filter']['fecha'];
		  $this->view->salario = $resultado['filter']['salario'];
		  if ($this->getRequest()->isPost()) {
				$data = $this->_getAllParams();
				$ubi = '';
				if (isset($data['ubicacion']) && is_array($data['ubicacion'])) {
					 foreach ($data['ubicacion'] as $key => $value )
						  $ubi .= $value.'--';
					 $ubi = substr($ubi, 0, strlen($ubi) - 2);
				}

				$carrera = '';
				if (isset($data['radCarrera']) && is_array($data['radCarrera'])) {
					 foreach ($data['radCarrera'] as $key => $value )
						  $carrera .= $value.'--';
					 $carrera = substr($carrera, 0, strlen($carrera) - 2);
				}


				$empresas = '';
				if (isset($data['empresa']) && is_array($data['empresa'])) {
					 foreach ($data['empresa'] as $key => $value )
						  $empresas .= trim($value).'--';
					 $empresas = substr($empresas, 0, strlen($empresas) - 2);
				}

				//Áreas
				$areas = '';
				if (strlen($data['areas1']) > 1)
					 $areas .= $data['areas1'].'--';
				if (strlen($data['areas2']) > 1)
					 $areas .= $data['areas2'].'--';
				if (strlen($data['areas3']) > 1)
					 $areas .= $data['areas3'].'--';
				$areas = substr($areas, 0,  strlen($areas) - 2);

				//Nivel
				$nivel = '';
				if (strlen($data['nivelPuestos1']) > 1)
					 $nivel .= $data['nivelPuestos1'].'--';
				if (strlen($data['nivelPuestos2']) > 1)
					 $nivel .= $data['nivelPuestos2'].'--';
				if (strlen($data['nivelPuestos3']) > 1)
					 $nivel .= $data['nivelPuestos3'].'--';
				$nivel = substr($nivel, 0,  strlen($nivel) - 2);

				$query = $data['txtAdvanceSearch'];
				$remuneracion = isset($data['radRemuneration']) ? $data['radRemuneration'] : '';
				$fhPub = isset($data['radDate']) ? $data['radDate'] : '';
				$queryFinal = '';

				//Búsqueda por cxense con buscamas
				$filter = new Zend_Filter_StripTags;
				if ($query != '') {
					 $query = str_replace(" ", "+", $query);
					 $queryFinal .= '/q/' . strtolower($filter->filter($query));
				}

				if ($areas != '')
					 $queryFinal .= "/areas/". $areas;
				if ($nivel != '')
					 $queryFinal .= "/nivel/". $nivel;
				if ($ubi != '')
					 $queryFinal .= "/ubicacion/". $ubi;
				if ($carrera != '')
					 $queryFinal .= "/carrera/". $carrera;
				if ($empresas != '')
					 $queryFinal .= "/empresa/". $empresas;

				//print_r($data);
				if ($remuneracion != '')
					 $queryFinal .= "/remuneracion/". $remuneracion;

				if ($fhPub != '')
				$queryFinal .= "/fecha-publicacion/". $fhPub;
				//echo $queryFinal;

				$this->_redirect("/buscar".$queryFinal);
		  }

	 }

	 public function searchCompanyAction()
	 {
		  $this->_helper->layout->disableLayout();
		  $this->_helper->viewRenderer->setNoRender();

		  $modelEmpresa = new Solr_SolrAviso();
		  $dataEmpresa = array(
						  'status'=>'0',
						  "messages"=> $this->_messageError
					 );


		  if (!$this->getRequest()->isPost() && !$this->getRequest()->isXmlHttpRequest()) {
				die('Acceso denegado');
		  }
		  $data = $this->getRequest()->getPost();
		  if (isset($data['value']) && !empty($data['value'])) {

				//3 a más caracteres hace la búsqueda
				if (strlen($data['value']) >= 3) {
					 $data = $this->_getAllParams();
					 $filter = new Zend_Filter_StripTags();
					 $descripcion = $filter->filter($data['value']);

//                $filterAlnum = new Zend_Filter_Alnum(true);
//                $descripcion = $filterAlnum->filter($descripcion);
					 $dataEmpresa['status']='1';
					 $dataEmpresa['messages']='Se encontraron los datos';

					 $Items = $modelEmpresa->obtenerEmpresasBusquedaAvanzada($descripcion);
					 $dataEmpresa['items']=$Items;
					 echo Zend_Json::encode($dataEmpresa);
				}

		  }

		  exit;

	 }


	 private function quitarCapasNoUsadas()
	 {
		  Zend_Layout::getMvcInstance()->assign('noMostrarEscapeFormAP', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarescapeFormPaso', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarnextFormAP', true);


		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAlert', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAlertBCV', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarCntDataMsjs', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarCntDataNotifs', true);


		  //Zend_Layout::getMvcInstance()->assign('noMostrarWinAtencionN', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinExtendN', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarcntNewAdminEM', true);

		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAvisoEmpleo', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinVerProceso', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinVerHistorial', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAlertVerProceso', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAlertEliminarProceso', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAlertAnularCip', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAlertEliminarAviso', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAlertBajaAviso', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAlertEliminarAdm', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAlertEliminarprocess', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAlertEliminarAllprocess', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAlertPrivilegioAdm', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAnadirNotas', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAnadirMensaje', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinInvitarProceso', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarEjemploext', true);

		  Zend_Layout::getMvcInstance()->assign('noMostrarWinRegistrarReferenciado', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinRegistrarInvitacionBuscador', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinEnviarBolsa', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinBloquearPos', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinDesBloquearPos', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinDetalleMembresia', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarEjmAvisoPA1', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinMoverBolsa', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAddGroup', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinChangeNameGroup', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinDeleteGroup', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAlertEliminarBusqueda', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAlertEliminarRecom', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinAlertVerRecom', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarWinQuitarPostulante', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarDivModalContact', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarDivWrapSelection', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarDivModalSaveSearch', true);

		  Zend_Layout::getMvcInstance()->assign('noMostrarDivModalDeleteSearch', true);
		  Zend_Layout::getMvcInstance()->assign('noMostrarDivWrapHelp', true);

		  // ...

	 }

	 public function redirecionAreas($prmAreasnext){
		  $areanext=  explode('--', $prmAreasnext);
		  $paredirec="";
		  $respuesta=array();
		  $respuesta['return']=false;
		  foreach ($areanext as $value) {
				$datanewArea=Solr_SolrAviso::areanew($value);
				if($datanewArea){
					 $respuesta['return']=true;
					 $prmAreasnext=str_replace($value, $datanewArea, $prmAreasnext);;
				}

		  }
		  $prmAreasnext= explode('--', $prmAreasnext);
		  $prmAreasnext= array_unique($prmAreasnext);
		  $prmAreasnext= implode('--', $prmAreasnext);
		  $respuesta['param']=$prmAreasnext;
		  return $respuesta;
	 }

	 public function filterUbicacionAvisoAction() {
		 $this->_helper->layout->disableLayout();
		 $this->_helper->viewRenderer->setNoRender();
		 $ubicacion = $this->_getParam('value');
		 $tok = $this->_getParam('csrfhash');

		 $slugFilter = new App_Filter_Slug();
		 $requestValido = ($this->getRequest()->isPost() && $this->getRequest()->isXmlHttpRequest());
		 $requestValido = ($requestValido && $ubicacion && $tok);

		 if (!$requestValido) {
			 exit;
		  }
		  $data = array(
						  'status' => 0,
						  'message' => 'Por favor vuelva ha intentarlo',
						  'token' => CSRF_HASH
					 );
		if ($this->_hash->isValid($tok) ) {
				$filter = new Zend_Filter();
				$filter->addFilter(new Zend_Filter_StripTags());

				$ubicacion = $filter->filter($ubicacion);

				$ubicacion = $slugFilter->filter($ubicacion);

				$modelSolrAviso= new Solr_SolrAviso();

				$Items = $modelSolrAviso->getUbicacionByName($ubicacion,3);
				$data['status']='1';
				$data['items']=$Items;
				$data['message']='Se encontraron los datos';
		  }

		  $this->_response->appendBody(Zend_Json::encode($data));
	 }
}
