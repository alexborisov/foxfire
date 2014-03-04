#Class File Layout

###Class Header

Every database table in FoxFire is represented by its own class. If a class needs multiple database tables, each of its tables is wrapped in a separate child class, and the parent class instantiates the child classes.

Each table class starts with a vars block. If the class is used as the data source for an INSERT, UPDATE, or INDATE query, the contents of any vars which have names that match the names of columns in the class' database table will be used in the query. For example, if the database table has a column called 'user_id', the class has a var called $user_id, and an instance of the class is used as a data source for a query, then the contents of $user_id will be used in the query.

Following the vars block is the table declaration. This array defines the structure of the database table, and is explained in detail later in the documentation.

Following the table declaration is the struct function. The struct function allows other classes to access the class' table declaration without instantiating the class. This is necessary for running queries that use more than one table.

```php
class RAD_albumTypePolicy extends FOX_db_base {

	// @@@@@@ VARS BLOCK @@@@@@@

	var $cache;			// Main cache array for this class

	// ============================================================================================================ //

        // @@@@@@ TABLE DECLARATION @@@@@@@

	public static $struct = array(

		"table" => "RAD_albumTypePolicy",
		"engine" => "InnoDB", // Required for transactions
		"columns" => array(
		    "module_id" =>	array(	"php"=>"int",    "sql"=>"tinyint",	"format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",		"auto_inc"=>false,  "default"=>null,
			// This forces every type_id + branch + node name combination to be unique
			"index"=>array("name"=>"module_id_type_id_branch_id_node", "col"=>array("module_id", "type_id", "branch_id", "node"), "index"=>"PRIMARY", "this_row"=>true) ),
		    "type_id" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "branch_id" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>null,	"flags"=>"UNSIGNED NOT NULL",	"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "node" =>		array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>16,	"flags"=>"NOT NULL",		"auto_inc"=>false,  "default"=>null,	"index"=>true),
		    "val" =>		array(	"php"=>"serialize", "sql"=>"longtext",	"format"=>"%s", "width"=>null,	"flags"=>"",			"auto_inc"=>false,  "default"=>null,	"index"=>false),
		 )
	);


	// @@@@@@ STRUCT FUNCTION @@@@@@@

	public static function _struct() {

		return self::$struct;
	}

	// ================================================================================================================
```
	
###Install / Uninstall Hooks (WordPress)

Below a database class' declaration, we declare the install and uninstall hook functions. These functions add the class' table to the database when a WordPress plugin is installed, and drop it from the database when the plugin is uninstalled. The database class inherits the functions which do the actual work from class FOX_db_base.

```php
class RAD_albumTypePolicy extends FOX_db_base {

    // ...

} // End class RAD_albumTypePolicy


function install_RAD_albumTypePolicy(){

	$cls = new RAD_albumTypePolicy();
	$cls->install();
}
add_action( 'rad_install', 'install_RAD_albumTypePolicy', 2 );


function uninstall_RAD_albumTypePolicy(){

	$cls = new RAD_albumTypePolicy();
	$cls->uninstall();
}
add_action( 'rad_uninstall', 'uninstall_RAD_albumTypePolicy', 2 );
```
