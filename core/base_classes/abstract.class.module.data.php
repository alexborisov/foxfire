<?php

/**
 * FOXFIRE MODULE DATA STORAGE
 * This class operates as a simple key:value datastore for module data and configuration settings. This class
 * should be used when a module has to store more than 10 KB of data, otherwise use class FOX_config.
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Base Classes
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

// ###############################################################################################
// ###############################################################################################
// 
// NOTE: This class needs to be rebuilt as a child class, extending a base 'datastore'
//       abstract class
//       
// ###############################################################################################
// ###############################################################################################


abstract class FOX_module_data_base extends FOX_db_base {

    
    	var $process_id;		    // Unique process id for this thread. Used by FOX_db_base for cache 
					    // locking. Loaded by descendent class.
	
	var $cache;			    // Main cache array for this class
	
	var $mCache;			    // Local copy of memory cache singleton. Used by FOX_db_base for cache 
					    // operations. Loaded by descendent class.	
	
	
	// ================================================================================================================



	/**
	 * Fetches one or more keys for one or more modules. If the keys are not in the cache,
	 * they will be retrieved from the database and added to the cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/array $module_id | Single module id as int. Multiple module id's as array of ints.
	 * @param string/array $key | Single key name as string. Multiple key names as array of strings.
	 * @return bool | Exception on failure. True on success.
	 */

	public function get($module_id, $key=null){

	}


	/**
	 * Returns all data for one or more $module_id's.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/array $module_id | Single module id as int. Multiple module id's as array of ints.
	 * @return bool | Exception on failure. True on success.
	 */

	public function getModule($module_id){
		
	}

	/**
	 * Returns one or more keys belonging to a single module id.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | Single module id as int.
	 * @param string/array $key | Single key name as string. Multiple key names as array of strings.
	 * @return bool | False on failure. True on success.
	 */

	public function getKey($module_id, $key){

	}


	/**
	 * Creates a new key or updates an existing key. Updates the database and cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | album module id
	 * @param string $key | single key name as string (max 16 characters)
	 * @param mixed $val | value to assign to key
	 * @return bool | Exception on failure. True on success.
	 */

	public function setKey($module_id, $key, $val){
		
	}


	/**
	 * Drops one or more keys belonging to a single module id from the database and cache.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | module id
	 * @param string $key | name of the key
	 * @return bool | Exception on failure. True on success.
	 */

	public function dropKey($module_id, $key) {

	}


	/**
	 * Drops ALL DATA for a single module id from the database and cache. Generally
	 * used when deleting an album module from the site.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $module_id | ID of the module
	 * @return bool | False on failure. True on success.
	 */

	public function dropModule($module_id) {

	}


	/**
	 * Deletes ALL DATA for ALL MODULES and empties the cache. Generally
	 * used for testing and debug.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return bool | False on failure. True on success.
	 */

	public function dropAll() {

	}
	
	

} // End of class FOX_module_data_base

?>