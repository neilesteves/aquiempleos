<?php

class Postulante_BolsasDeTrabajoController extends App_Controller_Action_Postulante
{


	 protected $_cache = null;

	 public function init()
	 {
		  parent::init();
		  $this->_cache = Zend_Registry::get('cache');
	 }

	 public function indexAction(){

		  $modelEmpresa = new Application_Model_Empresa();

		  $formBusqueda = new Application_Form_BuscarEmpresa;
		  $this->view->formBusqueda = $formBusqueda;

		  $data = $this->_getAllParams();


		  $nomEmpresa = isset($data['razonsocial']) ? $data['razonsocial'] : '';

		  $filter = new Zend_Filter();
		  $filter->addFilter(new Zend_Filter_StripTags(),new Zend_Filter_Alnum());
		  $nomEmpresa = $filter->filter($nomEmpresa);



		  $paginator = $modelEmpresa->getPaginator($nomEmpresa, null);
		  //$paginado = $this->config->paginadoresEmpresa->pagina->landing;


		  $this->view->headTitle()->set('Bolsas de Trabajo en Perú | AquiEmpleos');
		  $this->view->paginado = 0;
		  $this->view->numReg = 0;

		  //$paginator->setItemCountPerPage(0);
		  //$paginator->setCurrentPageNumber(1);
		  $this->view->empresas = $paginator;

	 }


}
