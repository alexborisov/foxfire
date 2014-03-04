#FOX_db::runSelectQueryJoin()

This is our advanced select function with multi-table joins. For the standard version, see FOX_db::runSelectQuery(), for the simplified version, see FOX_db::runSelectQueryCol()

```php
/**
 * Runs a SELECT query with a JOIN statement on a pair of the plugin's db tables.
 *
 * @param array $primary | Primary table class name and args
 *	=> VAL @param string/array $class | Name of class that owns the primary table (as string), or the class's $struct array (as array)
 *	=> ARR @param array $args | Args in the form: array("col"=>column_name, "op" => "<, >, =, !=", "val" => "int | string | array()")
 *	    => ARR @param int '' | Array index
 *		=> VAL @param string $col | Name of the column in the db table this key describes
 *		=> VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
 *		=> VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
 *
 * @param array $join | Joined tables class names and args
 *	=> ARR @param int '' | Array index
 *	    => VAL @param string/array $class | Name of class that owns the joined table (as string), or the class's $struct array (as array)
 *	    => ARR @param array $on | Join condition for this table
 *		=> VAL @param string $pri | Name of primary table column to join on
 *		    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
 * 	 	    => VAL @param string $sec | Name of joined table column to join on
 *	    => ARR @param array $args | Args in the form: array("col"=>column_name, "op"=>"<, >, =, !=", "val"=>"int | string | array()")
 *		=> ARR @param int '' | Array index
 *		    => VAL @param string $col | Name of the column this key describes
 *		    => VAL @param string $op | SQL comparison operator to use: ">=" | "<=" | ">" | "<" | "=" | "!=" | "<>"
 *		    => VAL @param int/string/array $val | Value or values to test against. Single value as int/string. Multiple values as array.
 *
 * @param array $ctrl | Control parameters for the query
 *	=> VAL @param int $page | Set current page (used when traversing multi-page data sets)
 *	=> VAL @param int $per_page | Max number of rows to return in a query / number of rows to return per page when transversing a multi-page data set
 *	=> VAL @param int $offset | Shift results page forward or backward "n" items within the returned data set
 *	=> ARR @param array $sort | Sort results by supplied parameters. Multi-dimensional sorts possible by passing multiple arrays.
 *	    => ARR @param int '' | Array index
 *		=> VAL @param string $class | Class name that owns the table
 *		=> VAL @param string $col | Name of column to sort by
 *		=> VAL @param string $sort | Direction to sort in. "ASC" | "DESC"
 *	=> ARR @param array $group | Apply SQL GROUP to columns. Multi-dimensional group possible by passing multiple arrays.
 *	    => ARR @param int '' | Array index
 *		=> VAL @param string $class | Class name that owns the table
 *		=> VAL @param string $col | Name of column to apply GROUP to
 *		=> VAL @param string $sort | Direction to sort in. "ASC" | "DESC"
 *	=> ARR @param bool/array $count | Count columns. Bool TRUE to use COUNT(DISTINCT primary_table.*)
 *	    => ARR @param int '' | Array index
 *		=> VAL @param string $class | Class name that owns the table
 *		=> VAL @param string $col | Column to count
 *	=> ARR @param bool/array $sum | Sum columns.
 *	    => ARR @param int '' | Array index
 *		=> VAL @param string $class | Class name that owns the table
 *		=> VAL @param string $col | Column to sum
 *	=> VAL @param string $format | @see FOX_db::runQuery() for detailed info on format string
 *	=> VAL @param string $key_col | Column name to get key names from when using $format="key" or $format="asc"
 *	=> VAL @param string $asc_col | Column name to use as value when using $format="asc"
 *
 * @param bool/array $columns | Primary table columns to use in query. NULL to select all columns. FALSE to select no columns.
 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
 *
 * @param bool $check | set false to disable *all* query error checking.
 *
 * @return bool/int/array | False on failure, int on count, array of rows on success.
 */

public function runSelectQueryJoin($primary, $join, $columns=null, $ctrl=null, $check=true){

 // ...

}
```

