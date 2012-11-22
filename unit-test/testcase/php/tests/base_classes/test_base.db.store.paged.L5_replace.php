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

class FOX_dataStore_paged_L5_tester_replaceMethods extends FOX_dataStore_paged_L5_base {
    

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
	
	
}  // ENDOF: class FOX_dataStore_paged_L5_tester_replaceMethods 

                                      

/**
 * FOXFIRE UNIT TEST SCRIPT - L5 PAGED ABSTRACT DATASTORE CLASS - REPLACE METHODS
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

class core_L5_paged_abstract_replaceMethods extends RAZ_testCase {

	
    	function setUp() {

	    
		parent::setUp();
				
		
		// Install the db table
		// ===========================================
		
		$this->cls = new FOX_dataStore_paged_L5_tester_replaceMethods();
		
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
	* Test fixture for replaceL2_multi() method, cold cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_replaceL2_multi_COLD() {
	    

		self::loadData();
		
		try {
			$flush_ok = $this->cls->flushCache();
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
				
		$this->assertEquals(true, $flush_ok);		
				
		
		// COLD CACHE - Flushed after previous ADD operation
		// ===================================================================				
		
		// NOTE: a L2 must have at least *one* L1 within it in order to have an entry within the DB. If 
		// a L2 doesn't have L1's inside it, there's nothing to write to the L1 column in the database
		// (which would violate the table index). In addition to this, storing empty L2's would waste
		// space in the table and cache. Therefore, overwriting a L2 node with an empty array drops
		// that node from the datastore.
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$data = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>'foo', // 1=>null,
											  // 2=>false,
										7=>'bar'
								    ),
								    'T'=>array(),	  // Drop this L2
								    'W'=>array(	1=>'baz' )					    
							)
					    ),

					    // Ignore the entire L4 'Y' node
					    // 'Y'=>array( ... ),

					    // Add a new L4 'E' node
					    'E'=>array(	'K'=>array( 'K'=>array(	
										1=>(int)1,
										2=>(int)-1,
										3=>true,
										5=>false
								    ),
								    'T'=>array(	4=>null)							    
							),
							'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )	
				),

				// Ignore the entire L5 '2' node
				// 2=>array( ... ),

				// Add a new L5 '3' node
				3=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
				)		    
		);
		
		
		// Replace items
		// ==============================
		
		$ctrl = array(		    
				'validate'=>false		    
		);
				
		try {			
			$this->cls->replaceL2_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
		// Check cache state
		// ==============================			
		
		// The LUT's will be set for all L2 items that we modified, since, by overwriting an
		// entire L2 item, we've given it authority. The other LUT arrays won't exist in the cache
		// yet because we haven't done a read.
		
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_1 = array(	    'L2'=>array(    'X'=>array( 'K'=>array(
										    'K'=>true,
										    'W'=>true
									)
							    ),
							    'E'=>array( 'K'=>array(
										    'K'=>true,
										    'T'=>true
									),
									'Z'=>array( 'Z'=>true )									
							    ),							
					    ),
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>'foo', 
												7=>'bar'
										    ),
										    'W'=>array(	1=>'baz' )							    
									)						
							    ),	
							    'E'=>array(	'K'=>array( 'K'=>array(	
												1=>(int)1,
												2=>(int)-1,
												3=>true,
												5=>false
										    ),
										    'T'=>array(	4=>null)							    
									),
									'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
							    )					    
					    )
		);
		
		$this->assertEquals($check_cache_1, $this->cls->cache[1]);	
		
		
		$check_cache_3 = array(	    'L2'=>array(    'X'=>array( 'K'=>array( 'K'=>true ),
										'Z'=>array( 'Z'=>true )
					    )),			    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_3, $this->cls->cache[3]);
		
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be in the cache.
		// ====================================================================
		
		$check_cache = array(		    
					1=>$check_cache_1,
					3=>$check_cache_3,		    
		    
		);		
		
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_1, $check_cache_3, $check_cache);
	
		
		// Fetch updated items
		// ==============================
		
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
		
		$this->assertEquals(true, $valid);						
		
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_data_1 = array(	    'X'=>array(	'K'=>array( 'K'=>array(	
										1=>'foo', 
										7=>'bar'
								    ),
								    'W'=>array(	1=>'baz' )							    
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
					    ),
					    'E'=>array(	'K'=>array( 'K'=>array(	
										1=>(int)1,
										2=>(int)-1,
										3=>true,
										5=>false
								    ),
								    'T'=>array(	4=>null)							    
							),
							'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )					    
		);
		
		$this->assertEquals($check_data_1, $result[1]);		
		
		
		$check_data_2 = array(	    'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
		);
		
		$this->assertEquals($check_data_2, $result[2]);	
		
		
		$check_data_3 = array(	    'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
		);
		
		$this->assertEquals($check_data_3, $result[3]);	
		
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there
		// ====================================================================
		
		$check_data = array(
					1=>$check_data_1,
					2=>$check_data_2,
					3=>$check_data_3,		    		    
		);
			
		$this->assertEquals($check_data, $result);
		
		unset($check_data_1, $check_data_2, $check_data_3, $check_data);		

		
		// Check cache state
		// ==============================					
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_1 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>'foo', 
												7=>'bar'
										    ),
										    'W'=>array(	1=>'baz' )							    
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
							    ),
							    'E'=>array(	'K'=>array( 'K'=>array(	
												1=>(int)1,
												2=>(int)-1,
												3=>true,
												5=>false
										    ),
										    'T'=>array(	4=>null)							    
									),
									'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
							    )					    
					    )
		);
		
		$this->assertEquals($check_cache_1, $this->cls->cache[1]);
		
		$check_cache_2 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,				    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_2, $this->cls->cache[2]);		
		
		$check_cache_3 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,				    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_3, $this->cls->cache[3]);
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there
		// ====================================================================
		
		$check_cache = array(		    
					1=>$check_cache_1,
					2=>$check_cache_2,
					3=>$check_cache_3,		    		    
		);
		
		$this->assertEquals($check_cache, $this->cls->cache);
		
		unset($check_cache_1, $check_cache_2, $check_cache_3, $check_cache);
			
		
	}
	
	
       /**
	* Test fixture for replaceL2_multi() method, warm cache
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
		
		// NOTE: a L2 must have at least *one* L1 within it in order to have an entry within the DB. If 
		// a L2 doesn't have L1's inside it, there's nothing to write to the L1 column in the database
		// (which would violate the table index). In addition to this, storing empty L2's would waste
		// space in the table and cache. Therefore, overwriting a L2 node with an empty array drops
		// that node from the datastore.
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";		
		
		$data = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>'foo', // 1=>null,
											  // 2=>false,
										7=>'bar'
								    ),
								    'T'=>array(),	  // Drop this L2
								    'W'=>array(	1=>'baz' )					    
							)
					    ),

					    // Ignore the entire L4 'Y' node
					    // 'Y'=>array( ... ),

					    // Add a new L4 'E' node
					    'E'=>array(	'K'=>array( 'K'=>array(	
										1=>(int)1,
										2=>(int)-1,
										3=>true,
										5=>false
								    ),
								    'T'=>array(	4=>null)							    
							),
							'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )	
				),

				// Ignore the entire L5 '2' node
				// 2=>array( ... ),

				// Add a new L5 '3' node
				3=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
				)		    
		);
		
		
		// Replace items
		// ==============================
		
		$ctrl = array(		    
				'validate'=>false		    
		);
				
		try {			
			$this->cls->replaceL2_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
		// Check cache state
		// ==============================			
		
		// The LUT's will be set for all L2 items that we modified, since, by overwriting an
		// entire L2 item, we've given it authority. The other LUT arrays won't exist in the cache
		// yet because we haven't done a read.
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_1 = array(	    'L2'=>array(    'X'=>array( 'K'=>array(
										    'K'=>true,
										    'W'=>true
									)
							    ),				   
							    'E'=>array( 'K'=>array(
										    'K'=>true,
										    'T'=>true
									),
									'Z'=>array( 'Z'=>true )									
							    ),							
					    ),
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>'foo', 
												7=>'bar'
										    ),
										    'W'=>array(	1=>'baz' )							    
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
							    ),
							    'E'=>array(	'K'=>array( 'K'=>array(	
												1=>(int)1,
												2=>(int)-1,
												3=>true,
												5=>false
										    ),
										    'T'=>array(	4=>null)							    
									),
									'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
							    )					    
					    )
		);
		
		$this->assertEquals($check_cache_1, $this->cls->cache[1]);
		
		
		$check_cache_2 = array(	    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_2, $this->cls->cache[2]);
		
		
		$check_cache_3 = array(	    'L2'=>array(    'X'=>array( 'K'=>array( 'K'=>true ),
									'Z'=>array( 'Z'=>true )
					    )),			    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);				
				
		$this->assertEquals($check_cache_3, $this->cls->cache[3]);
		
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there
		// ====================================================================
		
		$check_cache = array(		    
					1=>$check_cache_1,
					2=>$check_cache_2,
					3=>$check_cache_3,		    		    
		);
		
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_1, $check_cache_2, $check_cache_3, $check_cache);
	
		
		// Fetch updated items
		// ==============================
		
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
		
		$this->assertEquals(true, $valid);						
		
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_data_1 = array(	    'X'=>array(	'K'=>array( 'K'=>array(	
										1=>'foo', 
										7=>'bar'
								    ),
								    'W'=>array(	1=>'baz' )							    
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
					    ),
					    'E'=>array(	'K'=>array( 'K'=>array(	
										1=>(int)1,
										2=>(int)-1,
										3=>true,
										5=>false
								    ),
								    'T'=>array(	4=>null)							    
							),
							'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )					    
		);
		
		$check_data_2 = array(	    'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
		);
		
		$check_data_3 = array(	    'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
		);
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there
		// ====================================================================
		
		$check_data = array(
					1=>$check_data_1,
					2=>$check_data_2,
					3=>$check_data_3,		    
		);
			
		$this->assertEquals($check_data, $result);
		
		unset($check_data_1, $check_data_2, $check_data_3, $check_data);

		
		// Check cache state
		// ==============================			
		
		// The LUT's will now be set for all items that we requested in the previous GET operation
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_1 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>'foo', 
												7=>'bar'
										    ),
										    'W'=>array(	1=>'baz' )							    
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
							    ),
							    'E'=>array(	'K'=>array( 'K'=>array(	
												1=>(int)1,
												2=>(int)-1,
												3=>true,
												5=>false
										    ),
										    'T'=>array(	4=>null)							    
									),
									'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
							    )					    
					    )
		);
		
		$this->assertEquals($check_cache_1, $this->cls->cache[1]);
		
		$check_cache_2 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,				    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_2, $this->cls->cache[2]);
		
		$check_cache_3 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,				    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_3, $this->cls->cache[3]);		
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there
		// ====================================================================
		
		$check_cache = array(		    
					1=>$check_cache_1,
					2=>$check_cache_2,	
					3=>$check_cache_3,			    		    
		);
		
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_1, $check_cache_2, $check_cache_3, $check_cache);		
			
		
	}
	
	
       /**
	* Test fixture for replaceL2_multi() method, hot cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_replaceL2_multi_HOT() {
	    

		self::loadData();
		
		// Load updated items
		// ==============================
		
		$request = array(
				    1=>array(),
				    2=>array()	    
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
		
		// NOTE: a L2 must have at least *one* L1 within it in order to have an entry within the DB. If 
		// a L2 doesn't have L1's inside it, there's nothing to write to the L1 column in the database
		// (which would violate the table index). In addition to this, storing empty L2's would waste
		// space in the table and cache. Therefore, overwriting a L2 node with an empty array drops
		// that node from the datastore.
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";		
		
		$data = array(
				1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>'foo', // 1=>null,
											  // 2=>false,
										7=>'bar'
								    ),
								    'T'=>array(),	  // Drop this L2
								    'W'=>array(	1=>'baz' )					    
							)
					    ),

					    // Ignore the entire L4 'Y' node
					    // 'Y'=>array( ... ),

					    // Add a new L4 'E' node
					    'E'=>array(	'K'=>array( 'K'=>array(	
										1=>(int)1,
										2=>(int)-1,
										3=>true,
										5=>false
								    ),
								    'T'=>array(	4=>null)							    
							),
							'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )	
				),

				// Ignore the entire L5 '2' node
				// 2=>array( ... ),

				// Add a new L5 '3' node
				3=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
				)		    
		);
		
		
		// Replace items
		// ==============================
		
		$ctrl = array(		    
				'validate'=>false		    
		);
				
		try {			
			$this->cls->replaceL2_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
		// Check cache state
		// ==============================			
		
		// The LUT's will be set for all L2 items that we modified, since, by overwriting an
		// entire L2 item, we've given it authority. The other LUT arrays won't exist in the cache
		// yet because we haven't done a read.
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
				
		$check_cache_1 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,					    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>'foo', 
												7=>'bar'
										    ),
										    'W'=>array(	1=>'baz' )							    
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
							    ),
							    'E'=>array(	'K'=>array( 'K'=>array(	
												1=>(int)1,
												2=>(int)-1,
												3=>true,
												5=>false
										    ),
										    'T'=>array(	4=>null)							    
									),
									'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
							    )					    
					    )
		);
		
		$this->assertEquals($check_cache_1, $this->cls->cache[1]);			
		
		$check_cache_2 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,						    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_2, $this->cls->cache[2]);			
		
		$check_cache_3 = array(   
					    'L2'=>array(    'X'=>array( 'K'=>array( 'K'=>true ),
									'Z'=>array( 'Z'=>true )
					    )),			    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_3, $this->cls->cache[3]);		
		
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there
		// ====================================================================
		
		$check_cache = array(		    
					1=>$check_cache_1,
					2=>$check_cache_2,
					3=>$check_cache_3,		    		    
		);		
		
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_1, $check_cache_2, $check_cache_3, $check_cache);
	
		
		// Load updated items
		// ==============================
		
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
		
		$this->assertEquals(true, $valid);						
		
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_data_1 = array(	    'X'=>array(	'K'=>array( 'K'=>array(	
										1=>'foo', 
										7=>'bar'
								    ),
								    'W'=>array(	1=>'baz' )							    
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
					    ),
					    'E'=>array(	'K'=>array( 'K'=>array(	
										1=>(int)1,
										2=>(int)-1,
										3=>true,
										5=>false
								    ),
								    'T'=>array(	4=>null)							    
							),
							'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )					    
		);
		
		$this->assertEquals($check_data_1, $result[1]);
		
		$check_data_2 = array(	    'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
		);
		
		$this->assertEquals($check_data_2, $result[2]);
		
		$check_data_3 = array(	    'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
		);
		
		$this->assertEquals($check_data_3, $result[3]);
		
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there
		// ====================================================================
		
		$check_data = array(
					1=>$check_data_1,
					2=>$check_data_2,	
					3=>$check_data_3,			    
		);
			
		$this->assertEquals($check_data, $result);	
		
		unset($check_data_1, $check_data_2, $check_data_3, $check_data);

		
		// Check cache state
		// ==============================			
		
		// The LUT's will now be set for all items that we requested in the previous GET operation
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_1 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>'foo', 
												7=>'bar'
										    ),
										    'W'=>array(	1=>'baz' )							    
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
							    ),
							    'E'=>array(	'K'=>array( 'K'=>array(	
												1=>(int)1,
												2=>(int)-1,
												3=>true,
												5=>false
										    ),
										    'T'=>array(	4=>null)							    
									),
									'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
							    )					    
					    )
		);
		
		$this->assertEquals($check_cache_1, $this->cls->cache[1]);
		
		$check_cache_2 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,				    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_2, $this->cls->cache[2]);
		
		$check_cache_3 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,				    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_3, $this->cls->cache[3]);		
		
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there
		// ====================================================================
		
		$check_cache = array(		    
					1=>$check_cache_1,
					2=>$check_cache_2,
					3=>$check_cache_3,		    		    
		);
		
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_1, $check_cache_2, $check_cache_3, $check_cache);
			
		
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
		
		try {
			$flush_ok = $this->cls->flushCache();
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
				
		$this->assertEquals(true, $flush_ok);		
				
		
		// COLD CACHE - Flushed after previous ADD operation
		// ===================================================================				
		
		// NOTE: a L3 must have at least *one* L2->L1 walk within it in order to have an entry within the  
		// db. If a L3->L1 walk inside it, there's nothing to write to the L1 and L2 columns in the db
		// (which would violate the table index). In addition to this, storing empty L3's would waste
		// space in the table and cache. Therefore, overwriting a L3 node with an empty array drops
		// that node from the datastore.
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$data = array(
				1=>array(   'X'=>array(	'K'=>array(), // Drop this L3
							'R'=>array( 'X'=>array(	
										9=>'foo',
										3=>'bar'
								    )					    
							),				    
							'V'=>array( 'K'=>array(	
										1=>'foo',
										7=>'bar'
								    ),
								    'W'=>array(	1=>'baz' )					    
							)
				    
							// Ignore the entire L3 'Z' node
							// 'Z'=>array( ... )
					    ),

					    // Ignore the entire L4 'Y' node
					    // 'Y'=>array( ... ),

					    // Add a new L4 'E' node
					    'E'=>array(	'K'=>array( 'K'=>array(	
										1=>(int)1,
										2=>(int)-1,
										3=>true,
										5=>false
								    ),
								    'T'=>array(	4=>null)							    
							),
							'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )	
				),

				// Ignore the entire L5 '2' node
				// 2=>array( ... ),

				// Add a new L5 '3' node
				3=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
				)		    
		);
		
		
		// Replace items
		// ==============================
		
		$ctrl = array(		    
				'validate'=>false		    
		);
				
		try {			
			$this->cls->replaceL3_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
		// Check cache state
		// ==============================			
		
		// The LUT's will be set for all L3 items that we modified, since, by overwriting an
		// entire L3 item, we've given it authority. The other LUT arrays won't exist in the cache
		// yet because we haven't done a read.
		
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_1 = array(	    'L3'=>array(    'X'=>array( 
									'R'=>true,
									'V'=>true
							    ),
							    'E'=>array( 
									'K'=>true,
									'Z'=>true
							    ),					    						
					    ),
					    'keys'=>array(  'X'=>array(	'R'=>array( 'X'=>array(	
												9=>'foo',
												3=>'bar'
										    )					    
									),				    
									'V'=>array( 'K'=>array(	
												1=>'foo',
												7=>'bar'
										    ),
										    'W'=>array( 1=>'baz' )					    
									)
							    ),
							    'E'=>array(	'K'=>array( 'K'=>array(	
												1=>(int)1,
												2=>(int)-1,
												3=>true,
												5=>false
										    ),
										    'T'=>array(	4=>null)							    
									),
									'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
							    )							
					    )				
		);
		
		$this->assertEquals($check_cache_1, $this->cls->cache[1]);		
		
		$check_cache_3 = array(	    'L3'=>array(    'X'=>array( 'K'=>true,
										'Z'=>true
					    )),			    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_3, $this->cls->cache[3]);
		
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be in the cache.
		// ====================================================================
		
		$check_cache = array(	
					1=>$check_cache_1,
					3=>$check_cache_3		    
		);		
	
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_1, $check_cache_3, $check_cache);
		
		
		// Load updated items
		// ==============================
		
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
		
		$this->assertEquals(true, $valid);						
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_data_1 = array(  'X'=>array( 'R'=>array( 'X'=>array(	
									    9=>'foo',
									    3=>'bar'
								)					    
						    ),				    
						    'V'=>array( 'K'=>array(	
									    1=>'foo',
									    7=>'bar'
								),
								'W'=>array( 1=>'baz' )					    
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
					),					    
					'E'=>array(	'K'=>array( 'K'=>array(	
									    1=>(int)1,
									    2=>(int)-1,
									    3=>true,
									    5=>false
								),
								'T'=>array(	4=>null)							    
						    ),
						    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					)							
		);
		
		$this->assertEquals($check_data_1, $result[1]);	
		
		$check_data_2 = array(	'X'=>array( 'K'=>array( 'K'=>array(	
									    1=>(string)"foo",
									    2=>array(null, true, false, 1, 1.0, "foo")
								)							    
						    ),
						    'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					)					    
		);
		
		$this->assertEquals($check_data_2, $result[2]);
		
		$check_data_3 = array(  'X'=>array( 'K'=>array( 'K'=>array(	
									    1=>(string)"foo",
									    2=>array(null, true, false, 1, 1.0, "foo")
								)							    
						    ),
						    'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					)					    
		);
		
		$this->assertEquals($check_data_3, $result[3]);		
		
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there
		// ====================================================================
		
		$check_data = array(
					1=>$check_data_1,
					2=>$check_data_2,
					3=>$check_data_3,		    
		);
			
		$this->assertEquals($check_data, $result);	
		
		unset($check_data_1, $check_data_2, $check_data_3, $check_data);

		
		// Check cache state
		// ==============================			
				
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_1 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,
					    'keys'=>array(  'X'=>array(	'R'=>array( 'X'=>array(	
												9=>'foo',
												3=>'bar'
										    )					    
									),				    
									'V'=>array( 'K'=>array(	
												1=>'foo',
												7=>'bar'
										    ),
										    'W'=>array( 1=>'baz' )					    
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
							    ),					    
							    'E'=>array(	'K'=>array( 'K'=>array(	
												1=>(int)1,
												2=>(int)-1,
												3=>true,
												5=>false
										    ),
										    'T'=>array(	4=>null)							    
									),
									'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
							    )							
					    )
		);
		
		$this->assertEquals($check_cache_1, $this->cls->cache[1]);
		
		$check_cache_2 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,				    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_2, $this->cls->cache[2]);
		
		$check_cache_3 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,				    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_3, $this->cls->cache[3]);	
		
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there
		// ====================================================================
		
		$check_cache = array(		    
					1=>$check_cache_1,
					2=>$check_cache_2,		    
					3=>$check_cache_3,		    
		);
		
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_1, $check_cache_2, $check_cache_3, $check_cache);
			
		
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
		
		// NOTE: a L2 must have at least *one* L1 within it in order to have an entry within the DB. If 
		// a L2 doesn't have L1's inside it, there's nothing to write to the L1 column in the database
		// (which would violate the table index). In addition to this, storing empty L2's would waste
		// space in the table and cache. Therefore, overwriting a L2 node with an empty array drops
		// that node from the datastore.
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";		
		
		$data = array(
				1=>array(   'X'=>array(	'K'=>array(), // Drop this L3
							'R'=>array( 'X'=>array(	
										9=>'foo',
										3=>'bar'
								    )					    
							),				    
							'V'=>array( 'K'=>array(	
										1=>'foo',
										7=>'bar'
								    ),
								    'W'=>array(	1=>'baz' )					    
							)
				    
							// Ignore the entire L3 'Z' node
							// 'Z'=>array( ... )
					    ),

					    // Ignore the entire L4 'Y' node
					    // 'Y'=>array( ... ),

					    // Add a new L4 'E' node
					    'E'=>array(	'K'=>array( 'K'=>array(	
										1=>(int)1,
										2=>(int)-1,
										3=>true,
										5=>false
								    ),
								    'T'=>array(	4=>null)							    
							),
							'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )	
				),

				// Ignore the entire L5 '2' node
				// 2=>array( ... ),

				// Add a new L5 '3' node
				3=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
				)		    
		);
		
		
		// Replace items
		// ==============================
		
		$ctrl = array(		    
				'validate'=>false		    
		);
				
		try {			
			$this->cls->replaceL3_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
		// Check cache state
		// ==============================			
		
		// The LUT's will be set for all L3 items that we modified, since, by overwriting an
		// entire L3 item, we've given it authority. The other LUT arrays won't exist in the cache
		// yet because we haven't done a read.
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_1 = array(	    'L3'=>array(    'X'=>array( 
									'R'=>true,
									'V'=>true
							    ),
							    'E'=>array( 
									'K'=>true,
									'Z'=>true
							    ),					    						
					    ),
					    'keys'=>array(  'X'=>array(	'R'=>array( 'X'=>array(	
												9=>'foo',
												3=>'bar'
										    )					    
									),				    
									'V'=>array( 'K'=>array(	
												1=>'foo',
												7=>'bar'
										    ),
										    'W'=>array( 1=>'baz' )					    
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
							    ),					    
							    'E'=>array(	'K'=>array( 'K'=>array(	
												1=>(int)1,
												2=>(int)-1,
												3=>true,
												5=>false
										    ),
										    'T'=>array(	4=>null)							    
									),
									'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
							    )							
					    )
		);
		
		$this->assertEquals($check_cache_1, $this->cls->cache[1]);
		
		$check_cache_2 = array(	    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_2, $this->cls->cache[2]);
		
		$check_cache_3 = array(	    'L3'=>array(    'X'=>array( 'K'=>true,
										    'Z'=>true
					    )),				    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_3, $this->cls->cache[3]);
		
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there
		// ====================================================================
		
		$check_cache = array(		    
					1=>$check_cache_1,	
		    			2=>$check_cache_2,
					3=>$check_cache_3		    		    
		);	

		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_1, $check_cache_2, $check_cache_3, $check_cache);

		
		// Load updated items
		// ==============================
		
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
		
		$this->assertEquals(true, $valid);						
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_data_1 = array(	    'X'=>array(	'R'=>array( 'X'=>array(	
										9=>'foo',
										3=>'bar'
								    )					    
							),				    
							'V'=>array( 'K'=>array(	
										1=>'foo',
										7=>'bar'
								    ),
								    'W'=>array( 1=>'baz' )					    
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
					    ),					    
					    'E'=>array(	'K'=>array( 'K'=>array(	
										1=>(int)1,
										2=>(int)-1,
										3=>true,
										5=>false
								    ),
								    'T'=>array(	4=>null)							    
							),
							'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )							
		);
		
		$check_data_2 = array(	    'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
		);
		
		$check_data_3 = array(	    'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
		);
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there
		// ====================================================================
		
		$check_data = array(
					1=>$check_data_1,
					2=>$check_data_2,
					3=>$check_data_3,		    
		);
			
		$this->assertEquals($check_data, $result);	
		
		unset($check_data_1, $check_data_2,$check_data_3, $check_data);

		
		// Check cache state
		// ==============================			
		
		// The LUT's will now be set for all items that we requested in the previous GET operation
		
		
		$check_cache_1 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,
					    'keys'=>array(  'X'=>array(	'R'=>array( 'X'=>array(	
												9=>'foo',
												3=>'bar'
										    )					    
									),				    
									'V'=>array( 'K'=>array(	
												1=>'foo',
												7=>'bar'
										    ),
										    'W'=>array( 1=>'baz' )					    
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
							    ),					    
							    'E'=>array(	'K'=>array( 'K'=>array(	
												1=>(int)1,
												2=>(int)-1,
												3=>true,
												5=>false
										    ),
										    'T'=>array(	4=>null)							    
									),
									'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
							    )							
					    )
		);
		
		$this->assertEquals($check_cache_1, $this->cls->cache[1]);
		
		$check_cache_2 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,				    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_2, $this->cls->cache[2]);
		
		$check_cache_3 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,				    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_3, $this->cls->cache[3]);		
		
		$check_cache = array(		    
					1=>$check_cache_1,
					2=>$check_cache_2,	
					3=>$check_cache_3			    		    
		);
		
		$this->assertEquals($check_cache, $this->cls->cache);
		
		unset($check_cache_1, $check_cache_2, $check_cache_3, $check_cache);
			
		
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
		
		// Load updated items
		// ==============================
		
		$request = array(
				    1=>array(),
				    2=>array()	    
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
		
		// NOTE: a L3 must have at least *one* L2 within it in order to have an entry within the DB. If 
		// a L3 doesn't have L2's inside it, there's nothing to write to the L1 column in the database
		// (which would violate the table index). In addition to this, storing empty L3's would waste
		// space in the table and cache. Therefore, overwriting a L3 node with an empty array drops
		// that node from the datastore.
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";		
		
		$data = array(
				1=>array(   'X'=>array(	'K'=>array(), // Drop this L3
							'R'=>array( 'X'=>array(	
										9=>'foo',
										3=>'bar'
								    )					    
							),				    
							'V'=>array( 'K'=>array(	
										1=>'foo',
										7=>'bar'
								    ),
								    'W'=>array(	1=>'baz' )					    
							)
				    
							// Ignore the entire L3 'Z' node
							// 'Z'=>array( ... )
					    ),

					    // Ignore the entire L4 'Y' node
					    // 'Y'=>array( ... ),

					    // Add a new L4 'E' node
					    'E'=>array(	'K'=>array( 'K'=>array(	
										1=>(int)1,
										2=>(int)-1,
										3=>true,
										5=>false
								    ),
								    'T'=>array(	4=>null)							    
							),
							'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )	
				),

				// Ignore the entire L5 '2' node
				// 2=>array( ... ),

				// Add a new L5 '3' node
				3=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )					    
				)		    
		);
		
		
		// Replace items
		// ==============================
		
		$ctrl = array(		    
				'validate'=>false		    
		);
				
		try {			
			$this->cls->replaceL3_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
		// Check cache state
		// ==============================			
				
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_1 = array(	    'all_cached'=>true,	    // $all_cached will be true because this L5 had
					    'L4'=>null,		    // authority from the previous GET operation
					    'L3'=>null,
					    'L2'=>null,
					    'keys'=>array(  'X'=>array(	'R'=>array( 'X'=>array(	
												9=>'foo',
												3=>'bar'
										    )					    
									),				    
									'V'=>array( 'K'=>array(	
												1=>'foo',
												7=>'bar'
										    ),
										    'W'=>array( 1=>'baz' )					    
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
							    ),					    
							    'E'=>array(	'K'=>array( 'K'=>array(	
												1=>(int)1,
												2=>(int)-1,
												3=>true,
												5=>false
										    ),
										    'T'=>array(	4=>null)							    
									),
									'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
							    )							
					    )
		);
		
		$this->assertEquals($check_cache_1, $this->cls->cache[1]);
		
		
		$check_cache_2 = array(	    'all_cached'=>true,	    // $all_cached will be true because this L5 had
					    'L4'=>null,		    // authority from the previous GET operation
					    'L3'=>null,
					    'L2'=>null,				    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_2, $this->cls->cache[2]);
		

		$check_cache_3 = array(	    'L3'=>array(    'X'=>array( 'K'=>true,	// This L5 didn't exist when the GET operation ran. It
									'Z'=>true	// only has authority in the L3 LUT, because the L5 was
					    )),						// created during a L3 replace operation.			    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_3, $this->cls->cache[3]);
		
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there
		// ====================================================================
		
		$check_cache = array(		    
					1=>$check_cache_1,
					2=>$check_cache_2,
					3=>$check_cache_3,		    
		);
		
		unset($check_cache_1, $check_cache_2, $check_cache_3, $check_cache);
		
		
		// Load updated items
		// ==============================
		
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
		
		$this->assertEquals(true, $valid);						
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_data_1 = array(	    'X'=>array(	'R'=>array( 'X'=>array(	
										9=>'foo',
										3=>'bar'
								    )					    
							),				    
							'V'=>array( 'K'=>array(	
										1=>'foo',
										7=>'bar'
								    ),
								    'W'=>array( 1=>'baz' )					    
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
					    ),					    
					    'E'=>array(	'K'=>array( 'K'=>array(	
										1=>(int)1,
										2=>(int)-1,
										3=>true,
										5=>false
								    ),
								    'T'=>array(	4=>null)							    
							),
							'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
					    )					    
		);
		
		$this->assertEquals($check_data_1, $result[1]);
		
		$check_data_2 = array(	    'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )							    
		);
		
		$this->assertEquals($check_data_2, $result[2]);
		
		$check_data_3 = array(	    'X'=>array(	'K'=>array( 'K'=>array(	
										1=>(string)"foo",
										2=>array(null, true, false, 1, 1.0, "foo")
								    )							    
							),
							'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
					    )						    
		);
		
		$this->assertEquals($check_data_3, $result[3]);
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there
		// ====================================================================
		
		$check_data = array(
					1=>$check_data_1,	
		    			2=>$check_data_2,
					3=>$check_data_3		    
		);
			
		$this->assertEquals($check_data, $result);
		
		unset($check_data_1, $check_data_2, $check_data_3, $check_data);
		

		// Check cache state
		// ==============================			
		
		// The LUT's will now be set for all items that we requested in the previous GET operation
		
		// PASS 1: Check the L5 nodes individually to simplify debugging
		// ====================================================================
		
		$check_cache_1 = array(	    'all_cached'=>true,	    // $all_cached will be true because this L5 had
					    'L4'=>null,		    // authority from the previous GET operation
					    'L3'=>null,
					    'L2'=>null,
					    'keys'=>array(  'X'=>array(	'R'=>array( 'X'=>array(	
												9=>'foo',
												3=>'bar'
										    )					    
									),				    
									'V'=>array( 'K'=>array(	
												1=>'foo',
												7=>'bar'
										    ),
										    'W'=>array( 1=>'baz' )					    
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
							    ),					    
							    'E'=>array(	'K'=>array( 'K'=>array(	
												1=>(int)1,
												2=>(int)-1,
												3=>true,
												5=>false
										    ),
										    'T'=>array(	4=>null)							    
									),
									'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
							    )							
					    )
		);
		
		$this->assertEquals($check_cache_1, $this->cls->cache[1]);		
		
		$check_cache_2 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,				    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_2, $this->cls->cache[2]);
		
		$check_cache_3 = array(	    'all_cached'=>true,
					    'L4'=>null,
					    'L3'=>null,
					    'L2'=>null,				    
					    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    )							    
									),
									'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
							    )					    
					    )						
		);
		
		$this->assertEquals($check_cache_3, $this->cls->cache[3]);
		
		
		// PASS 2: Combine the L5 nodes into a single array and check it
		// again. This finds L5 keys that aren't supposed to be there
		// ====================================================================
		
		$check_cache = array(		    
					1=>$check_cache_1,
					2=>$check_cache_2,
					3=>$check_cache_3		    		    
		);
		
		$this->assertEquals($check_cache, $this->cls->cache);	
		
		unset($check_cache_1, $check_cache_2, $check_cache_3, $check_cache);
			
		
	}
	
	
	function tearDown() {
	   
		$this->cls = new FOX_dataStore_paged_L5_tester_replaceMethods();
		$unistall_ok = $this->cls->uninstall();
		
		$this->assertEquals(true, $unistall_ok);
		
		parent::tearDown();
	}

    
}

?>