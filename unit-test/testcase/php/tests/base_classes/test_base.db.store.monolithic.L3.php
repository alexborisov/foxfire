<?php

/**
 * BP-MEDIA UNIT TEST SCRIPT - L3 MONOLITHIC ABSTRACT DATASTORE CLASS
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


class core_L3_monolithic_abstract extends RAZ_testCase {


	var $alb;	    // Album manager class instance
	var $med;	    // Media manager class instance

	// ============================================================================================================ //
	

    	function setUp() {

		parent::setUp();
		
		// Install the album object db table, required by the album manager class
		$album_object = new BPM_album();
		$album_object->install();

		$media_object = new BPM_media();
		$media_object->install();

		// Use class dependency-injection capability to load our classes with
		// mock objects instead of the real singletons

		$args = array(
			'dCache'=> new mock_dCache(),
			'mediaModules'=> new mock_mediaModuleManager(),
			'albumModules'=> new mock_albumModuleManager()
		);

		$this->alb = new BPM_albumManager($args);
		$this->med = new BPM_mediaManager($args);

	}


	function test_addItem_single_slug(){

		// Clear the albums table, medias table, and caches
		// ===================================================

		$this->alb->truncate();
		$this->alb->flushCache();
		$this->med->truncate();
		$this->med->flushCache();


		// Create an album
		// ===============================================

		$test_data = array (
			'date_created'=>"2011-01-01 15:14:13",
			'title'=>"Test Title",
			'caption'=>"Test Caption",
			'privacy'=>4,
			'module_slug'=>"slug_01"
		);

		$result = $this->alb->addItem($user_id=1, $test_data, $error);
		$this->assertEquals(1, $result, BPM_debug::formatError_print($error) );

		// Verify album data is correct
		// ===================================================

		$db = new BPM_db();

		$args = array(
			    array("col"=>"id", "op"=>"=", "val"=>1 )
		);

		$ctrl = array("format"=>"row_array");

		$db_result = $db->runSelectQuery( BPM_album::_struct(), $args, $columns=null, $ctrl);

		$check_data = array (
			"id"=>1,
			'owner_id'=>1,
			'cover_image'=>0,
			'display_order'=>null,
			'date_created'=>"2011-01-01 15:14:13",
			'title'=>"Test Title",
			'caption'=>"Test Caption",
			'privacy'=>4,
			'module_id'=>1
		);

		$this->assertEquals($check_data, $db_result );

		// Add another album to check that the album_id is being incremented correctly
		// ===================================================

		$test_data = array (
			'date_created'=>"2011-01-01 15:14:13",
			'title'=>"Test Title",
			'caption'=>"Test Caption",
			'privacy'=>4,
			'module_slug'=>"slug_01"
		);

		$result = $this->alb->addItem($user_id=1, $test_data, $error);

		// Check correct album_id is being returned
		$this->assertEquals(2, $result, BPM_debug::formatError_print($error) );

	}

	
	function test_addItem_single_id(){


		// Clear the albums table, medias table, and caches
		// ===================================================

		$this->alb->truncate();
		$this->alb->flushCache();
		$this->med->truncate();
		$this->med->flushCache();

		// Create an album
		// ===============================================

		$test_data = array (
			'date_created'=>"2011-01-01 15:14:13",
			'title'=>"Test Title",
			'caption'=>"Test Caption",
			'privacy'=>3,
			'module_id'=>2
		);

		$result = $this->alb->addItem($user_id=1, $test_data, $error);

		// Check the album_id is being returned
		$this->assertEquals(1, $result, BPM_debug::formatError_print($error) );

		// Verify album data is correct
		// ===================================================

		$db = new BPM_db();

		$args = array(
			    array("col"=>"id", "op"=>"=", "val"=>1 )
		);

		$ctrl = array("format"=>"row_array");

		$db_result = $db->runSelectQuery( BPM_album::_struct(), $args, $columns=null, $ctrl);

		$check_data = array (
			"id"=>1,
			'owner_id'=>1,
			'cover_image'=>0,
			'display_order'=>null,
			'date_created'=>"2011-01-01 15:14:13",
			'title'=>"Test Title",
			'caption'=>"Test Caption",
			'privacy'=>3,
			'module_id'=>2
		);

		$this->assertEquals($check_data, $db_result );

	}


	function test_addItem_multi(){

	    
		// Clear the albums table, medias table, and caches
		// ===================================================

		// Clear the albums table, medias table, and caches
		// ===================================================

		$this->alb->truncate();
		$this->alb->flushCache();
		$this->med->truncate();
		$this->med->flushCache();

		// Create albums
		// ===================================================

		$test_data = array (

			array(	'date_created'=>"2011-01-01 15:14:13",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>3,
				'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:14",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>2,
				'module_slug'=>'slug_03'
			),
			array(	'date_created'=>"2011-01-01 15:14:15",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>1,
				'module_id'=>2
			)
		);

		$result = $this->alb->addItemMulti($user_id=1, $test_data, $error);

		// Since we're doing a multi-item add, the function should be returning (bool) true
		// instead of the (int) album_id returned when doing a single insert

		$this->assertEquals(true, $result, BPM_debug::formatError_print($error) );

		// Verify album data is correct
		// ===================================================

		$db = new BPM_db();

		$args = array(
			    array("col"=>"owner_id", "op"=>"=", "val"=>1 )
		);

		$ctrl = array("format"=>"array_key_array", "key_col"=>"id");

		$db_result = $db->runSelectQuery( BPM_album::_struct(), $args, $columns=null, $ctrl);

		// Verify the record matches the test data

		$check_data = array (

			1=>array(
				"id"=>1,
				'owner_id'=>1,
				'cover_image'=>0,
				'display_order'=>null,
				'date_created'=>"2011-01-01 15:14:13",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>3,
				'module_id'=>1
			),
			2=>array(
				"id"=>2,
				'owner_id'=>1,
				'cover_image'=>0,
				'display_order'=>null,
				'date_created'=>"2011-01-01 15:14:14",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>2,
				'module_id'=>3
			),
			3=>array(
				"id"=>3,
				'owner_id'=>1,
				'cover_image'=>0,
				'display_order'=>null,
				'date_created'=>"2011-01-01 15:14:15",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>1,
				'module_id'=>2
			)
		);

		$this->assertEquals($check_data, $db_result );

	}


	function test_getItem_single(){

	    
		// Clear the albums table, medias table, and caches
		// ===================================================

		$this->alb->truncate();
		$this->alb->flushCache();
		$this->med->truncate();
		$this->med->flushCache();

		// Load class with test data
		// ===================================================

		$test_data = array (

			array(	'date_created'=>"2011-01-01 15:14:13",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>3,
				'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:14",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>2,
				'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:15",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>1,
				'module_id'=>2
			),
			array(	'date_created'=>"2011-01-01 15:14:15",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>1,
				'module_id'=>3
			)
		);

		$result = $this->alb->addItemMulti($user_id=1, $test_data, $error);

		// Since we're doing a multi-item add, the function should be returning (bool) true
		// instead of the (int) album_id returned when doing a single insert

		$this->assertEquals(true, $result, BPM_debug::formatError_print($error) );

		$result = $this->alb->get(1);

		$check_object = new BPM_album( array(
							"id"=>1,
							'owner_id'=>1,
							'cover_image'=>0,
							'display_order'=>null,
							'date_created'=>"2011-01-01 15:14:13",
							'title'=>"Test Title",
							'caption'=>"Test Caption",
							'privacy'=>3,
							'module_id'=>1
						) );

		$this->assertEquals($check_object, $result );

	}

	
	function test_getItem_multi(){


		// Clear the albums table, medias table, and caches
		// ===================================================

		$this->alb->truncate();
		$this->alb->flushCache();
		$this->med->truncate();
		$this->med->flushCache();

		// Load class with test data
		// ===================================================
		
		$test_data = array (

			array(	'date_created'=>"2011-01-01 15:14:13",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>3,
				'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:14",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>2,
				'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:15",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>1,
				'module_id'=>2
			),
			array(	'date_created'=>"2011-01-01 15:14:15",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>1,
				'module_id'=>3
			)
		);

		$result = $this->alb->addItemMulti($user_id=1, $test_data, $error);

		// Since we're doing a multi-item add, the function should be returning (bool) true
		// instead of the (int) album_id returned when doing a single insert

		$this->assertEquals(true, $result, BPM_debug::formatError_print($error) );

		$result = $this->alb->get( array(1, 2, 3) );

		$check_array = array(
					new BPM_album( array(
					    "id"=>1,
					    'owner_id'=>1,
					    'cover_image'=>0,
					    'display_order'=>null,
					    'date_created'=>"2011-01-01 15:14:13",
					    'title'=>"Test Title",
					    'caption'=>"Test Caption",
					    'privacy'=>3,
					    'module_id'=>1
					) ),
					new BPM_album( array(
					    "id"=>2,
					    'owner_id'=>1,
					    'cover_image'=>0,
					    'display_order'=>null,
					    'date_created'=>"2011-01-01 15:14:14",
					    'title'=>"Test Title",
					    'caption'=>"Test Caption",
					    'privacy'=>2,
					    'module_id'=>1
					) ),
					new BPM_album( array(
					    "id"=>3,
					    'owner_id'=>1,
					    'cover_image'=>0,
					    'display_order'=>null,
					    'date_created'=>"2011-01-01 15:14:15",
					    'title'=>"Test Title",
					    'caption'=>"Test Caption",
					    'privacy'=>1,
					    'module_id'=>2
					) )

		);

		$this->assertEquals($check_array, $result );

	}


	function test_editItem_simple(){


		// Clear the albums table, medias table, and caches
		// ===================================================

		$this->alb->truncate();
		$this->alb->flushCache();
		$this->med->truncate();
		$this->med->flushCache();

		// Load class with test data
		// ===================================================

		$test_data = array (

			array(	'date_created'=>"2011-01-01 15:14:13",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>3,
				'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:14",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>2,
				'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:15",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>1,
				'module_id'=>2
			)
		);

		$result = $this->alb->addItemMulti($user_id=1, $test_data, $error);
		
		// Return result should be "true" because its a multi-add
		$this->assertEquals(true, $result, BPM_debug::formatError_print($error) );


		// Modify an item
		// ===================================================

		$edit_data = array(
				    'cover_image'=>1377,			// Changing cover image from null
				    'display_order'=>array(2600,1377,6969),	// Changing display order from null
				    'date_created'=>"2011-01-01 16:14:14",
				    'title'=>"Test Title Updated",
				    'caption'=>"Test Caption Updated",
				    'privacy'=>2				// Privacy value is set, but is the same as current value
		);

		$result = $this->alb->editItem($user_id=1, $album_id=2, $edit_data, $error);
		$this->assertEquals(true, $result, BPM_debug::formatError_print($error) );

		// Verify items were correctly updated
		// ===================================================

		$result = $this->alb->get( array(1, 2, 3) );

		$check_array = array(
					new BPM_album( array(
					    "id"=>1,
					    'owner_id'=>1,
					    'cover_image'=>0,
					    'display_order'=>null,
					    'date_created'=>"2011-01-01 15:14:13",
					    'title'=>"Test Title",
					    'caption'=>"Test Caption",
					    'privacy'=>3,
					    'module_id'=>1
					) ),
					new BPM_album( array(
					    "id"=>2,
					    'owner_id'=>1,
					    'cover_image'=>1377,		
					    'display_order'=>array(2600,1377,6969),
					    'date_created'=>"2011-01-01 16:14:14",
					    'title'=>"Test Title Updated",
					    'caption'=>"Test Caption Updated",
					    'privacy'=>2,
					    'module_id'=>1
					) ),
					new BPM_album( array(
					    "id"=>3,
					    'owner_id'=>1,
					    'cover_image'=>0,
					    'display_order'=>null,
					    'date_created'=>"2011-01-01 15:14:15",
					    'title'=>"Test Title",
					    'caption'=>"Test Caption",
					    'privacy'=>1,
					    'module_id'=>2
					) )

		);

		$this->assertEquals($check_array, $result );

	}


	function test_editItem_privacyUpdate(){


		// Clear the albums table, medias table, and caches
		// ===================================================

		$this->alb->truncate();
		$this->alb->flushCache();
		$this->med->truncate();
		$this->med->flushCache();

		// Load albums class with test data
		// ===================================================

		$test_data = array (

			array(	'date_created'=>"2011-01-01 15:14:13",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>3,
				'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:14",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>2,
				'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:15",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>1,
				'module_id'=>2
			)
		);

		$result = $this->alb->addItemMulti($user_id=1, $test_data, $error);

		// Return result should be "true" because its a multi-add
		$this->assertEquals(true, $result, BPM_debug::formatError_print($error) );


		// Add media items to test album
		// ===================================================
		
		$test_data = array (
			'owner_id'=>1,
			'album_id'=>2,
			'title'=>"Test Media Title 01",
			'caption'=>"Test Media Caption 01",
			'date_created'=>"2011-02-02 17:18:19",
			'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(1, $result, BPM_debug::formatError_print($error) );

		$test_data = array (
			'owner_id'=>1,
			'album_id'=>2,
			'title'=>"Test Media Title 02",
			'caption'=>"Test Media Caption 02",
			'date_created'=>"2011-02-02 17:18:19",
			'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(2, $result, BPM_debug::formatError_print($error) );

		$test_data = array (
			'owner_id'=>1,
			'album_id'=>2,
			'title'=>"Test Media Title 03",
			'caption'=>"Test Media Caption 03",
			'date_created'=>"2011-02-02 17:18:19",
			'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(3, $result, BPM_debug::formatError_print($error) );

		// Modify an album, changing its privacy level
		// ===================================================

		$edit_data = array(
				    'cover_image'=>1377,			// Changing cover image from null
				    'display_order'=>array(2600,1377,6969),	// Changing display order from null
				    'date_created'=>"2011-01-01 16:14:14",
				    'title'=>"Test Title Updated",
				    'caption'=>"Test Caption Updated",
				    'privacy'=>4				// Privacy value changed
		);

		$result = $this->alb->editItem($user_id=1, $album_id=2, $edit_data, $error);
		$this->assertEquals(true, $result, BPM_debug::formatError_print($error) );

		// Verify albums were correctly updated
		// ===================================================

		$result = $this->alb->get( array(1, 2, 3) );

		$check_array = array(
					new BPM_album( array(
					    "id"=>1,
					    'owner_id'=>1,
					    'cover_image'=>0,
					    'display_order'=>null,
					    'date_created'=>"2011-01-01 15:14:13",
					    'title'=>"Test Title",
					    'caption'=>"Test Caption",
					    'privacy'=>3,
					    'module_id'=>1
					) ),
					new BPM_album( array(
					    "id"=>2,
					    'owner_id'=>1,
					    'cover_image'=>1377,
					    'display_order'=>array(2600,1377,6969),
					    'date_created'=>"2011-01-01 16:14:14",
					    'title'=>"Test Title Updated",
					    'caption'=>"Test Caption Updated",
					    'privacy'=>4,
					    'module_id'=>1
					) ),
					new BPM_album( array(
					    "id"=>3,
					    'owner_id'=>1,
					    'cover_image'=>0,
					    'display_order'=>null,
					    'date_created'=>"2011-01-01 15:14:15",
					    'title'=>"Test Title",
					    'caption'=>"Test Caption",
					    'privacy'=>1,
					    'module_id'=>2
					) )

		);

		$this->assertEquals($check_array, $result );

		
		// Verify medias were correctly updated
		// ===================================================

		$db = new BPM_db();

		$args = array(
			    array("col"=>"album_id", "op"=>"=", "val"=>2 )
		);

		$ctrl = array("format"=>"row_array");

		$db_result = $db->runSelectQuery( BPM_media::_struct(), $args, $columns=null, $ctrl);

		$check_data = array(

			array (
			    "id"=>1,
			    'owner_id'=>1,
			    'album_id'=>2,
			    'date_created'=>"2011-02-02 17:18:19",
			    'title'=>"Test Media Title 01",
			    'caption'=>"Test Media Caption 01",
			    'privacy'=>4,
			    'module_id'=>1,
			    'pixels_x'=>1000,
			    'pixels_y'=>2000,
			    'bytes_master'=>1111,
			    'module_id'=>1,
			    'master_id'=>"ABC1111"
			),
			array (
			    "id"=>2,
			    'owner_id'=>1,
			    'album_id'=>2,
			    'date_created'=>"2011-02-02 17:18:19",
			    'title'=>"Test Media Title 02",
			    'caption'=>"Test Media Caption 02",
			    'privacy'=>4,
			    'module_id'=>1,
			    'pixels_x'=>1000,
			    'pixels_y'=>2000,
			    'bytes_master'=>1111,
			    'module_id'=>1,
			    'master_id'=>"ABC1111"
			),
			array (
			    "id"=>3,
			    'owner_id'=>1,
			    'album_id'=>2,
			    'date_created'=>"2011-02-02 17:18:19",
			    'title'=>"Test Media Title 03",
			    'caption'=>"Test Media Caption 03",
			    'privacy'=>4,
			    'module_id'=>1,
			    'pixels_x'=>1000,
			    'pixels_y'=>2000,
			    'bytes_master'=>1111,
			    'module_id'=>1,
			    'master_id'=>"ABC1111"
			)

		);
		

	}


	function test_deleteItem_single_noMedias(){

	    
		// Clear the albums table, medias table, and caches
		// ===================================================

		$this->alb->truncate();
		$this->alb->flushCache();
		$this->med->truncate();
		$this->med->flushCache();


		// Load albums class with test data
		// ===================================================

		$test_data = array (

			array(	'date_created'=>"2011-01-01 15:14:13",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>3,
				'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:14",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>2,
				'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:15",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>1,
				'module_id'=>2
			)
		);

		$result = $this->alb->addItemMulti($user_id=1, $test_data, $error);

		// Return result should be "true" because its a multi-add
		$this->assertEquals(true, $result, BPM_debug::formatError_print($error) );


		// Delete an album
		// ===================================================

		$result = $this->alb->deleteItem($user_id=1, $album_id=2, $error);
		$this->assertEquals(1, $result, BPM_debug::formatError_print($error) );

		
		// Verify album was correctly deleted
		// ===================================================

		$result = $this->alb->get( array(1, 2, 3) );

		$check_array = array(
					new BPM_album( array(
					    "id"=>1,
					    'owner_id'=>1,
					    'cover_image'=>0,
					    'display_order'=>null,
					    'date_created'=>"2011-01-01 15:14:13",
					    'title'=>"Test Title",
					    'caption'=>"Test Caption",
					    'privacy'=>3,
					    'module_id'=>1
					) ),
					new BPM_album( array(
					    "id"=>3,
					    'owner_id'=>1,
					    'cover_image'=>0,
					    'display_order'=>null,
					    'date_created'=>"2011-01-01 15:14:15",
					    'title'=>"Test Title",
					    'caption'=>"Test Caption",
					    'privacy'=>1,
					    'module_id'=>2
					) )

		);

		$this->assertEquals($check_array, $result );

	}


	function test_deleteItem_multi_noMedias(){


		// Clear the albums table, medias table, and caches
		// ===================================================

		$this->alb->truncate();
		$this->alb->flushCache();
		$this->med->truncate();
		$this->med->flushCache();

		// Load albums class with test data
		// ===================================================

		$test_data = array (

			array(	'date_created'=>"2011-01-01 15:14:13",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>3,
				'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:14",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>2,
				'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:15",
				'title'=>"Test Title",
				'caption'=>"Test Caption",
				'privacy'=>1,
				'module_id'=>2
			)
		);

		$result = $this->alb->addItemMulti($user_id=1, $test_data, $error);

		// Return result should be "true" because its a multi-add
		$this->assertEquals(true, $result, BPM_debug::formatError_print($error) );

		// Delete albums
		// ===================================================

		$result = $this->alb->deleteItem($user_id=1, $album_id=array(1,3), $error);
		$this->assertEquals(2, $result, BPM_debug::formatError_print($error) );

		// Verify albums were correctly deleted
		// ===================================================

		$result = $this->alb->get( array(1, 2, 3) );

		$check_array = array(
					new BPM_album( array(
					    "id"=>2,
					    'owner_id'=>1,
					    'cover_image'=>0,
					    'display_order'=>null,
					    'date_created'=>"2011-01-01 15:14:14",
					    'title'=>"Test Title",
					    'caption'=>"Test Caption",
					    'privacy'=>2,
					    'module_id'=>1
					) )

		);

		$this->assertEquals($check_array, $result );

	}


	function test_deleteItem_single_withMedias(){


		// Clear the albums table, medias table, and caches
		// ===================================================

		$this->alb->truncate();
		$this->alb->flushCache();
		$this->med->truncate();
		$this->med->flushCache();

		// Load albums class with test data
		// ===================================================

		$test_data = array (

			array(	'date_created'=>"2011-01-01 15:14:13", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>3, 'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:14", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>2, 'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:15", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>1, 'module_id'=>2
			)
		);

		$result = $this->alb->addItemMulti($user_id=1, $test_data, $error);

		// Return result should be "true" because its a multi-add
		$this->assertEquals(true, $result, BPM_debug::formatError_print($error) );

		// Add media items to test albums
		// ===================================================

		$test_data = array (
			'owner_id'=>1, 'album_id'=>2, 'title'=>"Test Media Title 01", 'caption'=>"Test Media Caption 01",
			'date_created'=>"2011-02-02 17:18:19", 'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(1, $result, BPM_debug::formatError_print($error) );

		$test_data = array (
			'owner_id'=>1, 'album_id'=>2, 'title'=>"Test Media Title 02", 'caption'=>"Test Media Caption 02",
			'date_created'=>"2011-02-02 17:18:19", 'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(2, $result, BPM_debug::formatError_print($error) );

		$test_data = array (
			'owner_id'=>1, 'album_id'=>2, 'title'=>"Test Media Title 03", 'caption'=>"Test Media Caption 03",
			'date_created'=>"2011-02-02 17:18:19", 'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(3, $result, BPM_debug::formatError_print($error) );


		// Delete an album
		// ===================================================

		$result = $this->alb->deleteItem($user_id=1, $album_id=2, $error);
		$this->assertEquals(1, $result, BPM_debug::formatError_print($error) );


		// Verify album was correctly deleted
		// ===================================================

		$result = $this->alb->get( array(1, 2, 3) );

		$check_array = array(
					new BPM_album( array(
					    "id"=>1,
					    'owner_id'=>1,
					    'cover_image'=>0,
					    'display_order'=>null,
					    'date_created'=>"2011-01-01 15:14:13",
					    'title'=>"Test Title",
					    'caption'=>"Test Caption",
					    'privacy'=>3,
					    'module_id'=>1
					) ),
					new BPM_album( array(
					    "id"=>3,
					    'owner_id'=>1,
					    'cover_image'=>0,
					    'display_order'=>null,
					    'date_created'=>"2011-01-01 15:14:15",
					    'title'=>"Test Title",
					    'caption'=>"Test Caption",
					    'privacy'=>1,
					    'module_id'=>2
					) )

		);

		$this->assertEquals($check_array, $result );

		// Verify medias were deleted
		// ===================================================

		$db = new BPM_db();

		$args = array(
			    array("col"=>"album_id", "op"=>"=", "val"=>2 )
		);

		$ctrl = array("format"=>"row_array");

		$db_result = $db->runSelectQuery( BPM_media::_struct(), $args, $columns=null, $ctrl);

		$this->assertEquals(null, $db_result );
		
	}


	function test_deleteItem_multi_withMedias(){


		// Clear the albums table, medias table, and caches
		// ===================================================

		$this->alb->truncate();
		$this->alb->flushCache();
		$this->med->truncate();
		$this->med->flushCache();


		// Load albums class with test data
		// ===================================================

		$test_data = array (

			array(	'date_created'=>"2011-01-01 15:14:13", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>3, 'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:14", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>2, 'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:15", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>1, 'module_id'=>2
			)
		);

		$result = $this->alb->addItemMulti($user_id=1, $test_data, $error);
		$this->assertEquals(true, $result, BPM_debug::formatError_print($error) );


		// Add media items to test albums
		// ===================================================

		$test_data = array (
			'owner_id'=>1, 'album_id'=>1, 'title'=>"Test Media Title 01", 'caption'=>"Test Media Caption 01",
			'date_created'=>"2011-02-02 17:18:19", 'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(1, $result, BPM_debug::formatError_print($error) );

		$test_data = array (
			'owner_id'=>1, 'album_id'=>1, 'title'=>"Test Media Title 02", 'caption'=>"Test Media Caption 02",
			'date_created'=>"2011-02-02 17:18:19", 'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(2, $result, BPM_debug::formatError_print($error) );

		$test_data = array (
			'owner_id'=>1, 'album_id'=>1, 'title'=>"Test Media Title 03", 'caption'=>"Test Media Caption 03",
			'date_created'=>"2011-02-02 17:18:19", 'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(3, $result, BPM_debug::formatError_print($error) );

		$test_data = array (
			'owner_id'=>1, 'album_id'=>2, 'title'=>"Test Media Title 04", 'caption'=>"Test Media Caption 04",
			'date_created'=>"2011-02-02 17:18:19", 'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(4, $result, BPM_debug::formatError_print($error) );

		$test_data = array (
			'owner_id'=>1, 'album_id'=>2, 'title'=>"Test Media Title 05", 'caption'=>"Test Media Caption 05",
			'date_created'=>"2011-02-02 17:18:19", 'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(5, $result, BPM_debug::formatError_print($error) );

		$test_data = array (
			'owner_id'=>1, 'album_id'=>2, 'title'=>"Test Media Title 06", 'caption'=>"Test Media Caption 06",
			'date_created'=>"2011-02-02 17:18:19", 'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(6, $result, BPM_debug::formatError_print($error) );

		$test_data = array (
			'owner_id'=>1, 'album_id'=>3, 'title'=>"Test Media Title 07", 'caption'=>"Test Media Caption 07",
			'date_created'=>"2011-02-02 17:18:19", 'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(7, $result, BPM_debug::formatError_print($error) );

		$test_data = array (
			'owner_id'=>1, 'album_id'=>3, 'title'=>"Test Media Title 08", 'caption'=>"Test Media Caption 08",
			'date_created'=>"2011-02-02 17:18:19", 'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(8, $result, BPM_debug::formatError_print($error) );

		$test_data = array (
			'owner_id'=>1, 'album_id'=>3, 'title'=>"Test Media Title 09", 'caption'=>"Test Media Caption 09",
			'date_created'=>"2011-02-02 17:18:19", 'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(9, $result, BPM_debug::formatError_print($error) );


		// Delete albums
		// ===================================================

		$result = $this->alb->deleteItem($user_id=1, $album_id=array(1,3), $error);
		$this->assertEquals(2, $result, BPM_debug::formatError_print($error) );


		// Verify albums were deleted
		// ===================================================

		$result = $this->alb->get( array(1, 2, 3) );

		$check_array = array(
					new BPM_album( array(
					    "id"=>2,
					    'owner_id'=>1,
					    'cover_image'=>4,
					    'display_order'=>array(4,5,6),
					    'date_created'=>"2011-01-01 15:14:14",
					    'title'=>"Test Title",
					    'caption'=>"Test Caption",
					    'privacy'=>2,
					    'module_id'=>1
					) )

		);

		$this->assertEquals($check_array, $result );

		// Verify medias were deleted
		// ===================================================

		$db = new BPM_db();

		$args = array(
			    array("col"=>"owner_id", "op"=>"=", "val"=>1 )
		);

		$ctrl = array("format"=>"array_array");

		$db_result = $db->runSelectQuery( BPM_media::_struct(), $args, $columns=null, $ctrl);

		$check_data = array(

			array (
			    "id"=>4,
			    'owner_id'=>1,
			    'album_id'=>2,
			    'date_created'=>"2011-02-02 17:18:19",
			    'title'=>"Test Media Title 04",
			    'caption'=>"Test Media Caption 04",
			    'privacy'=>2,
			    'module_id'=>1,
			    'pixels_x'=>1000,
			    'pixels_y'=>2000,
			    'bytes_master'=>1111,
			    'module_id'=>1,
			    'master_id'=>"ABC1111"
			),
			array (
			    "id"=>5,
			    'owner_id'=>1,
			    'album_id'=>2,
			    'date_created'=>"2011-02-02 17:18:19",
			    'title'=>"Test Media Title 05",
			    'caption'=>"Test Media Caption 05",
			    'privacy'=>2,
			    'module_id'=>1,
			    'pixels_x'=>1000,
			    'pixels_y'=>2000,
			    'bytes_master'=>1111,
			    'module_id'=>1,
			    'master_id'=>"ABC1111"
			),
			array (
			    "id"=>6,
			    'owner_id'=>1,
			    'album_id'=>2,
			    'date_created'=>"2011-02-02 17:18:19",
			    'title'=>"Test Media Title 06",
			    'caption'=>"Test Media Caption 06",
			    'privacy'=>2,
			    'module_id'=>1,
			    'pixels_x'=>1000,
			    'pixels_y'=>2000,
			    'bytes_master'=>1111,
			    'module_id'=>1,
			    'master_id'=>"ABC1111"
			)

		);

		$this->assertEquals($check_data, $db_result );
		
	}


	function test_getInstanceData(){


		// Clear the albums table, medias table, and caches
		// ===================================================

		$this->alb->truncate();
		$this->alb->flushCache();
		$this->med->truncate();
		$this->med->flushCache();

		// Load albums class with test data
		// ===================================================

		$test_data = array (

			// Module #1 has 5 instances, so its equal to the limit
			// ---------------------------------------------------------
			array(	'date_created'=>"2011-01-01 15:14:13", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>3, 'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:13", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>3, 'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:13", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>3, 'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:13", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>3, 'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:13", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>3, 'module_id'=>1
			),
			// Module #2 has 4 instances, so its under the limit
			// ---------------------------------------------------------
			array(	'date_created'=>"2011-01-01 15:14:15", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>1,'module_id'=>2
			),
			array(	'date_created'=>"2011-01-01 15:14:15", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>1,'module_id'=>2
			),
			array(	'date_created'=>"2011-01-01 15:14:15", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>1,'module_id'=>2
			),
			array(	'date_created'=>"2011-01-01 15:14:15", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>1,'module_id'=>2
			),
			// Module #3 has 2 instances, so its under the limit
			// ---------------------------------------------------------
			array(	'date_created'=>"2011-01-01 15:14:15", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>1,'module_id'=>3
			),
			array(	'date_created'=>"2011-01-01 15:14:15", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>1,'module_id'=>3
			)

		);

		$result = $this->alb->addItemMulti($user_id=1, $test_data, $error);
		$this->assertEquals(true, $result, BPM_debug::formatError_print($error) );

		// Check correct results are returned
		// ===================================================

		$result = $this->alb->getInstanceData($user_id, $module_slug="slug_01", $error);

		$check = array(
				"module_id"=>1,
				"max_instances"=>5,
				"instances_used"=>5,
				"instances_left"=>0
		);

		$this->assertEquals($check, $result, BPM_debug::formatError_print($error) );

		$result = $this->alb->getInstanceData($user_id, $module_slug="slug_02", $error);

		$check = array(
				"module_id"=>2,
				"max_instances"=>7,
				"instances_used"=>4,
				"instances_left"=>3
		);

		$this->assertEquals($check, $result, BPM_debug::formatError_print($error) );

		$result = $this->alb->getInstanceData($user_id, $module_slug="slug_03", $error);

		$check = array(
				"module_id"=>3,
				"max_instances"=>5,
				"instances_used"=>2,
				"instances_left"=>3
		);

		$this->assertEquals($check, $result, BPM_debug::formatError_print($error) );

		// Try to get data for a deactivated module
		$result = $this->alb->getInstanceData($user_id, $module_slug="slug_04", $error);

		$this->assertEquals(false, $result, BPM_debug::formatError_print($error) );

		// Get data for a module with no instances
		$result = $this->alb->getInstanceData($user_id, $module_slug="slug_05", $error);

		$check = array(
				"module_id"=>5,
				"max_instances"=>5,
				"instances_used"=>0,
				"instances_left"=>5
		);

		$this->assertEquals($check, $result, BPM_debug::formatError_print($error) );

	}


	function test_getTypesList(){


		// Clear the albums table, medias table, and caches
		// ===================================================

		$this->alb->truncate();
		$this->alb->flushCache();
		$this->med->truncate();
		$this->med->flushCache();

		// Check initial state
		// ===================================================

		$result = $this->alb->getTypesList($user_id=1, $error);

		$check = array(
				array(
					"module_id"=>1,
					"slug"=>"slug_01",
					"php_class"=>"class_01",
					"actionName"=>"test_action_1",
					"actionDescription"=>"test_description_1",
					"maxInstances"=>5,
					"instancesUsed"=>0,
					"instancesLeft"=>5
				),
				array(
					"module_id"=>2,
					"slug"=>"slug_02",
					"php_class"=>"class_02",
					"actionName"=>"test_action_2",
					"actionDescription"=>"test_description_2",
					"maxInstances"=>7,
					"instancesUsed"=>0,
					"instancesLeft"=>7
				),
				array(
					"module_id"=>3,
					"slug"=>"slug_03",
					"php_class"=>"class_03",
					"actionName"=>"test_action_3",
					"actionDescription"=>"test_description_3",
					"maxInstances"=>5,
					"instancesUsed"=>0,
					"instancesLeft"=>5
				),
				array(
					"module_id"=>5,
					"slug"=>"slug_05",
					"php_class"=>"class_05",
					"actionName"=>"test_action_5",
					"actionDescription"=>"test_description_5",
					"maxInstances"=>5,
					"instancesUsed"=>0,
					"instancesLeft"=>5
				)

		);

		$this->assertEquals($check, $result, BPM_debug::formatError_print($error) );

		
		// Load albums class with test data
		// ===================================================

		$test_data = array (

			// Module #1 has 5 instances, so its equal to the limit
			// ---------------------------------------------------------
			array(	'date_created'=>"2011-01-01 15:14:13", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>3, 'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:13", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>3, 'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:13", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>3, 'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:13", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>3, 'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:13", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>3, 'module_id'=>1
			),
			// Module #2 has 4 instances, so its under the limit
			// ---------------------------------------------------------
			array(	'date_created'=>"2011-01-01 15:14:15", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>1,'module_id'=>2
			),
			array(	'date_created'=>"2011-01-01 15:14:15", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>1,'module_id'=>2
			),
			array(	'date_created'=>"2011-01-01 15:14:15", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>1,'module_id'=>2
			),
			array(	'date_created'=>"2011-01-01 15:14:15", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>1,'module_id'=>2
			),
			// Module #3 has 2 instances, so its under the limit
			// ---------------------------------------------------------
			array(	'date_created'=>"2011-01-01 15:14:15", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>1,'module_id'=>3
			),
			array(	'date_created'=>"2011-01-01 15:14:15", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>1,'module_id'=>3
			)

		);

		$result = $this->alb->addItemMulti($user_id=1, $test_data, $error);
		$this->assertEquals(true, $result, BPM_debug::formatError_print($error) );


		// Check modified state
		// ===================================================

		$result = $this->alb->getTypesList($user_id=1, $error);

		$check = array(
				array(
					"module_id"=>1,
					"slug"=>"slug_01",
					"php_class"=>"class_01",
					"actionName"=>"test_action_1",
					"actionDescription"=>"test_description_1",
					"maxInstances"=>5,
					"instancesUsed"=>5,
					"instancesLeft"=>0
				),
				array(
					"module_id"=>2,
					"slug"=>"slug_02",
					"php_class"=>"class_02",
					"actionName"=>"test_action_2",
					"actionDescription"=>"test_description_2",
					"maxInstances"=>7,
					"instancesUsed"=>4,
					"instancesLeft"=>3
				),
				array(
					"module_id"=>3,
					"slug"=>"slug_03",
					"php_class"=>"class_03",
					"actionName"=>"test_action_3",
					"actionDescription"=>"test_description_3",
					"maxInstances"=>5,
					"instancesUsed"=>2,
					"instancesLeft"=>3
				),
				array(
					"module_id"=>5,
					"slug"=>"slug_05",
					"php_class"=>"class_05",
					"actionName"=>"test_action_5",
					"actionDescription"=>"test_description_5",
					"maxInstances"=>5,
					"instancesUsed"=>0,
					"instancesLeft"=>5
				)

		);

		$this->assertEquals($check, $result, BPM_debug::formatError_print($error) );

	}

	function test_getParentAlbum() {


		// Clear the albums table, medias table, and caches
		// ===================================================

		$this->alb->truncate();
		$this->alb->flushCache();
		$this->med->truncate();
		$this->med->flushCache();

		// Load albums class with test data
		// ===================================================

		$test_data = array (

			array(	'date_created'=>"2011-01-01 15:14:13", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>3, 'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:14", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>2, 'module_id'=>1
			),
			array(	'date_created'=>"2011-01-01 15:14:15", 'title'=>"Test Title",
				'caption'=>"Test Caption", 'privacy'=>1, 'module_id'=>2
			)
		);

		$result = $this->alb->addItemMulti($user_id=1, $test_data, $error);

		// Return result should be "true" because its a multi-add
		$this->assertEquals(true, $result, BPM_debug::formatError_print($error) );

		// Add media items to test albums
		// ===================================================

		$test_data = array (
			'owner_id'=>1, 'album_id'=>3, 'title'=>"Test Media Title 01", 'caption'=>"Test Media Caption 01",
			'date_created'=>"2011-02-02 17:18:19", 'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(1, $result, BPM_debug::formatError_print($error) );

		$test_data = array (
			'owner_id'=>1, 'album_id'=>2, 'title'=>"Test Media Title 02", 'caption'=>"Test Media Caption 02",
			'date_created'=>"2011-02-02 17:18:19", 'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(2, $result, BPM_debug::formatError_print($error) );

		$test_data = array (
			'owner_id'=>1, 'album_id'=>1, 'title'=>"Test Media Title 03", 'caption'=>"Test Media Caption 03",
			'date_created'=>"2011-02-02 17:18:19", 'module_slug'=>"slug_01"
		);

		$result = $this->med->addItem($test_data, $media_object, $error);
		$this->assertEquals(3, $result, BPM_debug::formatError_print($error) );


		// Fetch parent album
		// ===================================================

		$result = $this->alb->getParentAlbum($media_id=3);
		$this->assertEquals(1, $result);

		
	}
	

	function tearDown() {

		$class = new BPM_album();
		$class->uninstall();

		$class = new BPM_media();
		$class->uninstall();
		
		// class BPM_albumManager and class BPM_mediaManager do not have to
		// be uninstalled because they don't have tables associated with them
		
		parent::tearDown();
	}

    
}




?>