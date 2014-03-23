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


// Toggle between Normal Mode and WordPress Mode
// ===========================================================================================

if( defined('AUTH_KEY') && defined('NONCE_KEY') ){	// If these two constants are set, WordPress is installed
	
	define( 'FOX_WP_ACTIVE', true);
	
	// Constant to check if FoxFire is installed
	define ( 'FOX_IS_INSTALLED', 1 );

	// Internal version number (Git repository commit number)
	define ( 'FOX_VERSION', '2618' );

	// Version of FoxFire shown on admin screen. This lets us show text like "1.0-RC1"
	define ( 'FOX_DISPLAY_VERSION', "2.0" );

	// Build date of FoxFire shown on admin screen
	define ( 'FOX_DISPLAY_DATE', "2014.03.05" );


	if(!defined( 'FOX_EXPERT_MODE' )){
	    
	      define ('FOX_EXPERT_MODE', 1);
	}

	define( 'FOX_FOLDER', plugin_basename(dirname(dirname(__FILE__))) );
	define( 'FOX_PATH_BASE', WP_PLUGIN_DIR . '/' . FOX_FOLDER );
	define( 'FOX_URL_BASE', WP_PLUGIN_URL . '/' . FOX_FOLDER );	
	
}
else {

	define( 'FOX_WP_ACTIVE', false);
	
	define( 'FOX_PATH_BASE', dirname(dirname(__FILE__)));

	if(defined('CORE_PHP_URL')){

		define( 'FOX_URL_BASE', CORE_PHP_URL . '/foxfire' );
	}
	else {	
		define( 'FOX_URL_BASE', 'http://localhost/foxfire' );
	}
		
}	

define( 'FOX_PATH_CORE', FOX_PATH_BASE . '/core' );
define( 'FOX_URL_CORE', FOX_URL_BASE . '/core' );
define( 'FOX_PATH_LIB', FOX_PATH_BASE . "/lib" );
define( 'FOX_URL_LIB', FOX_URL_BASE . "/lib" );



global $fox;

$fox = new stdClass();

// WP and BP abstraction
// ===========================================================================================

require ( dirname( __FILE__ ) . '/abstraction/class.bp.abstraction.php' );
require ( dirname( __FILE__ ) . '/abstraction/class.wp.abstraction.php' );
	
	
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


if(FOX_WP_ACTIVE){
    
	$cls = new FOX_version();
	$libs_ok = $cls->allOK();
}
else {
	$libs_ok = true;
}

