<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - DATABASE - SELECT LEFT-JOIN
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package BP-Media
 * @subpackage Unit Test
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

class database_runSelectQueryLeftJoin extends RAZ_testCase {

    
	static $struct_primary = array(

		"table" => "bpm_test_runSelectQueryLeftJoin_primary",
		"engine" => "InnoDB",
		"columns" => array(
		    "id" =>	array(	"php"=>"int",	    "sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
		    "name" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>"UNIQUE"),
		    "text" =>	array(	"php"=>"string",    "sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null,	     "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "priv" =>	array(	"php"=>"int",	    "sql"=>"tinyint",	"format"=>"%d", "width"=>2,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,	"index"=>true),
		    "files" =>	array(	"php"=>"int",	    "sql"=>"mediumint",	"format"=>"%d", "width"=>7,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>false),
		    "space" =>	array(	"php"=>"float",	    "sql"=>"bigint",	"format"=>"%d", "width"=>null,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>false)
		 )
	);


	static $struct_join = array(

		"table" => "bpm_test_runSelectQueryLeftJoin_secondary",
		"engine" => "InnoDB",
		"columns" => array(
		    "id" =>	    array(  "php"=>"int",	"sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
		    "tree" =>	    array(  "php"=>"int",	"sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "quantity" =>   array(  "php"=>"int",	"sql"=>"smallint",	"format"=>"%d", "width"=>6,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "size" =>	    array(  "php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "color" =>	    array(  "php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>250,	"flags"=>null,	     "auto_inc"=>false, "default"=>null,  "index"=>false),
		    "weight" =>	    array(  "php"=>"float",	"sql"=>"bigint",	"format"=>"%d", "width"=>10,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,	  "index"=>true),
		 )
	);


    	function setUp() {

		parent::setUp();

		$this->tdb = new BPM_db();

		try {
			$this->tdb->runAddTable(self::$struct_primary);
		}
		catch (BPM_exception $fail) {
		    
		    
			// CASE 1: the table already exists in the db (likely from a previous failed test 
			// run), so we need to make sure its clear.
			// ===============================================================================
			if($fail->data['numeric'] == 2){
			    
				try {
					$this->tdb->runTruncateTable(self::$struct_primary);
				}
				catch (BPM_exception $child) {

					$this->fail("Primary table already existed. Failure while clearing table. Error code: " . $child->data['numeric']);
				}
			    
			}
			
			// CASE 2: something is seriously wrong with the database. Abort.
			// ===============================================================================			
			else {
				$this->fail("Failure while adding primary table to database. Error code: " . $fail->data['numeric']);				
			}
			
		}
		
		try {
			$this->tdb->runAddTable(self::$struct_join);
		}
		catch (BPM_exception $fail) {
		    
		    
			// CASE 1: the table already exists in the db (likely from a previous failed test 
			// run), so we need to make sure its clear.
			// ===============================================================================
			if($fail->data['numeric'] == 2){
			    
				try {
					$this->tdb->runTruncateTable(self::$struct_join);
				}
				catch (BPM_exception $child) {

					$this->fail("Join table already existed. Failure while clearing table. Error code: " . $child->data['numeric']);
				}
			    
			}
			
			// CASE 2: something is seriously wrong with the database. Abort.
			// ===============================================================================			
			else {
				$this->fail("Failure while adding join table to database. Error code: " . $fail->data['numeric']);				
			}
			
		}


		// Load primary table
		// ===========================================

		$pri_01 = new stdClass();
		$pri_01->name = "data_01";
		$pri_01->text = "Test Text String 01";
		$pri_01->priv = "1";
		$pri_01->files = "222";
		$pri_01->space = "3333";

		$pri_02 = new stdClass();
		$pri_02->name = "data_02";
		$pri_02->text = "Test Text String 02";
		$pri_02->priv = "1";
		$pri_02->files = "222";
		$pri_02->space = "4444";

		$pri_03 = new stdClass();
		$pri_03->name = "data_03";
		$pri_03->text = "Test Text String 03";
		$pri_03->priv = "1";
		$pri_03->files = "555";
		$pri_03->space = "6666";

		$pri_04 = new stdClass();
		$pri_04->name = "data_04";
		$pri_04->text = "Test Text String 04";
		$pri_04->priv = "3";
		$pri_04->files = "555";
		$pri_04->space = "6666";

		$pri_05 = new stdClass();
		$pri_05->name = "data_05";
		$pri_05->text = "Test Text String 05";
		$pri_05->priv = "4";
		$pri_05->files = "666";
		$pri_05->space = "7777";
		
		$check_array = array($pri_01, $pri_02, $pri_03, $pri_04, $pri_05);

		$columns = array("mode"=>"exclude", "col"=>"id");
		
		try {
			$this->tdb->runInsertQueryMulti(self::$struct_primary, $pri_01, $columns);
			$this->tdb->runInsertQueryMulti(self::$struct_primary, $pri_02, $columns);
			$this->tdb->runInsertQueryMulti(self::$struct_primary, $pri_03, $columns);
			$this->tdb->runInsertQueryMulti(self::$struct_primary, $pri_04, $columns);
			$this->tdb->runInsertQueryMulti(self::$struct_primary, $pri_05, $columns);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		

		
                // Check primary table
                // ===========================================
		
		
		try {
			$ctrl = array("format"=>"array_object");
			$result = $this->tdb->runSelectQuery(self::$struct_primary, $args = null, $columns, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$this->assertEquals($check_array, $result);
		

		// Load join table
		// ===========================================

		$sec_01 = new stdClass();
		$sec_01->tree = "1";
		$sec_01->quantity = "2";
		$sec_01->size = "data_01";
		$sec_01->color = "red";
		$sec_01->weight = "11111";

		$sec_02 = new stdClass();
		$sec_02->tree = "1";
		$sec_02->quantity = "3";
		$sec_02->size = "data_02";
		$sec_02->color = "green";
		$sec_02->weight = "22222";

		$sec_03 = new stdClass();
		$sec_03->tree = "1";
		$sec_03->quantity = "2";
		$sec_03->size = "data_03";
		$sec_03->color = "blue";
		$sec_03->weight = "33333";

		$sec_04 = new stdClass();
		$sec_04->tree = "2";
		$sec_04->quantity = "2";
		$sec_04->size = "data_01";
		$sec_04->color = "red";
		$sec_04->weight = "11111";
		
		$sec_05 = new stdClass();
		$sec_05->tree = "3";
		$sec_05->quantity = "2";
		$sec_05->size = "data_02";
		$sec_05->color = "green";
		$sec_05->weight = "22222";
		
		$sec_06 = new stdClass();
		$sec_06->tree = "3";
		$sec_06->quantity = "2";
		$sec_06->size = "data_03";
		$sec_06->color = "blue";
		$sec_06->weight = "33333";
		
		$sec_07 = new stdClass();
		$sec_07->tree = "5";
		$sec_07->quantity = "2";
		$sec_07->size = "data_04";
		$sec_07->color = "blue";
		$sec_07->weight = "33333";
		
		$check_array = array($sec_01, $sec_02, $sec_03, $sec_04, $sec_05, $sec_06, $sec_07);

		try {
			$this->tdb->runInsertQueryMulti(self::$struct_join, $sec_01, $columns);
			$this->tdb->runInsertQueryMulti(self::$struct_join, $sec_02, $columns);
			$this->tdb->runInsertQueryMulti(self::$struct_join, $sec_03, $columns);
			$this->tdb->runInsertQueryMulti(self::$struct_join, $sec_04, $columns);
			$this->tdb->runInsertQueryMulti(self::$struct_join, $sec_05, $columns);
			$this->tdb->runInsertQueryMulti(self::$struct_join, $sec_06, $columns);
			$this->tdb->runInsertQueryMulti(self::$struct_join, $sec_07, $columns);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		
		
                // Check join table
                // ===========================================
		
		try {
			$ctrl = array("format"=>"array_object");		    
			$result = $this->tdb->runSelectQuery(self::$struct_join, $args=null, $columns, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}

		$this->assertEquals($check_array, $result);

	}


	function test_runSelectQueryLeftJoin_defaultAliases_2() {
    
	    
		// Join two tables using default column and table aliases
		// ============================================================================================
				
		try {
		    
			$primary = array( "class"=>self::$struct_primary);

			$join = array(
					array(
					    "class"=>self::$struct_join,
					    "on"=>array("pri"=>"id", "op"=>"=", "sec"=>"tree")
					    )
			);	
			
			$ctrl = array("format"=>"array_array");
			
			$result = $this->tdb->runSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl);
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}						
		
		$check_array = array(
			array( "t1id"=>1, "t1name"=>"data_01", "t1text"=>"Test Text String 01", "t1priv"=>1, "t1files"=>222, "t1space"=>3333, "t2id"=>1, "t2tree"=>1, "t2quantity"=>2, "t2size"=>"data_01", "t2color"=>"red", "t2weight"=>11111),
			array( "t1id"=>1, "t1name"=>"data_01", "t1text"=>"Test Text String 01", "t1priv"=>1, "t1files"=>222, "t1space"=>3333, "t2id"=>2, "t2tree"=>1, "t2quantity"=>3, "t2size"=>"data_02", "t2color"=>"green", "t2weight"=>22222),
			array( "t1id"=>1, "t1name"=>"data_01", "t1text"=>"Test Text String 01", "t1priv"=>1, "t1files"=>222, "t1space"=>3333, "t2id"=>3, "t2tree"=>1, "t2quantity"=>2, "t2size"=>"data_03", "t2color"=>"blue", "t2weight"=>33333),
			array( "t1id"=>2, "t1name"=>"data_02", "t1text"=>"Test Text String 02", "t1priv"=>1, "t1files"=>222, "t1space"=>4444, "t2id"=>4, "t2tree"=>2, "t2quantity"=>2, "t2size"=>"data_01", "t2color"=>"red", "t2weight"=>11111),
			array( "t1id"=>3, "t1name"=>"data_03", "t1text"=>"Test Text String 03", "t1priv"=>1, "t1files"=>555, "t1space"=>6666, "t2id"=>5, "t2tree"=>3, "t2quantity"=>2, "t2size"=>"data_02", "t2color"=>"green", "t2weight"=>22222),
			array( "t1id"=>3, "t1name"=>"data_03", "t1text"=>"Test Text String 03", "t1priv"=>1, "t1files"=>555, "t1space"=>6666, "t2id"=>6, "t2tree"=>3, "t2quantity"=>2, "t2size"=>"data_03", "t2color"=>"blue", "t2weight"=>33333),
			array( "t1id"=>4, "t1name"=>"data_04", "t1text"=>"Test Text String 04", "t1priv"=>3, "t1files"=>555, "t1space"=>6666, "t2id"=>0, "t2tree"=>0, "t2quantity"=>0, "t2size"=>"", "t2color"=>"", "t2weight"=>0),
			array( "t1id"=>5, "t1name"=>"data_05", "t1text"=>"Test Text String 05", "t1priv"=>4, "t1files"=>666, "t1space"=>7777, "t2id"=>7, "t2tree"=>5, "t2quantity"=>2, "t2size"=>"data_04", "t2color"=>"blue", "t2weight"=>33333)
			
		);  

		$this->assertEquals($check_array, $result);
		
	}
    
	
	function test_runSelectQueryLeftJoin_defaultAliases_3() {
	    
	    
		// Join three tables using default column and table aliases
		// ============================================================================================
		
		try {
		    
			$primary = array( "class"=>self::$struct_primary);

			$join = array(
					array(
					    "class"=>self::$struct_join,
					    "on"=>array("pri"=>"id", "op"=>"=", "sec"=>"tree")
					    ),
					array(
					    "class"=>self::$struct_join,
					    "on"=>array("pri"=>"id", "op"=>"=", "sec"=>"tree")
					    )
			);	
			
			$ctrl = array("format"=>"array_array");
			
			$result = $this->tdb->runSelectQueryLeftJoin($primary, $join, $columns=null, $ctrl);
			
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
			
		$check_array = array(
		    
			array(	"t1id"=>1, "t1name"=>"data_01", "t1text"=>"Test Text String 01", "t1priv"=>1, "t1files"=>222, "t1space"=>3333, 
				"t2id"=>1, "t2tree"=>1, "t2quantity"=>2, "t2size"=>"data_01", "t2color"=>"red", "t2weight"=>11111, 
				"t3id"=>1, "t3tree"=>1, "t3quantity"=>2, "t3size"=>"data_01", "t3color"=>"red", "t3weight"=>11111  
			),
			array(	"t1id"=>1, "t1name"=>"data_01", "t1text"=>"Test Text String 01", "t1priv"=>1, "t1files"=>222, "t1space"=>3333, 
				"t2id"=>1, "t2tree"=>1, "t2quantity"=>2, "t2size"=>"data_01", "t2color"=>"red", "t2weight"=>11111, 
				"t3id"=>2, "t3tree"=>1, "t3quantity"=>3, "t3size"=>"data_02", "t3color"=>"green", "t3weight"=>22222 
			),
			array(	"t1id"=>1, "t1name"=>"data_01", "t1text"=>"Test Text String 01", "t1priv"=>1, "t1files"=>222, "t1space"=>3333, 
				"t2id"=>1, "t2tree"=>1, "t2quantity"=>2, "t2size"=>"data_01", "t2color"=>"red", "t2weight"=>11111, 
				"t3id"=>3, "t3tree"=>1, "t3quantity"=>2, "t3size"=>"data_03", "t3color"=>"blue", "t3weight"=>33333 
			),				
		    	array(	"t1id"=>1, "t1name"=>"data_01", "t1text"=>"Test Text String 01", "t1priv"=>1, "t1files"=>222, "t1space"=>3333, 
				"t2id"=>2, "t2tree"=>1, "t2quantity"=>3, "t2size"=>"data_02", "t2color"=>"green", "t2weight"=>22222, 
				"t3id"=>1, "t3tree"=>1, "t3quantity"=>2, "t3size"=>"data_01", "t3color"=>"red", "t3weight"=>11111  
			),
		    	array(	"t1id"=>1, "t1name"=>"data_01", "t1text"=>"Test Text String 01", "t1priv"=>1, "t1files"=>222, "t1space"=>3333, 
				"t2id"=>2, "t2tree"=>1, "t2quantity"=>3, "t2size"=>"data_02", "t2color"=>"green", "t2weight"=>22222, 
				"t3id"=>2, "t3tree"=>1, "t3quantity"=>3, "t3size"=>"data_02", "t3color"=>"green", "t3weight"=>22222 
			),		    
		    	array(	"t1id"=>1, "t1name"=>"data_01", "t1text"=>"Test Text String 01", "t1priv"=>1, "t1files"=>222, "t1space"=>3333, 
				"t2id"=>2, "t2tree"=>1, "t2quantity"=>3, "t2size"=>"data_02", "t2color"=>"green", "t2weight"=>22222, 
				"t3id"=>3, "t3tree"=>1, "t3quantity"=>2, "t3size"=>"data_03", "t3color"=>"blue", "t3weight"=>33333 
			),
			array(	"t1id"=>1, "t1name"=>"data_01", "t1text"=>"Test Text String 01", "t1priv"=>1, "t1files"=>222, "t1space"=>3333, 
				"t2id"=>3, "t2tree"=>1, "t2quantity"=>2, "t2size"=>"data_03", "t2color"=>"blue", "t2weight"=>33333, 
				"t3id"=>1, "t3tree"=>1, "t3quantity"=>2, "t3size"=>"data_01", "t3color"=>"red", "t3weight"=>11111  
			),		    
			array(	"t1id"=>1, "t1name"=>"data_01", "t1text"=>"Test Text String 01", "t1priv"=>1, "t1files"=>222, "t1space"=>3333, 
				"t2id"=>3, "t2tree"=>1, "t2quantity"=>2, "t2size"=>"data_03", "t2color"=>"blue", "t2weight"=>33333, 
				"t3id"=>2, "t3tree"=>1, "t3quantity"=>3, "t3size"=>"data_02", "t3color"=>"green", "t3weight"=>22222 
			),
			array(	"t1id"=>1, "t1name"=>"data_01", "t1text"=>"Test Text String 01", "t1priv"=>1, "t1files"=>222, "t1space"=>3333, 
				"t2id"=>3, "t2tree"=>1, "t2quantity"=>2, "t2size"=>"data_03", "t2color"=>"blue", "t2weight"=>33333, 
				"t3id"=>3, "t3tree"=>1, "t3quantity"=>2, "t3size"=>"data_03", "t3color"=>"blue", "t3weight"=>33333 
			),
			array(	"t1id"=>2, "t1name"=>"data_02", "t1text"=>"Test Text String 02", "t1priv"=>1, "t1files"=>222, "t1space"=>4444,  
				"t2id"=>4, "t2tree"=>2, "t2quantity"=>2, "t2size"=>"data_01", "t2color"=>"red", "t2weight"=>11111,
				"t3id"=>4, "t3tree"=>2, "t3quantity"=>2, "t3size"=>"data_01", "t3color"=>"red", "t3weight"=>11111
			),		    
			array(	"t1id"=>3, "t1name"=>"data_03", "t1text"=>"Test Text String 03", "t1priv"=>1, "t1files"=>555, "t1space"=>6666,
				"t2id"=>5, "t2tree"=>3, "t2quantity"=>2, "t2size"=>"data_02", "t2color"=>"green", "t2weight"=>22222,
				"t3id"=>5, "t3tree"=>3, "t3quantity"=>2, "t3size"=>"data_02", "t3color"=>"green", "t3weight"=>22222, 
			),		    
			array(	"t1id"=>3, "t1name"=>"data_03", "t1text"=>"Test Text String 03", "t1priv"=>1, "t1files"=>555, "t1space"=>6666, 
				"t2id"=>5, "t2tree"=>3, "t2quantity"=>2, "t2size"=>"data_02", "t2color"=>"green", "t2weight"=>22222,
				"t3id"=>6, "t3tree"=>3, "t3quantity"=>2, "t3size"=>"data_03", "t3color"=>"blue", "t3weight"=>33333
			),		    
			array(	"t1id"=>3, "t1name"=>"data_03", "t1text"=>"Test Text String 03", "t1priv"=>1, "t1files"=>555, "t1space"=>6666,
				"t2id"=>6, "t2tree"=>3, "t2quantity"=>2, "t2size"=>"data_03", "t2color"=>"blue", "t2weight"=>33333,
				"t3id"=>5, "t3tree"=>3, "t3quantity"=>2, "t3size"=>"data_02", "t3color"=>"green", "t3weight"=>22222, 
			),		    		    
			array(	"t1id"=>3, "t1name"=>"data_03", "t1text"=>"Test Text String 03", "t1priv"=>1, "t1files"=>555, "t1space"=>6666,
				"t2id"=>6, "t2tree"=>3, "t2quantity"=>2, "t2size"=>"data_03", "t2color"=>"blue", "t2weight"=>33333,
				"t3id"=>6, "t3tree"=>3, "t3quantity"=>2, "t3size"=>"data_03", "t3color"=>"blue", "t3weight"=>33333 
			),
			array(	"t1id"=>4, "t1name"=>"data_04", "t1text"=>"Test Text String 04", "t1priv"=>3, "t1files"=>555, "t1space"=>6666,
				"t2id"=>0, "t2tree"=>0, "t2quantity"=>0, "t2size"=>"", "t2color"=>"", "t2weight"=>0,
				"t3id"=>0, "t3tree"=>0, "t3quantity"=>0, "t3size"=>"", "t3color"=>"", "t3weight"=>0 
			),
			array(	"t1id"=>5, "t1name"=>"data_05", "t1text"=>"Test Text String 05", "t1priv"=>4, "t1files"=>666, "t1space"=>7777, 
				"t2id"=>7, "t2tree"=>5, "t2quantity"=>2, "t2size"=>"data_04", "t2color"=>"blue", "t2weight"=>33333,
				"t3id"=>7, "t3tree"=>5, "t3quantity"=>2, "t3size"=>"data_04", "t3color"=>"blue", "t3weight"=>33333
			)		  			
		);  

		$this->assertEquals($check_array, $result);
		
	}
	    	  
	
	function test_runSelectQueryLeftJoin_aliases_sumCountGroup_2() {
	    
	    
		// Join two tables using aliases (column and table), Sum, Count and group by
		// ============================================================================================		
		
		try {
		    
			$primary = array( "class"=>self::$struct_primary, "alias"=>"prim");

			$join = array(
					array(
					    "class"=>self::$struct_join,
					    "alias"=>"sec",
					    "on"=>array("pri"=>"id", "op"=>"=", "sec"=>"tree")
					    )
			);

			$columns = array(
				    array("table_alias"=>"prim", "col_name"=>"name", "col_alias"=>"name"),
				    array("table_alias"=>"prim", "col_name"=>"text", "col_alias"=>"text"),
				    array("table_alias"=>"prim", "col_name"=>"priv", "col_alias"=>"priv"),
				    array("table_alias"=>"sec", "col_name"=>"tree", "col_alias"=>"tree_amt", "count"=>true),
				    array("table_alias"=>"sec", "col_name"=>"quantity", "col_alias"=>"quantity_total", "sum"=>true)
			);

			$ctrl = array("format"=>"array_array", 'group'=>array( array("class"=>self::$struct_primary, "col"=>"name", "sort"=>"ASC") ) );

			$result = $this->tdb->runSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
			
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		

		$check_array = array(
				    array( "name"=>"data_01",	"text"=> "Test Text String 01",	"priv"=>1,  "tree_amt"=>3,  "quantity_total"=>7 ),
				    array( "name"=>"data_02",	"text"=> "Test Text String 02",	"priv"=>1,  "tree_amt"=>1,  "quantity_total"=>2 ),
				    array( "name"=>"data_03",	"text"=> "Test Text String 03",	"priv"=>1,  "tree_amt"=>2,  "quantity_total"=>4 ),
				    array( "name"=>"data_04",	"text"=> "Test Text String 04",	"priv"=>3,  "tree_amt"=>0,  "quantity_total"=>0 ),
				    array( "name"=>"data_05",	"text"=> "Test Text String 05",	"priv"=>4,  "tree_amt"=>1,  "quantity_total"=>2 )
		);   
		
		$this->assertEquals($check_array, $result);
		
	}
	
	
	function test_runSelectQueryLeftJoin_aliases_sumCountGroup_3() {
	    
	    
		// Join three tables using aliases (column and table), Sum, Count and group by
		// ============================================================================================
				
		try {
		    
			$primary = array( "class"=>self::$struct_primary, "alias"=>"prim");

			$join = array(
					array(
					    "class"=>self::$struct_join,
					    "alias"=>"sec",
					    "on"=>array("pri"=>"id", "op"=>"=", "sec"=>"tree")
					    ),
					array(
					    "class"=>self::$struct_join,
					    "alias"=>"ter",
					    "on"=>array("pri"=>"id", "op"=>"=", "sec"=>"tree")
					    )
			);

			$columns = array(
				array( "table_alias"=>"prim",	"col_name"=>"name",	"col_alias"=>"name" ),
				array( "table_alias"=>"prim",	"col_name"=>"text",	"col_alias"=>"text" ),
				array( "table_alias"=>"prim",	"col_name"=>"priv",	"col_alias"=>"priv" ),
				array( "table_alias"=>"sec",	"col_name"=>"tree",	"col_alias"=>"tree_amt",	"count"=>true ),
				array( "table_alias"=>"ter",	"col_name"=>"quantity",	"col_alias"=>"quantity_total",	"sum"=>true )
			);

			$ctrl = array("format"=>"array_array", 'group'=>array( array("class"=>self::$struct_primary, "col"=>"name", "sort"=>"ASC") ) );

			$result = $this->tdb->runSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
			
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		

		$check_array = array(
			array( "name"=>"data_01",   "text"=> "Test Text String 01", "priv"=>1,	"tree_amt"=>9,	"quantity_total"=>21 ),
			array( "name"=>"data_02",   "text"=> "Test Text String 02", "priv"=>1,	"tree_amt"=>1,	"quantity_total"=>2 ),
			array( "name"=>"data_03",   "text"=> "Test Text String 03", "priv"=>1,	"tree_amt"=>4,	"quantity_total"=>8 ),
			array( "name"=>"data_04",   "text"=> "Test Text String 04", "priv"=>3,	"tree_amt"=>0,	"quantity_total"=>0 ),
			array( "name"=>"data_05",   "text"=> "Test Text String 05", "priv"=>4,	"tree_amt"=>1,	"quantity_total"=>2 )
		);   
		
		$this->assertEquals($check_array, $result);
		
	}
	
	
	function test_runSelectQueryLeftJoin_aliases_multiplyCountGroupSourt_2() {
	    
	    
		// Join two tables using aliases (column and table), Sum (multiplying) two columns, Count, group by and Sort
		// ============================================================================================
				
		try {
		    
			$primary = array( "class"=>self::$struct_primary, "alias"=>"prim");

			$join = array(
					array(
					    "class"=>self::$struct_join,
					    "alias"=>"sec",
					    "on"=>array("pri"=>"id", "op"=>"=", "sec"=>"tree")
					    )
			);
			
			$columns = array(
			    
				array( "table_alias"=>"prim",	"col_name"=>"name", "col_alias"=>"name" ),
				array( "table_alias"=>"prim",	"col_name"=>"text", "col_alias"=>"text" ),
				array( "table_alias"=>"prim",	"col_name"=>"priv", "col_alias"=>"priv" ),
				array( "table_alias"=>"sec",	"col_name"=>"tree", "col_alias"=>"tree_amt", "count"=>true),
				array( "col_alias"=>"weight_total", "sum"=> array(
										    array( "table_alias"=>"sec",    "col_name"=>"weight",   "op"=>"+"),
										    array( "table_alias"=>"sec",    "col_name"=>"quantity", "op"=>"*")				
								    )
				)
			);

			$ctrl = array( 
				"format"=>"array_array", 
				'group'=>	array( array("class"=>self::$struct_primary, "col"=>"name", "sort"=>"ASC") ), 
				'sort'=>	array( array("class"=>self::$struct_primary, "col"=>"name", "sort"=>"DESC"))
			);

			$result = $this->tdb->runSelectQueryLeftJoin($primary, $join, $columns, $ctrl, $check=true);		    
			
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		

		$check_array = array(
			array( "name"=>"data_05",   "text"=> "Test Text String 05", "priv"=>4,	"tree_amt"=>1,	"weight_total"=>66666	),		    
			array( "name"=>"data_04",   "text"=> "Test Text String 04", "priv"=>3,	"tree_amt"=>0,	"weight_total"=>0	),
			array( "name"=>"data_03",   "text"=> "Test Text String 03", "priv"=>1,	"tree_amt"=>2,	"weight_total"=>111110	),
			array( "name"=>"data_02",   "text"=> "Test Text String 02", "priv"=>1,	"tree_amt"=>1,	"weight_total"=>22222	),
			array( "name"=>"data_01",   "text"=> "Test Text String 01", "priv"=>1,	"tree_amt"=>3,	"weight_total"=>155554	)
		);   
		
		$this->assertEquals($check_array, $result);
		
	}
	
	
	function test_runSelectQueryLeftJoin_args() {
	    
	    
		// Join two tables using arguments
		// ============================================================================================
						
		try {
		    
			$args_primary = array( array( "col"=>"priv", "op"=>"<=", "val"=>3) );

			$primary = array( "class"=>self::$struct_primary, "args"=>$args_primary, "alias"=>"prim");

			$args_join = array( array( "col"=>"tree", "op"=>"!=", "val"=>2) );

			$join = array(
					array(
					    "class"=>self::$struct_join,
					    "on"=>array("pri"=>"id", "op"=>"=", "sec"=>"tree"),
					    "alias"=>"sec",
					    "args"=>$args_join
					)
			);

			$columns = array(
			    
				array( "table_alias"=>"prim",	"col_name"=>"name", "col_alias"=>"name"),
				array( "table_alias"=>"prim",	"col_name"=>"text", "col_alias"=>"text"),
				array( "table_alias"=>"prim",	"col_name"=>"priv", "col_alias"=>"priv"),
				array( "table_alias"=>"sec",	"col_name"=>"tree", "col_alias"=>"tree_amt", "count"=>true),
				array( "col_alias"=>"weight_total", "sum"=> array(
										    array("table_alias"=>"sec", "col_name"=>"weight", "op"=>"+"),
										    array("table_alias"=>"sec", "col_name"=>"quantity", "op"=>"*")				
								    )
				)
			);

			$ctrl = array(
				"format"=>"array_array", 
				'group'=>	array( array("class"=>self::$struct_primary, "col"=>"name", "sort"=>"ASC") ), 
				'sort'=>	array( array("class"=>self::$struct_primary, "col"=>"name", "sort"=>"DESC") )
			);

			$result = $this->tdb->runSelectQueryLeftJoin($primary, $join, $columns, $ctrl);
	    			
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
		$check_array = array(
			array( "name"=>"data_03",   "text"=>"Test Text String 03",  "priv"=>1,	"tree_amt"=>2,	"weight_total"=>111110 ),
			array( "name"=>"data_01",   "text"=>"Test Text String 01",  "priv"=>1,	"tree_amt"=>3,	"weight_total"=>155554 )
		);   

		$this->assertEquals($check_array, $result);		
		
	}
	
	
	function test_runSelectQueryLeftJoin_argssortInSet() {
	    
	    
		// Sort in set
		// ============================================================================================
		
		try {
		    
			$primary = array( "class"=>self::$struct_primary, "alias"=>"prim");

			$join = array(
					array(
					    "class"=>self::$struct_join,
					    "alias"=>"sec",
					    "on"=>array("pri"=>"id", "op"=>"=", "sec"=>"tree")
					    )
			);

			$columns = array(
				array( "table_alias"=>"prim",	"col_name"=>"name",	"col_alias"=>"name" ),
				array( "table_alias"=>"prim",	"col_name"=>"text",	"col_alias"=>"text" ),
				array( "table_alias"=>"prim",	"col_name"=>"priv",	"col_alias"=>"priv" ),
				array( "table_alias"=>"sec",	"col_name"=>"tree",	"col_alias"=>"tree_amt",	"count"=>true),
				array( "table_alias"=>"sec",	"col_name"=>"quantity",	"col_alias"=>"quantity_total",	"sum"=>true)
			);

			$ctrl=array(
				"format"=>"array_array", 
				'group'=>   array( array("class"=>self::$struct_primary, "col"=>"name", "sort"=>"ASC") ) ,
				'sort'=>    array( array("class"=>self::$struct_primary, "col"=>"id", "sort"=>array(3,4,5,1,2)))
			);


			$result = $this->tdb->runSelectQueryLeftJoin($primary, $join, $columns, $ctrl);		    
    			
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}		

		$check_array = array(
			array( "name"=>"data_03", "text"=> "Test Text String 03", "priv"=>1, "tree_amt"=>2, "quantity_total"=>4 ),
			array( "name"=>"data_04", "text"=> "Test Text String 04", "priv"=>3, "tree_amt"=>0, "quantity_total"=>0 ),
			array( "name"=>"data_05", "text"=> "Test Text String 05", "priv"=>4, "tree_amt"=>1, "quantity_total"=>2 ),		    
			array( "name"=>"data_01", "text"=> "Test Text String 01", "priv"=>1, "tree_amt"=>3, "quantity_total"=>7 ),
			array( "name"=>"data_02", "text"=> "Test Text String 02", "priv"=>1, "tree_amt"=>1, "quantity_total"=>2 )				    
		);   
		
		$this->assertEquals($check_array, $result);
		
	}
	    
	    
	function test_runSelectQueryLeftJoin_offsetPaging() {
	    
	    
		// Offset and per page
		// ============================================================================================
		
		try {
		    
			$primary = array( "class"=>self::$struct_primary, "alias"=>"prim");

			$join = array(
					array(
					    "class"=>self::$struct_join,
					    "alias"=>"sec",
					    "on"=>array("pri"=>"id", "op"=>"=", "sec"=>"tree")
					 )
			);

			$columns = array(
				array( "table_alias"=>"prim",	"col_name"=>"name",	"col_alias"=>"name" ),
				array( "table_alias"=>"prim",	"col_name"=>"text",	"col_alias"=>"text" ),
				array( "table_alias"=>"prim",	"col_name"=>"priv",	"col_alias"=>"priv" ),
				array( "table_alias"=>"sec",	"col_name"=>"tree",	"col_alias"=>"tree_amt",	"count"=>true),
				array( "table_alias"=>"sec",	"col_name"=>"quantity",	"col_alias"=>"quantity_total",	"sum"=>true)
			);

			$ctrl=array(
				    "format"=>"array_array", 
				    "per_page"=>2, 
				    "offset"=>3,
				    'group'=>array( array("class"=>self::$struct_primary, "col"=>"name", "sort"=>"ASC") ) 
			);

			$result = $this->tdb->runSelectQueryLeftJoin($primary, $join, $columns, $ctrl);		    
    			
		}
		catch (BPM_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		

		$check_array = array(
			array( "name"=>"data_04", "text"=> "Test Text String 04", "priv"=>3, "tree_amt"=>0, "quantity_total"=>0 ),
			array( "name"=>"data_05", "text"=> "Test Text String 05", "priv"=>4, "tree_amt"=>1, "quantity_total"=>2 )
		);   
		
		$this->assertEquals($check_array, $result);
		
	}
	

	function tearDown() {

	    
		$this->tdb = new BPM_db();
		
		try {
			$this->tdb->runDropTable(self::$struct_primary);
		}
		catch (BPM_exception $child) {
		    		    
			$this->fail("Error while dropping primary database table. Error code: " . $child->data['numeric']);			    
		}
		
		try {
			$this->tdb->runDropTable(self::$struct_join);
		}
		catch (BPM_exception $child) {
		    		    
			$this->fail("Error while dropping join database table. Error code: " . $child->data['numeric']);			    
		}			

		parent::tearDown();
		
	}

}

?>