####$primary

Primary table class name and args. Typical usage:

```php
$primary = array(  "class"=>"BPM_primaryTableClass",
		"args"=>array( array("col"=>col_1, "op"=>"<", "val"=>"20"),
			       array("col"=>col_2, "op"=>"=", "val"=>array("red", "green", "blue")
	                     )
);
```

####$class

Name of the class that owns the primary table (as string), or the class's $struct array. See the class layout page for more info.

####$args

Test condition to select rows based on. If no $args parameter is passed, all rows in the table will be selected.

####$col

Single column name, as string, to run the comparison on.

####$op

Comparison operator to use. Valid operators are: "<", ">", "=", "!=", and "<>". Only "=", "!=", and "<>" can be used when passing multiple values as an array to test against.

####$val

Single value to test against as int, float, or string. Multiple values to test against as array of int, float, or string.

####$join

Joined table(s) class name and args. Typical usage:

```php
$join = array(  array( "class"=>"BPM_joinTableClass_1",
		       "on"=>array( "pri"=>"user_id", "op"=>"=", "sec"=>"user_id"),
		       "args"=>array( array("col"=>"karma", "op"=>">", "val"=>20),
			              array("col"=>"location", "op"=>"=", "val"=>array("canada", "uk", "australia")
	               )
	        ),
		array( "class"=>"BPM_joinTableClass_2",
		       "on"=>array( "pri"=>"user_id", "op"=>"=", "sec"=>"user_id"),
		       "args"=>array( array("col"=>"media_items", "op"=>">", "val"=>5),
			              array("col"=>"item_class", "op"=>"!=", "val"=>array("hidden", "private", "nsfw")
	               )
	        )		     
);
```

####$class

Name of the class to join to (as string), or the class's $struct array. See the class layout page for more info.

####$on

Test condition to join the primary and joined tables on.

####$pri

Column name in the primary table.
"$op"

Comparison operator to use. Valid operators are: "<", ">", "=", "!=", and "<>". Only "=", "!=", and "<>" can be used when passing multiple values as an array to test against.

####$sec

Column name in the joined table

####$args

Test condition(s) to filter rows in the joined table by.

####$col

Single column name, as string, to run the comparison on.

####$op

Comparison operator to use. Valid operators are: "<", ">", "=", "!=", and "<>". Only "=", "!=", and "<>" can be used when passing multiple values as an array to test against.

####$val

Single value to test against as int, float, or string. Multiple values to test against as array of int, float, or string.

####$columns

Database columns in the primary table to fetch from the SQL server.
If no array is passed, the function will return all columns in the table. If a column in the database for a given row is empty, its key in the result object/array will contain a NULL value. Typical usage:

```php
$columns = array( $mode=>"exclude", $col=>"col_4");

$columns = array(
		    $mode=>"include",
		    $col=>"array("col_3", "col_1", "col_5")
);
```

####$mode

If set to "include", the function will only use the column or columns specified in $col. If set to "exclude", the function will exclude all columns except the column or columns specified in $col.

####$col

Single column name passed as string or multiple column names passed as array of strings. Columns excluded from, or not included in, the query are completely removed from the generated SQL statement.

####$ctrl

Control parameters for the query. Typical usage:

```php
$ctrl = array(
		"page"=>1, "per_page"=>20, "offset"=>0,
		"sort"=>array(
				array("class"=>$primary_table, "col"=>"col_1", "dir"=>"ASC"),
				array("class"=>$join_table_1, "col"=>"col_2", "dir"=>"DESC")
		),
		"format"=>"array_array"
);
```

####$page

Sets the current results page. When working with large data sets it's often necessary to break the results of a query into pages. For example, say a site search query returns 1,000 results. We decide to break them into pages of 50 results. Setting $page=2 would show results 51 to 100.

####$per_page

Sets the maximum number of rows to return in a results page, and as a result, the maximum number of results to return in the query.

####$offset

Shift the results page forward or backward "n" items. If $per_page=50, $page=2, and $offset=0, the query will return results 51 to 100. But if $per_page=50, $page=2, and $offset=3, the query will return results 54 to 103.

