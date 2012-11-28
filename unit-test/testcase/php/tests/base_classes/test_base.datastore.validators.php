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
	}
	
	
       /**
	* Test fixture for dropMulti() method, trie mode, cold cache
	*
	* @version 1.0
	* @since 1.0
	* 
        * =======================================================================================
	*/	
	public function test_dropMulti_trie_COLD() {
	    
	    
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
		
	}
	
	
	function tearDown() {
	   
		parent::tearDown();
	}

    
}

?>