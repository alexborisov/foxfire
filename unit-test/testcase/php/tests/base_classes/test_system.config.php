<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - CONFIG CLASS
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


class system_config extends RAZ_testCase {


    	function setUp() {

	    
		parent::setUp();
				
		
		// Install the db table
		// ===========================================
		
		$this->cls = new FOX_config();
		
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
	* Loads the storage class with data
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

		    array( "plugin"=>'plugin_1', "tree"=>"X", "branch"=>"K", "node"=>"N1", "filter"=>"debug", "ctrl"=>false, "val"=>null),
		    array( "plugin"=>'plugin_1', "tree"=>"X", "branch"=>"K", "node"=>"N2", "filter"=>"debug", "ctrl"=>false, "val"=>false),
		    array( "plugin"=>'plugin_1', "tree"=>"X", "branch"=>"K", "node"=>"N5", "filter"=>"debug", "ctrl"=>false, "val"=>true),
		    array( "plugin"=>'plugin_1', "tree"=>"X", "branch"=>"Z", "node"=>"N3", "filter"=>"debug", "ctrl"=>false, "val"=>(int)0),	

		    array( "plugin"=>'plugin_1', "tree"=>"Y", "branch"=>"K", "node"=>"N1", "filter"=>"debug", "ctrl"=>false, "val"=>(int)1),
		    array( "plugin"=>'plugin_1', "tree"=>"Y", "branch"=>"K", "node"=>"N2", "filter"=>"debug", "ctrl"=>false, "val"=>(int)-1),
		    array( "plugin"=>'plugin_1', "tree"=>"Y", "branch"=>"K", "node"=>"N3", "filter"=>"debug", "ctrl"=>false, "val"=>(float)1.7),
		    array( "plugin"=>'plugin_1', "tree"=>"Y", "branch"=>"Z", "node"=>"N4", "filter"=>"debug", "ctrl"=>false, "val"=>(float)-1.6),

		    array( "plugin"=>'plugin_2', "tree"=>"X", "branch"=>"K", "node"=>"N1", "filter"=>"debug", "ctrl"=>false, "val"=>(string)"foo"),
		    array( "plugin"=>'plugin_2', "tree"=>"X", "branch"=>"K", "node"=>"N2", "filter"=>"debug", "ctrl"=>false, "val"=>array(null, true, false, 1, 1.0, "foo")),
		    array( "plugin"=>'plugin_2', "tree"=>"X", "branch"=>"Z", "node"=>"N3", "filter"=>"debug", "ctrl"=>false, "val"=>$test_obj)	
		    
		);		
		
		// Load class with data
		// ===============================================================
		
