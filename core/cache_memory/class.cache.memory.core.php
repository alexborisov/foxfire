<?php

/**
 * FOXFIRE MEMORY CACHE
 * Implements an advanced key-value store cache
 * 
 * FEATURES
 * --------------------------------------
 *  -> Thread level caching
 *  -> Server level caching
 *  -> Data Center level caching
 *  -> Namespacing
 *  -> Page locking
 *  -> Transactions
 *  -> Supports APC cache engine
 *  -> Supports Memcached cache engine
 *  -> Supports Redis cache engine
 *  -> Cascading cache engine fall-back
 *  -> Engine performance reporting
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

class FOX_mCache {
	
    
    	var $process_id;		    // Unique process id for this thread. Used by the cache engine instances
					    // for namespace-level cache locking. 
	
	var $engines;			    // Cache engine singletons array	


	// ================================================================================================================


	public function __construct($args=null){

	    
		// Handle dependency-injection for unit tests
	    
		if(FOX_sUtil::keyExists('process_id', $args)){
		    
			$this->process_id = $args['process_id'];
		}
		else {	
			global $fox;
			$this->process_id = $fox->process_id;
		}
		
		if(FOX_sUtil::keyExists('engines', $args)){
		    
			$this->engines = &$args['engines'];
		}
		else {	
			$this->engines = array(
				'apc'		=>  new FOX_mCache_driver_apc( array('process_id'=>$this->process_id) ),
				'memcached'	=>  new FOX_mCache_driver_memcached( array('process_id'=>$this->process_id) ),
				'redis'		=>  new FOX_mCache_driver_redis( array('process_id'=>$this->process_id) ),
				'thread'	=>  new FOX_mCache_driver_thread( array('process_id'=>$this->process_id) ),  			    
				'loopback'	=>  new FOX_mCache_driver_loopback( array('process_id'=>$this->process_id) )    			    				
			);
		}		
		
	}


	/**
	 * Sets which caching engines are active
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param bool/array $ctrl | True to set all active. False to set none active. Array of engine names to set specific engines.
	 * @return bool | Exception on failure. True on success.
	 */

	public function setActiveEngines($ctrl){


		// CASE 1: (bool)false, (int)0 or empty array to disable all caching engines
		// =================================================================================
		if( $ctrl == false ){

			$this->engines['apc']->disable();
			$this->engines['memcached']->disable();
			$this->engines['redis']->disable();
			$this->engines['thread']->disable();			
			
		}
		// CASE 2: Array of engine names, to enable only selected engines
		// =================================================================================
		elseif( is_array($ctrl) ){

		    
			if( array_search( 'apc', $ctrl ) !== false ){

				$this->engines['apc']->enable();
			}
			else {
				$this->engines['apc']->disable();
			}

			if( array_search( 'memcached', $ctrl ) !== false ){
			    
				$this->engines['memcached']->enable();
			}
			else {
				$this->engines['memcached']->disable();
			}			
			
			if( array_search( 'redis', $ctrl ) !== false ){
			    
				$this->engines['redis']->enable();
			}
			else {
				$this->engines['redis']->disable();
			}			
			
			if( array_search( 'thread', $ctrl ) !== false ){
			    
				$this->engines['thread']->enable();
			}
			else {
				$this->engines['thread']->disable();
			}	
			

		}
		// CASE 3: Enable all caching engines
		// =================================================================================		
		elseif( $ctrl == true ){

			$this->engines['apc']->enable();
			$this->engines['memcached']->enable();
			$this->engines['redis']->enable();
			$this->engines['thread']->enable();			

		}		
		// CASE 4: Bad input
		// =================================================================================
		else {
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Missing or invalid input",
				'data'=>$ctrl,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}		
		
		return true;

	}
	
	
	/**
	 * Returns performace data for the requested engine
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $name | Name of engine as string
	 * @return array | Exception on failure. Engine data array on success.
	 */

	public function getStats($engine_name){

		
		if( FOX_sUtil::keyExists($engine_name, $this->engines) ){  // Check engine exists

			if( $this->engines[$engine_name]->isActive() ){   // Check engine is enabled

				try {
					$result = $this->engines[$engine_name]->getStats();
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>1,
						'text'=>"Error in cache engine driver",						
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}		    
				
				return $result;
			}
		}
		else {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Specified engine name doesn't exist",
				'data'=>$engine_name,			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			    			    
		}			

	}	


	/**
	 * Clears the persistent cache for the class namespace and broadcasts the 
	 * update to any classes that are listening for it
	 *
	 * @version 1.0
	 * @since 1.0
	 * 
         * @param array $args | Control args
	 *	=> VAL @param array $engine | Class cache engines array
	 *	=> VAL @param string $namespace | Class namespace
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function flushCache($args) {


		// Heavily error-check. Bad parameters passed to this method could cause 
		// difficult-to-debug intermittent errors throughout the plugin
		// =====================================================================

		if( !FOX_sUtil::keyExists('engine', $args) || empty($args['engine']) ) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid engine parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !FOX_sUtil::keyExists('namespace', $args) || empty($args['namespace']) ) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Invalid namespace parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}		
				
			    
		// Find the first active cache engine specified by the class
		// =====================================================================		
	    
		$engine = false;
		
		foreach( $args['engine'] as $engine_name ){
		    
			// Check engine exists
		    
			if( FOX_sUtil::keyExists($engine_name, $this->engines)  ){

				// Check engine is active
			    
				if( $this->engines[$engine_name]->isActive() ){

					$engine =& $this->engines[$engine_name];
					break;
				}
			}
			else {
			    
				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Specified engine name doesn't exist",
					'data'=>$engine_name,			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));			    			    
			}			
		}
		unset($engine_name);
		
		if(!$engine){
		    
			// If none of the requested engines are active, 
			// use the loopback engine
		    
			$engine =& $this->engines['loopback'];
		}
		
		try {
			$engine->flushNamespace($args['namespace']);		
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Cache flush error",
				'data'=>array('engine'=>$engine, 'args'=>$args),			    
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
	 *	=> VAL @param array $engine | Class cache engines array
	 *	=> VAL @param string $namespace | Class namespace
	 *	=> VAL @param string/array $pages | Single page as string. Multiple pages as array of string.
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function flushCachePage($args) {

	    
		// Heavily error-check. Bad parameters passed to this method could cause 
		// difficult-to-debug intermittent errors throughout the plugin
		// =====================================================================

		if( !FOX_sUtil::keyExists('engine', $args) || empty($args['engine']) ) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid engine parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !FOX_sUtil::keyExists('namespace', $args) || empty($args['namespace']) ) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Invalid namespace parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !FOX_sUtil::keyExists('pages', $args) || empty($args['pages']) ) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Invalid pages parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}		
			
		
		// Find the first active cache engine specified by the class		
		// =====================================================================
		
		if(!is_array($args['pages'])){
		    
			$args['pages'] = array($args['pages']);
		}
	    
		$engine = false;
		
		foreach( $args['engine'] as $engine_name ){
		    
			// Check engine exists
		    
			if( FOX_sUtil::keyExists($engine_name, $this->engines) ){

				// Check engine is active
			    
				if( $this->engines[$engine_name]->isActive() ){

					$engine =& $this->engines[$engine_name];
					break;
				}
			}
			else {
			    
				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Specified engine name doesn't exist",
					'data'=>$engine_name,			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));			    			    
			}			
		}
		unset($engine_name);
		
		if(!$engine){
		    
			// If none of the requested engines are active, 
			// use the loopback engine
		    
			$engine =& $this->engines['loopback'];
		}
		
		
		try {
			$engine->flushCachePage($args);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Error in engine->flushCachePage()",
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
	 *	=> VAL @param array $engine | Class cache engines array
	 *	=> VAL @param string $namespace | Class namespace
	 * 	 
         * @return array | Exception on failure. Monolithic cache image on success.
         */

        public function readCache($args){
	    

		// Heavily error-check. Bad parameters passed to this method could cause 
		// difficult-to-debug intermittent errors throughout the plugin
		// =====================================================================

		if( !FOX_sUtil::keyExists('engine', $args) || empty($args['engine']) ) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid engine parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !FOX_sUtil::keyExists('namespace', $args) || empty($args['namespace']) ) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Invalid namespace parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}		
					    
	    
		// Find the first active cache engine specified by the class
		// =====================================================================
		
		$engine = false;
		
		foreach( $args['engine'] as $engine_name ){
		    
			// Check engine exists
		    
			if( FOX_sUtil::keyExists($engine_name, $this->engines) ){

				// Check engine is active
			    
				if( $this->engines[$engine_name]->isActive() ){

					$engine =& $this->engines[$engine_name];
					break;
				}
			}
			else {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Specified engine name doesn't exist",
					'data'=>$engine_name,			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));			    			    
			}			
		}
		unset($engine_name);
		
		if(!$engine){
		    
			// If none of the requested engines are active, 
			// use the loopback engine
		    
			$engine =& $this->engines['loopback'];
		}


		try {
			$result = $engine->readCache($args);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error in engine->readCache()",
				'data'=>$args,                                   
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
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
	 *	=> VAL @param array $engine | Class cache engines array
	 *	=> VAL @param string $namespace | Class namespace
	 *	=> VAL @param string/array $keys | Single key as string. Multiple keys as array of string.
	 * 	 
	 * @return array | Exception on failure. Array of paged cache page images on success.
	 */

	public function readCachePage($args){	    
	    	  
	    
		// Heavily error-check. Bad parameters passed to this method could cause 
		// difficult-to-debug intermittent errors throughout the plugin
		// =====================================================================

		if( !FOX_sUtil::keyExists('engine', $args) || empty($args['engine']) ) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid engine parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !FOX_sUtil::keyExists('namespace', $args) || empty($args['namespace']) ) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Invalid namespace parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !FOX_sUtil::keyExists('pages', $args) || empty($args['pages']) ) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Invalid pages parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}		
							   
		// Find the first active cache engine specified by the class
		// =====================================================================
		
		$engine = false;
		
		foreach( $args['engine'] as $engine_name ){
		    
			// Check engine exists
		    
			if( FOX_sUtil::keyExists($engine_name, $this->engines) ){

				// Check engine is active
			    
				if( $this->engines[$engine_name]->isActive() ){

					$engine =& $this->engines[$engine_name];
					break;
				}
			}
			else {
			    
				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Specified engine name doesn't exist",
					'data'=>$engine_name,			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));			    			    
			}			
		}
		unset($engine_name);
		
		if(!$engine){
		    
			// If none of the requested engines are active, 
			// use the loopback engine
		    
			$engine =& $this->engines['loopback'];
		}
		
		
		// Check for illegal page names
		// =============================================================
		
		if( !is_array($args['pages']) ){	
		    
			$args['pages'] = array($args['pages']);
		}
														
		foreach( $args['pages'] as $page_name ){

			if($page_name == 'cache'){

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Called with reserved page name 'cache'",
					'data'=>$args['pages'],				    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));		    		  
			}
		}	
		unset($page_name);
			
		
		try {
			$result = $engine->readCachePage($args);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error in engine->readCachePage()",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
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
	 *	=> VAL @param array $engine | Class cache engines array
	 *	=> VAL @param string $namespace | Class namespace
	 * 	 
	 * @return bool | Exception on failure. True on success.
	 */

	public function writeCache($args){

	    
		// Heavily error-check. Bad parameters passed to this method could cause 
		// difficult-to-debug intermittent errors throughout the plugin
		// =====================================================================

		if( !FOX_sUtil::keyExists('engine', $args) || empty($args['engine']) ) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid engine parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !FOX_sUtil::keyExists('namespace', $args) || empty($args['namespace']) ) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Invalid namespace parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}		
							   
		// Find the first active cache engine specified by the class
		// =====================================================================		
	    
		$engine = false;
		
		foreach( $args['engine'] as $engine_name ){
		    
			// Check engine exists
		    
			if( FOX_sUtil::keyExists($engine_name, $this->engines)  ){

				// Check engine is active
			    
				if( $this->engines[$engine_name]->isActive() ){

					$engine =& $this->engines[$engine_name];
					break;
				}
			}
			else {
			    
				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Specified engine name doesn't exist",
					'data'=>$engine_name,			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));			    			    
			}			
		}
		unset($engine_name);
		
		if(!$engine){
		    
			// If none of the requested engines are active, 
			// use the loopback engine
		    
			$engine =& $this->engines['loopback'];
		}

		try {
			$engine->writeCache($args);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error in engine->writeCache()",
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
	 *	=> VAL @param array $engine | Class cache engines array
	 *	=> VAL @param string $namespace | Class namespace
	 *	=> VAL @param string/array $pages | Single page as string. Multiple pages as array of string.
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function writeCachePage($args){

	    
		// Heavily error-check. Bad parameters passed to this method could cause 
		// difficult-to-debug intermittent errors throughout the plugin
		// =====================================================================

		if( !FOX_sUtil::keyExists('engine', $args) || empty($args['engine']) ) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid engine parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !FOX_sUtil::keyExists('namespace', $args) || empty($args['namespace']) ) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Invalid namespace parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}		
		
		if( !FOX_sUtil::keyExists('pages', $args) || empty($args['pages']) ) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Invalid pages parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}						
			    
		// Find the first active cache engine specified by the class
		// =====================================================================		
	    
		$engine = false;
		
		foreach( $args['engine'] as $engine_name ){
		    
			// Check engine exists
		    
			if( FOX_sUtil::keyExists($engine_name, $this->engines)  ){

				// Check engine is active
			    
				if( $this->engines[$engine_name]->isActive() ){

					$engine =& $this->engines[$engine_name];
					break;
				}
			}
			else {
			    
				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Specified engine name doesn't exist",
					'data'=>$engine_name,			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));			    			    
			}			
		}
		unset($engine_name);
		
		if(!$engine){
		    
			// If none of the requested engines are active, 
			// use the loopback engine
		    
			$engine =& $this->engines['loopback'];
		}
		
		
		// Check for illegal page names
		// =============================================================
		
		if( !is_array($args['pages']) ){	
		    
			$args['pages'] = array($args['pages']);
		}
														
		foreach( $args['pages'] as $page_name ){

			if($page_name == 'cache'){

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Called with reserved page name 'cache'",
					'data'=>$args['pages'],				    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));		    		  
			}
		}	
		unset($page_name);
		
		// Write pages to cache engine
		// =============================================================
		
		try {
			$engine->writeCachePage($args);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error in engine->writeCachePage()",
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
	 *	=> VAL @param array $engine | Class cache engines array
	 *	=> VAL @param string $namespace | Class namespace
	 *	=> VAL @param int $process_id | Process ID to use as owner 
	 *	=> VAL @param int $seconds |  Time in seconds from present time until lock expires	  
	 * 	  
	 * @return array | Exception on failure. Cache image on success.
	 */

	public function lockCache($args){
	    	    
		
		// Heavily error-check. Bad parameters passed to this method could cause 
		// difficult-to-debug intermittent errors throughout the plugin
		// =====================================================================

		if( !FOX_sUtil::keyExists('engine', $args) || empty($args['engine']) ) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid engine parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !FOX_sUtil::keyExists('namespace', $args) || empty($args['namespace']) ) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Invalid namespace parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !FOX_sUtil::keyExists('process_id', $args) || !is_int($args['process_id']) ) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Invalid process_id parameter",
				'data'=>array('args'=>$args),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !FOX_sUtil::keyExists('seconds', $args) ) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Invalid seconds parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}									    		
		
		// Find the first active cache engine specified by the class
		// =====================================================================		
	    
		$engine = false;
		
		foreach( $args['engine'] as $engine_name ){
		    
			// Check engine exists
		    
			if( FOX_sUtil::keyExists($engine_name, $this->engines) ){

				// Check engine is active
			    
				if( $this->engines[$engine_name]->isActive() ){

					$engine =& $this->engines[$engine_name];
					break;
				}
			}
			else {
			    
				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Specified engine name doesn't exist",
					'data'=>$engine_name,			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));			    			    
			}			
		}
		unset($engine_name);
		
		if(!$engine){
		    
			// If none of the requested engines are active, 
			// use the loopback engine
		    
			$engine =& $this->engines['loopback'];
		}		
		
		// 
		// =============================================================
		
		
		try {
			$result = $engine->lockCache($args);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error in engine->lockCache()",
				'data'=>$args,				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
				
		return $result;
		
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
	 * @param string/array $pages | Single page as string. Multiple pages as array of strings.
	 * 
         * @param array $args | Control args
	 *	=> VAL @param array $engine | Class cache engines array
	 *	=> VAL @param string $namespace | Class namespace
	 *	=> VAL @param int $process_id | Process ID to use as owner 
	 *	=> VAL @param int $seconds |  Time in seconds from present time until lock expires	  
	 *	=> VAL @param int/string/array $pages | Single page as int/string. Multiple pages as array of int/string.	 
	 * 
	 * @return mixed | Exception on failure. Mixed on success.
	 */

	public function lockCachePage($args){
	    
	    
		// Heavily error-check. Bad parameters passed to this method could cause 
		// difficult-to-debug intermittent errors throughout the plugin
		// =====================================================================

		if( !FOX_sUtil::keyExists('engine', $args) || empty($args['engine']) ) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid engine parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !FOX_sUtil::keyExists('namespace', $args) || empty($args['namespace']) ) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Invalid namespace parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !FOX_sUtil::keyExists('process_id', $args) || !is_int($args['process_id']) ) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Invalid process_id parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !FOX_sUtil::keyExists('seconds', $args) ) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Invalid seconds parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !FOX_sUtil::keyExists('pages', $args) || empty($args['pages']) ) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Invalid pages parameter",
				'data'=>$args,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}		
										
		// Find the first active cache engine specified by the class
		// =====================================================================		
	    
		$engine = false;
		
		foreach( $args['engine'] as $engine_name ){
		    
			// Check engine exists
		    
			if( FOX_sUtil::keyExists($engine_name, $this->engines) ){

				// Check engine is active
			    
				if( $this->engines[$engine_name]->isActive() ){

					$engine =& $this->engines[$engine_name];
					break;
				}
			}
			else {
			    
				throw new FOX_exception( array(
					'numeric'=>6,
					'text'=>"Specified engine name doesn't exist",
					'data'=>$engine_name,			    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));			    			    
			}			
		}
		unset($engine_name);
		
		if(!$engine){
		    
			// If none of the requested engines are active, 
			// use the loopback engine
		    
			$engine =& $this->engines['loopback'];
		}
		
		
		// Check for illegal page names
		// =============================================================
		
		if( !is_array($args['pages']) ){	
		    
			$args['pages'] = array($args['pages']);
		}
														
		foreach( $args['pages'] as $page_name ){

			if($page_name == 'cache'){

				throw new FOX_exception( array(
					'numeric'=>7,
					'text'=>"Called with reserved page name 'cache'",
					'data'=>$args['pages'],				    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));		    		  
			}
		}	
		unset($page_name);
			
			    
		try {
			$result = $engine->lockCachePage($args);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>8,
				'text'=>"Error in engine->lockCachePage()",
				'data'=>$args,				    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}	
		
		return $result;
					
	}	



} // End of class FOX_mCache


?>