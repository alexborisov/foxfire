#Result Formatters

FOX_db can automatically format results returned by the SQL server (typically an array of objects with no heirarchy) into easy to work with structured objects and arrays. This functionality is accessed by setting the $format, $key_col, and $val_col parameters in the select function's $ctrl array.

```php
 /* @param array $ctrl | Control parameters for the query
 *      //...
 *      => VAL @param string $format | @see FOX_db::runQuery() for detailed info on format string
 *      => VAL @param string $key_col | Column name to get key names from when using formatter functions
 *      => VAL @param string $val_col | Column name to use as value when using formatter functions
 */
```
 
Unless otherwise noted, examples below are based on the following test table:

```php
col_1	col_2	col_3	col_4
1	red	dog	big
2	green	cat	med
3	blue	bird	small
4	black	fish	tiny
```

###(null)

Returns raw result as returned by SQL server.

```php
// EXAMPLE: "SELECT * FROM test_table"
```

###var

Used when fetching query results that return a single variable.

```php
// EXAMPLE: "SELECT COUNT(*) FROM test_table WHERE col_2 = red"
//
// RESULT: string "1" (all data is returned in string format)
```

###col

Used when fetching query results which only contain values from a single column.

```php
// EXAMPLE: "SELECT col2 FROM test_table"
//
// RESULT: array("red", "green", "blue", "black")
```

###row_object

Returns a single database row as an object, with variable names mapped to column names. If the query returns multiple rows, only the first row is returned.

```php
// EXAMPLE: "SELECT * FROM test_table WHERE col_1 = 2"
//
// RESULT: object stdClass(
//		    col_1->2
//		    col_2->"green"
//		    col_3->"cat"
//		    col_4->"med"
//	    )
```

###row_array

Returns a single database row as an array, with key names mapped to column names. If the query returns multiple rows, only the first row is returned.

```php
// EXAMPLE: "SELECT * FROM test_table WHERE col_1 = 2"
//
// RESULT: array(
//		    "col_1"=>2
//		    "col_2"=>"green"
//		    "col_3"=>"cat"
//		    "col_4"=>"med"
//	       )
```

###array_key_single

Returns results as an array of ints or strings, where the array key names are set based on the contents of a database column.

```php
// EXAMPLE: "SELECT col_2, col_3 FROM test_table" + $ctrl["key_col"]="col_2", $ctrl["val_col"]="col_3"
//
// RESULT:   array(
//		     "red"=>"dog",
//		     "green"=>"cat",
//		     "blue"=>"bird",
//		     "black"=>"fish"
//	     )
```

###array_object

Returns results as an array of objects, where the primary array keys are zero-indexed ints

```php
// EXAMPLE: "SELECT * FROM test_table"
//
// RESULT:   array(
//		    stdClass( "col_1"->1, "col_2"->"red", "col_3"->"dog", "col_4"->"big"),
//		    stdClass( "col_1"->2, "col_2"->"green", "col_3"->"cat", "col_4"->"med"),
//		    stdClass( "col_1"->3, "col_2"->"blue", "col_3"->"bird", "col_4"->"small"),
//		    stdClass( "col_1"->4, "col_2"->"black", "col_3"->"fish", "col_4"->"tiny"),
//	     )
```

###array_array

Returns results as an array of arrays, where the primary array keys are zero-indexed ints

```php
// EXAMPLE: "SELECT * FROM test_table"

// RESULT:   array(
//		    array( "col_1"=>1, "col_2"=>"red", "col_3"=>"dog", "col_4"=>"big"),
//		    array( "col_1"=>2, "col_2"=>"green", "col_3"=>"cat", "col_4"=>"med"),
//		    array( "col_1"=>3, "col_2"=>"blue", "col_3"=>"bird", "col_4"=>"small"),
//		    array( "col_1"=>4, "col_2"=>"black", "col_3"=>"fish", "col_4"=>"tiny"),
//	     )
```

###array_key_object

When used with a single column name passed as a string:

Returns results as an array of objects, where the primary array key names are set based on the contents of a database column.

```php
// EXAMPLE: "SELECT * FROM test_table" + $ctrl["key_col"]="col_3"
// RESULT:   array(
//		    "dog" =>stdClass( "col_1"->1, "col_2"->"red", "col_3"->"dog", "col_4"->"big"),
//		    "cat" =>stdClass( "col_1"->2, "col_2"->"green", "col_3"->"cat", "col_4"->"med"),
//		    "bird"=>stdClass( "col_1"->3, "col_2"->"blue", "col_3"->"bird", "col_4"->"small"),
//		    "fish"=>stdClass( "col_1"->4, "col_2"->"black", "col_3"->"fish", "col_4"->"tiny"),
//	     )
```

