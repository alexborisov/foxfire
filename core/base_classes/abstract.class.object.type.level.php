<?php

/**
 * FOXFIRE OBJECT TYPES LEVELS CLASS
 * Stores information about object type levels, issues each level a unique level_id, assures each level
 * within an object type has a unique rank, and determines which key_id is required to access a level.
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Base Classes
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

abstract class FOX_objectTypeLevel_base extends FOX_db_base {


	var $cache;			    // Main cache array for this class
	
	var $mCache;			    // Local copy of memory cache singleton. Used by FOX_db_base for cache 
					    // operations. Loaded by descendent class.		
	

	// ============================================================================================================ //


	/**
	 * Creates a new access level within an object type.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param array $data |
	 *	=> VAL @param int $module_id | Module that owns the object type that owns the access level.
	 *	=> VAL @param int $type_id | Object type that owns the access level.
	 *	=> VAL @param string $level_slug | Slug name for this level. Must be unique to all other slugs.
	 * 	=> VAL @param string $level_name | Name of the level. Max 64 characters.
	 *	=> VAL @param string $level_desc | Description of the level. Max 255 characters.
	 *	=> VAL @param int $rank | Rank of this level within the object type. 1-255 where 1 is the highest.
	 *	=> VAL @param int $key_id | Key id required to access to this level
	 *
	 * @return int | Exception on failure. $level_id of the new level on success.
	 */

	public function addLevel($data) {


		$db = new FOX_db();

		// Check that the rank doesn't already exist
		// ======================================================

		$args = array(
				array("col"=>"module_id", "op"=>"=", "val"=>$data["module_id"]),
				array("col"=>"type_id", "op"=>"=", "val"=>$data["type_id"]),
				array("col"=>"rank", "op"=>"=", "val"=>$data["rank"])
		);

		$ctrl = array("format"=>"var", "count"=>true );

		try {
			$already_exists = $db->runSelectQuery($this->_struct(), $args, $columns=null, $ctrl );
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error reading from database",
				'data'=>array('args'=>$args,'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		if($already_exists){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Object type already exists",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		else {
			
			// Add to the database
			// ======================================================

			try {
				$db->runInsertQuery($this->_struct(), $data, $columns=null);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error writing to database",
					'data'=>$data,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
		
			// NOTE: The new type is *not* added to the "type_levels" cache table in this function. This is to ensure
			// that if $cache->["type_levels"]["type_id"] exists in the table, it contains an array of *all*
			// the level_ids for that type_id. This is more efficient than using a separate "type_id was cached"
			// table as we do in other classes, because new type levels will rarely be added to the system.
			
			// Update the cache
			// ==============================
			
			$level_id = $db->insert_id;
			
			try {
				$cache_image = self::readCache();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Cache read error",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}

			// Rebuild the cache image
			
			$cache_image["slug_to_level_id"][$data["module_id"]][$data["type_id"]][$data["level_slug"]] = $level_id;
			$cache_image["keys"][$level_id] = $data;

			try {
				self::writeCache($cache_image);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Cache write error",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
			
			// Overwrite the class cache
			$this->cache = $cache_image;

			return (int)$level_id;

		}

	}

	
	/**
	 * Fetches data for an access level within an object type.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int $level_id | Unique id for this access level.
	 * @return bool/array | Exception on failure. False on nonexitent. Level data array  on success.
	 */

	public function getLevel($level_id) {


		$db = new FOX_db();

		// If the key doesn't exist in the class cache array, fetch it from the persistent cache
		if( !FOX_sUtil::keyExists($level_id, $this->cache["keys"]) ){

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

			// If the requested key doesn't exist in the persistent cache, load it from the db
			if( !FOX_sUtil::keyExists($level_id, $this->cache["keys"]) ){

				// Otherwise, fetch the requested level_id from the db
				$ctrl = array("format"=>"row_array");
				$columns = array("mode"=>"exclude", "col"=>array("level_id") );

				try {
					$row_data = $db->runSelectQueryCol($this->_struct(), "level_id", "=", $level_id, $columns, $ctrl );
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Error reading from database",
						'data'=>array('columns'=>$columns, 'ctrl'=>$ctrl),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}				

				// Update the cache
				// ==============================
				
				if($row_data){

					try {
						$cache_image = self::readCache();
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>3,
							'text'=>"Cache read error",
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));		    
					}

					// Rebuild the cache image
					
					$cache_image["slug_to_level_id"][$row_data["module_id"]][$row_data["type_id"]][$row_data["level_slug"]] = $level_id;
					$cache_image["keys"][$level_id] = $row_data;

					try {
						self::writeCache($cache_image);
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>4,
							'text'=>"Cache write error",
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));		    
					}
					
					// Overwrite the class cache
					$this->cache = $cache_image;
					
				}
				else {
					// The requested level_id doesn't exist in the db
					return false;
				}
			}

		}

		return $this->cache["keys"][$level_id];

	}

	
	/**
	 * Updates the ranks of all levels within an object type
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | id of module that owns the object type
	 * @param int $type_id | id of object type that owns the level id's
	 * @param array $ranks | array of ranks to apply to the object type's access levels in the form "type_id"=>"rank"
	 *
	 * @return bool | Exception on failure. False on no change. True on success.
	 */

	public function setRanks($module_id, $type_id, $ranks) {


		$db = new FOX_db();
		$result_array = array();


		// Check that the $ranks array has no missing ranks
		// ========================================================================
		
		asort($ranks);
		$rank_check = 1;

		foreach($ranks as $rank){

			if($rank != $rank_check){
			    
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Missing rank key",
					'data'=>array('module_id'=>$module_id, 'type_id'=>$type_id, 'ranks'=>$ranks),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

			$rank_check++;
		}
		unset($rank, $rank_check);


		// If *all* of the levels for a type_id have been cached, there will be an
		// array containing it's level_id's in $this->cache["type_levels"][$type_id],
		// if it exists, we can use the cached data instead of querying the database
		// ========================================================================
		
		if( FOX_sUtil::keyExists($this->cache["type_levels"], $type_id) ){

			$levels = $this->cache["type_levels"][$type_id];

			foreach( $levels as $level_id ){

				$result_array[$level_id] = $this->cache["keys"][$level_id];
			}
			unset($levels, $level_id);

		}
		// If not, refresh the cache and try again
		// ========================================================================
		else {

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

			if( FOX_sUtil::keyExists($this->cache["type_levels"], $type_id) ){

				$levels = $this->cache["type_levels"][$type_id];

				foreach( $levels as $level_id ){

					$result_array[$level_id] = $this->cache["keys"][$level_id];
				}
				unset($levels, $level_id);

			}
			// If its still not there, load all the type_id's levels from the database
			// ========================================================================
			else {

				$args = array(
						array("col"=>"module_id", "op"=>"=", "val"=>$module_id),
						array("col"=>"type_id", "op"=>"=", "val"=>$type_id)
				);

				$ctrl = array("format"=>"array_key_array", "key_col"=>array("level_id"));

				try {
					$db_result = $db->runSelectQuery($this->_struct(), $args, $columns=null, $ctrl );
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>3,
						'text'=>"Error reading from database",
						'data'=>array('args'=>$args, 'ctrl'=>$ctrl),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}				


				if($db_result){

					foreach( $db_result as $level_id => $row_data){

						$result_array[$level_id] = $row_data;
					}
					unset($level_id, $row_data);
				}
			}
		}

		// Check the number of keys in the $ranks array matches the number of
		// levels that the object type has
		// ========================================================================
		
		$ranks_count = count($ranks);
		$db_count = count($result_array);

		if( $ranks_count > $db_count ){

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Rank array has more keys than database",
				'data'=>array('ranks'=>$ranks, 'result_array'=>$result_array),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		elseif( $ranks_count < $db_count){

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Rank array has less keys than database",
				'data'=>array('ranks'=>$ranks, 'result_array'=>$result_array),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}


		// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		try {
			$db->beginTransaction();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Couldn't initiate transaction",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}

		// Lock the cache
		// ===============================
		
		try {
			$cache_image = self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>7,
				'text'=>"Cache read error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}	

		$rows_changed = 0;

		foreach( $result_array as $level_id => $row_data){

			if( $ranks[$level_id] != $row_data["rank"] ){

				$row_data["rank"] = $ranks[$level_id];

				$args = array(
					    array("col"=>"module_id", "op"=>"=", "val"=>$module_id),
					    array("col"=>"type_id", "op"=>"=", "val"=>$type_id),
					    array("col"=>"level_id", "op"=>"=", "val"=>$level_id)
				);

				try {
					$rows_changed += (int)$db->runUpdateQuery($this->_struct(), $row_data, $args, $columns=null);
				}
				catch (FOX_exception $child) {				

					try {
						$db->rollbackTransaction();
					}
					catch (FOX_exception $child_2) {

						throw new FOX_exception( array(
							'numeric'=>8,
							'text'=>"Error writing to database. Rollback failed.",
							'data'=>array('rollback_exception'=>$child_2, 'args'=>$args, 
								      'columns'=>$columns, 'ctrl'=>$ctrl),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));		    
					}
		
					throw new FOX_exception( array(
						'numeric'=>9,
						'text'=>"Error writing to database. Rollback successful.",
						'data'=>array('row_data'=>$row_data, 'args'=>$args),
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}				

			}

		}
		unset($level_id, $row_data);

		
		try {
			$db->commitTransaction();
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>10,
				'text'=>"Couldn't commit transaction",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
		
		
		// Rebuild the cache image
		// ======================================

		foreach( $result_array as $level_id => $row_data){

			if( $ranks[$level_id] != $row_data["rank"] ){

				$row_data["rank"] = $ranks[$level_id];

				$cache_image["slug_to_level_id"][$row_data["module_id"]][$row_data["type_id"]][$row_data["level_slug"]] = $level_id;
				$cache_image["keys"][$level_id] = $row_data;
			}
		}
		unset($level_id, $row_data);

		$cache_image["type_levels"][$type_id] = array_keys($ranks);
		
		
		// Update the persistent cache, releasing our lock
		// ================================================
		
		try {
			self::writeCache($cache_image);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>11,
				'text'=>"Cache write error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		// Overwrite the class cache
		$this->cache = $cache_image;

		return (bool)$rows_changed;

	}


	/**
	 * Edits an existing level
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param array $data |
	 *	=> VAL @param int $level_id | Unique id for this access level.
	 *	=> VAL @param string $level_slug | Slug name for this level. Must be unique to all other slugs.
	 * 	=> VAL @param string $level_name | Name of the level. Max 64 characters.
	 *	=> VAL @param string $level_desc | Description of the level. Max 255 characters.
	 *	=> VAL @param int $rank | Rank of this level within the object type. 1-255 where 1 is the highest.
	 *	=> VAL @param int $key_id | Key id required to access to this level
	 *
	 * @return int | Exception on failure. False on no change. True on success.
	 */

	public function editLevel($data) {

	    
		$db = new FOX_db();

		if( !$data["level_id"] ) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Missing level_id in data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		// Nulling these columns prevents a user accidentally changing the module_id and
		// type_id values by passing in array keys for them when they're not supposed to.

		unset($data["module_id"]);
		unset($data["type_id"]);


		// We have to load the original row from the database before we modify it. This is so we know
		// the original $level_slug value so we can delete it from the cache if the user changes it, and
		// so we can build an efficient query that only updates fields that have been changed.

		$ctrl = array("format"=>"row_array");
		
		try {
			$row_data = $db->runSelectQueryCol($this->_struct(), "level_id", "=", $data["level_id"], $columns=null, $ctrl );
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error reading from database",
				'data'=>array('level_id'=>$data["level_id"], 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		// Compare the existing column values to the new column values in the $data array, and add
		// any columns with updated values to the $update_data array.

		$update_data = array();

		foreach($data as $col => $val){

			if( $row_data[$col] != $val ){
				$update_data[$col] = $val;
			}
		}
		unset($col, $val);


		// If rank is being updated, check that the new rank doesn't already exist
		// =========================================================================

		if($update_data["rank"]){

			$args = array(
					array("col"=>"module_id", "op"=>"=", "val"=>$row_data["module_id"]),
					array("col"=>"type_id", "op"=>"=", "val"=>$row_data["type_id"]),
					array("col"=>"rank", "op"=>"=", "val"=>$data["rank"])
			);

			$ctrl = array( "format"=>"var", "count"=>true );

			try {
				$already_exists = $db->runSelectQuery($this->_struct(), $args, $columns=null, $ctrl );
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error reading from database",
					'data'=>array('args'=>$args,'ctrl'=>$ctrl),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}			

			if($already_exists){
			    
				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Rank you are trying to change target item to already exists",
					'data'=>array('data'=>$data, 'faulting_rank'=>$update_data["rank"]),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}

		}

		// If there are no values that need to be updated, return false to indicate no db rows were changed.
		// Otherwise, run the query. Rules set when the database table was created will prevent duplicate
		// $level_slugs and duplicate $rank values within a $level_id, and other data integrity problems.

		if( count($update_data) == 0){	
		    
			return false;
		}


		// Lock the cache
		// ==========================

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

		// Run the update
		// ==========================

		try {
			$rows_changed = $db-> runUpdateQueryCol($this->_struct(), $update_data, "level_id", "=", $data["level_id"], $columns=null);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error writing to database",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}			

		// Rebuild the cache image
		// ==========================		

		// If the level's $slug value was changed, delete the old entry and create a
		// new one in the slug_to_id cache table

		if( $update_data["level_slug"] && ($update_data["level_slug"] != $row_data["level_slug"]) ){

			unset( $cache_image["slug_to_level_id"][$row_data["module_id"]][$row_data["type_id"]][$row_data["level_slug"]] );
			$cache_image["slug_to_level_id"][$row_data["module_id"]][$row_data["type_id"]][$data["level_slug"]] = $row_data["level_id"];
		}

		// If the level's $rank value was changed, delete the old entry and create a
		// new entry in the type_levels cache table

		if( $update_data["rank"] && ($update_data["rank"] != $row_data["rank"]) ){

			unset( $cache_image["type_levels"][$row_data["type_id"]][$row_data["rank"]] );
			$cache_image["type_levels"][$row_data["type_id"]][$data["rank"]] = true;
		}

		// Update any changed keys in the main cache table

		foreach($update_data as $col => $val){

			$cache_image["keys"][$data["level_id"]][$col] = $val;
		}
		unset($col, $val);

		
		// Update the persistent cache, releasing our lock
		// ===========================================================

		try {
			self::writeCache($cache_image);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>7,
				'text'=>"Cache write error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		// Overwrite the class cache
		$this->cache = $cache_image;
			
		return (bool)$rows_changed;
		
	}


	/**
	 * Deletes one or more object type levels
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/array $level_id | Single level_id as int. Multiple level_ids as array of ints.
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function dropLevel($level_id) {


		$db = new FOX_db();

		if(!is_array($level_id)){
			$level_id = array($level_id);
		}

		// Fetch the level_id, level_slug, and type_id for each level to be deleted
		// **This info is necessary to remove each level's cache entries**
		// =======================================================================
		
		$args = array(
				array("col"=>"level_id", "op"=>"=", "val"=>$level_id)
		);

		$columns = array("mode"=>"include", "col"=>array("level_id", "level_slug", "type_id") );
		$ctrl = array("format"=>"array_key_array", "key_col"=>array("level_id") );

		try {
			$del_types = $db->runSelectQuery($this->_struct(), $args, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error reading from database",
				'data'=>array('args'=>$args, 'columns'=>$columns, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
			
		if(!$del_types){  
		    
			// No items exist in the db
			return 0;
		}

		    
		// Lock the cache
		// ==========================

		try {
			$cache_image = self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Cache read error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}

		
		// Clear items from the db
		// ==========================	

		try {
			$rows_changed = $db->runDeleteQueryCol($this->_struct(), "level_id", "=", $level_id);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error while deleting from database",
				'data'=>array("level_id"=>$level_id),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}

		// Rebuild the cache image
		// ==========================	

		foreach($del_types as $level => $data){

			unset( $cache_image["slug_to_level_id"][$data["module_id"]][$data["type_id"]][$data["level_slug"]] );
			unset( $cache_image["type_levels"][$data["type_id"]] );
			unset( $cache_image["keys"][$level] );
		}
		unset($level, $data);


		// Update the persistent cache, releasing our lock
		// ===========================================================

		try {
			self::writeCache($cache_image);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Cache write error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}

		// Overwrite the class cache
		$this->cache = $cache_image;		
		
		return (int)$rows_changed;
			
	}

	
	/**
	 * Deletes one or more object types
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/array $type_id | Single type_id as int. Multiple type_ids as array of ints.
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function dropType($type_id) {


		$db = new FOX_db();

		if(!is_array($type_id)){
			$type_id = array($type_id);
		}

		// Fetch the level_id, level_slug, and type_id for each level_id owned 
		// by  the type_id to be deleted. 
		// **This info is necessary to remove each level's cache entries**
		// =====================================================================
		
		$args = array(
				array("col"=>"type_id", "op"=>"=", "val"=>$type_id)
		);

		$columns = array("mode"=>"include", "col"=>array("level_id", "level_slug", "type_id") );
		
		$ctrl = array("format"=>"array_key_array", "key_col"=>array("level_id") );

		
		try {
			$del_types = $db->runSelectQuery($this->_struct(), $args, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error reading from database",
				'data'=>array('args'=>$args, 'columns'=>$columns, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		if(!$del_types){  
		    
			// No items exist in the db
			return 0;
		}

		// Lock the cache
		// ==========================

		try {
			$cache_image = self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Cache read error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		// Clear items from the db
		// ==========================		

		try {
			$rows_changed = $db->runDeleteQueryCol($this->_struct(), "type_id", "=", $type_id);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error while deleting from database",
				'data'=>array("type_id"=>$type_id),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		// Rebuild the cache image
		// ==========================	

		// Remove the deleted types
		foreach($del_types as $level => $data){

			unset( $cache_image["slug_to_level_id"][$data["module_id"]][$data["type_id"]][$data["level_slug"]] );
			unset( $cache_image["type_levels"][$data["type_id"]] );
			unset( $cache_image["keys"][$level] );
		}
		unset($level, $data);
		
		// Update the persistent cache, releasing our lock
		// ===========================================================

		try {
			self::writeCache($cache_image);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Cache write error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}

		// Overwrite the class cache
		$this->cache = $cache_image;		
		
		return (int)$rows_changed;

	}

	
	/**
	 * Gets all levels for an object type
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int/array $type_ids | Single type_id as int. Multiple type_ids as array of ints.
	 * @return array | Exception on failure. Data array on success.
	 */

	public function getType($type_ids) {


		$db = new FOX_db();
		$result = array();

		if(!is_array($type_ids)){
			$type_ids = array($type_ids);
		}

		if( $this->cache["type_levels"]){

			$in_cache = array_keys( $this->cache["type_levels"] );
		}
		else {
			$in_cache = array();
		}

		$db_fetch = array_diff($type_ids, $in_cache);
		$cache_fetch = array_diff($type_ids, $db_fetch);


		// If there are object types already in the cache, fetch them from the cache
		// ========================================================================
		if( count($cache_fetch) > 0 ){

			foreach( $cache_fetch as $type_id){

				$level_ids = $this->cache["type_levels"][$type_id];

				foreach( $level_ids as $level_id){

					$result[$type_id][$level_id] = $this->cache["keys"][$level_id];
				}
				unset($level_id);

			}
			unset($type_id, $level_ids);

		}

		// If there are object types not in the cache, fetch them from the database
		// ========================================================================
		if( count($db_fetch) > 0 ){

			$args = array(
					array("col"=>"type_id", "op"=>"=", "val"=>$db_fetch)
			);

			$ctrl = array("format"=>"array_key_array", "key_col"=>array("type_id", "level_id")  );

			try {
				$db_result = $db->runSelectQuery($this->_struct(), $args, $columns=null, $ctrl);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error reading from database",
					'data'=>array('args'=>$args,'ctrl'=>$ctrl),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}			
			

			// Update the cache
			// ==============================
			
			if($db_result){

				// Get cache image from persistent cache
			    
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

				// Rebuild the cache image, build $result array
				
				foreach( $db_result as $type_id => $levels ){

					$result[$type_id] = $levels;

					$cache_image["type_levels"][$type_id] = array_keys($levels);

					foreach( $levels as $level_id => $data ){

						$cache_image["slug_to_level_id"][$data["module_id"]][$data["type_id"]][$data["level_slug"]] = $level_id;
						$cache_image["keys"][$level_id] = $data;

					}
					unset($level_id, $data);

				}
				unset($type_id, $levels);

				// Update the persistent cache
				
				try {
					self::writeCache($cache_image);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>4,
						'text'=>"Cache write error",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}

				// Overwrite the class cache
				$this->cache = $cache_image;
				
			}

		}

		return $result;
		
	}


	/**
	 * Deletes one or more modules
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int/array $module_id | Single module_id as int. Multiple module_ids as array of ints.
	 * @return int | Exception on failure. Number of affected db rows on success.
	 */

	public function dropModule($module_id) {


		$db = new FOX_db();

		if(!is_array($module_id)){
			$module_id = array($module_id);
		}

		// Fetch the level_id, level_slug, and type_id for each level_id owned 
		// by the module_id to be deleted. 
		// **This info is necessary to remove each level's cache entries**
		// ========================================================================
		
		$args = array(
				array("col"=>"module_id", "op"=>"=", "val"=>$module_id)
		);

		$columns = array("mode"=>"include", "col"=>array("level_id", "level_slug", "type_id") );
		
		$ctrl = array("format"=>"array_key_array", "key_col"=>array("level_id") );

		try {
			$del_types = $db->runSelectQuery($this->_struct(), $args, $columns, $ctrl);
		}
		catch (FOX_exception $child) {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error reading from database",
				'data'=>array('args'=>$args, 'columns'=>$columns, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		if(!$del_types){  
		    
			// No items exist in the db
			return 0;
		}

		// Lock the cache
		// ==========================

		try {
			$cache_image = self::lockCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Cache read error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		
		
		
		// Clear items from the db
		// ==========================	

		try {
			$rows_changed = $db->runDeleteQueryCol($this->_struct(), "module_id", "=", $module_id);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error while deleting from database",
				'data'=>array("module_id"=>$module_id),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
				
		// Rebuild the cache image
		// ==========================
		
		foreach($del_types as $level => $data){

			unset( $cache_image["slug_to_level_id"][$data["module_id"]][$data["type_id"]][$data["level_slug"]] );
			unset( $cache_image["type_levels"][$data["type_id"]] );
			unset( $cache_image["keys"][$level] );
		}
		unset($level, $data);

		
		// Update the persistent cache, releasing our lock
		// ===========================================================

		try {
			self::writeCache($cache_image);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Cache write error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}

		// Overwrite the class cache
		$this->cache = $cache_image;		
		
		return (int)$rows_changed;

	}


	/**
	 * Fetches the level_id of one or more slugs
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | module_id that owns the object type_id
	 * @param int $type_id | Object type_id that owns the level_id
	 * @param string $level_slug | Single level_slug as string. Multiple slugs as array of strings.
	 * 
	 * @return array | Exception on failure. False on nonexistent. Array "level_slug"=>"level_id" on success
	 */

	public function slugToTypeId($module_id, $type_id, $level_slug) {


		$db = new FOX_db();

		if(!is_array($level_slug)){
			$level_slug = array($level_slug);
		}

		// Load as many id's as possible from the cache
		// ============================================

		$result = array();
		$missing_slugs = array();
		$persistent_cache_loaded = false;
		
		foreach($level_slug as $slug){

			// If a requested slug is in the class cache, add its level_id to the the results array
			if( FOX_sUtil::keyExists($slug, $this->cache["slug_to_level_id"][$module_id][$type_id]) ){

				$result[$slug] = $this->cache["slug_to_level_id"][$module_id][$type_id][$slug];
			}
			// If the requested slug is not in the class cache, and the class cache hasn't been updated from the
			// persistent cache, update the class cache from the persistent cache and try again
			elseif( !$persistent_cache_loaded){

				try {
					self::loadCache();
					$persistent_cache_loaded = true;
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>1,
						'text'=>"Cache read error",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}
				
				if( FOX_sUtil::keyExists($slug, $this->cache["slug_to_level_id"][$module_id][$type_id]) ){

					$result[$slug] = $this->cache["slug_to_level_id"][$module_id][$type_id][$slug];
				}
				else {
					$missing_slugs[] = $slug;
				}
			}
			// If the requested slug is not in the class cache, and the class cache has been updated from the
			// persistent cache, we need to fetch the slug from the db
			else {
				$missing_slugs[] = $slug;
			}
		}
		unset($slug);


		// Fetch any missing "level_slug"-"level_id" pairs from the db
		// ===========================================================

		if( count($missing_slugs) > 0){

			$args = array(
					array("col"=>"module_id", "op"=>"=", "val"=>$module_id),
					array("col"=>"type_id", "op"=>"=", "val"=>$type_id),
					array("col"=>"level_slug", "op"=>"=", "val"=>$missing_slugs)
			);

			$columns = array("mode"=>"include", "col"=>array("level_slug", "level_id") );
			
			$ctrl = array("format"=>"array_key_single", "key_col"=>"level_slug", "val_col"=>"level_id");

			try {
				$db_result = $db->runSelectQuery($this->_struct(), $args, $columns, $ctrl);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Error reading from database",
					'data'=>array('args'=>$args, 'columns'=>$columns, 'ctrl'=>$ctrl),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
		
			// Update the cache
			// ==============================
			
			if($db_result){		
						

				$result = array_merge($result, $db_result);

				try {
					$cache_image = self::readCache();
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>3,
						'text'=>"Cache read error",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}

				// Rebuild cache image
				
				foreach($db_result as $slug => $level_id){

					$cache_image["slug_to_level_id"][$module_id][$type_id][$slug] = $level_id;
				}
				unset($slug, $level_id);

				try {
					self::writeCache($cache_image);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>4,
						'text'=>"Cache write error",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}

				// Overwrite the class cache
				$this->cache = $cache_image;
				
			}			

		}
		

		if( count($result) >= 1){
			return $result;
		}
		else{
			return false;
		}

	}


} // End of class FOX_objectTypeLevel

?>