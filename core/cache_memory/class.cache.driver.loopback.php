<?php

/**
 * FOXFIRE MEMORY CACHE - LOOPBACK DRIVER
 * Implements a dummy cache engine that discards data for all key writes, reports nonexistent for all
 * key reads, and reports success for every action. This driver is used as a test tool to guarantee
 * data is never stored in the cache. It's also used as the last-resort engine for db classes that store
 * large amounts of data to the cache, precluding the use of the "thread" cache engine.
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Cache Loopback
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_mCache_driver_loopback extends FOX_mCache_driver_base {

	
	var $process_id;		    // Unique process id for this thread. Used for namespace-level locking.
		

	public function __construct($args=null){
	    
	    
		// Handle process-id binding
		// ===========================================================
		
		if(FOX_sUtil::keyExists('process_id', $args)){
		    
			// Binding to a reference is important. It makes the cache engine $process_id
			// update if the FOX_mCache is changed, which we do during unit testing.
		    
			$this->process_id = &$args['process_id'];			
		}
		else {	
			global $fox;
			$this->process_id = $fox->process_id;
		}	    
		
	}
	
	
	/**
	 * Checks if the cache engine driver is active
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return bool | True if active. False if not.
	 */

	public function isActive(){
		
		return true;
	}
	
	
	/**
	 * Returns the cache engine's performance statistics
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return array | Engine stats array
	 */

	public function getStats(){
		
		return array();
	}	
	
	
	/**
	 * Removes all entries in the cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function flushAll(){

		return true;	
	}	
	
	
	/**
	 * Removes all entries within the specified namespace from the cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @return bool | Exception on failure. True on success.
	 */

	public function flushNamespace($ns){

	    
		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		
		}
		
		return 1;
	}
	
	
	/**
	 * Locks an entire namespace within the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $namespace | Class namespace
	 * @param int $seconds |  Time in seconds from present time until lock expires	  
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function lockNamespace($ns, $seconds){


		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>$ns,			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		
		}

		return 1;

	}
	
	
	/**
	 * Unlocks a locked namespace within the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $namespace | Class namespace	  
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function unlockNamespace($ns){


		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>$ns,			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		
		}

		return 1;

	}
	
	
	/**
	 * Stores a value into the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param string $var | Name of the cache variable
	 * @param mixed $val | Value to assign
	 * @param int $check_offset | Offset to check against
	 * @param bool $clear_lock | True to clear a namespace lock, if the PID owns it	 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function set($ns, $var, $val, $check_offset=null, $clear_lock=false){
		
	    
		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		
		}
			
		return true;
	}


	/**
	 * Stores multiple values into the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param array $data | Data to set in the form "key"=>"val"
	 * @param int $check_offset | Offset to check against
	 * @param bool $clear_lock | True to clear a namespace lock, if the PID owns it	 
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function setMulti($ns, $data, $check_offset=null, $clear_lock=false){	    
		
	    
		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		
		}
		
		return true;
	}


	/**
	 * Retrieves a value from the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param string $var | Name of the cache variable
	 * @param bool &$valid | True if key exists in cache. False if not.
	 * 
	 * @return int &$offset | Current namespace offset
	 * @return mixed | Exception on failure. Stored data item on success.
	 */

	public function get($ns, $var, &$valid=null, &$offset=null){

	    
		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		
		}
		
		$valid = false;
		$offset = 1;
		
		return null;
	}


	/**
	 * Retrieves multiple values from the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param array $names | Array of cache variable names
	 * @param int &$offset | Current namespace offset
	 * 	 
	 * @return mixed | Exception on failure. Stored data item on success.
	 */

	public function getMulti($ns, $names, &$offset=null){

	    
		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		
		}
		
		$offset = 1;
		
		return array();
	}


	/**
	 * Deletes an item from the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param string $var | Name of key
	 * @param int $check_offset | Offset to check against	 
	 * 
	 * @return bool | Exception on failure. True on key exists. False on key doesn't exist.
	 */

	public function del($ns, $var, $check_offset=null){

	    
		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		
		}
		
		return true;
	}
	

	/**
	 * Deletes multiple items from the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param array $data | Key names as array of strings.
	 * @param int $check_offset | Offset to check against	 
	 * 
	 * @return int | Exception on failure. Int number of keys deleted on success.
	 */

	public function delMulti($ns, $data, $check_offset=null){

	    
		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));		    		
		}
		
		return count($data);
		
	}	
	

	
	
		

	

} // End of class FOX_mCache_driver_loopback


?>