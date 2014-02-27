<?php

/**
 * FOXFIRE NETWORK UTILITY FUNCTIONS
 * Utility functions that do commonly used minor tasks
 *
 * @version 1.0
 * @since 1.0
 * @package FoxFire
 * @subpackage Util
 * @license GPL v2.0
 * @link https://github.com/FoxFire/foxfire
 *
 * ========================================================================================================
 */

class FOX_Network_Utils {

	private function __construct() {}

	/**
	 * Check a media url and follow redirects
	 *
	 * @param string $url url to check
	 * @return string|WP_Error final url or error
	 */
	
	public static function check_url( $url ){
	    
		if( empty($url) )
			return new WP_Error('bp_media:url_handle:no_url',sprintf(__("Url handler called without url.","foxfire"),$url) );

		$url = trim($url);
		$url_components = parse_url($url);

		// We need at least a host and a path, or a query
		if( empty($url_components['host']) || ( empty($url_components['path']) && empty($url_components['query']) ) )
			return new WP_Error('bp_media:url_handle:incomplete_url',sprintf(__("Stop processing url %s : doesn't seem a complete media url.","foxfire"),$url) );

		// If the url is given without scheme, http is assumed
		if( empty($url_components['scheme'])  ) {
			$url = 'http://'.$url;
			$url_components['scheme'] = 'http';
		}

		$test_url = $url;

		// Check if the scheme is supported and if the url returns a valid response
		switch( $url_components['scheme'] ) {
			case 'https':
				$test_url = FOX_Network_Utils::follow_redirects( $test_url );

				if ( !is_wp_error($test_url) && 200 == wp_remote_retrieve_response_code( FOX_Network_Utils::remote_head( $test_url ) ) ) {
					$url = $test_url;
					break;
				}else // If an https url is not valid, retry without SSL
					$test_url = substr_replace($url, 'http', 0, 5);

			case 'http':
				$test_url = FOX_Network_Utils::follow_redirects( $test_url );
				if( is_wp_error($test_url) )
					return $test_url;

				$head_request = FOX_Network_Utils::remote_head( $test_url );

				if( is_wp_error($head_request) )
					return new WP_Error( 'bp_media:http:request_error', sprintf( __('Error in the request of url %s (original %s ): %s - %s',"foxfire"),$test_url , $url, $head_response->get_error_code(), $head_response->get_error_message() ), $head_response->get_error_data() );
				if( 200 != wp_remote_retrieve_response_code( $head_request ) )

					return new WP_Error('bp_media:http:response_code_not_200', sprintf( __('Unsuccessful request of url %s (original %s ): response code %d - %s',"foxfire"),$test_url , $url, wp_remote_retrieve_response_code( $head_request ), wp_remote_retrieve_response_message($head_request) ) );
				$url = $test_url;

				break;

			default:
				return new WP_Error('bp_media:url_handle:invalid_url_schema',sprintf(__('Stop processing url %s : the scheme %s is not supported.',"foxfire"),$url_components['scheme']));
		}

		return $url;
	}

	/**
	 * Recursive function to follow redirects
	 *
	 * @param string $url the url to follow
	 * @param int $max_redirects
	 * @param array $followed_urls used in recursion, leave empty
	 * @return string $url final url
	 */
	public static function follow_redirects($url, $max_redirects = 5, $followed_urls = array() ) {
	    
		$head_response = FOX_Network_Utils::remote_head( $url );
		if( is_wp_error($head_response) )
			return new WP_Error( 'bp_media:http:request_error', sprintf( __('Error in the request of url %s : %s - %s',"foxfire"), $url, $head_response->get_error_code(), $head_response->get_error_message() ), $head_response->get_error_data() );

		$location = wp_remote_retrieve_header( $head_response, 'location' );

		$followed_urls[] = $url;

		if( !$location )
			return $url;
		elseif ( $max_redirects-- > 0 )
			return bp_media_follow_redirects( $location, $max_redirects, $followed_urls );
		else
			return new WP_Error('bp_media:http:too_many_redirects', sprintf( __('Too many redirects, followed urls: %s',"foxfire"), join(' -> ', $followed_urls) ) );
	}

