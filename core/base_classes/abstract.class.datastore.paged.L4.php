<?php

/**
 * FOXFIRE L4 PAGED ABSTRACT DATASTORE CLASS
 * Implements a highly efficient 4th order paged datastore
 *
 * FEATURES
 * --------------------------------------
 *  -> Transient cache support
 *  -> Persistent cache support
 *  -> Progressive cache loading
 *  -> SQL transaction support
 *  -> Fully atomic operations
 *  -> Advanced error handling
 *  -> Multi-thread safe
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

abstract class FOX_dataStore_paged_L4_base extends FOX_db_base {


    	var $process_id;		    // Unique process id for this thread. Used by FOX_db_base for cache 
					    // locking. Loaded by descendent class.
	
	var $cache;			    // Main cache array for this class
	
	var $mCache;			    // Local copy of memory cache singleton. Used by FOX_db_base for cache 
					    // operations. Loaded by descendent class.		


	/* ================================================================================================================
	 *	Cache Strategy: "paged"
	 *
	 *	=> ARR array $cache | Main cache array
	 *
 	 *	    => ARR array $module_id | Module cache page    --------------------------------------------
	 *
	 *		=> ARR @param array $keys | Main datastore
	 *		    => ARR string '' | type_id
	 *			=> ARR string | branch_id
	 *			    => KEY string | key_id
	 *				=> VAL mixed | serialized key data
	 *
	 *		=> VAL bool $all_cached | True if cache page has authority (all rows loaded from db)
	 *
	 *		=> ARR array $type_id | Type dictionary
	 *		    => KEY string '' | type_id
	 *			=> VAL bool | True if cached. False if not.
	 *
	 *		=> ARR array $branch_id | Branch dictionary
	 *		    => ARR string '' | type_id
	 *			=> KEY string | branch_id
	 *			    => VAL bool | True if cached. False if not.
	 *
	 *	    -------------------------------------------------------------------------------------------
	 *
	 * */


	/**
	 * Loads key, type_id, REMOVE, or all data for one or more modules from the db
	 * into the key cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/array $module_id | Single module id as int. Multiple module id as array of int.
	 * @param string/array $type_id | Single type_id name as string. Multiple type_id id as array of string.
	 * @param string/array $branch_id | Single type as string. Multiple types as array of strings.
	 * @param int/array $key_id | Single key_id as int. Multiple key_id's as array of ints.
	 * @param bool $skip_load | If set true, the function will not update the class cache array from
	 *			    the persistent cache before adding data from a db call and saving it
	 *			    back to the persistent cache.
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function load($module_id, $type_id=null, $branch_id=null, $key_id=null, $skip_load=false){


		$db = new FOX_db();
		$struct = $this->_struct();

		// Build and run query
		// ===========================================================

		$args = array(
				array("col"=>"module_id", "op"=>"=", "val"=>$module_id)
		);

		if($type_id){

			$args[] = array("col"=>"type_id", "op"=>"=", "val"=>$type_id);

			if($branch_id){

				$args[] = array("col"=>"branch_id", "op"=>"=", "val"=>$branch_id);

				if($key_id){
					$args[] = array("col"=>"key_id", "op"=>"=", "val"=>$key_id);
				}
			}
		}


		$ctrl = array("format"=>"array_key_array", "key_col"=>array("module_id","type_id", "branch_id", "key_id") );
		$columns = null;

		try {
			$db_result = $db->runSelectQuery($struct, $args, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
					'text'=>"Error loading requested keys from database",
					'data'=>array (
							"module_id"=>$module_id,
							"type_id"=>$type_id,
							"branch_id"=>$branch_id,
							"key_id"=>$key_id,
							"skip_load"=>$skip_load
					),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		if(!$db_result){

			// If the query returned zero results, no further work needs to be done
			return true;
		}


		// Load the persistent cache records for all returned module_id's into
		// the temp class cache
		// =====================================================================

		$module_ids = array_keys($db_result);

		if($skip_load){

			foreach($module_ids as $_module_id){

				$update_cache[$_module_id] = $this->cache[$_module_id];
			}
			unset($_module_id);
		}
		else {
			try {
				$update_cache = self::readCachePage($module_ids);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Cache get error",
					'data'=>array("module_ids"=>array_keys($db_result)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}
		}
		unset($module_ids);


		// Overwrite the temp cache records with the data fetched in the db query,
		// while setting the correct heirarchical cache flags
		// =====================================================================

		$ancestor_cached = false;

		foreach( $db_result as $_module_id => $type_ids ){

			if(!$type_id){

				// Indicate all data for the module_id is cached
				$update_cache[$_module_id]["all_cached"] = true;

				// Clear descendent dictionaries, because they're redundant
				unset($update_cache[$_module_id]["type_id"]);
				unset($update_cache[$_module_id]["branch_id"]);

				// Prevent descendents from loading their dictionaries
				$ancestor_cached = true;
			}

			foreach( $type_ids as $_type_id => $branch_ids ){

				if(!$branch_id && !$ancestor_cached){

					$update_cache[$_module_id]["type_id"][$_type_id] = true;
					unset($update_cache[$_module_id]["branch_id"]);

					$ancestor_cached = true;
				}

				foreach( $branch_ids as $_branch_id => $key_ids ){

					if(!$key_id && !$ancestor_cached){

						$update_cache[$_module_id]["branch_id"][$_type_id][$_branch_id] = true;
					}

					foreach( $key_ids as $_key_id => $val ){

						$update_cache[$_module_id]["keys"][$_type_id][$_branch_id][$_key_id] = $val["key_id"];
					}
					unset($_key_id, $val);

				}
				unset($_branch_id, $key_ids);

			}
			unset($_type_id, $branch_ids);

		}
		unset($_module_id, $type_ids);

		// Clear empty walks from dictionary arrays
		$update_cache["branch_id"] = FOX_sUtil::arrayPrune($update_cache["branch_id"], 1);


		// Overwrite the persistent cache records with the temp class cache array
		// =====================================================================

		try {
			self::writeCachePage($update_cache);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Cache set error",
				'data'=>$update_cache,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// Write the temp class cache array items to the class cache array
		foreach($update_cache as $update_module_id => $module_data){

			$this->cache[$update_module_id] = $module_data;
		}
		unset($update_module_id, $module_data);


		return true;

	}


	/**
	 * Loads all data for one or more $module_id's into the data cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/array $module_id | Single module id as int. Multiple module id's as array of ints.
	 * @return bool | Exception on failure. True on success.
	 */

	public function loadModule($module_id){


		try {
			$result = self::load($module_id);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::load()",
				'data'=> $module_id,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		return $result;

	}


	/**
	 * Loads one or more type_ids for a single module_id into the data cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | Single module id
	 * @param string/array $type_id | Single type_id name as string. Multiple type_id names as array of strings.
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function loadType($module_id, $type_id){


		try {
			$result = self::load($module_id, $type_id);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::load()",
				'data'=> array('module_id'=>$module_id, 'type_id'=>$type_id),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		return $result;

	}


	/**
	 * Loads one or more key types for a single type_id and module_id into the data cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | Single module id
	 * @param string $type_id | Single type_id name
	 * @param string/array $branch_id | Single key type as string. Multiple key types as array of strings.
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function loadBranch($module_id, $type_id, $branch_id){


		try {
			$result = self::load($module_id, $type_id, $branch_id);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::load()",
				'data'=> array('module_id'=>$module_id, 'type_id'=>$type_id,
						'branch_id'=>$branch_id),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		return $result;

	}


	/**
	 * Loads one or more keys for a single branch_id, type_id, and module_id into the data cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | Single module id
	 * @param string $type_id | Single type_id name
	 * @param string $branch_id | Single key type
	 * @param int/array $key_id | Single key_id type as int. Multiple key_ids as array of ints.
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function loadKey($module_id, $type_id, $branch_id, $key_id){


		try {
			$result = self::load($module_id, $type_id, $branch_id, $key_id);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::load()",
				'data'=> array('module_id'=>$module_id, 'type_id'=>$type_id,
						'branch_id'=>$branch_id, 'key_id'=>$key_id),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		return $result;

	}


	/**
	 * Fetches a key_id, branch_id, type_id, or an entire module's keystore from the key cache.
	 *
	 * If an object is not in the cache yet, it will be retrieved from the database
	 * and added to the cache. Multiple items in the *lowest level group* specified
	 * can be retrieved in a single query by passing their names or id's as an array.
	 *
	 * WRONG: get( $module_id=1, $type_ids = array(3, 6, 7, 2),  $branch_ids="foo")
	 *                    ^^^^^^^^^^^^^^^^^^^^^^^^
	 * RIGHT: get( $module_id=1, $type_ids=7, $branch_ids=array("foo","bar","baz")
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/array $module_ids | Single $module_id as int. Multiple as array of ints.
	 * @param int/array $type_ids | Single $type_id as int. Multiple as array of ints.
	 * @param string/array $branch_ids | Single key type as string. Multiple key types as array of strings.
	 * @param bool &$valid | True if the object exists. False if not.
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function get($module_id, $type_ids=null, $branch_ids=null, $key_ids=null, &$valid=null){



		// CASE 1: One or more $key_ids
		// =====================================================================
		if($module_id && $type_ids && $branch_ids && $key_ids){

			if( is_array($module_id) || is_array($type_ids) || is_array($branch_ids) ){

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Attempted to pass multiple module id's, type_ids, or branch_id's when specifying key_id",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}

			// If the module_id is not present in the class cache array, try to load it
			// from the persistent cache

			if( !FOX_sUtil::keyExists($module_id, $this->cache) ){

				try {
					$this->cache[$module_id] = self::readCache($module_id);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Cache get error",
						'data'=>array("module_id"=>$module_id, "type_id"=>$type_ids,
								"branch_id"=>$branch_ids, "key_id"=>$key_ids),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));
				}
			}

			// Single key
			if( !is_array($key_ids) ){
				$single = true;
				$key_ids = array($key_ids);
			}
			else {
				$single = false;
			}


			// Find all the keys that have been requested but are not in the cache
			$missing_keys = array();

			$module_cached = FOX_sUtil::keyTrue("all_cached", $this->cache[$module_id]);
			$type_id_cached = FOX_sUtil::keyTrue($type_ids, $this->cache[$module_id]["type_id"]);
			$type_cached = FOX_sUtil::keyTrue($branch_ids, $this->cache[$module_id]["branch_id"][$type_ids]);

			if( !$module_cached  && !$type_id_cached  && !$type_cached ){

				foreach($key_ids as $key_id){

					if( !FOX_sUtil::keyExists($key_id, $this->cache[$module_id]["keys"][$type_ids][$branch_ids]) ){

						$missing_keys[] = $key_id;
					}

				}
				unset($key_id);

			}

			// Load missing keys
			if( count($missing_keys) != 0 ){

				try {
					$this->load($module_id, $type_ids, $branch_ids, $missing_keys, $skip_load=true);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>3,
						'text'=>"Load error",
						'data'=>array("module_id"=>$module_id, "type_id"=>$type_ids,
								"branch_id"=>$branch_ids, "missing_keys"=>$missing_keys),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));
				}
			}

			$result = array();

			// Build an array of the requested keys
			foreach($key_ids as $key_id){

				if( FOX_sUtil::keyExists($key_id, $this->cache[$module_id]["keys"][$type_ids][$branch_ids]) ){

					$result[$key_id] = $this->cache[$module_id]["keys"][$type_ids][$branch_ids][$key_id];
				}
				else {
					$result[$key_id] = null;
				}

			}
			unset($key_id);

			// Only set the $valid flag true if every requested key was successfully fetched
			if( count($result) == count($key_ids) ){
				$valid = true;
			}
			else {
				$valid = false;
			}

			// If only one key was requested, and the key was successfully retrieved from the db,
			// lift the result array up one level

			if( ($single == true) && (count($result) == 1) ){

				$result = $result[$key_ids[0]];
			}

			return $result;

		}

		// CASE 2: One or more $branch_ids
		// =====================================================================
		if( $module_id && $type_ids && $branch_ids && !$key_ids ){


			if( is_array($module_id) || is_array($type_ids) ){

				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Attempted to pass multiple module id's, or type_ids when specifying branch_id",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}

			// If the module_id is not present in the class cache array, try to load it
			// from the persistent cache

			if( !FOX_sUtil::keyExists($module_id, $this->cache) ){

				try {
					$this->cache[$module_id] = self::readCache($module_id);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>5,
						'text'=>"Cache get error",
						'data'=>array("module_id"=>$module_id,"type_id"=>$type_ids,
								"branch_id"=>$branch_ids, "key_id"=>$key_ids),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));
				}
			}

			// Single key
			if( !is_array($branch_ids) ){
				$single = true;
				$branch_ids = array($branch_ids);
			}
			else {
				$single = false;
			}


			// Find all the branch_ids that have been requested but are not in the cache
			$missing_types = array();

			$module_cached = FOX_sUtil::keyTrue("all_cached", $this->cache[$module_id]);
			$type_id_cached = FOX_sUtil::keyTrue($type_ids, $this->cache[$module_id]["type_id"]);
			$type_cached = FOX_sUtil::keyTrue($branch_ids, $this->cache[$module_id]["branch_id"][$type_ids]);

			if( !$module_cached && !$type_id_cached && !$type_cached ){

				foreach($branch_ids as $branch_id){

					if( !FOX_sUtil::keyExists($branch_id, $this->cache[$module_id]["keys"][$type_ids]) ){

						$missing_types[] = $branch_id;
					}

				}
				unset($branch_id);

			}

			// Load missing keys
			if( count($missing_types) != 0 ){

				try {
					$this->load($module_id, $type_ids, $missing_types, null, $skip_load=true);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>6,
						'text'=>"Load error",
						'data'=>array("module_id"=>$module_id, "type_id"=>$type_ids,
								"branch_id"=>$branch_ids, "missing_types"=>$missing_types),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));
				}
			}

			$result = array();

			// Build an array of the requested keys
			foreach($branch_ids as $branch_id){

				if( FOX_sUtil::keyExists($branch_id, $this->cache[$module_id]["keys"][$type_ids]) ){

					$result[$branch_id] = $this->cache[$module_id]["keys"][$type_ids][$branch_id];
				}
				else {
					$result[$branch_id] = null;
				}

			}
			unset($branch_id);

			// Only set the $valid flag true if every requested key was successfully fetched
			if( count($result) == count($branch_ids) ){
				$valid = true;
			}
			else {
				$valid = false;
			}

			// If only one key was requested, and the key was successfully retrieved from the db,
			// lift the result array up one level

			if( ($single == true) && (count($result) == 1) ){

				$result = $result[$branch_ids[0]];
			}

			return $result;

		}

		// CASE 3: One or more $type_ids
		// =====================================================================
		elseif( $module_id && $type_ids && !$branch_ids && !$key_ids ){


			if( is_array($module_id) ){

				throw new FOX_exception( array(
					'numeric'=>7,
					'text'=>"Attempted to pass multiple module id's when specifying type_id",
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}

			// If the module_id is not present in the class cache, try to load it
			// from the persistent cache
			if( !FOX_sUtil::keyExists($module_id, $this->cache) ){

				try {
					$this->cache[$module_id] = self::readCache($module_id);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>8,
						'text'=>"Cache get error",
						'data'=>array("module_id"=>$module_id, "type_id"=>$type_ids,
								"branch_id"=>$branch_ids, "key_id"=>$key_ids),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));
				}
			}

			// Single type_id
			if( !is_array($type_ids) ){
				$single = true;
				$type_ids = array($type_ids);
			}
			else {
				$single = false;
			}


			// Find all the type_ids that have been requested but are not in the cache
			$missing_type_ids = array();

			foreach($type_ids as $type_id){

				$module_cached = FOX_sUtil::keyTrue("all_cached", $this->cache[$module_id]);
				$type_id_cached = FOX_sUtil::keyTrue($type_id, $this->cache[$module_id]["type_id"]);

				if( !$module_cached && !$type_id_cached ){

					$missing_type_ids[] = $type_id;
				}

			}
			unset($type_id);

			// Load missing type_ids
			if( count($missing_type_ids) != 0 ){

				try {
					self::load($module_id, $missing_type_ids, null, null, $skip_load=true);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>9,
						'text'=>"Load error",
						'data'=>array("module_id"=>$module_id, "type_id"=>$type_ids,
								"branch_id"=>$branch_ids, "key_id"=>$key_ids, "missing_type_ids"=>$missing_type_ids),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));
				}
			}

			$result = array();

			// Build an array of the requested type_ids
			foreach($type_ids as $type_id){

				if( FOX_sUtil::keyExists($type_id, $this->cache[$module_id]["keys"]) ){

					$result[$type_id] = $this->cache[$module_id]["keys"][$type_id];
				}
				else {
					$result[$type_id] = null;
				}

			}
			unset($type_id);

			// Only set the $valid flag true if every requested type_id was successfully fetched
			if( count($result) == count($type_ids) ){
				$valid = true;
			}
			else {
				$valid = false;
			}

			// If only one type_id was requested, and the type_id was successfully retrieved from the db,
			// lift the result array up one level

			if( ($single == true) && (count($result) == 1) ){

				$result = $result[$type_ids[0]];
			}

			return $result;

		}

		// CASE 4: One or more $module_ids
		// =====================================================================
		elseif($module_id && !$type_ids && !$branch_ids && !$key_ids) {

			if(!is_array($module_id)){

				$single = true;
				$module_id = array($module_id);
			}
			else {
				$single = false;
			}

			// Find all the module_id's that have been requested but are not in the cache
			$missing_module_ids = array();

			foreach($module_id as $current_module_id){

				if(!FOX_sUtil::keyTrue("all_cached", $this->cache[$current_module_id]) ){

					$missing_module_ids[] = $current_module_id;
				}

			}
			unset($current_module_id);


			// Load missing module_id's
			if( count($missing_module_ids) != 0 ){

				try {
					self::load($missing_module_ids, null, null, null, $skip_load=true);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>13,
						'text'=>"Load error",
						'data'=>array("missing_module_ids"=>$missing_module_ids),
						'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
						'child'=>$child
					));
				}
			}

			$result = array();

			// Build an array of the requested module_ids
			foreach($module_id as $current_module_id){

				if( FOX_sUtil::keyExists($current_module_id, $this->cache) ){

					$result[$current_module_id] = $this->cache[$current_module_id]["keys"];
				}
				else {
					$result[$current_module_id] = null;
				}

			}
			unset($current_module_id);

			// Only set the $valid flag true if every requested module_id was successfully fetched
			if( count($result) == count($module_id) ){
				$valid = true;
			}
			else {
				$valid = false;
			}

			// If only one module_id was requested, and the module_id was successfully retrieved from the db,
			// lift the result array up one level

			if( ($single = true) && (count($result) == 1) ){

				$result = $result[$module_id[0]];
			}

			return $result;

		}

		// CASE 6: Bad input
		// =====================================================================
		else {

			throw new FOX_exception( array(
				'numeric'=>14,
				'text'=>"Bad input args",
				'data'=>array("module_id"=>$module_id, "type_id"=>$type_ids,
					      "branch_id"=>$branch_ids, "key_id"=>$key_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

	}


	/**
	 * Returns all data for one or more $module_id's.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/array $module_id | Single module id as int. Multiple module id's as array of ints.
	 * @param bool &$valid | True if module_id exists. False if not.
	 * @return bool | Exception on failure. True on success.
	 */

	public function getModule($module_id, &$valid=null){


		try {
			$result = self::get($module_id, null, null, null, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::get()",
				'data'=> $module_id,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		return $result;

	}


	/**
	 * Returns one or more type_id data arrays from a single REMOVE belonging to a single module id
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | Single module id
	 * @param string/array $type_id | Single type_id as string. Multiple type_ids as array of strings.
	 * @param bool &$valid | True if type_id exists. False if not.
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function getType($module_id, $type_id, &$valid=null){


		try {
			$result = self::get($module_id, $type_id, null, null, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::get()",
				'data'=> array('module_id'=>$module_id, 'type_id'=>$type_id),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		return $result;

	}


	/**
	 * Returns one or more key types for a single type_id belonging to a single module id.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | Single module id
	 * @param string $type_id | Single type_id
	 * @param string/array $branch_id | Single key type as string. Multiple key types as array of strings.
	 * @param bool &$valid | True if key exists. False if not.
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function getBranch($module_id, $type_id, $branch_id, &$valid=null){


		try {
			$result = self::get($module_id, $type_id, $branch_id, $key_id=null, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::get()",
				'data'=> array('module_id'=>$module_id, 'type_id'=>$type_id,
						'branch_id'=>$branch_id),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		return $result;

	}


	/**
	 * Returns one or more keys for a single key type within a single type_id belonging to
	 * a single module id.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | Single module id
	 * @param string $type_id | Single type_id
	 * @param string $branch_id | Single key type
	 * @param int/array $key_id | Single key_id as int. Multiple key_ids as array of ints.
	 * @param bool &$valid | True if key exists. False if not.
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function getKey($module_id, $type_id, $branch_id, $key_id, &$valid=null){


		try {
			$result = self::get($module_id, $type_id, $branch_id, $key_id, $valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::get()",
				'data'=> array('module_id'=>$module_id, 'type_id'=>$type_id,
						'branch_id'=>$branch_id, 'key_id'=>$key_id),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		return $result;

	}


	/**
	 * Creates a new key or updates an existing key.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | module id
	 * @param string $type_id | type_id id
	 * @param string $branch_id | key type
	 * @param int $key_id | key id
	 * @param bool/int/float/string/array/obj $ctrl_val | key value
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function setKey($module_id, $type_id, $branch_id, $key_id, $ctrl_val){


		$data = array(
				array(
					"module_id"=>$module_id,
					"type_id"=>$type_id,
					"branch_id"=>$branch_id,
					"key_id"=>$key_id,
					"ctrl_val"=>$ctrl_val
				)
		);

		try {
			$result = self::setKeyMulti($data);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error calling self::setKeyMulti",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		return $result;

	}


	/**
	 * Creates or updates one or more keys.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
         * @param array $data | Array of row arrays
	 *	=> ARR @param int '' | Individual row array
	 *	    => VAL @param int $module_id | module id
	 *	    => VAL @param string $type_id | type_id id
	 *	    => VAL @param string $branch_id | key type
	 *	    => VAL @param int $key_id | key id
	 *	    => VAL @param bool/int/float/string/array/obj $ctrl_val | key value
	 *
	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function setKeyMulti($data){


		$db = new FOX_db();
                $struct = $this->_struct();

		$update_data = array();
		$insert_data = array();

		if( !is_array($data) || (count($data) < 1) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty data array",
				'data'=>$data,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}


		// Process each row
		// ===========================================================

		foreach( $data as $row ){

			if( empty($row["module_id"]) ){

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Empty module_id",
					'data'=>$row,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}

			if( empty($row["type_id"]) ){

				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Empty type_id name",
					'data'=>$row,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}

			if( empty($row["branch_id"]) ){

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Empty key type",
					'data'=>$row,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}

			if( empty($row["key_id"]) ){

				throw new FOX_exception( array(
					'numeric'=>6,
					'text'=>"Empty key id",
					'data'=>$row,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>null
				));
			}

			// Expand the key into a heirarchical array
			$update_data[$row["module_id"]][$row["type_id"]][$row["branch_id"]][$row["key_id"]] = $row["ctrl_val"];

		}
		unset($row);


		// CASE 1: Transactions aren't required.
		// ===========================================================
		if( count($data) == 1 ){

			$row = $data[0];

			// Lock and load the cache key
			try {
				$page_image = self::lockCachePage($row["module_id"]);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>7,
					'text'=>"Cache lock error",
					'data'=>$row,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}

			try {
				$rows_changed = $db->runIndateQuery($struct, $row, $columns=null);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>8,
					'text'=>"Error while writing to the database",
					'data'=>$data,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}

			// Update the key's value in the cache page
			$page_image["keys"][$row["type_id"]][$row["branch_id"]][$row["key_id"]] = $row["ctrl_val"];

			// Write the updated image back to the cache (which also releases our lock)
			try {
				self::writeCachePage( array( $row["module_id"] => $page_image ) );
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>9,
					'text'=>"Cache set error",
					'data'=>$this->cache[$row["module_id"]],
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}

			// If the persistent cache write was successful, write the new page image
			// to the class cache

			$this->cache[$row["module_id"]] = $page_image;

			return (int)$rows_changed;

		}
		// CASE 2: Transactions are required.
		// ===========================================================
		else {

			// Lock and load the persistent cache records for all the module_id's
			// into the temp class cache array

			try {
				$update_cache = self::lockCachePage( array_keys($update_data) );
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>10,
					'text'=>"Cache get error",
					'data'=>$update_data,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}

			// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

			try {
				$db->beginTransaction();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>11,
					'text'=>"Couldn't initiate transaction",
					'data'=>$data,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}

			$rows_changed = 0;

			foreach( $update_data as $module_id => $type_ids ){

				foreach( $type_ids as $type_id => $branch_ids ){

					foreach( $branch_ids as $branch_id => $key_ids ){

						foreach( $key_ids as $key_id => $ctrl_val){

							$insert_data = array(
										"module_id"=>$module_id,
										"type_id"=>$type_id,
										"branch_id"=>$branch_id,
										"key_id"=>$key_id,
										"ctrl_val"=>$ctrl_val
							);

							try {
								$rows_changed += (int)$db->runIndateQuery($struct, $insert_data, $columns=null);
							}
							catch (FOX_exception $child) {

								try {
									$db->rollbackTransaction();
								}
								catch (FOX_exception $child_2) {

									throw new FOX_exception( array(
										'numeric'=>12,
										'text'=>"Error while writing to the database. Error rolling back.",
										'data'=>array('rollback_exception'=>$child_2),
										'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
										'child'=>$child
									));
								}

								throw new FOX_exception( array(
									'numeric'=>13,
									'text'=>"Error while writing to the database. Successful rollback.",
									'data'=>$data,
									'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
									'child'=>$child
								));
							}

							// Overwrite the temp class cache array with the data we set in the db query
							$update_cache[$module_id]["keys"][$type_id][$branch_id][$key_id] = $ctrl_val;


						}
						unset($key_id, $ctrl_val);
					}
					unset($branch_id, $key_ids);
				}
				unset($type_id, $branch_ids);
			}
			unset($module_id, $type_ids);


			// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


			try {
				$db->commitTransaction();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>14,
					'text'=>"Error commiting transaction to database",
					'data'=>$data,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}


			// Overwrite the persistent cache records with the temp class cache array items

			try {
				self::writeCachePage($update_cache);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>15,
					'text'=>"Cache set error",
					'data'=>$data,
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}

			// Write the temp class cache array items to the class cache array
			foreach($update_cache as $module_id => $module_data){

				$this->cache[$module_id] = $module_data;
			}
			unset($module_id, $module_data);


			return (int)$rows_changed;

		}


	}


	/**
	 * Creates or replaces a module's entire policy.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | module_id
	 *
         * @param array $data | Array of type_id arrays
	 *	    => ARR @param string $type_id | type_id array
	 *		=> ARR @param string $branch_id | branch_id array
	 *		    => ARR @param int $key_id | key_id array
	 *			=> VAL @param bool/int/float/string/array/obj $ctrl_val | control value
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function setPolicy($module_id, $data){


		$db = new FOX_db();
                $struct = $this->_struct();

		if( empty($module_id) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty or incorrect module_id",
				'data'=>array("module_id"=>$module_id, "data"=>$data),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		if( empty($data) || !is_array($data) ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Empty or malformed data array",
				'data'=>array("module_id"=>$module_id, "data"=>$data),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		// Lock the module_id's cache page
		// ===========================================================

		try {
			self::lockCachePage($module_id);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error locking cache page",
				'data'=>array("module_id"=>$module_id),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}


		// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


		try {
			$db->beginTransaction();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Couldn't initiate transaction",
				'data'=>array("module_id"=>$module_id, "data"=>$data),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// Clear all entries for the module_id from the db
		// ===========================================================

		$args = array(
				array("col"=>"module_id", "op"=>"=", "val"=>$module_id)
		);

		try {
			$db->runDeleteQuery($struct, $args, $ctrl=null);
		}
		catch (FOX_exception $child) {

			try {
				$db->rollbackTransaction();
			}
			catch (FOX_exception $child_2) {

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Error while deleting from the database. Error rolling back.",
					'data'=>array('rollback_exception'=>$child_2, "module_id"=>$module_id),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Error while deleting from the database. Successful rollback.",
				'data'=>array("module_id"=>$module_id),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}


		// Flatten the heirarchical $data array into an array of row arrays
		// while calculating the cache flags
		// ================================================================

		$update_cache = array();
		$insert_data = array();

		foreach( $data as $type_id => $branch_ids ){

			$update_cache["type_id"][$type_id] = true;

			foreach( $branch_ids as $branch_id => $key_ids ){

				$update_cache["branch_id"][$type_id][$branch_id] = true;

				foreach( $key_ids as $key_id => $ctrl_val){

					$update_cache["keys"][$type_id][$branch_id][$key_id] = $ctrl_val;

					$insert_data[] = array(
								"module_id"=>$module_id,
								"type_id"=>$type_id,
								"branch_id"=>$branch_id,
								"key_id"=>$key_id,
								"ctrl_val"=>$ctrl_val
					);
				}
				unset($key_id, $ctrl_val);
			}
			unset($branch_id, $key_ids);
		}
		unset($type_id, $branch_ids);



		// Write to db
		// ===========================================================

		try {
			$db->runInsertQueryMulti($struct, $insert_data, $columns=null, $ctrl=null);
		}
		catch (FOX_exception $child) {

			try {
				$db->rollbackTransaction();
			}
			catch (FOX_exception $child_2) {

				throw new FOX_exception( array(
					'numeric'=>7,
					'text'=>"Error while writing to the database. Error rolling back.",
					'data'=>array('insert_data'=>$insert_data, 'rollback_exception'=>$child_2),
					'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
					'child'=>$child
				));
			}

			throw new FOX_exception( array(
				'numeric'=>8,
				'text'=>"Error while writing to the database. Successful rollback.",
				'data'=>$insert_data,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@


		try {
			$db->commitTransaction();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>9,
				'text'=>"Error commiting transaction to database",
				'data'=>$insert_data,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}


		// Overwrite the module_id's cache page, releasing our lock
		// ===========================================================

		try {
			self::writeCachePage( array( $module_id => $update_cache) );
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>10,
				'text'=>"Cache set error",
				'data'=>array("module_id"=>$module_id, "update_data"=>$update_cache),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}


		// Write the temp class cache to the class cache
		$this->cache[$module_id] = $update_cache;

		return true;

	}


	/**
	 * Drops one or more keys from the database
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | ID of the module
	 * @param int $type_id | ID of the type_id
	 * @param string $branch_id | key type
	 * @param int/array $key_ids | Single key_id as int. Multiple key_ids as array of int.
	 *
	 * @return bool | Exception on failure. True on success. False on nonexistent.
	 */

	public function dropKey($module_id, $type_id, $branch_id, $key_ids) {


		$db = new FOX_db();
                $struct = $this->_struct();

		if( empty($module_id) || empty($type_id) || empty($branch_id) || empty($key_ids) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty control parameter",
				'data'=>array("module_id"=>$module_id, "type_id"=>$type_id, "branch_id"=>$branch_id, "key_id"=>$key_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		if( is_array($module_id ) || is_array($type_id) || is_array($branch_id) ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Attempted to pass multiple module ids, type_ids, or branch_ids",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		// Lock and load the module_id's cache page
		// ===========================================================

		try {
			$update_cache = self::lockCachePage($module_id);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error locking cache page",
				'data'=>array("module_id"=>$module_id),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// Drop affected type_ids from the db
		// ===========================================================

		$args = array(
				array("col"=>"module_id", "op"=>"=", "val"=>$module_id),
				array("col"=>"type_id", "op"=>"=", "val"=>$type_id),
				array("col"=>"branch_id", "op"=>"=", "val"=>$branch_id),
				array("col"=>"key_id", "op"=>"=", "val"=>$key_ids)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args, $ctrl=null);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error while deleting from database",
				'data'=>array("module_id"=>$module_id, "type_id"=>$type_id, "branch_id"=>$branch_id, "key_id"=>$key_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}


		// Rebuild the module_id's cache page image
		// ===========================================================

		if(!is_array($key_ids)){
			$key_ids = array($key_ids);
		}

		foreach($key_ids as $key_id){
			unset($update_cache["keys"][$type_id][$branch_id][$key_id]);
		}
		unset($key_id);


		$update_cache["keys"] = FOX_sUtil::arrayPrune($update_cache["keys"], 2);


		// Overwrite the module_id's cache page, releasing our lock
		// ===========================================================

		try {
			self::writeCachePage( array($module_id => $update_cache) );
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Cache set error",
				'data'=>array("module_id"=>$module_id, "update_cache"=>$update_cache),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		$this->cache[$module_id] = $update_cache;


		return (bool)$rows_changed;

	}


	/**
	 * Drops one or more key types from the database
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | ID of the module
	 * @param int $type_id | ID of the type_id
	 * @param string/array $branch_ids | Single branch_id as string. Multiple branch_ids as array of string.
	 *
	 * @return bool | Exception on failure. True on success. False on nonexistent.
	 */

	public function dropBranch($module_id, $type_id, $branch_ids) {


		$db = new FOX_db();
                $struct = $this->_struct();

		if( empty($module_id) || empty($type_id) || empty($branch_ids) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty control parameter",
				'data'=>array("module_id"=>$module_id, "type_id"=>$type_id, "branch_id"=>$branch_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		if( is_array($module_id ) || is_array($type_id) ){

			throw new FOX_exception( array(
				    'numeric'=>2,
				    'text'=>"Attempted to pass multiple module ids, or type_ids",
				    'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				    'child'=>null
			));
		}

		// Lock and load the module_id's cache page
		// ===========================================================

		try {
			$update_cache = self::lockCachePage($module_id);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error locking cache page",
				'data'=>array("module_id"=>$module_id),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// Drop affected type_ids from the db
		// ===========================================================

		$args = array(
				array("col"=>"module_id", "op"=>"=", "val"=>$module_id),
				array("col"=>"type_id", "op"=>"=", "val"=>$type_id),
				array("col"=>"branch_id", "op"=>"=", "val"=>$branch_ids)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args, $ctrl=null);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error while deleting from database",
				'data'=>array("module_id"=>$module_id, "type_id"=>$type_id, "branch_id"=>$branch_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// Rebuild the module_id's cache page image
		// ===========================================================

		if(!is_array($branch_ids)){
			$branch_ids = array($branch_ids);
		}

		foreach($branch_ids as $branch_id){
			unset($update_cache["keys"][$type_id][$branch_id]);
			unset($update_cache["branch_id"][$type_id][$branch_id]);
		}
		unset($branch_id);


		$update_cache["keys"] = FOX_sUtil::arrayPrune($update_cache["keys"], 2);
		$update_cache["branch_id"] = FOX_sUtil::arrayPrune($update_cache["branch_id"], 1);


		// Overwrite the module_id's cache page, releasing our lock
		// ===========================================================

		try {
			self::writeCachePage( array($module_id => $update_cache) );
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Cache set error",
				'data'=>array("module_id"=>$module_id, "update_cache"=>$update_cache),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		$this->cache[$module_id] = $update_cache;

		return (bool)$rows_changed;

	}


	/**
	 * Drops all keys from the database and cache for one or more type_ids belonging
	 * to a single module id.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | ID of the module
	 * @param string/array $type_ids | Single type_id as string. Multiple type_ids as array of string.
	 *
	 * @return bool | Exception on failure. True on success. False on nonexistent.
	 */

	public function dropType($module_id, $type_ids) {


		$db = new FOX_db();
                $struct = $this->_struct();

		if( empty($module_id) || empty($type_ids) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty control parameter",
				'data'=>array("module_id"=>$module_id, "type_id"=>$type_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		if( is_array($module_id ) ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Attempted to pass multiple module id's",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		// Lock and load the module_id's cache page
		// ===========================================================

		try {
			$update_cache = self::lockCachePage($module_id);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Error locking cache page",
				'data'=>array("module_id"=>$module_id),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// Drop affected type_ids from the db
		// ===========================================================

		$args = array(
				array("col"=>"module_id", "op"=>"=", "val"=>$module_id),
				array("col"=>"type_id", "op"=>"=", "val"=>$type_ids)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args, $ctrl=null);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error while deleting from database",
				'data'=>array("module_id"=>$module_id, "type_id"=>$type_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// Rebuild the module_id's cache page image
		// ===========================================================

		if(!is_array($type_ids)){
			$type_ids = array($type_ids);
		}

		foreach($type_ids as $type_id){

			unset($update_cache["keys"][$type_id]);
			unset($update_cache["branch_id"][$type_id]);
			unset($update_cache["type_id"][$type_id]);
		}
		unset($type_id);

		$update_cache["keys"] = FOX_sUtil::arrayPrune($update_cache["keys"], 2);
		$update_cache["branch_id"] = FOX_sUtil::arrayPrune($update_cache["branch_id"], 1);


		// Overwrite the module_id's cache page, releasing our lock
		// ===========================================================

		try {
			self::writeCachePage( array($module_id => $update_cache) );
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Cache set error",
				'data'=>array("module_id"=>$module_id, "update_cache"=>$update_cache),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		$this->cache[$module_id] = $update_cache;


		return (bool)$rows_changed;

	}


	/**
	 * Drops all data for one or more module_id's from the database and cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int/array $module_id | Single module_id as int. Multiple module_ids as array of int.
	 * @return bool | Exception on failure. True on success. False on nonexistent.
	 */

	public function dropModule($module_ids) {


		$db = new FOX_db();
                $struct = $this->_struct();

		if( empty($module_ids)){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty module_id",
				'data'=>array("module_ids"=>$module_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		// Lock and load the module_id's cache page
		// ===========================================================

		try {
			self::lockCachePage($module_ids);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error locking cache page",
				'data'=>array("module_ids"=>$module_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// Drop affected type_ids from the db
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
				'text'=>"Error while deleting from database",
				'data'=>array("module_ids"=>$module_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// Drop the module_ids cache pages
		// ===========================================================

		try {
			self::flushCachePage($module_ids);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error flushing cache pages",
				'data'=>array("module_ids"=>$module_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}


		return (bool)$rows_changed;

	}


	/**
	 * Drops one or more "branch_id"=>"key_id" pairs for for ALL MODULES on the site
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param array $keys | Array of "branch_id"=>"key_id" pairs
	 *	    => KEY @param string $branch_id | Key type
	 *		=> VAL @param int $key_id | Key id

	 * @return int | Exception on failure. Int number of rows changed on success.
	 */

	public function dropSiteKey($keys) {


		$db = new FOX_db();
		$struct = $this->_struct();

		if( empty($keys) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty keys array",
				'data'=>array("keys"=>$keys),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		$query_args = array();

		foreach($keys as $branch_id => $key_ids){

			if( is_array($key_ids) ){

				foreach($key_ids as $key_id){

					$query_args[] = array("branch_id"=>$branch_id, "key_id"=>$key_id);
				}
				unset($key_id);
			}
			else {
				$query_args[] = array("branch_id"=>$branch_id, "key_id"=>$key_ids);
			}

		}
		unset($branch_id);

		$args = array(
				"key_col"=>$key_col = array("branch_id", "key_id"),
				"args"=>$query_args
		);

		$ctrl = array(
			"args_format"=>"matrix"
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args, $ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error while deleting from database",
				'data'=>array("keys"=>$keys, "args"=>$args),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// Since this operation affects *all* module_id's, we have to flush
		// the entire namespace

		try {
			self::flushCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Cache flush error",
				'data'=>array("keys"=>$keys),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		return (int)$rows_changed;

	}


	/**
	 * Drops one or more keytypes for for ALL MODULES on the site
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string/array $branch_ids | Single key type as string. Multiple types as array of strings.
	 * @return int | Exception on failure. Number of rows changed on success.
	 */

	public function dropSiteBranch($branch_ids) {


		$db = new FOX_db();
		$struct = $this->_struct();

		if( empty($branch_ids) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty control parameter",
				'data'=>array("branch_id"=>$branch_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		$args = array(
				array("col"=>"branch_id", "op"=>"=", "val"=>$branch_ids)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args, $ctrl=null);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error while deleting from database",
				'data'=>array("branch_id"=>$branch_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// Since this operation affects *all* module_id's, we have to flush the cache
		try {
			self::flushCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Cache flush error",
				'data'=>array("branch_id"=>$branch_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		return (int)$rows_changed;

	}


	/**
	 * Drops one or more type_ids for for ALL MODULES on the site
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $type_ids | Single type_id as string. Multiple type_ids as array of string.
	 * @return int | Exception on failure. Number of rows changed on success.
	 */

	public function dropSiteType($type_ids) {


		$db = new FOX_db();
		$struct = $this->_struct();

		if( empty($type_ids) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty control parameter",
				'data'=>array("type_id"=>$type_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}

		$args = array(
				array("col"=>"type_id", "op"=>"=", "val"=>$type_ids)
		);

		try {
			$rows_changed = $db->runDeleteQuery($struct, $args, $ctrl=null);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error while deleting from database",
				'data'=>array("type_id"=>$type_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// Since this operation affects *all* module_id's, we have to flush
		// the entire cache namespace

		try {
			self::flushCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Cache flush error",
				'data'=>array("type_id"=>$type_ids),
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}


		return (int)$rows_changed;

	}


	/**
	 * Deletes the entire module data store, and flushes the cache. Generally
	 * used for testing and debug.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool | Exception on failure. True on success.
	 */

	public function dropAll() {


		$db = new FOX_db();
		$struct = $this->_struct();

		try {
			$db->runTruncateTable($struct);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error while clearing the database",
				'data'=>null,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

		// Since this operation affects *all* module_id's, we have to flush
		// the entire cache namespace

		try {
			self::flushCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Cache flush error",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>$child
			));
		}

	}




} // End of class FOX_dataStore_paged_L5_base

?>