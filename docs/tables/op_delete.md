#Delete Queries

FOX_db has two functions that are used to run DELETE queries: FOX_db::runDeleteQuery() and FOX_db::runDeleteQueryCol().

##FOX_db::runDeleteQuery()

FOX_db::runDeleteQuery() is the full version of the delete function.

```php
/**
 * Runs a DELETE query on one of the plugin's db tables.
 *
 * @param array $struct | Structure of the db table, @see class FOX_db header for examples
 *
 * @param array $args | Args in the form: array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
 *	=> ARR @param int '' | Array index
 *	    => VAL @param string $col | Name of the column in the db table this key describes
 *	    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
 *	    => VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
 *
 * @param bool $check | set false to disable *all* query error checking.
 *
 * @return int | Int 0 on failure. Int number of rows deleted on success.
 */

public function runDeleteQuery($struct, $args, $check=true){

 // ...

}
```

####$struct

Structure of the table to operate on. See table array page for more info.

####$args

Test condition to select rows based on. If no $args parameter is passed, all rows in the table will be selected. 

Typical usage:

```php
$args = array(
		array("col"=>col_1, "op"=>"<", "val"=>"20"),
		array("col"=>col_1, "op"=>"!=", "val"=>"pink"),
		array("col"=>col_2, "op"=>"=", "val"=>array("red", "green", "blue")
);
```

####$col

Single column name, as string, to run the comparison on.


####$op

Comparison operator to use. Valid operators are: "<", ">", "=", "!=", and "<>". Only "=", "!=", and "<>" can be used when passing multiple values as an array to test against.


####$val

Single value to test against as int, float, or string. Multiple values to test against as array of int, float, or string.

####$check

If not set to false, the function will perform basic error checking on the query before running it.


##FOX_db::runDeleteQueryCol()

FOX_db::runDeleteQueryCol() is the simplified version of the delete function.

```php
/**
 * Runs a DELETE query on one of the plugin's db tables.
 *
 * @param array $struct | Structure of the db table, @see class FOX_db header for examples
 *
 * @param array $args | Args in the form: array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
 *	=> ARR @param int '' | Array index
 *	    => VAL @param string $col | Name of the column in the db table this key describes
 *	    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
 *	    => VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
 *
 * @param bool $check | set false to disable *all* query error checking.
 *
 * @return int | Int 0 on failure. Int number of rows deleted on success.
 */

public function runDeleteQuery($struct, $args, $check=true){

 // ...

}
```

####$struct

Structure of the table to operate on. See table array page for more info.

####$col

Single column name, as string, to run the comparison on.

####$op

Comparison operator to use. Valid operators are: "<", ">", "=", "!=", and "<>". Only "=", "!=", and "<>" can be used when passing multiple values as an array to test against.

####$val

Single value to test against as int, float, or string. Multiple values to test against as array of int, float, or string.

####$check

If not set to false, the function will perform basic error checking on the query before running it.
Usage Examples

#Examples
The examples below are working code that can be run on a live database. Use this code to install the test table.

```php

$struct = array(

	"table" => "test_a",
	"engine" => "InnoDB",
	"columns" => array(
	    "col_1" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
	    "col_2" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
	    "col_3" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false)
	 )
);

$tdb = new FOX_db();
$result = $tdb->runAddTable(self::$struct);
```

###Single constraint

```php

$args = array(

	array("col"=>"col_1", "op"=>"=", "val"=>53)
);

$result = $tdb->runDeleteQuery($struct, $args, $check = true);
```
```sql
"DELETE FROM test_a WHERE 1 = 1 AND col_1 = 53";
```

###Multiple constraints

```php
$args = array(

	array("col"=>"col_1", "op"=>"=", "val"=>53),
	array("col"=>"col_2", "op"=>"!=", "val"=>"test_val")
);

$result = $tdb->runDeleteQuery($struct, $args, $check = true);
```

```sql
"DELETE FROM test_a WHERE 1 = 1 AND col_1 = 53 AND col_2 != 'test_val'"
```

###Simplified Version

```php
$result = $tdb->runDeleteQueryCol($struct, $col = "col_1", $op = "<>", $val = 31, $check = true);
```

```sql
"DELETE FROM test_a WHERE 1 = 1 AND col_1 <> 31"
```
