<?php

/**
 * FOXFIRE PAGE MODULE MANAGER CLASS
 * Handles registration and configuration for page modules.
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Page Modules
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

class FOX_pageModuleManager extends FOX_moduleManager_shared_base {


    	var $process_id;		    // Unique process id for this thread. Used by ancestor class
					    // FOX_db_base for cache locking.

	var $mCache;			    // Local copy of memory cache singleton. Used by ancestor
					    // class FOX_db_base for cache operations.

	var $admin_modules = array();	    // Array of all module php_classes as loaded on the admin screen

	var $targets = array();		    // Array of all available targets for the template the module
					    // is currently using (or default views if not supplied by template)

	var $views = array();		    // Array of all available views for the template the module
					    // is currently using (or default roles if not supplied by template)

	var $caps = array();		    // Array of all available capability locks for the template the module
					    // is currently using (or default roles if not supplied by template)

	var $thumbs = array();		    // Array of thumbnail configuration data (or default values if not supplied
					    // by template)

	var $dir_override;		    // Used during unit testing. If set, the class will load modules from the
					    // path specified.


	// ============================================================================================================ //

        // DB table names and structures are hard-coded into the class. This allows class methods to be
	// fired from an AJAX call, without loading the entire BP stack.

	public static $struct = array(

		"table" => "FOX_pageModuleManager",
		"engine" => "InnoDB",
		"cache_namespace" => "FOX_pageModuleManager",
		"cache_strategy" => "monolithic",
		"cache_engine" => array("memcached", "redis", "apc"),
		"columns" => array(
		    "module_id" =>  array(  "php"=>"int",	"sql"=>"tinyint",   "format"=>"%d", "width"=>null,  "flags"=>"UNSIGNED NOT NULL",   "auto_inc"=>true,   "default"=>null,    "index"=>"PRIMARY"),
		    "plugin_slug" =>array(  "php"=>"string",	"sql"=>"varchar",   "format"=>"%s", "width"=>32,    "flags"=>"NOT NULL",	    "auto_inc"=>false,  "default"=>null,    "index"=>true),		    
		    "module_slug" =>array(  "php"=>"string",	"sql"=>"varchar",   "format"=>"%s", "width"=>32,    "flags"=>"NOT NULL",	    "auto_inc"=>false,  "default"=>null,    "index"=>"UNIQUE"),
		    "module_name" =>array(  "php"=>"string",	"sql"=>"varchar",   "format"=>"%s", "width"=>32,    "flags"=>"NOT NULL",	    "auto_inc"=>false,  "default"=>null,    "index"=>"UNIQUE"),
		    "php_class" =>  array(  "php"=>"string",	"sql"=>"varchar",   "format"=>"%s", "width"=>128,   "flags"=>"NOT NULL",	    "auto_inc"=>false,  "default"=>null,    "index"=>"UNIQUE"),
		    "active" =>	    array(  "php"=>"bool",	"sql"=>"tinyint",   "format"=>"%d", "width"=>1,	    "flags"=>"NOT NULL",	    "auto_inc"=>false,  "default"=>0,	    "index"=>true)
		 )
	);

	// ================================================================================================================
	// 	
	// $module_id	    | Globally unique ID assigned to the page module by FoxFire. This allows faster database performance
	//		    | and lower memory usage, and it could be tied to potentially millions of entried in the key database
	//		    
	// $plugin_slug	    | Offical WordPress 'slug' for the plugin that owns this page module "/wp-content/plugins/PLUGIN_SLUG"
	// 			    
	// $module_slug	    | Globally unique human-readable slug used to refer to the page module in URL's and admin screens
	// 
	// $module_name	    | Full name of your page module. This will be automatically translated if translations files exist.
	// 
	// $php_class	    | PHP class of your page module. 
	// 
	// $active	    | True to enable page module. False to disable it.
	//
	// ================================================================================================================	
	
	
	public static $offset = "page";

	// PHP allows this: $foo = new $class_name; $result = $foo::$struct; but does not allow this: $result = $class_name::$struct;
	// or this: $result = $class_name::get_struct(); ...so we have to do this: $result = call_user_func( array($class_name,'_struct') );

	public static function _struct() {

		return self::$struct;
	}

	public static function _offset() {

		return self::$offset;
	}


	// ================================================================================================================


	public function __construct($args=null) {

						
		try{
			parent::init($args);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				'numeric'=>1,
				'text'=>"Error initializing base class",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		try{
			self::loadCache();
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				'numeric'=>2,
				'text'=>"Error loading cache",
				'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				'child'=>$child
			));
		}
		
		
		
	}


} // End of class FOX_pageModuleManager



/**
 * Hooks on the plugin's install function, creates database tables and
 * configuration options for the class.
 *
 * @version 1.0
 * @since 1.0
 */

function install_FOX_pageModuleManager(){

	$cls = new FOX_pageModuleManager();
	
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
add_action( 'fox_install', 'install_FOX_pageModuleManager', 2 );


/**
 * Hooks on the plugin's uninstall function. Removes all database tables and
 * configuration options for the class.
 *
 * @version 1.0
 * @since 1.0
 */

function uninstall_FOX_pageModuleManager(){

	$cls = new FOX_pageModuleManager();
	
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
add_action( 'fox_uninstall', 'uninstall_FOX_pageModuleManager', 2 );

?>