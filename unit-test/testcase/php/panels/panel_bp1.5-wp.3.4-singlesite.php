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


class panel_FOX_bp15_wp34_singleSite extends RAZ_testPanel_base {

    	
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

		define('TITAN_DOMAIN', 'http://localhost');
		define('TITAN_BASE_DOMAIN', 'localhost');
		
		define('CDN_PATH', dirname(__FILE__) . '/cdn');
		define('CDN_URL', TITAN_DOMAIN . '/cdn');

		define('TITAN_AJAX_KEY', 'egXA<!QK3eK5G*6]lB>,q,cDVwacC/<y;?L7(^X%,O~[Kp*^0zfVUyz4wsV(8y[1');
		define('TITAN_SESS_KEY', 'V(En)SZiI+A25QUwh<Ac9oAqv7&eVP9UNyW0/kVFP iN?tEFsB<)-=rB.l0^RK(=');
		define('TITAN_AUTH_KEY', '13{S~FZ=#~W;M?>Vxe~lIO<m=!5v-91.G9zpx|V}+vz5#c<)uC*w7qnvR&:I8Tz%');				
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

$cls = new panel_FOX_bp15_wp34_singleSite();

global $razor;
$razor->registerTestPanel($cls);

?>