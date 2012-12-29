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
		
		// Generate our own cache singleton. We *have* to use a real persistent
		// caching engine so that the cache threads share a common cache.
		
		$this->mCache = new FOX_mCache();
		$this->mCache->setActiveEngines(array('apc'));	
		
		$this->init();		
		
	}
	
		
}  // ENDOF: class FOX_dataStore_paged_L5_tester_ACID
                                       

/**
 * FOXFIRE UNIT TEST SCRIPT - L5 PAGED ABSTRACT DATASTORE CLASS - ACID (Atomicity, Consistency, Isolation, Durability)
 * 
 * @see http://en.wikipedia.org/wiki/ACID
 * 
 * Background concepts:
 * 
 * @see http://en.wikipedia.org/wiki/Race_condition
 * @see http://en.wikipedia.org/wiki/Mutual_exclusion
 * @see http://en.wikipedia.org/wiki/Concurrency_control
 * @see http://en.wikipedia.org/wiki/Deadlock
 * @see http://en.wikipedia.org/wiki/Resource_starvation
 * @see http://en.wikipedia.org/wiki/Linearizability
 * @see http://en.wikipedia.org/wiki/Lock_(computer_science)
 * @see http://en.wikipedia.org/wiki/Symmetric_multiprocessing
 * 
 * And why they're important:
 * 
 * @see http://en.wikipedia.org/wiki/Therac-25
 * @see http://en.wikipedia.org/wiki/2003_North_America_blackout#Findings
 * @see http://en.wikipedia.org/wiki/Spirit_rover#Sol_17_.28January_21.2C_2004.29_flash_memory_management_anomaly
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

class core_L5_paged_abstract_ACID extends RAZ_testCase {

	
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
	* Loads the class instance with the test data set, and verifies it was correctly written
        * to the database and cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function loadData() {

 
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>null),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>2, "L0"=>false),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T", "L1"=>1, "L0"=>true),
				array( "L5"=>1, "L4"=>"X", "L3"=>"Z", "L2"=>"Z", "L1"=>3, "L0"=>(int)0),	

				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>(int)1),
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>2, "L0"=>(int)-1),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"T", "L1"=>3, "L0"=>(float)1.7),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"Z", "L2"=>"Z", "L1"=>4, "L0"=>(float)-1.6),
		    
		    		array( "L5"=>2, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>(string)"foo"),
		    		array( "L5"=>2, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>2, "L0"=>array(null, true, false, 1, 1.0, "foo")),
		    		array( "L5"=>2, "L4"=>"X", "L3"=>"Z", "L2"=>"Z", "L1"=>3, "L0"=>$test_obj)	
		    
		);		
		
		// Load class with data
		// ===============================================================
				    					
		try {
			$rows_changed = $this->cls->setL1_multi($test_data, $ctrl=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)11 to indicate  11 keys were added
		$this->assertEquals(11, $rows_changed); 								
		
		
		// Check cache state
		// ===============================================================	
		
		// NOTE: the LUT's won't be set at this point, because we haven't done any 
		// database reads that give objects authority
		
		$check = array(
				1=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>null,
												2=>false
										    ),
										    'T'=>array(	1=>true )							    
									),
									'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
							    ),	
							    'Y'=>array(	'K'=>array( 'K'=>array(	
												1=>(int)1,
												2=>(int)-1
										    ),
										    'T'=>array(	3=>(float)1.7 )							    
									),
									'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
							    )
					    )
				),			
				2=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )	
					    )						
				)		    		    
		);
		
		$this->assertEquals($check, $this->cls->cache);	
		
		
		// Check db state
		// ===============================================================		
		
		$check = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>null,
										2=>false
								    ),
								    'T'=>array(	1=>true )							    
							),
							'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
					    ),	
					    'Y'=>array(	'K'=>array( 'K'=>array(	
										1=>(int)1,
										2=>(int)-1
								    ),
								    'T'=>array(	3=>(float)1.7 )							    
							),
							'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )					    
				),			
				2=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
				 )		    		    
		);		
		
		$db = new FOX_db();	
		
		$columns = null;
		$args = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L5','L4','L3','L2','L1')
		);
		
		try {			
			$result = $db->runSelectQuery($this->cls->_struct(), $args, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
                $this->assertEquals($check, $result);		
				
	}
	
	
       /**
	* Test getMulti() with locked persistent cache pages
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getMulti_persistentCache_lockedPages() {
		    

		// Set up victim class and load it with data
		// ===========================================================
	    
		$debug_handler = new FOX_debugHandler();
	    
		$this->cls->init(array('debug_on'=>true, 'debug_handler'=>$debug_handler));
		
		$this->cls->process_id = 6900;
		
		self::loadData();	
		
		
		// Set an action on the victim class 'persistent_cache_lock_end'
		// event that attempts to read from the pages it locked using
		// a class instance owned by a different PID
		// ===========================================================
		
		try {	
			$test_fixture =& $this;
		    
			$debug_handler->addEvent( array(
			    
				'type'=>'trap',
				'pid'=>6900,
				'function'=>'getMulti', 
				'text'=>'persistent_cache_lock_end',	
			    
				'modifier'=> function($parent, $vars) use (&$test_fixture) {		   

					$attacker = new FOX_dataStore_paged_L5_tester_ACID();
					$attacker->process_id = 2600;

					$ctrl = array(		    
							'validate'=>true,
							'r_mode'=>'matrix'		    
					);

					$valid = false;

					try {			
						$result = $attacker->getL1(1, 'Y', 'K', 'K', 2, $ctrl, $valid);
						$test_fixture->fail("getMulti() failed to throw exception on persistent cache page lock collision");
					}
					catch (FOX_exception $child) { }

					unset($attacker);
					
					return array();		    		    		    
				}
		
			));
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
	
		
		// Run the victim class getL1 method, which uses the getMulti() method, 
		// causing the action to fire and the modifier function to run. The 
		// attacker PID should throw an exception. The victim PID should operate
		// as normal.
		// ===========================================================
		
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'matrix'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL1(1, 'Y', 'K', 'K', 2, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);	
		$this->assertEquals(-1, $result);	
		
	}
	
	
       /**
	* Test getMulti() with a database read failure
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getMulti_database_readFailure() {
		    

		// Set up victim class and load it with data
		// ===========================================================
	    
		$debug_handler = new FOX_debugHandler();
	    
		$this->cls->init(array('debug_on'=>true, 'debug_handler'=>$debug_handler));
		
		$this->cls->process_id = 6900;
		
		self::loadData();	
		
		
		// Set an action on the victim class 'persistent_cache_lock_end'
		// event that attempts to read from the pages it locked using
		// a class instance owned by a different PID
		// ===========================================================
		
		try {	
			$test_fixture =& $this;
		    
			$debug_handler->addEvent( array(
			    
				'type'=>'trap',
				'pid'=>6900,
				'function'=>'getMulti', 
				'text'=>'persistent_cache_lock_end',	
			    
				'modifier'=> function($parent, $vars) use (&$test_fixture) {		   

					$attacker = new FOX_dataStore_paged_L5_tester_ACID();
					$attacker->process_id = 2600;

					$ctrl = array(		    
							'validate'=>true,
							'r_mode'=>'matrix'		    
					);

					$valid = false;

					try {			
						$result = $attacker->getL1(1, 'Y', 'K', 'K', 2, $ctrl, $valid);
						$test_fixture->fail("getMulti() failed to throw exception on persistent cache page lock collision");
					}
					catch (FOX_exception $child) { }

					unset($attacker);
					
					return array();		    		    		    
				}
		
			));
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
	
		
		// Run the victim class getL1 method, which uses the getMulti() method, 
		// causing the action to fire and the modifier function to run. The 
		// attacker PID should throw an exception. The victim PID should operate
		// as normal.
		// ===========================================================
		
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'matrix'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL1(1, 'Y', 'K', 'K', 2, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);	
		$this->assertEquals(-1, $result);	

		
	}	
	
	
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
	
	// Scenario #4
	// =====================================================
	// PID 1 reads a key, finds it isn't in the cache, and reads it from the DB
	// PID 2 writes to the same key after PID 1 has read from the database, then writes the updated value to the cache
	// PID 1 tries to overwrite the key with the old value	
	
	function tearDown() {
	   
		$this->cls = new FOX_dataStore_paged_L5_tester_ACID();
		$unistall_ok = $this->cls->uninstall();
		
		$this->assertEquals(true, $unistall_ok);
		
		parent::tearDown();
	}

    
}

?>