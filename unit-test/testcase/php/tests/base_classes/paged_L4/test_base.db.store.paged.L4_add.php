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

class FOX_dataStore_paged_L4_tester_addMethods extends FOX_dataStore_paged_L4_base {
    

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
	
		
}  // ENDOF: class FOX_dataStore_paged_L4_tester_addMethods
  
                                      

/**
 * FOXFIRE UNIT TEST SCRIPT - L4 PAGED ABSTRACT DATASTORE CLASS - ADD METHODS
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

class core_L4_paged_abstract_addMethods extends RAZ_testCase {

	
    	function setUp() {

	    
		parent::setUp();
				
		
		// Install the db table
		// ===========================================
		
		$this->cls = new FOX_dataStore_paged_L4_tester_addMethods();
		
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
	* Test fixture for addL1() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_addL1() {

	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array( "L4"=>1, "L3"=>"X", "L2"=>"K", "L1"=>1, "L0"=>null),
				array( "L4"=>1, "L3"=>"X", "L2"=>"K", "L1"=>2, "L0"=>false),
				array( "L4"=>1, "L3"=>"X", "L2"=>"K", "L1"=>5, "L0"=>true),
				array( "L4"=>1, "L3"=>"X", "L2"=>"Z", "L1"=>3, "L0"=>(int)0),	

				array( "L4"=>1, "L3"=>"Y", "L2"=>"K", "L1"=>1, "L0"=>(int)1),
				array( "L4"=>1, "L3"=>"Y", "L2"=>"K", "L1"=>2, "L0"=>(int)-1),
		    		array( "L4"=>1, "L3"=>"Y", "L2"=>"K", "L1"=>3, "L0"=>(float)1.7),
		    		array( "L4"=>1, "L3"=>"Y", "L2"=>"Z", "L1"=>4, "L0"=>(float)-1.6),
		    
		    		array( "L4"=>2, "L3"=>"X", "L2"=>"K", "L1"=>1, "L0"=>(string)"foo"),
		    		array( "L4"=>2, "L3"=>"X", "L2"=>"K", "L1"=>2, "L0"=>array(null, true, false, 1, 1.0, "foo")),
		    		array( "L4"=>2, "L3"=>"X", "L2"=>"Z", "L1"=>3, "L0"=>$test_obj)	
		    
		);		
		
		// Load class with data
		// ===============================================================
		
		foreach( $test_data as $item ){
		    						
			try {
				$set_ok = $this->cls->addL1($item['L4'], $item['L3'], $item['L2'], $item['L1'], $item['L0'], $ctrl=null);
			}
			catch (FOX_exception $child) {
							    
				$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
			}			
			
			// Should return (int)1 to indicate a key was added
			$this->assertEquals(1, $set_ok); 			
			
		}
		unset($item);
		
		
		// Test adding duplicate item
		// ===============================================================
		
		try {
			$this->cls->addL1(1, 'X', 'Z', 3, 0, $ctrl=null);
			
			// Execution will halt on the previous line if addL1() throws an exception
			$this->fail("Method addL1() failed to throw an exception on duplicate entry");			
		}
		catch (FOX_exception $child) {
					    
		}			
			
		
		// Check cache state
		// ===============================================================	
		
		// NOTE: the LUT's won't be set at this point, because we haven't done any 
		// database reads that give objects authority
		
		$check = array(
				1=>array(   'keys'=>array(  'X'=>array(	'K'=>array(
										    1=>null,
										    2=>false,
										    5=>true 							    
									),
									'Z'=>array( 3=>(int)0 ) 						
							    ),	
							    'Y'=>array(	'K'=>array( 
										    1=>(int)1,
										    2=>(int)-1,
										    3=>(float)1.7 							    
									),
									'Z'=>array( 4=>(float)-1.6 ) 						
							    )
					    )
				),			
				2=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo")										    							    
									),
									'Z'=>array( 3=>$test_obj ) 						
							    )	
					    )						
				 )		    		    
		);
		
		$this->assertEquals($check, $this->cls->cache);	
		
		
		// Check db state
		// ===============================================================		
		
		$check = array(
				1=>array(   'X'=>array(	'K'=>array( 
								    1=>null,
								    2=>false,
								    5=>true							    
							),
							'Z'=>array( 3=>(int)0 ) 						
					    ),	
					    'Y'=>array(	'K'=>array( 
								    1=>(int)1,
								    2=>(int)-1,
								    3=>(float)1.7 							    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				),			
				2=>array(   'X'=>array(	'K'=>array( 
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo")								   						    
							),
							'Z'=>array( 3=>$test_obj ) 						
					    )					    
				 )		    		    
		);		
		
		
		$db = new FOX_db();	
		
		$columns = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L4','L3','L2','L1')
		);
		
		try {
			$struct = $this->cls->_struct();			
			$result = $db->runSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
                $this->assertEquals($check, $result);		
		
		
	}
	
	
       /**
	* Test fixture for addL1_multi() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_addL1_multi() {

	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array( "L4"=>1, "L3"=>"X", "L2"=>"K", "L1"=>1, "L0"=>null),
				array( "L4"=>1, "L3"=>"X", "L2"=>"K", "L1"=>2, "L0"=>false),
				array( "L4"=>1, "L3"=>"X", "L2"=>"K", "L1"=>5, "L0"=>true),
				array( "L4"=>1, "L3"=>"X", "L2"=>"Z", "L1"=>3, "L0"=>(int)0),	

				array( "L4"=>1, "L3"=>"Y", "L2"=>"K", "L1"=>1, "L0"=>(int)1),
				array( "L4"=>1, "L3"=>"Y", "L2"=>"K", "L1"=>2, "L0"=>(int)-1),
		    		array( "L4"=>1, "L3"=>"Y", "L2"=>"K", "L1"=>3, "L0"=>(float)1.7),
		    		array( "L4"=>1, "L3"=>"Y", "L2"=>"Z", "L1"=>4, "L0"=>(float)-1.6),
		    
		    		array( "L4"=>2, "L3"=>"X", "L2"=>"K", "L1"=>1, "L0"=>(string)"foo"),
		    		array( "L4"=>2, "L3"=>"X", "L2"=>"K", "L1"=>2, "L0"=>array(null, true, false, 1, 1.0, "foo")),
		    		array( "L4"=>2, "L3"=>"X", "L2"=>"Z", "L1"=>3, "L0"=>$test_obj)	
		    
		);		
		
		// Load class with data
		// ===============================================================
				    					
		try {
			$rows_changed = $this->cls->addL1_multi($test_data, $ctrl=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
		}			

		// Should return (int)1 to indicate a key was added
		$this->assertEquals(11, $rows_changed); 			
			
		
		// Test adding single duplicate item
		// ===============================================================
				
		$dupe_items = array(	

				array( "L4"=>1, "L3"=>"Y", "L2"=>"K", "L1"=>1, "L0"=>(int)1),		    
		);
		
		try {
			$this->cls->addL1_multi($dupe_items, $ctrl=null);
			
			// Execution will halt on the previous line if addL1() throws an exception
			$this->fail("Method addL1_multi() failed to throw an exception on duplicate entry");			
		}
		catch (FOX_exception $child) {}
		
		
		// Test adding multiple duplicate items
		// ===============================================================
				
		$dupe_items = array(

		    		array( "L4"=>1, "L3"=>"Y", "L2"=>"K", "L1"=>3, "L0"=>(float)1.7),
		    		array( "L4"=>1, "L3"=>"Y", "L2"=>"Z", "L1"=>4, "L0"=>(float)-1.6),		   
		    		array( "L4"=>2, "L3"=>"X", "L2"=>"K", "L1"=>1, "L0"=>(string)"foo"),
		    		array( "L4"=>2, "L3"=>"X", "L2"=>"K", "L1"=>2, "L0"=>array(null, true, false, 1, 1.0, "foo")),
		);
		
		try {
			$this->cls->addL1_multi($dupe_items, $ctrl=null);
			
			// Execution will halt on the previous line if addL1_multi() throws an exception
			$this->fail("Method addL1_multi() failed to throw an exception on duplicate entry");	
			
		}
		catch (FOX_exception $child) {}
		
		
		// Check cache state
		// ===============================================================	
		
		// NOTE: the LUT's won't be set at this point, because we haven't done any 
		// database reads that give objects authority
		
		$check = array(
				1=>array(   'keys'=>array(  'X'=>array(	'K'=>array(
										    1=>null,
										    2=>false,
										    5=>true 							    
									),
									'Z'=>array( 3=>(int)0 ) 						
							    ),	
							    'Y'=>array(	'K'=>array( 
										    1=>(int)1,
										    2=>(int)-1,
										    3=>(float)1.7 							    
									),
									'Z'=>array( 4=>(float)-1.6 ) 						
							    )
					    )
				),			
				2=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo")										    							    
									),
									'Z'=>array( 3=>$test_obj ) 						
							    )	
					    )						
				 )		    		    
		);		
		
		$this->assertEquals($check, $this->cls->cache);	
		
		
		// Check db state
		// ===============================================================		
		
		$check = array(
				1=>array(   'X'=>array(	'K'=>array( 
								    1=>null,
								    2=>false,
								    5=>true							    
							),
							'Z'=>array( 3=>(int)0 ) 						
					    ),	
					    'Y'=>array(	'K'=>array( 
								    1=>(int)1,
								    2=>(int)-1,
								    3=>(float)1.7 							    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				),			
				2=>array(   'X'=>array(	'K'=>array( 
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo")								   						    
							),
							'Z'=>array( 3=>$test_obj ) 						
					    )					    
				 )		    		    
		);			
		
		$db = new FOX_db();	
		
		$columns = null;
		$args = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L4','L3','L2','L1')
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
	* Test fixture for addL2() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_addL2() {

    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array( "L4"=>1, "L3"=>"X", "L2"=>"K", "L1s"=>array(
										    1=>null,
										    2=>false,
										    5=>true
										)), 
				array( "L4"=>1, "L3"=>"X", "L2"=>"Z", "L1s"=>array(
										    3=>(int)0
										)),
				array( "L4"=>1, "L3"=>"Y", "L2"=>"K", "L1s"=>array(
										    1=>(int)1,
										    2=>(int)-1,
										    3=>(float)1.7
										)),
		    		array( "L4"=>1, "L3"=>"Y", "L2"=>"Z", "L1s"=>array(
										    4=>(float)-1.6
										)),		    
		    		array( "L4"=>2, "L3"=>"X", "L2"=>"K", "L1s"=>array(
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo")
										)),
		    		array( "L4"=>2, "L3"=>"X", "L2"=>"Z", "L1s"=>array(
										    3=>$test_obj
										)),	
		    
		);		
		
		// Load class with data
		// ===============================================================
		
		foreach( $test_data as $item ){
		    						
			try {
				$rows_changed = $this->cls->addL2($item['L4'], $item['L3'], $item['L2'], $item['L1s'], $ctrl=null);
			}
			catch (FOX_exception $child) {
							    
				$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
			}			
			
			// Should return number of L1's added
			$this->assertEquals(count($item['L1s']), $rows_changed, ("ITEM: " . $item['L4'] . $item['L3'] . $item['L2'])); 			
			
		}
		unset($item);
			
		
		
		// Test adding duplicate item
		// ===============================================================
		
		try {		    
		    
			$this->cls->addL2(1, "X", "Z", array( 3=>(int)0 ), $ctrl=null);
			
			// Execution will halt on the previous line if addL2() throws an exception
			$this->fail("Method addL2() failed to throw an exception on duplicate entry");	
			
		}
		catch (FOX_exception $child) {}
		
		
		// Check cache state
		// ===============================================================	
		
		// NOTE: the LUT's won't be set at this point, because we haven't done any 
		// database reads that give objects authority
		
		$check = array(
				1=>array(   'keys'=>array(  'X'=>array(	'K'=>array(
										    1=>null,
										    2=>false,
										    5=>true 							    
									),
									'Z'=>array( 3=>(int)0 ) 						
							    ),	
							    'Y'=>array(	'K'=>array( 
										    1=>(int)1,
										    2=>(int)-1,
										    3=>(float)1.7 							    
									),
									'Z'=>array( 4=>(float)-1.6 ) 						
							    )
					    )
				),			
				2=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo")										    							    
									),
									'Z'=>array( 3=>$test_obj ) 						
							    )	
					    )						
				)		    		    
		);	
		
		$this->assertEquals($check, $this->cls->cache);	
		
		
		// Check db state
		// ===============================================================		
		
		$check = array(
				1=>array(   'X'=>array(	'K'=>array( 
								    1=>null,
								    2=>false,
								    5=>true							    
							),
							'Z'=>array( 3=>(int)0 ) 						
					    ),	
					    'Y'=>array(	'K'=>array( 
								    1=>(int)1,
								    2=>(int)-1,
								    3=>(float)1.7 							    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				),			
				2=>array(   'X'=>array(	'K'=>array( 
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo")								   						    
							),
							'Z'=>array( 3=>$test_obj ) 						
					    )					    
				)		    		    
		);	
				
		$db = new FOX_db();	
		
		$columns = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L4','L3','L2','L1')
		);
		
		try {
			$struct = $this->cls->_struct();			
			$result = $db->runSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
                $this->assertEquals($check, $result);		
		
		
	}	


       /**
	* Test fixture for addL2_multi() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	function test_addL2_multi() {

	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array( "L4"=>1, "L3"=>"X", "L2"=>"K", "L1"=>array(
										    1=>null,
										    2=>false,
										    5=>true
										)),
				array( "L4"=>1, "L3"=>"X", "L2"=>"Z", "L1"=>array(
										    3=>(int)0
										)),
				array( "L4"=>1, "L3"=>"Y", "L2"=>"K", "L1"=>array(
										    1=>(int)1,
										    2=>(int)-1,
										    3=>(float)1.7
										)),
		    		array( "L4"=>1, "L3"=>"Y", "L2"=>"Z", "L1"=>array(
										    4=>(float)-1.6
										)),		    
		    		array( "L4"=>2, "L3"=>"X", "L2"=>"K", "L1"=>array(
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo")
										)),
		    		array( "L4"=>2, "L3"=>"X", "L2"=>"Z", "L1"=>array(
										    3=>$test_obj
										)),	
		    
		);		
		
		
		// Load class with data
		// ===============================================================
				    					
		try {
			$set_ok = $this->cls->addL2_multi($test_data, $ctrl=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
		}			

		// Should return number of L1's added
		$this->assertEquals(11, $set_ok); 			
			
								
		// Test adding single duplicate item
		// ===============================================================
		
		$dupe_items = array(
				array("L4"=>1, "L3"=>"X", "L2"=>"K", "L1"=>array(1=>null,2=>false))
		);
		
		try {		    
		    
			$set_ok = $this->cls->addL2_multi($dupe_items, $ctrl=null);
			
			// Execution will halt on the previous line if addL1() throws an exception
			$this->fail("Method addL2_multi() failed to throw an exception on duplicate entry");			
		}
		catch (FOX_exception $child) {}
		
		
		// Test adding multiple duplicate items
		// ===============================================================
		
		$dupe_items = array(
				array( "L4"=>1, "L3"=>"X", "L2"=>"Z", "L1"=>array(
										    3=>(int)0)
										),
				array( "L4"=>1, "L3"=>"Y", "L2"=>"K", "L1"=>array(
										    1=>(int)1,
										    2=>(int)-1)
										),
		);
		
		try {		    		   
			$set_ok = $this->cls->addL2_multi($dupe_items, $ctrl=null);
			
			// Execution will halt on the previous line if addL1() throws an exception
			$this->fail("Method addL2_multi() failed to throw an exception on duplicate entry");			
		}
		catch (FOX_exception $child) {}		
		
		
		
		// Check cache state
		// ===============================================================	
		
		// NOTE: the LUT's won't be set at this point, because we haven't done any 
		// database reads that give objects authority
		
		$check = array(
				1=>array(   'keys'=>array(  'X'=>array(	'K'=>array(
										    1=>null,
										    2=>false,
										    5=>true 							    
									),
									'Z'=>array( 3=>(int)0 ) 						
							    ),	
							    'Y'=>array(	'K'=>array( 
										    1=>(int)1,
										    2=>(int)-1,
										    3=>(float)1.7 							    
									),
									'Z'=>array( 4=>(float)-1.6 ) 						
							    )
					    )
				),			
				2=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo")										    							    
									),
									'Z'=>array( 3=>$test_obj ) 						
							    )	
					    )						
				 )		    		    
		);	
		
		$this->assertEquals($check, $this->cls->cache);	
		
		
		// Check db state
		// ===============================================================		
		
		$check = array(
				1=>array(   'X'=>array(	'K'=>array( 
								    1=>null,
								    2=>false,
								    5=>true							    
							),
							'Z'=>array( 3=>(int)0 ) 						
					    ),	
					    'Y'=>array(	'K'=>array( 
								    1=>(int)1,
								    2=>(int)-1,
								    3=>(float)1.7 							    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				),			
				2=>array(   'X'=>array(	'K'=>array( 
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo")								   						    
							),
							'Z'=>array( 3=>$test_obj ) 						
					    )					    
				)		    		    
		);
		
		$db = new FOX_db();	
		
		$columns = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L4','L3','L2','L1')
		);
		
		try {
			$struct = $this->cls->_struct();			
			$result = $db->runSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
                $this->assertEquals($check, $result);		
		
		
	}
	
       /**
	* Test fixture for addL3() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	function test_addL3() {

    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array( "count"=>4, "L4"=>1, "L3"=>"X", "L2s"=>array(	"K"=>array( 
												    1=>null,
												    2=>false,
												    5=>true
											),
											"Z"=>array( 
												    3=>(int)0
											)
										)),
		    
				array( "count"=>4, "L4"=>1, "L3"=>"Y", "L2s"=>array(	"K"=>array( 
												    1=>(int)1,												
												    2=>(int)-1,
												    3=>(float)1.7
											),
											"Z"=>array( 
												    4=>(float)-1.6
											)
										)),
		    		    		    
		    		array( "count"=>3, "L4"=>2, "L3"=>"X", "L2s"=>array(	"K"=>array( 
												    1=>(string)"foo",
												    2=>array(null, true, false, 1, 1.0, "foo")
											),
											"Z"=>array( 
												    3=>$test_obj
											)
										))		    		    
		);	
		
		
		// Load class with data
		// ===============================================================
		
		foreach( $test_data as $item ){
		    						
			try {
				$rows_changed = $this->cls->addL3($item['L4'], $item['L3'], $item['L2s'], $ctrl=null);
			}
			catch (FOX_exception $child) {
							    
				$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
			}			
			
			// Should return number of L1's added
			$this->assertEquals( $item['count'], $rows_changed, ("ITEM: " . $item['L4'] . $item['L3']) ); 
			
			
		}
		unset($item);
		
		
		// Test adding a single duplicate item
		// ===============================================================
		
		try {			
			
			$this->cls->addL3(1, "Y", array(    "K"=>array( 
									1=>(int)1,												
									2=>(int)-1,
									3=>(float)1.7
							    ),
							    "Z"=>array( 
									4=>(float)-1.6
							    )
					 ), $ctrl=null);			
			
			// Execution will halt on the previous line if addL4() throws an exception
			$this->fail("Method addL3() failed to throw an exception on duplicate entry");
			
		}
		catch (FOX_exception $child) {}
		
		
		// Check cache state
		// ===============================================================	
		
		// NOTE: the LUT's won't be set at this point, because we haven't done any 
		// database reads that give objects authority
		
		$check = array(
				1=>array(   'keys'=>array(  'X'=>array(	'K'=>array(
										    1=>null,
										    2=>false,
										    5=>true 							    
									),
									'Z'=>array( 3=>(int)0 ) 						
							    ),	
							    'Y'=>array(	'K'=>array( 
										    1=>(int)1,
										    2=>(int)-1,
										    3=>(float)1.7 							    
									),
									'Z'=>array( 4=>(float)-1.6 ) 						
							    )
					    )
				),			
				2=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo")										    							    
									),
									'Z'=>array( 3=>$test_obj ) 						
							    )	
					    )						
				 )		    		    
		);	
		
		$this->assertEquals($check, $this->cls->cache);	
		
		
		// Check db state
		// ===============================================================		
		
		$check = array(
				1=>array(   'X'=>array(	'K'=>array( 
								    1=>null,
								    2=>false,
								    5=>true							    
							),
							'Z'=>array( 3=>(int)0 ) 						
					    ),	
					    'Y'=>array(	'K'=>array( 
								    1=>(int)1,
								    2=>(int)-1,
								    3=>(float)1.7 							    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				),			
				2=>array(   'X'=>array(	'K'=>array( 
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo")								   						    
							),
							'Z'=>array( 3=>$test_obj ) 						
					    )					    
				)		    		    
		);
		
		$db = new FOX_db();	
		
		$columns = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L4','L3','L2','L1')
		);
		
		try {
			$struct = $this->cls->_struct();			
			$result = $db->runSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
                $this->assertEquals($check, $result);		
		
		
	}	


       /**
	* Test fixture for addL4_multi() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	function test_addL3_multi() {

	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array(	"L4"=>1, "L3"=>"X", "L2"=>array(    "K"=>array( 
											1=>null,
											2=>false,
											5=>true
									    ),
									    "Z"=>array( 
											3=>(int)0
									    )
								)),
		    
				array(	"L4"=>1, "L3"=>"Y", "L2"=>array(    "K"=>array( 
											1=>(int)1,												
											2=>(int)-1,
											3=>(float)1.7
									    ),
									    "Z"=>array( 
											4=>(float)-1.6
									    )
								)),
		    		    		    
		    		array(	"L4"=>2, "L3"=>"X", "L2"=>array(    "K"=>array( 
											1=>(string)"foo",
											2=>array(null, true, false, 1, 1.0, "foo")
									    ),
									    "Z"=>array( 
											3=>$test_obj
									    )
								))		    		    
		);		
		
		
		// Load class with data
		// ===============================================================
				    					
		try {
			$set_ok = $this->cls->addL3_multi($test_data, $ctrl=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
		}			

		// Should return number of L1's added
		$this->assertEquals(11, $set_ok); 			
			
		
			
		// Test adding a single duplicate item
		// ===============================================================
		
		try {			
			
			$dupe_data = array(
			    
				array(	"L4"=>1, "L3"=>"Y", "L2"=>array(    "K"=>array( 
											1=>(int)1,												
											2=>(int)-1,
											3=>(float)1.7
									    ),
									    "Z"=>array( 
											4=>(float)-1.6
									    )
								))
			);
		    
			$this->cls->addL3_multi($dupe_data, $ctrl=null);			
			
			// Execution will halt on the previous line if addL3_multi() throws an exception
			$this->fail("Method addL3_multi() failed to throw an exception on duplicate entry");	
			
		}
		catch (FOX_exception $child) {}
		
		
		// Test adding multiple duplicate items
		// ===============================================================
		
		try {			
			
			$dupe_data = array(
			    
				array(	"L4"=>1, "L3"=>"X", "L2"=>array(    "K"=>array( 
											1=>null,
											2=>false,
											5=>true
									    ),
									    "Z"=>array( 
											3=>(int)0
									    )
							    )),

				array(	"L4"=>1, "L3"=>"Y", "L2"=>array(    "K"=>array( 
											1=>(int)1,												
											2=>(int)-1,
											3=>(float)1.7
									    ),
									    "Z"=>array(
											4=>(float)-1.6
									    )
							    ))
			);
		    
			$this->cls->addL3_multi($dupe_data, $ctrl=null);			
			
			// Execution will halt on the previous line if addL3_multi() throws an exception
			$this->fail("Method addL3_multi() failed to throw an exception on duplicate entry");	
			
		}
		catch (FOX_exception $child) {}
		
				
		// Check cache state
		// ===============================================================	
		
		// NOTE: the LUT's won't be set at this point, because we haven't done any 
		// database reads that give objects authority
		
		$check = array(
				1=>array(   'keys'=>array(  'X'=>array(	'K'=>array(
										    1=>null,
										    2=>false,
										    5=>true 							    
									),
									'Z'=>array( 3=>(int)0 ) 						
							    ),	
							    'Y'=>array(	'K'=>array( 
										    1=>(int)1,
										    2=>(int)-1,
										    3=>(float)1.7 							    
									),
									'Z'=>array( 4=>(float)-1.6 ) 						
							    )
					    )
				),			
				2=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo")										    							    
									),
									'Z'=>array( 3=>$test_obj ) 						
							    )	
					    )						
				 )		    		    
		);
		
		$this->assertEquals($check, $this->cls->cache);	
		
		
		// Check db state
		// ===============================================================		
		
		$check = array(
				1=>array(   'X'=>array(	'K'=>array( 
								    1=>null,
								    2=>false,
								    5=>true							    
							),
							'Z'=>array( 3=>(int)0 ) 						
					    ),	
					    'Y'=>array(	'K'=>array( 
								    1=>(int)1,
								    2=>(int)-1,
								    3=>(float)1.7 							    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				),			
				2=>array(   'X'=>array(	'K'=>array( 
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo")								   						    
							),
							'Z'=>array( 3=>$test_obj ) 						
					    )					    
				)		    		    
		);		
		
		
		$db = new FOX_db();	
		
		$columns = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L4','L3','L2','L1')
		);
		
		try {
			$struct = $this->cls->_struct();			
			$result = $db->runSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
                $this->assertEquals($check, $result);		
		
		
	}
	
	
       /**
	* Test fixture for addL4() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	function test_addL4() {

    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array( "count"=>8, "L4"=>1, "L3s"=>array(   "X"=>array(	"K"=>array( 
												    1=>null,
												    2=>false,
												    5=>true
											),
											"Z"=>array( 
												    3=>(int)0
											)
									    ),
									    "Y"=>array(	"K"=>array( 
												    1=>(int)1,												
												    2=>(int)-1,
												    3=>(float)1.7
											),
											"Z"=>array( 
												    4=>(float)-1.6
											)
									    )),				    				   				    
				),
		    
		    		array( "count"=>3, "L4"=>2, "L3s"=>array(   "X"=>array(	"K"=>array( 
												    1=>(string)"foo",
												    2=>array(null, true, false, 1, 1.0, "foo")
											),
											"Z"=>array( 
												    3=>$test_obj
											)
									    ))
				)				    
		);	
		
		
		// Load class with data
		// ===============================================================
		
		foreach( $test_data as $item ){
		    						
			try {
				$rows_changed = $this->cls->addL4($item['L4'], $item['L3s'],  $ctrl=null);
			}
			catch (FOX_exception $child) {
							    
				$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
			}			
			
			// Should return number of L1's added
			$this->assertEquals( $item['count'], $rows_changed, ("ITEM: " . $item['L4']) ); 
			
			
		}
		unset($item);
			
		
		// Test adding a single duplicate item
		// ===============================================================
		
		try {			
			
			$this->cls->addL4(2, array( "X"=>array(	"K"=>array( 
									    1=>(string)"foo",
									    2=>array(null, true, false, 1, 1.0, "foo")
								),
								"Z"=>array( 
									    3=>$test_obj
								)
					  )), $ctrl=null);			
			
			// Execution will halt on the previous line if addL4() throws an exception
			$this->fail("Method addL4() failed to throw an exception on duplicate entry");
			
		}
		catch (FOX_exception $child) {}
		
		

		// Check cache state
		// ===============================================================	
		
		// NOTE: the LUT's won't be set at this point, because we haven't done any 
		// database reads that give objects authority
		
		$check = array(
				1=>array(   'keys'=>array(  'X'=>array(	'K'=>array(
										    1=>null,
										    2=>false,
										    5=>true 							    
									),
									'Z'=>array( 3=>(int)0 ) 						
							    ),	
							    'Y'=>array(	'K'=>array( 
										    1=>(int)1,
										    2=>(int)-1,
										    3=>(float)1.7 							    
									),
									'Z'=>array( 4=>(float)-1.6 ) 						
							    )
					    )
				),			
				2=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo")										    							    
									),
									'Z'=>array( 3=>$test_obj ) 						
							    )	
					    )						
				 )		    		    
		);
		
		$this->assertEquals($check, $this->cls->cache);	
		
		
		// Check db state
		// ===============================================================		
		
		$check = array(
				1=>array(   'X'=>array(	'K'=>array( 
								    1=>null,
								    2=>false,
								    5=>true							    
							),
							'Z'=>array( 3=>(int)0 ) 						
					    ),	
					    'Y'=>array(	'K'=>array( 
								    1=>(int)1,
								    2=>(int)-1,
								    3=>(float)1.7 							    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				),			
				2=>array(   'X'=>array(	'K'=>array( 
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo")								   						    
							),
							'Z'=>array( 3=>$test_obj ) 						
					    )					    
				)		    		    
		);		
		
		
		$db = new FOX_db();	
		
		$columns = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L4','L3','L2','L1')
		);
		
		try {
			$struct = $this->cls->_struct();			
			$result = $db->runSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
                $this->assertEquals($check, $result);		
		
		
	}	


       /**
	* Test fixture for addL4_multi() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	function test_addL4_multi() {

   
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array( "L4"=>1, "L3"=>array(   "X"=>array( "K"=>array( 
											1=>null,
											2=>false,
											5=>true
									    ),
									    "Z"=>array( 
											3=>(int)0
									    )
								),
								"Y"=>array( "K"=>array( 
											1=>(int)1,												
											2=>(int)-1,
											3=>(float)1.7
									    ),
									    "Z"=>array( 
											4=>(float)-1.6
									    )
								)),				    				   				    
				),
		    
		    		array( "L4"=>2, "L3"=>array(   "X"=>array( "K"=>array(
											1=>(string)"foo",
											2=>array(null, true, false, 1, 1.0, "foo")
									    ),
									    "Z"=>array( 
											3=>$test_obj
									    )
								))
				)				    
		);		
		
		
		// Load class with data
		// ===============================================================
				    					
		try {
			$set_ok = $this->cls->addL4_multi($test_data, $ctrl=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
		}			

		// Should return number of L1's added
		$this->assertEquals(11, $set_ok); 			
			
		
			
		// Test adding a single duplicate item
		// ===============================================================
		
		try {			
			
			$dupe_data = array(
			    
				array( "L4"=>1, "L3"=>array(   "X"=>array( "K"=>array( 
											1=>null,
											2=>false,
											5=>true
									    ),
									    "Z"=>array( 
											3=>(int)0
									    )
								),
								"Y"=>array( "K"=>array( 
											1=>(int)1,												
											2=>(int)-1,
											3=>(float)1.7
									    ),
									    "Z"=>array( 
											4=>(float)-1.6
									    )
								)),				    				   				    
				),
			);
		    
			$this->cls->addL4_multi($dupe_data, $ctrl=null);			
			
			// Execution will halt on the previous line if addL4_multi() throws an exception
			$this->fail("Method addL4_multi() failed to throw an exception on duplicate entry");	
			
		}
		catch (FOX_exception $child) {}
		
		
		// Test adding multiple duplicate items
		// ===============================================================
		
		try {			
			
			$dupe_data = array(
			    
				array( "L4"=>1, "L3"=>array(   "X"=>array( "K"=>array( 
											1=>null,
											2=>false,
											5=>true
									    ),
									    "Z"=>array( 
											3=>(int)0
									    )
								),
								"Y"=>array( "K"=>array( 
											1=>(int)1,												
											2=>(int)-1,
											3=>(float)1.7
									    ),
									    "Z"=>array( 
											4=>(float)-1.6
									    )
								)),				    				   				    
				),
		    
		    		array( "L4"=>2, "L3"=>array(   "X"=>array( "K"=>array( 
											1=>(string)"foo",
											2=>array(null, true, false, 1, 1.0, "foo")
									    ),
									    "Z"=>array( 
											3=>$test_obj
									    )
								))
				)
			);
		    
			$this->cls->addL4_multi($dupe_data, $ctrl=null);			
			
			// Execution will halt on the previous line if addL4_multi() throws an exception
			$this->fail("Method addL4_multi() failed to throw an exception on duplicate entry");	
			
		}
		catch (FOX_exception $child) {}
		
		
		
		// Check cache state
		// ===============================================================	
		
		// NOTE: the LUT's won't be set at this point, because we haven't done any 
		// database reads that give objects authority
		
		$check = array(
				1=>array(   'keys'=>array(  'X'=>array(	'K'=>array(
										    1=>null,
										    2=>false,
										    5=>true 							    
									),
									'Z'=>array( 3=>(int)0 ) 						
							    ),	
							    'Y'=>array(	'K'=>array( 
										    1=>(int)1,
										    2=>(int)-1,
										    3=>(float)1.7 							    
									),
									'Z'=>array( 4=>(float)-1.6 ) 						
							    )
					    )
				),			
				2=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo")										    							    
									),
									'Z'=>array( 3=>$test_obj ) 						
							    )	
					    )						
				 )		    		    
		);
		
		$this->assertEquals($check, $this->cls->cache);	
		
		
		// Check db state
		// ===============================================================		
		
		$check = array(
				1=>array(   'X'=>array(	'K'=>array( 
								    1=>null,
								    2=>false,
								    5=>true							    
							),
							'Z'=>array( 3=>(int)0 ) 						
					    ),	
					    'Y'=>array(	'K'=>array( 
								    1=>(int)1,
								    2=>(int)-1,
								    3=>(float)1.7 							    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				),			
				2=>array(   'X'=>array(	'K'=>array( 
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo")								   						    
							),
							'Z'=>array( 3=>$test_obj ) 						
					    )					    
				)		    		    
		);		
				
		$db = new FOX_db();	
		
		$columns = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L4','L3','L2','L1')
		);
		
		try {
			$struct = $this->cls->_struct();			
			$result = $db->runSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
                $this->assertEquals($check, $result);		
		
		
	}
	
	
       /**
	* Test fixture for addMulti() method (trie mode)
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	function test_addMulti_trie() {

   
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(
				1=>array(   'X'=>array(	'K'=>array( 
								    1=>null,
								    2=>false,
								    5=>true 							    
							),
							'Z'=>array( 3=>(int)0 ) 						
					    ),	
					    'Y'=>array(	'K'=>array( 
								    1=>(int)1,
								    2=>(int)-1,								  
								    3=>(float)1.7 						    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				),			
				2=>array(   'X'=>array(	'K'=>array(
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo")								    						    
							),
							'Z'=>array( 3=>$test_obj ) 						
					    )					    
				 )		    		    
		);		
		
		
		// Load class with data
		// ===============================================================
				    					
		try {
			$ctrl = array('mode'=>'trie');
			$set_ok = $this->cls->addMulti($test_data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
		}			

		// Should return number of L1's added
		$this->assertEquals(11, $set_ok); 			
			
		
		
		// Test adding some  duplicate itemes
		// ===============================================================
		
		try {			
		    
			$dupe_data = array(
					1=>array(   'X'=>array(	'K'=>array( 
									    1=>null,
									    2=>false,
									    5=>true 							    
								)						
						    ),	
						    'Y'=>array(	'K'=>array( 
									    1=>(int)1,
									    2=>(int)-1,
									    3=>(float)1.7 							    
								),
								'Z'=>array( 4=>(float)-1.6 ) 						
						    )					    
					)		    		    
			);
		    
			$ctrl = array('mode'=>'trie');			
			$this->cls->addMulti($dupe_data, $ctrl);			
			
			// Execution will halt on the previous line if addMulti() throws an exception
			$this->fail("Method addMulti() failed to throw an exception on duplicate entry");	
			
		}
		catch (FOX_exception $child) {}		
		
		
		
		// Check cache state
		// ===============================================================	
		
		// NOTE: the LUT's won't be set at this point, because we haven't done any 
		// database reads that give objects authority
		
		$check = array(
				1=>array(   'keys'=>array(  'X'=>array(	'K'=>array(
										    1=>null,
										    2=>false,
										    5=>true 							    
									),
									'Z'=>array( 3=>(int)0 ) 						
							    ),	
							    'Y'=>array(	'K'=>array( 
										    1=>(int)1,
										    2=>(int)-1,
										    3=>(float)1.7 							    
									),
									'Z'=>array( 4=>(float)-1.6 ) 						
							    )
					    )
				),			
				2=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo")										    							    
									),
									'Z'=>array( 3=>$test_obj ) 						
							    )	
					    )						
				 )		    		    
		);
		
		$this->assertEquals($check, $this->cls->cache);	
		
		
		// Check db state
		// ===============================================================		
		
		$check = array(
				1=>array(   'X'=>array(	'K'=>array( 
								    1=>null,
								    2=>false,
								    5=>true							    
							),
							'Z'=>array( 3=>(int)0 ) 						
					    ),	
					    'Y'=>array(	'K'=>array( 
								    1=>(int)1,
								    2=>(int)-1,
								    3=>(float)1.7 							    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				),			
				2=>array(   'X'=>array(	'K'=>array( 
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo")								   						    
							),
							'Z'=>array( 3=>$test_obj ) 						
					    )					    
				)		    		    
		);
		
		$db = new FOX_db();	
		
		$columns = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L4','L3','L2','L1')
		);
		
		try {
			$struct = $this->cls->_struct();			
			$result = $db->runSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
                $this->assertEquals($check, $result);		
		
		
	}
	
	
       /**
	* Test fixture for addMulti() method (matrix mode)
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	function test_addMulti_matrix() {

   
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";					
		
		$test_data = array(
				    array('L4'=>1, 'L3'=>'X', 'L2'=>'K', 'L1'=>1, 'L0'=>null),
				    array('L4'=>1, 'L3'=>'X', 'L2'=>'K', 'L1'=>2, 'L0'=>false),
				    array('L4'=>1, 'L3'=>'X', 'L2'=>'K', 'L1'=>5, 'L0'=>true),
				    array('L4'=>1, 'L3'=>'X', 'L2'=>'Z', 'L1'=>3, 'L0'=>(int)0),
				    array('L4'=>1, 'L3'=>'Y', 'L2'=>'K', 'L1'=>1, 'L0'=>(int)1),
				    array('L4'=>1, 'L3'=>'Y', 'L2'=>'K', 'L1'=>2, 'L0'=>(int)-1),
				    array('L4'=>1, 'L3'=>'Y', 'L2'=>'K', 'L1'=>3, 'L0'=>(float)1.7),
				    array('L4'=>1, 'L3'=>'Y', 'L2'=>'Z', 'L1'=>4, 'L0'=>(float)-1.6),
				    array('L4'=>2, 'L3'=>'X', 'L2'=>'K', 'L1'=>1, 'L0'=>(string)"foo"),
				    array('L4'=>2, 'L3'=>'X', 'L2'=>'K', 'L1'=>2, 'L0'=>array(null, true, false, 1, 1.0, "foo")),	
				    array('L4'=>2, 'L3'=>'X', 'L2'=>'Z', 'L1'=>3, 'L0'=>$test_obj)		    
		    		    
		);
		
		
		// Load class with data
		// ===============================================================
				    					
		try {
			$ctrl = array('mode'=>'matrix');
			$set_ok = $this->cls->addMulti($test_data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
		}			

		// Should return number of L1's added
		$this->assertEquals(11, $set_ok); 			
			
		
		
		// Test adding some  duplicate itemes
		// ===============================================================
		
		try {			
		    
			$dupe_data = array(
					    array('L4'=>1, 'L3'=>'X', 'L2'=>'K', 'L1'=>1, 'L0'=>null),
					    array('L4'=>1, 'L3'=>'X', 'L2'=>'K', 'L1'=>2, 'L0'=>false),
					    array('L4'=>1, 'L3'=>'X', 'L2'=>'K', 'L1'=>1, 'L0'=>true),
					    array('L4'=>1, 'L3'=>'X', 'L2'=>'Z', 'L1'=>3, 'L0'=>(int)0),
					    array('L4'=>1, 'L3'=>'Y', 'L2'=>'K', 'L1'=>1, 'L0'=>(int)1),
					    array('L4'=>1, 'L3'=>'Y', 'L2'=>'K', 'L1'=>2, 'L0'=>(int)-1),
					    array('L4'=>1, 'L3'=>'Y', 'L2'=>'K', 'L1'=>3, 'L0'=>(float)1.7),
					    array('L4'=>1, 'L3'=>'Y', 'L2'=>'Z', 'L1'=>4, 'L0'=>(float)-1.6),	    
			);
		    
			$ctrl = array('mode'=>'matrix');			
			$this->cls->addMulti($dupe_data, $ctrl);			
			
			// Execution will halt on the previous line if addMulti() throws an exception
			$this->fail("Method addMulti() failed to throw an exception on duplicate entry");	
			
		}
		catch (FOX_exception $child) {}		
		
		
		
		// Check cache state
		// ===============================================================	
		
		// NOTE: the LUT's won't be set at this point, because we haven't done any 
		// database reads that give objects authority
		
		$check = array(
				1=>array(   'keys'=>array(  'X'=>array(	'K'=>array(
										    1=>null,
										    2=>false,
										    5=>true 							    
									),
									'Z'=>array( 3=>(int)0 ) 						
							    ),	
							    'Y'=>array(	'K'=>array( 
										    1=>(int)1,
										    2=>(int)-1,
										    3=>(float)1.7 							    
									),
									'Z'=>array( 4=>(float)-1.6 ) 						
							    )
					    )
				),			
				2=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo")										    							    
									),
									'Z'=>array( 3=>$test_obj ) 						
							    )	
					    )						
				 )		    		    
		);
		
		$this->assertEquals($check, $this->cls->cache);	
		
		
		// Check db state
		// ===============================================================		
		
		$check = array(
				1=>array(   'X'=>array(	'K'=>array( 
								    1=>null,
								    2=>false,
								    5=>true							    
							),
							'Z'=>array( 3=>(int)0 ) 						
					    ),	
					    'Y'=>array(	'K'=>array( 
								    1=>(int)1,
								    2=>(int)-1,
								    3=>(float)1.7 							    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				),			
				2=>array(   'X'=>array(	'K'=>array( 
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo")								   						    
							),
							'Z'=>array( 3=>$test_obj ) 						
					    )					    
				)		    		    
		);	
		
		
		$db = new FOX_db();	
		
		$columns = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L4','L3','L2','L1')
		);
		
		try {
			$struct = $this->cls->_struct();			
			$result = $db->runSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
                $this->assertEquals($check, $result);		
		
		
	}	

	
	function tearDown() {
	   
		$this->cls = new FOX_dataStore_paged_L4_tester_addMethods();
		$unistall_ok = $this->cls->uninstall();
		
		$this->assertEquals(true, $unistall_ok);
		
		parent::tearDown();
	}

    
}

?>