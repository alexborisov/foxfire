<?php

/**
 * FOXFIRE UNIT TESTS - TEST CONFIGURATION AND SELECTION
 *
 * Ensures scripts are run in a specific order, and allows scripts to be easily enabled and
 * disabled without having to move them between directories (which would cause huge SVN problems
 * if developers were editing and moving around scripts at the same time).
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Unit Test
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */


class FOX_testPlan extends RAZ_testPlan_base {


	public function __construct() {

		self::setTestPanels();
		self::setTestCases();	    
	}
	
	
	/**
         * Loads mock classes used by the unit test runners in this dictionary
         *
         * @version 1.0
         * @since 1.0
         */ 
    
	public function loadMockClasses() {

//		global $razor;
//		
//		require_once(dirname( __FILE__ ) . '/mock_classes/mock_class.page.module.manager.php');
//		require_once(dirname( __FILE__ ) . '/mock_classes/mock_class.album.module.manager.php');
//		require_once(dirname( __FILE__ ) . '/mock_classes/mock_class.media.module.manager.php');
//		require_once(dirname( __FILE__ ) . '/mock_classes/mock_class.network.module.manager.php');
//		require_once(dirname( __FILE__ ) . '/mock_classes/mock_class.disk.cache.php');    
	}
	
	
	/**
         * Fetches the requested database images and loads their remapper classes
         *
         * @version 1.0
         * @since 1.0
         */

	public function setTestPanels() {
	    
	    
		$this->panels = array(

			"A" => array(
					"enable"=>true,  
					"name"=>"BP 1.5 on WP 3.4 in SINGLE SITE mode", 
					"file"=>"/panels/panel_bp1.5-wp.3.4-singlesite.php"					    						
			),
			"B" => array(
					"enable"=>false,  
					"name"=>"BP 1.5 on WP 3.4 in MULTISITE mode", 
					"file"=>"/panels/panel_bp1.5-wp.3.4-multisite.php"					    						
			)		    

		);		
		
	}	
		
	
	/**
         * Loads requested test cases so the test core can run them
         *
         * @version 1.0
         * @since 1.0
         */

