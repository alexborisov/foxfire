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
	* Test fixture for replaceL2_multi() method, trie mode, L5 trie depth
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_replaceL2_multi() {
	    

		self::loadData();
				
		
		// STAGE 1 - WARM CACHE - Loaded from previous 'add' operation
		// ===================================================================
		
		
		// Replace items
		// ==============================
		
		// NOTE: a L2 must have at least *one* L1 within it in order to have an entry within the DB. If 
		// a L2 doesn't have L1's inside it, there's nothing to write to the L1 column in the database,
		// which would violate the table index. In addition to this, storing empty L2's would waste
		// space in the table and cache.
		
		$data = array(
					1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
											1=>'foo', // 1=>null,
												  // 2=>false,
											7=>'bar'
									    ),
									    'T'=>array(),	  // Drop this L2
									    'W'=>array(	1=>'baz' )					    
								)
						    )
					)
		);
		
		$ctrl = array(		    
				'validate'=>false		    
		);
		
		
		try {			
			$this->cls->replaceL2_multi($data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		
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
		
		$this->assertEquals(true, $valid);		
				
	        
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check_data = array(
					1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
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
		// ==============================			
		
		$check_cache = array(		    
					1=>array(   'all_cached'=>true,
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
	
	
	function tearDown() {
	   
		$this->cls = new FOX_dataStore_paged_L5_tester_replaceMethods();
		$unistall_ok = $this->cls->uninstall();
		
		$this->assertEquals(true, $unistall_ok);
		
		parent::tearDown();
	}

    
}

?>