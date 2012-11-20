<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - SYSTEM UTILS CLASS
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package FoxFire
 * @subpackage Unit Test
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

class utils_system extends RAZ_testCase {


    	function setUp() {

		parent::setUp();
	}


	function test_arrayPrune() {


		$source_data = array (

			"1"=>array(
				"1"=>array(),
				"2"=>array(
					"1"=>array(
						"A"=>"test_val",
						"B"=>"test_val",
					),
					"2"=>array(
						"A"=>"test_val",
						"B"=>"test_val",
					)
				)
			),
			"2"=>array(
				"2"=>array(
					"1"=>array()
				)
			),
			"3"=>array(
				"1"=>array(
					"2"=>array(
						"A"=>"test_val"
					),
					"3"=>array(
						"C"=>"",
					),
					"4"=>array(
						"C"=>"test_val",
					)
				),

			)

		);


		$check_data = array (

			"1"=>array(
				"2"=>array(
					"1"=>array(
						"A"=>"test_val",
						"B"=>"test_val",
					),
					"2"=>array(
						"A"=>"test_val",
						"B"=>"test_val",
					)
				)
			),
			"3"=>array(
				"1"=>array(
					"2"=>array(
						"A"=>"test_val"
					),
					"3"=>array(
						"C"=>"",
					),
					"4"=>array(
						"C"=>"test_val",
					)
				),

			)

		);

		$result = FOX_sUtil::arrayPrune($source_data, 3);

		$this->assertEquals($check_data, $result);


	}


	function test_keyIntersect() {


		$master= array(

			    "a"=>array(

				    "q"=>array(
						0=>1,
						1=>2,
						2=>3
				    ),
				    "r"=>null,
				    "s"=>array(),
				    "t"=>0,
				    "1"=>"test_1",
				    "2"=>"test_2",
				    3=>false,
				    4=>array(
						0=>"1",
						1=>"2",
						2=>3
				    )
			     ),
			    "b"=>array(

				    "u"=>array(
						0=>1,
						1=>2,
						2=>3
				    ),
				    "v"=>null,
				    "w"=>array(),
				    "x"=>"test_4",
				    "1"=>"test_5",
				    "2"=>false,
				    3=>0,
				    4=>array(
						0=>"1",
						1=>"2",
						2=>3
				    )
			     ),
			     "1"=>"foo",
			     "2"=>"bar",
			     3=>"baz",
			     4=>"taz"
		);

		$slave = array(

			    "a"=>array(

				    "q"=>array(
						0=>1,
						1=>3
				    ),
				    "r"=>"fail_1",
				    "s"=>array(),
				    "t"=>"fail_2",
				    "2"=>"fail_3",
				    "3"=>"fail_4",
				    4=>array(
						0=>"1",
						1=>"2",
						2=>3
				    )
			     ),
			    "z"=>array(

				    "u"=>array(
						0=>1,
						1=>2,
						2=>3
				     ),
				    "v"=>"fail_5",
				    "w"=>array(),
				    "x"=>"fail_6",
				    "1"=>"fail_7",
				    "2"=>"fail_8",
				    3=>"fail_9",
				    4=>array("1","2",3)
			     ),
			     "1"=>"qfq",
			     2=>"rtz",
			     3=>"fqx",
			     "4"=>"trw"
		);

		// When an intersect happens, the values from the $master array
		// will be copied to $result

		$check = array(

			    "a"=>array(

				    "q"=>array(
						0=>1,
						1=>2
				     ),
				    "r"=>null,
				    "s"=>array(),
				    "t"=>0,
				    "2"=>"test_2",
				    "3"=>false,
				    4=>array(
						0=>"1",
						1=>"2",
						2=>3
				    )
			     ),
			     1=>"foo",
			     2=>"bar",
			     3=>"baz",
			     4=>"taz"
		);


		$result = FOX_sUtil::keyIntersect($master, $slave, $error);

		$this->assertEquals($check, $result, FOX_debug::formatError_print($error));
		unset($error);


	}

