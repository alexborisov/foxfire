<?php

/**
 * FOXFIRE VERSIONS CLASS
 * Checks that all plugins, API's, and services on the host system are the minimum
 * versions needed for FoxFire to run properly
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Util
 * @license GPL v2.0
 * @link https://github.com/FoxFire
 *
 * ========================================================================================================
 */

class FOX_version {

    
	var $min_php_ver = "5.3.0";	    // Minimum PHP Version required to run FoxFire
	var $min_sql_ver = "5.0.15";	    // Minimum SQL Version required to run FoxFire
	var $min_wp_ver = "3.2.1";	    // Minimum WordPress Version required to run FoxFire


	public function  __construct() {}


	/**
	 * Compares two version numbers using a supplied comparison operator. This
	 * function does not have the problematic "3.1" < "3.1.0" behavior that
	 * PHP's version of the function displays.
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * @param string $ver1 | reference string
	 * @param string $ver2 | comparison string
	 * @param string $op | comparison operator: ">=", "<=", ">", "<", "=", "!="
	 * @return bool $result| result of $ver1 [comparison] $ver2
	 */

	public function checkVersion($ver1, $ver2, $op) {


		$valid_ops = array(">=", "<=", ">", "<", "=", "!=");

		if( array_search($op, $valid_ops) === false ){

			throw new FOX_exception(array(
					'numeric'=>1,
					'text'=>"Called with invalid comparison operator. ",
					'data'=>array("ver1"=>$ver1, "ver2"=>$ver2, "op"=>$op),
					'file'=>__FILE__, 'line'=>__LINE__, 'method'=>__METHOD__,
				    )
			);
		}


		// Pre-process the version strings
		// ================================================================================

		$versions = array( "ver1"=>$ver1, "ver2"=>$ver2 );

		foreach($versions as $key => $val){
		    

			// Make absolutely sure PHP treats the input data as a string
			$val = (string)$val;

			// Make sure nobody tries to slip in a rouge Omega symbol
			$val = str_replace("Ω", "", $val);

			// Convert all plausible separator characters to Omega symbols (Ω). It has to be done this way
			// because every single separator charactor has a control function in PCRE
			$separators = array("-", "_", "+", "/", ",", ".", "\\");
			$val = str_replace($separators, "Ω", $val);

			// Remove any remaining non-alphanumeric characters, including spaces
			$val = preg_replace( '|[^a-zA-Z0-9Ω]|', '', $val );

			// Convert each group of one or more Ω separator characters into single "." character. This handles
			// accidental or repeated separators in the version string.
			$val = preg_replace('|Ω+|', '.', $val);

			// Convert all letters to lower case
			$val = strtolower($val);

			$versions[$key] = $val;


		} 
		unset($key, $val);


		// Explode version strings into arrays. Add padding keys filled with (int)0 so each
		// version array has the same number of numeric groups.
		// ================================================================================

		$v1 = explode('.', $versions["ver1"]);
		$v1_size = count($v1);

		$v2 = explode('.', $versions["ver2"]);
		$v2_size = count($v2);

		if($v1_size > $v2_size){

			$diff = $v1_size - $v2_size;

			for($i=1; $i<=$diff; $i++){
				$v2[] = "0";
			}
		}
		elseif($v2_size > $v1_size){

			$diff = $v2_size - $v1_size;

			for($i=1; $i<=$diff; $i++){
				$v1[] = "0";
			}
		}

		// Do the comparison operation. Note array is LTR ordered, and the
		// left-most term is the most significant term.
		// =======================================================================

		$lt_found = false;
		$gt_found = false;
		$gt_pos = 0;
		$lt_pos = 0;

		switch($op){

			// Greater Than or Equal To ">="
			//========================================================
			case ">=" : {
				
				foreach ($v1 as $key => $val_1) {

					$val_2 = $v2[$key];

					if( $val_1 < $val_2){

						if($lt_pos < $key){
							$lt_pos = $key;
						}

						$lt_found = true;
					}
					elseif( $val_1 > $val_2){

						if($gt_pos < $key){
							$gt_pos = $key;
						}

						$gt_found = true;
					}
				}


				if(!$lt_found && !$gt_found){
					$result = true;
				}
				elseif($gt_found && !$lt_found){
					$result = true;
				}
				elseif($gt_found && $lt_found){
				    
				    if ($v1 > $v2){
					
				    return true;
				    }
				    else{
					return false;
					}
				}
				else {
					if($gt_pos < $lt_pos){
						$result = true;
					}
					else {
						$result = false;
					}
				}

			} break;

			// Less Than or Equal To "<="
			//========================================================
			case "<=" : {

				foreach ($v1 as $key => $val_1) {

					$val_2 = $v2[$key];

					if( $val_1 < $val_2){

						if($lt_pos < $key){
							$lt_pos = $key;
						}

						$lt_found = true;
					}
					elseif( $val_1 > $val_2){

						if($gt_pos < $key){
							$gt_pos = $key;
						}

						$gt_found = true;
					}
				}


				if(!$lt_found && !$gt_found){
					$result = true;
				}
				elseif($lt_found && !$gt_found){
					$result = true;
				}
				elseif($gt_found && $lt_found){
				    
				    if ($v2 > $v1){
					
				    return true;
				    }
				    else{
					return false;
					}
				}
				else {
					if($lt_pos < $gt_pos){
						$result = true;
					}
					else {
						$result = false;
					}
				}

			} break;

			// Greater Than ">"
			//========================================================
			case ">"  : {

				foreach ($v1 as $key => $val_1) {

					$val_2 = $v2[$key];

					if( $val_1 < $val_2){

						if($lt_pos < $key){
							$lt_pos = $key;
						}

						$lt_found = true;
					}
					elseif( $val_1 > $val_2){

						if($gt_pos < $key){
							$gt_pos = $key;
						}

						$gt_found = true;
					}
				}


				if(!$gt_found){
					$result = false;
				}
				elseif($gt_found && !$lt_found){
					$result = true;
				}
				elseif($gt_found && $lt_found){
				 
				    if ($v1 < $v2){
					
				    return false;
				    }
				    else{
					return true;
					}
				}
				else {
					if($gt_pos < $lt_pos){
						$result = true;
					}
					else {
						$result = false;
					}
				}


			} break;

			// Less Than "<"
			//========================================================
			case "<"  : {

				foreach ($v1 as $key => $val_1) {

					$val_2 = $v2[$key];

					if( $val_1 < $val_2){

						if($lt_pos < $key){
							$lt_pos = $key;
						}

						$lt_found = true;
					}
					elseif( $val_1 > $val_2){

						if($gt_pos < $key){
							$gt_pos = $key;
						}

						$gt_found = true;
					}
				}


				if(!$lt_found){
					$result = false;
				}
				elseif($lt_found && !$gt_found){
					$result = true;
				}
				elseif($gt_found && $lt_found){
				    
				    if ($v2 > $v1){
					
				    return true;
				    }
				    else{
					return false;
					}
				}
				else {
					if($lt_pos < $gt_pos){
						$result = true;
					}
					else {
						$result = false;
					}
				}

			} break;

			// Equal To "="
			//========================================================
			case "="  : {

				$result = true;

				foreach ($v1 as $key => $val_1) {

					$val_2 = $v2[$key];

					// If any of the blocks is not equal,
					// then the entire string is fails

					if( $val_1 != $val_2 ){
						$result = false;
					}

				}


			} break;

			// Not Equal To "!="
			//========================================================
			case "!=" : {

				$result = false;

				foreach ($v1 as $key => $val_1) {

					$val_2 = $v2[$key];

					// If any of the blocks is not equal,
					// then the entire string passes

					if( ($val_1 != $val_2) ){
						$result = true;
					}
				}

			} break;

		} // END: switch($op)

		return $result;


	} // END function checkVersionNumber()


