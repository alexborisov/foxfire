<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - VERSION CHECK CLASS
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

class util_versionCheck extends RAZ_testCase {


	var $cls;	    // Class instance

	// ============================================================================================================ //

    	function setUp() {

		parent::setUp();		
		$this->cls = new FOX_version();
	}



	function test_equals(){

	    
		$test_data = array();

		// "=" ===============================================================================

		$test_data[] = array("a"=>"7",		"b"=>"7",	    "op"=>"=",	"res"=>true);
		$test_data[] = array("a"=>"5",		"b"=>"11",	    "op"=>"=",	"res"=>false);
		$test_data[] = array("a"=>"1",		"b"=>"1.0",	    "op"=>"=",	"res"=>true);
		$test_data[] = array("a"=>"1",		"b"=>"1.0.0",	    "op"=>"=",	"res"=>true);
		$test_data[] = array("a"=>"1",		"b"=>"1.0.0.0",	    "op"=>"=",	"res"=>true);
		$test_data[] = array("a"=>"1",		"b"=>"1.00",	    "op"=>"=",	"res"=>true);
		$test_data[] = array("a"=>"1.0",	"b"=>"1",	    "op"=>"=",	"res"=>true);
		$test_data[] = array("a"=>"1.0",	"b"=>"0.1",	    "op"=>"=",	"res"=>false);
		$test_data[] = array("a"=>"1.0.0",	"b"=>"1",	    "op"=>"=",	"res"=>true);
		$test_data[] = array("a"=>"1.00",	"b"=>"1",	    "op"=>"=",	"res"=>true);
		$test_data[] = array("a"=>"0.1.9",	"b"=>"00.01.9.0",   "op"=>"=",	"res"=>true);

		foreach($test_data as $test ){

			$result = $this->cls->checkVersion($test["a"], $test["b"], $test["op"]);
			$error = "A: " . $test["a"] . " B: " . $test["b"];
			
			$this->assertEquals($test["res"], $result, $error);
			
		}
		unset($test);
		
	}


	function test_notEquals(){

	    
		$test_data = array();

		// "!=" ===============================================================================

		$test_data[] = array("a"=>"7",		"b"=>"9",	    "op"=>"!=",	"res"=>true);
		$test_data[] = array("a"=>"5",		"b"=>"5",	    "op"=>"!=",	"res"=>false);
		$test_data[] = array("a"=>"1.2",	"b"=>"1.0",	    "op"=>"!=",	"res"=>true);
		$test_data[] = array("a"=>"0.11",	"b"=>"1.0.0",	    "op"=>"!=",	"res"=>true);
		$test_data[] = array("a"=>"0.0.1",	"b"=>"1.0.0.0",	    "op"=>"!=",	"res"=>true);
		$test_data[] = array("a"=>"1.00.1",	"b"=>"1.00",	    "op"=>"!=",	"res"=>true);
		$test_data[] = array("a"=>"1.0.1",	"b"=>"1",	    "op"=>"!=",	"res"=>true);
		$test_data[] = array("a"=>"01.0",	"b"=>"0.01",	    "op"=>"!=",	"res"=>true);
		$test_data[] = array("a"=>"1.0.0",	"b"=>"0.1",	    "op"=>"!=",	"res"=>true);
		$test_data[] = array("a"=>"1.001",	"b"=>"1",	    "op"=>"!=",	"res"=>true);
		$test_data[] = array("a"=>"0.1.9",	"b"=>"2.01.9.0",    "op"=>"!=",	"res"=>true);

		foreach($test_data as $test ){

			$result = $this->cls->checkVersion($test["a"], $test["b"], $test["op"]);
			$error = "A: " . $test["a"] . " B: " . $test["b"];
			
			$this->assertEquals($test["res"], $result, $error);
		}
		unset($test);
		
	}


