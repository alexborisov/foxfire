<?php

/**
 * BP-MEDIA HASH TABLE CLASS
 * Maps text strings to 128-bit MD5 hashes, and allows them to be retrieved using the hash value. This
 * class is typically used when text strings need to be normalized to a fixed key length for use as
 * keys in assiciative arrays. DO NOT USE THIS CLASS FOR SECURITY HASHING, the MD5 algorithm is TOO FAST
 * to guard against brute-force attacks.
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package BP-Media
 * @subpackage Hash Table
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

class BPM_hashTable {


	var $store;			    // Hash => String dictionary
	var $prefix;			    // String to prepend to hashes to guarantee keys are always strings	
	
	var $base_16_chars = '0123456789abcdef';
	var $base_62_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';	
	
	
	// ================================================================================================================


	public function __construct($ctrl=null){

		$this->store = array();	
		
		$ctrl_default = array(
			'prefix' => 'K'
		);
		
		$ctrl = wp_parse_args($ctrl, $ctrl_default);
		
		$this->prefix = $ctrl['prefix'];
		
	}
		
	
	/**
	 * Removes all entries in the store.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function flush(){

		$this->store = array();	
		
		return true;		
	}			
	
	/**
	 * Returns the prefixed hash value for a string without adding it to the store
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param mixed $value | single value
	 * @return bool | Exception on failure. Prefixed hash key on success.
	 */

	public function hash($value){
		
		// Values MUST be serialized to prevent (int)0, (bool)false, and
		// NULL from all mapping to the same key

		$s_value = serialize($value);

		// Converting from base_16 to base_62 format reduces the key length 
		// by almost 50%, saving memory

		$hash_16 = md5($s_value);	
		$hash_10 = BPM_math::convertFromBase($hash_16, $this->base_16_chars);			
		$hash_62 = BPM_math::convertToBase($hash_10, $this->base_62_chars);

		// We add $this-prefix to each key to guarantee the key is still  
		// a string in the event we get something like "0000000000000005" 
		// back from convertToBase(). If we passed the value straight 
		// through, PHP would set the 6th key in the array instead of the 
		// named key "0000000000000005".			

		return $this->prefix . $hash_62;	
	    
	}
	
	
	/**
	 * Writes a single raw value to the store
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param mixed $value | single value
	 * @return bool | Exception on failure. Prefixed hash key on success.
	 */

	public function set($value){
		
		$result = self::setMulti( array(0=>$value) );

		return $result[0];	
	    
	}
	
	
	/**
	 * Writes a multiple raw strings to the store 
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @param mixed $value | single value	 
	 * @return bool | Exception on failure. Array of prefixed hash keys on success.
	 */

	public function setMulti($data){	   
			    
		$result = array();
	    
		foreach( $data as $value){
		    
			// Values MUST be serialized to prevent (int)0, (bool)false, and
			// NULL from all mapping to the same key
		    
			$s_value = serialize($value);
		    
			// Converting from base_16 to base_62 format reduces the key length 
			// by almost 50%, saving memory
		    
			$hash_16 = md5($s_value);	
			$hash_10 = BPM_math::convertFromBase($hash_16, $this->base_16_chars);			
			$hash_62 = BPM_math::convertToBase($hash_10, $this->base_62_chars);
			
			// We add $this-prefix to each key to guarantee the key is still  
			// a string in the event we get something like "0000000000000005" 
			// back from convertToBase(). If we passed the value straight 
			// through, PHP would set the 6th key in the array instead of the 
			// named key "0000000000000005".			
			
			$prefixed = $this->prefix . $hash_62;
			
			$this->store[$prefixed] = $s_value;	
			
			$result[] = $prefixed;
			
		}
		unset($value);	
		
		// We can't return as an array of $value => $hash because
		// its entirely possible we'll get things like "foo text string"
		// which isn't a valid associative array key
		
		return $result;	
		
	}	
	
	
	/**
	 * Given a prefixed hash key, returns the key's stored value
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * 
	 * @param string $hash | prefixed hash value
	 * @return mixed | Null on failure. Key value on success.
	 */

	public function get($key, &$valid=null){
			 
	    	    
		if( BPM_sUtil::keyExists($key, $this->store) ){

			$valid = true;		    
			$result = unserialize($this->store[$key]);					
		}
		else {
			$valid = false;		    
			$result = null;
		}
			
		return $result;	
		
	}
	
	
	/**
	 * Reads multiple prefixed hash keys from the store
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 *
	 * @return bool | Exception on failure. True on success.
	 */

	public function getMulti($keys){
			    

		$result = array();
		
		foreach( $keys as $key ){
		    
			if( BPM_sUtil::keyExists($key, $this->store) ){
			    
				$result[$key] = unserialize($this->store[$key]);
			}
		}
		unset($key);
		
		return $result;	
		
	}	

	
	/**
	 * Deletes a prefixed hash key from the datastore
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * 
	 * @param string $key | Prefixed hash key.
	 * @return bool | False on failure. True on success.
	 */

	public function del($key){

	    
		if( BPM_sUtil::keyExists($key, $this->store) ){

			unset($this->store[$key]);
			return true;
		}
		else {
			return false;
		}		
		
	}	
	
	
	/**
	 * Deletes a prefixed hash key from the datastore
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * 
	 * @param array $keys | Array of prefixed hash keys
	 * @return int | Int number of keys deleted
	 */

	public function delMulti($keys){

		$key_count = 0;
	    
		foreach($keys as $key){
		    
			$success = self::del($key);
			
			if($success){
				$key_count++;
			}
		}
		unset($key);
		
		return $key_count;
		
	}	
		
	

	
} // End of class BPM_hashTable

?>