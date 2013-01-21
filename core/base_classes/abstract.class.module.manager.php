<?php

/**
 * FOXFIRE MODULE MANAGER BASE CLASS
 * Handles registration and configuration for Page, Album, Media, Network, and future types of modules
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

abstract class FOX_module_manager_base extends FOX_db_base {


    	var $process_id;		    // Unique process id for this thread. Used by FOX_db_base for cache 
					    // locking. Loaded by descendent class.
	
	var $cache;			    // Main cache array for this class
	
	var $db;			    // Local copy of database singleton
	
	var $mCache;			    // Local copy of memory cache singleton. Used by FOX_db_base for cache 
					    // operations. Loaded by descendent class.	
	
	var $debug_on;			    // Send debugging info to the debug handler	
	var $debug_handler;		    // Local copy of debug singleton	
	
	
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
	 *		    => VAL string $plugin_slug | Slug of plugin that owns the page module
	 *		    => VAL string $module_slug | The page module's slug
	 *		    => VAL string $module_name | The page module's name
	 *		    => VAL string $php_class | The page module's PHP class
	 *		    => VAL bool   $active | True if page module is active. False if not.
	 *
 	 *	    => ARR array $php_class | PHP class dictionary
	 *		=> KEY string $php_class | Name of PHP class
	 *		    => VAL int $module_id | The module's id
	 *
  	 *	    => ARR array $slug | Slug dictionary
	 *		=> KEY string $module_slug | Name of the page module's slug
	 *		    => VAL int $module_id | The page module's id
	 *
   	 *	    => ARR array $active_modules | Active modules dictionary
	 *		=> KEY int $module_id | The module's id
	 *		    => VAL bool [always true] (presence of key indicates module is active)
	 *
	 *	    => VAL bool $all_cached | True if the cache has authority (all modules loaded from db)
	 * */

	// ================================================================================================================


	/**
	 * Initializes the class. This function MUST be called in the __construct() method
	 * of descendent classes. 
	 * 
	 *
	 * @version 1.0
	 * @since 1.0
	 * 
	 * @return bool | Exception on failure. True on success.
	 */

	public function init($args=null){
	    
		
		// Base Path
		// ===========================================================
		
		if(!FOX_sUtil::keyExists('base_path', $args)){
		    
			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Missing base path parameter",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$args
			));    
		}
		
		// Debug events
		// ===========================================================
		
		if(FOX_sUtil::keyExists('debug_on', $args) && ($args['debug_on'] == true) ){
		    
			$this->debug_on = true;
		    
			if(FOX_sUtil::keyExists('debug_handler', $args)){

				$this->debug_handler =& $args['debug_handler'];		    
			}
			else {
				global $fox;
				$this->debug_handler =& $fox->debug_handler;		    		    
			}	    
		}
		else {
			$this->debug_on = false;		    		    
		}
		
		// Database singleton
		// ===========================================================
		
		if(FOX_sUtil::keyExists('db', $args) ){
		    
			$this->db =& $args['db'];		    
		}
		else {
			$this->db = new FOX_db( array('pid'=>$this->process_id) );		    		    
		}
		
		
		// Memory cache singleton
		// ===========================================================
		
		if(FOX_sUtil::keyExists('mCache', $args) ){
		    
			$this->mCache =& $args['mCache'];		    
		}
		else {
			global $fox;
			$this->mCache =& $fox->mCache;		    		    
		}		
			    				
		$this->init = true;
	    
	}
	
	
	/**
	 * Scans each subdirectory in the modules folder, adding loader.php to the include path. When a
	 * module's loader.php file is loaded, the module registers itself with the modules singleton and
	 * becomes live on the system.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return int | Exception on failure. (int) number of modules loaded on success.
	 */

	public function loadAllModules($path=false) {


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		if($path){

			$modules_list = glob( $path . '*');
		}
		else {
			$modules_list = glob( FOX_PATH_BASE .'/modules/' . $this->_offset() . '/*');
		}
		
		$result = 0;
		
		foreach( $modules_list as $module_path ){

			if( file_exists($module_path . "/loader.php") ){

				include_once( $module_path . "/loader.php" );
				$result++;
			}
			else {			    
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Module contains no loader file",
					'data'=>$module_path,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    			    
			}
					
		}
		unset($module_path);

		
		return $result;
		
	}


	/**
	 * Scans each subdirectory in the modules folder, adding loader.php to the include path. When a
	 * module's loader.php file is loaded, the module registers itself with the modules singleton and
	 * becomes live on the system.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool | Exception on failure. True on success.
	 */

	public function loadModule($module_slugs, $base_path=false) {


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		if( empty($module_slugs) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Called with empty slugs parameter",
				'data'=>array('module_slugs'=>$module_slugs),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		elseif( !is_array($module_slugs) ){

			// Handle single string as input
			$module_slugs = array($module_slugs);
		}


		if(!$base_path){

			$base_path =  FOX_PATH_BASE .'/modules/' . $this->_offset() . '/';
		}

		$result = 0;
		
		foreach( $module_slugs as $slug ){

			if( file_exists($base_path . $slug . "/loader.php") ){

				include_once( $base_path . $slug . "/loader.php" );
				$result++;
			}
			else {			    
				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Specified module contains no loader file",
					'data'=>$base_path . $slug ,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));			    			    
			}			
		}
		unset($slug);
		
		return $result;

	}


	/**
	 * Registers a page module with the system. Generates a new $module_id for the 
	 * page module if it doesn't exist on the system. Otherwise returns the page
	 * module's current $module_id.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $plugin_slug | WordPress slug for plugin that owns this page module	 
	 * @param string $module_slug | Module slug name. Max 32 characters. Must be unique.
	 * @param string $module_name | Human readable page module name. Max 32 characters.
	 * @param string $php_class | Module PHP class. Max 255 characters. Must be unique.
	 * @param bool $active | True to auto-activate the module (used during unit testing)
	 *
	 * @return int | Exception on failure. Module id on success.
	 */

	public function register($plugin_slug, $module_slug, $module_name, $php_class, $active=false) {


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

		// Check the PHP class name
		// ========================================================

		try {
			$class_exists = self::getByClass($php_class);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error checking class name",
				'data'=> array(	'plugin_slug'=>$plugin_slug, 'module_slug'=>$module_slug, 
						'module_name'=>$module_name, 'php_class'=>$php_class, 'active'=>$active),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// Add it to the system
		// ========================================================

		if(!$class_exists ){

			try {
				$module_id = self::add($plugin_slug, $module_slug, $module_name, $php_class, $active);
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error adding module to database",
					'data'=> array(	'plugin_slug'=>$plugin_slug, 'module_slug'=>$module_slug, 
							'module_name'=>$module_name, 'php_class'=>$php_class, 'active'=>$active),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

		}
		else {
			$module_id = $class_exists['module_id'];
		}

		
		return $module_id;

	}


	/**
	 * Installs a page module on the system and generates a module_id for it
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string $plugin_slug | WordPress slug for plugin that owns this page module	 
	 * @param string $module_slug | Module slug name. Max 32 characters. Must be unique.
	 * @param string $module_name | Human readable page module name. Max 32 characters.
	 * @param string $php_class | Module PHP class. Max 255 characters. Must be unique.
	 * @param bool $active | True to auto-activate the module (used during unit testing)
	 *
	 * @return int | Exception on failure. Module id on success.
	 */

	public function add($plugin_slug, $module_slug, $module_name, $php_class, $active=false){

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		$data = array( array(	'plugin_slug'=>$plugin_slug, 
					'module_slug'=>$module_slug, 
					'module_name'=>$module_name, 
					'php_class'=>$php_class, 
					'active'=>$active
			));

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


	/**
	 * Installs one or more modules on the system and generates a module_ids for them
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param array $data | Array of modules to add
	 *	=> ARR @param int '' | Array index
	 *	    => VAL @param string $slug | The module's slug
	 *	    => VAL @param string $name | The module's name
	 *	    => VAL @param string $php_class | The module's PHP class
	 *	    => VAL @param bool $active | True if active. False if not.
	 *
	 * @return array | Exception on failure. Array of module id's on success.
	 */

	public function addMulti($data){
	    

		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		// Note that because we're adding a *new* item to the datastore, we don't need
		// to lock the cache. There are no entries in the cache to become out of sync
		// with the db in the event of a failure.

		try {
			$insert_id = $this->db->runInsertQueryMulti($this->_struct(), $data, $columns=null, $ctrl=null);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error writing to database",
				'data'=> array('data'=>$data),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// Load the class cache from the persistent cache
		// =================================================

		try {
			self::loadCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Cache get error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		if( !$insert_id || ($insert_id != (int)$insert_id) ){

			throw new FOX_exception( array(
				'numeric'=>3,
				'text'=>"Database did not return valid insert_id",
				'data'=>array("data"=>$data, "failed_id"=>$insert_id),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		$module_id = $insert_id;
		$result = array();

		// MySQL will return the insert_id of the first row in the insert operation. Each
		// successive row will have a sequential insert_id value incremented by the
		// autoincrement_increment value set in the table definition array

		foreach($data as $row_data){

			$row_data["module_id"] = $module_id;

			$this->cache["module_id"][$module_id] = $row_data;
			$this->cache["php_class"][$row_data["php_class"]] = $module_id;
			$this->cache["slug"][$row_data["slug"]] = $module_id;

			if($row_data["active"] == true ){
				$this->cache["active_modules"][$module_id] = true;
			}

			$result[] = $module_id;
			$module_id += $this->db->auto_increment_increment;
		}
		unset($row_data, $module_id);


		// Write back the class cache to the persistent cache
		// =================================================

		try {
			self::saveCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Cache set error",
				'data'=>array('class_cache'=>$this->cache),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		return $result;

	}
	
	
	/**
	 * Edits a module that already exists on the system.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param array $data |
	 *	=> VAL @param int $module_id | The id of the module
	 *	=> VAL @param string $slug | Machine slug for the module. [a-z AZ 09 _-] only. Max 16 chars.
	 *	=> VAL @param string $name | Human readable name of the module. Max 128 chars
	 *	=> VAL @param bool $active | True if this module is active False if not.
	 *	=> VAL @param string $php_class | PHP class for the module.
	 *
	 * @param bool $unit_test | Unit testing flag. Set true to stop function from throwing errors.
	 *
	 * @return int | Exception on failure. Number of db rows changed on success.
	 */

	public function edit($data) {


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		$this->db = new FOX_db();

		if( empty($data["module_id"]) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Missing module_id",
				'data'=>$data,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}

		$data["module_id"] = (int)$data["module_id"];


		// Get the module's current db entry (required during cache update)
		// =================================================================

		$args = array(
				array("col"=>"module_id", "op"=>"=", "val"=>$data["module_id"] ),
		);
		$ctrl = array("format"=>"row_array");

		try {
			$previous = $this->db->runSelectQuery($this->_struct(), $args, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error reading from database",
				'data'=> array('args'=>$args, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// Lock the current cache namespace
		// =================================================================

		try {
			self::lockCache($this->process_id, 5);
		}
		catch (FOX_exception $child) {

			if($child->getNumeric() == 4){

				// In the future we'll handle this gracefully, but to simplify
				// testing, we currently just throw an exception

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Another thread had exclusive use of the cache namespace",
					'data'=> array('lock_info'=>$child->data),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));

			}
			else {
				throw new FOX_exception( array(
					'numeric'=>4,
					'text'=>"Error locking persitent cache",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}
		}

		// Run the query
		// =================================================================

		$args = array(
				array("col"=>"module_id", "op"=>"=", "val"=>$data["module_id"] ),
		);
		$columns = array("mode"=>"exclude", "col"=>array("module_id") );

		try {
			$rows_changed = $this->db->runUpdateQuery($this->_struct(), $data, $args, $columns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Error writing to database",
				'data'=> array('data'=>$data, 'args'=>$args, 'columns'=>$columns),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// Rebuild the class cache if necessary
		// =============================================================

		// Fill in any fields missing from the $data array with the values that are currently in the db
		$data = wp_parse_args($data, $previous);

		// Update main datastore
		$this->cache["module_id"][$data["module_id"]] = $data;

		// Update php_class dictionary
		if($previous["php_class"] != $data["php_class"] ){

			unset($this->cache["php_class"][$previous["php_class"]]);
			$this->cache["php_class"][$data["php_class"]] = $data["module_id"];
		}

		// Update slug dictionary
		if($previous["php_class"] != $data["php_class"] ){

			unset($this->cache["slug"][$previous["slug"]]);
			$this->cache["slug"][$data["slug"]] = $data["module_id"];
		}

		// Update active_modules dictionary
		if( $previous["active"] != $data["active"] ){

			if($data["active"] == true ){

				$this->cache["active_modules"][$data["module_id"]] = true;
			}
			else {
				unset($this->cache["active_modules"][$data["module_id"]]);
			}
		}

		// Update the persistent cache and release our lock
		// =============================================================

		try {
			self::saveCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>6,
				'text'=>"Cache set error",
				'data'=>$this->cache,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		return (int)$rows_changed;

	}
	
	
	/**
	 * Returns an array containing the class names of all modules that are currently
	 * present in the modules directory
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return array | Exception on failure. Array containing the modules' class names on success.
	 */

	public function getAdminModules() {

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		return $this->admin_modules;
		
	}


	/**
	 * Gets the slug of the currently selected module at the admin screen
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | Exception on failure. Module slug as string on success.
	 */

	public function getSelectedModule() {

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		$san = new FOX_sanitize();

		try {
			$result = $san->slug($_GET['module'], $ctrl=null);
		}
		catch (FOX_Exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Sanitizer function returned an error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		return $result;

	}


	/**
	 * Gets the slug of the currently selected module tab at the admin screen
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return string | Exception on failure. Module tab slug as string on success.
	 */

	public function getSelectedTab() {

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		$san = new FOX_sanitize();

		try {
			$result = $san->slug($_GET['tab'], $ctrl=null);
		}
		catch (FOX_Exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Sanitizer function returned an error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		return $result;

	}


	/**
	 * Loads admin page scripts for all modules.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool | Exception on failure. True on success.
	 */

	public function loadAdminScripts() {

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}	    

		// Check if the modules have been loaded
		// ============================================

		$modules_loaded = count($this->admin_modules);

		if($modules_loaded < 1){

			try {
				self::loadAllModules();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error loading modules",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}
		}

		foreach($this->admin_modules as $module){

			// Get the module
			// ============================================

			try {
				$selected_module = self::getSelectedModule();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Error getting selected module",
					'data'=>array('module'=>$module),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

			// Get the tab
			// ============================================

			try {
				$selected_tab = self::getSelectedTab();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error getting selected tab",
					'data'=>array('module'=>$module),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}


			// If no module being selected, which happens on the first load of the
			// modules page, enqueue the scripts for the first module in the
			// array. This is the module that the modules page will show.

			if(!$selected_module){

				$module->enqueueAdminScripts($selected_tab);
				break;
			}
			elseif( $module->getSlug() == $selected_module){

				$module->enqueueAdminScripts($selected_tab);
				break;
			}

		}
		unset($module);

		return true;

	}


	/**
	 * Loads admin page CSS styles for all modules.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return bool | Exception on failure. True on success.
	 */

	public function loadAdminStyles() {

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		// Check if the modules have been loaded. If not, load them.

		$modules_loaded = count($this->admin_modules);

		if($modules_loaded < 1){

			try {
				self::loadAllModules();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Error loading modules",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}
		}

		foreach($this->admin_modules as $module){

			try {
				$selected_module = self::getSelectedModule();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Error getting selected module",
					'data'=>array('module'=>$module),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

			// Get the tab
			// ============================================

			try {
				$selected_tab = self::getSelectedTab();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error getting selected tab",
					'data'=>array('module'=>$module),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

			// If no module being selected, which happens on the first load of the
			// modules page, enqueue the scripts for the first module in the
			// array. This is the module that the modules page will show.

			if(!$selected_module){

				$module->enqueueAdminStyles($selected_tab);
				break;
			}
			elseif( $module->getSlug() == $selected_module){

				$module->enqueueAdminStyles($selected_tab);
				break;
			}

		}
		unset($module);

		return true;

	}


	/**
	 * Loads module data into the cache
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param array $data | Array of identifiers to cache, or null to cache all rows in the database
	 *	=> VAL @param int/array $module_id | Single module_id as int. Multiple module_ids as array of int.
	 *	=> VAL @param string/array $php_class | Single php_class as string. Multiple php_classes as array of strings.
	 *	=> VAL @param string/array $slug | Single slug as string. Multiple slugs as array of strings.
	 *
	 * @return array | Exception on failure. Array of ints on success.
	 */

	public function load($data=null){


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		$this->db = new FOX_db();

		$args = array();

		if( $data["module_id"]){
			$args[] = array("col"=>"module_id", "op"=>"=", "val"=>$data["module_id"] );
		}
		elseif( $data["php_class"]){
			$args[] = array("col"=>"php_class", "op"=>"=", "val"=>$data["php_class"] );
		}
		elseif( $data["slug"]){
			$args[] = array("col"=>"slug", "op"=>"=", "val"=>$data["slug"] );
		}
		else{
			$all_cached = true;
		}

		// The "key_col" arg is sent in as (string) instead of (array) so that the results
		// formatter includes "module_id" in each returned result

		$ctrl = array("format"=>"array_key_array", "key_col"=>"module_id" );

		try {
			$this->db_result = $this->db->runSelectQuery($this->_struct(), $args, $columns=null, $ctrl);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Error reading from database",
				'data'=> array('data'=>$data, 'ctrl'=>$ctrl),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}


		if($this->db_result){


			// Load the class cache from the persistent cache
			// =================================================

			try {
				self::loadCache();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Cache get error",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

			// Add the values
			// =================================================

			if($all_cached == true){

				$this->cache["all_cached"] = true;
			}

			foreach($this->db_result as $module_id => $row_data){

				$this->cache["module_id"][$module_id] = $row_data;
				$this->cache["php_class"][$row_data["php_class"]] = $module_id;
				$this->cache["slug"][$row_data["slug"]] = $module_id;

				if($row_data["active"] == true ){
					$this->cache["active_modules"][$module_id] = true;
				}
			}

			// Write back the class cache to the persistent cache
			// =================================================

			try {
				self::saveCache();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Cache set error",
					'data'=>array('class_cache'=>$this->cache),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

		}

		return true;

	}


	/**
	 * Returns an array containing all data for all active modules
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return array | Exception on failure. Array of class names on success.
	 */

	public function getActiveModules() {

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		// If the all_cached flag isn't set in the class cache, reload the class
		// cache from the persistent cache.

		if( !FOX_sUtil::keyTrue("all_cached", $this->cache) ){


			// Load the class cache from the persistent cache
			// =================================================

			try {
				self::loadCache();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Cache get error",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

			// If the all_cached flag isn't set in the class cache after reloading from
			// the persistent cache, load both caches from the db.

			if( !FOX_sUtil::keyTrue("all_cached", $this->cache) ){

				try {
					self::load($data=null);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Load error",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));
				}
			}

		}

		// At this point, all modules should be in the cache
		if( FOX_sUtil::keyExists("active_modules", $this->cache) ){

			$result = array_intersect_key($this->cache["module_id"], $this->cache["active_modules"]);
		}
		else {
			// Handle all modules on the system being disabled (or no modules installed on system)
			$result = array();
		}

		return $result;

	}


	/**
	 * Returns an array containing all data for all modules
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return array | Exception on failure. Array of class names on success.
	 */

	public function getAllModules() {

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		// If the all_cached flag isn't set in the class cache, reload the class
		// cache from the persistent cache.

		if( !FOX_sUtil::keyTrue("all_cached", $this->cache) ){

			// Load the class cache from the persistent cache
			// =================================================

			try {
				self::loadCache();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>1,
					'text'=>"Cache get error",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

			// If the all_cached flag isn't set in the class cache after reloading from
			// the persistent cache, load both caches from the db.

			if( !FOX_sUtil::keyTrue("all_cached", $this->cache) ){

				try {
					self::load($data=null);
				}
				catch (FOX_exception $child) {

					throw new FOX_exception( array(
						'numeric'=>2,
						'text'=>"Load error",
						'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						'child'=>$child
					));
				}
			}

		}

		// At this point, all modules should be in the cache
		if( FOX_sUtil::keyExists("module_id", $this->cache) ){

			$result = $this->cache["module_id"];
		}
		else {
			// Handle no modules installed on system
			$result = array();
		}

		return $result;

	}


	/**
	 * Returns all data columns for one or more module id's.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int/array $module_id | Single module_id as int. Multiple module_ids as array of int.
	 * @return array | Exception on failure. Array of module data arrays on success.
	 */

	public function getByID($module_id){


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		if( empty($module_id) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Called with empty module_id",
				'data'=>array('module_id'=>$module_id),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));

		}
		elseif( !is_array($module_id) ){

			// Handle single string as input
			$module_id = array($module_id);
		}

		// Build a list of all module_ids that are not in the cache
		// ========================================================
		$missing_ids = array();
		$cache_reloaded = false;

		foreach($module_id as $module){

			if( !FOX_sUtil::keyExists($module, $this->cache["module_id"]) ){

				// If the module_id is not present in the class cache, try reloading
				// the class cache from the persistent cache.

				if(!$cache_reloaded){

					try {
						self::loadCache();
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>2,
							'text'=>"Cache get error",
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));
					}

					$cache_reloaded = true;

					//  If the module_id is still not present in the class cache after reloading from
					//  the persistent cache, add it to the array of modules to fetch from the db.

					if( !FOX_sUtil::keyExists($module, $this->cache["module_id"]) ){

						$missing_ids[] = $module;
					}
				}
				else {
					$missing_ids[] = $module;
				}
			}
		}
		unset($module);

		// Cache all missing module_ids
		// ========================================================

		if($missing_ids){

			try {
				self::load( array("module_id"=>$missing_ids) );
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Load error",
					'data'=> array("module_id"=>$missing_ids),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}


		}

		// If only one module_id was requested, just return the contents of its db row. If
		// multiple module_ids were requested, return them as a set of arrays keyed by module_id
		// =======================================================================================

		if( count($module_id) == 1){

			$result =  FOX_sUtil::keyVal($module_id[0], $this->cache["module_id"]);
		}
		else {
			$result = array();

			foreach( $module_id as $module ){

				// Check if the module_id exists in the main datastore
				if( FOX_sUtil::keyExists($module, $this->cache["module_id"]) ){

					$result[$module] = $this->cache["module_id"][$module];
				}
			}
			unset($module);
		}

		return $result;

	}


	/**
	 * Returns all data columns for one or more modules, given the module's PHP class name.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string/array $php_class | Single class as string. Multiple classes as array of strings.
	 * @return arrat | Exception on failure. Array of module data arrays on success.
	 */

	public function getByClass($php_class){


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		if( empty($php_class) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Called with empty php_class",
				'data'=>array('php_class'=>$php_class),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		elseif( !is_array($php_class) ){
			// Handle single string input
			$php_class = array($php_class);
		}


		// Build a list of all classes that are not in the cache
		// ========================================================
		$missing_classes = array();
		$cache_reloaded = false;

		foreach($php_class as $class_name){

			if( !FOX_sUtil::keyExists($class_name, $this->cache["php_class"]) ){

				// If the php_class is not present in the class cache, try reloading
				// the class cache from the persistent cache.

				if(!$cache_reloaded){

					try {
						self::loadCache();
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>2,
							'text'=>"Cache get error",
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));
					}

					$cache_reloaded = true;

					//  If the php_class is still not present in the class cache after reloading from
					//  the persistent cache, add it to the array of classes to fetch from the db.

					if( !FOX_sUtil::keyExists($class_name, $this->cache["php_class"]) ){

						$missing_classes[] = $class_name;
					}
				}
				else {
					$missing_classes[] = $class_name;
				}
			}
		}
		unset($class_name);


		// Cache all missing slugs (uses a single query)
		// ========================================================
		if($missing_classes){

			try {
				self::load( array("php_class"=>$missing_classes) );
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Load error",
					'data'=> array("php_class"=>$missing_classes),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}

		}

		// If only one module_id was requested, just return the contents of its db row. If
		// multiple module_ids were requested, return them as a set of arrays keyed by module_id
		// =======================================================================================

		if( count($php_class) == 1){

			// Check if the php_class exists in the php_class dictionary
			if( FOX_sUtil::keyExists($php_class[0], $this->cache["php_class"]) ){

				$module_id = $this->cache["php_class"][$php_class[0]];
				$result =  FOX_sUtil::keyVal($module_id, $this->cache["module_id"]);
			}
			else {
				$result = null;
			}

		}
		else {
			$result = array();

			foreach($php_class as $class_name){

				// Check if the class_name exists in the php_class dictionary
				if( FOX_sUtil::keyExists($class_name, $this->cache["php_class"]) ){

					$module_id = $this->cache["php_class"][$class_name];

					// Check if the module_id exists in the main datastore
					if( FOX_sUtil::keyExists($module_id, $this->cache["module_id"]) ){

						$result[$class_name] = $this->cache["module_id"][$module_id];
					}
				}
			}
			unset($class_name, $module_id);
		}

		return $result;

	}


	/**
	 * Returns all data columns for one or more modules, given the module's slug name.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param string/array $slug | Single slug as string. Multiple slugs as array of strings.
	 * @return array | Exception on failure. Array of module data arrays on success.
	 */

	public function getBySlug($slug){


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		if( empty($slug) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Called with empty slug",
				'data'=>array('slug'=>$slug),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		elseif( !is_array($slug) ){
			// Handle single string input
			$slug = array($slug);
		}


		// Build a list of all requested slugs not in the cache
		// ========================================================
		$missing_slugs = array();

		foreach($slug as $slug_name){

			if( !FOX_sUtil::keyExists($slug_name, $this->cache["slug"]) ){

				// If the php_class is not present in the class cache, try reloading
				// the class cache from the persistent cache.

				if(!$cache_reloaded){

					try {
						self::loadCache();
					}
					catch (FOX_exception $child) {

						throw new FOX_exception( array(
							'numeric'=>2,
							'text'=>"Cache get error",
							'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							'child'=>$child
						));
					}

					$cache_reloaded = true;

					//  If the php_class is still not present in the class cache after reloading from
					//  the persistent cache, add it to the array of classes to fetch from the db.

					if( !FOX_sUtil::keyExists($slug_name, $this->cache["slug"]) ){

						$missing_slugs[] = $slug_name;
					}
				}
				else {
					$missing_slugs[] = $slug_name;
				}
			}
		}
		unset($slug_name);


		// Cache all missing requested slugs (uses a single query)
		// ========================================================
		if($missing_slugs){

			try {
				self::load( array("slug"=>$missing_slugs) );
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Load error",
					'data'=> array("slug"=>$missing_slugs),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}
		}


		// If only one module_id was requested, just return the contents of its db row. If
		// multiple module_ids were requested, return them as a set of arrays keyed by module_id
		// =======================================================================================
		if( count($slug) == 1){

			// Check if the slug_name exists in the slug dictionary
			if( FOX_sUtil::keyExists($slug[0], $this->cache["slug"]) ){

				$module_id = $this->cache["slug"][$slug[0]];
				$result =  FOX_sUtil::keyVal($module_id, $this->cache["module_id"]);
			}
			else {
				$result = null;
			}

		}
		else {
			$result = array();

			foreach($slug as $slug_name){

				// Check if the slug_name exists in the slug dictionary
				if( FOX_sUtil::keyExists($slug_name, $this->cache["slug"]) ){

					$module_id = $this->cache["slug"][$slug_name];

					// Check if the module_id exists in the main datastore
					if( FOX_sUtil::keyExists($module_id, $this->cache["module_id"]) ){

						$result[$slug_name] = $this->cache["module_id"][$module_id];
					}
				}

			}
			unset($slug_name, $module_id);
		}

		return $result;

	}


	/**
	 * Sets one or more module's status to active, making them available to use.
	 * This version of the function uses the module's slug as the identifier.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string/array $slug | Single module slug as string. Multiple modules as array of strings.
	 * @return bool | Exception on failure. True on success. False on already activated.
	 */

	public function activateBySlug($slugs) {


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		$this->db = new FOX_db();

		if( empty($slugs) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Missing slug",
				'data'=>$slugs,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		elseif( !is_array($slugs) ){

			// Handle single string input
			$slugs = array($slugs);
		}

		// Lock the current cache namespace
		// =================================================================

		try {
			self::lockCache($this->process_id, 5);
		}
		catch (FOX_exception $child) {

			if($child->getNumeric() == 4){

				// In the future we'll handle this gracefully, but to simplify
				// testing, we currently just throw an exception

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Another thread had exclusive use of the cache namespace",
					'data'=> array('lock_info'=>$child->data),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));

			}
			else {
				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error locking persitent cache",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}
		}


		// Run the query
		// ================================================

		$data = array("active"=>true);
		$args = array( array("col"=>"slug", "op"=>"=", "val"=>$slugs) );
		$columns = array("mode"=>"include", "col"=>"active");

		try {
			$rows_changed = $this->db->runUpdateQuery($this->_struct(), $data, $args, $columns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error updating database",
				'data'=>array('data'=>$data, 'args'=>$args, 'columns'=>$columns),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}


		// Update the persistent cache and release our lock
		// =============================================================

		foreach($slugs as $slug){

			// If a module_id exists in the class cache, update its
			// entries in the dictionaries and datastore

			if( FOX_sUtil::keyExists($slug, $this->cache["slug"]) ){

				$module_id = $this->cache["slug"][$slug];

				$this->cache["module_id"][$module_id]["active"] = true;
				$this->cache["active_modules"][$module_id] = true;
			}

		}
		unset($slug, $module_id);


		try {
			self::saveCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Cache set error",
				'data'=>$this->cache,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}


		return (bool)$rows_changed;

	}


	/**
	 * Sets one or more module's status to active, making them available to use.
	 * This version of the function uses the module's module_id as the identifier.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int/array $module_id | Single module_id as int. Multiple module_ids as array of ints.
	 * @return bool | Exception on failure. True on success. False on already activated.
	 */

	public function activateById($module_ids) {


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		$this->db = new FOX_db();

		if( empty($module_ids) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Missing module_id",
				'data'=>$module_ids,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		elseif( !is_array($module_ids) ){

			// Handle single string input
			$module_ids = array($module_ids);
		}

		// Lock the current cache namespace
		// =================================================================

		try {
			self::lockCache($this->process_id, 5);
		}
		catch (FOX_exception $child) {

			if($child->getNumeric() == 4){

				// In the future we'll handle this gracefully, but to simplify
				// testing, we currently just throw an exception

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Another thread had exclusive use of the cache namespace",
					'data'=> array('lock_info'=>$child->data),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));

			}
			else {
				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error locking persitent cache",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}
		}

		// Run the query
		// ================================================

		$data = array("active"=>true);
		$args = array( array("col"=>"module_id", "op"=>"=", "val"=>$module_ids) );
		$columns = array("mode"=>"include", "col"=>"active");

		try {
			$rows_changed = $this->db->runUpdateQuery($this->_struct(), $data, $args, $columns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error writing to database",
				'data'=> array('data'=>$data, 'args'=>$args, 'columns'=>$columns),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// Update the persistent cache and release our lock
		// =============================================================

		foreach($module_ids as $module_id){

			// If a module_id exists in the class cache, update its
			// entries in the dictionaries and datastore

			if( FOX_sUtil::keyExists($module_id, $this->cache["module_id"]) ){

				$this->cache["module_id"][$module_id]["active"] = true;
				$this->cache["active_modules"][$module_id] = true;
			}

		}
		unset($module_id);

		try {
			self::saveCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Cache set error",
				'data'=>$this->cache,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}


		return (bool)$rows_changed;

	}


	/**
	 * Sets one or more module's status to inactive, making them unavailable for use.
	 * This version of the function uses the module's slug as the identifier.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string/array $slug | Single module slug as string. Multiple modules as array of strings.
	 * @return bool | Exception on failure. True on success. False on already deactivated.
	 */

	public function deactivateBySlug($slugs) {


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		$this->db = new FOX_db();

		if( empty($slugs) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Empty slug parameter",
				'data'=>$slugs,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		elseif( !is_array($slugs) ){

			// Handle single string input
			$slugs = array($slugs);
		}

		// Lock the current cache namespace
		// =================================================================

		try {
			self::lockCache($this->process_id, 5);
		}
		catch (FOX_exception $child) {

			if($child->getNumeric() == 4){

				// In the future we'll handle this gracefully, but to simplify
				// testing, we currently just throw an exception

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Another thread had exclusive use of the cache namespace",
					'data'=> array('lock_info'=>$child->data),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));

			}
			else {
				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error locking persitent cache",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}
		}

		// Run the query
		// ================================================

		$data = array("active"=>false);
		$args = array( array("col"=>"slug", "op"=>"=", "val"=>$slugs) );
		$columns = array("mode"=>"include", "col"=>"active");

		try {
			$rows_changed = $this->db->runUpdateQuery($this->_struct(), $data, $args, $columns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error updating database",
				'data'=> array('data'=>$data, 'args'=>$args, 'columns'=>$columns),
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// Update the persistent cache and release our lock
		// =============================================================

		foreach($slugs as $slug){

			// If a module_id exists in the class cache, update its
			// entries in the dictionaries and datastore

			if( FOX_sUtil::keyExists($slug, $this->cache["slug"]) ){

				$module_id = $this->cache["slug"][$slug];

				$this->cache["module_id"][$module_id]["active"] = false;
				unset($this->cache["active_modules"][$module_id]);
			}

		}
		unset($slug, $module_id);

		try {
			self::saveCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Cache set error",
				'data'=>$this->cache,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}


		return (bool)$rows_changed;

	}


	/**
	 * Sets one or more module's status to inactive, making them unavailable for use.
	 * This version of the function uses the module's module_id as the identifier.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int/array $module_id | Single module_id as int. Multiple module_ids as array of ints.
	 * @return bool | Exception on failure. True on success. False on already deactivated.
	 */

	public function deactivateById($module_ids) {


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		$this->db = new FOX_db();

		if( empty($module_ids) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Missing module_id",
				'data'=>$module_ids,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		elseif( !is_array($module_ids) ){

			// Handle single string as input
			$module_ids = array($module_ids);
		}

		// Lock the current cache namespace
		// =================================================================

		try {
			self::lockCache($this->process_id, 5);
		}
		catch (FOX_exception $child) {

			if($child->getNumeric() == 4){

				// In the future we'll handle this gracefully, but to simplify
				// testing, we currently just throw an exception

				throw new FOX_exception( array(
					'numeric'=>2,
					'text'=>"Another thread had exclusive use of the cache namespace",
					'data'=> array('lock_info'=>$child->data),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>null
				));

			}
			else {
				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Error locking persitent cache",
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}
		}

		// Run the query
		// ================================================

		$data = array("active"=>false);
		$args = array( array("col"=>"module_id", "op"=>"=", "val"=>$module_ids) );
		$columns = array("mode"=>"include", "col"=>"active");

		try {
			$rows_changed = $this->db->runUpdateQuery($this->_struct(), $data, $args, $columns);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error updating database",
				'data'=>$module_ids,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		// Update the persistent cache and release our lock
		// =============================================================

		foreach($module_ids as $module_id){

			// If a module_id exists in the class cache, update its
			// entries in the dictionaries and datastore

			if( FOX_sUtil::keyExists($module_id, $this->cache["module_id"]) ){

				$this->cache["module_id"][$module_id]["active"] = false;
				unset($this->cache["active_modules"][$module_id]);
			}

		}
		unset($module_id);

		try {
			self::saveCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>5,
				'text'=>"Cache set error",
				'data'=>$this->cache,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}


		return (bool)$rows_changed;

	}


	/**
	 * Removes a module from the system. Modules are responsible for deleting their own
	 * config keys and data objects.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param int/array $module_id | Single module_id as int. Multiple module ids as array of ints.
	 * @return bool | Exception on failure. True on Success. False on nonexistent.
	 */

	public function deleteById($module_ids) {


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		$this->db = new FOX_db();

		if( empty($module_ids) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Missing module_id",
				'data'=>$module_ids,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		elseif( !is_array($module_ids) ){

			// Handle single string as input
			$module_ids = array($module_ids);
		}


		// Drop the items from the cache
		// =============================================================
		// NOTE: we don't need to lock the persistent cache because we're deleting
		// items from it, not updating items in it. If the db delete operation later in
		// the method fails, the items will be added back to the cache on the next read

		try {
			self::loadCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Error loading cache",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		$update_required = false;

		foreach($module_ids as $module_id){

			// If a deleted module_id exists in the class cache, remove its
			// entries from the dictionaries and datastore

			if( FOX_sUtil::keyExists($module_id, $this->cache["module_id"]) ){

				$data = $this->cache["module_id"][$module_id];

				unset($this->cache["module_id"][$module_id]);
				unset($this->cache["php_class"][$data["php_class"]]);
				unset($this->cache["slug"][$data["slug"]]);
				unset($this->cache["active_modules"][$module_id]);
				unset($data);

				$update_required = true;
			}

		}
		unset($module_id);

		// If entries had to be removed from the class cache, write the
		// class cache back to the persistent cache

		if($update_required){

			try {
				self::saveCache();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Cache set error",
					'data'=>$this->cache,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}
		}

		// Delete the module_ids from the database
		// =============================================================

		$args = array(
				array("col"=>"module_id", "op"=>"=", "val"=>$module_ids)
		);

		try {
			$rows_changed = $this->db->runDeleteQuery($this->_struct(), $args);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error deleting from database",
				'data'=>$module_ids,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}


		return (bool)$rows_changed;

	}


	/**
	 * Removes a module from the system. Modules are responsible for deleting their own
	 * config keys and data objects.
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string/array $slug | Single slug as string. Multiple slugs as array of strings.
	 * @return bool | Exception on failure. True on Success. False on nonexistent.
	 */

	public function deleteBySlug($slugs) {


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		$this->db = new FOX_db();

		if( empty($slugs) ){

			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Missing slug",
				'data'=>$slugs,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>null
			));
		}
		elseif( !is_array($slugs) ){

			// Handle single string as input
			$slugs = array($slugs);
		}


		// Drop the items from the cache
		// =============================================================
		// NOTE: we don't need to lock the persistent cache because we're deleting
		// items from it, not updating items in it. If the db delete operation later in
		// the method fails, the items will be added back to the cache on the next read

		try {
			self::loadCache();
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>2,
				'text'=>"Cache get error",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}

		$update_required = false;

		foreach($slugs as $slug){

			// If a deleted module_id exists in the class cache, remove its
			// entries from the dictionaries and datastore

			if( FOX_sUtil::keyExists($slug, $this->cache["slug"]) ){

				$module_id = $this->cache["slug"][$slug];
				$data = $this->cache["module_id"][$module_id];

				unset($this->cache["slug"][$slug]);
				unset($this->cache["module_id"][$module_id]);
				unset($this->cache["php_class"][$data["php_class"]]);
				unset($this->cache["active_modules"][$module_id]);
				unset($data, $module_id);

				$update_required = true;
			}

		}
		unset($slug);

		// If entries had to be removed from the class cache, write the
		// class cache back to the persistent cache

		if($update_required){

			try {
				self::saveCache();
			}
			catch (FOX_exception $child) {

				throw new FOX_exception( array(
					'numeric'=>3,
					'text'=>"Cache set error",
					'data'=>$this->cache,
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					'child'=>$child
				));
			}
		}

		// Delete the slugs from the database
		// =============================================================

		$args = array(
				array("col"=>"slug", "op"=>"=", "val"=>$slugs)
		);

		try {
			$rows_changed = $this->db->runDeleteQuery($this->_struct(), $args);
		}
		catch (FOX_exception $child) {

			throw new FOX_exception( array(
				'numeric'=>4,
				'text'=>"Error deleting from database",
				'data'=>$slugs,
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}


		return (bool)$rows_changed;

	}


	/**
	 * Loads the configuration data for the template the module is currently using
	 *
	 * @version 1.0
	 * @since 1.0
	 * @param string $slug | Module slug name
	 * @return bool | Exception on failure. True on Success.
	 */

	public function loadTemplateConfig($slug) {


		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		$template_name = 'modules/' . $this->_offset() . '/' . $slug . '/config.xml';
		$located_template = locate_template($template_name, $load=false, $require_once=true );

		if(!$located_template){

			return false;
		}
		else {

			$cls = new FOX_xml();
			$result =  $cls->parseFile($located_template, 1, 'attribute');

			$primary = $result["config"]["tabs"]["primary"];


			if($primary["targets"]){

				if( count($primary["targets"]["loc"]) == 1 ){

					$this->targets[$primary["targets"]["loc"]["name"]["value"]] = true;
				}
				elseif( count($primary["targets"]["loc"]) > 1 ){

					foreach($primary["targets"]["loc"] as $target){

						$this->targets[$target["name"]["value"]] = true;
					}
					unset($target);
				}

			}

			if($primary["views"]){

				if( count($primary["views"]["view"]) == 1 ){

					$this->views[$primary["views"]["view"]["name"]["value"]] = $primary["views"]["view"]["desc"]["value"];
				}
				elseif( count($primary["views"]["view"]) > 1 ){

					foreach($primary["views"]["view"] as $view){

						$this->views[$view["name"]["value"]] = $view["desc"]["value"];
					}
					unset($view);
				}

			}

			if($primary["caps"]){

				if( count($primary["caps"]["cap"]) == 1 ){

					$this->caps[$primary["caps"]["cap"]["name"]["value"]] = $primary["caps"]["cap"]["desc"]["value"];
				}
				elseif( count($primary["caps"]["cap"]) > 1 ){

					foreach($primary["caps"]["cap"] as $cap){

						$this->caps[$cap["name"]["value"]] = $cap["desc"]["value"];
					}
					unset($cap);
				}

			}

			if($primary["thumbs"]){

				$this->thumbs["algorithm"] = $primary["thumbs"]["algorithm"]["value"];
				$this->thumbs["page"] = $primary["thumbs"]["page"]["value"];
				$this->thumbs["row"] = $primary["thumbs"]["row"]["value"];
				$this->thumbs["base"] = $primary["thumbs"]["base"]["value"];
				$this->thumbs["scale_x"] = $primary["thumbs"]["scale_x"]["value"];
				$this->thumbs["scale_y"] = $primary["thumbs"]["scale_y"]["value"];
				$this->thumbs["order"] = $primary["thumbs"]["order"]["value"];
				$this->thumbs["direction"] = $primary["thumbs"]["direction"]["value"];
			}

			//FOX_Debug::dump($this->targets); FOX_Debug::dump($this->views); FOX_Debug::dump($this->caps); die;

			return true;

		}

	}


	/**
	 * Returns the available target screen targets for the template that the
	 * page module is currently using
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return array | Exception on failure. Array of available targets on success.
	 */

	public function getTargets() {

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		return $this->targets;
		
	}


	/**
	 * Returns the available views for the template that the page module is currently using
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return array | Exception on failure. Array of available views on success.
	 */

	public function getViews() {

		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		return $this->views;
	}


	/**
	 * Returns the available capability locks for the template that the page module is currently using
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return array | Exception on failure. Array of available capability locks on success.
	 */

	public function getCaps() {

		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		return $this->caps;
	}


	/**
	 * Returns the thumbnail config data for the template that the page module is currently using
	 *
	 * @version 1.0
	 * @since 1.0
	 * @return array | Exception on failure. Array of thumbnail config data on success.
	 */

	public function getThumbs() {

	    
		if(!$this->init){

			throw new FOX_exception( array(
				'numeric'=>0,
				'text'=>"Descendent class must call init() before using class methods",
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));
		}
		
		return $this->thumbs;
	}



} // End of class FOX_module_manager_base



?>