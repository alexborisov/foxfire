<?php

/**
 * FOXFIRE MATH FUNCTIONS
 * Base math functions
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Math
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_math {


	private function  __construct() {}


	/**
	 * Converts a formatted size to bytes
	 *
	 * @param $size_str formatted size (up to PB)
	 * @return int size in bytes
	 */

	public static function formattedSizeToBytes($size_str){

	    
		// Convert any string of the form "12345GB", "12345678 gb". "5678g", etc to "g"
		$suffix = preg_replace( '/.*?([a-z]).*/', '$1', strtolower($size_str) );

		// Convert the numeric characters and decimal point in the string to a float.
		$scalar = (float)$size_str;
		
		// Multiply scalar by prefix
		switch( $suffix ) {

			case 'p':   // Petabytes
				$result = $scalar * (1024 * 1024 * 1024 * 1024 * 1024);
				break;
			case 't':   // Terabytes
				$result = $scalar * (1024 * 1024 * 1024 * 1024);
				break;
			case 'g':   // Gigabytes
				$result = $scalar * (1024 * 1024 * 1024);
				break;
			case 'm':   // Megabytes
				$result = $scalar * (1024 * 1024);
				break;
			case 'k':   // Kilobytes
				$result = $scalar * 1024;
				break;
			default	:   // Bytes
				$result = $scalar;
				break;			    
		}

		return $result;
		
	}


	/**
	 * Converts an int value in bytes to a human readable string
	 *
	 * @param int $bytes | size value in bytes
	 * @param int $precision | number of decimal places to round to
	 * @return string | composited text string
	 */
	
	public static function bytesToFormattedSize($bytes, $precision=2) {

	    
		$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		$bytes /= pow(1024, $pow);

		return round($bytes, $precision) . ' ' . $units[$pow];
		
	}


	/**
	 * Converts an int value in bytes to a SI-formatted human readable string.
	 * Example "22714" => "22.7K"
	 *
	 * @param float $raw | raw size value
	 * @param int $digits | number of digits to show in the result (must be >= 3)
	 * @return string | composited text string
	 */
	public static function siFormat($raw, $digits=3, $drop_decimal=false) {

		$raw = (float)$raw;

		if($raw < 1000){

			if($drop_decimal) {

				$result = round($raw);
			}
			else {
				$primary_digits = strlen( (string)round($raw) );
				$decimal_digits = $digits - $primary_digits;

				$result = number_format($raw, $decimal_digits, '.', '');
			}
		}
		else {

			// If the raw value is negative, invert it so that the
			// logarithm and power math below works properly

			if($raw < 0){
				$negative = true;
				$raw = -$raw;
			}

			$units = array('K', 'M', 'G', 'T');

			$pow = floor(($raw ? log($raw) : 0) / log(1000));
			$pow = min($pow, (count($units) - 1) );
			$raw /= pow(1000, $pow);

			$primary_digits = strlen( (string)round($raw) );
			$decimal_digits = $digits - $primary_digits;

			$result = number_format($raw, $decimal_digits, '.', '');

			if($negative){
				$result = -$result;
			}

			$result .= $units[$pow - 1];

		}

		return (string)$result;
		
	}

	
	/**
	 * Converts an int or float into a string with commas inserted between groups of three 
	 * numbers on the left side of the decimal point.
	 *
	 * @param int/float $num | Number as int or float
	 * @return string | Exception on failure. Formatted string on success.
	 */

	public static function formatNum($num) {

	    			    
		if( is_numeric($num) ) {  // Check if its a number

		    
			if( strpos($num, '.') === false ) {  // Check if it has a decimal point

				$decimals = 0;
			}
			else {
				$decimals = strlen($num) - ( strpos($num, '.') + 1 );
			}
		    
			return number_format($num, $decimals);
			
		}
		else {
		    
			throw new FOX_exception( array(
				'numeric'=>1,
				'text'=>"Called with non-number as data source",
				'data'=>$num,
				'file'=>__FILE__, 'class'=>__CLASS__, 'function'=>__FUNCTION__, 'line'=>__LINE__,  
				'child'=>null
			));			    
		}

	}
	 
	
	/**
	 * Encodes a base-10 formatted string to an arbitrary base. Not guaranteed to
	 * be accurate for numbes with a decimal point.
	 *
	 * @param string $str | String in base-10 format. String format is used because
	 *			it can handle numbers greater than 64 bits.
	 * 
	 * @param string $chars | Characters used to encode in arbitrary base
	 * 
	 * @return string | String in arbitrary base format
	 */
	
	public static function convertFromBase($str, $chars) {
	    
	    
		// NOTE: We have to set the "precision paramater" to 0 to the bcadd and bcmul
		// calls, otherwise they may add arbitrary zeros after the decimal point.
	    
		if( preg_match('/^[' . $chars . ']+$/', $str) ){

			$result = '0';

			for ($i=0; $i<strlen($str); $i++){

				if( $i != 0 ){

					$result = bcmul($result, strlen($chars), 0);
				}

				$result = bcadd($result, strpos($chars, $str[$i]), 0);
			}

			return $result;

		}
		else {
			return false;
		}
	    	    
	}
	

	/**
	 * Converts a string encoded in an arbitrary base to base-10 format. Not guaranteed to
	 * be accurate for numbes with a decimal point.
	 *
	 * @param string $str | Source string encoded in arbitrary base
	 * @param string $chars | Characters used to encode in arbitrary base
	 * 
	 * @return string | String in base-10 format. String format is used because
	 *		    it can handle numbers greater than 64 bits.
	 */
	
	public static function convertToBase($str, $chars) {

	    
		// NOTE: We have to set the "precision paramater" to 0 to the bcdiv 
		// call, otherwise it may add arbitrary zeros after the decimal point. 
	    
		if( preg_match('/^[0-9]+$/', $str) ){
		    
			$result = '';

			do {
				$result .= $chars[bcmod($str, strlen($chars))];
				$str = bcdiv($str, strlen($chars), 0);
			}
			while( bccomp($str, '0') != 0 );

			return strrev($result);
		    
		}
		else {
			return false;
		}
		
	}
	
	/**
	 * Given time in seconds, converts to a string in H:M:S: format
	 *
	 * @param int $seconds | Time in seconds
	 * 
	 * @return string | String in H:M:S format
	 */
	
	public static function formatTime($seconds) {


		$hours = round($seconds / 3600);
		
		$seconds = $seconds % 3600;
	
		$minutes = round($seconds / 60);
		
		$seconds = $seconds % 60;;
		
		$result = '';
		
		if($hours){
		    
			$result .= $hours . ':';
		}
		
		if($minutes < 10){
		    
			$result .= '0';
		}
		
		$result .= $minutes . ':';
		
		if($seconds < 10){
		    
			$result .= '0';
		}
		
		$result .= $seconds;
		
		return $result;
						
	}	
	

	
} // End of class FOX_math

?>