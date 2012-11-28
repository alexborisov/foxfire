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
	* Test fixture for dropMulti() method, trie mode, cold cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropMulti_trie_COLD() {
	    

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
					    'Y'=>array(	// Collapse this node
							'K'=>true,
							'Z'=>true
					    )				    
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
			$rows_changed = $this->cls->dropMulti($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Should return (int)1 to indicate 18 rows were dropped
		$this->assertEquals(18, $rows_changed);
		
		
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
		
		$check_cache = array();
		
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
	* Test fixture for dropMulti() method, trie mode, warm cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropMulti_trie_WARM() {
	    

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
					    'Y'=>array(	// Collapse this node
							'K'=>true,
							'Z'=>true
					    )
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
			$rows_changed = $this->cls->dropMulti($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Should return (int)1 to indicate 18 rows were dropped
		$this->assertEquals(18, $rows_changed);
		
		
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
		
		// The all_cached flag won't exist in the cache yet because we haven't 
		// done a database read. The L2, L3, and L4 LUT's will be null.
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_1 = array(	    'L4'=>null,
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
	
	
       /**
	* Test fixture for dropMulti() method, trie mode, hot cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropMulti_trie_HOT() {
 	    

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
					    'Y'=>array(	// Collapse this node
							'K'=>true,
							'Z'=>true
					    )				    
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
			$rows_changed = $this->cls->dropMulti($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Should return (int)1 to indicate 18 rows were dropped
		$this->assertEquals(18, $rows_changed);
		
		
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
	
	
       /**
	* Test fixture for dropMulti() method, trie mode, data integrity
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropMulti_trie_dataIntegrity() {
	    
	    
		$ctrl = array(
			'validate'=>true,
			'mode'=>'trie',
			'trap_*'=>true
		);
		
	    
		// Valid data trie
		// ####################################################################
	    
		$data = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>true
								    ),
								    'T'=>true							    
							),
							'Z'=>true						
					    ),	
					    'Y'=>array(	// Collapse this node
							'K'=>true,
							'Z'=>true
					    )				    
				)
		);
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		
		// Invalid L1 node data type
		// ####################################################################
	    
		$data = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										"F"=>true
								    ),
								    'T'=>true							    
							),
							'Z'=>true						
					    ),	
					    'Y'=>array(	// Collapse this node
							'K'=>true,
							'Z'=>true
					    )				    
				)
		);
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
			
			// Execution will halt on the previous line if dropMulti() throws an exception
			$this->fail("Method dropMulti failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}		
		
		
		// Invalid L2 node data type
		// ####################################################################
	    
		$data = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>true
								    ),
								    1=>true							    
							),
							'Z'=>true						
					    ),	
					    'Y'=>array(	// Collapse this node
							'K'=>true,
							'Z'=>true
					    )				    
				)
		);
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
			
			// Execution will halt on the previous line if dropMulti() throws an exception
			$this->fail("Method dropMulti failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}		
		
		// Invalid L2 node value
		// ####################################################################
	    
		$data = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>true
								    ),
								    'T'=>"foo"							    
							),
							'Z'=>true						
					    ),	
					    'Y'=>array(	// Collapse this node
							'K'=>true,
							'Z'=>true
					    )				    
				)
		);
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
			
			// Execution will halt on the previous line if dropMulti() throws an exception
			$this->fail("Method dropMulti failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}
		
		
		// Invalid L3 node data type
		// ####################################################################
	    
		$data = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>true
								    ),
								    'T'=>true							    
							),
							'1'=>true						
					    ),	
					    'Y'=>array(	// Collapse this node
							'K'=>true,
							'Z'=>true
					    )				    
				)
		);
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
			
			// Execution will halt on the previous line if dropMulti() throws an exception
			$this->fail("Method dropMulti failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}		
		
		// Invalid L3 node value
		// ####################################################################
	    
		$data = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>true
								    ),
								    'T'=>true							    
							),
							'Z'=>(float)21.5						
					    ),	
					    'Y'=>array(	// Collapse this node
							'K'=>true,
							'Z'=>true
					    )				    
				)
		);
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
			
			// Execution will halt on the previous line if dropMulti() throws an exception
			$this->fail("Method dropMulti failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}		

				
		// Invalid L4 node data type
		// ####################################################################
	    
		$data = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>true
								    ),
								    'T'=>true							    
							),
							'Z'=>true						
					    ),	
					    '7'=>array(	// Collapse this node
							'K'=>true,
							'Z'=>true
					    )				    
				)
		);
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
			
			// Execution will halt on the previous line if dropMulti() throws an exception
			$this->fail("Method dropMulti failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}		
		
		// Invalid L4 node value
		// ####################################################################
	    
		$data = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>true
								    ),
								    'T'=>true							    
							),
							'Z'=>true						
					    ),	
					    'Y'=>array(	// Collapse this node
							'K'=>true,
							'Z'=>true
					    ),
					    'F'=>"bar"
				)
		);
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
			
			// Execution will halt on the previous line if dropMulti() throws an exception
			$this->fail("Method dropMulti failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}			    
				
		// Invalid L5 node data type
		// ####################################################################
	    
		$data = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>true
								    ),
								    'T'=>true							    
							),
							'Z'=>true						
					    ),	
					    'Z'=>array(	// Collapse this node
							'K'=>true,
							'Z'=>true
					    )				    
				),
				'F'=>true
		);
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
			
			// Execution will halt on the previous line if dropMulti() throws an exception
			$this->fail("Method dropMulti failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}		
		
		// Empty data array
		// ####################################################################
	    
		$data = array();
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
			
			// Execution will halt on the previous line if dropMulti() throws an exception
			$this->fail("Method dropMulti failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}		
		
		
	}
	
	
       /**
	* Test fixture for dropMulti() method, matrix mode, cold cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropMulti_matrix_COLD() {
	    

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
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T"		),
				array( "L5"=>1, "L4"=>"X", "L3"=>"Z"			),	
				array( "L5"=>1, "L4"=>"Y"				),		    
		    		array( "L5"=>2						),
				array( "L5"=>3						)		    		    
		);		
		
		$ctrl = array(
			'validate'=>true,
			'mode'=>'matrix',
			'trap_*'=>true
		);
		
		try {			
			$rows_changed = $this->cls->dropMulti($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	//array('depth'=>10, 'data'=>true)
		}
		
		// Should return (int)1 to indicate 18 rows were dropped
		$this->assertEquals(18, $rows_changed);
		
		
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
		
		$check_cache = array();
		
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
	* Test fixture for dropMulti() method, matrix mode, warm cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropMulti_matrix_WARM() {


		self::loadData();
				
		
		// WARM CACHE - Items in cache from previous ADD operation
		// ===================================================================				
	    
		// Drop objects
		// ####################################################################
		
		$data = array(
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T"		),
				array( "L5"=>1, "L4"=>"X", "L3"=>"Z"			),	
				array( "L5"=>1, "L4"=>"Y"				),		    
		    		array( "L5"=>2						),
				array( "L5"=>3						)		    		    
		);		
		
		$ctrl = array(
			'validate'=>true,
			'mode'=>'matrix',
			'trap_*'=>true
		);
		
		try {			
			$rows_changed = $this->cls->dropMulti($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Should return (int)1 to indicate 18 rows were dropped
		$this->assertEquals(18, $rows_changed);
		
		
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
		
		// The all_cached flagwon't exist in the cache yet because we haven't done 
		// a database read. The L2, L3, and L4 LUT's will be null.
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_1 = array(	    
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
	
	
       /**
	* Test fixture for dropMulti() method, matrix mode, hot cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropMulti_matrix_HOT() {
  

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
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T"		),
				array( "L5"=>1, "L4"=>"X", "L3"=>"Z"			),	
				array( "L5"=>1, "L4"=>"Y"				),		    
		    		array( "L5"=>2						),
				array( "L5"=>3						)		    		    
		);		
		
		$ctrl = array(
			'validate'=>true,
			'mode'=>'matrix',
			'trap_*'=>true
		);
		
		try {			
			$rows_changed = $this->cls->dropMulti($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Should return (int)1 to indicate 18 rows were dropped
		$this->assertEquals(18, $rows_changed);
		
		
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
	
	
	
	
       /**
	* Test fixture for dropMulti() method, matrix mode, data integrity
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropMulti_matrix_dataIntegrity() {
	    
	    
		$ctrl = array(
			'validate'=>true,
			'mode'=>'matrix',
			'trap_*'=>true
		);
		
	    
		// Valid data matrix
		// ####################################################################
	    
		$data = array(

				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T"		),
				array( "L5"=>1, "L4"=>"X", "L3"=>"Z"			),	
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K"			),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"Z"			)
		);		
						
		try {			
			$this->cls->dropMulti($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		
		// Invalid L1 node data type
		// ####################################################################
	    
		$data = array(

				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>'1'),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T"		),
				array( "L5"=>1, "L4"=>"X", "L3"=>"Z"			),	
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K"			),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"Z"			)
		);	
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
			
			// Execution will halt on the previous line if dropMulti() throws an exception
			$this->fail("Method dropMulti failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}		
	
		
		// Invalid L2 node data type
		// ####################################################################
	    
		$data = array(

				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>1		),
				array( "L5"=>1, "L4"=>"X", "L3"=>"Z"			),	
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K"			),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"Z"			)
		);	
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
			
			// Execution will halt on the previous line if dropMulti() throws an exception
			$this->fail("Method dropMulti failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}		
		
		
		// Invalid L3 node data type
		// ####################################################################
	    
		$data = array(

				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T"		),
				array( "L5"=>1, "L4"=>"X", "L3"=>1			),	
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K"			),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"Z"			)
		);	
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
			
			// Execution will halt on the previous line if dropMulti() throws an exception
			$this->fail("Method dropMulti failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}		
						
		// Invalid L4 node data type
		// ####################################################################
	    
		$data = array(

				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T"		),
				array( "L5"=>1, "L4"=>"X", "L3"=>"Z"			),	
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K"			),
		    		array( "L5"=>1, "L4"=>"1"			)
		);	
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
			
			// Execution will halt on the previous line if dropMulti() throws an exception
			$this->fail("Method dropMulti failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}				
				
		// Invalid L5 node data type
		// ####################################################################
	    
		$data = array(

				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T"		),
				array( "L5"=>1, "L4"=>"X", "L3"=>"Z"			),	
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K"			),
		    		array( "L5"=>'1'					)
		);	
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
			
			// Execution will halt on the previous line if dropMulti() throws an exception
			$this->fail("Method dropMulti failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}				
		
		// Empty data array
		// ####################################################################
	    
		$data = array();
		
		try {			
			$this->cls->dropMulti($data, $ctrl);
			
			// Execution will halt on the previous line if dropMulti() throws an exception
			$this->fail("Method dropMulti failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}		
		
		
	}
	
	
       /**
	* Test fixture for dropL1() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropL1() {
   
	    
		self::loadData();
		
		$ctrl = array(
			"validate"=>true
		);	    

		// Drop a L1 in single mode
		// ==============================================
		
		try {
			$rows_changed = $this->cls->dropL1(1, "X", "K", "K", 1, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)1 to indicate 1 db row was deleted
		$this->assertEquals(1, $rows_changed); 
		
		// Drop a L1 in single mode
		// ==============================================	    

		try {
			$rows_changed = $this->cls->dropL1(2, "X", "K", "K", 1, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)1 to indicate 1 db row was deleted
		$this->assertEquals(1, $rows_changed); 		
		
		
		// Drop multiple L1's in array mode
		// ==============================================	    

		try {
			$rows_changed = $this->cls->dropL1(3, "X", "K", "K", array(1,2), $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)2 to indicate 2 db rows were deleted
		$this->assertEquals(2, $rows_changed); 	
		
		
		// Drop multiple L1's in array mode
		// ==============================================	    

		try {
			$rows_changed = $this->cls->dropL1(3, "Y", "K", "K", array(1,2), $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)2 to indicate 2 db rows were deleted
		$this->assertEquals(2, $rows_changed); 	
		
		
		// Verify datastore is in correct state
		// ==============================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
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
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
				),
				3=>array(   'X'=>array(	'K'=>array( 'T'=>array(	1=>true )							    
							),
							'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
					    ),	
					    'Y'=>array(	'K'=>array( 'T'=>array(	3=>(float)1.7 )							    
							),
							'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )					    
				)		    
		);
		
		$request = array(
				    1=>array(),	    // Request all L5 tries in datastore
				    2=>array(),
				    3=>array()		    
		);
		
		$ctrl = array(
			'validate'=>true,
			'q_mode'=>'trie',
			'r_mode'=>'trie',		    
			'trap_*'=>true
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		$this->assertEquals($check, $result);
		
		
	}
	
	
       /**
	* Test fixture for dropL1_multi() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropL1_multi() {

	    
		self::loadData();
		
		
		$ctrl = array(
			"validate"=>true
		);	    

		
		// Drop multiple L1's in single mode
		// ==============================================
		
		try {
		    
			$drop_nodes = array(
					array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1),
					array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>2),
					array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T", "L1"=>1),
					array( "L5"=>1, "L4"=>"X", "L3"=>"Z", "L2"=>"Z", "L1"=>3),	
					array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>1)	    
			);	
			
			$rows_changed = $this->cls->dropL1_multi($drop_nodes, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}
		
		// Should return (int)5 to indicate 5 rows were dropped from the db
		$this->assertEquals(5, $rows_changed); 
		
		
		// Drop multiple L1's in multi mode
		// ==============================================
		
		try {
		    
			$drop_nodes = array(
					array( "L5"=>2, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>array(1,2)),
					array( "L5"=>3, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>array(1,2))	    
			);	
			
			$rows_changed = $this->cls->dropL1_multi($drop_nodes, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}
		
		// Should return (int)4 to indicate 4 rows were dropped from the db
		$this->assertEquals(4, $rows_changed); 		
		
		// Verify datastore is in correct state
		// ==============================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(
				1=>array(   'Y'=>array(	'K'=>array( 'K'=>array(	
										2=>(int)-1
								    ),
								    'T'=>array(	3=>(float)1.7 )							    
							),
							'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )					    
				),			
				2=>array(   'X'=>array(	'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
				),
				3=>array(   'X'=>array(	'K'=>array( 'T'=>array(	1=>true )							    
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
		
		$request = array(
				    1=>array(),	    // Request all L5 tries in datastore
				    2=>array(),
				    3=>array()		    
		);
		
		$ctrl = array(
			'validate'=>true,
			'q_mode'=>'trie',
			'r_mode'=>'trie',		    
			'trap_*'=>true
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		$this->assertEquals($check, $result);
		
	}
	
	
	
       /**
	* Test fixture for dropL1_multi() method, data integrity
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropL1_multi_dataIntegrity() {
	    
	    
		$ctrl = array(
			'validate'=>true
		);
		
	    
		// Valid data matrix
		// ####################################################################
	    
		$data = array(

				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>2)		    
		);		
						
		try {			
			$this->cls->dropL1_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

			
		// L1 - Single invalid L1 key
		// ####################################################################
		
		try {			
			$data = array(

					array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1),
					array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>'2')		    
			);	
			
			$this->cls->dropL1_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		
		// L1 - Single invalid L1 key in 'multi' mode
		// ####################################################################
		
		try {			
			$data = array(

					array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>array(1,'2') ),
					array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>3	    )		    
			);	
			
			$this->cls->dropL1_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L1 - Multiple invalid L1 keys in 'multi' mode
		// ####################################################################
		
		try {			
			$data = array(

					array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>array("F",'2') ),
					array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>3	    )		    
			);	
			
			$this->cls->dropL1_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}		
		
		// L1 - Valid L1, invalid L2
		// ####################################################################

		try {			
			$data = array(

					array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>1,	 "L1"=>1	    ),
					array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>3	    )		    
			);	
			
			$this->cls->dropL1_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L1 - Valid L1, invalid L3
		// ####################################################################		
		
		try {			
			$data = array(

					array( "L5"=>1, "L4"=>"X", "L3"=>"1", "L2"=>"K", "L1"=>1	    ),
					array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>3	    )		    
			);	
			
			$this->cls->dropL1_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L1 - Valid L1, invalid L4
		// ####################################################################
		
		try {			
			$data = array(

					array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1	    ),
					array( "L5"=>1, "L4"=>"", "L3"=>"K", "L2"=>"K", "L1"=>3	    )		    
			);	
			
			$this->cls->dropL1_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L1 - Valid L1, invalid L5
		// ####################################################################

		try {			
			$data = array(

					array( "L5"=>null, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1	    ),
					array( "L5"=>1, "L4"=>"Z", "L3"=>"K", "L2"=>"K", "L1"=>3	    )		    
			);	
			
			$this->cls->dropL1_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}		
		
		// Empty data array
		// ####################################################################	    		

		try {			
			$data = array();	
			
			$this->cls->dropL1_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}				

		// L1 - Valid L1, extraneous key
		// ####################################################################

		try {			
			$data = array(

					array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1, "L9"=>false),
					array( "L5"=>1, "L4"=>"Z", "L3"=>"K", "L2"=>"K", "L1"=>3	    )		    
			);	
			
			$this->cls->dropL1_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}		
	
		
	}	
	
	
       /**
	* Test fixture for dropL2() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropL2() {
   
	    
		self::loadData();
		
		$ctrl = array(
			"validate"=>true
		);	    

		// Drop a L2 in single mode
		// ==============================================
		
		try {
			$rows_changed = $this->cls->dropL2(1, "X", "K", "K", $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)2 to indicate 2 db rows were deleted
		$this->assertEquals(2, $rows_changed); 
		
		// Drop a L2 in single mode
		// ==============================================	    

		try {
			$rows_changed = $this->cls->dropL2(1, "X", "K", "T", $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)1 to indicate 1 db row was deleted
		$this->assertEquals(1, $rows_changed); 		
		
		
		// Drop multiple L2's in array mode
		// ==============================================	    

		try {
			$rows_changed = $this->cls->dropL2(3, "X", "K", array("K", "T"), $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)3 to indicate 3 db rows were deleted
		$this->assertEquals(3, $rows_changed); 	
		
		
		// Drop multiple L2's in array mode
		// ==============================================	    

		try {
			$rows_changed = $this->cls->dropL2(3, "Y", "K", array("K", "T"), $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)3 to indicate 3 db rows were deleted
		$this->assertEquals(3, $rows_changed); 	
		

		// Verify datastore is in correct state
		// ==============================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(
				1=>array(   'X'=>array(	'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
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
				3=>array(   'X'=>array(	'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
					    ),	
					    'Y'=>array(	'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )					    
				)		    
		);		
		
		$request = array(
				    1=>array(),	    // Request all L5 tries in datastore
				    2=>array(),
				    3=>array()		    
		);
		
		$ctrl = array(
			'validate'=>true,
			'q_mode'=>'trie',
			'r_mode'=>'trie',		    
			'trap_*'=>true
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		$this->assertEquals($check, $result);
		
		
	}	
	
	
       /**
	* Test fixture for dropL2_multi() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropL2_multi() {

	    
		self::loadData();
		
		
		$ctrl = array(
			"validate"=>true
		);	    

		
		// Drop multiple L2's in single mode
		// ==============================================
		
		try {
		    
			$drop_nodes = array(
					array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K"),
					array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T"),
					array( "L5"=>1, "L4"=>"X", "L3"=>"Z", "L2"=>"Z"),	
					array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K")	    
			);	
			
			$rows_changed = $this->cls->dropL2_multi($drop_nodes, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}
		
		// Should return (int)6 to indicate 6 rows were dropped from the db
		$this->assertEquals(6, $rows_changed); 
		
		
		// Drop multiple L2's in multi mode
		// ==============================================
		
		try {
		    
			$drop_nodes = array(
					array( "L5"=>2, "L4"=>"X", "L3"=>"K", "L2"=>array("K")),
					array( "L5"=>3, "L4"=>"X", "L3"=>"K", "L2"=>array("K","T"))	    
			);	
			
			$rows_changed = $this->cls->dropL2_multi($drop_nodes, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}
		
		// Should return (int)5 to indicate 5 rows were dropped from the db
		$this->assertEquals(5, $rows_changed); 		
		
		
		// Verify datastore is in correct state
		// ==============================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(
				1=>array(   'Y'=>array(	'K'=>array( 'T'=>array(	3=>(float)1.7 )							    
							),
							'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )					    
				),			
				2=>array(   'X'=>array(	'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
				),
				3=>array(   'X'=>array(	'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
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
		
		$request = array(
				    1=>array(),	    // Request all L5 tries in datastore
				    2=>array(),
				    3=>array()		    
		);
		
		$ctrl = array(
			'validate'=>true,
			'q_mode'=>'trie',
			'r_mode'=>'trie',		    
			'trap_*'=>true
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		$this->assertEquals($check, $result);
		
	}
	
	
       /**
	* Test fixture for dropL3() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropL3() {
   
	    
		self::loadData();
		
		$ctrl = array(
			"validate"=>true
		);	    

		// Drop a L3 in single mode
		// ==============================================
		
		try {
			$rows_changed = $this->cls->dropL3(1, "X", "K", $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)3 to indicate 3 db rows were deleted
		$this->assertEquals(3, $rows_changed); 
		
		// Drop a L3 in single mode
		// ==============================================	    

		try {
			$rows_changed = $this->cls->dropL3(1, "X", "Z", $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)1 to indicate 1 db row was deleted
		$this->assertEquals(1, $rows_changed); 		
		
		
		// Drop multiple L3's in array mode
		// ==============================================	    

		try {
			$rows_changed = $this->cls->dropL3(2, "X", array("K", "Z"), $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)3 to indicate 3 db rows were deleted
		$this->assertEquals(3, $rows_changed); 	
		
		
		// Drop multiple L3's in array mode
		// ==============================================	    

		try {
			$rows_changed = $this->cls->dropL3(3, "Y", array("K", "Z"), $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)4 to indicate 4 db rows were deleted
		$this->assertEquals(4, $rows_changed); 	
		

		// Verify datastore is in correct state
		// ==============================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(
				1=>array(   'Y'=>array(	'K'=>array( 'K'=>array(	
										1=>(int)1,
										2=>(int)-1
								    ),
								    'T'=>array(	3=>(float)1.7 )							    
							),
							'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )					    
				),			
				3=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>null,
										2=>false
								    ),
								    'T'=>array(	1=>true )							    
							),
							'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
					    )				    
				)		    
		);		
		
		$request = array(
				    1=>array(),	    // Request all L5 tries in datastore
				    2=>array(),
				    3=>array()		    
		);
		
		$ctrl = array(
			'validate'=>true,
			'q_mode'=>'trie',
			'r_mode'=>'trie',		    
			'trap_*'=>true
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);	// L5 branch '2' shouldn't exist	
		$this->assertEquals($check, $result);
		
		
	}
	
	
       /**
	* Test fixture for dropL3_multi() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropL3_multi() {

	    
		self::loadData();
		
		
		$ctrl = array(
			"validate"=>true
		);	    

		
		// Drop multiple L3's in single mode
		// ==============================================
		
		try {
		    
			$drop_nodes = array(
					array( "L5"=>1, "L4"=>"X", "L3"=>"K"),
					array( "L5"=>1, "L4"=>"X", "L3"=>"Z"),	
					array( "L5"=>1, "L4"=>"Y", "L3"=>"K")	    
			);	
			
			$rows_changed = $this->cls->dropL3_multi($drop_nodes, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}
		
		// Should return (int)7 to indicate 7 rows were dropped from the db
		$this->assertEquals(7, $rows_changed); 
		

		// Drop multiple L3's in multi mode
		// ==============================================
		
		try {
		    
			$drop_nodes = array(
					array( "L5"=>2, "L4"=>"X", "L3"=>array("K","Z")),
					array( "L5"=>3, "L4"=>"Y", "L3"=>array("K","Z"))	    
			);	
			
			$rows_changed = $this->cls->dropL3_multi($drop_nodes, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}
		
		// Should return (int)7 to indicate 7 rows were dropped from the db
		$this->assertEquals(7, $rows_changed); 		

		
		// Verify datastore is in correct state
		// ==============================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(
				1=>array(   'Y'=>array(	'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )					    
				),			
				3=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>null,
										2=>false
								    ),
								    'T'=>array(	1=>true )							    
							),
							'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
					    )					    
				)		    
		);	
		
		$request = array(
				    1=>array(),	    // Request all L5 tries in datastore
				    2=>array(),
				    3=>array()		    
		);
		
		$ctrl = array(
			'validate'=>true,
			'q_mode'=>'trie',
			'r_mode'=>'trie',		    
			'trap_*'=>true
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);	// L5 node '2' shouldn't exist	
		$this->assertEquals($check, $result);
		
	}
	
	
       /**
	* Test fixture for dropL4() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropL4() {
   
	    
		self::loadData();
		
		$ctrl = array(
			"validate"=>true
		);	    

		// Drop a L4 in single mode
		// ==============================================
		
		try {
			$rows_changed = $this->cls->dropL4(1, "X", $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)4 to indicate 4 db rows were deleted
		$this->assertEquals(4, $rows_changed); 
		
		// Drop a L4 in single mode
		// ==============================================	    

		try {
			$rows_changed = $this->cls->dropL4(1, "Y", $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)4 to indicate 4 db rows were deleted
		$this->assertEquals(4, $rows_changed); 		

		
		// Drop multiple L4's in array mode
		// ==============================================	    

		try {
			$rows_changed = $this->cls->dropL4(2, array("X"), $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)3 to indicate 3 db rows were deleted
		$this->assertEquals(3, $rows_changed); 	
		
		
		// Drop multiple L4's in array mode
		// ==============================================	    

		try {
			$rows_changed = $this->cls->dropL4(3, array("X", "Y"), $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)8 to indicate 8 db rows were deleted
		$this->assertEquals(8, $rows_changed); 	
		

		// Verify datastore is in correct state
		// ==============================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		// The delete operations should have cleared the entire datastore
		$check = array();		
		
		$request = array(
				    1=>array(),	    // Request all L5 tries in datastore
				    2=>array(),
				    3=>array()		    
		);
		
		$ctrl = array(
			'validate'=>true,
			'q_mode'=>'trie',
			'r_mode'=>'trie',		    
			'trap_*'=>true
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);	// None of the requested keys should exist	
		$this->assertEquals($check, $result);
		
		
	}
	
	
       /**
	* Test fixture for dropL4_multi() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropL4_multi() {

	    
		self::loadData();
		
		
		$ctrl = array(
			"validate"=>true
		);	    

		
		// Drop multiple L4's in single mode
		// ==============================================
		
		try {
		    
			$drop_nodes = array(
					array( "L5"=>1, "L4"=>"X"),
					array( "L5"=>1, "L4"=>"Y")	    
			);	
			
			$rows_changed = $this->cls->dropL4_multi($drop_nodes, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}
		
		// Should return (int)8 to indicate 8 rows were dropped from the db
		$this->assertEquals(8, $rows_changed); 
		

		// Drop multiple L4's in multi mode
		// ==============================================
		
		try {
		    
			$drop_nodes = array(
					array( "L5"=>3, "L4"=>array("X","Y"))	    
			);	
			
			$rows_changed = $this->cls->dropL4_multi($drop_nodes, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}
		
		// Should return (int)8 to indicate 8 rows were dropped from the db
		$this->assertEquals(8, $rows_changed); 		

		
		// Verify datastore is in correct state
		// ==============================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(			
				2=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
				)		    
		);	
		
		$request = array(
				    1=>array(),	    // Request all L5 tries in datastore
				    2=>array(),
				    3=>array()		    
		);
		
		$ctrl = array(
			'validate'=>true,
			'q_mode'=>'trie',
			'r_mode'=>'trie',		    
			'trap_*'=>true
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);	// L5 node '1' and '3' shouldn't exist	
		$this->assertEquals($check, $result);
		
	}
	
	
       /**
	* Test fixture for dropL5() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropL5() {
   
	    
		self::loadData();
		
		$ctrl = array(
			"validate"=>true
		);	    

		// Drop a L5 in single mode
		// ==============================================
		
		try {
			$rows_changed = $this->cls->dropL5(2, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));			
		}			

		// Should return (int)3 to indicate 3 db rows were deleted
		$this->assertEquals(3, $rows_changed); 
		

		// Drop multiple L5's in array mode
		// ==============================================	    

		try {
			$rows_changed = $this->cls->dropL5(array(3,1), $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)16 to indicate 16 db rows were deleted
		$this->assertEquals(16, $rows_changed); 	
				

		// Verify datastore is in correct state
		// ==============================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		// The delete operations should have cleared the entire datastore
		$check = array();		
		
		$request = array(
				    1=>array(),	    // Request all L5 tries in datastore
				    2=>array(),
				    3=>array()		    
		);
		
		$ctrl = array(
			'validate'=>true,
			'q_mode'=>'trie',
			'r_mode'=>'trie',		    
			'trap_*'=>true
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);	// None of the requested keys should exist	
		$this->assertEquals($check, $result);
		
		
	}
	
	
       /**
	* Test fixture for dropL5_multi() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropL5_multi() {

	    
		self::loadData();
		
		
		$drop_ctrl = array(
			"validate"=>true
		);	    

		
		// Drop multiple L5's in single mode
		// ==============================================
		
		try {
		    
			$drop_nodes = array(
					array( "L5"=>1),
					array( "L5"=>2),
			    		array( "L5"=>3)	
			);	
			
			$rows_changed = $this->cls->dropL5_multi($drop_nodes, $drop_ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}
		
		// Should return (int)19 to indicate 19 rows were dropped from the db
		$this->assertEquals(19, $rows_changed); 				

		
		// Verify datastore is in correct state
		// ==============================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array();   // The deletes should have cleared the entire datastore	
		
		$request = array(
				    1=>array(),	    // Request all L5 tries in datastore
				    2=>array(),
				    3=>array()		    
		);
		
		$get_ctrl = array(
			'validate'=>true,
			'q_mode'=>'trie',
			'r_mode'=>'trie',		    
			'trap_*'=>true
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $get_ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);	// None of the requested nodes should exist	
		$this->assertEquals($check, $result);
		
		
		// Reload the datastore
		// ==============================================
		
		self::loadData();
		
		
		// Drop multiple L5's in array mode
		// ==============================================
		
		try {
		    
			$drop_nodes = array(
					array( "L5"=>array(1,3) )
			);	
			
			$rows_changed = $this->cls->dropL5_multi($drop_nodes, $drop_ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}
		
		// Should return (int)16 to indicate 16 rows were dropped from the db
		$this->assertEquals(16, $rows_changed); 				

		
		// Verify datastore is in correct state
		// ==============================================		
		
		$check = array(
				2=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
				)		    
		);   			
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $get_ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);	// The '1' and '3' L5 nodes shouldn't exist	
		$this->assertEquals($check, $result);		
		
	}
	
	
       /**
	* Tests keyType validators for every possible combination of L5->L1 walks for
        * all dropL[x]() methods
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_dataIntegrity_dropLx_keyType() {
	    
		
		self::loadData();
					
		
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'trie'		    
		);
		
		// L1 - Single VALID L1
		// ===========================================
		
		$valid = false;
		
		try {			
			$rows_changed = $this->cls->dropL1(1, 'Y', 'K', 'K', 1, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(1, $rows_changed);
		
		unset($rows_changed);
		
		
		// L1 - Single invalid L1 key
		// ===========================================
		
		try {			
			$result = $this->cls->dropL1(1, 'Y', 'K', 'K', "T", $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L1 - Single invalid L1 key in 'multi' mode
		// =================================================
		
		try {			
			$result = $this->cls->dropL1(1, 'Y', 'K', 'K', array(1, "T"), $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L1 - Multiple invalid L1 keys in 'multi' mode
		// ====================================================
		
		try {			
			$result = $this->cls->dropL1(1, 'Y', 'K', 'K', array("Q", "T"), $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L1 - Valid L1, invalid L2
		// ===========================================

		try {			
			$result = $this->cls->dropL1(1, 'Y', 'K', 2, 1, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {

		}			
				
		// L1 - Valid L1, invalid L3
		// ===========================================

		
		try {			
			$result = $this->cls->dropL1(1, 'Y', 7, 'K', 1, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {

		}		
		
		
		// L1 - Valid L1, invalid L4
		// ===========================================

		try {			
			$result = $this->cls->dropL1(1, '4', 'K', 'K', 1, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {
	
		}
		
		// L1 - Valid L1, invalid L5
		// ===========================================

		try {			
			$result = $this->cls->dropL1('2', 'Y', 'K', 'K', 1, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {

		}		
		
		// ==========================================================================================	
		
		
		// L2 - Single VALID L2
		// ===========================================
		
		$valid = false;
		
		try {			
			$rows_changed = $this->cls->dropL2(1, 'Y', 'K', 'K', $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(1, $rows_changed);	// One key was already deleted in L1 tests
		
		unset($rows_changed);		
		
		
		// L2 - Single invalid L2 key
		// ===========================================
		
		try {			
			$result = $this->cls->dropL2(1, 'Y', 'K', 1, $ctrl);
			
			// Execution will halt on the previous line if dropL2() throws an exception
			$this->fail("Method dropL2() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L2 - Single invalid L2 key in 'multi' mode
		// =================================================
		
		try {			
			$result = $this->cls->dropL2(1, 'Y', 'K', array(1,'K'), $ctrl);
			
			// Execution will halt on the previous line if dropL2() throws an exception
			$this->fail("Method dropL2() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L2 - Multiple invalid L2 keys in 'multi' mode
		// ====================================================
		
		try {			
			$result = $this->cls->dropL2(1, 'Y', 'K', array('1','2'), $ctrl);
			
			// Execution will halt on the previous line if dropL2() throws an exception
			$this->fail("Method dropL2() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		
		// L2 - Valid L2, invalid L3
		// ===========================================		
		
		try {			
			$result = $this->cls->dropL2(1, 'Y', '1', 'K', $ctrl);
			
			// Execution will halt on the previous line if dropL2() throws an exception
			$this->fail("Method dropL2() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {
	
		}
				
		// L2 - Valid L2, invalid L4
		// ===========================================		
		
		try {			
			$result = $this->cls->dropL2(1, 6, 'K', 'K', $ctrl);
			
			// Execution will halt on the previous line if dropL2() throws an exception
			$this->fail("Method dropL2() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {
	
		}
		
		// L2 - Valid L2, invalid L5
		// ===========================================		
		
		try {			
			$result = $this->cls->dropL2('2', 'Y', 'K', 'K', $ctrl);
			
			// Execution will halt on the previous line if dropL2() throws an exception
			$this->fail("Method dropL2() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {
	
		}		
		
		// ==========================================================================================	
		
		
		// L3 - Single VALID L3
		// ===========================================
		
		$valid = false;
		
		try {			
			$rows_changed = $this->cls->dropL3(1, 'Y', 'K', $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(1, $rows_changed); // 2 rows already deleted in previous tests
		
		unset($rows_changed);		
		
		
		// L3 - Single invalid L3 key
		// ===========================================
		
		try {			
			$result = $this->cls->dropL3(1, 'Y', '6', $ctrl);
			
			// Execution will halt on the previous line if dropL3() throws an exception
			$this->fail("Method dropL3() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L3 - Single invalid L3 key in 'multi' mode
		// =================================================
		
		try {			
			$result = $this->cls->dropL3(1, 'Y', array(99, 'R'), $ctrl);
			
			// Execution will halt on the previous line if dropL3() throws an exception
			$this->fail("Method dropL3() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L3 - Multiple invalid L3 keys in 'multi' mode
		// ====================================================
		
		try {			
			$result = $this->cls->dropL3(1, 'Y', array(1,2), $ctrl);
			
			// Execution will halt on the previous line if dropL3() throws an exception
			$this->fail("Method dropL3() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		
		// L3 - Valid L3, invalid L4
		// ===========================================		
		
		try {			
			$result = $this->cls->dropL3(1, 2, 'K', $ctrl);
			
			// Execution will halt on the previous line if dropL3() throws an exception
			$this->fail("Method dropL3() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {
	
		}
				
		// L3 - Valid L3, invalid L5
		// ===========================================		
		
		try {			
			$result = $this->cls->dropL3('2', 'Y', 'K', $ctrl);
			
			// Execution will halt on the previous line if dropL3() throws an exception
			$this->fail("Method dropL3() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {
	
		}			
		
		// ==========================================================================================		
		
		
		// L4 - Single VALID L4
		// ===========================================
		
		$valid = false;
		
		try {			
			$rows_changed = $this->cls->dropL4(1, 'Y', $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(1, $rows_changed);	// 3 rows deleted in previous tests
		
		unset($rows_changed);		
		
		
		// L4 - Single invalid L4 key
		// ===========================================
		
		try {			
			$result = $this->cls->dropL4(1, '7', $ctrl);
			
			// Execution will halt on the previous line if dropL4() throws an exception
			$this->fail("Method dropL4() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L4 - Single invalid L4 key in 'multi' mode
		// =================================================
		
		try {			
			$result = $this->cls->dropL4(1, array('Y', 99), $ctrl);
			
			// Execution will halt on the previous line if dropL4() throws an exception
			$this->fail("Method dropL4() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L4 - Multiple invalid L4 keys in 'multi' mode
		// ====================================================
		
		try {			
			$result = $this->cls->dropL4(1, array(2,'4'), $ctrl);
			
			// Execution will halt on the previous line if dropL4() throws an exception
			$this->fail("Method dropL4() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		
		// L4 - Valid L4, invalid L5
		// ===========================================		
		
		try {			
			$result = $this->cls->dropL4('2', 'Y', $ctrl);
			
			// Execution will halt on the previous line if dropL4() throws an exception
			$this->fail("Method dropL4() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {
	
		}				
		
		// ==========================================================================================	
	
		
		// L4 - Single VALID L5
		// ===========================================
		
		$valid = false;
		
		try {			
			$rows_changed = $this->cls->dropL5(1, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(4, $rows_changed);
		
		unset($rows_changed);		
		
		
		// L5 - Single invalid L5 key
		// ===========================================
		
		try {			
			$result = $this->cls->dropL5('2', $ctrl);
			
			// Execution will halt on the previous line if dropL5() throws an exception
			$this->fail("Method dropL5() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L5 - Single invalid L5 key in 'multi' mode
		// =================================================
		
		try {			
			$result = $this->cls->dropL5(array(), $ctrl);
			
			// Execution will halt on the previous line if dropL5() throws an exception
			$this->fail("Method dropL5() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L5 - Multiple invalid L5 keys in 'multi' mode
		// ====================================================
		
		try {			
			$result = $this->cls->dropL5(array('T',null), $ctrl);
			
			// Execution will halt on the previous line if dropL5() throws an exception
			$this->fail("Method dropL5() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
				
		
	}
	
	
	function tearDown() {
	   
		$this->cls = new FOX_dataStore_paged_L5_tester_dropMethods();
		$unistall_ok = $this->cls->uninstall();
		
		$this->assertEquals(true, $unistall_ok);
		
		parent::tearDown();
	}

    
}

?>