####$sort

Column and direction to sort results in. An array of one or more arrays containing keys "$class", "$col" and "$sort" where $class is the name of the database class that owns the table or its structure array, $col is the name of the column to sort by and $sort is the direction to sort in, either "ASC" or "DESC. If multiple arrays are passed, the sorts will be cascaded, with the first array having the highest priority and the last array having the lowest.

####$group

Column and direction to group results by. An array of one or more arrays containing keys "$class", "$col" and "$sort" where $class is the name of the database class that owns the table or its structure array, $col is the name of the column to group by and $sort is the direction to sort in, either "ASC" or "DESC. If multiple arrays are passed, the function will cascade the group clauses, with the first array having the highest priority and the last array having the lowest. Note: SQL "GROUP BY" clause has a counterintuitive meaning and does not actually "group" results in the conventional sense of the word. See the MySQL docs.

####$count

Return a count of db rows. An array containing keys "$class" and "$col, where "$class" is is the name of the database class that owns the table or its structure array and $col can accept the following: bool true to count all matching rows in table. Single column as string. Multiple columns as an array of strings.

####$sum

Return the sum of a column. An array containing keys "$class" and "$col, where "$class" is is the name of the database class that owns the table or its structure array and $col can accept the following: bool true to count all matching rows in table. Single column as string. Multiple columns as an array of strings.

####$format

Results format string. See the query formatter section for details.

####$key_col

Column name to get key names from when using $format="key" or $format="asc". See the query formatter section for details.

####$asc_col

Column name to use as value when using $format="asc". See the query formatter section for details.

####$check

If not set, or set to true, the function will perform basic error checking on the query before running it.

#Examples

The examples below are working code that can be run on a live database. Use this code to install the test table.

```php
// Primary array
// ===================================================

$struct_primary = array(

	"table" => "test_a",
	"engine" => "InnoDB",
	"columns" => array(
	    "col_1" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
	    "col_2" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
	    "col_3" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false)
	 )
);
```

```php
// Join array
// ===================================================

$struct_join = array(

	"table" => "test_b",
	"engine" => "InnoDB",
	"columns" => array(
	    "col_4" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
	    "col_5" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
	    "col_6" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false)
	 )
);
```
```php
$tdb = new FOX_db();
$result = $tdb->runAddTable($struct_primary);
$result = $tdb->runAddTable($struct_join);
No columns skipped

$primary = array( "class"=>$struct_primary, 
		  "args"=>array(
				 array( "col"=>"col_1", "op"=>"=", "val"=>1)
		  )
);

$join = array(
		array(
		    "class"=>$struct_join,
		    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
		    "args"=>array(
		                  array( "col"=>"col_6", "op"=>">=", "val"=>6)
		    )
		)
);

$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null
);

$result = $tdb->runSelectQueryJoin($primary, $join, $columns=null, $ctrl, $check=true);
```
```sql
"SELECT DISTINCT struct_primary.* FROM struct_primary
 INNER JOIN struct_join AS alias_struct_join
 ON (struct_primary.col_1 = alias_struct_join.col_4)
 WHERE 1 = 1 AND struct_primary.col_1 = 1 AND alias_struct_join.col_6 >= 6"
```

###Single column selected using INCLUDE mode

```php
$primary = array( "class"=>$struct_primary,
		  "args"=>array(
				 array( "col"=>"col_1", "op"=>"=", "val"=>1)
		  )
);

$join = array(
		array(
		    "class"=>$struct_join,
		    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
		    "args"=>array(
		                  array( "col"=>"col_6", "op"=>">=", "val"=>6)
		    )
		)
);

$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null
);

$columns = array("mode"=>"include", "col"=>"col_1");

$result = $tdb->runSelectQueryJoin($primary, $join, $columns, $ctrl, $check=true);
```

