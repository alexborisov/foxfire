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

		global $razor;
		
		require_once(dirname( __FILE__ ) . '/mock_classes/mock_class.page.module.manager.php');
		require_once(dirname( __FILE__ ) . '/mock_classes/mock_class.album.module.manager.php');
		require_once(dirname( __FILE__ ) . '/mock_classes/mock_class.media.module.manager.php');
		require_once(dirname( __FILE__ ) . '/mock_classes/mock_class.network.module.manager.php');
		require_once(dirname( __FILE__ ) . '/mock_classes/mock_class.disk.cache.php');
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

//			"core" => array(
//				array( "enable"=>true,  "name"=>"Typecasters", "mock"=>array(), "file"=>"/tests/core/test_class.typecast.php" ),
//				array( "enable"=>false, "name"=>"Sanitizers", "mock"=>array(), "file"=>"/tests/core/test_class.sanitizers.php" ),
//				array( "enable"=>true,  "name"=>"Version Check", "mock"=>array(), "file"=>"/tests/core/test_class.version.check.php" )
//			),
//		    
//			"util" => array(
//				array( "enable"=>true,  "name"=>"System", "mock"=>array(), "file"=>"/tests/util/test_class.utils.system.php" ),
//				array( "enable"=>true,  "name"=>"Math", "mock"=>array(), "file"=>"/tests/util/test_class.utils.math.php" ),
//				array( "enable"=>true,  "name"=>"Hash Table", "mock"=>array(), "file"=>"/tests/util/test_class.utils.hash.table.php" ),
//			    	array( "enable"=>true,  "name"=>"Trie Flatten", "mock"=>array(), "file"=>"/tests/util/test_class.utils.trie.flatten.php" ),
//			    	array( "enable"=>true,  "name"=>"Trie Clip", "mock"=>array(), "file"=>"/tests/util/test_class.utils.trie.clip.php" )			    
//			),		    
//
//			"system" => array(
//				array( "enable"=>false,  "name"=>"Event Logger", "mock"=>array(), "file"=>"/tests/system/test_system.logging.event.php" ),
//			),
//
//			"stats" => array(
//				array( "enable"=>false,  "name"=>"System Stats", "mock"=>array(), "file"=>"/tests/stats/test_class.stats.php" ),
//			),
//
//			"database" => array(
//				array( "enable"=>true,	"name"=>"Builder Delete", "mock"=>array(), "file"=>"/tests/database/test_db.builder.delete.php" ),
//				array( "enable"=>true,	"name"=>"Builder Indate", "mock"=>array(), "file"=>"/tests/database/test_db.builder.indate.php" ),
//				array( "enable"=>true,	"name"=>"Builder Insert", "mock"=>array(), "file"=>"/tests/database/test_db.builder.insert.php" ),
//				array( "enable"=>true,	"name"=>"Builder Left Join", "mock"=>array(), "file"=>"/tests/database/test_db.builder.join.left.php" ),
//				array( "enable"=>true,	"name"=>"Builder Standard Join", "mock"=>array(), "file"=>"/tests/database/test_db.builder.join.php" ),			    
//				array( "enable"=>true,	"name"=>"Builder Select", "mock"=>array(), "file"=>"/tests/database/test_db.builder.select.php" ),
//				array( "enable"=>true,	"name"=>"Builder Table", "mock"=>array(), "file"=>"/tests/database/test_db.builder.table.php" ),
//				array( "enable"=>true,	"name"=>"Builder Update", "mock"=>array(), "file"=>"/tests/database/test_db.builder.update.php" ),
//				array( "enable"=>true,	"name"=>"Builder Where", "mock"=>array(), "file"=>"/tests/database/test_db.builder.where.php" ),
//				array( "enable"=>true,	"name"=>"Builder Where-Trie", "mock"=>array(), "file"=>"/tests/database/test_db.builder.where.trie.php" ),				    
//				array( "enable"=>true,	"name"=>"Builder Where-Matrix", "mock"=>array(), "file"=>"/tests/database/test_db.builder.where.matrix.php" ),			    
//				array( "enable"=>true,	"name"=>"Builder Insert", "mock"=>array(), "file"=>"/tests/database/test_db.builder.insert.php" ),			    			    
//				array( "enable"=>true,	"name"=>"Result Formatters", "mock"=>array(), "file"=>"/tests/database/test_db.core.formatters.php" ),	
//				array( "enable"=>true,	"name"=>"Op Delete", "mock"=>array(), "file"=>"/tests/database/test_db.op.delete.php" ),			    
//				array( "enable"=>true,	"name"=>"Op Indate", "mock"=>array(), "file"=>"/tests/database/test_db.op.indate.php" ),
//				array( "enable"=>true,	"name"=>"Op Insert", "mock"=>array(), "file"=>"/tests/database/test_db.op.insert.php" ),			    
//				array( "enable"=>true,	"name"=>"Op Insert (id check)", "mock"=>array(), "file"=>"/tests/database/test_db.op.insert.id.php" ),			    
//				array( "enable"=>true,	"name"=>"Op Standard Join", "mock"=>array(), "file"=>"/tests/database/test_db.op.join.php" ),
//				array( "enable"=>true,	"name"=>"Op Left Join", "mock"=>array(), "file"=>"/tests/database/test_db.op.join.left.php" ),			    			    
//				array( "enable"=>true,	"name"=>"Op Tables", "mock"=>array(), "file"=>"/tests/database/test_db.op.table.php" ),
//				array( "enable"=>true,	"name"=>"Op Transactions", "mock"=>array(), "file"=>"/tests/database/test_db.op.transaction.php" ),
//				array( "enable"=>true,	"name"=>"Op Update", "mock"=>array(), "file"=>"/tests/database/test_db.op.update.php" ),			    			    			    
//			),
//
//			"memory_cache" => array(
//				array( "enable"=>true,  "name"=>"Loopback", "mock"=>array(), "file"=>"/tests/cache_memory/test_cache.driver.loopback.php" ),
//				array( "enable"=>true,  "name"=>"Thread", "mock"=>array(), "file"=>"/tests/cache_memory/test_cache.driver.thread.php" ),				    
//				array( "enable"=>true,  "name"=>"APC", "mock"=>array(), "file"=>"/tests/cache_memory/test_cache.driver.apc.php" ),	
//			    	array( "enable"=>false,  "name"=>"Memcached", "mock"=>array(), "file"=>"/tests/cache_memory/test_cache.driver.memcached.php" ),
//			    	array( "enable"=>false,  "name"=>"Redis", "mock"=>array(), "file"=>"/tests/cache_memory/test_cache.driver.redis.php" ),			    
//			),
//		    
//			"disk_cache" => array(
//				array( "enable"=>false,  "name"=>"Disk Cache", "mock"=>array(), "file"=>"/tests/cache_disk/test_disk.cache.php" ),
//			),

			"base" => array(
				array( "enable"=>false,	 "name"=>"L5 Paged (Add)", "mock"=>array(), "file"=>"/tests/base_classes/test_base.db.store.paged.L5_add.php" ),
				array( "enable"=>false,	 "name"=>"L5 Paged (Set)", "mock"=>array(), "file"=>"/tests/base_classes/test_base.db.store.paged.L5_set.php" ),
				array( "enable"=>false,	 "name"=>"L5 Paged (Get)", "mock"=>array(), "file"=>"/tests/base_classes/test_base.db.store.paged.L5_get.php" ),
				array( "enable"=>true,	 "name"=>"L5 Paged (Replace)", "mock"=>array(), "file"=>"/tests/base_classes/test_base.db.store.paged.L5_replace.php" ),			    
			    
				array( "enable"=>false,	 "name"=>"L3 Monolithic", "mock"=>array(), "file"=>"/tests/base_classes/test_base.db.store.monolithic.L3.php" ),			    
				array( "enable"=>false,  "name"=>"Module Manager", "mock"=>array(), "file"=>"/tests/base_classes/test_base.module.manager.php" ),
				array( "enable"=>false,  "name"=>"Dictionary Base", "mock"=>array(), "file"=>"/tests/base_classes/test_base.db.dictionary.php" )
			),

			"store" => array(
				array( "enable"=>false,  "name"=>"System Config", "mock"=>array(), "file"=>"/tests/store/test_config.system.php" ),
				array( "enable"=>false,  "name"=>"User Settings", "mock"=>array(), "file"=>"/tests/store/test_user.data.php" ),
			),

			"album" => array(
				array( "enable"=>false,  "name"=>"AlbumType Data", "mock"=>array(), "file"=>"/tests/album/test_album.type.data.php" ),
				array( "enable"=>false,  "name"=>"AlbumType Levels", "mock"=>array(), "file"=>"/tests/album/test_album.type.level.php" ),
				array( "enable"=>false,  "name"=>"AlbumType Policy", "mock"=>array(), "file"=>"/tests/album/test_album.type.policy.php" ),
				array( "enable"=>false,  "name"=>"Album Module Manager", "mock"=>array(), "file"=>"/tests/album/test_album.module.manager.php" ),
				array( "enable"=>false,  "name"=>"Album Object", "mock"=>array(), "file"=>"/tests/album/test_class.album.object.php" ),
				array( "enable"=>false,  "name"=>"Album Object Manager", "mock"=>array(), "file"=>"/tests/album/test_class.album.manager.php" ),
				array( "enable"=>false,  "name"=>"Album Template", "mock"=>array(), "file"=>"/tests/album/test_class.album.template.php" )
			),

			"media" => array(
				array( "enable"=>false,  "name"=>"Media Object", "mock"=>array(), "file"=>"/tests/media/test_class.media.object.php" ),
				array( "enable"=>false,  "name"=>"Media Object Manager", "mock"=>array(), "file"=>"/tests/media/test_class.media.manager.php" ),
				array( "enable"=>false,  "name"=>"Media Template", "mock"=>array(), "file"=>"/tests/media/test_class.media.template.php" )
			),

			"nav" => array(
				array( "enable"=>false,  "name"=>"Location Modules", "mock"=>array(), "file"=>"/tests/nav/test_class.location.module.php" ),
				array( "enable"=>false,  "name"=>"Location Policy", "mock"=>array(), "file"=>"/tests/nav/test_class.location.policy.php" ),
				array( "enable"=>false,	 "name"=>"Navigation Core", "mock"=>array(), "file"=>"/tests/nav/test_class.navigation.php" ),
				array( "enable"=>false,  "name"=>"Module Slug", "mock"=>array(), "file"=>"/tests/nav/test_class.module.slug.php" )
			),

			"meta" => array(
				array( "enable"=>false,  "name"=>"Member Tags", "mock"=>array(), "file"=>"/tests/meta/test_class.member.tags.php" ),
				array( "enable"=>false,  "name"=>"Keyword Tags", "mock"=>array(), "file"=>"/tests/meta/test_keyword.php" ),
				array( "enable"=>false,  "name"=>"EXIF Data", "mock"=>array(), "file"=>"/tests/meta/test_exif.php" )
			),

			"rbac" => array(
				array( "enable"=>false,  "name"=>"Group Keyring", "mock"=>array(), "file"=>"/tests/rbac/test_user.group.keyring.php" ),
				array( "enable"=>false,  "name"=>"Group Members", "mock"=>array(), "file"=>"/tests/rbac/test_user.group.members.php" ),
				array( "enable"=>false,  "name"=>"Group Types", "mock"=>array(), "file"=>"/tests/rbac/test_user.group.types.php" ),
				array( "enable"=>false,  "name"=>"Key Types", "mock"=>array(), "file"=>"/tests/rbac/test_user.key.types.php" ),
				array( "enable"=>false,  "name"=>"User Keyring", "mock"=>array(), "file"=>"/tests/rbac/test_user.keyring.php" )
			),

			"spanner" => array(
				array( "enable"=>false,  "name"=>"Spanner", "mock"=>array(), "file"=>"/tests/spanner/test_class.thumbs.spanner.php" )
			)

		);

	}

}

$cls = new FOX_testPlan();

global $razor;
$razor->registerTestPlan($cls);

?>