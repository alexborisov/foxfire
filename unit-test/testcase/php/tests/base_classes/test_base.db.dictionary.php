<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - DICTIONARY BASE CLASS
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

class FOX_test_dictionary extends FOX_dictionary_base {


        // DB table names and structures are hard-coded into the class. This allows class methods to be
	// fired from an AJAX call, without loading the entire BP stack.

	public static $struct = array(

		"table" => "FOX_test_dictionary",
		"engine" => "InnoDB", // Required for transactions
		"cache_namespace" => "FOX_test_dictionary",
		"cache_strategy" => "paged",
		"cache_engine" => array("memcached", "redis", "apc", "thread"),	 	    
		"columns" => array(
		    "id" =>	array(	"php"=>"int",	"sql"=>"smallint",	"format"=>"%d", "width"=>null,"flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
		    "token" =>	array(	"php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>"UNIQUE"),
		 )
	);


	// PHP allows this: $foo = new $class_name; $result = $foo::$struct; but does not allow this: $result = $class_name::$struct;
	// or this: $result = $class_name::get_struct(); ...so we have to do this: $result = call_module_func( array($class_name,'_struct') );

	public static function _struct() {

		return self::$struct;
	}

	// ================================================================================================================

	public function __construct($args=null){
	    

		$this->process_id = 1337;

		
		$this->init($args=null);
		
		
	}


} // End class FOX_logging_dictionary_tree

class core_base_dictionary extends RAZ_testCase {

	var $cls;
	var $mCache;

	function setUp() {

		parent::setUp();

		// Install the db table
		// ===========================================
		
		$this->cls = new FOX_test_dictionary();
		
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

	function test_addToken_Single() {

		// Test adding one token
		// ======================================================
		$token = "test token";


		try {
			$result_add = $this->cls->addToken( $token);
		}
		catch(FOX_exception $child){
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check Function Return
		// ======================================================
		$id = 1;
		$this->assertEquals($id, $result_add);

		// Check db
		// ======================================================
	    	$db = new FOX_db();

		global $fox;

		try{
			$result_select = $db->runSelectQuery(FOX_test_dictionary::_struct(), 
								$args=array( array( "col"=>"token", "op"=>"=", "val"=>$token) ), 
								$column=array("mode"=>"include", "col"=>"id"), 
								$ctrl=array("format"=>"var")
				);
		}
		catch(FOX_exception $child){
		    
			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));
			
		}
		$this->assertEquals($id, $result_select);

		// Check class cache
		// ======================================================

		$this->assertEquals($id, $this->cls->cache["tokens"][$this->cls->generateHash($token)]);

		$this->assertEquals($token, $this->cls->cache["ids"][$id]);
		
		// Test adding invalid tokens
		// ======================================================

		// Test adding null token		
		try {
			$result_add = $this->cls->addToken( null);
			// Execution will halt on the previous line if addToken() throws an exception
			$this->fail("Method addToken() failed to throw an exception on invalid token type");			
		}
		catch(FOX_exception $child){

		}

		// Test adding bool token		
		try {
			$result_add = $this->cls->addToken( true);
			// Execution will halt on the previous line if addToken() throws an exception
			$this->fail("Method addToken() failed to throw an exception on invalid token type");			
		}
		catch(FOX_exception $child){

		}		
		
		// Test adding int token		
		try {
			$result_add = $this->cls->addToken( 1);
			// Execution will halt on the previous line if addToken() throws an exception
			$this->fail("Method addToken() failed to throw an exception on invalid token type");			
		}
		catch(FOX_exception $child){

		}
		
		// Test adding float token		
		try {
			$result_add = $this->cls->addToken( 1.7);
			// Execution will halt on the previous line if addToken() throws an exception
			$this->fail("Method addToken() failed to throw an exception on invalid token type");			
		}
		catch(FOX_exception $child){

		}	
		
		// Test adding an array as a token		
		try {
			$result_add = $this->cls->addToken(array(array("one")));
			// Execution will halt on the previous line if addToken() throws an exception
			$this->fail("Method addToken() failed to throw an exception on invalid token type");			
		}
		catch(FOX_exception $child){

		}
		
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";		
		
		// Test adding an object as a token		
		try {
			$result_add = $this->cls->addToken($test_obj);
			// Execution will halt on the previous line if addToken() throws an exception
			$this->fail("Method addToken() failed to throw an exception on invalid token type");			
		}
		catch(FOX_exception $child){

		}
		
	}


	function test_addToken_Multi() {

		// Test adding multiple tokens
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check function return
		// =======================================================

		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		

		$this->assertEquals($exp_array, $add_result);


		// Check db entry
		// =======================================================
		$db = new FOX_db();
		try{
			$select_result = $db->runSelectQuery(FOX_test_dictionary::_struct(), 
								$args=null, 
								$columns=null, 
								$ctrl=array("format"=>"array_array") );
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}
		$exp_array = array(	array( "id"=>5, "token"=>"five"),
					array( "id"=>4, "token"=>"four"),
					array( "id"=>1, "token"=>"one"),
					array( "id"=>3, "token"=>"three"),
					array( "id"=>2, "token"=>"two")
		);

		$this->assertEquals( $exp_array, $select_result);

		// Check class cache
		// =======================================================

		$exp_array = array( $this->cls->generateHash("one")=>1, 
				    $this->cls->generateHash("two")=>2, 
				    $this->cls->generateHash("three")=>3, 
				    $this->cls->generateHash("four")=>4, 
				    $this->cls->generateHash("five")=>5
			);

		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);

		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);
		
		// Test adding invalid tokens
		// ======================================================
		
		// Test adding one invalid one valid tokens 
		try {
			$result_add = $this->cls->addToken( array("six",true));
			// Execution will halt on the previous line if addToken() throws an exception
			$this->fail("Method addToken() failed to throw an exception on invalid token type");			
		}
		catch(FOX_exception $child){

		}	

		// Test adding multiple invalid tokens 
		try {
			$result_add = $this->cls->addToken( array(12,1.7));
			// Execution will halt on the previous line if addToken() throws an exception
			$this->fail("Method addToken() failed to throw an exception on invalid token type");			
		}
		catch(FOX_exception $child){

		}	
		
		// Check class cache
		// =======================================================

		$exp_array = array( $this->cls->generateHash("one")=>1, 
				    $this->cls->generateHash("two")=>2, 
				    $this->cls->generateHash("three")=>3, 
				    $this->cls->generateHash("four")=>4, 
				    $this->cls->generateHash("five")=>5
			);

		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);

		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);		
		
	}

	function test_dbFetchToken_Single() {

		// Load db then empty cache
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}
		try{
		    	$result = $this->cls->flushCache();
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		$this->cls->cache = array();
		
		// test single fetch
		// ======================================================
		try{

			$this->assertEquals(array(1=>"one"),$this->cls->dbFetchToken("one") );

			$this->assertEquals(array(2=>"two"),$this->cls->dbFetchToken("two") );

			$this->assertEquals(array(3=>"three"),$this->cls->dbFetchToken("three") );

			$this->assertEquals(array(4=>"four"),$this->cls->dbFetchToken("four") );

			$this->assertEquals(array(5=>"five"),$this->cls->dbFetchToken("five") );
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check class cache
		// =======================================================

		$exp_array = array( $this->cls->generateHash("one")=>1, 
				    $this->cls->generateHash("two")=>2, 
				    $this->cls->generateHash("three")=>3, 
				    $this->cls->generateHash("four")=>4, 
				    $this->cls->generateHash("five")=>5
			);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);

		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);
		
		// Test invalid tokens
		// ======================================================

		// Test fetching null token		
		try {
			$result = $this->cls->dbFetchToken( null);
			// Execution will halt on the previous line if dbFetchToken() throws an exception
			$this->fail("Method dbFetchToken() failed to throw an exception on invalid token type");			
		}
		catch(FOX_exception $child){

		}

		// Test fetching bool token		
		try {
			$result = $this->cls->dbFetchToken( true);			
		}
		catch(FOX_exception $child){

		}		
		
		$this->assertEquals(array(), $result);
		
		// Test fetching int token		
		try {
			$result = $this->cls->dbFetchToken( 1);			
		}
		catch(FOX_exception $child){

		}
		
		$this->assertEquals(array(), $result);
		
		// Test fetching float token		
		try {
			$result = $this->cls->dbFetchToken( 1.7);			
		}
		catch(FOX_exception $child){

		}	
		
		$this->assertEquals(array(), $result);		
		
