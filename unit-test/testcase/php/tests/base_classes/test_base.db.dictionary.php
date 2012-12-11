<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - DICTIONARY BASE CLASS
 * Exercises all functions of the class
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

class FOX_test_dictionary extends FOX_dictionary_base {


        // DB table names and structures are hard-coded into the class. This allows class methods to be
	// fired from an AJAX call, without loading the entire BP stack.

	public static $struct = array(

		"table" => "FOX_test_dictionary",
		"engine" => "InnoDB", // Required for transactions
		"cache_namespace" => "FOX_test_dictionary",
		"cache_strategy" => "paged",
		"columns" => array(
		    "id" =>		array(	"php"=>"int",	"sql"=>"smallint",	"format"=>"%d", "width"=>null,"flags"=>"NOT NULL", "auto_inc"=>true,  "default"=>null,  "index"=>"PRIMARY"),
		    "token" =>	array(	"php"=>"string",	"sql"=>"varchar",	"format"=>"%s", "width"=>32,	"flags"=>"NOT NULL", "auto_inc"=>false, "default"=>null,  "index"=>"UNIQUE"),
		 )
	);


	// PHP allows this: $foo = new $class_name; $result = $foo::$struct; but does not allow this: $result = $class_name::$struct;
	// or this: $result = $class_name::get_struct(); ...so we have to do this: $result = call_module_func( array($class_name,'_struct') );

	public static function _struct() {

		return self::$struct;
	}

	// ================================================================================================================


	public function FOX_test_dictionary() {

	}


} // End class FOX_logging_dictionary_tree

class core_base_dictionary extends RAZ_testCase {

	var $cls;

	function setUp() {

		parent::setUp();

		$this->cls = new FOX_test_dictionary();

		// --The install method throws errors
		$result = $this->cls->install($error);

		// --Note that I'm updating FOX_error so you can just go $error->getAsText()

		$this->assertEquals(true, $result, FOX_debug::formatError_print($error) );

		unset($error);

	}

	function test_addToken_Single() {

		// Clear db and cache
	    	// ======================================================

	    	try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}


		// test adding one token
		// ======================================================
		$token = "test token";