	function test_gte(){

		$test_data = array();

		// ">=" ===============================================================================

		$test_data[] = array("a"=>"7",		"b"=>"7",	    "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"8",		"b"=>"7",	    "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"5",		"b"=>"7",	    "op"=>">=",	"res"=>false);
		$test_data[] = array("a"=>"1",		"b"=>"1.0.0",	    "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"3.1",	"b"=>"3.1.0",	    "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"1",		"b"=>"1.0.0.0",	    "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"1",		"b"=>"1.00",	    "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"11.0",	"b"=>"1",	    "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"1.0.0",	"b"=>"1",	    "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"1.00",	"b"=>"1",	    "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"0.1.9",	"b"=>"00.01.9.0",   "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"6.5.8",	"b"=>"5.5.8",	    "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"9.5.8",	"b"=>"5.5.8",	    "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"15.5.8",	"b"=>"5.5.8",	    "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"15.5.15",	"b"=>"5.5.8",	    "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"7.4.9",	"b"=>"5.5.8",	    "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"6.1.15",	"b"=>"5.5.8",	    "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"6.0.15",	"b"=>"5.5.8",	    "op"=>">=",	"res"=>true);
		$test_data[] = array("a"=>"15.0.15",	"b"=>"16.5.8",	    "op"=>">=",	"res"=>false);
		$test_data[] = array("a"=>"15.0.15.3",	"b"=>"17.5.8.2",    "op"=>">=",	"res"=>false);
		$test_data[] = array("a"=>"5.3.6",	"b"=>"5.3.0",	    "op"=>">=",	"res"=>true);

		foreach($test_data as $test ){

			$result = $this->cls->checkVersion($test["a"], $test["b"], $test["op"]);
			$error = "A: " . $test["a"] . " B: " . $test["b"];
			
			$this->assertEquals($test["res"], $result, $error);
			
		}
		unset($test);
		
	}


	function test_lte(){

		$test_data = array();

		// "<=" ===============================================================================

		$test_data[] = array("a"=>"7",		"b"=>"7",	    "op"=>"<=",	"res"=>true);
		$test_data[] = array("a"=>"7",		"b"=>"8",	    "op"=>"<=",	"res"=>true);
		$test_data[] = array("a"=>"11",		"b"=>"5",	    "op"=>"<=",	"res"=>false);
		$test_data[] = array("a"=>"1",		"b"=>"1.0",	    "op"=>"<=",	"res"=>true);
		$test_data[] = array("a"=>"1",		"b"=>"1.0.0",	    "op"=>"<=",	"res"=>true);
		$test_data[] = array("a"=>"1",		"b"=>"1.0.0.0",	    "op"=>"<=",	"res"=>true);
		$test_data[] = array("a"=>"1",		"b"=>"1.00",	    "op"=>"<=",	"res"=>true);
		$test_data[] = array("a"=>"1.0",	"b"=>"1",	    "op"=>"<=",	"res"=>true);
		$test_data[] = array("a"=>"1.0.0",	"b"=>"1",	    "op"=>"<=",	"res"=>true);
		$test_data[] = array("a"=>"1.00",	"b"=>"1",	    "op"=>"<=",	"res"=>true);
		$test_data[] = array("a"=>"0.1.9",	"b"=>"00.01.9.0",   "op"=>"<=",	"res"=>true);
		$test_data[] = array("a"=>"0.1.9.6",	"b"=>"1.9.6.0",	    "op"=>"<=", "res"=>true);
		$test_data[] = array("a"=>"5.5.8",	"b"=>"4.4.9",	    "op"=>"<=",	"res"=>false);
		$test_data[] = array("a"=>"5.3.8",	"b"=>"4.4.9",	    "op"=>"<=",	"res"=>false);

		foreach($test_data as $test ){

			$result = $this->cls->checkVersion($test["a"], $test["b"], $test["op"]);
			$error = "A: " . $test["a"] . " B: " . $test["b"];
			
			$this->assertEquals($test["res"], $result, $error);
		}
		unset($test);
		
	}


