<?php

/**
 * FOXFIRE DATA SANITIZATION CLASS
 * Ensures externally generated data (user input, uploaded files, API, PUSH, oEmbed, etc)
 * meet the format and size specified by the function which consumes the data.
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Database
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfirewiki/DOCS_FOX_db_top
 *
 * ========================================================================================================
 */

class FOX_sanitize {


	/**
	 * Test function for use in unit tests
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return mixed | the processed string
	 */

	public function debug($input, $ctrl=null, &$valid=null, &$error=null){

		if(!$ctrl){
			$valid = true;
			$error = null;
			return $input;
		}
		else {
			$valid = $ctrl["valid"];
			$error = $ctrl["error"];
			return $ctrl["input"];
		}
	}

	
	/**
	 * Guarantees the value is either true, false, or NULL.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return bool | the processed string, cast as (bool)
	 */

	public function bool($input, $ctrl=null, &$valid=null, &$error=null){

	    
		$ctrl_default = array( "null_input"=>"null" );
		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);

		if($input === null){

			if($ctrl["null_input"] == "null"){

				$valid = true;
				return null;
			}
			elseif($ctrl["null_input"] == "false"){

				$valid = true;
				return false;
			}
			elseif($ctrl["null_input"] == "trap"){

				$valid = false;
				$error = "null input value";
				return null;
			}
		}
		else {
			$result = (bool)$input;
			$valid = true;
			return $result;
		}

	}


	/**
	 * Guarantees the value is either is either an int, or NULL.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return int | the processed string, cast as (int)
	 */

	public function int($input, $ctrl=null, &$valid=null, &$error=null){

		$ctrl_default = array(	"null_input"=>"null",
					// These are maxint/minint for a 32-bit system
					"min_val"=>-2147483648,
					"max_val"=>2147483647		    
		);

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);

		if($input === null){

			if($ctrl["null_input"] == "null"){

				$valid = true;
				return null;
			}
			elseif($ctrl["null_input"] == "zero"){

				$input = 0;
			}
			elseif($ctrl["null_input"] == "trap"){

				$valid = false;
				$error = "null input value";
				return null;
			}
		}
		else {

			$result = intval($input);

			if( $result >= $ctrl["min_val"] ){
			    
				if($result <= $ctrl["max_val"] ){

					$valid = true;
					return $result;
				}
				else {
					$valid = false;
					$error = "exceeds min_val";
					return null;
				}	
			}
			else {
				$valid = false;
				$error = "exceeds min_val";
				return null;
			}
		}

	}


	/**
	 * Guarantees the value is either is either a float, or NULL.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return float | the processed string, cast as (float)
	 */

	public function float($input, $ctrl=null, &$valid=null, &$error=null){


		$ctrl_default = array(	"null_input"=>"null" );
		
		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);


		if($input === null){

			if($ctrl["null_input"] == "null"){

				$valid = true;
				return null;
			}
			elseif($ctrl["null_input"] == "zero"){

				$input = 0;
			}
			elseif($ctrl["null_input"] == "trap"){

				$valid = false;
				echo $error;
				$error = "null input value";
				return null;
			}
		}
		else {

			$result = floatval($input);

			if($ctrl["min_val"]){

				if( $result < $ctrl["min_val"] ){

					$valid = false;
					$error = "exceeds min_val";
					return null;
				}
			}
			elseif($ctrl["max_val"]){

				if( $result > $ctrl["max_val"] ){

					$valid = false;
					$error = "exceeds max_val";
					return null;
				}
			}
			else {

				$valid = true;
				return $result;
			}
		}

	}


	/**
         * Strips eveything except [A-Z][a-z][0-9][\s] from
	 * the input string and truncates it to the specified length.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return string | the processed string
	 */

	public function textAndNumbers($input, $ctrl=null, &$valid=null, &$error=null){

		
		$ctrl_default = array(  "null_input"=>"null",
					"min_len"=>0,
					"max_len"=>50000    // (int)0 for unlimited length, but beware some PHP string functions
		);					    // crash on very large strings. For example, preg_replace() crashes
							    // at about 94,000 characters.

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);

		
		if($input === null){

			if($ctrl["null_input"] == "null"){

				$valid = true;
				return null;
			}
			elseif($ctrl["null_input"] == "trap"){

				$valid = false;
				$error = "null input value";
				return null;
			}
		}
		else {

			// Make absolutely sure PHP treats the input data as a string
			$result = (string)$input;

			// Convert accent characters to their non-accented equivalents, because you can't
			// use accented characters in URL's, and slugs/gallery titles form part of URLs
			$result = self::removeAccents($result);

			// Remove all non-alphanumeric characters
			$result = preg_replace( '|[^a-zA-Z0-9\s]|', '', $result );

			// Consolidate contiguous whitespace inside the string: "New    York" -> "New York"
			$result = preg_replace( '|\s+|', ' ', $result );

			// Remove all whitespace from beginning and end of term: "New York  " -> "New York"
			$result = trim($result);

			$len = strlen($result);

			// Handle string being zero length after trimming off padding characters
			if($len == 0){

				if($ctrl["null_input"] == "null"){

					$valid = true;
					return null;
				}
				elseif($ctrl["null_input"] == "trap"){

					$valid = false;
					$error = "null input value";
					return null;
				}
			}

			// Truncate strings that exceed the max length
			if($len > $ctrl["max_len"]){

				$valid = true;
				$result = substr($result, 0, $ctrl["max_len"]);
				return $result;
			}
			// Fail strings that exceed min length
			elseif($len < $ctrl["min_len"]){

				$valid = false;
				$error = "string exceeds min_len";
				return $result;
			}
			// Otherwise, return the processed string
			else {

				$valid = true;
				return $result;
			}
		}

	}


	/**
         * Strips everything except [a-z][0-9][-_] from the input string.
	 * This is intended for checking user-defined slug names.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return string | the processed string
	 */

	public function slug($input, $ctrl=null, &$valid=null, &$error=null){


		$ctrl_default = array(  "null_input"=>"trap",
					"min_len"=>1,
					"max_len"=>255	// (int)0 for unlimited length, but beware some PHP string functions
		);					// crash on very large strings. For example, preg_replace() crashes
							// at about 94,000 characters.

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);


		if($input === null){

			if($ctrl["null_input"] == "null"){

				$valid = true;
				return null;
			}
			elseif($ctrl["null_input"] == "trap"){

				$valid = false;
				$error = "null input value";
				return null;
			}
		}
		else {

			// Make absolutely sure PHP treats the input data as a string
			$result = (string)$input;

			// Convert accent characters to their non-accented equivalents, because you can't
			// use accented characters in URL's, and slugs/gallery titles form part of URLs
			$result = self::removeAccents($result);

			// Remove all non-alphanumeric characters, including spaces (slugs cannot have spaces in them)
			$result = preg_replace( '|[^a-zA-Z0-9_-]|', '', $result );
			
			// Consolidate blocks of "_" and "-" characters
			$result = preg_replace('|_+|', '_', $result);
			$result = preg_replace('|-+|', '-', $result);

			// Remove all whitespace from beginning and end of term: "New York  " -> "New York"
			$result = trim($result);

			$len = strlen($result);

			// Handle string being zero length after trimming off padding characters
			if($len == 0){

				if($ctrl["null_input"] == "null"){

					$valid = true;
					return null;
				}
				elseif($ctrl["null_input"] == "trap"){

					$valid = false;
					$error = "null input value";
					return null;
				}
			}

			// Truncate strings that exceed the max length
			if( ($len > $ctrl["max_len"]) && ($ctrl["max_len"] != 0) ){

				$valid = true;
				$result = substr($result, 0, $ctrl["max_len"]);
				return $result;
			}
			// Fail strings that exceed min length
			elseif($len < $ctrl["min_len"]){

				$valid = false;
				$error = "string exceeds min_len";
				return $result;
			}
			// Otherwise, return the processed string
			else {

				$valid = true;
				return $result;
			}
		}

	}


	public function keyName($input, $ctrl=null, &$valid=null, &$error=null){


		$ctrl_default = array(  "null_input"=>"trap",
					"min_len"=>1,
					"max_len"=>32	// (int)0 for unlimited length, but beware some PHP string functions
		);					// crash on very large strings. For example, preg_replace() crashes
							// at about 94,000 characters.

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);


		if($input === null){

			if($ctrl["null_input"] == "null"){

				$valid = true;
				return null;
			}
			elseif($ctrl["null_input"] == "trap"){

				$valid = false;
				$error = "null input value";
				return null;
			}
		}
		else {

			// Make absolutely sure PHP treats the input data as a string
			$result = (string)$input;

			// Remove all non-alphanumeric characters, including spaces
			$result = preg_replace( '|[^a-zA-Z0-9_-]|', '', $result );

			// Remove all whitespace from beginning and end of key
			$result = trim($result);

			$len = strlen($result);

			// Handle string being zero length after trimming off padding characters
			if($len == 0){

				if($ctrl["null_input"] == "null"){

					$valid = true;
					return null;
				}
				elseif($ctrl["null_input"] == "trap"){

					$valid = false;
					$error = "null input value";
					return null;
				}
			}

			// Truncate strings that exceed the max length
			if( ($len > $ctrl["max_len"]) && ($ctrl["max_len"] != 0) ){

				$valid = true;
				$result = substr($result, 0, $ctrl["max_len"]);
				return $result;
			}
			// Fail strings that exceed min length
			elseif($len < $ctrl["min_len"]){

				$valid = false;
				$error = "string exceeds min_len";
				return $result;
			}
			// Otherwise, return the processed string
			else {

				$valid = true;
				return $result;
			}
		}

	}


	/**
         * Removes everything except [A-Z],[a-z],[0-9],[-_] No spaces or special
	 * characters allowed. Used to sanitize class and function names. Currently does
	 * not block numeric prefixes on names (which are illegal)
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return string | the processed string
	 */

	public function classOrFunction($input, $ctrl=null, &$valid=null, &$error=null){


		$ctrl_default = array(  "null_input"=>"trap",
					"min_len"=>1,
					"max_len"=>32	// (int)0 for unlimited length, but beware some PHP string functions
		);					// crash on very large strings. For example, preg_replace() crashes
							// at about 94,000 characters.

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);


		if($input === null){

			if($ctrl["null_input"] == "null"){

				$valid = true;
				return null;
			}
			elseif($ctrl["null_input"] == "trap"){

				$valid = false;
				$error = "null input value";
				return null;
			}
		}
		else {

			// Make absolutely sure PHP treats the input data as a string
			$result = (string)$input;

			// Remove all non-alphanumeric characters, including spaces
			$result = preg_replace( '|[^a-zA-Z0-9_-]|', '', $result );

			// Remove all whitespace from beginning and end of the string
			$result = trim($result);

			$len = strlen($result);

			// Handle string being zero length after trimming off padding characters
			if($len == 0){

				if($ctrl["null_input"] == "null"){

					$valid = true;
					return null;
				}
				elseif($ctrl["null_input"] == "trap"){

					$valid = false;
					$error = "null input value";
					return null;
				}
			}

			// Truncate strings that exceed the max length
			if( ($len > $ctrl["max_len"]) && ($ctrl["max_len"] != 0) ){

				$valid = true;
				$result = substr($result, 0, $ctrl["max_len"]);
				return $result;
			}
			// Fail strings that exceed min length
			elseif($len < $ctrl["min_len"]){

				$valid = false;
				$error = "string exceeds min_len";
				return $result;
			}
			// Otherwise, return the processed string
			else {

				$valid = true;
				return $result;
			}
		}

	}


	/**
         * Removes everything except [A-Z],[a-z],[0-9],[-_.] No spaces or special
	 * characters allowed, except [-], [_], and [.] ...this policy is inline
	 * with Gmail and many other websites. It greatly simplifies backend coding.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return string | the processed string
	 */

	public function userName($input, $ctrl=null, &$valid=null, &$error=null){

		$ctrl_default = array(  "null_input"=>"trap",
					"min_len"=>1,
					"max_len"=>255	// (int)0 for unlimited length, but beware some PHP string functions
		);					// crash on very large strings. For example, preg_replace() crashes
							// at about 94,000 characters.

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);


		if($input === null){

			if($ctrl["null_input"] == "null"){

				$valid = true;
				return null;
			}
			elseif($ctrl["null_input"] == "trap"){

				$valid = false;
				$error = "null input value";
				return null;
			}
		}
		else {

			// Make absolutely sure PHP treats the input data as a string
			$result = (string)$input;

			// Convert accent characters to their non-accented equivalents, because you can't
			// use accented characters in URL's, and slugs/gallery titles form part of URLs
			$result = self::removeAccents($result);

			// Remove all non-alphanumeric characters, including spaces (slugs cannot have spaces in them)
			$result = preg_replace( '|[^a-zA-Z0-9_-.]|', '', $result );

			// Consolidate blocks of "_" and "-" characters
			$result = preg_replace('|_+|', '_', $result);
			$result = preg_replace('|-+|', '-', $result);
			$result = preg_replace('|.+|', '.', $result);

			// Remove all whitespace from beginning and end of term: "New York  " -> "New York"
			$result = trim($result);

			$len = strlen($result);

			// Handle string being zero length after trimming off padding characters
			if($len == 0){

				if($ctrl["null_input"] == "null"){

					$valid = true;
					return null;
				}
				elseif($ctrl["null_input"] == "trap"){

					$valid = false;
					$error = "null input value";
					return null;
				}
			}

			// Truncate strings that exceed the max length
			if( ($len > $ctrl["max_len"]) && ($ctrl["max_len"] != 0) ){

				$valid = false;
				$error = "string exceeds max_len";
				return $result;
			}
			// Fail strings that exceed min length
			elseif($len < $ctrl["min_len"]){

				$valid = false;
				$error = "string exceeds min_len";
				return $result;
			}
			// Otherwise, return the processed string
			else {

				$valid = true;
				return $result;
			}
		}

	}


	/**
         * Strips everything except [A-Z][a-z][0-9][:/_\.] from the input string.
	 * This is intended for letting admin user configure file paths on their
	 * installations.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return string | the processed string
	 */

	public function fileStringLocal($input, $ctrl=null, &$valid=null, &$error=null){

		$ctrl_default = array(  "null_input"=>"null",
					"min_len"=>0,
					"max_len"=>50000    // (int)0 for unlimited length, but beware some PHP string functions
		);					    // crash on very large strings. For example, preg_replace() crashes
							    // at about 94,000 characters.

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);


		if($input === null){

			if($ctrl["null_input"] == "null"){

				$valid = true;
				return null;
			}
			elseif($ctrl["null_input"] == "trap"){

				$valid = false;
				$error = "null input value";
				return null;
			}
		}
		else {

			// Make absolutely sure PHP treats the input data as a string
			$result = (string)$input;

			// Make sure nobody tries to slip in a rouge Omega symbol
			$result = str_replace("Ω", "", $result);
			
			// Convert accent characters to their non-accented equivalents, because you can't
			// use accented characters in URL's, and slugs/gallery titles form part of URLs
			$result = self::removeAccents($result);

			// The PHP preg_replace() function does NOT work properly with the "\" character. Although
			// it can be escaped by using a \\ double instance, PREG won't reliably match groups of \'s.
			// Unfortunately, the "\" character is used in directory paths in Windows-based servers.
			// To get around this limitation, we replace "\" with "Ω", run it through preg_replace()
			// and then switch it back ...we can't use less exotic characters like "?&^$#@!*" because 
			// every single one of them is a preg_replace() control character.

			$result = str_replace("\\", "Ω", $result);

			// Remove all non-alphanumeric characters
			$result = preg_replace( '|[^a-zA-Z0-9Ω/:_.~-]|', '', $result );

			$result = str_replace("Ω", "\\", $result);

			// Remove all whitespace from beginning and end of term: "New York  " -> "New York"
			$result = trim($result);

			$len = strlen($result);

			// Handle string being zero length after trimming off padding characters
			if($len == 0){

				if($ctrl["null_input"] == "null"){

					$valid = true;
					return null;
				}
				elseif($ctrl["null_input"] == "trap"){

					$valid = false;
					$error = "null input value";
					return null;
				}
			}

			// Truncate strings that exceed the max length
			if($len > $ctrl["max_len"]){

				$valid = true;
				$result = substr($result, 0, $ctrl["max_len"]);
				return $result;
			}
			// Fail strings that exceed min length
			elseif($len < $ctrl["min_len"]){

				$valid = false;
				$error = "string exceeds min_len";
				return $result;
			}
			// Otherwise, return the processed string
			else {

				$valid = true;
				return $result;
			}
		}
	}


	/**
         * Strips ASCII and Latin1 control characters. Passes spaces.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return string | the processed string
	 */

	public function printableCharacter($input, $ctrl=null, &$valid=null, &$error=null){

		$ctrl_default = array(  "null_input"=>"null",
					"min_len"=>0,
					"max_len"=>50000    // (int)0 for unlimited length, but beware some PHP string functions
		);					    // crash on very large strings. For example, preg_replace() crashes
							    // at about 94,000 characters.

		$ctrl = FOX_sUtil::parseArgs($ctrl, $ctrl_default);


		if($input === null){

			if($ctrl["null_input"] == "null"){

				$valid = true;
				return null;
			}
			elseif($ctrl["null_input"] == "trap"){

				$valid = false;
				$error = "null input value";
				return null;
			}
		}
		else {

			// Make absolutely sure PHP treats the input data as a string
			$result = (string)$input;

			// We can't use the [:print:] character class because its a POSIX regex class and it's
			// not available in PHP's PCRE regex implementation (POSIX was deprecated in PHP 5.3).

			// Remove ASCII control characters
			$result = preg_replace( '[\x00-\x1F\x7F]', '', $result );
			
			// Remove Latin1 control characters
			$result = preg_replace( '[\x80-\x9F]', '', $result );

			$len = strlen($result);

			// Handle string being zero length after trimming off padding characters
			if($len == 0){

				if($ctrl["null_input"] == "null"){

					$valid = true;
					return null;
				}
				elseif($ctrl["null_input"] == "trap"){

					$valid = false;
					$error = "null input value";
					return null;
				}
			}

			// Truncate strings that exceed the max length
			if($len > $ctrl["max_len"]){

				$valid = true;
				$result = substr($result, 0, $ctrl["max_len"]);
				return $result;
			}
			// Fail strings that exceed min length
			elseif($len < $ctrl["min_len"]){

				$valid = false;
				$error = "string exceeds min_len";
				return $result;
			}
			// Otherwise, return the processed string
			else {

				$valid = true;
				return $result;
			}
		}
	}




	/**
         * Validates each array key name passes the validator specified in $ctrl['key'] and each
	 * key value passes the validator specified in $ctrl['val']
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return string | the processed string
	 */

	public function arraySimple($input, $ctrl=null, &$valid=null, &$error=null){

		$ctrl_default = array(  "null_input"=>"null",
					"min_len"=>0,
					"max_len"=>50000    // (int)0 for unlimited length, but beware some PHP string functions
		);					    // crash on very large strings. For example, preg_replace() crashes
							    // at about 94,000 characters.

//		foreach( $input as $key_name => $value){
//
//			// Validate key name
//			// Validate value
//		}

		$valid = true;
		
		return $input;

	}

	/**
         * Validates each array key name passes the validator specified in $ctrl['key'] and each
	 * key value passes the validator specified in $ctrl['val']
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return string | the processed string
	 */

	public function arrayPattern($input, $ctrl=null, &$valid=null, &$error=null){

		$ctrl_default = array(  "null_input"=>"null",
					"min_len"=>0,
					"max_len"=>50000    // (int)0 for unlimited length, but beware some PHP string functions
		);					    // crash on very large strings. For example, preg_replace() crashes
							    // at about 94,000 characters.

//		foreach( $input as $key_name => $value){
//
//			// Validate key name
//			// Validate value
//		}

		$valid = true;

		return $input;

	}


	/**
         * Validates nested arrays with arbitrary numbers of keys
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return string | the processed string
	 */

	public function arrayComplex($input, $ctrl=null, &$valid=null, &$error=null){

		$ctrl_default = array(  "null_input"=>"null",
					"min_len"=>0,
					"max_len"=>50000    // (int)0 for unlimited length, but beware some PHP string functions
		);					    // crash on very large strings. For example, preg_replace() crashes
							    // at about 94,000 characters.
		

		$valid = true;

		return $input;

	}


	/**
         * Checks that a URL is valid, including HTTP and HTTPS. Does not allow GET parameters (/whatever/&param1=a&param2=b)
	 * to be appended to a URL
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return string | the processed string
	 */

	public function URL($input, $ctrl=null, &$valid=null, &$error=null){

		$ctrl_default = array(  "null_input"=>"null",
					"min_len"=>0,
					"max_len"=>50000    // (int)0 for unlimited length, but beware some PHP string functions
		);					    // crash on very large strings. For example, preg_replace() crashes
							    // at about 94,000 characters.


		$valid = true;

		return $input;

	}


	/**
         * Checks for a valid set of command prompt options as passed to the FFmpeg transcoder
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return string | the processed string
	 */

	public function commandPromptOptions($input, $ctrl=null, &$valid=null, &$error=null){

		$ctrl_default = array(  "null_input"=>"null",
					"min_len"=>0,
					"max_len"=>50000    // (int)0 for unlimited length, but beware some PHP string functions
		);					    // crash on very large strings. For example, preg_replace() crashes
							    // at about 94,000 characters.


		$valid = true;

		return $input;

	}


	/**
         * Checks for a valid i18n string
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return string | the processed string
	 */

	public function i18nString($input, $ctrl=null, &$valid=null, &$error=null){

		$ctrl_default = array(  "null_input"=>"null",
					"min_len"=>0,
					"max_len"=>50000    // (int)0 for unlimited length, but beware some PHP string functions
		);					    // crash on very large strings. For example, preg_replace() crashes
							    // at about 94,000 characters.


		$valid = true;

		return $input;

	}



	/**
         * Removes all hyperlinks present in the input string, replacing them with
	 * a user-defined value. Also provides an array containing the "text body"
	 * of each link, and the URL the link pointed to. This information is useful
	 * for triggering capchas when a user has posted too many similar links.
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return array $links | array( array("link_text"=>"link url") )
	 */

	public function removeLinks($input, $ctrl=null, &$valid=null, &$error=null){
 
	}

	

	/**
         * TODO: Previously known as bp_album_terms_strip_specialchars
         *
         * @version 1.0
         * @since 1.0
         *
         * @param string $terms | input string to process
	 * @return string | the processed string
	 */

	public function stripSpecialChars($terms) {

		$terms = trim($terms, " \n\t\r\0\x0B,");

		$terms = self::removeAccents($terms);

		$terms = preg_replace('/[^\w-,]/', '', $terms);

		$terms = preg_replace('|\s+|', ' ', $terms);
		$terms = preg_replace('|_+|', '_', $terms);
		$terms = preg_replace('|-+|', '-', $terms);

		$terms = preg_replace('/[-_ ]{2,}/', '-', $terms);

		return $terms;
	}



	/**
         * TODO: Previously known as bp_album_filter_cleanKeyword
	 *
         * @version 1.0
         * @since 1.0
         *
         * @param string $term | input string to process
	 * @return string | the processed string
	 */

	public function cleanKeyword($term){

		// Chop term to 255 characters to prevent overflow attacks. Many PHP string functions have maximum
		// lengths. For example, preg_replace() crashes at about 94,000 characters. See php.net

		if( strlen($term) > 254) {
		    $term = substr($term, 0, 254);
		}

		// Convert accent characters to their non-accented equivalents
		$term = self::removeAccents( $term );

		// Remove all non-alphanumeric characters
		$term = preg_replace( '|[^a-zA-Z0-9\s]|', '', $term );

		// Consolidate contiguous whitespace inside the term. "New    York" -> "New York"
		$term = preg_replace( '|\s+|', ' ', $term );

		// Remove all whitespace from beginning and end of term. "New York  " -> "New York"
		$term = trim($term);

		// Convert all characters to lower case
		$term = strtolower($term);

		return $term;
	}


	/**
         * TODO: Previously known as bp_album_filter_makeUrlFromTerm
	 *
         * @version 1.0
         * @since 1.0
         *
         * @param string $term | input string to process
	 * @return string | the processed string
	 */

	public function makeUrlFromTerm($term){

		// Replace all non-alphanumeric characters, and spaces, with a "-"
		$term = preg_replace( '|[^a-zA-Z0-9]|', '-', $term );

		return $term;
	}


	/**
         * TODO: Previously known as bp_album_filter_makeTermFromUrl
	 *
         * @version 1.0
         * @since 1.0
         *
         * @param string $term | input string to process
	 * @return string | the processed string
	 */

	public function makeTermFromUrl($term){

		// Chop term to 255 characters to prevent overflow attacks. Many PHP string functions have maximum
		// lengths. For example, preg_replace() crashes at about 94,000 characters. See php.net

		if( strlen($term) > 254) {
			$term = substr($term, 0, 254);
		}

		// Convert accent characters to their non-accented equivalents
		$term = self::removeAccents( $term );

		// Remove all non-alphanumeric characters, including spaces, because you can't have a space in
		// a URL. Note this also does the equivalent of running trim() on the string.
		$term = preg_replace( '|[^a-zA-Z0-9-]|', '', $term );

		// Replace groups of dashes with a single dash "------" -> "-"
		//$term = preg_replace( '|\s| ', '-', $term );

		// Convert dashes to spaces
		$term = preg_replace( '|\-+|', ' ', $term );

		// Convert all characters to lower case
		$term = strtolower($term);

		return $term;
	}
	
	
	/**
	 * Checks to see if a string is utf8 encoded. NOTE: This function checks for 5-Byte sequences, 
	 * UTF8 has Bytes Sequences with a maximum length of 4.

         * @version 1.0
         * @since 1.0
	 * 
	 * @param string $str The string to be checked
	 * @return bool True if $str fits a UTF-8 model, false otherwise.
	 */
	function seemsUtf8($str) {
	    
	    
		$length = strlen($str);
		
		for($i=0; $i < $length; $i++){
		    
			$c = ord($str[$i]);
			
			if($c < 0x80){
			    
				$n = 0; // 0bbbbbbb
			}
			elseif( ($c & 0xE0) == 0xC0 ){
			    
				$n=1; // 110bbbbb
			}
			elseif( ($c & 0xF0) == 0xE0){
			    
				$n=2; // 1110bbbb
			}
			elseif( ($c & 0xF8) == 0xF0 ){
			    
				$n=3; // 11110bbb
			}
			elseif( ($c & 0xFC) == 0xF8 ){
			    
				$n=4; // 111110bb
			}
			elseif( ($c & 0xFE) == 0xFC ){ 
			    
				$n=5; // 1111110b
			}
			else {
				// Does not match any model
				return false; 
			}
			
			for($j=0; $j<$n; $j++) { 
				
				# n bytes matching 10bbbbbb follow ?
			    
				if( (++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80) ){
				    
					return false;
				}
			}
			
		}
		
		return true;
		
	}

	/**
	 * Converts all accent characters to ASCII characters. If there are no accent characters, 
	 * then the string given is just returned.
	 *
         * @version 1.0
         * @since 1.0
	 *
	 * @param string $string Text that might have accent characters
	 * @return string Filtered string with replaced "nice" characters.
	 */	
	function removeAccents($string) {
	    
	    
		if( !preg_match('/[\x80-\xff]/', $string) ){
		    
			return $string;
		}

		if( self::seemsUtf8($string) ){
		    
			$chars = array(
			    
				// Decompositions for Latin-1 Supplement

				chr(194).chr(170) => 'a', chr(194).chr(186) => 'o',
				chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
				chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
				chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
				chr(195).chr(134) => 'AE',chr(195).chr(135) => 'C',
				chr(195).chr(136) => 'E', chr(195).chr(137) => 'E',
				chr(195).chr(138) => 'E', chr(195).chr(139) => 'E',
				chr(195).chr(140) => 'I', chr(195).chr(141) => 'I',
				chr(195).chr(142) => 'I', chr(195).chr(143) => 'I',
				chr(195).chr(144) => 'D', chr(195).chr(145) => 'N',
				chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
				chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
				chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
				chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
				chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
				chr(195).chr(158) => 'TH',chr(195).chr(159) => 's',
				chr(195).chr(160) => 'a', chr(195).chr(161) => 'a',
				chr(195).chr(162) => 'a', chr(195).chr(163) => 'a',
				chr(195).chr(164) => 'a', chr(195).chr(165) => 'a',
				chr(195).chr(166) => 'ae',chr(195).chr(167) => 'c',
				chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
				chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
				chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
				chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
				chr(195).chr(176) => 'd', chr(195).chr(177) => 'n',
				chr(195).chr(178) => 'o', chr(195).chr(179) => 'o',
				chr(195).chr(180) => 'o', chr(195).chr(181) => 'o',
				chr(195).chr(182) => 'o', chr(195).chr(184) => 'o',
				chr(195).chr(185) => 'u', chr(195).chr(186) => 'u',
				chr(195).chr(187) => 'u', chr(195).chr(188) => 'u',
				chr(195).chr(189) => 'y', chr(195).chr(190) => 'th',
				chr(195).chr(191) => 'y', chr(195).chr(152) => 'O',
				// Decompositions for Latin Extended-A
				chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
				chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
				chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
				chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
				chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
				chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
				chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
				chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
				chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
				chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
				chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
				chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
				chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
				chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
				chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
				chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
				chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
				chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
				chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
				chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
				chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
				chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
				chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
				chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
				chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
				chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
				chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
				chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
				chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
				chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
				chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
				chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
				chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
				chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
				chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
				chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
				chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
				chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
				chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
				chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
				chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
				chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
				chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
				chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
				chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
				chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
				chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
				chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
				chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
				chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
				chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
				chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
				chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
				chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
				chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
				chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
				chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
				chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
				chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
				chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
				chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
				chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
				chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
				chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
				// Decompositions for Latin Extended-B
				chr(200).chr(152) => 'S', chr(200).chr(153) => 's',
				chr(200).chr(154) => 'T', chr(200).chr(155) => 't',
				// Euro Sign
				chr(226).chr(130).chr(172) => 'E',
				// GBP (Pound) Sign
				chr(194).chr(163) => '',
				// Vowels with diacritic (Vietnamese)
				// unmarked
				chr(198).chr(160) => 'O', chr(198).chr(161) => 'o',
				chr(198).chr(175) => 'U', chr(198).chr(176) => 'u',
				// grave accent
				chr(225).chr(186).chr(166) => 'A', chr(225).chr(186).chr(167) => 'a',
				chr(225).chr(186).chr(176) => 'A', chr(225).chr(186).chr(177) => 'a',
				chr(225).chr(187).chr(128) => 'E', chr(225).chr(187).chr(129) => 'e',
				chr(225).chr(187).chr(146) => 'O', chr(225).chr(187).chr(147) => 'o',
				chr(225).chr(187).chr(156) => 'O', chr(225).chr(187).chr(157) => 'o',
				chr(225).chr(187).chr(170) => 'U', chr(225).chr(187).chr(171) => 'u',
				chr(225).chr(187).chr(178) => 'Y', chr(225).chr(187).chr(179) => 'y',
				// hook
				chr(225).chr(186).chr(162) => 'A', chr(225).chr(186).chr(163) => 'a',
				chr(225).chr(186).chr(168) => 'A', chr(225).chr(186).chr(169) => 'a',
				chr(225).chr(186).chr(178) => 'A', chr(225).chr(186).chr(179) => 'a',
				chr(225).chr(186).chr(186) => 'E', chr(225).chr(186).chr(187) => 'e',
				chr(225).chr(187).chr(130) => 'E', chr(225).chr(187).chr(131) => 'e',
				chr(225).chr(187).chr(136) => 'I', chr(225).chr(187).chr(137) => 'i',
				chr(225).chr(187).chr(142) => 'O', chr(225).chr(187).chr(143) => 'o',
				chr(225).chr(187).chr(148) => 'O', chr(225).chr(187).chr(149) => 'o',
				chr(225).chr(187).chr(158) => 'O', chr(225).chr(187).chr(159) => 'o',
				chr(225).chr(187).chr(166) => 'U', chr(225).chr(187).chr(167) => 'u',
				chr(225).chr(187).chr(172) => 'U', chr(225).chr(187).chr(173) => 'u',
				chr(225).chr(187).chr(182) => 'Y', chr(225).chr(187).chr(183) => 'y',
				// tilde
				chr(225).chr(186).chr(170) => 'A', chr(225).chr(186).chr(171) => 'a',
				chr(225).chr(186).chr(180) => 'A', chr(225).chr(186).chr(181) => 'a',
				chr(225).chr(186).chr(188) => 'E', chr(225).chr(186).chr(189) => 'e',
				chr(225).chr(187).chr(132) => 'E', chr(225).chr(187).chr(133) => 'e',
				chr(225).chr(187).chr(150) => 'O', chr(225).chr(187).chr(151) => 'o',
				chr(225).chr(187).chr(160) => 'O', chr(225).chr(187).chr(161) => 'o',
				chr(225).chr(187).chr(174) => 'U', chr(225).chr(187).chr(175) => 'u',
				chr(225).chr(187).chr(184) => 'Y', chr(225).chr(187).chr(185) => 'y',
				// acute accent
				chr(225).chr(186).chr(164) => 'A', chr(225).chr(186).chr(165) => 'a',
				chr(225).chr(186).chr(174) => 'A', chr(225).chr(186).chr(175) => 'a',
				chr(225).chr(186).chr(190) => 'E', chr(225).chr(186).chr(191) => 'e',
				chr(225).chr(187).chr(144) => 'O', chr(225).chr(187).chr(145) => 'o',
				chr(225).chr(187).chr(154) => 'O', chr(225).chr(187).chr(155) => 'o',
				chr(225).chr(187).chr(168) => 'U', chr(225).chr(187).chr(169) => 'u',
				// dot below
				chr(225).chr(186).chr(160) => 'A', chr(225).chr(186).chr(161) => 'a',
				chr(225).chr(186).chr(172) => 'A', chr(225).chr(186).chr(173) => 'a',
				chr(225).chr(186).chr(182) => 'A', chr(225).chr(186).chr(183) => 'a',
				chr(225).chr(186).chr(184) => 'E', chr(225).chr(186).chr(185) => 'e',
				chr(225).chr(187).chr(134) => 'E', chr(225).chr(187).chr(135) => 'e',
				chr(225).chr(187).chr(138) => 'I', chr(225).chr(187).chr(139) => 'i',
				chr(225).chr(187).chr(140) => 'O', chr(225).chr(187).chr(141) => 'o',
				chr(225).chr(187).chr(152) => 'O', chr(225).chr(187).chr(153) => 'o',
				chr(225).chr(187).chr(162) => 'O', chr(225).chr(187).chr(163) => 'o',
				chr(225).chr(187).chr(164) => 'U', chr(225).chr(187).chr(165) => 'u',
				chr(225).chr(187).chr(176) => 'U', chr(225).chr(187).chr(177) => 'u',
				chr(225).chr(187).chr(180) => 'Y', chr(225).chr(187).chr(181) => 'y',
				// Vowels with diacritic (Chinese, Hanyu Pinyin)
				chr(201).chr(145) => 'a',
				// macron
				chr(199).chr(149) => 'U', chr(199).chr(150) => 'u',
				// acute accent
				chr(199).chr(151) => 'U', chr(199).chr(152) => 'u',
				// caron
				chr(199).chr(141) => 'A', chr(199).chr(142) => 'a',
				chr(199).chr(143) => 'I', chr(199).chr(144) => 'i',
				chr(199).chr(145) => 'O', chr(199).chr(146) => 'o',
				chr(199).chr(147) => 'U', chr(199).chr(148) => 'u',
				chr(199).chr(153) => 'U', chr(199).chr(154) => 'u',
				// grave accent
				chr(199).chr(155) => 'U', chr(199).chr(156) => 'u',
			    
			);

			$string = strtr($string, $chars);
			
		} 
		else {
		    
			// Assume ISO-8859-1 if not UTF-8
		    
			$chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
				.chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
				.chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
				.chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
				.chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
				.chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
				.chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
				.chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
				.chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
				.chr(252).chr(253).chr(255);

			$chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

			$string = strtr($string, $chars['in'], $chars['out']);
			
			$double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
			$double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
			
			$string = str_replace($double_chars['in'], $double_chars['out'], $string);
			
		}

		return $string;
		
	}	
	

} // End of class FOX_sanitize

?>