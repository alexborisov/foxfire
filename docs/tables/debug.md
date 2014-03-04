#Debugging Tools

FoxFire has formidable built-in debugging capabilities, including the ability to log SQL queries, raw server results, formatted data, and SQL server errors. Debug variables are located at the top of the FOX_db class file. 

#####Output is saved to '/core/utils/log.txt'

```php
class FOX_db {

	// ...

	var $last_error = '';			    // The last error returned by SQL server
	var $last_query;			    // Full SQL string of the last query made
	var $last_result;			    // Results of the last query made

	// ...

	var $print_query_args = false;		    // Log args passed to the query generators
	var $print_query_sql = false;		    // Log SQL strings produced by the query generators
	var $print_result_raw = false;		    // Log raw data returned from SQL server
	var $print_result_cast = false;		    // Log returned data from the type caster
	var $print_result_formatted = false;	    // Log returned data from results formatter

						    // Output will be saved to '/core/utils/log.txt'
	// ...
```
####$print_query_args

When set true, $print_query_args will write arguements passed to the query generators to the debug file.

####Sample data

```php
2011-07-10 21:04:19 : Array
(
    [query] => Array
        (
            [0] => INSERT INTO wp_bpm_test_transactions_A (name, text, priv, files, space) VALUES (%s, %s, %d, %d, %d)
            [1] => data_01
            [2] => Test Text String 01
            [3] => 1
            [4] => 222
            [5] => 3333
        )

)

2011-07-10 21:04:19 : Array
(
    [query] => Array
        (
            [0] => SELECT name, text, priv, files, space FROM wp_bpm_test_transactions_A WHERE 1 = 1 AND name = %s
            [1] => data_01
        )

    [types] => Array
        (
            [name] => Array
                (
                    [php] => string
                    [sql] => varchar
                )

            [text] => Array
                (
                    [php] => string
                    [sql] => varchar
                )

            [priv] => Array
                (
                    [php] => int
                    [sql] => tinyint
                )

            [files] => Array
                (
                    [php] => int
                    [sql] => mediumint
                )

            [space] => Array
                (
                    [php] => float
                    [sql] => bigint
                )

        )

)
Array
(
    [format] => array_object
)
```

####$print_query_sql

When set true, $print_query_sql will write SQL strings produced by query generators to the debug file.

####Sample data