	/**
	 * Same as wp_remote_head but cached (not persistent)
	 *
	 * Note: error responses are not cached
	 */
	public static function remote_head ( $url, $args = array() ) {

		static $cache = array();

		$cache_key = $url;
		if( $args )
			$cache_key .= ' args: ' . serialize($args);

		if( !isset( $cache[$cache_key] ) )
			if ( is_wp_error( $head = wp_remote_head( $url, $args ) ) )
				return $head;
			else
				$cache[$cache_key] = $head;

		return $cache[$cache_key];
	}

	/**
	 * Download a remote file
	 *
	 * @param string $url url to download
	 * @param string $file_path path of the saved file
	 * @param int $timeout timeout in sec
	 * @return string|WP_Error downloaded file path or error
	 */
	public static function download_file( $url, $file_path, $timeout ) {
	    
		$handle = FOX_sUtil::new_file_handle($file_path);
		if( is_wp_error( $handle ) )
			return $handle;

		$response = wp_remote_get( $url, array('timeout' => $timeout) );

		if ( is_wp_error( $response ) ) {
			fclose( $handle );
			unlink( $file_path );
			return new WP_Error( 'bp_media:http:request_error', sprintf( __('Error in the request of url %s : %s - %s',"foxfire"), $url, $response->get_error_code(), $response->get_error_message() ), $response->get_error_data() );
		}

		if ( 200 != wp_remote_retrieve_response_code( $response ) ) {
			fclose( $handle );
			unlink( $file_path );
			return new WP_Error('bp_media:http:response_code_not_200', sprintf( __('Unsuccessful request of url %s : response code %d - %s',"foxfire"), $url, wp_remote_retrieve_response_code( $response ), wp_remote_retrieve_response_message( $response ) ) );
		}

		fwrite( $handle, wp_remote_retrieve_body($response) );
		fclose( $handle );
		clearstatcache();

		return $file_path;
	}
	
	
	/**
	 * Returns the name of a TLD, if it exists
	 *
	 * @param string $tld | tld as string
	 * @return bool/string | False on failure. Name of TLD on success.
	 */
	