	public function setTestCases() {


		$this->cases = array(

			"core" => array(
				array( "enable"=>true,  "name"=>"Typecasters", "mock"=>array(), "file"=>"/tests/core/test_class.typecast.php" ),		   
				array( "enable"=>false, "name"=>"Sanitizers", "mock"=>array(), "file"=>"/tests/core/test_class.sanitizers.php" ),
				array( "enable"=>false,  "name"=>"Version Check", "mock"=>array(), "file"=>"/tests/core/test_class.version.check.php" )
			),
		    
			"util" => array(
				array( "enable"=>true,  "name"=>"System", "mock"=>array(), "file"=>"/tests/util/test_class.utils.system.php" ),
				array( "enable"=>true,  "name"=>"Math", "mock"=>array(), "file"=>"/tests/util/test_class.utils.math.php" ),
				array( "enable"=>true,  "name"=>"Hash Table", "mock"=>array(), "file"=>"/tests/util/test_class.utils.hash.table.php" ),
			    	array( "enable"=>true,  "name"=>"Trie Flatten", "mock"=>array(), "file"=>"/tests/util/test_class.utils.trie.flatten.php" ),
			    	array( "enable"=>true,  "name"=>"Trie Clip", "mock"=>array(), "file"=>"/tests/util/test_class.utils.trie.clip.php" )			    
			),		    

			"database" => array(
				array( "enable"=>true,	"name"=>"Builder Delete", "mock"=>array(), "file"=>"/tests/database/test_db.builder.delete.php" ),
				array( "enable"=>true,	"name"=>"Builder Indate", "mock"=>array(), "file"=>"/tests/database/test_db.builder.indate.php" ),
				array( "enable"=>true,	"name"=>"Builder Insert", "mock"=>array(), "file"=>"/tests/database/test_db.builder.insert.php" ),
				array( "enable"=>true,	"name"=>"Builder Left Join", "mock"=>array(), "file"=>"/tests/database/test_db.builder.join.left.php" ),
				array( "enable"=>true,	"name"=>"Builder Standard Join", "mock"=>array(), "file"=>"/tests/database/test_db.builder.join.php" ),			    
				array( "enable"=>true,	"name"=>"Builder Select", "mock"=>array(), "file"=>"/tests/database/test_db.builder.select.php" ),
				array( "enable"=>true,	"name"=>"Builder Table", "mock"=>array(), "file"=>"/tests/database/test_db.builder.table.php" ),
				array( "enable"=>true,	"name"=>"Builder Update", "mock"=>array(), "file"=>"/tests/database/test_db.builder.update.php" ),
				array( "enable"=>true,	"name"=>"Builder Where", "mock"=>array(), "file"=>"/tests/database/test_db.builder.where.php" ),
				array( "enable"=>true,	"name"=>"Builder Where-Trie", "mock"=>array(), "file"=>"/tests/database/test_db.builder.where.trie.php" ),				    
				array( "enable"=>true,	"name"=>"Builder Where-Matrix", "mock"=>array(), "file"=>"/tests/database/test_db.builder.where.matrix.php" ),			    
				array( "enable"=>true,	"name"=>"Builder Insert", "mock"=>array(), "file"=>"/tests/database/test_db.builder.insert.php" ),			    			    
				array( "enable"=>true,	"name"=>"Result Formatters", "mock"=>array(), "file"=>"/tests/database/test_db.core.formatters.php" ),	
				array( "enable"=>true,	"name"=>"Op Delete", "mock"=>array(), "file"=>"/tests/database/test_db.op.delete.php" ),			    
				array( "enable"=>true,	"name"=>"Op Indate", "mock"=>array(), "file"=>"/tests/database/test_db.op.indate.php" ),
				array( "enable"=>true,	"name"=>"Op Insert", "mock"=>array(), "file"=>"/tests/database/test_db.op.insert.php" ),			    
				array( "enable"=>true,	"name"=>"Op Insert (id check)", "mock"=>array(), "file"=>"/tests/database/test_db.op.insert.id.php" ),			    
				array( "enable"=>true,	"name"=>"Op Standard Join", "mock"=>array(), "file"=>"/tests/database/test_db.op.join.php" ),
				array( "enable"=>true,	"name"=>"Op Left Join", "mock"=>array(), "file"=>"/tests/database/test_db.op.join.left.php" ),			    			    
				array( "enable"=>true,	"name"=>"Op Tables", "mock"=>array(), "file"=>"/tests/database/test_db.op.table.php" ),
				array( "enable"=>true,	"name"=>"Op Transactions", "mock"=>array(), "file"=>"/tests/database/test_db.op.transaction.php" ),
				array( "enable"=>true,	"name"=>"Op Update", "mock"=>array(), "file"=>"/tests/database/test_db.op.update.php" ),		    
			),

			"memory_cache" => array(
				array( "enable"=>true,  "name"=>"Loopback", "mock"=>array(), "file"=>"/tests/cache_memory/test_cache.driver.loopback.php" ),
				array( "enable"=>true,  "name"=>"Thread", "mock"=>array(), "file"=>"/tests/cache_memory/test_cache.driver.thread.php" ),				    
				array( "enable"=>true,  "name"=>"APC", "mock"=>array(), "file"=>"/tests/cache_memory/test_cache.driver.apc.php", 'ext'=>array('apc') ),	
			    	array( "enable"=>true,  "name"=>"Memcached", "mock"=>array(), "file"=>"/tests/cache_memory/test_cache.driver.memcached.php", 'ext'=>array('memcache') ),
			    	array( "enable"=>false,  "name"=>"Redis", "mock"=>array(), "file"=>"/tests/cache_memory/test_cache.driver.redis.php" ),			    
			),
		    
			"base" => array(
			    
				array( "enable"=>true,	 "name"=>"Validators",	"mock"=>array(), "file"=>"/tests/base_classes/test_base.datastore.validators.php" ),
			    
				array( "enable"=>true,	 "name"=>"L5 Paged (Add)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L5/test_base.db.store.paged.L5_add.php" ),
				array( "enable"=>true,	 "name"=>"L5 Paged (Set)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L5/test_base.db.store.paged.L5_set.php" ),
				array( "enable"=>true,	 "name"=>"L5 Paged (Get)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L5/test_base.db.store.paged.L5_get.php" ),
				array( "enable"=>true,	 "name"=>"L5 Paged (Replace)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L5/test_base.db.store.paged.L5_replace.php" ),
				array( "enable"=>true,	 "name"=>"L5 Paged (Drop)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L5/test_base.db.store.paged.L5_drop.php" ),
				array( "enable"=>true,	 "name"=>"L5 Paged (Global)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L5/test_base.db.store.paged.L5_global.php" ),			    
			    	array( "enable"=>false,	 "name"=>"L5 Paged (ACID)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L5/test_base.db.store.paged.L5_ACID.php" ),	
			    
//				array( "enable"=>true,	 "name"=>"L4 Paged (Add)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L4/test_base.db.store.paged.L4_add.php" ),
//				array( "enable"=>true,	 "name"=>"L4 Paged (Set)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L4/test_base.db.store.paged.L4_set.php" ),
//				array( "enable"=>true,	 "name"=>"L4 Paged (Get)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L4/test_base.db.store.paged.L4_get.php" ),
//				array( "enable"=>true,	 "name"=>"L4 Paged (Replace)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L4/test_base.db.store.paged.L4_replace.php" ),
//				array( "enable"=>true,	 "name"=>"L4 Paged (Drop)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L4/test_base.db.store.paged.L4_drop.php" ),
//				array( "enable"=>true,	 "name"=>"L4 Paged (Global)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L4/test_base.db.store.paged.L4_global.php" ),
//			    
//				array( "enable"=>true,	 "name"=>"L3 Paged (Add)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L3/test_base.db.store.paged.L3_add.php" ),
//				array( "enable"=>true,	 "name"=>"L3 Paged (Set)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L3/test_base.db.store.paged.L3_set.php" ),
//				array( "enable"=>true,	 "name"=>"L3 Paged (Get)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L3/test_base.db.store.paged.L3_get.php" ),
//				array( "enable"=>true,	 "name"=>"L3 Paged (Replace)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L3/test_base.db.store.paged.L3_replace.php" ),
//				array( "enable"=>true,	 "name"=>"L3 Paged (Drop)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L3/test_base.db.store.paged.L3_drop.php" ),
//				array( "enable"=>true,	 "name"=>"L3 Paged (Global)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L3/test_base.db.store.paged.L3_global.php" ),
//			    
//				array( "enable"=>true,	 "name"=>"L2 Paged (Add)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L2/test_base.db.store.paged.L2_add.php" ),
//				array( "enable"=>true,	 "name"=>"L2 Paged (Set)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L2/test_base.db.store.paged.L2_set.php" ),
//				array( "enable"=>true,	 "name"=>"L2 Paged (Get)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L2/test_base.db.store.paged.L2_get.php" ),
//				array( "enable"=>true,	 "name"=>"L2 Paged (Replace)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L2/test_base.db.store.paged.L2_replace.php" ),
//				array( "enable"=>true,	 "name"=>"L2 Paged (Drop)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L2/test_base.db.store.paged.L2_drop.php" ),
//				array( "enable"=>true,	 "name"=>"L2 Paged (Global)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L2/test_base.db.store.paged.L2_global.php" ),
//			    
//				array( "enable"=>false,	 "name"=>"L1 Paged (Add)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L1/test_base.db.store.paged.L1_add.php" ),
//				array( "enable"=>false,	 "name"=>"L1 Paged (Set)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L1/test_base.db.store.paged.L1_set.php" ),
//				array( "enable"=>false,	 "name"=>"L1 Paged (Get)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L1/test_base.db.store.paged.L1_get.php" ),
//				array( "enable"=>false,	 "name"=>"L1 Paged (Replace)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L1/test_base.db.store.paged.L1_replace.php" ),
//				array( "enable"=>false,	 "name"=>"L1 Paged (Drop)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L1/test_base.db.store.paged.L1_drop.php" ),
//				array( "enable"=>false,	 "name"=>"L1 Paged (Global)",	"mock"=>array(), "file"=>"/tests/base_classes/paged_L1/test_base.db.store.paged.L1_global.php" ),			    
//			    
//				array( "enable"=>false,	 "name"=>"L3 Monolithic", "mock"=>array(), "file"=>"/tests/base_classes/test_base.db.store.monolithic.L3.php" ),			    
//				array( "enable"=>false,  "name"=>"Module Manager", "mock"=>array(), "file"=>"/tests/base_classes/test_base.module.manager.php" ),
//				array( "enable"=>true,  "name"=>"Dictionary Base", "mock"=>array(), "file"=>"/tests/base_classes/test_base.db.dictionary.php" ),
//				array( "enable"=>false,	 "name"=>"Config", "mock"=>array(), "file"=>"/tests/base_classes/test_system.config.php" ),				    
			),

			"geospatial" => array(
				array( "enable"=>false,  "name"=>"Collection", "mock"=>array(), "file"=>"/tests/geospatial/geometry/test_collection.php" ),
				array( "enable"=>false,  "name"=>"Geometry Collection", "mock"=>array(), "file"=>"/tests/geospatial/geometry/test_geometry.collection.php" ),
				array( "enable"=>false,  "name"=>"Geometry Core", "mock"=>array(), "file"=>"/tests/geospatial/geometry/test_geometry.core.php" ),
				array( "enable"=>false,  "name"=>"Geometry Base", "mock"=>array(), "file"=>"/tests/geospatial/geometry/test_geometry.php" ),
				array( "enable"=>false,  "name"=>"Line String", "mock"=>array(), "file"=>"/tests/geospatial/geometry/test_line.string.php" ),
				array( "enable"=>false,  "name"=>"Line String Multi", "mock"=>array(), "file"=>"/tests/geospatial/geometry/test_line.string.multi.php" ),			    
				array( "enable"=>false,  "name"=>"Point", "mock"=>array(), "file"=>"/tests/geospatial/geometry/test_point.php" ),			    
				array( "enable"=>false,  "name"=>"Point Multi", "mock"=>array(), "file"=>"/tests/geospatial/geometry/test_point.multi.php" ),			    
				array( "enable"=>false,  "name"=>"Polygon", "mock"=>array(), "file"=>"/tests/geospatial/geometry/test_polygon.php" ),				   
				array( "enable"=>false,  "name"=>"Polygon Multi", "mock"=>array(), "file"=>"/tests/geospatial/geometry/test_polygon.multi.php" ),				    
			    
				array( "enable"=>false,  "name"=>"Adapter Base", "mock"=>array(), "file"=>"/tests/geospatial/adapter/test_adapter.base.php" ),
				array( "enable"=>false,  "name"=>"WKB", "mock"=>array(), "file"=>"/tests/geospatial/adapter/test_adapter.wkb.php" ),
				array( "enable"=>false,  "name"=>"WKT", "mock"=>array(), "file"=>"/tests/geospatial/adapter/test_adapter.wkt.php" ),				    
				array( "enable"=>false,  "name"=>"EWKB", "mock"=>array(), "file"=>"/tests/geospatial/adapter/test_adapter.ewkb.php" ),
				array( "enable"=>false,  "name"=>"EWKT", "mock"=>array(), "file"=>"/tests/geospatial/adapter/test_adapter.ewkt.php" ),	
			    	array( "enable"=>false,  "name"=>"GeoHash", "mock"=>array(), "file"=>"/tests/geospatial/adapter/test_adapter.geo.hash.php" ),
				array( "enable"=>false,  "name"=>"Google Geocoder", "mock"=>array(), "file"=>"/tests/geospatial/adapter/test_adapter.google.geocode.php" ),	
				array( "enable"=>false,  "name"=>"GPX", "mock"=>array(), "file"=>"/tests/geospatial/adapter/test_adapter.gpx.php" ),
				array( "enable"=>false,  "name"=>"JSON", "mock"=>array(), "file"=>"/tests/geospatial/adapter/test_adapter.json.php" ),	
				array( "enable"=>false,  "name"=>"KML", "mock"=>array(), "file"=>"/tests/geospatial/adapter/test_adapter.kml.php" ),	
				array( "enable"=>false,  "name"=>"RSS", "mock"=>array(), "file"=>"/tests/geospatial/adapter/test_adapter.rss.php" ),		
			),	
					    
//			"store" => array(
//				array( "enable"=>false,  "name"=>"System Config", "mock"=>array(), "file"=>"/tests/store/test_config.system.php" ),
//				array( "enable"=>false,  "name"=>"User Settings", "mock"=>array(), "file"=>"/tests/store/test_user.data.php" ),
//			),
//
//			"logging" => array(
//				array( "enable"=>false,  "name"=>"Logging Event", "mock"=>array(), "file"=>"/tests/logging/test_class.logging.event.php" ),
//			),		    
		);

	}

}

$cls = new FOX_testPlan();

global $razor;
$razor->registerTestPlan($cls);

?>