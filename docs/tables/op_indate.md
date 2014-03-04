INDATE Queries

INDATE (INsert-upDATE) queries INSERT a new row if the query doesn't find a row with a matching primary key in the table, but UPDATE the existing
row if the query finds a row with a matching primary key in the table.
Indate queries allow some database operations to be completed with half the number of queries used by a typical search-update pattern. Note that there is no actual SQL indate operator, the functionality is achieved by adding a "ON DUPLICATE KEY UPDATE" clause to a standard insert query.

FOX_db::runIndateQuery()

/**
 * Runs an INDATE [INsert-upDATE query on one of the plugin's db tables. If the query attempts to insert a row
 * whose primary key already exists, the existing row will be updated. Indate queries ONLY work on db tables
 * that have a primary key (or a multi-column composite primary key).
 *
 * @param array $struct | Structure of the db table, @see class FOX_db header for examples
 *
 * @param array/object $data | Class with $column_1, $column_2 in the namespace, or array of the form ("column_1"=>"value_1", "column_2", "value_2")
 *
 * @param bool/array $columns | Columns to use in query. NULL to select all columns.
 *	=> VAL @param string $mode | Column operating mode. "include" | "exclude"
 *	=> VAL @param string/array $col | Single column name as string. Multiple column names as array of strings
 *
 * @param bool $check | set false to disable *all* query error checking.
 *
 * @returnint | Int 0 on failure. Int number of rows affected on success.
 */

public function runIndateQuery($struct, $data, $columns=null, $check=true){

 // ...

}
"$struct"

Structure of the table to operate on. See class layout page for more info.
"$data"

Data source for the query. When using an array, it has to follow this structure below.
The name of the key sets the column name to write to, and the value of the key sets the data to write to the column. Keys can be in any order and saved values can be any PHP data type, including arrays and objects. FOX_db will automatically typecast values to the right SQL data type based on the options set in the table definition array. For this query, the primary key MUST be set in the array or object.

$data = array( "col_name_1"=>"val", "col_name_2"=>"val", "col_name_2"=>"val",)
When using an object, the contents of any class variables which match the names of database columns will be saved to the database during the query. Our database classes typically follow the structure shown below.

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
This is the minimum class structure required for the function to work.

class bar {

        var $col_1;            // Column variable 1
        var $col_2;            // Column variable 2
        var $col_3;            // Column variable 3
}
"$columns"

Database columns to use during by the query.
If no array is passed, the function will try to save all columns listed in $struct to the database during the query. If keys for the columns are not present in the $data array, or variables for columns don't exist in the $data object, the function will exclude the missing columns from the SQL query, leaving the values currently stored in the database unchanged.

If an array is passed, it has to have the following structure:

array( $mode=>"include|exclude", $col=>"col_name | array("col_name_1", "col_name_2") );
"$mode"

If set to "include", the function will only use the column or columns specified in $col. If set to "exclude", the function will exclude all columns except the column or columns specified in $col.
"$col"

Single column name passed as string, or multiple column names passed as array of strings. Columns excluded from, or not included in, the query are completely removed from the generated SQL statement. This will cause the values for these columns currently sured in the database to remain unchanged.
"$check"

If not set, or set to true, the function will perform basic error checking on the query before running it.
Usage Examples

The examples below are working code that can be run on a live database. Use this code to install the test table.

$struct = array(

	"table" => "test_a",
	"engine" => "InnoDB",
	"columns" => array(
	    "col_1" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>"PRIMARY"),
	    "col_2" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
	    "col_3" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false)
	 )
);


$tdb = new FOX_db();
$result = $tdb->runAddTable(self::$struct);
Array as data source

$data = array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_53');

$result = $tdb->runIndateQuery($struct, $data, $columns=null, $check=true);
"INSERT INTO test_a (col_1, col_2, col_3) VALUES (17, 's_31', 's_53')
 ON DUPLICATE KEY UPDATE col_1 = 17, col_2 = 's_31', col_3 = 's_53'";
Array as data source, single column using INCLUDE mode

$data = array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_19');

$columns = array("mode"=>"include", "col"=>"col_1");

$result = $tdb->runIndateQuery($struct, $data, $columns, $check=true);
"INSERT INTO test_a (col_1) VALUES (17) ON DUPLICATE KEY UPDATE col_1 = 17";
Array as data source, multiple columns using INCLUDE mode

$data = array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_19');

$columns = array("mode"=>"include", "col"=>array("col_1", "col_3") );

$result = $tdb->runIndateQuery($struct, $data, $columns, $check=true);
"INSERT INTO test_a (col_1, col_3) VALUES (17, 's_19') ON DUPLICATE KEY UPDATE col_1 = 17, col_3 = 's_19'";
Array as data source, single column using EXCLUDE mode

$data = array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_19');

$columns = array("mode"=>"exclude", "col"=>"col_3");

$result = $tdb->runIndateQuery($struct, $data, $columns, $check=true);
"INSERT INTO test_a (col_1, col_2) VALUES (17, 's_31') ON DUPLICATE KEY UPDATE col_1 = 17, col_2 = 's_31'";
Array as data source, multiple columns using EXCLUDE mode

$data = array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_19');

$columns = array("mode"=>"exclude", "col"=>array("col_2", "col_3") );

$result = $tdb->runIndateQuery($struct, $data, $columns, $check=true);
"INSERT INTO test_a (col_1) VALUES (17) ON DUPLICATE KEY UPDATE col_1 = 17";
Object as data source

$data = new stdClass();
$data->col_1 = 17;
$data->col_2 = "s_31";

$result = $tdb->runIndateQuery($struct, $data, $columns=null, $check=true);
"INSERT INTO test_a (col_1, col_2, col_3) VALUES (17, 's_31', NULL)
 ON DUPLICATE KEY UPDATE col_1 = 17, col_2 = 's_31', col_3 = NULL";
Object as data source, single column using EXCLUDE mode

$data = new stdClass();
$data->col_1 = 17;
$data->col_2 = "s_31";
$data->col_3 = "s_19";

$columns = array("mode"=>"exclude", "col"=>array("col_2", "col_3") );

$result = $tdb->runIndateQuery($struct, $data, $columns, $check=true);
"INSERT INTO test_a (col_1) VALUES (17) ON DUPLICATE KEY UPDATE col_1 = 17";