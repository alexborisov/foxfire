<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - CONFIG CLASS
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


class system_config extends RAZ_testCase {


	var $cls;
	var $check_array = array();
	var $delimiter;

	var $tree_max = 5;
	var $branch_max = 3;
	var $key_max = 5;


    	function setUp() {

		parent::setUp();
		$this->cls = new FOX_config();
		$this->cls->install();
		$this->delimiter = $this->cls->key_delimiter;
	}


	function generateData($type=null){


		$valid_types = array("null", "bool", "int", "float", "string", "array", "object");

		if($type === null){	// "0" is a valid type

			$current_type = $valid_types[mt_rand(0,6)];
		}
		else {
			// Using the modulus operator causes the function to cycle
			// through types if $type variable exceeds (int)5.
			$treex = $type % 6;
			$current_type = $valid_types[$treex];
		}

		switch($current_type){

			case "null" : {

				$val = null;

			} break;

			case "bool" : {

				$val = mt_rand(0,1);
				$val = (bool)$val;

			} break;

			case "int" : {

				$val = mt_rand(-100,100);
				$val = (int)$val;

			} break;

			case "float" : {

				$val = (mt_rand(-100,100) / 17);
				$val = (float)$val;

			} break;

			case "string" : {

				$width = mt_rand(0, 87);
				$val = FOX_sUtil::random_string($width);

			} break;

			case "array" : {

				$named_keys = mt_rand(0,1);
				$val = array();

				if(!$named_keys){

					for($i=0; $i<5; $i++){
						$val[] = mt_rand(-2500, 2500);
					}
				}
				else {

					for($i=0; $i<5; $i++){

						$width = mt_rand(1, 37);

						// PHP does not support arrays with numeric string keys. It silently converts them
						// to int keys. For example: $test_array["1234"] = "foo"; $test_array["bar"] = "baz";
						// var_dump($test_array) produces [ test_array[(int)1234] = "foo", test_array[(string)bar]
						// = "baz"]
						//
						// FOX_sUtil::random_string() can (and does) produce values like (string)7. To avoid
						// debugging problems, prepend all the key names with "k" to ensure PHP treats them as strings.

						$key_name = "k" . FOX_sUtil::random_string($width);
						$val[$key_name] = mt_rand(-2500, 2500);
					}
				}

			} break;

			case "object" : {

				$val = new stdClass();

				for($i=0; $i<3; $i++){

					$width = mt_rand(1, 17);

					// We prepend all the key names with "k" because FOX_sUtil::random_string()
					// can (and does) produce values like (string)7, which is an illegal name for a
					// variable in an object [you can't do $test_object->7 = "foo"; ...because the parser
					// doesn't know if you mean (string)7 or (int)7 ]
					//
					// But, PHP *doesn't catch* this defect when the variable name is created dynamically
					// [ $name = "7"; $test_object->{$name} = "foo" ]. Instead, it converts the key's type
					// from (string)7 to (int)7 and allows the variable to be accessed only if the name is
					// created dynamically [$name = "7"; echo ($test_object->{$name}; ]

					$key_name = "k" . FOX_sUtil::random_string($width);
					$val->{$key_name} = mt_rand(-2500, 2500);
				}

			} break;

		} // End of switch($current_type)

		return $val;

	}


