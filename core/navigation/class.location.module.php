<?php

/**
 * FOXFIRE NAVIGATION LOCATION MODULE
 * Handles registration and configuration for module targets
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

class FOX_loc_module extends FOX_db_base {


    	var $process_id;		    // Unique process id for this thread. Used by ancestor class 
					    // FOX_db_base for cache locking. 	
	
	var $mCache;			    // Local copy of memory cache singleton. Used by ancestor 
					    // class FOX_db_base for cache operations. 
	
	var $cache;			    // Main cache array for this class
	

	// ============================================================================================================ //

        // DB table names and structures are hard-coded into the class. This allows class methods to be
	// fired from an AJAX call, without loading the entire BP stack.

	public static $struct = array(

		"table" => "fox_nav_location_module",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_loc_module",
		"cache_strategy" => "monolithic",
		"cache_engine" => array("memcached", "redis", "apc"),	    
		"columns" => array(
		    "location" =>   array(  "php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>true),

		    // Note: targets must be processed as "string" instead of "serialize" so that we can search
		    // for "location" = "page + "target" = "page_id" when a WordPress page is deleted

		    "target" =>  array(  "php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>array("name"=>
				    "location_target", "col"=>array("location", "target"), "index"=>"PRIMARY"), "this_row"=>true),

		    "tab_title" =>  array(  "php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"",			"auto_inc"=>false,  "default"=>null,	"index"=>false),

		    // Note: the database can't check for duplicate tab positions at same location. Tab position will always be 0 for modules at "page"
		    // location because they do not use a tab position. Positions must also be checked against tabs owned by BuddyPress

		    "tab_position" =>array(  "php"=>"int",	"sql"=>"tinyint",	"format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED",		"auto_inc"=>false,  "default"=>null,	"index"=>false),

		    // Each module can only have one entry. Otherwise there will be a cardinality problem when one module tries to point to another module's pages
		    "module_id" =>  array(  "php"=>"int",	"sql"=>"tinyint",	"format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>"UNIQUE"),


		    "php_class" =>  array(  "php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>255,	"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>"UNIQUE")

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
	 * Loads target data into the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param array $data | Array of targets to cache, or null to cache all rows in the database
	 *	=> VAL @param string/array $location | Single location as string. Multiple locations as array of strings.
	 *	=> VAL @param string/array $target | Single target as string. Multiple targets as array of strings.
	 *	=> VAL @param int/array $module_id | Single module_id as int. Multiple module_ids as array of int.
	 *
	 * @return bool | Exception on failure. False on nonexistent. True on success.
	 */

	public function load($data, $skip_load=false){


		$db = new FOX_db();
		$struct = self::_struct();

		// Build and run query
		// ===========================================================
		
		$args = array();

		if( $data["location"]){
		    
			$args[] = array("col"=>"location", "op"=>"=", "val"=>$data["location"] );
		}
		elseif( $data["target"]){
		    
			$args[] = array("col"=>"target", "op"=>"=", "val"=>$data["target"] );
		}
		elseif( $data["module_id"]){
		    
			$args[] = array("col"=>"module_id", "op"=>"=", "val"=>$data["module_id"] );
		}

		$ctrl = array("format"=>"array_key_array", "key_col"=>array("location", "target") );
		$columns = null;

		try {
			$db_result = $db->runSelectQuery($struct, $args, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error reading from database",
				'data'=>array('args'=>$args,'columns'=>$columns,'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		

		if(!$db_result){			    
			// No results were found
			return false;		    
		}

		    
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

		if( empty($data) ){

			$cache_image["all_cached"] = true;
		}
		elseif( !$data["target"] && !$data["module_id"] ){


			if( !is_array($data["location"]) ){

				$cache_locations = array($data["location"]);
			}
			else {
				$cache_locations = $data["location"];
			}

			foreach( $cache_locations as $location_name){

				$cache_image["locations"][$location_name] = true;
			}
			unset($cache_locations, $location_name);

		}

		foreach( $db_result as $location => $targets ){

			foreach( $targets as $target => $target_data ){

				if($location == "page"){
					// If the location is a page, only copy the fields used by pages
					$cache_image["data"][$location][$target]["module_id"] = $target_data["module_id"];
					$cache_image["data"][$location][$target]["php_class"] = $target_data["php_class"];
				}
				else {
					$cache_image["data"][$location][$target] = $target_data;
				}
			}
			unset($location, $targets);

		}
		unset($location, $targets);


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
	 * Registers ownership of a target to a FoxFire page module if no entry exists in the database. Updates
	 * the existing entry if an entry already exists in the database.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data |
	 *	=> VAL @param string $location | Display location.
	 *	=> VAL @param int/string $target | Module target. Must be (int) for pages, (string) for all other locations.
	 *	=> VAL @param string $tab_title | Title for the module's tab (not used for pages).
	 *	=> VAL @param int $tab_position | Tab position (0-255) (not used for pages).
	 *	=> VAL @param int $module_id | Page module id.
	 *	=> VAL @param string $php_class | PHP class for the page module.
	 * 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function setTarget($data) {


		if( !is_array($data) || empty($data["module_id"]) ) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Missing value in data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		// Check if the module_id already exists, since module_id's are globally unique
		
		try {
			$module_data = self::getID($data["module_id"]);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error in self::getID()",
				'data'=>$data["module_id"],
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		
		

		// CASE 1: Module exists in the db. Update the existing record.
		// ============================================================
		if($module_data){

			// If the supplied data is the same as what's in the database
			// skip updating the db record

			$check_data = wp_parse_args($data, $module_data);

			if( $module_data["location"] == $check_data["location"] ){

				if( ($module_data["location"] == "page") &&
				    ($module_data["target"] == $check_data["target"]) ){

					return 0;
				}
				elseif( ($module_data["location"] != "page") &&
					($module_data["target"] == $check_data["target"]) &&
					($module_data["tab_title"] == $check_data["tab_title"]) &&
					($module_data["tab_position"] == $check_data["tab_position"])){

					return 0;
				}

				// If the location is not being changed, we can fill-in missing fields
				// from the existing db entry.
				$data = wp_parse_args($data, $module_data);
			}

			try {
				$rows_changed = self::edit($data);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error in self::edit()",
					'data'=>$data,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
		
			return (int)$rows_changed;
			

		}
		// CASE 2: Module doesn't exist in the db. Add new record.
		// ============================================================
		else {

			try {
				$rows_changed = self::addTarget($data);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Error in self::addTarget()",
					'data'=>$data,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
			
			return (int)$rows_changed;

		}

		
	}


	/**
	 * Registers ownership of a target to a FoxFire page module
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data |
	 *	=> VAL @param string $location | Display location.
	 *	=> VAL @param int/string $target | Module target. Must be (int) for pages, (string) for all other locations.
	 *	=> VAL @param string $tab_title | Title for the module's tab (not used for pages).
	 *	=> VAL @param int $tab_position | Tab position (0-255) (not used for pages).
	 *	=> VAL @param int $module_id | Page module id.
	 *	=> VAL @param string $php_class | PHP class for the page module.
	 * 
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function addTarget($data) {

	    
		// Wrap in array to match addTargetMulti() format
		$data = array($data);

		try {
			$rows_changed = self::addTargetMulti($data);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in self::addTargetMulti()",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		return (int)$rows_changed;

	}


	/**
	 * Registers one or more "Page Module" <=> "Target" relationships
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | Array of row arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param string $location | Display location.
	 *	    => VAL @param int/string $target | Module target. Must be (int) for pages, (string) for all other locations.
	 *	    => VAL @param string $tab_title | Title for the module's tab (not used for pages).
	 *	    => VAL @param int $tab_position | Tab position (0-255) (not used for pages).
	 *	    => VAL @param int $module_id | Page module id.
	 *	    => VAL @param string $php_class | PHP class for the page module.
	 *
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function addTargetMulti($data){


		$db = new FOX_db(); 
		$struct = self::_struct();
		
		$insert_data = array();

		// Process each row
		// ===========================================================

		foreach( $data as $row ){

			if( !is_array($row) || empty($row["location"]) || empty($row["target"]) ||
			    empty($row["module_id"]) || empty($row["php_class"]) ) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Missing value in data array",
					'data'=>$row,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}

			if( $row["location"] == 'page' ) {


				if( !is_int($row["target"]) ){

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Attempted to use non-int value as a page target",
						'data'=>$row,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));
				}

				$insert_data[] = array(
							"location" => $row["location"],
							"target" => (int)$row["target"],
							"module_id" => $row["module_id"],
							"php_class" => $row["php_class"]
				);

			}
			else {

				if( empty($row["tab_title"]) || empty($row["tab_position"]) ) {

					throw new FOX_exception( array(
						'numeric'=>3,
						'text'=>"Non-page targets require tab_title and tab_position to be set",
						'data'=>$row,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));
				}

				if( !is_int($row["tab_position"]) ){

					throw new FOX_exception( array(
						'numeric'=>4,
						'text'=>"Attempted to use non-int value as tab position",
						'data'=>$row,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));				    
				}

				$insert_data[] = array(
							"location" => $row["location"],
							"target" => (string)$row["target"],
							"tab_title" => $row["tab_title"],
							"tab_position" => (int)$row["tab_position"],
							"module_id" => $row["module_id"],
							"php_class" => $row["php_class"]
				);

			}

		}
		unset($row);


		// Lock the cache
		// ===========================================================

		try {
			$cache_image = self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Error locking cache",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// Update the database
		// ===========================================================

		$columns = null;
		$ctrl = null;
		
		try {
			$rows_changed = $db->runInsertQueryMulti($struct, $insert_data, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error reading from database",
				'data'=>array('insert_data'=>$insert_data, 'columns'=>$columns, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		
		// Rebuild the cache image
		// ===========================================================

		foreach( $insert_data as $row ){

			$row_data = array(  "module_id"=>$row["module_id"],
					    "php_class"=>$row["php_class"]
			);

			if( array_key_exists("tab_title", $row) ){

				$row_data["tab_title"] = $row["tab_title"];
			}

			if( array_key_exists("tab_position", $row) ){

				$row_data["tab_position"] = $row["tab_position"];
			}

			$cache_image["data"][$row["location"]][$row["target"]] = $row_data;

		}
		unset($row);

		
		// Write the image back to the persistent cache, releasing our lock
		// ===========================================================

		try {
			self::writeCache($cache_image);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>7,
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
	 * Returns all targets that belong to the specified location or locations
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string/array $locations | Single location name as string. Multiple location names as array of strings.
	 * @return null/array | Exception on failure. Null or empty array on nonexistent. Array of row data arrays on success.
	 */

	public function getLocation($locations=null){


		// CASE 1: Fetch all locations in database
		// =============================================================
		if( empty($locations) ){

			// If the all_cached flag isn't set in the class cache, reload the class
			// cache from the persistent cache.

			if( !FOX_sUtil::keyTrue("all_cached", $this->cache) ){

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

				// If the all_cached flag isn't set in the class cache after reloading from
				// the persistent cache, load the module data from the db.

				if( !FOX_sUtil::keyTrue("all_cached", $this->cache) ){

					try {
						self::load(null, true);
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>2,
							'text'=>"Error in self::load()",
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));		    
					}							
				}
			}

			return $this->cache["data"];
			

		}
		// CASE 2: Fetch specific location or array of locations
		// =============================================================

		else {

			// Handle single string $location
			if( !is_array($locations) ){

				$locations = array($locations);
			}

			// Build a list of all locations that are not in the cache
			// ========================================================

			if( !FOX_sUtil::keyTrue("all_cached", $this->cache)  ){

				$missing_locations = array();
				$cache_reloaded = false;

				foreach($locations as $location){

					if( !FOX_sUtil::keyExists($location, $this->cache["locations"]) ){

						// If the location is not present in the class cache, try reloading
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

							//  If the location is still not present in the class cache after reloading from
							//  the persistent cache, add it to the array of locations to fetch from the db.

							if( !FOX_sUtil::keyExists($location, $this->cache["locations"]) ){

								$missing_locations[] = $location;
							}
						}
						else {
							$missing_locations[] = $location;
						}
					}
				}
				unset($location);


				// Cache all missing locations (uses a single query)
				// ========================================================
				
				if($missing_locations){

					try {
						self::load( array("location"=>$missing_locations), $cache_reloaded);
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>4,
							'text'=>"Error in self::load()",
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));		    
					}
				}

			}


			// If only one location was requested, just return the contents of its db row. If
			// multiple locations were requested, return them as a set of arrays
			// =======================================================================================
			if( count($locations) == 1){

				$result = $this->cache["data"][$locations[0]];
			}
			else {
				$result = array();

				foreach( $locations as $location ){

					if( FOX_sUtil::keyExists($location, $this->cache["data"]) ){

						$result[$location] = $this->cache["data"][$location];
					}
				}
				unset($location);
			}

			return $result;

		}


	}


	/**
	 * Returns all targets owned by the module_id or module id's.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | Single module_id as int. Multiple module_id's as array of ints.
	 * @return null/string| Exception on failure. Null or empty array on nonexistent. Data array on success.
	 */

	public function getID($module_id){


		$result = array();

		// Switch between single and multi modes
		if( !is_array($module_id) ){

			$single = true;
			$module_id = array($module_id);
			$result = null;
		}
		else {
			$single = false;
			$result = array();
		}

		// Load all locations from the database
		// =============================================================
		
		try {
			$locations = self::getLocation(null);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error loading ids from database",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		
		
		// Build result
		// =============================================================
		
		if($locations){

			foreach($locations as $location => $targets){

				foreach($targets as $target => $data){

					if( array_search($data["module_id"], $module_id) !== false ){

						if($single){

							$result = array(
								"location"=>$location,
								"target"=>$target,
								"tab_title"=>$data["tab_title"],
								"tab_position"=>$data["tab_position"],
								"php_class"=>$data["php_class"]
							);

							// Performance tweak
							return $result;
						}
						else {

							$row = array(
								"location"=>$location,
								"target"=>$target,
								"tab_title"=>$data["tab_title"],
								"tab_position"=>$data["tab_position"],
								"php_class"=>$data["php_class"]
							);

							$result[$data["module_id"]] = $row;

						}
					}
				}
				unset($target, $data);
			}
			unset($location, $targets);

		}

		
		return $result;

	}


	/**
	 * Edits a a module's table entry
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param array $data |
	 *	=> VAL @param string $location | Display location.
	 *	=> VAL @param int/string $target | Module target. Must be (int) for pages, (string) for all other locations.
	 *	=> VAL @param string $tab_title | Title for the module's tab (not used for pages).
	 *	=> VAL @param int $tab_position | Tab position (0-255) (not used for pages).
	 *	=> VAL @param int $module_id | Page module id.
	 *
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function edit($data) {

	    
		$db = new FOX_db();
		$struct = self::_struct();

		// Trap bad input
		// =============================================================

		if( !$data["module_id"] ){

		    	throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Must supply module_id",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		if( !$data["target"] && !$data["tab_title"] && !$data["tab_position"] ){

		    	throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Must supply at least 1 field to change",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    
		}

		// Trap nonexistent module_id
		// =============================================================

		try {
			$db_record = self::getID($data["module_id"]);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error in self::getID()",
				'data'=>$data["module_id"],
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		if(!$db_record){

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Supplied module_id doesn't have a database record",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));			
		}

		$update = array();

		// CASE 1: Changing the location
		// =============================================================
		if( $data["location"] && ($data["location"] != $db_record["location"]) ){


			// CASE 1A: Changing from slug to page
			// ------------------------------------------
			if($data["location"] == "page"){


				if(!$data["target"]){

					throw new FOX_exception( array(
						'numeric'=>5,
						'text'=>"Target must be set when changing location from a slug to a page",
						'data'=>$data,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));	
				}

				if( !is_int($data["target"]) ){

					throw new FOX_exception( array(
						'numeric'=>6,
						'text'=>"Attempted to use non-int value as a page target",
						'data'=>$data,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));
				}

				$update["location"] = $data["location"];
				$update["target"] = (int)$data["target"];
				$update["tab_title"] = null;
				$update["tab_position"] = null;

			}

			// CASE 1B: Changing from page to slug
			// ------------------------------------------
			elseif( $db_record["location"] == "page" ){

				if(!$data["target"]){

					throw new FOX_exception( array(
						'numeric'=>7,
						'text'=>"Target must be set when changing location from a page to a slug",
						'data'=>$data,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));					
				}

				if(!$data["tab_title"] || !$data["tab_position"] ){

					throw new FOX_exception( array(
						'numeric'=>8,
						'text'=>"The tab_title and tab_position fields must be set when changing location from a page to a slug",
						'data'=>$data,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));
				}

				if( !is_int($data["tab_position"]) ){

					throw new FOX_exception( array(
						'numeric'=>9,
						'text'=>"Attempted to use non-int value as a tab position",
						'data'=>$data,
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>null
					));
				}

				$update["location"] = $data["location"];
				$update["target"] = (string)$data["target"];
				$update["tab_title"] = (string)$data["tab_title"];;
				$update["tab_position"] = (int)$data["tab_position"];;

			}
			// CASE 1C: Changing from slug to slug
			// ------------------------------------------
			else {

				$update["location"] = $data["location"];

				if( $data["target"] ){
					$update["target"] = (string)$data["target"];
				}

				if( $data["tab_title"] ){
					$update["tab_title"] = (string)$data["tab_title"];
				}

				if( $data["tab_position"] ){

					if( !is_int($data["tab_position"]) ){

						throw new FOX_exception( array(
							'numeric'=>10,
							'text'=>"Attempted to use non-int value as a tab position",
							'data'=>$data,
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>null
						));					    
					}

					$update["tab_position"] = (int)$data["tab_position"];
				}

			}

		}

		// CASE 2: Not changing the location
		// =============================================================
		else {

			// CASE 2A: Editing a page
			// ------------------------------------------
			if( $db_record["location"] == "page" ){

				if( $data["target"] ){

					if( !is_int($data["target"]) ){

						throw new FOX_exception( array(
							'numeric'=>11,
							'text'=>"Attempted to use non-int value as a page target",
							'data'=>$data,
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>null
						));
					}

					$update["target"] = (int)$data["target"];
				}


			}
			// CASE 2B: Editing a slug
			// ------------------------------------------
			else {

				if( $data["target"] ){
					$update["target"] = (string)$data["target"];
				}

				if( $data["tab_title"] ){
					$update["tab_title"] = (string)$data["tab_title"];
				}

				if( $data["tab_position"] ){

					if( !is_int($data["tab_position"]) ){

						throw new FOX_exception( array(
							'numeric'=>12,
							'text'=>"Attempted to use non-int value as a tab position",
							'data'=>$data,
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>null
						));						
					}

					$update["tab_position"] = (int)$data["tab_position"];
				}

			}

		}


		// Lock the cache
		// ===========================================================

		try {
			$cache_image = self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>13,
				'text'=>"Error locking cache",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// Update the database
		// ===========================================================

		$args = array(
				array("col"=>"module_id", "op"=>"=", "val"=>$data["module_id"])
		);

		$columns = array("mode"=>"exclude", "col"=>array("module_id") );

		try {
			$rows_changed = $db->runUpdateQuery($struct, $update, $args, $columns);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>14,
				'text'=>"Error writing to database",
				'data'=>array('update'=>$update, 'args'=>$args, 'columns'=>$columns),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		// Update the persistent cache
		// ===========================================================
		
		if(!$rows_changed){
		    
			// If no rows were changed, we can just write-back the 
			// cache image to release our lock
		    
			try {
				self::writeCache($cache_image);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>15,
					'text'=>"Error writing to cache",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}		    
		    
		}
		else {
		    
			// If rows were changed, we have to flush the entire cache
			try {
				self::flushCache();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>16,
					'text'=>"Error flushing cache",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
		}
		

		return (int)$rows_changed;

	}


	/**
	 * Removes one or more locations from the database and cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string/array $locations | Single location as string. Multiple locations as array of strings.
	 * @return int | Exception on failure. Int number of db rows changed on success.
	 */

	public function dropLocation($locations) {

	    
		$db = new FOX_db();
		$struct = self::_struct();

		if( empty($locations) ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Called with invalid locations parameter",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		elseif( !is_array($locations) ){
		    
			// Handle single int as input
			$locations = array($locations);
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
				array("col"=>"location", "op"=>"=", "val"=>$locations)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args, $ctrl=null);
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

		foreach( $locations as $location ){

			if( FOX_sUtil::keyExists($location, $cache_image["data"]) ){

				unset($cache_image["data"][$location]);
				unset($cache_image["locations"][$location]);
			}

		}
		unset($location);
		
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
	 * Removes one or more targets from the database and cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $location | Single location as string.
	 * @param int/string/array targets | Single target as int or string. Multiple targets as array of int/string.
	 * @return int | Exception on failure. Int number of db rows changed on success.
	 */

	public function dropTarget($location, $targets) {


		$db = new FOX_db();
		$struct = self::_struct();

		if( empty($location) || !is_string($location) ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Invalid location parameter",
				'data'=>array($location, $targets),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));		    
		}

		if( empty($targets) ){
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Invalid targets parameter",
				'data'=>array($location, $targets),			    
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		
		if( !is_array($targets) ){
			// Handle single int as input
			$targets = array($targets);
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
				array("col"=>"location", "op"=>"=", "val"=>$location),
				array("col"=>"target", "op"=>"=", "val"=>$targets)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args, $ctrl=null);
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

		foreach( $targets as $target ){

			if( FOX_sUtil::keyExists($target, $cache_image["data"][$location]) ){

				unset($cache_image["data"][$location][$target]);

				// If deleting the target makes its parent location empty, remove
				// the parent location from the cache as well

				if( count($cache_image["data"][$location]) == 0 ){

					unset($cache_image["data"][$location]);
					unset($cache_image["locations"][$location]);
				}
			}

		}
		unset($target);

		
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
	 * @param int/array $module_ids | Single module_id as int. Multiple module_id's as array of int.
	 * @return int | Exception on failure. Int number of db rows changed on success.
	 */

	public function dropModule($module_ids) {


		$db = new FOX_db();
		$struct = self::_struct();

		if( empty($module_ids) ){
		    
			throw new FOX_exception( array(
				'numeric'=>1,
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
				array("col"=>"module_id", "op"=>"=", "val"=>$module_ids)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args, $ctrl=null);
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
		
		// Update the persistent cache
		// ===========================================================
		
		if(!$rows_changed){
		    
			// If no rows were changed, we can just write-back the 
			// cache image to release our lock
		    
			try {
				self::writeCache($cache_image);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Error writing to cache",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}		    
		    
		}
		else {
		    
			// If rows were changed, we have to flush the entire cache
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
		}
		

		return (int)$rows_changed;
		

	}



} // End of class FOX_loc_module



/**
 * Hooks on the plugin's install function, creates database tables and
 * configuration options for the class.
 *
 * @version 1.0
 * @since 1.0
 */

function install_FOX_loc_module(){

	$cls = new FOX_loc_module();
	
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
add_action( 'fox_install', 'install_FOX_loc_module', 2 );


/**
 * Hooks on the plugin's uninstall function. Removes all database tables and
 * configuration options for the class.
 *
 * @version 1.0
 * @since 1.0
 */

function uninstall_FOX_loc_module(){

	$cls = new FOX_loc_module();
	
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
add_action( 'fox_uninstall', 'uninstall_FOX_loc_module', 2 );

?>