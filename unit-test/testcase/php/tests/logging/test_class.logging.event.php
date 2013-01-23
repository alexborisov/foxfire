<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - LOGGING EVENT CLASS
 * Exercises all functions of the class
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

class core_logging_event extends RAZ_testCase {

	var $cls, $tree, $branch, $node;
	

	function setUp() {

		parent::setUp();
				
		
		// Install the db table
		// ===========================================
		
		$this->cls = new FOX_log_event();
		$this->tree = new FOX_log_dictionary_tree();
		$this->branch = new FOX_log_dictionary_branch();
		$this->node = new FOX_log_dictionary_node();
		
		try {
			$install_ok = $this->cls->install();
			$install_ok = $this->tree->install();
			$install_ok = $this->branch->install();
			$install_ok = $this->node->install();
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}		
				
		$this->assertEquals(true, $install_ok);	
		
		
		// Clear table to guard against previous failed test
		// ===========================================
		
		try {
			$truncate_ok = $this->cls->truncate();
			$truncate_ok = $this->tree->truncate();
			$truncate_ok = $this->branch->truncate();
			$truncate_ok = $this->node->truncate();
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
				
		$this->assertEquals(true, $truncate_ok);
		
		
		// Flush cache to guard against previous failed test
		// ===========================================
		
		try {
			$flush_ok = $this->tree->flushCache();
			$flush_ok = $this->branch->flushCache();
			$flush_ok = $this->node->flushCache();		
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
				
		$this->assertEquals(true, $flush_ok);		

	}	

       /**
	* Loads the class instance with the test data set, and verifies it was correctly written
        * to the database
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function loadData($using_dictionary=false) {
	    
	    if ($using_dictionary){
		
		
	    } else {
		
		    self::test_add_no_dict();
	    }
	    
	    
	}	
	
	
       /**
	* Loads the class instance with the test data set, and verifies it was correctly written
        * to the database
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function emptyData($using_dictionary=false) {

	    	// Clear table to guard against previous failed test
		// ===========================================
		
		try {
			$truncate_ok = $this->cls->truncate();
			$truncate_ok = $this->tree->truncate();
			$truncate_ok = $this->branch->truncate();
			$truncate_ok = $this->node->truncate();
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
				
		$this->assertEquals(true, $truncate_ok);
		
		
		// Flush cache to guard against previous failed test
		// ===========================================
		
		try {
			$flush_ok = $this->tree->flushCache();
			$flush_ok = $this->branch->flushCache();
			$flush_ok = $this->node->flushCache();		
		}
		catch (FOX_exception $child) {
		    
			$this->fail($child->dumpString(1));		    
		}
				
		$this->assertEquals(true, $flush_ok);		    
	    
	}	

	
       /**
	* Test add() not using dictionary
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_add_no_dict() {
	    
		$check_data = array(
				    array( 'id'=>1, 'tree'=>1, 'branch'=>1, 'node'=>1, 'user_id'=>1, 'level'=>1, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>2, 'tree'=>1, 'branch'=>1, 'node'=>2, 'user_id'=>1, 'level'=>1, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>3, 'tree'=>1, 'branch'=>1, 'node'=>3, 'user_id'=>1, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>4, 'tree'=>1, 'branch'=>2, 'node'=>1, 'user_id'=>2, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>5, 'tree'=>1, 'branch'=>2, 'node'=>2, 'user_id'=>2, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>6, 'tree'=>1, 'branch'=>2, 'node'=>3, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>7, 'tree'=>1, 'branch'=>3, 'node'=>1, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 2, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>8, 'tree'=>1, 'branch'=>3, 'node'=>2, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 2, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>9, 'tree'=>1, 'branch'=>3, 'node'=>3, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 3, 2013), 'summary'=>'test summary', 'data'=>'data' ),		
		    );

		// Add single event
		// ===============================================================

		foreach ($check_data  as $data) {
			try{
				$result = $this->cls->add(
				    array( 'tree'=>$data['tree'], 'branch'=>$data['branch'], 'node'=>$data['node'], 'user_id'=>$data['user_id'], 
					'level'=>$data['level'], 'date'=>$data['date'], 'summary'=>$data['summary'], 'data'=>$data['data']) );
			}
			catch (FOX_exception $child){
				throw new FOX_exception( array(
							    'numeric'=>1,
							    'text'=>"Add exception",
							    'data'=>array( "Add values"=>$data),
							    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							    'child'=>$child
							)
				);
			}
			$this->assertEquals($data['id'], $result);


		}

		// Check DB
		// ===============================================================	    
		
		try{
			$result = $this->cls->query(null, null, null, false);
		}
		catch (FOX_exception $child){
			throw new FOX_exception( array(
						    'numeric'=>2,
						    'text'=>"Query exception",
						    'data'=>array( "args"=>null, "columns"=>null, "ctrl"=>null, "returnwords"=>false),
						    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						    'child'=>$child
						)
			);
		}
		$this->assertEquals($check_data, $result);
				
	}	
	
       /**
	* Test dropID() not using dictionary
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropID_no_dict() {
	    
		self::loadData();
	    
		$check_data = array(
			array( 'id'=>1, 'tree'=>1, 'branch'=>1, 'node'=>1, 'user_id'=>1, 'level'=>1, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
			array( 'id'=>2, 'tree'=>1, 'branch'=>1, 'node'=>2, 'user_id'=>1, 'level'=>1, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
			array( 'id'=>3, 'tree'=>1, 'branch'=>1, 'node'=>3, 'user_id'=>1, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
			array( 'id'=>4, 'tree'=>1, 'branch'=>2, 'node'=>1, 'user_id'=>2, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
			array( 'id'=>5, 'tree'=>1, 'branch'=>2, 'node'=>2, 'user_id'=>2, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
			array( 'id'=>6, 'tree'=>1, 'branch'=>2, 'node'=>3, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
			array( 'id'=>7, 'tree'=>1, 'branch'=>3, 'node'=>1, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 2, 2013), 'summary'=>'test summary', 'data'=>'data' ),
			array( 'id'=>8, 'tree'=>1, 'branch'=>3, 'node'=>2, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 2, 2013), 'summary'=>'test summary', 'data'=>'data' ),
			array( 'id'=>9, 'tree'=>1, 'branch'=>3, 'node'=>3, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 3, 2013), 'summary'=>'test summary', 'data'=>'data' ),		
		);
		
		// Test single dropID 
		// ===============================================================
		
		while (count($check_data)>0){
		
			$last_id = count($check_data);
			
			try{
				$result = $this->cls->dropID($last_id);
			}
			catch (FOX_exception $child){
				throw new FOX_exception( array(
							    'numeric'=>1,
							    'text'=>"DropID exception",
							    'data'=>array( "id"=>$lastid),
							    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							    'child'=>$child
							)
				);
			}			
			$this->assertEquals(1, $result);
			
			array_pop($check_data);
			
			try{
				$result = $this->cls->query(null,null,null, false);
			}
			catch (FOX_exception $child){
				throw new FOX_exception( array(
							    'numeric'=>2,
							    'text'=>"Query exception",
							    'data'=>array( "args"=>null, "columns"=>null, "ctrl"=>null, "returnwords"=>false),
							    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
							    'child'=>$child
							)
				);
			}
			if (count($check_data)>0){
			    
				    $this->assertEquals($check_data, $result);
				
			} else {
				    // At this stage all of the Ids have been droped 
				    $this->assertEquals(false, $result);			    
				
			}
		    
		}
		
		// Clear DB and cache then load DB
		// ===============================================================
		self::emptyData();
		self::loadData();

		// Test multi dropID 
		// ===============================================================
		$all_ids = array(1,2,3,4,5,6,7,8,9);
		try{
			$result = $this->cls->dropID($all_ids);
		}
		catch (FOX_exception $child){
			throw new FOX_exception( array(
						    'numeric'=>1,
						    'text'=>"DropID exception",
						    'data'=>array( "id"=>$lastid),
						    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						    'child'=>$child
						)
			);
		}			
		$this->assertEquals(9, $result);
		
		// Test DB empty 
		// ===============================================================				
		
		try{
			$result = $this->cls->query(null,null,null, false);
		}
		catch (FOX_exception $child){
			throw new FOX_exception( array(
						    'numeric'=>2,
						    'text'=>"Query exception",
						    'data'=>array( "args"=>null, "columns"=>null, "ctrl"=>null, "returnwords"=>false),
						    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						    'child'=>$child
						)
			);
		}			
		$this->assertEquals(false, $result);
		
	}		
	
       /**
	* Test dropAll() not using dictionary
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropAll_no_dict() {
	    
		self::loadData();
		
		// Test dropAll 
		// ===============================================================		
		try{
			$result = $this->cls->dropAll(false);
		}
		catch (FOX_exception $child){
			throw new FOX_exception( array(
						    'numeric'=>1,
						    'text'=>"DropAll exception",
						    'data'=>null,
						    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						    'child'=>$child
						)
			);
		}			
		$this->assertEquals(true, $result);
		
		// Test DB empty 
		// ===============================================================				
		
		try{
			$result = $this->cls->query(null,null,null, false);
		}
		catch (FOX_exception $child){
			throw new FOX_exception( array(
						    'numeric'=>2,
						    'text'=>"Query exception",
						    'data'=>array( "args"=>null, "columns"=>null, "ctrl"=>null, "returnwords"=>false),
						    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						    'child'=>$child
						)
			);
		}			
		$this->assertEquals(false, $result);
	}	
	
       /**
	* Test drop() single condition not using dictionary
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_drop_no_dict() {
	    
		self::loadData();
		
		// Test drop single condition single value
		// ===============================================================
		
		$args = array( array("col"=>"id", "op"=>"=", "val"=>9 ) );
		
		try{
			$result = $this->cls->drop( $args );
		}
		catch (FOX_exception $child){
			throw new FOX_exception( array(
						    'numeric'=>1,
						    'text'=>"Drop exception",
						    'data'=>array( "args"=>$args),
						    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						    'child'=>$child
						)
			);
		}
		
		$this->assertEquals(1, $result);
		
		// Test drop single condition multiple values
		// ===============================================================
		
		$args = array( array("col"=>"user_id", "op"=>"=", "val"=>array(2,3) ) );
		
		try{
			$result = $this->cls->drop( $args );
		}
		catch (FOX_exception $child){
			throw new FOX_exception( array(
						    'numeric'=>1,
						    'text'=>"Drop exception",
						    'data'=>array( "args"=>$args),
						    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						    'child'=>$child
						)
			);
		}
		
		$this->assertEquals(5, $result);	
		
		// Test drop multiple conditions single value
		// ===============================================================
		
		$args = array(	array("col"=>"user_id", "op"=>"=", "val"=>1 ),
				array("col"=>"node", "op"=>"=", "val"=>1 )
		    );
		
		try{
			$result = $this->cls->drop( $args );
		}
		catch (FOX_exception $child){
			throw new FOX_exception( array(
						    'numeric'=>1,
						    'text'=>"Drop exception",
						    'data'=>array( "args"=>$args),
						    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						    'child'=>$child
						)
			);
		}
		
		$this->assertEquals(1, $result);
		
		// Check DB 
		// ===============================================================
		
		$check_data = array(
				    array( 'id'=>2, 'tree'=>1, 'branch'=>1, 'node'=>2, 'user_id'=>1, 'level'=>1, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>3, 'tree'=>1, 'branch'=>1, 'node'=>3, 'user_id'=>1, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
		    );		
		
		try{
			$result = $this->cls->query(null,null,null, false);
		}
		catch (FOX_exception $child){
			throw new FOX_exception( array(
						    'numeric'=>2,
						    'text'=>"Query exception",
						    'data'=>array( "args"=>null, "columns"=>null, "ctrl"=>null, "returnwords"=>false),
						    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						    'child'=>$child
						)
			);
		}			
		$this->assertEquals($check_data, $result);		

		// Clear DB and cache then load DB
		// ===============================================================
		self::emptyData();
		self::loadData();
		
		// Test drop multiple conditions multiple values
		// ===============================================================
		
		$args = array(	array("col"=>"user_id", "op"=>"=", "val"=>array(1,3) ),
				array("col"=>"branch", "op"=>"=", "val"=>array(1,3) )
		    );
		
		try{
			$result = $this->cls->drop( $args );
		}
		catch (FOX_exception $child){
			throw new FOX_exception( array(
						    'numeric'=>1,
						    'text'=>"Drop exception",
						    'data'=>array( "args"=>$args),
						    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						    'child'=>$child
						)
			);
		}
		
		$this->assertEquals(6, $result);	
		
		// Check DB 
		// ===============================================================

		$check_data = array(
			array( 'id'=>4, 'tree'=>1, 'branch'=>2, 'node'=>1, 'user_id'=>2, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
			array( 'id'=>5, 'tree'=>1, 'branch'=>2, 'node'=>2, 'user_id'=>2, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
			array( 'id'=>6, 'tree'=>1, 'branch'=>2, 'node'=>3, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
		);
		
		try{
			$result = $this->cls->query(null,null,null, false);
		}
		catch (FOX_exception $child){
			throw new FOX_exception( array(
						    'numeric'=>2,
						    'text'=>"Query exception",
						    'data'=>array( "args"=>null, "columns"=>null, "ctrl"=>null, "returnwords"=>false),
						    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						    'child'=>$child
						)
			);
		}			
		$this->assertEquals($check_data, $result);
		
		// Clear DB and cache then load DB
		// ===============================================================
		self::emptyData();
		self::loadData();
		
		// Test empty args
		// ===============================================================

		try{
			$result = $this->cls->drop( $args=null );
			
			// Execution will halt on the previous line if drop() throws an exception
			$this->fail("Method drop() failed to throw an exception on empty args");					
		}
		catch (FOX_exception $child){

		}
				
		// Test invalid column
		// ===============================================================		
		
		$args = array( array("col"=>"le", "op"=>">", "val"=>2 ) );
		try{
			$result = $this->cls->drop( $args );
			
			// Execution will halt on the previous line if drop() throws an exception
			$this->fail("Method drop() failed to throw an exception on invalid column");					
		}
		catch (FOX_exception $child){

		}		
		// Test invalid value
		// ===============================================================		

//		$args = array( array("col"=>"level", "op"=>"=", "val"=>"tree" ) );
//		try{
//			$result = $this->cls->drop( $args );
//			
//			// Execution will halt on the previous line if drop() throws an exception
//			$this->fail("Method drop() failed to throw an exception on empty args");					
//		}
//		catch (FOX_exception $child){
//
//		}		
		// Test invalid operation
		// ===============================================================		
		
		$args = array( array("col"=>"level", "op"=>"equal", "val"=>1 ) );
		try{
			$result = $this->cls->drop( $args );
			
			// Execution will halt on the previous line if drop() throws an exception
			$this->fail("Method drop() failed to throw an exception on empty args");					
		}
		catch (FOX_exception $child){

		}		
		
		// Test greater then dictionary column
		// ===============================================================		

		$args = array( array("col"=>"branch", "op"=>">", "val"=>2 ) );
		try{
			$result = $this->cls->drop( $args );
			
			// Execution will halt on the previous line if drop() throws an exception
			$this->fail("Method drop() failed to throw an exception on empty args");					
		}
		catch (FOX_exception $child){

		}		
	}	
	
       /**
	* Test query() not using dictionary
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_query_no_dict() {
	    
		self::loadData();
		
		// Test query minimal arguments
		// ===============================================================		
		$check_data = array(
				    array( 'id'=>1, 'tree'=>1, 'branch'=>1, 'node'=>1, 'user_id'=>1, 'level'=>1, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>2, 'tree'=>1, 'branch'=>1, 'node'=>2, 'user_id'=>1, 'level'=>1, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>3, 'tree'=>1, 'branch'=>1, 'node'=>3, 'user_id'=>1, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>4, 'tree'=>1, 'branch'=>2, 'node'=>1, 'user_id'=>2, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>5, 'tree'=>1, 'branch'=>2, 'node'=>2, 'user_id'=>2, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>6, 'tree'=>1, 'branch'=>2, 'node'=>3, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>7, 'tree'=>1, 'branch'=>3, 'node'=>1, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 2, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>8, 'tree'=>1, 'branch'=>3, 'node'=>2, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 2, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>9, 'tree'=>1, 'branch'=>3, 'node'=>3, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 3, 2013), 'summary'=>'test summary', 'data'=>'data' ),		
		    );

		try{
			$result = $this->cls->query(null,null,null, false);
		}
		catch (FOX_exception $child){
			throw new FOX_exception( array(
						    'numeric'=>2,
						    'text'=>"Query exception",
						    'data'=>array( "args"=>null, "columns"=>null, "ctrl"=>null, "returnwords"=>false),
						    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						    'child'=>$child
						)
			);
		}			
		$this->assertEquals($check_data, $result);
		
		
		// Test query single arg
		// ===============================================================		
		$check_data = array(
				    array( 'id'=>9, 'tree'=>1, 'branch'=>3, 'node'=>3, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 3, 2013), 'summary'=>'test summary', 'data'=>'data' )
		    );

		try{
			$args = array( array("col"=>"id", "op"=>"=", "val"=>9 ) );
			$result = $this->cls->query($args,null,null, false);
		}
		catch (FOX_exception $child){
			throw new FOX_exception( array(
						    'numeric'=>2,
						    'text'=>"Query exception",
						    'data'=>array( "args"=>$args, "columns"=>null, "ctrl"=>null, "returnwords"=>false),
						    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						    'child'=>$child
						)
			);
		}			
		$this->assertEquals($check_data, $result);
		
		// Test query multiple args
		// ===============================================================		
		$check_data = array(
				    array( 'id'=>4, 'tree'=>1, 'branch'=>2, 'node'=>1, 'user_id'=>2, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>5, 'tree'=>1, 'branch'=>2, 'node'=>2, 'user_id'=>2, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>7, 'tree'=>1, 'branch'=>3, 'node'=>1, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 2, 2013), 'summary'=>'test summary', 'data'=>'data' ),
				    array( 'id'=>8, 'tree'=>1, 'branch'=>3, 'node'=>2, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 2, 2013), 'summary'=>'test summary', 'data'=>'data' ),
		    );

		try{
			$args = array(	array("col"=>"level", "op"=>"=", "val"=>2 ),
					array("col"=>"node", "op"=>"<", "val"=>3 )
			);
			$result = $this->cls->query($args,null,null, false);
		}
		catch (FOX_exception $child){
			throw new FOX_exception( array(
						    'numeric'=>2,
						    'text'=>"Query exception",
						    'data'=>array( "args"=>$args, "columns"=>null, "ctrl"=>null, "returnwords"=>false),
						    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						    'child'=>$child
						)
			);
		}			
		$this->assertEquals($check_data, $result);	
		
//		// Test query multiple args multiple values
//		// ===============================================================		
//		$check_data = array(
//				    array( 'id'=>4, 'tree'=>1, 'branch'=>2, 'node'=>1, 'user_id'=>2, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
//				    array( 'id'=>5, 'tree'=>1, 'branch'=>2, 'node'=>2, 'user_id'=>2, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 1, 2013), 'summary'=>'test summary', 'data'=>'data' ),
//				    array( 'id'=>7, 'tree'=>1, 'branch'=>3, 'node'=>1, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 2, 2013), 'summary'=>'test summary', 'data'=>'data' ),
//				    array( 'id'=>8, 'tree'=>1, 'branch'=>3, 'node'=>2, 'user_id'=>3, 'level'=>2, 'date'=>(int)mktime(0, 0, 0, 1, 2, 2013), 'summary'=>'test summary', 'data'=>'data' ),		
//		    );
//
//		try{
//			$args = array(	array("col"=>"branch",	"op"=>"=",  "val"=>array(2,3) ),
//					array("col"=>"node",	"op"=>"=",  "val"=>1 )
//			);
//			$result = $this->cls->query($args,null,null, false);
//		}
//		catch (FOX_exception $child){
//			throw new FOX_exception( array(
//						    'numeric'=>2,
//						    'text'=>"Query exception",
//						    'data'=>array( "args"=>$args, "columns"=>null, "ctrl"=>null, "returnwords"=>false),
//						    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
//						    'child'=>$child
//						)
//			);
//		}			
//		$this->assertEquals($check_data, $result);		
		
	}	
	
	function tearDown() {

		$this->cls->uninstall();
		$this->tree->uninstall();
		$this->branch->uninstall();
		$this->node->uninstall();
		parent::tearDown();
	}


}

	
?>
