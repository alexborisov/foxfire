<?php

/**
 * FOXFIRE BASE DATABASE CLASS
 * Provides installation and caching functions for FoxFire's database classes
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Database
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfirewiki/DOCS_FOX_db_top
 *
 * ========================================================================================================
 */

abstract class FOX_db_base {


	/**
	 * Adds a database table
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool | Exception on failure. True on success.
	 */

	public function install(){
	    
		$db = new FOX_db();
		
		try {
			$db->runAddTable($this->_struct());
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Failed to create table",
				'data'=>array('struct'=>$this->_struct() ),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		return true;
		
	}


	/**
	 * Drops a database table
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool | Exception on failure. True on success.
	 */

	public function uninstall(){

		$db = new FOX_db();
		
		try {
			$db->runDropTable($this->_struct());
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Failed to remove table",
				'data'=>array('struct'=>$this->_struct() ),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		return true;
		
	}


	/**
	 * Deletes all data from a database table
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool | Exception on failure. True on success.
	 */

	public function truncate(){

		$db = new FOX_db();
		
		try {
			$db->runTruncateTable($this->_struct());
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Failed to truncate table",
				'data'=>array('struct'=>$this->_struct() ),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		return true;

	}


	/**
	 * Clears the persistent cache for the class namespace and broadcasts the 
	 * update to any classes that are listening for it
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool | Exception on failure. True on success.
	 */

	public function flushCache() {

	
		$struct = $this->_struct();

		try {
			$this->mCache->flushCache( array( 
				'engine'=>$struct["cache_engine"], 
				'namespace'=>$struct["cache_namespace"]
			));
			
			$this->cache = array();			
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in cache singleton",
				'data'=>array('struct'=>$struct),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		do_action( 'fox_flushCache_' . $struct["cache_namespace"] );
		
		return true;

	}

	
	/**
	 * Clears a paged cache page within the class namespace and broadcasts the 
	 * update to any classes that are listening for it
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool | Exception on failure. True on success.
	 */

	public function flushCachePage($pages) {


		$struct = $this->_struct();	
		
		if($struct['cache_strategy'] != 'paged'){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"This method can only be used on classes that use a paged cache",
				'data'=>array('struct'=>$struct),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		}		
		
		try {
			$this->mCache->flushCachePage( array( 
				'engine'=>$struct["cache_engine"], 
				'namespace'=>$struct["cache_namespace"],
				'pages'=>$pages
			));			
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in cache singleton",
				'data'=>$pages,			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		foreach( $pages as $page ){
		    
			unset($this->cache[$page]);
		}
		unset($page);

		
		do_action( 'fox_flushCachePage_' . $struct["cache_namespace"], $pages );
		
		return true;

	}
	

        /**
         * Loads a monolithic class cache array from the persistent cache and broadcasts the 
         * update to any classes that are listening for it
         *
         * @version 1.0
         * @since 1.0
         * @return bool | Exception on failure. True on success.
         */

        public function loadCache(){

	    
                $struct = $this->_struct();
		
		if($struct['cache_strategy'] == 'paged'){

			// We can't "load" a paged cache, because we have no way
			// of knowing what the individual cache keys are
		    
			$this->cache = array();    		    
		
		}
		elseif( $struct['cache_strategy'] == 'monolithic'){
					    
			$valid = false;
			
			try {
				$cache_image = self::readCache($valid);
			}
			catch (FOX_exception $child) {

			    $child->dump();
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error calling self::readCache()",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
			
			// If there's a valid entry for this class' cache in the the persistent
			// cache, use it, otherwise use an empty array
			
			if($valid){			    
				    $this->cache = $cache_image;
			}
			else {
				    $this->cache = array();
			}
			
		}
		
		else {		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Unrecognized cache strategy",
				'data'=>$struct['cache_strategy'],
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		}
		
                do_action( 'fox_loadCache_' . $struct["cache_namespace"], $this->cache );

                return true;

        }
	
	
	/**
	 * Loads pages in a 'paged' class cache array from the persistent cache and broadcasts the 
	 * update to any classes that are listening for it
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string/array $pages | Single page as string. Multiple pages as array of string.
	 * @return bool | Exception on failure. True on success.
	 */

	public function loadCachePage($pages){	    
	    
	    
		$struct = $this->_struct();
		
		if($struct['cache_strategy'] != 'paged'){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"This method can only be used on classes that use a paged cache",
				'data'=>array('struct'=>$struct),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		}		
		
		try {
			$cache_result = self::readCachePage($pages);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error calling self::readCachePage()",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		
														
		foreach($cache_result as $name => $data){
		    
			$this->cache[$name] = $data;		    
		}
		unset($name, $data);

		
		do_action( 'fox_loadCachePage_' . $struct["cache_namespace"], $this->cache );

		return true;

	}	
	
	
        /**
         * Loads a monolithic class cache array from the persistent cache and broadcasts the 
         * update to any classes that are listening for it
         *
         * @version 1.0
         * @since 1.0
         * @return bool | Exception on failure. Monolithic cache image on success.
         */

        public function readCache(&$valid=null){
	    
		
                $struct = $this->_struct();
		
		if($struct['cache_strategy'] != 'monolithic'){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"This method can only be used on classes that use a monolithic cache",
				'data'=>array('struct'=>$struct),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		}		

		try {
			$cache_image = $this->mCache->readCache( 
								array( 
									'engine'=>$struct["cache_engine"], 
									'namespace'=>$struct["cache_namespace"]
								),
								$valid
			);		    
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in cache singleton",
				'data'=>array('struct'=>$struct),                                   
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

                do_action( 'fox_readCache_' . $struct["cache_namespace"], $cache_image, $valid);
		
                return $cache_image;

        }
	
	
	/**
	 * Loads pages in a 'paged' class cache array from the persistent cache and broadcasts the 
	 * update to any classes that are listening for it
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string/array $pages | Single page as string. Multiple pages as array of string.
	 * @return array | Exception on failure. Array of paged cache page images on success.
	 */

	public function readCachePage($pages){	    
	    	    
		
		$struct = $this->_struct();
		
		if($struct['cache_strategy'] != 'paged'){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"This method can only be used on classes that use a paged cache",
				'data'=>array('struct'=>$struct),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		}		
																								    			    
		try {
			$result = $this->mCache->readCachePage( array( 
				'engine'=>$struct["cache_engine"], 
				'namespace'=>$struct["cache_namespace"],
				'pages'=>$pages
			));
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in cache singleton",
				'data'=>array('struct'=>$struct, 'pages'=>$pages),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		do_action( 'fox_readCachePage_' . $struct["cache_namespace"], $result);

		return $result;

	}		


	/**
	 * Saves a monolithic class cache array to the persistent cache, and broadcasts the 
	 * update to any classes that are listening for it
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool | Exception on failure. True on success.
	 */

	public function saveCache(){
	    
	    
		$struct = $this->_struct();	
		
		if($struct['cache_strategy'] != 'monolithic'){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"This method can only be used on classes that use a monolithic cache",
				'data'=>array('struct'=>$struct),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		}		

		try {
			self::writeCache($this->cache);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"error calling self::writeCache()",
				'data'=>array("struct"=>$struct, "cache"=>$this->cache),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		do_action( 'fox_saveCache_' . $struct["cache_namespace"], $this->cache );
		
		return true;
		
	}
	
	
	/**
	 * Saves pages in a paged class cache array to the persistent cache, and broadcasts the 
	 * update to any classes that are listening for it
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string/array $pages | Single page as string. Multiple pages as array of strings.
	 * @return bool | Exception on failure. True on success.
	 */

	public function saveCachePage($pages){


		$struct = $this->_struct();
		
		if($struct['cache_strategy'] != 'paged'){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"This method can only be used on classes that use a paged cache",
				'data'=>array('struct'=>$struct),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		}		
		
		if(!is_array($pages)){
			$pages = array($pages); 
		}
		
		$processed_pages = array();
		
		foreach( $pages as $page_name ){
		    
			if(FOX_sUtil::keyExists($page_name, $this->cache) ){
			    
				$processed_pages[$page_name] = $this->cache[$page_name];
			}
			else {			    
				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Called with key name that doesn't exist in the class cache",
					'data'=>array("faulting_page"=>$page_name, "cache"=>$this->cache),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    			    
			}
		}

		try {
			self::writeCachePage($processed_pages);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in self::writeCachePage()",
				'data'=>$processed_pages,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		do_action( 'fox_saveCachePage_' . $struct["cache_namespace"], $processed_pages );
		
		return true;
		
	}
	
	
	/**
	 * Writes a monolithic class cache array to the persistent cache, and broadcasts the 
	 * update to any classes that are listening for it
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool | Exception on failure. True on success.
	 */

	public function writeCache($image){

		
		$struct = $this->_struct();
		
		if($struct['cache_strategy'] != 'monolithic'){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"This method can only be used on classes that use a monolithic cache",
				'data'=>array('struct'=>$struct),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		}		

		try {
			$this->mCache->writeCache( array( 
				'engine'=>$struct["cache_engine"], 
				'namespace'=>$struct["cache_namespace"],
				'image'=>$image
			));		    
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in cache singleton",
				'data'=>array("struct"=>$struct, "image"=>$image),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		do_action( 'fox_writeCache_' . $struct["cache_namespace"], $image);
		
		return true;
		
	}
	
	
	/**
	 * Writes paged class cache array pages to the persistent cache, and broadcasts the 
	 * update to any classes that are listening for it
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param array $pages | pages as 'page_key' => 'page_image'
	 * @return bool | Exception on failure. True on success.
	 */

	public function writeCachePage($pages){

	    
		$struct = $this->_struct();
		
		if($struct['cache_strategy'] != 'paged'){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"This method can only be used on classes that use a paged cache",
				'data'=>array('struct'=>$struct),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		}		

		try {
			$this->mCache->writeCachePage( array( 
				'engine'=>$struct["cache_engine"], 
				'namespace'=>$struct["cache_namespace"],
				'pages'=>$pages
			));		    
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Cache set error",
				'data'=>$pages,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		do_action( 'fox_writeCachePage_' . $struct["cache_namespace"], $pages );
		
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
	 * @param array $ctrl | Control parameters 
	 *	=> VAL @param float $seconds |  Time in seconds from present time until lock expires	  
	 *	=> VAL @param string $mode | 'fetch' -  Returns an array of requested cache keys
	 *				     'update' - Overwrites class cache array with requested keys
	 * 	  
	 * @return bool | Exception on failure. True on success.
	 */

	public function lockCache($ctrl=null){
	    	    
	
		$struct = $this->_struct();
		
		if($struct['cache_strategy'] != 'monolithic'){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"This method can only be used on classes that use a monolithic cache",
				'data'=>array('struct'=>$struct),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		}		
		
		$ctrl_default = array(
			'seconds'=>5.0,
                        'mode'=>'fetch'		    
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);	
		
		$ctrl['process_id'] = $this->process_id;		
		$ctrl['engine'] = $struct["cache_engine"];
		$ctrl['namespace'] = $struct["cache_namespace"];
		
		try {
			$cache_image = $this->mCache->lockCache($ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in cache singleton",
				'data'=>array('struct'=>$struct),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		
		if($ctrl['mode'] == 'update'){
		    		
			$this->cache = $cache_image;			
			do_action( 'fox_lockCache_' . $struct["cache_namespace"], $cache_image);

			return true;						
		}
		elseif($ctrl['mode'] == 'fetch'){
		    
			do_action( 'fox_lockCache_' . $struct["cache_namespace"], $cache_image);
			
			return $cache_image;					    
		}
		else {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Invalid ctrl 'mode' parameter",
				'data'=>$ctrl,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    		    
		}	
		
	}	
	
	
	/**
	 * Loads the requested pages from the persistent cache, then locks the requested cache 
	 * pages until the timeout expires or the PID releases the lock by overwriting the pages. 
	 * Read requests in the namespace will throw an exception until the lock expires. Write
	 * and delete requests will remove the lock and clear/update the namespace.
	 *
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @param string/array $keys | Single key as string. Multiple keys as array of strings.
	 * 
	 * @param array $ctrl | Control parameters 
	 *	=> VAL @param float $seconds |  Time in seconds from present time until lock expires	  
	 *	=> VAL @param string $mode | 'fetch' -  Returns an array of requested cache keys
	 *				     'update' - Overwrites class cache array with requested keys
	 * 
	 * @return mixed | Exception on failure. Mixed on success.
	 */

	public function lockCachePage($keys, $ctrl=null){
	    	    

		$struct = $this->_struct();
		
		if($struct['cache_strategy'] != 'paged'){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"This method can only be used on classes that use a paged cache",
				'data'=>array('struct'=>$struct),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		}
		
		$ctrl_default = array(
			'seconds'=>5.0,
                        'mode'=>'fetch'		    
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);			
		
		try {
			$cache_image = $this->mCache->lockCachePage( array( 
				'process_id'=>$this->process_id,		    
				'engine'=>$struct["cache_engine"], 
				'namespace'=>$struct["cache_namespace"],
				'pages'=>$keys,
				'seconds'=>$ctrl['seconds']
			));
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Cache get error",
				'data'=>array('struct'=>$struct),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		if($ctrl['mode'] == 'update'){
		    
			foreach($cache_image as $name => $data){

				$this->cache[$name] = $data;
			}
			unset($name, $data);
			
			do_action( 'fox_lockCachePage_' . $struct["cache_namespace"], $cache_image);

			return true;			
			
		}
		elseif($ctrl['mode'] == 'fetch'){
		    
			do_action( 'fox_lockCachePage_' . $struct["cache_namespace"], $cache_image);
			
			return $cache_image;					    
		}
		else {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Invalid ctrl 'mode' parameter",
				'data'=>$ctrl,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		    
		}										
		
		return $cache_image;								
		
	}		


	/**
	 * Locks the class' entire namespace, giving the locking PID exclusive control of it.
	 *
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @param array $ctrl | Control parameters 
	 *	=> VAL @param int $seconds |  Time in seconds from present time until lock expires	  
	 * 
	 * @return mixed | Exception on failure. True on success.
	 */

	public function lockNamespace($ctrl=null){
	    	    

		$struct = $this->_struct();
		
		if($struct['cache_strategy'] != 'paged'){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"This method can only be used on classes that use a paged cache",
				'data'=>array('struct'=>$struct),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		}		
		
		$ctrl_default = array(
			'seconds'=>5,	    
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);			
		
		try {
			$result = $this->mCache->lockNamespace( array( 
				'process_id'=>$this->process_id,		    
				'engine'=>$struct["cache_engine"], 
				'namespace'=>$struct["cache_namespace"],
				'seconds'=>$ctrl['seconds']
			));
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in mCache->lockNamespace()",
				'data'=>array('struct'=>$struct),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
									
		return $result;
		
	}
	
	
	/**
	 * Releases an exclusive PID lock on the class' namespace. If released by a different
	 * PID than the one that set the lock, the class namespace will be flushed to maintain
	 * data integrity.
	 *
	 * @version 1.0
	 * @since 1.0  
	 * 
	 * @return int | Exception on failure. Int current offset on success.
	 */

	public function unlockNamespace(){
	    	    

		$struct = $this->_struct();
		
		if($struct['cache_strategy'] != 'paged'){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"This method can only be used on classes that use a paged cache",
				'data'=>array('struct'=>$struct),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    		    
		}				
		
		try {
			$result = $this->mCache->unlockNamespace( array( 
				'process_id'=>$this->process_id,		    
				'engine'=>$struct["cache_engine"], 
				'namespace'=>$struct["cache_namespace"]
			));
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in mCache->unlockNamespace()",
				'data'=>array('struct'=>$struct),				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
									
		return $result;
		
	}
	
	

} // End of abstract class FOX_db_base

?>