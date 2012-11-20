<?php

/**
 * BP-MEDIA MODULE OBJECT TYPES DATA CLASS
 * Stores basic information about object types (such as an album type or media type) created by modules
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package BP-Media
 * @subpackage Base Classes
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

abstract class BPM_objectTypeData_base extends BPM_db_base {

    
    	var $process_id;		    // Unique process id for this thread. Used by BPM_db_base for cache 
					    // locking. Loaded by descendent class.
	
	var $cache;			    // Main cache array for this class
	
	var $mCache;			    // Local copy of memory cache singleton. Used by BPM_db_base for cache 
					    // operations. Loaded by descendent class.	
	

	// ============================================================================================================ //


	/**
	 * Creates a new object type
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $data |
	 *	=> VAL @param int $module_id | id for the module that owns this type.
	 *	=> VAL @param string $type_slug | Slug name for this type. Must be unique to all other slugs.
	 *	=> VAL @param string $name_admin | Admin name for this object type. Max 255 characters.
	 *	=> VAL @param string $txt_admin | Admin description for this object type.
	 *	=> VAL @param string $action_txt | Text shown to user in create object link. Max 255 characters.
	 *	=> VAL @param string $name_user | Name shown to users for this object type. Max 255 characters.
	 *	=> VAL @param string $txt_user | Description shown to users for this object type.
	 *
	 * @return bool/int | Exception on failure. $type_id of the new object type on success.
	 */

	public function addType($data) {

	    
		$db = new BPM_db();

		$columns = array("mode"=>"exclude", "col"=>array("type_id") );
		
		try {
			$db->runInsertQuery($this->_struct(), $data, $columns);
		}
		catch (BPM_exception $child) {
		    
			throw new BPM_exception( array(
				'numeric'=>1,
				'text'=>"Error writing to database",
				'data'=> array('data'=>$data, 'columns'=>$columns),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		// NOTE: The new type is *not* added to the "module_id_types" cache table in this function. This is to ensure
		// that if $cache->["module_id_types"]["module_id"] exists in the table, it contains an array of *all*
		// the type_ids for that module_id. This is more efficient than using a separate "module_id was cached"
		// table as we do in other classes, because new object types will rarely be added to the system.

		$type_id = $db->insert_id;

		// Update the cache
		// =====================================================================
		
		try {
			$cache_image = self::readCache();
		}
		catch (BPM_exception $child) {
		    
			throw new BPM_exception( array(
				'numeric'=>2,
				'text'=>"Cache read error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		// Rebuild the cache image
		
		$cache_image["slug_to_type_id"][$data["module_id"]][$data["type_slug"]] = $type_id;
		$cache_image["keys"][$type_id] = $data;

		try {
			self::writeCache($cache_image);
		}
		catch (BPM_exception $child) {
		    
			throw new BPM_exception( array(
				'numeric'=>3,
				'text'=>"Cache write error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		// Overwrite the class cache
		$this->cache = $cache_image;
		
		return $type_id;

	}


	/**
	 * Fetches a record for one or more object types
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * @param int/array $type_id | Single type_id as int, multiple type_ids as array of int.
	 * @return array | Exception on failure. Array containing row column values on success.
	 */

	public function getType($type_ids) {

	    
		$db = new BPM_db();

		if( !is_array($type_ids) ){

			$single_output = true;
			$type_ids = array($type_ids);
		}
		else {
			$single_output = false;
		}

		$cache_fetch = array();
		$db_fetch = array();
		$result_array = array();
		$cache_loaded = false;

		foreach($type_ids as $type_id){

			// If the type_id is in the cache, use the cached data
			if( BPM_sUtil::keyExists($type_id, $this->cache["keys"])  ){

				$cache_fetch[] = $type_id;

			}
			// Otherwise, load the class cache from the persistent cache and try again
			elseif( $cache_loaded == false ) {

				try {
					self::loadCache();
					$cache_loaded = true;					
				}
				catch (BPM_exception $child) {

					throw new BPM_exception( array(
						'numeric'=>1,
						'text'=>"Cache read error",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}				

				if( BPM_sUtil::keyExists($type_id, $this->cache["keys"]) ){

					$cache_fetch[] = $type_id;
				}
				else {
					$db_fetch[] = $type_id;
				}
			}
			// If the type_id is not in the persistant cache, fetch it from the db
			else {

				$db_fetch[] = $type_id;
			}

		}
		unset($type_id);


		// If there are object types already in the cache, fetch them from the cache
		// ========================================================================
		if( count($cache_fetch) > 0 ){

			foreach( $cache_fetch as $type_id ){

				$result_array[$type_id] = $this->cache["keys"][$type_id];
			}
			unset($type_id);
		}


		// If there are object types not in the cache, fetch them from the database
		// ========================================================================
		if( count($db_fetch) > 0 ){

		    
			$ctrl = array("format"=>"array_key_array", "key_col"=>array("type_id"));
			
			try {
				$db_result = $db->runSelectQueryCol($this->_struct(), "type_id", "=", $db_fetch, $columns=null, $ctrl );
			}
			catch (BPM_exception $child) {

				throw new BPM_exception( array(
					'numeric'=>1,
					'text'=>"Error reading from database",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}			
			
			// Update the cache
			// ===========================
			
			if($db_result){

				try {
					$cache_image = self::readCache();
				}
				catch (BPM_exception $child) {

					throw new BPM_exception( array(
						'numeric'=>2,
						'text'=>"Cache read error",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}

				// Rebuild the cache image
				
				foreach( $db_result as $type_id => $row_data) {

					// Write the db row to the results array
					$result_array[$type_id] = $row_data;

					// Add the db row to the cache
					$cache_image["slug_to_type_id"][$row_data["module_id"]][$row_data["type_slug"]] = $type_id;
					$cache_image["keys"][$type_id] = $row_data;
				}
				unset($type_id, $row_data);

				try {
					self::writeCache($cache_image);
				}
				catch (BPM_exception $child) {

					throw new BPM_exception( array(
						'numeric'=>3,
						'text'=>"Cache write error",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}
				
				// Overwrite the class cache
				$this->cache = $cache_image;

			}

			
		} // ENDOF: if( count($db_fetch) > 0 )

		
		if($single_output){

			// The results array will only contain one key, but its id
			// could be anything. This finds the key's id.
			$keys = array_keys($result_array);

			return $result_array[$keys[0]];
		}
		else {
			return $result_array;
		}

	}


	/**
	 * Returns the module_id that owns an object type_id
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * @param int $type_id | the type's id
	 * @return int | Exception on failure. Int module_id on success.
	 */

	public function getParentModule($type_id) {

	 
		try {
			$type_data = self::getType($type_id);
		}
		catch (BPM_exception $child) {
		    
			throw new BPM_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::getType()",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}
		
		if($type_data){
			return $type_data["module_id"];
		}
		else {
			return false;
		}

	}


	/**
	 * Edits an existing object type
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $data |
	 *	=> VAL @param int $type_id | Unique id for this object type
	 *	=> VAL @param string $type_slug | Slug name for this type. Must be unique to all other slugs.
	 *	=> VAL @param string $name_admin | Admin name for this object type. Max 255 characters.
	 *	=> VAL @param string $txt_admin | Admin description for this object type.
	 *	=> VAL @param string $action_txt | Text shown to user in create object link. Max 255 characters.
	 *	=> VAL @param string $name_user | Name shown to users for this object type. Max 255 characters.
	 *	=> VAL @param string $txt_user | Description shown to users for this object type.
	 *
	 * @return bool | Exception on failure. True on success. False on no change.
	 */

	public function editType($data) {


		$db = new BPM_db();

		if(!$data["type_id"]) {
		    
			throw new BPM_exception( array(
				'numeric'=>1,
				'text'=>"Missing type_id parameter in data array",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		// Nulling this column prevents a user accidentally changing the module_id by passing in array
		// key value for it for even when they're not supposed to.

		unset($data["module_id"]);


		// We have to load the original row from the database before we modify it. This is so we know
		// the original $type_slug value so we can delete it from the cache if the user changes it, and
		// so we build an efficient query that only updates fields that have been changed.

		$ctrl = array("format"=>"row_array");
		
		try {
			$row_data = $db->runSelectQueryCol($this->_struct(), "type_id", "=", $data["type_id"], $columns=null, $ctrl );
		}
		catch (BPM_exception $child) {
		    
			throw new BPM_exception( array(
				'numeric'=>2,
				'text'=>"Error reading from database",			    
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


		// If there are no values that need to be updated, return true to indicate no db rows were changed.
		// Otherwise, run the query. Rules set when the database table was created will prevent duplicate
		// $type_slugs, and other data integrity problems

		if( count($update_data) == 0){
		    
			return true;
		}
		else {
		    
			// Lock the cache
			// ==========================

			try {				
				$cache_image = self::lockCache();
			}
			catch (BPM_exception $child) {

				throw new BPM_exception( array(
					'numeric'=>3,
					'text'=>"Error locking cache",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
			
			// Run the update
			// ==========================
			
			try {
				$rows_changed = $db-> runUpdateQueryCol($this->_struct(), $update_data, "type_id", "=", $data["type_id"], $columns=null);
			}
			catch (BPM_exception $child) {

				throw new BPM_exception( array(
					'numeric'=>4,
					'text'=>"Error writing to database",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}			

		}
		
		// Rebuild the cache image
		// ==========================	
		
		if($rows_changed){

			// If the type's slug value was changed, delete the old entry and create a
			// new one in the slug_to_id cache table
			if( $data["type_slug"] && ($data["type_slug"] != $row_data["type_slug"]) ){

				unset( $cache_image["slug_to_type_id"][$row_data["module_id"]][$row_data["type_slug"]] );
				$cache_image["slug_to_type_id"][$row_data["module_id"]][$data["type_slug"]] = $data["type_id"];
			}

			// Update any changed keys in the main cache table
			foreach($update_data as $col => $val){
				$cache_image["keys"][$data["type_id"]][$col] = $val;
			}
			unset($col, $val);
		}
		
		// Update the persistent cache, releasing our lock
		// ===========================================================

		try {
			self::writeCache($cache_image);
		}
		catch (BPM_exception $child) {

			throw new BPM_exception( array(
				'numeric'=>5,
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
	 * Deletes one or more object types from the system and cache
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * @param int/array $type_id | Single type_id as int. Multiple type_ids as array of ints.
	 * @return bool | Exception on failure. True on success. False on nonexistent.
	 */

	public function dropType($type_id) {

	    
		$db = new BPM_db();

		if(!is_array($type_id)){
			$type_id = array($type_id);
		}

		// Fetch the type_id, type_slug, and module_id for each object type to be
		// deleted. This info is necessary to remove each type's cache entries.
		
		$args = array(
				array("col"=>"type_id", "op"=>"=", "val"=>$type_id)
		);

		$columns = array("mode"=>"include", "col"=>array("type_id", "type_slug", "module_id") );
		$ctrl = array("format"=>"array_key_array", "key_col"=>array("type_id") );

		try {
			$del_types = $db->runSelectQuery($this->_struct(), $args, $columns, $ctrl);
		}
		catch (BPM_exception $child) {
		    
			throw new BPM_exception( array(
				'numeric'=>1,
				'text'=>"Error reading from database",
				'data'=>array('args'=>$args, 'columns'=>$columns, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));		    
		}		

		if($del_types){	    // The if($del_types) prevents foreach() crashing if
				    // none of the object types specified actually exist in the db

			// Lock and load the cache
			// ==========================

			try {				
				$cache_image = self::lockCache();
			}
			catch (BPM_exception $child) {

				throw new BPM_exception( array(
					'numeric'=>2,
					'text'=>"Error locking cache",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
			
			// Run the update
			// ==========================

			try {
				$rows_changed = $db->runDeleteQueryCol($this->_struct(), "type_id", "=", $type_id);
			}
			catch (BPM_exception $child) {

				throw new BPM_exception( array(
					'numeric'=>3,
					'text'=>"Error writing to database",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
		
			// Rebuild the cache image
			// ==========================
			
			foreach($del_types as $type => $data){

				unset( $cache_image["slug_to_type_id"][$data["module_id"]][$data["type_slug"]] );
				unset( $cache_image["module_id_types"][$data["module_id"]] );
				unset( $cache_image["keys"][$type] );
			}
			unset($type, $data);

			
			// Update the persistent cache, releasing our lock
			// ===========================================================

			try {
				self::writeCache($cache_image);
			}
			catch (BPM_exception $child) {

				throw new BPM_exception( array(
					'numeric'=>4,
					'text'=>"Cache write error",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}
			
			// Overwrite the class cache
			$this->cache = $cache_image;

		}
		
		return (bool)$rows_changed;

	}


	/**
	 * Fetches the type_id of a given type_slug, for one or more slugs.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * @param int $module_id | Module id that owns this object type.
	 * @param string $type_slug | Single slug as string. Multiple slugs as array of strings.
	 * @return bool/array | Exception on failure. False on nonexistent. Array "type_slug"=>"type_id" on success.
	 */

	public function slugToTypeId($module_id, $type_slug) {


		$db = new BPM_db();

		if(!is_array($type_slug)){
			$type_slug = array($type_slug);
		}
		
		// Load as many id's as possible from the cache
		// ============================================

		$result = array();
		$missing_slugs = array();
		$persistent_cache_loaded = false;

		foreach($type_slug as $slug){

			// If a requested slug is in the class cache, add its level_id to the the results array
			if( BPM_sUtil::keyExists($slug, $this->cache["slug_to_type_id"][$module_id]) ){

				$result[$slug] = $this->cache["slug_to_type_id"][$module_id][$slug];
			}
			// If the requested slug is not in the class cache, and the class cache hasn't been updated from the
			// persistent cache, update the class cache from the persistent cache and try again
			elseif( !$persistent_cache_loaded){

				try {
					self::loadCache();
					$persistent_cache_loaded = true;					
				}
				catch (BPM_exception $child) {

					throw new BPM_exception( array(
						'numeric'=>1,
						'text'=>"Cache read error",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}

				if( BPM_sUtil::keyExists($slug, $this->cache["slug_to_type_id"][$module_id]) ){

					$result[$slug] = $this->cache["slug_to_type_id"][$module_id][$slug];
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


		// Fetch any missing "type_slug"-"type_id" pairs from the db
		// ===========================================================

		if( count($missing_slugs) > 0){

			$args = array(
					array("col"=>"module_id", "op"=>"=", "val"=>$module_id),
					array("col"=>"type_slug", "op"=>"=", "val"=>$missing_slugs)
			);

			$columns = array("mode"=>"include", "col"=>array("type_slug", "type_id") );
			$ctrl = array("format"=>"array_key_single", "key_col"=>"type_slug", "val_col"=>"type_id");

			try {
				$db_result = $db->runSelectQuery($this->_struct(), $args, $columns, $ctrl);
			}
			catch (BPM_exception $child) {

				throw new BPM_exception( array(
					'numeric'=>2,
					'text'=>"Error reading from database",
					'data'=>array('args'=>$args, 'columns'=>$columns, 'ctrl'=>$ctrl),				    
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}			

			if($db_result){	    // The if($db_result) prevents array_merge()
					    // foreach() from crashing on an empty db result

				$result = array_merge($result, $db_result);

				// Update the cache
				// ==============================

				try {			
					$cache_image = self::readCache();
				}
				catch (BPM_exception $child) {

					throw new BPM_exception( array(
						'numeric'=>3,
						'text'=>"Cache read error",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}

				// Rebuild the cache image
				
				foreach($db_result as $slug => $type_id){
				    
					$cache_image["slug_to_type_id"][$slug] = $type_id;
				}
				unset($slug, $type_id);

				try {
					self::writeCache($cache_image);
				}
				catch (BPM_exception $child) {

					throw new BPM_exception( array(
						'numeric'=>4,
						'text'=>"Cache write error",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}
				
			}
			
		}
		
		// Overwrite the class cache
		$this->cache = $cache_image;

		if( count($result) > 0){
		    
			return $result;
		}
		else{
			return false;
		}

	}


	/**
	 * Returns all type_ids owned by a module
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * @param int $module_id| Single module_id as int.
	 * @return array | Exception on failure. False on nonexistent. Array of type_ids on success.
	 */

	public function getTypes($module_id) {


		$db = new BPM_db()
		;
		$result = array();

		// If the module_id doesn't exist in the class cache array, fetch it from the persistent cache
		if( !BPM_sUtil::keyExists($module_id, $this->cache["module_id_types"]) ){

			try {
				self::loadCache();
			}
			catch (BPM_exception $child) {

				throw new BPM_exception( array(
					'numeric'=>1,
					'text'=>"Cache read error",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));		    
			}

			// If the module_id doesn't exist in the persistent cache, load it from the db
			if( !BPM_sUtil::keyExists($module_id, $this->cache["module_id_types"]) ){

				$columns = array("mode"=>"include", "col"=>array("type_id") );
				$ctrl = array("format"=>"col");
				
				try {
					$db_result = $db->runSelectQueryCol($this->_struct(), "module_id", "=", $module_id, $columns, $ctrl );
				}
				catch (BPM_exception $child) {

					throw new BPM_exception( array(
						'numeric'=>2,
						'text'=>"Error reading from database",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));		    
				}				

				// Update the cache
				// ==============================
				
				if($db_result){

					try {
						$cache_image = self::readCache();
					}
					catch (BPM_exception $child) {

						throw new BPM_exception( array(
							'numeric'=>3,
							'text'=>"Cache read error",
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));		    
					}

					// Rebuild the cache image
					
					foreach($db_result as $type_id){

						$result[] = $type_id;
						$cache_image["module_id_types"][$module_id][$type_id] = true;
					}
					unset($type_id);

					try {
						self::writeCache($cache_image);
					}
					catch (BPM_exception $child) {

						throw new BPM_exception( array(
							'numeric'=>4,
							'text'=>"Cache write error",
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));		    
					}
					
					// Overwrite the class cache
					$this->cache = $cache_image;

				}
				else{
					// The module_id doesn't exist
					return false;
				}
			}

		}

		// Build the result array
		$type_ids = $this->cache["module_id_types"][$module_id];
		$result = array_keys($type_ids);

		return $result;

	}



} // End of class BPM_moduleTypeData_base

?>