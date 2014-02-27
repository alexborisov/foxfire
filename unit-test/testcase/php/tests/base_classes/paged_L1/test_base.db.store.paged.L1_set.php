<?php

/**
 * L1 PAGED ABSTRACT DATASTORE TEST CLASS
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

class FOX_dataStore_paged_L1_tester_setMethods extends FOX_dataStore_paged_L1_base {
    

	public static $struct = array(

		"table" => "FOX_dataStore_paged_L1_base",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_dataStore_paged_L1_base",
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
		
		// Generate our own cache singleton, and only enable the 'thread'
		// engine to eliminate potential problems with APC, Memcached, etc
		
		$this->mCache = new FOX_mCache();
		$this->mCache->setActiveEngines(array('thread'));
		
		$this->init();
		
	}
	
		
}  // ENDOF: class FOX_dataStore_paged_L1_tester_setMethods
  
                                      

/**
 * FOXFIRE UNIT TEST SCRIPT - L1 PAGED ABSTRACT DATASTORE CLASS - SET METHODS
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

class core_L1_paged_abstract_setMethods extends RAZ_testCase {

	
    	function setUp() {

	    
		parent::setUp();
				
		
		// Install the db table
		// ===========================================
		
		$this->cls = new FOX_dataStore_paged_L1_tester_setMethods();
		
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
	* Test fixture for setL1() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_setL1() {

	    
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
		
		foreach( $test_data as $item ){
		    						
			try {
				$set_ok = $this->cls->setL1($item['L5'], $item['L4'], $item['L3'], $item['L2'], $item['L1'], $item['L0'], $ctrl=null);
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
			$rows_changed = $this->cls->setL1(1, 'X', 'Z', 'Z', 3, 0, $ctrl=null);
			
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed); 	
			
		}
		catch (FOX_exception $child) {
			
			// Shouldn't throw an exception because the set() method allows
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));		    
		}			
			
		
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
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L5','L4','L3','L2','L1')
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
	* Test fixture for setL1_multi() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_setL1_multi() {

	    
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

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
		}			

		// Should return (int)1 to indicate a key was added
		$this->assertEquals(11, $rows_changed); 			
			
		
		// Test adding single duplicate item
		// ===============================================================
				
		$dupe_items = array(	

				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>(int)1),		    
		);
		
		try {
			$rows_changed = $this->cls->setL1_multi($dupe_items, $ctrl=null);
			
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed);
			
		}
		catch (FOX_exception $child) {
		    
			// Shouldn't throw an exception because the set() method allws
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			    		    
		}
		
		
		// Test adding multiple duplicate items
		// ===============================================================
				
		$dupe_items = array(

		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"T", "L1"=>3, "L0"=>(float)1.7),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"Z", "L2"=>"Z", "L1"=>4, "L0"=>(float)-1.6),		   
		    		array( "L5"=>2, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>1, "L0"=>(string)"foo"),
		    		array( "L5"=>2, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>2, "L0"=>array(null, true, false, 1, 1.0, "foo")),
		);
		
		try {
			$rows_changed = $this->cls->setL1_multi($dupe_items, $ctrl=null);
			
			// Execution will halt on the previous line if setL1_multi() throws an exception
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed);
			
		}
		catch (FOX_exception $child) {
		    
			// Shouldn't throw an exception because the set() method allows
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));		    		    
		}
		
		
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
	* Test fixture for setL2() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_setL2() {

    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1s"=>array(
												1=>null,
												2=>false
											)),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T", "L1s"=>array(
												1=>true
											)),   
				array( "L5"=>1, "L4"=>"X", "L3"=>"Z", "L2"=>"Z", "L1s"=>array(
												3=>(int)0
											)),
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1s"=>array(
												1=>(int)1,
												2=>(int)-1
											)),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"T", "L1s"=>array(
												3=>(float)1.7
											)),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"Z", "L2"=>"Z", "L1s"=>array(
												4=>(float)-1.6
											)),		    
		    		array( "L5"=>2, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1s"=>array(
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
											)),
		    		array( "L5"=>2, "L4"=>"X", "L3"=>"Z", "L2"=>"Z", "L1s"=>array(
												3=>$test_obj
											)),	
		    
		);		
		
		// Load class with data
		// ===============================================================
		
		foreach( $test_data as $item ){
		    						
			try {
				$rows_changed = $this->cls->setL2($item['L5'], $item['L4'], $item['L3'], $item['L2'], $item['L1s'], $ctrl=null);
			}
			catch (FOX_exception $child) {
							    
				$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
			}			
			
			// Should return number of L1's added
			$this->assertEquals(count($item['L1s']), $rows_changed, ("ITEM: " . $item['L5'] . $item['L4'] . $item['L3'] . $item['L2'])); 			
			
		}
		unset($item);
			
		
		
		// Test adding duplicate item
		// ===============================================================
		
		try {		    
		    
			$rows_changed = $this->cls->setL2(1, "X", "Z", "Z", array( 3=>(int)0 ), $ctrl=null);
			
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed);
			
		}
		catch (FOX_exception $child) {
		    
			// Shouldn't throw an exception because the set() method allws
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));		    		    		    
		}
		
		
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
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L5','L4','L3','L2','L1')
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
	* Test fixture for setL2_multi() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	function test_setL2_multi() {

	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>array(
												1=>null,
												2=>false
											)),
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"T", "L1"=>array(
												1=>true
											)),   
				array( "L5"=>1, "L4"=>"X", "L3"=>"Z", "L2"=>"Z", "L1"=>array(
												3=>(int)0
											)),
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>array(
												1=>(int)1,
												2=>(int)-1
											)),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"T", "L1"=>array(
												3=>(float)1.7
											)),
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"Z", "L2"=>"Z", "L1"=>array(
												4=>(float)-1.6
											)),		    
		    		array( "L5"=>2, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>array(
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
											)),
		    		array( "L5"=>2, "L4"=>"X", "L3"=>"Z", "L2"=>"Z", "L1"=>array(
												3=>$test_obj
											)),	
		    
		);		
		
		
		// Load class with data
		// ===============================================================
				    					
		try {
			$set_ok = $this->cls->setL2_multi($test_data, $ctrl=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
		}			

		// Should return number of L1's added
		$this->assertEquals(11, $set_ok); 			
										
		
		// Test adding single duplicate item
		// ===============================================================
				
		$dupe_items = array(
				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>"K", "L1"=>array(1=>null,2=>false))
		);
		
		try {
			$rows_changed = $this->cls->setL2_multi($dupe_items, $ctrl=null);
			
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed);
			
		}
		catch (FOX_exception $child) {
		    
			// Shouldn't throw an exception because the setL2_multi() method allows
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			    		    
		}
			
		// Test adding multiple duplicate items
		// ===============================================================
				
		$dupe_items = array(
				array( "L5"=>1, "L4"=>"X", "L3"=>"Z", "L2"=>"Z", "L1"=>array(3=>(int)0)),
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>"K", "L1"=>array(1=>(int)1,2=>(int)-1)),
		);
		
		try {
			$rows_changed = $this->cls->setL2_multi($dupe_items, $ctrl=null);
			
			// Execution will halt on the previous line if setL2_multi() throws an exception
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed);
			
		}
		catch (FOX_exception $child) {
		    
			// Shouldn't throw an exception because the set() method allws
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));		    		    
		}		
		
		
		
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
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L5','L4','L3','L2','L1')
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
	* Test fixture for setL3() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	function test_setL3() {

    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array( "count"=>3, "L5"=>1, "L4"=>"X", "L3"=>"K", "L2s"=>array( "K"=>array(
													    1=>null,
													    2=>false
												),
												"T"=>array(
													    1=>true
												))),				    		    		   
				array( "count"=>1, "L5"=>1, "L4"=>"X", "L3"=>"Z", "L2s"=>array( "Z"=>array(
													    3=>(int)0
												))),
		    
				array( "count"=>3, "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2s"=>array( "K"=>array(
													    1=>(int)1,												
													    2=>(int)-1
												),
												"T"=>array(
													    3=>(float)1.7
												))),
		    
		    		array( "count"=>1, "L5"=>1, "L4"=>"Y", "L3"=>"Z", "L2s"=>array( "Z"=>array(
													    4=>(float)-1.6
											    ))),		    		    
		    		array( "count"=>2, "L5"=>2, "L4"=>"X", "L3"=>"K", "L2s"=>array( "K"=>array(
													    1=>(string)"foo",
													    2=>array(null, true, false, 1, 1.0, "foo")
											    ))),
		    		array( "count"=>1, "L5"=>2, "L4"=>"X", "L3"=>"Z", "L2s"=>array( "Z"=>array(
													    3=>$test_obj
											    )))			    
		);	
		
		
		// Load class with data
		// ===============================================================
		
		foreach( $test_data as $item ){
		    						
			try {
				$rows_changed = $this->cls->setL3($item['L5'], $item['L4'], $item['L3'], $item['L2s'], $ctrl=null);
			}
			catch (FOX_exception $child) {
							    
				$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
			}			
			
			// Should return number of L1's added
			$this->assertEquals( $item['count'], $rows_changed, ("ITEM: " . $item['L5'] . $item['L4'] . $item['L3']) ); 
			
			
		}
		unset($item);
			
		
		// Test adding duplicate item
		// ===============================================================
		
		try {			
			$rows_changed = $this->cls->setL3(1, "X", "Z", array( "Z"=>array( 3=>(int)0) ), $ctrl=null);		
			
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed);	
			
		}
		catch (FOX_exception $child) {
		    
			// Shouldn't throw an exception because the set() method allows
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			    		    		    
		}		
		

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
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L5','L4','L3','L2','L1')
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
	* Test fixture for setL3_multi() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	function test_setL3_multi() {

	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array( "L5"=>1, "L4"=>"X", "L3"=>"K", "L2"=>array( "K"=>array(
												1=>null,
												2=>false
										    ),
										    "T"=>array(
												1=>true
										    ))),				    		    		   
				array( "L5"=>1, "L4"=>"X", "L3"=>"Z", "L2"=>array( "Z"=>array(
												3=>(int)0
										    ))),		    
				array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>array( "K"=>array(
												1=>(int)1,												
												2=>(int)-1
										    ),
										    "T"=>array(
												3=>(float)1.7
										    ))),		    
		    		array( "L5"=>1, "L4"=>"Y", "L3"=>"Z", "L2"=>array( "Z"=>array(
												4=>(float)-1.6
										    ))),		    		    
		    		array( "L5"=>2, "L4"=>"X", "L3"=>"K", "L2"=>array( "K"=>array(
												1=>(string)"foo",
												2=>array(null, true, false, 1, 1.0, "foo")
										    ))),
		    		array( "L5"=>2, "L4"=>"X", "L3"=>"Z", "L2"=>array( "Z"=>array(
												3=>$test_obj
										    )))			    
		);		
		
		
		// Load class with data
		// ===============================================================
				    					
		try {
			$set_ok = $this->cls->setL3_multi($test_data, $ctrl=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
		}			

		// Should return number of L1's added
		$this->assertEquals(11, $set_ok); 			
					
		
		
		// Test adding a single duplicate item
		// ===============================================================
		
		$dupe_items = array( array( "L5"=>1, "L4"=>"X", "L3"=>"Z", "L2"=>array( "Z"=>array(3=>(int)0 ))));
		
		try {		    		   
			$rows_changed  = $this->cls->setL3_multi($dupe_items, $ctrl=null);
			
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed);
			
		}
		catch (FOX_exception $child) {
		    
			// Shouldn't throw an exception because the set() method allows
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			    		    
		}
		
		
		// Test adding multiple duplicate items
		// ===============================================================
		
		$dupe_items = array(
		    
		array( "L5"=>1, "L4"=>"X", "L3"=>"Z", "L2"=>array( "Z"=>array(
										3=>(int)0
								    ))),		    
		array( "L5"=>1, "L4"=>"Y", "L3"=>"K", "L2"=>array( "K"=>array(
										1=>(int)1,												
										2=>(int)-1
								    ),
								    "T"=>array(
										3=>(float)1.7
								    )))
		);
		
		try {		    		   
			$rows_changed = $this->cls->setL3_multi($dupe_items, $ctrl=null);
			
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed);
			
		}
		catch (FOX_exception $child) {
		    
			// Shouldn't throw an exception because the set() method allows
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));		    		    
		}
		
		
		
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
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L5','L4','L3','L2','L1')
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
	* Test fixture for setL4() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	
	function test_setL4() {

    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array( "count"=>4, "L5"=>1, "L4"=>"X", "L3s"=>array(	"K"=>array( "K"=>array(
														1=>null,
														2=>false
												    ),
												    "T"=>array(
														1=>true
											)),
											"Z"=>array( "Z"=>array(
														3=>(int)0
											))
										)),
		    
				array( "count"=>4, "L5"=>1, "L4"=>"Y", "L3s"=>array(	"K"=>array( "K"=>array(
														1=>(int)1,												
														2=>(int)-1
												    ),
												    "T"=>array(
														3=>(float)1.7
											)),
											"Z"=>array( "Z"=>array(
														4=>(float)-1.6
											))
										)),
		    		    		    
		    		array( "count"=>3, "L5"=>2, "L4"=>"X", "L3s"=>array(	"K"=>array( "K"=>array(
														1=>(string)"foo",
														2=>array(null, true, false, 1, 1.0, "foo")
											)),
											"Z"=>array( "Z"=>array(
														3=>$test_obj
											))
										))		    		    
		);	
		
		
		// Load class with data
		// ===============================================================
		
		foreach( $test_data as $item ){
		    						
			try {
				$rows_changed = $this->cls->setL4($item['L5'], $item['L4'], $item['L3s'], $ctrl=null);
			}
			catch (FOX_exception $child) {
							    
				$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
			}			
			
			// Should return number of L1's added
			$this->assertEquals( $item['count'], $rows_changed, ("ITEM: " . $item['L5'] . $item['L4']) ); 
			
		}
		unset($item);
						
		
		// Test adding duplicate item
		// ===============================================================
		
		try {			
			$rows_changed = $this->cls->setL4(1, "Y", array(    "K"=>array( "K"=>array(
										    1=>(int)1,												
										    2=>(int)-1
									 ),
									 "T"=>array(
										    3=>(float)1.7
									 )),
							    "Z"=>array( "Z"=>array(
										    4=>(float)-1.6
					))), $ctrl=null);		
			
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed);	
			
		}
		catch (FOX_exception $child) {
		    
			// Shouldn't throw an exception because the set() method allows
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			    		    		    
		}
		
		
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
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L5','L4','L3','L2','L1')
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
	* Test fixture for setL4_multi() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	function test_setL4_multi() {

	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array(	"L5"=>1, "L4"=>"X", "L3"=>array(    "K"=>array( "K"=>array(
												    1=>null,
												    2=>false
											),
											"T"=>array(
												    1=>true
											)),
									    "Z"=>array( "Z"=>array(
												    3=>(int)0
											))
								)),
		    
				array(	"L5"=>1, "L4"=>"Y", "L3"=>array(    "K"=>array( "K"=>array(
												    1=>(int)1,												
												    2=>(int)-1
											),
											"T"=>array(
												    3=>(float)1.7
											)),
											"Z"=>array( "Z"=>array(
												    4=>(float)-1.6
											))
								)),
		    		    		    
		    		array(	"L5"=>2, "L4"=>"X", "L3"=>array(    "K"=>array( "K"=>array(
												    1=>(string)"foo",
												    2=>array(null, true, false, 1, 1.0, "foo")
											)),
									    "Z"=>array( "Z"=>array(
												    3=>$test_obj
											))
								))		    		    
		);		
		
		
		// Load class with data
		// ===============================================================
				    					
		try {
			$set_ok = $this->cls->setL4_multi($test_data, $ctrl=null);
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
			    
				array(	"L5"=>1, "L4"=>"Y", "L3"=>array(    "K"=>array( "K"=>array(
												    1=>(int)1,												
												    2=>(int)-1
											),
											"T"=>array(
												    3=>(float)1.7
											)),
											"Z"=>array( "Z"=>array(
												    4=>(float)-1.6
											))
								))
			);
		    
			$rows_changed = $this->cls->setL4_multi($dupe_data, $ctrl=null);			
			
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed);	
			
		}
		catch (FOX_exception $child) {
		    		    		    
			// Shouldn't throw an exception because the set() method allows
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
		}
		
		
		// Test adding multiple duplicate items
		// ===============================================================
		
		try {			
			
			$dupe_data = array(
			    
				array(	"L5"=>1, "L4"=>"X", "L3"=>array(    "K"=>array( "K"=>array(
												    1=>null,
												    2=>false
											),
											"T"=>array(
												    1=>true
											)),
									    "Z"=>array( "Z"=>array(
												    3=>(int)0
											))
								)),

				array(	"L5"=>1, "L4"=>"Y", "L3"=>array(    "K"=>array( "K"=>array(
												    1=>(int)1,												
												    2=>(int)-1
											),
											"T"=>array(
												    3=>(float)1.7
											)),
											"Z"=>array( "Z"=>array(
												    4=>(float)-1.6
											))
								))
			);
		    
			$rows_changed = $this->cls->setL4_multi($dupe_data, $ctrl=null);			
			
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed);		
			
		}
		catch (FOX_exception $child) {
		    		    
			// Shouldn't throw an exception because the set() method allows
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));
			
		}
				
		
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
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L5','L4','L3','L2','L1')
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
	* Test fixture for setL5() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	function test_setL5() {

    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array( "count"=>8, "L5"=>1, "L4s"=>array(   "X"=>array(	"K"=>array( "K"=>array(
														1=>null,
														2=>false
												    ),
												    "T"=>array(
														1=>true
											)),
											"Z"=>array( "Z"=>array(
														3=>(int)0
											))
									    ),
									    "Y"=>array(	"K"=>array( "K"=>array(
														1=>(int)1,												
														2=>(int)-1
												    ),
												    "T"=>array(
														3=>(float)1.7
											)),
											"Z"=>array( "Z"=>array(
														4=>(float)-1.6
											))
									    )),				    				   				    
				),
		    
		    		array( "count"=>3, "L5"=>2, "L4s"=>array(   "X"=>array(	"K"=>array( "K"=>array(
														1=>(string)"foo",
														2=>array(null, true, false, 1, 1.0, "foo")
											)),
											"Z"=>array( "Z"=>array(
														3=>$test_obj
											))
									    ))
				)				    
		);	
		
		
		// Load class with data
		// ===============================================================
		
		foreach( $test_data as $item ){
		    						
			try {
				$rows_changed = $this->cls->setL5($item['L5'], $item['L4s'],  $ctrl=null);
			}
			catch (FOX_exception $child) {
							    
				$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
			}			
			
			// Should return number of L1's added
			$this->assertEquals( $item['count'], $rows_changed, ("ITEM: " . $item['L5']) ); 
			
			
		}
		unset($item);
			
		
		// Test adding a single duplicate item
		// ===============================================================
		
		try {			
			
			$rows_changed = $this->cls->setL5(2, array( "X"=>array(	"K"=>array( "K"=>array(
											1=>(string)"foo",
											2=>array(null, true, false, 1, 1.0, "foo")
										)),
								"Z"=>array( "Z"=>array(
											3=>$test_obj
					)))), $ctrl=null);			
			
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed);
			
		}
		catch (FOX_exception $child) {
		    
			// Shouldn't throw an exception because the set() method allows
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));		    		    
		}
				

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
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L5','L4','L3','L2','L1')
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
	* Test fixture for setL5_multi() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	function test_setL5_multi() {

   
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(

				array( "L5"=>1, "L4"=>array(   "X"=>array( "K"=>array( "K"=>array(
												    1=>null,
												    2=>false
											),
											"T"=>array(
												    1=>true
									    )),
									    "Z"=>array( "Z"=>array(
												    3=>(int)0
									    ))
								),
								"Y"=>array( "K"=>array( "K"=>array(
												    1=>(int)1,												
												    2=>(int)-1
											),
											"T"=>array(
												    3=>(float)1.7
									    )),
									    "Z"=>array( "Z"=>array(
												    4=>(float)-1.6
									    ))
								)),				    				   				    
				),
		    
		    		array( "L5"=>2, "L4"=>array(   "X"=>array( "K"=>array( "K"=>array(
												    1=>(string)"foo",
												    2=>array(null, true, false, 1, 1.0, "foo")
									    )),
									    "Z"=>array( "Z"=>array(
												    3=>$test_obj
									    ))
								))
				)				    
		);		
		
		
		// Load class with data
		// ===============================================================
				    					
		try {
			$set_ok = $this->cls->setL5_multi($test_data, $ctrl=null);
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
			    
				array( "L5"=>1, "L4"=>array(   "X"=>array( "K"=>array( "K"=>array(
												    1=>null,
												    2=>false
											),
											"T"=>array(
												    1=>true
									    )),
									    "Z"=>array( "Z"=>array(
												    3=>(int)0
									    ))
								),
								"Y"=>array( "K"=>array( "K"=>array(
												    1=>(int)1,												
												    2=>(int)-1
											),
											"T"=>array(
												    3=>(float)1.7
									    )),
									    "Z"=>array( "Z"=>array(
												    4=>(float)-1.6
									    ))
								)),				    				   				    
				),
			);
		    
			$rows_changed = $this->cls->setL5_multi($dupe_data, $ctrl=null);			
			
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed);
			
		}
		catch (FOX_exception $child) {
		    
			// Shouldn't throw an exception because the set() method allows
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));		    		    		    
		}
		
		
		// Test adding multiple duplicate items
		// ===============================================================
		
		try {			
			
			$dupe_data = array(
			    
				array( "L5"=>1, "L4"=>array(   "X"=>array( "K"=>array( "K"=>array(
												    1=>null,
												    2=>false
											),
											"T"=>array(
												    1=>true
									    )),
									    "Z"=>array( "Z"=>array(
												    3=>(int)0
									    ))
								),
								"Y"=>array( "K"=>array( "K"=>array(
												    1=>(int)1,												
												    2=>(int)-1
											),
											"T"=>array(
												    3=>(float)1.7
									    )),
									    "Z"=>array( "Z"=>array(
												    4=>(float)-1.6
									    ))
								)),				    				   				    
				),
		    
		    		array( "L5"=>2, "L4"=>array(   "X"=>array( "K"=>array( "K"=>array(
												    1=>(string)"foo",
												    2=>array(null, true, false, 1, 1.0, "foo")
									    )),
									    "Z"=>array( "Z"=>array(
												    3=>$test_obj
									    ))
								))
				)
			);
		    
			$rows_changed = $this->cls->setL5_multi($dupe_data, $ctrl=null);			
			
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed);	
			
		}
		catch (FOX_exception $child) {		    
		    
			// Shouldn't throw an exception because the set() method allows
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));		    
		}
		
		
		
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
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L5','L4','L3','L2','L1')
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
	* Test fixture for setMulti() method (trie mode)
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	function test_setMulti_trie() {

   
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";	
				
		$test_data = array(
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
		
		
		// Load class with data
		// ===============================================================
				    					
		try {
			$ctrl = array('mode'=>'trie');
			$rows_changed = $this->cls->setMulti($test_data, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
		}			

		// Should return number of L1's added
		$this->assertEquals(11, $rows_changed); 			
					
		
		
		// Test adding some duplicate itemes
		// ===============================================================
		
		try {			
		    
			$dupe_data = array(
					1=>array(   'X'=>array(	'K'=>array( 'K'=>array(	
											1=>null,
											2=>false
									    ),
									    'T'=>array(	1=>true )							    
								)						
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
		    
			$ctrl = array('mode'=>'trie');
			$rows_changed = $this->cls->setMulti($dupe_data, $ctrl);			
			
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed);
			
			
		}
		catch (FOX_exception $child) {
		    
			// Shouldn't throw an exception because the set() method allows
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));		    
		    		    		    
		}		
		
		
		
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
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L5','L4','L3','L2','L1')
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
	* Test fixture for setMulti() method (matrix mode)
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/
	
	function test_setMulti_matrix() {

   
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";					
		
		$test_data = array(
				    array('L5'=>1, 'L4'=>'X', 'L3'=>'K', 'L2'=>'K', 'L1'=>1, 'L0'=>null),
				    array('L5'=>1, 'L4'=>'X', 'L3'=>'K', 'L2'=>'K', 'L1'=>2, 'L0'=>false),
				    array('L5'=>1, 'L4'=>'X', 'L3'=>'K', 'L2'=>'T', 'L1'=>1, 'L0'=>true),
				    array('L5'=>1, 'L4'=>'X', 'L3'=>'Z', 'L2'=>'Z', 'L1'=>3, 'L0'=>(int)0),
				    array('L5'=>1, 'L4'=>'Y', 'L3'=>'K', 'L2'=>'K', 'L1'=>1, 'L0'=>(int)1),
				    array('L5'=>1, 'L4'=>'Y', 'L3'=>'K', 'L2'=>'K', 'L1'=>2, 'L0'=>(int)-1),
				    array('L5'=>1, 'L4'=>'Y', 'L3'=>'K', 'L2'=>'T', 'L1'=>3, 'L0'=>(float)1.7),
				    array('L5'=>1, 'L4'=>'Y', 'L3'=>'Z', 'L2'=>'Z', 'L1'=>4, 'L0'=>(float)-1.6),
				    array('L5'=>2, 'L4'=>'X', 'L3'=>'K', 'L2'=>'K', 'L1'=>1, 'L0'=>(string)"foo"),
				    array('L5'=>2, 'L4'=>'X', 'L3'=>'K', 'L2'=>'K', 'L1'=>2, 'L0'=>array(null, true, false, 1, 1.0, "foo")),	
				    array('L5'=>2, 'L4'=>'X', 'L3'=>'Z', 'L2'=>'Z', 'L1'=>3, 'L0'=>$test_obj)		    
		    		    
		);
		
		
		// Load class with data
		// ===============================================================
				    					
		try {
			$ctrl = array('mode'=>'matrix');
			$set_ok = $this->cls->setMulti($test_data, $ctrl);
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
					    array('L5'=>1, 'L4'=>'X', 'L3'=>'K', 'L2'=>'K', 'L1'=>1, 'L0'=>null),
					    array('L5'=>1, 'L4'=>'X', 'L3'=>'K', 'L2'=>'K', 'L1'=>2, 'L0'=>false),
					    array('L5'=>1, 'L4'=>'X', 'L3'=>'K', 'L2'=>'T', 'L1'=>1, 'L0'=>true),
					    array('L5'=>1, 'L4'=>'X', 'L3'=>'Z', 'L2'=>'Z', 'L1'=>3, 'L0'=>(int)0),
					    array('L5'=>1, 'L4'=>'Y', 'L3'=>'K', 'L2'=>'K', 'L1'=>1, 'L0'=>(int)1),
					    array('L5'=>1, 'L4'=>'Y', 'L3'=>'K', 'L2'=>'K', 'L1'=>2, 'L0'=>(int)-1),
					    array('L5'=>1, 'L4'=>'Y', 'L3'=>'K', 'L2'=>'T', 'L1'=>3, 'L0'=>(float)1.7),
					    array('L5'=>1, 'L4'=>'Y', 'L3'=>'Z', 'L2'=>'Z', 'L1'=>4, 'L0'=>(float)-1.6),	    
			);
		    
			$ctrl = array('mode'=>'matrix');			
			$rows_changed = $this->cls->setMulti($dupe_data, $ctrl);			
			
			// Should return (int)0 to indicate no rows were changed
			$this->assertEquals(0, $rows_changed);	
			
		}
		catch (FOX_exception $child) {
		    
			// Shouldn't throw an exception because the set() method allows
			// existing rows to be overwritten 	
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));		    		    
		}		
		
		
		
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
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('L5','L4','L3','L2','L1')
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
	   
		$this->cls = new FOX_dataStore_paged_L1_tester_setMethods();
		$unistall_ok = $this->cls->uninstall();
		
		$this->assertEquals(true, $unistall_ok);
		
		parent::tearDown();
	}

    
}

?>