<?php

/**
 * FOXFIRE MEMORY CACHE - DRIVER BASE CLASS
 * Implements common driver methods
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Memory Cache
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

abstract class FOX_mCache_driver_base {
	
    
	/**
	 * Clears the persistent cache for the class namespace and broadcasts the 
	 * update to any classes that are listening for it
	 *
	 * @version 1.0
	 * @since 1.0
	 * 
         * @param array $args | Control args
	 *	=> VAL @param string $namespace | Class namespace
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function flushCache($args) {

		
		try {
			$this->flushNamespace($args["namespace"]);		
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in descendent->flushNamespace()",
				'data'=>$args,			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		return true;

	}

	
	/**
	 * Clears a paged cache page within the class namespace and broadcasts the 
	 * update to any classes that are listening for it
	 *
	 * @version 1.0
	 * @since 1.0
	 * 
         * @param array $args | Control args
	 *	=> VAL @param string $namespace | Class namespace
	 *	=> VAL @param string/array $pages | Single page as string. Multiple pages as array of string.
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function flushCachePage($args) {

		
		try {
			$this->delMulti($args["namespace"], $args['pages']);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in descendent->delMulti()",
				'data'=>$args,			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		return true;

	}
	
		
        /**
         * Loads a monolithic class cache array from the persistent cache and broadcasts the 
         * update to any classes that are listening for it
         *
         * @version 1.0
         * @since 1.0
	 * 
         * @param array $args | Control args
	 *	=> VAL @param string $namespace | Class namespace
	 * 	 
         * @return array | Exception on failure. Monolithic cache image on success.
         */

        public function readCache($args, &$valid=null){	    

		$valid = false;
	    
		try {
			$cache_image = $this->get($args["namespace"], "cache", $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in descendent->get()",
				'data'=>$args,                                   
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		

		// CASE 1: The namespace has no cache entry
		// =============================================================                
		if(!$valid){

			$result = null;

		}
		// CASE 2: The namespace has a cache entry, but its not locked
		// =============================================================                
		elseif( !FOX_sUtil::keyExists("lock", $cache_image) ) {

			$result = $cache_image;             

		}
		// CASE 3: The namespace has a cache entry, and its locked
		// =============================================================
		else {

			$expiry_time = $cache_image['lock']['expire'];
			$current_time = microtime(true);

			if( $current_time > $expiry_time ){

				// If the lock has expired, the cache contents are no longer
				// valid. So return an empty page image. 

				$result = null;                                       
			}
			else {
				// If the lock is still active, throw an exception and let
				// the calling function decide what it wants to do

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Namespace is currently locked",
					'data'=>$cache_image['lock'],
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}

		}
		
                return $result;

        }
	
	
	/**
	 * Loads pages in a 'paged' class cache array from the persistent cache and broadcasts the 
	 * update to any classes that are listening for it
	 *
	 * @version 1.0
	 * @since 1.0
	 * 
         * @param array $args | Control args
	 *	=> VAL @param string $namespace | Class namespace
	 *	=> VAL @param string/array $pages | Single page as string. Multiple pages as array of string.
	 * 	 
	 * @return array | Exception on failure. Array of paged cache page images on success.
	 */

	public function readCachePage($args){	    	    	  	    
								 
				
	    	if( !is_array($args['pages']) ){
		    
			$args['pages'] = array($args['pages']);
		}
				
		// Fetch keys
		// =============================================================
		
		try {
			$cache_result = $this->getMulti($args["namespace"], $args['pages']);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in descendent->getMulti()",
				'data'=>$args,				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		// Process keys
		// =============================================================

		$processed_result = array();
		$locked_pages = array();

		foreach( $args['pages'] as $page_name ){

			// Page has no cache entry		
			if( !FOX_sUtil::keyExists($page_name, $cache_result) ){

				// Don't write the page to the array
							
				//$processed_result[$page_name] = array();

			}
			
			// Page has cache entry, but its not locked
			elseif( !FOX_sUtil::keyExists("lock", $cache_result[$page_name]) ) {

				$processed_result[$page_name] = $cache_result[$page_name];		    

			}
			
			// Page has a cache entry, and its locked
			else {

				$expiry_time = $cache_result[$page_name]['lock']['expire'];
				$current_time = microtime(true);

				if( $current_time > $expiry_time ){

					// Don't write the page to the array

					//$processed_result[$page_name] = array();					    
				}
				else {
					// Othewise, the lock is still valid, so flag the key
					$locked_pages[$page_name] = $cache_result[$page_name]['lock'];
				}
			}						

		}
		unset($page_name);
		
				
		// Build results array
		// =============================================================
		
		$result = array();

		if( count($locked_pages) == 0 ){

			foreach($processed_result as $name => $data){

				$result[$name] = $data;
			}
			unset($name, $data);								    
		}
		else {
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"One or more pages are currently locked",
				'data'=>$locked_pages,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			    			    
		}
				
		return $result;

	}		
	
	
	/**
	 * Writes a monolithic class cache array to the persistent cache, and broadcasts the 
	 * update to any classes that are listening for it
	 *
	 * @version 1.0
	 * @since 1.0
	 * 
         * @param array $args | Control args
	 *	=> VAL @param string $namespace | Class namespace
	 *	=> VAL @param string $image | Cache image 
	 * 	 
	 * @return bool | Exception on failure. True on success.
	 */

	public function writeCache($args){

		try {
			$this->set($args["namespace"], "cache", $args['image']);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in descendent->set()",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		return true;
		
	}
	
	
	/**
	 * Writes paged class cache array pages to the persistent cache, and broadcasts the 
	 * update to any classes that are listening for it
	 *
	 * @version 1.0
	 * @since 1.0
	 * 
         * @param array $args | Control args
	 *	=> VAL @param string $namespace | Class namespace
	 *	=> VAL @param string/array $pages | Single page as string. Multiple pages as array of string.
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function writeCachePage($args){

		
		// Write keys to cache engine
		// =============================================================
	    
		if( !is_array($args['pages']) ){
		    
			$args['pages'] = array($args['pages']);
		}	    
		
		try {
			$this->setMulti($args["namespace"], $args['pages']);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in descendent->setMulti()",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		return true;
		
	}		
	
	
	/**
	 * Loads a monolithic class cache array from the persistent cache and locks the class 
	 * namespace until the timeout expires or the PID releases the lock by writing to the 
	 * cache. Read requests in the namespace will throw an exception until the lock expires. 
	 * Write and delete requests will remove the lock and clear/update the namespace.
	 *
	 * @version 1.0
	 * @since 1.0
	 * 
         * @param array $args | Control args
	 *	=> VAL @param string $namespace | Class namespace
	 *	=> VAL @param int $process_id | Process ID to use as owner 
	 *	=> VAL @param int $seconds |  Time in seconds from present time until lock expires	  
	 * 	  
	 * @return array | Exception on failure. Cache image on success.
	 */

	public function lockCache($args){
	    	    
		
		// Try to fetch the current entry from the persistent cache
		// =============================================================
			
		// NOTE: in order to guarantee that the local class cache array isn't 
		// overwritten until all operations in the locking process are successful, 
		// and because loadCache() strips the lock array from cache entries as 
		// it loads them, we have to manually perform all the operations
		
		$valid = false;
		
		try {
			$cache_image = $this->get($args["namespace"], "cache", $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in descendent->get()",
				'data'=>$args,				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		$lock_array = array( 'pid'=>$args['process_id'], 'expire'=> ( microtime(true) + $args['seconds'] ) );
		
		
		// CASE 1: The namespace has no cache entry
		// =============================================================		
		if(!$valid){
		    
			$class_cache = array();		    
			$cache_image = array('lock'=>$lock_array);
			
			// Write an empty cache array containing the lock array back to the persistent cache

			try {
				$this->set($args["namespace"], "cache", $cache_image);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Error in descendent->set()",
					'data'=>array("args"=>$args, "cache_image"=>$cache_image),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}					
			
		}
		// CASE 2: The namespace has a cache entry, but its not locked
		// =============================================================		
		elseif( !FOX_sUtil::keyExists("lock", $cache_image) ) {
		    
			$class_cache = $cache_image;		    
			$cache_image['lock'] = $lock_array;
		    
			try {
				$this->set($args["namespace"], "cache", $cache_image);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error in descendent->set()",
					'data'=>array("args"=>$args, "cache_image"=>$cache_image),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}				    
			
		}
		// CASE 3: The namespace has a cache entry, and its locked
		// =============================================================
		else {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Namespace is currently locked",
				'data'=>$cache_image['lock'],
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		}
		
		
		return $class_cache;
		
	}	
	
	
	/**
	 * Loads a monolithic class cache array from the persistent cache and locks the class 
	 * namespace until the timeout expires or the PID releases the lock by writing to the 
	 * cache. Read requests in the namespace will throw an exception until the lock expires. 
	 * Write and delete requests will remove the lock and clear/update the namespace.
	 *
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @param string/array $keys | Single key as string. Multiple keys as array of strings.
	 * 
         * @param array $args | Control args
	 *	=> VAL @param string $namespace | Class namespace
	 *	=> VAL @param int $process_id | Process ID to use as owner 
	 *	=> VAL @param int $seconds |  Time in seconds from present time until lock expires	  
	 *	=> VAL @param string/array $pages | Single page as string. Multiple pages as array of string.	 
	 * 
	 * @return mixed | Exception on failure. Mixed on success.
	 */

	public function lockCachePage($args){
	    
	
	    	if( !is_array($args['pages']) ){
		    
			$args['pages'] = array($args['pages']);
		}	    
			    
		try {
			$cache_result = $this->getMulti($args["namespace"], $args['pages']);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in descendent->getMulti()",
				'data'=>$args,				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		$processed_result = array();
		$locked_pages = array();

		foreach( $args['pages'] as $page ){

			// Page has no cache entry
			// =============================================================		
			if( !FOX_sUtil::keyExists($page, $cache_result) ){

				// Write an empty array to the result			
				$processed_result[$page] = array();

			}
			// Page has cache entry, but its not locked
			// =============================================================		
			elseif( !FOX_sUtil::keyExists("lock", $cache_result[$page]) ) {

				$processed_result[$page] = $cache_result[$page];		    

			}
			// Page has a cache entry, and its locked
			// =============================================================
			else {

				$expiry_time = $cache_result[$page]['lock']['expire'];
				$current_time = microtime(true);

				if( $current_time > $expiry_time ){

					// If the lock has expired, the cache contents are no longer
					// valid. Return an empty cache array	

					$processed_result[$page] = array();					    
				}
				else {
					// Othewise, the lock is still valid, so flag the key
					$locked_pages[$page] = $cache_result[$page]['lock'];
				}
			}						

		}
		unset($page);
  
		// If any of the pages were already locked, throw an exception

		if( count($locked_pages) != 0 ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"One or more requested pages are currently locked",
				'data'=>$locked_pages,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			    			    
		}
				
		
		// Build a cache image with the lock array added to each cache
		// page, and write it to the cache
		// =============================================================
		
		$cache_image = array();		
		$lock_array = array( 'pid'=>$args['process_id'], 'expire'=> ( microtime(true) + $args['seconds'] ) );
		
		foreach( $processed_result as $page => $data ){
		    
			// FoxFire data classes always use an array as their storage variable and
			// store scalar values into it as keys. They never use a single scalar
			// variable as their storage object. So the line below is valid.
		    
			$data['lock'] = $lock_array;
			$cache_image[$page] = $data;		    
		}
		unset($page, $data);
		
		
		try {
			$this->setMulti($args["namespace"], $cache_image);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in descendent->setMulti()",
				'data'=>$cache_image,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		return $processed_result;
					
	}	



} // End of class FOX_mCache_driver_base


?>