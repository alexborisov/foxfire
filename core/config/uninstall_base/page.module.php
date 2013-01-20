<?php
/**
 * FOXFIRE PAGE MODULE UN-INSTALL CLASS
 * Handles uninstall operations for FoxFire page modules
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Config
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

class FOX_pageModule_uninstall_base {


	var $slug;		// Album module's slug
	var $name;		// Album module's name
	var $php_class;		// Album module's PHP class

	// ============================================================================================================ //


	/**
	 * Loads the class with information about the album module
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $slug | Module slug name. Max 16 characters. Must be unique.
	 * @param string $name | Module name. Max 32 characters.
	 * @param string $php_class | Module PHP class. Max 255 characters. Must be unique.
	 */

	function FOX_pageModule_uninstall_base($slug, $name, $php_class) {

		$this->__construct($slug, $name, $php_class);
	}

	/**
	 * Loads the class with information about the album module
	 *
	 * @version 1.0
	 * @since 1.0
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
	 * Removes configuration keys for the module's "settings" page
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param array $error | If system fails to delete one or more configuration keys, this
	 *			 variable will contain an array of failed key names.
	 * @return bool | False on one or more keys failing to be deleted. True on success.
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
	 * Removes configuration keys for the module's "templates" page
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param array $error | If system fails to delete one or more configuration keys, this
	 *			 variable will contain an array of failed key names.
	 * @return bool | False on one or more keys failing to be deleted. True on success.
	 */

	public function templates( &$error=null ) {

		global $fox;
		$result = array();

		if(!$fox->config->dropKey(	$tree="templates",
						$branch = "pageModules",
						$key = $this->slug)

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


} // End of class FOX_pageModule_uninstall_base

?>