```sql
"SELECT DISTINCT struct_primary.col_1 FROM struct_primary
 INNER JOIN struct_join AS alias_struct_join
 ON (struct_primary.col_1 = alias_struct_join.col_4)
 WHERE 1 = 1 AND struct_primary.col_1 = 1 AND alias_struct_join.col_6 >= 6"
```

###Multiple columns selected using INCLUDE mode

```php
$primary = array( "class"=>$struct_primary,
		  "args"=>array(
				 array( "col"=>"col_1", "op"=>"=", "val"=>1)
		  )
);

$join = array(
		array(
		    "class"=>$struct_join,
		    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
		    "args"=>array(
		                  array( "col"=>"col_6", "op"=>">=", "val"=>6)
		    )
		)
);

$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null
);

$columns = array("mode"=>"include", "col"=>array("col_1", "col_2") );

$result = $tdb->runSelectQueryJoin($primary, $join, $columns, $ctrl, $check=true);
```

```sql
"SELECT DISTINCT struct_primary.col_1, struct_primary.col_2 FROM struct_primary
 INNER JOIN struct_join AS alias_struct_join
 ON (struct_primary.col_1 = alias_struct_join.col_4)
 WHERE 1 = 1 AND struct_primary.col_1 = 1 AND alias_struct_join.col_6 >= 6"
```

###Single column skipped using EXCLUDE mode

```php
$primary = array( "class"=>$struct_primary,
		  "args"=>array(
				 array( "col"=>"col_1", "op"=>"=", "val"=>1)
		  )
);

$join = array(
		array(
		    "class"=>$struct_join,
		    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
		    "args"=>array(
		                  array( "col"=>"col_6", "op"=>">=", "val"=>6)
		    )
		)
);

$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null
);

$columns = array("mode"=>"exclude", "col"=>"col_1");

$result = $tdb->runSelectQueryJoin($primary, $join, $columns, $ctrl, $check=true);
```

```sql
"SELECT DISTINCT struct_primary.col_2, struct_primary.col_3 FROM struct_primary
 INNER JOIN struct_join AS alias_struct_join
 ON (struct_primary.col_1 = alias_struct_join.col_4)
 WHERE 1 = 1 AND struct_primary.col_1 = 1 AND alias_struct_join.col_6 >= 6"
```

###Multiple columns skipped using EXCLUDE mode

```php
$primary = array( "class"=>$struct_primary,
		  "args"=>array(
				 array( "col"=>"col_1", "op"=>"=", "val"=>1)
		  )
);

$join = array(
		array(
		    "class"=>$struct_join,
		    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
		    "args"=>array(
		                  array( "col"=>"col_6", "op"=>">=", "val"=>6)
		    )
		)
);

$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null
);

$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

$result = $tdb->runSelectQueryJoin($primary, $join, $columns, $ctrl, $check=true);
```

```sql
"SELECT DISTINCT struct_primary.col_2 FROM struct_primary
 INNER JOIN struct_join AS alias_struct_join
 ON (struct_primary.col_1 = alias_struct_join.col_4)
 WHERE 1 = 1 AND struct_primary.col_1 = 1 AND alias_struct_join.col_6 >= 6"
```

###Count items, bool true

```php
$primary = array( "class"=>$struct_primary,
		  "args"=>array(
				 array( "col"=>"col_1", "op"=>"=", "val"=>1)
		  )
);

$join = array(
		array(
		    "class"=>$struct_join,
		    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
		    "args"=>array(
		                  array( "col"=>"col_6", "op"=>">=", "val"=>6)
		    )
		)
);

$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null,
	'count'=>true
);

$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

$result = $tdb->runSelectQueryJoin($primary, $join, $columns, $ctrl, $check=true);
```

```sql
"SELECT COUNT(DISTINCT struct_primary.*) FROM struct_primary
 INNER JOIN struct_join AS alias_struct_join
 ON (struct_primary.col_1 = alias_struct_join.col_4)
 WHERE 1 = 1 AND struct_primary.col_1 = 1 AND alias_struct_join.col_6 >= 6"
```

###Count items, single primary table column

