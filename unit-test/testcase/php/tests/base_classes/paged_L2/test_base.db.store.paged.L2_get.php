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

class FOX_dataStore_paged_L2_tester_getMethods extends FOX_dataStore_paged_L2_base {
    

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
	
	
}  // ENDOF: class FOX_dataStore_paged_L2_tester_getMethods 

                                      

/**
 * FOXFIRE UNIT TEST SCRIPT - L2 PAGED ABSTRACT DATASTORE CLASS - GET METHODS
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

class core_L2_paged_abstract_getMethods extends RAZ_testCase {

	
    	function setUp() {

	    
		parent::setUp();
				
		
		// Install the db table
		// ===========================================
		
		$this->cls = new FOX_dataStore_paged_L2_tester_getMethods();
		
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

				array( "L2"=>'X', "L1"=>1, "L0"=>null	    ),
				array( "L2"=>'X', "L1"=>2, "L0"=>false	    ),
				array( "L2"=>'X', "L1"=>5, "L0"=>true	    ),
				array( "L2"=>'X', "L1"=>3, "L0"=>(int)0	    ),	

				array( "L2"=>'Y', "L1"=>1, "L0"=>(int)1	    ),
				array( "L2"=>'Y', "L1"=>2, "L0"=>(int)-1	    ),
		    		array( "L2"=>'Y', "L1"=>3, "L0"=>(float)1.7	    ),
		    		array( "L2"=>'Y', "L1"=>4, "L0"=>(float)-1.6    ),
		    
		    		array( "L2"=>'Z', "L1"=>1, "L0"=>(string)"foo"  ),
		    		array( "L2"=>'Z', "L1"=>2, "L0"=>array(null, true, false, 1, 1.0, "foo")),
		    		array( "L2"=>'Z', "L1"=>3, "L0"=>$test_obj	    )	
		    
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
		// ===============================================================		
		
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
				    'X'=>array(),
				    'Y'=>array(),
				    'Z'=>array()
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
						
		$this->assertEquals($check_data, $result);	
		
		
		// Check cache state
		// ===============================================================			
		
		$check_cache = array(		    
					'X'=>array(   'all_cached'=>true,

						    'keys'=>array(  
											    1=>null,
											    2=>false,
											    3=>(int)0,
											    5=>true							    

								    ),				    
					),    
					'Y'=>array(   'all_cached'=>true,

						    'keys'=>array(  					    
											    1=>(int)1,
											    2=>(int)-1,
											    3=>(float)1.7, 							    
											    4=>(float)-1.6  						
								    ),
						    
					),			
					'Z'=>array(   'all_cached'=>true,				    
						    'keys'=>array(  
											    1=>(string)"foo",
											    2=>array(null, true, false, 1, 1.0, "foo"),
											    3=>$test_obj  						
								     ),				    
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
				    'X'=>array( 
									2=>true,
									5=>true, 							    
									3=>true  						
				    ),	
				    'Y'=>array(
									4=>true  						
				    ),			
				    'Z'=>array(
									1=>true,
									3=>true  						
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
					'X'=>array( 
									    2=>false,
									    5=>true, 							    
									    3=>(int)0  						
					),	
					'Y'=>array( 
									    4=>(float)-1.6  						
					),			
					'Z'=>array(
									    1=>(string)"foo",
									    3=>$test_obj  						
					)		    		    
		);				
				
		$this->assertEquals($check_data, $result);	
		
						
		// Check cache state
		// ===============================================================			
		
		$check_cache_warm = array(		    
					    'X'=>array(   // There will be no LUT arrays, because all request data is fully-qualified 
							// L4->L1 walks, and all requested items are already in the cache, so
							// neither the persistent cache nor db load code runs
							'keys'=>array(  
												1=>null,
												2=>false,
												5=>true, 							    
												3=>(int)0  						
									),
					    ),
					    'Y'=>array(   
							'keys'=>array(  
												1=>(int)1,
												2=>(int)-1,
												3=>(float)1.7, 							    
												4=>(float)-1.6 						    
									),
					    ),			
					    'Z'=>array(   
							'keys'=>array(
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo"),																			    
												3=>$test_obj  						
									),
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
					    'X'=>array(   
							'keys'=>array( 
												2=>false,
												5=>true, 							    
												3=>(int)0  						
									),
					    ),
					    'Y'=>array(   
							'keys'=>array( 
												4=>(float)-1.6  						
									),
					    ),			
					    'Z'=>array(   
							'keys'=>array(  
												1=>(string)"foo",
												3=>$test_obj  						
									),
	
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
				array( "L2"=>'X', "L1"=>2),
				array( "L2"=>'X',"L1"=>1),
				array( "L2"=>'X', "L1"=>3),	
				array( "L2"=>'Y', "L1"=>2),
		    		array( "L2"=>'Y', "L1"=>4),		    
		    		array( "L2"=>'Z', "L1"=>1),
		    		array( "L2"=>'Z', "L1"=>3)		    		    
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
					'X'=>array( 
									    1=>null,
									    2=>false, 							    
									    3=>(int)0  						
					),	
					'Y'=>array( 
									    2=>(int)-1,
									    4=>(float)-1.6  						
					),			
					'Z'=>array(
									    1=>(string)"foo",
									    3=>$test_obj  						
					)		    		    
		);				
				
		$this->assertEquals($check_data, $result);	
		
						
		// Check cache state
		// ===============================================================			
		
		$check_cache_warm = array(		    
					    'X'=>array(   // There will be no LUT arrays, because all request data is fully-qualified 
							// L4->L1 walks, and all requested items are already in the cache, so
							// neither the persistent cache nor db load code runs

							'keys'=>array(
												1=>null,
												2=>false,
												5=>true, 							    
												3=>(int)0  						
									),
					    ),
					    'Y'=>array(   // There will be no LUT arrays, because all request data is fully-qualified 
							// L4->L1 walks, and all requested items are already in the cache, so
							// neither the persistent cache nor db load code runs

							'keys'=>array(
												1=>(int)1,
												2=>(int)-1,
												3=>(float)1.7, 							    
												4=>(float)-1.6 						    
									),
					    ),			
					    'Z'=>array(   // There will be no LUT arrays, because all request data is fully-qualified 
							// L4->L1 walks, and all requested items are already in the cache, so
							// neither the persistent cache nor db load code runs

							'keys'=>array(  
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo"),																			    
												3=>$test_obj  						
									),
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
					    'X'=>array(   
						
							'keys'=>array(
												1=>null,
												2=>false, 							    
												3=>(int)0  						
									)
					    ),
					    'Y'=>array(   
						
							'keys'=>array(
												2=>(int)-1,
												4=>(float)-1.6 						
									)
					    ),			
					    'Z'=>array(   
						
							'keys'=>array(  
												1=>(string)"foo",
												3=>$test_obj  						
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
				    'X'=>array(  	2=>true	),			
				    'Y'=>true,
				    'Z'=>true
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
									    1=>(string)"foo",
									    2=>array(null, true, false, 1, 1.0, "foo"),									    							    
									    3=>$test_obj  						
					)		    		    
		);				
				
		$this->assertEquals($check_data, $result);	
		
				
		// Check cache state
		// ===============================================================			
		
		$check_cache_warm = array(		    
					    'X'=>array( 
							'keys'=>array(
												1=>null,
												2=>false,
												5=>true, 							    
												3=>(int)0  						
							 ),
					    ),	
					    'Y'=>array(   
							'all_cached'=>true,
							'keys'=>array(
												1=>(int)1,
												2=>(int)-1,
												3=>(float)1.7, 							    
												4=>(float)-1.6 						    
							),
					    ),			
					    'Z'=>array(   
							'all_cached'=>true,
							'keys'=>array(
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo"),																			    
												3=>$test_obj  						
							),
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
					'X'=>array(   					    
						    'keys'=>array(
											    2=>false
										
								    ),
					),
					'Y'=>array(   
						    'all_cached'=>true,
						    'keys'=>array(
											    1=>(int)1,
											    2=>(int)-1,
											    3=>(float)1.7, 							    
											    4=>(float)-1.6 						
								    ),
					),			
					'Z'=>array( 
						    'all_cached'=>true,
						    'keys'=>array(
											    1=>(string)"foo",
											    2=>array(null, true, false, 1, 1.0, "foo"),																			    
											    3=>$test_obj  						
								    ),
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
	* Test fixture for getMulti() method, matrix mode, mixed object depths
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
				    array( "L2"=>'X', "L1"=>2	),
				    array( "L2"=>'Y'		),		    
				    array( "L2"=>'Z'		)	    		    
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
									    1=>(string)"foo",
									    2=>array(null, true, false, 1, 1.0, "foo"),									    							    
									    3=>$test_obj  						
					)		    		    
		);			
				
		$this->assertEquals($check_data, $result);	
		
				
		// Check cache state
		// ===============================================================			
		
		$check_cache_warm = array(		    
					    'X'=>array(   
							'keys'=>array(
												1=>null,
												2=>false,
												5=>true, 							    
												3=>(int)0  						
									),	
					    ),
					    'Y'=>array(   
							'all_cached'=>true,
							'keys'=>array(
												1=>(int)1,
												2=>(int)-1,
												3=>(float)1.7, 							    
												4=>(float)-1.6 						    
									),
					    ),			
					    'Z'=>array(   
							'all_cached'=>true,
							'keys'=>array(
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo"),																			    
												3=>$test_obj  						
							),						
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
					'X'=>array(   					    
						    'keys'=>array(
											    2=>false
										
								    ),	
					),
					'Y'=>array(   					    
						    'all_cached'=>true,					    
						    'keys'=>array(
											    1=>(int)1,
											    2=>(int)-1,
											    3=>(float)1.7, 							    
											    4=>(float)-1.6 						
						    ),
					),			
					'Z'=>array(   
						    'all_cached'=>true,
						    'keys'=>array(
											    1=>(string)"foo",
											    2=>array(null, true, false, 1, 1.0, "foo"),																			    
											    3=>$test_obj  						
						    ),
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
			$result = $this->cls->getL1('Y', 2, $ctrl, $valid);
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
			$result = $this->cls->getL1('Y',  2, $ctrl, $valid);
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
			$result = $this->cls->getL1('Y', array(1,2), $ctrl, $valid);
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
			$result = $this->cls->getL1('Y', array(1,2), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
	
		$check = array(		    
				array( "L2"=>'Y', "L1"=>1, "L0"=>(int)1),
				array( "L2"=>'Y', "L1"=>2, "L0"=>(int)-1),		    
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
			$result = $this->cls->getL2('X', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
	
		$check = array(
					'1'=>null,
					'2'=>false,
					'3'=>(int)0,
					'5'=>true				    
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
			$result = $this->cls->getL2('X',  $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
	
		$check = array(	
				array( "L2"=>'X', "L1"=>1, "L0"=>null),
				array( "L2"=>'X', "L1"=>2, "L0"=>false),		    
				array( "L2"=>'X', "L1"=>3, "L0"=>(int)0),
				array( "L2"=>'X', "L1"=>5, "L0"=>true),			    	    
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
			$result = $this->cls->getL2( array('X','Y'), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(	
				'X'=>array(
								    1=>null,
								    2=>false,
								    3=>(int)0,				    
								    5=>true 							      						
					    ),	
				'Y'=>array(
								    1=>(int)1,
								    2=>(int)-1,
								    3=>(float)1.7, 						    
								    4=>(float)-1.6  						
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
			$result = $this->cls->getL2( array('X','Y'), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(	
				array( "L2"=>'X', "L1"=>1, "L0"=>null	    ),
				array( "L2"=>'X', "L1"=>2, "L0"=>false	    ),
				array( "L2"=>'X', "L1"=>3, "L0"=>(int)0	    ),			    
				array( "L2"=>'X', "L1"=>5, "L0"=>true	    ),
				array( "L2"=>'Y', "L1"=>1, "L0"=>(int)1	    ),
				array( "L2"=>'Y', "L1"=>2, "L0"=>(int)-1	    ),
		    		array( "L2"=>'Y', "L1"=>3, "L0"=>(float)1.7   ),
		    		array( "L2"=>'Y', "L1"=>4, "L0"=>(float)-1.6  ),
		);

		$this->assertEquals($check, $result);	
			
	}
	
	
	
	// ###########################################################################################################################
	// ###########################################################################################################################
	
	
       /**
	* Tests &$valid flag for every possible combination of L2->L1 walks
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
			$result = $this->cls->getL1('X', 2, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		
		// L1 - Single invalid L1
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL1('X', 99, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);
		
		
		// L1 - Single invalid L1 in 'multi' mode
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL1('X', array(10,99), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
		// L1 - Multiple invalid L1s in 'multi' mode
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL1('Y', array(88,99), $ctrl, $valid);
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
			$result = $this->cls->getL2('X', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		
		// L2 - Single invalid L2
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL2('K', $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);
		
		
		// L2 - Single invalid L2 in 'multi' mode
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL2(array('X','K'), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(false, $valid);
				
		// L2 - Multiple invalid L2s in 'multi' mode
		// ===========================================
		
		$valid = false;
		
		try {			
			$result = $this->cls->getL2(array('K', 'J'), $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
		$this->assertEquals(false, $valid);	
		
	}
	
	
       /**
	* Tests keyType validators for every possible combination of L2->L1 walks
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
			$result = $this->cls->getL1('X', 1, $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		unset($valid);
		
		
		// L1 - Single invalid L1 key
		// ===========================================
		
		try {			
			$result = $this->cls->getL1(1, 'X', $ctrl, $valid);
			
			// Execution will halt on the previous line if getL1() throws an exception
			$this->fail("Method getL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L1 - Single invalid L1 key in 'multi' mode
		// =================================================
		
		try {			
			$result = $this->cls->getL1('X', array(1, "T"), $ctrl, $valid);
			
			// Execution will halt on the previous line if getL1() throws an exception
			$this->fail("Method getL1() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L1 - Multiple invalid L1 keys in 'multi' mode
		// ====================================================
		
		try {			
			$result = $this->cls->getL1('X', array("Q", "T"), $ctrl, $valid);
			
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
			$result = $this->cls->getL2('X',  $ctrl, $valid);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $valid);
		
		unset($valid);		
		
		
		// L2 - Single invalid L2 key
		// ===========================================
		
		try {			
			$result = $this->cls->getL2(1, $ctrl, $valid);
			
			// Execution will halt on the previous line if getL4() throws an exception
			$this->fail("Method getL2() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L2 - Single invalid L2 key in 'multi' mode
		// =================================================
		
		try {			
			$result = $this->cls->getL2(array('Y', 99), $ctrl, $valid);
			
			// Execution will halt on the previous line if getL4() throws an exception
			$this->fail("Method getL2() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}
		
		// L2 - Multiple invalid L2 keys in 'multi' mode
		// ====================================================
		
		try {			
			$result = $this->cls->getL2(array(4,'4'), $ctrl, $valid);
			
			// Execution will halt on the previous line if getL4() throws an exception
			$this->fail("Method getL2() failed to throw an exception on invalid key type");
			
		}
		catch (FOX_exception $child) {

		}

	}
	
	
	
	function tearDown() {
	   
		$this->cls = new FOX_dataStore_paged_L2_tester_getMethods();
		$unistall_ok = $this->cls->uninstall();
		
		$this->assertEquals(true, $unistall_ok);
		
		parent::tearDown();
	}

    
}

?>