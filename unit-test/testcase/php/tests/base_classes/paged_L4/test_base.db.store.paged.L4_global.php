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

class FOX_dataStore_paged_L4_tester_globalMethods extends FOX_dataStore_paged_L4_base {
    

	public static $struct = array(

		"table" => "FOX_dataStore_paged_L4_base",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_dataStore_paged_L4_base",
		"cache_strategy" => "paged",
		"cache_engine" => array("memcached", "redis", "apc", "thread"),	    
		"columns" => array(
		    "L4" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",	"auto_inc"=>false,  "default"=>null,
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
	
	
}  // ENDOF: class FOX_dataStore_paged_L4_tester_globalMethods 

                                      

/**
 * FOXFIRE UNIT TEST SCRIPT - L4 PAGED ABSTRACT DATASTORE CLASS - GLOBAL METHODS
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

class core_L4_paged_abstract_globalMethods extends RAZ_testCase {

	
    	function setUp() {

	    
		parent::setUp();
				
		
		// Install the db table
		// ===========================================
		
		$this->cls = new FOX_dataStore_paged_L4_tester_globalMethods();
		
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

				array( "L4"=>'A', "L3"=>"X", "L2"=>"K", "L1"=>1, "L0"=>null),
				array( "L4"=>'A', "L3"=>"X", "L2"=>"K", "L1"=>2, "L0"=>false),
				array( "L4"=>'A', "L3"=>"X", "L2"=>"K", "L1"=>5, "L0"=>true),
				array( "L4"=>'A', "L3"=>"X", "L2"=>"Z", "L1"=>3, "L0"=>(int)0),	

				array( "L4"=>'A', "L3"=>"Y", "L2"=>"K", "L1"=>1, "L0"=>(int)1),
				array( "L4"=>'A', "L3"=>"Y", "L2"=>"K", "L1"=>2, "L0"=>(int)-1),
		    		array( "L4"=>'A', "L3"=>"Y", "L2"=>"K", "L1"=>3, "L0"=>(float)1.7),
		    		array( "L4"=>'A', "L3"=>"Y", "L2"=>"Z", "L1"=>4, "L0"=>(float)-1.6),
		    
		    		array( "L4"=>'B', "L3"=>"X", "L2"=>"K", "L1"=>1, "L0"=>(string)"foo"),
		    		array( "L4"=>'B', "L3"=>"X", "L2"=>"K", "L1"=>2, "L0"=>array(null, true, false, 1, 1.0, "foo")),
		    		array( "L4"=>'B', "L3"=>"X", "L2"=>"Z", "L1"=>3, "L0"=>$test_obj),
		    
				array( "L4"=>'C', "L3"=>"X", "L2"=>"K", "L1"=>1, "L0"=>null),
				array( "L4"=>'C', "L3"=>"X", "L2"=>"K", "L1"=>2, "L0"=>false),
				array( "L4"=>'C', "L3"=>"X", "L2"=>"K", "L1"=>5, "L0"=>true),
				array( "L4"=>'C', "L3"=>"X", "L2"=>"Z", "L1"=>3, "L0"=>(int)0),	

				array( "L4"=>'C', "L3"=>"Y", "L2"=>"K", "L1"=>1, "L0"=>(int)1),
				array( "L4"=>'C', "L3"=>"Y", "L2"=>"K", "L1"=>2, "L0"=>(int)-1),
		    		array( "L4"=>'C', "L3"=>"Y", "L2"=>"K", "L1"=>3, "L0"=>(float)1.7),
		    		array( "L4"=>'C', "L3"=>"Y", "L2"=>"Z", "L1"=>4, "L0"=>(float)-1.6),		    
		    
		);		
		
		// Load class with data
		// ####################################################################
				    					
		try {
			$rows_changed = $this->cls->setL1_multi($test_data, $ctrl=null);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));			
		}			

		// Should return (int)19 to indicate  19 keys were added
		$this->assertEquals(19, $rows_changed); 								
		
		
		// Check cache state
		// ####################################################################	
		
		// NOTE: the LUT's won't be set at this point, because we haven't done any 
		// database reads that give objects authority
		
		$check = array(
				'A'=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 
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
				'B'=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 
										    1=>(string)"foo",
										    2=>array(null, true, false, 1, 1.0, "foo")										    							    
									),
									'Z'=>array( 3=>$test_obj ) 						
							    )	
					    )						
				),
				'C'=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 
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
				)		    
		);
		
		$this->assertEquals($check, $this->cls->cache);	
		
		
		// Check db state
		// ####################################################################		
		
		$check = array(
				'A'=>array(   'X'=>array(	'K'=>array( 
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
				'B'=>array(   'X'=>array(	'K'=>array( 
								    1=>(string)"foo",
								    2=>array(null, true, false, 1, 1.0, "foo")								    							    
							),
							'Z'=>array( 3=>$test_obj ) 						
					    )					    
				),
				'C'=>array(   'X'=>array(	'K'=>array( 	
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
	* Test fixture for dropGlobal() method, L1, single item
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropGlobal_L1_single() {
	    

		self::loadData();			
	    
		
		// Drop objects
		// ####################################################################

		$drop_ctrl = array(
			"validate"=>true
		);
		
		try {			
			$rows_changed = $this->cls->dropGlobal(1, 1, $drop_ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Should report 5 rows were dropped
		$this->assertEquals(5, $rows_changed);
		
		
		// Verify db state
		// ####################################################################
		
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
		
		// NOTE: the datastore will automatically clip empty branches
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(
				'A'=>array(   'X'=>array(	'K'=>array( 
								    2=>false,
								    5=>true 							    
							),
							'Z'=>array( 3=>(int)0 ) 						
					    ),	
					    'Y'=>array(	'K'=>array( 
								    2=>(int)-1,
								    3=>(float)1.7 							    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				),			
				'B'=>array(   'X'=>array(	'K'=>array( 
								    2=>array(null, true, false, 1, 1.0, "foo")								    							    
							),
							'Z'=>array( 3=>$test_obj ) 						
					    )					    
				),
				'C'=>array(   'X'=>array(	'K'=>array( 	
								    2=>false,
								    5=>true 							    
							),
							'Z'=>array( 3=>(int)0 ) 						
					    ),	
					    'Y'=>array(	'K'=>array( 
								    2=>(int)-1,
								    3=>(float)1.7 							    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				)		    
		);	
		
                $this->assertEquals($check, $result);
		
		
		// Check class cache state
		// ####################################################################					
		
		$check_cache = array();		
                $this->assertEquals($check_cache, $this->cls->cache);		

		
		// Verify persistent cache state by reading-back all items
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
		
		$this->assertEquals(true, $valid);  // Should report valid because all
						    // requested L5's exist
		
		$this->assertEquals($check, $result);
		
		
	}
	
	
       /**
	* Test fixture for dropGlobal() method, L1, multiple items
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropGlobal_L1_multi() {
	    

		self::loadData();			
	    
		
		// Drop objects
		// ####################################################################

		$drop_ctrl = array(
			"validate"=>true
		);
		
		try {			
			$rows_changed = $this->cls->dropGlobal(1, array(1,2), $drop_ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Should report 12 rows were dropped
		$this->assertEquals(10, $rows_changed);
		
		
		// Verify db state
		// ####################################################################
		
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
		
		// NOTE: the datastore will automatically clip empty branches
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(
				'A'=>array(   'X'=>array(	'K'=>array( 
								    5=>true 							    
							),
							'Z'=>array( 3=>(int)0 ) 						
					    ),	
					    'Y'=>array(	'K'=>array( 
								    3=>(float)1.7 							    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				),			
				'B'=>array(   'X'=>array(	'Z'=>array( 3=>$test_obj ) ) ),
				'C'=>array(   'X'=>array(	'K'=>array( 	
								    5=>true 							    
							),
							'Z'=>array( 3=>(int)0 ) 						
					    ),	
					    'Y'=>array(	'K'=>array( 
								    3=>(float)1.7 							    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				)		    
		);	
		
                $this->assertEquals($check, $result);
		
		
		// Check class cache state
		// ####################################################################					
		
		$check_cache = array();		
                $this->assertEquals($check_cache, $this->cls->cache);		

		
		// Verify persistent cache state by reading-back all items
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
		
		$this->assertEquals(true, $valid);  // Should report valid because all
						    // requested L5's exist
		
		$this->assertEquals($check, $result);
		
		
	}
	


       /**
	* Test fixture for dropGlobal() method, L2, single item
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropGlobal_L2_single() {
	    

		self::loadData();			
	    
		
		// Drop objects
		// ####################################################################

		$drop_ctrl = array(
			"validate"=>true
		);
		
		try {			
			$rows_changed = $this->cls->dropGlobal(2, 'K', $drop_ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Should report 14 rows were dropped
		$this->assertEquals(14, $rows_changed);
		
		
		// Verify db state
		// ####################################################################
		
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
		
		// NOTE: the datastore will automatically clip empty branches
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(
				'A'=>array(   'X'=>array(	'Z'=>array( 3=>(int)0 ) ),	
					    'Y'=>array(	'Z'=>array( 4=>(float)-1.6 )) 											    					    
				),			
				'B'=>array(   'X'=>array(	'Z'=>array( 3=>$test_obj )) 						
					    					    
				),
				'C'=>array(   'X'=>array(	'Z'=>array( 3=>(int)0 ) ),											   	
					    'Y'=>array(	'Z'=>array( 4=>(float)-1.6 )) 											    					    
				)		    
		);
		
                $this->assertEquals($check, $result);
		
		
		// Check class cache state
		// ####################################################################					
		
		$check_cache = array();		
                $this->assertEquals($check_cache, $this->cls->cache);		

		
		// Verify persistent cache state by reading-back all items
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
		
		$this->assertEquals(true, $valid);  // Should report valid because all
						    // requested L4's exist
		
		$this->assertEquals($check, $result);
		
		
	}
	
	
       /**
	* Test fixture for dropGlobal() method, L3, multi item
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropGlobal_L2_multi() {
	    

		self::loadData();			
	    
		
		// Drop objects
		// ####################################################################

		$drop_ctrl = array(
			"validate"=>true
		);
		
		try {			
			$rows_changed = $this->cls->dropGlobal(2, array('K','Z'), $drop_ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Should report 19 rows were dropped
		$this->assertEquals(19, $rows_changed);
		
		
		// Verify db state
		// ####################################################################
		
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
		
		// This will clear the entire datastore
		
                $this->assertEquals(null, $result);
		
		
		// Check class cache state
		// ####################################################################					
		
		$check_cache = array();		
                $this->assertEquals($check_cache, $this->cls->cache);		

		
		// Verify persistent cache state by reading-back all items
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
		
		$this->assertEquals(false, $valid);	// Should report invalid because 
							// requested L4's don't exist
		
		$this->assertEquals(array(), $result);
		
		
	}
	
	
       /**
	* Test fixture for dropGlobal() method, L3, single item
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropGlobal_L3_single() {
  

		self::loadData();			
	    
		
		// Drop objects
		// ####################################################################

		$drop_ctrl = array(
			"validate"=>true
		);
		
		try {			
			$rows_changed = $this->cls->dropGlobal(3, 'X', $drop_ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Should report 11 rows were dropped
		$this->assertEquals(11, $rows_changed);
		
		
		// Verify db state
		// ####################################################################
		
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
		
		// NOTE: the datastore will automatically clip empty branches
		
		$check = array(
				'A'=>array(   'Y'=>array(	'K'=>array( 
								    1=>(int)1,
								    2=>(int)-1,
								    3=>(float)1.7 						    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				),			
				'C'=>array(   'Y'=>array(	'K'=>array( 
								    1=>(int)1,
								    2=>(int)-1,
								    3=>(float)1.7 							    
							),
							'Z'=>array( 4=>(float)-1.6 ) 						
					    )					    
				)		    
		);
		
                $this->assertEquals($check, $result);
		
		
		// Check class cache state
		// ####################################################################					
		
		$check_cache = array();		
                $this->assertEquals($check_cache, $this->cls->cache);		

		
		// Verify persistent cache state by reading-back all items
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
		
		$this->assertEquals(false, $valid);	// Should report invalid because 
							// a requested L5 doesn't exist
		
		$this->assertEquals($check, $result);
		
		
	}
	
	
       /**
	* Test fixture for dropGlobal() method, L4, multi item
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropGlobal_L3_multi() {
  

		self::loadData();			
	    
		
		// Drop objects
		// ####################################################################

		$drop_ctrl = array(
			"validate"=>true
		);
		
		try {			
			$rows_changed = $this->cls->dropGlobal(3, array('X','Y'), $drop_ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		// Should report 19 rows were dropped
		$this->assertEquals(19, $rows_changed);
		
		
		// Verify db state
		// ####################################################################
		
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
		
                $this->assertEquals(null, $result);
		
		
		// Check class cache state
		// ####################################################################					
		
		$check_cache = array();		
                $this->assertEquals($check_cache, $this->cls->cache);		

		
		// Verify persistent cache state by reading-back all items
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
		
		$this->assertEquals(false, $valid);	// Should report invalid because 
							// a requested L5 doesn't exist
		
		$this->assertEquals(array(), $result);
		
		
	}
	
	
	function tearDown() {
	   
		$this->cls = new FOX_dataStore_paged_L4_tester_globalMethods();
		$unistall_ok = $this->cls->uninstall();
		
		$this->assertEquals(true, $unistall_ok);
		
		parent::tearDown();
	}

    
}

?>