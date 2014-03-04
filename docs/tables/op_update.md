#UPDATE Queries

FOX_db has two functions that are used to run UPDATE queries: FOX_db::runUpdateQuery() and FOX_db::runUpdateQueryCol().

##FOX_db::runUpdateQuery()

FOX_db::runUpdateQuery() is the full version of the update function. It can use both arrays and objects as its data source.

```php
/**
 * Runs an UPDATE query on one of the plugin's db tables.
 *
 * @param array $struct | Structure of the db table, @see class FOX_db header for examples
 *
 * @param array/object $data | Class with $column_1, $column_2 in the namespace, or array of the form ("column_1"=>"value_1", "column_2"=>"value_2")
 * 	=> ARR @param string | Name of the db column this key describes
 *	    => VAL @param int/string | Value to assign to the column
 *
 * @param array $args | Args in the form: array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
 *	=> ARR @param int '' | Array index
 *	    => VAL @param string $col | Name of the column in the db table this key describes
 *	    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
 *	    => VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
 *
 * @param array $columns | Columns to include / exclude from query.
 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
 *
 * @param bool $check | set false to disable *all* query error checking.
 *
 * @return int | Int 0 on failure. Int number of rows affected on success.
 */

public function runUpdateQuery($struct, $data, $args, $columns=null, $check=true){

 // ...

}
```

####$struct

Structure of the table to operate on. See class layout page for more info.

####$data

Data source for the query. When using an array, it has to follow this structure below.
The name of the key sets the column name to write to, and the value of the key sets the data to write to the column. Keys can be in any order and saved values can be any PHP data type, including arrays and objects. FOX_db will automatically typecast values to the right SQL data type based on the options set in the table definition array.

```php
$data = array( "col_name_1"=>"val", "col_name_2"=>"val", "col_name_2"=>"val",);
```

When using an object, the contents of any class variables which match the names of database columns will be saved to the database during the query. Our database classes typically follow the structure shown below.

```php
class foo extends FOX_db_base {

        var $col_1;            // Column variable 1
        var $col_2;            // Column variable 2
        var $col_3;            // Column variable 3

        var $not_a_col_1;      // Internal variable 1
        var $other_var;	       // Internal variable 2

        // ============================================================================================================ //

        static $struct_join = array(

                "table" => "test_bar",
                "engine" => "MyISAM",
                "columns" => array(
                    "col_1" => array(  "php"=>"int",       "sql"=>"smallint",  "format"=>"%d", "width"=>6,   ...
                    "col_2" => array(  "php"=>"string",    "sql"=>"varchar",   "format"=>"%s", "width"=>250, ...
                    "col_3" => array(  "php"=>"string",    "sql"=>"varchar",   "format"=>"%s", "width"=>250, ...
                 )
        );

        public static function _struct() {
                return self::$struct;
        }
}
```

This is the minimum class structure required for the function to operate.

```php
class bar {

        var $col_1;            // Column variable 1
        var $col_2;            // Column variable 2
        var $col_3;            // Column variable 3
}
```

####$args

Test condition to execute the update on. The array must have the structure shown below.

```php
array(
	array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()"),
	array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
);
```

####$col

Single column name, as string, to run the comparison on.

####$op

Comparison operator to use. "<, >, =, !="

####$val

Single value to test against as int, float, or string. Multiple values to test against as array of int, float, or string.

####$columns

Database columns to use during the insert query. If no array is passed, the function will try to save all columns listed in $struct to the database during the query. If keys for the columns are not present in the $data array, or variables for columns don't exist in the $data object, the function will exclude the missing columns from the SQL query, leaving the values currently stored in the database unchanged.

If an array is passed, it has to have the following structure:

```php
array( $mode=>"include|exclude", $col=>"col_name | array("col_name_1", "col_name_2") );
```

####$mode

If set to "include", the function will only use the column or columns specified in $col. If set to "exclude", the function will exclude all columns except the column or columns specified in $col.

####$col

Single column name passed as string, or multiple column names passed as array of strings. Columns excluded from, or not included in, the query are completely removed from the generated SQL statement. This will cause the values for these columns currently sured in the database to remain unchanged.

####$check

If not set, or set to true, the function will perform basic error checking on the query before running it.

##FOX_db::runUpdateQueryCol()

FOX_db::runUpdateQueryCol() functions like FOX_db::runUpdateQuery(), except the $col, $op, and $val parameters are passed in individually instead of in an array. This means the function can only run a comparison against the contents of a single column.

