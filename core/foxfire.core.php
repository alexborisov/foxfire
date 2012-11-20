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
define ( 'FOX_DISPLAY_DATE', "2012.11.20" );


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
	$fox->process_id = "foo"; //getmypid();

	// Setup our exception handler
	$fox->error = new FOX_exceptionHandler();

	function fox_exceptionHandler($error){

	    global $fox;
	    $fox->error->add($error);

	    $error = FOX_debug::formatError_print($error->data);
	    var_dump($error);

	}
	//set_exception_handler('fox_exceptionHandler');

	// WP and BP abstraction
	require ( dirname( __FILE__ ) . '/abstraction/class.bp.abstraction.php' );
	require ( dirname( __FILE__ ) . '/abstraction/class.wp.abstraction.php' );

	// Load the database classes, as all the core classes need them
	// ===============================================================
	
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
	
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.module.data.php' );
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.datastore.monolithic.L3.php' );
	require ( dirname( __FILE__ ) . '/base_classes/class.datastore.validators.php' );	
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.datastore.paged.L4.php' );
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.datastore.paged.L5.php' );
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.object.type.data.php' );
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.object.type.level.php' );
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.dictionary.php' );

	// Load the cache and config classes, as all the core classes need them,
	// and create the global singletons for these two classes.
	// ===============================================================
	
	require ( dirname( __FILE__ ) . '/cache_disk/class.cache.disk.php' );

	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.memory.core.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/abstract.class.cache.driver.base.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.driver.apc.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.driver.loopback.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.driver.memcache.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.driver.redis.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.driver.thread.php' );

	require ( dirname( __FILE__ ) . '/config/class.config.system.php' );

	$fox->mCache = new FOX_mCache();    // Memory cache singleton
	$fox->disk = new FOX_dCache();	    // Disk singleton


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
	
	

}

?>