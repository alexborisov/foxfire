<?php

/**
 * FOXFIRE USER CLASSES CLASS
 * Handles different classes that users can be assigned to
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

class FOX_userClass extends FOX_db_base {

    
    	var $process_id;		    // Unique process id for this thread. Used by ancestor class 
					    // FOX_db_base for cache locking. 	
	
	var $mCache;			    // Local copy of memory cache singleton. Used by ancestor 
					    // class FOX_db_base for cache operations. 
	
	var $cache;			    // Main cache array for this class
	
	public $id;			    // The id of this user class
	public $name;			    // Name of this user class
	public $is_default;		    // If true, new users are assigned to this class when their profiles are created
	public $transfer_to;		    // Users are placed in this class when moved out of their current class by the system
	public $tag_who;		    // 0 = off, 1 = friends, 2 = all users
	public $in_what;		    // 0 = off, 1 = in own items, 2 = in friends items, 3 = in friends^2 items, 4 = in all items
	public $tag_self;		    // 0 = off, 1 = in own items, 2 = in friends items, 3 = in friends^2 items, 4 = in all items
	public $can_comment;		    // 0 = off, 1 = own items, 2 = friends items, 3 = friends^2 items, 4 = all items
	public $can_view;		    // 0 = default, 1 = override friends, 2 = override private


	// ============================================================================================================ //


        // DB table names and structures are hard-coded into the class. This allows class methods to be
	// fired from an AJAX call, without loading the entire BP stack.

	public static $struct = array(

		"table" => "fox_usr_class_types",
		"engine" => "MyISAM",
		"cache_namespace" => "FOX_userClass",
		"cache_strategy" => "monolithic",
		"cache_engine" => array("memcached", "redis", "apc"),	    
		"columns" => array(
		    "id" =>		array(	"php"=>"int",	    "sql"=>"int",	"format"=>"%d", "width"=>null,"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>true,  "default"=>null,	"index"=>"PRIMARY"),
		    "name" =>		array(	"php"=>"int",	    "sql"=>"varchar",	"format"=>"%s", "width"=>255,	"flags"=>"NOT NULL",		"auto_inc"=>false, "default"=>null,	"index"=>"UNIQUE"),
		    "is_default" =>	array(	"php"=>"bool",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>1,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false, "default"=>null,	"index"=>true),
		    "transfer_to" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>null,"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false, "default"=>null,	"index"=>true),
		    "tag_who" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>1,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false, "default"=>null,	"index"=>true),
		    "in_what" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>1,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false, "default"=>null,	"index"=>true),
		    "tag_self" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>1,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false, "default"=>null,	"index"=>true),
		    "can_comment" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>1,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false, "default"=>null,	"index"=>true),
		    "can_view" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>1,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false, "default"=>null,	"index"=>true)
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
	 * Creates a new user class
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param array $data |
	 *	=> VAL @param string $name | Name of this user class
	 *	=> VAL @param int $is_default | Default class users are placed into when signing-up, if true.
	 *	=> VAL @param int $transfer_to | Class users are placed in when moved out of this class by the system
	 *	=> VAL @param int $tag_who | 0 = off, 1 = friends, 2 = all users
	 *	=> VAL @param int $in_what | 0 = off, 1 = in own items, 2 = in friends items, 3 = in friends^2 items, 4 = in all items
	 *	=> VAL @param int $tag_self | 0 = off, 1 = in own items, 2 = in friends items, 3 = in friends^2 items, 4 = in all items
	 *	=> VAL @param int $can_comment | 0 = off, 1 = own items, 2 = friends items, 3 = friends^2 items, 4 = all items
	 *	=> VAL @param int $can_view | 0 = default, 1 = override friends, 2 = override private
	 *	=> VAL @param int $ghost | (make user invisible) 0 = off, 1 = from public, 2 = from friends^2, 3 = from friends
	 * @return bool | False on failure. True on success.
	 */

	public function addClass($data) {

		// Trap missing *required* fields. empty() returns true for unset, null, or zero
		// variables, isset returns true for unset or null variables (but not zero).

		if( empty($data["name"]) ||
		    empty($data["is_default"]) ||
		    empty($data["transfer_to"]) ||
		    isset($data["tag_who"]) ||
		    isset($data["in_what"]) ||
		    isset($data["tag_self"]) ||
		    isset($data["can_comment"]) ||
		    isset($data["can_view"]) ||
		    isset($data["ghost"]) )
		    {
			echo "\n Missing required parameter in FOX_userClass::addClass\n";
			echo "Data array was: \n";
			var_dump($data);
			die;
		}

		$data = array($data);	// Must wrap our data in an array because
					// runInsertQueryMulti can handle multiple inserts at once

		$db = new FOX_db();
		try{
			$this->id = $db->runInsertQueryMulti(self::$struct, $data, $columns=null, $ctrl=null);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB insert exception",
				    'data'=>$data,
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}

		return $this->id;
	}


	/**
	 * Edits an existing user class
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param array $data |
	 *	=> VAL @param int $id | id of this class
	 *	=> VAL @param string $name | Name of this user class
	 *	=> VAL @param int $is_default | Default class users are placed into when signing-up, if true.
	 *	=> VAL @param int $transfer_to | Class users are placed in when moved out of this class by the system
	 *	=> VAL @param int $tag_who | 0 = off, 1 = friends, 2 = all users
	 *	=> VAL @param int $in_what | 0 = off, 1 = in own items, 2 = in friends items, 3 = in friends^2 items, 4 = in all items
	 *	=> VAL @param int $tag_self | 0 = off, 1 = in own items, 2 = in friends items, 3 = in friends^2 items, 4 = in all items
	 *	=> VAL @param int $can_comment | 0 = off, 1 = own items, 2 = friends items, 3 = friends^2 items, 4 = all items
	 *	=> VAL @param int $can_view | 0 = default, 1 = override friends, 2 = override private
	 *	=> VAL @param int $ghost | 0 = off, 1 = from public, 2 = from friends^2, 3 = from friends
	 * @return bool | False on failure. True on success.
	 */

	public function editClass($data) {

		// Trap missing *required* fields
		if( !$data["id"])
		    {
			echo "\n Missing class id in";
			echo " FOX_userClass::editClass"; die;
		}

		// Fetch the existing DB row for this user class

		$db = new FOX_db();
		$columns=array("format"=>"exclude", "col"=>"id" );
		$ctrl=array("format"=>"row_array");

		try{
			$result = $db->runSelectQueryCol(self::$struct, "id", "=", $data["id"], $columns, $ctrl );
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB select exception",
				    'data'=>array( "col"=>"id", "op"=>"=", "val"=>$data["id"],"columns"=>$columns, "ctrl"=>$ctrl),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}

		if($result){

			$update = array();

			// Build an array of keys that need to be updated

			foreach( $args as $key => $val){

				if( $data[$key] != $result[$key] ){
					$update[$key] = $val;
				}
			}

			// If the update data is changing the class we are editing to the "default"
			// class, we need to remove the "default" flag from the other class.

			if( $update["is_default"] == true){

				// Since many SQL installations don't support transaction capabilities, we have to temporarily let two
				// user classes have the default flag. First, we find the id of the class that currently has it

				$columns = array("format"=>"include", "col"=>"id" );
				$ctrl = array("format"=>"var");
				try{
					$class_id = $db->runSelectQueryCol(self::$struct, "is_default", "=", true, $columns, $ctrl);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>2,
						    'text'=>"DB select exception",
						    'data'=>array( "col"=>"is_default", "op"=>"=", "val"=>true,"columns"=>$columns, "ctrl"=>$ctrl),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}

				// Then we assign it to the new class
				try{
					$result = $db->runUpdateQueryCol(self::$struct, $update, "id", "=", $data["id"]);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>3,
						    'text'=>"DB update exception",
						    'data'=>array( "data"=>$update, "col"=>"id", "op"=>"=", "val"=>$data["id"]),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
				// If the query successfully executed, we remove the "is_default" flag from the old class

				if($result){

					$unset_default = array("is_default"=>false);
					try{
						$result = $db->runUpdateQueryCol(self::$struct, $unset_default, "id", "=", $class_id);
					}
					catch(FOX_exception $child){

						throw new FOX_exception(array(
							    'numeric'=>4,
							    'text'=>"DB update exception",
							    'data'=>array( "data"=>$unset_default, "col"=>"id", "op"=>"=", "val"=>$class_id),
							    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
							    'child'=>$child
							    )
						);
					}
				}
			}
			else {
				// If we're not changing the "is_default" flag, it's just a normal update query
				try{
					$result = $db->runUpdateQueryCol(self::$struct, $update, "id", "=", $data["id"]);
				}
				catch(FOX_exception $child){

					throw new FOX_exception(array(
						    'numeric'=>5,
						    'text'=>"DB update exception",
						    'data'=>array( "data"=>$update, "col"=>"id", "op"=>"=", "val"=>$data["id"]),
						    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
						    'child'=>$child
						    )
					);
				}
			}

		}

		return $result;
	}


	/**
	 * Deletes an existing user class. Users in this class will be moved to the class's "transfer_to"
	 * class. If the "transfer_to" class was not set, users will be moved to the site's "default" class.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @param int $id | id of this class to delete
	 * @return bool/int | False on failure. Numer of affected users on success.
	 */

	public function deleteClass($id) {


		// Fetch the db entry for this user class
		// ============================================================================================
		$db = new FOX_db();
		$columns = array("format"=>"include", "col"=>array("id", "is_default", "transfer_to") );
		$ctrl = array("format"=>"row_array");
		try{
			$result = $db->runSelectQueryCol(self::$struct, "id", "=", $id, $columns, $ctrl);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>1,
				    'text'=>"DB select exception",
				    'data'=>array( "col"=>"id", "op"=>"=", "val"=>$id, "columns"=>$columns, "ctrl"=>$ctrl),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		// Trap user trying to delete the default class
		// ============================================================================================
		if($result["is_default"] == true){
			echo "\nERROR: You cannot delete a class that is set as the default user class. To delete this ";
			echo "class, set another class as the default class, then come back and delete this one.\n"; die;
		}


		// If the old class has a "transfer_to" class, use it. If the old class doesn't list a
		// "transfer_to" class, find the class that has the "default" flag, and use that class instead.
		// ============================================================================================
		if( !empty($result["transfer_to"]) ){

			$dest_class = $result["transfer_to"];
		}
		else {
			$columns = array("format"=>"include", "col"=>"id" );
			$ctrl = array("format"=>"var");
			try{
				$dest_class = $db->runSelectQueryCol(self::$struct, "is_default", "=", true, $columns, $ctrl);
			}
			catch(FOX_exception $child){

				throw new FOX_exception(array(
					    'numeric'=>2,
					    'text'=>"DB select exception",
					    'data'=>array( "col"=>"is_default", "op"=>"=", "val"=>true, "columns"=>$columns, "ctrl"=>$ctrl),
					    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
					    'child'=>$child
					    )
				);
			}
		}

		// Run the update query
		// ============================================================================================

		$update = array( "user_class_id" => $dest_class,
				 "when_class_set" => gmdate( "Y-m-d H:i:s"),
				 "when_class_expires" => null
		);

		try{
			$result = $db->runUpdateQueryCol( FOX_user::_struct(), $update, "user_class_id", "=", $id);
		}
		catch(FOX_exception $child){

			throw new FOX_exception(array(
				    'numeric'=>3,
				    'text'=>"DB update exception",
				    'data'=>array( "data"=>$update, "col"=>"usewr_class_id", "op"=>"=", "val"=>$id),
				    'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    'child'=>$child
				    )
			);
		}
		// TODO: Although this code will move the users to the new class and delete the old class, it will cause
		// huge problems if the classes have different limitations. For example, if the old class allowed video
		// uploads in albums, and the new class does not. Or if the old class allowed 10 instances of an album
		// type and the new one does not, etc. We need to come up with ways to resolve these problems.


	}



	public function moveToClass($id) {

	    }



} // End of class FOX_userClass


/**
 * Hooks on the plugin's install function, creates database tables and
 * configuration options for the class.
 *
 * @version 1.0
 * @since 1.0
 */

function install_FOX_userClass(){

	$cls = new FOX_userClass();
	
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
add_action( 'fox_install', 'install_FOX_userClass', 2 );


/**
 * Hooks on the plugin's uninstall function. Removes all database tables and
 * configuration options for the class.
 *
 * @version 1.0
 * @since 1.0
 */

function uninstall_FOX_userClass(){

	$cls = new FOX_userClass();
	
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
add_action( 'fox_uninstall', 'uninstall_FOX_userClass', 2 );

?>