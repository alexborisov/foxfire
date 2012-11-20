<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - DATABASE - SELECT JOIN
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Unit Test
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class database_runSelectQueryJoin extends RAZ_testCase {

    
        static $struct_primary = array(

                "table" => "fox_test_runSelectQueryJoin_primary",
                "engine" => "InnoDB",
                "columns" => array(
                    "id" =>     array(  "php"=>"int",       "sql"=>"smallint",  "format"=>"%d", "width"=>6,     "flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
                    "name" =>   array(  "php"=>"string",    "sql"=>"varchar",   "format"=>"%s", "width"=>250,   "flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>"UNIQUE"),
                    "text" =>   array(  "php"=>"string",    "sql"=>"varchar",   "format"=>"%s", "width"=>250,   "flags"=>null,       "auto_inc"=>false, "default"=>null,  "index"=>false),
                    "priv" =>   array(  "php"=>"int",       "sql"=>"tinyint",   "format"=>"%d", "width"=>2,     "flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>true),
                    "files" =>  array(  "php"=>"int",       "sql"=>"mediumint", "format"=>"%d", "width"=>7,     "flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>false),
                    "space" =>  array(  "php"=>"float",     "sql"=>"bigint",    "format"=>"%d", "width"=>null,  "flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,   "index"=>false)
                 )
        );


        static $struct_join = array(

                "table" => "fox_test_runSelectQueryJoin_secondary",
                "engine" => "InnoDB",
                "columns" => array(
                    "id" =>     array(  "php"=>"int",       "sql"=>"smallint",  "format"=>"%d", "width"=>6,     "flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
                    "size" =>   array(  "php"=>"string",    "sql"=>"varchar",   "format"=>"%s", "width"=>250,   "flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>false),
                    "color" =>  array(  "php"=>"string",    "sql"=>"varchar",   "format"=>"%s", "width"=>250,   "flags"=>null,       "auto_inc"=>false, "default"=>null,  "index"=>false),
                    "weight" => array(  "php"=>"float",     "sql"=>"bigint",    "format"=>"%d", "width"=>10,    "flags"=>"NOT NULL", "auto_inc"=>false, "default"=>0,     "index"=>true),
                 )
        );


    	function setUp() {

		parent::setUp();

		$this->tdb = new FOX_db();

		try {
			$this->tdb->runAddTable(self::$struct_primary);
		}
		catch (FOX_exception $fail) {
		    
		    
			// CASE 1: the table already exists in the db (likely from a previous failed test 
			// run), so we need to make sure its clear.
			// ===============================================================================
			if($fail->data['numeric'] == 2){
			    
				try {
					$this->tdb->runTruncateTable(self::$struct_primary);
				}
				catch (FOX_exception $child) {

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
		catch (FOX_exception $fail) {
		    
		    
			// CASE 1: the table already exists in the db (likely from a previous failed test 
			// run), so we need to make sure its clear.
			// ===============================================================================
			if($fail->data['numeric'] == 2){
			    
				try {
					$this->tdb->runTruncateTable(self::$struct_join);
				}
				catch (FOX_exception $child) {

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

                $check_array = array($pri_01, $pri_02, $pri_03, $pri_04);

                $columns = array("mode"=>"exclude", "col"=>"id");

		try {
			$this->tdb->runInsertQueryMulti(self::$struct_primary, $pri_01, $columns);
			$this->tdb->runInsertQueryMulti(self::$struct_primary, $pri_02, $columns);
			$this->tdb->runInsertQueryMulti(self::$struct_primary, $pri_03, $columns);
			$this->tdb->runInsertQueryMulti(self::$struct_primary, $pri_04, $columns);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		

                // Check primary table
                // ===========================================
		
		$ctrl = array("format"=>"array_object");	
		
		try {
			$result = $this->tdb->runSelectQuery(self::$struct_primary, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
                $this->assertEquals($check_array, $result);


                // Load join table
                // ===========================================

                $sec_01 = new stdClass();
                $sec_01->size = "data_01";
                $sec_01->color = "red";
                $sec_01->weight = "11111";

                $sec_02 = new stdClass();
                $sec_02->size = "data_02";
                $sec_02->color = "green";
                $sec_02->weight = "22222";

                $sec_03 = new stdClass();
                $sec_03->size = "data_03";
                $sec_03->color = "blue";
                $sec_03->weight = "33333";

                $check_array = array($sec_01, $sec_02, $sec_03);

		try {
			$this->tdb->runInsertQueryMulti(self::$struct_join, $sec_01, $columns);
			$this->tdb->runInsertQueryMulti(self::$struct_join, $sec_02, $columns);
			$this->tdb->runInsertQueryMulti(self::$struct_join, $sec_03, $columns);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
		
                // Check join table
                // ===========================================
		
		$ctrl = array("format"=>"array_object");
		
		try {
			$result = $this->tdb->runSelectQuery(self::$struct_join, $args=null, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}
                

                $this->assertEquals($check_array, $result);

	}


	function test_runSelectQueryJoin(){


                $args_primary = array(

                        array( "col"=>"priv", "op"=>"=", "val"=>1)
                );

                $primary = array( "class"=>self::$struct_primary, "args"=>$args_primary);


                $args_join = array(

                        array( "col"=>"weight", "op"=>"=", "val"=>22222)
                );

                $join = array(
                                array(
                                    "class"=>self::$struct_join,
                                    "on"=>array("pri"=>"name", "op"=>"=", "sec"=>"size"),
                                    "args"=>$args_join
                                )
                );

                $columns = array("mode"=>"exclude", "col"=>"id");
		
		$ctrl = array("format"=>"array_object");

		try {
			$result = $this->tdb->runSelectQueryJoin($primary, $join, $columns, $ctrl);
		}
		catch (FOX_exception $child) {

			$this->fail($child->dumpString(1));	
		}		                

                $data_check = new stdClass();
                $data_check->name = "data_02";
                $data_check->text = "Test Text String 02";
                $data_check->priv = "1";
                $data_check->files = "222";
                $data_check->space = "4444";

                $check_array = array($data_check);

                $this->assertEquals($check_array, $result);				
		
	}

	function tearDown() {

	    
		$this->tdb = new FOX_db();
		
		try {
			$this->tdb->runDropTable(self::$struct_primary);
		}
		catch (FOX_exception $child) {
		    		    
			$this->fail("Error while dropping primary database table. Error code: " . $child->data['numeric']);			    
		}
		
		try {
			$this->tdb->runDropTable(self::$struct_join);
		}
		catch (FOX_exception $child) {
		    		    
			$this->fail("Error while dropping join database table. Error code: " . $child->data['numeric']);			    
		}			

		parent::tearDown();
	}

}

?>