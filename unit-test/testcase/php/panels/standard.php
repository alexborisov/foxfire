<?php

/**
 * FOXFIRE TEST PANEL - BP 1.5 | WP 3.4 | SINGLE SITE MODE
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Test Panel
 * @license GPL v2.0
 *
 * ========================================================================================================
 */


class panel_FOX_standard extends RAZ_testPanel_base {

    	
	public function __construct() {

		$this->slug = "panel_FOX_bp15_wp34_singleSite";
		$this->name = "BP 1.5 | WP 3.4 | SINGLE SITE MODE";   
		
	}	
	
	
	/**
	 * This method is used to add globals and defines that would normally be set in 'wp-config.php'
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function setupGlobals() {

		define('WPLANG', '');
		define('WP_DEBUG', false);
			    
		define('DB_NAME', 'unit_test');
		define('DB_USER', 'test');
		define('DB_PASSWORD', 'test');
		define('DB_HOST', 'localhost');
		define('DB_CHARSET', 'utf8');
		define('DB_COLLATE', '');
			
	}
	
	
	/**
	 * Move assets (such as plugin files) into place before the test runner spins-up
	 * the WordPress installation
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function setupAssets() {

	}
	
	
	/**
	 * Set up db image and remapping options
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function setupDB() {		
	    
		global $razor;
	    
		// Clear any tables left over from previous tests
		drop_tables($razor->db_object);
	}
	
	
	/**
	 * Perform any setup tasks that have to be completed after the DB image has been loaded
	 * but before the test groups are run
	 *
	 * @version 1.0
	 * @since 1.0
	 */	
	function setupState() {
	    
	}
	
	
	/**
	 * Cleanup assets after completion of the test panel
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	function tearDownAssets() {

	}			
	
}

$cls = new panel_FOX_standard();

global $razor;
$razor->registerTestPanel($cls);

?>