		try {
			$result_add = $this->cls->addToken( $token);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$token),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}

		// Check Function Return
		// ======================================================
		$id = 1;
		$this->assertEquals($id, $result_add);

		// Check db
		// ======================================================
	    	$db = new FOX_db();

		global $fox;

		$struct = $this->cls->_struct();
		try{
			$result_select = $db->runSelectQuery(FOX_test_dictionary::_struct(), $args=array( array( "col"=>"token", "op"=>"=", "val"=>$token) ), $column=array("mode"=>"include", "col"=>"id"), $ctrl=array("format"=>"var"));
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"DB Select exception",
					'data'=>array( "args"=>$args, "column"=>$column, "ctrl"=>$ctrl),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}
		$this->assertEquals($id, $result_select);

		// Check class cache
		// ======================================================

		$this->assertEquals($id, $this->cls->cache["tokens"][$token]);

		$this->assertEquals($token, $this->cls->cache["ids"][$id]);

		// Check persistent cache
		// ======================================================
		if($fox->cache->isActive()) {

			try{
				$this->assertEquals($id, $fox->cache->get($struct["cache_namespace"],"token_".$token ) );
				$this->assertEquals($token, $fox->cache->get($struct["cache_namespace"],"id_".$id ));
			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>5,
						'text'=>"Cache get exception",
						'data'=>array( "token"=>"token_".$token, "id"=>"id_".$id),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);
				return false;
			}
		}
	}


	function test_addToken_Multi() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}

		// Test adding multiple tokens
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$token),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}


		// Check function return
		// =======================================================

		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);

		$this->assertEquals($exp_array, $add_result);


		// Check db entry
		// =======================================================

		try{
			$select_result = $db->runSelectQuery(FOX_test_dictionary::_struct(), $args=null, $columns=null, $ctrl=array("format"=>"array_array") );
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"DB Select exception",
					'data'=>array("ctrl"=>$ctrl),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}
		$exp_array = array(  array( "id"=>5, "token"=>"five"),
					array( "id"=>4, "token"=>"four"),
					array( "id"=>1, "token"=>"one"),
					array( "id"=>3, "token"=>"three"),
					array( "id"=>2, "token"=>"two")
		);

		$this->assertEquals( $exp_array, $select_result);

		// Check class cache
		// =======================================================

		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);

		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);

		$this->assertEquals(array_flip($exp_array), $this->cls->cache["ids"]);

		//Check tokens has been added to persistent cache
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>1, "get"=>"token_one");
				$this->assertEquals(1, $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>"one", "get"=>"id_1");
				$this->assertEquals("one", $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>2, "get"=>"token_two");
				$this->assertEquals(2, $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>"two", "get"=>"id_2");
				$this->assertEquals("two", $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>3, "get"=>"token_three");
				$this->assertEquals(3, $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>"three", "get"=>"id_3");
				$this->assertEquals("three", $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>4, "get"=>"token_four");
				$this->assertEquals(4, $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>"four", "get"=>"id_4");
				$this->assertEquals("four", $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>5, "get"=>"token_five");
				$this->assertEquals(5, $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>"five", "get"=>"id_5");
				$this->assertEquals("five", $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>5,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);
				return false;
			}
		}
	}

	function test_dbFetchToken_Single() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}

		// load db then empty cache
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}
		try{
		    	$result = $this->cls->flushCache();
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}

		// test single fetch
		// ======================================================
		try{
			$fetch_values = array("exp"=>array("one"=>1), "fetch"=>"one");
			$this->assertEquals(array("one"=>1),$this->cls->dbFetchToken("one") );

			$fetch_values = array("exp"=>array("two"=>2), "fetch"=>"two");
			$this->assertEquals(array("two"=>2),$this->cls->dbFetchToken("two") );

			$fetch_values = array("exp"=>array("three"=>3), "fetch"=>"three");
			$this->assertEquals(array("three"=>3),$this->cls->dbFetchToken("three") );

			$fetch_values = array("exp"=>array("four"=>4), "fetch"=>"four");
			$this->assertEquals(array("four"=>4),$this->cls->dbFetchToken("four") );

			$fetch_values = array("exp"=>array("five"=>5), "fetch"=>"five");
			$this->assertEquals(array("five"=>5),$this->cls->dbFetchToken("five") );
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>5,
					'text'=>"dbfetchToken exception",
					'data'=>array( "fetch values"=>$fetch_values),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}

		// Check class cache
		// ======================================================
		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);

		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);

		$this->assertEquals(array_flip($exp_array), $this->cls->cache["ids"]);



		//Check persistent cache
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>1, "get"=>"token_one");
				$this->assertEquals(1, $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>"one", "get"=>"id_1");
				$this->assertEquals("one", $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>2, "get"=>"token_two");
				$this->assertEquals(2, $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>"two", "get"=>"id_2");
				$this->assertEquals("two", $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>3, "get"=>"token_three");
				$this->assertEquals(3, $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>"three", "get"=>"id_3");
				$this->assertEquals("three", $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>4, "get"=>"token_four");
				$this->assertEquals(4, $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>"four", "get"=>"id_4");
				$this->assertEquals("four", $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>5, "get"=>"token_five");
				$this->assertEquals(5, $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>"five", "get"=>"id_5");
				$this->assertEquals("five", $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>5,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);
				return false;
			}
		}



	}


	function test_dbFetchToken_Multi() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}

		// load db then empty cache
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}
		try{
		    $result = $this->cls->flushCache();
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}

		// test Multiple fetch
		// ======================================================


		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);

		try{
			$this->assertEquals($exp_array, $this->cls->dbFetchToken($add_tokens));
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>5,
					'text'=>"dbfetchToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}


		//check class cache
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		$this->assertEquals(array_flip($exp_array), $this->cls->cache["ids"]);


		//Check persistent cache
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>1, "get"=>"token_one");
				$this->assertEquals(1, $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>"one", "get"=>"id_1");
				$this->assertEquals("one", $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>2, "get"=>"token_two");
				$this->assertEquals(2, $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>"two", "get"=>"id_2");
				$this->assertEquals("two", $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>3, "get"=>"token_three");
				$this->assertEquals(3, $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>"three", "get"=>"id_3");
				$this->assertEquals("three", $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>4, "get"=>"token_four");
				$this->assertEquals(4, $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>"four", "get"=>"id_4");
				$this->assertEquals("four", $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>5, "get"=>"token_five");
				$this->assertEquals(5, $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>"five", "get"=>"id_5");
				$this->assertEquals("five", $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>5,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);
				return false;
			}
		}


	}

	function test_dbFetchId_Single() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);
			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db then empty cache
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}
		try{
		    $result = $this->cls->flushCache();
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// test single fetch
		// ======================================================

		try{
			$fetch_value= array("exp"=>array(1=>"one"), "fetch"=>1);
			$this->assertEquals(array(1=>"one"),$this->cls->dbFetchId(1) );

			$fetch_value= array("exp"=>array(2=>"two"), "fetch"=>2);
			$this->assertEquals(array(2=>"two"),$this->cls->dbFetchId(2) );

			$fetch_value= array("exp"=>array(3=>"three"), "fetch"=>3);
			$this->assertEquals(array(3=>"three"),$this->cls->dbFetchId(3) );

			$fetch_value= array("exp"=>array(4=>"four"), "fetch"=>4);
			$this->assertEquals(array(4=>"four"),$this->cls->dbFetchId(4) );

			$fetch_value= array("exp"=>array(5=>"five"), "fetch"=>5);
			$this->assertEquals(array(5=>"five"),$this->cls->dbFetchId(5) );
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>5,
					'text'=>"dbfetchToken exception",
					'data'=>array( "fetch values"=>$fetch_values),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		//check class cache
		// ======================================================
		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		$this->assertEquals(array_flip($exp_array), $this->cls->cache["ids"]);


		//Check persistent cache
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>1, "get"=>"token_one");
				$this->assertEquals(1, $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>"one", "get"=>"id_1");
				$this->assertEquals("one", $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>2, "get"=>"token_two");
				$this->assertEquals(2, $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>"two", "get"=>"id_2");
				$this->assertEquals("two", $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>3, "get"=>"token_three");
				$this->assertEquals(3, $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>"three", "get"=>"id_3");
				$this->assertEquals("three", $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>4, "get"=>"token_four");
				$this->assertEquals(4, $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>"four", "get"=>"id_4");
				$this->assertEquals("four", $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>5, "get"=>"token_five");
				$this->assertEquals(5, $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>"five", "get"=>"id_5");
				$this->assertEquals("five", $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>5,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}
	}

	function test_dbFetchId_Multi() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db then empty cache
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}
		try{
		    $result = $this->cls->flushCache();
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}


		// Test multi fetch
		// ======================================================

		$add_ids = array(1,2,3,4,5);
		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
		try{
		$this->assertEquals(array_flip($exp_array), $this->cls->dbFetchId($add_ids));
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>5,
					'text'=>"dbfetchToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}


		//check class cache
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		$this->assertEquals(array_flip($exp_array), $this->cls->cache["ids"]);


		//Check persistent cache
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>1, "get"=>"token_one");
				$this->assertEquals(1, $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>"one", "get"=>"id_1");
				$this->assertEquals("one", $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>2, "get"=>"token_two");
				$this->assertEquals(2, $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>"two", "get"=>"id_2");
				$this->assertEquals("two", $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>3, "get"=>"token_three");
				$this->assertEquals(3, $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>"three", "get"=>"id_3");
				$this->assertEquals("three", $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>4, "get"=>"token_four");
				$this->assertEquals(4, $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>"four", "get"=>"id_4");
				$this->assertEquals("four", $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>5, "get"=>"token_five");
				$this->assertEquals(5, $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>"five", "get"=>"id_5");
				$this->assertEquals("five", $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>5,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}

	}

	function test_cacheFetchToken_Single() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		//Check persistent cache
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_fetch = array("exp"=>array("one"=>1), "fetch"=>"one");
				$this->assertEquals(array("one"=>1), $this->cls->cacheFetchToken("one") );

				$cache_fetch = array("exp"=>array("two"=>2), "fetch"=>"two");
				$this->assertEquals(array("two"=>2), $this->cls->cacheFetchToken("two") );

				$cache_fetch = array("exp"=>array("three"=>3), "fetch"=>"three");
				$this->assertEquals(array("three"=>3), $this->cls->cacheFetchToken("three") );

				$cache_fetch = array("exp"=>array("four"=>4), "fetch"=>"four");
				$this->assertEquals(array("four"=>4), $this->cls->cacheFetchToken("four") );

				$cache_fetch = array("exp"=>array("five"=>5), "fetch"=>"five");
				$this->assertEquals(array("five"=>5), $this->cls->cacheFetchToken("five") );

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>5,
						'text'=>"cacheFetchToken exception",
						'data'=>array("cache_values"=>$cache_fetch),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}

	}

	function test_cacheFetchToken_Multi() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Test cacheFetchToken
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{

				$exp_array  = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);

	    			$this->assertEquals($exp_array, $this->cls->cacheFetchToken($add_tokens) );

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>4,
						'text'=>"CacheFetchToken exception",
						'data'=>array("tokens"=>$add_tokens),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}

	}

	function test_cacheFetchId_Single() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		//Check persistent cache
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_fetch = array("exp"=>array(1=>"one"), "fetch"=>1);
				$this->assertEquals(array(1=>"one"), $this->cls->cacheFetchToken(1) );

				$cache_fetch = array("exp"=>array(2=>"two"), "fetch"=>2);
				$this->assertEquals(array(2=>"two"), $this->cls->cacheFetchToken(2) );

				$cache_fetch = array("exp"=>array(3=>"three"), "fetch"=>3);
				$this->assertEquals(array(3=>"three"), $this->cls->cacheFetchToken(3) );

				$cache_fetch = array("exp"=>array(4=>"four"), "fetch"=>4);
				$this->assertEquals(array(4=>"four"), $this->cls->cacheFetchToken(4) );

				$cache_fetch = array("exp"=>array(5=>"five"), "fetch"=>5);
				$this->assertEquals(array(5=>"five"), $this->cls->cacheFetchToken(5) );

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>4,
						'text'=>"cacheFetchId exception",
						'data'=>array("cache_values"=>$cache_fetch),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}
	}

	function test_cacheFetchId_Multi() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check persistent cache
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{

				$ids = array(1, 2, 3, 4, 5);
				$exp_array = array (1=>"one", 2=> "two", 3=> "three", 4=>"four", 5=> "five");
				$this->assertEquals($exp_array ,$this->cls->cacheFetchId($ids));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>4,
						'text'=>"cacheFetchId exception",
						'data'=>array("cache_values"=>$ids),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}

	}

	function test_getToken_Single_Class() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		//test single class cache get
		// ======================================================
		try{
			$get_token= array("exp"=>1, "get"=>"one");
			$this->assertEquals(1,$this->cls->getToken("one"));

			$get_token= array("exp"=>2, "get"=>"two");
			$this->assertEquals(2,$this->cls->getToken("two"));

			$get_token= array("exp"=>3, "get"=>"three");
			$this->assertEquals(3,$this->cls->getToken("three"));

			$get_token= array("exp"=>4, "get"=>"four");
			$this->assertEquals(4,$this->cls->getToken("four"));

			$get_token= array("exp"=>5, "get"=>"five");
			$this->assertEquals(5,$this->cls->getToken("five"));
		}
		catch(FOX_exception $child){

			throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"getToken exception",
					'data'=>array( "getToken"=>$get_token),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}
	}

	function test_getToken_Multi_Class() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Test Multi Class Cache getToken
		// ======================================================
		try{
			$exp_array  = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
			$this->assertEquals($exp_array, $this->cls->getToken($add_tokens));
		}
		catch(FOX_exception $child){

			throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"getToken exception",
					'data'=>array( "add tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}
	}

	function test_getToken_Single_Persistent() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Test single persistent cache get
		// ======================================================

		//clear class cache
		$this->cls->cache = array( );

		try{
			$get_token= array("exp"=>1, "get"=>"one");
			$this->assertEquals(1,$this->cls->getToken("one"));

			$get_token= array("exp"=>2, "get"=>"two");
			$this->assertEquals(2,$this->cls->getToken("two"));

			$get_token= array("exp"=>3, "get"=>"three");
			$this->assertEquals(3,$this->cls->getToken("three"));

			$get_token= array("exp"=>4, "get"=>"four");
			$this->assertEquals(4,$this->cls->getToken("four"));

			$get_token= array("exp"=>5, "get"=>"five");
			$this->assertEquals(5,$this->cls->getToken("five"));
		}
		catch(FOX_exception $child){

			throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"getToken exception",
					'data'=>array( "getToken"=>$get_token),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check that class cache has been rebuilt
		// ======================================================
		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		$this->assertEquals(array_flip($exp_array), $this->cls->cache["ids"]);
	}

	function test_getToken_Multi_Persistent() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Test Multi Persistent Cache get
		// ======================================================
		try{
			$exp_array  = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
			$this->assertEquals($exp_array, $this->cls->getToken($add_tokens));
		}
		catch(FOX_exception $child){

			throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"getToken exception",
					'data'=>array( "add tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check that class cache has been rebuilt
		// ======================================================
		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		$this->assertEquals(array_flip($exp_array), $this->cls->cache["ids"]);
	}

	function test_getToken_Single_Db() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db and flush cache
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Test single db get
		// ======================================================

		try{
			$get_token= array("exp"=>1, "get"=>"one");
			$this->assertEquals(1,$this->cls->getToken("one"));

			$get_token= array("exp"=>2, "get"=>"two");
			$this->assertEquals(2,$this->cls->getToken("two"));

			$get_token= array("exp"=>3, "get"=>"three");
			$this->assertEquals(3,$this->cls->getToken("three"));

			$get_token= array("exp"=>4, "get"=>"four");
			$this->assertEquals(4,$this->cls->getToken("four"));

			$get_token= array("exp"=>5, "get"=>"five");
			$this->assertEquals(5,$this->cls->getToken("five"));
		}
		catch(FOX_exception $child){

			throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"getToken exception",
					'data'=>array( "getToken"=>$get_token),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check that class cache has been rebuilt
		// ======================================================
		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		$this->assertEquals(array_flip($exp_array), $this->cls->cache["ids"]);

		// Check that persistent cache has been rebuilt
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>1, "get"=>"token_one");
				$this->assertEquals(1, $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>"one", "get"=>"id_1");
				$this->assertEquals("one", $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>2, "get"=>"token_two");
				$this->assertEquals(2, $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>"two", "get"=>"id_2");
				$this->assertEquals("two", $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>3, "get"=>"token_three");
				$this->assertEquals(3, $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>"three", "get"=>"id_3");
				$this->assertEquals("three", $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>4, "get"=>"token_four");
				$this->assertEquals(4, $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>"four", "get"=>"id_4");
				$this->assertEquals("four", $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>5, "get"=>"token_five");
				$this->assertEquals(5, $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>"five", "get"=>"id_5");
				$this->assertEquals("five", $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>5,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}

	}

	function test_getToken_Multi_Db() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db and flush cache
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Test Multi Db get
		// ======================================================
		try{
			$exp_array  = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
			$this->assertEquals($exp_array, $this->cls->getToken($add_tokens));
		}
		catch(FOX_exception $child){

			throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"getToken exception",
					'data'=>array( "add tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check that class cache has been rebuilt
		// ======================================================
		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		$this->assertEquals(array_flip($exp_array), $this->cls->cache["ids"]);

		// Check that persistent cache has been rebuilt
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>1, "get"=>"token_one");
				$this->assertEquals(1, $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>"one", "get"=>"id_1");
				$this->assertEquals("one", $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>2, "get"=>"token_two");
				$this->assertEquals(2, $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>"two", "get"=>"id_2");
				$this->assertEquals("two", $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>3, "get"=>"token_three");
				$this->assertEquals(3, $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>"three", "get"=>"id_3");
				$this->assertEquals("three", $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>4, "get"=>"token_four");
				$this->assertEquals(4, $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>"four", "get"=>"id_4");
				$this->assertEquals("four", $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>5, "get"=>"token_five");
				$this->assertEquals(5, $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>"five", "get"=>"id_5");
				$this->assertEquals("five", $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>5,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}
	}

	function test_getToken_Single_Insert() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Test single insert and get
		// ======================================================

		try{
			$get_token= array("exp"=>1, "get"=>"one");
			$this->assertEquals(1,$this->cls->getToken("one"));

			$get_token= array("exp"=>2, "get"=>"two");
			$this->assertEquals(2,$this->cls->getToken("two"));

			$get_token= array("exp"=>3, "get"=>"three");
			$this->assertEquals(3,$this->cls->getToken("three"));

			$get_token= array("exp"=>4, "get"=>"four");
			$this->assertEquals(4,$this->cls->getToken("four"));

			$get_token= array("exp"=>5, "get"=>"five");
			$this->assertEquals(5,$this->cls->getToken("five"));
		}
		catch(FOX_exception $child){

			throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"getToken exception",
					'data'=>array( "getToken"=>$get_token),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check that class cache has been rebuilt
		// ======================================================
		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		$this->assertEquals(array_flip($exp_array), $this->cls->cache["ids"]);

		// Check that persistent cache has been rebuilt
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>1, "get"=>"token_one");
				$this->assertEquals(1, $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>"one", "get"=>"id_1");
				$this->assertEquals("one", $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>2, "get"=>"token_two");
				$this->assertEquals(2, $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>"two", "get"=>"id_2");
				$this->assertEquals("two", $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>3, "get"=>"token_three");
				$this->assertEquals(3, $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>"three", "get"=>"id_3");
				$this->assertEquals("three", $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>4, "get"=>"token_four");
				$this->assertEquals(4, $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>"four", "get"=>"id_4");
				$this->assertEquals("four", $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>5, "get"=>"token_five");
				$this->assertEquals(5, $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>"five", "get"=>"id_5");
				$this->assertEquals("five", $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>5,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}

	}

	function test_getToken_Multi_Insert() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Test multi insert and  get
		// ======================================================
		try{
			$exp_array  = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
			$this->assertEquals($exp_array, $this->cls->getToken($add_tokens));
		}
		catch(FOX_exception $child){

			throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"getToken exception",
					'data'=>array( "add tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check that class cache has been rebuilt
		// ======================================================
		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		$this->assertEquals(array_flip($exp_array), $this->cls->cache["ids"]);

		// Check that persistent cache has been rebuilt
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>1, "get"=>"token_one");
				$this->assertEquals(1, $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>"one", "get"=>"id_1");
				$this->assertEquals("one", $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>2, "get"=>"token_two");
				$this->assertEquals(2, $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>"two", "get"=>"id_2");
				$this->assertEquals("two", $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>3, "get"=>"token_three");
				$this->assertEquals(3, $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>"three", "get"=>"id_3");
				$this->assertEquals("three", $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>4, "get"=>"token_four");
				$this->assertEquals(4, $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>"four", "get"=>"id_4");
				$this->assertEquals("four", $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>5, "get"=>"token_five");
				$this->assertEquals(5, $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>"five", "get"=>"id_5");
				$this->assertEquals("five", $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>5,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}
	}

	function test_getToken_Mixed_Insert_Get() {

		// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db with first two tokens
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$this->cls->addToken($add_tokens[0]);
			$this->cls->addToken($add_tokens[1]);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Test multi mixed insert and get
		// ======================================================
		try{
			$exp_array  = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
			$this->assertEquals($exp_array, $this->cls->getToken($add_tokens));
		}
		catch(FOX_exception $child){

			throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"getToken exception",
					'data'=>array( "add tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check that class cache has been rebuilt
		// ======================================================
		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		$this->assertEquals(array_flip($exp_array), $this->cls->cache["ids"]);

		// Check that persistent cache has been rebuilt
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>1, "get"=>"token_one");
				$this->assertEquals(1, $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>"one", "get"=>"id_1");
				$this->assertEquals("one", $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>2, "get"=>"token_two");
				$this->assertEquals(2, $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>"two", "get"=>"id_2");
				$this->assertEquals("two", $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>3, "get"=>"token_three");
				$this->assertEquals(3, $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>"three", "get"=>"id_3");
				$this->assertEquals("three", $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>4, "get"=>"token_four");
				$this->assertEquals(4, $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>"four", "get"=>"id_4");
				$this->assertEquals("four", $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>5, "get"=>"token_five");
				$this->assertEquals(5, $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>"five", "get"=>"id_5");
				$this->assertEquals("five", $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>5,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}
	}

	function test_getId_Single_Class(){

	    	// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		//test single class cache get
		// ======================================================
		try{

			$get_id= array("exp"=>"one", "get"=>1);
			$this->assertEquals("one",$this->cls->getId(1));

			$get_id= array("exp"=>"two", "get"=>2);
			$this->assertEquals("two",$this->cls->getId(2));

			$get_id= array("exp"=>"three", "get"=>3);
			$this->assertEquals("three",$this->cls->getId(3));

			$get_id= array("exp"=>"four", "get"=>4);
			$this->assertEquals("four",$this->cls->getId(4));

			$get_id= array("exp"=>"five", "get"=>5);
			$this->assertEquals("five",$this->cls->getId(5));

		}
		catch(FOX_exception $child){

			throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"getId exception",
					'data'=>array( "getId"=>$get_id),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

	}

	function test_getId_Multi_Class(){

	    	// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}


		// Test Multi Class Cache getId
		// ======================================================
		try{
			$get_ids = array(1, 2, 3, 4, 5);
			$exp_array  = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
			$this->assertEquals($exp_array, $this->cls->getId($get_ids));
		}
		catch(FOX_exception $child){

			throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"getId exception",
					'data'=>array( "get ids"=>$get_ids),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}
	}

	function test_getId_Single_Persistent(){

	    	// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Clear class cache
		// ======================================================
		$this->cls->cache = array( );

		//test single class cache get
		// ======================================================
		try{

			$get_id= array("exp"=>"one", "get"=>1);
			$this->assertEquals("one",$this->cls->getId(1));

			$get_id= array("exp"=>"two", "get"=>2);
			$this->assertEquals("two",$this->cls->getId(2));

			$get_id= array("exp"=>"three", "get"=>3);
			$this->assertEquals("three",$this->cls->getId(3));

			$get_id= array("exp"=>"four", "get"=>4);
			$this->assertEquals("four",$this->cls->getId(4));

			$get_id= array("exp"=>"five", "get"=>5);
			$this->assertEquals("five",$this->cls->getId(5));

		}
		catch(FOX_exception $child){

			throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"getId exception",
					'data'=>array( "getId"=>$get_id),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check that class cache has been rebuilt
		// ======================================================
		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		$this->assertEquals(array_flip($exp_array), $this->cls->cache["ids"]);

	}

	function test_getId_Multi_Persistent(){

	    	// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Clear class cache
		// ======================================================
		$this->cls->cache = array( );

		// Test Multi Class Cache getId
		// ======================================================
		try{
			$get_ids = array(1, 2, 3, 4, 5);
			$exp_array  = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
			$this->assertEquals($exp_array, $this->cls->getId($get_ids));
		}
		catch(FOX_exception $child){

			throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"getId exception",
					'data'=>array( "get ids"=>$get_ids),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check that class cache has been rebuilt
		// ======================================================
		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		$this->assertEquals(array_flip($exp_array), $this->cls->cache["ids"]);

	}

	function test_getId_Single_Db(){

	    	// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db and flush cache
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		//test single class cache get
		// ======================================================
		try{

			$get_id= array("exp"=>"one", "get"=>1);
			$this->assertEquals("one",$this->cls->getId(1));

			$get_id= array("exp"=>"two", "get"=>2);
			$this->assertEquals("two",$this->cls->getId(2));

			$get_id= array("exp"=>"three", "get"=>3);
			$this->assertEquals("three",$this->cls->getId(3));

			$get_id= array("exp"=>"four", "get"=>4);
			$this->assertEquals("four",$this->cls->getId(4));

			$get_id= array("exp"=>"five", "get"=>5);
			$this->assertEquals("five",$this->cls->getId(5));

		}
		catch(FOX_exception $child){

			throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"getId exception",
					'data'=>array( "getId"=>$get_id),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check that class cache has been rebuilt
		// ======================================================
		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		$this->assertEquals(array_flip($exp_array), $this->cls->cache["ids"]);

		// Check that persistent cache has been rebuilt
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>1, "get"=>"token_one");
				$this->assertEquals(1, $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>"one", "get"=>"id_1");
				$this->assertEquals("one", $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>2, "get"=>"token_two");
				$this->assertEquals(2, $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>"two", "get"=>"id_2");
				$this->assertEquals("two", $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>3, "get"=>"token_three");
				$this->assertEquals(3, $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>"three", "get"=>"id_3");
				$this->assertEquals("three", $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>4, "get"=>"token_four");
				$this->assertEquals(4, $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>"four", "get"=>"id_4");
				$this->assertEquals("four", $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>5, "get"=>"token_five");
				$this->assertEquals(5, $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>"five", "get"=>"id_5");
				$this->assertEquals("five", $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>5,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}

	}

	function test_getId_Multi_Db(){

	    	// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db and flush cache
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Test Multi Class Cache getId
		// ======================================================
		try{
			$get_ids = array(1, 2, 3, 4, 5);
			$exp_array  = array(1=>"one", 2=>"two", 3=>"three", 4=>"four", 5=>"five");
			$this->assertEquals($exp_array, $this->cls->getId($get_ids));
		}
		catch(FOX_exception $child){

			throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"getId exception",
					'data'=>array( "get ids"=>$get_ids),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check that class cache has been rebuilt
		// ======================================================
		$exp_array = array("one"=>1, "two"=>2, "three"=>3, "four"=>4, "five"=>5);
		$this->assertEquals($exp_array, $this->cls->cache["tokens"]);
		$this->assertEquals(array_flip($exp_array), $this->cls->cache["ids"]);

		// Check that persistent cache has been rebuilt
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>1, "get"=>"token_one");
				$this->assertEquals(1, $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>"one", "get"=>"id_1");
				$this->assertEquals("one", $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>2, "get"=>"token_two");
				$this->assertEquals(2, $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>"two", "get"=>"id_2");
				$this->assertEquals("two", $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>3, "get"=>"token_three");
				$this->assertEquals(3, $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>"three", "get"=>"id_3");
				$this->assertEquals("three", $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>4, "get"=>"token_four");
				$this->assertEquals(4, $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>"four", "get"=>"id_4");
				$this->assertEquals("four", $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>5, "get"=>"token_five");
				$this->assertEquals(5, $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>"five", "get"=>"id_5");
				$this->assertEquals("five", $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>5,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}

	}

	function test_dropAll() {

	    	// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Test dropAll
		// ======================================================
		try{
			$this->cls->dropAll();
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"dropAll exception",
					'data'=>null,
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}


		$db = new FOX_db();

		// Check Db has no rows
		// ======================================================
		try{
			$this->assertEquals( "0", $db->runSelectQuery(FOX_test_dictionary::$struct, $args=null, $columns=null, $ctrl= array( "count"=>true, "format"=>"var"), $error));
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>5,
					'text'=>"DB select exception",
					'data'=>array("ctrl"=>array( "count"=>true, "format"=>"var")),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check Class cache is empty
		$this->assertEquals( array(), $this->cls->cache);

		// Check that persistent cache is empty
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>array(), "get"=>"token_one");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_1");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>array(), "get"=>"token_two");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_2");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>array(), "get"=>"token_three");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_3");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>array(), "get"=>"token_four");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_4");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>array(), "get"=>"token_five");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_5");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>6,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}
	}

	function test_dropToken_Single() {

	    	// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Test  single dropToken
		// ======================================================

		try{
			$drop_values = array("exp"=>1, "token"=>"one");
			$this->assertEquals(1, $this->cls->dropToken("one"));

			$drop_values = array("exp"=>1, "token"=>"two");
			$this->assertEquals(1, $this->cls->dropToken("two"));

			$drop_values = array("exp"=>1, "token"=>"three");
			$this->assertEquals(1, $this->cls->dropToken("three"));

			$drop_values = array("exp"=>1, "token"=>"four");
			$this->assertEquals(1, $this->cls->dropToken("four"));

			$drop_values = array("exp"=>1, "token"=>"five");
			$this->assertEquals(1, $this->cls->dropToken("five"));

		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"dropToken exception",
					'data'=>array( "drop values"=>$drop_values),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check Db has no rows
		// ======================================================
		try{
			$this->assertEquals( "0", $db->runSelectQuery(FOX_test_dictionary::$struct, $args=null, $columns=null, $ctrl= array( "count"=>true, "format"=>"var"), $error));
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>5,
					'text'=>"DB select exception",
					'data'=>array("ctrl"=>array( "count"=>true, "format"=>"var")),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check Class cache is empty
		$this->assertEquals( array(), $this->cls->cache);

		// Check that persistent cache is empty
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>array(), "get"=>"token_one");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_1");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>array(), "get"=>"token_two");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_2");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>array(), "get"=>"token_three");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_3");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>array(), "get"=>"token_four");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_4");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>array(), "get"=>"token_five");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_5");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>6,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}

	}

	function test_dropToken_Multi() {

	    	// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Test  single dropToken
		// ======================================================

		try{
			$this->assertEquals(1, $this->cls->dropToken($add_tokens));

		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"dropToken exception",
					'data'=>array( "add tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check Db has no rows
		// ======================================================
		try{
			$this->assertEquals( "0", $db->runSelectQuery(FOX_test_dictionary::$struct, $args=null, $columns=null, $ctrl= array( "count"=>true, "format"=>"var"), $error));
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>5,
					'text'=>"DB select exception",
					'data'=>array("ctrl"=>array( "count"=>true, "format"=>"var")),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check Class cache is empty
		$this->assertEquals( array(), $this->cls->cache);

		// Check that persistent cache is empty
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>array(), "get"=>"token_one");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_1");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>array(), "get"=>"token_two");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_2");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>array(), "get"=>"token_three");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_3");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>array(), "get"=>"token_four");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_4");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>array(), "get"=>"token_five");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_5");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>6,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}

	}

	function test_dropId_Single() {

	    	// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Test  single dropId
		// ======================================================

		try{
			$drop_values = array("exp"=>1, "id"=>1);
			$this->assertEquals(1, $this->cls->dropId(1));

			$drop_values = array("exp"=>1, "id"=>2);
			$this->assertEquals(1, $this->cls->dropId(2));

			$drop_values = array("exp"=>1, "id"=>3);
			$this->assertEquals(1, $this->cls->dropId(3));

			$drop_values = array("exp"=>1, "id"=>4);
			$this->assertEquals(1, $this->cls->dropId(4));

			$drop_values = array("exp"=>1, "id"=>"5");
			$this->assertEquals(1, $this->cls->dropId(5));

		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"dropId exception",
					'data'=>array( "drop values"=>$drop_values),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check Db has no rows
		// ======================================================
		try{
			$this->assertEquals( "0", $db->runSelectQuery(FOX_test_dictionary::$struct, $args=null, $columns=null, $ctrl= array( "count"=>true, "format"=>"var"), $error));
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>5,
					'text'=>"DB select exception",
					'data'=>array("ctrl"=>array( "count"=>true, "format"=>"var")),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check Class cache is empty
		$this->assertEquals( array(), $this->cls->cache);

		// Check that persistent cache is empty
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>array(), "get"=>"token_one");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_1");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>array(), "get"=>"token_two");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_2");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>array(), "get"=>"token_three");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_3");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>array(), "get"=>"token_four");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_4");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>array(), "get"=>"token_five");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_5");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>6,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}

	}

	function test_dropId_Multi() {

	    	// Clear db and cache
		// ======================================================
		try{
			$result = $this->cls->truncate();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>1,
					'text'=>"Class truncate exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		try{
			$result = $this->cls->flushCache();
			$this->assertEquals(true, $result);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>2,
					'text'=>"Class flushCache exception",
					'data'=>array( "class name"=>get_class($this->cls)),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// load db
		// ======================================================

		$add_tokens = array("one", "two", "three", "four", "five");

		try{
			$add_result = $this->cls->addToken($add_tokens);
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>3,
					'text'=>"addToken exception",
					'data'=>array( "tokens"=>$add_tokens),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Test  single dropId
		// ======================================================

		try{
			$drop_ids = array(1, 2,3,4,5);
			$this->assertEquals(5, $this->cls->dropId($drop_ids));

		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>4,
					'text'=>"dropId exception",
					'data'=>array( "drop ids"=>$drop_ids),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check Db has no rows
		// ======================================================
		try{
			$this->assertEquals( "0", $db->runSelectQuery(FOX_test_dictionary::$struct, $args=null, $columns=null, $ctrl= array( "count"=>true, "format"=>"var"), $error));
		}
		catch(FOX_exception $child){
		    	throw new FOX_exception(
				    array(
					'numeric'=>5,
					'text'=>"DB select exception",
					'data'=>array("ctrl"=>array( "count"=>true, "format"=>"var")),
					'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
					'child'=>$child
				    )
			);

			return false;
		}

		// Check Class cache is empty
		$this->assertEquals( array(), $this->cls->cache);

		// Check that persistent cache is empty
		// ======================================================
		global $fox;
		$struct = $this->cls->_struct();
		if($fox->cache->isActive()) {

			try{
				$cache_values = array("exp"=>array(), "get"=>"token_one");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_one" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_1");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_1" ));

				$cache_values = array("exp"=>array(), "get"=>"token_two");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_two" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_2");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_2" ));

				$cache_values = array("exp"=>array(), "get"=>"token_three");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_three" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_3");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_3" ));

				$cache_values = array("exp"=>array(), "get"=>"token_four");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_four" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_4");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_4" ));

				$cache_values = array("exp"=>array(), "get"=>"token_five");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"token_five" ) );

				$cache_values = array("exp"=>array(), "get"=>"id_5");
				$this->assertEquals(array(), $fox->cache->get($struct["cache_namespace"],"id_5" ));

			}
			catch(FOX_exception $child){
				throw new FOX_exception(
					    array(
						'numeric'=>6,
						'text'=>"Cache get exception",
						'data'=>array("cache_values"=>$cache_values),
						'file'=>__FILE__, 'class'=>__CLASS__, 'method'=>__METHOD__, 'line'=>__LINE__, 
						'child'=>$child
					    )
				);

				return false;
			}
		}

	}


	function tearDown() {

		$this->cls->uninstall();

		parent::tearDown();
	}


}



?>
