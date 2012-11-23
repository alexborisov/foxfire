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

class FOX_dataStore_paged_L5_tester_dropMethods extends FOX_dataStore_paged_L5_base {
    

	public static $struct = array(

		"table" => "FOX_dataStore_paged_L5_base",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_dataStore_paged_L5_base",
		"cache_strategy" => "paged",
		"cache_engine" => array("memcached", "redis", "apc", "thread"),	    
		"columns" => array(
		    "L5" =>	array(	"php"=>"int",    "sql"=>"int",	"format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,
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
	
	
}  // ENDOF: class FOX_dataStore_paged_L5_tester_dropMethods 

                                      

/**
 * FOXFIRE UNIT TEST SCRIPT - L5 PAGED ABSTRACT DATASTORE CLASS - DROP METHODS
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

class core_L5_paged_abstract_dropMethods extends RAZ_testCase {

	
    	function setUp() {

	    
		parent::setUp();
				
		
		// Install the db table
		// ===========================================
		
		$this->cls = new FOX_dataStore_paged_L5_tester_dropMethods();
		
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
		    		array( "L5"=>2, "L4"=>"X", "L3"=>"Z", "L2"=>"Z", "L1"=>3, "L0"=>$test_obj),
		    
				array( "L5"=>3, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>null),
				array( "L5"=>3, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>2, "L0"=>false),
				array( "L5"=>3, "L4"=>"X", "L3"=>"K", "L2"=>"T", "L1"=>1, "L0"=>true),
				array( "L5"=>3, "L4"=>"X", "L3"=>"Z", "L2"=>"Z", "L1"=>3, "L0"=>(int)0),	

				array( "L5"=>3, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>(int)1),
				array( "L5"=>3, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>2, "L0"=>(int)-1),
		    		array( "L5"=>3, "L4"=>"Y", "L3"=>"K", "L2"=>"T", "L1"=>3, "L0"=>(float)1.7),
		    		array( "L5"=>3, "L4"=>"Y", "L3"=>"Z", "L2"=>"Z", "L1"=>4, "L0"=>(float)-1.6),		    
		    
		);		
		
		// Load class with data
		// ####################################################################
				    					
		try {
			$rows_changed = $this->cls->setL1_multi($test_data, $ctrl=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)19 to indicate  19 keys were added
		$this->assertEquals(19, $rows_changed); 								
		
		
		// Check cache state
		// ####################################################################	
		
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
				),
				3=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
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
				)		    
		);
		
		$this->assertEquals($check, $this->cls->cache);	
		
		
		// Check db state
		// ####################################################################		
		
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
				),
				3=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
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
	* Test fixture for dropMulti() method, cold cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropMulti_COLD() {
	    

		self::loadData();
		
		
		// Flush the cache
		// ####################################################################
		
		try {
			$flush_ok = $this->cls->flushCache();
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
				
		$this->assertEquals(true, $flush_ok);		
				
		
		// COLD CACHE - Flushed after previous ADD operation
		// ===================================================================				
	    
		// Drop objects
		// ####################################################################
		
		$data = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>true
								    ),
								    'T'=>true							    
							),
							'Z'=>true						
					    ),	
					    'Y'=>true					    
				),
				2=>true,
				3=>true
		);
		
		$ctrl = array(
			'validate'=>true,
			'mode'=>'trie',
			'trap_*'=>true
		);
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
		// Verify db state
		// ####################################################################
		
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
		
		// NOTE: the datastore will automatically clip empty branches
		
		$check = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										2=>false
								    )						    
							)						
					    )				    
				)	    
		);
		
                $this->assertEquals($check, $result);
		
		
		// Check cache state
		// ####################################################################			
		
		// The all_cached flag, L2, L3, and L4 LUT's won't exist in the cache yet 
		// because we haven't done a database read.
		
		$check_cache = array(
					1=>array()
		);
		
                $this->assertEquals($check_cache, $this->cls->cache);		

		
		// Verify persistent cache state by reading-back all items
		// ####################################################################		
		
		
		$request = array(
				    1=>array(),
				    2=>array(),
				    3=>array()		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid); // Should report invalid because 
						    // the '2' and '3' L5's don't exist
		
		$this->assertEquals($check, $result);
		
	}
	
	
       /**
	* Test fixture for dropMulti() method, warm cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropMulti_WARM() {
	    

		self::loadData();
				
		
		// WARM CACHE - Items in cache from previous ADD operation
		// ===================================================================				
	    
		// Drop objects
		// ####################################################################
		
		$data = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>true
								    ),
								    'T'=>true							    
							),
							'Z'=>true						
					    ),	
					    'Y'=>true					    
				),
				2=>true,
				3=>true
		);
		
		$ctrl = array(
			'validate'=>true,
			'mode'=>'trie',
			'trap_*'=>true
		);
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
		// Verify db state
		// ####################################################################
		
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
		
		// NOTE: the datastore will automatically clip empty branches
		
		$check = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										2=>false
								    )						    
							)						
					    )				    
				)	    
		);
		
                $this->assertEquals($check, $result);
		
		// Check cache state
		// ####################################################################			
		
		// The all_cached flag, L2, L3, and L4 LUT's won't exist in the cache yet 
		// because we haven't done a database read.
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_1 = array(	    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												2=>false
										    )							    
									)					
							    )				    
					    )
		);
		
		$this->assertEquals($check_cache_1, $this->cls->cache[1]);
		
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(
					1=>$check_cache_1
		);
		
                $this->assertEquals($check_cache, $this->cls->cache);		

		
		// Verify persistent cache state by reading-back all items
		// ####################################################################		
		
		
		$request = array(
				    1=>array(),
				    2=>array(),
				    3=>array()		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid); // Should report invalid because 
						    // the '2' and '3' L5's don't exist
		
		$this->assertEquals($check, $result);
		
	}
	
	
       /**
	* Test fixture for dropMulti() method, hot cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropMulti_HOT() {
	    

		self::loadData();
				
		
		// Load the cache
		// ####################################################################
		
		$request = array(
				    1=>array(),
				    2=>array(),
				    3=>array()		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
				
		
		// HOT CACHE - All items in cache have authority from previous GET operation
		// ===================================================================				
	    
		// Drop objects
		// ####################################################################
		
		$data = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>true
								    ),
								    'T'=>true							    
							),
							'Z'=>true						
					    ),	
					    'Y'=>true					    
				),
				2=>true,
				3=>true
		);
		
		$ctrl = array(
			'validate'=>true,
			'mode'=>'trie',
			'trap_*'=>true
		);
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
		// Verify db state
		// ####################################################################
		
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
		
		// NOTE: the datastore will automatically clip empty branches
		
		$check = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										2=>false
								    )						    
							)						
					    )				    
				)	    
		);
		
                $this->assertEquals($check, $result);
		
		
		// Check cache state
		// ####################################################################			
		
		// Since we're working with a hot cache, the all_cached flag will be set for all
		// nodes that already exist in the database. The L2, L3, and L4 LUT's for these
		// nodes will be missing, because the all_cached flag takes priority.
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_1 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												2=>false
										    )							    
									)					
							    )				    
					    )
		);
		
		$this->assertEquals($check_cache_1, $this->cls->cache[1]);
		
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(
					1=>$check_cache_1
		);
		
                $this->assertEquals($check_cache, $this->cls->cache);		

		
		// Verify persistent cache state by reading-back all items
		// ####################################################################		
		
		
		$request = array(
				    1=>array(),
				    2=>array(),
				    3=>array()		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid); // Should report invalid because 
						    // the '2' and '3' L5's don't exist
		
		$this->assertEquals($check, $result);
		
	}
	
	
	
	function tearDown() {
	   
		$this->cls = new FOX_dataStore_paged_L5_tester_dropMethods();
		$unistall_ok = $this->cls->uninstall();
		
		$this->assertEquals(true, $unistall_ok);
		
		parent::tearDown();
	}

    
}

?>