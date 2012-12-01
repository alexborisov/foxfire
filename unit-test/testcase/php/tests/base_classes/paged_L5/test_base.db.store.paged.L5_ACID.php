<?php

/**
 * L5 PAGED ABSTRACT DATASTORE TEST CLASS
 * This class is used to instantiate the abstract base class, and create its database structure array
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

class FOX_dataStore_paged_L5_tester_ACID extends FOX_dataStore_paged_L5_base {
    

	public static $struct = array(

		"table" => "FOX_dataStore_paged_L5_base",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_dataStore_paged_L5_base",
		"cache_strategy" => "paged",
		"cache_engine" => array("memcached", "redis", "apc", "thread"),	    
		"columns" => array(
		    "L5" =>	array(	"php"=>"int",    "sql"=>"int",	"format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",		"auto_inc"=>false,  "default"=>null,
			// This forces every zone + rule + key_type + key_id combination to be unique
			"index"=>array("name"=>"top_level_index",	"col"=>array("L5", "L4", "L3", "L2", "L1"), "index"=>"PRIMARY"), "this_row"=>true),
		    "L4" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "L3" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "L2" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "L1" =>	array(	"php"=>"int",	    "sql"=>"int",	"format"=>"%d", "width"=>null,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "L0" =>	array(	"php"=>"serialize", "sql"=>"longtext",	"format"=>"%s", "width"=>null,	"flags"=>"",		"auto_inc"=>false,  "default"=>null,	"index"=>false),
		 )
	);	

	public static function _struct() {

		return self::$struct;
	}
	
	
	public function __construct(){
	    

		$this->process_id = 1337;
		
		// Generate our own cache singleton, and only enable the 'thread'
		// engine to eliminate potential problems with APC, Memcached, etc
		
		$this->mCache = new FOX_mCache();
		$this->mCache->setActiveEngines(array('thread'));
		
		$this->init();
		
	}
	
		
}  // ENDOF: class FOX_dataStore_paged_L5_tester_ACID
  
                                      

/**
 * FOXFIRE UNIT TEST SCRIPT - L5 PAGED ABSTRACT DATASTORE CLASS - ACID (Atomicity, Consistency, Isolation, Durability)
 * 
 * @see http://en.wikipedia.org/wiki/ACID
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

class core_L5_paged_abstract_setMethods extends RAZ_testCase {

	
    	function setUp() {

	    
		parent::setUp();
				
		
		// Install the db table
		// ===========================================
		
		$this->cls = new FOX_dataStore_paged_L5_tester_ACID();
		
		try {
			$install_ok = $this->cls->install();
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
				
		$this->assertEquals(true, $install_ok);	
		
		
		// Clear table to guard against previous failed test
		// ===========================================
		
		try {
			$truncate_ok = $this->cls->truncate();
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
				
		$this->assertEquals(true, $truncate_ok);
		
		
		// Flush cache to guard against previous failed test
		// ===========================================
		
		try {
			$flush_ok = $this->cls->flushCache();
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
				
		$this->assertEquals(true, $flush_ok);		

	}

	
       /**
	* Test fixture for setL1() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_setL1() {
	
		return;
		
		// Scenario #1 
		// =====================================================
		// PID 1 locks three cache pages
		// PID 2 locks the namespace
		// PID 1 tries to overwrite the pages it locked
		
		// Scenario #2 
		// =====================================================
		// PID 1 locks three cache pages
		// PID 2 flushes the namespace
		// PID 1 tries to save the pages it locked		
		
		// Scenario #3
		// =====================================================
		// PID 1 locks three cache pages
		// PID 2 tries to delete two of those pages
		// PID 1 tries to save the pages it locked
		
	}
	
	
	
	
	function tearDown() {
	   
		$this->cls = new FOX_dataStore_paged_L5_tester_setMethods();
		$unistall_ok = $this->cls->uninstall();
		
		$this->assertEquals(true, $unistall_ok);
		
		parent::tearDown();
	}

    
}

?>