//		// Test fetching an array as a token		
//		try {
//			$result = $this->cls->dbFetchToken(array(array("one")));
//			// Execution will halt on the previous line if dbFetchToken() throws an exception
//			$this->fail("Method dbFetchToken() failed to throw an exception on invalid token type");			
//		}
//		catch(FOX_exception $child){
//		    
//			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));
//		}
//		
//		// Test fetching an object as a token	
//		
//		$test_obj = new stdClass();
//		$test_obj->foo = "11";
//		$test_obj->bar = "test_Bar";				
//		
//		try {
//			$result = $this->cls->dbFetchToken($test_obj);
//			// Execution will halt on the previous line if dbFetchToken() throws an exception
//			$this->fail("Method dbFetchToken() failed to throw an exception on invalid token type");			
//		}
//		catch(FOX_exception $child){
//
//		}
				
		
	}


	function test_dbFetchToken_Multi() {

		// load db then empty cache
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}
		try{
		    $result = $this->cls->flushCache();
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		$this->cls->cache = array();
		
		// Test Multiple fetch
		// ======================================================


		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");

		try{
			$this->assertEquals($exp_array, $this->cls->dbFetchToken($add_tokens));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}


		// Check class cache
		// =======================================================

		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);

		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);

		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);
		
		// Test invalid tokens
		// ======================================================

		// Test fetching missing token		
		try {
			$result = $this->cls->dbFetchToken( "six");

		}
		catch(FOX_exception $child){

		}
		$this->assertEquals(array(), $result);
		
		// Test fetching multiple tokens one invalid		
		try {
			$result = $this->cls->dbFetchToken( array( "five", 12));			
		}
		catch(FOX_exception $child){

		}
		$this->assertEquals(array(5=>"five"), $result);
		
		// Test fetching multiple tokens multiple invalid		
		try {
			$result = $this->cls->dbFetchToken( array( 1.7, 12));			
		}
		catch(FOX_exception $child){

		}		
		$this->assertEquals(array(), $result);

	}

	function test_dbFetchId_Single() {

		// load db then empty cache
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}
		try{
		    $result = $this->cls->flushCache();
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		
		$this->cls->cache = array();
		
		// test single fetch
		// ======================================================

		try{

			$this->assertEquals(array(1=>"one"),$this->cls->dbFetchId(1) );

			$this->assertEquals(array(2=>"two"),$this->cls->dbFetchId(2) );

			$this->assertEquals(array(3=>"three"),$this->cls->dbFetchId(3) );

			$this->assertEquals(array(4=>"four"),$this->cls->dbFetchId(4) );

			$this->assertEquals(array(5=>"five"),$this->cls->dbFetchId(5) );
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check class cache
		// =======================================================

		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);

		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);

		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);

		
		// Test invalid ids
		// ======================================================

		// Test fetching null id		
		try {
			$result = $this->cls->dbFetchId( null);
			// Execution will halt on the previous line if dbFetchId() throws an exception
			$this->fail("Method dbFetchId() failed to throw an exception on invalid id type");			
		}
		catch(FOX_exception $child){

		}

