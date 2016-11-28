<?php

	class App_View_Helper_Contenedor extends Zend_View_Helper_Abstract
	{

		public function Contenedor ($paso,$estado,$fecha){
			$estado = ucwords($estado);
			return "<div class='contenedor-paso-line contenedor-paso-line-$paso'><span class='text-paso'>$estado</span>
								<div class='check-paso'><i aria-hidden='true' class='fa fa-check icon-check-postulaciones'></i></div><span class='text-fecha'>$fecha</span>
							</div> ";
		}
	}
?>