```php
/**
 * Runs a single-column keyed UPDATE query on one of the plugin's db tables

 * @param array $struct | Structure of the db table, @see class FOX_db header for examples
 *
 * @param array/object $data | Class with $column_1, $column_2 in the namespace, or array of the form ("column_1"=>"value_1", "column_2"=>"value_2")
 * 	=> KEY @param string | Name of the db column this key describes
 *	    => VAL @param int/string | Value to assign to the column
 *
 * @param string $col | Column name to use for WHERE construct
 * @param string $op | Comparison operator ">=", "<=", ">", "<", "=", "!=", "<>"
 * @param int/string $val | Comparison value to use in WHERE construct
 *
 * @param array $columns | Columns to include / exclude from query.
 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
 *
 * @param bool $check | set false to disable *all* query error checking.
 *
 * @return int | Int 0 on failure. Int number of rows affected on success.
 */

public function runUpdateQueryCol($struct, $data, $col, $op, $val, $columns=null, $check=true){

    // ...
 }
```
 
####$struct

Same as FOX_db::runUpdateQuery().

####$data

Same as FOX_db::runUpdateQuery().

####$col

Single column name, as string, to run the comparison on.

####$op

Comparison operator to use. "<, >, =, !="

####$val

Single value to test against as int, float, or string. Multiple values to test against as array of int, float, or string.

####$columns

Same as FOX_db::runUpdateQuery().

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

###Array as data source

```php
$data = array(
	'col_1'=>17,
	'col_2'=>'s_31'
);

$result = $tdb->runUpdateQuery($struct, $data, $args = "overwrite_all", $columns=null, $check=true);
```

```sql
"UPDATE test_a SET col_1 = 17, col_2 = 's_31' WHERE 1 = 1";
```

###Array as data source, single column using INCLUDE mode

```php
$data = array(
	'col_1'=>17,
	'col_2'=>'s_31',
	'col_3'=>'s_19'
);

$columns = array("mode"=>"include", "col"=>"col_2");

$result = $tdb->runUpdateQuery($struct, $data, $args = "overwrite_all", $columns, $check=true);
```

```sql
"UPDATE test_a SET col_2 = 's_31' WHERE 1 = 1";
```

###Array as data source, multiple columns using INCLUDE mode

```php
$data = array(
	'col_1'=>17,
	'col_2'=>'s_31',
	'col_3'=>'s_19'
);

$columns = array("mode"=>"include", "col"=>array("col_1", "col_3") );

$result = $tdb->runUpdateQuery($struct, $data, $args = "overwrite_all", $columns, $check=true);
```

```sql
"UPDATE test_a SET col_1 = 17, col_3 = 's_19' WHERE 1 = 1";
```

###Array as data source, single column using EXCLUDE mode

```php
$data = array(
	'col_1'=>17,
	'col_2'=>'s_31',
	'col_3'=>'s_19'
);

$columns = array("mode"=>"exclude", "col"=>"col_1");

$result = $tdb->runUpdateQuery($struct, $data, $args = "overwrite_all", $columns, $check=true);
```

```sql
"UPDATE test_a SET col_2 = 's_31', col_3 = 's_19' WHERE 1 = 1";
```

###Array as data source, multiple columns using EXCLUDE mode

```php
$data = array(
	'col_1'=>17,
	'col_2'=>'s_31',
	'col_3'=>'s_19'
);

$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

$result = $tdb->runUpdateQuery($struct, $data, $args = "overwrite_all", $columns, $check=true);
```

```sql
"UPDATE test_a SET col_2 = 's_31' WHERE 1 = 1";
```

###Array as data source, with constraints

```php
$data = array(
	'col_1'=>17,
	'col_2'=>'s_31'
);

$args = array(

	array("col"=>"col_1", "op"=>"=", "val"=>53)
);

$result = $tdb->runUpdateQuery($struct, $data, $args, $columns=null, $check=true);
```
```sql
"UPDATE test_a SET col_1 = 17, col_2 = 's_31' WHERE 1 = 1 AND col_1 = 53";
```

###Object as data source

```php
$data = new stdClass();
$data->col_1 = 17;
$data->col_2 = "s_31";

$result = $tdb->runUpdateQuery($struct, $data, $args="overwrite_all", $columns=null, $check=true);
```

```sql
"UPDATE test_a SET col_1 = 17, col_2 = 's_31', col_3 = NULL WHERE 1 = 1";
```

###Object as data source, with constraints

```php
$data = new stdClass();
$data->col_1 = 17;
$data->col_2 = "s_31";

$args = array(
	    array("col"=>"col_1", "op"=>"=", "val"=>11)
);

$result = $tdb->runUpdateQuery($struct, $data, $args, $columns=null, $check=true);
```

```sql
"UPDATE test_a SET col_1 = 17, col_2 = 's_31', col_3 = NULL WHERE 1 = 1 AND col_1 = 11";
```
