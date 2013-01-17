<?php

/**
 * RADIENT ACCESS MANAGER CLASS
 * Controls which screens which users have access to
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package Radient
 * @subpackage Access Control
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

class RAD_accessManager extends FOX_db_base {


    	var $process_id;		    // Unique process id for this thread. Used by ancestor class
					    // FOX_db_base for cache locking.

	var $mCache;			    // Local copy of memory cache singleton. Used by ancestor
					    // class FOX_db_base for cache operations.

	var $cache;			    // Main cache array for this class


	// ============================================================================================================ //

        // DB table names and structures are hard-coded into the class. This allows class methods to be
	// fired from an AJAX call, without loading the entire BP stack.

	public static $struct = array(

	    "table" => "rad_sys_config_data",
	    "engine" => "InnoDB",
	    "cache_namespace" => "RAD_accessManager",
	    "cache_strategy" => "monolithic",
	    "cache_engine" => array("memcached", "redis", "apc"),
	    "columns" => array(
		"tree"=>    array(	"php"=>"string",	"sql"=>"varchar",   "format"=>"%s", "width"=>32,	"index"=>array("name"=>
				    "tree_branch_node", "col"=>array("tree", "branch", "node"), "index"=>"PRIMARY"), "this_row"=>true),
		"branch"=>  array(	"php"=>"string",	"sql"=>"varchar",   "format"=>"%s", "width"=>32,    "flags"=>"NOT NULL",    "auto_inc"=>false,  "default"=>null,    "index"=>true),
		"node"=>    array(	"php"=>"string",	"sql"=>"varchar",   "format"=>"%s", "width"=>32,    "flags"=>"NOT NULL",    "auto_inc"=>false,  "default"=>null,    "index"=>true),
		"val"=>	    array(	"php"=>"serialize",	"sql"=>"longtext",  "format"=>"%s", "width"=>null,  "flags"=>"",	    "auto_inc"=>false,  "default"=>null,    "index"=>false)
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



} // End of class RAD_accessManager



/**
 * Hooks on the plugin's install function, creates database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function install_RAD_accessManager(){

	$cls = new RAD_accessManager();
	
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
add_action( 'rad_install', 'install_RAD_accessManager', 2 );


/**
 * Hooks on the plugin's uninstall function. Removes all database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function uninstall_RAD_accessManager(){

	$cls = new RAD_accessManager();
	
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
add_action( 'rad_uninstall', 'uninstall_RAD_accessManager', 2 );

?>