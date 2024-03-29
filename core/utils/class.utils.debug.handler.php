<?php

/**
 * FOXFIRE DEBUG HANDLER
 * FoxFire's debug handler is a flexible class that allows highly detailed logging, on-the-fly variable
 * tampering, and the ability to simulate multi-threaded operations within a single thread. This class
 * is built for simplicity of debugging instead of speed, and is NOT INTENDED to be used during the normal 
 * operation of the FoxFire core. Use the FoxFire event system instead.
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

		$this->events = array();
		$this->log_buffer = array();
		
	}

	
	public function addEvent($data){

	    
		if(!FOX_sUtil::keyExists('type', $data) || (($data['type'] != 'log') && ($data['type'] != 'trap')) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid event 'type' paramater",
				'data'=>$data,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			    
		}
		
		$this->events[] = $data;
		
		return true;
		
	}
	
	public function dropEvent($data){

	   
	}	

	public function event($data){


		foreach( $this->events as $event ){
		
			// This is probably one of the few situations in all of modern computer science where
			// a massive "elseif" chain is the optimal solution. 
		    
			// Ordered by frequency of use, for best performance
		    
			if( FOX_sUtil::keyExists('pid', $event) && ($event['pid'] != $data['pid']) ){
			
			    continue;
			}
			elseif( FOX_sUtil::keyExists('text', $event) && ($event['text'] != $data['text']) ){

			    continue;
			}			
			elseif( FOX_sUtil::keyExists('class', $event) && ($event['class'] != $data['class']) ){

			    continue;
			}
			elseif( FOX_sUtil::keyExists('function', $event) && ($event['function'] != $data['function']) ){

			    continue;
			}
			elseif( FOX_sUtil::keyExists('file', $event) && ($event['file'] != $data['file']) ){

			    continue;
			}			
			elseif( FOX_sUtil::keyExists('line', $event) && ($event['line'] != $data['line']) ){

			    continue;
			}
			else {
					
				if($event['type'] == 'log'){
				
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
				else {
				    
					// Events are emitted by class instances. Pass the class instance that emitted the event, and all
					// currently defined variables within the *function inside the class instance* that emitted the
					// event, to the function sent in as the event handler; then take the results (if any) returned 
					// by the event handler function and use them to overwrite the variables within the function 
					// inside the class instance that emitted the event.
				    
					return $event['modifier']($data['parent'], $data['vars']);	
					
				}							    			    
			}
			
		}
		unset($event);
		
		// Handle not matching on any events
		return array();
		
	}
	
	public function dump(){

	    
		var_dump($this->log_buffer);
		
	}	


} // End of class FOX_exceptionHandler

?>