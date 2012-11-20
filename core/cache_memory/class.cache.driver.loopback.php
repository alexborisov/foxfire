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



	public function __construct($args=null){
		
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
	 * Stores a value into the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $ns | Namespace of the cache variable
	 * @param string $var | Name of the cache variable
	 * @param mixed $val | Value to assign
	 * @return bool | False on failure. True on success.
	 */

	public function set($ns, $var, $val){
		
	    
		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
	 * @return bool | False on failure. True on success.
	 */

	public function setMulti($ns, $data){	    
		
	    
		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
	 * @return mixed | False on failure. Stored data item on success.
	 */

	public function get($ns, $var, &$valid=null){

	    
		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		
		}
		
		$valid = false;
		
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
	 * @return mixed | False on failure. Stored data item on success.
	 */

	public function getMulti($ns, $names){

	    
		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		
		}
		
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
	 * @return bool | False on failure. True on success.
	 */

	public function del($ns, $var){

	    
		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
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
	 * @return bool | False on failure. Int number of keys deleted on success.
	 */

	public function delMulti($ns, $data){

	    
		if( empty($ns) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty namespace value",
				'data'=>array('ns'=>$ns),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		
		}
		
		return count($data);
		
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
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		
		}
		
		return true;
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

	

} // End of class FOX_mCache_driver_loopback


?>