	function test_lt(){

	    
		$test_data = array();

		// "<" ===============================================================================

		$test_data[] = array("a"=>"7",		"b"=>"11",	    "op"=>"<",	"res"=>true);
		$test_data[] = array("a"=>"7",		"b"=>"7",	    "op"=>"<",	"res"=>false);
		$test_data[] = array("a"=>"23",		"b"=>"5",	    "op"=>"<",	"res"=>false);
		$test_data[] = array("a"=>"0.1.9.5",	"b"=>"1.9.5",	    "op"=>"<",	"res"=>true);
		$test_data[] = array("a"=>"0.0.1.7",	"b"=>"0.3",	    "op"=>"<",	"res"=>true);
		$test_data[] = array("a"=>"1",		"b"=>"1.5",	    "op"=>"<",	"res"=>true);
		$test_data[] = array("a"=>"01.0.7",	"b"=>"1.0.7.2",	    "op"=>"<",	"res"=>true);
		$test_data[] = array("a"=>"2",		"b"=>"2.0.0.1",	    "op"=>"<",	"res"=>true);
		$test_data[] = array("a"=>"1.1.1.1",	"b"=>"1.1.2.1",	    "op"=>"<",	"res"=>true);
		$test_data[] = array("a"=>"1.2.1.2",	"b"=>"3.1.2.1",	    "op"=>"<",	"res"=>true);
		$test_data[] = array("a"=>"4.2.1.2",	"b"=>"3.1.2.1",	    "op"=>"<",	"res"=>false);

		foreach($test_data as $test ){

			$result = $this->cls->checkVersion($test["a"], $test["b"], $test["op"]);
			$error = "A: " . $test["a"] . " B: " . $test["b"];
			
			$this->assertEquals($test["res"], $result, $error);
			
		}
		unset($test);

	}


	function test_gt(){

	    
		$test_data = array();

		// ">" ===============================================================================

		$test_data[] = array("a"=>"7",		"b"=>"5",	    "op"=>">",	"res"=>true);
		$test_data[] = array("a"=>"5",		"b"=>"5",	    "op"=>">",	"res"=>false);
		$test_data[] = array("a"=>"5",		"b"=>"7",	    "op"=>">",	"res"=>false);
		$test_data[] = array("a"=>"1.3.1",	"b"=>"1.3.0",	    "op"=>">",	"res"=>true);
		$test_data[] = array("a"=>"1.3.1.0",	"b"=>"1.3.0",	    "op"=>">",	"res"=>true);
		$test_data[] = array("a"=>"1.3.0.1",	"b"=>"1.3",	    "op"=>">",	"res"=>true);
		$test_data[] = array("a"=>"1.3.0",	"b"=>"1.3.0",	    "op"=>">",	"res"=>false);
		$test_data[] = array("a"=>"1.3.1",	"b"=>"1.3",	    "op"=>">",	"res"=>true);
		$test_data[] = array("a"=>"1.3.0.0",	"b"=>"1.3.0",	    "op"=>">",	"res"=>false);
		$test_data[] = array("a"=>"1.03.0",	"b"=>"1.3.0",	    "op"=>">",	"res"=>false);
		$test_data[] = array("a"=>"01.3.0",	"b"=>"1.3.0",	    "op"=>">",	"res"=>false);
		$test_data[] = array("a"=>"2.2.1.2",	"b"=>"3.1.2.1",	    "op"=>">",	"res"=>false);
		$test_data[] = array("a"=>"4.2.1.2",	"b"=>"3.1.2.1",	    "op"=>">",	"res"=>true);

		foreach($test_data as $test ){

			$result = $this->cls->checkVersion($test["a"], $test["b"], $test["op"]);
			$error = "A: " . $test["a"] . " B: " . $test["b"];
			
			$this->assertEquals($test["res"], $result, $error);
			
		}
		unset($test);

	}

	
	function test_format_num() {

	    
		$test_data = array( 
				    array( 'test'=> 1000,		'result'=> '1,000'),
				    array( 'test'=> 1000.1,		'result'=> '1,000.1'),
				    array( 'test'=> 1000.12,		'result'=> '1,000.12'),
				    array( 'test'=> 1000.123,		'result'=> '1,000.123'),
				    array( 'test'=> 123456789.123,	'result'=> '123,456,789.123'),
		);
		
		foreach ($test_data as $test)
		{
			$this->assertEquals($test['result'], FOX_math::formatNum($test['test']));
		}
		unset($test);
		
	}


	function tearDown() {
	    
		parent::tearDown();
	}


	
}

?>