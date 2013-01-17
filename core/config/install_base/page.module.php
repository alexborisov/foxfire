<?php
/**
 * RADIENTPAGE MODULE INSTALL CLASS
 * Handles install operations for Radient page modules
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package Radient
 * @subpackage Config
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

class RAD_pageModule_install_base {


	var $slug;		// Album module's slug
	var $name;		// Album module's name
	var $php_class;		// Album module's PHP class

	// ============================================================================================================ //


	/**
	 * Loads the class with information about the album module
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $slug | Module slug name. Max 16 characters. Must be unique.
	 * @param string $name | Module name. Max 32 characters.
	 * @param string $php_class | Module PHP class. Max 255 characters. Must be unique.
	 */

	function RAD_pageModule_install_base($slug, $name, $php_class) {

		$this->__construct($slug, $name, $php_class);
	}

	/**
	 * Loads the class with information about the album module
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param string $slug | Module slug name. Max 16 characters. Must be unique.
	 * @param string $name | Module name. Max 32 characters.
	 * @param string $php_class | Module PHP class. Max 255 characters. Must be unique.
	 */

	function __construct($slug, $name, $php_class) {

		$this->slug = $slug;
		$this->name = $name;
		$this->php_class = $php_class;
	}


	/**
	 * Creates configuration keys for the module's "settings" page, and loads them with
	 * default values
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $error | If system fails to create one or more configuration keys, this
	 *			 variable will contain an array of failed key names.
	 * @return bool | False on one or more keys failing to be created. True on success.
	 */

	public function settings( &$error=null ) {

		global $rad;
		$result = array();

					    
		if( count($result) == 0){
			return true;
		}
		else {
			// Return array of failed keys
			$error = $result;
			return false;
		}

	}


	/**
	 * Creates configuration keys for the module's "templates" page, and loads them with
	 * default values
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $error | If system fails to create one or more configuration keys, this
	 *			 variable will contain an array of failed key names.
	 * @return bool | False on one or more keys failing to be created. True on success.
	 */

	public function templates( &$error=null ) {

		global $fox;
		$result = array();

		$data = array(

			"viewSite"=>		RAD_PATH_PAGE_MODULES . "/" . $this->slug . "/templates/view_site.php",
			"viewProfile" =>	RAD_PATH_PAGE_MODULES . "/" . $this->slug . "/templates/view_profile.php",
			"viewEmbedded" =>	RAD_PATH_PAGE_MODULES . "/" . $this->slug . "/templates/view_embedded.php",

		);



		if(!$fox->config->addNode("radient", 	$tree="templates",
						$branch = "pageModules",
						$key = $this->slug,
						$val = $data,
						$filter="arraySimple",
						$ctrl=array( "key"=>"keyName", "val"=>"fileStringLocal" ),
						$error=null)

					    ) {$result[]=$key;}


		if( count($result) == 0){
			return true;
		}
		else {
			// Return array of failed keys
			$error = $result;
			return false;
		}

	}


} // End of class RAD_pageModule_install_base

?>