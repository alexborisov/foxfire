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


if( !defined( 'FOX_PATH_BASE' ) ){
      define( 'FOX_PATH_BASE', CORE_PHP_PATH . '/foxfire' );
}

if( !defined( 'FOX_URL_BASE' ) ){
      define( 'FOX_URL_BASE', CORE_PHP_URL . '/foxfire' );
}

if( !defined( 'FOX_PATH_CORE' ) ){
     define( 'FOX_PATH_CORE', FOX_PATH_BASE . '/core' );
}

if( !defined( 'FOX_URL_CORE' ) ){
      define( 'FOX_URL_CORE', FOX_URL_BASE . '/core' );
}

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


// NOTE: We're currently explicitly loading all plugin files on every launch
// to simplify things during the development process. The production version
// of FoxFire will use dynamic file loading to improve load times.
// ===========================================================================

// Load translation files
//load_textdomain( "foxfire", dirname( __FILE__ ) . '/languages/foxfire-' . get_locale() . '.mo' );

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

		//$error = FOX_debug::formatError_print($error->data);
		$out = $error->dumpString(array('depth'=>1, 'data'=>true));
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


require ( dirname( __FILE__ ) . '/page_modules/class.page.module.abstract.php' );
require ( dirname( __FILE__ ) . '/page_modules/class.page.module.interface.php' );
require ( dirname( __FILE__ ) . '/page_modules/class.page.module.manager.php' );	

$fox->pageModules = new FOX_pageModuleManager();
					

?>