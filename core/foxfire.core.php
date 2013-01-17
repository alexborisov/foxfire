<?php

/**
 * FOXFIRE CORE
 * Handles the overall operations of the plugin
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Main
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */


// Constant to check if FoxFire is installed
define ( 'FOX_IS_INSTALLED', 1 );

// Internal version number (Git repository commit number)
define ( 'FOX_VERSION', '2618' );

// Version of FoxFire shown on admin screen. This lets us show text like "0.1.9-RC1"
define ( 'FOX_DISPLAY_VERSION', "1.0" );

// Build date of FoxFire shown on admin screen
define ( 'FOX_DISPLAY_DATE', "2013.01.13" );


// Define expert mode constants, if they do not already exist
// ===========================================================================================

if ( ! defined( 'FOX_EXPERT_MODE' ) )
      define ('FOX_EXPERT_MODE', 1);

// Define directory paths, if they do not already exist
if( !defined( 'FOX_FOLDER' ) ){
      define( 'FOX_FOLDER', plugin_basename(dirname(dirname(__FILE__))) );
}

if( !defined( 'FOX_PATH_BASE' ) ){
      define( 'FOX_PATH_BASE', WP_PLUGIN_DIR . '/' . FOX_FOLDER );
}

if( !defined( 'FOX_URL_BASE' ) ){
      define( 'FOX_URL_BASE', WP_PLUGIN_URL . '/' . FOX_FOLDER );
}

if( !defined( 'FOX_PATH_CORE' ) ){
     define( 'FOX_PATH_CORE', WP_PLUGIN_DIR . '/' . FOX_FOLDER . "/core" );
}

if( !defined( 'FOX_URL_CORE' ) ){
      define( 'FOX_URL_CORE', WP_PLUGIN_URL . '/' . FOX_FOLDER . "/core" );
}

if( !defined( 'FOX_PATH_LIB' ) ){
      define( 'FOX_PATH_LIB', WP_PLUGIN_DIR . '/' . FOX_FOLDER . "/lib" );
}

if( !defined( 'FOX_URL_LIB' ) ){
      define( 'FOX_URL_LIB', WP_PLUGIN_URL . '/' . FOX_FOLDER . "/lib" );
}


/**  
 * Compatible System Detection
 * ===========================================================================================
 * 
 * This function checks that the user has the correct versions of PHP, MySQL, GD,
 * WordPress, and BuddyPress ...and will not load or run any of the plugin files
 * unless they meet the minimum requirements.
 *
 * Users that have incorrect PHP/SQL/GD/WP/BP versions are sent to a screen explaining
 * the problem and where to get help.
 *
 * @version 1.0
 * @since 1.0
 */

global $fox;

$fox = new stdClass();

// These classes are REQUIRED for the version checker and debug functions to operate
// ===========================================================================================

require ( dirname( __FILE__ ) . '/utils/class.utils.debug.php' );
require ( dirname( __FILE__ ) . '/utils/class.utils.debug.diff.php' );
require ( dirname( __FILE__ ) . '/utils/class.utils.debug.handler.php' );
require ( dirname( __FILE__ ) . '/utils/class.utils.network.php' );
require ( dirname( __FILE__ ) . '/utils/class.utils.math.php' );
require ( dirname( __FILE__ ) . '/utils/class.utils.trie.php' );
require ( dirname( __FILE__ ) . '/utils/class.utils.trie.flatten.php' );
require ( dirname( __FILE__ ) . '/utils/class.utils.trie.flatten.iterator.php' );
require ( dirname( __FILE__ ) . '/utils/class.utils.trie.clip.php' );
require ( dirname( __FILE__ ) . '/utils/class.utils.trie.clip.iterator.php' );
require ( dirname( __FILE__ ) . '/utils/class.utils.hash.table.php' );
require ( dirname( __FILE__ ) . '/utils/class.utils.system.php' );
require ( dirname( __FILE__ ) . '/utils/class.utils.xml.php' );
require ( dirname( __FILE__ ) . '/utils/class.utils.unit.test.php' );
require ( dirname( __FILE__ ) . '/utils/class.version.check.php' );

$lib_versions = new FOX_version();

