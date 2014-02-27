<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - MEMORY CACHE | TRANSIENT | THREAD
 * Tests the operation of the memory cache when using the transient thread cache
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


require_once ( dirname( __FILE__ ) . '/abstract_cache.driver.functions.php' );
require_once ( dirname( __FILE__ ) . '/abstract_cache.driver.ops.php' );


class core_mCache_driver_thread_ops extends core_mCache_driver_ops {

    
    	function setUp() {
		
		parent::setUp();
	
		$this->cls = new FOX_mCache_driver_thread();				

		if( !$this->cls->isActive() ){

			$this->markTestSkipped('Thread cache is not active on this server');
		}
	}
			
	function tearDown() {

		parent::tearDown();
	}

}


class core_mCache_driver_thread_classFunctions extends core_mCache_driver_classFunctions {
    
        
    	function setUp() {
		
		parent::setUp();
	
		$this->cls = new FOX_mCache_driver_thread();			

		if( !$this->cls->isActive() ){

			$this->markTestSkipped('Thread cache is not active on this server');
		}
	}
	
    	
	function tearDown() {

		parent::tearDown();
	}	
	
}


?>