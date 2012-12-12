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
	
	var $triggers;
	
	var $log_buffer;		
	

	// ============================================================================================================ //


	public function  __construct() {

		$this->triggers = array();
		$this->log_buffer = array();
		
	}

	
	public function addTrigger($data){

	    
		if(!FOX_sUtil::keyExists('type', $data) || (($data['type'] != 'log') || ($data['type'] != 'intercept')) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid trigger 'type' paramater",
				'data'=>$data,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			    
		}
		
		$this->log_buffer[] = array(
					    'pid'=>$data['pid'],
					    'text'=>$data['text'],		    
					    'file'=>$data['file'], 
					    'class'=>$data['class'], 
					    'function'=>$data['function'], 
					    'line'=>$data['line'], 
		    
		);
		
		return array();
		
	}
	
	

	public function event($data){


		
		$this->log_buffer[] = array(
					    'pid'=>$data['pid'],
					    'file'=>$data['file'], 
					    'class'=>$data['class'], 
					    'function'=>$data['function'], 
					    'line'=>$data['line'], 
					    'text'=>$data['text'],		    
		);
		
		return array();
		
	}
	
	public function dump(){

	    
		var_dump($this->log_buffer);
		
	}	


} // End of class FOX_exceptionHandler

?>