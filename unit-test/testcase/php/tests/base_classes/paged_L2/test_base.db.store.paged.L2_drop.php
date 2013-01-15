<?php

/**
 * L2 PAGED ABSTRACT DATASTORE TEST CLASS
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

class FOX_dataStore_paged_L2_tester_dropMethods extends FOX_dataStore_paged_L2_base {
    

	public static $struct = array(

		"table" => "FOX_dataStore_paged_L2_base",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_dataStore_paged_L2_base",
		"cache_strategy" => "paged",
		"cache_engine" => array("memcached", "redis", "apc", "thread"),	    
		"columns" => array(
		    "L2" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,
			// This forces every zone + rule + key_type + key_id combination to be unique
			"index"=>array("name"=>"top_level_index",	"col"=>array("L2", "L1"), "index"=>"PRIMARY"), "this_row"=>true),
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
	
	
}  // ENDOF: class FOX_dataStore_paged_L2_tester_dropMethods 

                                      

/**
 * FOXFIRE UNIT TEST SCRIPT - L2 PAGED ABSTRACT DATASTORE CLASS - DROP METHODS
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

class core_L2_paged_abstract_dropMethods extends RAZ_testCase {

	
    	function setUp() {

	    
		parent::setUp();
				
		
		// Install the db table
		// ===========================================
		
		$this->cls = new FOX_dataStore_paged_L2_tester_dropMethods();
		
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

				array( "L2"=>'X', "L1"=>1, "L0"=>null),
				array( "L2"=>'X', "L1"=>2, "L0"=>false),
				array( "L2"=>'X', "L1"=>5, "L0"=>true),
				array( "L2"=>'X', "L1"=>3, "L0"=>(int)0),	

				array( "L2"=>'Y', "L1"=>1, "L0"=>(int)1),
				array( "L2"=>'Y', "L1"=>2, "L0"=>(int)-1),
		    		array( "L2"=>'Y', "L1"=>3, "L0"=>(float)1.7),
		    		array( "L2"=>'Y', "L1"=>4, "L0"=>(float)-1.6),
		    
		    		array( "L2"=>'Z', "L1"=>1, "L0"=>(string)"foo"),
		    		array( "L2"=>'Z', "L1"=>2, "L0"=>array(null, true, false, 1, 1.0, "foo")),
		    		array( "L2"=>'Z', "L1"=>3, "L0"=>$test_obj)
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
		$this->assertEquals(11, $rows_changed); 								
		
		
		// Check cache state
		// ####################################################################	
		
		// NOTE: the LUT's won't be set at this point, because we haven't done any 
		// database reads that give objects authority
		
		$check = array(
				'X'=>array(   'keys'=>array(
										    1=>null,
										    2=>false,
										    5=>true, 							    
										    3=>(int)0  						
							    )
				),
				'Y'=>array(   'keys'=>array(
										    1=>(int)1,
										    2=>(int)-1,
										    3=>(float)1.7, 							    
										    4=>(float)-1.6 
							    )
					    
				),			
				'Z'=>array(   'keys'=>array(  
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo"),
										    3=>$test_obj  						
							    )	
				)
		);
		
		$this->assertEquals($check, $this->cls->cache);	
		
		
		// Check db state
		// ####################################################################		
		
		$check = array(
				'X'=>array(
								    1=>null,
								    2=>false,
								    5=>true, 							    
								    3=>(int)0  						
				),	
				'Y'=>array(
								    1=>(int)1,
								    2=>(int)-1,
								    3=>(float)1.7, 							    
								    4=>(float)-1.6  						
				),			
				'Z'=>array(
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo"),								    							    
								    3=>$test_obj  						
				)
		);		
		
		$db = new FOX_db();	
		
		$columns = null;
		$args = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L2','L1')
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
				'X'=>array(   1=>true ),
				'Y'=>true,
				'Z'=>true
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
		$this->assertEquals(8, $rows_changed);
		
		
		// Verify db state
		// ####################################################################
		
		$db = new FOX_db();	
		
		$columns = null;
		$args = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L2','L1')
		);
		
		try {			
			$result = $db->runSelectQuery($this->cls->_struct(), $args, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		// NOTE: the datastore will automatically clip empty branches
		
		$check = array(
				'X'=>array(
								    2=>false,
								    3=>(int)0,				    
								    5=>true
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
				    'X'=>array(),
				    'Y'=>array(),
				    'Z'=>array()
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid); // Should report invalid because 
						    // the '2' and '3' L4's don't exist
		
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
				'X'=>array(  1=>true	),	
				'Y'=>true,
				'Z'=>true
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
		$this->assertEquals(8, $rows_changed);
		
		
		// Verify db state
		// ####################################################################
		
		$db = new FOX_db();	
		
		$columns = null;
		$args = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L2','L1')
		);
		
		try {			
			$result = $db->runSelectQuery($this->cls->_struct(), $args, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		// NOTE: the datastore will automatically clip empty branches
		
		$check = array(
				'X'=>array( 
								    2=>false,
								    3=>(int)0,
								    5=>true					  
				)	    
		);
		
                $this->assertEquals($check, $result);
		
		// Check cache state
		// ####################################################################			
		
		// The all_cached flag won't exist in the cache yet because we haven't 
		// done a database read. The L2 and L3 LUT's will be null.
		
		// PASS 1: Check the L4 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_X = array(	    
					    'keys'=>array( 
										    2=>false,
										    5=>true,
										    3=>(int)0

					    )
		);
		
		$this->assertEquals($check_cache_X, $this->cls->cache['X']);
		
		
		// PASS 2: Combine the L4 nodes into a single array and check it
		// again. This finds L4 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(
					'X'=>$check_cache_X
		);
		
                $this->assertEquals($check_cache, $this->cls->cache);		

		
		// Verify persistent cache state by reading-back all items
		// ####################################################################		
		
		
		$request = array(
				    'X'=>array(),
				    'Y'=>array(),
				    'Z'=>array()		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid); // Should report invalid because 
						    // the '2' and '3' L4's don't exist
		
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
				    'X'=>array(),
				    'Y'=>array(),
				    'Z'=>array()		    
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
				'X'=>array( 1=>true ),	
				'Y'=>true,
				'Z'=>true
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
		$this->assertEquals(8, $rows_changed);
		
		
		// Verify db state
		// ####################################################################
		
		$db = new FOX_db();	
		
		$columns = null;
		$args = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L2','L1')
		);
		
		try {			
			$result = $db->runSelectQuery($this->cls->_struct(), $args, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		// NOTE: the datastore will automatically clip empty branches
		
		$check = array(
				'X'=>array(  	
								    2=>false,	
								    3=>(int)0,
								    5=>true
				)	    
		);
		
                $this->assertEquals($check, $result);
		
		
		// Check cache state
		// ####################################################################			
		
		// Since we're working with a hot cache, the all_cached flag will be set for all
		// nodes that already exist in the database. The L2, and L3 LUT's for these
		// nodes will be missing, because the all_cached flag takes priority.
		
		// PASS 1: Check the L4 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_X = array(	    'all_cached'=>true,
					    'L2'=>null,
					    'keys'=>array( 
										    2=>false,
										    3=>(int)0,
										    5=>true
					    )
		);
		
		$this->assertEquals($check_cache_X, $this->cls->cache['X']);
		
		
		// PASS 2: Combine the L4 nodes into a single array and check it
		// again. This finds L4 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(
					'X'=>$check_cache_X
		);
		
                $this->assertEquals($check_cache, $this->cls->cache);		

		
		// Verify persistent cache state by reading-back all items
		// ####################################################################		
		
		
		$request = array(
				    'X'=>array(),
				    'Y'=>array(),
				    'Z'=>array()		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid); // Should report invalid because 
						    // the '2' and '3' L4's don't exist
		
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
				'X'=>array( 1=>true )
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
				'X'=>array( "F"=>true )	
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
				'X'=>array( 1=>true ),	
				1=>true
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
				array( "L2"=>'X', "L1"=>1	),
		    		array( "L2"=>'Y'		),
				array( "L2"=>'Z'		)		    		    
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
		$this->assertEquals(8, $rows_changed);
		
		
		// Verify db state
		// ####################################################################
		
		$db = new FOX_db();	
		
		$columns = null;
		$args = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L2','L1')
		);
		
		try {			
			$result = $db->runSelectQuery($this->cls->_struct(), $args, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		// NOTE: the datastore will automatically clip empty branches
		
		$check = array(
				'X'=>array(   
								    2=>false,
								    3=>(int)0,
								    5=>true
				)	    
		);
		
                $this->assertEquals($check, $result);
		
		
		// Check cache state
		// ####################################################################			
		
		// The all_cached flag, L2, and L3 LUT's won't exist in the cache yet 
		// because we haven't done a database read.
		
		$check_cache = array();
		
                $this->assertEquals($check_cache, $this->cls->cache);		

		
		// Verify persistent cache state by reading-back all items
		// ####################################################################		
		
		
		$request = array(
				    'X'=>array(),
				    'Y'=>array(),
				    'Z'=>array()		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid); // Should report invalid because 
						    // the '2' and '3' L4's don't exist
		
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
				array( "L2"=>'X', "L1"=>1	),
		    		array( "L2"=>'Y'		),
				array( "L2"=>'Z'		)		    		    
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
		$this->assertEquals(8, $rows_changed);
		
		
		// Verify db state
		// ####################################################################
		
		$db = new FOX_db();	
		
		$columns = null;
		$args = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L2','L1')
		);
		
		try {			
			$result = $db->runSelectQuery($this->cls->_struct(), $args, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		// NOTE: the datastore will automatically clip empty branches
		
		$check = array(		    		    
				'X'=>array(
						    2=>false,
						    3=>(int)0,
						    5=>true
				)	    
		);
		
                $this->assertEquals($check, $result);
		
		// Check cache state
		// ####################################################################			
		
		// The all_cached flag won't exist in the cache yet because we haven't done 
		// a database read. The L2 and L3 LUT's will be null.
		
		// PASS 1: Check the L4 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_X = array(	    
					    'keys'=>array(  
										    2=>false,
										    5=>true,
										    3=>(int)0

					    )
		);
		
		$this->assertEquals($check_cache_X, $this->cls->cache['X']);
		
		
		// PASS 2: Combine the L4 nodes into a single array and check it
		// again. This finds L4 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(
					'X'=>$check_cache_X
		);
		
                $this->assertEquals($check_cache, $this->cls->cache);		

		
		// Verify persistent cache state by reading-back all items
		// ####################################################################		
		
		
		$request = array(
				    'X'=>array(),
				    'Y'=>array(),
				    'Z'=>array()		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid); // Should report invalid because 
						    // the '2' and '3' L4's don't exist
		
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
				    'X'=>array(),
				    'Y'=>array(),
				    'Z'=>array()		    
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
				array( "L2"=>'X', "L1"=>1	),
		    		array( "L2"=>'Y'		),
				array( "L2"=>'Z'		)		    		    
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
		$this->assertEquals(8, $rows_changed);
		
		
		// Verify db state
		// ####################################################################
		
		$db = new FOX_db();	
		
		$columns = null;
		$args = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L2','L1')
		);
		
		try {			
			$result = $db->runSelectQuery($this->cls->_struct(), $args, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		// NOTE: the datastore will automatically clip empty branches
		
		$check = array(
				'X'=>array(
								    2=>false,
								    3=>(int)0,
								    5=>true
				)	    
		);
		
                $this->assertEquals($check, $result);
		
		
		// Check cache state
		// ####################################################################			
		
		// Since we're working with a hot cache, the all_cached flag will be set for all
		// nodes that already exist in the database. The L2 and L3 LUT's for these
		// nodes will be missing, because the all_cached flag takes priority.
		
		// PASS 1: Check the L4 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_X = array(	    'all_cached'=>true,
					    'L2'=>null,
					    'keys'=>array( 
										    2=>false,
										    3=>(int)0,
										    5=>true
					    )
		);
		
		$this->assertEquals($check_cache_X, $this->cls->cache['X']);
		
		
		// PASS 2: Combine the L4 nodes into a single array and check it
		// again. This finds L4 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(
					'X'=>$check_cache_X
		);
		
                $this->assertEquals($check_cache, $this->cls->cache);		

		
		// Verify persistent cache state by reading-back all items
		// ####################################################################		
		
		
		$request = array(
				    'X'=>array(),
				    'Y'=>array(),
				    'Z'=>array()		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid); // Should report invalid because 
						    // the '2' and '3' L4's don't exist
		
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

				array( "L2"=>'X', "L1"=>1	),
				array( "L2"=>'Y',		),
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

				array( "L2"=>'X', "L1"=>'1'	),
				array( "L2"=>1		),
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

				array( "L2"=>'X', "L1"=>1	),
				array( "L2"=>2		)
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
			$rows_changed = $this->cls->dropL1('X', 1, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)1 to indicate 1 db row was deleted
		$this->assertEquals(1, $rows_changed); 
				
		// Drop multiple L1's in array mode
		// ==============================================	    

		try {
			$rows_changed = $this->cls->dropL1('X', array(3,5), $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)2 to indicate 2 db rows were deleted
		$this->assertEquals(2, $rows_changed); 	
		
		
		// Drop multiple L1's in array mode
		// ==============================================	    

		try {
			$rows_changed = $this->cls->dropL1('Z', array(1,2), $ctrl);
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
				'X'=>array(   
								    2=>false 						
				),	
				'Y'=>array(
								    1=>(int)1,
								    2=>(int)-1,								 
								    3=>(float)1.7, 						    
								    4=>(float)-1.6  						
				),			
				'Z'=>array(							    							    
								    3=>$test_obj 
				),		    
		);
		
		$request = array(
				    'X'=>array(),	    // Request all L3 tries in datastore
				    'Y'=>array(),
				    'Z'=>array()		    
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
					array( "L2"=>'X', "L1"=>1),
					array( "L2"=>'X', "L1"=>2),
					array( "L2"=>'X', "L1"=>5),
					array( "L2"=>'X', "L1"=>3),	
					array( "L2"=>'Y', "L1"=>1)	    
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
					array( "L2"=>'Y', "L1"=>array(2,3)),
					array( "L2"=>'Z', "L1"=>array(1,2))	    
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
				'Y'=>array(   							    
								    4=>(float)-1.6  						
				),			
				'Z'=>array(
										    3=>$test_obj  				    
				)		    
		);	
		
		$request = array(
				    'Y'=>array(),
				    'Z'=>array()		    
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

				array( "L2"=>'X', "L1"=>1),
				array( "L2"=>'X', "L1"=>2)		    
		);		
						
		try {			
			$this->cls->dropL1_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

			
		// Single invalid L1 key
		// ####################################################################
		
		try {			
			$data = array(

					array( "L2"=>'X', "L1"=>1),
					array( "L2"=>'X', "L1"=>'2')		    
			);	
			
			$this->cls->dropL1_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		
		// Single invalid L1 key in 'multi' mode
		// ####################################################################
		
		try {			
			$data = array(

					array( "L2"=>'X', "L1"=>array(1,'2')    ),
					array( "L2"=>'X', "L1"=>3		    )		    
			);	
			
			$this->cls->dropL1_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// Multiple invalid L1 keys in 'multi' mode
		// ####################################################################
		
		try {			
			$data = array(

					array( "L2"=>'X', "L1"=>array("F",'2')	),
					array( "L2"=>'X', "L1"=>3			)		    
			);	
			
			$this->cls->dropL1_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}		
		
		// Valid L1, invalid L2
		// ####################################################################
		
		try {			
			$data = array(

					array( "L2"=>"",  "L1"=>1  ),
					array( "L2"=>'X', "L1"=>3  )	    
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

		// Valid L1, extraneous key
		// ####################################################################

		try {			
			$data = array(
					array( "L2"=>'X', "L1"=>1, "L9"=>false  ),
					array( "L2"=>'X', "L1"=>3		    )		    
			);	
			
			$this->cls->dropL1_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}		
	
		// Missing L1 key
		// ####################################################################

		try {			
			$data = array(
					array( "L2"=>'X', "L1"=>1	),
					array( "L2"=>'X',		)		    
			);	
			
			$this->cls->dropL1_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// Valid L1, L2 key replaced with extraneous key
		// ####################################################################

		try {			
			$data = array(
					array( "L2"=>'X', "L1"=>1),
					array( "L0"=>2, "L1"=>2)		    
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

		// Drop a L3 in single mode
		// ==============================================
		
		try {
			$rows_changed = $this->cls->dropL2('X', $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)4 to indicate 4 db rows were deleted
		$this->assertEquals(4, $rows_changed); 
		
		// Drop multiple L3 in single mode
		// ==============================================	    

		try {
			$rows_changed = $this->cls->dropL2(array('Y','Z'), $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)4 to indicate 4 db rows were deleted
		$this->assertEquals(7, $rows_changed); 		
			

		// Verify datastore is in correct state
		// ==============================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		// The delete operations should have cleared the entire datastore
		$check = array();		
		
		$request = array(
				    'X'=>array(),	    // Request all L3 tries in datastore
				    'Y'=>array(),
				    'Z'=>array()		    
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
	* Test fixture for dropL2_multi() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropL2_multi_mode() {

	    
		self::loadData();
		
		
		$ctrl = array(
			"validate"=>true
		);	    

		
		// Drop multiple L2's in single mode
		// ==============================================
		
		try {
		    
			$drop_nodes = array(
					array( "L2"=>'X'),
					array( "L2"=>'Z')	    
			);	
			
			$rows_changed = $this->cls->dropL2_multi($drop_nodes, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}
		
		// Should return (int)8 to indicate 8 rows were dropped from the db
		$this->assertEquals(7, $rows_changed); 
			

		
		// Verify datastore is in correct state
		// ==============================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(			
				'Y'=>array(
						    1=>(int)1,
						    2=>(int)-1,
						    3=>(float)1.7, 							    
						    4=>(float)-1.6 						
				)		    
		);	
		
		$request = array(
				    'X'=>array(),	    // Request all L4 tries in datastore
				    'Y'=>array(),
				    'Z'=>array()		    
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
		
		$this->assertEquals(false, $valid);	// L2 node '1' and '3' shouldn't exist	
		$this->assertEquals($check, $result);
		
	}
		
       /**
	* Test fixture for dropL2_multi() method, data integrity
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropL2_multi_dataIntegrity() {
	    
	    
		$ctrl = array(
			'validate'=>true
		);
		
	    
		// Valid data matrix
		// ####################################################################
	    
		$data = array(
				array( "L2"=>'X' ),
				array( "L2"=>'Z' )		    
		);		
						
		try {			
			$this->cls->dropL2_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

			
		// Single invalid L2 key
		// ####################################################################
		
		try {			
			$data = array(
					array( "L2"=>"X" ),
					array( "L2"=>1   )		    
			);	
			
			$this->cls->dropL2_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL3_multi() throws an exception
			$this->fail("Method dropL2_multi() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) { }
		
		
		// Single invalid L2 key in 'multi' mode
		// ####################################################################
		
		try {			
			$data = array(
					array( "L2"=>array("X",2)   ),
					array( "L2"=>'Y'	    )		    
			);	
			
			$this->cls->dropL2_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL3_multi() throws an exception
			$this->fail("Method dropL2_multi() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// Multiple invalid L2 keys in 'multi' mode
		// ####################################################################
		
		try {			
			$data = array(
					array( "L2"=>array(1,'2') ),
					array( "L2"=>'Y'	    )		    
			);	
			
			$this->cls->dropL2_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL3_multi() throws an exception
			$this->fail("Method dropL2_multi() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}				
		
		// Null L2
		// ####################################################################

		try {			
			$data = array(

					array( "L2"=>null   ),
					array( "L2"=>'X'    )		    
			);	
			
			$this->cls->dropL2_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL3_multi() throws an exception
			$this->fail("Method dropL2_multi() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}		
		
		// Empty data array
		// ####################################################################	    		

		try {			
			$data = array();	
			
			$this->cls->dropL2_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL3_multi() throws an exception
			$this->fail("Method dropL2_multi() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}				

		// Valid L2, extraneous key
		// ####################################################################

		try {			
			$data = array(
					array( "L2"=>'X' ),
					array( "L2"=>'X', "L10"=>"Z"	     )		    
			);	
			
			$this->cls->dropL2_multi($data, $ctrl);
			
			// Execution will halt on the previous line if dropL3_multi() throws an exception
			$this->fail("Method dropL2_multi() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}		
				
	}	
	
			
       /**
	* Tests keyType validators for every possible combination of L4->L1 walks for
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
			$rows_changed = $this->cls->dropL1('X', 1, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(1, $rows_changed);
		
		unset($rows_changed);
		
		
		// L1 - Single invalid L1 key
		// ===========================================
		
		try {			
			$result = $this->cls->dropL1('X', "T", $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L1 - Single invalid L1 key in 'multi' mode
		// =================================================
		
		try {			
			$result = $this->cls->dropL1('X', array(1, "T"), $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L1 - Multiple invalid L1 keys in 'multi' mode
		// ====================================================
		
		try {			
			$result = $this->cls->dropL1('X', array("Q", "T"), $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L1 - Valid L1, invalid L2
		// ===========================================

		try {			
			$result = $this->cls->dropL1(2, 1, $ctrl);
			
			// Execution will halt on the previous line if dropL1() throws an exception
			$this->fail("Method dropL1() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {

		}		
		
		// #########################################################################################	

		// L2 - Single VALID L2
		// ===========================================
		
		$valid = false;
		
		try {			
			$rows_changed = $this->cls->dropL2('X', $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(3, $rows_changed);
		
		unset($rows_changed);		
		
		
		// L2 - Single invalid L2 key
		// ===========================================
		
		try {			
			$result = $this->cls->dropL2(1, $ctrl);
			
			// Execution will halt on the previous line if dropL3() throws an exception
			$this->fail("Method dropL2() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L2 - Single invalid L2 key in 'multi' mode
		// =================================================
		
		try {			
			$result = $this->cls->dropL2(array( 1), $ctrl);
			
			// Execution will halt on the previous line if dropL3() throws an exception
			$this->fail("Method dropL2() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {}
		
		
		// L2 - Multiple invalid L2 keys in 'multi' mode
		// ====================================================
		
		try {			
			$result = $this->cls->dropL2(array(2,null), $ctrl);
			
			// Execution will halt on the previous line if dropL3() throws an exception
			$this->fail("Method dropL2() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) { }
				
		
	}
	
	
	function tearDown() {
	   
		$this->cls = new FOX_dataStore_paged_L2_tester_dropMethods();
		$unistall_ok = $this->cls->uninstall();
		
		$this->assertEquals(true, $unistall_ok);
		
		parent::tearDown();
	}

    
}

?>