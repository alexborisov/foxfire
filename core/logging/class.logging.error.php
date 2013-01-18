<?php

/**
 * RADIENT ERROR LOGGING
 * Handles error logging within Radient
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package Radient
 * @subpackage Logging
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

class RAD_errorLog extends FOX_db_base {
    
	// DB table names and structures are hard-coded into the class. This allows class methods to be
	// fired from an AJAX call, without loading the entire BP stack.

	public static $struct = array(

		"table" => "rad_sys_logging_error",
		"engine" => "InnoDB", // Required for transactions
		"cache_namespace" => null,
		"cache_strategy" => null,
		"cache_engine" => null,	    
		"columns" => array(
		    "id" =>			array(	"php"=>"int",	    "sql"=>"bigint",	    "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>true,  "default"=>null,	"index"=>"PRIMARY"),
		    "tree" =>		array(	"php"=>"int",	    "sql"=>"smallint",	    "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "branch" =>		array(	"php"=>"int",	    "sql"=>"smallint",	    "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "node" =>		array(	"php"=>"int",	    "sql"=>"smallint",	    "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "user_id" =>		array(	"php"=>"int",	    "sql"=>"int",	    "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED",		"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "level" =>		array(	"php"=>"int",	    "sql"=>"tinyint",	    "format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "date" =>		array(	"php"=>"int",	    "sql"=>"datetime",    "format"=>"%s", "width"=>null,	"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "summary" =>		array(	"php"=>"string",	    "sql"=>"varchar",	    "format"=>"%s", "width"=>128,	"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>false),
		    "data" =>		array(	"php"=>"serialize",	    "sql"=>"longtext",	    "format"=>"%s", "width"=>null,	"flags"=>"",			"auto_inc"=>false,  "default"=>null,	"index"=>false)
		 )
	);
	
	
	// PHP allows this: $foo = new $class_name; $result = $foo::$struct; but does not allow this: $result = $class_name::$struct;
	// or this: $result = $class_name::get_struct(); ...so we have to do this: $result = call_user_func( array($class_name,'_struct') );

	public static function _struct() {

		return self::$struct;
	}
	
	// ================================================================================================================

    
	public function RAD_errorLog () {

	    }
	
	 /**
	 * Adds one or more errors to the error log
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param int $tree | id of the event's tree
	 * @param int $branch | id of the event's branch
	 * @param int $node | id of the event's node
	 * @param int $user_id | id of the user_id associated with this event. Use int 0 for a system event
	 * @param int $level | severity of this event. 1-255, where 1 is the most serious
	 * @param int $date | date and time the event occured, as linux datetime
	 * @param string $summary | Human readable summary of the event. Max 128 characters.
	 * @param mixed $data | Full data associated with this event. Any PHP data type can be stored.
	 *
	 * @return bool/int | False on failure. ID of the created log entry on success.
	 */   
	    
	public function add($tree, $branch, $node, $user_id, $level, $date, $summary, $data=null) {
	    
	    }
	
	    
	    
	/**
	 * Adds one or more errors to the error log
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $data | Array of event arrays
	 *	=> ARR @param int '' | Array index
	 *	    => VAL @param int $tree | id of the event's tree
	 *	    => VAL @param int $branch | id of the event's branch
	 *	    => VAL @param int $node | id of the event's node
	 *	    => VAL @param int $user_id | id of the user_id associated with this event. Use int 0 for a system event
	 *	    => VAL @param int $level | severity of this event. 1-255, where 1 is the most serious
	 *	    => VAL @param int $date | date and time the event occured, as linux datetime
	 *	    => VAL @param string $summary | Human readable summary of the event. Max 128 characters.
	 *	    => VAL @param mixed $data | Full data associated with this event. Any PHP data type can be stored.
	 *
	 * @return bool/int | False on failure. ID of the created log entry on success with single row. True on success with multiple rows.
	 */    
	    
	public function addMulti($data) {
	     
	    }
	
	/**
	 * Drops one or more errors from the log, based on the supplied args
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param array $args | Args in the form: array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
	 *	=> ARR @param int '' | Array index
	 *	    => VAL @param string $col | Name of the column in the db table this key describes
	 *	    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
	 *	    => VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
	 *
	 * @return bool/int | False on failure. Number of rows deleted on success.
	 */  
	    
	public function drop($data) {
	    
	    }
	
    } // End of class RAD_errorLog
      
    
 /**
 * Hooks on the plugin's install function, creates database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function install_RAD_errorLog(){

	$cls = new RAD_errorLog();
	
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
add_action( 'rad_install', 'install_RAD_errorLog', 2 );


/**
 * Hooks on the plugin's uninstall function. Removes all database tables and
 * configuration options for the class.
 *
 * @version 0.1.9
 * @since 0.1.9
 */

function uninstall_RAD_errorLog(){

	$cls = new RAD_errorLog();
	
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
add_action( 'rad_uninstall', 'uninstall_RAD_errorLog', 2 );

?>