<?php

/**
 * BP-MEDIA portABLE PHP LIBRARY - MEMCACHED 
 * Implements most of the functionality of PHP's memcache*D* library using native PHP code, providng 
 * bridge functionality that doesn't require PEAR, doesn't require installation, and can be included 
 * in a project's version control system. This comes at the price of a significant drop in speed, and
 * it only supports a single mamacached instance. Whenever possible, try to use PHP's pre-compiled 
 * memcached library.
 * 
 * @see http://php.net/manual/en/book.memcache.php
 * @see http://php.net/manual/en/book.memcached.php (*note the extra 'D'*)
 * 
 * @version 0.1.9
 * @since 0.1.9
 * @author Based on code from https://github.com/pompo500/xslib-memcached
 * @package BP-Media
 * @subpackage Memcached Portable
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/
 *
 * ========================================================================================================
 */

class BPM_memcached_portable {
    
    
	private $host;			    // Host name or ip address for memcached instance
	private $port;			    // Port for memcached instance

	private $handle;		    // Handle to a socket, as returned by fsockopen()

	
	// ================================================================================================================
	

	public function __construct() {}
	
	
	/*! connect to a memcached -server
	
		@param host hostname or IP of the server
		@param port port of the server
		@param timeout connection timeout [seconds]
		@return xsMemcached on success or bool false if connection failed
	*/
	public function connect($host, $port, $timeout=5) {
	    
		$this->host = $host;
		$this->port = $port;

		$err_no = $errMsg = NULL;

		// Try to open connection to the server
		
		$this->handle= @fsockopen($this->host, $this->port, $err_no, $errMsg, $timeout);
		
		if(!$this->handle){
			return false;
		}
		else {
			return true;
		}
		
	}

	/*! set a value, unconditionally
	
		@param key key
		@param value value
		@param TTL Time-to-live of value
		@return bool Success
	*/
	public function set($key, $value, $TTL = 0) {
	    
		return $this->setOp($key, $value, $TTL, 'set');
	}

	/*! add a value (only if key did not exist)
	
		@param key value
		@param value value
		@param TTL Time-to-live
		@return bool Success (false if key was found)
	*/
	public function add($key, $value, $TTL = 0) {
	    
		return $this->setOp($key, $value, $TTL, 'add');
	}

	/*! append to a value (only if key exists)
	
		@param key value
		@param value value
		@param TTL Time-to-live
		@return bool Success (false if key was not found)
	*/
	public function append($key, $value, $TTL = 0) {
	    
		return $this->setOp($key, $value, $TTL, 'append');
	}

	/*! prepend to a value (only if key exists)
	
		@param key value
		@param value value
		@param TTL Time-to-live
		@return bool Success (false if key was not found)
	*/
	public function prepend($key, $value, $TTL = 0) {
	    
		return $this->setOp($key, $value, $TTL, 'prepend');
	}

	/*! replace a value (= write only if key exists)
	
		@param key value
		@param value value
		@param TTL Time-to-live
		@return bool Success (false if key was not found)
	*/
	public function replace($key, $value, $TTL = 0) {
	    
		return $this->setOp($key, $value, $TTL, 'replace');
	}

	/*! get a value
	
		@param key
		@return bool false if not found, string if found
	*/
	public function get($key) {
	    
		$this->writeLine('get ' . $key);

		$result = '';

		$header = $this->readLine();

		// Header not found => value not found
		if($header == 'END')
			return false;

		while(($line = $this->readLine()) != 'END')
			$result .= $line;

		if($result == '')
			return false;

		$header = explode(' ', $header);

		if($header[0] != 'VALUE' || $header[1] != $key)
			throw new Exception('unexcpected response format');

		$Meta = $header[2];

		$Len = $header[3];

		return $result;
	}

	/*! delete a value
	
		@param key key
		@return bool Success (false if key did not exist)
	*/
	public function delete($key) {
	    
		return $this->writeLine('delete ' . $key, true) != 'NOT_FOUND';
	}

	/*! increment a counter, but only if it exists
	
		@param key key
		@param Amount Amount to decrement
		@return bool Success
	*/
	public function incr($key, $Amount = 1) {
	    
		return ($ret = $this->writeLine('incr ' . $key . ' ' . $Amount, true)) != 'NOT_FOUND' ?
			$ret :
			false
		;
	}

	/*! decrement a counter, but only if it exists (does not go below 0)
	
		@param key key
		@param Amount Amount to decrement
		@return bool Success
	*/
	public function decr($key, $Amount = 1) {
	    
		return ($ret = $this->writeLine('decr ' . $key . ' ' . $Amount, true)) != 'NOT_FOUND' ?
			$ret :
			false
		;
	}	

	/*! return statistics
	
		@param key key to return from statistics
		@return array of statistics. Ex: [pid: 2131, uptime: ...] or or false if key given & not found
	*/
	public function stats($key = NULL) {
	    
		$ret = array();

		$this->writeLine('stats');

		while(($line = $this->readLine()) != 'END')
		{
			$line = explode(' ', $line);

			if($line[0] != 'STAT')
				throw new Exception('unexcpected response format');

			$ret[$line[1]] = $line[2];
		}

		if($key)
			return isset($ret[$key]) ? $ret[$key] : false;

		return $ret;
	}

	/*! Close the connection
	
		@return void
	*/
	public function quit() {
	    
		$this->writeLine('quit');
	}

	/*! Helper to do all set operations (set/add/replace/append/prepend)
	
		@param key value
		@param value value
		@param TTL Time-to-live
		@param op operation name (set/add/...)
		@return bool Success
	*/
	private function setOp($key, $value, $TTL, $op) {
	    
		$this->writeLine($op . ' ' . $key . ' 0 ' . $TTL . ' ' . strlen($value));

		$this->writeLine($value);

		return $this->readLine() == 'STORED';
	}

	/*! Helper function to write a line into the socket
	
		@param command command to write, without the line ending
		@param response Read response too? If set to true, returns readLine()
		@sa readLine()
		@return bool Success or string from readLine() (see response parameter)
	*/
	private function writeLine($command, $response = false) {
	    
		fwrite($this->handle, $command . "\r\n");

		if($response)
			return $this->readLine();

		return true;
	}

	/*! Helper function to read a line from the socket
	
		@return string line of response (without the line ending)
	*/
	private function readLine() {
	    
		return rtrim(fgets($this->handle), "\r\n");
	}


	

	
} // End of class BPM_memcached_portable


?>