# Key Features of The BPM_db Class

### Strong Typing

PHP and SQL use different data types. In PHP, a "number" can only be three data types:

```php
$number = (bool) 1;
$number = (int) 10000;
$number = (float) 23.95;
```

In MySQL, a "number" can be represented by at least twelve basic data types, with hundreds of permutations:

```php
"tinyint", "unsigned tinyint"
"smallint", "unsigned smallint"
"mediumint", "unsigned mediumint"
"int", "unsigned int",
"bigint", "unsigned bigint",
"float"(1-64 bits), "unsigned float"(1-64 bits)
```

BPM_db automatically typecasts between PHP and MySQL. To ensure the class makes both a correct and expected type conversion, casting 
information is hard-coded into the table declaration.

```php
public static $struct = array(

	"table" => "test_a",
	"engine" => "InnoDB",
	"columns" => array(
	    "col_1" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6),   // ...
	    "col_2" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250), // ...
	    "col_3" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250)  // ...
	 )
);

// $php    | The type format the variable is stored in within PHP
// $sql    | The type format the variable is stored in within SQL
// $format | Print method to use when sending data to the SQL server
// $width  | Field width to use when sending data from the SQL server to PHP
```

###SQL-Injection Protection

SQL servers are not designed to be fed raw data. If strings containing comment characters, operators, and various SQL fragments are passed to a function that assembles SQL queries, and it fails to process them properly, the string will be injected into the query. This changes what the query does, and can be used to damage or gain access to a website's database.

SQL-Injection is a huge topic. You can learn more about it here: PHP.net, Wikipedia

BPM_db automatically escapes strings sent to the database and enforces strong typing on all other data types. If attack code is passed to BPM_db, it will either be stored as a simple string, or it will be cast to numeric "1". This, in theory, currently makes it impossible to launch a SQL-Injection attack against the SQL server.

Typical Attack Scenario:

$sql  = "SELECT id, name, inserted, size FROM products
                  WHERE size = '$size'
                  ORDER BY $order LIMIT $limit, $offset;";

$result = query($sql);

$size = "' UNION SELECT '1', concat(uname||'-'||passwd) as name, '1971-01-01', '0' from usertable; --";

$sql  = "SELECT id, name, inserted, size FROM products WHERE size = ''
	 UNION SELECT '1', concat(uname||'-'||passwd) as name, '1971-01-01', '0' from usertable;
	 -- ORDER BY $order LIMIT $limit, $offset;";

$result = query($sql);

// ...and the server is p3wned
How BPM_db Blocks It:

// Table declaration
// ================================================================================
// "col_1" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", ...
// "col_2" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", ...

$attack_1 = "'; DROP TABLE users; -- ";
$attack_2 = "UNION select '1', concat(uname||'-'||passwd) as name, '1971-01-01', '0' from usertable; # ";

$data = array( 'col_1'=>$attack_1, 'col_2'=>$attack_2 );

$result = $tdb->buildInsertQuery(self::struct, $data);

echo $result;

// "INSERT INTO test_a (col_1, col_2) VALUES ('\; DROP TABLE users; -- ', 1);"

$args = array(
	 array("col"=>"col_1", "op"=>"=", "val"=>$attack_1),
	 array("col"=>"col_2", "op"=>"=", "val"=>$attack_2)
);

$result = $tdb->buildSelectQuery(self::struct, $args, $columns=null, $ctrl=null);

echo $result;

// "SELECT * FROM test_a  WHERE 1 = 1 AND col_1 = '\; DROP TABLE users; --' AND col_2 = 1;"
When we add support for SQL constructs that cannot use "blind" escaping, such as "LIKE %string%", BPM_db will apply additional processing to strings prior to handing them off to the SQL server.

Typical Attack Scenario:

$sql  = "SELECT * FROM products WHERE id LIKE '%$attack%'";
$result = query($sql);

$attack = "a%' exec master..xp_cmdshell 'net user test testpass /ADD'"