	function load_table(){


		// Fill table with policy options having random sizes
		// and data types. Save a copy of these to a local array.
		// =================================================================

		$total_keys = 0;


		    for( $tree=1; $tree < $this->tree_max; $tree++) {

			for( $branch=1; $branch < $this->branch_max; $branch++) {

			    for ($key=1; $key < $this->key_max; $key++) {


				// Cycle through data types for the first 50 keys, then
				// randomly pick data types
				if($total_keys < 50){
					$val = self::generateData($total_keys);
				}
				else {
					$val = self::generateData();
				}

				$tree_name = "T" . $tree;
				$branch_name = "B" . $branch;
				$key_name = "K" . $key;
				$filter = "debug";
				$ctrl = false;

				// Write generated value to policy class and check array
				$set_ok = $this->cls->addNode($tree_name, $branch_name, $key_name, $val, $filter, $ctrl);

				$this->assertEquals(true, $set_ok);

				$this->check_array[$tree_name][$branch_name][$key_name]["val"] = $val;
				$this->check_array[$tree_name][$branch_name][$key_name]["filter"] = $filter;
				$this->check_array[$tree_name][$branch_name][$key_name]["ctrl"] = $ctrl;

				$total_keys++;

			    } // ENDOF for( $key

			} // ENDOF for( $branch

		    } // ENDOF: for( $tree


	}


	function check_table(){

		// Load every key stored to the table and compare it against
		// the value stored in the check array, making sure data types were
		// correctly recovered (bool) false doesn't become (int) 0 or "NULL"
		// ====================================================================


		for( $tree=1; $tree < $this->tree_max; $tree++) {

		    for( $branch=1; $branch < $this->branch_max; $branch++) {

			for ($key=1; $key < $this->key_max; $key++) {

				$tree_name = "T" . $tree;
				$branch_name = "B" . $branch;
				$key_name = "K" . $key;

				$expected_val = $this->check_array[$tree_name][$branch_name][$key_name]["val"];
				$expected_filter = $this->check_array[$tree_name][$branch_name][$key_name]["filter"];
				$expected_ctrl = $this->check_array[$tree_name][$branch_name][$key_name]["ctrl"];
				
				$result = $this->cls->getNode($tree_name, $branch_name, $key_name, $valid);


				if( FOX_sUtil::keyExists($key_name, $this->check_array[$tree_name][$branch_name]) ){

					$this->assertEquals(true, $valid);

					$result_val = $result["val"];
					$result_filter = $result["filter"];
					$result_ctrl = $result["ctrl"];

					$this->assertEquals($expected_val, $result_val);
					$this->assertEquals($expected_filter, $result_filter);
					$this->assertEquals($expected_ctrl, $result_ctrl);
				}
				else {
					$this->assertEquals(null, $result);
					$this->assertEquals(false, $valid);
				}

			}
		    }
		}
		
	}


	function test_create(){

		// Clear the db
		$this->cls->truncate();

		// Delete all entries in the class cache
		$this->cls->flushCache();

		// Load the database and check array with test data
		self::load_table();

		// Verify the stored data matches the check array
		self::check_table();


	} // End of test_create



	function test_update(){

		// Clear the db
		$this->cls->truncate();

		// Delete all entries in the class cache
		$this->cls->flushCache();

		// Load the database and check array with test data
		self::load_table();

		// Overwrite half of the items in the table with random new data
		// of random type
		// =================================================================
		$total_keys = 0;

		    for( $tree=1; $tree < $this->tree_max; $tree++) {

			for( $branch=1; $branch < $this->branch_max; $branch++) {

			    for ($key=1; $key < $this->key_max; $key++) {

				$overwrite = mt_rand(0,1);

				if($overwrite){

					// Cycle through data types for the first 50 keys, then
					// randomly pick data types

					if($total_keys < 50){
						$val = self::generateData($total_keys);
					}
					else {
						$val = self::generateData();
					}

					$tree_name = "T" . $tree;
					$branch_name = "B" . $branch;
					$key_name = "K" . $key;

					// Write generated value to config class and check array. Note: we cannot check for
					// a "true" return value from the function, because in some cases the overwrite function
					// tries to write the same value that is currently in a key to a key ...in which case
					// the db returns (int)0 because no rows were changed by the query, and so setKey()
					// returns (bool)false as a result.

					$this->cls->setNode($tree_name, $branch_name, $key_name, $val);

					// Update the check array with the new value
					$this->check_array[$tree_name][$branch_name][$key_name]["val"] = $val;

					$total_keys++;

				}   // ENDOF if($overwrite)

			    } // ENDOF for( $key

			} // ENDOF for( $branch

		    } // ENDOF: for( $tree

		// Delete all entries in the class cache
		$this->cls->flushCache();

		// Verify the stored data matches the check array
		self::check_table();


	} // End of test_update