	function test_loftHierarchy() {

		$nodes = array(
				"1"=>array("slug"=>"aaa", "parent"=>0),
				"2"=>array("slug"=>"bbb", "parent"=>0),
				"3"=>array("slug"=>"ccc", "parent"=>0),
				"4"=>array("slug"=>"ddd", "parent"=>0),
				"5"=>array("slug"=>"eee", "parent"=>2),
				"6"=>array("slug"=>"fff", "parent"=>2),
				"7"=>array("slug"=>"ggg", "parent"=>2),
				"8"=>array("slug"=>"hhh", "parent"=>3),
				"9"=>array("slug"=>"iii", "parent"=>3),
				"10"=>array("slug"=>"jjj", "parent"=>3),
				"11"=>array("slug"=>"hhh", "parent"=>4),
				"12"=>array("slug"=>"iii", "parent"=>4),
				"13"=>array("slug"=>"jjj", "parent"=>4),
				"14"=>array("slug"=>"aaa", "parent"=>5),
				"15"=>array("slug"=>"bbb", "parent"=>5),
				"16"=>array("slug"=>"ccc", "parent"=>5),
				"17"=>array("slug"=>"xxx", "parent"=>15),
				"18"=>array("slug"=>"yyy", "parent"=>16),
				"19"=>array("slug"=>"zzz", "parent"=>16)
		);

		$check = array(	"children"=>array(
					    "1"=>array( "slug"=>"aaa",
							"parent"=>0,
							"node_id"=>1,
					    ),
					    "2"=>array( "slug"=>"bbb",
							"parent"=>0,
							"node_id"=>2,
							"children"=>array(
								"5"=>array( "slug"=>"eee",
									    "parent"=>2,
									    "node_id"=>5,
									    "children"=>array(
										    "14"=>array("slug"=>"aaa",
												"parent"=>5,
												"node_id"=>14
										    ),
										    "15"=>array("slug"=>"bbb",
												"parent"=>5,
												"node_id"=>15,
												"children"=>array(
														    "17"=>array("slug"=>"xxx",
																"parent"=>15,
																"node_id"=>17
														    )
												)
										    ),
										    "16"=>array("slug"=>"ccc",
												"parent"=>5,
												"node_id"=>16,
												"children"=>array(
														    "18"=>array("slug"=>"yyy",
																"parent"=>16,
																"node_id"=>18
														    ),
														    "19"=>array("slug"=>"zzz",
																"parent"=>16,
																"node_id"=>19
														    )
												)
										    )
									    )
									 ),
								"6"=>array( "slug"=>"fff",
									    "parent"=>2,
									    "node_id"=>6),
								"7"=>array(
									    "slug"=>"ggg",
									    "parent"=>2,
									    "node_id"=>7,)
							)
					    ),
					    "3"=>array( "slug"=>"ccc",
							"parent"=>0,
							"node_id"=>3,
							"children"=>array(
								"8"=>array( "slug"=>"hhh",
									    "parent"=>3,
									    "node_id"=>8),
								"9"=>array( "slug"=>"iii",
									    "parent"=>3,
									    "node_id"=>9),
								"10"=>array("slug"=>"jjj",
									    "parent"=>3,
									    "node_id"=>10)
						    )
					    ),
					    "4"=>array( "slug"=>"ddd",
							"parent"=>0,
							"node_id"=>4,
							"children"=>array(
								"11"=>array("slug"=>"hhh",
									    "parent"=>4,
									    "node_id"=>11),
								"12"=>array("slug"=>"iii",
									    "parent"=>4,
									    "node_id"=>12),
								"13"=>array("slug"=>"jjj",
									    "parent"=>4,
									    "node_id"=>13)
						    )
					    )


				)

		 );

		$result = FOX_sUtil::loftHierarchy($nodes);

		$this->assertEquals($check, $result, FOX_debug::formatError_print($error));
		unset($error);

	}

	
	function test_walkIntersectTree() {


		$tree = array(	"children"=>array(
					    "1"=>array( "slug"=>"aaa",
							"parent"=>0,
							"node_id"=>1,
					    ),
					    "2"=>array( "slug"=>"bbb",
							"parent"=>0,
							"node_id"=>2,
							"children"=>array(
								"5"=>array( "slug"=>"eee",
									    "parent"=>2,
									    "node_id"=>5,
									    "children"=>array(
										    "14"=>array("slug"=>"aaa",
												"parent"=>5,
												"node_id"=>14
										    ),
										    "15"=>array("slug"=>"bbb",
												"parent"=>5,
												"node_id"=>15,
												"children"=>array(
														    "17"=>array("slug"=>"xxx",
																"parent"=>15,
																"node_id"=>17
														    )
												)
										    ),
										    "16"=>array("slug"=>"ccc",
												"parent"=>5,
												"node_id"=>16,
												"children"=>array(
														    "18"=>array("slug"=>"yyy",
																"parent"=>16,
																"node_id"=>18
														    ),
														    "19"=>array("slug"=>"zzz",
																"parent"=>16,
																"node_id"=>19
														    )
												)
										    )
									    )
									 ),
								"6"=>array( "slug"=>"fff",
									    "parent"=>2,
									    "node_id"=>6),
								"7"=>array(
									    "slug"=>"ggg",
									    "parent"=>2,
									    "node_id"=>7,)
							)
					    ),
					    "3"=>array( "slug"=>"ccc",
							"parent"=>0,
							"node_id"=>3,
							"children"=>array(
								"8"=>array( "slug"=>"hhh",
									    "parent"=>3,
									    "node_id"=>8),
								"9"=>array( "slug"=>"iii",
									    "parent"=>3,
									    "node_id"=>9),
								"10"=>array("slug"=>"jjj",
									    "parent"=>3,
									    "node_id"=>10)
						    )
					    ),
					    "4"=>array( "slug"=>"ddd",
							"parent"=>0,
							"node_id"=>4,
							"children"=>array(
								"11"=>array("slug"=>"hhh",
									    "parent"=>4,
									    "node_id"=>11),
								"12"=>array("slug"=>"iii",
									    "parent"=>4,
									    "node_id"=>12),
								"13"=>array("slug"=>"jjj",
									    "parent"=>4,
									    "node_id"=>13)
						    )
					    )


				)

		 );

		// Exact match
		// ===============================================

		$walk = array( "0"=>"bbb", "1"=>"eee", "2"=>"aaa");

		$check = array( "endpoint_id"=>14, 
				"endpoint_name"=>"aaa",
				"walk_key"=>2,
				"transect"=>array()
		);

		$result = FOX_sUtil::walkIntersectTree($walk, $tree, $error);

		$this->assertEquals($check, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Walk longer than graph
		// ===============================================

		$walk = array( "0"=>"bbb", "1"=>"eee", "2"=>"ccc", "3"=>"qqq", "4"=>"ttt");

		$check = array( "endpoint_id"=>16, "endpoint_name"=>"ccc", "walk_key"=>2, "transect"=>array("qqq","ttt"));

		$result = FOX_sUtil::walkIntersectTree($walk, $tree, $error);

		$this->assertEquals($check, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Graph longer than walk
		// ===============================================

		$walk = array( "0"=>"ccc");

		$check = array( "endpoint_id"=>3, "endpoint_name"=>"ccc", "walk_key"=>0, "transect"=>array());

		$result = FOX_sUtil::walkIntersectTree($walk, $tree, $error);

		$this->assertEquals($check, $result, FOX_debug::formatError_print($error));
		unset($error);

		// Null intersect
		// ===============================================

		$walk = array( "0"=>"zzz");

		$check = array("endpoint_id"=>null, "endpoint_name"=>null, "walk_key"=>null, "transect"=>array());

		$result = FOX_sUtil::walkIntersectTree($walk, $tree, $error);

		$this->assertEquals($check, $result, FOX_debug::formatError_print($error));
		unset($error);

	}	


	function test_pluginPathToURL(){


		// Single path as string
		// ============================================

		$test = FOX_PATH_BASE . "/foo/bar.jpg";
		$check = FOX_URL_BASE . "/foo/bar.jpg";

		$result = FOX_sUtil::pluginPathToURL($test);

		$this->assertEquals($check, $result);


		// Multiple paths as array
		// ============================================

		$test = array(
				FOX_PATH_BASE . "/foo/bar1.jpg",
				FOX_PATH_BASE . "/foo/bar2.jpg",
				FOX_PATH_BASE . "/foo/bar3.jpg",
		);

		$check = array(
				FOX_URL_BASE . "/foo/bar1.jpg",
				FOX_URL_BASE . "/foo/bar2.jpg",
				FOX_URL_BASE . "/foo/bar3.jpg",
		);		

		$result = FOX_sUtil::pluginPathToURL($test);

		$this->assertEquals($check, $result);

	}


	function tearDown() {
		parent::tearDown();
	}
	
}

?>