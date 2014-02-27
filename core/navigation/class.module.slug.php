<?php

/**
 * FOXFIRE MODULE SLUG DATA
 * Handles registration and configuration for album, media, and network module slugs
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Navigation
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

class FOX_module_slug extends FOX_db_base {


    	var $process_id;		    // Unique process id for this thread. Used by ancestor class 
					    // FOX_db_base for cache locking. 	
	
	var $mCache;			    // Local copy of memory cache singleton. Used by ancestor 
					    // class FOX_db_base for cache operations. 
	
	var $cache;			    // Main cache array for this class
	
	var $debug_on = false;		    // Send debugging info to the debug handler	
	

	// ============================================================================================================ //

        // DB table names and structures are hard-coded into the class. This allows class methods to be
	// fired from an AJAX call, without loading the entire BP stack.

	public static $struct = array(

		"table" => "fox_sys_module_slug",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_module_slug",
		"cache_strategy" => "monolithic",
		"cache_engine" => array("memcached", "redis", "apc"),	    
		"columns" => array(
		    "module_type" =>array(  "php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "module_slug" =>array(  "php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>array("name"=>
				    "module_type_module_slug", "col"=>array("module_type", "module_slug"), "index"=>"PRIMARY"), "this_row"=>true),
		    "module_id" =>  array(  "php"=>"int",	"sql"=>"tinyint",	"format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>array("name"=>
				    "module_type_module_id", "col"=>array("module_type", "module_id"), "index"=>"UNIQUE"), "this_row"=>true),
		    "php_class" =>  array(  "php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>255,	"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>array("name"=>
				    "module_type_php_class", "col"=>array("module_type", "php_class"), "index"=>"UNIQUE"), "this_row"=>true)
		 )
	);

	// PHP allows this: $foo = new $class_name; $result = $foo::$struct; but does not allow this: $result = $class_name::$struct;
	// or this: $result = $class_name::get_struct(); ...so we have to do this: $result = call_user_func( array($class_name,'_struct') );

	public static function _struct() {

		return self::$struct;
	}

	
	// ================================================================================================================

	
	public function __construct($args=null) {

		if($args){
			$this->process_id = &$args['process_id'];
			$this->mCache = &$args['mCache'];				
		}
		else {
			global $fox;
			$this->process_id = &$fox->process_id;
			$this->mCache = &$fox->mCache;				
		}

		try{
			self::loadCache();
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Error loading cache",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
	}


	/**
	 * Loads module_slug data into the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param array $data | Array of identifiers to cache, or null to cache all rows in the database
	 *	=> VAL @param string/array $module_type | Single module_type as string. Multiple module_types as array of strings.
	 *	=> VAL @param string/array $module_slug | Single module_slug as string. Multiple module_slugs as array of strings.
	 *	=> VAL @param int/array $module_id | Single module_id as int. Multiple module_ids as array of int.
	 *
	 * @return array | Exception on failure. False on nonexistent. Results array on success.
	 */

	public function load($data=null, $skip_load=false){

	    
		$db = new FOX_db();
		$struct = self::_struct();

		$args = array();

		// Build query args
		// ====================================================================

		if( $data["module_type"]){
			$args[] = array("col"=>"module_type", "op"=>"=", "val"=>$data["module_type"] );
		}
		elseif( $data["module_slug"]){
			$args[] = array("col"=>"module_slug", "op"=>"=", "val"=>$data["module_slug"] );
		}
		elseif( $data["module_id"]){
			$args[] = array("col"=>"module_id", "op"=>"=", "val"=>$data["module_id"] );
		}

		$ctrl = array("format"=>"array_key_array", "key_col"=>array("module_type", "module_slug") );
		$columns=null;		

		try {
			$db_result = $db->runSelectQuery($struct, $args, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error reading from database",
				'data'=>array('args'=>$args, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}		


		if(!$db_result){	
		    
			// No results were found
			return false;		    
		}
		else {
		    
			// Fetch the persistent cache image
			// ===================================================

			if($skip_load){

				$cache_image = $this->cache;
			}
			else {
				try {
					$cache_image = self::readCache();
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Cache read error",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));
				}
			}
			
			// Rebuild the cache image
			// ===================================================

			if( !$data["module_type"] && !$data["module_slug"] && !$data["module_id"]){

				$cache_image["all_cached"] = true;
			}
			elseif( !$data["module_slug"] && !$data["module_id"] ){


				if( !is_array($data["module_type"]) ){

					$cache_module_types = array($data["module_type"]);
				}
				else {
					$cache_module_types = $data["module_type"];
				}

				foreach( $cache_module_types as $module_type_name){

					$cache_image["module_types"][$module_type_name] = true;
				}
				unset($cache_module_types, $module_type_name);

			}
		
			foreach( $db_result as $module_type => $module_slugs ){

				foreach( $module_slugs as $module_slug => $module_slug_data ){

					$cache_image["data"][$module_type][$module_slug] = $module_slug_data;
				}
				unset($module_type, $module_slugs);

			}
			unset($module_type, $module_slugs);

			// Update the persistent cache
			// ===================================================

			try {
				 self::writeCache($cache_image);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Cache write error",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

			// Update the class cache
			$this->cache = $cache_image;
			
			return true;

		}


	}


	/**
	 * Registers ownership of a module_slug to a FoxFire page module
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $module_type | Module type. "page", "album", "media", "network"
	 * @param string $module_slug | Module slug
	 * @param int $module_id | Numeric id of the module
	 * @param string $php_class | Module's PHP class
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function addSlug($module_type, $module_slug, $module_id, $php_class) {

	    
		$data = array( array("module_type"=>$module_type, "module_slug"=>$module_slug, 
				     "module_id"=>$module_id, "php_class"=>$php_class) );

		try {
			$result = self::addSlugMulti($data);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::addSlugMulti()",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		
		

		return $result;

	}


	/**
	 * Registers ownership of one or more slugs to a FoxFire page module
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | Array of row arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param string $module_type | module type
	 *	    => VAL @param int $module_id | id of the module
	 *	    => VAL @param string $php_class | PHP class for the module
	 *	    => VAL @param int $module_slug | module module_slug
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function addSlugMulti($data){
	    
	    
		$db = new FOX_db();		
		$struct = self::_struct();
		
		$columns = null;
		$ctrl = null;

		try {
			$db->runInsertQueryMulti($struct, $data, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error writing to database",
				'data'=>array('data'=>$data,'columns'=>$columns,'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		// Fetch the persistent cache image
		// ===================================================

		try {
			$cache_image = self::readCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Cache read error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
				
		// Rebuild the cache image
		// ===================================================
		
		foreach( $data as $row ){

			$row_data = array(  "module_id"=>$row["module_id"],
					    "php_class"=>$row["php_class"]
				    );

			$cache_image["data"][$row["module_type"]][$row["module_slug"]] = $row_data;
		}
		unset($row, $row_data);

		
		// Update the persistent cache
		// ===================================================

		try {
			    self::writeCache($cache_image);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Cache write error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// Update the class cache
		$this->cache = $cache_image;
		
		return true;

	}


	/**
	 * Returns an array containing all data for all module_slugs owned by FoxFire page modules
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return bool/array | False on failure. Empty array on nonexistent. Array of class names on success.
	 */

	public function getAll() {

	    
		// If the all_cached flag isn't set in the class cache, reload the class
		// cache from the persistent cache.
	    
		if($this->cache["all_cached"] != true){

			try {
				self::loadCache();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Cache read error",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}

			// If the all_cached flag isn't set in the class cache, after reloading from
			// the persistent cache, load the module data from the db.
			
			if($this->cache["all_cached"] != true){
			    
				try {
					self::load(null, $skip_load=true);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Error calling self::load()",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));
				}

			}
		}

		$result = $this->cache["data"];

		return $result;
		
	}


	/**
	 * Returns all entries that belong to the specified module_type or module_types
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string/array $module_types | Single module_type name as string. Multiple module_type names as array of strings.
	 * @return null/array | Exception on failure. Null or empty array on nonexistent. Array of row data arrays on success.
	 */

	public function getType($module_types){


		if( empty($module_types) ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Called with empty module_types parameter",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		elseif( !is_array($module_types) ){
			// Handle single string $module_type
			$module_types = array($module_types);
		}

		// Build a list of all module_types that are not in the cache
		// ========================================================

		if( !$this->cache["all_cached"] ){

			$missing_module_types = array();
			$cache_reloaded = false;

			foreach($module_types as $module_type){

				if( !$this->cache["module_types"][$module_type] ){

					// If the module_type is not present in the class cache, try reloading
					// the class cache from the persistent cache.
				    
					if(!$cache_reloaded){

						try {
							self::loadCache();
						}
						catch (FOX_exception $child) {

							throw new FOX_exception( array(
								'numeric'=>2,
								'text'=>"Cache read error",
								'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								'child'=>$child
							));		    
						}
						
						$cache_reloaded = true;

						//  If the module_type is still not present in the class cache after reloading from
						//  the persistent cache, add it to the array of module_types to fetch from the db.
						
						if( !$this->cache["module_types"][$module_type] ){
							$missing_module_types[] = $module_type;
						}
					}
					else {
						$missing_module_types[] = $module_type;
					}
				}
			}
			unset($module_type);


			// Load all missing module_types (uses a single query)
			// ========================================================
			
			if($missing_module_types){
			    			    
				try {
					self::load( array("module_type"=>$missing_module_types), $cache_reloaded);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>3,
						'text'=>"Error in self::load() method",
						'data'=>array("module_type"=>$missing_module_types, 'cache_reloaded'=>$cache_reloaded),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}			    
				
			}

		}


		// If only one module_type was requested, just return the contents of its db row. If
		// multiple module_types were requested, return them as a set of arrays
		// =======================================================================================
		if( count($module_types) == 1){

			$result = $this->cache["data"][$module_types[0]];
		}
		else {
			$result = array();

			foreach( $module_types as $module_type ){

				if($this->cache["data"][$module_type] != null){
					$result[$module_type] = $this->cache["data"][$module_type];
				}
			}
			unset($module_type);
		}

		return $result;

	}


	/**
	 * Returns all entries that belong to the specified module_slug or module_slugs
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $module_type | Single module_type name as string.
	 * @param string/array $module_slugs | Single module_slug name as string. Multiple module_slug names as array of strings.
	 * @return null/array | Exception on failure. Null or empty array on nonexistent. Array of row data arrays on success.
	 */

	public function getSlug($module_type, $module_slugs){

	    
		if( empty($module_type) || !is_string($module_type) ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid module_types parameter",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    
		}

		if( empty($module_slugs) ){
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Invalid module_slugs parameter",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !is_array($module_slugs) ){
			// Handle single string $page_id
			$module_slugs = array($module_slugs);
		}

		// Build a list of all module_slugs that are not in the cache
		// ========================================================

		if( !$this->cache["all_cached"] && !$this->cache["module_types"][$module_type] ){

			$missing_module_slugs = array();
			$cache_reloaded = false;

			foreach($module_slugs as $module_slug){

				if( !$this->cache["data"][$module_type][$module_slug] ){

					// If the module_type is not present in the class cache, try reloading
					// the class cache from the persistent cache.
					if(!$cache_reloaded){

						try {
							self::loadCache();
						}
						catch (FOX_exception $child) {

							throw new FOX_exception( array(
								'numeric'=>3,
								'text'=>"Cache read error",
								'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								'child'=>$child
							));		    
						}
						
						$cache_reloaded = true;

						//  If the module_type is still not present in the class cache after reloading from
						//  the persistent cache, add it to the array of module_types to fetch from the db.
						if( !$this->cache["data"][$module_type][$module_slug] ){
							$missing_module_slugs[] = $module_slug;
						}
					}
					else {
						$missing_module_slugs[] = $module_slug;
					}
				}
			}
			unset($module_slug);


			// Load all missing module_slugs (uses a single query)
			// ========================================================
			
			if($missing_module_slugs){
			    
				$slugs_data = array("module_type"=>$module_type, "module_slug"=>$module_slugs );
				
				try {
					self::load($slugs_data, $cache_reloaded);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>4,
						'text'=>"Error in self::load() method",
						'data'=>array('slugs_data'=>$slugs_data, 'cache_reloaded'=>$cache_reloaded),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}			    
				
			}

		}


		// If only one module_slug was requested, just return the contents of its db row. If
		// multiple module_slugs were requested, return them as a set of arrays
		// =======================================================================================
		if( count($module_slugs) == 1){

			$result = $this->cache["data"][$module_type][$module_slugs[0]];
		}
		else {
			$result = array();

			foreach( $module_slugs as $module_slug ){

				if($this->cache["data"][$module_type][$module_slug] != null){
					$result[$module_slug] = $this->cache["data"][$module_type][$module_slug];
				}
			}
			unset($module_slug);
		}

		return $result;

	}


	/**
	 * Returns the module_slug corresponding to a module_type + module_id pair
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $module_type | Single module_type name as string.
	 * @param int $module_id | Module id
	 * 
	 * @return null/string| Exception on failure. False on nonexistent. Data array on success.
	 */

	public function getID($module_type, $module_id){


		try {
			$module_slugs = self::getType($module_type);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::getType()",
				'data'=>array('module_type'=>$module_type, 'module_id'=>$module_id),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
				
		$result = false;

		if($module_slugs){

			foreach($module_slugs as $module_slug => $data){

				if($data["module_id"] == $module_id){

					$result = array(
						"module_slug"=>$module_slug,
						"php_class"=>$data["php_class"]
					);
					break;
				}
			}
		}

		return $result;

	}


	/**
	 * Edits a module_slug table entry
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param array $data |
	 *	=> VAL @param string $module_type | module type
	 *	=> VAL @param int $module_id | id of the module
	 *	=> VAL @param string $php_class | PHP class for the page module
	 *	=> VAL @param int $module_slug | module module_slug
	 *
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function edit($data) {

	    
		$db = new FOX_db();
		$struct = self::_struct();

		// Trap bad input
		// =============================================================

		if(!$data["module_type"] || !$data["module_id"]){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Must supply module_type and module_id",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		if(!$data["php_class"] && !$data["module_slug"]){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Must specify either php_class or module_slug",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    
		}

		// Lock the cache
		// ===========================================================

		try {
			self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error locking cache",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// Update the database
		// ===========================================================

		$update = array();

		if($data["module_slug"]){

			$update["module_slug"] = $data["module_slug"];
		}

		if($data["php_class"]){

			$update["php_class"] = $data["php_class"];
		}

		$args = array(
				array("col"=>"module_type", "op"=>"=", "val"=>$data["module_type"]),
				array("col"=>"module_id", "op"=>"=", "val"=>$data["module_id"])
		);

		$columns = array("mode"=>"exclude", "col"=>array("module_type", "module_id") );

		
		try {
			$rows_changed = $db->runUpdateQuery($struct, $update, $args, $columns);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error updating database",
				'data'=>array('update'=>$update, 'args'=>$args, 'columns'=>$columns),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		// Flush the cache
		// =============================================================
		
		// NOTE: this is a case where it's not practical to rebuild the cache. We'd have to run an 
		// additional query to fetch the old module_slug and php_class values to clear them from the cache
		
		try {
			self::flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Error flushing cache",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		return (int)$rows_changed;

	}


	/**
	 * Removes one or more module_types from the database and cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string/array $module_types | Single module_type as string. Multiple module_types as array of strings.
	 * @return int | Exception on failure. Int number of db rows changed on success.
	 */

	public function deleteType($module_types) {


		$db = new FOX_db();
		$struct = self::_struct();

		if( empty($module_types) ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid module_types parameter",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		elseif( !is_array($module_types) ){
		    
			// Handle single int as input
			$module_types = array($module_types);
		}

		// Lock the cache
		// ===========================================================

		try {
			$cache_image = self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error locking cache",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		// Update the database
		// ===========================================================
		
		$args = array(
				array("col"=>"module_type", "op"=>"=", "val"=>$module_types)
		);
		
		try {
			$rows_changed = $db->runDeleteQuery($struct, $args);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error deleting from database",
				'data'=>array('args'=>$args),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		// Rebuild the cache image
		// ===========================================================

		foreach( $module_types as $module_type ){

			if( FOX_sUtil::keyExists($module_type, $cache_image["data"]) ){

				unset($cache_image["data"][$module_type]);
				unset($cache_image["module_types"][$module_type]);
			}

		}
		unset($module_type);

		
		// Write the image back to the persistent cache, releasing our lock
		// ===========================================================

		try {
			self::writeCache($cache_image);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Cache write error",
				'data'=>array('cache_image'=>$cache_image),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// Update the class cache
		$this->cache = $cache_image;
		
		return (int)$rows_changed;
		
		
	}


	/**
	 * Removes one or more module_slugs from the database and cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @param string $module_type | Single module_type as string.
	 * @param string/array module_slugs | Single module_slug as string. Multiple module_slugs as array of strings.
	 * 
	 * @return int | Exception on failure. Int number of db rows changed on success.
	 */

	public function deleteSlug($module_type, $module_slugs) {


		$db = new FOX_db();
		$struct = self::_struct();

		if( empty($module_type) || !is_string($module_type) ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid module_types parameter",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    
		}

		if( empty($module_slugs) ){
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Invalid module_slugs parameter",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !is_array($module_slugs) ){
		    
			// Handle single int as input
			$module_slugs = array($module_slugs);
		}

		// Lock the cache
		// ===========================================================

		try {
			$cache_image = self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error locking cache",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// Update the database
		// ===========================================================
		
		$args = array(
				array("col"=>"module_type", "op"=>"=", "val"=>$module_type),
				array("col"=>"module_slug", "op"=>"=", "val"=>$module_slugs)
		);
		
		try {
			$rows_changed = $db->runDeleteQuery($struct, $args);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error deleting from database",
				'data'=>array('args'=>$args),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		// Rebuild the cache image
		// ===========================================================		

		foreach( $module_slugs as $module_slug ){

			if( FOX_sUtil::keyExists($module_slug, $cache_image["data"][$module_type]) ){

				unset($cache_image["data"][$module_type][$module_slug]);

				// If deleting the module_slug makes its parent module_type empty, remove
				// the parent module_type from the cache as well

				if( count($cache_image["data"][$module_type]) == 0 ){

					unset($cache_image["data"][$module_type]);
					unset($cache_image["module_types"][$module_type]);
				}
			}

		}
		unset($module_slug);

		// Write the image back to the persistent cache, releasing our lock
		// ===========================================================

		try {
			self::writeCache($cache_image);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Cache write error",
				'data'=>array('cache_image'=>$cache_image),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// Update the class cache
		$this->cache = $cache_image;

		return (int)$rows_changed;

	}


	/**
	 * Removes one or more modules from the database and cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @param string $module_type | Single module_type as string.
	 * @param int/array $module_ids | Single module_id as int. Multiple module_id's as array of int.
	 * 
	 * @return int | Exception on failure. Int number of db rows changed on success.
	 */

	public function deleteModule($module_type, $module_ids) {

	    
		$db = new FOX_db();
		$struct = self::_struct();

		if( empty($module_type) || !is_string($module_type) ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid module_types parameter",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    
		}

		if( empty($module_ids) ){
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Invalid module_ids parameter",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !is_array($module_ids) ){
			// Handle single int as input
			$module_ids = array($module_ids);
		}

		// Lock the cache
		// ===========================================================

		try {
			self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error locking cache",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		// Update the database
		// ===========================================================
		
		$args = array(
				array("col"=>"module_type", "op"=>"=", "val"=>$module_type),
				array("col"=>"module_id", "op"=>"=", "val"=>$module_ids)
		);
		
		try {
			$rows_changed = $db->runDeleteQuery($struct, $args);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error deleting from database",
				'data'=>array('args'=>$args),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}

		// Flush the cache
		// =============================================================
		
		// NOTE: this is a case where it's not practical to rebuild the cache. We'd have to run an 
		// additional query to fetch the old module_slug and php_class values to clear them from the cache
		
		try {
			self::flushCache();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error flushing cache",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}

		
		return (int)$rows_changed;
		
		
	}
	
	

} // End of class FOX_module_slug



/**
 * Hooks on the plugin's install function, creates database tables and
 * configuration options for the class.
 *
 * @version 1.0
 * @since 1.0
 */

function install_FOX_module_slug(){

	$cls = new FOX_module_slug();
	
	try {
		$cls->install();
	}
	catch (FOX_exception $child) {

		// If the error is being thrown because the table already exists, 
		// just discard it
	    
		if( $child->data['child']->data['numeric'] != 2 ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error creating db table",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
	}
	
}
add_action( 'fox_install', 'install_FOX_module_slug', 2 );


/**
 * Hooks on the plugin's uninstall function. Removes all database tables and
 * configuration options for the class.
 *
 * @version 1.0
 * @since 1.0
 */

function uninstall_FOX_module_slug(){

	$cls = new FOX_module_slug();
	
	try {
		$cls->uninstall();
	}
	catch (FOX_exception $child) {

		// If the error is being thrown because the table doesn't exist, 
		// just discard it
	    
		if( $child->data['child']->data['numeric'] != 3 ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error dropping db table",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
	}
	
}
add_action( 'fox_uninstall', 'uninstall_FOX_module_slug', 2 );

?>