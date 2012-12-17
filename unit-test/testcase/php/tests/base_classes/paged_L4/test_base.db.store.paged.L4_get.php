<?php

/**
 * L4 PAGED ABSTRACT DATASTORE TEST CLASS
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

class FOX_dataStore_paged_L4_tester_getMethods extends FOX_dataStore_paged_L4_base {
    

	public static $struct = array(

		"table" => "FOX_dataStore_paged_L4_base",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_dataStore_paged_L4_base",
		"cache_strategy" => "paged",
		"cache_engine" => array("memcached", "redis", "apc", "thread"),	    
		"columns" => array(
		    "L4" =>	array(	"php"=>"int",    "sql"=>"int",	"format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",		"auto_inc"=>false,  "default"=>null,
			// This forces every combination to be unique
			"index"=>array("name"=>"top_level_index",	"col"=>array("L4", "L3", "L2", "L1"), "index"=>"PRIMARY"), "this_row"=>true),
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
	
	
}  // ENDOF: class FOX_dataStore_paged_L4_tester_getMethods 

                                      

/**
 * FOXFIRE UNIT TEST SCRIPT - L4 PAGED ABSTRACT DATASTORE CLASS - GET METHODS
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

class core_L4_paged_abstract_getMethods extends RAZ_testCase {

	
    	function setUp() {

	    
		parent::setUp();
				
		
		// Install the db table
		// ===========================================
		
		$this->cls = new FOX_dataStore_paged_L4_tester_getMethods();
		
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
	* Test fixture for getMulti() method, trie mode, L5 trie depth
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getMulti_trie_L5() {
	    
		
		self::loadData();
				
		
		// STAGE 1 - WARM CACHE - Loaded from previous 'add' operation
		// ===================================================================
		
		$request = array(
				    1=>array(),
				    2=>array()		    		    
		);
		
		$ctrl = array(		    
				'validate'=>true,
				'q_mode'=>'trie',
				'r_mode'=>'trie',		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
	        
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check_data = array(
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
				
				
		$this->assertEquals($check_data, $result);	
		
		
		// Check cache state
		// ===============================================================			
		
		$check_cache = array(		    
					1=>array(   'all_cached'=>true,
						    'L4'=>null,
						    'L3'=>null,
						    'L2'=>null,
						    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
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
					2=>array(   'all_cached'=>true,
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
					)		    		    
		);
		
		$this->assertEquals($check_cache, $this->cls->cache);			
		
		
		// STAGE 2 - COLD CACHE - Flush cache and run again
		// ===================================================================
		
		$this->cls->flushCache();
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		

		$this->assertEquals($check_data, $result);		
		
		$this->assertEquals($check_cache, $this->cls->cache);
						
		
		// STAGE 3 - HOT CACHE - Loaded, with authority, by previous call
		// ===================================================================		
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		

		$this->assertEquals($check_data, $result);	
		
		$this->assertEquals($check_cache, $this->cls->cache);		
		
	}
	
	
       /**
	* Test fixture for getMulti() method, matrix mode, L5 trie depth
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getMulti_matrix_L5() {
	    
		
		self::loadData();
				
		
		// STAGE 1 - WARM CACHE - Loaded from previous 'add' operation
		// ===================================================================
		
		$request = array(
				array( "L5"=>1),
				array( "L5"=>2),		    		    
		);
		
		$ctrl = array(		    
				'validate'=>true,
				'q_mode'=>'matrix',
				'r_mode'=>'trie',		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(10));	
		}
		
		$this->assertEquals(true, $valid);		
		
	        
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check_data = array(
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
				
				
		$this->assertEquals($check_data, $result);	
		
		
		// Check cache state
		// ===============================================================			
		
		$check_cache = array(		    
					1=>array(   'all_cached'=>true,
						    'L4'=>null,
						    'L3'=>null,
						    'L2'=>null,
						    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
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
					2=>array(   'all_cached'=>true,
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
					)		    		    
		);
		
		$this->assertEquals($check_cache, $this->cls->cache);			
		
		
		// STAGE 2 - COLD CACHE - Flush cache and run again
		// ===================================================================
		
		$this->cls->flushCache();
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		

		$this->assertEquals(true, $valid);
		
		$this->assertEquals($check_data, $result);		
		
		$this->assertEquals($check_cache, $this->cls->cache);
						
		
		// STAGE 3 - HOT CACHE - Loaded, with authority, by previous call
		// ===================================================================		
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		

		$this->assertEquals($check_data, $result);	
		
		$this->assertEquals($check_cache, $this->cls->cache);		
		

	}
	

	
       /**
	* Test fixture for getMulti() method, trie mode, L4 trie depth
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getMulti_trie_L4() {
	    
		
		self::loadData();
				
		
		// STAGE 1 - WARM CACHE - Loaded from previous 'add' operation
		// ===================================================================
		
		$request = array(
				    1=>array('Y'=>true),
				    2=>array('X'=>true)		    		    
		);
		
		$ctrl = array(		    
				'validate'=>true,
				'q_mode'=>'trie',
				'r_mode'=>'trie',		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
	        
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check_data = array(
					1=>array(   'Y'=>array(	'K'=>array( 'K'=>array(	
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
				
				
		$this->assertEquals($check_data, $result);	
		
		
		// Check cache state
		// ===============================================================			
		
		$check_cache_warm = array(		    
					    1=>array(   'L4'=>array(	'Y'=>true),	// The L4 'Y' trie will have authority because
							'L3'=>null,			// we loaded it
							'L2'=>null,
							'keys'=>array(  
									// The L4 'X' trie will still be in the cache from the
									// add operation

									'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>null,
													    2=>false
												),
												'T'=>array(	1=>true )							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
									),	
									'Y'=>array( 'K'=>array( 'K'=>array(	
													    1=>(int)1,
													    2=>(int)-1
												),
												'T'=>array(	3=>(float)1.7 )							    
										    ),
										    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
									)
							)
					    ),			
					    2=>array(   'L4'=>array(	'X'=>true),	// The L4 'X' trie will have authority because
							'L3'=>null,			// we loaded it
							'L2'=>null,				    
							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>(string)"foo",
													    2=>array(null, true, false, 1, 1.0, "foo")
												)							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
									)	
							)						
					    )		    		    
		);
		
		$this->assertEquals($check_cache_warm, $this->cls->cache);			
		
		
		// STAGE 2 - COLD CACHE - Flush cache and run again
		// ===================================================================
												
		$this->cls->flushCache();
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}	
		
		$this->assertEquals(true, $valid);
		
		$this->assertEquals($check_data, $result);
	
		
		$check_cache_cold = array(		    
					    1=>array(   'L4'=>array(	'Y'=>true),	// The L4 'Y' trie will have authority because
							'L3'=>null,			// we loaded it
							'L2'=>null,
							'keys'=>array(  
									'Y'=>array( 'K'=>array( 'K'=>array(	
													    1=>(int)1,
													    2=>(int)-1
												),
												'T'=>array(	3=>(float)1.7 )							    
										    ),
										    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
									)
							)
					    ),			
					    2=>array(   'L4'=>array(	'X'=>true),	// The L4 'X' trie will have authority because
							'L3'=>null,			// we loaded it
							'L2'=>null,				    
							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>(string)"foo",
													    2=>array(null, true, false, 1, 1.0, "foo")
												)							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
									)	
							)						
					    )		    		    
		);
		
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);	
		
		
		// STAGE 3 - HOT CACHE - Loaded, with authority, by previous call
		// ===================================================================	
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$this->assertEquals(true, $valid);
		
		$this->assertEquals($check_data, $result);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);
		
		
	}	

	
       /**
	* Test fixture for getMulti() method, matrix mode, L4 trie depth
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getMulti_matrix_L4() {
	    
		
		self::loadData();
				
		
		// STAGE 1 - WARM CACHE - Loaded from previous 'add' operation
		// ===================================================================
		
		$request = array(
				array( "L5"=>1, "L4"=>"Y"),
				array( "L5"=>2, "L4"=>"X")		    		    
		);
		
		$ctrl = array(		    
				'validate'=>true,
				'q_mode'=>'matrix',
				'r_mode'=>'trie',		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
	        
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check_data = array(
					1=>array(   'Y'=>array(	'K'=>array( 'K'=>array(	
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
				
				
		$this->assertEquals($check_data, $result);	
		
		
		// Check cache state
		// ===============================================================			
		
		$check_cache_warm = array(		    
					    1=>array(   'L4'=>array(	'Y'=>true),	// The L4 'Y' trie will have authority because
							'L3'=>null,			// we loaded it
							'L2'=>null,
							'keys'=>array(  
									// The L4 'X' trie will still be in the cache from the
									// add operation

									'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>null,
													    2=>false
												),
												'T'=>array(	1=>true )							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
									),	
									'Y'=>array( 'K'=>array( 'K'=>array(	
													    1=>(int)1,
													    2=>(int)-1
												),
												'T'=>array(	3=>(float)1.7 )							    
										    ),
										    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
									)
							)
					    ),			
					    2=>array(   'L4'=>array(	'X'=>true),	// The L4 'X' trie will have authority because
							'L3'=>null,			// we loaded it
							'L2'=>null,				    
							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>(string)"foo",
													    2=>array(null, true, false, 1, 1.0, "foo")
												)							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
									)	
							)						
					    )		    		    
		);
		
		$this->assertEquals($check_cache_warm, $this->cls->cache);			
		
		
		// STAGE 2 - COLD CACHE - Flush cache and run again
		// ===================================================================
												
		$this->cls->flushCache();
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);
	
		
		$check_cache_cold = array(		    
					    1=>array(   'L4'=>array(	'Y'=>true),	// The L4 'Y' trie will have authority because
							'L3'=>null,			// we loaded it
							'L2'=>null,
							'keys'=>array(  
									'Y'=>array( 'K'=>array( 'K'=>array(	
													    1=>(int)1,
													    2=>(int)-1
												),
												'T'=>array(	3=>(float)1.7 )							    
										    ),
										    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
									)
							)
					    ),			
					    2=>array(   'L4'=>array(	'X'=>true),	// The L4 'X' trie will have authority because
							'L3'=>null,			// we loaded it
							'L2'=>null,				    
							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>(string)"foo",
													    2=>array(null, true, false, 1, 1.0, "foo")
												)							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
									)	
							)						
					    )		    		    
		);
		
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);	
		
		
		// STAGE 3 - HOT CACHE - Loaded, with authority, by previous call
		// ===================================================================	
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);
		
		
	}	
	
	
       /**
	* Test fixture for getMulti() method, trie mode, L3 trie depth
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getMulti_trie_L3() {
	    
		
		self::loadData();
				
		
		// STAGE 1 - WARM CACHE - Loaded from previous 'add' operation
		// ===================================================================
		
		$request = array(
				    1=>array('Y'=>array(
							'K'=>true,
							'Z'=>true
					    )),
				    2=>array('X'=>array('Z'=>true))		    		    
		);
		
		$ctrl = array(		    
				'validate'=>true,
				'q_mode'=>'trie',
				'r_mode'=>'trie',		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
	        
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check_data = array(
					1=>array(   'Y'=>array(	'K'=>array( 'K'=>array(	
											1=>(int)1,
											2=>(int)-1
									    ),
									    'T'=>array(	3=>(float)1.7 )							    
								),
								'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
						    )					    
					),			
					2=>array(   'X'=>array(	'Z'=>array( 'Z'=>array( 3=>$test_obj ))))		    		    
		);
				
				
		$this->assertEquals($check_data, $result);	
		
		
		// Check cache state
		// ===============================================================			
		
		$check_cache_warm = array(		    
					    1=>array(   'L4'=>null,
							'L3'=>array(	'Y'=>array( 'K'=>true, 
										    'Z'=>true
							)),
							'L2'=>null,
							'keys'=>array(  
									// The L4 'X' trie will still be in the cache from the
									// add operation

									'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>null,
													    2=>false
												),
												'T'=>array( 1=>true )							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
									),	
									'Y'=>array( 'K'=>array( 'K'=>array(	
													    1=>(int)1,
													    2=>(int)-1
												),
												'T'=>array( 3=>(float)1.7 )							    
										    ),
										    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
									)
							)
					    ),			
					    2=>array(   'L4'=>null,
							'L3'=>array(	'X'=>array(
										    'Z'=>true
							)),		
							'L2'=>null,				    
							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>(string)"foo",
													    2=>array(null, true, false, 1, 1.0, "foo")
												)							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
									)	
							)						
					    )		    		    
		);
		
		$this->assertEquals($check_cache_warm, $this->cls->cache);			
		
		
		// STAGE 2 - COLD CACHE - Flush cache and run again
		// ===================================================================
												
		$this->cls->flushCache();
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);

		
		$check_cache_cold = array(		    
					    1=>array(   'L4'=>null,
							'L3'=>array(	'Y'=>array( 'K'=>true, 
										    'Z'=>true
							)),
							'L2'=>null,
							'keys'=>array(  
									'Y'=>array( 'K'=>array( 'K'=>array(	
													    1=>(int)1,
													    2=>(int)-1
												),
												'T'=>array( 3=>(float)1.7 )							    
										    ),
										    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
									)
							)
					    ),			
					    2=>array(   'L4'=>null,
							'L3'=>array(	'X'=>array( 'Z'=>true )),		
							'L2'=>null,				    
							'keys'=>array(  'X'=>array( 'Z'=>array( 'Z'=>array( 3=>$test_obj ))))						
					    )		    		    
		);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);
		
		
		// STAGE 3 - HOT CACHE - Loaded, with authority, by previous call
		// ===================================================================	
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);		
		
		
	}
	
	
       /**
	* Test fixture for getMulti() method, matrix mode, L3 trie depth
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getMulti_matrix_L3() {
	    
		
		self::loadData();
				
		
		// STAGE 1 - WARM CACHE - Loaded from previous 'add' operation
		// ===================================================================
		
		$request = array(
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K"),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"Z"),
		    		array( "L5"=>2, "L4"=>"X", "L3"=>"Z")	
		    
		);
		
		$ctrl = array(		    
				'validate'=>true,
				'q_mode'=>'matrix',
				'r_mode'=>'trie',		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
	        
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check_data = array(
					1=>array(   'Y'=>array(	'K'=>array( 'K'=>array(	
											1=>(int)1,
											2=>(int)-1
									    ),
									    'T'=>array(	3=>(float)1.7 )							    
								),
								'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
						    )					    
					),			
					2=>array(   'X'=>array(	'Z'=>array( 'Z'=>array( 3=>$test_obj ))))		    		    
		);
				
				
		$this->assertEquals($check_data, $result);	
		
		
		// Check cache state
		// ===============================================================			
		
		$check_cache_warm = array(		    
					    1=>array(   'L4'=>null,
							'L3'=>array(	'Y'=>array( 'K'=>true, 
										    'Z'=>true
							)),
							'L2'=>null,
							'keys'=>array(  
									// The L4 'X' trie will still be in the cache from the
									// add operation

									'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>null,
													    2=>false
												),
												'T'=>array( 1=>true )							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
									),	
									'Y'=>array( 'K'=>array( 'K'=>array(	
													    1=>(int)1,
													    2=>(int)-1
												),
												'T'=>array( 3=>(float)1.7 )							    
										    ),
										    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
									)
							)
					    ),			
					    2=>array(   'L4'=>null,
							'L3'=>array(	'X'=>array(
										    'Z'=>true
							)),		
							'L2'=>null,				    
							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>(string)"foo",
													    2=>array(null, true, false, 1, 1.0, "foo")
												)							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
									)	
							)						
					    )		    		    
		);
		
		$this->assertEquals($check_cache_warm, $this->cls->cache);			
		
		
		// STAGE 2 - COLD CACHE - Flush cache and run again
		// ===================================================================
												
		$this->cls->flushCache();
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);

		
		$check_cache_cold = array(		    
					    1=>array(   'L4'=>null,
							'L3'=>array(	'Y'=>array( 'K'=>true, 
										    'Z'=>true
							)),
							'L2'=>null,
							'keys'=>array(  
									'Y'=>array( 'K'=>array( 'K'=>array(	
													    1=>(int)1,
													    2=>(int)-1
												),
												'T'=>array( 3=>(float)1.7 )							    
										    ),
										    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
									)
							)
					    ),			
					    2=>array(   'L4'=>null,
							'L3'=>array(	'X'=>array( 'Z'=>true )),		
							'L2'=>null,				    
							'keys'=>array(  'X'=>array( 'Z'=>array( 'Z'=>array( 3=>$test_obj ))))						
					    )		    		    
		);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);
		
		
		// STAGE 3 - HOT CACHE - Loaded, with authority, by previous call
		// ===================================================================	
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);		
		
		
	}		
	
	
       /**
	* Test fixture for getMulti() method, trie mode, L2 trie depth
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getMulti_trie_L2() {
	    
		
		self::loadData();
				
		
		// STAGE 1 - WARM CACHE - Loaded from previous 'add' operation
		// ===================================================================		
		
		$request = array(
				    1=>array(   'Y'=>array( 'K'=>array( 'K'=>true,
									'T'=>true							    
							    ),
							    'Z'=>array( 'Z'=>true) 						
						)					    
				    ),			
				    2=>array(   'X'=>array( 'Z'=>array( 'Z'=>true))					    
				    )		    		    
		);
		
		$ctrl = array(		    
				'validate'=>true,
				'q_mode'=>'trie',
				'r_mode'=>'trie',		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
	        
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check_data = array(
					1=>array(   'Y'=>array(	'K'=>array( 'K'=>array(	
											1=>(int)1,
											2=>(int)-1
									    ),
									    'T'=>array(	3=>(float)1.7 )							    
								),
								'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
						    )					    
					),			
					2=>array(   'X'=>array(	'Z'=>array( 'Z'=>array( 3=>$test_obj )))					    
					)		    		    
		);
				
				
		$this->assertEquals($check_data, $result);	
		
		
		// Check cache state
		// ===============================================================			
		
		$check_cache_warm = array(		    
					    1=>array(   'L4'=>null,
							'L3'=>null,
							'L2'=>array(    'Y'=>array( 'K'=>array( 'K'=>true,
												'T'=>true),
										    'Z'=>array( 'Z'=>true))
							),
							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>null,
													    2=>false
												),
												'T'=>array( 1=>true )							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
									),	
									'Y'=>array( 'K'=>array( 'K'=>array(	
													    1=>(int)1,
													    2=>(int)-1
												),
												'T'=>array( 3=>(float)1.7 )							    
										    ),
										    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
									)
							)
					    ),			
					    2=>array(   'L4'=>null,
							'L3'=>null,		
							'L2'=>array(    'X'=>array( 'Z'=>array( 'Z'=>true ))
							),			    
							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>(string)"foo",
													    2=>array(null, true, false, 1, 1.0, "foo")
												)							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
									)	
							)						
					    )		    		    
		);
		
		$this->assertEquals($check_cache_warm, $this->cls->cache);			

		
		// STAGE 2 - COLD CACHE - Flush cache and run again
		// ===================================================================
												
		$this->cls->flushCache();
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);
		
		
		$check_cache_cold = array(		    
					    1=>array(   'L4'=>null,
							'L3'=>null,
							'L2'=>array(    'Y'=>array( 'K'=>array( 'K'=>true,
												'T'=>true),
										    'Z'=>array( 'Z'=>true))
							),
							'keys'=>array(  'Y'=>array( 'K'=>array( 'K'=>array(	
													    1=>(int)1,
													    2=>(int)-1
												),
												'T'=>array( 3=>(float)1.7 )							    
										    ),
										    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
									)
							)
					    ),			
					    2=>array(   'L4'=>null,
							'L3'=>null,		
							'L2'=>array(    'X'=>array( 'Z'=>array( 'Z'=>true ))
							),			    
							'keys'=>array(  'X'=>array(	
										    'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
									)	
							)						
					    )		    		    
		);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);
		
		
		// STAGE 3 - HOT CACHE - Loaded, with authority, by previous call
		// ===================================================================	
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);		
		
		
	}
	
	
       /**
	* Test fixture for getMulti() method, matrix mode, L2 trie depth
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getMulti_matrix_L2() {
	    
		
		self::loadData();
				
		
		// STAGE 1 - WARM CACHE - Loaded from previous 'add' operation
		// ===================================================================		
		
		$request = array(
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K"),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"T"),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"Z", "L2"=>"Z"),
		    		array( "L5"=>2, "L4"=>"X", "L3"=>"Z", "L2"=>"Z")			    		    
		);		
		
		$ctrl = array(		    
				'validate'=>true,
				'q_mode'=>'matrix',
				'r_mode'=>'trie',		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
	        
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check_data = array(
					1=>array(   'Y'=>array(	'K'=>array( 'K'=>array(	
											1=>(int)1,
											2=>(int)-1
									    ),
									    'T'=>array(	3=>(float)1.7 )							    
								),
								'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
						    )					    
					),			
					2=>array(   'X'=>array(	'Z'=>array( 'Z'=>array( 3=>$test_obj )))					    
					)		    		    
		);
				
				
		$this->assertEquals($check_data, $result);	
		
		
		// Check cache state
		// ===============================================================			
		
		$check_cache_warm = array(		    
					    1=>array(   'L4'=>null,
							'L3'=>null,
							'L2'=>array(    'Y'=>array( 'K'=>array( 'K'=>true,
												'T'=>true),
										    'Z'=>array( 'Z'=>true))
							),
							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>null,
													    2=>false
												),
												'T'=>array( 1=>true )							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
									),	
									'Y'=>array( 'K'=>array( 'K'=>array(	
													    1=>(int)1,
													    2=>(int)-1
												),
												'T'=>array( 3=>(float)1.7 )							    
										    ),
										    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
									)
							)
					    ),			
					    2=>array(   'L4'=>null,
							'L3'=>null,		
							'L2'=>array(    'X'=>array( 'Z'=>array( 'Z'=>true ))
							),			    
							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>(string)"foo",
													    2=>array(null, true, false, 1, 1.0, "foo")
												)							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
									)	
							)						
					    )		    		    
		);
		
		$this->assertEquals($check_cache_warm, $this->cls->cache);			

		
		// STAGE 2 - COLD CACHE - Flush cache and run again
		// ===================================================================
												
		$this->cls->flushCache();
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);
		
		
		$check_cache_cold = array(		    
					    1=>array(   'L4'=>null,
							'L3'=>null,
							'L2'=>array(    'Y'=>array( 'K'=>array( 'K'=>true,
												'T'=>true),
										    'Z'=>array( 'Z'=>true))
							),
							'keys'=>array(  'Y'=>array( 'K'=>array( 'K'=>array(	
													    1=>(int)1,
													    2=>(int)-1
												),
												'T'=>array( 3=>(float)1.7 )							    
										    ),
										    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
									)
							)
					    ),			
					    2=>array(   'L4'=>null,
							'L3'=>null,		
							'L2'=>array(    'X'=>array( 'Z'=>array( 'Z'=>true ))
							),			    
							'keys'=>array(  'X'=>array(	
										    'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
									)	
							)						
					    )		    		    
		);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);
		
		
		// STAGE 3 - HOT CACHE - Loaded, with authority, by previous call
		// ===================================================================	
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);		
		
		
	}	
	
	
       /**
	* Test fixture for getMulti() method, trie mode, L1 trie depth
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getMulti_trie_L1() {
	    
		
		self::loadData();
				
		
		// STAGE 1 - WARM CACHE - Loaded from previous 'add' operation
		// ===================================================================

		$request = array(
				    1=>array(   'X'=>array( 'K'=>array( 'K'=>array(	
										    2=>true
									),
									'T'=>array( 1=>true )							    
							    ),
							    'Z'=>array( 'Z'=>array( 3=>true) 						
						)),	
						'Y'=>array( 'K'=>array( 'K'=>array(	
										    2=>true
									)						    
							    ),
							    'Z'=>array( 'Z'=>array( 4=>true )) 						
						)					    
				    ),			
				    2=>array(   'X'=>array( 'K'=>array( 'K'=>array( 1=>true )),
							    'Z'=>array( 'Z'=>array( 3=>true )) 						
						)					    
				    )		    		    
		);		
		
		$ctrl = array(		    
				'validate'=>true,
				'q_mode'=>'trie',
				'r_mode'=>'trie',		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
	        
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check_data = array(
					1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
											2=>false
									    ),
									    'T'=>array(	1=>true )							    
								),
								'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
						    ),	
						    'Y'=>array(	'K'=>array( 'K'=>array(	
											2=>(int)-1
									    )						    
								),
								'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
						    )					    
					),			
					2=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
											1=>(string)"foo",
									    )							    
								),
								'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
						    )					    
					)		    		    
		);				
				
		$this->assertEquals($check_data, $result);	
		
						
		// Check cache state
		// ===============================================================			
		
		$check_cache_warm = array(		    
					    1=>array(   // There will be no LUT arrays, because all request data is fully-qualified 
							// L5->L1 walks, and all requested items are already in the cache, so
							// neither the persistent cache nor db load code runs

							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>null,
													    2=>false
												),
										    'T'=>array(	1=>true )							    
										    ),
										    'Z'=>array(	'Z'=>array( 3=>(int)0)) 						
									),	
									'Y'=>array( 'K'=>array( 'K'=>array(	
													    1=>(int)1,
													    2=>(int)-1
												),
												'T'=>array( 3=>(float)1.7 )							    
										    ),
										    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
									)
							)
					    ),			
					    2=>array(   // There will be no LUT arrays, because all request data is fully-qualified 
							// L5->L1 walks, and all requested items are already in the cache, so
							// neither the persistent cache nor db load code runs

							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>(string)"foo",
													    2=>array(null, true, false, 1, 1.0, "foo")
												)							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
									)	
							)						
					   )		    		    
		);

		$this->assertEquals($check_cache_warm, $this->cls->cache);			

		
		// STAGE 2 - COLD CACHE - Flush cache and run again
		// ===================================================================
												
		$this->cls->flushCache();
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);
		
		
		$check_cache_cold = array(		    
					    1=>array(   'L4'=>null,
							'L3'=>null,
							'L2'=>null,
							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    2=>false
												),
												'T'=>array( 1=>true )							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
									),	
									'Y'=>array( 'K'=>array( 'K'=>array(	
													    2=>(int)-1
												)						    
										    ),
										    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
									)
							)
					    ),			
					    2=>array(   'L4'=>null,
							'L3'=>null,
							'L2'=>null,				    
							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>(string)"foo",
												)							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
									)	
							)						
					    )		    		    
		);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);
		
		
		// STAGE 3 - HOT CACHE - Loaded, with authority, by previous call
		// ===================================================================	
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);		
				
	}
	
	
       /**
	* Test fixture for getMulti() method, matrix mode, L1 trie depth
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getMulti_matrix_L1() {
	    
		
		self::loadData();
				
		
		// STAGE 1 - WARM CACHE - Loaded from previous 'add' operation
		// ===================================================================
		
		$request = array(
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>2),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T", "L1"=>1),
				array( "L5"=>1, "L4"=>"X", "L3"=>"Z", "L2"=>"Z", "L1"=>3),	
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>2),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"Z", "L2"=>"Z", "L1"=>4),		    
		    		array( "L5"=>2, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1),
		    		array( "L5"=>2, "L4"=>"X", "L3"=>"Z", "L2"=>"Z", "L1"=>3)		    		    
		);		
		
		$ctrl = array(		    
				'validate'=>true,
				'q_mode'=>'matrix',
				'r_mode'=>'trie',		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
	        
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check_data = array(
					1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
											2=>false
									    ),
									    'T'=>array(	1=>true )							    
								),
								'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
						    ),	
						    'Y'=>array(	'K'=>array( 'K'=>array(	
											2=>(int)-1
									    )						    
								),
								'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
						    )					    
					),			
					2=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
											1=>(string)"foo",
									    )							    
								),
								'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
						    )					    
					)		    		    
		);				
				
		$this->assertEquals($check_data, $result);	
		
						
		// Check cache state
		// ===============================================================			
		
		$check_cache_warm = array(		    
					    1=>array(   // There will be no LUT arrays, because all request data is fully-qualified 
							// L5->L1 walks, and all requested items are already in the cache, so
							// neither the persistent cache nor db load code runs

							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>null,
													    2=>false
												),
										    'T'=>array(	1=>true )							    
										    ),
										    'Z'=>array(	'Z'=>array( 3=>(int)0)) 						
									),	
									'Y'=>array( 'K'=>array( 'K'=>array(	
													    1=>(int)1,
													    2=>(int)-1
												),
												'T'=>array( 3=>(float)1.7 )							    
										    ),
										    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
									)
							)
					    ),			
					    2=>array(   // There will be no LUT arrays, because all request data is fully-qualified 
							// L5->L1 walks, and all requested items are already in the cache, so
							// neither the persistent cache nor db load code runs

							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>(string)"foo",
													    2=>array(null, true, false, 1, 1.0, "foo")
												)							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
									)	
							)						
					   )		    		    
		);

		$this->assertEquals($check_cache_warm, $this->cls->cache);			

		
		// STAGE 2 - COLD CACHE - Flush cache and run again
		// ===================================================================
												
		$this->cls->flushCache();
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);
		
		
		$check_cache_cold = array(		    
					    1=>array(   'L4'=>null,
							'L3'=>null,
							'L2'=>null,
							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    2=>false
												),
												'T'=>array( 1=>true )							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
									),	
									'Y'=>array( 'K'=>array( 'K'=>array(	
													    2=>(int)-1
												)						    
										    ),
										    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
									)
							)
					    ),			
					    2=>array(   'L4'=>null,
							'L3'=>null,
							'L2'=>null,				    
							'keys'=>array(  'X'=>array( 'K'=>array( 'K'=>array(	
													    1=>(string)"foo",
												)							    
										    ),
										    'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
									)	
							)						
					    )		    		    
		);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);
		
		
		// STAGE 3 - HOT CACHE - Loaded, with authority, by previous call
		// ===================================================================	
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);		
				
	}
	
	
       /**
	* Test fixture for getMulti() method, trie mode, mixed object depths
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getMulti_trie_mixed() {
	    
		
		self::loadData();
				
		
		// STAGE 1 - WARM CACHE - Loaded from previous 'add' operation
		// ===================================================================

		$request = array(
				    1=>array(   'X'=>array( 'K'=>array( 'K'=>array(	
										    2=>true
									),
									'T'=>true							    
							    ),
							    'Z'=>true						
						),	
						'Y'=>true					    
				    ),			
				    2=>true	    		    
		);		
		
		$ctrl = array(		    
				'validate'=>true,
				'q_mode'=>'trie',
				'r_mode'=>'trie',		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
	        
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check_data = array(
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
											1=>(string)"foo",
											2=>array(null, true, false, 1, 1.0, "foo")
									    )							    
								),
								'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
						    )					    
					)		    		    
		);				
				
		$this->assertEquals($check_data, $result);	
		
				
		// Check cache state
		// ===============================================================			
		
		$check_cache_warm = array(		    
					1=>array(   'L4'=>array(    'Y'=>true ),
						    'L3'=>array(    'X'=>array( 'Z'=>true )),
						    'L2'=>array(    'X'=>array( 'K'=>array( 'T'=>true))),
						    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
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
					2=>array(   'all_cached'=>true,
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
					)		    		    
		);

		$this->assertEquals($check_cache_warm, $this->cls->cache);			

		
		// STAGE 2 - COLD CACHE - Flush cache and run again
		// ===================================================================
												
		$this->cls->flushCache();
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);
		
		
		$check_cache_cold = array(		    
					1=>array(   'L4'=>array(    'Y'=>true ),
						    'L3'=>array(    'X'=>array( 'Z'=>true )),
						    'L2'=>array(    'X'=>array( 'K'=>array( 'T'=>true))),
						    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
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
					2=>array(   'all_cached'=>true,
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
					)		    		    
		);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);
		
		
		// STAGE 3 - HOT CACHE - Loaded, with authority, by previous call
		// ===================================================================	
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);		
				
	}	
	
	
       /**
	* Test fixture for getMulti() method, trie mode, mixed object depths
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_getMulti_matrix_mixed() {
	    
		
		self::loadData();
				
		
		// STAGE 1 - WARM CACHE - Loaded from previous 'add' operation
		// ===================================================================

		$request = array(		    
				    array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>2),
				    array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T"),
				    array( "L5"=>1, "L4"=>"X", "L3"=>"Z"),	
				    array( "L5"=>1, "L4"=>"Y"),		    
				    array( "L5"=>2)	    		    
		);
		
		$ctrl = array(		    
				'validate'=>true,
				'q_mode'=>'matrix',
				'r_mode'=>'trie'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));	
		}
		
		$this->assertEquals(true, $valid);
		
	        
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check_data = array(
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
											1=>(string)"foo",
											2=>array(null, true, false, 1, 1.0, "foo")
									    )							    
								),
								'Z'=>array( 'Z'=>array( 3=>$test_obj )) 						
						    )					    
					)		    		    
		);				
				
		$this->assertEquals($check_data, $result);	
		
				
		// Check cache state
		// ===============================================================			
		
		$check_cache_warm = array(		    
					1=>array(   'L4'=>array(    'Y'=>true ),
						    'L3'=>array(    'X'=>array( 'Z'=>true )),
						    'L2'=>array(    'X'=>array( 'K'=>array( 'T'=>true))),
						    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
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
					2=>array(   'all_cached'=>true,
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
					)		    		    
		);

		$this->assertEquals($check_cache_warm, $this->cls->cache);			

		
		// STAGE 2 - COLD CACHE - Flush cache and run again
		// ===================================================================
												
		$this->cls->flushCache();
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);
		
		
		$check_cache_cold = array(		    
					1=>array(   'L4'=>array(    'Y'=>true ),
						    'L3'=>array(    'X'=>array( 'Z'=>true )),
						    'L2'=>array(    'X'=>array( 'K'=>array( 'T'=>true))),
						    'keys'=>array(  'X'=>array(	'K'=>array( 'K'=>array(	
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
					2=>array(   'all_cached'=>true,
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
					)		    		    
		);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);
		
		
		// STAGE 3 - HOT CACHE - Loaded, with authority, by previous call
		// ===================================================================	
		
		$valid = false;
		
		try {			
			$result = $this->cls->getMulti($request, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);		
		
		$this->assertEquals($check_data, $result);
		
		$this->assertEquals($check_cache_cold, $this->cls->cache);		
				
	}	
	
	
	// ###########################################################################################################################
	// ###########################################################################################################################
	
	
       /**
	* Test fixture for getL1() method, single item, 'trie' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_getL1_single_trie() {
	    
		
		self::loadData();
					
		
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'trie'		    
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
	* Test fixture for getL1() method, single item, 'matrix' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_getL1_single_matrix() {
	    
		
		self::loadData();
							
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
	* Test fixture for getL1() method, multiple items, 'trie' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_getL1_multi_trie() {
	    
		
		self::loadData();
					
		
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'trie'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL1(1, 'Y', 'K', 'K', array(1,2), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		$check = array(
				'1'=>(int)1,
				'2'=>(int)-1
		);

		$this->assertEquals($check, $result);	
			
	}
	
	
       /**
	* Test fixture for getL1() method, multiple items, 'matrix' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_getL1_multi_matrix() {
	    

		self::loadData();
							
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'matrix'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL1(1, 'Y', 'K', 'K', array(1,2), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
	
		$check = array(		    
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>(int)1),
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>2, "L0"=>(int)-1),		    
		);

		$this->assertEquals($check, $result);	
			
	}	
	
	
       /**
	* Test fixture for getL2() method, single item, 'trie' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_getL2_single_trie() {
	    
		
		self::loadData();
					
		
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'trie'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL2(1, 'Y', 'K', 'K', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
	
		$check = array(
				'1'=>(int)1,
				'2'=>(int)-1
		);
		
		$this->assertEquals($check, $result);	
			
	}
	
	
       /**
	* Test fixture for getL2() method, single item, 'matrix' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_getL2_single_matrix() {
	    

		self::loadData();
							
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'matrix'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL2(1, 'Y', 'K', 'K', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
	
		$check = array(		    
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>(int)1),
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>2, "L0"=>(int)-1),		    
		);
		
		$this->assertEquals($check, $result);	
			
	}	
	
	
       /**
	* Test fixture for getL2() method, multiple items, 'trie' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_getL2_multi_trie() {
	    
	
		self::loadData();
					
		
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'trie'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL2(1, 'Y', 'K', array('K','T'), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		$check = array(
				'K'=>array(
					    1=>(int)1,
					    2=>(int)-1
				),
				'T'=>array( 
					    3=>(float)1.7
				)		    
		);

		$this->assertEquals($check, $result);	
			
	}
	
	
       /**
	* Test fixture for getL2() method, multiple items, 'matrix' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_getL2_multi_matrix() {
	    

		self::loadData();
							
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'matrix'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL2(1, 'Y', 'K', array('K','T'), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
	
		$check = array(		    
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>(int)1),
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>2, "L0"=>(int)-1),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"T", "L1"=>3, "L0"=>(float)1.7)		    
		);

		$this->assertEquals($check, $result);	
			
	}	
	
	
       /**
	* Test fixture for getL3() method, single item, 'trie' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_getL3_single_trie() {
	    
		
		self::loadData();					
		
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'trie'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL3(1, 'Y', 'K', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
	
		$check = array(
				'K'=>array(	
					    1=>(int)1,
					    2=>(int)-1
				),
				'T'=>array( 3=>(float)1.7 )
		);
		
		$this->assertEquals($check, $result);	
			
	}
	
	
       /**
	* Test fixture for getL3() method, single item, 'matrix' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	public function test_getL3_single_matrix() {
	    

		self::loadData();
							
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'matrix'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL3(1, 'Y', 'K', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
	
		$check = array(		    
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>(int)1),
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>2, "L0"=>(int)-1),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"T", "L1"=>3, "L0"=>(float)1.7)		    
		);
		
		$this->assertEquals($check, $result);	
			
	}	
	
	
       /**
	* Test fixture for getL3() method, multiple items, 'trie' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_getL3_multi_trie() {
	    
	    
		self::loadData();
							
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'trie'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL3(1, 'Y', array('K','Z'), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		$check = array(
				'K'=>array( 'K'=>array(	
							1=>(int)1,
							2=>(int)-1
					    ),
					    'T'=>array(	3=>(float)1.7 )							    
				),
				'Z'=>array( 'Z'=>array( 4=>(float)-1.6 ))	    
		);

		$this->assertEquals($check, $result);	
			
	}
	
	
       /**
	* Test fixture for getL3() method, multiple items, 'matrix' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	public function test_getL3_multi_matrix() {
	    

		self::loadData();
							
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'matrix'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL3(1, 'Y', array('K','Z'), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
	
		$check = array(		    
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>(int)1),
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>2, "L0"=>(int)-1),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"T", "L1"=>3, "L0"=>(float)1.7),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"Z", "L2"=>"Z", "L1"=>4, "L0"=>(float)-1.6)		    
		);

		$this->assertEquals($check, $result);	
			
	}
	
	
       /**
	* Test fixture for getL4() method, single item, 'trie' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_getL4_single_trie() {
	    
		
		self::loadData();					
		
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'trie'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL4(1, 'Y', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
	
		$check = array(
				'K'=>array( 'K'=>array(	
							1=>(int)1,
							2=>(int)-1
					    ),
					    'T'=>array(	3=>(float)1.7 )							    
				),
				'Z'=>array( 'Z'=>array( 4=>(float)-1.6 ))
		);
		
		$this->assertEquals($check, $result);	
			
	}
	
	
       /**
	* Test fixture for getL4() method, single item, 'matrix' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	public function test_getL4_single_matrix() {
	    

		self::loadData();
							
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'matrix'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL4(1, 'Y', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
	
		$check = array(		    
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>(int)1),
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>2, "L0"=>(int)-1),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"T", "L1"=>3, "L0"=>(float)1.7),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"Z", "L2"=>"Z", "L1"=>4, "L0"=>(float)-1.6)		    
		);
		
		$this->assertEquals($check, $result);	
			
	}	
	
	
       /**
	* Test fixture for getL4() method, multiple items, 'trie' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_getL4_multi_trie() {
	    
   
		self::loadData();
							
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'trie'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL4(1, array('X','Y'), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		$check = array(
				'X'=>array( 'K'=>array( 'K'=>array(	
								    1=>null,
								    2=>false
							),
							'T'=>array( 1=>true )							    
					    ),
					    'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
				),	
				'Y'=>array( 'K'=>array( 'K'=>array(	
								    1=>(int)1,
								    2=>(int)-1
							),
							'T'=>array( 3=>(float)1.7 )							    
					    ),
					    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
				)	    
		);

		$this->assertEquals($check, $result);	
			
	}
	
	
       /**
	* Test fixture for getL4() method, multiple items, 'matrix' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	public function test_getL4_multi_matrix() {
	    

		self::loadData();
							
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'matrix'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL4(1, array('X','Y'), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
	
		$check = array(		    
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>null),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>2, "L0"=>false),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T", "L1"=>1, "L0"=>true),
				array( "L5"=>1, "L4"=>"X", "L3"=>"Z", "L2"=>"Z", "L1"=>3, "L0"=>(int)0),	
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>(int)1),
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>2, "L0"=>(int)-1),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"T", "L1"=>3, "L0"=>(float)1.7),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"Z", "L2"=>"Z", "L1"=>4, "L0"=>(float)-1.6)		    
		);

		$this->assertEquals($check, $result);	
			
	}
	
	
       /**
	* Test fixture for getL5() method, single item, 'trie' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_getL5_single_trie() {
	    
		
		self::loadData();					
		
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'trie'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL5(1, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
	
		$check = array(
				'X'=>array( 'K'=>array( 'K'=>array(	
								    1=>null,
								    2=>false
							),
							'T'=>array( 1=>true )							    
					    ),
					    'Z'=>array( 'Z'=>array( 3=>(int)0)) 						
				),	
				'Y'=>array( 'K'=>array( 'K'=>array(	
								    1=>(int)1,
								    2=>(int)-1
							),
							'T'=>array( 3=>(float)1.7 )							    
					    ),
					    'Z'=>array( 'Z'=>array( 4=>(float)-1.6 )) 						
				)
		);
		
		$this->assertEquals($check, $result);	
			
	}
	
	
       /**
	* Test fixture for getL5() method, single item, 'matrix' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	public function test_getL5_single_matrix() {
	    
	    
		self::loadData();
							
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'matrix'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL5(1, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
	
		$check = array(		    
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>null),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>2, "L0"=>false),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T", "L1"=>1, "L0"=>true),
				array( "L5"=>1, "L4"=>"X", "L3"=>"Z", "L2"=>"Z", "L1"=>3, "L0"=>(int)0),	
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>(int)1),
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>2, "L0"=>(int)-1),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"T", "L1"=>3, "L0"=>(float)1.7),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"Z", "L2"=>"Z", "L1"=>4, "L0"=>(float)-1.6)		    
		);
		
		$this->assertEquals($check, $result);	
			
	}	
	
	
       /**
	* Test fixture for getL5() method, multiple items, 'trie' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_getL5_multi_trie() {
	    

		self::loadData();
							
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'trie'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL5(array(1,2), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
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

		$this->assertEquals($check, $result);	
			
	}
	
	
       /**
	* Test fixture for getL5() method, multiple items, 'matrix' return format
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	public function test_getL5_multi_matrix() {
	    

		self::loadData();
							
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'matrix'		    
		);
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL5(array(1,2), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
	
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(	
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
		);

		$this->assertEquals($check, $result);	
			
	}
	
	// ###########################################################################################################################
	// ###########################################################################################################################
	
	
       /**
	* Tests &$valid flag for every possible combination of L5->L1 walks
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_dataIntegrity_validFlag() {
	    
		
		self::loadData();
					
		
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'trie'		    
		);
		
		// L1 - Single VALID L1
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL1(1, 'Y', 'K', 'K', 1, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		
		// L1 - Single invalid L1
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL1(1, 'Y', 'K', 'K', 99, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);
		
		
		// L1 - Single invalid L1 in 'multi' mode
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL1(1, 'Y', 'K', 'K', array(1,99), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
		// L1 - Multiple invalid L1s in 'multi' mode
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL1(1, 'Y', 'K', 'K', array(88,99), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$this->assertEquals(false, $valid);
		
		
		// L1 - Single valid L1, invalid L2
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL1(1, 'Y', 'K', 'Z', 1, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);	
		
		
		// L1 - Single valid L1, invalid L3
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL1(1, 'Y', 'T', 'K', 1, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);
		
		
		// L1 - Single valid L1, invalid L4
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL1(1, 'T', 'K', 'K', 1, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);	
		
		
		// L1 - Single valid L1, invalid L5
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL1(99, 'Y', 'K', 'K', 1, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);
		
		
		// ==========================================================================================
		// ==========================================================================================
		
		
		// L2 - Single VALID L2
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL2(1, 'Y', 'K', 'K', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		
		// L2 - Single invalid L2
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL2(1, 'Y', 'K', 'F', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);
		
		
		// L2 - Single invalid L2 in 'multi' mode
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL2(1, 'Y', 'K', array('K','F'), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
				
		// L2 - Multiple invalid L2s in 'multi' mode
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL2(1, 'Y', 'K', array('K','G'), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$this->assertEquals(false, $valid);
		
		
		// L2 - Single valid L2, invalid L3
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL2(1, 'Y', 'T', 'K', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);
		
		
		// L2 - Single valid L2, invalid L4
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL2(1, 'T', 'K', 'K', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);
		
		
		// L2 - Single valid L2, invalid L5
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL2(99, 'Y', 'K', 'K', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);			
		
		
		// ==========================================================================================
		// ==========================================================================================
		
		
		// L3 - Single VALID L3
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL3(1, 'Y', 'K', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		
		// L3 - Single invalid L3
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL3(1, 'Y', 'Q', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);
		
		
		// L3 - Single invalid L3 in 'multi' mode
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL3(1, 'Y', array('Q','K'), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
				
		// L3 - Multiple invalid L3s in 'multi' mode
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL3(1, 'Y', array('Q','W'), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$this->assertEquals(false, $valid);		
		
		
		// L3 - Single valid L3, invalid L4
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL3(1, 'T', 'K', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);
		
		
		// L3 - Single valid L3, invalid L5
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL3(99, 'Y', 'K', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);		
		
		
		// ==========================================================================================
		// ==========================================================================================
		
		
		// L4 - Single VALID L4
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL4(1, 'Y', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		
		// L4 - Single invalid L4
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL4(1, 'T', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);
		
		
		// L4 - Single invalid L4 in 'multi' mode
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL4(1, array('T','Y'), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
				
		// L4 - Multiple invalid L4s in 'multi' mode
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL4(1, array('T','Q'), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$this->assertEquals(false, $valid);	
		
		
		// L4 - Single valid L4, invalid L5
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL4(99, 'Y', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);
		
		
		// ==========================================================================================
		// ==========================================================================================
		
		 				
		// L5 - Single invalid L5
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL5(99, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);
		
		
		// L5 - Single invalid L5 in 'multi' mode
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL5(array(1,99), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
				
		// L5 - Multiple invalid L5s in 'multi' mode
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL5(array(88,99), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$this->assertEquals(false, $valid);
			
			
	}
	
	
       /**
	* Tests keyType validators for every possible combination of L5->L1 walks
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	public function test_dataIntegrity_keyType() {
	    
		
		self::loadData();
					
		
		$ctrl = array(		    
				'validate'=>true,
				'r_mode'=>'trie'		    
		);
		
		// L1 - Single VALID L1
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL1(1, 'Y', 'K', 'K', 1, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		unset($valid);
		
		
		// L1 - Single invalid L1 key
		// ===========================================
		
		try {			
			$result = $this->cls->getL1(1, 'Y', 'K', 'K', "T", $ctrl, $valid);
			
			// Execution will halt on the previous line if getL1() throws an exception
			$this->fail("Method getL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L1 - Single invalid L1 key in 'multi' mode
		// =================================================
		
		try {			
			$result = $this->cls->getL1(1, 'Y', 'K', 'K', array(1, "T"), $ctrl, $valid);
			
			// Execution will halt on the previous line if getL1() throws an exception
			$this->fail("Method getL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L1 - Multiple invalid L1 keys in 'multi' mode
		// ====================================================
		
		try {			
			$result = $this->cls->getL1(1, 'Y', 'K', 'K', array("Q", "T"), $ctrl, $valid);
			
			// Execution will halt on the previous line if getL1() throws an exception
			$this->fail("Method getL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L1 - Valid L1, invalid L2
		// ===========================================

		try {			
			$result = $this->cls->getL1(1, 'Y', 'K', 2, 1, $ctrl, $valid);
			
			// Execution will halt on the previous line if getL1() throws an exception
			$this->fail("Method getL1() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {

		}			
				
		// L1 - Valid L1, invalid L3
		// ===========================================

		
		try {			
			$result = $this->cls->getL1(1, 'Y', 7, 'K', 1, $ctrl, $valid);
			
			// Execution will halt on the previous line if getL1() throws an exception
			$this->fail("Method getL1() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {

		}		
		
		
		// L1 - Valid L1, invalid L4
		// ===========================================

		try {			
			$result = $this->cls->getL1(1, '4', 'K', 'K', 1, $ctrl, $valid);
			
			// Execution will halt on the previous line if getL1() throws an exception
			$this->fail("Method getL1() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {
	
		}
		
		// L1 - Valid L1, invalid L5
		// ===========================================

		try {			
			$result = $this->cls->getL1('2', 'Y', 'K', 'K', 1, $ctrl, $valid);
			
			// Execution will halt on the previous line if getL1() throws an exception
			$this->fail("Method getL1() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {

		}		
		
		// ==========================================================================================	
		
		
		// L2 - Single VALID L2
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL2(1, 'Y', 'K', 'K', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		unset($valid);		
		
		
		// L2 - Single invalid L2 key
		// ===========================================
		
		try {			
			$result = $this->cls->getL2(1, 'Y', 'K', 1, $ctrl, $valid);
			
			// Execution will halt on the previous line if getL2() throws an exception
			$this->fail("Method getL2() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L2 - Single invalid L2 key in 'multi' mode
		// =================================================
		
		try {			
			$result = $this->cls->getL2(1, 'Y', 'K', array(1,'K'), $ctrl, $valid);
			
			// Execution will halt on the previous line if getL2() throws an exception
			$this->fail("Method getL2() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L2 - Multiple invalid L2 keys in 'multi' mode
		// ====================================================
		
		try {			
			$result = $this->cls->getL2(1, 'Y', 'K', array('1','2'), $ctrl, $valid);
			
			// Execution will halt on the previous line if getL2() throws an exception
			$this->fail("Method getL2() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		
		// L2 - Valid L2, invalid L3
		// ===========================================		
		
		try {			
			$result = $this->cls->getL2(1, 'Y', '1', 'K', $ctrl, $valid);
			
			// Execution will halt on the previous line if getL2() throws an exception
			$this->fail("Method getL2() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {
	
		}
				
		// L2 - Valid L2, invalid L4
		// ===========================================		
		
		try {			
			$result = $this->cls->getL2(1, 6, 'K', 'K', $ctrl, $valid);
			
			// Execution will halt on the previous line if getL2() throws an exception
			$this->fail("Method getL2() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {
	
		}
		
		// L2 - Valid L2, invalid L5
		// ===========================================		
		
		try {			
			$result = $this->cls->getL2('2', 'Y', 'K', 'K', $ctrl, $valid);
			
			// Execution will halt on the previous line if getL2() throws an exception
			$this->fail("Method getL2() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {
	
		}		
		
		// ==========================================================================================	
		
		
		// L3 - Single VALID L3
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL3(1, 'Y', 'K', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		unset($valid);		
		
		
		// L3 - Single invalid L3 key
		// ===========================================
		
		try {			
			$result = $this->cls->getL3(1, 'Y', '6', $ctrl, $valid);
			
			// Execution will halt on the previous line if getL3() throws an exception
			$this->fail("Method getL3() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L3 - Single invalid L3 key in 'multi' mode
		// =================================================
		
		try {			
			$result = $this->cls->getL3(1, 'Y', array(99, 'R'), $ctrl, $valid);
			
			// Execution will halt on the previous line if getL3() throws an exception
			$this->fail("Method getL3() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L3 - Multiple invalid L3 keys in 'multi' mode
		// ====================================================
		
		try {			
			$result = $this->cls->getL3(1, 'Y', array(1,2), $ctrl, $valid);
			
			// Execution will halt on the previous line if getL3() throws an exception
			$this->fail("Method getL3() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		
		// L3 - Valid L3, invalid L4
		// ===========================================		
		
		try {			
			$result = $this->cls->getL3(1, 2, 'K', $ctrl, $valid);
			
			// Execution will halt on the previous line if getL3() throws an exception
			$this->fail("Method getL3() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {
	
		}
				
		// L3 - Valid L3, invalid L5
		// ===========================================		
		
		try {			
			$result = $this->cls->getL3('2', 'Y', 'K', $ctrl, $valid);
			
			// Execution will halt on the previous line if getL3() throws an exception
			$this->fail("Method getL3() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {
	
		}			
		
		// ==========================================================================================		
		
		
		// L4 - Single VALID L4
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL4(1, 'Y', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		unset($valid);		
		
		
		// L4 - Single invalid L4 key
		// ===========================================
		
		try {			
			$result = $this->cls->getL4(1, '7', $ctrl, $valid);
			
			// Execution will halt on the previous line if getL4() throws an exception
			$this->fail("Method getL4() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L4 - Single invalid L4 key in 'multi' mode
		// =================================================
		
		try {			
			$result = $this->cls->getL4(1, array('Y', 99), $ctrl, $valid);
			
			// Execution will halt on the previous line if getL4() throws an exception
			$this->fail("Method getL4() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L4 - Multiple invalid L4 keys in 'multi' mode
		// ====================================================
		
		try {			
			$result = $this->cls->getL4(1, array(2,'4'), $ctrl, $valid);
			
			// Execution will halt on the previous line if getL4() throws an exception
			$this->fail("Method getL4() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		
		// L4 - Valid L4, invalid L5
		// ===========================================		
		
		try {			
			$result = $this->cls->getL4('2', 'Y', $ctrl, $valid);
			
			// Execution will halt on the previous line if getL4() throws an exception
			$this->fail("Method getL4() failed to throw an exception on invalid key type");			
		}
		catch (FOX_exception $child) {
	
		}				
		
		// ==========================================================================================	
		
		
		// L4 - Single VALID L5
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL5(1, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		unset($valid);		
		
		
		// L5 - Single invalid L5 key
		// ===========================================
		
		try {			
			$result = $this->cls->getL5('2', $ctrl, $valid);
			
			// Execution will halt on the previous line if getL5() throws an exception
			$this->fail("Method getL5() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L5 - Single invalid L5 key in 'multi' mode
		// =================================================
		
		try {			
			$result = $this->cls->getL5(array(), $ctrl, $valid);
			
			// Execution will halt on the previous line if getL5() throws an exception
			$this->fail("Method getL5() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L5 - Multiple invalid L5 keys in 'multi' mode
		// ====================================================
		
		try {			
			$result = $this->cls->getL5(array('T',null), $ctrl, $valid);
			
			// Execution will halt on the previous line if getL5() throws an exception
			$this->fail("Method getL5() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
				
		
	}
	
	
	function tearDown() {
	   
		$this->cls = new FOX_dataStore_paged_L4_tester_getMethods();
		$unistall_ok = $this->cls->uninstall();
		
		$this->assertEquals(true, $unistall_ok);
		
		parent::tearDown();
	}

    
}

?>