```php
$primary = array( "class"=>$struct_primary,
		  "args"=>array(
				 array( "col"=>"col_1", "op"=>"=", "val"=>1)
		  )
);

$join = array(
		array(
		    "class"=>$struct_join,
		    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
		    "args"=>array(
		                  array( "col"=>"col_6", "op"=>">=", "val"=>6)
		    )
		)
);

$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null,
	'count'=>array( array("class"=>$struct_primary, "col"=>"col_1") )
);

$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

$result = $tdb->runSelectQueryJoin($primary, $join, $columns, $ctrl, $check=true);
```

```sql
"SELECT COUNT(DISTINCT struct_primary.col_1) FROM struct_primary
 INNER JOIN struct_join AS alias_struct_join
 ON (struct_primary.col_1 = alias_struct_join.col_4)
 WHERE 1 = 1 AND struct_primary.col_1 = 1 AND alias_struct_join.col_6 >= 6"
```

###Count items, multiple columns

```php
$primary = array( "class"=>$struct_primary,
		  "args"=>array(
				 array( "col"=>"col_1", "op"=>"=", "val"=>1)
		  )
);

$join = array(
		array(
		    "class"=>$struct_join,
		    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
		    "args"=>array(
		                  array( "col"=>"col_6", "op"=>">=", "val"=>6)
		    )
		)
);

$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null,
	'count'=>array(
			array("class"=>$struct_primary, "col"=>"col_1"),
			array("class"=>$struct_join, "col"=>"col_6")
	)
);

$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

$result = $tdb->runSelectQueryJoin($primary, $join, $columns, $ctrl, $check=true);
```

```sql
"SELECT COUNT(DISTINCT struct_primary.col_1), COUNT(DISTINCT struct_join.col_6)
 FROM struct_primary
 INNER JOIN struct_join AS alias_struct_join
 ON (struct_primary.col_1 = alias_struct_join.col_4)
 WHERE 1 = 1 AND struct_primary.col_1 = 1 AND alias_struct_join.col_6 >= 6"
```

###Sort items, primary table used as sort class

```php
$primary = array( "class"=>$struct_primary,
		  "args"=>array(
				 array( "col"=>"col_1", "op"=>"=", "val"=>1)
		  )
);

$join = array(
		array(
		    "class"=>$struct_join,
		    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
		    "args"=>array(
		                  array( "col"=>"col_6", "op"=>">=", "val"=>6)
		    )
		)
);

$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null,
	'sort'=>array( array("class"=>$struct_primary, "col"=>"col_1", "sort"=>"DESC") )
	 // NOTE: the column(s) you are ordering by must be in the returned data set

);

$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

$result = $tdb->runSelectQueryJoin($primary, $join, $columns, $ctrl, $check=true);
```

```sql
"SELECT DISTINCT struct_primary.col_2
 FROM struct_primary
 INNER JOIN struct_join AS alias_struct_join
 ON (struct_primary.col_1 = alias_struct_join.col_4)
 WHERE 1 = 1 AND struct_primary.col_1 = 1 AND alias_struct_join.col_6 >= 6
 ORDER BY struct_primary.col_1 DESC"
```

###Sort items, primary table used as sort class, multiple sort columns

```php
$primary = array( "class"=>$struct_primary,
		  "args"=>array(
				 array( "col"=>"col_1", "op"=>"=", "val"=>1)
		  )
);

$join = array(
		array(
		    "class"=>$struct_join,
		    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
		    "args"=>array(
		                  array( "col"=>"col_6", "op"=>">=", "val"=>6)
		    )
		)
);

$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null,
	'sort'=>array( array("class"=>$struct_primary, "col"=>"col_1", "sort"=>"DESC"),
		       array("class"=>$struct_primary, "col"=>"col_2", "sort"=>"ASC")
		       // NOTE: the column(s) you are ordering by must be in the returned data set
		     )
);

$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

$result = $tdb->runSelectQueryJoin($primary, $join, $columns, $ctrl, $check=true);
```

