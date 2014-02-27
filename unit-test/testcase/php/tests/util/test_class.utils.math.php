<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - MTH UTILS
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

class utils_math extends RAZ_testCase {


    	function setUp() {

		parent::setUp();
	}


	function test_formatNum() {

		$test_data = array( array( 'test'=> 1000,	    'result'=> '1,000'),
				    array( 'test'=> 1000.1,	    'result'=> '1,000.1'),
				    array( 'test'=> 1000.12,	    'result'=> '1,000.12'),
				    array( 'test'=> 1000.123,	    'result'=> '1,000.123'),
				    array( 'test'=> 123456789.123,  'result'=> '123,456,789.123'),
		);

		foreach ($test_data as $data){

			$this->assertEquals($data['result'], FOX_math::formatNum($data['test']));
		}

	}

	
	function test_siFormat() {

		// NOTE: this test data set isn't completely accurate, because assertEquals() considers
		// "00.00000000" and "0.0" to be EQUAL even though we send them in *as strings*
	    
		$test_data = array( array( 'raw'=> 0,		'digits'=>2,    'result'=> '0.0'),
				    array( 'raw'=> 87,		'digits'=>3,    'result'=> '87.0'),
				    array( 'raw'=> 1000,	'digits'=>3,    'result'=> '1.00K'),
				    array( 'raw'=> 22639,	'digits'=>3,    'result'=> '22.6K'),
				    array( 'raw'=> 108153,	'digits'=>4,    'result'=> '108.2K'),
				    array( 'raw'=> 2974301,	'digits'=>3,    'result'=> '2.97M'),
				    array( 'raw'=> 830195287,   'digits'=>5,    'result'=> '830.20M'),
				    array( 'raw'=> 3081582000,  'digits'=>2,    'result'=> '3.1G')
		);

		foreach ($test_data as $data){

			$this->assertEquals($data['result'], FOX_math::siFormat($data['raw'], $data['digits']));
		}

	}
	
		
	function test_convertToBase() {
	    
	    
		$check_str = "12345678900987654321";
		
		$base_16_chars = '0123456789abcdef';
		$base_62_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		
		$result = FOX_math::convertToBase($check_str, $base_16_chars);
		
		$this->assertEquals($result, "ab54a98cdc6770b1");
		
		$result = FOX_math::convertToBase($check_str, $base_62_chars);

		$this->assertEquals($result, "eHZl6hFR4k1");
	   	    
	}
	
	
	function test_convertFromBase() {
	    	    
	    
		$check_str = "12345678900987654321";
		
		$base_16_chars = '0123456789abcdef';
		$base_62_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		
		$base_16_str = "ab54a98cdc6770b1";
		$base_62_str = "eHZl6hFR4k1";
		
		$result = FOX_math::convertFromBase($base_16_str, $base_16_chars);
		
		$this->assertEquals($result, $check_str);
		
		$result = FOX_math::convertFromBase($base_62_str, $base_62_chars);

		$this->assertEquals($result, $check_str);
	    
	}	


	function tearDown() {
	    
		parent::tearDown();
	}
	
}

?>