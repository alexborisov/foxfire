#FOX_db::runSelectQueryCol()

This is our simplified single-table select function. For the standard version, see FOX_db::runSelectQuery(), for the advanced version with join capabilities, see FOX_db::runSelectQueryJoin()

```php
/**
 * Runs single-column keyed SELECT query on one of the plugin's db tables
 *
 * @param array $struct | Structure of the db table, @see class FOX_db header for examples
 *
 * @param string $col | Column name to use for WHERE construct
 * @param string $op | Comparison operator ">=", "<=", ">", "<", "=", "!=", "<>"
 * @param int/string $val | Comparison value to use in WHERE construct
 *
 * @param bool/array $columns | Columns to use in query. NULL to select all columns. FALSE to select no columns.
 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
 *
 * @param array $ctrl | Control parameters for the query
 *	=> VAL @param int $page | Set current page (used when traversing multi-page data sets)
 *	=> VAL @param int $per_page | Max number of rows to return in a query / number of rows to return per page when transversing a multi-page data set
 *	=> VAL @param int $offset | Shift results page forward or backward "n" items within the returned data set
 *	=> ARR @param array $sort | Sort results by supplied parameters. Multi-dimensional sorts possible by passing multiple arrays.
 *	    => ARR @param int '' | Array index
 *		=> VAL @param string $col | Name of column to sort by
 *		=> VAL @param string $sort | Direction to sort in. "ASC" | "DESC"
 *	=> ARR @param array $group | Apply SQL GROUP to columns. Multi-dimensional group possible by passing multiple arrays.
 *	    => ARR @param int '' | Array index
 *		=> VAL @param string $col | Name of column to apply GROUP to
 *		=> VAL @param string $sort | Direction to sort in. "ASC" | "DESC"
 *
 *	=> VAL @param bool/string/array $count | Return a count of db rows. Bool true to use COUNT(*). Single column as string. Multiple columns as array.
 *      => VAL @param bool/string/array $sum | Return a sum of db rows. Single column as string. Multiple columns as array.
 *
 *	=> VAL @param string $format | @see FOX_db::runQuery() for detailed info on format string
 *	=> VAL @param string $key_col | Column name to get key names from when using $format="key" or $format="asc"
 *	=> VAL @param string $asc_col | Column name to use as value when using $format="asc"
 *
 * @param bool $check | set false to disable *all* query error checking.
 *
 * @return bool/array | False on failure. Query array on success.
 */

public function runSelectQueryCol($struct, $col, $op, $val, $columns=null, $ctrl=null, $check=true){

 // ...

}
```

###$struct

Structure of the table to run the query on. See class layout page for more info.

###$col

Single column name, as string, to run the comparison on.

###$op

Comparison operator to use. Valid operators are: "<", ">", "=", "!=", and "<>". Only "=", "!=", and "<>" can be used when passing multiple values as an array to test against.

###$val

Single value to test against as int, float, or string. Multiple values to test against as array of int, float, or string.

###$columns

Database columns to fetch from the SQL server. If no array is passed, the function will return all columns in the table. If a column in the database for a given row is empty, its key in the result object/array will contain a NULL value. Typical usage:

```php
$columns = array( $mode=>"exclude", $col=>"col_4");

$columns = array(
		    $mode=>"include",
		    $col=>"array("col_3", "col_1", "col_5")
);
```

###$mode

If set to "include", the function will only use the column or columns specified in $col. If set to "exclude", the function will exclude all columns except the column or columns specified in $col.

###$col

Single column name passed as string or multiple column names passed as array of strings. Columns excluded from, or not included in, the query are completely removed from the generated SQL statement.

###$ctrl

Control parameters for the query. Typical usage:

```php
$ctrl = array(
		"page"=>1, "per_page"=>20, "offset"=>0,
		"sort"=>array(
				array("col"=>"col_1", "dir"=>"ASC"),
				array("col"=>"col_2", "dir"=>"DESC")
		),
		"format"=>"array_array"
);
```

###$page

Sets the current results page. When working with large data sets it's often necessary to break the results of a query into pages. For example, say a site search query returns 1,000 results. We decide to break them into pages of 50 results. Setting $page=2 would show results 51 to 100.

###$per_page

Sets the maximum number of rows to return in a results page, and as a result, the maximum number of results to return in the query.

###$offset

Shift the results page forward or backward "n" items. If $per_page=50, $page=2, and $offset=0, the query will return results 51 to 100. But if $per_page=50, $page=2, and $offset=3, the query will return results 54 to 103.

###$sort

Column and direction to sort results in. An array of one or more arrays containing keys "$col" and "$sort" where $col is the name of the column to sort by and $sort is the direction to sort in, either "ASC" or "DESC. If multiple arrays are passed, the sorts will be cascaded, with the first array having the highest priority and the last array having the lowest.

###$group

Column and direction to group results by. An array of one or more arrays containing keys "$col" and "$sort" where $col is the name of the column to group by and $sort is the direction to sort in, either "ASC" or "DESC. If multiple arrays are passed, the function will cascade the group clauses, with the first array having the highest priority and the last array having the lowest. Note: SQL "GROUP BY" clause has a counterintuitive meaning and does not actually "group" results in the conventional sense of the word. See the MySQL docs.

###$count

Return a count of db rows. Bool true to count all matching rows in table. Single column as string. Multiple columns as an array of strings.

###$sum

Return the sum of one or more columns. Single column as string. Multiple columns as an array of strings.

###$format

Results format string. See the query formatter section for details.

###$key_col

Column name to get key names from when using $format="key" or $format="asc". See the query formatter section for details.

###$asc_col

Column name to use as value when using $format="asc". See the query formatter section for details.

###$check

If not set, or set to true, the function will perform basic error checking on the query before running it.

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