		foreach( $test_data as $item ){
		    						
			try { 
				$rows_changed = $this->cls->addNode(	$item['plugin'], 
									$item['tree'], 
									$item['branch'], 
									$item['node'], 
									$item['val'], 
									$item['filter'],
									$item['ctrl']
				);			    
			}
			catch (FOX_exception $child) {
							    
				$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));			
			}			
			
			// Should return (int)1 to indicate a node was added
			$this->assertEquals(1, $rows_changed); 			
			
		}
		unset($item);

		
		// Check db state
		// ===============================================================		
		
		$check = array(
				"plugin_1"=>array(  'X'=>array( 'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>null
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>false
									    ),
									    'N5'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>true
									    ),										
								),
								'Z'=>array( 'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)0
									    )
								)
						    ),	
						    'Y'=>array(	'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)1
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)-1
									    ),
									    'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(float)1.7
									    )							    
								),
								'Z'=>array( 'N4'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(float)-1.6
									    )
								)
						    )					    
				),			
				"plugin_2"=>array(  'X'=>array(	'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(string)"foo"
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>array(null, true, false, 1, 1.0, "foo")
									    )								   						    
								),
								'Z'=>array( 'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>$test_obj
									    )
								) 						
						    )					    
				)		    		    
		);	
		
		$db = new FOX_db();	
		
		$columns = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('plugin','tree','branch','node')
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
	* Test fixture for addNode() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_addNode() {

	    
		self::loadData();
		
		
		// Test overwriting an existing node with same data
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	"plugin_1", 
								"X", 
								"K", 
								"N1", 
								null, 
								"debug",
								false
			);					
						
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));		    
		}			

		// Should return (int)0 to indicate no rows were changed
		$this->assertEquals(0, $rows_changed);	
		
		
		// Check db state
		// ===============================================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(
				"plugin_1"=>array(  'X'=>array( 'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>null
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>false
									    ),
									    'N5'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>true
									    ),										
								),
								'Z'=>array( 'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)0
									    )
								)
						    ),	
						    'Y'=>array(	'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)1
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)-1
									    ),
									    'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(float)1.7
									    )							    
								),
								'Z'=>array( 'N4'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(float)-1.6
									    )
								)
						    )					    
				),			
				"plugin_2"=>array(  'X'=>array(	'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(string)"foo"
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>array(null, true, false, 1, 1.0, "foo")
									    )								   						    
								),
								'Z'=>array( 'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>$test_obj
									    )
								) 						
						    )					    
				)		    		    
		);	
		
		$db = new FOX_db();	
		
		$columns = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('plugin','tree','branch','node')
		);
		
		try {
			$struct = $this->cls->_struct();			
			$result = $db->runSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
                $this->assertEquals($check, $result);		
		
		
		// Test overwriting an existing node with different data
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	"plugin_1", 
								"X", 
								"K", 
								"N1", 
								"updated_value", 
								"debug",
								false
			);
						
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));		    
		}			

		// Should return (int)2 to indicate a node was updated
		$this->assertEquals(2, $rows_changed);		
		
		
		// Check db state
		// ===============================================================		
		
		$check = array(
				"plugin_1"=>array(  'X'=>array( 'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>'updated_value'
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>false
									    ),
									    'N5'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>true
									    ),										
								),
								'Z'=>array( 'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)0
									    )
								)
						    ),	
						    'Y'=>array(	'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)1
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)-1
									    ),
									    'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(float)1.7
									    )							    
								),
								'Z'=>array( 'N4'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(float)-1.6
									    )
								)
						    )					    
				),			
				"plugin_2"=>array(  'X'=>array(	'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(string)"foo"
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>array(null, true, false, 1, 1.0, "foo")
									    )								   						    
								),
								'Z'=>array( 'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>$test_obj
									    )
								) 						
						    )					    
				)		    		    
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
	* Test fixture for addNode(), data integrity checks
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_addNode_dataIntegrity() {

	    
		self::loadData();

		
		// Null plugin name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	null, 
								"X", 
								"K", 
								"N1", 
								null, 
								"debug",
								false
			);
			
			// Execution will halt on the previous line if addNode() throws an exception
			$this->fail("Method addNode() failed to throw an exception on invalid plugin name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer plugin name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	1, 
								"X", 
								"K", 
								"N1", 
								null, 
								"debug",
								false
			);
			
			// Execution will halt on the previous line if addNode() throws an exception
			$this->fail("Method addNode() failed to throw an exception on invalid plugin name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped plugin name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	"1", 
								"X", 
								"K", 
								"N1", 
								null, 
								"debug",
								false
			);
			
			// Execution will halt on the previous line if addNode() throws an exception
			$this->fail("Method addNode() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}			
		
		
		// Null tree name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	"plugin_3", 
								null, 
								"K", 
								"N1", 
								null, 
								"debug",
								false
			);
			
			// Execution will halt on the previous line if addNode() throws an exception
			$this->fail("Method addNode() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer tree name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	"plugin_3", 
								1, 
								"K", 
								"N1", 
								null, 
								"debug",
								false
			);
			
			// Execution will halt on the previous line if addNode() throws an exception
			$this->fail("Method addNode() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped tree name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	"plugin_3", 
								"1", 
								"K", 
								"N1", 
								null, 
								"debug",
								false
			);
			
			// Execution will halt on the previous line if addNode() throws an exception
			$this->fail("Method addNode() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}	
		
		
		// Null branch name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	"plugin_3", 
								"V", 
								null, 
								"N1", 
								null, 
								"debug",
								false
			);
			
			// Execution will halt on the previous line if addNode() throws an exception
			$this->fail("Method addNode() failed to throw an exception on invalid branch name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer branch name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	"plugin_3", 
								"V", 
								1, 
								"N1", 
								null, 
								"debug",
								false
			);
			
			// Execution will halt on the previous line if addNode() throws an exception
			$this->fail("Method addNode() failed to throw an exception on invalid branch name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped branch name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	"plugin_3", 
								"V", 
								"1", 
								"N1", 
								null, 
								"debug",
								false
			);
			
			// Execution will halt on the previous line if addNode() throws an exception
			$this->fail("Method addNode() failed to throw an exception on invalid branch name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Null node name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	"plugin_3", 
								"V", 
								"X", 
								null, 
								null, 
								"debug",
								false
			);
			
			// Execution will halt on the previous line if addNode() throws an exception
			$this->fail("Method addNode() failed to throw an exception on invalid node name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer node name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	"plugin_3", 
								"V", 
								"X", 
								"1", 
								null, 
								"debug",
								false
			);
			
			// Execution will halt on the previous line if addNode() throws an exception
			$this->fail("Method addNode() failed to throw an exception on invalid node name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped node name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	"plugin_3", 
								"V", 
								"X", 
								"1", 
								null, 
								"debug",
								false
			);
			
			// Execution will halt on the previous line if addNode() throws an exception
			$this->fail("Method addNode() failed to throw an exception on invalid node name");			
						
		}
		catch (FOX_exception $child) {}			
		
		
		// Null filter name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	"plugin_3", 
								"V", 
								"X", 
								"N1", 
								null, 
								null,
								false
			);
			
			// Execution will halt on the previous line if addNode() throws an exception
			$this->fail("Method addNode() failed to throw an exception on invalid filter name");			
						
		}
		catch (FOX_exception $child) {}		
		
		// Nonexistent filter name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	"plugin_3", 
								"V", 
								"X", 
								"N1", 
								null, 
								"fail_filter",
								false
			);
			
			// Execution will halt on the previous line if addNode() throws an exception
			$this->fail("Method addNode() failed to throw an exception on invalid filter name");			
						
		}
		catch (FOX_exception $child) {}
		
		
		// Data fails filter
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	"plugin_3", 
								"V", 
								"X", 
								"N1", 
								null, 
								"debug",
								array(
								    'valid'=>false,
								    'error'=>'test',
								    'input'=>null
								)
			);
			
			// Execution will halt on the previous line if addNode() throws an exception
			$this->fail("Method addNode() failed to throw an exception on input data failing filter");			
						
		}
		catch (FOX_exception $child) {}			
		
		
		// Check db state
		// ===============================================================		
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(
				"plugin_1"=>array(  'X'=>array( 'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>null
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>false
									    ),
									    'N5'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>true
									    ),										
								),
								'Z'=>array( 'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)0
									    )
								)
						    ),	
						    'Y'=>array(	'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)1
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)-1
									    ),
									    'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(float)1.7
									    )							    
								),
								'Z'=>array( 'N4'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(float)-1.6
									    )
								)
						    )					    
				),			
				"plugin_2"=>array(  'X'=>array(	'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(string)"foo"
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>array(null, true, false, 1, 1.0, "foo")
									    )								   						    
								),
								'Z'=>array( 'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>$test_obj
									    )
								) 						
						    )					    
				)		    		    
		);	
		
		$db = new FOX_db();	
		
		$columns = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('plugin','tree','branch','node')
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
	* Test fixture for setNode() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_setNode() {

	    
		self::loadData();
		
		
		// Test overwriting an existing node with same data
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->setNode(	"plugin_1", 
								"X", 
								"K", 
								"N1", 
								null
			);					
						
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));		    
		}			

		// Should return (int)0 to indicate no rows were changed
		$this->assertEquals(0, $rows_changed);	
		
		
		// Check db state
		// ===============================================================
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(
				"plugin_1"=>array(  'X'=>array( 'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>null
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>false
									    ),
									    'N5'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>true
									    ),										
								),
								'Z'=>array( 'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)0
									    )
								)
						    ),	
						    'Y'=>array(	'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)1
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)-1
									    ),
									    'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(float)1.7
									    )							    
								),
								'Z'=>array( 'N4'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(float)-1.6
									    )
								)
						    )					    
				),			
				"plugin_2"=>array(  'X'=>array(	'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(string)"foo"
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>array(null, true, false, 1, 1.0, "foo")
									    )								   						    
								),
								'Z'=>array( 'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>$test_obj
									    )
								) 						
						    )					    
				)		    		    
		);	
		
		$db = new FOX_db();	
		
		$columns = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('plugin','tree','branch','node')
		);
		
		try {
			$struct = $this->cls->_struct();			
			$result = $db->runSelectQuery($struct, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
                $this->assertEquals($check, $result);		
		
		
		// Test overwriting an existing node with different data
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->setNode(	"plugin_1", 
								"X", 
								"K", 
								"N1", 
								"updated_value"
			);
						
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));		    
		}			

		// Should return (int)2 to indicate a node was updated
		$this->assertEquals(2, $rows_changed);		
		
		
		// Check db state
		// ===============================================================		
		
		$check = array(
				"plugin_1"=>array(  'X'=>array( 'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>'updated_value'
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>false
									    ),
									    'N5'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>true
									    ),										
								),
								'Z'=>array( 'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)0
									    )
								)
						    ),	
						    'Y'=>array(	'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)1
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)-1
									    ),
									    'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(float)1.7
									    )							    
								),
								'Z'=>array( 'N4'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(float)-1.6
									    )
								)
						    )					    
				),			
				"plugin_2"=>array(  'X'=>array(	'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(string)"foo"
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>array(null, true, false, 1, 1.0, "foo")
									    )								   						    
								),
								'Z'=>array( 'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>$test_obj
									    )
								) 						
						    )					    
				)		    		    
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
	* Test fixture for setNode(), data integrity checks
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_setNode_dataIntegrity() {

	    
		self::loadData();

		
		// Null plugin name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->setNode(	null, 
								"X", 
								"K", 
								"N1", 
								null
			);
			
			// Execution will halt on the previous line if setNode() throws an exception
			$this->fail("Method setNode() failed to throw an exception on invalid plugin name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer plugin name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->setNode(	1, 
								"X", 
								"K", 
								"N1", 
								null
			);
			
			// Execution will halt on the previous line if setNode() throws an exception
			$this->fail("Method setNode() failed to throw an exception on invalid plugin name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped plugin name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->setNode(	"1", 
								"X", 
								"K", 
								"N1", 
								null
			);
			
			// Execution will halt on the previous line if setNode() throws an exception
			$this->fail("Method setNode() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}			
		
		
		// Null tree name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->setNode(	"plugin_3", 
								null, 
								"K", 
								"N1", 
								null
			);
			
			// Execution will halt on the previous line if setNode() throws an exception
			$this->fail("Method setNode() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer tree name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->setNode(	"plugin_3", 
								1, 
								"K", 
								"N1", 
								null
			);
			
			// Execution will halt on the previous line if setNode() throws an exception
			$this->fail("Method setNode() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped tree name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->setNode(	"plugin_3", 
								"1", 
								"K", 
								"N1", 
								null
			);
			
			// Execution will halt on the previous line if setNode() throws an exception
			$this->fail("Method setNode() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}	
		
		
		// Null branch name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->setNode(	"plugin_3", 
								"V", 
								null, 
								"N1", 
								null
			);
			
			// Execution will halt on the previous line if setNode() throws an exception
			$this->fail("Method setNode() failed to throw an exception on invalid branch name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer branch name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->setNode(	"plugin_3", 
								"V", 
								1, 
								"N1", 
								null
			);
			
			// Execution will halt on the previous line if setNode() throws an exception
			$this->fail("Method setNode() failed to throw an exception on invalid branch name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped branch name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->setNode(	"plugin_3", 
								"V", 
								"1", 
								"N1", 
								null
			);
			
			// Execution will halt on the previous line if setNode() throws an exception
			$this->fail("Method setNode() failed to throw an exception on invalid branch name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Null node name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->setNode(	"plugin_3", 
								"V", 
								"X", 
								null, 
								null
			);
			
			// Execution will halt on the previous line if setNode() throws an exception
			$this->fail("Method setNode() failed to throw an exception on invalid node name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer node name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->setNode(	"plugin_3", 
								"V", 
								"X", 
								"1", 
								null
			);
			
			// Execution will halt on the previous line if setNode() throws an exception
			$this->fail("Method setNode() failed to throw an exception on invalid node name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped node name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->setNode(	"plugin_3", 
								"V", 
								"X", 
								"1", 
								null
			);
			
			// Execution will halt on the previous line if setNode() throws an exception
			$this->fail("Method setNode() failed to throw an exception on invalid node name");			
						
		}
		catch (FOX_exception $child) {}			
		
		
		// Data fails filter
		// ===============================================================
				
		try { 
			// Modify an existing node so it fails on null input
		    
			$this->cls->addNode(	"plugin_1", 
						"X", 
						"K", 
						"N1", 
						false, 
						"bool",
						array(
						    'null_input'=>'trap'
						)
			);			    
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));			
		}			
			
		try {
		    	// Write a value to the node that fails validation
		    
			$rows_changed = $this->cls->setNode(	"plugin_3", 
								"V", 
								"X", 
								"N1", 
								null
			);
			
			// Execution will halt on the previous line if setNode() throws an exception
			$this->fail("Method setNode() failed to throw an exception on input data failing filter");			
						
		}
		catch (FOX_exception $child) {}			
		
		
		// Check db state
		// ===============================================================		
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
		
		$check = array(
				"plugin_1"=>array(  'X'=>array( 'K'=>array( 
									    'N1'=>array(
											    'filter'=>'bool', 
											    'filter_ctrl'=>array('null_input'=>'trap'), 
											    'val'=>false
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>false
									    ),
									    'N5'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>true
									    ),										
								),
								'Z'=>array( 'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)0
									    )
								)
						    ),	
						    'Y'=>array(	'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)1
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(int)-1
									    ),
									    'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(float)1.7
									    )							    
								),
								'Z'=>array( 'N4'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(float)-1.6
									    )
								)
						    )					    
				),			
				"plugin_2"=>array(  'X'=>array(	'K'=>array( 
									    'N1'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>(string)"foo"
									    ),
									    'N2'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>array(null, true, false, 1, 1.0, "foo")
									    )								   						    
								),
								'Z'=>array( 'N3'=>array(
											    'filter'=>'debug', 
											    'filter_ctrl'=>false, 
											    'val'=>$test_obj
									    )
								) 						
						    )					    
				)		    		    
		);	
		
		$db = new FOX_db();	
		
		$columns = null;
		
		$ctrl = array(
				'format'=>'array_key_array',
				'key_col'=>array('plugin','tree','branch','node')
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
	* Test fixture for getNode() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getNode() {

	    
		self::loadData();
		
		
		// Existing node, "single" mode
		// ===============================================================
		
		$valid = false;
		
		try {
			$result = $this->cls->getNode(	"plugin_1", 
							"X", 
							"K", 
							"N1",
							$valid
			);					
						
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));		    
		}			

		$this->assertEquals(true, $valid);		
		$this->assertEquals(null, $result);
				
		
		// Existing nodes, "multi" mode
		// ===============================================================
		
		$valid = false;
		
		try {
			$result = $this->cls->getNode(	"plugin_1", 
							"X", 
							"K", 
							array("N1", "N2"),
							$valid
			);					
						
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));		    
		}			

		$this->assertEquals(true, $valid);
		
		$check = array( 
				'N1'=>null,
				'N2'=>false
		);

		$this->assertEquals($check, $result);		
		
		
		// Nonexistent node, "single" mode
		// ===============================================================
		
		$valid = false;
		
		try {
			$result = $this->cls->getNode(	"plugin_3", 
							"X", 
							"K", 
							"N1",
							$valid
			);					
						
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));		    
		}			

		$this->assertEquals(false, $valid);		
		$this->assertEquals(null, $result);
		
		
		// Existing nodes, "multi" mode, with nonexistent node
		// ===============================================================
		
		$valid = false;
		
		try {
			$result = $this->cls->getNode(	"plugin_1", 
							"X", 
							"K", 
							array("N1", "N2", "fail_node"),
							$valid
			);					
						
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));		    
		}			

		$this->assertEquals(false, $valid);
		
		$check = array( 
				'N1'=>null,
				'N2'=>false
		);

		$this->assertEquals($check, $result);			
		
	}
	
	
       /**
	* Test fixture for getNode(), data integrity checks
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getNode_dataIntegrity() {

	    
		self::loadData();

		
		// Null plugin name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getNode(	null, 
								"X", 
								"K", 
								"N1"
			);
			
			// Execution will halt on the previous line if getNode() throws an exception
			$this->fail("Method getNode() failed to throw an exception on invalid plugin name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer plugin name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getNode(	1, 
								"X", 
								"K", 
								"N1"
			);
			
			// Execution will halt on the previous line if getNode() throws an exception
			$this->fail("Method getNode() failed to throw an exception on invalid plugin name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped plugin name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getNode(	"1", 
								"X", 
								"K", 
								"N1"
			);
			
			// Execution will halt on the previous line if getNode() throws an exception
			$this->fail("Method getNode() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}			
		
		
		// Null tree name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getNode(	"plugin_3", 
								null, 
								"K", 
								"N1"
			);
			
			// Execution will halt on the previous line if getNode() throws an exception
			$this->fail("Method getNode() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer tree name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getNode(	"plugin_3", 
								1, 
								"K", 
								"N1"
			);
			
			// Execution will halt on the previous line if getNode() throws an exception
			$this->fail("Method getNode() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped tree name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getNode(	"plugin_3", 
								"1", 
								"K", 
								"N1"
			);
			
			// Execution will halt on the previous line if getNode() throws an exception
			$this->fail("Method getNode() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}	
		
		
		// Null branch name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getNode(	"plugin_3", 
								"V", 
								null, 
								"N1"
			);
			
			// Execution will halt on the previous line if getNode() throws an exception
			$this->fail("Method getNode() failed to throw an exception on invalid branch name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer branch name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getNode(	"plugin_3", 
								"V", 
								1, 
								"N1"
			);
			
			// Execution will halt on the previous line if getNode() throws an exception
			$this->fail("Method getNode() failed to throw an exception on invalid branch name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped branch name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getNode(	"plugin_3", 
								"V", 
								"1", 
								"N1"
			);
			
			// Execution will halt on the previous line if getNode() throws an exception
			$this->fail("Method getNode() failed to throw an exception on invalid branch name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Null node name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getNode(	"plugin_3", 
								"V", 
								"X", 
								null
			);
			
			// Execution will halt on the previous line if getNode() throws an exception
			$this->fail("Method getNode() failed to throw an exception on invalid node name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer node name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getNode(	"plugin_3", 
								"V", 
								"X", 
								"1"
			);
			
			// Execution will halt on the previous line if getNode() throws an exception
			$this->fail("Method getNode() failed to throw an exception on invalid node name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped node name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getNode(	"plugin_3", 
								"V", 
								"X", 
								"1"
			);
			
			// Execution will halt on the previous line if getNode() throws an exception
			$this->fail("Method getNode() failed to throw an exception on invalid node name");			
						
		}
		catch (FOX_exception $child) {}			
		
	}	
	
	
       /**
	* Test fixture for getBranch() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getBranch() {
	    
	    
		self::loadData();
		
		
		// Existing branch, "single" mode
		// ===============================================================
		
		$valid = false;
		
		try {
			$result = $this->cls->getBranch(    "plugin_1", 
							    "X", 
							    "K", 
							    $valid
			);					
						
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));		    
		}			

		$this->assertEquals(true, $valid);	
		
		$check = array(
				'N1'=>null,
				'N2'=>false,
				'N5'=>true											    		    
		);
		
		$this->assertEquals($check, $result);
				
		
		// Existing nodes, "multi" mode
		// ===============================================================
		
		$valid = false;
		
		try {
			$result = $this->cls->getBranch(    "plugin_1", 
							    "X", 
							    array("K", "Z"),
							    $valid
			);					
						
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));		    
		}			

		$this->assertEquals(true, $valid);
		
		$check = array(
				'K'=>array( 
					    'N1'=>null,
					    'N2'=>false,
					    'N5'=>true,										
				),
				'Z'=>array( 'N3'=>(int)0 )		    		    
		);
		
		$this->assertEquals($check, $result);		
	
		
		// Nonexistent node, "single" mode
		// ===============================================================
		
		$valid = false;
		
		try {
			$result = $this->cls->getBranch(    "plugin_3", 
							    "X", 
							    "fail", 
							    $valid
			);					
						
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));		    
		}			

		$this->assertEquals(false, $valid);		
		$this->assertEquals(null, $result);
		
			
		// Existing nodes, "multi" mode, with nonexistent node
		// ===============================================================
		
		$valid = false;
		
		try {
			$result = $this->cls->getBranch(    "plugin_1", 
							    "X", 
							    array("K", "Z", "fail"),
							    $valid
			);					
						
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));		    
		}			

		$this->assertEquals(false, $valid);
		
		$check = array(
				'K'=>array( 
					    'N1'=>null,
					    'N2'=>false,
					    'N5'=>true,										
				),
				'Z'=>array( 'N3'=>(int)0 )		    		    
		);

		$this->assertEquals($check, $result);			
		
	}
	
	
       /**
	* Test fixture for getBranch(), data integrity checks
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getBranch_dataIntegrity() {

   
		self::loadData();

		
		// Null plugin name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getBranch(	null, 
								"X", 
								"K"
			);
			
			// Execution will halt on the previous line if getBranch() throws an exception
			$this->fail("Method getBranch() failed to throw an exception on invalid plugin name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer plugin name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getBranch(	1, 
								"X", 
								"K"
			);
			
			// Execution will halt on the previous line if getBranch() throws an exception
			$this->fail("Method getBranch() failed to throw an exception on invalid plugin name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped plugin name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getBranch(	"1", 
								"X", 
								"K"
			);
			
			// Execution will halt on the previous line if getBranch() throws an exception
			$this->fail("Method getBranch() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}			
		
		
		// Null tree name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getBranch(	"plugin_3", 
								null, 
								"K"
			);
			
			// Execution will halt on the previous line if getBranch() throws an exception
			$this->fail("Method getBranch() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer tree name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getBranch(	"plugin_3", 
								1, 
								"K"
			);
			
			// Execution will halt on the previous line if getBranch() throws an exception
			$this->fail("Method getBranch() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped tree name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getBranch(	"plugin_3", 
								"1", 
								"K"
			);
			
			// Execution will halt on the previous line if getBranch() throws an exception
			$this->fail("Method getBranch() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}	
		
		
		// Null branch name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getBranch(	"plugin_3", 
								"V", 
								null
			);
			
			// Execution will halt on the previous line if getBranch() throws an exception
			$this->fail("Method getBranch() failed to throw an exception on invalid branch name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer branch name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getBranch(	"plugin_3", 
								"V", 
								1
			);
			
			// Execution will halt on the previous line if getBranch() throws an exception
			$this->fail("Method getBranch() failed to throw an exception on invalid branch name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped branch name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getBranch(	"plugin_3", 
								"V", 
								"1"
			);
			
			// Execution will halt on the previous line if getBranch() throws an exception
			$this->fail("Method getBranch() failed to throw an exception on invalid branch name");			
						
		}
		catch (FOX_exception $child) {}	
		
	}	
	
	
       /**
	* Test fixture for getTree() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getTree() {

//		$test_obj = new stdClass();
//		$test_obj->foo = "11";
//		$test_obj->bar = "test_Bar";
//		
//		$check = array(
//				"plugin_1"=>array(  'X'=>array( 'K'=>array( 
//									    'N1'=>null,
//									    'N2'=>false,
//									    'N5'=>true,										
//								),
//								'Z'=>array( 'N3'=>(int)0 )
//						    ),	
//						    'Y'=>array(	'K'=>array( 
//									    'N1'=>(int)1,
//									    'N2'=>(int)-1,
//									    'N3'=>(float)1.7							    
//								),
//								'Z'=>array( 'N4'=>(float)-1.6 )
//						    )					    
//				),			
//				"plugin_2"=>array(  'X'=>array(	'K'=>array( 
//									    'N1'=>(string)"foo",
//									    'N2'=>array(null, true, false, 1, 1.0, "foo")								   						    
//								),
//								'Z'=>array( 'N3'=>$test_obj ) 						
//						    )					    
//				)		    		    
//		);
	    
		self::loadData();
		
		
		// Existing branch, "single" mode
		// ===============================================================
		
		$valid = false;
		
		try {
			$result = $this->cls->getTree(	"plugin_1", 
							"X", 
							$valid
			);					
						
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));		    
		}			

		$this->assertEquals(true, $valid);	
		
		$check = array(
				'K'=>array( 
					    'N1'=>null,
					    'N2'=>false,
					    'N5'=>true,										
				),
				'Z'=>array( 'N3'=>(int)0 )						    	    		    
		);
		
		$this->assertEquals($check, $result);
				
		
		// Existing nodes, "multi" mode
		// ===============================================================
		
		$valid = false;
		
		try {
			$result = $this->cls->getTree(	"plugin_1", 
							array("X", "Y"),
							$valid
			);					
						
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));		    
		}			

		$this->assertEquals(true, $valid);
		
		$check = array(
				'X'=>array( 'K'=>array( 
							'N1'=>null,
							'N2'=>false,
							'N5'=>true,										
					    ),
					    'Z'=>array( 'N3'=>(int)0 )
				),	
				'Y'=>array(	'K'=>array( 
							'N1'=>(int)1,
							'N2'=>(int)-1,
							'N3'=>(float)1.7							    
					    ),
					    'Z'=>array( 'N4'=>(float)-1.6 )
				)						    		    		    
		);
		
		$this->assertEquals($check, $result);		
	
		
		// Nonexistent node, "single" mode
		// ===============================================================
		
		$valid = false;
		
		try {
			$result = $this->cls->getTree(	"plugin_3", 
							"fail", 
							$valid
			);					
						
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));		    
		}			

		$this->assertEquals(false, $valid);		
		$this->assertEquals(null, $result);
		
			
		// Existing nodes, "multi" mode, with nonexistent node
		// ===============================================================
		
		$valid = false;
		
		try {
			$result = $this->cls->getTree(	"plugin_1", 
							array("X", "Y", "fail"),
							$valid
			);					
						
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>10, 'data'=>true)));		    
		}			

		$this->assertEquals(false, $valid);
		
		$check = array(
				'X'=>array( 'K'=>array( 
							'N1'=>null,
							'N2'=>false,
							'N5'=>true,										
					    ),
					    'Z'=>array( 'N3'=>(int)0 )
				),	
				'Y'=>array(	'K'=>array( 
							'N1'=>(int)1,
							'N2'=>(int)-1,
							'N3'=>(float)1.7							    
					    ),
					    'Z'=>array( 'N4'=>(float)-1.6 )
				)						    		    		    
		);

		$this->assertEquals($check, $result);			
		
	}
	
	
       /**
	* Test fixture for getTree(), data integrity checks
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_getTree_dataIntegrity() {

   return;
		self::loadData();

		
		// Null plugin name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getTree(	null, 
								"X", 
								"K"
			);
			
			// Execution will halt on the previous line if getTree() throws an exception
			$this->fail("Method getTree() failed to throw an exception on invalid plugin name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer plugin name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getTree(	1, 
								"X", 
								"K"
			);
			
			// Execution will halt on the previous line if getTree() throws an exception
			$this->fail("Method getTree() failed to throw an exception on invalid plugin name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped plugin name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getTree(	"1", 
								"X", 
								"K"
			);
			
			// Execution will halt on the previous line if getTree() throws an exception
			$this->fail("Method getTree() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}			
		
		
		// Null tree name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getTree(	"plugin_3", 
								null, 
								"K"
			);
			
			// Execution will halt on the previous line if getTree() throws an exception
			$this->fail("Method getTree() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer tree name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getTree(	"plugin_3", 
								1, 
								"K"
			);
			
			// Execution will halt on the previous line if getTree() throws an exception
			$this->fail("Method getTree() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped tree name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getTree(	"plugin_3", 
								"1", 
								"K"
			);
			
			// Execution will halt on the previous line if getTree() throws an exception
			$this->fail("Method getTree() failed to throw an exception on invalid tree name");			
						
		}
		catch (FOX_exception $child) {}	
		
		
		// Null branch name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getTree(	"plugin_3", 
								"V", 
								null
			);
			
			// Execution will halt on the previous line if getTree() throws an exception
			$this->fail("Method getTree() failed to throw an exception on invalid branch name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer branch name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getTree(	"plugin_3", 
								"V", 
								1
			);
			
			// Execution will halt on the previous line if getTree() throws an exception
			$this->fail("Method getTree() failed to throw an exception on invalid branch name");			
						
		}
		catch (FOX_exception $child) {}	
		
		// Integer-mapped branch name
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->getTree(	"plugin_3", 
								"V", 
								"1"
			);
			
			// Execution will halt on the previous line if getTree() throws an exception
			$this->fail("Method getTree() failed to throw an exception on invalid branch name");			
						
		}
		catch (FOX_exception $child) {}	
		
	}
	
	
	function tearDown() {
	   
		$this->cls = new FOX_config();
		$unistall_ok = $this->cls->uninstall();
		
		$this->assertEquals(true, $unistall_ok);
		
		parent::tearDown();
	}



}

?>