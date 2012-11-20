<?php

/**
 * BP-MEDIA DATA SANITIZATION CLASS
 * Ensures externally generated data (user input, uploaded files, API, PUSH, oEmbed, etc)
 * meet the format and size specified by the function which consumes the data.
 *
 * @version 0.1.9
 * @since 0.1.9
 * @package BP-Media
 * @subpackage Database
 * @license GPL v2.0
 * @link http://code.google.com/p/buddypress-media/wiki/DOCS_BPM_db_top
 *
 * ========================================================================================================
 */

class BPM_sanitize {


	/**
	 * Test function for use in unit tests
         *
         * @version 0.1.9
         * @since 0.1.9
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
         * @version 0.1.9
         * @since 0.1.9
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return bool | the processed string, cast as (bool)
	 */

	public function bool($input, $ctrl=null, &$valid=null, &$error=null){

		$ctrl_default = array( "null_input"=>"null" );
		$ctrl = wp_parse_args($ctrl, $ctrl_default);

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
         * @version 0.1.9
         * @since 0.1.9
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

		$ctrl = wp_parse_args($ctrl, $ctrl_default);

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
         * @version 0.1.9
         * @since 0.1.9
         *
         * @param string $input | input string to process
	 * @param array $ctrl | control parameters
	 * @return float | the processed string, cast as (float)
	 */

	public function float($input, $ctrl=null, &$valid=null, &$error=null){


		$ctrl_default = array(	"null_input"=>"null" );
		
		$ctrl = wp_parse_args($ctrl, $ctrl_default);


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
         * @version 0.1.9
         * @since 0.1.9
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

		$ctrl = wp_parse_args($ctrl, $ctrl_default);

		
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
			$result = remove_accents($result);

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
         * @version 0.1.9
         * @since 0.1.9
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

		$ctrl = wp_parse_args($ctrl, $ctrl_default);


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
			$result = remove_accents($result);

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

		$ctrl = wp_parse_args($ctrl, $ctrl_default);


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
         * @version 0.1.9
         * @since 0.1.9
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

		$ctrl = wp_parse_args($ctrl, $ctrl_default);


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
         * @version 0.1.9
         * @since 0.1.9
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

		$ctrl = wp_parse_args($ctrl, $ctrl_default);


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
			$result = remove_accents($result);

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
         * @version 0.1.9
         * @since 0.1.9
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

		$ctrl = wp_parse_args($ctrl, $ctrl_default);


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
			$result = remove_accents($result);

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
         * @version 0.1.9
         * @since 0.1.9
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

		$ctrl = wp_parse_args($ctrl, $ctrl_default);


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
         * @version 0.1.9
         * @since 0.1.9
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
         * @version 0.1.9
         * @since 0.1.9
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
         * @version 0.1.9
         * @since 0.1.9
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
         * @version 0.1.9
         * @since 0.1.9
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
         * @version 0.1.9
         * @since 0.1.9
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
         * @version 0.1.9
         * @since 0.1.9
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
         * @version 0.1.9
         * @since 0.1.9
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
         * @version 0.1.9
         * @since 0.1.9
         *
         * @param string $terms | input string to process
	 * @return string | the processed string
	 */

	public function stripSpecialChars($terms) {

		$terms = trim($terms, " \n\t\r\0\x0B,");

		$terms = remove_accents($terms);

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
         * @version 0.1.9
         * @since 0.1.9
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
		$term = remove_accents( $term );

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
         * @version 0.1.9
         * @since 0.1.9
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
         * @version 0.1.9
         * @since 0.1.9
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
		$term = remove_accents( $term );

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

} // End of class BPM_sanitize

?>