```php
2014-07-10 21:09:20 : CREATE TABLE wp_bpm_test_transactions_A ( id smallint(6) NOT NULL AUTO_INCREMENT, name varchar(250) NOT NULL, text varchar(250), priv tinyint(2) NOT NULL DEFAULT '0', files mediumint(7) NOT NULL DEFAULT '0', space bigint NOT NULL DEFAULT '0', PRIMARY KEY (id), UNIQUE KEY name (name), KEY priv (priv) ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8;
2014-07-10 21:09:20 : CREATE TABLE wp_bpm_test_transactions_B ( id smallint(6) NOT NULL AUTO_INCREMENT, name varchar(250) NOT NULL, text varchar(250), priv tinyint(2) NOT NULL DEFAULT '0', files mediumint(7) NOT NULL DEFAULT '0', space bigint NOT NULL DEFAULT '0', PRIMARY KEY (id), UNIQUE KEY name (name), KEY priv (priv) ) ENGINE=MyISAM DEFAULT CHARACTER SET utf8;
2014-07-10 21:09:20 : START TRANSACTION
2014-07-10 21:09:20 : INSERT INTO wp_bpm_test_transactions_A (name, text, priv, files, space) VALUES ('data_01', 'Test Text String 01', 1, 222, 3333)
2014-07-10 21:09:20 : COMMIT
2014-07-10 21:09:20 : SELECT name, text, priv, files, space FROM wp_bpm_test_transactions_A WHERE 1 = 1 AND name = 'data_01'
2014-07-10 21:09:20 : TRUNCATE TABLE wp_bpm_test_transactions_A
2014-07-10 21:09:20 : DROP TABLE wp_bpm_test_transactions_A
2014-07-10 21:09:20 : DROP TABLE wp_bpm_test_transactions_B
2014-07-10 21:09:20 : CREATE TABLE wp_bpm_test_transactions_A ( id smallint(6) NOT NULL AUTO_INCREMENT, name varchar(250) NOT NULL, text varchar(250), priv tinyint(2) NOT NULL DEFAULT '0', files mediumint(7) NOT NULL DEFAULT '0', space bigint NOT NULL DEFAULT '0', PRIMARY KEY (id), UNIQUE KEY name (name), KEY priv (priv) ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8;
2014-07-10 21:09:20 : CREATE TABLE wp_bpm_test_transactions_B ( id smallint(6) NOT NULL AUTO_INCREMENT, name varchar(250) NOT NULL, text varchar(250), priv tinyint(2) NOT NULL DEFAULT '0', files mediumint(7) NOT NULL DEFAULT '0', space bigint NOT NULL DEFAULT '0', PRIMARY KEY (id), UNIQUE KEY name (name), KEY priv (priv) ) ENGINE=MyISAM DEFAULT CHARACTER SET utf8;
2014-07-10 21:09:20 : START TRANSACTION
2014-07-10 21:09:20 : INSERT INTO wp_bpm_test_transactions_A (name, text, priv, files, space) VALUES ('data_01', 'Test Text String 01', 1, 222, 3333)
2014-07-10 21:09:20 : SELECT name, text, priv, files, space FROM wp_bpm_test_transactions_A WHERE 1 = 1 AND name = 'data_01'
2014-07-10 21:09:20 : COMMIT
2014-07-10 21:09:20 : TRUNCATE TABLE wp_bpm_test_transactions_A
2014-07-10 21:09:20 : DROP TABLE wp_bpm_test_transactions_A
2014-07-10 21:09:20 : DROP TABLE wp_bpm_test_transactions_B
2014-07-10 21:09:20 : CREATE TABLE wp_bpm_test_transactions_A ( id smallint(6) NOT NULL AUTO_INCREMENT, name varchar(250) NOT NULL, text varchar(250), priv tinyint(2) NOT NULL DEFAULT '0', files mediumint(7) NOT NULL DEFAULT '0', space bigint NOT NULL DEFAULT '0', PRIMARY KEY (id), UNIQUE KEY name (name), KEY priv (priv) ) ENGINE=InnoDB DEFAULT CHARACTER SET utf8;
2014-07-10 21:09:20 : CREATE TABLE wp_bpm_test_transactions_B ( id smallint(6) NOT NULL AUTO_INCREMENT, name varchar(250) NOT NULL, text varchar(250), priv tinyint(2) NOT NULL DEFAULT '0', files mediumint(7) NOT NULL DEFAULT '0', space bigint NOT NULL DEFAULT '0', PRIMARY KEY (id), UNIQUE KEY name (name), KEY priv (priv) ) ENGINE=MyISAM DEFAULT CHARACTER SET utf8;
2014-07-10 21:09:20 : START TRANSACTION
2014-07-10 21:09:20 : INSERT INTO wp_bpm_test_transactions_A (name, text, priv, files, space) VALUES ('data_01', 'Test Text String 01', 1, 222, 3333), ('data_02', 'Test Text String 02', 1, 333, 4444)
2014-07-10 21:09:20 : COMMIT
```

####$print_result_raw

When set true, $print_result_raw will write the raw results returned by the SQL server to the debug file.

####Sample data

```php
2014-07-10 21:11:27 : format = null
1
2014-07-10 21:11:27 : RAW, format = array_object

2014-07-10 21:11:27 : format = null
1
2014-07-10 21:11:27 : RAW, format = array_object

2014-07-10 21:11:27 : format = null
2
2014-07-10 21:11:27 : RAW, format = array_array
Array
(
    [0] => stdClass Object
        (
            [name] => data_01
            [text] => Test Text String 01
            [priv] => 1
            [files] => 222
            [space] => 3333
        )

    [1] => stdClass Object
        (
            [name] => data_02
            [text] => Test Text String 02
            [priv] => 1
            [files] => 333
            [space] => 4444
        )

)
```

####$print_result_cast

When set true, $print_result_cast will write the processed SQL results returned by the typecasters to the debug file.

####Sample data

```php
2014-07-10 21:13:58 :
CAST, format = array_object

2014-07-10 21:13:58 :
CAST, format = array_object

2014-07-10 21:13:58 :
CAST, format = array_array
Array
(
    [0] => stdClass Object
        (
            [name] => data_01
            [text] => Test Text String 01
            [priv] => 1
            [files] => 222
            [space] => 3333
        )

    [1] => stdClass Object
        (
            [name] => data_02
            [text] => Test Text String 02
            [priv] => 1
            [files] => 333
            [space] => 4444
        )

)
```

####$print_result_formatted

When set true, $print_result_formatted will write the processed results returned by the result formatters to the debug file.

```php
2014-07-10 21:15:25 :
FORMATTED, format = array_array
Array
(
    [0] => Array
        (
            [name] => data_01
            [text] => Test Text String 01
            [priv] => 1
            [files] => 222
            [space] => 3333
        )

    [1] => Array
        (
            [name] => data_02
            [text] => Test Text String 02
            [priv] => 1
            [files] => 333
            [space] => 4444
        )

)
```
