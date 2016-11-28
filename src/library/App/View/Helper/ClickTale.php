<?php

/**
 * Description of Attribs
 *
 * @author eanaya
 */
class App_View_Helper_ClickTale extends Zend_View_Helper_Abstract
{

	 public function ClickTale()
	 {
		  return $this;
	 }
	 public function Superior()
	 {
		  return '<!-- ClickTale Top part --><script type="text/javascript">var WRInitTime=(new Date()).getTime();</script><!-- ClickTale end of Top part -->';
	 }
	 public function Inferior()
	 {
		  return '<!-- ClickTale Bottom part --><script type=\'text/javascript\'> document.write(unescape("%3Cscript%20src=\'"+ (document.location.protocol==\'https:\'? "https://cdnssl.clicktale.net/www02/ptc/ba81aef3-0ea3-4794-a628-40f31622414d.js": "http://cdn.clicktale.net/www02/ptc/ba81aef3-0ea3-4794-a628-40f31622414d.js")+"\'%20type=\'text/javascript\'%3E%3C/script%3E"));</script><!-- ClickTale end of Bottom part -->';
	 }
	 public function Adicional()
	 {
		  return '<SCRIPT type=\'text/javascript\'>window.ClickTaleSettings = { XHRWrapper: { Enable: true, MaxResponseSize: 1000000} }; </SCRIPT> <SCRIPT type=\'text/javascript\'>document.write(unescape("%3Cscript%20src=\'" + (document.location.protocol == \'https:\' ? \'https://clicktalecdn.sslcs.cdngc.net/www/\' : \'http://cdn.clicktale.net/www/\') + "XHRWrapper.js\'%20type=\'text/javascript\'%3E%3C/script%3E"));</SCRIPT>';
	 }

}
