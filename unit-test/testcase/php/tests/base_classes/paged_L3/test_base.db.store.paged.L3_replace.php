<?php

/**
 * L3 PAGED ABSTRACT DATASTORE TEST CLASS
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

class FOX_dataStore_paged_L3_tester_replaceMethods extends FOX_dataStore_paged_L3_base {
    

	public static $struct = array(

		"table" => "FOX_dataStore_paged_L3_base",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_dataStore_paged_L3_base",
		"cache_strategy" => "paged",
		"cache_engine" => array("memcached", "redis", "apc", "thread"),	    
		"columns" => array(
		    "L3" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,
			// This forces every zone + rule + key_type + key_id combination to be unique
			"index"=>array("name"=>"top_level_index",	"col"=>array("L3", "L2", "L1"), "index"=>"PRIMARY"), "this_row"=>true),
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
	
	
}  // ENDOF: class FOX_dataStore_paged_L3_tester_replaceMethods 

                                      

/**
 * FOXFIRE UNIT TEST SCRIPT - L3 PAGED ABSTRACT DATASTORE CLASS - REPLACE METHODS
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

class core_L3_paged_abstract_replaceMethods extends RAZ_testCase {

	
    	function setUp() {

	    
		parent::setUp();
				
		
		// Install the db table
		// ===========================================
		
		$this->cls = new FOX_dataStore_paged_L3_tester_replaceMethods();
		
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

				array( "L3"=>'A', "L2"=>"X", "L1"=>1, "L0"=>null),
				array( "L3"=>'A', "L2"=>"X", "L1"=>2, "L0"=>false),
				array( "L3"=>'A', "L2"=>"X", "L1"=>5, "L0"=>true),
				array( "L3"=>'A', "L2"=>"X", "L1"=>3, "L0"=>(int)0),	

				array( "L3"=>'A', "L2"=>"Y", "L1"=>1, "L0"=>(int)1),
				array( "L3"=>'A', "L2"=>"Y", "L1"=>2, "L0"=>(int)-1),
		    		array( "L3"=>'A', "L2"=>"Y", "L1"=>3, "L0"=>(float)1.7),
		    		array( "L3"=>'A', "L2"=>"Y", "L1"=>4, "L0"=>(float)-1.6),
		    
		    		array( "L3"=>'B', "L2"=>"K", "L1"=>1, "L0"=>(string)"foo"),
		    		array( "L3"=>'B', "L2"=>"K", "L1"=>2, "L0"=>array(null, true, false, 1, 1.0, "foo")),
		    		array( "L3"=>'B', "L2"=>"K", "L1"=>3, "L0"=>$test_obj)	
		    
		);		
		
		// Load class with data
		// ####################################################################
				    					
		try {
			$rows_changed = $this->cls->setL1_multi($test_data, $ctrl=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));			
		}			

		// Should return (int)11 to indicate  11 keys were added
		$this->assertEquals(11, $rows_changed); 								
		
		
		// Check cache state
		// ####################################################################	
		
		// NOTE: the LUT's won't be set at this point, because we haven't done any 
		// database reads that give objects authority
		
		$check = array(
				'A'=>array(   'keys'=>array(  'X'=>array(		
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
							    )
					    )
				),			
				'B'=>array(   'keys'=>array(  'K'=>array( 	
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo"),
										    3=>$test_obj  						
							    )	
					    )						
				)		    		    
		);
		
		$this->assertEquals($check, $this->cls->cache);	
		
		
		// Check db state
		// ####################################################################		
		
		$check = array(
				'A'=>array(   'X'=>array(	
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
					    )					    
				),			
				'B'=>array(   'K'=>array( 
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo"),								    							    
								    3=>$test_obj  						
					    )					    
				 )		    		    
		);		
		
		$db = new FOX_db();	
		
		$columns = null;
		$args = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L3','L2','L1')
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
	* Test fixture for replaceL3_multi() method, cold cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_replaceL2_multi_COLD() {
	    

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
		
		// NOTE: a L2 must have at least one L1 key within it in order to have an entry within the  
		// db. Without a L1 key inside it, there's nothing to write to the L1 column in the
		// db (which would violate the table index). In addition to this, storing empty L2's would waste 
		// space in the table and cache. Therefore, overwriting a L2 node with an empty array drops
		// that node from the datastore
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$data = array(
				'A'=>array(   'X'=>array(	
								    9=>'foo',
								    3=>'bar',										   					    
								    1=>'foo',
								    7=>'bar',
								    4=>'baz' 					    
					    ),
					    
					    'Y'=>array()
					    	
				),

				// Ignore the entire L3 '2' node
				// 2=>array( ... ),

				// Create a new L3 '3' node
				'C'=>array(   'X'=>array(	
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo"),								    							    
								    3=>$test_obj  						
					    )					    
				)		    
		);
		
		// Replace items
		// ####################################################################
		
		$ctrl = array(		    
				'validate'=>true		    
		);
				
		try {			
			$this->cls->replaceL2_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this-> fail($child->dumpString(1));	
		}
		
		
		// Check cache state
		// ####################################################################			
		
		// The LUT's will be set for all L2 nodes that we modified, since, by overwriting an
		// entire L2 node, we've given it authority. The other LUT arrays won't exist in the cache
		// yet because we haven't done a read.		
		
		// PASS 1: Check the L3 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_A = array(	    'L2'=>array(    'X'=>true),
					    'keys'=>array(  'X'=>array(	
										    9=>'foo',
										    3=>'bar',										   					    									
										    1=>'foo',
										    7=>'bar',
										    4=>'baz' 					    
							    ),						
					    )				
		);

		$this->assertEquals($check_cache_A, $this->cls->cache['A']);		
		
		$check_cache_C = array(	    'L2'=>array(    'X'=>true ),			    
					    'keys'=>array(  'X'=>array(	
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo"),								    							    
										    3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_C, $this->cls->cache['C']);
		
		
		// PASS 2: Combine the L3 nodes into a single array and check it
		// again. This finds L4 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(	
					'A'=>$check_cache_A,
					'C'=>$check_cache_C		    
		);		
	
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_A, $check_cache_C, $check_cache);
		
		
		// Load updated items
		// ####################################################################
		
		$request = array(
				    'A'=>array(),
				    'B'=>array(),
				    'C'=>array()		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);						
		
		// PASS 1: Check the L3 nodes individually to simplify debugging
		// ====================================================================
		
		$check_data_A = array(  'X'=>array( 
								9=>'foo',
								3=>'bar',										   					    									
								1=>'foo',
								7=>'bar',
								4=>'baz' 						    
					)
	
		);
		
		$this->assertEquals($check_data_A, $result['A']);	
		
		$check_data_B = array(	'K'=>array( 	
								1=>(string)"foo",
								2=>array(null, true, false, 1, 1.0, "foo"),
								3=>$test_obj  						
					)					    
		);

		$this->assertEquals($check_data_B, $result['B']);
		
		$check_data_C = array(	'X'=>array( 	
								1=>(string)"foo",
								2=>array(null, true, false, 1, 1.0, "foo"),
								3=>$test_obj  						
					)					    
		);
		
		$this->assertEquals($check_data_C, $result['C']);		
		
		
		// PASS 2: Combine the L3 nodes into a single array and check it
		// again. This finds L3 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_data = array(
					'A'=>$check_data_A,
					'B'=>$check_data_B,
					'C'=>$check_data_C,		    
		);
			
		$this->assertEquals($check_data, $result);	
		
		unset($check_data_A, $check_data_B, $check_data_C, $check_data);

		
		// Check cache state
		// ####################################################################			
				
		// PASS 1: Check the L3 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_A = array(	    'all_cached'=>true,
					    'L2'=>null,
					    'keys'=>array(  'X'=>array(
									9=>'foo',
									3=>'bar',										   					    									
									1=>'foo',
									7=>'bar',
									4=>'baz' 
							    ),
					    )
		);
		
		$this->assertEquals($check_cache_A, $this->cls->cache['A']);
		
		$check_cache_B = array(	    'all_cached'=>true,
					    'L2'=>null,				    
					    'keys'=>array(  'K'=>array(	
									1=>(string)"foo",
									2=>array(null, true, false, 1, 1.0, "foo"),
									3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_B, $this->cls->cache['B']);
		
		$check_cache_C = array(	    'all_cached'=>true,
					    'L2'=>null,				    
					    'keys'=>array(  'X'=>array(	
									1=>(string)"foo",
									2=>array(null, true, false, 1, 1.0, "foo"),
									3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_C, $this->cls->cache['C']);	
		
		
		// PASS 2: Combine the L3 nodes into a single array and check it
		// again. This finds L3 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(		    
					'A'=>$check_cache_A,
					'B'=>$check_cache_B,		    
					'C'=>$check_cache_C,		    
		);
		
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_A, $check_cache_B, $check_cache_C, $check_cache);
			
		
	}
	
	
       /**
	* Test fixture for replaceL3_multi() method, warm cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_replaceL2_multi_WARM() {
   

		self::loadData();		
				
		
		// WARM CACHE - Items in cache from previous ADD operation
		// ===================================================================				
		
		// NOTE: a L2 must have at least one L1 key within it in order to have an entry within the  
		// db. Without a L1 key inside it, there's nothing to write to the L1 column in the
		// db (which would violate the table index). In addition to this, storing empty L2's would waste 
		// space in the table and cache. Therefore, overwriting a L2 node with an empty array drops
		// that node from the datastore
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";		
		
		$data = array(
				'A'=>array(   'X'=>array(	
								    9=>'foo',
								    3=>'bar',										   					    
								    1=>'foo',
								    7=>'bar',
								    4=>'baz' 					    
					    ),
					    
					    'Y'=>array()
					    	
				),

				// Ignore the entire L3 '2' node
				// 2=>array( ... ),

				// Create a new L3 '3' node
				'C'=>array(   'X'=>array(	
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo"),								    							    
								    3=>$test_obj  						
					    )					    
				)		    
		);
		
		
		// Replace items
		// ####################################################################
		
		$ctrl = array(		    
				'validate'=>true		    
		);
				
		try {			
			$this->cls->replaceL2_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
		// Check cache state
		// ####################################################################			
		
		// The LUT's will be set for all L2 nodes that we modified, since, by overwriting an
		// entire L2 node, we've given it authority. The other LUT arrays won't exist in the cache
		// yet because we haven't done a read.
		
		// PASS 1: Check the L3 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_A = array(	    'L2'=>array(    'X'=>true ),
					    'keys'=>array(  'X'=>array(	
								    9=>'foo',
								    3=>'bar',										   					    
								    1=>'foo',
								    7=>'bar',
								    4=>'baz' 						    
							    )						
					    )
		);
		
		$this->assertEquals($check_cache_A, $this->cls->cache['A']);
		
		$check_cache_B = array(	    'keys'=>array(  'K'=>array(	
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo"),										  						    
										    3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_B, $this->cls->cache['B']);
		
		$check_cache_C = array(	    'L2'=>array(    'X'=>true	),				    
					    'keys'=>array(  'X'=>array(	
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo"),										    							    
										    3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_C, $this->cls->cache['C']);
		
		
		// PASS 2: Combine the L3 nodes into a single array and check it
		// again. This finds L3 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(		    
					'A'=>$check_cache_A,	
		    			'B'=>$check_cache_B,
					'C'=>$check_cache_C		    		    
		);	

		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_A, $check_cache_B, $check_cache_C, $check_cache);

		
		// Load updated items
		// ####################################################################
		
		$request = array(
				    'A'=>array(),
				    'B'=>array(),
				    'C'=>array()		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);						
		
		
		// PASS 1: Check the L3 nodes individually to simplify debugging
		// ====================================================================
		
		$check_data_A = array(	    'X'=>array(	
								    9=>'foo',
								    3=>'bar',										   					    
								    1=>'foo',
								    7=>'bar',
								    4=>'baz' 				    

					    )
		);
		
		$check_data_B = array(	    'K'=>array(	
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo"),								    						    
								    3=>$test_obj 
					    )					    
		);
		
		$check_data_C = array(	    'X'=>array(	
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo"),								    						    
								    3=>$test_obj 
					    )					    
		);
		
		// PASS 2: Combine the L3 nodes into a single array and check it
		// again. This finds L3 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_data = array(
					'A'=>$check_data_A,
					'B'=>$check_data_B,
					'C'=>$check_data_C,		    
		);
			
		$this->assertEquals($check_data, $result);	
		
		unset($check_data_A, $check_data_B,$check_data_C, $check_data);

		
		// Check cache state
		// ####################################################################			
		
		// PASS 1: Check the L3 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_A = array(	    'all_cached'=>true,
					    'L2'=>null,
					    'keys'=>array(  'X'=>array(		   					    
								    1=>'foo',
								    3=>'bar',						
								    4=>'baz', 											
								    7=>'bar',
								    9=>'foo'
							    ),
					    )
		);
		
		$this->assertEquals($check_cache_A, $this->cls->cache['A']);
		
		$check_cache_B = array(	    'all_cached'=>true,
					    'L2'=>null,				    
					    'keys'=>array(  'K'=>array(	
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo"),										    							    
										    3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_B, $this->cls->cache['B']);
		
		$check_cache_C = array(	    'all_cached'=>true,
					    'L2'=>null,				    
					    'keys'=>array(  'X'=>array( 	
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo"),										    							    
										    3=>$test_obj  				
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_C, $this->cls->cache['C']);		
		
		$check_cache = array(		    
					'A'=>$check_cache_A,
					'B'=>$check_cache_B,	
					'C'=>$check_cache_C			    		    
		);
		
		$this->assertEquals($check_cache, $this->cls->cache);
		
		unset($check_cache_A, $check_cache_B, $check_cache_C, $check_cache);
			
		
	}
	
	
       /**
	* Test fixture for replaceL3_multi() method, hot cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_replaceL2_multi_HOT() {
  

		self::loadData();
		
		
		// Load the cache
		// ####################################################################
		
		$request = array(
				    'A'=>array(),
				    'B'=>array()	    
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
		
		// NOTE: a L2 must have at least one L1 key within it in order to have an entry within the  
		// db. Without a L1 key inside it, there's nothing to write to the L1 column in the
		// db (which would violate the table index). In addition to this, storing empty L2's would waste 
		// space in the table and cache. Therefore, overwriting a L2 node with an empty array drops
		// that node from the datastore
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";		
		
		$data = array(
				'A'=>array(   'X'=>array(	
								    9=>'foo',
								    3=>'bar',										   					    
								    1=>'foo',
								    7=>'bar',
								    4=>'baz' 					    
					    ),
					    
					    'Y'=>array()
					    	
				),

				// Ignore the entire L3 '2' node
				// 2=>array( ... ),

				// Create a new L3 '3' node
				'C'=>array(   'X'=>array(	
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo"),								    							    
								    3=>$test_obj  						
					    )					    
				)		    
		);
		
		
		// Replace items
		// ####################################################################
		
		$ctrl = array(		    
				'validate'=>true		    
		);
				
		try {			
			$this->cls->replaceL2_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
		// Check cache state
		// ####################################################################			
				
		// Since we're working with a hot cache, the all_cached flag will be set for all
		// nodes that already exist in the database. The L2 and L3 LUT's for these
		// nodes will be missing, because the all_cached flag takes priority. However, for
		// L3 nodes that were created in the cache as a result of us adding new L2's to the
		// datastore, entries in the parent L3 object's L2 LUT will be set. This is because
		// the parent L4 was created, not read from the db; so its all_cached flag won't be 
		// set because it doesn't have authority.
				
		// PASS 1: Check the L4 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_A = array(	    'all_cached'=>true,
					    'L2'=>null,
					    'keys'=>array(  'X'=>array(		   					    
								    1=>'foo',
								    3=>'bar',						
								    4=>'baz', 											
								    7=>'bar',
								    9=>'foo'
							    ),
					    )
		);
		
		$this->assertEquals($check_cache_A, $this->cls->cache['A']);
		
		
		$check_cache_B = array(	    'all_cached'=>true,
					    'L2'=>null,				    
					    'keys'=>array(  'K'=>array(	
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo"),										    							    
										    3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_B, $this->cls->cache['B']);
		

		$check_cache_C = array(	    'L2'=>array(    'X'=>true	),			    
					    'keys'=>array(  'X'=>array( 	
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo"),										    							    
										    3=>$test_obj  				
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_C, $this->cls->cache['C']);
		
		
		// PASS 2: Combine the L4 nodes into a single array and check it
		// again. This finds L4 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(		    
					'A'=>$check_cache_A,
					'B'=>$check_cache_B,
					'C'=>$check_cache_C,		    
		);
		
		unset($check_cache_A, $check_cache_B, $check_cache_C, $check_cache);
		
		
		// Load updated items
		// ####################################################################
		
		$request = array(
				    'A'=>array(),
				    'B'=>array(),
				    'C'=>array()		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);						
		
		
		// PASS 1: Check the L4 nodes individually to simplify debugging
		// ====================================================================
		
		$check_data_A = array(	    'X'=>array(	
								    1=>'foo',
								    3=>'bar',						
								    4=>'baz', 											
								    7=>'bar',
								    9=>'foo'					    
					    ),    
		);
		
		$this->assertEquals($check_data_A, $result['A']);
		
		$check_data_B = array(	    'K'=>array(	 
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo"),								    							    
								    3=>$test_obj  						
					    )							    
		);
		
		$this->assertEquals($check_data_B, $result['B']);
		
		$check_data_C = array(	    'X'=>array(	 
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo"),								    							    
								    3=>$test_obj  						
					    )						    
		);
		
		$this->assertEquals($check_data_C, $result['C']);
		
		// PASS 2: Combine the L4 nodes into a single array and check it
		// again. This finds L4 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_data = array(
					'A'=>$check_data_A,	
		    			'B'=>$check_data_B,
					'C'=>$check_data_C		    
		);
			
		$this->assertEquals($check_data, $result);
		
		unset($check_data_A, $check_data_B, $check_data_C, $check_data);
		

		// Check cache state
		// ####################################################################			
		
		
		// PASS 1: Check the L4 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_A = array(	    'all_cached'=>true,	    // $all_cached will be true because this L4 had		    
					    'L2'=>null,
					    'keys'=>array(  'X'=>array(	
								    1=>'foo',
								    3=>'bar',						
								    4=>'baz', 											
								    7=>'bar',
								    9=>'foo'			    
							    )							
					    )
		);
		
		$this->assertEquals($check_cache_A, $this->cls->cache['A']);		
		
		$check_cache_B = array(	    'all_cached'=>true,
					    'L2'=>null,				    
					    'keys'=>array(  'K'=>array(	
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo"),
										    3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_B, $this->cls->cache['B']);
		
		$check_cache_C = array(	    'all_cached'=>true,
					    'L2'=>null,				    
					    'keys'=>array(  'X'=>array(	
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo"),
										    3=>$test_obj  												
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_C, $this->cls->cache['C']);
		
		
		// PASS 2: Combine the L4 nodes into a single array and check it
		// again. This finds L4 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(		    
					'A'=>$check_cache_A,
					'B'=>$check_cache_B,
					'C'=>$check_cache_C		    		    
		);
		
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_A, $check_cache_B, $check_cache_C, $check_cache);
					
	}
	
	
       /**
	* Test fixture for replaceL2_multi() method, data integrity
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_replaceL2_multi_dataIntegrity() {
	    
	    
		$ctrl = array(		    
				'validate'=>true		    
		);
		
	    
		// Valid data trie
		// ####################################################################
	    
		$data = array(
				'A'=>array(   'X'=>array(	
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo"),
								    3=>'bar' 
					    )					    
				)		    
		);
		
		try {			
			$this->cls->replaceL2_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		
		// Invalid data type for node at clip_order
		// ####################################################################
	    
		// NOTE: Since this is replaceL2_multi(), clip_order is at the L2 key
		
		$data = array(
				'A'=>array(   'X'=>array(	
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo"),
								    3=>'bar' 
					    ),
					    'Y'=>(string)'fail' // Invalid clip node
					    					    
				)		    
		);
		
		try {			
			$this->cls->replaceL2_multi($data, $ctrl);
			
			// Execution will halt on the previous line if replaceL2_multi() throws an exception
			$this->fail("Method replaceL2_multi failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}		
			
		
		// Walk terminates above clip_order (L4)
		// ####################################################################
	    
		// NOTE: Since this is replaceL2_multi(), clip_order is at the L2 key
		
		$data = array(
				'A'=>array(   'X'=>array(	
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo")								    								    				    

					    )				    
				),
				'B'=>array() // Invalid clip node (at L4)
		);
		
		try {			
			$this->cls->replaceL2_multi($data, $ctrl);
			
			// Execution will halt on the previous line if replaceL2_multi() throws an exception
			$this->fail("Method replaceL2_multi failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {

		}
		
		
		// Invalid L1 key data type
		// ####################################################################
	    
		// NOTE: we can't do the '1'=> "fails on string" key data type test here because PHP
		// automatically converts (string)'1' to (int)1 before sending it in to the function
		
		$data = array(
				'A'=>array(   'X'=>array(	
								    'K'=>(string)"foo", // Invalid L1 key
								     2=>array(null, true, false, 1, 1.0, "foo")								    			   

					    )					    
				)		    
		);
		
		try {			
			$this->cls->replaceL2_multi($data, $ctrl);
			
			// Execution will halt on the previous line if replaceL2_multi() throws an exception
			$this->fail("Method replaceL2_multi failed to throw an exception on invalid L1 key");			
		}
		catch (FOX_exception $child) {

		}				
		
		// Invalid L2 key data type
		// ####################################################################
		
		$data = array(
				'A'=>array(   99=>array(	
								    1=>(string)"foo", 
								    2=>array(null, true, false, 1, 1.0, "foo")								    			    
											    				
					    )					    
				)		    
		);
		
		try {			
			$this->cls->replaceL2_multi($data, $ctrl);
			
			// Execution will halt on the previous line if replaceL2_multi() throws an exception
			$this->fail("Method replaceL2_multi failed to throw an exception on invalid L3 key");			
		}
		catch (FOX_exception $child) {

		}	
		
		// Invalid L3 key data type
		// ####################################################################
		
		// NOTE: we can't do the '1'=> "fails on string" key data type test here because PHP
		// automatically converts (string)'1' to (int)1 before sending it in to the function
		
		$data = array(
				'A'=>array(   'X'=>array(	
								    1=>(string)"foo", 
								    2=>array(null, true, false, 1, 1.0, "foo")								    			    
												
					    )			    
				),
				// Invalid L3 key
				1=>array( 'X'=>array(	
								    1=>(string)"foo", 
								    2=>array(null, true, false, 1, 1.0, "foo")								    			    
					    )			    
				)		    
		);
		
		try {			
			$this->cls->replaceL2_multi($data, $ctrl);
			
			// Execution will halt on the previous line if replaceL2_multi() throws an exception
			$this->fail("Method replaceL2_multi failed to throw an exception on invalid L4 key");			
		}
		catch (FOX_exception $child) {

		}		
		
	    
	}
	
	
       /**
	* Test fixture for replaceL3_multi() method, cold cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_replaceL3_multi_COLD() {
    

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
		
		// NOTE: a L3 must have at least one L2->L1 walk within it in order to have an entry within the  
		// db. Without a L2->L1 walk inside it, there's nothing to write to the L1 and L2 columns 
		// in the db (which would violate the table index). In addition to this, storing empty L3's would 
		// waste space in the table and cache. Therefore, overwriting a L3 node with an empty array drops
		// that node from the datastore
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$data = array(
				'A'=>array(   'X'=>array(	
								    9=>'foo',
								    3=>'bar',										   					    
								    1=>'foo',
								    7=>'bar',
								    4=>'baz' 					    
					    )
					    	
				),


				// Ignore the entire L3 '2' node
				// 2=>array( ... ),

				// Create a new L3 '3' node
				'C'=>array(   'X'=>array(	
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo"),								    							    
								    3=>$test_obj  						
					    )					    
				)		    
		);
		
		
		// Replace items
		// ####################################################################
		
		$ctrl = array(		    
				'validate'=>true		    
		);
				
		try {			
			$this->cls->replaceL3_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
		// Check cache state
		// ####################################################################			
		
		// The LUT's will be set for all L3 nodes that we modified, since, by overwriting an
		// entire L3 node, we've given it authority. The other LUT arrays won't exist in the cache
		// yet because we haven't done a read.
		
		
		// PASS 1: Check the L3 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_A = array(	    'all_cached'=>true,
					    'keys'=>array(  'X'=>array(	
										    9=>'foo',
										    3=>'bar',										   					    									
										    1=>'foo',
										    7=>'bar',
										    4=>'baz' 					    
							    ),						
					    )				
		);

		$this->assertEquals($check_cache_A, $this->cls->cache['A']);		
		
		$check_cache_C = array(	    'all_cached'=>true,
					    'keys'=>array(  'X'=>array(	
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo"),								    							    
										    3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_C, $this->cls->cache['C']);
		
		
		// PASS 2: Combine the L3 nodes into a single array and check it
		// again. This finds L3 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(	
					'A'=>$check_cache_A,
					'C'=>$check_cache_C		    
		);		
	
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_A, $check_cache_C, $check_cache);

		
		// Load updated items
		// ####################################################################
		
		$request = array(
				    'A'=>array(),
				    'B'=>array(),
				    'C'=>array()		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);						
		
		
		// PASS 1: Check the L3 nodes individually to simplify debugging
		// ====================================================================
		
		$check_data_A = array(  'X'=>array( 
								9=>'foo',
								3=>'bar',										   					    									
								1=>'foo',
								7=>'bar',
								4=>'baz' 						    
					)
	
		);
		
		$this->assertEquals($check_data_A, $result['A']);	
		
		$check_data_B = array(	'K'=>array( 	
								1=>(string)"foo",
								2=>array(null, true, false, 1, 1.0, "foo"),
								3=>$test_obj  						
					)					    
		);

		$this->assertEquals($check_data_B, $result['B']);
		
		$check_data_C = array(	'X'=>array( 	
								1=>(string)"foo",
								2=>array(null, true, false, 1, 1.0, "foo"),
								3=>$test_obj  						
					)					    
		);
		
		$this->assertEquals($check_data_C, $result['C']);		
		
		
		// PASS 2: Combine the L3 nodes into a single array and check it
		// again. This finds L3 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_data = array(
					'A'=>$check_data_A,
					'B'=>$check_data_B,
					'C'=>$check_data_C,		    
		);
			
		$this->assertEquals($check_data, $result);	
		
		unset($check_data_A, $check_data_B, $check_data_C, $check_data);

		
		// Check cache state
		// ####################################################################			
				
		// PASS 1: Check the L3 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_A = array(	    'all_cached'=>true,
					    'keys'=>array(  'X'=>array(
									9=>'foo',
									3=>'bar',										   					    									
									1=>'foo',
									7=>'bar',
									4=>'baz' 
							    ),
					    )
		);
		
		$this->assertEquals($check_cache_A, $this->cls->cache['A']);
		
		$check_cache_B = array(	    'all_cached'=>true,
					    'L2'=>null,
					    'keys'=>array(  'K'=>array(	
									1=>(string)"foo",
									2=>array(null, true, false, 1, 1.0, "foo"),
									3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_B, $this->cls->cache['B']);
		
		$check_cache_C = array(	    'all_cached'=>true,
					    'keys'=>array(  'X'=>array(	
									1=>(string)"foo",
									2=>array(null, true, false, 1, 1.0, "foo"),
									3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_C, $this->cls->cache['C']);		
		
		
		// PASS 2: Combine the L3 nodes into a single array and check it
		// again. This finds L3 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(		    
					'A'=>$check_cache_A,
					'B'=>$check_cache_B,		    
					'C'=>$check_cache_C,		    
		);
		
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_A, $check_cache_B, $check_cache_C, $check_cache);
			
		
	}
	
	
       /**
	* Test fixture for replaceL3_multi() method, warm cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_replaceL3_multi_WARM() {


		self::loadData();		
				
		
		// WARM CACHE - Items in cache from previous ADD operation
		// ===================================================================				
		
		// NOTE: a L3 must have at least one L2->L1 walk within it in order to have an entry within the  
		// db. Without a L2->L1 walk inside it, there's nothing to write to the L1 and L2 columns 
		// in the db (which would violate the table index). In addition to this, storing empty L3's would 
		// waste space in the table and cache. Therefore, overwriting a L3 node with an empty array drops
		// that node from the datastore
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";		
		
		$data = array(
				'A'=>array(   'X'=>array(	
								    9=>'foo',
								    3=>'bar',										   					    
								    1=>'foo',
								    7=>'bar',
								    4=>'baz' 					    
					    )
					    	
				),


				// Ignore the entire L3 '2' node
				// 2=>array( ... ),

				// Create a new L3 '3' node
				'C'=>array(   'X'=>array(	
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo"),								    							    
								    3=>$test_obj  						
					    )					    
				)		    
		);
		
		
		// Replace items
		// ####################################################################
		
		$ctrl = array(		    
				'validate'=>true		    
		);
				
		try {			
			$this->cls->replaceL3_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
		// Check cache state
		// ####################################################################			
		
		// The LUT's will be set for all L3 nodes that we modified, since, by overwriting an
		// entire L3 node, we've given it authority. The other LUT arrays won't exist in the cache
		// yet because we haven't done a read.
		
		
		// PASS 1: Check the L3 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_A = array(	    'all_cached'=>true,
					    'keys'=>array(  'X'=>array(	
										    9=>'foo',
										    3=>'bar',										   					    									
										    1=>'foo',
										    7=>'bar',
										    4=>'baz' 					    
							    ),						
					    )				
		);
		
		$this->assertEquals($check_cache_A, $this->cls->cache['A']);	
		
		$check_cache_B = array(	    
					    'keys'=>array(  'K'=>array(	
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo"),								    							    
										    3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_B, $this->cls->cache['B']);		
		
		$check_cache_C = array(	    'all_cached'=>true,
					    'keys'=>array(  'X'=>array(	
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo"),								    							    
										    3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_C, $this->cls->cache['C']);
		
		
		// PASS 2: Combine the L4 nodes into a single array and check it
		// again. This finds L4 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(	
					'A'=>$check_cache_A,
					'B'=>$check_cache_B,
					'C'=>$check_cache_C		    
		);		
	
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_A, $check_cache_C, $check_cache);

		
		// Load updated items
		// ####################################################################
		
		$request = array(
				    'A'=>array(),
				    'B'=>array(),
				    'C'=>array()		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);						
		
		
		// PASS 1: Check the L4 nodes individually to simplify debugging
		// ====================================================================
		
		$check_data_A = array(  'X'=>array( 
								9=>'foo',
								3=>'bar',										   					    									
								1=>'foo',
								7=>'bar',
								4=>'baz' 						    
					)
	
		);
		
		$this->assertEquals($check_data_A, $result['A']);	
		
		$check_data_B = array(	'K'=>array( 	
								1=>(string)"foo",
								2=>array(null, true, false, 1, 1.0, "foo"),
								3=>$test_obj  						
					)					    
		);

		$this->assertEquals($check_data_B, $result['B']);
		
		$check_data_C = array(	'X'=>array( 	
								1=>(string)"foo",
								2=>array(null, true, false, 1, 1.0, "foo"),
								3=>$test_obj  						
					)					    
		);
		
		$this->assertEquals($check_data_C, $result['C']);		
		
		
		// PASS 2: Combine the L4 nodes into a single array and check it
		// again. This finds L4 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_data = array(
					'A'=>$check_data_A,
					'B'=>$check_data_B,
					'C'=>$check_data_C,		    
		);
			
		$this->assertEquals($check_data, $result);	
		
		unset($check_data_A, $check_data_B, $check_data_C, $check_data);

	
		// Check cache state
		// ####################################################################		
		
		// PASS 1: Check the L4 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_A = array(	    'all_cached'=>true,
					    'keys'=>array(  'X'=>array(
									9=>'foo',
									3=>'bar',										   					    									
									1=>'foo',
									7=>'bar',
									4=>'baz' 
							    ),
					    )
		);
		
		$this->assertEquals($check_cache_A, $this->cls->cache['A']);
		
		$check_cache_B = array(	    'all_cached'=>true,
					    'L2'=>null,
					    'keys'=>array(  'K'=>array(	
									1=>(string)"foo",
									2=>array(null, true, false, 1, 1.0, "foo"),
									3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_B, $this->cls->cache['B']);
		
		$check_cache_C = array(	    'all_cached'=>true,
					    'keys'=>array(  'X'=>array(	
									1=>(string)"foo",
									2=>array(null, true, false, 1, 1.0, "foo"),
									3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_C, $this->cls->cache['C']);	
		
		
		// PASS 2: Combine the L4 nodes into a single array and check it
		// again. This finds L4 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(		    
					'A'=>$check_cache_A,
					'B'=>$check_cache_B,		    
					'C'=>$check_cache_C,		    
		);
		
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_A, $check_cache_B, $check_cache_C, $check_cache);
			
		
	}
	
	
       /**
	* Test fixture for replaceL3_multi() method, hot cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_replaceL3_multi_HOT() {


		self::loadData();
		
		
		// Load the cache
		// ####################################################################
		
		$request = array(
				    'A'=>array(),
				    'B'=>array()	    
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
		
		// NOTE: a L3 must have at least one L2->L1 walk within it in order to have an entry within the  
		// db. Without a L2->L1 walk inside it, there's nothing to write to the L1 and L2 columns 
		// in the db (which would violate the table index). In addition to this, storing empty L3's would 
		// waste space in the table and cache. Therefore, overwriting a L3 node with an empty array drops
		// that node from the datastore
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";		
		
		$data = array(
				'A'=>array(   'X'=>array(	
								    9=>'foo',
								    3=>'bar',										   					    
								    1=>'foo',
								    7=>'bar',
								    4=>'baz' 					    
					    )
					    	
				),


				// Ignore the entire L3 '2' node
				// 2=>array( ... ),

				// Create a new L3 '3' node
				'C'=>array(   'X'=>array(	
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo"),								    							    
								    3=>$test_obj  						
					    )					    
				)		    
		);
		
		
		// Replace items
		// ####################################################################
		
		$ctrl = array(		    
				'validate'=>true		    
		);
				
		try {			
			$this->cls->replaceL3_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
		// Check cache state
		// ####################################################################
		
		// Since we're working with a hot cache, the all_cached flag will be set for all
		// nodes that already exist in the database. The L2 and L3 LUT's for these
		// nodes will be missing, because the all_cached flag takes priority. 
		// 
		// However, for L4 nodes that were created in the cache as a result of us adding new 
		// L2's to the datastore, entries in the parent L4 object's L3 LUT will be set. This 
		// is because the parent L4 was created, not read from the db; so its all_cached flag 
		// won't be set because it doesn't have authority.
						
		// PASS 1: Check the L4 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_A = array(	    'all_cached'=>true,
					    'keys'=>array(  'X'=>array(	
										    9=>'foo',
										    3=>'bar',										   					    									
										    1=>'foo',
										    7=>'bar',
										    4=>'baz' 					    
							    ),						
					    )				
		);
		
		$this->assertEquals($check_cache_A, $this->cls->cache['A']);	
		
		$check_cache_B = array(	    'all_cached'=>true,
					    'L2'=>null,
					    'keys'=>array(  'K'=>array(	
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo"),								    							    
										    3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_B, $this->cls->cache['B']);		
		
		$check_cache_C = array(	    'all_cached'=>true,
					    'keys'=>array(  'X'=>array(	
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo"),								    							    
										    3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_C, $this->cls->cache['C']);	
		
		
		// PASS 2: Combine the L4 nodes into a single array and check it
		// again. This finds L4 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(		    
					'A'=>$check_cache_A,
					'B'=>$check_cache_B,		    
					'C'=>$check_cache_C,		    
		);
		
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_A, $check_cache_B, $check_cache_C, $check_cache);
	
		
		// Load updated items
		// ####################################################################
		
		$request = array(
				    'A'=>array(),
				    'B'=>array(),
				    'C'=>array()		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);						
		
		
		// PASS 1: Check the L4 nodes individually to simplify debugging
		// ====================================================================
		
		$check_data_A = array(  'X'=>array( 
								9=>'foo',
								3=>'bar',										   					    									
								1=>'foo',
								7=>'bar',
								4=>'baz' 						    
					)
	
		);
		
		$this->assertEquals($check_data_A, $result['A']);	
		
		$check_data_B = array(	'K'=>array( 	
								1=>(string)"foo",
								2=>array(null, true, false, 1, 1.0, "foo"),
								3=>$test_obj  						
					)					    
		);

		$this->assertEquals($check_data_B, $result['B']);
		
		$check_data_C = array(	'X'=>array( 	
								1=>(string)"foo",
								2=>array(null, true, false, 1, 1.0, "foo"),
								3=>$test_obj  						
					)					    
		);
		
		$this->assertEquals($check_data_C, $result['C']);		
		
		
		// PASS 2: Combine the L4 nodes into a single array and check it
		// again. This finds L4 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_data = array(
					'A'=>$check_data_A,
					'B'=>$check_data_B,
					'C'=>$check_data_C,		    
		);
			
		$this->assertEquals($check_data, $result);	
		
		unset($check_data_A, $check_data_B, $check_data_C, $check_data);
		

		// Check cache state
		// ####################################################################			
		
		// PASS 1: Check the L4 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_A = array(	    'all_cached'=>true,
					    'keys'=>array(  'X'=>array(
									9=>'foo',
									3=>'bar',										   					    									
									1=>'foo',
									7=>'bar',
									4=>'baz' 
							    ),
					    )
		);
		
		$this->assertEquals($check_cache_A, $this->cls->cache['A']);
		
		$check_cache_B = array(	    'all_cached'=>true,
					    'L2'=>null,
					    'keys'=>array(  'K'=>array(	
									1=>(string)"foo",
									2=>array(null, true, false, 1, 1.0, "foo"),
									3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_B, $this->cls->cache['B']);
		
		$check_cache_C = array(	    'all_cached'=>true,
					    'keys'=>array(  'X'=>array(	
									1=>(string)"foo",
									2=>array(null, true, false, 1, 1.0, "foo"),
									3=>$test_obj  						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_C, $this->cls->cache['C']);	
		
		
		// PASS 2: Combine the L4 nodes into a single array and check it
		// again. This finds L4 keys that aren't supposed to be there.
		// ====================================================================
		
		$check_cache = array(		    
					'A'=>$check_cache_A,
					'B'=>$check_cache_B,		    
					'C'=>$check_cache_C,		    
		);
		
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_A, $check_cache_B, $check_cache_C, $check_cache);
			
		
	}
	
	
       /**
	* Test fixture for replaceL3_multi() method, data integrity
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_replaceL3_multi_dataIntegrity() {
	    
	    
		$ctrl = array(		    
				'validate'=>true		    
		);
		
	    
		// Valid data trie
		// ####################################################################
	    
		// NOTE: Since this is replaceL3_multi(), clip_order is at the L3 key
		
		$data = array(
				'A'=>array(   'X'=>array(		
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo"),
								    3=>'bar'

					    )
				)		    
		);
		
		try {			
			$this->cls->replaceL3_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Invalid data type for node at clip_order
		// ####################################################################
	    
		// NOTE: Since this is replaceL3_multi(), clip_order is at the L2 key
		
		$data = array(
				'A'=>array(   'X'=>array(	
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo"),
								    3=>'bar' 							
					    ),
					    'R'=>(string)'fail' // Invalid clip node				    
				)		    
		);
		
		try {			
			$this->cls->replaceL3_multi($data, $ctrl);
			
			// Execution will halt on the previous line if replaceL3_multi() throws an exception
			$this->fail("Method replaceL3_multi failed to throw an exception on invalid data trie");			
		}
		catch (FOX_exception $child) {
	
		}		
		
		// Invalid L1 key data type
		// ####################################################################
	    
		// NOTE: we can't do the '1'=> "fails on string" key data type test here because PHP
		// automatically converts (string)'1' to (int)1 before sending it in to the function
		
		$data = array(
				'A'=>array(   'X'=>array(	
								    'K'=>(string)"foo", // Invalid L1 key
								    2=>array(null, true, false, 1, 1.0, "foo"),								    			   
								    3=>'bar' 
					    ),		
					    'R'=>array() // Drop this L3 node
				)		    
		);
		
		try {			
			$this->cls->replaceL3_multi($data, $ctrl);
			
			// Execution will halt on the previous line if replaceL3_multi() throws an exception
			$this->fail("Method replaceL3_multi failed to throw an exception on invalid L1 key");			
		}
		catch (FOX_exception $child) {

		}				
		
		// Invalid L2 key data type
		// ####################################################################
		
		$data = array(
				'A'=>array(   'X'=>array(	
								    1=>(string)"foo", 
								    2=>array(null, true, false, 1, 1.0, "foo")											    
					    ),				
					    99=>array(  // Invalid L2 key	
								    1=>(string)"foo", 
								    2=>array(null, true, false, 1, 1.0, "foo")								   			    

					    ),
					    'R'=>array() // Drop this L3 node
				)		    
		);
		
		try {			
			$this->cls->replaceL3_multi($data, $ctrl);
			
			// Execution will halt on the previous line if replaceL3_multi() throws an exception
			$this->fail("Method replaceL3_multi failed to throw an exception on invalid L3 key");			
		}
		catch (FOX_exception $child) {

		}	
			
		// Invalid L3 key data type
		// ####################################################################
		
		// NOTE: we can't do the '1'=> "fails on string" key data type test here because PHP
		// automatically converts (string)'1' to (int)1 before sending it in to the function
		
		$data = array(
				'A'=>array(   'X'=>array( 
								    1=>(string)"foo", 
								    2=>array(null, true, false, 1, 1.0, "foo")								    			    
					    )			    
				),
				// Invalid L3 key
				1=>array( 'X'=>array(	
								    1=>(string)"foo", 
								    2=>array(null, true, false, 1, 1.0, "foo")								    			    
					    )			    
				)		    
		);
		
		try {			
			$this->cls->replaceL3_multi($data, $ctrl);
			
			// Execution will halt on the previous line if replaceL3_multi() throws an exception
			$this->fail("Method replaceL3_multi failed to throw an exception on invalid L4 key");			
		}
		catch (FOX_exception $child) {

		}		
		
	    
	}			
	
	
	function tearDown() {
	   
		$this->cls = new FOX_dataStore_paged_L3_tester_replaceMethods();
		$unistall_ok = $this->cls->uninstall();
		
		$this->assertEquals(true, $unistall_ok);
		
		parent::tearDown();
	}

    
}

?>