<?php

/**
 * BP-MEDIA CONFIGURATION CLASS
 * Handles all configuration settings for the plugin
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package FoxFire
 * @subpackage Config
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

class FOX_config extends FOX_dataStore_monolithic_L3_base {


    	var $process_id;		    // Unique process id for this thread. Used by FOX_db_base for cache
					    // locking. Inherited from FOX_dataStore_monolithic_L3_base.

	var $mCache;			    // Local copy of memory cache singleton

	var $install_classes_loaded;	    // True if the base install classes have been loaded
	var $uninstall_classes_loaded;	    // True if the base uninstall classes have been loaded

	var $key_delimiter = "^";	    // The character the class uses to split keys into "tree", "branch", and "node"
					    // when parsing returned forms.

	// ============================================================================================================ //

	public static $struct = array(

	    "table" => "fox_sys_config_data",
	    "engine" => "InnoDB",
	    "cache_namespace" => "FOX_config",
	    "cache_strategy" => "monolithic",
	    "cache_engine" => array("memcached", "redis", "apc"),
	    "columns" => array(

		"tree"=>    array(  "php"=>"string",    "sql"=>"varchar",   "format"=>"%s", "width"=>32,	"index"=>array("name"=>
				    "tree_branch_node", "col"=>array("tree", "branch", "node"), "index"=>"PRIMARY"), "this_row"=>true),

		"branch"=>  array(	"php"=>"string",    "sql"=>"varchar",   "format"=>"%s", "width"=>32,    "flags"=>"NOT NULL",    "auto_inc"=>false,  "default"=>null,    "index"=>true),
		"node"=>    array(	"php"=>"string",    "sql"=>"varchar",   "format"=>"%s", "width"=>32,    "flags"=>"NOT NULL",    "auto_inc"=>false,  "default"=>null,    "index"=>true),
		"filter"=>  array(	"php"=>"string",    "sql"=>"varchar",   "format"=>"%s", "width"=>64,   "flags"=>"NOT NULL",    "auto_inc"=>false,  "default"=>null,    "index"=>true),
		"val"=>	    array(	"php"=>"serialize", "sql"=>"longtext",  "format"=>"%s", "width"=>null,  "flags"=>"",		"auto_inc"=>false,  "default"=>null,    "index"=>false),
		"ctrl"=>    array(	"php"=>"serialize", "sql"=>"longtext",  "format"=>"%s", "width"=>null,  "flags"=>"",		"auto_inc"=>false,  "default"=>null,    "index"=>false)
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
         * Processes config variables passed via an HTML form
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param array $post | HTML form array
         * @return int | Exception on failure. Number of rows changed on success.
         */

	public function processHTMLForm($post){


		if( empty($post['key_names']) ) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"No key names posted with form",
				'data'=> $post,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		$san = new FOX_sanitize();

		// Explode fields array into individual key names
		$options = explode(',', stripslashes($post['key_names']));

		// Convert the encoded full name into its corresponding $tree, $branch,
		// and $key, then add the group to the query array
		// ====================================================================

		$query_keys = array();

		foreach($options as $option) {

			$full_name = explode($this->key_delimiter, $option);

			// Process the raw form strings into proper key names
			$raw_tree = trim( $full_name[0] );
			$raw_branch = trim( $full_name[1] );
			$raw_key = trim( $full_name[2] );

			// Passed by reference
			$tree_valid = null; $branch_valid = null; $key_valid = null;
			$tree_error = null; $branch_error = null; $key_error = null;

			try {
				$tree = $san->keyName($raw_tree, null, $tree_valid, $tree_error);
				$branch = $san->keyName($raw_branch, null, $branch_valid, $branch_error);
				$key = $san->keyName($raw_key, null, $key_valid, $key_error);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Error in sanitizer function",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

			if(!$tree_valid){

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Called with invalid tree name",
					'data'=>array('raw_tree'=>$raw_tree, 'san_error'=>$tree_error),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}
			elseif(!$branch_valid){

				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Called with invalid branch name",
					'data'=>array('raw_branch'=>$raw_branch, 'san_error'=>$branch_error),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}
			elseif(!$key_valid){

				throw new FOX_exception( array(
					'numeric'=>5,
					'text'=>"Called with invalid key name",
					'data'=>array('raw_key'=>$raw_key, 'san_error'=>$key_error),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));
			}

			$query_keys[$tree][$branch][$key] = true;

		}
		unset($option, $tree, $raw_tree, $branch, $raw_branch, $key, $raw_key);
		unset($tree_error, $branch_error, $key_error, $tree_valid, $branch_valid, $key_valid);


		// Fold all requested keys into the minimum number of queries possible,
		// then fetch the data for those keys
		// ====================================================================

		foreach($query_keys as $tree => $query_branch_array){

			foreach($query_branch_array as $branch => $query_keys_array){

				$keys = array_keys($query_keys_array);

				// Note: we don't validate if the requested keys exist at this
				// stage because get() only tells us that "one or more" keys
				// weren't valid. Its much more useful to list the actual missing
				// keys, which we do in the next stage of the algorithm.

				try {
					self::get($tree, $branch, $keys);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>6,
						'text'=>"Error in self::get()",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));
				}
			}
		}
		unset($tree, $query_branch_array, $branch, $query_keys_array, $keys);


		// Iterate through all of the requested keys, cast the submitted form
		// data for each key to the format specified in the key's db entry
		// ====================================================================

		$rows_changed = 0;

		foreach($query_keys as $tree => $current_branch_array){

			foreach($current_branch_array as $branch => $current_keys_array){

				foreach($current_keys_array as $key => $fake_var){

					$filter = $this->cache["keys"][$tree][$branch][$key]["filter"];
					$ctrl = $this->cache["keys"][$tree][$branch][$key]["ctrl"];

					if(!is_string($filter) ){

						throw new FOX_exception( array(
							'numeric'=>7,
							'text'=>"Trying to set nonexistent key. Tree: $tree | Branch: $branch | Key: $key",
							'data'=>array('current_keys_array'=>$current_keys_array,
								      'tree'=>$tree, 'branch'=>$branch, 'key'=>$key),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>null
						));
					}

					// Remove any escaping PHP has added to the posted form value
					$post_key = $tree . $this->key_delimiter . $branch . $this->key_delimiter . $key;
					$post[$post_key] = FOX_sUtil::formVal($post[$post_key]);


					// Run key value through the specified filter
					// =====================================================

					$filter_valid = null; $filter_error = null; // Passed by reference

					try {
						$new_val = $san->{$filter}($post[$post_key], $ctrl, $filter_valid, $filter_error);
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>8,
							'text'=>"Error in filter function",
							'data'=>array('filter'=>$filter, 'val'=>$post[$post_key], 'ctrl'=>$ctrl,
								      'filter_valid'=>$filter_valid, 'filter_error'=>$filter_error),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));
					}

					if(!$filter_valid){

						throw new FOX_exception( array(
							'numeric'=>9,
							'text'=>"Filter function reports value data isn't valid",
							'data'=>array('filter'=>$filter, 'val'=>$post[$post_key], 'ctrl'=>$ctrl,
								      'filter_valid'=>$filter_valid, 'filter_error'=>$filter_error),
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>null
						));
					}

					// If the new value doesn't match the stored value, update the key
					// ====================================================================

					if( $new_val != $this->cache["keys"][$tree][$branch][$key]["val"] ){

						try {
							$rows_changed += self::setNode($tree, $branch, $key, $new_val);
						}
						catch (FOX_exception $child) {

							throw new FOX_exception( array(
								'numeric'=>10,
								'text'=>"Error setting key. Tree: $tree | Branch: $branch | Key: $key",
								'data'=>array('tree'=>$tree, 'branch'=>$branch,
									      'key'=>$key, 'new_val'=>$new_val),
								'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
								'child'=>$child
							));
						}
					}


				}   // ENDOF: foreach($current_keys_array as $key => $fake_var)
				unset($key, $fake_var);


			} // ENDOF: foreach($current_branch_array as $branch => $current_keys_array)
			unset($branch, $current_keys_array);


		} // ENDOF: foreach($query_keys as $tree => $current_branch_array)
		unset($tree, $current_branch_array);


		return $rows_changed;



	} // ENDOF: function processHTMLForm


	/**
         * Resets the class's print_keys array, making it ready to accept a new batch of keys. This method
	 * MUST be called at the beginning of each admin form to clear old keys from the singleton.
         *
         * @version 0.1.9
         * @since 0.1.9
         */

	public function initKeysArray(){

		$this->print_keys = array();
	}


	/**
         * Creates a composite keyname given the keys's tree, branch, and name. Adds the
	 * composited key name to the $print_keys array for printing the form's keys array.
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param string $tree | Tree name
	 * @param string $branch | Branch name
	 * @param string $key | Key name
	 *
         * @return ECHO | ECHO composited key name on success.
         */

	public function printKeyName($tree, $branch, $key){


		echo 'name="' . self::getKeyName($tree, $branch, $key) . '"';

	}

	public function getKeyName($tree, $branch, $key){

		$key_name = ($tree . $this->key_delimiter . $branch . $this->key_delimiter . $key);

		// Add formatted key name to the $keys array
		$this->print_keys[$key_name] = true;

		return $key_name;

	}


	/**
         * Prints a key's value
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param string $tree | Tree name
	 * @param string $branch | Branch name
	 * @param string $key | Key name
	 * @param string $validate | If set true, throws an error if key doesn't exist in datastore.
	 *
         * @return ECHO | ECHO exception on failure. ECHO composited key name on success.
         */

	public function printKeyVal($tree, $branch, $key, $validate=false){


		$result = 'value="';
		$is_valid = false; // Passed by reference


		try {
			$result .= self::getNodeVal($tree, $branch, $key, $is_valid);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error in self::getNodeVal()",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		if( ($validate == true) && !$is_valid ){

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Key doesn't exist",
				'data'=>array('tree'=>$tree, 'branch'=>$branch, 'key'=>$key),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		else {
			echo $result . '"';
		}

	}


	/**
         * Creates a composited string used to print a hidden field containing all of the key names enqueued
	 * using keyName() or getKeyName()
         *
         * @version 0.1.9
         * @since 0.1.9
	 *
	 * @param string $field_name | (optional) name to use for the form field
         * @return ECHO | composited hidden form field string
         */

	public function printKeysArray($field_name=null){

		echo self::getKeysArray($field_name);
	}

	public function getKeysArray($field_name=null){

		// Handle no $field_name being passed

		if(!$field_name){
		    $field_name = "key_names";
		}

                $result = '<input type="hidden" name="' . $field_name . '" value="';

		$keys_left = count( $this->print_keys) - 1;

		foreach( $this->print_keys as $key_name => $value) {

			$result .= $key_name;

			if($keys_left != 0){
				$result .= ", ";
				$keys_left--;
			}
		}

                $result .= '" />';

		return $result;

	}


	/**
         * Places the system in "install mode" by loading all the base install classes
	 * from the /core/config/install_base folder
         *
         * @version 0.1.9
         * @since 0.1.9
         */

	public function installMode(){


		if(!$this->install_classes_loaded){

			$base_install_classes = glob( FOX_PATH_BASE .'/core/config/install_base/*.php');

			foreach ( $base_install_classes as $path ){

				if( file_exists($path) ){
					include_once ($path);
				}
			}

			$this->install_classes_loaded = true;

		}

	}


	/**
         * Places the system in "uninstall mode" by loading all the base uninstall classes
	 * from the /core/config/uninstall_base folder
         *
         * @version 0.1.9
         * @since 0.1.9
         */

	public function uninstallMode(){

		if(!$this->uninstall_classes_loaded){

			$base_uninstall_classes = glob( FOX_PATH_BASE .'/core/config/uninstall_base/*.php');

			foreach ( $base_uninstall_classes as $path ){

				if( file_exists($path) ){
					include_once ($path);
				}
			}

			$this->uninstall_classes_loaded = true;

		}

	}



} // End of class FOX_config


/**
 * Hooks on the plugin's install function, creates database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function install_FOX_config(){

	$cls = new FOX_config();
	$cls->install();
}
add_action( 'fox_install', 'install_FOX_config', 2 );


/**
 * Hooks on the plugin's uninstall function. Removes all database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function uninstall_FOX_config(){

	$cls = new FOX_config();
	$cls->uninstall();
}
add_action( 'fox_uninstall', 'uninstall_FOX_config', 2 );


?>