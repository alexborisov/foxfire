Database Tables

It's important to put some thought into designing tables, since a properly designed table which uses the SQL server to enforce ranges, data types, and unique combinations of indices can eliminate huge numbers of unnecessary queries.

// Problem: only allow unique country-state-city combinations.

// Bad Algorithm:
// ===========================
// 1) Query country + city + state
// 2) If not present, insert row
// 3) If present, report error

// Good algorithm:
// ===========================
// ...Create a table with a UNIQUE multi_column index on (country + state + city)
// 1) Insert the row
// 2) If the query fails, report error
Table Declaration

Database tables in FOX_db are created using a table declaration array.

public static $struct = array(

	"table" => "bpm_sys_album_type_policy",
	"engine" => "InnoDB",
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
"Table"

This variable defines the name of the database table. It must follow all SQL table naming rules.
"Engine"

This variable specifies the database storage engine to use for the table. You can use any MySQL database engine that you have installed on your server
(MyISAM, InnoDB, IBMDB2I, MERGE, MEMORY, FEDERATED, ARCHIVE, CSV, BLACKHOLE, etc), but transactions are only supported on the
InnoDB engine.
"columns" => array(
	"type_id"=>array("php"=>"int","sql"=>"tinyint","format"=>"%d", "width"=>null,"flags"=>"UNSIGNED NOT NULL",
	"auto_inc"=>false, "default"=>null,"index"=>true)
)
"Columns"

An array of columns to create in the table in the form column_name=>array(parameters). Be careful when choosing column names. If you pick an invalid
column name ("option", "range", or "separator" for example), then MySQL will refuse to create the table. See the MySQLdocumentation.
"columns => php"

The PHP data type to convert results to when fetching data from the SQL server. Valid types are "bool", "int", "float", "string", "serialize", "array", "object". If "int" is specified and the SQL data type is "date" or "datetime", the value will automatically be converted to a linux timestamp using mktime().
"columns => sql"

The SQL data type to convert results to when sending data to the SQL server. Valid types are "tinyint", "smallint", "mediumint", "int", "bigint", "float", "double", "char", "varchar", "text", "mediumtext", "longtext", "date", "datetime". Unsigned versions of numeric types are created by adding the keyword "UNSIGNED" to the flags field. Note that many SQL data types have counter-intuitive rules and behaviors ...see the MySQL documentation.
"columns => format"

The sprintf() format to use when "printing" a PHP data type into an SQL statement. Only %d and %s can be used.
"columns => width"

Width sent to the SQL server when creating database columns. The width argument has different meanings depending on the SQL data type. With "int", the data will be stored in 32-bit format but the results from the SQL server will be truncated to fit. With "float", width affects the maximum number of digits returned including the number of decimal places, and hence the precision of results. With data types such as "varchar", it sets the actual width data is stored at in the SQL server. See the MySQL documentation.
"columns => flags"

Flags to add to the column's data type declaration when creating a table. For example, if a column's "format" is "int", it's "width" is "16", and "flags" is set to be "UNSIGNED NOT NULL", the full data type declaration will be "int(16) UNSIGNED NOT NULL". You can use any valid SQL flag, but the most common are "UNSIGNED" and "NOT NULL".
"columns => auto_inc"

True to make the column auto-increment in SQL, false to not make it auto-increment. Some database engines only allow one auto-increment column in tables, and auto-increment columns can only be used with specific data types. See the MySQL documentation.
"columns => default"

Default value to use for a column if a value is not supplied when creating a new row. Also affects the operation of auto-increment columns.
// Single index
"index"=>"PRIMARY"

// Multi index
"index"=>array("name"=>"module_id_type_id_branch_id_node", "col"=>array("module_id", "type_id", "branch_id", "node"),
	       "index"=>"PRIMARY", "this_row"=>true )
"Columns => Index"

Bool "true" to add a standard index to the column. Bool "false" to add no index to the column. String "PRIMARY" to index column as the table's primary 
key. String "UNIQUE" to force each row to have a unique value for the column. Note: a table can only have one PRIMARY and one UNIQUE column. String 
"FULLTEXT" to add a full-text index to the column. Note: FULLTEXT indices are only allowed on MyISAM tables.
Multi-column (composite) indices are created by passing an array with the following keys:

"index => index_name"

Unique name for this index. Cannot be a reserved word or the name of any of the db columns.
"index => col"

Array of column names to include in the index.
"index => index"

The type of index to use for the composite index. Follows the same rules as the main "index" field, but you cannot specify an array (because MySQL doesn't allow nested indices).
"index => this_row"

The index type to use for this db row. Follows the same rules as the main "index" field, but you cannot specify an array.
Usage Examples

Standard Index on Single Column

$struct = array(

	"table" => "bpm_test_index_default",
	"engine" => "InnoDB",
	"columns" => array(
	    "priv" =>	array(	"php"=>"int", "sql"=>"tinyint", "format"=>"%d", "width"=>2, "flags"=>null,
	    "auto_inc"=>false, "default"=>null,   "index"=>true),
	    "files" =>	array(	"php"=>"int", "sql"=>"mediumint", "format"=>"%d", "width"=>7, "flags"=>null,
	    "auto_inc"=>false, "default"=>null,   "index"=>false)
	 )
);
 "CREATE TABLE bpm_test_index_default ( priv tinyint(2), files mediumint(7), KEY priv (priv) )
  ENGINE=InnoDB {$charset_collate};";
PRIMARY Index on Single Column

$struct = array(

	"table" => "bpm_test_index_primary",
	"engine" => "InnoDB",
	"columns" => array(
	    "id" =>	array(	"php"=>"int", "sql"=>"smallint", "format"=>"%d", "width"=>6, "flags"=>null,
	    "auto_inc"=>false,  "default"=>null,  "index"=>"PRIMARY")
	 )
);
"CREATE TABLE bpm_test_index_primary ( id smallint(6), PRIMARY KEY (id) ) ENGINE=InnoDB {$charset_collate};";
UNIQUE Index on Single Column

$struct = array(

	"table" => "bpm_test_index_unique",
	"engine" => "InnoDB",
	"columns" => array(
	    "name" =>	array(	"php"=>"string", "sql"=>"varchar", "format"=>"%s", "width"=>250, "flags"=>null,
	    "auto_inc"=>false, "default"=>null,  "index"=>"UNIQUE")
	 )
);
"CREATE TABLE bpm_test_index_unique ( id smallint(6), UNIQUE KEY (id) ) ENGINE=InnoDB {$charset_collate};";
FULLTEXT Index on Single Column

$struct = array(

	"table" => "bpm_test_index_full",
	"engine" => "MyISAM",
	"columns" => array(
	    "id" =>	array(	"php"=>"int", "sql"=>"smallint", "format"=>"%d", "width"=>6, "flags"=>null,
	    "auto_inc"=>false,  "default"=>null,  "index"=>"FULLTEXT")
	 )
);
"CREATE TABLE bpm_test_index_full ( id smallint(6), FULLTEXT KEY (id) ) ENGINE=MyISAM {$charset_collate};";
AUTO INCREMENT on Single Column

$struct = array(

	"table" => "bpm_test_index_auto",
	"engine" => "InnoDB",
	"columns" => array(
	    "priv" =>	array(	"php"=>"int", "sql"=>"tinyint", "format"=>"%d", "width"=>2, "flags"=>null,
	    "auto_inc"=>true, "default"=>null,   "index"=>true)
	 )
);
"CREATE TABLE bpm_test_index_auto ( priv tinyint(2) AUTO_INCREMENT, KEY priv (priv) ) 
 ENGINE=InnoDB {$charset_collate};";
DEFAULT Value on Single Column

$struct = array(

	"table" => "bpm_test_default",
	"engine" => "InnoDB",
	"columns" => array(
	    "priv" =>	array(	"php"=>"int", "sql"=>"tinyint", "format"=>"%d", "width"=>2, "flags"=>null,
	    "auto_inc"=>false, "default"=>"test_val", "index"=>true)
	 )
);
"CREATE TABLE bpm_test_default ( priv tinyint(2) DEFAULT 'test_val', KEY priv (priv) )
 ENGINE=InnoDB {$charset_collate};";
FLAGS with a Single Column

$struct = array(

	"table" => "bpm_test_flags",
	"engine" => "InnoDB",
	"columns" => array(
	    "priv" =>	array(	"php"=>"int", "sql"=>"tinyint", "format"=>"%d", "width"=>2, "flags"=>"UNSIGNED NOT NULL",
	    "auto_inc"=>false, "default"=>null, "index"=>false)
	 )
);
"CREATE TABLE bpm_test_flags ( priv tinyint(2) UNSIGNED NOT NULL ) ENGINE=InnoDB {$charset_collate};";
COMPOSITE KEY with Multiple Columns

$struct = array(

	"table" => "bpm_test_composite",
	"engine" => "InnoDB",
	"columns" => array(
	    "priv" =>	array(	"php"=>"int", "sql"=>"tinyint", "format"=>"%d", "width"=>2, "flags"=>"NOT NULL",
				"auto_inc"=>false, "default"=>null,
				"index"=>array("name"=>"composite_1", "col"=>array("col_1", "col_2"), "index"=>"UNIQUE"))
	 )
);
"CREATE TABLE bpm_test_composite ( priv tinyint(2) NOT NULL, UNIQUE KEY composite_1 (col_1, col_2) )
 ENGINE=InnoDB {$charset_collate};";