```sql
"SELECT DISTINCT struct_primary.col_2
 FROM struct_primary
 INNER JOIN struct_join AS alias_struct_join
 ON (struct_primary.col_1 = alias_struct_join.col_4)
 WHERE 1 = 1 AND struct_primary.col_1 = 1 AND alias_struct_join.col_6 >= 6
 ORDER BY struct_primary.col_1 DESC, ORDER BY struct_primary.col_2 ASC"
```

###Sort items, primary and joined tables used as sort classes

```php
$primary = array( "class"=>$struct_primary,
		  "args"=>array(
				 array( "col"=>"col_1", "op"=>"=", "val"=>1)
		  )
);

$join = array(
		array(
		    "class"=>$struct_join,
		    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
		    "args"=>array(
		                  array( "col"=>"col_6", "op"=>">=", "val"=>6)
		    )
		)
);

$ctrl = array(	// This disables the LIMIT construct, as we don't want to test it here
	'page'=>null,
	'per_page'=>null,
	'sort'=>array( array("class"=>$struct_join, "col"=>"col_4", "sort"=>"DESC"),
		       array("class"=>$struct_primary, "col"=>"col_2", "sort"=>"ASC")
		       // NOTE: the column(s) you are ordering by must be in the returned data set
		     )
);

$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

$result = $tdb->runSelectQueryJoin($primary, $join, $columns, $ctrl, $check=true);
```

```sql
"SELECT DISTINCT struct_primary.col_2
 FROM struct_primary
 INNER JOIN struct_join AS alias_struct_join
 ON (struct_primary.col_1 = alias_struct_join.col_4)
 WHERE 1 = 1 AND struct_primary.col_1 = 1 AND alias_struct_join.col_6 >= 6
 ORDER BY struct_join.col_4 DESC, ORDER BY struct_primary.col_2 ASC"
```

###Paging

```php
$primary = array( "class"=>$struct_primary,
		  "args"=>array(
				 array( "col"=>"col_1", "op"=>"=", "val"=>1)
		  )
);

$join = array(
		array(
		    "class"=>$struct_join,
		    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
		    "args"=>array(
		                  array( "col"=>"col_6", "op"=>">=", "val"=>6)
		    )
		)
);

$ctrl = array(
	// Not setting the "sort" flag is completely valid. The db will just page through the
	// items in whatever order they are stored in the table.
	'page'=>5,
	'per_page'=>7
);

$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

$result = $tdb->runSelectQueryJoin($primary, $join, $columns, $ctrl, $check=true);
```

```sql
"SELECT DISTINCT struct_primary.col_2
 FROM struct_primary
 INNER JOIN struct_join AS alias_struct_join
 ON (struct_primary.col_1 = alias_struct_join.col_4)
 WHERE 1 = 1 AND struct_primary.col_1 = 1 AND alias_struct_join.col_6 >= 6
 LIMIT 28, 7"
```

###Offset

```php
$primary = array( "class"=>$struct_primary,
		  "args"=>array(
				 array( "col"=>"col_1", "op"=>"=", "val"=>1)
		  )
);

$join = array(
		array(
		    "class"=>$struct_join,
		    "on"=>array("pri"=>"col_1", "op"=>"=", "sec"=>"col_4"),
		    "args"=>array(
		                  array( "col"=>"col_6", "op"=>">=", "val"=>6)
		    )
		)
);

$ctrl = array(
	// Not setting the "sort" flag is completely valid. The db will just page through the
	// items in whatever order they are stored in the table.
	'per_page'=>7,
	'offset'=>3
);

$columns = array("mode"=>"exclude", "col"=>array("col_1", "col_3") );

$result = $tdb->runSelectQueryJoin($primary, $join, $columns, $ctrl, $check=true);
```

```sql
"SELECT DISTINCT struct_primary.col_2
 FROM struct_primary
 INNER JOIN struct_join AS alias_struct_join
 ON (struct_primary.col_1 = alias_struct_join.col_4)
 WHERE 1 = 1 AND struct_primary.col_1 = 1 AND alias_struct_join.col_6 >= 6
 LIMIT 3, 7"
```
