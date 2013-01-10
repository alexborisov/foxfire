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

		    array( "plugin"=>'plugin_1', "tree"=>"X", "branch"=>"K", "node"=>"N_1", "filter"=>"debug", "ctrl"=>false, "val"=>null),
		    array( "plugin"=>'plugin_1', "tree"=>"X", "branch"=>"K", "node"=>"N_2", "filter"=>"debug", "ctrl"=>false, "val"=>false),
		    array( "plugin"=>'plugin_1', "tree"=>"X", "branch"=>"K", "node"=>"N_5", "filter"=>"debug", "ctrl"=>false, "val"=>true),
		    array( "plugin"=>'plugin_1', "tree"=>"X", "branch"=>"Z", "node"=>"N_3", "filter"=>"debug", "ctrl"=>false, "val"=>(int)0),	

		    array( "plugin"=>'plugin_1', "tree"=>"Y", "branch"=>"K", "node"=>"N_1", "filter"=>"debug", "ctrl"=>false, "val"=>(int)1),
		    array( "plugin"=>'plugin_1', "tree"=>"Y", "branch"=>"K", "node"=>"N_2", "filter"=>"debug", "ctrl"=>false, "val"=>(int)-1),
		    array( "plugin"=>'plugin_1', "tree"=>"Y", "branch"=>"K", "node"=>"N_3", "filter"=>"debug", "ctrl"=>false, "val"=>(float)1.7),
		    array( "plugin"=>'plugin_1', "tree"=>"Y", "branch"=>"Z", "node"=>"N_4", "filter"=>"debug", "ctrl"=>false, "val"=>(float)-1.6),

		    array( "plugin"=>'plugin_2', "tree"=>"X", "branch"=>"K", "node"=>"N_1", "filter"=>"debug", "ctrl"=>false, "val"=>(string)"foo"),
		    array( "plugin"=>'plugin_2', "tree"=>"X", "branch"=>"K", "node"=>"N_2", "filter"=>"debug", "ctrl"=>false, "val"=>array(null, true, false, 1, 1.0, "foo")),
		    array( "plugin"=>'plugin_2', "tree"=>"X", "branch"=>"Z", "node"=>"N_3", "filter"=>"debug", "ctrl"=>false, "val"=>$test_obj)	
		    
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
							    
				$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));			
			}			
			
			// Should return (int)1 to indicate a node was added
			$this->assertEquals(1, $rows_changed); 			
			
		}
		unset($item);
		
		
		// Test adding duplicate node
		// ===============================================================
		
		try {
			$rows_changed = $this->cls->addNode(	"plugin_1", 
								"X", 
								"K", 
								"N_1", 
								"updated_value", 
								"debug",
								false
			);		
		}
		catch (FOX_exception $child) {
					
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));		    
		}			
			
		// Should return (int)1 to indicate a node was added
		$this->assertEquals(1, $rows_changed);	
		
		
//		// Check cache state
//		// ===============================================================	
//		
//		// NOTE: the LUT's won't be set at this point, because we haven't done any 
//		// database reads that give objects authority
//		
//		$check = array(
//				1=>array(   'keys'=>array(  'X'=>array(	'K'=>array(
//										    1=>null,
//										    2=>false,
//										    5=>true 							    
//									),
//									'Z'=>array( 3=>(int)0 ) 						
//							    ),	
//							    'Y'=>array(	'K'=>array( 
//										    1=>(int)1,
//										    2=>(int)-1,
//										    3=>(float)1.7 							    
//									),
//									'Z'=>array( 4=>(float)-1.6 ) 						
//							    )
//					    )
//				),			
//				2=>array(   'keys'=>array(  'X'=>array(	'K'=>array( 
//										    1=>(string)"foo",
//										    2=>array(null, true, false, 1, 1.0, "foo")										    							    
//									),
//									'Z'=>array( 3=>$test_obj ) 						
//							    )	
//					    )						
//				 )		    		    
//		);
//		
//		$this->assertEquals($check, $this->cls->cache);	
//		
//		
//		// Check db state
//		// ===============================================================		
//		
//		$check = array(
//				1=>array(   'X'=>array(	'K'=>array( 
//								    1=>null,
//								    2=>false,
//								    5=>true							    
//							),
//							'Z'=>array( 3=>(int)0 ) 						
//					    ),	
//					    'Y'=>array(	'K'=>array( 
//								    1=>(int)1,
//								    2=>(int)-1,
//								    3=>(float)1.7 							    
//							),
//							'Z'=>array( 4=>(float)-1.6 ) 						
//					    )					    
//				),			
//				2=>array(   'X'=>array(	'K'=>array( 
//								    1=>(string)"foo",
//								    2=>array(null, true, false, 1, 1.0, "foo")								   						    
//							),
//							'Z'=>array( 3=>$test_obj ) 						
//					    )					    
//				 )		    		    
//		);		
//		
//		
//		$db = new FOX_db();	
//		
//		$columns = null;
//		
//		$ctrl = array(
//				'format'=>'array_key_array',
//				'key_col'=>array('L4','L3','L2','L1')
//		);
//		
//		try {
//			$struct = $this->cls->_struct();			
//			$result = $db->runSelectQuery($struct, $args=null, $columns, $ctrl);
//		}
//		catch (FOX_exception $child) {
//
//			$this->fail($child->dumpString(1));	
//		}		
//		
//                $this->assertEquals($check, $result);		
		
		
	}





	function tearDown() {
	   
		$this->cls = new FOX_config();
		$unistall_ok = $this->cls->uninstall();
		
		$this->assertEquals(true, $unistall_ok);
		
		parent::tearDown();
	}



}

?>