$sql  = "SELECT * FROM products
                    WHERE id LIKE '%a%'
                    exec master..xp_cmdshell 'net user test testpass /ADD'--";

$result = query($sql);

// ...and the attacker has p3wned the system (if its running on Windows)
How BPM_db Blocks It:

// When we add support for the "LIKE" operator, sample code goes here.
Query Optimization

INSERT Queries

BPM_db automatically selects the most efficient SQL query format to use for a given task.
If the user passes an object that contains a single db row:
$data = array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_53');
...BPM_db will use a single insert query:

"INSERT INTO test_a (col_1, col_2, col_3) VALUES (17, 's_31', 's_53')";
But if the user passes multiple row objects in the $data array:

$data = array(

	array('col_1'=>17, 'col_2'=>'s_31', 'col_3'=>'s_53'),
	array('col_1'=>94, 'col_2'=>'s_66', 'col_3'=>'s_81'),
	array('col_1'=>21, 'col_2'=>'s_13', 'col_3'=>'s_42')
);
...BPM_db will combine them into a multi-insert query:

"INSERT INTO test_a (col_1, col_2, col_3) VALUES (17, 's_31', 's_53'), (94, 's_66', 's_81'), (21, 's_13', 's_42')";
INDATE Queries

BPM_db also supports INDATE (INsert-upDATE) queries. In most cases, this eliminates the need to search a table for duplicates before inserting new rows.
$db = new BPM_db();
$data = array('col_1'=>'a', 'col_2'=>'b', 'col_3'=>'c');

$result = $db->runIndateQuery($struct, $data, $columns=null, $check=true);
Adds a "duplicate key" construct to the generated query:

"INSERT INTO table_name (col_1, col_2, col_3) VALUES ('a', 'b', 'c')
 ON DUPLICATE KEY UPDATE col_1 = 'a', col_2 = 'b', col_3 = 'c'";
IN Queries

If a user passes a single token to test against:
$args = array( "col"=>"col_1", "op"=>">=", "val"=>1);
$db = new BPM_db();

$result = $db->buildSelectQuery($struct, $args, $columns, $ctrl, $check = true);
...BMP_db will build a single token equality statement.

"SELECT * FROM table_name WHERE col_1 = 1";
But if the user passes multiple tokens to test against:

$args = array( "col"=>"col_1", "op"=>">=", "val"=>array(1, 3, 5, 7) );
$db = new BPM_db();

$result = $db->buildSelectQuery($struct, $args, $columns, $ctrl, $check = true);
...BPM_db will build an IN statement.

"SELECT * FROM table_name WHERE col_1 IN(1, 3, 5, 7) ";
Transactions

In large web applications, developers often need to run queries in groups, and have all of them succeed or the entire group fail.

For example, in a "banking" application, a table might consist of rows that hold the account balances of different users. If "User A" transfers fifty bitcoins to "User B", we'd need to run two queries: one to deduct fifty bitcoins from "User A's" account, and another to add fifty bitcoins to "User B's" account. We'd definitely not want the first query to run, then have the second query fail due to a database or server error.

SQL transactions are designed to protect against this kind of scenario. They guarantee that all of the queries in a given transaction will succeed, or all of them will be rolled-back ...even if power is cut to the server in the middle of the operation.

BPM_db has full support for SQL transactions, including semaphores to prevent accidental transaction nesting.

Typical Usage

if( $db->beginTransaction() ){

	// Insert new group into the db
	$insert_ok = $db->runInsertQuery(self::$struct, $data, $columns=null);
	$new_group_id = $db->insert_id;

	// Remove remove default flag from old group
	$unset_default = array("is_default"=>false);
	$unset_ok = $db->runUpdateQueryCol(self::$struct, $unset_default, "group_id", "=", $old_default);

	// If all queries were successful, commit the transaction
	if($insert_ok && $new_group_id && $unset_ok){
		$result = $db->commitTransaction();
	}
	else {
		$db->rollbackTransaction();
		$result=false;
	}
}
else {
	$result = false;
}