	function test_dropKey_single(){

		// Clear the db
		$this->cls->truncate();

		// Delete all entries in the class cache
		$this->cls->flushCache();

		// Load the database and check array with test data
		self::load_table();

		// Drop half of the items in the table, using single string dropKey

		    for( $tree=1; $tree < $this->tree_max; $tree++) {

			for( $branch=1; $branch < $this->branch_max; $branch++) {

			    for ($key=1; $key < $this->key_max; $key++) {

				$key_name = "key" . $key;
				$drop = mt_rand(0,1);

				if($drop){

				    // Drop the key
				    $this->cls->dropNode($tree, $branch, $key_name);
				    unset($this->check_array[$tree][$branch][$key_name]);


				}   // ENDOF if($drop)

			    } // ENDOF for( $key

			} // ENDOF for( $branch

		    } // ENDOF: for( $tree



		// Verify the stored data matches the check array
		self::check_table();


	} // End of test_dropKey_single


	function test_dropBranch_single(){

		// Clear the db
		$this->cls->truncate();

		// Delete all entries in the class cache
		$this->cls->flushCache();

		// Load the database and check array with test data
		self::load_table();

		// Drop half of the items in the table, using single string dropBranch

		    for( $tree=1; $tree < $this->tree_max; $tree++) {

			for( $branch=1; $branch < $this->branch_max; $branch++) {

				$drop = mt_rand(0,1);

				if($drop){

				    // Drop the branch
				    $this->cls->dropBranch($tree, $branch);
				    unset($this->check_array[$tree][$branch]);


				}   // ENDOF if($drop)

			} // ENDOF for( $branch

		    } // ENDOF: for( $tree



		// Verify the stored data matches the check array
		self::check_table();


	} // End of test_dropBranch_single


	function test_dropTree_single(){

		// Clear the db
		$this->cls->truncate();

		// Delete all entries in the class cache
		$this->cls->flushCache();

		// Load the database and check array with test data
		self::load_table();

		// Drop half of the items in the table, using single string dropType

		    for( $tree=1; $tree < $this->tree_max; $tree++) {

			    $drop = mt_rand(0,1);

			    if($drop){

				// Drop the branch
				$this->cls->dropTree($tree);
				unset($this->check_array[$tree]);


			    }   // ENDOF if($drop)

		    } // ENDOF: for( $tree


		// Verify the stored data matches the check array
		self::check_table();


	} // End of test_dropType_single



	function test_dropNode_array(){

		// Clear the db
		$this->cls->truncate();

		// Delete all entries in the class cache
		$this->cls->flushCache();

		// Load the database and check array with test data
		self::load_table();

		// Drop half of the items in the table, using single string dropKey


		    for( $tree=1; $tree < $this->tree_max; $tree++) {

			for( $branch=1; $branch < $this->branch_max; $branch++) {

				$drop_array = array();

				$drop = true;

				for ($key=1; $key < $this->key_max; $key++) {

					$key_name = "key" . $key;

					if($drop){
						$drop_array[] = $key_name;
						$drop = false;
					}
					else {
						$drop = true;
					}

				} // ENDOF for( $key

				// Drop the keys
				$this->cls->dropNode($tree, $branch, $drop_array);

				foreach($drop_array as $drop_key){
					unset($this->check_array[$tree][$branch][$drop_key]);
				}

			} // ENDOF for( $branch

		    } // ENDOF: for( $tree




		// Verify the stored data matches the check array
		self::check_table();


	} // End of test_dropKey_array


