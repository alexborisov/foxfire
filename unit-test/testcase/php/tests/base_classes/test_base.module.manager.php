<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - MODULE MANAGER CLASS
 * Exercises all functions of the class
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package FoxFire
 * @subpackage Unit Test
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */


class FOX_moduleManager_test_stub extends FOX_module_manager_base {


	var $cache;			    // Main cache array for this class

	var $admin_modules = array();	    // Array of all module php_classes as loaded on the admin screen

	var $targets = array();		    // Array of all available targets for the template the
					    // page module is currently using (or default views if not supplied by template)

	var $views = array();		    // Array of all available views for the template the page module
					    // is currently using (or default roles if not supplied by template)

	var $caps = array();		    // Array of all available capability locks for the template the page module
					    // is currently using (or default roles if not supplied by template)

	var $thumbs = array();		    // Array of thumbnail configuration data (or default values if not supplied by template)

	var $dir_override;		    // Used during unit testing. If set, the class will load page modules
					    // from the path specified.


	// ============================================================================================================ //

        // DB table names and structures are hard-coded into the class. This allows class methods to be
	// fired from an AJAX call, without loading the entire BP stack.

	public static $struct = array(

		"table" => "fox_sys_module_manager_test_stub",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_moduleManager_test_stub",
		"cache_strategy" => "monolithic",
		"columns" => array(
		    "module_id" =>	    array(  "php"=>"int",	"sql"=>"tinyint",	"format"=>"%d", "width"=>null,"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>true,   "default"=>null,	"index"=>"PRIMARY"),
		    "slug" =>	    array(  "php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>32,	    "flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>"UNIQUE"),
		    "name" =>	    array(  "php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>32,	    "flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>"UNIQUE"),
		    "php_class" =>	    array(  "php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>255,	    "flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>"UNIQUE"),
		    "active" =>	    array(  "php"=>"bool",	"sql"=>"tinyint",	"format"=>"%d", "width"=>1,	    "flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>0,	"index"=>true)
		 )
	);

	public static $offset = "test_stub";

	// PHP allows this: $foo = new $class_name; $result = $foo::$struct; but does not allow this: $result = $class_name::$struct;
	// or this: $result = $class_name::get_struct(); ...so we have to do this: $result = call_user_func( array($class_name,'_struct') );

	public static function _struct() {

		return self::$struct;
	}

	public static function _offset() {

		return self::$offset;
	}


	// ================================================================================================================

	public function FOX_moduleManager_test_stub($error=null) {

		$cache_ok = self::loadCache($cache_get_error);

		if(!$cache_ok){

			$error = array(
				'numeric'=>1,
				'text'=>"Cache load error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$cache_get_error
			);
			return false;
		}
	}


}  // ENDOF class FOX_moduleManager_test_stub



class core_FOX_moduleManager extends RAZ_testCase {

	var $cls;

    	function setUp() {

		parent::setUp();

		$this->cls = new FOX_moduleManager_test_stub();
		$result = $this->cls->install($error);

		$this->assertEquals(true, $result, FOX_debug::formatError_print($error) );
		unset($error);
	}


	function test_addMulti(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify correct cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				2 => true,
				4 => true
			 ),
			 "all_cached" => true
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Add more modules
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_06", "name"=> "name_06",  "php_class"=> "class_06", "active"=> true),
				array( "slug"=> "slug_07", "name"=> "name_07",  "php_class"=> "class_07", "active"=> false)
		);

		$check_ids = array(0=>6, 1=>7);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false),
				6 => array( "module_id" => 6, "slug"=> "slug_06", "name"=> "name_06",  "php_class"=> "class_06", "active"=> true),
				7 => array( "module_id" => 7, "slug"=> "slug_07", "name"=> "name_07",  "php_class"=> "class_07", "active"=> false)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify correct cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false),
				6 => array( "module_id" => 6, "slug"=> "slug_06", "name"=> "name_06",  "php_class"=> "class_06", "active"=> true),
				7 => array( "module_id" => 7, "slug"=> "slug_07", "name"=> "name_07",  "php_class"=> "class_07", "active"=> false)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
				"class_06" =>6,
				"class_07" =>7,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,
				"slug_06" =>6,
				"slug_07" =>7,
			 ),
			 "active_modules" => array(
				1 => true,
				2 => true,
				4 => true,
				6 => true
			 ),
			 "all_cached" => true
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Reject on slug crash
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_07", "name"=> "name_07_ALT",  "php_class"=> "class_07_ALT", "active"=> true),
				array( "slug"=> "slug_08", "name"=> "name_08",  "php_class"=> "class_08", "active"=> false)
		);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals(false, $result, FOX_debug::formatError_print($error));
		unset($error);

		$this->assertEquals($check_cache, $this->cls->cache);	// Verify cache has not changed

		// Reject on name crash
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_07_ALT", "name"=> "name_07",  "php_class"=> "class_07_ALT", "active"=> true),
				array( "slug"=> "slug_08", "name"=> "name_08",  "php_class"=> "class_08", "active"=> false)
		);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals(false, $result, FOX_debug::formatError_print($error));
		unset($error);

		$this->assertEquals($check_cache, $this->cls->cache);	// Verify cache has not changed

		$this->assertEquals($check_cache, $this->cls->cache);	// Verify cache has not changed

		// Reject on class crash
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_07_ALT", "name"=> "name_07_ALT",  "php_class"=> "class_07", "active"=> true),
				array( "slug"=> "slug_08", "name"=> "name_08",  "php_class"=> "class_08", "active"=> false)
		);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals(false, $result, FOX_debug::formatError_print($error));
		unset($error);

		$this->assertEquals($check_cache, $this->cls->cache);	// Verify cache has not changed


		// Clear cache and check correct data is returned
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error) );
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

	}


	function test_register(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data and verify correct module_id's are returned
		// ===========================================================

		$test_data = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		foreach( $test_data as $module_id => $data){

			$result = $this->cls->register($data['slug'], $data['name'], $data['php_class'], $data['active'], $error);
			$this->assertEquals($module_id, $result, FOX_debug::formatError_print($error));
			unset($error);
		}

		// Verify correct cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,
			 ),
			 "active_modules" => array(
				1 => true,
				2 => true,
				4 => true
			 )

		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check correct data is returned
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);


	} // End of test_register



	function test_getActiveModules(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true)
		);

		$result = $this->cls->getActiveModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check correct data is returned
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getActiveModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);


	} // End of test_getActiveModules


	function test_getByID_single(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true);

		$result = $this->cls->getByID(2, $error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear the cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getByID(2, $error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify correct cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true)
			 ),
			 "php_class" => array(
				"class_02" =>2
			 ),
			 "slug" => array(
				"slug_02" =>2
			 ),
			 "active_modules" => array(
				2 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Try to fetch a non-existent item
		// ===================================================

		$result = $this->cls->getByID(999, $error);
		$this->assertEquals(null, $result);

		// Verify correct cache state
		// ===================================================

		$this->assertEquals(null, $error);


	} // End of test_getByID_single


	function test_getByID_multi(){


		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				2 => array( "module_id"=>2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id"=>3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				5 => array( "module_id"=>5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$result = $this->cls->getByID(array(2,3,5), $error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear the cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error) );
		unset($error);

		$result = $this->cls->getByID(array(2,3,5), $error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify correct cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
			 ),
			 "php_class" => array(
				"class_02" =>2,
				"class_03" =>3,
				"class_05" =>5
			 ),
			 "slug" => array(
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_05" =>5
			 ),
			 "active_modules" => array(
				2 => true
			 )

		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Try to fetch multiple non-existent items
		// ===================================================

		$result = $this->cls->getByID(array(222,333,444), $error);
		$this->assertEquals(array(), $result);

		// Verify correct cache state
		// ===================================================

		$this->assertEquals(null, $error);


	} // End of test_getByID_multi


	function test_getByClass_single(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true);

		$result = $this->cls->getByClass("class_02", $error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear the cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getByClass("class_02", $error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify correct cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true)
			 ),
			 "php_class" => array(
				"class_02" =>2
			 ),
			 "slug" => array(
				"slug_02" =>2
			 ),
			 "active_modules" => array(
				2 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Try to fetch a non-existent item
		// ===================================================

		$result = $this->cls->getByClass("fail_class", $error);
		$this->assertEquals(null, $result);

		// Verify correct cache state
		// ===================================================

		$this->assertEquals(null, $error);


	} // End of test_getByClass_single


	function test_getByClass_multi(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				"class_02" => array( "module_id"=>2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				"class_03" => array( "module_id"=>3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				"class_05" => array( "module_id"=>5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$result = $this->cls->getByClass(array("class_02", "class_03", "class_05"), $error);

		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear the cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error) );
		unset($error);

		$result = $this->cls->getByClass(array("class_02", "class_03", "class_05"), $error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify correct cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
			 ),
			 "php_class" => array(
				"class_02" =>2,
				"class_03" =>3,
				"class_05" =>5
			 ),
			 "slug" => array(
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_05" =>5
			 ),
			 "active_modules" => array(
				2 => true
			 )

		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Try to fetch multiple non-existent items
		// ===================================================

		$result = $this->cls->getByClass(array("fail_1", "fail_2", "fail_3"), $error);
		$this->assertEquals(array(), $result);

		// Verify correct cache state
		// ===================================================

		$this->assertEquals(null, $error);


	} // End of test_getByClass_multi


	function test_getBySlug_single(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true);

		$result = $this->cls->getBySlug("slug_02", $error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear the cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getBySlug("slug_02", $error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify correct cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true)
			 ),
			 "php_class" => array(
				"class_02" =>2
			 ),
			 "slug" => array(
				"slug_02" =>2
			 ),
			 "active_modules" => array(
				2 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Try to fetch a non-existent item
		// ===================================================

		$result = $this->cls->getBySlug("fail_slug", $error);
		$this->assertEquals(null, $result);

		// Verify correct cache state
		// ===================================================

		$this->assertEquals(null, $error);


	} // End of test_getBySlug_single


	function test_getBySlug_multi(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				"slug_02" => array( "module_id"=>2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				"slug_03" => array( "module_id"=>3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				"slug_05" => array( "module_id"=>5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$result = $this->cls->getBySlug(array("slug_02", "slug_03", "slug_05"), $error);

		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear the cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getBySlug(array("slug_02", "slug_03", "slug_05"), $error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify correct cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
			 ),
			 "php_class" => array(
				"class_02" =>2,
				"class_03" =>3,
				"class_05" =>5
			 ),
			 "slug" => array(
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_05" =>5
			 ),
			 "active_modules" => array(
				2 => true
			 )

		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Try to fetch multiple non-existent items
		// ===================================================

		$result = $this->cls->getBySlug(array("fail_1", "fail_2", "fail_3"), $error);
		$this->assertEquals(array(), $result);

		// Verify correct cache state
		// ===================================================

		$this->assertEquals(null, $error);


	} // End of test_getBySlug_multi


	function test_activateByID_single(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> false),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify initial cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> false),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				4 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Activate a module
		// ===================================================

		$result = $this->cls->activateByID(2, $error);
		$this->assertEquals(1, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify updated cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> false),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				2 => true,
				4 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Return zero on trying to activate nonexistent module
		// ===================================================

		$result = $this->cls->activateByID(99, $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Return zero on trying to activate already activated module
		// ==========================================================

		$result = $this->cls->activateByID(4, $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify cache state has not changed
		// ===================================================

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> false),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);


	} // End of test_activateByID_single


	function test_activateByID_multi(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> false),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify initial cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> false),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				4 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Activate multiple modules
		// ===================================================

		$result = $this->cls->activateByID(array(1,2,5), $error);
		$this->assertEquals(3, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify updated cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				2 => true,
				4 => true,
				5 => true,
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Return zero on trying to activate nonexistent modules
		// ===================================================

		$result = $this->cls->activateByID(array(33,44,55), $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Return zero on trying to activate already activated module
		// ==========================================================

		$result = $this->cls->activateByID(array(1,2), $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify cache state has not changed
		// ===================================================

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);


	} // End of test_activateByID_multi


	function test_activateBySlug_single(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> false),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> false),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify initial cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> false),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> false),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Activate a module
		// ===================================================

		$result = $this->cls->activateBySlug("slug_02", $error);
		$this->assertEquals(1, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify updated cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> false),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> false),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				2 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Return zero on trying to activate nonexistent module
		// ===================================================

		$result = $this->cls->activateBySlug("fail_slug", $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Return zero on trying to activate already activated module
		// ==========================================================

		$result = $this->cls->activateByID("slug_04", $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify cache state has not changed
		// ===================================================

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Activate three more modules
		// ===================================================

		$result = $this->cls->activateBySlug("slug_05", $error);
		$this->assertEquals(1, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->activateBySlug("slug_03", $error);
		$this->assertEquals(1, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->activateBySlug("slug_04", $error);
		$this->assertEquals(1, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify updated cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> false),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				2 => true,
				3 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> false),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);


	} // End of test_activateBySlug_single


	function test_activateBySlug_multi(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> false),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify initial cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> false),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				4 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Activate multiple modules
		// ===================================================

		$result = $this->cls->activateBySlug(array("slug_01","slug_02","slug_05"), $error);
		$this->assertEquals(3, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify updated cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				2 => true,
				4 => true,
				5 => true,
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Return zero on trying to activate nonexistent modules
		// ===================================================

		$result = $this->cls->activateBySlug(array("fail_01","fail_02","fail_03"), $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Return zero on trying to activate already activated module
		// ==========================================================

		$result = $this->cls->activateByID(array("slug_01","slug_02"), $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify cache state has not changed
		// ===================================================

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);


	} // End of test_activateBySlug_multi


	function test_deactivateByID_single(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify initial cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				3 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Deactivate a module
		// ===================================================

		$result = $this->cls->deactivateByID(3, $error);
		$this->assertEquals(1, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify updated cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Return zero on trying to deactivate nonexistent module
		// ===================================================

		$result = $this->cls->deactivateByID(99, $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Return zero on trying to deactivate an inactive module
		// ==========================================================

		$result = $this->cls->deactivateByID(2, $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify cache state has not changed
		// ===================================================

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);


	} // End of test_deactivateByID_single


	function test_deactivateByID_multi(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify initial cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				3 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Activate multiple modules
		// ===================================================

		$result = $this->cls->deactivateByID(array(3,4,5), $error);
		$this->assertEquals(3, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify updated cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> false),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Return zero on trying to deactivate multiple nonexistent modules
		// ===================================================

		$result = $this->cls->deactivateByID(array(33,44,55), $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Return zero on trying to deactivate multiple inactive modules
		// ==========================================================

		$result = $this->cls->deactivateByID(array(3,4,5), $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify cache state has not changed
		// ===================================================

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> false),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);


	} // End of test_deactivateByID_multi


	function test_deactivateBySlug_single(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify initial cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				3 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Deactivate a module
		// ===================================================

		$result = $this->cls->deactivateBySlug("slug_03", $error);
		$this->assertEquals(1, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify updated cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Return zero on trying to deactivate nonexistent module
		// ===================================================

		$result = $this->cls->deactivateBySlug("fail_slug", $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Return zero on trying to deactivate an inactive module
		// ==========================================================

		$result = $this->cls->deactivateBySlug("slug_02", $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify cache state has not changed
		// ===================================================

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);


	} // End of test_deactivateBySlug_single


	function test_deactivateBySlug_multi(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify initial cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				3 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Activate multiple modules
		// ===================================================

		$result = $this->cls->deactivateBySlug(array("slug_03","slug_04","slug_05"), $error);
		$this->assertEquals(3, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify updated cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> false),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Return zero on trying to deactivate multiple nonexistent modules
		// ===================================================

		$result = $this->cls->deactivateByID(array("fail_1","fail_2","fail_3"), $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Return zero on trying to deactivate multiple inactive modules
		// ==========================================================

		$result = $this->cls->deactivateByID(array("slug_02","slug_04","slug_05"), $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify cache state has not changed
		// ===================================================

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> false),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);


	} // End of test_deactivateBySlug_multi


	function test_deleteByID_single(){

	    	// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify initial cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				3 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Deactivate a module
		// ===================================================

		$result = $this->cls->deleteByID(3, $error);
		$this->assertEquals(1, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify updated cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Return zero on trying to deactivate nonexistent module
		// ===================================================

		$result = $this->cls->deleteByID(99, $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify cache state has not changed
		// ===================================================

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

	} // End of test_deleteByID_single


	function test_deleteByID_multi(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify initial cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				3 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Activate multiple modules
		// ===================================================

		$result = $this->cls->deleteByID(array(2,3,4), $error);
		$this->assertEquals(3, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify updated cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_05" =>5
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_05" =>5
			 ),
			 "active_modules" => array(
				1 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Return zero on trying to delete multiple nonexistent modules
		// ===================================================

		$result = $this->cls->deactivateByID(array(33,44,55), $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify cache state has not changed
		// ===================================================

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

	} // End of test_deleteByID_multi


	function test_deleteBySlug_single(){

	    	// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify initial cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				3 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Deactivate a module
		// ===================================================

		$result = $this->cls->deleteBySlug("slug_03", $error);
		$this->assertEquals(1, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify updated cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Return zero on trying to deactivate nonexistent module
		// ===================================================

		$result = $this->cls->deleteBySlug("fail_slug", $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify cache state has not changed
		// ===================================================

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

	} // End of test_deleteBySlug_single


	function test_deleteBySlug_multi(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify initial cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				3 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Activate multiple modules
		// ===================================================

		$result = $this->cls->deleteBySlug(array("slug_02","slug_03","slug_04"), $error);
		$this->assertEquals(3, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify updated cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_05" =>5
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_05" =>5
			 ),
			 "active_modules" => array(
				1 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Return zero on trying to delete multiple nonexistent modules
		// ===================================================

		$result = $this->cls->deactivateByID(array("fail_1","fail_2","fail_3"), $error);
		$this->assertEquals(0, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify cache state has not changed
		// ===================================================

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

	} // End of test_deleteBySlug_multi


	function test_edit_allFields_allChanged(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify initial cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				3 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// All fields, unique update values
		// ===================================================

		$data = array( "module_id" => 1, "slug"=> "slug_01_updated", "name"=> "name_01_updated",  "php_class"=> "class_01_updated", "active"=> false);
		$result = $this->cls->edit($data, $error);
		$this->assertEquals(1, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify updated cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01_updated", "name"=> "name_01_updated",  "php_class"=> "class_01_updated", "active"=> false),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01_updated" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01_updated" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				3 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01_updated", "name"=> "name_01_updated",  "php_class"=> "class_01_updated", "active"=> false),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);


	} // End of test_edit_allFields_allChanged


	function test_edit_allFields_someChanged(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> false),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify initial cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> false),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				3 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// All fields, partial unique values
		// ===================================================

		$data = array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04_updated",  "php_class"=> "class_04", "active"=> true);
		$result = $this->cls->edit($data, $error);
		$this->assertEquals(1, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify updated cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04_updated",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				3 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04_updated",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);


	} // End of test_edit_allFields_someChanged


	function test_edit_allFields_partials(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify initial cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				3 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// All fields, partial unique values
		// ===================================================

		$data = array( "module_id" => 5, "name"=> "name_05_updated");
		$result = $this->cls->edit($data, $error);
		$this->assertEquals(1, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify updated cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05_updated",  "php_class"=> "class_05", "active"=> true)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				3 => true,
				4 => true,
				5 => true
			 )
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> false),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> true),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05_updated",  "php_class"=> "class_05", "active"=> true)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Clear cache and check again
		// ===================================================

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);


	} // End of test_edit_allFields_partials


	function test_data_integrity(){

		// Clear the table and cache
		// ===================================================

		$result = $this->cls->truncate($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Load test data
		// ===================================================

		$insert_data = array(
				array( "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				array( "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				array( "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				array( "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				array( "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$check_ids = array(0=>1, 1=>2, 2=>3, 3=>4, 4=>5);

		$result = $this->cls->addMulti($insert_data, $error);
		$this->assertEquals($check_ids, $result, FOX_debug::formatError_print($error));	// Verify correct ids are returned
		unset($error);

		// Verify correct data is returned
		// ===================================================

		$check_db = array(
				// The key for each module array is the module_id
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
		);

		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify correct cache state
		// ===================================================

		$check_cache = array(

			"module_id" => array(
				1 => array( "module_id" => 1, "slug"=> "slug_01", "name"=> "name_01",  "php_class"=> "class_01", "active"=> true),
				2 => array( "module_id" => 2, "slug"=> "slug_02", "name"=> "name_02",  "php_class"=> "class_02", "active"=> true),
				3 => array( "module_id" => 3, "slug"=> "slug_03", "name"=> "name_03",  "php_class"=> "class_03", "active"=> false),
				4 => array( "module_id" => 4, "slug"=> "slug_04", "name"=> "name_04",  "php_class"=> "class_04", "active"=> true),
				5 => array( "module_id" => 5, "slug"=> "slug_05", "name"=> "name_05",  "php_class"=> "class_05", "active"=> false)
			 ),
			 "php_class" => array(
				"class_01" =>1,
				"class_02" =>2,
				"class_03" =>3,
				"class_04" =>4,
				"class_05" =>5,
			 ),
			 "slug" => array(
				"slug_01" =>1,
				"slug_02" =>2,
				"slug_03" =>3,
				"slug_04" =>4,
				"slug_05" =>5,

			 ),
			 "active_modules" => array(
				1 => true,
				2 => true,
				4 => true
			 ),
			 "all_cached" => true
		);

		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Fail on missing module_id
		// =================================

		$data = array("slug"=> "slug_01_updated", "name"=> "name_01_updated",  "php_class"=> "class_01_updated", "active"=> true);
		$result = $this->cls->edit($data, $unit_test=true);
		$this->assertEquals(false, $result);

		// Verify cache was not modified
		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Clear cache
		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify db was not modified
		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Fail on slug collision
		// =================================

		$data = array("module_id" => 1, "slug"=> "slug_02");
		$result = $this->cls->edit($data, $unit_test=true);
		$this->assertEquals(false, $result);

		// Verify cache was not modified
		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Clear cache
		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify db was not modified
		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Fail on php_class collision
		// =================================

		$data = array("module_id" => 1, "php_class"=> "class_02");
		$result = $this->cls->edit($data, $unit_test=true);
		$this->assertEquals(false, $result);

		// Verify cache was not modified
		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Clear cache
		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify db was not modified
		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Fail on name collision
		// =================================

		$data = array("module_id" => 1, "name"=> "name_02");
		$result = $this->cls->edit($data, $unit_test=true);
		$this->assertEquals(false, $result);

		// Verify cache was not modified
		$this->assertEquals($check_cache, $this->cls->cache);
		unset($error);

		// Clear cache
		$result = $this->cls->flushCache($error);
		$this->assertEquals(true, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Verify db was not modified
		$result = $this->cls->getAllModules($error);
		$this->assertEquals($check_db, $result, FOX_debug::formatError_print($error));
		unset($error);


	} // End of test_data_integrity


	function tearDown() {

		$this->cls = new FOX_moduleManager_test_stub();
		$this->cls->uninstall();

		parent::tearDown();
	}

}


?>