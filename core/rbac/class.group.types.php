<?php

/**
 * FOXFIRE USER GROUP TYPES
 * This class operates as a central controller for all user access groups within the system. Access groups perform
 * a similar function to WordPress "Roles", but are more flexible. Each group has a set of admin-defined keys that
 * allow users to do things on the site. When a user is assigned to a group, the group's keys are added to the user's
 * keyring. Users can be members of multiple groups at the same time, giving them the combined keys of all the groups
 * they are members of. The admin can grant or revoke keys from a single user without affecting the other members of
 * the group.
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage RBAC
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

class FOX_uGroupType extends FOX_db_base {


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

		"table" => "fox_sys_user_grouptypes",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_uGroupType",
		"cache_strategy" => "monolithic",
		"cache_engine" => array("memcached", "redis", "apc"),	    
		"columns" => array(
		    "group_id" =>	array(	"php"=>"int",	"sql"=>"smallint",	"format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>true,   "default"=>null, "index"=>"PRIMARY"),
		    "name" =>	array(	"php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>32,		"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null, "index"=>"UNIQUE"),
		    "title" =>	array(	"php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>128,		"flags"=>"",			"auto_inc"=>false,  "default"=>null, "index"=>false),
		    "caption" =>	array(	"php"=>"string",	"sql"=>"longtext",	"format"=>"%s", "width"=>null,		"flags"=>"",			"auto_inc"=>false,  "default"=>null, "index"=>false),
		    "is_default" =>	array(	"php"=>"bool",	"sql"=>"tinyint",	"format"=>"%d", "width"=>1,		"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null, "index"=>true),
		    "icon" =>	array(	"php"=>"int",	"sql"=>"int",	"format"=>"%d", "width"=>null,	"flags"=>"",			"auto_inc"=>false,  "default"=>null, "index"=>false),
		 )
	);

	// TODO: The group icon will be handled as a standard media item that resides in a special admin album. This will be done
	// using the same "album as object" functionality used for group albums.


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
	 * Loads one or more group id's into the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string/array $name | Group name as string. Multiple names as array of string.
	 * @param bool $skip_load | If set true, the function will not update the class cache array from
	 *			    the persistent cache before adding data from a db call and saving it
	 *			    back to the persistent cache.
	 * @return bool/int/array | False on failure. Mixed data types on success.
	 */

	public function load($name, $skip_load=false){

		global $fox;
		$db = new FOX_db();

		// Build the query
		// =================================================================

		$args = array(
				array("col"=>"name", "op"=>"=", "val"=>$name)
		);

		$columns = array("mode"=>"include", "col"=>array("group_id", "name") );

		$ctrl = array("format"=>"array_key_array", "key_col"=>array("name") );

		try{
			$result = $db->runSelectQuery(self::$struct, $args, $columns, $ctrl);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB select exception",
				    'data'=>array("args"=>$args,"columns"=>$columns, "ctrl"=>$ctrl),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		// Load the class cache array from the persistent cache
		if(!$skip_load){

			try{
				self::loadCache();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>2,
					    'text'=>"loadCache exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
		}

		// Update the cache array with the values fetched from the database
		if($result){

			if( is_array($name) ){

				foreach( $result as $group_name => $group_id ){

					$this->cache["ids"][$group_name] = $group_id["group_id"];
				}
				unset($group_name, $group_id);
			}
			else {
				$this->cache["ids"][$name] = $group_id["group_id"];
			}

			// Write the updated class cache array to the persistent cache
			try{
				$cache_ok = self::saveCache();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>3,
					    'text'=>"saveCache exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

			return $cache_ok;

		}
		else {
			return false;
		}

	}


	/**
	 * Returns the ID of the default group
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return bool/int | False on failure. Int default group id on success.
	 */

	public function getDefaultGroupID(){


		// If default_id is already set in the class cache array, return its value
		if( FOX_sUtil::keyExists("default_id", $this->cache) ){

			return $this->cache["default_id"];
		}
		else {

			// If not, load the class cache from the persistent cache and try again
			try{
				self::loadCache();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>1,
					    'text'=>"loadCache exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			if( FOX_sUtil::keyExists("default_id", $this->cache)){

				return $this->cache["default_id"];
			}
			else {

				// If default_id is not set in the persistent cache, fetch the default
				// class from the database, and add it to the persistent cache and the
				// class cache

				$db = new FOX_db();

				$args = array(
						array("col"=>"is_default", "op"=>"=", "val"=>1)
				);

				$columns = array("mode"=>"include", "col"=>array("group_id") );

				$ctrl = array("format"=>"var");

				try{
					$result = $db->runSelectQuery(self::$struct, $args, $columns, $ctrl);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>2,
						    'text'=>"DB select exception",
						    'data'=>array("args"=>$args, "columns"=>$columns, "ctrl"=>$ctrl),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				if($result){

					// Update the class cache
					$this->cache["default_id"] = $result;

					// Update the persistent cache
					try{
						$cache_ok = self::saveCache();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>3,
							    'text'=>"saveCache exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
					if($cache_ok){
						return $result;
					}
					else {
						return false;
					}
				}
				else {

					throw new FOX_exception(array(
						    'numeric'=>4,
						    'text'=>"FOX_uGroupType::getDefaultGroupID called on a database containing no default group",
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>null
						    )
					);
				}

			}

		}

	}


	/**
	 * Fetches the $group_id of one or more groups
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string/array $name | Single name as string. Multiple as array of strings.
	 * @return bool/int/array | False on failure. Int or array of ints on success.
	 */

	public function getGroupID($name){


		if( !is_array($name) ){
			$single_mode = true;
			$name = array($name);
		}

		// Build a list of keys we need to fetch from the db

		$missing_names = array();

		foreach($name as $group_name){

		    if( !FOX_sUtil::keyExists($group_name, $this->cache["ids"]) ){

			    $missing_names[] = $group_name;
		    }

		}
		unset($group_name);


		// Load any missing groups
		if( count($missing_names) > 0 ){

			try{
				self::load($missing_names, $skip_load=false);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>1,
					    'text'=>"FOX_uGroupType load exception",
					    'data'=>array("missing_names"=>$missing_names, "skip_load"=>false),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
		}


		// Build the result
		if($single_mode){

			return $this->cache["ids"][$name[0]];
		}
		else {
			$result = array();

			foreach($name as $group_name){

				$result[$group_name] = $this->cache["ids"][$group_name];
			}

			return $result;
		}

	}


	/**
	 * Fetches all fields for one or more groups. This function is NOT cached, and should
	 * only be used in admin editing screens, where all of the group's data fields are
	 * required.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/array $group_id | id of the group. Multiple id's as array of ints.
	 * @return bool/int | False on failure. Array of arrays of group rows on success.
	 */

	public function getGroupData($group_id){

		$db = new FOX_db();

		$ctrl = array("format"=>"array_key_array", "key_col"=>array("group_id"));

		try{
			$result = $db->runSelectQueryCol(self::$struct, "group_id", "=", $group_id, $columns=null, $ctrl);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB select exception",
				    'data'=>array("col"=>"group_id", "op"=>"=", "val"=>$group_id, "ctrl"=>$ctrl),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		return $result;
	}

	/**
         * Returns an array of groupType objects based on user supplied parameters. This function is
	 * NOT cached, and should only be used in admin editing screens, where all of the group's data
	 * fields are required.
         *
         * @version 1.0
         * @since 1.0
         *
	 * @param array $args | @see FOX_db::runSelectQuery() for array structure
	 * @param bool/array $columns | @see FOX_db::runSelectQuery() for array structure
	 * @param array $ctrl | @see FOX_db::runSelectQuery() for array structure
         * @return bool/int/array | False on failure. Int on count. Array of objects on success.
         */

	public function query($args=null, $columns=null, $ctrl=null) {

		$ctrl_default = array(
			"format"=>"array_array"
		);

		$ctrl = wp_parse_args($ctrl, $ctrl_default);

		$db = new FOX_db();
		try{
			$result = $db->runSelectQuery(self::$struct, $args, $columns, $ctrl);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB select exception",
				    'data'=>array("args"=>$args, "columns"=>$columns, "ctrl"=>$ctrl),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		return $result;
	}


	/**
	 * Creates a single group
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $name | name of group (max 32 chars)
	 * @param string $title | title of group (max 128 chars)
	 * @param string $caption | caption of group
	 * @param bool $is_default | if set true, this group is the default group
	 * @param int $icon | media_id of image to use as icon for the group
	 * @return bool | False on failure. Created group's id on success.
	 */

	public function createGroup($data){
		
	    
		if( !FOX_sUtil::keyExists('name', $data) || empty($data['name']) ){
		    
			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"Missing or invalid 'name' key",
				    'data'=>$data,
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>null
			));		    
		    
		}
		
		
		if( !FOX_sUtil::keyExists('is_default', $data) || ($data['is_default'] === null) ){
		    
			$data['is_default'] = false;		    
		}		
		

		// IMPORTANT: Default group flag rules
		// ==========================================
		// 1) Only one group can be the default group
		// 2) There must always be a default group

		$db = new FOX_db();
		$ctrl = array("format"=>"var", "count"=>true);
		try{
			$groups_count = $db->runSelectQuery(self::$struct, $args=null, $columns=null, $ctrl);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB select exception",
				    'data'=>array("ctrl"=>$ctrl),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}

		// CASE 1: No other groups exist
		// =============================================================================
		if($groups_count == 0){

			$data["is_default"] = true;	// Force $is_default flag to be true

			// Insert new group into the db
			try{
				$insert_ok = $db->runInsertQuery(self::$struct, $data, $columns=null);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>2,
					    'text'=>"DB insert exception",
					    'data'=>array("data"=>$data),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			$new_group_id = $db->insert_id;

			if($insert_ok && $new_group_id){
				$result = true;
			}
			else {
				$result=false;
			}

		}

		// CASE 2: Other groups exist, and user has set this group to be the new default
		// =============================================================================
		elseif( ($data['is_default'] == true) && ($groups_count != 0) ){

			// Find $group_id of current default group

			$args = array(
					array("col"=>"is_default", "op"=>"=", "val"=>"1")
			);
			$columns = array("mode"=>"include", "col"=>"group_id" );
			$ctrl = array("format"=>"var");

			try{
				$old_default = $db->runSelectQuery(self::$struct, $args, $columns, $ctrl);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>3,
					    'text'=>"DB select exception",
					    'data'=>array("args"=>$args, "columns"=>$columns, "ctrl"=>$ctrl),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

			// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

			try{
				$started_transaction = $db->beginTransaction();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>2,
					    'text'=>"beginTransaction exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			if($started_transaction){

				// Insert new group into the db
				try{
					$insert_ok = $db->runInsertQuery(self::$struct, $data, $columns=null);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>3,
						    'text'=>"DB insert exception",
						    'data'=>$data,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				$new_group_id = $db->insert_id;

				// Remove remove default flag from old group
				$unset_default = array("is_default"=>false);
				try{
					$unset_ok = $db->runUpdateQueryCol(self::$struct, $unset_default, "group_id", "=", $old_default);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>4,
						    'text'=>"DB update exception",
						    'data'=>array("data"=>$unset_default, "col"=>"group_id", "op"=>"=", "val"=>$old_default),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				// If all queries were successful, commit the transaction
				if($insert_ok && $new_group_id && $unset_ok){

					try{
						$result = $db->commitTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>5,
							    'text'=>"commitTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
				}
				else {
					//echo "\ninsert_ok:$insert_ok new_group_id: $new_group_id old_default:$old_default unset_ok:$unset_ok";
					try{
						$db->rollbackTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>6,
							    'text'=>"rollbackTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
					$result=false;
				}

			}
			else {
				$result = false;
			}

			// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		}

		// CASE 3: Other groups exist, and user has not set this group to be the default
		// =============================================================================
		else {

			// Insert new group into the db
			try{
				$insert_ok = $db->runInsertQuery(self::$struct, $data, $columns=null);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>7,
					    'text'=>"DB insert exception",
					    'data'=>array("data"=>$data),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			$new_group_id = $db->insert_id;

			if($insert_ok && $new_group_id){
				$result = true;
			}
			else {
				$result=false;
			}

		}

		// Update the cache
		// =========================================
		if($new_group_id && $result){

			try{
				self::loadCache();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>4,
					    'text'=>"loadCache exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

			$this->cache["ids"][$data['name']] = $new_group_id;

			if($data['is_default']){
				$this->cache["default_id"] = $new_group_id;
			}

			try{
				$cache_ok = self::saveCache();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>5,
					    'text'=>"saveCache exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

			if($cache_ok){
				return $new_group_id;
			}
			else {
				return false;
			}

		}
		else {
			return false;
		}

	}


	/**
	 * Edits fields for an existing group, given the group's id
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $group_id | id of the group
	 * @param string $name | name of group (max 32 chars)
	 * @param string $title | title of group (max 128 chars)
	 * @param string $caption | caption of group
	 * @param bool $is_default | if set true, this group is the default group
	 * @param int $icon | media_id of image to use as icon for the group
	 * @return bool | False on failure. True on success.
	 */

	public function editGroup($group_id, $name, $title, $caption, $is_default=false, $icon=null){

		// IMPORTANT: Default group flag rules
		// ==========================================
		// 1) Only one group can be the default group
		// 2) There must always be a default group


		global $fox;

		$data = array(	"name"=>$name,
				"title"=>$title,
				"caption"=>$caption
		);

		if($is_default == true){
			$data["is_default"] = true;
		}

		if($icon){
			$data["icon"] = $icon;
		}


		// Get the column values for the current group
		// ==========================================================

		$db = new FOX_db();

		$columns = array("mode"=>"exclude", "col"=>array("group_id") );
		$ctrl = array("format"=>"col");

		try{
			$old = $db->runSelectQueryCol(self::$struct, "group_id", "=", $group_id, $columns, $ctrl);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>4,
				    'text'=>"DB select exception",
				    'data'=>array( "col"=>"group_id", "op"=>"=", "val"=>$group_id,"columns"=>$columns, "ctrl"=>$ctrl),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}

		// CASE 1: User has set $is_default FALSE and this is currrently the DEFAULT group
		// =============================================================================
		if( ($data["is_default"] != true) && ($old["is_default"] == true) ){

			// There always has to be a default group
			return false;
		}

		// CASE 2: User is not setting this group to be the default group
		// =============================================================================
		if( $data["is_default"] != true ){

			// Update the group's data
			try{
				$group_update_ok = $db->runUpdateQueryCol(self::$struct, $data, "group_id", "=", $group_id);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>5,
					    'text'=>"DB update exception",
					    'data'=>array( "data"=>$data, "col"=>"group_id", "op"=>"=", "val"=>$group_id),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			if($group_update_ok){
				$result = true;
			}
			else {
				$result=false;
			}
		}

		// CASE 3: User is setting this group to be the default group
		// =============================================================================
		else {

			// Find $group_id of current default group
			$args = array(
					array("col"=>"is_default", "op"=>"=", "val"=>true)
			);

			$columns = array("mode"=>"include", "col"=>"group_id" );
			$ctrl = array("format"=>"var");

			try{
			    $old_default = $db->runSelectQuery(self::$struct, $args, $columns, $ctrl);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>6,
					    'text'=>"DB select exception",
					    'data'=>array( "args"=>$args, "columns"=>$columns, "ctrl"=>$ctrl),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

			// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

			try{
				$started_transaction = $db->beginTransaction();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>7,
					    'text'=>"beginTransaction exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			if( $started_transaction ){

				// Update the group's fields, setting its default flag
				try{
					$group_update_ok = $db->runUpdateQueryCol(self::$struct, $data, "group_id", "=", $group_id);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>8,
						    'text'=>"DB update exception",
						    'data'=>array( "data"=>$data, "col"=>"group_id", "op"=>"=", "val"=>$group_id),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				// Remove remove default flag from old group
				$unset_default = array("is_default"=>false);
				try{
					$default_update_ok = $db->runUpdateQueryCol(self::$struct, $unset_default, "group_id", "=", $old_default);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>9,
						    'text'=>"DB update exception",
						    'data'=>array( "data"=>$unset_default, "col"=>"group_id", "op"=>"=", "val"=>$old_default),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				// If all queries were successful, commit the transaction
				if($group_update_ok && $default_update_ok){

					try{
						$result = $db->commitTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>10,
							    'text'=>"commitTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
				}
				else {
					try{
						$db->rollbackTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>11,
							    'text'=>"rollbackTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
					$result=false;
				}

			}
			else {
				$result = false;
			}

			// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		}

		if($result){

			// If any values stored in the cache have been changed, update the cache
			if( ($old["is_default"] != $data["is_default"]) || ($old["name"] != $data["name"]) ) {

				try{
					self::loadCache();
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>12,
						    'text'=>"loadCache exception",
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				if($old["name"] != $data["name"]){

					unset($this->cache["ids"][$old["name"]]);
					$this->cache["ids"][$name] = $group_id;
				}

				if($old["is_default"] != $data["is_default"]){

					$this->cache["default_id"] = $group_id;
				}

				try{
					$cache_ok = self::saveCache();
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>13,
						    'text'=>"saveCache exception",
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				return $cache_ok;
			}
			else {
				return true;
			}

		}
		else {
			return false;
		}


	}


	/**
	 * Deletes a single group, given its group_id
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $group_id | id of the group
	 * @return bool | False on failure. True on success.
	 */

	public function deleteGroup($group_id){

		global $fox;
		$db = new FOX_db();

		$columns = array("mode"=>"include", "col"=>array("is_default", "name") );
		$ctrl = array("format"=>"row_array");
		try{
			$group = $db->runSelectQueryCol(self::$struct, "group_id", "=", $group_id, $columns, $ctrl);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB select exception",
				    'data'=>array( "data"=>$data, "col"=>"group_id", "op"=>"=", "val"=>$group_id, "columns"=>$columns, "ctrl"=>$ctrl),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}

		// If the group we're trying to delete is the default group, reject the action. There must *always* be a
		// default group on the system. If the admin wants to delete the default group, they have to make
		// another group the default group first.

		if($group["is_default"] == true){
			//echo "\nclass.user.group.types::deleteGroup() - attempted delete on default group\n";
			return false;
		}

		// Trap trying to delete a nonexistent group
		if(!$group){
			//echo "\nclass.user.group.types::deleteGroup() - attempted delete on nonexistent group: $group_id \n";
			return false;
		}

		// Get the user_id of every user in the group we're deleting
		$columns = array("mode"=>"include", "col"=>"user_id" );
		$ctrl = array("format"=>"col");
		try{
			$user_ids = $db->runSelectQueryCol(FOX_uGroupMember::_struct(), "group_id", "=", $group_id, $columns, $ctrl);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>2,
				    'text'=>"DB select exception",
				    'data'=>array( "data"=>$data, "col"=>"group_id", "op"=>"=", "val"=>$group_id, "columns"=>$columns, "ctrl"=>$ctrl),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}

		// CASE 1: There are users that are members of the group
		// ===============================================================================
		if($user_ids){

			// Load all of the groups that each user is currently in, except
			// the group we're removing them from
			$args = array(
					array("col"=>"user_id", "op"=>"=", "val"=>$user_ids),
					array("col"=>"group_id", "op"=>"!=", "val"=>$group_id),
			);

			$ctrl = array(	"format"=>"array_key_array_grouped", "key_col"=>array("user_id", "group_id") );

			try{
				$in_groups = $db->runSelectQuery(FOX_uGroupMember::_struct(), $args, $columns=null, $ctrl);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>3,
					    'text'=>"DB select exception",
					    'data'=>array( "args"=>$args, "ctrl"=>$ctrl),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}

			// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

			try{
				$started_transaction = $db->beginTransaction();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>4,
					    'text'=>"beginTransaction exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
			if($started_transaction){

				$keys_ok = true;
				try{
					$gk = new FOX_uGroupKeyRing();
				}
				catch(FOX_exception $child){

					throw new FOX_exception(
						array(
						    'numeric'=>2,
						    'text'=>"FOX_uGroupKeyRing constructor exception",
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				foreach($user_ids as $user){

					// Get the combined keyring of all the user's other groups
					try{
						$keep_keys = $gk->getKeys($in_groups[$user]);
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>5,
							    'text'=>"FOX_uGroupKeyRing getKeys exception",
							    'data'=>array( "user"=>$in_groups[$user]),
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
					// Get the keyring of the group we're removing the user from
					try{
						$drop_keys = $gk->getKeys($group_id);
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>6,
							    'text'=>"FOX_uGroupKeyRing getKeys exception",
							    'data'=>array( "group_id"=>$group_id),
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
					// Intersect the $keep_keys and $drop_keys arrays to get
					// a list of keys we need to revoke from the user
					if($keep_keys && $drop_keys){
						$revoke_keys = array_diff($drop_keys, $keep_keys);
					}
					else{
						$revoke_keys = $drop_keys;
					}

					// Revoke all the keys we previously calculated
					if($revoke_keys){
						$ks = new FOX_uKeyRing();
						try{
							$revoke_ok = $ks->revokeKey($user, $revoke_keys);
						}
						catch(FOX_exception $child){

							throw new FOX_exception(array(
								    'numeric'=>7,
								    'text'=>"FOX_uKeyRing revokeKeys exception",
								    'data'=>array( "user"=>$user, "revoke_keys"=>$revoke_keys),
								    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								    'child'=>$child
								    )
							);
						}
						if(!$revoke_ok){
							$keys_ok = false;
						}
					}
					else {
						// Handle no keys to revoke
						$keys_ok = true;
					}

				}
				unset($user);

				// Because we are inside a transaction, we have to directly delete items from
				// the other class's db tables. If we deleted items using the other class's
				// functions, the other classes would remove them from their caches before we
				// could confirm all steps in the transaction were successful.
				// ============================================================================

				// Drop the group-user pairs from the group members table
				$args = array(
						array("col"=>"group_id", "op"=>"=", "val"=>$group_id)
				);
				try{
					$gm_ok = $db->runDeleteQuery(FOX_uGroupMember::_struct(), $args);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>8,
						    'text'=>"DB delete exception",
						    'data'=>$args,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}

				// Drop the group-key pairs from the group keyring table
				$args = array(
						array("col"=>"group_id", "op"=>"=", "val"=>$group_id)
				);
				try{
					$gk_ok = $db->runDeleteQuery(FOX_uGroupKeyRing::_struct(), $args);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>9,
						    'text'=>"DB delete exception",
						    'data'=>$args,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}

				// Drop the group from the group types table
				$args = array(
						array("col"=>"group_id", "op"=>"=", "val"=>$group_id)
				);
				try{
					$gt_ok = $db->runDeleteQuery(self::$struct, $args);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>10,
						    'text'=>"DB delete exception",
						    'data'=>$args,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				// Update the cache
				if( $keys_ok && ($gm_ok !== false) && ($gk_ok !== false) && $gt_ok ){	// Handle groups with no members and
													// groups with no keys returning (int)0
					try{
						$commit_ok = $db->commitTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>11,
							    'text'=>"commitTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
					if($commit_ok){

						// Because we directly modified other class's db tables, we have to
						// flush the cache for the affected classes
						try{
							$fox->cache->flushNamespace("FOX_uGroupMember");
						}
						catch(FOX_exception $child){

							throw new FOX_exception(
								array(
								    'numeric'=>12,
								    'text'=>"FOX_uGroupMember flushNamespace exception",
								    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								    'child'=>$child
								    )
							);
						}
						try{
							$fox->cache->flushNamespace("FOX_uGroupKeyRing");
						}
						catch(FOX_exception $child){

							throw new FOX_exception(
								array(
								    'numeric'=>13,
								    'text'=>"FOX_uGroupKeyRing flushNamespace exception",
								    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								    'child'=>$child
								    )
							);
						}
						// Load, update, writeback
						try{
							self::loadCache();
						}
						catch(FOX_exception $child){

							throw new FOX_exception(array(
								    'numeric'=>14,
								    'text'=>"FOX_uGroupKeyRing getKeys exception",
								    'data'=>array( "user"=>$group_id),
								    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								    'child'=>$child
								    )
							);
						}
						unset($this->cache["ids"][$group["name"]]);
						$cache_ok = self::saveCache();

						return $cache_ok;

					}
					else {
						return false;
					}
				}

			}
			else {
				// If we couldn't start a transaction, return false
				return false;
			}

			// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		}

		// CASE 2: No users are members of the group
		// ===============================================================================
		else {

			// @@@@@@ BEGIN TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

			try{
				$started_transaction = $db->beginTransaction();
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>15,
					    'text'=>"beginTransaction exception",
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}


			if($started_transaction){

				// Because we are inside a transaction, we have to directly delete items from
				// the other class's db tables. If we deleted items using the other class's
				// functions, the other classes would remove them from their caches before we
				// could confirm all steps in the transaction were successful.
				// ============================================================================

				// Drop the group-key pairs from the group keyring table
				$args = array(
						array("col"=>"group_id", "op"=>"=", "val"=>$group_id)
				);
				try{
					$gk_ok = $db->runDeleteQuery(FOX_uGroupKeyRing::_struct(), $args);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>16,
						    'text'=>"DB delete exception",
						    'data'=>$args,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}

				// Drop the group from the group types table
				$args = array(
						array("col"=>"group_id", "op"=>"=", "val"=>$group_id)
				);
				try{
					$gt_ok = $db->runDeleteQuery(self::$struct, $args);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>17,
						    'text'=>"DB delete exception",
						    'data'=>$args,
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				// Update the cache
				if( ($gk_ok !== false) && $gt_ok ){	// Handle groups with no keys
									// returning (int)0

					try{
						$commit_ok = $db->commitTransaction();
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>18,
							    'text'=>"commitTransaction exception",
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
					if($commit_ok){

						// Because we directly modified another class's db table, we
						// have to flush the cache for the affected class
						try{
							$fox->cache->flushNamespace("FOX_uGroupKeyRing");
						}
						catch(FOX_exception $child){

							throw new FOX_exception(
								array(
								    'numeric'=>2,
								    'text'=>"FOX_uGroupKeyRing flushNamespace exception",
								    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								    'child'=>$child
								    )
							);
						}
						// Load, update, writeback
						try{
							self::loadCache();
						}
						catch(FOX_exception $child){

							throw new FOX_exception(array(
								    'numeric'=>19,
								    'text'=>"loadCache exception",
								    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								    'child'=>$child
								    )
							);
						}
						unset($this->cache["ids"][$group["name"]]);
						try{
							$cache_ok = self::saveCache();
						}
						catch(FOX_exception $child){

							throw new FOX_exception(array(
								    'numeric'=>20,
								    'text'=>"saveCache exception",
								    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								    'child'=>$child
								    )
							);
						}
						return $cache_ok;

					}
					else {
						return false;
					}
				}

			}
			else {
				// If we couldn't start a transaction, return false
				return false;
			}

			// @@@@@@ END TRANSACTION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

		}

		// It might be possible to do this using a sophisticated query

		// Remove all keys granted by the group, from every user on the site, unless another group grants
		// the key, and the user is a member of that other group
		// ========================================================
		// DELETE kst
		// FROM user_keystore_table AS kst
		// INNER JOIN group_members_table AS gmt ON kst.user_id = gmt.user_id	    // user has to be a member of the deleted group
		// INNER JOIN group_keyring_table AS gkt ON gmt.group_id = gkt.group_id	    // key has to be granted by the deleted group
		// WHERE kst.key_id NOT IN (SELECT key_id
		//			    FROM group_keyring_table AS gkt2
		//			    INNER JOIN group_members_table AS gmt2 ON gkt2.group_id = gmt2.group_id
		//			    WHERE gmt2.group_id != gmt.group_id	    // where the key does not belong to another group
		//			    AND gmt2.user_id = gmt.user_id )	    // and the user is a member of that group
		// AND gkt.group_id = [this group]
		// AND gmt.group_id = [this group]

		// ...It also might be possible to do this using MySQL "foreign keys"

	}


	/**
	 * Returns all keys currently stored in the db
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return bool/array | False on failure. Array of keys on success.
	 */

	public function getAll() {


		$result = array(

				"7"=>"Administrators",
				"24"=>"Members",
				"41"=>"Spammers",
				"26"=>"Moderator",
				"45"=>"Sponsor"

		);

		return $result;

	}



} // End of class FOX_uGroupType


/**
 * Hooks on the plugin's install function, creates database tables and
 * configuration options for the class.
 *
 * @version 1.0
 * @since 1.0
 */

function install_FOX_uGroupType(){

	$cls = new FOX_uGroupType();
	
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
add_action( 'fox_install', 'install_FOX_uGroupType', 2 );


/**
 * Hooks on the plugin's uninstall function. Removes all database tables and
 * configuration options for the class.
 *
 * @version 1.0
 * @since 1.0
 */

function uninstall_FOX_uGroupType(){

	$cls = new FOX_uGroupType();
	
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
add_action( 'fox_uninstall', 'uninstall_FOX_uGroupType', 2 );

?>