	function test_dropBranch_array(){

		// Clear the db
		$this->cls->truncate();

		// Delete all entries in the class cache
		$this->cls->flushCache();

		// Load the database and check array with test data
		self::load_table();

		// Drop half of the items in the table, using single string dropKey

		    for( $tree=1; $tree < $this->tree_max; $tree++) {

			$drop_array = array();
			$drop = true;

			for( $branch=1; $branch < $this->branch_max; $branch++) {

				if($drop){
					$drop_array[] = $branch;
					$drop = false;
				}
				else {
					$drop = true;
				}

			} // ENDOF for( $branch

			// Drop the branches
			$this->cls->dropBranch($tree, $drop_array);

			foreach($drop_array as $drop_branch){
				unset($this->check_array[$tree][$drop_branch]);
			}

		    } // ENDOF: for( $tree


		// Verify the stored data matches the check array
		self::check_table();


	} // End of test_dropBranch_array


	function test_dropTree_array(){

		// Clear the db
		$this->cls->truncate();

		// Delete all entries in the class cache
		$this->cls->flushCache();

		// Load the database and check array with test data
		self::load_table();

		// Drop half of the items in the table, using single string dropKey
		$drop = true;
		$drop_array = array();

		for( $tree=1; $tree < $this->tree_max; $tree++) {

			if($drop){
				$drop_array[] = $tree;
				$drop = false;
			}
			else {
				$drop = true;
			}

		} // ENDOF: for( $tree

		// Drop the types
		$this->cls->dropTree($drop_array);

		foreach($drop_array as $drop_type){
			unset($this->check_array[$drop_type]);
		}


		// Verify the stored data matches the check array
		self::check_table();


	} // End of test_dropType_array


	function test_ProcessHTMLForm(){

		// Clear the db
		$this->cls->truncate();

		// Delete all entries in the class cache
		$this->cls->flushCache();

		// Load the database and check array with test data. This is necessary because we
		// want to use the setKey() function for form data, not createKey()
		self::load_table();

		// Verify the stored data matches the check array
		self::check_table();


		$post_array = array();		    // The simulated superglobal $_POST array that the function has to process

		$names_array = array();		    // Used to build the comma separated list of var names within the $_POST
						    // array that our function needs to process


		// Assemble the names array into a comma-separated string of names,
		// add it to the $post_array, and then process the array
		// ====================================================================

		foreach( $this->check_array as $tree_name => $branches ){

		    foreach( $branches as $branch_name => $keys ){

			foreach( $keys as $key_name => $data ) {

				$post_key = $tree_name . $this->delimiter . $branch_name . $this->delimiter . $key_name;

				$post_array[$post_key] = $data["val"];
				$names_array[] = $post_key;
			}
		    }
		}
		unset($tree_name, $branches, $branch_name, $keys, $key_name);


		$names_left = sizeof($names_array) - 1;

		foreach($names_array as $name){

			$post_array["key_names"] .= $name;

			if($names_left != 0){
				$post_array["key_names"] .= ", ";
				$names_left--;
			}
		}
		unset($name);

		$this->cls->processHTMLForm($post_array);


		// Load every config option stored to the db and compare it against
		// the updated values stored in the local $check_array
		// ===================================================================

		for( $tree = 0; $tree < $tree_max; $tree++) {

		    for( $branch = 0; $branch < $branch_max; $branch++) {

			for ($key = 0; $key < $key_max; $key++) {

			    	$tree_str = "T".$tree;
				$branch_str = "B".$branch;
				$key_str = "K".$key;

				$result = $this->cls->getNodeVal($tree_str, $branch_str, $key_str);

				$this->assertEquals($result, $check_array[$tree_str][$branch_str][$key_str], $message=null, $delta=0.001);
			}
		    }
		}


	}


	function tearDown() {

		$class = new FOX_config();
		$class->uninstall();

		parent::tearDown();
	}



}




?>