if(!$libs_ok) {

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



	// Exceptions and error handling
	require ( dirname( __FILE__ ) . '/utils/class.utils.exception.php' );
	require ( dirname( __FILE__ ) . '/utils/class.utils.exception.handler.php' );

	// Get this thread's process ID
	$fox->process_id = 0001; //getmypid();

	// Setup our exception handler
	$fox->error = new FOX_exceptionHandler();

	function fox_exceptionHandler($error){

		global $fox;

		if( method_exists($error, 'dumpString') ){

			$fox->error->add($error);

			// Not dumping errors deep enough and truncating them with
                        // a "..."? xDebug is hijacking var_dump. Disable it in php.ini
                        
			$out = $error->dumpString(array('depth'=>20, 'data'=>true));
			FOX_debug::dump($out);

		}
		else {
			var_dump($error);
		}


	}
	set_exception_handler('fox_exceptionHandler');


	// Spin-up the Memory Cache system
	// ===============================================================		
	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.memory.core.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/abstract.class.cache.driver.base.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.driver.apc.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.driver.loopback.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.driver.memcache.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.driver.redis.php' );
	require ( dirname( __FILE__ ) . '/cache_memory/class.cache.driver.thread.php' );
	
	$fox->mCache = new FOX_mCache(); 
	
	// Spin-up the disk interface system
	// ===============================================================	
	//  require ( dirname( __FILE__ ) . '/cache_disk/class.cache.disk.php' );
	//  $fox->disk = new FOX_dCache()

	
	// Load the URI router class
	// ===============================================================
	
//	require ( dirname( __FILE__ ) . '/navigation/class.router.php' );	
//	$fox->router = new FOX_router();
	
	
	// Data Integrity Classes
	// ===============================================================

	require ( dirname( __FILE__ ) . '/data_integrity/class.di.typecast.php' );
	require ( dirname( __FILE__ ) . '/data_integrity/class.di.sanitizers.php' );


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
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.module.manager.base.php' );	
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.module.manager.private.php' );
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.module.manager.public.php' );	
	require ( dirname( __FILE__ ) . '/base_classes/abstract.class.module.data.php' );
	
	
	// Load Geospatial classes
	// ===============================================================
	
	require ( dirname( __FILE__ ) . '/geospatial/adapters/class.geo.adapter.php' );	
	require ( dirname( __FILE__ ) . '/geospatial/adapters/class.wkb.php' );
	require ( dirname( __FILE__ ) . '/geospatial/adapters/class.wkt.php' );		
	require ( dirname( __FILE__ ) . '/geospatial/adapters/class.ewkb.php' );
	require ( dirname( __FILE__ ) . '/geospatial/adapters/class.ewkt.php' );
	require ( dirname( __FILE__ ) . '/geospatial/adapters/class.geo.hash.php' );
	require ( dirname( __FILE__ ) . '/geospatial/adapters/class.geo.json.php' );
	require ( dirname( __FILE__ ) . '/geospatial/adapters/class.geo.rss.php' );
	require ( dirname( __FILE__ ) . '/geospatial/adapters/class.google.geocode.php' );
	require ( dirname( __FILE__ ) . '/geospatial/adapters/class.gpx.php' );
	require ( dirname( __FILE__ ) . '/geospatial/adapters/class.kml.php' );

	require ( dirname( __FILE__ ) . '/geospatial/geometry/class.geometry.php' );		
	require ( dirname( __FILE__ ) . '/geospatial/geometry/class.collection.php' );	
	require ( dirname( __FILE__ ) . '/geospatial/geometry/class.geometry.collection.php' );		
	require ( dirname( __FILE__ ) . '/geospatial/geometry/class.geometry.core.php' );
	require ( dirname( __FILE__ ) . '/geospatial/geometry/class.line.string.multi.php' );
	require ( dirname( __FILE__ ) . '/geospatial/geometry/class.line.string.php' );
	require ( dirname( __FILE__ ) . '/geospatial/geometry/class.point.multi.php' );
	require ( dirname( __FILE__ ) . '/geospatial/geometry/class.point.php' );
	require ( dirname( __FILE__ ) . '/geospatial/geometry/class.polygon.multi.php' );
	require ( dirname( __FILE__ ) . '/geospatial/geometry/class.polygon.php' );
	
	
	// Load FoxFire's config settings
	// ===============================================================		
	require ( dirname( __FILE__ ) . '/config/class.config.system.php' );
	require ( dirname( __FILE__ ) . '/config/class.config.default.keys.php' );
	require ( dirname( __FILE__ ) . '/config/class.config.default.schema.php' );
	
	$fox->config = new FOX_config();
	
	
	// Load the RBAC system
	// ===============================================================
	
	require ( dirname( __FILE__ ) . '/rbac/class.user.data.php' );
	require ( dirname( __FILE__ ) . '/rbac/class.user.php' );
	require ( dirname( __FILE__ ) . '/rbac/class.group.keyring.php' );
	require ( dirname( __FILE__ ) . '/rbac/class.group.members.php' );
	require ( dirname( __FILE__ ) . '/rbac/class.group.types.php' );
	require ( dirname( __FILE__ ) . '/rbac/class.key.types.system.php' );
	require ( dirname( __FILE__ ) . '/rbac/class.key.types.token.php' );
	require ( dirname( __FILE__ ) . '/rbac/class.user.keyring.php' );
	require ( dirname( __FILE__ ) . '/rbac/class.user.types.php' );
	
	// Load the logging classes
	// ===============================================================
	
	require ( dirname( __FILE__ ) . '/logging/class.logging.dictionary.tree.php' );
	require ( dirname( __FILE__ ) . '/logging/class.logging.dictionary.branch.php' );
	require ( dirname( __FILE__ ) . '/logging/class.logging.dictionary.node.php' );
	require ( dirname( __FILE__ ) . '/logging/class.logging.event.php' );
	//require ( dirname( __FILE__ ) . '/logging/class.logging.error.php' );	
	
	
	if(FOX_WP_ACTIVE){
	    
	    
		// Load the Navigation classes 
		// ===============================================================

		require ( dirname( __FILE__ ) . '/navigation/class.location.module.php' );
		require ( dirname( __FILE__ ) . '/navigation/class.location.policy.php' );
		require ( dirname( __FILE__ ) . '/navigation/class.module.slug.php' );
		require ( dirname( __FILE__ ) . '/navigation/class.navigation.php' );


		$fox->navigation = new FOX_nav();

		require ( dirname( __FILE__ ) . '/page_modules/class.page.module.abstract.php' );
		require ( dirname( __FILE__ ) . '/page_modules/class.page.module.interface.php' );
		require ( dirname( __FILE__ ) . '/page_modules/class.page.module.manager.php' );	

		$fox->pageModules = new FOX_pageModuleManager();


		// Load the AJAX functions
		// ===============================================================
		require ( dirname( __FILE__ ) . '/js/register.scripts.php' );	
		require ( dirname( __FILE__ ) . '/admin/sub.admin.core.php' );
		
	
		// Load translation files
		load_textdomain( "foxfire", dirname( __FILE__ ) . '/languages/foxfire-' . get_locale() . '.mo' );	
		
		do_action( 'fox_coreReady' );
	
	
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
		 * @version 1.0
		 * @since 1.0
		 */

		function fox_core_check_installed() {

			global $fox, $razor;

			if( current_user_can('install_plugins') ) {

				try {
					$installed = $fox->config->getNodeVal('foxfire', "system", "core", "installed");
				}
				catch (FOX_exception $child) {

					// If the plugin's config class database table doesn't exist, the 
					// config class will throw an exception
				}		    


				if(!$installed && !$razor){

					do_action( 'fox_install' );
					do_action( 'fox_setDefaults' );
				}
			}
		}
		add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', 'fox_core_check_installed' );
		
	
	}
	
	
}
					

?>