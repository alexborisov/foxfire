# Introducing FoxFire
FoxFire is a PHP => NoSQL => SQL object-relational mapping library. It connects PHP to NoSQL data stores like Redis and Memcached, SQL datastores like Postgres and MySQL, and adds thread-safe multilevel caching with full transaction support.

There are plenty of other ORM's out there, but all of them have at least one of the following problems:

* They don't let the developer manipulate the data as an object
* They don't handle transactions
* They don't handle caching
* They try to handle every possible SQL operation, adding massive complexity

FoxFire lets developers work with tables in the database _as if they were objects_, and allows developers to run queries on them in a structured, object-oriented manner. It has ACID compliant transaction support, and handles transaction coupling between the SQL servers and cache engines. That means if you run a transaction against a cached table, and the transaction fails, the transaction gets rolled-back in the database _and_ the cache.

FoxFire does _not_ support every possible query you can throw at an SQL server. Its designed to handle the types of operations typically seen in _large web apps_, and to do so _quickly and securely_. Properly used, FoxFire makes web apps _immune to SQL Injection_, and eliminates vexing problems like casting between PHP and SQL data types.

FoxFire is also WordPress aware, and will automatically load if placed in the /wp-content/plugins/ folder.

# Jump Points
### [Unit Tests](https://github.com/foxly/foxfire/tree/master/unit-test/testcase/php/tests)
FoxFire has over [450 unit tests](https://github.com/foxly/foxfire/tree/master/unit-test/testcase/php/tests) providing 100% coverage for every engine, on every platform. Our [Razor Test Platform](https://github.com/foxly/razor) runs on Linux, Windows, and OSX, with _zero configuration_, "it just works" ...and it can be safely stored to a version control system.

### [Documentation](https://github.com/foxly/foxfire/blob/master/docs/README.md)
FoxFire is heavily documented in-code [(example)](https://github.com/foxly/foxfire/blob/master/core/database/class.database.query.runners.php). For executive-level documentation covering key concepts and design rationale, along with examples and illustrations, [see the docs folder](https://github.com/foxly/foxfire/blob/master/docs/README.md).

### [Working Code](https://github.com/foxly/foxfire/tree/master/core)
FoxFire powers [Radient](https://github.com/foxly/radient), a plugin we're writing that more-or-less implements Flickr + YouTube for WordPress sites with 10K-500K users. 


# Quick Examples

### Create a Table:

```php
class foo extends FOX_db_base {

	var $id;	    // The foo's id
	var $name;	    // Human readable name of the foo
	var $text;	    // Foo text block
	var $priv;	    // Privacy level of the foo
	var $files;	    // Number of files owned by foo instance
	var $space;	    // Disk space used by foo

	// ============================================================================================================ //

	public static $struct_primary = array(

		"table" => "test_foo",
		"engine" => "InnoDB",
		"cache_namespace" => "test_foo",
		"cache_strategy" => "monolithic",
		"cache_engine" => array("memcached", "redis", "apc"),
		"columns" => array(
		    "id" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
		    "name" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>"UNIQUE"),
		    "text" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null,	     "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "priv" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>2,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,	"index"=>true),
		    "files" =>	array(	"php"=>"int",	    "sql"=>"mediumint",	"format"=>"%d", "width"=>7,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>false),
		    "space" =>	array(	"php"=>"float",	    "sql"=>"bigint",	"format"=>"%d", "width"=>null,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>false)
		 )
	);

	public static function _struct() {
		return self::$struct;
	}
}

$cls = new foo();
$cls->install();

```

### Create Another Table:

```php
class bar extends FOX_db_base {

	var $id;	    // The bar's id
	var $size;	    // Size of the bar
	var $color;	    // Color of the bar
	var $weight;	    // Weight of the bar in grams

	// ============================================================================================================ //

	static $struct_join = array(

		"table" => "test_bar",
		"engine" => "InnoDB",
		"cache_namespace" => "test_bar",
		"cache_strategy" => "monolithic",
		"cache_engine" => array("memcached", "redis", "apc"),
		"columns" => array(
		    "id" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
		    "size" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "color" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null,	     "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "weight" =>	array(	"php"=>"float",	    "sql"=>"bigint",	"format"=>"%d", "width"=>10,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,	  "index"=>true),
		 )
	);

	public static function _struct() {
		return self::$struct;
	}
}

$cls = new bar();
$cls->install();

```

### Load Tables With Data, Using Arrays:

```php
$foo_data = array(
		array( "name"=>"data_01", "text"=>"Test Text String 01", "priv"=>1, "files"=>222, "space"=>3333),
		array( "name"=>"data_02", "text"=>"Test Text String 02", "priv"=>1, "files"=>222, "space"=>4444),
		array( "name"=>"data_03", "text"=>"Test Text String 03", "priv"=>1, "files"=>555, "space"=>6666),
		array( "name"=>"data_04", "text"=>"Test Text String 04", "priv"=>3, "files"=>555, "space"=>6666)
);

$bar_data = array(
		array( "size"=>"data_01", "color"=>"red", "weight"=>11111),
		array( "size"=>"data_02", "color"=>"green", "weight"=>22222)
);

$db = new FOX_db();

$db->runInsertQueryMulti(foo::_struct(), $foo_data, $columns=null, $check=true);
$db->runInsertQueryMulti(bar::_struct(), $bar_data, $columns=null, $check=true);
```

### Load Tables With Data, Using Objects:

```php
$db = new FOX_db();
$bar = new bar();

$bar->size = "data_03";
$bar->color = "blue";
$bar->weight = 33333;

$db->runInsertQuery($bar::_struct(), $bar, $columns=null, $check=true);
```

### Run a Simple Query:

```php
$db = new FOX_db;

$columns = array("mode"=>"exclude", "col"=>"id");
$ctrl=array("format"=>"array_array");

$result = $db->runSelectQuery(foo::_struct(), $args=null, $columns, $ctrl, $check = true);

var_dump($result);

//  $result = array(
//		array( "name"=>"data_01", "text"=>"Test Text String 01", "priv"=>1, "files"=>222, "space"=>3333),
//		array( "name"=>"data_02", "text"=>"Test Text String 02", "priv"=>1, "files"=>222, "space"=>4444),
//		array( "name"=>"data_03", "text"=>"Test Text String 03", "priv"=>1, "files"=>555, "space"=>6666),
//		array( "name"=>"data_04", "text"=>"Test Text String 04", "priv"=>3, "files"=>555, "space"=>6666)
//  );

```

### Run an Advanced Query:

```php
$db = new FOX_db;

$primary = array( "class"=>foo::_struct(), "args"=>array( "col"=>"priv", "op"=>"=", "val"=>1) );

$join = array(
		array(
		    "class"=>bar::_struct(),
		    "on"=>array("pri"=>"name", "op"=>"=", "sec"=>"size"),
		    "args"=>array( "col"=>"weight", "op"=>"=", "val"=>22222 )
		)
);

$columns = array("mode"=>"exclude", "col"=>"id");

$result = $db->runSelectQueryJoin($primary, $join, $columns, $ctrl=array("format"=>"array_array"), $check=true);

var_dump($result);

//  $result = array(
//		array( "name"=>"data_02", "text"=>"Test Text String 02", "priv"=>1, "files"=>222, "space"=>4444)
//  );
```
