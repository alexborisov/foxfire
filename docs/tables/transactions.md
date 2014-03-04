SQL Transaction Support

Transactions are like an "undo" command for SQL servers. They let users run a group of queries, one after another, but don't actually write the queries to the database until a "COMMIT" command is run. This is useful when rows in multiple tables are dependent on each other and have to be updated as a group, or for when data has to be deleted from multiple tables simultaneously, like when deleting a user.

FOX_db has basic support for SQL transactions when using the InnoDB storage engine. In theory, this means support for multiple users running transactions simultaneously, but we haven't tested it yet.

FOX_db's transaction functions are very easy to use:

Start a transaction by calling FOX_db::beginTransaction()
Run your queries
Commit the transaction using FOX_db::commitTransaction()
Or, rollback the transaction using FOX_db::rollbackTransaction().
Important concepts:

Transactions cannot be nested. If a user has a transaction open, they cannot open a second transaction inside the first transaction. The default SQL server behavior is to automatically commit the first transaction if the user tries to upen a second transaction. Our function will simply return false until the currently open transaction is committed.
Transactions roll-back unless committed. If a user's database connection is lost, a PHP script crashes, or a PHP script finishes execution without running a commit, uncommitted transactions for that script instance will automatically be rolled-back by the SQL server.
Transactions are supposed to be ACID, but it's possible to set up a SQL server and run queries in ways that break ACID compliance. Avoid doing this unless you're exceptionally skilled and can handle the problems it will cause.
FOX_db::beginTransaction()

FOX_db::beginTransaction() starts a transaction on the user's current database handle.
/**
 * Starts a SQL transaction. Note that only InnoDB tables currently support transactions.
 *
 * @return bool | False on failure. True on success.
 */

public function beginTransaction(){

 // ...

}
Returns true if a transaction was successfully started. Returns false if a transaction could not be started, typically caused by the user already having a transaction in progress (as per SQL rules, transactions cannot be nested).

FOX_db::commitTransaction()

FOX_db::commitTransaction() commits a transaction on the user's current database handle.
/**
 * Commits a SQL transaction. Note that only InnoDB tables currently support transactions.
 *
 * @return bool | False on failure. True on success.
 */

public function commitTransaction(){

 // ...

}
Returns true if the trasaction was successfully committed. Returns false if the transaction could not be committed, typically caused by the user not having a transaction in progress.

FOX_db::rollbackTransaction()

FOX_db::rollbackTransaction() rolls-back the transaction currently in progress on the user's current database handle.
/**
 * Rolls-back a SQL transaction. Note that only InnoDB tables currently support transactions.
 *
 * @return bool | False on failure. True on success.
 */

public function rollbackTransaction(){

 // ...

}
Returns true if the transaction was successfully rolled-back. Returns false if the transaction could not be rolled-back, typically caused by the user not having a transaction in progress.

Usage Examples

The examples below are working code that can be run on a live database. Use this code to install the test table.

static $struct_a = array(

	"table" => "bpm_test_transactions_A",
	"engine" => "InnoDB",
	"columns" => array(
	    "id" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
	    "name" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>"UNIQUE"),
	    "text" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null, "auto_inc"=>false, "default"=>null,  "index"=>false),
	    "priv" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>2,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>true),
	    "files" =>	array(	"php"=>"int",	    "sql"=>"mediumint",	"format"=>"%d", "width"=>7,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>false),
	    "space" =>	array(	"php"=>"float",	    "sql"=>"bigint",	"format"=>"%d", "width"=>null,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>false)
	 )
);

$tdb = new FOX_db();
$result = $tdb->runAddTable($struct_a);
Insert -> Commit -> Select

$tdb = new FOX_db();
$table_name = 'bpm_test_transactions_A';

$data_insert_01 = array();

$data_insert_01[] = array(
    "name" => "data_01",
    "text" => "Test Text String 01",
    "priv" => 1,
    "files" => 222,
    "space" => 3333
);

$data_check_01 = new stdClass();
$data_check_01->name = "data_01";
$data_check_01->text = "Test Text String 01";
$data_check_01->priv = "1";
$data_check_01->files = "222";
$data_check_01->space = "3333";

$columns = array("mode"=>"exclude", "col"=>"id");

$tdb->beginTransaction();

    $tdb->runInsertQueryMulti(self::$struct_a, $data_insert_01, $columns, $check=true);

$tdb->commitTransaction();

$result = $tdb->runSelectQueryCol($struct_a, $col = 'name', $op = "=", $val = 'data_01', $columns, $ctrl=array("format"=>"array_object"), $check = true);

$this->assertEquals($data_check_01, $result[0]);

$tdb->runTruncateTable($struct_a);
Insert -> Rollback -> Select

$tdb = new FOX_db();

$table_name = 'bpm_test_transactions_A';

$data_insert_01 = array(

	array("name" => "data_01", "text" => "Test Text String 01", "priv" => 1, "files" => 222, "space" => 3333),
	array("name" => "data_02", "text" => "Test Text String 02", "priv" => 1, "files" => 333, "space" => 4444)
);

$data_insert_02 = array(

	array("name" => "data_03", "text" => "Test Text String 03", "priv" => 1, "files" => 555, "space" => 6666),
	array("name" => "data_04", "text" => "Test Text String 04", "priv" => 1, "files" => 666, "space" => 7777)
);

$data_check = array(

	array("name" => "data_01", "text" => "Test Text String 01", "priv" => 1, "files" => 222, "space" => 3333),
	array("name" => "data_02", "text" => "Test Text String 02", "priv" => 1, "files" => 333, "space" => 4444),
	array("name" => "data_03", "text" => "Test Text String 03", "priv" => 1, "files" => 555, "space" => 6666),
	array("name" => "data_04", "text" => "Test Text String 04", "priv" => 1, "files" => 666, "space" => 7777)
);

$columns = array("mode"=>"exclude", "col"=>"id");


// Start a transaction, add $data_insert_01 to the table, commit transaction
$tdb->beginTransaction();

    $tdb->runInsertQueryMulti($struct_a, $data_insert_01, $columns, $check=true);

$tdb->commitTransaction();

$ctrl=array("format"=>"array_array");
$result = $tdb->runSelectQueryCol($struct_a, $col = 'priv', $op = "=", $val = '1', $columns, $ctrl, $check=true);

// Contents of the table should match contents of $data_insert_01
$this->assertEquals($data_insert_01, $result);


// Start a transaction, add $data_insert_02 to the table

$tdb->beginTransaction();

    $tdb->runInsertQueryMulti($struct_a, $data_insert_02, $columns, $check=true);

    $ctrl=array("format"=>"array_array");
    $result = $tdb->runSelectQueryCol($struct_a, $col = 'priv', $op = "=", $val = '1', $columns, $ctrl, $check=true);

    // Contents of table should match contents of $data_insert_01 plus $data_insert_02 ($data_check)
    $this->assertEquals($data_check, $result);

// Rollback the transaction
$tdb->rollbackTransaction();

$ctrl=array("format"=>"array_array");
$result = $tdb->runSelectQueryCol($struct_a, $col = 'priv', $op = "=", $val = '1', $columns, $ctrl, $check=true);

// Contents of the table should match contents of $data_insert_01 again
$this->assertEquals($data_insert_01, $result);

$tdb->runTruncateTable($struct_a);