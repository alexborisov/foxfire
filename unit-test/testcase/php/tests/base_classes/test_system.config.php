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
	* Test fixture for addNode() method
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_addNode() {

	    
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





	function tearDown() {
	   
		$this->cls = new FOX_config();
		$unistall_ok = $this->cls->uninstall();
		
		$this->assertEquals(true, $unistall_ok);
		
		parent::tearDown();
	}



}

?>