	public static function getTLD($tld) {
	    
	    
		$data = array(
		    
			'aero'   => 'air-transport industry',
			'asia'   => 'Asia-Pacific region',
			'biz'    => 'business',
			'cat'    => 'Catalan',
			'com'    => 'commercial',
			'coop'   => 'cooperatives',
			'edu'	 => 'US education',
			'gov'	 => 'US government',
			'info'   => 'information',
			'int'    => 'international organizations',
			'jobs'   => 'companies',
			'mil'	 => 'US military',
			'mobi'   => 'mobile devices',
			'museum' => 'museums',
			'name'   => 'individuals, by name',
			'net'    => 'network',
			'org'    => 'organization',
			'post'   => 'postal services',
			'pro'    => 'professions',
			'tel'    => 'Internet communication services',
			'travel' => 'travel and tourism industry related sites',
			'xxx'    => 'Porn',

			'ac' => "Ascension Island",
			'ad' => "Andorra",
			'ae' => "United Arab Emirates",
			'af' => "Afghanistan",
			'ag' => "Antigua and Barbuda",
			'ai' => "Anguilla",
			'al' => "Albania",
			'am' => "Armenia",
			'an' => "Netherlands Antilles (being phased out)",
			'ao' => "Angola",
			'aq' => "Antarctica",
			'ar' => "Argentina",
			'as' => "American Samoa",
			'at' => "Austria",
			'au' => "Australia",
			'aw' => "Aruba",
			'ax' => "Aland Islands",
			'az' => "Azerbaijan",
			'ba' => "Bosnia and Herzegovina",
			'bb' => "Barbados",
			'bd' => "Bangladesh",
			'be' => "Belgium",
			'bf' => "Burkina Faso",
			'bg' => "Bulgaria",
			'bh' => "Bahrain",
			'bi' => "Burundi",
			'bj' => "Benin",
			'bl' => "Saint Barthelemy",
			'bm' => "Bermuda",
			'bn' => "Brunei Darussalam",
			'bo' => "Bolivia",
			'bq' => "Bonaire, Sint Eustatius and Saba",
			'br' => "Brazil",
			'bs' => "Bahamas",
			'bt' => "Bhutan",
			'bv' => "Bouvet Island",
			'bw' => "Botswana",
			'by' => "Belarus",
			'bz' => "Belize",
			'ca' => "Canada",
			'cc' => "Cocos (Keeling) Islands",
			'cd' => "Congo, The Democratic Republic of the",
			'cf' => "Central African Republic",
			'cg' => "Congo",
			'ch' => "Switzerland",
			'ci' => "Cote d'Ivoire",
			'ck' => "Cook Islands",
			'cl' => "Chile",
			'cm' => "Cameroon",
			'cn' => "China",
			'co' => "Colombia",
			'cr' => "Costa Rica",
			'cu' => "Cuba",
			'cv' => "Cape Verde",
			'cw' => "Curaçao",
			'cx' => "Christmas Island",
			'cy' => "Cyprus",
			'cz' => "Czech Republic",
			'de' => "Germany",
			'dj' => "Djibouti",
			'dk' => "Denmark",
			'dm' => "Dominica",
			'do' => "Dominican Republic",
			'dz' => "Algeria",
			'ec' => "Ecuador",
			'ee' => "Estonia",
			'eg' => "Egypt",
			'eh' => "Western Sahara",
			'er' => "Eritrea",
			'es' => "Spain",
			'et' => "Ethiopia",
			'eu' => "European Union",
			'fi' => "Finland",
			'fj' => "Fiji",
			'fk' => "Falkland Islands (Malvinas)",
			'fm' => "Micronesia, Federated States of",
			'fo' => "Faroe Islands",
			'fr' => "France",
			'ga' => "Gabon",
			'gb' => "United Kingdom",
			'gd' => "Grenada",
			'ge' => "Georgia",
			'gf' => "French Guiana",
			'gg' => "Guernsey",
			'gh' => "Ghana",
			'gi' => "Gibraltar",
			'gl' => "Greenland",
			'gm' => "Gambia",
			'gn' => "Guinea",
			'gp' => "Guadeloupe",
			'gq' => "Equatorial Guinea",
			'gr' => "Greece",
			'gs' => "South Georgia and the South Sandwich Islands",
			'gt' => "Guatemala",
			'gu' => "Guam",
			'gw' => "Guinea-Bissau",
			'gy' => "Guyana",
			'hk' => "Hong Kong",
			'hm' => "Heard Island and McDonald Islands",
			'hn' => "Honduras",
			'hr' => "Croatia",
			'ht' => "Haiti",
			'hu' => "Hungary",
			'id' => "Indonesia",
			'ie' => "Ireland",
			'il' => "Israel",
			'im' => "Isle of Man",
			'in' => "India",
			'io' => "British Indian Ocean Territory",
			'iq' => "Iraq",
			'ir' => "Iran, Islamic Republic of",
			'is' => "Iceland",
			'it' => "Italy",
			'je' => "Jersey",
			'jm' => "Jamaica",
			'jo' => "Jordan",
			'jp' => "Japan",
			'ke' => "Kenya",
			'kg' => "Kyrgyzstan",
			'kh' => "Cambodia",
			'ki' => "Kiribati",
			'km' => "Comoros",
			'kn' => "Saint Kitts and Nevis",
			'kp' => "Korea, Democratic People's Republic of",
			'kr' => "Korea, Republic of",
			'kw' => "Kuwait",
			'ky' => "Cayman Islands",
			'kz' => "Kazakhstan",
			'la' => "Lao People's Democratic Republic",
			'lb' => "Lebanon",
			'lc' => "Saint Lucia",
			'li' => "Liechtenstein",
			'lk' => "Sri Lanka",
			'lr' => "Liberia",
			'ls' => "Lesotho",
			'lt' => "Lithuania",
			'lu' => "Luxembourg",
			'lv' => "Latvia",
			'ly' => "Libyan Arab Jamahiriya",
			'ma' => "Morocco",
			'mc' => "Monaco",
			'md' => "Moldova, Republic of",
			'me' => "Montenegro",
			'mf' => "Saint Martin (French part)",
			'mg' => "Madagascar",
			'mh' => "Marshall Islands",
			'mk' => "Macedonia, The Former Yugoslav Republic of",
			'ml' => "Mali",
			'mlc' => "Copycat Easter Egg",
			'mm' => "Myanmar",
			'mn' => "Mongolia",
			'mo' => "Macao",
			'mp' => "Northern Mariana Islands",
			'mq' => "Martinique",
			'mr' => "Mauritania",
			'ms' => "Montserrat",
			'mt' => "Malta",
			'mu' => "Mauritius",
			'mv' => "Maldives",
			'mw' => "Malawi",
			'mx' => "Mexico",
			'my' => "Malaysia",
			'mz' => "Mozambique",
			'na' => "Namibia",
			'nc' => "New Caledonia",
			'ne' => "Niger",
			'nf' => "Norfolk Island",
			'ng' => "Nigeria",
			'ni' => "Nicaragua",
			'nl' => "Netherlands",
			'no' => "Norway",
			'np' => "Nepal",
			'nr' => "Nauru",
			'nu' => "Niue",
			'nz' => "New Zealand",
			'om' => "Oman",
			'pa' => "Panama",
			'pe' => "Peru",
			'pf' => "French Polynesia",
			'pg' => "Papua New Guinea",
			'ph' => "Philippines",
			'pk' => "Pakistan",
			'pl' => "Poland",
			'pm' => "Saint Pierre and Miquelon",
			'pn' => "Pitcairn",
			'pr' => "Puerto Rico",
			'ps' => "Palestinian Territory, Occupied",
			'pt' => "Portugal",
			'pw' => "Palau",
			'py' => "Paraguay",
			'qa' => "Qatar",
			're' => "Reunion",
			'ro' => "Romania",
			'rs' => "Serbia",
			'ru' => "Russian Federation",
			'rw' => "Rwanda",
			'sa' => "Saudi Arabia",
			'sb' => "Solomon Islands",
			'sc' => "Seychelles",
			'sd' => "Sudan",
			'se' => "Sweden",
			'sg' => "Singapore",
			'sh' => "Saint Helena",
			'si' => "Slovenia",
			'sj' => "Svalbard and Jan Mayen",
			'sk' => "Slovakia",
			'sl' => "Sierra Leone",
			'sm' => "San Marino",
			'sn' => "Senegal",
			'so' => "Somalia",
			'sr' => "Suriname",
			'st' => "Sao Tome and Principe",
			'su' => "Soviet Union (being phased out)",
			'sv' => "El Salvador",
			'sx' => "Sint Maarten (Dutch part)",
			'sy' => "Syrian Arab Republic",
			'sz' => "Swaziland",
			'tc' => "Turks and Caicos Islands",
			'td' => "Chad",
			'tf' => "French Southern Territories",
			'tg' => "Togo",
			'th' => "Thailand",
			'tj' => "Tajikistan",
			'tk' => "Tokelau",
			'tl' => "Timor-Leste",
			'tm' => "Turkmenistan",
			'tn' => "Tunisia",
			'to' => "Tonga",
			'tp' => "Portuguese Timor (being phased out)",
			'tr' => "Turkey",
			'tt' => "Trinidad and Tobago",
			'tv' => "Tuvalu",
			'tw' => "Taiwan, Province of China",
			'tz' => "Tanzania, United Republic of",
			'ua' => "Ukraine",
			'ug' => "Uganda",
			'uk' => "United Kingdom",
			'um' => "United States Minor Outlying Islands",
			'us' => "United States",
			'uy' => "Uruguay",
			'uz' => "Uzbekistan",
			'va' => "Holy See (Vatican City State)",
			'vc' => "Saint Vincent and the Grenadines",
			've' => "Venezuela, Bolivarian Republic of",
			'vg' => "Virgin Islands, British",
			'vi' => "Virgin Islands, U.S.",
			'vn' => "Viet Nam",
			'vu' => "Vanuatu",
			'wf' => "Wallis and Futuna",
			'ws' => "Samoa",
			'ye' => "Yemen",
			'yt' => "Mayotte",
			'za' => "South Africa",
			'zm' => "Zambia",
			'zw' => "Zimbabwe",
		);
		
		if( $tld && array_key_exists($tld, $data) ){
		    
			return $data[$tld];
		}
		else {
			return false;
		}
		
		
	}
	
	
	/**
	 * Determine if SSL is used.
	 *
	 * @version 1.0
	 * @since 1.0
	 *
	 * @return bool| True if SSL, false if not
	 */
	
	public static function isSSL() {
	    
	    
		if( isset($_SERVER['HTTPS']) ){
		    
			if( strtolower($_SERVER['HTTPS']) == 'on'){
			    
				return true;
			}			
			elseif( $_SERVER['HTTPS']  == '1'){
			    
				return true;
			}
			
		} 
		elseif( isset($_SERVER['SERVER_PORT']) && ( $_SERVER['SERVER_PORT'] == '443' ) ) {
		    
			return true;
		}
		
		return false;
	}	
	

} // End of class FOX_Network_Utils

?>