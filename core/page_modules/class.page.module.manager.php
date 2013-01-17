<?php

/**
 * RADIENT PAGE MODULE MANAGER CLASS
 * Handles registration and configuration for page modules.
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package Radient
 * @subpackage Page Modules
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

class RAD_pageModuleManager extends RAD_module_manager_base {


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

		"table" => "rad_sys_page_modules",
		"engine" => "InnoDB",
		"cache_namespace" => "RAD_pageModuleManager",
		"cache_strategy" => "monolithic",
		"cache_engine" => array("memcached", "redis", "apc"),
		"columns" => array(
		    "module_id" =>	array(  "php"=>"int",		"sql"=>"tinyint",	"format"=>"%d", "width"=>null,"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>true,   "default"=>null,	"index"=>"PRIMARY"),
		    "slug" =>	array(  "php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>"UNIQUE"),
		    "name" =>	array(  "php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>"UNIQUE"),
		    "php_class" =>	array(  "php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>255,	"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>"UNIQUE"),
		    "active" =>	array(  "php"=>"bool",	"sql"=>"tinyint",	"format"=>"%d", "width"=>1,	"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>0,	"index"=>true)
		 )
	);

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

		// Handle dependency-injection for unit tests
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


} // End of class RAD_pageModuleManager



/**
 * Hooks on the plugin's install function, creates database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function install_RAD_pageModuleManager(){

	$cls = new RAD_pageModuleManager();
	
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
add_action( 'rad_install', 'install_RAD_pageModuleManager', 2 );


/**
 * Hooks on the plugin's uninstall function. Removes all database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function uninstall_RAD_pageModuleManager(){

	$cls = new RAD_pageModuleManager();
	
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
add_action( 'rad_uninstall', 'uninstall_RAD_pageModuleManager', 2 );



/**
 * RADIENT PAGE MODULE TEMPLATE CLASS
 * Operates main loop in templates when displaying media items
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package Radient
 * @subpackage Page Modules
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

class RAD_pageModuleManager_template extends FOX_db_walker {


	var $pag_links;			// Pagination links for the current page in the template object

	// ============================================================================================================ //


        /**
         * Creates a template object filled with query result objects, based on user supplied parameters
         *
         * @version 0.1.9
         * @since 0.1.9
         *
	 * @param array $args | @see FOX_db::runSelectQuery() for array structure
	 * @param bool/array $columns | @see FOX_db::runSelectQuery() for array structure
	 * @param array $ctrl | @see FOX_db::runSelectQuery() for array structure
	 *
         * @return bool/int/array | False on failure. Int on count. Array of page objects on success.
         */

	function __construct($args=null, $columns=null, $ctrl=null) {


	    	// Run the parent constructor
		// =========================================================================

		$db_class = new RAD_pageModuleManager();

		parent::__construct($db_class, $args, $columns, $ctrl);


	    	// Build the pagination links string
		// =========================================================================

		$this->pag_links = paginate_links( array(

			'base' => add_query_arg( 'page', '%#%' ),
			'format' => '',
			'total' => $this->total_pages,
			'current' => (int) $ctrl["page"],
			'prev_text' => '&larr;',
			'next_text' => '&rarr;',
			'mid_size' => 1
		));

	}


} // End of class RAD_pageModuleManager_template

?>