#INSERT Queries

FOX_db has two functions which are used to perform INSERT queries: FOX_db::runInsertQuery() and FOX_db::runInsertQueryMulti().

##FOX_db::runInsertQuery()

FOX_db::runInsertQuery() can only insert one row into the database at a time. It can use both arrays and objects as its data source.

```php
/**
 * Runs an INSERT query that inserts a SINGLE row into one of the plugin's db tables.
 *
 * @param array $struct | Table structure array
 *
 * @param array/object $data | Row array
 *	    => KEY @param string | Name of the db column this key describes
 *		=> VAL @param int/string | Value to assign to the column
 *
 * @param bool/array $columns | Columns to use in query. NULL to select all columns.
 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
 *
 * @param bool $check | set false to disable *all* query error checking.
 *
 * @return int | Int 0 on failure. Int number of rows affected on success.
 */

public function runInsertQuery($struct, $data, $columns=null, $check=true){

 // ...

}
```

####$struct

This variable is the table definition array for the table to insert data into. See class layout page for more info.

####$data

The data source for the query. When using an array, it has to follow this structure below.
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

####$columns

The database columns to use during the insert query.
If no array is passed, the function will try to save all columns listed in $struct to the database during the query. If keys for the columns are not present in the $data array, or variables for columns don't exist in the $data object, the function will write NULL values for the missing columns to the database.

If an array is passed, it has to have the following structure:

```php
array( $mode=>"include|exclude", $col=>"col_name | array("col_name_1", "col_name_2") );
```

####$mode

If set to "include", the function will only use the column or columns specified in $col. If set to "exclude", the function will exclude all columns except the column or columns specified in $col.

####$col

Single column name passed as string, or multiple column names passed as array of strings. Columns excluded from, or not included in, the query are completely removed from the generated SQL statement. This can change the effect of the query, depending on whether or not default values were set in the table definition.

####$check

If not set, or set to true, the function will perform basic error checking on the query before running it.

##FOX_db::runInsertQueryMulti()

FOX_db::runInsertQueryMulti() functions exactly like FOX_db::runInsertQuery(), except its $data parameter is an array of arrays. It combines all of the rows into passed in its $data array into a single query.
For queries containing large numbers of rows with large amounts of data in each row, it may be necessary to split the operation into multiple queries to avoid exceeding the SQL server's max_allowed_packet setting (approx 1MB, but is server specific).

```php
/**
 * Runs an INSERT query that inserts MULTIPLE rows into one of the plugin's db tables.
 *
 * @param array $struct | Table structure array
 *
 * @param array/object $data | Array of row arrays
 *	=> ARR @param int '' | Individual row array
 *	    => KEY @param string | Name of the db column this key describes
 *		=> VAL @param int/string | Value to assign to the column
 *
 * @param bool/array $columns | Columns to use in query. NULL to select all columns.
 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
 *
 * @param bool $check | set false to disable *all* query error checking.
 *
 * @return int | Int 0 on failure. Int number of rows affected on success.
 */

 public function runInsertQueryMulti($struct, $data, $columns=null, $check=true){

    // ...
 }
```
 
####$data

The data source for the query. It has to be an array of arrays with this structure:

```php
$data = array(
		array( "col_name_1"=>"val", "col_name_2"=>"val", "col_name_2"=>"val"),
		array( "col_name_1"=>"val", "col_name_2"=>"val", "col_name_2"=>"val"),
		array( "col_name_1"=>"val", "col_name_2"=>"val", "col_name_2"=>"val")
```

In the future, you will also be able to use an array of objects with this structure:

```php
class bar {

        var $col_1;            // Column variable 1
        var $col_2;            // Column variable 2
        var $col_3;            // Column variable 3
}
$data = array(
		$bar_1, $bar_2, $bar_3
);
```

#Usage Examples

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

	array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_53')
);

$tdb->runInsertQuery($struct, $data, $columns=null, $check=true);
```

```sql
"INSERT INTO test_a (col_1, col_2, col_3) VALUES (17, 's_31', 's_53')";
```

###Array as data source, single column using INCLUDE mode

```php
$data = array(

	array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_19')
);

$columns = array("mode"=>"include", "col"=>"col_2");

$tdb->runInsertQuery($struct, $data, $columns, $check=true);
```

```sql
"INSERT INTO test_a (col_2) VALUES ('s_31')";
```

###Array as data source, multiple columns using INCLUDE mode

```php
$data = array(

	array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_19')
);

$columns = array("mode"=>"include", "col"=>array("col_1", "col_3") );

$tdb->runInsertQuery($struct, $data, $columns, $check=true);
```

```sql
"INSERT INTO test_a (col_1, col_3) VALUES (17, 's_19')";
```

###Array as data source, single column using EXCLUDE mode

```php
$data = array(

	array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_19')
);

$columns = array("mode"=>"exclude", "col"=>"col_1");

$tdb->runInsertQuery($struct, $data, $columns, $check=true);
```

```sql
"INSERT INTO test_a (col_2, col_3) VALUES ('s_31', 's_19')";
```

###Array as data source, multiple columns using EXCLUDE mode

```php
$data = array(

	array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_19')
);

$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

$tdb->runInsertQuery($struct, $data, $columns, $check=true);
```

```sql
"INSERT INTO test_a (col_2) VALUES ('s_31')";
```

###Array as data source, multiple inserts in single query

```php
$data = array(

	array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_53'),
	array('col_1'=>94, 'col_2'=>'s_66', 'col_3'=>'s_81'),
	array('col_1'=>21, 'col_2'=>'s_13', 'col_3'=>'s_42')
);

$tdb->runInsertQueryMulti($struct, $data, $columns, $check=true);
```

```sql
"INSERT INTO {$table} (col_1, col_2, col_3) VALUES (17, 's_31', 's_53'), (94, 's_66', 's_81'), (21, 's_13', 's_42')";
```

###Object as data source

```php
$data = new stdClass();
$data->col_1 = 17;
$data->col_2 = "s_31";

$tdb->runInsertQuery($struct, $data, $columns, $check=true);
```
```sql
"INSERT INTO test_a (col_1, col_2, col_3) VALUES (17, 's_31', NULL)";
```
