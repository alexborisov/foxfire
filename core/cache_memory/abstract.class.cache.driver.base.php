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
	 * @return int | Exception on failure. Int current namespace offset on success.
	 */

	public function flushCache($args) {

		
		try {
			$offset = $this->flushNamespace($args["namespace"]);		
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in descendent->flushNamespace()",
				'data'=>$args,			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}
		
		return $offset;

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
	 *	=> VAL @param int $check_offset | Offset to check against
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function flushCachePage($args) {

		
		try {
			$keys_deleted = $this->delMulti($args["namespace"], $args['pages'], $args['check_offset']);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in descendent->delMulti()",
				'data'=>$args,			    
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>$child
			));
		}
		
		return $keys_deleted;

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
	 * @return bool &$valid | True if key exists in cache. False if not.
	 * @return int &$offset | Current namespace offset 	 
         * @return array | Exception on failure. Monolithic cache image on success.
         */

        public function readCache($args, &$valid=null, &$offset=null){	    

	    
		$valid = false;
	    
		try {
			$cache_image = $this->get($args["namespace"], "cache", $valid, $offset);
		}
		catch (FOX_exception $child) {

			if($child->data['numeric'] == 4){
			    
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Namespace is currently locked by another PID",
					'data'=>$child->data['data'],				    
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>null
				));
			}
			else {			    
				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Error in descendent::get()",
					'data'=>$args,				    
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));			    			    
			}
		}
		
		
		if(!$valid){

			$result = array();
		}              
		else {
			$result = $cache_image;             
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
	 * @return int &$offset | Current namespace offset
	 * @return array | Exception on failure. Array of paged cache page images on success.
	 */

	public function readCachePage($args, &$offset=null){	    	    	  	    
								 
				
	    	if( !is_array($args['pages']) ){
		    
			$args['pages'] = array($args['pages']);
		}
				
		// Fetch keys
		// =============================================================
		
		try {
			$cache_result = $this->getMulti($args["namespace"], $args['pages'], $offset);
		}
		catch (FOX_exception $child) {

			if($child->data['numeric'] == 4){
			    
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Namespace is currently locked by another PID",
					'data'=>$child->data['data'],				    
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>null
				));
			}
			else {			    
				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Error in descendent::getMulti()",
					'data'=>$args,				    
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));			    			    
			}
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
				'numeric'=>3,
				'text'=>"One or more pages are currently locked",
				'data'=>$locked_pages,
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
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
	 *	=> VAL @param int $check_offset | Offset to check against
	 * 	 
	 * @return bool | Exception on failure. True on success.
	 */

	public function writeCache($args){

	    
		try {
			$this->set($args["namespace"], "cache", $args['image'], $args['check_offset'], true);
		}
		catch (FOX_exception $child) {

			if($child->data['numeric'] == 4){
			    
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Namespace is currently locked by another PID",
					'data'=>$child->data['data'],				    
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>null
				));
			}
			elseif($child->data['numeric'] == 5) {	
			    
				throw new FOX_exception(array(
					'numeric'=>2,
					'text'=>"Current offset doesn't match expected offset",
					'data'=>$child->data['data'],
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>null
				));			    			    
			}			
			else {			    
				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error in descendent::set()",
					'data'=>$args,				    
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));			    			    
			}
			
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
	 *	=> VAL @param int $check_offset | Offset to check against
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
			$this->setMulti($args["namespace"], $args['pages'], $args['check_offset']);
		}
		catch (FOX_exception $child) {

			if($child->data['numeric'] == 4){
			    
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Namespace is currently locked by another PID",
					'data'=>$child->data['data'],				    
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>null
				));
			}
			elseif($child->data['numeric'] == 5) {	
			    
				throw new FOX_exception(array(
					'numeric'=>2,
					'text'=>"Current offset doesn't match expected offset",
					'data'=>$child->data['data'],
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>null
				));			    			    
			}			
			else {			    
				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error in descendent::set()",
					'data'=>$args,				    
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));			    			    
			}
						
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
	 * @return int &$offset | Current namespace offset	  
	 * @return array | Exception on failure. Cache image on success.
	 */

	public function lockCache($args, &$offset=null){
	    	    
		
		// Try to lock the cache namespace
		// =============================================================
	    
		try {
			$lock_offset = $this->lockNamespace($args["namespace"], $args['seconds']);
		}
		catch (FOX_exception $child) {

			if($child->data['numeric'] == 4){
			    
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Namespace is currently locked by another PID",
					'data'=>$child->data['data'],				    
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>null
				));
			}
			else {			    
				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Error in descendent::lockNamespace()",
					'data'=>$args,				    
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));			    			    
			}
		}
		
	    
		// Try to fetch the current entry from the persistent cache
		// =============================================================			
		
		$valid = false;
		$get_offset = false;
		
		try {
			$cache_image = $this->get($args["namespace"], "cache", $valid, $get_offset);
		}
		catch (FOX_exception $child) {

			if($child->data['numeric'] == 4){
			    
				// During the namespace locking sequence, the current PID will read the current offset
				// from the cache, then set the lock array and offset. If a second PID reads the offset
				// before the current PID changes it, and overwrites the lock array after the current PID
				// has written to it, a write collision will occur. In this situation, the second PID will 
				// have control of the namespace. 
			    
				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Write collision during namespace locking sequence. The other PID won.",
					'data'=>$child->data['data'],				    
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>null
				));
			}
			else {			    
				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Error in descendent::get()",
					'data'=>$args,				    
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));			    			    
			}
		}
		
		
		if($lock_offset == $get_offset){
		    
			$offset = $lock_offset;
		}
		else {
		    
			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Namespace was flushed by a different PID during read sequence",
				'data'=>array('new_offset'=>$get_offset),				    
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));		    		    
		}
	
		if(!$valid){
		    
			return array();				
		}		
		else {		    
			return $cache_image;		    
		}		
		
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
	 *	=> VAL @param int $seconds |  Time in seconds from present time until lock expires	  
	 *	=> VAL @param string/array $pages | Single page as string. Multiple pages as array of string.	 
	 * 
	 * @return int &$offset | Current namespace offset	 
	 * @return mixed | Exception on failure. Mixed on success.
	 */

	public function lockCachePage($args, &$offset=null){
	    

	    	if( !is_array($args['pages']) ){
		    
			$args['pages'] = array($args['pages']);
		}	    
			    
		try {
			$cache_result = $this->getMulti($args["namespace"], $args['pages'], $offset);
		}
		catch (FOX_exception $child) {

			if($child->data['numeric'] == 4){
			    
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Cache namespace is currently locked by another PID",
					'data'=>$child->data['data'],				    
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>null
				));
			}
			else {			    
				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Error in descendent::getMulti()",
					'data'=>$args,				    
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));			    			    
			}
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
				elseif($cache_result[$page]['lock']['pid'] == $this->process_id){
				    				    
					// If the lock is owned by the current PID, just write back the lock array to the cache
					// with an updated timestamp, refreshing the lock. This provides important functionality,
					// letting a PID that has a lock on the page extend its lock time incrementally as it
					// works through a complex processing job. If the PID had to release and reset the lock
					// each time, the data would be venerable to being overwritten by other PID's.		
				    
					unset($cache_result[$page]['lock']);
					$processed_result[$page] = $cache_result[$page];					
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
				'numeric'=>3,
				'text'=>"One or more requested pages are currently locked",
				'data'=>$locked_pages,
				'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
				'child'=>null
			));			    			    
		}
				
		
		// Build a cache image with the lock array added to each cache
		// page, and write it to the cache
		// =============================================================
		
		$cache_image = array();		
		$lock_array = array( 'pid'=>$this->process_id, 'expire'=> ( microtime(true) + $args['seconds'] ) );
		
		foreach( $processed_result as $page => $data ){
		    
			// FoxFire data classes always use an array as their storage variable and
			// store scalar values into it as keys. They never use a single scalar
			// variable as their storage object. So the line below is valid.
		    
			$data['lock'] = $lock_array;
			$cache_image[$page] = $data;		    
		}
		unset($page, $data);
		
		
		try {
			$this->setMulti($args["namespace"], $cache_image, $offset);
		}
		catch (FOX_exception $child) {

		    
			if($child->data['numeric'] == 5){
			    
				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Namespace was flushed by another PID during page locking sequence.",
					'data'=>$child->data['data'],				    
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>null
				));
			}
			elseif($child->data['numeric'] == 4){	
			    
				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Namespace was locked by another PID during page locking sequence.",
					'data'=>$child->data['data'],				    
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>null
				));			    			    
			}
			else {
			
				throw new FOX_exception( array(
					'numeric'=>6,
					'text'=>"Error in descendent::setMulti()",
					'data'=>$cache_image,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				));			
			}
			
		}
		
		return $processed_result;
					
	}	



} // End of class FOX_mCache_driver_base


?>