	/**
	 * Get which version of PHP is installed
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * @return string | PHP Version
	 */

	public function getPHPVersion()
	{
		return PHP_VERSION;
	}	
	
	
	/**
	 * Get which version of WordPress is installed
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * @return string | WordPress Version
	 */

	public function getWPVersion()
	{
		global $wp_version;
		return $wp_version;
	}

	/**
	 * Get which version of MySQL is installed
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * @return string | MySQL Version
	 */

	public function getSQLVersion()
	{
		global $wpdb;
		return $wpdb->db_version();
	}


	/**
	 * Get which version of Apache is installed
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * @return string | Apache Version
	 */

	public function getApacheVersion()
	{
		return apache_get_version();
	}


	/**
	 * Checks that PHP is the minimum version needed for FoxFire to run properly
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * @return bool | False on failure. True on success.
	 */

	public function phpOK() {

		if( self::checkVersion( self::getPHPVersion(), $this->min_php_ver, '>=') == true )
		{
			return true;
		}
		else {
			return false;
		}

	}


	/**
	 * Checks that MySQL is the minimum version needed for FoxFire to run properly
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * @return bool | False on failure. True on success.
	 */

	public function sqlOK() {

		if( self::checkVersion( self::getSQLVersion(), $this->min_sql_ver, '>=') == true )
		{
			return true;
		}
		else {
			return false;
		}

	}


	/**
	 * Checks that WordPress is the minimum version needed for FoxFire to run properly
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * @return bool | False on failure. True on success.
	 */

	public function wpOK() {

		if( self::checkVersion( self::getWPVersion(), $this->min_wp_ver, '>=') == true )
		{
			return true;
		}
		else {
			return false;
		}

	}


	/**
	 * Checks that all plugins, API's, and services on the host system are the minimum
	 * versions needed for FoxFire to run properly
	 *
	 * @version 0.1.9
	 * @since 0.1.9
	 * @return bool | False on failure. True on success.
	 */

	public function allOK() {

		if( (self::phpOK() == true)
		&&  (self::sqlOK() == true)
		&&  (self::wpOK() == true))
		{
			return true;
		}
		else {
			return false;
		}

	}

	
} // End of class FOX_version

?>