//		// Test fetching bool id		
//		try {
//			$result = $this->cls->dbFetchId( true);			
//		}
//		catch(FOX_exception $child){
//
//		}		
//		
//		$this->assertEquals(array(), $result);
		
		// Test fetching string id		
		try {
			$result = $this->cls->dbFetchId( "one");			
		}
		catch(FOX_exception $child){

		}
		
		$this->assertEquals(array(), $result);
		
//		// Test fetching float id		
//		try {
//			$result = $this->cls->dbFetchId( 1.7);			
//		}
//		catch(FOX_exception $child){
//
//		}	
//		
//		$this->assertEquals(array(), $result);		
		
//		// Test fetching an array as an id		
//		try {
//			$result = $this->cls->dbFetchId(array(array(1)));
//			// Execution will halt on the previous line if dbFetchId() throws an exception
//			$this->fail("Method dbFetchId() failed to throw an exception on invalid id type");			
//		}
//		catch(FOX_exception $child){
//		    
//			$this->fail($child->dumpString(array('depth'=>1, 'data'=>true)));
//		}
		
//		// Test fetching an object as a id	
//		
//		$test_obj = new stdClass();
//		$test_obj->foo = "11";
//		$test_obj->bar = "test_Bar";				
//		
//		try {
//			$result = $this->cls->dbFetchId($test_obj);
//			// Execution will halt on the previous line if dbFetchId() throws an exception
//			$this->fail("Method dbFetchId() failed to throw an exception on invalid id type");			
//		}
//		catch(FOX_exception $child){
//
//		}		
		
		
	}

	function test_dbFetchId_Multi() {

		// load db then empty cache
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}
		try{
		    $result = $this->cls->flushCache();
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		$this->cls->cache = array();
		
		// Test multi fetch
		// ======================================================

		$add_ids = array(1,2,3,4,5);
		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		try{
		$this->assertEquals($exp_array, $this->cls->dbFetchId($add_ids));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check class cache
		// =======================================================

		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);

		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);

		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);

	}

	function test_cacheFetchToken_Single() {

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Clear class cache
		// ======================================================
		$this->cls->cache = array();
		
		
		// Test cacheFetchToken
		// ======================================================

		try{
			
			$this->assertEquals(array(1=>"one"), $this->cls->cacheFetchToken("one") );

			$this->assertEquals(array(2=>"two"), $this->cls->cacheFetchToken("two") );

			$this->assertEquals(array(3=>"three"), $this->cls->cacheFetchToken("three") );

			$this->assertEquals(array(4=>"four"), $this->cls->cacheFetchToken("four") );

			$this->assertEquals(array(5=>"five"), $this->cls->cacheFetchToken("five") );

		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check class cache
		// =======================================================

		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);

		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);

		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);
	}

	function test_cacheFetchToken_Multi() {

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Clear class cache
		// ======================================================
		$this->cls->cache = array();		
		
		// Test cacheFetchToken
		// ======================================================
		try{

			$exp_array  = array(	1=>"one", 
						2=>"two", 
						3=>"three", 
						4=>"four",
						5=>"five"
				);

			$this->assertEquals($exp_array, $this->cls->cacheFetchToken($add_tokens) );

		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check class cache
		// =======================================================

		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);

		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);

		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);		
	}

	function test_cacheFetchId_Single() {

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Clear class cache
		// ======================================================
		$this->cls->cache = array();		
		
		// Test cacheFetchId
		// ======================================================

		try{
			$this->assertEquals(array(1=>"one"), $this->cls->cacheFetchId(1) );

			$this->assertEquals(array(2=>"two"), $this->cls->cacheFetchId(2) );

			$this->assertEquals(array(3=>"three"), $this->cls->cacheFetchId(3) );

			$this->assertEquals(array(4=>"four"), $this->cls->cacheFetchId(4) );

			$this->assertEquals(array(5=>"five"), $this->cls->cacheFetchId(5) );
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}
		
		// Check class cache
		// =======================================================

		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);

		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);

		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);		
		
	}

	function test_cacheFetchId_Multi() {

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Clear class cache
		// ======================================================
		$this->cls->cache = array();		
		
		// Test cacheFetchId
		// ======================================================
		try{

			$ids = array(1, 2, 3, 4, 5);
			$exp_array = array (1=>"one", 2=> "two", 3=> "three", 4=>"four", 5=> "five");
			$this->assertEquals($exp_array ,$this->cls->cacheFetchId($ids));

		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check class cache
		// =======================================================

		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);

		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);

		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);		

	}

	function test_getToken_Single_Class() {

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}
		
		//test single class cache get
		// ======================================================
		try{
			$this->assertEquals(1,$this->cls->getToken("one"));

			$this->assertEquals(2,$this->cls->getToken("two"));

			$this->assertEquals(3,$this->cls->getToken("three"));

			$this->assertEquals(4,$this->cls->getToken("four"));

			$this->assertEquals(5,$this->cls->getToken("five"));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));
		}
	}

	function test_getToken_Multi_Class() {

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}	
		
		// Test Multi Class Cache getToken
		// ======================================================
		try{
			$exp_array  = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
			$this->assertEquals($exp_array, $this->cls->getToken($add_tokens));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}
	}

	function test_getToken_Single_Persistent() {

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Clear class cache
		$this->cls->cache = array( );		
		
		// Test single persistent cache get
		// ======================================================

		try{
			$this->assertEquals(1,$this->cls->getToken("one"));

			$this->assertEquals(2,$this->cls->getToken("two"));

			$this->assertEquals(3,$this->cls->getToken("three"));

			$this->assertEquals(4,$this->cls->getToken("four"));

			$this->assertEquals(5,$this->cls->getToken("five"));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check that class cache has been rebuilt
		// ======================================================
		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);

		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		
		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");		
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);
	}

	function test_getToken_Multi_Persistent() {

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Clear class cache
		$this->cls->cache = array( );		
		
		// Test Multi Persistent Cache get
		// ======================================================
		try{
			$exp_array  = array( 1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
			$this->assertEquals($exp_array, $this->cls->getToken($add_tokens));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check that class cache has been rebuilt
		// ======================================================
		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		
		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");		
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);
	}

	function test_getToken_Single_Db() {

		// load db and flush cache
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Clear class cache
		// ======================================================		
		$this->cls->cache = array( );				
		
		// Test single db get
		// ======================================================

		try{
			$this->assertEquals(1,$this->cls->getToken("one"));

			$this->assertEquals(2,$this->cls->getToken("two"));

			$this->assertEquals(3,$this->cls->getToken("three"));

			$this->assertEquals(4,$this->cls->getToken("four"));

			$this->assertEquals(5,$this->cls->getToken("five"));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check that class cache has been rebuilt
		// ======================================================
		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		
		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");		
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);

	}

	function test_getToken_Multi_Db() {

		// load db and flush cache
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Clear class cache
		$this->cls->cache = array( );				
		
		// Test Multi Db get
		// ======================================================
		try{
			$exp_array  = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
			$this->assertEquals($exp_array, $this->cls->getToken($add_tokens));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check that class cache has been rebuilt
		// ======================================================
		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		
		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");		
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);
	}

	function test_getToken_Single_Insert() {

		// Test single insert and get
		// ======================================================

		try{
			$this->assertEquals(1,$this->cls->getToken("one"));

			$this->assertEquals(2,$this->cls->getToken("two"));

			$this->assertEquals(3,$this->cls->getToken("three"));

			$this->assertEquals(4,$this->cls->getToken("four"));

			$this->assertEquals(5,$this->cls->getToken("five"));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));
		}

		// Check class cache
		// ======================================================
		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		
		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);

	}

	function test_getToken_Multi_Insert() {

		// Test multi insert and  get
		// ======================================================
		try{
			$exp_array  = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
			$this->assertEquals($exp_array, $this->cls->getToken(array_values($exp_array)));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check class cache
		// ======================================================
		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		
		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);
		
	}

	function test_getToken_Mixed_Insert_Get() {

		// load db with first two tokens
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$this->cls->addToken($add_tokens[0]);
			$this->cls->addToken($add_tokens[1]);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Test multi mixed insert and get
		// ======================================================
		try{
			$exp_array  = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
			$this->assertEquals($exp_array, $this->cls->getToken(array_values($add_tokens)));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check class cache
		// ======================================================
		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		
		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);

	}

	function test_getId_Single_Class(){

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		//test single class cache get
		// ======================================================
		try{
			$this->assertEquals("one",$this->cls->getId(1));

			$this->assertEquals("two",$this->cls->getId(2));

			$this->assertEquals("three",$this->cls->getId(3));

			$this->assertEquals("four",$this->cls->getId(4));

			$this->assertEquals("five",$this->cls->getId(5));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

	}

	function test_getId_Multi_Class(){

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}


		// Test Multi Class Cache getId
		// ======================================================
		try{
			$get_ids = array(1, 2, 3, 4, 5);
			$exp_array  = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
			$this->assertEquals($exp_array, $this->cls->getId($get_ids));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}
	}

	function test_getId_Single_Persistent(){

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Clear class cache
		// ======================================================
		$this->cls->cache = array( );

		//test single class cache get
		// ======================================================
		try{
			$this->assertEquals("one",$this->cls->getId(1));

			$this->assertEquals("two",$this->cls->getId(2));

			$this->assertEquals("three",$this->cls->getId(3));

			$this->assertEquals("four",$this->cls->getId(4));

			$this->assertEquals("five",$this->cls->getId(5));

		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check class cache
		// ======================================================
		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		
		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);

	}

	function test_getId_Multi_Persistent(){

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Clear class cache
		// ======================================================
		$this->cls->cache = array( );

		// Test Multi Class Cache getId
		// ======================================================
		try{
			$get_ids = array(1, 2, 3, 4, 5);
			$exp_array  = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
			$this->assertEquals($exp_array, $this->cls->getId($get_ids));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check class cache
		// ======================================================
		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		
		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);

	}

	function test_getId_Single_Db(){

		// load db and flush cache
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Clear class cache
		// ======================================================		
		$this->cls->cache = array( );						
		
		//test single class cache get
		// ======================================================
		try{
			$this->assertEquals("one",$this->cls->getId(1));

			$this->assertEquals("two",$this->cls->getId(2));

			$this->assertEquals("three",$this->cls->getId(3));

			$this->assertEquals("four",$this->cls->getId(4));

			$this->assertEquals("five",$this->cls->getId(5));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check class cache
		// ======================================================
		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		
		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);

	}

	function test_getId_Multi_Db(){

		// load db and flush cache
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));
		}
		
		// Clear class cache
		// ======================================================		
		$this->cls->cache = array( );						

		// Test Multi Class Cache getId
		// ======================================================
		try{
			$get_ids = array(1, 2, 3, 4, 5);
			$exp_array  = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
			$this->assertEquals($exp_array, $this->cls->getId($get_ids));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check class cache
		// ======================================================
		$exp_array = array($this->cls->generateHash("one")=>1, $this->cls->generateHash("two")=>2, $this->cls->generateHash("three")=>3, $this->cls->generateHash("four")=>4, $this->cls->generateHash("five")=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		
		$exp_array = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
		$this->assertEquals($exp_array, $this->cls->cache["ids"]);

	}

	function test_dropAll() {

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Test dropAll
		// ======================================================
		try{
			$this->cls->dropAll();
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}


		$db = new FOX_db();

		// Check Db has no rows
		// ======================================================
		try{
			$this->assertEquals( false, $db->runSelectQuery(FOX_test_dictionary::$struct, $args=null, $columns=null, $ctrl=null));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check Class cache is empty
		$this->assertEquals( array(), $this->cls->cache);

	}

	function test_dropToken_Single() {

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Test  single dropToken
		// ======================================================

		try{
			$this->assertEquals(1, $this->cls->dropToken("one"));

			$this->assertEquals(1, $this->cls->dropToken("two"));

			$this->assertEquals(1, $this->cls->dropToken("three"));

			$this->assertEquals(1, $this->cls->dropToken("four"));

			$this->assertEquals(1, $this->cls->dropToken("five"));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check Db has no rows
		// ======================================================
		try{
			$db = new FOX_db();
			$this->assertEquals( false, $db->runSelectQuery(FOX_test_dictionary::$struct, $args=null, $columns=null, $ctrl=null));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check Class cache is empty
		$this->assertEquals( array('ids'=>array(), 'tokens'=>array()), $this->cls->cache);

	}

	function test_dropToken_Multi() {

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Test multi dropToken
		// ======================================================

		try{
			$this->assertEquals(5, $this->cls->dropToken($add_tokens));

		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check Db has no rows
		// ======================================================
		try{
			$db = new FOX_db();
			$this->assertEquals( false, $db->runSelectQuery(FOX_test_dictionary::$struct));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check Class cache is empty
		$this->assertEquals(array('ids'=>array(), 'tokens'=>array()), $this->cls->cache);

	}

	function test_dropId_Single() {

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Test  single dropId
		// ======================================================

		try{
			$this->assertEquals(1, $this->cls->dropId(1));

			$this->assertEquals(1, $this->cls->dropId(2));

			$this->assertEquals(1, $this->cls->dropId(3));

			$this->assertEquals(1, $this->cls->dropId(4));

			$this->assertEquals(1, $this->cls->dropId(5));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check Db has no rows
		// ======================================================
		try{
			$db = new FOX_db();
			$this->assertEquals( false, $db->runSelectQuery(FOX_test_dictionary::$struct));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check Class cache is empty
		$this->assertEquals( array('ids'=>array(), 'tokens'=>array()), $this->cls->cache);

	}

	function test_dropId_Multi() {

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Test  single dropId
		// ======================================================

		try{
			$drop_ids = array(1, 2,3,4,5);
			$this->assertEquals(5, $this->cls->dropId($drop_ids));

		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check Db has no rows
		// ======================================================
		try{
			$db = new FOX_db();
			$this->assertEquals( false, $db->runSelectQuery(FOX_test_dictionary::$struct));
		}
		catch(FOX_exception $child){

			$this->fail($child->dumpString(array('depth'=>50, 'data'=>true)));

		}

		// Check Class cache is empty
		$this->assertEquals( array('ids'=>array(), 'tokens'=>array()), $this->cls->cache);

	}


	function tearDown() {

		$this->cls->uninstall();

		parent::tearDown();
	}


}



?>