When used with multiple column names passed as an array of strings:

Returns results as an array of arrays^N, where the array key names and heirarchy are set based on the contents of the $key_col array. This output format REQUIRES that each taxonomy group (in the example below "col_3" + "col_4") is UNIQUE

```php
// EXAMPLE TABLE "test_table_2"
// ======================================
//  col_1 | col_2 | col_3 | col_4 | col_5
// ======================================
//    1   |  red  |  dog  |  big  | heavy
//    2   |  green|  dog  |  med  | light
//    3   |  blue |  bird | small | light
//    4   |  black|  fish |  tiny | average
//    5   |  black|  fish | large | light
//
// UNIQUE KEY col_3_col4(col_3, col_4)
//
// EXAMPLE: "SELECT * FROM test_table" + $ctrl["key_col"] = array("col_3, "col_4")
//
// RESULT:   array(
//		    "dog" =>array(
//				    "big"=>stdClass("col_1"->1, "col_2"->"red", "col_5"->"heavy"),
//				    "med"=>stdClass("col_1"->2, "col_2"->"green", "col_5"->"light")
//		    ),
//		    "bird"=>array(
//				    "small"=>stdClass("col_1"->3, "col_2"->"blue", "col_5"->"light")
//		    ),
//		    "fish"=>array(
//				    "tiny"=>stdClass("col_1"->4, "col_2"->"black", "col_5"->"average"),
//				    "large"=>stdClass("col_1"->5, "col_2"->"black", "col_5"->"light")
//		    )
//	     )
```

###array_key_array

When used with a single column name passed as a string:

Returns results as an array of arrays, where the primary array key names are set based on the contents of a database column.

```php
// EXAMPLE: "SELECT * FROM test_table" + $ctrl["key_col"]="col_3"
//
// RESULT:   array(
//		    "dog" =>array( "col_1"=>1, "col_2"=>"red", "col_3"=>"dog", "col_4"=>"big"),
//		    "cat" =>array( "col_1"=>2, "col_2"=>"green", "col_3"=>"cat", "col_4"=>"med"),
//		    "bird"=>array( "col_1"=>3, "col_2"=>"blue", "col_3"=>"bird", "col_4"=>"small"),
//		    "fish"=>array( "col_1"=>4, "col_2"=>"black", "col_3"=>"fish", "col_4"=>"tiny"),
//	     )
```

When used with multiple column names passed as an array of strings:

Returns results as an array of arrays^N, where the array key names and heirarchy are set based on the contents of the $key_col array. This output format REQUIRES that each taxonomy group (in the example below "col_3" + "col_4") is UNIQUE

```php
// EXAMPLE TABLE "test_table_2"
// ======================================
//  col_1 | col_2 | col_3 | col_4 | col_5
// ======================================
//    1   |  red  |  dog  |  big  | heavy
//    2   |  green|  dog  |  med  | light
//    3   |  blue |  bird | small | light
//    4   |  black|  fish |  tiny | average
//    5   |  black|  fish | large | light
//
// UNIQUE KEY col_3_col4(col_3, col_4)
//
// EXAMPLE: "SELECT * FROM test_table" + $ctrl["key_col"] = array("col_3, "col_4")
//
// RESULT:   array(
//		    "dog" =>array(
//				    "big"=>array("col_1"=>1, "col_2"=>"red", "col_5"=>"heavy"),
//				    "med"=>array("col_1"=>2, "col_2"=>"green", "col_5"=>"light")
//		    ),
//		    "bird"=>array(
//				    "small"=>array("col_1"=>3, "col_2"=>"blue", "col_5"=>"light")
//		    ),
//		    "fish"=>array(
//				    "tiny"=>array("col_1"=>4, "col_2"=>"black", "col_5"=>"average"),
//				    "large"=>array("col_1"=>5, "col_2"=>"black", "col_5"=>"light")
//		    )
//	     )
```

###array_key_array_grouped

Requires at least TWO column names, and columns not specified in $key_col are not included in the results set.

Returns results as an array of arrays^N-1, where the array key names and heirarchy are set based on the contents of the $key_col array. Results in the last db column specified in $key_col are grouped together in an int-keyed array. The key name corresponds to the order in which a rows is returned from the database and the value of each key is the column's value in the database row.

