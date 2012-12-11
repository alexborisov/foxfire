<?php

/**
 * FOXFIRE DEBUG HANDLER
 * Global singleton that accepts and processes debug events
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Util
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_debugHandler {

    
	var $init_time;				    
	
	var $log_events;
	
	var $log_buffer;	
	
	var $intercept_events;	
	

	// ============================================================================================================ //


	public function  __construct() {

		$this->log_events = array();
		$this->log_buffer = array();
		$this->intercept_events = array();
		
	}


	public function event($data){

	    
		if( array_key_exists($data['pid'], $this->trap_buffer) ){
		
		
		}
		
		$this->buffer[] = $data;
	}


} // End of class FOX_exceptionHandler

?>