###No columns selected

```php
$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null
);

$result = $tdb->runSelectQueryCol($struct, $col="col_1", $op="<>", $val=37, $columns=null, $ctrl, $check=true);
```

```sql
"SELECT * FROM test_a WHERE 1 = 1 AND col_1 <> 37"
```

###Single column selected using INCLUDE mode

```php
$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null
);

$columns = array("mode"=>"include", "col"=>"col_1");

$result = $tdb->runSelectQueryCol($struct, $col="col_1", $op="<>", $val=37, $columns, $ctrl, $check=true);
```

```sql
"SELECT col_2 FROM test_a WHERE 1 = 1 AND col_1 <> 37"
```

###Multiple columns selected using INCLUDE mode

```php
$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null
);

$columns = array("mode"=>"include", "col"=>array("col_1", "col_2") );

$result = $tdb->runSelectQueryCol($struct, $col="col_1", $op="<>", $val=37, $columns, $ctrl, $check=true);
```

```sql
"SELECT col_1, col_2 FROM test_a WHERE 1 = 1 AND col_1 <> 37"
```

###Single column skipped using EXCLUDE mode

```php
$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null
);

$columns = array("mode"=>"exclude", "col"=>"col_1");

$result = $tdb->runSelectQueryCol($struct, $col="col_1", $op="<>", $val=37, $columns, $ctrl, $check=true);

```

```sql
"SELECT col_2, col_3 FROM test_a WHERE 1 = 1 AND col_1 <> 37"
```

###Multiple columns skipped using EXCLUDE mode

```php
$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null
);

$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

$result = $tdb->runSelectQueryCol($struct, $col="col_1", $op="<>", $val=37, $columns, $ctrl, $check=true);
```

```sql
"SELECT col_2 FROM test_a WHERE 1 = 1 AND col_1 <> 37"
```

###Count items, bool true

```
$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null,
	'count'=>true
);

$result = $tdb->runSelectQueryCol($struct, $col="col_1", $op="<>", $val=37, $columns=null, $ctrl, $check=true);
```

```sql
"SELECT COUNT(*) FROM test_a WHERE 1 = 1 AND col_1 <> 37"
```

###Count items, single column as string

```php
$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null,
	'count'=>"col_3"
);

$result = $tdb->runSelectQueryCol($struct, $col="col_1", $op="<>", $val=37, $columns, $ctrl, $check=true);
```

```sql
"SELECT COUNT(col_3) FROM test_a WHERE 1 = 1 AND col_1 <> 37"
```

###Count items, multiple columns as array

```php
$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null,
	'count'=>array("col_1","col_3")
);

$result = $tdb->runSelectQueryCol($struct, $col="col_1", $op="<>", $val=37, $columns, $ctrl, $check=true);
```

```sql
"SELECT COUNT(col_1), COUNT(col_3) FROM test_a WHERE 1 = 1 AND col_1 <> 37"
```

###Sort items, single column

```php
$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null,
	'sort'=>array(
			array("col"=>"col_1", "sort"=>"DESC")
	 )
);

$result = $tdb->runSelectQueryCol($struct, $col="col_1", $op="<>", $val=37, $columns=null, $ctrl, $check=true);
```

```sql
"SELECT * FROM test_a WHERE 1 = 1 AND col_1 <> 37 ORDER BY col_1 DESC"
```

###Sort items, multiple columns

```php

$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null,
	'sort'=>array( array("col"=>"col_1", "sort"=>"DESC"),
		       array("col"=>"col_3", "sort"=>"ASC")
         )
);

$result = $tdb->runSelectQueryCol($struct, $col="col_1", $op="<>", $val=37, $columns=null, $ctrl, $check=true);
```

```sql
"SELECT * FROM test_a WHERE 1 = 1 AND col_1 <> 37 ORDER BY col_1 DESC, col_3 ASC"
```

###Group items, single column

```php
$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null,
	'group'=>array( array("col"=>"col_1", "sort"=>"DESC") )
);

$result = $tdb->runSelectQueryCol($struct, $col="col_1", $op="<>", $val=37, $columns=null, $ctrl, $check=true);
```

```sql
"SELECT * FROM test_a WHERE 1 = 1 AND col_1 <> 37 GROUP BY col_1 DESC"
```

###Group items, multiple columns

```php
$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null,
	'group'=>array( array("col"=>"col_1", "sort"=>"DESC"),
		        array("col"=>"col_3", "sort"=>"ASC")
         )
);

$result = $tdb->runSelectQueryCol($struct, $col="col_1", $op="<>", $val=37, $columns=null, $ctrl, $check=true);
```

```sql
"SELECT * FROM test_a WHERE 1 = 1 AND col_1 <> 37 GROUP BY col_1 DESC, col_3 ASC"

```

###Paging, limit number of returned results

```php
$ctrl = array(
	// Not setting the "sort" flag is completely valid. The db will just page through the
	// items in whatever order they are stored in the table.
	'page'=>1,
	'per_page'=>7
);

$result = $tdb->runSelectQueryCol($struct, $col="col_1", $op="<>", $val=37, $columns=null, $ctrl, $check=true);
```

```sql
"SELECT * FROM test_a WHERE 1 = 1 AND col_1 <> 37 LIMIT 0, 7"
```

###Paging, offset by X pages

```php
$ctrl = array(
	// Not setting the "sort" flag is completely valid. The db will just page through the
	// items in whatever order they are stored in the table.
	'page'=>5,
	'per_page'=>7
);

$result = $tdb->runSelectQueryCol($struct, $col="col_1", $op="<>", $val=37, $columns=null, $ctrl, $check=true);
```

```sql
"SELECT * FROM test_a WHERE 1 = 1 AND col_1 <> 37 LIMIT 28, 7"
```
