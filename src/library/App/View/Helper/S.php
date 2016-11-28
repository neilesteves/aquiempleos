<?php


/**
 * S view Helpers is a Loader for Static files
 * providing ability to read prefix from config
 * and suffix a versioning query string
 *
 */
class App_View_Helper_S
	 extends Zend_View_Helper_HtmlElement
{

	 protected static $lastCommit;

	 public function __construct()
	 {
		  $lc_file = APPLICATION_PATH . '/../last_commit';
		  if (is_readable($lc_file)) {
				if (!isset(self::$lastCommit)) {
					 self::$lastCommit = trim(file_get_contents($lc_file));
				}
		  }
	 }

	 /**
	  * @param  String
	  * @return string
	  */
	 public function S($file = "")
	 {
		  if(!$file){
				return $this;
		  }

		  return MEDIA_URL .$file . '?v=' . self::$lastCommit;
	 }

	 public function getLastCommit()
	 {
		  return '?v=' . self::$lastCommit;
	 }

}
