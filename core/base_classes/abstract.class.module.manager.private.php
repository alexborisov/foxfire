<?php

/**
 * FOXFIRE PRIVATE MODULE MANAGER BASE CLASS
 * Handles registration and configuration for modules that are only used within a single plugin
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Modules
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

abstract class FOX_moduleManager_private_base extends FOX_moduleManager_base {


    	var $process_id;		    // Unique process id for this thread. Used by FOX_db_base for cache
					    // locking. Loaded by descendent class.

	var $cache;			    // Main cache array for this class

	var $mCache;			    // Local copy of memory cache singleton. Used by FOX_db_base for cache
					    // operations. Loaded by descendent class.

	var $admin_modules = array();	    // Array of all module php_classes as loaded on the admin screen

	var $targets = array();		    // Array of all available targets for the template the module
					    // is currently using (or default views if not supplied by template)

	var $views = array();		    // Array of all available views for the template the module
					    // is currently using (or default roles if not supplied by template)

	var $caps = array();		    // Array of all available capability locks for the template the module
					    // is currently using (or default roles if not supplied by template)

	var $thumbs = array();		    // Array of thumbnail configuration data (or default values if not supplied
					    // by template)

	
	/* ================================================================================================================
	 *	Cache Strategy: "monolithic"
	 *
	 *	=> ARR array $cache | Main cache array
	 *
 	 *	    => ARR array $module_id | Main datastore
	 *		=> ARR int '' | Array index
	 *		    => VAL string $slug | The module's slug
	 *		    => VAL string $name | The module's name
	 *		    => VAL string $php_class | The module's PHP class
	 *		    => VAL bool $active | True if active. False if not.
	 *
 	 *	    => ARR array $php_class | PHP class dictionary
	 *		=> KEY string $php_class | Name of PHP class
	 *		    => VAL int $module_id | The module's id
	 *
  	 *	    => ARR array $slug | Slug dictionary
	 *		=> KEY string $slug | Name of the module's slug
	 *		    => VAL int $module_id | The module's id
	 *
   	 *	    => ARR array $active_modules | Active modules dictionary
	 *		=> KEY int $module_id | The module's id
	 *		    => VAL bool [always true] (presence of key indicates module is active)
	 *
	 *	    => VAL bool $all_cached | True if the cache has authority (all modules loaded from db)
	 * */

	// ================================================================================================================


	/**
	 * Registers a module with the module manager
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $slug | Module slug name. Max 16 characters. Must be unique.
	 * @param string $name | Module name. Max 32 characters.
	 * @param string $php_class | Module PHP class. Max 255 characters. Must be unique.
	 * @param bool $active | True to auto-activate the module (used during unit testing)
	 *
	 * @return int | Exception on failure. Module id on success.
	 */

	public function register($slug, $name, $php_class, $active=false) {


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		if( class_exists($php_class) ){	    // This check is necessary so we can run unit tests
						    // without creating huge numbers of mock classes
			$this->admin_modules[] = new $php_class();
		}

		// Check the slug name
		// ========================================================

		try {
			$slug_exists = self::getBySlug($slug);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error checking slug name",
				'data'=> array('slug'=>$slug, 'name'=>$name, 'php_class'=>$php_class, 'active'=>$active),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// Check the class name
		// ========================================================

		try {
			$class_exists = self::getByClass($name);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error checking class name",
				'data'=> array('slug'=>$slug, 'name'=>$name, 'php_class'=>$php_class, 'active'=>$active),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// Add it to the system
		// ========================================================

		if( !$slug_exists && !$class_exists ){

			try {
				$module_id = self::add($slug, $name, $php_class, $active);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error adding module to database",
					'data'=> array('slug'=>$slug, 'name'=>$name, 'php_class'=>$php_class, 'active'=>$active),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

		}
		else {
			$module_id = $slug_exists['module_id'];
		}

		return $module_id;

	}


	/**
	 * Installs a module on the system and generates a module_id for it
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $slug | Machine slug for the module. [a-z AZ 09 _-] only. Max 16 chars.
	 * @param string $name | Human readable name of the module. Max 128 chars
	 * @param string $php_class | PHP class for the module.
	 * @param bool $active | True to set this module as active. False to set it as inactive.
	 *
	 * @return bool | Exception on failure. Id of new row on success.
	 */

	public function add($slug, $name, $php_class, $active=false){


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		$data = array( array( "slug"=>$slug, "name"=>$name, "php_class"=>$php_class, "active"=>$active) );

		try {
			$result = self::addMulti($data);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"addMulti functon reported an error",
				'data'=> array('data'=>$data),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		return $result[0];

	}



} // End of class FOX_moduleManager_private_base



?>