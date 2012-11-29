<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - DATASTORE VALIDATORS
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

class core_datastore_validators extends RAZ_testCase {

	
    	function setUp() {
	    
		parent::setUp();
		
		$this->struct = array(

			"table" => "FOX_dataStore_paged_tester_validators",
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
		
	}
	
	
       /**
	* Test fixture for dropMulti() method, trie mode, cold cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_validateKey() {
	    
	    			
		$cls = new FOX_dataStore_validator($this->struct);
		
		
		// SCALAR INT
		// #################################################################
		
		
		// PASS on scalar int when scalar int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'scalar',
							    'var'=>7
			));	
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $result);
		
		
		// FAIL on string when scalar int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'scalar',
							    'var'=>'foo'
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);
		
		
		// FAIL on int equivalent string when scalar int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'scalar',
							    'var'=>'17'
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);		
		
		
		// FAIL on null when scalar int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'scalar',
							    'var'=>null
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);
		
		
		// FAIL on int array when scalar int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'scalar',
							    'var'=>array(1,2)
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);
		
		
		// FAIL on empty array when scalar int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'scalar',
							    'var'=>array()
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);		
		
		
		
		// ARRAY INT
		// #################################################################		
		
		
		// PASS on array int when array int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'array',
							    'var'=>array(7)
			));	
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $result);
		
		
		// PASS on empty array when array int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'array',
							    'var'=>array()
			));	
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $result);		
		
		
		// FAIL on null when array int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'array',
							    'var'=>null
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);		
		
		
		// FAIL on scalar int when array int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'array',
							    'var'=>7
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);
		
		
		// FAIL on array string when array int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'array',
							    'var'=>array('foo','bar')
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);			
		
		
		// FAIL on array mixed when array int expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'array',
							    'var'=>array(1,'bar',7,8)
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);	
		
		
		// FAIL on int equivalent string keys
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'int',
							    'format'=>'array',
							    'var'=>array(1,'2',7,8)
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);		
		
		
		
		// SCALAR STRING
		// #################################################################
		
		
		// PASS on scalar string when scalar string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'scalar',
							    'var'=>'foo'
			));	
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $result);
		
		
		// FAIL on int when scalar string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'scalar',
							    'var'=>7
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);
		
		
		// FAIL on int equivalent string when scalar string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'scalar',
							    'var'=>'17'
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);		
		
		
		// FAIL on null when scalar string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'scalar',
							    'var'=>null
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);
		
		
		// FAIL on string array when scalar string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'scalar',
							    'var'=>array('foo','bar')
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);
		
		
		// FAIL on empty array when scalar string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'scalar',
							    'var'=>array()
			));				
			
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);		
		
		
		
		// ARRAY STRING
		// #################################################################		
		
		
		// PASS on array string when array string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'array',
							    'var'=>array('foo')
			));	
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $result);
		
		
		// PASS on empty array when array string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'array',
							    'var'=>array()
			));	
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals(true, $result);		
		
		
		// FAIL on null when array string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'array',
							    'var'=>null
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);		
		
		
		// FAIL on scalar string when array string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'array',
							    'var'=>'foo'
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);
		
		
		// FAIL on array int when array string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'array',
							    'var'=>array(1,2)
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);			
		
		
		// FAIL on array mixed when array string expected
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'array',
							    'var'=>array('foo','bar',7,'baz')
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);	
		
		
		// FAIL on int equivalent string keys
		// ===========================================
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>'string',
							    'format'=>'array',
							    'var'=>array('A','2','B','C')
			));							
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));		    
		}
		
		$this->assertNotEquals(true, $result);	
		
		
	}
	
	
	
       /**
	* Test fixture for dropMulti() method, trie mode, cold cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_validateKTrie() {
	return;   
	    
		$struct = array(

			"table" => "FOX_dataStore_paged_tester_validators",
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
		
		$cls = new FOX_dataStore_validator($struct);
		
		
		try {			
			$result = $cls->validateKey( array(
							    'type'=>$this->cols['L' . $level]['type'],
							    'format'=>'scalar',
							    'var'=>$row[$this->cols['L' . $level]['db_col']]
			));	
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
	}
	
	
	function tearDown() {
	   
		parent::tearDown();
	}

    
}

?>