For a working example of how to use this result formatter, see the function database_resultFormatters::test_array_key_array_grouped() in the database unit tests.

```php

// EXAMPLE TABLE "test_table_2"
// ===============================
//  col_1 | col_2 | col_3 | col_4
// ===============================
//    1   |  red  |  dog  | A
//    2   |  green|  dog  | B
//    3   |  green|  dog  | C
//    4   |  green|  dog  | D
//    5   |  blue |  bird | A
//    6   |  black|  bird | A
//    7   |  black|  fish | A
//    8   |  black|  fish | B
//
// UNIQUE KEY no_duplicates(col_2, col_3, col_4)
//
// EXAMPLE: "SELECT * FROM test_table" + $ctrl["key_col"] = array("col_2, "col_3", "col_4")
//
// NOTE: "col_1" is excluded because it is not specified in $ctrl["key_col"]
//
// RESULT:   array(
//		    "dog" =>array(
//				    "red"=>	array("A"),
//				    "green"=>	array("B","C","D")
//		    ),
//		    "bird" =>array(
//				    "blue"=>	array("A"),
//				    "black"=>	array("A")
//		    ),
//		    "fish" =>array(
//				    "black"=>	array("A","B")
//		    )
//	     )
```

###array_key_array_true

Returns results as an array of arrays^N-1, where the array key names and heirarchy are set based on the contents of the $key_col array. Results
in the last db column specified in $key_col are grouped together in an result-keyed array where the key name is the column's value in the database
row and the key's value is (bool)true.
For a working example of how to use this result formatter, see the function database_resultFormatters::test_array_key_array_true() in the database unit tests.

```php

// EXAMPLE TABLE "test_table_2"
// ===============================
//  col_1 | col_2 | col_3 | col_4
// ===============================
//    1   |  red  |  dog  | A
//    2   |  green|  dog  | B
//    3   |  green|  dog  | C
//    4   |  green|  dog  | D
//    5   |  blue |  bird | A
//    6   |  black|  bird | A
//    7   |  black|  fish | A
//    8   |  black|  fish | B
//
// UNIQUE KEY no_duplicates(col_2, col_3, col_4)
//
// EXAMPLE: "SELECT * FROM test_table" + $ctrl["key_col"] = array("col_2, "col_3", "col_4")
//
// NOTE: "col_1" is excluded because it is not specified in $ctrl["key_col"]
//
// RESULT:   array(
//		    "dog" =>array(
//				    "red"=>	array("A"=>"true"),
//				    "green"=>	array("B"=>"true","C"=>"true","D"=>"true")
//		    ),
//		    "bird" =>array(
//				    "blue"=>	array("A"=>"true"),
//				    "black"=>	array("A"=>"true")
//		    ),
//		    "fish" =>array(
//				    "black"=>	array("A"=>"true","B"=>"true")
//		    )
//	     )
```

###array_key_array_false

Returns results as an array of arrays^N-1, where the array key names and heirarchy are set based on the contents of the $key_col array. 

Results in the last db column specified in $key_col are grouped together in an result-keyed array where the key name is the column's value in the database row and the key's value is (bool)false.

For a working example of how to use this result formatter, see the function database_resultFormatters::test_array_key_array_false() in the database unit tests.

```php
// EXAMPLE TABLE "test_table_2"
// ===============================
//  col_1 | col_2 | col_3 | col_4
// ===============================
//    1   |  red  |  dog  | A
//    2   |  green|  dog  | B
//    3   |  green|  dog  | C
//    4   |  green|  dog  | D
//    5   |  blue |  bird | A
//    6   |  black|  bird | A
//    7   |  black|  fish | A
//    8   |  black|  fish | B
//
// UNIQUE KEY no_duplicates(col_2, col_3, col_4)
//
// EXAMPLE: "SELECT * FROM test_table" + $ctrl["key_col"] = array("col_2, "col_3", "col_4")
//
// NOTE: "col_1" is excluded because it is not specified in $ctrl["key_col"]
//
// RESULT:   array(
//		    "dog" =>array(
//				    "red"=>	array("A"=>"false"),
//				    "green"=>	array("B"=>"false","C"=>"false","D"=>"false")
//		    ),
//		    "bird" =>array(
//				    "blue"=>	array("A"=>"false"),
//				    "black"=>	array("A"=>"false")
//		    ),
//		    "fish" =>array(
//				    "black"=>	array("A"=>"false","B"=>"false")
//		    )
//	     )
```
