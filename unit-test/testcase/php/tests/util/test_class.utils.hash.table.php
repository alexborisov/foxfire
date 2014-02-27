<?php

/**
 * FOXFIRE UNIT TEST SCRIPT - UTILS | HASH TABLE
 * Tests the operation of FoxFire's hash table class
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


class utils_hashTable extends RAZ_testCase {

    
    	function setUp() {
		
		parent::setUp();		
		$this->cls = new FOX_hashTable();
	}
	

	function test_set_single_get_single() {
	    	    
	    
		$flush_ok = $this->cls->flush();
		$this->assertEquals(true, $flush_ok);		
	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
	    
		$test_data = array(
				    array('value'=>null, 'hash'=>'K6ICKNBeftpYwYaCJw8zDLu'),
				    array('value'=>false,'hash'=>'K4VK2cqPxPZbkCZhwN6MzIP'),
				    array('value'=>true,'hash'=>'K22xPMBNvR3eHREpxD7xJJW'),
				    array('value'=>(int)0, 'hash'=>'K2dO46aYtZKHGtY5xN23U34'),
				    array('value'=>(int)1, 'hash'=>'K1xSwqQbsao7YnqK9wSsW4P'),
				    array('value'=>(int)-1, 'hash'=>'K3yRejENU57GUUKBhjVkm67'),
				    array('value'=>(float)1.7, 'hash'=>'K3niwkD4bufpSRd8Gcn3BMW'),
				    array('value'=>(float)-1.6, 'hash'=>'KsjmzXp8pW2iqFWpsgIKrB'),
				    array('value'=>(string)"foo",'hash'=>'K1DMpNMVuSNelDnh9Px98s9'),
				    array('value'=>array(null, true, false, 1, 1.0, "foo"),'hash'=>'K4BmKvNS1b9ioCwX6jauasy'),
				    array('value'=>$test_obj,'hash'=>'K5ORqWbzu5S5IR8OSKUUu8F')
		);
		
		foreach( $test_data as $item ){
		    
			$hash = $this->cls->set($item['value']);
			
			$this->assertEquals($item['hash'], $hash);
			
		}
		unset($item);
		
		
		foreach( $test_data as $item ){
		    
			$valid = false;
			$value = $this->cls->get($item['hash'], $valid);
			
			// The cache should report the key as valid
			$this->assertEquals(true, $valid);
			
			// The returned value should match the value we set
			$this->assertEquals($item['value'], $value);	
			
		}
		unset($item);			    	   		
	    
	}
	
	
	function test_set_multi_get_multi() {
	    	    
	    
		$flush_ok = $this->cls->flush();
		$this->assertEquals(true, $flush_ok);
		
		// Test positive and negative versions of all PHP
		// data types in a single namespace	
	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
	    
		$test_data = array(
				    array('value'=>null, 'hash'=>'K6ICKNBeftpYwYaCJw8zDLu'),
				    array('value'=>false,'hash'=>'K4VK2cqPxPZbkCZhwN6MzIP'),
				    array('value'=>true,'hash'=>'K22xPMBNvR3eHREpxD7xJJW'),
				    array('value'=>(int)0, 'hash'=>'K2dO46aYtZKHGtY5xN23U34'),
				    array('value'=>(int)1, 'hash'=>'K1xSwqQbsao7YnqK9wSsW4P'),
				    array('value'=>(int)-1, 'hash'=>'K3yRejENU57GUUKBhjVkm67'),
				    array('value'=>(float)1.7, 'hash'=>'K3niwkD4bufpSRd8Gcn3BMW'),
				    array('value'=>(float)-1.6, 'hash'=>'KsjmzXp8pW2iqFWpsgIKrB'),
				    array('value'=>(string)"foo",'hash'=>'K1DMpNMVuSNelDnh9Px98s9'),
				    array('value'=>array(null, true, false, 1, 1.0, "foo"),'hash'=>'K4BmKvNS1b9ioCwX6jauasy'),
				    array('value'=>$test_obj,'hash'=>'K5ORqWbzu5S5IR8OSKUUu8F')
		);		
		
		
		// Write keys to class
		// =====================================================
		
		$add_items = array();
		
		foreach( $test_data as $item ){
		    
			$add_items[] = $item['value'];		    
		}
		unset($item);
						
		$hashes = $this->cls->setMulti($add_items);
		
		
		// Verify correct hashes were returned
		// =====================================================					
		
		foreach( $test_data as $item_id => $data ){
		    
			$this->assertEquals($data['hash'], $hashes[$item_id]);		    
		}
		unset($item_id, $data);
				
		
		// Fetch hash values from class
		// =====================================================
		
		$values = $this->cls->getMulti($hashes);
		

		foreach( $test_data as $item_id => $data ){
		    
			$this->assertEquals($data['value'], $values[$data['hash']]);		    
		}
		unset($item_id, $data);	
			
		
	}
	
	
	function test_delSingle() {
	    
	    	   	
		$flush_ok = $this->cls->flush();
		$this->assertEquals(true, $flush_ok);
		
		// Test positive and negative versions of all PHP
		// data types in a single namespace	
	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
	    
		$test_data = array(
				    array('value'=>null, 'hash'=>'K6ICKNBeftpYwYaCJw8zDLu'),
				    array('value'=>false,'hash'=>'K4VK2cqPxPZbkCZhwN6MzIP'),
				    array('value'=>true,'hash'=>'K22xPMBNvR3eHREpxD7xJJW'),
				    array('value'=>(int)0, 'hash'=>'K2dO46aYtZKHGtY5xN23U34'),
				    array('value'=>(int)1, 'hash'=>'K1xSwqQbsao7YnqK9wSsW4P'),
				    array('value'=>(int)-1, 'hash'=>'K3yRejENU57GUUKBhjVkm67'),
				    array('value'=>(float)1.7, 'hash'=>'K3niwkD4bufpSRd8Gcn3BMW'),
				    array('value'=>(float)-1.6, 'hash'=>'KsjmzXp8pW2iqFWpsgIKrB'),
				    array('value'=>(string)"foo",'hash'=>'K1DMpNMVuSNelDnh9Px98s9'),
				    array('value'=>array(null, true, false, 1, 1.0, "foo"),'hash'=>'K4BmKvNS1b9ioCwX6jauasy'),
				    array('value'=>$test_obj,'hash'=>'K5ORqWbzu5S5IR8OSKUUu8F')
		);		
		
		
		// Write keys to class
		// =====================================================
		
		$add_items = array();
		
		foreach( $test_data as $item ){
		    
			$add_items[] = $item['value'];		    
		}
		unset($item);
						
		$hashes = $this->cls->setMulti($add_items);		
				
		
		// Verify initial state
		// =====================================================
		
		$values = $this->cls->getMulti($hashes);
		
		foreach( $test_data as $item_id => $data ){
		    
			$this->assertEquals($data['value'], $values[$data['hash']]);		    
		}
		unset($item_id, $data);	
		
		
		// Delete some keys
		// =====================================================
		
		$delete_keys = array(1,4,6);
		
		foreach($delete_keys as $key_id){
		    
			$key_exists = $this->cls->del($test_data[$key_id]['hash']);

			// Should return true to indicate key was valid
			$this->assertEquals(true, $key_exists);
					    
		}
		
		
		// Verify class is in correct state
		// =====================================================

		foreach( $test_data as $item_id => $data ){
		    
			$valid = false;
			$value = $this->cls->get($data['hash'], $valid);
			
			// Key was deleted
			// =====================			
			if( array_search($item_id, $delete_keys) !== false){

				// The cache should report the key as not valid
				$this->assertEquals(false, $valid);

				// The returned value should be NULL
				$this->assertEquals(null, $value);
				
			}
			// Key was not deleted
			// =====================			
			else {			    
				// The cache should report the key as valid
				$this->assertEquals(true, $valid);

				// The returned value should match the value we set
				$this->assertEquals($data['value'], $value);
			}
			
		}
		unset($item_id, $data);			
									    	   			    
	}

	
	function test_delMulti() {
	    
	    	   	
		$flush_ok = $this->cls->flush();
		$this->assertEquals(true, $flush_ok);
		
		// Test positive and negative versions of all PHP
		// data types in a single namespace	
	    
		$test_obj = new stdClass();
		$test_obj->foo = "11";
		$test_obj->bar = "test_Bar";
	    
		$test_data = array(
				    array('value'=>null, 'hash'=>'K6ICKNBeftpYwYaCJw8zDLu'),
				    array('value'=>false,'hash'=>'K4VK2cqPxPZbkCZhwN6MzIP'),
				    array('value'=>true,'hash'=>'K22xPMBNvR3eHREpxD7xJJW'),
				    array('value'=>(int)0, 'hash'=>'K2dO46aYtZKHGtY5xN23U34'),
				    array('value'=>(int)1, 'hash'=>'K1xSwqQbsao7YnqK9wSsW4P'),
				    array('value'=>(int)-1, 'hash'=>'K3yRejENU57GUUKBhjVkm67'),
				    array('value'=>(float)1.7, 'hash'=>'K3niwkD4bufpSRd8Gcn3BMW'),
				    array('value'=>(float)-1.6, 'hash'=>'KsjmzXp8pW2iqFWpsgIKrB'),
				    array('value'=>(string)"foo",'hash'=>'K1DMpNMVuSNelDnh9Px98s9'),
				    array('value'=>array(null, true, false, 1, 1.0, "foo"),'hash'=>'K4BmKvNS1b9ioCwX6jauasy'),
				    array('value'=>$test_obj,'hash'=>'K5ORqWbzu5S5IR8OSKUUu8F')
		);		
		
		
		// Write keys to class
		// =====================================================
		
		$add_items = array();
		
		foreach( $test_data as $item ){
		    
			$add_items[] = $item['value'];		    
		}
		unset($item);
						
		$hashes = $this->cls->setMulti($add_items);		
				
		
		// Verify initial state
		// =====================================================
		
		$values = $this->cls->getMulti($hashes);
		
		foreach( $test_data as $item_id => $data ){
		    
			$this->assertEquals($data['value'], $values[$data['hash']]);		    
		}
		unset($item_id, $data);	
		
		
		// Delete some keys
		// =====================================================
		
		$delete_keys = array(1,4,6);
		$delete_key_hashes = array();
		
		foreach($delete_keys as $key_id){
		    
			$delete_key_hashes[] = $test_data[$key_id]['hash'];		    					    
		}
		unset($key_id);
		
		// Add a key that doesn't exist
		$delete_key_hashes[] = 'NONEXISTENT_KEY_HASH';
		
		$key_count = $this->cls->delMulti($delete_key_hashes);

		// Should report 3 keys were deleted
		$this->assertEquals(3, $key_count);		
		
		
		// Verify class is in correct state
		// =====================================================

		foreach( $test_data as $item_id => $data ){
		    
			$valid = false;
			$value = $this->cls->get($data['hash'], $valid);
			
			// Key was deleted
			// =====================			
			if( array_search($item_id, $delete_keys) !== false){

				// The cache should report the key as not valid
				$this->assertEquals(false, $valid);

				// The returned value should be NULL
				$this->assertEquals(null, $value);
				
			}
			// Key was not deleted
			// =====================			
			else {			    
				// The cache should report the key as valid
				$this->assertEquals(true, $valid);

				// The returned value should match the value we set
				$this->assertEquals($data['value'], $value);
			}
			
		}
		unset($item_id, $data);			
			
						    	   			    
	}	
		
		
	function tearDown() {

		parent::tearDown();
	}

}

?>