if( !$lib_versions->allOK() ) {

	require ( dirname( __FILE__ ) . '/admin/class.recovery.core.php' );

	/**
	 * Adds the FoxFire admin menu to the WordPress "Site" admin menu
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	
	function fox_add_recovery_admin_menu() {

		if ( !is_super_admin() ){
			return false;
		}

		$fox_admin = new FOX_admin("site");

	}
	add_action( 'admin_menu', 'fox_add_recovery_admin_menu', 2 );


	/**
	 * Adds the FoxFire admin menu to the WordPress "Network" admin menu.
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	
	function fox_add_recovery_network_menu() {

		if( !is_super_admin() ){
			return false;
		}

		$fox_admin = new FOX_admin("network");
		
	}
	add_action( 'network_admin_menu', 'fox_add_recovery_network_menu', 2 );

}
else {

	// NOTE: We're currently explicitly loading all plugin files on every launch
	// to simplify things during the development process. The production version
	// of FoxFire will use dynamic file loading to improve load times.
	// ===========================================================================

	// Load translation files
	load_textdomain( "foxfire", dirname( __FILE__ ) . '/languages/foxfire-' . get_locale() . '.mo' );

	// Exceptions and error handling
	require ( dirname( __FILE__ ) . '/utils/class.utils.exception.php' );
	require ( dirname( __FILE__ ) . '/utils/class.utils.exception.handler.php' );

	// Get this thread's process ID
	$fox->process_id = 0001; //getmypid();

	// Setup our exception handler
	$fox->error = new FOX_exceptionHandler();

	function fox_exceptionHandler($error){

		global $fox;
		$fox->error->add($error);

		//$error = FOX_debug::formatError_print($error->data);
		FOX_debug::dump($error);

	}
	set_exception_handler('fox_exceptionHandler');

	// WP and BP abstraction
	require ( dirname( __FILE__ ) . '/abstraction/class.bp.abstraction.php' );
	require ( dirname( __FILE__ ) . '/abstraction/class.wp.abstraction.php' );

	// Load the database classes, as all the core classes need them
	// ===============================================================
	
	require ( dirname( __FILE__ ) . '/database/class.database.driver.mysql.php' );	
	require ( dirname( __FILE__ ) . '/database/class.database.driver.mysql_i.php' );	
	require ( dirname( __FILE__ ) . '/database/class.database.core.php' );
	require ( dirname( __FILE__ ) . '/database/class.database.query.builders.php' );
	require ( dirname( __FILE__ ) . '/database/class.where.matrix.php' );
	require ( dirname( __FILE__ ) . '/database/class.where.matrix.iterator.php' );		
	require ( dirname( __FILE__ ) . '/database/class.database.query.runners.php' );
	require ( dirname( __FILE__ ) . '/database/abstract.class.database.php' );
	require ( dirname( __FILE__ ) . '/database/class.database.walker.php' );
	require ( dirname( __FILE__ ) . '/database/class.database.typecast.php' );
	require ( dirname( __FILE__ ) . '/database/class.database.sanitizers.php' );
	require ( dirname( __FILE__ ) . '/database/class.database.util.php' );

	// Load the base classes
	// ===============================================================
	
	//require ( dirname( __FILE__ ) . '/base_classes/abstract.class.module.data.php' );
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.datastore.monolithic.L3.php' );
	require ( dirname( __FILE__ ) . '/base_classes/class.datastore.validators.php' );
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.datastore.paged.L1.php' );	
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.datastore.paged.L2.php' );		
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.datastore.paged.L3.php' );	
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.datastore.paged.L4.php' );
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.datastore.paged.L5.php' );
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.object.type.data.php' );
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.object.type.level.php' );
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.dictionary.php' );
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.module.manager.php' );
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.module.data.php' );		
	
	// Load the Navigation system
	// ===============================================================
	
	require ( dirname( __FILE__ ) . '/navigation/class.location.module.php' );
	require ( dirname( __FILE__ ) . '/navigation/class.location.policy.php' );
	require ( dirname( __FILE__ ) . '/navigation/class.module.slug.php' );
	//require ( dirname( __FILE__ ) . '/navigation/class.router.php' );
	require ( dirname( __FILE__ ) . '/navigation/class.navigation.php' );
	
	require ( dirname( __FILE__ ) . '/page_modules/class.page.module.abstract.php' );
	require ( dirname( __FILE__ ) . '/page_modules/class.page.module.interface.php' );
	require ( dirname( __FILE__ ) . '/page_modules/class.page.module.manager.php' );	
	

	// Load the cache and config classes, as all the core classes need them,
	// and create the global singletons for these two classes.
	// ===============================================================
	
	//require ( dirname( __FILE__ ) . '/cache_disk/class.cache.disk.php' );

	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.memory.core.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/abstract.class.cache.driver.base.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.driver.apc.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.driver.loopback.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.driver.memcache.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.driver.redis.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.driver.thread.php' );

	require ( dirname( __FILE__ ) . '/config/class.config.system.php' );	
	require ( dirname( __FILE__ ) . '/admin/sub.admin.core.php' );
	

	$fox->mCache = new FOX_mCache();    // Memory cache singleton
	//$fox->disk = new FOX_dCache();	    // Disk singleton
	
	$fox->config = new FOX_config();			// Config singleton
	
	//$fox->router = new FOX_router();			// URI router singleton
	$fox->navigation = new FOX_nav();			// Navigation singleton
	$fox->pageModules = new FOX_pageModuleManager();	// Page modules singleton	


	/**
	 * Adds the plugin admin menu to the WordPress "Site" admin menu
	 *
	 * @version 1.0
	 * @since 1.0
	 */
	
	function fox_add_admin_menu() {

		if( !is_super_admin() ){
		    
			return false;
		}

		$fox_admin = new FOX_admin("site");

	}
	add_action( 'admin_menu', 'fox_add_admin_menu', 2 );


	/**
	 * Adds the plugin admin menu to the WordPress "Network" admin menu.
	 *
	 * @uses is_super_admin() returns true if the current user is a site admin, false if not
	 * @version 1.0
	 * @since 1.0
	 */
	
	function fox_add_network_menu() {

		if( !is_super_admin() ){
		    
			return false;
		}

		$fox_admin = new FOX_admin("network");

	}
	add_action( 'network_admin_menu', 'fox_add_network_menu', 2 );
	
	
	/**
	 * Checks that the plugins database tables are installed. Runs the install routine if they are
	 * not. This corrects a bug whereby the install routine is not triggered when BP-Media is
	 * installed in the plugins directory on a WPMU system.
	 *
	 * @global $bpm The BP-Media global variable
	 * @version 0.1.9
	 * @since 0.1.9
	 */

	function fox_core_check_installed() {

		global $fox, $razor;

		if( current_user_can('install_plugins') ) {

			$installed = $fox->config->getNodeVal('foxfire', "system", "core", "installed");

			if(!$installed && !$razor){

				do_action( 'fox_install' );
				do_action( 'fox_setDefaults' );
			}
		}
	}
	add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', 'fox_core_check_installed' );	
	
	
}

?>